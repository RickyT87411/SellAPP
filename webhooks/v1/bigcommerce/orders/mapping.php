<?php
define("_API_TRANSACTIONS_URL", "https://prod.playbill.com.au/api/v1/transactions/");
define("_API_BIGCOMMERCE_STORE_URL", "https://api.bigcommerce.com/stores/%s/v2/store");
define("_API_BIGCOMMERCE_ORDERS_URL", "https://api.bigcommerce.com/stores/%s/v2/orders/%s");

global $_GET_FIELD_MAPPING_;
global $_POST_FIELD_MAPPING_;
global $_PUT_FIELD_MAPPING_;
global $_DELETE_FIELD_MAPPING_;
global $_API_TRANSACTIONS_POST_MAPPING;
global $_API_BIGCOMMERCE_REQUEST_HEADERS;
global $_API_BIGCOMMERCE_ORDERS_MAPPING;
global $_API_BIGCOMMERCE_ORDER_STATUSES;

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
$_WEBHOOK_TYPE = "sale";

$_GET_FIELD_MAPPING_ = array();

$_POST_FIELD_MAPPING_ = array(
	 "_scope"			=>		"scope"
	,"_store_id"		=>		"store_id"
	,"_hash"			=>		"hash"
	,"_created_at"		=>		"created_at"
	,"_producer"		=>		"producer"
	,"_order_id"		=>		"orderId"
	,"_id"				=>		"id"
);

$_PUT_FIELD_MAPPING_ = array();

$_DELETE_FIELD_MAPPING_ = array();

$_API_TRANSACTIONS_POST_MAPPING = array(
	 "_channel"							=>		"channel"
	,"_source"							=>		"source"
	,"_source_instance"					=>		"source_instance"
	,"_type"							=>		"type"
	,"_sub_type"						=>		"sub_type"
	,"_transaction_parent_id"			=>		"source_parent_id"
	,"_transaction_id"					=>		"source_id"
	,"_title"							=>		"title"
	,"_status"							=>		"status"
	,"_location"						=>		"location"
	,"_location_type"					=>		"location_type"
	,"_outlet"							=>		"outlet"
	,"_register"						=>		"register"
	,"_transaction_timezone"			=>		"timezone"
	,"_transaction_date"				=>		"date"
	,"_created_at"						=>		"created_at"
	,"_updated_at"						=>		"updated_at"
	,"_updated_at_utc"					=>		"updated_at_utc"
	,"_monotonic_version"				=>		"monotonic_version"
	,"_version_hash"					=>		"version_hash"
	,"_integrate"						=>		"integrated"
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

$_API_BIGCOMMERCE_ORDERS_MAPPING = array(
	 "_id" => "id"
	,"_customer_id" => "customer_id"
	,"_date_created" => "date_created"
	,"_date_modified" => "date_modified"
	,"_date_shipped" => "date_shipped"
	,"_status_id" => "status_id"
	,"_cart_id" => "cart_id"
	,"_status" => "status"
	,"_subtotal_ex_tax" => "subtotal_ex_tax"
	,"_subtotal_inc_tax" => "subtotal_inc_tax"
	,"_subtotal_tax" => "subtotal_tax"
	,"_base_shipping_cost" => "base_shipping_cost"
	,"_shipping_cost_ex_tax" => "shipping_cost_ex_tax"
	,"_shipping_cost_inc_tax" => "shipping_cost_inc_tax"
	,"_shipping_cost_tax" => "shipping_cost_tax"
	,"_shipping_cost_tax_class_id" => "shipping_cost_tax_class_id"
	,"_base_handling_cost" => "base_handling_cost"
	,"_handling_cost_ex_tax" => "handling_cost_ex_tax"
	,"_handling_cost_inc_tax" => "handling_cost_inc_tax"
	,"_handling_cost_tax" => "handling_cost_tax"
	,"_handling_cost_tax_class_id" => "handling_cost_tax_class_id"
	,"_base_wrapping_cost" => "base_wrapping_cost"
	,"_wrapping_cost_ex_tax" => "wrapping_cost_ex_tax"
	,"_wrapping_cost_inc_tax" => "wrapping_cost_inc_tax"
	,"_wrapping_cost_tax" => "wrapping_cost_tax"
	,"_wrapping_cost_tax_class_id" => "wrapping_cost_tax_class_id"
	,"_total_ex_tax" => "total_ex_tax"
	,"_total_inc_tax" => "total_inc_tax"
	,"_total_tax" => "total_tax"
	,"_items_total" => "items_total"
	,"_items_shipped" => "items_shipped"
	,"_payment_method" => "payment_method"
	,"_payment_provider_id" => "payment_provider_id"
	,"_refunded_amount" => "refunded_amount"
	,"_order_is_digital" => "order_is_digital"
	,"_store_credit_amount" => "store_credit_amount"
	,"_gift_certificate_amount" => "gift_certificate_amount"
	,"_ip_address" => "ip_address"
	,"_geoip_country" => "geoip_country"
	,"_geoip_country_iso2" => "geoip_country_iso2"
	,"_currency_id" => "currency_id"
	,"_currency_code" => "currency_code"
	,"_currency_exchange_rate" => "currency_exchange_rate"
	,"_default_currency_id" => "default_currency_id"
	,"_default_currency_code" => "default_currency_code"
	,"_staff_notes" => "staff_notes"
	,"_customer_message" => "customer_message"
	,"_discount_amount" => "discount_amount"
	,"_coupon_discount" => "coupon_discount"
	,"_shipping_address_count" => "shipping_address_count"
	,"_is_deleted" => "is_deleted"
	,"_is_email_opt_in" => "is_email_opt_in"
	,"_ebay_order_id" => "ebay_order_id"
	,"_order_source" => "order_source"
	,"_external_source" => "external_source"
	,"_external_id" => "external_id"
	,"_external_merchant_id" => "external_merchant_id"
	,"_custom_status" => "custom_status"
);

$_API_BIGCOMMERCE_ORDER_STATUSES = array(
	 0	=>	"Incomplete"
	,1	=>	"Pending"
	,2	=>	"Shipped"
	,3	=>	"Partially Shipped"
	,4	=>	"Refunded"
	,5	=>	"Cancelled"
	,6	=>	"Declined"
	,7	=>	"Awaiting Payment"
	,8	=>	"Awaiting Pickup"
	,9	=>	"Awaiting Shipment"
	,10	=>	"Completed"
	,11	=>	"Awaiting Fulfillment"
	,12	=>	"Manual Verification Required"
	,13	=>	"Disputed"
	,14	=>	"Partially Refunded"
);