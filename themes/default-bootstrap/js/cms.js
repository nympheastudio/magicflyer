/*
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
*/
$(document).ready(function(){
	if (typeof ad !== 'undefined' && ad && typeof adtoken !== 'undefined' && adtoken)
	{
		$(document).on('click', 'input[name=publish_button]', function(e){
			e.preventDefault();
			submitPublishCMS(ad, 0, adtoken);
		});
		$(document).on('click', 'input[name=lnk_view]', function(e){
			e.preventDefault();
			submitPublishCMS(ad, 1, adtoken);
		});
	}
	


	
	//$('.cms-carte-papillon .button').bind('click', function(event) {
   //    
   //    event.preventDefault();
	//	var qty = $("input[name=quantity_livret]").val();
	//	//alert('add2cart'+qty);
	//	window.location.href = 'index.php?controller=cart&add=1&id_product=173&qty='+qty;
	//	
   //});
	
	
	/*	
	$('.cms-47 .button').bind('click', function(event) {
        
        event.preventDefault();
		event.stopPropagation();
		
		$(".modal_add2cart").modal('toggle');
		$(".modal_add2cart").addClass('visible_login');
		
		var qty = $("input[name=quantity_livret]").val();
		if(qty<=0)qty=1;
		var coloris = $("#coloris").val();
		var id_p=173;
		if(coloris==1) id_p = 173;//carte papillon coeur Argenté
		if(coloris==2) id_p = 184;//carte papillon coeur Or
		
		$.ajax({
		  url: 'index.php?controller=cart&ajax=true&add=1&id_product='+id_p+'&qty='+qty,
		  cache: true,
		  async: true,
		}).done(function() {
		 location.href = 'https://www.magicflyer.comindex.php?controller=category&id_category=51';
		
		});


		
    });
	
	
	
	//$(".modal_add2cart").hide();
	$('.cms-46 .button').bind('click', function(event) {
        
        event.preventDefault();
		event.stopPropagation();
		
		$(".modal_add2cart").modal('toggle');
		$(".modal_add2cart").addClass('visible_login');
		
		
		var qty = $("input[name=quantity_livret]").val();
		if(qty<=0)qty=1;
		
		var coloris = $("#coloris").val();
		var id_p=174;
		if(coloris==1) id_p = 174;//livret coeur Argenté
		if(coloris==2) id_p = 183;//livret coeur Or
		
		$.ajax({
		  url: 'index.php?controller=cart&ajax=true&add=1&id_product='+id_p+'&qty='+qty,
		  //context: document.body,
		  cache: true,
		  async: true,
		}).done(function() {
		 location.href = 'https://www.magicflyer.comindex.php?controller=category&id_category=51';
		
		});
		
    });
	*/
	
	
	/* SCROLL BTN FROM TOP */

		$('a.scroll_btn').bind('click', function(event) {
				var $anchor = $(this);
				$('html, body').stop().animate({
					scrollTop: ($($anchor.attr('href')).offset().top)
				}, 1250, 'easeInOutExpo');
				event.preventDefault();
			});

	
	
});

function submitPublishCMS(url, redirect, token)
{
	var id_cms = $('#admin-action-cms-id').val();

	$.ajaxSetup({async: false});
	$.post(url+'/index.php', { 
			action: 'PublishCMS',
			id_cms: id_cms, 
			status: 1, 
			redirect: redirect,
			ajax: 1,
			tab: 'AdminCmsContent',
			token: token
		},
		function(data)
		{
			if (data.indexOf('error') === -1)
				document.location.href = data;
		}
	);
	return true;
}



	 

