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
(function($) {
    $(document).ready(function() {
        $('input[name="product_id_order_detail"]', $('#orderProducts')).each(function() {
            var tr = $(this).parents('tr').first();
            value = $(this).val();
            var content = $('.marketplace_detail[data-value=' + value + ']');

            if (content.length > 0) {
                var col = tr.find('td').filter(':visible').length;
                tr.after('<tr class="product-line-custom"><td></td><td colspan="' + (col - 1) + '">' + content.html() + '</td></tr>');
                tr.find('td').css('border-bottom', 'none');
            }
        });
    });
})(jQuery);