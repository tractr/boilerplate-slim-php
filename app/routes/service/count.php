<?php 

/**
 * Count service.
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator as Validator;

$app->get('/service/count', function (Request $request, Response $response, array $args) {

    $_get = $request->getQueryParams();

    $validator = new Validator($_get);

    // forbiden keys
    unset($_get['_id']);
    unset($_get['created_at']);
    unset($_get['description']);

    if ($validator->validate()) {

        $query = App\Models\service::get_cursor($_get, $this->get('credential'), request_from_admin($request));

        $total = $query->count();
        $data = array(
            'total' => $total,
        );

        $payload = json_encode($data);

        $response->getBody()->write($payload);
        return $response
                  ->withHeader('Content-Type', 'application/json')
                  ->withStatus(200);
    }

    // Request malformed
    return $response->withStatus(400);
});