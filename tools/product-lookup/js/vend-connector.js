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
 *	Dear Systems API interface layer
 *
 */
function VendInterface(instance) {
	// Init the Dear mappings
	this._init(instance);
}

VendInterface.prototype._proxy = '/SimplePHPProxy.php';
VendInterface.prototype._instance_mapping_uri = '/json/vend-instances.json';
VendInterface.prototype._data_mapping_uri = '/json/vend-product-mappings.json';

VendInterface.prototype._init = function(instance) {
	this._data_map = {};
	this._endpoints = {};
	this._headers = {};
	this._instance = null;
	this._get_instance_mappings(instance);
	//this._get_data_mappings();
};

VendInterface.prototype._get_instance_mappings = function(instance) {
	var intf = this;
	$.ajax({
		url: VendInterface.prototype._instance_mapping_uri, 
		dataType: "json",
		async: false,
		cache: false,
		success: function(data) {
			if(data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.contents)) {
				intf._instance = data.contents[instance] || null;
				if( intf._instance) {
					intf._endpoints = intf._instance.endpoints || {};
					intf._headers = intf._instance.headers || {};
				}
			}
		}
	});
};

VendInterface.prototype._get_data_mappings = function() {
	var intf = this;
	$.ajax({
		url: VendInterface.prototype._data_mapping_uri,
		dataType: "json",
		async: false,
		cache: false,
		success: function(data) {
			if(data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.contents)) {
				intf._data_map = data.contents || {};
			}
		}
	});
};

VendInterface.prototype._response_codes = {
	"ERR_INSTANCE_INVALID" : {
		"statusCode" : 500,
		"statusText" : "Vend instance invalid or missing. {0}"
	},
	"ERR_ENDPOINTS_INVALID" : {
		"statusCode" : 500,
		"statusText" : "Vend endpoints invalid or missing. {0}"
	},
	"ERR_HEADERS_INVALID" : {
		"statusCode" : 500,
		"statusText" : "Vend authentication headers invalid or missing. {0}"
	},
	"ERR_PRODUCT_DATA_INVALID" : {
		"statusCode" : 500,
		"statusText" : "Vend Product data invalid or missing. {0}"
	},
	"ERR_PRODUCT_ID_INVALID" : {
		"statusCode" : 500,
		"statusText" : "Vend Product ID is invalid or missing. {0}"
	},
	"ERR_PRODUCT_BINDING" : {
		"statusCode" : 500,
		"statusText" : "Unable to bind Product mapping for {0}"
	}
};

VendInterface.prototype.getInstances = function(
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback,
	dev
) {
	var url = VendInterface.prototype._instance_mapping_uri;
	
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


VendInterface.prototype.getProducts = function(
	id,
	sku,
	handle,
	active,
	since,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback,
	dev,
	_page,
	_results,
	_data
) {
	// Check Instance is valid
	if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
		var response = VendInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
		var response = VendInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints["products"] == null || this._endpoints["products"] === undefined) {
		var response = VendInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "products"));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Headers are pre-loaded
	if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
		var response = VendInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	var url = VendInterface.prototype._proxy;
	var sub_url = this._endpoints["products"];
	var headers = this._headers;
	
	var params = {};
	if(id) params["id"] = id;
	if(sku) params["sku"] = sku;
	if(handle) params["handle"] = handle;
	if(active) params["active"] = active;
	if(since) params["since"] = since;
	if(_page) params["page"] = _page;

	$.ajax({
		url:		url,
		data:		{ 
						"url": sub_url + "?" + jQuery.param( params ),
						"headers": JSON.stringify(headers),
						"full_headers" : 1, 
						"full_status": 1
					},
		method:		"GET",
		dataType:	"json",
		contentType: "application/json; charset=utf-8",
		cache:		false,
		global: 	false,
		async:		true,
		beforeSend:	(before_send_callback ? function(xhr) {before_send_callback(xhr)} : null),
		success:	function(data) {
			try {
				if( data && !jQuery.isEmptyObject(data) &&  !jQuery.isEmptyObject(data.contents)) {
					var recordset = data.contents.products || [];
					var total = data.contents.results || 0;

					if( recordset.length == 0 ) {
						// All records have been collected
						if(isFunction(success_callback)) success_callback(_data || []);
					} else {
						// Increment page counter
						_page = _page || 1;
						_page++;
						
						// Increment total results queried
						_results = _results || 0;
						_results += recordset.length;
						
						// Extend the Product array to include the returned results
						if(_data)
							$.extend(true, _data, data.contents.products);
						else
							_data = data.contents.products;
	
						// Query the next page if there are still more pages
						if( _results < total) {
							VendInterface.prototype.getProducts(
								id,
								sku,
								handle,
								active,
								since,
								before_send_callback,
								success_callback,
								error_callback,
								complete_callback,
								dev,
								_page,
								_results,
								_data
							);
						} else {
							// Return all the records to-date
							if(isFunction(success_callback)) success_callback(_data || []);
						}
					}
					
				} else {
					// Return all the records to-date
					if(isFunction(success_callback)) success_callback(_data || []);
				}
			} catch(err) {
				// Return the error response
				if(isFunction(error_callback)) error_callback(err);
			}
		},
		error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
		complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
	});
};

VendInterface.prototype.getCustomers = function(
	id,
	code,
	email,
	since,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback,
	dev,
	_page,
	_results,
	_data
) {
	
	// Check Instance is valid
	if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
		var response = VendInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
		var response = VendInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints["customers"] == null || this._endpoints["customers"] === undefined) {
		var response = VendInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "customers"));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Headers are pre-loaded
	if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
		var response = VendInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	var url = VendInterface.prototype._proxy;
	var sub_url = this._endpoints["customers"];
	var headers = this._headers;
	
	var params = {};
	if(id) params["id"] = id;
	if(code) params["code"] = code;
	if(email) params["email"] = email;
	if(since) params["since"] = since;
	if(_page) params["page"] = _page;

	$.ajax({
		url:		url,
		data:		{ 
						"url": sub_url + "?" + jQuery.param( params ),
						"headers": JSON.stringify(headers),
						"full_headers" : 1, 
						"full_status": 1
					},
		method:		"GET",
		dataType:	"json",
		contentType: "application/json; charset=utf-8",
		cache:		false,
		global: 	false,
		async:		true,
		beforeSend:	(before_send_callback ? function(xhr) {before_send_callback(xhr)} : null),
		success:	function(data) {
			try {
				if( data && !jQuery.isEmptyObject(data) &&  !jQuery.isEmptyObject(data.contents)) {
					var recordset = data.contents.customers || [];
					var total = data.contents.results || 0;

					if( recordset.length == 0 ) {
						// All records have been collected
						if(isFunction(success_callback)) success_callback(_data || []);
					} else {
						// Increment page counter
						_page = _page || 1;
						_page++;
						
						// Increment total results queried
						_results = _results || 0;
						_results += recordset.length;
						
						// Extend the Product array to include the returned results
						if(_data)
							$.extend(true, _data, data.contents.customers);
						else
							_data = data.contents.customers;
	
						// Query the next page if there are still more pages
						if( _results < total) {
							VendInterface.prototype.getCustomers(
								id,
								code,
								email,
								since,
								before_send_callback,
								success_callback,
								error_callback,
								complete_callback,
								dev,
								_page,
								_results,
								_data
							);
						} else {
							// Return all the records to-date
							if(isFunction(success_callback)) success_callback(_data || []);
						}
					}
					
				} else {
					// Return all the records to-date
					if(isFunction(success_callback)) success_callback(_data || []);
				}
			} catch(err) {
				// Return the error response
				if(isFunction(error_callback)) error_callback(err);
			}
		},
		error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
		complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
	});
};

VendInterface.prototype.getRegisters = function(
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback,
	dev
) {
	
	// Check Instance is valid
	if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
		var response = VendInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
		var response = VendInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints["registers"] == null || this._endpoints["registers"] === undefined) {
		var response = VendInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "registers"));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Headers are pre-loaded
	if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
		var response = VendInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	var url = VendInterface.prototype._proxy
	var sub_url = this._endpoints["registers"];
	var headers = this._headers;
	
	$.ajax({
		url:		url,
		data:		{ 
						"url": sub_url, 
						"headers": JSON.stringify(headers),
						"full_headers" : 1, 
						"full_status": 1
					},
		method:		"GET",
		dataType:	"json",
		contentType: "application/json; charset=utf-8",
		cache:		false,
		global: 	false,
		async:		true,
		beforeSend:	(before_send_callback ? function(xhr) {before_send_callback(xhr)} : null),
		success:	function(data) {success_callback(data)},
		error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
		complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
	});
};

VendInterface.prototype.getOutlets = function(
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback,
	dev
) {
	
	// Check Instance is valid
	if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
		var response = VendInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
		var response = VendInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints["outlets"] == null || this._endpoints["outlets"] === undefined) {
		var response = VendInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "outlets"));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Headers are pre-loaded
	if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
		var response = VendInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	var url = VendInterface.prototype._proxy
	var sub_url = this._endpoints["outlets"];
	var headers = this._headers;
	
	$.ajax({
		url:		url,
		data:		{ 
						"url": sub_url, 
						"headers": JSON.stringify(headers),
						"full_headers" : 1, 
						"full_status": 1
					},
		method:		"GET",
		dataType:	"json",
		contentType: "application/json; charset=utf-8",
		cache:		false,
		global: 	false,
		async:		true,
		beforeSend:	(before_send_callback ? function(xhr) {before_send_callback(xhr)} : null),
		success:	function(data) {success_callback(data)},
		error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
		complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
	});
};
