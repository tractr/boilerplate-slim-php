<?php 

/**
 * Delete Service.
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
$app->delete('/service/{id}', function (Request $request, Response $response, array $args) {
	
    global $check_auth;
    //Authentication
    $check_auth();

    $data = App\Models\Service::find($args['id']);

    if ($data == null) {
    	// Entity was not found
    	return $response->withStatus(404);
    }

    $data->delete();

    return $response->withStatus(204);
});