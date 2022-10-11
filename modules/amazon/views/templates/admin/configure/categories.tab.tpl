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
<div id="menudiv-categories" class="tabItem {if $categories.selected_tab}selected active{/if} panel form-horizontal">
    <h3>{l s='Categories' mod='amazon'}</h3>

    {if !$amazon.is_lite}
    <div class="form-group">
        <div class="margin-form">
            <div class="amz-info-level-info {if $psIs16}alert alert-info col-lg-offset-3{/if}" style="font-size:1.1em">
                <ul>
                    <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                    <li>{$categories.tutorial|escape:'quotes':'UTF-8'}</li>
                </ul>
            </div>
        </div>
    </div>
    {/if}

    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Categories Settings' mod='amazon'}</label>

        <div class="margin-form col-lg-9">
            <table cellspacing="0" cellpadding="0" class="table">
                <tr class="active">
                    <th style="width:30px"></th>
                    <th>{l s='Name' mod='amazon'}</th>
                    <th style="width: 235px"><span class="amazon-products-creation" rel="amazon-products-creation">{l s='Profile' mod='amazon'}</span></th>
                </tr>
                {if isset($categories) && is_array($categories.list) && count($categories.list)}
                    {foreach $categories.list as $id_category => $details}
                        <tr class="cat-line{($details.alt_row|intval) ? ' alt_row' : ''}">
                            <td>
                                {if !$details.disabled}
                                    <input type="checkbox" rel="category[]" class="category{($details.id_category_default|intval == $id_category|intval) ? ' id_category_default' : ''}" id="category_{$id_category|intval}" value="{$id_category|intval}" {$details.checked|escape:'htmlall':'UTF-8'}/>
                                {/if}
                            </td>
                            <td style="cursor:pointer">
                                <img src="{$details.img_level|escape:'htmlall':'UTF-8'}" alt="" /> &nbsp;<label for="category_{$id_category|intval}" class="t">{$details.name|escape:'htmlall':'UTF-8'}</label>
                            </td>
                            <td>
                                {if !$details.disabled && is_array($categories.profiles) && count($categories.profiles)}
                                <span class="amazon-products-creation" rel="amazon-products-creation" style="display:none">
                                    {if (isset($categories.profiles.name) && is_array($categories.profiles.name) && count($categories.profiles.name))}
                                    <select rel="profile2category[{$id_category|intval}]" style="width:180px;margin-right:10px;">
                                        <option value="">{l s='Please choose a profile' mod='amazon'}</option>
                                        {foreach $categories.profiles.name as $profile}
                                            <option value="{$profile|escape:'htmlall':'UTF-8'}" {if $profile == $details.profile}selected="selected"{/if}>{$profile|escape:'htmlall':'UTF-8'}</option>
                                        {/foreach}
                                    </select>
                                    {/if}
                                    &nbsp;<span class="arrow-cat-duplicate"></span>
                                {/if}
                                </span>
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="3">
                            {l s='No category were found.' mod='amazon'}
                        </td>
                    </tr>
                {/if}
                {* !Ajout debuss-a *}
            </table>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3"></label>

        <div class="margin-form col-lg-9">
            <p>
                {l s='Tip: to select multiple categories, thick the first checkbox, press on Shift key then select the last one.' mod='amazon'}<br/>
            </p>
        </div>
    </div>
    <hr class="amz-separator"/>

    {if $categories.expert_mode}
        <div rel="amazon-expert">
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Brute Force' mod='amazon'}</label>

                <div class="margin-form col-lg-9">
                    <input type="checkbox" name="brute_force" value="1" {if $categories.brute_force}checked{/if} /><span
                            style="font-size:1.2em;color:black;position:relative;top:+2px;">&nbsp;&nbsp;{l s='Activates forcing updates' mod='amazon'}</span>

                    <p>
                        {l s='Force product update even it was not modified' mod='amazon'}<br/>
                        <span style="color:red;font-weight:bold;">{l s='This feature is not recommended !' mod='amazon'}</span>
                    </p>
                </div>
            </div>
        </div>
    {else}
        <input type="hidden" name="brute_force" value="0"/>
    {/if}


    {$categories.validation|escape:'quotes':'UTF-8'}
</div>