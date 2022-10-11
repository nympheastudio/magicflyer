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
{if $oleaqty_istpl16}
	{if $olea_has_qty_prices}
	<section  class="page-product-box" >
	<h3 class="idTabHrefShort page-product-heading">{l s='Prices' mod='oleafoquantityprices'}</h3>
	{include file="$prices_info_tpl_path"}
	</section>
	{/if}
{else}
	{if $olea_has_qty_prices}
	<ul class="idTabs clearfix">
		<li><a style="cursor: pointer" class="selected">{l s='Quantity discount' mod='oleafoquantityprices'}</a></li>
	</ul>
	<div id="quantityDiscountFOQty">
	{include file="$prices_info_tpl_path"}
	</div>
	{/if}
{/if}