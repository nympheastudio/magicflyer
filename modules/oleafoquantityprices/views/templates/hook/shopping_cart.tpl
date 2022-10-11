{** 
  * ---------------------------------------------------------------------------------
  * 
  * This file is part of the 'oleafoquantityprices' module feature 
  * Developped for Prestashop  platform.
  * You are not allowed to use it on several site
  * You are not allowed to sell or redistribute this module
  * This header must not be removed
  * 
  * @category XXX
  * @author OleaCorner <contact@oleacorner.com> <www.oleacorner.com>
  * @copyright OleaCorner
  * @version 1.0
  * 
  * ---------------------------------------------------------------------------------
  *}
{if $oleafoqty_multi_of_minimal}
{if $oleafoqty_isforajax}
<script type="text/javascript">
//<![CDATA[
$(document).ready(function () {
	
	$('#cart_summary .cart_item').each(function () {
		if ($(this).data('oleafoqty_minimalqty') > 1 )
		{
			$(this).find('.oleafoqty_cart_quantity_input')
				.val(oleafoqty_roundqty($(this).find('.cart_quantity_input').val(), $(this).data('oleafoqty_minimalqty')));
		}
	});
});
//]]>
</script>
{else}
<script type="text/javascript">
//<![CDATA[
	var $olea_cart_summary = $('#cart_summary');
	{foreach $products as $olea_product}
		$olea_cart_summary.find('[id^="product_'+{$olea_product.id_product|intval}+'_'+{$olea_product.id_product_attribute|intval}+'"]').data('oleafoqty_minimalqty', {$olea_product.minimal_quantity|intval})
	{/foreach}
	
	function oleafoqty_roundqty (total, step) {
		return Math.max(1, Math.floor(parseInt(total)/parseInt(step)));
	}
	
$(document).ready(function () {
	$('#cart_summary .cart_item').each(function () {
		if ($(this).data('oleafoqty_minimalqty') > 1 )
		{
			$theinput = $(this).find('.cart_quantity_input');
			$('<div>'+"{l s='By' mod='oleafoquantityprices'}"+' '+parseInt($(this).data('oleafoqty_minimalqty'))+' x</div>')
				.insertBefore($theinput.clone().removeAttr('id').removeAttr('name')
								.removeClass('cart_quantity_input2').addClass('oleafoqty_cart_quantity_input')
								.val(oleafoqty_roundqty($('[name="'+$theinput.attr('name')+'_hidden"]').val(), $(this).data('oleafoqty_minimalqty')))
								.typeWatch({
										highlight: true, wait: 600, captureLength: 0, callback: function(val){
											$(this.el).closest('.cart_item').find('.cart_quantity_input:not(".oleafoqty_cart_quantity_input")').val(val*$(this.el).closest('.cart_item').data('oleafoqty_minimalqty'));
											updateQty(val*$(this.el).closest('.cart_item').data('oleafoqty_minimalqty'), true, $(this.el).closest('.cart_item').find('.cart_quantity_input'));
										}
									})
								.insertAfter($theinput.hide())
								);
		}
	});
	if ($().jquery < "1.9") {
		$('.cart_quantity_up').die('click');
		$('.cart_quantity_down').die('click');
	}
	$('.cart_quantity_up').off('click').on('click', function(e){
		e.preventDefault();
		upQuantity($(this).attr('id').replace('cart_quantity_up_', ''), $(this).closest('.cart_item').data('oleafoqty_minimalqty'));
		$('#' + $(this).attr('id').replace('_up_', '_down_')).removeClass('disabled');
	});
	$('.cart_quantity_down').off('click').on('click', function(e){
		e.preventDefault();
		downQuantity($(this).attr('id').replace('cart_quantity_down_', ''), $(this).closest('.cart_item').data('oleafoqty_minimalqty'));
	});

});

//]]>
</script>
{/if}
{/if}