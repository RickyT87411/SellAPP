/** 	GLOBAL VARS 
  *	=========================
  */

/** Navigation menu */
var nav = null;
  
/** API Details */
var api = null;

var dear_monitor = null;
var self_monitor = null;

var dear_alive_uri = "https://inventory.dearsystems.com";
var dear_alive_delay_ms = 60000;

/* Response message when running any AJAX requests on the EditableGrid */

// Default HTML with INFO icon
var last_response = null;

var info_basic_response =
	"<div class='isa_info'>"+
		"<i class='fa fa-info-circle'></i>"+
		"{0}"+
	"</div>"
;
// Default HTML with INFO icon and LOADER
var info_response =
	"<div class='isa_info'>"+
		"<i class='fa fa-info-circle'></i>"+
		"{0}"+
		"<div class='inline-loader'><img src='../../images/ajax-loader.gif'/></div>"+
	"</div>"
;
// Default HTML with WARNING icon
var warning_response = 
	"<div class='isa_warning'>"+
	   "<i class='fa fa-warning'></i>"+
	   "{0}"+
	"</div>"
;
// Default HTML with ERROR icon
var error_response = 
	"<div class='isa_error'>"+
	   "<i class='fa fa-times-circle'></i>"+
	   "[{0}]: {1}"+
	"</div>"
;
// Default HTML with SUCCESS icon
var success_response = 
	"<div class='isa_success'>"+
	   "<i class='fa fa-check'></i>"+
	   "{0}"+
	"</div>"
;

/**	USER FUNCTIONS
  *	Functions that can be used dynamically through the webpage
  *	=========================
  */
  
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

// Fetches URL params fed into the current page
$.urlParam = function(name){
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

/**	DOCUMENT.REDAY Functions
  *	HTML Object setup
  *	========================= 
  */

$(document).ready(function() {

	dear_monitor = new DearHeartbeatMonitor(
		"dear-server-status",
		dear_alive_uri,
		dear_alive_delay_ms
	);
	
	// Adds the ability to click on the Heartbeat icon to manually check server status
	$('#dear-server-status').on('click', '#dear-server-status-heartbeat', function() {
		dear_monitor.check();
	});
	
    // Bootstraps the Navigation menu and the events
	$.get('/tools/nav.html #navigation', function(data)  {
		$('#navigation').replaceWith(data);
		
		// Side navigation menu events
		$('#menu-trigger').on('click', function() {
			$('body').toggleClass('menu-active');
		});
		$('#sidenav-close').on('click', function() {
			$('body').toggleClass('menu-active');
		});
	});
	
	// Bootstraps the Form HTML and the events
	$.get('form.html #form', function(data)  {
		$('#form').replaceWith(data);
		
		rangeSlider();
		
		// Configure form events and start
		init(function() {
			// Configure database select event		
			$('#database .sub-settings li').on('click', function() {
				var opt = this;

				$.each($('#database .sub-settings li'), function() {
					if( this !== opt ) {
						$(this).toggleClass('current-item', false);
					}
				});

				$(opt).toggleClass('current-item', true);
				$('#active-database').text($('.sub-settings .current-item a p').text());
				$('#inventory-instance').val($('.sub-settings .current-item a p').text());
				$('#inventory-instance').trigger('change');
				
				// Save choice in Cookies for later use
				Cookies.set('pb-pst-active-database', $('.sub-settings .current-item a p').text());
			});
			
			$('#form-consignment').on('click', function() {
				if($(this).is(":checked")) {
					$('#form-finance-purchase-tax').val("GST Free Purchases");
					validate_select('#form-finance-purchase-tax');
					$('#form-finance-sale-tax').val("GST on Consignment");
					validate_select('#form-finance-sale-tax');
				} else {
					$('#form-finance-purchase-tax').val("");
					validate_select('#form-finance-purchase-tax');
					$('#form-finance-sale-tax').val("");
					validate_select('#form-finance-sale-tax');
				}
			});
			
			// Set starting database from Cookies if available
			var activedb = Cookies.get('pb-pst-active-database');
			
			if( activedb !== undefined )
				$('.sub-settings li a p').filter(function(index){ return $(this).text() === activedb; }).parent().parent().trigger('click');
			
			// Initialize datepicker
			$('#form-misc-saleability-date').datepicker({
				minDate: (new Date()),
				startDate: (new Date()),
				clearButton: true,
				autoClose: true
			});
			
		});		
	});	
});