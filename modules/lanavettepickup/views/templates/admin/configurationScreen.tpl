{*
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2017 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}

{if !$is_PS16}
    {*     <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?libraries=places&sensor=true"></script> *}
    <script type="text/javascript"
            src="//maps.googleapis.com/maps/api/js?libraries=places&key={$google_maps_key|escape:'html':'UTF-8'}"></script>
    <script type="text/javascript"
            src="{$pickup_dir|escape:'html':'UTF-8'}views/js/vendor/markerwithlabel/markerwithlabel.min.js"></script>
    <script type="text/javascript"
            src="{$pickup_dir|escape:'html':'UTF-8'}views/js/vendor/infobox/infobox.min.js"></script>
{/if}
<div class="pickup_configuration_screen_content {if !$is_PS16}ps_15{/if}">

    <div class="bootstrap">
        <div class="row">
            <div class="col-lg-12">
                <!-- top menu -->
                <div class="row">
                    <ul class="pickup_configuration_screen_top_menu">
                        <li class="col" {if $mode_dev}style="background: black; padding: 0 8px; color: yellow; border-radius: 4px;
"{/if}>
                            <img class="menu_logo_lanavette"
                                 src="{$modules_dir|escape:'html':'UTF-8'}lanavettepickup/views/img/pickup-cube.jpg"
                                 alt="">
                            {if $mode_dev}MODE DEV{/if}
                        </li>
                        <li class="col">
                            <a href="#" tab="home" class="selected">
                                {l s='Homepage' mod='lanavettepickup'}
                            </a>
                        </li>
                        <li class="col">
                            <a href="#" tab="shipping">
                                {l s='Sender information' mod='lanavettepickup'}
                            </a>
                        </li>
                        <li class="col">
                            <a href="#" tab="advanced_parameters">
                                {l s='Advanced settings' mod='lanavettepickup'}
                            </a>
                        </li>
                        <li class="col">
                            <a href="#" tab="shipments">
                                {l s='Manage your order status' mod='lanavettepickup'}
                            </a>
                        </li>
                        {*<li class="col">*}
                            {*<a href="#" tab="contact_us">*}
                                {*{l s='Contact us' mod='lanavettepickup'}*}
                            {*</a>*}
                        {*</li>*}
                    </ul>
                </div>

                <!-- content -->
                <div class="row">
                    <div class="pickup_configuration_content">
                        <!-- home -->
                        <div class="pickup_configuration_content_page pickup_configuration_content_home "
                             tab="home">
                            <div class="panel home">
                                <img class="logo_lanavette"
                                     src="{$modules_dir|escape:'html':'UTF-8'}lanavettepickup/views/img/logo_lanavettep.png"
                                     alt="La Navette Pickup - Logo"/>
                                {strip}
                                    <p>
                                        {l s='La Navette Pickup is the new solution to send parcel between parcelshops ' mod='lanavettepickup'}
                                    </p>
                                    <p>
                                        {{l s='|a|https://lanavette-bo.pickup.fr/Resources/Upload/files/Technical_Specifications_WS_laNavettePickup.pdf|-a|Download our documentation|/a| to configure the module.' mod='lanavettepickup'}|escape:'html':'UTF-8'|replace:'|a|':'<a class="red" href="'|replace:'|-a|':'">'|replace:'|/a|':'</a>'}
                                    </p>
                                {/strip}
                                <div class="home__link-container">
                                    <a class="home__link-block home__link-block--left"
                                       href="mailto:{l s='client-prestashop@lanavette.zendesk.com' mod='lanavettepickup'}">{* @todo: link to pdf *}
                                        <h3>{l s='Become a customer' mod='lanavettepickup'}</h3>
                                        <p>{l s='Contact our Customer Service team: client-prestashop@lanavette.zendesk.com' mod='lanavettepickup'}</p>
                                    </a>
                                    <a class="home__link-block home__link-block--right" href="#" tab="shipping">
                                        <h3>{l s='I am already a customer' mod='lanavettepickup'}</h3>
                                        <p>{l s='Continue to setup the module' mod='lanavettepickup'}</p>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- shipping -->
                        <div class="pickup_configuration_content_page pickup_configuration_content_shipping pickup_hide"
                             tab="shipping">
                            {if isset($save_shipping) && $save_shipping}
                                <div class="alert alert-success">
                                    {l s='Your informations has been saved.' mod='lanavettepickup'}
                                </div>
                            {/if}

                            {$ws_form} {* Generation code html HelperOptions. escape impossible *}

                            {if $ws_ok}
                                <div class="panel">
                                    <p>{l s='To be able to offer the solution « La Navette Pickup », you have to set it up in the Shipping Menu:' mod='lanavettepickup'}
                                        <a class="red"
                                           href="index.php?controller=AdminCarrierWizard&token={getAdminToken tab='AdminCarrierWizard'}&id_carrier={$id_carrier|escape:'html':'UTF-8'}"
                                           target="_blank">{l s='Shipping > Carriers' mod='lanavettepickup'}</a>
                                    </p>
                                </div>
                                {$form} {* Generation code html HelperOptions. escape impossible *}
                                <div class="panel " id="configuration_fieldset_general">
                                    <div class="panel-heading">
                                        <i class="icon-cogs"></i>
                                        {l s='Your drop off parcelshop' mod='lanavettepickup'}
                                    </div>

                                    <div class="form-wrapper">
                                        <div class="form-group">
                                            {if !$is_PS16}
                                                <div class="optionsDescription">
                                                    {l s='Select the Parcelshop where you will drop off your parcels.' mod='lanavettepickup'}
                                                    <br/>
                                                    {l s='If the recipient does not collect the parcel, it will automatically be returned to the parcelshop where it was dropped off.' mod='lanavettepickup'}
                                                </div>
                                            {else}
                                                <div class="alert alert-info">
                                                    {l s='Select the Parcelshop where you will drop off your parcels.' mod='lanavettepickup'}</br>
                                                    {l s='If the recipient does not collect the parcel, it will automatically be returned to the parcelshop where it was dropped off.' mod='lanavettepickup'}
                                                </div>
                                            {/if}

                                            {include file='../hook/map.tpl'}

                                            <div style="clear:both"></div>
                                            <p class="save_pudo"
                                               style="display:none; text-align:right;">{l s='Click on save to reflect this change.' mod='lanavettepickup'}</p>
                                        </div>
                                    </div>
                                </div>
                                <button id="submitPickupConfig" name="submitPickupConfig"
                                        class="btn btn-default pull-right">
                                    <i class="process-icon-save"></i>
                                    {l s='Save' mod='lanavettepickup'}
                                </button>
                            {/if}

                            <span class="pickup_red">*</span> {l s='Mandatory fields' mod='lanavettepickup'}
                        </div>

                        <!-- advanced_parameters -->
                        <div class="pickup_configuration_content_page pickup_configuration_content_advanced_parameters pickup_hide"
                             tab="advanced_parameters">
                            {if isset($save_advanced_parameters) && $save_advanced_parameters}
                                <div class="alert alert-success">
                                    {l s='Your informations has been saved.' mod='lanavettepickup'}
                                </div>
                            {/if}
                            {$advanced_form} {* Generation code html HelperOptions. escape impossible *}
                            <div class="alert alert-info">
                                {l s='Please note that la Navette Pickup’s responsibility will not be engaged in case of insufficient packaging. The cost of the insurance is not reflected on the transportation cost. If you wish to have your customers bear the price of the insurance, you need to modify the fees, or to add it as a fixed fee in Handling Cost' mod='lanavettepickup'}.
                            </div>
                            <button id="submitPickupAdvancedParameters" name="submitPickupAdvancedParameters"
                                    class="btn btn-default pull-right">
                                <i class="process-icon-save"></i>
                                {l s='Save' mod='lanavettepickup'}
                            </button>
                        </div>

                        <!-- shipments -->
                        <div class="pickup_configuration_content_page pickup_configuration_content_shipments pickup_hide"
                             tab="shipments">
                            {if isset($save_shipments) && $save_shipments}
                                <div class="alert alert-success">
                                    {l s='Your informations has been saved.' mod='lanavettepickup'}
                                </div>
                            {/if}
                            {$states_form} {* Generation code html HelperOptions. escape impossible *}
                            <button id="submitPickupShipments" name="submitPickupShipments"
                                    class="btn btn-default pull-right">
                                <i class="process-icon-save"></i>
                                {l s='Save' mod='lanavettepickup'}
                            </button>
                        </div>

                        {*<!-- contact_us -->*}
                        {*<div class="pickup_configuration_content_page pickup_configuration_content_contact_us pickup_hide"*}
                             {*tab="contact_us">*}
                            {*<div class="panel">*}
                                {*<div class="panel-heading">*}
                                    {*<i class="icon-cogs"></i>*}
                                    {*{l s='Contact us' mod='lanavettepickup'}*}
                                {*</div>*}
                                {*<p>*}
                                    {*{l s='Please email our sales support team if you have any questions:' mod='lanavettepickup'}*}
                                {*</p>*}
                                {*<div>*}
                                    {*<ul>*}
                                        {*<li>*}
                                            {*{l s='Email : ' mod='lanavettepickup'}*}
                                            {*<a class="red"*}
                                               {*href="mailto:{l s='client-prestashop@lanavette.zendesk.com' mod='lanavettepickup'}">*}
                                                {*{l s='client-prestashop@lanavette.zendesk.com'  mod='lanavettepickup'}*}
                                            {*</a>*}
                                        {*</li>*}
                                    {*</ul>*}
                                {*</div>*}
                            {*</div>*}
                        {*</div>*}
                    </div>
                </div>
            </div>
        </div>

        <div style="clear: both"></div>
    </div>

    <script>
        var modules_dir = '{$modules_dir|escape:'html':'UTF-8'}'; {* smarty global var required for map.js *}

        var config_tab = '{$config_tab|escape:'html':'UTF-8'}';

        var ps_order_process_type = {if $ps_order_process_type}true{else}false{/if};

        var ids_filled = {if $ids_filled}'true'
        {else}'false'{/if};
        if (config_tab == '' && ids_filled) {
            config_tab = 'shipping';
        }

        var config_required_fields = new Array(
            'LNP2_NAME',
            'LNP2_MAIL',
            'LNP2_PHONE'
        );

        var config_ws_required_fields = new Array(
            'LNP2_NAVETTE_PARTNER_ID',
            'LNP2_NAVETTE_PARTNER_PWD'
        );

        var required_fields = $.merge(config_required_fields, config_ws_required_fields);

        $(document).ready(function () {

            // put a star for required fields
            $.each(required_fields, function (index, value) {
                var $field = $('input[name=' + value + ']');
                if (!$field.length) {
                    $field = $('input[id=' + value + ']');
                }
                var $label = $field.parent().parent().children('label');
                $label.html($.trim($label.html()) + '<span class="pickup_red">*</span>');
            });

            // change tab
            $('.pickup_configuration_screen_content a[tab]').click(function (e) {
                e.preventDefault();

                $('.pickup_configuration_screen_top_menu a').removeClass('selected');
                $('.pickup_configuration_screen_top_menu a[tab="' + $(this).attr('tab') + '"]').addClass('selected');

                $('.pickup_configuration_content_page').hide();
                $('.pickup_configuration_content_page[tab="' + $(this).attr('tab') + '"]').show();
            });

            // submit form
            $('#submitPickupConfig').click(function () {
                $('.has-error').removeClass('has-error');

                for (var i in config_required_fields) {
                    var field = config_required_fields[i];
                    var input_field = $('input[name=' + field + ']');

                    if (input_field.val() === '') {
                        $('#conf_id_' + field).addClass('has-error');
                        var field_caption = $('#conf_id_' + field).find('label').text().trim();
                        alert('{l s='|field| required' mod='lanavettepickup'}'.replace('|field|', field_caption));
                        return;
                    }
                }

                var can_parse = false;
                try {
                    JSON.parse($('input[name=LNP2_DROP_OFF_SITE]').val().replace(/\|/g, '"'));
                    can_parse = true;
                } catch (err) {

                }

                if (!can_parse) {
                    alert('{l s='You need to pick a pudo' mod='lanavettepickup'}');
                    return;
                }

                // trick to find form also under PS1.5
                $('input[name=LNP2_NAME]').parents('form').first().submit();
            });

            // submit form
            $('button[name=submitPickupWS]').click(function (e) {
                $('.has-error').removeClass('has-error');

                for (var i in config_ws_required_fields) {
                    var field = config_ws_required_fields[i];
                    var input_field = $('input[name=' + field + ']');

                    if (input_field.val() === '') {
                        $('#conf_id_' + field).addClass('has-error');
                        var field_caption = $('#conf_id_' + field).find('label').text().trim();
                        alert('{l s='|field| required' mod='lanavettepickup'}'.replace('|field|', field_caption));
                        e.preventDefault();
                        return;
                    }
                }
            });

            // submit form
            $('#submitPickupAdvancedParameters').click(function () {
                // trick to find form also under PS1.5
                $('#LNP2_LOGS').parents('form').first().submit();
            });

            // submit form
            $('#submitPickupShipments').click(function () {
                // trick to find form also under PS1.5
                $('#LNP2_SHIPPED_ORDER_STATE').parents('form').first().submit();
            });

            {if $pickup_drop_off_site}
            $('#pickup_map_search_input').val("{$pickup_drop_off_site.name|escape:'html':'UTF-8'} {$pickup_drop_off_site.address1|escape:'html':'UTF-8'} {$pickup_drop_off_site.zipCode|escape:'html':'UTF-8'} {$pickup_drop_off_site.city|escape:'html':'UTF-8'}");
            if ($('input[name=LNP2_DROP_OFF_SITE]').val()) {
                changePickupMapSearchIcon('change');
            } else {
                changePickupMapSearchIcon('cross');
            }
            selectedSite = {$pickup_drop_off_site|@json_encode nofilter};
            {/if}

            $('a[tab=' + (config_tab ? config_tab : 'home' ) + ']').trigger('click');

            if ($('input[name=LNP2_DROP_OFF_SITE]').val()) {
                $('#lnp2_map').slideUp();
            }

        });

        var pudo_lat = '{Configuration::get('LNP2_DROP_OFF_PUDO_LAT')|escape:'html':'UTF-8'}';
        var pudo_lng = '{Configuration::get('LNP2_DROP_OFF_PUDO_LNG')|escape:'html':'UTF-8'}';

        var map_is_drop_off = true;

        var security_token = '{$security_token|escape:'html':'UTF-8'}';

        var change_str = "{l s='Change' mod='lanavettepickup'}";
        var opening_hours_str = "{l s='Opening hours' mod='lanavettepickup'}";
        var closing_time_title_str = "{l s='Closing Period' mod='lanavettepickup'}";
        var closing_time_str = "{l s='Closed from |start| to |end|' mod='lanavettepickup'}";
        var day_of_week_str_ar = {
            '1': "{l s='Monday' mod='lanavettepickup'}",
            '2': "{l s='Tuesday' mod='lanavettepickup'}",
            '3': "{l s='Wednesday' mod='lanavettepickup'}",
            '4': "{l s='Thursday' mod='lanavettepickup'}",
            '5': "{l s='Friday' mod='lanavettepickup'}",
            '6': "{l s='Saturday' mod='lanavettepickup'}",
            '7': "{l s='Sunday' mod='lanavettepickup'}"
        };
        var ws_ok = {if $ws_ok == 1}true{else}false{/if};
        var no_pudo_found = "{l s='No parcelshop found for this area.' mod='lanavettepickup'}";

    </script>
