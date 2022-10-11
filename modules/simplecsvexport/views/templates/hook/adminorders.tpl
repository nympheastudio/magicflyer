{*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<script type="text/javascript">
	function sendCsvByMail() {
		$.ajax({
			type: "POST",
			url: "{$link->getAdminLink('simplecsvexport')}",
			async: true,
			dataType: "json",
			data: {
				ajax: "1",
				token: "{$token_simple|escape:'htmlall':'UTF-8'}",
				tab: "AdminSimpleCsvExport",
				action: "sendCsvByMail",
				id_order: {$id_order|escape:'htmlall':'UTF-8'}
			},
			success: function(res)
            {
							if (res.found)
                {
									$('#resultMsg').html('{l s='Order was exported with success' mod='simplecsvexport'}');
									$('#resultMsg').addClass("conf confirm");
									$('#resultMsg').show();
								}
								else
                {					
									$('#resultMsg').html('{l s='Error: please check your email configuration' mod='simplecsvexport'}');
									
									$('#resultMsg').addClass('error');
									$('#resultMsg').show();
								}
							}
						});
					}
</script>
<br>
<div id="resultMsg" class="warn" style="display:none;"></div>
<div class="clear">&nbsp;</div>
<input type="hidden" value="1" name="export_csv">
<input onClick='return sendCsvByMail()' class="button" type="submit" value="{l s='Export this order by email' mod='simplecsvexport'}" name="submitExportOrderCSV" />
