var pct = null;
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
function DearSystemsInterface(instance) {
	// Init the Dear mappings
	this._init(instance);
}

DearSystemsInterface.prototype._proxy = '/CorsProxy.php';
DearSystemsInterface.prototype._instance_mapping_uri = '/tools/json/dear-instances.json';
DearSystemsInterface.prototype._data_mapping_uri = '/tools/json/dear-product-mappings.json';

DearSystemsInterface.prototype._init = function(instance) {
	this._data_map = {};
	this._endpoints = {};
	this._headers = {};
	this._instance = null;
	this._instance_name = null;
	this._get_instance_mappings(instance);
	this._get_data_mappings();
};

DearSystemsInterface.prototype._get_instance_mappings = function(instance) {
	var dear = this;
	$.ajax({
		url: DearSystemsInterface.prototype._instance_mapping_uri,
		dataType: "json",
		async: false,
		cache: false,
		success: function(data) {
			if(data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.contents)) {
				dear._instance = data.contents[instance] || null;
				if( dear._instance ) {
					dear._instance_name = instance;
					dear._endpoints = dear._instance.endpoints || {};
					dear._headers = dear._instance.headers || {};
				}
			}
		}
	});
};

DearSystemsInterface.prototype._get_data_mappings = function() {
	var dear = this;
	$.ajax({
		url: DearSystemsInterface.prototype._data_mapping_uri,
		dataType: "json",
		async: false,
		cache: false,
		success: function(data) {
			if(data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.contents)) {
				dear._data_map = data.contents || {};
			}
		}
	});
};

DearSystemsInterface.prototype._response_codes = {
	"ERR_INSTANCE_INVALID" : {
		"statusCode" : 500,
		"statusText" : "Dear Systems instance invalid or missing. {0}"
	},
	"ERR_ENDPOINTS_INVALID" : {
		"statusCode" : 500,
		"statusText" : "Dear Systems endpoints invalid or missing. {0}"
	},
	"ERR_HEADERS_INVALID" : {
		"statusCode" : 500,
		"statusText" : "Dear Systems authentication headers invalid or missing. {0}"
	},
	"ERR_PRODUCT_FAMILY_DATA_INVALID" : {
		"statusCode" : 500,
		"statusText" : "Dear Systems ProductFamily data invalid or missing. {0}"
	},
	"ERR_PRODUCT_DATA_INVALID" : {
		"statusCode" : 500,
		"statusText" : "Dear Systems Product data invalid or missing. {0}"
	},
	"ERR_PRODUCT_ID_INVALID" : {
		"statusCode" : 500,
		"statusText" : "Dear Systems Product ID is invalid or missing. {0}"
	},
	"ERR_PRODUCT_FAMILY_BINDING" : {
		"statusCode" : 500,
		"statusText" : "Unable to bind ProductFamily mapping for {0}"
	},
	"ERR_PRODUCT_BINDING" : {
		"statusCode" : 500,
		"statusText" : "Unable to bind Product mapping for {0}"
	}
};

DearSystemsInterface.prototype.getInstances = function(
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback
) {
	var url = DearSystemsInterface.prototype._instance_mapping_uri;

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

DearSystemsInterface.prototype.getProductBrands = function(
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback
) {

	// Check Instance is valid
	if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Endpoints are pre-loaded
	if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Endpoints are pre-loaded
	if(this._endpoints["ProductBrands"] == null || this._endpoints["ProductBrands"] === undefined) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "ProductBrands"));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Headers are pre-loaded
	if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	var url = DearSystemsInterface.prototype._proxy
	var sub_url = this._endpoints["ProductBrands"];
	var headers = this._headers;

	$.ajax({
		url:		url,
		//headers:	headers,
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

DearSystemsInterface.prototype.getChartOfAccounts = function(
	name,
	accountclass,
	status,
	accept_payments,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback
) {
	// Check Instance is valid
	if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Endpoints are pre-loaded
	if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Endpoints are pre-loaded
	if(this._endpoints["ChartOfAccounts"] == null || this._endpoints["ChartOfAccounts"] === undefined) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "ChartOfAccounts"));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Headers are pre-loaded
	if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	var url = DearSystemsInterface.prototype._proxy;
	var sub_url = this._endpoints["ChartOfAccounts"];
	var headers = this._headers;

	var params = {};
	if(name) params["name"] = name;
	if(accountclass) params["class"] = accountclass;
	if(status) params["status"] = status;
	if(accept_payments) params["acceptpayments"] = accept_payments;

	$.ajax({
		url:		url,
		//headers:	headers,
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
		success:	function(data) {success_callback(data)},
		error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
		complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
	});
};

DearSystemsInterface.prototype.getProductCategories = function(
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback
) {
	// Check Instance is valid
	if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Endpoints are pre-loaded
	if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Endpoints are pre-loaded
	if(this._endpoints["ProductCategories"] == null || this._endpoints["ProductCategories"] === undefined) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "ProductCategories"));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Endpoints are pre-loaded
	if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	var url = DearSystemsInterface.prototype._proxy
	var sub_url = this._endpoints["ProductCategories"];
	var headers = this._headers;

	$.ajax({
		url:		url,
		//headers:	headers,
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
		async:		true,
		beforeSend:	(before_send_callback ? function(xhr) {before_send_callback(xhr)} : null),
		success:	function(data) {success_callback(data)},
		error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
		complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
	});
};

DearSystemsInterface.prototype.getTaxationRules = function(
	is_tax_for_purchase,
	is_tax_for_sale,
	is_active,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback
) {
	// Check Instance is valid
	if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Endpoints are pre-loaded
	if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Endpoints are pre-loaded
	if(this._endpoints["TaxationRules"] == null || this._endpoints["TaxationRules"] === undefined) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "TaxationRules"));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	// Check Headers are pre-loaded
	if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
		var response = DearSystemsInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	var url = DearSystemsInterface.prototype._proxy;
	var sub_url = this._endpoints["TaxationRules"];
	var headers = this._headers;

	var params = {};

	if (is_tax_for_purchase != null && is_tax_for_purchase !== undefined)
		params["IsTaxForPurchase"] = is_tax_for_purchase === true;

	if (is_tax_for_sale != null && is_tax_for_sale !== undefined)
		params["IsTaxForSale"] = is_tax_for_sale === true;

	if (is_active != null && is_active !== undefined)
		params["isActive"] = is_active === true;

	$.ajax({
		url:		url,
		//headers:	headers,
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
		success:	function(data) {success_callback(data)},
		error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
		complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
	});
};

DearSystemsInterface.prototype.getProductFamilies = function(
	name,
	sku,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback,
	_page,
	_results,
	_products
) {
	try {
		// Check Instance is valid
		if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(this._endpoints["ProductFamilies"] == null || this._endpoints["ProductFamilies"] === undefined) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "ProductFamilies"));
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Headers are pre-loaded
		if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		var dear = this;
		var url = DearSystemsInterface.prototype._proxy;
		var sub_url = this._endpoints["ProductFamilies"];
		var headers = this._headers;

		var params = {};
		if(sku) params["sku"] = sku;
		if(name) params["name"] = name;
		if(_page) params["page"] = _page;

		$.ajax({
			url:		url,
			//headers:	headers,
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
					if ( data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.status) && data.status.http_code !== 200) {
						if(isFunction(error_callback)) error_callback(data.contents, data.contents.ErrorCode, data.conents.Exception);
					} else if( data && !jQuery.isEmptyObject(data) &&  !jQuery.isEmptyObject(data.contents)) {
						var products = data.contents.ProductFamilies || [];
						var total = data.contents.Total || 0;

						if( products.length == 0 ) {
							// All products have been collected
							if(isFunction(success_callback)) success_callback(_products || []);
						} else {
							// Increment page counter
							_page = _page || 1;
							_page++;

							// Increment total results queried
							_results = _results || 0;
							_results += products.length;

							// Extend the Product array to include the returned results
							if(_products)
								$.extend(true, _products, data.contents.ProductFamilies);
							else
								_products = data.contents.ProductFamilies;

							// Query the next page if there are still more pages
							if( _results < total) {
								dear.getProductFamilies(
									name,
									sku,
									before_send_callback,
									success_callback,
									error_callback,
									complete_callback,
									_page,
									_results,
									_products
								);
							} else {
								// Return all the products to-date
								if(isFunction(success_callback)) success_callback(_products || []);
							}
						}
					} else {
						// Return all the products to-date
						if(isFunction(success_callback)) success_callback(_products || []);
					}
				} catch(err) {
					// Return the error response
					if(isFunction(error_callback)) error_callback(err);
				}
			},
			error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
			complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
		});
	} catch(err) {
		if(isFunction(error_callback)) error_callback(err);
	}
};

DearSystemsInterface.prototype.getProducts = function(
	name,
	sku,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback,
	_page,
	_results,
	_products
) {
	try {
		// Check Instance is valid
		if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(this._endpoints["Products"] == null || this._endpoints["Products"] === undefined) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "Products"));
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Headers are pre-loaded
		if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}
		var dear = this;
		var url = DearSystemsInterface.prototype._proxy;
		var sub_url = this._endpoints["Products"];
		var headers = this._headers;

		var params = {};
		if(sku) params["sku"] = sku;
		if(name) params["name"] = name;
		if(_page) params["page"] = _page;
		params["includeDeprecated"] = true;

		$.ajax({
			url:		url,
			//headers:	headers,
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
						var products = data.contents.Products || [];
						var total = data.contents.Total || 0;

						if( products.length == 0 ) {
							// All products have been collected
							if(isFunction(success_callback)) success_callback(_products || []);
						} else {
							// Increment page counter
							_page = _page || 1;
							_page++;

							// Increment total results queried
							_results = _results || 0;
							_results += products.length;

							// Extend the Product array to include the returned results
							if(_products)
								$.extend(true, _products, data.contents.Products);
							else
								_products = data.contents.Products;

							// Query the next page if there are still more pages
							if( _results < total) {
								dear.getProducts(
									name,
									sku,
									before_send_callback,
									success_callback,
									error_callback,
									complete_callback,
									_page,
									_results,
									_products
								);
							} else {
								// Return all the products to-date
								if(isFunction(success_callback)) success_callback(_products || []);
							}
						}

					} else {
						// Return all the products to-date
						if(isFunction(success_callback)) success_callback(_products || []);
					}
				} catch(err) {
					// Return the error response
					if(isFunction(error_callback)) error_callback(err);
				}
			},
			error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
			complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
		});
	} catch(err) {
		if(isFunction(error_callback)) error_callback(err);
	}
};

DearSystemsInterface.prototype.postProductFamily = function(
	data,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback
) {
	try {
		// Check Instance is valid
		if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(this._endpoints["ProductFamilies"] == null || this._endpoints["ProductFamilies"] === undefined) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "ProductFamilies"));
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Headers are pre-loaded
		if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(data == null || data === undefined || jQuery.isEmptyObject(data)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_PRODUCT_FAMILY_DATA_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		var url = DearSystemsInterface.prototype._proxy;
		var sub_url = this._endpoints["ProductFamilies"];
		var headers = this._headers;

		headers["Content-Type"] = "application/json; charset=utf-8";

		var params =
		{
			"url": sub_url,
			"headers": JSON.stringify(headers),
			"full_headers" : 1,
			"full_status": 1,
			"full_request": 1,
			"json" : 1
		};

		console.log(data);
		console.log(JSON.stringify(data));

		$.ajax({
			url:		url + '?' + jQuery.param( params ),
			//headers:	headers,
			data:		data,
			method:		"POST",
			cache:		false,
			global: 	false,
			async:		true,
			beforeSend:	(before_send_callback ? function(xhr) {before_send_callback(xhr)} : null),
			success:	function(data) {
				try {
					console.log(data)
					if( data && !jQuery.isEmptyObject(data) && data.status.http_code == 200 && !jQuery.isEmptyObject(data.contents)) {
						// Return created dataset
						if(isFunction(success_callback)) success_callback(data.contents);
					} else {
						// Return the error response
						if(isFunction(error_callback)) error_callback(data.contents, data.contents.ErrorCode, data.conents.Exception);
					}
				} catch(err) {
					// Return the error response
					if(isFunction(error_callback)) error_callback(data, (data && data.status ? data.status.http_code : err), err);
				}
			},
			error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
			complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
		});
	} catch(err) {
		if(isFunction(error_callback)) error_callback(err);
	}
};

DearSystemsInterface.prototype.postProduct = function(
	data,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback
) {
	try {
		// Check Instance is valid
		if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(this._endpoints["Products"] == null || this._endpoints["Products"] === undefined) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "Products"));
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Headers are pre-loaded
		if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(data == null || data === undefined || jQuery.isEmptyObject(data)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_PRODUCT_DATA_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		var url = DearSystemsInterface.prototype._proxy;
		var sub_url = this._endpoints["Products"];
		var headers = this._headers;

		headers["Content-Type"] = "application/json; charset=utf-8";

		var params =
		{
			"url": sub_url,
			"headers": JSON.stringify(headers),
			"full_headers" : 1,
			"full_status": 1,
			"full_request": 1,
			"json" : 1
		};

		console.log(JSON.stringify(data));

		$.ajax({
			url:		url + '?' + jQuery.param( params ),
			//headers:	headers,
			data:		data,
			dataType:	"json",
			contentType:"application/json",
			method:		"POST",
			cache:		false,
			global: 	false,
			async:		true,
			beforeSend:	(before_send_callback ? function(xhr) {before_send_callback(xhr)} : null),
			success:	function(data) {
				try {
					console.log(data);
					if( data && !jQuery.isEmptyObject(data) && data.status.http_code == 200 && !jQuery.isEmptyObject(data.contents)) {
						// Return created dataset
						if(isFunction(success_callback)) success_callback(data.contents);
					} else {
						// Return the error response
						if(isFunction(error_callback)) error_callback(data.contents, data.contents.ErrorCode, data.conents.Exception);
					}
				} catch(err) {
					// Return the error response
					if(isFunction(error_callback)) error_callback(data, (data && data.status ? data.status.http_code : err), err);
				}
			},
			error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
			complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
		});
	} catch(err) {
		if(isFunction(error_callback)) error_callback(err);
	}
};


DearSystemsInterface.prototype.postMappedProductFamily = function(
	data,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback
) {
	try {
		var dear = this;

		// Attempt to map
		this.bindProductFamilyData(
			data,
			function(product_family) {
				//success_callback(product_family);
				//return;

				// Check if mapping yielded any results
				if( product_family && !jQuery.isEmptyObject(product_family) ) {
					// Pass through the newly mapped ProductFamily to the POST method
					dear.postProductFamily(
						product_family,
						before_send_callback,
						success_callback,
						error_callback,
						complete_callback
					);
				}
			},
			function(err) {
				//error_callback(err);
				//return;

				// Mapping is absent so assume data is correctly structured to
				// Dear Systems ProductFamily spec
				if( err == null ) {
					dear.postProductFamily(
						data,
						before_send_callback,
						success_callback,
						error_callback,
						complete_callback
					);
				} else {
					var response = DearSystemsInterface.prototype._response_codes["ERR_PRODUCT_FAMILY_BINDING"];
					if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
					if(isFunction(complete_callback)) complete_callback(response);
					return false;
				}
			}
		);
	} catch(err) {
		if(isFunction(error_callback)) error_callback(err);
	}
}

DearSystemsInterface.prototype.bindProductFamilyData = function(
	data,
	success_callback,
	error_callback
) {
	try {
		// No explicit data mapping has been put in place
		// so assume that the input data is valid to Dear's spec
		if( !this._data_map ) {
			if(isFunction(error_callback)) error_callback(null);
			return;
		}

		var map = this._data_map;
		var product_family = {};

		// Loop through each key in the data set and check if they key has been
		// mapped in either 'ProductFamily' or 'ProductFamily.Product' and assign the value
		// to the destination field
		$.each(data, function(key, val) {
			// Assign all 'ProductFamily' fields
			if( map['ProductFamily'][key] && (map['ProductFamily'][key]).localeCompare('Products') != 0)
				product_family[map['ProductFamily'][key]] = val;

			// Assign all 'ProductFamily.Product' entries
			if( map['ProductFamily'][key] && (map['ProductFamily'][key]).localeCompare('Products') == 0 && val && !jQuery.isEmptyObject(val) ) {
				// Create the Product array
				product_family['Products'] = [];

				// Loop through the source Product array and assign only the mapped fields
				$.each(val, function(index, entry) {
					// Product instance
					product = {};
					// Look through all the fields for the current Product entry
					// Only assign the mapped fields to the Product
					$.each(entry, function(pkey, pval) {
						if( map['ProductFamily.Product'][pkey] )
							product[map['ProductFamily.Product'][pkey]] = pval;
					});

					// If the Product instance contains data then include in the
					// Product array
					if( product && !jQuery.isEmptyObject(product) )
						product_family['Products'].push(product);
				});
			}

		});

		if(isFunction(success_callback)) success_callback(product_family);
	} catch( err ) {
		if(isFunction(error_callback)) error_callback(err);
	}

}

DearSystemsInterface.prototype.postMappedProduct = function(
	data,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback
) {
	try {
		var dear = this;

		// Attempt to map
		this.bindProductData(
			data,
			function(product) {
				// Check if mapping yielded any results
				if( product && !jQuery.isEmptyObject(product) ) {
					// Pass through the newly mapped ProductFamily to the POST method
					dear.postProduct(
						product,
						before_send_callback,
						success_callback,
						error_callback,
						complete_callback
					);
				}
			},
			function(err) {
				// Mapping is absent so assume data is correctly structured to
				// Dear Systems ProductFamily spec
				if( err == null ) {
					dear.postProduct(
						data,
						before_send_callback,
						success_callback,
						error_callback,
						complete_callback
					);
				} else {
					var response = DearSystemsInterface.prototype._response_codes["ERR_PRODUCT_BINDING"];
					if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
					if(isFunction(complete_callback)) complete_callback(response);
					return false;
				}
			}
		);

	} catch(err) {
		if(isFunction(error_callback)) error_callback(err);
	}
}

DearSystemsInterface.prototype.bindProductData = function(
	data,
	success_callback,
	error_callback
) {
	try {
		// No explicit data mapping has been put in place
		// so assume that the input data is valid to Dear's spec
		if( !this._data_map ) {
			if(isFunction(error_callback)) error_callback(null);
			return;
		}

		var map = this._data_map;
		var product = {};

		// Loop through each key in the data set and check if they key has been
		// mapped in 'Product' and assign the value to the destination field
		$.each(data, function(key, val) {
			// Assign all 'Product' fields
			if( map['Product'][key] )
				product[map['Product'][key]] = val;
		});

		if(isFunction(success_callback)) success_callback(product);
	} catch( err ) {
		if(isFunction(error_callback)) error_callback(err);
	}

}

DearSystemsInterface.prototype.putProduct = function(
	data,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback
) {
	try {
		// Check Instance is valid
		if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Endpoints are pre-loaded
		if(this._endpoints["Products"] == null || this._endpoints["Products"] === undefined) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "Products"));
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check Headers are pre-loaded
		if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Data is valid and a JSON
		if(data == null || data === undefined || jQuery.isEmptyObject(data)) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_PRODUCT_DATA_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}

		// Check ID is set
		if(data.ID == null || data.ID === undefined) {
			var response = DearSystemsInterface.prototype._response_codes["ERR_PRODUCT_ID_INVALID"];
			if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
			if(isFunction(complete_callback)) complete_callback(response);
			return false;
		}


		var url = DearSystemsInterface.prototype._proxy;
		var sub_url = this._endpoints["Products"];
		var headers = this._headers;

		headers["Content-Type"] = "application/json; charset=utf-8";

		var params =
		{
			"url": sub_url,
			"headers": JSON.stringify(headers),
			"full_headers" : 1,
			"full_status": 1,
			"full_request": 1,
			"json" : 1
		};

		console.log(JSON.stringify(data));

		$.ajax({
			url:		url + '?' + jQuery.param( params ),
			//headers:	headers,
			data:		data,
			method:		"PUT",
			cache:		false,
			global: 	false,
			async:		true,
			beforeSend:	(before_send_callback ? function(xhr) {before_send_callback(xhr)} : null),
			success:	function(data) {
				try {
					if( data && !jQuery.isEmptyObject(data) && data.status.http_code == 200 && !jQuery.isEmptyObject(data.contents)) {
						// Return created dataset
						if(isFunction(success_callback)) success_callback(data.contents);
					} else {
						// Return the error response
						if(isFunction(error_callback)) error_callback(data.contents, data.contents.ErrorCode, data.conents.Exception);
					}
				} catch(err) {
					// Return the error response
					if(isFunction(error_callback)) error_callback(data, (data && data.status ? data.status.http_code : err), err);
				}
			},
			error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
			complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus)} : null)
		});
	} catch(err) {
		if(isFunction(error_callback)) error_callback(err);
	}
};