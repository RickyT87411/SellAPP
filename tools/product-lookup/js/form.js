// Dev mode ON/OFF
var devmode = false;

var proxy = '/SimplePHPProxy.php';

var playbill_connector = null;
var submit_lock = false;

String.prototype.escapeSpecialChars = String.prototype.escapeSpecialChars || function() {
    return this
    	.replace(/[\\]/g, '\\\\')
		.replace(/[\"]/g, '\\\"')
		.replace(/[\/]/g, '\\/')
		.replace(/[\b]/g, '\\b')
		.replace(/[\f]/g, '\\f')
		.replace(/[\n]/g, '\\n')
		.replace(/[\r]/g, '\\r')
		.replace(/[\t]/g, '\\t');
};

String.prototype.escapeSpecialXMLChars = String.prototype.escapeSpecialXMLChars || function() {
    return this
    	.replace(/[\"]/g, '&quot;')
		.replace(/[\']/g, '&apos;')
		.replace(/[<]/g, '&lt;')
		.replace(/[>]/g, '&gt;')
};

String.prototype.unescapeSpecialXMLChars = String.prototype.unescapeSpecialXMLChars || function() {
    return this
    	.replace(/&quot;/g, '"')
		.replace(/&apos;/g, '\'')
		.replace(/&lt;/g, '<')
		.replace(/&gt;/g, '>')
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

String.prototype.hashCode = function() {
  var hash = 0, i, chr, len;
  if (this.length === 0) return hash;
  for (i = 0, len = this.length; i < len; i++) {
    chr   = this.charCodeAt(i);
    hash  = ((hash << 5) - hash) + chr;
    hash |= 0; // Convert to 32bit integer
  }
  return hash;
};

var matrix = {};

var Quagga = window.Quagga;
var BarcodeReader = null;

function init(
	callback
) {
	var domain = window.location.host.split( '.' );
	var subdomain = domain[0];

	playbill_connector = new PlaybillInterface(subdomain);

	form_event_setup();
	callback();
}

function form_event_setup() {
	$('#form-search-criteria').on('keydown', function(event) {
		var key = event.keyCode || e.which;
	
		if( key == 9 )
			$('#form-submit').trigger('click');
	});

	$('#form-submit').on('click', function() {
		var query = $('#form-search-criteria').val();
		
		if(playbill_connector && query && !submit_lock) { 
			query_product(
				[query,query],
				0,
				function(xhr) { 
					submit_lock = true;
					$('#product-results').toggleClass("disabled", true);
					toggleValid('#form-search-criteria', null);
					$('#form-search-criteria').toggleClass("loading", true);
				},
				function(data, is_streaming = false, stream_instance = 1) { 		
					if( data && !jQuery.isEmptyObject(data) ) {
						toggleValid('#form-search-criteria', true);
						toggleErrorDescriptor('form-search-criteria');
						
						var barcode = data[0] && data[0]["barcodes"] && data[0]["barcodes"][0] ? data[0].barcodes[0] : barcode;

						populate_product_table(data, is_streaming, stream_instance);
						
						$('#product-results').toggleClass("disabled", false);
					} else {
						toggleValid('#form-search-criteria', false);
						toggleErrorDescriptor('form-search-criteria', "Could not locate Barcode" );
					}
				},
				function(xhr, textStatus, textErrorThrown) {
					toggleValid('#form-search-criteria', false);
					toggleErrorDescriptor('form-search-criteria',textStatus+": "+textErrorThrown);
				},
				function(xhr, textStatus, is_streaming = false, stream_instance = 1) { 
					if(!is_streaming) {
						$('#form-search-criteria').toggleClass("loading", false);
						$('#form-search-criteria').select();
					}
					
					if(xhr.status != 200) {
						toggleValid('#form-search-criteria', false);
						toggleErrorDescriptor('form-search-criteria', xhr.status+": "+xhr.statusText);
					}
					if(!is_streaming) {		
						submit_lock = false;
					}
				},
				true // stream results
			);
		} else {
			toggleValid('#form-search-criteria', null);
			toggleErrorDescriptor('form-search-criteria');
		}
		
		return false;
	});
	
	BarcodeReader = {
		_scanner: null,
		init: function() {
			this.attachListeners();
		},
		decode: function(src) {
			Quagga.decodeSingle({
				decoder: {
					readers: [
						 'ean_reader'
						,'ean_8_reader'
						,'upc_reader'
						,'code_128_reader'
					] // List of active readers
				},
				locate: true, // try to locate the barcode in the image
				src: src
			}, function(result) {
				// Reset barcode reader
				document.getElementById('form-search-barcode-file').value = "";
				
				// Process barcode file for matching code
				try{
					if(result && result !== undefined && result.codeResult) {
						$('#form-search-criteria').val(result.codeResult.code);
						$('#form-submit').click();
					} else {
						$('#form-search-criteria').val('');
						toggleValid('#form-search-criteria', false);
						toggleErrorDescriptor('form-search-criteria', 'Unable to read barcode');
					}
				} catch(err) {
					$('#form-search-criteria').val('');
					toggleValid('#form-search-criteria', false);
					toggleErrorDescriptor('form-search-criteria', 'Unable to read barcode');
				}
			});
		},
		attachListeners: function() {
			var self = this,
				button = document.querySelector('#form-search-barcode-init'),
				fileInput = document.querySelector('#form-search-barcode-file');

			button.addEventListener("click", function onClick(e) {
				e.preventDefault();
				document.querySelector('#form-search-barcode-file').click();
			});

			fileInput.addEventListener("change", function onChange(e) {
				e.preventDefault();
				if (e.target.files && e.target.files.length) {
					self.decode(URL.createObjectURL(e.target.files[0]));
				}
			});
		}
	};
	BarcodeReader.init();
	
}

function query_product(
	criteria_array, 
	index, 
	before_callback,
	success_callback,
	error_callback,
	complete_callback,
	stream_results
) { 
	// Attempt to find match for valid search criteria iteration
	if( criteria_array[index] != null ) {
		// Create search variable array
		var search = [null,null]; 
		// Allocate chosen variable for search by recursive index
		search[index] = criteria_array[index];

		switch(index) {
			case 0:
				console.log("Searching for matching BARCODE ["+search[index]+"]");
				break;
			case 1:
				// Replace spaces with commas to prepare for query
				search[index] = search[index] && search[index] !== undefined ? search[index].replace(/ +/g, ",") : null;
				console.log("Searching for matching NAME ["+search[index]+"]");
				break;				
		}
		
		playbill_connector.lookupProduct(
			search[0],
			search[1],
			null,
			null,
			function(xhr) {
				// Only trigger on first event
				if(index == 0)	before_callback(xhr);
				
			},
			function(data, is_streaming = false, stream_instance = 1) {
				if( data && !jQuery.isEmptyObject(data) ) {
					// Something was found in this iteration so proceed to success callback
					success_callback(data, is_streaming, stream_instance);
					// Set index to the end of the array to trigger complete condition
					index = criteria_array.length;
				} else {
					// Try next iteration
					query_product(
						criteria_array, 
						index+1, 
						before_callback,
						success_callback,
						error_callback,
						complete_callback,
						stream_results
					);
				}
			},
			function(xhr, textStatus, textErrorThrown) {
				// Throw error regardless of iteration
				error_callback(xhr, textStatus, textErrorThrown);
			},
			function(xhr, textStatus, is_streaming = false, stream_instance = 1) {
				// Only trigger on last iteration
				if( criteria_array[index+1] == null ) {
					complete_callback(xhr, textStatus, is_streaming, stream_instance)
				}
			},
			stream_results
		);
	} else {
		// Return null result if no matches were found after all iterations
		success_callback(null);
	}
}

function generate_barcode_image_link(barcode) { 
	var src = "https://barcode.tec-it.com/barcode.ashx?translate-esc=off&data={0}&code={1}&unit=Fit&dpi=360&imagetype=png&rotation=0&color=000000&bgcolor=FFFFFF&qunit=Mm&quiet=1";
	var code = "Code128";
		
	if(barcode) { 
		// UPC-A
		if( $.isNumeric(barcode) && barcode.length == 12 ) {
			code = "UPCA";
		} 
		// EAN-13
		else if ( $.isNumeric(barcode) && barcode.length == 13 ) {
			code = "EAN13";
		}
	
		src = String.format(src, barcode, code);
		
		return src;
	}
	
	return '';
}

function populate_product_table(data, is_streaming = false, stream_instance = 1) { 
	var company_currencies = {
		 "playbill": "AUD"
		,"playbillnz": "NZD"
	};
	
	// Validate @stream_instance
	stream_instance = stream_instance != null && stream_instance !== undefined && typeof stream_instance == 'number' ? stream_instance : 1;
	 
	var tbl_body =  stream_instance == 1 ? document.createElement("tbody") : null;

	// Populate table
	if( tbl_body != null ) {
		$('#product-results').find('tbody').replaceWith(tbl_body);
	} else {
		tbl_body = $('#product-results').find('tbody')[0];
	}

	$.each(data, function(index, product) {
		var barcodes = product.barcodes;
		var mapping_id = product.mapping_id;
		var dear_product_id = product.dear_product_id;
		var dear_instance = product.dear_instance;
		var vend_product_id = product.vend_product_id;
		var vend_instance = product.vend_instance;

		// Create row
		var tbl_row = tbl_body.insertRow();
		
		// Create cells
		var cell_sku =  tbl_row.insertCell();
		var cell_product =  tbl_row.insertCell();
		var cell_barcodes =  tbl_row.insertCell();
		var cell_retail =  tbl_row.insertCell();
		var cell_company =  tbl_row.insertCell();
		var cell_dear =  tbl_row.insertCell();
		var cell_vend =  tbl_row.insertCell();
		
		// Set 'data-label' for responsive table styling
		cell_sku.setAttribute('data-label', 'SKU');
		cell_product.setAttribute('data-label', 'Product');
		cell_barcodes.setAttribute('data-label', 'Barcode(s)');
		cell_retail.setAttribute('data-label', 'Retail');
		cell_company.setAttribute('data-label', 'Company');
		cell_dear.setAttribute('data-label', 'Dear');
		cell_vend.setAttribute('data-label', 'Vend');
		
		// Set cell classes
		cell_sku.className = 'responsive-table-cell-align-left';
		cell_product.className = 'responsive-table-cell-align-left';
		cell_barcodes.className = 'responsive-table-cell-align-left';
		cell_retail.className = 'responsive-table-cell-align-center';
		cell_company.className = 'responsive-table-cell-align-left';
		cell_dear.className = 'responsive-table-cell-align-center';
		cell_vend.className = 'responsive-table-cell-align-center';
		
		// Set cell values
		cell_sku.appendChild(document.createTextNode(product["sku"]));
		cell_company.appendChild(document.createTextNode(dear_instance));
		
		// ** RETAIL cell special case **
		var retail_currency = company_currencies[product["dear_instance"]];
		var retail_price = product["retail_price"];
		retail_price = (retail_price*1).toFixed(2);
		
		(retail_price + "").replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
		
		var retail_value = retail_currency ? retail_currency + " " + retail_price : retail_price;

		cell_retail.appendChild(document.createTextNode(retail_value));
		
		// ** PRODUCT cell special case **
		var cell_product_link = document.createElement('a');
		
		cell_product_link.appendChild(document.createTextNode(product["name"]));
		cell_product_link.title = product["name"];
		cell_product_link.href = product["dear_product_link"];
		
		cell_product.appendChild(cell_product_link);
		
		// ** BARCODES cell special case **
		if(product["barcodes"] && !jQuery.isEmptyObject(product["barcodes"])) {
			$.each(product["barcodes"], function(index, barcode) { 
				var cell_barcode_img = document.createElement('img');
			
				cell_barcode_img.className = "barcode";
				cell_barcode_img.src = generate_barcode_image_link(barcode);
			
				cell_barcodes.appendChild(cell_barcode_img);
			});
		} else {
			var cell_barcode_img = document.createElement('img');
			
			cell_barcode_img.className = "barcode";
			cell_barcode_img.src = generate_barcode_image_link(product["sku"]);
			
			cell_barcodes.appendChild(cell_barcode_img);
		}
		
		// ** DEAR cell special case **
		var cell_dear_div = document.createElement('div');
		var cell_dear_div_id = mapping_id+"-dear";;
		
		cell_dear_div.id = cell_dear_div_id
		cell_dear_div.className = "status";
		cell_dear_div.setAttribute("data-dear-product-id",dear_product_id);
		cell_dear_div.setAttribute("data-valid","");
		
		cell_dear.appendChild(cell_dear_div);
		
		lookup_dear_product (
			 dear_product_id
			,dear_instance
			,function() {
				$("#"+cell_dear_div_id).toggleClass("loading", true);
			}
			,function(data) {
				var exists = data && !jQuery.isEmptyObject(data);
				toggleValid("#"+cell_dear_div_id, exists);
			}
			,function(xhr, textStatus, textErrorThrown) {
				toggleValid("#"+cell_dear_div_id, false);
			}
			,function(xhr) {
				$("#"+cell_dear_div_id).toggleClass("loading", false);
			}
		);
		
		// ** VEND cell special case **
		var cell_vend_div = document.createElement('div');
		var cell_vend_div_id = mapping_id+"-vend";;
		
		cell_vend_div.id = cell_vend_div_id;
		cell_vend_div.className = "status";
		cell_vend_div.setAttribute("data-vend-product-id",vend_product_id);
		cell_vend_div.setAttribute("data-valid","");
		
		cell_vend.appendChild(cell_vend_div);

		lookup_vend_product (
			 vend_product_id
			,vend_instance
			,function() {
				$("#"+cell_vend_div_id).toggleClass("loading", true);
			}
			,function(data) {
				var exists = data && !jQuery.isEmptyObject(data);
				toggleValid("#"+cell_vend_div_id, exists);
			}
			,function(xhr, textStatus, textErrorThrown) {
				toggleValid("#"+cell_vend_div_id, false);
			}
			,function(xhr) {
				$("#"+cell_vend_div_id).toggleClass("loading", false);
			}
		);
	
	});
	
	
}

function lookup_dear_product(
	 id
	,instance
	,before_send_callback
	,success_callback
	,error_callback
	,complete_callback
) {
	var dear_connector = new DearSystemsInterface(instance);

	if(dear_connector._instance) {
		dear_connector.getProducts(
			 id
			,null
			,null
			,before_send_callback
			,success_callback
			,error_callback
			,complete_callback
		);
	} else { 
		error_callback();
	}
}

function lookup_vend_product(
	 id
	,instance
	,before_send_callback
	,success_callback
	,error_callback
	,complete_callback
) {
	var vend_connector = new VendInterface(instance);
	
	if(vend_connector._instance) {
		vend_connector.getProducts(
			 id
			,null
			,null
			,null
			,null
			,before_send_callback
			,success_callback
			,error_callback
			,complete_callback
		);
	} else { 
		error_callback();
	}
}

/**
 * Utility Methods
 *
 */
 
function lobibox_alert(title, msg, callback) {
	if( !msg )
		return;
	
	var list = '<ul>';	

	if( $.isArray(msg) ) {
		for(var i = 0; i < msg.length; ++i) {
			if( !jQuery.isEmptyObject(msg[i]) ) {
			   $.each(msg[i], function(key,val) {
				   list += '<li>'+ key +': '+ val +'</li>';
			   });
			} else {
				list += '<li>'+ msg[i] +'</li>';
			}
		
		};		
	} else if( !jQuery.isEmptyObject(msg) ) {
		$.each(msg, function(key,val) {
			list += '<li>'+ key +': '+ val +'</li>';
		})
	} else {
		console.log('normal');
		list += '<li>'+ msg +'</li>';
	}

	list += '</ul>';
	msg = list;
	
	callback(title, msg);
}
 
function lobibox_alert_error(title, msg) {
	Lobibox.alert('error', {
		title: title,
		msg: msg
	})

	$('.lobibox-error').resize();
}

function lobibox_alert_success(title, msg) {
	Lobibox.alert('success', {
		title: title,
		msg: msg
	});
	
	$('.lobibox-success').resize();
}

function validate_select ( id ) {
	if( $(id).is("select") && $(id).attr('required') !== undefined ) {
		if( $(id).val() && $(id).val() !== undefined && $(id).find('option [value="'+$(id).val()+'"]') ) {
			toggleValid(id, true);
		} else if ( $(id).val() !== '' && $(id).val() !== undefined && $(id).val() !== null ) {
			toggleValid(id, false);
		} else {
			toggleValid(id, null);
		}
	}
}

function simple_validation ( id ) {
	if ( id !== undefined ) {
		if ( $(id).val() > '' ) {
			toggleValid(id, true);
		} else {
			toggleValid(id, false);
		}
	
	}
}

function toggleValid( id, state ) {
	if( state === true ) {
		$(id).attr('data-valid', "true");
	} else if ( state === false ) {
		$(id).attr('data-valid', "false");
	} else {
		$(id).attr('data-valid', "");
	}
}

function toggleErrorDescriptor( id, msg = null) {
	var obj = '#error-'+id;
	var state = $('#'+id).attr('data-valid') !== "" ? $('#'+id).attr('data-valid') === "true" : true;

	if(!state) { 
		Lobibox.notify( 'error', {
			title: 'Error',
			msg: msg,
			positon: 'bottom right',
			icon: false,
			sound: false
		});
	}

	//$(obj).toggleClass('disabled', state);
	//$(obj).html($(obj).find('i')[0].outerHTML + msg);
}