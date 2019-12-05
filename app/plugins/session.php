<?php 
require 'AuthException.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Valitron\Validator as Validator;

/**
 * --------------------------
 * GET COOKIE VALUE
 * --------------------------
 * get current cookie value usign cookie configuration index name
 */

function get_cookie_value ()
{
	global $config;

	return isset($_COOKIE[$config['cookie']['name']]) ? $_COOKIE[$config['cookie']['name']] : null;
}

/**
 * --------------------------
 * CREATE SESSION
 * --------------------------
 * create new session and cache it
 */

function create_session ($user)
{
	global $config;
	//write session inside file

	//set cookie with uniq_id
	$uniq_id = uniqid();

	//folder and file path
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
			$config['cookie']['path'],
			$config['cookie']['domain'],
			$config['cookie']['secure'],
			$config['cookie']['httponly'],
			$config['cookie']['options']
		);

		flock($file, LOCK_EX);
		fwrite($file, json_encode($data));
		flock($file, LOCK_UN);
		fclose($file);
	}

	return $data;
}

/**
 * --------------------------
 * GET CURRENT SESSION
 * --------------------------
 * fetch current session cache
 */

function get_current_session ()
{
	$cookie_value = get_cookie_value();

	$folder_path = dirname(__DIR__) . '/cache';
	$file_path = $folder_path . '/' . $cookie_value . '.json';

	if (!file_exists($file_path)) {
		return null;
	}

	return json_decode(
		file_get_contents($file_path),
		true
	);
}

/**
 * --------------------------
 * DELETE CURRENT SESSION
 * --------------------------
 * delete current session
 */

function delete_current_session ()
{
	global $config;
	$cookie_value = get_cookie_value();

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
}

/**
 * --------------------------
 * CHECK AUTHENTICATION
 * --------------------------
 * check if current session is authenticated
 */

function check_auth()
{
	$session_data = get_current_session();
    if ($session_data == null) {
    	// user doesn't have current session
    	throw new AuthException();
    }

    return true;
}

/**
 * --------------------------
 * REQUEST FROM ADMIN
 * --------------------------
 * function that check if request if from admin
 */

function request_from_admin ($request)
{
    $routes = $request->getAttribute('route');
    
    return preg_match('#^\/admin#', $routes->getPattern());
}

/**
 * --------------------------
 * CREATE SESSION
 * --------------------------
 * Authenticate user and return current session
 */

$app->post('/password/login', function (Request $request, Response $response, array $args) {

	global $capsule;
    $data = $request->getParsedBody();

    //Form validation
    $validator = new Validator($data);
    $validator->rule('required', 'email');
    $validator->rule('required', 'password');
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
			$session_data = create_session($user);

			$response->getBody()->write(json_encode($session_data));
			return $response
					->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
		}
    }

    // Bad payload request
    return $response->withStatus(401);
});

/**
 * --------------------------
 * GET SESSION
 * --------------------------
 * get user current session
 */

$app->get('/session', function (Request $request, Response $response, array $args) {

    $session_data = get_current_session();

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

/**
 * --------------------------
 * DELETE SESSION
 * --------------------------
 * delete user current session
 */

$app->delete('/session', function (Request $request, Response $response, array $args) {

    $result = delete_current_session();

    if (!$result) {
    	// user doesn't have current session
    	return $response->withStatus(401);
    }

    return $response
              ->withStatus(204);
});