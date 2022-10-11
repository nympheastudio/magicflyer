/**
 * JavaScript
 *
 * @version 1.4.4
 * @license GNU Lesser General Public License, http://www.gnu.org/copyleft/lesser.html
 * @author  buy-addons.com, http://odvarko.cz
 * @created 2008-06-15
 * @updated 2014-12-09
 * @link    http://buy-addons.com
 */
jQuery(document).ready(function() {
	$( '#mega_menu_plus a:empty' ).remove();
	$( '#mega_menu_plus .mobi').click(function(event){
		//event.preventDefault();
		//console.log("aa");
		if($("#mega_menu_plus ul.mg-menu").hasClass("show-ul")==false){
			$("#mega_menu_plus ul.mg-menu").removeClass( "hidden-ul" ).addClass("show-ul");
		}else{
			$("#mega_menu_plus ul.mg-menu").removeClass( "show-ul" ).addClass("hidden-ul");
		}
	});
	$('.mg-menu > li > span.submore').click(function(){
		if($(this).parents('li').hasClass("show-submenu")==false){
			$(".mg-menu li").removeClass( "show-submenu" )
			$(this).parents('li').removeClass( "hidden-submenu" )
			$(this).parents('li').addClass("show-submenu");
		}else{
			$(this).parents('li').removeClass( "show-submenu" ).addClass("hidden-submenu");
		}
	});
	$('.treelinks li > span.submore').click(function(){
		if($(this).parent('li').hasClass("show-submenu")==false){
			var children=$(this).parent('li').attr('class');
			$(".treelinks li."+children).removeClass( "show-submenu" );
			$(".treelinks li."+children+" li").removeClass( "show-submenu" );
			$(this).parent('li').removeClass( "hidden-submenu" );
			$(this).parent('li').addClass("show-submenu");
		}else{
			$(this).parent('li').removeClass( "show-submenu" ).addClass("hidden-submenu");
			var children=$(this).parent('li').attr('class');
			$(".treelinks li."+children).removeClass( "show-submenu" );
			$(".treelinks li."+children+" li").removeClass( "show-submenu" );
			$(this).parent('li').removeClass( "hidden-submenu" );
		}
	});
});
function showPanel(anchor,speed) {
		if(anchor=='default'){
			$("#mega_menu_plus ul li").hover(function(){ 
				$(this).find('div.sub').css({visibility: "visible",display: "none"}).show(speed); 
				},function(){ 
				$(this).find('div.sub').css({visibility: "hidden",display: "none"}).hide(speed); 
			});
			$("ul.treelinks li ul li").hover(function(){ 
				$(this).find('ul li').css({visibility: "visible",display: "none"}).show(speed); 
				},function(){ 
				$(this).find('ul li').css({visibility: "hidden"}); 
			});
				
		}else if(anchor=='fade'){
			$("#mega_menu_plus ul li").hover(function(){ 
				$(this).find('div.sub').css({visibility: "visible",display: "none"}).fadeIn(speed); 
				},function(){ 
				$(this).find('div.sub').css({visibility: "hidden",display: "none"}).hide(speed); 
			}); 	
			$("ul.treelinks li ul li").hover(function(){ 
				$(this).find('ul li').css({visibility: "visible",display: "none"}).fadeIn(speed); 
				},function(){ 
				$(this).find('ul li').css({visibility: "hidden"}); 
			});
		}else if(anchor=='slide'){
			$("#mega_menu_plus ul li").hover(function(){ 
				$(this).find('div.sub').css({visibility: "visible",display: "none"}).slideDown(speed); 
				},function(){ 
				$(this).find('div.sub').css({visibility: "hidden",display: "none"}).slideUp(speed); 
			}); 
			$("ul.treelinks li ul li").hover(function(){ 
				$(this).find('ul li').css({visibility: "visible",display: "none"}).slideDown(speed); 
				},function(){ 
				$(this).find('ul li').css({visibility: "hidden"}); 
			});
		}else if(anchor=='none'){
			
		}
}