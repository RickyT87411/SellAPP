/**
 * ================================
 *	Server Monitor JS
 * ================================
 *
 *	Checks server heartbeat intermittently
 */
 
/* JSON Options List
 *	{
 *		uri 				: <server address>	// Must accept OPTIONS HTTP request
 *		delay				: <delay between checks>
 *		checkingCallback 	: function() { //doWork(); }
 *		onlineCallback		: function(data) { //doWork(); }
 *		offlineCallback		: function(xhr, textStatus, thrownError) { //doWork(); }
 *		completeCallback	: function(xhr, textStatus ) { //doWork(); }
 *	}
 *
 *	E.G.
 *		var monitor = new HeartbeatMonitor( 
 *			{ 
 *				"uri" 							: "myserveraddress.com/ping", 
 *				"delay" 						: "10000" ,
 *				"checkingCallback"		: function() { $("status").html("checking"); },
 *				"onlineCallback" 			: function() { $("status").html("online"); },
 *				"offlineCallback" 			: function(xhr, textStatus, thrownError) { $("status").html("offline"); },
 *				"completeCallback"	: function(xhr, textStatus) { console.log(this.status); }
 *			} 
 *		);
 */

function HeartbeatMonitor(options) { 
	var uri = null;
	var delay = 60000;
	var status = 0;
	var method = "OPTIONS";
	var checkingCallback = null;
	var onlineCallback = null;
	var offlineCallback = null;
	var completeCallback = null;
	
	/** Hearbeat settings */
	if(!jQuery.isEmptyObject(options)) {
		this.uri 							= options.uri || null;
		this.delay 						= options.delay || 60000;
		this.method 			= options.method || "OPTIONS";
		this.checkingCallback		= options.checkingCallback || function(){};
		this.onlineCallback			= options.onlineCallback || function(){};
		this.offlineCallback			= options.offlineCallback || function(){};
		this.completeCallback	= options.completeCallback || function(){};
	}
		
	// Default status
	this.status = 0;
	var interval = null;
}

/** PRIVATE Methods **/
HeartbeatMonitor.prototype.heartbeat = function(
	uri,
	method,
	before_callback,
	success_callback,
	error_callback,
	complete_callback
) {
	$.ajax({
		type: 					method,
		url: 					uri,
		cache:					false,
		global: 				false,
		async:					true,
		timeout: 				30000,
		beforeSend:		function(xhr, obj) {before_callback(xhr, obj)},
		success: 			function(data) {success_callback(data)},
		error: 					function(xhr, textStatus, thrownError) {error_callback(xhr, textStatus, thrownError)},
		complete: 			function(xhr, textStatus) {complete_callback(xhr, textStatus)}
	});
}


HeartbeatMonitor.prototype.check = function () {
	this.heartbeat (
		this.uri,
		this.method,
		this.checkingCallback || function(){},
		this.onlineCallback || function(){},
		this.offlineCallback || function(){},
		this.completeCallback || function(){}
	);
}


/** PUBLIC Methods **/
HeartbeatMonitor.prototype.start = function () {
	if (this.uri != null) {
		// Initial check
		this.check();
		// For binding purposes
		var that = this;
		// Sets interval heartbeat check
		this.interval = setInterval( function() {
				return that.check();
			}, 
			this.delay
		);
	}
}

HeartbeatMonitor.prototype.stop = function () {
	if(this.interval != null) {
		clearInterval(this.interval);
	}
}

