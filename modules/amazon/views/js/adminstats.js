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
    var chart_color = ['#1777B6','#2CA121','#E61409','#FF7F00','#6B399C','#B3591F'];

    $(document).ready(function () {
        // Change style of toolbar when change chart
        function toggleChangeChart(selected, color) {
            selected.siblings().removeClass('active').css('background-color', '');
            selected.addClass('active');
            selected.css('background-color', color);
        }

        $(document).on('click', '#amazon_chart_toolbar dl', function() {
            var chart_type = $(this).data('chart');
            var chart_data = window['chart_data'][chart_type];
            var index = $(this).data('index');
            var chart_label = $(this).data('chart-label');
            var color = chart_color[index];
            toggleChangeChart($(this), color);

            // Create chart
            var chart = nv.models.lineChart().useInteractiveGuideline(true);

            // Format date from Unix to human readable
            chart.xAxis.tickFormat(function(d) {
                var date = new Date(d * 1000);
                return date.toLocaleDateString();
            });

            // Format price for some chart
            if (chart_type.indexOf('sales') > -1) {
                chart.yAxis.tickFormat(function(d) {
                    /** @var object currency */
                    return formatCurrency(parseFloat(d), currency.format, currency.sign, currency.blank);
                });
            }

            // Get chart data from reverse variable
            var data = [];
            if (chart_data) {
                for (var i in chart_data) {
                    if (chart_data.hasOwnProperty(i)) {
                        var value = chart_data[i];
                        if (value.hasOwnProperty('date') && value.hasOwnProperty('data')) {
                            data.push({x: parseInt(value['date']), y: parseInt(value['data'])});
                        }
                    }
                }
            }

            // Draw chart into right place
            d3.select('#amazon-stats-chart .chart svg')
                .datum([{values: data, key: chart_label, color: color}])
                .call(chart);

            // Re-draw when resize window
            nv.utils.windowResize(chart.update);

            return chart;
        });

        // Active first chart after load
        $('#amazon_chart_toolbar').find('dl').first().click();
    });
})(jQuery);