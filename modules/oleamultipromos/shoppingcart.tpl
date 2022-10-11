{*
{if $olea_promo_total_by_mail != 0}
<div id="olea_multipromo_info" class="table_block">
	{l s='Validating this cart, you will receive vouchers for your coming orders, for a total of ' mod='oleamultipromos'}<span class="price">{displayPrice price=$olea_promo_total_by_mail currency=$olea_promo_id_currency}</span>
</div>
{/if}
*}

{if isset($olea_promo_discounts) && count($olea_promo_discounts)}
	<div id="olea_multipromo_info" class="table_block {if $olea_isps16}olea_ps16{/if}">
	<p>{l s='Validating this cart, you will receive vouchers for your coming orders' mod='oleamultipromos'}
	{if $olea_promo_total_by_mail>0}{l s='for a total of ' mod='oleamultipromos'}{displayPrice price=$olea_promo_total_by_mail currency=$olea_promo_id_currency}{/if}</p>
	<table>
	{foreach from=$olea_promo_discounts item=olea_discount}
	<tr><td>{$olea_discount.oleamultipromo_mail_message}{* html field, no escaper *}</td></tr>
	{/foreach}
	</table>
	</div>
{/if}