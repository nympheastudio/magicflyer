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
$(document).ready(function() {
	$(window).scroll(function(){
			var s = $(window).scrollTop();
			var ontop=$('.stayontop').offset().top;
			// console.log("offset:"+ontop);
			// console.log("scrollTop:"+s);
			if(s>ontop){
				$('#mega_menu_plus').addClass("ontop");
			}else{
				$('#mega_menu_plus').removeClass("ontop");
			}
		});
});