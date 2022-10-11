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

var pageMapping = false;
$(document).ready(function () {
    if (pageMapping) return;
    pageMapping = true;

    var tagify_mappings_params = {
        delimiters: [13, 44, 59, 58],
        addTagPrompt: $('#text-add-custom-attributes').val(),
        addTagOnBlur: true
    };

    var chosen_params = {
        'width': '400px',
        'search_contains': true,
        'placeholder_text_single': $('#text-add-select-option').val(),
        'no_results_text': $('#text-add-select-no-result').val()
    };

    $('select', $('#menudiv-mapping')).chosen(chosen_params);

    $('.mapping-collapse a').click(function () {
        target_div = $(this).parents().get(3);
        console.log(target_div);
        $(target_div).find('.mapping-collapse a').toggle();
        $(target_div).find('.amazon-mapping').toggle();
    });
    $('.free-mapping input.input-left, .free-mapping span.arrow').click(function () {
        target_div = $(this).parent();
        $(target_div).find('input.input-right').val($(target_div).find('input.input-left').val())
    });
    if ($.isFunction($('.tagify').tagify))
        $('.tagify').tagify(tagify_mappings_params);

});