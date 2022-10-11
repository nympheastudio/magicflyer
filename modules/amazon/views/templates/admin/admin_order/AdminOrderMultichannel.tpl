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
{foreach from=$js_urls item=js_url}
    <script type="text/javascript" src="{$js_url|escape:'quotes':'UTF-8'}"></script>
{/foreach}
<link rel="stylesheet" type="text/css" href="{$css_url|escape:'quotes':'UTF-8'}">

<input type="hidden" id="fbaorder_url" value="{$fbaorder_url|escape:'quotes':'UTF-8'}"/>
<input type="hidden" id="amazon_order_id" value="{$id_order|escape:'quotes':'UTF-8'}"/>
<input type="hidden" id="amazon_token" value="{$amazon_token|escape:'quotes':'UTF-8'}"/>
<input type="hidden" id="amazon_id_lang" value="{$id_lang|intval}"/>
<input type="hidden" id="amazon_debug" value="{$debug|escape:'quotes':'UTF-8'}"/>
<input type="hidden" id="context_key" value="{$context_key|escape:'quotes':'UTF-8'}"/>
<input type="hidden" id="amazon_text_fba_cancel"
       value="{l s='Are you sure to want to cancel this Amazon shipping ? This operation is not reversible...' mod='amazon'}"/>

{include file=$template_path|cat:'MarketplaceDetail.tpl' details=$marketplace_detail}

{if $ps_version_is_16}
<div class="col-lg-7">
    <div class="panel">
        <fieldset id="amazon-order-ps16">
            {elseif ps_version_is_15}
            <fieldset id="amazon-order-ps15" style="margin-top:10px;">
                {else}
                <fieldset id="amazon-order-ps14" style="width:400px;margin-top:10px;">
                    {/if}
                    <legend style="font-size:14px;padding-bottom: 5px;">
                        <img src="{$images_url|escape:'quotes':'UTF-8'}a32.png" alt="{l s='Amazon Marketplace' mod='amazon'}" class="logo">&nbsp;<span>{l s='Amazon Marketplace' mod='amazon'}</span>
                        <img src="{$marketplace_flag|escape:'quotes':'UTF-8'}" alt="{$marketplace_region|escape:'html':'UTF-8'}" class="flag" style="float:right;"/>
                    </legend>

                    <img src="{$images_url|escape:'quotes':'UTF-8'}green-loader.gif" alt="{l s='Loading' mod='amazon'}"
                         id="order-fba-loader"/>

                    <p>{l s='Fulfillment' mod='amazon'} : <span
                                style="color:red;font-weight:bold">{l s='Multi-Channel Order / Fulfilled By Amazon' mod='amazon'}</span>
                    </p>

                    <p>{l s='Current Status' mod='amazon'} :<span>{$marketplace_status|escape:'quotes':'UTF-8'}</span>
                    </p>

                    <p id="order-fba-ajax-error">{l s='Unexpected error while fetching data from Amazon' mod='amazon'}</p>

                    <p id="order-fba-error-message"></p>

                    <p id="order-fba-message"></p>

                    <hr id="order-fba-detail-spacer"/>
                    <table id="order-fba-detail" style="display:none;border-spacing: 5px;">
                        <tr>
                            <td style="text-align:right;">{l s='Order ID' mod='amazon'}&nbsp;</td>
                            <td rel="DisplayableOrderId" style="font-weight:bold"></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;">{l s='Order Status' mod='amazon'}&nbsp;</td>
                            <td rel="FulfillmentOrderStatus" style="font-weight:bold"></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;">{l s='Received On' mod='amazon'}&nbsp;</td>
                            <td rel="ReceivedDateTime"></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;">{l s='Last Update' mod='amazon'}&nbsp;</td>
                            <td rel="StatusUpdatedDateTime"></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;">{l s='Fulfillment Method' mod='amazon'}&nbsp;</td>
                            <td rel="FulfillmentMethod"></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;">{l s='Items' mod='amazon'}&nbsp;</td>
                            <td rel="Items" style="color:navy"></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;">{l s='Shipping Time Category' mod='amazon'}&nbsp;</td>
                            <td rel="ShippingSpeedCategory"></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;color:green;">{l s='Estimated Shipping Date' mod='amazon'}&nbsp;</td>
                            <td rel="EstimatedShipDateTime" style="color:green;"></td>
                        </tr>
                        <tr>
                            <td style="text-align:right;color:green;">{l s='Estimated Arrival Date' mod='amazon'}&nbsp;</td>
                            <td rel="EstimatedArrivalDateTime" style="font-weight:bold;color:green"></td>
                        </tr>
                    </table>

                    <hr id="order-fba-detail-spacer2"/>
                    <input type="button" class="button" style="float:left" id="amazon_cancel_fba"
                           value="{l s='Cancel Shipping' mod='amazon'}"/>
                    <input type="button" class="button" style="float:right" id="amazon_get_details"
                           value="{l s='Get Details' mod='amazon'}"/>

                    {if $marketplace_canceled}
                        <p id="order-fba-canceled" style="font-weight:bold;"><img
                                    src="{$images_url|escape:'quotes':'UTF-8'}cross.png"
                                    alt="{l s='Canceled' mod='amazon'}"/>&nbsp;&nbsp;{l s='This FBA shipping has been canceled' mod='amazon'}
                        </p>
                    {/if}

                    {if $debug}
                        <p style="clear:both">
                            <span class="amazon_label">{l s='Debug Mode' mod='amazon'}:</span><span class="amazon_text"
                                                                                                    style="color:red;font-weight:bold">{l s='Active' mod='amazon'}</span><br/>
                            <span>Debug:</span>
                        <pre id="amazon-output">&nbsp;</pre>
                        </p>
                        <br/>
                    {else}
                        <pre id="amazon-output" style="display:none">&nbsp;</pre>
                    {/if}



                </fieldset>
                {if $ps_version_is_16}
    </div>
</div>
{/if}