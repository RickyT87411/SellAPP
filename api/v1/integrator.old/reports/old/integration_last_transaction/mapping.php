<?php

global $_FIELD_MAPPING_;

$_FIELD_MAPPING_ = array(
	 "_lhs_host"					=>		"source_host"
	,"_lhs_instance"				=>		"source_host_instance"
	,"_lhs_transaction_type"		=>		"source_type"
	,"_lhs_transaction_status"		=>		"source_status"
	,"_rhs_host"					=>		"target_host"
	,"_rhs_instance"				=>		"target_host_instance"
	,"_rhs_transaction_type"		=>		"target_type"
	,"_rhs_transaction_status"		=>		"target_status"
	,"_broker"						=>		"broker"
	,"_include_failures"			=>		"include_failures"
);

?>