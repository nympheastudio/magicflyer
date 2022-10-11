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
    <td class="col-left">{l s='ASIN' mod='amazon'}</td>
    <td style="padding-bottom:5px;">
        <input type="text" name="amz-asin-{$data.id_lang|intval}" value="{$data.default|escape:'htmlall':'UTF-8'}"
               style="width:120px"/>&nbsp;&nbsp;
        <input type="hidden" class="amz-asin-mustbeset" value="{l s='EAN13 or UPC must be set !' mod='amazon'}"/>
        <input type="hidden" class="amz-asin-unable" value="{l s='Unable to fetch ASIN for' mod='amazon'}"/>

        <button type="submit" class="button btn btn-default amz-probe-asin">
            <i class="icon-search icon-white"></i> {l s='UPC/EAN > ASIN' mod='amazon'}
        </button>

        &nbsp;&nbsp;&nbsp; <span class="amz-small-line">{l s='ASIN is optional' mod='amazon'}</span>

        &nbsp;&nbsp;&nbsp; <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}/green-loader.gif"
                                title="{l s='UPC/EAN > ASIN' mod='amazon'}" class="asin-loader" style="display:none"/>


        <div class="asin-response" style="display:none;">&nbsp;</div>
    </td>
</tr>