<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Modelos\Categorias;

//POST
$app->post('/categorias', function (Request $request, Response $response) use ($pdo) {
    $datos = $request->getParsedBody();

    if (empty($datos['nombre_c']) || empty($datos['descripcion_c'])) {
        $response->getBody()->write(json_encode(['error' => 'Todos los campos son requeridos']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $categoria = new Categorias($pdo);
    $ok = $categoria->crearCategoria($datos['nombre_c'], $datos['descripcion_c']);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Categoría creada correctamente' : 'Error al crear la categoría',
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 201 : 500);
});

//GET
$app->get('/categorias', function (Request $request, Response $response) use ($pdo) {
    $categoria = new Categorias($pdo);
    $resultado = $categoria->traerCategorias();

    $response->getBody()->write(json_encode([
        'mensaje' => 'Listado de categorías',
        'datos' => $resultado
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

//PUT
$app->put('/categorias/{id_categoria}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_categoria = (int)$args['id_categoria'];
    $datos = $request->getParsedBody();

    if (empty($datos['nombre_c']) || empty($datos['descripcion_c'])) {
        $response->getBody()->write(json_encode(['error' => 'Todos los campos son requeridos']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $categoria = new Categorias($pdo);
    $ok = $categoria->actualizarCategoria($id_categoria, $datos['nombre_c'], $datos['descripcion_c']);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Categoría actualizada correctamente' : 'Error al actualizar la categoría',
        'id_categoria' => $id_categoria,
        'datos_recibidos' => $datos
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});

//DELETE
$app->delete('/categorias/{id_categoria}', function (Request $request, Response $response, $args) use ($pdo) {
    $id_categoria = (int)$args['id_categoria'];
    $categoria = new Categorias($pdo);
    $ok = $categoria->eliminarCategoria($id_categoria);

    $response->getBody()->write(json_encode([
        'mensaje' => $ok ? 'Categoría eliminada correctamente' : 'Error al eliminar la categoría',
        'id_categoria' => $id_categoria
    ]));
    return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus($ok ? 200 : 500);
});

?>