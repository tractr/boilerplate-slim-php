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
    global $check_auth;
    global $get_current_session;
    //Authentication
    $check_auth();
    $credential = $get_current_session();

    //Form validation
    $validator = new Validator($data);
    $validator->rule('required', 'name');
    $validator->rule('required', 'description', true );
    /* No rule for property name */
    /* No rule for property description */

    if ($validator->validate()) {
        try {

            $_data = App\Models\Service::create(array(
                'name' => $data['name'],
                'description' => $data['description'],
                // Init internal fields
                'created_at' => date('Y-m-d H:i:s'),
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
