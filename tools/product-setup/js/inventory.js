var department_options_api 			= "/tools/json/departments.json";
var hierarchy_options_api 			= "/tools/json/hierarchy.json";
var variant_options_api 			= "/tools/json/variants.json";
var brand_codes_api			 		= "/tools/json/brand-aa-lookup.json";
var defaults_list_api				= "/tools/json/defaults.json";
var business_streams_options_api	= "/tools/json/business-streams.json";
var variant_order_options_api		= "/tools/json/variant-ordering.json";

function isFunction(functionToCheck) {
	var getType = {};
 	return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
}

function InventoryHelper(instance, devmode) {
	this.instnace = instance;
	this.devmode = devmode;
}

function InventoryInterface() {
	this.interface = new DearSystemsInterface();
}

function InventoryInterface(instance, devmode) {
	this.instance = instance;
	this.interface = new DearSystemsInterface(instance);
	this.devmode = devmode;
}

InventoryInterface.prototype.setInstance = function(
	instance,
	devmode
){
	this.interface = new DearSystemsInterface(instance);
	this.devmode = devmode;
};

InventoryInterface.prototype.getInstances = function(
	before_callback,
	success_callback,
	error_callback,
	complete_callback
){
	// Pre-requisite library for API support
	if( !isFunction(this.interface.getInstances) )
		return false;

	this.interface.getInstances (
		before_callback,
		success_callback,
		error_callback,
		complete_callback
	);
};

InventoryInterface.prototype.generateBaseSKU = function (
	department,
	brand,
	brand_code,
	name,
	category,
	sub_category,
	year,
	priority
) {
	// Clean inputs
	department 		= $.trim(department);
	brand			= $.trim(brand);
	brand_code 		= $.trim(brand_code).replace(/[^a-z0-9\s]/gi, '')
  brand_code = $.trim(brand_code)
	name 			= $.trim(name);
	category 		= $.trim(category);
	sub_category 	= $.trim(sub_category);
	year 			= $.trim(year);
	priority 		= priority && $.isNumeric(priority) ? priority : 99;

	// Escape if any values are invalid
	if ( !department || !name || !category || !sub_category )
		return null;
	
	/* Regex capture the follow:
	 *		brand_part = see rules below, 
	 *			e.g. 'Sydney Swans' = 'SYSW', 'Sydney Roosters' = 'SYRO'
	 *		year_part = last two year characters in a short or long year notation, 
	 *			e.g. '2016' = '16, 'Summer 16' = '16'
	 *		category_part = first two characters of the category, 
	 *			e.g. 'Apparel' = 'AP', 'Recordings' = 'RE'
	 */
	var year_regex 		 = /(\d{2})/g;
	//var brand_regex 	 = /^(\S{4})/g;
	var brand_regex      = /^\b(\S{1})\S*\s*\b(\S{1})\S*\s*\b(\S{1})\S*\s*\b(\S{1})\S*|^\b(\S{2})\S*\s*\b(\S{1})\S*\s*\b(\S{1})\S*|^\b(\S{2})\S*\s*\b(\S{2})\S*\s*$|^\b(\S{3})\S*\s*$/;
	var category_regex 	 = /^(\S{2})/g;
		
	var year_matches 	 = year.match(year_regex);
	var brand_matches	 = brand_code.match(brand_regex);
	var category_matches = category.match(category_regex);

	var year_part 		 = !year_matches ? 
								null : 
								year_matches.length > 1 ? 
									year_matches[1] : 
									year_matches[0];
	/*
	var brand_part 		 = brand_matches && brand_matches.length > 0 ? 
								brand_matches[0] : 
								null;
    */

  var brand_part = null;
	var category_part 	 = category_matches && category_matches.length > 0 ?
								category_matches[0] :
								null;
    console.log(brand_matches);
	/* If matches were found then capture either:
	 *		Option 1 : character index 0-1,0,0 of the first 3 text words
	 *		Option 2 : character indexes 0-1,0-1 of the first 2 text words
	 *		Option 3 : character indexes 0-2 of the first text word
	 *
	 */
	if ( brand_matches ) {
		if ( brand_matches[1] && brand_matches[2] && brand_matches[3] && brand_matches[4])
		    brand_part = brand_matches[1] + brand_matches[2] + brand_matches[3] + brand_matches[4];
		else if ( brand_matches[5] && brand_matches[6] && brand_matches[7] )
			brand_part = brand_matches[5] + brand_matches[6] + brand_matches[7];
		else if ( brand_matches[8] && brand_matches[9] )
			brand_part = brand_matches[8] + brand_matches[9];
		else 
			brand_part = brand_matches[10];
	}

	// Escape if any values are invalid
	if ( !year_part || !brand_part || !category_part )
		return null;
	
	// Strip special parts from @name as to only contain the description
	var full_year  = (new Date("01/01/"+year_part)).getFullYear();
	
	var pass_1_regex = new RegExp("\\b"+brand+"\\b", "i");
	var pass_2_regex = new RegExp("\\b"+year+"\\b|\\b"+year_part+"\\b|\\b"+full_year+"\\b" , "i");
	
	var stripped_name = name.replace(pass_1_regex, "");
	stripped_name = stripped_name.replace(pass_2_regex, "");
	
	// Remove any extra spaces created
	stripped_name = $.trim(stripped_name).replace(/ +(?= )/g,"");;
	
	var short_name = "";
	var short_name_regex 	= /\b(\S{1})\S*\b\s*\b(\S{1})\S*\b\s*\b(\S{1})\S*\b\s*$|\b(\S{1})\S*\b\s*\b(\S{2})\S*\b\s*$|\b(\S{3})\S*\b\s*$/;
								
	var short_name_matches 	= stripped_name.match(short_name_regex);

	/* If matches were found then capture either:
	 *		Option 1 : character index 0 of the last 3 text words
	 *		Option 2 : character indexes 0-1 of the 2nd last text word and character index 0 of the last text word
	 *		Option 3 : character indexes 0-2 of the last text word
	 *
	 */
	if ( short_name_matches ) {
		if ( short_name_matches[1] && short_name_matches[2] && short_name_matches[3] )
			short_name = short_name_matches[1] + short_name_matches[2] + short_name_matches[3];
		else if ( short_name_matches[4] && short_name_matches[5] )
			short_name = short_name_matches[4] + short_name_matches[5];
		else 
			short_name = short_name_matches[6];
	}
	
	// Pad the name for any missing key characters
	short_name = sub_category.substring(0,1) + short_name + "ZZZZ";
	// Capture the first 4 characters
	short_name = short_name.substring(0,4).replace(" ","");
	
	var sku = (brand_part + year_part + category_part + ("00"+priority).slice(-2) + short_name).toUpperCase();
	
	return sku;
};

InventoryInterface.prototype.nextProductCodeSequence = function (
	base_sku,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback,
	_seq
) {
	var self = this;
	
	if ( !base_sku )
		return;
		
	// Pre-requisite library for API support
	if( !isFunction(self.interface.getProductFamilies) )
		return false;
	
	// Set the sequence to start at
	_seq = _seq || 0;
	_seq++;
	
	var new_sku = base_sku + _seq;
	
	self.interface.getProductFamilies(
		null,
		new_sku,
		before_callback,
		function(data, textStatus, textErrorThrown) {
			if( !textStatus ) {
				console.log(data);
				// Return the next sequential SKU or NULL on error
				if( data && $.isArray(data) && data.length == 0 ) {
					if(isFunction(complete_callback)) complete_callback(data);
					if(isFunction(sucess_callback)) sucess_callback( { "sku" : new_sku } );
				} else if ( data && $.isArray(data) &&  data.length > 0 ) {
					self.nextProductCodeSequence (
						base_sku,
						before_callback,
						sucess_callback,
						error_callback,
						complete_callback,
						_seq
					);
				} else {
					if(isFunction(complete_callback)) complete_callback(data);
					if(isFunction(sucess_callback)) sucess_callback( { "sku" : null } );
				}
			} else {
				if(error_callback) error_callback(data, textStatus, textErrorThrown);
			}
		},
		function(xhr, textStatus, textErrorThrown) {
			if(isFunction(complete_callback)) complete_callback(xhr);
			if(isFunction(error_callback)) error_callback(xhr, textStatus, textErrorThrown);
		},
		null,
		self.devmode
	);
};

InventoryInterface.prototype.checkProductName = function(
	name,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	if ( !name )
		return;
		
	// Pre-requisite library for API support
	if( !isFunction(this.interface.getProductFamilies) )
		return false;

	this.interface.getProductFamilies(
		name,
		null,
		before_callback,
		function(data, textStatus, textErrorThrown) {
			
			if( textStatus === undefined || !textStatus ) {		
				var matching_name = name;
				
				// Check all the returned results for any matching names
				if( data && $.isArray(data) && data.length < 1 ) {
					matching_name = name;
				} else if ( data && $.isArray(data) &&  data.length > 0 ) {
					$.each(data, function(key, val) {
						if( val && val.Name && val.Name.localeCompare(name) == 0 ) {
							matching_name = null;
							return;
						}
					});
				} else {
					// Matching name was found
					matching_name = null;
				}

				if(isFunction(complete_callback)) complete_callback(data);
				if(isFunction(sucess_callback)) sucess_callback( { "name" : matching_name } );
			} else {
				if(error_callback) error_callback(data, textStatus, textErrorThrown);
			}
		},
		function(xhr, textStatus, textErrorThrown) {
			if(isFunction(complete_callback)) complete_callback(xhr);
			if(isFunction(error_callback)) error_callback(xhr, textStatus, textErrorThrown);
		},
		null,
		this.devmode
	);	
};

InventoryInterface.prototype.checkProductSKU = function(
	sku,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	if ( !sku )
		return;
		
	// Pre-requisite library for API support
	if( !isFunction(this.interface.getProducts) )
		return false;


	this.interface.getProducts(
		null,
		sku,
		before_callback,
		function(data, textStatus, textErrorThrown) { 

			if( textStatus === undefined || !textStatus ) {		
				var matching_sku = sku;
				// Check all the returned results for any matching names
				if( data && $.isArray(data) && data.length < 1 ) {
					matching_sku = sku;
				} else if ( data && $.isArray(data) &&  data.length > 0 ) {
					$.each(data, function(key, val) {
						if( val && val.SKU && val.SKU.localeCompare(sku) == 0 ) {
							matching_sku = null;
							return;
						}
					});
				} else {
					// Matching name was found
					matching_sku = null;
				}

				if(isFunction(complete_callback)) complete_callback(data);
				if(isFunction(sucess_callback)) sucess_callback( { "sku" : matching_sku } );
			} else {
				if(error_callback) error_callback(data, textStatus, textErrorThrown);
			}
		},
		function(xhr, textStatus, textErrorThrown) {
			if(isFunction(complete_callback)) complete_callback(xhr);
			if(isFunction(error_callback)) error_callback(xhr, textStatus, textErrorThrown);
		},
		null,
		this.devmode
	);
}

InventoryInterface.prototype.checkProductVariantName = function(
	name,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	if ( !name )
		return;
		
	// Pre-requisite library for API support
	if( !isFunction(this.interface.getProducts) )
		return false;


	this.interface.getProducts(
		name,
		null,
		before_callback,
		function(data, textStatus, textErrorThrown) { 

			if( textStatus === undefined || !textStatus ) {		
				var matching_name = name;
				
				// Check all the returned results for any matching names
				if( data && $.isArray(data) && data.length < 1 ) {
					matching_name = name;
				} else if ( data && $.isArray(data) &&  data.length > 0 ) {
					$.each(data, function(key, val) {
						if( val && val.Name && val.Name.localeCompare(name) == 0 ) {
							matching_name = null;
							return;
						}
					});
				} else {
					// Matching name was found
					matching_name = null;
				}

				if(isFunction(complete_callback)) complete_callback(data);
				if(isFunction(sucess_callback)) sucess_callback( { "name" : matching_name } );
			} else {
				if(error_callback) error_callback(data, textStatus, textErrorThrown);
			}
		},
		function(xhr, textStatus, textErrorThrown) {
			if(isFunction(complete_callback)) complete_callback(xhr);
			if(isFunction(error_callback)) error_callback(xhr, textStatus, textErrorThrown);
		},
		null,
		this.devmode
	);
}

InventoryInterface.prototype.getProductCategories = function(
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	this.interface.getProductCategories (
		before_callback,
		sucess_callback,
		error_callback,
		complete_callback,
		this.devmode
	);
};

InventoryInterface.prototype.getProductBrands = function(
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	this.interface.getProductBrands (
		before_callback,
		sucess_callback,
		error_callback,
		complete_callback,
		this.devmode
	);
};

InventoryInterface.prototype.getChartOfAccounts = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	this.interface.getChartOfAccounts (
		data.name,
		data.accountClass,
		data.status,
		data.acceptPayments,
		before_callback,
		sucess_callback,
		error_callback,
		complete_callback,
		this.devmode
	);
};

InventoryInterface.prototype.getTaxationRules = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	this.interface.getTaxationRules (
		data.purchaseRules,
		data.saleRules,
		data.isActive,
		before_callback,
		sucess_callback,
		error_callback,
		complete_callback
	);
};

InventoryInterface.prototype.getProductFamilies = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	this.interface.getProductFamilies (
		data.name,
		data.sku,
		before_callback,
		sucess_callback,
		error_callback,
		complete_callback
	);
};

InventoryInterface.prototype.getProducts = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	this.interface.getProducts (
		data.name,
		data.sku,
		before_callback,
		sucess_callback,
		error_callback,
		complete_callback
	);
};

InventoryInterface.prototype.postProductFamily = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	this.interface.postMappedProductFamily (
		data,
		before_callback,
		sucess_callback,
		error_callback,
		complete_callback
	);
};

InventoryInterface.prototype.postProduct = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	this.interface.postMappedProduct (
		data,
		before_callback,
		sucess_callback,
		error_callback,
		complete_callback
	);
};

InventoryInterface.prototype.deactivateProduct = function(
	id,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	this.interface.putProduct (
		{
			"ID" : id,
			"Status" : "Deprecated"
		},
		before_callback,
		sucess_callback,
		error_callback,
		complete_callback
	);
};

InventoryHelper.prototype.getDepartments = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	$.ajax({
		url:		department_options_api,
		method:		"GET",
		data:		data,
		dataType:	"json",
		cache:		false,
		beforeSend: isFunction(before_callback) 	? before_callback	: function() {},
		success:	function(xdata) { InventoryHelper.prototype.__dataForInstance(data.instance, xdata, sucess_callback); },
		error:		isFunction(error_callback) 		? error_callback	: function() {},
		complete:	isFunction(complete_callback) 	? complete_callback	: function() {}
	})
};

InventoryHelper.prototype.getBusinessStreams = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	$.ajax({
		url:		business_streams_options_api,
		method:		"GET",
		data:		data,
		dataType:	"json",
		cache:		false,
		beforeSend: isFunction(before_callback) 	? before_callback	: function() {},
		success:	function(xdata) { InventoryHelper.prototype.__dataForInstance(data.instance, xdata, sucess_callback); },
		error:		isFunction(error_callback) 		? error_callback	: function() {},
		complete:	isFunction(complete_callback) 	? complete_callback	: function() {}
	})
};

InventoryHelper.prototype.getVariantOrdering = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	$.ajax({
		url:		variant_order_options_api,
		method:		"GET",
		data:		data,
		dataType:	"json",
		cache:		false,
		beforeSend: isFunction(before_callback) 	? before_callback	: function() {},
		success:	isFunction(sucess_callback) 	? sucess_callback	: function() {},
		error:		isFunction(error_callback) 		? error_callback	: function() {},
		complete:	isFunction(complete_callback) 	? complete_callback	: function() {}
	})
};

InventoryHelper.prototype.getBrandCodes = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	$.ajax({
		url:		brand_codes_api,
		method:		"GET",
		data:		data,
		dataType:	"json",
		cache:		false,
		beforeSend: isFunction(before_callback) 	? before_callback	: function() {},
		success:	isFunction(sucess_callback) 	? sucess_callback	: function() {},
		error:		isFunction(error_callback) 		? error_callback	: function() {},
		complete:	isFunction(complete_callback) 	? complete_callback	: function() {}
	})
};

InventoryHelper.prototype.getHierarchies = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	$.ajax({
		url:		hierarchy_options_api,
		method:		"GET",
		data:		data,
		dataType:	"json",
		cache:		false,
		beforeSend: isFunction(before_callback) 	? before_callback	: function() {},
		success:	function(xdata) { InventoryHelper.prototype.__dataForInstance(data.instance, xdata, sucess_callback); },
		error:		isFunction(error_callback) 		? error_callback	: function() {},
		complete:	isFunction(complete_callback) 	? complete_callback	: function() {}
	})
};

InventoryHelper.prototype.getVariants = function(
	data,
	before_callback,
	sucess_callback,
	error_callback,
	complete_callback
) {
	$.ajax({
		url:		variant_options_api,
		method:		"GET",
		data:		data,
		dataType:	"json",
		cache:		false,
		beforeSend: isFunction(before_callback) 	? before_callback	: function() {},
		success:	function(xdata) { InventoryHelper.prototype.__dataForInstance(data.instance, xdata, sucess_callback); },
		error:		isFunction(error_callback) 		? error_callback	: function() {},
		complete:	isFunction(complete_callback) 	? complete_callback	: function() {}
	})
};

InventoryHelper.prototype.__dataForInstance = function(
	instance,
	data,
	callback
) {
	// Extract instance specific data tree
	if(!jQuery.isEmptyObject(data) && instance !== undefined) {
		data = data.hasOwnProperty("contents") ? data.contents: [];
		data = data.hasOwnProperty(instance) ? data[instance] : [];
		data = { "contents" : data };
	}

	if( isFunction(callback) ) {
		callback(data);
	}
}
