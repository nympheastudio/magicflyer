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
{if !$master}
    <!-- profile header -->
    <div class="profile-header" id="profile-header-{$profile_header.profile_id|intval}">
        <br/>
        <label class="profile-obj-title col-lg-3"
               style="color:navy">{$profile_header.name|escape:'htmlall':'UTF-8'}</label>

        <div class="margin-form col-lg-9">
            <table {if $psIs16}width="90%"{/if}>
                <tr>
                    <td>
                        <span class="type">{* $profile_header.type|escape:'quotes':'UTF-8' *}</span>
                    </td>
                    <td align="center" width="50px">
                        <img src="{$profiles.images_url|escape:'quotes':'UTF-8'}duplicate_1.png" class="profile-dup-img"
                             alt="{l s='Duplicate' mod='amazon'}" title="{l s='Duplicate' mod='amazon'}"
                             rel="{$profile_header.profile_id|escape:'htmlall':'UTF-8'}" style="width:32px" />
                    </td>
                    <td align="center" width="50px">
                        <img src="{$profiles.images_url|escape:'quotes':'UTF-8'}cross.png" class="profile-del-img"
                             alt="{l s='Delete' mod='amazon'}" title="{l s='Delete' mod='amazon'}"
                             rel="{$profile_header.profile_id|escape:'htmlall':'UTF-8'}"/>
                    </td>
                    <td align="center" width="50px">
                        <img src="{$profiles.images_url|escape:'quotes':'UTF-8'}edit.png" class="profile-edit-img"
                             alt="{l s='Edit' mod='amazon'}" title="{l s='Edit' mod='amazon'}"
                             rel="{$profile_header.profile_id|escape:'htmlall':'UTF-8'}"/>
                    </td>
            </table>
        </div>
    </div>
    <!-- eof profile header -->


{/if}



<!-- div profile -->
<div {if $master}id="master-profile"{/if} class="{$profile_class|escape:'htmlall':'UTF-8'} form-group"
     rel="{$profile_header.profile_id|intval}">

    <input type="hidden" name="profiles[version4]" value="1"/>

    {if $master}
        <span class="profile-del">{l s='Remove this profile from the list' mod='amazon'}<img
                    src="{$profiles.images_url|escape:'quotes':'UTF-8'}cross.png" class="profile-del-img2"/></span>
        <h2>{l s='New Profile' mod='amazon'}</h2>
        <br/>
        <div class="form-group">
            <label class="profile-obj-title col-lg-3" rel="profile_name"><span>{l s='Profile Name' mod='amazon'}</span></label>

            <div class="margin-form col-lg-9">
                <input type="text" name="profiles[name][_key_]" class="profile-name"
                       value="{$profile_header.name|escape:'quotes':'UTF-8'}"/>
            </div>
        </div>
    {else}
        <input type="hidden" name="profiles[name][_key_]" value="{$profile_header.name|escape:'quotes':'UTF-8'}"/>
    {/if}

    <!-- Marketplace Selector -->
    {if $profiles.marketplaces.show}
        <div class="margin-form col-lg-9 col-lg-offset-3 main-country-selector">
            <table class="country-selector">
                <tr>
                    {foreach from=$profiles.marketplaces.countries item=marketplace}
                        <td>
                            <span class="amazon-tab-selector{if $marketplace.default} active{/if}"
                                  rel="{$marketplace.iso_code|escape:'quotes':'UTF-8'}">
                                <img src="{$marketplace.image|escape:'quotes':'UTF-8'}"
                                     title="{$marketplace.name_long|escape:'quotes':'UTF-8'}"/><span
                                        class="name">{$marketplace.name_short|escape:'quotes':'UTF-8'}</span>
                                <input type="hidden" rel="id_lang"
                                       value="{$marketplace.id_lang|escape:'quotes':'UTF-8'}"/>
                            </span>
                        </td>
                    {/foreach}
                </tr>

            </table>

            <div class="col-lg-12 country-separator">
                <div class="amazon-tab-bar"></div>
            </div>

        </div>
    {else}
        {foreach from=$profiles.marketplaces.countries item=marketplace}
            <span style="display:none;" class="amazon-tab-selector active"
                  rel="{$marketplace.iso_code|escape:'quotes':'UTF-8'}">
                <input type="hidden" rel="id_lang" value="{$marketplace.id_lang|escape:'quotes':'UTF-8'}"/>
            </span>
        {/foreach}
    {/if}

    {foreach from=$profile_group key=id_lang item=profile}
        {if isset($profiles.marketplaces.countries[$id_lang])}
            <div class="amazon-tab amazon-tab-{$id_lang|escape:'quotes':'UTF-8'}"
                 rel="{$profiles.marketplaces.countries[$id_lang].iso_code|escape:'quotes':'UTF-8'}"
                 {if !$profiles.marketplaces.countries[$id_lang].default}style="display:none"{/if} class="col-lg-12">
                {include file="{$profiles.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/configure/profiles/profile_body.tpl" profile_key=$profile_key marketplace=$profiles.marketplaces.countries[$id_lang]}
            </div>
        {/if}
    {/foreach}

</div>
<!-- eof div profile -->