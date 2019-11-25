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


use DI\Container;
use Slim\Factory\AppFactory;
use App\WidgetController;

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
// $app->addRoutingMiddleware();


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

$routes = glob('../app/model/*');

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