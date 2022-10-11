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
<div class="col-lg-5 specific-field-data{if (isset($data.optionnal) && $data.optionnal)} optionnal{/if} {if (isset($data.class) && $data.class)}{$data.class|escape:'quotes':'UTF-8'}{/if}"
     {if (isset($data.rel) && $data.rel)}rel="{$data.rel|escape:'quotes':'UTF-8'}"{/if} >
    {if $data.tip}
        <label class="tip" title="{$data.tip|escape:'html':'UTF-8'}"><span>{$data.title|escape:'html':'UTF-8'}
                &nbsp;</span></label>
    {elseif $data.tip2}
        <label class="tip2" title="{$data.tip2|escape:'html':'UTF-8'}"><span>{$data.title|escape:'html':'UTF-8'}
                &nbsp;</span></label>
    {else}
        <label>{$data.title|escape:'html':'UTF-8'}&nbsp;</label>
    {/if}

    {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$data.fields.hidden}

    {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.select.inc.tpl" data=$data.fields.multiple}

    {if isset($data.fields.default)}
        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.input.inc.tpl" data=$data.fields.default}
    {/if}

    {if isset($data.fields.allowed) && is_array($data.fields.allowed) && count($data.fields.allowed)}
        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.select.inc.tpl" data=$data.fields.allowed}
    {/if}

    {if isset($data.choice_data)}
        {include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.select.inc.tpl" data=$data.choice_data}
    {/if}
   
    {if isset($data.choices_required)}
	{include file="{$data.path|escape:'quotes':'UTF-8'}/configure/helpers/specific_field.hidden.inc.tpl" data=$data.choices_required}
    {/if}

    {if (isset($data.variation) && $data.variation)}
        <span class="variant">{l s='Variant' mod='amazon'}</span>
    {/if}

    {if (isset($data.required) && $data.required)}
        <span class="mandatory">{l s='Required' mod='amazon'}</span>
    {elseif (isset($data.recommended) && $data.recommended)}
        <span class="recommended">{l s='Recommended' mod='amazon'}</span>
    {elseif (isset($data.choice) && $data.choice)}
        <span class="choice">{l s='Choice' mod='amazon'}</span>
    {elseif (isset($data.preferred) && $data.preferred)}
        <span class="preferred">{l s='Preferred' mod='amazon'}</span>
    {elseif (isset($data.featured) && $data.featured)}
        <span class="featured">{l s='Featured' mod='amazon'}</span>
    {/if}

    {if (isset($data.sample) && $data.sample)}
        <span class="sample">{$data.sample|escape:'html':'UTF-8'}</span>
    {/if}
</div>