<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'validate.php';
require 'mapping.php';
require 'dbconfig.php';

define("_DEAR_DOMAIN_PROD_", "playbill");
define("_DEAR_DOMAIN_DEV_", "playbilldev");

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
 * Routes
 */
// GET route
$app->get(
    '/',
    function () {
    }
);

// PUT route
$app->put(
    '/',
    function ($request, $response, $args) use ($app) {
    	// Import field mapping
    	global $_FIELD_MAPPING_;
    	// Include OAUTH keys
		global $_DEAR_DOMAIN_AUTH_KEYS_;
		global $_VEND_DOMAIN_AUTH_KEYS_;
    	
    	try {
			// Validate input
			$errs = validate($request->getBody());
			$valid = empty($errs);
		
			// Return an array list of errors if invalid
			if(!$valid) {
				return 	$response->withStatus(400)
							->withHeader("Content-Type","application/json")
							->write(json_encode($errs));
			}
			
			// Set params
			$devmode = $request->getParam("devmode") === true || strcmp($request->getParam("devmode"), "true") == 0 ? true : false;
			$verbose = $request->getParam("verbose") === true || strcmp($request->getParam("verbose"), "true") == 0 ? true : false;
			
			$domain = $request->getParam("domain");
			$update_barcode = $request->getParam("update_barcode") === true || strcmp($request->getParam("update_barcode"), "true") == 0 ? true : false;
			$update_vend = $request->getParam("update_vend") === true || strcmp($request->getParam("update_vend"), "true") == 0 ? true : false;
			
			// Convert JSON data to array
			$array = json_decode($request->getBody(), true);

			$results = array();
			
			foreach($array as $data) {
				$old_sku = $data["old_sku"];
				$new_sku = $data["new_sku"];
				$result = array();
				
				$result["old_sku"] = $old_sku;
				$result["new_sku"] = $new_sku;
					
				// Execute the stored procedure
				$dear_url = _DEAR_PRODUCT_API_V1_;
				$dear_get_url =  $dear_url . "?sku=" . urlencode($old_sku);
				
				$dear_headers = array(
					"Content-type: application/json",
					_DEAR_AUTH_ACCOUNT_PARAM_   . ": " . $_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_ACCOUNT_PARAM_],
					_DEAR_AUTH_KEY_PARAM_       . ": " . $_DEAR_DOMAIN_AUTH_KEYS_[$domain][_DEAR_AUTH_KEY_PARAM_]
				);
		
				// Get Product from Dear API
				$get = _GET($dear_get_url, $dear_headers, false, false, $errs, $info);
				
				// Exract existing @ID field for update
				$get = json_decode($get,true);
				$dear_product = $get && is_array($get) && array_key_exists(_DEAR_PRODUCT_NODE_, $get) ? $get[_DEAR_PRODUCT_NODE_] : null;
				$dear_product = $dear_product && is_array($dear_product) ? $dear_product[0] : array();
				
				if($verbose == true) {
				    $result["dear_product"] = $dear_product;
				}
				
				// Check that there is a SKU change
				if( strcmp($dear_product["SKU"], $old_sku) == 0  && empty($errs) ) {				
					// Update Vend if selected
					if($update_vend) { 
						// Determine SKU used for Vend
						$vend_sku = strcmp($dear_product["Barcode"], "") == 0 || strcmp($dear_product["Barcode"], "null") == 0 ?
							$dear_product["SKU"] : 
							$dear_product["Barcode"]
						;
					
						$vend_url = sprintf(_VEND_PRODUCT_API_V1_, $domain);
						$vend_get_url =  $vend_url . "/?sku=" . urlencode($vend_sku);
				
						$vend_headers = array(
							"Content-type: application/json",
							_VEND_AUTH_PARAM_   . ": " . $_VEND_DOMAIN_AUTH_KEYS_[$domain][_VEND_AUTH_PARAM_]
						);
						
						// Get Product from Vend API
						$get = _GET($vend_get_url, $vend_headers, false, false, $errs, $info);
						
						// Exract existing @ID field for update
						$get = json_decode($get,true);
						$vend_products = $get && is_array($get) && array_key_exists(_VEND_PRODUCT_NODE_, $get) ? $get[_VEND_PRODUCT_NODE_] : null;
						$vend_product = array();
						
						if($vend_products && is_array($vend_products)) {
							foreach( $vend_products as $object ) {
								if( array_key_exists("sku", $object) && strcmp($object["sku"], $vend_sku) == 0) {
									$vend_product = $object;
									break;
								}
							}
						}
						
						if($verbose) $result["vend_product"] = $vend_product;
						
						// Extract Vend ID if possible
						$vend_id = array_key_exists("id", $vend_product) ? $vend_product["id"] : null;
						$result["vend_id"] = $vend_id;
						
						// Update Vend
						if($vend_id && empty($errs)) {
							$payload = array(
								"id"	=>	$vend_id,
								"sku"	=>	$new_sku
							);
														
							$payload = json_encode($payload);
					
							$post = _POST($vend_url, $payload, null, $vend_headers, false, false, $errs, $info);
						
							$post = json_decode($post, true);
							$vend_products = $post && is_array($post) && array_key_exists(_VEND_PRODUCT_POST_NODE_, $post) ? $post[_VEND_PRODUCT_POST_NODE_] : null;
							$vend_product = $vend_products && is_array($vend_products) ? $vend_products[0] : $vend_products;
							
							if($verbose == true) {
							    $result["vend_product"] = $vend_products;
							}
						}
					}
				
					$dear_id = $dear_product && array_key_exists("ID", $dear_product) ? $dear_product["ID"] : null;
					
					$result["dear_id"] = $dear_id;
					
					if($dear_id && empty($errs)) {
						$payload = array(
							"ID"	=>	$dear_id,
							"SKU"	=>	$new_sku
						);
						
						if($update_barcode)
							$payload["Barcode"] = $new_sku;
						
						$payload = json_encode($payload);
					
						$put = _PUT($dear_url, $payload, null, $dear_headers, false, false, $errs, $info);

						$put = json_decode($put, true);
                        $error_code =  $put && is_array($put) && array_key_exists("ErrorCode", $put) ? $put["ErrorCode"] : 0;
                        $error_message =  $put && is_array($put) && array_key_exists("Exception", $put) ? $put["Exception"] : 0;
						$dear_product = $put && is_array($put) && array_key_exists(_DEAR_PRODUCT_NODE_, $put) ? $put[_DEAR_PRODUCT_NODE_] : null;
						$dear_product = $dear_product && is_array($dear_product) ? $dear_product[0] : array();
						
						if($verbose == true) {
						    $result["dear_product"] = $dear_product;
						}
						
						if($error_code != 0) {
						    $result["error_code"] = $error_code;
						    $result["error_message"] = $error_message;
						}
					}
				} else {
					$dear_product = array();
				}
				
				if(!empty($errs)) {
					return 	$response->withStatus(400)
							->withHeader("Content-Type","application/json")
							->write(json_encode($errs));
				}
				
				if($result)
					array_push($results, $result);
			}

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
