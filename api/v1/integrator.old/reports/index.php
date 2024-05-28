<?php

require $_SERVER['DOCUMENT_ROOT'].'/include.php';

/**
 * Step 1: Require the Slim Framework
 */
require 'Slim/Slim.php';
require 'Slim/Middleware.php';
require 'Slim/Extras/Middleware/HttpBasicAuth.php';
require 'Slim/Logger/DateTimeFileWriter.php';

\Slim\Slim::registerAutoloader();

/**
 * Step 2: Instantiate a Slim application
 *  OPTIONAL: Logging enabled
 */
$app = new \Slim\Slim(array(
    'log.writer' => new \Slim\Logger\DateTimeFileWriter(array(
        'path' => './logs',
        'name_format' => 'Y-m-d',
        'message_format' => '%label% - %date% - %message%'
    )),
    'debug' => true
));


/**
 * Step 3: Configure HTTP Basic Authentication for access
 */
$app->add(new \Slim\Extras\Middleware\HttpBasicAuth('dev-integrator', 'Zj|~YxiCqh!2'));

/**
 * Step 4: Define the Slim application routes
 */

// GET route
$app->get(
    '/',
    function () {
        $contents = file_get_contents('help.html');
        echo $contents;
    }
);

// POST route
$app->post(
    '/post',
    function () {
        
    }
);

// PUT route
$app->put(
    '/put',
    function () {
        
    }
);

/**
 * Step 5: Run the Slim application
 */
$app->run();
