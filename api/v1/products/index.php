<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
require 'validate-get.php';
require 'validate-post.php';
require 'validate-delete.php';
require 'mapping.php';
require 'tokens.php';

define("_GET_PROC_", "product_query_v7");
define("_POST_PROC_", "product_json_upsert_v4");
define("_DELETE_PROC_", "product_delete_v1");

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

if(!function_exists("__convert_product_xml_to_json")) {
	function __convert_product_xml_to_json($xmlstr = null) {
		try {
			if($xmlstr == null)
				return null;

			// Convert to XML
			$xml = simplexml_load_string( $xmlstr , null , LIBXML_NOCDATA );

			// Convert to JSON following special rules for
			// named array nodes in case singletons are provided
			return __xml_to_array(
				$xml,
				"prodoct",
				array(
					"barcodes",
					"tags",
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
    			 $_GET_FIELD_MAPPING_["_id"] => $request->getParam($_GET_FIELD_MAPPING_["_id"])
    			,$_GET_FIELD_MAPPING_["_hash"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_hash"])
    			,$_GET_FIELD_MAPPING_["_sku"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_sku"])
    			,$_GET_FIELD_MAPPING_["_barcode"] => $request->getParam($_GET_FIELD_MAPPING_["_barcode"])
    			,$_GET_FIELD_MAPPING_["_company"] => $request->getParam($_GET_FIELD_MAPPING_["_company"])
    			,$_GET_FIELD_MAPPING_["_country_code"] => $request->getParam($_GET_FIELD_MAPPING_["_country_code"])
    			,$_GET_FIELD_MAPPING_["_region"] => $request->getParam($_GET_FIELD_MAPPING_["_region"])
    			,$_GET_FIELD_MAPPING_["_search"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_search"])
    			,$_GET_FIELD_MAPPING_["_order_by"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_order_by"])
    			,$_GET_FIELD_MAPPING_["_asc"] 	=> $request->getParam($_GET_FIELD_MAPPING_["_asc"])
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
					 ":_id".
					",:_hash".
					",:_sku".
					",:_barcode".
					",:_company".
					",:_country_code".
					",:_region".
					",:_search".
					",:_order_by".
					",:_asc".
					",:__limit".
					",:__page".
					",@total_records".
				");"
			);
			$stmt->bindParam(':_id', 			$data[$_GET_FIELD_MAPPING_["_id"]], PDO::PARAM_STR,36);
			$stmt->bindParam(':_hash', 			$data[$_GET_FIELD_MAPPING_["_hash"]], PDO::PARAM_STR,64);
			$stmt->bindParam(':_sku', 			$data[$_GET_FIELD_MAPPING_["_sku"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_barcode', 		$data[$_GET_FIELD_MAPPING_["_barcode"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_company', 		$data[$_GET_FIELD_MAPPING_["_company"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_country_code', 	$data[$_GET_FIELD_MAPPING_["_country_code"]], PDO::PARAM_STR,2);
			$stmt->bindParam(':_region', 		$data[$_GET_FIELD_MAPPING_["_region"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_search', 		$data[$_GET_FIELD_MAPPING_["_search"]], PDO::PARAM_STR,1028);
			$stmt->bindParam(':_order_by', 		$data[$_GET_FIELD_MAPPING_["_order_by"]], PDO::PARAM_STR,256);
			$stmt->bindParam(':_asc', 			$data[$_GET_FIELD_MAPPING_["_asc"]]);
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

// POST route
$app->post(
    '/',
    function ($request, $response, $args) use ($app) {
    	try {
    		$contentType = $request->getContentType();
    		$body = $request->getBody();

			// Detect XML input method and convert XML --> JSON standard format
			// and continue to validate and process accordingly
    		if(strcmp(substr($contentType,0,strlen("application/xml")), "application/xml") == 0) {
    			$array = __convert_product_xml_to_json($body);
    			$body = json_encode($array, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
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

			$json = $body;

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_json".
					",:__verbose".
					",@error_code".
					",@error_message".
				");"
			);

			$stmt->bindParam(":_json", $json, PDO::PARAM_STR);
			$stmt->bindParam(":__verbose", $verbose, PDO::PARAM_BOOL);
			$stmt->execute();

			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$row["tags"] = $row["tags"] != null ? explode(",", $row["tags"]) : array();
				$row["barcodes"] = $row["barcodes"] != null ? explode(",", $row["barcodes"]) : array();
				$row["tag_ids"] = $row["tag_ids"] != null ? explode(",", $row["tag_ids"]) : array();
				$row["barcode_ids"] = $row["barcode_ids"] != null ? explode(",", $row["barcode_ids"]) : array();
				array_push($results, $row);
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
				if( count($results) == 0 ) {
					$results[] = array(
						"result" => "fail",
						"result_code" => $err_code || -1,
						"result_message" => $e->getMessage()
					);
				}
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
    	// Import field mapping
    	global $_DELETE_FIELD_MAPPING_;

    	try {
    		$body = array(
    			 $_DELETE_FIELD_MAPPING_["_id"] => $request->getParam($_DELETE_FIELD_MAPPING_["_id"])
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
			$host 	= _HOST_URI_;
			$dbname = _HOST_DB_;
			$usr 	= _HOST_USER_;
			$pwd 	= _HOST_PASSWORD_;

			$proc = _DELETE_PROC_;

			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$results = array();

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_id".
				");"
			);
			$stmt->bindParam(':_id', 			$data[$_DELETE_FIELD_MAPPING_["_id"]], PDO::PARAM_STR,36);
			$stmt->execute();

			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$row["tags"] = $row["tags"] != null ? explode(",", $row["tags"]) : array();
				$row["barcodes"] = $row["barcodes"] != null ? explode(",", $row["barcodes"]) : array();
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

/**
 * Run the Slim application
 */
$app->run();
