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
<input type="hidden" name="inputFormProducts" id="inputFormProducts" value="{foreach from=$form_products item=form_product}{$form_product.id_product|escape:'htmlall':'UTF-8'}-{/foreach}" />
<input type="hidden" name="nameFormProducts" id="nameFormProducts" value="{foreach from=$form_products item=form_product}{$form_product.name|escape:'html':'UTF-8'}¤{/foreach}" />
<div id="ajax_choose_product">
    <div class="input-group">
	<input type="text" id="product_form_products_input" name="product_form_products_input" />
	<span class="input-group-addon"><i class="icon-search"></i></span>
    </div>
</div>

<div id="divFormProducts">
{foreach from=$form_products item=form_product}
<div class="form-control-static row">
    <div class="col-lg-1">
	<button type="button" class="btn btn-default delFormProduct" name="{$form_product.id_product|escape:'htmlall':'UTF-8'}">
	    <i class="icon-remove text-danger"></i>
	</button>
    </div>
    <div class="col-lg-11">
	<span>{$form_product.name|escape:'html':'UTF-8'}{if !empty($form_product.reference)}&nbsp;{l s='(ref: %s)' sprintf=$form_product.reference mod='formmaker'}{/if}</span>
    </div>
</div>
{/foreach}
</div>