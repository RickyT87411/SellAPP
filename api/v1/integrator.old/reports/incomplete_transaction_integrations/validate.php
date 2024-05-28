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
				,"_broker_job_id"
				,"_broker_job_instance_id"
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

function test_mandatory_fields_set($data, $fields, &$errs) {
	foreach($fields as $field)
		if(!array_key_exists($field, $data))
			array_push($errs, to_error(400, "Mandatory '".$field."' attribute has not been set"));
}

function test_mandatory_fields_valid($data, $fields, &$errs) {
	foreach($fields as $field)
		if(array_key_exists($field, $data) && !$data[$field])
			array_push($errs, to_error(400, "Mandatory '".$field."' attribute value cannot be blank"));	
}

function test_string_fields_valid($data, $fields, &$errs) {
	foreach($fields as $field)
		if(array_key_exists($field, $data) && $data[$field] != null && strcmp($data[$field],"") == 0)
			array_push($errs, to_error(400, "'".$field."' attribute value cannot be blank"));	
}

function test_numeric_fields_valid($data, $fields, &$errs) {
	foreach($fields as $field)
		if(array_key_exists($field, $data) && $data[$field] != null && !is_numeric($data[$field]))
			array_push($errs, to_error(400, "'".$field."' attribute value must be numeric"));	
}

function test_date_fields_valid($data, $fields, &$errs) {
	foreach($fields as $field)
		if(array_key_exists($field, $data)) {
			$d = DateTime::createFromFormat('Y-m-d H:i:s', $data[$field]);	
			if(!$d || $d->format('Y-m-d H:i:s') != $data[$field])
				array_push($errs, to_error(400, "'".$field."' attribute value must be a valid date of the format yyyy-mm-dd HH:mm:ss"));
		}
}

function test_enum_fields_valid($data, $fields, $enum, &$errs) {
	foreach($fields as $field)
		if(array_key_exists($field, $data) && !in_array($data[$field], $enum, TRUE))
			array_push($errs, to_error(400, "'".$field."' attribute value must be one of [".implode(", ",$enum)."]"));
}

function to_error($code, $message) {
	return array(
		"ErrorCode"	=>	$code,
		"Exception"	=>	$message
	);
}

?>