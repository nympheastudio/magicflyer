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
{if isset($settings.marketplace.config)}

    {foreach $settings.locales.config key=id_lang item=locale}
        <!-- marketplace configuration section -->
        {assign var="marketplace" value=$settings.marketplace.config[$id_lang]}

        <!-- div menudiv - lang -->
        <div id="menudiv-{$locale.iso_code|escape:'htmlall':'UTF-8'}"
             class="tabItem {if ($locale.iso_code == $settings.locales.selected_tab)}selected{/if} panel form-horizontal">

            <h3>{$locale.name|escape:'htmlall':'UTF-8'}
                &nbsp;{if $locale.region}&nbsp;&gt;&nbsp;Amazon - {$locale.region|escape:'htmlall':'UTF-8'}{/if}</h3>
            <input type="hidden" name="id_lang" id="lang-{$id_lang|intval}"
                   value="{$locale.iso_code|escape:'htmlall':'UTF-8'}"/>
            <input type="hidden" id="id-lang-{$locale.iso_code|escape:'htmlall':'UTF-8'}" class="margin-form col-lg-9"
                   value="{$id_lang|intval}"/>

            {if !$amazon.is_lite}
            <div class="form-group">
                <div class="margin-form">
                    <div class="amz-info-level-info {if $psIs16}alert alert-info col-lg-offset-3{/if}"
                         style="font-size:1.1em">
                        <ul>
                            <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                            <li>{$settings.tutorial_1|escape:'quotes':'UTF-8'}</li>
                        </ul>
                    </div>
                </div>
            </div>
            {/if}

            <div class="form-group">
                <label class="control-label col-lg-3" style="color:grey;">{l s='Active' mod='amazon'}</label>

                <div class="margin-form col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="actives[{$id_lang|intval}]"
                               id="active-{$locale.iso_code|escape:'htmlall':'UTF-8'}" rel="1" value="1"
                               {if ($marketplace.active)}checked{/if} /><label
                                for="active-{$locale.iso_code|escape:'htmlall':'UTF-8'}"
                                class="label-checkbox">{l s='Yes' mod='amazon'}</label>
                        <input type="radio" name="actives[{$id_lang|intval}]"
                               id="active2-{$locale.iso_code|escape:'htmlall':'UTF-8'}" rel="1" value="0"
                               {if !($marketplace.active)}checked{/if} /><label
                                for="active2-{$locale.iso_code|escape:'htmlall':'UTF-8'}"
                                class="label-checkbox">{l s='No' mod='amazon'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
                {if $psIs16}<br/><br/>{/if}
                <label class="control-label col-lg-3">{l s='Platform' mod='amazon'}</label>

                <div class="margin-form col-lg-9">
                    <select name="marketPlaceRegion[{$id_lang|intval}]" style="width:340px; display: inline;">
                        <option disabled="disabled">{l s='Choose the platform for this region' mod='amazon'}</option>
                        <option value=""></option>
                        {foreach from=$settings.locales.platforms key=iso_code item=platform}
                            <option value="{$iso_code|escape:'htmlall':'UTF-8'}"
                                    {if ($iso_code == $locale.platform_selected)}selected{/if}>{$platform|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    {if $locale.platform_selected_required}<span
                            class="mandatory">{l s='Required' mod='amazon'}</span>{/if}

                </div>
                {if $psIs16}<br/><br/>{/if}
                <label class="control-label col-lg-3">{l s='Currency' mod='amazon'}</label>

                <div class="margin-form col-lg-9">

                    <select name="marketPlaceCurrency[{$id_lang|intval}]" style="width:340px; display: inline;">
                        <option disabled="disabled">{l s='Choose the currency for this region' mod='amazon'}</option>
                        <option value=""></option>
                        {foreach from=$settings.locales.currencies item=currency}
                            <option value="{$currency.iso_code|escape:'htmlall':'UTF-8'}"
                                    {if ($currency.iso_code == $locale.currency)}selected{/if}>{$currency.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    {if $locale.currency_required}<span class="mandatory">{l s='Required' mod='amazon'}</span>{/if}

                    <div class="change-locales">

                    </div>
                </div>
            </div>

            <div class="margin-form col-lg-12">
                <hr class="amz-separator" style="width:30%"/>
            </div>

            <div {if (!$marketplace.active)}style="display:none"{/if}>
                <div class="api-settings form-group">
                    <label class="control-label col-lg-3" rel="register"
                           style="color:grey;"><span>{l s='API Settings' mod='amazon'}</span></label>

                    <div class="margin-form col-lg-9">
                        &nbsp;{if $psIs16}<br><br>{/if}
                    </div>

                    <label class="control-label col-lg-3">{l s='Country/Language' mod='amazon'}</label>

                    <div class="margin-form col-lg-9">
                        <span>{$marketplace.flag|escape:'quotes':'UTF-8'}</span>
                        {if $psIs16}<br><br>{/if}
                    </div>

                    <div class="api-settings">
                        <div style="{$marketplace.display|escape:'htmlall':'UTF-8'}">
                            <label class="control-label col-lg-3"
                                   rel="merchantid"><span>{l s='Merchant ID' mod='amazon'}</span></label>

                            <div class="margin-form col-lg-9">
                                <input type="text" name="merchantId[{$id_lang|intval}]"
                                       style="width:300px; display: inline;"
                                       value="{$marketplace.merchantId|escape:'htmlall':'UTF-8'}"/>
                                <input type="hidden" name="marketPlaceLang[{$id_lang|intval}]"
                                       value="{$id_lang|intval}" {$marketplace.disabled|escape:'htmlall':'UTF-8'} />
                                {if $marketplace.merchantId_required}<span
                                        class="mandatory">{l s='Required' mod='amazon'}</span>{/if}
                                {if $psIs16}<br><br>{/if}
                            </div>
                        </div>

                        <div  {if !$marketplace.displayAll}style="display:none"{/if}>
                            <label class="control-label col-lg-3"
                                   rel="marketplace_id"><span>{l s='Marketplace Id' mod='amazon'}</span></label>

                            <div class="margin-form col-lg-9">
                                <input type="text" name="marketPlaceId[{$id_lang|intval}]"
                                       style="width:300px; display: inline;"
                                       value="{$marketplace.marketPlaceId|escape:'htmlall':'UTF-8'}"
                                       {if $marketplace.region != 'ca'}readonly class="disabled"{/if} rel="1"/>
                                {if $psIs16}<br><br>{/if}
                            </div>
                        </div>

                        <div style="{$marketplace.display|escape:'htmlall':'UTF-8'}">

                            <div class="form-group" {if !$marketplace.displayAll}style="display:none"{/if}>
                                <label class="control-label col-lg-3"
                                       rel="awskeyid"><span>{l s='AWS Key Id' mod='amazon'}</span></label>

                                <div class="margin-form col-lg-9">
                                    <input type="text" name="awsKeyId[{$id_lang|intval}]"
                                           style="width:300px; display: inline;"
                                           value="{$marketplace.awsKeyId|escape:'htmlall':'UTF-8'}" {$marketplace.disabled|escape:'htmlall':'UTF-8'} />
                                    {if $marketplace.awsKeyId_required}<span
                                            class="mandatory">{l s='Required' mod='amazon'}</span>{/if}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-3"
                                ><span>{l s='MWS Token' mod='amazon'}</span></label>

                                <div class="margin-form col-lg-9">
                                    <input type="password" name="mwsToken[{$id_lang|intval}]"
                                           style="width:380px; display: inline;"
                                           value="{$marketplace.mwsToken|escape:'htmlall':'UTF-8'}" {$marketplace.disabled|escape:'htmlall':'UTF-8'} />
                                </div>
                            </div>


                            <div class="form-group" {if !$marketplace.displayAll}style="display:none"{/if}>
                                <label class="control-label col-lg-3"
                                       rel="secretkey"><span>{l s='AWS Secret Key' mod='amazon'}</span></label>

                                <div class="margin-form col-lg-9">
                                    <input type="password" name="awsSecretKey[{$id_lang|intval}]"
                                           style="width:380px; display: inline;"
                                           value="{$marketplace.awsSecretKey|escape:'htmlall':'UTF-8'}" {$marketplace.disabled|escape:'htmlall':'UTF-8'} />
                                    {if $marketplace.awsSecretKey_required}<span
                                            class="mandatory">{l s='Required' mod='amazon'}</span>{/if}

                                    {if !$amazon.is_lite}
                                    <p><strong>{l s='Tutorial URL' mod='amazon'}
                                            : </strong>{$settings.tutorial_2|escape:'quotes':'UTF-8'}</p>
                                    {/if}
                                </div>
                            </div>

                            <label class="control-label col-lg-3">{l s='API Check' mod='amazon'}</label>

                            <div class="margin-form col-lg-9">
                                <input type="button" class="button btn" id="check-{$id_lang|intval}"
                                       value="{l s='Check Connectivity' mod='amazon'}" style="width:280px;"/>
                                <img src="{$settings.images_url|escape:'quotes':'UTF-8'}loader-connection.gif"
                                     alt="{l s='Check Connectivity' mod='amazon'}" class="check-loader"
                                     style="position:relative;top:-4px;display:none"/>
                                <br/>
                                <br/>

                                <div class="server-response" id="server-response-{$id_lang|intval}">&nbsp;</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group margin-form">
                    <hr class="amz-separator" style="width:30%"/>
                </div>
                <!-- detailed configuration section -->
                {assign var="general" value=$settings.general.config[$id_lang]}


                <div class="form-group">
                    <label class="control-label col-lg-3"
                           style="color:grey;">{l s='General Settings' mod='amazon'}</label>

                    <div class="col-lg-9">&nbsp;<br/><br/></div>

                    <div rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none;">
                        <label class="control-label col-lg-3" rel="outstock"><span>{l s='Stock Break-even' mod='amazon'}</span><sup class="expert">{l s='Expert' mod='amazon'}</sup></label>

                        <div class="margin-form col-lg-9">
                            <input type="text" name="outofstock[{$id_lang|intval}]"
                                   value="{$general.out_of_stock|escape:'htmlall':'UTF-8'}" style="width:50px;"/>
                        </div>

                        <div class="form-group">
                            <div class="margin-form col-lg-12">
                                <hr class="amz-separator" style="width:30%; {if !$psIs16}margin-top: 40px;{/if}"/>
                            </div>
                        </div>
                    </div>

                    <div class="amazon-prices-rules" rel="amazon-prices-rules" style="display:none;">
                        <label class="control-label col-lg-3"
                               rel="price_rule"><span>{l s='Default Price Rule' mod='amazon'}</span></label>

                        <div class="margin-form col-lg-9">
                            <select name="price_rule[{$id_lang|intval}][type]" class="price-rule-type"
                                    style="display: inline; max-width:150px;vertical-align: top;">
                            <option value="percent"
                                    {if ($general.price_rule.type == 'percent')}selected{/if}>{l s='Percentage' mod='amazon'}</option>
                            {if isset($general.price_rule.currency_sign)}
                                <option value="value"
                                        {if ($general.price_rule.type == 'value')}selected{/if}>{l s='Value' mod='amazon'}</option>
                            {/if}
                            </select>

                            &nbsp;&nbsp;
                            <div id="default-price-rule-{$id_lang|intval}" class="default-price-rule"
                                 style="display: inline-block;">
                                {if isset($general.price_rule.rule.from)}

                                    {foreach from=$general.price_rule.rule.from key=index item=value}
                                        <div class="price-rule">
                                            <input type="text" name="price_rule[{$id_lang|intval}][rule][from][]"
                                                   rel="from"
                                                   style="width:50px"
                                                   value="{$general.price_rule.rule.from[$index]|escape:'htmlall':'UTF-8'}"/>&nbsp;&nbsp;{$general.price_rule.currency_sign|escape:'htmlall':'UTF-8'}
                                            <span>
                                &nbsp;&nbsp;<img src="{$settings.images_url|escape:'quotes':'UTF-8'}slash.png"
                                                 class="price-rule-slash" alt=""/>&nbsp;&nbsp;
                                </span>
                                            <input type="text" name="price_rule[{$id_lang|intval}][rule][to][]" rel="to"
                                                   style="width:50px"
                                                   value="{$general.price_rule.rule.to[$index]|escape:'htmlall':'UTF-8'}"/>&nbsp;&nbsp;{$general.price_rule.currency_sign|escape:'htmlall':'UTF-8'}
                                            <span>
                                &nbsp;&nbsp;<img src="{$settings.images_url|escape:'quotes':'UTF-8'}next.png"
                                                 class="price-rule-next" alt=""/>&nbsp;&nbsp;
                                </span>

                                            <select name="price_rule[{$id_lang|intval}][rule][percent][]" rel="percent"
                                                    style="width:100px;{if ($general.price_rule.type != 'percent')}display:none;{/if}">
                                            <option></option>
                                            {section name=price_rule_percent loop=99}
                                                <option value="{$smarty.section.price_rule_percent.iteration|escape:'htmlall':'UTF-8'}"
                                                        {if $general.price_rule.rule.percent[$index] == $smarty.section.price_rule_percent.iteration}selected{/if}>{$smarty.section.price_rule_percent.iteration|escape:'htmlall':'UTF-8'}
                                                    &#37;</option>
                                            {/section}
                                            <option disabled>--</option>
                                            {section name=price_rule_percent loop=99}
                                                <option value="-{$smarty.section.price_rule_percent.iteration|escape:'htmlall':'UTF-8'}"
                                                        {if $general.price_rule.rule.percent[$index] == ($smarty.section.price_rule_percent.iteration * -1)}selected{/if}>
                                                    -{$smarty.section.price_rule_percent.iteration|escape:'htmlall':'UTF-8'}
                                                    &#37;</option>
                                            {/section}
                                            </select>
                                            {if isset($general.price_rule.currency_sign)}
                                            <select name="price_rule[{$id_lang|intval}][rule][value][]" rel="value"
                                                    style="width:100px;{if ($general.price_rule.type != 'value')}display:none;{/if}">
                                            <option></option>
                                            {section name=price_rule_value loop=99}
                                                <option value="{$smarty.section.price_rule_value.iteration|escape:'htmlall':'UTF-8'}"
                                                        {if isset($general.price_rule.rule.value[$index]) && $general.price_rule.rule.value[$index] == $smarty.section.price_rule_value.iteration}selected{/if}>{$smarty.section.price_rule_value.iteration|escape:'htmlall':'UTF-8'} {$general.price_rule.currency_sign|escape:'htmlall':'UTF-8'}</option>
                                            {/section}
                                            <option disabled>--</option>
                                            {section name=price_rule_value loop=99}
                                                <option value="-{$smarty.section.price_rule_value.iteration|escape:'htmlall':'UTF-8'}"
                                                        {if isset($general.price_rule.rule.value[$index]) && $general.price_rule.rule.value[$index] == ($smarty.section.price_rule_value.iteration * -1)}selected{/if}>
                                                    -{$smarty.section.price_rule_value.iteration|escape:'htmlall':'UTF-8'} {$general.price_rule.currency_sign|escape:'htmlall':'UTF-8'}</option>
                                            {/section}
                                            </select>
                                        {/if}
                                            <span class="price-rule-add" {if $index > 0}style="display:none;"{/if}><img
                                                        src="{$settings.images_url|escape:'quotes':'UTF-8'}plus.png"
                                                        alt="{l s='Add a rule' mod='amazon'}"/></span>
                                    <span class="price-rule-remove" {if $index == 0}style="display:none;"{/if}><img
                                                src="{$settings.images_url|escape:'quotes':'UTF-8'}minus.png"
                                                alt="{l s='Remove a rule' mod='amazon'}"/></span>
                                        </div>
                                    {/foreach}

                                {/if}
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="margin-form col-lg-12">
                                <hr class="amz-separator" style="width:30%; margin-top: 40px;"/>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-lg-3" rel="rounding"><span>{l s='Rounding' mod='amazon'}</span></label>

                        <div class="margin-form col-lg-9 rounding">
                            <input type="radio" name="rounding[{$id_lang|intval}]" value="1" {$general.rounding_1|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='One Digit' mod='amazon'}</span>
                            <input type="radio" name="rounding[{$id_lang|intval}]" value="2" {$general.rounding_2|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='Two Digits' mod='amazon'}</span>
                            <input type="radio" name="rounding[{$id_lang|intval}]" value="3" {$general.rounding_3|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='Smart Rounding' mod='amazon'}</span>
                            <input type="radio" name="rounding[{$id_lang|intval}]" value="4" {$general.rounding_4|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='None' mod='amazon'}</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="margin-form col-lg-12">
                            <hr class="amz-separator"
                                style="width:30%; {if !$psIs16}margin-top: 40px;{else}margin-top: 40px;{/if}"/>
                        </div>
                    </div>

                    <!-- Default product tax code -->
                    <div class="form-group">
                        <label class="control-label col-lg-3" rel="ptc"><span>{l s='Default Product Tax Code' mod='amazon'}</span></label>

                        <div class="margin-form col-lg-9 ptc">
                            <select name="ptc[{$id_lang|intval}]" id="ptc[{$id_lang|intval}]">
                                <option value="">{l s='N/A' mod='amazon'}</option>
                                {if isset($general.ptc) && is_array($general.ptc)}
                                    {foreach from=$general.ptc item=ptc}
                                        <option value="{$ptc.ptc|escape:'htmlall':'UTF-8'}" {if $general.ptc_selected == $ptc.ptc}selected{/if}>{$ptc.description|escape:'htmlall':'UTF-8'}</option>
                                    {/foreach}
                                {/if}
                            </select>
                        </div>
                    </div>
                    {include file="./separator.tpl" big=true}
                    <!-- End Default product tax code -->

                    <!-- Default tax rule -->
                    <div class="form-group tax-rule">
                        <label class="control-label col-lg-3" rel="default_tax_rule">
                            <span>{l s='Default Tax Rule' mod='amazon'}</span>
                        </label>
                        <div class="margin-form col-lg-9 default_tax_rule">
                            <select name="default_tax_rule[{$id_lang|intval}]" id="default_tax_rule[{$id_lang|intval}]">
                                <option value="">{l s='Exempted' mod='amazon'}</option>
                                {if isset($general.default_tax_rule) && is_array($general.default_tax_rule)}
                                    {foreach from=$general.default_tax_rule item=default_tax_rule}
                                        <option value="{$default_tax_rule.id_tax_rules_group|escape:'htmlall':'UTF-8'}"
                                                {if $general.default_tax_rule_selected == $default_tax_rule.id_tax_rules_group}selected{/if}>
                                            {$default_tax_rule.name|escape:'htmlall':'UTF-8'}
                                        </option>
                                    {/foreach}
                                {/if}
                            </select>
                        </div>
                    </div>
                    {include file="./separator.tpl" big=true}
                    <!-- End Default tax rule -->

                    <div rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none;">

                        <div class="form-group">
                            <label class="control-label col-lg-3" rel="sort_order"><span>{l s='Sort Order' mod='amazon'}</span><sup
                                        class="expert">{l s='Expert' mod='amazon'}</sup></label>

                            <div class="margin-form col-lg-9 sort_order">
                                <input type="radio" name="sort_order[{$id_lang|intval}]" value="1" {$general.sort_order_1|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='First Name, Last Name' mod='amazon'}</span>
                                <input type="radio" name="sort_order[{$id_lang|intval}]" value="2" {$general.sort_order_2|escape:'htmlall':'UTF-8'} /><span class="span_text">{l s='Last Name, First Name' mod='amazon'}</span>
                            </div>
                        </div>

                        {include file="./separator.tpl" big=true}

                        <div class="form-group">

                            <label class="control-label col-lg-3"
                                   rel="synch_field"><span>{l s='Synchronization Field' mod='amazon'}</span><sup
                                        class="expert">{l s='Expert' mod='amazon'}</sup></label>

                            <div class="margin-form col-lg-9">
                                <select name="synch_field[{$id_lang|intval}]" style="width:250px;">
                                    <option value="" disabled="disabled">
                                    {l s='Choose one of the following' mod='amazon'}</option>
                                    <option value="ean13" {$general.synch_field_ean13|escape:'htmlall':'UTF-8'}>EAN13
                                        (Europe)
                                    </option>
                                    <option value="upc" {$general.synch_field_upc|escape:'htmlall':'UTF-8'}>UPC (United
                                        States)
                                    </option>
                                    <option value="both" {$general.synch_field_both|escape:'htmlall':'UTF-8'}>Both
                                        (EAN13 then UPC)
                                    </option>
                                    {if ($settings.general.expert_mode)}
                                        <option value="reference" {$general.synch_field_reference|escape:'htmlall':'UTF-8'}>
                                            SKU
                                        </option>
                                    {/if}
                                </select>
                                <input type="hidden" name="change_synch_field[{$id_lang|intval}]"
                                       value="{l s='You changed the Synchronization Field and this is not recommended. Please refer to the Amazon documentation prior changing this value.' mod='amazon'}"/>
                            </div>
                        </div>
                    </div>

                    <div rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none;">
                        <div class="form-group">
                            <label class="control-label col-lg-3"
                                   rel="asin"><span>{l s='ASIN has the Priority' mod='amazon'}</span><sup
                                        class="expert">{l s='Expert' mod='amazon'}</sup></label>

                            <div class="margin-form col-lg-9">
                                <input type="checkbox" name="use_asin[{$id_lang|intval}]"
                                       {if ($general.asin_has_priority)}checked{/if}
                                       value="1" rel="1"/>&nbsp;<span class="span_text">{l s='Yes' mod='amazon'}</span>


                            </div>
                            <div class="margin-form col-lg-12">
                                <hr class="amz-separator" style="width:30%"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- carriers configuration section -->

                <div rel="amazon-orders" class="amazon-orders" style="display:none;">

                    {assign var="incoming_carrier" value=$settings.carriers.config.incoming[$id_lang]}
                    {assign var="outgoing_carrier" value=$settings.carriers.config.outgoing[$id_lang]}

                    <div class="form-group">
                        <label class="control-label col-lg-3"
                               style="color:grey">{l s='Carrier Mapping' mod='amazon'}</label><br/><br/>

                        <label class="control-label col-lg-3"
                               rel="incoming_order"><span>{l s='For incoming orders' mod='amazon'}</span></label>

                        <!-- incoming order carriers -->
                        <div class="margin-form col-lg-9">
                            {foreach from=$incoming_carrier key=index item=carrier}
                                <div id="carrier-group-{$id_lang|intval}-{$index|escape:'htmlall':'UTF-8'}"
                                     class="carrier-group">
                                    <select name="amazon_carrier[{$id_lang|intval}][]"
                                            style="width:250px; display: inline;">
                                        <option disabled="disabled">{l s='Choose the associated carrier on Amazon' mod='amazon'}</option>
                                        <option value=""></option>
                                        {if isset($carrier.amazon_carrier) && is_array($carrier.amazon_carrier)}
                                            {foreach from=$carrier.amazon_carrier key=key item=amazon_carrier}
                                                <option value="{$key|escape:'htmlall':'UTF-8'}"
                                                        {if $amazon_carrier.selected}selected{/if}>{$amazon_carrier.name|escape:'quotes':'UTF-8'}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                <span style="position:relative;top:-4px;">&nbsp;&nbsp;<img
                                            src="{$settings.images_url|escape:'quotes':'UTF-8'}next.png"
                                            style="max-height:16px;opacity:0.5"
                                            alt=""/>&nbsp;&nbsp;</span>
                                    <select name="carrier[{$id_lang|intval}][]" style="width:250px; display: inline;">
                                        <option disabled="disabled">{l s='Choose an appropriate carrier for Amazon Orders' mod='amazon'}</option>
                                        <option value="0"></option>
                                        {if isset($carrier.prestashop_carrier) && is_array($carrier.prestashop_carrier)}
                                            {foreach from=$carrier.prestashop_carrier key=id_carrier item=prestashop_carrier}
                                                <option value="{$id_carrier|intval}"
                                                        {if $prestashop_carrier.selected}selected{/if}>{$prestashop_carrier.name|escape:'htmlall':'UTF-8'}{if $prestashop_carrier.is_module}&nbsp;({l s='Module' mod='amazon'}){/if}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                    &nbsp;&nbsp;
                                <span class="add-carrier addnewcarrier"
                                      rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_add|escape:'quotes':'UTF-8'}>
                                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}plus.png"
                                         alt="{l s='Add a new carrier' mod='amazon'}"/></span>
                                <span class="remove-carrier removecarrier"
                                      rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_del|escape:'quotes':'UTF-8'}>
                                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}minus.png"
                                         alt="{l s='Add a new carrier' mod='amazon'}"/></span>
                                    <br/>
                                </div>
                                <!-- eof div carrier group -->
                            {/foreach}
                            <div id="new-carriers-{$id_lang|intval}"></div>

                            <br/>
                        </div>
                        <!-- eof incoming order carriers -->

                        <!-- outgoing order carriers -->
                        <label class="control-label col-lg-3"
                               rel="outgoing_order"><span>{l s='For Outgoing Orders' mod='amazon'}</span></label>

                        <div class="margin-form col-lg-9">
                            {foreach from=$outgoing_carrier key=index item=carrier}
                                <div id="outgoing-carrier-group-{$id_lang|intval}-{$index|escape:'htmlall':'UTF-8'}"
                                     class="carrier-group">
                                    <select style="width:250px; display: inline;"
                                            name="carrier_default[{$id_lang|intval}][prestashop][]">
                                        <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                        <option value="0"></option>
                                        {foreach from=$carrier.prestashop_carrier key=id_carrier item=prestashop_carrier}
                                            <option value="{$id_carrier|intval}"
                                                    {if $prestashop_carrier.selected}selected{/if}>{$prestashop_carrier.name|escape:'htmlall':'UTF-8'}{if $prestashop_carrier.is_module}&nbsp;({l s='Module' mod='amazon'}){/if}</option>
                                        {/foreach}
                                    </select>
                                <span style="position:relative;top:-4px;">&nbsp;&nbsp;<img
                                            src="{$settings.images_url|escape:'quotes':'UTF-8'}next.png"
                                            style="max-height:16px;opacity:0.5"
                                            alt=""/>&nbsp;&nbsp;</span>
                                    <select name="carrier_default[{$id_lang|intval}][amazon][]"
                                            style="width:250px; display: inline;">
                                        <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                        <option value=""></option>
                                        {foreach from=$carrier.amazon_carrier key=key item=amazon_carrier}
                                            <option value="{$key|escape:'htmlall':'UTF-8'}"
                                                    {if $amazon_carrier.selected}selected{/if}>{$amazon_carrier.name|escape:'htmlall':'UTF-8'}</option>
                                        {/foreach}
                                    </select>
                                    &nbsp;&nbsp;
                                <span class="add-carrier addnew-outgoing-carrier"
                                      rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_add|escape:'quotes':'UTF-8'}>
                                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}plus.png"
                                         alt="{l s='Add a new carrier' mod='amazon'}"/></span>
                                <span class="remove-carrier remove-outgoing-carrier"
                                      rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_del|escape:'quotes':'UTF-8'}>
                                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}minus.png" alt=""/></span>
                                </div>
                                <!-- eof outgoing carrier group -->
                            {/foreach}
                            <div id="outgoing-new-carriers-{$id_lang|intval}"></div>

                        </div>
                        <!-- eof outgoing order carriers -->

                        <div class="clearfix"></div>

                        {if !$amazon.is_lite}
                        <div class="form-group" style="margin-top:15px;">
                            {if $settings.carriers.config.has_carrier_modules}
                                <div class="margin-form">
                                    <div class="amz-info-level-warning {if $psIs16}alert alert-warning col-lg-offset-3{/if}"
                                         style="font-size:1.1em">
                                        <p>
                                            {l s='Some carriers/modules have been detected' mod='amazon'}...<br/>
                                            {l s='Please read our online tutorial' mod='amazon'}:<br/>
                                            {$settings.carriers.carrier_modules_tutorial|escape:'quotes':'UTF-8'}<br/>
                                        </p>
                                    </div>
                                </div>
                            {/if}
                            <hr class="amz-separator" style="width:30%"/>
                        </div>
                        {/if}
                    </div>


                    {if $settings.carriers.fba_multichannel}
                        {assign var="fba_multichannel_carrier" value=$settings.carriers.config.fba_multichannel[$id_lang]}
                        <!-- fba_multichannel order carriers -->
                        <div class="form-group">
                            <label class="control-label col-lg-3">{l s='For FBA Multi Channel Orders' mod='amazon'}</label>

                            <div class="margin-form col-lg-9">
                                {foreach from=$fba_multichannel_carrier key=index item=carrier}
                                    <div id="multichannel-carrier-group-{$id_lang|intval}-{$index|escape:'htmlall':'UTF-8'}" class="carrier-group">
                                        <select style="width:250px" name="carrier_multichannel[{$id_lang|intval}][prestashop][]">
                                            <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                            <option value="0"></option>
                                            {foreach from=$carrier.prestashop_carrier key=id_carrier item=prestashop_carrier}
                                                <option value="{$id_carrier|intval}"
                                                        {if $prestashop_carrier.selected}selected{/if}>{$prestashop_carrier.name|escape:'htmlall':'UTF-8'}</option>
                                            {/foreach}
                                        </select>
                                <span style="position:relative;top:-4px;">&nbsp;&nbsp;<img src="{$settings.images_url|escape:'quotes':'UTF-8'}next.png" style="max-height:16px;opacity:0.5" alt=""/>&nbsp;&nbsp;</span>
                                        <select name="carrier_multichannel[{$id_lang|intval}][amazon][]" style="width:250px;">
                                            <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                            <option value=""></option>
                                            {foreach from=$carrier.amazon_carrier key=key item=amazon_carrier}
                                                <option value="{$key|escape:'htmlall':'UTF-8'}"
                                                        {if $amazon_carrier.selected}selected{/if}>{$amazon_carrier.name|escape:'htmlall':'UTF-8'}</option>
                                            {/foreach}
                                        </select>
                                        &nbsp;&nbsp;
                                        <span class="add-carrier addnew-multichannel-carrier" rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_add|escape:'quotes':'UTF-8'}><img src="{$settings.images_url|escape:'quotes':'UTF-8'}plus.png" alt="{l s='Add a new carrier' mod='amazon'}"/></span>
                                        <span class="remove-carrier remove-multichannel-carrier" rel="{$index|escape:'htmlall':'UTF-8'}" {$carrier.display_del|escape:'quotes':'UTF-8'}><img src="{$settings.images_url|escape:'quotes':'UTF-8'}minus.png" alt=""/></span>
                                    </div>
                                    <!-- eof fba_multichannel carrier group -->
                                {/foreach}
                                <div id="multichannel-new-carriers-{$id_lang|intval}"></div>

                                <p>
                                    {l s='Associate as relevant as possible your Store\'s carrier with the Amazon carrier for FBA Multi-Channel' mod='amazon'}
                                    <br/>
                                </p>
                            </div>
                        </div>
                        <!-- eof fba_multichannel order carriers -->
                        <div class="margin-form col-lg-12">
                            <hr class="amz-separator" style="width:30%"/>
                        </div>
						<div class="clearfix"></div>
                    {/if}


                </div><!-- div rel=amazon orders -->

                {if $settings.overrides.allow}
                    <div class="form-group">
                        <label class="control-label col-lg-3"
                               style="color:grey">{l s='Shipping Overrides' mod='amazon'}</label>

                        <div class="margin-form clearfix">&nbsp;</div>

                        <label class="control-label col-lg-3">{l s='Standard' mod='amazon'}</label>

                        <div class="margin-form col-lg-9">
                            <select name="overrides_std[{$id_lang|intval}]" style="width:250px; display: inline;">
                                <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                <option value=""></option>
                                {foreach from=$settings.overrides.standard.$id_lang key=key item=override}
                                    <option value="{$key|escape:'htmlall':'UTF-8'}"
                                            {if $override.selected}selected{/if}>{$override.name|escape:'quotes':'UTF-8'}</option>
                                {/foreach}
                            </select><br/>
                            {if $psIs16}<br>{/if}
                        </div>

                        <label class="control-label col-lg-3">{l s='Express' mod='amazon'}</label>

                        <div class="margin-form col-lg-9">
                            <select name="overrides_exp[{$id_lang|intval}]" style="width:250px;">
                                <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                <option value=""></option>
                                {foreach from=$settings.overrides.express.$id_lang key=key item=override}
                                    <option value="{$key|escape:'htmlall':'UTF-8'}"
                                            {if $override.selected}selected{/if}>{$override.name|escape:'quotes':'UTF-8'}</option>
                                {/foreach}
                            </select><br/>
                        </div>
                        <div class="margin-form col-lg-9 col-lg-offset-3">
                            <p>{l s='Amazon Shipping Method to override the default shipping charges when specified in product sheet' mod='amazon'}
                                <br/>
                                {l s='Please refer to the documentation to choose the appropriate value' mod='amazon'}
                                <br/>
                            </p>
                        </div>
                    </div>
                {/if}

                <div rel="amazon-smart-shipping" style="display:none">
                    {if isset($settings.shipping_methods.$id_lang)}
                        <div class="form-group">
                            <label class="control-label col-lg-3"
                                   rel="shipping_mode"><span>{l s='Default Shipping Method' mod='amazon'}</span></label>

                            <div class="margin-form col-lg-9">
                                <select name="shipping_method[{$id_lang|intval}]" style="width:250px;">
                                    <option disabled="disabled">{l s='Choose one of the following' mod='amazon'}</option>
                                    <option value=""></option>
                                    {foreach from=$settings.shipping_methods.$id_lang key=key item=shipping_method}
                                        <option value="{$key|escape:'htmlall':'UTF-8'}"
                                                {if $shipping_method.selected}selected{/if}>{$shipping_method.name|escape:'htmlall':'UTF-8'}</option>
                                    {/foreach}
                                </select><br/>


                            </div>
                        </div>
                    {/if}
                </div>
            </div>

            <!-- validation button -->
            {$settings.validate.$id_lang|escape:'quotes':'UTF-8'}
        </div>
        <!-- eof div menudiv lang -->
    {/foreach}
{/if}
