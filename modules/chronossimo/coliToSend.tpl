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
*}<div id="chronossimo_logo">
	<img src="../modules/chronossimo/logo_chronossimo.png" alt="Logo Chronossimo" title="Logo Chronossimo">
</div>

<h3>{l s='Liste des commandes à expédier'}</h3>
<a href="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=1&order_statut_id={$order_statut_id}" id="addOrderBt"><img border="0" src="../img/admin/add.gif"> {l s='Ajouter'}</a>
<form name="form_orderToVerif" id="form_orderToVerif" method="POST" action="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=2">
{if $orders}
<table class="table expedition_list" cellspacing="0" cellpadding="0">
	<thead>
		<tr class="nodrag nodrop">
			<th><input type="checkbox" id="check_all_orders" checked="checked" title="Sélectionner / Désélectionner tout"/> {l s='ID'}</th>
			<th>{l s='Client'}</th>
			<!--<th>{l s='Statut'}</th>-->
			<th>{l s='Total'}</th>
			<th>{l s='Date'}</th>
			<th>{l s='Poids'}</th>
			<th>{l s='Pays'}</th>
			<th>{l s='Transporteur'}</th>
			
			{if $douane}<th>{l s='Origine'}</th>{/if}
			{if $douane}<th class="colSH" {if $douane_last_select != 4}style="display: none;"{/if}>{l s='Numéro SH'}</th>{/if}
			<th class="colCoche">{if $douane}{l s='S'}{else}{l s='Signature'}{/if}</th>
			<th class="colCoche">{if $douane}{l s='A'}{else}{l s='Assurance'}{/if}</th>
			<th class="colCoche">{if $douane}{l s='V'}{else}{l s='Volumineux'}{/if}</th>
			<th>{l s=''}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$orders item=order}
		<tr>
			<td><input type="hidden" name="order_id[]" value="{$order.order_id}" /><input type="checkbox" class="order_id_check" name="order_id_check[]" value="{$order.order_id}" checked="checked"/> {$order.order_id}</td>
			<td>{$order.adresse.firstname} {$order.adresse.lastname}</td>
			<!--<td>{$order.statut}</td>-->
			<td>{$order.total} €</td>
			<td>{$order.order_fields.date_add}</td>
			<td><input type="text" class="poids" name="poids[]" value="{$order.poids}" style="width: 40px;" /> {$order.unite_poids} <img class="duplicate" src="../modules/chronossimo/downarrow.gif" /></td>
			<td>{$order.pays}</td>
			<td>{$order.transporteur}</td>
			
			{if $douane}<td>{if $order.douane}<select name="pays_origine[]" class="pays_origine"><option value="">{l s='Choisir'}</option>{foreach from=$country item=pays}<option value="{$pays}"{if $order.pays_origine == $pays} selected="selected"{/if}>{$pays}</option>{/foreach}</select> <img class="duplicate" src="../modules/chronossimo/downarrow.gif" />{else}&nbsp;<input type="hidden" name="pays_origine[]" value="" />{/if}</td>{/if}
			{if $douane}<td class="colSH" {if $douane_last_select != 4}style="display: none;"{/if}>{if $order.douane}<input type="text" name="numero_sh[]" class="numero_sh" title="{l s='Numéro SH du produit le plus onéreux'}" value="{$order.numero_sh}"/> <img class="duplicate" src="../modules/chronossimo/downarrow.gif" />{else}<input type="hidden" name="numero_sh[]" value="" />{/if}</td>{/if}
			<td><input type="checkbox" name="signature[]" value="{$order.order_id}"{if $signature || $order.signature} checked="checked"{/if} title="{l s='Demander la signature du colis'}" /></td>
			<td><input type="checkbox" name="assurance[]" value="{$order.order_id}"{if $assurance || $order.assurance} checked="checked"{/if} title="{l s='Assurer la valeur du colis'}" /></td>
			<td><input type="checkbox" name="volumineux[]" value="{$order.order_id}"{if $volumineux || $order.volumineux} checked="checked"{/if} title="{l s='Déclarer le colis volumineux'}" /></td>
			<td><a href="pdf.php?id_order={$order.order_id}&pdf" target="_blank" ><img src="../img/admin/tab-invoice.gif" title="{l s='Voir la facture'}"/></a> <a href="index.php?tab=AdminOrders&id_order={$order.order_id}&vieworder&token={$tokenAdminOrders}" target="_blank" ><img src="../img/admin/details.gif" title="{l s='Voir le détail de la commande'}" /></a></td>
		</tr>
		{/foreach}

	</tbody>
</table>

{if $douane}
<a class="aideNumeroSH" href="http://ec.europa.eu/taxation_customs/dds2/taric/taric_consultation.jsp?Lang=FR" target="_blank" title="{l s='Trouver le numéro SH d\'un produit'}">{l s='Trouver un numéro SH'}</a>
{/if}
<br /><br />

			<fieldset class="details">
				<legend>{l s='Options complémentaires'}</legend>
				{*
				<label>{l s='Joindre la facture'} : </label>
				<div class="margin-form">
					<input type="checkbox" name="joindre_facture" value="1" {if $joindre_facture_last_select}checked="checked"{/if}/>
				</div>
				*}
				<label>{l s='MAJ du statut de la commande'} : </label>
				<div class="margin-form">
					<input type="checkbox" name="set_statut" value="1" {if $set_statut_last_select}checked="checked"{/if}/>
				</div>
				<label>{l s='Attribuer un numéro de suivi'} : </label>
				<div class="margin-form">
					<input type="checkbox" name="set_suivi" value="1" {if $set_suivi_last_select}checked="checked"{/if}/>
				</div>
				{if $douane}
				<label>{l s='Déclaration Douanne'} <sup>*</sup> : </label>
				<div class="margin-form">
					<select name="nature_douane" id="nature_douane">
							<option value="">{l s='Sélectionner'}</option>
							<option value="6"{if $douane_last_select==6} selected="selected"{/if}>{l s='Retour de marchandise'}</option>
							<option value="3"{if $douane_last_select==3} selected="selected"{/if}>{l s='Cadeau'}</option>
							<option value="1"{if $douane_last_select==1} selected="selected"{/if}>{l s='Echantillon'}</option>
							<option value="2"{if $douane_last_select==2} selected="selected"{/if}>{l s='Document'}</option>
							<option value="4"{if $douane_last_select==4} selected="selected"{/if}>{l s='Envoi commercial'}</option>
							<option value="5"{if $douane_last_select==5} selected="selected"{/if}>{l s='Autre'}</option>
						</select>
				</div>
				{/if}
				{if $bypass_confirm}
				<label>{l s='Ne pas afficher la page de confirmation'} : </label>
				<div class="margin-form">
					<input type="checkbox" name="bypass_confirm" id="bypass_confirm" value="1" />
					<input type="hidden" id="redirect_secure_paiement" name="paiement_automatique" value="1" />
				</div>
				{/if}
				</fieldset>
				<input type="hidden" name="sessionID" id="sessionID" value="{$sessionID}" />
				<input type="hidden" name="orders_to_send" value="{$orders_to_send}" />
				<p style="margin-top: 20px;"><input id="bSubmit" type="submit" value="{l s='Envoyer les colis'}" name="submit" class="button"/></p>
{else}
<h3>{l s='Aucune commande'}</h3>
{/if}
<input type="hidden" name="order_to_add" id="order_to_add" value="{foreach from=$order_to_add item=order}{$order},{/foreach}" />
<input type="hidden" name="carrier" id="carrier" value="{$carrier}" />
</form>
<br />
{literal}
<script type="text/javascript">


$(document).ready(function() {
	last_progress = 0;
	last_update = (new Date()).getTime();
	progress_view = 0;
	progress_step = 0;
    {/literal}
    pro = {if $pro}true{else}false{/if};
    {literal}
	
	$("#form_orderToVerif").submit(function() {
			returnVal = true;
			if ($("#nature_douane") && $("#nature_douane").val() == '')
			{
				alert("Vous devez choisir la nature douanne du colis");
				returnVal = false;
			}
			if ($(".pays_origine") )
			{
				$(".pays_origine").each(function(index) {
					if ($(this).val() == '') // Si le pays n'est pas encore selectionne
					{
						if ($(this).parent().parent().children("td").first().children("input[type=checkbox]").is(':checked')) // Si cette commande est coche pour l'expedition
						{
					  		alert("Vous devez choisir le pays d origine des produits de vos colis pour la declaration de douane");
							returnVal = false;
							return false;
						}
					}
				});
				
			}

        var nbreColis = $("input[type=checkbox].order_id_check:checked").length

        if (nbreColis <= 0)
        {
            returnVal = false;
            alert('Vous devez selectioner des colis à envoyer');
        }
        if (nbreColis > 25 && pro==false)
        {
            returnVal = false;
            alert('Il n\'est pas possible de selectionner plus de 25 colis par affranchissement.');
        }

			if ($(".numero_sh") &&  $("#nature_douane").val() == 4)
			{
				$(".numero_sh").each(function(index) {
					if ($(this).val().length < 6 || $(this).val().length > 10) // Si le pays n'est pas encore selectionne
					{
						if ($(this).parent().parent().children("td").first().children("input[type=checkbox]").is(':checked')) // Si cette commande est coche pour l'expedition
						{
					  		alert('Vous devez indiquer le numéro SH des commandes en choisissant "'+$("#nature_douane option:selected").text()+'"');
							returnVal = false;
							return false;
						}
					}
				});
				
			}
			
			
			
			 if (returnVal)
			 {
	        		progressBar();
			 }
				return returnVal;
		});
		
		// On fait disparaitre la fenetre lors d'un clic en dehors de la zone
		$("*", document.body).click(function(e){
				if ((e.target.id != "addOrder" && e.target.id != "input_order_to_add" && e.target.id != "btAddOrderConfirm")){
					$("#addOrder").fadeOut();
				}
			});
		
		
		$("#addOrderBt").click(function() {
			$("#addOrder").fadeIn()
			
			
			$("#btAddOrderConfirm").click(function() {
				urlString = '';
				var order_to_add = $("#order_to_add").val();
				//var order_to_add = "aa,ii";
				var order_to_add_array = order_to_add.split(",");
				for(var i in order_to_add_array)
				{
					if (order_to_add_array[i])
						urlString += '&order_to_add[]='+order_to_add_array[i];
				}
					if ($("#input_order_to_add").val())
						urlString += '&order_to_add[]='+$("#input_order_to_add").val();
					if ($("#carrier").val())
						urlString += '&carrier='+$("#carrier").val();
				//urlString
				location.href = $("#addOrderBt").attr('href')+urlString;
			});
			return false;
		});
		
		$("#nature_douane").change(function() {
			if ($(this).val() == 4)
				$(".colSH").fadeIn();
			else
				$(".colSH").fadeOut();
				
			});
			
		$(".duplicate").attr('title', 'Appliquer la même valeur en dessous').click(function() {
			valeur = $(this).prev().val();
			class_name = $(this).prev().attr('class');
			if (class_name)
				$(this).parents("tr").nextAll("tr").find("."+class_name).each(function(index) {
					type = $(this).attr('type');

					if (type == 'select-one')
						$(this).children("option[value='"+valeur+"']").attr('selected','selected');
					else
						$(this).val(valeur);
				});
		});
		
		$("#check_all_orders").click(function() {
//				$(".order_id_check").attr("checked", $("#check_all_orders").is(':checked'));
			checkOrder();
		});

	function checkOrder() {
		var compteur = 0;
		$(".order_id_check").each(function(index) {

			var check = $("#check_all_orders").is(':checked');
			if (!check || pro || compteur < 20)
				$(this).attr("checked", check);
			compteur++;
		});
	}
		

});
</script>
{/literal}
<div id="progressDiv" style="display: none;">
	<img src="../modules/chronossimo/ajax_loader.gif" width="64" height="64"/>
	<div id="progressbar"></div>
	<div id="progressText"></div>
</div>

<div id="addOrder" style="display: none;">
	{l s='ID de la commande'}<input type="text" id ="input_order_to_add" name="input_order_to_add" />
		<input type="submit" id="btAddOrderConfirm" class="button" value="Ajouter" />
</div>


<script src="../modules/chronossimo/js/jquery.timers.js" type="text/javascript"></script>
<script src="../modules/chronossimo/js/jquery.progressbar.js" type="text/javascript"></script>
<script src="../modules/chronossimo/js/chronossimo.js" type="text/javascript"></script>
<link type="text/css" rel="stylesheet" href="../modules/chronossimo/js/jquery.progressbar.css" />
<link type="text/css" rel="stylesheet" href="../modules/chronossimo/chronossimo.css" />