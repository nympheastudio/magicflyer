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

    $('button[name="amazon-cancel-button"], button[name="amazon-revert-button"]').click(function () {
    
        var params = '&amazon_token=' + $('#amazon_token').val() + '&id_order=' + $('#amazon_order_id').val() + '&amazon_id_lang=' + $('#amazon_id_lang').val() + '&context_key=' + $('#context_key').val() + '&debug=' + $('#amazon_debug').val() + '&action=cancel' + '&reason=' + $('#amazon-cancel').val() + '&cancel_status=' + $('#amazon_cancel_status').val();
            
        $('#amazon-cancel-loader').show();
    
        $('#amazon-cancel-success').html('').hide();
        $('#amazon-cancel-error').html('').hide();
    
        var pAjax = new Object();
        pAjax.url = $('#cancel_url').val() + '&action=cancel&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = params;
    
        $.ajax({
            success: function (data) {
                $('#amazon-cancel-loader').hide();

                if (window.console)
                    console.log(data);

                if (data.result && !data.error)
                {
                    $('#amazon-cancel-success').html(data.result).show();
                    $('button[name="amazon-cancel-button"]').attr('disabled', true);
                }
                else
                    $('#amazon-cancel-error').html(data.result).show();
            },
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data,
            error: function (data) {
                $('#amazon-cancel-loader').hide();
                $('#amazon-cancel-success').hide();

                if (window.console)
                    console.log(data);
    
                if (data.status == 200 && data.responseText)
                    $('#amazon-cancel-error').html(data.responseText).show();
                else {
                    $('#amazon-cancel-error').html($('#messaging_ajax_error').val()).show();
                    if (typeof(data) == 'object' && data.responseText)
                        $('#amazon-cancel-error').append('<br />' + data.responseText).show();
                }
            }
        });
    });

});

       