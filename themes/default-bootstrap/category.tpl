{include file="$tpl_dir./errors.tpl"}
{if isset($category)}
	{if $category->id AND $category->active}
		{if $scenes || $category->description || $category->id_image}
			<div class="content_scene_cat row">
				{if $scenes}
				<div class="content_scene"   {if $category->id_image}
					style="background:url({$link->getCatImageLink($category->link_rewrite, $category->id_image,
					'category_default')|escape:'html':'UTF-8'})  no-repeat scroll right / cover;
					min-height:{$categorySize.height}px;"{/if}>
					{include file="$tpl_dir./scenes.tpl" scenes=$scenes}
					{if $category->description}
					<div class="cat_desc rte">
						{if Tools::strlen($category->description) > 30}
						<div id="category_description_short">{$description_short}</div>
						<div id="category_description_full" class="unvisible">{$category->description}</div>
						<a href="{$link->getCategoryLink($category->id_category, $category->link_rewrite)|escape:'html':'UTF-8'}"
							class="lnk_more">{l s='More'}</a>
						{else}
						<div>{$category->description}</div>
						{/if}
						<p><a href="#" href="javascript:history.go(-1)"  class="back_button scroll_btn"><span class="fa fa-angle-left"></span>{l s='back'}</a></p>
					</div><!--onclick="history.go(-1)"-->
					{/if}
				</div>
				{else}
				<div class="content_scene_cat_bg"  {if $category->id_image}
					style="background:url({$link->getCatImageLink($category->link_rewrite, $category->id_image,
					'category_default')|escape:'html':'UTF-8'})   no-repeat scroll right / cover;
					min-height:{$categorySize.height}px;"{/if}>
					<div class="bg_video">
						
						{if $category->id == '51'}
							<!-- Tous papillons -->
				
						{elseif $category->id == '59'}
							<!-- FLUOS -->
							<div class="video_impulsion">
								<video  width="100%" height="100%" loop muted autoplay poster="https://www.magicflyer.com/themes/default-bootstrap/img/video/Papillons-Fluos-Festifs.jpg">
									<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Papillons-Fluos-Festifs.webm" type="video/webm"> 
									<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Papillons-Fluos-Festifs.mp4" type="video/mp4"> 
									<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Papillons-Fluos-Festifs.ogv" type="video/ogg"> 
									Votre navigateur ne permet pas de lire les vid??os HTML5. 
								</video>
								
								
							</div>
						{elseif $category->id == '56' || $category->id == '71'}
							<!--metaux precieux -->
							<div class="video_metaux">
								<video  width="100%" height="100%" loop muted autoplay poster="https://www.magicflyer.com/themes/default-bootstrap/img/video/Papillon-Metaux-Precieux.jpg">
									<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Papillon-Metaux-Precieux.webm" type="video/webm"> 
									<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Papillon-Metaux-Precieux.mp4" type="video/mp4"> 
									<source src="https://www.magicflyer.com/themes/default-bootstrap/img/video/Papillon-Metaux-Precieux.ogv" type="video/ogg"> 
									Votre navigateur ne permet pas de lire les vid??os HTML5. 
								</video>
								
								
							</div>


						{elseif $category->id == '60'}
							<!-- Nature sauvage -->
						{elseif $category->id == '58'}
							<!--Plume -->
						{elseif $category->id == '57'}
							<!--fleur baroque -->
						{elseif $category->id == '55'}
							<!--new concept -->
						{elseif $category->id == '68'}
							<!--IMPULSION -->
						{elseif $category->id == '65'}
							<!--Promotions -->
							
						{elseif $category->id == '72'}
							<!-- Carte perso -->
						<div class="header_perso"><img src="http://www.magicflyer.com/img/cms/2020/Bandeau-Cartes-personnalisees-1600x290.jpg" alt="Personnalisez votre carte papillon !" /></div>
							
							
							
						{/if}
					</div>




					{if $category->description}
					<div class="cat_desc">
						<span class="category-name">
							{strip}
							{$category->name|escape:'html':'UTF-8'}
							{if isset($categoryNameComplement)}
							{$categoryNameComplement|escape:'html':'UTF-8'}
							{/if}
							{/strip}
						</span>
						{if Tools::strlen($category->description) > 30}
						<div id="category_description_short" class="rte">{$description_short}</div>
						<div id="category_description_full" class="unvisible rte">{$category->description}</div>
						<a href="{$link->getCategoryLink($category->id_category, $category->link_rewrite)|escape:'html':'UTF-8'}"
							class="lnk_more">{l s='More'}</a>
						{else}
						<div class="rte">{$category->description}</div>
						
						{/if}
						
						<p><a href="javascript:history.go(-1)"  class="back_button scroll_btn"><span class="fa fa-angle-left"></span>{l s='back'}</a></p>
					</div>
					{/if}
					
				</div>
				{/if}

			</div>
		{/if}




		<!--<h1 class="page-heading{if (isset($subcategories) && !$products) || (isset($subcategories) && $products) || !isset($subcategories) && $products} product-listing{/if}"><span class="cat-name">{$category->name|escape:'html':'UTF-8'}{if isset($categoryNameComplement)}&nbsp;{$categoryNameComplement|escape:'html':'UTF-8'}{/if}</span></h1>-->

		{if isset($subcategories)}
			{if (isset($display_subcategories) && $display_subcategories eq 1) || !isset($display_subcategories) }
			<!-- Subcategories -->
			<div id="subcategories">
				<ul class="clearfix">
					{foreach from=$subcategories item=subcategory}
					<li class="col-sm-4 col-xs-6">
						<div class="subcategory-image">
							<a href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}"
								title="{$subcategory.name|escape:'html':'UTF-8'}" class="img">
								{if $subcategory.id_image}
								<img class="replace-2x"
									src="{$link->getCatImageLink($subcategory.link_rewrite, $subcategory.id_image, 'medium_default')|escape:'html':'UTF-8'}"
									alt="{$subcategory.name|escape:'html':'UTF-8'}" width="{$mediumSize.width}"
									height="{$mediumSize.height}" />
								{else}
								<img class="replace-2x" src="{$img_cat_dir}{$lang_iso}-default-medium_default.jpg"
									alt="{$subcategory.name|escape:'html':'UTF-8'}" width="{$mediumSize.width}"
									height="{$mediumSize.height}" />
								{/if}
								
								{if $subcategory.description}
								<div class="cat_desc">{$subcategory.description}</div>
								{/if}
							</a>
						</div>
						<h5><a class="subcategory-name"
								href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}">{$subcategory.name|truncate:100:'...'|escape:'html':'UTF-8'}</a>
						</h5>
					</li>
					{/foreach}
				</ul>
			</div>


			{/if}
		{else}
			

		<div id="right_column" class="col-xs-12">
			{$HOOK_RIGHT_COLUMN}
			{include file="./modules/blocklayered/blocklayered.tpl"}
		</div>



			{if $products}
				




				{if $category->id == 71}
				<p class="page-heading">{l s='Papillon magique description pack festif'}</p>
				{/if}




				{include file="./product-list.tpl" products=$products}

			{/if}

		{/if}





	{elseif $category->id}
		<p class="alert alert-warning">{l s='This category is currently unavailable.'}</p>
	{/if}
{/if}