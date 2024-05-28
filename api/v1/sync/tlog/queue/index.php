<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-delete.php';
require 'validate-put.php';
require 'mapping.php';
require 'tokens.php';

define("_GET_PROC_", "sync_tlog_transaction_queue_v3");
define("_POST_PROC_", "sync_tlog_transaction_queue_v3");

define("_DEFAULT_PAGE_LIMIT_", 100);
define("_MAX_PAGE_LIMIT_", 1000);

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


class JsonEncodedException extends \Exception
{
    /**
     * Json encodes the message and calls the parent constructor.
     *
     * @param null           $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct(json_encode($message), $code, $previous);
    }

    /**
     * Returns the json decoded message.
     *
     * @return mixed
     */
    public function getDecodedMessage()
    {
        return json_decode($this->getMessage());
    }
}

if(!function_exists("to_boolean")) {
	function to_boolean($input) {
		// Return null if not of any valid boolean value
		if( !(
				$input === true ||
				$input === false ||
				strcmp(strtolower($input),"true") == 0 ||
				strcmp(strtolower($input),"false") == 0 ||
				(is_numeric($input) && $input == -1 || $input == 1) ||
				strcmp($input,"1") == 0 ||
				strcmp($input,"-1") == 0
			)
		) return null;

		$bool = (
			$input === true ||
			strcmp(strtolower($input),"true") == 0 ||
			(is_numeric($input) && $input == 1) ||
			strcmp($input,"1") == 0
		);

		return $bool;
	}
}

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
    		$contentType = $request->getContentType();
    		$body = array(
    			 $_GET_FIELD_MAPPING_["_tlog_header_type"] => $request->getParam($_GET_FIELD_MAPPING_["_tlog_header_type"])
				,$_GET_FIELD_MAPPING_["_transaction_date_from"] => $request->getParam($_GET_FIELD_MAPPING_["_transaction_date_from"])
				,$_GET_FIELD_MAPPING_["_transaction_date_to"] => $request->getParam($_GET_FIELD_MAPPING_["_transaction_date_to"])
				,$_GET_FIELD_MAPPING_["_transaction_updated_from"] => $request->getParam($_GET_FIELD_MAPPING_["_transaction_updated_from"])
				,$_GET_FIELD_MAPPING_["_transaction_updated_to"] => $request->getParam($_GET_FIELD_MAPPING_["_transaction_updated_to"])
				,$_GET_FIELD_MAPPING_["_process_transaction_date_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_transaction_date_csv"])
				,$_GET_FIELD_MAPPING_["_process_transaction_channel_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_transaction_channel_csv"])
				,$_GET_FIELD_MAPPING_["_process_transaction_source_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_transaction_source_csv"])
				,$_GET_FIELD_MAPPING_["_process_transaction_source_instance_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_transaction_source_instance_csv"])
				,$_GET_FIELD_MAPPING_["_process_transaction_sub_type_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_transaction_sub_type_csv"])
				,$_GET_FIELD_MAPPING_["_process_transaction_parent_id_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_transaction_parent_id_csv"])
				,$_GET_FIELD_MAPPING_["_process_transaction_id_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_transaction_id_csv"])
				,$_GET_FIELD_MAPPING_["_process_transaction_title_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_transaction_title_csv"])
				,$_GET_FIELD_MAPPING_["_process_transaction_status_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_transaction_status_csv"])
				,$_GET_FIELD_MAPPING_["_process_location_code_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_location_code_csv"])
				,$_GET_FIELD_MAPPING_["_process_outlet_code_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_process_outlet_code_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_transaction_date_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_transaction_date_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_transaction_channel_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_transaction_channel_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_transaction_source_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_transaction_source_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_transaction_source_instance_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_transaction_source_instance_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_transaction_sub_type_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_transaction_sub_type_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_transaction_parent_id_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_transaction_parent_id_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_transaction_id_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_transaction_id_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_transaction_title_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_transaction_title_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_transaction_status_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_transaction_status_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_location_code_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_location_code_csv"])
				,$_GET_FIELD_MAPPING_["_ignore_outlet_code_csv"] => $request->getParam($_GET_FIELD_MAPPING_["_ignore_outlet_code_csv"])
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

			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd, array(PDO::ATTR_PERSISTENT => true));
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$page = $request->getParam("page") ? $request->getParam("page") : 1;
			$page = !is_numeric($page) ? 1 : $page;
			$page = $page <= 0 ? 1 : $page;

			$limit = $request->getParam("limit") ? $request->getParam("limit") : _DEFAULT_PAGE_LIMIT_;
			$limit = !is_numeric($limit) ? _DEFAULT_PAGE_LIMIT_ : $limit;
			$limit = $limit <= 0 ? _DEFAULT_PAGE_LIMIT_ : $limit > _MAX_PAGE_LIMIT_ ? _MAX_PAGE_LIMIT_ : $limit;

			$in_sync = $data[$_GET_FIELD_MAPPING_["_in_sync"]] === true || strcmp($data[$_GET_FIELD_MAPPING_["_in_sync"]], "true") == 0 ? true : false;
			$asc = $data[$_GET_FIELD_MAPPING_["_asc"]] === true || strcmp($data[$_GET_FIELD_MAPPING_["_asc"]], "true") == 0 ? true : false;

			$errs = array();
			$results = array(
				"pagination" => array(
					 "total_records" => 0
					,"total_pages" => 0
					,"page_records" => 0
					,"page" => $page
					,"limit" => $limit
				),
				"records" => array()
			);

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_tlog_header_type".
					",:_transaction_date_from".
					",:_transaction_date_to".
					",:_transaction_updated_from".
					",:_transaction_updated_to".
					",:_process_transaction_date_csv".
					",:_process_transaction_channel_csv".
					",:_process_transaction_source_csv".
					",:_process_transaction_source_instance_csv".
					",:_process_transaction_sub_type_csv".
					",:_process_transaction_parent_id_csv".
					",:_process_transaction_id_csv".
					",:_process_transaction_title_csv".
					",:_process_transaction_status_csv".
					",:_process_location_code_csv".
					",:_process_outlet_code_csv".
					",:_ignore_transaction_date_csv".
					",:_ignore_transaction_channel_csv".
					",:_ignore_transaction_source_csv".
					",:_ignore_transaction_source_instance_csv".
					",:_ignore_transaction_sub_type_csv".
					",:_ignore_transaction_parent_id_csv".
					",:_ignore_transaction_id_csv".
					",:_ignore_transaction_title_csv".
					",:_ignore_transaction_status_csv".
					",:_ignore_location_code_csv".
					",:_ignore_outlet_code_csv".
					",:_in_sync".
					",:_asc".
					",:__limit".
					",:__page".
					",@total_records".
				");"
			);
			$stmt->bindParam(":_tlog_header_type", $data[$_GET_FIELD_MAPPING_["_tlog_header_type"]]);
			$stmt->bindParam(":_transaction_date_from", $data[$_GET_FIELD_MAPPING_["_transaction_date_from"]]);
			$stmt->bindParam(":_transaction_date_to", $data[$_GET_FIELD_MAPPING_["_transaction_date_to"]]);
			$stmt->bindParam(":_transaction_updated_from", $data[$_GET_FIELD_MAPPING_["_transaction_updated_from"]]);
			$stmt->bindParam(":_transaction_updated_to", $data[$_GET_FIELD_MAPPING_["_transaction_updated_to"]]);
			$stmt->bindParam(":_process_transaction_date_csv", $data[$_GET_FIELD_MAPPING_["_process_transaction_date_csv"]]);
			$stmt->bindParam(":_process_transaction_channel_csv", $data[$_GET_FIELD_MAPPING_["_process_transaction_channel_csv"]]);
			$stmt->bindParam(":_process_transaction_source_csv", $data[$_GET_FIELD_MAPPING_["_process_transaction_source_csv"]]);
			$stmt->bindParam(":_process_transaction_source_instance_csv", $data[$_GET_FIELD_MAPPING_["_process_transaction_source_instance_csv"]]);
			$stmt->bindParam(":_process_transaction_sub_type_csv", $data[$_GET_FIELD_MAPPING_["_process_transaction_sub_type_csv"]]);
			$stmt->bindParam(":_process_transaction_parent_id_csv", $data[$_GET_FIELD_MAPPING_["_process_transaction_parent_id_csv"]]);
			$stmt->bindParam(":_process_transaction_id_csv", $data[$_GET_FIELD_MAPPING_["_process_transaction_id_csv"]]);
			$stmt->bindParam(":_process_transaction_title_csv", $data[$_GET_FIELD_MAPPING_["_process_transaction_title_csv"]]);
			$stmt->bindParam(":_process_transaction_status_csv", $data[$_GET_FIELD_MAPPING_["_process_transaction_status_csv"]]);
			$stmt->bindParam(":_process_location_code_csv", $data[$_GET_FIELD_MAPPING_["_process_location_code_csv"]]);
			$stmt->bindParam(":_process_outlet_code_csv", $data[$_GET_FIELD_MAPPING_["_process_outlet_code_csv"]]);
			$stmt->bindParam(":_ignore_transaction_date_csv", $data[$_GET_FIELD_MAPPING_["_ignore_transaction_date_csv"]]);
			$stmt->bindParam(":_ignore_transaction_channel_csv", $data[$_GET_FIELD_MAPPING_["_ignore_transaction_channel_csv"]]);
			$stmt->bindParam(":_ignore_transaction_source_csv", $data[$_GET_FIELD_MAPPING_["_ignore_transaction_source_csv"]]);
			$stmt->bindParam(":_ignore_transaction_source_instance_csv", $data[$_GET_FIELD_MAPPING_["_ignore_transaction_source_instance_csv"]]);
			$stmt->bindParam(":_ignore_transaction_sub_type_csv", $data[$_GET_FIELD_MAPPING_["_ignore_transaction_sub_type_csv"]]);
			$stmt->bindParam(":_ignore_transaction_parent_id_csv", $data[$_GET_FIELD_MAPPING_["_ignore_transaction_parent_id_csv"]]);
			$stmt->bindParam(":_ignore_transaction_id_csv", $data[$_GET_FIELD_MAPPING_["_ignore_transaction_id_csv"]]);
			$stmt->bindParam(":_ignore_transaction_title_csv", $data[$_GET_FIELD_MAPPING_["_ignore_transaction_title_csv"]]);
			$stmt->bindParam(":_ignore_transaction_status_csv", $data[$_GET_FIELD_MAPPING_["_ignore_transaction_status_csv"]]);
			$stmt->bindParam(":_ignore_location_code_csv", $data[$_GET_FIELD_MAPPING_["_ignore_location_code_csv"]]);
			$stmt->bindParam(":_ignore_outlet_code_csv", $data[$_GET_FIELD_MAPPING_["_ignore_outlet_code_csv"]]);
			$stmt->bindParam(":_in_sync", $in_sync, PDO::PARAM_BOOL);
			$stmt->bindParam(":_asc", $asc, PDO::PARAM_BOOL);
			$stmt->bindParam(":__limit", $limit);
			$stmt->bindParam(":__page", $page);
			$stmt->execute();

			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$results["records"][] = $row;
			}

			$stmt->closeCursor();

			/** PAGINATION UPDATE **/
			// Capture output parameter
			$q = $conn->query("select @total_records as total_records;")->fetch(PDO::FETCH_ASSOC);
			$total_records = $q && array_key_exists("total_records", $q) ? $q["total_records"] : 1;

			$results["pagination"]["page_records"] = count($results["records"]);
			$results["pagination"]["total_records"] = $total_records * 1;
			$results["pagination"]["total_pages"] = ceil($total_records / $limit);
			/**********************/

			// Close connection
			$conn = null;

			// Return input body as results to confirm success
			return 	$response->withStatus(200)
						->withHeader("Content-Type","application/json")
						->write(json_encode($results));

		} catch (JsonEncodedException $e) {
						return $response->withStatus(400)
								->withHeader("Content-Type","application/json")
								->write($e->getMessage());
		} catch (PDOException $e) {
			return 	$response->withStatus(500)
							->withHeader("Content-Type","application/json")
							->write(json_encode(array(
						"ErrorCode"	=>	500,
						"Exception"	=>	$e->getMessage()
					), JSON_NUMERIC_CHECK));
		} catch (Exception $e) {
			return 	$response->withStatus(500)
							->withHeader("Content-Type","application/json")
							->write(json_encode(array(
						"ErrorCode"	=>	500,
						"Exception"	=>	$e->getMessage()
					), JSON_NUMERIC_CHECK));
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
    		$contentType = $request->getContentType();
    		$body = $request->getBody();

			// Validate input
			$errs = validate_post($body);
			$valid = empty($errs);

			// Return an array list of errors if invalid
			if(!$valid) {
				return 	$response->withStatus(400)
							->withHeader("Content-Type","application/json")
							->write(json_encode($errs));
			}
			//return $response->withStatus(400)->withHeader("Content-Type","application/json")->write(json_encode($errs));
			// Convert JSON data to array
			$data = json_decode($body, true);

			// DB Settings
			$host 	= _HOST_URI_;
			$dbname = _HOST_DB_;
			$usr 	= _HOST_USER_;
			$pwd 	= _HOST_PASSWORD_;

			$proc 	= _POST_PROC_;

			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd, array(PDO::ATTR_PERSISTENT => true));
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$page = $request->getParam("page") ? $request->getParam("page") : 1;
			$page = !is_numeric($page) ? 1 : $page;
			$page = $page <= 0 ? 1 : $page;

			$limit = $request->getParam("limit") ? $request->getParam("limit") : _DEFAULT_PAGE_LIMIT_;
			$limit = !is_numeric($limit) ? _DEFAULT_PAGE_LIMIT_ : $limit;
			$limit = $limit <= 0 ? _DEFAULT_PAGE_LIMIT_ : $limit > _MAX_PAGE_LIMIT_ ? _MAX_PAGE_LIMIT_ : $limit;

			$in_sync = $data[$_POST_FIELD_MAPPING_["_in_sync"]] === true || strcmp($data[$_POST_FIELD_MAPPING_["_in_sync"]], "true") == 0 ? true : false;
			$asc = $data[$_POST_FIELD_MAPPING_["_asc"]] === true || strcmp($data[$_POST_FIELD_MAPPING_["_asc"]], "true") == 0 ? true : false;

			$errs = array();
			$results = array(
				"pagination" => array(
					 "total_records" => 0
					,"total_pages" => 0
					,"page_records" => 0
					,"page" => $page
					,"limit" => $limit
				),
				"records" => array()
			);

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_tlog_header_type".
					",:_transaction_date_from".
					",:_transaction_date_to".
					",:_transaction_updated_from".
					",:_transaction_updated_to".
					",:_process_transaction_date_csv".
					",:_process_transaction_channel_csv".
					",:_process_transaction_source_csv".
					",:_process_transaction_source_instance_csv".
					",:_process_transaction_sub_type_csv".
					",:_process_transaction_parent_id_csv".
					",:_process_transaction_id_csv".
					",:_process_transaction_title_csv".
					",:_process_transaction_status_csv".
					",:_process_location_code_csv".
					",:_process_outlet_code_csv".
					",:_ignore_transaction_date_csv".
					",:_ignore_transaction_channel_csv".
					",:_ignore_transaction_source_csv".
					",:_ignore_transaction_source_instance_csv".
					",:_ignore_transaction_sub_type_csv".
					",:_ignore_transaction_parent_id_csv".
					",:_ignore_transaction_id_csv".
					",:_ignore_transaction_title_csv".
					",:_ignore_transaction_status_csv".
					",:_ignore_location_code_csv".
					",:_ignore_outlet_code_csv".
					",:_in_sync".
					",:_asc".
					",:__limit".
					",:__page".
					",@total_records".
				");"
			);
			$stmt->bindParam(":_tlog_header_type", $data[$_POST_FIELD_MAPPING_["_tlog_header_type"]]);
			$stmt->bindParam(":_transaction_date_from", $data[$_POST_FIELD_MAPPING_["_transaction_date_from"]]);
			$stmt->bindParam(":_transaction_date_to", $data[$_POST_FIELD_MAPPING_["_transaction_date_to"]]);
			$stmt->bindParam(":_transaction_updated_from", $data[$_POST_FIELD_MAPPING_["_transaction_updated_from"]]);
			$stmt->bindParam(":_transaction_updated_to", $data[$_POST_FIELD_MAPPING_["_transaction_updated_to"]]);
			$stmt->bindParam(":_process_transaction_date_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_date_csv"]]);
			$stmt->bindParam(":_process_transaction_channel_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_channel_csv"]]);
			$stmt->bindParam(":_process_transaction_source_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_source_csv"]]);
			$stmt->bindParam(":_process_transaction_source_instance_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_source_instance_csv"]]);
			$stmt->bindParam(":_process_transaction_sub_type_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_sub_type_csv"]]);
			$stmt->bindParam(":_process_transaction_parent_id_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_parent_id_csv"]]);
			$stmt->bindParam(":_process_transaction_id_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_id_csv"]]);
			$stmt->bindParam(":_process_transaction_title_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_title_csv"]]);
			$stmt->bindParam(":_process_transaction_status_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_status_csv"]]);
			$stmt->bindParam(":_process_location_code_csv", $data[$_POST_FIELD_MAPPING_["_process_location_code_csv"]]);
			$stmt->bindParam(":_process_outlet_code_csv", $data[$_POST_FIELD_MAPPING_["_process_outlet_code_csv"]]);
			$stmt->bindParam(":_ignore_transaction_date_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_date_csv"]]);
			$stmt->bindParam(":_ignore_transaction_channel_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_channel_csv"]]);
			$stmt->bindParam(":_ignore_transaction_source_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_source_csv"]]);
			$stmt->bindParam(":_ignore_transaction_source_instance_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_source_instance_csv"]]);
			$stmt->bindParam(":_ignore_transaction_sub_type_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_sub_type_csv"]]);
			$stmt->bindParam(":_ignore_transaction_parent_id_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_parent_id_csv"]]);
			$stmt->bindParam(":_ignore_transaction_id_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_id_csv"]]);
			$stmt->bindParam(":_ignore_transaction_title_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_title_csv"]]);
			$stmt->bindParam(":_ignore_transaction_status_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_status_csv"]]);
			$stmt->bindParam(":_ignore_location_code_csv", $data[$_POST_FIELD_MAPPING_["_ignore_location_code_csv"]]);
			$stmt->bindParam(":_ignore_outlet_code_csv", $data[$_POST_FIELD_MAPPING_["_ignore_outlet_code_csv"]]);
			$stmt->bindParam(":_in_sync", $in_sync, PDO::PARAM_BOOL);
			$stmt->bindParam(":_asc", $asc, PDO::PARAM_BOOL);
			$stmt->bindParam(":__limit", $limit);
			$stmt->bindParam(":__page", $page);
			$stmt->execute();

			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$results["records"][] = $row;
			}

			$stmt->closeCursor();

			/** PAGINATION UPDATE **/
			// Capture output parameter
			$q = $conn->query("select @total_records as total_records;")->fetch(PDO::FETCH_ASSOC);
			$total_records = $q && array_key_exists("total_records", $q) ? $q["total_records"] : 1;

			$results["pagination"]["page_records"] = count($results["records"]);
			$results["pagination"]["total_records"] = $total_records * 1;
			$results["pagination"]["total_pages"] = ceil($total_records / $limit);
			/**********************/

			// Close connection
			$conn = null;

			// Return input body as results to confirm success
			return 	$response->withStatus(200)
						->withHeader("Content-Type","application/json")
						->write(json_encode($results, JSON_NUMERIC_CHECK));

		} catch (JsonEncodedException $e) {
						return $response->withStatus(400)
								->withHeader("Content-Type","application/json")
								->write($e->getMessage());
		} catch (PDOException $e) {
			return 	$response->withStatus(500)
							->withHeader("Content-Type","application/json")
							->write(json_encode(array(
						"ErrorCode"	=>	500,
						"Exception"	=>	$e->getMessage()
					), JSON_NUMERIC_CHECK));
		} catch (Exception $e) {
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
    	try {
   			return 	__method_not_allowed("PUT", $response);
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