<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-put.php';
require 'validate-delete.php';
require 'mapping.php';
require 'tokens.php';

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
   			return 	__method_not_allowed("GET", $response);
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

// POST route
$app->post(
    '/',
    function ($request, $response, $args) use ($app) {
    	// Import field mapping
    	global $_POST_FIELD_MAPPING_;
    	global $_API_BIGCOMMERCE_REQUEST_HEADERS;
    	global $_API_BIGCOMMERCE_STORE_INSTANCE_MAPPING;
    	global $_API_BIGCOMMERCE_CATALOG_URL;
    	global $_API_BIGCOMMERCE_PRODUCTS_VARIANTS_MAPPING;
    	global $log;
    	
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

			$instance = $data[$_POST_FIELD_MAPPING_["_source_instance"]];
			$sku = $data[$_POST_FIELD_MAPPING_["_sku"]];
			
			$store_id = $_API_BIGCOMMERCE_STORE_INSTANCE_MAPPING[$instance];
			
			// Un-mapped BigCommerce instance
			if( !$store_id || empty($store_id) ) {
				// Return error
				return 	$response->withStatus(400)
					->withHeader("Content-Type","application/json")
					->write(json_encode([
						"ErrorCode"	=>	400,
						"Exception"	=>	"$instance is not an integrated BigCommerce shop instance"
					]));
			}
			
			$headers = $_API_BIGCOMMERCE_REQUEST_HEADERS[$store_id];
			$payload = [];
			
			$pid = null;
			$vid = null;
						
			// Fetch Variant details
			$url =  sprintf(_API_BIGCOMMERCE_CATALOG_VARIANTS_URL, $store_id, $sku);
			$log->info("[GET] request to BC API @ '$url':");
			// GET request to BigCommerce Catalog/Products/Variants API
			$results = _GET($url, $headers, true, false, $errs, $info);
			// Http response code
			$hc = $info != null && array_key_exists("http_code", $info) && $info["http_code"] != null && is_numeric($info["http_code"]) ? $info["http_code"] : 500;

			// Return error
			if($hc !== 200) {
				// Return error
				return 	$response->withStatus($hc)
					->withHeader("Content-Type","application/json")
					->write(json_encode($results));
			}

			$results = json_decode($results, true);
			$results = is_array($results) && !empty($results) && array_key_exists("data", $results) ? $results["data"] : [];
		
			// Check products catalog
			if( empty($results) ) {
				// Fetch Product details
				$url =  sprintf(_API_BIGCOMMERCE_CATALOG_PRODUCTS_URL, $store_id, $sku);
				$log->info("[GET] request to BC API @ '$url':");
				// GET request to BigCommerce Catalog/Products API
				$results = _GET($url, $headers, true, false, $errs, $info);
				// Http response code
				$hc = $info != null && array_key_exists("http_code", $info) && $info["http_code"] != null && is_numeric($info["http_code"]) ? $info["http_code"] : 500;

				// Return error
				if($hc !== 200) {
					// Return error
					return 	$response->withStatus($hc)
						->withHeader("Content-Type","application/json")
						->write(json_encode($results));
				}

				$results = json_decode($results, true);
				$results = is_array($results) && !empty($results) && array_key_exists("data", $results) ? $results["data"] : [];
				
				if( empty($results) ) {
					// Return error
					return 	$response->withStatus(400)
						->withHeader("Content-Type","application/json")
						->write(json_encode([
							"ErrorCode"	=>	400,
							"Exception"	=>	"SKU [$sku] does not exist in BigCommerce instance"
						]));
				}
				
				$i = array_search($sku, array_column($results, 'sku'));
				$results = $results[$i];
				
				$pid = $results["id"];
			} else {
				$i = array_search($sku, array_column($results, 'sku'));
				$results = $results[$i];
			
				$pid = $results["product_id"];
				$vid = $results["id"];
			}

			$available = $data[$_POST_FIELD_MAPPING_["_available"]];
			$available = $available < 0 ? 0 : $available;
			
			$payload[$_API_BIGCOMMERCE_PRODUCTS_VARIANTS_MAPPING["_inventory_level"]] = $available;

			$voh = $data[$_POST_FIELD_MAPPING_["_value_on_hand"]];
			$soh = $data[$_POST_FIELD_MAPPING_["_on_hand"]];
			
			if( !empty($voh) && !empty($soh) ) {			
				$cost = round($voh / $soh, 2);
				$payload[$_API_BIGCOMMERCE_PRODUCTS_VARIANTS_MAPPING["_cost_price"]] = $cost;
			}
			
			$url = $vid !== null ? 
					sprintf(_API_BIGCOMMERCE_PRODUCTS_VARIANTS_URL, $store_id, $pid, $vid) 
				:	sprintf(_API_BIGCOMMERCE_PRODUCTS_URL, $store_id, $pid)
			;
			
			$log->info("[PUT] request to BC API @ '$url':");
			// PUT request to BigCommerce Catalog/Products[/Varaints] API
			$results = _PUT($url, json_encode($payload), null, $headers, false, false, $errs, $info);
			// Http response code
			$hc = $info != null && array_key_exists("http_code", $info) && $info["http_code"] != null && is_numeric($info["http_code"]) ? $info["http_code"] : 500;
			// Convert JSON response to array
			$results = json_decode($results);
					
			// Return input body as results with Http code passed on
			return 	$response->withStatus($hc)
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
    	global $_API_BIGCOMMERCE_REQUEST_HEADERS;
    	global $_API_BIGCOMMERCE_STORE_INSTANCE_MAPPING;
    	global $_API_BIGCOMMERCE_PRODUCTS_VARIANTS_MAPPING;
    	global $log;
    	
    	try {
			// Validate input
			$contentType = $request->getContentType();
    		$body = $request->getBody(); 		

			// Validate input
			$errs = validate_put($body);
			$valid = empty($errs);
		
			// Return an array list of errors if invalid
			if(!$valid) {
				return 	$response->withStatus(400)
							->withHeader("Content-Type","application/json")
							->write(json_encode($errs));
			}
			
			// Convert JSON data to array
			$data = json_decode($body, true);
			
			$log->info("Payload:");
			$log->info(json_encode($data));
			
			$instance = $data[$_PUT_FIELD_MAPPING_["_source_instance"]];
			
			$store_id = $_API_BIGCOMMERCE_STORE_INSTANCE_MAPPING[$instance];
			
			// Un-mapped BigCommerce instance
			if( !$store_id || empty($store_id) ) {
				// Return error
				return 	$response->withStatus(400)
					->withHeader("Content-Type","application/json")
					->write(json_encode([
						"ErrorCode"	=>	400,
						"Exception"	=>	"$instance is not an integrated BigCommerce shop instance"
					]));
			}
			
			$headers = $_API_BIGCOMMERCE_REQUEST_HEADERS[$store_id];
			$payload = [];

			$pid = $data[$_PUT_FIELD_MAPPING_["_product_id"]];
			$vid = $data[$_PUT_FIELD_MAPPING_["_variant_id"]];

			$available = $data[$_PUT_FIELD_MAPPING_["_available"]];
			$available = $available < 0 ? 0 : $available;
			
			$payload[$_API_BIGCOMMERCE_PRODUCTS_VARIANTS_MAPPING["_inventory_level"]] = $available;

			$voh = $data[$_PUT_FIELD_MAPPING_["_value_on_hand"]];
			$soh = $data[$_PUT_FIELD_MAPPING_["_on_hand"]];
			
			if( !empty($voh) && !empty($soh) ) {			
				$cost = round($voh / $soh, 2);
				$payload[$_API_BIGCOMMERCE_PRODUCTS_VARIANTS_MAPPING["_cost_price"]] = $cost;
			}

			$url = $vid !== null ? 
					sprintf(_API_BIGCOMMERCE_PRODUCTS_VARIANTS_URL, $store_id, $pid, $vid) 
				:	sprintf(_API_BIGCOMMERCE_PRODUCTS_URL, $store_id, $pid)
			;
			
			$log->info("[PUT] request to BC API @ '$url':");
			// PUT request to BigCommerce Catalog/Products[/Varaints] API
			$results = _PUT($url, json_encode($payload), null, $headers, false, false, $errs, $info);
			// Http response code
			$hc = $info != null && array_key_exists("http_code", $info) && $info["http_code"] != null && is_numeric($info["http_code"]) ? $info["http_code"] : 500;
			// Convert JSON response to array
			$results = json_decode($results);
			
			// Return input body as results with Http code passed on
			return 	$response->withStatus($hc)
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
