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
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA

*}
<script type="text/javascript">
jQuery(function ($) {
    "use strict";
	var productCarousel = $(".blog-carousel"),
	container = $(".container");	
	if (productCarousel.length > 0) productCarousel.each(function () {
	var items = {$widget_setting.JBW_ITEM_SHOW},		
	    itemsDesktop = {$widget_setting.JBW_ITEM_SHOW},
	    itemsDesktopSmall = 2,
	    itemsTablet = 1,
	    itemsMobile = 1;	
	$(this).owlCarousel({
	    items: items,		
	    itemsDesktop: [1199, itemsDesktop],
	    itemsDesktopSmall: [991, itemsDesktopSmall],
	    itemsTablet: [768, itemsTablet],
	    itemsTabletSmall: false,
	    itemsMobile: [480, itemsMobile],
	    navigation: true,
	    pagination:  false,
	    rewindNav: {if $widget_setting.JBW_REWIND == '1'}true{else}false{/if},
	    navigationText: ["", ""],
	    scrollPerPage: {if $widget_setting.JBW_SCROLLPERPAGE == '1'}true{else}false{/if},
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
<div class="jmsblog-home-widget">
	<div class="block-title">
		<h3>
			<span>{l s='Latest Post' mod='jmsblogwidget'}</span>
		</h3>
	</div>
	{if $posts|@count gt 0}
	<div class="blog-carousel">	
	{foreach from=$posts item=post}		
		{assign var=params value=['post_id' => $post.post_id, 'category_slug' => $post.category_alias, 'slug' => $post.alias]}
		{assign var=catparams value=['category_id' => $post.category_id, 'slug' => $post.category_alias]}
		<div class="blog-item">
			{if $post.link_video && $widget_setting.JBW_SHOW_MEDIA}
			<div class="post-thumb">
				{$post.link_video}
			</div>
			{elseif $post.image && $widget_setting.JBW_SHOW_MEDIA}
			<div class="post-thumb">
				<a href="#"><img src="{$image_baseurl|escape:'html':'UTF-8'}thumb_{$post.image|escape:'html':'UTF-8'}" alt="{$post.title|escape:'htmlall':'UTF-8'}" class="img-responsive" /></a>			 		
			</div>
			{/if}	
			<h4 class="post-title"><a href="{jmsblog::getPageLink('jmsblog-post', $params)}">{$post.title|escape:'htmlall':'UTF-8'}</a></h4>
			<ul class="post-meta">
				{if $widget_setting.JBW_SHOW_CATEGORY}
				<li class="post-category"><span>{l s='Category' mod='jmsblogwidget'} :</span> <a href="{jmsblog::getPageLink('jmsblog-category', $catparams)}">{$post.category_name|escape:'html':'UTF-8'}</a></li>
				{/if}					
				<li class="post-created"><span>{l s='Created' mod='jmsblogwidget'} :</span> {$post.created|escape:'html':'UTF-8'|date_format:"%B %e, %Y"}</li>
				{if $widget_setting.JBW_SHOW_VIEWS}
				<li class="post-views"><span>{l s='Views' mod='jmsblogwidget'} :</span> {$post.views|escape:'html':'UTF-8'}</li>
				{/if}
				{if $widget_setting.JBW_SHOW_COMMENT}
				<li class="post-comments"><span>{l s='Comments' mod='jmsblogwidget'} :</span> {$post.comment_count|escape:'html':'UTF-8'}</li>
				{/if}	
			</ul>
			{if $widget_setting.JBW_SHOW_INTROTEXT}
			<div class="blog-intro">{$post.introtext}</div>	
			{/if}
			{if $widget_setting.JBW_SHOW_READMORE}
			<a class="blog-readmore">{l s='read more'} ...</a>	
			{/if}
		</div>	
	{/foreach}
	</div>
	{else}
		{l s='Dont have any items in this section' mod='jmsblogwidget'} !
	{/if}
</div>
