<?php

global $_GET_FIELD_MAPPING_;
global $_POST_FIELD_MAPPING_;
global $_DELETE_FIELD_MAPPING_;
global $_ENUM_ORDER_BY_;

$_GET_FIELD_MAPPING_ = array(
	 "_id"					=>		"id"
	,"_sku"					=>		"sku"
	,"_barcode"				=>		"barcode"
	,"_company"				=>		"company"
	,"_country_code"		=>		"country_code"
	,"_dear_instance"		=>		"dear_instance"
	,"_dear_product_id"		=>		"dear_product_id"
	,"_dear_sku"			=>		"dear_sku"
	,"_vend_instance"		=>		"vend_instance"
	,"_vend_product_id"		=>		"vend_product_id"
	,"_vend_sku"			=>		"vend_sku"
	,"_created_from"		=>		"created_from"
	,"_created_to"			=>		"created_to"
	,"_updated_from"		=>		"updated_from"
	,"_updated_to"			=>		"updated_to"
	,"_fuzzy_match"			=>		"fuzzy_match"
	,"_order_by"			=>		"order_by"
	,"_asc"					=>		"asc"
	
	
	,"_source"				=>		"source"
	,"_source_instance"		=>		"source_instance"
	,"_source_id"			=>		"source_id"
	,"_source_sku"			=>		"source_sku"
	
);

$_POST_FIELD_MAPPING_ = array(
	 "_id"							=>		"id"
	,"_sku"							=>		"sku"
	,"_barcode"						=>		"barcode"
	,"_company"						=>		"company"
	,"_country_code"				=>		"country_code"
	,"_dear_instance"				=>		"dear_instance"
	,"_dear_product_id"				=>		"dear_product_id"
	,"_dear_sku"					=>		"dear_sku"
	,"_dear_created_at"				=>		"dear_created_at"
	,"_dear_updated_at"				=>		"dear_updated_at"
	,"_vend_instance"				=>		"vend_instance"
	,"_vend_product_id"				=>		"vend_product_id"
	,"_vend_sku"					=>		"vend_sku"
	,"_vend_version"				=>		"vend_version"
	,"_vend_created_at"				=>		"vend_created_at"
	,"_vend_updated_at"				=>		"vend_updated_at"
	,"_fuzzy_match"					=>		"fuzzy_match"
	
	,"_source"						=>		"source"
	,"_source_instance"				=>		"source_instance"
	,"_source_parent_id"			=>		"source_parent_id"
	,"_source_id"					=>		"source_id"
	,"_source_sku"					=>		"source_sku"
	,"_source_version_hash"			=>		"source_version_hash"
	,"_source_monotonic_version"	=>		"source_monotonic_version"
	,"_source_created_at"			=>		"source_created_at"
	,"_source_updated_at"			=>		"source_updated_at"
);

$_DELETE_FIELD_MAPPING_ = array(
	 "_id"					=>		"id"
	,"_sku"					=>		"sku"
	,"_company"				=>		"company"
	,"_country_code"		=>		"country_code"
	,"_dear_instance"		=>		"dear_instance"
	,"_dear_product_id"		=>		"dear_product_id"
	,"_dear_sku"			=>		"dear_sku"
	,"_vend_instance"		=>		"vend_instance"
	,"_vend_product_id"		=>		"vend_product_id"
	,"_vend_sku"			=>		"vend_sku"
	
	,"_source"						=>		"source"
	,"_source_instance"				=>		"source_instance"
	,"_source_id"					=>		"source_id"
	,"_source_sku"					=>		"source_sku"
);

$_ENUM_ORDER_BY_ = array(
	 "dear"
	,"vend"
	,"playbill"
	,"source"
	,"source_instance"
	,"source_parent_id"
	,"source_id"
	,"source_sku"
	,"source_version_hash"
	,"source_monotonic_version"
	,"source_created_at"
	,"source_updated_at"
);