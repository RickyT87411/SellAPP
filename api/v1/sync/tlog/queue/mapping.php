<?php

global $_POST_FIELD_MAPPING_;
global $_ENUM_TLOG_HEADER_TYPE_;

/** POST Fields **/

$_GET_FIELD_MAPPING_ = array(
	 "_tlog_header_type" => "tlog_header_type"
	,"_transaction_date_from" => "date_from"
	,"_transaction_date_to" => "date_to"
	,"_transaction_updated_from" => "updated_from"
	,"_transaction_updated_to" => "updated_to"
	,"_process_transaction_date_csv" => "process_date_csv"
	,"_process_transaction_channel_csv" => "process_channel_csv"
	,"_process_transaction_source_csv" => "process_source_csv"
	,"_process_transaction_source_instance_csv" => "process_source_instance_csv"
	,"_process_transaction_sub_type_csv" => "process_sub_type_csv"
	,"_process_transaction_parent_id_csv" => "process_source_parent_id_csv"
	,"_process_transaction_id_csv" => "process_source_id_csv"
	,"_process_transaction_title_csv" => "process_title_csv"
	,"_process_transaction_status_csv" => "process_status_csv"
	,"_process_location_code_csv" => "process_location_csv"
	,"_process_outlet_code_csv" => "process_outlet_csv"
	,"_ignore_transaction_date_csv" => "ignore_date_csv"
	,"_ignore_transaction_channel_csv" => "ignore_channel_csv"
	,"_ignore_transaction_source_csv" => "ignore_source_csv"
	,"_ignore_transaction_source_instance_csv" => "ignore_source_instance_csv"
	,"_ignore_transaction_sub_type_csv" => "ignore_sub_type_csv"
	,"_ignore_transaction_parent_id_csv" => "ignore_source_parent_id_csv"
	,"_ignore_transaction_id_csv" => "ignore_source_id_csv"
	,"_ignore_transaction_title_csv" => "ignore_title_csv"
	,"_ignore_transaction_status_csv" => "ignore_status_csv"
	,"_ignore_location_code_csv" => "ignore_location_csv"
	,"_ignore_outlet_code_csv" => "ignore_outlet_csv"
	,"_in_sync" => "in_sync"
	,"_asc" => "asc"
);

$_POST_FIELD_MAPPING_ = array(
	 "_tlog_header_type" => "tlog_header_type"
	,"_transaction_date_from" => "date_from"
	,"_transaction_date_to" => "date_to"
	,"_transaction_updated_from" => "updated_from"
	,"_transaction_updated_to" => "updated_to"
	,"_process_transaction_date_csv" => "process_date_csv"
	,"_process_transaction_channel_csv" => "process_channel_csv"
	,"_process_transaction_source_csv" => "process_source_csv"
	,"_process_transaction_source_instance_csv" => "process_source_instance_csv"
	,"_process_transaction_sub_type_csv" => "process_sub_type_csv"
	,"_process_transaction_parent_id_csv" => "process_source_parent_id_csv"
	,"_process_transaction_id_csv" => "process_source_id_csv"
	,"_process_transaction_title_csv" => "process_title_csv"
	,"_process_transaction_status_csv" => "process_status_csv"
	,"_process_location_code_csv" => "process_location_csv"
	,"_process_outlet_code_csv" => "process_outlet_csv"
	,"_ignore_transaction_date_csv" => "ignore_date_csv"
	,"_ignore_transaction_channel_csv" => "ignore_channel_csv"
	,"_ignore_transaction_source_csv" => "ignore_source_csv"
	,"_ignore_transaction_source_instance_csv" => "ignore_source_instance_csv"
	,"_ignore_transaction_sub_type_csv" => "ignore_sub_type_csv"
	,"_ignore_transaction_parent_id_csv" => "ignore_source_parent_id_csv"
	,"_ignore_transaction_id_csv" => "ignore_source_id_csv"
	,"_ignore_transaction_title_csv" => "ignore_title_csv"
	,"_ignore_transaction_status_csv" => "ignore_status_csv"
	,"_ignore_location_code_csv" => "ignore_location_csv"
	,"_ignore_outlet_code_csv" => "ignore_outlet_csv"
	,"_in_sync" => "in_sync"
	,"_asc" => "asc"
);

$_ENUM_TLOG_HEADER_TYPE_ = array(
	 "purchase"
	,"sale"
	,"stock"
);