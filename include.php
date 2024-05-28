<?php

/* Development Site URI containing custom PHP include path */
set_include_path(get_include_path() . PATH_SEPARATOR . '/www/dev.playbill.com.au/includes/');

/*************************
 * API accessor wrappers *
 *************************/
function _GET($url, $headers = null, $ssl_verification = false, $debug = false, &$errs=null, &$apiinfo=null) {
	try {
		if( !$url )
			return false;
		
		// GET request to Vend Product API
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if( $headers)   curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, is_bool($ssl_verification) ? $ssl_verification : false);

		// Execute RESTful API call
		$response = curl_exec($curl);
		
		// Get response info
		$apiinfo = curl_getinfo($curl);
		
		curl_close($curl);
		
		return $response;
	} catch(Exception $e) {
		if($errs && is_array($errs))
			array_push($errs, __api_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
		else
			throw($e);
		return false;
	}
}

function _POST($url, $payload = null, $fields = array(), $headers = null, $ssl_verification = false, $debug = false, &$errs=null, &$apiinfo=null) {
	try {
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
		
		// Execute RESTful API call
		$response = curl_exec($curl);

		// Get response info
		$apiinfo = curl_getinfo($curl);
		
		curl_close($curl);
		
		return $response;
	} catch(Exception $e) {
		if($errs && is_array($errs))
			array_push($errs, __api_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
		else
			throw($e);
		return false;
	}
}

function _PUT($url, $payload = null, $fields = array(), $headers = null, $ssl_verification = false, $debug = false, &$errs=null, &$apiinfo=null) {
	try {
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
		
		// Execute RESTful API call
		$response = curl_exec($curl);

		// Get response info
		$apiinfo = curl_getinfo($curl);
		
		curl_close($curl);
		
		return $response;
	} catch(Exception $e) {
		if($errs && is_array($errs))
			array_push($errs, __api_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
		else
			throw($e);
		return false;
	}
}

/*************************
 * API helper functions *
 *************************/
function __api_error($code, $message) {
	return array(
		"ErrorCode"	=>	$code,
		"Exception"	=>	$message
	);
}

function __method_not_allowed($method, $response) {
	return $response->withStatus(405)
			->withHeader("Content-Type","application/json")
			->write(json_encode($method." method not supported for this endpoint"));
}


if(!function_exists("test_mandatory_fields_set")) {
	function test_mandatory_fields_set($data, $fields, &$errs, $tree_path = '') {
		try {
			foreach($fields as $field)
				if(!array_key_exists($field, $data))
					array_push($errs, to_error(400, "Mandatory '".($tree_path ? $tree_path.'.' : '').$field."' attribute has not been set"));
		} catch (Exception $e) {
			array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
		}
	}
}

if(!function_exists("test_mandatory_fields_valid")) {
	function test_mandatory_fields_valid($data, $fields, &$errs, $tree_path = '') {
		try {
			foreach($fields as $field)
				if(array_key_exists($field, $data) && !$data[$field])
					array_push($errs, to_error(400, "Mandatory '".($tree_path ? $tree_path.'.' : '').$field."' attribute value cannot be blank"));
		} catch (Exception $e) {
			array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
		}
	}
}

if(!function_exists("test_string_fields_valid")) {
	function test_string_fields_valid($data, $fields, &$errs, $tree_path = '') {
		try {
			return;
			foreach($fields as $field)
				if(array_key_exists($field, $data) && !isset($data[$field]) )
					array_push($errs, to_error(400, "'".($tree_path ? $tree_path.'.' : '').$field."' attribute value cannot be blank"));
		} catch (Exception $e) {
			array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
		}
	}
}

if(!function_exists("test_numeric_fields_valid")) {
	function test_numeric_fields_valid($data, $fields, &$errs, $tree_path = '') {
		try {
			foreach($fields as $field)
				if(array_key_exists($field, $data) && $data[$field] != null && !is_numeric($data[$field]))
					array_push($errs, to_error(400, "'".($tree_path ? $tree_path.'.' : '').$field."' attribute value must be numeric and not null"));
		} catch (Exception $e) {
			array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
		}
	}
}

if(!function_exists("test_date_fields_valid")) {
	function test_date_fields_valid($data, $fields, &$errs, $tree_path = '') {
		try {
			foreach($fields as $field)
				if(array_key_exists($field, $data) && $data[$field] != null) {
					$d = DateTime::createFromFormat('Y-m-d H:i:s', $data[$field], new DateTimeZone("UTC"));	
					if(!$d || $d->format('Y-m-d H:i:s') != $data[$field])
						array_push($errs, to_error(400, "'".($tree_path ? $tree_path.'.' : '').$field."' attribute value must be a valid date of the format yyyy-mm-dd HH:mm:ss"));
				}
		} catch (Exception $e) {
			array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
		}
	}
}

if(!function_exists("test_bool_fields_valid")) {
	function test_bool_fields_valid($data, $fields, &$errs, $tree_path = '') {
		foreach($fields as $field)
			if(array_key_exists($field, $data) && $data[$field] != null && 
				!(
					$data[$field] === true ||
					$data[$field] === false ||
					strcmp(strtolower($data[$field]),"true") == 0 || 
					strcmp(strtolower($data[$field]),"false") == 0 ||
					(is_numeric($data[$field]) && $data[$field] == -1 || $data[$field] == 1) ||
					strcmp($data[$field],"1") == 0 ||
					strcmp($data[$field],"-1") == 0
				)
			)
				array_push($errs, to_error(400, "'".($tree_path ? $tree_path.'.' : '').$field."' attribute value must be a boolean type, i.e. one of [true, false, 0, 1]"));
	}
}

if(!function_exists("test_enum_fields_valid")) {
	function test_enum_fields_valid($data, $fields, $enum, &$errs, $tree_path = '') {
		try {
			foreach($fields as $field)
				if(array_key_exists($field, $data) && $data[$field] != null && !in_array($data[$field], $enum, TRUE))
					array_push($errs, to_error(400, "'".($tree_path ? $tree_path.'.' : '').$field."' attribute value must be one of [".implode(", ",$enum)."]"));
		} catch (Exception $e) {
			array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
		}
	}
}

if(!function_exists("test_object_fields_valid")) {
	function test_object_fields_valid($data, $fields, $enum, &$errs, $tree_path = '') {
		try {
			foreach($fields as $field)
				if(array_key_exists($field, $data) && $data[$field] != null && !is_array($data[$field]))
					array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data[$field])."] is not a valid Object or Array"));
		} catch (Exception $e) {
			array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
		}
	}
}

if(!function_exists("test_timezone_fields_valid")) {
	function test_timezone_fields_valid($data, $fields, &$errs, $tree_path = '') {
		try {
			foreach($fields as $field)
				if(array_key_exists($field, $data) && !in_array($data[$field], timezone_identifiers_list()))
					array_push($errs, to_error(400, "'".($tree_path ? $tree_path.'.' : '').$field."' attribute value must be a valid standard internet Timezone name"));
		} catch (Exception $e) {
			array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
		}
	}
}

if(!function_exists("is_assoc_array")) {
	function is_assoc_array($arr) {
		if (array() === $arr) return false;
    	return array_keys($arr) !== range(0, count($arr) - 1);
	}
}

if(!function_exists("to_error")) {
	function to_error($code, $message) {
		return array(
			"ErrorCode"	=>	$code,
			"Exception"	=>	$message
		);
	}
}
if(!function_exists("to_utf8_encoding")) {
	function to_utf8_encoding($content) { 
		if(!mb_check_encoding($content, 'UTF-8') 
			OR !($content === mb_convert_encoding(mb_convert_encoding($content, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {

			$content = mb_convert_encoding($content, 'UTF-8'); 
		} 
		return $content; 
	} 
}