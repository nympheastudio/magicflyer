{*
 * @package Jms Slider Layer
 * @version 1.0
 * @Copyright (C) 2009 - 2015 Joommasters.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Website: http://www.joommasters.com
*}
<form novalidate="" enctype="multipart/form-data" method="post" action="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=jmssliderlayer" class="defaultForm form-horizontal" id="module_form">
<div class="panel"><h3><i class="icon-list-ul"></i> {l s='Layers of' mod='jmssliderlayer'} <a 
									href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=jmssliderlayer&editSlide=1&id_slide={$current_slide.id_slide|escape:'html':'UTF-8'}">
									"{$current_slide.title|escape:'htmlall':'UTF-8'}"</a>	
	</h3>
	
	<div id="slide_img" style="{if $current_slide.bg_type}background-image:url({$root_url|escape:'html':'UTF-8'}modules/jmssliderlayer/views/img/{$current_slide.main_img|escape:'html':'UTF-8'});{else}background-color:{$current_slide.bg_color|escape:'html':'UTF-8'};{/if} {if $current_slide.bgfit!='normal'}background-size:{$current_slide.bgfit|escape:'html':'UTF-8'};{/if} background-repeat:{if $current_slide.bgrepeat}repeat{else}no-repeat{/if}; {if $current_slide.bgposition}background-position:{$current_slide.bgposition|escape:'html':'UTF-8'};{/if}">		
		<div id="slide_grid" style="width:{$grid_width|escape:'html':'UTF-8'}px;height:{$grid_height|escape:'html':'UTF-8'}px;" data-toggle="tooltip" class="label-tooltip" data-original-title="This grey mask box is grid area where Layers are displayed. The width and height of it are configured in Module Genenal Setting page. The max height and width of the Grid box are the height and width of the Slider. The Grid will be centered Vertically in case the height of the Slider is higher and be centered Horizontally in case the width of the Slider is wider." data-html="true">
		{foreach from=$layers item=layer key=l_index}
			<div class="tp-caption {$layer.layer_class|escape:'html':'UTF-8'} {if $l_index ==0}active{/if} {if $layer.video_fullscreen}fullscreenvideo{/if}" style="{if !$layer.video_fullscreen}top:{$layer.data_y|escape:'html':'UTF-8'}px;{/if}z-index:{$layer.ordering|escape:'html':'UTF-8' + 1};" id="caption_{$layer.layer_id|escape:'html':'UTF-8'}">
				{if $layer.data_type=='text'}
					{$layer.layer_text|html_entity_decode|escape:'':'UTF-8'}
				{elseif $layer.data_type=='img'}
					<img {if $layer.img_hh}data-hh="{$layer.img_hh|escape:'html':'UTF-8'}"{/if} {if $layer.img_ww}data-ww="{$layer.img_ww|escape:'html':'UTF-8'}"{/if} alt="" src="{$root_url|escape:'html':'UTF-8'}modules/jmssliderlayer/views/img/layers/{$layer.layer_img|escape:'html':'UTF-8'}" />
				{elseif $layer.data_type=='vimeovideo'}	
					<iframe src='http://player.vimeo.com/video/{$layer.layer_video|escape:'html':'UTF-8'}?title=0&byline=0&portrait=0;api=1' {if $layer.video_fullscreen}width='100%' height='100%' style='width:100%;height:100%;'{else}width='{$layer.video_width|escape:'html':'UTF-8'}px' height='{$layer.video_height|escape:'html':'UTF-8'}px' style='width:{$layer.video_width|escape:'html':'UTF-8'}px;height:{$layer.video_height|escape:'html':'UTF-8'}px;'{/if}></iframe>
				{else}
					<iframe src="http://www.youtube.com/embed/{$layer.layer_video|escape:'html':'UTF-8'}?enablejsapi=1&html5=1&hd=1&wmode=opaque&controls=1&showinfo=0;rel=0;" {if $layer.video_fullscreen}width='100%' height='100%' style='width:100%;height:100%;'{else}width='{$layer.video_width|escape:'html':'UTF-8'}px' height='{$layer.video_height|escape:'html':'UTF-8'}px' style='width:{$layer.video_width|escape:'html':'UTF-8'}px;height:{$layer.video_height|escape:'html':'UTF-8'}px;'{/if}></iframe>	
				{/if}
			</div>
		{/foreach}
		</div>
	</div>
	<div id="layer-tools" class="btn-group-action">
		<a class="btn btn-default" id="add-text"><i class="icon-text"></i>Add Layer:Text</a>		
		<a class="btn btn-default"  id="add-img"><i class="icon-image"></i>Add Layer:Image</a>
		<a class="btn btn-default"  id="add-video"><i class="icon-youtube-play"></i>Add Layer:Video</a>		
	</div>
	<script>
				$(document).ready(function(){
					var _mheight = $("#slide_grid").height();
					var _mwidth = $("#slide_grid").width();						
					$('.tp-caption').draggable({						
						stop: function(event, ui) {
							// Show dropped position.
							strId = $(this).attr('id');		
							currentId = strId.substring(8, 20);							
							var Stoppos = $(this).position();
							$('#data_x_' + currentId).val(Math.round(Stoppos.left));							
							$('#data_y_' + currentId).val(Math.round(Stoppos.top));							
						}
					});
					
					$("#textdialog").dialog({
						autoOpen: false
					});
					$("#imgdialog").dialog({
						autoOpen: false
					});
					$("#videodialog").dialog({
						autoOpen: false
					});
				});
				
	</script>	
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-cmd-12 form-horizontal">			
		<div class="panel panel-default">
			<h3 class="panel-heading">{l s='Layer Params' mod='jmssliderlayer'}</h3>			
			<div class="panel-body clearfix" style="display: block;" id="layer_params">	
			{foreach from=$layers item=layer key=l_index}
				<input type="hidden" name="layer_ids[]" value="{$layer.layer_id|escape:'html':'UTF-8'}" />
				<div id="layerform_{$layer.layer_id|escape:'html':'UTF-8'}" class="form-wrapper {if $l_index ==0}active{/if}">
					<div class="form-group">
						<label class="control-label col-lg-2">{l s='Title' mod='jmssliderlayer'}</label>						
						<div class="col-lg-4">
							<input type="text" class="form-control" value="{$layer.title|escape:'html':'UTF-8'}" id="title_{$layer.layer_id|escape:'html':'UTF-8'}" name="title_{$layer.layer_id|escape:'html':'UTF-8'}">
						</div>	
					</div>
					<div class="form-group">
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="These are Styling classes created in the settings.css  You can add unlimited amount of Styles in your own css file, to style your captions at the top level already" data-html="true">{l s='Layer Class' mod='jmssliderlayer'}</span></label>						
						<div class="col-lg-4">
							<select class="form-control layer_class" id="layer_class_{$layer.layer_id|escape:'html':'UTF-8'}" name="layer_class_{$layer.layer_id|escape:'html':'UTF-8'}"> 
								{foreach from=$caption_cls item=caption_cl}
									<option value="{$caption_cl.id|escape:'html':'UTF-8'}" {if $caption_cl.id == $layer.layer_class}selected="selected"{/if}>{$caption_cl.id|escape:'html':'UTF-8'}</option>
								{/foreach}
							</select>
						</div>	
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="The speed in milliseconds of the transition to move the Layer in the Slide at the defined  timepoint" data-html="true">{l s='Speed' mod='jmssliderlayer'}</span></label>						
						<div class="col-lg-4">
							<input type="text" class="fixed-width-xl form-control" value="{$layer.speed|escape:'html':'UTF-8'}" id="speed" name="speed_{$layer.layer_id|escape:'html':'UTF-8'}">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Possible Values are 'left', 'center', 'right', or any Value between -2500  and 2500" data-html="true">{l s='Data X' mod='jmssliderlayer'}</span></label>						
						<div class="col-lg-4">
							<input type="text" class="form-control data_x" value="{$layer.data_x|escape:'html':'UTF-8'}" id="data_x_{$layer.layer_id|escape:'html':'UTF-8'}" name="data_x_{$layer.layer_id|escape:'html':'UTF-8'}">
						</div>						
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Possible Values are 'top', 'center', 'bottom', or any Value between -2500  and 2500." data-html="true">{l s='Data Y' mod='jmssliderlayer'}</span></label>						
						<div class="col-lg-4">
							<input type="text" class="form-control data_y" value="{$layer.data_y|escape:'html':'UTF-8'}" id="data_y_{$layer.layer_id|escape:'html':'UTF-8'}" name="data_y_{$layer.layer_id|escape:'html':'UTF-8'}">
						</div>	
					</div>
					<div class="form-group">
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="The timepoint in millisecond when/at the Layer should move in to the slide" data-html="true">{l s='Start' mod='jmssliderlayer'}</span></label>						
						<div class="col-lg-4">
							<input type="text" class="form-control start" value="{$layer.start|escape:'html':'UTF-8'}" id="start_{$layer.layer_id|escape:'html':'UTF-8'}" name="start_{$layer.layer_id|escape:'html':'UTF-8'}">
						</div>						
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="The timepoint in millisecond when/at the Layer should move out from the slide" data-html="true">{l s='End' mod='jmssliderlayer'}</span></label>						
						<div class="col-lg-4">
							<input type="text" class="form-control end" value="{$layer.end|escape:'html':'UTF-8'}" id="end_{$layer.layer_id|escape:'html':'UTF-8'}" name="end_{$layer.layer_id|escape:'html':'UTF-8'}">
						</div>	
					</div>
					<div class="form-group">					
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Incoming Animation Class" data-html="true">{l s='Incoming Animation' mod='jmssliderlayer'}</span></label>
						<div class="col-lg-4">
							<select class="form-control" id="incoming_class_{$layer.layer_id|escape:'html':'UTF-8'}" name="incoming_class_{$layer.layer_id|escape:'html':'UTF-8'}"> 
								{foreach from=$incoming item=ioption}
									<option value="{$ioption.id|escape:'html':'UTF-8'}" {if $ioption.id == $layer.incoming_class}selected="selected"{/if}>{$ioption.name|escape:'html':'UTF-8'}</option>
								{/foreach}
							</select>
						</div>
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Outgoing Animation Class" data-html="true">{l s='Outgoing Animation' mod='jmssliderlayer'}</span></label>
						<div class="col-lg-4">
							<select class="form-control" id="outgoing_class_{$layer.layer_id|escape:'html':'UTF-8'}" name="outgoing_class_{$layer.layer_id|escape:'html':'UTF-8'}"> 
								{foreach from=$outgoing item=ooption}
									<option value="{$ooption.id|escape:'html':'UTF-8'}" {if $ooption.id == $layer.outgoing_class}selected="selected"{/if}>{$ooption.name|escape:'html':'UTF-8'}</option>
								{/foreach}
							</select>
						</div>
					</div>	
					<div class="form-group">			
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="The Easing Art how the layer is moved in to the slide" data-html="true">{l s='Easing' mod='jmssliderlayer'}</span></label>												
						<div class="col-lg-4">
							<select class="form-control" id="easing_{$layer.layer_id|escape:'html':'UTF-8'}" name="easing_{$layer.layer_id|escape:'html':'UTF-8'}"> 
								{foreach from=$easing item=soption}
									<option value="{$soption.id|escape:'html':'UTF-8'}" {if $soption.id == $layer.easing}selected="selected"{/if}>{$soption.id|escape:'html':'UTF-8'}</option>
								{/foreach}
							</select>
						</div>	
					</div>
					<input type="hidden" name="data_type_{$layer.layer_id|escape:'html':'UTF-8'}" value="{$layer.data_type|escape:'html':'UTF-8'}" />
					{if $layer.data_type =='text'}
					<div class="form-group">
						<label class="control-label col-lg-2">{l s='Text or html' mod='jmssliderlayer'}</label>			
						<div class="col-lg-10">	
							<textarea cols="30" rows="6" name="layer_text_{$layer.layer_id|escape:'html':'UTF-8'}" id="layer_text_{$layer.layer_id|escape:'html':'UTF-8'}" class="form-control layer_textarea">{$layer.layer_text}</textarea>
						</div>	
					</div>
					<div class="form-group">
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Split Text Animation (incoming transition) to 'words', 'chars' or 'lines'. This will create amazing Animation Effects on one go, without the needs to create more layers" data-html="true">{l s='Incoming Split Text' mod='jmssliderlayer'}</span></label>												
						<div class="col-lg-4">
							<select class="form-control" id="easing_{$layer.layer_id|escape:'html':'UTF-8'}" name="splitin_{$layer.layer_id|escape:'html':'UTF-8'}"> 	
								<option value="" {if $layer.splitin == ''}selected="selected"{/if}>None</option>	
								<option value="words" {if $layer.splitin == 'words'}selected="selected"{/if}>Words</option>
								<option value="chars" {if $layer.splitin == 'chars'}selected="selected"{/if}>Chars</option>
								<option value="lines" {if $layer.splitin == 'lines'}selected="selected"{/if}>Lines</option>
							</select>
						</div>	
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Split Text Animation (outgoing transition) to 'words', 'chars' or 'lines'. Only available if End is set!" data-html="true">{l s='Outgoing Split Text' mod='jmssliderlayer'}</span></label>												
						<div class="col-lg-4">	
							<select class="form-control" id="easing_{$layer.layer_id|escape:'html':'UTF-8'}" name="splitout_{$layer.layer_id|escape:'html':'UTF-8'}"> 	
								<option value="" {if $layer.splitout == ''}selected="selected"{/if}>None</option>	
								<option value="words" {if $layer.splitout == 'words'}selected="selected"{/if}>Words</option>
								<option value="chars" {if $layer.splitout == 'chars'}selected="selected"{/if}>Chars</option>
								<option value="lines" {if $layer.splitout == 'lines'}selected="selected"{/if}>Lines</option>
							</select>
						</div>	
					</div>
					{elseif $layer.data_type=='img'}
					<div class="form-group">
						<label class="control-label col-lg-2"><span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Please upload layers image to modules/jmssliderlayer/views/img/layers/ folder" data-html="true">{l s='Select Image' mod='jmssliderlayer'}</span></label>		
						<div class="col-lg-10">
							<select name="layer_img_{$layer.layer_id|escape:'html':'UTF-8'}" class="layer_img_box" id="layer_img_{$layer.layer_id|escape:'html':'UTF-8'}" class="form-control">
								{foreach from=$layerimgs item=layerimg}	
									<option value="{$layerimg.id|escape:'html':'UTF-8'}" {if $layerimg.id == $layer.layer_img}selected="selected"{/if}>{$layerimg.id|escape:'html':'UTF-8'}</option>
								{/foreach}
							</select>
						</div>			
					</div>
					{else}	
						<div class="form-group">
						<label class="control-label col-lg-2">{l s='Video Source' mod='jmssliderlayer'}</label>		
						<div class="col-lg-10">
							<select name="video_source_{$layer.layer_id|escape:'html':'UTF-8'}" id="video_source_{$layer.layer_id|escape:'html':'UTF-8'}" class="form-control">								
									<option value="vimeovideo" {if $layer.data_type == 'vimeovideo'}selected="selected"{/if}>Vimeo Video</option>
									<option value="youtubevideo" {if $layer.data_type == 'youtubevideo'}selected="selected"{/if}>Youtube Video</option>								
							</select>
						</div>			
					</div>
						<div class="form-group">
							<label class="control-label col-lg-2">{l s='Video ID' mod='jmssliderlayer'}</label>						
							<div class="col-lg-10">
								<input type="text" class="form-control" value="{$layer.layer_video|escape:'html':'UTF-8'}" id="layer_video_{$layer.layer_id|escape:'html':'UTF-8'}" name="layer_video_{$layer.layer_id|escape:'html':'UTF-8'}">
							</div>	
						</div>						
						<div class="form-group">
							<label class="control-label col-lg-2">{l s='Video Width' mod='jmssliderlayer'}</label>						
							<div class="col-lg-4">
								<input type="text" class="form-control" value="{$layer.video_width|escape:'html':'UTF-8'}" id="video_width_{$layer.layer_id|escape:'html':'UTF-8'}" name="video_width_{$layer.layer_id|escape:'html':'UTF-8'}">
							</div>	
							<label class="control-label col-lg-2">{l s='Video Height' mod='jmssliderlayer'}</label>						
							<div class="col-lg-4">
								<input type="text" class="form-control" value="{$layer.video_height|escape:'html':'UTF-8'}" id="video_height_{$layer.layer_id|escape:'html':'UTF-8'}" name="video_height_{$layer.layer_id|escape:'html':'UTF-8'}">
							</div>	
						</div>
						<div class="form-group">
							<label class="control-label col-lg-2">{l s='Video Autoplay' mod='jmssliderlayer'}</label>
							<div class="col-lg-4">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" {if $layer.video_fullscreen == 1}checked="checked"{/if} value="1" id="video_fullscreen_{$layer.layer_id|escape:'html':'UTF-8'}_on" name="video_fullscreen_{$layer.layer_id|escape:'html':'UTF-8'}">
									<label for="video_fullscreen_{$layer.layer_id|escape:'html':'UTF-8'}_on">Yes</label>
									<input type="radio" {if $layer.video_fullscreen == 0}checked="checked"{/if} value="0" id="video_fullscreen_{$layer.layer_id|escape:'html':'UTF-8'}_off" name="video_fullscreen_{$layer.layer_id|escape:'html':'UTF-8'}">
									<label for="video_fullscreen_{$layer.layer_id|escape:'html':'UTF-8'}_off">No</label>
									<a class="slide-button btn"></a>
								</span>
							</div>	
							<label class="control-label col-lg-2">{l s='Video Autoplay' mod='jmssliderlayer'}</label>
							<div class="col-lg-4">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" {if $layer.video_autoplay == 1}checked="checked"{/if} value="1" id="video_autoplay_{$layer.layer_id|escape:'html':'UTF-8'}_on" name="video_autoplay_{$layer.layer_id|escape:'html':'UTF-8'}">
									<label for="video_autoplay_{$layer.layer_id|escape:'htmlall':'UTF-8'}_on">Yes</label>
									<input type="radio" {if $layer.video_autoplay == 0}checked="checked"{/if} value="0" id="video_autoplay_{$layer.layer_id|escape:'html':'UTF-8'}_off" name="video_autoplay_{$layer.layer_id|escape:'html':'UTF-8'}">
									<label for="video_autoplay_{$layer.layer_id|escape:'htmlall':'UTF-8'}_off">No</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
						</div>						
					{/if}
					
				</div>
			{/foreach}
			</div>
		</div>	
		</div>
		 <div id="textdialog" title="{l s='Add Text Layer' mod='jmssliderlayer'}" class="form-horizontal">  			
			<div class="panel panel-default">			
				<div class="panel-body clearfix" style="display: block;">	
					<div class="form-group">
						<label class="control-label col-lg-4">{l s='Text or html' mod='jmssliderlayer'}</label>
						<div class="col-lg-8">	
							<textarea name="layer_text_new" id="layer_text_new" rows="6" cols="30" class="form-control"></textarea>
						</div>	
					</div>
				</div>
			</div>	
			<button class="btn btn-default" id="submittext">{l s='Add' mod='jmssliderlayer'}</button> 			
         </div>
		 <div id="imgdialog" title="{l s='Add Layer Image' mod='jmssliderlayer'}">  			
			<table cellpadding="5" cellspacing="5" style="border-collapse:separate;border-spacing:5px;">
				<tr>
					<td>
					<label class="control-label">{l s='Select Image' mod='jmssliderlayer'}</label>		
					</td>
					<td>
					<select name="layer_img_new" id="layer_img_new" class="fixed-width-xl">
						{foreach from=$layerimgs item=layerimg}	
							<option value="{$layerimg.id|escape:'html':'UTF-8'}" {if $layerimg.id == $layer.layer_img}selected="selected"{/if}>{$layerimg.id|escape:'html':'UTF-8'}</option>
						{/foreach}
					</select>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
					<button class="btn" id="submitimg">{l s='Add' mod='jmssliderlayer'}</button> 			
					</td>
				</tr>	
			</table>	
         </div>
		 <div id="videodialog" title="{l s='Add Layer Video' mod='jmssliderlayer'}">  		
			<table cellpadding="5" cellspacing="5" style="border-collapse:separate;border-spacing:5px;">
				<tr>
					<td><label class="control-label">{l s='Video Type' mod='jmssliderlayer'}</label></td>
					<td>
						<select name="layer_video_type" id="layer_video_type">
							<option value="vimeovideo">Vimeo Video</option>
							<option value="youtubevideo">Youtube Video</option>
						</select>
					</td>
				</tr>
				<tr>
					<td><label class="control-label">{l s='Video ID' mod='jmssliderlayer'}</label></td>
					<td><input type="text" name="layer_video_new" id="layer_video_new" /></td>
				</tr>
				<tr>
					<td><label class="control-label">{l s='Video Width' mod='jmssliderlayer'}</label></td>
					<td><input type="text" name="video_width_new" id="video_width_new" /></td>
				</tr>
				<tr>
					<td><label class="control-label">{l s='Video Height' mod='jmssliderlayer'}</label></td>
					<td><input type="text" name="video_height_new" id="video_height_new" /></td>
				</tr>
				<tr>
					<td></td>
					<td>
					<button class="btn" id="submitvideo">{l s='Add' mod='jmssliderlayer'}</button>
					</td>
				</tr>	
			</table>	
			
         </div>
		<div id="slidesContent" class="col-xs-12 col-sm-5 col-md-5 col-cmd-12">
			<div id="layers" class="panel panel-default">				
				<h3 class="panel-heading">{l s='Layer List' mod='jmssliderlayer'}</h3>			
				<div class="panel-body clearfix" style="display: block;" id="layers">	
				{if $layers|@count gt 0}
				{foreach from=$layers item=layer key=l_index}
					<div id="layers_{$layer.layer_id|escape:'html':'UTF-8'}" class="panel layer {if $l_index ==0}active{/if}">
						<div class="row">
							<div class="col-lg-1">
								<span><i class="icon-arrows "></i></span>
							</div>
							<div class="col-md-7">							
								#{$layer.layer_id|escape:'html':'UTF-8'} - {$layer.title|escape:'htmlall':'UTF-8'}
							</div>						
							<div class="col-md-4">							
								<div class="btn-group-action pull-right">
									<a class="btn btn-default" onclick="if(confirm('Are you sure want to remove this layer?')) { document.location='{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=jmssliderlayer&delete_id_layer={$layer.layer_id|escape:'html':'UTF-8'}&id_slide={$current_slide.id_slide|escape:'html':'UTF-8'}'; } else { return true;}">
									<i class="icon-trash"></i>
									{l s='Delete' mod='jmssliderlayer'}
									</a>													
								</div>
							</div>
						</div>
					</div>
				{/foreach}
				{else}
					{l s='There is no layer for this slide' mod='jmssliderlayer'}
				{/if}
				</div>	
			</div>
		</div>
	</div>	
	<input type="hidden" name="selected_id" id="selected_id" value="{$layers[0].layer_id|escape:'html':'UTF-8'}" />
	<input type="hidden" name="slide_id" id="slide_id" value="{$current_slide.id_slide|escape:'html':'UTF-8'}" />
	<input type="hidden" name="site_url" id="site_url" value="{$root_url|escape:'html':'UTF-8'}" />
	<input type="hidden" name="submitLayers" value="1" />
	<div class="panel-footer">		
		<a href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=jmssliderlayer" class="btn btn-default" href="#"><i class="process-icon-back"></i> {l s='Back to Slides List' mod='jmssliderlayer'}</a>
		<button class="btn btn-default pull-right" name="submitLayers" id="module_form_submit_btn" value="1" type="submit">
			<i class="process-icon-save"></i>{l s='Save Slide' mod='jmssliderlayer'} 
		</button>		
	</div>
</div>
</form>