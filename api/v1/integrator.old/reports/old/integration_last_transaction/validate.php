<?php

require 'mapping.php';

function validate($data) { 
	$errs = array();
	try {	 	
		// TEST: String values are valid
		test_string_fields_valid(
			$data,
			array(
				 "_lhs_host"
				,"_lhs_instance"
				,"_lhs_transaction_type"
				,"_lhs_transaction_status"
				,"_rhs_host"
				,"_rhs_instance"
				,"_rhs_transaction_type"
				,"_rhs_transaction_status"
				,"_broker"
				,"_include_failures"
			),
			$errs
		);
		
		// TEST: Date values are valid
		test_numeric_fields_valid(
			$data,
			array(
				 "_expiry_in_seconds"
			),
			$errs
		);		
	} catch (Exception $e) {
		array_push($errs, to_error("500", $e->getMessage()));	
	}
	
	return $errs;
}
