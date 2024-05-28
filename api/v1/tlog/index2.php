<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-delete.php';
require 'mapping.php';
require 'tokens.php';

define("_POST_PROC_", "tlog_json_insert");

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

if(!function_exists("__convert_tlog_xml_to_json")) {
	function __convert_tlog_xml_to_json($xmlstr = null) {
		try {
			if($xmlstr == null)
				return null;
		
			// Convert to XML	
			$xml = simplexml_load_string( $xmlstr , null , LIBXML_NOCDATA );
		
			// Convert to JSON following special rules for
			// named array nodes in case singletons are provided
			return __xml_to_array(
				$xml,
				"tlog",
				array(
					"tlog",
					"finance_objects",
					"lines",
					"tenders",
					"categories",
					"attributes",
					"promotions"
				)
			) ;
			
		} catch(Exception $e) {
			throw new JsonEncodedException(
				to_error(500, $e->getMessage())
			);
		}
	}
}
if(!function_exists("__xml_to_array")) {
	function __xml_to_array(SimpleXMLElement $parent, $parent_name, array $specified_array_nodes = array())
	{
		$array = array();
	
		// For each node from ROOT, traverse tree with Node=>Element pairs
		foreach ($parent as $name => $element) {
			// Deterine if the node is a singleton 
			// and that it is not a specifeid array node
			if( in_array($parent_name, $specified_array_nodes) ) {
				$node = & $array[];
			} else {
				$node = & $array[$name];
			}
			// Traverse the tree further if the Element has children
			// Otherwise capture the Element value
			$node = $element->count() ? __xml_to_array($element, $name, $specified_array_nodes) : trim($element);
		}

		return $array;
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
if(!function_exists("to_bigdecimal")) {
	function to_bigdecimal($input) {
		try {
			if(!is_float($input))
				return null;
			
			return strval($input);
			
		} catch(Exception $e) {
			return null;
		}
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
    			 $_GET_FIELD_MAPPING_["_transaction_date_from"] => $request->getParam($_GET_FIELD_MAPPING_["_transaction_date_from"])
				,$_GET_FIELD_MAPPING_["_transaction_date_to"] => $request->getParam($_GET_FIELD_MAPPING_["_transaction_date_to"])
				,$_GET_FIELD_MAPPING_["_tlog_header_type"] => $request->getParam($_GET_FIELD_MAPPING_["_tlog_header_type"])
				,$_GET_FIELD_MAPPING_["_location_code"] => $request->getParam($_GET_FIELD_MAPPING_["_location_code"])
				,$_GET_FIELD_MAPPING_["_since"] => $request->getParam($_GET_FIELD_MAPPING_["_since"])
				,$_GET_FIELD_MAPPING_["_status"] => $request->getParam($_GET_FIELD_MAPPING_["_status"])
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

			/** PAGINATION **/
        	$page = $request->getParam("page") ? $request->getParam("page") : 1;
			$page = !is_numeric($page) ? 1 : $page;
			$page = $page <= 0 ? 1 : $page;
			
			$limit = $request->getParam("limit") ? $request->getParam("limit") : _DEFAULT_PAGE_LIMIT_;
			$limit = !is_numeric($limit) ? _DEFAULT_PAGE_LIMIT_ : $limit;
			$limit = $limit <= 0 ? _DEFAULT_PAGE_LIMIT_ : $limit > _MAX_PAGE_LIMIT_ ? _MAX_PAGE_LIMIT_ : $limit;

			$results = array();
			
			// Execute the stored procedure
			$url =  _API_POST_TRANSACTION_QUERY_URL_ . "?page=" . $page . "&limit=" . $limit;

			// Get Product from Dear API
			$response = _POST($url, $data, null, $headers, false, false, $errs, $info);
			
			// Throw error if found
			if(!empty($errs)) {
				throw new JsonEncodedException($errs);
			}
			
			$results = json_decode($response);
						
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

// POST route
$app->post(
    '/',
    function ($request, $response, $args) use ($app) {
    	// Import field mapping
    	global $_TLOG_HEADER_OBJECT_;	
		global $_TLOG_LINE_OBJECT_;		
		global $_TLOG_TENDER_OBJECT_;	
		global $_CUSTOMER_OBJECT_;		
		global $_SUPPLIER_OBJECT_;			
		global $_TENDER_DETAIL_OBJECT_;			
		global $_BANKING_OBJECT_;			
    	
    	try {
    		$contentType = $request->getContentType();
    		$body = $request->getBody();
    		
			// Detect XML input method and convert XML --> JSON standard format
			// and continue to validate and process accordingly
			if(strcmp($contentType, "application/xml") == 0) {
				$array = __convert_tlog_xml_to_json($body);
				$body = json_encode($array);
			}
		
			// Validate input
			$errs = validate_post($body);
			$valid = empty($errs);
	
			// Return an array list of errors if invalid
			if(!$valid) {
				return 	$response->withStatus(400)
							->withHeader("Content-Type","application/json")
							->write(json_encode($errs));
			}

			// Set params
			$verbose = $request->getParam("verbose") === true || strcmp($request->getParam("verbose"), "true") == 0 ? true : false;
			// Set the base64 flag to reduce transmit size
    		$base64 = true;
			
			// DB Settings
			$host 	= _HOST_URI_;
			$dbname = _HOST_DB_;
			$usr 	= _HOST_USER_;
			$pwd 	= _HOST_PASSWORD_;
			
			$proc = _POST_PROC_;
			
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd, array(PDO::ATTR_PERSISTENT => true));
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$errs = array();
			$results = array();
			
			$json = base64_encode($body);

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_json".
					",:__verbose".
					",:__base64".
					",@error_code".
					",@error_message".
				");"
			);
			
			$stmt->bindParam(":_json", $json, PDO::PARAM_STR);
			$stmt->bindParam(":__verbose", $verbose, PDO::PARAM_BOOL);
			$stmt->bindParam(":__base64", $base64, PDO::PARAM_BOOL);
			$stmt->execute();

			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
				$results[] = $row;
			}

			$stmt->closeCursor();
			
			// Capture output parameter
			$result = $conn->query("select @error_code as error_code, @error_message as error_message;")->fetch(PDO::FETCH_ASSOC);

			$err_code = $result && array_key_exists("error_code", $result) ? $result["error_code"] : null;
			$err_msg = $result && array_key_exists("error_message", $result) ? $result["error_message"] : null;

			// Close connection
			$conn = null;

			try {
				// Throw error
				if($err_code && is_numeric($err_code) && $err_code > 0) {
					throw new Exception(
						$err_code.": ".$err_msg
					);
				}
			} catch(Exception $e) {
				// Append error result
				$results[] = array(
					"result" => "fail",
					"result_code" => $err_code || -1,
					"result_message" => $e->getMessage()
				);
			}
			
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
					)));
		} catch (Exception $e) {
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
    	try {
			return 	__method_not_allowed("PUT", $response);
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
					)));
		}
    }
);

/**
 * Run the Slim application
 */
$app->run();
