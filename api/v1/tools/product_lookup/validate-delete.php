<?php

require 'mapping.php';

function validate_delete($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_DELETE_FIELD_MAPPING_;

		// Default test for input
		if(!$body) {
			array_push($errs, to_error(400, "Missing JSON request body"));
			return $errs;
		}
		// Convert to JSON
   	 	$array = json_decode($body, true);
   	 	
   	 	// TEST: Valid JSON format
		if(json_last_error() != JSON_ERROR_NONE) {
			array_push($errs, to_error(400, "Invalid JSON format for request body"));
			return $errs;
		}
		
		// Default test for input
		if(!is_array($array)) {
			array_push($errs, to_error(400, "Root JSON Node must be an array"));
			return $errs;
		}
		
		// Loop through each transaction in the array
		foreach($array as $data) {
			// Test JSON Node is an Object or Array
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node [".addslashes($data)."] is not a valid Object or Array"));
				return $errs;
			}
		
			// TEST: String values are valid
			test_mandatory_fields_set(
				$data,
				array(
					 $_DELETE_FIELD_MAPPING_["_sku"]
				),
				$errs
			);
			
			// TEST: String values are valid
			test_mandatory_fields_valid(
				$data,
				array(
					 $_DELETE_FIELD_MAPPING_["_sku"]
				),
				$errs
			);
		}	
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}

if(!function_exists("to_error")) {
	function to_error($code, $message) {
		return array(
			"ErrorCode"	=>	$code,
			"Exception"	=>	$message
		);
	}
}
