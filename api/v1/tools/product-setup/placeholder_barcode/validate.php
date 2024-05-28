<?php

require 'mapping.php';

function validate($body = null) { 
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
					 $_FIELD_MAPPING_["_barcode"]
					,$_FIELD_MAPPING_["_barcode_type"]
					,$_FIELD_MAPPING_["_assigned_title"]
					,$_FIELD_MAPPING_["_used_system"]
					,$_FIELD_MAPPING_["_assigned_by"]
					,$_FIELD_MAPPING_["_assigned_at"]
				),
				$errs
			);
			// TEST: Mandatory fields contain non-empty data
			test_mandatory_fields_valid(
				$data,
				array(
					 $_FIELD_MAPPING_["_barcode"]
					,$_FIELD_MAPPING_["_barcode_type"]
					,$_FIELD_MAPPING_["_assigned_title"]
					,$_FIELD_MAPPING_["_used_system"]
					,$_FIELD_MAPPING_["_assigned_by"]
					,$_FIELD_MAPPING_["_assigned_at"]
				),
				$errs
			);
		
			// TEST: Date values are valid
			test_date_fields_valid(
				$data,
				array(
					$_FIELD_MAPPING_["_assigned_at"]
				),
				$errs
			);	
		}	
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}

function test_mandatory_fields_set($data, $fields, &$errs) {
	try {
		foreach($fields as $field)
			if(!array_key_exists($field, $data))
				array_push($errs, to_error(400, "Mandatory '".$field."' attribute has not been set"));
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function test_mandatory_fields_valid($data, $fields, &$errs) {
	try {
		foreach($fields as $field)
			if(array_key_exists($field, $data) && !$data[$field])
				array_push($errs, to_error(400, "Mandatory '".$field."' attribute value cannot be blank"));
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function test_string_fields_valid($data, $fields, &$errs) {
	try {
		foreach($fields as $field)
			if(array_key_exists($field, $data) && !$data[$field])
				array_push($errs, to_error(400, "'".$field."' attribute value cannot be blank"));
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function test_numeric_fields_valid($data, $fields, &$errs) {
	try {
		foreach($fields as $field)
			if(array_key_exists($field, $data) && !is_numeric($data[$field]))
				array_push($errs, to_error(400, "'".$field."' attribute value must be numeric and not null"));
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function test_date_fields_valid($data, $fields, &$errs) {
	try {
		foreach($fields as $field)
			if(array_key_exists($field, $data)) {
				$d = DateTime::createFromFormat('Y-m-d H:i:s', $data[$field], new DateTimeZone("Australia/Sydney"));	
				if(!$d || $d->format('Y-m-d H:i:s') != $data[$field])
					array_push($errs, to_error(400, "'".$field."' attribute value must be a valid date of the format yyyy-mm-dd HH:mm:ss"));
			}
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function test_enum_fields_valid($data, $fields, $enum, &$errs) {
	try {
		foreach($fields as $field)
			if(array_key_exists($field, $data) && !in_array($data[$field], $enum, TRUE))
				array_push($errs, to_error(400, "'".$field."' attribute value must be one of [".implode(", ",$enum)."]"));
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function to_error($code, $message) {
	return array(
		"ErrorCode"	=>	$code,
		"Exception"	=>	$message
	);
}
