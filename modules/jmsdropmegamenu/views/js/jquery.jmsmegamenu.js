/**
 * @package Jms Drop Megamenu
 * @version 1.0
 * @Copyright (C) 2009 - 2013 Joommasters.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Website: http://www.joommasters.com
**/

(function($){	
	$.fn.jmsMegaMenu = function(options){
		//set default options  
		var defaults = {
			openspeed: 100,
			closespeed: 400,
			event: 'hover'
		};

		//call in the default otions
		var options = $.extend(defaults, options);
		var $MegaMenuObj = this;
		
		//act upon the element that is passed into the design    
		return $MegaMenuObj.each(function(options){				
			_Init();
			
			function megaOver(){
				var subNav = $('.dropdown-menu',this);				
				$(this).addClass('open');
				if(subNav.length>0) {
					var rect = subNav[0].getBoundingClientRect();				
					if(rect.right > $(window).width()) {
						subNav.css('right','0px');
						subNav.css('left','auto');
					}
				}	
			}
			function megaAction(obj){
				var subNav = $('.dropdown-menu',obj);
				$(obj).addClass('open');
				
			}
			function megaOut(){				
				$(this).removeClass('open');
			}
			function megaActionClose(obj){				
				$(obj).removeClass('open');				
			}
			function megaReset(){
				$('li',$MegaMenuObj).removeClass('open');
			}

			function _Init(){
				$('> li',$MegaMenuObj).each(function(){					
					var cols = parseInt(this.getAttribute('data-cols'));
					var megacols = $('.mega-col',this);
					if(megacols.length < cols) {
						cols = megacols.length;
					}
					var innerItemWidth = 0 ;
					for(i=0;i<cols;i++) {						
						innerItemWidth = innerItemWidth + megacols.eq(i).outerWidth(true);
					}					
					var subNav = $('.mega-row',this);
					//subNav.css('width',innerItemWidth+'px');
					
					
				});
				if(defaults.event == 'hover'){
					var config = {
							sensitivity: 2, // number = sensitivity threshold (must be 1 or higher)
							interval: defaults.openspeed, // number = milliseconds for onMouseOver polling interval
							over: megaOver, // function = onMouseOver callback (REQUIRED)
							timeout: defaults.closespeed, // number = milliseconds delay before onMouseOut
							out: megaOut // function = onMouseOut callback (REQUIRED)
					};
					$('li',$MegaMenuObj).hoverIntent(config);
				}
				if(defaults.event == 'click'){
					
					$('body').mouseup(function(e){
						if(!$(e.target).parents('.open').length){
							megaReset();
						}
					});

					$('> li > a',$MegaMenuObj).click(function(e){
						var $parentLi = $(this).parent();
						if($parentLi.hasClass('open')){
							megaActionClose($parentLi);
						} else {
							megaAction($parentLi);
						}
						e.preventDefault();
					});
				}
			}
		});
	};
})(jQuery);