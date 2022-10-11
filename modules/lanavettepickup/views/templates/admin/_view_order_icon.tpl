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

<link href="{$modules_dir|escape:'html':'UTF-8'}lanavettepickup/views/css/ps15.css" rel="stylesheet" type="text/css" media="all"/>

<span class="btn-group pull-right">
    <a href="index.php?controller=AdminOrders&amp;id_order={$id_order|escape:'html':'UTF-8'}&amp;vieworder&amp;token={getAdminToken tab='AdminOrders'}"
       class="btn btn-default" title="{l s='View' mod='lanavettepickup'}">
        <i class="icon-search-plus"></i> {l s='View' mod='lanavettepickup'}
    </a>
</span>
