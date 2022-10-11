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
*}<link type="text/css" rel="stylesheet" href="../modules/chronossimo/chronossimo.css" />


{if $smarty.post}
<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" />{l s='Mise à jour effectué'}</div>
{/if}


<div style="text-align: center;">
<div id="chronossimo_logo">
	<img src="../modules/chronossimo/logo_chronossimo.png" alt="Logo Chronossimo" title="Logo Chronossimo">
</div>

	
	<form id="form_orderToSend" class="bootstrap" name="form_orderToSend" action="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=1" method="post">
			<fieldset>
				<legend>{l s='Commande à expédier'}</legend>

				<label>{l s='Statut des commandes'} : </label>
				<div class="margin-form">
					<select name="order_statut_id">
						{foreach from=$OrderState item=state}
							<option value="{$state.id_order_state}"{if $state.id_order_state==$last_order_state} selected="selected"{/if}>{$state.name}</option>
						{/foreach}
					</select>
				</div><!-- .margin-form -->
				<div class="margin-form">{l s='Statut actuel des commandes que vous souhaitez expédier'}</div>
				<br />
			<div id="moreFilter" class="hide">
				<label>{l s='Date des commandes'} : </label>
				<div class="margin-form">
					{*<input type="text" style="width:70px" value="" name="dateFilter" id="dateFilter" class="filter datepicker">*}

					<div class="date_range">
						<div class="input-group fixed-width-md center">
							<input type="text" class="filter datepicker date-input form-control" id="dateFilterStart" name="dateFilterStart" placeholder="Du">
							{*<input type="hidden" id="orderFilterFilterStart" name="orderFilterFilter[0]" value="">*}
											<span class="input-group-addon">
												<i class="icon-calendar"></i>
											</span>
						</div>
						<div class="input-group fixed-width-md center">
							<input type="text" class="filter datepicker date-input form-control" id="dateFilterEnd" name="dateFilterEnd" placeholder="Au">
							{*<input type="hidden" id="orderFilterFilterEnd" name="orderFilterFilter[1]" value="">*}
											<span class="input-group-addon">
												<i class="icon-calendar"></i>
											</span>
						</div>
					</div>

				</div><!-- .margin-form -->
				<div class="margin-form">{l s='Filtrage par date'}</div>
				<br />



				<label>{l s='Contenant ce produit'} : </label>
				<div class="margin-form">
					<div id="ajax_choose_product">
						<div class="input-group">
							<input type="text" id="product_filter" name="product_filter" autocomplete="off" class="">
							<span class="input-group-addon"><i class="icon-search"></i></span>
						</div>
					</div>
				</div><!-- .margin-form -->
				<div class="margin-form">{l s='Filtrage par produit'}</div>
			</div>
				<br />
				
				<div class="options">
					<label><input type="checkbox" name="moreFilter" id="moreFilterInput" autocomplete="off"/> {l s='Plus d\'option de filtrage'}</label><br />
					<label><input type="checkbox" name="signature_all" value="1" /> {l s='Remise contre signature'}</label><br />
					<label><input type="checkbox" name="assurance_all" value="1" /> {l s='Assurer la valeur du colis'}</label><br />
					<label><input type="checkbox" name="volumineux_all" value="1" /> {l s='Colis Volumineux'}</label><br />
				</div>
				<input type="hidden" name="carrier" value="{$carrier}" />
			<input type="submit" value="{l s='Lister les commandes'}" name="submit" class="button"/>
			</fieldset>
			</form><br />
			
			
			<p><a href="index.php?tab=AdminModules&configure=chronossimo&token={$tokenConfigAdminChronossimo}&tab_module=shipping_logistics&module_name=chronossimo">{l s='Editer la configuration'}</a> | <a href="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=100">{l s='Historique des expéditions'}</a></p>
</div>
<script type="text/javascript" src="../js/jquery/jquery-ui-1.8.10.custom.min.js"></script> <!-- prestashop 1.4 -->
<script type="text/javascript" src="../js/jquery/datepicker/ui/i18n/ui.datepicker-fr.js"></script><!-- prestashop 1.4 -->
<script type="text/javascript" src="../js/jquery/jquery.autocomplete.js"></script><!-- prestashop 1.4 -->
<link href="../css/jquery.autocomplete.css" rel="stylesheet" type="text/css"/><!-- prestashop 1.4 -->

<link href="../js/jquery/plugins/autocomplete/jquery.autocomplete.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="../js/jquery/plugins/autocomplete/jquery.autocomplete.js"></script>

<script type="text/javascript">

	$( document ).ready(function() {
		if ($("#form_orderToSend .datepicker").length > 0)
			$("#form_orderToSend .datepicker").datepicker({
				prevText: '',
				nextText: '',
				dateFormat: 'yy-mm-dd'
			});

		function checkFilter() {
			if ($(this).is(':checked'))
				$("#moreFilter").hide().removeClass("hide").slideDown();
			else
				$("#moreFilter").slideUp();
		}
		checkFilter();

		$("#moreFilterInput").click(checkFilter);

		$("#moreFilterInput").parent().fadeOut().fadeIn().fadeOut().fadeIn();



				$('#product_filter')
						.autocomplete('ajax_products_list.php', {
							minChars: 1,
							autoFill: true,
							max:20,
							matchContains: true,
							mustMatch:false,
							scroll:false,
							cacheLength:0,
							formatItem: function(item) {
								return item[1]+' - '+item[0];
							}
						}).result(null);

				$('#product_filter').setOptions({
					extraParams: {
						excludeIds : '1'
					}
				});
	});
</script>