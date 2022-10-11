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
<div id="menudiv-features" class="tabItem {if $features.selected_tab}selected{/if} panel form-horizontal">

    <h3>{l s='Features' mod='amazon'}</h3>

    {if !$amazon.is_lite}
    <div class="form-group">
        <div class="margin-form">
            <div class="amz-info-level-info {if $psIs16}alert alert-info col-lg-offset-3{/if}" style="font-size:1.1em">
                <ul>
                    <li>{l s='Please choose below the main features you expect from the module' mod='amazon'}</li>
                    <li>{$features.tutorial|escape:'quotes':'UTF-8'}</li>
                    <li class="full_version"><img src="{$settings.images_url|escape:'quotes':'UTF-8'}noway.png" />: {l s='Available in the complete version' mod='amazon'}: <a href="http://addons.prestashop.com/fr/58_common-services" target="_blank">http://addons.prestashop.com/fr/58_common-services</a></li>
                </ul>
            </div>
        </div>
    </div>
    {/if}

    <div class="form-group">
        <div class="margin-form col-lg-offset-1 col-lg-11">
            <table class="feature-table">

                <!-- CATALOG SECTION -->
                <tbody>
                <tr class="features-section">
                    <td colspan="3">
                        <img src="{$features.images|escape:'htmlall':'UTF-8'}catalog_sync.png" style="float:left"
                             title="{l s='Catalog Features' mod='amazon'}"/>
                        <span>{l s='Catalog Features' mod='amazon'}</span>
                    </td>
                </tr>

                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[offers]" value="1"
                               {if ($features.config.offers)}checked{/if} id="feat-offers-cb"
                               class="regular-checkbox big-checkbox"><label for="feat-offers-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Create Offers' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Allows create offers automatically' mod='amazon'}<br/>
                        {if !$amazon.is_lite}
                        {$features.links.offers|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[creation]" value="1"  {if (!$features.config.creation && $features.config.noway)}disabled{/if}
                               {if ($features.config.creation)}checked{/if} id="feat-products-creation-cb"
                               class="regular-checkbox big-checkbox{if (!$features.config.creation && $features.config.noway)} noway{/if}"><label for="feat-products-creation-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Products Creation' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Create new products on Amazon' mod='amazon'}
                        {if !$amazon.is_lite}: <br/>
                        {$features.links.creation|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>


                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[wizard]" value="1" {if (!$features.config.wizard && $features.config.noway)}disabled{/if}
                               {if ($features.config.wizard)}checked{/if} id="feat-wizard-cb"
                               class="regular-checkbox big-checkbox{if (!$features.config.wizard && $features.config.noway)} noway{/if}"><label for="feat-wizard-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Matching Wizard' mod='amazon'}<span
                                    class="experimental">{l s='Experimental' mod='amazon'}</span></span>
                    </td>
                    <td style="width:60%">
                        {l s='Visual & interactive tool creating automaticaly offers on Amazon' mod='amazon'}<br/>
                        {*{$features.links.wizard|escape:'quotes':'UTF-8'}*}
                    </td>
                </tr>

                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[prices_rules]" value="1"
                               {if ($features.config.prices_rules)}checked{/if} id="feat-prices-rules-cb"
                               class="regular-checkbox big-checkbox"><label for="feat-prices-rules-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Price Rules' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Adjust and format your prices for Amazon' mod='amazon'}
                        {if !$amazon.is_lite}: <br/>
                        {$features.links.prices_rules|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>
                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[second_hand]" value="1"
                               {if ($features.config.second_hand)}checked{/if} id="feat-second-hand-cb"
                               class="regular-checkbox big-checkbox"><label for="feat-second-hand-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Second Hand' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Sell second hand, collectible or refurbished products' mod='amazon'}<br/>
                        {if !$amazon.is_lite}
                        {$features.links.second_hand|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>
                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[filters]" value="1" {if (!$features.config.filters && $features.config.noway)}disabled{/if}
                               {if ($features.config.filters)}checked{/if} id="feat-filters-cb"
                               class="regular-checkbox big-checkbox{if (!$features.config.filters && $features.config.noway)} noway{/if}"><label for="feat-filters-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Filters' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Allows to exclude product depending on certain criterias (Brand, Supplier...)' mod='amazon'}
                        <br/>
                        {if !$amazon.is_lite}
                        {$features.links.filters|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>
                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[import_products]" value="1" {if (!$features.config.import_products && $features.config.noway)}disabled{/if}
                               {if ($features.config.import_products)}checked{/if} id="feat-import_products-cb"
                               class="regular-checkbox big-checkbox{if (!$features.config.import_products && $features.config.noway)} noway{/if}"><label for="feat-import_products-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Import' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Allows to import your existing inventory from Amazon' mod='amazon'}
                        <br/>
                        {if !$amazon.is_lite}
                        {$features.links.import_products|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                <!-- INTERNATIONAL SECTION -->
                <tr class="features-section">
                    <td colspan="3">
                        <img src="{$features.images|escape:'htmlall':'UTF-8'}world.png" style="float:left"
                             title="{l s='International' mod='amazon'}"/>
                        <span>{l s='International' mod='amazon'}</span>
                    </td>
                </tr>
                <tr class="features-items">
                    <td style="width:10%">
                        <!-- name amazonEurope for retro compatibility reason -->
                        <input type="checkbox" name="features[amazon_europe]" value="1"
                               {if ($features.config.amazon_europe)}checked{/if} id="feat-europe-cb"
                               class="regular-checkbox big-checkbox"><label for="feat-europe-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Europe' mod='amazon'}<span>
                    </td>
                    <td style="width:60%">
                        {l s='Activate Amazon Europe support' mod='amazon'}
                        {if !$amazon.is_lite}: <br/>
                        {$features.links.europe|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[worldwide]" value="1" {if (!$features.config.worldwide && $features.config.noway)}disabled{/if}
                               {if ($features.config.worldwide)}checked{/if} id="feat-worldwide-cb"
                               class="regular-checkbox big-checkbox{if (!$features.config.worldwide && $features.config.noway)} noway{/if}"><label for="feat-worldwide-cb"></label>
                    </td>
                    <td style="width:30%">
                       <span>{l s='USA and Worldwide' mod='amazon'}<span>
                    </td>
                    <td style="width:60%">
                        {l s='Activate Amazon USA and worldwide platforms support' mod='amazon'}<br/>
                    </td>
                </tr>

                <!-- SMART FEATURES SECTION -->
                <tr class="features-section">
                    <td colspan="3">
                        <img src="{$features.images|escape:'htmlall':'UTF-8'}user_superman.png" style="float:left"
                             title="{l s='Smart Features' mod='amazon'}"/>
                        <span>{l s='Smart Features' mod='amazon'}</span>
                    </td>
                </tr>
                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[messaging]" value="1"
                               {if ($features.config.messaging)}checked{/if} id="feat-messaging-cb"
                               class="regular-checkbox big-checkbox"><label for="feat-messaging-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Messaging' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Activate Messaging. Allows to send invoices, term and conditions to the customer' mod='amazon'}
                        {if !$amazon.is_lite}
                        <br/>
                        {$features.links.messaging|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>
                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[shipping]" value="1" {if ($features.config.shipping)}checked{/if} {if (!$features.config.shipping && $features.config.noway)}disabled{/if}
                               {if ($features.config.shipping)}checked{/if} id="feat-shipping-cb"
                               class="regular-checkbox big-checkbox{if (!$features.config.shipping && $features.config.noway)} noway{/if}"><label for="feat-shipping-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Shipping' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Allows to manage shipping template, shipping per region' mod='amazon'}<br/>
                        {if !$amazon.is_lite}
                        {$features.links.shipping_template|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>
                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[fba]" value="1" {if ($features.config.fba)}checked{/if} {if (!$features.config.fba && $features.config.noway)}disabled{/if}
                               id="feat-fba-cb" class="regular-checkbox big-checkbox{if (!$features.config.fba && $features.config.noway)} noway{/if}"><label for="feat-fba-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Amazon FBA' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Add Amazon FBA support to your module' mod='amazon'} <br/>
                        {if !$amazon.is_lite}
                        {$features.links.fba|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[repricing]" value="1" {if ($features.config.repricing)}checked{/if} {if (!$features.config.repricing && $features.config.noway)}disabled{/if}
                               id="feat-repricing-cb" class="regular-checkbox big-checkbox{if (!$features.config.repricing && $features.config.noway)} noway{/if}"><label for="feat-repricing-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Repricing' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Integrated automated price competition tool' mod='amazon'} <br/>
                        {if !$amazon.is_lite}
                        {$features.links.repricing|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[business]" value="1" {if ($features.config.business)}checked{/if} {if (!$features.config.business && $features.config.noway)}disabled{/if}
                               {if ($features.config.business)}checked{/if} id="feat-business-cb" {if (!$features.config.business && $features.config.noway)}disabled{/if}
                               class="regular-checkbox big-checkbox{if (!$features.config.business && $features.config.noway)} noway{/if}"><label for="feat-business-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Business' mod='amazon'}<span class="experimental">{l s='New' mod='amazon'}</span></span>
                    </td>
                    <td style="width:60%">
                        {l s='Use Amazon Business' mod='amazon'} <br/>
                        {if !$amazon.is_lite}
                        {$features.links.business|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                <!-- ORDERS SECTION -->
                <tr class="features-section">
                    <td colspan="3">
                        <img src="{$features.images|escape:'htmlall':'UTF-8'}calculator_edit.png" style="float:left"
                             title="{l s='Orders' mod='amazon'}"/>
                        <span>{l s='Orders' mod='amazon'}</span>
                    </td>
                </tr>
                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[orders]" value="1"
                               {if ($features.config.orders)}checked{/if} id="feat-orders-cb"
                               class="regular-checkbox big-checkbox"><label for="feat-orders-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Import' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Activate import of orders' mod='amazon'}
                        {if !$amazon.is_lite}: <br/>
                        {$features.links.orders|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[orders_reports]" value="1"
                               {if ($features.config.orders_reports)}checked{/if} id="feat-orders_reports-cb"
                               class="regular-checkbox big-checkbox"><label for="feat-orders_reports-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Orders Reports' mod='amazon'}<span class="experimental">{l s='New' mod='amazon'}</span></span>
                    </td>
                    <td style="width:60%">
                        {l s='Activate import Amazon orders reports.' mod='amazon'}
                        {if !$amazon.is_lite}: <br/>
                            {$features.links.orders_reports|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[remote_cart]" value="1" {if (!$features.config.remote_cart && $features.config.noway)}disabled{/if}
                               {if ($features.config.remote_cart)}checked{/if} id="feat-remote_cart-cb"
                               class="regular-checkbox big-checkbox{if (!$features.config.fba && $features.config.noway)} noway{/if}"><label for="feat-remote_cart-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Remote Cart' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Activate Amazon Remote Cart management' mod='amazon'}
                        {if !$amazon.is_lite}: <br/>
                        {$features.links.remote_cart|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[cancel_orders]" value="1" {if (!$features.config.cancel_orders && $features.config.noway)}disabled{/if}
                               {if ($features.config.cancel_orders)}checked{/if} id="feat-cancel-orders-cb"
                               class="regular-checkbox big-checkbox{if (!$features.config.fba && $features.config.noway)} noway{/if}"><label for="feat-cancel-orders-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Orders Cancelation' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Handle orders cancelations' mod='amazon'}
                        {if !$amazon.is_lite}: <br/>
                        {$features.links.cancel_orders|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>
                <!-- EXPERT MODE SECTION -->
                <tr class="features-section">
                    <td colspan="3">
                        <img src="{$features.images|escape:'htmlall':'UTF-8'}user_ninja.png" style="float:left"
                             title="{l s='Expert Mode' mod='amazon'}"/>
                        <span style="color:red;">{l s='Expert Mode' mod='amazon'}</span>
                    </td>
                </tr>
                <tr class="features-items">
                    <td style="width:10%">
                        <input type="checkbox" name="features[expert_mode]" {if ($features.config.expert_mode)}checked{/if} value="1" {if (!$features.config.expert_mode && $features.config.noway)}disabled{/if}
                               {if ($features.config.expert_mode)}checked{/if} value="1" id="feat-expert-mode-cb"
                               class="regular-checkbox big-checkbox {if (!$features.config.expert_mode && $features.config.noway)} noway{else} scull-checkbox{/if}"><label
                                for="feat-expert-mode-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Activate' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Activate Expert Mode. Support is void when activated without the agreement of the support' mod='amazon'}
                        {if !$amazon.is_lite}: <br/>
                        {$features.links.expert_mode|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                {if (isset($features.experimental) && $features.experimental)}
                <tr class="features-items amazon-expert-mode" rel="amazon-expert-mode" style="display:none">
                    <td style="width:10%">
                        <input type="checkbox" name="demo_mode" {if ($features.config.demo_mode)}checked{/if}
                               value="1" id="feat-demo-mode-cb"
                               class="regular-checkbox big-checkbox dynamic-config"><label
                                for="feat-demo-mode-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Demo Mode' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Activate Demo Mode for development needs.' mod='amazon'}<br/>
                    </td>
                </tr>
                {/if}

                <tr class="features-items amazon-expert-mode" rel="amazon-expert-mode" style="display:none">
                    <td style="width:10%">
                        <!-- named expert mode for compatibility reason -->
                        <input type="checkbox" name="features[gcid]" {if ($features.config.gcid)}checked{/if} value="1" {if (!$features.config.gcid && $features.config.noway)}disabled{/if}
                               id="feat-gcid-cb" class="regular-checkbox big-checkbox{if (!$features.config.gcid && $features.config.noway)} noway{/if}"><label
                                for="feat-gcid-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Code Exemption' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Activate a GCID Code Exemption' mod='amazon'}
                        {if !$amazon.is_lite}: <br/>
                        {$features.links.gcid|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                <!-- DEBUG MODE SECTION -->
                <tr class="features-section">
                    <td colspan="3">
                        <img src="{$features.images|escape:'htmlall':'UTF-8'}bug.png" style="float:left"
                             title="{l s='Debug Mode' mod='amazon'}"/>
                        <span style="color:red;">{l s='Debug Mode' mod='amazon'}</span>
                    </td>
                </tr>

                <tr class="features-items" rel="amazon-debug-mode">
                    <td style="width:10%">
                        <input type="checkbox" name="debug_mode" {if ($features.config.debug_mode)}checked{/if}
                               value="1" id="feat-debug-mode-cb"
                               class="regular-checkbox big-checkbox dynamic-config"><label
                                for="feat-debug-mode-cb"></label>
                    </td>
                    <td style="width:30%">
                        <span>{l s='Debug Mode' mod='amazon'}</span>
                    </td>
                    <td style="width:60%">
                        {l s='Activate Debug Mode for development needs.' mod='amazon'}
                        {if !$amazon.is_lite}: <br/>
                        {$features.links.debug_express|escape:'quotes':'UTF-8'}
                        {/if}
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>

    {$profiles.validation|escape:'quotes':'UTF-8'}

</div>