{**
* Formmaker
*
* @category  Module
* @author    silbersaiten <info@silbersaiten.de>
* @support   silbersaiten <support@silbersaiten.de>
* @copyright 2015 silbersaiten
* @version   1.0.3
* @link      http://www.silbersaiten.de
* @license   See joined file licence.txt
*}

<div class="form-group clearfix">
    <label class="control-label col-lg-3">{l s='Form' mod='formmaker'}</label>
    <div class="col-lg-9">
		{if !$form_list || !form_list|@count}
			<div class="alert alert-warning">{l s='You have to add forms first' mod='formmaker'}</div>
		{else}
			{foreach $languages as $language}
				{if $languages|count > 1}
				<div class="form-group translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}"{if $language.id_lang != $defaultFormLanguage} style="display:none;"{/if}>
					<div class="col-lg-9">
				{/if}
					<select name="form_id_{$language.id_lang|escape:'htmlall':'UTF-8'}">
						{foreach from=$form_list item=form}
						<option value="{$form.id_fm_form|escape:'htmlall':'UTF-8'}">{$form.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				{if $languages|count > 1}
					</div>
					<div class="col-lg-2">
						<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
							{$language.iso_code|escape:'htmlall':'UTF-8'}
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							{foreach from=$languages item=language}
							<li>
								<a href="javascript:hideOtherLanguage({$language.id_lang|escape:'htmlall':'UTF-8'});" tabindex="-1">{$language.name|escape:'htmlall':'UTF-8'}</a>
							</li>
							{/foreach}
						</ul>
					</div>
				</div>
				{/if}
			{/foreach}
		{/if}
    </div>
</div>
