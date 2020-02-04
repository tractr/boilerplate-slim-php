<?php

/**
 * ---------------------------
 * INITIALIZE SLIM APPLICATION
 * ---------------------------
 * Init config
 */

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('App\\Plugins\\', __DIR__ . '/../app/plugins');
$loader->addPsr4('App\\Models\\', __DIR__ . '/../app/models');
$loader->addPsr4('App\\Library\\', __DIR__ . '/../app/library');

/**
 * Init configuration
 */
require __DIR__ . '/../app/config/index.php';

use App\Library\CORSMiddleWare;
use App\Library\HttpException;
use App\Library\JSONResponseMiddleWare;
use App\Library\QueryStringMiddleWare;
use App\Library\RequestBodyMiddleWare;
use App\Plugins\Session;
use DI\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException as SlimHttpException;
use Slim\Factory\AppFactory;

/**
 * --------------------------
 * DEPENDENCIES
 * --------------------------
 * Create Container using PHP-DI
 */
$container = new Container();

/**
 * --------------------------
 * APP FACTORY
 * --------------------------
 * Init app factory
 */
AppFactory::setContainer($container);
$app = AppFactory::create();

/**
 * --------------------------
 * LOGGER
 * --------------------------
 * Logging framework for PHP applications.
 */

$container->set('logger', function () {
    $logger = new Logger('appLogger');
    $file_handler = new StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
});

/**
 * --------------------------
 * DATABASE
 * --------------------------
 * Service factory for the ORM
 */

$capsule = new Capsule;
$capsule->addConnection($config['db']);
$capsule->bootEloquent();
$capsule->setAsGlobal();

/**
 * --------------------------
 * REDIS
 * --------------------------
 * Used for not persistent data
 */

$container->set('redis', function () use ($config) {
    return new Predis\Client($config['redis']);
});

/**
 * --------------------------
 * ROUTES
 * --------------------------
 * It charge all routes files
 */

$routes = glob('../app/routes/*');

foreach ($routes as $route) {

    if (is_dir($route)) {

        $sub_routes = glob($route . '/*');

        foreach ($sub_routes as $sub_route) {
            if (!is_dir($sub_route)) {
                require_once($sub_route);
            }
        }
    } else {
        require_once($route);
    }
}

/**
 * --------------------------
 * SESSION PLUGIN
 * --------------------------
 */
$container->set('session', new Session($app, $capsule, $config['cookie']));

/**
 * --------------------------
 * QUERY STRING MIDDLEWARE
 * --------------------------
 * Object used to parse query string
 */
$app->add(new QueryStringMiddleWare());

/**
 * --------------------------
 * REQUEST BODY MIDDLEWARE
 * --------------------------
 * Object used to parse body encoding
 */
$app->add(new RequestBodyMiddleWare());

/**
 * --------------------------
 * CORS MIDDLEWARE
 * --------------------------
 * It enables lazy CORS.
 */
$app->options('/{routes:.+}', function (Request $request, Response $response, $args) {
    return $response->withStatus(204);
});
$app->add(new CORSMiddleWare());

/**
 * --------------------------
 * JSON RESPONSE MIDDLEWARE
 * --------------------------
 * Add "Content-Type: application/json" on responses
 */
$app->add(new JSONResponseMiddleWare());

/**
 * --------------------------
 * ROUTING MIDDLEWARE
 * --------------------------
 */
$app->addRoutingMiddleware();

/**
 * --------------------------
 * ERROR MIDDLEWARE
 * --------------------------
 * Function handling error
 */
$error_middleware_handler = function (ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) use ($app) {

    $response = $app->getResponseFactory()->createResponse()
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Origin', $request->getHeaderLine('Origin'))
        ->withHeader('Access-Control-Expose-Headers', 'WWW-Authenticate,Server-Authorization')
        ->withHeader('Content-Type', 'application/json');

    // App Error
    if ($exception instanceof HttpException) {

        $response->getBody()->write(json_encode(array(
            'statusCode' => $exception->getCode(),
            'error' => $exception->getStatusText(),
            'message' => $exception->getMessage()
        )));
        $response = $response->withStatus($exception->getCode());
    } // Slim Error
    elseif ($exception instanceof SlimHttpException) {

        $code = $exception->getCode();
        if (!HttpException::isStatusCodeValid($code)) {
            $code = 500;
        }

        // Avoid OPTIONS to catch all
        if ($code === 405 && $exception->getMessage() === 'Method not allowed. Must be one of: OPTIONS') {
            $response->getBody()->write(json_encode(array(
                'statusCode' => 404,
                'error' => HttpException::getStatusTextForCode(404),
                'message' => 'Route not found'
            )));
            $response = $response->withStatus(404);
        } else {
            $response->getBody()->write(json_encode(array(
                'statusCode' => $code,
                'error' => HttpException::getStatusTextForCode($code),
                'message' => $exception->getMessage()
            )));
            $response = $response->withStatus($code);
        }
    } // Other errors
    else {

        $payload = array(
            'statusCode' => 500,
            'error' => HttpException::getStatusTextForCode(500),
            'message' => $exception->getMessage()
        );
        if ($displayErrorDetails) {
            $payload['details'] = $exception->getTraceAsString();
        }

        $response->getBody()->write(json_encode($payload));
        $response = $response->withStatus(500);
    }

    return $response;
};
$error_middleware = $app->addErrorMiddleware(true, true, true);
$error_middleware->setDefaultErrorHandler($error_middleware_handler);
