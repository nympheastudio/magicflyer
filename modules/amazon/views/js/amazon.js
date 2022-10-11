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

var pageInitialized1 = false;
$(document).ready(function () {
    if (pageInitialized1) return;
    pageInitialized1 = true;
    var start_time = [];
    var context = $('#content');

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
    logtime('Amazon.js Overall', false);

    if ('function' !== typeof($.fn.prop)) {
        jQuery.fn.extend({
            prop: function() {
                return this;
            }
        });
    }

    var chosen_params = {
        'width': '230px',
        'search_contains': true,
        'placeholder_text_single': $('#text-add-select-option').val(),
        'no_results_text': $('#text-add-select-no-result').val()
    };

    logtime('PTC', false);

    var ptc_chosen_params = {
        'width': '500px',
        'search_contains': true,
        'placeholder_text_single': $('#text-add-select-option').val(),
        'no_results_text': $('#text-add-select-no-result').val()
    };
    $('div[id^=menudiv] .tax-rule select', context).chosen(ptc_chosen_params);
    $('div[id^=menudiv] .ptc select', context).chosen(ptc_chosen_params);

    logtime('PTC', true);

    /* Glossary */

    logtime('Tipping glossaries', false);

    if (window.console) {
        console.log('Glossary count: '+ $('label[rel], li[rel]', context).length);
    }


    $('label[rel], li[rel]', context).not('#menudiv-profiles label, #menudiv-profiles li', context).each(function () {

        var target_glossary_key = $(this).attr('rel');

        var target_glossary_div = $('#glossary div.glossary[rel=' + target_glossary_key + ']'); // peformance trouble
        //var target_glossary_div = $('#glossary.glossary.' + target_glossary_key);

        if (target_glossary_div && target_glossary_div.length) {
            if ($('span', this) && $('span', this).length)
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

    logtime('Tipping glossaries', true);

    // Display Amazon Status on page load
    //
    function DisplayServiceStatus(statuses)
    {
        $('#informations-table-heading').show();
        $('#informations-trailer').show();
        $('table.table.amz-seller-accounts tbody :visible').remove();

        logtime('Display Service Status', false);

        // Display Status for every platform
        //
        for (var merchant in statuses) {
            status = statuses[merchant];
            console.log(status);

            line1 = $('#amz-informations-model-1').clone().appendTo('table.table.amz-seller-accounts tbody').show();
            line1.children('[rel=merchant]').html(merchant);
            line1.children('[rel=platform]').html(statuses[merchant].platform);
            line1.children('[rel=datetime]').html(statuses[merchant].datetime);
            line1.children('[rel=drift]').html(statuses[merchant].drift);
            line1.children('[rel=status]').html('<img src="' + statuses[merchant].image + '" alt="' + statuses[merchant].status + '" />');

            line2 = $('#amz-informations-model-2').clone().appendTo('table.table.amz-seller-accounts tbody').show();
            line2.children('[rel=message]').html(statuses[merchant].messages);
        }

        // Display Marketplaces Participations
        //
        var pAjax = new Object();
        pAjax.url = $('#check_url').val();
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = $('input[name^="merchantId"], input[name^="marketPlaceId"], input[name^="awsKeyId"], input[name^="awsSecretKey"], input[name^="mwsToken"], select[name^="marketPlaceRegion"], select[name^="marketPlaceCurrency"]').serialize();

        $('#participation-loader').show();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: 'id_lang=' + $('#id_lang').val() + '&action=participations&' + pAjax.data,
            success: function (data) {
                $('#participation-loader').hide();

                if (window.console)
                    console.log(data);

                $('#participation-error').hide();
                $('#participation-table-heading').show();
                $('table.table.amz-participation tbody :visible').remove();

                if (typeof(data) == 'object' && data.result != 'undefined') {
                    $('#participation-debug').text(data.result);
                }

                if (!data.error) {
                    for (var merchant in data.participations) {
                        if (window.console)
                            console.log(merchant);

                        for (marketplace in data.participations[merchant]) {

                            var marketplaceInfo = data.participations[merchant][marketplace];
                            var line = $('#amz-participation-model').clone().appendTo('table.table.amz-participation tbody').show();
                            line.children('[rel=merchant]').html(merchant);
                            line.children('[rel=name]').html(marketplaceInfo.name);
                            line.children('[rel=cc]').html(marketplaceInfo.cc);
                            line.children('[rel=currency]').html(marketplaceInfo.currency);
                            line.children('[rel=l_status]').html('<img src="' + marketplaceInfo.l_image + '" alt="' + marketplaceInfo.l_status + '" />');
                            line.children('[rel=r_status]').html('<img src="' + marketplaceInfo.r_image + '" alt="' + marketplaceInfo.r_status + '" />');

                            if (!marketplaceInfo.currency_state)
                                line.children('[rel=currency]').css({'color': 'red', 'font-weight': 'bold'});
                        }
                    }
                }
                else if (data.error && data.errors) {
                    $('#participation-error').html(data.errors).show();
                }
                else {
                    $('#participation-error').html($('#infos_ajax_error').val()).show();
                }
                logtime('Display Service Status', true);
            },
            error: function (data) {
                $('#participation-loader').hide();
                $('#participation-error').html($('#infos_ajax_error').val()).show();
            }
        });
    }


    $('.participation-label', $('#menudiv-informations')).click(function () {
        $('#participation-debug').toggle();
    });

    $(function () {
        $('#status-loader', context).show();

        var pAjax = new Object();
        pAjax.url = $('#check_url').val();
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = $('input[name^="merchantId"], input[name^="marketPlaceId"], input[name^="awsKeyId"], input[name^="awsSecretKey"], input[name^="mwsToken"], select[name^="marketPlaceRegion"], select[name^="marketPlaceCurrency"]', context).serialize();

        if (window.console)
            console.log(pAjax);

        logtime('Marketplace Statuses', false);

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: 'id_lang=' + $('#id_lang', context).val() + '&action=status&' + pAjax.data,
            success: function (data) {
                $('#status-loader').hide();

                if (window.console)
                    console.log(data);

                if (!data.error) {
                    DisplayServiceStatus(data.statuses);
                }
                else if (data.error && data.errors) {
                    $('#status-error', context).html(data.errors).show();
                    $('#participation-loader', context).hide();
                }
                else {
                    $('#status-error', context).html($('#infos_ajax_error', context).val()).show();
                    $('#participation-loader', context).hide();
                }
                logtime('Marketplace Statuses', true);
            },
            error: function (data) {
                $('#status-loader', context).hide();
                $('#participation-loader', context).hide();
                $('#status-error', context).html($('#infos_ajax_error', context).val()).show();

                logtime('Marketplace Statuses', true);
            }
        });
    });


    
    // AJAX Checker
    //
    $(function () {
        var pAjax = new Object();
        pAjax.url = $('#env_check_url').val();
        pAjax.type = 'GET';
        pAjax.data_type = 'jsonp';
        pAjax.data = null;

        logtime('Ajax Check', false);

        var to_display = '#error-' + $('#env_check_url').attr('id');

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (!data.pass) {
                    $('#amz-env-infos').show();
                    $(to_display).show();
                }
                logtime('Ajax Check', true);
            },
            error: function (data) {
                if (window.console)
                    console.log(data);
                $('#amz-env-infos').show();
                $(to_display).show();
            }
        });
    });


    // Amazon Pinger
    //
    $(function () {
        var pAjax = new Object();
        pAjax.url = $('#service_check_url').val();
        pAjax.type = 'GET';
        pAjax.data_type = 'jsonp';
        pAjax.data = null;

        logtime('Ajax Check', false);

        var to_display = '#error-' + $('#service_check_url').attr('id');

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (!data.pass) {
                    $('#amz-env-infos').show();
                    $(to_display).show();
                }
                logtime('Ajax Check', true);
            },
            error: function (data) {
                if (window.console)
                    console.log(data);
                $('#amz-env-infos').show();
                $(to_display).show();
            }
        });
    });

    // max_input_vars checker
    //
    function EnvCheck() {
        logtime('Env Check', false);

        var max_input_vars = parseInt($('#max_input_vars').val());
        var cur_input_vars = $('input, select, textarea, button').length;

        if (max_input_vars && max_input_vars < cur_input_vars) {
            $('#error-max_input_vars').show();
            $('#amz-env-infos').show();
        }
        if ($('#amz-env-infos div > div[rel="toshow"]').length) {
            $('#amz-env-infos').show();
        }
        logtime('Env Check', true);
    };

    EnvCheck();

    $('#valid-values-button').click(function () {
        // Valid Values loader
        $(function () {

            $('#valid-values-success').html('').hide();
            $('#valid-values-warning').html('').hide();
            $('#valid-values-error').html('').hide();

            $('#valid-values-loader').hide();

            ValidValues('download-valid-values');
        });
    });

    $('#valid-values-delete-button').click(function () {
        if (confirm($('#valid-values-delete-text').val())) {
            $('#valid-values-success').html('').hide();
            $('#valid-values-warning').html('').hide();
            $('#valid-values-error').html('').hide();

            $('#valid-values-loader').hide();

            ValidValues('delete-valid-values');
        }

    });

    function ValidValues(action)
    {
        var pAjax = new Object();

        pAjax.url = $('#amazon_tools_url').val();
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = null;

        if (window.console)
            console.log(pAjax);

        logtime('Valid Values', false);

        $('#valid-values-loader').show();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: 'id_lang=' + $('#id_lang').val() + '&action=' + action + '&' + pAjax.data,
            success: function (data) {
                $('#valid-values-loader').hide();

                if (window.console)
                    console.log(data);

                if (typeof(data.warning) == 'object' && data.warning.length) {
                    $('#valid-values-warning').show();
                    $.each(data.warning, function (w, warning) {
                        $('#valid-values-warning').append(warning + '<br />');
                    });
                }

                if (typeof(data.output) == 'string' && data.output.length)
                {
                    $('#valid-values-warning').show();
                    $('#valid-values-warning').append(data.output);
                }

                if (typeof(data.error) == 'object' && data.error.length)
                {
                    $('#valid-values-error').show();
                    $.each(data.error, function (e, error) {
                        $('#valid-values-error').append(error + '<br />');
                    });
                }

                if (typeof(data.success) == 'object' && data.success.length)
                {
                    $('#valid-values-success').show();
                    $.each(data.success, function (s, success) {
                        $('#valid-values-success').append(success + '<br />');
                    });
                }
                if (typeof(data.console) == 'object' && data.console.length)
                    $('#valid-values-error').append(data.console).show();

                if (data.pass && data.continue)
                    ValidValues('install-valid-values');

                logtime('Valid Values', true);
            },
            error: function (data) {
                $('#valid-values-loader').hide();
                $('#valid-values-success').hide();
                $('#valid-values-warning').hide();
                $('#valid-values-error').show();
                $('#valid-values-error').html($('#tools_ajax_error').val()).show();

                if (data.status && data.status.length)
                    $('#valid-values-error').append('<pre>Status:' + data.status + '</pre>');
                if (data.statusText && data.statusText.length)
                    $('#valid-values-error').append('<pre>Status:' + data.statusText + '</pre>');
                if (data.responseText && data.responseText.length)
                    $('#valid-values-error').append('<pre>' + data.responseText + '</pre>');

            }
        });
    }


    $('#maintenance-carrier').click(function () {
        $(function () {

            $('#maintenance-success').html('').hide();
            $('#maintenance-warning').html('').hide();
            $('#maintenance-error').html('').hide();

            $('#maintenance-loader').hide();

            Maintenance('carriers-update');
        });
    });

    $('#maintenance-delete-translations').click(function () {
        // Valid Values loader
        $(function () {

            $('#maintenance-success').html('').hide();
            $('#maintenance-warning').html('').hide();
            $('#maintenance-error').html('').hide();

            $('#maintenance-loader').hide();

            Maintenance('delete-translations');
        });
    });

    $('#maintenance-delete-models').click(function () {
        // Valid Values loader
        $(function () {

            $('#maintenance-success').html('').hide();
            $('#maintenance-warning').html('').hide();
            $('#maintenance-error').html('').hide();

            $('#maintenance-loader').hide();

            Maintenance('delete-models');
        });
    });

    function Maintenance(action) {
        var pAjax = new Object();

        pAjax.url = $('#amazon_tools_url').val();
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = null;

        if (window.console)
            console.log(pAjax);

        $('#maintenance-loader').show();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: 'id_lang=' + $('#id_lang').val() + '&action=' + action + '&' + pAjax.data,
            success: function (data) {
                $('#maintenance-loader').hide();

                if (window.console)
                    console.log(data);

                if (typeof(data.warning) == 'object' && data.warning.length) {
                    $('#maintenance-warning').show();
                    $.each(data.warning, function (w, warning) {
                        $('#maintenance-warning').append(warning + '<br />');
                    });
                }
                /*
                 if (typeof(data.output) == 'object' && data.output.length) {
                 $('#maintenance-warning').show();
                 $.each(data.output, function (o, output) {
                 $('#maintenance-warning').append(output+'<br />');
                 });
                 }
                 */

                if (typeof(data.error) == 'object' && data.error.length) {
                    $('#maintenance-error').show();
                    $.each(data.error, function (e, error) {
                        $('#maintenance-error').append(error + '<br />');
                    });
                }

                if (typeof(data.success) == 'object' && data.success.length) {
                    $('#maintenance-success').show();
                    $.each(data.success, function (s, success) {
                        $('#maintenance-success').append(success + '<br />');
                    });
                }
                if (typeof(data.console) == 'object' && data.console.length)
                    $('#maintenance-error').append(data.console).show();
            },
            error: function (data) {
                $('#maintenance-loader').hide();
                $('#maintenance-success').hide();
                $('#maintenance-warning').hide();
                $('#maintenance-error').show();
                $('#maintenance-error').html($('#tools_ajax_error').val()).show();

                if (data.status && data.status.length)
                    $('#maintenance-error').append('<pre>Status:' + data.status + '</pre>');
                if (data.statusText && data.statusText.length)
                    $('#maintenance-error').append('<pre>Status:' + data.statusText + '</pre>');
                if (data.responseText && data.responseText.length)
                    $('#maintenance-error').append('<pre>' + data.responseText + '</pre>');

            }
        });
    }

    $('div[rel=amazon-orders] select', context).chosen(chosen_params);

    $('.addnewcarrier', $('div[id^="carrier-group"]', context)).click(function () {
        var lang = $('input[name=selected_tab]').val();
        var id_lang = $('#id-lang-' + lang).val();
        var indexX = $(this).attr('rel');

        cloned = $('#carrier-group-' + id_lang + '-' + indexX).clone().appendTo('#new-carriers-' + id_lang);
        newIndex = parseInt(indexX) + 1;

        cloned.attr('id', '#carrier-group-' + id_lang + '-' + newIndex).find('select').val(0);

        cloned.find('.addnewcarrier').remove();
        cloned.find('.removecarrier').show().attr('rel', newIndex).
        click(function () {
            $(this).parent().remove();
        });

        $('.chosen-container', cloned).remove();
        $('select', cloned).chosen(chosen_params);
    });
    $('.removecarrier', context).click(function () {
        $(this).parent().remove();
    });

    $('.addnew-outgoing-carrier', context).click(function () {
        var lang = $('input[name=selected_tab]', context).val();
        var id_lang = $('#id-lang-' + lang).val();
        var indexX = $(this).attr('rel');

        cloned = $('#outgoing-carrier-group-' + id_lang + '-' + indexX).clone().appendTo('#outgoing-new-carriers-' + id_lang);
        newIndex = parseInt(indexX) + 1;

        cloned.attr('id', '#outgoing-carrier-group-' + id_lang + '-' + newIndex).find('select').val(0);

        cloned.find('.addnew-outgoing-carrier').remove();
        cloned.find('.remove-outgoing-carrier').show().attr('rel', newIndex).
        click(function () {
            $(this).parent().remove();
        });
        $('.chosen-container', cloned).remove();
        $('select', cloned).chosen(chosen_params);
    });
    $('.remove-outgoing-carrier', context).click(function () {
        $(this).parent().remove();
    });

    $('.addnew-multichannel-carrier', context).click(function () {
        var lang = $('input[name=selected_tab]', context).val();
        var id_lang = $('#id-lang-' + lang).val();
        var indexX = $(this).attr('rel');

        cloned = $('#multichannel-carrier-group-' + id_lang + '-' + indexX).clone().appendTo('#multichannel-new-carriers-' + id_lang);
        newIndex = parseInt(indexX) + 1;

        cloned.attr('id', '#multichannel-carrier-group-' + id_lang + '-' + newIndex);

        cloned.find('.addnew-multichannel-carrier', context).remove();
        cloned.find('.remove-multichannel-carrier', context).show().attr('rel', newIndex).
        click(function () {
            $(this).parent().remove();
        });
        $('.chosen-container', cloned).remove();
        $('select', cloned).chosen(chosen_params);
    });
    $('.remove-multichannel-carrier', context).click(function () {
        $(this).parent().remove();
    });

    // Tab active or not active
    //
    $('div[id^=menudiv-]', context).each(function () {

        if ($(this).find('input[name^="actives"]').length != 0 && !parseInt($(this).find('input[name^="actives"]:checked').val())) {
            tabInactive($(this));
        }
        else if ($(this).find('input[name^="actives"]').length != 0) {
            tabActive($(this));
        }
    });

    $('input[id^="active-"]', context).click(function () {
        result = $(this).attr('id').match('^(.*)-(.*)$');
        lang = result[2];
        currentTab = $('#menudiv-' + lang);

        if (!parseInt(currentTab.find('input[name^="actives"]:checked').val()))
            tabInactive(currentTab);
        else
            tabActive(currentTab);

    });
    function tabActive(tab) {
        $(tab).find('input, select, textarea', context).each(function () {
            if ($(this).attr('rel') > '') return (true);
            if ($(this).attr('type') == 'checkbox') return (true);
            if ($(this).attr('name') == 'submit') return (true);
            $(this).attr('readonly', false).attr('disabled', false).removeClass('disabled');
        });
    }

    function tabInactive(tab) {
        $(tab).find('input, select, textarea', context).each(function () {
            if ($(this).attr('rel') > '') return (true);
            if ($(this).attr('type') == 'checkbox') return (true);
            if ($(this).attr('name') == 'submit') return (true);
            $(this).attr('readonly', 'readonly').attr('disabled', 'disabled').addClass('disabled');
        });
    }

    // First Tab
    //
    //$('li[id^="menu-"]:last').after( $('li[id^="menu-"].selected') ) ;

    $('li[id^="menu-"]', context).click(function () {
        result = $(this).attr('id').match('^(.*)-(.*)$');
        lang = result[2];

        $('input[name=selected_tab]').val(lang);

        if (!$(this).hasClass('selected')) {
            //$('li[id^="menu-"]:last').after( $(this) ) ;
            $('li[id^="menu-"]').removeClass('selected');
            $(this).addClass('selected');
            $('div[id^="menudiv-"]').hide();
            $('div[id="menudiv-' + lang + '"]').show().trigger('shown');
        }
    });

    $('#amazonEurope').click(function () {
        selector = $('#marketPlaceMaster');
        if ($(this).attr('checked') == true || $(this).attr('checked') == 'checked') {
            selector.attr('disabled', false);
            selector.addClass('master-enabled');
            selector.removeClass('master-disabled');
        }
        else {
            selector.attr('disabled', 'disabled');
            selector.addClass('master-disabled');
            selector.removeClass('master-enabled');
        }
    });
    $('#marketPlaceMaster').click(function () {
        if ($(this).attr('disabled') == false)
            return (false);
        else
            return (true);
    });

    $('input[name=checkme]', $('#menudiv-categories')).click(function ()
    {
        var categories_context = $(this).parents().get(3);

        $('input.category', $(categories_context)).each(function () {
            if ($(this).attr('checked'))
                $(this).attr('checked', false);
            else
                $(this).attr('checked', 'checked');
        });

        $(this).attr('checked', false);

    });
    // Multiple selection for checkboxes
    (function ($) {
        $.fn.enableCheckboxRangeSelection = function () {
            var lastCheckbox = null;
            var $spec = this;
            $spec.unbind("click.checkboxrange");
            $spec.bind("click.checkboxrange", function (e) {
                if (lastCheckbox !== null && (e.shiftKey || e.metaKey)) {
                    var is_checked = e.target.checked ? true : false;
                    $spec.slice(
                        Math.min($spec.index(lastCheckbox), $spec.index(e.target)),
                        Math.max($spec.index(lastCheckbox), $spec.index(e.target)) + 1
                    ).attr('checked', is_checked).prop('checked', is_checked);
                }
                lastCheckbox = e.target;
            });
        };
    })(jQuery);

    $('.category', $('#menudiv-categories')).enableCheckboxRangeSelection();

    $('input[name^=queue]', $('#menudiv-tools')).enableCheckboxRangeSelection();

    $('input[id^="check-"]', $('#content .tabItem')).click(function () {
        result = $(this).attr('id').match('^(.*)-(.*)$');
        lang = result[2];
        selected_tab = $('input[name=selected_tab]').val();

        result = $('input[name=id_lang][value="' + selected_tab + '"]').attr('id').match('^(.*)-(.*)$');
        id_lang = result[2];

        if (parseInt($('select[name="marketPlaceCurrency[' + id_lang + ']"] :selected').val()) == 0) {
            alert($('#check_msg_currency').val());
            return (false);
        }
        if (parseInt($('select[name="marketPlaceRegion[' + id_lang + ']"] :selected').val()) == 0) {
            alert($('#check_msg_region').val());
            return (false);
        }

        var merchantId = $('input[name="merchantId[' + id_lang + ']"]', context).val();
        var marketPlaceId = $('input[name="marketPlaceId[' + id_lang + ']"]', context).val();
        var awsKeyId = $('input[name="awsKeyId[' + id_lang + ']"]', context).val();
        var awsSecretKey = $('input[name="awsSecretKey[' + id_lang + ']"]', context).val();
        var mwsToken = $('input[name="mwsToken[' + id_lang + ']"]', context).val();

        marketPlaceRegion = $('select[name="marketPlaceRegion[' + id_lang + ']"]', context).val();
        marketPlaceCurrency = $('select[name="marketPlaceCurrency[' + id_lang + ']"]', context).val();

        $('.check-loader', context).show();
        $('#server-response-' + lang).slideDown();

        $.ajax({
            type: 'POST',
            url: $('#check_url').val(),
            data: 'id_lang=' + $('#id_lang').val() + '&merchantId=' + merchantId + '&marketPlaceId=' + marketPlaceId + '&awsKeyId=' + awsKeyId + '&awsSecretKey=' + encodeURIComponent(awsSecretKey) + '&mwsToken=' + encodeURIComponent(mwsToken) + '&marketPlaceRegion=' + marketPlaceRegion + '&marketPlaceCurrency=' + marketPlaceCurrency + '&action=check',
            success: function (data) {
                $('.check-loader').hide();
                $('#server-response-' + lang).html(data);
            },
            error: function (data) {
                $('.check-loader').hide();
                $('#server-response-' + lang).html(data);
            }
        });
    });

    $('#support-mode_dev').click(function () {
        $('.support-information-loader').show();

        var current_status = $('#mode_dev-status').val();

        $('#devmode-response-success').html('').hide();
        $('#devmode-response-danger').html('').hide();

        $.ajax({
            type: 'POST',
            dataType: 'jsonp',
            url: $(this).attr('rel') + '&status=' + current_status + '&callback=?',
            success: function (data) {
                $('.support-information-loader').hide();
                $('#devmode-response-success').html(data.message).slideDown();
                if (data.status == true) {
                    $('#mode_dev-status').val('0');
                    $('#support-mode_dev').val($('#mode_dev-status-off').val());
                    $('#prestashop-info-dev').show();
                }
                else {
                    $('#mode_dev-status').val('1');
                    $('#support-mode_dev').val($('#mode_dev-status-on').val());
                    $('#prestashop-info-dev').hide();
                }
            },
            error: function (data) {
                $('.support-information-loader').hide();
                $('#devmode-response-danger').html(data.responseText).slideDown();
            }
        });
    });


    // Condition/State Mapping
    //
    $('select[id^=condition_map-]', $('#menudiv-parameters')).change(function () {
        value = $(this).val();

        $('select[id^=condition_map-] option[value="' + value + '"]:selected').parent().val(0);
        $('#' + $(this).attr('id') + ' option[value="' + value + '"]').attr('selected', true);
    });

    $('input[name="submit"], button[name="submit"]', context).click(function () {
        if ($('select[name="order_state"] :selected').index() == 0) {
            alert($('select[name="order_state"] option:eq(0)').val() + ' !');
            return (false);
        }
        if ($('select[name="sent_state"] :selected').index() == 0) {
            alert($('select[name="sent_state"] option:eq(0)').val() + ' !');
            return (false);
        }
    });




    $('.config-type', context).click(function () {
        $('.config-type').toggle();
        $('.advanced-settings').slideToggle();
    });

    $('#fba').click(function () {
        if ($(this).attr('checked'))
            alert($('#text_fba').val());
    });
    $('#creation').click(function () {
        if ($(this).attr('checked'))
            alert($('#text_creation').val());
    });

    $('#fba_multichannel').change(function () {
        if ($(this).attr('checked'))
            alert($('#text_multichannel').val());
    });

    $('#dummy').click(function () {
        $('#dummy-product').slideDown();
    });
    $('#dummy-2').click(function () {
        $('#dummy-product').slideUp();
    });

    $('select[name^="marketPlaceRegion"], select[name^="marketPlaceCurrency"]', context).change(function () {
        result = $('div[id^=menudiv-]:visible').attr('id').match('^(.*)-(.*)$');
        country = result[2];

        $('#menudiv-' + country + ' .change-locales:visible').html('');
        //$('#menudiv-' + country + ' .change-locales:visible').html($('input[name="submit"]:visible, button[name="submit"]:visible').clone());
        $('input[name="submit"]:visible, button[name="submit"]:visible').css('border', '1px solid red');

        $('#menudiv-' + country + ' input').not('.button').not('.disabled').addClass('disabled').attr('readonly', 'readonly');
        $('#menudiv-' + country + ' textarea').not('.button').not('.disabled').addClass('disabled').attr('readonly', 'readonly');
    });

    $('div[id^="menudiv-"]', context).each(function () {
        result = $(this).attr('id').match('^(.*)-(.*)$');
        country = result[2];

        $('#menudiv-' + country + ' select[name^="marketPlaceRegion"]').each(function () {
            if (parseInt($(this).val()) == 0)
                $('#menudiv-' + country + ' input, textarea').not('.button').not('[rel=1]').not('.disabled').addClass('disabled').attr('readonly', 'readonly')
        });
        $('#menudiv-' + country + ' select[name^="marketPlaceCurrency"]').each(function () {
            if (parseInt($(this).val()) == 0)
                $('#menudiv-' + country + ' input, textarea').not('.button').not('[rel=1]').not('.disabled').addClass('disabled').attr('readonly', 'readonly')
        });
    });


    //
    // Manufacturer Include/Exclude
    //
    $('#manufacturer-move-right', $('#menudiv-filters')).click(function () {
        return !$('#selected-manufacturers option:selected').remove().appendTo('#available-manufacturers');
    });
    $('#manufacturer-move-left', $('#menudiv-filters')).click(function () {
        return !$('#available-manufacturers option:selected').remove().appendTo('#selected-manufacturers');
    });

    //
    // Suppliers Include/Exclude
    //
    $('#supplier-move-right', $('#menudiv-filters')).click(function () {
        return !$('#selected-suppliers option:selected').remove().appendTo('#available-suppliers');
    });
    $('#supplier-move-left', $('#menudiv-filters')).click(function () {
        return !$('#available-suppliers option:selected').remove().appendTo('#selected-suppliers');
    });

    $('input[name="submit"], button[name="submit"]').click(function () {
        $('#available-suppliers option').attr('selected', true);
        $('#selected-suppliers option').attr('selected', true);
        $('#available-manufacturers option').attr('selected', true);
        $('#selected-manufacturers option').attr('selected', true);
    });

    // Change Synch Field warning
    //
    $('select[name^="synch_field"]', $('#content .tabItem')).change(function () {
        alert($('input[name^="change_synch_field"]:first').val());
    });

    // Stock only function warning
    //
    $('input[name="stock_only"]', $('#content .tabItem')).change(function () {
        if ($(this).attr('checked'))
            alert($('input[name="stock_only_changed"]:first').val());
    });

    // Tools Tab
    $('.radio_config', $('#content .tabItem')).click(function (event) {
        radio = $(this).find('input[type=radio]');

        if (event.target.type !== 'radio')
            $(radio, this).trigger('click');
    });
    $('.checkbox_config', $('#content .tabItem')).click(function (event) {
        radio = $(this).find('input[type=checkbox]');

        if (event.target.type !== 'checkbox')
            $(radio, this).trigger('click');
    });

    $('#tools_code_export_submit').click(function () {

        $('.export-loader', $('#content .tabItem')).show();

        $.ajax({
            type: 'POST',
            url: $('#amazon_tools_url').val(),
            dataType: 'jsonp',
            data: {
                'action': 'product-code-export',
                'active': $('#tools_code_export_active').is(':checked') ? 1 : 0,
                'in_stock': $('#tools_code_export_in_stock').is(':checked') ? 1 : 0,
            },
            success: function (data) {
                $('.export-loader').hide();

                if (window.console)
                    console.log(data);

                if (data.error == true) {
                    $('#tools_code_export_success').hide();
                    $('#tools_code_export_error').show();
                    $('#tools_code_export_result_error').html(data.errors);
                }
                else if (data.file) {
                    $('#tools_code_export_success').show();
                    $('#tools_code_export_error').hide();
                    $('#tools_code_export_result_ok').html(data.filelink);
                }

            },
            error: function (data) {
                if (window.console)
                    console.log(data);

                $('.export-loader').hide();
                $('#tools_code_export_success').hide();
                $('#tools_code_export_error').show();
                $('#tools_code_export_result_error').html($('#tools_ajax_error').val());
            }
        });
        return (false);
    });


    $('#queue-delete').click(function () {

        $('.queue-loader', context).show();

        $.ajax({
            type: 'POST',
            url: $('#amazon_tools_url').val(),
            dataType: 'jsonp',
            data: 'action=queue-delete&' + $('.table-queue-delete input:checked', context).serialize(),
            success: function (data) {
                $('.queue-loader').hide();

                if (window.console)
                    console.log(data);

                if (data.error != true) {
                    $('.table-queue-delete input:checked', context).parent().parent().remove();
                }
            }
        });
        return (false);
    });
    $('#mail_invoice').click(function () {
        $('#mail_invoice_activated').slideDown();
    });
    $('#mail_invoice-2').click(function () {
        $('#mail_invoice_activated').slideUp();
    });
    $('#mail_review').click(function () {
        $('#mail_review_activated').slideDown();
    });
    $('#mail_review-2').click(function () {
        $('#mail_review_activated').slideUp();
    });
    $('#smart-shipping-active').click(function () {
        $('#smart-shipping').slideToggle();
    });
    $('#customer_thread').click(function () {
        $('#customer_thread_activated').slideDown();
    });
    $('#customer_thread-2').click(function () {
        $('#mail_review_activated').slideUp();
    });

    /*
     * General Price Rules
     */
    $('.price-rule-add', $('#content .tabItem')).click(function () {
        var source_i = '';
        source_i = $(this).parent();

        var dest_i = $(this).parent().parent();
        var is_first_rule = $(dest_i).find('.price-rule').length;

        var from_val = Number(dest_i.find('input[rel=from]:last').val());
        var to_val = Number(dest_i.find('input[rel=to]:last').val());

        dest_i.find('input[rel=from]:last,input[rel=to]:last').removeClass('required');

        if (!dest_i.find('input[rel=from]:last').val().length || (is_first_rule !== 1 && !parseInt(from_val)))
            from_val = null;

        if (!dest_i.find('input[rel=to]:last').val().length || !parseInt(to_val))
            to_val = null;

        if (parseInt(from_val) && parseInt(to_val) && from_val > to_val && from_val >= (parseInt(source_i.find('input[rel=from]').val()) + 1)) {
            dest_i.find('input[rel=to]:last').val('');
            to_val = null;
        }
        else if (parseInt(from_val) && parseInt(to_val) && from_val > to_val) {
            dest_i.find('input[rel=from]:last').val('');
            from_val = null;
        }

        if (!to_val || (is_first_rule !== 1 && !from_val)) {
            if (from_val === null)
                dest_i.find('input[rel=from]:last').addClass('required');
            if (to_val === null)
                dest_i.find('input[rel=to]:last').addClass('required');
            return (false);
        }
        cloned = source_i.clone().appendTo(dest_i);
        cloned.find('input').val('');
        cloned.find('input[rel=from]').val(parseInt(to_val + 1));

        cloned.find('.price-rule-add, .price-rule-remove').toggle();
        cloned.find('.price-rule-remove').
        click(function () {
            $(this).parent().remove();
        });
    });
    $('.price-rule-remove', $('#content .tabItem')).click(function () {
        $(this).parent().remove();
    });
    $('.price-rule-type', $('#content .tabItem')).change(function () {
        var type = $(this).val();
        console.log($(this));
        if (type !== 'percent' && type !== 'value')
            return (false);
        $(this).parent().find('select[rel="percent"], select[rel="value"]').hide();
        $(this).parent().find('select[rel="' + type + '"]').show();
    });

    $('#preorder-chk-1').click(function () {
        $('#order-state-preorder').slideDown();
    });
    $('#preorder-chk-2').click(function () {
        $('#order-state-preorder').slideUp();
    });

    $('#special-chk-1').click(function () {
        $('#specials-apply-rules-section').slideDown();
    });
    $('#special-chk-2').click(function () {
        $('#specials-apply-rules-section').slideUp();
    });

    function DisplayPrice(obj) {
        price = obj.val();
        if (price <= 0 || !price) return;
        price = parseFloat(price.replace(',', '.'));
        if (isNaN(price)) price = 0;
        price = price.toFixed(2);

        obj.val(price);
    }

    $('.price-filter-value', $('#content .tabItem')).blur(function () {
        DisplayPrice($(this));
    });
    $('.shipping-gauge, .shipping-tare', $('#content .tabItem')).blur(function () {
        DisplayPrice($(this));
    });
    
    $('.arrow-cat-duplicate', $('#content .tabItem')).click(function () {
        var current_line = $(this).parents().get(2);
        var current_cat_checkbox = $(current_line).find('input[type=checkbox]');
        var current_cat_select = $(current_line).find('select');
        var next_lines = $(current_line).nextAll();

        if (current_cat_select.val().length)
            current_cat_checkbox.attr('checked', true);
        else
            current_cat_checkbox.attr('checked', false);

        next_lines.each(function () {
            var selector = $(this).find('select');

            if (current_cat_select.val().length) {
                if (selector.val().length && selector.val() != current_cat_select.val())
                    return (false);
                $(this).find('input[type=checkbox]').attr('checked', true);
            }
            else
                $(this).find('input[type=checkbox]').attr('checked', false);

            selector.val(current_cat_select.val());
        });
    });


    $('.profile-mapping-required', $('#content .tabItem')).click(function () {
        $('#menu-mapping').click();
    });

    $('.cron-mode', $('#content .tabItem')).click(function () {
        div_id = $(this).attr('rel');

        if ($('#' + div_id).is(':visible')) {
            $('#' + div_id + '.cron-toggle').slideUp('slow');
            return (false);
        }

        $('.cron-toggle').hide();
        $('#' + div_id + '.cron-toggle').slideDown('slow');
    });

    $('#install-cronjobs').click(function () {

        $('#cronjob-loader').show();

        $.ajax({
            type: 'POST',
            url: $('#amazon_tools_url').val(),
            dataType: 'jsonp',
            data: {
                'action': 'install-cron-jobs',
                'prestashop-cronjobs-params': $('#prestashop-cronjobs-params').text(),
                'context_key': $('#context_key').val()
            },
            success: function (data) {
                $('#cronjob-loader').hide();

                if (window.console)
                    console.log(data);

                if (data.error == true) {
                    $('#cronjobs_success').hide();
                    $('#cronjobs_error').show();
                    $('#cronjobs_error').html(data.output);
                }
                else {
                    $('#cronjobs_success').show();
                    $('#cronjobs_error').hide();
                    $('#cronjobs_success').html(data.output);
                }

            },
            error: function (data) {
                if (window.console)
                    console.log(data);

                $('#cronjob-loader').hide();
                $('#cronjobs_success').hide();
                $('#cronjobs_error').show();
                $('#cronjobs_error').html($('#tools_ajax_error').val());
            }
        });
        return (false);
    });

    $('.dynamic-config, label[for=feat-debug-mode-cb]', $('#content .tabItem')).change(function () {

        if ($(this).attr('type') && $(this).attr('type') == 'checkbox')
            var conf_value = $(this).attr('checked') == 'checked' || $(this).attr('checked') == true ? 1 : 0;
        else
            var conf_value = $(this).val();

        $.ajax({
            type: 'POST',
            url: $('#amazon_tools_url').val(),
            dataType: 'jsonp',
            data: {
                'action': 'dynamic-config',
                'field': $(this).attr('name'),
                'value': conf_value
            },
            success: function (data) {

                if (window.console)
                    console.log(data);

                if (!data.error) {
                    showSuccessMessage($('.amazon-message-success:first', context).val());
                }
                else {
                    showErrorMessage($('.amazon-message-error:first', context).val());
                }
            }
        });
        return (false);
    });

    $('.cat-line td:nth-child(2)', $('#menudiv-categories', context)).click(function (ev) {
        var target_line = $(this).parent();
        $(':input', target_line).trigger('click');
    });

    $('.cat-line select', $('#menudiv-categories', context)).change(function (ev) {
        
        if($(this).val() && $(this).val().length) {
            var target_line = $(this).parents().get(2);
            var status = $(':input', target_line).is(':checked')
            console.log(status);
            if (status == false) {
                $(':input', target_line).attr('checked', true).prop('checked', true);
            }
        }
    });

    $('#amazon_form').submit(function () {
        $('#menudiv-mapping input, #menudiv-mapping select', context).each(function (ind, val) {
            if ($(this).val() && $(this).val().length && $(this).attr('rel') && $(this).attr('rel').length) {
                $(this).attr('name', $(this).attr('rel'));
                $(this).attr('rel', null);
            }
        });

        $('#menudiv-profiles .profile', context).each(function (ind, val) {
            var elements = $(this).find('input, select');

            $(elements).each(function () {
                if (!$(this).attr('name'))
                    return;
                var name = $(this).attr('name');
                $(this).attr('name', name.replace('_key_', ind));
            });
        });

        $('#menudiv-repricing .repricing-strategies .repricing-strategie', context).not('.master').each(function (ind, val) {
            var elements = $(this).find('input, select');

            $(elements).each(function () {

                if ($(this).val() && $(this).val().length && $(this).attr('rel') && $(this).attr('rel').length) {
                    $(this).attr('name', $(this).attr('rel'));
                    $(this).attr('rel', null);
                }
                if (!$(this).attr('name'))
                    return;

                var name = $(this).attr('name');

                $(this).attr('name', name.replace('_key_', ind));
            });
        });

        $('input[rel^=category]:checked', context).attr('name', 'category[]');

        $('select[rel^=profile2category] option:selected[value!=""]', context).parent().each(function () {
            $(this).attr('name', $(this).attr('rel'));
        });

        $('#menudiv-mapping input[id^="tag-"]', context).each(function (ind, val) {
            $(this).val($(this).tagify('serialize'));
        });
        $('#menudiv-profiles input.browsenode[has=tagify]', context).each(function (ind, val) {
            $(this).val($(this).tagify('serialize'));
        });

        $('#amazon_form').after($('<input type="hidden" name="post-count" value="' + $('input[name], select[name], textarea[name]').length + '" />'));
        $('#amazon_form').append($('<input type="hidden" name="post-check" value="1" />'));

        return (true);
    });

    $('input[name="submit"], button[name="submit"]', $('#content .tabItem')).show();

    logtime('Amazon.js Overall', true);
});



