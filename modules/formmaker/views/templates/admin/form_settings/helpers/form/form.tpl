{**
* Formmaker
*
* @category  Module
* @author    silbersaiten <info@silbersaiten.de>
* @support   silbersaiten <support@silbersaiten.de>
* @copyright 2015 silbersaiten
* @version   1.1.0
* @link      http://www.silbersaiten.de
* @license   See joined file licence.txt
*}
{extends file="helpers/form/form.tpl"}

{block name="legend"}
	{$smarty.block.parent}
	
	{if isset($missing_templates) && $missing_templates}
		<div class="alert alert-warning">
			<h4>{l s='Email templates are missing for some languages' mod='formmaker'}</h4>
			<ul>
			{foreach from=$missing_templates item=template}
				<li><strong>{$template.template_name|escape:'htmlall':'UTF-8'}</strong> ({$template.template|escape:'htmlall':'UTF-8'}) {l s='for language' mod='formmaker'} <strong>{$template.language|escape:'htmlall':'UTF-8'}</strong></li>
			{/foreach}
			</ul>
		</div>
	{/if}
{/block}