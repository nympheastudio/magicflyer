{*
 * @package Jms Drop Megamenu
 * @version 1.0
 * @Copyright (C) 2009 - 2015 Joommasters.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Website: http://www.joommasters.com
*}

{$menu_html|escape:'html':'UTF-8'}
<script type="text/javascript">
	jQuery(document).ready(function($) {
    	jQuery('#jms-megamenu').jmsMegaMenu({    			
    		event: 'hover',
    		openspeed: 400,
    		closespeed: 400
    	});		
	});
	function window_resize_handler(maxwidth) {
		var length = $(window).width();
		if (length > maxwidth) {
			$('#jmsmenuwrap').removeClass('mobile-menu');
			$('#jms-megamenu').show();
			$('#jmsresmenu_dropdown').hide();			
		} else {		
			$('#jmsresmenu_dropdown').show();
			$('#jms-megamenu').hide();		
			$('#jmsmenuwrap').addClass('mobile-menu');	
		}
	}

	window_resize_handler(768);
	$(window).resize(function() {
		 window_resize_handler(768);
	});	
</script>