{**
 * pm_crosssellingoncart
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2017 - http://www.presta-module.com
 * @license   Commercial
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *}

{extends file="helpers/form/form.tpl"}


{block name="field"}
	{if version_compare($ps_version, '1.6.0.0', '<')}
		<div class="margin-form bo-remove-padding-left">
	{else}
		{* {$smarty.block.parent} *}
	{/if}
		{block name="input"}
			{if $input.type == 'advancedstyles'}
				<fieldset id="csoc_advanced_styles_fieldset">
					<div class="dynamicTextarea" style="width:95%;">
						<textarea style="height:150px" rows="5" name="PM_{$module_prefix|escape:'html':'UTF-8'}_ADVANCED_STYLES" id="CSOC_css">{pm_crosssellingoncart::getAdvancedStylesDb()}{* HTML *}</textarea>
					</div>

					<div class="clear"></div>

					<script type="text/javascript">
					   var editorCSOC_css = CodeMirror.fromTextArea(document.getElementById("CSOC_css"), {ldelim}mode: "css", lineNumbers: true, autofocus: true{rdelim});
					</script>
				</fieldset>

			{elseif $input.type == 'html'}
				{$input.html_content}{* HTML *}

			{elseif $input.type == 'switch'}
				{if version_compare($ps_version, '1.6.0.0', '<')}
					<div class="margin-form">
						<span class="switch prestashop-switch fixed-width-lg">
							{foreach $input.values as $value}
								<input type="radio" name="{$input.name|escape:'html':'UTF-8'}"{if $value.value == 1} id="{$input.name|escape:'html':'UTF-8'}_on"{else} id="{$input.name|escape:'html':'UTF-8'}_off"{/if} value="{$value.value|intval}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
								{strip}
									<label {if $value.value == 1} for="{$input.name|escape:'html':'UTF-8'}_on"{else} for="{$input.name|escape:'html':'UTF-8'}_off"{/if}>
										{if $value.value == 1}
											{l s='Yes' mod='pm_crosssellingoncart'}
										{else}
											{l s='No' mod='pm_crosssellingoncart'}
										{/if}
									</label>
								{/strip}
							{/foreach}
							<a class="slide-button btn"></a>
						</span>
					</div>
				{else}
					<div class="col-lg-9">
						<span class="switch prestashop-switch fixed-width-lg">
							{foreach $input.values as $value}
								<input type="radio" name="{$input.name|escape:'html':'UTF-8'}"{if $value.value == 1} id="{$input.name|escape:'html':'UTF-8'}_on"{else} id="{$input.name|escape:'html':'UTF-8'}_off"{/if} value="{$value.value|intval}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
								{strip}
									<label {if $value.value == 1} for="{$input.name|escape:'html':'UTF-8'}_on"{else} for="{$input.name|escape:'html':'UTF-8'}_off"{/if}>
										{if $value.value == 1}
											{l s='Yes' mod='pm_crosssellingoncart'}
										{else}
											{l s='No' mod='pm_crosssellingoncart'}
										{/if}
									</label>
								{/strip}
							{/foreach}
							<a class="slide-button btn"></a>
						</span>
					</div>
				{/if}

			{elseif $input.type == 'produitsimposes'}
				<script>var idShop = {$id_shop|intval}</script>
					<div class="form-group {$input.form_group_class|escape:'html':'UTF-8'}" id="csoc_compulsory_products_form_group">
						<div class="col-lg-5">
							<input type="hidden" name="PM_CSOC_inputProducts" id="PM_CSOC_inputProducts" value="{foreach $input.values as $product}{$product.id|intval}-{/foreach}" />
							<input type="hidden" name="PM_CSOC_nameProducts" id="PM_CSOC_nameProducts" value="{foreach $input.values as $product}{$product.name|escape:'html':'UTF-8'}¤{/foreach}" />
							<div id="ajax_choose_product">
								<div class="input-group">
									<input type="text" id="product_autocomplete_input" name="product_autocomplete_input" placeholder="{l s='Start typing an ID, reference, or product name' mod='pm_crosssellingoncart'}" />
									<span class="input-group-addon"><i class="icon-search"></i></span>
								</div>
							</div>

							<div id="PM_CSOC_divProducts">
								{foreach $input.values as $product}
									<div id="csoc_product_{$product.id|intval}" class="form-control-static">
										<button type="button" class="btn btn-default" name="{$product.id|intval}" onclick="delProduct({$product.id|intval})">
											<i class="icon-remove text-danger"></i>
										</button>
										{if (version_compare($ps_version, '1.7.0.0', '>='))}
											<img src="../img/tmp/product_mini_{$product.id_image|intval}.jpg" />{$product.name|escape:'html':'UTF-8'}
										{else}
											<img src="../img/tmp/product_mini_{$product.id|intval}_{$id_shop|intval}.jpg" />{$product.name|escape:'html':'UTF-8'}
										{/if}
									</div>
								{/foreach}
							</div>
						</div>
					</div>
			{else}
				{$smarty.block.parent}
			{/if}
		{/block}
	{if version_compare($ps_version, '1.6.0.0', '<')}
		</div>
	{/if}
{/block}