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
					 $_PUT_FIELD_MAPPING_["_company"]
					,$_PUT_FIELD_MAPPING_["_product_id"]
					,$_PUT_FIELD_MAPPING_["_sku"]
					,$_PUT_FIELD_MAPPING_["_name"]
					,$_PUT_FIELD_MAPPING_["_location"]
				),
				$errs
			);
			// TEST: Mandatory fields contain non-empty data
			test_mandatory_fields_valid(
				$data,
				array(
					 $_PUT_FIELD_MAPPING_["_company"]
					,$_PUT_FIELD_MAPPING_["_product_id"]
					,$_PUT_FIELD_MAPPING_["_sku"]
					,$_PUT_FIELD_MAPPING_["_name"]
					,$_PUT_FIELD_MAPPING_["_location"]
				),
				$errs
			);

			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_PUT_FIELD_MAPPING_["_on_hand"]
					,$_PUT_FIELD_MAPPING_["_allocated"]
					,$_PUT_FIELD_MAPPING_["_available"]
					,$_PUT_FIELD_MAPPING_["_on_order"]
					,$_PUT_FIELD_MAPPING_["_value_on_hand"]
				),
				$errs
			);
		
			// TEST: Date values are valid
			test_date_fields_valid(
				$data,
				array(
					 $_PUT_FIELD_MAPPING_["_expiry_date"]
				),
				$errs
			);
			
		}	
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}