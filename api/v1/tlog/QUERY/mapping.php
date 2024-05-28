<?php

global $_TLOG_HEADER_OBJECT_;		// DONE
global $_TLOG_LINE_OBJECT_;			// DONE
global $_TLOG_TENDER_OBJECT_;		// DONE
global $_TLOG_FINANCE_OBJECT_;		// DONE

/** Header Objects **/
global $_USER_OBJECT_;				// DONE
global $_CUSTOMER_OBJECT_;			// DONE
global $_SUPPLIER_OBJECT_;			// DONE
global $_LOCATION_OBJECT_;			// DONE
global $_OUTLET_OBJECT_;			// DONE
global $_REGISTER_OBJECT_;			// DONE
global $_TRANSFER_OBJECT_;			// DONE
global $_TRANSFER_SOURCE_OBJECT_;	// DONE
global $_TRANSFER_TARGET_OBJECT_;	// DONE

/** Line Objects **/
global $_PRODUCT_OBJECT_;			// DONE
global $_PRODUCT_FAMILY_OBJECT_;	// DONE
global $_CATEGORY_OBJECT_;			// DONE
global $_ATTRIBUTE_OBJECT_;			// DONE
global $_SUPPLIER_PRODUCT_OBJECT_;	// DONE
global $_RETURN_REASON_OBJECT_;		// DONE
global $_DISCOUNT_REASON_OBJECT_;	// DONE
global $_VOID_REASON_OBJECT_;		// DONE
global $_PROMOTION_OBJECT_;			// DONE
global $_DISPATCH_OBJECT_;			// DONE
global $_COMMISSION_OBJECT_;		// DONE
global $_ROYALTY_OBJECT_;			// DONE
global $_PROFIT_SHARE_OBJECT_;		// DONE

/** Tender Objects **/
global $_TENDER_DETAIL_OBJECT_;		// DONE
global $_SURCHARGE_OBJECT_;			// DONE
global $_FEE_OBJECT_;				// DONE
global $_ISSUER_OBJECT_;			// DONE
global $_CARD_OBJECT_;				// DONE
global $_PAYMENT_OBJECT_;			// DONE
global $_PROVIDER_OBJECT_;			// DONE
global $_GATEWAY_OBJECT_;			// DONE
global $_AUTH_OBJECT_;				// DONE
global $_BANKING_OBJECT_;			// DONE

global $_POST_FIELD_MAPPING_;

global $_ENUM_QUERY_TLOG_HEADER_TYPE_;
global $_ENUM_QUERY_STATUS_;
global $_ENUM_QUERY_ORDER_BY_;

/** POST Fields **/

$_POST_FIELD_MAPPING_ = array(
	 "_tlog_header_type" => "tlog_header_type"
	,"_transaction_date_from" => "date_from"
	,"_transaction_date_to" => "date_to"
	,"_transaction_created_from" => "transaction_created_from"
	,"_transaction_created_to" => "transaction_created_to"
	,"_transaction_updated_from" => "transaction_updated_from"
	,"_transaction_updated_to" => "transaction_updated_to"
	,"_process_transaction_date_csv" => "process_date_csv"
	,"_process_transaction_channel_csv" => "process_channel_csv"
	,"_process_transaction_source_csv" => "process_source_csv"
	,"_process_transaction_source_instance_csv" => "process_source_instance_csv"
	,"_process_transaction_type_csv" => "process_type_csv"
	,"_process_transaction_id_csv" => "process_source_id_csv"
	,"_process_transaction_title_csv" => "process_title_csv"
	,"_process_transaction_status_csv" => "process_status_csv"
	,"_process_location_type_csv" => "process_location_type_csv"
	,"_process_location_code_csv" => "process_location_code_csv"
	,"_process_outlet_type_csv" => "process_outlet_type_csv"
	,"_process_outlet_code_csv" => "process_outlet_code_csv"
	,"_process_register_type_csv" => "process_register_type_csv"
	,"_process_register_code_csv" => "process_register_code_csv"
	,"_process_customer_type_csv" => "process_customer_type_csv"
	,"_process_customer_code_csv" => "process_customer_code_csv"
	,"_process_supplier_type_csv" => "process_supplier_type_csv"
	,"_process_supplier_code_csv" => "process_supplier_code_csv"
	,"_process_user_code_csv" => "process_user_code_csv"
	,"_ignore_transaction_date_csv" => "ignore_date_csv"
	,"_ignore_transaction_channel_csv" => "ignore_channel_csv"
	,"_ignore_transaction_source_csv" => "ignore_source_csv"
	,"_ignore_transaction_source_instance_csv" => "ignore_source_instance_csv"
	,"_ignore_transaction_type_csv" => "ignore_type_csv"
	,"_ignore_transaction_id_csv" => "ignore_source_id_csv"
	,"_ignore_transaction_title_csv" => "ignore_title_csv"
	,"_ignore_transaction_status_csv" => "ignore_status_csv"
	,"_ignore_location_type_csv" => "ignore_location_type_csv"
	,"_ignore_location_code_csv" => "ignore_location_code_csv"
	,"_ignore_outlet_type_csv" => "ignore_outlet_type_csv"
	,"_ignore_outlet_code_csv" => "ignore_outlet_code_csv"
	,"_ignore_register_type_csv" => "ignore_register_type_csv"
	,"_ignore_register_code_csv" => "ignore_register_code_csv"
	,"_ignore_customer_type_csv" => "ignore_customer_type_csv"
	,"_ignore_customer_code_csv" => "ignore_customer_code_csv"
	,"_ignore_supplier_type_csv" => "ignore_supplier_type_csv"
	,"_ignore_supplier_code_csv" => "ignore_supplier_code_csv"
	,"_ignore_user_code_csv" => "ignore_user_code_csv"
	,"_since" => "since"
	,"_include_audit_trail" => "include_audit_trail"
	,"_status" => "status"
	,"_order_by" => "order_by"
	,"_asc" => "asc"
	,"__limit" => "limit"
	,"__page" => "page"

);

$_ENUM_QUERY_TLOG_HEADER_TYPE_ = array(
	 "purchase"
	,"sale"
	,"stock"
);

$_ENUM_QUERY_STATUS_ = array(
	  "unfulfilled"
	 ,"undelivered"
	 ,"complete"
	 ,"backordered"
	 ,"refund"
	 ,"return"
	 ,"exchange"
	 ,"discounted"
	 ,"unpaid"
);

$_ENUM_QUERY_ORDER_BY_ = array(
	 "channel"
	,"source"
	,"source_instance"
	,"tlog_header_type"
	,"type"
	,"source_id"
	,"title"
	,"status"
	,"date"
	,"required_by"
	,"fulfilled_at"
	,"created_at"
	,"updated_at"
	,"tlog_created_at"
	,"tlog_updated_at"
);


/* ========================== */
/** Mapping to Output Format **/
/* ========================== */
global $_TLOG_HEADER_OBJECT_;	// DONE
global $_TLOG_LINE_OBJECT_;		// DONE
global $_TLOG_TENDER_OBJECT_;	// DONE
global $_TLOG_FINANCE_OBJECT_;	// DONE

/** Header Objects **/
global $_USER_OBJECT_;			// DONE
global $_CUSTOMER_OBJECT_;		// DONE
global $_SUPPLIER_OBJECT_;		// DONE
global $_LOCATION_OBJECT_;		// DONE
global $_OUTLET_OBJECT_;		// DONE
global $_REGISTER_OBJECT_;		// DONE
global $_TRANSFER_OBJECT_;		// DONE

/** Line Objects **/
global $_PRODUCT_OBJECT_;			// DONE
global $_PRODUCT_FAMILY_OBJECT_;	// DONE
global $_CATEGORY_OBJECT_;			// DONE
global $_ATTRIBUTE_OBJECT_;			// DONE
global $_SUPPLIER_PRODUCT_OBJECT_;	// DONE
global $_RETURN_REASON_OBJECT_;		// DONE
global $_DISCOUNT_REASON_OBJECT_;	// DONE
global $_VOID_REASON_OBJECT_;		// DONE
global $_PROMOTION_OBJECT_;			// DONE
global $_DISPATCH_OBJECT_;			// DONE
global $_COMMISSION_OBJECT_;		// DONE
global $_ROYALTY_OBJECT_;			// DONE
global $_PROFIT_SHARE_OBJECT_;		// DONE

/** Tender Objects **/
global $_TENDER_DETAIL_OBJECT_;		// DONE
global $_SURCHARGE_OBJECT_;			// DONE
global $_FEE_OBJECT_;				// DONE
global $_ISSUER_OBJECT_;			// DONE
global $_CARD_OBJECT_;				// DONE
global $_PAYMENT_OBJECT_;			// DONE
global $_PROVIDER_OBJECT_;			// DONE
global $_GATEWAY_OBJECT_;			// DONE
global $_AUTH_OBJECT_;				// DONE
global $_BANKING_OBJECT_;			// DONE

global $_ENUM_TLOG_HEADER_TYPE_;	// DONE
global $_ENUM_TLOG_LINE_TYPE_;		// DONE
global $_ENUM_TLOG_TENDER_TYPE_;	// DONE
global $_ENUM_CONSIGNMENT_TYPE_;	// DONE

$_TLOG_HEADER_OBJECT_ = array(
	 "id" => "tlog_header_id"
	,"tlog_header_type" => "tlog_header_type"
	,"system_created_at" => "tlog_created_at"
	,"system_updated_at" => "tlog_updated_at"
	,"grouping_id" => "aggregate_id"
	,"update_if_exists" => "update_if_exists"
	,"channel" => "channel"
	,"source" => "source"
	,"source_instance" => "source_instance"
	,"transaction_id" => "source_id"
	,"transaction_type" => "type"
	,"transaction_status" => "status"
	,"transaction_title" => "reference"
	,"transaction_class" => "class"
	,"transaction_category" => "category"
	,"transaction_date" => "date"
	,"transaction_required_by_date" => "required_by"
	,"transaction_fulfilment_date" => "fulfilled_at"
	,"transaction_created_at" => "created_at"
	,"transaction_updated_at" => "updated_at"
	,"transaction_timezone" => "timezone"
	,"source_currency" => "currency_from"
	,"target_currency" => "currency_to"
	,"exchange_rate" => "exchange_rate"
	,"exchange_rate_at" => "exchange_rate_at"
	,"total_quantity_in" => "total_quantity_in"
	,"total_quantity_out" => "total_quantity_out"
	,"total_price" => "total_price"
	,"total_discount" => "total_discount"
	,"total_cost" => "total_cost"
	,"subtotal" => "subtotal"
	,"tax" => "tax"
	,"total" => "total"
	,"paid" => "paid"
	,"global_discount" => "global_discount"
	,"return_for_tlog_id" => "return_for_tlog_id"
	,"return_for_transaction_id" => "return_for_transaction_id"
	,"return_for_transaction_title" => "return_for_transaction_reference"
	,"_customer_object" => "customer"
	,"_user_object" => "user"
	,"_supplier_object" => "supplier"
	,"_location_object" => "location"
	,"_outlet_object" => "outlet"
	,"_register_object" => "register"
	,"_transfer_object" => "transfer"
	,"_dispatch_object" => "dispatch"
	,"_tlog_lines_object" => "lines"
	,"_tlog_tenders_object" => "tenders"
	,"note" => "note"
);

$_TLOG_LINE_OBJECT_ = array(
	 "id" => "tlog_line_id"
	,"tlog_line_type" => "tlog_line_type"
	,"line_sequence" => "sequence"
	,"grouping_id" => "aggregate_id"
	,"line_id" => "reference"
	,"line_status" => "status"
	,"consignment_type" => "consignment_type"
	,"unit_cost" => "unit_cost"
	,"unit_price" => "unit_price"
	,"unit_discount" => "unit_discount"
	,"unit_tax" => "unit_tax"
	,"quantity" => "quantity"
	,"cost" => "cost"
	,"price" => "price"
	,"discount" => "discount"
	,"tax" => "tax"
	,"subtotal" => "subtotal"
	,"total" => "total"
	,"charitable" => "charitable"
	,"free" => "free"
	,"quantity_ordered" => "quantity_ordered"
	,"quantity_picked" => "quantity_picked"
	,"quantity_packed" => "quantity_packed"
	,"quantity_fulfilled" => "quantity_fulfilled"
	,"quantity_received" => "quantity_received"
	,"required_by" => "required_by"
	,"_product_object" => "product"
	,"_discount_object" => "discount_reason"
	,"_return_object" => "return_reason"
	,"_void_object" => "void_reason"
	,"_promotion_object" => "promotion"
	,"_dispatch_object" => "dispatch"
	,"_royalty_object" => "royalty"
	,"_commission_object" => "commission"
	,"_profit_share_object" => "profit_share"
	,"_transfer_object" => "transfer"
	,"_finance_objects" => "finance_objects"
	,"note" => "note"
);

$_TLOG_TENDER_OBJECT_ = array(
	 "id" => "tlog_tender_id"
	,"tlog_tender_type" => "tlog_tender_type"
	,"line_sequence" => "sequence"
	,"grouping_id" => "aggregate_id"
	,"line_id" => "reference"
	,"line_status" => "status"
	,"amount" => "amount"
	,"_surcharge_object" => "surcharge"
	,"_fee_object" => "fee"
	,"_tender_detail_object" => "tender"
	,"_finance_objects" => "finance_objects"
	,"note" => "note"
);

$_TLOG_FINANCE_OBJECT_ = array(
	 "id" => "tlog_finance_object_id"
	,"tlog_parent_type" => "tlog_parent_type"
	,"tlog_parent_id" => "tlog_parent_id"
	,"sequence" => "sequence"
	,"financial_id" => "financial_id"
	,"financial_code" => "financial_reference"
	,"financial_type" => "financial_type"
	,"financial_entity" => "financial_entity"
	,"financial_location" => "financial_location"
	,"financial_business_unit" => "financial_business_unit"
	,"financial_division" => "financial_division"
	,"account_id" => "account_id"
	,"account_type" => "account_type"
	,"account_sub_type" => "account_sub_type"
	,"account_reference" => "account_reference"
	,"account_currency" => "account_currency"
	,"account_title" => "account_name"
	,"account_division" => "account_division"
	,"bank_id" => "bank_id"
	,"bank_type" => "bank_type"
	,"bank_code" => "bank_reference"
	,"bank_title" => "bank_name"
	,"bank_account_number" => "bank_account_number"
	,"creditor_id" => "creditor_id"
	,"creditor_code" => "creditor_reference"
	,"creditor_title" => "creditor_name"
	,"creditor_type" => "creditor_type"
	,"creditor_account_payable_id" => "creditor_account_payable_id"
	,"creditor_account_payable_code" => "creditor_account_payable_code"
	,"debtor_id" => "debtor_id"
	,"debtor_code" => "debtor_reference"
	,"debtor_title" => "debtor_name"
	,"debtor_type" => "debtor_type"
	,"debtor_account_receivable_id" => "debtor_account_receivable_id"
	,"debtor_account_receivable_code" => "debtor_account_receivable_code"
	,"description" => "description"
	,"debit" => "debit"
	,"credit" => "credit"
	,"note" => "note"
);

$_USER_OBJECT_ = array(
	 "user_id" => "id"
	,"user_code" => "reference"
	,"user_title" => "name"
);

$_CUSTOMER_OBJECT_ = array(
	 "customer_id" => "id"
	,"customer_code" => "reference"
	,"customer_title" => "name"
	,"customer_type" => "type"
);

$_SUPPLIER_OBJECT_ = array(
	 "supplier_id" => "id"
	,"supplier_code" => "reference"
	,"supplier_title" => "name"
	,"supplier_type" => "type"
);

$_LOCATION_OBJECT_ = array(
	 "location_id" => "id"
	,"location_code" => "reference"
	,"location_title" => "name"
	,"location_type" => "type"
);

$_OUTLET_OBJECT_ = array(
	 "outlet_id" => "id"
	,"outlet_code" => "reference"
	,"outlet_title" => "name"
	,"outlet_type" => "type"
);

$_REGISTER_OBJECT_ = array(
	 "register_id" => "id"
	,"register_code" => "reference"
	,"register_title" => "name"
	,"register_type" => "type"
);

$_DISCOUNT_REASON_OBJECT_ = array(
	 "discount_reason_id" => "id"
	,"discount_reason_code" => "reference"
	,"discount_reason_title" => "name"
);

$_RETURN_REASON_OBJECT_ = array(
	 "return_reason_id" => "id"
	,"return_reason_code" => "reference"
	,"return_reason_title" => "name"
);

$_VOID_REASON_OBJECT_ = array(
	 "void_reason_id" => "id"
	,"void_reason_code" => "reference"
	,"void_reason_title" => "name"
);

$_PROMOTION_OBJECT_ = array(
	 "promotion_code" => "code"
	,"promotion_tracking_id" => "tracking_id"
);

$_PRODUCT_OBJECT_ = array(
	 "inventory_id" => "inventory_id"
	,"stock_locator_id" => "stock_locator_id"
	,"product_id" => "id"
	,"product_sku" => "sku"
	,"product_barcode" => "barcode"
	,"product_code" => "reference"
	,"product_title" => "name"
	,"_product_family_object" => "family"
	,"product_class" => "class"
	,"department" => "department"
	,"brand" => "brand"
	,"business_stream" => "business_stream"
	,"category_1" => "category_1"
	,"category_2" => "category_2"
	,"category_3" => "category_3"
	,"category_4" => "category_4"
	,"category_5" => "category_5"
	,"category_6" => "category_6"
	,"category_7" => "category_7"
	,"category_8" => "category_8"
	,"category_9" => "category_9"
	,"category_10" => "category_10"
	,"year" => "year"
	,"season" => "season"
	,"hierarchy_id" => "hierarchy_id"
	,"variant_1_name" => "variant_1_name"
	,"variant_2_name" => "variant_2_name"
	,"variant_3_name" => "variant_3_name"
	,"variant_4_name" => "variant_4_name"
	,"variant_5_name" => "variant_5_name"
	,"variant_1" => "variant_1"
	,"variant_2" => "variant_2"
	,"variant_3" => "variant_3"
	,"variant_4" => "variant_4"
	,"variant_5" => "variant_5"
	,"uom" => "uom"
	,"retail_price" => "retail_price"
	,"wholesale_price" => "wholesale_price"
	,"_supplier_object" => "supplier_object"
	,"_category_objects" => "categories"
	,"_attribute_objects" => "attributes"
);

$_PRODUCT_FAMILY_OBJECT_ = array(
	 "product_family_id" => "id"
	,"product_family_sku" => "sku"
	,"product_family_code" => "reference"
	,"product_family_title" => "name"
);

$_SUPPLIER_PRODUCT_OBJECT_ = array(
	 "supplier_id" => "id"
	,"supplier_code" => "reference"
	,"supplier_title" => "name"
	,"supplier_product_id" => "product_id"
	,"supplier_product_code" => "product_code"
	,"supplier_reorder_code" => "reorder_code"
);

$_CATEGORY_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_value" => "value"
);

$_ATTRIBUTE_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_value" => "value"
);

$_COMMISSION_OBJECT_ = array(
	 "commission_amount" => "amount"
	,"commission_percentage" => "percentage"
);

$_ROYALTY_OBJECT_ = array(
	 "royalty_amount" => "amount"
	,"royalty_percentage" => "percentage"
);

$_PROFIT_SHARE_OBJECT_ = array(
	 "profit_share_amount" => "amount"
	,"profit_share_percentage" => "percentage"
);

$_SURCHARGE_OBJECT_ = array(
	 "surcharge" => "amount"
	,"surcharge_tax" => "tax"
);

$_FEE_OBJECT_ = array(
	 "fee" => "amount"
	,"fee_tax" => "tax"
);

$_TRANSFER_OBJECT_ = array(
	 "_source_location_object" => "source"
	,"_target_location_object" => "target"
);

$_TRANSFER_SOURCE_OBJECT_ = array(
	 "source_location_id" => "id"
	,"source_location_code" => "reference"
	,"source_location_title" => "name"
	,"source_location_type" => "type"
);

$_TRANSFER_TARGET_OBJECT_ = array(
     "target_location_id" => "id"
	,"target_location_code" => "reference"
	,"target_location_title" => "name"
	,"target_location_type" => "type"
);

$_DISPATCH_OBJECT_ = array(
	 "courier" => "courier"
	,"tracking_url" => "tracking_url"
	,"tracking_number" => "tracking_number"
	,"delivery_required_by" => "delivery_required_by"
	,"delivery_expected_by" => "delivery_expected_by"
	,"delivery_received_at" => "delivery_received_at"
);

$_TENDER_DETAIL_OBJECT_ = array(
	 "tender_id" => "id"
	,"tender_code" => "reference"
	,"tender_title" => "name"
	,"tender_type" => "type"
	,"_issuer_object" => "issuer"
	,"_card_object" => "card"
	,"_payment_object" => "payment"
	,"_banking_object" => "banking"
	,"_finance_objects" => "finance_objects"
);

$_ISSUER_OBJECT_ = array(
	 "tender_issuer_id" => "id"
	,"tender_issuer_code" => "reference"
	,"tender_issuer_title" => "name"
);

$_CARD_OBJECT_ = array(
	 "card_id" => "id"
	,"card_code" => "reference"
	,"card_title" => "name"
	,"card_holder" => "holder"
	,"card_number" => "number"
	,"card_expiry_month" => "expiry_month"
	,"card_expiry_year" => "expiry_year"
	,"card_cvv" => "cvv"
	,"card_pin" => "pin"
	,"card_balance" => "balance"
);

$_PAYMENT_OBJECT_ = array(
	 "payment_authorization_type" => "authorization_type"
	,"payment_authorized" => "authorized"
	,"payment_authorized_at" => "authorized_at"
	,"payment_token" => "token"
	,"_provider_object" => "provider"
);

$_PROVIDER_OBJECT_ = array(
	 "payment_provider_id" => "id"
	,"payment_provider_code" => "reference"
	,"payment_provider_title" => "name"
	,"_gateway_object" => "gateway"
);

$_GATEWAY_OBJECT_ = array(
	 "payment_gateway_id" => "id"
	,"payment_gateway_code" => "reference"
	,"payment_gateway_title" => "name"
	,"_auth_object" => "auth"
);

$_AUTH_OBJECT_ = array(
	 "payment_auth_id" => "id"
	,"payment_auth_code" => "reference"
	,"payment_auth_title" => "name"
);

$_BANKING_OBJECT_ = array(
	 "banking_amount_expected" => "expected"
	,"banking_amount_counted" => "counted"
	,"banking_variance" => "variance"
	,"banking_committed" => "committed"
	,"_finance_objects" => "finance_objects"
);