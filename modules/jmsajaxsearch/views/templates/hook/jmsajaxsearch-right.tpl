{*
 * @package Jms Ajax Search
 * @version 1.1
 * @Copyright (C) 2009 - 2015 Joommasters.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Website: http://www.joommasters.com
*}

<div id="search_block_nav" class="block exclusive">
	<a class="btn-xs dropdown-toggle icon-search" data-toggle="dropdown" href="#"> </a>
	<a class="btn-xs dropdown-toggle close-icon" href="#"><i class="zmdi zmdi-close"></i></a>
	<form method="get" action="{$link->getPageLink('search', true)|escape:'html':'UTF-8'}">
			<input type="hidden" name="controller" value="search" />
			<input type="hidden" name="orderby" value="position" />
			<input type="hidden" name="orderway" value="desc" />
			<input class="search_query" type="text" id="search_query_block" name="search_query" placeholder="{l s='Search' mod='jmsajaxsearch'}" />
	</form>
</div>