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
$(document).ready(function () {
    // For PS 1.5017
    if ($('select[name="id_address"]'))
        $('select[name="id_address"]').css('width', '400px');

    var tab_selector = $('#amazon-order-ps16');

    if (tab_selector.length) {
        $(tab_selector).parent().insertBefore('#formAddPaymentPanel')
    }

    $('#amazon_get_details').click(function () {

        $('#order-fba-loader').show();
        $('#amazon-output').hide().html('');

        $.ajax({
            type: 'POST',
            url: $('#fbaorder_url').val(),
            data: 'amazon_token=' + $('#amazon_token').val() + '&id_order=' + $('#amazon_order_id').val() + '&amazon_id_lang=' + $('#amazon_id_lang').val() + '&context_key=' + $('#context_key').val() + '&debug=' + $('#amazon_debug').val() + '&action=info',
            dataType: 'jsonp',
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (parseInt($('#amazon_debug').val())) {
                    $('#amazon-output').append(data.output);
                    $('#amazon-output').append(data.response);
                    $('#amazon-output').append(data.error);
                }
                if (data.error != 'undefined' && !data.error) {
                    $('#order-fba-detail td[rel="ReceivedDateTime"]').html(data.info.ReceivedDateTime);
                    $('#order-fba-detail td[rel="StatusUpdatedDateTime"]').html(data.info.StatusUpdatedDateTime);
                    $('#order-fba-detail td[rel="FulfillmentMethod"]').html(data.info.FulfillmentMethod);
                    $('#order-fba-detail td[rel="FulfillmentOrderStatus"]').html(data.info.FulfillmentOrderStatus);
                    $('#order-fba-detail td[rel="DisplayableOrderId"]').html(data.info.DisplayableOrderId);
                    $('#order-fba-detail td[rel="Items"]').html(data.info.Items);
                    $('#order-fba-detail td[rel="ShippingSpeedCategory"]').html(data.info.ShippingSpeedCategory);
                    $('#order-fba-detail td[rel="EstimatedShipDateTime"]').html(data.info.EstimatedShipDateTime);
                    $('#order-fba-detail td[rel="EstimatedArrivalDateTime"]').html(data.info.EstimatedArrivalDateTime);
                    $('#order-fba-detail').show();
                    $('#order-fba-detail-spacer').show();
                    $('#order-fba-ajax-error').hide();
                }
                else {
                    if (data.error_message)
                        $('#order-fba-error-message').show().html(data.error_message);
                    else $('#order-fba-ajax-error').show();
                }

                if (data.errors && data.errors.length) {
                    $.each(data.errors, function (e, error) {
                        $('#order-fba-ajax-error').append('<br />' + error);
                    });
                    $('#order-fba-ajax-error').show();
                }

                $('#order-fba-loader').hide();
            },
            error: function (data) {
                if (data.status && data.status.length)
                    $('#amazon-output').append('<b>Status Code:' + data.status + '</b>');
                if (data.statusText && data.statusText.length)
                    $('#amazon-output').append('<b>Status Text:' + data.statusText + '</b>');
                if (data.responseText && data.responseText.length)
                    $('#amazon-output').append('<b>Response:</b>' + data.responseText);
                $('#amazon-output').show();
                $('#order-fba-ajax-error').show();
                $('#order-fba-loader').hide();
            }
        });

    });

    $('#amazon_cancel_fba').click(function () {

        if (!confirm($('#amazon_text_fba_cancel').val())) {
            return (false);
        }
        var fba_button = $(this);

        fba_button.attr('disabled', true);

        $('#order-fba-loader').show();
        $('#amazon-output').hide().html('');

        $.ajax({
            type: 'POST',
            url: $('#fbaorder_url').val(),
            data: 'amazon_token=' + $('#amazon_token').val() + '&id_order=' + $('#amazon_order_id').val() + '&amazon_id_lang=' + $('#amazon_id_lang').val() + '&context_key=' + $('#context_key').val() + '&debug=' + $('#amazon_debug').val() + '&action=cancel',
            dataType: 'jsonp',
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (parseInt($('#amazon_debug').val())) {
                    $('#amazon-output').append(data.output);
                    $('#amazon-output').append(data.response);
                    $('#amazon-output').append(data.error);
                }
                if (data.error != 'undefined' && !data.error) {
                    $('#order-fba-canceled').show();
                }
                else {
                    if (data.error_message)
                        $('#order-fba-error-message').show().html(data.error_message);
                    else $('#order-fba-ajax-error').show();
                }
                if (data.errors && data.errors.length) {
                    $.each(data.errors, function (e, error) {
                        $('#order-fba-ajax-error').append('<br />' + error);
                    });
                    $('#order-fba-ajax-error').show();
                }
                $('#order-fba-loader').hide();
                fba_button.attr('disabled', false);
            },
            error: function (data) {
                if (data.status && data.status.length)
                    $('#amazon-output').append('<b>Status Code:' + data.status + '</b>');
                if (data.statusText && data.statusText.length)
                    $('#amazon-output').append('<b>Status Text:' + data.statusText + '</b>');
                if (data.responseText && data.responseText.length)
                    $('#amazon-output').append('<b>Response:</b>' + data.responseText);
                $('#amazon-output').show();

                $('#order-fba-ajax-error').show();
                $('#order-fba-loader').hide();
                fba_button.attr('disabled', false);
            }
        });

    });

    $('#amazon_fba_create').click(function () {
        var fba_button = $(this);

        fba_button.attr('disabled', true);
        $('#amazon-output').hide().html('');
        $('#order-fba-loader').show();

        $.ajax({
            type: 'POST',
            url: $('#fbaorder_url').val(),
            data: 'amazon_token=' + $('#amazon_token').val() + '&id_order=' + $('#amazon_order_id').val() + '&amazon_id_lang=' + $('#amazon_id_lang').val() + '&context_key=' + $('#context_key').val() + '&debug=' + $('#amazon_debug').val() + '&action=create',
            dataType: 'jsonp',
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (parseInt($('#amazon_debug').val())) {
                    $('#amazon-output').append(data.output);
                    $('#amazon-output').append(data.response);
                    $('#amazon-output').append(data.error);
                }
                if (data.error != 'undefined' && !data.error) {
                    $('#order-fba-message').show().html(data.response);
                    $('#order-fba-ajax-error').hide();
                }
                else {
                    if (data.error_message)
                        $('#order-fba-error-message').show().html(data.error_message);
                    else $('#order-fba-ajax-error').show();
                }
                if (data.errors && data.errors.length) {
                    $.each(data.errors, function (e, error) {
                        $('#order-fba-ajax-error').append('<br />' + error);
                    });
                    $('#order-fba-ajax-error').show();
                }
                $('#order-fba-loader').hide();
                fba_button.attr('disabled', false);
            },
            error: function (data) {

                if (data.status && data.status.length)
                    $('#amazon-output').append('<b>Status Code:' + data.status + '</b>');
                if (data.statusText && data.statusText.length)
                    $('#amazon-output').append('<b>Status Text:' + data.statusText + '</b>');
                if (data.responseText && data.responseText.length)
                    $('#amazon-output').append('<b>Response:</b>' + data.responseText);
                $('#amazon-output').show();
                $('#order-fba-ajax-error').show();
                $('#order-fba-loader').hide();
                fba_button.attr('disabled', false);
            }
        });

    });

});

