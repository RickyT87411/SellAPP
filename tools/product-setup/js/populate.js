var defaults_list_api			= "/tools/json/defaults.json";

// lookups
var brand_aa_lookup = {};
var variant_sizing_lookup = {};
var master_defaults_lookup = {};
var defaults_lookup = {};

/* Default dropdown menu options */
var loading_class = "loading";

var loading_option = "<option value='' disabled selected>Loading list please wait...</option>"
var available_option = "<option value=''  selected='selected'>Select an option</option>";
var no_option  = "<option value='' disabled selected>No options</option>";
var error_option = "<option value='' disabled selected>Loading error</option>";

var no_instance_list  = "<li><a href='#'><p><i>No Instances</i></p></a></li>";
var error_instance_list  = "<li><a href='#'><p><b>Error!</b></p></a></li>";

/**	USER FUNCTIONS
  *	Functions that can be used dynamically through the webpage
  *	=========================
  */

// Fetches URL params fed into the current page
$.urlParam = $.urlParam || function(name){
	var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	if (results==null){
	   return -1;
	}
	else{
	   return results[1] || null;
	}
};

// Converts a JSON object into a String
JSON.stringify = JSON.stringify || function (obj) {
    var t = typeof (obj);
    if (t != "object" || obj === null) {
        // simple data type
        if (t == "string") obj = '"'+obj+'"';
        return String(obj);
    }
    else {
        // recurse array or object
        var n, v, json = [], arr = (obj && obj.constructor == Array);
        for (n in obj) {
            v = obj[n]; t = typeof(v);
            if (t == "string") v = '"'+v+'"';
            else if (t == "object" && v !== null) v = JSON.stringify(v);
            json.push((arr ? "" : '"' + n + '":') + String(v));
        }
        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
    }
};

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
    
    // start with the second argument (i = 1)
    for (var i = 1; i < arguments.length; i++) {
        // "gm" = RegEx options for Global search (more than one instance)
        // and for Multiline search
        var regEx = new RegExp("\\{" + (i - 1) + "\\}", "gm");
        theString = theString.replace(regEx, arguments[i]);
    }
    
    return theString;
};

function parse_product_category ( category ) {
	var regex = /\s*([^|]*)\s*/;
	var matches = $.trim(category).match(regex);
	return matches && matches.length > 1 ? $.trim(matches[1]) : null;
}

var corefunc = function(
	id,
	callfunc,
	data,
	opfunc
) {
	if( !isFunction(callfunc) )
		return false;

	callfunc(
		data,
		function(xhr, obj) {
			$(id).toggleClass(loading_class, true);
			$(id).html(loading_option);
		},
		function(data) { 
			var options = $(id);
			if(!jQuery.isEmptyObject(data)) {
				// Clear the current content of the select
				options.html('');
				
				// Callback operation function passing in data
				if(isFunction(opfunc)) opfunc(options, data);
				
				if ( $('select'+id+' option').length > 1 ) {
					options.prepend($(available_option));
				} else {
					options.prepend($(no_option));
				}
				
			} else {
				// Clear the current content of the select
				options.html('');
				options.append($(no_option));
			}
		},
		function(xhr, textStatus, thrownError){
			var options = $(id);
			options.html('');

			if( xhr.status == 200 ) {
				var data = JSON.parse(xhr.responseText);
	
				if(!jQuery.isEmptyObject(data)) {
					// Clear the current content of the select
					options.html('');
				
					// Callback operation function passing in data
					if(isFunction(opfunc)) opfunc(options, data);
				
					if ( $('select'+id+' option').length > 1 )
						options.prepend($(available_option));
					else
						options.prepend($(no_option));
				} else {
					// Clear the current content of the select
					options.html('');
				}
			} else if(xhr.status == 404) {
				options.append($(no_option));
			} else {
				options.append($(error_option));
			}
		},
		function(xhr, textStatus) { 
			$(id).toggleClass(loading_class, false); 
		}
	);
};

// Populates the available Inventory Instances from 
function populate_inventory_instances (
	sel,
	opt,
	inventory,
	callback
) {
	var selector = $(sel);
	var options = $(opt);

	inventory.getInstances (
		function(xhr, obj) {
			
		},
		function(data) { 
			if(!jQuery.isEmptyObject(data)) {
				var selector = $(sel);
				var options = $(opt);
				// Clear the current content of the select
				selector.html('');
				options.html('');
				// Append each item of the resultset
				$.each(data, function(key, val) {
					selector.append(
						$("<li>").append(
							$("<a>").append(
								$("<p>").text(val)
							).attr("href","#")
						)
					);
					options.append($("<option />").val(val).text(val));
				});
			} else {
				var selector = $(sel);
				var options = $(opt);
				// Clear the current content of the select
				selector.html('');
				selector.append($(no_instance_list));
			}
		},
		function(xhr, textStatus, thrownError){
			var selector = $(sel);
			var options = $(opt);
			selector.html('');	
				
			if(xhr.status == 404) {
				options.html('');
				selector.append($(no_instance_list));
			} else {
				options.html('');
				selector.append($(error_instance_list));
			}
		},
		function(xhr, textStatus) {
			var selector = $(sel);
			var options = $(opt);
			selector.change();
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}

// Populates the Department dropdown
function populate_departments(
	id, 
	callback
) {
	corefunc (
		id,
		InventoryHelper.prototype.getDepartments,
		{ instance: inventory.instance },
		function(options, data) {
			if ( data.contents ) {
				// Append each item of the resultset
				$.each(data.contents, function(key, val) {
					options.append($("<option />").val(key).text(val));
				});
				options.trigger('change');
			}
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}

// Populates the Brand dropdown from DEAR
function populate_product_brands (
	id, 
	inventory, 
	callback
) {
	corefunc (
		id,
		InventoryHelper.prototype.getBrandCodes,
		{},
		function(options, data) {
			if ( data.contents ) {
				brand_aa_lookup = data.contents;
			}

			// Get all Dear Brands
			inventory.getProductBrands(
				function(xhr, obj) {
					$(id).toggleClass(loading_class, true);
					$(id).html(loading_option);
				},
				function(data) { 
					var options = $(id);
			
					if(!jQuery.isEmptyObject(data)) {
						// Clear the current content of the select
						options.html('');

						// Append each item of the resultset
						$.each(data.contents, function(key, val) {
							options.append($("<option />").val(val.Name).text(val.Name));
						});
						options.prepend($(available_option));
						options.trigger('change');
					} else {
						// Clear the current content of the select
						options.html('');
						options.append($(no_option));
					}
				},
				function(xhr, textStatus, thrownError){
					var options = $(id);
					options.html('');
	
					if(xhr.status == 404) {
						options.append($(no_option));
					} else {
						options.append($(error_option));
					}
				},
				function(xhr) {
					$(id).toggleClass(loading_class, false);
					// Optional callback after completion
					if(isFunction(callback)) callback(this);
				}
			);
		}
	);
}

// Populates the Variant Sizing lookup table
function populate_variant_sizing_lookup (
	callback
) {
	InventoryHelper.prototype.getVariantOrdering(
		{ instance: inventory.instance },
		null,
		function(data) {
			if ( data.contents ) {
				variant_sizing_lookup = data.contents;
			}
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}

// Populates the Business Streams dropdown from JSON mapping
function populate_business_streams (
	id, 
	callback
) {
	corefunc (
		id,
		InventoryHelper.prototype.getBusinessStreams,
		{ instance: inventory.instance },
		function(options, data) {
			if ( data.contents ) {
				// Append each item of the resultset
				$.each(data.contents, function(key, val) {
					options.append($("<option />").val(key).text(val));
				});
			}
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}

// Populates the Cost of Goods Sold Account dropdown from DEAR
function populate_product_cogs_account (
	id, 
	inventory, 
	callback
) {
	// Get all Dear COGS Account codes
	inventory.getChartOfAccounts(
		{
			"accountClass": "EXPENSE",
			"status": "ACTIVE"
		},
		function(xhr, obj) {
			$(id).toggleClass(loading_class, true);
			$(id).html(loading_option);
		},
		function(data) { 
			var options = $(id);
	
			if(!jQuery.isEmptyObject(data)) {
				// Clear the current content of the select
				options.html('');

				// Append each item of the resultset
				$.each(data.contents, function(key, val) {
					options.append($("<option />").val(val.Code).text(val.Name));
				});
				options.prepend($(available_option));
			} else {
				// Clear the current content of the select
				options.html('');
				options.append($(no_option));
			}
		},
		function(xhr, textStatus, thrownError){
			var options = $(id);
			options.html('');

			if(xhr.status == 404) {
				options.append($(no_option));
			} else {
				options.append($(error_option));
			}
		},
		function(xhr) {
			$(id).toggleClass(loading_class, false);
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}

// Populates the Inventory Account dropdown from DEAR
function populate_product_inventory_account (
	id, 
	inventory, 
	callback
) {
	// Get all Dear COGS Account codes
	inventory.getChartOfAccounts(
		{
			"accountClass": "ASSET",
			"status": "ACTIVE"
		},
		function(xhr, obj) {
			$(id).toggleClass(loading_class, true);
			$(id).html(loading_option);
		},
		function(data) { 
			var options = $(id);
	
			if(!jQuery.isEmptyObject(data)) {
				// Clear the current content of the select
				options.html('');
				// Append each item of the resultset
				$.each(data.contents, function(key, val) {
					options.append($("<option />").val(val.Code).text(val.Name));
				});
				options.prepend($(available_option));
			} else {
				// Clear the current content of the select
				options.html('');
				options.append($(no_option));
			}
		},
		function(xhr, textStatus, thrownError){
			var options = $(id);
			options.html('');

			if(xhr.status == 404) {
				options.append($(no_option));
			} else {
				options.append($(error_option));
			}
		},
		function(xhr) {
			$(id).toggleClass(loading_class, false);
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}

// Populates the Revenue Account dropdown from DEAR
function populate_product_revenue_account (
	id, 
	inventory, 
	callback
) {
	// Get all Dear COGS Account codes
	inventory.getChartOfAccounts(
		{
			"accountClass": "REVENUE",
			"status": "ACTIVE"
		},
		function(xhr, obj) {
			$(id).toggleClass(loading_class, true);
			$(id).html(loading_option);
		},
		function(data) { 
			var options = $(id);
	
			if(!jQuery.isEmptyObject(data)) {
				// Clear the current content of the select
				options.html('');
				// Append each item of the resultset
				$.each(data.contents, function(key, val) {
					options.append($("<option />").val(val.Code).text(val.Name));
				});
				options.prepend($(available_option));
			} else {
				// Clear the current content of the select
				options.html('');
				options.append($(no_option));
			}
		},
		function(xhr, textStatus, thrownError){
			var options = $(id);
			options.html('');

			if(xhr.status == 404) {
				options.append($(no_option));
			} else {
				options.append($(error_option));
			}
		},
		function(xhr) {
			$(id).toggleClass(loading_class, false);
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}

// Populates the Expense Account dropdown from DEAR
function populate_product_expense_account (
	id, 
	inventory, 
	callback
) {
	// Get all Dear COGS Account codes
	inventory.getChartOfAccounts(
		{
			"accountClass": "EXPENSE",
			"status": "ACTIVE"
		},
		function(xhr, obj) {
			$(id).toggleClass(loading_class, true);
			$(id).html(loading_option);
		},
		function(data) { 
			var options = $(id);
	
			if(!jQuery.isEmptyObject(data)) {
				// Clear the current content of the select
				options.html('');

				// Append each item of the resultset
				$.each(data.contents, function(key, val) {
					options.append($("<option />").val(val.Code).text(val.Name));
				});
				options.prepend($(available_option));
			} else {
				// Clear the current content of the select
				options.html('');
				options.append($(no_option));
			}
		},
		function(xhr, textStatus, thrownError){
			var options = $(id);
			options.html('');

			if(xhr.status == 404) {
				options.append($(no_option));
			} else {
				options.append($(error_option));
			}
		},
		function(xhr) {
			$(id).toggleClass(loading_class, false);
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}

// Populates the ACTIVE Purchase Tax Rules from Dear
function populate_purchase_tax_rules (
	id, 
	inventory, 
	callback
) {
	// Get all Dear Brands
	inventory.getTaxationRules(
		{
			"purchaseRules": true,
			"saleRules" : null,
			"isActive" : true
		},
		function(xhr, obj) {
			$(id).toggleClass(loading_class, true);
			$(id).html(loading_option);
		},
		function(data) { 
			var options = $(id);
	
			if(!jQuery.isEmptyObject(data)) {
				// Clear the current content of the select
				options.html('');

				// Append each item of the resultset
				$.each(data.contents, function(key, val) {
					options.append($("<option />").val(val.Name).text(val.Name));
				});
				options.prepend($(available_option));
			} else {
				// Clear the current content of the select
				options.html('');
				options.append($(no_option));
			}
		},
		function(xhr, textStatus, thrownError){
			var options = $(id);
			options.html('');

			if(xhr.status == 404) {
				options.append($(no_option));
			} else {
				options.append($(error_option));
			}
		},
		function(xhr) {
			$(id).toggleClass(loading_class, false);
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}

// Populates the ACTIVE Sale Tax Rules from Dear
function populate_sale_tax_rules (
	id, 
	inventory, 
	callback
) {
	// Get all Dear Brands
	inventory.getTaxationRules(
		{
			"purchaseRules": null,
			"saleRules" : true,
			"isActive" : true
		},
		function(xhr, obj) {
			$(id).toggleClass(loading_class, true);
			$(id).html(loading_option);
		},
		function(data) { 
			var options = $(id);
	
			if(!jQuery.isEmptyObject(data)) {
				// Clear the current content of the select
				options.html('');

				// Append each item of the resultset
				$.each(data.contents, function(key, val) {
					options.append($("<option />").val(val.Name).text(val.Name));
				});
				options.prepend($(available_option));
			} else {
				// Clear the current content of the select
				options.html('');
				options.append($(no_option));
			}
		},
		function(xhr, textStatus, thrownError){
			var options = $(id);
			options.html('');

			if(xhr.status == 404) {
				options.append($(no_option));
			} else {
				options.append($(error_option));
			}
		},
		function(xhr) {
			$(id).toggleClass(loading_class, false);
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}

// Populates the Category dropdown from DEAR
function populate_product_categories (
	id,
	department,
	inventory,
	callback
) {
	var options = $(id);

	if ( !department ) {
		// Clear the current content of the select
		options.html('');
		options.append($(no_option));
		// Optional callback after completion
		if(isFunction(callback)) callback(this); 
	} else {
		inventory.getProductCategories (
			function(xhr, obj) {
				$(id).toggleClass(loading_class, true);
				$(id).html(loading_option);
			},
			function(data) { 
				if(!jQuery.isEmptyObject(data)) {
					var options = $(id);
					// Clear the current content of the select
					options.html('');
					
					// Append each item of the resultset
					$.each(data.contents, function(key, val) {
						if( val.Name.match( new RegExp('\\b'+department+'\\b', 'i') ) )
							 options.append($("<option />").val(val.Name).text(val.Name));
					});
					options.prepend($(available_option));
				} else {
					var options = $(id);
					// Clear the current content of the select
					options.html('');
					options.append($(no_option));
				}
			},
			function(xhr, textStatus, thrownError){
				var options = $(id);
				options.html('');	
					
				if(xhr.status == 404) {
					options.append($(no_option));
				} else {
					options.append($(error_option));
				}
			},
			function(xhr, textStatus) {
				var options = $(id);
				$(id).toggleClass(loading_class, false);
				options.change();
				// Optional callback after completion
				if(isFunction(callback)) callback(this); 
			}
		);
	}
}

// Propogates the broad Product Category to a particular category class
function populate_product_tier1 (
	id,
	category,
	callback
) {
	var options = $(id);
	var regex = /[^|]*\|\s*(.*)\s*$/;
	var matches = $.trim(category).match(regex);
	if ( matches && matches.length > 1 ) {
		// Set Tier 1 to default to category
		options.html('');
		options.append($("<option />").val(matches[1]).text(matches[1]));
		// Optional callback after completion
		if(isFunction(callback)) callback(this); 
	}
}

// Populates a specified Product Category Tier
function populate_product_tier (
	id,
	category,
	tier1,
	tier_n_key, 
	callback
) {
	corefunc (
		id,
		InventoryHelper.prototype.getHierarchies,
		{ instance: inventory.instance },
		function(options, data) {
			// Check valid JSON path
			if ( data.contents && data.contents[tier_n_key] && data.contents[tier_n_key][category] ) {
				// Iterate through the Tier n options and only append
				// items that are available under the Tier 1 hierarchy
				$.each(data.contents[tier_n_key][category], function(key, val) { 
					if ( val[tier1] )
						options.append($("<option />").val(key).text(key));
				});
			}
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}

// Populates a specified Product Variant
function populate_product_variant (
	id,
	variants_in_use,
	variant_n_key,
	callback
) {
	if( $(id).val() )
		return;
	
	corefunc (
		id,
		InventoryHelper.prototype.getVariants,
		{ instance: inventory.instance },
		function(options, data) {
			// Check valid JSON path
			if ( data.contents ) {
				// Iterate through the Variants and add all the
				// options that aren't currently in use
				$.each(data.contents, function(key, val) { 
					if ( !variants_in_use || !variants_in_use.indexOf(key) )
						options.append($("<option />").val(val).text(val));
				});	
				$(options).trigger('change');
			}
			// Optional callback after completion
			if(isFunction(callback)) callback(this); 
		}
	);
}


// Populates any default values
function populate_defaults (
	callback
) {
	var instance = inventory !== undefined && inventory.instance !== undefined ? inventory.instance : null;

	// Only populate the master lookup once
	if( jQuery.isEmptyObject(master_defaults_lookup) ) {
		$.ajax({
			type: 					"GET",
			url: 					defaults_list_api,
			data:					{},
			dataType:				'application/json',
			cache:					false,
			async:					true,
			beforeSend:	function(xhr, obj) {
			},
			success: function(data) { 
				if(!jQuery.isEmptyObject(data)) {
					// Check valid JSON path
					if ( data && data.contents ) {
						if(!jQuery.isEmptyObject(data) && instance !== undefined) {
							data = data.hasOwnProperty("contents") ? data.contents: [];
						
							// Set master with all companies
							master_defaults_lookup = data;
						
							data = data.hasOwnProperty(instance) ? data[instance] : [];
							data = { "contents" : data };
							
							// Populate instance defaults
							defaults_lookup = data.contents; 
						}					
					}
				}
			},
			error: 	function(xhr, textStatus, thrownError){
				if( xhr.status == 200 ) {
					var data = JSON.parse(xhr.responseText);
	
					if(!jQuery.isEmptyObject(data)) {
						// Check valid JSON path
						if ( data && data.contents ) {
							if(!jQuery.isEmptyObject(data) && instance !== undefined) {
								data = data.hasOwnProperty("contents") ? data.contents: [];
							
								// Set master with all companies
								master_defaults_lookup = data;
							
								data = data.hasOwnProperty(instance) ? data[instance] : [];
								data = { "contents" : data };
								
								// Populate instance defaults
								defaults_lookup = data.contents;
							}
						}
					}
				}
			},
			complete: function(xhr, textStatus) { 
				// Optional callback after completion
				if(isFunction(callback)) callback(defaults_lookup); 
			}
		});
	} else {
		// Extract instance defaults from master lookup
		data = master_defaults_lookup.hasOwnProperty(instance) ? master_defaults_lookup[instance] : [];
		data = { "contents" : data };
		
		// Populate instance defaults
		defaults_lookup = data.contents;
		// Optional callback after completion
		if(isFunction(callback)) callback(defaults_lookup); 
	}
}

function set_default (
	id,
	selector,
	department,
	brand,
	category
) { 
	var data = defaults_lookup.hasOwnProperty(selector) ? defaults_lookup[selector] : null;

	// Check that there is a default assignment for this {@DEPARTMENT}.{@BRAND}.{@CATEGORY}			
	if ( selector && data ) {
		department 	= data[department] ? department : "!{default}";
		brand 		= data[department] && data[department][brand] ? brand : "!{default}";
		category 	= data[department] && data[department][brand] && data[department][brand][category] ? category : "!{default}";

		// Return if not even a full default path is available
		if( !data[department] || !data[department][brand] || !data[department][brand][category] ) 
			return;
	
		// Assign the default value
		var old = $(id).val();
		var val = calculate_default_value(data[department][brand][category]);
	
		val = val && val !== undefined ? val : '';
	
		//	* If it is a <select> element then change the option selection based on matching values
		if ( $(id).is("select") ) {
			if( val && $('#'+$(id).attr('id')+' option[value="'+val.replace('"','\\"')+'"]').val() !== undefined ) 
				$('#'+$(id).attr('id')+' option[value="'+val.replace('"','\\"')+'"]').prop('selected', 'selected');
			else if ( $('#'+$(id).attr('id')+' option').filter(function () { return $(this).html() == val; }).val() !== undefined )
				$('#'+$(id).attr('id')+' option').filter(function () { return $(this).html() == val; }).prop('selected', 'selected');
			else
				$(id).find('option[value=""]').prop('selected', 'selected');
		
			//$(id).trigger('change').trigger('focusout');
		} else {
			$(id).val(val);
		}

		// Trigger any change events if value has changed
		if( val !== undefined && val.localeCompare(old) != 0 ) {
			$(id).trigger('change').trigger('focusout');
		}
	}
}

function calculate_default_value(val, target) {
	if (!val)
		return null;

	// Regex for special calculation expression
	var regex = /^\!\{(\w*)\}$/;
	var match = val.match(regex);
	var ret = val;

	// If a match was found then evaluate to calculated expression
	if( match && match.length && match.length > 1 ) {
		var expr = match[1];
		
		// Test for any sub expressions that invoke nested filters
		var subregex = /(.*):(.*)/;
		var submatch = expr.match(subregex);
		var subfunc = submatch && submatch.length > 2 ? submatch[1] : null;
		var subexpr = subfunc ? submatch[2] : null;
		
		// Replace the expression with the sub expression if found
		expr = subexpr || expr;
		
		var season = '';
		
		switch ( expr ) {
			case 'current_season':
				season = get_season(new Date().getMonth());
			case 'current_year':
				var year = new Date().getFullYear();
				ret = year + (season ? '/' + season : '');
				break;
			case 'blank':
				ret = '';
			default:
				break;
		}
	}
	
	return ret;
}

function get_season(month) {
    var season = '';
    switch(month) {
        case 11:
        case 0:
        case 1:
            season = 'Summer';
        break;
        case 2:
        case 3:
        case 4:
            season = 'Autumn';
        break;
        case 5:
        case 6:
        case 7:
            season = 'Winter';
        break;
        case 8:
        case 9: 
        case 10:
            season = 'Spring';
        break;
    }
	return season;
}