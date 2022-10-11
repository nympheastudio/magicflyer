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
<div id="menudiv-shipping" class="tabItem {if $shipping.selected_tab}selected{/if} panel form-horizontal">

    <input type="hidden" id="amazon_shipping_url" value="{$shipping.shipping_url|escape:'quotes':'UTF-8'}"/>
    <input type="hidden" id="shiping_ajax_error"
           value="{l s='An unexpected server side error occurs, please verify your module configuration first.' mod='amazon'}"/>

    <h3>{l s='Shipping' mod='amazon'}</h3>

    <div class="form-group" rel="amazon-shipping-templates">
        <label class="control-label col-lg-3" rel="shipping_templates"><span>{l s='Shipping Templates' mod='amazon'}</span><span class="experimental">{l s='New' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="shipping[shipping_templates]" id="shipping-template-switch-on" value="1" {if $shipping.shipping_templates.enabled}checked{/if} />
                    <label for="shipping-template-switch-on" class="label-checkbox">{l s='Yes' mod='amazon'}</label>
                    <input type="radio" name="shipping[shipping_templates]" id="shipping-template-switch-off" value="0" {if !$shipping.shipping_templates.enabled}checked{/if} />
                    <label for="shipping-template-switch-off" class="label-checkbox">{l s='No' mod='amazon'}</label>
                    <a class="slide-button btn"></a>
                </span>
        </div>
    </div>


    <div class="form-group" id="shipping-templates" {if !$shipping.shipping_templates.enabled}style="display:none"{/if}>
    <!-- Marketplace Selector -->
    {if $shipping.marketplaces.show}
        {if !$amazon.is_lite}
        <div class="margin-form">
            <div class="amz-info-level-info {if $psIs16}alert alert-info col-lg-offset-3{/if}" style="font-size:1.1em">
                <ul>
                    <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                    <li>{$shipping.tutorial|escape:'quotes':'UTF-8'}</li>
                </ul>
            </div>
        </div>
        {/if}

        <div class="form-group" style="margin-bottom:0;">
            <div class="margin-form col-lg-9 col-lg-offset-3">
                <table class="country-selector">
                    <tr>
                        {foreach from=$shipping.marketplaces.countries item=marketplace}
                            <td>
                            <span class="amazon-tab-selector{if $marketplace.default} active{/if}" rel="{$marketplace.iso_code|escape:'quotes':'UTF-8'}">
                                <img src="{$marketplace.image|escape:'quotes':'UTF-8'}" title="{$marketplace.name_long|escape:'quotes':'UTF-8'}"/>
                                <span class="name">{$marketplace.name_short|escape:'quotes':'UTF-8'}</span>
                                <input type="hidden" rel="id_lang" value="{$marketplace.id_lang|escape:'quotes':'UTF-8'}"/>
                            </span>
                            </td>
                        {/foreach}
                    </tr>
                </table>

                <div class="col-lg-9 country-separator">
                    <div class="amazon-tab-bar"></div>
                </div>
            </div>

        </div>
    {else}
        {foreach from=$shipping.marketplaces.countries item=marketplace}
            <span style="display:none;" class="amazon-tab-selector active"
                  rel="{$marketplace.iso_code|escape:'quotes':'UTF-8'}">
                <input type="hidden" rel="id_lang" value="{$marketplace.id_lang|escape:'quotes':'UTF-8'}"/>
            </span>
        {/foreach}
    {/if}


        {foreach from=$shipping.marketplaces.countries item=marketplace}
        <div class="amazon-tab amazon-tab-{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
             rel="{$marketplace.iso_code|escape:'quotes':'UTF-8'}" {if !$marketplace.default}style="display:none"{/if}>

            <div class="form-group">
                <div class="margin-form col-lg-9">
                    <div class="current-country">
                        <img src="{$marketplace.image|escape:'quotes':'UTF-8'}" title="{$marketplace.name_long|escape:'quotes':'UTF-8'}"/><span class="name">{$marketplace.name_short|escape:'quotes':'UTF-8'}</span>
                    </div>
                </div>
            </div>


            <label class="control-label col-lg-3" rel="shipping_groups"><span>{l s='Group Name/s' mod='amazon'}</span></label>

            <div class="form-group available-shipping-groups-container" {if (!isset($shipping.shipping_templates.groups[$marketplace.region]) || !is_array($shipping.shipping_templates.groups[$marketplace.region]))}style="display:none;"{/if}>
                <div align="left" class="margin-form col-lg-offset-3">
                    <select class="available-shipping-groups" rel="{$marketplace.id_lang|intval}" multiple="multiple"  {if (!isset($shipping.shipping_templates.groups[$marketplace.region]) || !is_array($shipping.shipping_templates.groups[$marketplace.region]))}style="display:none;"{/if}>
                        <option value="0" disabled style="color:silver;">{l s='Available Shipping Groups' mod='amazon'}</option>
                        {if (isset($shipping.shipping_templates.groups[$marketplace.region]) && is_array($shipping.shipping_templates.groups[$marketplace.region]))}
                            {foreach from=$shipping.shipping_templates.groups[$marketplace.region] key=key item=name}
                                <option value="{$key|escape:'htmlall':'UTF-8'}">{$name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        {/if}
                    </select>
                    <div class="clearfix"></div>
                    <p>{l s='This list is displayed for information purpose' mod='amazon'}</p>
                </div>
            </div>

            <div class="form-group">
                <div align="left" class="margin-form col-lg-offset-3">
                    <input type="button" class="button btn shipping-groups-get"
                           id="shipping-groups-get-{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                           rel="{$marketplace.id_lang|intval}"
                           value="{l s='Update Groups Names from Amazon' mod='amazon'}"/>

                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}loader-connection.gif"
                         class="shipping-groups-loader" style="display:none"/>
                </div>
            </div>

            <div class="form-group">
                <div align="left" class="margin-form col-lg-offset-3">
                    <div class="{$class_success|escape:'htmlall':'UTF-8'} shipping-groups-success"
                         style="display:none">
                    </div>

                    <div class="{$class_warning|escape:'htmlall':'UTF-8'} shipping-groups-warning"
                         style="display:none">
                    </div>

                    <div class="{$class_error|escape:'htmlall':'UTF-8'} shipping-groups-error"
                         style="display:none">
                    </div>

                    <pre class="shipping-groups-debug"
                         style="display:none">
                    </pre>
                </div>
            </div>


        </div>
        {/foreach}

    <div class="form-group">
        <hr class="amz-separator"/>
    </div>
    </div><!-- ship by region -->

    {if $shipping.expert_mode}
    <div class="form-group">
        <label class="control-label col-lg-3" rel="shipping_tare"><span>{l s='Reverse Tare' mod='amazon'}</span><sup class="experimental">{l s='New' mod='amazon'}</sup></span></label>

        <div class="margin-form col-lg-9">
            <input type="text" name="shipping[tare]" class="shipping-tare" style="width:100px; display: inline;" value="{$shipping.tare|escape:'htmlall':'UTF-8'}"/>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" rel="shipping_gauge"><span>{l s='Reverse Gauge' mod='amazon'}</span><sup class="experimental">{l s='New' mod='amazon'}</sup></span></label>

        <div class="margin-form col-lg-9">
            <input type="text" name="shipping[gauge]" class="shipping-gauge" style="width:100px; display: inline;" value="{$shipping.gauge|escape:'htmlall':'UTF-8'}"/>
        </div>
    </div>

    <div class="form-group">
        <hr class="amz-separator"/>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" rel="exclusive_override"><span>{l s='Allow Exclusive Shipping Charges Overrides' mod='amazon'}</span><sup class="expert">{l s='Deprecated' mod='amazon'}</sup></span></label>

        <div class="margin-form col-lg-9">
            {*<input type="checkbox" name="shipping[allow_overrides]" value="1" {if $shipping.allow_overrides}checked{/if} />&nbsp;<span class="span_text">&nbsp;{l s='Yes' mod='amazon'}</span><br />*}
            <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="shipping[allow_overrides]" id="allow_override_1" value="1"
                           {if $shipping.allow_overrides}checked{/if} /><label for="allow_override_1"
                                                                               class="label-checkbox">{l s='Yes' mod='amazon'}</label>
                    <input type="radio" name="shipping[allow_overrides]" id="allow_override_2" value="0"
                           {if !$shipping.allow_overrides}checked{/if} /><label for="allow_override_2"
                                                                                class="label-checkbox">{l s='No' mod='amazon'}</label>
                    <a class="slide-button btn"></a>
                </span>


        </div>
    </div>
    {/if}


    <div class="form-group">
        <label class="control-label col-lg-3"
               rel="smartshipping"><span>{l s='Smart Shipping' mod='amazon'}</span><sup class="expert">{l s='Deprecated' mod='amazon'}</sup></label>
        {*<div class="margin-form col-lg-9">
            <input type="checkbox" name="shipping[smart_shipping][active]" id="smart-shipping-active" value="1" {if $shipping.smart_shipping.active}checked{/if} />&nbsp;<span class="span_text">&nbsp;{l s='Yes' mod='amazon'}</span><br />
            <p>{l s='Use "Smart Shipping" facility. This options allows to calculate per product shipping fees based on weight' mod='amazon'}<br />
            </p>
        </div>*}
        <div class="margin-form col-lg-9">
        <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" name="shipping[smart_shipping][active]" id="smart-shipping-active" value="1"
                   {if $shipping.smart_shipping.active}checked{/if} /><label for="smart-shipping-active"
                                                                             class="label-checkbox">{l s='Yes' mod='amazon'}</label>
            <input type="radio" name="shipping[smart_shipping][active]" id="smart-shipping-active-2" value="0"
                   {if !($shipping.smart_shipping.active)}checked{/if} /><label for="smart-shipping-active-2"
                                                                                class="label-checkbox">{l s='No' mod='amazon'}</label>
            <a class="slide-button btn"></a>
        </span>


        </div>
    </div>

    {if isset($shipping.smart_shipping.mapping)}
    <div id="smart-shipping" class="form-group"
         {if ( ! isset($shipping.smart_shipping.active) || ! $shipping.smart_shipping.active )}style="display:none;"{/if}>
        <label class="control-label col-lg-3"
               rel="kind_override"><span>{l s='Kind of Override' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9">
            <input type="radio" name="shipping[smart_shipping][kind]"
                   value="{$shipping.smart_shipping.kind.additive|escape:'htmlall':'UTF-8'}"
                   {if $shipping.smart_shipping.kind.value == $shipping.smart_shipping.kind.additive}checked{/if} />&nbsp;<span
                    class="span_text">&nbsp;{l s='Additive' mod='amazon'}</span>&nbsp;&nbsp;&nbsp;
            <input type="radio" name="shipping[smart_shipping][kind]"
                   value="{$shipping.smart_shipping.kind.exclusive|escape:'htmlall':'UTF-8'}"
                   {if $shipping.smart_shipping.kind.value == $shipping.smart_shipping.kind.exclusive}checked{/if} />&nbsp;<span
                    class="span_text">&nbsp;{l s='Exclusive' mod='amazon'}</span>


        </div>
        {if $shipping.smart_shipping.mapping}
            <label class="control-label col-lg-3">{l s='Mapping' mod='amazon'}</label>
            <div class="margin-form smart-shipping">
                {foreach from=$shipping.smart_shipping.mapping key=shipping_method item=selected_id_carrier}
                    <select name="shipping[smart_shipping][prestashop][{$shipping_method|escape:'htmlall':'UTF-8'}]"
                            style="position:relative;top:+2px;padding:1px;width:250px;">
                        <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                        <option value="0"></option>

                        {foreach from=$shipping.carriers key=id_carrier item=carrier_name}
                            <option value="{$id_carrier|intval}"
                                    {if $id_carrier == $selected_id_carrier}selected{/if}>{$carrier_name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <span><img src="{$settings.images_url|escape:'quotes':'UTF-8'}next.png"
                               style="max-height:16px;opacity:0.5"
                               alt=""/></span>
                    <input type="text"
                           name="shipping[smart_shipping][amazon][{$shipping_method|escape:'htmlall':'UTF-8'}]"
                           value="{$shipping_method|escape:'htmlall':'UTF-8'}" style="width:250px;"/>
                    <br/>
                {/foreach}
                <br/>
            </div>
        {else}
            <label class="control-label col-lg-3">&nbsp;</label>
            <div class="margin-form col-lg-9">
                <div class="info alert alert-info">
                    {l s='To be able to configure this, you must configure the "Default Shipping Method" in your marketplace localized tabs.' mod='amazon'}
                    <br/>
                    {l s='More details about this feature on our blog' mod='amazon'}: <a
                            href="http://blog.common-services.com/amazon-marketplace-frais-de-port-bases-sur-le-poids/"
                            target="_blank">http://blog.common-services.com/amazon-marketplace-frais-de-port-bases-sur-le-poids/</a>
                </div>
            </div>
        {/if}
    </div>
    {else}
    <div id="smart-shipping" class="form-group">
        <label class="control-label col-lg-3">&nbsp;</label>

        <div class="margin-form col-lg-9">
            <div class="info alert alert-info">
                {l s='To be able to configure this, you must configure the "Default Shipping Method" in your marketplace localized tabs.' mod='amazon'}
                <br/>
                {l s='More details about this feature on our blog' mod='amazon'}: <a
                        href="http://blog.common-services.com/amazon-marketplace-frais-de-port-bases-sur-le-poids/"
                        target="_blank">http://blog.common-services.com/amazon-marketplace-frais-de-port-bases-sur-le-poids/</a>
            </div>
        </div>
    </div>
    {/if}
    {if !$psIs16}
    <hr class="amz-separator"/>
    {/if}
    <!-- validation button -->
    {$shipping.validation|escape:'quotes':'UTF-8'}

</div>