<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Modelos\Usuarios;

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

?>