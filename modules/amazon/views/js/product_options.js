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
    id_lang = null;

    // PS 1.4 or 1.5
    if ($('#product').length) {
        var product_form = $('#product');
        Amazon_Init();
    }
    else {
        var product_form = $('#product_form');
        setTimeout(Amazon_Init, 2000);
    }

    function Amazon_Init() {
        var amazon_options = $('meta[name="amazon-options"]').attr('content');

        if (!amazon_options.length) return (false);

        $.ajax({
            type: 'POST',
            url: amazon_options,
            data: product_form.attr('action') + '&rand=' + new Date().valueOf(),
            success: function (data) {
                // PS 1.5
                if ($('#step1 .separation:eq(2)').length) {
                    $('#step1 .separation:eq(2)').parent().after().append(data);
                    $('#step1 .separation:eq(2)').parent().after().append('<div class="separation"></div>');
                }
                else if ($('#step1 hr:eq(1)').length) {
                    $('#step1 hr:eq(1)').parent().parent().after(data);
                }
                //P.S. 1.6
                else if ($('div#product_options').length) {
                    $("<hr/><div id='amazon-ext'><table><tbody>" + data + "</tbody></table></div>").insertAfter('div#product_options');
                }
                id_lang = $('.tabItem.selected input[name="id_lang"]').val();
                if (!id_lang)
                    id_lang = $('.tabItem input[name="id_lang"]:first').val();
                if (!id_lang)
                    id_lang = 1;

                ProductOptionInit();
            }
        });
    }

    function ProductOptionInit() {
        $('.amazon-details .fba[rel="0"]').hide(); // On Load

        $('#amazon-save-options').click(function () {

            $.ajax({
                type: 'POST',
                url: $('#amazon-product-options-json-url').val(),
                data: $('.amazon-details input').serialize() + '&action=set&id_lang=' + id_lang + '&rand=' + new Date().valueOf() + '&callback=?',
                beforeSend: function (data) {
                    $('#result-amz').html('').hide();
                    $('amazon-save-loader').show();
                },
                success: function (data) {
                    $('amazon-save-loader').hide();
                    $('#result-amz').html(data).show()
                }
            });


        });


        // Propagate function
        //
        function propagate(obj, type, action) {
            console.log($(obj).attr('id'));
            result = $(obj).attr('id').match('^(.*)-(.*)$');
            lang = result[2];

            $.ajax({
                type: 'POST',
                url: $('#amazon-product-options-json-url').val(),
                data: product_form.attr('action') + '&' + $('.amazon-details input').serialize() + '&action=' + action + '&rand=' + new Date().valueOf() + '&callback=?',
                beforeSend: function (data) {
                    $('#' + type + '-loader-' + lang).show();
                    $('#result-amz').html('').hide()
                },
                success: function (data) {
                    $('#' + type + '-loader-' + lang).hide();
                    $('#result-amz').html(data).show()
                }
            });
        }

        // Actions
        //
        $('input[id^="amz-action"]').click(function () {
            $('input[name^="amz-action"][value="' + $(this).val() + '"]').attr('checked', $(this).attr('checked'));
        });

        $('.amz-action-label').click(function (e) {
            var val = $(this).prev().val();
            if (e.target.type !== 'checkbox') {
                $('input[name^="amz-action"][value="' + val + '"]').trigger('click');
            }
        });

        $('.amz-propagate-action-cat').click(function () {

            if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

            propagate($(this), 'action', 'propagate-action-cat');

        });
        $('.amz-propagate-action-shop').click(function () {

            if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

            propagate($(this), 'action', 'propagate-action-shop');
        });
        $('.amz-propagate-action-manufacturer').click(function () {

            if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

            propagate($(this), 'action', 'propagate-action-manufacturer');
        });
        $('.amz-propagate-action-supplier').click(function () {

            if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

            propagate($(this), 'action', 'propagate-action-supplier');
        });

        // Bullet Points
        //
        $('.amz-propagate-bulletpoint-cat').click(function () {

            if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

            propagate($(this), 'bulletpoint', 'propagate-bulletpoint-cat');

        });
        $('.amz-propagate-bulletpoint-shop').click(function () {

            if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

            propagate($(this), 'bulletpoint', 'propagate-bulletpoint-shop');
        });
        $('.amz-propagate-bulletpoint-manufacturer').click(function () {

            if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

            propagate($(this), 'bulletpoint', 'propagate-bulletpoint-manufacturer');
        });
        $('.amz-propagate-bulletpoint-supplier').click(function () {

            if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

            propagate($(this), 'bulletpoint', 'propagate-bulletpoint-supplier');
        });

        // Complementary Text 
        //
        $('.amz-propagate-text-cat').click(function () {

            if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

            propagate($(this), 'extra-text', 'propagate-text-cat');

        });
        $('.amz-propagate-text-shop').click(function () {

            if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

            propagate($(this), 'extra-text', 'propagate-text-shop');
        });
        $('.amz-propagate-text-manufacturer').click(function () {

            if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

            propagate($(this), 'extra-text', 'propagate-text-manufacturer');
        });
        $('.amz-propagate-text-supplier').click(function () {

            if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

            propagate($(this), 'extra-text', 'propagate-text-supplier');
        });

        // FBA 
        //
        $('.amz-propagate-fba-cat').click(function () {

            if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

            propagate($(this), 'fba', 'propagate-fba-cat');

        });
        $('.amz-propagate-fba-shop').click(function () {

            if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

            propagate($(this), 'fba', 'propagate-fba-shop');
        });
        $('.amz-propagate-fba-manufacturer').click(function () {

            if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

            propagate($(this), 'fba', 'propagate-fba-manufacturer');
        });
        $('.amz-propagate-fba-supplier').click(function () {

            if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

            propagate($(this), 'fba', 'propagate-fba-supplier');
        });

        // FBA - Value Added
        //
        $('.amz-propagate-fbavalue-cat').click(function () {

            if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

            propagate($(this), 'fbavalue', 'propagate-fbavalue-cat');

        });
        $('.amz-propagate-fbavalue-shop').click(function () {

            if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

            propagate($(this), 'fbavalue', 'propagate-fbavalue-shop');
        });
        $('.amz-propagate-fbavalue-manufacturer').click(function () {

            if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

            propagate($(this), 'fbavalue', 'propagate-fbavalue-manufacturer');
        });
        $('.amz-propagate-fbavalue-supplier').click(function () {

            if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

            propagate($(this), 'fbavalue', 'propagate-fbavalue-supplier');
        });

        // Latency
        //
        $('.amz-propagate-latency-cat').click(function () {
            if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

            propagate($(this), 'latency', 'propagate-latency-cat');
        });

        $('.amz-propagate-latency-shop').click(function () {
            if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

            propagate($(this), 'latency', 'propagate-latency-shop');
        });

        $('.amz-propagate-latency-manufacturer').click(function () {
            if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

            propagate($(this), 'latency', 'propagate-latency-manufacturer');
        });

        $('.amz-propagate-latency-supplier').click(function () {
            if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

            propagate($(this), 'latency', 'propagate-latency-supplier');
        });

        // Shipping Override
        // 
        $('.amz-propagate-shipping-cat').click(function () {
            if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

            propagate($(this), 'shipping', 'propagate-shipping-cat');
        });
        $('.amz-propagate-shipping-shop').click(function () {
            if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

            propagate($(this), 'shipping', 'propagate-shipping-shop');
        });
        $('.amz-propagate-shipping-manufacturer').click(function () {
            if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

            propagate($(this), 'shipping', 'propagate-shipping-manufacturer');
        });
        $('.amz-propagate-shipping-supplier').click(function () {
            if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

            propagate($(this), 'shipping', 'propagate-shipping-supplier');
        });

        // Disable Product 
        //
        $('.amz-propagate-disable-cat').click(function () {
            if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

            propagate($(this), 'disable', 'propagate-disable-cat');
        });

        $('.amz-propagate-disable-shop').click(function () {
            if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

            propagate($(this), 'disable', 'propagate-disable-shop');
        });

        $('.amz-propagate-disable-manufacturer').click(function () {
            if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

            propagate($(this), 'disable', 'propagate-disable-manufacturer');
        });

        $('.amz-propagate-disable-supplier').click(function () {
            if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

            propagate($(this), 'disable', 'propagate-disable-supplier');
        });

        // Force
        //
        $('.amz-propagate-force-cat').click(function () {
            if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

            propagate($(this), 'force', 'propagate-force-cat');
        });

        $('.amz-propagate-force-shop').click(function () {
            if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

            propagate($(this), 'force', 'propagate-force-shop');
        });

        $('.amz-propagate-force-manufacturer').click(function () {
            if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

            propagate($(this), 'force', 'propagate-force-manufacturer');
        });

        $('.amz-propagate-force-supplier').click(function () {
            if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

            propagate($(this), 'force', 'propagate-force-supplier');
        });

        // Gift
        //
        $('.amz-propagate-gift-cat').click(function () {
            if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

            propagate($(this), 'gift', 'propagate-gift-cat');
        });

        $('.amz-propagate-gift-shop').click(function () {
            if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

            propagate($(this), 'gift', 'propagate-gift-shop');
        });

        $('.amz-propagate-gift-manufacturer').click(function () {
            if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

            propagate($(this), 'gift', 'propagate-gift-manufacturer');
        });

        $('.amz-propagate-gift-supplier').click(function () {
            if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

            propagate($(this), 'gift', 'propagate-gift-supplier');
        });

        // Go to product page
        $('input[id^="amazon-goto-"]').click(function () {
            result = $(this).attr('id').match('^(.*)-(.*)$');
            asin = result[2];

            window.open($(this).attr('rel'));
        });

        // ASIN
        $('input[id^="amz-ean-asin-"]').click(function () {

            result = $(this).attr('id').match('^(.*)-(.*)$');
            lang = result[2];


            if (!$('input[name="ean13"]').val().length && $('input[name="ean13"]').val().length) {
                $('#asin-response-' + lang).slideDown();
                $('#asin-response-' + lang).html($('#amz-asin-mustbeset').val());
                return (false);
            }

            $.ajax({
                type: 'POST',
                url: $('#amazon-product-options-json-url').val(),
                data: $('.amazon-details input').serialize() + '&' + $('input[name="ean13"], input[name="upc"], input[name="marketPlaceId"],  input[name^="advertisingAPI"]').serialize() + '&action=ean2asin&rand=' + new Date() + '&id_lang=' + lang + '&callback=?',
                dataType: 'json',
                beforeSend: function (data) {
                    $('#asin-loader-' + lang).show();
                    $('#asin-response-' + lang).html('').hide()
                },
                success: function (data) {
                    $('#asin-loader-' + lang).hide(),

                        $('#asin-response-' + lang).html(data.output).show();

                    if (data.asin)
                        $('#amz-asin-' + lang).val(data.asin);
                }
            });

        });


        $('input[id^="amz-asin-ean-"]').click(function () {

            result = $(this).attr('id').match('^(.*)-(.*)$');
            lang = result[2];


            if (!$('input[name="amz-asin-' + lang + '"]').val().length) {
                $('#asin-response-' + lang).slideDown();
                $('#asin-response-' + lang).html($('#amz-asin-mustbeset').val());
                return (false);
            }

            $.ajax({
                type: 'POST',
                url: $('#amazon-product-options-json-url').val(),
                data: $('.amazon-details input').serialize() + '&' + $('input[name="amz-asin-' + lang + '"], input[name="marketPlaceId"],  input[name^="advertisingAPI"]').serialize() + '&action=asin2ean&rand=' + new Date() + '&id_lang=' + lang + '&callback=?',
                dataType: 'json',
                beforeSend: function (data) {
                    $('#asin-loader-' + lang).show();
                    $('#asin-response-' + lang).html('').hide()
                },
                success: function (data) {
                    $('#asin-loader-' + lang).hide(),

                        $('#asin-response-' + lang).html(data.output).show();

                    if (data.ean)
                        $('input[name=ean13]').val(data.ean);
                    if (data.upc)
                        $('input[name=upc]').val(data.upc);
                }
            });

        });


        function DisplayPrice(obj) {
            price = obj.val();
            if (price <= 0 || !price) return;
            price = parseFloat(price.replace(',', '.'));
            if (isNaN(price)) price = 0;
            price = price.toFixed(2);

            obj.val(price);
        }

        $('input[name^="amz-shipping-"]').blur(function () {
            DisplayPrice($(this));
        });

        $('input[name^="amz-price-"]').blur(function () {
            DisplayPrice($(this));
        });

        $('input[name^="amz-fbavalue-"]').blur(function () {
            DisplayPrice($(this));
        });


        $('input[name^="amz-fba-"]').click(function () {
            if ($(this).attr('checked')) {
                $('input[name^="amz-fba-"][rel="europe"]').attr('checked', true);
                $('.amazon-details .fba').show();
            }
            else {
                $('input[name^="amz-fba-"][rel="europe"]').attr('checked', false);
                $('.amazon-details .fba').hide();
            }
        });


        $('#amazon-options').click(function () {
            image = $('#amz-toggle-img');

            newImage = image.attr('rel');
            oldImage = image.attr('src');

            image.attr('src', newImage);
            image.attr('rel', oldImage);

            if ($('.amazon-details').is(':visible'))
                $('.amazon-details').hide();
            else
                $('.amazon-details').show();
        });

        // First Tab
        //
        $('div[id^="menudiv-' + $('input[name=selected_tab]').val() + '"]').show();

        $('li[id^="menu-"]').click(function () {
            result = $(this).attr('id').match('^(.*)-(.*)$');
            lang = result[2];

            $('input[name=selected_tab]').val(lang);

            if (!$(this).hasClass('selected')) {
                $('li[id^="menu-"]').removeClass('selected');
                $(this).addClass('selected');
                $('div[id^="menudiv-"]').hide();
                $('div[id^="menudiv-' + lang + '"]').show();
            }
        });

        function DeleteBulletPointItem(obj) {
            result = $(obj).attr('id').match('(.*)-(.*)$');
            id_lang = result[2];
            $(obj).parent().parent().remove();
        }

        $('.amazon-bullet-point-del').click(function () {
            DeleteBulletPointItem($(this));
        });
        $('.amazon-bullet-point-add').click(function () {
            result = $(this).attr('id').match('(.*)-(.*)$');
            id_lang = result[2];

            if ($('span[class^="amazon-bullet-container-' + id_lang + '"]').length >= 5) {
                alert($('input[name="amz-text-max-bullet"]:first').val());
                return (false);
            }

            last_item = $('span[class^="amazon-bullet-container-' + id_lang + '"]:last');
            result = last_item.attr('id').match('-([0-9]+)-([0-9]+)$');
            last_item_index = parseInt(result[1]);
            new_item_index = (last_item_index + 1) + '-' + id_lang;

            cloned = $('#amazon-bullet-container-1-' + id_lang).clone()
                .insertAfter(last_item)
                .attr('id', 'amazon-bullet-container-' + new_item_index);
            cloned.find('input').val('');
            cloned.find('img[id^="amazon-bullet-point-add-"]').attr('id', 'amazon-bullet-point-add-' + new_item_index).hide();
            cloned.find('img[id^="amazon-bullet-point-del-"]').attr('id', 'amazon-bullet-point-del-' + new_item_index).show().click(function () {
                DeleteBulletPointItem($(this));
            });
        });


    }

});

