/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @package   Amazon Market Place
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * Support by mail:  support.amazon@common-services.com
 */
var pageMessaging = false;

$(document).ready(function () {
    if (pageMessaging) return;
    pageMessaging = true;

    $('#test-invoice-button').click(function () {

        var id_order = parseInt($('#test-invoice-select').val());

        if (!id_order) {
            return(false);
        }

        $('#test-invoice-loader').show();

        $('#test-invoice-success').html('').hide();
        $('#test-invoice-error').html('').hide();

        var pAjax = new Object();
        pAjax.url = $('#amazon_tools_url').val() + '&action=test-invoice&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = '&id_order='+id_order;
        
        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data,
            success: function (data) {
                $('#test-invoice-loader').hide();

                if (window.console)
                    console.log(data);

                if (data.result && !data.error)
                    $('#test-invoice-success').html(data.result).show();
                else if (data.result && data.error)
                    $('#test-invoice-error').html(data.result + data.output).show();
                else
                    $('#test-invoice-error').html(data).show();
            },
            error: function (data) {
                $('#test-invoice-loader').hide();
                $('#test-invoice-success').hide();
                $('#test-invoice-error').hide();

                if (window.console)
                    console.log(data);

                if (data.status == 200 && data.responseText)
                    $('#test-invoice-error').html(data.responseText).show();
                else {
                    $('#test-invoice-error').html($('#messaging_ajax_error').val()).show();
                    if (typeof(data) == 'object' && data.responseText)
                        $('#test-invoice-error').append('<br />' + data.responseText).show();
                }
            }
        });
    });
    
    $('#test-review-button').click(function () {

        var id_order = parseInt($('#test-review-select').val());

        if (window.console) {
            console.log('Sending Review incentive test for order:'+id_order);
        }
        if (!id_order) {
            return(false);
        }

        $('#test-review-loader').show();

        $('#test-review-success').html('').hide();
        $('#test-review-error').html('').hide();

        var pAjax = new Object();
        pAjax.url = $('#amazon_tools_url').val() + '&action=test-review&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = '&id_order='+id_order;
        
        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data,
            success: function (data) {
                $('#test-review-loader').hide();

                if (window.console)
                    console.log(data);

                if (data.result)
                    $('#test-review-success').html(data.result).show();
                else
                    $('#test-review-error').html(data).show();
            },
            error: function (data) {
                $('#test-review-loader').hide();
                $('#test-review-success').hide();
                $('#test-review-error').hide();

                if (window.console)
                    console.log(data);

                if (data.status == 200 && data.responseText)
                    $('#test-review-error').html(data.responseText).show();
                else {
                    $('#test-review-error').html($('#messaging_ajax_error').val()).show();
                    if (typeof(data) == 'object' && data.responseText)
                        $('#test-review-error').append('<br />' + data.responseText).show();
                }
            }
        });
    });
});