<?php

require_once(__DIR__ . "/ZObject2.php");

$url = isset($_GET['url']) ? $_GET['url'] : null;
$method =  $_SERVER['REQUEST_METHOD'];
$parameters = $_GET;
$headers = \Z\__Object::__array_get($parameters, "headers");
$headers = \Z\__Object::__is_json_array($headers) ?  \Z\__Object::__json_array($headers) : [];
$body = [];

// Setup for accepted request methods
switch(true) {
	case strcasecmp($method,"POST") == 0:
	case strcasecmp($method,"PUT") == 0:
		$body = file_get_contents( 'php://input' );
		break;
	default:
		$body = $_POST;
}

$body = \Z\__Object::__is_json_array($body) ?  \Z\__Object::__json_array($body) : $body;

if(!is_array($body)) {
	parse_str($body, $nbody);
	if(is_array($nbody)) {
		$body = $nbody;
	}
}

$jsonp = \Z\__Object::__array_get($parameters, "callback", "string");

$response = \Z\__Object::__http_request([
	"url" => $url,
	"method" => $method,
	"headers" => $headers,
	"parameters" => [],
	"payload" => $body
]);

$contents = \Z\__Object::__array_get($response, "body");
$contents = \Z\__Object::__is_json_array($contents) ?  \Z\__Object::__json_array($contents) : $contents;

$response = [
	"request" => $body,
	"request-headers" => $headers,
	"contents" => $contents,
	"headers" => \Z\__Object::__array_get($response, "headers", "array", []),
	"status" => array_replace(
		\Z\__Object::__array_get($response, "status", "array", []),
		["http_code" => \Z\__Object::__array_get($response, "http_code", "int", 500)],
		["error" => \Z\__Object::__array_get($response, "errors")]
	)
];

foreach($response["headers"] as $k => $array) {
	foreach($array as $v) {
		if(strtolower($k) === "content-length") {
			$v = strlen(json_encode($response));
		}
		$header = $k.":".$v;
		header($header);
	}
}

$response = json_encode($response);

print $jsonp ? "$jsonp($response)" : $response;
