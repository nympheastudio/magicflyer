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

var CS_Amazon = {};

(function($) {
    function listReports() {
        var url     = $('#reports-url').val(),
            type    = $('#reports-type').val(),
            pAjax   = {},
            data    = {
                type: type,
                context_key: $('#context_key').val(),
                instant_token: $('#instant_token').val(),
                rand: new Date().valueOf()
            };

        pAjax.type = 'POST';
        pAjax.url = url + '?action=list-reports&callback=?';
        pAjax.data = $('#country-selector').serialize() + '&' + $('#amazonParams').serialize() + '&' + $.param(data);
        pAjax.data_type = 'json';

        if (window.console) {
            console.log("Reports URL is :" + url);
        }
        var div_loader = $('#reports-loader');
        var div_errors = $('#reports-error');
        var div_warnings = $('#reports-warning');
        var div_result = $('#reports-result');

        $('#catalog-report-summary').hide().html('');
        $('#catalog-report-details').hide().html('');

        $('#reports-none-available').hide();

        div_loader.show();

        div_result.html('').hide();
        div_errors.html('').hide();
        div_warnings.html('').hide();

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            data: pAjax.data,
            dataType: pAjax.data_type,
            success: function (data) {
                div_loader.hide();
                div_errors.hide();
                div_result.hide();
                div_warnings.hide();

                if (window.console)
                    console.log(data);

                if (data.output) {
                    div_result.show();

                    $.each(data.output, function (o, output) {
                        div_result.append(output + '<br/>');
                    });
                }

                if (data.message) {
                    div_result.show();

                    $.each(data.messages, function (o, message) {
                        div_result.append(message + '<br/>');
                    });
                }
                if (data.warning) {
                    div_warnings.show();

                    $.each(data.warnings, function (w, warning) {
                        div_warnings.append(warning + '<br/>');
                    });
                }

                if (data.error) {
                    div_errors.show();

                    $.each(data.errors, function (e, errormsg) {
                        div_errors.append(errormsg + '<br/>');
                    });

                }

                // July-04-2018: Hide header and remove table content before render result
                var reportTab = $('#amazonReportOptions');
                reportTab.find('.report-table-heading').hide();
                reportTab.find('table.report tbody tr:gt(0)').remove();

                if (data.count) {
                    $('#reports-none-available').hide();
                    DisplayReportList(data.reports);
                }
                else {
                    $('#reports-none-available').show();
                }

            },
            error: function (data) {
                div_loader.hide();
                div_errors.show();
                div_errors.html('AJAX Error<br><br>' + data.responseText);

                ManageAjaxError(pAjax, data, div_errors);

                if (window.console)
                    console.log(data);
            }
        });
        return (false);
    }

    function DisplayReportList(reportset) {
        var irow = 0;

        $.each(reportset, function (r, report) {
            if (irow === 0)
                $('#amazonReportOptions .report-table-heading').show();

            if (window.console)
                console.log(report);

            // Clone Line, Append to the table and fill the order data
            var report_line = $('#amazonReportOptions .report-model:first').clone().appendTo('#amazonReportOptions table.table.report tbody.reports');
            report_line.attr('rel', report.id);

            report_line.children('[rel=id]').addClass('submission-feed-id').html(report.id);
            report_line.children('[rel=region]').html(report.region);
            report_line.children('[rel=type]').html(report.type);
            report_line.children('[rel=start]').html(report.timestart);
            report_line.children('[rel=stop]').html(report.timestop);
            report_line.children('[rel=duration]').html(report.duration);
            report_line.children('[rel=items]').html(report.records);

            report_line.addClass(irow++ % 2 ? 'alt_row' : '');
            report_line.show();
        });
        $('#amazonReportOptions .report-table-heading').show();
    }

    function ManageAjaxError(aCall, data, outdiv) {
        if (window.console) {
            console.log('Ajax Error');
            console.log(aCall);
            console.log(data);
        }
        outdiv.show().html($('#serror').val());

        if (data.output)
            outdiv.append('<br />' + data.output);

        if (data.responseText)
            outdiv.append('<br />' + data.responseText);

        outdiv.append('<hr />');
        outdiv.append($('#sdebug').val() + ':  ');

        outdiv.append('<form method="' + aCall.type + '" action="' + aCall.url + '?debug=1&' + aCall.data + '" target="_blank">' +
            '<input type="submit" class="button" id="send-debug" value="Execute in Debug Mode" /></form>');
    }

    $(document).ready(function() {
        var reportTab = $('#menudiv-report');
        // On load
        listReports();

        // Choose tab Report
        $('#menu-report').click(listReports);

        // Change platform
        $('#country-selector input[name="amazon_lang"]').change(listReports);

        // Select report
        reportTab.delegate('table.report tbody tr', 'click', function () {
            $('#menudiv-report table.report tbody tr').removeClass('report-selected');
            $(this).addClass('report-selected');
        });

        // Display report
        $('#submit-report-display').click(function () {
            var selectedReport = $('#menudiv-report table.report tbody tr.report-selected');
            if (!selectedReport.length) {
                alert($('#catalog-reports-select-msg').val());
                return (false);
            }
            var reportId    = selectedReport.attr('rel'),
                reportType  = selectedReport.find('td[rel=type]').text();

            var pAjax = {};
            pAjax.type = 'POST';
            pAjax.url = $('#reports-url').val();
            pAjax.data = $('#country-selector').serialize() + '&' + $('#amazonParams').serialize() + '&action=one-report&type=' + reportType + '&reportid=' + reportId + '&instant_token=' + $('#instant_token').val() + '&context_key=' + $('#context_key').val() + '&rand=' + new Date().valueOf();

            $('#wait-report').show();
            $('#submission-results').html('').hide();

            $.ajax({
                type: pAjax.type,
                url: pAjax.url,
                data: pAjax.data,
                success: function (data) {
                    $('#wait-report').hide();
                    $('#report-set').slideDown();
                    $('#submission-results').html(data).show();
                },
                error: function (data) {
                    $('#wait-report').hide();
                    $('#report-set').hide();
                    ManageAjaxError(pAjax, data, $('#amazon-report-error'));
                }
            });
            return (false);

        });
    });

    // Exploit for global usage
    CS_Amazon.listReports = listReports;
})(jQuery);