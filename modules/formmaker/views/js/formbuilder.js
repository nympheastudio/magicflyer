/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2015 silbersaiten
 * @version   0.0.1
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */
var formbuilder = {
    draggedItem: false,
    
    init: function()
    {
        $('#form_builder_wrapper').parent('div').removeClass().addClass('col-lg-12');

        $('#elementMenuContainer').fixTo('#form_builder_wrapper', {
            mind: '.page-head:first, #header_infos',
        });
        
        $('#form_maker').droppable({
            drop: function(e, ui) {
                formbuilder.draggedItem = ui.draggable;
            }
        }).sortable({
            placeholder:"formmaker-placeholder-form",
            receive: function(evt, ui) {
                formbuilder.draggedItem.addClass('form-element').removeClass('btn').removeClass('btn-default').removeClass('btn-primary').css('display', 'block').attr('id', 'element_' + formbuilder.getNextElementId());
                
                formbuilder.createField(formbuilder.draggedItem.attr('id'), formbuilder.draggedItem.attr('rel'), formbuilder.draggedItem);
            }
        });

        formbuilder.initFormProductsAutocomplete();

        $(document).on('click', '.delFormProduct', function(){
            formbuilder.delFormProduct($(this).attr('name'));
        });

        $('#form_elements li').draggable({
            helper: 'clone',
            connectToSortable: '#form_maker',
            appendTo: '#form_builder_wrapper',
            zIndex: 1000,
        });

        $(document).on('click', '#form_maker li .field-delete', function(){
            $(this).parents('li:first').slideUp('fast', function(){
                $(this).find('input.field-delete-input').val(1);
            });
        });
    },
	
    createValidationSelect: function(fieldData, parentId, selectedValue)
    {
        if (typeof(validationMethods) != 'undefined' && validationMethods.length) {
            var validationBlock = $(document.createElement('div')).addClass('validation-block-inner form-group col-lg-2')
            var select = $(document.createElement('select')).attr('name', 'input[' + parentId + '][validation]');

            if (!selectedValue && fieldData && typeof(fieldData.settings) != 'undefined' && typeof(fieldData.settings.validation) != 'undefined') {
                selectedValue = fieldData.settings.validation;
            }

            select.append($(document.createElement('option')).attr('value', 'false').text('--'));

            for (var i in validationMethods) {
                select.append($(document.createElement('option')).attr('value', validationMethods[i].method).text(validationMethods[i].name));
            }

            if (selectedValue) {
                select.val(selectedValue);
            } else {
                select.val('false');
            }

            validationBlock
                .append(
                    $(document.createElement('div')).addClass('form-group')
                        .append($(document.createElement('label')).addClass('control-label').append($(document.createElement('span')).addClass('label-tooltip').attr({'data-toggle': 'tooltip', 'data-original-title': 'Select a validation method to be applied to this field\'s value.'}).text('Validation')))
                        .append(select)
                );

            return validationBlock;
        }

        return false;
    },
	
    waitForTinyMce: function(callback)
    {        
        if (typeof(tinymce) == 'undefined' || !tinymce.editors.length) {
            setTimeout(function(){formbuilder.waitForTinyMce(callback)}, 1000);
        } else {
            if (callback) {
                callback.call(this);
            }
        }
    },
    
    createField: function(parentId, fieldType, element)
    {
        switch (fieldType) {
            case 'htmlBlock':
                formbuilder.createHtmlBlock(parentId, fieldType, element, false);
                break;
            case 'textInput':
                formbuilder.createTextField(parentId, fieldType, element, false, false, false, true);
                break;
            case 'passwordInput':
                formbuilder.createPasswordField(parentId, fieldType, element, false, false, false, true);
                break;
            case 'dateInput':
                formbuilder.createDateField(parentId, fieldType, element);
                break;
            case 'colorInput':
                formbuilder.createColorField(parentId, fieldType, element);
                break;
            case 'fileInput':
                formbuilder.createFileField(parentId, fieldType, element, true);
                break;
            case 'textareaInput':
                formbuilder.createTextareaField(parentId, fieldType, element, false, false, false, true);
                break;
            case 'selectInput':
                formbuilder.createSelectField(parentId, fieldType, element, false, true);
                break;
            case 'radioInput':
                formbuilder.createRadioField(parentId, fieldType, element, false, true);
                break;
            case 'checkboxInput':
                formbuilder.createCheckboxField(parentId, fieldType, element, false, true);
                break;
        }
    },
    
    createHtmlBlock: function(parentId, fieldType, element, settingsBlock, valuesBlock, fieldData, validationBlock)
    {
        var f = formbuilder.createFieldWrapper(parentId, fieldType, settingsBlock, valuesBlock, fieldData, validationBlock, true);
        
        f.find('.field-contents').append(formbuilder.createTranslatableField('description_' + parentId, fieldData ? fieldData.description : null, false, 'textarea'))

        f.find('.label-tooltip').tooltip();
        
        element.empty().append(f);
        
        formbuilder.waitForTinyMce(function(){
            for (var i in languages) {
                tinymce.EditorManager.execCommand('mceAddEditor', true, 'fmaker_translate_description_' + parentId + '_' + languages[i].id_lang);
            }
        });
    },

    createTextField: function(parentId, fieldType, element, settingsBlock, valuesBlock, fieldData, validationBlock)
    {
        var f = formbuilder.createFieldWrapper(parentId, fieldType, settingsBlock, valuesBlock, fieldData, validationBlock);
        
        f.find('.label-tooltip').tooltip();
        
        f.find('.field-contents').append($(document.createElement('input')).attr({'type': 'text'}));
        
        element.empty().append(f);
    },
    
    createPasswordField: function(parentId, fieldType, element, settingsBlock, valuesBlock, fieldData, validationBlock)
    {
        var f = formbuilder.createFieldWrapper(parentId, fieldType, settingsBlock, valuesBlock, fieldData, validationBlock);
        
        f.find('.label-tooltip').tooltip();
        
        f.find('.field-contents').append($(document.createElement('input')).attr({'type': 'password'}));
        
        element.empty().append(f);
    },
    
    createDateField: function(parentId, fieldType, element, settingsBlock, valuesBlock, fieldData, validationBlock)
    {
        var f = formbuilder.createFieldWrapper(parentId, fieldType, settingsBlock, valuesBlock, fieldData, validationBlock),
            p = $(document.createElement('div')).addClass('input-group fixed-width-md');
        
        p.append($(document.createElement('input')).attr({'type': 'text', 'size': 3}).datepicker());
        p.append($(document.createElement('span')).addClass('input-group-addon')
            .append($(document.createElement('i')).addClass('icon-calendar'))
        )
        
        f.find('.field-contents').append(p);
        f.find('.label-tooltip').tooltip();
        
        element.empty().append(f);
    },

    createColorField: function(parentId, fieldType, element, settingsBlock, valuesBlock, fieldData, validationBlock)
    {
        var f = formbuilder.createFieldWrapper(parentId, fieldType, settingsBlock, valuesBlock, fieldData, validationBlock),
            p = $(document.createElement('div')).addClass('input-group fixed-width-md');
        
        p.append($(document.createElement('input')).attr({'type': 'color', 'size': 3}).spectrum());
        
        f.find('.field-contents').append($(document.createElement('input')).attr({'type': 'color', 'size': 3}).val('#000000'));
        f.find('.label-tooltip').tooltip();
        
        element.empty().append(f);
        
        f.find('input[type=color]').spectrum();
    },
    
    createTextareaField: function(parentId, fieldType, element, settingsBlock, valuesBlock, fieldData, validationBlock)
    {
        var f = formbuilder.createFieldWrapper(parentId, fieldType, settingsBlock, valuesBlock, fieldData, validationBlock);
        
        f.find('.field-contents').append($(document.createElement('textarea')));
        f.find('.label-tooltip').tooltip();
        
        element.empty().append(f);
    },
    
    createFileField: function(parentId, fieldType, element, settingsBlock, valuesBlock, fieldData, validationBlock)
    {
        var f = formbuilder.createFieldWrapper(parentId, fieldType, settingsBlock, valuesBlock, fieldData, validationBlock),
            settings = f.find('.settings-block-contents');

        settings
            .append(
                $(document.createElement('div')).addClass('form-group')
                .append($(document.createElement('label')).text('Allowed extensions'))
                .append($(document.createElement('input')).attr({'type': 'text', 'name': 'input[' + parentId + '][settings][extensions]'}).addClass('allowed-extensions').val((fieldData && typeof(fieldData.settings.extensions) != 'undefined') ? fieldData.settings.extensions : null))
            )
            .append(
                $(document.createElement('div')).addClass('help-block').text('List of allowed extensions, delimited by a comma (ex. jpg,jpeg,png,gif)')
            )

        f.find('.field-contents').append($(document.createElement('input')).attr({'type': 'file'}));
        f.find('.label-tooltip').tooltip();
        
        element.empty().append(f);
    },
    
    createSelectField: function(parentId, fieldType, element, settingsBlock, valuesBlock, fieldData, validationBlock)
    {
        var f = formbuilder.createFieldWrapper(parentId, fieldType, settingsBlock, valuesBlock, fieldData, validationBlock),
            valuesContainer = f.find('.values-block-contents'),
            values          = $(document.createElement('ul')).addClass('settings-list'),
            addValueBtn     = $(document.createElement('a')).attr('href', '#').addClass('btn btn-primary').text('Add Value'),
            select          = $(document.createElement('select')),

        clearSelect = function(value) {
            var valueId = parseInt(value.attr('rel')),
                select = value.parents('.field-wrapper:first').find('.field-contents select')

            if (!isNaN(valueId)) {
                select.find('option[value=' + valueId + ']').remove();
            }
	    },
        updateSelect = function(value, valueText) {
            var valueId = parseInt(value.attr('rel')),
                select = value.parents('.field-wrapper:first').find('.field-contents select');

            if (!isNaN(valueId)) {
                select.find('option[value=' + valueId + ']').text(valueText);
            }
        }

        if (fieldData && typeof(fieldData.values) != 'undefined') {
            var index = 0;

            for (var i in fieldData.values) {
                values.append(formbuilder.createValueBlock(
                    fieldData.values[i].name,
                    parentId,
                    index,
                    fieldData.values[i].id,
                    function(valueText){
                        updateSelect(this, valueText);
                    },
                    function(){
                        clearSelect(this);
                    }
                ));

                select.append($(document.createElement('option')).attr('value', index).text(fieldData.values[i].name[currentLanguage]));

                index++;
            }
        } else {
            values.append(formbuilder.createValueBlock(
                '--',
                parentId,
                0,
                0,
                function(valueText){
                    updateSelect(this, valueText);
                },
                function(){
                    clearSelect(this);
                }
            ));

            select.append($(document.createElement('option')).attr('value', 0).text('--'));
        }

        valuesContainer.append(values).append(addValueBtn);

        addValueBtn.click(function(e){
            e.preventDefault();

            var block = $(this).prev('ul.settings-list'),
                select = $(this).parents('.field-wrapper:first').find('.field-contents select'),
                parentId = $(this).parents('li.form-element:first').attr('id'),
                valueId = formbuilder.getNextValueIdInBlock(block);

            select.append($(document.createElement('option')).attr({'value': valueId}).text('--'));

            block.append(formbuilder.createValueBlock(
                '--',
                parentId,
                valueId,
                0,
                function(valueText){
                    updateSelect(this, valueText);
                },
                function(){
                    clearSelect(this);
                }
            ));

            return false;
        });

        f.find('.field-contents').append(select);
        f.find('.label-tooltip').tooltip();
        
        element.empty().append(f);
    },
    
    createRadioField: function(parentId, fieldType, element, settingsBlock, valuesBlock, fieldData, validationBlock)
    {
        var f = formbuilder.createFieldWrapper(parentId, fieldType, settingsBlock, valuesBlock, fieldData, validationBlock),
            valuesContainer = f.find('.values-block-contents'),
            values          = $(document.createElement('ul')).addClass('settings-list'),
            addValueBtn     = $(document.createElement('a')).attr('href', '#').addClass('btn btn-primary').text('Add Value'),
            radioBlock      = $(document.createElement('div')).addClass('radio-block'),
            createRadio     = function(parent, id, value) {
                var block = $(document.createElement('div')).addClass('radio');

                block.append($(document.createElement('input')).attr({'type': 'radio', 'name': parent.attr('id') + '_' + id})).append($(document.createElement('span')).addClass('value-text').text(typeof(value) == 'string' ? value : value[currentLanguage]));

                return block;
            },
            clearRadio = function(value) {
                var valueId = parseInt(value.attr('rel')),
                    parentId = value.parents('li.form-element:first').attr('id'),
                    radioBlock = value.parents('.field-wrapper:first').find('.field-contents div.radio')
                
                if ( ! isNaN(valueId)) {
                    radioBlock.find('input[name=' + parentId + '_' + valueId + ']').parents('.radio:first').remove();
                }
            },
            updateRadio = function(value, valueText) {
                var valueId = parseInt(value.attr('rel')),
                    parentId = value.parents('li.form-element:first').attr('id'),
                    radioBlock = value.parents('.field-wrapper:first').find('.field-contents div.radio');

                if ( ! isNaN(valueId)) {
                    radioBlock.find('input[name=' + parentId + '_' + valueId + ']').next('span.value-text').text(valueText);
                }
            }

        if (fieldData && typeof(fieldData.values) != 'undefined') {
            var index = 0;

            for (var i in fieldData.values) {
                values.append(formbuilder.createValueBlock(
                    fieldData.values[i].name,
                    parentId,
                    index,
                    fieldData.values[i].id,
                    function(valueText){
                        updateRadio(this, valueText);
                    },
                    function(){
                        clearRadio(this);
                    }
                ));

                radioBlock.append(createRadio(element, index, fieldData.values[i].name));

                index++;
            }
        } else {
            values.append(formbuilder.createValueBlock(
                '--',
                parentId,
                0,
                0,
                function(valueText){
                    updateRadio(this, valueText);
                },
                function(){
                    clearRadio(this);
                }
            ));

            radioBlock.append(createRadio(element, 0, '--'));
        }

        valuesContainer.append(values).append(addValueBtn);

        addValueBtn.click(function(e){
            e.preventDefault();

            var block = $(this).prev('ul.settings-list'),
                select = $(this).parents('.field-wrapper:first').find('.field-contents select'),
                parentId = $(this).parents('li.form-element:first').attr('id'),
                value_id = formbuilder.getNextValueIdInBlock(block);

            radioBlock.append(createRadio(block.parents('li.form-element:first'), value_id, '--'));

            block.append(formbuilder.createValueBlock(
                '--',
                parentId,
                value_id,
                0,
                function(valueText){
                    updateRadio(this, valueText);
                },
                function(){
                    clearRadio(this);
                }
            ));

            return false;
        });

        f.find('.field-contents').append(radioBlock);
        f.find('.label-tooltip').tooltip();

        element.empty().append(f);
    },
    
    createCheckboxField: function(parentId, fieldType, element, settingsBlock, valuesBlock, fieldData, validationBlock)
    {
        var f = formbuilder.createFieldWrapper(parentId, fieldType, settingsBlock, valuesBlock, fieldData, validationBlock),
            valuesContainer = f.find('.values-block-contents'),
            values          = $(document.createElement('ul')).addClass('settings-list'),
            addValueBtn     = $(document.createElement('a')).attr('href', '#').addClass('btn btn-primary').text('Add Value'),
            checkboxBlock   = $(document.createElement('div')).addClass('checkbox-block'),
            createCheckbox  = function(parent, id, value) {
                var block = $(document.createElement('div')).addClass('checkbox');
                
                block.append($(document.createElement('input')).attr({'type': 'checkbox', 'name': parent.attr('id') + '_' + id})).append($(document.createElement('span')).addClass('value-text').text(typeof(value) == 'string' ? value : value[id_language]));
		
                return block;
            },
            clearCheckbox = function(value) {
                var valueId = parseInt(value.attr('rel')),
                    parentId = value.parents('li.form-element:first').attr('id'),
                    checkboxBlock = value.parents('.field-wrapper:first').find('.field-contents div.checkbox')
                
                if (!isNaN(valueId)) {
                    checkboxBlock.find('input[name=' + parentId + '_' + valueId + ']').parents('.checkbox:first').remove();
                }
            },
            updateCheckbox = function(value, valueText) {
                var valueId = parseInt(value.attr('rel')),
                    parentId = value.parents('li.form-element:first').attr('id'),
                    checkboxBlock = value.parents('.field-wrapper:first').find('.field-contents div.checkbox');
                
                if (!isNaN(valueId)) {
                    checkboxBlock.find('input[name=' + parentId + '_' + valueId + ']').next('span.value-text').text(valueText);
                }
            }
	    
        if (fieldData && typeof(fieldData.values) != 'undefined') {
            var index = 0;
            for (var i in fieldData.values) {
                values.append(formbuilder.createValueBlock(
                    fieldData.values[i].name,
                    parentId,
                    index,
                    fieldData.values[i].id,
                    function(valueText){
                        updateCheckbox(this, valueText);
                    },
                    function(){
                        clearCheckbox(this);
                    }
                ));
                
                checkboxBlock.append(createCheckbox(element, index, fieldData.values[i].name));
                
                index++;
            }
        } else {
            values.append(formbuilder.createValueBlock(
                '--',
                parentId,
                0,
                0,
                function(valueText){
                    updateCheckbox(this, valueText);
                },
                function(){
                    clearCheckbox(this);
                }
            ));
            
            checkboxBlock.append(createCheckbox(element, 0, '--'));
        }
	
        valuesContainer.append(values).append(addValueBtn);
	
        addValueBtn.click(function(e){
            e.preventDefault();
            
            var block = $(this).prev('ul.settings-list'),
                select = $(this).parents('.field-wrapper:first').find('.field-contents select'),
                parentId = $(this).parents('li.form-element:first').attr('id'),
                value_id = formbuilder.getNextValueIdInBlock(block);
            
            checkboxBlock.append(createCheckbox(block.parents('li.form-element:first'), value_id, '--'));
            
            block.append(formbuilder.createValueBlock(
                '--',
                parentId,
                value_id,
                0,
                function(valueText){
                    updateCheckbox(this, valueText);
                },
                function(){
                    clearCheckbox(this);
                }
            ));
            
            return false;
        });
	
        f.find('.field-contents').append(checkboxBlock);
        f.find('.label-tooltip').tooltip();
        
        element.empty().append(f);
    },
    
    createFieldWrapper: function(parentId, fieldType, settingsBlock, valuesBlock, fieldData, validationBlock, notAnInput)
    {
        notAnInput = notAnInput || false;
        settingsBlock = settingsBlock || false,
        validationBlock = validationBlock || false,
        valuesBlock = valuesBlock || false,
        fieldData = (typeof(fieldData) == 'object' && fieldData != null) ? fieldData : false,
        deleted = (fieldData && typeof(fieldData.deleted) != 'undefined' && parseInt(fieldData.deleted) == 1) ? 1 : 0,
        settings = false,
        validation = false,
        values = false;

        if (settingsBlock) {
            settings = $(document.createElement('div')).addClass('settings-block-inner form-group')
            .append(
                $(document.createElement('label')).text(formbuilder.tr('Field Settings'))
            )
            .append(
                $(document.createElement('div')).addClass('settings-block-contents')
            )
        }
	
        if (notAnInput) {
            return $(document.createElement('div')).addClass('field-wrapper ' + fieldType)
                    .append(
                        $(document.createElement('label')).addClass('field-name').text(formbuilder.getFieldNameByReference(fieldType))
                    )
                    .append(
                        $(document.createElement('span')).addClass('field-delete').append(
                            $(document.createElement('i')).addClass('icon-remove')
                        )
                    )
                    .append($(document.createElement('input')).attr({'type': 'hidden', 'name': 'input[' + parentId + '][type]'}).val(fieldType))
                    .append($(document.createElement('input')).attr({'type': 'hidden', 'name': 'input[' + parentId + '][id]'}).val((fieldData && typeof(fieldData.id) != 'undefined') ? fieldData.id : 0))
                    .append($(document.createElement('input')).attr({'type': 'hidden', 'name': 'input[' + parentId + '][deleted]'}).addClass('field-delete-input').val(deleted))
                    .append(
                        $(document.createElement('div')).addClass('field-contents-wrapper row')
                            .append(
                                $(document.createElement('div')).addClass('form-group  col-lg-10')
                                    .append($(document.createElement('label')).text(formbuilder.tr('Preview')))
                                    .append($(document.createElement('div')).addClass('field-contents'))
                            )
                            .append(
                                $(document.createElement('div')).addClass('form-group col-lg-2')
                                    .append($(document.createElement('label')).text(formbuilder.tr('Class')))
                                    .append(
                                        $(document.createElement('div'))
                                            .append($(document.createElement('input')).attr({'type': 'text', 'name': 'input[' + parentId + '][class]'}).val(fieldData ? fieldData.css_class : null))
                                    )
                            )
                            .append(
                                $(document.createElement('div')).addClass('clear').css('clear', 'both')
                            )
                    )
        }

        if (validationBlock) {
            validation = formbuilder.createValidationSelect(fieldData, parentId, fieldData.validation)
        } else {
            if (fieldType == 'dateInput') {
                var time = false;
                if (fieldData.settings && fieldData.settings.time && fieldData.settings.time == "1") {
                    time = true;
                }
                validation =  $(document.createElement('div')).addClass('form-group col-lg-2')
                    .append($(document.createElement('label')).text(formbuilder.tr('Time')))
                    .append($(document.createElement('span')).addClass('switch prestashop-switch fixed-width-lg')
                        .append($(document.createElement('input')).attr({'type': 'radio', 'name': 'input[' + parentId + '][settings][time]', 'id': 'block_time_'+parentId+'_on', 'value': 1}).prop({'checked':time}))
                        .append($(document.createElement('label')).attr({'for': 'block_time_'+parentId+'_on'}).addClass('radioCheck dtp dtp_on').text(formbuilder.tr('Yes')))
                        .append($(document.createElement('input')).attr({'type': 'radio', 'name': 'input[' + parentId + '][settings][time]', 'id': 'block_time_'+parentId+'_off', 'value': 0}).prop({'checked':!time}))
                        .append($(document.createElement('label')).attr({'for': 'block_time_'+parentId+'_off'}).addClass('radioCheck dtp dtp_off').text(formbuilder.tr('No')))
                        .append($(document.createElement('a')).addClass('slide-button btn'))
                    )
            } else {
                validation = $(document.createElement('div')).addClass('col-lg-2');
            }
        }
	
        if (valuesBlock) {
            values = $(document.createElement('div')).addClass('values-block-inner form-group col-lg-12')
            .append(
                $(document.createElement('label')).text(formbuilder.tr('Field Values'))
            )
            .append(
                $(document.createElement('div')).addClass('values-block-contents')
            );
        }
	
        var r = $(document.createElement('div')).addClass('field-wrapper ' + fieldType)
            .append(
                $(document.createElement('label')).addClass('field-name').text(formbuilder.getFieldNameByReference(fieldType))
            )
            .append(
                $(document.createElement('span')).addClass('field-delete').append(
                    $(document.createElement('i')).addClass('icon-remove')
                )
            )
            .append(
                $(document.createElement('div')).addClass('field-contents-wrapper row')
                .append(
                    $(document.createElement('div')).addClass('form-group  col-lg-3')
                        .append($(document.createElement('label')).addClass('required').text(formbuilder.tr('Label')))
                        .append(formbuilder.createTranslatableField('label_' + parentId, fieldData ? fieldData.label : null))
                        //.append($(document.createElement('input')).attr({'type': 'text', 'name': 'input[' + parentId + '][label]'}).val(fieldData ? fieldData.label : null))
                        .append($(document.createElement('input')).attr({'type': 'hidden', 'name': 'input[' + parentId + '][type]'}).val(fieldType))
                        .append($(document.createElement('input')).attr({'type': 'hidden', 'name': 'input[' + parentId + '][id]'}).val((fieldData && typeof(fieldData.id) != 'undefined') ? fieldData.id : 0))
                        .append($(document.createElement('input')).attr({'type': 'hidden', 'name': 'input[' + parentId + '][deleted]'}).addClass('field-delete-input').val(deleted))
                )
                .append(
                    $(document.createElement('div')).addClass('form-group col-lg-3')
                        .append($(document.createElement('label')).text(formbuilder.tr('Description')))
                        .append(formbuilder.createTranslatableField('description_' + parentId, fieldData ? fieldData.description : null))
                        //.append($(document.createElement('input')).attr({'type': 'text', 'name': 'input[' + parentId + '][description]'}).val(fieldData ? fieldData.description : null))
                )
                .append(
                    $(document.createElement('div')).addClass('form-group col-lg-2')
                        .append($(document.createElement('label')).text(formbuilder.tr('Class')))
                        .append(
                            $(document.createElement('div'))
                                .append($(document.createElement('input')).attr({'type': 'text', 'name': 'input[' + parentId + '][class]'}).val(fieldData ? fieldData.css_class : null))
                        )
                )
                .append(
                    validation
                )
                .append(
                    $(document.createElement('div')).addClass('form-group col-lg-2')
                        .append($(document.createElement('label')).text(formbuilder.tr('Required')))
                        .append($(document.createElement('span')).addClass('switch prestashop-switch fixed-width-lg')
                            .append($(document.createElement('input')).attr({'type': 'radio', 'name': 'input[' + parentId + '][required]', 'id': 'block_required_'+parentId+'_on', 'value': 1}).prop('checked', fieldData.required))
                            .append($(document.createElement('label')).attr({'for': 'block_required_'+parentId+'_on'}).addClass('radioCheck').text(formbuilder.tr('Yes')))
                            .append($(document.createElement('input')).attr({'type': 'radio', 'name': 'input[' + parentId + '][required]', 'id': 'block_required_'+parentId+'_off', 'value': 0}).prop('checked', !fieldData.required))
                            .append($(document.createElement('label')).attr({'for': 'block_required_'+parentId+'_off'}).addClass('radioCheck').text(formbuilder.tr('No')))
                            .append($(document.createElement('a')).addClass('slide-button btn'))
                        )
                )
                .append(
                    $(document.createElement('div')).addClass('form-group col-lg-12')
                    .append($(document.createElement('label')).text(formbuilder.tr('Preview')))
                    .append($(document.createElement('div')).addClass('field-contents'))
                )
                .append(
                    values
                )
                .append(
                    settings
                )
            );
	
        return r;
    },
    
    createValueBlock: function(valueName, parentId, valueId, valueDbId, onRefresh, onDelete)
    {
        var v = $(document.createElement('li')).addClass('settings-list-value').attr('rel', valueId);
        
        if (typeof(valueName) == 'string') {
            valueName = formbuilder.createLanguageField(valueName);
        }
	
        v.append($(document.createElement('span')).addClass('settings-value-name')
            .append(formbuilder.createTranslatableField('values_' + parentId + '_' + valueId + '_' + valueDbId, valueName))
            .append($(document.createElement('input')).attr({'type': 'hidden', 'name': 'input[' + parentId + '][values][value_' + valueId + '][deleted]'}).val(0).addClass('value-deleted-input'))
            .append($(document.createElement('div')).addClass('field-value-actions-wrapper')
                .append($(document.createElement('span')).addClass('value-name-refresh').attr('rel', 'fmaker_translate_values_' + parentId + '_' + valueId + '_' + valueDbId + '_')
                    .append($(document.createElement('i')).addClass('icon-refresh'))
                )
                .append($(document.createElement('span')).addClass('settings-value-delete')
                    .append($(document.createElement('i')).addClass('icon-remove'))
                )
            )
        );

        v.find('.value-name-refresh').click(function(e){
            e.preventDefault();
            
            var p = $(this).parents('li.settings-list-value:first'),
                t = p.find('input[name=' + $(this).attr('rel') + id_language + ']').val();
            
            if (onRefresh) {
                onRefresh.call(v, [t]);
            }
            
            return false;
        });
	
        v.find('.settings-value-delete').click(function(e){
            e.preventDefault();
            
            v.find('input.value-deleted-input').val(1);
            v.slideUp('fast');
            
            if (onDelete) {
                onDelete.call(v);
            }
            
            return false;
        });
	
        return v;
    },
    
    createLanguageField: function(value)
    {
        var r = [];
        
        for (var i in languages) {
            r[languages[i].id_lang] = value;
        }
        
        return r;
    },
    
    getNextValueIdInBlock: function(valuesBlock)
    {
        var valuesList = valuesBlock.find('li.settings-list-value');
        
        if (valuesList.length) {
            var max = 0;
            
            valuesList.each(function(){
                if ($(this).attr('rel')) {
                    var currentId = parseInt($(this).attr('rel'));
                    
                    if (!isNaN(currentId) && currentId > max) {
                        max = currentId;
                    }
                }
            });
            
            return max + 1;
        }
        
        return 0;
    },
    
    getNextElementId: function()
    {
        var list = $('#form_maker').find('li.form-element');
        
        if (list.length) {
            var max = 0;
            
            list.each(function(){
                if ($(this).attr('id')) {
                    var currentId = parseInt($(this).attr('id').split('_')[1]);
                    
                    if (currentId > max) {
                        max = currentId;
                    }
                }
            });
            
            return max + 1;
        }
        
        return 0;
    },
    
    loadFields: function(fields)
    {
        var index = 1,
            container = $('#form_maker'),
            parent,
            parentId,
            deleted;
    
        for (var i in fields) {
            parentId = 'element_' + index;
            deleted = (typeof(fields[i].deleted) != 'undefined' && parseInt(fields[i].deleted) == 1) ? 1 : 0;
            parent = $(document.createElement('li')).addClass('form-element').attr('id', parentId).attr('rel', fields[i].type);
            
            if (deleted) {
                parent.hide();
            }
        
            container.append(parent);
        
            switch (fields[i].type) {
                case 'htmlBlock':
                    formbuilder.createHtmlBlock(parentId, fields[i].type, parent, false, false, fields[i], false);
                    break;
                case 'textInput':
                    formbuilder.createTextField(parentId, fields[i].type, parent, false, false, fields[i], true);
                    break;
                case 'passwordInput':
                    formbuilder.createPasswordField(parentId, fields[i].type, parent, false, false, fields[i], true);
                    break;
                case 'dateInput':
                    formbuilder.createDateField(parentId, fields[i].type, parent, false, false, fields[i]);
                    break;
                case 'colorInput':
                    formbuilder.createColorField(parentId, fields[i].type, parent, false, false, fields[i]);
                    break;
                case 'fileInput':
                    formbuilder.createFileField(parentId, fields[i].type, parent, true, false, fields[i]);
                    break;
                case 'textareaInput':
                    formbuilder.createTextareaField(parentId, fields[i].type, parent, false, false, fields[i], true);
                    break;
                case 'selectInput':
                    formbuilder.createSelectField(parentId, fields[i].type, parent, false, true, fields[i]);
                    break;
                case 'radioInput':
                    formbuilder.createRadioField(parentId, fields[i].type, parent, false, true, fields[i]);
                    break;
                case 'checkboxInput':
                    formbuilder.createCheckboxField(parentId, fields[i].type, parent, false, true, fields[i]);
                    break;
            }
            index++;
        }
    },
    
    getFieldNameByReference: function(ref)
    {
        switch (ref) {
            case 'htmlBlock':
                return formbuilder.tr('HTML Block');
                break;
            case 'textInput':
                return formbuilder.tr('Text Input');
                break;
            case 'passwordInput':
                return formbuilder.tr('Password Input');
                break;
            case 'dateInput':
                return formbuilder.tr('Date Picker');
                break;
            case 'colorInput':
                return formbuilder.tr('Color Picker');
                break;
            case 'fileInput':
                return formbuilder.tr('File Upload');
                break;
            case 'textareaInput':
                return formbuilder.tr('Textarea');
                break;
            case 'selectInput':
                return formbuilder.tr('Select');
                break;
            case 'radioInput':
                return formbuilder.tr('Radio Group');
                break;
            case 'checkboxInput':
                return formbuilder.tr('Checkbox Group');
                break;
        }
        
        return formbuilder.tr('Unknown Input');
    },
    
    createTranslatableField: function(field_name, values, fieldClass, type)
    {
        type = type || 'input';
        values = (typeof(values) == 'object' && values != null) ? values : false;
        fieldClass = fieldClass || 'row';
    
        var field_wrapper = $(document.createElement('div')).addClass(fieldClass);
    
        for (var i in languages) {
            if (languages.length > 0) {
                var wrapper = $(document.createElement('div')).addClass('translatable-field lang-' + languages[i].id_lang),
                    container = $(document.createElement('div')).addClass('col-lg-9'),
                    languageButtonBlock = $(document.createElement('div')).addClass('col-lg-2'),
                    languageButton = $(document.createElement('button')).attr({'type': 'button', 'data-toggle': 'dropdown'}).addClass('btn btn-default dropdown-toggle'),
                    languageDropDown = $(document.createElement('ul')).addClass('dropdown-menu');
                
                switch (type) {
                    case 'input':
                        container.append(
                            $(document.createElement('input')).attr({'type': 'text', 'name': 'fmaker_translate_' + field_name + '_' + languages[i].id_lang}).val((values && typeof(values[languages[i].id_lang]) != 'undefined') ? values[languages[i].id_lang] : null)
                        );
                        break;
                    case 'textarea':
                        container.append(
                            $(document.createElement('textarea')).attr({'name': 'fmaker_translate_' + field_name + '_' + languages[i].id_lang, 'id': 'fmaker_translate_' + field_name + '_' + languages[i].id_lang}).val((values && typeof(values[languages[i].id_lang]) != 'undefined') ? values[languages[i].id_lang] : null)
                        );
                        break;
                }
                
                for (var y in languages) {
                    if (languages[y].id_lang == languages[i].id_lang) {
                        languageButton.text(languages[y].iso_code + ' ');
                        languageButton.append($(document.createElement('span')).addClass('caret'))
                    }
                    
                    languageDropDown.append($(document.createElement('li'))
                        .append($(document.createElement('a')).attr('href', 'javascript:hideOtherLanguage(' + languages[y].id_lang + ');').text(languages[y].name))
                    )
                }
                
                if (languages[i].id_lang != defaultLanguage) {
                    wrapper.hide();
                }
                
                languageButtonBlock.append(languageButton).append(languageDropDown);
                
                wrapper.append(container).append(languageButtonBlock);
                
                field_wrapper.append(wrapper);
            }
        }
    
        return field_wrapper;
    },
    
    tr: function(str)
    {
        if (typeof(formmakerTranslate) != 'undefined' && str in formmakerTranslate) {
            return formmakerTranslate[str];
        }
        
        return str;
    },
    
    initFormProductsAutocomplete: function ()
    {
        $('#product_form_products_input')
            .autocomplete(productSearchPath, {
                minChars: 1,
                autoFill: true,
                max:20,
                matchContains: true,
                mustMatch:false,
                scroll:false,
                cacheLength:0,
                parse: function(data) {
                    data = $.parseJSON(data);
                    var parsed_data = [];
                    
                    for (i = 0; i < data.length; i++) {
                        parsed_data[i] = {
                            data: data[i],
                            value: data[i].ref,
                            result: data[i].name
                        }
                    }
                    
                    return parsed_data 
                },
                formatItem: function(item) {
                    return item.name+' - '+item.ref;
                }
            }).result(formbuilder.addFormProduct);
        
        $('#product_form_products_input').setOptions({
            extraParams: {
                excludeIds: formbuilder.getFormProductIds(),
                exclude_packs: true
            }
        });
    },

    getFormProductIds: function()
    {
        if ($('#inputFormProducts').val() === undefined)
            return 0;
        
        return $('#inputFormProducts').val().replace(/\-/g,',');
    },

    addFormProduct: function(event, data, formatted)
    {
        if (data == null)
            return false;
    
        var productId = data.id;
        var productName = data.name;
        var productReference = data.ref;
        var productImage = data.image;
    
        var $divFormProducts = $('#divFormProducts');
        var $inputFormProducts = $('#inputFormProducts');
        var $nameFormProducts = $('#nameFormProducts');
    
        var productFormBlock = $(document.createElement('div')).addClass('form-control-static row');
    
        productFormBlock.append(
            $(document.createElement('div')).addClass('col-lg-1')
                .append(
                    $(document.createElement('button')).addClass('delFormProduct btn btn-default').attr('name', productId)
                        .append($(document.createElement('i')).addClass('icon-remove text-danger'))
                )
        )
    
        productFormBlock.append(
            $(document.createElement('div')).addClass('col-lg-11')
                /*.append(
                productImage ? $(document.createElement('img')).addClass('thumbnail').attr('src', productImage) : null
                )*/
                .append($(document.createElement('span')).text(productName+' ('+productReference+')'))
        )
    
        $divFormProducts.append(productFormBlock);
    
        $nameFormProducts.val($nameFormProducts.val() + productName + '¤');
        $inputFormProducts.val($inputFormProducts.val() + productId + '-');
        $('#product_form_products_input').val('');
        $('#product_form_products_input').setOptions({
            extraParams: {excludeIds : formbuilder.getFormProductIds()}
        });
    },

    delFormProduct: function(id)
    {
        var div = getE('divFormProducts');
        var input = getE('inputFormProducts');
        var name = getE('nameFormProducts');
    
        var inputCut = input.value.split('-');
        var nameCut = name.value.split('¤');
    
        if (inputCut.length != nameCut.length)
            return jAlert('Bad size');
    
        input.value = '';
        name.value = '';
        div.innerHTML = '';
        for (i in inputCut) {
            if (!inputCut[i] || !nameCut[i])
                continue ;
            if (inputCut[i] != id) {
                input.value += inputCut[i] + '-';
                name.value += nameCut[i] + '¤';
                div.innerHTML += '<div class="form-control-static"><button type="button" class="delFormProduct btn btn-default" name="' + inputCut[i] +'"><i class="icon-remove text-danger"></i></button>&nbsp;' + nameCut[i] + '</div>';
            }
        }
    
        $('#product_form_products_input').setOptions({
            extraParams: {excludeIds : formbuilder.getFormProductIds()}
        });
    }
}

$(function(){
	if ($('form#fm_form_form').length) {
		formbuilder.init();
		
		if (typeof(postFields) != 'undefined') {
			formbuilder.loadFields(postFields)
		}
	}

    $('.form-elements-wrapper').on('click', '.dtp', function(){
        var p = $(this).closest('.field-contents-wrapper.row');
        var inp = p.find('.hasDatepicker');
    });
	
    $('.form-assign-all').change(function(){
        var p = $(this).parents('.prestashop-switch:first'),
            s;
        
        if (p.length) {
            s = p.find('input[type=radio]:checked');
            
            if (s.length) {
                if (s.attr('value') == 1) {
                    var l = $('.assign-to-all-wrapper').not(p);
                    
                    if (l.length) {
                        l.each(function(){
                            $(this).find('input[type=radio][value=0]').prop('checked', true);
                        });
                    }
                }
                
                var selectedForm = s.attr('value') == 0 ? 0 : p.data('formId');
                
                $.post(controllerPath, {'setSelectedForm': selectedForm}, function(data){
                
                }, 'json');
            }
        }
    });
});
