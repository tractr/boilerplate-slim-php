<?php

use App\Library\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Valitron\Validator as Validator;

/**
 * --------------------------
 * GET COOKIE VALUE
 * --------------------------
 * get current cookie value usign cookie configuration index name
 */

function get_cookie_value()
{
    global $config;

    return isset($_COOKIE[$config['cookie']['name']]) ? $_COOKIE[$config['cookie']['name']] : null;
}

/**
 * --------------------------
 * CREATE SESSION
 * --------------------------
 * create new session and cache it
 * @param \Illuminate\Database\Eloquent\Model $user
 * @return array
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 */
function create_session($user)
{
    global $config, $container;
    //write session inside file

    //set cookie with uniq_id
    $uniq_id = uniqid(true);

    $data = array(
        '_id' => $user->_id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role
    );

    // Store the session in Redis
    $container->get('redis')->setex("sid-{$uniq_id}", $config['cookie']['expire'], json_encode($data));

    // Set cookie with uniq id
    setcookie(
        $config['cookie']['name'],
        $uniq_id,
        time() + $config['cookie']['expire'],
        $config['cookie']['path'],
        $config['cookie']['domain'],
        $config['cookie']['secure'],
        $config['cookie']['httponly']
    );

    return $data;
}

/**
 * --------------------------
 * GET CURRENT SESSION
 * --------------------------
 * fetch current session cache
 * @return array|null
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 */
function get_current_session()
{
    global $container;
    $uniq_id = get_cookie_value();

    if (!$uniq_id) {
        return null;
    }

    $content = $container->get('redis')->get("sid-{$uniq_id}");

    if (!$content) {
        return null;
    }

    return json_decode($content, true);
}

/**
 * --------------------------
 * DELETE CURRENT SESSION
 * --------------------------
 * delete current session
 * @return bool
 * @throws \DI\DependencyException
 * @throws \DI\NotFoundException
 */
function delete_current_session()
{
    global $config, $container;
    $uniq_id = get_cookie_value();

    if (!$uniq_id) {
        return false;
    }

    $container->get('redis')->del("sid-{$uniq_id}");

    // Remove cookie
    setcookie(
        $config['cookie']['name'],
        null
    );

    return true;
}

/**
 * --------------------------
 * VERIFY AUTHENTICATION
 * --------------------------
 * check if current session is authenticated and may have access to admin (if necessary)
 * @param Request $request
 * @return bool
 * @throws HttpException
 */

function verify_credentials(Request $request)
{
    $session_data = $request->getAttribute('credentials');
    if ($session_data == null) {
        // user doesn't have current session
        throw new HttpException(401, 'You must authenticate to access this resource');
    }

    if ($request->getAttribute('fromAdmin') && $session_data['role'] !== 'admin') {
        // user doesn't have current session
        throw new HttpException(403, 'You are not allowed to access this resource');
    }

    return true;
}

/**
 * --------------------------
 * VERIFY OWNERSHIP
 * --------------------------
 * check if current session is authorized to access this resource
 * @param Request $request
 * @param int|int[] $ownerIds
 * @return bool
 * @throws HttpException
 */

function verify_ownership(Request $request, $ownerIds)
{
    if (!is_array($ownerIds)) {
        $ownerIds = [$ownerIds];
    }
    $session_data = $request->getAttribute('credentials');

    if (
        $session_data === null ||
        ($session_data['role'] !== 'admin' && !in_array($session_data['_id'], $ownerIds))
    ) {
        // user doesn't have current session
        throw new HttpException(403, 'You are not allowed to access this resource');
    }

    return true;
}

/**
 * --------------------------
 * REQUEST FROM ADMIN
 * --------------------------
 * function that check if request if from admin
 * @param Request $request
 * @return boolean
 */

function request_from_admin(Request $request)
{
    $routeContext = RouteContext::fromRequest($request);

    return boolval(preg_match('#^\/admin#', $routeContext->getRoute()->getPattern()));
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
            throw new HttpException(401, 'User not found or wrong password');
        }

        //On vérifie la cohérence du mot de passe
        if (App\Library\Encryption::test($data['password'], $user->password)) {
            //Enregistrement du cookie
            $session_data = create_session($user);

            $response->getBody()->write(json_encode($session_data));
            return $response->withStatus(201);
        } else {
            throw new HttpException(401, 'User not found or wrong password');
        }
    }

    // Bad payload request
    throw HttpException::badRequest($validator);
});

/**
 * --------------------------
 * GET SESSION
 * --------------------------
 * get user current session
 */

$app->get('/session', function (Request $request, Response $response, array $args) {

    verify_credentials($request);

    $payload = json_encode($request->getAttribute('credentials'));

    $response->getBody()->write($payload);
    return $response->withStatus(200);
});

/**
 * --------------------------
 * DELETE SESSION
 * --------------------------
 * delete user current session
 */

$app->delete('/session', function (Request $request, Response $response, array $args) {

    verify_credentials($request);

    delete_current_session();

    return $response->withStatus(204);
});


/**
 * --------------------------
 * SESSION MIDDLEWARE
 * --------------------------
 * Add metadata to current session
 */
$app->add(function (Request $request, RequestHandler $handler): ResponseInterface {

    // add the session data
    $sessionData = get_current_session();
    $request = $request->withAttribute('credentials', $sessionData);
    // Shortcut to user's id
    $request = $request->withAttribute('userId', $sessionData !== NULL ? $sessionData['_id'] : NULL);

    // Denotes if the request is from admin
    $request = $request->withAttribute('fromAdmin', request_from_admin($request));

    return $handler->handle($request);
});