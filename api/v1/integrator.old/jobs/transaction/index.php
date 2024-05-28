<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate.php';
require 'mapping.php';

define("_POST_TRANSACTION_PROC_PROD_", "transaction_upsert_v3");
define("_POST_TRANSACTION_PROC_DEV_", "transaction_upsert_v3");

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
    function () {
    }
);

// POST route
$app->post(
    '/',
    function ($request, $response, $args) use ($app) {
    	// Import field mapping
    	global $_FIELD_MAPPING_;
    	global $_SOURCE_CHANNEL_MAPPING_;
    	
    	try {
			// Validate input
			$errs = validate($request->getBody());
			$valid = empty($errs);
		
			// Return an array list of errors if invalid
			if(!$valid) {
				return 	$response->withStatus(400)
							->withHeader("Content-Type","application/json")
							->write(json_encode($errs));
			}
		
			// Convert JSON data to array
			$array = json_decode($request->getBody(), true);
			
			// Set params
			$devmode = $request->getParam("devmode") === true || strcmp($request->getParam("devmode"), "true") == 0 ? true : false;
			
			// DB Settings
			$host 	= !$devmode ? _HOST_URI_PROD_ 		: _HOST_URI_DEV_;
			$dbname = !$devmode ? _HOST_DB_PROD_ 		: _HOST_DB_DEV_;
			$usr 	= !$devmode ? _HOST_USER_PROD_ 		: _HOST_USER_DEV_;
			$pwd 	= !$devmode ? _HOST_PASSWORD_PROD_ 	: _HOST_PASSWORD_DEV_;

			$proc 	= !$devmode ? _POST_TRANSACTION_PROC_PROD_ : _POST_TRANSACTION_PROC_DEV_;
	
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$results = array();
			
			foreach($array as $data) {
				// Use the transaction date as the seed for determining the offset with DST in consideration
				$trxdate = $data[$_FIELD_MAPPING_["_transaction_date"]];
				$tz = $data[$_FIELD_MAPPING_["_transaction_timezone"]];
				$offset = null;
				
				if($tz) { 
					$dtz = new DateTimeZone($tz);
					$time = new DateTime($trxdate, $dtz);
					$offset = $dtz->getOffset( $time );
				}
				
				$source = strtolower($data[$_FIELD_MAPPING_["_source"]]);
				$channel = $_SOURCE_CHANNEL_MAPPING_[$source];
			
				// Execute the stored procedure
				$stmt = $conn->prepare(
					"CALL $proc(".
						 ":_channel".
						",:_source".
						",:_source_instance".
						",:_location".
						",:_location_type".
						",:_outlet".
						",:_register".
						",:_type".
						",:_id".
						",:_title".
						",:_status".
						",:_transaction_date".
						",:_created_at".
						",:_updated_at".
						",:_updated_at_utc".
						",:_transaction_timezone".
						",:_transaction_timezone_offset".
						",:_resync".
						",:_integrate".
					");"
				);
				$stmt->bindParam(':_channel', 						$channel, 										PDO::PARAM_STR,255); 
				$stmt->bindParam(':_source', 						$source, 										PDO::PARAM_STR,255); 
				$stmt->bindParam(':_source_instance', 				$data[$_FIELD_MAPPING_["_source_instance"]], 	PDO::PARAM_STR,255); 
				$stmt->bindParam(':_location', 						$data[$_FIELD_MAPPING_["_location"]], 			PDO::PARAM_STR,256); 
				$stmt->bindParam(':_location_type', 				$data[$_FIELD_MAPPING_["_location_type"]], 		PDO::PARAM_STR,50);
				$stmt->bindParam(':_outlet', 						$data[$_FIELD_MAPPING_["_outlet"]], 			PDO::PARAM_STR,256); 
				$stmt->bindParam(':_register', 						$data[$_FIELD_MAPPING_["_register"]], 			PDO::PARAM_STR,256); 
				$stmt->bindParam(':_type', 							$data[$_FIELD_MAPPING_["_type"]], 				PDO::PARAM_STR,50); 
				$stmt->bindParam(':_id', 							$data[$_FIELD_MAPPING_["_id"]], 				PDO::PARAM_STR,100); 
				$stmt->bindParam(':_title', 						$data[$_FIELD_MAPPING_["_title"]], 				PDO::PARAM_STR,100); 
				$stmt->bindParam(':_status', 						$data[$_FIELD_MAPPING_["_status"]], 			PDO::PARAM_STR,50); 
				$stmt->bindParam(':_transaction_date', 				$data[$_FIELD_MAPPING_["_transaction_date"]]); 
				$stmt->bindParam(':_created_at', 					$data[$_FIELD_MAPPING_["_created_at"]]); 
				$stmt->bindParam(':_updated_at', 					$data[$_FIELD_MAPPING_["_updated_at"]]);
				$stmt->bindParam(':_updated_at_utc', 				$data[$_FIELD_MAPPING_["_updated_at_utc"]]);
				$stmt->bindParam(':_transaction_timezone', 			$data[$_FIELD_MAPPING_["_transaction_timezone"]]);
				$stmt->bindParam(':_transaction_timezone_offset', 	$offset);
				$stmt->bindParam(':_resync', 						$data[$_FIELD_MAPPING_["_resync"]],				PDO::PARAM_BOOL); 
				$stmt->bindParam(':_integrate', 					$data[$_FIELD_MAPPING_["_integrate"]],			PDO::PARAM_BOOL); 
				$stmt->execute();
				
				// Parse record set
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					array_push($results, $row);
				}
				
				$stmt->closeCursor();
			}

			// Return input body as results to confirm success
			return 	$response->withStatus(200)
						->withHeader("Content-Type","application/json")
						->write(json_encode($results, JSON_NUMERIC_CHECK));
		
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

/**
 * Run the Slim application
 */
$app->run();
