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

	$.get('menu.html #api-menu', function(data)  {
		$('#api-menu').replaceWith(data);
	});
	
});