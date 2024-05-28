<?php

require 'mapping.php';

function validate_get($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_GET_FIELD_MAPPING_;
		global $_ENUM_ORDER_BY_;

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
				 $_GET_FIELD_MAPPING_["_id"]
				,$_GET_FIELD_MAPPING_["_sku"]
				,$_GET_FIELD_MAPPING_["_barcode"]
				,$_GET_FIELD_MAPPING_["_company"]
				,$_GET_FIELD_MAPPING_["_country_code"]
				,$_GET_FIELD_MAPPING_["_dear_instance"]
				,$_GET_FIELD_MAPPING_["_dear_product_id"]
				,$_GET_FIELD_MAPPING_["_dear_sku"]
				,$_GET_FIELD_MAPPING_["_vend_instance"]
				,$_GET_FIELD_MAPPING_["_vend_product_id"]
				,$_GET_FIELD_MAPPING_["_dear_sku"]
			),
			$errs
		);
	
		// TEST: Date values are valid
		test_date_fields_valid(
			$data,
			array(
				 $_GET_FIELD_MAPPING_["_created_from"]
				,$_GET_FIELD_MAPPING_["_created_to"]
				,$_GET_FIELD_MAPPING_["_updated_from"]
				,$_GET_FIELD_MAPPING_["_updated_to"]
			),
			$errs
		);

		// TEST: Boolean values are valid
		test_bool_fields_valid(
			$data,
			array(
				 $_GET_FIELD_MAPPING_["_fuzzy_match"]
				,$_GET_FIELD_MAPPING_["_asc"]
			),
			$errs
		);
		
		// TEST: Boolean values are valid
		test_enum_fields_valid(
			$data,
			array(
				 $_GET_FIELD_MAPPING_["_order_by"]
			),
			$_ENUM_ORDER_BY_,
			$errs
		);
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}
