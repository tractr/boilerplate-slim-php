<?php 

/**
 * Read service.
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator as Validator;

$app->get('/service', function (Request $request, Response $response, array $args) {

    $_get = $request->getQueryParams();

    $page = isset($_get['_page']) ? $_get['_page'] : 0;
    $limit = isset($_get['_limit']) ? $_get['_limit'] : 10;
    $order = isset($_get['_order']) ? $_get['_order'] : 'created_at';
    $sort = isset($_get['_sort']) ? $_get['_sort'] : 'asc';
    $skip = $page * $limit;

    //Todo : éliminer les case non recherchable

    $model = new App\Models\Service();
    $query = App\Models\Service::get_cursor($model, $_get);

    $data_list = $query->orderBy($order, $sort)
                   ->skip($skip)
                   ->take($limit)
                   ->get();

    $total = $query->count();

    $data = array(
        'page' => $page ,
        'limit' => $limit,
        'count' => count($data_list),
        'total' => $total,
        'items' => $data_list
    );

    $payload = json_encode($data);

    $response->getBody()->write($payload);
    return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(200);
});