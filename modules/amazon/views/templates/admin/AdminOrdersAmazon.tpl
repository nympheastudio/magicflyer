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
<div class="clean"></div>
{if !$ps16x}<br/>{/if}


<ul id="menuTab" class="nav">
    <li id="menu-import" class="menuTabButton {if $selected_tab eq 'import'}selected{/if}"><a href="#"><span>&nbsp;<img
                        src="{$images|escape:'quotes':'UTF-8'}import-big-1.png"
                        alt="{l s='Import' mod='amazon'}"/>&nbsp;&nbsp;{l s='Import' mod='amazon'}</span></a></li>
    <li id="menu-report" class="menuTabButton {if $selected_tab eq 'report'}selected active{/if}"><a href="#"><span>&nbsp;<img
                        src="{$images|escape:'quotes':'UTF-8'}report-big.png"
                        alt="{l s='Report' mod='amazon'}"/>&nbsp;&nbsp;{l s='Report' mod='amazon'}</span></a></li>
</ul>

<div id="tabList" class="panel">
    <div id="menudiv-import" class="tabItem {if $selected_tab eq 'import'}selected{/if}" rel="import">

        <form action="#" id="amazonOrderOptions" class="form-inline" name="amazonOrderOptions" method="POST">
            <div class="clean" style="margin-top:15px;"></div>
            <fieldset class="panel">
                {if $ps16x}
                    <div class="panel-heading">
                        <img src="{$images|escape:'quotes':'UTF-8'}cog.gif" alt="" class="middle"/> {l s='Parameters' mod='amazon'}
                    </div>
                {else}
                    <legend>
                        <img src="{$images|escape:'quotes':'UTF-8'}cog.gif" alt="" class="middle"/> {l s='Parameters' mod='amazon'}
                    </legend>
                {/if}

                {if $ps16x}<h2 style="color:silver">{l s='Import Orders' mod='amazon'}</h2>{else}<h3 style="color:silver"{l s='Import Orders' mod='amazon'}</h3>{/if}

                <table style="" class="amz-options">
                    <tr>
                        <td>
                            <span class="amz-small-help">{l s='Standard Options' mod='amazon'}</span>
                        </td>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%">
                                <tr>
                                    {if isset($fba) && $fba}
                                        <td>{l s='Channel' mod='amazon'}&nbsp;&nbsp;
                                            <select type="text" name="channel">
                                                <option value="AFN">{l s='Amazon Fulfilments' mod='amazon'}</option>
                                                <option value="MFN">{l s='Merchant Fulfilments' mod='amazon'}</option>
                                                <option value="" selected>{l s='All Orders' mod='amazon'}</option>
                                            </select>
                                        </td>
                                    {/if}
                                    <td>{l s='From' mod='amazon'} <input type="text" id="datepickerFrom"
                                                                         name="datepickerFrom"
                                                                         value="{$start_date|escape:'quotes':'UTF-8'}"
                                                                         style="width:80px"/></td>
                                    <td>{l s='To' mod='amazon'} <input type="text" id="datepickerTo" name="datepickerTo"
                                                                       value="{$current_date|escape:'quotes':'UTF-8'}"
                                                                       style="width:80px"/></td>
                                    <td>{l s='Status' mod='amazon'}
                                        <select type="text" name="statuses" id="statuses">
                                            <option value="All">{l s='Retrieve all pending orders' mod='amazon'}</option>
                                            <option value="Pending">{l s='Pending - This order is pending on the MarketPlace' mod='amazon'}</option>
                                            <option value="Unshipped" selected="selected">{l s='Unshipped - This order is awaiting shipment' mod='amazon'}</option>
                                            <option value="PartiallyShipped">{l s='Partially to be shipped - This order is pending partial shipment' mod='amazon'}</option>
                                            <option value="Shipped">{l s='Shipped - This order has been shipped' mod='amazon'}</option>
                                            <option value="Canceled">{l s='Canceled - This order has been canceled' mod='amazon'}</option>
                                            <option value="In Cart">{l s='In Cart - This order has not by paid yet' mod='amazon'}</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

            </fieldset>
        </form>
        <br/>

        {*Aug-23-2018: Remove warning about Carriers/Modules*}

        <form action="#" id="amazonOrders" name="amazonOrders" method="POST">
            <table class="table order table-hover" cellpadding="0" cellspacing="0"
                   style="width: 100%; margin-bottom:10px;">

                <thead id="order-table-heading" style="display:none">
                <tr class="active">
                    <th class="center" width="20px"><input type="checkbox" id="checkme" class="order-check"/></th>
                    <th class="center" width="20px"></th>

                    <th class="center" width="90px">Date</th>
                    <th class="center" width="200px">ID</th>
                    <th class="center" width="70px">Status</th>
                    <th class="center">Customer</th>
                    <th class="center">Shipment</th>
                    <th class="center">FF/by</th>
                    <th class="center">Qty</th>
                    <th class="center">Total</th>
                </tr>
                </thead>
                <tbody>
                <tr class="row_hover" id="order-model" style="display:none;">
                    <td class="center" rel="checkbox"><input type="checkbox" class="order-check"/></td>
                    <td class="center" rel="flag"><img src="{$images|escape:'quotes':'UTF-8'}/geo_flags/fr.gif"
                                                       alt="fr"/></td>
                    <td class="center" rel="date">&nbsp;</td>
                    <td class="left order-link" rel="id">&nbsp;</td>
                    <td class="left" rel="status">&nbsp;</td>
                    <td class="left" rel="customer">&nbsp;</td>
                    <td class="left" rel="shipping">&nbsp;</td>
                    <td class="center" rel="fulfillment">&nbsp;</td>
                    <td class="center" rel="quantity">&nbsp;</td>
                    <td class="right" rel="total">&nbsp;</td>
                </tr>
                </tbody>
            </table>
        </form>
        <div class="button-verify btn float-left" id="submit-orders-list"><img
                    src="{$images|escape:'quotes':'UTF-8'}verify-big.png"
                    alt="{l s='Show Orders' mod='amazon'}"/><span>{l s='Show Orders' mod='amazon'}</span>
        </div>
        <div class="button-proceed btn float-right" id="submit-orders-import">
            <span>{l s='Import Selected Orders' mod='amazon'}</span><img
                    src="{$images|escape:'quotes':'UTF-8'}import-cloud-big.png"
                    alt="{l s='Import Selected Orders' mod='amazon'}"/>
        </div>
        <div style="margin-top:80px;">
            <br/>
        </div>
        <br/>

        <div class="conf" id="amz-loader"><img src="{$images|escape:'quotes':'UTF-8'}loading.gif" alt=""
                                               style="margin-left: 50%;"/></div>
        <div class="{$alert_class.success|escape:'quotes':'UTF-8'}" id="amazon-import-result"
             style="margin-top:20px;"></div>
        <div class="{$alert_class.warning|escape:'quotes':'UTF-8'}" id="amazon-import-warning"
             style="margin-top:20px;"></div>
        <div class="{$alert_class.danger|escape:'quotes':'UTF-8'}" id="amazon-import-error"
             style="margin-top:20px;"></div>
        <div class="{$alert_class.info|escape:'quotes':'UTF-8'}" id="amazon-import-support"
             style="margin-top:20px;"></div>

    </div>

    <!-- Report Tab -->
    {include file=$tpl_path|escape:'quotes':'UTF-8'|cat:'views/templates/admin/ReportAmazon.tpl' report_url=$report_url type='order'}
</div>
{if $widget}
{* Support Widget*}
<script type="text/javascript" src="https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.js"></script>
{include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/support/widget.tpl" data=$widget}
{* End of Support Widget*}
{/if}
