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
		<a href="index.php?controller=AdminFormReport&amp;token={getAdminToken tab='AdminFormReport'}" class="btn btn-default pull-right"><i class="process-icon-eye icon-eye"></i> {l s='View Reports' mod='formmaker'}</a>
	</div>
	{$smarty.block.parent}
{/block}