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

<input type="hidden" id="amazon_url" value="{$marketplace_url|escape:'quotes':'UTF-8'}"/>

{include file=$template_path|cat:'MarketplaceDetail.tpl' details=$marketplace_detail}

{if $ps_version_is_16}
<div class="col-lg-7 .bootstrap">
    <div class="panel">
        <fieldset id="amazon-order-ps16">
            {elseif $ps_version_is_15}
            <fieldset id="amazon-order-ps15" style="margin-top:10px;">
                {else}
                <fieldset id="amazon-order-ps14" style="width:400px;margin-top:10px;">
                    {/if}
                    <legend style="font-size:14px;padding-bottom: 5px;">
                        <img src="{$images_url|escape:'quotes':'UTF-8'}a32.png" alt="{l s='Amazon Marketplace' mod='amazon'}" class="logo">&nbsp;<span>{l s='Amazon Marketplace' mod='amazon'}</span>
                        <img src="{$marketplace_flag|escape:'quotes':'UTF-8'}" alt="{$marketplace_region|escape:'html':'UTF-8'}" class="flag" style="float:right;"/>
                    </legend>

                    {if $debug}
                        <p>
                            <span class="amazon_label">{l s='Debug Mode' mod='amazon'}:</span><span class="amazon_text"
                                                                                                    style="color:red;font-weight:bold">{l s='Active' mod='amazon'}</span><br/>
                            <span class="amazon_label">{l s='Tracking Number' mod='amazon'}:</span><span
                                    class="amazon_text">{if $tracking_number}
                                    <b>{$tracking_number|escape:'html':'UTF-8'}</b>{else}<b
                                        style="color:red">{l s='None' mod='amazon'}</b>{/if}</span>
                        </p>
                        <br/>
                    {/if}

                    {if ( !empty($marketplace_channel))}
                        <p>{l s='Fulfillment' mod='amazon'} : <span style="color:red;font-weight:bold">{$marketplace_channel|escape:'html':'UTF-8'}</span>
                        </p>
                    {/if}

                    <p>
                        <label class="control-label">{l s='Marketplace Order ID' mod='amazon'} : </label>&nbsp;<b>{$marketplace_order_id|escape:'html':'UTF-8'}</b>
                        <input type="button" class="button btn btn-primary pull-right" id="amazon_go" value="{l s='Go to Amazon Seller Central' mod='amazon'}"/>
                    </p>

                    {if isset($amazon_order_info) && is_array($amazon_order_info) && count($amazon_order_info)}
                        {foreach from=$amazon_order_info item=order_info_detail}
                            <p>
                                {if ($order_info_detail.label)}
                                    <label class="control-label">{$order_info_detail.label|escape:'html':'UTF-8'}:</label>&nbsp;
                                {/if}

                                {if ($order_info_detail.bold && $order_info_detail.color)}
                                    <span style="font-weight:bold;color:{$order_info_detail.color|escape:'html':'UTF-8'}">{$order_info_detail.value|escape:'html':'UTF-8'}</span>
                                {elseif ($order_info_detail.bold)}
                                    <span style="font-weight:bold;color:{$order_info_detail.color|escape:'html':'UTF-8'}">{$order_info_detail.value|escape:'html':'UTF-8'}</span>
                                {else}
                                    <span>{$order_info_detail.value|escape:'html':'UTF-8'}</span>
                                {/if}

                            </p>
                        {/foreach}
                    {/if}

                    <!-- Html to injection -->
                    <a id="amazon-switch" class="amazon-switch-name btn btn-default" href="{$endpoint|escape:'html':'UTF-8'}" title="{l s='Switch firstname/lastname' mod='amazon'}">
                        <i class="icon-exchange"></i>
                        <span class="amazon-switch-text">&nbsp;{l s='Switch firstname/lastname' mod='amazon'}</span>
                    </a>
                    <!-- Html to injection -->

                </fieldset>
                {if $ps_version_is_16}
    </div>
</div>
{/if}