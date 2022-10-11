{**
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
 *}

{if count($csoc_product_selection) > 0}

<div id="csoc-container" class="{if isset($on_product_page) && $on_product_page}page-product-box{/if} {$csoc_prefix|strtolower|escape:'html':'UTF-8'}">
	{if $csoc_bloc_title}<h2 class="csoc">{$csoc_bloc_title|escape:'html':'UTF-8'}</h2>{/if}

	<div id="{$csoc_prefix|escape:'html':'UTF-8'}" class="clearfix">
		{foreach from=$csoc_product_selection item='cartProduct' name=cartProduct}
		<div class="csoc_product product-container">
			{if !empty($csoc_display["{$csoc_prefix}_DISPLAY_IMG"])}
			<div class="product-image-container">
				<a class="product_image clearfix" href="{$link->getProductLink($cartProduct.id_product, $cartProduct.link_rewrite, $cartProduct.category)}" title="{$cartProduct.name|escape:'html':'UTF-8'}">
					{if empty($cartProduct.link_rewrite)}
						<img src="{$link->getImageLink("default", $cartProduct.id_image, $imageSize)}" alt="{$cartProduct.name|escape:'html':'UTF-8'}" />
					{else}
						<img src="{$link->getImageLink($cartProduct.link_rewrite, $cartProduct.id_image, $imageSize)}" alt="{$cartProduct.name|escape:'html':'UTF-8'}" />
					{/if}
				</a>
			</div><!-- .product-image-container -->
			{/if}
			{if !empty($csoc_display["{$csoc_prefix}_DISPLAY_TITLE"])}
			<h3 class="csoc_product_title"><a href="{$link->getProductLink($cartProduct.id_product, $cartProduct.link_rewrite, $cartProduct.category)}" title="{$cartProduct.name|escape:'html':'UTF-8'}">{$cartProduct.name|truncate:23:'...'|escape:'htmlall':'UTF-8'}</a></h3>
			{/if}
			<p class="price_container">
				{if version_compare($smarty.const._PS_VERSION_, '1.4.0.0', '>=')}
					{if (!$PS_CATALOG_MODE AND ((isset($cartProduct.show_price) && $cartProduct.show_price) || (isset($cartProduct.available_for_order) && $cartProduct.available_for_order)))}
						{if isset($cartProduct.show_price) && !empty($csoc_display["{$csoc_prefix}_DISPLAY_PRICE"]) && $cartProduct.show_price && !isset($restricted_country_mode)}
							<span class="price" style="display: inline;">{if !$priceDisplay}{convertPrice price=$cartProduct.price}{else}{convertPrice price=$cartProduct.price_tax_exc}{/if}</span><br />
							{if $cartProduct.price_without_reduction > $cartProduct.price && isset($cartProduct.reduction) && $cartProduct.reduction}
								{if $priceDisplay >= 0 && $priceDisplay <= 2}
									<span style="text-decoration: line-through;">{convertPrice price=$cartProduct.price_without_reduction}</span>
									{if $tax_enabled && $display_tax_label == 1}
										{if $priceDisplay == 1}{l s='tax excl.' mod='pm_crosssellingoncart'}{else}{l s='tax incl.' mod='pm_crosssellingoncart'}{/if}
									{/if}
									<br />
								{/if}
							{/if}
							{if isset($cartProduct.on_sale) && $cartProduct.on_sale && isset($cartProduct.show_price) && $cartProduct.show_price && !$PS_CATALOG_MODE}
								<span class="on_sale">{l s='On sale!' mod='pm_crosssellingoncart'}</span><br/>
							{elseif isset($cartProduct.reduction) && $cartProduct.reduction && isset($cartProduct.show_price) && $cartProduct.show_price && !$PS_CATALOG_MODE}
								<span class="discount">{l s='Reduced price!' mod='pm_crosssellingoncart'}</span><br/>
							{/if}
							{if isset($cartProduct.online_only) && $cartProduct.online_only}
								<span class="online_only">{l s='Online only!' mod='pm_crosssellingoncart'}</span>
							{/if}
						{/if}
						{if isset($cartProduct.available_for_order) && !empty($csoc_display["{$csoc_prefix}_DISPLAY_AVAILABILITY"]) && $cartProduct.available_for_order && !isset($restricted_country_mode)}
							<span class="availability">
								{if ($cartProduct.allow_oosp || $cartProduct.quantity > 0)}
									{l s='Available' mod='pm_crosssellingoncart'}
								{elseif (isset($cartProduct.quantity_all_versions) && $cartProduct.quantity_all_versions > 0)}
									{l s='Product available with different options' mod='pm_crosssellingoncart'}
								{else}
									{l s='Out of stock' mod='pm_crosssellingoncart'}
								{/if}
							</span>
						{/if}
					{/if}
				{else}
					{if !empty($csoc_display["{$csoc_prefix}_DISPLAY_PRICE"])}
						<span class="price" style="display: inline;">{if !$priceDisplay}{convertPrice price=$cartProduct.price}{else}{convertPrice price=$cartProduct.price_tax_exc}{/if}</span>
						{if $cartProduct.price_without_reduction > $cartProduct.price && isset($cartProduct.reduction) && $cartProduct.reduction}
							{if $priceDisplay >= 0 && $priceDisplay <= 2}
								<span class="price" style="text-decoration: line-through;">{convertPrice price=$cartProduct.price_without_reduction}</span>
								{if $tax_enabled && $display_tax_label == 1}
									{if $priceDisplay == 1}{l s='tax excl.' mod='pm_crosssellingoncart'}{else}{l s='tax incl.' mod='pm_crosssellingoncart'}{/if}
								{/if}
								<br />
							{/if}
						{/if}
						{if $cartProduct.on_sale}
							<span class="on_sale">{l s='On sale!' mod='pm_crosssellingoncart'}</span><br/>
						{elseif ($cartProduct.reduction_price != 0 || $cartProduct.reduction_percent != 0) && ($cartProduct.reduction_from == $cartProduct.reduction_to OR ($smarty.now|date_format:'%Y-%m-%d %H:%M:%S' <= $cartProduct.reduction_to && $smarty.now|date_format:'%Y-%m-%d %H:%M:%S' >= $cartProduct.reduction_from))}
							<span class="discount">{l s='Reduced price!' mod='pm_crosssellingoncart'}</span><br/>
						{/if}
					{/if}
					{if !empty($csoc_display["{$csoc_prefix}_DISPLAY_AVAILABILITY"])}
						<span class="availability">{if ($cartProduct.allow_oosp OR $cartProduct.quantity > 0)}{l s='Available' mod='pm_crosssellingoncart'}{else}{l s='Out of stock' mod='pm_crosssellingoncart'}{/if}</span>
					{/if}
				{/if}
			</p>
			{if !empty($csoc_display["{$csoc_prefix}_DISPLAY_BUTTON"])}
				{if ($cartProduct.quantity > 0 OR $cartProduct.allow_oosp) AND $cartProduct.customizable != 2}
					{if version_compare($smarty.const._PS_VERSION_, '1.5.0.0', '>=')}
						{assign var='csoc_id_product' value=$cartProduct.id_product}
						<div class="addtocart_buttons_container"><a class="button ajax_add_to_cart_button exclusive" rel="ajax_id_product_{$cartProduct.id_product|intval}" href="{$link->getPageLink('cart',false, NULL, "add&amp;id_product=$csoc_id_product&amp;token=$csoc_static_token", false)}" title="{l s='Add to cart' mod='pm_crosssellingoncart'}"><span></span>{l s='Add to cart' mod='pm_crosssellingoncart'}</a></div>
					{else}
						<div class="addtocart_buttons_container"><a class="exclusive ajax_add_to_cart_button" rel="ajax_id_product_{$cartProduct.id_product|intval}" href="{$base_dir}cart.php?qty=1&amp;id_product={$cartProduct.id_product|intval}&amp;add&amp;token={$csoc_static_token|escape:'html':'UTF-8'}" title="{l s='Add to cart' mod='pm_crosssellingoncart'}">{l s='Add to cart' mod='pm_crosssellingoncart'}</a></div>
					{/if}
				{else}
					<span class="exclusive">{l s='Add to cart' mod='pm_crosssellingoncart'}</span>
				{/if}
			{/if}
		</div>
		{/foreach}
	</div>
	<div class="clear"></div>
</div>

<script>
setTimeout(function() {ldelim}
	if (typeof($csocjqPm) == 'undefined') $csocjqPm = $;
	$csocjqPm(document).ready(function() {ldelim}
		$csocjqPm("#{$csoc_prefix|escape:'html':'UTF-8'}").owlCarousel({ldelim}
			items : {if sizeof($csoc_product_selection) < $csoc_products_quantity}{$csoc_product_selection|sizeof|intval}{else}{$csoc_products_quantity|intval}{/if},
			itemsCustom : false,
			itemsDesktop : false,
			itemsDesktopSmall : false,
			itemsTablet : [768,{$csoc_products_quantity_tablet|intval}],
			itemsTabletSmall : false,
			itemsMobile : [479,{$csoc_products_quantity_mobile|intval}],
			slideSpeed : 200,
			paginationSpeed : 800,
			autoPlay : true,
			stopOnHover : true,
			goToFirstSpeed : 1000,
			navigation : false,
			navigationText : ["prev","next"],
			scrollPerPage : true,
			pagination : true,
			baseClass : "owl-carousel",
			theme : "owl-theme",
			mouseDraggable : false,
			responsiveBaseWidth: {if $csoc_prefix == 'PM_CSOC'}window{else}{literal}$csocjqPm('.nyroModalCont, .mfp-content'){/literal}{/if}
		{rdelim});
		if (typeof(modalAjaxCart) == 'undefined' && typeof(ajaxCart) != 'undefined' && typeof(pm_reloadCartOnAdd) != 'undefined' && typeof(pm_csocLoopInterval) == 'undefined') {ldelim}
			pm_csocLoopInterval = setInterval(function() {ldelim}
				pm_reloadCartOnAdd('{$csoc_order_page_link}');
			{rdelim}, 500);
		{rdelim}
		if ($('#product').size() > 0) {ldelim}
			$('div#csoc-container .ajax_add_to_cart_button').unbind('click');
			$(document).off('click', '.ajax_add_to_cart_button').off('click', 'div#csoc-container .ajax_add_to_cart_button').on('click', 'div#csoc-container .ajax_add_to_cart_button', function(e) {ldelim}
				e.preventDefault();
				var idProduct =  $(this).data('id-product');
				if (typeof(idProduct) == 'undefined')
					var idProduct =  $(this).attr('rel').replace('nofollow', '').replace('ajax_id_product_', '');
				if ($(this).attr('disabled') != 'disabled') {ldelim}
					ajaxCart.add(idProduct, null, false, this);

					var owl = $(".owl-carousel").data('owlCarousel');
					owl.removeItem(owl.currentItem);
					owl.reinit();

					if ($('#PM_CSOC .csoc_product').length <= 0) {ldelim}
						$('#csoc-container').remove();
					{rdelim}
				{rdelim}
				return false;
			{rdelim});
		{rdelim}
	{rdelim});
{rdelim}, 50);
</script>
{/if}