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
<div id="menudiv-report" class="tabItem {if $selected_tab eq 'report'}selected{/if}" rel="report">
    <form action="#" id="amazonReportOptions" name="amazonReportOptions" method="POST">
        <h2 style="color:silver">{l s='Report' mod='amazon'}</h2>

        <div class="clean" style="margin-top:15px;"></div>
        <br/>

        <div class="hint clear alert alert-info">
            {l s='This will display a report generated by Amazon about your last submission. Please use this report for any support, if required.' mod='amazon'}
            <br/><br/>
        </div>
        <br/>

        <div class="form-group">
            <label class="control-label col-lg-3" style="color:grey">{l s='Report List' mod='amazon'}</label>

            <div class="margin-form col-lg-9">

                <div class="{$alert_class.warning|escape:'htmlall':'UTF-8'} col-lg-12" id="reports-none-available"
                     style="display:none;">{l s='No available report' mod='amazon'}</div>

                <div id="reports-loader"></div>

                <table class="table report table-hover" cellpadding="0" cellspacing="0">

                    <thead class="report-table-heading" style="display:none">
                    <tr class="active">
                        <th class="left">{l s='Id' mod='amazon'}</th>
                        <th class="left">{l s='Region' mod='amazon'}</th>
                        <th class="left">{l s='Type' mod='amazon'}</th>
                        <th class="left">{l s='Start' mod='amazon'}</th>
                        <th class="left">{l s='Stop' mod='amazon'}</th>
                        <th class="left">{l s='Duration' mod='amazon'}</th>
                        <th class="left">{l s='Items' mod='amazon'}</th>
                    </tr>
                    </thead>
                    <tbody class="reports">
                    <tr class="row_hover report-model" style="display:none;">
                        <td class="left" rel="id"></td>
                        <td class="left" rel="region"></td>
                        <td class="left" rel="type"></td>
                        <td class="left" rel="start"></td>
                        <td class="left" rel="stop">&nbsp;</td>
                        <td class="left" rel="duration">&nbsp;</td>
                        <td class="left" rel="items">&nbsp;</td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>

        <hr style="width:30%;"/>
        {*July-04-2018: Remove not used button*}

        <div class="form-group">
            <div class="margin-form col-lg-offset-3">

                {*Hidden fields*}
                <input type="hidden" id="catalog-reports-select-msg" value="{l s='You must select a report' mod='amazon'}"/>
                <input type="hidden" id="reports-url" value="{$report_url|escape:'htmlall':'UTF-8'}"/>
                <input type="hidden" id="reports-type" value="{$type|escape:'htmlall':'UTF-8'}"/>
                <br/><br/><br/>

                <div class="{$alert_class.success|escape:'htmlall':'UTF-8'} col-lg-12" id="reports-result"
                     style="display:none;"></div>
                <div class="{$alert_class.danger|escape:'htmlall':'UTF-8'} col-lg-12" id="reports-error"
                     style="display:none;"></div>
                <div class="{$alert_class.warning|escape:'htmlall':'UTF-8'} col-lg-12" id="reports-warning"
                     style="display:none"></div>

                <pre id="catalog-report-summary" style="display: none;"></pre>
                <pre id="catalog-report-details" style="display: none;"></pre>
            </div>
        </div>


        <div class="button-proceed float-right" id="submit-report-display"><span>{l s='View Report' mod='amazon'}</span><img
                    src="{$images|escape:'quotes':'UTF-8'}import-cloud-big.png"
                    alt="{l s='View Report' mod='amazon'}"/></div>

        <div style="margin-top:60px;">
            <br/>
        </div>

        <div id="wait-report" style="display:none;"><img
                    src="{$images|escape:'quotes':'UTF-8'}small-loader.gif"/><span>{l s='Please wait for the report' mod='amazon'}</span>
        </div>

        <fieldset class="panel" id="report-set" style="display:none;">
            {if $ps16x}<h3>{else}<legend>{/if}
                <img src="{$images|escape:'quotes':'UTF-8'}printer.gif" alt="{l s='Report' mod='amazon'}" class="middle"/> {l s='Report' mod='amazon'}
            {if $ps16x}</h3>{else}</legend>{/if}

            <div id="submission-results"></div>
        </fieldset>
        <div class="conf alert alert-success" id="amazon-report-result"></div>
        <div class="error alert alert-danger" id="amazon-report-error"></div>
        <div style="margin-top:40px;">
            <br/>
        </div>
    </form>
</div>