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
var pageProfiles2 = false;

$(document).ready(function () {
    if (pageProfiles2) return;
    pageProfiles2 = true;

    var start_time = [];
    var context = $('#content');

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

    logtime('profiles.js overall', false);

    $('#menudiv-profiles', context).delegate('input.profile-name', 'keypress', function () {
        var regex = new RegExp("[\+\&]");
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (regex.test(key)) {
            event.preventDefault();
            return false;
        }
    });

    $('#menudiv-profiles label.tip[title], #menudiv-profiles label.tip2[title]', context).each(function () {
        $(this).qtip();
    });

    var sessionId = new Date().valueOf();
    var uniqueId = parseInt(sessionId) + 1;

    var chosen_params = {
        'width': '400px',
        'search_contains': true,
        'placeholder_text_single': $('#text-add-select-option', context).val(),
        'no_results_text': $('#text-add-select-no-result', context).val()
    };

    $('.profile-stored select.profile-universe, .profile-stored select.product_type', $('#menudiv-profiles', context)).chosen(chosen_params);

    /*
     *  PROFILES MANAGEMENT
     */

    /**
     * Remove event for all profiles, even late-created one.
     */
    $(document).on('click', '#profile-container .profile-del', function() {
        $(this).parents('.profile').first().slideUp('slow', function () {
            $(this).remove();
        });
    });

    function profileAdd(cloned) {

        if (window.console)
            console.log('Cloned Profile', $(cloned));

        cloned.find('.profile-universe', context).change(function () {
            selectUniverse(cloned);
        });
        // price rules
        cloned.find('.price-rule-type', context).change(function () {
            var type = $(this).val();
            if (type !== 'percent' && type !== 'value')
                return (false);
            $(this).parent().find('select[rel="percent"], select[rel="value"]').hide();
            $(this).parent().find('select[rel="' + type + '"]').show();
        });
        cloned.find('.price-rule-add', context).click(function () {
            var source_i = $(this).parent();
            var dest_i = $(this).parent().parent();
            var is_first_rule = $(dest_i).find('.price-rule').length;

            var from_val = Number(dest_i.find('input[rel=from]:last').val());
            var to_val = Number(dest_i.find('input[rel=to]:last').val());

            dest_i.find('input[rel=from]:last,input[rel=to]:last', context).removeClass('required');

            if (!dest_i.find('input[rel=from]:last', context).val().length || (is_first_rule !== 1 && !parseInt(from_val)))
                from_val = null;

            if (!dest_i.find('input[rel=to]:last', context).val().length || !parseInt(to_val))
                to_val = null;

            if (parseInt(from_val) && parseInt(to_val) && from_val > to_val && from_val >= (parseInt(source_i.find('input[rel=from]').val()) + 1)) {
                dest_i.find('input[rel=to]:last', context).val('');
                to_val = null;
            }
            else if (parseInt(from_val) && parseInt(to_val) && from_val > to_val) {
                dest_i.find('input[rel=from]:last', context).val('');
                from_val = null;
            }

            if (!to_val || (is_first_rule !== 1 && !from_val)) {
                if (from_val === null)
                    dest_i.find('input[rel=from]:last', context).addClass('required');
                if (to_val === null)
                    dest_i.find('input[rel=to]:last', context).addClass('required');
                return (false);
            }
            var cloned2 = source_i.clone().appendTo(dest_i);
            cloned2.find('input', context).val('');
            cloned2.find('input[rel=from]', context).val(parseInt(to_val + 1));

            cloned2.find('.price-rule-add, .price-rule-remove', context).toggle();
            cloned2.find('.price-rule-remove', context).
            click(function () {
                $(this, context).parent().remove();
            });
            if ($('#menudiv-profiles .optional-fields') && $('#menudiv-profiles .optional-fields').length)
                $('#menudiv-profiles .optional-fields').draggable();
        });
        cloned.find('.price-rule-remove', context).click(function () {
            $(this, context).parent().remove();
        });

        applyTagify($('.browsenode', cloned));

        addTip(cloned);

        $('select.profile-universe', cloned).chosen(chosen_params);
        
        return(cloned);
    }

    $('#profile-add', context).click(function () {
        if (window.console)
            console.log('Add Profile', $(this));

        var source = $('#master-profile', context).clone().removeAttr('id').removeClass('master-profile').addClass('profile').prependTo('#profile-container').slideDown('slow');
        profileAdd(source);
    });


    $('.profile-dup-img', $('#menudiv-profiles', context)).click(function () {
        var profile_id = $(this).attr('rel');
        var profile = $('#menudiv-profiles .profile[rel=' + profile_id + ']', context);
        consoleLog(profile);
        $(profile).slideUp();

        var last_profile_id = Number($('#profile-items .profile:last').attr('rel'));
        var new_profile_id = last_profile_id + 1;


        var cloned_header = $('.profile-copy', context).clone();
        var cloned = $('#profile-items .profile[rel='+profile_id+']').clone().removeAttr('id')
            .removeClass('stored-profile').attr('rel', new_profile_id).addClass('profile')
            .prependTo('#profile-container');

        // Remove generated-tagify inputs before regenerate them
        cloned.find('.tagify-container').remove();
        var new_profile = profileAdd(cloned);

        $('input[name^="profiles[name]"]', cloned).remove();
        $(new_profile).slideDown('slow');
        cloned_header.prependTo(cloned).show();

        var new_name = $('input[name^="profiles[name]"]', profile).val()+$('.profile-name', cloned_header).attr('rel');

        // Bug with chosen
        $('.universe-section .chosen-container:eq(1)', $(new_profile)).remove();

        consoleLog(new_profile);
        updateProfile(new_profile, new_name);
    });



    $('#menudiv-profiles', context).delegate('.profile-copy input.profile-name', 'change', function (ev) {

        profile_name = $(this).val();

        target_profile = $(this).parents().get(3);
        new_name = $(this).val();

        updateProfile(target_profile, new_name);
    });
    function updateProfile(target_profile, new_name)
    {
        $('.profile-name', $(target_profile)).val(new_name);

        var loader = $('.xsd-load', $(target_profile));
        $.ajax({
            type: 'POST',
            url: $('#xsd_operations_url').val() + '?callback=?',
            dataType: 'json',
            data: 'action=getkey&profile_name=' + encodeURIComponent(new_name) + '&rand=' + new Date().valueOf(),
            beforeProcess: function (data) {

            },
            success: function (result) {
                if (window.console)
                    console.log(result);

                //str.replace(/\[extra\]\[[^\]]*\]/, '[extra][newkey]');
                loader.hide();
                if (result.errors) {
                    $('#ajax-error').html($('#xsd_ajax_error').val() + '<br />' + result.errors).show();
                    if (result.status && result.status.length)
                        $('#ajax-error').append('<pre>Status:' + result.status + '</pre>');
                    if (result.statusText && result.statusText.length)
                        $('#ajax-error').append('<pre>Status:' + result.statusText + '</pre>');
                    if (result.responseText && result.responseText.length)
                        $('#ajax-error').append('<pre>' + result.responseText + '</pre>');
                } else {
                    $.each($('input[name^="profiles[extra]"], select[name^="profiles[extra]"]', $(target_profile)), function (index, target_item) {
                        $(target_item).attr('name', $(target_item).attr('name').replace(/\[extra\]\[[^\]]*\]/, '[extra]['+result.new_key+']'));
                    });
                    $('input[name=profile_key]', $(target_profile)).val(result.new_key);
                }


            },
            error: function (result) {
                if (window.console)
                    console.log(result);
                loader.hide();
                $('#ajax-error').html($('#xsd_ajax_error').val()).show();
                if (result.status && result.status.length)
                    $('#ajax-error').append('<pre>Status:' + result.status + '</pre>');
                if (result.statusText && result.statusText.length)
                    $('#ajax-error').append('<pre>Status:' + result.statusText + '</pre>');
                if (result.responseText && result.responseText.length)
                    $('#ajax-error').append('<pre>' + result.responseText + '</pre>');
            }
        });

    }


    function addTip(obj)
    {
        logtime('Loading profiles tips', false);
        $('label[rel]', obj).each(function () {

            var target_glossary_key = $(this).attr('rel');
            var target_glossary_div = $('#glossary div.glossary[rel="' + target_glossary_key + '"]');

            if (target_glossary_div && target_glossary_div.length) {
                if ($('span', this) && $('span', this))
                    var title = $('span', this).text();
                else
                    var title = null;

                $(this).qtip({

                    content: {
                        text: target_glossary_div.html(),
                        title: title
                    },
                    hide: {
                        fixed: true,
                        delay: 300
                    },
                    plugins: {}
                });
                $(this).addClass('tip');
            }
        });
        logtime('Loading profiles tips', true);
    }

    $('.profile-universe', $('#menudiv-profiles', context)).change(function (ev) {
        ev.preventDefault();
        if (window.console)
            console.log($(this));
        selectUniverse($(this).parents().get(3));
    });


    $('#menudiv-profiles', context).delegate('.type_reload', 'click', function () {
        var source_select = $('select.profile-universe', $(this).parents().get(2));
        if (window.console)
            console.log(source_select);
        selectUniverse($(source_select).parents().get(3));
    });

    $('.profile-del-img', $('#menudiv-profiles', context)).click(function () {
        var profile_id = $(this).attr('rel');
        $('#menudiv-profiles .profile[rel=' + profile_id + ']', context).slideUp('slow', function () {
            $(this).remove();
        });
        $('#profile-header-' + profile_id, context).slideUp('slow', function () {
            $(this).remove();
        });
    });

    $('.profile-edit-img', $('#menudiv-profiles', context)).click(function () {
        var profile_id = $(this).attr('rel'),
            $optional_field = $('#menudiv-profiles .optional-fields');
        if ($optional_field && $optional_field.length) {
            $optional_field.draggable();
        }
        $('#menudiv-profiles .profile[rel=' + profile_id + ']', context).slideToggle();
    });

    // Fetch XSD
    //
    function selectUniverse(obj) {
        var source_div = $(obj);
        var source_tab = $(obj).find('.amazon-tab:visible');


        if (window.console)
            console.log('Source object', obj);

        var profile = $('input[name^="profiles[name]"]', source_div).val();
        var result = $('.fetch-result', source_tab);
        var selected = $('.profile-universe', $('.amazon-tab:visible', source_div)).val();
        var product_type = $('.product_type', source_tab);
        var mandatory_fields = $('.mandatory_fields', source_tab);

        if (window.console)
            console.log(source_div, source_tab, selected);

        if (!$(source_div).find('.profile-stored').length && (!profile || !profile.length)) {
            alert($('#error_profile_name').val());
            $('select.profile-universe', source_div).val(null);
            return (false);
        }
        $('#ajax-error').html('').hide();

        var loader = $('.xsd-load', $(source_div));

        loader.show();
        mandatory_fields.parent().hide();

        var lang = $('.amazon-tab-selector.active', source_div).attr('rel');
        var id_lang = $('.amazon-tab-selector.active input[rel=id_lang]', source_div).val();

        $.ajax({
            type: 'POST',
            url: $('#xsd_operations_url').val() + '?callback=?',
            data: 'action=fetch&id_lang=' + id_lang + '&lang=' + lang + '&selected=' + selected + '&rand=' + new Date().valueOf() + '&callback=?',
            success: function (result) {
                loader.hide();
                mandatory_fields.html('');
                if (window.console) {
                    console.log(product_type, result);
                }
                product_type.html(result);
                product_type.unbind('change').change(function (obj) {
                    changeProductType(source_div, source_tab, product_type, selected);
                });
                $('select.product_type', source_div).trigger('chosen:updated');
                $('select.product_type', source_div).chosen(chosen_params);
            },
            error: function (result) {
                loader.hide();
                if (window.console)
                    console.log(result);
                $('#ajax-error').html($('#xsd_ajax_error').val()).show();
                if (result.status && result.status.length)
                    $('#ajax-error').append('<pre>Status:' + result.status + '</pre>');
                if (result.statusText && result.statusText.length)
                    $('#ajax-error').append('<pre>Status:' + result.statusText + '</pre>');
                if (result.responseText && result.responseText.length)
                    $('#ajax-error').append('<pre>' + result.responseText + '</pre>');
            }
        });
        return (false);
    }

    function changeProductType(source_div, source_tab, product_type, selected) {

        var profile = $('input[name^="profiles[name]"]', $(source_div)).val();


        var mandatory_fields = $('.mandatory_fields', $(source_tab, context));

        if ($('.optional-container:visible') && $('.optional-container:visible').length)
            $('.optional-container:visible').slideUp().html('');
        if (window.console)
            console.log(source_div, source_tab);

        var loader = $('.xsd-load', $(source_div, context));

        loader.show();
        mandatory_fields.parent().hide();

        var lang = $('.amazon-tab-selector.active', source_div).attr('rel');
        var id_lang = $('.amazon-tab-selector.active input[rel=id_lang]', source_div).val();

        $('#ajax-error').html('').hide();

        $.ajax({
            type: 'POST',
            url: $('#xsd_operations_url').val() + '?callback=?',
            dataType: 'json',
            data: 'action=extrafields&id_lang=' + id_lang + '&lang=' + lang + '&profile=' + encodeURIComponent(profile) + '&selected=' + $(product_type).val() + '&universe=' + selected + '&rand=' + new Date().valueOf(),
            beforeProcess: function (data) {
                $(mandatory_fields).html('');
            },
            success: function (result) {
                if (window.console)
                    console.log(result);
                loader.hide();
                if (result.errors) {
                    $('#ajax-error').html($('#xsd_ajax_error').val() + '<br />' + result.errors).show();
                    if (result.status && result.status.length)
                        $('#ajax-error').append('<pre>Status:' + result.status + '</pre>');
                    if (result.statusText && result.statusText.length)
                        $('#ajax-error').append('<pre>Status:' + result.statusText + '</pre>');
                    if (result.responseText && result.responseText.length)
                        $('#ajax-error').append('<pre>' + result.responseText + '</pre>');
                }

                mandatory_fields.html(result.fields).parent().show().parent().show();
                addTip(mandatory_fields);
                $('.tip', mandatory_fields).not('label[rel]').qtip();
                $('.tip2', mandatory_fields).not('label[rel]').qtip();

                onChangeSortVariant(mandatory_fields);
                onChangeMultipleSelect(mandatory_fields);
                checkMaxInputVarsAgain();
            },
            error: function (result) {
                if (window.console)
                    console.log(result);
                loader.hide();
                $('#ajax-error').html($('#xsd_ajax_error').val()).show();
                if (result.status && result.status.length)
                    $('#ajax-error').append('<pre>Status:' + result.status + '</pre>');
                if (result.statusText && result.statusText.length)
                    $('#ajax-error').append('<pre>Status:' + result.statusText + '</pre>');
                if (result.responseText && result.responseText.length)
                    $('#ajax-error').append('<pre>' + result.responseText + '</pre>');
            }
        });
        return (true);
    }

    function onChangeSortVariant() {
        $('select.variation, select.variation-selected').change(function () {
            sortVariant($(this));
        });
    }

    $('select.variation, select.variation-selected').change(function () {
        sortVariant($(this));
    });

    function checkMaxInputVarsAgain()
    {
        logtime('checkMaxInputVarsAgain', false);
        var max_input_vars = parseInt($('#max_input_vars').val());
        var cur_input_vars = $('input, select, textarea, button').length;

        if (max_input_vars && max_input_vars < cur_input_vars) {
            $('#error-max_input_vars').show();
            $('#amz-env-infos').show();
            $('#error-max_input_vars').clone().prependTo($($('#profile-add').parents().get(1)).after());
        }
        logtime('checkMaxInputVarsAgain', true);
    }


    function sortVariant(obj) {
        if (!obj.val() || !obj.val().length)
            return (false);

        var variant_item_list = obj.find('option:selected').attr('rel');
        var variant_items = variant_item_list.split(',');
        var source_div = obj.parents().get(1);

        var variant_container = $(source_div, context).find('.variant-items-container');

        $(source_div, context).find('.variant-items-container div.specific-field-data select').removeClass('variation-mandatory');
        $(source_div, context).find('.variant-items-container div.specific-field-data').appendTo($(source_div).find('.variant-items-container-restore').get(0));

        if (variant_items.length) {
            $.each(variant_items, function (index, value) {
                $(source_div, context).find('div.variation[rel="' + value + '"]').appendTo(variant_container.get(0));
                $(source_div, context).find('div.variation[rel="' + value + '"]').find('select').addClass('variation-mandatory');
            });
        }

    }

    $('.profile-attribute', $('#menudiv-profiles', context)).change(function (ev) {
        return (changeMultipleSelect(this));
    });
    function onChangeMultipleSelect(fields_div) {
        $(fields_div).find('.profile-attribute').each(function (ind, val) {
            $(this).change(function (ev) {
                return (changeMultipleSelect(this));
            });
        });
    }

    function changeMultipleSelect(selector) {
	
	if($(selector).attr('rel') != 'default') {
	    if ($(selector).val() == '^d') {
            $(selector).parent().find('input').hide();
            $(selector).parent().find('input[rel="default"], select[rel="default"]').show();
	    } else if ($(selector).val() == '^v') {
            $(selector).parent().find('select.extra-option').show();
        } else {
		    $(selector).parent().find('input[rel="default"], select[rel="default"], select.extra-option').hide();
	    }
	}

        if ($(selector).attr('rel') && $(selector).attr('rel').length) {
            target_div = $(selector).parents().get(2);

            if ($(selector).val() && $(selector).val().length)
                $(target_div).find('.profile-amazon-unit[rel=' + $(selector).attr('rel') + ']').show().find('select').show();
            else
                $(target_div).find('.profile-amazon-unit[rel=' + $(selector).attr('rel') + ']').hide().find('select').show();
        }
    }

    $('#menudiv-profiles', context).delegate('.profile .bullet-point-edit-img', 'click', function (ev) {
        var profile = $(this).parents().get(3);
        var id_bullet_point = $(this).parent().attr('rel');

        BulletPointGenerator(profile, id_bullet_point);
    });

    $('#menudiv-profiles', context).delegate('.profile .bullet-point-item span a', 'click', function (ev) {
        ev.preventDefault();
        var target_item = $($(this).parents().get(2));

        if (window.console)
            console.log('Delete Bulletpoint', target_item);

        $('.bullet-point-item span', target_item).hide();
        $('input', target_item).val('');

    });


    $('#menudiv-profiles', context).delegate('.profile .delete-optionnals', 'click', function (ev) {
        var target_item = $($(this).parents().get(2));

        console.log(target_item);

        $('.specific-field-data.optionnal', target_item).each(function (i, item) {
            if (!$('select.profile-attribute :selected', item).val().length) {
                rel_attr = $(this).attr('rel');
                $(this).slideUp().remove(); // remove specific field section for this item
                if (rel_attr && rel_attr.length) // remove associated amazon attribute
                    $('.profile-amazon-unit[rel="' + rel_attr + '"]', $(this).parents().get(0)).remove();
            }
        });
    });

    /*
    function bulletPointGenerator(profile, id_bullet_point) {
        if (window.console)
            console.log('bulletPointGenerator', profile, id_bullet_point);

        var uniqueBulletPointId = 'bp-tmp-' + uniqueId++;
        var target_bullet_point = $('.bullet-point-container[rel=' + id_bullet_point + ']', profile);
        var bullet_point_data = $('input', target_bullet_point).val();

        target_bullet_point.attr('id', uniqueBulletPointId);

        var pAjax = new Object();

        pAjax.url = $('#amazon_tools_url').val() + '&id_lang=' + $('#id_lang').val() + '&action=bullet-point-generator&id=' + uniqueBulletPointId;
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = 'data=' + bullet_point_data;

        if (window.console)
            console.log(pAjax);

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data,
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (data.html) {
                    $('#amazon-bulletpoint-box, #amazon-bulletpoint-overlay').remove();
                    $('<div id="amazon-bulletpoint-overlay">').appendTo('body');
                    $('<div id="amazon-bulletpoint-box">').appendTo('body');
                    $('#amazon-bulletpoint-box').html(data.html);
                    $('.bulletpoint-box').fadeIn();
                }
            },
            error: function (data) {
                console.log('error', data);
            }
        });
    }
    */

    $('#menudiv-profiles', context).delegate('.country-selector td', 'click', function () {

    //$('#menudiv-profiles').delegate('.amazon-tab-selector', 'click', function () {
        var target_profile = $($(this).parents().get(4));
        var target_selector = $('span.amazon-tab-selector', this);

        if (window.console) console.log(target_profile, this);

        if (!target_selector.hasClass('active')) {
            var iso_code = target_selector.attr('rel');

            $('.amazon-tab-selector', target_profile).removeClass('active');
            target_selector.addClass('active');
            $('.amazon-tab', target_profile).hide();
            $('.amazon-tab[rel="' + iso_code + '"]', target_profile).show();
        }
    });

    applyTagify($('#menudiv-profiles #profile-items .profile input.browsenode'));

    // Fetch XSD
    //
    function loadSpecificFields() {
        var target_field = $('input[rel=has_data][value="0"]', '#menudiv-profiles .specific-fields .mandatory_fields').first();

        if (window.console)
            console.log(target_field);

        if (!$(target_field).length)
            return;

        var target_div = $(target_field).parent().parent();
        var profile = $($(target_div).parents().get(1));
        var subprofile = $($(target_div).parents().get(0));

        if (window.console)
            console.log(target_div);


        logtime('Loading profile datas for ' + profile, false);

        var loader = $('.xsd-load', profile);

        if (!$('.mandatory_fields input[rel=has_data][value=0]', subprofile).length)
            return;

        var mandatory_fields = $('.mandatory_fields', subprofile);
        var profile_id_lang = $('.mandatory_fields input[rel=profile_id_lang]', subprofile).val();
        var profile_key = $('.mandatory_fields input[rel=profile_key]', subprofile).val();

        var lang = $('.amazon-tab-selector.active', profile).attr('rel');

        mandatory_fields.parent().hide();
        loader.show();

        if ($('select.product_type', profile).length && $('select.product_type', profile).val().length) {
            $('#menu-profiles .profiles-loader').show();

            $.ajax({
                type: 'POST',
                url: $('#xsd_operations_url').val() + '?callback=?',
                data: 'action=load&id_lang=' + profile_id_lang + '&lang=' + lang + '&profile_key=' + profile_key + '&rand=' + new Date().valueOf() + '&callback=?',
                success: function (result) {
                    loader.hide();

                    if (typeof(result) == 'object' && result.error) {
                        mandatory_fields.html(null).parent().hide();
                    }
                    else if (result && result.length) {
                        mandatory_fields.html(result).parent().show();

                        addTip(mandatory_fields);
                        $('.tip', mandatory_fields).not('label[rel]').qtip();
                        $('.tip2', mandatory_fields).not('label[rel]').qtip();

                        onChangeSortVariant(mandatory_fields);
                        onChangeMultipleSelect(mandatory_fields);
                    }
                    $(target_field).remove();

                    if (window.console)
                        console.log('loadSpecificFields: Recurse', profile, subprofile);

                    $('#menu-profiles .profiles-loader', context).hide();

                    logtime('Loading profile datas for ' + profile, true);

                    loadSpecificFields();//recurse
                },
                error: function (result) {
                    loader.hide();

                    $('#menu-profiles .profiles-loader', context).hide();

                    if (window.console)
                        console.log(result);
                    $('#ajax-error', context).html($('#xsd_ajax_error').val()).show();
                    if (result.status && result.status.length)
                        $('#ajax-error', context).append('<pre>Status:' + result.status + '</pre>');
                    if (result.statusText && result.statusText.length)
                        $('#ajax-error', context).append('<pre>Status:' + result.statusText + '</pre>');
                    if (result.responseText && result.responseText.length)
                        $('#ajax-error', context).append('<pre>' + result.responseText + '</pre>');
                }
            });
        }
        else {
            mandatory_fields.html(null).parent().hide();
            $(target_field).remove();
            loadSpecificFields();//recurse
        }
    }

    loadSpecificFields();


    // Fetch XSD
    //
    function loadSpecificOptionalFields(source_div) {
        var profile = $('input[name^="profiles[name]"]', $(source_div)).val();
        var source_tab = $(source_div).find('.amazon-tab:visible');
        var mandatory_fields = $('.mandatory_fields', $(source_tab, context));
        var id_lang = $('.amazon-tab-selector.active input[rel=id_lang]', source_div).val();
        var lang = $('.amazon-tab-selector.active', source_div).attr('rel');
        var universe = $('.profile-universe', source_tab).val();
        var product_type = $('.product_type', source_tab).val();
        var profile_id = $(source_div).attr('rel');

        var params = {
            'action':'optionals',
            'id_lang':id_lang,
            'lang':lang,
            'profile':profile,
            'universe':universe,
            'selected':product_type,
            'rand': new Date().valueOf()
        };
        if (window.console) console.log(params);

        var loader = $('.xsd-load', source_div);

        console.log()
        loader.show();

        $.ajax({
            type: 'POST',
            url: $('#xsd_operations_url').val() + '?callback=?',
            dataType: 'jsonp',
            data: params,
            success: function (result) {
                loader.hide();
                if (window.console) console.log(result);
                $('.optional-fields .optional-container', $(source_tab)).slideUp().html('');
                if (typeof(result) == 'object') {
                    var fields = jQuery.parseJSON( result.fields );
                    if (typeof(result) == 'object') {

                        $.each(fields.specific_fields, function(field, items) {
                            if (window.console) console.log(field, items, typeof(items));
                            if (!$('input[rel="'+field+'"],select[rel="'+field+'"]', source_tab).length && typeof(items) == 'object' && items != null) {
                                $('.optional-fields .optional-container', $(source_tab)).append(items.html);
                            }
                        });
                        $('#menudiv-profiles .optional-fields').draggable();
                        $('.optional-fields .optional-container', $(source_tab)).slideDown();
                    } else {
                        alert('No field to display');
                    }

                } else {
                    $('#ajax-error').html($('#xsd_ajax_error').val()).show();
                }
            },
            error: function (result) {
                loader.hide();

                $('#menu-profiles .profiles-loader', context).hide();

                if (window.console)
                    console.log(result);
                $('#ajax-error', context).html($('#xsd_ajax_error').val()).show();
                if (result.status && result.status.length)
                    $('#ajax-error', context).append('<pre>Status:' + result.status + '</pre>');
                if (result.statusText && result.statusText.length)
                    $('#ajax-error', context).append('<pre>Status:' + result.statusText + '</pre>');
                if (result.responseText && result.responseText.length)
                    $('#ajax-error', context).append('<pre>' + result.responseText + '</pre>');
            }
        });
    }

    $('#menudiv-profiles', context).delegate('.load-optionals', 'click', function () {
        var source_div = $(this).parents().get(4);

        if (window.console)
            console.log(source_div);
        loadSpecificOptionalFields(source_div);
    });

    $('#menudiv-profiles', context).delegate('.clear-optionals', 'click', function () {
        var source_div = $(this).parents().get(4);
        $('.optional-fields .optional-container', $(source_div)).slideUp().html('');
        if (window.console)
            console.log(source_div);
        $('')
    });

    $('#menudiv-profiles', context).delegate('.add-optional', 'click', function () {
        var source_div = $(this).parents().get(5);
        var target_attribute = $('input.field-name', $(this).parent()).val();
        if (window.console) {
            console.log(source_div, target_attribute)
        }
        addOptionalField(source_div, target_attribute);
    });
    function addOptionalField(source_div, attribute_name) {
        var profile = $('input[name^="profiles[name]"]', $(source_div)).val();
        var source_tab = $(source_div).find('.amazon-tab:visible');
        var mandatory_fields = $('.mandatory_fields', $(source_tab, context));
        var id_lang = $('.amazon-tab-selector.active input[rel=id_lang]', source_div).val();
        var lang = $('.amazon-tab-selector.active', source_div).attr('rel');
        var universe = $('.profile-universe', source_tab).val();
        var product_type = $('.product_type', source_tab).val();
        var profile_id = $(source_div).attr('rel');

        var subprofile = source_tab;

        logtime('Loading profile datas for ' + profile, false);

        var loader = $('.xsd-load', profile);

        var mandatory_fields = $('.mandatory_fields', subprofile);
        var optional_fields = $('.mandatory_fields', subprofile);
        var profile_key = $('.mandatory_fields input[rel=profile_key]', subprofile).val();

        loader.show();

        $.ajax({
            type: 'POST',
            url: $('#xsd_operations_url').val() + '?callback=?',
            data:
                {
                    'action':'optionals',
                    'id_lang':id_lang,
                    'lang':lang,
                    'profile_key':profile_key,
                    'attribute':attribute_name,
                    'universe': universe,
                    'profile': encodeURIComponent(profile),
                    'product_type': product_type,
                    'rand':new Date().valueOf(),
                },
            dataType:'jsonp',
            success: function (data) {
                loader.hide();
                if (window.console) console.log(data);

                if (typeof(data) == 'object') {
                    $('.optional-field-selector[rel="'+attribute_name+'"').slideUp();

                    var target_field = $('.specific-field-tail', subprofile);
                    var target_attribute = $('.specific-field-attributes', subprofile);

                    target_field.animate().before(data.specific_fields_html);
                    target_attribute.animate().append(data.attributes_fields_html).show();

                    var target_field_div = $('.optionnal', mandatory_fields).last();
                    var target_attribute_div = $('.specific-field-attributes', mandatory_fields).last();

                    if (window.console)
                        console.log(target_field_div, target_attribute_div);

                    //TODO: Bug with tips
                    addTip(target_field_div);
                    $('.tip', target_field_div).not('label[rel]').qtip();
                    $('.tip2', target_field_div).not('label[rel]').qtip();

                    target_attribute_div.show();
                    $('.extra-option', target_attribute_div).show();
                    //
                    onChangeMultipleSelect(target_field_div);
                } else {
                    $('#ajax-error', context).html($('#xsd_ajax_error').val()).show();
                }
            },
            error: function (result) {
                loader.hide();

                $('#menu-profiles .profiles-loader', context).hide();

                if (window.console)
                    console.log(result);
                $('#ajax-error', context).html($('#xsd_ajax_error').val()).show();
                if (result.status && result.status.length)
                    $('#ajax-error', context).append('<pre>Status:' + result.status + '</pre>');
                if (result.statusText && result.statusText.length)
                    $('#ajax-error', context).append('<pre>Status:' + result.statusText + '</pre>');
                if (result.responseText && result.responseText.length)
                    $('#ajax-error', context).append('<pre>' + result.responseText + '</pre>');
            }
        });
    }


    logtime('profiles.js overall', true);

    /**
     * Apply tagify for input elements
     * @param $element
     */
    function applyTagify($element) {
        if ($.isFunction($('.tagify').tagify)) {
            $element.tagify({
                delimiters: [13, 44, 59, 58],
                addTagPrompt: $('#text-add-browsenodes').val(),
                addTagOnBlur: true
            });
            $element.attr('has', 'tagify');
        }
    }

    function consoleLog(content) {
        if (window.console) {
            console.log(content);
        }
    }
});