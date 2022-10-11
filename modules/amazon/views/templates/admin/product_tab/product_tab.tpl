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
<div id="amazon-product-tab" {if $product_tab.ps15}class="ps15"{/if} {if $product_tab.ps17x}class="ps17"{/if}>
    <script type="text/javascript"
            src="{$product_tab.module_url|escape:'quotes':'UTF-8'}views/js/jquery.qtip.js"></script>
    <script type="text/javascript"
            src="{$product_tab.module_url|escape:'quotes':'UTF-8'}views/js/product_tab.js?version={$product_tab.version|escape:'htmlall':'UTF-8'}"></script>

    <link href="{$product_tab.module_url|escape:'quotes':'UTF-8'}views/css/jquery.qtip.css" rel="stylesheet"
          type="text/css"/>

    <link href="{$product_tab.module_url|escape:'quotes':'UTF-8'}views/css/product_tab.css?version={$product_tab.version|escape:'htmlall':'UTF-8'}"
          rel="stylesheet" type="text/css"/>

    <input type="hidden" id="amz-text-propagate-shop"
           value="{l s='Be carefull ! Are you sure to want to propagate this value to the entire shop ?' mod=amazon}"/>
    <input type="hidden" id="amz-text-propagate-cat"
           value="{l s='Be carefull ! Are you sure to want to propagate this value to all the products of this categories ?' mod=amazon}"/>
    <input type="hidden" id="amz-text-propagate-manufacturer"
           value="{l s='Be carefull ! Are you sure to want to change this value for all the products having the same Manufacturer ?' mod=amazon}"/>
    <input type="hidden" id="amz-text-propagate-supplier"
           value="{l s='Be carefull ! Are you sure to want to change this value for all the products having the same Supplier ?' mod=amazon}"/>
    <input type="hidden" id="amz-product-options-copy" value="{l s='Copied' mod='amazon'}"/>
    <input type="hidden" id="amz-product-options-paste" value="{l s='Pasted' mod='amazon'}"/>

    {if isset($product_tab.json_url)}
    <input type="hidden" value="{$product_tab.json_url|escape:'htmlall':'UTF-8'}" id="amazon-product-options-json-url"/>
    {/if}

    <input type="hidden" value="{$product_tab.id_product|escape:'htmlall':'UTF-8'}" id="amazon-id-product"/>
    <input type="hidden" value="{l s='Parameters successfully saved' mod='amazon'}"
           id="amazon-product-options-message-success"/>
    <input type="hidden" value="{l s='Unable to save parameters...' mod='amazon'}"
           id="amazon-product-options-message-error"/>

    {if isset($product_tab.amazon_tokens) && is_array($product_tab.amazon_tokens) && count($product_tab.amazon_tokens)}
        {foreach from=$product_tab.amazon_tokens key=k item=token}
            <input type="hidden" name="amazon_token[{$k|escape:'htmlall':'UTF-8'}]"
                   value="{$token|escape:'htmlall':'UTF-8'}"/>
        {/foreach}
    {/if}

    <div class="{if !$product_tab.ps17x}panel {/if}product-tab">
        <h3 class="tab" {if !$product_tab.ps17x}style="display:none"{/if}><img src="{$product_tab.images|escape:'htmlall':'UTF-8'}a32.png"
                             title="{l s='Amazon Marketplace' mod='amazon'}"/> {l s='Amazon Marketplace' mod='amazon'}
        </h3>

        {if isset($product_tab.shop_warning) && $product_tab.shop_warning}
            <div class="form-group">
                <div class="margin-form col-lg-12">
                    <div class="{$product_tab.class_warning|escape:'htmlall':'UTF-8'}">
                        {$product_tab.shop_warning|escape:'htmlall':'UTF-8'}
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        {elseif !$product_tab.active}
            <div class="margin-form col-lg-9" style="width:100%;margin-top:20px;">
                <div class="{$product_tab.class_warning|escape:'htmlall':'UTF-8'}">{l s='Module has not been activated or configured yet' mod='amazon'}</div>
            </div>
        {else}
            <div class="form-group">
                <table id="amazon-table-product" class="table amazon-item">
                    <thead>
                    <tr class="nodrag nodrop">
                        <th>
                        </th>
                        <th class="left">
                            <span class="title_box">{l s='Name' mod='amazon'}</span>
                        </th>
                        <th class="left reference">
                            <span class="title_box">{l s='Reference code' mod='amazon'}</span>
                        </th>
                        <th class="left reference">
                            <span class="title_box">EAN13</span>
                        </th>
                        <th class="left reference">
                            <span class="title_box">UPC</span>
                        </th>
                        <th class="center action">
                        </th>
                        <th class="center action">
                        </th>
                        <th class="center action">
                        </th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr class="highlighted" rel="{$product_tab.id_product|escape:'htmlall':'UTF-8'}_0">
                        <td class="left">
                            <input type="radio" id="amazon-item-radio" name="complex_id_product"
                                   value="{$product_tab.product.complex_id|escape:'htmlall':'UTF-8'}" checked>
                        </td>
                        <td class="left" rel="name">
                            {$product_tab.product.name|escape:'html':'UTF-8'}
                        </td>
                        <td class="left amazon-editable" rel="reference">
                            {$product_tab.product.reference|escape:'html':'UTF-8'}
                        </td>
                        <td class="left amazon-editable" rel="ean13">
                            {$product_tab.product.ean13|escape:'html':'UTF-8'}
                        </td>
                        <td class="left amazon-editable" rel="upc">
                            {$product_tab.product.upc|escape:'html':'UTF-8'}
                        </td>
                        <td class="center action">
                            <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}cross.png"
                                 class="delete-product-option"
                                 rel="{$product_tab.product.complex_id|escape:'htmlall':'UTF-8'}"
                                 title="{l s='Delete product option entry' mod='amazon'}"/>
                        </td>
                        <td class="center action">
                            <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}page_white_copy.png"
                                 class="copy-product-option"
                                 rel="{$product_tab.product.complex_id|escape:'htmlall':'UTF-8'}"
                                 title="{l s='Copy product option entry' mod='amazon'}"/>
                        </td>
                        <td class="center action">
                            <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}paste_plain.png"
                                 class="paste-product-option"
                                 rel="{$product_tab.product.complex_id|escape:'htmlall':'UTF-8'}"
                                 title="{l s='Paste product option entry' mod='amazon'}"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            {if is_array($product_tab.combinations) && count($product_tab.combinations)}
                <div class="form-group">
                    <h3 class="tab" id="amazon-title"> {l s='Combinations' mod='amazon'}</h3>

                    <div class="table-responsive">
                        <table id="amazon-table-combinations" class="table amazon-item">
                            <thead>
                            <tr class="nodrag nodrop">
                                <th>
                                </th>
                                <th class="left">
                                    <span class="title_box">{l s='Attribute' mod='amazon'}</span>
                                </th>
                                <th class="left reference">
                                    <span class="title_box">{l s='Reference code' mod='amazon'}</span>
                                </th>
                                <th class="left reference">
                                    <span class="title_box">EAN13</span>
                                </th>
                                <th class="left reference">
                                    <span class="title_box">UPC</span>
                                </th>
                                <th class="center action">
                                </th>
                                <th class="center action">
                                </th>
                                <th class="center action">
                                </th>
                            </tr>
                            </thead>

                            <tbody>
                            {foreach from=$product_tab.combinations key=id_product_attribute item=combination}
                                <tr rel="{$combination.complex_id|escape:'htmlall':'UTF-8'}">
                                    <td class="left">
                                        <input type="radio" id="amazon-item-radio" name="complex_id_product"
                                               value="{$combination.complex_id|escape:'htmlall':'UTF-8'}">
                                    </td>
                                    <td class="left" rel="name">
                                        {$combination.name|escape:'html':'UTF-8'}
                                    </td>
                                    <td class="left amazon-editable" rel="reference">
                                        {$combination.reference|escape:'html':'UTF-8'}
                                    </td>
                                    <td class="left amazon-editable" rel="ean13">
                                        {$combination.ean13|escape:'html':'UTF-8'}
                                    </td>
                                    <td class="left amazon-editable" rel="upc">
                                        {$combination.upc|escape:'html':'UTF-8'}
                                    </td>
                                    <td class="center action">
                                        <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}cross.png"
                                             class="delete-product-option"
                                             rel="{$combination.complex_id|escape:'htmlall':'UTF-8'}"
                                             title="{l s='Delete product option entry' mod='amazon'}"/>
                                    </td>
                                    <td class="center action">
                                        <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}page_white_copy.png"
                                             class="copy-product-option"
                                             rel="{$combination.complex_id|escape:'htmlall':'UTF-8'}"
                                             title="{l s='Copy product option entry' mod='amazon'}"/>
                                    </td>
                                    <td class="center action">
                                        <img src="{$product_tab.images|escape:'htmlall':'UTF-8'}paste_plain.png"
                                             class="paste-product-option"
                                             rel="{$combination.complex_id|escape:'htmlall':'UTF-8'}"
                                             title="{l s='Paste product option entry' mod='amazon'}"/>
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>

                        </table>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                        </div>
                    </div>

                    <div class="clearfix"></div>
                </div>
            {/if}
            <div class="form-group">
                {if $product_tab.show_countries}
                    <h3 class="tab" id="amazon-title"> {l s='Marketplaces' mod='amazon'}</h3>
                    <table class="country-selector">
                        <tr>
                            {foreach from=$product_tab.marketplaces item=marketplace}
                                <td>
                            <span class="amazon-tab-selector{if $marketplace.default} active{/if}"
                                  rel="{$marketplace.iso_code|escape:'htmlall':'UTF-8'}">
                                <img src="{$marketplace.image|escape:'htmlall':'UTF-8'}"
                                     title="{$marketplace.name_long|escape:'htmlall':'UTF-8'}"/><span
                                        class="name">{$marketplace.name_short|escape:'htmlall':'UTF-8'}</span>
                                <input type="hidden" name="amazon_lang[]"
                                       value="{$marketplace.id_lang|escape:'htmlall':'UTF-8'}" rel="1"/>
                            </span>
                                </td>
                            {/foreach}
                        </tr>

                    </table>
                    <div class="col-lg-12">
                        <div class="amazon-tab-bar"></div>
                    </div>
                {else}
                    <h3 class="tab" id="amazon-title"> {l s='Amazon Marketplace' mod='amazon'}</h3>
                    {foreach from=$product_tab.marketplaces item=marketplace}
                        <span class="amazon-tab-selector{if $marketplace.default} active{/if}"
                              rel="{$marketplace.iso_code|escape:'htmlall':'UTF-8'}" style="display:none;">
                            <input type="hidden" name="amazon_lang[]"
                                   value="{$marketplace.id_lang|escape:'htmlall':'UTF-8'}" rel="1"/>
                        </span>
                    {/foreach}
                {/if}

                {foreach from=$product_tab.marketplaces item=marketplace}
                    {include file="{$product_tab.module_path|escape:'htmlall':'UTF-8'}/views/templates/admin/product_tab/options_tab.tpl" data=$product_tab.product_options product=$product_tab.product}
                {/foreach}
            </div>
        {/if}
        <div class="clearfix"></div>
    </div>
    <div class="debug"></div>
    {include file="{$product_tab.module_path|escape:'htmlall':'UTF-8'}/views/templates/admin/product_tab/glossary.tpl"}
</div>
