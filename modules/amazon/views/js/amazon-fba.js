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

var fbaLoaded = false;
$(document).ready(function () {
    if (fbaLoaded) return;
    fbaLoaded = true;

    var start_time = [];

    function logtime(action, end)
    {
        if (!window.console)
            return(false);

        if (typeof(start_time[action]) == 'undefined' || start_time[action] == null)
            start_time[action] = new Date().getTime();

        if (end)
        {
            var end_time = new Date().getTime();

            console.log('Logtime for '+action+' duration:', end_time - start_time[action]);

            start_time[action] = null;
        }
    }
    logtime('amazon-fba.js overall', false);

    $('input[name="fba_stock_behaviour"]').click(function () {
        $('#fba-stock-init').slideToggle();
    });

    $('#menudiv-fba').delegate('.amazon-tab-selector', 'click', function () {
        var target_div = $('#menudiv-fba');

        if (!$(this).hasClass('active')) {
            var iso_code = $(this).attr('rel');

            $('.amazon-tab-selector', target_div).removeClass('active');
            $(this).addClass('active');
            $('.amazon-tab', target_div).hide();
            $('.amazon-tab[rel="' + iso_code + '"]', target_div).show();

            if (window.console) {
                console.log('current tab: ' + iso_code);
            }
        }
    });

    function updateStockInit(target_tab, id_lang, pAjax)
    {
        $('.stock-init-loader', target_tab).show();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data,
            success: function (data) {

                if (window.console)
                    console.log(data);

                if (typeof(data) == 'object')
                {
                    if (data.message)
                    {
                        $('.stock-init-success', target_tab).html('');

                        $.each(data.messages, function (m,message) {
                            $('.stock-init-success', target_tab).append(message+'<br />');
                        });
                        $('.stock-init-success', target_tab).show();
                    }

                    if (data.error)
                    {
                        $('.stock-init-warning', target_tab).html('');

                        $.each(data.errors, function (e,error) {
                            $('.stock-init-warning', target_tab).append(error+'<br />');
                        });
                        $('.stock-init-warning', target_tab).show();
                    }

                } else {
                    $('.stock-init-error', target_tab).append(data).show();
                }

                if (data.pass && data.continue) {
                    setTimeout(function () {
                        updateStockInit(target_tab, id_lang, pAjax)
                    }, 30000);
                } else {
                    $('.stock-init-loader', target_tab).hide();
                    $('.stock-init-get', target_tab).attr('disabled', false);
                }

                if (data.debug && data.output)
                {
                    $('.stock-init-debug', target_tab).append(data.output).show();
                }

            },
            error: function (data) {
                $('.stock-init-loader', target_tab).hide();
                $('.stock-init-success', target_tab).hide();
                $('.stock-init-warning', target_tab).hide();
                $('.stock-init-get', target_tab).attr('disabled', false);
                if (window.console)
                    console.log(data);

                if (data.status == 200 && data.responseText)
                    $('.stock-init-warning', target_tab).html(data.responseText).show();
                else
                {
                    $('.stock-init-error', target_tab).html($('#shiping_ajax_error').val()).show();
                    if (typeof(data) == 'object' && data.responseText)
                        $('.stock-init-error', target_tab).append('<br />' + data.responseText).show();
                    else
                        $('.stock-init-error', target_tab).append(data).show();

                }
            }
        });
    }

    $('.stock-init-get', $('#menudiv-fba')).click(function ()
    {
        var target_tab = $($(this).parents().get(2));

        $('.stock-init-get', target_tab).attr('disabled', true);

        if (window.console)
            console.log(target_tab);

        var lang = $(target_tab).attr('rel');
        var id_lang = $('#id_lang').val();

        var pAjax = new Object();
        pAjax.url = $('#amazon_stock_init_url').val() + '&action=inventory&id_lang='+id_lang+'&lang=' + lang + '&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = $('#menudiv-fba input').serialize();

        $('.stock-init-success', target_tab).html('').hide();
        $('.stock-init-warning', target_tab).html('').hide();
        $('.stock-init-error', target_tab).html('').hide();
        $('.stock-init-debug', target_tab).html('').hide();

        updateStockInit(target_tab, id_lang, pAjax);

    });

    $('.stock-init-delete', $('#menudiv-fba')).click(function ()
    {
        var target_tab = $($(this).parents().get(2));

        $('.stock-init-delete', target_tab).attr('disabled', true);

        if (window.console)
            console.log(target_tab);

        var lang = $(target_tab).attr('rel');
        var id_lang = $('#id_lang').val();

        var pAjax = new Object();
        pAjax.url = $('#amazon_stock_init_url').val() + '&action=delete&id_lang='+id_lang+'&lang=' + lang + '&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = $('#menudiv-fba input').serialize();

        $('.stock-init-success', target_tab).html('').hide();
        $('.stock-init-warning', target_tab).html('').hide();
        $('.stock-init-error', target_tab).html('').hide();
        $('.stock-init-debug', target_tab).html('').hide();

        updateStockInit(target_tab, id_lang, pAjax);

    });

    logtime('amazon-fba.js overall', true);

});