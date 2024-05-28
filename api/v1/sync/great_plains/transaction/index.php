<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-put.php';
require 'validate-delete.php';
require 'mapping.php';
require 'tokens.php';

define("_GET_PROC_", "sync_great_plains_transaction_query_v1");
define("_PUT_PROC_", "sync_great_plains_transaction_upsert_v1");

/** PAGINATION **/
define("_DEFAULT_PAGE_LIMIT_", 100);
define("_MAX_PAGE_LIMIT_", 1000);
/**/

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
 
/** 
/*	AUTH Token Authentication MiddleWare 
/**/
$app->add(function ($request, $response, $next) {
	try {
		$token = $request->getHeader("Authorization");
		$token = is_array($token) ? $token[0] : "";
	
		if( !array_key_exists($token, $GLOBALS["_TOKENS_"]) )
			return $response->withStatus(403)
							->withHeader("Content-Type","application/json")
							->write(json_encode("Access not allowed for token [".$token."]"))
			;

		return $next($request, $response);
		
	} catch (Exception $e) {
		return 	$response->withStatus(500)
						->withHeader("Content-Type","application/json")
						->write(json_encode(array(
					"ErrorCode"	=>	500,
					"Exception"	=>	$e->getMessage()
				), JSON_NUMERIC_CHECK));
	}
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
    			 $_GET_FIELD_MAPPING_["_batch_hash"] => $request->getParam($_GET_FIELD_MAPPING_["_batch_hash"])
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

			// DB Settings
			$host 	= _HOST_URI_;
			$dbname = _HOST_DB_;
			$usr 	= _HOST_USER_;
			$pwd 	= _HOST_PASSWORD_;

			$proc 	= _GET_PROC_;
        	
        	$results = array();

			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_batch_hash".
				");"
			);
			$stmt->bindParam(':_batch_hash', $data[$_GET_FIELD_MAPPING_["_batch_hash"]],PDO::PARAM_STR,64);
			$stmt->execute();
				
			// Parse record set
			$row = $stmt->fetch();
			if($row && count($row) && $row[0] != null ) {
				$results = json_decode($row[0]);
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
    	try {
   			return 	__method_not_allowed("POST", $response);
		} catch (PDOException $e) {
			return 	$response->withStatus(500)
							->withHeader("Content-Type","application/json")
							->write(json_encode(array(
						"ErrorCode"	=>	500,
						"Exception"	=>	$e->getMessage()
					), JSON_NUMERIC_CHECK));
		}
    }
);

// PUT route
$app->put(
    '/',
    function ($request, $response, $args) use ($app) {
    	// Import field mapping
    	global $_PUT_FIELD_MAPPING_;
    	
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

			// DB Settings
			$host 	= _HOST_URI_;
			$dbname = _HOST_DB_;
			$usr 	= _HOST_USER_;
			$pwd 	= _HOST_PASSWORD_;

			$proc 	= _PUT_PROC_;
	
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$results = array();
			
			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_batch_hash".
					",:_great_plains_transaction_id".
					",:_great_plains_transaction_type".
					",:_great_plains_transaction_sub_type".
					",:_great_plains_transaction_title".
					",:_great_plains_transaction_status".
				");"
			);
			$stmt->bindParam(':_batch_hash', 						$data[$_PUT_FIELD_MAPPING_["_batch_hash"]], PDO::PARAM_STR,64); 
			$stmt->bindParam(':_great_plains_transaction_id', 		$data[$_PUT_FIELD_MAPPING_["_great_plains_transaction_id"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_great_plains_transaction_type', 	$data[$_PUT_FIELD_MAPPING_["_great_plains_transaction_type"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_great_plains_transaction_sub_type', $data[$_PUT_FIELD_MAPPING_["_great_plains_transaction_sub_type"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_great_plains_transaction_title', 	$data[$_PUT_FIELD_MAPPING_["_great_plains_transaction_title"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_great_plains_transaction_status', 	$data[$_PUT_FIELD_MAPPING_["_great_plains_transaction_status"]], PDO::PARAM_STR,256);
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

// DELETE route
$app->delete(
    '/',
    function ($request, $response, $args) use ($app) {
    	try {
   			return 	__method_not_allowed("DELETE", $response);
		} catch (PDOException $e) {
			return 	$response->withStatus(500)
							->withHeader("Content-Type","application/json")
							->write(json_encode(array(
						"ErrorCode"	=>	500,
						"Exception"	=>	$e->getMessage()
					), JSON_NUMERIC_CHECK));
		}
    }
);

/**
 * Run the Slim application
 */
$app->run();
