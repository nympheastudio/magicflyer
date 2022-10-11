var oleafoqtyprices_qtydown = function ($elt) {
	var $qty_elt = $elt.siblings('.oleacombiqty');
	qty = parseInt($qty_elt.val());
	if (! isNaN (qty) && qty > 1)
		$qty_elt.val (qty - 1);
	else
		$qty_elt.val (1);
	
	var minimal_quantity = $elt.closest('.oleafoqty_combinationrow').data('minimal_quantity');
	if (typeof minimal_quantity != "undefined") {
		// Case of Combination
	}
	else {
		// case of simple product
		minimal_quantity = (typeof minimalQuantity != "undefined")
				? minimalQuantity  // Product page
				: $elt.closest('tr').data('minimal_quantity'); // ajax Popup
	}
	
	if ($qty_elt.val() < parseInt(minimal_quantity))
		$qty_elt.val(parseInt(minimal_quantity));
		
	oleafoqtyprices_updatetotal($elt);
}

var oleafoqtyprices_qtyup = function ($elt) {
	var $qty_elt = $elt.siblings('.oleacombiqty');
	var qty = parseInt($qty_elt.val());
	if (! isNaN (qty))
		$qty_elt.val (qty + 1);
	else
		$qty_elt.val (1);
	oleafoqtyprices_updatetotal($elt);
}
//--------------------------------------------------------
var oleafoqtyprices_qtydownmulti = function ($elt) {
	var $qty_elt = $elt.siblings('.oleafoqty_multiqtyinput');
	var qty = parseInt($qty_elt.val());
	if (! isNaN (qty) && qty > 1)
		$qty_elt.val (qty - 1);
	else
		$qty_elt.val (1);
	$qty_elt.change();
	//oleafoqtyprices_updatemultiquantity($elt);
}
var oleafoqtyprices_qtyupmulti = function ($elt) {
	var $qty_elt = $elt.siblings('.oleafoqty_multiqtyinput');
	var qty = parseInt($qty_elt.val());
	if (! isNaN (qty))
		$qty_elt.val (qty + 1);
	else
		$qty_elt.val (1);
	$qty_elt.change();
	//oleafoqtyprices_updatemultiquantity($elt);
}

//--------------------------------------------------------
var oleafoqtyprices_updatetotal = function ($elt) {
	var qtyprice = 0,
		$td = $elt.closest('td'),
		prices = $td.data('prices'),
		$qty_elt,
		qty;

	if ($elt.hasClass('oleacombiqty'))
		$qty_elt = $elt;
	else
		$qty_elt = $elt.siblings('.oleacombiqty');
	qty = parseInt($qty_elt.val());

	if (! isNaN(qty)) {
		for (i=0,m=prices.length; i<m; i++) {
			if (prices[i].qty <= qty)
				qtyprice = prices[i].price;
		}
		var total = qty * qtyprice;
		$td.find('.foqty_total_price').html(formatCurrency(total, currencyFormat, currencySign, currencyBlank));
	}
	
} 

//--------------------------------------------------------
var oleafoqtyprices_updatemultiquantity = function ($elt) {
	var multi = parseInt($elt.val());
	
	if (isNaN(multi))
		multi = 0;
	
	nb_total = multi * parseInt($elt.closest('tr').data('minimal_quantity'));
	var $td = $elt.closest('.oleacombitd');
	$td.find('.oleaqty_multiqtytotal').html(nb_total);
	$td.find('.oleacombiqty').val(nb_total);
	oleafoqtyprices_updatetotal($td.find('.oleacombiqty'));
}

var oleafoqtyprices_updatespanspricesofquantity = function () {
	var $spansprices = $('.oleafoqty_globalqtyprice');
	var olea_id_combination = parseInt($('#idCombination').val());
	if (isNaN(olea_id_combination)) // case of simple product
		olea_id_combination = 0

	$spansprices.hide();
	$('.content_prices').removeClass('oleafoqty_hasspanprices');
	$('[id="oleafoqty_price_'+olea_id_combination+'"]').each(function (index, element) {
		if ($(this).data('oleafromqty') <= parseInt($('#quantity_wanted').val())) {
			$spansprices.hide();
			$(this).show();
		}
	});
	if ($spansprices.filter(':visible').length > 0)
		$('.content_prices').addClass('oleafoqty_hasspanprices');
}

//--------------------------------------------------------
var oleafoqtyprices_init = function ($root) {
	oleafoqty_combinations = [];
	if (typeof combinations != "undefined") {
		$.each(combinations, function(key, value) {
			oleafoqty_combinations[value['idCombination']] = value;
		});
	}
	
	$root.find('.foqty_btn_updateqty_down').unbind('click').click(function () {
		oleafoqtyprices_qtydown( $(this) );
		return false;
	}).click();

	$root.find('.foqty_btn_updateqty_up').unbind('click').click(function () {
		oleafoqtyprices_qtyup( $(this) );
		return false;
	});

	$root.find('.oleacombiqty').bind('change keyup', function () {
		oleafoqtyprices_updatetotal($(this));
	}).each (function () {
		oleafoqtyprices_updatetotal($(this));
	});
	
	$root.find('.oleaqty_multiqty').bind('change keyup', function () {
		oleafoqtyprices_updatemultiquantity($(this));
	});
	$root.find('.oleafoqty_multiqtyintable').bind('change keyup', function () {
		oleafoqtyprices_updatemultiquantity($(this));
	});
	
	$root.find('.foqty_btn_updateqty_downmulti').unbind('click').click(function () {
		oleafoqtyprices_qtydownmulti( $(this) );
		return false;
	}).click();

	$root.find('.foqty_btn_updateqty_upmulti').unbind('click').click(function () {
		oleafoqtyprices_qtyupmulti( $(this) );
		return false;
	});

	// Product page, main qty management
	if ($root.find('.oleafoqty_mainmulti').length) {
		$root.find('.oleafoqty_mainmulti').bind('change keyup', function () {
			var multi = parseInt($(this).find('.oleaqty_multiqty').val());
			if (isNaN(multi))
				multi = 0;
			var mini_qty = (oleafoqty_combinations.length > 0) 
						?oleafoqty_combinations[$('#idCombination').val()]['minimal_quantity']
						:minimalQuantity;
			$('#quantity_wanted').val(mini_qty * multi);
			$(this).find('.oleaqty_multiqtytotal').html(mini_qty * multi);
			oleafoqtyprices_updatespanspricesofquantity();
		}).find('.oleaqty_multiqtymini').html((oleafoqty_combinations.length > 0) ?oleafoqty_combinations[$('#idCombination').val()]['minimal_quantity'] :minimalQuantity);
		$root.find('.oleafoqty_mainmulti').change();
		
		if ($('#quantity_wanted_multi_p').length > 0) {
			$('#quantity_wanted_multi_p').insertAfter($('#quantity_wanted_p'));
			if ($('#quantity_wanted_p:visible').length > 0)
				$('#quantity_wanted_multi_p').removeClass('hidden');
			$('#quantity_wanted_p').wrap( "<div class='hidden'></div>" );
		}
	}
	
	$('#oleafoqty_spansprices').appendTo($('#our_price_display').parent().parent());
	
	$root.find('.oleacombitd a').unbind('click').click(function(){
		var $td = $(this).closest('.oleacombitd'),
		    qty = parseInt($td.find('.oleacombiqty').val()),
		    id_product_attribute = parseInt($td.find('.oleacombiattrib').val());
		if (isNaN(qty) || qty < 1)
			return false;
		if (typeof modalAjaxCart != "undefined")
			modalAjaxCart.add( $('#product_page_product_id').val(), id_product_attribute, true, qty, null);
		else {
			ajaxCart.add( $('#product_page_product_id').val(), id_product_attribute, true, null, qty, null);
			$.fancybox.close();
			//$.nmTop().close();
		}
		return false
	});

}

var oleafoqtyprices_showpopup = function (idProduct) {
	var oleaQtyAjax = oleaQtyPricesPath+'ajaxinfo.php?type=declinationstable&id_product='+idProduct+'&modal=true&width='+oleaQtyPricesWidth;
	//if ($().jquery > "1.3") {
	//	$.nmManual(modalUrl,{showCloseButton: true, closeButton: '<a href="#" class="nyroModalClose nyroModalCloseButton nmReposition" title="close">Close</a>',sizes: {initW: oleaQtyPricesWidth,w:oleaQtyPricesWidth,minW:oleaQtyPricesWidth},closeOnClick: false,callbacks: {afterShowCont:function() {setTimeout(function() {$.nmTop().resize(true);},100);}}});
	//}
	$.ajax({
		url: oleaQtyAjax,
		type: 'get',
		
		//Succès de la requête
		success: function(data) {
			$data = $(data);
			oleafoqtyprices_init($data);
			$.fancybox({
				'transitionIn'	:	'elastic',
				'transitionOut'	:	'elastic',
				'speedIn'		:	600, 
				'speedOut'		:	200, 
				'overlayShow'	:	false,
				'content'       :   $data
			});
			/*
			$.nmData($data,{showCloseButton: true, 
							closeButton: '<a href="#" class="nyroModalClose nyroModalCloseButton nmReposition" title="close">Close</a>',
							sizes2: {initW: oleaQtyPricesWidth,w:oleaQtyPricesWidth,minW:oleaQtyPricesWidth},
							closeOnClick: false,
							callbacks2: {afterShowCont:function() {setTimeout(function() {$.nmTop().resize(true);},100);}}});
							*/
		}
	});

	
	
}

var oleafoqtyprices_initpopupinlist = function () {

	if (typeof modalAjaxCart != "undefined") {
		var pmoverridebuttons = modalAjaxCart.overrideButtonsInThePage;
		modalAjaxCart.overrideButtonsInThePage = function () {
			pmoverridebuttons();
			$('.ajax_add_to_cart_button').unbind('click').click(function(){
				var idProduct =  $(this).attr('rel').replace('ajax_id_product_', '');
				if (idProduct == 'nofollow') // case of 1.6 template
					idProduct = $(this).data('id-product');
				oleafoqtyprices_showpopup (idProduct);
				return false;
			});

		}
	} 
	if (typeof ajaxCart != "undefined") {
		var overridebuttons = ajaxCart.overrideButtonsInThePage;
		ajaxCart.overrideButtonsInThePage = function () {
			overridebuttons();
			$('.ajax_add_to_cart_button').unbind('click').click(function(){
				var idProduct =  $(this).attr('rel').replace('ajax_id_product_', '');
				if (idProduct == 'nofollow')  // case of 1.6 template
					idProduct = $(this).data('id-product');
				//modalAjaxCart.add(idProduct, null, false);
				oleafoqtyprices_showpopup (idProduct);
				return false;
			});

		}
	}
}


//--------------------------------------------------------
if (typeof findCombination != 'undefined') {
	var olea_main_findCombination = findCombination;
	findCombination = function ()
	{
		olea_main_findCombination();
		if (typeof oleafoqty_combinations !== 'undefined') {
			var id = $("#idCombination").val(),
		    	$qty_wanted_multi = $('#quantity_wanted_multi_p');
			$qty_wanted_multi.find('.oleaqty_multiqtymini').html(oleafoqty_combinations[id]['minimal_quantity']);
			$qty_wanted_multi.find('input').val(1);
			$('#quantity_wanted_multi_p .oleaqty_multiqty').change();
		}
		checkMinimalQuantity();
	}
}

//--------------------------------------------------------
if (typeof checkMinimalQuantity != 'undefined') {
	var olea_main_checkMinimalQuantity = checkMinimalQuantity;
	checkMinimalQuantity = function (minimal_qty)
	{
		olea_main_checkMinimalQuantity(minimal_qty);
		oleafoqtyprices_updatespanspricesofquantity();
	}
}


//--------------------------------------------------------
$(document).ready(function () {
	oleafoqtyprices_init($('body'));
});
