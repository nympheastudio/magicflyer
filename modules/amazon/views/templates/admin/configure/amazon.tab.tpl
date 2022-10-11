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

<div id="menudiv-amazon" class="tabItem {if $amazon.selected_tab}selected{/if} panel form-horizontal">


    <div class="form-group">
        <label class="col-lg-3">{if $psIs16}
            <h2>{else}<h3>{/if}{$amazon.name|escape:'htmlall':'UTF-8'}
                    v{$amazon.version|escape:'htmlall':'UTF-8'}{if $psIs16}
            </h2>
            {else}</h3>{/if}</label>

        <div class="margin-form col-lg-9">
            <p style="color: navy;">{$amazon.description|escape:'htmlall':'UTF-8'}</p>

            <p style="color: navy;">{l s='The following features are provided with this module :' mod='amazon'}</p>
            <ul>
                <li>{l s='Retrieve Orders from the MarketPlace by Web Service' mod='amazon'}</li>
                <li>{l s='Update Orders Status in the MarketPlace by Web Service' mod='amazon'}</li>
                <li>{l s='Update & Create Products in the MarketPlace' mod='amazon'}</li>
            </ul>
            <hr/>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Informations' mod='amazon'}</label>

        {if $amazon.lang_fr}
            {assign var="author_link" value="https://www.prestashop.com/fr/experts/editeurs-de-modules/common-services"}
            {assign var="amazon_module_link" value="http://addons.prestashop.com/fr/58_common-services"}
        {else}
            {assign var="author_link" value="https://www.prestashop.com/experts/module-creators/common-services"}
            {assign var="amazon_module_link" value="http://addons.prestashop.com/en/58_common-services"}
        {/if}

        <div class="margin-form col-lg-9">
            <span style="color:navy">{l s='This module is provided by' mod='amazon'} :</span> Common-Services<br>
            {if !$amazon.is_lite}
                <br>
                <span style="color:navy">{l s='Informations, follow up on our blog' mod='amazon'} :</span><br>
                <a href="http://www.common-services.com" target="_blank">http://www.common-services.com</a><br>
            {/if}
            <br>
            <span style="color:navy">{l s='More informations about us on Prestashop website' mod='amazon'} :</span><br>
            <a href="{$author_link|escape:'htmlall':'UTF-8'}" target="_blank">{$author_link|escape:'htmlall':'UTF-8'}</a><br>
            <br>
            <span style="color:navy">{l s='You will certainly appreciate our other modules' mod='amazon'} :</span><br>
            <a href="{$amazon_module_link|escape:'htmlall':'UTF-8'}" target="_blank">{$amazon_module_link|escape:'htmlall':'UTF-8'}</a>
        </div>
    </div>

    <br/>
    {if !$amazon.is_lite}
    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Documentation' mod='amazon'}</label>

        <div class="margin-form col-lg-9">
            <div class="col-lg-1"><img src="{$amazon.images|escape:'htmlall':'UTF-8'}books.png" alt="docs"/></div>
            <div class="col-lg-11">
                <span style="color:red; font-weight:bold;">{l s='Please, first read the provided documentation:' mod='amazon'}
                    :</span><br>
                {$amazon.documentation|escape:'quotes':'UTF-8'}<br>
            </div>
        </div>
    </div>

    <br>
    {/if}

    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Support' mod='amazon'}</label>

        <div class="margin-form col-lg-9">
            <div class="col-lg-1"><img src="{$amazon.images|escape:'htmlall':'UTF-8'}submit_support_request.png"
                                       alt="support"></div>
            <div class="col-lg-11">
                <span style="color:navy">
                    {l s='The technical support is available by e-mail only.' mod='amazon'}<br>
                    {l s='For any support, please provide us' mod='amazon'} :<br>
                </span>
                <ul>
                    <li>{l s='A detailed description of the issue or encountered problem' mod='amazon'}</li>
                    <li rel="order_id">
                        <span>
                        {l s='Your Pretashop Addons Order ID available in your Prestashop Addons order history' mod='amazon'}
                        </span>
                    </li>
                    <li>{l s='Your Prestashop version' mod='amazon'} : <span
                                style="color: red;">Prestashop {$amazon.ps_version|escape:'htmlall':'UTF-8'}</span></li>
                    <li>{l s='Your module version' mod='amazon'} : <span
                                style="color: red;">Amazon v{$amazon.version|escape:'htmlall':'UTF-8'}</span></li>
                </ul>
                {if !$amazon.is_lite}
                <br>
                <span style="color:navy">{l s='Support Common-Services' mod='amazon'} :</span> <a
                        href="mailto:support.amazon@common-services.com?subject={$amazon.support_info.subject|escape:'htmlall':'UTF-8'}&body={$amazon.support_info.body|escape:'htmlall':'UTF-8'}"
                        title="Email">support.amazon@common-services.com</a><br>
                <br>
                {/if}
            </div>
            <hr style="clear: both;">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3">{l s='Licence' mod='amazon'}</label>

        <div class="margin-form col-lg-9">
            <p style="font-weight: bold;">
                {l s='This module is subject to a commercial license from SARL SMC.' mod='amazon'}<br>
                {l s='To obtain a license, please contact us: support.amazon@common-services.com' mod='amazon'}<br>
                {l s='In case of acquisition on Prestastore, the invoice itself is a proof of license' mod='amazon'}
            </p>
        </div>
    </div>

</div>