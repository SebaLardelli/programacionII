<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Modelos\Tematica;

//PUT
$app->post('/tematicas', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();

    if (empty($datos['id_categoria']) || empty($datos['nombre_t']) || empty($datos['descripcion_t'])) {
        $response->getBody()->write(json_encode(['error' => 'Todos los campos son requeridos']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $tematica = new Tematica($pdo);
    $ok = $tematica->crearTematica(
        $datos['id_categoria'],
        $datos['nombre_t'],
        $datos['descripcion_t']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Temática creada correctamente' : 'Error al crear la temática',
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
});

//GET
$app->get('/tematicas', function (Request $request, Response $response) use ($pdo) {
    $tematica = new Tematica($pdo);
    $resultado = $tematica->traerTematicas();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de temáticas',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


$app->get('/tematicas/{id_tematica}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_tematica = $args['id_tematica'];
    $tematica = new Tematica($pdo);
    $resultado = $tematica->traerTematicaPorId($id_tematica);

    if ($resultado) {
        $response->getBody()->write(json_encode([
            'mensaje' => 'Temática encontrada',
            'datos' => $resultado
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode([
            'error' => 'No se encontró la temática con ese ID'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

//PUT
$app->put('/tematicas/{id_tematica}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_tematica = $args['id_tematica'];
    $datos = $request->getParsedBody();

    if (empty($datos['id_categoria']) || empty($datos['nombre_t']) || empty($datos['descripcion_t'])) {
        $response->getBody()->write(json_encode(['error' => 'Todos los campos son requeridos']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $tematica = new Tematica($pdo);
    $ok = $tematica->actualizarTematica(
        $id_tematica,
        $datos['id_categoria'],
        $datos['nombre_t'],
        $datos['descripcion_t']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Temática actualizada correctamente' : 'Error al actualizar la temática',
        'id_tematica' => $id_tematica,
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});

//DELETE
$app->delete('/tematicas/{id_tematica}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_tematica = $args['id_tematica'];
    $tematica = new Tematica($pdo);
    $ok = $tematica->eliminarTematica($id_tematica);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Temática eliminada correctamente' : 'Error al eliminar la temática',
        'id_tematica' => $id_tematica
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});

?>