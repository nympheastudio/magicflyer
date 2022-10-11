{**
* Formmaker
*
* @category  Module
* @author    silbersaiten <info@silbersaiten.de>
* @support   silbersaiten <support@silbersaiten.de>
* @copyright 2016 silbersaiten
* @version   1.3.0
* @link      http://www.silbersaiten.de
* @license   See joined file licence.txt
*}
<form method="post" class="form-maker-form container" action="" data-form-id="{$form->id}" data-form-product="{if isset($form_product)}{$form_product}{else}0{/if}">
    <input type="hidden" name="form_id" value="{$form->id}" />
    <div class="form_{$form->id}">
	{if ! isset($product_page) || ! $product_page}
	<h3 class="page-subheading">{$form->name}</h3>
	{/if}
	{if $form->description}
	    <div class="rte">{$form->description nofilter}</div>
	{/if}
	<div class="row">
	{foreach from=$form_data item=form_element}
	    <div class="form-group{if $form_element.required} required{/if}{if $form_element.css_class} {$form_element.css_class}{else} clearer col-xs-12{/if}" rel="{$form_element.type}" id="element_{$form_element.id}">
		{if $form_element.label}<label for="element_{$form_element.id}_{$form_element.type}">{$form_element.label}{if $form_element.required} <sup>*</sup>{/if}</label>{/if}
	    {if $form_element.type == 'textInput'}
		<input type="text" name="element_{$form_element.id}" id="element_{$form_element.id}_{$form_element.type}" class="form-control" />
	    {else if $form_element.type == 'passwordInput'}
		<div class="input-group">
			<input type="password" name="element_{$form_element.id}" id="element_{$form_element.id}_{$form_element.type}" class="form-control" />
			<span class="field-icon">
				<i class="material-icons">lock</i>
			</span>
		</div>
	    {else if $form_element.type == 'dateInput'}
	    <div class="input-group">
			<input type="text" name="element_{$form_element.id}" id="element_{$form_element.id}_{$form_element.type}" class="form-control {if array_key_exists('settings', $form_element) && array_key_exists('time', $form_element['settings']) && $form_element['settings']->time == 1}datetimepicker{else}datepicker{/if}" />
			<span class="field-icon">
				<i class="material-icons">insert_invitation</i>
			</span>
		</div>
	    {else if $form_element.type == 'colorInput'}
		<div class="color-wrapper">
		    <input type="text" name="element_{$form_element.id}" class="form-control colorinput" />
		</div>
	    {else if $form_element.type == 'fileInput'}
		<!-- <input type="file" name="element_{$form_element.id}" class="form-control fileinput" /> -->
        <input type="file" id="element_{$form_element.id}_{$form_element.type}" name="element_{$form_element.id}" class="filestyle fileinput">
		<ul class="upload-data"></ul>
	    {else if $form_element.type == 'textareaInput'}
		<textarea name="element_{$form_element.id}" id="element_{$form_element.id}_{$form_element.type}" class="form-control"></textarea>
	    {else if $form_element.type == 'selectInput'}
		<div class="values-container">
		    <select name="element_{$form_element.id}" id="element_{$form_element.id}_{$form_element.type}" class="form-control">
			{foreach from=$form_element.values item=value name=i}
			<option value="{$value.id}">{$value.name}</option>
			{/foreach}
		    </select>
		</div>
	    {else if $form_element.type == 'radioInput'}
		<div class="values-container">
		    {foreach from=$form_element.values item=value name=i}
		    <div class="radio">
				<span class="custom-radio">
				    <input type="radio" name="element_{$form_element.id}_value" id="value_{$value.id}" />
				    <span></span>
				</span>
			<label for="value_{$value.id}">{$value.name}</label>
		    </div>
		    {/foreach}
		</div>
	    {else if $form_element.type == 'checkboxInput'}
		<div class="values-container">
		    {foreach from=$form_element.values item=value name=i}
		    <div class="checkbox">
				<span class="custom-checkbox">
				    <input type="checkbox" name="element_{$form_element.id}_value" id="value_{$value.id}" />
				    <span><i class="material-icons checkbox-checked">&#xE5CA;</i></span>
				</span>
				<label for="value_{$value.id}">{$value.name}</label>
		    </div>
		    {/foreach}
		</div>
	    {/if}
	    {if $form_element.description}
	    <span class="form_info">{$form_element.description nofilter}</span>
	    {/if}
	    </div>
	{/foreach}
	</div>
	{if isset($captcha_path) && $captcha_path}
	<div class="row">
		<div class="form-group required col-lg-12">
			<label for="captchaText">{l s='Please enter the text you see on the picture' mod='formmaker'}</label>
			<div class="clearer"></div>
			<div class="catcha-wrapper">
				<div class="captcha-container">
					<img class="captcha thumbnail" src="{$captcha_path}?id_form={$form->id|intval}" data-form-id="{$form->id}" />
					<a href="#" class="trigger-captcha" data-form-id="{$form->id|intval}" onclick="console.log($(this).data('formId'), $('.captcha[data-form-id='+$(this).data('formId')+']')); $('.captcha[data-form-id='+$(this).data('formId')+']').attr('src', '{$captcha_path}?id_form='+$(this).data('formId')+'&'+Math.random()); return false;">
						<i class="material-icons">refresh</i>
					</a>
				</div>
				<input type="text" class="form-control" name="captchaText" id="captchaText"/>
			</div>
		</div>
	</div>
	{/if}
	<footer class="form-footer text-xs-right">
		<button type="submit" class="btn btn-primary"><span><i class="material-icons">mail_outline</i> {if $form->submit_button != ''}{$form->submit_button}{else}{l s='Submit' mod='formmaker'}{/if}</span></button>
    </footer>
	
    </div>
</form>
<script>
	window.onhashchange = function () {
		pageParamFMSend['page'] = window.location.href;
	}
	var pageParamFMSend = {
        page: window.location.href,
        {foreach from=$smarty.get key=id item=param}
        {$id}: "{$param}",
        {/foreach}
	};
</script>