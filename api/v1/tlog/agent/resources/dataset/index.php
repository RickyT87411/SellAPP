<?php
require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';
//require 'validate-get.php';
require 'validate-post.php';
//require 'validate-delete.php';
require 'mapping.php';

define("_POST_PROC_", "`tlog_agent_dataset_fetch_delta`");

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
    	try {
			return 	__method_not_allowed("GET", $response);
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
    	global $_TLOG_AGENT_DATASET_FETCH_DELTA_OBJECT_;			
    	
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
			$data = json_decode($request->getBody(), true);
			
			$map = $_TLOG_AGENT_DATASET_FETCH_DELTA_OBJECT_;

			// Set params
			$devmode = $request->getParam("devmode") === true || strcmp($request->getParam("devmode"), "true") == 0 ? true : false;
			$fetch_type = $request->getParam("fetch_type");
			
			// DB Settings
			$host 	=  _HOST_URI_;
			$dbname =  _HOST_DB_;
			$usr 	=  _HOST_USER_;
			$pwd 	=  _HOST_PASSWORD_;
			
			$proc 	= _POST_PROC_;
			
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd, array(PDO::ATTR_PERSISTENT => true));
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$page = $request->getParam("page") ? $request->getParam("page") : 1;
			$page = !is_numeric($page) ? 1 : $page;
			$page = $page <= 0 ? 1 : $page;
			
			$limit = $request->getParam("limit") ? $request->getParam("limit") : _DEFAULT_PAGE_LIMIT_;
			$limit = !is_numeric($limit) ? _DEFAULT_PAGE_LIMIT_ : $limit;
			$limit = $limit <= 0 ? _DEFAULT_PAGE_LIMIT_ : $limit > _MAX_PAGE_LIMIT_ ? _MAX_PAGE_LIMIT_ : $limit;
			
			$results = array(
				"pagination" => array(
					 "dataset" => $data[$map["_tlog_dataset_type"]]
					,"since" => "0000-00-00 00:00:00"
					,"limit" => $limit
					,"page" => $page
					,"page_records" => 0
					,"total_records" => 0
				),
				"records" => array()
			);

			// Execute the stored procedure
			$stmt = $conn->prepare(
				"CALL $proc(".
					 ":_session_id".
					",:_tlog_dataset_type".
					",:__limit".
					",:__page".
					",@dataset_fetch_since".
					",@total_records".
				");"
			);
			
			$stmt->bindParam(":_session_id", $data[$map["_session_id"]], PDO::PARAM_STR);
			$stmt->bindParam(":_tlog_dataset_type", $data[$map["_tlog_dataset_type"]], PDO::PARAM_STR);
			$stmt->bindParam(":__limit", $limit, PDO::PARAM_INT);
			$stmt->bindParam(":__page", $page, PDO::PARAM_INT);
			$stmt->execute();

			// Parse record set
			// Parse record set
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
				$results["records"][] = $row;
			}

			$stmt->closeCursor();
			
			// Capture output parameter
			$q = $conn->query("select @dataset_fetch_since as dataset_fetch_since, @total_records as total_records;")->fetch(PDO::FETCH_ASSOC);
			$total_records = $q && array_key_exists("total_records", $q) ? $q["total_records"] : 1;
			$dataset_fetch_since = $q && array_key_exists("dataset_fetch_since", $q) ? $q["dataset_fetch_since"] : "0000-00-00 00:00:00";
			
			$results["pagination"]["page_records"] = count($results["records"]);
			$results["pagination"]["total_records"] = $total_records * 1;
			$results["pagination"]["total_pages"] = ceil($total_records / $limit);
			$results["pagination"]["since"] = $dataset_fetch_since;

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
