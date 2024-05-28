<?php
define("_API_POST_TRANSACTION_QUERY_URL_", "https://prod.playbill.com.au/api/v1/tlog/QUERY/");

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

global $_GET_FIELD_MAPPING_;
global $_POST_FIELD_MAPPING_;
global $_DELETE_FIELD_MAPPING_;

/** GET Fields **/

$_GET_FIELD_MAPPING_ = array(
	 "_transaction_date_from" 	=> "transaction_date_from"
	,"_transaction_date_to" 	=> "transaction_date_to"
	,"_tlog_header_type" 		=> "tlog_header_type"
	,"_location_code" 			=> "location_code"
	,"_since" 					=> "since"
	,"_status" 					=> "status"
);

/** POST Fields **/

$_TLOG_HEADER_OBJECT_ = array(
	 "_tlog_header_type" => "tlog_header_type"
	,"_id" => "id"
	,"_grouping_id" => "aggregate_id"
	,"_update_if_exists" => "update_if_exists"
	,"_channel" => "channel"
	,"_source" => "source"
	,"_source_instance" => "source_instance"
	,"_type" => "type"
	,"_status" => "status"
	,"_reference" => "reference"
	,"_class" => "class"
	,"_category" => "category"
	,"_date" => "date"
	,"_required_by" => "required_by"
	,"_fulfilled_at" => "fulfilled_at"
	,"_created_at" => "created_at"
	,"_updated_at" => "updated_at"
	,"_timezone" => "timezone"
	,"_currency_from" => "currency_from"
	,"_currency_to" => "currency_to"
	,"_exchange_rate" => "exchange_rate"
	,"_exchange_rate_at" => "exchange_rate_at"
	,"_total_quantity_in" => "total_quantity_in"
	,"_total_quantity_out" => "total_quantity_out"
	,"_total_price" => "total_price"
	,"_total_discount" => "total_discount"
	,"_total_cost" => "total_cost"
	,"_subtotal" => "subtotal"
	,"_tax" => "tax"
	,"_total" => "total"
	,"_paid" => "paid"
	,"_global_discount" => "global_discount"
	,"_return_for_tlog_id" => "return_for_tlog_id"
	,"_return_for_transaction_id" => "return_for_transaction_id"
	,"_return_for_transaction_reference" => "return_for_transaction_reference"
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
	,"_note" => "note"
);

$_TLOG_LINE_OBJECT_ = array(
	 "_tlog_line_type" => "tlog_line_type"
	,"_id" => "id"
	,"_sequence" => "sequence"
	,"_grouping_id" => "aggregate_id"
	,"_reference" => "reference"
	,"_status" => "status"
	,"_consignment_type" => "consignment_type"
	,"_unit_cost" => "unit_cost"
	,"_unit_price" => "unit_price"
	,"_unit_discount" => "unit_discount"
	,"_unit_tax" => "unit_tax"
	,"_quantity" => "quantity"
	,"_cost" => "cost"
	,"_price" => "price"
	,"_discount" => "discount"
	,"_tax" => "tax"
	,"_subtotal" => "subtotal"
	,"_total" => "total"
	,"_charitable" => "charitable"
	,"_free" => "free"
	,"_quantity_ordered" => "quantity_ordered"
	,"_quantity_picked" => "quantity_picked"
	,"_quantity_packed" => "quantity_packed"
	,"_quantity_fulfilled" => "quantity_fulfilled"
	,"_quantity_received" => "quantity_received"
	,"_required_by" => "required_by"
	,"_product_object" => "product"
	,"_discount_object" => "discount_reason"
	,"_return_object" => "return_reason"
	,"_void_object" => "void_reason"
	,"_promotion_object" => "promotion"
	,"_transfer_object" => "transfer"
	,"_dispatch_object" => "dispatch"
	,"_royalty_object" => "royalty"
	,"_commission_object" => "commission"
	,"_profit_share_object" => "profit_share"
	,"_finance_objects" => "finance_objects"
	,"_note" => "note"
);

$_TLOG_TENDER_OBJECT_ = array(
	 "_tlog_tender_type" => "tlog_tender_type"
	,"_id" => "id"
	,"_sequence" => "sequence"
	,"_grouping_id" => "aggregate_id"
	,"_reference" => "reference"
	,"_status" => "status"
	,"_amount" => "amount"
	,"_surcharge_object" => "surcharge"
	,"_fee_object" => "fee"
	,"_tender_detail_object" => "tender"
	,"_finance_objects" => "finance_objects"
	,"_note" => "note"
);

$_TLOG_FINANCE_OBJECT_ = array(
	 "_tlog_parent_type" => "tlog_parent_type"
	,"_tlog_parent_id" => "tlog_parent_id"
	,"_sequence" => "sequence"
	,"_financial_id" => "financial_id"
	,"_financial_code" => "financial_reference"
	,"_financial_type" => "financial_type"
	,"_financial_entity" => "financial_entity"
	,"_financial_location" => "financial_location"
	,"_financial_business_unit" => "financial_business_unit"
	,"_financial_division" => "financial_division"
	,"_account_id" => "account_id"
	,"_account_type" => "account_type"
	,"_account_sub_type" => "account_sub_type"
	,"_account_reference" => "account_reference"
	,"_account_currency" => "account_currency"
	,"_account_title" => "account_name"
	,"_account_division" => "account_division"
	,"_bankable" => "bankable"
	,"_bank_id" => "bank_id"
	,"_bank_type" => "bank_type"
	,"_bank_code" => "bank_reference"
	,"_bank_title" => "bank_name"
	,"_bank_account_number" => "bank_account_number"
	,"_creditor_id" => "creditor_id"
	,"_creditor_code" => "creditor_reference"
	,"_creditor_title" => "creditor_name"
	,"_creditor_type" => "creditor_type"
	,"_creditor_account_payable_id" => "creditor_account_payable_id"
	,"_creditor_account_payable_code" => "creditor_account_payable_code"
	,"_debtor_id" => "debtor_id"
	,"_debtor_code" => "debtor_reference"
	,"_debtor_title" => "debtor_name"
	,"_debtor_type" => "debtor_type"
	,"_debtor_account_receivable_id" => "debtor_account_receivable_id"
	,"_debtor_account_receivable_code" => "debtor_account_receivable_code"
	,"_description" => "description"
	,"_debit" => "debit"
	,"_credit" => "credit"
	,"_note" => "note"
);

$_USER_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
);

$_CUSTOMER_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_type" => "type"
	,"_finance_object" => "finance_object"
);

$_SUPPLIER_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_type" => "type"
	,"_finance_object" => "finance_object"
);

$_LOCATION_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_type" => "type"
	,"_address_type" => "address_type"
	,"_address_1" => "address_1"
	,"_address_2" => "address_2"
	,"_suburb" => "suburb"
	,"_city" => "city"
	,"_postal_code" => "postal_code"
	,"_state" => "state"
	,"_country_code" => "country_code"
);

$_OUTLET_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_type" => "type"
);

$_REGISTER_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_type" => "type"
);

$_DISCOUNT_REASON_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
);

$_RETURN_REASON_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
);

$_VOID_REASON_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
);

$_PROMOTION_OBJECT_ = array(
	 "_code" => "code"
	,"_tracking_id" => "tracking_id"
);

$_PRODUCT_OBJECT_ = array(
	 "_inventory_id" => "inventory_id"
	,"_stock_locator_id" => "stock_locator_id"
	,"_id" => "id"
	,"_sku" => "sku"
	,"_barcode" => "barcode"
	,"_code" => "reference"
	,"_title" => "name"
	,"_product_family_object" => "family"
	,"_class" => "class"
	,"_department" => "department"
	,"_brand" => "brand"
	,"_business_stream" => "business_stream"
	,"_category_1" => "category_1"
	,"_category_2" => "category_2"
	,"_category_3" => "category_3"
	,"_category_4" => "category_4"
	,"_category_5" => "category_5"
	,"_category_6" => "category_6"
	,"_category_7" => "category_7"
	,"_category_8" => "category_8"
	,"_category_9" => "category_9"
	,"_category_10" => "category_10"
	,"_hierarchy_id" => "hierarchy_id"
	,"_year" => "year"
	,"_season" => "season"
	,"_variant_1_name" => "variant_1_name"
	,"_variant_2_name" => "variant_2_name"
	,"_variant_3_name" => "variant_3_name"
	,"_variant_4_name" => "variant_4_name"
	,"_variant_5_name" => "variant_5_name"
	,"_variant_1" => "variant_1"
	,"_variant_2" => "variant_2"
	,"_variant_3" => "variant_3"	
	,"_variant_4" => "variant_4"
	,"_variant_5" => "variant_5"
	,"_uom" => "uom"
	,"_retail_price" => "retail_price"
	,"_wholesale_price" => "wholesale_price"
	,"_supplier_object" => "supplier_object"
	,"_category_objects" => "categories"
	,"_attribute_objects" => "attributes"
);

$_PRODUCT_FAMILY_OBJECT_ = array(
	 "_id" => "id"
	,"_sku" => "sku"
	,"_code" => "reference"
	,"_title" => "name"
);

$_SUPPLIER_PRODUCT_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_product_id" => "product_id"
	,"_product_code" => "product_code"
	,"_reorder_code" => "reorder_code"
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
	 "_amount" => "amount"
	,"_percentage" => "percentage"
);

$_ROYALTY_OBJECT_ = array(
	 "_amount" => "amount"
	,"_percentage" => "percentage"
);

$_PROFIT_SHARE_OBJECT_ = array(
	 "_amount" => "amount"
	,"_percentage" => "percentage"
);

$_SURCHARGE_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_fee" => "amount"
	,"_tax" => "tax"
);

$_FEE_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_fee" => "amount"
	,"_tax" => "tax"
);

$_TRANSFER_OBJECT_ = array(
	 "_source_location_object" => "source"
	,"_target_location_object" => "target"
);

$_DISPATCH_OBJECT_ = array(
	 "_courier" => "courier"
	,"_tracking_url" => "tracking_url"
	,"_tracking_number" => "tracking_number"
	,"_delivery_required_by" => "delivery_required_by"
	,"_delivery_expected_by" => "delivery_expected_by"
	,"_delivery_received_at" => "delivery_received_at"
);

$_TENDER_DETAIL_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_type" => "type"
	,"_issuer_object" => "issuer"
	,"_card_object" => "card"
	,"_payment_object" => "payment"
	,"_banking_object" => "banking"
	,"_finance_objects" => "finance_objects"
);

$_ISSUER_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
);

$_CARD_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_type" => "type"
	,"_holder" => "holder"
	,"_number" => "number"
	,"_expiry_month" => "expiry_month"
	,"_expiry_year" => "expiry_year"
	,"_cvv" => "cvv"
	,"_pin" => "pin"
	,"_balance" => "balance"
);

$_PAYMENT_OBJECT_ = array(
	 "_authorization_type" => "authorization_type"
	,"_authorized" => "authorized"
	,"_authorized_at" => "authorized_at"
	,"_token" => "token"
	,"_provider_object" => "provider"
);

$_PROVIDER_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_gateway_object" => "gateway"
);

$_GATEWAY_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
	,"_auth_object" => "auth"
);

$_AUTH_OBJECT_ = array(
	 "_id" => "id"
	,"_code" => "reference"
	,"_title" => "name"
);

$_BANKING_OBJECT_ = array(
	 "_expected" => "expected"
	,"_counted" => "counted"
	,"_variance" => "variance"
	,"_committed" => "committed"
	,"_name" => "name"
	,"_finance_objects" => "finance_objects"
);

$_ENUM_TLOG_HEADER_TYPE_ = array(
	 "purchase"
	,"sale"
	,"stock"
);

$_ENUM_TLOG_LINE_TYPE_ = array(
	 "in"
	,"out"
);

$_ENUM_TLOG_TENDER_TYPE_ = array(
	 "payment"
	,"banking"
	,"enquiry"
);

$_ENUM_HEADER_CLASS_TYPE_ = array(
	 "stock"
	,"service"
	,"mixed"
);

$_ENUM_PRODUCT_CLASS_TYPE_ = array(
	 "stock"
	,"service"
	,"noninventory"
	,"asset"
	,"modifier"
	,"other"
);

$_ENUM_CONSIGNMENT_TYPE_ = array(
	 "consignment"
	,"sale-or-return"
	,"stock-on-hand"
);

$_ENUM_PAYMENT_TYPE_ = array(
	 "auto"
	,"AUTO"
	,"manual"
	,"MANUAL"
	,"pin"
	,"PIN"
	,"swipe"
	,"SWIPE"
	,"emv"
	,"EMV"
	,"signature"
	,"SIGNATURE"
	,"paypass"
	,"PAYPASS"
	,"gateway"
	,"GATEWAY"
	,"nfc"
	,"NFC"
	,"token"
	,"TOKEN"
	,"custom"
	,"CUSTOM"
	,"unverified"
	,"UNVERIFIED"
	,"mixed"
	,"MIXED"
);