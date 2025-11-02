<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;

$app->post('/login', function (Request $request, Response $response) use ($pdo, $key) {
    $authHeader = $request->getHeaderLine('Authorization');

    // Validar que venga el header de Authorization con Basic Auth
    if (!$authHeader || stripos($authHeader, 'Basic ') !== 0) {
        $response->getBody()->write(json_encode([
            "error" => "Se requiere autenticación básica",
            "message" => "Debe enviar el header Authorization con formato: Basic base64(email:password)"
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Decodificar las credenciales Basic Auth
    $decoded = base64_decode(substr($authHeader, 6));
    $parts = explode(':', $decoded, 2);
    
    if (count($parts) !== 2) {
        $response->getBody()->write(json_encode([
            "error" => "Formato de autenticación inválido",
            "message" => "El formato debe ser email:password codificado en base64"
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    [$email, $password] = $parts;

    // Validar que email y password no estén vacíos
    if (empty($email) || empty($password)) {
        $response->getBody()->write(json_encode([
            "error" => "Credenciales incompletas",
            "message" => "Email y contraseña son requeridos"
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Buscar el usuario en la base de datos
    $stmt = $pdo->prepare("SELECT id_usuario, id_rol, contrasena_hash, nombre_usuario, apellido FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validar credenciales
    if (!$usuario || !password_verify($password, $usuario['contrasena_hash'])) {
        $response->getBody()->write(json_encode([
            "error" => "Credenciales inválidas",
            "message" => "Email o contraseña incorrectos"
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Generar el token JWT
    $now = time();
    $exp = $now + 3600; // Token válido por 1 hora

    $payload = [
        "iat" => $now,
        "exp" => $exp,
        "data" => [
            "id_usuario" => (int)$usuario['id_usuario'],
            "email"      => $email,
            "id_rol"     => (int)$usuario['id_rol'],
            "nombre"     => $usuario['nombre_usuario'],
            "apellido"   => $usuario['apellido']
        ]
    ];

    try {
        $token = JWT::encode($payload, $key, 'HS256');

        $response->getBody()->write(json_encode([
            "token" => $token
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (\Exception $e) {
        $response->getBody()->write(json_encode([
            "error" => "Error al generar el token",
            "message" => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

?>

