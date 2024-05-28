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
function PlaybillInterface(instance) {
	// Init the mappings
	this._init(instance);
}

PlaybillInterface.prototype._proxy = '/SimplePHPProxy.php';
PlaybillInterface.prototype._instance_mapping_uri = '/json/playbill-instances.json';
PlaybillInterface.prototype._data_mapping_uri = '/json/playbill-mappings.json';

PlaybillInterface.prototype._init = function(instance, proxy) {
	this._data_map = {};
	this._endpoints = {};
	this._headers = {};
	this._instance = null;
	this._get_instance_mappings(instance);
	//this._get_data_mappings();
};

PlaybillInterface.prototype._get_instance_mappings = function(instance) {
	var playbill = this;
	$.ajax({
		url: PlaybillInterface.prototype._instance_mapping_uri, 
		dataType: "json",
		async: false,
		cache: false,
		success: function(data) {
			if(data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.contents)) {
				playbill._instance = data.contents[instance] || null;
				if( playbill._instance) {
					playbill._endpoints = playbill._instance.endpoints || {};
					playbill._headers = playbill._instance.headers || {};
					
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

PlaybillInterface.prototype._get_data_mappings = function() {
	var playbill = this;
	$.ajax({
		url: PlaybillInterface.prototype._data_mapping_uri,
		dataType: "json",
		async: false,
		cache: false,
		success: function(data) {
			if(data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.contents)) {
				playbill._data_map = data.contents || {};
			}
		}
	});
};

PlaybillInterface.prototype._response_codes = {
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
	}
};

PlaybillInterface.prototype.getInstances = function(
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback,
	dev
) {
	var url = PlaybillInterface.prototype._instance_mapping_uri;
	
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

PlaybillInterface.prototype.lookupProduct = function(
	barcode,
	name,
	company,
	country_code,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback,
	stream_results = false,
	_page,
	_total_pages,
	_records
) {
	var playbill = this;
	
	// Check Instance is valid
	if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
		var response = PlaybillInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, this._instance));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
		var response = PlaybillInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints["product_lookup"] == null || this._endpoints["product_lookup"] === undefined) {
		var response = PlaybillInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "ProductLookup"));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Headers are pre-loaded
	if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
		var response = PlaybillInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	var url = PlaybillInterface.prototype._proxy
	var sub_url = this._endpoints["product_lookup"];
	var headers = this._headers;

	var params = {};
	if(barcode) params["barcode"] = barcode;
	if(name) params["name"] = name;
	if(company) params["company"] = company;
	if(country_code) params["country_code"] = country_code;
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
					var records = data.contents.records || [];
					try {
						_total_pages = data.contents.pagination.total_pages || 0;
					} catch(err) {
						_total_pages = 0;
					}

					if( records.length == 0 ) {
						// All records have been collected
						if(isFunction(success_callback)) success_callback(_records || []);
					} else {
						// Increment page counter
						_page = _page || 1;
						_page++;
						
						// Extend the Product array to include the returned results if not streaming
						if(_records && !stream_results)
							$.extend(true, _records, data.contents.records);
						else
							_records = data.contents.records;
						
						// Return records collected thus far via streaming
						if( stream_results && _total_pages > 1) {
							if(isFunction(success_callback)) success_callback(_records || [], (_page <= _total_pages) , _page - 1);
						}

						// Query the next page if there are still more pages
						if( _page <= _total_pages) {
							playbill.lookupProduct(
								barcode,
								name,
								company,
								country_code,
								before_send_callback,
								success_callback,
								error_callback,
								complete_callback,
								stream_results,
								_page,
								_total_pages,
								_records
							);
						} else if(!stream_results || _total_pages <= 1) {
							// Return all the records to-date
							if(isFunction(success_callback)) success_callback(_records || [], false, 1);
						}
					}
				} else if( data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.status) && data.status.error) {
					if(isFunction(error_callback)) error_callback(data, data.status.http_code, data.status.error);
				} else {
					// Return all the records to-date
					if(isFunction(success_callback)) success_callback(_records || []);
				}
			} catch(err) {
				// Return the error response
				if(isFunction(error_callback)) error_callback(err);
			}
		},
		error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
		complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus, (_page <= _total_pages) , _page - 1)} : null)
	});
};

PlaybillInterface.prototype.lookupProductSOH = function(
	sku,
	name,
	barcode,
	company,
	country_code,
	active,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback,
	stream_results = false,
	_page,
	_total_pages,
	_records
) {
	var playbill = this;

	// Check Instance is valid
	if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
		var response = PlaybillInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, this._instance));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
		var response = PlaybillInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints["product_lookup"] == null || this._endpoints["product_lookup"] === undefined) {
		var response = PlaybillInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "ProductLookup"));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Headers are pre-loaded
	if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
		var response = PlaybillInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	var url = PlaybillInterface.prototype._proxy
	var sub_url = this._endpoints["product_soh_lookup"];
	var headers = this._headers;

	var params = {};
	if(sku) params["sku"] = sku;
	if(name) params["name"] = name;
	if(barcode) params["barcode"] = barcode;
	if(company) params["company"] = company;
	if(country_code) params["country_code"] = country_code;
	if(active) params["active"] = active === true || active === 1 ? true : false;
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
					var records = data.contents.records || [];
					try {
						_total_pages = data.contents.pagination.total_pages || 0;
					} catch(err) {
						_total_pages = 0;
					}

					if( records.length == 0 ) {
						// All records have been collected
						if(isFunction(success_callback)) success_callback(_records || []);
					} else {
						// Increment page counter
						_page = _page || 1;						
						_page++;

						// Extend the Product array to include the returned results if not streaming
						if(_records && !stream_results)
							$.extend(true, _records, data.contents.records);
						else
							_records = data.contents.records;
						
						// Return records collected thus far via streaming
						if( stream_results && _total_pages > 1) {
							if(isFunction(success_callback)) success_callback(_records || [], (_page <= _total_pages) , _page - 1);
						}

						// Query the next page if there are still more pages
						if( _page <= _total_pages) {
							playbill.lookupProductSOH(
								sku,
								name,
								barcode,
								company,
								country_code,
								active,
								before_send_callback,
								success_callback,
								error_callback,
								complete_callback,
								stream_results,
								_page,
								_total_pages,
								_records
							);
						} else if(!stream_results || _total_pages <= 1) {
							// Return all the records to-date
							if(isFunction(success_callback)) success_callback(_records || [], false, 1);
						}
					}
				} else if( data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.status) && data.status.error) {
					if(isFunction(error_callback)) error_callback(data, data.status.http_code, data.status.error);
				} else {
					// Return all the records to-date
					if(isFunction(success_callback)) success_callback(_records || []);
				}
			} catch(err) {
				// Return the error response
				if(isFunction(error_callback)) error_callback(err);
			}
		},
		error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
		complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus, (_page <= _total_pages) , _page - 1)} : null)
	});
};

PlaybillInterface.prototype.lookupProductFamily = function(
	sku,
	name,
	barcode,
	company,
	country_code,
	active,
	before_send_callback,
	success_callback,
	error_callback,
	complete_callback,
	stream_results = false,
	_page,
	_total_pages,
	_records
) {
	var playbill = this;
	
	// Check Instance is valid
	if(this._instance == null || this._instance === undefined || jQuery.isEmptyObject(this._instance)) {
		var response = PlaybillInterface.prototype._response_codes["ERR_INSTANCE_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, this._instance));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints == null || this._endpoints === undefined || jQuery.isEmptyObject(this._endpoints)) {
		var response = PlaybillInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Endpoints are pre-loaded
	if(this._endpoints["product_family_lookup"] == null || this._endpoints["product_family_lookup"] === undefined) {
		var response = PlaybillInterface.prototype._response_codes["ERR_ENDPOINTS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, String.format(response.statusText, "ProductFamilyLookup"));
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}
	
	// Check Headers are pre-loaded
	if(this._headers == null || this._headers === undefined || jQuery.isEmptyObject(this._headers)) {
		var response = PlaybillInterface.prototype._response_codes["ERR_HEADERS_INVALID"];
		if(isFunction(error_callback)) error_callback(response, response.statusCode, response.statusText);
		if(isFunction(complete_callback)) complete_callback(response);
		return false;
	}

	var url = PlaybillInterface.prototype._proxy
	var sub_url = this._endpoints["product_family_lookup"];
	var headers = this._headers;

	var params = {};
	if(sku) params["sku"] = sku;
	if(name) params["name"] = name;
	if(barcode) params["barcode"] = barcode;
	if(company) params["company"] = company;
	if(country_code) params["country_code"] = country_code;
	if(active) params["active"] = active === true || active === 1 ? true : false;
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
					var records = data.contents.records || [];
					
					try {
						_total_pages = data.contents.pagination.total_pages || 0;
					} catch(err) {
						_total_pages = 0;
					}

					if( records.length == 0 ) {
						// All records have been collected
						if(isFunction(success_callback)) success_callback(_records || []);
					} else {
						// Increment page counter
						_page = _page || 1;
						_page++;

						// Extend the Product array to include the returned results if not streaming
						if(_records && !stream_results)
							$.extend(true, _records, data.contents.records);
						else
							_records = data.contents.records;
						
						// Return records collected thus far via streaming
						if( stream_results && _total_pages > 1) {
							if(isFunction(success_callback)) success_callback(_records || [], (_page <= _total_pages) , _page - 1);
						}

						// Query the next page if there are still more pages
						if( _page <= _total_pages) {
							playbill.lookupProductFamily(
								sku,
								name,
								barcode,
								company,
								country_code,
								active,
								before_send_callback,
								success_callback,
								error_callback,
								complete_callback,
								stream_results,
								_page,
								_total_pages,
								_records
							);
						} else if(!stream_results || _total_pages <= 1) {
							// Return all the records to-date
							if(isFunction(success_callback)) success_callback(_records || [], false, 1);
						}
					}
				} else if( data && !jQuery.isEmptyObject(data) && !jQuery.isEmptyObject(data.status) && data.status.error) {
					if(isFunction(error_callback)) error_callback(data, data.status.http_code, data.status.error);
				} else {
					// Return all the records to-date
					if(isFunction(success_callback)) success_callback(_records || []);
				}
			} catch(err) {
				// Return the error response
				if(isFunction(error_callback)) error_callback(err);
			}
		},
		error:		(error_callback ? function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)} : null),
		complete:	(complete_callback ? function(xhr, textStatus) {complete_callback(xhr, textStatus, (_page <= _total_pages) , _page - 1)} : null)
	});
};
