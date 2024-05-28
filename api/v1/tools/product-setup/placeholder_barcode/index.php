<?php

require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';

define("_NEXT_PLACEHOLDER_BARCODE_PROC_PROD_", "tools_product_setup_next_placeholder_barcode");
define("_NEXT_PLACEHOLDER_BARCODE_PROC_DEV_", "tools_product_setup_next_placeholder_barcode");
define("_QUERY_PLACEHOLDER_BARCODE_PROC_PROD_", "tools_product_setup_query_placeholder_barcode");
define("_QUERY_PLACEHOLDER_BARCODE_PROC_DEV_", "tools_product_setup_query_placeholder_barcode");
define("_ASSIGN_PLACEHOLDER_BARCODE_PROC_PROD_", "tools_product_setup_commit_placeholder_barcode");
define("_ASSIGN_PLACEHOLDER_BARCODE_PROC_DEV_", "tools_product_setup_commit_placeholder_barcode");
define("_DELETE_PLACEHOLDER_BARCODE_PROC_PROD_", "tools_product_setup_delete_placeholder_barcode");
define("_DELETE_PLACEHOLDER_BARCODE_PROC_DEV_", "tools_product_setup_delete_placeholder_barcode");

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


/**
 * Routes
 */
// GET route
$app->get(
    '/',
    function ($request, $response, $args) use ($app) {     
        // Set params
        $devmode = $request->getParam("devmode") === true || strcmp($request->getParam("devmode"), "true") == 0 ? true : false;
        
        // DB Settings
        $host 	= !$devmode ? _HOST_URI_PROD_ 		: _HOST_URI_DEV_;
        $dbname = !$devmode ? _HOST_DB_PROD_ 		: _HOST_DB_DEV_;
        $usr 	= !$devmode ? _HOST_USER_PROD_ 		: _HOST_USER_DEV_;
        $pwd 	= !$devmode ? _HOST_PASSWORD_PROD_ 	: _HOST_PASSWORD_DEV_;

		$proc	= null;
		$call	= "";
        
        $next_barcode = null;
		
		$opt_next 		= strcmp($request->getParam("next"), "true") == 0; 
		$prefix			= $request->getParam("prefix");
		$placeholder	= $request->getParam("placeholder");
		$replacement	= $request->getParam("replacement");
		
        try {
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$results = array();
			
			// Deterimne proc based on query type
			if($opt_next) {
				$proc = !$devmode ? _NEXT_PLACEHOLDER_BARCODE_PROC_PROD_ : _NEXT_PLACEHOLDER_BARCODE_PROC_DEV_;
				$call = "CALL $proc(:_prefix, @next_barcode);";
				// Execute the stored procedure
				$stmt = $conn->prepare($call);
				$stmt->bindParam(':_prefix', 	$prefix, 		PDO::PARAM_STR,50); 
				$stmt->execute();
				array_push($results,  $stmt->fetch(PDO::FETCH_ASSOC));
			} else {
				$proc = !$devmode ? _QUERY_PLACEHOLDER_BARCODE_PROC_PROD_ : _QUERY_PLACEHOLDER_BARCODE_PROC_DEV_;
				$call = "CALL $proc(:_placeholder, :_replacement);";
				// Execute the stored procedure
				$stmt = $conn->prepare($call);
				$stmt->bindParam(':_placeholder', 	$placeholder, 		PDO::PARAM_STR,50); 
				$stmt->bindParam(':_replacement', 	$replacement, 		PDO::PARAM_STR,50);
				$stmt->execute();
				
				// Parse record set
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					array_push($results, $row);
				}
			}

			$stmt->closeCursor();

			return 	$response->withStatus(200)
						->withHeader("Content-Type","application/json")
						->write(json_encode($results));
		} catch (PDOException $e) {
			return 	$response->withStatus(500)
						->withHeader("Content-Type","application/json")
						->write(json_encode(array(
							"error"	=>	$e->getMessage()
						)));
		}
        
    }
);

// POST route
$app->post(
    '/',
    function ($request, $response, $args) use ($app) {     
        // Set params
        // Convert JSON data to array
		$data = json_decode($request->getBody(), true);
        $barcode = $data && array_key_exists("barcode", $data) ? $data["barcode"] : null;
        $devmode = $request->getParam("devmode") === true || strcmp($request->getParam("devmode"), "true") == 0 ? true : false;
        
        // DB Settings
        $host 	= !$devmode ? _HOST_URI_PROD_ 		: _HOST_URI_DEV_;
        $dbname = !$devmode ? _HOST_DB_PROD_ 		: _HOST_DB_DEV_;
        $usr 	= !$devmode ? _HOST_USER_PROD_ 		: _HOST_USER_DEV_;
        $pwd 	= !$devmode ? _HOST_PASSWORD_PROD_ 	: _HOST_PASSWORD_DEV_;

        $proc 	= !$devmode ? _ASSIGN_PLACEHOLDER_BARCODE_PROC_PROD_ : _ASSIGN_PLACEHOLDER_BARCODE_PROC_DEV_;

        try {
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$results = array();

			foreach($array as $data) {				
				// Execute the stored procedure
				$stmt = $conn->prepare("CALL $proc(:prefix, @next_placeholder_barcode);");
				$stmt->bindParam(':prefix', $prefix, PDO::PARAM_STR,37); 
				$stmt->execute();
				$stmt->closeCursor();
				// Execute the stored procedure
				$stmt = $conn->prepare(
					"CALL $proc(".
						 ":_barcode".
						",:_barcode_type".
						",:_barcode_replaced_with".
						",:_barcode_replaced_by".
						",:_barcode_replaced_at".
						",:_assigned_title".
						",:_used".
						",:_used_system".
						",:_used_uri".
						",:_used_for".
						",:_assigned_by".
						",:_assigned_at".
					");"
				);
				$stmt->bindParam(':_barcode', 					$data[$_FIELD_MAPPING_["_barcode"]], 		PDO::PARAM_STR,50); 
				$stmt->bindParam(':_barcode_type', 				$data[$_FIELD_MAPPING_["_barcode_type"]], 	PDO::PARAM_STR,45); 
				$stmt->bindParam(':_barcode_replaced_with', 	null, 										PDO::PARAM_STR,50); 
				$stmt->bindParam(':_barcode_replaced_by', 		null, 										PDO::PARAM_STR,100); 
				$stmt->bindParam(':_barcode_replaced_at', 		null); 
				$stmt->bindParam(':_assigned_title', 			$data[$_FIELD_MAPPING_["_assigned_title"]], PDO::PARAM_STR,255); 
				$stmt->bindParam(':_used', 						$data[$_FIELD_MAPPING_["_used"]]); 
				$stmt->bindParam(':_used_system', 				$data[$_FIELD_MAPPING_["_used_system"]],	PDO::PARAM_STR,255); 
				$stmt->bindParam(':_used_uri', 					$data[$_FIELD_MAPPING_["_used_uri"]], 		PDO::PARAM_STR,100); 
				$stmt->bindParam(':_used_for', 					$data[$_FIELD_MAPPING_["_used_for"]], 		PDO::PARAM_STR,45); 
				$stmt->bindParam(':_assigned_by', 				$data[$_FIELD_MAPPING_["_assigned_by"]], 	PDO::PARAM_STR,100); 
				$stmt->bindParam(':_assigned_at', 				$data[$_FIELD_MAPPING_["_assigned_at"]]); 
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$stmt->closeCursor();
				
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
							"error"	=>	$e->getMessage()
						)));
		}
        
    }
);

// PUT route
$app->put(
    '/',
    function ($request, $response, $args) use ($app) {     
        // Set params
        // Convert JSON data to array
		$data = json_decode($request->getBody(), true);
        $barcode = $data && array_key_exists("barcode", $data) ? $data["barcode"] : null;
        $devmode = $request->getParam("devmode") === true || strcmp($request->getParam("devmode"), "true") == 0 ? true : false;
        
        // DB Settings
        $host 	= !$devmode ? _HOST_URI_PROD_ 		: _HOST_URI_DEV_;
        $dbname = !$devmode ? _HOST_DB_PROD_ 		: _HOST_DB_DEV_;
        $usr 	= !$devmode ? _HOST_USER_PROD_ 		: _HOST_USER_DEV_;
        $pwd 	= !$devmode ? _HOST_PASSWORD_PROD_ 	: _HOST_PASSWORD_DEV_;

        $proc 	= !$devmode ? _ASSIGN_PLACEHOLDER_BARCODE_PROC_PROD_ : _ASSIGN_PLACEHOLDER_BARCODE_PROC_DEV_;

        try {
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$results = array();

			foreach($array as $data) {				
				// Execute the stored procedure
				$stmt = $conn->prepare("CALL $proc(:prefix, @next_placeholder_barcode);");
				$stmt->bindParam(':prefix', $prefix, PDO::PARAM_STR,37); 
				$stmt->execute();
				$stmt->closeCursor();
				// Execute the stored procedure
				$stmt = $conn->prepare(
					"CALL $proc(".
						 ":_barcode".
						",:_barcode_type".
						",:_barcode_replaced_with".
						",:_barcode_replaced_by".
						",:_barcode_replaced_at".
						",:_assigned_title".
						",:_used".
						",:_used_system".
						",:_used_uri".
						",:_used_for".
						",:_assigned_by".
						",:_assigned_at".
					");"
				);
				$stmt->bindParam(':_barcode', 					$data[$_FIELD_MAPPING_["_barcode"]], 				PDO::PARAM_STR,50); 
				$stmt->bindParam(':_barcode_type', 				$data[$_FIELD_MAPPING_["_barcode_type"]], 			PDO::PARAM_STR,45); 
				$stmt->bindParam(':_barcode_replaced_with', 	$data[$_FIELD_MAPPING_["_barcode_replaced_with"]], 	PDO::PARAM_STR,50); 
				$stmt->bindParam(':_barcode_replaced_by', 		$data[$_FIELD_MAPPING_["_barcode_replaced_by"]], 	PDO::PARAM_STR,100); 
				$stmt->bindParam(':_barcode_replaced_at', 		$data[$_FIELD_MAPPING_["_barcode_replaced_at"]]); 
				$stmt->bindParam(':_assigned_title', 			$data[$_FIELD_MAPPING_["_assigned_title"]], 		PDO::PARAM_STR,255); 
				$stmt->bindParam(':_used', 						$data[$_FIELD_MAPPING_["_used"]]); 
				$stmt->bindParam(':_used_system', 				$data[$_FIELD_MAPPING_["_used_system"]],			PDO::PARAM_STR,255); 
				$stmt->bindParam(':_used_uri', 					$data[$_FIELD_MAPPING_["_used_uri"]], 				PDO::PARAM_STR,100); 
				$stmt->bindParam(':_used_for', 					$data[$_FIELD_MAPPING_["_used_for"]], 				PDO::PARAM_STR,45); 
				$stmt->bindParam(':_assigned_by', 				$data[$_FIELD_MAPPING_["_assigned_by"]], 			PDO::PARAM_STR,100); 
				$stmt->bindParam(':_assigned_at', 				$data[$_FIELD_MAPPING_["_assigned_at"]]); 
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$stmt->closeCursor();
				
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
							"error"	=>	$e->getMessage()
						)));
		}
        
    }
);

// PUT route
$app->delete(
    '/',
    function ($request, $response, $args) use ($app) {     
        // Set params
        // Convert JSON data to array
		$data = json_decode($request->getBody(), true);
        $barcode = $data && array_key_exists("barcode", $data) ? $data["barcode"] : null;
        $devmode = $request->getParam("devmode") === true || strcmp($request->getParam("devmode"), "true") == 0 ? true : false;
        
        // DB Settings
        $host 	= !$devmode ? _HOST_URI_PROD_ 		: _HOST_URI_DEV_;
        $dbname = !$devmode ? _HOST_DB_PROD_ 		: _HOST_DB_DEV_;
        $usr 	= !$devmode ? _HOST_USER_PROD_ 		: _HOST_USER_DEV_;
        $pwd 	= !$devmode ? _HOST_PASSWORD_PROD_ 	: _HOST_PASSWORD_DEV_;

        $proc 	= !$devmode ? _DELETE_PLACEHOLDER_BARCODE_PROC_PROD_ : _DELETE_PLACEHOLDER_BARCODE_PROC_DEV_;
        
        try {
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$results = array();

			foreach($array as $data) {				
				// Execute the stored procedure
				$stmt = $conn->prepare("CALL $proc(:prefix, @next_placeholder_barcode);");
				$stmt->bindParam(':prefix', $prefix, PDO::PARAM_STR,37); 
				$stmt->execute();
				$stmt->closeCursor();
				// Execute the stored procedure
				$stmt = $conn->prepare(
					"CALL $proc(".
						 ":_barcode".
						",:_barcode_replaced_with".
					");"
				);
				$stmt->bindParam(':_barcode', 					$data[$_FIELD_MAPPING_["_barcode"]], 				PDO::PARAM_STR,50); 
				$stmt->bindParam(':_barcode_replaced_with', 	$data[$_FIELD_MAPPING_["_barcode_replaced_with"]], 	PDO::PARAM_STR,50); 
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$stmt->closeCursor();
				
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
							"error"	=>	$e->getMessage()
						)));
		}
        
    }
);

/**
 * Run the Slim application
 */
$app->run();
