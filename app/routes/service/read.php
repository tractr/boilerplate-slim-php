<?php 

/**
 * Read service.
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator as Validator;

$app->get('/service/{id}', function (Request $request, Response $response, array $args) {

    $data = App\Models\Service::find($args['id']);

    if ($data == null) {
    	// Entity was not found
    	return $response->withStatus(404);
    }

    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(201);
});