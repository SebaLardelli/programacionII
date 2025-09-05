<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Modelos\DetalleVentas;

//POST  
$app->post('/CrearDetalleVenta', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();

    $camposRequeridos = ['id_venta', 'id_carrito', 'id_producto', 'cantidad', 'precio_unitario'];
    foreach ($camposRequeridos as $campo) {
        if (empty($datos[$campo]) && $datos[$campo] !== "0") {
            $response->getBody()->write(json_encode(['error' => "El campo $campo es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    // Validar FKs
    $stmt = $pdo->prepare("SELECT 1 FROM ventas WHERE id_venta = ?");
    $stmt->execute([(int)$datos['id_venta']]);
    if (!$stmt->fetchColumn()) {
        $response->getBody()->write(json_encode(['error' => 'id_venta no existe en ventas']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("SELECT 1 FROM carrito WHERE id_carrito = ?");
    $stmt->execute([(int)$datos['id_carrito']]);
    if (!$stmt->fetchColumn()) {
        $response->getBody()->write(json_encode(['error' => 'id_carrito no existe en carrito']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("SELECT 1 FROM producto WHERE id_producto = ?");
    $stmt->execute([(int)$datos['id_producto']]);
    if (!$stmt->fetchColumn()) {
        $response->getBody()->write(json_encode(['error' => 'id_producto no existe en producto']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }


    $id_venta        = (int)$datos['id_venta'];
    $id_carrito      = (int)$datos['id_carrito'];
    $id_producto     = (int)$datos['id_producto'];
    $cantidad        = (int)$datos['cantidad'];
    $precio_unitario = (float)$datos['precio_unitario'];
    $importe_total_detalle = isset($datos['importe_total_detalle'])
        ? (float)$datos['importe_total_detalle']
        : (float)($cantidad * $precio_unitario);


    $detalle = new DetalleVentas(
        $pdo,
        0, 
        $id_venta,
        $id_carrito,
        $precio_unitario,
        $importe_total_detalle,
        $id_producto,
        $cantidad
    );

    $ok = $detalle->crearDetalleVenta(
        $id_venta,
        $id_carrito,
        $precio_unitario,
        $importe_total_detalle,
        $id_producto,
        $cantidad
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Detalle de venta creado exitosamente' : 'Error al crear el detalle de venta',
        'datos_recibidos' => $datos,
        'importe_total_detalle' => $importe_total_detalle
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
});

//GET
$app->get('/TraerDetalleVentas', function (Request $request, Response $response) use ($pdo) {
    $modelo = new DetalleVentas($pdo, 0, 0, 0, 0.0, 0.0, 0, 0);
    $resultado = $modelo->traerDetalleVentas();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de detalles de ventas',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/TraerDetalleVenta/{id_fila}', function (Request $request, Response $response, array $args) use ($pdo) {
    $id = (int)$args['id_fila'];

    $modelo = new DetalleVentas($pdo, 0, 0, 0, 0.0, 0.0, 0, 0);
    $detalle = $modelo->traerDetalleVentaPorId($id);

    if (!$detalle) {
        $response->getBody()->write(json_encode(['error' => 'Detalle de venta no encontrado']));
        return $response->withHeader('Content-Type','application/json')->withStatus(404);
    }

    $response->getBody()->write(json_encode(['datos' => $detalle]));
    return $response->withHeader('Content-Type','application/json')->withStatus(200);
})->add(new RoleMiddleware([1]));

//PUT
$app->put('/ActualizarDetalleVenta/{id_fila}', function ($request, $response, $args) use ($pdo) {
    $id_fila = (int)$args['id_fila'];
    $data = $request->getParsedBody();

    $requeridos = ['id_venta', 'id_carrito', 'id_producto', 'cantidad', 'precio_unitario'];
    foreach ($requeridos as $campo) {
        if (!isset($data[$campo])) {
            $response->getBody()->write(json_encode(["error" => "$campo es obligatorio"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    // FKs
    $stmt = $pdo->prepare("SELECT 1 FROM ventas WHERE id_venta = ?");
    $stmt->execute([(int)$data['id_venta']]);
    if (!$stmt->fetchColumn()) {
        $response->getBody()->write(json_encode(["error" => "id_venta no existe"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
    $stmt = $pdo->prepare("SELECT 1 FROM carrito WHERE id_carrito = ?");
    $stmt->execute([(int)$data['id_carrito']]);
    if (!$stmt->fetchColumn()) {
        $response->getBody()->write(json_encode(["error" => "id_carrito no existe"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
    $stmt = $pdo->prepare("SELECT 1 FROM producto WHERE id_producto = ?");
    $stmt->execute([(int)$data['id_producto']]);
    if (!$stmt->fetchColumn()) {
        $response->getBody()->write(json_encode(["error" => "id_producto no existe"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $id_venta        = (int)$data['id_venta'];
    $id_carrito      = (int)$data['id_carrito'];
    $id_producto     = (int)$data['id_producto'];
    $cantidad        = (int)$data['cantidad'];
    $precio_unitario = (float)$data['precio_unitario'];
    $importe_total_detalle = isset($data['importe_total_detalle'])
        ? (float)$data['importe_total_detalle']
        : (float)($cantidad * $precio_unitario);

    $modelo = new DetalleVentas($pdo, 0, 0, 0, 0.0, 0.0, 0, 0);
    $resultado = $modelo->actualizarDetalleVenta(
        $id_fila,
        $id_venta,
        $id_carrito,
        $precio_unitario,
        $importe_total_detalle,
        $id_producto,
        $cantidad
    );

    $response->getBody()->write(json_encode(
        $resultado 
            ? ["mensaje" => "Detalle de venta actualizado correctamente"] 
            : ["error" => "No se pudo actualizar el detalle de venta"]
    ));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($resultado ? 200 : 400);
});

//DELETE
$app->delete('/EliminarDetalleVenta/{id_fila}', function ($request, $response, $args) use ($pdo) {
    $id_fila = (int)$args['id_fila'];

    $modelo = new DetalleVentas($pdo, 0, 0, 0, 0.0, 0.0, 0, 0);
    $resultado = $modelo->eliminarDetalleVenta($id_fila);

    $response->getBody()->write(json_encode(
        $resultado 
            ? ["mensaje" => "Detalle de venta eliminado correctamente"] 
            : ["error" => "No se pudo eliminar el detalle de venta"]
    ));

    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($resultado ? 200 : 400);
});

?>
