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
<div id="chronossimo_logo">
	<img src="../modules/chronossimo/logo_chronossimo.png" alt="Logo Chronossimo" title="Logo Chronossimo">
</div>
<div id="utiliser_chronossimo"><a href="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}" title="{l s='Module accessible depuis l\'onglet des commandes'}" >{l s='Cliquez ici pour lancer le module'}</a></div>

	<form id="form_chronossimo_config" name="form_chronossimo_config" action="" method="post">

			<fieldset>
				<legend>{l s='Configuration de Chronossimo'}</legend>

                <label>{l s='Type de compte colissimo'} : </label>
                <div class="margin-form">
                    <input type="radio" size="33" name="comptepro" id="comptepro_1" value="1" {if $pro_id}checked="checked" {/if}/>
                    <label class="t" for="comptepro_1"> {l s='Je dispose d\'un compte professionnel'}</label>
                    <input type="radio" size="33" name="comptepro" id="comptepro_0" value="0" {if empty($pro_id)}checked="checked" {/if}/>
                    <label class="t" for="comptepro_0"> {l s='Je ne dispose pas d\'un compte professionnel'}</label>
                </div>

				<label>{l s='Adresse email'}<sup>*</sup> : </label>
                <div class="margin-form">
                    <input type="text" name="email" value="{$email}" />
                </div>



				<label>{l s='Mot de passe'} : </label>
				<div class="margin-form">
					<input type="password" name="mdp" value="{$mdp}" />
				</div>

                <div id="fieldset_comptepro">
                    <br />
                    <fieldset>
                        <legend>{l s='Informations compte professionnel'}</legend>
						{*A SUPPRIMER ENSUITE*}
						<input type="hidden" name="pro_type" value="1" />

                        {*<label>{l s='Type de compte'} : </label>*}
                        {*<div class="margin-form">*}
							{*<input type="radio" size="33" name="pro_type" id="compteproso_3" value="3" {if $pro_type==3}checked="checked" {/if}/>*}
							{*<label class="t" for="compteproso_3"> {l s='Boutique pro laposte'} <a href="https://pro.boutique.laposte.fr/authentification" class="small">(Ouvrir un compte)</a></label>*}
                            {*<input type="radio" size="33" name="pro_type" id="compteproso_1" value="1" {if $pro_type==1}checked="checked" {/if}/>*}
                            {*<label class="t" for="compteproso_1"> {l s='Entreprises colissimo en ligne'} <a href="https://www.colissimo.fr/entreprise/commandor/creationcpt-oecel/" class="small">(Ouvrir un compte)</a></label>*}
                            {*<input type="radio" size="33" name="pro_type" id="compteproso_2" value="2" {if $pro_type==2}checked="checked" {/if}/>*}
                            {*<label class="t" for="compteproso_2"> {l s='So Colissimo Flexibilité'}</label>*}
                        {*</div>*}

                        <br />
                            <label>{l s='Identifiant ColiPoste'}<sup>*</sup> : </label>
                            <div class="margin-form">
                                <input type="text" name="pro_id" id="pro_id" value="{$pro_id}" />
                            </div>


                        <label>{l s='Mot de passe'}<sup>*</sup> : </label>
                        <div class="margin-form">
                            <input type="password" name="pro_mdp" id="pro_mdp" value="{$pro_mdp}" />
                        </div>

                    </fieldset>
                    <br />
                </div>

				<fieldset>
				<legend>{l s='Informations de facturation'}</legend>
				<div class="margin-form">{l s='Informations de facturation de votre entreprise'}</div>
					<label>{l s='Société'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_societe" value="{$fact_societe}" />
						</div>
					<label>{l s='SIRET'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_siret" value="{$fact_siret}" />
						</div>
					<label>{l s='Numéro de TVA'} : </label>
						<div class="margin-form">
							<input type="text" name="fact_tva" value="{$fact_tva}" />
						</div>
					<label>{l s='Adresse (ligne 1)'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_addr1" value="{$fact_address1}" />
						</div>
					<label>{l s='Adresse (ligne 2)'} : </label>
						<div class="margin-form">
							<input type="text" name="fact_addr2" value="{$fact_address2}" />
						</div>
					<label>{l s='Code postal'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_cp" value="{$fact_postal_code}" />
						</div>
					<label>{l s='Ville'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_ville" value="{$fact_city}" />
						</div>
					<label>{l s='Pays'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="fact_pays" value="{$fact_country}" />
						</div>
					<label>{l s='Téléphone'} : </label>
						<div class="margin-form">
							<input type="text" name="fact_tel" value="{$fact_phone}" />
						</div>
				</fieldset>
				<br />
				<fieldset>
				<legend>{l s='Adresse d\'expédition des colis'}</legend>
				<div class="margin-form">{l s='Adresse de retour en cas de non livraison visible par le destinataire'}</div>

					<label>{l s='Sexe'} : </label>
						<div class="margin-form">
							<input type="radio" size="33" name="exp_gender" id="gender_1" value="1" {if $exp_gender!=2}checked="checked" {/if}/>
							<label class="t" for="gender_1"> {l s='Homme'}</label>
							<input type="radio" size="33" name="exp_gender" id="gender_2" value="2" {if $exp_gender==2}checked="checked" {/if}/>
							<label class="t" for="gender_2"> {l s='Femme'}</label>
						</div>

					<label>{l s='Nom'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_nom" value="{$exp_nom}" />
						</div>
					<label>{l s='Prénom'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_prenom" value="{$exp_prenom}" />
						</div>
					<label>{l s='Société'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_societe" value="{$exp_societe}" />
						</div>
					<label>{l s='Adresse (ligne 1)'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_addr1" value="{$exp_address1}" />
						</div>
					<label>{l s='Adresse (ligne 2)'} : </label>
						<div class="margin-form">
							<input type="text" name="exp_addr2" value="{$exp_address2}" />
						</div>
					<label>{l s='Code postal'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_cp" value="{$exp_postal_code}" />
						</div>
					<label>{l s='Ville'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_ville" value="{$exp_city}" />
						</div>
					<label>{l s='Pays'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_pays" value="{$exp_country}" />
						</div>
					<label>{l s='Téléphone'}<sup>*</sup> : </label>
						<div class="margin-form">
							<input type="text" name="exp_tel" value="{$exp_phone}" />
						</div>
				</fieldset>
				<br />
                <div id="fieldset_paiement">
				<fieldset>
				<legend>{l s='Informations de paiement'}</legend>
				<div class="margin-form">{l s='Remplissez les informations de votre moyen de paiement'}</div>
				<div class="margin-form">{l s='Si vous ne souhaitez pas sauvegarder vos informations de paiement, ils seront demandé à chaque paiement'}</div>
					<label>{l s='Titulaire de la carte'} : </label>
						<div class="margin-form">
							<input type="text" name="cb_name" value="{$cb_name}" />
						</div>
					<label>{l s='Numéro de carte'} : </label>
						<div class="margin-form">
							<input type="text" name="cb_num" value="{$cb_num}" />
						</div>
					<label>{l s='Code de vérification'} : </label>
						<div class="margin-form">
							<input type="text" name="cb_verif" value="{$cb_verif}" />
						</div>
					<label>{l s='Date d\'expiration'} : </label>
						<div class="margin-form">
                            <select class= "cb_validite" name="cb_month">
                                <option value=""></option>
                                {foreach from=$list_mois item=mois}
                                    <option value="{$mois|string_format:"%02d"}"{if $mois eq $cb_month} selected="selected"{/if}>{$mois|string_format:"%02d"}</option>
                                {/foreach}
                                {* Smarty V3 only
                                {for $i=1 to 12}
                                    <option value="{$i|string_format:"%02d"}"{if $i eq $cb_month} selected="selected"{/if}>{$i|string_format:"%02d"}</option>
                                {/for}
                                *}
                            </select> /
                            <select class= "cb_validite_year" name="cb_year">
                                <option value=""></option>
                                {foreach from=$list_annees item=annees}
                                    <option value="{$annees}"{if $cb_year eq $annees} selected="selected"{/if}>{$annees}</option>
                                {/foreach}
                                {* Smarty V3 only
                                {for $i=0 to 25}
                                    <option value="{$current_year+$i}"{if $cb_year eq $current_year+$i} selected="selected"{/if}>{$current_year+$i}</option>
                                {/for}
                                *}
                            </select>
                        </div>
				</fieldset>
                </div>
	<br />

				<fieldset class="fieldset_inside">
				<legend>{l s='Accepter les transporteurs suivant'}</legend>
				<div class="margin-form">
                    <div class="center">
                            <input type="button" value="{l s='Remplacer mes transporteurs par une configuration automatique'}" id="transporteur_auto" name="transporteur_auto" class="button"/>
                            <input type="hidden" id="transporteur_auto_hidden" name="transporteur_auto" value="0" />
                    </div><br /><br />
                    {l s='Choisissez le transporteur des commandes à expédier'}</div>
							<label><input type="checkbox" name="carriers_all" value="1" {if $Carriers_all}checked="checked"{/if} /> {l s='Tous'}</label><br /><br />
						{foreach from=$Carriers item=carrier}
							<label><input type="checkbox" name="carriers[]" value="{$carrier.id_carrier}" {if in_array($carrier.id_carrier, $Carriers_used)}checked="checked"{/if} /> {$carrier.name}</label><br />
						{/foreach}


				</fieldset>

				<br />
				<fieldset class="fieldset_inside">
				<legend>{l s='Paramètres de Chronossimo'}</legend>
				<div class="margin-form">{l s='Le statut des commandes en cours de livraison seront automatiquement mis sur "Livré" une fois la livraison terminé'}</div>
						<label><input type="checkbox" name="auto_update_order" value="1" {if $auto_update_order}checked="checked"{/if}/> {l s='Mise à jour automatique du statut des commandes'}</label><br />
						{*
							<div class="margin-form">{l s='Choisissez le statut des commandes que peut modifier Chronossimo'}</div>
						{foreach from=$OrderState item=state}
							<label><input type="checkbox" name="ordertoUpdate[]" value="{$state.id_order_state}" /> {$state.name}</label><br />
						{/foreach}
						*}
				<br /><br />
				<div class="margin-form">{l s='Permet de générer les bordereaux dans un fichier séparé pour faciliter l\'impression sur des étiquettes autocollantes'}</div>
						<label><input type="checkbox" name="split_bordereaux" value="1" {if $split_bordereaux}checked="checked"{/if}/> {l s='Générer les borderaux dans un fichier séparé'}</label><br />

				<br /><br />
				<div class="margin-form">{l s='Utiliser une connexion sécurisé SSL pour tous les échanges de données'}</div>
						<label><input type="checkbox" name="ssl" value="1" {if $ssl}checked="checked"{/if}/> {l s='Utiliser SSL'}</label><br />

                <br /><br />
                <div class="margin-form">{l s='Afficher des boutons d\'accès rapide sur le récapitulatif des commandes dans le backOffice'}</div>
                <label><input type="checkbox" name="showAdminOrders" value="1" {if $showAdminOrders}checked="checked"{/if}/> {l s='Afficher dans AdminOrders'}</label><br />


                <label><input type="checkbox" name="showAdminHome" value="1" {if $showAdminHome}checked="checked"{/if}/> {l s='Afficher à l\'accueil du backOffice'}</label><br />
                <br /><br />
                <div class="margin-form">{l s='Chronossimo affiche les produits à livrer à coté de vos bordereaux d\'expédition de façon à simplifier la préparation des colis. Vous pouvez désactiver cette fonctionnalité'}</div>
                <label><input type="checkbox" name="no_BL" value="1" {if $no_BL}checked="checked"{/if}/> {l s='Ne pas afficher les produits à livrer sur le bordereau'}</label><br />
                <br /><br />
                <div class="margin-form">{l s='Lorsque vous demandez l\'assurance sur vos colis, le montant de l\'assurance sera calculé à partir du prix d\'achat des produits plutôt que sur le prix de vente si vous l\'avez renseigné'}</div>
                <label><input type="radio" name="assurance_vente" value="0" {if not $assurance_vente}checked="checked"{/if}/> {l s='Assurer les produits au prix d\'achat'}</label><br />
                <label><input type="radio" name="assurance_vente" value="1" {if $assurance_vente}checked="checked"{/if}/> {l s='Assurer les produits au prix de vente'}</label><br />
                <br /><br />
                <div class="margin-form">{l s='Chronossimo vous propose comme poids par défaut la somme du poids des articles pour vos colis. Si vous le souhaitez, vous pouvez définir un poids par défaut à ajouter à vos colis (poids emballage etc)'}</div>

                <div class="margin-form">
                    <label>{l s='Poids à ajouter'} : </label>
                    <input type="text" name="poids_to_add" class="poids" value="{$poids_to_add}" /> {$unite_poids}
                </div>

				<label class="small"><input type="checkbox" name="chronossimo_debug" value="1" {if $chronossimo_debug}checked="checked"{/if}/> {l s='Mode debug'}</label><span class="small">{l s='Ne pas activer sauf indication contraire'}</span><br />


                </fieldset>


				<div class="margin-form"><sup>*</sup>{l s='Informations obligatoires'}</div>
			<input type="submit" value="{l s='Sauvegarder la configuration'}" name="submitLogin" class="button"/>

			</fieldset>
			</form><br />
{literal}
<script type="text/javascript">
    $(document).ready(function() {
        $("#comptepro_0,#comptepro_1").click(function()
        {
            proSettings();
        }
        );


        function proSettings()
        {
            if ($("#comptepro_0").is(':checked'))
            {
                $("#fieldset_comptepro").fadeOut();
                $("#pro_id").val("");
                $("#pro_mdp").val("");
                $("#fieldset_paiement").fadeIn();
            }
            else
            {
                $("#fieldset_comptepro").fadeIn();
                $("#fieldset_paiement").fadeOut();
            }
        }
        $("#transporteur_auto").click(function() {
           if (confirm('Attention:\nChronossimo va remplacer la configuration de tous vos transporteurs par une configuration automatique pour les couriers et colis la poste afin de vous simplifier la configuration.\nIl est préférable de sauvegarder votre base de données prestashop avant de valider cette action.\nVoulez vous continuer ?'))
            {
                $("#transporteur_auto_hidden").val(1);
                $("#form_chronossimo_config").submit();
            }
        });



        proSettings();
    });
</script>
{/literal}