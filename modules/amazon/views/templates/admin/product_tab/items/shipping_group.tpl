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
{if isset($data.groups[$id_lang])}
<tr class="amazon-details amazon-item-title">
    <td class="col-left" rel="shipping_template"><span>{l s='Shipping Template' mod='amazon'}</span></td>
    <td style="padding-bottom:5px;">
        <select name="amz-shipping_group-{$data.id_lang|intval}">
            <option></option>
            {foreach from=$data.groups[$id_lang] key=group_key item=group_name}
                <option value="{$group_key|escape:'htmlall':'UTF-8'}" {if $group_key == $data.default}selected{/if}>{$group_name|escape:'htmlall':'UTF-8'}</option>
            {/foreach}
        </select>
	<span class="amz-small-line propagation">{l s='Propagate this value to all products in this' mod='amazon'} :
					<a href="javascript:void(0)"
                       class="amz-propagate-shipping_group-cat amz-link">[ {l s='Category' mod='amazon'} ]</a>&nbsp;&nbsp;
                    <a href="javascript:void(0)"
                       class="amz-propagate-shipping_group-shop amz-link">[ {l s='Shop' mod='amazon'} ]</a>&nbsp;&nbsp;
					<a href="javascript:void(0)"
                       class="amz-propagate-shipping_group-manufacturer amz-link">[ {l s='Manufacturer' mod='amazon'} ]</a>&nbsp;&nbsp;
					<a href="javascript:void(0)"
                       class="amz-propagate-shipping_group-supplier amz-link">[ {l s='Supplier' mod='amazon'} ]</a>&nbsp;&nbsp;

	</span>
    </td>
</tr>
{/if}