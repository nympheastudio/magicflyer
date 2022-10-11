{*
 * @package Jms Slider Layer
 * @version 1.0
 * @Copyright (C) 2009 - 2015 Joommasters.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Website: http://www.joommasters.com
*}
<div class="panel">
	<h3>
	<span title="" data-toggle="tooltip" class="label-tooltip toogle" data-original-title="Click to Toggle" data-html="true">
		<i class="icon-list-ul"></i> {l s='Slides list' mod='jmssliderlayer'}
	</span>		
	
	<span class="panel-heading-action">		
		<a href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=jmssliderlayer&addSlide=1" class="btn btn-default">
			<i class="process-icon-new" style="font-size:14px; display:inline-block;height:16px;"></i>Add Slide
		</a>
		
	</span>
	</h3>
	<script>
			$(document).ready(function(){									
				$('.toogle').click(function(e){
					$('#slidesContent').toggle(200);
				});
				$('.panel-heading').click(function(e){
					$(this).next('.form-wrapper').toggle(200);
				});
			});
				
	</script>
	<div id="slidesContent">
		<div id="slides">
			{if $slides|@count gt 0}				
			{foreach from=$slides item=slide}
				<div id="slides_{$slide.id_slide|escape:'html':'UTF-8'}" class="panel">
					<div class="row">
						<div class="col-lg-1">
							<span><i class="icon-arrows "></i></span>
						</div>
						<div class="col-md-2">							
							{$slide.title|escape:'html':'UTF-8'}
						</div>
						<div class="col-md-2">							
							{if $slide.iso_lang}
								{l s='Language' mod='jmssliderlayer'} : {$slide.iso_lang|escape:'html':'UTF-8'}
							{else}
								{l s='All Language' mod='jmssliderlayer'}
							{/if}
						</div>
						<div class="col-md-3">
							<div class="btn-group-action pull-right">
								<a class="btn btn-default"
									href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=jmssliderlayer&layers=1&id_slide={$slide.id_slide|escape:'html':'UTF-8'}">
									<i class="icon-edit"></i>
									{l s='Layers Manager' mod='jmssliderlayer'}
								</a>
							</div>
						</div>
						<div class="col-md-4">							
							<div class="btn-group-action pull-right">
								
								<a class="btn btn-default" href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=jmssliderlayer&copySlide=1&id_slide={$slide.id_slide|escape:'html':'UTF-8'}">
									<i class="icon-copy"></i>
									{l s='Duplicate' mod='jmssliderlayer'}
								</a>
								<a class="btn {if $slide.active}btn-success{else}btn-danger{/if}"
									href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=jmssliderlayer&changeStatus&id_slide={$slide.id_slide|escape:'html':'UTF-8'}" title="title="{if $slide.active}Enabled{else}Disabled{/if}"">
									<i class="{if $slide.active}icon-check{else}icon-remove{/if}"></i>{if $slide.active}Enabled{else}Disabled{/if}
								</a>						
								<a class="btn btn-default"
									href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=jmssliderlayer&editSlide=1&id_slide={$slide.id_slide|escape:'html':'UTF-8'}">
									<i class="icon-edit"></i>
									{l s='Edit' mod='jmssliderlayer'}
								</a>
								<a class="btn btn-default" onclick="if(confirm('Are you sure want to remove this slide?')) { document.location='{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=jmssliderlayer&delete_id_slide={$slide.id_slide|escape:'htmlall'}'; } else { return true;}"
								>
									<i class="icon-trash"></i>
									{l s='Delete' mod='jmssliderlayer'}
								</a>
							</div>
						</div>
					</div>
				</div>
			{/foreach}
			{else}
				{l s='There is no slide' mod='jmssliderlayer'}
			{/if}
		</div>
	</div>
</div>