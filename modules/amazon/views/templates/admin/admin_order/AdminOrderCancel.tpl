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
<input type="hidden" id="cancel_url" value="{$cancel_url|escape:'quotes':'UTF-8'}"/>
<input type="hidden" id="amazon_order_id" value="{$id_order|escape:'quotes':'UTF-8'}"/>
<input type="hidden" id="amazon_token" value="{$amazon_token|escape:'quotes':'UTF-8'}"/>
<input type="hidden" id="amazon_id_lang" value="{$id_lang|escape:'quotes':'UTF-8'}"/>
<input type="hidden" id="amazon_debug" value="{$debug|escape:'quotes':'UTF-8'}"/>
<input type="hidden" id="amazon_cancel_status" value="{$cancel_status|intval}"/>
<input type="hidden" id="context_key" value="{$context_key|escape:'quotes':'UTF-8'}"/>

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

                    {if $debug}
                        <p>
                            <span class="amazon_label">{l s='Debug Mode' mod='amazon'}:</span><span class="amazon_text" style="color:red;font-weight:bold">{l s='Active' mod='amazon'}</span><br/>
                        </p>
                        <br/>
                    {/if}

                    {if ( !empty($marketplace_channel))}
                        <p>{l s='Fulfillment' mod='amazon'} : <span
                                    style="color:red;font-weight:bold">{$marketplace_channel|escape:'html':'UTF-8'}</span>
                        </p>
                    {/if}

                    <p>
                        <label class="control-label">{l s='Marketplace Order ID' mod='amazon'} : </label>&nbsp;<b>{$marketplace_order_id|escape:'html':'UTF-8'}</b>
                        <input type="button" class="button btn btn-primary pull-right" id="amazon_go" value="{l s='Go to Amazon Seller Central' mod='amazon'}"/>
                    </p>

                    {if $scenario == 'to_cancel'}
                    <span class="amazon_label"><span class="amazon_text" style="color:red;font-weight:bold">{l s='Cancelation pending, please provide a reason' mod='amazon'}:</span><br/><br/>
                    <div class="row">
                        <div class="col-lg-9">
                            <select id="amazon-cancel" class="chosen form-control" name="id_order_state">
                                {foreach from=$reasons key=id item=reason}
                                    <option value="{$id|intval}">{$reason|escape:'html':'UTF-8'}</option>
                                {/foreach}
                            </select>

                        </div>
                        <div class="col-lg-3">
                            <button type="submit" name="amazon-cancel-button" class="btn btn-primary">
                                {l s='Confirm' mod='amazon'}
                            </button>&nbsp;&nbsp;<img src="{$images_url|escape:'quotes':'UTF-8'}loader-connection.gif" id="amazon-cancel-loader" style="display:none"/>
                        </div>
                    </div>
                    {elseif $scenario == 'cancel_cancel'}
                    <span class="amazon_label"><span class="amazon_text" style="color:red;font-weight:bold">{l s='Cancelation has been scheduled' mod='amazon'}</span><br/><br/>
                    <div class="row">
                        <div class="col-lg-3">
                            <button type="submit" name="amazon-revert-button" class="btn btn-primary">
                                {l s='Revert' mod='amazon'}
                            </button>&nbsp;&nbsp;<img src="{$images_url|escape:'quotes':'UTF-8'}loader-connection.gif" id="amazon-cancel-loader" style="display:none"/>
                        </div>
                    </div>
                    {elseif $scenario == 'canceled'}
                    <span class="amazon_label"><span class="amazon_text" style="color:red;font-weight:bold">{l s='This order has been canceled' mod='amazon'}</span><br/><br/>
                    {/if}
                    <br />
                    <div class="margin-form">
                        <div id="amazon-cancel-success" class="{$class_success|escape:'htmlall':'UTF-8'}" style="display:none">
                        </div>

                        <div id="amazon-cancel-error" class="{$class_error|escape:'htmlall':'UTF-8'}" style="display:none">
                        </div>
                    </div>

                </fieldset>
                {if $ps_version_is_16}
    </div>
</div>
{/if}