/**
 * @package Jms Drop Megamenu
 * @version 1.0
 * @Copyright (C) 2009 - 2015 Joommasters.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Website: http://www.joommasters.com
**/

function checkAll(cbox) {
	var form = document.adminForm;
	var boxs = $(".jms-checkbox");
	if(cbox.checked) 
		cvalue = true;
	else 
		cvalue = false;	
		form.boxchecked.value = '';
	for(i=0;i<boxs.length;i++) {
		boxs[i].checked = cvalue;
	}	
}
function isChecked(c) {
	var form = document.adminForm;
	if(c.checked) 	
		form.boxchecked.value = c.value;
	else if(form.boxchecked.value==c.value)	
		form.boxchecked.value = null;
}
function submitform(task,link) {
	var form = document.adminForm;	
	if(task=='add') {
		document.location = link;
	} else if(task=='edit') {
		var form = document.adminForm;
		if(form.boxchecked.value) 
			document.location = link + '&mitem_id=' + form.boxchecked.value;	
		else 
			alert('Please select one item to edit');	
	} else if(task=='deleteMenu'){
		var boxs = $(".jms-checkbox");
		var can_del = false;
		for(i=0;i<boxs.length;i++) {
			if(boxs[i].checked) 
				can_del = true;
		}		
		if(!can_del) {
			alert('Please select one item to delete');			
		} else {
			form.action = link + '&'+ task;	
			form.submit();
		}
	} else if(task=='cStatus'){
		var boxs = $(".jms-checkbox");
		var can_change = false;
		for(i=0;i<boxs.length;i++) {
			if(boxs[i].checked) 
				can_change = true;
		}
		if(!can_change) {
			alert('Please select one item to change');			
		} else {
			form.action = link + '&'+ task;	
			form.submit();
		}	
	}
	return false;
}

function cStatus(id,link,status) {
	var form = document.adminForm;
	$('#' + id).prop('checked', true);		
	form.action = link + '&cStatus&status=' + status;
	form.submit();		
}

function cRemove(id,link) {
	var form = document.adminForm;
	$('#' + id).prop('checked', true);		
	form.action = link + '&deleteMenu';
	form.submit();		
}

function _initload() {
	var  jms_box = $('.jms-box');
	jms_box.hide();
	$('.' + $("#type").val()).show();	
	if($("#parent_id").val() > 0) {
		$('.level1').hide();
		$('.level2').show();
	} else {
		$('.level1').show();	
		$('.level2').hide();
	}	
}
function collapse_expand() {
	$('.collapse-expand').click(function (e) {
    	e.preventDefault();
        var _menu = $(this).parent().parent().parent().parent();
        var _submenu = _menu.next('.jms-submenu');               
        _submenu.toggle();
		if($(this).children('i').hasClass('icon-caret-down')) {
			$(this).children('i').removeClass('icon-caret-down');
			$(this).children('i').addClass('icon-caret-up');
		} else {
			$(this).children('i').removeClass('icon-caret-up');
			$(this).children('i').addClass('icon-caret-down');
		}	
    })
	$('.collapse-expand-all').click(function (e) {
		$('.jms-submenu').toggle();		
		if($(this).children('i').hasClass('icon-caret-down')) {
			$('.icon-caret-down').addClass('icon-caret-up');
			$('.icon-caret-down').removeClass('icon-caret-down');
		} else {	
			$('.icon-caret-up').addClass('icon-caret-down');
			$('.icon-caret-up').removeClass('icon-caret-up');
		}	
	})	
}
$(document).ready(function() {
	_initload();	
	$("#type").change(function() {			
		_initload();
	});
	$("#parent_id").change(function() {			
		_initload();
	});
	collapse_expand();
});