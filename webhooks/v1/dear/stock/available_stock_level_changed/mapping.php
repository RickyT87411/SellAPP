<?php
define("_API_SYNC_DEAR_PRODUCT_AVAILABILITY_URL", "https://prod.playbill.com.au/api/v1/sync/dear/product_availability/");

global $_GET_FIELD_MAPPING_;
global $_POST_FIELD_MAPPING_;
global $_PUT_FIELD_MAPPING_;
global $_DELETE_FIELD_MAPPING_;
global $_API_SYNC_DEAR_PRODUCT_AVAILABILITY_MAPPING;
global $_TYPE_ENUM;
global $_ORDER_BY_ENUM;

$_GET_FIELD_MAPPING_ = array();

$_POST_FIELD_MAPPING_ = array(
	 "_id"				=>		"ID"
	,"_sku"				=>		"SKU"
	,"_name"			=>		"Name"
	,"_barcode"			=>		"Barcode"
	,"_location"		=>		"Location"
	,"_bin"				=>		"Bin"
	,"_batch"			=>		"Batch"
	,"_expiry_date"		=>		"ExpiryDate"
	,"_category"		=>		"Category"
	,"_on_hand"			=>		"OnHand"
	,"_allocated"		=>		"Allocated"
	,"_available"		=>		"Available"
	,"_on_order"		=>		"OnOrder"
	,"_stock_on_hand"	=>		"StockOnHand"
	,"_max_rows"		=>		"MaxRows"
);

$_PUT_FIELD_MAPPING_ = array();

$_DELETE_FIELD_MAPPING_ = array();

$_API_SYNC_DEAR_PRODUCT_AVAILABILITY_MAPPING = array(
	 "instance"		=>		"company"
	,"ID"			=>		"product_id"
	,"SKU"			=>		"sku"
	,"Name"			=>		"name"
	,"Barcode"		=>		"barcode"
	,"Location"		=>		"location"
	,"Bin"			=>		"bin"
	,"Batch"		=>		"batch_serial"
	,"ExpiryDate"	=>		"expiry_date"
	,"OnHand"		=>		"on_hand"
	,"Allocated"	=>		"allocated"
	,"Available"	=>		"available"
	,"OnOrder"		=>		"on_order"
	,"StockOnHand"	=>		"value_on_hand"
);