/**
 * pm_crosssellingoncart
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2017 - http://www.presta-module.com
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

function showHideElementsOnLoading() {
	if ($('#PM_CSOC_DISPLAY_IN_PRODUCT_off').is(':checked')) {
		$('.pm_csoc_product_options').addClass('hide');
	}

	if ($('#PM_CSOC_DISPLAY_IN_CART_off').is(':checked')) {
		$('.pm_csoc_cart_options').addClass('hide');
	}

	if ($('#PM_CSOC_CROSSSELLING_off').is(':checked')) {
		$('.pm_csoc_crossselling_options').addClass('hide');
	}

	if ($('#PM_CSOC_ACCESSORIES_off').is(':checked')) {
		$('.pm_csoc_accessories_options').addClass('hide');
	}
}

function initAutomaticHidding() {
    $(document).on('change', 'input[name="PM_CSOC_DISPLAY_IN_PRODUCT"]', function() {
    	if ($(this).val() == 1) {
 			$(".pm_csoc_product_options").removeClass("hide");
 		} else {
 			$(".pm_csoc_product_options").addClass("hide");
 		}
    });
	$(document).on('change', 'input[name="PM_CSOC_CROSSSELLING"]', function() {
    	if ($(this).val() == 1) {
 			$(".pm_csoc_crossselling_options").removeClass("hide");
 		} else {
 			$(".pm_csoc_crossselling_options").addClass("hide");
 		}
    });
	$(document).on('change', 'input[name="PM_CSOC_ACCESSORIES"]', function() {
    	if ($(this).val() == 1) {
 			$(".pm_csoc_accessories_options").removeClass("hide");
 		} else {
 			$(".pm_csoc_accessories_options").addClass("hide");
 		}
    });
	$(document).on('change', 'input[name="PM_CSOC_DISPLAY_IN_CART"]', function() {
    	if ($(this).val() == 1) {
 			$(".pm_csoc_cart_options").removeClass("hide");
 		} else {
 			$(".pm_csoc_cart_options").addClass("hide");
 		}
    });
}

function initProductsAutocomplete()
{
	$('#product_autocomplete_input').autocomplete('ajax_products_list.php', {
			minChars: 1,
			autoFill: true,
			max:20,
			matchContains: true,
			mustMatch:false,
			scroll:false,
			cacheLength:0,
			formatItem: function(item) {
				item[2] = '<div style="margin-right: 10px;float:left;"><img src="../img/tmp/product_mini_' + item[1] + '_' + idShop + '.jpg" /></div>';
				return item[2] + '<h4 class="media-heading">' + item[1] + ' - ' + item[0] + '</h4>';
			}
		}).result(addProduct);

	$('#product_autocomplete_input').setOptions({
		extraParams: { excludeIds : getProductsIds() }
	});
};

function getProductsIds()
{
	if ($('#PM_CSOC_inputProducts').val() === "") {
		$('#PM_CSOC_inputProducts').val('9999999999-');
		$('#PM_CSOC_nameProducts').val('9999999999¤');
	}
	return $('#PM_CSOC_inputProducts').val().replace(/\-/g,',');
}

function addProduct(event, data, formatted)
{
	if (data == null)
		return false;

	var productId = data[1];
	var productName = data[0];

	var $divProducts = $('#PM_CSOC_divProducts');
	var $inputProducts = $('#PM_CSOC_inputProducts');
	var $nameProducts = $('#PM_CSOC_nameProducts');

	/* delete product from select + add product line to the div, input_name, input_ids elements */
	$divProducts.html($divProducts.html() + '<div id="csoc_product_'+ productId +'" class="form-control-static"><button type="button" class="btn btn-default" onclick="delProduct('+ productId +')" name="' + productId + '"><i class="icon-remove text-danger"></i></button><img src="../img/tmp/product_mini_' + productId + '_' + idShop + '.jpg" />&nbsp;'+ productName +'</div>');
	$nameProducts.val($nameProducts.val() + productName + '¤');
	$inputProducts.val($inputProducts.val() + productId + '-');
	$('#product_autocomplete_input').val('');
	$('#product_autocomplete_input').setOptions({
		extraParams: { excludeIds : getProductsIds() }
	});
};

function delProduct(id)
{
	var div = getE('PM_CSOC_divProducts');
	var input = getE('PM_CSOC_inputProducts');
	var name = getE('PM_CSOC_nameProducts');

	// Cut hidden fields in array
	var inputCut = input.value.split('-');
	var nameCut = name.value.split('¤');

	if (inputCut.length != nameCut.length)
		return jAlert('Bad size');

	// Reset all hidden fields
	input.value = '';
	name.value = '';
	div.innerHTML = '';
	for (i in inputCut)
	{
		// If empty, error, next
		if (!inputCut[i] || !nameCut[i])
			continue ;

		if (inputCut[i] == '9999999999' && nameCut[i] == '9999999999') {
			continue;
		}
		
		// Add to hidden fields no selected products OR add to select field selected product
		if (inputCut[i] != id)
		{
			input.value += inputCut[i] + '-';
			name.value += nameCut[i] + '¤';
			div.innerHTML += '<div class="form-control-static"><button type="button" class="btn btn-default" onclick="delProduct('+ inputCut[i] +')" name="' + inputCut[i] +'"><i class="icon-remove text-danger"></i></button><img src="../img/tmp/product_mini_' + inputCut[i] + '_' + idShop + '.jpg" />&nbsp;' + nameCut[i] + '</div>';
		}
		else
			$('#selectAccessories').append('<option selected="selected" value="' + inputCut[i] + '-' + nameCut[i] + '">' + inputCut[i] + ' - ' + nameCut[i] + '</option>');
	}

	$('#product_autocomplete_input').setOptions({
		extraParams: {excludeIds : getProductsIds()}
	});
};

$(document).ready(function() {
	$('div#addons-rating-container p.dismiss a').click(function() {
		$('div#addons-rating-container').hide(500);
		$.ajax({type : "GET", url : window.location+'&dismissRating=1' });
		return false;
	});

	$("#csoc_advanced_styles_fieldset").parent().removeClass("margin-form");

	showHideElementsOnLoading();
	initAutomaticHidding();
	initProductsAutocomplete();
});