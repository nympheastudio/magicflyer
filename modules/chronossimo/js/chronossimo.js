function progressBar()
{
	$("#bSubmit").hide();
	$("#progressDiv").show();
	if ($.browser.msie) // Reload de l'image gif pour activer l'animation (BUG IE)
		$("#progressDiv img").attr("src", "/modules/chronossimo/ajax_loader.gif");
	if ($.browser.webkit)
		$("#progressbar").hide(); // Bug: xmlhttprequest ne fonctionne pas pendant le chargement d'une page, on cache donc la barre
	$("#progressDiv").center();
	$("#progressbar").reportprogress(0);
	
	window.setInterval(function updateProgressBar()
	{
		
		var script=document.createElement('script');
		script.type='text/javascript';
		script.id = 'progressScript';
		script.src='http://www.chronossimo.fr/autocom/progressScript.js?sessionId='+$("#sessionID").val();
		
		$("head").append(script);
		
		//$.getJSON('http://www.chronossimo.fr/autocom/progress?sessionId='+$("#sessionID").val(), function(json) {}); // Trop tot pour Access-Control-Allow-Origin
		
		
	}, 500);
}
function updateValueProgress(json)
{
	if (last_progress !== json.progress)
			{ // On met à jour
						$("#progressText").html(json.text);
						$("#progressbar").stopTime();
						var delai = (new Date()).getTime() - last_update;
						
						progress_step = parseFloat((json.progress - last_progress) * 100 / (delai / 1.5)); // On met a jour la progression au environ de 1.5 fois moins que la mise a jour precedente
						//progress_step = parseFloat((json['progress'] - last_progress) * 100 / 2000);

									// On sauvegarde les valeurs pour la suite
						progress_view = parseFloat(last_progress);

						last_progress = json.progress; // on ne parse pas pour garder le type identique a json [ if (last_progress !== json['progress']) ]
						last_update = (new Date()).getTime();						
						
						$("#progressbar").everyTime(100, function() {
							//alert(progress_step);
							if (progress_view < last_progress)
							{
								progress_view = parseFloat(parseFloat(progress_view) + parseFloat(progress_step));
								if (progress_view >= last_progress) // On corrige si arrondi superieur
									progress_view = last_progress;
							}
							else
							{
								$("#progressbar").stopTime();
							}
								
							$("#progressbar").reportprogress(progress_view);
						});
			}
}


jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", Math.max(0, (($(window).height() - this.outerHeight()) / 2) + 
                                                $(window).scrollTop()) + "px");
    this.css("left", Math.max(0, (($(window).width() - this.outerWidth()) / 2) + 
                                                $(window).scrollLeft()) + "px");
    return this;
}