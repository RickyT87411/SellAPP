<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-delete.php';
require 'mapping.php';

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


require __DIR__ . '/src/APIRateLimit.php';

/**
 * Configure Middleware
 */
//$app->add(new \Slim\Extras\Middleware\HttpBasicAuth('dev-integrator', 'Zj|~YxiCqh!2'));

$app->add(function ($request, $response, $next) {

	$host 	= _HOST_URI_PROD_;
	$dbname = _HOST_DB_PROD_;
	$usr 	= _HOST_USER_PROD_;
	$pwd 	= _HOST_PASSWORD_PROD_;

    $requests = 100; // maximum number of requests
    $inmins = 60;    // in how many time (minutes)

    $APIRateLimit = new App\Utils\APIRateLimit($requests, $inmins,"mysql:host=$host;dbname=$dbname", $usr, $pwd);
    $mustbethrottled = $APIRateLimit();

    if ($mustbethrottled == false) {
        $responsen = $next($request, $response);
    } else {
        $responsen = $response->withStatus(429)
        				->withHeader('RateLimit-Limit', $requests);
    }

    return $responsen;
});

/**
 * Routes
 */
// GET route
$app->get(
    '/',
    function ($request, $response, $args) use ($app) {
   		// Import field mapping
    	global $_GET_FIELD_MAPPING_;
    	global $_SORT_INDEX_TABLE;
    	
    	try {
    		$body = array(
    			 $_GET_FIELD_MAPPING_["_department"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_department"])
    			,$_GET_FIELD_MAPPING_["_variant_name"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_variant_name"])
    			,$_GET_FIELD_MAPPING_["_variant_value"] => $request->getParam($_GET_FIELD_MAPPING_["_variant_value"])
    		);
    		
    		$body = json_encode($body);
    	
			// Validate input
			$errs = validate_get($body);
			$valid = empty($errs);
		
			// Return an array list of errors if invalid
			if(!$valid) {
				return 	$response->withStatus(400)
							->withHeader("Content-Type","application/json")
							->write(json_encode($errs));
			}
		
			// Convert JSON data to array
			$data = json_decode($body, true);
			
			$deparment = strtolower($data[$_GET_FIELD_MAPPING_["_department"]]);
			$variant = strtolower($data[$_GET_FIELD_MAPPING_["_variant_name"]]);
			$lookup = strtoupper($data[$_GET_FIELD_MAPPING_["_variant_value"]]);
			
			$department_table = array_key_exists($deparment, $_SORT_INDEX_TABLE) ? $_SORT_INDEX_TABLE[$deparment] : array();
			$variant_table = array_key_exists($variant, $department_table) ? $department_table[$variant] : array();
			$index = array_key_exists($lookup, $variant_table) ? $variant_table[$lookup] : 0;
		
			$result = array(
				 "department" 		=> $data[$_GET_FIELD_MAPPING_["_department"]]
				,"variant_name" 	=> $data[$_GET_FIELD_MAPPING_["_variant_name"]]
				,"variant_value" 	=> $data[$_GET_FIELD_MAPPING_["_variant_value"]]
				,"sort_index" 		=> $index
			);
			
			// Close connection
			$conn = null;
			
			// Return input body as results to confirm success
			return 	$response->withStatus(200)
						->withHeader("Content-Type","application/json")
						->write(json_encode($result));
		
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

// POST route
$app->post(
    '/',
    function ($request, $response, $args) use ($app) {
    }
);

// PUT route
$app->put(
    '/',
    function () {
    }
);

// DELETE route
$app->delete(
    '/',
    function ($request, $response, $args) use ($app) {
    }
);

/**
 * Run the Slim application
 */
$app->run();
