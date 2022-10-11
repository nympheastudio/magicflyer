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

    $('#amazon_go').click(function () {
        window.open($('#amazon_url').val());
    });

    var tab_selector = $('#amazon-order-ps16');

    if (tab_selector.length) {
        $(tab_selector).parent().insertBefore('#formAddPaymentPanel')
    }

    /**
     * Switch customer name or address name
     */
    var customer_name_element    = $('.panel .panel-heading .icon-user').siblings('span').first();
    var shipping_address_element = $('#addressShipping').find('.well .row div').first();
    var invoice_address_element  = $('#addressInvoice').find('.well .row div').first();

    /**
     * Add switch first name / last name buttons to 3 corresponding places
     */
    function addNameSwitch()
    {
        customer_name_element.append(_appendSwitchElement('btn btn-default', 'switch-customer', 'switch-customer-name'))
            .find('a').css('display', 'inline-block');
        shipping_address_element.append(_appendSwitchElement('', 'switch-shipping', 'switch-address-shipping-name'));
        invoice_address_element.append(_appendSwitchElement('', 'switch-invoice', 'switch-address-invoice-name'));
    }

    function _appendSwitchElement(remove_class, add_class, data_action)
    {
        var amazon_switch = $('#amazon-switch');
        return amazon_switch.clone().removeAttr('id')
            .removeClass(remove_class).addClass(add_class)
            .data('action', data_action);
    }

    /**
     * Execute switch name when click button
     * @var int id_order Get from master view
     */
    $(document).on('click', '.amazon-switch-name', function(e) {
        e.preventDefault();
        var switch_button = $(this);
        if (switch_button.hasClass('loading')) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: switch_button.attr('href'),
            dataType: 'jsonp',
            data: {'id_order': id_order, 'action': switch_button.data('action')},
            beforeSend: function() {
                switch_button.addClass('loading');
                switch_button.find('i').addClass('icon-refresh icon-spin').removeClass('icon-exchange');
            },
            success: function (data) {
                if (data.error) {
                    showErrorMessage(data.message);
                } else {
                    // Update view
                    if (switch_button.hasClass('switch-customer')) {
                        $('.panel .panel-heading .icon-user').siblings('span').first().find('a').first().html(data.html);
                    } else {
                        var address_element;
                        if (data.update_all_addresses) {
                            address_element = shipping_address_element.add(invoice_address_element);
                        } else {
                            if (switch_button.hasClass('switch-shipping')) {
                                address_element = shipping_address_element;
                            } else if (switch_button.hasClass('switch-invoice')) {
                                address_element = invoice_address_element;
                            }
                        }

                        // Update address(es) name
                        if (address_element) {
                            // Remove all text and break line node, which is represent address
                            address_element.contents().filter(function() {
                                return this.nodeType === Node.TEXT_NODE || this.nodeName.toLowerCase() === 'br';
                            }).remove();
                            // html content text in head and tail, they'll be truncate if use $(data.html)
                            $('<div></div>').html(data.html).contents().insertBefore(address_element.find('a.amazon-switch-name'));
                        }
                    }

                    showSuccessMessage(data.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (textStatus !== 'error' || errorThrown !== '') {
                    showErrorMessage(textStatus + ': ' + errorThrown);
                }
            },
            complete: function() {
                switch_button.removeClass('loading');
                switch_button.find('i').addClass('icon-exchange').removeClass('icon-refresh icon-spin');
            }
        });
    });

    addNameSwitch();
});
 
