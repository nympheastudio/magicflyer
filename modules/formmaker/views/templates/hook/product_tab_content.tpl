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
{* <!-- For Tabs-->
<div class="rte" id="form_formmaker">
	{include file=$form_path product_page=true}
</div>
*}
<h3 class="page-product-heading">{$form->name|escape:'htmlall':'UTF-8'}</h3>
<div class="rte">
    {include file=$form_path product_page=true}
</div>