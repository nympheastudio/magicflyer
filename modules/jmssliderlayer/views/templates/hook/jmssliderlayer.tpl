{*
 * @package Jms Slider Layer
 * @version 1.0
 * @Copyright (C) 2009 - 2015 Joommasters.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Website: http://www.joommasters.com
*}
{if $config.JMS_SL_GOOGLEFONT}
<link href='http://fonts.googleapis.com/css?family={$config.JMS_SL_GOOGLEFONT|escape:'html':'UTF-8'}' rel='stylesheet' type='text/css' />
{/if}
<div id="jmssliderlayer" {if $config.JMS_SL_CLASSSUFFIX}class="{$config.JMS_SL_CLASSSUFFIX|escape:'html':'UTF-8'}"{/if}>
<div class="tp-banner-container"><div class="tp-banner">	
<ul>
{foreach from=$slides item=slide name=jmssliderlayer}
	<li {if $slide.transition}data-transition="{$slide.transition|escape:'html':'UTF-8'}"{/if} data-slotamount="{$slide.slotamount|escape:'html':'UTF-8'}" data-masterspeed="{$slide.masterspeed|escape:'html':'UTF-8'}" {if $slide.thumb_img|escape:'html':'UTF-8'}data-thumb="{$root_url|escape:'html':'UTF-8'}modules/jmssliderlayer/views/img/thumbs/{$slide.thumb_img|escape:'html':'UTF-8'}"{else}data-thumb="{$root_url|escape:'html':'UTF-8'}modules/jmssliderlayer/views/img/thumb_{$slide.main_img|escape:'html':'UTF-8'}"{/if} data-saveperformance="on"  data-title="{$slide.title|escape:'html':'UTF-8'}">
		<!-- MAIN IMAGE -->
		<img src="{$root_url|escape:'html':'UTF-8'}modules/jmssliderlayer/views/img/dummy.png" {if $slide.bg_type}data-lazyload="{$root_url|escape:'html':'UTF-8'}modules/jmssliderlayer/views/img/{$slide.main_img|escape:'html':'UTF-8'}"{else}style="background-color:{$slide.bg_color|escape:'html':'UTF-8'}"{/if}  {if $slide.bgposition}data-bgposition="{$slide.bgposition|escape:'html':'UTF-8'}"{/if} {if $slide.kenburns}data-kenburns="on"{/if} {if $slide.bgpositionend}data-bgpositionend="{$slide.bgpositionend|escape:'html':'UTF-8'}"{/if} data-bgfit="{$slide.bgfit|escape:'html':'UTF-8'}" {if $slide.bgfitend}data-bgfitend="{$slide.bgfitend|escape:'html':'UTF-8'}"{/if} data-bgrepeat="{if $slide.bgrepeat}repeat{else}no-repeat{/if}">
		<!-- LAYERS -->
		{foreach from=$slide.layers item=layer name=jmssliderlayer key=l_index}
			<div class="tp-caption {if $layer.data_type=='vimeovideo' || $layer.data_type=='youtubevideo'}tp-fade{/if} {if $layer.data_type=='text' || $layer.layer_class=='arrowicon' || $layer.layer_class=='fullrounded'}{$layer.layer_class|escape:'html':'UTF-8'}{/if} {if $layer.incoming_class}{$layer.incoming_class|escape:'html':'UTF-8'}{/if} {if $layer.outgoing_class}{$layer.outgoing_class|escape:'html':'UTF-8'}{/if} {if $layer.customin}customin{/if} {if $layer.customout}customout{/if} {if $layer.special_class}{$layer.special_class|escape:'html':'UTF-8'}{/if} {if $layer.video_fullscreen}fullscreenvideo{/if} {if $layer.parallax_class}{$layer.parallax_class|escape:'html':'UTF-8'}{/if}"
			data-x="{$layer.data_x|escape:'html':'UTF-8'}"
			data-y="{$layer.data_y|escape:'html':'UTF-8'}"
			data-speed="{$layer.speed|escape:'html':'UTF-8'}"
			{if $layer.start}
			data-start="{$layer.start|escape:'html':'UTF-8'}"
			{/if}
			{if $layer.end}
			data-end="{$layer.end|escape:'html':'UTF-8'}"
			{/if}
			{if $layer.customin}
			data-customin="{$layer.customin|escape:'html':'UTF-8'}"
			{/if}
			{if $layer.customout}
			data-customout="{$layer.customout|escape:'html':'UTF-8'}"
			{/if}
			data-easing="{$layer.easing|escape:'html':'UTF-8'}"
			{if $layer.splitin}
			data-splitin="{$layer.splitin|escape:'html':'UTF-8'}"
			{/if}
			{if $layer.splitout}
			data-splitout="{$layer.splitout|escape:'html':'UTF-8'}"
			{/if}
			data-elementdelay="{$layer.elementdelay|escape:'html':'UTF-8'}"
			data-endelementdelay="{$layer.endelementdelay|escape:'html':'UTF-8'}"
			{if $layer.endspeed}
			data-endspeed="{$layer.endspeed|escape:'html':'UTF-8'}"
			{/if}
			{if $layer.linktoslide} 
			data-linktoslide="{$layer.linktoslide|escape:'html':'UTF-8'}"
			{/if}
			{if $layer.data_type=='vimeovideo' || $layer.data_type=='youtubevideo'}
			data-autoplay="{if $layer.video_autoplay}true{else}false{/if}"
			data-autoplayonlyfirsttime="false"
			{/if}
			style="z-index:{$l_index|escape:'html':'UTF-8' + 1};">
			{if $layer.data_type=='text'}
				{$layer.layer_text|escape:'html':'UTF-8'}
			{elseif $layer.data_type=='img'}
				<img src="{$root_url|escape:'html':'UTF-8'}modules/jmssliderlayer/views/img/dummy.png" {if $layer.img_hh}data-hh="{$layer.img_hh|escape:'html':'UTF-8'}"{/if} {if $layer.img_ww}data-ww="{$layer.img_ww|escape:'html':'UTF-8'}"{/if} alt="" data-lazyload="{$root_url|escape:'html':'UTF-8'}modules/jmssliderlayer/views/img/layers/{$layer.layer_img|escape:'html':'UTF-8'}" />
			{elseif $layer.data_type=='vimeovideo'}	
				<iframe src='http://player.vimeo.com/video/{$layer.layer_video|escape:'html':'UTF-8'}?title=0&byline=0&portrait=0;api=1' {if $layer.video_fullscreen}width='100%' height='100%' style='width:100%;height:100%;'{else}width='{$layer.video_width|escape:'html':'UTF-8'}px' height='{$layer.video_height|escape:'html':'UTF-8'}px' style='width:{$layer.video_width|escape:'html':'UTF-8'}px;height:{$layer.video_height|escape:'html':'UTF-8'}px;'{/if}></iframe>
			{else}
				<iframe src="http://www.youtube.com/embed/{$layer.layer_video|escape:'html':'UTF-8'}?enablejsapi=1&html5=1&hd=1&wmode=opaque&controls=1&showinfo=0;rel=0;" {if $layer.video_fullscreen}width='100%' height='100%' style='width:100%;height:100%;'{else}width='{$layer.video_width|escape:'html':'UTF-8'}px' height='{$layer.video_height|escape:'html':'UTF-8'}px' style='width:{$layer.video_width|escape:'html':'UTF-8'}px;height:{$layer.video_height|escape:'html':'UTF-8'}px;'{/if}></iframe>	
			{/if}
			</div>
		{/foreach}	
	</li>
{/foreach}
</ul>
<div class="tp-bannertimer {if $config.JMS_SL_TIMERBARPOS =='bottom'}tp-bottom{/if}"></div>
</div></div>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.tp-banner').show().revolution(
	{
		dottedOverlay:"none",
		delay:{$config.JMS_SL_DELAY|escape:'html':'UTF-8'},
		startwidth:{$config.JMS_SL_STARTWIDTH|escape:'html':'UTF-8'},
		startheight:{$config.JMS_SL_STARTHEIGHT|escape:'html':'UTF-8'},
		hideThumbs:200,
		thumbWidth:{$config.JMS_SL_THUMBWIDTH|escape:'html':'UTF-8'},
		thumbHeight:{$config.JMS_SL_THUMBHEIGHT|escape:'html':'UTF-8'},
		thumbAmount:5,
		navigationType:"{$config.JMS_SL_NAVIGATIONTYPE|escape:'html':'UTF-8'}",
		navigationArrows:"{$config.JMS_SL_NAVIGATIONARROWS|escape:'html':'UTF-8'}",
		navigationStyle:"{$config.JMS_SL_NAVIGATIONSTYLE|escape:'html':'UTF-8'}",
		touchenabled:"{if $config.JMS_SL_TOUCHENABLED}on{else}off{/if}",
		onHoverStop:"{if $config.JMS_SL_ONHOVERSTOP}on{else}off{/if}",
		swipe_velocity: 0.7,
		swipe_min_touches: 1,
		swipe_max_touches: 1,
		drag_block_vertical: false,
		parallax:"mouse",
		parallaxBgFreeze:"on",
		parallaxLevels:[7,4,3,2,5,4,3,2,1,0],
		keyboardNavigation:"{if $config.JMS_SL_NAVIGATIONTYPE}on{else}off{/if}",
		navigationHAlign:"{$config.JMS_SL_NAVIGATIONHALIGN|escape:'html':'UTF-8'}",
		navigationVAlign:"{$config.JMS_SL_NAVIGATIONVALIGN|escape:'html':'UTF-8'}",
		navigationHOffset:{$config.JMS_SL_NAVIGATIONHOFFSET|escape:'html':'UTF-8'},
		navigationVOffset:{$config.JMS_SL_NAVIGATIONVOFFSET|escape:'html':'UTF-8'},
		soloArrowLeftHalign:"left",
		soloArrowLeftValign:"center",
		soloArrowLeftHOffset:20,
		soloArrowLeftVOffset:0,
		soloArrowRightHalign:"right",
		soloArrowRightValign:"center",
		soloArrowRightHOffset:20,
		soloArrowRightVOffset:0,
		shadow:{$config.JMS_SL_SHADOW|escape:'html':'UTF-8'},
		fullWidth:"{if $config.JMS_SL_MODE == 'fullwidth'}on{else}off{/if}",
		fullScreen:"{if $config.JMS_SL_MODE == 'fullscreen'}on{else}off{/if}",
		spinner:"{$config.JMS_SL_SPINNER|escape:'html':'UTF-8'}",
		stopLoop:"off",
		stopAfterLoops:-1,
		stopAtSlide:-1,
		shuffle:"off",
		autoHeight:"off",
		forceFullWidth:"off",
		hideThumbsOnMobile:"off",
		hideNavDelayOnMobile:1500,
		hideBulletsOnMobile:"off",
		hideArrowsOnMobile:"off",
		hideThumbsUnderResolution:0,
		hideSliderAtLimit:0,
		hideCaptionAtLimit:0,
		hideAllCaptionAtLilmit:0,
		startWithSlide:0,
		hideTimerBar:"{if $config.JMS_SL_HIDETIMERBAR}on{else}off{/if}",						
		fullScreenOffsetContainer: ""
	});
});	//ready
</script>
