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
var formmakerfront = {
    init: function() {
        $('form.form-maker-form input.datepicker').datepicker();
        $('form.form-maker-form input.datetimepicker').datetimepicker();
        $('form.form-maker-form input.colorinput').spectrum();
        
        formmakerfront.registerFileUpload();
        
        $('form.form-maker-form').submit(function(e){
            e.preventDefault();
            
            formmakerfront.submitForm($(this));
            
            return false;
        });
    },
    
    registerFileUpload: function() {
        $('.fileinput').fileupload({
        
            // This element will accept file drag/drop uploading
            url: formmakerPath,
            dropZone: $('#drop'),
            maxNumberOfFiles: 1,
            formData: {
                'action': 'upload',
                'form_id': false,
                'element_id': false,
                'input_name': false,
                'current_filename': false
            },
        
            // This function is called when a file is added to the queue;
            // either via the browse button, or via drag/drop:
            add: function (e, data) {
                var formatFileSize = function formatFileSize(bytes) {
                    if (typeof bytes !== 'number') {
                        return '';
                    }
                
                    if (bytes >= 1000000000) {
                        return (bytes / 1000000000).toFixed(2) + ' GB';
                    }
                
                    if (bytes >= 1000000) {
                        return (bytes / 1000000).toFixed(2) + ' MB';
                    }
                
                    return (bytes / 1000).toFixed(2) + ' KB';
                },
                ul = data.form.find('input[name=' + data.fileInput.attr('name') + ']').parents('.form-group:first').find('ul.upload-data'),
                upload = $(this),
                existing_file = upload.next('input[name=' + data.fileInput.attr('name') + '_file]');
                
                if (existing_file.length) {
                    existing_file = existing_file.val();
                } else {
                    existing_file = 0;
                }
                
                ul.empty();
                
                formmakerfront.setUploadOptions(
                    upload,
                    {
                    'action': 'upload',
                    'input_name':  data.fileInput.attr('name'),
                    'form_id': data.form.data('formId'),
                    'input_id': parseInt(data.fileInput.attr('name').split('_')[1]),
                    'current_filename': existing_file
                    }
                );
            
                var tpl = $('<li class="working"><div class="load-indicator-wrapper"><div class="load-indicator"></div></div><p></p></div></li>');
            
                // Append the file name and file size
                tpl.find('p').text(data.files[0].name)
                        .prepend('<span class="status icon icon-time wait"></span>')
                        .append(' <i>(' + formatFileSize(data.files[0].size) + ')</i>')
                        .append('<span><i class="icon-remove"></span>');
            
                // Add the HTML to the UL element
                data.context = tpl.appendTo(ul);
            
                // Listen for clicks on the cancel icon
                tpl.find('span').click(function(){
            
                    if(tpl.hasClass('working')){
                        jqXHR.abort();
                    }
            
                    tpl.fadeOut(function(){
                        $('div.uploader > .filename').text(titleFileUploadFM);
                        $('div.uploader > .filename-container').remove();
                        tpl.remove();
                    });
            
                });
            
                // Automatically upload the file once it is added to the queue
                var jqXHR = data.submit().success(function(result, textStatus, jqXHR){
                    var json = JSON.parse(result);
                    var status = json['status'];
                
                    if (status == 'error'){
                        data.context.addClass('error');
                        
                        if (typeof(json.message) != 'undefined') {
                            data.context.append($(document.createElement('div')).addClass('alert alert-danger upload_err clear').html(json.message));
                        }
                    } else if (status == 'success') {
                        var anchor = $('form.form-maker-form[data-form-id=' + json['id_form'] + ']').find('input[name=' + json['input_name'] + ']'),
                            input = $('form.form-maker-form[data-form-id=' + json['id_form'] + ']').find('input[name=' + json['input_name'] + '_file]');

                        if (input.length) {
                            input.val(json['filename']);
                        } else if (anchor.length) {
                            $(document.createElement('input')).attr({'type': 'hidden', 'name': json['input_name'] + '_file'}).addClass('filename-container').val(json['filename']).insertAfter(anchor);
                        }
                    }
                
                    setTimeout(function(){
                    data.context.fadeOut('slow');
                    }, 100000);
                });
            },
        
            progress: function(e, data){
                var progress = parseInt(data.loaded / data.total * 100, 10);
            
                data.context.find('.load-indicator').css('width', progress + '%');
            
                if (progress == 100) {
                    data.context.removeClass('working');
                    data.context.find('span.status')
                        .removeClass('icon-time')
                        .removeClass('wait')
                        .addClass('ok')
                        .addClass('icon-check');
                }
            },
        
            fail:function(e, data){
                data.context.addClass('error');
                data.context.find('span.status')
                        .removeClass('icon-time')
                        .removeClass('wait')
                        .addClass('problem')
                        .addClass('icon-remove');
            }
        
        });
    },
    
    setUploadOptions: function(element, options) {
        element.fileupload('option', {
            formData: options
        });
    },
    
    serializeForm: function(form) {
        var elements = form.find('.form-group'),
            result = {
            'id_form': form.find('input[name=form_id]').val(),
            'id_product': parseInt(form.data('formProduct')),
            'captcha': form.find('input[name=captchaText]').val(),
            'elements': {}
            };
        
        if (elements.length) {
            elements.each(function(){
                var elementName = $(this).attr('id');
                
                if (elementName) {
                    var idElement = parseInt(elementName.split('_')[1]),
                        input = $(this).find('*[name=' + $(this).attr('id') + ']');
                        
                    result.elements[elementName] = {
                        'id_element': idElement,
                        'value': ''
                    };
                    
                    switch ($(this).attr('rel')) {
                        case 'textInput':
                        case 'passwordInput':
                        case 'dateInput':
                        result.elements[elementName]['value'] = input.val();
                        break;
                        case 'colorInput':
                        var color = '';
                        
                        if (input.length) {
                            color = '#' + input.spectrum('get').toHex();
                        }
                        
                        result.elements[elementName]['value'] = color;
                        break;
                        case 'fileInput':
                        var fileInput = $(this).find('input[name=' + $(this).attr('id') + '_file]');
                        
                        result.elements[elementName]['value'] = fileInput.length ? fileInput.val() : '';
                        break;
                        case 'textareaInput':
                        result.elements[elementName]['value'] = input.val();
                        break;
                        case 'selectInput':
                        var selectedValue = input.find('option:selected');
                        
                        result.elements[elementName]['value'] = selectedValue.length ? selectedValue.val() : '';
                        break;
                        case 'radioInput':
                        case 'checkboxInput':
                        var selectedValue = $(this).find('input[type=' + ($(this).attr('rel') == 'checkboxInput' ? 'checkbox' : 'radio') + ']:checked'),
                            r;
            
                        if (selectedValue.length) {
                            if ($(this).attr('rel') == 'checkboxInput') {
                                r = [];
                                
                                selectedValue.each(function(){
                                    r.push(parseInt($(this).attr('id').split('_')[1]));
                                });
                                
                                result.elements[elementName]['value'] = r;
                            } else {
                                result.elements[elementName]['value'] = parseInt(selectedValue.attr('id').split('_')[1]);
                            }
                        } else {
                            result.elements[elementName]['value'] = '';
                            break;
                        }
                        
                        break;
                    }
                }
            });
        }
        
        return result;
    },
    
    submitForm: function(form) {
        $('.form-upload-message').remove();
        
        var formData = formmakerfront.serializeForm(form);
        
        $.post(
            formmakerPath,
            {'action': 'submit', 'ajax': 1, 'form': formData, 'pageparam': pageParamFMSend, 'form_id': parseInt(form.data('formId')), 'form_product': parseInt(form.data('formProduct'))},
            function(data){
                if ($('.trigger-captcha').length) {
                    $('.trigger-captcha').trigger('click');
                }
                if (typeof(data.errors) != 'undefined') {
                    for (var i in data.errors) {
                        var error_block = $(document.createElement('div')).addClass('alert alert-danger form-upload-message').html(data.errors[i]);
                        
                        error_block.insertBefore(form);
                        $('html, body').animate({
                            scrollTop: error_block.offset().top
                        }, 1000);
                    }
                } else if (typeof(data.success) != 'undefined') {
                    var success_block = $(document.createElement('div')).addClass('alert alert-success form-upload-message').html(data.success);
                    success_block.insertBefore(form);
                    
                    $('div.uploader > .filename').text(titleFileUploadFM);
                    $('input.filename-container').val('');
                    $('ul.upload-data').empty();

                    if (typeof(data.redirect) != 'undefined' && data.redirect) {
                        window.location.href = data.redirect;
                    } else if (typeof(data.success_redirect) != 'undefined' && data.success_redirect) {
                        window.location.href = data.success_redirect;
                    } else {
                        $('html, body').animate({
                            scrollTop: success_block.offset().top
                        }, 1000);
                    }
                }
            }, 'json'
        );
    }
}

$(function(){
    formmakerfront.init();
});

$(window).load(function() {
    if (typeof($.uniform) != "undefined") {
        $.uniform.restore(".noUniform, .noUniform input, .noUniform select");
    }
});

$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};