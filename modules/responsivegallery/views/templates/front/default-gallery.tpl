{*
 *@license
*}
{capture name=path}

    <a href="{$link->getModuleLink('responsivegallery')|escape:'html':'UTF-8'}">{l s='Photo Gallery Responsive' mod='responsivegallery'}</a>
    <span class="navigation-pipe">{$navigationPipe|escape:'html':'UTF-8'}</span>
    {$RG_TITLE|escape:'html':'UTF-8'}
{/capture}
<div class="container marge_top">
<h1>{$RG_TITLE|escape:'html':'UTF-8'}</h1>

<div>{$RG_PREAMBLE}{* HTML description cannot escape *}</div>

<ul id="gallery">

	{foreach from=$gallery_items item='item'}
		{include file="./gallery-item.tpl" item=$item page=1}
	{/foreach}

</ul>

<script type="text/javascript">

	if(typeof kiwik === "undefined"){
		kiwik = {ldelim}{rdelim};
	}
	if(typeof kiwik.responsivegallery ==="undefined"){
		kiwik.responsivegallery = {ldelim}{rdelim};
	}
	//Configuration
	kiwik.responsivegallery.GALLERY_SELECTOR="#gallery";
	kiwik.responsivegallery.FADEIN_SPEED = {$RG_FADEIN_SPEED|intval};
	kiwik.responsivegallery.VERTICAL_MARGIN = {$RG_VERTICAL_MARGIN|intval};
	kiwik.responsivegallery.HORIZONTAL_MARGIN = {$RG_HORIZONTAL_MARGIN|intval};
	kiwik.responsivegallery.INNER_MARGIN = {$RG_INNER_MARGIN|intval};
	kiwik.responsivegallery.RG_CURRENT_PAGE = {$RG_CURRENT_PAGE|intval};
	kiwik.responsivegallery.FANCYBOX_ENABLED = true;
	kiwik.responsivegallery.RG_LEGEND_ON_PHOTO = {$RG_LEGEND_ON_PHOTO|intval};
	//Configuration des différents seuils
	kiwik.responsivegallery.SEUILS = {ldelim}
		0:{ldelim}'maxWidth':{$RG_BREAKPOINT_1|intval}, 'nbPerLine':{$RG_NB_ITEM_1|intval}{rdelim},
		1:{ldelim}'maxWidth':{$RG_BREAKPOINT_2|intval}, 'nbPerLine':{$RG_NB_ITEM_2|intval}{rdelim},
		2:{ldelim}'maxWidth':{$RG_BREAKPOINT_3|intval}, 'nbPerLine':{$RG_NB_ITEM_3|intval}{rdelim},
		3:{ldelim}'maxWidth':{$RG_BREAKPOINT_4|intval}, 'nbPerLine':{$RG_NB_ITEM_4|intval}{rdelim},
		4:{ldelim}'maxWidth':{$RG_BREAKPOINT_5|intval}, 'nbPerLine':{$RG_NB_ITEM_5|intval}{rdelim}
	{rdelim};
	kiwik.responsivegallery.current_gallery = {$current_gallery|intval};

</script>
