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
{if isset($form_data)}
    {capture name=path}
	{$form->name|escape:'htmlall':'UTF-8'}
    {/capture}
    {include file=$form_template}
{/if}