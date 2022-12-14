

{capture name=path}{l s='Your shopping cart'}{/capture}

<h1 id="cart_title" class="page-heading">{l s='Shopping-cart summary'}</h1>

{if isset($account_created)}
<p class="alert alert-success">
	{l s='Your account has been created.'}
</p>
{/if}

{assign var='current_step' value='summary'}
{include file="$tpl_dir./order-steps.tpl"}
{include file="$tpl_dir./errors.tpl"}







{if isset($empty)}
<p class="alert alert-warning">{l s='Your shopping cart is empty.'}</p>
{elseif $PS_CATALOG_MODE}
<p class="alert alert-warning">{l s='This store has not accepted your new order.'}</p>
{else}
<p style="display:none" id="emptyCartWarning" class="alert alert-warning">{l s='Your shopping cart is empty.'}</p>
{if isset($lastProductAdded) AND $lastProductAdded}
<div class="cart_last_product">
	<div class="cart_last_product_header">
		<div class="left">{l s='Last product added'}</div>
	</div>
	<a class="cart_last_product_img" href="{$link->getProductLink($lastProductAdded.id_product, $lastProductAdded.link_rewrite, $lastProductAdded.category, null, null, $lastProductAdded.id_shop)|escape:'html':'UTF-8'}">
		<img src="{$link->getImageLink($lastProductAdded.link_rewrite, $lastProductAdded.id_image, 'small_default')|escape:'html':'UTF-8'}" alt="{$lastProductAdded.name|escape:'html':'UTF-8'}"/>
	</a>
	<div class="cart_last_product_content">
		<p class="product-name">
			<a href="{$link->getProductLink($lastProductAdded.id_product, $lastProductAdded.link_rewrite, $lastProductAdded.category, null, null, null, $lastProductAdded.id_product_attribute)|escape:'html':'UTF-8'}">
				{$lastProductAdded.name|escape:'html':'UTF-8'}
			</a>
		</p>
		{if isset($lastProductAdded.attributes) && $lastProductAdded.attributes}
		<small>
			<a href="{$link->getProductLink($lastProductAdded.id_product, $lastProductAdded.link_rewrite, $lastProductAdded.category, null, null, null, $lastProductAdded.id_product_attribute)|escape:'html':'UTF-8'}">
				{$lastProductAdded.attributes|escape:'html':'UTF-8'}
			</a>
		</small>
		{/if}
	</div>
</div>
{/if}
{assign var='total_discounts_num' value="{if $total_discounts != 0}1{else}0{/if}"}
{assign var='use_show_taxes' value="{if $use_taxes && $show_taxes}2{else}0{/if}"}
{assign var='total_wrapping_taxes_num' value="{if $total_wrapping != 0}1{else}0{/if}"}
{* eu-legal *}

<div id="order-detail-content" class="table_block table-responsive">
	<table id="cart_summary" class="table table-bordered {if $PS_STOCK_MANAGEMENT}stock-management-on{else}stock-management-off{/if}">
		
		<tfoot class="col-md-3 col-xs-12">
			
			
			{if $use_taxes}
			{if $priceDisplay}
			
			
			<tr class="title_summary_cart"><td colspan="2"><h2>{l s='Summary'}</h2><p>{hook h="displayBeforeShoppingCartBlock"}
			</p></td></tr>
			<tr class="cart_total_price">
				<td  colspan="3" id="cart_voucher" class="cart_voucher">
					{if $voucherAllowed}
					{if isset($errors_discount) && $errors_discount}
					<ul class="alert alert-danger">
						{foreach $errors_discount as $k=>$error}
						<li>{$error|escape:'html':'UTF-8'}</li>
						{/foreach}
					</ul>
					{/if}
					<form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher1">
						
						<fieldset>
							<p>{l s='Do you have a voucher ?'}</p>
							<input type="text" class="discount_name form-control" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
							<input type="hidden" name="submitDiscount" />
							<button type="submit" name="submitAddDiscount" class="button btn btn-default button-small"><span>{l s='OK'}</span></button>
						</fieldset>
					</form>
					{if $displayVouchers}
					<p id="title" class="title-offers">{l s='Take advantage of our exclusive offers:'}</p>
					<div id="display_cart_vouchers">
						{foreach $displayVouchers as $voucher}
						{if $voucher.code != ''}<span class="voucher_name" data-code="{$voucher.code|escape:'html':'UTF-8'}">{$voucher.code|escape:'html':'UTF-8'}</span> - {/if}{$voucher.name}<br />
						{/foreach}
					</div>
					{/if}
					{/if}
				</td>
				
			</tr>
			{else}
			<tr class="title_summary_cart"><td colspan="2"><h2>{l s='Summary'}</h2><p>{hook h="displayBeforeShoppingCartBlock"}</p></td></tr>
			<tr class="cart_total_price">
				<td  colspan="2" id="cart_voucher" class="cart_voucher">
					{if $voucherAllowed}
					{if isset($errors_discount) && $errors_discount}
					<ul class="alert alert-danger">
						{foreach $errors_discount as $k=>$error}
						<li>{$error|escape:'html':'UTF-8'}</li>
						{/foreach}
					</ul>
					{/if}
					<form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher2">
						<fieldset>
							<p>{l s='Do you have a voucher ?'}</p>
							<input type="text" class="discount_name form-control" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
							<input type="hidden" name="submitDiscount" />
							<button type="submit" name="submitAddDiscount" class="button btn btn-default button-small"><span>{l s='OK'}</span></button>
						</fieldset>
					</form>
					{if $displayVouchers}
					<p id="title" class="title-offers">{l s='Take advantage of our exclusive offers:'}</p>
					<div id="display_cart_vouchers">
						{foreach $displayVouchers as $voucher}
						{if $voucher.code != ''}<span class="voucher_name" data-code="{$voucher.code|escape:'html':'UTF-8'}">{$voucher.code|escape:'html':'UTF-8'}</span> - {/if}{$voucher.name}<br />
						{/foreach}
					</div>
					{/if}
					{/if}
				</td>
			</tr>
			{/if}
			{else}
			<tr class="title_summary_cart"><td colspan="2"><h2>{l s='Summary'}</h2><p>{hook h="displayBeforeShoppingCartBlock"}</p></td></tr>
			<tr class="cart_total_price">
				<td  colspan="2" id="cart_voucher" class="cart_voucher">
					{if $voucherAllowed}
					{if isset($errors_discount) && $errors_discount}
					<ul class="alert alert-danger">
						{foreach $errors_discount as $k=>$error}
						<li>{$error|escape:'html':'UTF-8'}</li>
						{/foreach}
					</ul>
					{/if}
					<form action="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}" method="post" id="voucher3">
						<fieldset>
							<p>{l s='Do you have a voucher ?'}</p>
							<input type="text" class="discount_name form-control" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
							<input type="hidden" name="submitDiscount" />
							<button type="submit" name="submitAddDiscount" class="button btn btn-default button-small">
								<span>{l s='OK'}</span>
							</button>
						</fieldset>
					</form>
					{if $displayVouchers}
					<p id="title" class="title-offers">{l s='Take advantage of our exclusive offers:'}</p>
					<div id="display_cart_vouchers">
						{foreach $displayVouchers as $voucher}
						{if $voucher.code != ''}<span class="voucher_name" data-code="{$voucher.code|escape:'html':'UTF-8'}">{$voucher.code|escape:'html':'UTF-8'}</span> - {/if}{$voucher.name}<br />
						{/foreach}
					</div>
					{/if}
					{/if}
				</td>
			</tr>
			{/if}
			
			<!-- voucher enregistr?? -->
			{if sizeof($discounts)}
			
			{*foreach $discounts as $discount}
			<tr class="cart_discount {if $discount@last}last_item{elseif $discount@first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
				<td colspan="1" class="cart_discount_name" ><a
					href="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}?deleteDiscount={$discount.id_discount}"
					class="price_discount_delete"
					title="{l s='Delete'}">
					<i class="icon-trash"></i>
				</a>{$discount.name}</td>
				
				<td colspan="1" class="cart_discount_price">
					<span class="price-discount price">{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}</span>
				</td>
			</tr>
			<tr>
				<td colspan="2"><hr/></td>
			</tr>
			{/foreach*}
			<tr>
				<td colspan="2"><div  class="discount_list">
					{foreach $discounts as $discount}
					<table><tr class="cart_discount {if $discount@last}last_item{elseif $discount@first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
						<td colspan="1" width="65%" class="cart_discount_name" ><a
							href="{if $opc}{$link->getPageLink('order-opc', true)}{else}{$link->getPageLink('order', true)}{/if}?deleteDiscount={$discount.id_discount}"
							class="price_discount_delete"
							title="{l s='Delete'}">
							<i class="icon-trash"></i>
						</a>{$discount.name}</td>
						
						<td colspan="1" width="35%" class="cart_discount_price">
							<span class="price-discount price">{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}</span>
						</td>
					</tr>
					<tr>
						<td colspan="2"><hr/></td>
					</tr></table>
					{/foreach}
				</div></td>
			</tr>
			{/if}
			<!-- FIN voucher enregistr?? -->
			
			
			<tr {if $total_wrapping == 0} style="display: none;"{/if}>
				<td colspan="1" class="text-right">
					{if $use_taxes}
					{if $display_tax_label}{l s='Total gift wrapping (tax incl.)'}{else}{l s='Total gift-wrapping cost'}{/if}
					{else}
					{l s='Total gift-wrapping cost'}
					{/if}
				</td>
				<td colspan="1" class="price-discount price" id="total_wrapping">
					{if $use_taxes}
					{if $priceDisplay}
					{displayPrice price=$total_wrapping_tax_exc}
					{else}
					{displayPrice price=$total_wrapping}
					{/if}
					{else}
					{displayPrice price=$total_wrapping_tax_exc}
					{/if}
				</td>
			</tr>
			
			<tr class="cart_total_price">
				{if $use_taxes}
				<td class="total_price_container text-right">
					<span>{l s='Total TTC'}</span>
				</td>
				
				<td colspan="1" class="price" id="total_price_container">
					<span id="total_price_cart">{displayPrice price=$total_price-$total_shipping}</span>
				</td>
				{else}
				<td class="total_price_container text-right">
					<span>{l s='Total HT'}</span>
				</td>
				<td colspan="1" class="price" id="total_price_container">
					<span id="total_price_cart">{displayPrice price=$total_price_without_tax}</span>
				</td>
				{/if}
			</tr>
			
			<tr>
				<td colspan="2">
					{if $is_logged AND !$is_guest}
					{if ($total_price-$total_shipping) == 0}
					Erreur total panier !
					{else}
					
					<a class="button-continue">{l s='Finaliser ma commande'}</a>
					{/if}
					
					{else}
					<a data-action="login"  class="login btn" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Se connecter'}" id="show_modal_login">{l s='Se connecter'} </a>
					{/if}
				</td>
			</tr>
			
			<tr>
				<td  class="reassurance_cart" colspan="2">
					<p>{l s='Secured Payment'}</p>
					<img src="{$base_dir_ssl}themes/default-bootstrap/img/paiement-securise_papillon-magique.png" alt="{l s='Secured Payment'}"/>
					
				</td>
			</tr>
			
		</tfoot>
		<tbody class="col-md-9 col-xs-12">
			<script>
				var papillons_array ;
				var papillons;
				var papillons_html;
			</script>
			{assign var='odd' value=0}
			{assign var='have_non_virtual_products' value=false}
			{foreach $products as $product}
			{if $product.is_virtual == 0}
			{assign var='have_non_virtual_products' value=true}
			{/if}
			{assign var='productId' value=$product.id_product}
			{assign var='productAttributeId' value=$product.id_product_attribute}
			{assign var='quantityDisplayed' value=0}
			{assign var='odd' value=($odd+1)%2}
			{assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId) || count($gift_products)}
			{* Display the product line *}
			{include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
			{* Then the customized datas ones*}
			{if isset($customizedDatas.$productId.$productAttributeId)}
			{foreach $customizedDatas.$productId.$productAttributeId[$product.id_address_delivery] as $id_customization=>$customization}
			<tr
			id="product_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
			class="product_{$product.id_product} product_customization_for_{$product.id_product}_{$product.id_product_attribute}_{$product.id_address_delivery|intval}{if $odd} odd{else} even{/if} customization alternate_item {if $product@last && $customization@last && !count($gift_products)}last_item{/if}">
			<td></td>
			<td colspan="3" class="customize">
				{foreach $customization.datas as $type => $custom_data}
				{if $type == $CUSTOMIZE_FILE}
				<div class="customizationUploaded">
					<ul class="customizationUploaded">
						{foreach $custom_data as $picture}
						<!-- <li><img src="{$pic_dir}{$picture.value}_small" alt="" class="customizationUploaded" /></li> -->
						
						{if substr(basename($picture.value), 0, 1) == 'P'}
						<a href="{$pic_dir}{$picture.value}" target="_blank">{l s='Voir le fichier'}</a><br>
						{else}
						<img src="{$pic_dir}{$picture.value}_small" alt="" /><br>
						{/if}
						<!-- <a href="{$link->getProductDeletePictureLink($product, $field.id_customization_field)|escape:'html':'UTF-8'}" title="{l s='Delete'}" >
							<img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" class="customization_delete_icon" width="11" height="13" />
						</a> -->
						
						{/foreach}
					</ul>
				</div>
				{elseif $type == $CUSTOMIZE_TEXTFIELD}
				<ul class="typedText">
					{foreach $custom_data as $textField}
					
					
					
					{if $textField.name == 'image_fairepart' && $textField.value != '' }
					<li>
						<a href="{$base_dir_ssl}img/upload_fairepart/{$textField.value}" target="_blank">{l s='voir image'}</a>
						
					</li>
					{/if}
					
					
					
					{if $product.id_product == 336 || $product.id_product == 337 || $product.id_product == 338 }
					<!--var_dump($textField)-->
				</ul>
				
				{if $textField.id_customization_field==1194 || $textField.id_customization_field==1198  || $textField.id_customization_field==1202 }
				<div id="papillons_{$textField.id_customization}"></div>
				<script>
					
					$(document).ready(function () {
						papillons_{$textField.id_customization} = '{$textField.value}';
						let id_produit_courant = {$product.id_product};

						let nb_papillons_lies = 0;
						nb_papillons_lies = $('.product_customization_for_{$product.id_product }_{$product.id_product_attribute}_0').length;
						
						
						
						
						
						papillons_array = JSON.parse(papillons_{$textField.id_customization}) ;
						
						
						papillons_html = '';
						let nb_custom_qty = Number($('#cart_quantity_custom_{$product.id_product }_{$product.id_product_attribute}_0').text());//'{$customization.quantity}';
						let nb_papillon_max =  10 ;
						//if coeur
						if(id_produit_courant == 337){
							nb_papillon_max =  30 ;
						}

						console.log('nb_custom_qty ' + nb_custom_qty);
						console.log('nb_papillon ' + papillons_array.length);
						
						
						for (let i = 0; i < papillons_array.length; i++) {
							let d = papillons_array[i];
							let id = d[0];
							
							
							if(id){
								let qty = d[1] ;
								
								if (nb_custom_qty === 1 && papillons_array.length >= 2) {
									qty= nb_papillon_max  / papillons_array.length;	
								}
								if (nb_custom_qty >= 2 && papillons_array.length >= 2) {
									qty= nb_papillon_max * nb_custom_qty / papillons_array.length;	
								}
								if (nb_custom_qty >= 2 && papillons_array.length === 1) {

									console.log('in)'+nb_papillons_lies );
									qty = (nb_papillon_max * nb_custom_qty) / nb_papillons_lies ;	
								}
							
								
								
								if(id&&qty){
									getImageById(id, Math.round(qty));
									//papillons_html += id+'x'+qty+'<br>';
								}
							}
						}
						
						//document.getElementById('papillons').innerHTML = papillons_html;
						
						//get product image by id product in prestashop
						async function getImageById(idd, qty){
							
							let url = 'https://www.magicflyer.com/index.php?controller=cartePapillon&action=';
							
							
							if( idd && qty ) {
								
								let data = 'getImgId&id='+idd;
								let response = await fetch(url+data);
								let responseText = await response.text();
								
								let dataName = 'getProductName&id='+idd;
								let responseName = await fetch(url+dataName);
								let responseTextName = await responseName.text();
								
								document.getElementById('papillons_{$textField.id_customization}').innerHTML += '<div class="col-sm-6 col-xs-6"><center><img src="' + responseText + '" width="100" height="100" /><br>'+ responseTextName +'<br><span> x '+qty+'</span></center></div>';
							}
						}
						
						
						/*
						(async() => {
							await getImageById();
						})();*/
					});				
				</script>
				
				{else}
				{if $textField.value != ''}
				<p class="champs_perso_carte_papillon"></p><strong>{$textField.name}</strong> {$textField.value}<br></p>
				{/if}
				
				{/if}	
				
				<ul>
					{else}
					
					<li class="champs_perso">
						{if $textField.value == '_DATE_01-01-1970'}
						{l s='Je ne veux pas personnaliser mes coeurs magiques'}  
						{/if}
						
						{if 
							
							$textField.value != '#000'     &&
							$textField.value != '#FFF'    &&
							$textField.value != '#808080' &&
							$textField.value != '#C0C0C0' &&
							$textField.value != '#FF0000' &&
							$textField.value != '#800000' &&
							$textField.value != '#FFFF00' &&
							$textField.value != '#808000' &&
							$textField.value != '#00FF00' &&
							$textField.value != '#008000' &&
							$textField.value != '#00FFFF' &&
							$textField.value != '#0000FF' &&
							$textField.value != '#000080' &&
							$textField.value != '#FF00FF' &&
							$textField.value != '#800080'  &&
							$textField.value != '_DATE_01-01-1970'
							
						}
						
						
						<p>{$textField.name} : {$textField.value|replace:"_DATE_":"<br><span>Date :</span> "}<br></p>
						
						
						{else}
						
						{$textField.name} : 
						
						{if $textField.value=='#000'}	{l s='Noir'}          {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#FFF'}	{l s='Blanc'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#808080'}{l s='Gris'}        {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#C0C0C0'}{l s='Argent??'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#FF0000'}{l s='Rouge'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#800000'}{l s='Marron'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#FFFF00'}{l s='Jaune'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#808000'}{l s='Olive'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#00FF00'}{l s='Vert citron'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#008000'}{l s='Vert'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#00FFFF'}{l s='Turquoise'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#0000FF'}{l s='Bleu'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#000080'}{l s='Bleu marine'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#FF00FF'}{l s='Fuchsia'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						{if $textField.value=='#800080'}{l s='Violet'}           {/if}<!--<input type='color' value='{$textField.value}' disabled>-->
						
						
						{/if}
						
						
						
						{*if $textField.name == 'Image'}
						<br><a href="//www.magicflyer.com/img/upload_fairepart/{$textField.value}" target="_blank">
							<img src="https://www.magicflyer.com/img/upload_fairepart/{$textField.value}" style="width: 200px;" />
						</a>
						{/if*}
					</li>
					{/if}
					
					
					{*if $textField.name == 'image_fairepart'}
					<li>
						<a href="//www.magicflyer.com/img/upload_fairepart/{$textField.value}" target="_blank">{l s='voir image'}</a>
						
					</li>
					{else}
					<li>
						{$textField.name} : {$textField.value}
					</li>
					<li>{$textField.value}<hr></li>
					{if $textField.value != '_DATE_01-01-1970'}
					<li>
						{if $textField.name}
						<span>{$textField.name} :</span>
						{else}
						{l s='Text #'}{$textField@index+1}
						{/if}
						{$textField.value|replace:"_DATE_":"<br><span>Date :</span> "}
					</li>
					
					{else}
					<!-- <li>{l s='Je ne veux pas personnaliser mes coeurs magiques.'}</li> -->
					{/if}
					
					
					{/if*}
					
					{/foreach}
				</ul>
				{/if}
				{/foreach}
			</td>
			<td class="cart_quantity" colspan="1">
				{if isset($cannotModify) AND $cannotModify == 1}
				<span>{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed}{/if}</span>
				{else}
				<input type="hidden" value="{$customization.quantity}" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}_hidden"/>
				<input type="text" value="{$customization.quantity}" class="cart_quantity_input form-control grey" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"/>
				<div class="cart_quantity_button clearfix">
					
					
				</div>
				{/if}
			</td>
			<td class="cart_delete text-center">
				{if isset($cannotModify) AND $cannotModify == 1}
				{else}
				<a
				id="{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
				class="cart_quantity_delete"
				href="{$link->getPageLink('cart', true, NULL, "delete=1&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;id_address_delivery={$product.id_address_delivery}&amp;token={$token_cart}")|escape:'html':'UTF-8'}"
				rel="nofollow"
				title="{l s='Delete'}">
				<i class="icon-trash"></i>
			</a>
			{/if}
		</td>
		<td>
		</td>
	</tr>
	{assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
	{/foreach}
	
	{* If it exists also some uncustomized products *}
	{if $product.quantity-$quantityDisplayed > 0}{include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}{/if}
	{/if}
	{/foreach}
	{assign var='last_was_odd' value=$product@iteration%2}
	{foreach $gift_products as $product}
	{assign var='productId' value=$product.id_product}
	{assign var='productAttributeId' value=$product.id_product_attribute}
	{assign var='quantityDisplayed' value=0}
	{assign var='odd' value=($product@iteration+$last_was_odd)%2}
	{assign var='ignoreProductLast' value=isset($customizedDatas.$productId.$productAttributeId)}
	{assign var='cannotModify' value=1}
	{* Display the gift product line *}
	{include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
	{/foreach}
</tbody>

</table>
</div> <!-- end order-detail-content -->

{if $show_option_allow_separate_package}
<p>
	<input type="checkbox" name="allow_seperated_package" id="allow_seperated_package" {if $cart->allow_seperated_package}checked="checked"{/if} autocomplete="off"/>
	<label for="allow_seperated_package">{l s='Send available products first'}</label>
</p>
{/if}

{* Define the style if it doesn't exist in the PrestaShop version*}
{* Will be deleted for 1.5 version and more *}
{if !isset($addresses_style)}
{$addresses_style.company = 'address_company'}
{$addresses_style.vat_number = 'address_company'}
{$addresses_style.firstname = 'address_name'}
{$addresses_style.lastname = 'address_name'}
{$addresses_style.address1 = 'address_address1'}
{$addresses_style.address2 = 'address_address2'}
{$addresses_style.city = 'address_city'}
{$addresses_style.country = 'address_country'}
{$addresses_style.phone = 'address_phone'}
{$addresses_style.phone_mobile = 'address_phone_mobile'}
{$addresses_style.alias = 'address_title'}
{/if}

{if ((!empty($delivery_option) AND !isset($virtualCart)) OR $delivery->id OR $invoice->id) AND !$opc}
<div class="order_delivery clearfix row">
	{if !isset($formattedAddresses) || (count($formattedAddresses.invoice) == 0 && count($formattedAddresses.delivery) == 0) || (count($formattedAddresses.invoice.formated) == 0 && count($formattedAddresses.delivery.formated) == 0)}
	{if $delivery->id}
	<div class="col-xs-12 col-sm-6"{if !$have_non_virtual_products} style="display: none;"{/if}>
		<ul id="delivery_address" class="address item box">
			<li><h3 class="page-subheading">{l s='Delivery address'}&nbsp;<span class="address_alias">({$delivery->alias})</span></h3></li>
			{if $delivery->company}<li class="address_company">{$delivery->company|escape:'html':'UTF-8'}</li>{/if}
			<li class="address_name">{$delivery->firstname|escape:'html':'UTF-8'} {$delivery->lastname|escape:'html':'UTF-8'}</li>
			<li class="address_address1">{$delivery->address1|escape:'html':'UTF-8'}</li>
			{if $delivery->address2}<li class="address_address2">{$delivery->address2|escape:'html':'UTF-8'}</li>{/if}
			<li class="address_city">{$delivery->postcode|escape:'html':'UTF-8'} {$delivery->city|escape:'html':'UTF-8'}</li>
			<li class="address_country">{$delivery->country|escape:'html':'UTF-8'} {if $delivery_state}({$delivery_state|escape:'html':'UTF-8'}){/if}</li>
		</ul>
	</div>
	{/if}
	{if $invoice->id}
	<div class="col-xs-12 col-sm-6">
		<ul id="invoice_address" class="address alternate_item box">
			<li><h3 class="page-subheading">{l s='Invoice address'}&nbsp;<span class="address_alias">({$invoice->alias})</span></h3></li>
			{if $invoice->company}<li class="address_company">{$invoice->company|escape:'html':'UTF-8'}</li>{/if}
			<li class="address_name">{$invoice->firstname|escape:'html':'UTF-8'} {$invoice->lastname|escape:'html':'UTF-8'}</li>
			<li class="address_address1">{$invoice->address1|escape:'html':'UTF-8'}</li>
			{if $invoice->address2}<li class="address_address2">{$invoice->address2|escape:'html':'UTF-8'}</li>{/if}
			<li class="address_city">{$invoice->postcode|escape:'html':'UTF-8'} {$invoice->city|escape:'html':'UTF-8'}</li>
			<li class="address_country">{$invoice->country|escape:'html':'UTF-8'} {if $invoice_state}({$invoice_state|escape:'html':'UTF-8'}){/if}</li>
		</ul>
	</div>
	{/if}
	{else}
	{foreach from=$formattedAddresses key=k item=address}
	<div class="col-xs-12 col-sm-6"{if $k == 'delivery' && !$have_non_virtual_products} style="display: none;"{/if}>
		<ul class="address {if $address@last}last_item{elseif $address@first}first_item{/if} {if $address@index % 2}alternate_item{else}item{/if} box">
			<li>
				<h3 class="page-subheading">
					{if $k eq 'invoice'}
					{l s='Invoice address'}
					{elseif $k eq 'delivery' && $delivery->id}
					{l s='Delivery address'}
					{/if}
					{if isset($address.object.alias)}
					<span class="address_alias">({$address.object.alias})</span>
					{/if}
				</h3>
			</li>
			{foreach $address.ordered as $pattern}
			{assign var=addressKey value=" "|explode:$pattern}
			{assign var=addedli value=false}
			{foreach from=$addressKey item=key name=foo}
			{$key_str = $key|regex_replace:AddressFormat::_CLEANING_REGEX_:""}
			{if isset($address.formated[$key_str]) && !empty($address.formated[$key_str])}
			{if (!$addedli)}
			{$addedli = true}
			<li><span class="{if isset($addresses_style[$key_str])}{$addresses_style[$key_str]}{/if}">
				{/if}
				{$address.formated[$key_str]|escape:'html':'UTF-8'}
				{/if}
				{if ($smarty.foreach.foo.last && $addedli)}
			</span></li>
			{/if}
			{/foreach}
			{/foreach}
		</ul>
	</div>
	{/foreach}
	{/if}
</div>
{/if}
<div id="HOOK_SHOPPING_CART">{$HOOK_SHOPPING_CART}</div>


{if !empty($HOOK_SHOPPING_CART_EXTRA)}
<div class="clear"></div>
<div class="cart_navigation_extra">
	<div id="HOOK_SHOPPING_CART_EXTRA">{$HOOK_SHOPPING_CART_EXTRA}</div>
</div>
{/if}
{strip}
{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
{addJsDef currencyRate=$currencyRate|floatval}
{addJsDef currencyFormat=$currencyFormat|intval}
{addJsDef currencyBlank=$currencyBlank|intval}
{addJsDef deliveryAddress=$cart->id_address_delivery|intval}
{addJsDefL name=txtProduct}{l s='product' js=1}{/addJsDefL}
{addJsDefL name=txtProducts}{l s='products' js=1}{/addJsDefL}
{/strip}
{/if}
<script>
	$(document).ready(function () {


		//force refresh on qty click
		$('.cart_quantity_up').click(function () {
			$(this).closest('form').submit();
		});
		$('.cart_quantity_down').click(function () {
			$(this).closest('form').submit();
		});

		$('#total_price_cart').one('DOMSubtreeModified', function(){
			console.log('changed');
			$('#center_column').hide();
		$('#center_column').after('<div class="perso_loader"><center><img src="{$base_dir}themes/default-bootstrap/img/preloading.svg"><p>{l s="Loading..." }</p></center></div>');


			location.reload();
		});
		
	});
	
	
	</script>
