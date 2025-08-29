<?php

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Database\BaseDatos;
use App\Modelos\Localidades;
use App\Modelos\Usuarios;
use Tuupola\Middleware\HttpBasicAuthentication;
use Firebase\JWT\JWT;

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->addErrorMiddleware(true, true, true);

$db = new BaseDatos('localhost', 'proy_calco', 'root', '123456');
$pdo = $db->getPdo();



//AUTH BASICA

$app->get('/api/protected', function ($request, $response) use ($pdo) {
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
$app->post('/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if ($username === 'user' && $password === 'password') {
        $key = "your_secret_key";
        $payload = [
            "iss" => "example.com",
            "aud" => "example.com",
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + 3600,
            "data" => [
                "username" => $username
            ]
        ];
        $token = JWT::encode($payload, $key, 'HS256');
        $response->getBody()->write(json_encode(["token" => $token]));
    } else {
        $response->getBody()->write("Credenciales inválidas");
        return $response->withStatus(401);
    }
    return $response->withHeader('Content-Type', 'application/json');
});

// Middleware JWT
$app->add(new JwtAuthentication([
    "secret" => "your_secret_key",
    "attribute" => "token",
    "path" => "/api",
    "ignore" => ["/login"],
    "algorithm" => ["HS256"]
]));

// Ruta protegida
$app->get('/api/protected', function (Request $request, Response $response) {
    $token = $request->getAttribute('token');
    $username = $token['data']['username'];
    $response->getBody()->write("Hola, $username");
    return $response;
});


//LOCALIDAD

//POST
$app->post('/localidad', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();


    if (empty($datos['codigo_postal']) || empty($datos['provincia']) || empty($datos['nombre_localidad'])) {
        $response->getBody()->write(json_encode(['error' => 'Todos los campos son requeridos']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $localidad = new Localidades($pdo);
    $ok = $localidad->crearLocalidad(
        $datos['codigo_postal'],
        $datos['provincia'],
        $datos['nombre_localidad']
    );

    $respuesta = [
        'mensaje' => $ok ? 'Localidad creada correctamente' : 'Error al crear la localidad',
        'datos_recibidos' => $datos
    ];

    $response->getBody()->write(json_encode($respuesta));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
});


//GET
$app->get('/localidades', function (Request $request, Response $response) use ($pdo) {
    $localidades = new Localidades($pdo);
    $resultado = $localidades->traerLocalidades();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de localidades',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/localidades/{codigo_postal}', function (Request $request, Response $response, $args) use ($pdo) {
    $codigo_postal = $args['codigo_postal'];
    $localidades = new Localidades($pdo);
    $resultado = $localidades->traerLocalidadPorCP($codigo_postal);

    if ($resultado) {
        $response->getBody()->write(json_encode([
            'mensaje' => 'Localidad encontrada',
            'datos' => $resultado
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode([
            'error' => 'No se encontró la localidad con ese código postal'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

//PUT
$app->put('/localidades/{codigo_postal}', function (Request $request, Response $response, $args) use ($pdo) {
    $codigo_postal = $args['codigo_postal'];
    $datos = $request->getParsedBody();

    if (empty($datos['provincia']) || empty($datos['nombre_localidad'])) {
        $response->getBody()->write(json_encode([
            'error' => 'Los campos provincia y nombre_localidad son requeridos'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $localidades = new Localidades($pdo);
    $ok = $localidades->actualizarLocalidad(
        $codigo_postal,
        $datos['provincia'],
        $datos['nombre_localidad']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Localidad actualizada correctamente' : 'Error al actualizar la localidad',
        'codigo_postal' => $codigo_postal,
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});

//DELETE
$app->delete('/localidades/{codigo_postal}', function (Request $request, Response $response, $args) use ($pdo) {
    $codigo_postal = $args['codigo_postal'];
    $localidad = new Localidades($pdo);
    $ok = $localidad->eliminarLocalidad($codigo_postal);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Localidad eliminada correctamente' : 'Error al eliminar la localidad',
        'codigo_postal' => $codigo_postal
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});


//USUARIO

//POST
$app->post('/usuarios', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();

    $camposRequeridos = ['nombre_usuario', 'apellido', 'email', 'contrasena_hash', 'direccion', 'telefono', 'codigo_postal', 'fecha_registro', 'id_rol'];
    foreach ($camposRequeridos as $campo) {
        if (empty($datos[$campo])) {
            $response->getBody()->write(json_encode(['error' => "El campo $campo es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $stmt = $pdo->prepare("SELECT 1 FROM localidades WHERE codigo_postal = ?");
    $stmt->execute([$datos['codigo_postal']]);
    if (!$stmt->fetchColumn()) {
        $response->getBody()->write(json_encode(['error' => 'El código postal no existe en localidades']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

     $hash = password_hash($datos['contrasena_hash'], PASSWORD_DEFAULT);

    $usuario = new Usuarios(
        $pdo,
        $datos['nombre_usuario'],
        $datos['apellido'],
        $datos['email'],
        $hash,
        $datos['direccion'],
        $datos['telefono'],
        $datos['codigo_postal'],
        $datos['cuenta_verificada'] ?? false,
        $datos['fecha_registro'],
        $datos['id_rol']
    );

    $ok = $usuario->crearUsuario(
        $datos['nombre_usuario'],
        $datos['apellido'],
        $datos['email'],
        $hash,
        $datos['direccion'],
        $datos['telefono'],
        $datos['codigo_postal'],
        $datos['cuenta_verificada'] ?? false,
        $datos['fecha_registro'],
        $datos['id_rol']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Usuario creado exitosamente' : 'Error al crear el usuario',
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
});

//GET
$app->get('/usuarios', function (Request $request, Response $response) use ($pdo) {
    $usuarios = new Usuarios($pdo);
    $resultado = $usuarios->traerUsuarios();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de usuarios',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


//PUT
$app->put('/usuarios/{id_usuario}', function ($request, $response, $args) use ($pdo) {
    $id_usuario = $args['id_usuario'];
    $data = $request->getParsedBody();

    $usuarios = new Usuarios($pdo);

    //validar y asignar id_rol
    $id_rol = $data['id_rol'] ?? null;
    if ($id_rol === null) {
        $response->getBody()->write(json_encode([
            "error" => "id_rol es obligatorio"
        ]));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(400);
    }

    //verificar que id_rol exista 
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rol WHERE id_rol = ?");
    $stmt->execute([$id_rol]);
    if ($stmt->fetchColumn() == 0) {
        $response->getBody()->write(json_encode([
            "error" => "id_rol no existe"
        ]));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(400);
    }

    //hashear contraseña
    $contrasena = $data['contrasena_hash'] ?? null;
    $contrasena_hash = $contrasena ? password_hash($contrasena, PASSWORD_DEFAULT) : null;

    $resultado = $usuarios->actualizarUsuario(
        $id_usuario,
        $data['nombre_usuario'] ?? null,
        $data['apellido'] ?? null,
        $data['email'] ?? null,
        $contrasena_hash,
        $data['direccion'] ?? null,
        $data['telefono'] ?? null,
        $data['codigo_postal'] ?? null,
        $data['cuenta_verificada'] ?? 0,
        $data['fecha_registro'] ?? date('Y-m-d H:i:s'),
        $id_rol
    );

    $response->getBody()->write(json_encode(
        $resultado 
            ? ["mensaje" => "Usuario actualizado correctamente"] 
            : ["error" => "No se pudo actualizar el usuario"]
    ));

    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($resultado ? 200 : 400);
});



//DELETE
$app->delete('/usuarios/{id_usuario}', function ($request, $response, $args) use ($pdo) {
    $id_usuario = $args['id_usuario'];

    $usuarios = new Usuarios($pdo);

    $resultado = $usuarios->eliminarUsuario($id_usuario);

    $response->getBody()->write(json_encode(
        $resultado 
            ? ["mensaje" => "Usuario eliminado correctamente"] 
            : ["error" => "No se pudo eliminar el usuario"]
    ));

    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($resultado ? 200 : 400);
});

$app->run();

?>