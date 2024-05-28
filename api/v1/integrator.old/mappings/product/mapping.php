<?php

global $_GET_FIELD_MAPPING_;
global $_POST_FIELD_MAPPING_;
global $_DELETE_FIELD_MAPPING_;

$_GET_FIELD_MAPPING_ = array(
	 "_product_id"			=>		"id"
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
);

$_POST_FIELD_MAPPING_ = array(
	 "_product_id"			=>		"id"
	,"_sku"					=>		"sku"
	,"_barcode"				=>		"barcode"
	,"_company"				=>		"company"
	,"_country_code"		=>		"country_code"
	,"_dear_instance"		=>		"dear_instance"
	,"_dear_product_id"		=>		"dear_product_id"
	,"_dear_sku"			=>		"dear_sku"
	,"_dear_created_at"		=>		"dear_created_at"
	,"_dear_updated_at"		=>		"dear_updated_at"
	,"_vend_instance"		=>		"vend_instance"
	,"_vend_product_id"		=>		"vend_product_id"
	,"_vend_sku"			=>		"vend_sku"
	,"_vend_created_at"		=>		"vend_created_at"
	,"_vend_updated_at"		=>		"vend_updated_at"
	,"_fuzzy_match"			=>		"fuzzy_match"
);

$_DELETE_FIELD_MAPPING_ = array(
	 "_product_id"			=>		"id"
	,"_sku"					=>		"sku"
	,"_company"				=>		"company"
	,"_country_code"		=>		"country_code"
	,"_dear_instance"		=>		"dear_instance"
	,"_dear_product_id"		=>		"dear_product_id"
	,"_dear_sku"			=>		"dear_sku"
	,"_vend_instance"		=>		"vend_instance"
	,"_vend_product_id"		=>		"vend_product_id"
	,"_vend_sku"			=>		"vend_sku"
);
