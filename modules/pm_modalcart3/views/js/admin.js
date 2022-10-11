function showRelatedConfiguration(e){
	var value = $jqPm(e).val();
	if (typeof(value) == 'undefined' || value == 0) return;
	if ($jqPm(e).attr('id') == 'currentTemplate') $jqPm('.noSelectedTemplateInfo').hide();
	$jqPm('#displayConfiguration').load(_base_config_url+"&pm_load_function=displayTemplateConfiguration&template_class="+value, function() {$jqPm(this).fadeIn('fast');});
}

function displayRelatedTemplateWizard(e){
	var value = $jqPm(e).val();
	if (typeof(value) == 'undefined' || value == 0) return;
	$jqPm('#pm_mc3_wizard').load(_base_config_url+"&pm_load_function=displayTemplateWizard&id_template="+value, function() {$jqPm(this).fadeIn('fast');});
	$jqPm('#id_template_input').val(value);
}

function reloadBackOfficeTemplate(templateClass) {
	$jqPm('#pm_mc3_global_content').load(_base_config_url+"&pm_load_function=display_"+templateClass+"_Wizard");
}

function pm_saveItemsOrder(key) {
	var order = "";
	$jqPm("#pm_mc3_"+key+"_global_sort>li, #pm_mc3_"+key+"_product_line_sort_1>li, #pm_mc3_"+key+"_product_line_sort_2>li, #pm_mc3_"+key+"_product_line_sort>td, #pm_mc3_"+key+"_footer_sort>li, #pm_mc3_"+key+"_cart_summary td").each(function(){
		order += $jqPm(this).attr("id")+"-";
	});
	$jqPm('#fields_order').val(order);
	$jqPm.ajax({type : "GET", url : window.location+'&saveItemsOrder='+order+"&templateKey="+key });
}

function pm_saveDisplaySettings(itemName, displayValue) {
	$jqPm.ajax({type : "GET", url : window.location+'&saveDisplaySettings&itemName='+itemName+'&displayValue='+displayValue });
}

function showRelatedItems(e) {
	var itemName = $jqPm(e).attr("name");
	var itemVal = $jqPm(e).val();
	var templateKey = $jqPm("#template_key").val();
	
	switch(itemName){
		case 'display_free_content_1':
			pm_saveDisplaySettings(itemName, itemVal);
			if (itemVal==1) $jqPm("#pm_mc3_"+templateKey+"_free_content_1").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("#pm_mc3_"+templateKey+"_free_content_1").fadeOut("fast", function(){removeStyle(this)}).removeAttr('style');
		break;
		case 'display_title':
			pm_saveDisplaySettings(itemName, itemVal);
			if (itemVal==1) $jqPm("#pm_mc3_"+templateKey+"_title").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("#pm_mc3_"+templateKey+"_title").fadeOut("fast", function(){removeStyle(this)}).removeAttr('style');
		break;
		case 'display_free_content_2':
			pm_saveDisplaySettings(itemName, itemVal);
			if (itemVal==1) $jqPm("#pm_mc3_"+templateKey+"_free_content_2").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("#pm_mc3_"+templateKey+"_free_content_2").fadeOut("fast", function(){removeStyle(this);}).removeAttr('style');
		break;
		case 'display_free_shipping':
			pm_saveDisplaySettings(itemName, itemVal);
			if (itemVal==1) $jqPm("#pm_mc3_"+templateKey+"_free_shipping").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("#pm_mc3_"+templateKey+"_free_shipping").fadeOut("fast", function(){removeStyle(this);}).removeAttr('style');
		break;
		case 'display_subtotal':
			pm_saveDisplaySettings(itemName, itemVal);
			if (itemVal==1) $jqPm("#pm_mc3_"+templateKey+"_subtotal_row").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("#pm_mc3_"+templateKey+"_subtotal_row").fadeOut("fast", function(){removeStyle(this);}).removeAttr('style');
		break;
		case 'display_discounts':
			pm_saveDisplaySettings(itemName, itemVal);
			if (itemVal==1) $jqPm("#pm_mc3_"+templateKey+"_discounts_row").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("#pm_mc3_"+templateKey+"_discounts_row").fadeOut("fast", function(){removeStyle(this);}).removeAttr('style');
		break;
		case 'display_hook_cross_selling_on_cart':
			pm_saveDisplaySettings(itemName, itemVal);
			if (itemVal==1) $jqPm("#pm_mc3_"+templateKey+"_hook_cross_selling_on_cart").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("#pm_mc3_"+templateKey+"_hook_cross_selling_on_cart").fadeOut("fast", function(){removeStyle(this);}).removeAttr('style');
		break;
		case 'display_taxes':
			pm_saveDisplaySettings(itemName, itemVal);
			if (itemVal==1) $jqPm("#pm_mc3_"+templateKey+"_total_tax_row").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("#pm_mc3_"+templateKey+"_total_tax_row").fadeOut("fast", function(){removeStyle(this);}).removeAttr('style');
		break;
		case 'background_overlay':
			if (itemVal==1) $jqPm("#background_overlay_options").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("#background_overlay_options").fadeOut("fast", function(){removeStyle(this)}).removeAttr('style');
		break;
		case 'option_active':
			if (itemVal==1) $jqPm("#pm_options_display").fadeIn("fast", function(){removeStyle(this); updateChosen();}).removeAttr('style');
			else $jqPm("#pm_options_display").fadeOut("fast", function(){removeStyle(this)}).removeAttr('style');
		break;
		case 'display_shipping':
			pm_saveDisplaySettings(itemName, itemVal);
			if (itemVal==1) $jqPm("tr#pm_mc3_"+templateKey+"_shipping_row, .shipping_info").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("tr#pm_mc3_"+templateKey+"_shipping_row, .shipping_info").fadeOut("fast", function(){removeStyle(this)}).removeAttr('style');
			break;
		case 'attributes':
			if (itemVal==1) $jqPm("#pm_mc3_attributes_options").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("#pm_mc3_attributes_options").fadeOut("fast", function(){removeStyle(this)}).removeAttr('style');
			break;
		case 'jgrowl_sticky':
			if (itemVal==1) $jqPm("#pm_mc3_jgrowl_lifetime").fadeOut("fast");
			else $jqPm("#pm_mc3_jgrowl_lifetime").fadeIn("fast");
			break;
		case 'jgrowl_text_active':
			if (itemVal==1) $jqPm("#pm_mc3_jgrowl_text").fadeIn("fast");
			else $jqPm("#pm_mc3_jgrowl_text").fadeOut("fast");
		break;
		case 'jgrowl_display_order_btn':
			pm_saveDisplaySettings(itemName, itemVal);
			if (itemVal==1) $jqPm("#pm_mc3_"+templateKey+"_order_now").fadeIn("fast", function(){removeStyle(this)}).removeAttr('style');
			else $jqPm("#pm_mc3_"+templateKey+"_order_now").fadeOut("fast", function(){removeStyle(this);}).removeAttr('style');
		break;
		case 'truncate_text':
			if(itemVal==1) $jqPm("#pm_options_truncate_display").fadeIn("fast");
			else $jqPm("#pm_options_truncate_display").fadeOut("fast");
		break;
	}
	setTimeout(function() { pm_saveItemsOrder(templateKey)}, 500);
}

function updateChosen(){
	$('select.chosen').each(function(index) {
	  $(this).chosen('destroy').chosen()
	});
}

function removeStyle(e){
	$jqPm(e).css("opacity",'').css("width",'').css("height",'').css("padding",'').css("margin",'').css("overflow",'');
}