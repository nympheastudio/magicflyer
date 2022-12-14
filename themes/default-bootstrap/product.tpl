<script>
{if $product->id == 117}
		
		window.location.href = "{$link->getPageLink('perso_coeur', true)|escape:'quotes':'UTF-8' }";
	
	{elseif $product->id == 173}
	
		window.location.href = "{$link->getPageLink('perso_papillon', true)|escape:'quotes':'UTF-8' }&color=argente";
	
	{elseif $product->id == 184}
	
		window.location.href = "{$link->getPageLink('perso_papillon', true)|escape:'quotes':'UTF-8' }&color=or";
	
	{elseif $product->id == 312}
	
		window.location.href = "{$link->getPageLink('personnalisation', true)|escape:'quotes':'UTF-8' }&color=metal";
	
	{elseif $product->id == 228}
	
		window.location.href = "{$link->getPageLink('personnalisation', true)|escape:'quotes':'UTF-8' }&id_produit=228";
		
	{elseif $product->id == 309}
	
		window.location.href = "{$link->getPageLink('personnalisation', true)|escape:'quotes':'UTF-8' }&id_produit=309";
		
	{elseif $product->id == 310}
	
		window.location.href = "{$link->getPageLink('personnalisation', true)|escape:'quotes':'UTF-8' }&id_produit=310";
	
	{elseif $product->id == 174 || $product->id ==183 || $product->id ==311}
	
		window.location.href = "{$link->getPageLink('perso_livret', true)|escape:'quotes':'UTF-8' }&id_produit="{$product->id};
		
	{else}
		{if $product->id_category_default != 74 && $product->id_category_default != 83 }
			
			window.location.href = "{$link->getPageLink('index', true)|escape:'quotes':'UTF-8' }";
			
		{/if}
{/if}
</script>








{include file="$tpl_dir./errors.tpl"}
{if $errors|@count == 0}
{if !isset($priceDisplayPrecision)}
{assign var='priceDisplayPrecision' value=2}
{/if}
{if !$priceDisplay || $priceDisplay == 2}
{assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL, 6)}
{assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(false, $smarty.const.NULL)}
{elseif $priceDisplay == 1}
{assign var='productPrice' value=$product->getPrice(false, $smarty.const.NULL, 6)}
{assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(true, $smarty.const.NULL)}
{/if}


{*foreach from=$customizationFields item='field' name='customizationFields'}
{if $field.type == 0}
{assign var='key' value='pictures_'|cat:$product->id|cat:'_'|cat:$field.id_customization_field}


{/if}
{/foreach}

{if isset($pictures.$key)}
<script>
$(document).ready(function(e){
	//e.preventDefault();
	$('#center_column').hide();
	$('#buy_block').submit();
});
</script>
{/if*}

<div class="content_scene_cat row"><!--
<div class="content_scene_cat_bg" style="background: url({$link->getCatImageLink($category->link_rewrite, $category->id_image,
    'category_default')|escape:'html':'UTF-8'}) right center no-repeat; background-size: cover; min-height: 320px;"    >-->

<div class="content_scene_cat_bg"  >
    <div class="col-sm-6 col-md-4 col-lg-3 block_infos">
            {if $product->online_only}
            <p class="online_only">{l s='Online only'}</p>
            {/if}
            <h2 itemprop="name" class="titre"><span>{$product->name|escape:'html':'UTF-8'}</span></h2>
            <p><a href="#" onclick="history.go(-1)" class="back_button scroll_btn"><span class="fa fa-angle-left"> </span> {l s=' back'}</a> </p>


    </div>

    {if isset($images) && count($images) > 0}
    <div id="blockphotodesktop">
        <div id="thumbs_list" class="">
            <div id="slider">
                <a href="#" class="control_next">></a>
                <a href="#" class="control_prev"><</a>
                <ul style="width: 100% !important;" id="thumbs_list_frame">
                    {if isset($images)}
                    {foreach from=$images item=image name=thumbnails}
                    {assign var=imageIds value="`$product->id`-`$image.id_image`"}
                    {if !empty($image.legend)}
                    {assign var=imageTitle value=$image.legend|escape:'html':'UTF-8'}
                    {else}
                    {assign var=imageTitle value=$product->name|escape:'html':'UTF-8'}
                    {/if}
                    <li  id="thumbnail_{$image.id_image}">
                        <img src="{$link->getImageLink($product->link_rewrite, $imageIds, 'thickbox_default')|escape:'html':'UTF-8'}">
                    </li>
                    {/foreach}
                    {/if}
                </ul>
            </div> 
        </div>
        {literal}
            <script>
                jQuery(document).ready(function ($) {

setInterval(function () {
moveRight();
}, 3000);

var slideCount = $('#slider ul li').length;
var slideWidth = $('#slider ul li').width();
var slideHeight = $('#slider ul li').height();
var sliderUlWidth = slideCount * slideWidth;

$('#slider').css({ width: slideWidth, height: slideHeight });

$('#slider ul').css({ width: sliderUlWidth, marginLeft: - slideWidth });

$('#slider ul li:last-child').prependTo('#slider ul');

function moveLeft() {
$('#slider ul').animate({
  left: + slideWidth
}, 200, function () {
  $('#slider ul li:last-child').prependTo('#slider ul');
  $('#slider ul').css('left', '');
});
};

function moveRight() {
$('#slider ul').animate({
  left: - slideWidth
}, 200, function () {
  $('#slider ul li:first-child').appendTo('#slider ul');
  $('#slider ul').css('left', '');
});
};

$('a.control_prev').click(function () {
moveLeft();
});

$('a.control_next').click(function () {
moveRight();
});

});    

            </script>

        {/literal}





    </div>
    {/if}<!-- end blockphotodesktop -->



</div>




</div>

<div itemscope itemtype="https://schema.org/Product">
    <meta itemprop="url" content="{$link->getProductLink($product)}">
    <div class="primary_block row">



        {if isset($adminActionDisplay) && $adminActionDisplay}
        <div id="admin-action" class="container">
            <p class="alert alert-info">{l s='This product is not visible to your customers.'}
                <input type="hidden" id="admin-action-product-id" value="{$product->id}" />
                <a id="publish_button" class="btn btn-default button button-small" href="#">
                    <span>{l s='Publish'}</span>
                </a>
                <a id="lnk_view" class="btn btn-default button button-small" href="#">
                    <span>{l s='Back'}</span>
                </a>
            </p>
            <p id="admin-action-result"></p>
        </div>
        {/if}
        {if isset($confirmation) && $confirmation}
        <p class="confirmation">
            {$confirmation}
        </p>
        {/if}


        <!-- left infos-->
            <!--
<div class="button-back"><i class="icon-chevron-left" aria-hidden="true"></i><input class="button" type="button" onclick="history.back()" value="{l s='Back'}"></input></div>
-->
            {if isset($images) && count($images) > 0}
            <div id="blockphotomobile" class="pb-left-column col-xs-12 col-sm-12 col-md-3">
                <div id='mySwipe' class='swipe'>
                    <div class='swipe-wrap'>
                        {foreach from=$images item=image name=thumbnails}
                        {assign var=imageIds value="`$product->id`-`$image.id_image`"}
                        {if !empty($image.legend)}
                        {assign var=imageTitle value=$image.legend|escape:'html':'UTF-8'}
                        {else}
                        {assign var=imageTitle value=$product->name|escape:'html':'UTF-8'}
                        {/if}
                        <div><img src="{$link->getImageLink($product->link_rewrite, $imageIds, 'thickbox_default')|escape:'html':'UTF-8'}" /></div>
                        {/foreach}
                    </div>

                </div>



                <!-- 				<div style='text-align:center;padding-top:20px;' class='navigation-swipe'>
<button onclick='mySwipe.next()'> << </button>  
<button onclick='mySwipe.prev()'> >> </button> 
</div> -->
            </div>







            {/if}<!-- end blockphotomobile -->






        </div> <!-- end pb-left-column -->


        <!-- end left infos-->
        <!-- center infos -->
        <div class="pb-center-column col-xs-12 col-sm-12 col-md-9">
            <div class="fixed-content">


{* TAB DETAILS - SPECIFICATION *}
<ul class="nav nav-tabs nav-tb">
    <li class="active"><a data-toggle="tab" href="#home">DETAILS</a></li>
    <li><a data-toggle="tab" href="#menu2">{l s='GABARITS'}</a></li>
</ul>

<div class="tab-content">
    
    
    
    
    
    
    

    <div id="home" class="tab-pane fade  in active"> <!-- THIRD TAB CONTENT -->

        <!-- Description -->
        <section class="page-product-box">
            {$product->description}
        </section>
        <!--end Description -->

    </div> <!-- END THIRD TAB CONTENT -->
    
    
    
    
    <div id="menu2" class="tab-pane fade "> <!-- FIRST TAB CONTENT -->
        {if isset($attachments) && $attachments}
<!--Download -->
<section class="page-product-box">
    {foreach from=$attachments item=attachment name=attachements}

        <h4><a href="{$link->getPageLink('attachment', true, NULL, "id_attachment={$attachment.id_attachment}")|escape:'html':'UTF-8'}">{$attachment.name|escape:'html':'UTF-8'}</a></h4>
        <p class="text-muted">{$attachment.description|escape:'html':'UTF-8'}</p>
        <a class="btn btn-default btn-block" href="{$link->getPageLink('attachment', true, NULL, "id_attachment={$attachment.id_attachment}")|escape:'html':'UTF-8'}">
            <i class="icon-download"></i>
            {l s="Download"} ({Tools::formatBytes($attachment.file_size, 2)})
        </a>
    {/foreach} 
</section>
<!--end Download -->
{/if}
    </div> <!-- END FIRST TAB CONTENT -->



</div>

{* END TAB DETAILS - SPECIFICATION *}	

{* QUANTITY IN STOCK *}
{* {if ($display_qties == 1 && !$PS_CATALOG_MODE && $PS_STOCK_MANAGEMENT && $product->available_for_order)}
<!-- number of item in stock -->
<p id="pQuantityAvailable"{if $product->quantity <= 0} style="display: none;"{/if}>
    <span id="quantityAvailable">{$product->quantity|intval}</span>
    <span {if $product->quantity > 1} style="display: none;"{/if} id="quantityAvailableTxt">{l s='Item'}</span>
    <span {if $product->quantity == 1} style="display: none;"{/if} id="quantityAvailableTxtMultiple">{l s='Items'}</span>
    </p>
    {/if} *}
    {* END *}

    <!-- availability or doesntExist
<p id="availability_statut"{if !$PS_STOCK_MANAGEMENT || ($product->quantity <= 0 && !$product->available_later && $allow_oosp) || ($product->quantity > 0 && !$product->available_now) || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none;"{/if}>
    {*<span id="availability_label">{l s='Availability:'}</span>*}
    <span id="availability_value" class="label{if $product->quantity <= 0 && !$allow_oosp} label-danger{elseif $product->quantity <= 0} label-warning{else} label-success{/if}">{if $product->quantity <= 0}{if $PS_STOCK_MANAGEMENT && $allow_oosp}{$product->available_later}{else}{l s='This product is no longer in stock'}{/if}{elseif $PS_STOCK_MANAGEMENT}{$product->available_now}{/if}</span>
        </p> -->

    {if $PS_STOCK_MANAGEMENT}
    {if !$product->is_virtual}{hook h="displayProductDeliveryTime" product=$product}{/if}
    <p style="display:none" class="warning_inline" id="last_quantities"{if ($product->quantity > $last_qties || $product->quantity <= 0) || $allow_oosp || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none"{/if} >{l s='Warning: Last items in stock!'}</p>
        {/if}
    <p id="availability_date"{if ($product->quantity > 0) || !$product->available_for_order || $PS_CATALOG_MODE || !isset($product->available_date) || $product->available_date < $smarty.now|date_format:'%Y-%m-%d'} style="display: none;"{/if}>
        <span id="availability_date_label">{l s='Availability date:'}</span>
        <span id="availability_date_value">{if Validate::isDate($product->available_date)}{dateFormat date=$product->available_date full=false}{/if}</span>
        </p>
        <!-- Out of stock hook -->
    <div id="oosHook"{if $product->quantity > 0} style="display: none;"{/if}>
        {$HOOK_PRODUCT_OOS}
    </div>

    {* {if isset($HOOK_EXTRA_RIGHT) && $HOOK_EXTRA_RIGHT}{$HOOK_EXTRA_RIGHT}{/if} *}

    {* USEFUL LINK - (Print & Send to FRIENDS) *}
    {* {if !$content_only}
    <!-- usefull links-->
    <ul id="usefull_link_block" class="clearfix no-print">
        {if $HOOK_EXTRA_LEFT}{$HOOK_EXTRA_LEFT}{/if}
        <li class="print">
            <a href="javascript:print();">
                {l s='Print'}
            </a>
        </li>
    </ul>
    {/if} *}

    {* end center -  *}
    
    
    
    

    </div>
    </div>
    <!-- end center infos-->
            
            
            
            
            
            
            
            
            
            
    {* RIGHT *}

    <div class="pb-right-column col-xs-12 col-sm-12 col-md-3">
        
        
        
                {* PRODUCT PRICE *}
                <div class="">
                    {if $product->show_price && !isset($restricted_country_mode) && !$PS_CATALOG_MODE}
                    <!-- prices -->
                    <div>
                        <p class="our_price_display" itemprop="offers" itemscope itemtype="https://schema.org/Offer">{strip}
                            {if $product->quantity > 0}<link itemprop="availability" href="https://schema.org/InStock"/>{/if}
                            {if $priceDisplay >= 0 && $priceDisplay <= 2}
                                                                        <span id="our_price_display"  itemprop="price" content="{$productPrice}">
                                                                            <b>{convertPrice price=$product->getPrice(false, $smarty.const.NULL)} {l s='HT / unit??'} </b> 
                                                                            ({convertPrice price=$productPrice|floatval} {l s='TTC'})
                                                                        </span>
                                                                        
                    <!-- {if $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label))}
{if $priceDisplay == 1} {l s='tax excl.'}{else} {l s='tax incl.'}{/if}-->
                    {/if}
                    <meta itemprop="priceCurrency" content="{$currency->iso_code}" />
                    {hook h="displayProductPriceBlock" product=$product type="price"}
                    {/if}
                    {/strip}</p>
                <p id="reduction_percent" {if $productPriceWithoutReduction <= 0 || !$product->specificPrice || $product->specificPrice.reduction_type != 'percentage'} style="display:none;"{/if}>{strip}
                <span id="reduction_percent_display">
                    {if $product->specificPrice && $product->specificPrice.reduction_type == 'percentage'}-{$product->specificPrice.reduction*100}%{/if}
                </span>
                {/strip}</p>
            <p id="reduction_amount" {if $productPriceWithoutReduction <= 0 || !$product->specificPrice || $product->specificPrice.reduction_type != 'amount' || $product->specificPrice.reduction|floatval ==0} style="display:none"{/if}>{strip}
            <span id="reduction_amount_display">
                {if $product->specificPrice && $product->specificPrice.reduction_type == 'amount' && $product->specificPrice.reduction|floatval !=0}
                -{convertPrice price=$productPriceWithoutReduction|floatval-$productPrice|floatval}
                {/if}
            </span>
            {/strip}</p>
        <p id="old_price"{if (!$product->specificPrice || !$product->specificPrice.reduction)} class="hidden"{/if}>{strip}
            {if $priceDisplay >= 0 && $priceDisplay <= 2}
            {hook h="displayProductPriceBlock" product=$product type="old_price"}
            <span id="old_price_display">
                <span class="price">{if $productPriceWithoutReduction > $productPrice}{convertPrice price=$productPriceWithoutReduction|floatval}{/if}</span>{if $productPriceWithoutReduction > $productPrice && $tax_enabled && $display_tax_label == 1} {if $priceDisplay == 1}{l s='tax excl.'}{else}{l s='tax incl.'}{/if}{/if}
            </span>
    {/if}
    {/strip}</p>
{if $priceDisplay == 2}
<br />
<span id="pretaxe_price">{strip}
    <span id="pretaxe_price_display">{convertPrice price=$product->getPrice(false, $smarty.const.NULL)}</span> {l s='tax excl.'}
    {/strip}</span>
{/if}
</div> <!-- end prices -->
{if $packItems|@count && $productPrice < $product->getNoPackPrice()}
    <p class="pack_price">{l s='Instead of'} <span style="text-decoration: line-through;">{convertPrice price=$product->getNoPackPrice()}</span></p>
    {/if}
    {if $product->ecotax != 0}
    <p class="price-ecotax">{l s='Including'} <span id="ecotax_price_display">{if $priceDisplay == 2}{$ecotax_tax_exc|convertAndFormatPrice}{else}{$ecotax_tax_inc|convertAndFormatPrice}{/if}</span> {l s='for ecotax'}
        {if $product->specificPrice && $product->specificPrice.reduction}
        <br />{l s='(not impacted by the discount)'}
        {/if}
    </p>
    {/if}
    {if !empty($product->unity) && $product->unit_price_ratio > 0.000000}
    {math equation="pprice / punit_price" pprice=$productPrice  punit_price=$product->unit_price_ratio assign=unit_price}
    <p class="unit-price"><span id="unit_price_display">{convertPrice price=$unit_price}</span> {l s='per'} {$product->unity|escape:'html':'UTF-8'}</p>
    {hook h="displayProductPriceBlock" product=$product type="unit_price"}
    {/if}
    {/if} {*close if for show price*}
    {hook h="displayProductPriceBlock" product=$product type="weight" hook_origin='product_sheet'}
    {hook h="displayProductPriceBlock" product=$product type="after_price"}
    <div class="clear"></div>
    </div> <!-- end content_prices -->
                {* END PRODUCT PRICE *}
        
        
        {if ($product->show_price && !isset($restricted_country_mode)) || isset($groups) || $product->reference || (isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS)}
       


				               {if isset($product) && $product->customizable}
<!--Customization -->
<div  class="page-product-box">
    <!-- Customizable products -->
    <form method="post" action="{$customizationFormTarget}" enctype="multipart/form-data" id="customizationForm" class="clearfix">
        
        {if $product->uploadable_files|intval}
        <div class="customizableProductsFile">
            <ul id="uploadable_files" class="clearfix">
                {counter start=0 assign='customizationField'}
                {foreach from=$customizationFields item='field' name='customizationFields'}
                {if $field.type == 0}
                <li class="customizationUploadLine{if $field.required} required{/if}">{assign var='key' value='pictures_'|cat:$product->id|cat:'_'|cat:$field.id_customization_field}
                  
				  {if isset($pictures.$key)}

					
	<div class="customizationUploadBrowse">
		{if substr(basename($pictures.$key), 0, 1) == 'P'}
			<a href="{$pic_dir}{$pictures.$key}" target="_blank" class="voir_fichier">{l s='Voir le fichier'}</a>
		{else}
			<img src="{$pic_dir}{$pictures.$key}" alt="" />
		{/if}
		<a href="{$link->getProductDeletePictureLink($product, $field.id_customization_field)|escape:'html':'UTF-8'}" title="{l s='Supprimer fichier'}" >
			<img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" class="customization_delete_icon" width="11" height="13" />
		</a>
	</div>
	
             {/if}
					
					
                    <div class="customizationUploadBrowse form-group">

					

					   <label class="customizationUploadBrowseDescription small_text">
                            {if !empty($field.name)}
                            {$field.name}
                            {else}
                            {l s='Please select an image file from your computer'}
                            {/if}
                            {if $field.required}<sup>*</sup>{/if}
                        </label>
                        <input type="file"  name="file{$field.id_customization_field}" id="img{$customizationField}" class="noUniform form-control customization_block_input {if isset($pictures.$key)}filled{/if}" />
						
						

                    </div>
                </li>
                {counter}
                {/if}
                {/foreach}
            </ul>
        </div>
        {/if}
        {if $product->text_fields|intval}
        <div class="customizableProductsText">
            <h5 class="product-heading-h5">{l s='Text'}</h5>
            <ul id="text_fields">
                {counter start=0 assign='customizationField'}
                {foreach from=$customizationFields item='field' name='customizationFields'}
                {if $field.type == 1}
                <li class="customizationUploadLine{if $field.required} required{/if}">
                    <label for ="textField{$customizationField}">
                        {assign var='key' value='textFields_'|cat:$product->id|cat:'_'|cat:$field.id_customization_field}
                        {if !empty($field.name)}
                        {$field.name}
                        {/if}
                        {if $field.required}<sup>*</sup>{/if}
                    </label>
                    <textarea name="textField{$field.id_customization_field}" class="form-control customization_block_input" id="textField{$customizationField}" rows="3" cols="20">{strip}
                        {if isset($textFields.$key)}
                        {$textFields.$key|stripslashes}
                        {/if}
                        {/strip}</textarea>
                </li>
                {counter}
                {/if}
                {/foreach}
            </ul>
        </div>
        {/if}
        <p id="customizedDatas">
            <input type="hidden" name="quantityBackup" id="quantityBackup" value="" />
            <input type="hidden" name="submitCustomizedDatas" value="1" />
            <button class="button btn btn-default button button-small" name="saveCustomization">
                <span>{l s='Save'}</span>
            </button>
            <span id="ajax-loader" class="unvisible">
                <img src="{$img_ps_dir}loader.gif" alt="loader" />
            </span>
        </p>
    </form>
    <p class="clear required"><sup>*</sup> {l s='required fields'}</p>
</div>
<!--end Customization -->
{/if}

	   <!-- add to cart form-->
        <form id="buy_block"{if $PS_CATALOG_MODE && !isset($groups) && $product->quantity > 0} class="hidden"{/if} action="{$link->getPageLink('cart')|escape:'html':'UTF-8'}" method="post">
            <!-- hidden datas -->
            <p class="hidden">
                <input type="hidden" name="token" value="{$static_token}" />
                <input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
                <input type="hidden" name="add" value="1" />
                <input type="hidden" name="id_product_attribute" id="idCombination" value="" />
            </p>
            <div class="box-info-product">
                <div class="product_attributes">
                    <!-- quantity wanted -->



                    <!--
<div class="row">


<div class="col-xs-6 col-lg-6 col-md-6">
<div class="cpick" id="cpick">
<label>{l s='Couleur'}</label>
<span id="plus_de_couleur"> voir </span>
<span id="moins_de_couleur"> cacher </span>
</div>
</div>
<script type="text/javascript">

$( document ).ready(function () {
$( "#attributes  fieldset:eq(1)" ).addClass( "press" );

//var att = $( "#attributes  fieldset:eq(1)" ).find( "label:eq(0)" ).remove();

});
</script>-->

                    {if !$PS_CATALOG_MODE}
                    <div id="quantity_wanted_p"{if (!$allow_oosp && $product->quantity <= 0) || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none;"{/if}>
                        <label for="quantity_wanted">{l s='Quantity'}</label>
                        <a>
                            <input style="top: -3px;position: relative;"  min="1" name="qty" id="quantity_wanted" class="text" value="{if isset($quantityBackup)}{$quantityBackup|intval}{else}{if $product->minimal_quantity > 1}{$product->minimal_quantity}{else}1{/if}{/if}" /></a>


                        <div class="cart_quantity_button clearfix">

                            <a href="#" data-field-qty="qty" class="btn btn-default button-plus product_quantity_up">
                                <span><i class="icon-plus"></i></span>
                            </a>

                            <a  href="#" data-field-qty="qty" class="btn btn-default button-minus product_quantity_down">
                                <span><i class="icon-minus"></i></span>
                            </a>

                        </div>


                        </div>
                        {/if}

                        <!-- FIN DE ROW
</div>
-->
                        <!-- minimal quantity wanted -->
                    <p class="small_text" id="minimal_quantity_wanted_p"{if $product->minimal_quantity <= 1 || !$product->available_for_order || $PS_CATALOG_MODE} style="display: none;"{/if}>
                        {l s='The minimum purchase order quantity for the product is'} <b id="minimal_quantity_label">{$product->minimal_quantity}</b>
                        </p>
                    <div class="reste">
                        {if isset($groups)}
                        <!-- attributes -->
                        <div id="attributes">
                            <div class="clearfix"></div>
                            {foreach from=$groups key=id_attribute_group item=group}
                            {if $group.attributes|@count}
                            <fieldset class="attribute_fieldset">
                                {if ($group.group_type != 'color')}

                                <label style="margin-bottom: 12px;" class="attribute_label" {if $group.group_type != 'color' && $group.group_type != 'radio'}for="group_{$id_attribute_group|intval}"{/if}>{$group.name|escape:'html':'UTF-8'}&nbsp;</label>
                                {/if}

                                {assign var="groupName" value="group_$id_attribute_group"}
                                <div class="attribute_list">
                                    {if ($group.group_type == 'select')}
                                    {*if ($group.group_type != 'size')}
                                    <label>{l s='Size'}</label>
                                    {/if*}
                                    <div class="selectdiv">

                                        <label>
                                            <select name="{$groupName}" class="attribute_select " id="group_{$id_attribute_group|intval}">
                                                {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                                <option value="{$id_attribute|intval}"{if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $group.default == $id_attribute} selected="selected"{/if} title="{$group_attribute|escape:'html':'UTF-8'}">{$group_attribute|escape:'html':'UTF-8'}</option>
                                                {/foreach}
                                            </select>
                                        </label>






                                    </div>
                                    {elseif ($group.group_type == 'color')}
                                    <div class="pick">
                                        <!-- <label style="attribute_label">couleur</label> -->
                                        <ul id="color_to_pick_list" class="clearfix">
                                            {assign var="default_colorpicker" value=""}
                                            {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                            {assign var='img_color_exists' value=file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}
                                            <li{if $group.default == $id_attribute} class="selected"{/if}>
                                                <a href="{$link->getProductLink($product)|escape:'html':'UTF-8'}" id="color_{$id_attribute|intval}" name="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" class="color_pick{if ($group.default == $id_attribute)} selected{/if}"{if !$img_color_exists && isset($colors.$id_attribute.value) && $colors.$id_attribute.value} style="background:{$colors.$id_attribute.value|escape:'html':'UTF-8'};"{/if} title="{$colors.$id_attribute.name|escape:'html':'UTF-8'}">
                                                    {if $img_color_exists}
                                                    <img src="{$img_col_dir}{$id_attribute|intval}.jpg" alt="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" title="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" width="20" height="20" />
                                                    {/if}
                                                </a>
                                                </li>
                                            {if ($group.default == $id_attribute)}
                                            {$default_colorpicker = $id_attribute}
                                            {/if}
                                            {/foreach}
                                        </ul>


                                    </div>

                                    <input type="hidden" class="color_pick_hidden" name="{$groupName|escape:'html':'UTF-8'}" value="{$default_colorpicker|intval}" />
                                    {elseif ($group.group_type == 'radio')}
                                    <ul>
                                        {foreach from=$group.attributes key=id_attribute item=group_attribute}
                                        <li>
                                            <input type="radio" class="attribute_radio" name="{$groupName|escape:'html':'UTF-8'}" value="{$id_attribute}" {if ($group.default == $id_attribute)} checked="checked"{/if} />
                                            <span>{$group_attribute|escape:'html':'UTF-8'}</span>
                                        </li>
                                        {/foreach}
                                    </ul>
                                    {/if}
                                </div> <!-- end attribute_list -->
                            </fieldset>
                            {/if}
                            {/foreach}
                        </div> <!-- end attributes -->
                        {/if}
                    </div> <!-- end product_attributes -->







                </div>
                

 
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                <div class="box-cart-bottom">





                    <div{if (!$allow_oosp && $product->quantity <= 0) || !$product->available_for_order || (isset($restricted_country_mode) && $restricted_country_mode) || $PS_CATALOG_MODE} class="unvisible"{/if}>
                        <p id="add_to_cart" class="buttons_bottom_block no-print">
                            <button type="submit" name="Submit" class="btn btn-sub">
                                <span>{if $content_only && (isset($product->customization_required) && $product->customization_required)}{l s='Customize'}{else}{l s='Add to cart'}{/if}</span>
                            </button>
                        </p>
                        </div>





                        <!-- COUNTDOWN -->
                        {if $product->quantity lte  0}
                        <div class='countdown_bloc hidden'><div>{l s='Sold out'}</div></div>	
                        {/if}


                        <div class="countdown_bloc" {if (!$allow_oosp && $product->quantity >0 ) || $PS_CATALOG_MODE} class="unvisible"{/if}>                 
                            {foreach from=$features item=feature}

                            {if $feature.id_feature == 22}
                            {if $feature.value != ""}
                            <div>
                                <div class='restock-product'>{l s='Restock in '}</div>	
                                <!--<span class="bientot-dispo">{l s="bientot disponible"}</span>-->
                                <div id="countdown1" ></div>
                                <!-- <div class="countdown-date secondes2"><span class="seconds"></span></span><span class="countdown-legend">{l s="Secondes"}</span></div>	 -->
                            </div>



                            {/if}
                            {/if}	

                            {/foreach}	
                        </div>


                        <script>
                            $(document).ready(function() {

                                var date_renseignee = '{$feature.value}';
                                var d = date_renseignee.split(',');
                                var datefin = d[0] + ',' + (parseInt(d[1]) ) + ',' + d[2];
                                console.log(datefin +  "{$lang_iso}");

                                var timenow = new Date().getTime() / 1000;
                                var timeend = new Date( datefin  ) / 1000;//en JS les mois debutent de 0 ?? 11, donc 9 est octobre

                                var string_day = 'Days';
                                var string_hours = 'Hours';
                                var string_minutes = 'Minutes';
                                var string_secondes = 'Seconds';

                                if ( "{$lang_iso}" == "fr" ){
                                    var string_day = 'Jours';
                                    var string_hours = 'Heures';
                                    var string_minutes = 'Minutes';
                                    var string_secondes = 'Secondes';
                                }

                                $('#countdown1').ClassyCountdown({
                                    end: timeend,
                                    now: timenow,
                                    labels: true,
                                    labelsOptions: {

                                        lang: {

                                            days: string_day,
                                            hours: string_hours,
                                            minutes: string_minutes,
                                            seconds: string_secondes

                                        },
                                    },

                                });
                            });
                        </script>




                        <!--fin countdown-->
















                        {if isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS}{$HOOK_PRODUCT_ACTIONS}{/if} 
                        </div> <!-- end box-cart-bottom -->
                </div> <!-- end box-info-product -->
                </form>
				
				
				

				
				
				
            {/if}
            </div> <!-- end pb-right-column-->
    <!-- pb-right-column-->
</div> <!-- end primary_block -->

{if !$content_only}
{if (isset($quantity_discounts) && count($quantity_discounts) > 0)}
<!-- quantity discount -->
{* <section class="page-product-box">
    <h3 class="page-product-heading">{l s='Volume discounts'}</h3>
    <div id="quantityDiscount">
        <table class="std table-product-discounts">
            <thead>
                <tr>
                    <th>{l s='Quantity'}</th>
                    <th>{if $display_discount_price}{l s='Price'}{else}{l s='Discount'}{/if}</th>
                    <th>{l s='You Save'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$quantity_discounts item='quantity_discount' name='quantity_discounts'}
                {if $quantity_discount.price >= 0 || $quantity_discount.reduction_type == 'amount'}
                {$realDiscountPrice=$productPriceWithoutReduction|floatval-$quantity_discount.real_value|floatval}
                {else}
                {$realDiscountPrice=$productPriceWithoutReduction|floatval-($productPriceWithoutReduction*$quantity_discount.reduction)|floatval}
                {/if}
                <tr id="quantityDiscount_{$quantity_discount.id_product_attribute}" class="quantityDiscount_{$quantity_discount.id_product_attribute}" data-real-discount-value="{convertPrice price = $realDiscountPrice}" data-discount-type="{$quantity_discount.reduction_type}" data-discount="{$quantity_discount.real_value|floatval}" data-discount-quantity="{$quantity_discount.quantity|intval}">
                    <td>
                        {$quantity_discount.quantity|intval}
                    </td>
                    <td>
                        {if $quantity_discount.price >= 0 || $quantity_discount.reduction_type == 'amount'}
                        {if $display_discount_price}
                        {if $quantity_discount.reduction_tax == 0 && !$quantity_discount.price}
                        {convertPrice price = $productPriceWithoutReduction|floatval-($productPriceWithoutReduction*$quantity_discount.reduction_with_tax)|floatval}
                        {else}
                        {convertPrice price=$productPriceWithoutReduction|floatval-$quantity_discount.real_value|floatval}
                        {/if}
                        {else}
                        {convertPrice price=$quantity_discount.real_value|floatval}
                        {/if}
                        {else}
                        {if $display_discount_price}
                        {if $quantity_discount.reduction_tax == 0}
                        {convertPrice price = $productPriceWithoutReduction|floatval-($productPriceWithoutReduction*$quantity_discount.reduction_with_tax)|floatval}
                        {else}
                        {convertPrice price = $productPriceWithoutReduction|floatval-($productPriceWithoutReduction*$quantity_discount.reduction)|floatval}
                        {/if}
                        {else}
                        {$quantity_discount.real_value|floatval}%
                        {/if}
                        {/if}
                    </td>
                    <td>
                        <span>{l s='Up to'}</span>
                        {if $quantity_discount.price >= 0 || $quantity_discount.reduction_type == 'amount'}
                        {$discountPrice=$productPriceWithoutReduction|floatval-$quantity_discount.real_value|floatval}
                        {else}
                        {$discountPrice=$productPriceWithoutReduction|floatval-($productPriceWithoutReduction*$quantity_discount.reduction)|floatval}
                        {/if}
                        {$discountPrice=$discountPrice * $quantity_discount.quantity}
                        {$qtyProductPrice=$productPriceWithoutReduction|floatval * $quantity_discount.quantity}
                        {convertPrice price=$qtyProductPrice - $discountPrice}
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</section> *}
{/if}

{* En savoir plus - Full description *}

{* {if isset($product) && $product->description}
<!-- More info -->
<section class="page-product-box">
    <h3 class="page-product-heading">{l s='More info'}</h3>
    <!-- full description -->
    <div  class="rte">{$product->description}</div>
</section>
<!--end  More info -->
{/if} *}

{* {if isset($packItems) && $packItems|@count > 0}
<section id="blockpack">
    <h3 class="page-product-heading">{l s='Pack content'}</h3>
    {include file="$tpl_dir./product-list.tpl" products=$packItems}
</section>
{/if} *}

{* <!--HOOK_PRODUCT_TAB -->
<section class="page-product-box">
    {$HOOK_PRODUCT_TAB}
    {if isset($HOOK_PRODUCT_TAB_CONTENT) && $HOOK_PRODUCT_TAB_CONTENT}{$HOOK_PRODUCT_TAB_CONTENT}{/if}
</section>
<!--end HOOK_PRODUCT_TAB --> *}


{if isset($accessories) && $accessories}
<!--Accessories -->
<section class="page-product-box">
    {* 	<h3 class="page-product-heading">{l s='Accessories'}</h3>
    <div class="block products_block accessories-block clearfix">
        <div class="block_content">
            <ul id="bxslider" class="bxslider clearfix">
                {foreach from=$accessories item=accessory name=accessories_list}
                {if ($accessory.allow_oosp || $accessory.quantity_all_versions > 0 || $accessory.quantity > 0) && $accessory.available_for_order && !isset($restricted_country_mode)}
                {assign var='accessoryLink' value=$link->getProductLink($accessory.id_product, $accessory.link_rewrite, $accessory.category)}
                <li class="item product-box ajax_block_product{if $smarty.foreach.accessories_list.first} first_item{elseif $smarty.foreach.accessories_list.last} last_item{else} item{/if} product_accessories_description">
                    <div class="product_desc">
                        <a href="{$accessoryLink|escape:'html':'UTF-8'}" title="{$accessory.legend|escape:'html':'UTF-8'}" class="product-image product_image">
                            <img class="lazyOwl" src="{$link->getImageLink($accessory.link_rewrite, $accessory.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{$accessory.legend|escape:'html':'UTF-8'}" width="{$homeSize.width}" height="{$homeSize.height}"/>
                        </a>
                        <div class="block_description">
                            <a href="{$accessoryLink|escape:'html':'UTF-8'}" title="{l s='More'}" class="product_description">
                                {$accessory.description_short|strip_tags|truncate:25:'...'}
                            </a>
                        </div>
                    </div>
                    <div class="s_title_block">
                        <h5 itemprop="name" class="product-name">
                            <a href="{$accessoryLink|escape:'html':'UTF-8'}">
                                {$accessory.name|truncate:20:'...':true|escape:'html':'UTF-8'}
                            </a>
                        </h5>
                        {if $accessory.show_price && !isset($restricted_country_mode) && !$PS_CATALOG_MODE}
                        <span class="price">
                            {if $priceDisplay != 1}
                            {displayWtPrice p=$accessory.price}
                            {else}
                            {displayWtPrice p=$accessory.price_tax_exc}
                            {/if}
                            {hook h="displayProductPriceBlock" product=$accessory type="price"}
                        </span>
                        {/if}
                        {hook h="displayProductPriceBlock" product=$accessory type="after_price"}
                    </div>
                    <div class="clearfix" style="margin-top:5px">
                        {if !$PS_CATALOG_MODE && ($accessory.allow_oosp || $accessory.quantity > 0) && isset($add_prod_display) && $add_prod_display == 1}
                        <div class="no-print">
                            <a class="exclusive button ajax_add_to_cart_button" href="{$link->getPageLink('cart', true, NULL, "qty=1&amp;id_product={$accessory.id_product|intval}&amp;token={$static_token}&amp;add")|escape:'html':'UTF-8'}" data-id-product="{$accessory.id_product|intval}" title="{l s='Add to cart'}">
                                <span>{l s='Add to cart'}</span>
                            </a>
                        </div>
                        {/if}
                    </div>
                </li>
                {/if}
                {/foreach}
            </ul>
        </div>
    </div> *}
</section>
<!--end Accessories -->
{/if}
{if isset($HOOK_PRODUCT_FOOTER) && $HOOK_PRODUCT_FOOTER}{$HOOK_PRODUCT_FOOTER}{/if}
<!-- description & features -->

{/if}






</div> <!-- itemscope product wrapper -->

<script>
    
    $( document ).ready(function () {
	
	
	
			if (  $('.voir_fichier')[0] ){
				
				
				$( '#quantity_wanted_p').show();
				$( '#minimal_quantity_wanted_p').show();
				
				$( '#add_to_cart button').show();
				$( '.customizationUploadBrowseDescription').hide();
				$( '.customization_block_input').hide();
				$( '#customizedDatas button').hide();
				$('.page-product-box p.clear').hide();
				
			}else{
				$( '#quantity_wanted_p').hide();
				$( '#minimal_quantity_wanted_p').hide();
				$( '#add_to_cart button').hide();
				$( '.customizationUploadBrowseDescription').show();
				$( '.customization_block_input').show();
				$( '#customizedDatas button').show();
				$('.page-product-box p.clear').show();
				
			}
			
	if(
			(window.innerWidth <= 769) || 
			(window.matchMedia && 
				window.matchMedia('only screen and (max-width: 640px)').matches
				)
			){
		// alert('fp mobile 3 width :'+window.innerWidth);
		$('#blockphotomobile').show();
		$('#blockphotodesktop').hide();
		$('#view_full_size').hide();
	}else{
		
		$('#blockphotomobile').hide();
		$('#blockphotodesktop').show();
		$('#view_full_size').show();
	}

/*	
$( '#add_to_cart button').hide();

	$("#customizationForm").bind('ajax:complete', function() {

         $( '#add_to_cart  button').show();


   });*/
        /*		var short_descr = $("#short_description_content").text();
		var short_descritpion = $("#short_description_content").text().length;
		if(short_descritpion >=  118 ){
		var limit = 25;
		var sub_short_desc = $("#short_description_content").text().substring(0,limit);
		$("#short_description_content").prepend("<span class='read'>" + sub_short_desc + "...</span>");
		$("#short_description_content").append("<span class='all'></span>");
		$("#short_description_content").append("<span class='more'>{l s='Read more'}</span>");
		$(".more").css({
			"color": "#c1c1c1",
			"font-weight": "bolder",
			"font-size":"16",
			"text-decoration":"underline",
			"display":"block",
			"cursor":"pointer"
		});
		}else{
			var a = $("#short_description_content").text();
			$("#short_description_content").prepend("<span>" + a +"</span>");
		}
		$("#short_description_content p").remove();
		$(".more").click(function(){
			$(".read , .more").hide();
			$(".all").html(short_descr);
		});

		$("#short_description_content p").remove();
		$(window).scroll(function (event) {
			$('.fixed-content').scrollTop($(this).scrollTop());
		});
*/

        $("#color_to_pick_list").appendTo("#cpick");


        if($.trim($("#color_to_pick_list").html())==''){
            $('#cpick').css('display','none');
        }


        function initColorBlock(){		
            $('#color_to_pick_list').css({
                'width':'100px',
                'height':'28px',
                'overflow':'hidden'
            });
            $('#moins_de_couleur').hide();
        }		
        initColorBlock();

        $('#plus_de_couleur').click(function(){

            $('#plus_de_couleur').hide();
            $('#moins_de_couleur').show();

            $('#color_to_pick_list').css({
                'width':'100%',
                'height':'100%',
                'overflow':''
            });

            $('#cpick').addClass('open');


        });		

        $('#moins_de_couleur').click(function(){

            $('#plus_de_couleur').show();
            initColorBlock();

            $('#cpick').removeClass('open');

        });

        if(
            (window.innerWidth <= 769) || 
            (window.matchMedia && 
             window.matchMedia('only screen and (max-width: 640px)').matches
            )
        ){
            // alert('fp mobile 3 width :'+window.innerWidth);
            $('#blockphotomobile').show();
            $('#blockphotodesktop').hide();
            $('#view_full_size').hide();
        }else{

            $('#blockphotomobile').hide();
            $('#blockphotodesktop').show();
            $('#view_full_size').show();
        }





















    });
</script>
{strip}
{if isset($smarty.get.ad) && $smarty.get.ad}
{addJsDefL name=ad}{$base_dir|cat:$smarty.get.ad|escape:'html':'UTF-8'}{/addJsDefL}
{/if}
{if isset($smarty.get.adtoken) && $smarty.get.adtoken}
{addJsDefL name=adtoken}{$smarty.get.adtoken|escape:'html':'UTF-8'}{/addJsDefL}
{/if}
{addJsDef allowBuyWhenOutOfStock=$allow_oosp|boolval}
{addJsDef availableNowValue=$product->available_now|escape:'quotes':'UTF-8'}
{addJsDef availableLaterValue=$product->available_later|escape:'quotes':'UTF-8'}
{addJsDef attribute_anchor_separator=$attribute_anchor_separator|escape:'quotes':'UTF-8'}
{addJsDef attributesCombinations=$attributesCombinations}
{addJsDef currentDate=$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}
{if isset($combinations) && $combinations}
{addJsDef combinations=$combinations}
{addJsDef combinationsFromController=$combinations}
{addJsDef displayDiscountPrice=$display_discount_price}
{addJsDefL name='upToTxt'}{l s='Up to' js=1}{/addJsDefL}
{/if}
{if isset($combinationImages) && $combinationImages}
{addJsDef combinationImages=$combinationImages}
{/if}
{addJsDef customizationId=$id_customization}
{addJsDef customizationFields=$customizationFields}
{addJsDef default_eco_tax=$product->ecotax|floatval}
{addJsDef displayPrice=$priceDisplay|intval}
{addJsDef ecotaxTax_rate=$ecotaxTax_rate|floatval}
{if isset($cover.id_image_only)}
{addJsDef idDefaultImage=$cover.id_image_only|intval}
{else}
{addJsDef idDefaultImage=0}
{/if}
{addJsDef img_ps_dir=$img_ps_dir}
{addJsDef img_prod_dir=$img_prod_dir}
{addJsDef id_product=$product->id|intval}
{addJsDef jqZoomEnabled=$jqZoomEnabled|boolval}
{addJsDef maxQuantityToAllowDisplayOfLastQuantityMessage=$last_qties|intval}
{addJsDef minimalQuantity=$product->minimal_quantity|intval}
{addJsDef noTaxForThisProduct=$no_tax|boolval}
{if isset($customer_group_without_tax)}
{addJsDef customerGroupWithoutTax=$customer_group_without_tax|boolval}
{else}
{addJsDef customerGroupWithoutTax=false}
{/if}
{if isset($group_reduction)}
{addJsDef groupReduction=$group_reduction|floatval}
{else}
{addJsDef groupReduction=false}
{/if}
{addJsDef oosHookJsCodeFunctions=Array()}
{addJsDef productHasAttributes=isset($groups)|boolval}
{addJsDef productPriceTaxExcluded=($product->getPriceWithoutReduct(true)|default:'null' - $product->ecotax)|floatval}
{addJsDef productPriceTaxIncluded=($product->getPriceWithoutReduct(false)|default:'null' - $product->ecotax * (1 + $ecotaxTax_rate / 100))|floatval}
{addJsDef productBasePriceTaxExcluded=($product->getPrice(false, null, 6, null, false, false) - $product->ecotax)|floatval}
{addJsDef productBasePriceTaxExcl=($product->getPrice(false, null, 6, null, false, false)|floatval)}
{addJsDef productBasePriceTaxIncl=($product->getPrice(true, null, 6, null, false, false)|floatval)}
{addJsDef productReference=$product->reference|escape:'html':'UTF-8'}
{addJsDef productAvailableForOrder=$product->available_for_order|boolval}
{addJsDef productPriceWithoutReduction=$productPriceWithoutReduction|floatval}
{addJsDef productPrice=$productPrice|floatval}
{addJsDef productUnitPriceRatio=$product->unit_price_ratio|floatval}
{addJsDef productShowPrice=(!$PS_CATALOG_MODE && $product->show_price)|boolval}
{addJsDef PS_CATALOG_MODE=$PS_CATALOG_MODE}
{if $product->specificPrice && $product->specificPrice|@count}
{addJsDef product_specific_price=$product->specificPrice}
{else}
{addJsDef product_specific_price=array()}
{/if}
{if $display_qties == 1 && $product->quantity}
{addJsDef quantityAvailable=$product->quantity}
{else}
{addJsDef quantityAvailable=0}
{/if}
{addJsDef quantitiesDisplayAllowed=$display_qties|boolval}
{if $product->specificPrice && $product->specificPrice.reduction && $product->specificPrice.reduction_type == 'percentage'}
{addJsDef reduction_percent=$product->specificPrice.reduction*100|floatval}
{else}{addJsDef reduction_percent=0}{/if}
{if $product->specificPrice && $product->specificPrice.reduction && $product->specificPrice.reduction_type == 'amount'}
{addJsDef reduction_price=$product->specificPrice.reduction|floatval}
{else}
{addJsDef reduction_price=0}
{/if}
{if $product->specificPrice && $product->specificPrice.price}
{addJsDef specific_price=$product->specificPrice.price|floatval}
{else}
{addJsDef specific_price=0}
{/if}
{addJsDef specific_currency=($product->specificPrice && $product->specificPrice.id_currency)|boolval} {* TODO: remove if always false *}
{addJsDef stock_management=$PS_STOCK_MANAGEMENT|intval}
{addJsDef taxRate=$tax_rate|floatval}
{addJsDefL name=doesntExist}{l s='This combination does not exist for this product. Please select another combination.' js=1}{/addJsDefL}
{addJsDefL name=doesntExistNoMore}{l s='This product is no longer in stock' js=1}{/addJsDefL}
{addJsDefL name=doesntExistNoMoreBut}{l s='with those attributes but is available with others.' js=1}{/addJsDefL}
{addJsDefL name=fieldRequired}{l s='Please fill in all the required fields before saving your customization.' js=1}{/addJsDefL}
{addJsDefL name=uploading_in_progress}{l s='Uploading in progress, please be patient.' js=1}{/addJsDefL}
{addJsDefL name='product_fileDefaultHtml'}{l s='No file selected' js=1}{/addJsDefL}
{addJsDefL name='product_fileButtonHtml'}{l s='Choose File' js=1}{/addJsDefL}
{/strip}
{/if}


