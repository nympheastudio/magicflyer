{**
 * 2007-2018 PrestaShop
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
 * @author    EnvoiMoinsCher <api@boxtal.com>
 * @copyright 2007-2018 PrestaShop SA / 2011-2016 EnvoiMoinsCher
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registred Trademark & Property of PrestaShop SA
 *}
<link type="text/css" rel="stylesheet" href="{$emcBaseDir|escape:'htmlall':'UTF-8'}views/css/carrier.css" />
{if ($mobile)}
<link type="text/css" rel="stylesheet" href="{$emcBaseDir|escape:'htmlall':'UTF-8'}views/css/carrier-mobile.css" />
{/if}

<script type="text/javascript">
    var Emc_host = "{$host|escape:'htmlall':'UTF-8'}";

    /*
     * We need text translation for external js
     * the script next to this need some information and smarty cant give those translations
     * (*.js or header_hook => not parsed by smarty)
     */
     var Emc_messages = {
         delivery_message : "{$delivery_label}",
         close_map_translation : "{$closeMapTranslation|escape:'htmlall':'UTF-8'}",
         geolocate_map_translation : "{$geolocateMapTranslation|escape:'htmlall':'UTF-8'}",
         postcode_map_translation : "{$postcodeMapTranslation|escape:'htmlall':'UTF-8'}",
         city_map_translation : "{$cityMapTranslation|escape:'htmlall':'UTF-8'}",
         search_map_translation : "{$searchMapTranslation|escape:'htmlall':'UTF-8'}",
         search_mobile_map_translation : "{$searchMobileMapTranslation|escape:'htmlall':'UTF-8'}",
         no_pickup_point_found_try_other_addr : "{l s='no pickup point found : try modify shipmnt address' mod='envoimoinscher'}",
         select_pickup_point1 				 : "{l s='select pickup point 1' mod='envoimoinscher'}",
         select_pickup_point2 				 : "{l s='select pickup point 2' mod='envoimoinscher'}",
         select_pickup_point3 				 : "{l s='select pickup point 3' mod='envoimoinscher'}",
         select_this_pickup_point 			 : "{l s='select this pickup point' mod='envoimoinscher'}",
         selected_point : "{l s='Selected delivery relay point:' mod='envoimoinscher'}",
         change_point : "{l s='change' mod='envoimoinscher'}",
         before_continue_select_pickup_point : "{l s='before continue : select pickup point' mod='envoimoinscher'}",
         opening_hours : "{l s='Opening hours' mod='envoimoinscher'}",
         choose : [],
         close_map : "{l s='close X' mod='envoimoinscher'}",
         monday : "{l s='monday' mod='envoimoinscher'}",
         tuesday : "{l s='tuesday' mod='envoimoinscher'}",
         wednesday : "{l s='wednesday' mod='envoimoinscher'}",
         thursday : "{l s='thursday' mod='envoimoinscher'}",
         friday : "{l s='friday' mod='envoimoinscher'}",
         saturday : "{l s='saturday' mod='envoimoinscher'}",
         sunday : "{l s='sunday' mod='envoimoinscher'}",
         geoloc_problem : "{l s='Could not geolocate you' mod='envoimoinscher'}",
         pp_problem : "{l s='Could not load parcel points for this address' mod='envoimoinscher'}",
         pp_loading : "{l s='Loading points...' mod='envoimoinscher'}",
         not_same_country : "{l s='Wrong country on geolocation. You should change the country in your delivery address in you want to have your delivery in another country.' mod='envoimoinscher'}",
         carrier_unavailable : "{l s='This carrier is not available for this order' mod='envoimoinscher'}"
     };

     {foreach from=$relayName key=ope item=translation}
         Emc_messages.choose["{$ope|escape:'htmlall':'UTF-8'}"] = "{$translation|escape:'htmlall':'UTF-8'}";
     {/foreach}
</script>
