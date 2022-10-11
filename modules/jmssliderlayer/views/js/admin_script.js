/**
* 2007-2015 PrestaShop
*
* Slider Layer module for prestashop
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

function _slideformload() {
	if ($("#kenburns_on").prop("checked")) {		
		$('.kenburns_data').show();
		$('.no_kenburns_data').hide();
	} else {		
		$('.kenburns_data').hide();	
		$('.no_kenburns_data').show();
	}
	if ($("#bg_type_on").prop("checked")) {		
		$('.bg_img').show();
		$('.bg_color').hide();
	} else {		
		$('.bg_img').hide();	
		$('.bg_color').show();
	}
	var thumb = $('#thumb_img').val();			
	if(thumb)
		$('#thumb_preview').html('<img src="../modules/jmssliderlayer/views/img/thumbs/' + thumb + '" />' );
	else 	
		$('#thumb_preview').html('');
}
function loadxy() {
	jQuery.each($('.data_x'), function() {     
		 strId = $(this).attr('id');		
		 currentId = strId.substring(7, 20);
		 x = $(this).val();
		 var _mheight = $("#slide_grid").height();
		 var _mwidth = $("#slide_grid").width();		
		 if(x=='left') {							
			$('#caption_' + currentId).css('right','auto');
			$('#caption_' + currentId).css('left','0px');
			$('#caption_' + currentId).css('text-align','center');
		} else if(x=='right') {
			$('#caption_' + currentId).css('left','auto');
			$('#caption_' + currentId).css('right','0px');
			$('#caption_' + currentId).css('text-align','right');
		} else if(x=='center') {				
			$('#caption_' + currentId).css('right','auto');	
			var _left = (_mwidth - $('#caption_' + currentId).width())/2;							
			$('#caption_' + currentId).css('left',_left + 'px');
			$('#caption_' + currentId).css('text-align','center');
		} else {
			$('#caption_' + currentId).css('right','auto');	
			$('#caption_' + currentId).css('left',x + 'px');
		}	
	});
}
$(document).ready(function(){	
	_slideformload();
	loadxy();
	$('#data_type').change(function(e){					
		var val = $(this).val();
		_load(val);				
	});	
	$('.layer_img select').change(function(e){					
		var val = $(this).val();
		var img = $('<img />',{ id: 'Myid', src: '../modules/jmssliderlayer/views/img/layers/' + val, alt:'MyAlt'});
		$('#img_preview').html('<img src="../modules/jmssliderlayer/views/img/layers/' + val + '" />' );		
	});	
	$('#thumb_img').change(function(e){					
		var val = $(this).val();
		var img = $('<img />',{ id: 'Myid', src: '../modules/jmssliderlayer/views/img/thumbs/' + val, alt:'MyAlt'});
		if(val)
			$('#thumb_preview').html('<img src="../modules/jmssliderlayer/views/img/thumbs/' + val + '" />' );		
		else 	
			$('#thumb_preview').html('');
	});	
	$("#kenburns_on").click(function(e){
		$('.kenburns_data').show();
		$('.no_kenburns_data').hide();
	});	
	$("#kenburns_off").click(function(e){
		$('.kenburns_data').hide();	
		$('.no_kenburns_data').show();
	});
	$("#bg_type_on").click(function(e){
		$('.bg_img').show();
		$('.bg_color').hide();
	});	
	$("#bg_type_off").click(function(e){
		$('.bg_img').hide();	
		$('.bg_color').show();
	});
	$('#layer_class').change(function(e){					
		var val = $(this).val();
		$('#text_preview').removeClass();	
		$('#text_preview').addClass('tp-caption ' + val);
	});
	$('#layer_text').change(function(e){					
		var val = $(this).val();			
		$('#text_preview').html(val);
	});
	//layers list click
	$('.layer').click(function(e){
		$('.layer').removeClass('active');
		$(this).addClass('active');	
		strId = $(this).attr('id');		
		currentId = strId.substring(7, 20);
		$('.form-wrapper').hide();
		$('#layerform_' + currentId).show();
		$('.tp-caption').removeClass('active');
		$('#caption_' + currentId).addClass('active');	
		$('#selected_id').val(currentId);
	});		
	//layers list click
	$('.tp-caption').click(function(e){		
		strId = $(this).attr('id');		
		currentId = strId.substring(8, 20);
		$('.form-wrapper').hide();
		$('#layerform_' + currentId).show();
		$('.tp-caption').removeClass('active');
		$(this).addClass('active');	
		$('.layer').removeClass('active');
		$('#layers_' + currentId).addClass('active');	
		$('#selected_id').val(currentId);
	});
	//add text	
	
	$('#add-text').click(function(e){
		$("#textdialog").dialog("open"); 
        return false; 
	});
	$('#add-img').click(function(e){
		$("#imgdialog").dialog("open"); 
        return false; 
	});
	$('#add-video').click(function(e){
		$("#videodialog").dialog("open"); 
        return false; 
	});
	$('#submittext').click(function(e){		
		slide_id = $('#slide_id').val();
		layer_text = $('#layer_text_new').val();				
		url = $('#site_url').val() + 'modules/jmssliderlayer/ajax_jmssliderlayer.php?action=addLayer';			
		$.ajax({
			type: "POST",
			url: url,
			data: 'slide_id=' + slide_id + '&layer_text=' + layer_text + '&data_type=text',
			success: function(result){
				location.reload(true);
			},
			dataType: 'html'
		});
		return false;
	});
	$('#submitimg').click(function(e){		
		slide_id = $('#slide_id').val();
		layer_img = $('#layer_img_new').val();				
		url = $('#site_url').val() + 'modules/jmssliderlayer/ajax_jmssliderlayer.php?action=addLayer';			
		$.ajax({
			type: "POST",
			url: url,
			data: 'slide_id=' + slide_id + '&layer_img=' + layer_img + '&data_type=img',
			success: function(result){
				location.reload(true);
			},
			dataType: 'html'
		});
		return false;
	});
	$('#submitvideo').click(function(e){		
		slide_id = $('#slide_id').val();
		layer_video = $('#layer_video_new').val();
		video_width = $('#video_width_new').val();
		video_height = $('#video_height_new').val();
		layer_video_type = $('#layer_video_type').val();		
		url = $('#site_url').val() + 'modules/jmssliderlayer/ajax_jmssliderlayer.php?action=addLayer';							
		$.ajax({
			type: "POST",
			url: url,
			data: 'slide_id=' + slide_id + '&layer_video=' + layer_video + '&video_width=' + video_width + '&video_height='+ video_height + '&data_type=' + layer_video_type,
			success: function(result){
				location.reload(true);
			},
			dataType: 'html'
		});
		return false;
	});
	$('.layer_class').change(function(e){		
		layer_class = $(this).val();
		strId = $(this).attr('id');		
		currentId = strId.substring(12, 20);
		$('#caption_' + currentId).removeClass();
		$('#caption_' + currentId).addClass(layer_class);
		$('#caption_' + currentId).addClass('tp-caption ui-draggable active');
	});
	$('.layer_textarea').change(function(e){	
		layer_text = $(this).val();		
		strId = $(this).attr('id');		
		currentId = strId.substring(11, 20);
		$('#caption_' + currentId).html(layer_text);
	});
	var _mheight = $("#slide_grid").height();
	var _mwidth = $("#slide_grid").width();		
	$('.data_x').change(function(e) {
		var x = $(this).val();						
		strId = $(this).attr('id');		
		currentId = strId.substring(7, 20);	
		if(x=='left') {							
			$('#caption_' + currentId).css('right','auto');
			$('#caption_' + currentId).css('left','0px');
			$('#caption_' + currentId).css('text-align','center');
		} else if(x=='right') {
			$('#caption_' + currentId).css('left','auto');
			$('#caption_' + currentId).css('right','0px');
			$('#caption_' + currentId).css('text-align','right');
		} else if(x=='center') {				
			$('#caption_' + currentId).css('right','auto');	
			var _left = (_mwidth - $('#caption_' + currentId).width())/2;			
			$('#caption_' + currentId).css('left',_left + 'px');
			$('#caption_' + currentId).css('text-align','center');
		} else {
			$('#caption_' + currentId).css('right','auto');	
			$('#caption_' + currentId).css('left',x + 'px');
		}	
	});
	$('.data_y').change(function(e) {
		var y = $(this).val();
		strId = $(this).attr('id');		
		currentId = strId.substring(7, 20);									
		if(y=='top') {							
			$('#caption_' + currentId).css('bottom','auto');
			$('#caption_' + currentId).css('top','0px');			
		} else if(y=='bottom') {
			$('#caption_' + currentId).css('top','auto');
			$('#caption_' + currentId).css('bottom','0px');			
		} else if(y=='center') {				
			$('#caption_' + currentId).css('bottom','auto');	
			var _top = (_mheight - $('#caption_' + currentId).height())/2;			
			$('#caption_' + currentId).css('top',_top + 'px');			
		} else {
			$('#caption_' + currentId).css('bottom','auto');	
			$('#caption_' + currentId).css('top',y + 'px');
		}
	});
	$('.layer_img_box').change(function(e) {
		var img = $(this).val();
		strId = $(this).attr('id');		
		currentId = strId.substring(10, 20);
		$('#caption_' + currentId).html('<img src="' + $('#site_url').val() + 'modules/jmssliderlayer/views/img/layers/' + img +  '" />')	;		
	});
});