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
var pageRepricing = false;

$(document).ready(function () {
    if (pageRepricing) return;
    pageRepricing = true;

    $('#menudiv-repricing').delegate('.amazon-tab-selector', 'click', function () {
        var target_div = $('#menudiv-repricing');

        if (!$(this).hasClass('active')) {
            var iso_code = $(this).attr('rel');

            $('.amazon-tab-selector', target_div).removeClass('active');
            $(this).addClass('active');
            $('.amazon-tab', target_div).hide();
            $('.amazon-tab[rel="' + iso_code + '"]', target_div).show();
        }
    });


    $('#menudiv-repricing .repricing-strategies').delegate('.strategie-create-header input', 'blur', function () {
        var target_div = $($(this).parents().get(2));
        $('.strategie-display-header td[rel=name] b', target_div).text($(this).val());
    });

    $('#menudiv-repricing .repricing-strategies').delegate('.strategy-minimize', 'click', function () {
        var target_div = $($(this).parents().get(2));

        if (!$('.strategie-create-header input', target_div).val() || !$('.strategie-create-header input', target_div).val().length)
            return (false);

        $('.strategie-display-header', target_div).show();
        $('.strategie-create-header', target_div).hide();
        $('.strategie-body', target_div).hide();
    });

    $('#menudiv-repricing .repricing-strategies').delegate('.strategy-edit', 'click', function () {
        var target_div = $($(this).parents().get(5));

        $('.strategie-display-header', target_div).hide();
        $('.strategie-create-header', target_div).show();
        $('.strategie-body', target_div).show();
    });

    $('#menudiv-repricing .repricing-strategies').delegate('.strategy-delete', 'click', function () {
        var target_div = $($(this).parents().get(5));

        target_div.slideUp().remove();
    });

    $('#menudiv-repricing').delegate('.strategies-add', 'click', function () {
        var target_div = $($(this).parents().get(2)),
            master = $('.repricing-strategie.master', target_div),
            cloned = master.clone();

        newRepricingId(cloned, master);

        cloned.appendTo($('.repricing-strategies', target_div));

        $('.strategie-display-header', cloned).hide();
        $('.strategie-create-header', cloned).show();
        $('.strategie-body', cloned).show();

        cloned.removeClass('master').slideDown();

        $('input', cloned).removeAttr('readonly');

        addTip(cloned);
    });

    // Generate new id for cloned element, prevent duplicate id
    function newRepricingId(clonedElement, masterElement) {
        var i, activeBlock = $('.strategie-body .strategie-active', clonedElement);

        ['yes', 'no'].forEach(function(i) {
            var input = activeBlock.find('.strategy-active-input-'+i),
                label = activeBlock.find('.strategy-active-label-'+i),
                id_counter = parseInt(input.attr('data-id-counter')),       // data may not copy when clone
                id_counter_new = id_counter + 1,
                id_segment = input.attr('id').split('-');

            if (id_segment.length > 0) {
                id_segment[id_segment.length - 1] = id_counter_new;
                var new_id = id_segment.join('-');

                input.attr('id', new_id);
                label.attr('for', new_id);
            }

            masterElement.find('.strategie-body .strategie-active .strategy-active-input-'+i).attr('data-id-counter', id_counter_new);
        });
    }


    $('#repricing-aws-check').click(function () {
        $('#repricing-aws-check-loader').show();

        $('#repricing-aws-check-success').html('').hide();
        $('#repricing-aws-check-warning').html('').hide();
        $('#repricing-aws-check-error').html('').hide();


        if ($('.amazon-tab-selector.active', $('#menudiv-repricing')).length == 1) {
            var lang = $('.amazon-tab-selector.active', $('#menudiv-repricing')).attr('rel');
        } else {
            var lang = $('.amazon-tab-selector.active:visible', $('#menudiv-repricing')).attr('rel');
        }

        var pAjax = new Object();
        pAjax.url = $('#amazon_repricing_url').val() + '&action=check&lang=' + lang + '&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = $('#menudiv-repricing input').serialize();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data,
            success: function (data) {
                $('#repricing-aws-check-loader').hide();

                if (window.console)
                    console.log(data);

                if (data.result)
                    $('#repricing-aws-check-success').html(data.result).show();
                else
                    $('#repricing-aws-check-warning').html(data).show();
            },
            error: function (data) {
                $('#repricing-aws-check-loader').hide();
                $('#repricing-aws-check-success').hide();
                $('#repricing-aws-check-warning').hide();

                if (window.console)
                    console.log(data);

                if (data.status == 200 && data.responseText)
                    $('#repricing-aws-check-warning').html(data.responseText).show();
                else {
                    $('#repricing-aws-check-error').html($('#repricing_ajax_error').val()).show();
                    if (typeof(data) == 'object' && data.responseText)
                        $('#repricing-aws-check-error').append('<br />' + data.responseText).show();
                }
            }
        });
    });


    $('.repricing-service-check, .repricing-service-cancel', $('#menudiv-repricing')).click(function () {

        var target_tab = $($(this).parents().get(2));

        $('.repricing-service-check-loader', target_tab).show();

        $('.repricing-service-check-success', target_tab).html('').hide();
        $('.repricing-service-check-warning', target_tab).html('').hide();
        $('.repricing-service-check-error', target_tab).html('').hide();

        var id_lang = $(this).attr('rel');

        if ($(this).hasClass('repricing-service-cancel'))
            var action = 'cancel-service';
        else if ($(this).hasClass('repricing-service-check'))
            var action = 'check-service';
        else
            return;

        if ($('.amazon-tab-selector.active', $('#menudiv-repricing')).length == 1) {
            var lang = $('.amazon-tab-selector.active', $('#menudiv-repricing')).attr('rel');
        } else {
            var lang = $('.amazon-tab-selector.active:visible', $('#menudiv-repricing')).attr('rel');
        }

        var pAjax = new Object();
        pAjax.url = $('#amazon_repricing_url').val() + '&action=' + action + '&lang=' + lang + '&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = $('#menudiv-repricing input').serialize();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data,
            success: function (data) {
                $('.repricing-service-check-loader', target_tab).hide();

                if (window.console)
                    console.log(data);

                if (data.result)
                    $('.repricing-service-check-success', target_tab).html(data.result).show();
                else
                    $('.repricing-service-check-warning', target_tab).html(data).show();
            },
            error: function (data) {
                $('.repricing-service-check-loader', target_tab).hide();
                $('.repricing-service-check-success', target_tab).hide();
                $('.repricing-service-check-warning', target_tab).hide();

                if (window.console)
                    console.log(data);

                if (data.status == 200 && data.responseText)
                    $('.repricing-service-check-warning', target_tab).html(data.responseText).show();
                else {
                    $('.repricing-service-check-error', target_tab).html($('#repricing_ajax_error').val()).show();
                    if (typeof(data) == 'object' && data.responseText)
                        $('.repricing-service-check-error', target_tab).append('<br />' + data.responseText).show();
                }
            }
        });
    });


    $('.repricing-queue-check', $('#menudiv-repricing')).click(function () {

        var target_tab = $($(this).parents().get(2));

        $('.repricing-queue-check-loader', target_tab).show();

        $('.repricing-queue-check-success', target_tab).html('').hide();
        $('.repricing-queue-check-warning', target_tab).html('').hide();
        $('.repricing-queue-check-error', target_tab).html('').hide();

        $('.repricing-queue-purge-success', target_tab).html('').hide();
        $('.repricing-queue-purge-warning', target_tab).html('').hide();
        $('.repricing-queue-purge-error', target_tab).html('').hide();

        $('.purge-queue-section', target_tab).hide();

        var id_lang = $(this).attr('rel');

        if ($('.amazon-tab-selector.active', $('#menudiv-repricing')).length == 1) {
            var lang = $('.amazon-tab-selector.active', $('#menudiv-repricing')).attr('rel');
        } else {

            var lang = $('.amazon-tab-selector.active:visible', $('#menudiv-repricing')).attr('rel');
        }

        var pAjax = new Object();
        pAjax.url = $('#amazon_repricing_url').val() + '&action=check-queue&lang=' + lang + '&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = $('#menudiv-repricing input').serialize();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data,
            success: function (data) {
                $('.repricing-queue-check-loader', target_tab).hide();

                if (window.console)
                    console.log(data);

                if (data.result) {
                    $('.repricing-check-queue-result', target_tab).html(data.result).show();

                    $('.purge-queue-section', target_tab).show();
                }
                else
                    $('.repricing-queue-check-warning', target_tab).html(data).show();
            },
            error: function (data) {
                $('.repricing-queue-check-loader', target_tab).hide();
                $('.repricing-queue-check-success', target_tab).hide();
                $('.repricing-queue-check-warning', target_tab).hide();

                if (window.console)
                    console.log(data);

                if (data.status == 200 && data.responseText)
                    $('.repricing-queue-check-warning', target_tab).html(data.responseText).show();
                else {
                    $('.repricing-queue-check-error', target_tab).html($('#repricing_ajax_error').val()).show();
                    if (typeof(data) == 'object' && data.responseText)
                        $('.repricing-queue-check-error', target_tab).append('<br />' + data.responseText).show();
                }
            }
        });
    });


    $('.repricing-queue-purge', $('#menudiv-repricing')).click(function () {

        var target_tab = $($(this).parents().get(2));

        $('.repricing-queue-purge-loader', target_tab).show();

        $('.repricing-queue-purge-success', target_tab).html('').hide();
        $('.repricing-queue-purge-warning', target_tab).html('').hide();
        $('.repricing-queue-purge-error', target_tab).html('').hide();

        var id_lang = $(this).attr('rel');

        if ($('.amazon-tab-selector.active', $('#menudiv-repricing')).length == 1) {
            var lang = $('.amazon-tab-selector.active', $('#menudiv-repricing')).attr('rel');
        } else {

            var lang = $('.amazon-tab-selector.active:visible', $('#menudiv-repricing')).attr('rel');
        }

        var pAjax = new Object();
        pAjax.url = $('#amazon_repricing_url').val() + '&action=purge-queue&lang=' + lang + '&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = $('#menudiv-repricing input').serialize();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data,
            success: function (data) {
                $('.repricing-queue-purge-loader', target_tab).hide();

                if (window.console)
                    console.log(data);

                if (typeof(data) == 'object' && data.result) {
                    $.each(data.queues, function (n, name) {
                        $('input[name="purge_queue[' + name + ']"]').attr('disabled', true).attr('checked', true);
                    });
                    $('.repricing-queue-purge-success', target_tab).html(data.result).show();
                }
                else
                    $('.repricing-queue-purge-warning', target_tab).html(data).show();
            },
            error: function (data) {
                $('.repricing-queue-purge-loader', target_tab).hide();
                $('.repricing-queue-purge-success', target_tab).hide();
                $('.repricing-queue-purge-warning', target_tab).hide();

                if (window.console)
                    console.log(data);

                if (data.status == 200 && data.responseText)
                    $('.repricing-queue-purge-warning', target_tab).html(data.responseText).show();
                else {
                    $('.repricing-queue-purge-error', target_tab).html($('#repricing_ajax_error').val()).show();
                    if (typeof(data) == 'object' && data.responseText)
                        $('.repricing-queue-purge-error', target_tab).append('<br />' + data.responseText).show();
                }
            }
        });
    });


    function addTip(obj) {
        $('label[rel]', obj).each(function () {

            var target_glossary_key = $(this).attr('rel');
            var target_glossary_div = $('#glossary div.glossary[rel="' + target_glossary_key + '"]');

            if (target_glossary_div && target_glossary_div.length) {
                if ($('span', this) && $('span', this))
                    var title = $('span', this).text();
                else
                    var title = null;

                $(this).qtip({

                    content: {
                        text: target_glossary_div.html(),
                        title: title
                    },
                    hide: {
                        fixed: true,
                        delay: 300
                    },
                    plugins: {}
                });
                $(this).addClass('tip');
            }
        });
    }

});