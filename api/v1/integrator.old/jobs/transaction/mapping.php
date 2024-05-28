<?php

global $_FIELD_MAPPING_;
global $_SOURCE_CHANNEL_MAPPING_;

$_FIELD_MAPPING_ = array(
	 "_channel"							=>		"channel"
	,"_source"							=>		"source_host"
	,"_source_instance"					=>		"source_host_instance"
	,"_location"						=>		"transaction_location"
	,"_location_type"					=>		"transaction_location_type"
	,"_outlet"							=>		"transaction_outlet"
	,"_register"						=>		"transaction_register"
	,"_type"							=>		"transaction_type"
	,"_id"								=>		"transaction_id"
	,"_title"							=>		"transaction_title"
	,"_status"							=>		"transaction_status"
	,"_transaction_date"				=>		"transaction_date"
	,"_created_at"						=>		"created_at"
	,"_updated_at"						=>		"updated_at"
	,"_updated_at_utc"					=>		"updated_at_utc"
	,"_transaction_timezone"			=>		"transaction_timezone"
	,"_resync"							=>		"resync_integrations"
	,"_integrate"						=>		"integrate_transaction"
);

$_SOURCE_CHANNEL_MAPPING_ = array(
	 "dear"								=>	"inventory"
	,"vend"								=>	"pos"
	,"microsoft-dynamics-great-plains"	=>	"finance"
);