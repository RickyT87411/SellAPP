<?php

global $_GET_FIELD_MAPPING_;
global $_POST_FIELD_MAPPING_;
global $_PUT_FIELD_MAPPING_;
global $_DELETE_FIELD_MAPPING_;
global $_CHANNELS_ENUM;
global $_ORDER_BY_ENUM;

$_GET_FIELD_MAPPING_ = array(
	 "_id"							=>		"id"
	,"_hash"						=>		"hash"
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
	,"_contact_no"					=>		"contact_no"
	,"_active"						=>		"active"
	,"_integrate"					=>		"integrated"
	,"_since"						=>		"since"
	,"_order_by"					=>		"order_by"
	,"_asc"							=>		"asc"
);

$_POST_FIELD_MAPPING_ = array(
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

$_PUT_FIELD_MAPPING_ = array(
	 "_id"							=>		"id"
	,"_hash"						=>		"hash"
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

$_DELETE_FIELD_MAPPING_ = array(
	 "_id"							=>		"id"
	,"_hash"						=>		"hash"
);

$_CHANNELS_ENUM = array(
	 "ecommerce"
	,"finance"
	,"inventory"
	,"pos"
);

$_ORDER_BY_ENUM = array(
	 "id"
	,"hash"
	,"channel"
	,"source"
	,"source_instance"
	,"type"
	,"sub_type"
	,"email"
	,"customer_no"
	,"membership_no"
	,"debtor_id"
	,"firstname"
	,"lastname"
	,"contact_no"
	,"company"
	,"active_at"
	,"activated_at"
	,"created_at"
	,"updated_at"
	,"updated_at_utc"
	,"monotonic_version"
	,"integrated"
);


