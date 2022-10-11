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
{if $msg == 1}<div class="success">{l s='Your comment submited' mod='jmsblog'} ! {if $jmsblog_setting.JMSBLOG_AUTO_APPROVE_COMMENT == 0} {l s='Please waiting approve from Admin' mod='jmsblog'}.{/if}</div>{/if}
{if $cerrors|@count gt 0}
	<ul>
	{foreach from=$cerrors item=cerror}
		<li class="error">{$cerror}</li>
	{/foreach}	
	</ul>
{/if}
<div id="accordion" class="panel-group">
	<div class="panel panel-default">
		<div class="comment-heading clearfix">
			<h5><a data-toggle="collapse" data-parent="#accordion" href="#post-comments">{$comments|@count} {l s='Comments' mod='jmsblog'}</a></h5>
		</div>		
		<div id="post-comments" class="panel-collapse collapse">
		{if $comments}
			{foreach from=$comments item=comment key = k}
				<div class="post-comment clearfix">
					<div class="post-comment-info">
					<img class="attachment-widget wp-post-image img-responsive" src="{$image_baseurl|escape:'html':'UTF-8'}user.png" />
					<h6>{$comment.customer_name|escape:'html':'UTF-8'}</h6>
					<span class="customer_site">{$comment.customer_site|escape:'html':'UTF-8'}</span>
					<span class="time_add">{$comment.time_add|escape:'html':'UTF-8'}</small>
					</div>
					<p class="post-comment-content">{$comment.comment|escape:'html':'UTF-8'}</p>
				</div>
			{/foreach}	
		{/if}
		</div>
	</div>
</div>
{if $jmsblog_setting.JMSBLOG_ALLOW_GUEST_COMMENT || (!$jmsblog_setting.JMSBLOG_ALLOW_GUEST_COMMENT && $logged)}	
<div class="commentForm">
	<form id="commentForm" enctype="multipart/form-data" method="post" action="index.php?fc=module&module=jmsblog&controller=post&post_id={$post.post_id|escape:'html':'UTF-8'}&action=submitComment">
		<div class="row">
			<div class="col-sm-12">
				<h4 class="heading">Leave a Comment</h4>
				<p class="h-info">{l s='Your email address will not be published' mod='jmsblog'}.</p>
			</div>
		</div>	
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label for="comment_name">{l s='Your Name' mod='jmsblog'}<sup class="required">*</sup></label>
					<input id="customer_name" class="form-control" name="customer_name" type="text" value="{$customer.firstname} {$customer.lastname}" required />
				</div>	
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="comment_title">{l s='Your Email' mod='jmsblog'}<sup class="required">*</sup></label>
					<input id="comment_title" class="form-control" name="email" type="text" value="{$customer.email}" required />
				</div>
			</div>
		</div>
		<div class="form-group">
			<label for="comment_title">{l s='Your Website' mod='jmsblog'}</label>
			<input id="customer_site" class="form-control" name="customer_site" type="text" value=""/></br>
		</div>
		<div class="form-group">
			<label for="content">{l s='Your Comment' mod='jmsblog'}<sup class="required">*</sup></label>
			<textarea id="comment" class="form-control" name="comment" rows="8" required></textarea>
		</div>
		<div id="new_comment_form_footer">
			<input id="item_id_comment_send" name="post_id" type="hidden" value="{$post.post_id|escape:'html':'UTF-8'}" />
			<input id="item_id_comment_reply" name="post_id_comment_reply" type="hidden" value="" />
			<p class="">
				<button id="submitComment" class="btn btn-default" name="submitComment" type="submit">{l s='Submit Comment' mod='jmsblog'}</button>
			</p>
		</div>
	</form>
	<script>
	$("#commentForm").validate({
	  rules: {		
		customer_name: "required",		
		email: {
		  required: true,
		  email: true
		}
	  }
	});
	</script>
</div>
{/if}
{if !$jmsblog_setting.JMSBLOG_ALLOW_GUEST_COMMENT && !$logged}
	{l s='Please Login to comment' mod='jmsblog'}
{/if}