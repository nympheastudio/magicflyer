{*
* 2007-2017 PrestaShop
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
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{* Generate HTML code for printing Invoice Icon with link *}
<link href="{$modules_dir|escape:'html':'UTF-8'}lanavettepickup/views/css/ps15.css" rel="stylesheet" type="text/css" media="all"/>
<span class="btn-group-action">
	<span class="btn-group">
		<a class="btn btn-default"
           href="index.php?controller=Lnp2PickupOrderPage&token={getAdminToken tab='Lnp2PickupOrderPage'}&print_pdf=true&id_order={$id_order|escape:'html':'UTF-8'}">
			<i class="icon-file-text"></i> {l s='Print' mod='lanavettepickup'}
		</a>
        &nbsp;
		<a class="btn btn-default"
           href="index.php?controller=Lnp2PickupOrderPage&token={getAdminToken tab='Lnp2PickupOrderPage'}&id_order={$id_order|escape:'html':'UTF-8'}&change_status=1">
			<i class="icon-file-text"></i> {l s='Change status' mod='lanavettepickup'}
		</a>        
    </span>
</span>
