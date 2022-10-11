{*
* cdorderlimit :: Gestion des commandes dans prestashop
*
* @author    contact@cleanpresta.com (www.cleanpresta.com)
* @copyright 2015 cleandev.net
* @license   You only can use module, nothing more!
*}

{if $CDO_valeurs|escape:'html':'UTF-8'}
	</div>
	</div>
	
	<div>
	<div class="container" style="margin-top:15px">
	<div class='alert alert-danger'>
		<h4>{l s='To be able to pass the order' mod='cdorderlimit'}, {$CDO_messages|escape:'html':'UTF-8'}</h4>
	</div>
{/if}