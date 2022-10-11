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
{if isset($data.comment)}
    <!-- {$data.comment|escape:'quotes':'UTF-8'} -->
{/if}
<select name="{$data.name|escape:'quotes':'UTF-8'}"
        {if isset($data.multiple)}multiple{/if}
        {if isset($data.class)}class="{$data.class|escape:'quotes':'UTF-8'}"{/if}
        {if isset($data.style)}style="{if isset($data.display) && !$data.display}display:none;{/if}{$data.style|escape:'quotes':'UTF-8'}"
        {else}
            {if isset($data.display) && !$data.display}style="display:none"{/if}
        {/if}
        {if isset($data.disabled)}{$data.disabled|escape:'quotes':'UTF-8'}{/if}
        {if isset($data.rel)}rel="{$data.rel|escape:'quotes':'UTF-8'}"{/if}>

    {if isset($data.title)}
        <option disabled>[{$data.title|escape:'html':'UTF-8'}]</option>{/if}
    <option disabled>--{l s='select an option' mod='amazon'}--</option>
    <option></option>

    {if is_array($data.options) && count($data.options)}
        {foreach from=$data.options key=value item=option}
            <option {if isset($option.value)}
                value="{$option.value|escape:'quotes':'UTF-8'}"{/if}  {if isset($option.disabled)}disabled{/if} {if isset($option.expert)}rel="amazon-expert-mode" class="amazon-expert-mode"{/if}
                    style="{if isset($option.disabled)}color:silver;{/if}{if isset($option.style)}{$option.style|escape:'quotes':'UTF-8'}{/if}"
                    {if isset($option.rel)}rel="{$option.rel|escape:'quotes':'UTF-8'}"{/if}
                    {if isset($option.onclick)}onclick="{$option.onclick|escape:'quotes':'UTF-8'}"{/if}
                    {if isset($option.selected) && $option.selected}selected{/if} >{$option.name|escape:'quotes':'UTF-8' nofilter}</option>
        {/foreach}
    {/if}
</select>