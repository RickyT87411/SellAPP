<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

$app->add(function (RequestInterface $request, ResponseInterface $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/') {
        // permanently redirect paths with a trailing slash
        // to their non-trailing counterpart
        $uri = $uri->withPath(substr($path, 0, -1));

        if($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        }
        else {
            return $next($request->withUri($uri), $response);
        }
    }

    return $next($request, $response);
});

// Cache control
$app->add(function (RequestInterface $request, ResponseInterface $response, callable $next) {
	header_remove('Expires');
	header_remove('Last-Modified');
	header_remove('Cache-Control');
	header_remove('Pragma');
	
	$response = $response->withHeader('Expires', 'Tue, 01 Jan 2000 00:00:00 GMT');
	$response = $response->withHeader('Last-Modified', gmdate("D, d M Y H:i:s") . ' GMT');
	$response = $response->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
	$response = $response->withHeader('Pragma', 'no-cache');

	return $next($request, $response);
});


$app->add(function (RequestInterface $request, ResponseInterface $response, callable $next) {
    $response = $next($request, $response);

    return $response
        ->withHeader('Access-Control-Allow-Origin', $this->get('settings')['cors'])
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, HEAD');
});