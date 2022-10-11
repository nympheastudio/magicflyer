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
<div id="menudiv-cron" class="tabItem {if $cron.selected_tab}selected{/if} panel form-horizontal">
    <input type="hidden" id="context_key" value="{$cron.context_key|escape:'htmlall':'UTF-8'}"
    <h3>{l s='Cron URLs' mod='amazon'}</h3>

    {if !$amazon.is_lite}
    <div class="margin-form">
        <div class="amz-info-level-info {if $psIs16}alert alert-info col-lg-offset-3{/if}" style="font-size:1.1em">
            <ul>
                <li>{l s='Please read our online tutorial' mod='amazon'}:</li>
                <li>{$cron.tutorial|escape:'quotes':'UTF-8'}</li>
            </ul>
        </div>
    </div>
    {/if}

    <div class="form-group">
        <div class="margin-form col-lg-offset-3">
            <div id="cronjobs_success" class="{$class_success|escape:'htmlall':'UTF-8'}" style="display:none">
            </div>

            <div id="cronjobs_error" class="{$class_error|escape:'htmlall':'UTF-8'}" style="display:none">
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="margin-form col-lg-offset-3">
            <div class="cron-mode" rel="prestashop-cron">
                <img src="{$cron.images_url|escape:'quotes':'UTF-8'}/prestashop-cronjobs-icon.png"
                     title="{l s='Prestashop Cronjobs (Module)' mod='amazon'}"/>
                <h4>{l s='Prestashop Cronjobs (Module)' mod='amazon'}</h4>

                <div style="float:right" class="cron-prestashop">
                    {if $cron.prestashop.installed}
                        <span style="color:green">{l s='Installed' mod='amazon'}</span>
                    {elseif $cron.prestashop.exists}
                        <span style="color:red">{l s='Detected, Not installed' mod='amazon'}</span>
                    {else}
                        <span style="color:red">{l s='Not detected' mod='amazon'}</span>
                    {/if}
                </div>
            </div>

        </div>
    </div>


<div id="prestashop-cron" class="cron-toggle" {if !$cron.prestashop.installed}style="display:none"{/if} >
<div class="form-group">
<div class="margin-form col-lg-offset-3">

    {if !$cron.prestashop.installed}
        <div class="margin-form col-lg-9">
            <div class="{$class_warning|escape:'htmlall':'UTF-8'}">{l s='Prestashop Cronjobs is not installed.' mod='amazon'} {if !$cron.prestashop.exists}(
                    <a href="https://github.com/PrestaShop/cronjobs/archive/master.zip" target="_blank">https://github.com/PrestaShop/cronjobs</a>
                    ){/if}</div>
        </div>
    {else}
        <span class="title">{l s='Those lines will be added in Prestashop Cronjobs module' mod='amazon'}:</span>
<div id="prestashop-cronjobs-lines">
{if isset($cron.orders)}
{if $cron.orders.import}
{foreach from=$cron.orders.import item=task}
<b>{$task.title|escape:'htmlall':'UTF-8'}</b>({$task.lang|escape:'htmlall':'UTF-8'}): {l s='each' mod='amazon'} {$task.frequency|intval|abs} {if $task.frequency > 12}{l s='minutes' mod='amazon'}{elseif $task.frequency > 1}{l s='hours' mod='amazon'}{else}{l s='hour' mod='amazon'}{/if}, {l s='url' mod='amazon'}:<a href="{$task.url|escape:'quotes':'UTF-8'}" target="_blank">{$task.short_url|escape:'quotes':'UTF-8'}</a>
{/foreach}
{/if}
{if $cron.orders.status}

{foreach from=$cron.orders.status item=task}
<b>{$task.title|escape:'htmlall':'UTF-8'}</b>({$task.lang|escape:'htmlall':'UTF-8'}): {l s='each' mod='amazon'} {$task.frequency|intval|abs} {if $task.frequency > 12}{l s='minutes' mod='amazon'}{elseif $task.frequency > 1}{l s='hours' mod='amazon'}{else}{l s='hour' mod='amazon'}{/if}, {l s='url' mod='amazon'}: <a href="{$task.url|escape:'quotes':'UTF-8'}" target="_blank">{$task.short_url|escape:'quotes':'UTF-8'}</a>
{/foreach}
{/if}
{if $cron.orders.canceled}

{foreach from=$cron.orders.canceled item=task}<b>{$task.title|escape:'htmlall':'UTF-8'}</b>({$task.lang|escape:'htmlall':'UTF-8'}): {l s='each' mod='amazon'} {$task.frequency|intval|abs} {if $task.frequency > 12}{l s='minutes' mod='amazon'}{elseif $task.frequency > 1}{l s='hours' mod='amazon'}{else}{l s='hour' mod='amazon'}{/if}, {l s='url' mod='amazon'}: <a href="{$task.url|escape:'quotes':'UTF-8'}" target="_blank">{$task.short_url|escape:'quotes':'UTF-8'}</a>
{/foreach}
{/if}
{if $cron.orders.report}

{foreach from=$cron.orders.report item=task}<b>{$task.title|escape:'htmlall':'UTF-8'}</b>({$task.lang|escape:'htmlall':'UTF-8'}): {l s='each' mod='amazon'} {$task.frequency|intval|abs} {if $task.frequency > 12}{l s='minutes' mod='amazon'}{elseif $task.frequency > 1}{l s='hours' mod='amazon'}{else}{l s='hour' mod='amazon'}{/if}, {l s='url' mod='amazon'}: <a href="{$task.url|escape:'quotes':'UTF-8'}" target="_blank">{$task.short_url|escape:'quotes':'UTF-8'}</a>
{/foreach}
{/if}
{/if}
{if $cron.fba.status}

{foreach from=$cron.fba.status item=task}
<b>{$task.title|escape:'htmlall':'UTF-8'}</b>({$task.lang|escape:'htmlall':'UTF-8'}): {l s='each' mod='amazon'} {$task.frequency|intval|abs} {if $task.frequency > 12}{l s='minutes' mod='amazon'}{elseif $task.frequency > 1}{l s='hours' mod='amazon'}{else}{l s='hour' mod='amazon'}{/if}, {l s='url' mod='amazon'}: <a href="{$task.url|escape:'quotes':'UTF-8'}" target="_blank">{$task.short_url|escape:'quotes':'UTF-8'}</a>
{/foreach}
{/if}
{if $cron.fba.stocks}

{foreach from=$cron.fba.stocks item=task}
<b>{$task.title|escape:'htmlall':'UTF-8'}</b>({$task.lang|escape:'htmlall':'UTF-8'}): {l s='each' mod='amazon'} {$task.frequency|intval|abs} {if $task.frequency > 12}{l s='minutes' mod='amazon'}{elseif $task.frequency > 1}{l s='hours' mod='amazon'}{else}{l s='hour' mod='amazon'}{/if}, {l s='url' mod='amazon'}: <a href="{$task.url|escape:'quotes':'UTF-8'}" target="_blank">{$task.short_url|escape:'quotes':'UTF-8'}</a>
{/foreach}
{/if}

{if $cron.products.synch}
{foreach from=$cron.products.synch item=task}
<b>{$task.title|escape:'htmlall':'UTF-8'}</b>({$task.lang|escape:'htmlall':'UTF-8'}): {l s='each' mod='amazon'} {$task.frequency|intval|abs} {if $task.frequency > 12}{l s='minutes' mod='amazon'}{elseif $task.frequency > 1}{l s='hours' mod='amazon'}{else}{l s='hour' mod='amazon'}{/if}, {l s='url' mod='amazon'}: <a href="{$task.url|escape:'quotes':'UTF-8'}" target="_blank">{$task.short_url|escape:'quotes':'UTF-8'}</a>
{/foreach}
{/if}

{if $cron.repricing.reprice}
{foreach from=$cron.repricing.reprice item=task}
<b>{$task.title|escape:'htmlall':'UTF-8'}</b>({$task.lang|escape:'htmlall':'UTF-8'}): {l s='each' mod='amazon'} {$task.frequency|intval|abs} {if $task.frequency > 12}{l s='minutes' mod='amazon'}{elseif $task.frequency > 1}{l s='hours' mod='amazon'}{else}{l s='hour' mod='amazon'}{/if}, {l s='url' mod='amazon'}: <a href="{$task.url|escape:'quotes':'UTF-8'}" target="_blank">{$task.short_url|escape:'quotes':'UTF-8'}</a>
{/foreach}
{/if}

{if $cron.repricing.update}
{foreach from=$cron.repricing.update item=task}
<b>{$task.title|escape:'htmlall':'UTF-8'}</b>({$task.lang|escape:'htmlall':'UTF-8'}): {l s='each' mod='amazon'} {$task.frequency|intval|abs} {if $task.frequency > 12}{l s='minutes' mod='amazon'}{elseif $task.frequency > 1}{l s='hours' mod='amazon'}{else}{l s='hour' mod='amazon'}{/if}, {l s='url' mod='amazon'}: <a href="{$task.url|escape:'quotes':'UTF-8'}" target="_blank">{$task.short_url|escape:'quotes':'UTF-8'}</a>
{/foreach}
{/if}

{if $cron.repricing.export}
{foreach from=$cron.repricing.export item=task}
<b>{$task.title|escape:'htmlall':'UTF-8'}</b>({$task.lang|escape:'htmlall':'UTF-8'}): {l s='each' mod='amazon'} {$task.frequency|intval|abs} {if $task.frequency > 12}{l s='minutes' mod='amazon'}{elseif $task.frequency > 1}{l s='hours' mod='amazon'}{else}{l s='hour' mod='amazon'}{/if}, {l s='url' mod='amazon'}: <a href="{$task.url|escape:'quotes':'UTF-8'}" target="_blank">{$task.short_url|escape:'quotes':'UTF-8'}</a>
{/foreach}
{/if}


{if $cron.messaging.grab}
{foreach from=$cron.messaging.grab item=task}
<b>{$task.title|escape:'htmlall':'UTF-8'}</b>({$task.lang|escape:'htmlall':'UTF-8'}): {l s='each' mod='amazon'} {$task.frequency|intval|abs} {if $task.frequency > 12}{l s='minutes' mod='amazon'}{elseif $task.frequency > 1}{l s='hours' mod='amazon'}{else}{l s='hour' mod='amazon'}{/if}, {l s='url' mod='amazon'}: <a href="{$task.url|escape:'quotes':'UTF-8'}" target="_blank">{$task.short_url|escape:'quotes':'UTF-8'}</a>
{/foreach}
{/if}
</div>
<textarea id="prestashop-cronjobs-params" name="prestashop-cronjobs-params" style="display:none">
{if isset($cron.orders)}
{foreach from=$cron.orders.import item=task}{$task.title|escape:'htmlall':'UTF-8'}|{$task.lang|escape:'htmlall':'UTF-8'}|{$task.frequency|escape:'htmlall':'UTF-8'}|{$task.url|escape:'quotes':'UTF-8'}!
{/foreach}
{foreach from=$cron.orders.status item=task}{$task.title|escape:'htmlall':'UTF-8'}|{$task.lang|escape:'htmlall':'UTF-8'}|{$task.frequency|escape:'htmlall':'UTF-8'}|{$task.url|escape:'quotes':'UTF-8'}!
{/foreach}
{foreach from=$cron.orders.canceled item=task}{$task.title|escape:'htmlall':'UTF-8'}|{$task.lang|escape:'htmlall':'UTF-8'}|{$task.frequency|escape:'htmlall':'UTF-8'}|{$task.url|escape:'quotes':'UTF-8'}!
{/foreach}
{foreach from=$cron.orders.report item=task}{$task.title|escape:'htmlall':'UTF-8'}|{$task.lang|escape:'htmlall':'UTF-8'}|{$task.frequency|escape:'htmlall':'UTF-8'}|{$task.url|escape:'quotes':'UTF-8'}!
{/foreach}
{/if}
{foreach from=$cron.fba.status item=task}{$task.title|escape:'htmlall':'UTF-8'}|{$task.lang|escape:'htmlall':'UTF-8'}|{$task.frequency|escape:'htmlall':'UTF-8'}|{$task.url|escape:'quotes':'UTF-8'}!
{/foreach}
{foreach from=$cron.fba.stocks item=task}{$task.title|escape:'htmlall':'UTF-8'}|{$task.lang|escape:'htmlall':'UTF-8'}|{$task.frequency|escape:'htmlall':'UTF-8'}|{$task.url|escape:'quotes':'UTF-8'}!
{/foreach}
{foreach from=$cron.products.synch item=task}{$task.title|escape:'htmlall':'UTF-8'}|{$task.lang|escape:'htmlall':'UTF-8'}|{$task.frequency|escape:'htmlall':'UTF-8'}|{$task.url|escape:'quotes':'UTF-8'}!
{/foreach}
{foreach from=$cron.repricing.reprice item=task}{$task.title|escape:'htmlall':'UTF-8'}|{$task.lang|escape:'htmlall':'UTF-8'}|{$task.frequency|escape:'htmlall':'UTF-8'}|{$task.url|escape:'quotes':'UTF-8'}!
{/foreach}
{foreach from=$cron.repricing.update item=task}{$task.title|escape:'htmlall':'UTF-8'}|{$task.lang|escape:'htmlall':'UTF-8'}|{$task.frequency|escape:'htmlall':'UTF-8'}|{$task.url|escape:'quotes':'UTF-8'}!
{/foreach}
{foreach from=$cron.repricing.export item=task}{$task.title|escape:'htmlall':'UTF-8'}|{$task.lang|escape:'htmlall':'UTF-8'}|{$task.frequency|escape:'htmlall':'UTF-8'}|{$task.url|escape:'quotes':'UTF-8'}!
{/foreach}
{foreach from=$cron.messaging.grab item=task}{$task.title|escape:'htmlall':'UTF-8'}|{$task.lang|escape:'htmlall':'UTF-8'}|{$task.frequency|escape:'htmlall':'UTF-8'}|{$task.url|escape:'quotes':'UTF-8'}!
{/foreach}
</textarea>
                    <br/>
                    {if $cron.prestashop.installed}
                        <span style="color:green">{l s='Click on install/update button to auto-configure your Prestashop cronjobs module' mod='amazon'}
                            :</span>
                        <button class="button btn btn-default" style="float:right" id="install-cronjobs"><img
                                    src="{$cron.images_url|escape:'quotes':'UTF-8'}plus.png"
                                    alt=""/>&nbsp;&nbsp; {l s='Install/Update' mod='amazon'}</button>
                        <img src="{$cron.images_url|escape:'quotes':'UTF-8'}loader-connection.gif" alt=""
                             id="cronjob-loader"/>
                    {/if}
                {/if}
            </div>
        </div>
    </div>


    <div class="form-group">
        <div class="margin-form col-lg-offset-3">
            <div class="cron-mode" rel="manual-cron">
                <img src="{$cron.images_url|escape:'quotes':'UTF-8'}/terminal.png"
                     title="{l s='Manual Cron URLs' mod='amazon'}"/>
                <h4>{l s='Manual Cron URLs' mod='amazon'}</h4>
            </div>
        </div>
    </div>

    <div id="manual-cron" class="cron-toggle" {if $cron.prestashop.installed}style="display:none"{/if}>
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Synchronize' mod='amazon'}</label>
            {if $cron.products.synch}
                <div class="margin-form col-lg-9">
                    <p class="cron-title">{l s='URL to synchronize products to be used to configure your crontab.' mod='amazon'}</p>
                    {foreach from=$cron.products.synch item=synch}
                        <img src="{$synch.flag|escape:'htmlall':'UTF-8'}" alt="{$synch.lang|escape:'htmlall':'UTF-8'}"
                             class="cron-img"/>
                        &nbsp;&nbsp;
                        <input type="text" class="cron-url" value="{$synch.url|escape:'htmlall':'UTF-8'}" readonly/>
                        <br/>
                    {/foreach}
                </div>
            {else}
                <div class="margin-form col-lg-9">
                    <p style="color:red;font-size: 1.2em;">{l s='Your module is not configured yet.' mod='amazon'}</p>
                </div>
            {/if}
        </div>

        <hr class="amz-separator" style="width:30%"/>
        {if isset($cron.orders)}
        <div rel="amazon-orders" class="amazon-orders">
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Orders' mod='amazon'}</label>
                {if $cron.orders.import}
                    <div class="margin-form col-lg-9">
                        <p class="cron-title">{l s='URL to import orders to be used to configure your crontab.' mod='amazon'}</p>
                        {foreach from=$cron.orders.import item=import}
                            <img src="{$import.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$import.lang|escape:'htmlall':'UTF-8'}" class="cron-img"/>
                            &nbsp;&nbsp;
                            <input type="text" class="cron-url" value="{$import.url|escape:'htmlall':'UTF-8'}"
                                   readonly/>
                            <br/>
                        {/foreach}
                    </div>
                {else}
                    <div class="margin-form col-lg-9">
                        <p style="color:red;font-size: 1.2em;">{l s='Your module is not configured yet.' mod='amazon'}</p>
                    </div>
                {/if}
            </div>


            {if $cron.orders.status}
                <div class="form-group">
                    <div class="margin-form col-lg-offset-3  col-lg-9">
                        <p class="cron-title">{l s='URL to send statuses to be used to configure your crontab.' mod='amazon'}</p>
                        {foreach from=$cron.orders.status item=status}
                            <img src="{$status.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$status.lang|escape:'htmlall':'UTF-8'}" class="cron-img"/>
                            &nbsp;&nbsp;
                            <input type="text" class="cron-url" value="{$status.url|escape:'htmlall':'UTF-8'}"
                                   readonly/>
                            <br/>
                        {/foreach}
                    </div>
                </div>
            {/if}

            {if $cron.orders.canceled}
                <div class="form-group">
                    <div class="margin-form col-lg-offset-3  col-lg-9">
                        <p class="cron-title">{l s='URL to manage canceled order to be used to configure your crontab.' mod='amazon'}</p>
                        {foreach from=$cron.orders.canceled item=status}
                            <img src="{$status.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$status.lang|escape:'htmlall':'UTF-8'}" class="cron-img"/>
                            &nbsp;&nbsp;
                            <input type="text" class="cron-url" value="{$status.url|escape:'htmlall':'UTF-8'}"
                                   readonly/>
                            <br/>
                        {/foreach}
                    </div>
                </div>
            {/if}

            {if isset($cron.orders.report) && $cron.orders.report}
                <div class="form-group">
                    <div class="margin-form col-lg-offset-3  col-lg-9">
                        <p class="cron-title">{l s='URL to import orders reports' mod='amazon'}</p>
                        {foreach from=$cron.orders.report item=report}
                            <img src="{$report.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$report.lang|escape:'htmlall':'UTF-8'}" class="cron-img"/>
                            &nbsp;&nbsp;
                            <input type="text" class="cron-url" value="{$report.url|escape:'htmlall':'UTF-8'}"
                                   readonly/>
                            <br/>
                        {/foreach}
                    </div>
                </div>
            {/if}
        </div>
        {/if}

        <div rel="amazon-fba" class="amazon-fba">
            {if (is_array($cron.fba.status) && count($cron.fba.status)) || (is_array($cron.fba.stocks) && count($cron.fba.stocks))}
                <hr class="amz-separator" style="width:30%"/>
                <label class="control-label col-lg-3">{l s='FBA' mod='amazon'}</label>
            {/if}

            {if $cron.fba.status}
                <div class="form-group">
                    <div class="margin-form col-lg-offset-3">
                        <p class="cron-title">{l s='URL used to update Multi-Channel FBA orders status.' mod='amazon'}</p>
                        {foreach from=$cron.fba.status item=status}
                            <img src="{$status.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$status.lang|escape:'htmlall':'UTF-8'}" class="cron-img"/>
                            &nbsp;&nbsp;
                            <input type="text" class="cron-url" value="{$status.url|escape:'htmlall':'UTF-8'}"
                                   readonly/>
                            <br/>
                        {/foreach}
                    </div>
                </div>
            {/if}

            {if $cron.fba.stocks}
                <div class="form-group">
                    <div class="margin-form col-lg-offset-3">
                        <p class="cron-title">{l s='URL used to automate your FBA inventory.' mod='amazon'}</p>
                        {foreach from=$cron.fba.stocks item=stocks}
                            <img src="{$stocks.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$stocks.lang|escape:'htmlall':'UTF-8'}" class="cron-img"/>
                            &nbsp;&nbsp;
                            <input type="text" class="cron-url" value="{$stocks.url|escape:'htmlall':'UTF-8'}"
                                   readonly/>
                            <br/>
                        {/foreach}
                    </div>
                </div>
            {/if}
        </div>

        <div rel="amazon-repricing">
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Repricing' mod='amazon'}</label>
                {if $cron.repricing.reprice}
                    <div class="margin-form col-lg-9">
                        <p class="cron-title">{l s='URL used to automate offers repricing, price calculation.' mod='amazon'}</p>
                        {foreach from=$cron.repricing.reprice item=repricing}
                            <img src="{$repricing.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$repricing.lang|escape:'htmlall':'UTF-8'}" class="cron-img"/>
                            &nbsp;&nbsp;
                            <input type="text" class="cron-url" value="{$repricing.url|escape:'htmlall':'UTF-8'}"
                                   readonly/>
                            <br/>
                        {/foreach}
                    </div>
                    <div class="margin-form col-lg-9 col-lg-offset-3">
                        <p class="cron-title">{l s='URL used to push offers updates to the queue.' mod='amazon'}</p>
                        {foreach from=$cron.repricing.update item=repricing}
                            <img src="{$repricing.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$repricing.lang|escape:'htmlall':'UTF-8'}" class="cron-img"/>
                            &nbsp;&nbsp;
                            <input type="text" class="cron-url" value="{$repricing.url|escape:'htmlall':'UTF-8'}"
                                   readonly/>
                            <br/>
                        {/foreach}
                    </div>
                    <div class="margin-form col-lg-9 col-lg-offset-3">
                        <p class="cron-title">{l s='URL used to send the repricing feed.' mod='amazon'}</p>
                        {foreach from=$cron.repricing.export item=repricing}
                            <img src="{$repricing.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$repricing.lang|escape:'htmlall':'UTF-8'}" class="cron-img"/>
                            &nbsp;&nbsp;
                            <input type="text" class="cron-url" value="{$repricing.url|escape:'htmlall':'UTF-8'}"
                                   readonly/>
                            <br/>
                        {/foreach}
                    </div>
                {else}
                    <div class="margin-form col-lg-9">
                        <p style="color:red;font-size: 1.2em;">{l s='Your module is not configured yet.' mod='amazon'}</p>
                    </div>
                {/if}
            </div>
        </div>

        {if isset($cron.products.fix)}
        <div rel="amazon-expert-mode" class="amazon-expert-mode">
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Stock Discrepencies Fix' mod='amazon'}</label>
                {if $cron.products.fetch}
                    <div class="margin-form col-lg-9">
                        <p class="cron-title">{l s='URL used to retrieve the real stocks levels from Amazon.' mod='amazon'}</p>
                        {foreach from=$cron.products.fetch item=fetch}
                            <img src="{$fetch.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$fetch.lang|escape:'htmlall':'UTF-8'}" class="cron-img"/>
                            &nbsp;&nbsp;
                            <input type="text" class="cron-url" value="{$fetch.url|escape:'htmlall':'UTF-8'}"
                                   readonly/>
                            <br/>
                        {/foreach}
                    </div>
                    <div class="margin-form col-lg-9 col-lg-offset-3">
                        <p class="cron-title">{l s='URL used to parse and fix the discrepencies, this script has to be run 15 minutes after.' mod='amazon'}</p>
                        {foreach from=$cron.products.fix item=fix}
                            <img src="{$fix.flag|escape:'htmlall':'UTF-8'}"
                                 alt="{$fix.lang|escape:'htmlall':'UTF-8'}" class="cron-img"/>
                            &nbsp;&nbsp;
                            <input type="text" class="cron-url" value="{$fix.url|escape:'htmlall':'UTF-8'}"
                                   readonly/>
                            <br/>
                        {/foreach}
                    </div>
                {else}
                    <div class="margin-form col-lg-9">
                        <p style="color:red;font-size: 1.2em;">{l s='Your module is not configured yet.' mod='amazon'}</p>
                    </div>
                {/if}
            </div>
        </div>
        {/if}

    </div>
</div><!-- menudiv-cron -->