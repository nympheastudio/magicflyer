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
<div class="amazon-sub-tab {if !$hidden}main{/if}"
     rel="{$id_lang|escape:'quotes':'UTF-8'}-{$complex_id|escape:'quotes':'UTF-8'}"
     {if $hidden}style="display:none"{/if}>

    <input type="hidden" name="amazon-options-create" value="{$data.create|escape:'quotes':'UTF-8'}"/>

    <div class="section">
        <div class="amazon-tab-product-title">{$data.name|escape:'quotes':'UTF-8'}</div>
    </div>


    <div class="section">
        <h4>{l s='Data' mod='amazon'}</h4>

        <div>
            <table class="amazon-datas">
                {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/bullet_points.tpl" data=$data['bullet_points']}

                {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/extra_text.tpl" data=$data['extra_text']}

                {if $product_tab.alternative_content}
                    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/alternative_title.tpl" data=$data['alternative_title']}
                    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/alternative_description.tpl" data=$data['alternative_description']}
                {/if}
            </table>
        </div>
    </div>

    <div class="section">
        <h4>{l s='Options' mod='amazon'}</h4>

        <div>
            <table class="amazon-options">
                {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/asin.tpl" data=$data['asin']}
                {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/extra_price.tpl" data=$data['extra_price']}
                {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/unavailable.tpl" data=$data['unavailable']}
                {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/force_in_stock.tpl" data=$data['force_in_stock']}

                {if isset($data['nopexport'])}
                    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/nopexport.tpl" data=$data['nopexport']}
                {/if}

                {if isset($data['noqexport'])}
                    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/noqexport.tpl" data=$data['noqexport']}
                {/if}

                {if isset($data['fba_option'])}
                    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/fba_option.tpl" data=$data['fba_option']}
                {/if}

                {if isset($data['fba_value'])}
                    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/fba_value.tpl" data=$data['fba_value']}
                {/if}

                {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/latency.tpl" data=$data['latency']}
                {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/gift.tpl" data=$data['gift']}

                {if isset($data['shipping_overrides'])}
                    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/shipping_overrides.tpl" data=$data['shipping_overrides']}
                {/if}

                {if isset($data['shipping_group'])}
                    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/shipping_group.tpl" data=$data['shipping_group']}
                {/if}

                {if isset($data['browsenode'])}
                    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/browsenode.tpl" data=$data['browsenode']}
                {/if}

                {if isset($data['go_amazon'])}
                    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/go_amazon.tpl" data=$data['go_amazon']}
                {/if}
            </table>
        </div>
    </div>

    {if isset($data['repricing'])}
        <div class="section">
            <h4>{l s='Repricing' mod='amazon'}</h4>

            <div>
                <table class="amazon-options">
                    {include file="{$product_tab.module_path|escape:'quotes':'UTF-8'}/views/templates/admin/product_tab/items/repricing.tpl" data=$data['repricing']}
                </table>
            </div>
        </div>
    {/if}
</div>