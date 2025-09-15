(function( $ ) {
	'use strict';

	$(document).ready(function(){

		$(document).on('click','.master-menu > a.item',function(e){
			e.preventDefault();
			
			if ( $(this).parent().hasClass('submenu-open') ) {
				$(this).parent().removeClass('submenu-open');
			} else {
				$(this).parent().addClass('submenu-open');
			}
		});

		$(document).on('click','.sidebar-toggle',function(){
			if( !$(this).parents('.sejolisa-memberarea-content.dimmed').length &&
				$('.ui.sidebar').hasClass('visible') ) {
				$('.ui.sidebar').removeClass('visible');
			}
			$('.ui.sidebar').sidebar('toggle');
		});

		var url = window.location.pathname,
        urlRegExp = new RegExp(url.replace(/\/$/,'') + "$");
        $('.sejolisa-memberarea-menu a.item').each(function(){
            if(urlRegExp.test(this.href.replace(/\/$/,''))){
				$(this).addClass('active');
				if ($(this).parents('.master-menu').length) {
					$(this).parents('.master-menu').addClass('submenu-open');
				}
            }
        });

	});

})( jQuery );
