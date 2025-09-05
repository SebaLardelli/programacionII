<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Modelos\PuntosRetiro;

//POST
$app->post('/CrearPuntoRetiro', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();

    if (empty($datos['direccion']) || empty($datos['horarios']) || empty($datos['codigo_postal'])) {
        $response->getBody()->write(json_encode(['error' => 'Todos los campos son requeridos']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $punto = new PuntosRetiro($pdo);
    $ok = $punto->crearPuntoRetiro(
        $datos['direccion'],
        $datos['horarios'],
        $datos['codigo_postal']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Punto de retiro creado correctamente' : 'Error al crear el punto de retiro',
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
})->add(new RoleMiddleware([1]));

//GET
$app->get('/TraerPuntoRetiro', function (Request $request, Response $response) use ($pdo) {
    $punto = new PuntosRetiro($pdo);
    $resultado = $punto->traerPuntosRetiro();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de puntos de retiro',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
})->add(new RoleMiddleware([1,2]));

$app->get('/TraerPuntoRetiro/{id_punto_retiro}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_punto_retiro = (int)$args['id_punto_retiro'];
    $punto = new PuntosRetiro($pdo);
    $resultado = $punto->traerPuntoRetiroPorId($id_punto_retiro);

    if ($resultado) {
        $response->getBody()->write(json_encode([
            'mensaje' => 'Punto de retiro encontrado',
            'datos' => $resultado
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode([
            'error' => 'No se encontró el punto de retiro con ese ID'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
})->add(new RoleMiddleware([1,2]));

//PUT
$app->put('/ActualizarPuntoRetiro/{id_punto_retiro}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_punto_retiro = (int)$args['id_punto_retiro'];
    $datos = $request->getParsedBody();

    if (empty($datos['direccion']) || empty($datos['horarios']) || empty($datos['codigo_postal'])) {
        $response->getBody()->write(json_encode(['error' => 'Todos los campos son requeridos']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $punto = new PuntosRetiro($pdo);
    $ok = $punto->actualizarPuntoRetiro(
        $id_punto_retiro,
        $datos['direccion'],
        $datos['horarios'],
        $datos['codigo_postal']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Punto de retiro actualizado correctamente' : 'Error al actualizar el punto de retiro',
        'id_punto_retiro' => $id_punto_retiro,
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
})->add(new RoleMiddleware([1]));

//DELETE
$app->delete('/EliminarPuntoRetiro/{id_punto_retiro}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_punto_retiro = (int)$args['id_punto_retiro'];
    $punto = new PuntosRetiro($pdo);
    $ok = $punto->eliminarPuntoRetiro($id_punto_retiro);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Punto de retiro eliminado correctamente' : 'Error al eliminar el punto de retiro',
        'id_punto_retiro' => $id_punto_retiro
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
})->add(new RoleMiddleware([1]));

?>