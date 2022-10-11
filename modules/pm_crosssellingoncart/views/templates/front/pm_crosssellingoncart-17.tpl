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
	{if isset($on_product_page) && $on_product_page}
		<div id="csoc-container" class="page-product-box product-accessories clearfix {$csoc_prefix|strtolower}">
			{if $csoc_bloc_title}
				<h3 class="h5 text-uppercase">{$csoc_bloc_title}</h3>
			{/if}

			<div id="{$csoc_prefix}" class="bx-wrapper block products_block csoc-block products clearfix">
				{foreach from=$csoc_product_selection item='cartProduct' name=cartProduct}
			      {block name='product_miniature'}
					{include 'module:pm_crosssellingoncart/views/templates/front/product.tpl' product=$cartProduct}
			      {/block}
			    {/foreach}
			</div>
		</div>
	{else}
		<div id="csoc-container" class="product-accessories {$csoc_prefix|strtolower}">
			{if $csoc_bloc_title}
			<div class="card-block">
				<h1 class="step-title h3">{$csoc_bloc_title}</h1>
			</div>
			{/if}
			<div id="{$csoc_prefix}" class="clearfix">
				{foreach from=$csoc_product_selection item='cartProduct' name=cartProduct}
			      {block name='product_miniature'}
					{include 'module:pm_crosssellingoncart/views/templates/front/product.tpl' product=$cartProduct}
			      {/block}
			    {/foreach}
			</div>
		</div>
	{/if}
{/if}