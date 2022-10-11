var modalAjaxCart;
var pmjQueryInstance;
var mc3Debug = false;

function initModalAjaxCart() {
	if (mc3Debug) console.log('initModalAjaxCart');
	
	modalAjaxCart = {
		//override every button in the page in relation to the cart (with a delay)
		overrideButtonsInThePageDelayed : function() {
			modalAjaxCart.overrideButtonsInThePage();
			setTimeout(function() { modalAjaxCart.overrideButtonsInThePage(); }, 300);
		},
		
		//override every button in the page in relation to the cart
		overrideButtonsInThePage : function(){
			if (mc3Debug) console.log('overrideButtonsInThePage');
			//for every 'add' buttons...
			$(modalCartjQuerySelector).unbind('click').click(function(){
				var idProduct =  $.trim($(this).attr('rel').replace('nofollow', '').replace('ajax_id_product_', ''));
				if (typeof(idProduct) == 'undefined' || idProduct == '' || isNaN(idProduct)) {
					var idProduct =  $(this).data('id-product');
				}
				if ($(this).attr('disabled') != 'disabled') {
					if ($('input#quantity_wanted_'+idProduct).size() > 0 && !isNaN($('input#quantity_wanted_'+idProduct).val())) {
						modalAjaxCart.add(idProduct, null, false, $('input#quantity_wanted_'+idProduct).val());
					} else {
						modalAjaxCart.add(idProduct, null, false);
					}
				}
				return false;
			});
			
			//for product page 'add' button...
			$(modalCartProductjQuerySelector).unbind('click').click(function(){
				modalAjaxCart.add( $('#product_page_product_id').val(), $('#idCombination').val(), true, $('#quantity_wanted').val(), null);
				return false;
			});
		},
		refreshCartOrder : function(){
			if (mc3Debug) console.log('refreshCartOrder');
			$('#order-detail-content:first').load(modalCartOrderPageLink+'?content_only=1 #order-detail-content:first', function(responseText, textStatus, XMLHttpRequest) {
				$('.cart_quantity_up').unbind('click').click(function(){ upQuantity($(this).attr('id').replace('cart_quantity_up_', '')); return false;	});
				$('.cart_quantity_down').unbind('click').click(function(){ downQuantity($(this).attr('id').replace('cart_quantity_down_', '')); return false; });
				$('.cart_quantity_delete' ).unbind('click').click(function(){ deletProductFromSummary($(this).attr('id')); return false; });
				$('.cart_quantity_input').typeWatch({ highlight: true, wait: 600, captureLength: 0, callback: updateQty });
			});
		},
		// cart to fix display when using back and previous browsers buttons
		refresh : function(){
			if (mc3Debug) console.log('refresh');
			//send the ajax request to the server
			$.ajax({
				type: 'GET',
				headers: { "cache-control": "no-cache" },
				url: baseDir + 'cart.php' + '?rand=' + new Date().getTime(),
				async: true,
				cache: false,
				dataType : "json",
				data: 'ajax=true&token=' + static_token,
				success: function(jsonData)
				{
					modalAjaxCart.updateCart(jsonData, false);
				}
			});
		},
		
		// add a product in the cart via ajax
		add : function(idProduct, idCombination, addedFromProductPage, quantity, whishlist){
			if (mc3Debug) console.log('add');
			if (addedFromProductPage && !checkCustomizations()) {
				alert(fieldRequired);
				return ;
			}
			// avoid double-click
			if (addedFromProductPage) {
				$(modalCartProductjQuerySelector).attr('disabled', 'disabled').removeClass('exclusive').addClass('exclusive_disabled');
				$('.filled').removeClass('filled');
			} else {
				$(modalCartjQuerySelector).attr('disabled', 'disabled');
			}
			//send the ajax request to the server
			$.ajax({
				type: 'POST',
				url: baseDir + 'cart.php' + '?rand=' + new Date().getTime(),
				async: true,
				cache: false,
				dataType : "json",
				data: 'controller=cart&add=1&ajax=true&qty=' + ((quantity && quantity != null) ? quantity : '1') + '&id_product=' + idProduct + '&token=' + static_token + ( (parseInt(idCombination) && idCombination != null) ? '&ipa=' + parseInt(idCombination): '') + ($('#order-detail-content:first').size() > 0 ? '&summary=true' : ''),
				success: function(jsonData) {
					if (!jsonData.errors) modalAjaxCart.showModal("product_add", idProduct, idCombination);
					// add appliance to whishlist module
					if (whishlist && !jsonData.errors)
						WishlistAddProductCart(whishlist[0], idProduct, idCombination, whishlist[1]);
					
					modalAjaxCart.updateCart(jsonData, false);
					
					if (typeof(ajaxCart) != 'undefined' && !jsonData.errors) {
						ajaxCart.updateCartInformation(jsonData, addedFromProductPage);
					} else {
						//reactive the button when adding has finished
						if (addedFromProductPage)
							$(modalCartProductjQuerySelector).removeAttr('disabled').addClass('exclusive').removeClass('exclusive_disabled');
						else
							$(modalCartjQuerySelector).removeAttr('disabled');
					}
					
					if ($('#order-detail-content:first').size() > 0) {
						if (typeof(updateCartSummary) != 'undefined')
							updateCartSummary(jsonData.summary);
						if (typeof(updateCustomizedDatas) != 'undefined')
							updateCustomizedDatas(jsonData.customizedDatas);
						if (typeof(updateHookShoppingCart) != 'undefined')
							updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
						if (typeof(updateHookShoppingCartExtra) != 'undefined')
							updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
						if (typeof(updateCarrierList) != 'undefined' && jsonData.carriers != null)
							updateCarrierList(jsonData);
						// if we are in one page checkout
						if (typeof(updateCarrierSelectionAndGift) != 'undefined' && typeof(orderProcess) != 'undefined')
							updateCarrierSelectionAndGift();
						else if (typeof(updateCartMinQuantity) != 'undefined')
							updateCartMinQuantity();
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
						alert("Impossible to add the product to the cart.\n\ntextStatus: '" + textStatus + "'\nerrorThrown: '" + errorThrown + "'\nresponseText:\n" + XMLHttpRequest.responseText);
						//reactive the button when adding has finished
						if (addedFromProductPage)
							$(modalCartProductjQuerySelector).removeAttr('disabled').addClass('exclusive').removeClass('exclusive_disabled');
						else
							$(modalCartjQuerySelector).removeAttr('disabled');
					}
			});
		},
		
		/* Modal Type:
		 * - product_add
		 * - pack_add
		 * - refresh
		 */
		showModal : function(type, idProduct, idCombination) {
			if (mc3Debug) console.log('showModal');
			var quantityAdded = parseInt($('#quantity_wanted').val());

			if ($('body').attr('id') == 'order' || $('body').attr('id') == 'order-opc') modalAjaxCart.refreshCartOrder();
			if (modalCartType == 'jgrowl') {
				$.ajax({
					type: 'POST',
					url: baseDir + 'modules/pm_modalcart3/controllers/front/ajax_front.php',
					cache: false,
					dataType : 'script',
					data: 'action='+type+'&id_product='+(typeof(idProduct) != 'undefined' ? '&id_product='+idProduct : '')+(typeof(idCombination) != 'undefined' ? '&id_product_attribute='+idCombination : '')+'&'+mc3_uniqid(),
					success: function(responseText) {}
				});
			} else {
				var modalUrl = baseDir + 'modules/pm_modalcart3/controllers/front/ajax_front.php?action='+type+(typeof(idProduct) != 'undefined' ? '&id_product='+idProduct : '')+(typeof(idCombination) != 'undefined' ? '&id_product_attribute='+idCombination : '')+'&quantity_added='+quantityAdded+'&modal=true&'+mc3_uniqid();
				mc3_setJQueryInstance();

				pmjQueryInstance.ajax({
					url: modalUrl,
					cache: false,
					success: function(responseText) {
						// Update modal content only
						popupInstance = (typeof(contentOnly) !== 'undefined' && contentOnly) ? window.parent.pmjQueryInstance.magnificPopup : pmjQueryInstance.magnificPopup;

						if (typeof(popupInstance.instance.isOpen) != 'undefined' && popupInstance.instance.isOpen) {
							popupInstance.instance.content.html(responseText);
						} else {
							popupInstance.open({
								showCloseBtn: modalCartDisplayCloseButton,
								items: {
									src: responseText,
									type: 'inline'
								}
							});
						}
						modalAjaxCart.overrideButtonsInThePageDelayed();
					}
				});
			}
		},
		
		// modal for advanced pack (deprecated - only do a refresh)
		showModalPack : function(id_pack, packProductId, packCombination, packProductExclude) {
			if (mc3Debug) console.log('showModalPack');
			if ($('body').attr('id') == 'order' || $('body').attr('id') == 'order-opc') modalAjaxCart.refreshCartOrder();
		},
		
		// Remove a product
		remove : function(idProduct, idCombination, customizationId, idAddressDelivery){
			if (mc3Debug) console.log('remove');
			$.ajax({
				type: 'POST',
				url: baseDir + 'cart.php' + '?rand=' + new Date().getTime(),
				async: true,
				cache: false,
				dataType : "json",
				data: 'controller=cart&delete=1&id_product=' + idProduct + '&ipa=' + ((idCombination != null && parseInt(idCombination)) ? idCombination : '') + ((customizationId && customizationId != null) ? '&id_customization=' + customizationId : '') + '&id_address_delivery=' + idAddressDelivery + '&token=' + static_token + '&ajax=true',
				success: function(jsonData)	{
					modalAjaxCart.updateCart(jsonData, true);
					if ($('body').attr('id') == 'order' || $('body').attr('id') == 'order-opc')
						deleteProductFromSummary(idProduct+'_'+idCombination+'_'+customizationId+'_'+idAddressDelivery);
				},
				error: function() {alert('ERROR: unable to delete the product');}
			});
		},
		
		// Update product quantity from the modal
		updateModalQty : function(action, id_product, id_product_attribute, id_address_delivery, id_customization) {
			if (mc3Debug) console.log('updateModalQty');
			//send the ajax request to the server
			$.ajax({
				type: 'GET',
				url: baseDir + 'cart.php',
				async: true,
				cache: false,
				dataType: 'json',
				data: 'ajax=true'
					+'&add'
					+'&summary'
					+'&id_product='+id_product
					+'&ipa='+id_product_attribute
					+ (action == 'del' ? '&op=down' : '')
					+ ((id_customization !== 0) ? '&id_customization='+id_customization : '')
					+'&qty=1'
					+'&token='+static_token,
				success: function(jsonData) {
					modalAjaxCart.updateCart(jsonData, true);
				},
				error: function() { alert('ERROR: unable to update product quantity'); }
			});
		},
		
		// Remove a product from the modal
		removeModalProduct : function(id_product, id_product_attribute, id_customization, id_address_delivery) {
			if (mc3Debug) console.log('removeModalProduct');
			modalAjaxCart.remove(id_product, id_product_attribute, id_customization, id_address_delivery);
		},
		
		//generally update the display of the cart
		updateCart : function(jsonData, refreshCall) {
			if (mc3Debug) console.log('updateCart');
			if (typeof(refreshCall) == 'undefined') refreshCall = false;
			modalAjaxCart.overrideButtonsInThePageDelayed();
			//user errors display
			if (jsonData.hasError)
			{
				var errors = '';
				for (error in jsonData.errors)
					//IE6 bug fix
					if (error != 'indexOf')
						errors += jsonData.errors[error] + "\n";
				alert(errors);
			} else {
				
				if (typeof(ajaxCart) != 'undefined') ajaxCart.expand();
				if (typeof(ajaxCart) != 'undefined') ajaxCart.updateCartEverywhere(jsonData);
				//if (typeof(ajaxCart) != 'undefined') ajaxCart.hideOldProducts(jsonData);
				if (typeof(ajaxCart) != 'undefined') ajaxCart.displayNewProducts(jsonData);
				if (typeof(ajaxCart) != 'undefined' && typeof(jsonData.discounts) != 'undefined') ajaxCart.refreshVouchers(jsonData);
				if (typeof(ajaxCart) != 'undefined') ajaxCart.refresh();
				
				if (typeof(jsonData.products) != 'undefined' && jsonData.products.length == 0) {
					// Close all modal if it's open
					mc3_closeModal();
				} else {
					if (typeof(refreshCall) != 'undefined' && refreshCall == true) modalAjaxCart.showModal("refresh");
				}
				
			}
			modalAjaxCart.overrideButtonsInThePageDelayed();
		}
	}
	//modalAjaxCart.refresh();
	modalAjaxCart.overrideButtonsInThePageDelayed();
}

function mc3_uniqid(){
	var newDate = new Date;
	return newDate.getTime();
}

function mc3_setJQueryInstance() {
	if (typeof($mc3jqPm) != 'undefined') {
		pmjQueryInstance = $mc3jqPm;
	} else {
		pmjQueryInstance = $;
	}
	if (typeof(pmjQueryInstance) == 'undefined') {
		if (mc3Debug) console.log('pmjQueryInstance is undefined');
	}
}

function mc3_closeModal() {
	pmjQueryInstance.magnificPopup.close();
}

function mc3_continueShopping(){
	// Close all modal if it's open
	mc3_closeModal();
}

function mc3_overrideButtonsInThePageLoop() {
	var interval = setInterval(function() {
		if (typeof(modalAjaxCart) != 'undefined') {
			modalAjaxCart.overrideButtonsInThePage();
		}
	}, 1000);
}
mc3_overrideButtonsInThePageLoop();

$(document).ready(function(){
	initModalAjaxCart();
});

if (typeof(document) != 'undefined') $(document).ready(function() { if (typeof(modalAjaxCart) != 'undefined') modalAjaxCart.overrideButtonsInThePageDelayed(); });
if (typeof(window) != 'undefined') $(window).load(function() { if (typeof(modalAjaxCart) != 'undefined') modalAjaxCart.overrideButtonsInThePageDelayed(); });