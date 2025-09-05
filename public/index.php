<?php

require __DIR__ . '/../vendor/autoload.php';

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
use Dotenv\Dotenv;

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->addErrorMiddleware(true, true, true);

//Variables Entorno

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);

$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];


$db = new BaseDatos($host, $dbname, $user, $password);
$pdo = $db->getPdo();

require __DIR__ . '/../src/CRUD/CrudLocalidades.php';
require __DIR__ . '/../src/CRUD/CrudUsuarios.php';
require __DIR__ . '/../src/CRUD/CrudTematicas.php';
require __DIR__ . '/../src/CRUD/CrudProductos.php';
require __DIR__ . '/../src/CRUD/CrudMetodosPago.php';
require __DIR__ . '/../src/CRUD/CrudPuntosRetiro.php';
require __DIR__ . '/../src/CRUD/CrudCategorias.php';
require __DIR__ . '/../src/CRUD/CrudVentas.php';
require __DIR__ . '/../src/CRUD/CrudDetalleVentas.php';
require __DIR__ . '/../src/CRUD/CrudCarrito.php';

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
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "secret"    => $key,
    "algorithm" => ["HS256"],
    "path"      => ["/"],          
    "ignore"    => ["/login", "/Crearlocalidad", "/Crearusuarios"],    
    "attribute" => "token",
    "secure"    => false,
    "error"     => function (Response $response, array $args) {
        $payload = ["error" => "Unauthorized", "message" => $args["message"] ?? "Token inválido"];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader("Content-Type","application/json")->withStatus(401);
    }
]));

$app->get('/api/protected', function (Request $request, Response $response) {
    $token = $request->getAttribute("token");
    $email = $token->data->email;
    $rol   = $token->data->id_rol == 1 ? 'admin' : 'usuario';

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

        // Soportar token como objeto (stdClass) o como array
        $userRole = null;
        if (is_object($token) && isset($token->data)) {
            $userRole = isset($token->data->id_rol) ? (int)$token->data->id_rol : null;
        } elseif (is_array($token)) {
            $data = $token['data'] ?? null;
            if (is_array($data)) {
                $userRole = isset($data['id_rol']) ? (int)$data['id_rol'] : null;
            } elseif (is_object($data)) {
                $userRole = isset($data->id_rol) ? (int)$data->id_rol : null;
            }
        }

        // Si no hay token o no trae id_rol, corresponde 401 (no autenticado)
        if ($userRole === null) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Autorización por rol
        if (!in_array($userRole, $this->allowedRoles, true)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Acceso denegado']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}

$app->run();

?>