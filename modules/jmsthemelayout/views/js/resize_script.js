/**
 * @package Jms Theme Layout
 * @version 1.0
 * @Copyright (C) 2009 - 2014 Joommasters.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Website: http://www.joommasters.com
**/

function pos_resizable() {
	$( "#rowlist .position" ).resizable({
		grid:90,
		handles: 'e',
		stop: function( event, ui ) {
			var parent = ui.element.parent();
			var dev_col = $('#rowlist').attr('class');
			var col_val = Math.round(ui.element.width()/90).toString();
			var pos_class = ui.element.attr('class');
			var posclass_arr = pos_class.split(" ");
			var i = 0;
			for(i=0; i < posclass_arr.length; i++) {
				if (posclass_arr[i].indexOf(dev_col) >= 0) {
					ui.element.removeClass(posclass_arr[i]);
					ui.element.addClass(dev_col + '-' + col_val);
				}
			}
			ui.element.attr('data-' + dev_col, col_val);
			ui.element.css('width','none');
		}
	});	
}
function reset_class() {
	$( "#rowlist .position" ).each(function( index ) {
		var pos_class = $(this).attr('class');
		var posclass_arr = pos_class.split(" ");
		var i = 0;
		for(i = 0; i < posclass_arr.length; i++) {
			if (posclass_arr[i].indexOf('bk-') == 0) {
				$(this).removeClass(posclass_arr[i]);
				$(this).addClass(posclass_arr[i].substring(3,posclass_arr[i].length));
			}
		}
	});
}
function change_class(dev_class) {
	reset_class();
	$( "#rowlist .position" ).each(function( index ) {
		var pos_class = $(this).attr('class');
		var posclass_arr = pos_class.split(" ");
		var i = 0;
		for(i = 0; i < posclass_arr.length; i++) {
			if (posclass_arr[i].indexOf(dev_class) == -1 && posclass_arr[i] != 'ui-resizable' && posclass_arr[i] != 'position') {
				$(this).removeClass(posclass_arr[i]);
				$(this).addClass('bk-' + posclass_arr[i]);
			}
		}
	});
}
jQuery(function ($) {
    "use strict";
	pos_resizable();
	change_class('col-lg');
	$(".save_pos").click(function() {
		var dev_col = $('#rowlist').attr('class');
		var positions = $('.row-positions').find('div[class*=' + dev_col + ']');
		var string = '';
		var	i = 0;
		for(i=0; i < positions.length; i++) {
			string += positions.eq(i).attr('data-id-pos') + '-' + positions.eq(i).attr('data-col-lg') + '-' + positions.eq(i).attr('data-col-md') + '-' + positions.eq(i).attr('data-col-sm') + '-' + positions.eq(i).attr('data-col-xs');
			if((i + 1) < positions.length) string +=  '|';
		}
		var url = $('#current_url').val() + '?action=savepos';
		//alert(url + '&pos=' + string);
		$.ajax({
			type: 'POST',
			url: url,
			data: 'pos=' + string,
		  	success: function(){
				showSuccessMessage('The position width saved!');
		  	}
		});
		return false;
	});
	$(".devices-layout .dropdown-menu a").click(function() {
		$('#rowlist').removeClass();
		var dev_class = $(this).attr('data-device');
		$('#rowlist').addClass('col-' + dev_class);
		$('.devices-layout a').removeClass('btn-success');
		$('.devices-layout a').addClass('btn-default');
		$(this).removeClass('btn-default');
		$(this).addClass('btn-success');
		change_class('col-' + dev_class);
	});
});
