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
<div id="menudiv-mapping"
     class="tabItem {if $mapping.selected_tab}selected{/if} panel form-horizontal{if !$psIs16} ps15{/if}">
    <h3>{l s='Mappings' mod='amazon'}</h3>

    <input type="hidden" id="text-add-select-option" value="{l s='Select an Option' mod='amazon'}"/>
    <input type="hidden" id="text-add-select-no-result" value="{l s='No results match' mod='amazon'}"/>

    {if !$amazon.is_lite}
    <div class="form-group">
        <div class="margin-form">
            <div class="amz-info-level-info {if $psIs16}alert alert-info col-lg-offset-3{/if}" style="font-size:1.1em">
                <ul>
                    <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                    <li>{$mapping.tutorial|escape:'quotes':'UTF-8'}</li>
                </ul>
            </div>
        </div>
    </div>
    {/if}

    {if isset($mapping.count) && !$mapping.count}
        <div class="form-group">
            <div class="margin-form col-lg-offset-3 col-lg-9 {$class_warning|escape:'htmlall':'UTF-8'}">
                {l s='No mappings to display' mod='amazon'}:</li>
                <ul>
                    <li>{l s='Mappings appear after profiles configuration, if necessary.' mod='amazon'}</li>
                    <li>{l s='If there are no fields to map, mappings won\'t appear.' mod='amazon'}</li>
                </ul>
            </div>
        </div>
        <div class="form-group">
            <hr class="amz-separator" style="width:30%"/>
        </div>
    {/if}

    {if isset($mapping.lang) && is_array($mapping.lang) && count($mapping.lang)}
        <div rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none;">
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Ungroup Mappings' mod='amazon'}<span
                            class="expert">{l s='Expert' mod='amazon'}</span></label>

                <div class="margin-form col-lg-9">
                    <input type="checkbox" rel="mapping[ungroup]" {if $mapping.ungroup}checked{/if} value="1"
                           class="fixed"/>&nbsp;<span class="span_text">{l s='Yes' mod='amazon'}</span>

                    <p>
                        {l s='Mappings are groupped by universe and attribute field, optimal method. Ungroup will allow you to have one mapping by universe, field and profile.' mod='amazon'}
                        <br/>
                        <span style="color:red">{l s='After changing this option, previous mappings will be lost' mod='amazon'}</span>
                    </p>
                </div>
                <div class="margin-form col-lg-12">
                    <hr class="amz-separator" style="width:30%"/>
                </div>
            </div>
        </div>
        {if isset($mapping.fixed) && is_array($mapping.fixed) && count($mapping.fixed)}
            {foreach from=$mapping.lang key=id_lang item=mapping_lang}
                {if isset($mapping.fixed[$id_lang])}
                    <div class="amazon-mapping-section">
                        <div class="form-group">
                            <label class="control-label col-lg-3"
                                   style="color:grey">{l s='Fixed Values for' mod='amazon'}</label>

                            <div class="margin-form col-lg-9"><span style="position:relative;top:+2px;font-size:1em;">
                                <img src="{$mapping_lang.flag|escape:'htmlall':'UTF-8'}"
                                     alt="{$mapping_lang.name|escape:'htmlall':'UTF-8'}"/> <b
                                            style="position:relative;top:+2px">{$mapping_lang.name|escape:'htmlall':'UTF-8'}</b>
                            </span>&nbsp;&nbsp;
                            <span class="mapping-collapse">
                                <a href="javascript:void(0)" rel="{$mapping_lang.iso_code|escape:'htmlall':'UTF-8'}"
                                   {if $mapping_lang.fixed_collapsed}style="display:none;"{/if}>[ +
                                    ]&nbsp;&nbsp;{l s='Show' mod='amazon'}</a>
                                <a href="javascript:void(0)" rel="{$mapping_lang.iso_code|escape:'htmlall':'UTF-8'}"
                                   {if !$mapping_lang.fixed_collapsed}style="display:none;"{/if}>[ -
                                    ]&nbsp;&nbsp;{l s='Hide' mod='amazon'}</a>
                            </span>
                            </div>
                        </div>
                        <div class="cleaner"></div>
                        <br/>

                        <div class="amazon-mapping" {if !$mapping_lang.fixed_collapsed}style="display:none;"{/if}>
                            {assign var=mappings value=$mapping.fixed[$id_lang]}
                            {foreach from=$mappings key=mapping_key item=mapping_groups}
                                {foreach from=$mapping_groups key=amazon_attribute item=mapping_group}
                                    <div class="form-group">
                                        <div class="margin-form col-lg-offset-3 col-lg-9 mapping-type">
                                            <h4>{$mapping_group.name|escape:'quotes':'UTF-8'}</h4>
                                        </div>
                                    </div>
                                    <div class="cleaner"><!-- PS 1.5 --></div>
                                    <div class="form-group">
                                        <label for="mapping-group-{$id_lang|intval}-{$mapping_key|escape:'htmlall':'UTF-8'}-{$amazon_attribute|escape:'htmlall':'UTF-8'}"
                                               class="control-label col-lg-3">
                                            <span style="color:green;">{l s='Default Text' mod='amazon'}</span>&nbsp;&nbsp;
                                        </label>

                                        <div class="margin-form col-lg-9">
                                            <input type="text" style="width:250px"
                                                   rel="mapping[fixed][{$id_lang|intval}][{$mapping_key|escape:'htmlall':'UTF-8'}][{$amazon_attribute|escape:'htmlall':'UTF-8'}]"
                                                   value="{$mapping_group.value|escape:'htmlall':'UTF-8'}"
                                                   placeholder="{l s='Enter a valid value for' mod='amazon'} {$amazon_attribute|escape:'htmlall':'UTF-8'} {l s='here' mod='amazon'}"/>
                                        </div>
                                    </div>
                                {/foreach}
                            {/foreach}

                        </div>
                    </div>
                    <!-- amazon-mapping attributes -->
                    <hr style="width:30%"/>
                {/if}
            {/foreach}
        {/if}


        {if isset($mapping.attribute) &&  is_array($mapping.attribute) && count($mapping.attribute)}
            {foreach from=$mapping.lang key=id_lang item=mapping_lang}
                {if isset($mapping.attribute[$id_lang])}
                    <div class="amazon-mapping-section">
                        <div class="form-group">
                            <label class="control-label col-lg-3"
                                   style="color:grey">{l s='Attributes for' mod='amazon'}</label>

                            <div class="margin-form col-lg-9"><span style="position:relative;top:+2px;font-size:1em;">
                                <img src="{$mapping_lang.flag|escape:'htmlall':'UTF-8'}"
                                     alt="{$mapping_lang.name|escape:'htmlall':'UTF-8'}"/> <b
                                            style="position:relative;top:+2px">{$mapping_lang.name|escape:'htmlall':'UTF-8'}</b>
                            </span>&nbsp;&nbsp;
                            <span class="mapping-collapse">
                                <a href="javascript:void(0)" rel="{$mapping_lang.iso_code|escape:'htmlall':'UTF-8'}"
                                   {if $mapping_lang.attr_collapsed}style="display:none;"{/if}>[ +
                                    ]&nbsp;&nbsp;{l s='Show' mod='amazon'}</a>
                                <a href="javascript:void(0)" rel="{$mapping_lang.iso_code|escape:'htmlall':'UTF-8'}"
                                   {if !$mapping_lang.attr_collapsed}style="display:none;"{/if}>[ -
                                    ]&nbsp;&nbsp;{l s='Hide' mod='amazon'}</a>
                            </span>
                            </div>
                        </div>
                        <div class="cleaner"></div>
                        <br/>

                        <div class="amazon-mapping" {if !$mapping_lang.attr_collapsed}style="display:none;"{/if}>
                            {assign var=mappings value=$mapping.attribute[$id_lang]}
                            {foreach from=$mappings key=mapping_key item=mapping_groups}
                                {foreach from=$mapping_groups key=id_attribute_group item=mapping_group}
                                    <div class="form-group">
                                        <div class="margin-form col-lg-offset-3 col-lg-9 mapping-type">
                                            <h4>{$mapping_group.name|escape:'quotes':'UTF-8'}</h4>
                                        </div>
                                    </div>
                                    <div class="cleaner"><!-- PS 1.5 --></div>
                                    <div class="form-group {if !$mapping_group.has_valid_values}free-mapping{/if}">
                                        <label for="mapping-group-{$id_lang|intval}-{$mapping_key|escape:'htmlall':'UTF-8'}-{$id_attribute_group|intval}"
                                               class="control-label col-lg-3">
                                            {if $mapping_group.has_valid_values}
                                                <label rel="constrained_mapping"><span
                                                            style="color:orange;">{l s='Constrained Mapping' mod='amazon'}</span></label>
                                                &nbsp;&nbsp;
                                            {else}
                                                <label rel="free_mapping"> <span
                                                            style="color:green;">{l s='Free Mapping' mod='amazon'}</span></label>
                                                &nbsp;&nbsp;
                                            {/if}
                                        </label>

                                        <div class="margin-form col-lg-9">
                                            {foreach from=$mapping_group.left key=index item=attribute}
                                                <div id="mapping-group-{$id_lang|intval}-{$mapping_key|escape:'htmlall':'UTF-8'}-{$id_attribute_group|intval}-{$index|escape:'htmlall':'UTF-8'}"
                                                     class="mapping-group">
                                                    <input type="text"
                                                           rel="{$id_lang|intval}-{$mapping_key|escape:'htmlall':'UTF-8'}-{$id_attribute_group|intval}"
                                                           class="input-left" style="width:250px" readonly
                                                           value="{$attribute.name|escape:'htmlall':'UTF-8'}"/>

                                                <span class="arrow">
                                                    <img src="{$mapping.images_url|escape:'quotes':'UTF-8'}next.png"
                                                         alt=""/>
                                                </span>

                                                    {if $mapping_group.has_valid_values}
                                                        <select rel="mapping[attributes][const][{$id_lang|intval}][{$mapping_key|escape:'htmlall':'UTF-8'}][{$id_attribute_group|intval}][{$attribute.id_attribute|escape:'htmlall':'UTF-8'}]" style="width:250px">
                                                        <option value=""></option>
                                                        {foreach from=$mapping_group.right key=key item=valid_value}
                                                        <option value="{$key|escape:'quotes'}" {if $attribute.mapping == $key}selected{/if}>{$valid_value|escape:'htmlall':'UTF-8'}</option>
                                                    {/foreach}
                                                        </select>
                                                    {else}
                                                        <input type="text" rel="mapping[attributes][free][{$id_lang|intval}][{$mapping_key|escape:'htmlall':'UTF-8'}][{$id_attribute_group|intval}][{$attribute.id_attribute|escape:'htmlall':'UTF-8'}]" value="{$attribute.mapping|escape:'htmlall':'UTF-8'}"  class="input-right" style="width:250px;">
                                                    {/if}
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                    {if $mapping_group['mandatory']}
                                        {if $mapping_group.has_valid_values}
                                            <input type="hidden"
                                                   name="mapping[mandatory][attributes][const][{$id_lang|intval}][{$mapping_key|escape:'htmlall':'UTF-8'}][{$id_attribute_group|intval}]"
                                                   value="1">
                                        {else}
                                            <input type="hidden"
                                                   name="mapping[mandatory][attributes][free][{$id_lang|intval}][{$mapping_key|escape:'htmlall':'UTF-8'}][{$id_attribute_group|intval}]"
                                                   value="1">
                                        {/if}
                                    {/if}

                                    {if isset($mapping_group['match_list'])}
                                        <div class="form-group" style="margin-top:15px;">
                                            <div class="margin-form col-lg-offset-3">
                                                <span style="color:darkgrey">{l s='Those values have been automatically matched' mod='amazon'}
                                                    : </span><span
                                                        style="color:darkgreen">{$mapping_group['match_list']|escape:'htmlall':'UTF-8'}</span>
                                            </div>
                                        </div>
                                    {/if}

                                {/foreach}
                            {/foreach}

                        </div>
                    </div>
                    <!-- amazon-mapping attributes -->
                    <hr style="width:30%"/>
                {/if}
            {/foreach}
        {/if}


        {if isset($mapping.feature) && is_array($mapping.feature) && count($mapping.feature)}
            {foreach from=$mapping.lang key=id_lang item=mapping_lang}
                {if isset($mapping.feature[$id_lang])}
                    <div class="amazon-mapping-section">
                        <div class="form-group">
                            <label class="control-label col-lg-3"
                                   style="color:grey">{l s='Features for' mod='amazon'}</label>

                            <div class="margin-form col-lg-9"><span style="position:relative;top:+2px;font-size:1em;">
                                <img src="{$mapping_lang.flag|escape:'htmlall':'UTF-8'}"
                                     alt="{$mapping_lang.name|escape:'htmlall':'UTF-8'}"/> <b
                                            style="position:relative;top:+2px">{$mapping_lang.name|escape:'htmlall':'UTF-8'}</b>
                            </span>&nbsp;&nbsp;
                            <span class="mapping-collapse">
                                <a href="javascript:void(0)" rel="{$mapping_lang.iso_code|escape:'htmlall':'UTF-8'}"
                                   {if $mapping_lang.feat_collapsed}style="display:none;"{/if}>[ +
                                    ]&nbsp;&nbsp;{l s='Show' mod='amazon'}</a>
                                <a href="javascript:void(0)" rel="{$mapping_lang.iso_code|escape:'htmlall':'UTF-8'}"
                                   {if !$mapping_lang.feat_collapsed}style="display:none;"{/if}>[ -
                                    ]&nbsp;&nbsp;{l s='Hide' mod='amazon'}</a>
                            </span>
                            </div>
                        </div>
                        <div class="cleaner"></div>
                        <br/>

                        <div class="amazon-mapping mapping-group"
                             {if !$mapping_lang.feat_collapsed}style="display:none;"{/if}>
                            {assign var=mappings value=$mapping.feature[$id_lang]}
                            {foreach from=$mappings key=mapping_key item=mapping_groups}
                                {foreach from=$mapping_groups key=id_feature item=mapping_group}
                                    <div class="form-group">
                                        <div class="margin-form col-lg-offset-3 col-lg-9 mapping-type">
                                            <h4>{$mapping_group.name|escape:'quotes':'UTF-8'}</h4>
                                        </div>
                                    </div>
                                    <div class="cleaner"><!-- PS 1.5 --></div>
                                    <div class="form-group {if !$mapping_group.has_valid_values}free-mapping{/if}">
                                        <label for="mapping-group-{$id_lang|intval}-{$mapping_key|escape:'htmlall':'UTF-8'}-{$id_feature|intval}"
                                               class="control-label col-lg-3">
                                            {if $mapping_group.has_valid_values}
                                                <label rel="constrained_mapping"><span
                                                            style="color:orange;">{l s='Constrained Mapping' mod='amazon'}</span></label>
                                                &nbsp;&nbsp;
                                            {else}
                                                <label rel="free_mapping"> <span
                                                            style="color:green;">{l s='Free Mapping' mod='amazon'}</span></label>
                                                &nbsp;&nbsp;
                                            {/if}
                                        </label>

                                        <div class="margin-form col-lg-9">
                                            {foreach from=$mapping_group.left key=index item=feature_value}
                                                <div id="mapping-group-{$id_lang|intval}-{$mapping_key|escape:'htmlall':'UTF-8'}-{$id_feature|intval}-{$index|escape:'htmlall':'UTF-8'}"
                                                     class="mapping-group">
                                                    <input type="text" class="input-left" style="width:250px" readonly
                                                           value="{$feature_value.value|escape:'htmlall':'UTF-8'}"/>

                                                <span class="arrow">
                                                    <img src="{$mapping.images_url|escape:'quotes':'UTF-8'}next.png"
                                                         alt=""/>
                                                </span>

                                                    {if $mapping_group.has_valid_values}
                                                        <select rel="mapping[features][const][{$id_lang|intval}][{$mapping_key|escape:'htmlall':'UTF-8'}][{$id_feature|intval}][{$feature_value.id_feature_value|escape:'htmlall':'UTF-8'}]" rel="{$id_lang|intval}-{$id_feature|intval}" style="width:250px">
                                                        <option value=""></option>
                                                        {foreach from=$mapping_group.right key=key item=valid_value}
                                                        <option value="{$key|escape:'quotes'}" {if $feature_value.mapping == $key}selected{/if}>{$valid_value|escape:'htmlall':'UTF-8'}</option>
                                                    {/foreach}
                                                        </select>
                                                    {else}
                                                        <input type="text" rel="mapping[features][free][{$id_lang|intval}][{$mapping_key|escape:'htmlall':'UTF-8'}][{$id_feature|intval}][{$feature_value.id_feature_value|escape:'htmlall':'UTF-8'}]"  class="input-right" value="{$feature_value.mapping|escape:'htmlall':'UTF-8'}" style="width:250px;" />
                                                    {/if}

                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                    {if $mapping_group['mandatory']}
                                        {if $mapping_group.has_valid_values}
                                            <input type="hidden"
                                                   name="mapping[mandatory][features][const][{$id_lang|intval}][{$mapping_key|escape:'htmlall':'UTF-8'}]"
                                                   value="1">
                                        {else}
                                            <input type="hidden"
                                                   name="mapping[mandatory][features][free][{$id_lang|intval}][{$mapping_key|escape:'htmlall':'UTF-8'}]"
                                                   value="1">
                                        {/if}
                                    {/if}


                                    {if isset($mapping_group['match_list'])}
                                        <div class="form-group" style="margin-top:15px;">
                                            <div class="margin-form col-lg-offset-3">
                                                <span style="color:darkgrey">{l s='Those values have been automatically matched' mod='amazon'}
                                                    : </span><span
                                                        style="color:darkgreen">{$mapping_group['match_list']|escape:'htmlall':'UTF-8'}</span>
                                            </div>
                                        </div>
                                    {/if}
                                {/foreach}
                            {/foreach}
                        </div>
                    </div>
                    <!-- amazon-mapping features -->
                    <hr style="width:30%"/>
                {/if}
            {/foreach}
        {/if}


        {if isset($mapping.add_mapping_lang) && is_array($mapping.add_mapping_lang) && count($mapping.add_mapping_lang) && isset($mapping.add_mapping) && is_array($mapping.add_mapping) && count($mapping.add_mapping)}
            <div rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none;">

                <input type="hidden" id="text-add-custom-attributes" value="{l s='Add custom value' mod='amazon'}"/>
                {foreach from=$mapping.add_mapping_lang key=id_lang item=mapping_lang}
                    {if isset($mapping.add_mapping[$id_lang])}
                        <div class="amazon-mapping-section">
                            <div class="form-group">
                                <label class="control-label col-lg-3"
                                       style="color:grey">{l s='Custom Mapping Values for' mod='amazon'}<span
                                            class="expert">{l s='Expert' mod='amazon'}</span></label>

                                <div class="margin-form col-lg-9"><span
                                            style="position:relative;top:+2px;font-size:1em;">
                            <img src="{$mapping_lang.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$mapping_lang.name|escape:'htmlall':'UTF-8'}"/> <b
                                                style="position:relative;top:+2px">{$mapping_lang.name|escape:'htmlall':'UTF-8'}</b>
                        </span>&nbsp;&nbsp;
                        <span class="mapping-collapse">
                            <a href="javascript:void(0)" rel="{$mapping_lang.iso_code|escape:'htmlall':'UTF-8'}"
                               {if $mapping_lang.attr_collapsed}style="display:none;"{/if} >[ +
                                ]&nbsp;&nbsp;{l s='Show' mod='amazon'}</a>
                            <a href="javascript:void(0)" rel="{$mapping_lang.iso_code|escape:'htmlall':'UTF-8'}"
                               {if !$mapping_lang.attr_collapsed}style="display:none;"{/if} >[ -
                                ]&nbsp;&nbsp;{l s='Hide' mod='amazon'}</a>
                        </span>
                                </div>
                            </div>


                            <div class="amazon-mapping mapping-group"
                                 {if !$mapping_lang.attr_collapsed}style="display:none;"{/if}>
                                {assign var=mappings value=$mapping.add_mapping[$id_lang]}
                                {foreach from=$mappings key=universe item=mapping_group}
                                    <label class="control-label col-lg-3"
                                           style="color:grey">{$universe|escape:'quotes':'UTF-8'}</label>
                                    <div class="form-group">
                                        {foreach from=$mapping_group key=attribute_key item=items}

                                            {foreach from=$items item=amazon_attribute}
                                                <div class="col-lg-9 col-lg-offset-3 margin-form mapping-tag-group">
                                                    <span class="custom-valid-value-title">{$amazon_attribute|escape:'quotes':'UTF-8'}</span>
                                                    <input type="text" class="tagify"
                                                           id="tag-{$universe|escape:'quotes':'UTF-8'}-{$amazon_attribute|escape:'quotes':'UTF-8'}-{$mapping_lang.region|escape:'quotes':'UTF-8'}"
                                                           name="custom_mapping[{$universe|escape:'quotes':'UTF-8'}][{$amazon_attribute|escape:'quotes':'UTF-8'}][{$mapping_lang.region|escape:'quotes':'UTF-8'}]"
                                                           {if isset($mapping.add_mapping_values[$id_lang][$universe][$amazon_attribute])}value="{$mapping.add_mapping_values[$id_lang][$universe][$amazon_attribute]|escape:'quotes':'UTF-8'}"{/if}/>
                                                </div>
                                            {/foreach}
                                        {/foreach}
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    {/if}
                {/foreach}
                <div class="margin-form col-lg-9 col-lg-offset-3">
                    <p>
                        {l s='Additionnal mappings, this can be used to add missing valid values' mod='amazon'}<br/>
                        {l s='your mappings will never be deleted even in case of update of valid values table' mod='amazon'}
                        <br/>
                        <br/>
                    </p>
                </div>
                <br/>
                <hr class="amz-separator" style="width:30%"/>

            </div>
        {/if}

    {/if}


    {$mapping.validation|escape:'quotes':'UTF-8'}

</div>