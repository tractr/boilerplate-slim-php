<?php 

/**
 * Read service.
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator as Validator;

$app->patch('/service/{id}', function (Request $request, Response $response, array $args) {

    $data = App\Models\Service::find($args['id']);

    if ($data == null) {
    	// Entity was not found
    	return $response->withStatus(404);
    }

    $_data = $request->getParsedBody();

    //Form validation
    $validator = new Validator($_data);
    $validator->rules(array(
        'required' => array(
            array('name'),
            array('description', true),//allow null
        )
    ));

    if ($validator->validate()) {

        try {

            $data->name = $data['name'];
            $data->description = $data['description'];
            $data->save();

            $payload = json_encode($data);
            $response->getBody()->write($payload);

            return $response->withStatus(204);

        } catch (\Exception $e) {
            if ($e->getCode() == 23000) {
                // Deal with duplicate key error
                return $response->withStatus(409);
            }
        }

        
    }

    // Bad payload request
    return $response->withStatus(400);
});