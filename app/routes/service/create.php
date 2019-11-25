<?php 

/**
 * Create service.
 * Returns the created service.
 *
 * Form validation used here is vlucas/valitron library, for more information, you can read documentation here https://github.com/vlucas/valitron
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator as Validator;

$app->post('/service', function (Request $request, Response $response, array $args) {

    $data = $request->getParsedBody();

    //Form validation
    $validator = new Validator($data);
    $validator->rules(array(
        'required' => array(
            array('name'),
            array('description', true),//allow null
        )
    ));

    if ($validator->validate()) {

        try {
            $_data = App\Models\Service::create(array(
                'name' => $data['name'],
                'description' => $data['description'],
            ));

            $payload = json_encode($_data);
            $response->getBody()->write($payload);

            return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(201);

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