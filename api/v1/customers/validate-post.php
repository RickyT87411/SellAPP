<?php

require 'mapping.php';

function validate_post($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_POST_FIELD_MAPPING_;
		global $_CHANNELS_ENUM;
		global $_TYPE_ENUM;

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
				 $_POST_FIELD_MAPPING_["_channel"]
				,$_POST_FIELD_MAPPING_["_source"]
				,$_POST_FIELD_MAPPING_["_source_instance"]
				,$_POST_FIELD_MAPPING_["_source_id"]
			),
			$errs
		);
		// TEST: Mandatory fields contain non-empty data
		test_mandatory_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_source"]
				,$_POST_FIELD_MAPPING_["_source_instance"]
				,$_POST_FIELD_MAPPING_["_source_id"]
			),
			$errs
		);
		// TEST: Field contains valid enumerator values
		test_enum_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_channel"]
			),
			$_CHANNELS_ENUM,
			$errs
		);
		// TEST: Date values are valid
		test_date_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_active_at"]
				,$_POST_FIELD_MAPPING_["_activated_at"]
				,$_POST_FIELD_MAPPING_["_created_at"]
				,$_POST_FIELD_MAPPING_["_updated_at"]
				,$_POST_FIELD_MAPPING_["_updated_at_utc"]
			),
			$errs
		);
		// TEST: Numeric values are valid
		test_numeric_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_monotonic_version"]
			),
			$errs
		);
		// TEST: Boolean values are valid
		test_bool_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_active"]
			),
			$errs
		);	
		// TEST: Object values are valid
		test_object_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_rewards"]
			),
			null,
			$errs
		);
		// TEST: Timezone values are valid
		test_timezone_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_timezone"]
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
