{*
 * @package Jms Ajax Search
 * @version 1.1
 * @Copyright (C) 2009 - 2015 Joommasters.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Website: http://www.joommasters.com
*}
	<div class="search-box" role="menu">
		<span class="icon-search"></span>
		<form method="get" action="{$link->getPageLink('search')|escape:'html':'UTF-8'}" id="searchbox">
		<input type="hidden" name="controller" value="search" />
		<input type="hidden" name="orderby" value="position" />
		<input type="hidden" name="orderway" value="desc" />
		<input type="text" id="ajax_search" name="search_query" placeholder="{l s='Search' mod='jmsajaxsearch'}" class="form-control" />
		<input type="hidden" id="noproduct_text" name="noproduct_text" value="{l s='There is no product' mod='jmsajaxsearch'}" />
		</form>
		<div id="search_result">
		</div>
	</div>	