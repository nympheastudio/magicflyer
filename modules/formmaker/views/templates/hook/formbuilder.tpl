{**
* Formmaker
*
* @category  Module
* @author    silbersaiten <info@silbersaiten.de>
* @support   silbersaiten <support@silbersaiten.de>
* @copyright 2016 silbersaiten
* @version   1.1.1
* @link      http://www.silbersaiten.de
* @license   See joined file licence.txt
*}
<div id="form_builder_wrapper" class="row">
    <script type="text/javascript">
	{if isset($post_fields)}
	var postFields = {$post_fields|escape:'quotes':'UTF-8'};
	{/if}
	var languages = {$languages|escape:'quotes':'UTF-8'};
	var currentLanguage = {$current_language|escape:'htmlall':'UTF-8'};
	var defaultLanguage = {$default_language|intval};
	var validationMethods = [];
	{foreach from=$validation_methods key=validation_method item=data}
		validationMethods.push({ldelim}method: "{$validation_method|escape:'htmlall':'UTF-8'}", name: "{$data.name|escape:'htmlall':'UTF-8'}"{rdelim});
	{/foreach}
    </script>
	<div id="elementMenuContainer">
		<ul class="nav col-lg-12 btn-group" role="group" id="form_elements">
			<li class="first btn btn-primary" rel="htmlBlock" >{l s='HTML Block' mod='formmaker'}</a>
			<li class="first btn btn-default" rel="textInput">{l s='Text Input' mod='formmaker'}</li>
			<li class="btn btn-default" rel="passwordInput">{l s='Password Input' mod='formmaker'}</li>
			<li class="btn btn-default" rel="dateInput">{l s='Datepicker' mod='formmaker'}</li>
			<li class="btn btn-default" rel="colorInput">{l s='Color Picker' mod='formmaker'}</li>
			<li class="btn btn-default" rel="fileInput">{l s='File Upload' mod='formmaker'}</li>
			<li class="btn btn-default" rel="textareaInput">{l s='Textarea' mod='formmaker'}</li>
			<li class="btn btn-default" rel="selectInput">{l s='Select' mod='formmaker'}</li>
			<li class="btn btn-default" rel="radioInput">{l s='Radio Group' mod='formmaker'}</li>
			<li class="last btn btn-default" rel="checkboxInput">{l s='Checkbox Group' mod='formmaker'}</li>
		</ul>
	</div>
	<div class="form-elements-wrapper">
		<div class="col-lg-12">
			<ul id="form_maker"></ul>
		</div>
	</div>
</div>
