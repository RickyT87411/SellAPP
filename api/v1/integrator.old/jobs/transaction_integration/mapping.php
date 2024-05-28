<?php

global $_FIELD_MAPPING_;
global $_ENUM_INTEGRATION_STATUS_;
global $_ENUM_TRANSMISSION_TYPE_;

$_FIELD_MAPPING_ = array(
	 "_lhs_host"				=>		"source_host"
	,"_lhs_host_instance"		=>		"source_host_instance"
	,"_lhs_uri"					=>		"source_host_uri"
	,"_lhs_type"				=>		"source_type"
	,"_lhs_id"					=>		"source_id"
	,"_lhs_title"				=>		"source_title"
	,"_lhs_status"				=>		"source_status"
	,"_rhs_host"				=>		"target_host"
	,"_rhs_host_instance"		=>		"target_host_instance"
	,"_rhs_uri"					=>		"target_host_uri"
	,"_rhs_type"				=>		"target_type"
	,"_rhs_id"					=>		"target_id"
	,"_rhs_title"				=>		"target_title"
	,"_rhs_status"				=>		"target_status"
	,"_transmission_type"		=>		"transmission_type"
	,"_broker"					=>		"broker"
	,"_broker_uri"				=>		"broker_uri"
	,"_broker_job_id"			=>		"broker_job_id"
	,"_broker_job_instance_id"	=>		"broker_job_instance_id"
	,"_result"					=>		"result_code"
	,"_result_description"		=>		"result_description"
	,"_executed_at"				=>		"executed_at"
	,"_integration_status"		=>		"integration_status"
	,"integration_id"			=>		"integration_id"
);

$_ENUM_INTEGRATION_STATUS_ = array(
	 "pending"
	,"extracting"
	,"extracted"
	,"processing"
	,"processed"
	,"exporting"
	,"exported"
	,"translating"
	,"translated"
	,"wanted"
	,"skipped"
	,"extraction-error"
	,"processing-error"
	,"export-error"
	,"translate-error"
	,"in-sync"
	,"duplicate"
);

$_ENUM_TRANSMISSION_TYPE_ = array(
	 "inbound"
	,"outbound"
	,"bidirectional"
);