$(document).ready(function() {
	
	$('.select-option').each(function() {		
		name = $(this).attr("name");		
		elemens = $.find('input[name="'+name+'"]');		
		for(i =0; i< elemens.length; i++){			
			if(!$(elemens[i]).attr("checked")){				
				$('.' + name + '-' +$(elemens[i]).val()).hide();
			}					
			
			$(elemens[i]).click(function() {
				subNameb   = $(this).attr("name");
				subElemens = $.find('input[name="'+subNameb+'"]');
				for(j =0; j< subElemens.length; j++){					
					if(!$(subElemens[j]).attr("checked")){						
						$('.' + $(subElemens[j]).attr("name") + '-' +$(subElemens[j]).val()).hide();
					}else{						
						$('.' + $(subElemens[j]).attr("name") + '-' +$(subElemens[j]).val()).show();
					}
				}				
			});
		}			
	});	
		
	
	$('.select-group').each(function() {
		currentValue = $(this).val();
		name = $(this).attr("name");		
		$(this).find("option").each(function(index,Element) {		
		    if($(Element).val() == currentValue){		    	
		    	$('.' + name + '-' + $(Element).val()).show();
		    }else{		    	
		    	$('.' + name + '-' + $(Element).val()).hide();
		    }
		});
	});	
	
	$('.select-group').change(function() {	   
		currentValue = $(this).val();
		name = $(this).attr("name");        		
		$(this).find("option").each(function(index,Element) {		
		    if($(Element).val() == currentValue){		          
		    	$('.' + name + '-' + $(Element).val()).show();
		    }else{
		    	$('.' + name + '-' + $(Element).val()).hide();
		    }
		});		
	});
});
function lofSelectAll(obj){	
	$(obj).find("option").each(function(index,Element) {
		$(Element).attr("selected","selected");
	});	
}