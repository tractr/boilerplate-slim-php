<?php 

/**
 * Count service.
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/service/count', function (Request $request, Response $response, array $args) {
    global $request_from_admin;

    $_get = $request->getQueryParams();

    $validator = new Validator($_get);
    $validator->rule('required', '_page', true);
    $validator->rule('required', '_limit', true);

    $validator->rules(array(
        'min' => array(
            array('_limit', 1), 
            array('_page', 1)
        ),
        'max' => array(
            array('_limit', 100)
        ) 
    ));

    // forbiden keys
    unset($_get['_id']);
    unset($_get['created_at']);
    unset($_get['description']);

    if ($validator->validate()) {

        $model = new App\Models\Service();
        $query = App\Models\Service::get_cursor($model, $_get, $this->get('credential'), $request_from_admin($request));

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