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

<script type="text/javascript">
$(document).ready(function () {
  var item = $('#delivery_option_6_1');
  var itemParent = item.parent();
  itemParent.css('background-color', 'red');
  itemParent.append("TEST");
  item.append("TEST2");
  
  item.click(function() {
    alert("CLICKED");
  });
  if(item.attr("checked") == true || item.attr("checked") == "checked")
  { 
    alert("is checked");
  }
});
</script>

{*
{foreach from=$offers key=o item=offer} {$offer.pointsList}<br />
    {include file="$templateCarrier" var=$offer var=$choosenCarrier var=$defaultCarrier var=$opc var=$id_address}
  {/foreach}
*}