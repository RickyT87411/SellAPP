<?php

require 'mapping.php';

function validate($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_FIELD_MAPPING_;

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

		// TEST: String values are valid
		test_string_fields_valid(
			$data,
			array(
				 $_FIELD_MAPPING_["_source"]
				,$_FIELD_MAPPING_["_source_instance"]
				,$_FIELD_MAPPING_["_location_type"]
				,$_FIELD_MAPPING_["_location"]
				,$_FIELD_MAPPING_["_outlet"]
				,$_FIELD_MAPPING_["_register"]
				,$_FIELD_MAPPING_["_type"]
				,$_FIELD_MAPPING_["_id"]
				,$_FIELD_MAPPING_["_title"]
				,$_FIELD_MAPPING_["_status"]
			),
			$errs
		);
	
		// TEST: Date values are valid
		test_enum_fields_valid(
			$data,
			array(
				 $_FIELD_MAPPING_["_by_date_type"]
			),
			array(
				 "transacted"
				,"created"
				,"updated"
				,"logged"
			),
			$errs
		);		
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}

