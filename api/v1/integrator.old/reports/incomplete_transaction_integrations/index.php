<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate.php';
require 'mapping.php';

define("_GET_INCOMPLETE_TRANSACTION_INTEGRATIONS_PROC_PROD_", "integration_report_incomplete_transaction_integrations");
define("_GET_INCOMPLETE_TRANSACTION_INTEGRATIONS_PROC_DEV_", "integration_report_incomplete_transaction_integrations");

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
    	global $_FIELD_MAPPING_;
    	
    	try {
			// Extract query paramaters
			$data = array (
				 "_lhs_host"					=>	$request->getParam($_FIELD_MAPPING_["_lhs_host"])
				,"_lhs_instance"				=>	$request->getParam($_FIELD_MAPPING_["_lhs_instance"])
				,"_lhs_transaction_type"		=>	$request->getParam($_FIELD_MAPPING_["_lhs_transaction_type"])
				,"_lhs_transaction_status"		=>	$request->getParam($_FIELD_MAPPING_["_lhs_transaction_status"])
				,"_rhs_host"					=>	$request->getParam($_FIELD_MAPPING_["_rhs_host"])
				,"_rhs_instance"				=>	$request->getParam($_FIELD_MAPPING_["_rhs_instance"])
				,"_rhs_transaction_type"		=>	$request->getParam($_FIELD_MAPPING_["_rhs_transaction_type"])
				,"_rhs_transaction_status"		=>	$request->getParam($_FIELD_MAPPING_["_rhs_transaction_status"])
				,"_broker"						=>	$request->getParam($_FIELD_MAPPING_["_broker"])
				,"_broker_job_id"				=>	$request->getParam($_FIELD_MAPPING_["_broker_job_id"])
				,"_broker_job_instance_id"		=>	$request->getParam($_FIELD_MAPPING_["_broker_job_instance_id"])
				,"_expiry_in_seconds"			=>	$request->getParam($_FIELD_MAPPING_["_expiry_in_seconds"])
				,"_updated_since"				=>	$request->getParam($_FIELD_MAPPING_["_updated_since"])
			);
			
			// Validate input
			$errs = validate($data);
			$valid = empty($errs);
		
			// Return an array list of errors if invalid
			if(!$valid) {
				return 	$response->withStatus(400)
							->withHeader("Content-Type","application/json")
							->write(json_encode($errs));
			}
		
			 // Set params
			$devmode = $request->getParam("devmode") === true || strcmp($request->getParam("devmode"), "true") == 0 ? true : false;
		
			// DB Settings
			$host 	= !$devmode ? _HOST_URI_PROD_ 		: _HOST_URI_DEV_;
			$dbname = !$devmode ? _HOST_DB_PROD_ 		: _HOST_DB_DEV_;
			$usr 	= !$devmode ? _HOST_USER_PROD_ 		: _HOST_USER_DEV_;
			$pwd 	= !$devmode ? _HOST_PASSWORD_PROD_ 	: _HOST_PASSWORD_DEV_;

			$proc 	= !$devmode ? _GET_INCOMPLETE_TRANSACTION_INTEGRATIONS_PROC_PROD_ : _GET_INCOMPLETE_TRANSACTION_INTEGRATIONS_PROC_DEV_;
        
        	$results = array();
        
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_lhs_host".
					",:_lhs_instance".
					",:_lhs_transaction_type".
					",:_lhs_transaction_status".
					",:_rhs_host".
					",:_rhs_instance".
					",:_rhs_transaction_type".
					",:_rhs_transaction_status".
					",:_broker".
					",:_broker_job_id".
					",:_broker_job_instance_id".
					",:_expiry_in_seconds".
					",:_updated_since".
				");"
			);
			$stmt->bindParam(':_lhs_host', 					$data["_lhs_host"], 				PDO::PARAM_STR,255); 
			$stmt->bindParam(':_lhs_instance', 				$data["_lhs_instance"], 			PDO::PARAM_STR,255); 
			$stmt->bindParam(':_lhs_transaction_type', 		$data["_lhs_transaction_type"], 	PDO::PARAM_STR,50); 
			$stmt->bindParam(':_lhs_transaction_status', 	$data["_lhs_transaction_status"], 	PDO::PARAM_STR,100); 
			$stmt->bindParam(':_rhs_host', 					$data["_rhs_host"], 				PDO::PARAM_STR,255); 
			$stmt->bindParam(':_rhs_instance', 				$data["_rhs_instance"], 			PDO::PARAM_STR,255); 
			$stmt->bindParam(':_rhs_transaction_type', 		$data["_rhs_transaction_type"], 	PDO::PARAM_STR,50); 
			$stmt->bindParam(':_rhs_transaction_status', 	$data["_rhs_transaction_status"], 	PDO::PARAM_STR,100); 
			$stmt->bindParam(':_broker', 					$data["_broker"], 					PDO::PARAM_STR,255); 
			$stmt->bindParam(':_broker_job_id', 			$data["_broker_job_id"],			PDO::PARAM_STR,50); 
			$stmt->bindParam(':_broker_job_instance_id', 	$data["_broker_job_instance_id"],	PDO::PARAM_STR,50); 
			$stmt->bindParam(':_expiry_in_seconds', 		$data["_expiry_in_seconds"]);
			$stmt->bindParam(':_updated_since', 			$data["_updated_since"]);
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
    function () use ($app) {	
    }
);

// PUT route
$app->put(
    '/',
    function () {
    }
);

/**
 * Run the Slim application
 */
$app->run();

