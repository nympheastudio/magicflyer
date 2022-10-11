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
<div class="panel" id="blocklist">
	{if $id_row}
	<h3><i class="icon-list-ul"></i> {l s='Position List' mod='jmsthemelayout'}
	</h3>
	<div id="slidesContent">
		<div class="rows positions">
			{foreach from=$positions item=position}
				<div id="positions_{$position.id_position|escape:'html':'UTF-8'}" class="panel">
					<div class="row">
						<div class="col-lg-1">
							<span><i class="icon-arrows"></i></span>
						</div>						
						<div class="col-md-11">
							<h4 class="pull-left">#{$position.id_position|escape:'html':'UTF-8'} - {$position.title|escape:'html':'UTF-8'}</h4>
							<div class="btn-group-action pull-right">
								<a class="btn {if $position.active}btn-success{else}btn-danger{/if}"	href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&changePositionStatus&id_prof={$id_prof|escape:'html':'UTF-8'}&id_row={$id_row|escape:'html':'UTF-8'}&id_position={$position.id_position|escape:'html':'UTF-8'}" title="{if $position.active}Enabled{else}Disabled{/if}">
									<i class="{if $position.active}icon-check{else}icon-remove{/if}"></i>{if $position.active}Enabled{else}Disabled{/if}
								</a>								
								<a class="btn btn-default"									href="{$adminlink|escape:'htmlall':'UTF-8'}&configure=jmsthemelayout&id_prof={$id_prof|escape:''}&edit_position&id_row={$id_row|escape:'html':'UTF-8'}&id_position={$position.id_position|escape:'html':'UTF-8'}">
									<i class="icon-edit"></i>
									{l s='Edit' mod='jmsthemelayout'}
								</a>
								<a class="btn btn-default"
									href="{$adminlink|escape:'html':'UTF-8'}&configure=jmsthemelayout&id_prof={$id_prof|escape:''}&id_row={$id_row|escape:'html':'UTF-8'}&delete_id_position={$position.id_position|escape:'html':'UTF-8'}">
									<i class="icon-trash"></i>
									{l s='Delete' mod='jmsthemelayout'}
								</a>
							</div>
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
	{else}
	{l s='Please Save to create Row First then you still can add Position' mod='jmsthemelayout'}
	{/if}
</div>