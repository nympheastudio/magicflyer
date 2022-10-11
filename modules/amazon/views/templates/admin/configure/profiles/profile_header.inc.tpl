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
{if !$version4}
    <div class="form-group">
        <div align="left" class="margin-form">
            <div class="{$info_classes.class_error|escape:'htmlall':'UTF-8'}">
                {l s='Your profile has been created for version 3, it is obsolete, please remove and recreate it.' mod='amazon'}
            </div>
        </div>
    </div>
{/if}
<input type="hidden" rel="profile_version4" value="{$version4|intval}"/>
{include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$data.universe}
{include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$data.product_type}
{include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$data.version}

{if isset($data.fields.has_valid_values) && is_array($data.fields.has_valid_values) && count($data.fields.has_valid_values)}
    {foreach from=$data.fields.has_valid_values item=has_valid_values}
        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$has_valid_values}
    {/foreach}
{/if}

{if isset($data.fields.variation) && is_array($data.fields.variation) && count($data.fields.variation)}
    <div class="col-lg-9">
        <label class="profile-obj-title tip" rel="variant"><span>{l s='Variant' mod='amazon'}</span></label>
    </div>
    {if isset($data.fields.is_variation) &&  is_array($data.fields.is_variation) && count($data.fields.is_variation)}
        {foreach from=$data.fields.is_variation item=is_variation}
            {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$is_variation}
        {/foreach}
    {/if}

    {if isset($data.fields.variation) &&  is_array($data.fields.variation) && count($data.fields.variation)}
        {foreach from=$data.fields.variation item=variation}
            {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$variation}
        {/foreach}
    {/if}
    <div class="col-lg-9 specific-field-variant">
        <label>{l s='Variant/Combination on' mod='amazon'}&nbsp;</label>
        {if isset($data.fields.variant)}
            {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.select.inc.tpl" data=$data.fields.variant}
        {/if}
        <!-- variant items container: do not remove -->

    </div>
    <div class="variant-items-container">
        {if isset($variants) && is_array($variants)}
            {foreach from=$variants item=variant}
                {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.multiple.inc.tpl" data=$variant}
            {/foreach}
        {/if}
    </div>
    <div class="margin-form col-lg-9">
        <hr style="width:100%"/>
        <label class="profile-obj-title" rel="amazon_attributes"><span>{l s='Mappings' mod='amazon'}</span></label>
    </div>

    <div class="variant-items-container-restore">
    </div>
{/if}