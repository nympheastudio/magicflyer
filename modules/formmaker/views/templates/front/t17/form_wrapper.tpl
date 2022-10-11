{**
* Formmaker
*
* @category  Module
* @author    silbersaiten <info@silbersaiten.de>
* @support   silbersaiten <support@silbersaiten.de>
* @copyright 2016 silbersaiten
* @version   1.3.0
* @link      http://www.silbersaiten.de
* @license   See joined file licence.txt
*}
{extends file='page.tpl'}

{block name='page_content'}
{if isset($form_data)}
    {capture name=path}
	{$form->name}
    {/capture}
    {include file='module:formmaker/views/templates/front/t17/form.tpl'}
{/if}
{/block}