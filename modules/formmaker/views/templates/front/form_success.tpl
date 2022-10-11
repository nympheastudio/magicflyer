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
{capture name=path}
{$form->name|escape:'htmlall':'UTF-8'}
{/capture}
<div class="row">
	<div class="col-lg-12">
		{if $form->message_on_completed}
			{$form->message_on_completed|escape:'UTF-8'}
		{else}
			<h3>{l s='Thank you!' mod='formmaker'}</h3>
			<p>{l s='You have successfully submitted the form.' mod='formmaker'}</p>
		{/if}
		<div class="buttons"><a class="btn btn-default button button-medium" href="{$base_dir|escape:'quotes':'UTF-8'}" title="{l s='Home' mod='formmaker'}"><span><i class="icon-chevron-left left"></i>{l s='Home page' mod='formmaker'}</span></a></div>
	</div>
</div>