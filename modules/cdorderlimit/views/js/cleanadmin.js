/**
* 2013 - 2015 CleanDev
*
* NOTICE OF LICENSE
*
* This file is proprietary and can not be copied and/or distributed
* without the express permission of CleanDev
*
* @author    CleanPresta : www.cleanpresta.com <contact@cleanpresta.com>
* @copyright 2013 - 2015 CleanDev.net
* @license   You only can use module, nothing more!
*/

$(document).ready(function(){
	//tab selected
	$('.clean-module .list-group-item').on('click', function() {
		var id = $(this).attr('href');
		$(".clean-module .list-group-item, .clean-module .tab-content .tab-pane.mainTab").removeClass("active");
		$(this).addClass("active");
		$('.clean-module .tab-content .mainTab'+id).addClass("active");
	});
	
	//hach acces
	var href = location.href.split('#');
	if(href[1] != 'undefined'){
		$('.clean-module .list-group-item.'+href[1]).click();
	}
});