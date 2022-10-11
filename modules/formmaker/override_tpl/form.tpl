{**
* Formmaker
*
* @category  Module
* @author    silbersaiten <info@silbersaiten.de>
* @support   silbersaiten <support@silbersaiten.de>
* @copyright 2016 silbersaiten
* @version   1.2.7
* @link      http://www.silbersaiten.de
* @license   See joined file licence.txt
*}
<form method="post" class="form-maker-form row" action="" data-form-id="{$form->id|escape:'htmlall':'UTF-8'}" data-form-product="{if isset($form_product)}{$form_product|escape:'htmlall':'UTF-8'}{else}0{/if}">
    <input type="hidden" name="form_id" value="{$form->id|escape:'htmlall':'UTF-8'}" />
    <div class="form_{$form->id|escape:'htmlall':'UTF-8'} ">

	<div class="col-sm-6 col-xs-12">
	
	{if $form->description}
	    <div class="rte">{$form->description|escape:'UTF-8'}</div>
	{/if}
	</div>




	<div class="col-sm-6 col-xs-12">
	{if ! isset($product_page) || ! $product_page}
	<h1 class="page-subheading">{$form->name|escape:'htmlall':'UTF-8'}</h1>
	{/if}
	<div class="row">
	{foreach from=$form_data item=form_element}
	    <div class="form-group{if $form_element.required} required{/if}{if $form_element.css_class} {$form_element.css_class|escape:'htmlall':'UTF-8'}{else} clearer col-xs-12{/if}" rel="{$form_element.type|escape:'htmlall':'UTF-8'}" id="element_{$form_element.id|escape:'htmlall':'UTF-8'}">
		{if $form_element.label}<label for="element_{$form_element.id|escape:'htmlall':'UTF-8'}_{$form_element.type|escape:'htmlall':'UTF-8'}">{$form_element.label|escape:'htmlall':'UTF-8'}{if $form_element.required} <sup>*</sup>{/if}</label>{/if}
	    {if $form_element.type == 'textInput'}
		<input type="text" name="element_{$form_element.id|escape:'htmlall':'UTF-8'}" id="element_{$form_element.id|escape:'htmlall':'UTF-8'}_{$form_element.type|escape:'htmlall':'UTF-8'}" class="form-control" placeholder="{$form_element.label|escape:'htmlall':'UTF-8'}{if $form_element.required} *{/if}" />
	    {else if $form_element.type == 'passwordInput'}
		<div class="input-group">
			<input type="password" name="element_{$form_element.id|escape:'htmlall':'UTF-8'}" id="element_{$form_element.id|escape:'htmlall':'UTF-8'}_{$form_element.type|escape:'htmlall':'UTF-8'}" class="form-control" placeholder="{$form_element.label|escape:'htmlall':'UTF-8'}{if $form_element.required} *{/if}" />
			<span class="input-group-addon"><i class="icon-lock"></i></span>
		</div>
	    {else if $form_element.type == 'dateInput'}
	    <div class="input-group">
			<input type="text" name="element_{$form_element.id|escape:'htmlall':'UTF-8'}" id="element_{$form_element.id|escape:'htmlall':'UTF-8'}_{$form_element.type|escape:'htmlall':'UTF-8'}" class="form-control {if array_key_exists('settings', $form_element) && array_key_exists('time', $form_element['settings']) && $form_element['settings']->time == 1}datetimepicker{else}datepicker{/if}"  />
			<span class="input-group-addon"><i class="icon-calendar"></i></span>
		</div>
	    {else if $form_element.type == 'colorInput'}
		<div class="color-wrapper">
		    <input type="text" name="element_{$form_element.id|escape:'htmlall':'UTF-8'}" class="form-control colorinput" />
		</div>
	    {else if $form_element.type == 'fileInput'}
		<input type="file" name="element_{$form_element.id|escape:'htmlall':'UTF-8'}" id="element_{$form_element.id|escape:'htmlall':'UTF-8'}_{$form_element.type|escape:'htmlall':'UTF-8'}" class="form-control fileinput" />
		<ul class="upload-data"></ul>
	    {else if $form_element.type == 'textareaInput'}
		<textarea name="element_{$form_element.id|escape:'htmlall':'UTF-8'}" id="element_{$form_element.id|escape:'htmlall':'UTF-8'}_{$form_element.type|escape:'htmlall':'UTF-8'}" class="form-control" placeholder="{$form_element.label|escape:'htmlall':'UTF-8'}{if $form_element.required} *{/if}"></textarea>
	    {else if $form_element.type == 'selectInput'}
		<div class="values-container">
		    <select name="element_{$form_element.id|escape:'htmlall':'UTF-8'}" id="element_{$form_element.id|escape:'htmlall':'UTF-8'}_{$form_element.type|escape:'htmlall':'UTF-8'}" class="form-control">
			{foreach from=$form_element.values item=value name=i}
			<option value="{$value.id|escape:'htmlall':'UTF-8'}">{$value.name|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
		    </select>
		</div>
	    {else if $form_element.type == 'radioInput'}
		<div class="values-container">
		    {foreach from=$form_element.values item=value name=i}
		    <div class="radio">
			<span>
			    <input type="radio" name="element_{$form_element.id|escape:'htmlall':'UTF-8'}_value" id="value_{$value.id|escape:'htmlall':'UTF-8'}" />
			</span>
			<label for="value_{$value.id|escape:'htmlall':'UTF-8'}">{$value.name|escape:'htmlall':'UTF-8'}</label>
		    </div>
		    {/foreach}
		</div>
	    {else if $form_element.type == 'checkboxInput'}
		<div class="values-container">
		    {foreach from=$form_element.values item=value name=i}
		    <div class="checkbox">
			<span>
			    <input type="checkbox" name="element_{$form_element.id|escape:'htmlall':'UTF-8'}_value" id="value_{$value.id|escape:'htmlall':'UTF-8'}" />
			</span>
			<label for="value_{$value.id|escape:'htmlall':'UTF-8'}">{$value.name|escape:'htmlall':'UTF-8'}</label>
		    </div>
		    {/foreach}
		</div>
	    {/if}
	    {if $form_element.description}
	    <span class="form_info">{$form_element.description|escape:'UTF-8'}</span>
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
					<img class="captcha thumbnail" src="{$captcha_path|escape:'htmlall':'UTF-8'}?id_form={$form->id|intval}" data-form-id="{$form->id|escape:'htmlall':'UTF-8'}" />
					<a href="#" class="trigger-captcha" data-form-id="{$form->id|intval}" onclick="console.log($(this).data('formId'), $('.captcha[data-form-id='+$(this).data('formId')+']')); $('.captcha[data-form-id='+$(this).data('formId')+']').attr('src', '{$captcha_path|escape:'htmlall':'UTF-8'}?id_form='+$(this).data('formId')+'&'+Math.random()); return false;">
						<i class="icon icon-refresh"></i>
					</a>
				</div>
				<input type="text" class="form-control" name="captchaText" id="captchaText"/>
			</div>
		</div>
	</div>
	{/if}
	<button type="submit" class="btn btn-default button button-medium"><span><i class="icon-envelope"></i> {if $form->submit_button != ''}{$form->submit_button|escape:'htmlall':'UTF-8'}{else}{l s='Submit' mod='formmaker'}{/if}</span></button>
	</div>
   <!--
    <div class="col-sm-6 col-xs-12 desktop">
	
	{if $form->description}
	    <div class="rte">{$form->description|escape:'UTF-8'}</div>
	{/if}
	</div>
   
   
   -->
   
   
   
    </div>
</form>
<script>
	window.onhashchange = function () {
		pageParamFMSend['page'] = window.location.href;
	}
	var pageParamFMSend = {
        page: window.location.href,
        {foreach from=$smarty.get key=id item=param}
        {$id|escape:'html':'UTF-8'}: "{$param|escape:'html':'UTF-8'}",
        {/foreach}
	};
</script>