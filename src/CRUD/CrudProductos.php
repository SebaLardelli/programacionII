<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Modelos\Productos;

//POST
$app->post('/productos', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();

    $campos = ['nombre_p', 'descripcion_p', 'precio', 'stock', 'id_estado_p', 'tamaño', 'id_categoria', 'imagen_url'];
    foreach ($campos as $campo) {
        if (empty($datos[$campo]) && $datos[$campo] !== "0") {
            $response->getBody()->write(json_encode(['error' => "El campo $campo es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $producto = new Productos($pdo, 0);
    $ok = $producto->crearProducto(
        $datos['nombre_p'],
        $datos['descripcion_p'],
        (float)$datos['precio'],
        (int)$datos['stock'],
        (int)$datos['id_estado_p'],
        $datos['tamaño'],
        (int)$datos['id_categoria'],
        $datos['imagen_url']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Producto creado correctamente' : 'Error al crear el producto',
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
});

//GET
$app->get('/productos', function (Request $request, Response $response) use ($pdo) {
    $producto = new Productos($pdo, 0);
    $resultado = $producto->traerProductos();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de productos',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


$app->get('/productos/{id_producto}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_producto = (int)$args['id_producto'];
    $producto = new Productos($pdo, $id_producto);
    $resultado = $producto->traerProductoPorId($id_producto);

    if ($resultado) {
        $response->getBody()->write(json_encode([
            'mensaje' => 'Producto encontrado',
            'datos' => $resultado
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode([
            'error' => 'No se encontró el producto con ese ID'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

//PUT
$app->put('/productos/{id_producto}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_producto = (int)$args['id_producto'];
    $datos = $request->getParsedBody();

    $campos = ['nombre_p', 'descripcion_p', 'precio', 'stock', 'id_estado_p', 'tamaño', 'id_categoria', 'imagen_url'];
    foreach ($campos as $campo) {
        if (!isset($datos[$campo])) {
            $response->getBody()->write(json_encode(['error' => "El campo $campo es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $producto = new Productos($pdo, $id_producto);
    $ok = $producto->actualizarProducto(
        $id_producto,
        $datos['nombre_p'],
        $datos['descripcion_p'],
        (float)$datos['precio'],
        (int)$datos['stock'],
        (int)$datos['id_estado_p'],
        $datos['tamaño'],
        (int)$datos['id_categoria'],
        $datos['imagen_url']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Producto actualizado correctamente' : 'Error al actualizar el producto',
        'id_producto' => $id_producto,
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});

//DELETE
$app->delete('/productos/{id_producto}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_producto = (int)$args['id_producto'];
    $producto = new Productos($pdo, $id_producto);
    $ok = $producto->eliminarProducto($id_producto);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Producto eliminado correctamente' : 'Error al eliminar el producto',
        'id_producto' => $id_producto
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});

?>