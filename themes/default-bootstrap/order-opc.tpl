

{if $opc}
	{assign var="back_order_page" value="order-opc.php"}
	{else}
	{assign var="back_order_page" value="order.php"}
{/if}

{if $PS_CATALOG_MODE}
	{capture name=path}{l s='Your shopping cart'}{/capture}
	<h2 id="cart_title">{l s='Your shopping cart'}</h2>
	<p class="alert alert-warning">{l s='Your new order was not accepted.'}</p>
{else}
	{if $productNumber}
		<div id="etape1">
		<!-- Shopping Cart -->
		<!--
		<div class="info-special">{l s='Cher(e) client(e), pour cause de congés estivaux de certains de nos collaborateurs, les commandes ne pourront être livrées avant le 22 août. Nous vous remercions de votre patience et votre compréhension. Bel été à vous !'}</div>
		-->
		{include file="$tpl_dir./shopping-cart.tpl"}
		<!-- End Shopping Cart -->

		</div>
		<div id="etape2">
		<h2 id="cart_title">{l s='Shipping & Payment'}</h2>
		
		<!-- duplicata summary -->
		
		
		
		<table id="cart_summary_bis" class="table table-bordered {if $PS_STOCK_MANAGEMENT}stock-management-on{else}stock-management-off{/if}">
			
			<tfoot class="col-md-3 sol-xs-12">


				{if $use_taxes}
					{if $priceDisplay}
					
						
						<tr class="title_summary_cart"><td colspan="2"><h2>{l s='Summary'}</h2><p>{l s='10 Butterflies more to get the best pricetest1'}</p></td></tr>
						
					{else}
						<tr class="title_summary_cart"><td colspan="2"><h2>{l s='Summary'}</h2><!-- <p>{l s='10 Butterflies more to get the best pricetest2'}</p> --></td></tr>
						
					{/if}
				{else}
					<tr class="title_summary_cart"><td colspan="2"><h2>{l s='Summary'}</h2><p>{l s='10 Butterflies more to get the best pricetest3'}</p></td></tr>
					
				{/if}
				
				<!-- voucher enregistré -->
				{if sizeof($discounts)}
				
					{foreach $discounts as $discount}
						<tr class="cart_discount {if $discount@last}last_item{elseif $discount@first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
							<td colspan="1" class="cart_discount_name" >{$discount.name}<a
										href="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}?deleteDiscount={$discount.id_discount}"
										class="price_discount_delete"
										title="{l s='Delete'}">
										<i class="icon-trash"></i>
									</a></td>

							<td colspan="1" class="cart_discount_price">
								<span class="price-discount price">{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}</span>
							</td>
						
						</tr>
						<tr>
							<td colspan="2"><hr/></td>
						</tr>
					{/foreach}
				
			{/if}
				<!-- FIN voucher enregistré -->
				
				
				<tr {if $total_wrapping == 0} style="display: none;"{/if}>
					<td colspan="1" class="text-right">
						{if $use_taxes}
							{if $display_tax_label}{l s='Total gift wrapping (tax incl.)'}{else}{l s='Total gift-wrapping cost'}{/if}
						{else}
							{l s='Total gift-wrapping cost'}
						{/if}
					</td>
					<td colspan="1" class="price-discount price" id="total_wrapping">
						{if $use_taxes}
							{if $priceDisplay}
								{displayPrice price=$total_wrapping_tax_exc}
							{else}
								{displayPrice price=$total_wrapping}
							{/if}
						{else}
							{displayPrice price=$total_wrapping_tax_exc}
						{/if}
					</td>
				</tr>
				
				<tr class="cart_total_price">
					<td class="total_price_container text-right">
						<span>{l s='Total'}</span>
					</td>
					{if $use_taxes}
						<td colspan="1" class="price" id="total_price_container">
							<span id="total_price">{displayPrice price=$total_price}</span>
						</td>
					{else}
						<td colspan="1" class="price" id="total_price_container">
							<span id="total_price">{displayPrice price=$total_price_without_tax}</span>
						</td>
					{/if}
				</tr>
				
				<tr>
					<td colspan="2">
{if $total_price-$shippingCost > 0}
					{if $is_logged AND !$is_guest}
						<a class="button-continue">{l s='Finaliser ma commande'}</a>
					{else}
						<a data-action="login"  class="login btn" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Se connecter'}" id="show_modal_login">{l s='Se connecter'} </a>
					{/if}
{/if}
					</td>
				</tr>
				
			</tfoot>
			</table>
		
		
		<!-- FIN duplicata summary -->
		{if $is_logged AND !$is_guest}
			{include file="$tpl_dir./order-address.tpl"}
			{*include file="$tpl_dir./order-opc-new-account.tpl"*}
			
		{/if}
		<!-- Carrier -->
		{include file="$tpl_dir./order-carrier.tpl"}
		<!-- END Carrier -->
	
		<!-- Payment -->
{if $total_price-$shippingCost > 0}
		{include file="$tpl_dir./order-payment.tpl"}
{else}
<!-- <h1>Votre panier est à 0 Euro, impossible de poursuivre la commande !</h1> -->
{/if}
		<!-- END Payment -->
		
		{else}
			{capture name=path}{l s='Your shopping cart'}{/capture}
			<h2 class="page-heading">{l s='Your shopping cart'}</h2>
			{include file="$tpl_dir./errors.tpl"}
			<p class="alert alert-warning">{l s='Your shopping cart is empty.'}</p>
		{/if}
		
		
		
		
		
		</div>
		
		
		
		
		
{strip}
{addJsDef imgDir=$img_dir}
{addJsDef authenticationUrl=$link->getPageLink("authentication", true)|escape:'quotes':'UTF-8'}
{addJsDef orderOpcUrl=$link->getPageLink("order-opc", true)|escape:'quotes':'UTF-8'}
{addJsDef historyUrl=$link->getPageLink("history", true)|escape:'quotes':'UTF-8'}
{addJsDef guestTrackingUrl=$link->getPageLink("guest-tracking", true)|escape:'quotes':'UTF-8'}
{addJsDef addressUrl=$link->getPageLink("address", true, NULL, "back={$back_order_page}")|escape:'quotes':'UTF-8'}
{addJsDef orderProcess='order-opc'}
{addJsDef guestCheckoutEnabled=$PS_GUEST_CHECKOUT_ENABLED|intval}
{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
{addJsDef currencyRate=$currencyRate|floatval}
{addJsDef currencyFormat=$currencyFormat|intval}
{addJsDef currencyBlank=$currencyBlank|intval}
{addJsDef displayPrice=$priceDisplay}
{addJsDef taxEnabled=$use_taxes}
{addJsDef conditionEnabled=$conditions|intval}
{addJsDef vat_management=$vat_management|intval}
{addJsDef errorCarrier=$errorCarrier|@addcslashes:'\''}
{addJsDef errorTOS=$errorTOS|@addcslashes:'\''}
{addJsDef checkedCarrier=$checked|intval}
{addJsDef addresses=array()}
{addJsDef isVirtualCart=$isVirtualCart|intval}
{addJsDef isPaymentStep=$isPaymentStep|intval}
{addJsDefL name=txtWithTax}{l s='(tax incl.)' js=1}{/addJsDefL}
{addJsDefL name=txtWithoutTax}{l s='(tax excl.)' js=1}{/addJsDefL}
{addJsDefL name=txtHasBeenSelected}{l s='has been selected' js=1}{/addJsDefL}
{addJsDefL name=txtNoCarrierIsSelected}{l s='No carrier has been selected' js=1}{/addJsDefL}
{addJsDefL name=txtNoCarrierIsNeeded}{l s='No carrier is needed for this order' js=1}{/addJsDefL}
{addJsDefL name=txtConditionsIsNotNeeded}{l s='You do not need to accept the Terms of Service for this order.' js=1}{/addJsDefL}
{addJsDefL name=txtTOSIsAccepted}{l s='The service terms have been accepted' js=1}{/addJsDefL}
{addJsDefL name=txtTOSIsNotAccepted}{l s='The service terms have not been accepted' js=1}{/addJsDefL}
{addJsDefL name=txtThereis}{l s='There is' js=1}{/addJsDefL}
{addJsDefL name=txtErrors}{l s='Error(s)' js=1}{/addJsDefL}
{addJsDefL name=txtDeliveryAddress}{l s='Delivery address' js=1}{/addJsDefL}
{addJsDefL name=txtInvoiceAddress}{l s='Invoice address' js=1}{/addJsDefL}
{addJsDefL name=txtModifyMyAddress}{l s='Modify my address' js=1}{/addJsDefL}
{addJsDefL name=txtInstantCheckout}{l s='Instant checkout' js=1}{/addJsDefL}
{addJsDefL name=txtSelectAnAddressFirst}{l s='Please start by selecting an address.' js=1}{/addJsDefL}
{addJsDefL name=txtFree}{l s='Free' js=1}{/addJsDefL}

{capture}{if $back}&mod={$back|urlencode}{/if}{/capture}
{capture name=addressUrl}{$link->getPageLink('address', true, NULL, 'back='|cat:$back_order_page|cat:'?step=1'|cat:$smarty.capture.default)|escape:'quotes':'UTF-8'}{/capture}
{addJsDef addressUrl=$smarty.capture.addressUrl}
{capture}{'&multi-shipping=1'|urlencode}{/capture}
{addJsDef addressMultishippingUrl=$smarty.capture.addressUrl|cat:$smarty.capture.default}
{capture name=addressUrlAdd}{$smarty.capture.addressUrl|cat:'&id_address='}{/capture}
{addJsDef addressUrlAdd=$smarty.capture.addressUrlAdd}
{addJsDef opc=$opc|boolval}
{capture}<h3 class="page-subheading">{l s='Your billing address' js=1}</h3>{/capture}
{addJsDefL name=titleInvoice}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{capture}<h3 class="page-subheading">{l s='Your delivery address' js=1}</h3>{/capture}
{addJsDefL name=titleDelivery}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{capture}<a class="button button-small btn btn-default" href="{$smarty.capture.addressUrlAdd}" title="{l s='Update' js=1}"><span>{l s='Update' js=1}<i class="icon-chevron-right right"></i></span></a>{/capture}
{addJsDefL name=liUpdate}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{/strip}
{/if}


<div id="summary_etape2"></div>
<script>
	console.log('lang: {$lang_iso}');
	var lang_iso = '{$lang_iso}';
	
	//alert(lang_iso);
</script>
{literal}
<script>


$(document).ready(function(){
	 if ( $(window).width() > 268 ) {
	$('#etape2').hide();
	$('#summary_etape2').hide();
	
	$('.mg-menu li').remove();
	var cart_menu_fr ='<li><a href="#" id="cartstep_1" class="active">Récapitulatif de commande </a></li><li><a href="#" id="cartstep_2">Livraison & paiement</a></li><li><a href="https://www.magicflyer.com/fr/content/65-boutique">Retour à la boutique</a></li>';
	var cart_menu_en ='<li><a href="#" id="cartstep_1">Order content </a></li><li><a href="#" id="cartstep_2">Shipping & payment</a></li><li><a href="https://www.magicflyer.com/en/content/65-boutique">Back to the shop</a></li>';
	var cart_menu_es ='<li><a href="#" id="cartstep_1">Recapitulativo de pedido</a></li><li><a href="#" id="cartstep_2">Entrega y pago</a></li><li><a href="https://www.magicflyer.com/es/content/65-boutique">volver a la tienda</a></li>';

	if(lang_iso==='fr')$('.mg-menu').html(cart_menu_fr);
	else if(lang_iso==='es')$('.mg-menu').html(cart_menu_es);
	else $('.mg-menu').html(cart_menu_en);
	
	$('#cartstep_1').bind('click', function(event) {
        
        event.preventDefault();
		console.log('etape1');
		$('#etape1').show();
		$('#etape2').hide();
		//$(".title_summary_cart").appendTo("#cart_summary");
		//$(".cart_total_price").appendTo("#cart_summary");
		$('#summary_etape2').hide();
		$(this).addClass('active');
		$('#cartstep_2').removeClass('active');
		$('.button-continue').removeClass('button-continue-goto-payment');
    });	
	
	$('#cartstep_2, #cart_summary a.button-continue').bind('click', function(event) {
        
        event.preventDefault();
		console.log('etape2');
		$('#etape1').hide();
		$('#etape2').show();
		
		//$(".title_summary_cart").appendTo("#summary_etape2");
		//$(".cart_total_price").appendTo("#summary_etape2");
		//$('#summary_etape2').html($('#total_price').html());
		$('#summary_etape2').show();
		$(this).addClass('active');
		$('#cartstep_1').removeClass('active');
		
		$('.button-continue').addClass('button-continue-goto-payment');
			$('a.button-continue-goto-payment').bind('click', function(event) {
        
        
		$('html,body').animate({scrollTop: $('#opc_payment_methods').offset().top},'slow');

    });
		
    });


	
	
	
}});
</script>{/literal}