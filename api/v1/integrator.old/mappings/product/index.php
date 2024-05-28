<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-delete.php';
require 'mapping.php';

define("_GET_MAPPING_PROC_PROD_", "product_mapping_query");
define("_GET_MAPPING_PROC_DEV_", "product_mapping_query");

define("_POST_MAPPING_PROC_PROD_", "product_mapping_map");
define("_POST_MAPPING_PROC_DEV_", "product_mapping_map");

define("_DELETE_MAPPING_PROC_PROD_", "product_mapping_unmap");
define("_DELETE_MAPPING_PROC_DEV_", "product_mapping_unmap");

/** PAGINATION **/
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
    			 $_GET_FIELD_MAPPING_["_product_id"]		=> $request->getParam($_GET_FIELD_MAPPING_["_product_id"])
    			,$_GET_FIELD_MAPPING_["_sku"] 				=> $request->getParam($_GET_FIELD_MAPPING_["_sku"])
    			,$_GET_FIELD_MAPPING_["_barcode"] 			=> $request->getParam($_GET_FIELD_MAPPING_["_barcode"])
    			,$_GET_FIELD_MAPPING_["_company"] 			=> $request->getParam($_GET_FIELD_MAPPING_["_company"])
    			,$_GET_FIELD_MAPPING_["_country_code"]		=> $request->getParam($_GET_FIELD_MAPPING_["_country_code"])
    			,$_GET_FIELD_MAPPING_["_dear_instance"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_dear_instance"])
    			,$_GET_FIELD_MAPPING_["_dear_product_id"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_dear_product_id"])
    			,$_GET_FIELD_MAPPING_["_dear_sku"] 			=> $request->getParam($_GET_FIELD_MAPPING_["_dear_sku"])
    			,$_GET_FIELD_MAPPING_["_vend_instance"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_vend_instance"])
    			,$_GET_FIELD_MAPPING_["_vend_product_id"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_vend_product_id"])
    			,$_GET_FIELD_MAPPING_["_vend_sku"] 			=> $request->getParam($_GET_FIELD_MAPPING_["_vend_sku"])
    			,$_GET_FIELD_MAPPING_["_created_from"] 		=> $request->getParam($_GET_FIELD_MAPPING_["_created_from"])
    			,$_GET_FIELD_MAPPING_["_created_to"] 		=> $request->getParam($_GET_FIELD_MAPPING_["_created_to"])
    			,$_GET_FIELD_MAPPING_["_updated_from"] 		=> $request->getParam($_GET_FIELD_MAPPING_["_updated_from"])
    			,$_GET_FIELD_MAPPING_["_updated_to"] 		=> $request->getParam($_GET_FIELD_MAPPING_["_updated_to"])
    			,$_GET_FIELD_MAPPING_["_fuzzy_match"] 		=> $request->getParam($_GET_FIELD_MAPPING_["_fuzzy_match"])
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
			$s_fuzzymatch = $data[$_GET_FIELD_MAPPING_["_fuzzy_match"]] || false;
			$fuzzymatch = 	$s_fuzzymatch === true ||
							$s_fuzzymatch === false ||
							strcmp(strtolower($s_fuzzymatch),"true") == 0 || 
							strcmp(strtolower($s_zfuzzymatch),"false") == 0 ||
							(is_numeric($s_fuzzymatch) && $s_fuzzymatch == -1 || $s_fuzzymatch == 1) ||
							strcmp($s_fuzzymatch,"1") == 0 ||
							strcmp($s_fuzzymatch,"-1") == 0
			;

			// DB Settings
			$host 	= !$devmode ? _HOST_URI_PROD_ 		: _HOST_URI_DEV_;
			$dbname = !$devmode ? _HOST_DB_PROD_ 		: _HOST_DB_DEV_;
			$usr 	= !$devmode ? _HOST_USER_PROD_ 		: _HOST_USER_DEV_;
			$pwd 	= !$devmode ? _HOST_PASSWORD_PROD_ 	: _HOST_PASSWORD_DEV_;
	
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
			
			$proc 	= !$devmode ? _GET_MAPPING_PROC_PROD_ : _GET_MAPPING_PROC_DEV_;
			
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_product_id".
					",:_sku".
					",:_barcode".
					",:_company".
					",:_country_code".
					",:_dear_instance".
					",:_dear_product_id".
					",:_dear_sku".
					",:_vend_instance".
					",:_vend_product_id".
					",:_vend_sku".
					",:_created_from".
					",:_created_to".
					",:_updated_from".
					",:_updated_to".
					",:_fuzzy_match".
					",:__limit".
					",:__page".
					",@total_records".
				");"
			);
			$stmt->bindParam(':_product_id',		$data[$_GET_FIELD_MAPPING_["_product_id"]], PDO::PARAM_STR,36); 
			$stmt->bindParam(':_sku', 				$data[$_GET_FIELD_MAPPING_["_sku"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_barcode', 			$data[$_GET_FIELD_MAPPING_["_barcode"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_company', 			$data[$_GET_FIELD_MAPPING_["_company"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_country_code', 		$data[$_GET_FIELD_MAPPING_["_country_code"]], PDO::PARAM_STR,2);  
			$stmt->bindParam(':_dear_instance', 	$data[$_GET_FIELD_MAPPING_["_dear_instance"]], PDO::PARAM_STR,100); 
			$stmt->bindParam(':_dear_product_id', 	$data[$_GET_FIELD_MAPPING_["_dear_product_id"]], PDO::PARAM_STR,36);
			$stmt->bindParam(':_dear_sku', 			$data[$_GET_FIELD_MAPPING_["_dear_sku"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_vend_instance', 	$data[$_GET_FIELD_MAPPING_["_vend_instance"]], PDO::PARAM_STR,100); 
			$stmt->bindParam(':_vend_product_id', 	$data[$_GET_FIELD_MAPPING_["_vend_product_id"]], PDO::PARAM_STR,36);
			$stmt->bindParam(':_vend_sku', 			$data[$_GET_FIELD_MAPPING_["_vend_sku"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_created_from', 		$data[$_GET_FIELD_MAPPING_["_created_from"]]); 
			$stmt->bindParam(':_created_to', 		$data[$_GET_FIELD_MAPPING_["_created_to"]]); 
			$stmt->bindParam(':_updated_from', 		$data[$_GET_FIELD_MAPPING_["_updated_from"]]); 
			$stmt->bindParam(':_updated_to',		$data[$_GET_FIELD_MAPPING_["_updated_to"]]);
			$stmt->bindParam(':_fuzzy_match',		$fuzzymatch, PDO::PARAM_BOOL);
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
			$errs = validate_post($request->getBody());
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
        	
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			
			$results = array();

			foreach($array as $data) {		
				// Check @FUZZY_MATCH flag to just compare input @SKU to a close matching @SKU
				$s_fuzzymatch = $data[$_POST_FIELD_MAPPING_["_fuzzy_match"]] || false;
				$fuzzymatch = 	$s_fuzzymatch === true ||
								$s_fuzzymatch === false ||
								strcmp(strtolower($s_fuzzymatch),"true") == 0 || 
								strcmp(strtolower($s_fuzzymatch),"false") == 0 ||
								(is_numeric($s_fuzzymatch) && $s_fuzzymatch == -1 || $s_fuzzymatch == 1) ||
								strcmp($s_fuzzymatch,"1") == 0 ||
								strcmp($s_fuzzymatch,"-1") == 0
				;
				
				$proc 	= !$devmode ? _POST_MAPPING_PROC_PROD_ : _POST_MAPPING_PROC_DEV_;

				// Execute the stored procedure
				$stmt = $conn->prepare(
					"CALL $proc(".
						 ":_product_id".
						",:_sku".
						",:_barcode".
						",:_company".
						",:_country_code".
						",:_dear_instance".
						",:_dear_product_id".
						",:_dear_sku".
						",:_dear_created_at".
						",:_dear_updated_at".
						",:_vend_instance".
						",:_vend_product_id".
						",:_vend_sku".
						",:_vend_created_at".
						",:_vend_updated_at".
						",:_fuzzy_match".
					");"
				);
				$stmt->bindParam(':_product_id',		$data[$_POST_FIELD_MAPPING_["_product_id"]], 		PDO::PARAM_STR,36); 
				$stmt->bindParam(':_sku', 				$data[$_POST_FIELD_MAPPING_["_sku"]], 				PDO::PARAM_STR,256);
				$stmt->bindParam(':_barcode', 			$data[$_POST_FIELD_MAPPING_["_barcode"]], 			PDO::PARAM_STR,256);
				$stmt->bindParam(':_company', 			$data[$_POST_FIELD_MAPPING_["_company"]], 			PDO::PARAM_STR,256); 
				$stmt->bindParam(':_country_code', 		$data[$_POST_FIELD_MAPPING_["_country_code"]], 		PDO::PARAM_STR,2); 
				$stmt->bindParam(':_dear_instance', 	$data[$_POST_FIELD_MAPPING_["_dear_instance"]], 	PDO::PARAM_STR,100); 
				$stmt->bindParam(':_dear_product_id', 	$data[$_POST_FIELD_MAPPING_["_dear_product_id"]], 	PDO::PARAM_STR,36);
				$stmt->bindParam(':_dear_sku', 			$data[$_POST_FIELD_MAPPING_["_dear_sku"]], 			PDO::PARAM_STR,256);
				$stmt->bindParam(':_dear_created_at', 	$data[$_POST_FIELD_MAPPING_["_dear_created_at"]]); 
				$stmt->bindParam(':_dear_updated_at', 	$data[$_POST_FIELD_MAPPING_["_dear_updated_at"]]); 
				$stmt->bindParam(':_vend_instance', 	$data[$_POST_FIELD_MAPPING_["_vend_instance"]], 	PDO::PARAM_STR,100); 
				$stmt->bindParam(':_vend_product_id', 	$data[$_POST_FIELD_MAPPING_["_vend_product_id"]], 	PDO::PARAM_STR,36); 
				$stmt->bindParam(':_vend_sku', 			$data[$_POST_FIELD_MAPPING_["_vend_sku"]], 			PDO::PARAM_STR,256);
				$stmt->bindParam(':_vend_created_at', 	$data[$_POST_FIELD_MAPPING_["_vend_created_at"]]); 
				$stmt->bindParam(':_vend_updated_at', 	$data[$_POST_FIELD_MAPPING_["_vend_updated_at"]]);
				$stmt->bindParam(':_fuzzy_match',		$fuzzymatch, PDO::PARAM_BOOL);
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
    	// Import field mapping
    	global $_DELETE_FIELD_MAPPING_;
    	
    	try {
			// Validate input
			$errs = validate_delete($request->getBody());
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

			$proc 	= !$devmode ? _DELETE_MAPPING_PROC_PROD_ : _DELETE_MAPPING_PROC_DEV_;
	
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$results = array();
			
			foreach($array as $data) {				
				// Execute the stored procedure
				$stmt = $conn->prepare(
					"CALL $proc(".
						 ":_product_id".
						",:_sku".
						",:_barcode".
						",:_company".
						",:_country_code".
						",:_dear_instance".
						",:_dear_product_id".
						",:_dear_sku".
						",:_vend_instance".
						",:_vend_product_id".
						",:_vend_sku".
					");"
				);
				$stmt->bindParam(':_product_id',		$data[$_DELETE_FIELD_MAPPING_["_product_id"]], 		PDO::PARAM_STR,36); 
				$stmt->bindParam(':_sku', 				$data[$_DELETE_FIELD_MAPPING_["_sku"]], 			PDO::PARAM_STR,256);
				$stmt->bindParam(':_barcode', 			$data[$_DELETE_FIELD_MAPPING_["_barcode"]], 		PDO::PARAM_STR,256);
				$stmt->bindParam(':_company', 			$data[$_DELETE_FIELD_MAPPING_["_company"]], 		PDO::PARAM_STR,256); 
				$stmt->bindParam(':_country_code', 		$data[$_DELETE_FIELD_MAPPING_["_country_code"]], 	PDO::PARAM_STR,2); 
				$stmt->bindParam(':_dear_instance', 	$data[$_DELETE_FIELD_MAPPING_["_dear_instance"]], 	PDO::PARAM_STR,100); 
				$stmt->bindParam(':_dear_product_id', 	$data[$_DELETE_FIELD_MAPPING_["_dear_product_id"]], PDO::PARAM_STR,36); 
				$stmt->bindParam(':_dear_sku', 			$data[$_DELETE_FIELD_MAPPING_["_dear_sku"]], 		PDO::PARAM_STR,256);
				$stmt->bindParam(':_vend_instance',		$data[$_DELETE_FIELD_MAPPING_["_vend_instance"]], 	PDO::PARAM_STR,100); 
				$stmt->bindParam(':_vend_product_id', 	$data[$_DELETE_FIELD_MAPPING_["_vend_product_id"]], PDO::PARAM_STR,36); 
				$stmt->bindParam(':_vend_sku', 			$data[$_DELETE_FIELD_MAPPING_["_vend_sku"]], 		PDO::PARAM_STR,256);
				$stmt->execute();
				$stmt->closeCursor();
				
				array_push($results, $data);
			}
			
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
