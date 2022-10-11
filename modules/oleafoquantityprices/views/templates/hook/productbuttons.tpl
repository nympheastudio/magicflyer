{if isset($oleafoqty_multi_of_minimal) && $oleafoqty_multi_of_minimal}
<p id="quantity_wanted_multi_p" class="hidden oleafoqty_divqtymulti oleafoqty_mainmulti" {* {if (!$allow_oosp && $product->quantity <= 0) || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none;"{/if} *}>
<label>{l s='Quantity' mod='oleafoquantityprices'}</label>
{l s='By' mod='oleafoquantityprices'} <span class="oleaqty_multiqtymini">{$olea_product->minimal_quantity|intval}</span> x {*<span class="foqty_btn_updateqty_downmulti" > - </span>*}<input type="text" class="text oleaqty_multiqty oleafoqty_multiqtyinput" value="1" />{*<span class="foqty_btn_updateqty_upmulti" > + </span>*} = <span class="oleaqty_multiqtytotal">{$olea_product->minimal_quantity|intval}</span>
<span class="clearfix"></span>
</p>
{/if}

{if isset($oleafoqty_change_price_display) && $oleafoqty_change_price_display}
	{if isset($olea_combinations_qty_prices) AND sizeof($olea_combinations_qty_prices)}
	<div id="oleafoqty_spansprices" >
		{foreach from=$olea_combinations_qty_prices item=olea_combination name=olea_combinations_qty_prices}
			{foreach from=$olea_combination.prices key=olea_combination_qty item=olea_combination_price name=olea_combinations_prices}
			{if $olea_combination_qty > 1}
				<p style="display:none;" class="oleafoqty_globalqtyprice" id="oleafoqty_price_{$olea_combination.id_product_attribute|intval}" data-oleafromqty="{$olea_combination_qty|intval}">
				<span class="price">{displayPrice price=$olea_combination_price}</span> {l s='from' mod='oleafoquantityprices'} {$olea_combination_qty|intval}</p>
			{/if}
			{/foreach}
		{/foreach}
	</div>
	{/if}
	
	{if isset($olea_product_qty_prices) AND sizeof($olea_product_qty_prices)}
	<div id="oleafoqty_spansprices" >
			{foreach from=$olea_product_qty_prices key=olea_qty item=olea_price name=olea_qty_prices name=olea_qty_prices}
			{if $olea_qty > 1}
				<p style="display:none;" class="oleafoqty_globalqtyprice" id="oleafoqty_price_0" data-oleafromqty="{$olea_qty|intval}">
				<span class="price">{displayPrice price=$olea_price}</span> {l s='from' mod='oleafoquantityprices'} {$olea_qty|intval}</p>
			{/if}
			{/foreach}
	</div>
	{/if}
{/if}
