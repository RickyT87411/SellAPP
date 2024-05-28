/** 	GLOBAL VARS 
  *	=========================
  */

/** Navigation menu */
var nav = null;
  
/** API Details */
var api = null;

/**	DOCUMENT.REDAY Functions
  *	HTML Object setup
  *	========================= 
  */

$(document).ready(function() {
    
    // Replaces the navigation menu placeholder with the seperately defined nav.html content
	$.get('nav.html #navigation', function(data)  {
		$('#navigation').replaceWith(data);
	});
    
    /*
	 * Slidemenu 
	 *		Adds/removes a class name to the body to signify that the menu has been
	 *		triggered either positively or negatively respectively.
	 *		Once the class name has been added then the CSS takes over to control the slide-in.
	 */
	(function() {
		var $body = document.body
		, $menu_trigger = $body.getElementsByClassName('menu-trigger')[0];
		
		resize = function() {
			return $("body").css({
				"margin-top": ~~((window.innerHeight - 150) / 2) + "px"
			});
		};

		if ( typeof $menu_trigger !== 'undefined' ) {
			$menu_trigger.addEventListener('click', function() {
				$body.className = ( $body.className == 'menu-active' )? '' : 'menu-active';
			});
		}
		
		$(window).resize(resize);

		resize();

	}).call(this);

	$.get('api-help.html #api-help', function(data)  {
		$('#api-help').replaceWith(data);
	});
    
    (function ($) {
        $("#api-content").on('click', 'li', function (e) {
            var widget = $(this).next('.widget-content');
            $(widget).toggleClass('active').toggle(0);
            
            var icon = $(this).find('.info-icon');
            var main = $(this).find('.info-main');
            var sub = $(this).find('.info-sub');
            
            $(this).toggleClass('selected');
            $(icon).toggleClass('selected');
            $(main).toggleClass('selected');
            $(sub).toggleClass('selected');
        });
    })(jQuery);
	
});

