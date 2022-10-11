{if isset($olea_promo_product_footer) AND count($olea_promo_product_footer)>0}
<div id="olea_promo_product_footer" class="{if $olea_isps16}olea_ps16{/if}">
<ul class="main">
	{foreach from=$olea_promo_product_footer item=promo name=allpromoproductfooter}
		<li class="{if $smarty.foreach.allpromoproductfooter.first}first_item{elseif $smarty.foreach.allpromoproductfooter.last}last_item{/if} {if $smarty.foreach.allpromoproductfooter.index % 2}alternate_item{else}item{/if}">
			{$promo->communication_product_footer_modified}{* html field, no escaper *}
		</li>
	{/foreach}
</ul>
</div>
{/if}