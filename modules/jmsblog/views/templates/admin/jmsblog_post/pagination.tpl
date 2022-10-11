{*
* 2007-2014 PrestaShop
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
<div class="jms-blog-pagination">	
	{if $total > $limit}
	<div class="pagination">
		<ul>
		{for $foo = 1 to $pages}
			<li class="{if ($foo-1)*$limit == $start}active{/if}"><a href="{$link->getAdminLink('AdminJmsblogPost')|escape:'html':'UTF-8'}&configure=jmsblog&start={(($foo|escape:'htmlall':'UTF-8')-1)*$limit}&limit={$limit|escape:'htmlall':'UTF-8'}">{$foo|escape:'htmlall':'UTF-8'}</a></li>
		{/for}
		</ul>
	</div>
	{/if}
	<div class="counter-div">
		<span>Total : {$total|escape:'html':'UTF-8'} items</span>
	</div>
</div>