<?php
require $_SERVER['DOCUMENT_ROOT'].'/ZObject.php';
require 'CORSProxy.php';

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

/**
 * Import Dependencies and Instantiate
 */

require $_SERVER['DOCUMENT_ROOT']. '/api-vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/src/dependencies.php';

// Register middleware
require __DIR__ . '/src/middleware.php';


/**
 * Configure Middleware
 */

/**
 * Routes
 */
// ANY route
$app->any(
    '/',
    function ($request, $response, $args) use ($app) {
    	try {
    		$proxy = new \CORSProxy\Proxy($app->getContainer(), []);
    		return $proxy($request, $response, $args);
		} catch (PDOException $e) {
			return 	$response->withStatus(500)
							->withHeader("Content-Type","application/json")
							->write(json_encode(array(
						"ErrorCode"	=>	500,
						"Exception"	=>	$e->getMessage()
					)));
		}
        
    }
);

/**
 * Run the Slim application
 */
$app->run();
