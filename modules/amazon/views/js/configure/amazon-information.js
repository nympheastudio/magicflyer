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
        /**
         * Show PS info / Php info
         */
        (function () {
            var $loader         = $('.support-information-loader'),
                $content_result = $('#support-information-content');

            $.ajax({
                type: 'POST',
                url: $(this).attr('rel') + '&callback=?',
                data: {fields: $('input, select, textarea, button').length},
                beforeSend: function() {
                    $loader.show();
                },
                success: function (data) {
                    $content_result.html(data).slideDown();
                },
                error: function (jqXHR) {
                    $content_result.html(jqXHR).slideDown();
                },
                complete: function() {
                    $loader.hide();
                }
            });
        });

        /**
         * Download support files every time show the Information tab
         */
        $('#menudiv-informations').on('shown', function() {
            var $loader         = $('#support-information-file-loader'),    // Loader is show after ready
                $download       = $('#support-information-download'),
                $downloadLink   = $download.find('a.support-url');
            $download.hide();

            // Delay a little bit to keep tab changing smoothly
            setTimeout(function() {
                if (typeof html2canvas === 'undefined') {
                    $loader.hide();
                } else {
                    $loader.show();

                    html2canvas(document.body).then(function(canvas) {
                        $.ajax({
                            type: 'POST',
                            url: $downloadLink.attr('rel') + '&callback=?',
                            data: {screenShot: canvas.toDataURL("image/png")},
                            dataType: 'jsonp',
                            success: function(data) {
                                $loader.hide();
                                if (data.success && data.url) {
                                    $download.show();
                                    $downloadLink.attr('href', data.url);
                                }
                            },
                            complete: function() {
                                $loader.hide();
                            }
                        });
                    });
                }
            }, 1000);
        });
    });
})(jQuery);