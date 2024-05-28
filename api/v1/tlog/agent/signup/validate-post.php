<?php
require 'mapping.php';

function validate_post($body = null) { 
	$errs = array();
	try {
		// Import Field Mapping
		global $_TLOG_AGENT_SIGNUP_OBJECT_;
		
		$map = $_TLOG_AGENT_SIGNUP_OBJECT_;

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

		// TEST: String values are valid
		test_mandatory_fields_set(
			$data,
			array(
				 $map["_username"]
				,$map["_passphrase"]
			),
			$errs,
			$tree_path
		);
		
		// TEST: String values are valid
		test_string_fields_valid(
			$data,
			array(
				 $map["_username"]
				,$map["_passphrase"]
			),
			$errs,
			$tree_path
		);
		
		// TEST: Email values are valid
		test_email_fields_valid(
			$data,
			array(
				 $map["_username"]
			),
			$errs,
			$tree_path
		);

	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}