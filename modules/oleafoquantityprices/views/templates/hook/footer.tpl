{** 
  * ---------------------------------------------------------------------------------
  * 
  * This file is part of the 'oleafoquantityprices' module feature 
  * Developped for Prestashop  platform.
  * You are not allowed to use it on several site
  * You are not allowed to sell or redistribute this module
  * This header must not be removed
  * 
  * @category XXX
  * @author OleaCorner <contact@oleacorner.com> <www.oleacorner.com>
  * @copyright OleaCorner
  * @version 1.0
  * 
  * ---------------------------------------------------------------------------------
  *}
{if isset($oleaqty_popupinlist) && $oleaqty_popupinlist == 1}
<div id="oleafoqty_screensizedetection" class="hidden-xs"></div>

<script type="text/javascript">
//<![CDATA[
	var oleaQtyPricesPath = '{$oleaQtyPricesPath|escape:"htmlall":"UTF-8"}';
	var oleaQtyPricesWidth = {$oleaQtyPricesWidth|intval};
	var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
	var currencyFormat = '{$currencyFormat|intval}';
	var currencyBlank = '{$currencyBlank|intval}';
	if ($('#oleafoqty_screensizedetection').is(':visible'))
		oleafoqtyprices_initpopupinlist ();
//]]>
</script>
{/if}
