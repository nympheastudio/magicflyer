{*
 *@license
*}
{capture name=path}
    {l s='Photo Gallery Responsive' mod='responsivegallery'}
{/capture}
<h1>{$RG_TITLE|escape:'html':'UTF-8'}</h1>

<div>{$RG_PREAMBLE}{* HTML cannot escape *}</div>

<div id="galleries">

	{foreach from=$galleries item='gallery'}
		<div class="gallery-single">
			<a href="{$gallery.link|escape:'html':'UTF-8'}" title="{$gallery.title|escape:'html':'UTF-8'}">
				<div class="img-wrapper">
					<img src="{$ps_base_uri|escape:'html':'UTF-8'}{$gallery.image|escape:'html':'UTF-8'}" alt="{$gallery.title|escape:'html':'UTF-8'}"/>	
				</div>
				<div class="title">{$gallery.title|escape:'html':'UTF-8'}</div>
			</a>
		</div>
	{/foreach}

</div>