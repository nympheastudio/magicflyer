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
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div {if $class_home}class="{$class_home|escape:'html':'UTF-8'}"{/if}>
{foreach from=$rows item=row}
	<div {if $row.class}class="{$row.class|escape:'html':'UTF-8'}"{/if}>
		<div {if $row.fullwidth == 0}class="container"{/if}>
			<div class="home-row row">
			{foreach from=$row.positions item=position}
				<div class="home-position col-lg-{$position.col_lg|escape:'html':'UTF-8'} col-sm-{$position.col_sm|escape:'html':'UTF-8'} col-md-{$position.col_md|escape:'html':'UTF-8'} col-xs-{$position.col_xs|escape:'html':'UTF-8'} {$position.class_suffix|escape:'html':'UTF-8'}">
					{foreach from=$position.blocks item=block}
						<div class="home-block">
							{if $block.show_title}<h4 class="title_block"><span>{$block.title|escape:'html':'UTF-8'}</span></h4>{/if}
							<div class="block-content">
							{$block.return_value|escape:'html':'UTF-8'}
							</div>
						</div>
					{/foreach}
				</div>
			{/foreach}
			</div>
		</div>
	</div>
{/foreach}
</div>