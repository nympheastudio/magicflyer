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
{if $data.type == 'variant'}{* Mostly Jewelry *}
    <div class="col-lg-9">

        <div class="variant-section">
            <label rel="variant"><span>{l s='Variant' mod='amazon'}</span></label>&nbsp;&nbsp;

            {* Variation Field - ie: size/color *}
            {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.select.inc.tpl" data=$data.fields.variant}
            <br/>

            {if isset($data.fields.variant_items)}
                <table style="margin-left:60px;">
                    {foreach from=$data.fields.variant_items item=variant_item}
                        <tr class="variant-items" rel="{$variant_item.variant_key|escape:'quotes':'UTF-8'}"
                            style="{$variant_item.style|escape:'quotes':'UTF-8'}">
                            <td style="text-align:right;padding-right:10px;width:110px;">{$variant_item.title|escape:'quotes':'UTF-8'}</td>
                            <td>
                                {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.select.inc.tpl" data=$variant_item}
                            </td>
                            <td>

                            </td>
                        </tr>
                    {/foreach}
                </table>
            {/if}

        </div>
    </div>
{elseif $data.type == 'input_and_select'}
    <div class="col-lg-9">
        <label>{$data.title|escape:'quotes':'UTF-8'}&nbsp;</label>

        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.input.inc.tpl" data=$data.fields.input}

        <span>&nbsp;&nbsp;|&nbsp;&nbsp;</span>

        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.select.inc.tpl" data=$data.fields.select}

        {if (isset($data.required) && $data.required)}
            <span class="mandatory">{l s='Required' mod='amazon'}</span>
        {/if}

    </div>
{elseif $data.type == 'simple_feature'}
    <div class="col-lg-9">
        <label>{$data.title|escape:'quotes':'UTF-8'}&nbsp;</label>

        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.select.inc.tpl" data=$data.fields.select}

    </div>
{elseif $data.type == 'input_amazon_attribute'}
    <div class="col-lg-9">
        <label>{$data.title|escape:'quotes':'UTF-8'}&nbsp;</label>

        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.input.inc.tpl" data=$data.fields.input}

        &nbsp;&nbsp;
    </div>
{elseif $data.type == 'multiple'}
    {if !$data.variation_selected}
        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.multiple.inc.tpl" data=$data}
    {/if}
{/if}

{if isset($data.fields.encoded_valid_values) && is_array($data.fields.encoded_valid_values) && count($data.fields.encoded_valid_values)}
    {foreach from=$data.fields.encoded_valid_values item=encoded_valid_value}
        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$encoded_valid_value}
    {/foreach}
{/if}

{if isset($data.fields.required) && is_array($data.fields.required) && count($data.fields.required)}
    {foreach from=$data.fields.required item=required}
        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$required}
    {/foreach}
{/if}

{if isset($data.fields.choices) && is_array($data.fields.choices) && count($data.fields.choices)}
    {foreach from=$data.fields.choices item=choices}
        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$choices}
    {/foreach}
{/if}

{if isset($data.fields.choice_allowed_values) && is_array($data.fields.choice_allowed_values) && count($data.fields.choice_allowed_values)}
    {foreach from=$data.fields.choice_allowed_values item=choice_allowed_values}
        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$choice_allowed_values}
    {/foreach}
{/if}