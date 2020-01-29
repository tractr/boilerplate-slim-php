<?php 

/**
 * --------------------------
 * INITIALIZE SLIM APPLICATION
 * --------------------------
 * Init config
 */

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('App\\Models\\', __DIR__ . '/../app/models');
$loader->addPsr4('App\\Library\\', __DIR__ . '/../app/library');

/**
 * Init configuration
 */
require __DIR__ . '/../app/config/config.php';
require __DIR__ . '/../app/config/database.php';
require __DIR__ . '/../app/config/session.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Library\RequestBodyMiddleWare;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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
// Add Routing Middleware
$app->addRoutingMiddleware();

/**
 * Init plugins
 */
require __DIR__ . '/../app/plugins/session.php';

/**
 * --------------------------
 * CREDENTIAL
 * --------------------------
 * Get current session from session file
 */

$container->set('credential', function () {
    global $get_current_session;

    return $get_current_session();
});

/**
 * --------------------------
 * LOGGER
 * --------------------------
 * Logging framework for PHP applications.
 */

$container->set('logger', function () {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
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
    }
    else{
    	require_once($route);
    }
}

/**
 * --------------------------
 * CORS
 * --------------------------
 * It enables lazy CORS.
 */
$app->options('/{routes:.+}', function (Request $request, Response $response, $args) {
    return $response
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Headers', 'Accept,Authorization,Content-Type,If-None-Match')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Origin', $request->getHeader('Origin'))
        ->withHeader('Access-Control-Expose-Headers', 'WWW-Authenticate,Server-Authorization')
        ->withHeader('Access-Control-Max-Age', '86400');
});

/**
 * --------------------------
 * MODEL
 * --------------------------
 * It charge all routes files
 */

$routes = glob('../app/models/*');

foreach ($routes as $route) {
    
    if (is_dir($route)) {

        $sub_routes = glob($route . '/*');

        foreach ($sub_routes as $sub_route) {
            if (!is_dir($sub_route)) {
                require_once($sub_route);
            }
        }
    }
    else{
        require_once($route);
    }
}

/**
 * --------------------------
 * ERROR MIDDLEWARE
 * --------------------------
 * Function handling error
 */

$error_middleware_handler = function (ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) use ($app) {
    //Authentication error
    if ($exception instanceof AuthException) {

        $response = $app->getResponseFactory()->createResponse($exception->getCode());
        $response->getBody()->write($exception->getMessage());

        return $response;
    }

    $code = $exception->getCode();
    if ($code >= 400 && $code < 500) {
        $response = $app->getResponseFactory()->createResponse($code);
        return $response;
    }

    $response = $app->getResponseFactory()->createResponse($code);
    $text = $code >= 500 ? "An internal error occurred" : "An unknown error occurred";
    $response->getBody()->write($text);

    if ($displayErrorDetails) {
        $errorTrace = "\n{$exception->getMessage()}\n{$exception->getTraceAsString()}";
        $response->getBody()->write("<pre>{$errorTrace}</pre>");
    }

    return $response;
};
$error_middleware = $app->addErrorMiddleware(true, true, true);
$error_middleware->setDefaultErrorHandler($error_middleware_handler);


/**
 * --------------------------
 * REQUEST BODY MIDDLEWARE
 * --------------------------
 * Object used to parse body encoding
 */

$app->add(new RequestBodyMiddleWare());
