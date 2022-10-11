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
<tr class="amazon-details amazon-item-title fba" rel="{$data.isFBA|escape:'htmlall':'UTF-8'}"
    {if !strlen($data.isFBA)}style="display:none;"{/if}>
    <td class="col-left">{l s='FBA - Value Added' mod='amazon'} </td>
    <td style="padding-bottom:5px;">
        <input type="text" name="amz-fbavalue-{$data.id_lang|intval}" style="width:95px"
               value="{(floatval($data.default)) ? sprintf('%.02f', $data.default) : ''}"/>
        <span style="margin-left:10px">{l s='Additionnal value for FBA handled items' mod='amazon'}</span><br/>
        <span class="amz-small-line">{l s='This value will be added to the product price. It overrides FBA formula.' mod='amazon'}</span><br/>
	<span class="amz-small-line propagation">{l s='Propagate this value to all products in this' mod='amazon'} :
		<a href="javascript:void(0)" class="amz-propagate-fbavalue-cat amz-link">[ {l s='Category' mod='amazon'} ]</a>&nbsp;&nbsp;
		<a href="javascript:void(0)" class="amz-propagate-fbavalue-shop amz-link">[ {l s='Shop' mod='amazon'} ]</a>&nbsp;&nbsp;
		<a href="javascript:void(0)"
           class="amz-propagate-fbavalue-manufacturer amz-link">[ {l s='Manufacturer' mod='amazon'} ]</a>&nbsp;&nbsp;
		<a href="javascript:void(0)" class="amz-propagate-fbavalue-supplier amz-link">[ {l s='Supplier' mod='amazon'}
            ]</a>&nbsp;&nbsp;
	</span>
    </td>
</tr>