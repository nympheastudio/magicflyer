/**
* 2007-2015 PrestaShop
*
* Jms Theme Setting
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

(function($) {	
    $.JmsAjaxFunc = function() {
        this.requestData = 'jmsajax=1';
    };
    $.JmsAjaxFunc.prototype = {
        processAjax: function() {			
            var myElement = this;      
			myElement.getProductIDs();
			if(myElement.requestData != "jmsajax=1"){								
            $.ajax({
                type: 'POST',
                headers: {"cache-control": "no-cache"},
                url: baseDir + 'modules/jmsthemelayout/initajax.php' + '?rand=' + new Date().getTime(),
                async: true,
                cache: false,
                dataType: "json",
                data: myElement.requestData,
                success: function(jsonData) {					
                    if (jsonData) {						
						
                        if (jsonData.img2arr) {
                            var listProductImg = new Array();
                            for (i = 0; i < jsonData.img2arr.length; i++) {
                                listProductImg[jsonData.img2arr[i].id] = jsonData.img2arr[i].content;
                            }
                            $(".image-rollover").each(function() {
                                if (listProductImg[$(this).data("id-product")])									
									$( '<img class="img-responsive product-img2" title="" alt="" src="' + listProductImg[$(this).data("id-product")] + '"/>' ).insertAfter($(this).children('.product-img1'));									
                            });
                        }
                    }
                },
                error: function() {
                }
            });
            }
        },
		getProductIDs: function() {            
            var productimgs = "";
            $(".image-rollover").each(function() {
                if (!productimgs)
                    productimgs += $(this).data("id-product");
                else
                    productimgs += "," + $(this).data("id-product");
            });
            if (productimgs) {
                this.requestData += '&productids=' + productimgs;
            }						
            return false;
        }
    };
}(jQuery));

