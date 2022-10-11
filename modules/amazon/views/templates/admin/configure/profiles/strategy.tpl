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
<div class="repricing-strategie{if $strategy.master} master{/if}" {if $strategy.master}style="display:none"{/if}>
    <div class="strategie-display-header" {if $strategy.master}style="display:none"{/if}>
        <div class="margin-form col-lg-12">
            <input type="hidden"
                   rel="strategies[name][{$marketplace.id_lang|intval}][{$strategy.key|escape:'html':'UTF-8'}]"
                   value=""/>
            <table>
                <tr>
                    <td rel="name"><b>{$strategy.name|escape:'htmlall':'UTF-8'}</b></td>
                    <td align="center" width="50px" class="strategy-delete">
                        <img src="{$repricing.images_url|escape:'quotes':'UTF-8'}cross.png"
                             alt="{l s='Delete' mod='amazon'}"/>
                    </td>
                    <td align="center" width="50px" class="strategy-edit">
                        <img src="{$repricing.images_url|escape:'quotes':'UTF-8'}edit.png"
                             alt="{l s='Edit' mod='amazon'}"/>
                    </td>
                </tr>
            </table>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="strategie-create-header" style="display:none">
        <label class="control-label col-lg-3" rel="repricing_name"
               style="color:grey;"><span>{l s='Name' mod='amazon'}</span></label>

        <div class="margin-form col-lg-9">
            <input type="text"
                   rel="strategies[name][{$marketplace.id_lang|intval}][{$strategy.key|escape:'html':'UTF-8'}]"
                   {if !$strategy.master|escape:'html':'UTF-8'}readonly{/if}
                   value="{$strategy.name|escape:'html':'UTF-8'}"/>
            <span class="strategy-minimize">
                        <img src="{$repricing.images_url|escape:'quotes':'UTF-8'}minimize.png"
                             alt="{l s='Minimize' mod='amazon'}"/>
            </span>
        </div>
        <div class="clearfix"></div>
    </div>

    <div class="strategie-body" style="display:none">

        <div class="strategie-active">
            <label class="control-label col-lg-3" rel="repricing_active"
                   style="color:grey;"><span>{l s='Active' mod='amazon'}</span></label>

            <div class="margin-form col-lg-9">
                <span class="switch prestashop-switch fixed-width-lg">
                    {assign var=marketplace_id value=$marketplace.id_lang|intval}
                    {assign var=active1_id value=$active1_id|intval}
                    {assign var=active2_id value=$active2_id|intval}
                    {*Notice: All inputs here are radio, not sure it's right*}
                    <input type="radio" class="strategy-active-input-yes"
                           name="strategies[active][{$marketplace_id|escape:'html':'UTF-8'}][{$strategy.key|escape:'html':'UTF-8'}]"
                           id="strategie-active-{$marketplace_id|escape:'html':'UTF-8'}-{$active1_id|escape:'html':'UTF-8'}" value="1" data-id-counter="{$active1_id|escape:'html':'UTF-8'}"
                           {if $strategy.active}checked="checked"{/if} />
                    <label for="strategie-active-{$marketplace_id|escape:'html':'UTF-8'}-{$active1_id|escape:'html':'UTF-8'}" class="label-checkbox strategy-active-label-yes">
                        {l s='Yes' mod='amazon'}
                    </label>
                    <input type="radio" class="strategy-active-input-no"
                           name="strategies[active][{$marketplace_id|escape:'html':'UTF-8'}][{$strategy.key|escape:'html':'UTF-8'}]"
                           id="strategie-active-{$marketplace_id|escape:'html':'UTF-8'}-{$active2_id|escape:'html':'UTF-8'}" value="0" data-id-counter="{$active2_id|escape:'html':'UTF-8'}"
                           {if (!$strategy.active)}checked="checked"{/if} />
                    <label for="strategie-active-{$marketplace_id|escape:'html':'UTF-8'}-{$active2_id|escape:'html':'UTF-8'}" class="label-checkbox strategy-active-label-no">
                        {l s='No' mod='amazon'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
            <div class="clearfix"></div>
        </div>


        <div class="strategie-agressivity">
            <label class="control-label col-lg-3" rel="repricing_agressivity"
                   style="color:grey;"><span>{l s='Aggressivity' mod='amazon'}</span></label>

            <div class="margin-form col-lg-9">
                <select rel="strategies[agressivity][{$marketplace.id_lang|intval}][{$strategy.key|escape:'html':'UTF-8'}]">
                    <option></option>
                    {section name=strategy_agressivity loop=10}
                        <option value="{$smarty.section.strategy_agressivity.iteration|escape:'htmlall':'UTF-8'}"
                                {if $strategy.agressivity == $smarty.section.strategy_agressivity.iteration}selected{/if}>{$smarty.section.strategy_agressivity.iteration|escape:'htmlall':'UTF-8'}</option>
                    {/section}
                </select>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="strategie-base">
            <label class="control-label col-lg-3" rel="repricing_base"
                   style="color:grey;"><span>{l s='Base' mod='amazon'}</span></label>

            <div class="margin-form col-lg-9">
                <select rel="strategies[base][{$marketplace.id_lang|intval}][{$strategy.key|escape:'html':'UTF-8'}]">
                    <option></option>
                    <option value="1"
                            {if $strategy.base == $repricing.method.wholesale}selected{/if}>{l s='Wholesale Price' mod='amazon'}</option>
                    <option value="2"
                            {if $strategy.base == $repricing.method.regular}selected{/if}>{l s='Regular Price' mod='amazon'}</option>
                </select>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="strategie-limit">
            <label class="control-label col-lg-3" rel="repricing_limit"
                   style="color:grey;"><span>{l s='Limit' mod='amazon'}</span></label>

            <div class="margin-form col-lg-9">
                <select rel="strategies[limit][{$marketplace.id_lang|intval}][{$strategy.key|escape:'html':'UTF-8'}]">
                    <option></option>
                    {section name=strategy_limit loop=100}
                        <option value="-{$smarty.section.strategy_limit.iteration|escape:'htmlall':'UTF-8'}"
                                {if $strategy.limit == ($smarty.section.strategy_limit.iteration * -1)}selected{/if}>
                            - {$smarty.section.strategy_limit.iteration|escape:'htmlall':'UTF-8'}&#37;</option>
                    {/section}
                    {section name=strategy_limit loop=200}
                        <option {if $smarty.section.strategy_limit.iteration > 100}rel="amazon-expert-mode" class="amazon-expert-mode"{/if}
                                value="{$smarty.section.strategy_limit.iteration|escape:'htmlall':'UTF-8'}" {if $strategy.limit == $smarty.section.strategy_limit.iteration}selected{/if}>
                            + {$smarty.section.strategy_limit.iteration|escape:'htmlall':'UTF-8'}&#37;</option>
                    {/section}
                </select><span>{l s='(on base)' mod='amazon'}</span>
            </div>
            <div class="clearfix"></div>
        </div>


        <div class="strategie-delta">
            <label class="control-label col-lg-3" rel="repricing_delta"
                   style="color:grey;"><span>{l s='Delta' mod='amazon'}</span></label>

            <div class="margin-form col-lg-9">
                <select rel="strategies[delta_min][{$marketplace.id_lang|intval}][{$strategy.key|escape:'html':'UTF-8'}]">
                    <option></option>
                    {section name=strategy_delta_min loop=100}
                        <option value="-{$smarty.section.strategy_delta_min.iteration|escape:'htmlall':'UTF-8'}"
                                {if $strategy.delta_min == ($smarty.section.strategy_delta_min.iteration * -1)}selected{/if}>
                            - {$smarty.section.strategy_delta_min.iteration|escape:'htmlall':'UTF-8'}&#37;</option>
                    {/section}
                    {section name=strategy_delta_min loop=200}
                        <option {if $smarty.section.strategy_delta_min.iteration > 100}rel="amazon-expert-mode" class="amazon-expert-mode"{/if}
                                value="{$smarty.section.strategy_delta_min.iteration|escape:'htmlall':'UTF-8'}" {if $strategy.delta_min == $smarty.section.strategy_delta_min.iteration}selected{/if}>
                            + {$smarty.section.strategy_delta_min.iteration|escape:'htmlall':'UTF-8'}&#37;</option>
                    {/section}
                </select>

                <span> - </span>
                <select rel="strategies[delta_max][{$marketplace.id_lang|intval}][{$strategy.key|escape:'html':'UTF-8'}]">
                    <option></option>
                    {section name=strategy_delta_max loop=100}
                        <option value="-{$smarty.section.strategy_delta_max.iteration|escape:'htmlall':'UTF-8'}"
                                {if $strategy.delta_max == ($smarty.section.strategy_delta_max.iteration * -1)}selected{/if}>
                            - {$smarty.section.strategy_delta_max.iteration|escape:'htmlall':'UTF-8'}&#37;</option>
                    {/section}
                    {section name=strategy_delta_max loop=200}
                        <option {if $smarty.section.strategy_delta_max.iteration > 100}rel="amazon-expert-mode" class="amazon-expert-mode"{/if}
                                value="{$smarty.section.strategy_delta_max.iteration|escape:'htmlall':'UTF-8'}" {if $strategy.delta_max == $smarty.section.strategy_delta_max.iteration}selected{/if}>
                            + {$smarty.section.strategy_delta_max.iteration|escape:'htmlall':'UTF-8'}&#37;</option>
                    {/section}
                </select><span>{l s='(on regular price)' mod='amazon'}</span>
            </div>
            <div class="clearfix"></div>
        </div>

    </div>

</div>