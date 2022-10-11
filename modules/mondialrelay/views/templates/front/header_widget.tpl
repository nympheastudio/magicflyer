{*
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
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2018 PrestaShop SA
* @version   Release: $Revision: 6844 $
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}
<script type="text/javascript" src="//unpkg.com/leaflet/dist/leaflet.js"></script>
<link rel="stylesheet" type="text/css" href="//unpkg.com/leaflet/dist/leaflet.css" />

<script type="text/javascript">
	// Global JS Value
	var _PS_MR_MODULE_DIR_ = "{$new_base_dir|escape:'htmlall':'UTF-8'}";
	var mrtoken = "{$MRToken|escape:'htmlall':'UTF-8'}";
	var PS_MRMODE = "{$MR_MONDIAL_RELAY_MODE|escape:'htmlall':'UTF-8'}";
	var PS_MROPC = {$one_page_checkout|escape:'htmlall':'UTF-8'};
	var PS_MRTranslationList = [];
	var PS_MRCarrierMethodList = [];
	var PS_MRSelectedRelayPoint = {literal}{{/literal}'carrier_id': 0, 'relayPointNum': 0{literal}}{/literal};
	if (PS_MROPC) {
        var orderOpcUrl = "{$link->getPageLink("order-opc", true)|escape:'quotes':'UTF-8'}";
    }
    var MR_ajax_url = "{$MR_ajax_url|escape:'htmlall':'UTF-8'|urldecode}";

	PS_MRTranslationList['Select'] = "{l s='Select' mod='mondialrelay' js=1}";
	PS_MRTranslationList['Selected'] = "{l s='Selected' mod='mondialrelay' js=1}";
	PS_MRTranslationList['errorSelection'] = "{l s='Please choose a relay point' mod='mondialrelay' js=1}";
	PS_MRTranslationList['openingRelay'] = "{l s='Opening hours' mod='mondialrelay' js=1}";
	PS_MRTranslationList['moreDetails'] = "{l s='More details' mod='mondialrelay' js=1}";
</script>
<!--On charge le widget mondial relay depuis leurs serveurs-->
<script type="text/javascript" src="{$new_base_dir|escape:'htmlall':'UTF-8'}views/js/common.js"></script>
<script type="text/javascript" src="https://widget.mondialrelay.com/parcelshop-picker/jquery.plugin.mondialrelay.parcelshoppicker.min.js"></script>
<script type="text/javascript" src="{$new_base_dir|escape:'htmlall':'UTF-8'}views/js/mondialrelay_widget.js"></script>
