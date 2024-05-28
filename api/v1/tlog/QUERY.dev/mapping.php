<?php

global $_TLOG_RESULT_SCHEMA_;

global $_POST_FIELD_MAPPING_;

global $_ENUM_QUERY_TLOG_HEADER_TYPE_;
global $_ENUM_QUERY_STATUS_;
global $_ORDER_BY_ENUM;

/** POST Fields **/

$_POST_FIELD_MAPPING_ = array(
	 "_tlog_header_type" => "tlog_header_type"
	,"_transaction_date_from" => "transaction_date_from"
	,"_transaction_date_to" => "transaction_date_to"
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
	,"_include_finance_objects" => "include_finance_objects"
	,"__limit" => "limit"
	,"__page" => "page"

);

$_ENUM_QUERY_TLOG_HEADER_TYPE_ = array(
	 "purchase"
	,"sale"
	,"transfer"
	,"adjustment"
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

$_ORDER_BY_ENUM = array(
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

$_TLOG_RESULT_SCHEMA_ = 
'
{
	"__schema__": {
		"version": "2.0.2018.7.9"
	},
	"header": {
		"tlog_header_id": "string",
		"tlog_header_type": "string",
		"tlog_created_at": "date",
		"tlog_updated_at": "date",
		"update_if_exists": "boolean",
		"channel": "string",
		"source": "string",
		"source_instance": "string",
		"type": "string",
		"status": "string",
		"reference": "string",
		"id": "string",
		"grouping_id": "string",
		"class": "string",
		"category": "string",
		"date": "date",
		"required_by": "date",
		"fulfilled_at": "date",
		"created_at": "date",
		"updated_at": "date",
		"timezone": "string",
		"currency_from": "string",
		"currency_to": "string",
		"exchange_rate": "decimal",
		"exchange_rate_at": "date",
		"total_quantity_in": "decimal",
		"total_quantity_out": "decimal",
		"total_price": "decimal",
		"total_discount": "decimal",
		"total_cost": "decimal",
		"subtotal": "decimal",
		"tax": "decimal",
		"total": "decimal",
		"paid": "decimal",
		"global_discount": "decimal",
		"parent_transaction_id": "string",
		"parent_transaction_reference": "string",
		"parent_tlog_id": "string",
		"return_for_transaction_id": "string",
		"return_for_transaction_reference": "string",
		"return_for_tlog_id": "string",
		"customer": {
			"id": "string",
			"reference": "string",
			"name": "string",
			"type": "string",
			"finance_object": {
				"tlog_finance_object_id": "string",
				"sequence": "integer",
				"financial_id": "string",
				"financial_reference": "string",
				"financial_type": "string",
				"financial_entity": "string",
				"financial_location": "string",
				"financial_business_unit": "string",
				"financial_division": "string",
				"account_id": "string",
				"account_type": "string",
				"account_sub_type": "string",
				"account_reference": "string",
				"account_currency": "string",
				"account_name": "string",
				"account_division": "string",
				"debtor_id": "string",
				"debtor_reference": "string",
				"debtor_name": "string",
				"debtor_type": "string",
				"debtor_account_receivable_id": "string",
				"debtor_account_receivable_code": "string",
				"description": "string",
				"debit": "decimal",
				"credit": "decimal",
				"note": "string"
			}
		},
		"user": {
			"id": "string",
			"reference": "string",
			"name": "string"
		},
		"supplier": {
			"id": "string",
			"reference": "string",
			"name": "string",
			"type": "string",
			"finance_object": {
				"tlog_finance_object_id": "string",
				"sequence": "integer",
				"financial_id": "string",
				"financial_reference": "string",
				"financial_type": "string",
				"financial_entity": "string",
				"financial_location": "string",
				"financial_business_unit": "string",
				"financial_division": "string",
				"account_id": "string",
				"account_type": "string",
				"account_sub_type": "string",
				"account_reference": "string",
				"account_currency": "string",
				"account_name": "string",
				"account_division": "string",
				"creditor_id": "string",
				"creditor_reference": "string",
				"creditor_name": "string",
				"creditor_type": "string",
				"creditor_account_payable_id": "string",
				"creditor_account_payble_code": "string",
				"description": "string",
				"debit": "decimal",
				"credit": "decimal",
				"note": "string"
			}
		},
		"location": {
			"id": "string",
			"reference": "string",
			"name": "string",
			"type": "string"
		},
		"outlet": {
			"id": "string",
			"reference": "string",
			"name": "string",
			"type": "string"
		},
		"register": {
			"id": "string",
			"reference": "string",
			"name": "string",
			"type": "string"
		},
		"transfer": {
			"source": {
				"id": "string",
				"reference": "string",
				"name": "string",
				"type": "string"
			},
			"target": {
				"id": "string",
				"reference": "string",
				"name": "string",
				"type": "string"
			}
		},
		"dispatch": {
			"courier": "string",
			"tracking_url": "string",
			"tracking_number": "string",
			"required_by": "date",
			"expected_by": "date",
			"received_at": "date"
		},
		"lines": [
			{
				"tlog_line_id": "string",
				"tlog_line_type": "string",
				"id": "string",
				"sequence": "integer",
				"grouping_id": "string",
				"reference": "string",
				"status": "string",
				"consignment_type": "string",
				"unit_cost": "decimal",
				"unit_price": "decimal",
				"unit_discount": "decimal",
				"unit_tax": "decimal",
				"quantity": "decimal",
				"cost": "decimal",
				"price": "decimal",
				"discount": "decimal",
				"tax": "decimal",
				"subtotal": "decimal",
				"total": "decimal",
				"charitable": "boolean",
				"free": "boolean",
				"quantity_ordered": "decimal",
				"quantity_picked": "decimal",
				"quantity_packed": "decimal",
				"quantity_fulfilled": "decimal",
				"quantity_received": "decimal",
				"required_by": "date",
				"product": {
					"inventory_id": "string",
					"stock_locator_id": "string",
					"id": "string",
					"sku": "string",
					"reference": "string",
					"name": "string",
					"barcode": "string",
					"family": {
						"id": "string",
						"sku": "string",
						"reference": "string",
						"name": "string"
					},
					"class": "string",
					"department": "string",
					"brand": "string",
					"business_stream": "string",
					"category_1": "string",
					"category_2": "string",
					"category_3": "string",
					"category_4": "string",
					"category_5": "string",
					"category_6": "string",
					"category_7": "string",
					"category_8": "string",
					"category_9": "string",
					"category_10": "string",
					"year": "decimal",
					"season": "string",
					"hierarchy_id": "string",
					"variant_1_name": "string",
					"variant_2_name": "string",
					"variant_3_name": "string",
					"variant_4_name": "string",
					"variant_5_name": "string",
					"variant_1": "string",
					"variant_2": "string",
					"variant_3": "string",
					"variant_4": "string",
					"variant_5": "string",
					"uom": "string",
					"retail_price": "decimal",
					"wholesale_price": "decimal",
					"supplier": {
						"id": "string",
						"reference": "string",
						"name": "string",
						"product_id": "string",
						"product_code": "string",
						"reorder_code": "string"
					},
					"categories": [
						{
							"id": "string",
							"reference": "string",
							"name": "string",
							"value": "string"
						}
					],
					"attributes": [
						{
							"id": "string",
							"reference": "string",
							"name": "string",
							"value": "string"
						}
					]
				},
				"discount_reason": {
					"id": "string",
					"reference": "string",
					"name": "string"
				},
				"return_reason": {
					"id": "string",
					"reference": "string",
					"name": "string"
				},
				"void_reason": {
					"id": "string",
					"reference": "string",
					"name": "string"
				},
				"promotion": {
					"code": "string",
					"tracking_id": "string"
				},
				"transfer": {
					"source": {
						"id": "string",
						"reference": "string",
						"name": "string",
						"type": "string"
					},
					"target": {
						"id": "string",
						"reference": "string",
						"name": "string",
						"type": "string"
					}
				},
				"dispatch": {
					"courier": "string",
					"tracking_url": "string",
					"tracking_decimal": "string",
					"required_by": "date",
					"expected_by": "date",
					"received_at": "date"
				},
				"commission": {
					"amount": "decimal",
					"percentage": "decimal"
				},
				"royalty": {
					"amount": "decimal",
					"percentage": "decimal"
				},
				"profit_share": {
					"amount": "decimal",
					"percentage": "decimal"
				},
				"finance_objects": [
					{
						"tlog_finance_object_id": "string",
						"sequence": "integer",
						"financial_id": "string",
						"financial_reference": "string",
						"financial_type": "string",
						"financial_entity": "string",
						"financial_location": "string",
						"financial_business_unit": "string",
						"financial_division": "string",
						"account_id": "string",
						"account_type": "string",
						"account_sub_type": "string",
						"account_reference": "string",
						"account_currency": "string",
						"account_name": "string",
						"account_division": "string",
						"description": "string",
						"debit": "decimal",
						"credit": "decimal",
						"note": "string"
					}
				],
				"note": "string"
			}
		],
		"tenders": [
			{
				"tlog_tender_id": "string",
				"tlog_tender_type": "string",
				"id": "string",
				"sequence": "integer",
				"grouping_id": "string",
				"reference": "string",
				"status": "string",
				"amount": "decimal",
				"surcharge": {
					"id": "string",
					"reference": "string",
					"name": "string",
					"amount": "decimal",
					"tax": "decimal"
				},
				"fee": {
					"id": "string",
					"reference": "string",
					"name": "string",
					"amount": "decimal",
					"tax": "decimal"
				},
				"tender": {
					"id": "string",
					"reference": "string",
					"name": "string",
					"type": "string",
					"issuer": {
						"id": "string",
						"reference": "string",
						"name": "string"
					},
					"card": {
						"id": "string",
						"reference": "string",
						"name": "string",
						"holder": "string",
						"number": "string",
						"expiry_month": "decimal",
						"expiry_year": "decimal",
						"cvv": "decimal",
						"pin": "string",
						"balance": "decimal"
					},
					"payment": {
						"authorization_type": "string",
						"authorized": "boolean",
						"authorized_at": "date",
						"token": "string",
						"provider": {
							"id": "string",
							"reference": "string",
							"name": "string",
							"gateway": {
								"id": "string",
								"reference": "string",
								"name": "string",
								"auth": {
									"id": "string",
									"reference": "string",
									"name": "string"
								}
							}
						}
					},
					"banking": {
						"expected": "decimal",
						"counted": "decimal",
						"variance": "decimal",
						"committed": "boolean",
						"finance_objects": [
							{
								"tlog_finance_object_id": "string",
								"sequence": "integer",
								"financial_id": "string",
								"financial_reference": "string",
								"financial_type": "string",
								"financial_entity": "string",
								"financial_location": "string",
								"financial_business_unit": "string",
								"financial_division": "string",
								"account_id": "string",
								"account_type": "string",
								"account_sub_type": "string",
								"account_reference": "string",
								"account_currency": "string",
								"account_name": "string",
								"account_division": "string",
								"bank_id": "string",
								"bank_type": "string",
								"bank_reference": "string",
								"bank_name": "string",
								"bank_account_number": "string",
								"description": "string",
								"debit": "decimal",
								"credit": "decimal",
								"note": "string"
							}
						]
					},
					"finance_objects": [
						{
							"tlog_finance_object_id": "string",
							"sequence": "integer",
							"financial_id": "string",
							"financial_reference": "string",
							"financial_type": "string",
							"financial_entity": "string",
							"financial_location": "string",
							"financial_business_unit": "string",
							"financial_division": "string",
							"account_id": "string",
							"account_type": "string",
							"account_sub_type": "string",
							"account_reference": "string",
							"account_currency": "string",
							"account_name": "string",
							"account_division": "string",
							"description": "string",
							"debit": "decimal",
							"credit": "decimal",
							"note": "string"
						}
					],
					"note": "string"
				}
			}
		],
		"note": "string"
	}
}
';