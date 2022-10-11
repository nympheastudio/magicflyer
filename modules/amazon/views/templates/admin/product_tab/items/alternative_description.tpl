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
    <td class="col-left" rel="alternate_description"><span>{l s='Alternate Description' mod='amazon'}</span></td>
    <td style="padding-bottom:5px;">
		<textarea rows="4" cols="50" name="amz-description-{$data.id_lang|intval}">{$data.default|escape:'htmlall':'UTF-8'}</textarea>
		<br />
        <span class="amz-small-line">{l s='Product description override to export to Amazon' mod='amazon'}</span><br/>
    </td>
</tr>