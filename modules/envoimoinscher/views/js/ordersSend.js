/**
 * 2007-2018 PrestaShop
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
 * @author    EnvoiMoinsCher <api@boxtal.com>
 * @copyright 2007-2018 PrestaShop SA / 2011-2016 EnvoiMoinsCher
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registred Trademark & Property of PrestaShop SA
 */

$(document).ready(function () {
    if (ordersTodo > 0 && withCheck == 0 && massOrderPassed != 1) {
        doOrderRequest();
    } else if (massOrderPassed == 1) {
        showFinalMessage();
    }
    $('#selectOrDeselectAll1').click(function () {
        selectDeselectAll('#ORDERSTABLE1 tbody input[type="checkbox"]', this);
        enableMassOrderWhithoutCheck();
    });
    $('#ORDERSTABLE1 tbody input[type="checkbox"]').click(function () {
        checkboxClick(this, "#selectOrDeselectAll1", 1);
    });
});

function doOrderRequest()
{
    $.ajax({
        url: orderActionUrl,
        type: "POST",
        dataType: "json",
        success: function (res) {
            tries = 0;
            ordersTodo--;
            ordersDone++;
            $("#done").html(ordersDone);
            if (res.id != '') {
                $('#row-' + res.id).remove();
                // hide the send array if it is empty
                if ($('#ORDERSTABLE1 tbody tr').length == 0) {
                    $('#orderDo1').hide();
                }

            }
            if (ordersTodo == 0 || res.doOthers == 0) {
                showFinalMessage();
                return false;
            }
            doOrderRequest();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (tries < 3) {
                tries++;
                doOrderRequest();
            } else {
                alert(order_translation.error_occured+" " + errorThrown + ". "+ order_translation.please_refresh);
            }
        }
    });
}

function showFinalMessage()
{
    $.ajax({
        url: orderResultUrl,
        type: "GET",
        dataType: "json",
        success: function (res) {
            var target = "#okResult";
            var message = "<p class='mass_order_mess'>" + order_translation.orders_successfully_sent + "</p>";
            if (res.stats.errors > 0) {
                target = "#errorResult";
                message = "<p class='mass_order_mess'>" + order_translation.orders_successfully_sent_except + "</p>";
                for (var i = 0; i < res.errors.length; i++) {
                    var errorLine = res.errors[i];
                    message += "<p class='order_error'>ID <span>" + errorLine.id + ":</span> " + errorLine.message + "</p>";
                }
                message += "<p>"+order_translation.orders_not_sent+"</p>";
            }
            message += "<p>"+order_translation.page_refresh+"</p>";
            $("#massStats").remove();
            $(target).html(message);
            $(target).show();
            setTimeout(function () {
                window.location.reload();
                if($('.blockButtons ').hasClass('hidden')){
                    $('.blockButtons ').removeClass('hidden');
                }
            }, 10000);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (tries < 3) {
                tries++;
                showFinalMessage();
            } else {
                alert(order_translation.error_occured+" " + errorThrown + ". "+ order_translation.please_refresh)
            }
        }
    });
}