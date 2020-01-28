<?php 

/**
 * --------------------------
 * INITIALIZE SLIM APPLICATION
 * --------------------------
 * Init config
 */

require __DIR__ . '/../vendor/autoload.php';

/**
 * Init configuration
 */
require __DIR__ . '/../app/config/config.php';
require __DIR__ . '/../app/config/database.php';
require __DIR__ . '/../app/config/session.php';

/**
 * Init library
 */
require __DIR__ . '/../app/library/RequestBodyMiddleWare.php';


use DI\Container;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Database\Capsule\Manager as Capsule;


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

    if ($exception->getCode() == 404) {
        $response = $app->getResponseFactory()->createResponse(404);
        $response->getBody()->write('The request was not found on this server.');

        return $response;
    }

    $response = $app->getResponseFactory()->createResponse(500);
    $response->getBody()->write("An internal error occurred");

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
