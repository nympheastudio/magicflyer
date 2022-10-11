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
*}<br />
<fieldset>
        <legend><img alt="Adresse de livraison" src="../modules/chronossimo/logo.gif" style="width: 16px;">Chronossimo</legend>
        <div style="float: left; width: 50%;">
            <form action="index.php" method="GET">
                <input type="hidden" value="{$tokenAdminChronossimo}" name="token">
                <input type="hidden" value="AdminChronossimo" name="tab">
                <p class="center">
                    <input type="submit" value="Expédier par groupe" name="submit" class="button">
                </p>
            </form>

        </div>

        <div style="float: right; width: 50%;">
            <form action="index.php" method="GET">
                <input type="hidden" value="1" name="action">
                <input type="hidden" value="{$tokenAdminChronossimo}" name="token">
                <input type="hidden" value="AdminChronossimo" name="tab">
                <input type="hidden" value="{$id_order}" name="order_to_add[]">
                <p class="center">
                    <input type="submit" value="Expédier cette commande" name="submit" class="button">
                </p>
            </form>
        </div>

{if $bordereau}
    <fieldset style="margin-top: 60px;">
        <legend>Accès aux documents</legend>
    {if $pdf}
    <div style="float: left; width: 50%;">
        <p class="center">
        <input type="submit" value="Télécharger le PDF" name="submit" class="button" onclick="javascript:window.open('{$pdf}')">
        </p>
    </div>
    {/if}
   {if $pdf}
    <div style="float: right; width: 50%;">
        <p class="center">
        <input type="submit" value="Télécharger le bordereau" name="submit" class="button" onclick="javascript:window.open('{$pdf_bordereaux}')">
        </p>
    </div>
   {/if}
    </fieldset>
{/if}
</fieldset>
