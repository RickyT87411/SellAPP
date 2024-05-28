<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-put.php';
require 'validate-delete.php';
require 'mapping.php';
require 'tokens.php';

define("_GET_PROC_", "sync_great_plains_transaction_queue_v2");
define("_POST_PROC_", "sync_great_plains_transaction_queue_v2");
define("_PUT_PROC_", "sync_great_plains_transaction_queue_upsert_v1");

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
    			 $_GET_FIELD_MAPPING_["_date_from"] => $request->getParam($_GET_FIELD_MAPPING_["_date_from"])
				,$_GET_FIELD_MAPPING_["_date_to"] => $request->getParam($_GET_FIELD_MAPPING_["_date_to"])
				,$_GET_FIELD_MAPPING_["_created_from"] => $request->getParam($_GET_FIELD_MAPPING_["_created_from"])
				,$_GET_FIELD_MAPPING_["_created_to"] => $request->getParam($_GET_FIELD_MAPPING_["_created_to"])
				,$_GET_FIELD_MAPPING_["_updated_from"] => $request->getParam($_GET_FIELD_MAPPING_["_updated_from"])
				,$_GET_FIELD_MAPPING_["_updated_to"] => $request->getParam($_GET_FIELD_MAPPING_["_updated_to"])
				,$_GET_FIELD_MAPPING_["_process_source_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_source_csv"])
				,$_GET_FIELD_MAPPING_["_process_source_instance_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_source_instance_csv"])
				,$_GET_FIELD_MAPPING_["_process_type_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_type_csv"])
				,$_GET_FIELD_MAPPING_["_process_sub_type_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_sub_type_csv"])
				,$_GET_FIELD_MAPPING_["_process_id_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_id_csv"])
				,$_GET_FIELD_MAPPING_["_process_title_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_title_csv"])
				,$_GET_FIELD_MAPPING_["_process_status_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_status_csv"])
				,$_GET_FIELD_MAPPING_["_process_date_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_date_csv"])
				,$_GET_FIELD_MAPPING_["_process_location_type_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_location_type_csv"])
				,$_GET_FIELD_MAPPING_["_process_location_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_location_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_source_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_source_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_source_instance_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_source_instance_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_type_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_type_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_sub_type_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_sub_type_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_id_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_id_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_title_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_title_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_status_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_status_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_date_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_date_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_location_type_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_location_type_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_location_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_location_csv"])
				,$_GET_FIELD_MAPPING_["_in_sync"] => $request->getParam($_GET_FIELD_MAPPING_["_in_sync"])
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
			$limit = $limit <= 0 ? _DEFAULT_PAGE_LIMIT_ : ($limit > _MAX_PAGE_LIMIT_ ? _MAX_PAGE_LIMIT_ : $limit);

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

			$in_sync = $data[$_GET_FIELD_MAPPING_["_in_sync"]] === true || strcmp($data[$_GET_FIELD_MAPPING_["_in_sync"]], "true") == 0 ? true : false;
			$asc = $data[$_GET_FIELD_MAPPING_["_asc"]] === true || strcmp($data[$_GET_FIELD_MAPPING_["_asc"]], "true") == 0 ? true : false;

			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_date_from".
					",:_date_to".
					",:_created_from".
					",:_created_to".
					",:_updated_from".
					",:_updated_to".
					",:_process_source_csv".
					",:_process_source_instance_csv".
					",:_process_type_csv".
					",:_process_sub_type_csv".
					",:_process_id_csv".
					",:_process_title_csv".
					",:_process_status_csv".
					",:_process_date_csv".
					",:_process_location_type_csv".
					",:_process_location_csv".
					",:_ignore_source_csv".
					",:_ignore_source_instance_csv".
					",:_ignore_type_csv".
					",:_ignore_sub_type_csv".
					",:_ignore_id_csv".
					",:_ignore_title_csv".
					",:_ignore_status_csv".
					",:_ignore_date_csv".
					",:_ignore_location_type_csv".
					",:_ignore_location_csv".
					",:_in_sync".
					",:_asc".
					",:__limit".
					",:__page".
					",@total_records".
				");"
			);
			$stmt->bindParam(':_date_from',	 					$data[$_GET_FIELD_MAPPING_["_date_from"]]);
			$stmt->bindParam(':_date_to',	 					$data[$_GET_FIELD_MAPPING_["_date_to"]]);
			$stmt->bindParam(':_created_from',	 				$data[$_GET_FIELD_MAPPING_["_created_from"]]);
			$stmt->bindParam(':_created_to',	 				$data[$_GET_FIELD_MAPPING_["_created_to"]]);
			$stmt->bindParam(':_updated_from',	 				$data[$_GET_FIELD_MAPPING_["_updated_from"]]);
			$stmt->bindParam(':_updated_to',	 				$data[$_GET_FIELD_MAPPING_["_updated_to"]]);
			$stmt->bindParam(':_process_source_csv',	 		$data[$_GET_FIELD_MAPPING_["_process_source_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_source_instance_csv',	$data[$_GET_FIELD_MAPPING_["_process_source_instance_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_type_csv',	 			$data[$_GET_FIELD_MAPPING_["_process_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_sub_type_csv',	 		$data[$_GET_FIELD_MAPPING_["_process_sub_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_id_csv',	 			$data[$_GET_FIELD_MAPPING_["_process_id_csv"]],	PDO::PARAM_STR);
			$stmt->bindParam(':_process_title_csv',	 			$data[$_GET_FIELD_MAPPING_["_process_title_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_status_csv',	 		$data[$_GET_FIELD_MAPPING_["_process_status_csv"]],	PDO::PARAM_STR);
			$stmt->bindParam(':_process_date_csv',	 			$data[$_GET_FIELD_MAPPING_["_process_date_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_location_type_csv',	 	$data[$_GET_FIELD_MAPPING_["_process_location_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_location_csv',	 		$data[$_GET_FIELD_MAPPING_["_process_location_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_source_csv',	 			$data[$_GET_FIELD_MAPPING_["_ignore_source_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_source_instance_csv',	$data[$_GET_FIELD_MAPPING_["_ignore_source_instance_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_type_csv',	 			$data[$_GET_FIELD_MAPPING_["_ignore_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_sub_type_csv',	 		$data[$_GET_FIELD_MAPPING_["_ignore_sub_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_id_csv',	 				$data[$_GET_FIELD_MAPPING_["_ignore_id_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_title_csv',	 			$data[$_GET_FIELD_MAPPING_["_ignore_title_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_status_csv',	 			$data[$_GET_FIELD_MAPPING_["_ignore_status_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_date_csv',	 			$data[$_GET_FIELD_MAPPING_["_ignore_date_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_location_type_csv',	 	$data[$_GET_FIELD_MAPPING_["_ignore_location_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_location_csv',	 		$data[$_GET_FIELD_MAPPING_["_ignore_location_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_in_sync',	 					$in_sync, PDO::PARAM_BOOL);
			$stmt->bindParam(':_asc',	 						$asc, PDO::PARAM_BOOL);
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

			/** PAGINATION **/
        	$page = $request->getParam("page") ? $request->getParam("page") : 1;
			$page = !is_numeric($page) ? 1 : $page;
			$page = $page <= 0 ? 1 : $page;

			$limit = $request->getParam("limit") ? $request->getParam("limit") : _DEFAULT_PAGE_LIMIT_;
			$limit = !is_numeric($limit) ? _DEFAULT_PAGE_LIMIT_ : $limit;
			$limit = ( $limit <= 0 ? _DEFAULT_PAGE_LIMIT_ : $limit > _MAX_PAGE_LIMIT_ ) ? _MAX_PAGE_LIMIT_ : $limit;

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

			$in_sync = $data[$_POST_FIELD_MAPPING_["_in_sync"]] === true || strcmp($data[$_POST_FIELD_MAPPING_["_in_sync"]], "true") == 0 ? true : false;
			$asc = $data[$_POST_FIELD_MAPPING_["_asc"]] === true || strcmp($data[$_POST_FIELD_MAPPING_["_asc"]], "true") == 0 ? true : false;

			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_date_from".
					",:_date_to".
					",:_created_from".
					",:_created_to".
					",:_updated_from".
					",:_updated_to".
					",:_process_source_csv".
					",:_process_source_instance_csv".
					",:_process_type_csv".
					",:_process_sub_type_csv".
					",:_process_id_csv".
					",:_process_title_csv".
					",:_process_status_csv".
					",:_process_date_csv".
					",:_process_location_type_csv".
					",:_process_location_csv".
					",:_ignore_source_csv".
					",:_ignore_source_instance_csv".
					",:_ignore_type_csv".
					",:_ignore_sub_type_csv".
					",:_ignore_id_csv".
					",:_ignore_title_csv".
					",:_ignore_status_csv".
					",:_ignore_date_csv".
					",:_ignore_location_type_csv".
					",:_ignore_location_csv".
					",:_in_sync".
					",:_asc".
					",:__limit".
					",:__page".
					",@total_records".
				");"
			);
			$stmt->bindParam(':_date_from',	 					$data[$_POST_FIELD_MAPPING_["_date_from"]]);
			$stmt->bindParam(':_date_to',	 					$data[$_POST_FIELD_MAPPING_["_date_to"]]);
			$stmt->bindParam(':_created_from',	 				$data[$_POST_FIELD_MAPPING_["_created_from"]]);
			$stmt->bindParam(':_created_to',	 				$data[$_POST_FIELD_MAPPING_["_created_to"]]);
			$stmt->bindParam(':_updated_from',	 				$data[$_POST_FIELD_MAPPING_["_updated_from"]]);
			$stmt->bindParam(':_updated_to',	 				$data[$_POST_FIELD_MAPPING_["_updated_to"]]);
			$stmt->bindParam(':_process_source_csv',	 		$data[$_POST_FIELD_MAPPING_["_process_source_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_source_instance_csv',	$data[$_POST_FIELD_MAPPING_["_process_source_instance_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_type_csv',	 			$data[$_POST_FIELD_MAPPING_["_process_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_sub_type_csv',	 		$data[$_POST_FIELD_MAPPING_["_process_sub_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_id_csv',	 			$data[$_POST_FIELD_MAPPING_["_process_id_csv"]],	PDO::PARAM_STR);
			$stmt->bindParam(':_process_title_csv',	 			$data[$_POST_FIELD_MAPPING_["_process_title_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_status_csv',	 		$data[$_POST_FIELD_MAPPING_["_process_status_csv"]],	PDO::PARAM_STR);
			$stmt->bindParam(':_process_date_csv',	 			$data[$_POST_FIELD_MAPPING_["_process_date_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_location_type_csv',	 	$data[$_POST_FIELD_MAPPING_["_process_location_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_process_location_csv',	 		$data[$_POST_FIELD_MAPPING_["_process_location_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_source_csv',	 			$data[$_POST_FIELD_MAPPING_["_ignore_source_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_source_instance_csv',	$data[$_POST_FIELD_MAPPING_["_ignore_source_instance_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_type_csv',	 			$data[$_POST_FIELD_MAPPING_["_ignore_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_sub_type_csv',	 		$data[$_POST_FIELD_MAPPING_["_ignore_sub_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_id_csv',	 				$data[$_POST_FIELD_MAPPING_["_ignore_id_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_title_csv',	 			$data[$_POST_FIELD_MAPPING_["_ignore_title_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_status_csv',	 			$data[$_POST_FIELD_MAPPING_["_ignore_status_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_date_csv',	 			$data[$_POST_FIELD_MAPPING_["_ignore_date_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_location_type_csv',	 	$data[$_POST_FIELD_MAPPING_["_ignore_location_type_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_ignore_location_csv',	 		$data[$_POST_FIELD_MAPPING_["_ignore_location_csv"]], PDO::PARAM_STR);
			$stmt->bindParam(':_in_sync',	 					$in_sync, PDO::PARAM_BOOL);
			$stmt->bindParam(':_asc',	 						$asc, PDO::PARAM_BOOL);
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

// PUT route
$app->put(
    '/',
    function ($request, $response, $args) use ($app) {
    	// Import field mapping
    	global $_PUT_FIELD_MAPPING_;

    	try {
			// Validate input
			$errs = validate_put($request->getBody());
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
					 ":_id".
					",:_batch_hash".
					",:_updated_at_utc".
				");"
			);
			$stmt->bindParam(':_id', 				$data[$_PUT_FIELD_MAPPING_["_id"]], PDO::PARAM_STR,36);
			$stmt->bindParam(':_batch_hash', 		$data[$_PUT_FIELD_MAPPING_["_batch_hash"]], PDO::PARAM_STR,64);
			$stmt->bindParam(':_updated_at_utc', 	$data[$_PUT_FIELD_MAPPING_["_updated_at_utc"]]);
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
