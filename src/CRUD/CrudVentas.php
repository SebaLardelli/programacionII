<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Modelos\Ventas;

//POST
$app->post('/ventas', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();

    $campos = [
        'id_usuario', 'id_carrito', 'fecha_venta', 'id_metodo_pago',
        'importe_total', 'id_estado_v', 'id_punto_retiro', 'id_estado_p'
    ];
    foreach ($campos as $campo) {
        if (!isset($datos[$campo])) {
            $response->getBody()->write(json_encode(['error' => "El campo $campo es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $venta = new Ventas($pdo);
    $ok = $venta->crearVenta(
        (int)$datos['id_usuario'],
        (int)$datos['id_carrito'],
        $datos['fecha_venta'],
        (int)$datos['id_metodo_pago'],
        (float)$datos['importe_total'],
        (int)$datos['id_estado_v'],
        (int)$datos['id_punto_retiro'],
        (int)$datos['id_estado_p']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Venta creada correctamente' : 'Error al crear la venta',
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
});

//GET
$app->get('/ventas', function (Request $request, Response $response) use ($pdo) {
    $venta = new Ventas($pdo);
    $resultado = $venta->traerVentas();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de ventas',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/ventas/{id_venta}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_venta = (int)$args['id_venta'];
    $venta = new Ventas($pdo);
    $resultado = $venta->traerVentaPorId($id_venta);

    if ($resultado) {
        $response->getBody()->write(json_encode([
            'mensaje' => 'Venta encontrada',
            'datos' => $resultado
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode([
            'error' => 'No se encontró la venta con ese ID'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

//PUT
$app->put('/ventas/{id_venta}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_venta = (int)$args['id_venta'];
    $datos = $request->getParsedBody();

    $campos = [
        'id_usuario', 'id_carrito', 'fecha_venta', 'id_metodo_pago',
        'importe_total', 'id_estado_v', 'id_punto_retiro', 'id_estado_p'
    ];
    foreach ($campos as $campo) {
        if (!isset($datos[$campo])) {
            $response->getBody()->write(json_encode(['error' => "El campo $campo es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $venta = new Ventas($pdo);
    $ok = $venta->actualizarVenta(
        $id_venta,
        (int)$datos['id_usuario'],
        (int)$datos['id_carrito'],
        $datos['fecha_venta'],
        (int)$datos['id_metodo_pago'],
        (float)$datos['importe_total'],
        (int)$datos['id_estado_v'],
        (int)$datos['id_punto_retiro'],
        (int)$datos['id_estado_p']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Venta actualizada correctamente' : 'Error al actualizar la venta',
        'id_venta' => $id_venta,
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});

//DELETE
$app->delete('/ventas/{id_venta}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_venta = (int)$args['id_venta'];
    $venta = new Ventas($pdo);
    $ok = $venta->eliminarVenta($id_venta);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Venta eliminada correctamente' : 'Error al eliminar la venta',
        'id_venta' => $id_venta
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});

?>