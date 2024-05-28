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
		
		// Default test for input
		if(!is_array($data)) {
			array_push($errs, to_error(400, "Root JSON Node must be an array"));
			return $errs;
		}
		
		// TEST: Numeric values are valid
		test_numeric_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_on_hand_from"]
				,$_POST_FIELD_MAPPING_["_on_hand_to"]
				,$_POST_FIELD_MAPPING_["_allocated_from"]
				,$_POST_FIELD_MAPPING_["_allocated_to"]
				,$_POST_FIELD_MAPPING_["_available_from"]
				,$_POST_FIELD_MAPPING_["_available_to"]
				,$_POST_FIELD_MAPPING_["_on_order_from"]
				,$_POST_FIELD_MAPPING_["_on_order_to"]
				,$_POST_FIELD_MAPPING_["_value_on_hand_from"]
				,$_POST_FIELD_MAPPING_["_value_on_hand_to"]
			),
			$errs
		);
	
		// TEST: Date values are valid
		test_date_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_expiry_date_from"]
				,$_POST_FIELD_MAPPING_["_expiry_date_to"]
			),
			$errs
		);

	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}