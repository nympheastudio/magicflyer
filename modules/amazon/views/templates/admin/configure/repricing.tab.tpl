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
<div id="menudiv-repricing" class="tabItem {if $repricing.selected_tab}selected{/if} panel form-horizontal">
    <h3>{l s='Repricing' mod='amazon'}</h3>

    <input type="hidden" id="amazon_repricing_url" value="{$repricing.repricing_url|escape:'quotes':'UTF-8'}"/>
    <input type="hidden" id="repricing_ajax_error"
           value="{l s='An unexpected server side error occurs, please verify your module configuration first.' mod='amazon'}"/>

    {if !$amazon.is_lite}
    <div class="margin-form">
        <div class="amz-info-level-info {if $psIs16}alert alert-info col-lg-offset-3{/if}" style="font-size:1.1em">
            <ul>
                <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                <li>{$repricing.tutorial|escape:'quotes':'UTF-8'}</li>
            </ul>
        </div>
    </div>
    {/if}

    <div class="form-group">
        <label class="control-label col-lg-3" rel="repricing_settings"
               style="color:grey;"><span>{l s='API Settings' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3"
               rel="repricing_awskeyid"><span>{l s='AWS Key Id' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9">
            <input type="text" name="repricing[awsKeyId]" style="width:300px; display: inline;"
                   value="{$repricing.awsKeyId|escape:'htmlall':'UTF-8'}"/>
            {if $repricing.awsKeyId_required}<span class="mandatory">{l s='Required' mod='amazon'}</span>{/if}
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" rel="repricing_secretkey"><span>{l s='AWS Secret Key' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9">
            <input type="password" name="repricing[awsSecretKey]" style="width:380px; display: inline;"
                   value="{$repricing.awsSecretKey|escape:'htmlall':'UTF-8'}"/>
            {if $repricing.awsSecretKey_required}<span class="mandatory">{l s='Required' mod='amazon'}</span>{/if}
        </div>
    </div>

    <label class="control-label col-lg-3">{l s='API Check' mod='amazon'}</label>

    <div class="form-group">
        <div align="left" class="margin-form col-lg-offset-3">
            <input type="button" class="button btn" id="repricing-aws-check"
                   value="{l s='Check Connectivity' mod='amazon'}" style="width:280px;"/>
            <img src="{$settings.images_url|escape:'quotes':'UTF-8'}loader-connection.gif"
                 alt="{l s='Check Connectivity' mod='amazon'}" id="repricing-aws-check-loader" style="display:none"/>
        </div>
    </div>

    <div class="form-group">
        <div align="left" class="margin-form col-lg-offset-3">
            <div id="repricing-aws-check-success" class="{$class_success|escape:'htmlall':'UTF-8'}"
                 style="display:none">
            </div>

            <div id="repricing-aws-check-warning" class="{$class_warning|escape:'htmlall':'UTF-8'}"
                 style="display:none">
            </div>

            <div id="repricing-aws-check-error" class="{$class_error|escape:'htmlall':'UTF-8'}" style="display:none">
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="margin-form col-lg-12">
            <hr class="amz-separator" style="width:30%;"/>
        </div>
    </div>

    <!-- Marketplace Selector -->
    {if $repricing.marketplaces.show}
        <div class="form-group" style="margin-bottom:0">
            <div class="margin-form col-lg-9 col-lg-offset-3">
                <table class="country-selector">
                    <tr>
                        {foreach from=$repricing.marketplaces.countries item=marketplace}
                            <td>
                            <span class="amazon-tab-selector{if $marketplace.default} active{/if}" rel="{$marketplace.region|escape:'quotes':'UTF-8'}">
                                <img src="{$marketplace.image|escape:'quotes':'UTF-8'}" title="{$marketplace.name_long|escape:'quotes':'UTF-8'}"/><span class="name">{$marketplace.name_short|escape:'quotes':'UTF-8'}</span>
                                <input type="hidden" rel="id_lang"
                               value="{$marketplace.id_lang|escape:'quotes':'UTF-8'}"/>
                            </span>
                            </td>
                        {/foreach}
                    </tr>
                </table>

                <div class="col-lg-9 country-separator">
                    <div class="amazon-tab-bar"></div>
                </div>
            </div>

        </div>
    {else}
        {foreach from=$repricing.marketplaces.countries item=marketplace}
            <span style="display:none;" class="amazon-tab-selector active"
                  rel="{$marketplace.iso_code|escape:'quotes':'UTF-8'}">
                <input type="hidden" rel="id_lang" value="{$marketplace.id_lang|escape:'quotes':'UTF-8'}"/>
            </span>
        {/foreach}
    {/if}

    {foreach from=$repricing.marketplaces.countries item=marketplace}
        <div class="amazon-tab amazon-tab-{$marketplace.id_lang|escape:'quotes':'UTF-8'}" rel="{$marketplace.region|escape:'quotes':'UTF-8'}" {if !$marketplace.default}style="display:none"{/if}>

            <div class="form-group">
                <div class="margin-form col-lg-9">
                    <div class="current-country">
                        <img src="{$marketplace.image|escape:'quotes':'UTF-8'}" title="{$marketplace.name_long|escape:'quotes':'UTF-8'}"/><span class="name">{$marketplace.name_short|escape:'quotes':'UTF-8'}</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3" rel="repricing_service"
                       style="color:grey;"><span>{l s='Service Settings' mod='amazon'}</span></label>

                <div class="margin-form col-lg-9">
                </div>
            </div>

            <label class="control-label col-lg-3" rel="repricing_subscribe"><span>{l s='Subscribe' mod='amazon'}</span></label>

            <div class="form-group">
                <div align="left" class="margin-form col-lg-offset-3">
                    <input type="button" class="button btn repricing-service-cancel"
                           id="repricing-service-cancel-{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                           rel="{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                           value="{l s='Cancel Subscription' mod='amazon'}"/>
                    <input type="button" class="button btn repricing-service-check"
                           id="repricing-service-check-{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                           rel="{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                           value="{l s='Check/Subscribe Service' mod='amazon'}"/>
                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}loader-connection.gif"
                         class="repricing-service-check-loader" style="display:none"/>
                </div>
            </div>

            <div class="form-group">
                <div align="left" class="margin-form col-lg-offset-3">
                    <div class="{$class_success|escape:'htmlall':'UTF-8'} repricing-service-check-success"
                         style="display:none">
                    </div>

                    <div class="{$class_warning|escape:'htmlall':'UTF-8'} repricing-service-check-warning"
                         style="display:none">
                    </div>

                    <div class="{$class_error|escape:'htmlall':'UTF-8'} repricing-service-check-error"
                         style="display:none">
                    </div>
                </div>
            </div>

            <label class="control-label col-lg-3"
                   rel="repricing_maintenance"><span>{l s='Maintenance' mod='amazon'}</span></label>

            <div class="form-group">
                <div align="left" class="margin-form col-lg-offset-3">
                    <input type="button" class="button btn repricing-queue-check"
                           id="repricing-queue-check-{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                           rel="{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                           value="{l s='List Queues' mod='amazon'}"/>
                    <img src="{$settings.images_url|escape:'quotes':'UTF-8'}loader-connection.gif"
                         class="repricing-queue-check-loader" style="display:none"/>
                </div>
            </div>

            <div class="form-group">
                <div align="left" class="margin-form col-lg-offset-3">
                    <div class="{$class_success|escape:'htmlall':'UTF-8'} repricing-queue-check-success"
                         style="display:none">
                    </div>

                    <div class="{$class_warning|escape:'htmlall':'UTF-8'} repricing-queue-check-warning"
                         style="display:none">
                    </div>

                    <div class="{$class_error|escape:'htmlall':'UTF-8'} repricing-queue-check-error"
                         style="display:none">
                    </div>

                    <div class="repricing-check-queue-result" style="display:none">
                    </div>
                </div>
            </div>

            <div class="purge-queue-section" style="display:none;">
                <div class="form-group">
                    <div align="left" class="margin-form col-lg-offset-3">
                        <input type="button" class="button btn repricing-queue-purge"
                               id="repricing-queue-purge-{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                               rel="{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
                               value="{l s='Purge Selected Queues' mod='amazon'}"/>
                        <img src="{$settings.images_url|escape:'quotes':'UTF-8'}loader-connection.gif"
                             class="repricing-queue-purge-loader" style="display:none"/>
                    </div>
                </div>

                <div class="form-group">
                    <div align="left" class="margin-form col-lg-offset-3">
                        <div class="{$class_success|escape:'htmlall':'UTF-8'} repricing-queue-purge-success"
                             style="display:none">
                        </div>

                        <div class="{$class_warning|escape:'htmlall':'UTF-8'} repricing-queue-purge-warning"
                             style="display:none">
                        </div>

                        <div class="{$class_error|escape:'htmlall':'UTF-8'} repricing-queue-purge-error"
                             style="display:none">
                        </div>
                    </div>
                </div>
            </div>



            {if isset($repricing.strategies.strategy[$marketplace.id_lang]) && is_array($repricing.strategies.strategy[$marketplace.id_lang])}
                <div class="form-group">
                    <label class="control-label col-lg-3" rel="default_strategy" style="color:grey;"><span>{l s='Default Strategy' mod='amazon'}</span></label>
                    <div class="margin-form col-lg-9">
                        <select name="default_strategy[{$marketplace.id_lang|intval}]" style="width:300px">
                            {foreach from=$repricing.strategies.strategy[$marketplace.id_lang] item=strategy}
                                <option value=""></option>
                                <option value="{$strategy.key|escape:'htmlall':'UTF-8'}" {if $strategy.default}selected{/if}>{$strategy.name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            {/if}
            <div class="form-group">
                <div class="margin-form col-lg-12">
                    <hr class="amz-separator" style="width:30%;"/>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3" rel="repricing_strategies"
                       style="color:grey;"><span>{l s='Repricing Strategies' mod='amazon'}</span></label>

                <div class="margin-form col-lg-9">
                    <div class="strategies-add">
                        <span class="strategy-add-img"><img
                                    src="{$repricing.images_url|escape:'quotes':'UTF-8'}add.png"/></span>
                        <span class="strategy-add-title">{l s='Add a new repricing strategy' mod='amazon'}</span><br>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">&nbsp;</label>

                <div class="margin-form col-lg-9">
                    <div class="repricing-strategies">
                        {assign var=active1_id value=100}
                        {assign var=active2_id value=200}

                        {if isset($repricing.strategies.strategy[$marketplace.id_lang]) && is_array($repricing.strategies.strategy[$marketplace.id_lang])}
                            {foreach from=$repricing.strategies.strategy[$marketplace.id_lang] item=strategy}
                                {assign var=active1_id value=$active1_id+1}
                                {assign var=active2_id value=$active2_id+1}
                                {include file="{$repricing.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/configure/profiles/strategy.tpl"}
                            {/foreach}
                        {/if}

                        {assign var=active1_id value=$active1_id+1}
                        {assign var=active2_id value=$active2_id+1}
                        {include file="{$repricing.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/configure/profiles/strategy.tpl" strategy=$repricing.strategies.empty}
                    </div>
                </div>
            </div>


        </div>


    {/foreach}



    <!-- validation button -->
    {$fba.validation|escape:'quotes':'UTF-8'}


</div><!-- menudiv-fba -->