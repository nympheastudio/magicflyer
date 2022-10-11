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

{literal}
<script type="text/javascript">

    var lecabflash_express     = {/literal}{if $express_available}true{else}false{/if}{literal};

    var lecabflash_ajax_url       = "{/literal}{$modulePath|escape:'htmlall':'UTF-8'}{literal}ajax.php?lecabflash_token={/literal}{$ajax_token|escape:'htmlall':'UTF-8'}{literal}";
    var lecabflash_carrier_id     = "{/literal}{$lecabflash_carrier_id|escape:'htmlall':'UTF-8'}{literal}";

    var lecabflash_price_real     = "{/literal}{$lecabflash_price_real|escape:'htmlall':'UTF-8'}{literal}";  
    var lecabflash_price_express  = "{/literal}{$price|escape:'htmlall':'UTF-8'}{literal}";
    
    // error messages
    var lecabflash_error_api_down = "{/literal}{l s='Reservation service is actually unavailable. Please retry later.' mod='lecabflash'}{literal}";
    var lecabflash_error_no_crs = "{/literal}{l s='Reservation service is actually unavailable. Please retry later.' mod='lecabflash'}{literal}";
    var lecabflash_update_schedule = "{/literal}{l s='Update schedule' mod='lecabflash'}{literal}";

    var lecabflash_spinner_url    = "{/literal}{$modulePath|escape:'htmlall':'UTF-8'}{literal}views/img/spinner.gif";

    var todayAt = "{/literal}{l s='Today' mod='lecabflash'} {l s='at' mod='lecabflash'} {literal}";

    var monthNames = [
        "{/literal}{l s='January' mod='lecabflash'}{literal}",
        "{/literal}{l s='February' mod='lecabflash'}{literal}",
        "{/literal}{l s='March' mod='lecabflash'}{literal}",
        "{/literal}{l s='April' mod='lecabflash'}{literal}",
        "{/literal}{l s='May' mod='lecabflash'}{literal}",
        "{/literal}{l s='June' mod='lecabflash'}{literal}",
        "{/literal}{l s='July' mod='lecabflash'}{literal}",
        "{/literal}{l s='August' mod='lecabflash'}{literal}",
        "{/literal}{l s='September' mod='lecabflash'}{literal}",
        "{/literal}{l s='October' mod='lecabflash'}{literal}",
        "{/literal}{l s='November' mod='lecabflash'}{literal}",
        "{/literal}{l s='December' mod='lecabflash'}{literal}"
    ];

    var dayNames = [
        "{/literal}{l s='Sunday' mod='lecabflash'}{literal}",
        "{/literal}{l s='Monday' mod='lecabflash'}{literal}",
        "{/literal}{l s='Tuesday' mod='lecabflash'}{literal}",
        "{/literal}{l s='Wednesday' mod='lecabflash'}{literal}",
        "{/literal}{l s='Thursday' mod='lecabflash'}{literal}",
        "{/literal}{l s='Friday' mod='lecabflash'}{literal}",
        "{/literal}{l s='Saturday' mod='lecabflash'}{literal}"
    ];

</script>
{/literal}

<div id="lecabflashCarrierInfo">
    <div>
        {if $express_available}
            {* switch buttons *}
            {if !isset($error_address['address'])}
                <div id="lecabflash_switch" class="mt5">
                    <button id="lecabflash_switch_express" class="active">{l s='Immediate Fare' mod='lecabflash'}</button>
                    <button id="lecabflash_switch_rdv">{l s='Date booking' mod='lecabflash'}</button>
                </div>
            {/if}

            {* tab livraison immédiate *}
            <div id="lecabflash_express">
                {if !isset($error_address['address'])}
                    <div id="lecabflash_express_pickuptime" class="mb15" style="display:none;">
                        <p>{l s='Shipping schedule : ' mod='lecabflash'}<span></span></p>
                    </div>
                {/if}

                {if isset($error_address['address'])}
                    <div id="lecabflash_adr_error" class="mt15 mb15 ">
                        <p>
                            {l s='Our service cannot determine your address.' mod='lecabflash'}</br>
                            <b>{l s='Please choose which one is yours.' mod='lecabflash'}</b></br>                            
                        </p>
                    </div>
                    <ul id="list_new_address" class="mb15">
                        {foreach from=$error_address['address'] item=address}
                            <li name="{$error_address['cart_id']|escape:'htmlall':'UTF-8'}">{$address|escape:'htmlall':'UTF-8'}</li>
                        {/foreach}
                    </ul>
                    <p class="mt5 lecabflash_ital">
                        {l s='If your address contains "bis" or "ter", please indicate in the notes to the courier and select the equivalent address without "bis" or "ter".' mod='lecabflash'}
                    </p>
                {else}
                    <div class="mt15 mb15">
                        <p class="mb5">
                            {l s='Give informations about your address to our courier.' mod='lecabflash'}
                        </p>
                        <textarea name="lecabflash_pickup_address_info" id="lecabflash_pickup_address_info" class="lecabflash_textarea textarea-autosize textarea-y3" placeholder="{l s='access, code,…' mod='lecabflash'}">{$lecabflash_pickup_address_info|escape:'htmlall':'UTF-8'}</textarea>
                    </div>
                {/if}
            </div>
        {/if}

        {* tab prise de rendez-vous *}
        <div id="lecabflash_rdv" style="{if $express_available}display: none;{/if}">
            {if !$express_available}
                <p><b>{l s='Book a date' mod='lecabflash'}</b></p>
            {/if}

            {* success message *}
            <div id="lecabflash_rdv_propal">
                <p class="mb5">{l s='Congratulations, your rendezvous is booked.' mod='lecabflash'}</p>
                <p><b>{l s='Delivery schedule : ' mod='lecabflash'}</b><span id="lecabflash_rdv_hours"></span></p>
            </div>

            {if isset($error_address['address'])}
                <div id="lecabflash_adr_error" class="mt15 mb15 ">
                    <p>
                        {l s='Our service cannot determine your address.' mod='lecabflash'}</br>
                        <b>{l s='Please choose which one is yours.' mod='lecabflash'}</b>
                    </p>
                </div>
                <ul id="list_new_address" class="mb15">
                    {foreach from=$error_address['address'] item=address}
                        <li data-token="{$ajax_token|escape:'htmlall':'UTF-8'}" name="{$error_address['cart_id']|escape:'htmlall':'UTF-8'}">{$address|escape:'htmlall':'UTF-8'}</li>
                    {/foreach}
                </ul>
            {else}
                <div class="mt15 mb15">
                    <p class="mb5">
                        {l s='Give informations about your address to our courier.' mod='lecabflash'}
                    </p>
                    <textarea name="lecabflash_pickup_address_info" id="lecabflash_pickup_address_info" class="lecabflash_textarea textarea-autosize textarea-y3" placeholder="{l s='codes, accès, ...' mod='lecabflash'}">{$lecabflash_pickup_address_info|escape:'htmlall':'UTF-8'}</textarea>
                </div>
            {/if}
             <div class="mt15">
                {if !isset($error_address['address'])}
                    <a class="lecabflash_open_modal lecabflash_green_btn btn btn-sm btn-default">{l s='Choose a schedule' mod='lecabflash'}</a>
                {/if}
            </div>
        </div>

        {* error message *}
        <div id="lecabflash_rdv_error">
            <p><b>Erreur : </b><span id="lecabflash_rdv_error_reason"></span></p>
        </div>

        {if $debug}
            <p class="mt15"><a href="#showdebuglacab" data-toggle="collapse">DEBUG</a></p>
            <div id="showdebuglacab" class="collapse">
                <p><a href="{$modulePath|escape:'htmlall':'UTF-8'}ajax.php?action=logs">LOGS</a></p>
                <h6>estimate_context</h6>
                <code>{$debug.estimate_context|escape:'htmlall':'UTF-8'}</code>
                <h6>estimate_response</h6>
                <code>{$debug.estimate_response|escape:'htmlall':'UTF-8'}</code>
                <h6>confirm_request</h6>
                <code>{$debug.confirm_request|escape:'htmlall':'UTF-8'}</code>
                <h6>confirm_response</h6>
                <code>{$debug.confirm_response|escape:'htmlall':'UTF-8'}</code>
            </div>
        {/if}
    </div>

    {* modal horaires *}
    <div id="lecabflash_modal" class="modal fade" role="dialog" aria-labelledby="lecabflash_modal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{l s='Book a date' mod='lecabflash'}</h4>
                </div>
                <div class="modal-body">

                    {l s='Please choose your date and hour of delivery.' mod='lecabflash'}

                    <div>
                        <div class="form-group">
                            <div>
                                <label for="datetimepicker1_input">{l s='date' mod='lecabflash'}</label>
                                <div class='input-group date input-append' id='datetimepicker1'>
                                    <input type='text' class="form-control" id="datetimepicker1_input" value="{$date_default_rdv|date_format:'%d-%m-%Y'|escape:'htmlall':'UTF-8'}"/>
                                    <span class="input-group-addon add-on">
                                        <span class="icon-calendar"></span>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label for="datetimepicker2_input">{l s='hour' mod='lecabflash'}</label>
                                <div class='input-group date input-append' id='datetimepicker2'>
                                    <input type='text' class="form-control" id="datetimepicker2_input" value="{$date_default_rdv|date_format:'%H:%M'|escape:'htmlall':'UTF-8'}"/>
                                    <span class="input-group-addon add-on">
                                        <span class="icon-time"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {literal}
                    <script type="text/javascript">
                        $(function () {
                            $('#datetimepicker1').datetimepicker({
                                format: 'DD-MM-YYYY',
                                minDate: moment().millisecond(0).second(0).minute(0).hour(0),
                                allowInputToggle: true,
                                //debug: true
                            });
                            $('#datetimepicker2').datetimepicker({
                                format: 'HH:mm',
                                minDate: moment().add(3, 'm'),
                                allowInputToggle: true,
                                // debug: true
                            });

                            $("#datetimepicker1").on("dp.change", function (e) {
                                var iscurrentDate = e.date.isSame(new Date(), "day");
                                if(iscurrentDate) {
                                    $('#datetimepicker2').data("DateTimePicker").minDate(moment());
                                } else {
                                    $('#datetimepicker2').data("DateTimePicker").minDate(false);
                                }
                            });
                        });
                    </script>
                    {/literal}

                </div>
                <div class="modal-footer">
                    <button id="lecabflash_validate_pickup" type="button" class="lecabflash_green_btn btn btn-default" {* data-dismiss="modal" *}>{l s='Validate' mod='lecabflash'}</button>
                </div>
            </div>
        </div>
    </div>
</div>
