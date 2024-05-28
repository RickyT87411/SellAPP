<?php
require 'mapping.php';

function validate_get($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_GET_FIELD_MAPPING_;
		global $_ENUM_TLOG_HEADER_TYPE_;

		// Default test for input
		if($body == null || $body === "") {
			array_push($errs, to_error(400, "Missing JSON request body"));
			return $errs;
		}
		// Convert to JSON
   	 	$data = json_decode($body, true);
   	 	$tree_path = "$.query";
   	 	
   	 	// TEST: Valid JSON format
		if(json_last_error() != JSON_ERROR_NONE) {
			array_push($errs, to_error(400, "Invalid JSON format for request body"));
			return $errs;
		}
		
		// TEST: String values are valid
		test_string_fields_valid(
			$data,
			array(
				 $_GET_FIELD_MAPPING_["_process_transaction_date_csv"]
				,$_GET_FIELD_MAPPING_["_process_transaction_channel_csv"]
				,$_GET_FIELD_MAPPING_["_process_transaction_source_csv"]
				,$_GET_FIELD_MAPPING_["_process_transaction_source_instance_csv"]
				,$_GET_FIELD_MAPPING_["_process_transaction_type_csv"]
				,$_GET_FIELD_MAPPING_["_process_transaction_id_csv"]
				,$_GET_FIELD_MAPPING_["_process_transaction_title_csv"]
				,$_GET_FIELD_MAPPING_["_process_location_code_csv"]
				,$_GET_FIELD_MAPPING_["_process_outlet_code_csv"]
				,$_GET_FIELD_MAPPING_["_process_register_code_csv"]
				,$_GET_FIELD_MAPPING_["_ignore_transaction_date_csv"]
				,$_GET_FIELD_MAPPING_["_ignore_transaction_channel_csv"]
				,$_GET_FIELD_MAPPING_["_ignore_transaction_source_csv"]
				,$_GET_FIELD_MAPPING_["_ignore_transaction_source_instance_csv"]
				,$_GET_FIELD_MAPPING_["_ignore_transaction_type_csv"]
				,$_GET_FIELD_MAPPING_["_ignore_transaction_id_csv"]
				,$_GET_FIELD_MAPPING_["_ignore_transaction_title_csv"]
				,$_GET_FIELD_MAPPING_["_ignore_location_code_csv"]
				,$_GET_FIELD_MAPPING_["_ignore_outlet_code_csv"]
				,$_GET_FIELD_MAPPING_["_ignore_register_code_csv"]
			),
			$errs,
			$tree_path
		);
		// TEST: Enum fields
		test_enum_fields_valid(
			$data,
			array(
				 $_GET_FIELD_MAPPING_["_tlog_header_type"]
			),
			$_ENUM_TLOG_HEADER_TYPE_,
			$errs,
			$tree_path
		);
		// TEST: Date values are valid
		test_date_fields_valid(
			$data,
			array(
				 $_GET_FIELD_MAPPING_["_transaction_date_from"]
				,$_GET_FIELD_MAPPING_["_transaction_date_to"]
				,$_GET_FIELD_MAPPING_["_transaction_updated_from"]
				,$_GET_FIELD_MAPPING_["_transaction_updated_to"]
			),
			$errs,
			$tree_path
		);
		// TEST: Boolean values are valid
		test_bool_fields_valid(
			$data,
			array(
				 $_GET_FIELD_MAPPING_["_in_sync"]
				,$_GET_FIELD_MAPPING_["_asc"]
			),
			$errs,
			$tree_path
		);

	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}