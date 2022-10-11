{*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!-- Block search module TOP -->
<div id="search_block_top">
	<a href="" id="mySearch">{l s='Search' mod='blocksearch'}</a>
</div>
<!-- /Block search module TOP -->
<div id="myModalSearch" class="modalSearch in doFadeIn" >

	<!-- Header du modal -->	
	<div class="header-search">
	    <p class="closeSearch">
	    <span class="fermer-search"><i>×</i>{l s='Close' mod='blocksearch'}</span></p>
	</div>

	<!-- Modal content -->
	<div class="modalSearch-content">

		<!-- Formulaire -->
		<form id="searchbox" method="get" action="https://www.magicflyer.comfr/recherche">
		   	<label for="recherche"><h1 class="search-label">{l s='Search' mod='blocksearch'}</h1></label>
		    <input type="hidden" name="controller" value="search">
			<input type="hidden" name="orderby" value="position">
			<input type="hidden" name="orderway" value="desc">
		  	<input class="search_query recherche ac_input" id="search_query_top" type="text" name="search_query" value="" autofocus="" autocomplete="off">
		</form>
	</div>

</div>
{literal}
<script>
$(document).ready(function(){

// When the user clicks the button, open the modal 
	$( "#mySearch,#mySearch_mobile" ).click(function(e) {
		e.preventDefault();
		$("#myModalSearch").modal('show');
		$("#myModalSearch").addClass('doFadeIn');
		$("#myModalSearch").removeClass('doFadeOut');
		e.stopPropagation();
		$("#search_query_top").focus();
	});		
	
	$( ".link_modal_search" ).click(function(e) {
		e.preventDefault();
		$("#myModalSearch").modal('show');
		$("#myModalSearch").addClass('doFadeIn');
		$("#myModalSearch").removeClass('doFadeOut');
		e.stopPropagation();
		$("#search_query_top").focus();
	});	

	// When the user clicks on <span> (x), close the modal
	$( ".closeSearch" ).click(function(e) {
		$("#myModalSearch").modal('hide');
		$("#myModalSearch").addClass('doFadeOut');
		$("#myModalSearch").removeClass('doFadeIn');
	});	
	

});
</script>{/literal}
