<?php

namespace Z;

class zobject {
	protected $vars;
	protected $settings;
	protected $errs;
	protected $logger;
	
	protected $__key__;
	
	public function __construct($settings = []) {
		$this->vars = self::__array($this->vars, []);
		$this->settings = self::__array($this->settings, []);
		$this->errs = self::__array($this->errs, []);
		$this->logger = self::__array_get($settings, "logger");
		$this->__key__ = bin2hex(openssl_random_pseudo_bytes(256));
		$this->die_on_failure = self::__array_get($settings, "die_on_failure");
	}
	
	// Magic Getter
	public function __get($k) { 
		return self::__array_get($this->vars, $k);
	}

	// Magic Setter
	public function __set($k, $v) {
		switch(strtolower($k)) {
			case "die_on_failure":
				$this->vars[$k] = $v && is_bool($v) ? $v : true;
				break;
			default:
				$this->vars[$k] = $v;
		}
		return $this->vars[$k];
	}
	
	// Magic stringifier
	// Converts publicly accessible variables array in JSON format
	public function __tostring() {
		return json_encode($this->to_array());
	}
	
	public function __throw($message, $code) {
		if($this->die_on_failure) {
			throw new \Exception($message, $code);
		}
		
		array_push($this->errs, $message);
	}
	
	// Returns the client IP address
	public function client_ipaddr() {
		$arr = [
			'HTTP_CLIENT_IP', 
			'HTTP_X_FORWARDED_FOR', 
			'HTTP_X_FORWARDED', 
			'HTTP_X_CLUSTER_CLIENT_IP', 
			'HTTP_FORWARDED_FOR', 
			'HTTP_FORWARDED', 
			'REMOTE_ADDR'
		];
	
		foreach ($arr as $key) {
			if (array_key_exists($key, $_SERVER) === true) {
				foreach (explode(',', $_SERVER[$key]) as $ip) {
					if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
						return $ip;
					}
				}
			}
		}
	}
	
	// Convert public variables to an array
	public function to_array() {
		$a = array();
		foreach($this->vars as $k => $v) {
			$a[$k] = $v;
		}
		return $a;
	}
	
	public function __encrypt($v) {
		$plaintext = $v;
		$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
		$iv = openssl_random_pseudo_bytes($ivlen);
		$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $this->__key__, $options=OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $ciphertext_raw, $this->__key__, $as_binary=true);
		return base64_encode( $iv.$hmac.$ciphertext_raw );
	}
	
	public function __decrypt($v) {
		$c = base64_decode($v);
		$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len=32);
		$ciphertext_raw = substr($c, $ivlen+$sha2len);
		$original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $this->__key__, $options=OPENSSL_RAW_DATA, $iv);
		$calcmac = hash_hmac('sha256', $ciphertext_raw, $this->__key__, $as_binary=true);

		//PHP 5.6+ timing attack safe comparison
		try { 
			if (hash_equals($hmac, $calcmac)) {
				return $original_plaintext;
			} else {
				return null;
			}
		} catch(Exception $e) {
			return null;
		}
	}
	
	public static function __array_get($a, $k, $t = null, $df = null) {
		$v = $a != null && is_array($a) && array_key_exists($k, $a) ? $a[$k] : null;

		// Type cast if given a type		
		switch(strtolower(self::__string($t, ""))) {
			case "string":
			case "text":
				return self::__string($v, $df);
				break;
			case "numeric":
			case "number":
				return self::__numeric($v, $df);
				break;
			case "tiny":
			case "small":
			case "int":
			case "integer":
			case "long":
				return self::__int($v, $df);
				break;
			case "float":
			case "double":
			case "real":
				return self::__float($v, $df);
				break;
			case "decimal":
				return self::__decimal($v, $df);
				break;
			case "array":
				return self::__array($v, $df);
				break;
			default:
				return $v !== null ? $v : $df;
		}
	
		return $v !== null ? $v : $df;
	}
	
	public static function __replace($s, $placeholders = [], $encaps = "%s") {
		if( !empty($s) && $placeholders && is_array($placeholders) && count($placeholders) > 0 ) {
			foreach($placeholders as $k => $v) {
				// Check encapsulation contains a valid string placeholder
				$encaps = strpos($encaps, "%s") === false ? "%s" : $encaps;
				// Encapsulate any keys with the given encapsulation format
				$k = sprintf($encaps, $k);
				// Replace the occurances of the key in the string
				$s = str_replace($k, $v, $s);
			}
		}
		
		return $s;
	}
	
	// Type converting methods
	
	public static function __string($v, $df = null) {
		try{ return $v !== null && is_string($v) ? (string)$v : $df; } catch(\Exception $e) { return $df; }
	}
	
	public static function __numeric($v, $df = null) {
		try{ return $v !== null && is_numeric($v) ? $v : $df; } catch(\Exception $e) { return $df; }
	}
	
	public static function __number($v, $df = null) {
		return self::_numeric($v, $df);
	}
	
	public static function __int($v, $df = null) {
		try{ return $v !== null && is_int($v) ? (int)$v : $df; } catch(\Exception $e) { return $df; }
	}
	
	public static function __long($v, $df = null) {
		return self::__int($v, $df);
	}
	
	public static function __small($v, $df = null) {
		return self::__int($v, $df);
	}
	
	public static function __tiny($v, $df = null) {
		return self::__int($v, $df);
	}
	
	public static function __unsigned($v, $df = null) {
		return self::__int($v, $df) >= 0 ? self::__int($v, $df) : $df;
	}
	
	public static function __decimal($v, $df = null) {
		try{ return $v !== null && preg_match('/^[0-9]+(\.[0-9]+)?$/', $v) ? (float)$v : $df; } catch(\Exception $e) { return $df; }
	}
	
	public static function __float($v, $df = null) {
		try{ return $v !== null && is_float($v) ? (float)$v : $df; } catch(\Exception $e) { return $df; }
	}
	
	public static function __double($v, $df = null) {
		return self::__float($v, $df);
	}
	
	public static function __real($v, $df = null) {
		return self::__float($v, $df);
	}
	
	public static function __boolean($v, $df = null) {
		try{ 
			if($v !== null && is_bool($v)) {
				return $v;
			} else if ($v !== null && is_int($v)) { 
				return $v > 0;
			} else if ($v !== null && is_string($v) && !empty($v)) {
				switch(strtolower($v)) {
					case '1':
					case 'true':
					case 'on':
					case 'yes':
					case 'y':
						return true;
					default:
						return false;
				}
			} else {
				return $df;
			}
			
			return $v !== null && is_bool($v) ? (bool)$v : $df; 
		
		} catch(\Exception $e) { return $df; }
	}
	
	public static function __bool($v, $df = null) {
		return self::__boolean($v, $df);
	}
	
	public static function __array($v, $df = null) {
		try{ return $v !== null && is_array($v) ? (array)$v : $df; } catch(\Exception $e) { return $df; }
	}
	
	public static function __http_method($v, $df = "GET") {
		try{ 
			return 	$v !== null && is_string($v) && 
					array_key_exists(strtoupper($v), ["GET","HEAD","POST","PUT","DELETE","CONNECT","OPTIONS","TRACE","PATCH"]) ?
					strtoupper($v) : $df
			; 
		} catch(\Exception $e) { return $df; }
	}
		
	// Checks if a variable is in a given list
	public static function __in($needle, Array $haystack = [], $case_sensitive = false, $df = false) {
		$needle_c = is_string($needle) && !$case_sensitive ? strtolower($needle) : $needle;
		
		// If case insensitive then iteratively check each element as lowercase comparisons
		if( !$case_sensitive ) {
			foreach($haystack as $v) {
				$v = is_string($v) ? strtolower($v) : $v;
				if( $needle_c === $v ) {
					return $needle;
				} else {
					continue;
				}
			}
			// Return a no-match if still inside the method
			return $df;
		}
		
		// Return a simple array_key_exists results
		return array_key_exists($needle, $haystack) ? $needle : $df;
	}
		
	
	public function __info($s) {
		if( $this->logger && $this->logger instanceof \Monolog\Logger ) {
			$this->logger->info($s);
		}
	}
	
	public function __debug($s) {
		if( $this->logger && $this->logger instanceof \Monolog\Logger ) {
			$this->logger->debug($s);
		}
	}
	
	public function __warning($s) {
		if( $this->logger && $this->logger instanceof \Monolog\Logger ) {
			$this->logger->warning($s);
		}
	}
	
	public function __error($s) {
		if( $this->logger && $this->logger instanceof \Monolog\Logger ) {
			$this->logger->error($s);
		}
	}
	
	public function __http_get($url, $headers = null, $ssl_verification = false) {
		if( !$url )
			return false;
	
		// GET request to Vend Product API
		$curl = curl_init();
	
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if( $headers)   curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, is_bool($ssl_verification) ? $ssl_verification : false);

		// Extract response headers
		$response_headers = [];
		// this function is called by curl for each header received
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$response_headers) {
			$len = strlen($header);
			$header = explode(':', $header, 2);
			if (count($header) < 2) // ignore invalid headers
				return $len;

			$response_headers[strtolower(trim($header[0]))][] = trim($header[1]);

			return $len;
		});
	
		// Execute RESTful API call
		
		$body = curl_exec($curl);
	
		// Get response info
		$info = curl_getinfo($curl);

		// Extract HTTP code
		$hc = self::__integer(self::__array_get($info, 'http_code'),500);
		
		curl_close($curl);
		
		// Return complex result
		return [
			 'http_code' => $hc
			,'info' => $info
			,'headers' => $response_headers
			,'body' => $body
		];
	}

	public static function __http_post($url, $payload = null, $fields = array(), $headers = null, $ssl_verification = false, &$apiinfo) {
		if( !$url || (!$payload && (!$fields || count($fields) <= 0)) )
			return false;

		// Checks if the @FIELDS parameter is set which will then switch the POST to 'form-data' mode
		$fields_count = $fields && is_array($fields) && count($fields) > 0 ? count($fields) : 0;
		// Convert the @FIELDS associative array to 'form-data' parameters
		// N.B. This will replace any input @PAYLOAD
		if( $fields_count > 0 ) {
			//url-ify the data for the POST
			foreach($fields as $key=>$value) { 
				$payload .= $key.'='.$value.'&'; 
			}
			rtrim($payload, '&');
		}
	
		// Correct the URL to remove any trailing '/' characters as this may lead to a GET request
		$url = strripos($url, "/") == strlen($url) - 1 ? substr($url, 0, strlen($url)-1) : $url;

		// GET request to Vend Product API
		$curl = curl_init();
	
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, is_bool($ssl_verification) ? $ssl_verification : false);
		if( $fields_count <= 0 )    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		if( $fields_count >  0 )    curl_setopt($curl, CURLOPT_POST, $fields_count);
		if( $headers )              curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		if( $payload )              curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
		
		// Extract response headers
		$response_headers = [];
		// this function is called by curl for each header received
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$response_headers) {
			$len = strlen($header);
			$header = explode(':', $header, 2);
			if (count($header) < 2) // ignore invalid headers
				return $len;

			$response_headers[strtolower(trim($header[0]))][] = trim($header[1]);

			return $len;
		});
	
		// Execute RESTful API call
		
		$body = curl_exec($curl);
	
		// Get response info
		$info = curl_getinfo($curl);

		// Extract HTTP code
		$hc = self::__integer(self::__array_get($info, 'http_code'),500);
		
		curl_close($curl);
		
		// Return complex result
		return [
			 'http_code' => $hc
			,'info' => $info
			,'headers' => $response_headers
			,'body' => $body
		];
	}

	public static function __http_put($url, $payload = null, $fields = array(), $headers = null, $ssl_verification = false, &$apiinfo) {
		if( !$url || (!$payload && (!$fields || count($fields) <= 0)) )
			return false;

		// Checks if the @FIELDS parameter is set which will then switch the POST to 'form-data' mode
		$fields_count = $fields && is_array($fields) && count($fields) > 0 ? count($fields) : 0;
		// Convert the @FIELDS associative array to 'form-data' parameters
		// N.B. This will replace any input @PAYLOAD
		if( $fields_count > 0 ) {
			//url-ify the data for the POST
			foreach($fields as $key=>$value) { 
				$payload .= $key.'='.$value.'&'; 
			}
			rtrim($payload, '&');
		}
	
		// Correct the URL to remove any trailing '/' characters as this may lead to a GET request
		$url = strripos($url, "/") == strlen($url) - 1 ? substr($url, 0, strlen($url)-1) : $url;

		// GET request to Vend Product API
		$curl = curl_init();
	
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, is_bool($ssl_verification) ? $ssl_verification : false);
		if( $fields_count <= 0 )    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		if( $fields_count >  0 )    curl_setopt($curl, CURLOPT_POST, $fields_count);
		if( $headers )              curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		if( $payload )              curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
	
		// Extract response headers
		$response_headers = [];
		// this function is called by curl for each header received
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$response_headers) {
			$len = strlen($header);
			$header = explode(':', $header, 2);
			if (count($header) < 2) // ignore invalid headers
				return $len;

			$response_headers[strtolower(trim($header[0]))][] = trim($header[1]);

			return $len;
		});
	
		// Execute RESTful API call
		
		$body = curl_exec($curl);
	
		// Get response info
		$info = curl_getinfo($curl);

		// Extract HTTP code
		$hc = self::__integer(self::__array_get($info, 'http_code'),500);
		
		curl_close($curl);
		
		// Return complex result
		return [
			 'http_code' => $hc
			,'info' => $info
			,'headers' => $response_headers
			,'body' => $body
		];
	}
	
	public static function __http_request($o) {
		if( !$o || !self::__array($o) ) {
			$this->throw("HTTP Request options empty or malformed", 404);
			return false;
		}
		
		// Capture URL
		$url = self::__array_get($o, "url");
		// Determine the request HTTP method; Default = GET
		$method = self::__http_method(self::__array_get($o, "method"));
		// Capture headers
		$headers = self::__array_get($o, "headers", []);
		// Standardize header keys
		$headers = array_change_key_case($headers,CASE_LOWER);
		// Capture query parameters
    	$params = self::__array_get($o, "parameters", []);
    	// Capture payload
		$payload = self::__array_get($o, "payload");
		$form_fields = 1;
		// Check if payload is x-www-form-urlencoded and convert to URL encoded key=>value pairs
		if(strpos(self::__array_get($headers, "content-type",""), "application/x-www-form-urlencoded") !== false) {
			$payload = self::__array($payload,[]);
			$payload = http_build_query($payload);
			$form_fields = count($payload);
		}
		
		// Check for any arbitrary timeouts
		$timeout = self::__long(self::__array_get($o, "timeout"));
		// Convert params into HTTP query string
		$params = http_build_query($params);
		// Append query paramaters to URL
		$url .= ($params ? "?" . $params : "");

		// URL is only mandatory paramter
		if(!filter_var($url, FILTER_VALIDATE_URL)) {
			$this->throw("HTTP Request URL malformed or missing", 404);
			return false;
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
				curl_setopt($ch, CURLOPT_POST, $form_fields);
				break;
			case $method === "PUT" && $form_fields > 1:
				curl_setopt($ch, CURLOPT_POST, $form_fields);
				break;
			case $method === "DELETE" && $form_fields > 1:
				curl_setopt($ch, CURLOPT_POST, $form_fields);
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
					curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
				break;		
			}
		}
		
		// Convert header array into cURL header array
		if($headers) {
			// Convert to cURL formatted headers
			$ch_headers = [];
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
		if($timeout && $timeout > 0 ) {
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		}
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
		
		// Return any system errors
		if(!empty($errors)) {
			$this->throw($errors[0], $http_code);
			return false;
		}
		
		// Return result
		return 	[
			 "http_code" => $http_code
			,"headers" => $response_headers
			,"body" => $body
			,"status" => $status
			,"errors" => $errors
		
		];

	}
	
	protected function __class_name() {
		return (new \ReflectionClass($this))->getName();
	}
	
	protected function __class_short_name() {
		return (new \ReflectionClass($this))->getShortName();
	}
}