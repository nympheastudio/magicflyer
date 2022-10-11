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

{capture name=path}{$current_category.title|escape:'html':'UTF-8'}{/capture}

<h3 class="title-blog">{$current_category.title}</h3>

{if isset($posts) AND $posts}		

	<div class="post-list">

		{foreach from=$posts item=post}

			{assign var=params value=['post_id' => $post.post_id, 'category_slug' => $post.category_alias, 'slug' => $post.alias]}

			{assign var=catparams value=['category_id' => $post.category_id, 'slug' => $post.category_alias]}				

			<article class="blog-post">

				<h4 class="post-title"><a href="{jmsblog::getPageLink('jmsblog-post', $params)}">{$post.title|escape:'htmlall':'UTF-8'}</a></h4>

				{if $post.link_video && $jmsblog_setting.JMSBLOG_SHOW_MEDIA}

					<div class="post-thumb">

					{$post.link_video}

					</div>

				{elseif $post.image && $jmsblog_setting.JMSBLOG_SHOW_MEDIA}

					<div class="post-thumb">

						<a href="{jmsblog::getPageLink('jmsblog-post', $params)}"><img src="{$image_baseurl|escape:'html':'UTF-8'}{$post.image|escape:'html':'UTF-8'}" alt="{$post.title|escape:'htmlall':'UTF-8'}" class="img-responsive" /></a>			 		

					</div>

				{/if}				

			</article>			

		{/foreach}

	</div>

{else}	

{l s='Sorry, dont have any post in this category' mod='jmsblog'}

{/if}





