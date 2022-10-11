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
{if ($display_header)}
<div class="margin-form col-lg-9 col-lg-offset-1 specific-field-attributes">
{/if}
    {if isset($data.fields.attributes)}
        {if ($display_header)}
        <div style="color:navy;margin-bottom:10px;">
            {l s='You may need to fill those additionnal parameters' mod='amazon'}:
        </div>
        {/if}
        <!-- Fields Attributes : eg - Diameter, Attribute = unitOfMeasure CM -->
        {foreach from=$data.fields.attributes item=field_attribute}
            <div class="profile-amazon-unit"
                 {if isset($field_attribute.rel)}rel="{$field_attribute.rel|escape:'quotes':'UTF-8'}"{/if} {if isset($field_attribute.display) && !$field_attribute.display}style="display:none;"{/if}>
                <label>{$field_attribute.title|escape:'quotes':'UTF-8'}&nbsp;</label>
                {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.select.inc.tpl" data=$field_attribute}
                <span class="mandatory">{l s='Required' mod='amazon'}</span>
            </div>
        {/foreach}
        <!-- ! Fields Attributes -->
    {/if}
{if ($display_header)}
</div>
{/if}