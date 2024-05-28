<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-put.php';
require 'validate-delete.php';
require 'mapping.php';
require 'tokens.php';

define("_GET_PROC_", "customer_query_v1");
define("_POST_PROC_", "customer_upsert_v1");
define("_PUT_PROC_", "customer_upsert_v1");
define("_DELETE_PROC_", "customer_delete_v1");

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

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
global $log;
$log = new Logger('logger');
$log->pushHandler(new StreamHandler('logs/app.log'));

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
/*	API Rate Limiter MiddleWare 
/**/
$app->add(function ($request, $response, $next) {

	$host 	= _HOST_URI_;
	$dbname = _HOST_DB_;
	$usr 	= _HOST_USER_;
	$pwd 	= _HOST_PASSWORD_;

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
    			 $_GET_FIELD_MAPPING_["_id"] => $request->getParam($_GET_FIELD_MAPPING_["_id"])
				,$_GET_FIELD_MAPPING_["_hash"] => $request->getParam($_GET_FIELD_MAPPING_["_hash"])
				,$_GET_FIELD_MAPPING_["_channel"] => $request->getParam($_GET_FIELD_MAPPING_["_channel"])
				,$_GET_FIELD_MAPPING_["_source"] => $request->getParam($_GET_FIELD_MAPPING_["_source"])
				,$_GET_FIELD_MAPPING_["_source_instance"] => $request->getParam($_GET_FIELD_MAPPING_["_source_instance"])
				,$_GET_FIELD_MAPPING_["_type"] => $request->getParam($_GET_FIELD_MAPPING_["_type"])
				,$_GET_FIELD_MAPPING_["_sub_type"] => $request->getParam($_GET_FIELD_MAPPING_["_sub_type"])
				,$_GET_FIELD_MAPPING_["_email"] => $request->getParam($_GET_FIELD_MAPPING_["_email"])
				,$_GET_FIELD_MAPPING_["_status"] => $request->getParam($_GET_FIELD_MAPPING_["_status"])
				,$_GET_FIELD_MAPPING_["_customer_no"] => $request->getParam($_GET_FIELD_MAPPING_["_customer_no"])
				,$_GET_FIELD_MAPPING_["_membership_no"] => $request->getParam($_GET_FIELD_MAPPING_["_membership_no"])
				,$_GET_FIELD_MAPPING_["_debtor_id"] => $request->getParam($_GET_FIELD_MAPPING_["_debtor_id"])
				,$_GET_FIELD_MAPPING_["_firstname"] => $request->getParam($_GET_FIELD_MAPPING_["_firstname"])		
				,$_GET_FIELD_MAPPING_["_lastname"] => $request->getParam($_GET_FIELD_MAPPING_["_lastname"])
				,$_GET_FIELD_MAPPING_["_contact_no"] => $request->getParam($_GET_FIELD_MAPPING_["_contact_no"])
				,$_GET_FIELD_MAPPING_["_integrate"] => $request->getParam($_GET_FIELD_MAPPING_["_integrate"])
				,$_GET_FIELD_MAPPING_["_active"] => $request->getParam($_GET_FIELD_MAPPING_["_active"])
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

			$active = $data[$_GET_FIELD_MAPPING_["_active"]] != null ? $data[$_GET_FIELD_MAPPING_["_active"]] === true || strcmp($data[$_GET_FIELD_MAPPING_["_active"]], "true") == 0 ? true : false : null;
			$integrate = $data[$_GET_FIELD_MAPPING_["_integrate"]] != null ? $data[$_GET_FIELD_MAPPING_["_integrate"]] === true || strcmp($data[$_GET_FIELD_MAPPING_["_integrate"]], "true") == 0 ? true : false : null;
			$asc = $data[$_GET_FIELD_MAPPING_["_asc"]] === true || strcmp($data[$_GET_FIELD_MAPPING_["_asc"]], "true") == 0 ? true : false;

			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_id".
					",:_hash".
					",:_channel".
					",:_source".
					",:_source_instance".
					",:_source_id".
					",:_type".
					",:_sub_type".
					",:_email".
					",:_status".
					",:_customer_no".
					",:_membership_no".
					",:_debtor_id".
					",:_firstname".
					",:_lastname".
					",:_contact_no".
					",:_active".
					",:_integrate".
					",:_since".
					",:_order_by".
					",:_asc".
					",:__limit".
					",:__page".
					",@total_records".
				");"
			);
			$stmt->bindParam(':_id',	 						$data[$_GET_FIELD_MAPPING_["_id"]],					PDO::PARAM_STR,36);
			$stmt->bindParam(':_hash',	 						$data[$_GET_FIELD_MAPPING_["_hash"]],				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_channel', 						$data[$_GET_FIELD_MAPPING_["_channel"]], 			PDO::PARAM_STR,64); 
			$stmt->bindParam(':_source', 						$data[$_GET_FIELD_MAPPING_["_source"]], 			PDO::PARAM_STR,64); 
			$stmt->bindParam(':_source_instance', 				$data[$_GET_FIELD_MAPPING_["_source_instance"]], 	PDO::PARAM_STR,64); 
			$stmt->bindParam(':_source_id', 					$data[$_GET_FIELD_MAPPING_["_source_id"]], 			PDO::PARAM_STR,36); 
			$stmt->bindParam(':_type', 							$data[$_GET_FIELD_MAPPING_["_type"]], 				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_sub_type', 						$data[$_GET_FIELD_MAPPING_["_sub_type"]], 			PDO::PARAM_STR,64); 
			$stmt->bindParam(':_email', 						$data[$_GET_FIELD_MAPPING_["_email"]], 				PDO::PARAM_STR,64); 
			$stmt->bindParam(':_status', 						$data[$_GET_FIELD_MAPPING_["_status"]], 			PDO::PARAM_STR,64); 
			$stmt->bindParam(':_customer_no', 					$data[$_GET_FIELD_MAPPING_["_customer_no"]], 		PDO::PARAM_STR,128); 
			$stmt->bindParam(':_membership_no', 				$data[$_GET_FIELD_MAPPING_["_membership_no"]], 		PDO::PARAM_STR,256); 
			$stmt->bindParam(':_debtor_id', 					$data[$_GET_FIELD_MAPPING_["_debtor_id"]], 			PDO::PARAM_STR,256);
			$stmt->bindParam(':_firstname', 					$data[$_GET_FIELD_MAPPING_["_firstname"]], 			PDO::PARAM_STR,256); 
			$stmt->bindParam(':_lastname', 						$data[$_GET_FIELD_MAPPING_["_lastname"]], 			PDO::PARAM_STR,256); 
			$stmt->bindParam(':_contact_no', 					$data[$_GET_FIELD_MAPPING_["_contact_no"]], 		PDO::PARAM_STR,256); 
			$stmt->bindParam(':_active', 						$active,	PDO::PARAM_BOOL); 
			$stmt->bindParam(':_integrate', 					$integrate,	PDO::PARAM_BOOL); 
			$stmt->bindParam(':_since', 						$data[$_GET_FIELD_MAPPING_["_since"]]);
			$stmt->bindParam(':_order_by', 						$data[$_GET_FIELD_MAPPING_["_order_by"]]);
			$stmt->bindParam(':_asc', 							$asc,	PDO::PARAM_BOOL);
			$stmt->bindParam(":__limit", 						$limit);
			$stmt->bindParam(":__page", 						$page);
			$stmt->execute();
			
			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				// Expand JSON data
				if( $row && array_key_exists("schemes", $row) ) {
					$row["schemes"] = json_decode($row["schemes"], true);
				}
				
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
    	global $log;
    	
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

			// Use the updated date as the seed for determining the offset with DST in consideration
			$trxdate = $data[$_POST_FIELD_MAPPING_["_updated_at"]];
			$tz = $data[$_POST_FIELD_MAPPING_["_timezone"]];
			$offset = null;
			
			if($tz) { 
				$dtz = new DateTimeZone($tz);
				$time = new DateTime($trxdate, $dtz);
				$offset = $dtz->getOffset( $time );
			}

			$schemes = json_encode($data[$_POST_FIELD_MAPPING_["_schemes"]]);
			
			$accepts_marketing = $data[$_POST_FIELD_MAPPING_["_accepts_marketing"]] === true || strcmp($data[$_POST_FIELD_MAPPING_["_accepts_marketing"]], "true") == 0 ? true : false;
			$accepts_comms = $data[$_POST_FIELD_MAPPING_["_accepts_communications"]] === true || strcmp($data[$_POST_FIELD_MAPPING_["_accepts_communications"]], "true") == 0 ? true : false;
			$active = $data[$_POST_FIELD_MAPPING_["_active"]] === true || strcmp($data[$_POST_FIELD_MAPPING_["_active"]], "true") == 0 ? true : false;
			$integrate = $data[$_POST_FIELD_MAPPING_["_integrate"]] === true || strcmp($data[$_POST_FIELD_MAPPING_["_integrate"]], "true") == 0 ? true : false;
			
			$null = null;

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_id".
					",:_hash".		
					",:_channel".
					",:_source".
					",:_source_instance".
					",:_source_id".
					",:_type".
					",:_sub_type".
					",:_email".
					",:_status".
					",:_customer_no".
					",:_membership_no".
					",:_debtor_id".
					",:_firstname".
					",:_lastname".
					",:_othernames".
					",:_contact_no".
					",:_company".
					",:_schemes".
					",:_device_fingerprint".
					",:_device_ip_address".
					",:_advertising_id".
					",:_accepts_marketing".
					",:_accepts_communications".
					",:_timezone".
					",:_timezone_offset".
					",:_active".
					",:_active_at".
					",:_activated_at".
					",:_created_at".
					",:_updated_at".
					",:_updated_at_utc".
					",:_monotonic_version".
					",:_version_hash".
					",:_integrate".
				");"
			);
			$stmt->bindParam(':_id', 						$null);
			$stmt->bindParam(':_hash', 						$data[$_POST_FIELD_MAPPING_["_hash"]], 						PDO::PARAM_STR,64);
			$stmt->bindParam(':_channel', 					$data[$_POST_FIELD_MAPPING_["_channel"]], 					PDO::PARAM_STR,64);
			$stmt->bindParam(':_source', 					$data[$_POST_FIELD_MAPPING_["_source"]], 					PDO::PARAM_STR,64);
			$stmt->bindParam(':_source_instance', 			$data[$_POST_FIELD_MAPPING_["_source_instance"]], 			PDO::PARAM_STR,64);
			$stmt->bindParam(':_source_id', 				$data[$_POST_FIELD_MAPPING_["_source_id"]], 				PDO::PARAM_STR,36);
			$stmt->bindParam(':_type', 						$data[$_POST_FIELD_MAPPING_["_type"]], 						PDO::PARAM_STR,256);
			$stmt->bindParam(':_sub_type', 					$data[$_POST_FIELD_MAPPING_["_sub_type"]], 					PDO::PARAM_STR,256);
			$stmt->bindParam(':_email', 					$data[$_POST_FIELD_MAPPING_["_email"]], 					PDO::PARAM_STR,256);
			$stmt->bindParam(':_status', 					$data[$_POST_FIELD_MAPPING_["_status"]], 					PDO::PARAM_STR,256);
			$stmt->bindParam(':_customer_no', 				$data[$_POST_FIELD_MAPPING_["_customer_no"]], 				PDO::PARAM_STR,256);
			$stmt->bindParam(':_membership_no', 			$data[$_POST_FIELD_MAPPING_["_membership_no"]], 			PDO::PARAM_STR,256);
			$stmt->bindParam(':_debtor_id', 				$data[$_POST_FIELD_MAPPING_["_debtor_id"]], 				PDO::PARAM_STR,256);
			$stmt->bindParam(':_firstname', 				$data[$_POST_FIELD_MAPPING_["_firstname"]], 				PDO::PARAM_STR,256);
			$stmt->bindParam(':_lastname', 					$data[$_POST_FIELD_MAPPING_["_lastname"]], 					PDO::PARAM_STR,256);
			$stmt->bindParam(':_othernames', 				$data[$_POST_FIELD_MAPPING_["_othernames"]], 				PDO::PARAM_STR,256);
			$stmt->bindParam(':_contact_no', 				$data[$_POST_FIELD_MAPPING_["_contact_no"]], 				PDO::PARAM_STR,256);
			$stmt->bindParam(':_company', 					$data[$_POST_FIELD_MAPPING_["_company"]], 					PDO::PARAM_STR,256);
			$stmt->bindParam(':_schemes', 					$schemes);
			$stmt->bindParam(':_device_fingerprint', 		$data[$_POST_FIELD_MAPPING_["_device_fingerprint"]], 		PDO::PARAM_STR,256);
			$stmt->bindParam(':_device_ip_address', 		$data[$_POST_FIELD_MAPPING_["_device_ip_address"]], 		PDO::PARAM_STR,256);
			$stmt->bindParam(':_advertising_id', 			$data[$_POST_FIELD_MAPPING_["_advertising_id"]], 			PDO::PARAM_STR,256);
			$stmt->bindParam(':_accepts_marketing', 		$accepts_marketing,	PDO::PARAM_BOOL); 
			$stmt->bindParam(':_accepts_communications', 	$accepts_comms,	PDO::PARAM_BOOL); 
			$stmt->bindParam(':_timezone', 					$data[$_POST_FIELD_MAPPING_["_timezone"]]);
			$stmt->bindParam(':_timezone_offset', 			$offset);
			$stmt->bindParam(':_active', 					$active,	PDO::PARAM_BOOL); 
			$stmt->bindParam(':_active_at', 				$data[$_POST_FIELD_MAPPING_["_active_at"]]);
			$stmt->bindParam(':_activated_at', 				$data[$_POST_FIELD_MAPPING_["_activated_at"]]);
			$stmt->bindParam(':_created_at', 				$data[$_POST_FIELD_MAPPING_["_created_at"]]);
			$stmt->bindParam(':_updated_at', 				$data[$_POST_FIELD_MAPPING_["_updated_at"]]);
			$stmt->bindParam(':_updated_at_utc', 			$data[$_POST_FIELD_MAPPING_["_updated_at_utc"]]);
			$stmt->bindParam(':_monotonic_version', 		$data[$_POST_FIELD_MAPPING_["_monotonic_version"]]);
			$stmt->bindParam(':_version_hash', 				$data[$_POST_FIELD_MAPPING_["_version_hash"]]);
			$stmt->bindParam(':_integrate', 				$integrate,	PDO::PARAM_BOOL);
			$stmt->execute();

			// Parse record set
			$results = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if( $results && array_key_exists("schemes", $results) ) {
				$results["schemes"] = json_decode($results["schemes"], true);
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
    	global $log;

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

			// Use the updated date as the seed for determining the offset with DST in consideration
			$trxdate = $data[$_PUT_FIELD_MAPPING_["_updated_at"]];
			$tz = $data[$_PUT_FIELD_MAPPING_["_timezone"]];
			$offset = null;
			
			if($tz) { 
				$dtz = new DateTimeZone($tz);
				$time = new DateTime($trxdate, $dtz);
				$offset = $dtz->getOffset( $time );
			}

			$schemes = json_encode($data[$_PUT_FIELD_MAPPING_["_schemes"]]);

			$accepts_marketing = isset($data[$_PUT_FIELD_MAPPING_["_accepts_marketing"]]) ? $data[$_PUT_FIELD_MAPPING_["_accepts_marketing"]] === true || strcmp($data[$_PUT_FIELD_MAPPING_["_accepts_marketing"]], "true") == 0 ? true : false : null;
			$accepts_comms = isset($data[$_PUT_FIELD_MAPPING_["_accepts_communications"]]) ? $data[$_PUT_FIELD_MAPPING_["_accepts_communications"]] === true || strcmp($data[$_PUT_FIELD_MAPPING_["_accepts_communications"]], "true") == 0 ? true : false : null;
			$active = isset($data[$_PUT_FIELD_MAPPING_["_active"]]) ? $data[$_PUT_FIELD_MAPPING_["_active"]] === true || strcmp($data[$_PUT_FIELD_MAPPING_["_active"]], "true") == 0 ? true : false : null;
			$integrate = isset($data[$_PUT_FIELD_MAPPING_["_integrate"]]) ? $data[$_PUT_FIELD_MAPPING_["_integrate"]] === true || strcmp($data[$_PUT_FIELD_MAPPING_["_integrate"]], "true") == 0 ? true : false : null;

			$null = null;
			
			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_id".
					",:_hash".		
					",:_channel".
					",:_source".
					",:_source_instance".
					",:_source_id".
					",:_type".
					",:_sub_type".
					",:_email".
					",:_status".
					",:_customer_no".
					",:_membership_no".
					",:_debtor_id".
					",:_firstname".
					",:_lastname".
					",:_othernames".
					",:_contact_no".
					",:_company".
					",:_schemes".
					",:_device_fingerprint".
					",:_device_ip_address".
					",:_advertising_id".
					",:_accepts_marketing".
					",:_accepts_communications".
					",:_timezone".
					",:_timezone_offset".
					",:_active".
					",:_active_at".
					",:_activated_at".
					",:_created_at".
					",:_updated_at".
					",:_updated_at_utc".
					",:_monotonic_version".
					",:_version_hash".
					",:_integrate".
				");"
			);
			$stmt->bindParam(':_id', 						$data[$_PUT_FIELD_MAPPING_["_id"]], 					PDO::PARAM_STR,36);
			$stmt->bindParam(':_hash', 						$data[$_PUT_FIELD_MAPPING_["_hash"]], 					PDO::PARAM_STR,64);
			$stmt->bindParam(':_channel', 					$data[$_PUT_FIELD_MAPPING_["_channel"]], 				PDO::PARAM_STR,64);
			$stmt->bindParam(':_source', 					$data[$_PUT_FIELD_MAPPING_["_source"]], 				PDO::PARAM_STR,64);
			$stmt->bindParam(':_source_instance', 			$data[$_PUT_FIELD_MAPPING_["_source_instance"]], 		PDO::PARAM_STR,64);
			$stmt->bindParam(':_source_id', 				$data[$_PUT_FIELD_MAPPING_["_source_id"]], 				PDO::PARAM_STR,36);
			$stmt->bindParam(':_type', 						$data[$_PUT_FIELD_MAPPING_["_type"]], 					PDO::PARAM_STR,256);
			$stmt->bindParam(':_sub_type', 					$data[$_PUT_FIELD_MAPPING_["_sub_type"]], 				PDO::PARAM_STR,256);
			$stmt->bindParam(':_email', 					$data[$_PUT_FIELD_MAPPING_["_email"]], 					PDO::PARAM_STR,256);
			$stmt->bindParam(':_status', 					$data[$_PUT_FIELD_MAPPING_["_status"]], 				PDO::PARAM_STR,256);
			$stmt->bindParam(':_customer_no', 				$data[$_PUT_FIELD_MAPPING_["_customer_no"]], 			PDO::PARAM_STR,256);
			$stmt->bindParam(':_membership_no', 			$data[$_PUT_FIELD_MAPPING_["_membership_no"]], 			PDO::PARAM_STR,256);
			$stmt->bindParam(':_debtor_id', 				$data[$_PUT_FIELD_MAPPING_["_debtor_id"]], 				PDO::PARAM_STR,256);
			$stmt->bindParam(':_firstname', 				$data[$_PUT_FIELD_MAPPING_["_firstname"]], 				PDO::PARAM_STR,256);
			$stmt->bindParam(':_lastname', 					$data[$_PUT_FIELD_MAPPING_["_lastname"]], 				PDO::PARAM_STR,256);
			$stmt->bindParam(':_othernames', 				$data[$_PUT_FIELD_MAPPING_["_othernames"]], 			PDO::PARAM_STR,256);
			$stmt->bindParam(':_contact_no', 				$data[$_PUT_FIELD_MAPPING_["_contact_no"]], 			PDO::PARAM_STR,256);
			$stmt->bindParam(':_company', 					$data[$_PUT_FIELD_MAPPING_["_company"]], 				PDO::PARAM_STR,256);
			$stmt->bindParam(':_schemes', 					$schemes);
			$stmt->bindParam(':_device_fingerprint', 		$data[$_PUT_FIELD_MAPPING_["_device_fingerprint"]], 	PDO::PARAM_STR,256);
			$stmt->bindParam(':_device_ip_address', 		$data[$_PUT_FIELD_MAPPING_["_device_ip_address"]], 		PDO::PARAM_STR,256);
			$stmt->bindParam(':_advertising_id', 			$data[$_PUT_FIELD_MAPPING_["_advertising_id"]], 		PDO::PARAM_STR,256);
			$stmt->bindParam(':_accepts_marketing', 		$accepts_marketing,	PDO::PARAM_BOOL); 
			$stmt->bindParam(':_accepts_communications', 	$accepts_comms,	PDO::PARAM_BOOL); 
			$stmt->bindParam(':_timezone', 					$data[$_PUT_FIELD_MAPPING_["_timezone"]]);
			$stmt->bindParam(':_timezone_offset', 			$offset);
			$stmt->bindParam(':_active', 					$active,	PDO::PARAM_BOOL); 
			$stmt->bindParam(':_active_at', 				$data[$_PUT_FIELD_MAPPING_["_active_at"]]);
			$stmt->bindParam(':_activated_at', 				$data[$_PUT_FIELD_MAPPING_["_activated_at"]]);
			$stmt->bindParam(':_created_at', 				$data[$_PUT_FIELD_MAPPING_["_created_at"]]);
			$stmt->bindParam(':_updated_at', 				$data[$_PUT_FIELD_MAPPING_["_updated_at"]]);
			$stmt->bindParam(':_updated_at_utc', 			$data[$_PUT_FIELD_MAPPING_["_updated_at_utc"]]);
			$stmt->bindParam(':_monotonic_version', 		$data[$_PUT_FIELD_MAPPING_["_monotonic_version"]]);
			$stmt->bindParam(':_version_hash', 				$data[$_PUT_FIELD_MAPPING_["_version_hash"]]);
			$stmt->bindParam(':_integrate', 				$integrate,	PDO::PARAM_BOOL);
			$stmt->execute();
			
			// Parse record set
			$results = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if( $results && array_key_exists("schemes", $results) ) {
				$results["schemes"] = json_decode($results["schemes"], true);
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
					 ",:_hash".
				");"
			);
			$stmt->bindParam(':_id', $data[$_DELETE_FIELD_MAPPING_["_id"]], PDO::PARAM_STR,36); 
			$stmt->bindParam(':_hash', $data[$_DELETE_FIELD_MAPPING_["_hash"]], PDO::PARAM_STR,64); 
			$stmt->execute();
			
			// Parse record set
			$results = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if( $results && array_key_exists("schemes", $results) ) {
				$results["schemes"] = json_decode($results["schemes"], true);
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
