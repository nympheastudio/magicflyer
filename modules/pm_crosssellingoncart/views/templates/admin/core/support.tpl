{**
 * pm_crosssellingoncart
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2017 - http://www.presta-module.com
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *}

<div class="clearfix"></div>
<div class="row">
    <div class="col-xs-12">
        <div id="pm_support_informations" class="panel">
            <h2>{l s='Useful links' mod='pm_crosssellingoncart'}</h2>

            <ul class="pm_links_block">
                <li class="pm_module_version"><strong>{l s='Module Version: ' mod='pm_crosssellingoncart'}</strong> {$pm_module_version|escape:'htmlall':'UTF-8'}</li>

            {if isset($support_links) && $support_links|is_array && $support_links|sizeof}
                {foreach from=$support_links item=support_link}
                    <li class="pm_useful_link"><a href="{$support_link.link|escape:'htmlall':'UTF-8'}" target="_blank" class="pm_link">{$support_link.label|escape:'htmlall':'UTF-8'}</a></li>
                {/foreach}
            {/if}
            </ul>

            {if isset($copyright_link) && $copyright_link|is_array && $copyright_link|sizeof}
                <div class="pm_copy_block">
                {if (isset($copyright_link.link) && $copyright_link.link != '')}
                    <a href="{$copyright_link.link|escape:'htmlall':'UTF-8'}"{if isset($copyright_link.target)} target="{$copyright_link.target|escape:'htmlall':'UTF-8'}"{/if}{if isset($copyright_link.style)} style="{$copyright_link.style|escape:'htmlall':'UTF-8'}"{/if}
                    >
                {/if}
                <img src="{$copyright_link.img|escape:'htmlall':'UTF-8'}" />
                {if (isset($copyright_link.link) && $copyright_link.link != '')}
                    </a>
                {/if}
                </div>
            {/if}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        {include file="./cs-addons.tpl"}
    </div>
</div>