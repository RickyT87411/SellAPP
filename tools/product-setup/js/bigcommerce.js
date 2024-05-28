
function BigcommerceInterface(instance) {
    this._instance = instance;
    this._get_instance_mappings(instance);
}

BigcommerceInterface.prototype._proxy = '/SimplePHPProxy.php';
BigcommerceInterface.prototype._data_mapping_uri = '/tools/json/bigcommerce-product-mapping.json';
BigcommerceInterface.prototype._instance_mapping_uri = '/json/bigcommerce-instances.json';

BigcommerceInterface.prototype._response_codes = {
    "ERR_INSTANCE_INVALID" : {
        "statusCode" : 500,
        "statusText" : "Bigcommerce Systems instance invalid or missing. {0}"
    },
    "ERR_ENDPOINTS_INVALID" : {
        "statusCode" : 500,
        "statusText" : "Bigcommerce Systems endpoints invalid or missing. {0}"
    },
    "ERR_HEADERS_INVALID" : {
        "statusCode" : 500,
        "statusText" : "Bigcommerce Systems authentication headers invalid or missing. {0}"
    },
    "ERR_PRODUCT_FAMILY_DATA_INVALID" : {
        "statusCode" : 500,
        "statusText" : "Bigcommerce Systems ProductFamily data invalid or missing. {0}"
    },
    "ERR_PRODUCT_DATA_INVALID" : {
        "statusCode" : 500,
        "statusText" : "Bigcommerce Systems Product data invalid or missing. {0}"
    },
    "ERR_PRODUCT_ID_INVALID" : {
        "statusCode" : 500,
        "statusText" : "Bigcommerce Systems Product ID is invalid or missing. {0}"
    }
};

BigcommerceInterface.prototype._get_instance_mappings = function(instance) {
    var bigcommerce = this;
    $.ajax({
        url: BigcommerceInterface.prototype._instance_mapping_uri,
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            console.log('im getting called here...');
            if(data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.contents)) {
                bigcommerce._instance_detail = data.contents[instance] || null;
                if( bigcommerce._instance_detail ) {
                    bigcommerce._instance = instance;
                    bigcommerce._endpoints = bigcommerce._instance_detail.endpoints || {};
                    bigcommerce._headers = bigcommerce._instance_detail.headers || {};
                }
            }
        }
    });
};

BigcommerceInterface.prototype._get_data_mappings = function() {
    var bigcommerce = this;
    $.ajax({
        url: BigcommerceInterface.prototype._data_mapping_uri,
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            if(data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.contents)) {
                bigcommerce._data_map = data.contents || {};
            }
        }
    });
};