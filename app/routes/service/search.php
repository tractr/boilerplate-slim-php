<?php 
/**
 * Read service.
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator as Validator;

$app->get('/service', function (Request $request, Response $response, array $args) {

 
    $_get = $request->getQueryParams();

    $validator = new Validator($_get);
    $validator->rules(array(
        'required' => array(
            array('_page', true),
            array('_limit', true),
        ),
        'min' => array(
            array('_limit', 1), 
            array('_page', 0)
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

        $page = $_get['_page'] != null ? $_get['_page'] : 0;
        $limit = $_get['_limit'] != null ? $_get['_limit'] : 10;
        $order = 'desc';
        $sort =  'created_at';
        $skip = $page * $limit;

        if (isset($_get['_sort']) && in_array($_get['sort'], array('_id', 'created_at', 'name', ))) {
            $sort = $_get['_sort'];
        }

        if (isset($_get['_order']) && in_array($_get['_order'], array('asc', 'desc'))) {
            $order = $_get['_order'];
        }

        if ($sort == '' || $order == '') {
            // string wrong formed
            return $response->withStatus(400);
        }

        $model = new App\Models\Service();
        $query = App\Models\Service::get_cursor($model, $_get, $this->get('credential'), request_from_admin($request));
        $total = $query->count();
        $data_list = $query->orderBy($sort, $order)
                       ->skip($skip)
                       ->take($limit)
                       ->get();

        //populate relation_ship
        $result = array();
        foreach($data_list as $data)
        {
            //clone data into array
            $adr = json_encode($data);
            $adr = json_decode($adr, true);
            //populate relationship

            array_push($result, $adr);
        }

        $data = array(
            'page' => $page ,
            'limit' => $limit,
            'count' => count($result),
            'total' => $total,
            'items' => $result
        );

        $payload = json_encode($data);

        $response->getBody()->write($payload);
        return $response
                  ->withHeader('Content-Type', 'application/json')
                  ->withStatus(200);
    }

    // string wrong formed
    return $response->withStatus(400);
});