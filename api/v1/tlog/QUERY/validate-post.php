<?php
require 'mapping.php';

function validate_post($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_POST_FIELD_MAPPING_;
		global $_ENUM_QUERY_TLOG_HEADER_TYPE_;
		global $_ENUM_QUERY_STATUS_;

		// Default test for input
		if(!$body) {
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
				 $_POST_FIELD_MAPPING_["_process_transaction_date_csv"]
				,$_POST_FIELD_MAPPING_["_process_transaction_channel_csv"]
				,$_POST_FIELD_MAPPING_["_process_transaction_source_csv"]
				,$_POST_FIELD_MAPPING_["_process_transaction_source_instance_csv"]
				,$_POST_FIELD_MAPPING_["_process_transaction_type_csv"]
				,$_POST_FIELD_MAPPING_["_process_transaction_id_csv"]
				,$_POST_FIELD_MAPPING_["_process_transaction_title_csv"]
				,$_POST_FIELD_MAPPING_["_process_transaction_status_csv"]
				,$_POST_FIELD_MAPPING_["_process_location_type_csv"]
				,$_POST_FIELD_MAPPING_["_process_location_code_csv"]
				,$_POST_FIELD_MAPPING_["_process_outlet_type_csv"]
				,$_POST_FIELD_MAPPING_["_process_outlet_code_csv"]
				,$_POST_FIELD_MAPPING_["_process_register_type_csv"]
				,$_POST_FIELD_MAPPING_["_process_register_code_csv"]
				,$_POST_FIELD_MAPPING_["_process_customer_type_csv"]
				,$_POST_FIELD_MAPPING_["_process_customer_code_csv"]
				,$_POST_FIELD_MAPPING_["_process_supplier_type_csv"]
				,$_POST_FIELD_MAPPING_["_process_supplier_code_csv"]
				,$_POST_FIELD_MAPPING_["_process_user_code_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_transaction_date_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_transaction_channel_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_transaction_source_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_transaction_source_instance_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_transaction_type_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_transaction_id_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_transaction_title_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_transaction_status_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_location_type_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_location_code_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_outlet_type_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_outlet_code_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_register_type_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_register_code_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_customer_type_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_customer_code_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_supplier_type_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_supplier_code_csv"]
				,$_POST_FIELD_MAPPING_["_ignore_user_code_csv"]
			),
			$errs,
			$tree_path
		);
		// TEST: Boolean values are valid
		test_bool_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_include_audit_trail"]
				,$_POST_FIELD_MAPPING_["_asc"]
			),
			$errs
		);
		// TEST: Date values are valid
		test_date_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_transaction_date_from"]
				,$_POST_FIELD_MAPPING_["_transaction_date_to"]
				,$_POST_FIELD_MAPPING_["_transaction_created_from"]
				,$_POST_FIELD_MAPPING_["_transaction_created_to"]
				,$_POST_FIELD_MAPPING_["_transaction_updated_from"]
				,$_POST_FIELD_MAPPING_["_transaction_updated_to"]
				,$_POST_FIELD_MAPPING_["_since"]
			),
			$errs
		);
		// TEST: Enumerator values are valid
		test_enum_fields_valid(
			$data,
			array(
				$_POST_FIELD_MAPPING_["_tlog_header_type"]
			),
			$_ENUM_QUERY_TLOG_HEADER_TYPE_,
			$errs
		);
		
		// TEST: Enumerator values are valid
		test_enum_fields_valid(
			$data,
			array(
				$_POST_FIELD_MAPPING_["_status"]
			),
			$_ENUM_QUERY_STATUS_,
			$errs
		);
		// TEST: Order By value is valid
		test_enum_fields_valid(
			$data,
			array(
				 $_POST_FIELD_MAPPING_["_order_by"]
			),
			$_ORDER_BY_ENUM,
			$errs
		);
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}