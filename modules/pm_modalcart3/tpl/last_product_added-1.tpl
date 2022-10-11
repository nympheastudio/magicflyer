
		<div id="pm_mc3_content" class="clearfix">
			<div id="pm_mc3_lpa_container" class="clearfix">
		
						{if isset($title) && !empty($title)}
							<div id="pm_mc3_lpa_title" class="pm_mc3_lpa_line">
								{assign var='title' value=$title|replace:'%nb_total_products%':$nb_total_products}
								{if $priceDisplay != 1}
									{assign var='title' value=$title|replace:'%cart_total%':{convertPrice price=$summary.total_price}}
								{else}
									{assign var='title' value=$title|replace:'%cart_total%':{convertPrice price=$summary.total_price_without_tax}}
								{/if}
								{$title}
							</div><!-- #pm_mc3_lpa_title -->
						{/if}
					<table id="pm_mc3_lpa">
						<tbody>
							{assign var='productId' value=$product.id_product}
							{assign var='productAttributeId' value=$product.id_product_attribute}
							<tr>
					
						<td id="pm_mc3_lpa_product_image">
							
							<img src="{$product.image_src}" alt="{$product.name|escape:'htmlall':'UTF-8'}" />
							
						</td>
							<td class="pm_mc3_lpa_product_name">
								<span class="pm_mc3_lpa_product_name_content"><a href="{$product.image_link}" title="{$product.name|escape:'htmlall':'UTF-8'}">{if isset($quantity_added) && $quantity_added > 1}{$quantity_added|intval}x {/if}{$product.name|escape:'htmlall':'UTF-8'}</a></span>
								<br/>{if isset($product.isPack) && $product.isPack}<div class="pm_mc3_attributes_summary">{$product.attributes}</div>{else}<span class="pm_mc3_attributes_summary">{$product.attributes|escape:'htmlall':'UTF-8'}</span>{/if}
								{if isset($customizedDatas.$productId.$productAttributeId)}
									{foreach from=$customizedDatas.$productId.$productAttributeId key='id_customization' item='customization'}
										{foreach from=$customization.datas key='type' item='datas'}
											{if $type == $CUSTOMIZE_FILE}
												<div class="customizationUploaded">
													<ul class="customizationUploaded">
														{foreach from=$datas item='picture'}
															<li><img src="{$pic_dir}{$picture.value}_{$imageSize}" alt="" class="customizationUploaded" width="{$imageWidth}" height="{$imageHeight}"/></li>
														{/foreach}
													</ul>
												</div>
											{elseif $type == $CUSTOMIZE_TEXTFIELD}
												<ul class="typedText">
													{foreach from=$datas item='textField' name='typedText'}
														<li>{if $textField.name}{$textField.name}{else}{$customization_text_field_label}{$smarty.foreach.typedText.index+1}{/if}: {$textField.value}</li>
													{/foreach}
												</ul>
											{/if}
										{/foreach}
									{/foreach}
								{/if}
							</td>
						
						<td class="pm_mc3_lpa_product_price">
							{if $priceDisplay != 1}
								{convertPrice price=($product.price_wt + $product.ecotax_wt)}
							{else}
								{convertPrice price=($product.price + $product.ecotax_wt)}
							{/if}
							{if isset($product.ecotax) && $product.ecotax > 0}
								<br /><small>({$including_ecotax|replace:'%ecotax%':{convertPrice price=$product.ecotax_wt}})</small>
							{/if}
						</td>
							</tr>
						</tbody>
					</table><!-- #pm_mc3_lpa --><div id="pm_mc3_lpa_footer_actions_container" class="clearfix pm_mc3_lpa_line">
						<div id="pm_mc3_lpa_keep_shopping_container" class="clearfix pm_mc3_button_left">
							<div id="pm_mc3_lpa_keep_shopping" class="clearfix pm_mc3_button_left_sub">
								<a href="javascript:mc3_continueShopping();" id="pm_mc3_lpa_keep_shopping_btn" class="pm_mc3_actions_flat_button">{$keep_shopping}</a>
							</div><!-- #pm_mc3_lpa_keep_shopping -->
						</div><!-- #pm_mc3_lpa_keep_shopping_container -->
						<div id="pm_mc3_lpa_order_now_container" class="clearfix pm_mc3_button_right">
							<div id="pm_mc3_lpa_order_now" class="clearfix pm_mc3_button_right_sub">
								<a href="{$order_page_link}" id="pm_mc3_lpa_order_now_btn" class="pm_mc3_actions_flat_button">{$order_now}</a>
							</div><!-- #pm_mc3_lpa_order_now -->
						</div><!-- #pm_mc3_lpa_order_now_container --></div><!-- #pm_mc3_lpa_footer_actions_container -->
						{if isset($hook_cross_selling_on_cart) && !empty($hook_cross_selling_on_cart)}
							<div id="pm_mc3_lpa_hook_cross_selling_on_cart" class="pm_mc3_lpa_line">
								{$hook_cross_selling_on_cart}
								{literal}
								<script type="text/javascript">
									$(document).ready(function(){
										modalAjaxCart.overrideButtonsInThePage();
									});
								</script>
								{/literal}
							</div><!-- #pm_mc3_lpa_hook_cross_selling_on_cart -->
						{/if}
						
			</div><!-- #pm_mc3_lpa_container -->
		</div><!-- #pm_mc3_content -->
		