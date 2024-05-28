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
    	global $_API_SYNC_DEAR_PRODUCT_AVAILABILITY_MAPPING;
    	global $log;
    	try {
    		//$log->info("POST request received");
    		
    		$contentType = $request->getContentType();
    		$body = $request->getBody();
    		
    		/*
    		$log->info("Payload:");
			$log->info($body);
			*/
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
			
			/*
			$log->info("Headers:");
			$log->info(json_encode($request->getHeaders()));
			*/

			// Re-map to internal API format
			foreach( $data as $record ) {
				$r = array();
				$r[$_API_SYNC_DEAR_PRODUCT_AVAILABILITY_MAPPING["instance"]] = $instance;
				foreach( $record as $key=>$val) {
					$r[$_API_SYNC_DEAR_PRODUCT_AVAILABILITY_MAPPING[$key]] = $val;
				}
				$payload[] = $r;
			}
			
			/*
			$log->info("Re-mapped Payload:");
			$log->info(json_encode($payload));
			*/
			
			// Forward headers
			$headers = array(
				"Authorization: ".$auth
			);
			
			// Execute the stored procedure
			$url =  _API_SYNC_DEAR_PRODUCT_AVAILABILITY_URL . "?" . "verbose=".$verbose;

			/*
			$log->info("[PUT] request to PBG API @ '$url':");
			$log->info(json_encode($payload));
			*/
			
			// Get Product from Dear API
			$results = _PUT($url, json_encode($payload), null, $headers, false, false, $errs, $info);
			
			/*
			$log->info("Info:");
			$log->info(json_encode($info));
			$log->info("Payload:");
			$log->info($results);
			*/
			
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