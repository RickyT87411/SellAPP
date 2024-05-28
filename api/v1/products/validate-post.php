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
					 $_POST_FIELD_MAPPING_["_sku"]
					,$_POST_FIELD_MAPPING_["_company"]
					,$_POST_FIELD_MAPPING_["_country_code"]
					,$_POST_FIELD_MAPPING_["_name"]
					,$_POST_FIELD_MAPPING_["_type"]
					,$_POST_FIELD_MAPPING_["_costing_method"]
					,$_POST_FIELD_MAPPING_["_status"]
				),
				$errs
			);
			// TEST: Mandatory fields contain non-empty data
			test_mandatory_fields_valid(
				$data,
				array(
					 $_POST_FIELD_MAPPING_["_sku"]
					,$_POST_FIELD_MAPPING_["_company"]
					,$_POST_FIELD_MAPPING_["_country_code"]
					,$_POST_FIELD_MAPPING_["_name"]
					,$_POST_FIELD_MAPPING_["_type"]
					,$_POST_FIELD_MAPPING_["_costing_method"]
					,$_POST_FIELD_MAPPING_["_status"]
				),
				$errs
			);

			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_POST_FIELD_MAPPING_["_retail_price"]
					,$_POST_FIELD_MAPPING_["_last_cost_price"]
					,$_POST_FIELD_MAPPING_["_fixed_cost_price"]
					,$_POST_FIELD_MAPPING_["_average_cost_price"]
					,$_POST_FIELD_MAPPING_["_wholesale_price"]
					,$_POST_FIELD_MAPPING_["_length"]
					,$_POST_FIELD_MAPPING_["_width"]
					,$_POST_FIELD_MAPPING_["_height"]
					,$_POST_FIELD_MAPPING_["_weight"]
					,$_POST_FIELD_MAPPING_["_year"]
					,$_POST_FIELD_MAPPING_["_sorting_index"]
				),
				$errs
			);
			
			// TEST: Boolean values are valid
			test_bool_fields_valid(
				$data,
				array(
					 $_POST_FIELD_MAPPING_["_taxable"]
					,$_POST_FIELD_MAPPING_["_licensed"]
					,$_POST_FIELD_MAPPING_["_charitable"]
					,$_POST_FIELD_MAPPING_["_free"]
					,$_POST_FIELD_MAPPING_["_consignment"]
				),
				$errs
			);
		
			// TEST: Date values are valid
			test_date_fields_valid(
				$data,
				array(
					 $_POST_FIELD_MAPPING_["_created_at"]
					,$_POST_FIELD_MAPPING_["_updated_at"]
				),
				$errs
			);
			
			// TEST: Enumerator values are valid
			test_enum_fields_valid(
				$data,
				array(
					$_POST_FIELD_MAPPING_["_type"]
				),
				array(
					 "stock"
					,"service"
					,"noninventory"
					,"asset"
					,"modifier"
					,"other"
				),
				$errs
			);
			
			// TEST: Enumerator values are valid
			test_enum_fields_valid(
				$data,
				array(
					$_POST_FIELD_MAPPING_["_costing_method"]
				),
				array(
					 "FIFO"
					,"FEFO"
					,"average"
					,"serial"
				),
				$errs
			);
			
			// TEST: Enumerator values are valid
			test_enum_fields_valid(
				$data,
				array(
					$_POST_FIELD_MAPPING_["_status"]
				),
				array(
					 "active"
					,"deprecated"
					,"setup"
					,"hold"
				),
				$errs
			);
		}	
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}