<?php
    define("_DEAR_AUTH_ACCOUNT_PARAM_", "api-auth-accountid");
    define("_DEAR_AUTH_KEY_PARAM_", "api-auth-applicationkey");

    define("_VEND_PRODUCT_API_V0_1_", "https://%s.vendhq.com/api/products/");
    define("_VEND_PRODUCT_API_V1_0_", "https://%s.vendhq.com/api/1.0/product/");
    
    define("_DEAR_PURCHASELIST_API_V1_", "https://inventory.dearsystems.com/DearApi/PurchaseList/");
    define("_DEAR_PURCHASE_API_V1_", "https://inventory.dearsystems.com/DearApi/Purchase/");
    define("_DEAR_PRODUCT_API_V1_", "https://inventory.dearsystems.com/DearApi/Products/");

    define("_DEAR_PURCHASE_LIST_QUERY_", "?status=RECEIVED");
    define("_DEAR_PURCHASE_LIST_ORDERS_FIELD_", "PurchaseList");
    define("_DEAR_PURCHASE_LIST_ORDER_ID_FIELD_", "ID");
    define("_DEAR_PURCHASE_LIST_ORDER_NUMBER_FIELD_", "OrderNumber");
    define("_DEAR_PURCHASE_LIST_ORDER_LAST_UPDATED_FIELD_", "LastUpdatedDate");
    
    define("_DEAR_PURCHASE_ORDER_FIELD_", "Order");
    define("_DEAR_PURCHASE_ORDER_LINE_FIELD_", "Lines");
    define("_DEAR_PURCHASE_ORDER_LINE_SKU_FIELD_", "SKU");
    define("_DEAR_PURCHASE_ORDER_LINE_NAME_FIELD_", "Name");
    define("_DEAR_PURCHASE_ORDER_LINE_PRICE_FIELD_", "Price");
    define("_DEAR_PURCHASE_ORDER_LINE_DISCOUNT_FIELD_", "Discount");
    define("_DEAR_PURCHASE_ORDER_LINE_TOTAL_FIELD_", "Total");
    define("_DEAR_PURCHASE_ORDER_LINE_QUANTITY_FIELD_", "Quantity");
    define("_DEAR_PURCHASE_ORDER_LINE_TAX_TOTAL_FIELD_", "Tax");
    
    define("_DEAR_PRODUCT_NODE_", "Products");
    define("_DEAR_PRODUCT_SKU_QUERY_", "sku=");
    define("_DEAR_PRODUCT_NAME_QUERY_", "name=");
    
    define("_VEND_WEBHOOK_TRIGGER_TYPE_PARAM", "type");
    define("_VEND_WEBHOOK_DOMAIN_PARAM_", "domain_prefix");
    define("_VEND_WEBHOOK_PAYLOAD_PARAM_", "payload");
    
    define("_VEND_WEBHOOK_TRIGGER_TYPE_", "product.update");
    
    define("_VEND_PRODUCT_FIELD_", "products");
    define("_VEND_PRODUCT_ID_FIELD_", "id");
    define("_VEND_PRODUCT_SKU_FIELD_", "sku");
    define("_VEND_PRODUCT_BASE_NAME_FIELD_", "base_name");
    define("_VEND_PRODUCT_NAME_FIELD_", "name");
    define("_VEND_PRODUCT_COMPOSITE_FIELD_", "composites");
    define("_VEND_PRODUCT_INVENTORY_FIELD_", "inventory");
    define("_VEND_PRODUCT_SOH_FIELD_", "count");
    define("_VEND_PRODUCT_SUPPLIER_PRICE_FIELD_", "supply_price");
    define("_VEND_PRODUCT_ACTUAL_COST_FIELD_", "attributed_cost");
    define("_VEND_PRODUCT_TRACK_INVENTORY_FIELD_", "track_inventory");

    global $_VEND_DOMAIN_OAUTH2_KEYS_;
    global $_DEAR_DOMAIN_AUTH_KEYS_;
    
    $_VEND_DOMAIN_OAUTH2_KEYS_ = array(
        "playbill"      => "Bearer 2Bqb1zG2KckwX2KudxR0P2:7rV8m36GfDKbc7JVy",
        "playbilldev"   => "Bearer 5Ladw3FITzySnyKlMAXcU:LNXIR72krqvLHodQPY"
    );
    
    $_DEAR_DOMAIN_AUTH_KEYS_ = array(
        "playbill"      => array(
            _DEAR_AUTH_ACCOUNT_PARAM_   => "3690ecc8-db52-496d-b9e8-d81d3c602f68",
            _DEAR_AUTH_KEY_PARAM_       => "e720af4d-2e86-b81b-e12a-15f648d8c9c1"
        ),
        "playbilldev"   => array(
            _DEAR_AUTH_ACCOUNT_PARAM_   => "eab948c2-a308-4368-8b1f-f4354d316ffe",
            _DEAR_AUTH_KEY_PARAM_       => "ecdd123c-f9f0-c3cb-1d7c-7303d4d433e9"
        )
    );
?>