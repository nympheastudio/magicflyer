/*
* 2009-2017 202 ecommerce
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*  @author    202 ecommerce <support@202-ecommerce.com>
*  @copyright 2009-2017 202 ecommerce SARL
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

$(document).ready(function() {

    if (!window.lecabflashInitialized) {

        window.lecabflashInitialized = true;
        window.lecabflashMustRefresh = false;
        window.lecabflashHasError = false;

        if (lecabflash_express) {
            getLecabFlashQuotation();
        }

        function getLecabFlashQuotation() {
            $('#lecabflash_rdv_propal').hide();
            $('#lecabflash_rdv_error').hide();
            showLecabFlashPrice('loader');

             $.ajax({
                type: 'GET',
                url: lecabflash_ajax_url,
                dataType: 'json',
                data: {
                    'action': 'getquote',
                    'date': (lecabflash_express === false ? getUserIsoDate() : null),
                    'info': $('#lecabflash_pickup_address_info').val(),
                    'rdv': (lecabflash_express === false ? 'true' : 'false')
                },
            })
            .done(function(data) {
                // console.log('RESPONSE <<',data)
                var code = data['code'];
                var error_code = data['error_code'];
                var error_msg = data['error_msg'];
                if (error_code || error_msg) {
                    window.lecabflashHasError = true;
                    if (error_code == -10) { error_msg = lecabflash_error_api_down }
                    if (error_code == -11) { error_msg = lecabflash_error_no_crs }
                    $('#lecabflash_rdv_error_reason').html(error_msg); 
                    $('#lecabflash_rdv_propal').hide();
                    $('#lecabflash_rdv_error').show();
                    showLecabFlashPrice();
                } else {
                    window.lecabflashHasError = false;
                    window.lecabflashMustRefreshExpress = true;
                    $('#lecabflash_rdv_propal').show();
                    $('#lecabflash_rdv_price').html( data['price'] );

                    var data_iso_date = new Date(data['drop_date']);
                    var dayIndex = data_iso_date.getDay();
                    var monthIndex = data_iso_date.getMonth();

                    var hour_leading_zero = data_iso_date.getHours();
                    if (hour_leading_zero < 10) {
                        hour_leading_zero = '0' + hour_leading_zero;
                    }
                    var min_leading_zero = data_iso_date.getMinutes();
                    if (min_leading_zero < 10) {
                        min_leading_zero = '0' + min_leading_zero;
                    }

                    var formated_drop_date = dayNames[dayIndex] + ", " + data_iso_date.getDate() + " " + monthNames[monthIndex] + " " + data_iso_date.getFullYear() + " à " + hour_leading_zero + "h" + min_leading_zero;
                    
                    $('#lecabflash_rdv_hours').html( formated_drop_date );

                    if (lecabflash_express === true) {
                        $('#lecabflash_express_pickuptime span').html(todayAt + hour_leading_zero + "h" + min_leading_zero);
                        $('#lecabflash_express_pickuptime').show();
                    }

                    // Change button text to "update schedule".
                    $('.lecabflash_open_modal').html(lecabflash_update_schedule);

                    showLecabFlashPrice(data['price']);
                }
            });
            
        }

        function showLecabFlashPrice(price) {
            price = price || null;
            if (lecabflash_price_real==1) {
                var $el = $('.delivery_option_radio[value*="'+lecabflash_carrier_id+'"]').parents('tr').find('.delivery_option_price');
                if (price=='loader') {
                    $el.html('<div id="loading" style="display: block; text-align: center; margin-left: -20px; margin-top: -20px;"><img src="' + lecabflash_spinner_url + '" width="40" height="40" alt="loader"></div>');
                } else if (price) {    
                    $el.html( price+' &euro; TTC');
                } else {
                    $el.html('');
                }   
            }
        }

        function getUserIsoDate() {
            var user_date = $('#datetimepicker1_input').val().split('-');
            user_date = user_date[2] + "-" + user_date[1] + "-" + user_date[0];

            var user_time = $('#datetimepicker2_input').val();

            var user_iso_date = user_date + " " + user_time;
            // console.log(user_iso_date);
            return user_iso_date;
        }

        var $el = $('.delivery_option_radio[value*="'+lecabflash_carrier_id+'"]').parents('tr').find('td:nth-child(3)');
        if ($el && $el.length) {
            $el.append($('#lecabflashCarrierInfo'));
            $('#lecabflashCarrierInfo').show();
            //showLecabFlashPrice($('#lecabflash_express_price').html())
        }

        // If express delivery button is clicked, toggle express pannel
        // and refresh delivery price. 
        $('#lecabflash_switch_express').click(function(event) {
            event.preventDefault();
            if ($(this).hasClass('active')) {
                return;
            } else {
                lecabflash_express = true;
                $(this).parent().find('.active').removeClass('active');
                $(this).addClass('active');
                $('#lecabflash_rdv').hide();
                $('#lecabflash_express').show();
                showLecabFlashPrice('');
                if (window.lecabflashMustRefreshExpress) {
                        getLecabFlashQuotation();
                        $('#lecabflash_express_pickuptime').hide();
                } else {
                    showLecabFlashPrice( lecabflash_price_express );
                }
            }
        });

        // If rdv delivery button is clicked, toggle rdv pannel
        // and refresh delivery price, then open the date and time modal window.
        $('#lecabflash_switch_rdv').click(function(event) {
            event.preventDefault();
            if ($(this).hasClass('active')) {
                return;
            } else {
                lecabflash_express = false;
                $(this).parent().find('.active').removeClass('active');
                $(this).addClass('active');
                $('#lecabflash_express').hide();
                $('#lecabflash_rdv_propal').hide();
                $('#lecabflash_rdv').show();
                showLecabFlashPrice('');
                $('#lecabflash_modal').modal('show');
            }
        });

        // Open modal window on load if lecab carrier is selected
        // and express delivery is unavailable.
        if( $('#lecabflashCarrierInfo').closest('.delivery_option').find('.delivery_option_radio').is( ":checked" ) ) {
            if (lecabflash_express === false) {
                // Induce delay since modal is not ready to be opened right at loading time.
                setTimeout(function() {
                    $('#lecabflash_modal').modal('show');
                }, 2000);
            }
            if ($('#lecabflash_switch_express').hasClass( "active" )) {
                $('#lecabflashCarrierInfo').parent().parent().find('.delivery_option_price').children().show();
            }
        } else {

            $('#lecabflashCarrierInfo').parent().parent().find('.delivery_option_price').children().hide();

            if ($('#lecabflash_switch_express').hasClass( "active" )) {
                $('#lecabflashCarrierInfo').parent().parent().find('.delivery_option_price').children().show();
            }
        }

        // Open modal window each time lecabflash carrier is selected
        // and express delivery is unavailable.
        $('#lecabflashCarrierInfo').closest('.delivery_option').find('.delivery_option_radio').change(function() {
            if ($(this).is( ":checked" ) && lecabflash_express === false) {
                $('#lecabflash_modal').modal('show');
            }
            if ($('#lecabflash_switch_express').hasClass( "active" )) {
                $('#lecabflashCarrierInfo').parent().parent().find('.delivery_option_price').children().show();
            }
        });

        // Auto select lecabflash carrier if user clicks inside the lecabflash pannel.
        $('#lecabflashCarrierInfo').closest('.delivery_option').click(function(event) {
            if ($(event.target).hasClass('delivery_option_radio')) {
                return;
            } else if (!$(this).find('.delivery_option_radio').parent().hasClass('checked')) {
                $(this).find('.delivery_option_radio').prop("checked", true);
                $(this).find('.delivery_option_radio').click();
            }
        });

        // Duplicate textarea info content on both panels.
        $(".lecabflash_textarea").keyup(function() {
            $(".lecabflash_textarea").val( this.value );
        });

        // Open date and time modal window when hour button is clicked.
        $('.lecabflash_open_modal').click(function(event) {
            event.preventDefault();
            $('#lecabflash_modal').modal('show');
        });

        // When user clicks the modal's Validate button, close modal
        // and displays selected hours, then refresh delivery price.
        $('#lecabflash_validate_pickup').click(function(event){
            event.preventDefault();

            $('#lecabflash_modal').modal('hide');
        });

        // When user close the modal
        $('#lecabflash_modal').on('hidden.bs.modal', function (e) {
            getLecabFlashQuotation();
        });

        // If address is invalid, refresh page and define proper address
        // when user clicks on an address button.
        $("#list_new_address li").live("click",function() {
            var new_address = $(this).text();
            var data = { };

            data['action'] = "adresse_confirm";
            data['adresse'] = new_address;
            data['id_cart'] = $(this).attr('name');

            $.ajax({
                type: "POST",
                url: lecabflash_ajax_url,
                data: data,
                beforeSend: function() {
                    var docHeight = $(document).height();
                    $("body")
                        .addClass('modal-open')
                        .append('<div id="lecabflash_overlay"><div id="lecabflash_loading" style="position: absolute; top: 50%; left: 50%; display: block; text-align: center; margin-top: -20px; margin-left: -20px;"><img src="' + lecabflash_spinner_url + '" width="40" height="40" alt="loader"></div></div>');
                    $("#lecabflash_overlay")
                        .height($(window).height())
                        .css({
                            'opacity' : 0.6,
                            'position': 'fixed',
                            'top': 0,
                            'right': 0,
                            'bottom': 0,
                            'left': 0,
                            'background-color': 'black',
                            'width': '100%',
                            'z-index': 9999
                        }); 
                },
                success: function(response) {
                   window.location.replace(window.location.href+"&step=2");
                }
            });

        });

        $("form").submit(function (e) {
            if( $('#lecabflashCarrierInfo').closest('.delivery_option').find('.delivery_option_radio').is( ":checked" ) ) {
                
                getLecabFlashQuotation();

                if (window.lecabflashHasError) {
                    e.preventDefault();
                    var msg_error = $('#lecabflash_rdv_error_reason').text();
                    if (!!$.prototype.fancybox)
                        $.fancybox.open([
                        {
                            type: 'inline',
                            autoScale: true,
                            minHeight: 30,
                            content: '<p class="fancybox-error">' + msg_error + '</p>'
                        }],
                        {
                            padding: 0
                        });
                    else
                        alert(msg_error);
                }
            }
        });
    }
    //mypreload(["'" + lecabflash_spinner_url + "'"]);
});

function mypreload(arrayOfImages) {
    $(arrayOfImages).each(function(){
        $('<img/>')[0].src = this;
    });
}