<?php

global $_GET_FIELD_MAPPING_;
global $_POST_FIELD_MAPPING_;
global $_PUT_FIELD_MAPPING_;
global $_DELETE_FIELD_MAPPING_;
global $_CHANNELS_ENUM;
global $_TYPE_ENUM;
global $_ORDER_BY_ENUM;

$_GET_FIELD_MAPPING_ = array(
	 "_id"								=>		"id"
	,"_channel"							=>		"channel"
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
	,"_integrate"						=>		"integrated"
	,"_date_from"						=>		"date_from"
	,"_date_to"							=>		"date_to"
	,"_since"							=>		"since"
	,"_order_by"						=>		"order_by"
	,"_asc"								=>		"asc"
	,"_include_finance_objects"			=>		"include_finance_objects"
);

$_POST_FIELD_MAPPING_ = array(
	 "_channel"							=>		"channel"
	,"_source"							=>		"source"
	,"_source_instance"					=>		"source_instance"
	,"_type"							=>		"type"
	,"_sub_type"						=>		"sub_type"
	,"_transaction_parent_id"			=>		"source_parent_id"
	,"_transaction_id"					=>		"source_id"
	,"_title"							=>		"title"
	,"_status"							=>		"status"
	,"_location_id"						=>		"location_id"
	,"_location"						=>		"location"
	,"_location_type"					=>		"location_type"
	,"_outlet_id"						=>		"outlet_id"
	,"_outlet"							=>		"outlet"
	,"_register_id"						=>		"register_id"
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

$_PUT_FIELD_MAPPING_ = array(
	 "_id"								=>		"id"
	,"_channel"							=>		"channel"
	,"_source"							=>		"source"
	,"_source_instance"					=>		"source_instance"
	,"_type"							=>		"type"
	,"_sub_type"						=>		"sub_type"
	,"_transaction_parent_id"			=>		"source_parent_id"
	,"_transaction_id"					=>		"source_id"
	,"_title"							=>		"title"
	,"_status"							=>		"status"
	,"_location_id"						=>		"location_id"
	,"_location"						=>		"location"
	,"_location_type"					=>		"location_type"
	,"_outlet_id"						=>		"outlet_id"
	,"_outlet"							=>		"outlet"
	,"_register_id"						=>		"register_id"
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

$_DELETE_FIELD_MAPPING_ = array(
	 "_id"								=>		"id"
);

$_CHANNELS_ENUM = array(
	 "ecommerce"
	,"finance"
	,"inventory"
	,"pos"
);

$_TYPE_ENUM = array(
	 "purchase"
	,"sale"
	,"stock"
);

$_ORDER_BY_ENUM = array(
	 "id"
	,"hash"
	,"channel"
	,"source"
	,"source_instance"
	,"type"
	,"sub_type"
	,"source_parent_id"
	,"source_id"
	,"title"
	,"status"
	,"location"
	,"location_type"
	,"outlet"
	,"register"
	,"timezone"
	,"timezone_offset"
	,"date"
	,"created_at"
	,"updated_at"
	,"updated_at_utc"
	,"monotonic_version"
	,"integrated"
);


