<?php 

/**
 * Read service.
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator as Validator;

$app->get('/service/count', function (Request $request, Response $response, array $args) {

    $_get = $request->getQueryParams();

    $model = new App\Models\Service();
    $query = App\Models\Service::get_cursor($model, $_get);

    $total = $query->count();
    
    $data = array(
        'total' => $total,
    );

    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(200);
});