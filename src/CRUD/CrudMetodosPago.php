<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Modelos\MetodosPago;

//POST
$app->post('/metodospago', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();

    if (empty($datos['descripcion_mp'])) {
        $response->getBody()->write(json_encode(['error' => 'El campo descripcion_mp es requerido']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $metodo = new MetodosPago($pdo);
    $ok = $metodo->crearMetodoPago($datos['descripcion_mp']);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Método de pago creado correctamente' : 'Error al crear el método de pago',
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
});

//GET
$app->get('/metodospago', function (Request $request, Response $response) use ($pdo) {
    $metodo = new MetodosPago($pdo);
    $resultado = $metodo->traerMetodosPago();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de métodos de pago',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


$app->get('/metodospago/{id_metodo_pago}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_metodo_pago = (int)$args['id_metodo_pago'];
    $metodo = new MetodosPago($pdo);
    $resultado = $metodo->traerMetodoPagoPorId($id_metodo_pago);

    if ($resultado) {
        $response->getBody()->write(json_encode([
            'mensaje' => 'Método de pago encontrado',
            'datos' => $resultado
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode([
            'error' => 'No se encontró el método de pago con ese ID'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

//PUT
$app->put('/metodospago/{id_metodo_pago}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_metodo_pago = (int)$args['id_metodo_pago'];
    $datos = $request->getParsedBody();

    if (empty($datos['descripcion_mp'])) {
        $response->getBody()->write(json_encode(['error' => 'El campo descripcion_mp es requerido']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $metodo = new MetodosPago($pdo);
    $ok = $metodo->actualizarMetodoPago($id_metodo_pago, $datos['descripcion_mp']);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Método de pago actualizado correctamente' : 'Error al actualizar el método de pago',
        'id_metodo_pago' => $id_metodo_pago,
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});

//DELETE
$app->delete('/metodospago/{id_metodo_pago}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_metodo_pago = (int)$args['id_metodo_pago'];
    $metodo = new MetodosPago($pdo);
    $ok = $metodo->eliminarMetodoPago($id_metodo_pago);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Método de pago eliminado correctamente' : 'Error al eliminar el método de pago',
        'id_metodo_pago' => $id_metodo_pago
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});

?>