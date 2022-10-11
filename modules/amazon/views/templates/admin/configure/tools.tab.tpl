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
<div id="menudiv-tools" class="tabItem {if $tools.selected_tab}selected{/if} panel form-horizontal">

    <h3>{l s='Tools' mod='amazon'}</h3>

    <div rel="amazon-tools">
        <form action="{$tools.request_uri|escape:'quotes':'UTF-8'}" method="post" enctype="multipart/form-data">
            <input type="hidden" id="tools_ajax_error"
                   value="{l s='An unexpected server side error occurs, please verify your module configuration first.' mod='amazon'}"/>

            <h4 class="tools-tab-title">{l s='Products References File' mod='amazon'}</h4><span
                    style="float:left;margin-left:15px;">{l s='Manage Products Codes (EAN/UPC) and SKU (Reference)' mod='amazon'}</span><br/>

            <br/>

            {if !$amazon.is_lite}
            <div class="margin-form col-lg-offset-3">
                <div style="font-size:1.2em;line-height:140%;" class="amz-info-level-info alert alert-info">
                    <ul>
                        <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                        <li>{$tools.tutorial|escape:'quotes':'UTF-8'}</li>
                    </ul>
                </div>
            </div>
            {/if}

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Export' mod='amazon'}</label><br/>

                <div align="left" class="margin-form col-lg-9">
                    <p>
                        <span class="field_label"
                              style="width:100px;text-align:right;">{l s='Options' mod='amazon'}</span>
                        <span class="checkbox_config"><input type="checkbox" id="tools_code_export_active"
                                                             name="tools_code_export_active" value="1"
                                                             checked/>{l s='Only Active' mod='amazon'}</span>
                        <span class="checkbox_config"><input type="checkbox" id="tools_code_export_in_stock"
                                                             name="tools_code_export_in_stock" value="1"
                                                             checked/>{l s='Only in Stock' mod='amazon'}</span>

                        <input type="hidden" id="amazon_tools_url" value="{$tools.tools_url|escape:'quotes':'UTF-8'}"/>
                        <input type="submit" name="tools_code_export_submit" id="tools_code_export_submit"
                               value="{l s='Export Amazon Product Set' mod='amazon'}" class="button btn float-right"/>

                        <img src="{$tools.images_url|escape:'quotes':'UTF-8'}green-loader.gif" class="export-loader"
                             alt="{l s='Export Amazon Product Set' mod='amazon'}"/>
                    </p>
                    <br/>

                    <div id="tools_code_export_error" style="display:none;font-size:1.1em;width:90%;" class="error">
                        <ul>
                            <li>{l s='An error occured on file generation.' mod='amazon'}</li>
                            <li id="tools_code_export_result_error"></li>
                        </ul>
                    </div>

                    <div id="tools_code_export_success" style="display:none;" class="file-download">
                        <ul>
                            <li>{l s='Product reference file has been successfully exported.' mod='amazon'}</li>
                            <li>{l s='Please click on the following link to download:' mod='amazon'}</li>
                            <li id="tools_code_export_result_ok"></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3" rel="tool_import"><span>{l s='Import' mod='amazon'}</span></label><br/>

                <div align="left" class="margin-form col-lg-9">

                    <p>
                        <span class="field_label" {*style="width:200px;"*}>{l s='Replace Existing Data' mod='amazon'}</span>
                        <span class="radio_config"><input type="radio" name="tools_code_import_replace" value="1">{l s='Yes' mod='amazon'}</span>
                        <span class="radio_config"><input type="radio" name="tools_code_import_replace" value="0" checked>{l s='No' mod='amazon'}</span>
                        <br/>

                    </p>

                    <p>
                    <span class="field_label"
                          style="width:150px;text-align:right;">{l s='Send the CSV File' mod='amazon'}</span>&nbsp;&nbsp;
                        <input type="file" id="tools_code_import" name="tools_code_import" style="width:300px;"/>&nbsp;&nbsp;
                        <input type="submit" name="tools_code_import_submit" style="width:150px"
                               value="{l s='Send' mod='amazon'}" class="button btn float-right"/>
                    </p>

                </div>
            </div>
    </div>
    <br/>

    {if $tools.valid_values && isset($tools.valid_values_action)}
        <h4 class="valid-values-title">{l s='Valid Values Table' mod='amazon'}</h4>
            <span style="float:left;margin-left:15px;">{l s='List of valid values' mod='amazon'}</span>
            <br/>
            <br/>
            {if isset($tools.valid_values_last_import)}
                <div class="form-group">
                    <div class="margin-form col-lg-offset-3 col-lg-9">
                        <div class="{$class_info|escape:'htmlall':'UTF-8'}">
                            {$tools.valid_values_last_import|escape:'htmlall':'UTF-8'}
                        </div>
                    </div>
                </div>
            {/if}
            <div class="form-group">
                <div align="left" class="margin-form col-lg-offset-3">
                    <div id="valid-values-success" class="{$class_success|escape:'htmlall':'UTF-8'}" style="display:none">
                    </div>

                    <div id="valid-values-warning" class="{$class_warning|escape:'htmlall':'UTF-8'}" style="display:none">
                    </div>

                    <div id="valid-values-error" class="{$class_error|escape:'htmlall':'UTF-8'}" style="display:none">
                    </div>
                </div>
            </div>
            <div align="left" class="margin-form col-lg-offset-3">
                <p class="{$class_info|escape:'htmlall':'UTF-8'}" id="valid-values-loader" style="display:none">
                    {l s='Attempting to download and install valid values list from Amazon S3' mod='amazon'}... &nbsp;&nbsp;&nbsp;<img
                            src="{$tools.images_url|escape:'quotes':'UTF-8'}loader-connection.gif" alt=""/>
                </p>
            </div>
            <div align="left" class="margin-form col-lg-offset-3">
                <input type="hidden" id="valid-values-delete-text"
                       value="{l s='Are you sure that you want to delete valid values ?' mod='amazon'}"/>
                <input type="button" class="button btn  float-right" id="valid-values-button"
                   value="{$tools.valid_values_action|escape:'quotes':'UTF-8'}"/>
            <input type="button" class="button btn  float-right" id="valid-values-delete-button"
                   value="{l s='Delete' mod='amazon'}"/>
            <br/>
        </div>
        <br/>
    {/if}

    {if $tools.maintenance}
        <div rel="amazon-expert-mode" class="amazon-expert-mode" style="display:none">
            <h4 class="maintenance-title">{l s='Maintenance' mod='amazon'}</h4>
            <span style="float:left;margin-left:15px;">{l s='Perform Module Maintenance' mod='amazon'}</span><sup
                    class="expert">{l s='Expert' mod='amazon'}</sup>
            <br/>
            <br/>

            <div class="form-group">
                <div align="left" class="margin-form col-lg-offset-3">
                    <div id="maintenance-success" class="{$class_success|escape:'htmlall':'UTF-8'}"
                         style="display:none">
                    </div>

                    <div id="maintenance-warning" class="{$class_warning|escape:'htmlall':'UTF-8'}"
                         style="display:none">
                    </div>

                    <div id="maintenance-error" class="{$class_error|escape:'htmlall':'UTF-8'}" style="display:none">
                    </div>
                </div>
            </div>

            <div align="left" class="margin-form col-lg-offset-3">
                <table class="table table-maintenance">
                    <tbody>
                    <tr class="active">
                        <th>{l s='Operation' mod='amazon'}</th>
                        <th>{l s='Action' mod='amazon'}</th>
                    </tr>
                    <tr>
                        <td>
                            {l s='Update Amazon Carriers' mod='amazon'}
                        </td>
                        <td>
                            <input type="button" class="button btn float-right" id="maintenance-carrier"
                                   value="{l s='Update' mod='amazon'}"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {l s='Delete translations files' mod='amazon'}
                        </td>
                        <td>
                            <input type="button" class="button btn float-right" id="maintenance-delete-translations"
                                   value="{l s='Delete' mod='amazon'}"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {l s='Delete model data & translations file' mod='amazon'}
                        </td>
                        <td>
                            <input type="button" class="button btn float-right" id="maintenance-delete-models"
                                   value="{l s='Delete' mod='amazon'}"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div align="left" class="margin-form col-lg-offset-3">
                <p class="{$class_info|escape:'htmlall':'UTF-8'}" id="maintenance-loader" style="display:none">
                    {l s='Performing maintenance operation' mod='amazon'}... &nbsp;&nbsp;&nbsp;<img
                            src="{$tools.images_url|escape:'quotes':'UTF-8'}loader-connection.gif" alt=""/>
                </p>
            </div>

            <div align="left" class="margin-form col-lg-offset-3">
                <input type="hidden" id="maintenance-text"
                       value="{l s='Are you sure that you want to perform this action ?' mod='amazon'}"/>
            </div>
        </div>
        <br/>
    {/if}


    {if isset($tools.action_queue)}
        <br/>
        <br/>
        <h4 class="tools-tab-title">{l s='Queue' mod='amazon'}</h4>
        <span style="float:left;margin-left:15px;">{l s='Current queue to send to Amazon' mod='amazon'}</span>
        <br/>
        <br/>
        {if isset($tools.action_queue_missing) && $tools.action_queue_missing}
            <div class="form-group">
                <div class="margin-form col-lg-offset-3 col-lg-9">
                    <div class="{$class_error|escape:'htmlall':'UTF-8'}">
                        {l s='Queue table is missing' mod='amazon'}
                    </div>
                </div>
            </div>
        {elseif is_array($tools.action_queue) && !count($tools.action_queue)}
            <div class="form-group">
                <div class="margin-form col-lg-offset-3 col-lg-9">
                    <div class="{$class_info|escape:'htmlall':'UTF-8'}">
                        {l s='Queue is empty' mod='amazon'}
                    </div>
                </div>
            </div>
        {else}
            <div align="left" class="margin-form col-lg-offset-3">
                <table class="table table-queue">

                    <tbody>
                    <tr class="active">
                        <th><img src="{$tools.images_url|escape:'quotes':'UTF-8'}green-loader.gif" class="queue-loader"
                                 alt="{l s='Delete Selected Queue' mod='amazon'}"/></th>
                        <th>{l s='Language' mod='amazon'}</th>
                        <th>{l s='Action' mod='amazon'}</th>
                        <th>{l s='Start Date' mod='amazon'}</th>
                        <th>{l s='End Date' mod='amazon'}</th>
                        <th>{l s='Qty' mod='amazon'}</th>
                        <th class="table-queue-delete">{l s='Del.' mod='amazon'}</th>
                    </tr>
                    {if is_array($tools.action_queue) && count($tools.action_queue)}
                        {foreach from=$tools.action_queue item=queue}
                            <tr>
                                {if $queue.flag}
                                    <td><img src="{$queue.flag|escape:'htmlall':'UTF-8'}"
                                             alt="{$queue.lang|escape:'htmlall':'UTF-8'}"/></td>
                                {else}
                                    <td>-</td>
                                {/if}
                                <td>
                                    {$queue.lang|escape:'htmlall':'UTF-8'}
                                </td>
                                <td>
                                    {$queue.action_name|escape:'htmlall':'UTF-8'}
                                </td>
                                <td>
                                    {$queue.date_min|escape:'htmlall':'UTF-8'}
                                </td>
                                <td>
                                    {$queue.date_max|escape:'htmlall':'UTF-8'}
                                </td>
                                <td>
                                    {$queue.count|escape:'htmlall':'UTF-8'}
                                </td>
                                <td class="table-queue-delete">
                                    <input type="checkbox"
                                           name="queue[{$queue.id_lang|escape:'htmlall':'UTF-8'}][{$queue.action|escape:'htmlall':'UTF-8'}]"
                                           value="1"/>
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                    </tbody>
                </table>
                {if $psIs16}<br>{/if}
                <input type="button" id="queue-delete" value="{l s='Delete Selected Queue' mod='amazon'}"
                       class="button button-queue-delete btn float-right"/>
                {if $psIs16}<br><br>{/if}
            </div>
        {/if}
    {/if}
    </form>


    <div class="form-group">
        <div class="margin-form col-lg-offset-3">
            &nbsp;
        </div>
    </div>

</div><!-- menudiv-tools -->