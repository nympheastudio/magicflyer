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
{if $product}
<tr>
    <td colspan="2" style="border:1px solid #D6D4D4;">{l s='Product name:' mod='formmaker'} "{$product->name|escape:'htmlall':'UTF-8'}"</td>
</tr>
{/if}
{foreach from=$form_data item=form_element}
<tr>
    <td style="border:1px solid #D6D4D4; vertical-align: top">
		<table class="table">
		    <tr>
				<td width="10">&nbsp;</td>
				<td>
				    <font size="2" face="Open-sans, sans-serif" color="#555454">
					{$form_element['field']|escape:'htmlall':'UTF-8'}
				    </font>
				</td>
				<td width="10">&nbsp;</td>
		    </tr>
		</table>
    </td>
    <td style="border:1px solid #D6D4D4;">
		<table class="table">
		    <tr>
				<td width="10">&nbsp;</td>
				<td>
				    {if $form_element['type'] == 'fileInput' && !empty($form_element['value'])}
					<a href="{$form_element['value']|escape:'quotes':'UTF-8'}">{l s='Click to download' mod='formmaker'}</a>
				    {else if $form_element['type'] == 'selectInput' || $form_element['type'] == 'radioInput' || $form_element['type'] == 'checkboxInput'}
					{foreach from=$form_element['value'] item=value}
					<font size="2" face="Open-sans, sans-serif" color="#555454">
					    <strong>{$value|escape:'htmlall':'UTF-8'}</strong>
					</font><br />
					{/foreach}
				    {else}
					<font size="2" face="Open-sans, sans-serif" color="#555454">
					    <strong>{nl2br($form_element['value'])|escape:'htmlall':'UTF-8'}</strong>
					</font>
				    {/if}
				</td>
				<td width="10">&nbsp;</td>
		    </tr>
		</table>
    </td>
</tr>
{/foreach}