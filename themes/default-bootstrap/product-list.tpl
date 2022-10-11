{if isset($products) && $products}
{*define number of products per line in other page for desktop*}
{if $page_name !='index' && $page_name !='product'}
{assign var='nbItemsPerLine' value=6}
{assign var='nbItemsPerLineTablet' value=6}
{assign var='nbItemsPerLineMobile' value=6}
{else}
{assign var='nbItemsPerLine' value=4}
{assign var='nbItemsPerLineTablet' value=6}
{assign var='nbItemsPerLineMobile' value=6}
{/if}
{*define numbers of product per line in other page for tablet*}
{assign var='nbLi' value=$products|@count}
{math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
{math equation="nbLi/nbItemsPerLineTablet" nbLi=$nbLi nbItemsPerLineTablet=$nbItemsPerLineTablet assign=nbLinesTablet}
<!-- Products list -->
<ul{if isset($id) && $id} id="{$id}" {/if} class="product_list grid row{if isset($class) && $class} {$class}{/if}">
	{foreach from=$products item=product name=products}
	{math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
	{math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet
	assign=totModuloTablet}
	{math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile
	assign=totModuloMobile}
	{if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
	{if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
	{if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}
	<li class="ajax_block_product{if $page_name == 'index' || $page_name == 'product'} col-xs-6 col-sm-2 col-md-2{else} col-xs-6 col-sm-2 col-md-2{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLine == 0} last-in-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLine == 1} first-in-line{/if}{if $smarty.foreach.products.iteration > ($smarty.foreach.products.total - $totModulo)} last-line{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLineTablet == 0} last-item-of-tablet-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLineTablet == 1} first-item-of-tablet-line{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLineMobile == 0} last-item-of-mobile-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLineMobile == 1} first-item-of-mobile-line{/if}{if $smarty.foreach.products.iteration > ($smarty.foreach.products.total - $totModuloMobile)} last-mobile-line{/if}"
		{* personnalisation *} {if ($smarty.get.id_category==64 ) && ($product.id_product==228 ||
		$product.id_product==309 || $product.id_product==310) } style="display:none" {/if}>
		<div class="product-container" itemscope itemtype="http://schema.org/Product">
			<div class="left-block">
				<div class="product-image-container">
					{if $smarty.get.id_category == 64 || $smarty.get.id_category == 72}


					{assign var='productPersoImg' value=Product::getProductsPersoImg($product.id_product)}

					{assign var=imageIds value="$product.id_product-$productPersoImg"}


					<img class="replace-2x img-responsive" src="{$productPersoImg}" />



					{else}
					<img class="replace-2x img-responsive"
						src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}"
						alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}"
						title="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}"
						{if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}" {/if}
						itemprop="image" />
					{/if}
					{if isset($product.new) && $product.new == 1}
					<span class="new-label">{l s='New'}</span>
					{/if}
					{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price
					&& !$PS_CATALOG_MODE}
					<span class="sale-label">{l s='Sale!'}</span>
					{/if}
				</div>
				{hook h="displayProductDeliveryTime" product=$product}
				{hook h="displayProductPriceBlock" product=$product type="weight"}
			</div>
			<div class="right-block">
				<h2 itemprop="name">
					{if isset($product.pack_quantity) && $product.pack_quantity}{$product.pack_quantity|intval|cat:' x
					'}{/if}
					{$product.name|truncate:45:'...'|escape:'html':'UTF-8'}
				</h2>
				{hook h='displayProductListReviews' product=$product}
				{if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) ||
				(isset($product.available_for_order) && $product.available_for_order)))}
				<div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="content_price">
					{if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
					<meta itemprop="priceCurrency" content="{$currency->iso_code}" />


					{if isset($product.specific_prices) && $product.specific_prices &&
					isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
					{hook h="displayProductPriceBlock" product=$product type="old_price"}
					<span class="old-price product-price">
						{displayWtPrice p=$product.price_without_reduction}
					</span>
					{hook h="displayProductPriceBlock" id_product=$product.id_product type="old_price"}
					{if $product.specific_prices.reduction_type == 'percentage'}
					<span class="price-percent-reduction">-{$product.specific_prices.reduction * 100}%</span>
					{/if}
					{/if}
					{hook h="displayProductPriceBlock" product=$product type="price"}
					{hook h="displayProductPriceBlock" product=$product type="unit_price"}
					<span itemprop="price" class="price product-price">
						{if !$priceDisplay}{convertPrice price=$product.price
						*$product.minimal_quantity}{else}{convertPrice
						price=$product.price_tax_exc*$product.minimal_quantity}{/if}
					</span>

					{/if}
				</div>
				{/if}
				<div class="button-container">

					{if $smarty.get.id_category == 12 OR $smarty.get.id_category == 72}{* personnalisation *}

					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=personnalisation&id_produit={$product.id_product|intval}"
						rel="nofollow" title="{l s='Personnaliser'}" data-id-product="{$product.id_product|intval}"
						id="lien_{$product.id_product|intval}">
						<span>{l s='Personnaliser'}</span>
					</a>

					{elseif $smarty.get.id_category == 71}
					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=product&id_product={$product.id_product|intval}"
						rel="nofollow" title="{$product.name|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}">
						<span>{l s='Voir'}</span>
					</a>
					
					{elseif $product.id_product == 117 } 					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=product&id_product={$product.id_product|intval}"
						rel="nofollow" title="{$product.name|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}">
						<span>{l s='Voir'}</span>
					</a>
					{elseif $product.id_product == 173 } 					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=product&id_product={$product.id_product|intval}"
						rel="nofollow" title="{$product.name|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}">
						<span>{l s='Voir'}</span>
					</a>
					{elseif $product.id_product == 184 } 					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=product&id_product={$product.id_product|intval}"
						rel="nofollow" title="{$product.name|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}">
						<span>{l s='Voir'}</span>
					</a>
					{elseif $product.id_product == 312 } 					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=product&id_product={$product.id_product|intval}"
						rel="nofollow" title="{$product.name|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}">
						<span>{l s='Voir'}</span>
					</a>
					{elseif $product.id_product == 228 } 					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=product&id_product={$product.id_product|intval}"
						rel="nofollow" title="{$product.name|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}">
						<span>{l s='Voir'}</span>
					</a>
					{elseif $product.id_product == 309 } 					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=product&id_product={$product.id_product|intval}"
						rel="nofollow" title="{$product.name|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}">
						<span>{l s='Voir'}</span>
					</a>
					{elseif $product.id_product == 310 } 					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=product&id_product={$product.id_product|intval}"
						rel="nofollow" title="{$product.name|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}">
						<span>{l s='Voir'}</span>
					</a>
					{elseif $product.id_product == 174 }					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=product&id_product={$product.id_product|intval}"
						rel="nofollow" title="{$product.name|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}">
						<span>{l s='Voir'}</span>
					</a>
					{elseif $product.id_product == 183 }					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=product&id_product={$product.id_product|intval}"
						rel="nofollow" title="{$product.name|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}">
						<span>{l s='Voir'}</span>
					</a>
					{elseif $product.id_product == 311 }
					
					<a class="button btn btn-default "
						href="https://www.magicflyer.com/index.php?controller=product&id_product={$product.id_product|intval}"
						rel="nofollow" title="{$product.name|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}">
						<span>{l s='Voir'}</span>
					</a>
					
					
					
					{else}


					{if ($product.id_product_attribute == 0 || (isset($add_prod_display) && ($add_prod_display == 1)))
					&& $product.available_for_order && !isset($restricted_country_mode) && $product.customizable != 2 &&
					!$PS_CATALOG_MODE}
					{if (!isset($product.customization_required) || !$product.customization_required) &&
					($product.allow_oosp || $product.quantity > 0)}
					<div class="quantity_product_list">
						<!--

						{if $smarty.get.id_category == 13 || $smarty.get.controller == 'search'}
						<p>{l s='Quantity'}</p>
						{else}
						<p>{l s='Quantity_papillon'}</p>
						{/if}

-->


						{if $smarty.get.id_category == 63 }
						<!-- pap qty allowed : {$pap_qty_allowed} -->

						<input type="text" min="1" name="qty_{$product.id_product|intval}"
							id="quantity_to_cart_{$product.id_product|intval}" class="text qte2cart "
							value="{$pap_qty_allowed}" style="width:50px;" readonly>

						{else}

						
					
						<input type="text" min="1" name="qty_{$product.id_product|intval}"
							id="quantity_to_cart_{$product.id_product|intval}" class="text qte2cart"
							value="{$product.minimal_quantity}" style="width:50px;">
						<div class="cart_quantity_button clearfix">
							<a href="#" data-field-qty="quantity_to_cart_{$product.id_product|intval}"
								data-id="{$product.id_product|intval}" class="cart_quantity_up">
								<span><i class="icon-plus"></i></span></a>
							<a href="#" data-field-qty="quantity_to_cart_{$product.id_product|intval}"
								data-id="{$product.id_product|intval}" class="cart_quantity_down">
								<span><i class="icon-minus"></i></span></a>
						</div>
						{/if}

					</div>
					<script>

						function getNewPrice(_idp, _qty) {
							$.ajax({
								method: "GET",
								url: "http://www.magicflyer.com/ajax_price.php",
								data: { id_product: _idp, qty: _qty }
							})
								.done(function (r) {
									$('.product-price').html(r + ' €');
								});
						}


						$('.cart_quantity_up').off('click').on('click', function (e) {
							e.preventDefault();
							var qty_current_id = $(this).attr('data-field-qty');
							var current_id = $(this).attr('data-id');
							var current_qty = $('#' + qty_current_id).val();
							var new_qty = +current_qty + +1;
							$('#' + qty_current_id).val(new_qty);
							$('#lien_' + current_id).attr('href', $('#lien_' + current_id).attr('href') + '&qty=' + new_qty);

							getNewPrice(current_id, new_qty);

						});
						$('.cart_quantity_down').off('click').on('click', function (e) {
							e.preventDefault();
							var qty_current_id = $(this).attr('data-field-qty');
							var current_id = $(this).attr('data-id');
							var current_qty = $('#' + qty_current_id).val();
							$('#' + qty_current_id).val(current_qty - 1);
							$('#lien_' + current_id).attr('href', $('#lien_' + current_id).attr('href') + '&qty=' + (current_qty - 1));

							getNewPrice(current_id, (current_qty - 1));
						});

						$("#quantity_to_cart_{$product.id_product|intval}").on("change paste keyup", function () {
							//alert($(this).val()); 
							$('#lien_{$product.id_product|intval}').attr('href', 'https://www.magicflyer.com{$lang_iso}/panier?add=1&id_product={$product.id_product|intval}&qty=' + $(this).val());

							//console.log($('#lien_{$product.id_product|intval}').attr('data-id-product'));
						});
					</script>
					{capture}add=1&amp;qty={$product.minimal_quantity}&amp;id_product={$product.id_product|intval}{if
					isset($static_token)}&amp;token={$static_token}{/if}{/capture}

					{if $smarty.get.id_category == 63 }
					<script>
						$('.ajax_add_to_cart_button').off('click').on('click', function (e) {
							e.preventDefault();
							//$(this).hide();
							console.log('click elem');
							$(".button").each(function () {

								$(this).hide();

							});

						});

						function hideAllButtons() {
							$(".button").each(function () {

								$(this).hide();

							});
						}
					</script>
					<!--<a onclick="hideAllButtons();" class="button ajax_add_to_cart_button btn btn-default cart-button-callmodal" href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart'}" data-id-product="{$product.id_product|intval}" id="lien_{$product.id_product|intval}" data-minimal_quantity="{if isset($product.product_attribute_minimal_quantity) && $product.product_attribute_minimal_quantity > 1}{$product.product_attribute_minimal_quantity|intval}{else}{$product.minimal_quantity|intval}{/if}">
		<span>{l s='Add to cart'}</span>
		</a>-->
					{else}
					<a class="button ajax_add_to_cart_button btn btn-default cart-button-callmodal"
						href="{$link->getPageLink('cart', true, NULL, $smarty.capture.default, false)|escape:'html':'UTF-8'}"
						rel="nofollow" title="{l s='Add to cart'}" data-id-product="{$product.id_product|intval}"
						id="lien_{$product.id_product|intval}"
						data-minimal_quantity="{if isset($product.product_attribute_minimal_quantity) && $product.product_attribute_minimal_quantity > 1}{$product.product_attribute_minimal_quantity|intval}{else}{$product.minimal_quantity|intval}{/if}">
						<span>{l s='Add to cart'}</span>
					</a>
					{/if}




					{else}
					<span class="button ajax_add_to_cart_button btn btn-default disabled">
						<span>{l s='Add to cart'}</span>
					</span>
					{/if}
					{/if}

					{/if}{* fin exception categorie personnalisation *}
				</div>
				{if isset($product.color_list)}
				<div class="color-list-container">{$product.color_list}</div>
				{/if}
				<div class="product-flags">
					{if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) ||
					(isset($product.available_for_order) && $product.available_for_order)))}
					{if isset($product.online_only) && $product.online_only}
					<span class="online_only">{l s='Online only'}</span>
					{/if}
					{/if}
					{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price
					&& !$PS_CATALOG_MODE}
					{elseif isset($product.reduction) && $product.reduction && isset($product.show_price) &&
					$product.show_price && !$PS_CATALOG_MODE}
					<span class="discount">{l s='Reduced price!'}</span>
					{/if}
				</div>
			</div>
			{if $page_name != 'index'}
			<div class="functional-buttons clearfix">
				{hook h='displayProductListFunctionalButtons' product=$product}
				{if isset($comparator_max_item) && $comparator_max_item}
				<div class="compare">
					<a class="add_to_compare" href="{$product.link|escape:'html':'UTF-8'}"
						data-id-product="{$product.id_product}">{l s='Add to Compare'}</a>
				</div>
				{/if}
			</div>
			{/if}
		</div><!-- .product-container> -->
	</li>
	{/foreach}
	</ul>
	{addJsDefL name=min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
	{addJsDefL name=max_item}{l s='You cannot add more than %d product(s) to the product comparison'
	sprintf=$comparator_max_item js=1}{/addJsDefL}
	{addJsDef comparator_max_item=$comparator_max_item}
	{addJsDef comparedProductsIds=$compared_products}
	{/if}