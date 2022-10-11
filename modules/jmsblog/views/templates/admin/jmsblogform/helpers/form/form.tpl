{*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/form/form.tpl"}
{block name="field"}
	{if $input.type == 'file_lang'}
		<div class="row">
			{foreach from=$languages item=language}
				{if $languages|count > 1}
					<div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
				{/if}
					<div class="col-lg-6">
						{if $input.name=='image' && isset($fields[0]['form']['images'])}
						<img src="{$image_baseurl|escape:'html':'UTF-8'}thumb_{$fields[0]['form']['images'][$language.id_lang]|escape:'html':'UTF-8'}" class="img-thumbnail" />
						<input type="hidden" name="image_old_{$language.id_lang|escape:'html':'UTF-8'}" value="{$fields[0]['form']['images'][$language.id_lang]|escape:'html':'UTF-8'}" />
						{/if}
												
						<input id="{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}" type="file" name="{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}" class="hide" />
						<div class="dummyfile input-group">
							<span class="input-group-addon"><i class="icon-file"></i></span>
							<input id="{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}-name" type="text" class="disabled" name="filename" readonly />
							<span class="input-group-btn">
								<button id="{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
									<i class="icon-folder-open"></i> {l s='Choose a file' mod='jmsadvblog'}
								</button>
							</span>
						</div>
					</div>
				{if $languages|count > 1}
					<div class="col-lg-2">
						<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
							{$language.iso_code|escape:'html':'UTF-8'}
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							{foreach from=$languages item=lang}
							<li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'html':'UTF-8'});" tabindex="-1">{$lang.name|escape:'html':'UTF-8'}</a></li>
							{/foreach}
						</ul>
					</div>
				{/if}
				{if $languages|count > 1}
					</div>
				{/if}
				<script>
				$(document).ready(function(){
					$('#{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}-selectbutton').click(function(e){
						$('#{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}').trigger('click');
					});
					$('#{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}').change(function(e){
						var val = $(this).val();
						var file = val.split(/[\\/]/);
						$('#{$input.name|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}-name').val(file[file.length-1]);
					});
				});
			</script>
			{/foreach}
		</div>
	{/if}
{if $input.type == 'file_tags'}			
			<div class="col-lg-9">
				{if $languages|count > 1}
				<div class="row">
				{/if}
					{foreach from=$languages item=language}
						{literal}
						<script type="text/javascript">
							$().ready(function () {
								var input_id = '{/literal}tags_{$language.id_lang|escape:'html':'UTF-8'}{literal}';
								$('#'+input_id).tagify({delimiters: [13,44], addTagPrompt: '{/literal}{l s='Add tag' js=1}{literal}'});
								$({/literal}'#{$table|escape:'html':'UTF-8'}{literal}_form').submit( function() {
									$(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
								});
							});
						</script>
						{/literal}
					{if $languages|count > 1}
					<div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" >
						<div class="col-lg-9">
					{/if}
							{if isset($fields[0]['form']['tags'][$language.id_lang])}
							<input type="text" id="tags_{$language.id_lang|escape:'html':'UTF-8'}" class="tagify updateCurrentText" name="tags_{$language.id_lang|escape:'html':'UTF-8'}" value="{$fields[0]['form']['tags'][$language.id_lang]|escape:'':'UTF-8'}" />
							{else}
							<input type="text" id="tags_{$language.id_lang|escape:'html':'UTF-8'}" class="tagify updateCurrentText" name="tags_{$language.id_lang|escape:'html':'UTF-8'}" value="" />
							{/if}
					{if $languages|count > 1}
						</div>
						<div class="col-lg-2">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								{$language.iso_code|escape:'html':'UTF-8'}
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								{foreach from=$languages item=language}
								<li>
									<a href="javascript:hideOtherLanguage({$language.id_lang|escape:'html':'UTF-8'});">{$language.name|escape:'html':'UTF-8'}</a>
								</li>
								{/foreach}
							</ul>
						</div>
					</div>
					{/if}
					{/foreach}
				{if $languages|count > 1}
				</div>
				{/if}
			</div>
		
		
<script type="text/javascript">
	hideOtherLanguage({1|escape:'html':'UTF-8'});	
</script>

{/if}
	{$smarty.block.parent}
{/block}