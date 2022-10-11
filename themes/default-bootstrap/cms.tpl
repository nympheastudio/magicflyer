		{if $cms->id == 48}
			
			<script>window.location.href = "https://www.magicflyer.com/{$lang_iso}/content/65-boutique";</script>
			
		{/if}
		{if isset($cms) && !isset($cms_category)}
{if !$cms->active}
<br />
<div id="admin-action-cms">
<p>
<span>{l s='This CMS page is not visible to your customers.'}</span>
<input type="hidden" id="admin-action-cms-id" value="{$cms->id}" />
<input type="submit" value="{l s='Publish'}" name="publish_button" class="button btn btn-default"/>
<input type="submit" value="{l s='Back'}" name="lnk_view" class="button btn btn-default"/>
</p>
<div class="clear" ></div>
<p id="admin-action-result"></p>
</p>
</div>
{/if}
<div class="rte{if $content_only} content_only{/if}">
{$cms->content}
</div>



{elseif isset($cms_category)}
<div class="block-cms">
<h1><a href="{if $cms_category->id eq 1}{$base_dir}{else}{$link->getCMSCategoryLink($cms_category->id, $cms_category->link_rewrite)}{/if}">{$cms_category->name|escape:'html':'UTF-8'}</a></h1>


{if $cms_category->id == 7}	
{assign var='id_cms' value=48}
<div class="rte{if $content_only} content_only{/if}">
{$cms->content}
</div>
{else}

{if $cms_category->description}
<p>{$cms_category->description|escape:'html':'UTF-8'}</p>
{/if}


{if isset($sub_category) && !empty($sub_category)}	
<p class="title_block">{l s='List of sub categories in %s:' sprintf=$cms_category->name}</p>
<ul class="bullet list-group">
{foreach from=$sub_category item=subcategory}
<li>
<a class="list-group-item" href="{$link->getCMSCategoryLink($subcategory.id_cms_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}">{$subcategory.name|escape:'html':'UTF-8'}</a>
</li>
{/foreach}
</ul>
{/if}


{if isset($cms_pages) && !empty($cms_pages)}
<p class="title_block">{l s='List of pages in %s:' sprintf=$cms_category->name}</p>
<ul class="bullet list-group">
{foreach from=$cms_pages item=cmspages}
<li>
<a class="list-group-item" href="{$link->getCMSLink($cmspages.id_cms, $cmspages.link_rewrite)|escape:'html':'UTF-8'}">{$cmspages.meta_title|escape:'html':'UTF-8'}</a>
</li>
{/foreach}
</ul>
{/if}

{/if}



</div>


{else}
<div class="alert alert-danger">
{l s='This page does not exist.'}
</div>
{/if}


{if $cms->id == 49}
<h1>{l s='Prestige'}</h1>
<iframe src="//www.magicflyer.com/instagram.php" width="100%" height="3000px" scrolling="no" frameborder="0" allowtransparency="true">Browser not compatible.</iframe>
<style>
iframe{
    overflow:hidden;
}
</style>
{/if}



<!-- PAGE FAQ -->
{if $cms->id == 44}
<script type="text/javascript">
//alert('44');
// Execution de cette fonction lorsque le DOM sera entièrement chargé
$(document).ready(function() {
	// Masquage des réponses
	$("dd").hide();
	// CSS : curseur pointeur
	$("dt").css("cursor", "pointer");
	// Clic sur la question
	$("dt").click(function() {
		// Actions uniquement si la réponse n'est pas déjà visible
		if($(this).next().is(":visible") == false) {
			// Masquage des réponses
			$("dd").slideUp();
			// Affichage de la réponse placée juste après dans le code HTML
			$(this).next().slideDown();
		}
	});
});

// FIN DE FONCTION FAQ OPEN BLOCS		


</script>
{/if}



{literal}
<script type="text/javascript"> $(document).ready(function() { $("a.fancybox").fancybox(); }); </script>
{/literal}	

{if $cms->id == 57}
	<script language="javascript">
	$(document).ready(function() {
		let fixBugLaraFabianLastImgNotDisplayed = $('.row:last-child img').css('display','block');	
	});
	</script>
{/if}













{strip}
{if isset($smarty.get.ad) && $smarty.get.ad}
{addJsDefL name=ad}{$base_dir|cat:$smarty.get.ad|escape:'html':'UTF-8'}{/addJsDefL}
{/if}
{if isset($smarty.get.adtoken) && $smarty.get.adtoken}
{addJsDefL name=adtoken}{$smarty.get.adtoken|escape:'html':'UTF-8'}{/addJsDefL}
{/if}
{if isset($smarty.get.id_cms) && $smarty.get.id_cms}
{addJsDefL name=id_cms}{$smarty.get.id_cms|escape:'html':'UTF-8'}{/addJsDefL}
{/if}
{/strip}