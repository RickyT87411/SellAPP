<?php

require 'mapping.php';

function validate_post($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_POST_FIELD_MAPPING_;

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
		
		// Test JSON Node is an Object or Array
		if(!is_array($data)) {
			array_push($errs, to_error(400, "JSON Node [".addslashes($data)."] is not a valid Object or Array"));
			return $errs;
		}

		// TEST: Enum values are valid
		test_enum_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_by_system_master"]
			),
			array(
				 "dear"
				,"vend"
				,"playbill"
			),
			$errs
		);
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}
