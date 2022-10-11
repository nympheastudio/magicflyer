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
 * @author    Tran Pham
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * Support by mail:  support.amazon@common-services.com
*}

{l s='Marketplace Order ID: %s' mod='amazon' sprintf=$mp_order_id pdf='true'}
<br><br>

{if is_array($customizations) && count($customizations)}
    <b>{l s='Customization:' mod='amazon' pdf='true'}</b>
    <ul>
        {foreach from=$customizations key="sku" item="customization"}
            <li>
                {$sku|escape:'htmlall':'UTF-8'}:

                <ul>
                    {foreach from=$customization item="customization_item"}
                        {*Get display value: $optionValue or $text*}
                        {if isset($customization_item.optionValue)}
                            {assign var=customizationValue value=$customization_item.optionValue}
                        {elseif isset($customization_item.text)}
                            {assign var=customizationValue value=$customization_item.text}
                        {else}
                            {assign var=customizationValue value=""}
                        {/if}

                        <li>{$customization_item.label|escape:'htmlall':'UTF-8'}: {$customizationValue|escape:'htmlall':'UTF-8'}</li>
                    {/foreach}
                </ul>
            </li>
        {/foreach}
    </ul>
{/if}