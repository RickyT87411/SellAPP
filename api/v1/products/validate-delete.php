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
   	 	$data = json_decode($body, true);
   	 	
   	 	// TEST: Valid JSON format
		if(json_last_error() != JSON_ERROR_NONE) {
			array_push($errs, to_error(400, "Invalid JSON format for request body"));
			return $errs;
		}
	
		// TEST: Mandatory fields have been set
		test_mandatory_fields_set(
			$data,
			array(
				 $_DELETE_FIELD_MAPPING_["_id"]
			),
			$errs
		);
		// TEST: Mandatory fields contain non-empty data
		test_mandatory_fields_valid(
			$data,
			array(
				 $_DELETE_FIELD_MAPPING_["_id"]
			),
			$errs
		);

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
