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
{if isset($olea_combinations_qty_prices) AND sizeof($olea_combinations_qty_prices)}
{foreach from=$olea_combinations_qty_prices item=olea_combination name=olea_combinations_qty_prices}
	{if $olea_combination.id_product_attribute == $oleaqty_defaultcombi}
	{if $olea_combination.prices|count > 1}
	<div><label>{l s='Quantity prices' mod='oleafoquantityprices'}</label>
	<table class="tabolea_table std table-product-discounts">
	<tr><th>{l s='Quantity' mod='oleafoquantityprices'}</th><th>{l s='Price' mod='oleafoquantityprices'}</th></tr>
	{foreach from=$olea_combination.prices key=olea_qty item=olea_price name=olea_qty_prices name=olea_qty_prices}
		{if $olea_price@first}
		{elseif $olea_price@last}
		<tr><td>{$olea_prevqty|intval}-{$olea_qty-1|intval}</td><td class="olea_nowrap">{convertPrice price=$olea_prevprice}</td></tr>
		<tr><td>{$olea_qty|intval} +</td><td class="olea_nowrap">{convertPrice price=$olea_price}</td></tr>
		{else}
		<tr><td>{$olea_prevqty|intval}-{$olea_qty-1|intval}</td><td class="olea_nowrap">{convertPrice price=$olea_prevprice}</td></tr>
		{/if}
		{assign  var="olea_prevqty" value=$olea_qty}
		{assign  var="olea_prevprice" value=$olea_price}
	{/foreach}
	</table>
	</div>
	{/if}
	{/if}
{/foreach}

{elseif isset($olea_product_qty_prices) AND sizeof($olea_product_qty_prices)}
<div><label>{l s='Quantity prices' mod='oleafoquantityprices'}</label>
<table class="tabolea_table std table-product-discounts">
<tr><th>{l s='Quantity' mod='oleafoquantityprices'}</th><th>{l s='Price' mod='oleafoquantityprices'}</th></tr>
{foreach from=$olea_product_qty_prices key=olea_qty item=olea_price name=olea_qty_prices name=olea_qty_prices}
		{if $olea_price@first}
		{elseif $olea_price@last}
		<tr><td>{$olea_prevqty|intval}-{$olea_qty-1|intval}</td><td class="olea_nowrap">{convertPrice price=$olea_prevprice}</td></tr>
		<tr><td>{$olea_qty|intval} +</td><td class="olea_nowrap">{convertPrice price=$olea_price}</td></tr>
		{else}
		<tr><td>{$olea_prevqty|intval}-{$olea_qty-1|intval}</td><td class="olea_nowrap">{convertPrice price=$olea_prevprice}</td></tr>
		{/if}
		{assign  var="olea_prevqty" value=$olea_qty}
		{assign  var="olea_prevprice" value=$olea_price}
{/foreach}
</table>
</div>
{/if}

