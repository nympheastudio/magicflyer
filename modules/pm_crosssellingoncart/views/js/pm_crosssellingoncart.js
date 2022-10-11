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

var pm_csocLoopInterval;
if (typeof($csocjqPm) == 'undefined') $csocjqPm = $;
function pm_reloadCartOnAdd(orderPageURL) {
	if ($('#order-detail-content').size() > 0) {
		$('div#csoc-container .ajax_add_to_cart_button').unbind('click');
		$(document).off('click', '.ajax_add_to_cart_button').off('click', 'div#csoc-container .ajax_add_to_cart_button').on('click', 'div#csoc-container .ajax_add_to_cart_button', function(e) {
			e.preventDefault();
			var idProduct =  $(this).data('id-product');
			if (typeof(idProduct) == 'undefined')
				var idProduct =  $(this).attr('rel').replace('nofollow', '').replace('ajax_id_product_', '');
			if ($(this).attr('disabled') != 'disabled') {
				ajaxCart.add(idProduct, null, false, this);
				$('#order-detail-content:eq(0)').load(orderPageURL + '?content_only=1 #order-detail-content:eq(0)', null, function() {
					if (typeof(deleteProductFromSummary) !== 'undefined') {
						deleteProductFromSummary('0_0');
					}
					if (typeof(getCarrierListAndUpdate) !== 'undefined') {
						getCarrierListAndUpdate();
					}
					if (typeof(updatePaymentMethodsDisplay) !== 'undefined') {
						updatePaymentMethodsDisplay();
					}
					if (typeof(upQuantity) !== 'undefined') {
						$('.cart_quantity_up').off('click').on('click', function(e){
							e.preventDefault();
							upQuantity($(this).attr('id').replace('cart_quantity_up_', ''));
							$('#' + $(this).attr('id').replace('_up_', '_down_')).removeClass('disabled');
						});
					}
					if (typeof(downQuantity) !== 'undefined') {
						$('.cart_quantity_down').off('click').on('click', function(e){
							e.preventDefault();
							downQuantity($(this).attr('id').replace('cart_quantity_down_', ''));
						});
					}
					if (typeof(deleteProductFromSummary) !== 'undefined') {
						$('.cart_quantity_delete' ).off('click').on('click', function(e){
							e.preventDefault();
							deleteProductFromSummary($(this).attr('id'));
						});
					}
				});
			}
			return false;
		});
	}
}