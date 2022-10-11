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

var bulletpointInitialized1 = true;
$(document).ready(function () {
    if (bulletpointInitialized1) return;
    var bulletpointInitialized1 = true;

    if (!$('#bpe-recipient span.bpe-entity'))
        $('#bpe-recipient').html($('#drop-message').clone());
    $("#bpe-sample").html('');

    var sessionId = new Date().valueOf();

    var draggableArguments =
    {
        revert: 'invalid',
        helper: 'clone',
        appendTo: '#main',
        zIndex: 1500
    };

    var draggableOptions =
    {
        revert: // http://stackoverflow.com/questions/5735270/revert-a-jquery-draggable-object-back-to-its-original-container-on-out-event-of
            function (event, ui) {
                if (event !== false)
                    $(this).draggable('option', 'revertDuration', 0);
                else
                    $(this).draggable('option', 'revertDuration', 200);

                $(this).data("uiDraggable").originalPosition = {
                    top: 0,
                    left: 0
                };
                return true; // Always revert
            }
    };

    $('#amazon-bulletpoint-box .close-box').click(function () {
        existing_bullet_point = null;
        $('#amazon-bulletpoint-box').html('');
        $('#amazon-bulletpoint-overlay').remove();
    });

    function InitDraggable() {
        $('#bpe-recipient .bpe-entity').each(function () {
            if (!$(this).attr('id') || $(this).attr('id').length) {
                var entity_id = 'bpe-' + sessionId++;

                $(this).attr('id', entity_id);
            }
        });

        $('#bpe-attributes .bpe-item, #bpe-features .bpe-item, #bpe-fields .bpe-item, #bpe-text .bpe-input').draggable(draggableOptions);
    }

    InitDraggable();

    function InitDroppable() {
        $("#bpe-recipient").droppable({
            accept: "#bpe-recipient .bpe-cloned, #bpe-attributes .bpe-item, #bpe-features .bpe-item, #bpe-fields .bpe-item, #bpe-text .bpe-input",
            drop: function (event, ui) {
                var dropped = ui.draggable;
                var droppedOn = $(this);

                $('.drop-message', $(this)).remove();

                if ($(dropped).hasClass('bpe-cloned'))
                    cloned = $(dropped).detach().css({top: 0, left: 0}).appendTo(droppedOn);
                else
                    cloned = $(dropped).clone().detach().css({top: 0, left: 0}).appendTo(droppedOn);

                if ($(dropped).hasClass('bpe-input') && !$(dropped).hasClass('bpe-cloned'))
                    $('input', dropped).val('');

                var entity_id = 'bpe-' + sessionId++;

                cloned.attr('id', entity_id);
                cloned.addClass('bpe-cloned');
                cloned.draggable(draggableOptions);

                attachBulletPointHandlers(cloned);
            }
        });
    }

    InitDroppable();


    function attachBulletPointHandlers(obj) {
        if (window.console)
            console.log('attachBulletPointHandlers', obj);

        $('a', obj).show();
        $('a', obj).click(function () {
            $(this).parent().fadeOut().remove();
            GenerateSample();
        });

        $('input', obj).blur(function () {
            formatBpeInput($(this).parent().attr('id'));
            GenerateSample();
        });
        if ($(obj).hasClass('bpe-input')) {
            var entity_id = $(obj).attr('id');

            formatBpeInput(entity_id);
        }
        GenerateSample();
    }

    function GenerateSample() {
        $('#bpe-sample').html('');

        $('#bpe-recipient .bpe-entity').each(function () {

            if ($(this).hasClass('bpe-item')) {
                var cloned = $(this).clone();
                $('a', cloned).remove();
                var text = cloned.text().trim();
                $('#bpe-sample').append(text + ' ');
            }
            else if ($(this).hasClass('bpe-input')) {
                var text = $('input', this).val().trim();
                $('#bpe-sample').append(text + ' ');
            }
        });
    }

    function formatBpeInput(entity_id) {
        if (window.console)
            console.log('formatBpeInput', entity_id);

        var target_entity = $('#' + entity_id);

        if (!$('input', target_entity).val() || !$('input', target_entity).val().trim().length)
            return;

        $('input', target_entity).hide();
        $('span', target_entity).remove();
        target_entity.prepend($('<span>'));
        $('span', target_entity).text($('input', target_entity).val());

        $(target_entity).click(function () {
            if ($('input:hidden', $(this))) {
                editBpeInput(target_entity.attr('id'));
            }
        });
    }

    function editBpeInput(entity_id) {
        target_entity = $('#' + entity_id);

        if (window.console)
            console.log('editBpeInput', entity_id);

        $('span', target_entity).remove();
        $('input', target_entity).show().focus();
    }

    function SerializeBulletPoints(bullet_point_id) {
        var bulletpoints = new Object();

        if (window.console)
            console.log('SerializeBulletPoints', bullet_point_id);

        $('#bpe-recipient .bpe-entity').each(function () {
            if ($(this).hasClass('bpe-item')) {
                var cloned = $(this).clone();
                $('a', cloned).remove();
                var text = cloned.text().trim();
                var id_splitted = cloned.attr('id').split('-');
                var prop_splitted = cloned.attr('rel').split('-');
                var oid = id_splitted[1];

                bulletpoint =
                {
                    entity: 'bpe-item',
                    id: id_splitted[1],
                    value: text,
                    type: prop_splitted[1],
                    subtype: prop_splitted[2],
                    prestashop_id: prop_splitted[3]
                };
                bulletpoints[oid] = bulletpoint;
                console.log(bulletpoint);
            }
            else if ($(this).hasClass('bpe-input')) {
                var text = $('input', this).val().trim();
                var id_splitted = $(this).attr('id').split('-');
                var oid = id_splitted[1];

                bulletpoint =
                {
                    entity: 'bpe-input',
                    id: id_splitted[1],
                    value: text,
                    type: 'i',
                    subtype: null,
                    prestashop_id: null
                };
                bulletpoints[oid] = bulletpoint;
                console.log(bulletpoint);
            }

        });

        var pAjax = new Object();

        pAjax.url = $('#amazon_tools_url').val() + '&id_lang=' + $('#id_lang').val() + '&action=bullet-point-encode&id=' + bullet_point_id;
        pAjax.type = 'POST';
        pAjax.data_type = 'jsonp';
        pAjax.data = JSON.stringify(bulletpoints);

        if (window.console)
            console.log(pAjax);

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: 'data=' + pAjax.data,
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (data) {
                    var target_bullet_point = $('.bullet-point-container[id=' + bullet_point_id + ']');

                    console.log(target_bullet_point);

                    $('.bullet-point-item span', target_bullet_point).show();
                    $('input', target_bullet_point).val(data.bullet_point);

                    $('#amazon-bulletpoint-overlay').fadeOut();
                    $('#amazon-bulletpoint-box').fadeOut();

                    existing_bullet_point = null;
                }
            },
            error: function (data) {
                console.log('error', data);
            }
        });
    }


    function UnserializeBulletPoints(json_string) {
        json = JSON.parse(json_string);

        if (window.console)
            console.log('UnserializeBulletPoints', json);

        if (typeof(json) == 'object') {
            $.each(json, function (oid, item) {
                if (window.console)
                    console.log('New Object', item);

                var entity = $('<span>');
                var close_item = $('<a href="#">x</a>');

                entity.addClass('bpe-entity');
                entity.addClass('bpe-cloned');
                entity.addClass(item.entity);
                entity.attr('id', 'bpe-' + item.id);

                identifier = 'bpe-' + item.type + '-' + item.subtype + '-' + item.prestashop_id;
                entity.attr('rel', identifier);

                if (item.type == 'a') {
                    entity.text(item.value);
                    entity.addClass('bpe-item-attribute');
                }
                else if (item.type == 'f') {
                    entity.text(item.value);
                    entity.addClass('bpe-item-feature');
                }
                else if (item.type == 'x') {
                    entity.text(item.value);
                    entity.addClass('bpe-item-field');
                }
                else if (item.type == 'i') {
                    var input_model = $('<input type="text" placeholder="Personalize your bullet point" value="' + item.value + '" />');
                    entity.append(input_model);
                }
                entity.append(close_item.show());

                $('#bpe-recipient').append(entity);

                entity.draggable();
                attachBulletPointHandlers(entity);
                InitDroppable();
            });
        }
        console.log(json)

    }

    $('#amazon-bulletpoint-box .bulletpoint-use').click(function () {

        var bullet_point_id = $('input[name=bullet_point_id]', $('#bulletpoint-box')).val();

        SerializeBulletPoints(bullet_point_id);
    });

    // OnLoad: restore an existing bulletpoint


    if (typeof(existing_bullet_point) != 'undefined' && existing_bullet_point != null) {
        console.log('Existing', existing_bullet_point);
        UnserializeBulletPoints(existing_bullet_point);
    }
    else {
        console.log('Init');
        $('#bpe-recipient').html($('#drop-message').clone());
        $("#bpe-sample").html('');
    }


});