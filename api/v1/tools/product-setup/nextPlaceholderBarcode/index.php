<?php

require $_SERVER['DOCUMENT_ROOT'].'/include.php';
require 'dbconfig.php';

define("_NEXT_PLACEHOLDER_BARCODE_PROC_PROD_", "tools_product_setup_next_placeholder_barcode");
define("_NEXT_PLACEHOLDER_BARCODE_PROC_DEV_", "tools_product_setup_next_placeholder_barcode");

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
        $prefix =  $request->getParam("prefix");
        $devmode = $request->getParam("devmode") === true || strcmp($request->getParam("devmode"), "true") == 0 ? true : false;

        // DB Settings
        $host 	= !$devmode ? _HOST_URI_PROD_ 		: _HOST_URI_DEV_;
        $dbname = !$devmode ? _HOST_DB_PROD_ 		: _HOST_DB_DEV_;
        $usr 	= !$devmode ? _HOST_USER_PROD_ 		: _HOST_USER_DEV_;
        $pwd 	= !$devmode ? _HOST_PASSWORD_PROD_ 	: _HOST_PASSWORD_DEV_;

        $proc 	= !$devmode ? _NEXT_PLACEHOLDER_BARCODE_PROC_PROD_ : _NEXT_PLACEHOLDER_BARCODE_PROC_DEV_;
        
        $next_placeholder_barcode = null;

        try {
			$conn = new PDO("mysql:host=$host;dbname=$dbname", $usr, $pwd);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// Execute the stored procedure
			$stmt = $conn->prepare("CALL $proc(:prefix, @next_placeholder_barcode);");
			$stmt->bindParam(':prefix', $prefix, PDO::PARAM_STR,37); 
			$stmt->execute();
			$stmt->closeCursor();

			// Capture output parameter
			$next_placeholder_barcode = $conn->query("select @next_placeholder_barcode as barcode")->fetch(PDO::FETCH_ASSOC);
			
			return 	$response->withStatus(200)
						->withHeader("Content-Type","application/json")
						->write(json_encode($next_placeholder_barcode));
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

?>
