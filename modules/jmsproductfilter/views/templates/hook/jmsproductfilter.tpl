{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a retweet of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a retweet immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @retweetright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<script type="text/javascript">
jQuery(function ($) {
    "use strict";
	var productCarousel = $(".product-tab-carousel"),
	container = $(".container");
	if (productCarousel.length > 0) productCarousel.each(function () {
	var items = {$number_row|escape:''},
	    itemsDesktop = {$number_row|escape:''},
	    itemsDesktopSmall = 3,
	    itemsTablet = 2,
	    itemsMobile = 1;
	if ($("body").hasClass("noresponsive")) var items = {$number_row|escape:''},
	itemsDesktop = {$number_row|escape:''}, itemsDesktopSmall = {$number_row|escape:''}, itemsTablet = 2, itemsMobile = 1;
	else if ($(this).closest("section.col-md-8.col-lg-9").length > 0) var items = 3,
	itemsDesktop = 3, itemsDesktopSmall = 2, itemsTablet = 2, itemsMobile = 1;
	else if ($(this).closest("section.col-lg-9").length > 0) var items = 3,
	itemsDesktop = 3, itemsDesktopSmall = 2, itemsTablet = 2, itemsMobile = 1;
	else if ($(this).closest("section.col-sm-12.col-lg-6").length > 0) var items = 2,
	itemsDesktop = 2, itemsDesktopSmall = 3, itemsTablet = 2, itemsMobile = 1;
	else if ($(this).closest("section.col-lg-6").length > 0) var items = 2,
	itemsDesktop = 2, itemsDesktopSmall = 2, itemsTablet = 2, itemsMobile = 1;
	else if ($(this).closest("section.col-sm-12.col-lg-3").length > 0) var items = 1,
	itemsDesktop = 1, itemsDesktopSmall = 3, itemsTablet = 2, itemsMobile = 1;
	else if ($(this).closest("section.col-lg-3").length > 0) var items = 1,
	itemsDesktop = 1, itemsDesktopSmall = 2, itemsTablet = 2, itemsMobile = 1;
	$(this).owlCarousel({
	    items: items,
	    itemsDesktop: [1199, itemsDesktop],
	    itemsDesktopSmall: [991, itemsDesktopSmall],
	    itemsTablet: [768, itemsTablet],
	    itemsTabletSmall: false,
	    itemsMobile: [480, itemsMobile],
		autoPlay: false,
	    navigation: true,
	    pagination: false,
	    rewindNav: true,
	    navigationText: ["", ""],
	    scrollPerPage: false,
	    slideSpeed: 500,		
	    beforeInit: function rtlSwapItems(el) {
	        if ($("body").hasClass("rtl")) el.children().each(function (i, e) {
	            $(e).parent().prepend($(e))
	        })
	    },
	    afterInit: function afterInit(el) {
	        if ($("body").hasClass("rtl")) this.jumpTo(1000)
	    }
	})
	});
});
</script>
<div class="block-title">
	<h3>
		<span>{l s='Featured Products' mod="jmsproductfilter"}</span>
	</h3>
</div>
<section class="tab-section row">
    <div class="jms-tab">
		<ul class="nav nav-tabs" role="tablist">
			{$cf = 0}
			{if $product_filter.JMS_PF_FEATURED eq '1'}
				<li class="active"><a class="button" data-toggle="tab" href="#featured">{l s='Featured' mod="jmsproductfilter"}</a></li>
				{$cf = $cf + 1}
			{/if}	
			{if $product_filter.JMS_PF_NEW eq '1'}
				<li {if $cf eq 0}class="active"{/if}><a class="button" data-toggle="tab" href="#latest">{l s='Latest' mod="jmsproductfilter"}</a></li>
				{$cf = $cf + 1}
			{/if}		
			{if $product_filter.JMS_PF_TOPSELLER eq '1'}
				<li {if $cf eq 0}class="active"{/if}><a class="button" data-toggle="tab" href="#topseller">{l s='Bestseller' mod="jmsproductfilter"}</a></li>
				{$cf = $cf + 1}
			{/if}		
			{if $product_filter.JMS_PF_SPECIAL eq '1'}
				<li {if $cf eq 0}class="active"{/if}><a class="button" data-toggle="tab" href="#special">{l s='Special' mod="jmsproductfilter"}</a></li>
				{$cf = $cf + 1}
			{/if}			
			{if $product_filter.JMS_PF_ONSALE eq '1'}
				<li {if $cf eq 0}class="active"{/if}><a class="button" data-toggle="tab" href="#onsale">{l s='On Sale' mod="jmsproductfilter"}</a></li>
				{$cf = $cf + 1}
			{/if}			
		</ul>
    </div>		
	<div class="tab-content">
		{$cf = 0}
		{if $product_filter.JMS_PF_FEATURED eq '1'}
		 <div role="tabpanel" class="tab-pane active" id="featured">
			<div class="product-tab-carousel">
				{foreach from = $result_featured_products item = col_products}
					<div class="item" itemscope itemtype="http://schema.org/Product">
						{foreach from = $col_products item = product}
							<div class="product-preview {if $phover == 'image_swap'}image_swap{/if}">
								<div class="preview"> 
									<a href="{$product.link|escape:'html'}" title="{$product.name|escape:html:'UTF-8'}" class="product_image preview-image image-rollover" data-id-product="{$product.id_product}" itemprop="url">
									<img class="img-responsive product-img1" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html'}" alt="" itemprop="image" /></a>									
									{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
										<div class="label-wrapper">
											<div class="label label-sale">{l s='Sale' mod='jmsproductfilter'}</div>
										</div>
									{elseif isset($product.new) && $product.new == 1}
										<div class="label-wrapper wrapper-new">
											<div class="label label-new">{l s='New' mod='jmsproductfilter'}</div>
										</div>								
									{/if}		
									<div class="compare-cart-wish">            				
										{if ($product.id_product_attribute == 0 OR (isset($add_prod_display) AND ($add_prod_display == 1))) AND $product.available_for_order AND !isset($restricted_country_mode) AND $product.minimal_quantity == 1 AND $product.customizable != 2 AND !$PS_CATALOG_MODE}
											{if ($product.quantity > 0 OR $product.allow_oosp)}
											<a class="ajax_add_to_cart_button product-btn cart-button" data-id-product="{$product.id_product}" href="{$link->getPageLink('cart')|escape:'html'}?qty=1&amp;id_product={$product.id_product}&amp;token={$static_token}&amp;add" title="{l s='Add to cart' mod='jmsproductfilter'}"><i class="fa fa-check"></i><span><i class="fa fa-shopping-cart"></i></span></a>
											{else}
											<a href="#" class="disable product-btn cart-button" title="{l s='Add to cart' mod='jmsproductfilter'}"><span><i class="fa fa-shopping-cart"></i></span></a>
											{/if}																		
										{/if}
										<a class="addToWishlist product-btn" href="#" onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product}', false, 1); return false;" data-id-product="{$product.id_product}" title="{l s='Add to Wishlist'}"><i class="fa fa-heart"></i></a>
										{if isset($comparator_max_item) && $comparator_max_item}						
											<a class="add_to_compare product-btn" href="{$product.link|escape:'html':'UTF-8'}" data-id-product="{$product.id_product}" title="{l s='Add to Compare'}"><i class="fa fa-check"></i><i class="fa fa-files-o"></i></a>						
										{/if}
										<a rel="{$product.link|escape:'html'}" class="quick-view product-btn hidden-xs" title="{l s='Quick View' mod="jmsproductfilter"}"><i class="fa fa-external-link"></i></a>   
									</div>
								</div>
								<div class="product-info">
									<h3 class="title"><a itemprop="url" href="{$product.link|escape:'html'}">{$product.name|truncate:25:'...'|escape:'html':'UTF-8'}</a></h3>	
									{hook h='displayProductListReviews' product=$product}
									<div class="content_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
									{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
									{hook h="displayProductPriceBlock" product=$product type="old_price"}								
									<span class="old price">
										{displayWtPrice p=$product.price_without_reduction}
									</span>	
									{/if}
									{if $product.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}<span class="price new" itemprop="price">{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}</span>{/if}
									<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
								</div>	
								</div>													 
							</div>
						{/foreach}
					</div>
				{/foreach}
			</div>
		 </div>
		 {$cf = $cf + 1}
		 {/if}
		 {if $product_filter.JMS_PF_NEW eq '1'}
		 <div role="tabpanel" class="tab-pane {if $cf eq 0}active{/if}" id="latest">
			<div class="product-tab-carousel">
			{foreach from = $result_new_products item = col_products}
					<div class="item" itemscope itemtype="http://schema.org/Product">
						{foreach from = $col_products item = product}
						<div class="product-preview {if $phover=='image_swap'}image_swap{/if}">
							<div class="preview"> 
								<a href="{$product.link|escape:'html'}" class="preview-image product_img_link image-rollover" data-id-product="{$product.id_product}" itemprop="url">
									<img class="img-responsive product-img1" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html'}" alt="" itemprop="image" />
								</a>
								{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
									<span class="label label-sale">{l s='Sale' mod='jmsnewproducts'}</span>
								{elseif isset($product.new) && $product.new == 1}
									<span class="label label-new">{l s='New' mod='jmsnewproducts'}</span>										
								{/if}
								<div class="product-buttons">
									<a data-link="{$product.link|escape:'html':'UTF-8'}" class="quick-view product-btn hidden-xs" title="{l s='Quick View' mod='jmsnewproducts'}">{l s='Quick View' mod='jmsnewproducts'}</a>					
									{if ($product.id_product_attribute == 0 OR (isset($add_prod_display) AND ($add_prod_display == 1))) AND $product.available_for_order AND !isset($restricted_country_mode) AND $product.minimal_quantity == 1 AND $product.customizable != 2 AND !$PS_CATALOG_MODE}
										{if ($product.quantity > 0 OR $product.allow_oosp)}
											<a class="exclusive ajax_add_to_cart_button cart-button product-btn" data-id-product="{$product.id_product}" href="{$link->getPageLink('cart')|escape:'html':'UTF-8'}?qty=1&amp;id_product={$product.id_product}&amp;token={$static_token}&amp;add" title="{l s='Add to cart' mod='jmsnewproducts'}">
												<i class="icon_bag_alt"></i>
												<i class="icon_loading"></i>
												<i class="icon_check"></i>
											</a>
										{else}
											<a href="#" class="disable cart-button product-btn" title="{l s='Out of Stock' mod='jmsnewproducts'}"><i class="icon_bag_alt"></i></a>
										{/if}																		
									{/if}					
									<a class="addToWishlist product-btn" href="#" onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product|escape:'html'}', false, 1); return false;" data-id-product="{$product.id_product|escape:'html'}" title="{l s='Add to Wishlist' mod='jmsnewproducts'}"><i class="icon_heart_alt"></i></a>
								</div>		
							</div>
							<div class="product-info">
								<h3 class="title" itemprop="name">
									<a href="{$product.link|escape:'html'}" itemprop="url">{$product.name|truncate:25:'...'|escape:'html':'UTF-8'}</a>
								</h3>							
								{hook h='displayProductListReviews' product=$product}		
								<div class="content_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">	
									{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
									{hook h="displayProductPriceBlock" product=$product type="old_price"}								
									<span class="old price">
										{displayWtPrice p=$product.price_without_reduction}
									</span>		
									{/if}
									{if $product.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
										{hook h="displayProductPriceBlock" product=$product type="before_price"}
										<span class="price new" itemprop="price">
										{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
										</span>
									{/if}
									<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
									{hook h="displayProductPriceBlock" product=$product type="price"}
									{hook h="displayProductPriceBlock" product=$product type="unit_price"}
								</div>
							</div>						 
						</div>
					{/foreach}
				</div>
				{/foreach}
			</div>
		</div>
		{$cf = $cf + 1}
		{/if}
		{if $product_filter.JMS_PF_TOPSELLER eq '1'}
		 <div role="tabpanel" class="tab-pane {if $cf eq 0}active{/if}" id="topseller">
			<div class="product-tab-carousel">
				{foreach from = $result_topseller_products item = col_products}
					<div class="item" itemscope itemtype="http://schema.org/Product">
						{foreach from = $col_products item = product}
							<div class="product-preview {if $phover=='image_swap'}image_swap{/if}">
								<div class="preview"> 
									<a href="{$product.link|escape:'html'}" class="preview-image product_img_link image-rollover" data-id-product="{$product.id_product}" itemprop="url">
										<img class="img-responsive product-img1" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html'}" alt="" itemprop="image" />
									</a>
									{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
										<span class="label label-sale">{l s='Sale' mod='jmsnewproducts'}</span>
									{elseif isset($product.new) && $product.new == 1}
										<span class="label label-new">{l s='New' mod='jmsnewproducts'}</span>										
									{/if}
									<div class="product-buttons">
										<a data-link="{$product.link|escape:'html':'UTF-8'}" class="quick-view product-btn hidden-xs" title="{l s='Quick View' mod='jmsnewproducts'}">{l s='Quick View' mod='jmsnewproducts'}</a>					
										{if ($product.id_product_attribute == 0 OR (isset($add_prod_display) AND ($add_prod_display == 1))) AND $product.available_for_order AND !isset($restricted_country_mode) AND $product.minimal_quantity == 1 AND $product.customizable != 2 AND !$PS_CATALOG_MODE}
											{if ($product.quantity > 0 OR $product.allow_oosp)}
												<a class="exclusive ajax_add_to_cart_button cart-button product-btn" data-id-product="{$product.id_product}" href="{$link->getPageLink('cart')|escape:'html':'UTF-8'}?qty=1&amp;id_product={$product.id_product}&amp;token={$static_token}&amp;add" title="{l s='Add to cart' mod='jmsnewproducts'}">
													<i class="icon_bag_alt"></i>
													<i class="icon_loading"></i>
													<i class="icon_check"></i>
												</a>
											{else}
												<a href="#" class="disable cart-button product-btn" title="{l s='Out of Stock' mod='jmsnewproducts'}"><i class="icon_bag_alt"></i></a>
											{/if}																		
										{/if}					
										<a class="addToWishlist product-btn" href="#" onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product|escape:'html'}', false, 1); return false;" data-id-product="{$product.id_product|escape:'html'}" title="{l s='Add to Wishlist' mod='jmsnewproducts'}"><i class="icon_heart_alt"></i></a>
									</div>		
								</div>
								<div class="product-info">
									<h3 class="title" itemprop="name">
										<a href="{$product.link|escape:'html'}" itemprop="url">{$product.name|truncate:25:'...'|escape:'html':'UTF-8'}</a>
									</h3>							
									{hook h='displayProductListReviews' product=$product}		
									<div class="content_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">	
										{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
										{hook h="displayProductPriceBlock" product=$product type="old_price"}								
										<span class="old price">
											{displayWtPrice p=$product.price_without_reduction}
										</span>		
										{/if}
										{if $product.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
											{hook h="displayProductPriceBlock" product=$product type="before_price"}
											<span class="price new" itemprop="price">
											{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
											</span>
										{/if}
										<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
										{hook h="displayProductPriceBlock" product=$product type="price"}
										{hook h="displayProductPriceBlock" product=$product type="unit_price"}
									</div>
								</div>						 
							</div>
						{/foreach}
					</div>
				{/foreach}
			</div>
		 </div>
		{$cf = $cf + 1}
		{/if}
		{if $product_filter.JMS_PF_SPECIAL eq '1'}
		<div role="tabpanel" class="tab-pane {if $cf eq 0}active{/if}" id="special">
			<div class="product-tab-carousel">
				{foreach from = $result_special_products item = col_products}
					<div class="item" itemscope itemtype="http://schema.org/Product">
						{foreach from = $col_products item = product}
						<div class="product-preview {if $phover=='image_swap'}image_swap{/if}">
							<div class="preview"> 
								<a href="{$product.link|escape:'html'}" class="preview-image product_img_link image-rollover" data-id-product="{$product.id_product}" itemprop="url">
									<img class="img-responsive product-img1" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html'}" alt="" itemprop="image" />
								</a>
								{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
									<span class="label label-sale">{l s='Sale' mod='jmsnewproducts'}</span>
								{elseif isset($product.new) && $product.new == 1}
									<span class="label label-new">{l s='New' mod='jmsnewproducts'}</span>										
								{/if}
								<div class="product-buttons">
									<a data-link="{$product.link|escape:'html':'UTF-8'}" class="quick-view product-btn hidden-xs" title="{l s='Quick View' mod='jmsnewproducts'}">{l s='Quick View' mod='jmsnewproducts'}</a>					
									{if ($product.id_product_attribute == 0 OR (isset($add_prod_display) AND ($add_prod_display == 1))) AND $product.available_for_order AND !isset($restricted_country_mode) AND $product.minimal_quantity == 1 AND $product.customizable != 2 AND !$PS_CATALOG_MODE}
										{if ($product.quantity > 0 OR $product.allow_oosp)}
											<a class="exclusive ajax_add_to_cart_button cart-button product-btn" data-id-product="{$product.id_product}" href="{$link->getPageLink('cart')|escape:'html':'UTF-8'}?qty=1&amp;id_product={$product.id_product}&amp;token={$static_token}&amp;add" title="{l s='Add to cart' mod='jmsnewproducts'}">
												<i class="icon_bag_alt"></i>
												<i class="icon_loading"></i>
												<i class="icon_check"></i>
											</a>
										{else}
											<a href="#" class="disable cart-button product-btn" title="{l s='Out of Stock' mod='jmsnewproducts'}"><i class="icon_bag_alt"></i></a>
										{/if}																		
									{/if}					
									<a class="addToWishlist product-btn" href="#" onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product|escape:'html'}', false, 1); return false;" data-id-product="{$product.id_product|escape:'html'}" title="{l s='Add to Wishlist' mod='jmsnewproducts'}"><i class="icon_heart_alt"></i></a>
								</div>		
							</div>
							<div class="product-info">
								<h3 class="title" itemprop="name">
									<a href="{$product.link|escape:'html'}" itemprop="url">{$product.name|truncate:25:'...'|escape:'html':'UTF-8'}</a>
								</h3>							
								{hook h='displayProductListReviews' product=$product}		
								<div class="content_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">	
									{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
									{hook h="displayProductPriceBlock" product=$product type="old_price"}								
									<span class="old price">
										{displayWtPrice p=$product.price_without_reduction}
									</span>		
									{/if}
									{if $product.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
										{hook h="displayProductPriceBlock" product=$product type="before_price"}
										<span class="price new" itemprop="price">
										{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
										</span>
									{/if}
									<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
									{hook h="displayProductPriceBlock" product=$product type="price"}
									{hook h="displayProductPriceBlock" product=$product type="unit_price"}
								</div>
							</div>						 
						</div>
					{/foreach}
				</div>
				{/foreach}
			</div>
		</div>
		{$cf = $cf + 1}
		{/if}
		{if $product_filter.JMS_PF_ONSALE eq '1'}	
		<div role="tabpanel" class="tab-pane {if $cf eq 0}active{/if}" id="onsale">
			<div class="product-tab-carousel">
				{foreach from = $result_onsale_products item = col_products}
					<div class="item" itemscope itemtype="http://schema.org/Product">
						{foreach from = $col_products item = product}
						<div class="product-preview {if $phover=='image_swap'}image_swap{/if}">
							<div class="preview"> 
								<a href="{$product.link|escape:'html'}" class="preview-image product_img_link image-rollover" data-id-product="{$product.id_product}" itemprop="url">
									<img class="img-responsive product-img1" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html'}" alt="" itemprop="image" />
								</a>
								{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
									<span class="label label-sale">{l s='Sale' mod='jmsnewproducts'}</span>
								{elseif isset($product.new) && $product.new == 1}
									<span class="label label-new">{l s='New' mod='jmsnewproducts'}</span>										
								{/if}
								<div class="product-buttons">
									<a data-link="{$product.link|escape:'html':'UTF-8'}" class="quick-view product-btn hidden-xs" title="{l s='Quick View' mod='jmsnewproducts'}">{l s='Quick View' mod='jmsnewproducts'}</a>					
									{if ($product.id_product_attribute == 0 OR (isset($add_prod_display) AND ($add_prod_display == 1))) AND $product.available_for_order AND !isset($restricted_country_mode) AND $product.minimal_quantity == 1 AND $product.customizable != 2 AND !$PS_CATALOG_MODE}
										{if ($product.quantity > 0 OR $product.allow_oosp)}
											<a class="exclusive ajax_add_to_cart_button cart-button product-btn" data-id-product="{$product.id_product}" href="{$link->getPageLink('cart')|escape:'html':'UTF-8'}?qty=1&amp;id_product={$product.id_product}&amp;token={$static_token}&amp;add" title="{l s='Add to cart' mod='jmsnewproducts'}">
												<i class="icon_bag_alt"></i>
												<i class="icon_loading"></i>
												<i class="icon_check"></i>
											</a>
										{else}
											<a href="#" class="disable cart-button product-btn" title="{l s='Out of Stock' mod='jmsnewproducts'}"><i class="icon_bag_alt"></i></a>
										{/if}																		
									{/if}					
									<a class="addToWishlist product-btn" href="#" onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product|escape:'html'}', false, 1); return false;" data-id-product="{$product.id_product|escape:'html'}" title="{l s='Add to Wishlist' mod='jmsnewproducts'}"><i class="icon_heart_alt"></i></a>
								</div>		
							</div>
							<div class="product-info">
								<h3 class="title" itemprop="name">
									<a href="{$product.link|escape:'html'}" itemprop="url">{$product.name|truncate:25:'...'|escape:'html':'UTF-8'}</a>
								</h3>							
								{hook h='displayProductListReviews' product=$product}		
								<div class="content_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">	
									{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
									{hook h="displayProductPriceBlock" product=$product type="old_price"}								
									<span class="old price">
										{displayWtPrice p=$product.price_without_reduction}
									</span>		
									{/if}
									{if $product.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
										{hook h="displayProductPriceBlock" product=$product type="before_price"}
										<span class="price new" itemprop="price">
										{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
										</span>
									{/if}
									<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
									{hook h="displayProductPriceBlock" product=$product type="price"}
									{hook h="displayProductPriceBlock" product=$product type="unit_price"}
								</div>
							</div>						 
						</div>
					{/foreach}
				</div>
				{/foreach}
			</div>
		</div>
		{$cf = $cf + 1}
		{/if}		
	</div>
</section>