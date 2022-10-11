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
{assign var="first" value="1"}
{assign var="item" value="1"}

<tr class="amazon-details amazon-item-title">
    <td class="col-left" rel="bullet_point"><span>{l s='Key Product Features' mod='amazon'}</span></td>
    <td style="padding-bottom: 5px;">
        {foreach array('bullet_point1', 'bullet_point2', 'bullet_point3', 'bullet_point4', 'bullet_point5') as $key}
            {assign var="bullet" value=null}

            {if $data.default == null}
                {$bullet = null}
            {elseif isset($data.default[$key])}
                {$bullet = trim($data.default[$key])}
            {/if}
            <span class="amazon-bullet-container-{$data.id_lang|intval}"
                  {if empty($bullet) && $first != 1}style="display:none"{/if}>
				<input type="text" name="bullet_point{$item|intval}" value="{$bullet|escape:'htmlall':'UTF-8'}"
                       value="{$bullet|escape:'htmlall':'UTF-8'}" class="amazon-bullet-point"/>
				<span class="bulletpoint-action">
					{if $first}
                        <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}plus.png" alt="{l s='Add' mod='amazon'}"
                             class="amazon-bullet-point-add"/>
                        <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}minus.png"
                             alt="{l s='Remove' mod='amazon'}" style="display:none" class="amazon-bullet-point-del"/>


                                                                            {else}


                        <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}minus.png"
                             alt="{l s='Add' mod='amazon'}" class="amazon-bullet-point-del"/>
                        <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}plus.png"
                             alt="{l s='Remove' mod='amazon'}" style="display:none" class="amazon-bullet-point-add"/>
                    {/if}

                    {$first = false}
				</span><br/>
			</span>
            {$item = $item + 1}
        {/foreach}

        <span class="amz-small-line">{l s='You can add up to 5 bullets points, Up to 2000 characters per line.' mod='amazon'}</span><br/>
		<span class="amz-small-line propagation">{l s='Propagate this value to all products in this' mod='amazon'} :
			<a href="javascript:void(0)" class="amz-propagate-bulletpoint-cat amz-link">[ {l s='Category' mod='amazon'}
                ]</a>&nbsp;&nbsp;
			<a href="javascript:void(0)" class="amz-propagate-bulletpoint-shop amz-link">[ {l s='Shop' mod='amazon'}
                ]</a>&nbsp;&nbsp;
			<a href="javascript:void(0)"
               class="amz-propagate-bulletpoint-manufacturer amz-link">[ {l s='Manufacturer' mod='amazon'} ]</a>&nbsp;&nbsp;
			<a href="javascript:void(0)"
               class="amz-propagate-bulletpoint-supplier amz-link">[ {l s='Supplier' mod='amazon'} ]</a>&nbsp;&nbsp;
		</span>
        <input type="hidden" value="{l s='You can\'t add more than 5 bullet points !' mod='amazon'}"
               class="amz-text-max-bullet"/>
    </td>
</tr>