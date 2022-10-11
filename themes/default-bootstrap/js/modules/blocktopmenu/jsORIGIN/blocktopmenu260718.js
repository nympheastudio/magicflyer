

var responsiveflagMenu = false;
var categoryMenu = $('ul.sf-menu');
var mCategoryGrover = $('.sf-contener .cat-title');

$(document).ready(function(){
	

	categoryMenu = $('ul.sf-menu');
	mCategoryGrover = $('.sf-contener .cat-title');
	responsiveMenu();
	$(window).resize(responsiveMenu);
	
	initMenu();
	
});



function  initMenu(){
	
	$('.sf-menu li ul li a').eq(0).next('ul').attr('id','ssmenu_acacher');
	
	$('.sf-menu li ul li a').eq(0).next('ul').removeClass("cat_page");
	
	$('.sf-menu li ul li a').eq(0).hover(
	  function() {
		$( this ).next('ul').show();
	  }, function() {
		$( this ).next('ul').hide();
	  }
	);
	
	$('.sf-menu li ul li ul').eq(0).hover(
	  function() {
		$( this ).show();
	  }, function() {
		$( this ).hide();
	  }
	);
	
	 $('.sf-menu').find('ul').each(function(){

		 $(this).addClass('nocat_page');
		 $(this).hide();
		 

	 });
	
	
	
	


	
	
	
	
/*
	$('a[href^="http://www.magicflyer.com/fr/content/category/7-Lanceurs-de-papillons"]').click(function(event) {
		//alert($(this).attr('href'));
		event.stopPropagation();
	});
	
	$('a[href^="http://www.magicflyer.com/fr/content/category/7-Lanceurs-de-papillons"]').next('ul').hide();

	*/
	


	//$('#ssmenu_acacher').hide();
	$('#ssmenu_acacher').attr('style','display:none !important;');
	
	$('#ssmenu_acacher').removeClass("cat_page");
	
	
	
	$('.sf-menu li a').click(function(event) {
		event.stopPropagation();
		//$('#ssmenu_acacher').hide();
		//$('#ssmenu_acacher').removeClass("cat_page");
		//$('#ssmenu_acacher').remove();
		

		
		$(this).addClass('active');
		//event.stopPropagation();
		
		li_parent = $(this).closest('li');
		ul_sousmenu = li_parent.find('ul');
		
		//alert( ul_sousmenu.attr('id') );
		
		
		
		if (ul_sousmenu.length) {
			
			event.preventDefault();
			
	
			if (ul_sousmenu.hasClass('nocat_page')) {
				
				
				
				
				ul_sousmenu.hide().removeClass("nocat_page");
				
				
				
				ul_sousmenu.not( '#ssmenu_acacher' ).slideDown('slow', function(){
					$('#ssmenu_acacher').attr('style','display:none !important;');
		
					ul_sousmenu.addClass('cat_page');
					
	
					
					
				});
				
				
				
				
				
				
				
				console.log('Afficher sousmenu');
				
			}else{
				
				//$('#ssmenu_acacher').hide();
				ul_sousmenu.not( '#ssmenu_acacher' ).slideUp('slow',function() {
					
					//$('.sf-menu li ul li a').eq(0).next('ul').hide();
					
					ul_sousmenu.hide().removeClass("cat_page");
					ul_sousmenu.addClass('nocat_page');
					
				
				});
				
				console.log('cacher soumenu');
			}
		}
		
				
		
	});
	
	
	
	
	
	
}


// check resolution
function responsiveMenu()
{	
	if ($(window).width() <= 768 && responsiveflagMenu == false) {
		menuChange('enable');
		responsiveflagMenu = true;
	} else if ($(window).width() >= 769) {
		handleClickMenu();
		menuChange('disable');
		responsiveflagMenu = false;
	}

}

// init Super Fish Menu for 767px+ resolution
function desktopInit()
{
	mCategoryGrover.off();
	mCategoryGrover.removeClass('active');
	$('.sf-menu > li > ul').removeClass('menu-mobile').parent().find('.menu-mobile-grover').remove();
	
	
	$('.sf-menu').removeAttr('style');

	//categoryMenu.superfish('init');
	
	//add class for width define
	//$('.sf-menu > li > ul').addClass('submenu-container clearfix');
	// loop through each sublist under each top list item
	/*$('.sf-menu > li > ul').each(function(){
		i = 0;
		//add classes for clearing
		$(this).each(function(){
			if ($(this).attr('class') != "category-thumbnail"){
				i++;
				if(i % 2 == 1)
				$(this).addClass('first-in-line-xs');
				else if (i % 5 == 1)
				$(this).addClass('first-in-line-lg');
			}
		});
	});*/
}

function mobileInit()
{

	categoryMenu.superfish('destroy');
	$('.sf-menu').removeAttr('style');

	mCategoryGrover.on('click', function(e){
		$(this).toggleClass('active').parent().find('ul.menu-content').stop().slideToggle('medium');
		return false;
	});

	$('.sf-menu > li > ul').addClass('menu-mobile clearfix').parent().prepend('<span class="menu-mobile-grover"></span>');

	$(".sf-menu .menu-mobile-grover").on('click', function(e){
		var catSubUl = $(this).next().next('.menu-mobile');
		if (catSubUl.is(':hidden'))
		{
			catSubUl.slideDown();
			$(this).addClass('active');
		}
		else
		{
			catSubUl.slideUp();
			$(this).removeClass('active');
		}
		return false;
	});




}

// change the menu display at different resolutions
function menuChange(status)
{
	status == 'enable' ? mobileInit(): desktopInit();
}


function handleClickMenu(){

} 



