<?php

global $_FIELD_MAPPING_;

$_FIELD_MAPPING_ = array(
	 "old_sku"		=>		"old_sku"
	,"new_sku"		=>		"new_sku"
);

define("_DEAR_AUTH_ACCOUNT_PARAM_", "api-auth-accountid");
define("_DEAR_AUTH_KEY_PARAM_", "api-auth-applicationkey");
define("_DEAR_PRODUCT_API_V1_", "https://inventory.dearsystems.com/DearApi/Products/");
define("_DEAR_PRODUCT_NODE_", "Products");

define("_VEND_AUTH_PARAM_", "Authorization");
define("_VEND_PRODUCT_API_V1_", "https://%s.vendhq.com/api/products");
define("_VEND_PRODUCT_NODE_", "products");
define("_VEND_PRODUCT_POST_NODE_", "product");

global $_DEAR_DOMAIN_AUTH_KEYS_;
global $_VEND_DOMAIN_AUTH_KEYS_;

$_DEAR_DOMAIN_AUTH_KEYS_ = array(
	"playbill"      => array(
		_DEAR_AUTH_ACCOUNT_PARAM_   => "3690ecc8-db52-496d-b9e8-d81d3c602f68",
		_DEAR_AUTH_KEY_PARAM_       => "e720af4d-2e86-b81b-e12a-15f648d8c9c1"
	),
	"playbilldev"   => array(
		_DEAR_AUTH_ACCOUNT_PARAM_   => "eab948c2-a308-4368-8b1f-f4354d316ffe",
		_DEAR_AUTH_KEY_PARAM_       => "ecdd123c-f9f0-c3cb-1d7c-7303d4d433e9"
	),
	"playbillnz"   => array(
		_DEAR_AUTH_ACCOUNT_PARAM_   => "0914e842-a1e3-4eba-9e12-addc2f6b6c3d",
		_DEAR_AUTH_KEY_PARAM_       => "62e9437e-c870-2b63-a682-e24f86540f45"
	),
	"playbilluk"   => array(
		_DEAR_AUTH_ACCOUNT_PARAM_   => "7d5aa275-8405-436e-a134-68ca34eba438",
		_DEAR_AUTH_KEY_PARAM_       => "3a625b2d-9ea5-130d-bccf-59ffb617b6d4"
	),
	"platypusproductions"   => array(
		_DEAR_AUTH_ACCOUNT_PARAM_   => "4425b9f2-0a73-4031-9ae8-a7bef4afbdb1",
		_DEAR_AUTH_KEY_PARAM_       => "1c87767b-faa0-d02e-9cf7-db16194d37c6"
	)
	
);

$_VEND_DOMAIN_AUTH_KEYS_ = array(
	"playbill"      => array(
		_VEND_AUTH_PARAM_   => "Bearer 2Bqb1zG2KckwX2KudxR0P2:7rV8m36GfDKbc7JVy"
	),
	"playbilldev"   => array(
		_VEND_AUTH_PARAM_   => "Bearer 5OtjwgBqfHJZgreSOZGhV:XX3WZPMnsdabLBDXPR"
	),
	"playbillnz"   => array(
		_VEND_AUTH_PARAM_   => "Bearer 5OtjwgBqfHJZgreSOZGhV:XX3WZPMnsdabLBDXPR"
	),
	"platypusproductions"   => array(
		_VEND_AUTH_PARAM_   => "Bearer 5OtjwgBqfIMt7z08bgW06_qRce3jjYsHZuIr6rNX"
	)
);