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
<table class="tabolea_table std table-product-discounts">
{foreach from=$olea_combinations_qty_prices item=olea_combination name=olea_combinations_qty_prices}
<tr>
	<th>{l s='Minimal Qty' mod='oleafoquantityprices'}</th>
	<th>{l s='Price +tx' mod='oleafoquantityprices'}</th>
	<th>{l s='Price -tx' mod='oleafoquantityprices'}</th>
	<th>{l s='Unit Price -tx' mod='oleafoquantityprices'}</th>
</tr>

	{foreach from=$olea_combination.prices key=olea_combination_qty item=olea_combination_price name=olea_combinations_prices}
	<TR>
		<td>{$olea_combination_qty|intval}</td>
		<td>{convertPrice price=$olea_combination_price.ttc}</td>
		<td>{convertPrice price=$olea_combination_price.ht}</td>
		<td>{convertPrice price=$olea_combination_price.unit_ht}</td>
	</TR>
	{/foreach}

{/foreach}
</table>

{elseif isset($olea_product_qty_prices) AND sizeof($olea_product_qty_prices)}
<table class="tabolea_table std table-product-discounts">
<tr>{foreach from=$olea_product_qty_prices key=olea_qty item=olea_price name=olea_qty_prices name=olea_qty_prices}
		{if $olea_price@first}
			{if $olea_price@last}
			<th>{l s='Price' mod='oleafoquantityprices'}</th>  {* Case of only qty=1 in tab *}
			{/if}		
		{elseif $olea_price@last}
		<th>{$olea_prevqty|intval}-{$olea_qty|intval-1}</th>
		<th>{$olea_qty|intval} +</th>
		{else}
		<th>{$olea_prevqty|intval}-{$olea_qty|intval-1}</th>
		{/if}
		{assign  var="olea_prevqty" value=$olea_qty}
	{/foreach}
	{if ($olea_display_product_addtocart || $olea_foqty_isforajax)}
	<th  >{l s='To cart' mod='oleafoquantityprices'}</th>
	{/if}


</tr>
<tr>{foreach from=$olea_product_qty_prices key=olea_qty item=olea_price name=olea_qty_prices}
	<td>{convertPrice price=$olea_price}</td>
	{/foreach}
	{if $olea_display_product_addtocart || $olea_foqty_isforajax}
	<td class="oleacombitd" data-prices='[{foreach $olea_product_qty_prices as $pricesqty=>$qtyprice}{literal}{{/literal}"qty":{$pricesqty|intval},"price":{$qtyprice|floatval}{literal}}{/literal}{if $qtyprice@last}{else},{/if}{/foreach}]' >
		<span class="foqty_btn_updateqty_down" > - </span>
		<input type="text" size="2" value="1" class="oleacombiqty" />
		<span class="foqty_btn_updateqty_up" > + </span>
		<input type="hidden" value="0" class="oleacombiattrib" />
		{if $oleaqty_istpl16}
		<a class="button ajax_add_to_cart_button" href="#" title="{l s='Add to cart' mod='oleafoquantityprices'}" ><span><i class="icon-shopping-cart"></i></span></a>
		{elseif $oleaqty_post15}
		<a class="button_mini oleaqty_btnadd" href="#"><img src="{$img_dir|escape:'html':'UTF-8'}icon/pict_add_cart.png" /></a>
		{else}
		<a class=" " href="#"><img src="{$img_dir|escape:'html':'UTF-8'}icon/cart.gif" width=16 height=16 /></a>
		{/if}
		<p class="foqty_total_info">Total:<span class="foqty_total_price"></span></p>
	</td>
	{/if}
	
</tr>
</table>
{/if}

