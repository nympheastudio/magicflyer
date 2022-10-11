{*
Chronossimo - Gestion automatique de l'affranchissement et du suivi des colis

 NOTICE OF LICENCE

 This source file is subject to a commercial license from SARL VANVAN
 Use, copy, modification or distribution of this source file without written
 license agreement from the SARL VANVAN is strictly forbidden.
 In order to obtain a license, please contact us: contact@chronossimo.fr
 ...........................................................................
 INFORMATION SUR LA LICENCE D'UTILISATION

 L'utilisation de ce fichier source est soumise a une licence commerciale
 concédée par la société VANVAN
 Toute utilisation, reproduction, modification ou distribution du présent
 fichier source sans contrat de licence écrit de la part de la SARL VANVAN est
 expressément interdite.
 Pour obtenir une licence, veuillez contacter la SARL VANVAN a l'adresse:
                  contact@chronossimo.fr
 ...........................................................................
 @package    Chronossimo
 @version    1.0
 @copyright  Copyright(c) 2012-2014 VANVAN SARL
 @author     Wandrille R. <contact@chronossimo.fr>
 @license    Commercial license
 @link http://www.chronossimo.fr
*}<h3>{l s='Historique des expéditions'}</h3>
{if $orders}
<form name="form_orderToVerif" id="form_orderToVerif" method="POST" action="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=2">
<table class="table expedition_list" cellspacing="0" cellpadding="0">
	<thead>
		<tr class="nodrag nodrop">
			<th>{l s='ID'}</th>
			<th>{l s='Client'}</th>
			<th>{l s='Statut'}</th>
			<th>{l s='Total'}</th>
			<th>{l s='Date'}</th>
			<!--<th>{l s='Poids'}</th>-->
			<th>{l s='Pays'}</th>
			<th>{l s='Transporteur'}</th>
			
			<th>{l s='Signature'}</th>
			<th>{l s='Assurance'}</th>
			<th>{l s='Volumineux'}</th>
			<th>{l s='Prix'}</th>
			<th>{l s=''}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$orders item=order}
		<tr {if $auto_update_order}class="{if $order.livre==1}livraison_livre{elseif $order.livre==-1}livraison_error{else}livraison_encours{/if}"{/if}>
			<td>{$order.order_id}</td>
			<td>{$order.adresse.firstname} {$order.adresse.lastname}</td>
			<td>{$order.statut}</td>
			<td>{$order.total} €</td>
			<td>{$order.order_fields.date_add}</td>
			<!--<td>{$order.poids} {$order.unite_poids}</td>-->
			<td>{$order.pays}</td>
			<td>{$order.transporteur}</td>
			
			<td>{if $order.signature}<input type="hidden" name="signature[]" value="{$order.order_id}" />{/if}<input type="checkbox" name="signature[]" disabled="disabled" value="{$order.order_id}"{if $order.signature} checked="checked"{/if} /></td>
			<td>{if $order.assurance}<input type="hidden" name="assurance[]" value="{$order.order_id}" />{/if}<input type="checkbox" name="assurance[]" disabled="disabled" value="{$order.order_id}"{if $order.assurance} checked="checked"{/if} /></td>
			<td>{if $order.volumineux}<input type="hidden" name="volumineux[]" value="{$order.order_id}" />{/if}<input type="checkbox" name="volumineux[]" disabled="disabled" value="{$order.order_id}"{if $order.volumineux} checked="checked"{/if} /></td>
			<td>{$order.tarif}</td>
			<td>{if $order.suivi}<a href="{$order.suivi}" target="_blank" ><img src="../img/admin/delivery.gif" title="{l s='Voir le suivi'}"/></a>{/if}{if $order.pdf}<a href="{$order.pdf}" target="_blank" ><img src="../img/admin/pdf.gif" title="{l s='Voir le bordereau'}"/></a>{/if}<a href="pdf.php?id_order={$order.order_id}&pdf" target="_blank" ><img src="../img/admin/tab-invoice.gif" title="{l s='Voir la facture'}"/></a> <a href="index.php?tab=AdminOrders&id_order={$order.order_id}&vieworder&token={$tokenAdminOrders}" target="_blank" ><img src="../img/admin/details.gif" title="{l s='Voir le détail de la commande'}" /></a></td>
		</tr>
		{/foreach}

	</tbody>
</table>
<br />
{if $auto_update_order}
<p>{l s='Légende'}</p>
<p class="miniDescriptif">{l s='La couleur ne dépend pas du statut prestashop de la commande, mais du statut réel du colis identifié à l\'aide de son numéro de suivi.'}<br />{l s='La mise à jour automatique du statut des commandes est activé'}</p>
<div class='blocLegende livraison_livre'></div> {l s='La commande a été livré'} <br />
<div class='blocLegende livraison_encours'></div> {l s='La commande est en cours de livraison'}<br />
<div class='blocLegende livraison_error'></div> {l s='Impossible de déterminer le statut de la livraison'}<br />
{/if}
</form>
{else}
<h3>{l s='Aucune expédition pour le moment'}</h3>
{/if}
<br />

{literal}
<style>
.miniDescriptif {
	font-size: 10px;
	color:#746D69;
}
.blocLegende {
	height: 10px;
	width: 20px;
	border: 1px solid #746D69;
	display: inline-block;
}
.livraison_livre {
	background-color: #DDFFAA;
}
.livraison_encours {
	background-color: #EEDDFF;
}
.livraison_error {
	background-color: #DADADA;
}

	.details label{
		width: 300px;
	}
	.expedition_list tbody {
		font-size: 10px;
	}
	.pays_origine {
		width: 70px;
	}
	#progressDiv {
		margin: auto;
		width: 300px;
		height: 120px;
		border: 1px solid #000000;
		border-radius: 5px;
		padding-top: 5px;
		padding-bottom: 5px;
		background: #FFFFFF;
		position: absolute;
	}
	#progressDiv img {
		margin: auto;
		display: block;
		margin-top: 5px;
		margin-bottom: 10px;
	}
	#progressbar {
		margin: auto;
	}
	#progressText {
		margin: auto;
		text-align: center;
	}
</style>
{/literal}
