<?php
define("_API_CUSTOMERS_URL", "https://prod.playbill.com.au/api/v1/customers/");
define("_API_BIGCOMMERCE_STORE_URL", "https://api.bigcommerce.com/stores/%s/v2/store");
define("_API_BIGCOMMERCE_CUSTOMERS_URL", "https://api.bigcommerce.com/stores/%s/v2/customers/%s");
define("_API_BIGCOMMERCE_CUSTOMER_GROUPS_URL", "https://api.bigcommerce.com/stores/%s/v2/customer_groups/%s");

global $_GET_FIELD_MAPPING_;
global $_POST_FIELD_MAPPING_;
global $_PUT_FIELD_MAPPING_;
global $_DELETE_FIELD_MAPPING_;
global $_API_CUSTOMERS_POST_MAPPING;
global $_API_BIGCOMMERCE_REQUEST_HEADERS;
global $_API_BIGCOMMERCE_STORE_MAPPING;
global $_API_BIGCOMMERCE_CUSTOMERS_MAPPING;
global $_API_BIGCOMMERCE_CUSTOMER_GROUPS_MAPPING;

global $_WEBHOOK_CHANNEL;
global $_WEBHOOK_SOURCE;
global $_WEBHOOK_TYPE;


$_API_BIGCOMMERCE_REQUEST_HEADERS = array(
	 "nkpc55rifa"	=> [
		 "X-Auth-Client: " 	. "ipyy06etdnn1nmz0812jztfcj8ytme4"	
		,"X-Auth-Token: " 	. "6himo9j5fdiivvx3c4uimdzbjbfvss0"
		,"Content-Type: "	. "application/json"
		,"Accept: "			. "application/json"
	]
);

$_WEBHOOK_CHANNEL = "ecommerce";
$_WEBHOOK_SOURCE = "bigcommerce";

$_GET_FIELD_MAPPING_ = array();

$_POST_FIELD_MAPPING_ = array(
	 "_scope"			=>		"scope"
	,"_store_id"		=>		"store_id"
	,"_hash"			=>		"hash"
	,"_created_at"		=>		"created_at"
	,"_producer"		=>		"producer"
	,"_id"				=>		"id"
);

$_PUT_FIELD_MAPPING_ = array();

$_DELETE_FIELD_MAPPING_ = array();

$_API_CUSTOMERS_PUT_MAPPING = array(
	 "_hash"						=>		"hash"
	,"_channel"						=>		"channel"
	,"_source"						=>		"source"
	,"_source_instance"				=>		"source_instance"
	,"_source_id"					=>		"source_id"
	,"_type"						=>		"type"
	,"_sub_type"					=>		"sub_type"
	,"_email"						=>		"email"
	,"_status"						=>		"status"
	,"_customer_no"					=>		"customer_no"
	,"_membership_no"				=>		"membership_no"
	,"_debtor_id"					=>		"debtor_id"
	,"_firstname"					=>		"firstname"
	,"_lastname"					=>		"lastname"
	,"_othernames"					=>		"othernames"
	,"_contact_no"					=>		"contact_no"
	,"_company"						=>		"company"
	,"_schemes"						=>		"schemes"
	,"_device_fingerprint"			=>		"device_fingerprint"
	,"_device_ip_address"			=>		"device_ip_address"
	,"_advertising_id"				=>		"advertising_id"
	,"_accepts_marketing"			=>		"accepts_marketing"
	,"_accepts_communications"		=>		"accepts_communications"
	,"_timezone"					=>		"timezone"
	,"_active"						=>		"active"
	,"_active_at"					=>		"active_at"
	,"_activated_at"				=>		"activated_at"
	,"_created_at"					=>		"created_at"
	,"_updated_at"					=>		"updated_at"
	,"_updated_at_utc"				=>		"updated_at_utc"
	,"_monotonic_version"			=>		"monotonic_version"
	,"_version_hash"				=>		"version_hash"
	,"_integrate"					=>		"integrated"
);

$_API_BIGCOMMERCE_STORE_MAPPING = array(
	 "_id" => "id"
	,"_domain" => "domain"
	,"_secure_url" => "secure_url"
	,"_status" => "status"
	,"_name" => "name"
	,"_first_name" => "first_name"
	,"_last_name" => "last_name"
	,"_address" => "address"
	,"_country" => "country"
	,"_country_code" => "country_code"
	,"_phone" => "phone"
	,"_admin_email" => "admin_email"
	,"_order_email" => "order_email"
	,"_favicon_url" => "favicon_url"
	,"_timezone" => "timezone"
	,"_timezone_name" => "name"
	,"_timezone_raw_offset" => "raw_offset"
	,"_timezone_dst_offset" => "dst_offset"
	,"_timezone_dst_correction" => "dst_correction"
	,"_timezone_date_format" => "date_format"
	,"_timezone_date_format_display" => "display"
	,"_timezone_date_format_export" => "export"
	,"_timezone_date_format_extended_display" => "extended_display"
	,"_language" => "language"
	,"_currency" => "currency"
	,"_currency_symbol" => "currency_symbol"
	,"_decimal_separator" => "decimal_separator"
	,"_thousands_separator" => "thousands_separator"
	,"_decimal_places" => "decimal_places"
	,"_currency_symbol_location" => "currency_symbol_location"
	,"_weight_units" => "weight_units"
	,"_dimension_units" => "dimension_units"
	,"_dimension_decimal_places" => "dimension_decimal_places"
	,"_dimension_decimal_token" => "dimension_decimal_token"
	,"_dimension_thousands_token" => "dimension_thousands_token"
	,"_plan_name" => "plan_name"
	,"_plan_level" => "plan_level"
	,"_industry" => "industry"
	,"_logo" => "logo"
	,"_logo_url" => "logo_url"
	,"_is_price_entered_with_tax" => "is_price_entered_with_tax"
	,"_active_comparison_modules" => "active_comparison_modules"
	,"_features" => "features"
	,"_features_stencil_enabled" => "stencil_enabled"
	,"_features_sitewidehttps_enabled" => "sitewidehttps_enabled"
	,"_features_facebook_catalog_id" => "facebook_catalog_id"
	,"_features_checkout_type" => "checkout_type"
);

$_API_BIGCOMMERCE_CUSTOMERS_MAPPING = array(
	 "_id" => "id"
	,"_company" => "company"
	,"_first_name" => "first_name"
	,"_last_name" => "last_name"
	,"_email" => "email"
	,"_phone" => "phone"
	,"_date_created" => "date_created"
	,"_date_modified" => "date_modified"
	,"_store_credit" => "store_credit"
	,"_registration_ip_address" => "registration_ip_address"
	,"_customer_group_id" => "customer_group_id"
	,"_notes" => "notes"
	,"_tax_exempt_category" => "tax_exempt_category"
	,"_accepts_marketing" => "accepts_marketing"
	,"_addresses" => "addresses"
	,"_url" => "url"
	,"_resource" => "resource"
	,"_form_fields" => "form_fields"
	,"_reset_pass_on_login" => "reset_pass_on_login"
);

$_API_BIGCOMMERCE_CUSTOMER_GROUPS_MAPPING = array(
	 "_id" => "id"
	,"_name" => "name"
	,"_is_default" => "is_default"
	,"_category_access" => "category_access"
	,"_discount_rules" => "discount_rules"
	,"_discount_rules_type" => "type"
	,"_discount_rules_method" => "method"
	,"_discount_rules_amount" => "amount"
);
