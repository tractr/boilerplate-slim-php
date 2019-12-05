<?php 
/**
 * Update Service.
 *
 * Form validation used here is vlucas/valitron library, for more information, you can read documentation here https://github.com/vlucas/valitron
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator as Validator;

$app->patch('/service/{id}', function (Request $request, Response $response, array $args) {
    global $check_auth;
    //Authentication
    $check_auth();

    $data = App\Models\Service::find($args['id']);

    if ($data == null) {
    	// Entity was not found
    	return $response->withStatus(404);
    }

    $_data = $request->getParsedBody();
    //Form validation
    $validator = new Validator($_data);
    $validator->rule('required', 'name');
    $validator->rule('required', 'description', true );

    if ($validator->validate()) {

        try {
            $data->name = $_data['name'];
            $data->description = $_data['description'];

            $data->save();

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
