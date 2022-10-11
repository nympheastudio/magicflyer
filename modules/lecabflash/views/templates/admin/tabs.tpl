{*
 *
 * 2009-2017 202 ecommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    202 ecommerce <support@202-ecommerce.com>
 *  @copyright 2009-2017 202 ecommerce SARL
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 *}

{assign var="selected" value=Tools::getValue('tab','global')}

{literal}
<script language="javascript">
    var lecabflash_ajax_url = "{/literal}{$modulePath|escape:'htmlall':'UTF-8'}{literal}ajax.php";
    var wizzard_isActive = "{/literal}{$active|escape:'htmlall':'UTF-8'}{literal}";

    // Set wizzard buttons
    var btns = "<button class='button wizzard_btn wizzard_btn_prev'><i class='icon-arrow-left'></i>{/literal}{l s='Back' mod='lecabflash'}{literal}</button>";
       btns += "<button class='button wizzard_btn wizzard_btn_next'>{/literal}{l s='Next' mod='lecabflash'}{literal}<i class='icon-arrow-right'></i></button>";
       btns += "<button class='button wizzard_btn wizzard_btn_end' style='display: none;'>{/literal}{l s='Finish' mod='lecabflash'}{literal}<i class='icon-check'></i></button>";

</script>
{/literal}

<div class="lecab__main-container">
    <div class="lecab__left-col">
        <div class="lecab__logo">
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-lecab.png" alt="{l s='LeCab' mod='lecabflash'}">
        </div>
        <ul class="lecab__nav">
            <li>
                <a class="active" href="#" data-target="tab_conf">
                    <i class="icon-cog"></i>
                    {l s='Configuration' mod='lecabflash'}
                </a>
            </li>
{*             <li><a href="#">{l s='Advanced settings' mod='lecabflash'}</a></li>
            <li><a href="#">{l s='Journal API' mod='lecabflash'}</a></li>
            <li><a href="#">{l s='Cart' mod='lecabflash'}</a></li> *}
            <li>
                <a href="#" data-target="tab_help">
                    <i class="icon-question"></i>
                    {l s='Help' mod='lecabflash'}
                </a>
            </li>
            <li>
                <a href="#" data-target="tab_contact">
                    <i class="icon-comment"></i>
                    {l s='Contact' mod='lecabflash'}
                </a>
            </li>
        </ul>
        <span class="lecab__about">
            {l s='LeCabFlash carrier will be' mod='lecabflash'}<b> {l s='created and accessible for your customers once you have filled and validated the 4 configuration steps.' mod='lecabflash'}</b> {l s='If there is no dimensions filled in your products, LeCabFlash will send a default value (15x30x30cm)' mod='lecabflash'}</br>
            {l s='If the dimension of your product is not valid and if the courier have to refuse the fare, you‘ll be charged of 3,99€ for his venue.' mod='lecabflash'}
        </span>
    </div>

    <div class="lecab__right-col">
        <div id="tab_conf">
          <ul class="nav nav-tabs" role="tablist" id="nav-container">
    {*         <li role="presentation" class="active"><a href="#lecabflash_tab_main" role="tab" data-toggle="tab">{l s='Parametres généraux' mod='lecabflash'}</a></li>
            {if $active}    
                <li role="presentation"><a href="#lecabflash_tab_advanced" role="tab" data-toggle="tab">{l s='Parametres avancés' mod='lecabflash'}</a></li>
                <!-- <li role="presentation"><a href="#lecabflash_tab_journal"  role="tab" data-toggle="tab">{l s='Journal' mod='lecabflash'}</a></li> -->
                <li role="presentation"><a href="#lecabflash_tab_debug"  role="tab" data-toggle="tab">{l s='DEBUG' mod='lecabflash'}</a></li>
            {/if} *}

            <li role="presentation" class="active">
                <a href="#lecabflash_tab_addr" role="tab" data-toggle="tab" data-step="0">
                    {l s='Address' mod='lecabflash'}
                </a>
            </li>
            <li role="presentation">
                <a href="#lecabflash_tab_key" role="tab" data-toggle="tab" data-step="1">
                    {l s='API Key' mod='lecabflash'}
                </a>
            </li>
            <li role="presentation">
                <a href="#lecabflash_tab_hours" role="tab" data-toggle="tab" data-step="2">
                    {l s='Schedule' mod='lecabflash'}
                </a>
            </li>
            <li role="presentation">
                <a href="#lecabflash_tab_prices" role="tab" data-toggle="tab" data-step="3">
                    {l s='Price' mod='lecabflash'}
                </a>
            </li>
            <!-- <li role="presentation"><a href="#lecabflash_tab_journal"  role="tab" data-toggle="tab">{l s='Journal' mod='lecabflash'}</a></li> -->
            {* <li role="presentation"><a href="#lecabflash_tab_debug"  role="tab" data-toggle="tab">{l s='DEBUG' mod='lecabflash'}</a></li> *}

          </ul>


          <div class="tab-content" id="tab-container">
            <div role="tabpanel" class="tab-pane active" id="lecabflash_tab_addr" data-step="0" data-action="updateSettingsAddr">
                {$html_tab_addr}
            </div>
            <div role="tabpanel" class="tab-pane" id="lecabflash_tab_key" data-step="1" data-action="updateSettingsKey">
                {$html_tab_key}
            </div>
            <div role="tabpanel" class="tab-pane" id="lecabflash_tab_hours" data-step="2" data-action="updateSettingsHours">
                {$html_tab_hours}
            </div>
            <div role="tabpanel" class="tab-pane" id="lecabflash_tab_prices" data-step="3" data-action="updateSettingsPrices">
                {$html_tab_prices}
            </div>
            {* <div role="tabpanel" class="tab-pane" id="lecabflash_tab_journal">{$html_tab_journal}</div> *}
            {* <div role="tabpanel" class="tab-pane" id="lecabflash_tab_debug">{$html_tab_debug}</div> *}
          </div>
      </div>

        <div class="tab-content" id="tab_help" style="display: none;">
            <div class="panel">
                <div class="panel-heading">
                    {l s='Help' mod='lecabflash'}
                </div>
                  <ul class="list-group">
                    <li class="list-group-item">
                        <span class="h4">{l s='First registration' mod='lecabflash'}</span>
                        <p>{l s='To use LeCabFlash carrier, you have to  create an account on our platform ' mod='lecabflash'}<a href="https://www.lecab.fr/flashforprestashop.html" target="_blank">{l s='(link)' mod='lecabflash'}</a>{l s='. Then you have to copy your API key, and go throught the 3 following steps :' mod='lecabflash'}</br>
                        {l s='1. Insert your depot address' mod='lecabflash'}</br>
                        {l s='2. Insert your depot schedule' mod='lecabflash'}</br>
                        {l s='3. Choose your charge policy' mod='lecabflash'}</p>
                    </li>
                    <li class="list-group-item">
                        <span class="h4">{l s='Immediate Fare vs Book a date' mod='lecabflash'}</span>
                        <p>{l s='Two carriers modes are available : immdiate fare and date booking. Immediate fare check the avaibility of a courier to deliver the product right away. Booking a fare allow customers to choose a date and a schedule for the delivery.' mod='lecabflash'}</p>
                    </li>
                    <li class="list-group-item">
                        <span class="h4">{l s='Eligibility' mod='lecabflash'}</span>
                        <p>{l s='Immediate fare is available if :' mod='lecabflash'}</br>
                        {l s='- Your depot is open' mod='lecabflash'}</br>
                        {l s='- Customer address is available to fares' mod='lecabflash'}</br>
                        {l s='- Dimensions and weight are valid.' mod='lecabflash'}</br>
                        {l s='- A courier is valid' mod='lecabflash'}</p>
                        <p>{l s='You can choose to book a date instead of immediate fare. If you depot is closed or if no couriers are available, booking a date is shown by default. If the customer address or the package is not valid, the carrier is not available.' mod='lecabflash'}</p>
                    </li>
                    <li class="list-group-item">
                        <span class="h4">{l s='Package dimensions' mod='lecabflash'}</span>
                        <p>{l s='If the package to send has no dimension, the dimensions by default will be sent. If your package don‘t have the same dimensions, you can be charged for it.' mod='lecabflash'}</br>
                        </br>
                        {l s='If you do not specify the dimensions of your package for delivery, the default size will be: (default dimensions). Furthermore, if the package does not have the same dimensions as indicated, a rate increase will be applied. Finally if your package is too large for our courier, it may refuse taking charge and be entitled to claim travel allowances.' mod='lecabflash'}</p>
                    </li>
                    <li class="list-group-item">
                        <span class="h4">{l s='Schedule' mod='lecabflash'}</span>
                        <p>{l s='To avoid the venue of a courier when you are closed, please prevent on your schedule a little time for opening and closing your depot. If you refuse a coursier while  your shcedule says you are open, you will be charged.' mod='lecabflash'}</p>
                    </li>
                    <li class="list-group-item">
                        <span class="h4">{l s='Price' mod='lecabflash'}</span>
                        <p>{l s='By default, we send the price of the fare, and it is this fare which will be charge for your customer delivery.' mod='lecabflash'}</br>
                        {l s='You can decide to show a different price for your customers, by using a percentage or a fix amount to add. You can also create your own price in the settings.' mod='lecabflash'}</p>
                    </li>
                    <li class="list-group-item">
                        <span class="h4">{l s='Chosing a courier' mod='lecabflash'}</span>
                        <p>{l s='If your products don‘t have any dimension, you can choose a courier by default which will be systematically sent. Here are the differents dimensions available :' mod='lecabflash'}</br>
                        {l s='Bicycle → 15x15x30cm &lt;10kg' mod='lecabflash'}</br>
                        {l s='Car → service opening soon' mod='lecabflash'}</br>
                        {l s='Van → service opening soon' mod='lecabflash'}</p>
                    </li>
                    <li class="list-group-item">
                        <span class="h4">{l s='Order paid but fare not validate' mod='lecabflash'}</span>
                        <p>{l s='It is possible that we don‘t validate the rate once the paiement is valid. If so, you‘ll receive an error mail so you an contact your customer. No charge is sent.' mod='lecabflash'}</p>
                    </li>
                    <li class="list-group-item">
                        <span class="h4">{l s='Cron updating' mod='lecabflash'}</span>
                        <p>{l s='By default, cron update is done by the refresh rate of your website. If you are a power-user, you can choose to change it throught a cron updating.' mod='lecabflash'}</p>
                    </li>
                    <li class="list-group-item">
                        <span class="h4">{l s='OPC' mod='lecabflash'}</span>
                        <p>{l s='The module is compatible with OnePageCheckout' mod='lecabflash'}</p>
                    </li>
                    <li class="list-group-item">
                        <span class="h4">{l s='Sandbox' mod='lecabflash'}</span>
                        <p>{l s='If you are a developper, you can use a API test key in the module configuration.' mod='lecabflash'}</p>
                    </li>
                  </ul>
            </div>
        </div>

        <div class="tab-content" id="tab_contact" style="display: none;">
            <div class="panel">
                <div class="panel-heading">
                    {l s='Contact' mod='lecabflash'}
                </div>
                {l s='To contact LeCabFLASH :' mod='lecabflash'}</br>
                <a href="https://addons.prestashop.com/fr/contactez-nous?id_product=24670">{l s='click here' mod='lecabflash'}</a>
            </div>
        </div>

        <div id="lecab__clear-cache" class="alert alert-danger" style="margin-top: -70px; opacity: 0; transition: opacity, .5s,linear;">
            <p>{l s='To ensure LeCabFlash module to work properly, please make sure to clear caches of your shop. To do that, go inAdvanced Settings > Performances > Clear Caches' mod='lecabflash'}</p>
            <a href="index.php?tab=AdminPerformance&token={$token_admin_perf|escape:'htmlall':'UTF-8'}">{l s='Go to the performance page' mod='lecabflash'}</a>
        </div>

    </div>
</div>

<div id="wizzard_success" class="modal fade" tabindex="-1">
    <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">{l s='Settings complete' mod='lecabflash'}</h4>
      </div>
      <div class="modal-body">
        <img class="responsive" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-lecab.png" alt="{l s='LeCab' mod='lecabflash'}">
        {l s='Congratulations, you have complete the module settings. It is now available for your customers if they are eligible to the service (Paris and inner suburbs).' mod='lecabflash'}
        <div class="alert alert-danger" style="margin-top: 32px;">
            <p>{l s='To ensure LeCabFlash module to work properly, please make sure to clear caches of your shop. To do that, go in Advanced Settings > Performances > Clear Caches' mod='lecabflash'}</p>
            <a href="index.php?tab=AdminPerformance&token={$token_admin_perf|escape:'htmlall':'UTF-8'}">{l s='Go to the performance page' mod='lecabflash'}</a>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='lecabflash'}</button>
      </div>
    </div>
  </div>
</div>

<div id="wizzard_error" class="modal fade" tabindex="-1">
    <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">{l s='Settings error' mod='lecabflash'}</h4>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='lecabflash'}</button>
      </div>
    </div>
  </div>
</div>




