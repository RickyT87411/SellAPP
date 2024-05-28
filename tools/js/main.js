/** 	GLOBAL VARS 
  *	=========================
  */

/** Navigation menu */
var nav = null;

/**	DOCUMENT.REDAY Functions
  *	HTML Object setup
  *	========================= 
  */

$(document).ready(function() {
	// Bootstraps the Navigation menu and the events
	$.get('nav.html #navigation', function(data)  {
		$('#navigation').replaceWith(data);
		
		// Side navigation menu events
		$('#menu-trigger').on('click', function() {
			$('body').toggleClass('menu-active');
		});
		$('#sidenav-close').on('click', function() {
			$('body').toggleClass('menu-active');
		});
	});
    
	$.get('menu.html #api-menu', function(data)  {
		$('#api-menu').replaceWith(data);
	});
	
});