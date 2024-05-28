<?php

namespace Z;


/** BC Arithmetic add-ons **/

if(!function_exists('bcnegative')) {
	function bcnegative($n) {
		return strpos($n, '-') === 0; // Is the number less than 0?
	}
}
if(!function_exists('bcceil')) {
	function bcceil($n){
		return bcnegative($n) ? (($v = bcfloor(substr($n, 1))) ? "-$v" : $v)
							  : bcadd(strtok($n, '.'), strtok('.') != 0);
	}
}
if(!function_exists('bcfloor')) {
	function bcfloor($n) {
		return bcnegative($n) ? '-' . bcceil(substr($n, 1)) : strtok($n, '.');
	}
}
if(!function_exists('bcround')) {
	function bcround($n, $p = 0) {
		$e = bcpow(10, $p + 1);
		return bcdiv(bcadd(bcmul($n, $e, 0), bcnegative($n) ? -5 : 5), $e, $p);
	}
}
if(!function_exists('bcabs')) {
	function bcabs($n) {
		return bccomp($n, "0") < 0 ? bcmul($n, "-1") : $n;
	}
}

class __Object {
	protected $vars;
	protected $errs;
	protected $logger;
	protected $logger_name;
	private $dof = false;

	protected $__key__;

	public function __construct(&$settings = []) {
		$this->vars = [];
		$this->errs = [];

		$this->__key__ = null;

		// Merge/replace all settings with instantiated and imported settings files for full lineage
		// Priority order:
		//		... -> Grand Parent -> Parent -> Child -> Instantiated
		$this->vars = self::__array($this->__import_settings(), []);
		$settings = self::__array($settings, []);
		$settings = self::__array(array_replace($this->vars, $settings), []);

		$this->set_logger(self::__array_get($settings, "logger"));
		$this->die_on_failure(self::__array_get($settings, "die_on_failure", "bool", false));

		// Attempt to check any dependencies
		$dependencies = self::__array_get($settings, "dependencies");
		$this->check_dependencies($dependencies);
	}

	// Magic Getter
	public function __get($k) {
		switch(strtolower(self::__string($k,""))) {
			case "":
				return null;
			default:
				return self::__array_get($this->vars, $k);
		}
	}

	// Magic Setter
	public function __set($k, $v) {
		switch(strtolower(self::__string($k,""))) {
			case "":
				return null;
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
		$this->__critical($message, $code);

		if($this->die_on_failure()) {
			throw new \Exception($message, $code);
		}

		array_push($this->errs, $message);
	}

	public function die_on_failure($v = null) {
		if($v !== null) {
			$this->dof = self::__bool($v, self::__array_get($this->vars, "die_on_failure", "bool", false));
			$this->vars["die_on_failure"] = $this->dof;
		}
		return $this->dof;
	}

	public function check_dependencies($dependencies) {
		$dependencies = self::__array($dependencies,[]);
		foreach($dependencies as $dependency) {
			if(!class_exists($dependency)) {
				throw new \Exception("Missing dependency: {$dependency}", 500);
			}
		}
		return true;
	}

	public function set_logger($logger) {
		if($logger !== null && $logger instanceof \Z\Logger\LoggerInterface) {
			$this->logger = $logger;
			$this->logger_name = $logger->name();
		} else {
			return false;
		}
		return $this;
	}

	public function logger() {
		return $this->logger;
	}

	// Returns the client IP address
	public static function client_ipaddr() {
		$client_addr = null;

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
						$client_addr = $ip;
						break;
					}
				}
			}
		}

		return $client_addr !== null ? $client_addr : gethostbyname(gethostname());
	}

	// Returns the server IP address
	public static function server_ipaddr() {
		$server_ip = null;

		$arr = [
			'SERVER_ADDR',
		];

		foreach ($arr as $key) {
			if (array_key_exists($key, $_SERVER) === true) {
				foreach (explode(',', $_SERVER[$key]) as $ip) {
					if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
						$server_ip = $ip;
						break;
					}
				}
			}
		}

		return $server_ip !== null ? $server_ip : self::client_ipaddr();
	}

	public static function client_is_localhost() {
		return in_array(self::client_ipaddr(), ["127.0.0.1","::1"]);
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
		// Create a random encryption key
		if($this->__key__ === null) {
			$this->__key__ = bin2hex(openssl_random_pseudo_bytes(256));
		}

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
			if (hash_equals(self::__string($hmac,""), $calcmac)) {
				return $original_plaintext;
			} else {
				return null;
			}
		} catch(Exception $e) {
			return null;
		}
	}

	public static function __array_get($a, $k, $t = null, $df = null) {
		try {
			if(!is_string($k) && !is_int($k)) {
				return $df;
			}

			$v = $a != null && is_array($a) && array_key_exists($k, $a) ? $a[$k] : null;

			// Type cast if given a type
			switch(strtolower(self::__string($t, ""))) {
				case "string":
				case "text":
					return self::__string($v, $df);
				case "nestring":
					return self::__empty_string($v, $df);
				case "numeric":
				case "number":
					return self::__numeric($v, $df);
				case "bool":
				case "boolean":
					return self::__bool($v, $df);
				case "tiny":
				case "small":
				case "int":
				case "integer":
				case "long":
					return self::__int($v, $df);
				case "float":
				case "double":
				case "real":
					return self::__float($v, $df);
				case "unsigned":
					return self::__unsigned($v, $df);
				case "unsigned float":
				case "unsigned double":
				case "unsigned real":
					return self::__unsigned_float($v, $df);
				case "decimal":
					return self::__decimal($v, $df);
				case "bcdecimal":
					return self::__bcdecimal($v, null, $df);
				case "array":
					return self::__array($v, $df);
				case "object":
					return self::__object($v, $df);
				case "json":
					return self::__json($v, $df);
				case "json_array":
					return self::__json_array($v, $df);
				case "xml":
					return self::__xml($v, $df);
				default:
					return $v !== null ? $v : $df;
			}

			return $v !== null ? $v : $df;
		} catch(Exception $e) {
			return $df;
		}
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
		return self::__numeric($v, $df);
	}

	public static function __int($v, $df = null) {
		try{ return $v !== null && is_numeric($v) ? (int)$v : $df; } catch(\Exception $e) { return $df; }
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
		try{ return $v !== null && preg_match('/^\-?(\d+\.?\d*|\d*\.?\d+)$/', $v) ? (float)$v : $df; } catch(\Exception $e) { return $df; }
	}

	public static function __bcdecimal($v, $precision = null, $df = null) {
		try{ return $v !== null && preg_match('/^\-?(\d+\.?\d*|\d*\.?\d+)$/', $v) ? bcround((string)$v,self::__unsigned($precision,self::__bcscale())) : $df; } catch(\Exception $e) { return $df; }
	}

	public static function __float($v, $df = null) {
		try{ return $v !== null && is_numeric($v) ? (float)$v : $df; } catch(\Exception $e) { return $df; }
	}

	public static function __double($v, $df = null) {
		return self::__float($v, $df);
	}

	public static function __real($v, $df = null) {
		return self::__float($v, $df);
	}

	public static function __unsigned_float($v, $df = null) {
		return self::__float($v, $df) >= 0 ? self::__float($v, $df) : $df;
	}

	public static function __unsigned_double($v, $df = null) {
		return self::__unsigned_float($v, $df);
	}

	public static function __unsigned_real($v, $df = null) {
		return self::__unsigned_float($v, $df);
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

	public static function __indexed_array($v, $df = null) {
		try{ return $v !== null && is_array($v) && array_keys($v) === range(0, count($v)-1) ? (array)$v : $df; } catch(\Exception $e) { return $df; }
	}

	public static function __assoc_array($v, $df = null) {
		try{ return $v !== null && is_array($v) && array_keys($v) !== range(0, count($v)-1) ? (array)$v : $df; } catch(\Exception $e) { return $df; }
	}

	public static function __object($v, $df = null) {
		try{ return $v !== null && is_object($v) ? $v : $df; } catch(\Exception $e) { return $df; }
	}

	public static function __timezone($v, $df) {
		try{ return self::__is_string($v) && self::__in($v, timezone_identifiers_list()) ? $v : $df; } catch(\Exception $e) { return $df; }
	}

	public static function __json_array($v, $df = null) {
		try{
			// Convert to JSON
			$v = is_string($v) ? json_decode($v, true) : null;

			// Valid JSON format
			if(json_last_error() !== JSON_ERROR_NONE) {
				return $df;
			}

			return is_array($v) ? $v : $df;
		} catch(\Exception $e) { return $df; }
	}

	public static function __json($v, $df = null) {
		try{
			// Convert to JSON
			$v = json_encode($v);

			// Valid JSON format
			if(json_last_error() !== JSON_ERROR_NONE) {
				return $df;
			}

			return $v;
		} catch(\Exception $e) { return $df; }
	}

	public static function __xml($v, $df = null) {
		try{
			if(!self::__is_xml($v)) {
				return $df;
			}

			// Convert string
			if(is_string($v)) {
				$v = \DOMDocument::loadXML($v);
			// Convert SimpleXMLElement
			} else if($v instanceof \SimpleXMLElement) {
				$v = dom_import_simplexml($v);
			// Unhandled XML implementation
			} else if(!($v instanceof \DOMDocument)) {
				return $df;
			}

			// Return as a DOMDocument
			return $v;
		} catch(\Exception $e) { return $df; }
	}

	public static function __http_method($v, $df = "HEAD") {
		try{
			return 	$v !== null && is_string($v) &&
					in_array(strtoupper($v), self::HTTP_METHODS) ?
					strtoupper($v) : $df
			;
		} catch(\Exception $e) { return $df; }
	}

	public static function __http_header($v, $df = null) {
		try{
			return 	$v !== null && is_string($v) &&
					(
						in_array(strtolower($v), array_map('strtolower', self::HTTP_HEADERS)) ||
						preg_match('/^x-\w+$/i',$v)
					) ?
					$v : $df
			;
		} catch(\Exception $e) { return $df; }
	}

	public static function __http_header_array($v, $df = null) {
		try{
			if($v === null || !is_array($v)) {
				return $df;
			}

			foreach($v as $x => $y) {
				if(self::__http_header($x) === null) {
					if(	!is_string($y) &&
						!is_array($y) &&
						!($y instanceof int) &&
						!($y instanceof float) &&
						!($y instanceof bool)
					) {
						return $df;
					}
				}
			}

			return $v;
		} catch(\Exception $e) { return $df; }
	}

	// TESTERS

	public static function __is_string($v) {
		try{ return $v !== null && is_string($v); } catch(\Exception $e) { return false; }
	}

	public static function __is_stringable($v) {
		return 	(!is_array($v)) && ((!is_object($v) && settype($v, 'string') !== false) ||
    			(is_object($v) && method_exists($v, '__toString')))
    	;
	}

	public static function __is_numeric($v) {
		try{ return $v !== null && is_numeric($v); } catch(\Exception $e) { return false; }
	}

	public static function __is_number($v) {
		return self::__is_numeric($v);
	}

	public static function __is_int($v) {
		try{ return $v !== null && is_int($v); } catch(\Exception $e) { return false; }
	}

	public static function __is_long($v) {
		return self::__is_int($v);
	}

	public static function __is_small($v) {
		return self::__is_int($v);
	}

	public static function __is_tiny($v) {
		return self::__is_int($v);
	}

	public static function __is_unsigned($v) {
		return self::__int($v,-1) >= 0 ? self::__int($v, $df) : $df;
	}

	public static function __is_decimal($v) {
		try{ return $v !== null && preg_match('/^\-?(\d+\.?\d*|\d*\.?\d+)$/', $v); } catch(\Exception $e) { return false; }
	}

	public static function __is_bcdecimal($v) {
		return self::__is_decimal($v);
	}

	public static function __is_float($v) {
		try{ return $v !== null && is_float($v); } catch(\Exception $e) { return false; }
	}

	public static function __is_double($v) {
		return self::__is_float($v);
	}

	public static function __is_real($v) {
		return self::__is_float($v);
	}

	public static function __is_boolean($v) {
		try{
			if($v !== null && is_bool($v)) {
				return true;
			} else if ($v !== null && is_int($v)) {
				return true;
			} else if ($v !== null && is_string($v) && !empty($v)) {
				switch(strtolower($v)) {
					case '1':
					case 'true':
					case 'on':
					case 'yes':
					case 'y':
					case '0':
					case '-1':
					case 'false':
					case 'off':
					case 'no':
					case 'n':
						return true;
					default:
						return false;
				}
			}
			return false;
		} catch(\Exception $e) { return false; }
	}

	public static function __is_bool($v) {
		return self::__is_boolean($v);
	}

	public static function __is_array($v) {
		try{ return $v !== null && is_array($v); } catch(\Exception $e) { return false; }
	}

	public static function __is_indexed_array($v) {
		try{ return $v !== null && is_array($v) && array_keys($v) === range(0, count($v)-1); } catch(\Exception $e) { return false; }
	}

	public static function __is_assoc_array($v) {
		try{ return $v !== null && is_array($v) && array_keys($v) !== range(0, count($v)-1); } catch(\Exception $e) { return false; }
	}

	public static function __is_object($v) {
		try{ return $v !== null && is_object($v); } catch(\Exception $e) { return false; }
	}

	public static function __is_timezone($v) {
		try{ return self::__is_string($v) && self::__in($v, timezone_identifiers_list()); } catch(\Exception $e) { return false; }
	}

	public static function __is_json_array($v) {
		try{
			return is_string($v) && is_array(json_decode($v, true)) && (json_last_error() == JSON_ERROR_NONE);
		} catch(\Exception $e) { return false; }
	}

	public static function __is_json_string($v) {
		try{
			return is_string($v) && json_decode($v) && (json_last_error() == JSON_ERROR_NONE);
		} catch(\Exception $e) { return false; }
	}

	public static function __is_json($v) {
		try{
			// Convert to JSON
			$v = json_encode($v);

			// Valid JSON format
			if(json_last_error() !== JSON_ERROR_NONE) {
				return false;
			}

			return true;
		} catch(\Exception $e) { return false; }
	}

	public static function __is_xml($v) {
		try{
			return 	$v !== null &&
					(
						(is_string($v) && @simplexml_load_string($v) !== false) ||
						$v instanceof \SimpleXMLElement ||
						$v instanceof \DOMDocument
					)
			;
		} catch(\Exception $e) { return false; }
	}

	public static function __is_http_method($v) {
		try{
			return 	$v !== null && is_string($v) && in_array(strtoupper($v), self::HTTP_METHODS);
		} catch(\Exception $e) { return false; }
	}

	public static function __is_http_header($v) {
		try{
			return 	$v !== null && is_string($v) &&
					(
						in_array(strtolower($v), array_map('strtolower', self::HTTP_HEADERS)) ||
						preg_match('/^x-\w+$/i',$v)
					)
			;
		} catch(\Exception $e) { return false; }
	}

	public static function __is_http_header_array($v) {
		try{
			if($v === null || !is_array($v)) {
				return false;
			}

			foreach($v as $x => $y) {
				if(self::__is_http_header($x) === null) {
					return false;
				}
			}

			return true;
		} catch(\Exception $e) { return false; }
	}

	public static function __empty_string($v, $df = null) {
		if($df !== null) {
			return isset($v) === true && $v === '' ? $df : $v;
		}

		return isset($v) === true && $v === '';
	}

	public static function __bcscale() {
		return self::__unsigned((strlen(bcsqrt("2")) - 2), 0);
	}

		public static function __iso_country($code) {
		return self::__array_get(array_replace(self::ISO_3166_ALPHA2, self::ISO_3166_ALPHA3), self::__in(self::__string($code,""), array_replace(self::ISO_3166_ALPHA2, self::ISO_3166_ALPHA3), false, null));
	}

	public static function __iso_country_code($country, $alpha3 = false) {
		return !self::__bool($alpha3,false) ?
			self::__array_get(array_flip(self::ISO_3166_ALPHA2), self::__in(self::__string($country,""), array_flip(self::ISO_3166_ALPHA2))) :
			self::__array_get(array_flip(self::ISO_3166_ALPHA3), self::__in(self::__string($country,""), array_flip(self::ISO_3166_ALPHA3)))
		;
	}



	// Checks if a variable is in a given list
	public static function __in($needle, Array $haystack = [], $case_sensitive = false, $df = false) {
		$needle_c = is_string($needle) && !$case_sensitive ? strtolower($needle) : $needle;

		// If case insensitive then iteratively check each element as lowercase comparisons
		if(!$case_sensitive) {
			if(!self::__is_indexed_array($haystack)) {
				foreach($haystack as $k => $v) {
					$k_c = is_string($k) ? strtolower($k) : $k;
					if( $needle_c === $k_c ) {
						return $k;
					} else {
						continue;
					}
				}
			} else {
				foreach($haystack as $k) {
					$k_c = is_string($k) ? strtolower($k) : $k;
					if( $needle_c === $k_c ) {
						return $k;
					} else {
						continue;
					}
				}
			}

			// Return a no-match if still inside the method
			return $df;
		}

		// Return a simple array_key_exists results
		return array_key_exists($needle, $haystack) ? $needle : $df;
	}

	public static function __array_insert_before($key, array $array, $new_key, $new_value = null) {
		if($array !== null) {
			$new = [];
			if(self::__is_assoc_array($array) && array_key_exists($key, $array)) {
				foreach($array as $k => $value) {
					if($k === $key) {
						$new[$new_key] = $new_value;
					}
					$new[$k] = $value;
				}
			} else if(self::__is_indexed_array($array) && in_array($key, $array)) {
				foreach($array as $k) {
					if($k === $key) {
						$new[] = $new_key;
					}
					$new[] = $k;
				}
			} else {
				return false;
			}
			return $new;
		}
		return false;
	}

	public static function __array_insert_after($key, array $array, $new_key, $new_value = null) {
		if($array !== null) {
			$new = [];
			if(self::__is_assoc_array($array) && array_key_exists($key, $array)) {
				foreach($array as $k => $value) {
					$new[$k] = $value;
					if($k === $key) {
						$new[$new_key] = $new_value;
					}
				}
			} else if(self::__is_indexed_array($array) && in_array($key, $array)) {
				foreach($array as $k) {
					$new[] = $k;
					if($k === $key) {
						$new[] = $new_key;
					}
				}
			} else {
				return false;
			}
			return $new;
		}
		return false;
	}

	public static function __array_compact($haystack, $prune_null = true, $prune_string = false, $trim_string = true) {
		$fn = __FUNCTION__;

		foreach($haystack as $key => &$value) {
			if(is_array($value)) {
				$haystack[$key] = self::$fn($value, $prune_null, $prune_string, $trim_string);
			} else if(is_string($value) && $trim_string) {
				$haystack[$key] = trim($value);
			}

			if($prune_null && is_null($value)) {
				unset($haystack[$key]);
			} else if(is_string($value) && $prune_string && empty($value) && $value != "0"){
				unset($haystack[$key]);
			} else if(is_array($value) && empty($value)) {
				unset($haystack[$key]);
			}
		}

		return $haystack;
	}

	public static function __switchcase($switch, Array $cases = [], Array $case_results = [], $df = null) {
		try {
			if($switch !== null) {
				$cases = self::__array($cases, []);
				$case_results = self::__array($case_results, []);
				// Attempt to find an exact match between switch and case
				foreach($cases as $i => $c) {
					// If switch and case exactly match then return the respectively indexed case result if defined
					// if not defined case_results then return the matched case as a result
					if($switch === $c) {
						if(!empty($case_results)) {
							$r = self::__array_get($case_results, $i);
							return $r !== null ? $r : $df;
						} else {
							return $c;
						}
					}
				}
			}
			// Return default for a non-match result
			return $df;
		} catch(\Exception $e) { return $df; }
	}

	public static function __switchcase_assoc($switch, Array $cases = [], $df = null) {
		try {
			if($switch !== null) {
				$cases = self::__array($cases, []);
				// Attempt to find an exact match between switch and case
				foreach($cases as $k => $v) {
					// If switch and case exactly match then return the associated key value
					if($switch === $k) {
						return $v;
					}
				}
			}
			// Return default for a non-match result
			return $df;
		} catch(\Exception $e) { return $df; }
	}

	public function __info($s, $c = 0) {
		if($this->logger !== null) {
			$old_name = $this->logger->name();
			$this->logger->set_name(self::__array($this->logger_name, $old_name));
			$this->logger->info($s, $c);
			$this->logger->set_name($old_name);
		}
	}

	public function __notice($s, $c = 0) {
		if($this->logger !== null) {
			$old_name = $this->logger->name();
			$this->logger->set_name(self::__array($this->logger_name, $old_name));
			$this->logger->notice($s, $c);
			$this->logger->set_name($old_name);
		}
	}

	public function __debug($s, $c = 0) {
		if($this->logger !== null) {
			$old_name = $this->logger->name();
			$this->logger->set_name(self::__array($this->logger_name, $old_name));
			$this->logger->debug($s, $c);
			$this->logger->set_name($old_name);
		}
	}

	public function __warning($s, $c = 0) {
		if($this->logger !== null) {
			$old_name = $this->logger->name();
			$this->logger->set_name(self::__array($this->logger_name, $old_name));
			$this->logger->warning($s, $c);
			$this->logger->set_name($old_name);
		}
	}

	public function __error($s, $c = 0) {
		if($this->logger !== null) {
			$old_name = $this->logger->name();
			$this->logger->set_name(self::__array($this->logger_name, $old_name));
			$this->logger->error($s, $c);
			$this->logger->set_name($old_name);
		}
	}

	public function __critical($s, $c = 0) {
		if($this->logger !== null) {
			$old_name = $this->logger->name();
			$this->logger->set_name(self::__array($this->logger_name, $old_name));
			$this->logger->critical($s, $c);
			$this->logger->set_name($old_name);
		}
	}

	public function __alert($s, $c = 0) {
		if($this->logger !== null) {
			$old_name = $this->logger->name();
			$this->logger->set_name(self::__array($this->logger_name, $old_name));
			$this->logger->alert($s, $c);
			$this->logger->set_name($old_name);
		}
	}

	public function __emergency($s, $c = 0) {
		if($this->logger !== null) {
			$old_name = $this->logger->name();
			$this->logger->set_name(self::__array($this->logger_name, $old_name));
			$this->logger->emergency($s, $c);
			$this->logger->set_name($old_name);
		}
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
		$headers = self::__array_get($o, "headers", "array", []);
		// Standardize header keys
		$headers = array_change_key_case($headers,CASE_LOWER);

		// Capture query parameters
    	$params = self::__array_get($o, "parameters", []);
    	// Capture payload
		$payload = self::__array_get($o, "payload");
		$form_fields = 1;
		// Check if payload is x-www-form-urlencoded and convert to URL encoded key=>value pairs
		if(strpos(self::__array_get($headers, "content-type", "string", ""), "application/x-www-form-urlencoded") !== false) {
			$payload = self::__array($payload,[]);
			$form_fields = count($payload);
			$payload = http_build_query($payload);
		} else if(strpos(self::__array_get($headers, "content-type", "string", ""), "application/json") !== false) {
			$payload = self::__array($payload,[]);
			$payload = json_encode($payload, JSON_NUMERIC_CHECK);
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
			case ($method === "PUT" || $method === "PATCH") && $form_fields > 1:
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
					curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
				break;
			}
		}

		// Convert header array into cURL header array
		if($headers) {
			// Convert to cURL formatted headers
			$ch_headers = [];
			foreach($headers as $k => $v) {
				if(is_array($v)) {
					foreach($v as $k2 => $v2) {
						$ch_headers[] = $k.": ".$v2;
					}
				} else {
					$ch_headers[] = $k.": ".$v;
				}
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

	protected function __import_settings() {
		// Get full class implementation lineage
		$class = new \ReflectionClass($this);
		$lineage = [];
		// Prepend instantiated class' name
		$lineage[] = $class->getName();
		// Construct full lineage of instantiated class
		while ($class = $class->getParentClass()) {
			$lineage[] = $class->getName();
		}

		// Array of all imported settings
		$settings = [];

		// Import each classes *.settings.php file if implemented
		foreach(array_reverse(self::__array($lineage,[])) as $class) {
			$file = (new \ReflectionClass($class))->getFileName();

			$fsettings = [];
			// Attempt to fetch settings file associated with the call script
			// in the format of <script_basefilename>.settings.<script_file_extension>
			$fsettings_file = sprintf(
				 "%s.settings.%s"
				,substr($file, 0, strrpos($file, "."))
				,substr($file, strrpos($file, ".")+1)
			);

			if(file_exists($fsettings_file)) {
				$fsettings = self::__array(require $fsettings_file, []);
			}

			// Merge/replace by key any file defined settings into the input settings
			$settings = array_replace($settings, $fsettings);
		}

		// Return complete list of imported settings
		return self::__array($settings,[]);
	}

	// Reference lists
	const HTTP_METHODS = [
		 "GET"
		,"HEAD"
		,"POST"
		,"PUT"
		,"DELETE"
		,"CONNECT"
		,"OPTIONS"
		,"TRACE"
		,"PATCH"
	];

	const HTTP_HEADERS = [
		 "A-IM"
		,"Accept"
		,"Accept-Charset"
		,"Accept-Encoding"
		,"Accept-Language"
		,"Accept-Datetime"
		,"Access-Control-Request-Method"
		,"Access-Control-Request-Headers"
		,"Authorization"
		,"Cache-Control"
		,"Connection"
		,"Content-Length"
		,"Content-Type"
		,"Cookie"
		,"Date"
		,"Expect"
		,"Forwarded"
		,"From"
		,"Host"
		,"If-Match"
		,"If-Modified-Since"
		,"If-None-Match"
		,"If-Range"
		,"If-Unmodified-Since"
		,"Max-Forwards"
		,"Origin"
		,"Pragma"
		,"Proxy-Authorization"
		,"Range"
		,"Referer"
		,"TE"
		,"User-Agent"
		,"Upgrade"
		,"Via"
		,"Warning"
	];

	const ISO_3166_ALPHA2 = [
		 "AF" => "Afghanistan"
		,"AL" => "Albania"
		,"DZ" => "Algeria"
		,"AS" => "American Samoa"
		,"AD" => "Andorra"
		,"AO" => "Angola"
		,"AI" => "Anguilla"
		,"AQ" => "Antarctica"
		,"AG" => "Antigua and Barbuda"
		,"AR" => "Argentina"
		,"AM" => "Armenia"
		,"AW" => "Aruba"
		,"AU" => "Australia"
		,"AT" => "Austria"
		,"AZ" => "Azerbaijan"
		,"BS" => "Bahamas (the)"
		,"BH" => "Bahrain"
		,"BD" => "Bangladesh"
		,"BB" => "Barbados"
		,"BY" => "Belarus"
		,"BE" => "Belgium"
		,"BZ" => "Belize"
		,"BJ" => "Benin"
		,"BM" => "Bermuda"
		,"BT" => "Bhutan"
		,"BO" => "Bolivia (Plurinational State of)"
		,"BQ" => "Bonaire, Sint Eustatius and Saba"
		,"BA" => "Bosnia and Herzegovina"
		,"BW" => "Botswana"
		,"BV" => "Bouvet Island"
		,"BR" => "Brazil"
		,"IO" => "British Indian Ocean Territory (the)"
		,"BN" => "Brunei Darussalam"
		,"BG" => "Bulgaria"
		,"BF" => "Burkina Faso"
		,"BI" => "Burundi"
		,"CV" => "Cabo Verde"
		,"KH" => "Cambodia"
		,"CM" => "Cameroon"
		,"CA" => "Canada"
		,"KY" => "Cayman Islands (the)"
		,"CF" => "Central African Republic (the)"
		,"TD" => "Chad"
		,"CL" => "Chile"
		,"CN" => "China"
		,"CX" => "Christmas Island"
		,"CC" => "Cocos (Keeling) Islands (the)"
		,"CO" => "Colombia"
		,"KM" => "Comoros (the)"
		,"CD" => "Congo (the Democratic Republic of the)"
		,"CG" => "Congo (the)"
		,"CK" => "Cook Islands (the)"
		,"CR" => "Costa Rica"
		,"HR" => "Croatia"
		,"CU" => "Cuba"
		,"CW" => "Curaçao"
		,"CY" => "Cyprus"
		,"CZ" => "Czechia"
		,"CI" => "Côte d'Ivoire"
		,"DK" => "Denmark"
		,"DJ" => "Djibouti"
		,"DM" => "Dominica"
		,"DO" => "Dominican Republic (the)"
		,"EC" => "Ecuador"
		,"EG" => "Egypt"
		,"SV" => "El Salvador"
		,"GQ" => "Equatorial Guinea"
		,"ER" => "Eritrea"
		,"EE" => "Estonia"
		,"SZ" => "Eswatini"
		,"ET" => "Ethiopia"
		,"FK" => "Falkland Islands (the) [Malvinas]"
		,"FO" => "Faroe Islands (the)"
		,"FJ" => "Fiji"
		,"FI" => "Finland"
		,"FR" => "France"
		,"GF" => "French Guiana"
		,"PF" => "French Polynesia"
		,"TF" => "French Southern Territories (the)"
		,"GA" => "Gabon"
		,"GM" => "Gambia (the)"
		,"GE" => "Georgia"
		,"DE" => "Germany"
		,"GH" => "Ghana"
		,"GI" => "Gibraltar"
		,"GR" => "Greece"
		,"GL" => "Greenland"
		,"GD" => "Grenada"
		,"GP" => "Guadeloupe"
		,"GU" => "Guam"
		,"GT" => "Guatemala"
		,"GG" => "Guernsey"
		,"GN" => "Guinea"
		,"GW" => "Guinea-Bissau"
		,"GY" => "Guyana"
		,"HT" => "Haiti"
		,"HM" => "Heard Island and McDonald Islands"
		,"VA" => "Holy See (the)"
		,"HN" => "Honduras"
		,"HK" => "Hong Kong"
		,"HU" => "Hungary"
		,"IS" => "Iceland"
		,"IN" => "India"
		,"ID" => "Indonesia"
		,"IR" => "Iran (Islamic Republic of)"
		,"IQ" => "Iraq"
		,"IE" => "Ireland"
		,"IM" => "Isle of Man"
		,"IL" => "Israel"
		,"IT" => "Italy"
		,"JM" => "Jamaica"
		,"JP" => "Japan"
		,"JE" => "Jersey"
		,"JO" => "Jordan"
		,"KZ" => "Kazakhstan"
		,"KE" => "Kenya"
		,"KI" => "Kiribati"
		,"KP" => "Korea (the Democratic People's Republic of)"
		,"KR" => "Korea (the Republic of)"
		,"KW" => "Kuwait"
		,"KG" => "Kyrgyzstan"
		,"LA" => "Lao People's Democratic Republic (the)"
		,"LV" => "Latvia"
		,"LB" => "Lebanon"
		,"LS" => "Lesotho"
		,"LR" => "Liberia"
		,"LY" => "Libya"
		,"LI" => "Liechtenstein"
		,"LT" => "Lithuania"
		,"LU" => "Luxembourg"
		,"MO" => "Macao"
		,"MG" => "Madagascar"
		,"MW" => "Malawi"
		,"MY" => "Malaysia"
		,"MV" => "Maldives"
		,"ML" => "Mali"
		,"MT" => "Malta"
		,"MH" => "Marshall Islands (the)"
		,"MQ" => "Martinique"
		,"MR" => "Mauritania"
		,"MU" => "Mauritius"
		,"YT" => "Mayotte"
		,"MX" => "Mexico"
		,"FM" => "Micronesia (Federated States of)"
		,"MD" => "Moldova (the Republic of)"
		,"MC" => "Monaco"
		,"MN" => "Mongolia"
		,"ME" => "Montenegro"
		,"MS" => "Montserrat"
		,"MA" => "Morocco"
		,"MZ" => "Mozambique"
		,"MM" => "Myanmar"
		,"NA" => "Namibia"
		,"NR" => "Nauru"
		,"NP" => "Nepal"
		,"NL" => "Netherlands (the)"
		,"NC" => "New Caledonia"
		,"NZ" => "New Zealand"
		,"NI" => "Nicaragua"
		,"NE" => "Niger (the)"
		,"NG" => "Nigeria"
		,"NU" => "Niue"
		,"NF" => "Norfolk Island"
		,"MP" => "Northern Mariana Islands (the)"
		,"NO" => "Norway"
		,"OM" => "Oman"
		,"PK" => "Pakistan"
		,"PW" => "Palau"
		,"PS" => "Palestine, State of"
		,"PA" => "Panama"
		,"PG" => "Papua New Guinea"
		,"PY" => "Paraguay"
		,"PE" => "Peru"
		,"PH" => "Philippines (the)"
		,"PN" => "Pitcairn"
		,"PL" => "Poland"
		,"PT" => "Portugal"
		,"PR" => "Puerto Rico"
		,"QA" => "Qatar"
		,"MK" => "Republic of North Macedonia"
		,"RO" => "Romania"
		,"RU" => "Russian Federation (the)"
		,"RW" => "Rwanda"
		,"RE" => "Réunion"
		,"BL" => "Saint Barthélemy"
		,"SH" => "Saint Helena, Ascension and Tristan da Cunha"
		,"KN" => "Saint Kitts and Nevis"
		,"LC" => "Saint Lucia"
		,"MF" => "Saint Martin (French part)"
		,"PM" => "Saint Pierre and Miquelon"
		,"VC" => "Saint Vincent and the Grenadines"
		,"WS" => "Samoa"
		,"SM" => "San Marino"
		,"ST" => "Sao Tome and Principe"
		,"SA" => "Saudi Arabia"
		,"SN" => "Senegal"
		,"RS" => "Serbia"
		,"SC" => "Seychelles"
		,"SL" => "Sierra Leone"
		,"SG" => "Singapore"
		,"SX" => "Sint Maarten (Dutch part)"
		,"SK" => "Slovakia"
		,"SI" => "Slovenia"
		,"SB" => "Solomon Islands"
		,"SO" => "Somalia"
		,"ZA" => "South Africa"
		,"GS" => "South Georgia and the South Sandwich Islands"
		,"SS" => "South Sudan"
		,"ES" => "Spain"
		,"LK" => "Sri Lanka"
		,"SD" => "Sudan (the)"
		,"SR" => "Suriname"
		,"SJ" => "Svalbard and Jan Mayen"
		,"SE" => "Sweden"
		,"CH" => "Switzerland"
		,"SY" => "Syrian Arab Republic"
		,"TW" => "Taiwan (Province of China)"
		,"TJ" => "Tajikistan"
		,"TZ" => "Tanzania, United Republic of"
		,"TH" => "Thailand"
		,"TL" => "Timor-Leste"
		,"TG" => "Togo"
		,"TK" => "Tokelau"
		,"TO" => "Tonga"
		,"TT" => "Trinidad and Tobago"
		,"TN" => "Tunisia"
		,"TR" => "Turkey"
		,"TM" => "Turkmenistan"
		,"TC" => "Turks and Caicos Islands (the)"
		,"TV" => "Tuvalu"
		,"UG" => "Uganda"
		,"UA" => "Ukraine"
		,"AE" => "United Arab Emirates (the)"
		,"GB" => "United Kingdom of Great Britain and Northern Ireland (the)"
		,"UM" => "United States Minor Outlying Islands (the)"
		,"US" => "United States of America (the)"
		,"UY" => "Uruguay"
		,"UZ" => "Uzbekistan"
		,"VU" => "Vanuatu"
		,"VE" => "Venezuela (Bolivarian Republic of)"
		,"VN" => "Viet Nam"
		,"VG" => "Virgin Islands (British)"
		,"VI" => "Virgin Islands (U.S.)"
		,"WF" => "Wallis and Futuna"
		,"EH" => "Western Sahara"
		,"YE" => "Yemen"
		,"ZM" => "Zambia"
		,"ZW" => "Zimbabwe"
		,"AX" => "Åland Islands"
	];

	const ISO_3166_ALPHA3 = [
		 "AFG" => "Afghanistan"
		,"ALB" => "Albania"
		,"DZA" => "Algeria"
		,"ASM" => "American Samoa"
		,"AND" => "Andorra"
		,"AGO" => "Angola"
		,"AIA" => "Anguilla"
		,"ATA" => "Antarctica"
		,"ATG" => "Antigua and Barbuda"
		,"ARG" => "Argentina"
		,"ARM" => "Armenia"
		,"ABW" => "Aruba"
		,"AUS" => "Australia"
		,"AUT" => "Austria"
		,"AZE" => "Azerbaijan"
		,"BHS" => "Bahamas (the)"
		,"BHR" => "Bahrain"
		,"BGD" => "Bangladesh"
		,"BRB" => "Barbados"
		,"BLR" => "Belarus"
		,"BEL" => "Belgium"
		,"BLZ" => "Belize"
		,"BEN" => "Benin"
		,"BMU" => "Bermuda"
		,"BTN" => "Bhutan"
		,"BOL" => "Bolivia (Plurinational State of)"
		,"BES" => "Bonaire, Sint Eustatius and Saba"
		,"BIH" => "Bosnia and Herzegovina"
		,"BWA" => "Botswana"
		,"BVT" => "Bouvet Island"
		,"BRA" => "Brazil"
		,"IOT" => "British Indian Ocean Territory (the)"
		,"BRN" => "Brunei Darussalam"
		,"BGR" => "Bulgaria"
		,"BFA" => "Burkina Faso"
		,"BDI" => "Burundi"
		,"CPV" => "Cabo Verde"
		,"KHM" => "Cambodia"
		,"CMR" => "Cameroon"
		,"CAN" => "Canada"
		,"CYM" => "Cayman Islands (the)"
		,"CAF" => "Central African Republic (the)"
		,"TCD" => "Chad"
		,"CHL" => "Chile"
		,"CHN" => "China"
		,"CXR" => "Christmas Island"
		,"CCK" => "Cocos (Keeling) Islands (the)"
		,"COL" => "Colombia"
		,"COM" => "Comoros (the)"
		,"COD" => "Congo (the Democratic Republic of the)"
		,"COG" => "Congo (the)"
		,"COK" => "Cook Islands (the)"
		,"CRI" => "Costa Rica"
		,"HRV" => "Croatia"
		,"CUB" => "Cuba"
		,"CUW" => "Curaçao"
		,"CYP" => "Cyprus"
		,"CZE" => "Czechia"
		,"CIV" => "Côte d'Ivoire"
		,"DNK" => "Denmark"
		,"DJI" => "Djibouti"
		,"DMA" => "Dominica"
		,"DOM" => "Dominican Republic (the)"
		,"ECU" => "Ecuador"
		,"EGY" => "Egypt"
		,"SLV" => "El Salvador"
		,"GNQ" => "Equatorial Guinea"
		,"ERI" => "Eritrea"
		,"EST" => "Estonia"
		,"SWZ" => "Eswatini"
		,"ETH" => "Ethiopia"
		,"FLK" => "Falkland Islands (the) [Malvinas]"
		,"FRO" => "Faroe Islands (the)"
		,"FJI" => "Fiji"
		,"FIN" => "Finland"
		,"FRA" => "France"
		,"GUF" => "French Guiana"
		,"PYF" => "French Polynesia"
		,"ATF" => "French Southern Territories (the)"
		,"GAB" => "Gabon"
		,"GMB" => "Gambia (the)"
		,"GEO" => "Georgia"
		,"DEU" => "Germany"
		,"GHA" => "Ghana"
		,"GIB" => "Gibraltar"
		,"GRC" => "Greece"
		,"GRL" => "Greenland"
		,"GRD" => "Grenada"
		,"GLP" => "Guadeloupe"
		,"GUM" => "Guam"
		,"GTM" => "Guatemala"
		,"GGY" => "Guernsey"
		,"GIN" => "Guinea"
		,"GNB" => "Guinea-Bissau"
		,"GUY" => "Guyana"
		,"HTI" => "Haiti"
		,"HMD" => "Heard Island and McDonald Islands"
		,"VAT" => "Holy See (the)"
		,"HND" => "Honduras"
		,"HKG" => "Hong Kong"
		,"HUN" => "Hungary"
		,"ISL" => "Iceland"
		,"IND" => "India"
		,"IDN" => "Indonesia"
		,"IRN" => "Iran (Islamic Republic of)"
		,"IRQ" => "Iraq"
		,"IRL" => "Ireland"
		,"IMN" => "Isle of Man"
		,"ISR" => "Israel"
		,"ITA" => "Italy"
		,"JAM" => "Jamaica"
		,"JPN" => "Japan"
		,"JEY" => "Jersey"
		,"JOR" => "Jordan"
		,"KAZ" => "Kazakhstan"
		,"KEN" => "Kenya"
		,"KIR" => "Kiribati"
		,"PRK" => "Korea (the Democratic People's Republic of)"
		,"KOR" => "Korea (the Republic of)"
		,"KWT" => "Kuwait"
		,"KGZ" => "Kyrgyzstan"
		,"LAO" => "Lao People's Democratic Republic (the)"
		,"LVA" => "Latvia"
		,"LBN" => "Lebanon"
		,"LSO" => "Lesotho"
		,"LBR" => "Liberia"
		,"LBY" => "Libya"
		,"LIE" => "Liechtenstein"
		,"LTU" => "Lithuania"
		,"LUX" => "Luxembourg"
		,"MAC" => "Macao"
		,"MDG" => "Madagascar"
		,"MWI" => "Malawi"
		,"MYS" => "Malaysia"
		,"MDV" => "Maldives"
		,"MLI" => "Mali"
		,"MLT" => "Malta"
		,"MHL" => "Marshall Islands (the)"
		,"MTQ" => "Martinique"
		,"MRT" => "Mauritania"
		,"MUS" => "Mauritius"
		,"MYT" => "Mayotte"
		,"MEX" => "Mexico"
		,"FSM" => "Micronesia (Federated States of)"
		,"MDA" => "Moldova (the Republic of)"
		,"MCO" => "Monaco"
		,"MNG" => "Mongolia"
		,"MNE" => "Montenegro"
		,"MSR" => "Montserrat"
		,"MAR" => "Morocco"
		,"MOZ" => "Mozambique"
		,"MMR" => "Myanmar"
		,"NAM" => "Namibia"
		,"NRU" => "Nauru"
		,"NPL" => "Nepal"
		,"NLD" => "Netherlands (the)"
		,"NCL" => "New Caledonia"
		,"NZL" => "New Zealand"
		,"NIC" => "Nicaragua"
		,"NER" => "Niger (the)"
		,"NGA" => "Nigeria"
		,"NIU" => "Niue"
		,"NFK" => "Norfolk Island"
		,"MNP" => "Northern Mariana Islands (the)"
		,"NOR" => "Norway"
		,"OMN" => "Oman"
		,"PAK" => "Pakistan"
		,"PLW" => "Palau"
		,"PSE" => "Palestine, State of"
		,"PAN" => "Panama"
		,"PNG" => "Papua New Guinea"
		,"PRY" => "Paraguay"
		,"PER" => "Peru"
		,"PHL" => "Philippines (the)"
		,"PCN" => "Pitcairn"
		,"POL" => "Poland"
		,"PRT" => "Portugal"
		,"PRI" => "Puerto Rico"
		,"QAT" => "Qatar"
		,"MKD" => "Republic of North Macedonia"
		,"ROU" => "Romania"
		,"RUS" => "Russian Federation (the)"
		,"RWA" => "Rwanda"
		,"REU" => "Réunion"
		,"BLM" => "Saint Barthélemy"
		,"SHN" => "Saint Helena, Ascension and Tristan da Cunha"
		,"KNA" => "Saint Kitts and Nevis"
		,"LCA" => "Saint Lucia"
		,"MAF" => "Saint Martin (French part)"
		,"SPM" => "Saint Pierre and Miquelon"
		,"VCT" => "Saint Vincent and the Grenadines"
		,"WSM" => "Samoa"
		,"SMR" => "San Marino"
		,"STP" => "Sao Tome and Principe"
		,"SAU" => "Saudi Arabia"
		,"SEN" => "Senegal"
		,"SRB" => "Serbia"
		,"SYC" => "Seychelles"
		,"SLE" => "Sierra Leone"
		,"SGP" => "Singapore"
		,"SXM" => "Sint Maarten (Dutch part)"
		,"SVK" => "Slovakia"
		,"SVN" => "Slovenia"
		,"SLB" => "Solomon Islands"
		,"SOM" => "Somalia"
		,"ZAF" => "South Africa"
		,"SGS" => "South Georgia and the South Sandwich Islands"
		,"SSD" => "South Sudan"
		,"ESP" => "Spain"
		,"LKA" => "Sri Lanka"
		,"SDN" => "Sudan (the)"
		,"SUR" => "Suriname"
		,"SJM" => "Svalbard and Jan Mayen"
		,"SWE" => "Sweden"
		,"CHE" => "Switzerland"
		,"SYR" => "Syrian Arab Republic"
		,"TWN" => "Taiwan (Province of China)"
		,"TJK" => "Tajikistan"
		,"TZA" => "Tanzania, United Republic of"
		,"THA" => "Thailand"
		,"TLS" => "Timor-Leste"
		,"TGO" => "Togo"
		,"TKL" => "Tokelau"
		,"TON" => "Tonga"
		,"TTO" => "Trinidad and Tobago"
		,"TUN" => "Tunisia"
		,"TUR" => "Turkey"
		,"TKM" => "Turkmenistan"
		,"TCA" => "Turks and Caicos Islands (the)"
		,"TUV" => "Tuvalu"
		,"UGA" => "Uganda"
		,"UKR" => "Ukraine"
		,"ARE" => "United Arab Emirates (the)"
		,"GBR" => "United Kingdom of Great Britain and Northern Ireland (the)"
		,"UMI" => "United States Minor Outlying Islands (the)"
		,"USA" => "United States of America (the)"
		,"URY" => "Uruguay"
		,"UZB" => "Uzbekistan"
		,"VUT" => "Vanuatu"
		,"VEN" => "Venezuela (Bolivarian Republic of)"
		,"VNM" => "Viet Nam"
		,"VGB" => "Virgin Islands (British)"
		,"VIR" => "Virgin Islands (U.S.)"
		,"WLF" => "Wallis and Futuna"
		,"ESH" => "Western Sahara"
		,"YEM" => "Yemen"
		,"ZMB" => "Zambia"
		,"ZWE" => "Zimbabwe"
		,"ALA" => "Åland Islands"
	];

}