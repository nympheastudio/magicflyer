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

var lecab_wizzard = function() {

    var wizzard_tabs = $('#tab-container').children();
    var wizzard_nav = $('#nav-container').children();
    var current_step = 0;

    function insertButtons() {
        wizzard_tabs.find('.panel-footer').addClass("wizzard").children().hide().parent().append(btns);
        // Set buttons actions.
        $('#tab-container').on('click', '.wizzard_btn', function(e) {
            e.preventDefault();
            // Prevent action if button is disabled.
            if ($(this).hasClass("disabled")) { return; }

            if ($(this).hasClass("wizzard_btn_next")) {
                wizzard_ajax();
            } else if ($(this).hasClass("wizzard_btn_prev")) {
                wizzard_prev();
            } else if ($(this).hasClass("wizzard_btn_end")) {
                wizzard_end();
            }
        })

        // Disable original nav-tab navigation
        $('#nav-container li').addClass('disabled');
        $('body').on('click', '#nav-container li, #nav-container a', preventClick);
    };

    function preventClick( event ) {
        event.preventDefault();
        return false;
    };

    // Update Buttons depending on the steps.
    function updateButtons() {
        $(".tab-pane[data-step='0']").find(".wizzard_btn_prev").hide();
        $(".tab-pane[data-step='3']").find(".wizzard_btn_next").hide();
        $(".tab-pane[data-step='3']").find(".wizzard_btn_end").show();
    }

    // Display next step
    function wizzard_next() {
        current_step++;
        // Toggle next tab-pane.
        wizzard_tabs.removeClass("active");
        $('#tab-container').find(".tab-pane[data-step="+current_step+"]").addClass("active");
        // Toggle next nav-tab.
        wizzard_nav.removeClass("active");
        $('#nav-container').find("a[data-step="+current_step+"]").parent().addClass("active");
    }

    // Display previous step
    function wizzard_prev() {
        current_step--;
        // Toggle next tab-pane.
        wizzard_tabs.removeClass("active");
        $('#tab-container').find(".tab-pane[data-step="+current_step+"]").addClass("active");
        // Toggle next nav-tab.
        wizzard_nav.removeClass("active");
        $('#nav-container').find("a[data-step="+current_step+"]").parent().addClass("active");
    }

    function wizzard_end() {
        wizzard_ajax(true);
    }

    // Show error messages in a modal window
    function wizzard_error(errormsg) {
        $('#wizzard_error').find(".modal-body").html(errormsg);
        $('#wizzard_error').modal('show');
    }

    // When wizzard is complete, go to 1st tab
    // and remove the wizzard navigation.
    this.wizzard_terminate = function () {
        // return;
        
        // Remove wizzard buttons
        wizzard_tabs.find('.panel-footer').removeClass("wizzard").find(".wizzard_btn_next, .wizzard_btn_prev, .wizzard_btn_end").remove();
        wizzard_tabs.find('.panel-footer').find(":submit").show();

        // Toggle 1st tab-pane.
        wizzard_tabs.removeClass("active");
        $('#tab-container').find(".tab-pane[data-step='0']").addClass("active");
        // Toggle 1st nav-tab.
        wizzard_nav.removeClass("active");
        $('#nav-container').find("a[data-step='0']").parent().addClass("active");
        // Enable tab navigation
        $('#nav-container li').removeClass('disabled');
        // Remove event handler
        $('body').off('click', '#nav-container li, #nav-container a', preventClick);
    }

    // Ajax handler to submit forms
    function wizzard_ajax(end) {
        var url = lecabflash_ajax_url;
        var current_pannel = $("#tab-container").find(".tab-pane[data-step="+current_step+"]");

        var data = {};
        // Serializes the form's elements.
        $.each(current_pannel.find("form").serializeArray(), function(i,obj) {
            data[obj.name] = obj.value;
        });    
        data['action'] = current_pannel.attr("data-action");

        // console.log(data);

        $.ajax({
            type: "POST",
            url: url,
            data: data,
            success: function(response)
            {   
                // response format : {"success":false,"message":"Addresse non compatible"}
                if(response == "null") {                    // Data is left unchanged.
                    if (!end) {                             // Go to next step.
                        wizzard_next();
                    } else {                                // Wizzard is complete.
                        $('#wizzard_success').modal('show');
                        lecab_wizzard_instance.wizzard_terminate();
                    }
                } else {
                    var response = JSON.parse(response);
                    if(response["success"]) {               // Data has been saved.
                        if (!end) {                         // Go to next step.
                            wizzard_next(); 
                        } else {                            // Wizzard is complete.
                            $('#wizzard_success').modal('show');
                            lecab_wizzard_instance.wizzard_terminate();
                        }
                    } else {                                // Data is invalid.
                        wizzard_error(response["message"]); // Display error message.
                    }
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("Status: " + textStatus); alert("Error: " + errorThrown);
            }
        });
    }

    // Initialize wizzard
    if(!wizzard_isActive) {
        insertButtons();
        updateButtons();
    }
}

var lecab_wizzard_instance;
var selected_item = false;

var autocomplete_ajax_call = null;

var cache_timeout;

$(document).ready(function() {
    lecab_wizzard_instance = new lecab_wizzard();

    // form submit when wizard is off
    $("form").submit(function(e) {
        e.preventDefault();

        var url = lecabflash_ajax_url;
        var current_pannel = $(this).closest(".tab-pane");

        var save_button = current_pannel.find('button[type=submit]');
        // disable the save button during the process, and set loading icon
        save_button.prop('disabled', true);
        save_button.find('i').removeClass('process-icon-save').addClass('process-icon-loading');

        var data = {};
        $.each(current_pannel.find("form").serializeArray(), function(i,obj) {
            data[obj.name] = obj.value;
        });

        data['action'] = current_pannel.attr("data-action");
        
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            success: function(response)
            {
                // response format : {"success":false,"message":"Addresse non compatible"}
                if(response == "null") {                    // Data is left unchanged.
                } else {
                    var response = JSON.parse(response);
                    if(response["success"]) {               // Data has been saved.
                        // console.log("Data has been saved. ", response["message"]);
                    } else {                                // Data is invalid.
                        // Display error message.
                        $('#wizzard_error').find(".modal-body").html(response["message"]);
                        $('#wizzard_error').modal('show');
                    }
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("Status: " + textStatus); alert("Error: " + errorThrown);
            }
        }).done(function() {
            // Enable the save button after the process, and set back save icon.
            save_button.prop('disabled', false);
            save_button.find('i').removeClass('process-icon-loading').addClass('process-icon-save');

            // Display clear cache message,
            $('#lecab__clear-cache').css({
                'opacity': 1,
            });

            // then hide it after 10 seconds.
            (function start_cache_timeout() {
                clearTimeout(cache_timeout);

                cache_timeout = setTimeout(function() {
                    $('#lecab__clear-cache').css({
                        'opacity': 0,
                    });
                }, 10000);
            })();
        });
    });


    // Autocomplete
    $("#lecabflash_pickup_address").attr('autocomplete','off');
    $("#lecabflash_pickup_address").live( "input",function(e) {
        e.preventDefault();
    
        var url = lecabflash_ajax_url;
        var current_pannel = $(this).closest(".tab-pane");

        var data = { };

        data['action'] = "adresse_search";
        data['adresse'] = $("#lecabflash_pickup_address").val();
        data['lecabflash_token'] = $("#lecabflash_token").val();
        data['employee_id'] = $("#employee_id").val();

        // Abort previous ajax requests.
        if (autocomplete_ajax_call != null) {
            autocomplete_ajax_call.abort();
            autocomplete_ajax_call = null;
        }

        autocomplete_ajax_call = $.ajax({
            type: "POST",
            url: url,
            data: data,
            asaync: false,
            success: function(response)
            {
                //console.log(response);
                var res = JSON.parse(response);

                if(response != "null") {
                    $("#lecabflash_pickup_address").removeClass('completed');
                    $("#list_address").remove();
                    var html = "";
                    html += "<ul id='list_address'>";

                    $.each( res.data.locations, function( key, value ){
                        html += "<li value='" + value.address + "'>" + value.address + "</li>";
                    });

                    html += "</ul>";

                    $(html).insertAfter("#lecabflash_pickup_address");

                        // On autocomplete list mouse hover, removes keyboard selection
                        $('#list_address, #list_address li').mouseover(function() {
                            // console.log("mouseenter");
                            selected_item = false;
                            $('#list_address li').removeClass('selected');
                        });

                    // Resets list's selected item.
                    selected_item = false;
                }
            }
        });
    });

    // Set input value equal to clicked list item.
    $("#list_address li").live("click",function() {
        $("#lecabflash_pickup_address").addClass('completed');
        var value = $(this).text();

        $("#lecabflash_pickup_address").val(value);
        $("#list_address").remove();
    });

    // Set input value equal to first li item when user clicks outside list.
    $(document).live("click",function(event) {
        if ($("#list_address").length) {
            var value = $("#list_address").children().first().text();

            $("#list_address").remove();
            $("#lecabflash_pickup_address").val(value);
        }
    });

    // Allow arrow control selection on autocomplete list.
    $("#lecabflash_pickup_address").keydown(function(event) {
        selection_handler(event, event.which);
    });
    $("#lecabflash_pickup_address").keypress(function(event) {
        selection_handler(event, event.which);
    });

    var selection_handler = function(event, keycode) {
        // if autocomplete list is displayed
        if ( $('#list_address').length ) {
            switch (keycode) {
                case 40 : // arrow down
                    event.preventDefault();
                    if (!selected_item) {
                        $('#list_address li').first().addClass('selected');
                        selected_item = 1;
                    } else if ( $('#list_address').children().eq(selected_item).length ) {
                        $('#list_address li').removeClass('selected');
                        $('#list_address').children().eq(selected_item).addClass('selected');
                        selected_item++;
                    }
                    break;

                case 38 : // arrow up
                    event.preventDefault();
                    if (selected_item && $('#list_address').children().eq(selected_item-2).length && selected_item > 1 ) {
                        $('#list_address li').removeClass('selected');
                        $('#list_address').children().eq(selected_item-2).addClass('selected');
                        selected_item--;
                    }
                    break;

                case 13: // enter
                    event.preventDefault();
                    if( selected_item ) {
                        $("#lecabflash_pickup_address").addClass('completed');
                        var value = $('#list_address').children().eq(selected_item-1).text();

                        $("#lecabflash_pickup_address").val(value);
                        $("#list_address").remove();
                    }
                    break;
            }
        }
    };


    // Set the left menu actions.
    $(".lecab__nav li a").click(function() {
        var target = $(this).attr('data-target');

        $(".lecab__nav li a").removeClass("active");
        $(this).addClass("active");

        $(".lecab__right-col").children().hide();
        $("#"+target).show();
    });

    // On load, display rows depending on initial checkboxes values.
    $("#fieldset_hours input:checkbox").each(function() {
        var value = $(this).prop('checked');
        update_row($(this), value);
    });

    // Listen for checkbox changes.
    $("#fieldset_hours input:checkbox").change(function() {
        var value = $(this).prop('checked');
        update_row($(this), value);
    });
});


// HOURS TAB
////////////

// Set a gray color on the row if checkbox is unchecked
// and disable associated inputs.
function update_row(input, value) {
    if (value) {
        input.closest('tr').addClass("active");
        input.closest('tr').find('input:text').removeAttr('disabled');
    } else {
        input.closest('tr').removeClass("active");
        input.closest('tr').find('input:text').attr('disabled', 'disabled');
    }
}


// Prevent text in '.js_numbers_only' input
$(document).on('change', '.js_numbers_only', function(event) {
    this.value = parseInt(this.value) || 0;
});


// Force floats in '.js_floats_only' inputs
$(document).ready(function() {
    $('.js_floats_only').each(function() {
        // Store starting value in data-value attribute.
        $(this).data('value', this.value);
    });
});

$(document).on('keyup', '.js_floats_only', function(event) {
    var val = this.value;
    if ( val == '-' ) {
        // Allow starting with '-' symbol.
        return;
    } else {
        if ( isNaN(val) ) {
            // If value is not a number, abort and put back previous valid value.
            this.value = $(this).data('value');
        } else {
            // Value is valid, store it inside data-value attribute.
            $(this).data('value', val);
        }
    }
});
