<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-delete.php';
require 'validate-put.php';
require 'mapping.php';
require 'tokens.php';

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
    	global $_API_CUSTOMERS_PUT_MAPPING;
    	global $_API_BIGCOMMERCE_REQUEST_HEADERS;
    	global $_API_BIGCOMMERCE_STORE_MAPPING;
    	global $_API_BIGCOMMERCE_CUSTOMERS_MAPPING;
    	global $_API_BIGCOMMERCE_CUSTOMER_GROUPS_MAPPING;
    	global $_WEBHOOK_CHANNEL;
		global $_WEBHOOK_SOURCE;
    	global $log;
    	
    	try {
    		//$log->info("POST request received");
    		
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
			
			// Verboseness of reply
			$verbose = "false";
			
			// Get system instance header
			$instance = $request->getHeader("Data-Instance");
			$instance = $instance != null && count($instance) > 0 ? $instance[0] : "";

			// Get auth account used for API to pass through
			$auth = $request->getHeader("Authorization");
			$auth = $auth != null && count($auth) > 0 ? $auth[0] : "";

			$payload = array();
			
			$log->info("Headers:");
			$log->info(json_encode($request->getHeaders()));
			$log->info("Payload:");
			$log->info(json_encode($data));
			
			// Check scope for Store/Customer/*
			$scope = $data[$_POST_FIELD_MAPPING_["_scope"]];
			$status = "active";
			$active = true;
			
			switch(strtolower($scope)) {
				case "store/customer/deleted":
					$status = "deleted";
					$active = false;
					break;
				
			}
			
			// Parse store hash
			$store_id = $data[$_POST_FIELD_MAPPING_["_producer"]];
			$store_id = explode("/", $store_id);
			$store_id = $store_id && count($store_id) == 2 ? $store_id[1] : null;
			
			$headers = $_API_BIGCOMMERCE_REQUEST_HEADERS[$store_id];
			
			// Pre-define local vars to prevent scope destructor
			$channel = null;
			$source = null;
			$source_instance = null;
			$id = $data["data"][$_POST_FIELD_MAPPING_["_id"]];
			$hash = null;
			$email = null;
			$type = null;
			$sub_type = null;
			$customer_no = null;
			$membership_no = null;
			$debtor_id = null;
			$firstname = null;
			$lastname = null;
			$othernames = null;
			$company = null;
			$contact_no = null;
			$schemes_json = [
				[
					 "scheme"	=> "default"
					,"tier"		=> null
					,"value"	=> 0
			
				]
			];
			$device_fingerprint = null;
			$device_ip_address = null;
			$advertising_id = null;
			$accepts_marketing = false;
			$accepts_communications = false;
			$timezone = (new DateTimeZone("UTC"))->getName();
			$active_at = null;
			$activated_at = null;
			$created_at = null;
			$updated_at = null;
			$updated_at_utc = null;
			$monotonic_version = null;
			$version_hash = null;
			$integrated = true;
			
			if($headers && $store_id && $id) {
				// Fetch store information
				$url =  sprintf(_API_BIGCOMMERCE_STORE_URL, $store_id);
				$log->info("[GET] request to BC API @ '$url':");
				// GET request to BigCommerce Store API
				$results = _GET($url, $headers, true, false, $errs, $info);
				// Http response code
				$hc = $info != null && array_key_exists("http_code", $info) && $info["http_code"] != null && is_numeric($info["http_code"]) ? $info["http_code"] : 500;

				if($results && $hc === 200) {
					// Convert JSON response to array
					$results = json_decode($results, true);
					
					$log->info(json_encode($results));
					
					// If default is not defined in the header then capture the store name
					if( !$instance || $instance === "" ) {
						$instance = $results[$_API_BIGCOMMERCE_STORE_MAPPING["_name"]];
					}
					
					$tzobj = $results[$_API_BIGCOMMERCE_STORE_MAPPING["_timezone"]];

					if( $tzobj ) {					
						$timezone = $tzobj[$_API_BIGCOMMERCE_STORE_MAPPING["_timezone_name"]];
					}
					
				} else {
					// Return error
					return 	$response->withStatus($hc)
						->withHeader("Content-Type","application/json")
						->write(json_encode($results));
				}
				
				// Fetch customer details
				$url =  sprintf(_API_BIGCOMMERCE_CUSTOMERS_URL, $store_id, $id);
				$log->info("[GET] request to BC API @ '$url':");
				// GET request to BigCommerce Customers API
				$results = _GET($url, $headers, true, false, $errs, $info);
				// Http response code
				$hc = $info != null && array_key_exists("http_code", $info) && $info["http_code"] != null && is_numeric($info["http_code"]) ? $info["http_code"] : 500;

				if($results && $hc === 200) {
					// Convert JSON response to array
					$results = json_decode($results, true);
					
					$log->info(json_encode($results));
					
					$email = strtolower($results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_email"]]);
					
					$group_id = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_customer_group_id"]];
					
					// Fetch Customer Group details if set
					if( $group_id ) {					
						// Fetch customer details
						$url =  sprintf(_API_BIGCOMMERCE_CUSTOMER_GROUPS_URL, $store_id, $group_id);
						// GET request to BigCommerce Customers API
						$group_results = _GET($url, $headers, true, false, $errs, $info);
						// Http response code
						$hc = $info != null && array_key_exists("http_code", $info) && $info["http_code"] != null && is_numeric($info["http_code"]) ? $info["http_code"] : 500;
						$log->info(json_encode($group_results));
			
						if($group_results && $hc === 200) {
							$group_results = json_decode($group_results, true);
							$type = $group_results[$_API_BIGCOMMERCE_CUSTOMER_GROUPS_MAPPING["_name"]];
						}
					}
					
					$custom_fields = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_form_fields"]];
					
					// Parse any custom fields for customer and membership numbers
					if( $custom_fields && count($custom_fields) > 0 ) {
						foreach($custom_fields as $field) {
							$name = $field["name"];
							$value = $field["value"];
							
							switch(strtolower($name)) {
								case "customer no":
									$customer_no = $value;
									break;
								case "membership no":
									$membership_no = $value;
									break;
							}
						}
					}
					
					$debtor_id = null;
					$firstname = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_first_name"]];
					$lastname = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_last_name"]];
					$company = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_company"]];
					$contact_no = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_phone"]];
					$schemes_json = [];
					$schemes_json[] = [
						 "scheme"	=> "storecredit"
						,"tier"		=> null
						,"value"	=> $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_store_credit"]]			
					];
					$device_ip_address = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_registration_ip_address"]];
					$accepts_marketing = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_accepts_marketing"]];
					$accepts_communications = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_accepts_communications"]];

					$date = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_date_created"]];
					$created_at = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_date_created"]];
					$updated_at = $results[$_API_BIGCOMMERCE_CUSTOMERS_MAPPING["_date_modified"]];
					
					// Convert date from RFC 2822 format "D, d M Y H:i:s O" to "Y-m-d H:i:s"
					// and capture timezone in PHP TZ standard foramt
					
					$dt_1 = DateTime::createFromFormat(DateTime::RFC2822, $date);
					$dt_2 = DateTime::createFromFormat(DateTime::RFC2822, $created_at);
					$dt_3 = DateTime::createFromFormat(DateTime::RFC2822, $updated_at);
					
					$updated_at_utc = $dt_3->format("Y-m-d H:i:s");
					
					// Apply timezone to get local times
					$dt_1->setTimezone(new DateTimeZone($timezone));
					$dt_2->setTimezone(new DateTimeZone($timezone));
					$dt_3->setTimezone(new DateTimeZone($timezone));

					$date = $dt_1->format("Y-m-d H:i:s");
					$created_at = $dt_2->format("Y-m-d H:i:s");
					$updated_at = $dt_3->format("Y-m-d H:i:s");
					
					$active_at = $updated_at;
					$activated_at = $created_at;
					
					// Monotonic version if enabled by provider
					$monotonic_version = 0;
					
					// Hash result to create a version hash
					$version_hash = hash("sha256", json_encode($results));
					
					// Generate custom unique hash for customer
					$hash = hash(
						 "sha256"
						,strtolower(trim($instance))
						 .
						 strtolower(trim(($membership_no && $membership_no !== "" ? $membership_no : $email)))
					);
				} else {
					// Return error
					return 	$response->withStatus($hc)
						->withHeader("Content-Type","application/json")
						->write(json_encode($results));
				}
			}
		
			// Reset results to prevent accidental return of unintended result
			$results = array();

			$payload = [
				 $_API_CUSTOMERS_PUT_MAPPING["_hash"]					=>		$hash
				,$_API_CUSTOMERS_PUT_MAPPING["_channel"]				=>		$_WEBHOOK_CHANNEL
				,$_API_CUSTOMERS_PUT_MAPPING["_source"]					=>		$_WEBHOOK_SOURCE
				,$_API_CUSTOMERS_PUT_MAPPING["_source_instance"]		=>		$instance
				,$_API_CUSTOMERS_PUT_MAPPING["_source_id"]				=>		$id
				,$_API_CUSTOMERS_PUT_MAPPING["_type"]					=>		$type
				,$_API_CUSTOMERS_PUT_MAPPING["_sub_type"]				=>		$sub_type
				,$_API_CUSTOMERS_PUT_MAPPING["_email"]					=>		$email
				,$_API_CUSTOMERS_PUT_MAPPING["_status"]					=>		$status
				,$_API_CUSTOMERS_PUT_MAPPING["_customer_no"]			=>		$customer_no
				,$_API_CUSTOMERS_PUT_MAPPING["_membership_no"]			=>		$membership_no
				,$_API_CUSTOMERS_PUT_MAPPING["_debtor_id"]				=>		$debtor_id
				,$_API_CUSTOMERS_PUT_MAPPING["_firstname"]				=>		$firstname
				,$_API_CUSTOMERS_PUT_MAPPING["_lastname"]				=>		$lastname
				,$_API_CUSTOMERS_PUT_MAPPING["_othernames"]				=>		$othernames
				,$_API_CUSTOMERS_PUT_MAPPING["_contact_no"]				=>		$contact_no
				,$_API_CUSTOMERS_PUT_MAPPING["_company"]				=>		$company
				,$_API_CUSTOMERS_PUT_MAPPING["_schemes"]				=>		$schemes_json
				,$_API_CUSTOMERS_PUT_MAPPING["_device_fingerprint"]		=>		$device_fingerprint
				,$_API_CUSTOMERS_PUT_MAPPING["_device_ip_address"]		=>		$device_ip_address
				,$_API_CUSTOMERS_PUT_MAPPING["_advertising_id"]			=>		$advertising_id
				,$_API_CUSTOMERS_PUT_MAPPING["_accepts_marketing"]		=>		$accepts_marketing
				,$_API_CUSTOMERS_PUT_MAPPING["_accepts_communications"]	=>		$accepts_communications
				,$_API_CUSTOMERS_PUT_MAPPING["_timezone"]				=>		$timezone
				,$_API_CUSTOMERS_PUT_MAPPING["_active"]					=>		$active
				,$_API_CUSTOMERS_PUT_MAPPING["_active_at"]				=>		$active_at
				,$_API_CUSTOMERS_PUT_MAPPING["_activated_at"]			=>		$activated_at
				,$_API_CUSTOMERS_PUT_MAPPING["_created_at"]				=>		$created_at
				,$_API_CUSTOMERS_PUT_MAPPING["_updated_at"]				=>		$updated_at
				,$_API_CUSTOMERS_PUT_MAPPING["_updated_at_utc"]			=>		$updated_at_utc
				,$_API_CUSTOMERS_PUT_MAPPING["_monotonic_version"]		=>		$monotonic_version
				,$_API_CUSTOMERS_PUT_MAPPING["_version_hash"]			=>		$version_hash
				,$_API_CUSTOMERS_PUT_MAPPING["_integrate"]				=>		$integrated
			];
			
			$log->info("Re-mapped Payload:");
			$log->info(json_encode($payload));
			
			// Forward headers
			$headers = array(
				"Authorization: ".$auth
			);
			
			// Execute the stored procedure
			$url =  _API_CUSTOMERS_URL . "?" . "verbose=".$verbose;
			$log->info("[PUT] request to PBG API @ '$url':");
			// POST to Customers API
			$results = _PUT($url, json_encode($payload), null, $headers, false, false, $errs, $info);
			//$results = json_encode($payload);

			$log->info("Payload:");
			$log->info($results);
			
			
			// Throw error if found
			if(!empty($errs)) {
				throw new JsonEncodedException($errs);
			}
			
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