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

{if $data.in_category}
    <tr class="amazon-details amazon-action-buttons">
        <td class="col-left"></td>
        <td style="padding-bottom:5px;">
			<span class="amz-action-container">

				<span class="amz-action">
					<input type="radio" rel="action" name="amz-action-{$data.id_lang|intval}"
                           value="{$data.amazon_update|escape:'htmlall':'UTF-8'}"
                           {if ($data.default == $data.amazon_update)}checked{/if} />
					<span class="amz-action-label">{l s='Update' mod='amazon'}</span>
				</span>
				<img src="{$data.images|escape:'htmlall':'UTF-8'}check.png" title="{l s='Update' mod='amazon'}"
                     class="amz-action-img"/>
			</span>&nbsp;
			<span class="amz-action-container">
				<span class="amz-action">
					<input type="radio" rel="action" name="amz-action-{$data.id_lang|intval}"
                           value="{$data.amazon_add|escape:'htmlall':'UTF-8'}"
                           {if ($data.default == $data.amazon_add)}checked{/if} />
					<span class="amz-action-label">{l s='Create' mod='amazon'}</span>
				</span>
				<img src="{$data.images|escape:'htmlall':'UTF-8'}add.png" title="{l s='Create' mod='amazon'}"
                     class="amz-action-img"/>
			</span>&nbsp;
            {if $data.expert_mode && $data.deletion}
                <span class="amz-action-container">
				<span class="amz-action">
					<input type="radio" rel="action" name="amz-action-{$data.id_lang|intval}"
                           value="{$data.amazon_remove|escape:'htmlall':'UTF-8'}"
                           {if ($data.default == $data.amazon_remove)}checked{/if} />
					<span class="amz-action-label">{l s='Delete' mod='amazon'}</span>
				</span>
				<img src="{$data.images|escape:'htmlall':'UTF-8'}delete.gif" title="{l s='Delete' mod='amazon'}"
                     class="amz-action-img"/>
			</span>
            {/if}
            <br/>
            <span class="amz-small-line">{l s='Action which will be applied to this item on next synchronization' mod='amazon'}</span><br/>
			<span class="amz-small-line">{l s='Propagate this value to all products in this' mod='amazon'} :
				<a href="javascript:void(0)" class="amz-propagate-action-cat amz-link">[ {l s='Category' mod='amazon'}
                    ]</a>&nbsp;&nbsp;
				<a href="javascript:void(0)" class="amz-propagate-action-shop amz-link">[ {l s='Shop' mod='amazon'}
                    ]</a>&nbsp;&nbsp;
				<a href="javascript:void(0)"
                   class="amz-propagate-action-manufacturer amz-link">[ {l s='Manufacturer' mod='amazon'} ]</a>&nbsp;&nbsp;
				<a href="javascript:void(0)"
                   class="amz-propagate-action-supplier amz-link">[ {l s='Supplier' mod='amazon'} ]</a>&nbsp;&nbsp;
			</span>
        </td>
    </tr>
{else}
    <tr class="amazon-details">
        <td class="col-left"></td>
        <td style="padding-bottom:5px;"><span
                    class="not-amazon">{l s='This product is not within selected categories for export on Amazon' mod='amazon'}</span>
        </td>
    </tr>
{/if}