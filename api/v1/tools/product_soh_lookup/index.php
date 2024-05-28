<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-delete.php';
require 'mapping.php';
require 'tokens.php';


define("_GET_PROC_", "tools_product_soh_lookup_v3");

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
    			 $_GET_FIELD_MAPPING_["_sku"] => $request->getParam($_GET_FIELD_MAPPING_["_sku"])
    			,$_GET_FIELD_MAPPING_["_name"] => $request->getParam($_GET_FIELD_MAPPING_["_name"])
    			,$_GET_FIELD_MAPPING_["_barcode"] => $request->getParam($_GET_FIELD_MAPPING_["_barcode"])
    			,$_GET_FIELD_MAPPING_["_company"] => $request->getParam($_GET_FIELD_MAPPING_["_company"])
    			,$_GET_FIELD_MAPPING_["_country_code"] => $request->getParam($_GET_FIELD_MAPPING_["_country_code"])
    			,$_GET_FIELD_MAPPING_["_active"] => $request->getParam($_GET_FIELD_MAPPING_["_active"])
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
	
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_sku".
					",:_name".
					",:_barcode".
					",:_company".
					",:_country_code".
					",:_active".
					",:__limit".
					",:__page".
					",@total_records".
				");"
			);
			$stmt->bindParam(':_sku', 			$data[$_GET_FIELD_MAPPING_["_sku"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_name', 			$data[$_GET_FIELD_MAPPING_["_name"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_barcode', 		$data[$_GET_FIELD_MAPPING_["_barcode"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_company', 		$data[$_GET_FIELD_MAPPING_["_company"]], PDO::PARAM_STR,256); 
			$stmt->bindParam(':_country_code', 	$data[$_GET_FIELD_MAPPING_["_country_code"]], PDO::PARAM_STR,2); 
			$stmt->bindParam(':_active', 		$data[$_GET_FIELD_MAPPING_["_active"]], PDO::PARAM_BOOL);
			$stmt->bindParam(":__limit", $limit);
			$stmt->bindParam(":__page", $page);
			$stmt->execute();
			
			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$row["tags"] = $row["tags"] != null ? explode(",", $row["tags"]) : array();
				$row["barcodes"] = $row["barcodes"] != null ? explode(",", $row["barcodes"]) : array();
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

/**
 * Run the Slim application
 */
$app->run();
