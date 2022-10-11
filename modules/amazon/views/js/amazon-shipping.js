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
var shippingInitialized1 = false;
$(document).ready(function () {
    if (shippingInitialized1) return;
    shippingInitialized1 = true;

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
    logtime('amazon-shipping.js overall', false);

    $('#shipping-template-switch-on, #shipping-template-switch-off').click(function () {
        $('#shipping-templates').slideToggle();
    });

    $('input[name="shipping[shipping_templates]"]', $('#menudiv-shipping')).click(function () {
        console.log($(this).attr('checked'));
       if ($(this).attr('checked'))
           $('#shipping-templates').show();
        else
           $('#shipping-templates').hide();
    });

    $('#menudiv-shipping').delegate('.amazon-tab-selector', 'click', function () {
        var target_div = $('#menudiv-shipping');

        if (!$(this).hasClass('active')) {
            var iso_code = $(this).attr('rel');

            $('.amazon-tab-selector', target_div).removeClass('active');
            $(this).addClass('active');
            $('.amazon-tab', target_div).hide();
            $('.amazon-tab[rel="' + iso_code + '"]', target_div).show();
        }
    });


    function updateShippingGroups(target_tab, id_lang, pAjax)
    {
        $('.shipping-groups-loader', target_tab).show();

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
                        $('.shipping-groups-success', target_tab).html('');

                        $.each(data.messages, function (m,message) {
                            $('.shipping-groups-success', target_tab).append(message+'<br />');
                        });
                        $('.shipping-groups-success', target_tab).show();
                    }

                    if (data.error)
                    {
                        $('.shipping-groups-warning', target_tab).html('');

                        $.each(data.errors, function (e,error) {
                            $('.shipping-groups-warning', target_tab).append(error+'<br />');
                        });
                        $('.shipping-groups-warning', target_tab).show();
                    }

                    if (typeof(data.groups) == 'object' && data.groups != null)
                    {
                        if (window.console)
                            console.log(data.groups);

                        $('.available-shipping-groups option:gt(0)', target_tab).remove();

                        $.each(data.groups, function (g,group) {
                            $('.available-shipping-groups', target_tab).append('<option>'+group+'</option>'+"\n");
                        });

                        $('.available-shipping-groups-container', target_tab).show();
                        $('.available-shipping-groups', target_tab).slideDown();
                    }
                }
                else
                {
                    $('.shipping-groups-error', target_tab).append(data).show();
                }

                if (data.pass && data.continue)
                {
                    setTimeout(function () {
                        updateShippingGroups(target_tab, id_lang, pAjax)
                    }, 30000);
                }
                else
                {
                    $('.shipping-groups-loader', target_tab).hide();
                    $('.shipping-groups-get', target_tab).attr('disabled', false);
                }

                if (data.debug && data.output)
                {
                    $('.shipping-groups-debug', target_tab).append(data.output).show();
                }

            },
            error: function (data) {
                $('.shipping-groups-loader', target_tab).hide();
                $('.shipping-groups-success', target_tab).hide();
                $('.shipping-groups-warning', target_tab).hide();
                $('.shipping-groups-get', target_tab).attr('disabled', false);
                if (window.console)
                    console.log(data);

                if (data.status == 200 && data.responseText)
                    $('.shipping-groups-warning', target_tab).html(data.responseText).show();
                else
                {
                    $('.shipping-groups-error', target_tab).html($('#shiping_ajax_error').val()).show();
                    if (typeof(data) == 'object' && data.responseText)
                        $('.shipping-groups-error', target_tab).append('<br />' + data.responseText).show();
                    else
                        $('.shipping-groups-error', target_tab).append(data).show();

                }
            }
        });
    }

    $('.shipping-groups-get', $('#menudiv-shipping')).click(function ()
    {
        var target_tab = $($(this).parents().get(2));

        $('.shipping-groups-get', target_tab).attr('disabled', true);

        if (window.console)
            console.log(target_tab);

        var id_lang = $(this).attr('rel');

        var pAjax = new Object();
        pAjax.url = $('#amazon_shipping_url').val() + '&action=groups&id_lang='+$('#id_lang').val()+'&amazon_lang=' + id_lang + '&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = $('#menudiv-shipping input').serialize();

        $('.shipping-groups-success', target_tab).html('').hide();
        $('.shipping-groups-warning', target_tab).html('').hide();
        $('.shipping-groups-error', target_tab).html('').hide();
        $('.shipping-groups-debug', target_tab).html('').hide();

        updateShippingGroups(target_tab, id_lang, pAjax);

    });


    logtime('amazon-shipping.js overall', true);
});



