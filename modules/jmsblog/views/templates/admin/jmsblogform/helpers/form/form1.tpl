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
						<img src="{$image_baseurl|escape:'html':'UTF-8'}{$fields[0]['form']['images'][$language.id_lang]|escape:'html':'UTF-8'}" class="img-thumbnail" />
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
{if $check_product_association_ajax}
	{assign var=class_input_ajax value='check_product_name '}
{else}
	{assign var=class_input_ajax value=''}
{/if}


	<script type="text/javascript">
		
		var msg_select_one = "{l s='Please select at least one product.' js=1 mod='jmsadvblog'}";
		var msg_set_quantity = "{l s='Please set a quantity to add a product.' js=1 mod='jmsadvblog'}";

		{if isset($ps_force_friendly_product) && $ps_force_friendly_product}
			var ps_force_friendly_product = 1;
		{else}
			var ps_force_friendly_product = 0;
		{/if}
		{if isset($PS_ALLOW_ACCENTED_CHARS_URL) && $PS_ALLOW_ACCENTED_CHARS_URL}
			var PS_ALLOW_ACCENTED_CHARS_URL = 1;
		{else}
			var PS_ALLOW_ACCENTED_CHARS_URL = 0;
		{/if}
		{$combinationImagesJs|escape:'html':'UTF-8'}
		{if $check_product_association_ajax}
				var search_term = '';
				$('document').ready( function() {
					$(".check_product_name")
						.autocomplete(
							'{$link->getAdminLink('AdminProducts', true)|escape:'html':'UTF-8'|addslashes}', {
								minChars: 3,
								max: 10,
								width: $(".check_product_name").width(),
								selectFirst: false,
								scroll: false,
								dataType: "json",
								formatItem: function(data, i, max, value, term) {
									search_term = term;
									// adding the little
									if ($('.ac_results').find('.separation').length == 0)
										$('.ac_results').css('background-color', '#EFEFEF')
											.prepend('<div style="color:#585A69; padding:2px 5px">{l s='Use a product from the list' mod='jmsadvblog'}<div class="separation"></div></div>');
									return value;
								},
								parse: function(data) {
									var mytab = new Array();
									for (var i = 0; i < data.length; i++)
										mytab[mytab.length] = { data: data[i], value: data[i].name };
									return mytab;
								},
								extraParams: {
									ajax: 1,
									action: 'checkProductName',
									id_lang: {$id_lang|escape:'html':'UTF-8'}
								}
							}
						)
						.result(function(event, data, formatted) {
							// keep the searched term in the input
							$('#name_{$id_lang|escape:'html':'UTF-8'}').val(search_term);
							jConfirm('{l s='Do you want to use this product?' mod='jmsadvblog'}&nbsp;<strong>'+data.name+'</strong>', '{l s='Confirmation' mod='jmsadvblog'}', function(confirm){
								if (confirm == true)
									document.location.href = '{$link->getAdminLink('AdminProducts', true)|escape:'html':'UTF-8'}&updateproduct&id_product='+ data.id_product;
								else
									return false;
							});
						});
				});
		{/if}

			var no_related_product = '{l s='No related product' mod='jmsadvblog'}';
			var id_product_redirected = {$product->id_product_redirected|intval};
			var product_name_redirected = '{$product_name_redirected|escape:'html':'UTF-8'}';
	</script>

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
							<input type="text" id="tags_{$language.id_lang|escape:'html':'UTF-8'}" class="tagify updateCurrentText" name="tags_{$language.id_lang|escape:'html':'UTF-8'}" value="{$fields[0]['form']['tags'][$language.id_lang]|escape:'':'UTF-8'}" />
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
	var missing_product_name = '{l s='Please fill product name input field' js=1 mod='jmsadvblog'}';
</script>

{/if}
	{$smarty.block.parent}
{/block}