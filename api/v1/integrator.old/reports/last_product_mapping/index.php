<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-delete.php';
require 'mapping.php';

define("_GET_MAPPING_PROC_PROD_", "report_last_product_mapping_sync");
define("_GET_MAPPING_PROC_DEV_", "report_last_product_mapping_sync");

define("_POST_MAPPING_PROC_PROD_", "report_last_product_mapping_sync");
define("_POST_MAPPING_PROC_DEV_", "report_last_product_mapping_sync");

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
    	
    	try {
			$body = array(
    			 $_GET_FIELD_MAPPING_["_sku"] 				=> $request->getParam($_GET_FIELD_MAPPING_["_sku"])
    			,$_GET_FIELD_MAPPING_["_barcode"] 			=> $request->getParam($_GET_FIELD_MAPPING_["_barcode"])
    			,$_GET_FIELD_MAPPING_["_company"] 		=> $request->getParam($_GET_FIELD_MAPPING_["_company"])
    			,$_GET_FIELD_MAPPING_["_country_code"]	=> $request->getParam($_GET_FIELD_MAPPING_["_country_code"])
    			,$_GET_FIELD_MAPPING_["_dear_instance"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_dear_instance"])
    			,$_GET_FIELD_MAPPING_["_vend_instance"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_vend_instance"])
    			,$_GET_FIELD_MAPPING_["_by_system_master"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_by_system_master"])
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
			
			// Set params
			$devmode = $request->getParam("devmode") === true || strcmp($request->getParam("devmode"), "true") == 0 ? true : false;
			
			// DB Settings
			$host 	= !$devmode ? _HOST_URI_PROD_ 		: _HOST_URI_DEV_;
			$dbname = !$devmode ? _HOST_DB_PROD_ 		: _HOST_DB_DEV_;
			$usr 	= !$devmode ? _HOST_USER_PROD_ 		: _HOST_USER_DEV_;
			$pwd 	= !$devmode ? _HOST_PASSWORD_PROD_ 	: _HOST_PASSWORD_DEV_;

			$proc 	= !$devmode ? _GET_MAPPING_PROC_PROD_ : _GET_MAPPING_PROC_DEV_;
	
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$results = array();
			
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_sku".
					",:_barcode".
					",:_company".
					",:_country_code".
					",:_dear_instance".
					",:_vend_instance".
					",:_by_system_master".
				");"
			);
			$stmt->bindParam(':_sku', 				$data[$_GET_FIELD_MAPPING_["_sku"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_barcode', 			$data[$_GET_FIELD_MAPPING_["_barcode"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_company', 			$data[$_GET_FIELD_MAPPING_["_company"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_country_code', 		$data[$_GET_FIELD_MAPPING_["_country_code"]], PDO::PARAM_STR,2); 
			$stmt->bindParam(':_dear_instance', 	$data[$_GET_FIELD_MAPPING_["_dear_instance"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_vend_instance', 	$data[$_GET_FIELD_MAPPING_["_vend_instance"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_by_system_master', 	$data[$_GET_FIELD_MAPPING_["_by_system_master"]]); 
			$stmt->execute();
			
			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				array_push($results, $row);
			}
			
			$stmt->closeCursor();
			
			// Close connection
			$conn = null;

			// Return input body as results to confirm success
			return 	$response->withStatus(200)
						->withHeader("Content-Type","application/json")
						->write(json_encode($results));
		
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
    	// Import field mapping
    	global $_POST_FIELD_MAPPING_;
    	
    	try {
			// Validate input
			$errs = validate_post($request->getBody());
			$valid = empty($errs);
		
			// Return an array list of errors if invalid
			if(!$valid) {
				return 	$response->withStatus(400)
							->withHeader("Content-Type","application/json")
							->write(json_encode($errs));
			}
		
			// Convert JSON data to array
			$data = json_decode($request->getBody(), true);
			
			// Set params
			$devmode = $request->getParam("devmode") === true || strcmp($request->getParam("devmode"), "true") == 0 ? true : false;
			
			// DB Settings
			$host 	= !$devmode ? _HOST_URI_PROD_ 		: _HOST_URI_DEV_;
			$dbname = !$devmode ? _HOST_DB_PROD_ 		: _HOST_DB_DEV_;
			$usr 	= !$devmode ? _HOST_USER_PROD_ 		: _HOST_USER_DEV_;
			$pwd 	= !$devmode ? _HOST_PASSWORD_PROD_ 	: _HOST_PASSWORD_DEV_;

			$proc 	= !$devmode ? _POST_MAPPING_PROC_PROD_ : _POST_MAPPING_PROC_DEV_;
	
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$result = array();

			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_sku".
					",:_barcode".
					",:_company".
					",:_country_code".
					",:_dear_instance".
					",:_vend_instance".
					",:_by_system_master".
				");"
			);
			$stmt->bindParam(':_sku', 				$data[$_POST_FIELD_MAPPING_["_sku"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_barcode', 			$data[$_POST_FIELD_MAPPING_["_barcode"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_company', 			$data[$_POST_FIELD_MAPPING_["_company"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_country_code', 		$data[$_POST_FIELD_MAPPING_["_country_code"]], PDO::PARAM_STR,2); 
			$stmt->bindParam(':_dear_instance', 	$data[$_POST_FIELD_MAPPING_["_dear_instance"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_vend_instance', 	$data[$_POST_FIELD_MAPPING_["_vend_instance"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_by_system_master', 	$data[$_POST_FIELD_MAPPING_["_by_system_master"]]); 
			$stmt->execute();
			
			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				array_push($result, $row);
			}
			
			// Close connection
			$conn = null;

			$stmt->closeCursor();
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
