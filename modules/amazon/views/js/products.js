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
    $('.hint').slideDown();
    $('.amazon-create-hint[rel=wizard]').hide();
    $('.amazon-matching-hint[rel=wizard]').hide();

    if (!$('input[name="amazon_lang"]:checked').length)
        $('input[name="amazon_lang"]:first').attr('checked', 'checked');

    $('li[id^="menu-"]').click(function () {
        result = $(this).attr('id').match('^(.*)-(.*)$');
        tab = result[2];

        $('input[name=selected_tab]').val(tab);

        if (!$(this).hasClass('selected')) {
            $('li[id^="menu-"]').removeClass('selected');
            $(this).addClass('selected');
            $('div[id^="menudiv-"]').hide();
            $('div[id^="menudiv-' + tab + '"]').show();
        }
    });

    $('#amazon-informations-result').html('<img src="' + $('#img_loader').val() + '" alt="" style="margin-left:50%" />');
    $('#amazon-informations-result').fadeIn();

    // Display Informations
    //
    $.ajax({
        type: 'POST',
        url: $('#reports-url').val(),       // ReportAmazon.tpl
        data: $('#country-selector').serialize() + '&' + $('#amazonParams').serialize() + '&action=display-statistics&context_key=' + $('#context_key').val() + '&instant_token=' + $('#instant_token').val() + '&rand=' + new Date().valueOf(),
        success: function (data) {
            $('#amazon-informations-result').hide();
            $('#statistics-set-result').html(data);
            $('#statistics-set').slideDown();
        }
    });

    $('#menuTab li').click(function () {
        if ($(this).attr('id') == 'menu-creation')
            $('#support-images-exception').show();
        else
            $('#support-images-exception').hide();
    });

    // Check checkbox if label is clicked
    $('.amz-options tr td span').click(function () {
        if ($(this).prev('input'))
            $(this).prev().trigger('click');
    });

    function ManageAjaxError(aCall, data, outdiv) {
        if (window.console) {
            console.log('Ajax Error');
            console.log(aCall);
            console.log(data);
        }
        outdiv.show().html($('#serror').val());

        if (data.output)
            outdiv.append('<br />' + data.output);

        if (data.responseText)
            outdiv.append('<br />' + data.responseText);

        outdiv.append('<hr />');
        outdiv.append($('#sdebug').val() + ':  ');

        outdiv.append('<form method="' + aCall.type + '" action="' + aCall.url + '?debug=1&' + aCall.data + '" target="_blank">' +
            '<input type="submit" class="button" id="send-debug" value="Execute in Debug Mode" /></form>');
    }

    $('#submit-synchronize, #submit-synchronize-verify').click(function () {
        if (!$('input[name="amazon_lang"]').is(':checked') && !$('input[name="amazon_lang"]').val()) {
            alert($('#msg_lang').val());
            return (false);
        }
        params = '';

        if ($(this).attr('id') == 'submit-synchronize-verify') {
            params = '&action=update-verify';
        }
        else {
            params = '&action=update';
        }
        $('#amazon-synchronize-error').html('').hide();
        $('#amazon-synchronize-result').html('').fadeIn();
        $('#amazon-synchronize-result').html('<img src="' + $('#img_loader').val() + '" alt="" style="margin-left:50%" />');

        pAjax = new Object();
        pAjax.type = 'POST';
        pAjax.url = $('#update_url').val();
        pAjax.data = $('#country-selector').serialize() + '&' + $('#amazonParams').serialize() + '&' + $('#amazonSyncOptions').serialize() + params + '&context_key=' + $('#context_key').val() + '&rand=' + new Date().valueOf();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            data: pAjax.data,
            success: function (data) {
                $('#amazon-synchronize-result').html(data);
                if (typeof CS_Amazon.listReports === 'function') {
                    CS_Amazon.listReports();
                }
            },
            error: function (data) {
                $('#amazon-synchronize-result').hide();

                ManageAjaxError(pAjax, data, $('#amazon-synchronize-error'));
            }
        });

        return (false);

    });


    $('#submit-creation, #submit-creation-verify').click(function () {
        if (!$('input[name="amazon_lang"]').is(':checked') && !$('input[name="amazon_lang"]').val()) {
            alert($('#msg_lang').val());
            return (false);
        }
        params = '';

        if ($(this).attr('id') == 'submit-creation-verify') {
            params = '&action=create-verify';
        }
        else {
            params = '&action=create-export';
        }
        $('#amazon-creation-error').html('').hide();
        $('#amazon-creation-result').html('').fadeIn();
        $('#amazon-creation-result').html('<img src="' + $('#img_loader').val() + '" alt="" style="margin-left:50%" />');

        pAjax = new Object();
        pAjax.type = 'POST';
        pAjax.url = $('#update_url').val();
        pAjax.data = $('#country-selector').serialize() + '&' + $('#amazonParams').serialize() + '&' + $('#amazonCreateOptions').serialize() + params + '&context_key=' + $('#context_key').val() + '&rand=' + new Date().valueOf();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            data: pAjax.data,
            success: function (data) {
                $('#amazon-creation-result').html(data);
                if (typeof CS_Amazon.listReports === 'function') {
                    CS_Amazon.listReports();
                }
            },
            error: function (data) {
                $('#amazon-creation-result').hide();

                ManageAjaxError(pAjax, data, $('#amazon-creation-error'));
            }
        });

        return (false);

    });

    $('#submit-delete, #submit-delete-verify').click(function () {
        if (!$('input[name="amazon_lang"]').is(':checked') && !$('input[name="amazon_lang"]').val()) {
            alert($('#msg_lang').val());
            return (false);
        }
        params = '';

        if ($(this).attr('id') == 'submit-delete-verify') {
            params = '&action=delete-verify';
        }
        else {
            params = '&action=delete-export';
        }
        $('#amazon-delete-result').html('');
        $('#amazon-delete-result').fadeIn();
        $('#amazon-delete-result').html('<img src="' + $('#img_loader').val() + '" alt="" style="margin-left:50%" />');

        pAjax = new Object();
        pAjax.type = 'POST';
        pAjax.url = $('#update_url').val();
        pAjax.data = $('#country-selector').serialize() + '&' + $('#amazonParams').serialize() + '&' + $('#amazonDeleteOptions').serialize() + params + '&context_key=' + $('#context_key').val() + '&rand=' + new Date().valueOf();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            data: pAjax.data,
            success: function (data) {
                $('#amazon-delete-result').html(data);
                if (typeof CS_Amazon.listReports === 'function') {
                    CS_Amazon.listReports();
                }
            },
            error: function (data) {
                $('#amazon-delete-result').hide();

                ManageAjaxError(pAjax, data, $('#amazon-delete-error'));
            }
        });
        return (false);

    });

    $('#submit-creation, #submit-creation-verify').mouseenter(function () {
        if (!$('.amazon-create-hint[rel=action]').is(':visible')) {
            $('.amazon-create-hint').hide();
            $('.amazon-create-hint[rel=action]').fadeIn();
        }
    });
    $('#submit-creation-wizard').mouseenter(function () {
        if (!$('.amazon-create-hint[rel=wizard]').is(':visible')) {
            $('.amazon-create-hint').hide();
            $('.amazon-create-hint[rel=wizard]').fadeIn();
        }
    });


    $('#submit-synchronize, #submit-synchronize-verify').mouseenter(function () {
        if (!$('.amazon-matching-hint[rel=action]').is(':visible')) {
            $('.amazon-matching-hint').hide();
            $('.amazon-matching-hint[rel=action]').fadeIn();
        }
    });
    $('#submit-matching-wizard').mouseenter(function () {
        if (!$('.amazon-matching-hint[rel=wizard]').is(':visible')) {
            $('.amazon-matching-hint').hide();
            $('.amazon-matching-hint[rel="wizard"]').fadeIn();
        }
    });

    // July-04-2018: Remove not used button

    // July-205-2018: Move to separate file


    $('#statistics-purge').click(function () {

        if (confirm($('#statistics-purge-confirm').val())) {
            pAjax = new Object();
            pAjax.type = 'POST';
            pAjax.url = $('#reports-url').val();        // ReportAmazon.tpl
            pAjax.data = $('#country-selector').serialize() + '&' + $('#amazonParams').serialize() + '&action=purge&context_key=' + $('#context_key').val() + '&instant_token=' + $('#instant_token').val() + '&rand=' + new Date().valueOf();

            $('#amazon-informations-result').html('<img src="' + $('#img_loader').val() + '" alt="" style="margin-left:50%" />');
            $('#amazon-informations-result').fadeIn();

            $.ajax({
                type: pAjax.type,
                url: pAjax.url,
                data: pAjax.data,
                success: function (data) {
                    $('#amazon-informations-result').html(data);
                    $('#menudiv-report tbody.reports tr:not(":first")').remove();
                    $('#statistics-set-result table tbody tr:not(":first")').remove();
                },
                error: function (data) {
                    $('#amazon-informations-result').html().hide();
                    ManageAjaxError(pAjax, data, $('#amazon-report-error'));
                }
            });
            return (false);
        }
    });


    function downloadProductReport(pAjax, initialData) {
        var target_tab = $('#menudiv-import');

        if (window.console) {
            console.log('downloadProductReport');
            console.log(pAjax);
        }

        $('#amazon-import-loader', target_tab).show();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            data: pAjax.data,
            dataType: pAjax.data_type,
            success: function (data) {
                if (window.console) {
                    console.log(data);
                }

                if (typeof(data) == 'object') {
                    next_action=data.action;

                    $.each(data.messages, function (m,message) {
                        $('#amazon-import-success', target_tab).append(message+'<br />');
                    });

                    if (data.error) {
                        $.each(data.errors, function (e,error) {
                            $('#amazon-import-error', target_tab).append(error+'<br />');
                        });
                        $('#amazon-import-error', target_tab).show();
                    }

                    if (data.output && data.output.length) {
                        $('#amazon-import-success', target_tab).append(data.output).show();
                    }

                    if (data.end == true && data.error == false)
                    {
                        $('#amazon-import-success', target_tab).show();
                        $('#amazon-import-loader', target_tab).hide();

                        // Commented for debug purpose
                        // $('#submit-import-verify', target_tab).addClass('disabled').unbind('click');
                        $('#submit-import', target_tab).removeClass('disabled');

                        pAjax.data = initialData + '&action=' + next_action + '&offset='+data.offset;

                        if (window.console) {
                            console.log(pAjax);
                        }

                        $('#amazon-import-success', target_tab).show();
                    }
                    else if (data.continue == true) {
                        //$('#amazon-import-loader', target_tab).hide();

                        $('#amazon-import-success', target_tab).show();

                        if (data.process) {

                            pAjax.data = initialData + '&action=' + next_action + '&process=1&offset='+data.offset;

                            if (window.console) {
                                console.log('continuing with offers:' + pAjax.url);
                            }
                        }
                        if (!data.error) {
                            downloadProductReport(pAjax, initialData);
                        }
                        else $('#amazon-import-loader', target_tab).hide();

                    } else {
                        $('#amazon-import-loader', target_tab).hide();
                    }

                } else {
                    $('#amazon-import-loader', target_tab).hide();
                    $('#amazon-import-error', target_tab).append(data).show();
                    $('#amazon-import-success', target_tab).html('').hide();
                    $('#amazon-import-loader', target_tab).hide();
                }
            },
            error: function (data) {
                $('#amazon-import-loader', target_tab).hide();
                ManageAjaxError(pAjax, data, $('#amazon-import-error'));
            }
        });

        return (false);

    }


    function importProducts(pAjax, initialData) {
        var target_tab = $('#menudiv-import');
        var stop = parseInt($('#submit-import-stop').attr('rel'));

        if (window.console) {
            console.log('importProducts');
            console.log(pAjax);
        }

        if (stop) {
            return(false);
        }

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            data: pAjax.data,
            dataType: pAjax.data_type,
            success: function (data) {

                if (window.console) {
                    console.log(data);
                }

                if (typeof(data) == 'object') {
                    next_action=data.action;

                    if (window.console) {
                        console.log('result');
                        console.log(data);
                    }

                    $.each(data.messages, function (m,message) {
                        $('#amazon-import-success', target_tab).append(message+'<br />');
                    });

                    $('#amazon-import-success', target_tab).show();

                    if (data.error) {
                        $('#amazon-import-loader', target_tab).hide();
                        $('#amazon-import-error', target_tab).show();

                        $.each(data.errors, function (e,error) {
                            $('#amazon-import-error', target_tab).append(error+'<br />');
                        });
                    }

                    if (data.output) {
                        $('#amazon-import-success', target_tab).append(data.output);
                    }


                    if (data.process) {

                        pAjax.data = initialData + '&action=' + next_action + '&process=1&offset='+data.offset+'&'+$('#amazonImportOptions').serialize();

                        if (window.console) {
                            console.log('continuing with offers:' + pAjax.url + 'count:' + data.offset);
                        }
                        importProducts(pAjax, initialData);
                    } else {
                        $('#amazon-import-loader', target_tab).hide();
                    }

                } else {
                    $('#amazon-import-loader', target_tab).hide();
                    $('#amazon-import-error', target_tab).append(data).show();
                    $('#amazon-import-success', target_tab).html('').hide();
                    $('#amazon-import-loader', target_tab).hide();
                }
            },
            error: function (data) {
                $('#amazon-import-loader', target_tab).hide();
                ManageAjaxError(pAjax, data, $('#amazon-import-error'));
            }
        });

        return (false);

    }
    $('#submit-import-stop').click(function () {
        $(this).attr('rel', 1);
        $(this).toggle();
        $('#submit-import').toggle();
        $('#amazon-import-loader').hide();
    });

    $('#submit-import').click(function () {
        var target_tab = $('#menudiv-import');


        if (window.console) {
            console.log('Submit Import');
        }
        if ($(this).hasClass('disabled')) {
            return(false);
        }
        if (!$('input[name="amazon_lang"]').is(':checked') && !$('input[name="amazon_lang"]').val()) {
            alert($('#msg_lang').val());
            return (false);
        }

        $('#amazon-import-loader', target_tab).show();
        $('#amazon-import-error', target_tab).html('').hide();
        $('#amazon-import-success', target_tab).html('').hide();

        $('#submit-import').toggle();
        $('#submit-import-stop').toggle().attr('rel', 0);

        pAjax = new Object();
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.url = $('#import_url').val()+'?action=parse-products&callback=?';
        pAjax.data = $('#country-selector').serialize() + '&instant_token=' + $('#instant_token').val() + '&context_key=' + $('#context_key').val()+'&'+$('#amazonImportOptions').serialize() + '&rand=' + new Date().valueOf();

        if (window.console) {
            console.log(pAjax);
        }

        importProducts(pAjax, pAjax.data);
    });

    $('#submit-import-verify').click(function () {
        var target_tab = $('#menudiv-import');

        if (!$('input[name="amazon_lang"]').is(':checked') && !$('input[name="amazon_lang"]').val()) {
            alert($('#msg_lang').val());
            return (false);
        }

        $('#amazon-import-loader', target_tab).show();
        $('#amazon-import-error', target_tab).html('').hide();
        $('#amazon-import-success', target_tab).html('').hide();

        pAjax = new Object();
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.url = $('#import_url').val()+'?action=get-products&callback=?';
        pAjax.data = $('#country-selector').serialize() + '&instant_token=' + $('#instant_token').val() + '&context_key=' + $('#context_key').val() + '&rand=' + new Date().valueOf();

        downloadProductReport(pAjax, pAjax.data);

        return (false);
    });



});

