{if $MENU != ''}
<!-- Menu -->
<div id="block_top_menu" class="sf-contener clearfix col-lg-12">
<div class="cat-title">{l s="Categories" mod="blocktopmenu"}</div>
<ul class="sf-menu clearfix menu-content">
<hr  class="mobile_menu" />
{$MENU}
{if $MENU_SEARCH}
<li class="sf-search noBack" style="float:right">
<form id="searchbox" action="{$link->getPageLink('search')|escape:'html':'UTF-8'}" method="get">
<p>
<input type="hidden" name="controller" value="search" />
<input type="hidden" value="position" name="orderby"/>
<input type="hidden" value="desc" name="orderway"/>
<input type="text" name="search_query" value="{if isset($smarty.get.search_query)}{$smarty.get.search_query|escape:'html':'UTF-8'}{/if}" />
</p>
</form>
</li>
{/if}
<hr  class="mobile_menu" />
<li class="mobile_menu"><a href="" id="mySearch_mobile">{l s="Search" mod="blocktopmenu"}</a></li>
<li class="mobile_menu"><a data-action="login"  class="login" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Log in to your customer account' mod='blockuserinfo'}" id="show_modal_login">{l s='Connexion' mod='blocktopmenu'}</a></li>
<hr  class="mobile_menu"/>
{if count($languages) > 1}
{foreach from=$languages key=k item=language name="languages"}
<li class="mobile_menu lang_menu"><a href="https://www.magicflyer.com/{$language.iso_code|regex_replace:"/\s\(.*\)$/":""}">{$language.iso_code|regex_replace:"/\s\(.*\)$/":""}</a></li>
{/foreach}
{/if}


	<li class="mobile_menu" ><a href="{$link->getCMSLink('3')}">{l s='CGV'}</a></li>
	<li class="mobile_menu"><a href="{$link->getCMSLink('2')}">{l s='Mentions légales'}</a></li>

</ul>
</div>
<!--/ Menu -->
{/if}
<script>
$(document).ready(function(){
	var lang_iso = "{$lang_iso}";
	if(lang_iso=='fr'){
	
		$('.sf-menu li ul').eq(0).append('<li style="display:none"><a href="https://www.magicflyer.com/'+lang_iso+'/53-articles-deco">Accessoires</a></li><li><a href="https://www.magicflyer.com/fr/71-cartes-festives">Cartes festives</a></li>');
		
		$('.sf-menu li ul li a').eq(0).attr('href','https://www.magicflyer.com/fr/content/48-Envolee-papillon');
		
		$('.sf-menu li ul li a').eq(0).click(function(e) {
			
			e.preventDefault();
			window.location.href = 'https://www.magicflyer.com/fr/content/48-Envolee-papillon';
			
		});
		
		
 
/* 

$('.sf-menu li').eq(8).append('<ul class="nocat_page"><li ><a href="https://www.magicflyer.com/fr/content/52-communication-d-entreprise">Communication / Marketing</a></li><li><a href="https://www.magicflyer.com/fr/content/55-reseaux-de-distribution">Revendeurs</a></li></ul>');

*/		
/*		
		$('.sf-menu li a').eq(8).attr('href','https://www.magicflyer.com/fr/content/42-professionnels');
		
		$('.sf-menu li a').eq(8).click(function(e) {
			
			e.preventDefault();
			window.location.href = 'https://www.magicflyer.com/fr/content/42-professionnels';
			
		});
*/
		
		$('.sf-menu li ul li a').eq(11).attr('href','https://www.magicflyer.com/fr/content/6-tutoriel');
		$('.sf-menu li ul li a').eq(12).attr('href','https://www.magicflyer.com/fr/forms/3/Contact');

		
	}
	
		
	
	if(lang_iso=='en'){
		
		$('.sf-menu li ul').eq(0).append('<li style="display:none"><a href="https://www.magicflyer.com/'+lang_iso+'/53-decoration-accessories">Accessories</a></li><li><a href="https://www.magicflyer.com/en/71-cartes-festives">Festive cards</a></li>');
		
		$('.sf-menu li ul li a').eq(0).attr('href','https://www.magicflyer.com/en/content/48-butterflies-flighter');
		
		$('.sf-menu li ul li a').eq(0).click(function(e) {
			
			e.preventDefault();
			window.location.href = 'https://www.magicflyer.com/en/content/48-butterflies-flighter';
			
		});
		

		$('.sf-menu li ul li a').eq(11).attr('href','https://www.magicflyer.com/en/content/6-tutoriel');
		$('.sf-menu li ul li a').eq(12).attr('href','https://www.magicflyer.com/en/forms/3/Contact');

	}
	
	if(lang_iso=='es'){
		
		$('.sf-menu li ul').eq(0).append('<li style="display:none"><a href="https://www.magicflyer.com/'+lang_iso+'/53-accesorios">Accesorios</a></li><li><a href="https://www.magicflyer.com/es/71-cartes-festives">Tarjeta festivo</a></li>');
		
		$('.sf-menu li ul li a').eq(0).attr('href','https://www.magicflyer.com/es/content/48-vuelo-de-mariposas');
		
		$('.sf-menu li ul li a').eq(0).click(function(e) {
			
			e.preventDefault();
			window.location.href = 'https://www.magicflyer.com/es/content/48-vuelo-de-mariposas';
			
		});
		
		$('.sf-menu li ul li a').eq(11).attr('href','https://www.magicflyer.com/es/content/6-tutoriel');
		$('.sf-menu li ul li a').eq(12).attr('href','https://www.magicflyer.com/es/forms/3/Contact');

		

	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
	//init menu ouvert
	var id_cat,id_cms;
	//console.log( decodeURI($__GET.get('controller')));
	var controller = '{if isset($smarty.get.controller)}{$smarty.get.controller}{/if}';
	var module = '{if isset($smarty.get.module)}{$smarty.get.module}{/if}';
	
	
	if(controller=='category')id_cat = '{if isset($smarty.get.id_category)}{$smarty.get.id_category}{/if}';
	if(controller=='cms')id_cms = '{if isset($smarty.get.id_cms)}{$smarty.get.id_cms}{/if}';
	
	//menu produits
	console.log('cat:'+id_cat);
	
	switch(Number(id_cat)) {
		
	case 12:case 13:case 4:case 18:case 43:case 45:case 46:case 47:case 49:case 51:case 52:case 64:
		$('.sf-menu li ul').eq(0).removeClass('nocat_page');
		$('.sf-menu li ul').eq(0).addClass('cat_page');
		$('.sf-menu li ul').eq(0).css('display','block');
		break;
	}
	
	//menu cms
	switch(Number(id_cms)) {
	case 45:case 46:case 47:
		$('#ssmenu_acacher').css('display','block');

		break;	
		
	}
	switch(Number(id_cms)) {
	case 13:case 43:case 45:case 46:case 47:case 48:
		$('.sf-menu li ul').eq(0).removeClass('nocat_page');
		$('.sf-menu li ul').eq(0).addClass('cat_page');
		$('.sf-menu li ul').eq(0).css('display','block');
		
		$('#ssmenu_acacher').css('display','block');

		break;		
		
	case 42:
		$('.sf-menu li ul').eq(2).removeClass('cat_page');
		$('.sf-menu li ul').eq(2).addClass('nocat_page');
		$('.sf-menu li ul').eq(2).css('display','none');
		break;		
	
	case 10:
	case 49:
		$('.sf-menu li ul').eq(2).removeClass('nocat_page');
		$('.sf-menu li ul').eq(2).addClass('cat_page');
		$('.sf-menu li ul').eq(2).css('display','block');
		break;		
		
	case 38:
	case 44:
	case 50:
		$('.sf-menu li ul').eq(3).removeClass('nocat_page');
		$('.sf-menu li ul').eq(3).addClass('cat_page');
		$('.sf-menu li ul').eq(3).css('display','block');
		break;
	}	
	
	
	if(module!='jmsblog'){
	//menu controller
	switch(controller) {

	case 'category':
		$('.sf-menu li ul').eq(0).removeClass('nocat_page');
		$('.sf-menu li ul').eq(0).addClass('cat_page');
		$('.sf-menu li ul').eq(0).css('display','block');
		break;		
		
	case 'order-opc':
		//alert(controller);
		break;	
		
	case 'form':
		$('.sf-menu li ul').eq(3).removeClass('nocat_page');
		$('.sf-menu li ul').eq(3).addClass('cat_page');
		$('.sf-menu li ul').eq(3).css('display','block');
		break;
	}	
}
	switch(module) {

	case 'jmsblog':
		$('.sf-menu li ul').eq(2).removeClass('nocat_page');
		$('.sf-menu li ul').eq(2).addClass('cat_page');
		$('.sf-menu li ul').eq(2).css('display','block');
		break;
	}
	
});
</script>