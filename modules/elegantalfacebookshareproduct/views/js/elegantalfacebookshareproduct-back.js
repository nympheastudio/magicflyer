/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2018, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

var elegantalFormGroupClass = 'form-group';

jQuery(document).ready(function () {

    // Settings Page
    if (jQuery('[name="editSettings"]').length > 0) {
        elegantalFormVisibility(0);
        jQuery('input, select').on('change', function () {
            elegantalFormVisibility(250);
        });
    }

});

function elegantalFormVisibility(speed) {
    if (jQuery('[name="is_share_shop_url"]:checked').val() == 1) {
        jQuery('[name="url_to_share"]').parents('.' + elegantalFormGroupClass).hide();
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="url_to_share"]').parents('.' + elegantalFormGroupClass).prev('label').hide();
        }
    } else {
        jQuery('[name="url_to_share"]').parents('.' + elegantalFormGroupClass).fadeIn(speed);
        if (elegantalFormGroupClass == 'margin-form') {
            jQuery('[name="url_to_share"]').parents('.' + elegantalFormGroupClass).prev('label').fadeIn(speed);
        }
    }
}