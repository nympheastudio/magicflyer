/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @category  PrestaShop Module
 * @author    knowband.com <support@knowband.com>
 * @copyright 2015 knowband
 * @license   see file: LICENSE.txt
 */

$(document).ready(function() {

	$("#email, #customer_firstname, #customer_lastname").live('blur',function() {
		var email = $("#email").val();
		var fname = ($("#customer_firstname").val() !== undefined) ? $("#customer_firstname").val() : '';
		var lname = ($("#customer_lastname").val() !== undefined) ? $("#customer_lastname").val() : '';

		if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email))
		{
			$.ajax({
				type: 'POST',
				url: abd_ajax_url + '&ajax=true&action=add_guest_email&email='+email+'&fname='+fname+'&lname='+lname,
				cache: false,
				success: function(jsonData)
				{
				}
			});
		}
	});

	$("#login_email").blur(function() {
		var email = $("#login_email").val();
		if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email))
		{
			$.ajax({
				type: 'POST',
				url: abd_ajax_url + '&ajax=true&action=add_email&email=' + email,
				cache: false,
				success: function(jsonData)
				{
				}
			});
		}
	});

	$("#email_create").blur(function() {
		var email = $("#email_create").val();
		if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email))
		{
			$.ajax({
				type: 'POST',
				url: abd_ajax_url + '&ajax=true&action=add_email&email=' + email,
				cache: false,
				success: function(jsonData)
				{
				}
			});
		}
	});

	$("#guest_email, #firstname, #lastname").blur(function() {
		var email = $("#guest_email").val();
		var fname = ($("#firstname").val() !== undefined) ? $("#firstname").val() : '';
		var lname = ($("#lastname").val() !== undefined) ? $("#lastname").val() : '';
		if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email))
		{
			$.ajax({
				type: 'POST',
				url: abd_ajax_url + '&ajax=true&action=add_email&email=' + email+'&fname='+fname+'&lname='+lname,
				cache: false,
				success: function(jsonData)
				{
				}
			});
		}
	});
});
