<?php

namespace App\Plugins;

use App\Library\Encryption;
use App\Library\HttpException;
use DI\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Illuminate\Database\Capsule\Manager as Capsule;
use Slim\App;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Valitron\Validator as Validator;

class Session
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var App
     */
    private App $app;
    /**
     * @var Container
     */
    private Container $container;
    /**
     * @var Capsule
     */
    private Capsule $capsule;

    /**
     * Session constructor.
     * @param App $app
     * @param Capsule $capsule
     * @param $config
     */
    public function __construct(App $app, Capsule $capsule, $config) {
        $this->config = $config;
        $this->app = $app;
        $this->capsule = $capsule;
        $this->container = $this->app->getContainer();
        $this->register();
    }

    /**
     * Add routes to Slim App
     * @return $this
     */
    private function register()
    {
        // Store current this
        $plugin = $this;

        /**
         * --------------------------
         * CREATE SESSION
         * --------------------------
         * Authenticate user and return current session
         */
        $this->app->post('/password/login', function (Request $request, Response $response, array $args) use ($plugin) {

            $data = $request->getParsedBody();

            //Form validation
            $validator = new Validator($data);
            $validator->rule('required', 'email');
            $validator->rule('required', 'password');
            $validator->rule('email', 'email');

            if ($validator->validate()) {

                $user = $plugin->capsule->table('user')->where('email', $data['email'])->first();

                if ($user == null) {
                    throw new HttpException(401, 'User not found or wrong password');
                }

                //On vérifie la cohérence du mot de passe
                if (Encryption::test($data['password'], $user->password)) {
                    //Enregistrement du cookie
                    $session_data = $plugin->createSession($user);

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

        $this->app->get('/session', function (Request $request, Response $response, array $args) use ($plugin) {

            $plugin->verifyCredentials($request);

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

        $this->app->delete('/session', function (Request $request, Response $response, array $args) use ($plugin) {

            $plugin->verifyCredentials($request);

            $plugin->deleteCurrentSession();

            return $response->withStatus(204);
        });


        /**
         * --------------------------
         * SESSION MIDDLEWARE
         * --------------------------
         * Add metadata to current session
         */
        $this->app->add(function (Request $request, RequestHandler $handler) use ($plugin): ResponseInterface {

            // add the session data
            $sessionData = $plugin->getCurrentSession();
            $request = $request->withAttribute('credentials', $sessionData);
            // Shortcut to user's id
            $request = $request->withAttribute('userId', $sessionData !== NULL ? $sessionData['_id'] : NULL);

            // Denotes if the request is from admin
            $request = $request->withAttribute('fromAdmin', $plugin->requestFromAdmin($request));

            return $handler->handle($request);
        });

        return $this;
    }

    /**
     * --------------------------
     * GET COOKIE VALUE
     * --------------------------
     * get current cookie value usign cookie configuration index name
     */
    private function getCookieValue()
    {
        return isset($_COOKIE[$this->config['name']]) ? $_COOKIE[$this->config['name']] : null;
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
    private function createSession($user)
    {
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
        $this->container->get('redis')->setex("sid-{$uniq_id}", $this->config['expire'], json_encode($data));

        // Set cookie with uniq id
        setcookie(
            $this->config['name'],
            $uniq_id,
            time() + $this->config['expire'],
            $this->config['path'],
            $this->config['domain'],
            $this->config['secure'],
            $this->config['httponly']
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
    private function getCurrentSession()
    {
        $uniq_id = $this->getCookieValue();

        if (!$uniq_id) {
            return null;
        }

        $content = $this->container->get('redis')->get("sid-{$uniq_id}");

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
    private function deleteCurrentSession()
    {
        $uniq_id = $this->getCookieValue();

        if (!$uniq_id) {
            return false;
        }

        $this->container->get('redis')->del("sid-{$uniq_id}");

        // Remove cookie
        setcookie(
            $this->config['name'],
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
    public function verifyCredentials(Request $request)
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
    public function verifyOwnership(Request $request, $ownerIds)
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
    private function requestFromAdmin(Request $request)
    {
        $routeContext = RouteContext::fromRequest($request);

        return boolval(preg_match('#^\/admin#', $routeContext->getRoute()->getPattern()));
    }
}