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
<div class="hint clear alert alert-info">
    {l s='Important Tips' mod='amazon'}<br/>
    <ul>
        <li>{l s='Offers: Catalog synchronization - Send your Offers Feed, quantity, price and optionnal fields.' mod='amazon'}</li>
        <li>{l s='Products: Product Creation - Send your Products Feed, title, descriptions, product datas.' mod='amazon'}</li>
        <li>{l s='Report is mandatory for any support.' mod='amazon'}</li>
        <li id="support-images-exception" style="display:none">{l s='Images Exception' mod='amazon'}
            : {$about_images|escape:'quotes':'UTF-8'}</li>
    </ul>
</div>

<ul id="menuTab" class="nav{if $ps17x} ps17{/if}">
    <li id="menu-informations" class="menuTabButton {if $selected_tab eq 'informations'}selected active{/if}"><a
                href="#"><span>&nbsp;<img src="{$images|escape:'quotes':'UTF-8'}information-big.png"
                                          alt="{l s='Informations' mod='amazon'}"/>&nbsp;&nbsp;{l s='Informations' mod='amazon'}</span></a>
    </li>
    <li id="menu-synchronize" class="menuTabButton {if $selected_tab eq 'synchronize'}selected active{/if}"><a href="#"><span>&nbsp;<img
                        src="{$images|escape:'quotes':'UTF-8'}synchronize-big.png"
                        alt="{l s='Offers' mod='amazon'}"/>&nbsp;&nbsp;{l s='Offers' mod='amazon'}</span></a>
    </li>
    {if $creation}
        <li id="menu-creation" class="menuTabButton {if $selected_tab eq 'creation'}selected active{/if}"><a
                    href="#"><span>&nbsp;<img src="{$images|escape:'quotes':'UTF-8'}create-big-2.png"
                                              alt="{l s='Products' mod='amazon'}"/>&nbsp;&nbsp;{l s='Products' mod='amazon'}</span></a>
        </li>
    {/if}
    {if $expert_mode && $deletion}
        <li id="menu-delete" class="menuTabButton {if $selected_tab eq 'delete'}selected active{/if}"><a href="#"><span>&nbsp;<img
                            src="{$images|escape:'quotes':'UTF-8'}trash-big.png"
                            alt="{l s='Delete' mod='amazon'}"/>&nbsp;&nbsp;{l s='Delete' mod='amazon'}</span></a></li>
    {/if}
    {if $import_products}
        <li id="menu-import" class="menuTabButton {if $selected_tab eq 'import'}selected active{/if}"><a href="#"><span>&nbsp;<img
                            src="{$images|escape:'quotes':'UTF-8'}import-big-1.png"
                            alt="{l s='Import' mod='amazon'}"/>&nbsp;&nbsp;{l s='Import' mod='amazon'}</span></a></li>
    {/if}
    <li id="menu-report" class="menuTabButton {if $selected_tab eq 'report'}selected active{/if}"><a href="#"><span>&nbsp;<img
                        src="{$images|escape:'quotes':'UTF-8'}report-big.png"
                        alt="{l s='Report' mod='amazon'}"/>&nbsp;&nbsp;{l s='Report' mod='amazon'}</span></a></li>
</ul>

<div id="tabList" class="panel">
    <!-- Synchronize Only -->
    <div id="menudiv-informations" class="tabItem {if $selected_tab eq 'informations'}selected{/if}" rel="informations">
        <h2 style="color:silver">{l s='Informations' mod='amazon'}</h2>
        <br/>

        <div class="hint clear alert alert-info">
            {l s='This displays statistics about your catalog and Amazon synch. This should display after a while.' mod='amazon'}
            <br/><br/>
        </div>
        <br/>
        <fieldset id="statistics-set" class="panel">
            {if $ps16x}<div class="panel-heading">{else}<legend>{/if}<img src="{$images|escape:'quotes':'UTF-8'}statistics.png" alt="" class="middle"/> {l s='Statistics' mod='amazon'}
            {if $ps16x}</div>{else}</legend>{/if}

            <div id="statistics-set-result"></div>
        </fieldset>
        <div class="conf" id="amazon-informations-result" style="margin-top:20px;"></div>
        <div class="error" id="amazon-informations-error" style="margin-top:20px;"></div>
        <br/>

        <div id="amazon-automaton-report-model" style="display:none">
            <div class="amz-info-load" id="amz-info-load-model">
                <span class="amz-info-flag"></span><span
                        class="amz-info-marketplace alert alert-info">Amazon</span><br/>
                <span class="amz-info-title"></span> - <span class="amz-info-message alert alert-info">Message</span>
                <span class="amz-info-loader"></span>
            </div>
        </div>
        <div id="amazon-automaton-report"></div>
        <input type="button" class="button btn" id="statistics-purge" value="{l s='Purge' mod='amazon'}"/>
        <input type="hidden" id="statistics-purge-confirm"
               value="{l s='Do you agree to purge statistics and reports entries ?' mod='amazon'}"/>
    </div>


    <!-- Synchronize Only -->
    <div id="menudiv-synchronize" class="tabItem {if $selected_tab eq 'synchronize'}selected{/if}" rel="synchronize">
        <h2 style="color:silver">{l s='Offers Feed (Synchronize)' mod='amazon'}</h2>

        <form action="#" id="amazonSyncOptions" name="amazonSyncOptions" method="POST">
            <div class="clean" style="margin-top:15px;"></div>
            <fieldset class="panel">
                {if $ps16x}<div class="panel-heading">{else}<legend>{/if}<img src="{$images|escape:'quotes':'UTF-8'}cog.gif" alt=""
                                                      class="middle"/> {l s='Options' mod='amazon'}
                {if $ps16x}</div>{else}</legend>{/if}
                <table style="width:900px;" class="amz-options">
                    <tr>
                        <td>
                            <span class="amz-small-help">{l s='Standard Options' mod='amazon'}</span>
                        </td>
                        <td>
                            <span class="amz-small-help">{l s='Advanced Options' mod='amazon'}</span>
                        </td>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" value="1"
                                   name="extended-datas"/><span>{l s='Extended Data' mod='amazon'}</span><br/>
                            <span style="margin-left:23px;font-size:0.8em;color:navy;">{l s='Send Title, Description, Manufacturer, Browse Node, etc.' mod='amazon'}</span>
                        </td>
                        <td>
                            <input type="checkbox" value="1"
                                   name="xml-only"/><span>{l s='Display XML' mod='amazon'}</span><br/>
                        </td>
                        <td>
                            {if $repricing}
                            <input type="checkbox" value="1"
                                   name="price-feed"/><span>{l s='Force Price Feed' mod='amazon'}</span><br/>
                            {else}
                                &nbsp;
                            {/if}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" value="1"
                                   name="images"/><span>{l s='Send Images' mod='amazon'}</span>

                        </td>
                        <td>
                            <input type="checkbox" value="1"
                                   name="entire-catalog"/><span>{l s='Send Entire Catalog' mod='amazon'}</span>
                        </td>
                        <td>
                            {if $expert_mode}
                                <input type="checkbox" value="1" name="delete-xml"/>
                                <span>{l s='Delete' mod='amazon'}</span>
                            {/if}

                        </td>
                    </tr>
                </table>
            </fieldset>

            <br/>

            <div class="hint clear alert alert-info  amazon-matching-hint" rel="action">
                {l s='This will synchronize Amazon depending your stocks moves: Products Added, Updated, Deleted' mod='amazon'}
                <br/><br/>
                <b>{l s='After that, please do not forget to generate and save a report' mod='amazon'}</b>
                ({l s='For any support you will need the XML and the Report' mod='amazon'}) <br/>
            </div>
            <div class="hint clear alert alert-info  amazon-matching-hint" rel="wizard" style="display:none;">
                {l s='This wizard will download your inventory from Amazon. The inventory will be compared to your local inventory.' mod='amazon'}
                <br/>
                {l s='After that, the wizard will mark as to be matched the unknown offers present in your database but not on Amazon. The goal is to create your offers on Amazon.' mod='amazon'}
                <br/>
                <b>
                    ({l s='This operation is automatic but could take one hour, you just should be patient and wait the processing to finish.' mod='amazon'}
                    ) </b><br/>
            </div>
            <br/>

            <div id="amazon-automaton-matching-model" style="display:none">
                <div class="amz-info-load-wizard">
                    <span class="amz-info-flag"></span><span class="amz-info-marketplace">Amazon</span><br/>
                    <span class="amz-info-title"></span> - <span class="amz-info-message"></span>
                    <span class="amz-info-loader"></span>
                </div>
            </div>

            {if $wizard_enabled}
                {include file="$matching_box"}
            {/if}

            <div id="amazon-automaton-matching"></div>
            <br/>

            <div class="conf error alert alert-danger" id="amazon-automaton-matching-error" style="display:none"></div>
            <br/>

            {if $wizard_enabled}
                <div class="button-wizard" id="submit-matching-wizard" style="display: none"><img
                            src="{$images|escape:'quotes':'UTF-8'}wizard.png"
                            alt="{l s='Automatic Discovery Wizard' mod='amazon'}"/><span>{l s='Automatic Discovery Wizard' mod='amazon'}</span>
                </div>
            {/if}

            <div class="button-proceed float-right" id="submit-synchronize"><span>{l s='Send To Amazon' mod='amazon'}</span><img
                        src="{$images|escape:'quotes':'UTF-8'}export-cloud-big.png"
                        alt="{l s='Send To Amazon' mod='amazon'}"/></div>
            <div class="button-separator"></div>
            <div class="button-proceed button-verify float-right" id="submit-synchronize-verify"><img
                        src="{$images|escape:'quotes':'UTF-8'}verify-big.png"
                        alt="{l s='Verify' mod='amazon'}"/><span>{l s='Verify' mod='amazon'}</span>
            </div>

            <div style="margin-top:80px;">
                <br/>
            </div>
            <div class="conf alert alert-success" id="amazon-synchronize-result" style="margin-top:20px;"></div>
            <div class="error alert alert-danger" id="amazon-synchronize-error" style="margin-top:20px;"></div>
        </form>
    </div>

    {if $creation}
        <!-- Creation Mode -->
        <div id="menudiv-creation" class="tabItem {if $selected_tab eq 'creation'}selected{/if}" rel="creation">
            <form action="#" id="amazonCreateOptions" name="amazonCreateOptions" method="POST">
                <h2 style="color:silver">{l s='Products Feed (Products Sheets Creation)' mod='amazon'}</h2>

                <div class="clean" style="margin-top:15px;"></div>
                <fieldset class="panel">
                    {if $ps16x}<h3>{else}<legend>{/if}<img src="{$images|escape:'quotes':'UTF-8'}cog.gif" alt=""
                                                          class="middle"/> {l s='Options' mod='amazon'}
                    {if $ps16x}</h3>{else}</legend>{/if}
                    <table style="min-width:850px;" class="amz-options">
                        <tr>

                            <td>
                                <span class="amz-small-help">{l s='Advanced Options' mod='amazon'}</span>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>

                            <td>
                                <input type="checkbox" value="1"
                                       name="xml-only"/><span>{l s='Display XML' mod='amazon'}</span><br/>
                            </td>
                            <td>
                                {if $expert_mode}
                                    <input type="checkbox" value="1" name="delete-xml"/>
                                    <span>{l s='Delete' mod='amazon'}</span>
                                    &nbsp;
                                {/if}
                            </td>
                            <td rowspan="2" {if !$ps16x}width="230px" {else}width="280px"{/if}>
                                <div style="position:relative;left:+15px;">
                                    <span>{l s='Limiter' mod='amazon'}</span>&nbsp;
                                    <select name="limit">
                                        <option value="0">{l s='No Limit' mod='amazon'}</option>
                                        <option value="10">10</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                        <option value="500">500</option>
                                        <option value="1000">1000</option>
                                        {if $expert_mode}
                                            <option value="3000">3000</option>
                                            <option value="5000">5000</option>
                                            <option value="10000">10000</option>
                                        {/if}
                                    </select>&nbsp;&nbsp;<span class="limiter-help"><br/>
                            
                        <span class="limiter-help">
                            {l s='Limit to n products' mod='amazon'}<br/>
                            {l s='Useful to understand your server\'s limits...' mod='amazon'}<br/>
                            {l s='Also useful to send small batches' mod='amazon'}<br/>
                        </span>
                                </div>
                            </td>
                        </tr>
                        <tr>

                            <td>
                                {if $expert_mode}
                                    <input type="checkbox" value="1" name="relations-only"/>
                                    <span>{l s='Send Relations Only' mod='amazon'}</span>
                                    &nbsp;
                                {/if}
                            </td>
                            <td>
                                {if $expert_mode}
                                    <input type="checkbox" value="1" name="entire-catalog"/>
                                    <span>{l s='Send Entire Catalog' mod='amazon'}</span>
                                {/if}
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>

                    </table>
                    <input type="hidden" name="create" value="1"/>
                </fieldset>

                <br/>

                <div class="hint clear alert alert-info amazon-create-hint" rel="action">
                    {l s='This will send all the products having a profile and within the selected categories in your module configuration' mod='amazon'}
                    <br/>
                    {l s='If you want to add a product to the list, please go on the product sheet and choose "create" in Amazon options tab' mod='amazon'}
                    <br/><br/>
                    <b>{l s='After that, please do not forget to generate and save a report' mod='amazon'}</b>
                    ({l s='For any support you will need the XML and the Report' mod='amazon'}) <br/>
                </div>
                <div class="hint clear alert alert-info amazon-create-hint" rel="wizard" style="display:none;">
                    {l s='This wizard will download your inventory from Amazon. The inventory will be compared to your local inventory.' mod='amazon'}
                    <br/>
                    {l s='After that, the wizard will mark as to be created the unknown products on Amazon but present in your database. The goal is to create the unknown items on Amazon.' mod='amazon'}
                    <br/>
                    <b>
                        ({l s='This operation is automatic but could take one hour, you just should be patient and wait the processing terminate.' mod='amazon'}
                        ) </b><br/>
                </div>
                <br/>

                <div id="amazon-automaton-creation-model" style="display:none">
                    <div class="amz-info-load-wizard" id="amz-info-load-wizard-model">
                        <span class="amz-info-flag"></span><span class="amz-info-marketplace">Amazon</span><br/>
                        <span class="amz-info-title"></span> - <span class="amz-info-message"></span>
                        <span class="amz-info-loader"></span>
                    </div>
                </div>
                <div id="amazon-automaton-creation"></div>
                <div class="conf error alert alert-danger" id="amazon-automaton-creation-error"
                     style="display:none"></div>
                <br/>

                {if $wizard_enabled}
                    <div class="button-wizard" id="submit-creation-wizard" style="display:none"><img
                                src="{$images|escape:'quotes':'UTF-8'}wizard.png"
                                alt="{l s='Automatic Discovery Wizard' mod='amazon'}"/><span>{l s='Automatic Discovery Wizard' mod='amazon'}</span>
                    </div>
                {/if}


                <div class="button-proceed float-right" id="submit-creation"><span>{l s='Send To Amazon' mod='amazon'}</span><img
                            src="{$images|escape:'quotes':'UTF-8'}export-cloud-big.png"
                            alt="{l s='Send To Amazon' mod='amazon'}"/></div>
                <div class="button-separator"></div>
                <div class="button-proceed button-verify float-right" id="submit-creation-verify"><img
                            src="{$images|escape:'quotes':'UTF-8'}verify-big.png"
                            alt="{l s='Verify' mod='amazon'}"/><span>{l s='Verify' mod='amazon'}</span>
                </div>

                <div style="margin-top:80px;">
                    <br/>
                </div>
                <div class="{if $ps16x}conf alert alert-success{else}conf{/if}" id="amazon-creation-result"
                     style="margin-top:20px;"></div>
                <div class="{if $ps16x}conf alert alert-danger{else}error{/if}" id="amazon-creation-error"
                     style="margin-top:20px;"></div>
            </form>
        </div>
    {/if}

    {if $expert_mode}
        <!-- Delete Mode -->
        <div id="menudiv-delete" class="tabItem {if $selected_tab eq 'delete'}selected{/if}" rel="delete">
            <form action="#" id="amazonDeleteOptions" name="amazonDeleteOptions" method="POST">
                <h2 style="color:silver">{l s='Products Sheets Creation' mod='amazon'}</h2>

                <div class="clean" style="margin-top:15px;"></div>
                <fieldset class="panel">
                    {if $ps16x}<h3>{else}<legend>{/if}<img src="{$images|escape:'quotes':'UTF-8'}cog.gif" alt=""
                                                          class="middle"/> {l s='Options' mod='amazon'}
                    {if $ps16x}</h3>{else}</legend>{/if}
                    <table style="width:800px;" class="amz-options">
                        <tr>
                            <td>
                                <span class="amz-small-help">{l s='Standard Options' mod='amazon'}</span>
                            </td>
                            <td>
                                <span class="amz-small-help">{l s='Advanced Options' mod='amazon'}</span>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" value="1"
                                       name="delete-confirm"/><span>{l s='Confirmer' mod='amazon'}</span>&nbsp;
                            </td>
                            <td>
                                <input type="checkbox" value="1"
                                       name="xml-only"/><span>{l s='Display XML' mod='amazon'}</span><br/>
                            </td>
                            <td>
                                <input type="checkbox" value="1"
                                       name="delete-overrides"/><span>{l s='Delete Shipping Charges Overrides' mod='amazon'}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                &nbsp;&nbsp;&nbsp;
                            </td>
                            <td>
                                <input type="checkbox" value="1"
                                       name="entire-catalog"/><span>{l s='Send Entire Catalog' mod='amazon'}</span>
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <br/>

                <div class="hint clear alert alert-info">
                    {l s='This will send all the products having a profile and within the selected categories in your module configuration and selected for deletion on product page' mod='amazon'}
                    <br/><br/><br/>
                </div>
                <br/>

                <div class="button-proceed float-left" id="submit-delete"><span>{l s='Send To Amazon' mod='amazon'}</span><img
                            src="{$images|escape:'quotes':'UTF-8'}export-cloud-big.png"
                            alt="{l s='Send To Amazon' mod='amazon'}"/></div>
                <div class="button-separator"></div>
                <div class="button-verify float-right" id="submit-delete-verify"><img
                            src="{$images|escape:'quotes':'UTF-8'}verify-big.png"
                            alt="{l s='Verify' mod='amazon'}"/><span>{l s='Verify' mod='amazon'}</span>
                </div>

                <div style="margin-top:80px;">
                    <br/>
                </div>
                <div class="conf" id="amazon-delete-result" style="margin-top:20px;"></div>
                <div class="error" id="amazon-delete-error" style="margin-top:20px;"></div>
            </form>
        </div>
    {/if}



    {if $import_products}
        <!-- Delete Mode -->
        <div id="menudiv-import" class="tabItem {if $selected_tab eq 'import'}selected{/if}" rel="import">
            <form action="#" id="amazonImportOptions" name="amazonImportOptions" method="POST">
                <input type="hidden" id="catalog-import-url" value="{$import_url|escape:'htmlall':'UTF-8'}"/>
                <h2 style="color:silver">{l s='Import Products' mod='amazon'}</h2>

                <div class="clean" style="margin-top:15px;"></div>
                <fieldset class="panel">
                    {if $ps16x}<h3>{else}<legend>{/if}<img src="{$images|escape:'quotes':'UTF-8'}cog.gif" alt=""
                                                           class="middle"/> {l s='Options' mod='amazon'}
                        {if $ps16x}</h3>{else}</legend>{/if}
                    <table style="width:800px;" class="amz-options">
                        <tr>
                            <td>
                                <span class="amz-small-help">{l s='Standard Options' mod='amazon'}</span>
                            </td>
                            <td>
                                <span class="amz-small-help">{l s='Advanced Options' mod='amazon'}</span>
                            </td>
                            <td>
                                &nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" value="1" checked
                                       name="import-features"/><span>{l s='Create Features' mod='amazon'}</span>&nbsp;
                            </td>
                            <td>
                                <input type="checkbox" value="1" name="update-existing" checked/><span>{l s='Update existing' mod='amazon'}</span><br/>
                            </td>
                            <td>
                                <input type="checkbox" value="1" name="visbility-hidden" checked/><span>{l s='Hide product (switches visibilty to hidden)' mod='amazon'}</span><br/>
                            </td>

                        </tr>
                        <tr>
                            <td>

                            </td>
                            <td>
                                <input type="checkbox" value="1" name="update-price" checked/><span>{l s='Set price' mod='amazon'}</span><br/>&nbsp;&nbsp;&nbsp;
                            </td>
                            <td>
                                <input type="checkbox" value="1" name="update-quantity" checked/><span>{l s='Set quantity' mod='amazon'}</span><br/>&nbsp;&nbsp;&nbsp;
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <br/>

                <div class="hint clear alert alert-info">
                    {l s='Purpose of this tab is to import your existing inventory from Amazon and create it into Prestashop' mod='amazon'}
                    <br/><br/><br/>
                </div>
                <br/>

                <div class="button-proceed float-right disabled" id="submit-import"><span>{l s='Import from Amazon' mod='amazon'}</span><img
                            src="{$images|escape:'quotes':'UTF-8'}import-cloud-big.png"
                            alt="{l s='Import from Amazon' mod='amazon'}"/></div>
                <div class="button-proceed float-right" id="submit-import-stop" style="display:none"><span>{l s='Stop' mod='amazon'}</span><img
                            src="{$images|escape:'quotes':'UTF-8'}stop.png"
                            alt="{l s='Import from Amazon' mod='amazon'}"/></div>
                <div class="button-separator"></div>
                <div class="button-verify float-right" id="submit-import-verify"><img
                            src="{$images|escape:'quotes':'UTF-8'}verify-big.png"
                            alt="{l s='Verify' mod='amazon'}"/><span>{l s='Request' mod='amazon'}</span>
                </div>

                <div style="margin-top:80px;">
                    <br/>
                </div>
                <div class="conf" id="amazon-import-loader" style="display:none;"><img src="{$img_loader|escape:'htmlall':'UTF-8'}" alt="" style="margin-left:50%" /></div>
                <div class="conf alert alert-success" id="amazon-import-success" style="margin-top:20px;display:none;"></div>
                <div class="error alert alert-danger" id="amazon-import-error" style="margin-top:20px;;display:none;"></div>
            </form>
        </div>
    {/if}

    <!-- Report Tab -->
    {include file=$tpl_path|escape:'quotes':'UTF-8'|cat:'views/templates/admin/ReportAmazon.tpl' report_url=$report_url type='product'}
</div>

{if $widget}
{* Support Widget*}
<script type="text/javascript" src="https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.js"></script>
{include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/support/widget.tpl" data=$widget}
{* End of Support Widget*}
{/if}