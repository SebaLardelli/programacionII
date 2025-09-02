<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/CRUD/CrudLocalidades.php';
require __DIR__ . '/../src/CRUD/CrudUsuarios.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Factory\AppFactory;
use App\Database\BaseDatos;
use App\Modelos\Localidades;
use App\Modelos\Usuarios;
use Tuupola\Middleware\HttpBasicAuthentication;
use Firebase\JWT\JWT;
use Tuupola\Middleware\JwtAuthentication;

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->addErrorMiddleware(true, true, true);

$db = new BaseDatos('localhost', 'proy_calco', 'root', '123456');
$pdo = $db->getPdo();



//AUTH BASICA

$app->get('/api/protected-basic', function ($request, $response) use ($pdo) {
    $auth = $request->getHeaderLine('Authorization');

    //chequear header
    if (!str_starts_with($auth, 'Basic ')) {
        return $response->withStatus(401);
    }
    //decodificar base64
    [$email, $password] = explode(':', base64_decode(substr($auth, 6)), 2);

    $stmt = $pdo->prepare("SELECT id_rol, contrasena_hash FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($password, $usuario['contrasena_hash'])) {
        return $response->withStatus(401);
    }

    $response->getBody()->write(json_encode([
        'usuario' => $email,
        'rol'     => $usuario['id_rol'] == 1 ? 'admin' : 'usuario'
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});

//JWT

$key = "your_secret_key";


$app->post('/login', function (Request $request, Response $response) use ($pdo, $key) {
    $authHeader = $request->getHeaderLine('Authorization');

    if (!$authHeader || stripos($authHeader, 'Basic ') !== 0) {
        $response->getBody()->write(json_encode(["error" => "Se requiere autenticación básica"]));
        return $response->withHeader('Content-Type','application/json')->withStatus(401);
    }

    $decoded = base64_decode(substr($authHeader, 6));
    [$email, $password] = explode(':', $decoded, 2);

    $stmt = $pdo->prepare("SELECT id_rol, contrasena_hash FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($password, $usuario['contrasena_hash'])) {
        $response->getBody()->write(json_encode(["error" => "Credenciales inválidas"]));
        return $response->withHeader('Content-Type','application/json')->withStatus(401);
    }

    $now = time();

    $payload = [
        "iat" => $now,
        "exp" => $now + 3600, 
        "data" => [
            "email"  => $email,
            "id_rol" => (int)$usuario['id_rol']
        ]
    ];
    $token = JWT::encode($payload, $key, 'HS256');

    $response->getBody()->write(json_encode(["token" => $token]));
    return $response->withHeader('Content-Type','application/json');
});

// Middleware JWT
$app->add(new JwtAuthentication([
    "secret"     => $key,
    "algorithm"  => ["HS256"],
    "path"       => ["/api"],
    "ignore"     => ["/login"],
    "attribute"  => "token",
    "secure"     => false, 
    "error" => function (Response $response, array $args) {
        $payload = ["error" => "Unauthorized", "message" => $args["message"] ?? "Token inválido"];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader("Content-Type","application/json")->withStatus(401);
    }
]));


$app->get('/api/protected', function (Request $request, Response $response) {
    $token = $request->getAttribute("token"); // viene decodificado
    $email = $token['data']['email'];
    $rol   = $token['data']['id_rol'] == 1 ? 'admin' : 'usuario';

    $response->getBody()->write(json_encode([
        "usuario" => $email,
        "rol"     => $rol
    ]));
    return $response->withHeader('Content-Type','application/json');
});

// Autorización

class RoleMiddleware {
    private array $allowedRoles;
    public function __construct(array $allowedRoles) {
        $this->allowedRoles = $allowedRoles;
    }
    public function __invoke($request, $handler) {
        $token = $request->getAttribute("token");
        $userRole = $token['data']['id_rol'] ?? null;
        if (!$userRole || !in_array($userRole, $this->allowedRoles)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Acceso denegado']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }
        return $handler->handle($request);
    }
}

// Ruta accesible para admin (id_rol = 1)
$app->get('/api/admin-only', function ($req, $res) {
    $token = $req->getAttribute("token");
    $res->getBody()->write(json_encode([
        "msg" => "Bienvenido Admin",
        "usuario" => $token['data']['email']
    ]));
    return $res->withHeader("Content-Type", "application/json");
})->add(new RoleMiddleware([1]));

// Ruta accesible por usuarios comunes (id_rol = 2)
$app->get('/api/user-only', function ($req, $res) {
    $token = $req->getAttribute("token");
    $res->getBody()->write(json_encode([
        "msg" => "Hola Usuario",
        "usuario" => $token['data']['email']
    ]));
    return $res->withHeader("Content-Type", "application/json");
})->add(new RoleMiddleware([2]));


$app->run();

?>