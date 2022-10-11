/**
 * @package Jms Theme Layout
 * @version 1.0
 * @Copyright (C) 2009 - 2014 Joommasters.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Website: http://www.joommasters.com
**/


function _initload() {
	var  block_type = $('#block_type');
	var  html_content = $('.html_content');
	var  assign_module = $('.module');
	var  linklist = $('#linklist');

	if(block_type.val()=='link') {
		linklist.show();
		html_content.hide();
		assign_module.hide();
	} else if(block_type.val()=='custom_html') {
		linklist.hide();
		html_content.show();
		assign_module.hide();
	} else {
		linklist.hide();
		html_content.hide();
		assign_module.show();
	}
	
}
$(document).ready(function() {
	_initload();
	$("#block_type").change(function() {
		_initload();
	});	
});