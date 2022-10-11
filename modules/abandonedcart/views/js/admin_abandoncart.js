/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @category  PrestaShop Module
 * @author    knowband.com <support@knowband.com>
 * @copyright 2015 knowband
 * @license   see file: LICENSE.txt
 */

var msg_timeout = 10000; // 10 secs
var numeric_reg = /^[0-9]*$/;
//var decimal_reg = /^[0-9]{1,10}(?:\.\d{1,4})?$/;
var decimal_reg = /^[+]?([0-9]+(?:[\.][0-9]*)?|\.[0-9]+)$/;
var date_reg = /^([0-9]{4})\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/;

var currency_format = 1;
var currency_sign = '$';
var currency_blank = 0;

var current_name = '';

$(document).ready(function() {
    $('#abandoncart_enable_test').change(function() {
        if ($(this).is(":checked")) {
            $(".vss_testing_html").show();
        } else {
            $(".vss_testing_html").hide();
        }
    });
    /* Start - Code Modified by RS on 07-Sept-2017 stop the event in case the graph elements are not rendered */
    if ($('#placeholder').length) {
        $.ajax({
            url: action,
            data: '&ajax=true&method=getPieChartsData',
            type: 'post',
            datatype: 'json',
            success: function(json)
            {
                generatePieCharts(json);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Technical error occurred. Contact to support.');
            }
        });
    }
    /* End - Code Modified by RS on 07-Sept-2017 stop the event in case the graph elements are not rendered */

    $(".inc-err-msg").hide();

    $('.datepicker').datepicker({
        prevText: '',
        nextText: '',
        dateFormat: 'yy-mm-dd'
    });

    $("input:radio[name='velsof_abandoncart[schedule]']").live('click', function() {
        var value = $(this).val();
        if (value == 1)
        {
            $('#autoemail_template').css('display', 'inline-grid');
            $("#cron_instructions").show();
        }
        else {
            $('#autoemail_template').hide();
            $("#cron_instructions").hide();
        }
    });

    $('#email_template_list_body').on('dblclick', '.cge_tmlte_cell', function() {
        var id = $(this).attr('data');
        if (id != 0 && numeric_reg.test(id)) {
            var html = '<input data-id="' + id + '" type="text" name="changed_tml_name" value="' + $(this).html() + '" onblur="restoreTemplateName(this)" onkeyup="updateTemplateName(event, this)" />';
            current_name = $(this).html();
            $(this).html('');
            $(this).append(html);
            $(this).find('input').focus();
            $(this).find('input').select();
        }
    });

    $('#incentive_list_body').on('dblclick', '.ac_enable_disable_incentive', function() {
        var id = $(this).attr('data');
        disableIncentive(id, this);
    });

//    $('#velsof_filter').trigger('click');
});

function disableIncentive(incentive, e)
{
    $.ajax({
        type: 'POST',
        url: action,
        data: 'ajax=true&method=changeincentivestatus&incentive=' + incentive,
        dataType: 'json',
        success: function(json) {
            if (!json['status']) {
                $('#tab_incentive_msg_bar').removeClass('alert-success');
                $('#tab_incentive_msg_bar').removeClass('alert-danger');
                $('#tab_incentive_msg_bar').addClass('alert-danger');
                $('#tab_incentive_msg_bar').html(json['message']);
                $('#tab_incentive_msg_bar').show();
                setTimeout(function() {
                    $('#tab_incentive_msg_bar').hide();
                    $('#tab_incentive_msg_bar').html('');
                }, msg_timeout);
            } else {
                if (json['status_value'] == 1)
                    $(e).html('<span class="enabled_reminder_button">✔&nbsp;' + json['current_status'] + '</span>');
                else
                    $(e).html('<span class="disabled_reminder_button">❌&nbsp;' + json['current_status'] + '</span>');
                $(e).attr('data', json['data_value']);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert('Technical error occurred. Contact to support.');
        }
    });
}

function generatePieCharts(json)
{
    if (json['total_abandoned'] == 0 && json['total_converted'] == 0)
    {
        $('#pieChartHolder1').css('height', '90px');
        $('#placeholder').html('<br>' + ac_no_data_found_txt);
    }
    else
    {
        $('#pieChartHolder1').css('height', '410px');
        var data = [
            {label: ac_abandoned_carts_label, data: json['total_abandoned'], color: "#005CDE"},
            {label: ac_converted_carts_label, data: json['total_converted'], color: "#00A36A"}
        ];
        $.plot('#placeholder', data, {
            series: {
                pie: {
                    innerRadius: 0.4,
                    show: true,
                    label: {
                        show: true,
                        radius: 0.8,
                        formatter: function(label, series) {
                            return '<div style="border:1px solid grey;font-size:11px;text-align:center;padding:4px;color:white;background:black;opacity:0.5;">' +
                                    label + ' : ' +
                                    Math.round(series.percent) +
                                    '% (' + series.data[0][1] + ')</div>';
                        }
                    }
                }
            },
            legend: {
                show: false
            }
        });
    }

    if (json['total_abandoned_amount'] == 0 && json['total_converted_amount'] == 0)
    {
        $('#pieChartHolder2').css('height', '90px');
        $('#placeholder2').html('<br>' + ac_no_data_found_txt);
    }
    else
    {
        $('#pieChartHolder2').css('height', '410px');
        var data = [
            {label: ac_abandoned_amount_label, data: json['total_abandoned_amount'], color: "#005CDE"},
            {label: ac_converted_amount_label, data: json['total_converted_amount'], color: "#00A36A"}
        ];
        $.plot('#placeholder2', data, {
            series: {
                pie: {
                    innerRadius: 0.4,
                    show: true,
                    label: {
                        show: true,
                        radius: 0.8,
                        formatter: function(label, series) {
                            return '<div style="border:1px solid grey;font-size:11px;text-align:center;padding:4px;color:white;background:black;opacity:0.5;">' +
                                    label + ' : ' +
                                    Math.round(series.percent) +
                                    '% (' + formatCurrency(series.data[0][1], ac_curr_format, ac_curr_sign, ac_currency_blank) + ')</div>';
                        }
                    }
                }
            },
            legend: {
                show: false
            }
        });
    }
}

function submitConfiguration()
{
    
    var messgeObj = $('#content').find('.bootstrap').find('.alert');
    $(messgeObj).parent().remove();
    var errorMsg = '';
    var error = false;

    var days_value = $('input[name="velsof_abandoncart[delay_days]"]').val();
    if (days_value == '') {
        error = true;
        errorMsg += required_days + '<br>';
    } else if (days_value != 0 && !numeric_reg.test(days_value)) {
        error = true;
        errorMsg += invalid_num_msg + '<br>';
    } else if (days_value > 1000 || days_value < 0) {
        error = true;
        errorMsg += invalid_day_range + '<br>';
    }

    if (!error) {
        var delay_hrs = $('input[name="velsof_abandoncart[delay_hours]"]').val();
        if (delay_hrs == '') {
            error = true;
            errorMsg += required_hours + '<br>';
        } else if (delay_hrs != 0 && !numeric_reg.test(delay_hrs)) {
            error = true;
            errorMsg += invalid_num_msg + '<br>';
        } else if (delay_hrs > 24 || delay_hrs < 0) {
            error = true;
            errorMsg += invalid_num_range + '<br>';
        }
        
    }
    if ($("#abandoncart_enable_test").is(":checked")) {
        var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
        if (!error) {
            var email = $('input[name="velsof_abandoncart[testing_email_id]"]').val();
            if (email == '') {
                error = true;
                errorMsg += required_email + '<br>';
            } else if (email != 0 && !pattern.test(email)) {
                error = true;
                errorMsg += email_err + '<br>';
            }
        }
    }

    
//    var messgeObj = $('#content').find('.bootstrap').find('.alert');
//    $(messgeObj).parent().remove();
//    var errorMsg = '';
//    var error = false;
//
//    var days_value = $('input[name="velsof_abandoncart[delay_days]"]').val();
//    if (days_value == '') {
//        error = true;
//        errorMsg += required_field_msg + '<br>';
//    } else if (days_value != 0 && !numeric_reg.test(days_value)) {
//        error = true;
//        errorMsg += invalid_num_msg + '<br>';
//    }
//
//    if (!error) {
//        var delay_hrs = $('input[name="velsof_abandoncart[delay_hours]"]').val();
//        if (delay_hrs == '') {
//            error = true;
//            errorMsg += required_field_msg + '<br>';
//        } else if (delay_hrs != 0 && !numeric_reg.test(delay_hrs)) {
//            error = true;
//            errorMsg += invalid_num_msg + '<br>';
//        }
//    }
//    if ($("#abandoncart_enable_test").is(":checked")) {
//        var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
//        if (!error) {
//            var email = $('input[name="velsof_abandoncart[testing_email_id]"]').val();
//            if (email == '') {
//                error = true;
//                errorMsg += required_field_msg + '<br>';
//            } else if (email != 0 && !pattern.test(email)) {
//                error = true;
//                errorMsg += email_err + '<br>';
//            }
//        }
//    }

    if (!error) {
        $('#abandoncart_configuration_form').submit();
    } else {
        var errorHtml = '<div class="bootstrap abd-global-message"><div class="alert alert-danger">';
        errorHtml += '<button type="button" class="close" data-dismiss="alert">×</button>';
        errorHtml += errorMsg;
        errorHtml += '</div></div>';
        $('#velsof_abandoncart_container').before(errorHtml);
        setTimeout(function() {
            $('#velsof_abandoncart_container .abd-global-message').remove();
        }, msg_timeout);
    }

}

function dismiss_ac_modal(modal) {
    $('#' + modal + '_load').hide();
    $('#' + modal).modal('hide');
    $('#' + modal + '_progress').hide();
}


function generateGraph()
{
    var flag = 0;
    if (document.getElementById("date-start").value == '')
    {

        document.getElementById("date-start").placeholder = ac_start_date_msg;
        document.getElementById("date-start").style.background = "rgb(255,209,209)";
        flag++;
    }
    else
    {
        document.getElementById("date-start").placeholder = "";
        document.getElementById("date-start").style.background = "";
    }
    if (document.getElementById("date-end").value == '')
    {
        document.getElementById("date-end").placeholder = ac_end_date_msg;
        document.getElementById("date-end").style.background = "rgb(255,209,209)";
        flag++;
        //<span id='date-start-error' style='display:none'>Please enter start date</span>
    }
    else
    {
        document.getElementById("date-end").placeholder = "";
        document.getElementById("date-end").style.background = "";
    }

    if (document.getElementById("date-start").value > document.getElementById("date-end").value)
    {
        document.getElementById("error_date").innerHTML = ac_date_msg;
        flag++;
    }
    else
    {
        document.getElementById("error_date").innerHTML = "";
    }

    if (flag != 0)
    {
        return false;
    }
    else
    {
        /* Start - Code Modified by RS on 07-Sept-2017 for adding a loader when the Graph is being rendered */
        $('#abd_analytics_loader').show();
        $.ajax({
            url: action,
            data: "start=" + $("#date-start").val() + "&end=" + $("#date-end").val() + '&ajax=true&method=getChartData',
            type: 'post',
            datatype: 'json',
            success: function(json)
            {
                drawChart(json);
                $('#abd_analytics_loader').hide();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Technical error occurred. Contact to support.');
            }
        });
        /* End - Code Modified by RS on 07-Sept-2017 for adding a loader when the Graph is being rendered */
    }
}

function drawChart(json)
{
    var tickss = [];
    var datas1 = [];
    var datas2 = [];
    var datas3 = [];
    var datas4 = [];
    for (i = 0; i < json['stats']['abandon_carts'].length; i++)
    {
        var valuex = json['stats']['abandon_carts'][i][0];
        tickss.push([i, valuex]);
        var valuey = json['stats']['abandon_carts'][i][1];
        datas1.push([i, valuey]);
    }
    for (i = 0; i < json['stats']['converted_carts'].length; i++)
    {
        var valuez = json['stats']['converted_carts'][i][1];
        datas2.push([i, valuez]);
    }
    for (i = 0; i < json['stats']['abandon_amount'].length; i++)
    {
        var valueb = json['stats']['abandon_amount'][i][1];
        datas3.push([i, valueb]);
    }
    for (i = 0; i < json['stats']['converted_amount'].length; i++)
    {
        var valuec = json['stats']['converted_amount'][i][1];
        datas4.push([i, valuec]);
    }

    var dataset = [
        {
            label: ac_abandoned_carts_label,
            data: datas1,
            yaxis: 1,
            bars: {
                show: true,
                barWidth: 0.35,
                fill: true,
                order: 1,
                fillColor: "#dc3912"
            },
            color: "#dc3912"
        },
        {
            label: ac_converted_carts_label,
            data: datas2,
            yaxis: 1,
            bars: {
                show: true,
                barWidth: 0.35,
                fill: true,
                order: 2,
                fillColor: "#3366cc"
            },
            color: "#3366cc"
        },
        {
            label: ac_abandoned_amount_label,
            data: datas3,
            yaxis: 2,
            lines: {
                show: true,
                fill: false,
                lineWidth: 2.5,
                order: 3,
                fillColor: "#ff9900"
            },
            color: "#ff9900"
        },
        {
            label: ac_converted_amount_label,
            data: datas4,
            yaxis: 2,
            lines: {
                show: true,
                fill: false,
                lineWidth: 2.5,
                order: 4,
                fillColor: "#990099"
            },
            color: "#990099"
        },
    ];
    var options = {
        xaxis: {
            axisLabel: ac_timeline_label,
            ticks: tickss,
            axisLabelUseCanvas: true,
            axisLabelFontSizePixels: 12,
            axisLabelFontFamily: 'Verdana, Arial',
            axisLabelPadding: 10,
            autoscaleMargin: 0.01
        },
        yaxes: [{
                position: 'left',
                axisLabel: ac_num_carts_label,
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 3
            }, {
                position: 'right',
                axisLabel: ac_carts_amount_label,
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 3
            }],
        legend: {
            noColumns: 0,
            labelBoxBorderColor: "#000000",
            position: "nw",
            container: $('#graph_loader_legend')
        },
        grid: {
            hoverable: true,
            borderWidth: 1
        }
    };

    $.plot($("#flot-placeholder"), dataset, options);

    var previousPoint = null, previousLabel = null;

    $("#flot-placeholder").on("plothover", function(event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();

                var x = Math.round(item.datapoint[0]);
                if (x < 0) {
                    x = 0;
                }
                var y = item.datapoint[1];

                var color = item.series.color;
                var custom_price = '';
                if (item.seriesIndex == 2 || item.seriesIndex == 3)
                    custom_price = formatCurrency(y, ac_curr_format, ac_curr_sign, ac_currency_blank);
                else
                    custom_price = y;
                showTooltip(item.pageX,
                        item.pageY,
                        color,
                        "<strong>" + item.series.label + "</strong><br>" + item.series.xaxis.ticks[x].label + " : <strong>" + custom_price + " </strong>");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });

    function showTooltip(x, y, color, contents) {
        $('<div id="tooltip">' + contents + '</div>').css({
            position: 'absolute',
            display: 'none',
            top: y - 40,
            left: x - 120,
            border: '1px solid ' + color,
            padding: '3px',
            'font-size': '11px',
            'border-radius': '5px',
            'background-color': '#fff',
            'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
            opacity: 0.9
        }).appendTo("body").fadeIn(200);
    }
}


function getGraph()
{
    /* Start - Code Modified by RS on 07-Sept-2017 stop the event in case the graph elements are not rendered */
    if ($('#velsof_filter').length) {
        $('#velsof_filter').click();
    }
    /* End - Code Modified by RS on 07-Sept-2017 stop the event in case the graph elements are not rendered */
}

//function openEmailTemplate(key_template)
//{
//    var key = 0;
//    if (key_template > 0 && key_template != '') {
//        key = key_template;
//    }
//    
//    if(key > 0){
//        $('#modal_email_template_load').show();
//        getEmailTemplateToEdit(key);
//    }else{
//        $('#modal_email_template_load').show();
//        $('#modal_email_template .row-no-display').hide();
//        $('#modal_email_template').modal({'show':true, 'backdrop':'static'});
//    }
//}

function loadNewEmailTemplate()
{
    $('#modal_email_template #rd-temp-type-dis').attr('checked', 'checked');
    $('#modal_email_template #rd-temp-type-dis').trigger('click');
    $('#modal_email_template_load').show();
    $('#modal_email_template .row-no-display').hide();
    $('#modal_email_template select').val(1);
    $("#vss_cart_image").attr('src', image_path + "cart_image_1.png")
    $("#vss_cart_image_template").attr('src', image_path + "cart_image_1.png")
    $('#modal_email_template').modal({'show': true, 'backdrop': 'static'});
    $('.validation-advice').remove();
}

function getEmailTemplateToEdit()
{   
    $('.validation-advice').remove();
    $.ajax({
        url: action,
        data: '&ajax=true&method=getnewemailtemplate&type=' + $('#modal_email_template input[name="email_template[type]"]:checked').val(),
        type: 'post',
        datatype: 'json',
        success: function(json)
        {
            $('#modal_email_template .row-no-display').show();
            $('#email_template_form_key').attr('value', json['id_template']);
            $('#email_template_name_inp').attr('value', json['name']);
            $('#email_template_subject_inp').attr('value', json['subject']);

            $('input[name="email_template[type]"]').removeAttr('checked');
            $('input[name="email_template[type]"]').each(function() {
                if ($(this).attr('value') == json['type']) {
                    $(this).attr('checked', true);
                }
            });

            $('#email_template_body_inp_editor').html(json['body']);
            tinyMCE.get('email_template_body_inp_editor').setContent(json['body']);

            $('#modal_email_template_load').hide();
            $('#modal_email_template').modal({'show': true, 'backdrop': 'static'});
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert('Technical error occurred. Contact to support.');
            closeTemplatePopup();
        }
    });
}

function closeTemplatePopup()
{
    $('#modal_email_template_load').hide();
    $('#modal_email_template .row-no-display').hide();
    $('#email_template_form_key').attr('value', 0);
    $('#modal_email_template input[name="email_template[type]"]').removeAttr('checked');
    $('#modal_email_template input[type="text"], #modal_email_template textarea').val('');
    tinyMCE.get('email_template_body_inp_editor').setContent('');
    $('#modal_email_template').modal('hide');
}

function saveEmailTemplate(ele)
{
    var error = false;
    $('.validation-advice').remove();
    
//    $('#modal_email_template input[type="text"]').each(function() {
//        var field_name =  $(this).val();
//        if ($(this).hasClass('required_entry') && $(this).val() == '') {
//            error = true;
//            $(this).parent().append('<span class="validation-advice">' + required_template_name + '</span>');
//        } else if (field_name.length > 255) {
//            error = true;
//            $(this).parent().append('<span class="validation-advice">' + max_255_length + '</span>');
//        }
//    });
        
    if ($('input[name="email_template[name]"]').val().trim() == '') {
        error = true;
        $('input[name="email_template[name]"]').parent().append('<span class="validation-advice">' + required_template_name + '</span>');
    } else if ($('input[name="email_template[name]"]').val().length > 255) {
        error = true;
        $('input[name="email_template[name]"]').parent().append('<span class="validation-advice">' + max_255_length + '</span>');
    }
    
    if ($('input[name="email_template[subject]"]').val().trim() == '') {
        error = true;
        $('input[name="email_template[subject]"]').parent().append('<span class="validation-advice">' + required_email_subject + '</span>');
    } else if ($('input[name="email_template[subject]"]').val().length > 255) {
        error = true;
        $('input[name="email_template[subject]"]').parent().append('<span class="validation-advice">' + max_255_length + '</span>');
    }
    
    var text_data_html = tinyMCE.get('email_template_body_inp_editor').getContent('');
    var email_content = $(text_data_html).text();
    if (email_content.trim() == '') {
        error = true;
        $('#mce_32').parent().append('<span class="validation-advice">' + required_email_content + '</span>');
    }
    
    if (!error) {
        $(ele).parent().find('.modal_email_template_progress').css('display', 'inline-block');
        tinyMCE.triggerSave();
        $.ajax({
            url: action,
            data: $('#modal_email_template input, #modal_email_template textarea, #vss_cart_template').serialize() + '&ajax=true&method=saveemailtemplate',
            type: 'post',
            datatype: 'json',
            success: function(json)
            {
                $(ele).parent().find('.modal_email_template_progress').hide();
                if (json['status']) {
                    var key = $('#email_template_form_key').attr('value');
                    if (key != '' && key > 0) {
                        $('#email_template_row_id_' + json['data']['id_template']).html(json['data']['id_template']);
                        $('#email_template_row_nm_' + json['data']['id_template']).html(json['data']['name']);
                        $('#email_template_row_type_' + json['data']['id_template']).html(json['data']['template_type_text']);
                    } else {
                        getNextTemplatesPage($('#etemplate_list_current_page').attr('value'));
                    }

                    $('#tab_email_msg_bar').removeClass('alert-success');
                    $('#tab_email_msg_bar').removeClass('alert-danger');
                    $('#tab_email_msg_bar').addClass('alert-success');
                    $('#tab_email_msg_bar').html(json['message']);
                    $('#tab_email_msg_bar').show();
                    setTimeout(function() {
                        $('#tab_email_msg_bar').hide();
                        $('#tab_email_msg_bar').html('');
                    }, msg_timeout);
                    refreshTemplateDropdown();
                    closeTemplatePopup();
                } else {
                    $('#modal_email_template_process_status').addClass('alert-danger');
                    $('#modal_email_template_process_status').html(json['message']);
                    $('#modal_email_template_process_status').show();
                    setTimeout(function() {
                        $('#modal_email_template_process_status').hide();
                        $('#modal_email_template_process_status').removeClass('alert-success');
                        $('#modal_email_template_process_status').removeClass('alert-danger');
                        $('#modal_email_template_process_status').html('');
                    }, msg_timeout);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $(ele).parent().find('.modal_email_template_progress').hide();
                alert('Technical error occurred. Contact to support.');
            }
        });
    }
}

function remEmailTemplate(key_template)
{
    $('#tab_email_msg_bar').removeClass('alert-success');
    $('#tab_email_msg_bar').removeClass('alert-danger');
    var cnfr = confirm(conf_msg);
    if (cnfr) {
        $.ajax({
            url: action,
            data: '&ajax=true&method=rememailtemplate&key_template=' + key_template,
            type: 'post',
            datatype: 'json',
            success: function(json)
            {
                if (json['status']) {
                    $('#tab_email_msg_bar').addClass('alert-success');
                    getNextTemplatesPage($('#etemplate_list_current_page').attr('value'));
                    refreshTemplateDropdown();
                } else {
                    $('#tab_email_msg_bar').addClass('alert-danger');
                }
                $('#tab_email_msg_bar').html(json['message']);
                $('#tab_email_msg_bar').show();
                setTimeout(function() {
                    $('#tab_email_msg_bar').hide();
                    $('#tab_email_msg_bar').html('');
                }, msg_timeout);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Technical error occurred. Contact to support.');
            }
        });
    }
}

function getNextTemplatesPage(page,element)
{
    try {
        if($(element).attr("title") == "Next") {
            var page = $.trim($("#template_pagination").children(".abd-pagination-right").children(".abd-pagination").children(".active").html());
            page = parseInt(page) + 1;
        }
        if($(element).attr("title") == "Previous") {
            var page = $.trim($("#template_pagination").children(".abd-pagination-right").children(".abd-pagination").children(".active").html());
            page = parseInt(page) - 1;
        }
    } catch(e) {}
        
    $.ajax({
        url: action,
        data: '&ajax=true&method=gettemplatelist&temp_page_number=' + page,
        type: 'post',
        datatype: 'json',
        beforeSend: function()
        {
            $('#etemplate-list-block .abd-bigloader').show();
        },
        success: function(json)
        {
            $('#etemplate-list-block .abd-bigloader').hide();
            if (json['flag']) {
                var html = '';
                var i = 0;
                var row_class = 'odd';
                for (i = 0; i < json['data'].length; i++) {
                    if (i % 2 == 0)
                        row_class = 'even';
                    html += '<tr class="pure-table-' + row_class + '" id="email_template_list_' + json['data'][i]['id_template'] + '">';
                    html += '<td id="email_template_row_id_' + json['data'][i]['id_template'] + '" class="right">' + (json['start_serial'] + i) + '</td>';
                    html += '<td id="email_template_row_nm_' + json['data'][i]['id_template'] + '" data="' + json['data'][i]['id_template'] + '" class="cge_tmlte_cell">' + json['data'][i]['name'] + '</td>';
                    html += '<td id="email_template_row_type_' + json['data'][i]['id_template'] + '">' + json['data'][i]['template_type_text'] + '</td>';
                    html += '<td class="list_action_btn">';
                    html += '<a href="javascript:void(0)" onclick="openEmailTranslationPopup(' + json['data'][i]['id_template'] + ');" class="glyphicons edit"><i data-toggle="tooltip"  data-placement="top" data-original-title="'+edit_trans+'"></i></a>';
                    html += '<a href="javascript:void(0)" onclick="remEmailTemplate(' + json['data'][i]['id_template'] + ');" class="glyphicons remove"><i data-toggle="tooltip"  data-placement="top" data-original-title="'+del_msg+'"></i></a>';
                    html += '</td>';
                    html += '</tr>';
                }
                $('#email_template_list_body').html(html);
                $('#etemplate_list_current_page').attr('value', page);
            } else {
                var html = '<tr class="pure-table-odd empty-tbl"><td colspan="4" class="center"><span>' + empty_list_msg + '</span></td><tr>';
                $('#email_template_list_body').html(html);
            }
            $('#etemplate-list-block .paginator-block').html(json['pagination']);
            $('[data-toggle="tooltip"]').tooltip();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('#etemplate-list-block .abd-bigloader').hide();
            alert('Technical error occurred. Contact to support.');
        }
    });
}


function openEmailTranslationPopup(id_template)
{
    $('.validation-advice').remove();
    $('#modal_email_template_translation input[name="email_template_translation[id_template]"]').attr('value', id_template);
    $('#modal_email_template_translation').modal({'show': true, 'backdrop': 'static'});
    $.ajax({
            url: action,
            data: '&ajax=true&method=getdefaultlanguage',
            type: 'post',
            datatype: 'json',
            success: function(json)
            {
                getEmailTemplateTranslation(json);
            }
        })
}

function getEmailTemplateTranslation(id_lang)
{
    $('.validation-advice').remove();
    if (id_lang > 0) {
        var id_template = $('#modal_email_template_translation input[name="email_template_translation[id_template]"]').val();
        $('#modal_email_template_translation #template_translation_loader').show();
        $.ajax({
            url: action,
            data: '&ajax=true&method=getemailtemplatetranslation&id_template=' + id_template + '&id_lang=' + id_lang,
            type: 'post',
            datatype: 'json',
            success: function(json)
            {
                $('#modal_email_template_translation #template_translation_loader').hide();
                $('#modal_email_template_translation .row-no-display').show();
                $('#modal_email_template_translation select[name="email_template_translation[id_lang]"] option').each(function(a) {
                    if ($(this).attr('value') == id_lang) {
                        $(this).attr('selected', true);
                    }
                })
                $('#modal_email_template_translation input[name="email_template_translation[id_template]"]').attr('value', json['id_template']);
                $('#modal_email_template_translation input[name="email_template_translation[id_template_content]"]').attr('value', json['id_template_content']);
                $('#modal_email_template_translation input[name="email_template_translation[subject]"]').attr('value', json['subject']);
                $('#modal_email_template_translation select[name="email_template_translation[cart_template]"]').attr('value', json['cart_template']);

                $("#vss_cart_image").attr('src', image_path + "cart_image_" + json['cart_template'] + ".png")
                $("#vss_cart_image_template").attr('src', image_path + "cart_image_" + json['cart_template'] + ".png")

                $('#email_template_translation_body_inp_editor').html(json['body']);
                tinyMCE.get('email_template_translation_body_inp_editor').setContent(json['body']);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Technical error occurred. Contact to support.');
                closeTemplateTranslationPopup();
            }
        });
    }
}

function saveEmailTemplateTranslation(ele)
{
    var error = false;
    
    $('.validation-advice').remove();

    if ($('input[name="email_template_translation[subject]"]').val().trim() == '') {
        error = true;
        $('input[name="email_template_translation[subject]"]').parent().append('<span class="validation-advice">' + required_email_subject + '</span>');
    } else if ($('input[name="email_template_translation[subject]"]').val().length > 255) {
        error = true;
        $('input[name="email_template_translation[subject]"]').parent().append('<span class="validation-advice">' + max_255_length + '</span>');
    }
    var text_data_html = tinyMCE.get('email_template_translation_body_inp_editor').getContent('');
    var email_content = $(text_data_html).text();
    
    if (email_content.trim() == '') {
        error = true;
        $('#mce_71').parent().append('<span class="validation-advice">' + required_email_content + '</span>');
    }

    if ($('#modal_email_template_translation select[name="email_template_translation[id_lang]"] option:selected').attr('value') == 0) {
        error = true;
        $('#modal_email_template_translation select[name="email_template_translation[id_lang]"]').parent().append('<span class="validation-advice">' + required_field_msg + '</span>');
    }

    if (!error) {
        $(ele).parent().find('.modal_email_template_translation_progress').css('display', 'inline-block');
        tinyMCE.triggerSave();
        $.ajax({
            url: action,
            data: $('#modal_email_template_translation input, #modal_email_template_translation textarea, #modal_email_template_translation select').serialize() + '&ajax=true&method=saveemailtemplatetranslation',
            type: 'post',
            datatype: 'json',
            success: function(json)
            {
                $(ele).parent().find('.modal_email_template_translation_progress').hide();
                if (json['status']) {

                    $('#tab_email_msg_bar').removeClass('alert-success');
                    $('#tab_email_msg_bar').removeClass('alert-danger');
                    $('#tab_email_msg_bar').addClass('alert-success');
                    $('#tab_email_msg_bar').html(json['message']);
                    $('#tab_email_msg_bar').show();
                    setTimeout(function() {
                        $('#tab_email_msg_bar').hide();
                        $('#tab_email_msg_bar').html('');
                    }, msg_timeout);
                    closeTemplateTranslationPopup();
                } else {
                    $('#modal_email_template_translation_process_status').addClass('alert-danger');
                    $('#modal_email_template_translation_process_status').html(json['message']);
                    $('#modal_email_template_translation_process_status').show();
                    setTimeout(function() {
                        $('#modal_email_template_translation_process_status').hide();
                        $('#modal_email_template_translation_process_status').removeClass('alert-success');
                        $('#modal_email_template_translation_process_status').removeClass('alert-danger');
                        $('#modal_email_template_translation_process_status').html('');
                    }, msg_timeout);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $(ele).parent().find('.modal_email_template_translation_progress').hide();
                alert('Technical error occurred. Contact to support.');
            }
        });
    }
}

function closeTemplateTranslationPopup()
{
    $('#modal_email_template_load').hide();
    $('#modal_email_template_translation .row-no-display').hide();
    $('#modal_email_template_translation #template_translation_loader').hide();
    $('#modal_email_template_translation input[name="email_template_translation[id_template]"]').attr('value', 0);
    $('#modal_email_template_translation input[name="email_template_translation[id_template_content]"]').attr('value', 0);
    $('#modal_email_template_translation select[name="email_template_translation[id_lang]"] option').removeAttr('selected');
    $('#modal_email_template_translation input[type="text"], #modal_email_template_translation textarea').val('');
    tinyMCE.get('email_template_translation_body_inp_editor').setContent('');
    $('#modal_email_template_translation').modal('hide');
}

function removeModalStatus(modal)
{
    $('#' + modal + '_process_status').hide();
    $('#' + modal + '_process_status').removeClass('alert-success');
    $('#' + modal + '_process_status').removeClass('alert-danger');
    $('#' + modal + '_process_status').html('');
}

function editIncentive(key_incentive)
{
    $('.validation-advice').remove();
    $('#modal_incentive_form_editor').find('input:text').val('');
    var key = 0;
    if (key_incentive > 0 && key_incentive != '') {
        key = key_incentive;
    }
    $('#modal_incentive_form_load').show();

    $.ajax({
        url: action,
        data: '&ajax=true&method=getincentivedetail&key_incentive=' + key,
        type: 'post',
        datatype: 'json',
        success: function(json)
        {
            $('#modal_incentive_form_key').attr('value', json['id_incentive']);

            $('select[name="incentive[id_template]"] option').removeAttr('selected');
            $('select[name="incentive[id_template]"] option').each(function() {
                if ($(this).attr('value') == json['id_template']) {
                    $(this).attr('selected', 'selected');
                }
            });

            $('input[name="incentive[discount_type]"]').removeAttr('checked');
            $('input[name="incentive[discount_type]"]').each(function() {
                if ($(this).attr('value') == json['discount_type']) {
                    $(this).attr('checked', true);
                }
            });

            if (key == 0)
            {
                $('input[name="incentive[discount_value]"]').attr('placeholder', json['discount_value']);
                $('input[name="incentive[min_cart_value]"]').attr('placeholder', json['min_cart_value']);
                $('input[name="incentive[min_cart_value_for_mails]"]').attr('placeholder', json['min_cart_value_for_mails']);
                $('input[name="incentive[coupon_validity]"]').attr('placeholder', json['coupon_validity']);
                $('input[name="incentive[delay_days]"]').attr('placeholder', json['delay_days']);
                $('input[name="incentive[delay_hrs]"]').attr('placeholder', json['delay_hrs']);
            }
            else
            {
                $('input[name="incentive[discount_value]"]').attr('value', json['discount_value']);
                $('input[name="incentive[min_cart_value]"]').attr('value', json['min_cart_value']);
                $('input[name="incentive[min_cart_value_for_mails]"]').attr('value', json['min_cart_value_for_mails']);
                $('input[name="incentive[coupon_validity]"]').attr('value', json['coupon_validity']);
                $('input[name="incentive[delay_days]"]').attr('value', json['delay_days']);
                $('input[name="incentive[delay_hrs]"]').attr('value', json['delay_hrs']);
            }

            $('input[name="incentive[has_free_shipping]"]').removeAttr('checked');
            $('input[name="incentive[has_free_shipping]"]').each(function() {
                if ($(this).attr('value') == json['has_free_shipping']) {
                    $(this).attr('checked', true);
                }
            });

            $('input[name="incentive[status]"]').removeAttr('checked');
            $('input[name="incentive[status]"]').each(function() {
                if ($(this).attr('value') == json['status']) {
                    $(this).attr('checked', true);
                }
            });


            if (json['incentive_type'] == 1)
            {
                $('.discount_incentive_fields').hide();
                $('input[name="incentive[incentive_type]"]').val(json['incentive_type']);
                $('input[name="incentive[discount_value]"]').val(0);
                $('input[name="incentive[min_cart_value]"]').val(0);
                $('input[name="incentive[coupon_validity]"]').val(1);
            }
            else
            {
                $('.discount_incentive_fields').show();
                $('input[name="incentive[incentive_type]"]').val(json['incentive_type']);
            }

            $('#modal_incentive_form_load').hide();
            $('#modal_incentive_form').modal({'show': true, 'backdrop': 'static'});
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('#modal_incentive_form_load').hide();
            alert('Technical error occurred. Contact to support.');
        }
    });
}

function checkTemplateType(ele)
{
    $.ajax({
        url: action,
        data: '&ajax=true&method=checkTemplateType&template_id=' + $(ele).val(),
        type: 'post',
        datatype: 'json',
        success: function(json)
        {
            if (json['type'] == 1)
            {
                $('.discount_incentive_fields').hide();
                $('input[name="incentive[incentive_type]"]').val(json['type']);
                $('input[name="incentive[discount_value]"]').val(0);
                $('input[name="incentive[min_cart_value]"]').val(0);
                $('input[name="incentive[coupon_validity]"]').val(1);
            }
            else
            {
                $('.discount_incentive_fields').show();
                $('input[name="incentive[incentive_type]"]').val(json['type']);
            }
        }
    });
}

function checkTemplateTypeAndProceed(ele)
{
    if ($('input[name="incentive[incentive_type]"]').val() == 1)
        saveReminder(ele);
    else
        saveIncentive(ele);
}

function saveReminder(ele)
{
    var error = false;
    $('.validation-advice').remove();
    if ($('select[name="incentive[id_template]"]').val() == 0) {
        error = true;
        $('select[name="incentive[id_template]"]').parent().append('<span class="validation-advice">' + select_template_name + '</span>');
    }

    if ($('input[name="incentive[min_cart_value_for_mails]"]').val() == '') {
        error = true;
        $('input[name="incentive[min_cart_value_for_mails]"]').parent().append('<span class="validation-advice">' + required_min_cart + '</span>');
    } else if (!decimal_reg.test($('input[name="incentive[min_cart_value_for_mails]"]').val())) {
        error = true;
        $('input[name="incentive[min_cart_value_for_mails]"]').parent().append('<span class="validation-advice">' + invalid_flt_msg + '</span>');
    } else if ($('input[name="incentive[min_cart_value_for_mails]"]').val() > 9999999999 ) {
        error = true;
        $('input[name="incentive[min_cart_value_for_mails]"]').parent().append('<span class="validation-advice">' + number_length_error + '</span>');
    }

    if ($('input[name="incentive[delay_days]"]').val() == '') {
        error = true;
        $('input[name="incentive[delay_days]"]').parent().append('<span class="validation-advice">' + required_days + '</span>');
    } else if (!numeric_reg.test($('input[name="incentive[delay_days]"]').val())) {
        error = true;
        $('input[name="incentive[delay_days]"]').parent().append('<span class="validation-advice">' + invalid_num_msg + '</span>');
    } else if ($('input[name="incentive[delay_days]"]').val() > 1000 || $('input[name="incentive[delay_days]"]').val() < 0) {
        error = true;
        $('input[name="incentive[delay_days]"]').parent().append('<span class="validation-advice">' + invalid_day_range + '</span>');
    }

    if ($('input[name="incentive[delay_hrs]"]').val() == '') {
        error = true;
        $('input[name="incentive[delay_hrs]"]').parent().append('<span class="validation-advice">' + required_hours + '</span>');
    } else if (!numeric_reg.test($('input[name="incentive[delay_hrs]"]').val())) {
        error = true;
        $('input[name="incentive[delay_hrs]"]').parent().append('<span class="validation-advice">' + invalid_num_msg + '</span>');
    } else if ($('input[name="incentive[delay_hrs]"]').val() > 24 || $('input[name="incentive[delay_hrs]"]').val() < 0) {
        error = true;
        $('input[name="incentive[delay_hrs]"]').parent().append('<span class="validation-advice">' + invalid_num_range + '</span>');
    }
    
    if (!error) {
        $(ele).parent().find('.modal_incentive_form_progress').css('display', 'inline-block');
        $.ajax({
            url: action,
            data: $('#modal_incentive_form input, #modal_incentive_form select').serialize() + '&ajax=true&method=saveincentive',
            type: 'post',
            datatype: 'json',
            success: function(json)
            {
                $(ele).parent().find('.modal_incentive_form_progress').hide();
                if (json['status']) {
                    var key = $('#modal_incentive_form_key').attr('value');
                    if (key != '' && key > 0) {
                        if (json['data']['status'] == 1)
                            var status_txt = '<span class="enabled_reminder_button">✔&nbsp;' + json['data']['status_txt'] + '</span>';
                        else
                            var status_txt = '<span class="disabled_reminder_button">❌&nbsp;' + json['data']['status_txt'] + '</span>';
//			    $('#incentive_row_id_'+json['data']['id_incentive']).html(json['data']['id_incentive']);
                        $('#incentive_row_nm_' + json['data']['id_incentive']).html(json['data']['name']);
                        $('#incentive_row_type_' + json['data']['id_incentive']).html(json['data']['discount_type_txt']);
                        $('#incentive_row_val_' + json['data']['id_incentive']).html(json['data']['discount_value_txt']);
//			    $('#incentive_row_cval_'+json['data']['id_incentive']).html(json['data']['min_cart_value_txt']);
                        $('#incentive_row_cvalid_' + json['data']['id_incentive']).html(json['data']['coupon_validity_txt']);
//			    $('#incentive_row_fship_'+json['data']['id_incentive']).html(json['data']['has_free_shipping_txt']);
                        $('#incentive_row_stat_' + json['data']['id_incentive']).html(status_txt);
                        $('#incentive_row_dely_' + json['data']['id_incentive']).html(json['data']['delay_txt']);
                    } else {
                        getNextIncentivePage($('#abd_incentive_list_current_page').attr('value'));
                    }

                    $('#tab_incentive_msg_bar').removeClass('alert-success');
                    $('#tab_incentive_msg_bar').removeClass('alert-danger');
                    $('#tab_incentive_msg_bar').addClass('alert-success');
                    $('#tab_incentive_msg_bar').html(json['message']);
                    $('#tab_incentive_msg_bar').show();
                    setTimeout(function() {
                        $('#tab_incentive_msg_bar').hide();
                        $('#tab_incentive_msg_bar').html('');
                    }, msg_timeout);
                    dismiss_ac_modal('modal_incentive_form');
                } else {
                    $('#modal_incentive_form_process_status').addClass('alert-danger');
                    $('#modal_incentive_form_process_status').html(json['message']);
                    $('#modal_incentive_form_process_status').show();
                    setTimeout(function() {
                        $('#modal_incentive_form_process_status').hide();
                        $('#modal_incentive_form_process_status').removeClass('alert-success');
                        $('#modal_incentive_form_process_status').removeClass('alert-danger');
                        $('#modal_incentive_form_process_status').html('');
                    }, msg_timeout);
                }
                
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $(ele).parent().find('.modal_incentive_form_progress').hide();
                alert('Technical error occurred. Contact to support.');
            }
        });
    }
}

function saveIncentive(ele)
{      
    var error = false;
    $('.validation-advice').remove();
    if ($('select[name="incentive[id_template]"]').val() == 0) {
        error = true;
        $('select[name="incentive[id_template]"]').parent().append('<span class="validation-advice">' + select_template_name + '</span>');
    }

    if ($('input[name="incentive[min_cart_value_for_mails]"]').val() == '') {
        error = true;
        $('input[name="incentive[min_cart_value_for_mails]"]').parent().append('<span class="validation-advice">' + required_minimum_cart + '</span>');
    } else if (!decimal_reg.test($('input[name="incentive[min_cart_value_for_mails]"]').val())) {
        error = true;
        $('input[name="incentive[min_cart_value_for_mails]"]').parent().append('<span class="validation-advice">' + invalid_flt_msg + '</span>');
    } else if ($('input[name="incentive[min_cart_value_for_mails]"]').val() > 9999999999 ) {
        error = true;
        $('input[name="incentive[min_cart_value_for_mails]"]').parent().append('<span class="validation-advice">' + number_length_error + '</span>');
    }

    if ($('input[name="incentive[discount_value]"]').val() == '' || $('input[name="incentive[discount_value]"]').val() == 0) {
        error = true;
        $('input[name="incentive[discount_value]"]').parent().append('<span class="validation-advice">' + required_discount_value + '</span>');
    } else if (!decimal_reg.test($('input[name="incentive[discount_value]"]').val())) {
        error = true;
        $('input[name="incentive[discount_value]"]').parent().append('<span class="validation-advice">' + invalid_flt_msg + '</span>');
    } else if ($('input[name="incentive[discount_value]"]').val() > 9999999999 ) {
        error = true;
        $('input[name="incentive[discount_value]"]').parent().append('<span class="validation-advice">' + number_length_error + '</span>');
    }

    if ($('input[name="incentive[min_cart_value]"]').val() == '') {
        error = true;
        $('input[name="incentive[min_cart_value]"]').parent().append('<span class="validation-advice">' + required_min_cart + '</span>');
    } else if (!decimal_reg.test($('input[name="incentive[min_cart_value]"]').val())) {
        error = true;
        $('input[name="incentive[min_cart_value]"]').parent().append('<span class="validation-advice">' + invalid_flt_msg + '</span>');
    } else if ($('input[name="incentive[min_cart_value]"]').val() > 9999999999 ) {
        error = true;
        $('input[name="incentive[min_cart_value]"]').parent().append('<span class="validation-advice">' + number_length_error + '</span>');
    }

    if ($('input[name="incentive[coupon_validity]"]').val() == '') {
        error = true;
        $('input[name="incentive[coupon_validity]"]').parent().parent().append('<span class="validation-advice">' + required_coupon_validity + '</span>');
    } else if (!numeric_reg.test($('input[name="incentive[coupon_validity]"]').val())) {
        error = true;
        $('input[name="incentive[coupon_validity]"]').parent().parent().append('<span class="validation-advice">' + invalid_num_msg + '</span>');
    } else if ($('input[name="incentive[coupon_validity]"]').val() > 99999 ) {
        error = true;
        $('input[name="incentive[coupon_validity]"]').parent().append('<span class="validation-advice">' + number_length_error + '</span>');
    }

    if ($('input[name="incentive[delay_days]"]').val() == '') {
        error = true;
        $('input[name="incentive[delay_days]"]').parent().append('<span class="validation-advice">' + required_days + '</span>');
    } else if (!numeric_reg.test($('input[name="incentive[delay_days]"]').val())) {
        error = true;
        $('input[name="incentive[delay_days]"]').parent().append('<span class="validation-advice">' + invalid_num_msg + '</span>');
    } else if ($('input[name="incentive[delay_days]"]').val() > 1000 || $('input[name="incentive[delay_days]"]').val() < 0) {
        error = true;
        $('input[name="incentive[delay_days]"]').parent().append('<span class="validation-advice">' + invalid_day_range + '</span>');
    }

    if ($('input[name="incentive[delay_hrs]"]').val() == '') {
        error = true;
        $('input[name="incentive[delay_hrs]"]').parent().append('<span class="validation-advice">' + required_hours + '</span>');
    } else if (!numeric_reg.test($('input[name="incentive[delay_hrs]"]').val())) {
        error = true;
        $('input[name="incentive[delay_hrs]"]').parent().append('<span class="validation-advice">' + invalid_num_msg + '</span>');
    } else if ($('input[name="incentive[delay_hrs]"]').val() > 24 || $('input[name="incentive[delay_hrs]"]').val() < 0) {
        error = true;
        $('input[name="incentive[delay_hrs]"]').parent().append('<span class="validation-advice">' + invalid_num_range + '</span>');
    }

    if (!error) {
        $(ele).parent().find('.modal_incentive_form_progress').css('display', 'inline-block');
        $.ajax({
            url: action,
            data: $('#modal_incentive_form input, #modal_incentive_form select').serialize() + '&ajax=true&method=saveincentive',
            type: 'post',
            datatype: 'json',
            success: function(json)
            {
                $(ele).parent().find('.modal_incentive_form_progress').hide();
                if (json['status']) {
                    var key = $('#modal_incentive_form_key').attr('value');
                    if (key != '' && key > 0) {
                        if (json['data']['status'] == 1)
                            var status_txt = '<span class="enabled_reminder_button">✔&nbsp;' + json['data']['status_txt'] + '</span>';
                        else
                            var status_txt = '<span class="disabled_reminder_button">❌&nbsp;' + json['data']['status_txt'] + '</span>';
//                        $('#incentive_row_id_'+json['data']['id_incentive']).html(json['data']['id_incentive']);
                        $('#incentive_row_nm_' + json['data']['id_incentive']).html(json['data']['name']);
                        $('#incentive_row_type_' + json['data']['id_incentive']).html(json['data']['discount_type_txt']);
                        $('#incentive_row_val_' + json['data']['id_incentive']).html(json['data']['discount_value_txt']);
//                        $('#incentive_row_cval_'+json['data']['id_incentive']).html(json['data']['min_cart_value_txt']);
                        $('#incentive_row_cvalid_' + json['data']['id_incentive']).html(json['data']['coupon_validity_txt']);
//                        $('#incentive_row_fship_'+json['data']['id_incentive']).html(json['data']['has_free_shipping_txt']);
                        $('#incentive_row_stat_' + json['data']['id_incentive']).html(status_txt);
                        $('#incentive_row_dely_' + json['data']['id_incentive']).html(json['data']['delay_txt']);
                    } else {
                        getNextIncentivePage($('#abd_incentive_list_current_page').attr('value'));
                    }

                    $('#tab_incentive_msg_bar').removeClass('alert-success');
                    $('#tab_incentive_msg_bar').removeClass('alert-danger');
                    $('#tab_incentive_msg_bar').addClass('alert-success');
                    $('#tab_incentive_msg_bar').html(json['message']);
                    $('#tab_incentive_msg_bar').show();
                    setTimeout(function() {
                        $('#tab_incentive_msg_bar').hide();
                        $('#tab_incentive_msg_bar').html('');
                    }, msg_timeout);
                    dismiss_ac_modal('modal_incentive_form');
                } else {
                    $('#modal_incentive_form_process_status').addClass('alert-danger');
                    $('#modal_incentive_form_process_status').html(json['message']);
                    $('#modal_incentive_form_process_status').show();
                    setTimeout(function() {
                        $('#modal_incentive_form_process_status').hide();
                        $('#modal_incentive_form_process_status').removeClass('alert-success');
                        $('#modal_incentive_form_process_status').removeClass('alert-danger');
                        $('#modal_incentive_form_process_status').html('');
                    }, msg_timeout);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $(ele).parent().find('.modal_incentive_form_progress').hide();
                alert('Technical error occurred. Contact to support.');
            }
        });
    }
}

function remIncentive(key)
{
    $('#tab_incentive_msg_bar').removeClass('alert-success');
    $('#tab_incentive_msg_bar').removeClass('alert-danger');
    var cnfr = confirm(conf_rem_msg);
    if (cnfr) {
        $.ajax({
            url: action,
            data: '&ajax=true&method=remincentive&key=' + key,
            type: 'post',
            datatype: 'json',
            success: function(json)
            {
                if (json['status']) {
                    $('#tab_incentive_msg_bar').addClass('alert-success');
                    getNextIncentivePage($('#abd_incentive_list_current_page').attr('value'));
                } else {
                    $('#tab_incentive_msg_bar').addClass('alert-danger');
                }
                $('#tab_incentive_msg_bar').html(json['message']);
                $('#tab_incentive_msg_bar').show();
                setTimeout(function() {
                    $('#tab_incentive_msg_bar').hide();
                    $('#tab_incentive_msg_bar').html('');
                }, msg_timeout);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Technical error occurred. Contact to support.');
            }
        });
    }
}

function getNextIncentivePage(page,element)
{
    try {
        if($(element).attr("title") == "Next") {
            var page = $.trim($("#reminder_pagination").children(".abd-pagination-right").children(".abd-pagination").children(".active").html());
            page = parseInt(page) + 1;
        }
        if($(element).attr("title") == "Previous") {
            var page = $.trim($("#reminder_pagination").children(".abd-pagination-right").children(".abd-pagination").children(".active").html());
            page = parseInt(page) - 1;
        }
    } catch(e) {}
    
    $.ajax({
        url: action,
        data: '&ajax=true&method=getincentivelist&inc_page_number=' + page,
        type: 'post',
        datatype: 'json',
        beforeSend: function()
        {
            $('#inc-list-block .abd-bigloader').show();
        },
        success: function(json)
        {
            $('#inc-list-block .abd-bigloader').hide();
            if (json['flag']) {
                var html = '';
                var i = 0;
                var row_class = 'odd';
                for (i = 0; i < json['data'].length; i++) {
                    if (i % 2 == 0)
                        row_class = 'even';

                    if (json['data'][i]['status'] == 1)
                        var status_txt = '<span class="enabled_reminder_button">✔&nbsp;' + json['data'][i]['status_txt'] + '</span>';
                    else
                        var status_txt = '<span class="disabled_reminder_button">❌&nbsp;' + json['data'][i]['status_txt'] + '</span>';
                    html += '<tr class="pure-table-' + row_class + '" id="incentive_list_' + json['data'][i]['id_incentive'] + '">';
                    html += '<td id="incentive_row_id_' + json['data'][i]['id_incentive'] + '" class="right">' + (json['start_serial'] + i) + '</td>';
                    html += '<td id="incentive_row_nm_' + json['data'][i]['id_incentive'] + '">' + json['data'][i]['name'] + '</td>';
                    html += '<td id="incentive_row_type_' + json['data'][i]['id_incentive'] + '">' + json['data'][i]['discount_type_txt'] + '</td>';
                    html += '<td class="right" id="incentive_row_val_' + json['data'][i]['id_incentive'] + '">' + json['data'][i]['discount_value_txt'] + '</td>';
//                    html += '<td class="right" id="incentive_row_cval_'+json['data'][i]['id_incentive']+'">'+json['data'][i]['min_cart_value_txt']+'</td>';
                    html += '<td id="incentive_row_cvalid_' + json['data'][i]['id_incentive'] + '">' + json['data'][i]['coupon_validity_txt'] + '</td>';
//                    html += '<td id="incentive_row_fship_'+json['data'][i]['id_incentive']+'">'+json['data'][i]['has_free_shipping_txt']+'</td>';
                    html += '<td id="incentive_row_stat_' + json['data'][i]['id_incentive'] + '" data="' + json['data'][i]['id_incentive'] + '_' + json['data'][i]['status'] + '" class="ac_enable_disable_incentive">' + status_txt + '</td>';
                    html += '<td id="incentive_row_dely_' + json['data'][i]['id_incentive'] + '">' + json['data'][i]['delay_txt'] + '</td>';
                    html += '<td class="list_action_btn">';
                    html += '<a href="javascript:void(0)" onclick="editIncentive(' + json['data'][i]['id_incentive'] + ');" class="glyphicons edit"><i data-toggle="tooltip"  data-placement="top" data-original-title="'+edit_msg+'"></i></a>';
                    html += '<a href="javascript:void(0)" onclick="remIncentive(' + json['data'][i]['id_incentive'] + ');" class="glyphicons remove"><i data-toggle="tooltip"  data-placement="top" data-original-title="'+del_msg+'"></i></a>';
                    html += '</td>';
                    html += '</tr>';
                }
                $('#incentive_list_body').html(html);
                $('#abd_incentive_list_current_page').attr('value', page);
            } else {
                var html = '<tr class="pure-table-odd empty-tbl"><td colspan="10" class="center"><span>' + empty_list_msg + '</span></td><tr>';
                $('#incentive_list_body').html(html);
            }
            $('#inc-list-block .paginator-block').html(json['pagination']);
            $('[data-toggle="tooltip"]').tooltip();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('#inc-list-block .abd-bigloader').hide();
            alert('Technical error occurred. Contact to support.');
        }
    });
}

function resetAbandonedListFilter(page_number)
{
    $('#abd-list-filters select[name="abd_filter_type"] option').removeAttr('selected');
    $('#abd-list-filters input[name="abd_filter_type_email"]').attr('value', '');
    $('#abd-list-filters select[name="abd_filter_ctype"] option').removeAttr('selected');
    getAbandonedList(page_number);
}

function getAbandonedListonchange(a)
{
    getAbandonedList($('#abd_list_current_page').attr('value'));
}

/*
 * Function Modified by RS on 06-Sept-2017 for solving the problem of time delay on switchning pages in Abandoned Cart List
 */
function getAbandonedList(page, refresh)
{
    var refresh_str = '';
    refresh_str = (typeof refresh !== 'undefined') && refresh == '1' ? '&refresh_list=true' : '';
    var filter_str = '';
    if ($('#abd-list-filters input[name="abd_filter_type_email"]').attr('value') != '') {
        filter_str += '&qtype=' + $('#abd-list-filters select[name="abd_filter_type"]').val();
        filter_str += '&email=' + encodeURIComponent($('#abd-list-filters input[name="abd_filter_type_email"]').val());
    }

    if ($('#abd-list-filters select[name="abd_filter_ctype"]').val() != '') {
        filter_str += '&ctype=' + $('#abd-list-filters select[name="abd_filter_ctype"]').val();
    }

    $.ajax({
        url: action,
        data: '&ajax=true&method=getabandonlist&page_number=' + page + filter_str + '&cart_per_page=' + $('select[name=carts_per_page]').val()+refresh_str,
        type: 'post',
        datatype: 'json',
        beforeSend: function()
        {
            $('#abd-list-block .abd-bigloader').show();
        },
        success: function(json)
        {
            $('#abd-list-block .abd-bigloader').hide();

            if (json['flag']) {
                var html = '';
                var i = 0;
                var row_class = 'odd';
                for (i = 0; i < json['data'].length; i++) {
                    if (i % 2 == 0)
                        row_class = 'even';
                    html += '<tr class="pure-table-' + row_class + '">';
                    html += '<td class="right">' + (json['start_serial'] + i) + '</td>';
                    if (typeof json['data'][i]['tracked'] != 'undefined' && json['data'][i]['tracked'] == 1)
                    {
                        if (json['data'][i]['firstname'] != 'ABC' && json['data'][i]['lastname'] != 'DEF')
                            html += '<td><span class="tbl-cl-main-txt">' + json['data'][i]['firstname'] + ' ' + json['data'][i]['lastname'] + '</span><span class="tbl-cl-sub-txt">' + json['data'][i]['email'] + '</span>';
                        else
                            html += '<td><span class="tbl-cl-main-txt">' + ac_gust_cus_label + '</span><span class="tbl-cl-sub-txt">' + json['data'][i]['email'] + '</span>';
                    }
                    else if (json['data'][i]['id_customer'] <= 0)
                        html += '<td><span class="tbl-cl-main-txt">' + ac_gust_cus_label + '</span>';
                    else
                        html += '<td><span class="tbl-cl-main-txt">' + json['data'][i]['firstname'] + ' ' + json['data'][i]['lastname'] + '</span><span class="tbl-cl-sub-txt">' + json['data'][i]['email'] + '</span>';

                    html += '<span class="tbl-cl-sub-txt" style="display: block;">Language: ' + json['data'][i]['language_text'] + '</span></td>';
                    if (json['data'][i]['is_guest'] == 1) {
                        html += '<td >' + ac_guest_txt + '</td>';
                    } else {
                        html += '<td >' + ac_registered_txt + '</td>';
                    }

                    if (!json['data'][i]['has_coupon']) {
                        if ((typeof json['data'][i]['reminder_sent'] != 'undefined') && json['data'][i]['reminder_sent'] == 1)
                            html += '<td >' + reminder_sent_txt + '</td>';
                        else
                            html += '<td >' + ac_no_coupon_txt + '</td>';
                    } else {
                        html += '<td ><a href="javascript:void(0)" onclick="displayCouponDetail(' + json['data'][i]['id_customer'] + ', \'' + json['data'][i]['email'] + '\')">' + ac_coupon_details_txt + '</a></td>';
                    }
                    html += '<td>' + json['data'][i]['date_upd'] + '</td>';
                    html += '<td class="list_action_btn">';
                    if (json['data'][i]['id_customer'] <= 0)
                    {
                        if (typeof json['data'][i]['tracked'] != 'undefined' && json['data'][i]['tracked'] == 1)
                            html += '<a href="javascript:void(0)" onclick="displayReminderModal(' + json['data'][i]['id_customer'] + ',' + json['data'][i]['id_cart'] + ',' + json['data'][i]['id_abandon'] + ',' + json['data'][i]['id_lang'] + ');" class="glyphicons bell"><i data-toggle="tooltip"  data-placement="top" data-original-title="' + ac_send_non_discount_email_txt + '"></i></a>';
                        else
                            html += '<a href="javascript:void(0)" onclick="" class="glyphicons bell disabaled_glyphicons"><i data-toggle="tooltip" style="cursor: default;" data-placement="top" data-original-title="' + ac_email_not_txt + '"></i></a>';
                    }
                    else
                        html += '<a href="javascript:void(0)" onclick="displayReminderModal(' + json['data'][i]['id_customer'] + ',' + json['data'][i]['id_cart'] + ',' + json['data'][i]['id_abandon'] + ',' + json['data'][i]['id_lang'] + ');" class="glyphicons bell"><i data-toggle="tooltip"  data-placement="top" data-original-title="' + ac_send_non_discount_email_txt + '"></i></a>';

                    if (json['data'][i]['id_customer'] <= 0)
                        html += '<a href="javascript:void(0)" onclick="" class="glyphicons gift disabaled_glyphicons"><i data-toggle="tooltip" style="cursor: default;" data-placement="top" data-original-title="' + ac_email_not_txt + '"></i></a>';
                    else
                        html += '<a href="javascript:void(0)" onclick="displayDisocuntEmailModal(' + json['data'][i]['id_customer'] + ',' + json['data'][i]['id_cart'] + ',' + json['data'][i]['id_abandon'] + ',' + json['data'][i]['id_lang'] + ');" class="glyphicons gift"><i data-toggle="tooltip"  data-placement="top" data-original-title="' + ac_send_discount_email_txt + '"></i></a>';
                    html += '<a href="javascript:void(0)" onclick="displayCartDetail(' + json['data'][i]['id_customer'] + ',' + json['data'][i]['id_cart'] + ');" class="glyphicons shopping_cart"><i data-toggle="tooltip"  data-placement="top" data-original-title="' + ac_view_products_txt + '"></i></a>';
                    html += '<a type="' + json['data'][i]['id_abandon'] + '" href="javascript:void(0)" onclick="deleteAbandon(this);" class="glyphicons remove"><i data-toggle="tooltip"  data-placement="top" data-original-title="' + ac_remove_cart_txt + '"></i></a>';
                    html += '</td>';
                    html += '</tr>';
                }
                $('#abandon_cart_list_body').html(html);
                $('#abd_list_current_page').attr('value', page);
            } else {
                var html = '<tr class="pure-table-odd empty-tbl"><td colspan="6" class="center"><span>' + empty_list_msg + '</span></td><tr>';
                $('#abandon_cart_list_body').html(html);
            }
            $('#abd-list-block .paginator-block').html(json['pagination']);
            $('[data-toggle="tooltip"]').tooltip();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('#abd-list-block .abd-bigloader').hide();
            alert('Technical error occurred. Contact to support.');
        }
    });
}


function getConvertedList(page)
{
    $.ajax({
        url: action,
        data: '&ajax=true&method=getconvertedlist&page_number=' + page,
        type: 'post',
        datatype: 'json',
        beforeSend: function()
        {
            $('#converted-list-block .abd-bigloader').show();
        },
        success: function(json)
        {
            $('#converted-list-block .abd-bigloader').hide();
            if (json['flag']) {
                var html = '';
                var i = 0;
                var row_class = 'odd';
                for (i = 0; i < json['data'].length; i++) {
                    if (i % 2 == 0)
                        row_class = 'even';
                    html += '<tr class="pure-table-' + row_class + '">';
                    html += '<td class="right">' + (json['start_serial'] + i) + '</td>';
                    html += '<td>' + json['data'][i]['reference'] + '</td>';
                    html += '<td><span class="tbl-cl-main-txt">' + json['data'][i]['firstname'] + '</span><span class="tbl-cl-sub-txt">' + json['data'][i]['email'] + '</span></td>';
                    html += '<td>' + json['data'][i]['status'] + '</td>';
                    html += '<td class="right">' + json['data'][i]['formatted_total'] + '</td>';
                    html += '<td>' + json['data'][i]['date_add'] + '</td>';
                    html += '<td class="list_action_btn center"><a href="' + json['data'][i]['order_url'] + '" target="_blank" class="glyphicons riflescope"><i data-toggle="tooltip"  data-placement="top" data-original-title="Click to view order detail"></i></a></td>';
                    html += '</tr>';
                }
                $('#converted-list_body').html(html);
                $('#converted_list_current_page').attr('value', page);
            } else {
                var html = '<tr class="pure-table-odd empty-tbl"><td colspan="7" class="center"><span>' + empty_list_msg + '</span></td><tr>';
                $('#converted-list_body').html(html);
            }
            $('#converted-list-block .paginator-block').html(json['pagination']);
            $('[data-toggle="tooltip"]').tooltip();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('#converted-list-block .abd-bigloader').hide();
            alert('Technical error occurred. Contact to support.');
        }
    });
}

function getTemplate(id_template_content, modal)
{
    $.ajax({
        url: action,
        data: '&ajax=true&method=gettemplate&id_template_content=' + id_template_content,
        type: 'post',
        datatype: 'json',
        beforeSend: function() {
            $('#' + modal + ' .template_loader').show();
        },
        complete: function() {
            $('#' + modal + ' .template_loader').hide();
        },
        success: function(json) {
            $('#' + modal + ' .template_loader').hide();
            $('#' + modal + ' select option').each(function(){
              if($(this).attr('value') == id_template_content) {
                  $(this).attr('selected', true);
              }
            })   
            $('#' + modal + ' input[name="single_email_subject"]').attr('value', json['subject']);
            $('#' + modal + ' input[name="single_email_body"]').attr('value', json['body']);
            $('#' + modal + ' input[name="cart_template"]').attr('value', json['cart_template']);
            tinyMCE.get(modal + '_body_editor').setContent(json['body']);
            $('#' + modal + ' .row-no-display').show();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('#modal-email-status_reminder').hide();
            alert('Technical error occurred. Contact to support.');
        }
    });
}

function displayReminderModal(id_customer, id_cart, id_abandon, id_lang)
{
    $('.validation-advice').remove();
    $('#reminder_email_modal input[name="email_reminder[id_cart]"]').attr('value', id_cart);
    $('#reminder_email_modal input[name="email_reminder[id_customer]"]').attr('value', id_customer);
    $('#reminder_email_modal input[name="email_reminder[id_abandon]"]').attr('value', id_abandon);
    $('#reminder_email_modal input[name="email_reminder[id_lang]"]').attr('value', id_lang);
    $('#reminder_email_modal select[name="email_reminder[id_template_content]"] option').removeAttr('selected');
    $('#reminder_email_modal .row-no-display').show();
    $('#reminder_email_modal input[type="text"]').attr('value', '');
    tinyMCE.get('reminder_email_modal_body_editor').setContent('');
    $('#reminder_email_modal').modal({'show': true, 'backdrop': 'static'});
    var i=0;
    $('#reminder_email_modal select[name="email_reminder[id_template_content]"] option').each(function() {
        if ($(this).attr('value') != 0 && i==0) {
            getTemplate($(this).attr('value'), 'reminder_email_modal');
            i = 1;
        }
    })
}

function sendReminderMail()
{
    var error = false;
    $('.validation-advice').remove();

    if ($('#reminder_email_modal input[name="single_email_subject"]').val() == '') {
        error = true;
        $('#reminder_email_modal input[name="single_email_subject"]').parent().append('<span class="validation-advice">' + required_email_subject + '</span>');
    }

    var text_data_html = tinyMCE.get('reminder_email_modal_body_editor').getContent('');
    var email_content = $(text_data_html).text();
    if (email_content.trim() == '') {
        error = true;
        $('#mce_110').parent().append('<span class="validation-advice">' + required_email_content + '</span>');
    }

    if (!error)
    {
        tinyMCE.triggerSave();
        $.ajax({
            url: action,
            type: 'post',
            data: $('#reminder_email_modal input, #reminder_email_modal textarea, #reminder_email_modal select').serialize() + '&ajax=true&method=sendreminder',
            datatype: 'json',
            beforeSend: function() {
                $('#modal-reminder-email-status').show();
                $('#reminder_email_modal .modal-action-btn').hide();
            },
            success: function(json) {
                $('#modal-reminder-email-status').hide();
                $('#reminder_email_modal .modal-action-btn').show();
                $('#reminder_email_modal').modal('hide');

                var msg_class = '';
                if (json['status']) {
                    msg_class = 'alert-success';
                    if (json['status'] == -1)
                        msg_class = 'alert-danger';
                    else if (json['status'] == -2)
                        msg_class = 'alert-danger';
                } else {
                    msg_class = 'alert-danger';
                }
                $('#tab_abandon_list_msg_bar').removeClass('alert-success');
                $('#tab_abandon_list_msg_bar').removeClass('alert-danger');
                $('#tab_abandon_list_msg_bar').addClass(msg_class);
                $('#tab_abandon_list_msg_bar').html(json['message']);
                $('#tab_abandon_list_msg_bar').show();
                setTimeout(function() {
                    $('#tab_abandon_list_msg_bar').hide();
                    $('#tab_abandon_list_msg_bar').html('');
                }, msg_timeout);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Technical error occurred. Contact to support.');
                $('#modal-reminder-email-status').hide();
                $('#reminder_email_modal .modal-action-btn').show();
                $('#reminder_email_modal').modal('hide');
            }
        });
    }
}

function displayDisocuntEmailModal(id_customer, id_cart, id_abandon, id_lang)
{
    $('.validation-advice').remove();
    $('#modal_incentive_email input[name="email_discount[id_cart]"]').attr('value', id_cart);
    $('#modal_incentive_email input[name="email_discount[id_customer]"]').attr('value', id_customer);
    $('#modal_incentive_email input[name="email_discount[id_abandon]"]').attr('value', id_abandon);
    $('#modal_incentive_email input[name="email_discount[id_lang]"]').attr('value', id_lang);
    $('#modal_incentive_email .row-no-display').show();
    $('#modal_incentive_email input[type="text"]').attr('value', '');
    tinyMCE.get('modal_incentive_email_body_editor').setContent('');
    $('#modal_incentive_email select option').removeAttr('selected');

    $('#modal_incentive_email').modal({'show': true, 'backdrop': 'static'});
    var i=0;
    $('#modal_incentive_email select[name="email_discount[id_template_content]"] option').each(function() {
        if ($(this).attr('value') != 0 && i==0) {
            getTemplate($(this).attr('value'), 'modal_incentive_email');
            i = 1;
        }
    })
}

function sendDiscountEmail()
{
    var error = false;
    $('#modal_incentive_email .validation-advice').remove();

    if ($('#modal_incentive_email input[name="email_discount[discount_value]"]').val() == '' || $('#modal_incentive_email input[name="email_discount[discount_value]"]').val() == 0) {
        error = true;
        $('#modal_incentive_email input[name="email_discount[discount_value]"]').parent().append('<span class="validation-advice">' + required_discount_value + '</span>');
    } else if (!decimal_reg.test($('#modal_incentive_email input[name="email_discount[discount_value]"]').val())) {
        error = true;
        $('#modal_incentive_email input[name="email_discount[discount_value]"]').parent().append('<span class="validation-advice">' + invalid_flt_msg + '</span>');
    }

//    if ($('#modal_incentive_email input[name="email_discount[min_cart_value]"]').val() == '') {
//        error = true;
//        $('#modal_incentive_email input[name="email_discount[min_cart_value]"]').parent().append('<span class="validation-advice">' + required_min_cart + '</span>');
//    } 
    if ($('#modal_incentive_email input[name="email_discount[min_cart_value]"]').val() != '' && !decimal_reg.test($('#modal_incentive_email input[name="email_discount[min_cart_value]"]').val())) {
        error = true;
        $('#modal_incentive_email input[name="email_discount[min_cart_value]"]').parent().append('<span class="validation-advice">' + invalid_flt_msg + '</span>');
    }

    if ($('#modal_incentive_email input[name="email_discount[coupon_validity]"]').val() == '') {
        error = true;
        $('#modal_incentive_email input[name="email_discount[coupon_validity]"]').parent().parent().append('<span class="validation-advice">' + required_coupon_validity + '</span>');
    } else if (!numeric_reg.test($('input[name="email_discount[coupon_validity]"]').val())) {
        error = true;
        $('#modal_incentive_email input[name="email_discount[coupon_validity]"]').parent().parent().append('<span class="validation-advice">' + invalid_date_msg + '</span>');
    }

    if ($('#modal_incentive_email input[name="single_email_subject"]').val() == '') {
        error = true;
        $('#modal_incentive_email input[name="single_email_subject"]').parent().append('<span class="validation-advice">' + required_email_subject + '</span>');
    }
    
    var text_data_html = tinyMCE.get('modal_incentive_email_body_editor').getContent('');
    var email_content = $(text_data_html).text();
    if (email_content.trim() == '') {
        error = true;
        $('#mce_149').parent().append('<span class="validation-advice">' + required_email_content + '</span>');
    }

    if (!error) {
        tinyMCE.triggerSave();
        $.ajax({
            url: action,
            data: $('#modal_incentive_email input, #modal_incentive_email select, #modal_incentive_email textarea').serialize() + '&ajax=true&method=senddiscountemail',
            type: 'post',
            datatype: 'json',
            beforeSend: function() {
                $('#modal-incentive-email-status').show();
                $('#modal_incentive_email .modal-action-btn').hide();
            },
            success: function(json)
            {
                $('#modal-incentive-email-status').hide();
                $('#modal_incentive_email .modal-action-btn').show();
                $('#modal_incentive_email').modal('hide');

                var msg_class = '';
                if (json['status']) {
                    msg_class = 'alert-success';
                    if (json['status'] == -1)
                        msg_class = 'alert-danger';
                    else if (json['status'] == -2)
                        msg_class = 'alert-danger';
                } else {
                    msg_class = 'alert-danger';
                }
                $('#tab_abandon_list_msg_bar').removeClass('alert-success');
                $('#tab_abandon_list_msg_bar').removeClass('alert-danger');
                $('#tab_abandon_list_msg_bar').addClass(msg_class);
                $('#tab_abandon_list_msg_bar').html(json['message']);
                $('#tab_abandon_list_msg_bar').show();
                setTimeout(function() {
                    $('#tab_abandon_list_msg_bar').hide();
                    $('#tab_abandon_list_msg_bar').html('');
                }, msg_timeout);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Technical error occurred. Contact to support.');
                $('#modal-incentive-email-status').hide();
                $('#modal_incentive_email .modal-action-btn').show();
                $('#modal_incentive_email').modal('hide');
            }
        });
    }
}


function displayCartDetail(id_customer, id_cart)
{
    $('#cart_detail_tbl').hide();
    $('#cart_detail_loader').show();
    $('#cart_detail_modal').modal({'show': true, 'backdrop': 'static'});
    $.ajax({
        type: 'POST',
        url: action,
        data: '&ajax=true&method=getCustomerCartDetail&id_customer=' + id_customer + '&id_cart=' + id_cart,
        dataType: 'json',
        success: function(json) {
            if (json['customer']['fname'] != null && json['customer']['lname'] != null)
                $('#cart_detail_modal .ac_modal_customer_name').html(json['customer']['fname'] + ' ' + json['customer']['lname'] + ' - ');
            var html = '';
            if (json['products'].length > 0) {
                for (var i in json['products']) {
                    html += '<tr>';
                    html += '<td><a  href="' + json['products'][i]['pro_link'] + '" target="_blank"><img src="' + json['products'][i]['img_link'] + '" height="100px" width="100px"/></a></td>';
                    html += '<td style="text-align:left;"><a href="' + json['products'][i]['pro_link'] + '" target="_blank">' + json['products'][i]['name'] + '</a>' + ((json['products'][i]['attributes'] != '' && json['products'][i]['attributes'] != undefined) ? '<br><small>' + json['products'][i]['attributes'] + '</small>' : '') + '</td>';
                    html += '<td>' + ((json['products'][i]['reference'] != '') ? json['products'][i]['reference'] : '') + '</td>';
                    html += '<td>' + json['products'][i]['quantity'] + '</td>';
                    html += '<td>' + json['products'][i]['price_wt'] + '</td>';
                    html += '<td>' + json['products'][i]['total_wt'] + '</td>';
                    html += '</tr>';
                }
                html += '<tr><td class="ac_cart_total_row" colspan="5">' + ac_cart_total_msg + '</td><td class="ac_cart_total_row">' + json['cart_total'] + '</td></tr>'
            } else {
                html += '<tr><td colspan="6">' + ac_no_data_found_txt + '</td></tr>';
            }
            $('#ac_cart_product_row').html(html);
            $('#cart_detail_loader').hide();
            $('#cart_detail_tbl').show();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert('Technical error occurred. Contact to support.');
            removeCartDetailModal();
        }
    });
}

function removeCartDetailModal()
{
    dismiss_ac_modal('cart_detail_modal');
    $('#ac_cart_product_row').html('');
    $('#cart_detail_modal .ac_modal_customer_name').html('');
}

function deleteAbandon(elem)
{
    var id = $(elem).attr("type");
    selected_abd_rem_id = id;
    $('#modal_abandon_remove .modal-action-btn').show();
    $('#abd-rem-confrm-msg').show();
    $('#abandon_remove_processing').hide();
    $('#modal_abandon_remove').modal({'show': true, 'backdrop': 'static'});
}


function delAbandonAction(rem_action)
{
    if (rem_action == 1) {
        $('#modal_abandon_remove .modal-action-btn').hide();
        $('#abd-rem-confrm-msg').hide();
        $('#abandon_remove_processing').show();
        $.ajax({
            type: 'POST',
            url: action,
            data: '&ajax=true&method=deleteabandon&id_abandon=' + selected_abd_rem_id,
            dataType: 'json',
            success: function(json) {
                var msg_class = '';
                if (json['status']) {
                    msg_class = 'alert-success';
                    getAbandonedList($('#abd_list_current_page').attr('value'));
                } else {
                    msg_class = 'alert-danger';
                }
                $('#tab_abandon_list_msg_bar').removeClass('alert-success');
                $('#tab_abandon_list_msg_bar').removeClass('alert-danger');
                $('#tab_abandon_list_msg_bar').addClass(msg_class);
                $('#tab_abandon_list_msg_bar').html(json['message']);
                $('#tab_abandon_list_msg_bar').show();
                setTimeout(function() {
                    $('#tab_abandon_list_msg_bar').hide();
                    $('#tab_abandon_list_msg_bar').html('');
                }, msg_timeout);
                delAbandonAction(0);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('Technical error occurred. Contact to support.');
                delAbandonAction(0);
            }
        });
    } else {
        selected_abd_rem_id = 0;
        $('#modal_abandon_remove .modal-action-btn').show();
        $('#abd-rem-confrm-msg').show();
        $('#abandon_remove_processing').hide();
        $('#modal_abandon_remove').modal('hide');
    }
}

function displayCouponDetail(id_customer, email)
{
    $('#coupon-detail-tbl').hide();
    $('#coupon_detail_loader').show();
    $('#coupon_detail_modal').modal({'show': true, 'backdrop': 'static'});
    $.ajax({
        type: 'POST',
        url: action,
        data: '&ajax=true&method=getCouponDetail&id_customer=' + id_customer + '&email=' + encodeURIComponent(email),
        dataType: 'json',
        success: function(json) {
            if (json.length > 0) {
                var html = '';
                for (var i in json) {
                    html += '<tr>';
                    html += '<td class="ac_white_background">' + (i + 1) + '</td>';
                    html += '<td class="ac_white_background left">' + json[i]['code'] + '</td>';
                    html += '<td class="ac_white_background right">' + ((json[i]['reduction_percent'] == 0) ? json[i]['reduction_format'] : json[i]['reduction_percent'] + '%') + '</td>';
                    html += '<td class="ac_white_background right">' + json[i]['minimum_amount'] + '</td>';
                    html += '<td class="ac_white_background left">' + json[i]['date_from'] + '</td>';
                    html += '<td class="ac_white_background left">' + json[i]['date_to'] + '</td>';
                    html += '</tr>';
                }
                $('#ac_coupon_detail_row').html(html);
                $('#coupon_detail_loader').hide();
                $('#coupon-detail-tbl').show();
            } else {
                var html = '<tr class="pure-table-odd empty-tbl"><td class="center" colspan="6">' + empty_list_msg + '</td></tr>';
                $('#ac_coupon_detail_row').html(html);
                $('#coupon_detail_loader').hide();
                $('#coupon-detail-tbl').show();
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert('Technical error occurred. Contact to support.');
            removeCouponDetailModal();
        }
    });
}

function removeCouponDetailModal()
{
    dismiss_ac_modal('coupon_detail_modal');
    $('#ac_coupon_detail_row').html('');
    $('#coupon_detail_modal .ac_modal_customer_name').html('');
}


function refreshTemplateDropdown()
{
    $.ajax({
        type: 'POST',
        url: action,
        data: '&ajax=true&method=refreshtemplatedropwn',
        dataType: 'json',
        success: function(json) {
            var i = 0;
            var html = '';
            if (json['templates'].length > 0) {
                html += '<option value="0">'+sel_temp_msg+'</option>';
                for (i = 0; i < json['templates'].length; i++) {
                    html += '<option value="' + json['templates'][i]['id_template'] + '" >' + json['templates'][i]['name'] + '</option>';
                }

                $('.dropdn_templates').each(function() {
                    $(this).html(html);
                });
            }

            html = '';
            i = 0;
            if (json['trans_template_discount'].length > 0) {
                html += '<option value="0">Select template</option>';
                for (i = 0; i < json['trans_template_discount'].length; i++) {
                    html += '<option value="' + json['trans_template_discount'][i]['id_template_content'] + '" >' + json['trans_template_discount'][i]['name'] + '(' + json['trans_template_discount'][i]['language_text'] + ')</option>';
                }

                $('.dropdn_templates_translation_dis').each(function() {
                    $(this).html(html);
                });
            }

            html = '';
            i = 0;
            if (json['trans_template_ndiscount'].length > 0) {
                html += '<option value="0">Select template</option>';
                for (i = 0; i < json['trans_template_ndiscount'].length; i++) {
                    html += '<option value="' + json['trans_template_ndiscount'][i]['id_template_content'] + '" >' + json['trans_template_ndiscount'][i]['name'] + '(' + json['trans_template_ndiscount'][i]['language_text'] + ')</option>';
                }

                $('.dropdn_templates_translation_ndis').each(function() {
                    $(this).html(html);
                });
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
        }
    });
}

function updateTemplateName(event, e)
{
    if (event.keyCode == 13) {
        $(e).parent().find('.validation-advice').remove();
        var content = $(e).attr('value');
        var error = false;
        
        if ($(e).attr('value').trim() == '') {
            error = true;
            //$('input[name="email_template[name]"]').parent().append('<span class="validation-advice">' + required_template_name + '</span>');
            $(e).parent().append('<span class="validation-advice">' + required_template_name + '</span>');
        } else if ($(e).attr('value').length > 255) {
            error = true;
            //$('input[name="email_template[name]"]').parent().append('<span class="validation-advice">' + max_255_length + '</span>');
            $(e).parent().append('<span class="validation-advice">' + max_255_length + '</span>');
        }
               
        if (!error) {
            $.ajax({
                type: 'POST',
                url: action,
                data: $(e).parent().find('input').serialize() + '&ajax=true&method=updatetemplatename&id_template=' + $(e).attr('data-id'),
                dataType: 'json',
                success: function(json) {
                    if (!json['status']) {
                        $('#tab_email_msg_bar').removeClass('alert-success');
                        $('#tab_email_msg_bar').removeClass('alert-danger');
                        $('#tab_email_msg_bar').addClass('alert-danger');
                        $('#tab_email_msg_bar').html(json['message']);
                        $('#tab_email_msg_bar').show();
                        setTimeout(function() {
                            $('#tab_email_msg_bar').hide();
                            $('#tab_email_msg_bar').html('');
                        }, msg_timeout);
                    } else {
                        $(e).parent().parent().find('.cge_tmlte_cell').html($(e).attr('value'));
//                        $(e).parent().find('.cge_tmlte_cell').show();
                        $(e).remove();
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert('Technical error occurred. Contact to support.');
                    $(e).parent().find('.cge_tmlte_cell').show();
                    $(e).remove();
                }
            });
        }
    }
}

function restoreTemplateName(e)
{
    $(e).parent().parent().find('.cge_tmlte_cell').html(current_name);
    $(e).remove();
}

function cart_preview(a)
{
    $("#vss_cart_image").attr('src', image_path + "cart_image_" + $(a).val() + ".png")
    $("#vss_cart_image_template").attr('src', image_path + "cart_image_" + $(a).val() + ".png")
}

function isNumberKey(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode

    if (charCode == 46)
    {
        var inputValue = $("#inputfield").val()
        if (inputValue.indexOf('.') < 1)
        {
            return true;
        }
        return false;
    }
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57))
    {
        return false;
    }
    return true;
}

//function displaytestingmodehtml()
//{
//    $(".vss_testing_html").toggle('slow');
//}