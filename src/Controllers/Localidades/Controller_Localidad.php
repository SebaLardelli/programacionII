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
$app->put('/Actualizarlocalidades/{codigo_postal}', function (Request $request, Response $response, $args) use ($pdo) {
    $codigo_postal = $args['codigo_postal'];
    $datos = $request->getParsedBody();

    if (empty($datos['provincia']) || empty($datos['nombre_localidad'])) {
        $response->getBody()->write(json_encode([
            'error' => 'Los campos provincia y nombre_localidad son requeridos'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Verificar que la localidad existe antes de actualizar
    $localidades = new Localidades($pdo);
    $localidadExistente = $localidades->traerLocalidadPorCP($codigo_postal);
    
    if (!$localidadExistente) {
        $response->getBody()->write(json_encode([
            'error' => 'No se encontró la localidad con ese código postal',
            'codigo_postal' => $codigo_postal
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $ok = $localidades->actualizarLocalidad(
        $codigo_postal,
        $datos['provincia'],
        $datos['nombre_localidad']
    );

    if ($ok) {
        // Obtener los datos actualizados para confirmar
        $localidadActualizada = $localidades->traerLocalidadPorCP($codigo_postal);
        $response->getBody()->write(json_encode([
            'mensaje' => 'Localidad actualizada correctamente',
            'codigo_postal' => $codigo_postal,
            'datos_actualizados' => $localidadActualizada
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode([
            'error' => 'Error al actualizar la localidad',
            'codigo_postal' => $codigo_postal
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
})->add(new RoleMiddleware([1]));

//DELETE
$app->delete('/Eliminarlocalidades/codigo_postal', function (Request $request, Response $response, $args) use ($pdo) {
    $codigo_postal = $args['codigo_postal'];
    
    // Verificar que la localidad existe antes de eliminar
    $localidad = new Localidades($pdo);
    $localidadExistente = $localidad->traerLocalidadPorCP($codigo_postal);
    
    if (!$localidadExistente) {
        $response->getBody()->write(json_encode([
            'error' => 'No se encontró la localidad con ese código postal',
            'codigo_postal' => $codigo_postal
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $ok = $localidad->eliminarLocalidad($codigo_postal);

    if ($ok) {
        $response->getBody()->write(json_encode([
            'mensaje' => 'Localidad eliminada correctamente',
            'codigo_postal' => $codigo_postal
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode([
            'error' => 'Error al eliminar la localidad',
            'codigo_postal' => $codigo_postal
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
})->add(new RoleMiddleware([1]));

?>