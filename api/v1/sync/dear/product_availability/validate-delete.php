<?php

require 'mapping.php';

function validate_delete($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_DELETE_FIELD_MAPPING_;

		// Default test for input
		if(!$body) {
			array_push($errs, to_error(400, "Missing JSON request parameters"));
			return $errs;
		}
		// Convert to JSON
   	 	$data = json_decode($body, true);

   	 	// TEST: Valid JSON format
		if(json_last_error() != JSON_ERROR_NONE) {
			array_push($errs, to_error(400, "Invalid JSON format for request constructed"));
			return $errs;
		}
		
		// Test JSON Node is an Object or Array
		if(!is_array($data)) {
			array_push($errs, to_error(400, "JSON Node [".addslashes($data)."] is not a valid Object or Array"));
			return $errs;
		}
		
		// TEST: Mandatory fields have been set
		test_mandatory_fields_set(
			$data,
			array(
				 $_DELETE_FIELD_MAPPING_["_company"]
				,$_DELETE_FIELD_MAPPING_["_retention_in_secs"]
			),
			$errs
		);
		
		// TEST: Mandatory fields contain non-empty data
		test_mandatory_fields_valid(
			$data,
			array(
				 $_DELETE_FIELD_MAPPING_["_company"]
			),
			$errs
		);
		
		// TEST: Numeric values are valid
		test_numeric_fields_valid(
			$data,
			array(
				 $_DELETE_FIELD_MAPPING_["_retention_in_secs"]
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
