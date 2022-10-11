/**
* 2007-2015 PrestaShop
*
* Jms Ajax Search
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/


$(document).ready(function() {
	$( "#ajax_search" ).keyup(function() {		
		var search_key = $( "#ajax_search" ).val();			
		$.ajax({
			type: 'GET',
			url: baseDir + 'modules/jmsajaxsearch/ajax_search.php',
			headers: { "cache-control": "no-cache" },
			async: true,    	
			data: 'search_key=' + search_key,
			success: function(data)
			{		
				$('#search_result').innerHTML = data;		
			}
		}) .done(function( msg ) {
$( "#search_result" ).html(msg);
});
	})	
	$('html').click(function() {
		$( "#search_result" ).html('');
	});

	$('#search_result').click(function(event){
		event.stopPropagation();
	});
});