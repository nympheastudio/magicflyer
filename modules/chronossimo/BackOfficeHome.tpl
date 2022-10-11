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

{if $retroCompatibility}
{literal}
<style>
    .admin-box1 {
        background-color: #F8F8F8;
        border: 1px solid #CCCCCC;
        border-radius: 3px;
        float: left;
        font-size: 8pt;
        margin-bottom: 20px;
        padding: 0;
        width: 48.1%;
    }
    .admin-box1 h5 {
        background-image: -moz-linear-gradient(center top , #F9F9F9, #ECECEC);
        background: -webkit-gradient(linear, center top ,center bottom, from(#F9F9F9), to(#ECECEC)) repeat scroll 0 0 transparent;
        color: #333333;
        font-size: 12pt;
        font-weight: normal;
        line-height: 29px;
        margin: 0;
        padding: 0 0 0 15px;
        text-shadow: 0 1px 0 #FFFFFF;
    }
    .admin-home-box-list {
        list-style: none outside none;
        margin: 0;
        padding-left: 0;
    }
    .admin-home-box-list li {
        margin: 1px;
        padding: 5px 0;
    }
</style>
{/literal}
{/if}
<div class="admin-box1">
    <h5><div id="chronossimo_logo">
        <img src="../modules/chronossimo/logo_chronossimo.png" alt="Logo Chronossimo" title="Logo Chronossimo">
    </div></h5>
    <ul class="admin-home-box-list">
        <li>
            <a href="index.php?tab=AdminChronossimo&token={$tokenAdminChronossimo}" title="{l s='Module accessible depuis l\'onglet des commandes'}" style="color:black; padding-left: 10px;">Cliquez ici pour accéder à Chronossimo</a>
        </li>
    </ul>
</div>
{if $retroCompatibility}
<div style="clear: both;"></div>
{/if}