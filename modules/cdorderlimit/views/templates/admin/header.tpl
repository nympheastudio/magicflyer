{*
* 2013 - 2015 CleanDev
*
* NOTICE OF LICENSE
*
* This file is proprietary and can not be copied and/or distributed
* without the express permission of CleanDev
*
* @author    CleanPresta : www.cleanpresta.com <contact@cleanpresta.com>
* @copyright 2013 - 2015 CleanDev.net
* @license   You only can use module, nothing more!
*}

<div class="page-head">
	<h2 class="page-title" style="padding: 16px;">
		{$display_name|escape:'htmlall':'UTF-8'}
	</h2> 
	<div class="page-bar toolbarBox">
		<div class="btn-toolbar">
			<ul class="cc_button nav nav-pills pull-right">
				<li>
					<a id="desc-module-hook" class="toolbar_btn" href="{$module_trad|escape:'htmlall':'UTF-8'}" title="{l s='Translation' mod='cdorderlimit'}">
						<i class="process-icon-flag"></i>
						<div>{l s='Translation' mod='cdorderlimit'}</div>
					</a>
				</li>
				<li>
					<a id="desc-module-hook" class="toolbar_btn" href="{$module_hook|escape:'htmlall':'UTF-8'}" title="{l s='Manage hooks' mod='cdorderlimit'}">
						<i class="process-icon-anchor"></i>
						<div>{l s='Manage hooks' mod='cdorderlimit'}</div>
					</a>
				</li>
				<li>
					<a id="desc-module-back" class="toolbar_btn" href="{$module_back|escape:'htmlall':'UTF-8'}" title="{l s='Back' mod='cdorderlimit'}">
						<i class="process-icon-back"></i>
						<div>{l s='Back' mod='cdorderlimit'}</div>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>