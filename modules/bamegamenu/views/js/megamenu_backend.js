/**
 * JavaScript
 *
 * @version 1.4.4
 * @license GNU Lesser General Public License, http://www.gnu.org/copyleft/lesser.html
 * @author  buy-addons.com, http://odvarko.cz
 * @created 2008-06-15
 * @updated 2014-12-09
 * @link    http://buy-addons.com
 */
var max_rows=0;
var max_row_cols=new Array();
$(function () {
	$('[data-toggle="tooltip"]').tooltip();
	$('.move').css("cursor","move");
	$(".submenulink").after('<div class="form-group"><label class="control-label col-lg-3"><span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="SubMenu.">Submenu:</span></label><div class="col-lg-3 custom_url"></div></div>');
	$("select#typelink_one").after('<br><div class="custom_url_one"></div>');
	var check_typelink=$("select[name=typelink]").val();
	var mega_id_shop=$("#mega_id_shop").val();
	//console.log(mega_id_shop);
	if(check_typelink=='url'){
			var str = 'typelink=url&iditem='+$("input[name=id]").val()+'&mega_id_shop='+mega_id_shop+'&batoken='+batoken;
			$.ajax({		  
				type: 'POST',		 
				url: base_url+'/ajax_editmenu.php',		  
				data: str,		 
				success: function(data) { 
					$(".custom_url").html(data);
				}	
			});
	}else if(check_typelink=='existlinks'){
			var str = 'typelink=existlinks&iditem='+$("input[name=id]").val()+'&mega_id_shop='+mega_id_shop+'&batoken='+batoken;
			$.ajax({		  
				type: 'POST',		 
				url: base_url+'/ajax_editmenu.php',		  
				data: str,		 
				success: function(data) { 
					$(".custom_url").html(data);
				}	
			});
	}else if(check_typelink=='treelinks'){
		var str = 'typelink=existlinks&iditem='+$("input[name=id]").val()+'&mega_id_shop='+mega_id_shop+'&batoken='+batoken;
			$.ajax({		  
				type: 'POST',		 
				url: base_url+'/ajax_editmenu.php',		  
				data: str,		 
				success: function(data) { 
					$(".custom_url").html(data);
				}	
			});
	}else{
			$(".custom_url").html('<input type="text" id="custom_url" name="custom_url" value="" placeholder="Custom Url">');
	}
	var check_typelink_one=$("select[name=typelink_one]").val();
	if(check_typelink_one=='externallink'){
			var str = 'typelink=externallink&iditem='+$("input[name=id]").val()+'&mega_id_shop='+mega_id_shop+'&batoken='+batoken;
			$.ajax({		  
				type: 'POST',		 
				url: base_url+'/ajax_editmenu.php',		  
				data: str,		 
				success: function(data) { 
					$(".custom_url_one").html(data);
				}	
			});
	}else if(check_typelink_one=='systemlink'){
			var str = 'typelink=systemlink&name=_one&iditem='+$("input[name=id]").val()+'&mega_id_shop='+mega_id_shop+'&batoken='+batoken;
			$.ajax({		  
				type: 'POST',		 
				url: base_url+'/ajax_editmenu.php',		  
				data: str,		 
				success: function(data) { 
					$(".custom_url_one").html(data);
				}	
			});
	}else if(check_typelink_one=='htmllink'){
			var str = 'typelink=htmllink&name=custom_url_one&iditem='+$("input[name=id]").val()+'&mega_id_shop='+mega_id_shop+'&batoken='+batoken;
			$.ajax({		  
				type: 'POST',		 
				url: base_url+'/ajax_editmenu.php',		  
				data: str,		 
				success: function(data) {
					$(".custom_url_one").html(data);
				}	
			});
	}else{
			$(".custom_url_one").html('<input type="text" id="custom_url_one" name="custom_url_one" value="" placeholder="Custom Url">');
	}
  $("select#typelink").change(function () {
		if($("select#typelink").val()=='url'){
			$(".custom_url").html('<input type="text" id="custom_url" name="custom_url" value="" placeholder="Custom Url">');
		}else{
			$(".custom_url").html(menuoption);
		}
  });
  $("select#typelink_one").change(function () {
		if($("select#typelink_one").val()=='externallink'){
			$(".custom_url_one").html('<input type="text" id="custom_url_one" name="custom_url_one" value="" placeholder="Custom Url">');
		}else if($("select#typelink_one").val()=='systemlink'){
			$(".custom_url_one").html(menuoptionone);
		}else if($("select#typelink_one").val()=='htmllink'){
			$(".custom_url_one").html('<div class="btn btn-default" onclick="ViewEditHtmlMenu()" ><i class="process-icon-edit "></i><span>View/Edit Html</span></div><div class="view_edit_html" style="display:none"><textarea class="rte autoload_rte" aria-hidden="true" id="custom_url_one_view_edit_html" name="custom_url_one" rows="15" cols="15"></textarea></div><script type="text/javascript">var iso = "en";var ad = "";$(document).ready(function(){tinySetup({editor_selector :"autoload_rte"});});</script>');
		}
  });
  
  $( "input[name=make]" ).on( "click", function() {
	   var check_make=$( "input[name=make]:checked" ).val();
	   if(check_make==1){
			$( ".after_make" ).show(1000);
			$( ".after_dropdown" ).hide(1000);
	   }else{
			$( ".after_make" ).hide(1000);
			$( ".after_dropdown" ).show(1000);
	   }
	});
	$( "input[name=submenu]" ).on( "click", function() {
	   var check_sub=$( "input[name=submenu]:checked" ).val();
	   if(check_sub==1){
			$( ".sub_menu" ).show(1000);
	   }else{
			$( ".sub_menu" ).hide(1000);
	   }
	});
	var count=$( "div.add_row" ).length;
	var i=count;
	$( ".button_addrow" ).on( "click", function() {
		// tăng thêm số hàng tối da
		i=max_rows;
		max_rows+=1;
		max_row_cols[i]=1;
		//console.log(max_rows);
		var addrow;
			addrow='<div class="add_row row_'+i+' ">';
				addrow+='<div class="row_size">';
					addrow+='<label>Row Size :</label>';
						addrow+='<select name="size['+i+'][]" id="row_size_'+i+'" onchange="createcol('+i+',\'row_size_'+i+'\','+i+')">';
							addrow+='<option value="1">1</option>';
							addrow+='<option value="2">2</option>';
							addrow+='<option value="3">3</option>';
							addrow+='<option value="4">4</option>';
							addrow+='<option value="5">5</option>';
							addrow+='<option value="6">6</option>';
							addrow+='<option value="7">7</option>';
							addrow+='<option value="8">8</option>';
							addrow+='<option value="9">9</option>';
						addrow+='</select>';
					addrow+='<label class="button_removerow" onclick="removerow('+i+')">Remove Row</label>';
				addrow+='</div>';
				addrow+='<div class="list_col list_col_'+i+'">';
					addrow+='<div class="col col_0">';
						addrow+='<div style="display:none" class="cancel_col" onclick="cancel_col('+i+',0)"><i class="process-icon-cancel "></i></div>';
						addrow+='<div class="form-group">';
							addrow+='<label class="control-label col-lg-3">';
								addrow+='Label:';
							addrow+='</label>';
							addrow+='<div class="col-lg-9">';
								addrow+='<input type="text" id="labelmenu_col_'+i+'" class="copy2friendlyUrl updateCurrentText" name="sub['+i+'][0][]" value="" >';	
							addrow+='</div>';
						addrow+='</div>';
						addrow+='<div class="form-group">';	
							addrow+='<label class="control-label col-lg-3">';
								addrow+='Custom Class:';
							addrow+='</label>';
							addrow+='<div class="col-lg-9">';
								addrow+='<input type="text" id="customclass_col_'+i+'" class="copy2friendlyUrl updateCurrentText" name="sub['+i+'][0][]" value="" >';	
							addrow+='</div>';
						addrow+='</div>';
						addrow+='<div class="form-group">';	
							addrow+='<label class="control-label col-lg-3">';
								addrow+='Width:';
							addrow+='</label>';
							addrow+='<div class="col-lg-9">';
								addrow+='<input type="text" id="width_col_'+i+'" class="copy2friendlyUrl updateCurrentText" name="sub['+i+'][0][]" value="" >';	
							addrow+='</div>';
						addrow+='</div>';
						addrow+='<div class="form-group">';	
							addrow+='<label class="control-label col-lg-3">';
								addrow+='Type:';
							addrow+='</label>';
							addrow+='<div class="col-lg-9">';
								addrow+='<select name="sub['+i+'][0][]" id="typelink_col_'+i+'" onchange="SelectTypeLink('+i+',this,0)">';	
								addrow+='<option value="">Hide This</option>';
								addrow+='<option value="link">Link</option>';
								addrow+='<option value="customhtml">Custom Html</option>';
								addrow+='<option value="loadhook">Hook</option>';
								addrow+='<option value="product">Product</option>';
								addrow+='<option value="productlist">Product List</option>';
								addrow+='</select>';
								addrow+='<br><div class="custom_url_col custom_url_col_0"></div>';								
							addrow+='</div>';
						addrow+='</div>';
					addrow+='</div>';
				addrow+='</div>';
			addrow+='</div>';
	   $( ".end_row" ).after(addrow);
	   $( ".add_row" ).removeClass('end_row');
	   $( ".add_row" ).last().addClass( "end_row" );
	   i++;
		
	});
	
});
///////////////
function createcol(row,name,id){
	//var numcol=$("select[name="+name+"]").val();
	var numcol=$(".row_"+row+" select#"+name).val();
	var num=$(".row_"+row+" .list_col .col" ).length;
	var b;
	if(num>0){
		b=num;
		if(numcol>1){
			$('.row_'+row+' .cancel_col').css('display','block');
		}else{
			$('.row_'+row+' .cancel_col').css('display','none');
		}
	}else{
		b=0;
	}
	var col="";
	var i;
	for(var t=b;t<numcol;t++){
					i=max_row_cols[row];
					max_row_cols[row]++;		
					col+='<div class="col col_'+i+'">';
						col+='<div class="cancel_col" onclick="cancel_col('+row+','+i+')"><i class="process-icon-cancel "></i></div>';
						col+='<div class="form-group">';
							col+='<label class="control-label col-lg-3">';
								col+='Label:';
							col+='</label>';
							col+='<div class="col-lg-9">';
								col+='<input type="text" id="labelmenu_col_'+i+'" class="copy2friendlyUrl updateCurrentText" name="sub['+row+']['+i+'][]" value="" >';	
							col+='</div>';
						col+='</div>';
						col+='<div class="form-group">';	
							col+='<label class="control-label col-lg-3">';
								col+='Custom Class:';
							col+='</label>';
							col+='<div class="col-lg-9">';
								col+='<input type="text" id="customclass_col_'+i+'" class="copy2friendlyUrl updateCurrentText" name="sub['+row+']['+i+'][]" value="" >';	
							col+='</div>';
						col+='</div>';
						col+='<div class="form-group">';	
							col+='<label class="control-label col-lg-3">';
								col+='Width:';
							col+='</label>';
							col+='<div class="col-lg-9">';
								col+='<input type="text" id="width_col_'+i+'" class="copy2friendlyUrl updateCurrentText" name="sub['+row+']['+i+'][]" value="" >';	
							col+='</div>';
						col+='</div>';
						col+='<div class="form-group">';	
							col+='<label class="control-label col-lg-3">';
								col+='Type:';
							col+='</label>';
							col+='<div class="col-lg-9">';
								col+='<select name="sub['+row+']['+i+'][]" id="typelink_col_'+i+'" onchange="SelectTypeLink('+row+',this,'+i+')">';	
								col+='<option value="">Hide This</option>';
								col+='<option value="link">System Link</option>';
								col+='<option value="customhtml">Custom Html</option>';
								col+='<option value="loadhook">Hook</option>';
								col+='<option value="product">Product</option>';
								col+='<option value="productlist">Product List</option>';
								col+='</select>';
								col+='<br><div class="custom_url_col custom_url_col_'+i+'"></div>';								
							col+='</div>';
						col+='</div>';
					col+='</div>';
	}
	if(num>0){
		if(num>numcol){
			$(".list_col_"+id+" .col").last().remove();
			createcol(row,name,id);
		}else{
			$(".list_col_"+id+" .col").last().after(col);
		}
	}else{
		$(".list_col_"+id).html(col);
	}
	$('.row_'+row+' .col').css({
      "float": "left",
      "width": "295px"
    });
}
function SelectTypeLink(row,type,id){
	var mega_id_shop=$("#mega_id_shop").val();
	switch(type.value){
		case 'link':
			var str = 'name='+'_col_'+id+'&type=multiple&id='+id+'&row='+row+'&mega_id_shop='+mega_id_shop+'&batoken='+batoken;
			$.ajax({		  
				type: 'POST',		 
				url: base_url+'/ajax_submenu.php',		  
				data: str,		 
				success: function(data) {	
					$(".row_"+row+" .custom_url_col_"+id).html(data);    	
				}	
			});
		break;
		case 'customhtml':$(".row_"+row+" .custom_url_col_"+id).html('<div class="btn btn-default" onclick="ViewEditHtml('+row+','+id+')" ><i class="process-icon-edit "></i><span>View/Edit Html</span></div><div class="view_edit_html" style="display:none"><textarea class="autoload_rte" aria-hidden="true" id="sub_'+row+'_'+id+'" name="sub['+row+']['+id+'][]" rows="15" cols="15"><p></p></textarea></div><script type="text/javascript">var iso = "en";var ad = "";$(document).ready(function(){tinySetup({editor_selector :"autoload_rte"});});</script>');
		break;
		case 'loadhook':
			var str = 'name='+'_col_'+id+'&hook=loadhook&id='+id+'&row='+row+'&batoken='+batoken;
			$.ajax({		  
				type: 'POST',		 
				url: base_url+'/ajax_submenu.php',		  
				data: str,		 
				success: function(data) {
					$(".row_"+row+" .custom_url_col_"+id).html(data);    	
				}	
			});
		break;
		case 'product':$(".row_"+row+" .custom_url_col_"+id).html('<input type="text" id="product_'+id+'" name="sub['+row+']['+id+'][]" value="" placeholder="Product Id">');
		break;
		case 'productlist':
			var str = 'name='+'_col_'+id+'&product=productlist&id='+id+'&row='+row+'&mega_id_shop='+mega_id_shop+'&batoken='+batoken;
			$.ajax({		  
				type: 'POST',		 
				url: base_url+'/ajax_submenu.php',		  
				data: str,		 
				success: function(data) {	
					$(".row_"+row+" .custom_url_col_"+id).html(data);    	
				}	
			});
		break;
		case '':$(".row_"+row+" .custom_url_col_"+id).html('');
		break;
	}
}
function SelectEditTypeLink(row,type,id,iditem){
	var mega_id_shop=$("#mega_id_shop").val();
	var str = 'name='+'_col_'+id+'&type=multiple&id='+id+'&row='+row+'&iditem='+iditem+'&typelink='+type+'&mega_id_shop='+mega_id_shop+'&batoken='+batoken;
		$.ajax({		  
			type: 'POST',		 
			url: base_url+'/ajax_editmenu.php',		  
			data: str,		 
			success: function(data) {	
				$(".row_"+row+" .custom_url_col_"+id).html(data);    	
			}	
		});
			
}
function ViewEditHtml(row,id){
	$(".row_"+row+" .custom_url_col_"+id+" .view_edit_html").css("display","block");
	$('body').append( '<div onclick="closed_custom('+row+','+id+')" id="background_custom"></div>' );
}
function ViewEditHtmlMenu(){
	$(".custom_url_one .view_edit_html").css("display","block");
	$('body').append( '<div onclick="closed_custom_menu()" id="background_custom"></div>' );
}
function closed_custom(row,id){
	$('#background_custom').remove();
	$(".row_"+row+" .custom_url_col_"+id+" .view_edit_html").css("display","none");
}
function closed_custom_menu(){
	$('#background_custom').remove();
	$(".custom_url_one .view_edit_html").css("display","none");
}
function removerow(id){
	  $('.row_'+id).remove();
	  $( ".add_row" ).removeClass('end_row');
	  $( ".add_row" ).last().addClass( "end_row" );
}
function submitformmenu(id,action,status){
	switch(action){
		case 'addlabel':
			document.getElementById('action').value='addlabel';
			document.getElementById('id').value=id;
			break;
		case 'editlabel':
			document.getElementById('action').value='editlabel';
			document.getElementById('id').value=id;
			break;
		case 'savestay':
			document.getElementById('action').value='savestay';
			break;
		case 'deletelabel':
			document.getElementById('action').value='deletelabel';
			document.getElementById('id').value=id;
			break;
		case 'updatelabel':
			document.getElementById('action').value='updatelabel';
			document.getElementById('id').value=id;
			document.getElementById('status').value=status;
			break;
		case 'savelabel':
			document.getElementById('action').value='savelabel';
			document.getElementById('id').value=id;
			break;
		case 'customcss':
			document.getElementById('action').value='customcss';
			break;
		case 'cancellabel':
			document.getElementById('action').value='cancellabel';
			break;
		case 'languagetype':
			document.getElementById('action').value='languagetype';
			break;
		case 'configmega':
			document.getElementById('action').value='configmega';
			break;
	}
	if(action=='savestay' || action=='savelabel'){ 
		if($('input[name=labelmenu]').val()==''){
			$('input[name=labelmenu]').css('border','1px solid #FC0606');
			$.scrollTo($('input[name="labelmenu"]').offset().top-100, 500);
			return false;
		}
	}
	$("form#form-megamenu").submit();
}
function cancel_col(row,id){
	 $('.row_'+row+' .col_'+id).remove();
	var num=$('.row_'+row+' .list_col .col').length;
	 if(num==1){
		$('.row_'+row+' .cancel_col').css('display','none');
	 }
	 $('.row_'+row+' select#row_size_'+row).val(num);
}
$( document ).ready(function() {
	var check_make=$( "input[name=make]:checked" ).val();
	if(check_make==1){
			$( ".after_make" ).show();
			$( ".after_dropdown" ).hide();
	}else{
			$( ".after_make" ).hide();
			$( ".after_dropdown" ).show();
	}
	var check_sub=$( "input[name=submenu]:checked" ).val();
	if(check_sub==1){
			$( ".sub_menu" ).show();
	}else{
			$( ".sub_menu" ).hide();
	}
	// đếm số hàng đang có
	max_rows=$("div.add_row").size();
	//console.log(max_rows);
	// đếm số cột tối đa trong mỗi hàng
	$("div.add_row").each(function(index){
		max_row_cols[index]=$(this).find(".list_col .col").size();
	});
	//console.log(max_row_cols);
});
