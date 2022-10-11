{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="form-group" id="messreplace">

<div class="col-lg-9">
 <input id="{$id|escape:'html':'UTF-8'}" type="file" name="{$name|escape:'html':'UTF-8'}"{if isset($url)} data-url="{$url|escape:'html':'UTF-8'}"{/if} class="hide" />
    <button class="btn btn-default" data-style="expand-right" data-size="s" type="button" id="{$id|escape:'html':'UTF-8'}-add-button">
		<i class="icon-plus-sign" ></i> {l s='Add file' mod='messageattachment' }
	</button>
    <div>{$messageattach['error']|escape:'html':'UTF-8'}</div>
</div>
    <div class="col-lg-9" id="visualize">
        {foreach from=$list_attachment_render key=k  item=idAttachment}
            {if $idAttachment|escape:'htmlall':'UTF-8'=='logoreplace.png'}
                <img  data-url="{$linkattach|escape:'html':'UTF-8'}&ajax=1&id_attachment={$k|intval}" class="img-thumbnail" style="display:none;" id="{$k|intval}" src="../upload/{$idAttachment|escape:'htmlall':'UTF-8'}" onload="var ref=100;var ratio = ref / Number(this.width); var ih = Number(this.height); var h = ih * ratio;this.width = ref;this.height = h;$('#' + this.id).show();"> <input type="checkbox" id="delete{$k|intval}" onclick="deleteIS({$k|intval})"/>
            {else}
            <a class="fancybox" href="../upload/{$idAttachment|escape:'htmlall':'UTF-8'}"><img  data-url="{$linkattach|escape:'html':'UTF-8'}&ajax=1&id_attachment={$k|intval}" class="img-thumbnail" style="display:none;" id="{$k|intval}" src="../upload/{$idAttachment|escape:'htmlall':'UTF-8'}" onload="var ref=100;var ratio = ref / Number(this.width); var ih = Number(this.height); var h = ih * ratio;this.width = ref;this.height = h;$('#' + this.id).show();"></a><input type="checkbox" id="delete{$k|intval}" onclick="deleteIS({$k|intval})"/>
            {/if}
        {/foreach}
    </div>
</div>
<script type="text/javascript">
var success_message='{$messageattach['ok']|escape:'html':'UTF-8'}';
$(".fancybox").fancybox({
            'hideOnContentClick': true,
            'padding': 0,
            'overlayColor': '#D3D3D3',
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'overlayOpacity': 0.7,
            'autoDimensions': true,
            'centerOnScroll': true
        }
);

function deleteIS(id) {
    $.ajax({
        type: "GET",
        url: $('#'+id).attr('data-url'),
        success: function (msg) {
            if (eval(msg) == true) {
                $('#delete' + id).hide();
                $('#' + id).hide();
                showSuccessMessage('{$messageattach['delete']|escape:'html':'UTF-8'}');
            }
        }
    });
}
function humanizeSize(bytes) {
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
}

$(document).ready(function () {

    var {$id|escape:'html':'UTF-8'}_add_button = Ladda.create(document.querySelector('#{$id|escape:'html':'UTF-8'}-add-button'));
    var {$id|escape:'html':'UTF-8'}_total_files = 0;


    $('#{$id|escape:'html':'UTF-8'}').fileupload({
        dataType: 'json',
        autoUpload: true,
        singleFileUploads: true,
        maxFileSize: {$post_max_size|intval},
        success: function (e) {
            //showSuccessMessage(success_message);
        },
        start: function (e) {
            {$id|escape:'html':'UTF-8'}_add_button.start();
        },
        fail: function (e, data) {

            showErrorMessage(data.errorThrown.message);
        },
        done: function (e, data) {
            if (data.result) {
                if (typeof data.result.attachment_file !== 'undefined') {
                    if (typeof data.result.attachment_file.error !== 'undefined' && data.result.attachment_file.error.length > 0)
                        $.each(data.result.attachment_file.error, function (index, error) {
                            if (error==1)
                            {
                                showErrorMessage(data.result.attachment_file.name + ' : {$messageattach['error']|escape:'html':'UTF-8'}');
                            }
                            else {
                                showErrorMessage(data.result.attachment_file.name + ' : ' + error);
                            }
                        });
                    else {
                        showSuccessMessage(success_message);
                        var extention=data.result.attachment_file.name.split('.');

                        if (extention[1]=='png' || extention[1]=='jpg' || extention[1]=='gif' || extention[1]=='xml') {
                            $('#visualize').append('<a class="fancybox" href="../upload/' + data.result.attachment_file.tmpname + '"><img  data-url="{$linkattach|escape:'html':'UTF-8'}&ajax=1&id_attachment=' + data.result.attachment_file.id + '" class="img-thumbnail" style="display:none;" id="' + data.result.attachment_file.id + '" src="../upload/' + data.result.attachment_file.tmpname + '" onload="var ref=100;var ratio = ref / Number(this.width); var ih = Number(this.height); var h = ih * ratio;this.width = ref;this.height = h;$(\'#\' + this.id).show();""></a><input type="checkbox" id="delete' + data.result.attachment_file.id + '" onclick="deleteIS(' + data.result.attachment_file.id + ')"/>');
                        }
                        else
                        {
                            $('#visualize').append('<img  data-url="{$linkattach|escape:'html':'UTF-8'}&ajax=1&id_attachment=' + data.result.attachment_file.id + '" class="img-thumbnail" style="display:none;" id="' + data.result.attachment_file.id + '" src="../upload/logoreplace.png" onload="var ref=100;var ratio = ref / Number(this.width); var ih = Number(this.height); var h = ih * ratio;this.width = ref;this.height = h;$(\'#\' + this.id).show();""><input type="checkbox" id="delete' + data.result.attachment_file.id + '" onclick="deleteIS(' + data.result.attachment_file.id + ')"/>');
                        }
                    }
                }
            }
        },
    }).on('fileuploadalways', function (e, data) {
        {$id|escape:'html':'UTF-8'}_add_button.stop();
    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index, file = data.files[index];
        // if (file.error)
        //$('#{$id|escape:'html':'UTF-8'}-errors').append('<div class="row"><strong>'+file.name+'</strong> ('+humanizeSize(file.size)+') : '+file.error+'</div>').show();
    }).on('fileuploadsubmit', function (e, data) {
        var params = new Object();

        $('input[id^="attachment_name_"]').each(function () {
            id = $(this).prop("id").replace("attachment_name_", "attachment_name[") + "]";
            params[id] = $(this).val();
        });

        $('textarea[id^="attachment_description_"]').each(function () {
            id = $(this).prop("id").replace("attachment_description_", "attachment_description[") + "]";
            params[id] = $(this).val();
        });


        data.formData = params;
    });

    $('#{$id|escape:'html':'UTF-8'}-add-button').on('click', function () {
        //$('#{$id|escape:'html':'UTF-8'}-success').hide();
        //$('#{$id|escape:'html':'UTF-8'}-errors').html('').hide();
        {$id|escape:'html':'UTF-8'}_total_files = 0;
        $('#{$id|escape:'html':'UTF-8'}').trigger('click');
    });

    if ($('#messages #message #messreplace').length==0)
    {
        var a=0;
        $.each($('#messages #message .form-group'), function(key, value)
        {
            if (a==0) {
                $(this).before($('#messreplace'));
            }
            a++;
        });

    }

});

</script>