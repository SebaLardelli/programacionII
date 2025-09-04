<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Modelos\Localidades;

//POST
$app->post('/Crearlocalidad', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();


    if (empty($datos['codigo_postal']) || empty($datos['provincia']) || empty($datos['nombre_localidad'])) {
        $response->getBody()->write(json_encode(['error' => 'Todos los campos son requeridos']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $localidad = new Localidades($pdo);
    $ok = $localidad->crearLocalidad(
        $datos['codigo_postal'],
        $datos['provincia'],
        $datos['nombre_localidad']
    );

    $respuesta = [
        'mensaje' => $ok ? 'Localidad creada correctamente' : 'Error al crear la localidad',
        'datos_recibidos' => $datos
    ];

    $response->getBody()->write(json_encode($respuesta));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
});


//GET
$app->get('/Traerlocalidades', function (Request $request, Response $response) use ($pdo) {
    $localidades = new Localidades($pdo);
    $resultado = $localidades->traerLocalidades();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de localidades',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
})->add(new RoleMiddleware([1]));

$app->get('/TraerlocalidadesCP/{codigo_postal}', function (Request $request, Response $response, $args) use ($pdo) {
    $codigo_postal = $args['codigo_postal'];
    $localidades = new Localidades($pdo);
    $resultado = $localidades->traerLocalidadPorCP($codigo_postal);

    if ($resultado) {
        $response->getBody()->write(json_encode([
            'mensaje' => 'Localidad encontrada',
            'datos' => $resultado
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode([
            'error' => 'No se encontró la localidad con ese código postal'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
})->add(new RoleMiddleware([1]));

//PUT
$app->put('/Actualizarlocalidad/{codigo_postal}', function (Request $request, Response $response, $args) use ($pdo) {
    $codigo_postal = $args['codigo_postal'];
    $datos = $request->getParsedBody();

    if (empty($datos['provincia']) || empty($datos['nombre_localidad'])) {
        $response->getBody()->write(json_encode([
            'error' => 'Los campos provincia y nombre_localidad son requeridos'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $localidades = new Localidades($pdo);
    $ok = $localidades->actualizarLocalidad(
        $codigo_postal,
        $datos['provincia'],
        $datos['nombre_localidad']
    );

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Localidad actualizada correctamente' : 'Error al actualizar la localidad',
        'codigo_postal' => $codigo_postal,
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
})->add(new RoleMiddleware([1]));

//DELETE
$app->delete('/Eliminarlocalidad/{codigo_postal}', function (Request $request, Response $response, $args) use ($pdo) {
    $codigo_postal = $args['codigo_postal'];
    $localidad = new Localidades($pdo);
    $ok = $localidad->eliminarLocalidad($codigo_postal);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Localidad eliminada correctamente' : 'Error al eliminar la localidad',
        'codigo_postal' => $codigo_postal
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
})->add(new RoleMiddleware([1]));

?>