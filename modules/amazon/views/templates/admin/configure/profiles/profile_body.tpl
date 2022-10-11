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
{if $profiles.marketplaces.show}
    <div class="form-group">
        <h4 class="marketplace-heading"><img src="{$marketplace.image|escape:'quotes':'UTF-8'}"
                                             alt="{$marketplace.name_long|escape:'htmlall':'UTF-8'}"/>{$marketplace.name_long|escape:'htmlall':'UTF-8'}
        </h4>
    </div>
{/if}

<!-- Universe Selector -->
<div class="form-group universe-section">
    <label class="profile-obj-title col-lg-3" rel="universe"><span>{l s='Universe' mod='amazon'}</span></label>

    <div class="margin-form col-lg-9">
        <select name="profiles[universe][_key_][{$id_lang|intval}]" class="profile-universe">
        <option value=""></option>
        {foreach from=$profiles.universes[$id_lang] key=universe_key item=universe}
            <option value="{$universe_key|escape:'htmlall':'UTF-8'}"
                    {if $universe_key == $profile.universe}selected{/if}>{$universe|escape:'htmlall':'UTF-8'}</option>
        {/foreach}
        </select><span class="mandatory">{l s='Required' mod='amazon'}</span>


        <span class="xsd-load" style="display:none;">
            <img src="{$profiles.images_url|escape:'quotes':'UTF-8'}small-loader.gif"/>&nbsp;&nbsp;{l s='Loading data from Amazon' mod='amazon'}
        </span>

        <span class="fetch-result"></span>
    </div>
</div>
<!-- eof Universe Selector -->

<div class="form-group product-type-section">
    <!-- Product Type Selector -->
    <label class="profile-obj-title col-lg-3" rel="product_type"><span>{l s='Product Type' mod='amazon'}</span></label>

    <div class="margin-form col-lg-9">
        <select name="profiles[product_type][_key_][{$id_lang|intval}]" class="product_type">
            <option value="{$profile.product_type|escape:'htmlall':'UTF-8'}"
                    selected="selected">{$profile.product_type_translation|escape:'htmlall':'UTF-8'}</option>
        </select><span class="mandatory">{l s='Required' mod='amazon'}</span>

        <img src="{$profiles.images_url|escape:'quotes':'UTF-8'}refresh_static.png" class="type_reload"
             rel="{$profiles.images_url|escape:'quotes':'UTF-8'}refresh.png"/>

        <div class="margin-form col-lg-9">
            <hr style="width:30%;">
        </div>
    </div>
</div>
<!-- eof Product Type Selector -->

<!-- Extra Fields and Specifics Fields -->
<div class="margin-form col-lg-9 col-lg-offset-3 specific-fields" {if !isset($profile.specifics)}style="display:none;"{/if}>
    <div class="margin-form col-lg-6">
        <div class="mandatory_fields">
            {if isset($profile.specifics) && is_array($profile.specifics)}
                <input type="hidden" rel="has_data" value="0"/>
                <input type="hidden" rel="profile_id_lang" value="{$id_lang|intval}"/>
                <input type="hidden" rel="profile_key" value="{$profile_key|escape:'htmlall':'UTF-8'}"/>
            {elseif isset($profile.specifics)}
                <input type="hidden" rel="has_data" value="1"/>
                <input type="hidden" rel="profile_key" value="{$profile_key|escape:'htmlall':'UTF-8'}"/>
                {$profile.specifics}<!-- VALIDATION: Generated HTML with Quotes, unable to escape it... -->
            {/if}
        </div>


        <div class="profile-help" ><span class="mandatory">{l s='Required' mod='amazon'}</span>&nbsp;&nbsp;{l s='Please fill the mandatory fields for this profile' mod='amazon'}
        </div>
    </div>
    <div class="margin-form col-lg-4 optional-fields">
        <div class="optional-header">
            <p>{l s='Optional available fields' mod='amazon'}<img src="{$profiles.images_url|escape:'htmlall':'UTF-8'}/cross.png" title="{l s='Clear optional attributes' mod='amazon'}" class="clear-optionals" /></p>
            <input type="button" class="btn btn-default pull-right load-optionals" value="Load" />
        </div>
        <div class="optional-container">
        </div>
    </div>
</div>

<!-- eof Extra Fields and Specifics Fields -->

{if isset($profile.strategies)}
    <div rel="amazon-repricing">
        <div class="form-group margin-form profile-repricing">
            <label class="profile-obj-title control-label col-lg-3"
                   rel="profile_repricing"><span>{l s='Repricing Strategy' mod='amazon'}</span></label>

            <div class="col-lg-9">
                {if isset($profile.strategies) && is_array($profile.strategies) && count($profile.strategies)}
                    <select name="profiles[repricing][_key_][{$id_lang|intval}]">
                        <option value=""></option>
                        {foreach from=$profile.strategies key=strategy_key item=strategy}
                            <option value="{$strategy.key|escape:'htmlall':'UTF-8'}"
                                    {if $strategy.key == $profile.repricing}selected{/if}>{$strategy.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {else}
                    <span style="color:orange">{l s='Please configure priorly: Repricing tab, Strategies section' mod='amazon'}</span>
                {/if}
            </div>
            <div class="margin-form col-lg-9">
                <hr style="width:30%;">
            </div>
        </div>

    </div>
{/if}

<div class="form-group margin-form  bullet-points">
    <label class="profile-obj-title control-label col-lg-3"
           rel="bullet_point"><span>{l s='Product Key Features Strategy' mod='amazon'}</span></label>

    <div class="col-lg-9">
        <select name="profiles[bullet_point_strategy][_key_][{$id_lang|intval}]">
            <option value=""></option>
            <option rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none;" value="{$profiles.bullet_point_strategy_a|escape:'htmlall':'UTF-8'}" {if isset($profile.bullet_point_strategy) && $profile.bullet_point_strategy  == $profiles.bullet_point_strategy_a}selected{/if}>{l s='Attributes' mod='amazon'}</option>
            <option rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none;" value="{$profiles.bullet_point_strategy_af|escape:'htmlall':'UTF-8'}" {if isset($profile.bullet_point_strategy) && $profile.bullet_point_strategy  == $profiles.bullet_point_strategy_af}selected{/if}>{l s='Attributes, Features' mod='amazon'}</option>
            <option value="{$profiles.bullet_point_strategy_f|escape:'htmlall':'UTF-8'}" {if isset($profile.bullet_point_strategy) && $profile.bullet_point_strategy  == $profiles.bullet_point_strategy_f}selected{/if}>{l s='Features' mod='amazon'}</option>
            {if ($profiles.bullet_point_strategy_shortd)}
            <option value="{$profiles.bullet_point_strategy_d|escape:'htmlall':'UTF-8'}" {if isset($profile.bullet_point_strategy) && $profile.bullet_point_strategy  == $profiles.bullet_point_strategy_d}selected{/if}>{l s='Short Description' mod='amazon'}</option>
            {/if}
            <option rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none;" value="{$profiles.bullet_point_strategy_daf|escape:'htmlall':'UTF-8'}" {if isset($profile.bullet_point_strategy) && $profile.bullet_point_strategy  == $profiles.bullet_point_strategy_daf}selected{/if}>{l s='Short Description, Attributes, Features' mod='amazon'}</option>
            {if ($profiles.bullet_point_strategy_shortd)}
            <option value="{$profiles.bullet_point_strategy_df|escape:'htmlall':'UTF-8'}" {if isset($profile.bullet_point_strategy) && $profile.bullet_point_strategy  == $profiles.bullet_point_strategy_df}selected{/if}>{l s='Short Description, Features' mod='amazon'}</option>
            {/if}
        </select>
        <input type="checkbox" name="profiles[bullet_point_labels][_key_][{$id_lang|intval}]" value="1"
               {if isset($profile.bullet_point_labels) && $profile.bullet_point_labels}checked{/if} /><label
                rel="bullet_point_labels" class="span_text">{l s='With Labels' mod='amazon'}</label>
        <span class="recommended">{l s='Recommended' mod='amazon'}</span>
    </div>
    <div class="margin-form col-lg-9">
        <hr style="width:30%;">
    </div>
</div>

{*
            <div class="form-group bullet-points">
                <label class="profile-obj-title control-label col-lg-3" rel="bullet_point"><span>{l s='Product Key Features' mod='amazon'}</span></label>

                <div class="margin-form col-lg-9">
                    {foreach from=$profile.bullet_point key=id_bullet_point item=bullet_point}
                        <div class="bullet-point-container" rel="{$id_bullet_point|intval}">
                            <span class="bullet-point-item"><span {if !$bullet_point}style="display:none;"{/if}>{l s='Product Key Feature Data' mod='amazon'}<a href="#">x</a></span></span>

                            <input type="hidden" name="profiles[bullet_point][_key_][{$id_bullet_point|intval}]" class="profile-bullet-point" value="{$bullet_point|escape:'htmlall':'UTF-8'}"/>

                            <img src="{$profiles.images_url|escape:'htmlall':'UTF-8'}bullet_edit.png" title="{l s='Product Key Feature' mod='amazon'} #{$id_bullet_point + 1|intval}" class="bullet-point-edit-img"/>
                        </div>
                    {/foreach}
                    <div class="form-group">
                        <span class="recommended">{l s='Recommended' mod='amazon'}</span>
                    </div>
                </div>
                <hr style="width:30%;">
            </div>
*}

<!-- Recommended Browse Nodes
// https://catalog-mapper-fr.amazon.fr/catm/classifier/ProductClassifier.amzn
// https://sellercentral.amazon.fr/gp/help/help-page.html/ref=pt_200956770_cont_scsearch?ie=UTF8&itemID=200956770
// Browse Node ID
-->
<div class="form-group  margin-form recommended-browse-nodes">
    <label class="profile-obj-title control-label col-lg-3"
           rel="browse_node"><span>{l s='Recommended Browse Node' mod='amazon'}</span></label>

    <div class="col-lg-9">
        <input type="text" name="profiles[browsenode][_key_][{$id_lang|intval}]" class="browsenode"
               value="{if isset($profile.browse_node)}{$profile.browse_node|escape:'htmlall':'UTF-8'}{/if}"/>
        <span class="mandatory">{l s='Required' mod='amazon'}</span>
    </div>
    <div class="margin-form col-lg-9">
        <hr style="width:30%;">
    </div>
</div>

<!-- eof Recommended Browse Nodes -->
<div class="clearfix"></div>

<div rel="amazon-worldwide" class="amazon-worldwide">
    <div class="form-group margin-form">
        <!-- Item Type for USA -->
        <label class="profile-obj-title control-label col-lg-3"
               rel="itemtype"><span>{l s='Item Type' mod='amazon'}</span></label>

        <div class="col-lg-9">
            <input type="text" name="profiles[item_type][_key_][{$id_lang|intval}]" class="item-type"
                   value="{if isset($profile.item_type)}{$profile.item_type|escape:'htmlall':'UTF-8'}{/if}"/>
            <img src="{$profiles.images_url|escape:'quotes':'UTF-8'}geo_flags/us.jpg" alt="USA"
                 class="browse-lang-img"/>&nbsp;
            <img src="{$profiles.images_url|escape:'quotes':'UTF-8'}geo_flags/in.jpg" alt="India"
                 class="browse-lang-img"/>&nbsp;
            <span class="browse-lang-txt">{l s='U.S.A. / India Only' mod='amazon'}</span>
            <span class="mandatory">{l s='Required' mod='amazon'}</span><br/>
        </div>
    </div>
    <div class="margin-form col-lg-9">
        <hr style="width:30%;">
    </div>
    <div style="clear:both;"></div>
</div>

<div class="clearfix"><br /></div>
<!-- Default Shipping Templates --->
{if (isset($profiles.ptc) && isset($profiles.ptc[$id_lang]) && is_array($profiles.ptc[$id_lang]))}
    <div class="form-group margin-form profiles-ptc-list">
        <label class="profile-obj-title control-label col-lg-3" rel="ptc"><span>{l s='Product Tax Code override' mod='amazon'}</span></label>
        <div class="margin-form col-lg-9">
            <select name="profiles[ptc][_key_][{$id_lang|intval}]">
                <option></option>
                {foreach from=$profiles.ptc[$id_lang] item=ptc}
                    <option value="{$ptc.ptc|escape:'htmlall':'UTF-8'}" {if isset($profile.ptc_selected) && $ptc.ptc == $profile.ptc_selected}selected{/if}>{$ptc.description|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="margin-form col-lg-9">
            <hr style="width:30%;">
        </div>
    </div>
{/if}

<div class="clearfix"></div>

<div class="amazon-prices-rules" rel="amazon-prices-rules" style="display:none;">
    <div class="form-group margin-form">
        <label class="profile-obj-title control-label col-lg-3"
               rel="price_rule"><span>{l s='Default Price Rule' mod='amazon'}</span></label>
        <select name="profiles[price_rule][_key_][{$id_lang|intval}][type]" class="price-rule-type"
                style="display: inline; max-width:150px;vertical-align: top;">
        <option value="percent"
                {if ($profile.price_rule.type == 'percent')}selected{/if}>{l s='Percentage' mod='amazon'}</option>
        {if isset($profile.price_rule.currency_sign)}
            <option value="value"
                    {if ($profile.price_rule.type == 'value')}selected{/if}>{l s='Value' mod='amazon'}</option>
        {/if}
        </select>
        &nbsp;&nbsp;

        <div id="default-price-rule-{$id_lang|intval}" class="default-price-rule" style="display: inline-block;">
            {foreach from=$profile.price_rule.rule.from key=index item=value}
                <div class="price-rule">
                    <input type="text" name="profiles[price_rule][_key_][{$id_lang|intval}][rule][from][]" rel="from"
                           style="width:50px" value="{$profile.price_rule.rule.from[$index]|escape:'htmlall':'UTF-8'}"/>&nbsp;&nbsp;{$profile.price_rule.currency_sign|escape:'htmlall':'UTF-8'}
                    <span>&nbsp;&nbsp;<img src="{$settings.images_url|escape:'quotes':'UTF-8'}slash.png"
                                           class="price-rule-slash" alt=""/>&nbsp;&nbsp;</span>
                    <input type="text" name="profiles[price_rule][_key_][{$id_lang|intval}][rule][to][]" rel="to"
                           style="width:50px" value="{$profile.price_rule.rule.to[$index]|escape:'htmlall':'UTF-8'}"/>&nbsp;&nbsp;{$profile.price_rule.currency_sign|escape:'htmlall':'UTF-8'}
                    <span>&nbsp;&nbsp;<img src="{$settings.images_url|escape:'quotes':'UTF-8'}next.png"
                                           class="price-rule-next" alt=""/>&nbsp;&nbsp;</span>
                    <select name="profiles[price_rule][_key_][{$id_lang|intval}][rule][percent][]" rel="percent"
                            style="width:100px;{if ($profile.price_rule.type != 'percent')}display:none;{/if}">
                    <option></option>
                    {section name=price_rule_percent loop=99}
                        <option value="{$smarty.section.price_rule_percent.iteration|escape:'htmlall':'UTF-8'}"
                                {if $profile.price_rule.rule.percent[$index] == $smarty.section.price_rule_percent.iteration}selected{/if}>{$smarty.section.price_rule_percent.iteration|escape:'htmlall':'UTF-8'}
                            &#37;</option>
                    {/section}
                    <option disabled>--</option>
                    {section name=price_rule_percent loop=99}
                        <option value="-{$smarty.section.price_rule_percent.iteration|escape:'htmlall':'UTF-8'}"
                                {if $profile.price_rule.rule.percent[$index] == ($smarty.section.price_rule_percent.iteration * -1)}selected{/if}>
                            -{$smarty.section.price_rule_percent.iteration|escape:'htmlall':'UTF-8'} &#37;</option>
                    {/section}
                    </select>
                    {if isset($profile.price_rule.currency_sign)}
                        <select name="profiles[price_rule][_key_][{$id_lang|intval}][rule][value][]" rel="value" style="width:100px;{if ($profile.price_rule.type != 'value')}display:none;{/if}">
                            <option></option>
                            {section name=price_rule_value loop=99}
                            <option value="{$smarty.section.price_rule_value.iteration|escape:'htmlall':'UTF-8'}" {if $profile.price_rule.rule.value[$index] == $smarty.section.price_rule_value.iteration}selected{/if}>{$smarty.section.price_rule_value.iteration|escape:'htmlall':'UTF-8'} {$profile.price_rule.currency_sign|escape:'htmlall':'UTF-8'}</option>
                            {/section}
                            <option disabled>--</option>
                            {section name=price_rule_value loop=99}
                            <option value="-{$smarty.section.price_rule_value.iteration|escape:'htmlall':'UTF-8'}" {if $profile.price_rule.rule.value[$index] == ($smarty.section.price_rule_value.iteration * -1)}selected{/if}>-{$smarty.section.price_rule_value.iteration|escape:'htmlall':'UTF-8'} {$profile.price_rule.currency_sign|escape:'htmlall':'UTF-8'}</option>
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
        </div>
        <div class="margin-form col-lg-9">
            <hr style="width:30%;">
        </div>
    </div>
</div>

<!-- Shipping Delay / Latency -->
<div class="form-group margin-form">
    <label class="profile-obj-title control-label col-lg-3"
           rel="latency"><span>{l s='Latency' mod='amazon'}</span></label>

    <div class="col-lg-9">
        <input type="text" name="profiles[latency][_key_][{$id_lang|intval}]" style="width:40px"
               value="{$profile.latency|escape:'htmlall':'UTF-8'}"/>


    </div>
    <div class="margin-form col-lg-9">
        <hr style="width:30%;">
    </div>
</div>


<!-- Default Shipping Templates --->
{if (isset($profiles.shipping_templates.enabled) && $profiles.shipping_templates.enabled) && isset($profiles.shipping_templates.groups[$id_lang]) && is_array($profiles.shipping_templates.groups[$id_lang]) && count($profiles.shipping_templates.groups[$id_lang])}
<div class="form-group profiles-shipping-templates">
    <label class="profile-obj-title control-label col-lg-3" rel="default_template"><span>{l s='Default Shipping Template' mod='amazon'}</span></label>
    <div class="margin-form col-lg-9">
        <select name="profiles[shipping_group][_key_][{$id_lang|intval}]">
        <option></option>
        {foreach from=$profiles.shipping_templates.groups[$id_lang] key=group_key item=group_name}
            <option value="{$group_key|escape:'htmlall':'UTF-8'}" {if isset($profile.shipping_group) && $group_key == $profile.shipping_group}selected{/if}>{$group_name|escape:'htmlall':'UTF-8'}</option>
        {/foreach}
        </select>
    </div>
    <div class="margin-form col-lg-9">
        <hr style="width:30%;">
    </div>
</div>
{/if}

<!-- Code Exemption -->

<div class="profiles-code-exempt amazon-gcid" rel="amazon-gcid">
    <div class="form-group margin-form">
        <!-- EAN exemption -->
        <label class="profile-obj-title control-label col-lg-3 profile-code-exemption-l">{l s='Code Exemption' mod='amazon'}
            <span class="expert">{l s='Expert' mod='amazon'}</span></label>

        <div class="col-lg-9">
            <span style="font-size:1.1em;color:black;">{l s='Use EAN/UPC exemption for this profile' mod='amazon'}</span><br/>
            <select name="profiles[code_exemption][_key_][{$id_lang|intval}]">
                <option value="{$profiles.exemptions.none|escape:'htmlall':'UTF-8'}"
                        {if isset($profile.code_exemption) && $profile.code_exemption == $profiles.exemptions.none}selected{/if}></option>
                <option value="{$profiles.exemptions.compatibility|escape:'htmlall':'UTF-8'}"
                        {if isset($profile.code_exemption) && $profile.code_exemption == $profiles.exemptions.compatibility}selected{/if}
                        style="display:none"></option>
                <option value="{$profiles.exemptions.model_number|escape:'htmlall':'UTF-8'}"
                        {if isset($profile.code_exemption) && $profile.code_exemption == $profiles.exemptions.model_number}selected{/if}>
                    Model Number
                </option>
                <option value="{$profiles.exemptions.model_name|escape:'htmlall':'UTF-8'}"
                        {if isset($profile.code_exemption) && $profile.code_exemption == $profiles.exemptions.model_name}selected{/if}>
                    Model Name
                </option>
                <option value="{$profiles.exemptions.mfr_part_number|escape:'htmlall':'UTF-8'}"
                        {if isset($profile.code_exemption) && $profile.code_exemption == $profiles.exemptions.mfr_part_number}selected{/if}>
                    Manufacturer Part Number
                </option>
                <option value="{$profiles.exemptions.catalog_number|escape:'htmlall':'UTF-8'}"
                        {if isset($profile.code_exemption) && $profile.code_exemption == $profiles.exemptions.catalog_number}selected{/if}>
                    Catalog Number
                </option>
                <option value="{$profiles.exemptions.style_number|escape:'htmlall':'UTF-8'}"
                        {if isset($profile.code_exemption) && $profile.code_exemption == $profiles.exemptions.style_number}selected{/if}>
                    Style Number
                </option>
                <option value="{$profiles.exemptions.attr_ean|escape:'htmlall':'UTF-8'}"
                        {if isset($profile.code_exemption) && $profile.code_exemption == $profiles.exemptions.attr_ean}selected{/if}>
                    EAN/UPC
                </option>
                <option value="{$profiles.exemptions.generic|escape:'htmlall':'UTF-8'}"
                        {if isset($profile.code_exemption) && $profile.code_exemption == $profiles.exemptions.generic}selected{/if}>
                    Generic
                </option>
            </select>
            <input name="profiles[code_exemption_options][_key_][{$id_lang|intval}][private_label]" type="checkbox"
                   value="1"
                   {if isset($profile.code_exemption) && $profile.code_exemption_options.private_label}checked{/if} />&nbsp;&nbsp;<span
                    style="font-size:0.9em;color:black;">{l s='Private Label' mod='amazon'}</span>
        </div>
    </div>
    <div class="margin-form col-lg-9">
        <hr style="width:30%;">
    </div>
    <div style="clear:both;"></div>
</div>


<div rel="amazon-expert-mode" class="amazon-expert-mode">
    <div class="form-group margin-form">
        <!-- EAN exemption -->
        <label class="profile-obj-title control-label col-lg-3">{l s='SKU as Supplier Reference' mod='amazon'}<span
                    class="expert">{l s='Expert' mod='amazon'}</span></label>

        <div class="col-lg-9">
                                    <span style="position:relative;">
                                        <input name="profiles[sku_as_supplier_reference][_key_][{$id_lang|intval}]"
                                               type="checkbox" value="1"
                                               {if isset($profile.sku_as_supplier_reference) && $profile.sku_as_supplier_reference}checked{/if} />&nbsp;&nbsp;<span
                                                style="font-size:1.1em;color:black;">{l s='Send the Reference/SKU as the Supplier Reference' mod='amazon'}</span>
                                        <input name="profiles[sku_as_sup_ref_unconditionnaly][_key_][{$id_lang|intval}]"
                                               type="checkbox" value="1"
                                               {if isset($profile.sku_as_sup_ref_unconditionnaly) && $profile.sku_as_sup_ref_unconditionnaly}checked{/if} />&nbsp;&nbsp;<span
                                                style="font-size:0.9em;color:black;">{l s='Unconditionnaly' mod='amazon'}</span>
                                    </span>
        </div>
    </div>
    <div class="margin-form col-lg-9">
        <hr style="width:30%;">
    </div>
</div>


<!-- eof Shipping Delay / Latency -->