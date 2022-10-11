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
<div id="menudiv-filters" class="tabItem {if $filters.selected_tab}selected{/if} panel form-horizontal">
    <h3>{l s='Filters' mod='amazon'}</h3>

    {if !$amazon.is_lite}
    <div class="form-group">
        <div class="margin-form">
            <div class="amz-info-level-info {if $psIs16}alert alert-info col-lg-offset-3{/if}" style="font-size:1.1em">
                <ul>
                    <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                    <li>{$filters.tutorial|escape:'quotes':'UTF-8'}</li>
                </ul>
            </div>
        </div>
    </div>
    <br/>
    {/if}

    <div class="form-group">
        <label class="control-label col-lg-3" style="color:grey">{l s='Price Filters' mod='amazon'}</label>

        <div class="margin-form col-lg-9">
            &nbsp;
        </div>
        <div class="cleaner"></div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" rel="biggerthan"><span>{l s='Greater than' mod='amazon'}</span><span
                    style="font-weight:bold"> > </span></label>

        <div class="margin-form col-lg-9">
            <input type="text" name="price_filter[gt]" class="price-filter-value" style="width:100px; display: inline;"
                   value="{$filters.prices.gt|escape:'htmlall':'UTF-8'}"/>&nbsp;<span
                    style="font-size:1.2em;color:navy;"> {$filters.prices.currency_sign|escape:'htmlall':'UTF-8'} </span>
        </div>
    </div>
    <div class="form-group">
        <hr style="width:30%"/>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" rel="lessthan"><span>{l s='Less than' mod='amazon'}</span><span
                    style="font-weight:bold"> < </span></label>

        <div class="margin-form col-lg-9">
            <input type="text" name="price_filter[lt]" class="price-filter-value" style="width:100px; display: inline;"
                   value="{$filters.prices.lt|escape:'htmlall':'UTF-8'}"/>&nbsp;<span
                    style="font-size:1.2em;color:navy;"> {$filters.prices.currency_sign|escape:'htmlall':'UTF-8'} </span>
        </div>
    </div>
    <div class="form-group">
        <hr style="width:30%"/>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" style="color:grey">{l s='Manufacturers Filters' mod='amazon'}</label>

        <div class="margin-form col-lg-9">
            <div class="manufacturer-heading">
                <span><img src="{$filters.images_url|escape:'quotes':'UTF-8'}cross.png"
                           alt="{l s='Excluded' mod='amazon'}"/></span>
                <span><img src="{$filters.images_url|escape:'quotes':'UTF-8'}checked.png"
                           alt="{l s='Included' mod='amazon'}"/></span>
            </div>
            <select name="selected-manufacturers[]" class="selected-manufacturers" id="selected-manufacturers"
                    multiple="multiple">
                <option value="0" disabled style="color:orange;">{l s='Excluded Manufacturers' mod='amazon'}</option>
                {foreach from=$filters.manufacturers.filtered key=id_manufacturer item=name}
                    <option value="{$id_manufacturer|escape:'htmlall':'UTF-8'}">{$name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>

            <div class="sep">
                <img src="{$filters.images_url|escape:'quotes':'UTF-8'}arrow_left.png" class="move"
                     id="manufacturer-move-left"
                     alt="Left"/><br/><br/>
                <img src="{$filters.images_url|escape:'quotes':'UTF-8'}arrow_right.png" class="move"
                     id="manufacturer-move-right" alt="Right"/>
            </div>
            <select name="available-manufacturers[]" class="available-manufacturers" id="available-manufacturers"
                    multiple="multiple">

                <option value="0" disabled style="color:green;">{l s='Included Manufacturers' mod='amazon'}</option>
                {foreach from=$filters.manufacturers.available key=id_manufacturer item=name}
                    <option value="{$id_manufacturer|escape:'htmlall':'UTF-8'}">{$name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <hr style="width:30%"/>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" style="color:grey">{l s='Filter Suppliers' mod='amazon'}</label>

        <div class="margin-form col-lg-9">
            <div class="supplier-heading">
                <span><img src="{$filters.images_url|escape:'quotes':'UTF-8'}cross.png"
                           alt="{l s='Excluded' mod='amazon'}"/></span>
                <span><img src="{$filters.images_url|escape:'quotes':'UTF-8'}checked.png"
                           alt="{l s='Included' mod='amazon'}"/></span>
            </div>
            <select name="selected-suppliers[]" class="selected-suppliers" id="selected-suppliers" multiple="multiple">
                <option value="0" disabled style="color:orange;">{l s='Excluded Suppliers' mod='amazon'}</option>
                {foreach from=$filters.suppliers.filtered key=id_supplier item=name}
                    <option value="{$id_supplier|escape:'htmlall':'UTF-8'}">{$name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>

            <div class="sep">
                <img src="{$filters.images_url|escape:'quotes':'UTF-8'}arrow_left.png" class="move"
                     id="supplier-move-left"
                     alt="Left"/><br/><br/>
                <img src="{$filters.images_url|escape:'quotes':'UTF-8'}arrow_right.png" class="move"
                     id="supplier-move-right" alt="Right"/>
            </div>
            <select name="available-suppliers[]" class="available-suppliers" id="available-suppliers"
                    multiple="multiple">
                <option value="0" disabled style="color:green;">{l s='Included Suppliers' mod='amazon'}</option>
                {foreach from=$filters.suppliers.available key=id_supplier item=name}
                    <option value="{$id_supplier|escape:'htmlall':'UTF-8'}">{$name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <hr style="width:30%"/>
        </div>
    </div>


    <!-- validation button -->
    {$filters.validation|escape:'quotes':'UTF-8'}

</div><!-- menudiv-filters -->