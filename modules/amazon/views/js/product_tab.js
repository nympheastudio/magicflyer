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
    var amazon_context = $('#amazon-product-tab');

    $('.amazon-sub-tab', amazon_context).not('.main').find('.propagation').hide();
    $('.amazon-sub-tab', amazon_context).not('.main').find('.tip').removeClass('tip');

    /* Prestashop 1.7 - put logo into card-header*/
    module_div = $('#module_amazon');
    module_div.parent().parent().css('margin-top', '10px');
    if (module_div.length) {
        $('.card-header', module_div.parent()).prepend( $('h3 > img', module_div) ) ;
    }

	if ('function' !== typeof($.fn.prop)) {
	   jQuery.fn.extend({
		   prop: function() {
			   return this;
		   }
		});
	}
	
    $('.col-left[rel]').each(function () {

        var target_glossary_key = $(this).attr('rel');
        var target_glossary_div = $('#glossary div.glossary[rel=' + target_glossary_key + ']');

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
                }
            });
            $(this).addClass('tip');
        }
    });

    var id_lang = parseInt($('.amazon-tab-selector.active input[name^="amazon_lang"]').val());
    var id_product = parseInt($('#amazon-id-product').val());
    var complex_id_product = null;

    function getComplexProductId() {
        if ($('input[name=complex_id_product]:checked') && $('input[name=complex_id_product]:checked').val() && $('input[name=complex_id_product]:checked').val().length)
            complex_id_product = $('input[name=complex_id_product]:checked').val();
        else
            complex_id_product = id_product + '_0';

        return (complex_id_product);
    }

    getComplexProductId(); // onload

    function getSubTab() {
        if (!id_lang)
            return (null);
        if (!complex_id_product)
            return (null);

        target_tab = '.amazon-tab-' + id_lang;

        if (!$(target_tab).length)
            return (null);

        return ( $(target_tab, amazon_context).find('.amazon-sub-tab[rel="' + id_lang + '-' + complex_id_product + '"]') );
    }

    $('input[name=complex_id_product]', amazon_context).change(function (ev) {
        var tokens = null;
        var source_div_identifier = null;
        var target_div_identifier = null;
        var complex_id_product = null;

        if ($('input[name^=amazon_token]') && $('input[name^=amazon_token]').length)
            tokens = $('input[name^=amazon_token]').serialize();

        if (window.console)
            console.log('Context:', amazon_context);

        $('.amazon-sub-tab', amazon_context).hide();

        if ($('input[name=complex_id_product]:checked') && $('input[name=complex_id_product]:checked').val() && $('input[name=complex_id_product]:checked').val().length) {
            var complex_id_product = $('input[name=complex_id_product]:checked').val();
            var is_combination = true;
        }
        else {
            var is_combination = false;
            complex_id_product = id_product + '_0';
            target_div_identifier = id_lang + '-' + complex_id_product;

            var tab_to_show = '.amazon-sub-tab[rel="' + target_div_identifier + '"]';

            if (window.console)
                console.log('Tab to show:', tab_to_show);


            // display main product option
            $(tab_to_show, amazon_context).show();
            return;
        }

        source_div_identifier = id_lang + '-' + id_product + '_0';
        target_div_identifier = id_lang + '-' + complex_id_product;

        // Div exists, don't clone it, just display it
        if ($('.amazon-sub-tab[rel="' + target_div_identifier + '"]', amazon_context).length) {
            if (window.console)
                console.log('Existing product option', target_div_identifier, $('.amazon-sub-tab', amazon_context));
            $('.amazon-sub-tab', amazon_context).hide();
            $('.amazon-sub-tab[rel="' + target_div_identifier + '"]', amazon_context).show();
            return;
        }
        else {
            if (window.console)
                console.log('Adding product option from', source_div_identifier);

            // no data: clone data from parent product
            var source_div = $('.amazon-sub-tab[rel="' + source_div_identifier + '"]', amazon_context);

            var source_name = $('input[name=complex_id_product]:first').parent().parent().find('td[rel="name"]').text().trim();
            var attributes = $('input[name=complex_id_product]:checked').parent().parent().find('td[rel="name"]').text().trim();
            var source_reference = $('input[name=complex_id_product]:checked').parent().parent().find('td[rel="reference"]').text().trim();
			var target_title = null;
			
			if (source_reference.length)
				target_title = ' (' + source_reference + ')';
			else 
				target_title =  '';
						
            if (is_combination)
                var title = source_name + ' - ' + attributes + target_title;
            else
                var title = source_name + ' (' + source_reference + ')';

            cloned = source_div.clone().insertAfter('.amazon-sub-tab[rel="' + source_div_identifier + '"]');
            cloned.attr('rel', target_div_identifier).removeClass('main');
            cloned.find('.amazon-tab-product-title').text(title);
            cloned.find('.propagation').hide();
            cloned.find('.tip').removeClass('tip');
            cloned.find('input[name=amazon-options-create]').val(1);
            cloned.find('on-amazon').remove();
            cloned.show();

            if (window.console)
                console.log('Source Div:', source_div, 'Cloned:', cloned);
        }
    });


    $('.table.amazon-item tbody tr', amazon_context).click(function (e) {
        if (e.target.type == 'checkbox')
            return;

        $('.table.amazon-item tbody tr').find('input[type=radio]').attr('checked', false).prop('checked', false);
        $('.table.amazon-item tbody tr').removeClass('highlighted');

        $(this).addClass('highlighted');
        $(this).find('input[type=radio]').attr('checked', true).prop('checked', true).change();
    });

    $('.country-selector', amazon_context).delegate('.amazon-tab-selector', 'click', function () {
        if (!$(this).hasClass('active')) {
            var iso_code = $(this).attr('rel');

            if (window.console)
                console.log('Amazon-Tab', amazon_context);

            $('.amazon-tab-selector', amazon_context).removeClass('active');
            $(this).addClass('active');
            $('.amazon-tab').hide();
            $('.amazon-tab[rel="' + iso_code + '"]').show();
            id_lang = parseInt($(this).find('input[name^=amazon_lang]').val());

            var complex_id = getComplexProductId();
            var target_div_identifier = id_lang + '-' + complex_id;

            if ($('.amazon-sub-tab[rel="' + target_div_identifier + '"]', amazon_context).length)
                $('.amazon-sub-tab[rel="' + target_div_identifier + '"]', amazon_context).show();
            else {
                $('input[name=complex_id_product]:first').trigger('click');
                $('.amazon-sub-tab[rel="' + id_lang + '-' + id_product + '_0' + '"]', amazon_context).show();
            }
        }
    });


    $('#amazon-product-tab').delegate('.delete-product-option', 'click', function (ev) {
        var rel = $(this).attr('rel');
        target_sub_tab = getSubTab();
        var tokens = null;

        if ($('input[name^=amazon_token]', amazon_context) && $('input[name^=amazon_token]', amazon_context).length)
            tokens = $('input[name^=amazon_token]', amazon_context).serialize();

        var complex_id_product = $(this).attr('rel');
        var target_sub_tab = getSubTab();

        pAjax = new Object();
        pAjax.url = $('#amazon-product-options-json-url').val() + '&' + tokens + '&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';

        if (1) {
            var data = {
                'action': 'delete-product-option',
                'version': 4,
                'id_lang': id_lang,
                'id_product': $('#amazon-id-product').val(),
                'amazon_option_lang': id_lang, // compatibility
                'complex_id_product': complex_id_product,
                'seed': new Date().valueOf()
            };

            if (window.console)
                console.log('delete product options', $(target_sub_tab), data);

            $.ajax({
                type: pAjax.type,
                url: pAjax.url,
                dataType: pAjax.data_type,
                data: $(':input', $(target_sub_tab)).serialize() + '&' + $.param(data),
                success: function (data) {
                    $('input[name=complex_id_product]:first').trigger('click');

                    $('.amazon-sub-tab[rel="' + id_lang + '-' + complex_id_product + '"]', amazon_context).remove();

                    if (data.error)
                        showErrorMessage($('#amazon-product-options-message-error').val());
                    else
                        showSuccessMessage($('#amazon-product-options-message-success').val());

                    if (data.output)
                        $('#amazon-product-tab .debug').html(data.output);
                },
                error: function (data) {
                    if (window.console)
                        console.log('result', data);

                    showErrorMessage('Error');

                    if (data.status && data.status.length)
                        $('#amazon-product-tab .debug').append('<pre>Status Code:' + data.status + '</pre>');
                    if (data.statusText && data.statusText.length)
                        $('#amazon-product-tab .debug').append('<pre>Status Text:' + data.statusText + '</pre>');
                    if (data.responseText && data.responseText.length)
                        $('#amazon-product-tab .debug').append('<pre>Response:' + data.responseText + '</pre>');
                }
            });
        }
    });

    $('.amazon-tab', amazon_context).delegate('input, select, textarea', 'change', function (ev) {
        var rel = $(this).attr('rel');
        target_sub_tab = getSubTab();
        var tokens = null;
        var value = $(this).val();


        if ($('input[name^=amazon_token]') && $('input[name^=amazon_token]').length)
            tokens = $('input[name^=amazon_token]').serialize();

        if (rel != 'action' && $(this).attr('type') == 'checkbox')
            value = $(this).is(':checked') ? '1' : '0';
        else if (rel != 'action' && $(this).attr('type') == 'radio')
            value = $(this).is(':checked') ? '1' : '0';

        if (window.console)
            console.log($(this).attr('name'), value, $(':input', $(target_sub_tab)).serialize());

        var complex_id_product = getComplexProductId();
        var target_sub_tab = getSubTab();
        var create_mode_input = $(target_sub_tab).find('input[name="amazon-options-create"]');
        var create_mode = parseInt(create_mode_input.val());

        var action = $('input[name="amz-action-' + id_lang + '"]:checked').val();

        pAjax = new Object();
        pAjax.url = $('#amazon-product-options-json-url').val() + '&' + tokens + '&' + 'amz-action-' + id_lang + '=' + action + '&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';

        if (1) {
            var data = {
                'action': 'set',
                'version': 4,
                'id_lang': id_lang,
                'id_product': $('#amazon-id-product').val(),
                'amazon_option_lang': id_lang, // compatibility
                'complex_id_product': complex_id_product,
                'seed': new Date().valueOf()
            };

            if (window.console)
                console.log('create product options', $(target_sub_tab), data);

            $.ajax({
                type: pAjax.type,
                url: pAjax.url,
                dataType: pAjax.data_type,
                data: $(':input', $(target_sub_tab)).serialize() + '&' + $.param(data),
                success: function (data) {
                    create_mode_input.val('0');

                    if (data.error)
                        showErrorMessage($('#amazon-product-options-message-error').val());
                    else
                        showSuccessMessage($('#amazon-product-options-message-success').val());

                    if (data.output)
                        $('#amazon-product-tab .debug').html(data.output);
                },
                error: function (data) {
                    if (window.console)
                        console.log('Error', data);

                    showErrorMessage('Error');

                    if (data.status && data.status.length)
                        $('#amazon-product-tab .debug').append('<pre>Status Code:' + data.status + '</pre>');
                    if (data.statusText && data.statusText.length)
                        $('#amazon-product-tab .debug').append('<pre>Status Text:' + data.statusText + '</pre>');
                    if (data.responseText && data.responseText.length)
                        $('#amazon-product-tab .debug').append('<pre>Response:' + data.responseText + '</pre>');
                }
            });
        }
        return(true);
    });
    // Propagate function
    //
    function propagate(obj, type, action) {
        var tokens = null;

        if ($('input[name^=amazon_token]') && $('input[name^=amazon_token]').length)
            tokens = $('input[name^=amazon_token]').serialize();

        var target_sub_tab = getSubTab();
        var product_action = $('input[name="amz-action-' + id_lang + '"]:checked').val();

        $.ajax({
            type: 'POST',
            dataType: 'jsonp',
            url: $('#amazon-product-options-json-url').val() + '&' + tokens + '&' + 'amz-action-' + id_lang + '=' + product_action + '&rand=' + new Date().valueOf() + '&callback=?',
            data: 'version=4&id_product=' + id_product + '&amazon_option_lang=' + id_lang + '&' + $(':input', $(target_sub_tab)).serialize() + '&action=' + action,
            beforeSend: function (data) {
            },
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (data.error)
                    showErrorMessage($('#amazon-product-options-message-error').val());
                else
                    showSuccessMessage($('#amazon-product-options-message-success').val());

                if (data.output)
                    $('#amazon-product-tab .debug').html(data.output);
            },
            error: function (data) {
                if (window.console)
                    console.log('Error', data);

                showErrorMessage('Error');

                if (data.status && data.status.length)
                    $('#amazon-product-tab .debug').append('<pre>Status Code:' + data.status + '</pre>');
                if (data.statusText && data.statusText.length)
                    $('#amazon-product-tab .debug').append('<pre>Status Text:' + data.statusText + '</pre>');
                if (data.responseText && data.responseText.length)
                    $('#amazon-product-tab .debug').append('<pre>Response:' + data.responseText + '</pre>');
            }
        });
    }

    // Actions
    //

    $('.amz-action-container', amazon_context).click(function (e) {
        if (e.target.type !== 'radio') {
            $(':radio', this).trigger('click');
        }
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-action-cat', 'click', function () {

        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'action', 'propagate-action-cat');

    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-action-shop', 'click', function () {

        if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

        propagate($(this), 'action', 'propagate-action-shop');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-action-manufacturer', 'click', function () {

        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'action', 'propagate-action-manufacturer');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-action-supplier', 'click', function () {

        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'action', 'propagate-action-supplier');
    });

    // Bullet Points
    //
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-bulletpoint-cat', 'click', function () {

        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'bulletpoint', 'propagate-bulletpoint-cat');

    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-bulletpoint-shop', 'click', function () {

        if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

        propagate($(this), 'bulletpoint', 'propagate-bulletpoint-shop');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-bulletpoint-manufacturer', 'click', function () {

        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'bulletpoint', 'propagate-bulletpoint-manufacturer');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-bulletpoint-supplier', 'click', function () {

        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'bulletpoint', 'propagate-bulletpoint-supplier');
    });

    // Complementary Text
    //
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-text-cat', 'click', function () {

        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'extra-text', 'propagate-text-cat');

    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-text-shop', 'click', function () {

        if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

        propagate($(this), 'extra-text', 'propagate-text-shop');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-text-manufacturer', 'click', function () {

        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'extra-text', 'propagate-text-manufacturer');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-text-supplier', 'click', function () {

        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'extra-text', 'propagate-text-supplier');
    });

    // FBA
    //
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-fba-cat', 'click', function () {

        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'fba', 'propagate-fba-cat');

    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-fba-shop', 'click', function () {

        if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

        propagate($(this), 'fba', 'propagate-fba-shop');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-fba-manufacturer', 'click', function () {

        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'fba', 'propagate-fba-manufacturer');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-fba-supplier', 'click', function () {

        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'fba', 'propagate-fba-supplier');
    });

    // FBA - Value Added
    //
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-fbavalue-cat', 'click', function () {

        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'fbavalue', 'propagate-fbavalue-cat');

    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-fbavalue-shop', 'click', function () {

        if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

        propagate($(this), 'fbavalue', 'propagate-fbavalue-shop');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-fbavalue-manufacturer', 'click', function () {

        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'fbavalue', 'propagate-fbavalue-manufacturer');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-fbavalue-supplier', 'click', function () {

        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'fbavalue', 'propagate-fbavalue-supplier');
    });


    // Browsenode
    //
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-browsenode-cat', 'click', function () {
        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'browsenode', 'propagate-browsenode-cat');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-browsenode-manufacturer', 'click', function () {
        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'browsenode', 'propagate-browsenode-manufacturer');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-browsenode-supplier', 'click', function () {
        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'browsenode', 'propagate-browsenode-supplier');
    });

    // Latency
    //
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-latency-cat', 'click', function () {
        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'latency', 'propagate-latency-cat');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-latency-shop', 'click', function () {
        if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

        propagate($(this), 'latency', 'propagate-latency-shop');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-latency-manufacturer', 'click', function () {
        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'latency', 'propagate-latency-manufacturer');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-latency-supplier', 'click', function () {
        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'latency', 'propagate-latency-supplier');
    });

    // Shipping Override
    //
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-shipping-cat', 'click', function () {
        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'shipping', 'propagate-shipping-cat');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-shipping-shop', 'click', function () {
        if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

        propagate($(this), 'shipping', 'propagate-shipping-shop');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-shipping-manufacturer', 'click', function () {
        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'shipping', 'propagate-shipping-manufacturer');
    });
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-shipping-supplier', 'click', function () {
        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'shipping', 'propagate-shipping-supplier');
    });

    // Disable Product
    //
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-disable-cat', 'click', function () {
        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'disable', 'propagate-disable-cat');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-disable-shop', 'click', function () {
        if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

        propagate($(this), 'disable', 'propagate-disable-shop');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-disable-manufacturer', 'click', function () {
        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'disable', 'propagate-disable-manufacturer');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-disable-supplier', 'click', function () {
        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'disable', 'propagate-disable-supplier');
    });

    // Force
    //
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-force-cat', 'click', function () {
        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'force', 'propagate-force-cat');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-force-shop', 'click', function () {
        if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

        propagate($(this), 'force', 'propagate-force-shop');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-force-manufacturer', 'click', function () {
        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'force', 'propagate-force-manufacturer');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-force-supplier', 'click', function () {
        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'force', 'propagate-force-supplier');
    });

    // Gift
    //
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-gift-cat', 'click', function () {
        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'gift', 'propagate-gift-cat');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-gift-shop', 'click', function () {
        if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

        propagate($(this), 'gift', 'propagate-gift-shop');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-gift-manufacturer', 'click', function () {
        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'gift', 'propagate-gift-manufacturer');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-gift-supplier', 'click', function () {
        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'gift', 'propagate-gift-supplier');
    });

    // Shipping Group
    //
    $('.amazon-tab', amazon_context).delegate('.amz-propagate-shipping_group-cat', 'click', function () {
        if (!confirm($('#amz-text-propagate-cat').val()))  return (false);

        propagate($(this), 'shipping_group', 'propagate-shipping_group-cat');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-shipping_group-shop', 'click', function () {
        if (!confirm($('#amz-text-propagate-shop').val()))  return (false);

        propagate($(this), 'shipping_group', 'propagate-shipping_group-shop');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-shipping_group-manufacturer', 'click', function () {
        if (!confirm($('#amz-text-propagate-manufacturer').val()))  return (false);

        propagate($(this), 'shipping_group', 'propagate-shipping_group-manufacturer');
    });

    $('.amazon-tab', amazon_context).delegate('.amz-propagate-shipping_group-supplier', 'click', function () {
        if (!confirm($('#amz-text-propagate-supplier').val()))  return (false);

        propagate($(this), 'shipping_group', 'propagate-shipping_group-supplier');
    });

    // Go to product page
    $('input[id^="amazon-goto-"]', amazon_context).click(function () {
        result = $(this).attr('id').match('^(.*)-(.*)$');
        asin = result[2];

        window.open($(this).attr('rel'));
    });


    function DisplayPrice(obj) {
        price = obj.val();
        if (price <= 0 || !price) return;
        price = parseFloat(price.replace(',', '.'));
        if (isNaN(price)) price = 0;
        price = price.toFixed(2);

        obj.val(price);
    }

    $('.amazon-tab', amazon_context).delegate('input[name^="amz-shipping"]', 'blur', function () {
        DisplayPrice($(this));
    });

    $('.amazon-tab', amazon_context).delegate('input[name^="amz-price"]', 'blur', function () {
        if (window.console)
            console.log(amazon_context, this);
        DisplayPrice($(this));
    });

    $('.amazon-tab', amazon_context).delegate('input[name^="amz-fbavalue"]', 'blur', function () {
        if (window.console)
            console.log(amazon_context, this);
        DisplayPrice($(this));
    });

    $('.amazon-tab', amazon_context).delegate('input[name^="amz-repricing"]', 'blur', function () {
        if (window.console)
            console.log(amazon_context, this);
        DisplayPrice($(this));
    });


    $('.amazon-tab', amazon_context).delegate('input[name^="amz-fba-"]', 'click', function () {
        var target_sub_tab = getSubTab();

        if ($(this).prop('checked')) {
            console.log($('input[name^="amz-fba"][rel="europe"]', target_sub_tab));
            $('input[name^="amz-fba"][rel="europe"]', target_sub_tab).attr('checked', true).prop('checked', true);
            $('.amazon-details.fba', target_sub_tab).show();
        }
        else {
            $('input[name^="amz-fba"][rel="europe"]', target_sub_tab).attr('checked', false).prop('checked', false);
            $('.amazon-details.fba', target_sub_tab).hide();
        }
    });


    /*
     * Bullet Points
     */

    function DeleteBulletPointItem(obj) {
        target_section = $(obj).parent().parent();
        target_section.find('input').val('').trigger('change');
        target_section.hide();

        var bullet_point_values = $('span[class^="amazon-bullet-container-"] input', target_section.parent()).serializeArray();

        if (window.console)
            console.log(bullet_point_values);

        if (bullet_point_values) {
            var i = 1;
            $.each(bullet_point_values, function (idx, bullet_point) {

                if (bullet_point.value.length) {
                    $('input[name=bullet_point' + i.toString() + ']', target_section).val(bullet_point.value);
                    i++;
                }
            });
        }
        $('span[class^=amazon-bullet-container]:last input', target_section).val('');
    }

    $('.amazon-sub-tab', amazon_context).delegate('.bulletpoint-action', 'click', function (ev) {
        var target_sub_tab = ev.delegateTarget;
		var target_action = $(this).find('img:visible');
		
        if (window.console)
            console.log('Amazon - Bullet Point', target_sub_tab);

		if (target_action.hasClass('amazon-bullet-point-del'))
		{
			DeleteBulletPointItem(target_action);
			return(false);
		}	
        if ($('span[class^="amazon-bullet-container-"]:visible', target_sub_tab).length >= 5) {
            alert($('input[class="amz-text-max-bullet"]', target_sub_tab).val());
            return (false);
        }

        var target_bullet = $('span[class^=amazon-bullet-container]:not(:visible):first', target_sub_tab).show();

        $('input', target_bullet).val('');

        var bullet_point_values = $('span[class^="amazon-bullet-container-"] input', target_sub_tab).serializeArray();

        if (window.console)
            console.log(bullet_point_values);

        if (bullet_point_values) {
            var i = 1;
            $.each(bullet_point_values, function (idx, bullet_point) {
                $('input[name=bullet_point' + i.toString() + ']', target_sub_tab).val('');

                if (bullet_point.value.length) {
                    $('input[name=bullet_point' + i.toString() + ']', target_sub_tab).val(bullet_point.value);
                    i++;
                }
            });
        }
        $('span[class^=amazon-bullet-container]:last input', target_sub_tab).val('');
    });

    // ASIN
    $('.amazon-tab', amazon_context).delegate('.amz-probe-asin', 'click', function (ev) {
        asin_button = $(this);
        source = ev.delegateTarget;
        ev.preventDefault();

        if (asin_button.attr('disabled'))
            return (false);

        var tokens = null;

        if ($('input[name^=amazon_token]') && $('input[name^=amazon_token]').length)
            tokens = $('input[name^=amazon_token]').serialize();

        var complex_id_product = getComplexProductId();
        var target_sub_tab = getSubTab();

        pAjax = new Object();
        pAjax.url = $('#amazon-product-options-json-url').val() + '&' + tokens + '&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';

        var productData = new Object();
        var pass = false;

        $('input[name=complex_id_product]').each(function (ind, target_radio) {

            if ($(this).val() && $(this).val().length)
                var target_complex_id_product = $(this).val();
            else
                var target_complex_id_product = id_product + '_0';

            if (target_complex_id_product != complex_id_product) // Do for 1 only
                return;

            var target_line = $(this).parents().get(1);

            field_ean = $('td[rel="ean13"]', target_line).text().trim();
            field_upc = $('td[rel="upc"]', target_line).text().trim();

            if (!field_ean && !field_ean.length && !field_upc && !field_upc.length)
                return;

            productData[target_complex_id_product] = new Object();
            productData[target_complex_id_product].ean13 = new Object();
            productData[target_complex_id_product].ean13.code = field_ean;
            productData[target_complex_id_product].ean13.asin = null;
            productData[target_complex_id_product].upc = new Object();
            productData[target_complex_id_product].upc.code = field_upc;
            productData[target_complex_id_product].upc.asin = null;

            pass = true;
        });

        if (!pass) {
            showErrorMessage($('.amz-asin-mustbeset:first').val());
            return (false);
        }

        if (window.console)
            console.log('asin probe', productData);

        asin_button.attr('disabled', true);

        $('img.asin-loader', asin_button.parent()).show();

        var params = {
            'action': 'asin-probe',
            'version': 4,
            'id_lang': id_lang,
            'id_product': $('#amazon-id-product').val(),
            'seed': new Date().valueOf()
        };
        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: $.param(params) + '&data=' + JSON.stringify(productData),
            success: function (data) {
                if (window.console)
                    console.log(data);

                probed = false;

                $('img', asin_button.parent()).show();
                $('img.asin-loader', asin_button.parent()).hide();

                asin_button.attr('disabled', false);

                if (typeof(data.product_data) == 'object') {
                    $.each(data.product_data, function (returned_complex_id_product, identifiers) {

                        var EAN_ASIN = identifiers.ean13.asin ? new String(identifiers.ean13.asin) : null;
                        var UPC_ASIN = identifiers.upc.asin ? new String(identifiers.upc.asin) : null;

                        var target_section = $(asin_button.parents().get(1));

                        if (EAN_ASIN && EAN_ASIN.length) {
                            $('input', target_section).val(EAN_ASIN).addClass('asin-probed');
                            probed = true;
                        }

                        else if (UPC_ASIN && UPC_ASIN.length) {
                            $('input', target_section).val(UPC_ASIN).addClass('asin-probed');
                            probed = true;
                        }
                        else if (identifiers.ean13.error) {
                            showErrorMessage(identifiers.ean13.error);
                        }
                        else if (identifiers.upc.error) {
                            showErrorMessage(identifiers.upc.error);
                        }
                    });
                }

                if (data.error)
                    showErrorMessage($('#amazon-product-options-message-error').val());
                else if (probed)
                    showSuccessMessage($('#amazon-product-options-message-success').val());
                else
                    showErrorMessage($('.amz-asin-unable:first').val()) + complex_id_product;

                if (data.output)
                    $('#amazon-product-tab .debug').html(data.output);

            },
            error: function (data) {
                asin_button.attr('disabled', false);

                $('img.asin-loader', asin_button.parent()).hide();

                if (window.console)
                    console.log('result', data);

                if (data.status && data.status.length)
                    $('#amazon-product-tab .debug').append('<pre>Status Code:' + data.status + '</pre>');
                if (data.statusText && data.statusText.length)
                    $('#amazon-product-tab .debug').append('<pre>Status Text:' + data.statusText + '</pre>');
                if (data.responseText && data.responseText.length)
                    $('#amazon-product-tab .debug').append('<pre>Response:' + data.responseText + '</pre>');

                showErrorMessage($('#amazon-product-options-message-error').val());
            }
        });
    });

    // ASIN
    $('.amazon-tab', amazon_context).delegate('.amz-probe-multi-asin', 'click', function (ev) {
        asin_button = $(this);
        source = ev.delegateTarget;

        if (asin_button.attr('rel') && asin_button.attr('rel') == 'disabled')
            return (false);

        $('img', asin_button.parent()).hide();
        $('img.asin-loader', asin_button.parent()).show();

        var tokens = null;

        if ($('input[name^=amazon_token]') && $('input[name^=amazon_token]').length)
            tokens = $('input[name^=amazon_token]').serialize();

        var complex_id_product = getComplexProductId();
        var target_sub_tab = getSubTab();

        pAjax = new Object();
        pAjax.url = $('#amazon-product-options-json-url').val() + '&' + tokens + '&callback=?';
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';

        var productData = new Object();

        $('input[name=complex_id_product]').each(function (ind, target_radio) {

            if ($(this).val() && $(this).val().length)
                var target_complex_id_product = $(this).val();
            else
                var target_complex_id_product = id_product + '_0';

            target_line = $(this).parents().get(1);

            productData[target_complex_id_product] = new Object();
            productData[target_complex_id_product].ean13 = new Object();
            productData[target_complex_id_product].ean13.code = $('td[rel="ean13"]', target_line).text().trim();
            productData[target_complex_id_product].ean13.asin = null;
            productData[target_complex_id_product].upc = new Object();
            productData[target_complex_id_product].upc.code = $('td[rel="upc"]', target_line).text().trim();
            productData[target_complex_id_product].upc.asin = null;
        });

        if (window.console)
            console.log('asin probe', productData);

        asin_button.attr('rel', 'disabled');

        var params = {
            'action': 'asin-probe',
            'version': 4,
            'id_lang': id_lang,
            'id_product': $('#amazon-id-product').val(),
            'seed': new Date().valueOf()
        };
        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: $.param(params) + '&data=' + JSON.stringify(productData),
            success: function (data) {
                if (window.console)
                    console.log(data);

                $('img', asin_button.parent()).show();
                $('img.asin-loader', asin_button.parent()).hide();

                asin_button.attr('rel', '');

                if (typeof(data.product_data) == 'object') {
                    $.each(data.product_data, function (returned_complex_id_product, identifiers) {
                        console.log(returned_complex_id_product, identifiers);

                        console.log(identifiers.ean13.asin);
                        var EAN_ASIN = identifiers.ean13.asin ? new String(identifiers.ean13.asin) : null;
                        var UPC_ASIN = identifiers.ean13.asin ? new String(identifiers.upc.asin) : null;

                        var target_line = $('#amazon-product-tab table tr[rel="' + returned_complex_id_product + '"]');

                        if (EAN_ASIN && EAN_ASIN.length)
                            $('td[rel="asin"]', target_line).text(EAN_ASIN).addClass('asin-probed');
                        else if (UPC_ASIN && UPC_ASIN.length)
                            $('td[rel="asin"]', target_line).text(UPC_ASIN).addClass('asin-probed');
                    });
                }

                if (data.error)
                    showErrorMessage($('#amazon-product-options-message-error').val());
                else
                    showSuccessMessage($('#amazon-product-options-message-success').val());

                if (data.output)
                    $('#amazon-product-tab .debug').html(data.output);
            },
            error: function (data) {
                asin_button.attr('rel', '');

                $('img', asin_button.parent()).show();
                $('img.asin-loader', asin_button.parent()).hide();

                if (window.console)
                    console.log('result', data);

                showErrorMessage($('#amazon-product-options-message-error').val());

                if (data.status && data.status.length)
                    $('#amazon-product-tab .debug').append('<pre>Status Code:' + data.status + '</pre>');
                if (data.statusText && data.statusText.length)
                    $('#amazon-product-tab .debug').append('<pre>Status Text:' + data.statusText + '</pre>');
                if (data.responseText && data.responseText.length)
                    $('#amazon-product-tab .debug').append('<pre>Response:' + data.responseText + '</pre>');
            }
        });
    });

    $('#amazon-product-tab').delegate('.amazon-editable', 'click', function (ev) {
        var target_text = $(this).text().trim();
        var target_field = $(this).attr('rel');
        var target_cell = $(this);

        var complex_id_product = getComplexProductId();
        var tokens = null;

        if ($('input[name^=amazon_token]') && $('input[name^=amazon_token]').length)
            tokens = $('input[name^=amazon_token]').serialize();

        if (!$(':input', target_cell) || !$(':input', target_cell).length) {
            target_cell.html('<input type="text" value="">');

            $(':input', target_cell).val(target_text).focus();
            target_cell.attr('data-initial', target_text);

            $(':input', target_cell).blur(function (ev) {
                var target_cell = $(this).parent();
                var updated_value = $(this).val().trim();
                var pass = true;

                if (target_cell.attr('data-initial') == updated_value)
                    pass = false;

                $(this).parent().text(updated_value);

                if (pass) {
                    pAjax = new Object();
                    pAjax.url = $('#amazon-product-options-json-url').val() + '&' + tokens + '&callback=?';
                    pAjax.type = 'POST';
                    pAjax.data_type = 'jsonp';

                    var params = {
                        'action': 'update-field',
                        'version': 4,
                        'id_lang': id_lang,
                        'id_product': $('#amazon-id-product').val(),
                        'complex_id_product': complex_id_product,
                        'seed': new Date().valueOf(),
                        'field': target_field,
                        'value': updated_value
                    };

                    $.ajax({
                        success: function (data) {
                            if (window.console)
                                console.log(data);

                            if (data.error) {
                                target_cell.html(target_cell.attr('data-initial'));
                                showErrorMessage($('#amazon-product-options-message-error').val());
                            }
                            else
                                showSuccessMessage($('#amazon-product-options-message-success').val());

                            if (data.output)
                                $('#amazon-product-tab .debug').html(data.output);
                        },
                        type: pAjax.type,
                        url: pAjax.url,
                        dataType: pAjax.data_type,
                        data: $.param(params),
                        error: function (data) {
                            if (window.console)
                                console.log('ERROR', data);
                            target_cell.html(target_cell.attr('data-initial'));
                            showErrorMessage($('#amazon-product-options-message-error').val());

                            if (data.status && data.status.length)
                                $('#amazon-product-tab .debug').append('<pre>Status Code:' + data.status + '</pre>');
                            if (data.statusText && data.statusText.length)
                                $('#amazon-product-tab .debug').append('<pre>Status Text:' + data.statusText + '</pre>');
                            if (data.responseText && data.responseText.length)
                                $('#amazon-product-tab .debug').append('<pre>Response:' + data.responseText + '</pre>');
                        }
                    });
                }
            });
        }
    });

    /*
     * Edit functions: copy, paste, delete
     */

    $('.amazon-item .copy-product-option', amazon_context).click(function (ev) {
        var current_tab = $('.amazon-sub-tab:visible', amazon_context);

        var inputs = $(':input[name]:not([type=hidden]), :input[rel]:not([type=hidden])', current_tab);
        var input_values = inputs.serializeArray();

        sessionStorage['amazon-copy'] = JSON.stringify(input_values);

        if (window.console)
            console.log('Copy buffer for Amazon', input_values);

        showSuccessMessage($('#amz-product-options-copy').val());

        return (false);
    });

    $('.amazon-item .paste-product-option', amazon_context).click(function (ev) {
        var current_tab = $('.amazon-sub-tab:visible', amazon_context);

        var paste_buffer = sessionStorage['amazon-copy'];

        if (window.console)
            console.log('Paste buffer for Amazon', paste_buffer);

        if (paste_buffer != null) {
            var paste_items = JSON.parse(paste_buffer);

            if (window.console)
                console.log(paste_items);

            if (paste_items) {
                $(':input[name][type=checkbox]', current_tab).attr('checked', false).prop('checked', false);
                $(':input[name][type=radio]', current_tab).attr('checked', false).prop('checked', false);
                $(':input[rel][type=radio]', current_tab).attr('checked', false).prop('checked', false);
                $(':input[name][type=text]', current_tab).val(null);

                $.each(paste_items, function (i, item) {
                    var short_name = item.name.replace(new RegExp('-[0-9]*$'), '');

                    var target_input = $('input[name^="' + short_name + '"]', current_tab);

                    if (window.console)
                        console.log('Paste:', item, short_name);

                    if ($(target_input).attr('type') == 'text') {
                        $(target_input).val(item.value);
                        if (!$(target_input).parent().is(':visible') && item.value.length) // for bullet points
                            $(target_input).parent().show();
                    }
                    else if ($(target_input).attr('type') == 'checkbox' || $(target_input).attr('type') == 'radio') {
                        $('input[name^="' + short_name + '"][value="' + item.value + '"]', current_tab).attr('checked', true).prop('checked', true);
                    }

                });
                showSuccessMessage($('#amz-product-options-paste').val());

                $('input[name]:visible:first', current_tab).trigger('change');//triggers ajax post
            }

        }
        return (false);
    });


});