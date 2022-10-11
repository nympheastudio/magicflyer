{**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @package   Amazon Market Place
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * Support by mail:  support.amazon@common-services.com
*}
<tr class="amazon-details amazon-item-title">
    <td class="col-left" rel="repricing"><span>{l s='Custom Strategy' mod='amazon'}</span></td>
    <td style="padding-bottom:5px;" class="repricing">
        <input type="text" name="amz-repricing_min-{$data.id_lang|intval}"
               value="{$data.repricing_min|escape:'htmlall':'UTF-8'}" style="width:60px"/>&nbsp;&nbsp;&nbsp;
        <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}down.png"/>

        <input type="text" name="amz-repricing_max-{$data.id_lang|intval}"
               value="{$data.repricing_max|escape:'htmlall':'UTF-8'}" style="width:60px"/>&nbsp;&nbsp;&nbsp;
        <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}up.png"/>

        <br/>
        <span class="amz-small-line">{l s='Overrides the profile strategy by this custom strategy' mod='amazon'}</span><br/>
    </td>
</tr>