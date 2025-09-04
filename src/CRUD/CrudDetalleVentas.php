<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Modelos\DetalleVentas;

//POST
$app->post('/CrearDetalleVenta', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();

    $campos = ['id_venta', 'id_carrito', 'producto', 'cantidad', 'precio_unitario'];
    foreach ($campos as $campo) {
        if (!isset($datos[$campo])) {
            $response->getBody()->write(json_encode(['error' => "El campo $campo es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $detalle = new DetalleVentas($pdo);
    $ok = $detalle->crearDetalleVenta(
        (int)$datos['id_venta'],
        (int)$datos['id_carrito'],
        $datos['producto'],
        (int)$datos['cantidad'],
        (float)$datos['precio_unitario']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Detalle de venta creado correctamente' : 'Error al crear el detalle de venta',
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
})->add(new RoleMiddleware([1,2]));

//GET
$app->get('/TraerDetalleVentas', function (Request $request, Response $response) use ($pdo) {
    $detalle = new DetalleVentas($pdo);
    $resultado = $detalle->traerDetallesVentas();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de detalles de ventas',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
})->add(new RoleMiddleware([1,2]));


$app->get('/TraerDetalleVenta/{id_fila}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_fila = (int)$args['id_fila'];
    $detalle = new DetalleVentas($pdo);
    $resultado = $detalle->traerDetalleVentaPorId($id_fila);

    if ($resultado) {
        $response->getBody()->write(json_encode([
            'mensaje' => 'Detalle de venta encontrado',
            'datos' => $resultado
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode([
            'error' => 'No se encontró el detalle de venta con ese ID'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

//PUT
$app->put('/ActualizarDetalleVenta/{id_fila}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_fila = (int)$args['id_fila'];
    $datos = $request->getParsedBody();

    $campos = ['id_venta', 'id_carrito', 'producto', 'cantidad', 'precio_unitario'];
    foreach ($campos as $campo) {
        if (!isset($datos[$campo])) {
            $response->getBody()->write(json_encode(['error' => "El campo $campo es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $detalle = new DetalleVentas($pdo);
    $ok = $detalle->actualizarDetalleVenta(
        $id_fila,
        (int)$datos['id_venta'],
        (int)$datos['id_carrito'],
        $datos['producto'],
        (int)$datos['cantidad'],
        (float)$datos['precio_unitario']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Detalle de venta actualizado correctamente' : 'Error al actualizar el detalle de venta',
        'id_fila' => $id_fila,
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
})->add(new RoleMiddleware([1]));

//DELETE
$app->delete('/EliinarDetalleVenta/{id_fila}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_fila = (int)$args['id_fila'];
    $detalle = new DetalleVentas($pdo);
    $ok = $detalle->eliminarDetalleVenta($id_fila);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Detalle de venta eliminado correctamente' : 'Error al eliminar el detalle de venta',
        'id_fila' => $id_fila
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
})->add(new RoleMiddleware([1]));

?>
