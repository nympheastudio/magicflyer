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

<table class="{if $oleaqty_istpl16}table-product-discounts{else}tabolea_table{/if} std {if $oleafoqty_multi_of_minimal}byqtymulti{else}byqty{/if}" id="oleaqty_pricestable">
{foreach from=$olea_combinations_qty_prices item=olea_combination name=olea_combinations_qty_prices}
{if $smarty.foreach.olea_combinations_qty_prices.first}
<tr>{if $olea_display_combination_images}<th>&nbsp;</th>{/if}
	{if $olea_display_combination_reference}<th>{l s='reference' mod='oleafoquantityprices'}</th>{/if}
	{if $olea_display_combination_ean13}<th>{l s='ean13' mod='oleafoquantityprices'}</th>{/if}
	{foreach from=$olea_combination.attributes key=olea_combination_attribute_name item=olea_combination_attribute name=olea_combinations_attributes}
	<th>{$olea_combination_attribute.group|escape:'html':'UTF-8'}</th>
	{/foreach}
	{foreach from=$olea_combination.features key=olea_combination_feature_name item=olea_combination_feature name=olea_combinations_features}
	<th>{$olea_combination_feature_name|escape:'html':'UTF-8'}</th>
	{/foreach}

	{foreach from=$olea_combination.prices key=olea_combination_qty item=olea_combination_price name=olea_combinations_prices}
		{if $olea_combination_price@first}
			{if $olea_combination_price@last}
			<th>{l s='Price' mod='oleafoquantityprices'}</th>  {* Case of only qty=1 in tab *}
			{/if}		
		{elseif $olea_combination_price@last}
		<th>{$olea_prevqty|intval}-{$olea_combination_qty|intval-1}</th>
		<th>{$olea_combination_qty|intval} +</th>
		{else}
		<th>{$olea_prevqty|intval}-{$olea_combination_qty|intval-1}</th>
		{/if}
		{assign  var="olea_prevqty" value=$olea_combination_qty}
	{/foreach}
	{if $olea_display_combination_addtocart}
	<th  >{l s='To cart' mod='oleafoquantityprices'}</th>
	{/if}
</tr>
{/if}

<tr class="oleafoqty_combinationrow" data-id_combination="{$olea_combination.id_product_attribute|intval}" data-minimal_quantity="{$olea_combination.minimal_quantity|intval}">{if $olea_display_combination_images}
	<td>{if $olea_combination.id_image >0}
		{assign var=oleaImageIds value="`$olea_id_product`-`$olea_combination.id_image`"}
		{if $oleaqty_istpl16}{assign var=oleaImageFormat value="cart_default"}{else}{assign var=oleaImageFormat value="medium_default"}{/if}
		<div style="height:45px;width:45px;">
		<img class="img-responsive" src="{$link->getImageLink('miniature', $oleaImageIds, $oleaImageFormat)|escape:'html':'UTF-8'}"  height="100%" width="100%" />
		</div>
		{else}&nbsp;
		{/if}
	</td>
	{/if}
	{if $olea_display_combination_reference}<td class="tabolea_reference" >{$olea_combination.reference|escape:'html':'UTF-8'}</td>{/if}
	{if $olea_display_combination_ean13}<td class="tabolea_ean13" >{$olea_combination.ean13|escape:'html':'UTF-8'}</td>{/if}
	{foreach from=$olea_combination.attributes item=olea_combination_attribute name=olea_combinations_attributes}
	<td class="tabolea_attribute">
		{if !$olea_combination_attribute.is_color_group}
			{$olea_combination_attribute.name|escape:'html':'UTF-8'}
		{else}
			{assign var='img_color_exists' value=file_exists($img_col_dir|cat:$olea_combination_attribute.id_attribute|cat:'.jpg')}
			<div  class="color_pick foqty_colorinfo"{if !$img_color_exists} style="background:{$olea_combination_attribute.color|escape:'html':'UTF-8'};"{/if} title="{$olea_combination_attribute.name|escape:'html':'UTF-8'}">
				{if $img_color_exists}
					<img src="{$img_col_dir|escape:'html':'UTF-8'}{$olea_combination_attribute.id_attribute|intval}.jpg" alt="{$olea_combination_attribute.name|escape:'html':'UTF-8'}" title="{$olea_combination_attribute.name|escape:'html':'UTF-8'}" width="100%" height="100%" />
				{/if}
			</div>
		{/if}
	</td>
	{/foreach}
	{foreach from=$olea_combination.features item=olea_combination_feature name=olea_combinations_features}
	<td class="tabolea_feature">{$olea_combination_feature|escape:'html':'UTF-8'}</td>
	{/foreach}
	{foreach from=$olea_combination.prices key=olea_combination_qty item=olea_combination_price name=olea_combinations_prices}
	<td class="tabolea_price">
			{if (bool)$olea_display_group_price}<p class="olea_price olea_group_price">{convertPrice price=$olea_combination.prices_of_group.$olea_combination_qty}</p>{/if}
			<p class="olea_price">{convertPrice price=$olea_combination_price}</p>
	</td>
	{/foreach}
	{if ($olea_display_combination_addtocart || $olea_foqty_isforajax)}
	<td class="oleacombitd" data-prices='[{foreach $olea_combination.prices as $pricesqty=>$qtyprice}{literal}{{/literal}"qty":{$pricesqty|intval},"price":{$qtyprice|floatval}{literal}}{/literal}{if $qtyprice@last}{else},{/if}{/foreach}]' >
		<div class="oleafoqty_divqty">
		<input type="hidden" value="{$olea_combination.id_product_attribute|intval}" class="oleacombiattrib" />
		<span class="foqty_btn_updateqty_down" > - </span>
		<input type="text" size="2" value="{$olea_combination.minimal_quantity|intval}" class="oleacombiqty" />
		<span class="foqty_btn_updateqty_up" > + </span>
		</div>
		<div class="oleafoqty_divqtymulti">
		{$olea_combination.minimal_quantity|intval} x {*<span class="foqty_btn_updateqty_downmulti" > - </span>*}<input type="text" size=2 class="oleafoqty_multiqtyintable oleafoqty_multiqtyinput" value="1"/>{*<span class="foqty_btn_updateqty_upmulti" > + </span>*} = <span class="oleaqty_multiqtytotal">{$olea_combination.minimal_quantity|intval}</span>
		</div>
		{if $oleaqty_istpl16}
		<a class="button ajax_add_to_cart_button" href="#" title="{l s='Add to cart' mod='oleafoquantityprices'}"><span><i class="icon-shopping-cart"></i></span></a>
		{elseif $oleaqty_post15}
		<a class="button_mini oleaqty_btnadd" href="#"><img src="{$img_dir|escape:'html':'UTF-8'}icon/pict_add_cart.png" /></a>
		{else}
		<a class=" " href="#"><img src="{$img_dir|escape:'html':'UTF-8'}icon/cart.gif" width=16 height=16 /></a>
		{/if}
		<p class="foqty_total_info">Total:<span class="foqty_total_price"></span></p>
	</td>
	{/if}
</tr>
{/foreach}
</table>
{if $olea_ps_qty_on_combination}
<p>{l s='Quantity prices are per quantity on each combination' mod='oleafoquantityprices'}</p>
{else}
<p>{l s='Quantity prices are per quantity of mixed combinations' mod='oleafoquantityprices'}</p>
{/if}

{elseif isset($olea_product_qty_prices) AND sizeof($olea_product_qty_prices)}
<table class="tabolea_table std table-product-discounts  {if $oleafoqty_multi_of_minimal}byqtymulti{else}byqty{/if}"  id="oleaqty_pricestable">
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
<tr data-minimal_quantity="{if isset($oleafoqty_minimal_quantity)}{$oleafoqty_minimal_quantity|intval}{else}1{/if}">
	{foreach from=$olea_product_qty_prices key=olea_qty item=olea_price name=olea_qty_prices}
	<td class="tabolea_price">
		{if (bool)$olea_display_group_price}<p class="olea_price olea_group_price">{convertPrice price=$olea_product_qty_prices_of_group.$olea_qty}</p>{/if}
		<p class="olea_price">{convertPrice price=$olea_price}</p>
	</td>
	{/foreach}
	{if $olea_display_product_addtocart || $olea_foqty_isforajax}
	<td class="oleacombitd" data-prices='[{foreach $olea_product_qty_prices as $pricesqty=>$qtyprice}{literal}{{/literal}"qty":{$pricesqty|intval},"price":{$qtyprice|floatval}{literal}}{/literal}{if $qtyprice@last}{else},{/if}{/foreach}]' >
		<div class="oleafoqty_divqty">
		<span class="foqty_btn_updateqty_down" > - </span>
		<input type="text" size="2" value="1" class="oleacombiqty" />
		<span class="foqty_btn_updateqty_up" > + </span>
		<input type="hidden" value="0" class="oleacombiattrib" />
		</div>
		<div class="oleafoqty_divqtymulti">
		{if isset($oleafoqty_minimal_quantity)}{$oleafoqty_minimal_quantity|intval}{else}1{/if} x <input type="text" size=2 class="oleaqty_multiqty" value="1"/> = <span class="oleaqty_multiqtytotal">{if isset($oleafoqty_minimal_quantity)}{$oleafoqty_minimal_quantity|intval}{else}1{/if}</span>
		</div>
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

