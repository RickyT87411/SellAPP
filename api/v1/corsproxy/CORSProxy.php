<?php

namespace CORSProxy;

class Proxy extends \Z\zobject {
	const NON_FORWARDING_REQUEST_HEADERS = [
		"content-length" => [],
		"accept-encoding" => [],
		"host" => []
	];
	
	const NON_FORWARDING_RESPONSE_HEADERS = [
		"location" => [],
		"content-length" => [],
		"transfer-encoding" => []
	];

	public function __construct($container, $settings = []) {
		// Construct parent
		$settings['logger'] = self::__array_get($settings, 'logger') ? self::__array_get($settings, 'logger') : $container->get('logger');
		parent::__construct($settings);
    }
    
    public function __invoke($request, $response, $args) {
		if( !$request || !$response ) {
			$this->__throw("Framework parameters not initialised; cannot process request. Contact support.", 500);
			return false;
		}
		
		// Determine the request HTTP method
		$method = strtoupper($request->getMethod());
		// Capture headers
		$headers = getallheaders();
		
		// Check for any Authorization headers as they may have been dropped
		$header_auth = array_key_exists("Authorization", $headers) ? self::__array_get($headers, "Authorization") : self::__array_get($_SERVER, "REDIRECT_HTTP_AUTHORIZATION");
		if($header_auth) {
			$headers["Authorization"] = $header_auth;
		}
		
		// Capture query parameters
    	$params = self::__array($request->getQueryParams(), []);
    	// Capture payload
		$payload = $request->getBody()->getContents();
		// Check for URL and append paramters
		$url = self::__array_get($params, "url", "");
		// Remove URL parameter so it isn't forwarded on
		unset($params["url"]);
		// Convert params into HTTP query string
		$params = http_build_query($params);
		// Append query paramaters to URL
		$url .= ($params ? "?" . $params : "");

		// URL is only mandatory paramter
		if(!filter_var($url, FILTER_VALIDATE_URL)) {
			return 	$response
						->withStatus(400)
						->withHeader("Content-Type","application/json; charset=utf-8")
						->write(json_encode(
							[
								"error_code" => 404,
								"error_message" => "Mandatory paramter [URL] missing from request"
							],
							JSON_NUMERIC_CHECK
						))
			;
		}
		
		// Check for HTTPS
		$url_parts = parse_url($url);
		$use_ssl = strtolower(self::__array_get($url_parts, "scheme", "")) === "https" ? true : false;
		
		// Init cURL
		$ch = curl_init($url);

		// Check for correct cURL method handling option
		switch($method) {
			case $method === "HEAD":
				curl_setopt($ch, CURLOPT_NOBODY, true);
				break;
			case $method === "GET":
				curl_setopt($ch, CURLOPT_HTTPGET, true);
				break;
			case $method === "POST":
				curl_setopt($ch, CURLOPT_POST, true);
				break;
			default:
			 	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}
		
		// Check for payload
		if(!empty($payload)) {
			switch($method) {
				case "POST":
				case "PUT":
				case "DELETE":
					curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload);
				break;		
			}
		}
		
		// Convert header array into cURL header array
		if($headers) {
			// Standardize header keys
			$headers = array_change_key_case($headers,CASE_LOWER);
		
			// Proxy header transformation
			$headers['x-forwarded-for'] = array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? self::__array_get($_SERVER, 'HTTP_X_FORWARDED_FOR') : self::__array_get($_SERVER, 'REMOTE_ADDR');
			$headers['x-forwarded-host'] = array_key_exists('HTTP_HOST', $_SERVER) ? self::__array_get($_SERVER, 'HTTP_HOST') : self::__array_get($_SERVER, 'REMOTE_ADDR');
		
			// Remove forward headers that should be defined by host not client
			$headers = array_diff_key($headers,array_change_key_case(self::NON_FORWARDING_REQUEST_HEADERS,CASE_LOWER));
			
			$ch_headers = array();
  
			foreach($headers as $k => $v) {
				$ch_headers[] = $k.": ".$v;
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $ch_headers);
		}
		
		$response_headers = [];

		// Remaining cURL options
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $use_ssl);
		curl_setopt($ch, CURLOPT_USERAGENT, self::__array_get($_SERVER, "HTTP_USER_AGENT"));
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		//curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$response_headers) {
			$len = strlen($header);
			$header = explode(':', $header, 2);
			if (count($header) < 2) // ignore invalid headers
				return $len;

			$response_headers[strtolower(trim($header[0]))][] = trim($header[1]);

			return $len;
		});
		
		// Exectue request and capture header & body
		$body = curl_exec($ch);
  		
		$status = curl_getinfo($ch);
		$errors = curl_error($ch);

		curl_close($ch);
		
		// Capture HTTP result code
		$http_code = self::__array_get($status, "http_code", 500);
		$http_code = $http_code > 0 ? $http_code : 500;

		// Remove non-forwarding headers
		$response_headers = array_diff_key(array_change_key_case($response_headers,CASE_LOWER), array_change_key_case(self::NON_FORWARDING_RESPONSE_HEADERS,CASE_LOWER));

		// Append response headers
		foreach($response_headers as $k=>$v) {
			if(!is_array($v)) {
				$v = [$v];
			}			
			foreach($v as $v2) {
				if(!$response->hasHeader($k)) {
					$response = $response->withHeader($k,$v2);
				} else {
					$response = $response->withAddedHeader($k,$v2);
				}
			}
		}
		
		$data=[];
		$data['headers'] = $response_headers;
		$data['contents'] = json_decode($body);
		$data['request-headers'] = $headers;
		$data['request'] = $payload;
		$data['status']['http_code'] = $http_code;
		$data['status']['error'] = $errors;
		
		// Return any system errors
		if(!empty($errors)) {
			return 	$response
						->withStatus($http_code)
						->withHeader("Content-Type","application/json; charset=utf-8")
						->write(json_encode(
							[
								"error_code" => $http_code,
								"error_message" => $errors[0]
							],
							JSON_NUMERIC_CHECK
						))
			;
		}
		
		// Return result
		return 	$response
					->withStatus($http_code)
					->withHeader("Content-Type","application/json; charset=utf-8")
					->write(json_encode($data,JSON_NUMERIC_CHECK))
		;

	}
	
	protected function __valid_json($payload) {
    	// Check payload isn't empty
		if(!$payload) {
			return false;
		}
		// Convert to JSON
   	 	$payload = json_decode($payload, true);
   	 	
   	 	// Valid JSON format
		if(json_last_error() != JSON_ERROR_NONE) {
			return false;
		}
		
		// Return converted JSON -> Array
		return true;
    }

}