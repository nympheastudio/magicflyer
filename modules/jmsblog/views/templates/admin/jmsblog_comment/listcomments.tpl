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
<div class="panel">
	<h3><i class="icon-list-ul"></i> {l s='Comments' mod='jmsblog'}
	{if $waiting_total gt 0}
		<div class="pull-right">
		{l s='There is ' mod='jmsblog'} {$waiting_total} {l s='comments waiting approve' mod='jmsblog'} 	
		<a href="{$link->getAdminLink('AdminJmsblogComment')|escape:'html':'UTF-8'}&configure=jmsblog&ApproveAll" class="btn" title="#" >
		<i class="icon-warning"></i> {l s='Approve All' mod='jmsblog'}
		</a>	
		</div>
	{/if}
	</h3>
	<div class="table-responsive-row clearfix">
		<table class="table tableDnD"><tbody id="posts">
			<tr class="heading">
				<th>{l s='ID' mod='jmsblog'}</th>				
				<th>{l s='Name' mod='jmsblog'}</th>
				<th>{l s='Time' mod='jmsblog'}</th>				
				<th>{l s='Comment' mod='jmsblog'}</th>				
				<th class="right">{l s='Action' mod='jmsblog'}</th>
			</tr>
			{foreach from=$items key=i item=comment}
				<tr id="posts_{$comment.comment_id|escape:'html':'UTF-8'}" class="{if $i%2 == 1}odd{/if}">					
					<td class="row-id">
						{$comment.comment_id|escape:'html':'UTF-8'} 
					</td>					
					<td class="name">
						<h4 class="pull-left">{$comment.customer_name|escape:'html':'UTF-8'}</h4>
					</td>
					<td class="time">
						<h4 class="pull-left">{$comment.time_add|escape:'html':'UTF-8'}</h4>
					</td>
					<td class="comment">
						{$comment.comment|escape:'html':'UTF-8'}
					</td>					
					<td>
						<div class="btn-group-action pull-right">
							{if $comment.status == -2}
								<a href="{$link->getAdminLink('AdminJmsblogComment')|escape:'html':'UTF-8'}&configure=jmsblog&status_id_comment={$comment.comment_id|escape:'html':'UTF-8'}&Approve" class="btn btn-warning" title="#" >
									<i class="icon-warning"></i> {if $comment.status == -2}{l s='Approve' mod='jmsblog'}{/if}
								</a>
							{else}
								<a class="btn {if $comment.status == 1}btn-success{else}btn-danger{/if}"	href="{$link->getAdminLink('AdminJmsblogComment')|escape:'html':'UTF-8'}&configure=jmsblog&status_id_comment={$comment.comment_id|escape:'html':'UTF-8'}&changeCommentStatus" title="{if $comment.status == 1}Enabled{else}Disabled{/if}">
								<i class="{if $comment.status == 1}icon-check{else}icon-remove{/if}"></i>{if $comment.status == 1}Enabled{else}Disabled{/if}
								</a>
							{/if}
							<a class="btn btn-default"
									href="{$link->getAdminLink('AdminJmsblogComment')|escape:'html':'UTF-8'}&configure=jmsblog&delete_id_comment={$comment.comment_id|escape:'html':'UTF-8'}" onclick="return confirm('Are you sure you want to delete this item?');">
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