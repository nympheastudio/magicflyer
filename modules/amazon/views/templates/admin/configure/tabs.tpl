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
<!-- Tabs -->
<ul class="nav" id="menuTab">
    <li id="menu-amazon" class="menuTabButton {$tabs.amazon_selected|escape:'htmlall':'UTF-8'}"><a href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}a32.png" title="amazon"/>&nbsp;Amazon</span></a>
    </li>
    <li id="menu-informations" class="menuTabButton {$tabs.informations_selected|escape:'htmlall':'UTF-8'}"><a href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/information.png"
                        title="{$tabs.informations|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.informations|escape:'htmlall':'UTF-8'}</span></a>
    </li>

    <li id="menu-features" class="menuTabButton {$tabs.features_selected|escape:'htmlall':'UTF-8'}"><a href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/bricks.png"
                        title="{$tabs.features|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.features|escape:'htmlall':'UTF-8'}</span></a>
    </li>

    {foreach from=$tabs.platforms item=marketplace}
        {assign var="platformClass" value="menuTabButton "|cat:$marketplace.selected|escape:'htmlall':'UTF-8'}
        <li id="menu-{$marketplace.iso_code|escape:'htmlall':'UTF-8'}"
            {if !$marketplace.display}style="display:none"{/if}
            {if ($marketplace.area == 'eu' && !$marketplace.display)}rel="amazon-europe" class="{$platformClass|cat:' amazon-europe'}"{elseif ($marketplace.area == 'ww' && !$marketplace.display)}rel="amazon-worldwide" class="{$platformClass|cat:' amazon-worldwide'|escape:'htmlall':'UTF-8'}"{else}class="{$platformClass|escape:'htmlall':'UTF-8'}"{/if}>
            <a href="#"><span>&nbsp;<img
                            src="{$tabs.images_url|escape:'quotes':'UTF-8'}geo_flags_web2/flag_{$marketplace.geo_flag|escape:'htmlall':'UTF-8'}_32px.png"
                            title="{$marketplace.name_long|escape:'htmlall':'UTF-8'}"/>&nbsp;{$marketplace.name_short|escape:'htmlall':'UTF-8'}</span></a>
        </li>
    {/foreach}
    <li id="menu-parameters" class="menuTabButton {$tabs.parameters_selected|escape:'htmlall':'UTF-8'}"><a
                href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/params.png"
                        title="{$tabs.parameters|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.parameters|escape:'htmlall':'UTF-8'}</span></a>
    </li>
    <li id="menu-categories" class="menuTabButton {$tabs.categories_selected|escape:'htmlall':'UTF-8'}"><a
                href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/categories.png"
                        title="{$tabs.categories|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.categories|escape:'htmlall':'UTF-8'}</span></a>
    </li>

    <li id="menu-profiles" rel="amazon-products-creation"
        class="menuTabButton {$tabs.profiles_selected|escape:'htmlall':'UTF-8'} amazon-products-creation" style="display:none"><a href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/profiles.png"
                        title="{$tabs.profiles|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.profiles|escape:'htmlall':'UTF-8'}</span></a>
        <img src="{$tabs.images_url|escape:'quotes':'UTF-8'}green-loader.gif" class="profiles-loader"
             title="{l s='Loading Profiles' mod='amazon'}"/>
    </li>

    <li id="menu-mapping" rel="amazon-products-creation"
        class="menuTabButton {$tabs.mapping_selected|escape:'htmlall':'UTF-8'} amazon-products-creation" style="display:none"><a href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/mapping.png"
                        title="{$tabs.mapping|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.mapping|escape:'htmlall':'UTF-8'}</span></a>
    </li>

    <li id="menu-shipping" class="amazon-shipping menuTabButton {$tabs.shipping_selected|escape:'htmlall':'UTF-8'}"
        rel="amazon-shipping" style="display: none"><a href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/lorry.png"
                        title="{$tabs.shipping|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.shipping|escape:'htmlall':'UTF-8'}</span></a>
    </li>

    <li id="menu-filters" rel="amazon-filters" class="amazon-filters menuTabButton {$tabs.filters_selected|escape:'htmlall':'UTF-8'}"
        style="display:none"><a href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/filter.png"
                        title="{$tabs.filters|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.filters|escape:'htmlall':'UTF-8'}</span></a>
    </li>

    <li id="menu-messaging" class="amazon-messaging menuTabButton {$tabs.messaging_selected|escape:'htmlall':'UTF-8'}"
        rel="amazon-messaging" style="display: none"><a href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/mail.png"
                        title="{$tabs.messaging|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.messaging|escape:'htmlall':'UTF-8'}</span></a>
    </li>


    <li id="menu-fba" class="amazon-fba menuTabButton {$tabs.fba_selected|escape:'htmlall':'UTF-8'}" rel="amazon-fba"
        style="display: none"><a href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/world.png"
                        title="{$tabs.fba|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.fba|escape:'htmlall':'UTF-8'}</span>
        </a></li>

    {if isset($tabs.repricing)}
        <li id="menu-repricing" class="amazon-repricing menuTabButton {$tabs.fba_selected|escape:'htmlall':'UTF-8'}"
            rel="amazon-repricing" style="display: none"><a href="#"><span>&nbsp;<img
                            src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/repricing.png"
                            title="{$tabs.repricing|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.repricing|escape:'htmlall':'UTF-8'}</span>
            </a></li>
    {/if}

    <li id="menu-tools" class="menuTabButton {$tabs.tools_selected|escape:'htmlall':'UTF-8'}" rel="amazon-tools"><a
                href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/tools.png"
                        title="{$tabs.tools|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.tools|escape:'htmlall':'UTF-8'}</span></a>
    </li>

    {if isset($cron.display) && $cron.display}
        <li id="menu-cron" class="menuTabButton {$tabs.cron_selected|escape:'htmlall':'UTF-8'}"><a href="#"><span>&nbsp;<img
                            src="{$tabs.images_url|escape:'quotes':'UTF-8'}tabs/clock.png"
                            title="{$tabs.cron|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.cron|escape:'htmlall':'UTF-8'}</span></a>
        </li>
    {/if}

    <li id="menu-debug" class="menuTabButton {$tabs.cron_selected|escape:'htmlall':'UTF-8'}" rel="amazon-debug-mode"
        style="display:none"><a href="#"><span>&nbsp;<img
                        src="{$tabs.images_url|escape:'quotes':'UTF-8'}bug.png"
                        title="{$tabs.debug|escape:'htmlall':'UTF-8'}"/>&nbsp;{$tabs.debug|escape:'htmlall':'UTF-8'}</span></a>
    </li>

</ul>
<div id="ps16_tabs_separator"></div>
<!-- End Of Tabs -->