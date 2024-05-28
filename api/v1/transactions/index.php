<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-put.php';
require 'validate-delete.php';
require 'mapping.php';
require 'tokens.php';

define("_GET_PROC_", "transaction_query_v1");
define("_POST_PROC_", "transaction_upsert_v5");
define("_PUT_PROC_", "transaction_upsert_v5");
define("_DELETE_PROC_", "transaction_delete_v1");

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
    			 $_GET_FIELD_MAPPING_["_id"] => $request->getParam($_GET_FIELD_MAPPING_["_id"])
				,$_GET_FIELD_MAPPING_["_channel"] => $request->getParam($_GET_FIELD_MAPPING_["_channel"])
				,$_GET_FIELD_MAPPING_["_source"] => $request->getParam($_GET_FIELD_MAPPING_["_source"])
				,$_GET_FIELD_MAPPING_["_source_instance"] => $request->getParam($_GET_FIELD_MAPPING_["_source_instance"])
				,$_GET_FIELD_MAPPING_["_type"] => $request->getParam($_GET_FIELD_MAPPING_["_type"])
				,$_GET_FIELD_MAPPING_["_sub_type"] => $request->getParam($_GET_FIELD_MAPPING_["_sub_type"])
				,$_GET_FIELD_MAPPING_["_transaction_parent_id"] => $request->getParam($_GET_FIELD_MAPPING_["_transaction_parent_id"])
				,$_GET_FIELD_MAPPING_["_transaction_id"] => $request->getParam($_GET_FIELD_MAPPING_["_transaction_id"])
				,$_GET_FIELD_MAPPING_["_title"] => $request->getParam($_GET_FIELD_MAPPING_["_title"])
				,$_GET_FIELD_MAPPING_["_status"] => $request->getParam($_GET_FIELD_MAPPING_["_status"])
				,$_GET_FIELD_MAPPING_["_location"] => $request->getParam($_GET_FIELD_MAPPING_["_location"])
				,$_GET_FIELD_MAPPING_["_location_type"] => $request->getParam($_GET_FIELD_MAPPING_["_location_type"])
				,$_GET_FIELD_MAPPING_["_integrate"] => $request->getParam($_GET_FIELD_MAPPING_["_integrate"])
				,$_GET_FIELD_MAPPING_["_date_from"] => $request->getParam($_GET_FIELD_MAPPING_["_date_from"])
				,$_GET_FIELD_MAPPING_["_date_to"] => $request->getParam($_GET_FIELD_MAPPING_["_date_to"])
				,$_GET_FIELD_MAPPING_["_since"] => $request->getParam($_GET_FIELD_MAPPING_["_since"])
				,$_GET_FIELD_MAPPING_["_order_by"] => $request->getParam($_GET_FIELD_MAPPING_["_order_by"])
				,$_GET_FIELD_MAPPING_["_asc"] => $request->getParam($_GET_FIELD_MAPPING_["_asc"])
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
			
			/** PAGINATION **/
        	$page = $request->getParam("page") ? $request->getParam("page") : 1;
			$page = !is_numeric($page) ? 1 : $page;
			$page = $page <= 0 ? 1 : $page;
			
			$limit = $request->getParam("limit") ? $request->getParam("limit") : _DEFAULT_PAGE_LIMIT_;
			$limit = !is_numeric($limit) ? _DEFAULT_PAGE_LIMIT_ : $limit;
			$limit = $limit <= 0 ? _DEFAULT_PAGE_LIMIT_ : $limit > _MAX_PAGE_LIMIT_ ? _MAX_PAGE_LIMIT_ : $limit;
        	
        	$results = array(
				"pagination" => array(
					 "total_records" => 0
					,"total_pages" => 0
					,"page_records" => 0
					,"page" => $page*1
					,"limit" => $limit*1
				),
				"records" => array()
			);
        	/****/

			$integrate = $data[$_GET_FIELD_MAPPING_["_integrate"]] != null ? $data[$_GET_FIELD_MAPPING_["_integrate"]] === true || strcmp($data[$_GET_FIELD_MAPPING_["_integrate"]], "true") == 0 ? true : false : null;
			$asc = $data[$_GET_FIELD_MAPPING_["_asc"]] === true || strcmp($data[$_GET_FIELD_MAPPING_["_asc"]], "true") == 0 ? true : false;

			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_id".
					",:_channel".
					",:_source".
					",:_source_instance".
					",:_type".
					",:_sub_type".
					",:_transaction_parent_id".
					",:_transaction_id".
					",:_title".
					",:_status".
					",:_location_type".
					",:_location".
					",:_integrate".
					",:_date_from".
					",:_date_to".
					",:_since".
					",:_order_by".
					",:_asc".
					",:__limit".
					",:__page".
					",@total_records".
				");"
			);
			$stmt->bindParam(':_id',	 						$data[$_GET_FIELD_MAPPING_["_id"]],						PDO::PARAM_STR,36); 
			$stmt->bindParam(':_channel', 						$data[$_GET_FIELD_MAPPING_["_channel"]], 				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_source', 						$data[$_GET_FIELD_MAPPING_["_source"]], 				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_source_instance', 				$data[$_GET_FIELD_MAPPING_["_source_instance"]], 		PDO::PARAM_STR,64); 
			$stmt->bindParam(':_type', 							$data[$_GET_FIELD_MAPPING_["_type"]], 					PDO::PARAM_STR,64); 
			$stmt->bindParam(':_sub_type', 						$data[$_GET_FIELD_MAPPING_["_sub_type"]], 				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_transaction_parent_id', 		$data[$_GET_FIELD_MAPPING_["_transaction_parent_id"]], 	PDO::PARAM_STR,64); 
			$stmt->bindParam(':_transaction_id', 				$data[$_GET_FIELD_MAPPING_["_transaction_id"]], 		PDO::PARAM_STR,64); 
			$stmt->bindParam(':_title', 						$data[$_GET_FIELD_MAPPING_["_title"]], 					PDO::PARAM_STR,128); 
			$stmt->bindParam(':_status', 						$data[$_GET_FIELD_MAPPING_["_status"]], 				PDO::PARAM_STR,256); 
			$stmt->bindParam(':_location_type', 				$data[$_GET_FIELD_MAPPING_["_location_type"]], 			PDO::PARAM_STR,256);
			$stmt->bindParam(':_location', 						$data[$_GET_FIELD_MAPPING_["_location"]], 				PDO::PARAM_STR,256); 
			$stmt->bindParam(':_integrate', 					$integrate,	PDO::PARAM_BOOL); 
			$stmt->bindParam(':_date_from', 					$data[$_GET_FIELD_MAPPING_["_date_from"]]);
			$stmt->bindParam(':_date_to', 						$data[$_GET_FIELD_MAPPING_["_date_to"]]);
			$stmt->bindParam(':_since', 						$data[$_GET_FIELD_MAPPING_["_since"]]);
			$stmt->bindParam(':_order_by', 						$data[$_GET_FIELD_MAPPING_["_order_by"]]);
			$stmt->bindParam(':_asc', 							$asc,	PDO::PARAM_BOOL);
			$stmt->bindParam(":__limit", 						$limit);
			$stmt->bindParam(":__page", 						$page);
			$stmt->execute();
			
			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				// Append record
				$results["records"][] = $row;
			}
			
			$stmt->closeCursor();
			
			/** PAGINATION **/
			// Capture output parameter
			$q = $conn->query("select @total_records as total_records;")->fetch(PDO::FETCH_ASSOC);
			$total_records = $q && array_key_exists("total_records", $q) ? $q["total_records"] : 1;
			$results["pagination"]["page_records"] = count($results["records"]);
			$results["pagination"]["total_records"] = $total_records * 1;
			$results["pagination"]["total_pages"] = ceil($total_records / $limit);
			/**/

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

			// DB Settings
			$host 	= _HOST_URI_;
			$dbname = _HOST_DB_;
			$usr 	= _HOST_USER_;
			$pwd 	= _HOST_PASSWORD_;

			$proc 	= _POST_PROC_;
	
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$results = array();

			// Use the transaction date as the seed for determining the offset with DST in consideration
			$trxdate = $data[$_POST_FIELD_MAPPING_["_transaction_date"]];
			$tz = $data[$_POST_FIELD_MAPPING_["_transaction_timezone"]];
			$offset = null;
			
			if($tz) { 
				$dtz = new DateTimeZone($tz);
				$time = new DateTime($trxdate, $dtz);
				$offset = $dtz->getOffset( $time );
			}

			$integrate = $data[$_POST_FIELD_MAPPING_["_integrate"]] === true || strcmp($data[$_POST_FIELD_MAPPING_["_integrate"]], "true") == 0 ? true : false;
			$null = null;

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_id".
					",:_channel".
					",:_source".
					",:_source_instance".
					",:_type".
					",:_sub_type".
					",:_transaction_parent_id".
					",:_transaction_id".
					",:_title".
					",:_status".
					",:_location_id".
					",:_location".
					",:_location_type".
					",:_outlet_id".
					",:_outlet".
					",:_register_id".
					",:_register".
					",:_transaction_timezone".
					",:_transaction_timezone_offset".
					",:_transaction_date".
					",:_created_at".
					",:_updated_at".
					",:_updated_at_utc".
					",:_monotonic_version".
					",:_version_hash".
					",:_integrate".
				");"
			);
			$stmt->bindParam(':_id', 							$null, 													PDO::PARAM_STR,36); 
			$stmt->bindParam(':_channel', 						$data[$_POST_FIELD_MAPPING_["_channel"]], 				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_source', 						$data[$_POST_FIELD_MAPPING_["_source"]], 				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_source_instance', 				$data[$_POST_FIELD_MAPPING_["_source_instance"]], 		PDO::PARAM_STR,64); 
			$stmt->bindParam(':_type', 							$data[$_POST_FIELD_MAPPING_["_type"]], 					PDO::PARAM_STR,64); 
			$stmt->bindParam(':_sub_type', 						$data[$_POST_FIELD_MAPPING_["_sub_type"]], 				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_transaction_parent_id', 		$data[$_POST_FIELD_MAPPING_["_transaction_parent_id"]], PDO::PARAM_STR,64); 
			$stmt->bindParam(':_transaction_id', 				$data[$_POST_FIELD_MAPPING_["_transaction_id"]], 		PDO::PARAM_STR,64); 
			$stmt->bindParam(':_title', 						$data[$_POST_FIELD_MAPPING_["_title"]], 				PDO::PARAM_STR,128); 
			$stmt->bindParam(':_status', 						$data[$_POST_FIELD_MAPPING_["_status"]], 				PDO::PARAM_STR,256); 
			$stmt->bindParam(':_location_id', 					$data[$_POST_FIELD_MAPPING_["_location_id"]], 			PDO::PARAM_STR,256); 
			$stmt->bindParam(':_location', 						$data[$_POST_FIELD_MAPPING_["_location"]], 				PDO::PARAM_STR,256); 
			$stmt->bindParam(':_location_type', 				$data[$_POST_FIELD_MAPPING_["_location_type"]], 		PDO::PARAM_STR,256);
			$stmt->bindParam(':_outlet_id', 					$data[$_POST_FIELD_MAPPING_["_outlet_id"]], 			PDO::PARAM_STR,256); 
			$stmt->bindParam(':_outlet', 						$data[$_POST_FIELD_MAPPING_["_outlet"]], 				PDO::PARAM_STR,256); 
			$stmt->bindParam(':_register_id', 					$data[$_POST_FIELD_MAPPING_["_register_id"]], 			PDO::PARAM_STR,256); 
			$stmt->bindParam(':_register', 						$data[$_POST_FIELD_MAPPING_["_register"]], 				PDO::PARAM_STR,256); 
			$stmt->bindParam(':_transaction_timezone', 			$data[$_POST_FIELD_MAPPING_["_transaction_timezone"]]);
			$stmt->bindParam(':_transaction_timezone_offset', 	$offset);
			$stmt->bindParam(':_transaction_date', 				$data[$_POST_FIELD_MAPPING_["_transaction_date"]]); 
			$stmt->bindParam(':_created_at', 					$data[$_POST_FIELD_MAPPING_["_created_at"]]); 
			$stmt->bindParam(':_updated_at', 					$data[$_POST_FIELD_MAPPING_["_updated_at"]]);
			$stmt->bindParam(':_updated_at_utc', 				$data[$_POST_FIELD_MAPPING_["_updated_at_utc"]]);
			$stmt->bindParam(':_monotonic_version', 			$data[$_POST_FIELD_MAPPING_["_monotonic_version"]]);
			$stmt->bindParam(':_version_hash', 					$data[$_POST_FIELD_MAPPING_["_version_hash"]]);
			$stmt->bindParam(':_integrate', 					$integrate,	PDO::PARAM_BOOL); 
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

			// Use the transaction date as the seed for determining the offset with DST in consideration
			$trxdate = $data[$_PUT_FIELD_MAPPING_["_transaction_date"]];
			$tz = $data[$_PUT_FIELD_MAPPING_["_transaction_timezone"]];
			$offset = null;
			
			if($tz) { 
				$dtz = new DateTimeZone($tz);
				$time = new DateTime($trxdate, $dtz);
				$offset = $dtz->getOffset( $time );
			}

			$integrate = $data[$_PUT_FIELD_MAPPING_["_integrate"]] === true || strcmp($data[$_PUT_FIELD_MAPPING_["_integrate"]], "true") == 0 ? true : false;

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_id".
					",:_channel".
					",:_source".
					",:_source_instance".
					",:_type".
					",:_sub_type".
					",:_transaction_parent_id".
					",:_transaction_id".
					",:_title".
					",:_status".
					",:_location_id".
					",:_location".
					",:_location_type".
					",:_outlet_id".
					",:_outlet".
					",:_register_id".
					",:_register".
					",:_transaction_timezone".
					",:_transaction_timezone_offset".
					",:_transaction_date".
					",:_created_at".
					",:_updated_at".
					",:_updated_at_utc".
					",:_monotonic_version".
					",:_version_hash".
					",:_integrate".
				");"
			);
			$stmt->bindParam(':_id',	 						$data[$_PUT_FIELD_MAPPING_["_id"]],						PDO::PARAM_STR,36); 
			$stmt->bindParam(':_channel', 						$data[$_PUT_FIELD_MAPPING_["_channel"]], 				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_source', 						$data[$_PUT_FIELD_MAPPING_["_source"]], 				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_source_instance', 				$data[$_PUT_FIELD_MAPPING_["_source_instance"]], 		PDO::PARAM_STR,64); 
			$stmt->bindParam(':_type', 							$data[$_PUT_FIELD_MAPPING_["_type"]], 					PDO::PARAM_STR,64); 
			$stmt->bindParam(':_sub_type', 						$data[$_PUT_FIELD_MAPPING_["_sub_type"]], 				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_transaction_parent_id', 		$data[$_PUT_FIELD_MAPPING_["_transaction_parent_id"]], 	PDO::PARAM_STR,64); 
			$stmt->bindParam(':_transaction_id', 				$data[$_PUT_FIELD_MAPPING_["_transaction_id"]], 		PDO::PARAM_STR,64); 
			$stmt->bindParam(':_title', 						$data[$_PUT_FIELD_MAPPING_["_title"]], 					PDO::PARAM_STR,128); 
			$stmt->bindParam(':_status', 						$data[$_PUT_FIELD_MAPPING_["_status"]], 				PDO::PARAM_STR,256); 
			$stmt->bindParam(':_location_id', 					$data[$_POST_FIELD_MAPPING_["_location_id"]], 			PDO::PARAM_STR,256); 
			$stmt->bindParam(':_location', 						$data[$_POST_FIELD_MAPPING_["_location"]], 				PDO::PARAM_STR,256); 
			$stmt->bindParam(':_location_type', 				$data[$_POST_FIELD_MAPPING_["_location_type"]], 		PDO::PARAM_STR,256);
			$stmt->bindParam(':_outlet_id', 					$data[$_POST_FIELD_MAPPING_["_outlet_id"]], 			PDO::PARAM_STR,256); 
			$stmt->bindParam(':_outlet', 						$data[$_POST_FIELD_MAPPING_["_outlet"]], 				PDO::PARAM_STR,256); 
			$stmt->bindParam(':_register_id', 					$data[$_POST_FIELD_MAPPING_["_register_id"]], 			PDO::PARAM_STR,256); 
			$stmt->bindParam(':_transaction_timezone', 			$data[$_PUT_FIELD_MAPPING_["_transaction_timezone"]]);
			$stmt->bindParam(':_transaction_timezone_offset', 	$offset);
			$stmt->bindParam(':_transaction_date', 				$data[$_PUT_FIELD_MAPPING_["_transaction_date"]]); 
			$stmt->bindParam(':_created_at', 					$data[$_PUT_FIELD_MAPPING_["_created_at"]]); 
			$stmt->bindParam(':_updated_at', 					$data[$_PUT_FIELD_MAPPING_["_updated_at"]]);
			$stmt->bindParam(':_updated_at_utc', 				$data[$_PUT_FIELD_MAPPING_["_updated_at_utc"]]);
			$stmt->bindParam(':_monotonic_version', 			$data[$_PUT_FIELD_MAPPING_["_monotonic_version"]]);
			$stmt->bindParam(':_version_hash', 					$data[$_PUT_FIELD_MAPPING_["_version_hash"]]);
			$stmt->bindParam(':_integrate', 					$integrate,	PDO::PARAM_BOOL); 
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
    	// Import field mapping
    	global $_DELETE_FIELD_MAPPING_;
    	global $_SOURCE_CHANNEL_MAPPING_;
    	
    	try {
    		$body = array(
    			 $_DELETE_FIELD_MAPPING_["_id"] => $args[$_DELETE_FIELD_MAPPING_["_id"]]
    		);
    		
    		$body = json_encode($body);
    	
			// Validate input
			$errs = validate_delete($body);
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

			$proc 	= _DELETE_PROC_;
	
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$results = array();

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_id".
				");"
			);
			$stmt->bindParam(':_id', $data[$_DELETE_FIELD_MAPPING_["_id"]], PDO::PARAM_STR,36); 
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

/**
 * Run the Slim application
 */
$app->run();
