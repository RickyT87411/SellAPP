<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-put.php';
require 'validate-delete.php';
require 'mapping.php';
require 'tokens.php';

define("_GET_PROC_", "dear_product_availability_query_v1");
define("_POST_PROC_", "dear_product_availability_query_v1");
define("_PUT_PROC_", "dear_product_availability_upsert_v1");
define("_DELETE_PROC_", "dear_product_availability_purge_v1");

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
    			 $_GET_FIELD_MAPPING_["_company"] => $request->getParam($_GET_FIELD_MAPPING_["_company"])
    			,$_GET_FIELD_MAPPING_["_product_id"] => $request->getParam($_GET_FIELD_MAPPING_["_product_id"])
    			,$_GET_FIELD_MAPPING_["_sku"] => $request->getParam($_GET_FIELD_MAPPING_["_sku"])
    			,$_GET_FIELD_MAPPING_["_name"] => $request->getParam($_GET_FIELD_MAPPING_["_name"])
    			,$_GET_FIELD_MAPPING_["_barcode"] => $request->getParam($_GET_FIELD_MAPPING_["_barcode"])
    			,$_GET_FIELD_MAPPING_["_location"] => $request->getParam($_GET_FIELD_MAPPING_["_location"])
    			,$_GET_FIELD_MAPPING_["_location_code"] => $request->getParam($_GET_FIELD_MAPPING_["_location_code"])
    			,$_GET_FIELD_MAPPING_["_bin"] => $request->getParam($_GET_FIELD_MAPPING_["_bin"])
    			,$_GET_FIELD_MAPPING_["_batch_serial"] => $request->getParam($_GET_FIELD_MAPPING_["_batch_serial"])
    			,$_GET_FIELD_MAPPING_["_expiry_date_from"] => $request->getParam($_GET_FIELD_MAPPING_["_expiry_date_from"])
    			,$_GET_FIELD_MAPPING_["_expiry_date_to"] => $request->getParam($_GET_FIELD_MAPPING_["_expiry_date_to"])
    			,$_GET_FIELD_MAPPING_["_on_hand_from"] => $request->getParam($_GET_FIELD_MAPPING_["_on_hand_from"])
    			,$_GET_FIELD_MAPPING_["_on_hand_to"] => $request->getParam($_GET_FIELD_MAPPING_["_on_hand_to"])
    			,$_GET_FIELD_MAPPING_["_allocated_from"] => $request->getParam($_GET_FIELD_MAPPING_["_allocated_from"])
    			,$_GET_FIELD_MAPPING_["_allocated_to"] => $request->getParam($_GET_FIELD_MAPPING_["_allocated_to"])
    			,$_GET_FIELD_MAPPING_["_available_from"] => $request->getParam($_GET_FIELD_MAPPING_["_available_from"])
    			,$_GET_FIELD_MAPPING_["_available_to"] => $request->getParam($_GET_FIELD_MAPPING_["_available_to"])
    			,$_GET_FIELD_MAPPING_["_on_order_from"] => $request->getParam($_GET_FIELD_MAPPING_["_on_order_from"])
    			,$_GET_FIELD_MAPPING_["_on_order_to"] => $request->getParam($_GET_FIELD_MAPPING_["_on_order_to"])
    			,$_GET_FIELD_MAPPING_["_value_on_hand_from"] => $request->getParam($_GET_FIELD_MAPPING_["_value_on_hand_from"])
    			,$_GET_FIELD_MAPPING_["_value_on_hand_to"] => $request->getParam($_GET_FIELD_MAPPING_["_value_on_hand_to"])
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
			$host 	=  _HOST_URI_;
			$dbname =  _HOST_DB_;
			$usr 	=  _HOST_USER_;
			$pwd 	=  _HOST_PASSWORD_;
			
			$proc 	= _GET_PROC_;
			
        	/** PAGINATION **/
        	$page = $request->getParam("page") ? $request->getParam("page") : 1;
			$page = !is_numeric($page) ? 1 : $page;
			$page = $page <= 0 ? 1 : $page;
			
			$limit = $request->getParam("limit") ? $request->getParam("limit") : _DEFAULT_PAGE_LIMIT_;
			$limit = !is_numeric($limit) ? _DEFAULT_PAGE_LIMIT_ : $limit;
			$limit = $limit <= 0 ? _DEFAULT_PAGE_LIMIT_ : $limit > _MAX_PAGE_LIMIT_ ? _MAX_PAGE_LIMIT_ : $limit;
        	/****/
        	
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
        
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_company".
					",:_product_id".
					",:_sku".
					",:_name".
					",:_barcode".
					",:_location".
					",:_location_code".
					",:_bin".
					",:_batch_serial".
					",:_expiry_date_from".
					",:_expiry_date_to".
					",:_on_hand_from".
					",:_on_hand_to".
					",:_allocated_from".
					",:_allocated_to".
					",:_available_from".
					",:_available_to".
					",:_on_order_from".
					",:_on_order_to".
					",:_value_on_hand_from".
					",:_value_on_hand_to".
					",:__limit".
					",:__page".
					",@total_records".
				");"
			);
			$stmt->bindParam(":_company",				$data[$_GET_FIELD_MAPPING_["_company"]],PDO::PARAM_STR);
			$stmt->bindParam(":_product_id",			$data[$_GET_FIELD_MAPPING_["_product_id"]],PDO::PARAM_STR);
			$stmt->bindParam(":_sku",					$data[$_GET_FIELD_MAPPING_["_sku"]],PDO::PARAM_STR);
			$stmt->bindParam(":_name",					$data[$_GET_FIELD_MAPPING_["_name"]],PDO::PARAM_STR);
			$stmt->bindParam(":_barcode",				$data[$_GET_FIELD_MAPPING_["_barcode"]],PDO::PARAM_STR);
			$stmt->bindParam(":_location",				$data[$_GET_FIELD_MAPPING_["_location"]],PDO::PARAM_STR);
			$stmt->bindParam(":_location_code",			$data[$_GET_FIELD_MAPPING_["_location_code"]],PDO::PARAM_STR);
			$stmt->bindParam(":_bin",					$data[$_GET_FIELD_MAPPING_["_bin"]],PDO::PARAM_STR);
			$stmt->bindParam(":_batch_serial",			$data[$_GET_FIELD_MAPPING_["_batch_serial"]],PDO::PARAM_STR);
			$stmt->bindParam(":_expiry_date_from",		$data[$_GET_FIELD_MAPPING_["_expiry_date_from"]]);
			$stmt->bindParam(":_expiry_date_to",		$data[$_GET_FIELD_MAPPING_["_expiry_date_to"]]);
			$stmt->bindParam(":_on_hand_from",			$data[$_GET_FIELD_MAPPING_["_on_hand_from"]]);
			$stmt->bindParam(":_on_hand_to",			$data[$_GET_FIELD_MAPPING_["_on_hand_to"]]);
			$stmt->bindParam(":_allocated_from",		$data[$_GET_FIELD_MAPPING_["_allocated_from"]]);
			$stmt->bindParam(":_allocated_to",			$data[$_GET_FIELD_MAPPING_["_allocated_to"]]);
			$stmt->bindParam(":_available_from",		$data[$_GET_FIELD_MAPPING_["_available_from"]]);
			$stmt->bindParam(":_available_to",			$data[$_GET_FIELD_MAPPING_["_available_to"]]);
			$stmt->bindParam(":_on_order_from",			$data[$_GET_FIELD_MAPPING_["_on_order_from"]]);
			$stmt->bindParam(":_on_order_to",			$data[$_GET_FIELD_MAPPING_["_on_order_to"]]);
			$stmt->bindParam(":_value_on_hand_from",	$data[$_GET_FIELD_MAPPING_["_value_on_hand_from"]]);
			$stmt->bindParam(":_value_on_hand_to",		$data[$_GET_FIELD_MAPPING_["_value_on_hand_to"]]);
			$stmt->bindParam(":__limit", $limit);
			$stmt->bindParam(":__page", $page);
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
			
			// Convert JSON data to array
			$data = json_decode($body, true);

			// DB Settings
			$host 	=  _HOST_URI_;
			$dbname =  _HOST_DB_;
			$usr 	=  _HOST_USER_;
			$pwd 	=  _HOST_PASSWORD_;
			
			$proc 	= _POST_PROC_;
			
        	/** PAGINATION **/
        	$page = $request->getParam("page") ? $request->getParam("page") : 1;
			$page = !is_numeric($page) ? 1 : $page;
			$page = $page <= 0 ? 1 : $page;
			
			$limit = $request->getParam("limit") ? $request->getParam("limit") : _DEFAULT_PAGE_LIMIT_;
			$limit = !is_numeric($limit) ? _DEFAULT_PAGE_LIMIT_ : $limit;
			$limit = $limit <= 0 ? _DEFAULT_PAGE_LIMIT_ : $limit > _MAX_PAGE_LIMIT_ ? _MAX_PAGE_LIMIT_ : $limit;
        	/****/
        	
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
        
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_company".
					",:_product_id".
					",:_sku".
					",:_name".
					",:_barcode".
					",:_location".
					",:_location_code".
					",:_bin".
					",:_batch_serial".
					",:_expiry_date_from".
					",:_expiry_date_to".
					",:_on_hand_from".
					",:_on_hand_to".
					",:_allocated_from".
					",:_allocated_to".
					",:_available_from".
					",:_available_to".
					",:_on_order_from".
					",:_on_order_to".
					",:_value_on_hand_from".
					",:_value_on_hand_to".
					",:__limit".
					",:__page".
					",@total_records".
				");"
			);
			$stmt->bindParam(":_company",				$data[$_POST_FIELD_MAPPING_["_company"]],PDO::PARAM_STR);
			$stmt->bindParam(":_product_id",			$data[$_POST_FIELD_MAPPING_["_product_id"]],PDO::PARAM_STR);
			$stmt->bindParam(":_sku",					$data[$_POST_FIELD_MAPPING_["_sku"]],PDO::PARAM_STR);
			$stmt->bindParam(":_name",					$data[$_POST_FIELD_MAPPING_["_name"]],PDO::PARAM_STR);
			$stmt->bindParam(":_barcode",				$data[$_POST_FIELD_MAPPING_["_barcode"]],PDO::PARAM_STR);
			$stmt->bindParam(":_location",				$data[$_POST_FIELD_MAPPING_["_location"]],PDO::PARAM_STR);
			$stmt->bindParam(":_location_code",			$data[$_POST_FIELD_MAPPING_["_location_code"]],PDO::PARAM_STR);
			$stmt->bindParam(":_bin",					$data[$_POST_FIELD_MAPPING_["_bin"]],PDO::PARAM_STR);
			$stmt->bindParam(":_batch_serial",			$data[$_POST_FIELD_MAPPING_["_batch_serial"]],PDO::PARAM_STR);
			$stmt->bindParam(":_expiry_date_from",		$data[$_POST_FIELD_MAPPING_["_expiry_date_from"]]);
			$stmt->bindParam(":_expiry_date_to",		$data[$_POST_FIELD_MAPPING_["_expiry_date_to"]]);
			$stmt->bindParam(":_on_hand_from",			$data[$_POST_FIELD_MAPPING_["_on_hand_from"]]);
			$stmt->bindParam(":_on_hand_to",			$data[$_POST_FIELD_MAPPING_["_on_hand_to"]]);
			$stmt->bindParam(":_allocated_from",		$data[$_POST_FIELD_MAPPING_["_allocated_from"]]);
			$stmt->bindParam(":_allocated_to",			$data[$_POST_FIELD_MAPPING_["_allocated_to"]]);
			$stmt->bindParam(":_available_from",		$data[$_POST_FIELD_MAPPING_["_available_from"]]);
			$stmt->bindParam(":_available_to",			$data[$_POST_FIELD_MAPPING_["_available_to"]]);
			$stmt->bindParam(":_on_order_from",			$data[$_POST_FIELD_MAPPING_["_on_order_from"]]);
			$stmt->bindParam(":_on_order_to",			$data[$_POST_FIELD_MAPPING_["_on_order_to"]]);
			$stmt->bindParam(":_value_on_hand_from",	$data[$_POST_FIELD_MAPPING_["_value_on_hand_from"]]);
			$stmt->bindParam(":_value_on_hand_to",		$data[$_POST_FIELD_MAPPING_["_value_on_hand_to"]]);
			$stmt->bindParam(":__limit", $limit);
			$stmt->bindParam(":__page", $page);
			$stmt->execute();
			
			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				// Append record
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
			$array = json_decode($request->getBody(), true);
			
			// DB Settings
			$host 	=  _HOST_URI_;
			$dbname =  _HOST_DB_;
			$usr 	=  _HOST_USER_;
			$pwd 	=  _HOST_PASSWORD_;
			
			$proc 	= _PUT_PROC_;
			
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$results = array();
			
			$verbose = true;
			
			foreach($array as $data) {			
				// Execute the stored procedure
				$stmt = $conn->prepare(
					"CALL $proc(".
						 ":_company".
						",:_product_id".
						",:_sku".
						",:_name".
						",:_barcode".
						",:_location".
						",:_bin".
						",:_batch_serial".
						",:_expiry_date".
						",:_on_hand".
						",:_allocated".
						",:_available".
						",:_on_order".
						",:_value_on_hand".
						",:__verbose".
					");"
				);
				$stmt->bindParam(':_company', 				$data[$_PUT_FIELD_MAPPING_["_company"]], PDO::PARAM_STR,256);
				$stmt->bindParam(':_product_id', 			$data[$_PUT_FIELD_MAPPING_["_product_id"]], PDO::PARAM_STR,36);
				$stmt->bindParam(':_sku', 					$data[$_PUT_FIELD_MAPPING_["_sku"]], PDO::PARAM_STR,50);
				$stmt->bindParam(':_name', 					$data[$_PUT_FIELD_MAPPING_["_name"]], PDO::PARAM_STR,256);
				$stmt->bindParam(':_barcode', 				$data[$_PUT_FIELD_MAPPING_["_barcode"]], PDO::PARAM_STR,256);
				$stmt->bindParam(':_location', 				$data[$_PUT_FIELD_MAPPING_["_location"]], PDO::PARAM_STR,256);
				$stmt->bindParam(':_bin', 					$data[$_PUT_FIELD_MAPPING_["_bin"]], PDO::PARAM_STR,256);
				$stmt->bindParam(':_batch_serial', 			$data[$_PUT_FIELD_MAPPING_["_batch_serial"]], PDO::PARAM_STR,50);
				$stmt->bindParam(':_expiry_date', 			$data[$_PUT_FIELD_MAPPING_["_expiry_date"]]);
				$stmt->bindParam(':_on_hand', 				$data[$_PUT_FIELD_MAPPING_["_on_hand"]]);
				$stmt->bindParam(':_allocated', 			$data[$_PUT_FIELD_MAPPING_["_allocated"]]);
				$stmt->bindParam(':_available', 			$data[$_PUT_FIELD_MAPPING_["_available"]]);
				$stmt->bindParam(':_on_order', 				$data[$_PUT_FIELD_MAPPING_["_on_order"]]);
				$stmt->bindParam(':_value_on_hand', 		$data[$_PUT_FIELD_MAPPING_["_value_on_hand"]]);
				$stmt->bindParam(':__verbose', 				$verbose, PDO::PARAM_BOOL);
				$stmt->execute();
				
				// Parse record set
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					array_push($results, $row);
				}
				
				$stmt->closeCursor();
			}
			
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
    	
    	try {
    		$body = array(
    			 $_DELETE_FIELD_MAPPING_["_company"] => $request->getParam($_DELETE_FIELD_MAPPING_["_company"])
    			,$_DELETE_FIELD_MAPPING_["_retention_in_secs"] => $request->getParam($_DELETE_FIELD_MAPPING_["_retention_in_secs"])
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
			$host 	=  _HOST_URI_;
			$dbname =  _HOST_DB_;
			$usr 	=  _HOST_USER_;
			$pwd 	=  _HOST_PASSWORD_;
			
			$proc 	= _DELETE_PROC_;
			
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$verbose = $request->getParam("verbose") === true || strcmp($request->getParam("verbose"), "true") == 0 ? true : false;
			
			$results = array();
						
			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_company".
					",:_retention_in_secs".
					",:__verbose".
				");"
			);

			$stmt->bindParam(':_company', 			$data[$_DELETE_FIELD_MAPPING_["_company"]], PDO::PARAM_STR);
			$stmt->bindParam(':_retention_in_secs', $data[$_DELETE_FIELD_MAPPING_["_retention_in_secs"]]);
			$stmt->bindParam(':__verbose', 			$verbose, PDO::PARAM_BOOL);
			$stmt->execute();
			
			// Parse record set
			if($verbose) {
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					array_push($results, $row);
				}
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
