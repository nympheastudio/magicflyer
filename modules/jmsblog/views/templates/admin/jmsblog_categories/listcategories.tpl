{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="panel"><h3><i class="icon-list-ul"></i> {l s='Categories' mod='jmsblog'}
	<span class="panel-heading-action">
		<a class="list-toolbar-btn" href="{$link->getAdminLink('AdminJmsblogCategories')|escape:'html':'UTF-8'}&configure=jmsblog&addCategory=1">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Add new" data-html="true">
				<i class="process-icon-new "></i>
			</span>
		</a>
	</span>
	</h3>	
	<div class="table-responsive-row clearfix">
		<table class="table tableDnD"><tbody id="categories">
			<tr class="heading">
				<th>{l s='ID' mod='jmsblog'}</th>
				<th>{l s='Category Title' mod='jmsblog'}</th>
				<th></th>
				<th class="right">{l s='Action' mod='jmsblog'}</th>
			</tr>
			{foreach from=$items key=i item=category}
				<tr id="categories_{$category.category_id|escape:'html':'UTF-8'}" class="{if $i%2 == 1}odd{/if}">					
					<td class="row-id">
						{$category.category_id|escape:'html':'UTF-8'} 
					</td>
					<td class="title">
						<h4 class="pull-left">{$category.title|escape:'html':'UTF-8'}</h4>
					</td>
					<td>
						<span><i class="icon-arrows "></i></span>
					</td>
					<td>
						<div class="btn-group-action pull-right">
							<a class="btn {if $category.active}btn-success{else}btn-danger{/if}"	href="{$link->getAdminLink('AdminJmsblogCategories')|escape:'html':'UTF-8'}&configure=jmsblog&status_id_category={$category.category_id|escape:'html':'UTF-8'}&changeCategoryStatus" title="{if $category.active}Enabled{else}Disabled{/if}">
								<i class="{if $category.active}icon-check{else}icon-remove{/if}"></i>{if $category.active}Enabled{else}Disabled{/if}
							</a>
							<a class="btn btn-default"									href="{$link->getAdminLink('AdminJmsblogCategories')|escape:'html':'UTF-8'}&configure=jmsblog&id_category={$category.category_id|escape:'html':'UTF-8'}">
								<i class="icon-edit"></i>
								{l s='Edit' mod='jmsblog'}
							</a>								
							<a class="btn btn-default"
									href="{$link->getAdminLink('AdminJmsblogCategories')|escape:'html':'UTF-8'}&configure=jmsblog&delete_id_category={$category.category_id|escape:'html':'UTF-8'}" onclick="return confirm('Are you sure you want to delete this item?');">
								<i class="icon-trash"></i>
								{l s='Delete' mod='jmsblog'}
							</a>
						</div>
					</td>
				</tr>				
			{/foreach}
		</tbody></table>
	</div>		
</div>