{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="slider-products-title"><h3><span>{l s='New Products' mod='jmsnewproducts'}</span></h3></div>      
	<div class="product-carousel">
    	{assign var='liHeight' value=250}
		{assign var='nbItemsPerLine' value=4}
		{assign var='nbLi' value=$products|@count}
		{math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
		{math equation="nbLines*liHeight" nbLines=$nbLines|ceil liHeight=$liHeight assign=ulHeight}
		{foreach from=$new_products item=product name=jmsnewproducts}
			<div class="item ajax_block_product">
				<div class="product-preview">
					<div class="preview"> 
						<a href="{$product.link|escape:'html':'UTF-8'}" class="preview-image product_img_link"><img class="img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="" /></a>
							{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
				            	<span class="label label-sale">{l s='Sale' mod='jmsnewproducts'}</span>
				            {elseif isset($product.new) && $product.new == 1}
				                <span class="label label-new">{l s='New' mod='jmsnewproducts'}</span>										
							{/if}
				            <a rel="{$product.link|escape:'html':'UTF-8'}" class="quick-view hidden-xs" title="{l s='Quick View' mod='jmsnewproducts'}"><span class="icon-eye"></span></a>                				
                			{if ($product.id_product_attribute == 0 OR (isset($add_prod_display) AND ($add_prod_display == 1))) AND $product.available_for_order AND !isset($restricted_country_mode) AND $product.minimal_quantity == 1 AND $product.customizable != 2 AND !$PS_CATALOG_MODE}
								{if ($product.quantity > 0 OR $product.allow_oosp)}
									<a class="exclusive ajax_add_to_cart_button cart-button" rel="ajax_id_product_{$product.id_product|escape:'html':'UTF-8'}" href="{$link->getPageLink('cart')|escape:'html':'UTF-8'}?qty=1&amp;id_product={$product.id_product|escape:'html':'UTF-8'}&amp;token={$static_token|escape:'html':'UTF-8'}&amp;add" title="{l s='Add to cart' mod='jmsnewproducts'}"><span class="icon-cart"></span></a>
								{else}
									<a href="#" class="disable cart-button" title="{l s='Add to cart' mod='jmsnewproducts'}"><span class="icon-cart"></span></a>
								{/if}																		
							{/if}
					</div>
					<h3 class="title"><a href="{$product.link|escape:'html':'UTF-8'}">{$product.name|truncate:25:'...'|escape:'html':'UTF-8'}</a></h3>
					{hook h='displayProductListReviews' product=$product}								      
					{if $product.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}<span class="price new">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}</span>{else}{/if}
											 
				</div>
			</div>
		{/foreach}
    </div>
</div>
