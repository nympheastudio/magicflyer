{if isset($olea_promo_extra_right) AND count($olea_promo_extra_right)>0}
<div id="olea_promo_extra_right" class="{if $olea_isps16}olea_ps16{/if}">
<ul class="main">
	{foreach from=$olea_promo_extra_right item=promo name=allpromoextra}
		<li class="{if $smarty.foreach.allpromoextra.first}first_item{elseif $smarty.foreach.allpromoextra.last}last_item{/if} {if $smarty.foreach.allpromoextra.index % 2}alternate_item{else}item{/if}">
			{$promo->communication_extra_right_modified}{* html field, no escaper *}
		</li>
	{/foreach}
</ul>
</div>
{/if}