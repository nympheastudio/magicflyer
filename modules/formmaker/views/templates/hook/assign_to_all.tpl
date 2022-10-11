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
<span class="switch prestashop-switch fixed-width-lg assign-to-all-wrapper" data-form-id="{$id_fm_form|intval}">
	{strip}
	<input type="radio" class="form-assign-all" name="assign_to_all[{$id_fm_form|intval}]" id="assign_to_all_{$id_fm_form|intval}_on" value="1" {if $selected_form == $id_fm_form}checked="checked"{/if} />
	<label for="assign_to_all_{$id_fm_form|intval}_on" class="radioCheck">
		{l s='Yes' mod='formmaker'}
	</label>
	<input type="radio" class="form-assign-all" name="assign_to_all[{$id_fm_form|intval}]" id="assign_to_all_{$id_fm_form|intval}_off" value="0" {if $selected_form != $id_fm_form}checked="checked"{/if} />
	<label for="assign_to_all_{$id_fm_form|intval}_off" class="radioCheck">
		{l s='No' mod='formmaker'}
	</label>
	{/strip}
	<a class="slide-button btn"></a>
</span>