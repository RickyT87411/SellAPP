<?php
require 'mapping.php';

function validate_post($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_TLOG_AGENT_DATASET_FETCH_DELTA_OBJECT_;
		global $_ENUM_TLOG_AGENT_DATASET_TYPE_;

		$map = $_TLOG_AGENT_DATASET_FETCH_DELTA_OBJECT_;
		$datasets = $_ENUM_TLOG_AGENT_DATASET_TYPE_;

		// Default test for input
		if($body == null || $body === "") {
			array_push($errs, to_error(400, "Missing JSON request body"));
			return $errs;
		}
		// Convert to JSON
   	 	$data = json_decode($body, true);
   	 	$tree_path = "$";
   	 	
   	 	// TEST: Valid JSON format
		if(json_last_error() != JSON_ERROR_NONE) {
			array_push($errs, to_error(400, "Invalid JSON format for request body"));
			return $errs;
		}
		
		// TEST: Empty JSON
		if(empty($data)) {
			return $errs;
		}
		
		// TEST: String values are valid
		test_mandatory_fields_set(
			$data,
			array(
				 $map["_session_id"]
				,$map["_tlog_dataset_type"]
			),
			$errs,
			$tree_path
		);
		
		// TEST: String values are valid
		test_string_fields_valid(
			$data,
			array(
				 $map["_session_id"]
			),
			$errs,
			$tree_path
		);
		
		// TEST: Enumerator values are valid
		test_enum_fields_valid(
			$data,
			array(
				 $map["_tlog_dataset_type"]
			),
			$datasets,
			$errs,
			$tree_path
		);

	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}