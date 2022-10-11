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
{if $olea_has_qty_prices}
<div id="ajax_quantityDiscountFOQty">
<h3>{$oleaqty_productname|escape:"htmlall":"UTF-8"}</h3>
<input type="hidden" id="product_page_product_id" value="{$oleafoqty_ajaxidproduct|intval}" />
{include file="$prices_info_tpl_path"}
</div>
{/if}