<?php

require 'mapping.php';

function validate($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_FIELD_MAPPING_;

		// Import enumeration lists
		global $_ENUM_INTEGRATION_STATUS_;
		global $_ENUM_TRANSMISSION_TYPE_;
	
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
		
		// Test JSON is formed as an array
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
		
			// Extract primary key if set
			$integration_id = array_key_exists($_FIELD_MAPPING_["integration_id"], $data) ? $data[$_FIELD_MAPPING_["integration_id"]] : null;
			
			// Only test mandatory fields if the primary key is absent		
			if(!$integration_id) {
				// TEST: Mandatory fields have been set
				test_mandatory_fields_set(
					$data,
					array(
						 $_FIELD_MAPPING_["_lhs_host"]
						,$_FIELD_MAPPING_["_lhs_host_instance"]
						,$_FIELD_MAPPING_["_lhs_type"]
						,$_FIELD_MAPPING_["_lhs_id"]
						,$_FIELD_MAPPING_["_rhs_host"]
						,$_FIELD_MAPPING_["_rhs_host_instance"]
						,$_FIELD_MAPPING_["_rhs_type"]
						,$_FIELD_MAPPING_["_transmission_type"]
						,$_FIELD_MAPPING_["_integration_status"]
					),
					$errs
				);
				// TEST: Mandatory fields contain non-empty data
				test_mandatory_fields_valid(
					$data,
					array(
						 $_FIELD_MAPPING_["_lhs_host"]
						,$_FIELD_MAPPING_["_lhs_type"]
						,$_FIELD_MAPPING_["_lhs_id"]
						,$_FIELD_MAPPING_["_rhs_host"]
						,$_FIELD_MAPPING_["_rhs_type"]
						,$_FIELD_MAPPING_["_transmission_type"]
						,$_FIELD_MAPPING_["_integration_status"]
					),
					$errs
				);
			}
		
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_FIELD_MAPPING_["_lhs_host_instance"]
					,$_FIELD_MAPPING_["_lhs_uri"]
					,$_FIELD_MAPPING_["_lhs_title"]
					,$_FIELD_MAPPING_["_lhs_status"]
					,$_FIELD_MAPPING_["_rhs_host_instance"]
					,$_FIELD_MAPPING_["_rhs_uri"]
					,$_FIELD_MAPPING_["_rhs_id"]
					,$_FIELD_MAPPING_["_rhs_title"]
					,$_FIELD_MAPPING_["_rhs_status"]
					,$_FIELD_MAPPING_["_broker"]
					,$_FIELD_MAPPING_["_broker_uri"]
					,$_FIELD_MAPPING_["_broker_job_id"]
					,$_FIELD_MAPPING_["_broker_job_instance_id"]
					,$_FIELD_MAPPING_["_result_description"]
					,$_FIELD_MAPPING_["integration_id"]
				),
				$errs
			);
		
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_FIELD_MAPPING_["_result"]
				),
				$errs
			);
		
			// TEST: Date values are valid
			test_date_fields_valid(
				$data,
				array(
					 $_FIELD_MAPPING_["_executed_at"]
				),
				$errs
			);
		
			// TEST: Enumeration values are valid
			test_enum_fields_valid(
				$data,
				array(
					 $_FIELD_MAPPING_["_transmission_type"]
				),
				$_ENUM_TRANSMISSION_TYPE_,
				$errs
			);
		
			// TEST: Enumeration values are valid
			test_enum_fields_valid(
				$data,
				array(
					 $_FIELD_MAPPING_["_integration_status"]
				),
				$_ENUM_INTEGRATION_STATUS_,
				$errs
			);
		}
				
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}