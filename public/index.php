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
use Firebase\JWT\Key;


$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->addErrorMiddleware(true, true, true);

//Variables Entorno

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'JWT_SECRET', 'APP_URL']);


$appUrl = $_ENV['APP_URL'];
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];


$db = new BaseDatos($host, $dbname, $user, $password);
$pdo = $db->getPdo();

// JWT Secret
$key = $_ENV['JWT_SECRET'];

// Autorización
class RoleMiddleware {
    private array $allowedRoles;
    public function __construct(array $allowedRoles) {
        $this->allowedRoles = $allowedRoles;
    }
    public function __invoke($request, $handler) {
        $token = $request->getAttribute("token");

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

        if ($userRole === null) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        if (!in_array($userRole, $this->allowedRoles, true)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Acceso denegado']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}

// Controladores
require __DIR__ . '/../src/Controllers/Login/Controller_Login.php';
require __DIR__ . '/../src/Controllers/Usuarios/Controller_Usuarios.php';
require __DIR__ . '/../src/Controllers/Categoria/Controller_Categoria.php';
require __DIR__ . '/../src/Controllers/Productos/Controller_Productos.php';
require __DIR__ . '/../src/Controllers/Localidades/Controller_Localidad.php';
require __DIR__ . '/../src/Controllers/Tematica/Controller_Tematica.php';
require __DIR__ . '/../src/Controllers/PuntoRetiro/Controller_Punto_Retiro.php';
require __DIR__ . '/../src/Controllers/Metodo_Pago/Controller_Metodo_Pago.php';
require __DIR__ . '/../src/Controllers/Carrito/Controller_Carrito.php';
require __DIR__ . '/../src/Controllers/Ventas/Controller_Ventas.php';
require __DIR__ . '/../src/Controllers/DetalleVenta/Controller_Detalle_Venta.php';

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
        'rol'     => $usuario['id_rol'] == 1 ? 'admin' : 'usuario',
        'login_url' => $appUrl . "/login"
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});

// Middleware JWT - Debe ir DESPUÉS de registrar todas las rutas
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "secret"    => $key,
    "algorithm" => ["HS256"],
    "path"      => ["/"],          
    "ignore"    => ["/login", "/Crearlocalidad", "/CrearUsuario", "/Crearusuario"],    
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

$app->run();

?>