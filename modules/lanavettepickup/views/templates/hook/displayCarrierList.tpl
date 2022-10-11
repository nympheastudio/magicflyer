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

<script type="text/javascript">
    var is_ps16 = {if ($is_PS16)}true{else}false{/if};
</script>

{if isset($hide_pickup_carrier) && $hide_pickup_carrier}
    <script type="text/javascript">
        $('input.delivery_option_radio').each(function () {
            if ($(this).val() == '{$pickup_carrier_id},') {
                $(this).parents('.delivery_option').hide();
            }
        });
    </script>
{else}
    <div id="pickup_carrier" style="display: none;">

        <div style="clear:both"></div>

        <div id="pickup_carrier_map">
            <input type="hidden" name="LNP2_PICKUP_SITE">

            <!-- LIST + SEARCH + MAP -->
            <div class="pickup_list_search_and_map">

                <!-- right column -->
                <div class="pickup_list_right_col col-xs-12 col-sm-8 col-sm-push-4">

                    <p class="pickup_map_search_title">{l s='Choose a parcelshop on the map or select another address' mod='lanavettepickup'}</p>

                    {include file='../hook/map.tpl'}

                </div>
                <!-- end right column -->

                <!-- LIST -->
                <div class="pickup_list col-xs-12 col-sm-4 col-sm-pull-8">
                    <ul class="pickup_list_ul"></ul>
                </div>

                <div style="clear:both"></div>

            </div>
            <!-- END LIST + SEARCH + MAP -->
        </div>

    </div>
    <script>

        var modules_dir = '{$modules_dir|escape:'html':'UTF-8'}'; {* smarty global var required for map.js *}

        var map_is_drop_off = false;
        var sms_activated = false;
        var change_str = "{l s='Change' mod='lanavettepickup'}";
        var opening_hours_str = "{l s='Opening hours' mod='lanavettepickup'}";
        var closing_time_title_str = "{l s='Closing Period' mod='lanavettepickup'}";
        var closing_time_str = "{l s='Closed from |start| to |end|' mod='lanavettepickup'}";
        var day_of_week_str_ar = {
            1: "{l s='Monday' mod='lanavettepickup'}",
            2: "{l s='Tuesday' mod='lanavettepickup'}",
            3: "{l s='Wednesday' mod='lanavettepickup'}",
            4: "{l s='Thursday' mod='lanavettepickup'}",
            5: "{l s='Friday' mod='lanavettepickup'}",
            6: "{l s='Saturday' mod='lanavettepickup'}",
            7: "{l s='Sunday' mod='lanavettepickup'}"
        };

        var please_pick_a_pudo_str = "{l s='Please pick a delivery point' mod='lanavettepickup'}";
        var general_price = {$general_price|floatval};
        var lnp_corse_paid = {if $lnp_corse_paid}true{else}false{/if};
        var price_str = "{l s='From €2,90 to €6,90' mod='lanavettepickup'}";
        var corse_price = {$corse_price|floatval};
        var lnp2_free = {if $lnp2_free}true{else}false{/if};
        var pickup_carrier_id = '{$pickup_carrier_id|escape:'html':'UTF-8'}';
        var drop_off_pudo_id = '{$drop_off_pudo_id|escape:'html':'UTF-8'}';
        var security_token = '{$security_token|escape:'html':'UTF-8'}';
        var delivery_address = '{$delivery_address|escape:'html':'UTF-8'}';
        var delivery_zip = '{$delivery_zip|escape:'html':'UTF-8'}';
        var cart_id = '{$cart_id|escape:'html':'UTF-8'}';
        var ws_ok = {if $ws_ok}true{else}false{/if};
        var no_pudo_found = "{l s='No parcelshop found for this area.' mod='lanavettepickup'}";

        {*
            // this code needs to stay here to prevent loading duplication in one-page-checkout
            /*
            console.log('SET');
            console.log($('#delivery_option_pickup_sms_checkbox').length);
            $('#delivery_option_pickup_sms_checkbox').change(function() {
                pushSelectedPudo();
            })
            */
        *}


        $(document).ready(function () {
            setTimeout(function(){
                setCarrierPriceAndPickupOptionsDisplay();
            }, 500);
        });

    </script>
{/if}
