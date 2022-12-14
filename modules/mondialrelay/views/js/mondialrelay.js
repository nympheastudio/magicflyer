/**
 * 2007-2018 Mondial relay
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to Mondial relay so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Mondial relay to newer
 * versions in the future. If you wish to customize Mondial relay for your
 * needs please refer to Mondial relay for more information.
 *
 * @author    Mondial relay
 * @copyright 2007-2018 Mondial relay
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of Mondial relay
 */
var PS_MRObject = (function($, undefined) {

	var toggle_status_order_list = false;
	var toggle_history_order_list = false;
	var relay_point_max = 10;
	var last_gmap_info_clicked = '';

// /!\ All the following list data could be store into the same variable
// But for a better reading of the code, there are separated

// List of the supplier id which the user trying to fetch the relay
	var fetchingRelayPoint = new Array();

// List of the relay point object
	var relayPointDataContainers = new Array();

// List of the google map object
	var GmapList = new Object();

// List the marker liable to the relay pint
	var markerList = new Object();
	
	var selected = false;

	/**
	 * Toggle selected orders
	 */
	function toggleOrderListSelection()
	{
		toggle_status_order_list = !toggle_status_order_list;
		$('input[name="order_id_list[]"]').attr('checked', toggle_status_order_list);
	}

	/**
	 * Toggle selected histories
	 */
	function toggleHistoryListSelection()
	{
		toggle_history_order_list = !toggle_history_order_list;
		$('input[name="history_id_list[]"]').attr('checked', toggle_history_order_list);
	}

	/**
	 * Request Ajax call to get tickets
	 *
	 * @param detailedExpeditionList
	 */
	function getTickets(detailedExpeditionList)
	{
		$.ajax(
			{
				type : 'POST',
				url : MR_ajax_url,
				data :	{'detailedExpeditionList':detailedExpeditionList, 'method':'MRGetTickets', 'mrtoken':mrtoken, 'admin' : 1},
				dataType: 'json',
				success: function(json)
				{
					if (json && json.success)
					{
						$('#MR_error_histories').remove();
						for (id_order in json.success)
							if (json.success[id_order])
							{
								$('#URLA4_' + id_order).html('<a href="' + json.success[id_order].URLPDF_A4 + '">\
								<img width="20" src="' + _PS_MR_MODULE_DIR_ + 'views/img/pdf_icon.jpg" alt="download pdf"" /></a>');
								$('#URLA5_' + id_order).html('<a href="' + json.success[id_order].URLPDF_A5 + '">\
								<img width="20" src="' + _PS_MR_MODULE_DIR_ + 'views/img/pdf_icon.jpg" alt="download pdf"" /></a>');
								$('#URL10x15_' + id_order).html('<a href="' + json.success[id_order].URLPDF_A5 + '">\
								<img width="20" src="' + _PS_MR_MODULE_DIR_ + 'views/img/pdf_icon.jpg" alt="download pdf"" /></a>');
								$('#expeditionNumber_' + id_order).html(json.success[id_order].expeditionNumber);
								$('#detailHistory_' + id_order).children('td').children('input').attr('value', json.success[id_order].id_mr_history);
								$('#detailHistory_' + id_order).children('td').children('input').attr('id', 'PS_MRHistoryId_' + json.success[id_order].id_mr_history);
							}
					}
					displayBackGenerateSubmitButton();
					displayBackHistoriesSubmitButton();
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					displayBackGenerateSubmitButton();
				}
			});
	}

	/**
	 * Check error for the generated tickets
	 * @param json
	 */
	function checkErrorGeneratedTickets(json)
	{
		i = 0;
		$('.PS_MRErrorList').fadeOut('fast', function()
		{
			if ((++i >= $('.PS_MRErrorList').length) && json && json.error)
				for (id_order in json.error)
					if (json.error[id_order] && json.error[id_order].length)
					{
						$('#errorCreatingTicket_' + id_order).children('td').children('span').html('');
						$('#errorCreatingTicket_' + id_order).fadeOut('slow');
						$('#errorCreatingTicket_' + id_order).fadeIn('slow');
						for (numError in json.error[id_order])
							$('#errorCreatingTicket_' + id_order).children('td').children('span').append(json.error[id_order][numError] + '<br >');
					}
		});
		checkOtherErrors(json);
	}

	/**
	 * Check Errors code
	 * @param json
	 */
	function checkOtherErrors(json)
	{
		$('#otherErrors').fadeOut('fast', function()
		{
			if (json && json.other && json.other.error)
				for (numError in json.other.error)
					if (json.other.error[numError])
					{
						$('#otherErrors').fadeIn('slow');
						$('#otherErrors').children('span').html('');
						$('#otherErrors').children('span').append(json.other.error[numError]);
					}
		});
	}

	/**
	 * Check anything about generated tickets
	 * @param json
	 */
	function checkSucceedGenerateTickets(json)
	{
		detailedExpeditionList = new Array();

		i = 0;
		$('.PS_MRSuccessList').fadeOut('fast', function()
		{
			if ((++i >= $('.PS_MRSuccessList').length) && json && json.success)
			{
				for (id_order in json.success)
					if (json.success[id_order] && json.success[id_order].expeditionNumber)
					{
						$('#successCreatingTicket_' + id_order).children('td').children('span').html('');
						$('#PS_MRLineOrderInformation-' + id_order).remove();
						$('#successCreatingTicket_' + id_order).fadeIn('slow');
						detailedExpeditionList.push({'id_order':id_order, 'expeditionNumber':json.success[id_order].expeditionNumber});
						
						if (!$('#detailHistory_' + id_order).length)
						{
							$('#PS_MRHistoriqueTableList').append('\
							<tr id="detailHistory_' + id_order + '">\
								<td><input type="checkbox" class="history_id_list" name="history_id_list[]" value="' + id_order + '" /></td>\
								<td>' + id_order + '</td>\
								<td id="expeditionNumber_' + id_order + '"><img src="' + _PS_MR_MODULE_DIR_ + 'views/img/getTickets.gif" /></td>\
								<td id="URLA4_' + id_order + '"><img src="' + _PS_MR_MODULE_DIR_ + 'views/img/getTickets.gif" /></td>\
								<td id="URLA5_' + id_order + '"><img src="' + _PS_MR_MODULE_DIR_ + 'views/img/getTickets.gif" /></td>\
								<td id="URL10x15_' + id_order + '"><img src="' + _PS_MR_MODULE_DIR_ + 'views/img/getTickets.gif" /></td>\
							</tr>');
						}
						else
						{
							$('#detailHistory_' + id_order).children('#URLA4_' + id_order).html('<img src="' + _PS_MR_MODULE_DIR_ + 'views/img/getTickets.gif" />');
							$('#detailHistory_' + id_order).children('#URLA5_' + id_order).html('<img src="' + _PS_MR_MODULE_DIR_ + 'views/img/getTickets.gif" />');
							$('#detailHistory_' + id_order).children('#URL10x15_' + id_order).html('<img src="' + _PS_MR_MODULE_DIR_ + 'views/img/getTickets.gif" />');
							$('#detailHistory_' + id_order).children('#expeditionNumber_' + id_order).html('<img src="' + _PS_MR_MODULE_DIR_ + 'views/img/getTickets.gif" />');
						}
						$('#successCreatingTicket_' + id_order).fadeOut('slow');
					}
					
					$('.PS_MRSubmitButton').css('display', 'block');
			}
			if(detailedExpeditionList.length)
				return detailedExpeditionList;
		});
		return detailedExpeditionList;
	}

	/**
	 * Display the button to generate tickets
	 */
	function displayBackGenerateSubmitButton()
	{
		$('#PS_MRSubmitGenerateLoader').css('display', 'none');
		if ($('.order_id_list').length)
			$('#PS_MRSubmitButtonGenerateTicket').fadeIn('slow');
	}

	/**
	 * Display the button to delete histories
	 */
	function displayBackHistoriesSubmitButton()
	{
		$('#PS_MRSubmitDeleteHistoriesLoader').css('display', 'none');
		if ($('.history_id_list').length)
			$('#PS_MRSubmitButtonDeleteHistories').fadeIn('slow');
	}

	/**
	 * Request an ajax call to generate the tickets
	 */
	function generateTicketsAjax()
	{
		var order_id_list = new Array();
		var weight_list = new Array();
		var insurance_list = new Array();

		$('#PS_MRSubmitButtonGenerateTicket').css('display', 'none');
		$('#PS_MRSubmitGenerateLoader').fadeIn('slow');

		numSelected = $('input[name="order_id_list[]"]:checked').length;
		$('input[name="order_id_list[]"]:checked').each(function()
		{
			order_id_list.push($(this).val());
			weight_list.push(($('#weight_' + $(this).val()).val()) + '-' + $(this).val());
			insurance_list.push(($('#insurance_' + $(this).val()).val()) + '-' + $(this).val());
		});

		$.ajax(
			{
				type : 'POST',
				url : MR_ajax_url,
				data :	{'order_id_list' : order_id_list,
					'numSelected' : numSelected,
					'weight_list' : weight_list,
					'insurance_list' : insurance_list,
					'method' : 'MRCreateTickets',
					'mrtoken' : mrtoken,
                    'id_shop_selected' : id_shop_selected,
					'admin' : 1
				},
				dataType: 'json',
				success: function(json)
				{
					detailedExpeditionList = new Array();

					checkErrorGeneratedTickets(json);
					detailedExpeditionList = checkSucceedGenerateTickets(json);
					
					if (detailedExpeditionList.length)
						getTickets(detailedExpeditionList);
					else
						displayBackGenerateSubmitButton();
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					display_generate_button = true;
					displayBackGenerateSubmitButton();
				}
			});

		delete(order_id_list);
		delete(weight_list);
		delete(insurance_list);
	}

	/**
	 * Display deleted history details
	 */
	function displayDeletedHistoryInformation()
	{
		$('input[name="history_id_list[]"]:checked').each(function()
		{
			$(this).parent().parent().css('background-color', '#FFE2E3');
		});
		displayBackHistoriesSubmitButton();
	}

	/**
	 * Manage the removed histories id
	 *
	 * @param json
	 */
	function checkDeletedHistoriesId(json)
	{
		if (json && json.success)
		{
			// Allow to wait the end of the loop to manage unremoved item
			i = 0;
			for (numberHistoryId in json.success.deletedListId)
			{
				$('#PS_MRHistoryId_' + json.success.deletedListId[numberHistoryId]).parent().parent().fadeOut('fast', function() {
					$(this).remove();
					// Fadeout is asynchome verify everytime the number element
					if (++i == json.success.deletedListId.length)
						displayDeletedHistoryInformation(json.success.deletedListId.length);
				});
			}
			// Use if none element exist in the list
			if (i == json.success.deletedListId.length)
				displayDeletedHistoryInformation();
		}
		else
			displayBackHistoriesSubmitButton();
	}

	/**
	 * Delete the histories selected by the merchant
	 */
	function deleteSelectedHistories()
	{
		var history_id_list = new Array();

		$('#PS_MRSubmitButtonDeleteHistories').css('display', 'none');
		$('#PS_MRSubmitDeleteHistoriesLoader').fadeIn('slow');

		numSelected = $('input[name="order_id_list[]"]:checked').length;
		$('input[name="history_id_list[]"]:checked').each(function()
		{
			history_id_list.push($(this).val());
		});

		$.ajax(
			{
				type : 'POST',
				url : MR_ajax_url,
				data :	{'history_id_list' : history_id_list,
					'numSelected' : numSelected,
					'method' : 'deleteHistory',
					'mrtoken' : mrtoken},
				dataType: 'json',
				success: function(json)
				{
					checkOtherErrors(json);
					checkDeletedHistoriesId(json);
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					display_generate_button = true;
					displayBackHistoriesSubmitButton();
				}
			});
	}
	
	function PS_MRSubmitButtonPrintSelected(format) {
		var history_id_list = new Array();
		var history_id_list_str = '';		
		$('input[name="history_id_list[]"]:checked').each(function()
		{
			var id_order = $.trim($(this).parent().next().html());
			var expeditionNumber = $.trim($(this).parent().next().next().html());
			history_id_list.push({'id_order':id_order, 'expeditionNumber':expeditionNumber});
			expeditionNumber = strPad(expeditionNumber,8,0);				
			history_id_list_str += expeditionNumber+';';
		});
		
		if(history_id_list.length) {
			history_id_list_str = history_id_list_str.substr(0,(history_id_list_str.length-1));
			$.ajax(
				{
					type : 'POST',
					url : MR_ajax_url,
					data :	{
						'detailedExpeditionList' : history_id_list_str,
						'history_id_list' : history_id_list,
						'method' : 'MRDownloadPDF',
						'mrtoken' : mrtoken},
					dataType: 'json',
					success: function(json)
					{						
						var url = json.success[0];
						if(format == 4 && url!=null) {
							document.location.href= url.URL_PDF_A4;
						}
						if(format == 5 && url!=null) {
							document.location.href= url.URL_PDF_A5;
						}		
						if(format == '10x15' && url!=null) {
							document.location.href= url.URL_PDF_10x15;
						}							
					},
					error: function(xhr, ajaxOptions, thrownError)
					{
						;
					}
				});
		}
	}
	
	function strPad(input, length, string) {
    	string = string || '0'; input = input + '';
    	return input.length >= length ? input : new Array(length - input.length + 1).join(string) + input;
	}
	/**
	 * Display a fancy box displaying details about the
	 * backup of the database
	 */
	function PS_MRGetUninstallDetail(url)
	{
		$.ajax(
			{
				type: 'POST',
				url: MR_ajax_url,
				data: {'method' : 'uninstallDetail',
					'action' : 'showFancy',
					'href' : url,
					'mrtoken' : mrtoken},
				dataType: 'json',
				success: function(json)
				{
					((json.html.length) ?
						$.fancybox(json.html,
							{
								'autoDimensions'	: false,
								'width'						: 450,
								'height'					: 'auto',
								'transitionIn'		: 'none',
								'transitionOut'		: 'none',
								'onComplete'			: function()
								{
									PS_MRHandleUninstallButton(url);

									// Rewrite some css properties of Fancybox
									$('#fancybox-wrap').css('width', '');
									$('#fancybox-content').css('background-color', '');
									$('#fancybox-content').css('border', '');
								}
							})
						: '');
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					// Put debug to see error detail
				}
			});
		return false;
	}

	/**
	 * Handle the button when a user clicked on the uninstall button
	 */
	function PS_MRHandleUninstallButton(url)
	{
		$('#PS_MR_BackupAction').click(function()
		{
			$.fancybox.close();
			PS_MRBackupDatabase(url);
		});

		$('#PS_MR_UninstallAction').click(function()
		{
			window.location.href = url;
			$.fancybox.close();
			return true;
		});

		$('#PS_MR_StopUninstall').click(function()
		{
			$.fancybox.close();
			return false;
		});
	}

	/**
	 * Ajax call to keep the database of the module safe
	 */
	function PS_MRBackupDatabase(url)
	{
		$.ajax(
			{
				type: 'POST',
				url: MR_ajax_url,
				data: {'method' : 'uninstallDetail',
					'action' : 'backupAndUninstall',
					'mrtoken' : mrtoken},
				dataType: 'json',
				success: function(json)
				{
					url += '&keepDatabase=true';
					window.location.href = url;
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					// Put debug to see error detail
				}
			});
	}

	/**
	 * Add / update a entry to the selected carrier to the mr_selected table
	 * with the selected relay point information
	 *
	 * @param relayPointNumber
	 * @param id_carrier
	 */
	function PS_MRAddSelectedRelayPointInDB(relayPointNumber, id_carrier)
	{
		PS_MRSelectedRelayPoint['relayPointNum'] = relayPointNumber;

		// Ajax call to add the selection in the database (compatibility for 1.3)
		// But keep this way to add a selection better that the hook
		$.ajax({
			type: 'POST',
			url: MR_ajax_url,
			data: {'method' : 'addSelectedCarrierToDB',
				'relayPointInfo' : relayPointDataContainers[relayPointNumber],
				'id_carrier' : id_carrier,
				'id_mr_method' : PS_MRCarrierMethodList[id_carrier],
				'mrtoken' : mrtoken},
			success: function(json)
			{
				if (PS_MROPC && PS_MRData.PS_VERSION < '1.5')
					updateCarrierSelectionAndGift();
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
			}
		});
	}

	/**
	 *  Add / update a entry to the selected carrier to the mr_selected table
	 * Without relay point information
	 *
	 * @param id_carrier
	 */
	function PS_MRAddSelectedCarrierInDB(id_carrier)
	{
		PS_MRHideLastRelayPointList();
		// Make the request
		$.ajax({
			type: 'POST',
			url: MR_ajax_url,
			data: {'method' : 'addSelectedCarrierToDB',
				'id_carrier' : id_carrier,
				'id_mr_method' : PS_MRCarrierMethodList[id_carrier],
				'mrtoken' : mrtoken},
			success: function(json)
			{
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
			}
		});
	}

	/**
	 * Handle function when an user click on a shipping
	 *
	 * @param carrierSelected
	 * @param id_carrier
	 * @param MRLivraisonType
	 */
	function PS_MRCarrierSelectedProcess(carrierSelected, id_carrier, MRLivraisonType)
	{
		// Reset for any carrier changement
		if (MRLivraisonType != 'LD1' &&	MRLivraisonType != 'LDS' && MRLivraisonType != 'HOM')
		{
            if(PS_MRSelectedRelayPoint['relayPointNum'] == -1)
                PS_MRSelectedRelayPoint['relayPointNum'] = 0;
			// Seek Relay point if it doesn't a home delivery mode
			PS_MRGetRelayPoint(carrierSelected, MRLivraisonType);
		}
		else
		{
			// Simply add the selected carrier to the db, relay information will be empty
			PS_MRAddSelectedCarrierInDB(id_carrier);

			// Won't have any relay points
			PS_MRSelectedRelayPoint['relayPointNum'] = -1;
		}
	}

	/**
	 * Hide the last displayed relay point list
	 */
	function PS_MRHideLastRelayPointList()
	{
		$('.PS_MRSelectedCarrier').fadeOut('fast');
	}

	/**
	 * Check if the user select a carrier and a relay point if exist
	 */
	function PS_MRCheckSelectedRelayPoint()
	{
		var input;
		// Check if the input is linked to the module and look into
		// a temporary variable if a relay point has been selected
		if ((input = $('input[name=id_carrier]:checked')).length &&
			PS_MRCarrierMethodList[input.val()] != undefined &&
			PS_MRSelectedRelayPoint['relayPointNum'] == 0)
		{
			//$('#PS_MRSelectCarrierError').fadeIn('fast');
			alert(PS_MRTranslationList['errorSelection']);
			return false;
		}
		return true;
	}

	/**
	 * Link the generated relay point to an handle click
	 * Allow to add the selected relay point in the database
	 */
	function PS_MRHandleSelectedRelayPoint()
	{
		// Link all new generated relay point Selected button to an action
		$('.PS_MRSelectRelayPointButton').click(function()
		{
			// Unselect all previous selection (normaly juste one)
			$('.PS_MRFloatRelayPointSelected').each(function()
			{
				$(this).attr('class', 'PS_MRFloatRelayPointSelecteIt');
				$(this).children('a').text(PS_MRTranslationList['Select']);
			});
			// Make the Selection
			$(this).html(PS_MRTranslationList['Selected']);
			$(this).parent().attr('class', 'PS_MRFloatRelayPointSelected');

			// Get the info about the relay point (relayPoint_RelayPointNumber_IdCarrier)
			var tab = $(this).parent().parent().attr('id').split('_');

			// Store Separated data for the ajax query
			if (tab.length == 3)
			{
				var relayPointNumber = tab[1];
				var id_carrier = tab[2];
				PS_MRAddSelectedRelayPointInDB(relayPointNumber, id_carrier);
			}
		});
	}


	/**
	 * Display the relay point fetched
	 *
	 * @param json
	 * @param blockContent
	 * @param carrier_id
	 */
	function PS_MRDisplayRelayPoint(json, blockContent, carrier_id)
	{	
		if (typeof json != 'undefined' && typeof blockContent != 'undefined')
		{
			numberDisplayed = 0;

			// Disable Gmap for IE user
			//	if (!$.browser.msie)
			PS_MRCreateGmap(carrier_id);
			blockContent.fadeOut('fast', function()
			{
				$(this).children('td').html('');
				for (relayPoint in json.success)
				{
					// Check if the the content wasn't already displayed

                    if (json.success[relayPoint].Num != '') {
					    contentBlockid =  'relayPoint_' + json.success[relayPoint].Num + '_' + carrier_id;
                    }
					if (!$('#' + contentBlockid).size())
					{
						// Set translation if a preselection exist
						var BtTranslation = (PS_MRSelectedRelayPoint['relayPointNum'] == json.success[relayPoint].Num) ?
							PS_MRTranslationList['Selected'] : PS_MRTranslationList['Select'];

						var classSelection = (PS_MRSelectedRelayPoint['relayPointNum'] == json.success[relayPoint].Num) ?
							'PS_MRFloatRelayPointSelected' : 'PS_MRFloatRelayPointSelecteIt';

						$('<div class="PS_MRRelayPointInfo clearfix" id="' + contentBlockid + '">'
							+ '<img src="' + _PS_MR_MODULE_DIR_ + 'logo.png" />'
							+ '<p><b>' + json.success[relayPoint].LgAdr1 + '</b><br /> ' +  json.success[relayPoint].LgAdr3
							+ ' - ' + json.success[relayPoint].CP + ' - ' + json.success[relayPoint].Ville
							+ ' ' + json.success[relayPoint].Pays + '</p>'
							+ '<div class="' + classSelection + '">'
							+	'<a class="PS_MRSelectRelayPointButton">' + BtTranslation + '</a>'
							+ '</div> \
					</div>').appendTo($(this).children('td'));

						// Store all the object content to prevent an ajax request
						relayPointDataContainers[json.success[relayPoint].Num] = json.success[relayPoint];
						++numberDisplayed;
						// Display popup for IE user
						//if (!$.browser.msie)
						PS_MRAddGMapMarker(carrier_id, json.success[relayPoint].Num, contentBlockid);
						/*	else
						 $('#' + contentBlockid).children('p').click(function() {
						 PS_MROpenPopupDetail(json.success[relayPoint].permaLinkDetail);
						 });*/
					}
				} 
				PS_MRHandleSelectedRelayPoint();
				$(this).fadeIn('fast');
			});
		}
	}

	/**
	 * Display error about the fetch of the relay point
	 *
	 * @param errorList
	 * @param blockContent
	 */
	function PS_MRDisplayErrorRelayPoint(errorList, blockContent)
	{

		blockContent.fadeOut('fast', function()
		{
			$(this).children('td').html('');
			for (numError in errorList)
			{
				$('<div class="error">' + errorList[numError] + '</div>').appendTo($(this).children('td'));
			}
			$(this).fadeIn('fast');
		});
	}

	/**
	 * Fetch the relay point
	 *
	 * @param carrierSelected
	 */
	function PS_MRFetchRelayPoint(carrierSelected, MRLivraisonType)
	{
		carrier_id = carrierSelected.val().replace(',', ''); 
		// Block is an input, we need the 'tr' element
		blockTR =$('#MR_PR_list_'+carrier_id+' tr');
		// Add a new line to the table after the clicked parent element
		blockTR.after(' \
		<tr class="PS_MRSelectedCarrier" id="PS_MRSelectedCarrier_' + carrier_id + '"> \
			<td colspan="4"><div> \
				<img src="' + _PS_MR_MODULE_DIR_ + 'views/img/loader.gif" alt="" /> \
			</div> \
		</td></tr>');

		fetchingRelayPoint[carrier_id] = $('#PS_MRSelectedCarrier_' + carrier_id);
		$.ajax(
			{
				type: 'POST',
				url: MR_ajax_url,
				data: {'method' : 'MRGetRelayPoint',
					'id_carrier' : carrier_id,
					'ajax' : true,
					'step' : 2,
                    'mode_liv' : MRLivraisonType,
					'mrtoken' : mrtoken},
				dataType: 'json',
				success: function(json)
				{
					if (json && json.error && json.error.length)
						PS_MRDisplayErrorRelayPoint(json.error, $('#PS_MRSelectedCarrier_' + carrier_id));
					else if (json && json.success)
						PS_MRDisplayRelayPoint(json, $('#PS_MRSelectedCarrier_' + carrier_id), carrier_id);
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					// Put debug to see error detail
				}
			});
	}

	/**
	 * Display the relay point of a selected Carrier and keep fetched data
	 * in the html page (cache)
	 *
	 * @param carrierSelected
	 */
	function PS_MRGetRelayPoint(carrierSelected, MRLivraisonType)
	{
		carrier_id = carrierSelected.val().replace(',','');
		// Init back the inital view, hide existing element, (keep cached)
		element = 0;
		totalElement = $('.PS_MRSelectedCarrier').size();

		// Check if the element has already been fetched
		if (totalElement)
		// It Works like a foreach
			$('.PS_MRSelectedCarrier').fadeOut('fast', function()
			{
				if ((element + 1) >= totalElement)
				{
					// Check if the user already clicked and if the process is done
					if (typeof fetchingRelayPoint[carrier_id] != 'undefined')
					{
						fetchingRelayPoint[carrier_id].fadeIn('fast');
						return ;
					}
					// If the element isn't cached, we fetch it
					PS_MRFetchRelayPoint(carrierSelected, MRLivraisonType);
				}
				++element;
			});
		else
			PS_MRFetchRelayPoint(carrierSelected, MRLivraisonType);
	}

	/**
	 * Create the Gmap Block and cache the js object for the carrier
	 *
	 * @param id_carrier
	 */
	function PS_MRCreateGmap(id_carrier)
	{
		// This has been written this way because it needed to have a known block
		// present everytime in the page. Body is the only one sure.
		// It's an hidden block which will be put in the right block when user select his
		// own carrier
		$('body').prepend('\
		<tr id="PS_MRGmapDefaultPosition_' + id_carrier + '" class="PS_MRGmapDefaultPosition">\
			<td colspan="4"> \
				<div id="PS_MRGmap_' + id_carrier + '" class="PS_MRGmapStyle">\
		</div></td</tr>');
	}

	/**
	 * Resize the map when the div got changement about dimension / position and displaying
	 *
	 * @param $map
	 */
	function PS_MRGmapResizeEvent($map)
	{
		gmap = $map.gmap3({action:'get'});
		google.maps.event.trigger(gmap, 'resize');
	}

	/**
	 * Move the view of the gmap to a marker
	 * liable to the relay point
	 *
	 * @param $map
	 * @param marker
	 * @param relayNum
	 */
	function PS_MRGmapPlaceViewOnMarker($map, marker, relayNum)
	{
		$map.gmap3(
			{
				action:'panTo',
				args:[marker.position],
				callback: function()
				{
					PS_MRDisplayClickedGmapWindow(marker, relayNum, $map);

					// Make dancing markers in Firefox / IE  will use the CPU to 50 to 100 % about
					if (!$.browser.msie && !$.browser.mozilla)
						(function(m)
						{
							setTimeout(function()
							{
								m.setAnimation(google.maps.Animation.BOUNCE);
							}, 200);
						})(marker);
					//	marker.setAnimation(google.maps.Animation.BOUNCE);
				}
			});
	}

	/**
	 * Stop all dancing marker of the current selected carrier
	 *
	 * @param currentMarkerList
	 */
	function PS_MRStopDancingMarkers(currentMarkerList)
	{
		for (markerNumber in currentMarkerList)
			if (currentMarkerList[markerNumber] != undefined)
				if (currentMarkerList[markerNumber].getAnimation() != null)
					currentMarkerList[markerNumber].setAnimation(null);
	}

	/**
	 * Display the Gmap of the selected relay point
	 *
	 * @param contentBlockid
	 * @param $map
	 */
	function PS_MRDisplayGmap(contentBlockid, $map)
	{
		var tab = contentBlockid.split('_');
		var relayPointNumber = tab[1];
		var id_carrier = tab[2];

		// Stop the dancing marker of the current carrier
		PS_MRStopDancingMarkers(markerList[id_carrier]);
		if ($('#PS_MRGmap_' + id_carrier).css('display') == 'none')
		{
			$('#' + contentBlockid).after($('#PS_MRGmap_' + id_carrier));
			$('#PS_MRGmap_' + id_carrier).slideDown('fast', function()
			{
				PS_MRGmapResizeEvent($map);
				PS_MRGmapPlaceViewOnMarker($map, markerList[id_carrier][relayPointNumber], relayPointNumber);
			});
		}
		else
		{
			previousElem = $('#PS_MRGmap_' + id_carrier).prev();
			//$('#' + contentBlockid).after($('#PS_MRGmap_' + id_carrier));
			$('#PS_MRGmap_' + id_carrier).toggle('fast', function()
			{
				if (previousElem.attr('id') != contentBlockid)
				{
					$('#' + contentBlockid).after($(this));
					$('#PS_MRGmap_' + id_carrier).slideDown('fast', function()
					{
						PS_MRGmapResizeEvent($map);
						PS_MRGmapPlaceViewOnMarker($map, markerList[id_carrier][relayPointNumber], relayPointNumber);
					});
				}
			});
		}
	}

	/**
	 * Generate an html block to display the opening hours details
	 *
	 * @param relayInfo
	 */
	function PS_MRGetTimeRelayDetail(relayInfo)
	{
		var params = "\'height=200, width=400, top=100, left=100, toolbar=no, menubar=yes, location=no, resizable=yes, scrollbars=no, status=no\'";
		onClick = 'onClick="window.open(\'' + relayInfo.permaLinkDetail + '\', \'MondialRelay\', '+ params +' )"';

		var html = ' \
		<div class="PS_MRGmapBulbe"> \
			<img src="' + _PS_MR_MODULE_DIR_ + 'logo.png" width="10%" style="float:left;" /> \
			<p><b>' + relayInfo.LgAdr1 + '</b><br /> ' +  relayInfo.LgAdr3
			+ ' - ' + relayInfo.CP + ' - ' + relayInfo.Ville
			+ ' ' + relayInfo.Pays + '</p> \
			<a href="javascript:void(0)" ' + onClick + '>' + PS_MRTranslationList['moreDetails'] + '</a> \
		</div>';
		return html;
	}

	/**
	 * Call a MondialRelay page into a popup
	 *
	 * @param url
	 */
	function PS_MROpenPopupDetail(url)
	{
		window.open(url, 'MondialRelay',
			'height=200, width=400, top=100, left=100, toolbar=no, menubar=yes, \
					location=no, resizable=yes, scrollbars=no, status=no');
	}

	/**
	 * Display the gmap windows selected by the user
	 *
	 * @param marker
	 * @param relayNum
	 * @param mapObject
	 */
	function PS_MRDisplayClickedGmapWindow(marker, relayNum, mapObject)
	{
		if (last_gmap_info_clicked.length)
		{
			// Close the last opening window in gmap
			if ((lastWin = mapObject.gmap3({action:'get', name:'infowindow', tag:last_gmap_info_clicked})))
				lastWin.close();
		}

		// Open the selected detail window
		map = mapObject.gmap3('get');
		if ((currentWindow = mapObject.gmap3({action:'get', name:'infowindow', tag:relayNum})))
			currentWindow.open(map, marker);
		last_gmap_info_clicked = relayNum;
	}

	/**
	 * Add a new Marker to a Gmap for a carrier using the
	 * relay point information
	 *
	 * @param id_carrier
	 * @param relayPointNumber
	 * @param contentBlockid
	 */
	function PS_MRAddGMapMarker(id_carrier, relayPointNumber, contentBlockid)
	{
		// Check if the gmap has been properly created
		if (GmapList[id_carrier] == undefined)
			return ;
		var relayInfo = relayPointDataContainers[relayPointNumber];
		var detailContentHtml = PS_MRGetTimeRelayDetail(relayInfo);

		// Add Marker to the map
		var fullFormatedAddress = relayInfo.LgAdr1 + ' ' + relayInfo.LgAdr3 + ' ' +
			relayInfo.CP + ' ' + relayInfo.Ville + ' ' + relayInfo.Pays;
		GmapList[id_carrier].gmap3(
			{
				action: 'addMarker',
				address: fullFormatedAddress,
				tag:relayInfo.Num,
				map:
				{
					center: true,
					zoom: 14
				},
				marker: {
					events:
					{
						click:function(marker, event, data)
						{
							PS_MRDisplayClickedGmapWindow(marker, relayInfo.Num, $(this));
						}
					},
					callback: function(marker)
					{
						if (marker)
						{
							// Check if the a marker list exist for the carrier,
							if (markerList[id_carrier] == undefined)
								markerList[id_carrier] = new Object();

							// Store the marker in the markerList of the carrier
							markerList[id_carrier][relayPointNumber] = marker;

							// Link all relay point line info to an action
							$('#' + contentBlockid).children('p').click(function()
							{
								PS_MRDisplayGmap($(this).parent().attr('id'), GmapList[id_carrier]);
							});
							return true;
						}
						else
							$('#' + contentBlockid).children('p').click(function()
							{
								PS_MROpenPopupDetail(relayInfo.permaLinkDetail);
							});
					}
				},
				infowindow:
				{
					options: {content:detailContentHtml},
					tag:relayInfo.Num,
					callback: function(infowindow) {

						var windowList = $(this).gmap3({action:'get', name:'infowindow', all:true});
						$.each(windowList, function(i, elem) {
							elem.close();
						});
					}
				}
			});
		return false;
	}

	/**
	 * Display the selected form block for the configuration page
	 * @param block_id
	 */
	function PS_MRDisplayConfigurationForm(block_id)
	{
		var block_form_id = block_id + '_block';
		var total_form = $('.PS_MRFormType').length;
		var i = 0;
		$('.PS_MRFormType').each(function()
		{
			if ($(this).attr('id') != block_form_id)
				$(this).css('display', 'none');
			if ((i + 1) == total_form)
			{
				$('#' + block_form_id).fadeIn('fast');
				$('#' + block_id).parents('ul').children('li').attr('class', '');
				$('#' + block_id).parent().attr('class', 'selected');
			}
			++i;
		});

		if ($('#' + block_form_id).length == 0)
			$('#MR_error_account').fadeIn('fast');
	}
	
	function checkToDisplayRelayList()
	{
		if (typeof PS_MRData != 'undefined')
		{
			PS_MRSelectedRelayPoint['relayPointNum'] = PS_MRData.pre_selected_relay;
			
			if (PS_MRData.PS_VERSION < '1.5') {
				$('input[name="id_carrier"]').each(function(i, e){
					var parent_element = $(e).closest('tr');
					var new_element = 'MR_PR_list_'+$(e).val().replace(',', '');
					var MR_idcarrier = $(e).val().replace(',', '');
					MR_carrier = isMRCarrier(MR_idcarrier);
					
					if($('#'+new_element).length > 0) {
						$('#'+new_element).remove();
						if( isMRCarrier(MR_idcarrier)!=false && typeof(MR_carrier) != "undefined" && (MR_carrier.dlv_mode!='LD1' && MR_carrier.dlv_mode!='LDS' && MR_carrier.dlv_mode!='HOM')) {
							$(parent_element).after(
								'<tr><td colspan="10" style="border:0;"><div><table width="98%" id="'+new_element+'" class="trMRSelected" style="display:none;"><tr>'
								+	'<td style="border:0;"></td>'
								+'</tr></table></div>'
								+'</td></tr>'
								);
						}
					}
					else {
						if( isMRCarrier(MR_idcarrier)!=false ) {
							if(typeof(MR_carrier) != "undefined" && (MR_carrier.dlv_mode!='LD1' && MR_carrier.dlv_mode!='LDS' && MR_carrier.dlv_mode!='HOM'))
							{
								$(parent_element).after(
								'<tr><td colspan="10" style="border:0;"><div><table width="98%" id="'+new_element+'" class="trMRSelected" style="display:none;"><tr>'
								+	'<td style="border:0;"></td>'
								+'</tr></table></div>'
								+'</td></tr>'
								);
							}
						}
					}
					
					
					if($(e).attr('checked') == 'checked' || $(e).attr('checked')) {
						if(MR_carrier != false) {
							PS_MRCarrierMethodList[MR_idcarrier] = MR_carrier.id_mr_method;
							PS_MRSelectedRelayPoint['carrier_id'] = MR_idcarrier; 
							var new_element = 'MR_PR_list_'+MR_idcarrier;
					
							PS_MRCarrierSelectedProcess($(this), MR_idcarrier, MR_carrier.dlv_mode);
						
							if(MR_carrier.dlv_mode!='LD1' && MR_carrier.dlv_mode!='LDS' && MR_carrier.dlv_mode!='HOM')
								$('.trMRSelected').fadeIn('fast');
							else
								$('.trMRSelected').fadeOut('fast');
						}
						else {				
							PS_MRHideLastRelayPointList();	
							PS_MRSelectedRelayPoint['relayPointNum'] = -1;
							$('.trMRSelected').fadeOut('fast');
						}
					}
				});
			}
			else {
				$('input.delivery_option_radio').each(function(i, e){
					var MR_idcarrier = $(e).val().replace(',', '');
					MR_carrier = isMRCarrier(MR_idcarrier);
					var carrier_selected = $('input.delivery_option_radio:checked').val();
					var parent_element = $(e).parents('.delivery_option');
					var new_element = 'MR_PR_list_'+MR_idcarrier;
                    
					if($('#'+new_element).length > 0) {
						$('#'+new_element).remove();
						if( isMRCarrier(MR_idcarrier)!=false && typeof(MR_carrier) != "undefined" && (MR_carrier.dlv_mode!='LD1' && MR_carrier.dlv_mode!='LDS' && MR_carrier.dlv_mode!='HOM')) {
							$(parent_element).append(
							'<div><table width="98%" id="'+new_element+'" class="trMRSelected" style="display:none;"><tr>'
							+	'<td></td>'
							+'</tr></table></div>');
						}
					}
					else {
						if( isMRCarrier(MR_idcarrier)!=false && typeof(MR_carrier) != "undefined" && (MR_carrier.dlv_mode!='LD1' && MR_carrier.dlv_mode!='LDS' && MR_carrier.dlv_mode!='HOM')) {
							$(parent_element).append(
							'<div><table width="98%" id="'+new_element+'" class="trMRSelected" style="display:none;"><tr>'
							+	'<td></td>'
							+'</tr></table></div>');
						}
					}
					
					// Hide MR input if one of them is not selected 
					if($(e).val() == carrier_selected)
					{
						if(MR_carrier != false) {
							PS_MRCarrierMethodList[MR_idcarrier] = MR_carrier.id_mr_method;
							PS_MRSelectedRelayPoint['carrier_id'] = MR_idcarrier; 
							var new_element = 'MR_PR_list_'+MR_idcarrier;
					
							PS_MRCarrierSelectedProcess($(this), MR_idcarrier, MR_carrier.dlv_mode);
						
							if(MR_carrier.dlv_mode!='LD1' && MR_carrier.dlv_mode!='LDS' && MR_carrier.dlv_mode!='HOM')
								$('.trMRSelected').fadeIn('fast');
							else
								$('.trMRSelected').fadeOut('fast');
						}
						else {				
							PS_MRHideLastRelayPointList();	
							PS_MRSelectedRelayPoint['relayPointNum'] = -1;
							$('.trMRSelected').fadeOut('fast');
						}
					}
				});
			}
                        
		}
	}
	
	function isMRCarrier(id_carrier){
		var carrier_list = PS_MRData.carrier_list;
		for(i in carrier_list){
			var MR_carrier = carrier_list[i];
			if(MR_carrier.id_carrier == id_carrier) {
				return MR_carrier;
			}
		}
		return false;
	}

	$(document).ready(function()
	{
		$('#form').submit(function()
		{
			return PS_MRCheckSelectedRelayPoint();
		});
		$('#toggleStatusOrderList').click(function()
		{
			toggleOrderListSelection();
		});
		$('#toggleStatusHistoryList').click(function()
		{
			toggleHistoryListSelection();
		});
		$('#generate').click(function()
		{
			generateTicketsAjax();
		});
		$('#PS_MRSubmitButtonDeleteHistories').click(function()
		{
			deleteSelectedHistories();
		});
		$('#PS_MRSubmitButtonPrintSelectedA4').click(function()
		{
			PS_MRSubmitButtonPrintSelected(4);
		});
		$('#PS_MRSubmitButtonPrintSelectedA5').click(function()
		{
			PS_MRSubmitButtonPrintSelected(5);
		});
		$('#PS_MRSubmitButtonPrintSelected10x15').click(function()
		{
			PS_MRSubmitButtonPrintSelected('10x15');
		});

		// Shipping method list
		$('.send_disable_carrier_form').click(function() {
			$(this).parent('form').submit();
		});

		// Configuration form page
		$('#MR_config_menu a').each(function() {
			$(this).click(function() {
				PS_MRDisplayConfigurationForm($(this).attr('id'));
			});
		})

		if (typeof(PS_MR_SELECTED_TAB ) != 'undefined')
			$('#MR_' + PS_MR_SELECTED_TAB + '_block').fadeIn('fast');

		// Have the <li> elements centered (TODO: Change it using css if possible)
		if ($('#MR_config_menu').size())
		{
			var width = $('#MR_config_menu').width();

			// Take directly the ul width woudln't work
			var ul_width = 0;

			$('#MR_config_menu').find('ul > li').each(function() {

				var padding_left = parseInt($(this).css('padding-left').replace(/[^-\d\.]/g, ''));
				var padding_right = parseInt($(this).css('padding-right').replace(/[^-\d\.]/g, ''));

				ul_width += parseInt($(this).width()) + padding_left + padding_right;
			});

			width = ((width - ul_width) / 2);
			$('#MR_config_menu').children('ul').css('margin-left', width + 'px');
		}
		
/*
			// If MR carrier selected, check MR relay point is selected too
			$('input[name=processCarrier], button[name=processCarrier]').click(function(){  
				var _return = !(PS_MRSelectedRelayPoint['carrier_id'] && !PS_MRSelectedRelayPoint['relayPointNum']);
				if (!_return)
					alert(PS_MRTranslationList['errorSelection']);
				
				return _return;
			});
*/
		// En OPC, si l'utilisateur est d??connecter, PS_MRData n'existe pas au d??but
		$('input[name="id_carrier"]').live('click', function(){
			if (typeof PS_MRData != 'undefined' && PS_MRData.PS_VERSION < '1.5')
			{
				checkToDisplayRelayList();
			}
		});
			
			// Handle input click of the other input to hide the previous relay point list displayed
	});

	// To have public method access for this closure
	return {
		initFront : function() {
			checkToDisplayRelayList();
            setProtectRelaySelected();
		},
		uninstall : function(url)
		{
			return PS_MRGetUninstallDetail(url);
		}
	};
})(jQuery);




/**
 * Check connexion to webservice	 * 
 */
function mr_checkConnexion() {
	var enseigne = $("#MR_enseigne_webservice").val();
	var code_marque = $("#MR_code_marque").val();
	var key = $("#MR_webservice_key").val();
	$.ajax(
	{
		type : 'POST',
		url: MR_connexion_url,
		data :	"enseigne="+enseigne+"&code_marque="+code_marque+"&key="+key+"&token="+mrtoken,
		dataType: 'json',
		success: function(json)
		{
			if (json && json.success)
			{
				alert("Connection valide. Veuillez mettre ???? jour votre compte.");
			}
			else {
				if(json && json.error)
					alert(json.error);
				else
					alert("Service Indisponible");
			}
		} 
	});
}


