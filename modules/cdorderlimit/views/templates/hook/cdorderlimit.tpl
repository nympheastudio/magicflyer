{*
* cdorderlimit :: Gestion des commandes dans prestashop
*
* @author    contact@cleanpresta.com (www.cleanpresta.com)
* @copyright 2015 cleandev.net
* @license   You only can use module, nothing more!
*}

{if $CDO_valeur|escape:'html':'UTF-8'}
	<div class="alert alert-info">
		<p>
			{l s='To be able to pass the order' mod='cdorderlimit'}, {$CDO_message|escape:'html':'UTF-8'}
			{if $NAME_PRODUCTS} <span style="color:#74FF78">[ {l s='For products : ' mod='cdorderlimit'}{$NAME_PRODUCTS|escape:'html':'UTF-8'} ]</span>{/if}
		</p>
	</div>	
{/if}



<script>
	$(document).ready(function(){  
		{if $CDO_error == 0}
			$('#order-opc section.content-center > div:nth-child(n+2)').show();
			$('#center_column .page-heading, #center_column #opc_account, #center_column #carrier_area, #center_column #opc_payment_methods').show();
			$('#center_column').removeClass('cdo_error');
			return true;
		{else} 
			$('#order-opc section.content-center > div:nth-child(n+2)').hide();
			$('#center_column .page-heading, #center_column #opc_account, #center_column #carrier_area, #center_column #opc_payment_methods').hide();
			$('#center_column').addClass('cdo_error');
			return false;
		{/if} 
	});
</script>
