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

<script>
{literal}
	current_id_tab = '{$current_id_tab|intval}'; 
{/literal}
</script>

<div class="clean-module">
{if !$is_16}
	<div class="bootstrap">
	{include file="./header.tpl"}
{/if}
	{if $notice}
	<div class="">{$notice}</div> <!--This variable can not be escaped twice. She already escaped.-->
	{/if}
	<div class="clearfix">
		<div class="col-lg-2">
			<div class="list-group">
				<a href="#documentation" class="list-group-item active documentation" data-toggle="tab"><i class="icon-book"></i>&nbsp;&nbsp;{l s='Documentation' mod='cdorderlimit'}</a>
				{if $tabConfig && $tabConfig|@count>0}
					{foreach from=$tabConfig item='conf' key=i}
						<a href="#{$conf.id}" class="list-group-item {$conf.id}" data-toggle="tab"><i class="icon-cogs"></i>&nbsp;&nbsp; {$conf.title|escape:'htmlall':'UTF-8'}</a><!--This variable can not be escaped twice. She already escaped.-->
					{/foreach}
				{/if} 
				<a href="#contact" class="contact list-group-item" data-toggle="tab"><i class="icon-envelope"></i>&nbsp;&nbsp;{l s='Contact' mod='cdorderlimit'}</a>
			</div>
			
			<div class="list-group">
				<a class="list-group-item"><i class="icon-info"></i> &nbsp;&nbsp; {l s='Version' mod='cdorderlimit'} : {$version|escape:'htmlall':'UTF-8'}</a>
			</div>
		</div>
		<div class="tab-content col-lg-10">
			<!-- Documentation -->
			<div class="tab-pane panel active mainTabs" id="documentation"> 
				<div class="panel-heading"><i class="icon icon-book">&nbsp;&nbsp;</i> {l s='Documentation' mod='cdorderlimit'}</div>
				<div class="form-wrapper">
					<p>{$description|escape:'htmlall':'UTF-8'}</p>
					
					{if $readme}
					<div class="media">
						<a class="pull-left" target="_blank" href="{$readme|escape:'htmlall':'UTF-8'}" data-original-title="" title="">
							<img height="32" width="32" class="media-object" src="{$module_dir|escape:'htmlall':'UTF-8'}/views/img/pdf.png" alt="" title="">
						</a>
						<div class="media-body">
							{l s='Attached you will find the documentation for this module. Do not hesitate to consult in order to properly configure the module.' mod='cdorderlimit'}
						</div>
					</div>
					{/if}
					
					{if $change_log}
					<hr>
					<p style="font-size: 15px;font-weight: bold;">{l s='Change Log' mod='cdorderlimit'}</p>
					<div style="padding:10px;height:100px;border:1px dotted #f6f6f6;overflow-y:scroll;">
						{$change_log} <!--This variable can not be escaped twice. She already escaped.-->
					</div>
					{/if}
				</div> 
			</div>
			
			<!-- extra tabs -->
			{if $tabConfig && $tabConfig|@count>0}
				{foreach from=$tabConfig item='conf' key=i}
					<div id="{$conf.id|escape:'htmlall':'UTF-8'}" class="tab-pane mainTab">
						{$conf.content} <!--This variable can not be escaped twice. She already escaped.-->
					</div>
				{/foreach}
			{/if}
			
			<!-- contact -->
			<div id="contact" class="tab-pane panel mainTab"> 
				<div class="panel-heading"><i class="icon icon-envelope">&nbsp;&nbsp;</i>{l s='Contact-us' mod='cdorderlimit'}</a></div>
				<div class="form-wrapper">
					<p><b>{l s='Thank you for choosing a module developed by Clean Presta.' mod='cdorderlimit'}</b></p>
					<p>{l s='CleanPresta is an Ecommerce/PrestaShop department of CleanDev' mod='cdorderlimit'}</p>
					<hr>
					<p>{l s='If you encounter a problem using the module, our team is at your service here ' mod='cdorderlimit'} : <a target="_blank" href="{$addon_ratting|escape:'htmlall':'UTF-8'}">{l s='click here' mod='cdorderlimit'}</a> ({l s='Think to give us maximum information about your situation' mod='cdorderlimit'})</p>
					<p>{l s='But, read documentation before contacting us' mod='cdorderlimit'}</p>
					<hr>
					<p>{l s='See all our modules here' mod='cdorderlimit'} : <a target="_blank" href="http://addons.prestashop.com/fr/2_community?contributor=7767">{l s='click here' mod='cdorderlimit'}</a></p>
				</div> 
			</div>
		</div> 
		
		<div class="clearfix"></div>
		
		<div class="panel alert-success" style="text-align:center;background-color:#ddf0de;font-size: 14px;">
			<p>{l s='You are satisfied with your module, Encourage us' mod='cdorderlimit' mod='cdorderlimit'} : <a target="_blank" href="http://addons.prestashop.com/ratings.php">{l s='Please note this module on PrestaShop Addons, giving it 5 stars' mod='cdorderlimit'}</a></p>
			<p>{l s='If you are not satisfied' mod='cdorderlimit'} : <a target="_blank" href="{$addon_ratting|escape:'htmlall':'UTF-8'}">{l s='we will be pleased to hear from you' mod='cdorderlimit'}</a></p>
		</div>
		
		<div class="clearfix"></div>
		
		{if !empty($features) && $features.module|@count>0}
		<div class="panel">
			<div class="panel-heading"><i class="icon icon-signal">&nbsp;&nbsp;</i>{l s='Please do not forget' mod='cdorderlimit'}</a></div>
			<ul class="form-wrapper" style="padding:0"> 
				{foreach from=$features.module item='feature' key=i}
					<li class="col-md-12" style="margin-bottom:10px;border-bottom:1px dotted;list-style: none;padding:5px 0">
						<div class="col-xs-12 col-sm-4 col-md-4"><a href="{$feature.addon|escape:'htmlall':'UTF-8'}" target="_blank">{$feature.name|escape:'htmlall':'UTF-8'}</a></div>
						{if !empty($feature.description)}<div class="col-xs-12 col-sm-8 col-md-8">{$feature.description|escape:'htmlall':'UTF-8'}</div>{/if}
						<div style="clear:both"></div>
					</li>
				{/foreach}
			</ul> 
			<div style="clear:both"></div>
		</div>
		{/if}
	</div>
{if !$is_16}
	{include file="./header.tpl"}
	</div>
{/if}
</div>