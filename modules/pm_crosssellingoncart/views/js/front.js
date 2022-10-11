/**
 * pm_crosssellingoncart
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2017 - http://www.presta-module.com
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

setTimeout(function() {
	if (typeof($csocjqPm) == 'undefined') $csocjqPm = $;
	$csocjqPm(document).ready(function() {
		$csocjqPm(pm_crosssellingoncart.prefix).owlCarousel({
			items : parseInt(pm_crosssellingoncart.nbItems),
			itemsCustom : false,
			itemsDesktop : false,
			itemsDesktopSmall : false,
			itemsTablet : [768,parseInt(pm_crosssellingoncart.products_quantity_tablet)],
			itemsTabletSmall : false,
			itemsMobile : [479,parseInt(pm_crosssellingoncart.products_quantity_mobile)],
			slideSpeed : 200,
			paginationSpeed : 800,
			autoPlay : true,
			stopOnHover : true,
			goToFirstSpeed : 1000,
			navigation : false,
			navigationText : ["prev","next"],
			scrollPerPage : true,
			pagination : true,
			baseClass : "owl-carousel",
			theme : "owl-theme",
			mouseDraggable : false,
			responsiveBaseWidth: pm_crosssellingoncart.prefix == '#PM_CSOC' ?  window : $csocjqPm('.nyroModalCont, .mfp-content')
		});
		if (typeof(modalAjaxCart) == 'undefined' && typeof(ajaxCart) != 'undefined' && typeof(pm_reloadCartOnAdd) != 'undefined' && typeof(pm_csocLoopInterval) == 'undefined') {
			pm_csocLoopInterval = setInterval(function() {
				pm_reloadCartOnAdd(pm_crosssellingoncart.order_page_link);
			}, 500);
		}

		if ($csocjqPm('body#product').size() > 0) {
			// Remove product on CSOC
			$csocjqPm(document).on('click', '#PM_CSOC a.ajax_add_to_cart_button', function(e){
				e.preventDefault();
				var owl = $csocjqPm(pm_crosssellingoncart.prefix).data('owlCarousel');
				owl.removeItem(owl.currentItem);
				owl.reinit();

				if ($csocjqPm('#PM_CSOC .product-box').length <= 0) {
					$csocjqPm('#csoc-container').remove();
				}
			});
		}
	});
}, 50);