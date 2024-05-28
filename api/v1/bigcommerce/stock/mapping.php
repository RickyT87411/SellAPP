<?php
define("_API_BIGCOMMERCE_STORE_URL", "https://api.bigcommerce.com/stores/%s/v2/store");
define("_API_BIGCOMMERCE_CATALOG_VARIANTS_URL", "https://api.bigcommerce.com/stores/%s/v3/catalog/variants/?sku=%s");
define("_API_BIGCOMMERCE_CATALOG_PRODUCTS_URL", "https://api.bigcommerce.com/stores/%s/v3/catalog/products/?sku=%s");
define("_API_BIGCOMMERCE_PRODUCTS_VARIANTS_URL", "https://api.bigcommerce.com/stores/%s/v3/catalog/products/%s/variants/%s");
define("_API_BIGCOMMERCE_PRODUCTS_URL", "https://api.bigcommerce.com/stores/%s/v3/catalog/products/%s");

global $_GET_FIELD_MAPPING_;
global $_POST_FIELD_MAPPING_;
global $_PUT_FIELD_MAPPING_;
global $_DELETE_FIELD_MAPPING_;

global $_API_BIGCOMMERCE_REQUEST_HEADERS;
global $_API_BIGCOMMERCE_STORE_INSTANCE_MAPPING;
global $_API_BIGCOMMERCE_STORE_MAPPING;
global $_API_BIGCOMMERCE_PRODUCTS_VARIANTS_MAPPING;

global $_WEBHOOK_CHANNEL;
global $_WEBHOOK_SOURCE;

$_API_BIGCOMMERCE_STORE_INSTANCE_MAPPING = array(
	 "warriorssandbox"	=>	"nkpc55rifa"
);

$_API_BIGCOMMERCE_REQUEST_HEADERS = array(
	 "nkpc55rifa"	=> [
		 "X-Auth-Client: " 	. "ipyy06etdnn1nmz0812jztfcj8ytme4"	
		,"X-Auth-Token: " 	. "6himo9j5fdiivvx3c4uimdzbjbfvss0"
		,"Content-Type: "	. "application/json"
		,"Accept: "			. "application/json"
	]
);

$_POST_FIELD_MAPPING_ = array(
	 "_source_instance"		=> "source_instance"
	,"_sku" 				=> "sku"
	,"_location" 			=> "location"
	,"_bin" 				=> "bin"
	,"_batch_serial" 		=> "batch_serial"
	,"_expiry_date" 		=> "expiry_date"
	,"_on_hand" 			=> "on_hand"
	,"_allocated" 			=> "allocated"
	,"_available" 			=> "available"
	,"_on_order" 			=> "on_order"
	,"_value_on_hand"		=> "value_on_hand"
);

$_PUT_FIELD_MAPPING_ = array(
	 "_variant_id"			=> "variant_id"
	,"_product_id"			=> "product_id"
	,"_source_instance"		=> "source_instance"
	,"_location" 			=> "location"
	,"_bin" 				=> "bin"
	,"_batch_serial" 		=> "batch_serial"
	,"_expiry_date" 		=> "expiry_date"
	,"_on_hand" 			=> "on_hand"
	,"_allocated" 			=> "allocated"
	,"_available" 			=> "available"
	,"_on_order" 			=> "on_order"
	,"_value_on_hand"		=> "value_on_hand"
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

$_API_BIGCOMMERCE_PRODUCTS_VARIANTS_MAPPING = array(
     "_cost_price" => "cost_price"
	,"_price" => "price"
	,"_sale_price" => "sale_price"
	,"_retail_price" => "retail_price"
	,"_weight" => "weight"
	,"_width" => "width"
	,"_height" => "height"
	,"_depth" => "depth"
	,"_is_free_shipping" => "is_free_shipping"
	,"_fixed_cost_shipping_price" => "fixed_cost_shipping_price"
	,"_purchasing_disabled" => "purchasing_disabled"
	,"_purchasing_disabled_message" => "purchasing_disabled_message"
	,"_image_url" => "image_url"
	,"_upc" => "upc"
	,"_inventory_level" => "inventory_level"
	,"_inventory_warning_level" => "inventory_warning_level"
	,"_bin_picking_number" => "bin_picking_number"
);