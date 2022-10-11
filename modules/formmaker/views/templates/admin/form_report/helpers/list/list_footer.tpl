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
{extends file="helpers/list/list_footer.tpl"}

{block name="footer"}
	<div class="panel-footer" id="viewReportsButton">
		<a href="index.php?controller=AdminFormReport&amp;token={getAdminToken tab='AdminFormReport'}&export" class="btn btn-default pull-right"><i class="process-icon-export"></i> {l s='Export' mod='formmaker'}</a>
		{if isset($down_export)}
		<a href="index.php?controller=AdminFormReport&amp;token={getAdminToken tab='AdminFormReport'}&download={$down_export|escape:'htmlall':'UTF-8'}" class="btn btn-default pull-right"><i class="process-icon-download"></i> {l s='Download' mod='formmaker'}</a>
		{/if}
	</div>
	{$smarty.block.parent}
{/block}