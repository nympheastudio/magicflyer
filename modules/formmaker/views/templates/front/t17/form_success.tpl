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
{capture name=path}
{$form->name}
{/capture}
<div class="row">
	<div class="col-lg-12">
		{if $form->message_on_completed}
			{$form->message_on_completed nofilter}
		{else}
			<h3>{l s='Thank you!' mod='formmaker'}</h3>
			<p>{l s='You have successfully submitted the form.' mod='formmaker'}</p>
		{/if}
		<div class="buttons">
			<a class="btn btn-primary" href="{$base_dir}" title="{l s='Home' mod='formmaker'}">
				<span><i class="material-icons">keyboard_arrow_left</i>{l s='Home page' mod='formmaker'} </span>
			</a>
		</div>
	</div>
</div>
{/block}