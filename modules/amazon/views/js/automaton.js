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
var pageInitialized2 = false;
$(document).ready(function () {
    if (pageInitialized2) return;
    pageInitialized2 = true;

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
    check = 1;

    /*
     * Creation Wizard
     */
    $('#submit-creation-wizard').click(function () {

        amazon_id_lang = parseInt($('input[name=amazon_lang]:checked').val());

        if (!amazon_id_lang)
            return (false);

        check = 0;
        CreationWizardStart(amazon_id_lang, 0);
    });
    // July-16-2018: Merge code for same selector

    function CreationWizardStart(amazon_id_lang, status) {
        var tokens,
            tokenElements = $('input[name^=amazon_token]');

        if (tokenElements && tokenElements.length) {
            tokens = tokenElements.serialize();
        }

        pAjax = {};
        pAjax.url = $('#automaton_url').val();
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = 'action=creation-wizard&status=' + status + '&context_key=' + $('#context_key').val() + '&seed=' + new Date().valueOf() + '&' + tokens;

        if (window.console)
            console.log(pAjax);

        CreationWizardProcess(pAjax, amazon_id_lang, 'creation');
    }

    function CreationWizardProcess(pAjax, amazon_id_lang, type) {
        $('#amazon-automaton-' + type + '-error').html('').hide();

        if (window.console)
            console.log('AutomatonStart - Lang: ' + amazon_id_lang + ' Type:' + type);

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data + '&amazon_lang=' + amazon_id_lang,
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (data.abort == true) {
                    if (window.console)
                        window.console('Automaton aborted because of an initialization error.');
                    return (false);
                }
                $('#submit-creation-wizard').show();

                if (data.error && data.errors) {
                    $.each(data.errors, function (e, errormsg) {
                        $('#amazon-automaton-' + type + '-error').append(errormsg + '<br />');
                    });
                    $('#amazon-automaton-' + type + '-error').show();
                    $('amazon-automaton-' + type + '-' + amazon_id_lang).html('').hide();
                    return (false);
                }
                DisplayProcess(pAjax, amazon_id_lang, data.process, type);

                if (data && data.process && parseInt(data.process.resubmitTimer))
                    setTimeout(function () {
                        CreationWizardProcess(pAjax, amazon_id_lang, type)
                    }, parseInt(data.process.resubmitTimer) * 1000);
            }
        });
    }

    /*
     * Matching Wizard
     */

    $('#submit-matching-wizard').click(function () {
        amazon_id_lang = parseInt($('input[name=amazon_lang]:checked').val());

        if (!amazon_id_lang)
            return (false);

        check = 0;
        MatchingWizardStart(amazon_id_lang, 0);
    });
    $('input[name=amazon_lang]').each(function () {
        CreationWizardStart($(this).val(), 1);
        MatchingWizardStart($(this).val(), 1);
    });

    function MatchingWizardStart(amazon_id_lang, status) {
        $('#amazon-automaton-matching-killme').val('0');
        var tokens;

        if ($('input[name^=amazon_token]') && $('input[name^=amazon_token]').length)
            tokens = $('input[name^=amazon_token]').serialize();

        pAjax = new Object();
        pAjax.url = $('#automaton_url').val();
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = 'action=matching-wizard&status=' + status + '&context_key=' + $('#context_key').val() + '&seed=' + new Date().valueOf() + '&' + tokens;

        if (window.console)
            console.log(pAjax);

        MatchingWizardProcess(pAjax, amazon_id_lang, 'matching');
    }

    function MatchingWizardProcess(pAjax, amazon_id_lang, type) {
        $('#amazon-automaton-' + type + '-error').html('').hide();

        if (window.console)
            console.log('AutomatonStart - Lang: ' + amazon_id_lang + ' Type:' + type);

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data + '&amazon_lang=' + amazon_id_lang,
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (data.abort == true) {
                    if (window.console)
                        window.console('Automaton aborted because of an initialization error.');
                    return (false);
                }
                $('#submit-matching-wizard').show();

                if (data.error && data.errors) {
                    $.each(data.errors, function (e, errormsg) {
                        $('#amazon-automaton-' + type + '-error').append(errormsg + '<br />');
                    });
                    $('#amazon-automaton-' + type + '-error').show();
                    $('#amazon-automaton-' + type + '-' + amazon_id_lang).html('').hide();
                    return (false);
                }
                DisplayProcess(pAjax, amazon_id_lang, data.process, type);

                // Function returned products, process it
                //
                if (data && typeof(data.products) == 'object' && Object.keys(data.products).length) {
                    // Cleanup old entries
                    $('#amazon-automaton-matching-products').html('');

                    // Open Fancy Box
                    $('#amazon-overlay, #amazon-automaton-matching-window').toggle();

                    // Process products
                    MatchingWizardProcessProducts(data.products, amazon_id_lang, 'ean13');
                }

                if (data && data.process && parseInt(data.process.resubmitTimer))
                    setTimeout(function () {
                        MatchingWizardProcess(pAjax, amazon_id_lang, type)
                    }, parseInt(data.process.resubmitTimer) * 1000);
            },
            error: function (data) {
                ManageAjaxError(pAjax, data, $('#amazon-automaton-matching-error'));
            }
        });
    }

    function addImage(target, url, alt, suffix) {
        iId = 'p-img-' + suffix;

        if (url == null) {
            target.find('img[rel=loader]').fadeOut();
            target.find('img[rel=nope]').fadeIn();
            return;
        }
        target.append('<img src="' + url + '" rel="picture" alt="' + alt + '" id="' + iId + '" style="display:none;" />').find('img[rel=picture]').load(function () {
            $(this).parent().find('img[rel=loader]').fadeOut();
            $(this).fadeIn();
        });
    }

    function MatchingWizardProcessProducts(products, amazon_id_lang, type) {
        var tokens;

        if ($('input[name^=amazon_token]') && $('input[name^=amazon_token]').length)
            tokens = $('input[name^=amazon_token]').serialize();

        pAjax = new Object();
        pAjax.url = $('#automaton_url').val();
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = 'action=match-products&type=' + type + '&context_key=' + $('#context_key').val() + '&products=' + JSON.stringify(products) + '&seed=' + new Date().valueOf() + '&' + tokens;

        // Add Loader
        $('#amazon-automaton-matching-products-loader').clone().appendTo($('#amazon-automaton-matching-products')).attr('rel', 'active').show();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data + '&amazon_lang=' + amazon_id_lang,
            success: function (data) {
                $('.amazon-automaton-matching-products-loader[rel=active]').remove();

                if (window.console) {
                    console.log(data);
                }

                if (data.error && data.errors) {
                    error_target = $('#amazon-automaton-matching-error');

                    $.each(data.errors, function (e, errormsg) {
                        error_target.append(errormsg + '<br />');
                    });
                    error_target.show();

                    closeMatchingBox();
                    return (false);
                }

                if (data == null || data.products == null)
                    return (false);

                // Function returned products, process it 
                //
                if (typeof(data.products) == 'object' && Object.keys(data.products).length) {
                    $.each(data.products, function (p, product) {

                        if (window.console) {
                            console.log(product);
                        }
                        if (!product.checked)
                            return (true);

                        cloned = $('#amazon-automaton-matching-product-model').clone().appendTo($('#amazon-automaton-matching-products')).attr('id', 'pi-' + product.ean13).show();

                        if (!product.matched) {
                            cloned.attr('rel', 'unmatched');
                            cloned.find('.selection').removeClass('selectable');

                            if (!$('#matching-display-unmatched').attr('checked'))
                                cloned.hide();
                        }
                        else {
                            cloned.attr('rel', 'matched');

                            cloned.find('.selection').append('<input type="hidden" name="amazon_item" value="' + product.amazon.asin + '" />');
                            cloned.find('.selection').append('<input type="hidden" name="matched[' + product.reference + '][id_product]" value="' + product.id_product + '" />');
                            cloned.find('.selection').append('<input type="hidden" name="matched[' + product.reference + '][id_product_attribute]" value="' + product.id_product_attribute + '" />');
                            cloned.find('.selection').append('<input type="hidden" name="matched[' + product.reference + '][asin]" value="' + product.amazon.asin + '"/>');

                            if (product.brand_mismatch)
                                cloned.find('.mismatch').show();
                        }

                        cloned.find('span[rel="name"]').html(product.name);
                        cloned.find('span[rel="reference"]').html(product.reference);
                        cloned.find('span[rel="manufacturer"]').html(product.manufacturer);

                        if (type == 'ean13')
                            cloned.find('span[rel="code"]').html(product.ean13);
                        else
                            cloned.find('span[rel="code"]').html(product.upc);

                        if (product.matched) {
                            cloned.find('span[rel="amazon_name"]').html(product.amazon.name);
                            cloned.find('span[rel="amazon_brand"]').html(product.amazon.brand);
                            cloned.find('span[rel="amazon_asin"]').html(product.amazon.asin);
                        }
                        else {
                            cloned.find('.matching-product-right .content').css('visibility', 'hidden');
                        }

                        if (product.image_url.length && $('#matching-load-image').attr('checked')) {
                            addImage(cloned.find('td[rel=image]'), product.image_url, product.name, product.ean13);
                            addImage(cloned.find('td[rel=amazon_image]'), product.amazon.image_url, product.amazon.name, product.amazon.asin);
                        }
                        else {
                            cloned.find('td.image img[rel=loader]').hide();
                            cloned.find('td.image img[rel=nope]').show();
                        }
                    });
                    MatchingWizardProcessProducts(data.products, amazon_id_lang, type);
                }
            },
            error: function (data) {
                ManageAjaxError(pAjax, data, $('#amazon-automaton-matching-error'));
            }
        });
    }

    $('#amazon-automaton-matching-action-reject').click(function () {
        $(".amazon-automaton-matching-product[rel=matched] div.selected").addClass('amazon-automaton-matching-product-processing-reject');
        $(".amazon-automaton-matching-product[rel=matched] div.selected").parent().delay(800).fadeOut();
    });

    $('#amazon-automaton-matching-action-confirm').click(function () {
        data = $(".amazon-automaton-matching-product[rel=matched] div.selected").find('input').serialize();

        $(".amazon-automaton-matching-product[rel=matched] div.selected").addClass('amazon-automaton-matching-product-processing-ok');

        if (!data || !data.length)
            return (false);

        if (parseInt($('#amazon-automaton-matching-killme').val()))
            return (false);

        var tokens;

        if ($('input[name^=amazon_token]') && $('input[name^=amazon_token]').length)
            tokens = $('input[name^=amazon_token]').serialize();

        pAjax = new Object();
        pAjax.url = $('#automaton_url').val();
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = 'action=confirm-products&context_key=' + $('#context_key').val() + '&seed=' + new Date().valueOf() + data + '&' + tokens;

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data + '&amazon_lang=' + amazon_id_lang,
            success: function (data) {
                console.log(data);

                if (typeof(data.products) == 'object' && Object.keys(data.products).length) {
                    $.each(data.products, function (p, product) {
                        $(".amazon-automaton-matching-product[rel=matched] div.selected").find('input[name=amazon_item][value=' + product.asin + ']"').parent().parent().fadeOut();
                    });
                }
                else {
                    $(".amazon-automaton-matching-product[rel=matched] div.selected")
                        .toggleClass('amazon-automaton-matching-product-processing-ok')
                        .toggleClass('amazon-automaton-matching-product-processing-error');
                }
            },
            error: function (data) {
                ManageAjaxError(pAjax, data, $('#amazon-automaton-matching-error'));
            }
        });
    });

    $('#matching-display-unmatched').click(function () {
        if ($(this).attr('checked'))
            $('div[id^="pi-"][rel=unmatched]').show();
        else
            $('div[id^="pi-"][rel=unmatched]').hide();
    });
    $('#matching-display-selectall').click(function () {
        if ($(this).attr('checked'))
            $('.amazon-automaton-matching-product .selection.selectable:not(".selected")').addClass('selected');
        else
            $('.amazon-automaton-matching-product .selection.selectable.selected').removeClass('selected');
    });
    $('#amazon-automaton-matching-products').delegate('.amazon-automaton-matching-product', 'click', function () {
        $(this).find('.selection.selectable').toggleClass('selected');
    });

    $('#amazon-automaton-matching-close, #button-openbox').click(function () {
        closeMatchingBox();
    });
    function closeMatchingBox() {
        $('#amazon-automaton-matching-killme').val(1);
        $('#amazon-overlay, #amazon-automaton-matching-window').toggle();
    }

    /*
     * Common Functions
     */

    function DisplayProcess(pAjax, amazon_id_lang, processData, type) {
        if (window.console) {
            console.log('DisplayAutomatonProcess');
            console.log(processData);
        }
        output_target = 'amazon-automaton-' + type + '-' + amazon_id_lang;
        output_target_model = 'amazon-automaton-' + type + '-' + 'model';

        if (processData.hide) {
            $('#' + output_target + ' div span.amz-info-loader').hide();
            return;
        }
        else {
            $('#' + output_target + ' div span.amz-info-loader').show();
        }
        if (!processData.message) return (false);

        if (!$('#' + output_target).length)
            $('#' + output_target_model).clone().appendTo($('#amazon-automaton-' + type)).attr('id', output_target).show();

        $('#' + output_target + ' .amz-info-flag').html(processData.flag);
        $('#' + output_target + ' .amz-info-marketplace').html(processData.marketplace);
        $('#' + output_target + ' .amz-info-title').html(processData.title);
        $('#' + output_target + ' .amz-info-message').html(processData.message);

        if (parseInt(processData.loader))
            $('#' + output_target + ' div span.amz-info-loader').show();
        else
            $('#' + output_target + ' div span.amz-info-loader').hide();

    }

    $('body').append($('#amazon-overlay, #amazon-automaton-matching-window'));
});