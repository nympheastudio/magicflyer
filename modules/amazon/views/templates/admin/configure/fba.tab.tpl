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
<div id="menudiv-fba" class="tabItem {if $fba.selected_tab}selected{/if} panel form-horizontal">
    <h3>{l s='Fulfillment By Amazon' mod='amazon'}</h3>

    <input type="hidden" id="amazon_stock_init_url" value="{$fba.init_stock_url|escape:'quotes':'UTF-8'}"/>
    <input type="hidden" id="stock_init_ajax_error"
           value="{l s='An unexpected server side error occurs, please verify your module configuration first.' mod='amazon'}"/>
    {if !$amazon.is_lite}
    <div class="margin-form">
        <div class="amz-info-level-info {if $psIs16}alert alert-info col-lg-offset-3{/if}" style="font-size:1.1em">
            <ul>
                <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                <li>{$fba.tutorial|escape:'quotes':'UTF-8'}</li>
            </ul>
        </div>
    </div>
    {/if}

    <div class="form-group">
        <label class="control-label col-lg-3" rel="fba"><span>{l s='FBA Price formula' mod='amazon'}</span></label>

        <div align="left" class="margin-form col-lg-9">
            <input type="text" name="fba_formula" id="fba_formula" value="{$fba.formula|escape:'htmlall':'UTF-8'}"/>&nbsp;

        </div>
    </div>

    <hr class="amz-separator"/>

    <div class="form-group">
        <label class="control-label col-lg-3"
               rel="multi_channel"><span>{l s='Multi-Channel FBA' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9">
            <input type="hidden" id="text_multichannel" value="{l s='Please save the configuration to display the Multi-channel options' mod='amazon'}"/>
        <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" name="fba_multichannel" id="fba_multichannel" value="1" {if ($fba.multichannel)}checked{/if} /><label for="fba_multichannel" class="label-checkbox">{l s='Yes' mod='amazon'}</label>
            <input type="radio" name="fba_multichannel" id="fba_multichannel-2" value="0" {if !($fba.multichannel)}checked{/if} /><label for="fba_multichannel-2" class="label-checkbox">{l s='No' mod='amazon'}</label>
            <a class="slide-button btn"></a>
        </span>
        </div>
    </div>

    <br/>

    <div class="form-group">
        <label class="control-label col-lg-3"
               rel="multi_channel_auto"><span>{l s='Automatic Multi-Channel' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9">
        <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" name="fba_multichannel_auto" id="fba_multichannel_auto" value="1" {if ($fba.multichannel_auto)}checked{/if} /><label for="fba_multichannel_auto" class="label-checkbox">{l s='Yes' mod='amazon'}</label>
            <input type="radio" name="fba_multichannel_auto" id="fba_multichannel_auto-2" value="0" {if !($fba.multichannel_auto)}checked{/if} /><label for="fba_multichannel_auto-2" class="label-checkbox">{l s='No' mod='amazon'}</label>
            <a class="slide-button btn"></a>
        </span>
        </div>
    </div>

    <hr class="amz-separator"/>


    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Decrease Stock' mod='amazon'}</label>

        <div class="margin-form col-lg-9">
        <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" name="fba_decrease_stock" id="fba_decrease_stock" value="1" {if ($fba.decrease_stock)}checked{/if} /><label for="fba_decrease_stock" class="label-checkbox">{l s='Yes' mod='amazon'}</label>
            <input type="radio" name="fba_decrease_stock" id="fba_decrease_stock-2" value="0" {if !($fba.decrease_stock)}checked{/if} /><label for="fba_decrease_stock-2" class="label-checkbox">{l s='No' mod='amazon'}</label>
            <a class="slide-button btn"></a>
        </span>

        </div>
    </div>

    <hr class="amz-separator"/>

    <div class="form-group fba-stock-behaviour">
        <label class="control-label col-lg-3" rel="behaviour"><span>{l s='Behaviour' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9">
            <input type="radio" name="fba_stock_behaviour" value="{$fba.stock_behaviour_switch|escape:'quotes':'UTF-8'}" {if $fba.stock_behaviour == $fba.stock_behaviour_switch}checked{/if} ><label>{l s='Use Amazon FBA stock first, then switch to your own stock (AFN/MFN auto switching)' mod='amazon'}</label><br/>
            <input type="radio" name="fba_stock_behaviour" value="{$fba.stock_behaviour_synch|escape:'quotes':'UTF-8'}" {if $fba.stock_behaviour == $fba.stock_behaviour_synch}checked{/if} ><label>{l s='Synchronize Prestashop stocks from Amazon FBA, your shop\'s stock is overrode' mod='amazon'}</label>
        </div>
    </div>

    {if isset($fba.marketplaces)}
    <div id="fba-stock-init" {if !$fba.stock_init.enabled}style="display:none"{/if}>
        <hr class="amz-separator"/>

        <label class="control-label col-lg-3" rel="stock_init"><span>{l s='Stock Initialization' mod='amazon'}</span><sup class="experimental">{l s='New' mod='amazon'}</sup></label>
        <!-- Marketplace Selector -->
        {if isset($fba.marketplaces) && $fba.marketplaces.show}
            <div class="form-group" style="margin-bottom:0;">
                <div class="margin-form col-lg-9 col-lg-offset-3">
                    <table class="country-selector">
                        <tr>
                            {foreach from=$fba.marketplaces.countries item=marketplace}
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
                </div>

                <div class="col-lg-offset-3 col-lg-9 country-separator">
                    <div class="amazon-tab-bar"></div>
                </div>
            </div>
        {else}
            {foreach from=$fba.marketplaces.countries item=marketplace}
                <span style="display:none;" class="amazon-tab-selector active" rel="{$marketplace.iso_code|escape:'quotes':'UTF-8'}">
                <input type="hidden" rel="id_lang" value="{$marketplace.id_lang|escape:'quotes':'UTF-8'}"/>
                </span>
            {/foreach}
        {/if}


        {foreach from=$fba.marketplaces.countries item=marketplace}
            <div class="amazon-tab amazon-tab-{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                 rel="{$marketplace.iso_code|escape:'quotes':'UTF-8'}" {if !$marketplace.default}style="display:none"{/if}>

                <div class="form-group">
                    <div class="margin-form col-lg-9">
                        <div class="current-country">
                            <img src="{$marketplace.image|escape:'quotes':'UTF-8'}" title="{$marketplace.name_long|escape:'quotes':'UTF-8'}"/><span class="name">{$marketplace.name_short|escape:'quotes':'UTF-8'}</span>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <div align="left" class="margin-form col-lg-offset-3">
                        <input type="button" class="button btn stock-init-delete"
                               id="stock-init-delete-{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                               rel="{$marketplace.id_lang|intval}"
                               value="{l s='Delete Cache' mod='amazon'}" style="opacity:0.5" />&nbsp;&nbsp;&nbsp;

                        <input type="button" class="button btn stock-init-get"
                               id="stock-init-get-{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                               rel="{$marketplace.id_lang|intval}"
                               value="{l s='Update Stock from Amazon' mod='amazon'}"/>

                        {if $marketplace.europe}<input type="hidden" name="europe" value="1"/>{/if}
                        <img src="{$settings.images_url|escape:'quotes':'UTF-8'}loader-connection.gif"
                             class="stock-init-loader" style="display:none"/>
                    </div>
                </div>

                <div class="form-group">
                    <div align="left" class="margin-form col-lg-offset-3">
                        <div class="{$class_success|escape:'htmlall':'UTF-8'} stock-init-success"
                             style="display:none">
                        </div>

                        <div class="{$class_warning|escape:'htmlall':'UTF-8'} stock-init-warning"
                             style="display:none">
                        </div>

                        <div class="{$class_error|escape:'htmlall':'UTF-8'} stock-init-error"
                             style="display:none">
                        </div>

                    <pre class="stock-init-debug"
                         style="display:none">
                    </pre>
                    </div>
                </div>


            </div>
        {/foreach}

        <div class="form-group">
            <hr class="amz-separator"/>
        </div>
    </div>
    {/if}

    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Orders Statuses' mod='amazon'}</label>

        <div class="margin-form col-lg-9">
            <select name="fba_order_state" style="width:400px;">
                <option value="0">{l s='Choose the order status' mod='amazon'}</option>
                {foreach from=$fba.order_states item=order_state}
                    <option value="{$order_state.value|escape:'htmlall':'UTF-8'}"
                            {if ($fba.order_state == $order_state.value)}selected="selected"{/if}>{$order_state.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
            <p>
                <span>{l s='Choose the default order status for new incoming orders (FBA)' mod='amazon'}</span><br/>
            </p>

            <select name="fba_multichannel_state" style="width:400px;">
                <option value="0">{l s='Choose the order status' mod='amazon'}</option>
                {foreach from=$fba.order_states item=order_state}
                    <option value="{$order_state.value|escape:'htmlall':'UTF-8'}"
                            {if ($fba.multichannel_state == $order_state.value)}selected="selected"{/if}>{$order_state.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
            <p>
                <span>{l s='Choose a default order status for incoming FBA multi-channel orders' mod='amazon'}</span><br/>
            </p>

            <select name="fba_multichannel_sent_state" style="width:400px;">
                <option value="0">{l s='Choose the order status' mod='amazon'}</option>
                {foreach from=$fba.order_states item=order_state}
                    <option value="{$order_state.value|escape:'htmlall':'UTF-8'}"
                            {if ($fba.multichannel_sent_state == $order_state.value)}selected="selected"{/if}>{$order_state.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
            <p>
                <span>{l s='Choose a default order status for FBA multi-channel orders which has been sent by Amazon' mod='amazon'}</span><br/>
            </p>

            <select name="fba_multichannel_done_state" style="width:400px;">
                <option value="0">{l s='Choose the order status' mod='amazon'}</option>
                {foreach from=$fba.order_states item=order_state}
                    <option value="{$order_state.value|escape:'htmlall':'UTF-8'}"
                            {if ($fba.multichannel_done_state == $order_state.value)}selected="selected"{/if}>{$order_state.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
            <p>
                <span>{l s='Choose a default order status for FBA multi-channel orders which has been delivered by Amazon' mod='amazon'}</span><br/>
            </p>
        </div>
    </div>

    <hr class="amz-separator"/>

    <div class="form-group fba-notification">
        <label class="control-label col-lg-3" rel="notification"><span>{l s='Multichannel Notifications' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9">
            <input type="radio" name="fba_notification" value="{$fba.notification_shop|escape:'quotes':'UTF-8'}" {if $fba.notification == $fba.notification_shop}checked{/if} ><label>{l s='Shop' mod='amazon'}</label>
            <input type="radio" name="fba_notification" value="{$fba.notification_customer|escape:'quotes':'UTF-8'}" {if $fba.notification == $fba.notification_customer}checked{/if} ><label>{l s='Customer' mod='amazon'}</label>
            <input type="radio" name="fba_notification" value="{$fba.notification_both|escape:'quotes':'UTF-8'}" {if $fba.notification == $fba.notification_both}checked{/if} ><label>{l s='Both' mod='amazon'}</label>
        </div>
        <p>
            &nbsp;
        </p>
    </div>

    <!-- validation button -->
    {$fba.validation|escape:'quotes':'UTF-8'}


</div><!-- menudiv-fba -->