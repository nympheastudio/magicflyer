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
<div class="panel"><h3><i class="icon-list-ul"></i> {l s='HomePages List' mod='jmsthemelayout'}
	<span class="panel-heading-action">
		<a class="list-toolbar-btn" href="{$link->getAdminLink('AdminJmsthemelayoutHomepage')|escape:'html':'UTF-8'}&configure=jmsthemelayout&addHome=1">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Add new" data-html="true">
				<i class="process-icon-new "></i>
			</span>
		</a>
	</span>
	</h3>
	<div id="rows">
		<div id="homepage_list" class="homepage">
			{foreach from=$homepages key=i item=homepage}
				<div id="homepage_{$homepage.id_homepage|escape:'html':'UTF-8'}" class="panel">
					<div class="row">
						<div class="col-lg-1">
							<span><i class="icon-arrows "></i></span>
						</div>
						<div class="col-md-11">
							<h4 class="pull-left">#{$homepage.id_homepage|escape:'html':'UTF-8'} - {$homepage.title|escape:'html':'UTF-8'}</h4>
							<div class="btn-group-action pull-right">								
								<a class="btn btn-default"
									href="{$link->getAdminLink('AdminJmsthemelayoutHomepage')|escape:'html':'UTF-8'}&configure=jmsthemelayout&export_id_homepage={$homepage.id_homepage|escape:'html':'UTF-8'}">
									<i class="icon-download"></i>
									{l s='Export To XML' mod='jmsthemelayout'}
								</a>	
								<a class="btn btn-default"	href="{$link->getAdminLink('AdminJmsthemelayoutHomepage')|escape:'html':'UTF-8'}&configure=jmsthemelayout&edit_id_homepage={$homepage.id_homepage|escape:'html':'UTF-8'}&id_homepage={$homepage.id_homepage|escape:'html':'UTF-8'}">
									<i class="icon-edit"></i>
									{l s='Edit' mod='jmsthemelayout'}
								</a>
								<a class="btn btn-default"
									href="{$link->getAdminLink('AdminJmsthemelayoutHomepage')|escape:'html':'UTF-8'}&configure=jmsthemelayout&delete_id_homepage={$homepage.id_homepage|escape:'html':'UTF-8'}" onclick="return confirm('Are you sure you want to delete this item?');">
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
</div>