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
<script type="text/javascript">
$( document ).ready(function() {
	$( "#filter_post_id" ).change(function() {
		filterchange();
	});
	$( "#filter_cstate" ).change(function() {
		filterchange();
	});
});
function filterchange(){
	var post_id = $( "#filter_post_id" ).val();
	var state = $( "#filter_cstate" ).val();
	var url = "{$link->getAdminLink('AdminJmsblogComment')|escape:'html':'UTF-8'}&configure=jmsblog&filter_post_id=" + post_id + "&filter_cstate=" + state;
	url = url.replace('&amp;','&');
	window.location = url;
}
</script>
<div class="jms-blog-filter">
	<span>{l s='Post Filter' mod='jmsblog'}</span>
	<select id="filter_post_id">
		<option value="0">{l s='Select Post' mod='jmsblog'}</option>
		{foreach from=$categories item=category}
		{$category_index = "{$category.category_id}"}		
		<optgroup label="{$category.title|escape:'html':'UTF-8'}">
			{foreach from=$posts.$category_index item=post}
				<option value="{$post.post_id}" {if $post.post_id == $filter_post_id}selected{/if}>{$post.title}</option>
			{/foreach}
		</optgroup>
		{/foreach}
	</select>
	
	<span>{l s='State Filter' mod='jmsblog'}</span>
	<select id="filter_cstate">
		<option {if $filter_cstate == '-1'}selected{/if} value="-1">{l s='Select Status' mod='jmsblog'}</option>		
		<option {if $filter_cstate == '1'}selected{/if} value="1">{l s='Enabled' mod='jmsblog'}</option>		
		<option {if $filter_cstate == '0'}selected{/if} value="0">{l s='Disabled' mod='jmsblog'}</option>
		<option {if $filter_cstate == '-2'}selected{/if} value="-2">{l s='Waiting for approve' mod='jmsblog'}</option>		
	</select>
	
</div>