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

<fieldset class="panel" {$style|escape:'quotes':'UTF-8'}>
    {if $psIs16}
    <h3>{else}
        <legend>{/if}<img src="{$img|escape:'quotes':'UTF-8'}"
                          alt="{l s='Amazon MarketPlace' mod='amazon'}">&nbsp;{l s='Amazon MarketPlace' mod='amazon'} {$amazonPlatform|escape:'quotes':'UTF-8'} {if $psIs16}
    </h3>
    {else}</legend>{/if}

    {if $amz_case == 1}
    <input type="hidden" id="fbaorder_url"
           value="{$amazon_path|escape:'quotes':'UTF-8'}functions/fbaorder.php?europe=1"/>
    <input type="hidden" id="amazon_order_id" value="{$amazonOrderId|escape:'quotes':'UTF-8'}"/>
    <input type="hidden" id="amazon_token" value="{$amazonToken|escape:'quotes':'UTF-8'}"/>
    <input type="hidden" id="amazon_id_lang" value="{$id_lang|intval}"/>
    <img src="{$images|escape:'quotes':'UTF-8'}green-loader.gif" alt="{l s='Loading' mod='amazon'}"
         id="order-fba-loader"
         style="display:none;float:right;position:relative;top:+3px;"/>

    <p>{$channel_text|escape:'quotes':'UTF-8'}</p>

    <p style="color:green">{l s='This Order is Eligible for FBA Multi-Channel Process' mod='amazon'}</p>

    <p style="display:none;color:red;margin-left:30px;"
       id="order-fba-ajax-error">{l s='Unexpected error while fetching data from Amazon' mod='amazon'}</p>

    <p style="display:none;color:red;margin-left:30px;" id="order-fba-error-message"></p>

    <p style="display:none;color:green;margin-left:30px;font-weight:bold" id="order-fba-message"></p>
    <hr style="display:none;width:30%;border-bottom:1px solid silver" id="order-fba-detail-spacer"/>
    <hr style="width:30%;border-bottom:1px solid silver"/>
    <input type="button" class="button btn" style="float:right" id="amazon_fba_create"
           value="{l s='Ship this Order through Amazon' mod='amazon'}"/>
    <script type="text/javascript" src="{$amazon_path|escape:'quotes':'UTF-8'}views/js/adminorderfba.js"></script>
    {elseif $amz_case == 2}
    <input type="hidden" id="fbaorder_url"
           value="{$amazon_path|escape:'quotes':'UTF-8'}functions/fbaorder.php?europe=1"/>
    <input type="hidden" id="amazon_order_id" value="{$amazonOrderId|escape:'quotes':'UTF-8'}"/>
    <input type="hidden" id="amazon_token" value="{$amazonToken|escape:'quotes':'UTF-8'}"/>
    <input type="hidden" id="amazon_id_lang" value="{$id_lang|intval}"/>
    <input type="hidden" id="amazon_text_fba_cancel"
           value="{l s='Are you sure to want to cancel this Amazon shipping ? This operation is not reversible...' mod='amazon'}"/>
    <img src="{$images|escape:'quotes':'UTF-8'}green-loader.gif" alt="{l s='Loading' mod='amazon'}"
         id="order-fba-loader"
         style="display:none;float:right;position:relative;top:+3px;"/>

    <p>{$channel_text|escape:'quotes':'UTF-8'}</p>

    <p>{l s='Current Status' mod='amazon'} : <span>{$currentStatus|escape:'quotes':'UTF-8'}</span></p>

    <p style="display:none;color:red;margin-left:30px;"
       id="order-fba-ajax-error">{l s='Unexpected error while fetching data from Amazon' mod='amazon'}</p>

    <p style="display:none;color:red;margin-left:30px;" id="order-fba-error-message"></p>
    <hr style="display:none;width:30%;border-bottom:1px solid silver" id="order-fba-detail-spacer"/>
    {if $psIs16}
    <div class="table-responsive">{/if}
        <table id="order-fba-detail" style="display:none;border-spacing: 5px;" class="table">
            <tr>
                <td style="text-align:right;width:200px;border-bottom:none;">{l s='Order ID' mod='amazon'}</td>
                <td rel="DisplayableOrderId" style="font-weight:bold;border-bottom:none;"></td>
            </tr>
            <tr>
                <td style="text-align:right;width:200px;border-bottom:none;">{l s='Order Status' mod='amazon'}</td>
                <td rel="FulfillmentOrderStatus" style="font-weight:bold;border-bottom:none;"></td>
            </tr>
            <tr>
                <td style="text-align:right;width:200px;border-bottom:none;">{l s='Received On' mod='amazon'}</td>
                <td rel="ReceivedDateTime" style="border-bottom:none;"></td>
            </tr>
            <tr>
                <td style="text-align:right;width:200px;border-bottom:none;">{l s='Last Update' mod='amazon'}</td>
                <td rel="StatusUpdatedDateTime" style="border-bottom:none;"></td>
            </tr>
            <tr>
                <td style="text-align:right;width:200px;border-bottom:none;">{l s='Fulfillment Method' mod='amazon'}</td>
                <td rel="FulfillmentMethod" style="border-bottom:none;"></td>
            </tr>
            <tr>
                <td style="text-align:right;width:200px;border-bottom:none;">{l s='Items' mod='amazon'}</td>
                <td rel="Items" style="color:navy; border-bottom:none;"></td>
            </tr>
            <tr>
                <td style="text-align:right;width:200px;border-bottom:none;">{l s='Shipping Time Category' mod='amazon'}</td>
                <td rel="ShippingSpeedCategory" style="border-bottom:none;"></td>
            </tr>
            <tr>
                <td style="text-align:right;width:200px;border-bottom:none;color:green;">{l s='Estimated Shipping Date' mod='amazon'}</td>
                <td rel="EstimatedShipDateTime" style="color:green;border-bottom:none;"></td>
            </tr>
            <tr>
                <td style="text-align:right;width:200px;border-bottom:none;color:green;">{l s='Estimated Arrival Date' mod='amazon'}</td>
                <td rel="EstimatedArrivalDateTime" style="font-weight:bold;color:green;border-bottom:none;"></td>
            </tr>
        </table>
        {if $psIs16}</div>
    {/if}
    <p id="order-fba-canceled" style="{$style|escape:'quotes':'UTF-8'} font-weight:bold;"><img
                src="{$images|escape:'quotes':'UTF-8'}cross.png"
                alt="{l s='Canceled' mod='amazon'}"/>&nbsp;&nbsp;{l s='This FBA shipping has been canceled' mod='amazon'}
    </p>
    <hr style="width:30%;border-bottom:1px solid silver"/>
    <input type="button" class="button btn" style="float:left" id="amazon_cancel_fba"
           value="{l s='Cancel Shipping' mod='amazon'}"/>
    <input type="button" class="button btn" style="float:right" id="amazon_get_details"
           value="{l s='Get Details' mod='amazon'}"/>
    <script type="text/javascript" src="{$amazon_path|escape:'quotes':'UTF-8'}views/js/adminorderfba.js"></script>
    {elseif $amz_case == 3}
    <input type="hidden" id="amazon_url" value="{$amazon_url|escape:'quotes':'UTF-8'}"/>
    {if $psIs16}
    <p>
        <button class="btn" id="amazon_go" style="float: right; margin-top: -5px;"><i
                    class="icon-search"></i> {l s='Go to Amazon Seller Central' mod='amazon'}
        </button>{l s='Marketplace Order ID' mod='amazon'} :
        <b>{$marketPlaceOrderId|escape:'quotes':'UTF-8'}</b>{$channel_text|escape:'quotes':'UTF-8'}
    </p>
    {else}
    <p>
        {l s='Marketplace Order ID' mod='amazon'}
        <b>{$marketPlaceOrderId|escape:'quotes':'UTF-8'}</b>{$channel_text|escape:'quotes':'UTF-8'}
    </p>
    <br>

    <input type="button" class="button" id="amazon_go" value="{l s='Go to Amazon Seller Central' mod='amazon'}"
           style="float: right;"/>
    {/if}
    <script type="text/javascript" src="{$amazon_path|escape:'quotes':'UTF-8'}views/js/adminorder.js"></script>
    {/if}

</fieldset>