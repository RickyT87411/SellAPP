<?php

require 'mapping.php';

function validate($body) { 
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
		
		// TEST: String values are valid
		test_string_fields_valid(
			$data,
			array(
				 $_FIELD_MAPPING_["_process_transaction_date_csv"]
				,$_FIELD_MAPPING_["_process_source_transaction_host_csv"]
				,$_FIELD_MAPPING_["_process_source_transaction_host_instance_csv"]
				,$_FIELD_MAPPING_["_process_source_transaction_type_csv"]
				,$_FIELD_MAPPING_["_process_source_transaction_id_csv"]
				,$_FIELD_MAPPING_["_process_source_transaction_title_csv"]
				,$_FIELD_MAPPING_["_process_source_transaction_status_csv"]
				,$_FIELD_MAPPING_["_process_source_location_type_csv"]
				,$_FIELD_MAPPING_["_process_source_location_csv"]
				,$_FIELD_MAPPING_["_process_target_transaction_host_csv"]
				,$_FIELD_MAPPING_["_process_target_transaction_host_instance_csv"]
				,$_FIELD_MAPPING_["_process_target_transaction_type_csv"]
				,$_FIELD_MAPPING_["_process_target_transaction_id_csv"]
				,$_FIELD_MAPPING_["_process_target_transaction_title_csv"]
				,$_FIELD_MAPPING_["_process_target_transaction_status_csv"]
				,$_FIELD_MAPPING_["_process_target_location_type_csv"]
				,$_FIELD_MAPPING_["_process_target_location_csv"]
				,$_FIELD_MAPPING_["_ignore_transaction_date_csv"]
				,$_FIELD_MAPPING_["_ignore_source_transaction_host_csv"]
				,$_FIELD_MAPPING_["_ignore_source_transaction_host_instance_csv"]
				,$_FIELD_MAPPING_["_ignore_source_transaction_type_csv"]
				,$_FIELD_MAPPING_["_ignore_source_transaction_id_csv"]
				,$_FIELD_MAPPING_["_ignore_source_transaction_title_csv"]
				,$_FIELD_MAPPING_["_ignore_source_transaction_status_csv"]
				,$_FIELD_MAPPING_["_ignore_source_location_type_csv"]
				,$_FIELD_MAPPING_["_ignore_source_location_csv"]
				,$_FIELD_MAPPING_["_ignore_target_transaction_host_csv"]
				,$_FIELD_MAPPING_["_ignore_target_transaction_host_instance_csv"]
				,$_FIELD_MAPPING_["_ignore_target_transaction_type_csv"]
				,$_FIELD_MAPPING_["_ignore_target_transaction_id_csv"]
				,$_FIELD_MAPPING_["_ignore_target_transaction_title_csv"]
				,$_FIELD_MAPPING_["_ignore_target_transaction_status_csv"]
				,$_FIELD_MAPPING_["_ignore_target_location_type_csv"]
				,$_FIELD_MAPPING_["_ignore_target_location_csv"]
			),
			$errs
		);
		
		// TEST: Date values are valid
		test_date_fields_valid(
			$data,
			array(
				 $_FIELD_MAPPING_["_transaction_date_from"]
				,$_FIELD_MAPPING_["_transaction_date_to"]
				,$_FIELD_MAPPING_["_transaction_created_from"]
				,$_FIELD_MAPPING_["_transaction_created_to"]
				,$_FIELD_MAPPING_["_transaction_updated_from"]
				,$_FIELD_MAPPING_["_transaction_updated_to"]
			),
			$errs
		);		
		
		// TEST: Boolean values are valid
		test_bool_fields_valid(
			$data,
			array(
				 $_FIELD_MAPPING_["_integration_atomic_by_day"]
				,$_FIELD_MAPPING_["_integration_retry_partials"]
			),
			$errs
		);	
		
		test_enum_fields_valid(
			$data,
			array(
				$_FIELD_MAPPING_["_integration_status_outstanding"]
			),
			array(
				"1",
				"0",
				"-1",
				1,
				0,
				-1
			),
			$errs
		);
		
	} catch (Exception $e) {
		array_push($errs, to_error("500", $e->getMessage()));	
	}
	
	return $errs;
}