<?php 
require 'AuthException.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Valitron\Validator as Validator;

$get_cookie_value = function ()
{
	global $config;

	return isset($_COOKIE[$config['cookie']['name']]) ? $_COOKIE[$config['cookie']['name']] : null;
};

$create_session = function ($user)
{
	global $config;
	//write session inside file


	//set cookie with uniq_id
	$uniq_id = uniqid();
	$folder_path = dirname(__DIR__) . '/cache';
	$file_path = $folder_path . '/' . $uniq_id . '.json';

	$data = array(
		'ID' => $user->_id,
		'name' => $user->name,
		'email' => $user->email,
		'role' => $user->role
	);

	if (!is_dir($folder_path)) {
		mkdir($folder_path, 777);
	}

	if ($file = @fopen($file_path, 'c+'))
	{
		$granted = true;
	}

	if ($granted || !file_exists($file_path)) {

		setcookie(
			$config['cookie']['name'], 
			$uniq_id, 
			time() + $config['cookie']['expire'], 
			$config['cookie']['path']
		);

		flock($file, LOCK_EX);
		fwrite($file, json_encode($data));
		flock($file, LOCK_UN);
		fclose($file);
	}

	return $data;
};

$get_current_session = function ()
{
	global $get_cookie_value;
	$cookie_value = $get_cookie_value();

	$folder_path = dirname(__DIR__) . '/cache';
	$file_path = $folder_path . '/' . $cookie_value . '.json';

	if (!file_exists($file_path)) {
		return null;
	}

	return json_decode(
		file_get_contents($file_path)
	);
};

$delete_current_session = function ()
{
	global $config;
	global $get_cookie_value;
	$cookie_value = $get_cookie_value();

	$folder_path = dirname(__DIR__) . '/cache';
	$file_path = $folder_path . '/' . $cookie_value . '.json';

	if (!file_exists($file_path)) {
		return false;
	}

	unlink($file_path);
	setcookie(
		$config['cookie']['name'], 
		null
	);

	return true;
};

$auth_middleware = function (Request $request, RequestHandler $handler)
{
	global $get_current_session;
	$response = $handler->handle($request);

	//check if user is autheticated
	$session_data = $get_current_session();
    if ($session_data == null) {
    	// user doesn't have current session
    	throw new AuthException();
    }
	
	return $response;
};

/**
 * Create service.
 * Returns the created service.
 *
 */
$app->post('/password/login', function (Request $request, Response $response, array $args) {

	global $capsule;
	global $create_session;
    $data = $request->getParsedBody();

    //Form validation
    $validator = new Validator($data);
    $validator->rule('required', array('email','password'));
    $validator->rule('email', 'email');

    if ($validator->validate()) {

    	$user = $capsule::table('user')->where('email', $data['email'])->first();

    	if ($user == null) {
    		return $response->withStatus(404);
    	}

    	$password = md5($data['password']);

		//On vérifie la cohérence du mot de passe
		if ($user->password === $password) {
			//Enregistrement du cookie
			$session_data = $create_session($user);

			$response->getBody()->write(json_encode($session_data));
			return $response
					->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
		}
    }

    // Bad payload request
    return $response->withStatus(401);
});


$app->get('/session', function (Request $request, Response $response, array $args) {

    global $get_current_session;
    global $config;

    $session_data = $get_current_session();

    if ($session_data == null) {
    	// user doesn't have current session
    	return $response->withStatus(401);
    }

    $payload = json_encode($session_data);

    $response->getBody()->write($payload);
    return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(201);
});

$app->delete('/session', function (Request $request, Response $response, array $args) {

    global $delete_current_session;
    global $config;

    $result = $delete_current_session();

    if (!$result) {
    	// user doesn't have current session
    	return $response->withStatus(401);
    }

    return $response
              ->withStatus(204);
});