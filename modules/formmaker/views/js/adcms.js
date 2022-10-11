/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2015 silbersaiten
 * @version   0.0.1
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

$(function(){
	if (typeof(cmsbuilder) != 'undefined')
	{
		var formcms = {
			init: function(){
				cmsbuilder.registerSaveProcessor('form', function(containerIndex, blockIndex, fancyboxContent){
					var formData = fancyboxContent.find('select[name^=form_id_]');
					cmsbuilder.blockData[containerIndex].items[blockIndex].settings = {};
					cmsbuilder.blockData[containerIndex].items[blockIndex].settings['bsizelg'] = bsizelg;
					cmsbuilder.blockData[containerIndex].items[blockIndex].settings['bsizesm'] = bsizesm;
					cmsbuilder.blockData[containerIndex].items[blockIndex].settings['bsizexs'] = bsizexs;  

					if (formData.length)
					{
						cmsbuilder.blockData[containerIndex].items[blockIndex].content = {};
						
						formData.each(function(){
							var n = cmsbuilder.getLanguageInfoFromInputName($(this).attr('name'));
							
							if (n)
								cmsbuilder.blockData[containerIndex].items[blockIndex].content['lang_' + n.id_lang] = {'id_lang': n.id_lang, 'value': $(this).val()};
						});
					}
				});
				
				cmsbuilder.registerLoadProcessor('form', function(containerIndex, blockIndex, fancyboxContents, blockData){
			    	fancyboxContents.find('input[name=bsizelg]').val(blockData.settings.bsizelg ? blockData.settings.bsizelg : 4);
			    	fancyboxContents.find('input[name=bsizesm]').val(blockData.settings.bsizesm ? blockData.settings.bsizesm : 6);
			    	fancyboxContents.find('input[name=bsizexs]').val(blockData.settings.bsizexs ? blockData.settings.bsizexs : 12);
					for (var i in blockData.content)
						fancyboxContents.find('select[name=form_id_' + blockData.content[i].id_lang + ']').val(blockData.content[i].value);
				});
			}
		}
		
		formcms.init();
	}
});
