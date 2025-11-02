<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Modelos\Carrito;

// POST
$app->post('/CrearCarrito', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();

    // campos requeridos
    $camposRequeridos = ['id_usuario', 'importe_total', 'id_estado_car', 'fecha_creacion'];
    foreach ($camposRequeridos as $campo) {
        if (empty($datos[$campo]) && $datos[$campo] !== "0") {
            $response->getBody()->write(json_encode(['error' => "El campo $campo es requerido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    // Validar FK: usuario
    $stmt = $pdo->prepare("SELECT 1 FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([(int)$datos['id_usuario']]);
    if (!$stmt->fetchColumn()) {
        $response->getBody()->write(json_encode(['error' => 'id_usuario no existe en usuarios']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }


    $ahora = date('Y-m-d H:i:s');

    $carrito = new Carrito(
        $pdo,
        (int)$datos['id_usuario'],
        $datos['fecha_creacion'],
        $datos['fecha_ultima_actualizacion'] ?? $ahora,
        (float)$datos['importe_total'],
        (int)$datos['id_estado_car'],
        null
    );

    $ok = $carrito->crearCarrito(
        (int)$datos['id_usuario'],
        $datos['fecha_creacion'],
        $datos['fecha_ultima_actualizacion'] ?? $ahora,
        (float)$datos['importe_total'],
        (int)$datos['id_estado_car']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Carrito creado exitosamente' : 'Error al crear el carrito',
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
});

// GET: todos
$app->get('/TraerCarritos', function (Request $request, Response $response) use ($pdo) {
    $carritos = new Carrito($pdo);
    $resultado = $carritos->traerCarritos();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de carritos',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

// GET: por id
$app->get('/TraerCarrito/{id_carrito}', function (Request $request, Response $response, array $args) use ($pdo) {
    $id = (int)$args['id_carrito'];

    $modelo = new Carrito($pdo);
    $carrito = $modelo->traerCarritoPorId($id);

    if (!$carrito) {
        $response->getBody()->write(json_encode(['error' => 'Carrito no encontrado']));
        return $response->withHeader('Content-Type','application/json')->withStatus(404);
    }

    $response->getBody()->write(json_encode(['datos' => $carrito]));
    return $response->withHeader('Content-Type','application/json')->withStatus(200);
})->add(new RoleMiddleware([1])); // Igual que en Usuarios

// PUT
$app->put('/ActualizarCarrito/{id_carrito}', function ($request, $response, $args) use ($pdo) {
    $id_carrito = (int)$args['id_carrito'];
    $data = $request->getParsedBody();

    $carritos = new Carrito($pdo);

    // Requerir id_estado_car (análogamente a id_rol en Usuarios)
    $id_estado_car = $data['id_estado_car'] ?? null;
    if ($id_estado_car === null) {
        $response->getBody()->write(json_encode([
            "error" => "id_estado_car es obligatorio"
        ]));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(400);
    }

    // Validar que el carrito exista
    $stmt = $pdo->prepare("SELECT * FROM carrito WHERE id_carrito = ?");
    $stmt->execute([$id_carrito]);
    $actual = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$actual) {
        $response->getBody()->write(json_encode(["error" => "Carrito no existe"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }



    // Validar FK usuario si llega
    if (isset($data['id_usuario'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([(int)$data['id_usuario']]);
        if ($stmt->fetchColumn() == 0) {
            $response->getBody()->write(json_encode([
                "error" => "id_usuario no existe"
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    // Construir params finales (respetando fechas como en Usuarios)
    $id_usuario = isset($data['id_usuario']) ? (int)$data['id_usuario'] : (int)$actual['id_usuario'];
    $fecha_creacion = $actual['fecha_creacion']; // no se toca
    $fecha_ultima_actualizacion = $data['fecha_ultima_actualizacion'] ?? date('Y-m-d H:i:s');
    $importe_total = isset($data['importe_total']) ? (float)$data['importe_total'] : (float)$actual['importe_total'];

    $resultado = $carritos->actualizarCarrito(
        $id_carrito,
        $id_usuario,
        $fecha_creacion,
        $fecha_ultima_actualizacion,
        $importe_total,
        (int)$id_estado_car
    );

    $response->getBody()->write(json_encode(
        $resultado
            ? ["mensaje" => "Carrito actualizado correctamente"]
            : ["error" => "No se pudo actualizar el carrito"]
    ));

    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($resultado ? 200 : 400);
});

// DELETE
$app->delete('/EliminarCarrito/{id_carrito}', function ($request, $response, $args) use ($pdo) {
    $id_carrito = (int)$args['id_carrito'];

    $carritos = new Carrito($pdo);

    $resultado = $carritos->eliminarCarrito($id_carrito);

    $response->getBody()->write(json_encode(
        $resultado
            ? ["mensaje" => "Carrito eliminado correctamente"]
            : ["error" => "No se pudo eliminar el carrito"]
    ));

    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($resultado ? 200 : 400);
});

?>