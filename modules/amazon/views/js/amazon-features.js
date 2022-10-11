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

    var start_time = [];
    var context = $('#content');
    var features_div = $('#menudiv-features', context);

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
    logtime('amazon-features.js overall', false);

    function toggleFeature(name)
    {
        var tcheckbox = $('#feat-' + name + '-cb', features_div);

        if (tcheckbox.attr('checked') == 'checked' || tcheckbox.attr('checked') == true) {
            $('*[rel="amazon-' + name + '"]', context).fadeIn().show();
        }
        else {
            /*
            * convert REL to use Class for performance
            $('*[rel="amazon-' + name + '"]', context).find('input[type=radio]').not('.fixed').attr('checked', false);
            $('*[rel="amazon-' + name + '"]', context).find('input[type=checkbox]').not('.fixed').attr('checked', false);
            $('*[rel="amazon-' + name + '"]', context).find('input[rel][type=checkbox]').not('.fixed').attr('checked', true);
            $('*[rel="amazon-' + name + '"]', context).find('input[rel][type=radio]').not('.fixed').attr('checked', true);
            $('*[rel="amazon-' + name + '"]', context).fadeOut().hide();
            */

            var selectorName = $(".amazon-" + name, context);
            selectorName.find('input[type=radio]').not('.fixed').attr('checked', false);
            selectorName.find('input[type=checkbox]').not('.fixed').attr('checked', false);
            selectorName.find('input[rel][type=checkbox]').not('.fixed').attr('checked', true);
            selectorName.find('input[rel][type=radio]').not('.fixed').attr('checked', true);
            selectorName.fadeOut().hide();

            // Subfeatures
            selectorName.find('.is-amazon-feature[rel]').each(function () {
                toggleFeature($(this).attr('rel'));
            });
        }

        $('#feat-' + name + '-cb', context).unbind('click');
        $('#feat-' + name + '-cb', context).click(function () {
            toggleFeature(name);
        });

    }

    toggleFeature('products-creation');
    toggleFeature('prices-rules');
    toggleFeature('second-hand');
    toggleFeature('filters');
    toggleFeature('import_products'); //todo: cannot file any element with import_products => need to update later
    toggleFeature('expert-mode');
    toggleFeature('europe');
    toggleFeature('worldwide');
    toggleFeature('messaging');
    toggleFeature('fba');
    toggleFeature('gcid');
    toggleFeature('orders');
    toggleFeature('repricing');
    toggleFeature('shipping');
    toggleFeature('cancel-orders');

    logtime('amazon-features.js overall', true);
});