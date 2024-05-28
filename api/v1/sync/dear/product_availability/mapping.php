<?php

global $_GET_FIELD_MAPPING_;
global $_POST_FIELD_MAPPING_;
global $_PUT_FIELD_MAPPING_;
global $_DELETE_FIELD_MAPPING_;

$_GET_FIELD_MAPPING_ = array(
	 "_company"				=> "company"
	,"_product_id" 			=> "product_id"
	,"_sku" 				=> "sku"
	,"_name" 				=> "name"
	,"_barcode" 			=> "barcode"
	,"_location" 			=> "location"
	,"_location_code" 		=> "location_code"
	,"_bin" 				=> "bin"
	,"_batch_serial" 		=> "batch_serial"
	,"_expiry_date_from" 	=> "expiry_date_from"
	,"_expiry_date_to" 		=> "expiry_date_to"
	,"_on_hand_from" 		=> "on_hand_from"
	,"_on_hand_to" 			=> "on_hand_to"
	,"_allocated_from" 		=> "allocated_from"
	,"_allocated_to" 		=> "allocated_to"
	,"_available_from" 		=> "available_from"
	,"_available_to" 		=> "available_to"
	,"_on_order_from" 		=> "on_order_from"
	,"_on_order_to" 		=> "on_order_to"
	,"_value_on_hand_from" 	=> "value_on_hand_from"
	,"_value_on_hand_to" 	=> "value_on_hand_to"
);

$_POST_FIELD_MAPPING_ = array(
	 "_company"				=> "company"
	,"_product_id" 			=> "product_id"
	,"_sku" 				=> "sku"
	,"_name" 				=> "name"
	,"_barcode" 			=> "barcode"
	,"_location" 			=> "location"
	,"_location_code" 		=> "location_code"
	,"_bin" 				=> "bin"
	,"_batch_serial" 		=> "batch_serial"
	,"_expiry_date_from" 	=> "expiry_date_from"
	,"_expiry_date_to" 		=> "expiry_date_to"
	,"_on_hand_from" 		=> "on_hand_from"
	,"_on_hand_to" 			=> "on_hand_to"
	,"_allocated_from" 		=> "allocated_from"
	,"_allocated_to" 		=> "allocated_to"
	,"_available_from" 		=> "available_from"
	,"_available_to" 		=> "available_to"
	,"_on_order_from" 		=> "on_order_from"
	,"_on_order_to" 		=> "on_order_to"
	,"_value_on_hand_from" 	=> "value_on_hand_from"
	,"_value_on_hand_to" 	=> "value_on_hand_to"
);

$_PUT_FIELD_MAPPING_ = array(
	 "_company"				=> "company"
	,"_product_id" 			=> "product_id"
	,"_sku" 				=> "sku"
	,"_name" 				=> "name"
	,"_barcode" 			=> "barcode"
	,"_location" 			=> "location"
	,"_bin" 				=> "bin"
	,"_batch_serial" 		=> "batch_serial"
	,"_expiry_date" 		=> "expiry_date"
	,"_on_hand" 			=> "on_hand"
	,"_allocated" 			=> "allocated"
	,"_available" 			=> "available"
	,"_on_order" 			=> "on_order"
	,"_value_on_hand" 		=> "value_on_hand"
);

$_DELETE_FIELD_MAPPING_ = array(
	 "_company"				=> "company"
	,"_retention_in_secs"	=> "retention_in_secs"
);
