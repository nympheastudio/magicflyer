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
<div class="result_div">
{if $products}
<div class="results">	
	{foreach from=$products item=product name=i}
		<div class="item">
			<div class="left-img">
				<a href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" class="product_image">
					<img src="{$link->getImageLink($product.id_image, $product.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{$product.name|escape:html:'UTF-8'}" />
				</a>
			</div>
			<div class="right-info">
				<a href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|truncate:50:'...'|escape:'html':'UTF-8'}">
					{$product.name|truncate:35:'...'|escape:'html':'UTF-8'}
				</a>
				<span class="price">{convertPrice price=$product.price}</span>
			</div>
		</div>
	{/foreach}	
</div>
{else}
{$no_text|escape:'html':'UTF-8'}
{/if}
</div>
