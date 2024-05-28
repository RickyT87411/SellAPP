<?php

global $_GET_FIELD_MAPPING_;
global $_PUT_FIELD_MAPPING_;

$_GET_FIELD_MAPPING_ = array(
	 "_batch_hash"						=> 		"batch"
);

$_PUT_FIELD_MAPPING_ = array(
	 "_batch_hash"							=>		"batch"
	,"_great_plains_transaction_id"			=>		"id"
	,"_great_plains_transaction_type"		=>		"type"
	,"_great_plains_transaction_sub_type"	=>		"sub_type"
	,"_great_plains_transaction_title"		=>		"title"
	,"_great_plains_transaction_status"		=>		"status"
);

