<?php

require 'mapping.php';

function validate($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_FIELD_MAPPING_;

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
		
			// TEST: Mandatory fields have been set
			test_mandatory_fields_set(
				$data,
				array(
					 $_FIELD_MAPPING_["_source"]
					,$_FIELD_MAPPING_["_type"]
					,$_FIELD_MAPPING_["_id"]
					,$_FIELD_MAPPING_["_title"]
					,$_FIELD_MAPPING_["_transaction_date"]
				),
				$errs
			);
			// TEST: Mandatory fields contain non-empty data
			test_mandatory_fields_valid(
				$data,
				array(
					 $_FIELD_MAPPING_["_source"]
					,$_FIELD_MAPPING_["_type"]
					,$_FIELD_MAPPING_["_id"]
					,$_FIELD_MAPPING_["_title"]
				),
				$errs
			);
		
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_FIELD_MAPPING_["_source_instance"]
					,$_FIELD_MAPPING_["_status"]
				),
				$errs
			);
		
			// TEST: Date values are valid
			test_date_fields_valid(
				$data,
				array(
					 $_FIELD_MAPPING_["_transaction_date"]
					,$_FIELD_MAPPING_["_created_at"]
					,$_FIELD_MAPPING_["_updated_at"]
					,$_FIELD_MAPPING_["_updated_at_utc"]
				),
				$errs
			);
			
			// TEST: Timezone values are valid
			test_timezone_fields_valid(
				$data,
				array(
					 $_FIELD_MAPPING_["_transaction_timezone"]
				),
				$errs
			);	
		}	
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
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
