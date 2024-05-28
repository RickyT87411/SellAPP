<?php

require 'mapping.php';

function validate_put($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_PUT_FIELD_MAPPING_;
		
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
				 $_PUT_FIELD_MAPPING_["_id"]
				,$_PUT_FIELD_MAPPING_["_batch_hash"]
				,$_PUT_FIELD_MAPPING_["_updated_at_utc"]
			),
			$errs
		);
		// TEST: Mandatory fields contain non-empty data
		test_mandatory_fields_valid(
			$data,
			array(
				 $_PUT_FIELD_MAPPING_["_id"]
				,$_PUT_FIELD_MAPPING_["_batch_hash"]
				,$_PUT_FIELD_MAPPING_["_updated_at_utc"]
			),
			$errs
		);
		// TEST: Date values are valid
		test_date_fields_valid(
			$data,
			array(
				 $_PUT_FIELD_MAPPING_["_updated_at_utc"]
			),
			$errs
		);
	
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
