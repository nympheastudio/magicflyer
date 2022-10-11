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


<div style="text-align: center;">
    <div id="chronossimo_logo">
        <img src="../modules/chronossimo/logo_chronossimo.png" alt="Logo Chronossimo" title="Logo Chronossimo">
    </div>

<h3>{l s='Selectionner le type de votre envoi'}</h3>

    <div class="selectTransporteur">
        <div>
            <a href="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=1&carrier=LETTRESUIVIE{if $orderToAdd}&order_to_add[]={$orderToAdd}{/if}"><img src="../modules/chronossimo/img/lettre_suivie.png" style="width: 210px;"></a>
        </div>
        <div>
            <a href="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=1&carrier={if $orderToAdd}&order_to_add[]={$orderToAdd}{/if}"><img src="../modules/chronossimo/img/colissimo.png" style="width: 200px;"></a>
        </div>
    </div>
    <div class="clear"></div>

    <p><a href="index.php?tab=AdminModules&configure=chronossimo&token={$tokenConfigAdminChronossimo}&tab_module=shipping_logistics&module_name=chronossimo">{l s='Editer la configuration'}</a> | <a href="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=100">{l s='Historique des expéditions'}</a></p>
    <p><a href="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}&action=200">{l s='Intégration des numéros de suivi'}</a></p>
</div>


<script type="text/javascript">

    $( document ).ready(function() {

    });
</script>