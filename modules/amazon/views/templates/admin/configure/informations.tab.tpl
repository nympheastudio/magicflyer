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
<div id="menudiv-informations" class="tabItem {if $informations.selected_tab}selected{/if} panel form-horizontal">

    <h3>{l s='Informations' mod='amazon'}</h3>

    {if isset($informations.module_infos) && is_array($informations.module_infos) && count($informations.module_infos)}
        <h2>{l s='Module' mod='amazon'}</h2>
        <div id="amz-module-infos" class="form-group">
            <div align="left" class="margin-form amz-info col-lg-9 col-lg-offset-3">
                {foreach from=$informations.module_infos key=env_name item=module_info}
                    <div class="{$module_info.level|escape:'quotes':'UTF-8'}"
                         style="{if (!$module_info.display)}display:none;{/if}">
                        <p>
                            {$module_info.message|escape:'html':'UTF-8'}
                        </p>
                        {if isset($module_info.tutorial) && !$amazon.is_lite}
                            <br/>
                            <pre>{l s='Please read more about it on:' mod='amazon'} {$module_info.tutorial|escape:'quotes':'UTF-8'}</pre>
                        {/if}
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}

    <h2>{l s='Marketplace' mod='amazon'}</h2>

    <input type="hidden" id="infos_ajax_error"
           value="{l s='Please verify your module configuration first.' mod='amazon'}"/>
    <br/>

    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Amazon Web Services Status' mod='amazon'}</label>{if !$psIs16}
    <br/>{/if}
        <div align="left" class="margin-form col-lg-9">

            {if ($informations.display)}
                <div id="status-loader"></div>
                <div id="status-error" class="warn {if $psIs16}alert alert-warning{/if}"
                     style="display:none;width:95%;"></div>
                <table class="table amz-seller-accounts" cellpadding="0" cellspacing="0"
                       style="width: 100%; margin-bottom:10px;">
                    <thead id="informations-table-heading" style="display:none">
                    <tr class="active">
                        <th class="left">{l s='Merchant' mod='amazon'}</th>
                        <th class="left">{l s='Platform' mod='amazon'}</th>
                        <th class="left">{l s='Time' mod='amazon'}</th>
                        <th class="left">{l s='Drift' mod='amazon'}&nbsp;<span style="color:orange">*</span></th>
                        <th class="center">{l s='Status' mod='amazon'}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr id="amz-informations-model-1" class="amz-informations-1" style="display:none;">
                        <td class="left" style="font-weight:bold;color:navy;" rel="merchant"></td>
                        <td class="left" rel="platform"></td>
                        <td class="left" rel="datetime"></td>
                        <td class="left" rel="drift"></td>
                        <td class="center" rel="status"></td>
                    </tr>
                    <tr id="amz-informations-model-2" class="amz-informations-2" style="display:none;">
                        <td colspan="5" rel="message"></td>
                    </tr>


                    </tbody>
                </table>
                <p id="informations-trailer" style="display:none"><span
                            style="color:orange">*)&nbsp;</span>{l s='This is the time drift observed between your server\'s clock and Amazon server\'s clock' mod='amazon'}
                </p>
            {else}
                <div class="hint {if $psIs16}alert alert-info{/if}">{l s='When your module will be configured, the service status will appear here.' mod='amazon'}</div>
            {/if}
        </div>
    </div>


    {if ($informations.display)}
        <div class="form-group">
            <label class="control-label col-lg-3 participation-label" style="cursor:pointer">{l s='Amazon Marketplace Status' mod='amazon'}</label>{if !$psIs16}
        <br/>{/if}
            <div align="left" class="margin-form col-lg-9">
                <div id="participation-loader"></div>
                <div id="participation-error" class="warn {if $psIs16}alert alert-warning{/if}"
                     style="display:none;width:95%;"></div>
                <table class="table amz-participation" cellpadding="0" cellspacing="0"
                       style="width: 100%; margin-bottom:10px;">
                    <thead class="active" id="participation-table-heading" style="display:none">
                    <tr class="active">
                        <th class="left">{l s='Merchant' mod='amazon'}</th>
                        <th class="left">{l s='Marketplace' mod='amazon'}</th>
                        <th class="left">{l s='CC' mod='amazon'}</th>
                        <th class="left">{l s='Currency' mod='amazon'}</th>
                        <th class="center">{l s='Local' mod='amazon'}</th>
                        <th class="center">{l s='Remote' mod='amazon'}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr id="amz-participation-model" style="display:none;">
                        <td class="left" rel="merchant"></td>
                        <td class="left" rel="name"></td>
                        <td class="left" rel="cc"></td>
                        <td class="left" rel="currency"></td>
                        <td class="center" rel="l_status"></td>
                        <td class="center" rel="r_status"></td>
                    </tr>
                    </tbody>
                </table>
                <pre id="participation-debug" style="display:none;">

                </pre>
            </div>
        </div>
    {/if}

    <hr style="width:30%"/>

    <h2>{l s='Configuration' mod='amazon'}</h2>

    <input type="hidden" id="max_input_vars" value="{$informations.max_input_vars|intval}"/>

    <div id="amz-env-infos" class="form-group" style="display:none;">
        <label class="control-label col-lg-3">{l s='Environment' mod='amazon'}</label>{if !$psIs16}<br/>{/if}

        <div align="left" class="margin-form amz-info col-lg-9">
            {if $informations.env_infos}
                {foreach from=$informations.env_infos key=env_name item=env_info}
                    <div class="{$env_info.level|escape:'quotes':'UTF-8'}"
                         id="error-{$env_info.script.name|escape:'quotes':'UTF-8'}"
                         {if !$env_info.display}style="display:none;" {else}rel="toshow"{/if}>
                        {if isset($env_info.script.url)}
                            <!-- script URL -->
                            <input type="hidden" id="{$env_info.script.name|escape:'htmlall':'UTF-8'}"
                                   value="{$env_info.script.url|escape:'quotes':'UTF-8'}"
                                   rel="{$env_name|escape:'htmlall':'UTF-8'}"/>
                        {/if}
                        <p>
                            <span>{$env_info.message|escape:'html':'UTF-8'}</span>
                            {if isset($env_info.tutorial) && !$amazon.is_lite}
                            <br/>
                            <pre>{l s='Please read more about it on:' mod='amazon'} {$env_info.tutorial|escape:'quotes':'UTF-8'}</pre>
                            {/if}
                        </p>
                    </div>
                {/foreach}
            {/if}
        </div>
    </div>

    <div class="form-group">
        {if !$amazon.is_lite}
        <label class="control-label col-lg-3">{l s='PHP Settings' mod='amazon'}</label>{if !$psIs16}<br/>{/if}


        <div align="left" class="margin-form amz-info col-lg-9">
          
                    <div class="info {if $psIs16}alert alert-info{/if}">
                          <p>
                          {l s='We recommend to apply these settings:' mod='amazon'} {$informations.tutorial|escape:'quotes':'UTF-8'}
                        </p>
                    </div>

        </div>
        {/if}


        <div align="left" class="margin-form amz-info col-lg-9 col-lg-offset-3">
            {if ! $informations.php_info_ok}
                {foreach from=$informations.php_infos item=php_info}
                    <div class="{$php_info.level|escape:'quotes':'UTF-8'}">
                        <p>
                            <span>{$php_info.message|escape:'html':'UTF-8'}</span>
                            {if isset($php_info.link) && !$amazon.is_lite}
                                <br/>
                                <span class="amz-info-link">{l s='Please read more about it on:' mod='amazon'}: <a
                                            href="{$php_info.link|escape:'html':'UTF-8'}"
                                            target="_blank">{$php_info.link|escape:'quotes':'UTF-8'}</a></span>
                            {/if}
                            {if isset($php_info.tutorial) && !$amazon.is_lite}
                            <br/>
                            <br/>
                        <pre>{l s='Please read more about it on:' mod='amazon'} {$php_info.tutorial|escape:'quotes':'UTF-8'}</pre>
                        {/if}
                        </p>
                    </div>
                {/foreach}
            {else}
                <p class="amz-info-level-ok {if $psIs16}alert alert-success{/if}">
                    <span class="amz-info-text-ok">{l s='Your PHP configuration for the module has been checked and passed successfully...' mod='amazon'}</span>
                </p>
            {/if}
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Prestashop' mod='amazon'}</label>{if !$psIs16}<br/>{/if}
        <div align="left" class="margin-form amz-info col-lg-9">
            {if ! $informations.prestashop_info_ok}
                {foreach from=$informations.prestashop_infos item=prestashop_info}
                    <div class="{$prestashop_info.level|escape:'quotes':'UTF-8'}"
                         {if isset($prestashop_info.id)}id="{$prestashop_info.id|escape:'htmlall':'UTF-8'}"{/if}>
                        <p>
                            <span>{$prestashop_info.message|escape:'quotes':'UTF-8'}</span>
                            {if isset($prestashop_info.link) && !$amazon.is_lite}
                                <br/>
                                <span class="amz-info-link">{l s='Please read more about it on:' mod='amazon'}: <a
                                            href="{$prestashop_info.link|escape:'quotes':'UTF-8'}"
                                            target="_blank">{$prestashop_info.link|escape:'quotes':'UTF-8'}</a></span>
                            {/if}
                            {if isset($prestashop_info.tutorial) && !$amazon.is_lite}
                            <br/>
                            <br/>
                            <pre>{l s='Please read more about it on:' mod='amazon'} {$prestashop_info.tutorial|escape:'quotes':'UTF-8'}</pre>
                            {/if}
                        </p>
                    </div>
                {/foreach}
            {else}
                <p class="amz-info-level-ok {if $psIs16}alert alert-success{/if}">
                    <span class="amz-info-text-ok">{l s='Your Prestashop configuration for the module has been checked and passed successfully...' mod='amazon'}</span>
                </p>
            {/if}
        </div>
    </div>

    <!-- Support section -->
    <h2>{l s='Support' mod='amazon'}</h2>
    <div id="" class="form-group">
        <label class="control-label col-lg-3"></label>
        <!-- Show loader after load -->
        <div class="margin-form col-lg-9" id="support-information-file-loader" >
            <img src="{$informations.images|escape:'htmlall':'UTF-8'}loading.gif" alt="{l s='Support Information' mod='amazon'}"/>
        </div>
        <div class="margin-form col-lg-9" id="support-information-download">
            <a href="#" target="_blank" class="support-url"
               rel="{$informations.support_informations_url|escape:'quotes':'UTF-8'}&action=support-info">
                <img src="{$informations.images|escape:'htmlall':'UTF-8'}/zip64.png" class="support-file" title="Support Details" />
                <span>{l s='Download' mod='amazon'}</span>
            </a><br/>
            <p><em>{l s='This file contains support informations we would need for a faster diagnosis' mod='amazon'}</em></p>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3">&nbsp;</label>{if !$psIs16}<br/>{/if}
        <div align="left" class="margin-form amz-info col-lg-9">
            <input type="button" class="button btn" id="support-information-prestashop"
                   value="{l s='Prestashop Info' mod='amazon'}"
                   rel="{$informations.support_informations_url|escape:'quotes':'UTF-8'}&action=prestashop-info"/>&nbsp;&nbsp;
            <input type="button" class="button btn" id="support-information-php" value="{l s='PHP Info' mod='amazon'}"
                   rel="{$informations.support_informations_url|escape:'quotes':'UTF-8'}&action=php-info"/>&nbsp;&nbsp;
            <span rel="amazon-expert-mode" class="amazon-expert-mode">
                <input type="hidden" id="mode_dev-status" value="{if !$informations.mode_dev}1{else}0{/if}"/>
                <input type="hidden" id="mode_dev-status-on" value="{l s='Switch On DEV_MODE' mod='amazon'}"/>
                <input type="hidden" id="mode_dev-status-off" value="{l s='Switch Off DEV_MODE' mod='amazon'}"/>
                <input type="button" class="button btn" id="support-mode_dev"
                       {if !$informations.mode_dev}value="{l s='Switch On DEV_MODE' mod='amazon'}"
                       {else}value="{l s='Switch Off DEV_MODE' mod='amazon'}"{/if}
                       rel="{$informations.support_informations_url|escape:'quotes':'UTF-8'}&action=mode-dev"/>&nbsp;&nbsp;
            </span>
            <img src="{$informations.images|escape:'html':'UTF-8'}loader-connection.gif"
                 alt="{l s='Support Information' mod='amazon'}" class="support-information-loader"/><br/><br/>

            <div id="devmode-response">
                <div id="devmode-response-success" class="{$class_success|escape:'htmlall':'UTF-8'}"
                     style="display: none;"></div>
                <div id="devmode-response-danger" class="{$class_error|escape:'htmlall':'UTF-8'}"
                     style="display: none;"></div>
            </div>

            <!-- PS info / Php info detail go here -->
            <div id="support-information-content"></div>
        </div>
    </div>
    <!-- End Support section -->

</div><!-- menudiv-informations -->