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
<div id="menudiv-profiles"
     class="tabItem {$profiles.selected_tab|escape:'htmlall':'UTF-8'} panel form-horizontal{if !$psIs16} ps15{/if}">

    <h3>{l s='Profiles' mod='amazon'}</h3>

    <input type="hidden" id="xsd_path" value="{$profiles.xsd_path|escape:'html':'UTF-8'}"/>
    <input type="hidden" id="xsd_operations_url" value="{$profiles.xsd_operations_url|escape:'html':'UTF-8'}"/>
    <input type="hidden" id="xsd_ajax_error" value="{$profiles.xsd_ajax_error|escape:'html':'UTF-8'}"/>
    <input type="hidden" id="error_profile_name" value="{$profiles.error_profile_name|escape:'html':'UTF-8'}"/>
    <input type="hidden" id="text-add-browsenodes" value="{l s='Browse Node (max: 2)' mod='amazon'}"/>

    {if !$amazon.is_lite}
    <div class="form-group">
        <div class="margin-form">
            <div class="amz-info-level-info {if $psIs16}alert alert-info col-lg-offset-3{/if}" style="font-size:1.1em">
                <ul>
                    <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                    <li>{$profiles.tutorial|escape:'quotes':'UTF-8'}</li>
                </ul>
            </div>
        </div>
    </div>
    {/if}

    <!-- profile copy -->
    <div class="profile-copy" style="display:none;">
        <span class="profile-del">{l s='Remove this profile from the list' mod='amazon'}<img
                    src="{$profiles.images_url|escape:'quotes':'UTF-8'}cross.png" class="profile-del-img2"/></span>
        <h2>{l s='Profile Copy' mod='amazon'}</h2>
        <br/>
        <div class="form-group">
            <label class="profile-obj-title col-lg-3" rel="profile_name"><span>{l s='Profile Name' mod='amazon'}</span></label>

            <div class="margin-form col-lg-9">
                <input type="text" name="profiles[name][_key_]" class="profile-name" rel=" ({l s='Copy' mod='amazon'})" />
            </div>
        </div>
    </div>
    <!-- eof profile copy -->

    {if isset($profiles.empty_profile) && is_array($profiles.empty_profile) && count($profiles.empty_profile)}
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Profiles Configuration' mod='amazon'}</label>

            <div class="margin-form" class="profiles">
                <div id="profile-add">
                    <span class="profile-add-img"><img
                                src="{$profiles.images_url|escape:'quotes':'UTF-8'}add.png"/></span>
                    <span class="profile-add">{l s='Add a profile to the list' mod='amazon'}</span><br>
                </div>

            </div>
            <br/>

            <div class="form-group">
                <div class="margin-form col-lg-offset-3" style="margin-top:20px;">
                    <div class="error alert alert-danger" id="ajax-error" style="display:none">

                    </div>
                </div>
            </div>
        </div>
        <!-- Container to receive the new profiles -->
        <div id="profile-container"></div>
        <!-- stored profiles -->
        <div id="profile-items">
            {include file="{$profiles.module_path}/views/templates/admin/configure/profiles/profile.tpl" profile_header=$profiles.empty_profile_header profile_key='' profile_group=$profiles.empty_profile profile_class="profile-create master-profile" master=1}

            {if isset($profiles.header) && is_array($profiles.header) && count($profiles.header)}
                {foreach from=$profiles.header key=profile_key item=profile_header}
                    {include file="{$profiles.module_path}/views/templates/admin/configure/profiles/profile.tpl" profile_group=$profiles.config[$profile_key] profile_key=$profile_key profile_class="profile profile-stored" master=0}
                {/foreach}
            {/if}
        </div>
        <!-- eof div profile items -->
    {else}
        <div class="form-group">
            <div class="margin-form col-lg-offset-3 col-lg-9 {$class_warning|escape:'htmlall':'UTF-8'}">
                {l s='No profiles to display' mod='amazon'}:</li>
                <ul>
                    <li>{l s='Profiles section appears once the module is configured.' mod='amazon'}</li>
                </ul>
            </div>
        </div>
        <div class="form-group">
            <hr class="amz-separator" style="width:30%"/>
        </div>
    {/if}

    {$profiles.validation|escape:'quotes':'UTF-8'}
</div>