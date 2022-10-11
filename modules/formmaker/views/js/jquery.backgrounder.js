/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2015 silbersaiten
 * @version   0.0.1
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */
(function($) {
  $.fn.backgrounder = function(options) {
    var defaults = { element : 'body' };
    var options = $.extend(defaults, options);
	// Get the image we're using
    var img = $(this).children('img');
	
	img.each(function(){
	    var w = options.element == 'body' ? $(window).width() : $(options.element).width(),
		h = options.element == 'body' ? $(window).height() : $(options.element).height(),
		ow = $(this).width();
		oh = $(this).height();
		
	    if (ow / oh > w / h) { // image aspect ratio is wider than browser window
		var scale = h / oh;
		$(this).attr({'width':ow * scale, 'height':oh * scale});
	    } else {
		var scale = w / ow;
		$(this).attr({'width':ow * scale,'height':oh * scale});
	    }
	    
	    $(this).css({'left':-(($(this).width()-w)/2),'top':-(($(this).height()-h)/2)});
	});

    return this.each(function() { });
  };
}) (jQuery);