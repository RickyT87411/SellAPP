VendHeartbeatMonitor.prototype._proxy = '/SimplePHPProxy.php';

function UrlExists(url)
{
    var http = new XMLHttpRequest();
    http.open('HEAD', url, false);
    http.send();
    return http.status==200;
}

function VendHeartbeatMonitor(id, uri, delay) { 
	
	/** Time betwen heartbeats */
	var heartbeat_ms = delay;
	var heartbeat_obj = id;
	var heartbeat_id = id+"-heartbeat";
	var heartbeat_uri = uri; 
	var heartbeat_method = "GET";
	var heartbeat_monitor = null;
	
	var proxyCallback = null;
	
	if ( UrlExists(VendHeartbeatMonitor.prototype._proxy) ) {
		var params = {
			"url" : uri,
			"full_status" : 1,
			"full_headers" : 1
		};
	
		heartbeat_uri = VendHeartbeatMonitor.prototype._proxy + "?" + jQuery.param( params );
		
		proxyCallback = function (data) {
			return data && data.status && data.status.http_code && data.status.http_code == 200;
		};
	}
	
	/*
	 *	Dynamic HTML replacements for placeholders
	*/
	 // Server status HTML on initial check
	var server_status_checking = 
		'<div class="isa_warning" style="font-size: 1.2em">'+
			'<i class="fa fa-heartbeat" id="'+heartbeat_id+'" style="font-size: 1.5em"></i>'+
			'<p>Checking DEAR\'s pulse...</p>'+
		'</div>'
	;
	// Server status HTML when server has been seen online
	var server_status_online = 
		'<div class="isa_success" style="font-size: 1.2em">'+
			'<i class="fa fa-heartbeat" id="'+heartbeat_id+'" style="font-size: 1.5em"></i>'+
			'<p>DEAR Online</p>'+
		'</div>'
	;
	// Server status HTML when server has been seen offline
	var server_status_offline = 
		'<div class="isa_error" style="font-size: 1.2em">'+
			'<i class="fa fa-heartbeat" id="'+heartbeat_id+'" style="font-size: 1.5em"></i>'+
			'<p>DEAR Offline</p>'+
		'</div>'
	;

	// Default Server-Status for initial check
	$('#'+id).html(server_status_checking);
	$('#'+id).show();
	$('#'+heartbeat_id).effect (
		"pulsate",
		{ times: 5000},
		1500
	);
	
	/* 
	 * Creates a new HeartbeatMonitor which will periodically check
	 * if the input host URI is being responsive. The HeartbeatMonitor can be
	 * setup with what to do when the server is "online", "offline" or "checking" and
	 * a general response when "complete". This acts identically to a AJAX request
	 * functions for the respective types.
	 * N.B. The server must support OPTIONS request for lightweight pings or specify
	 *		alternate request method, e.g. GET
	 */
	this.heartbeat_monitor = new HeartbeatMonitor(
		{
			uri : 	heartbeat_uri,
			delay: 	heartbeat_ms,
			method: heartbeat_method,
			checkingCallback: function(xhr, obj) { 
				$('#'+heartbeat_id).effect ("pulsate", { times: 3}, 1500);
			},
			onlineCallback : function(data) {
				var result = proxyCallback ? proxyCallback(data) : true;
				if(result) {
					if(HeartbeatMonitor.status != 1) {
						$('#'+heartbeat_obj).fadeOut();
						$('#'+heartbeat_obj).html(server_status_online);
						$('#'+heartbeat_obj).fadeIn();
						this.status = 1;
					}
				} else {
					if(this.status != -1) {
						$('#'+heartbeat_obj).fadeOut();
						$('#'+heartbeat_obj).html(server_status_offline);
						$('#'+heartbeat_obj).fadeIn();
						this.status = -1;
					}
					$('#content').animate({
						scrollTop: $('#'+heartbeat_obj).offset().top
					}, 500);
				}
			},
			offlineCallback : function(xhr, textStatus, thrownError) {
				if(this.status != -1) {
					$('#'+heartbeat_obj).fadeOut();
					if(textStatus === "timeout") {
						$('#'+heartbeat_obj).html(server_status_checking);
					} else {
						$('#'+heartbeat_obj).html(server_status_offline);
					}
					$('#'+heartbeat_obj).fadeIn();
					this.status = -1;
				}
				$('#content').animate({
					scrollTop: $('#'+heartbeat_obj).offset().top
				}, 500);
			},
			completeCallback : function(xhr, textStatus) {
				$('#'+heartbeat_id).stop();
			}
		}
	);
	
	// Starts the periodic monitoring
	this.heartbeat_monitor.start();
}

VendHeartbeatMonitor.prototype.check = function () {
	this.heartbeat_monitor.check();
}