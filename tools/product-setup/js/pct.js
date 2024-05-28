function isFunction(functionToCheck) {
    var getType = {};
    return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
}

// Formats a string by replacing placeholders represented as {0}, {1}, {n} with specified text
// Similar to sprintf() in other languages
String.format = String.format || function() {
    // The string containing the format items (e.g. "{0}")
    // will and always has to be the first argument.
    var theString = arguments[0];

    if(!theString)
        return theString;

    // start with the second argument (i = 1)
    for (var i = 1; i < arguments.length; i++) {
        // "gm" = RegEx options for Global search (more than one instance)
        // and for Multiline search
        var regEx = new RegExp("\\{" + (i - 1) + "\\}", "gm");
        theString = theString.replace(regEx, arguments[i]);
    }

    return theString;
}

/**
 *	Playbill API interface layer
 *
 */
function PCTInterface(instance) {
    // Init the mappings
    this._init(instance);
}

PCTInterface.prototype._proxy = '/SimplePHPProxy.php';
PCTInterface.prototype._instance_mapping_uri = '/json/pct.json';

PCTInterface.prototype._init = function(instance, proxy) {
    this._endpoints = {};
    this._headers = {};
    this._instance = instance;
    this._get_instance_mappings(instance);
};

PCTInterface.prototype._get_instance_mappings = function(instance) {
    var playbill = this;
    $.ajax({
        url: PCTInterface.prototype._instance_mapping_uri,
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            if(data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.contents)) {
                playbill._instanceDetail = data.contents[instance] || null;
                if( playbill._instanceDetail) {
                    playbill._endpoints = playbill._instanceDetail.endpoints || {};
                    playbill._headers = playbill._instanceDetail.headers || {};

                    if(playbill._endpoints) {
                        $.each(playbill._endpoints, function(index, value) {
                            playbill._endpoints[index] = String.format(value, instance);
                        });
                    }
                }
            }
        }
    });
};


PCTInterface.prototype._response_codes = {
    "ERR_INSTANCE_INVALID" : {
        "statusCode" : 500,
        "statusText" : "Playbill instance invalid or missing. {0}"
    },
    "ERR_ENDPOINTS_INVALID" : {
        "statusCode" : 500,
        "statusText" : "Playbill endpoints invalid or missing. {0}"
    },
    "ERR_HEADERS_INVALID" : {
        "statusCode" : 500,
        "statusText" : "Playbill authentication headers invalid or missing. {0}"
    },
    "ERR_PRODUCT_ID_INVALID" : {
        "statusCode" : 500,
        "statusText" : "Playbill Product ID is invalid or missing. {0}"
    },
    "ERR_ORDER_ID_INVALID" : {
        "statusCode" : 500,
        "statusText" : "Playbill Product ID is invalid or missing. {0}"
    }
};

PCTInterface.prototype.getInstances = function(
    before_send_callback,
    success_callback,
    error_callback,
    complete_callback,
    dev
) {
    var url = PCTInterface.prototype._instance_mapping_uri;

    $.ajax({
        url:		url,
        dataType:	"json",
        cache:		false,
        global: 	false,
        async:		true,
        beforeSend:	(before_send_callback ? function(xhr) {before_send_callback(xhr)} : null),
        success:	function(data) {
            var instances = [];

            if(data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.contents)) {
                $.each(data.contents, function(key, val) {
                    instances.push(key);
                });
            }
            success_callback(instances);
        },
        error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
        complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
    });
};

PCTInterface.prototype.postProduct = function(
    data,
    before_send_callback,
    success_callback,
    error_callback,
    complete_callback
) {
    var playbill = this;

    console.log(this._instance);
    console.log(this._endpoints);
    // Check Instance is valid
    if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
        var response = PCTInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
        if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, this._instance));
        if(isFunction(complete_callback)) complete_callback(response);
        return false;
    }

    // Check Endpoints are pre-loaded
    if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
        var response = PCTInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
        if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
        if(isFunction(complete_callback)) complete_callback(response);
        return false;
    }

    // Check Headers are pre-loaded
    if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
        var response = PCTInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
        if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
        if(isFunction(complete_callback)) complete_callback(response);
        return false;
    }

    var url = PCTInterface.prototype._proxy;
    var sub_url = this._endpoints["post-product"];
    var headers = this._headers;

    console.log(headers);

    console.log(data);

    var params =
        {
            url:		sub_url,
            "headers": JSON.stringify(headers),
            "full_headers" : 1,
            "full_status": 1,
            "full_request": 1,
            "json" : 1
        };

    $.ajax({
        url:		url + '?' + jQuery.param( params ),
        data:		data,
        method:		"POST",
        cache:		false,
        global: 	false,
        async:		true,
        beforeSend:	null,
        success:	function(data) {
            try {
                console.log(data)
                if( data && !jQuery.isEmptyObject(data)) {
                    // Return created dataset
                    if(isFunction(success_callback)) success_callback(data);
                } else {
                    // Return the error response
                    if(isFunction(error_callback)) error_callback(data);
                }
            } catch(err) {
                // Return the error response
                if(isFunction(error_callback)) error_callback(data, (data && data.status ? data.status.http_code : err), err);
            }
        },
        error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
        complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
    });
};
