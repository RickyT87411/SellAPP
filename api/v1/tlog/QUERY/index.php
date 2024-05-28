<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-delete.php';
require 'validate-put.php';
require 'mapping.php';
require 'tokens.php';

define("_POST_PROC_", "tlog_query_v5");

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
			
			// Convert JSON data to array
			$data = json_decode($body, true);
			
			// Set params
			$order_by = $request->getParam("order_by");
			$asc = $request->getParam("asc") === true || strcmp($request->getParam("asc"), "true") == 0 ? true : false;
			
			$data[$_POST_FIELD_MAPPING_["_order_by"]] = $order_by;
			$data[$_POST_FIELD_MAPPING_["_asc"]] = $asc;
			
			// DB Settings
			$host 	= _HOST_URI_;
			$dbname = _HOST_DB_;
			$usr 	= _HOST_USER_;
			$pwd 	= _HOST_PASSWORD_;
			
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd, array(PDO::ATTR_PERSISTENT => true));
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$page = $request->getParam("page") ? $request->getParam("page") : 1;
			$page = !is_numeric($page) ? 1 : $page;
			$page = $page <= 0 ? 1 : $page;
			
			$limit = $request->getParam("limit") ? $request->getParam("limit") : _DEFAULT_PAGE_LIMIT_;
			$limit = !is_numeric($limit) ? _DEFAULT_PAGE_LIMIT_ : $limit;
			$limit = $limit <= 0 ? _DEFAULT_PAGE_LIMIT_ : $limit > _MAX_PAGE_LIMIT_ ? _MAX_PAGE_LIMIT_ : $limit;
			
			$errs = array();
			$results = array();
			
			// Execute query and convert results to tlog JSON format
			$results = __fetch_tlog_headers($conn, $data, $limit, $page, $devmode);
			
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

function __fetch_tlog_headers(&$conn, $data, $limit, $page, $devmode = false) {
	if(!$conn)
		return null;
	try {
		global $_POST_FIELD_MAPPING_;
		
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
		
		$proc = _POST_PROC_;
		
		$asc = $data[$_POST_FIELD_MAPPING_["_asc"]] === true || strcmp($data[$_POST_FIELD_MAPPING_["_asc"]], "true") == 0 ? true : false;
		
		$tmp = array();

		// Execute the stored procedure
		$stmt = $conn->prepare(
			"CALL $proc(".
				 ":_tlog_header_type".
				",:_transaction_date_from".
				",:_transaction_date_to".
				",:_transaction_created_from".
				",:_transaction_created_to".
				",:_transaction_updated_from".
				",:_transaction_updated_to".
				",:_process_transaction_date_csv".
				",:_process_transaction_channel_csv".
				",:_process_transaction_source_csv".
				",:_process_transaction_source_instance_csv".
				",:_process_transaction_type_csv".
				",:_process_transaction_id_csv".
				",:_process_transaction_title_csv".
				",:_process_transaction_status_csv".
				",:_process_location_type_csv".
				",:_process_location_code_csv".
				",:_process_outlet_type_csv".
				",:_process_outlet_code_csv".
				",:_process_register_type_csv".
				",:_process_register_code_csv".
				",:_process_customer_type_csv".
				",:_process_customer_code_csv".
				",:_process_supplier_type_csv".
				",:_process_supplier_code_csv".
				",:_process_user_code_csv".
				",:_ignore_transaction_date_csv".
				",:_ignore_transaction_channel_csv".
				",:_ignore_transaction_source_csv".
				",:_ignore_transaction_source_instance_csv".
				",:_ignore_transaction_type_csv".
				",:_ignore_transaction_id_csv".
				",:_ignore_transaction_title_csv".
				",:_ignore_transaction_status_csv".
				",:_ignore_location_type_csv".
				",:_ignore_location_code_csv".
				",:_ignore_outlet_type_csv".
				",:_ignore_outlet_code_csv".
				",:_ignore_register_type_csv".
				",:_ignore_register_code_csv".
				",:_ignore_customer_type_csv".
				",:_ignore_customer_code_csv".
				",:_ignore_supplier_type_csv".
				",:_ignore_supplier_code_csv".
				",:_ignore_user_code_csv".
				",:_since".
				",:_include_audit_trail".
				",:_status".
				",:_order_by".
				",:_asc".
				",:__limit".
				",:__page".
				",@total_records".
			");"
		);
		$stmt->bindParam(":_tlog_header_type", $data[$_POST_FIELD_MAPPING_["_tlog_header_type"]]);
		$stmt->bindParam(":_transaction_date_from", $data[$_POST_FIELD_MAPPING_["_transaction_date_from"]]);
		$stmt->bindParam(":_transaction_date_to", $data[$_POST_FIELD_MAPPING_["_transaction_date_to"]]);
		$stmt->bindParam(":_transaction_created_from", $data[$_POST_FIELD_MAPPING_["_transaction_created_from"]]);
		$stmt->bindParam(":_transaction_created_to", $data[$_POST_FIELD_MAPPING_["_transaction_created_to"]]);
		$stmt->bindParam(":_transaction_updated_from", $data[$_POST_FIELD_MAPPING_["_transaction_updated_from"]]);
		$stmt->bindParam(":_transaction_updated_to", $data[$_POST_FIELD_MAPPING_["_transaction_updated_to"]]);
		$stmt->bindParam(":_process_transaction_date_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_date_csv"]]);
		$stmt->bindParam(":_process_transaction_channel_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_channel_csv"]]);
		$stmt->bindParam(":_process_transaction_source_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_source_csv"]]);
		$stmt->bindParam(":_process_transaction_source_instance_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_source_instance_csv"]]);
		$stmt->bindParam(":_process_transaction_type_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_type_csv"]]);
		$stmt->bindParam(":_process_transaction_id_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_id_csv"]]);
		$stmt->bindParam(":_process_transaction_title_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_title_csv"]]);
		$stmt->bindParam(":_process_transaction_status_csv", $data[$_POST_FIELD_MAPPING_["_process_transaction_status_csv"]]);
		$stmt->bindParam(":_process_location_type_csv", $data[$_POST_FIELD_MAPPING_["_process_location_type_csv"]]);
		$stmt->bindParam(":_process_location_code_csv", $data[$_POST_FIELD_MAPPING_["_process_location_code_csv"]]);
		$stmt->bindParam(":_process_outlet_type_csv", $data[$_POST_FIELD_MAPPING_["_process_outlet_type_csv"]]);
		$stmt->bindParam(":_process_outlet_code_csv", $data[$_POST_FIELD_MAPPING_["_process_outlet_code_csv"]]);
		$stmt->bindParam(":_process_register_type_csv", $data[$_POST_FIELD_MAPPING_["_process_register_type_csv"]]);
		$stmt->bindParam(":_process_register_code_csv", $data[$_POST_FIELD_MAPPING_["_process_register_code_csv"]]);
		$stmt->bindParam(":_process_customer_type_csv", $data[$_POST_FIELD_MAPPING_["_process_customer_type_csv"]]);
		$stmt->bindParam(":_process_customer_code_csv", $data[$_POST_FIELD_MAPPING_["_process_customer_code_csv"]]);
		$stmt->bindParam(":_process_supplier_type_csv", $data[$_POST_FIELD_MAPPING_["_process_supplier_type_csv"]]);
		$stmt->bindParam(":_process_supplier_code_csv", $data[$_POST_FIELD_MAPPING_["_process_supplier_code_csv"]]);
		$stmt->bindParam(":_process_user_code_csv", $data[$_POST_FIELD_MAPPING_["_process_user_code_csv"]]);
		$stmt->bindParam(":_ignore_transaction_date_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_date_csv"]]);
		$stmt->bindParam(":_ignore_transaction_channel_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_channel_csv"]]);
		$stmt->bindParam(":_ignore_transaction_source_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_source_csv"]]);
		$stmt->bindParam(":_ignore_transaction_source_instance_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_source_instance_csv"]]);
		$stmt->bindParam(":_ignore_transaction_type_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_type_csv"]]);
		$stmt->bindParam(":_ignore_transaction_id_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_id_csv"]]);
		$stmt->bindParam(":_ignore_transaction_title_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_title_csv"]]);
		$stmt->bindParam(":_ignore_transaction_status_csv", $data[$_POST_FIELD_MAPPING_["_ignore_transaction_status_csv"]]);
		$stmt->bindParam(":_ignore_location_type_csv", $data[$_POST_FIELD_MAPPING_["_ignore_location_type_csv"]]);
		$stmt->bindParam(":_ignore_location_code_csv", $data[$_POST_FIELD_MAPPING_["_ignore_location_code_csv"]]);
		$stmt->bindParam(":_ignore_outlet_type_csv", $data[$_POST_FIELD_MAPPING_["_ignore_outlet_type_csv"]]);
		$stmt->bindParam(":_ignore_outlet_code_csv", $data[$_POST_FIELD_MAPPING_["_ignore_outlet_code_csv"]]);
		$stmt->bindParam(":_ignore_register_type_csv", $data[$_POST_FIELD_MAPPING_["_ignore_register_type_csv"]]);
		$stmt->bindParam(":_ignore_register_code_csv", $data[$_POST_FIELD_MAPPING_["_ignore_register_code_csv"]]);
		$stmt->bindParam(":_ignore_customer_type_csv", $data[$_POST_FIELD_MAPPING_["_ignore_customer_type_csv"]]);
		$stmt->bindParam(":_ignore_customer_code_csv", $data[$_POST_FIELD_MAPPING_["_ignore_customer_code_csv"]]);
		$stmt->bindParam(":_ignore_supplier_type_csv", $data[$_POST_FIELD_MAPPING_["_ignore_supplier_type_csv"]]);
		$stmt->bindParam(":_ignore_supplier_code_csv", $data[$_POST_FIELD_MAPPING_["_ignore_supplier_code_csv"]]);
		$stmt->bindParam(":_ignore_user_code_csv", $data[$_POST_FIELD_MAPPING_["_ignore_user_code_csv"]]);
		$stmt->bindParam(":_since", $data[$_POST_FIELD_MAPPING_["_since"]]);
		$stmt->bindParam(":_include_audit_trail", $data[$_POST_FIELD_MAPPING_["_include_audit_trail"]]);
		$stmt->bindParam(":_status", $data[$_POST_FIELD_MAPPING_["_status"]]);
		$stmt->bindParam(":_order_by", $data[$_POST_FIELD_MAPPING_["_order_by"]]);
		$stmt->bindParam(":_asc", $asc, PDO::PARAM_BOOL);
		$stmt->bindParam(":__limit", $limit);
		$stmt->bindParam(":__page", $page);
		$stmt->execute();
		
		$recordset = array();
		
		$header = array();
		$lines = array();
		$tenders = array();
		
		// Record set counter
		$i = 0;
		
		// Extract each record set in order of:
		//	1. @HEADER
		//	2. @LINES
		//	3. @TENDERS
		while ( $stmt->columnCount() ) {
			$row = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// Chained hash map of @LINES and @TENDERS
			$tmp = array();
			
			// Store @LINES and @TENDERS in a chained hash map
			if($i++ > 0) {
				foreach($row as $record) {
					$id = $record["tlog_header_id"];
					
					// Create chained hash index if abscent
					if(!array_key_exists($id, $tmp)) 
						$tmp[$id] = array();
						
					// Append record to @HEARD id index
					$tmp[$id][] = $record;
				}
			} else {
				// Return the @HEADER record as-is
				$tmp = $row;
			}
		
			$recordset[] = $tmp;
			
			$stmt->nextRowSet();
		}

		$stmt->closeCursor();
		
		// Assign specific record set to respective result array
		if(count($recordset) == 3 ) {
			$header = $recordset[0];
			$lines = $recordset[1];
			$tenders = $recordset[2];
		}
		
		// Capture output parameter
		$q = $conn->query("select @total_records as total_records;")->fetch(PDO::FETCH_ASSOC);
		$total_records = $q && array_key_exists("total_records", $q) ? $q["total_records"] : 1;

		$results["pagination"]["page_records"] = count($header);
		$results["pagination"]["total_records"] = $total_records * 1;
		$results["pagination"]["total_pages"] = ceil($total_records / $limit);
		
		// Parse record set
		foreach($header as $record) {
			$id = $record["id"];
			// Convert @HEADER to tlog JSON format
			$trx = __convert_header_to_tlog_json($record);
			
			// Create empty indexes for @LINES and @TENDERS
			$trx["lines"] = array();
			$trx["tenders"] = array();
			
			// Parse each matching @LINE
			if($lines != null && is_array($lines) && count($lines) > 0 && 
				array_key_exists($id, $lines) && $lines[$id] != null && is_array($lines[$id])) 
			{
				foreach($lines[$id] as $line) {
					$trx["lines"][] = __convert_line_to_tlog_format($line);
				}
			}
			// Parse each matching @TENDER
			if($tenders != null && is_array($tenders) && count($tenders) > 0 && 
				array_key_exists($id, $tenders) && $tenders[$id] != null && is_array($tenders[$id]) ) 
			{
				foreach($tenders[$id] as $tender) {
					$trx["tenders"][] = __convert_tender_to_tlog_format($tender);
				}
			}

			// Remove any empty @LINES or @TENDERS indexes
			if(array_key_exists("lines", $trx) && count($trx["lines"]) === 0) unset($trx["lines"]);
			if(array_key_exists("tenders", $trx) && count($trx["tenders"]) === 0) unset($trx["tenders"]);

			$results["records"][] = $trx;
		}
		
		return $results;
	} catch(Exception $e) {
		throw new JsonEncodedException(
			to_error(500, $e->getMessage())
		);
	}
}
function __convert_header_to_tlog_json($data) {
	if(!$data)
		return null;
		
	try {
		global $_TLOG_HEADER_OBJECT_;
		global $_USER_OBJECT_;			
		global $_CUSTOMER_OBJECT_;		
		global $_SUPPLIER_OBJECT_;		
		global $_LOCATION_OBJECT_;		
		global $_OUTLET_OBJECT_;		
		global $_REGISTER_OBJECT_;		
		global $_TRANSFER_OBJECT_;
		global $_TRANSFER_SOURCE_OBJECT_;
		global $_TRANSFER_TARGET_OBJECT_;
		global $_DISPATCH_OBJECT_;
		
		if(!$data)
			return null;
		
		$header = array();	
		// Header => *
		$user = array();
		$customer = array();
		$supplier = array();
		$location = array();
		$outlet   = array();
		$register = array();
		$transfer = array();
		$dispatch = array();
		// Header => Transfer => *
		$transfer_source = array();
		$transfer_target = array();

		// Map header
		foreach($_TLOG_HEADER_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $header, $target_field);
		}
		// Map user
		foreach($_USER_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $user, $target_field);
		}
		// Map customer
		foreach($_CUSTOMER_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $customer, $target_field);
		}
		// Map supplier
		foreach($_SUPPLIER_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $supplier, $target_field);
		}
		// Map location
		foreach($_LOCATION_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $location, $target_field);
		}
		// Map outlet
		foreach($_OUTLET_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $outlet, $target_field);
		}
		// Map register
		foreach($_REGISTER_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $register, $target_field);
		}
		// Map transfer
		foreach($_TRANSFER_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $transfer, $target_field);
		}
		// Map transfer_source
		foreach($_TRANSFER_SOURCE_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $transfer_source, $target_field);
		}
		// Map transfer_target
		foreach($_TRANSFER_TARGET_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $transfer_target, $target_field);
		}
		// Map dispatch
		foreach($_DISPATCH_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $dispatch, $target_field);
		}
		
		if(!empty($transfer_source)) 	$transfer[$_TRANSFER_OBJECT_["_transfer_source_object"]] = $transfer_source;
		if(!empty($transfer_target)) 	$transfer[$_TRANSFER_OBJECT_["_transfer_target_object"]] = $transfer_target;
		if(!empty($user)) 				$header[$_TLOG_HEADER_OBJECT_["_user_object"]] = $user;
		if(!empty($customer)) 			$header[$_TLOG_HEADER_OBJECT_["_customer_object"]] = $customer;
		if(!empty($supplier)) 			$header[$_TLOG_HEADER_OBJECT_["_supplier_object"]] = $supplier;
		if(!empty($location)) 			$header[$_TLOG_HEADER_OBJECT_["_location_object"]] = $location;
		if(!empty($outlet)) 			$header[$_TLOG_HEADER_OBJECT_["_outlet_object"]] = $outlet;
		if(!empty($register)) 			$header[$_TLOG_HEADER_OBJECT_["_register_object"]] = $register;
		if(!empty($transfer)) 			$header[$_TLOG_HEADER_OBJECT_["_transfer_object"]] = $transfer;
		if(!empty($dispatch)) 			$header[$_TLOG_HEADER_OBJECT_["_dispatch_object"]] = $dispatch;
		
		return $header;
	} catch(Exception $e) {
		throw new JsonEncodedException(
			to_error(500, $e->getMessage())
		);
	}
}

function __convert_line_to_tlog_format($data){
	if(!$data)
		return null;
	try {
		global $_TLOG_LINE_OBJECT_;
		global $_PRODUCT_OBJECT_;			
		global $_PRODUCT_FAMILY_OBJECT_;	
		global $_CATEGORY_OBJECT_;			
		global $_ATTRIBUTE_OBJECT_;			
		global $_SUPPLIER_PRODUCT_OBJECT_;
		global $_RETURN_REASON_OBJECT_;		
		global $_DISCOUNT_REASON_OBJECT_;
		global $_VOID_REASON_OBJECT_;		
		global $_PROMOTION_OBJECT_;			
		global $_DISPATCH_OBJECT_;			
		global $_COMMISSION_OBJECT_;		
		global $_ROYALTY_OBJECT_;			
		global $_PROFIT_SHARE_OBJECT_;
		global $_TRANSFER_OBJECT_;
		global $_TRANSFER_SOURCE_OBJECT_;
		global $_TRANSFER_TARGET_OBJECT_;
		
		$line = array();
		// Line => *
		$product = array();
		$discount_reason = array();
		$return_reason = array();
		$void_reason = array();
		$promotion = array();
		$dispatch = array();
		$commission = array();
		$royalty = array();
		$profit_share = array();
		$transfer = array();
		
		// Line => Transfer => *
		$transfer_source = array();
		$transfer_target = array();
	
		// Line => Product => *
		$family = array();
		$supplier_product = array();
		$categories = array();
		$attributes = array();

		// Map line
		foreach($_TLOG_LINE_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $line, $target_field);
		}
		// Map product
		foreach($_PRODUCT_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $product, $target_field);
		}
		// Map family
		foreach($_PRODUCT_FAMILY_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $family, $target_field);
		}
		// Map categories
		foreach($_CATEGORY_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $categories, $target_field);
		}
		// Map attributes
		foreach($_ATTRIBUTE_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $attributes, $target_field);
		}
		// Map supplier_product
		foreach($_SUPPLIER_PRODUCT_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $supplier_product, $target_field);
		}
		// Map return_reason
		foreach($_RETURN_REASON_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $return_reason, $target_field);
		}
		// Map discount_reason
		foreach($_DISCOUNT_REASON_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $discount_reason, $target_field);
		}
		// Map void_reason
		foreach($_VOID_REASON_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $void_reason, $target_field);
		}
		// Map promotion
		foreach($_PROMOTION_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $promotion, $target_field);
		}
		// Map dispatch
		foreach($_DISPATCH_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $dispatch, $target_field);
		}
		// Map commission
		foreach($_COMMISSION_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $commission, $target_field);
		}
		// Map royalty
		foreach($_ROYALTY_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $royalty, $target_field);
		}
		// Map profit_share
		foreach($_PROFIT_SHARE_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $profit_share, $target_field);
		}
		// Map transfer_target
		foreach($_TRANSFER_TARGET_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $transfer_target, $target_field);
		}
		
		
		if(!empty($family)) 			$product[$_PRODUCT_OBJECT_["_product_family_object"]] = $family;
		if(!empty($supplier_product)) 	$product[$_PRODUCT_OBJECT_["_supplier_object"]] = $supplier_product;
		if(!empty($categories)) 		$product[$_PRODUCT_OBJECT_["_category_objects"]] = $categories;
		if(!empty($attributes)) 		$product[$_PRODUCT_OBJECT_["_attribute_objects"]] = $attributes;
		if(!empty($product)) 			$line[$_TLOG_LINE_OBJECT_["_product_object"]] = $product;
		if(!empty($discount_reason)) 	$line[$_TLOG_LINE_OBJECT_["_discount_reason_object"]] = $discount_reason;
		if(!empty($return_reason)) 		$line[$_TLOG_LINE_OBJECT_["_return_reason_object"]] = $return_reason;
		if(!empty($void_reason)) 		$line[$_TLOG_LINE_OBJECT_["_void_reason_object"]] = $void_reason;
		if(!empty($promotion)) 			$line[$_TLOG_LINE_OBJECT_["_promotion_object"]] = $promotion;
		if(!empty($dispatch)) 			$line[$_TLOG_LINE_OBJECT_["_dispatch_object"]] = $dispatch;
		if(!empty($commission)) 		$line[$_TLOG_LINE_OBJECT_["_commission_object"]] = $commission;
		if(!empty($royalty)) 			$line[$_TLOG_LINE_OBJECT_["_royalty_object"]] = $royalty;
		if(!empty($profit_share)) 		$line[$_TLOG_LINE_OBJECT_["_profit_share_object"]] = $profit_share;
		if(!empty($transfer_source)) 	$transfer[$_TRANSFER_OBJECT_["_transfer_source_object"]] = $transfer_source;
		if(!empty($transfer_target)) 	$transfer[$_TRANSFER_OBJECT_["_transfer_target_object"]] = $transfer_target;
		
		return $line;
	} catch(Exception $e) {
		throw new JsonEncodedException(
			to_error(500, $e->getMessage())
		);
	}
}

function __convert_tender_to_tlog_format($data) {
	if(!$data)
		return null;
	
	try {
		global $_TLOG_TENDER_OBJECT_;
		global $_TENDER_DETAIL_OBJECT_;
		global $_SURCHARGE_OBJECT_;
		global $_FEE_OBJECT_;			
		global $_ISSUER_OBJECT_;		
		global $_CARD_OBJECT_;			
		global $_PAYMENT_OBJECT_;		
		global $_PROVIDER_OBJECT_;		
		global $_GATEWAY_OBJECT_;		
		global $_AUTH_OBJECT_;			
		global $_BANKING_OBJECT_;
	
		$line = array();
		// Line => *
		$tender = array();
		$surcharge = array();
		$fee = array();
		// Line => Tender => *
		$issuer = array();
		$card = array();
		$banking = array();
		$payment = array();
		// Line => Tender => Payment => *
		$provider = array();
		// Line => Tender => Payment => Provider => *
		$gateway = array();
		// Line => Tender => Payment => Provider => Gateway => *
		$auth = array();

		// Map line
		foreach($_TLOG_TENDER_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $line, $target_field);
		}
		// Map tender
		foreach($_TENDER_DETAIL_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $tender, $target_field);
		}
		// Map surcharge
		foreach($_SURCHARGE_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $surcharge, $target_field);
		}
		// Map fee
		foreach($_FEE_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $fee, $target_field);
		}
		// Map issuer
		foreach($_ISSUER_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $issuer, $target_field);
		}
		// Map card
		foreach($_CARD_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $card, $target_field);
		}
		// Map tender
		foreach($_PAYMENT_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $payment, $target_field);
		}
		// Map provider
		foreach($_PROVIDER_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $provider, $target_field);
		}
		// Map gateway
		foreach($_GATEWAY_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $gateway, $target_field);
		}
		// Map auth
		foreach($_AUTH_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $auth, $target_field);
		}
		// Map banking
		foreach($_BANKING_OBJECT_ as $source_field => $target_field) {
			__map_field($data, $source_field, $banking, $target_field);
		}
		
		if(!empty($auth)) 		$gateway[$_GATEWAY_OBJECT_["_auth_object"]] = $auth;
		if(!empty($gateway)) 	$provider[$_PROVIDER_OBJECT_["_gateway_object"]] = $gateway;
		if(!empty($provider)) 	$payment[$_PAYMENT_OBJECT_["_provider_object"]] = $provider;
		if(!empty($issuer)) 	$tender[$_TENDER_DETAIL_OBJECT_["_issuer_object"]] = $issuer;
		if(!empty($card)) 		$tender[$_TENDER_DETAIL_OBJECT_["_card_object"]] = $card;
		if(!empty($payment)) 	$tender[$_TENDER_DETAIL_OBJECT_["_payment_object"]] = $payment;
		if(!empty($banking)) 	$tender[$_TENDER_DETAIL_OBJECT_["_banking_object"]] = $banking;
		if(!empty($surcharge)) 	$line[$_TLOG_TENDER_OBJECT_["_surcharge_object"]] = $surcharge;
		if(!empty($fee)) 		$line[$_TLOG_TENDER_OBJECT_["_fee_object"]] = $fee;
		if(!empty($tender)) 	$line[$_TLOG_TENDER_OBJECT_["_tender_detail_object"]] = $tender;
		
		return $line;
	} catch(Exception $e) {
		throw new JsonEncodedException(
			to_error(500, $e->getMessage())
		);
	}
}

function __map_field($src, $from, &$dest, $to, $nullable = false) {
	try {
		
		if(!is_array($dest) || !$to)
			return;
		
		if( (!is_array($src) || !$from || !array_key_exists($from, $src) || $src[$from] == null)
			&& $nullable == false
		)
			return;
		else if( (!is_array($src) || !$from || !array_key_exists($from, $src) || $src[$from] == null)
				 && $nullable == true
			)
			$dest[$to] = null;
		else
			$dest[$to] = $src[$from];
		
		return;
	} catch(Exception $e) {
		throw new JsonEncodedException(
			to_error(500, $e->getMessage())
		);
	}
}