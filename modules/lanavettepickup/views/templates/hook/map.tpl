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

<!-- SEARCH -->
<div class="pickup_map_search col-xs-12">
    <div class="form-wrapper">
        <div class="form-group clearfix">
            {*<label class="control-label col-lg-3">*}
            {*{l s='Parcelshop' mod='lanavettepickup'}<span class="pickup_red">*</span>*}
            {*</label>*}
            <div class="col-lg-12">
                <input type="text" id="pickup_map_search_input"
                       class="pickup_map_search_input controls {if (isset($pudo_is_available)) && !$pudo_is_available}background_red{/if}"
                       placeholder="{l s='Search an adress, city or postal code' mod='lanavettepickup'}" type="text"
                       onKeyPress="return disableEnterKey(event)">
                <a href="#" class="pickup_map_search_icon lens"></a>
            </div>
        </div>
    </div>
</div>

{if (isset($pudo_is_available)) && !$pudo_is_available}
    <div class="pickup_map_pudo_unavailable_div clearfix">
        <p class="red">{l s='Your parcel shop is not available, please use the Edit button to chose another drop of point.' mod='lanavettepickup'}</p>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            changePickupMapSearchIcon('change');
        })
    </script>
{/if}


<!-- MAP -->
<div class="hidden-xs" id="lnp2_map"></div>

<!-- MAP OVERLAY BOX -->
<div style="display: none">
    <div class="pickup_map_overlay_box">
        <div class="pickup_map_overlay_box_infobox">
            <!-- close button -->
            <a href="#" class="pickup_map_overlay_box_close">Fermer</a>

            <div class="pickup_map_overlay_box_content">

                <!-- pudo info content -->
                <p class="pickup_map_overlay_box_content_pudo">
                    <span id="pickup_map_overlay_box_content_pudo_address"></span>
                </p>

                <!-- schedule content -->
                <ul class="pickup_map_overlay_box_content_schedule"></ul>

            </div>

            <a href="#" class="pickup_map_overlay_box_select_button">
                {l s='Select this Parcelshop' mod='lanavettepickup'}
            </a>
        </div>
    </div>
</div>

<!-- END MAP OVERLAY BOX -->
