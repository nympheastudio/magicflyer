/*
 *  @license
 */
 
//On cache le tri par position si ya pas de filtrage sur la galerie
$(function(){
	var selector = 'select[name="responsivegallery_itemFilter_a!id_gallery"]';

	var doPositionEnabler = function(){
		var value = $(selector).val();
		if(value == ""){
			$('.dragHandle').hide()
				.before('<td class=".blank_td_to_keep_it_good"></td>');
		}
		else{
			$('.dragHandle').show()
				.parent().find('.blank_td_to_keep_it_good').remove();
		}
	}

	//events
	$(selector).change(function(){
		doPositionEnabler();
	});
	doPositionEnabler();
});

