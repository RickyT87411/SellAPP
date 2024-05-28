<?php
require 'mapping.php';

function validate_tlog_header_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_TLOG_HEADER_OBJECT_;
		global $_ENUM_TLOG_HEADER_TYPE_;
		global $_ENUM_HEADER_CLASS_TYPE_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".header";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: Mandatory fields are set
			test_mandatory_fields_set(
				$data,
				array(
					 $_TLOG_HEADER_OBJECT_["_tlog_header_type"]
					,$_TLOG_HEADER_OBJECT_["_id"]
					,$_TLOG_HEADER_OBJECT_["_channel"]
					,$_TLOG_HEADER_OBJECT_["_source"]
					,$_TLOG_HEADER_OBJECT_["_source_instance"]
					,$_TLOG_HEADER_OBJECT_["_type"]
					,$_TLOG_HEADER_OBJECT_["_status"]
					,$_TLOG_HEADER_OBJECT_["_reference"]
				),
				$errs,
				$tree_path
			);
			// TEST: Mandatory fields are set
			test_mandatory_fields_valid(
				$data,
				array(
					 $_TLOG_HEADER_OBJECT_["_id"]
					,$_TLOG_HEADER_OBJECT_["_channel"]
					,$_TLOG_HEADER_OBJECT_["_source"]
					,$_TLOG_HEADER_OBJECT_["_source_instance"]
					,$_TLOG_HEADER_OBJECT_["_type"]
					,$_TLOG_HEADER_OBJECT_["_status"]
					,$_TLOG_HEADER_OBJECT_["_reference"]
				),
				$errs,
				$tree_path
			);
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_TLOG_HEADER_OBJECT_["_id"]
					,$_TLOG_HEADER_OBJECT_["_grouping_id"]
					,$_TLOG_HEADER_OBJECT_["_channel"]
					,$_TLOG_HEADER_OBJECT_["_source"]
					,$_TLOG_HEADER_OBJECT_["_source_instance"]
					,$_TLOG_HEADER_OBJECT_["_type"]
					,$_TLOG_HEADER_OBJECT_["_status"]
					,$_TLOG_HEADER_OBJECT_["_reference"]
					,$_TLOG_HEADER_OBJECT_["_category"]
					,$_TLOG_HEADER_OBJECT_["_return_for_tlog_id"]
					,$_TLOG_HEADER_OBJECT_["_return_for_transaction_id"]
					,$_TLOG_HEADER_OBJECT_["_return_for_transaction_reference"]
					,$_TLOG_HEADER_OBJECT_["_timezone"]
					,$_TLOG_HEADER_OBJECT_["_currency_from"]
					,$_TLOG_HEADER_OBJECT_["_currency_to"]
					,$_TLOG_HEADER_OBJECT_["_note"]
				),
				$errs,
				$tree_path
			);
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_TLOG_HEADER_OBJECT_["_exchange_rate"]
					,$_TLOG_HEADER_OBJECT_["_total_quantity_in"]
					,$_TLOG_HEADER_OBJECT_["_total_quantity_out"]
					,$_TLOG_HEADER_OBJECT_["_total_amount"]
					,$_TLOG_HEADER_OBJECT_["_total_price"]
					,$_TLOG_HEADER_OBJECT_["_total_discount"]
					,$_TLOG_HEADER_OBJECT_["_total_cost"]
					,$_TLOG_HEADER_OBJECT_["_subtotal"]
					,$_TLOG_HEADER_OBJECT_["_tax"]
					,$_TLOG_HEADER_OBJECT_["_total_tender_amount"]
					,$_TLOG_HEADER_OBJECT_["_global_discount"]
				),
				$errs,
				$tree_path
			);
			// TEST: Date values are valid
			test_date_fields_valid(
				$data,
				array(
					 $_TLOG_HEADER_OBJECT_["_date"]
					,$_TLOG_HEADER_OBJECT_["_required_by"]
					,$_TLOG_HEADER_OBJECT_["_fullfilled_at"]
					,$_TLOG_HEADER_OBJECT_["_created_at"]
					,$_TLOG_HEADER_OBJECT_["_updated_at"]
					,$_TLOG_HEADER_OBJECT_["_exchange_rate_at"]
				),
				$errs,
				$tree_path
			);
			// TEST: Boolean values are valid
			test_bool_fields_valid(
				$data,
				array(
					 $_TLOG_HEADER_OBJECT_["_update_if_exists"]
				),
				$errs,
				$tree_path
			);
			// TEST: Enumerator values are valid
			test_enum_fields_valid(
				$data,
				array(
					 $_TLOG_HEADER_OBJECT_["_tlog_header_type"]
				),
				$_ENUM_TLOG_HEADER_TYPE_,
				$errs,
				$tree_path
			);
			// TEST: Enumerator values are valid
			test_enum_fields_valid(
				$data,
				array(
					 $_TLOG_HEADER_OBJECT_["_class"]
				),
				$_ENUM_HEADER_CLASS_TYPE_,
				$errs,
				$tree_path
			);
			// TEST: Object fields are valid
			$customer = validate_customer_object(
				$data[$_TLOG_HEADER_OBJECT_["_customer_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$user = validate_user_object(
				$data[$_TLOG_HEADER_OBJECT_["_user_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$supplier = validate_supplier_object(
				$data[$_TLOG_HEADER_OBJECT_["_supplier_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$location = validate_location_object(
				$data[$_TLOG_HEADER_OBJECT_["_location_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$outlet = validate_outlet_object(
				$data[$_TLOG_HEADER_OBJECT_["_outlet_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$register = validate_register_object(
				$data[$_TLOG_HEADER_OBJECT_["_register_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$transfer = validate_transfer_object(
				$data[$_TLOG_HEADER_OBJECT_["_transfer_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$dispatch = validate_dispatch_object(
				$data[$_TLOG_HEADER_OBJECT_["_dispatch_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$lines = validate_tlog_line_objects(
				$data[$_TLOG_HEADER_OBJECT_["_tlog_lines_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$tenders = validate_tlog_tender_objects(
				$data[$_TLOG_HEADER_OBJECT_["_tlog_tenders_object"]], 
				$tree_path, 
				$errs		
			);
			return $customer && $user && $supplier && $location && $outlet 
				&& $register && $transfer && $dispatch && $lines && $tenders;
		}
		return FALSE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_tlog_line_objects($data = null, $tree_path, &$errs){
	try {
		// Append current tree path to parent node
		$tree_path = $tree_path . ".lines";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// Test if object is an associative array or sequential
			if(is_assoc_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path : addslashes($data))."] must be an Array of JSON Objects"));
				return FALSE;
			}
			
			// Element reference for Array
			$i = 0;
			// Test each Object within the array
			foreach( $data as $obj ) {
				// TEST: Validate object's test condition
				$line = validate_tlog_line_object(
					$obj,
					$tree_path."[".$i."]",
					$errs
				);
				// RESULT: Fail if any Object is invalid
				if(!$line)
					return FALSE;
					
				$i++;
			}
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_tlog_line_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_TLOG_LINE_OBJECT_;
		global $_ENUM_TLOG_LINE_TYPE_;
		global $_ENUM_CONSIGNMENT_TYPE_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".line";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: Mandatory fields are set
			test_mandatory_fields_set(
				$data,
				array(
					 $_TLOG_LINE_OBJECT_["_tlog_line_type"]
					,$_TLOG_LINE_OBJECT_["_id"]
					,$_TLOG_LINE_OBJECT_["_status"]
				),
				$errs,
				$tree_path
			);
			// TEST: Mandatory fields are set
			test_mandatory_fields_valid(
				$data,
				array(
					 $_TLOG_LINE_OBJECT_["_id"]
					,$_TLOG_LINE_OBJECT_["_status"]
				),
				$errs,
				$tree_path
			);
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_TLOG_LINE_OBJECT_["_id"]
					,$_TLOG_LINE_OBJECT_["_grouping_id"]
					,$_TLOG_LINE_OBJECT_["_reference"]
					,$_TLOG_LINE_OBJECT_["_status"]
					,$_TLOG_LINE_OBJECT_["_note"]
				),
				$errs,
				$tree_path
			);
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_TLOG_LINE_OBJECT_["_sequence"]
					,$_TLOG_LINE_OBJECT_["_amount"]
					,$_TLOG_LINE_OBJECT_["_unit_cost"]
					,$_TLOG_LINE_OBJECT_["_unit_price"]
					,$_TLOG_LINE_OBJECT_["_unit_discount"]
					,$_TLOG_LINE_OBJECT_["_unit_tax"]
					,$_TLOG_LINE_OBJECT_["_quantity"]
					,$_TLOG_LINE_OBJECT_["_cost"]
					,$_TLOG_LINE_OBJECT_["_price"]
					,$_TLOG_LINE_OBJECT_["_discount"]
					,$_TLOG_LINE_OBJECT_["_tax"]
					,$_TLOG_LINE_OBJECT_["_subtotal"]
					,$_TLOG_LINE_OBJECT_["_total"]
					,$_TLOG_LINE_OBJECT_["_quantity_ordered"]
					,$_TLOG_LINE_OBJECT_["_quantity_picked"]
					,$_TLOG_LINE_OBJECT_["_quantity_packed"]
					,$_TLOG_LINE_OBJECT_["_quantity_fulfilled"]
					,$_TLOG_LINE_OBJECT_["_quantity_received"]
				),
				$errs,
				$tree_path
			);
			// TEST: Date values are valid
			test_date_fields_valid(
				$data,
				array(
					 $_TLOG_LINE_OBJECT_["_required_by"]
				),
				$errs,
				$tree_path
			);
			// TEST: Boolean values are valid
			test_bool_fields_valid(
				$data,
				array(
					 $_TLOG_LINE_OBJECT_["_charitable"]
					,$_TLOG_LINE_OBJECT_["_free"]
				),
				$errs,
				$tree_path
			);
			// TEST: Enumerator values are valid
			test_enum_fields_valid(
				$data,
				array(
					 $_TLOG_LINE_OBJECT_["_tlog_line_type"]
				),
				$_ENUM_TLOG_LINE_TYPE_,
				$errs,
				$tree_path
			);
			// TEST: Enumerator values are valid
			test_enum_fields_valid(
				$data,
				array(
					 $_TLOG_LINE_OBJECT_["_consignment_type"]
				),
				$_ENUM_CONSIGNMENT_TYPE_,
				$errs,
				$tree_path
			);
			// TEST: Object fields are valid
			$product = validate_product_object(
				$data[$_TLOG_LINE_OBJECT_["_product_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$discount = validate_discount_reason_object(
				$data[$_TLOG_LINE_OBJECT_["_discount_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$return = validate_return_reason_object(
				$data[$_TLOG_LINE_OBJECT_["_return_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$void = validate_void_reason_object(
				$data[$_TLOG_LINE_OBJECT_["_void_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$promotion = validate_promotion_object(
				$data[$_TLOG_LINE_OBJECT_["_promotion_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$transfer = validate_transfer_object(
				$data[$_TLOG_LINE_OBJECT_["_transfer_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$dispatch = validate_promotion_object(
				$data[$_TLOG_LINE_OBJECT_["_dispatch_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$royalty = validate_royalty_object(
				$data[$_TLOG_LINE_OBJECT_["_royalty_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$commission = validate_commission_object(
				$data[$_TLOG_LINE_OBJECT_["_commission_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$profit_share = validate_profit_share_object(
				$data[$_TLOG_LINE_OBJECT_["_profit_share_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$finance = validate_tlog_finance_objects(
				$data[$_TLOG_LINE_OBJECT_["_finance_objects"]], 
				$tree_path, 
				$errs		
			);
			return $product && $discount && $return && $void && $promotion && $dispatch 
				&& $royalty && $commission && $profit_share && $finance;
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_tlog_tender_objects($data = null, $tree_path, &$errs){
	try {
		// Append current tree path to parent node
		$tree_path = $tree_path . ".tenders";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// Test if object is an associative array or sequential
			if(is_assoc_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path : addslashes($data))."] must be an Array of JSON Objects"));
				return FALSE;
			}
			
			// Element reference for Array
			$i = 0;
			// Test each Object within the array
			foreach( $data as $obj ) {
				// TEST: Validate object's test condition
				$tender = validate_tlog_tender_object(
					$obj,
					$tree_path."[".$i."]",
					$errs
				);
				// RESULT: Fail if any Object is invalid
				if(!$tender)
					return FALSE;
					
				$i++;
			}
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_tlog_tender_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_TLOG_TENDER_OBJECT_;
		global $_ENUM_TLOG_TENDER_TYPE_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".line";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: Mandatory fields are set
			test_mandatory_fields_set(
				$data,
				array(
					 $_TLOG_TENDER_OBJECT_["_tlog_tender_type"]
					,$_TLOG_TENDER_OBJECT_["_id"]
					,$_TLOG_TENDER_OBJECT_["_status"]
				),
				$errs,
				$tree_path
			);
			// TEST: Mandatory fields are set
			test_mandatory_fields_valid(
				$data,
				array(
					 $_TLOG_TENDER_OBJECT_["_id"]
					,$_TLOG_TENDER_OBJECT_["_status"]
				),
				$errs,
				$tree_path
			);
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_TLOG_TENDER_OBJECT_["_id"]
					,$_TLOG_TENDER_OBJECT_["_grouping_id"]
					,$_TLOG_TENDER_OBJECT_["_reference"]
					,$_TLOG_TENDER_OBJECT_["_status"]
					,$_TLOG_TENDER_OBJECT_["_note"]
				),
				$errs,
				$tree_path
			);
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_TLOG_TENDER_OBJECT_["_sequence"]
					,$_TLOG_TENDER_OBJECT_["_amount"]
				),
				$errs,
				$tree_path
			);
			// TEST: Enumerator values are valid
			test_enum_fields_valid(
				$data,
				array(
					 $_TLOG_TENDER_OBJECT_["_tlog_tender_type"]
				),
				$_ENUM_TLOG_TENDER_TYPE_,
				$errs,
				$tree_path
			);
			// TEST: Object fields are valid
			$surcharge = validate_surcharge_object(
				$data[$_TLOG_TENDER_OBJECT_["_surcharge_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$fee = validate_fee_object(
				$data[$_TLOG_TENDER_OBJECT_["_fee_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$tender = validate_tender_detail_object(
				$data[$_TLOG_TENDER_OBJECT_["_tender_detail_object"]], 
				$tree_path, 
				$errs		
			);
			// TEST: Object fields are valid
			$finance = validate_tlog_finance_objects(
				$data[$_TLOG_TENDER_OBJECT_["_finance_objects"]], 
				$tree_path, 
				$errs		
			);
			
			return $surcharge && $fee && $tender;
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_tlog_finance_objects($data = null, $tree_path, &$errs){
	try {
		// Append current tree path to parent node
		$tree_path = $tree_path . ".finance_objects";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// Test if object is an associative array or sequential
			if(is_assoc_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path : addslashes($data))."] must be an Array of JSON Objects"));
				return FALSE;
			}
			
			// Element reference for Array
			$i = 0;
			// Test each Object within the array
			foreach( $data as $obj ) {
				// TEST: Validate object's test condition
				$finance = validate_tlog_finance_object(
					$obj,
					$tree_path."[".$i."]",
					$errs
				);
				// RESULT: Fail if any Object is invalid
				if(!$finance)
					return FALSE;
					
				$i++;
			}
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_tlog_finance_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_TLOG_FINANCE_OBJECT_;

		// Append current tree path to parent node
		$tree_path = $tree_path . ".finance_object";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: Mandatory fields are set
			test_mandatory_fields_set(
				$data,
				array(
					 $_TLOG_FINANCE_OBJECT_["_sequence"]
				),
				$errs,
				$tree_path
			);
			// TEST: Mandatory fields are set
			test_mandatory_fields_valid(
				$data,
				array(
					 $_TLOG_FINANCE_OBJECT_["_sequence"]
				),
				$errs,
				$tree_path
			);
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_TLOG_FINANCE_OBJECT_["_sequence"]
					,$_TLOG_FINANCE_OBJECT_["_financial_id"]
					,$_TLOG_FINANCE_OBJECT_["_financial_reference"]
					,$_TLOG_FINANCE_OBJECT_["_financial_type"]
					,$_TLOG_FINANCE_OBJECT_["_financial_entity"]
					,$_TLOG_FINANCE_OBJECT_["_financial_location"]
					,$_TLOG_FINANCE_OBJECT_["_financial_business_unit"]
					,$_TLOG_FINANCE_OBJECT_["_financial_division"]
					,$_TLOG_FINANCE_OBJECT_["_account_id"]
					,$_TLOG_FINANCE_OBJECT_["_account_type"]
					,$_TLOG_FINANCE_OBJECT_["_account_sub_type"]
					,$_TLOG_FINANCE_OBJECT_["_account_code"]
					,$_TLOG_FINANCE_OBJECT_["_account_currency"]
					,$_TLOG_FINANCE_OBJECT_["_account_title"]
					,$_TLOG_FINANCE_OBJECT_["_account_division"]
					,$_TLOG_FINANCE_OBJECT_["_creditor_id"]
					,$_TLOG_FINANCE_OBJECT_["_creditor_code"]
					,$_TLOG_FINANCE_OBJECT_["_creditor_title"]
					,$_TLOG_FINANCE_OBJECT_["_creditor_type"]
					,$_TLOG_FINANCE_OBJECT_["_creditor_account_payable_id"]
					,$_TLOG_FINANCE_OBJECT_["_creditor_account_payable_code"]
					,$_TLOG_FINANCE_OBJECT_["_debtor_id"]
					,$_TLOG_FINANCE_OBJECT_["_debtor_code"]
					,$_TLOG_FINANCE_OBJECT_["_debtor_title"]
					,$_TLOG_FINANCE_OBJECT_["_debtor_type"]
					,$_TLOG_FINANCE_OBJECT_["_debtor_account_receivable_id"]
					,$_TLOG_FINANCE_OBJECT_["_debtor_account_receivable_code"]
					,$_TLOG_FINANCE_OBJECT_["_bank_id"]
					,$_TLOG_FINANCE_OBJECT_["_bank_type"]
					,$_TLOG_FINANCE_OBJECT_["_bank_code"]
					,$_TLOG_FINANCE_OBJECT_["_bank_title"]
					,$_TLOG_FINANCE_OBJECT_["_bank_account_number"]
					,$_TLOG_FINANCE_OBJECT_["_description"]
					,$_TLOG_FINANCE_OBJECT_["_note"]
				),
				$errs,
				$tree_path
			);
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_TLOG_FINANCE_OBJECT_["_debit"]
					,$_TLOG_FINANCE_OBJECT_["_credit"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_user_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_USER_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".user";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_USER_OBJECT_["_id"]
					,$_USER_OBJECT_["_code"]
					,$_USER_OBJECT_["_title"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function validate_customer_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_CUSTOMER_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".customer";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_CUSTOMER_OBJECT_["_id"]
					,$_CUSTOMER_OBJECT_["_code"]
					,$_CUSTOMER_OBJECT_["_title"]
					,$_CUSTOMER_OBJECT_["_type"]
				),
				$errs,
				$tree_path
			);
			// TEST: Object fields are valid
			test_object_fields_valid(
				$data,
				array(
					$_CUSTOMER_OBJECT_["_finance_object"]
				),
				$errs,
				$tree_path
			);
			return validate_tlog_finance_object(
				$data[$_CUSTOMER_OBJECT_["_finance_object"]], 
				$tree_path, 
				$errs		
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_supplier_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_SUPPLIER_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".supplier";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_SUPPLIER_OBJECT_["_id"]
					,$_SUPPLIER_OBJECT_["_code"]
					,$_SUPPLIER_OBJECT_["_title"]
					,$_SUPPLIER_OBJECT_["_type"]
				),
				$errs,
				$tree_path
			);
			// TEST: Object fields are valid
			test_object_fields_valid(
				$data,
				array(
					$_SUPPLIER_OBJECT_["_finance_object"]
				),
				$errs,
				$tree_path
			);
			return validate_tlog_finance_object(
				$data[$_SUPPLIER_OBJECT_["_finance_object"]], 
				$tree_path, 
				$errs		
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_location_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_LOCATION_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".location";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_LOCATION_OBJECT_["_id"]
					,$_LOCATION_OBJECT_["_code"]
					,$_LOCATION_OBJECT_["_title"]
					,$_LOCATION_OBJECT_["_type"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_outlet_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_OUTLET_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".outlet";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_OUTLET_OBJECT_["_id"]
					,$_OUTLET_OBJECT_["_code"]
					,$_OUTLET_OBJECT_["_title"]
					,$_OUTLET_OBJECT_["_type"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_register_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_REGISTER_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".register";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_REGISTER_OBJECT_["_id"]
					,$_REGISTER_OBJECT_["_code"]
					,$_REGISTER_OBJECT_["_title"]
					,$_REGISTER_OBJECT_["_type"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_transfer_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_TRANSFER_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".transfer";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: Validate object's test condition
			$source = validate_location_object(
				$data[$_TRANSFER_OBJECT_["_source_location_object"]],
				$tree_path,
				$errs
			);
			// TEST: Validate object's test condition
			$target = validate_location_object(
				$data[$_TRANSFER_OBJECT_["_target_location_object"]],
				$tree_path,
				$errs
			);
			// RESULT: Only true if all objects are valid
			return $source && $target;
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_product_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_PRODUCT_OBJECT_;
		global $_ENUM_PRODUCT_CLASS_TYPE_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".product";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: Mandatory fields are set
			test_mandatory_fields_set(
				$data,
				array(
					 $_PRODUCT_OBJECT_["_sku"]
				),
				$errs,
				$tree_path
			);
			// TEST: Mandatory fields are set
			test_mandatory_fields_valid(
				$data,
				array(
					 $_PRODUCT_OBJECT_["_sku"]
				),
				$errs,
				$tree_path
			);
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_PRODUCT_OBJECT_["_inventory_id"]
					,$_PRODUCT_OBJECT_["_stock_locator_id"]
					,$_PRODUCT_OBJECT_["_id"]
					,$_PRODUCT_OBJECT_["_sku"]
					,$_PRODUCT_OBJECT_["_barcode"]
					,$_PRODUCT_OBJECT_["_code"]
					,$_PRODUCT_OBJECT_["_title"]
					,$_PRODUCT_OBJECT_["_department"]
					,$_PRODUCT_OBJECT_["_brand"]
					,$_PRODUCT_OBJECT_["_business_stream"]
					,$_PRODUCT_OBJECT_["_category_1"]
					,$_PRODUCT_OBJECT_["_category_2"]
					,$_PRODUCT_OBJECT_["_category_3"]
					,$_PRODUCT_OBJECT_["_category_4"]
					,$_PRODUCT_OBJECT_["_category_5"]
					,$_PRODUCT_OBJECT_["_category_6"]
					,$_PRODUCT_OBJECT_["_category_7"]
					,$_PRODUCT_OBJECT_["_category_8"]
					,$_PRODUCT_OBJECT_["_category_9"]
					,$_PRODUCT_OBJECT_["_category_10"]
					,$_PRODUCT_OBJECT_["_season"]
					,$_PRODUCT_OBJECT_["_hierarchy_id"]
					,$_PRODUCT_OBJECT_["_variant_1_name"]
					,$_PRODUCT_OBJECT_["_variant_2_name"]
					,$_PRODUCT_OBJECT_["_variant_3_name"]
					,$_PRODUCT_OBJECT_["_variant_4_name"]
					,$_PRODUCT_OBJECT_["_variant_5_name"]
					,$_PRODUCT_OBJECT_["_variant_1"]
					,$_PRODUCT_OBJECT_["_variant_2"]
					,$_PRODUCT_OBJECT_["_variant_3"]
					,$_PRODUCT_OBJECT_["_variant_4"]
					,$_PRODUCT_OBJECT_["_variant_5"]
					,$_PRODUCT_OBJECT_["_uom"]
				),
				$errs,
				$tree_path
			);
			// TEST: Enumerator values are valid
			test_enum_fields_valid(
				$data,
				array(
					 $_PRODUCT_OBJECT_["_class"]
				),
				$_ENUM_PRODUCT_CLASS_TYPE_,
				$errs,
				$tree_path
			);
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_PRODUCT_OBJECT_["_retail_price"]
					,$_PRODUCT_OBJECT_["_wholesale_price"]
				),
				$errs,
				$tree_path
			);
			// TEST: Validate object's test condition
			$family = validate_product_family_object(
				$data[$_PRODUCT_OBJECT_["_product_family_object"]],
				$tree_path,
				$errs
			);
			// TEST: Validate object's test condition
			$family = validate_supplier_product_object(
				$data[$_PRODUCT_OBJECT_["_supplier_object"]],
				$tree_path,
				$errs
			);
			// TEST: Object fields are valid
			test_object_fields_valid(
				$data,
				array(
					$_PRODUCT_OBJECT_["_category_objects"]
				),
				$errs,
				$tree_path
			);
			// TEST: Validate object's test condition
			$categories = validate_category_objects(
				$data[$_PRODUCT_OBJECT_["_category_objects"]],
				$tree_path,
				$errs
			);
			// TEST: Object fields are valid
			test_object_fields_valid(
				$data,
				array(
					$_PRODUCT_OBJECT_["_attribute_objects"]
				),
				$errs,
				$tree_path
			);
			// TEST: Validate object's test condition
			$attributes = validate_attribute_objects(
				$data[$_PRODUCT_OBJECT_["_attribute_objects"]],
				$tree_path,
				$errs
			);
			return $family && $categories && $attributes;
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_product_family_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_PRODUCT_FAMILY_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".family";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: Mandatory fields are set
			test_mandatory_fields_set(
				$data,
				array(
					 $_PRODUCT_FAMILY_OBJECT_["_sku"]
				),
				$errs,
				$tree_path
			);
			// TEST: Mandatory fields are set
			test_mandatory_fields_valid(
				$data,
				array(
					 $_PRODUCT_FAMILY_OBJECT_["_sku"]
				),
				$errs,
				$tree_path
			);
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_PRODUCT_FAMILY_OBJECT_["_id"]
					,$_PRODUCT_FAMILY_OBJECT_["_code"]
					,$_PRODUCT_FAMILY_OBJECT_["_title"]
					,$_PRODUCT_FAMILY_OBJECT_["_sku"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_supplier_product_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_SUPPLIER_PRODUCT_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".supplier";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_SUPPLIER_PRODUCT_OBJECT_["_id"]
					,$_SUPPLIER_PRODUCT_OBJECT_["_code"]
					,$_SUPPLIER_PRODUCT_OBJECT_["_title"]
					,$_SUPPLIER_PRODUCT_OBJECT_["_type"]
					,$_SUPPLIER_PRODUCT_OBJECT_["_product_id"]
					,$_SUPPLIER_PRODUCT_OBJECT_["_product_code"]
					,$_SUPPLIER_PRODUCT_OBJECT_["_reorder_code"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_category_objects($data = null, $tree_path, &$errs){
	try {
		// Append current tree path to parent node
		$tree_path = $tree_path . ".categories";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// Test if object is an associative array or sequential
			if(is_assoc_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path : addslashes($data))."] must be an Array of JSON Objects"));
				return FALSE;
			}
			
			// Element reference for Array
			$i = 0;
			// Test each Object within the array
			foreach( $data as $obj ) {
				// TEST: Validate object's test condition
				$category = validate_category_object(
					$obj,
					$tree_path."[".$i."]",
					$errs
				);
				// RESULT: Fail if any Object is invalid
				if(!$category)
					return FALSE;
					
				$i++;
			}
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_category_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_CATEGORY_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".category";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_CATEGORY_OBJECT_["_id"]
					,$_CATEGORY_OBJECT_["_code"]
					,$_CATEGORY_OBJECT_["_title"]
					,$_CATEGORY_OBJECT_["_value"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_attribute_objects($data = null, $tree_path, &$errs){
	try {
		// Append current tree path to parent node
		$tree_path = $tree_path . ".attributes";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// Test if object is an associative array or sequential
			if(is_assoc_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path : addslashes($data))."] must be an Array of JSON Objects"));
				return FALSE;
			}
			
			// Element reference for Array
			$i = 0;
			// Test each Object within the array
			foreach( $data as $obj ) {
				// TEST: Validate object's test condition
				$attribute = validate_attribute_object(
					$obj,
					$tree_path."[".$i."]",
					$errs
				);
				// RESULT: Fail if any Object is invalid
				if(!$attribute)
					return FALSE;
					
				$i++;
			}
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_attribute_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_ATTRIBUTE_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".attribute";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_ATTRIBUTE_OBJECT_["_id"]
					,$_ATTRIBUTE_OBJECT_["_code"]
					,$_ATTRIBUTE_OBJECT_["_title"]
					,$_ATTRIBUTE_OBJECT_["_value"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_discount_reason_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_DISCOUNT_REASON_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".discount_reason";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_DISCOUNT_REASON_OBJECT_["_id"]
					,$_DISCOUNT_REASON_OBJECT_["_code"]
					,$_DISCOUNT_REASON_OBJECT_["_title"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_return_reason_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_RETURN_REASON_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".return_reason";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_RETURN_REASON_OBJECT_["_id"]
					,$_RETURN_REASON_OBJECT_["_code"]
					,$_RETURN_REASON_OBJECT_["_title"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_void_reason_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_VOID_REASON_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".void_reason";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_VOID_REASON_OBJECT_["_id"]
					,$_VOID_REASON_OBJECT_["_code"]
					,$_VOID_REASON_OBJECT_["_title"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_promotion_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_PROMOTION_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".promotion";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_PROMOTION_OBJECT_["_code"]
					,$_PROMOTION_OBJECT_["_tracking_id"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_dispatch_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_DISPATCH_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".dispatch";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_DISPATCH_OBJECT_["_courier"]
					,$_DISPATCH_OBJECT_["_tracking_url"]
					,$_DISPATCH_OBJECT_["_tracking_number"]
				),
				$errs,
				$tree_path
			);
			// TEST: Date values are valid
			test_date_fields_valid(
				$data,
				array(
					 $_DISPATCH_OBJECT_["_delivery_required_by"]
					,$_DISPATCH_OBJECT_["_delivery_expected_by"]
					,$_DISPATCH_OBJECT_["_delivery_received_at"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_commission_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_COMMISSION_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".commission";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_COMMISSION_OBJECT_["_amount"]
					,$_COMMISSION_OBJECT_["_percentage"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_royalty_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_ROYALTY_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".royalty";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_ROYALTY_OBJECT_["_amount"]
					,$_ROYALTY_OBJECT_["_percentage"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_profit_share_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_PROFIT_SHARE_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".profit_share";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_PROFIT_SHARE_OBJECT_["_amount"]
					,$_PROFIT_SHARE_OBJECT_["_percentage"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_surcharge_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_SURCHARGE_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".surcharge";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_SURCHARGE_OBJECT_["_id"]
					,$_SURCHARGE_OBJECT_["_code"]
					,$_SURCHARGE_OBJECT_["_title"]
				),
				$errs,
				$tree_path
			);
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_SURCHARGE_OBJECT_["_fee"]
					,$_SURCHARGE_OBJECT_["_tax"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_fee_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_FEE_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".fee";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_FEE_OBJECT_["_id"]
					,$_FEE_OBJECT_["_code"]
					,$_FEE_OBJECT_["_title"]
				),
				$errs,
				$tree_path
			);
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_FEE_OBJECT_["_fee"]
					,$_FEE_OBJECT_["_tax"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}
function validate_tender_detail_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_TENDER_DETAIL_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".tender";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_TENDER_DETAIL_OBJECT_["_id"]
				 	,$_TENDER_DETAIL_OBJECT_["_code"]
					,$_TENDER_DETAIL_OBJECT_["_title"]
					,$_TENDER_DETAIL_OBJECT_["_type"]
				),
				$errs,
				$tree_path
			);
			// TEST: Validate object's test condition
			$issuer = validate_issuer_object(
				$data[$_TENDER_DETAIL_OBJECT_["_issuer_object"]],
				$tree_path,
				$errs
			);
			// TEST: Validate object's test condition
			$card = validate_card_object(
				$data[$_TENDER_DETAIL_OBJECT_["_card_object"]],
				$tree_path,
				$errs
			);
			// TEST: Validate object's test condition
			$payment = validate_payment_object(
				$data[$_TENDER_DETAIL_OBJECT_["_payment_object"]],
				$tree_path,
				$errs
			);
			// TEST: Validate object's test condition
			$banking = validate_banking_object(
				$data[$_TENDER_DETAIL_OBJECT_["_banking_object"]],
				$tree_path,
				$errs
			);
			// TEST: Object fields are valid
			test_object_fields_valid(
				$data,
				array(
					$_TENDER_DETAIL_OBJECT_["_finance_objects"]
				),
				$errs,
				$tree_path
			);
			// TEST: Validate object's test condition
			$finance = validate_tlog_finance_objects(
				$data[$_TENDER_DETAIL_OBJECT_["_finance_objects"]], 
				$tree_path, 
				$errs		
			);
			
			return $issuer && $card && $payment && $banking && $finance;
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function validate_issuer_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_ISSUER_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".issuer";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_ISSUER_OBJECT_["_id"]
					,$_ISSUER_OBJECT_["_code"]
					,$_ISSUER_OBJECT_["_title"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function validate_card_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_CARD_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".card";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_CARD_OBJECT_["_id"]
					,$_CARD_OBJECT_["_code"]
					,$_CARD_OBJECT_["_title"]
					,$_CARD_OBJECT_["_holder"]
					,$_CARD_OBJECT_["_number"]
					,$_CARD_OBJECT_["_cvv"]
					,$_CARD_OBJECT_["_pin"]
				),
				$errs,
				$tree_path
			);
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_CARD_OBJECT_["_expiry_year"]
					,$_CARD_OBJECT_["_expiry_month"]
					,$_CARD_OBJECT_["_balance"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function validate_payment_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_PAYMENT_OBJECT_;
		global $_ENUM_PAYMENT_TYPE_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".payment";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_PAYMENT_OBJECT_["_authorization_type"]
					,$_PAYMENT_OBJECT_["_token"]
				),
				$errs,
				$tree_path
			);
			// TEST: Boolean values are valid
			test_bool_fields_valid(
				$data,
				array(
					 $_PAYMENT_OBJECT_["_authorized"]
				),
				$errs,
				$tree_path
			);
			// TEST: Date values are valid
			test_date_fields_valid(
				$data,
				array(
					 $_PAYMENT_OBJECT_["_authorized_at"]
				),
				$errs,
				$tree_path
			);
			// TEST: Enumerator values are valid
			test_enum_fields_valid(
				$data,
				array(
					 $_PAYMENT_OBJECT_["_authorization_type"]
				),
				$_ENUM_PAYMENT_TYPE_,
				$errs,
				$tree_path
			);
			// TEST: Numeric values are valid
			return validate_provider_object(
				$data[$_PAYMENT_OBJECT_["_provider_object"]],
				$tree_path,
				$errs
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function validate_provider_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_PROVIDER_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".provider";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_PROVIDER_OBJECT_["_id"]
					,$_PROVIDER_OBJECT_["_code"]
					,$_PROVIDER_OBJECT_["_title"]
				),
				$errs,
				$tree_path
			);
			
			// TEST: Numeric values are valid
			return validate_gateway_object(
				$data[$_PROVIDER_OBJECT_["_gateway_object"]],
				$tree_path,
				$errs
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function validate_gateway_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_GATEWAY_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".gateway";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_GATEWAY_OBJECT_["_id"]
					,$_GATEWAY_OBJECT_["_code"]
					,$_GATEWAY_OBJECT_["_title"]
				),
				$errs,
				$tree_path
			);
			
			// TEST: Numeric values are valid
			return validate_auth_object(
				$data[$_GATEWAY_OBJECT_["_auth_object"]],
				$tree_path,
				$errs
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function validate_auth_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_AUTH_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".auth";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			// TEST: String values are valid
			test_string_fields_valid(
				$data,
				array(
					 $_AUTH_OBJECT_["_id"]
					,$_AUTH_OBJECT_["_code"]
					,$_AUTH_OBJECT_["_title"]
				),
				$errs,
				$tree_path
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

function validate_banking_object($data = null, $tree_path, &$errs){
	try {
		// Import Field Mapping
		global $_BANKING_OBJECT_;
		// Append current tree path to parent node
		$tree_path = $tree_path . ".banking";
		
		if( $data ) {
			// Default test for input
			if(!is_array($data)) {
				array_push($errs, to_error(400, "JSON Node ["."'".($tree_path ? $tree_path.'.' : '').addslashes($data)."] is not a valid Object or Array"));
				return FALSE;
			}
			
			// TEST: Numeric values are valid
			test_numeric_fields_valid(
				$data,
				array(
					 $_BANKING_OBJECT_["_expected"]
					,$_BANKING_OBJECT_["_counted"]
					,$_BANKING_OBJECT_["_variance"]
				),
				$errs,
				$tree_path
			);
			// TEST: Boolean values are valid
			test_bool_fields_valid(
				$data,
				array(
					 $_BANKING_OBJECT_["_committed"]
					,$_BANKING_OBJECT_["_variance"]
				),
				$errs,
				$tree_path
			);
			// TEST: Object fields are valid
			test_object_fields_valid(
				$data,
				array(
					$_BANKING_OBJECT_["_finance_objects"]
				),
				$errs,
				$tree_path
			);
			return validate_tlog_finance_objects(
				$data[$_BANKING_OBJECT_["_finance_objects"]], 
				$tree_path, 
				$errs		
			);
		}
		return TRUE;
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
}

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
   	 	$tree_path = "$.tlog";
   	 	
   	 	// TEST: Valid JSON format
		if(json_last_error() != JSON_ERROR_NONE) {
			array_push($errs, to_error(400, "Invalid JSON format for request body"));
			return $errs;
		}
		
		// Test if object is an associative array or sequential
		if(is_assoc_array($array)) {
			array_push($errs, to_error(400, "JSON Node ['".$tree_path."'] must be an Array of JSON Objects"));
			return $errs;
		}
		
		$i = 0;
		
		// Loop through each transaction in the array
		foreach($array as $data) {
			// Test JSON Node is an Object or Array
			if(!is_assoc_array($data)) {
				array_push($errs, to_error(400, "JSON Node ['".$tree_path."[".$i."]"."'] is not a valid Object"));
				return $errs;
			}
			
			validate_tlog_header_object(
				$data,
				$tree_path."[".$i."]",
				$errs
			);	
		
			$i++;
		}	
	} catch (Exception $e) {
		array_push($errs, to_error(500, "Error during [".__FUNCTION__."] - ".$e->getMessage()));
	}
	
	return $errs;
}