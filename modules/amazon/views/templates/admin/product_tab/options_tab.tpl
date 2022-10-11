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
<div class="amazon-tab amazon-tab-{$marketplace.id_lang|escape:'quotes':'UTF-8'}"
     rel="{$marketplace.iso_code|escape:'quotes':'UTF-8'}" {if !$marketplace.default}style="display:none"{/if}
     class="col-lg-12">
    <span id="amazon-action-loader" style="display:none"><img
                src="{$product_tab.images|escape:'quotes':'UTF-8'}/green-loader.gif"/></span>
    {if $product_tab.show_countries}
        <h4 class="marketplace-heading"><img src="{$marketplace.image|escape:'quotes':'UTF-8'}"
                                             alt="{$marketplace.name_long|escape:'quotes':'UTF-8'}"/>{$marketplace.name_long|escape:'quotes':'UTF-8'}
        </h4>
        <div class="clearfix"></div>
    {/if}

    <div class="section">
        <h4>{l s='Action' mod='amazon'}</h4>

        <div>
            <table class="amazon-actions">
                {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/action.tpl" data=$data.action[$marketplace.id_lang]}
            </table>
        </div>
    </div>

    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/options_details.tpl" data=$data.options[$marketplace.id_lang] id_lang=$marketplace.id_lang complex_id=$product.complex_id hidden=false}

    {if isset($data.combinations_options) && is_array($data.combinations_options) && count($data.combinations_options) && isset($data.combinations_options[$marketplace.id_lang]) && is_array($data.combinations_options[$marketplace.id_lang]) && count($data.combinations_options[$marketplace.id_lang])}
        {foreach from=$data.combinations_options[$marketplace.id_lang] key=complex_id item=combination_option}
            {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/options_details.tpl" data=$combination_option id_lang=$marketplace.id_lang hidden=true}
        {/foreach}
    {/if}

</div>