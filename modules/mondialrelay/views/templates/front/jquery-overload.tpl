{*
* 2007-2018 PrestaShop
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
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2018 PrestaShop SA
* @version   Release: $Revision: 6844 $
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

{if $MR_overload_current_jquery}
	<script type="text/javascript">
		var currentJquery = jQuery.noConflict(true);
	</script>
	<script type="text/javascript" src="{$new_base_dir|escape:'htmlall':'UTF-8'}views/js/jquery-1.6.4.min.js"></script>
{else}
	<script type="text/javascript" src="{$new_base_dir|escape:'htmlall':'UTF-8'}views/js/jquery-1.6.4.min.js"></script>
	<script type="text/javascript">
		var MRjQuery = jQuery.noConflict(true);
	</script>
{/if}
