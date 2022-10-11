/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    EnvoiMoinsCher <api@boxtal.com>
 * @copyright 2007-2018 PrestaShop SA / 2011-2016 EnvoiMoinsCher
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registred Trademark & Property of PrestaShop SA
 */

$(document).ready(function() {
    populateStates($('#pz_iso').val(), '');
    updateSiretVat();
    updateDefaultEnl();

    $('#pz_iso').change(function() {
        populateStates($(this).val(), '');
        updateSiretVat();
        updateDefaultEnl();
    });
    
    $('select[name="default_shipping_country"]').change(function() {
        $('#pz_iso').val($(this).val());
        populateStates($(this).val(), $('#contact_etat').val());
        updateSiretVat();
        updateDefaultEnl();
    });
});

function populateStates(id_country, id_state)
{
    $.ajax({
        url: "index.php?controller=AdminEnvoiMoinsCher&id_country=" + id_country + "&option=returnStates&token=" + token,
        type: "GET",
        success: function (html) {
            if (html == "false") {
                $("#contact_etat").parent().hide();
                $("#contact_etat").parent().prev('label').hide();
                $("#contact_etat").html('');
            } else {
                $("#contact_etat").parent().show();
                $("#contact_etat").parent().prev('label').show();
                $("#contact_etat").html(html);

                // set submitted value if set
                if (id_state) {
                    $('#contact_etat').val(id_state);
                }
            }
        }
    });
}

function updateSiretVat()
{
    pays = $('#pz_iso').find(":selected")
    franceFirmChecked = pays.val() == "FR";
    spainFirmChecked = pays.val() == "ES";
    worldFirmChecked = pays.attr("in-ue") != 1;

    // change the placeholders and display of the siret inputs
    if (franceFirmChecked) {
    // France
        $('label[for="contact_stesiret"]').html(siret_label_fr);
        $('#tva').show();
        $('#tva input').removeAttr("disabled");
    } else if (spainFirmChecked) {
    // Spain
        $('label[for="contact_stesiret"]').html(siret_label_es);
        $('#tva').show();
        $('#tva input').removeAttr("disabled");
    } else if (!worldFirmChecked) {
    // Europe
        $('label[for="contact_stesiret"]').html(siret_label_world);
        $('#tva').show();
        $('#tva input').removeAttr("disabled");
    } else // World
    {
        $('label[for="contact_stesiret"]').html(siret_label_world);
        $('#tva').hide();
        $('#tva input').attr("disabled", "disabled");
    }
}

function updateDefaultEnl()
{
    var paysAddress = $('#pz_iso').find(":selected").val();
    var paysShipping = $('select[name="default_shipping_country"]').val();
    
    if (paysAddress != paysShipping) {
        $('#defaut_enl').hide();
        $('#defaut_enl input').attr("disabled", true);
    } else {
        $('#defaut_enl').show();
        $('#defaut_enl input').removeAttr("disabled");
    }
}