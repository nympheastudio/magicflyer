</div> <!--  close headerRight like topmenu -->
<div>
{foreach from=$slides item=slide}
	<div class="opartslideshowClear"></div>
	<div class="opartslideshow" id="opartslideshow_{$slide.id_opartslideshow_slideshow}">
	{if !empty($slide.images)}
		{foreach from=$slide.images item=image}
			<a href="{$image.targeturl}">
				<img src="{$base_dir}modules/opartslideshow/upload/{$image.filename}" width="{$slide.width}" height="{$slide.height}" alt="" />
				<span>
					{$image.description}
				</span>
			</a>
		{/foreach}
	{/if}
	</div>
{/foreach}

<script type="text/javascript">
	$(document).ready(function() {
		{foreach from=$slides item=slide}
			$('#opartslideshow_{$slide.id_opartslideshow_slideshow}').coinslider({ 
				'width': {$slide.width}, 
				'height': {$slide.height},  
				'spw' : {$slide.spw},
				'sph' : {$slide.sph},
				'delay' : {$slide.delay},
				'sDelay' : {$slide.sDelay},
				'opacity' : {$slide.opacity},
				'titleSpeed' : {$slide.titleSpeed},
				'effect' : '{$effectNames[$slide.effect]}',
				'navigation' : {if $slide.navigation==1}true{else}false{/if},
				'links' : {if $slide.links==1}true{else}false{/if},
				'hoverPause' : {if $slide.hoverpause==1}true{else}false{/if}
			});
		{/foreach}
	});
</script>