<?php
/**
* @author Presta-Module.com <support@presta-module.com>
* @copyright Presta-Module 2014
*
*       _______  ____    ____
*      |_   __ \|_   \  /   _|
*        | |__) | |   \/   |
*        |  ___/  | |\  /| |
*       _| |_    _| |_\/_| |_
*      |_____|  |_____||_____|
*
**************************************
**            ModalCart              *
**   http://www.presta-module.com    *
**************************************
*
*/

class mc3Tpl_LastProductAdded extends mc3Tpl
{
    // Every needed fields of the templates (ordered)
    public $fields = array(
        'free_content_1' => array(
            'text_lang' => array(),
            'tinymce' => true,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 0,
            'padding' => array(10, 20, 10, 20),
            'margin' => array(0, -10, 10, -9),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('#1e6dce', '#0D4C9B'),
            'css_rules' => array('#pm_mc3_lpa_free_content_1' => array('border', 'border_radius', 'margin', 'padding', 'box_shadow', 'background_color')),
        ),
        'title' => array(
            'text_lang' => array(),
            'font_color' => '#2b2b2b',
            'font_size' => 16,
            'text_align' => 'left',
            'font_style' => array('bold'),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 0,
            'padding' => array(10, 10, 10, 10),
            'margin' => array(10, 0, 10, 0),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('', ''),
            'css_rules' => array('#pm_mc3_lpa_title' => array('font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'border_radius', 'margin', 'padding', 'box_shadow', 'background_color')),
        ),
        'cart_summary' => array(
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'margin' => array(0, 0, 0, 0),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('#ffffff', ''),
            'css_rules' => array('#pm_mc3_lpa' => array('border', 'margin', 'box_shadow', 'background_color')),
        ),
        'product_image' => array(
            'option_active' => true,
            'link' => true,
            'text_align' => 'center',
            'image_size' => 'medium_default',
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'content_border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 50,
            'padding' => array(0, 0, 0, 0),
            'box_shadow' => array(0, 0, 4, 0, 'outset', '#505050'),
            'width' => 88,
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array(
                '#pm_mc3_lpa_product_image' => array('option_active', 'width', 'border', 'padding', 'background_color', 'text_align', 'vertical_align'),
                '#pm_mc3_lpa_product_image img' => array('content_border', 'box_shadow', 'border_radius')
            ),
        ),
        'product_name' => array(
            'option_active' => true,
            'attributes' => true,
            'link' => true,
            'font_color' => '#3f3f3f',
            'font_size' => 13,
            'text_align' => 'left',
            'font_style' => array('bold'),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(20, 0, 10, 10),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'attributes_font_color' => '#3f3f3f',
            'attributes_font_size' => 11,
            'attributes_font_style' => array('italic'),
            'attributes_text_shadow' => array(0, 0, 0, ''),
            'css_rules' => array(
                '.pm_mc3_lpa_product_name' => array('option_active', 'width', 'text_align', 'border', 'padding', 'background_color', 'vertical_align'),
                '.pm_mc3_lpa_product_name span.pm_mc3_lpa_product_name_content' => array('font_color', 'font_size', 'text_shadow', 'font_style'),
                '.pm_mc3_lpa_product_name a' => array('font_color', 'font_size', 'text_shadow', 'font_style'),
                '.pm_mc3_attributes_summary' => array('attributes_font_color', 'attributes_font_size', 'attributes_text_shadow', 'attributes_font_style')
            ),
        ),
        'product_description' => array(
            'option_active' => true,
            'truncate_text' => true,
            'truncate_limit' => 179,
            'font_color' => '#3f3f3f',
            'font_size' => 11,
            'text_align' => 'left',
            'font_style' => array('italic'),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(1, 0, 0, 0, 'solid', '#ebebeb'),
            'padding' => array(10, 10, 20, 10),
            'width' => 400,
            'background_color' => array('', ''),
            'vertical_align' => 'top',
            'css_rules' => array('.pm_mc3_lpa_product_description' => array('option_active', 'width', 'text_shadow', 'font_color', 'font_size', 'font_style', 'text_align', 'border', 'padding', 'background_color', 'vertical_align')),
        ),
        'product_price' => array(
            'option_active' => true,
            'font_color' => '#3f3f3f',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array('bold'),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(20, 10, 0, 0),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('.pm_mc3_lpa_product_price' => array('option_active', 'width', 'text_shadow', 'font_color', 'font_size', 'font_style', 'text_align', 'border', 'padding', 'background_color', 'vertical_align')),
        ),
        'footer_actions_container' => array(
            'border' => array(1, 0, 0, 0, 'solid', '#D0D0D0'),
            'border_radius' => 0,
            'padding' => array(5, 5, 5, 5),
            'margin' => array(0, 0, 0, 0),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('', ''),
            'css_rules' => array('#pm_mc3_lpa_footer_actions_container' => array('border', 'border_radius', 'padding', 'margin', 'box_shadow', 'background_color')),
        ),
        'keep_shopping' => array(
            'option_active' => true,
            'button' => true,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 3,
            'padding' => array(10, 0, 10, 10),
            'background_color' => array('', ''),
            'text_lang' => array(),
            'font_color' => '',
            'font_size' => 12,
            'text_align' => 'left',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'css3_padding' => array(5, 10, 5, 10),
            'css3_button_line_height' => 10,
            'css3_font_color' => '#000000',
            'css3_background_color' => '#ffffff',
            'css3_font_color_hover' => '#000000',
            'css3_background_color_hover' => '#f0f0f0',
            'css3_background_flat' => true,
            'css_rules' => array(
                '#pm_mc3_lpa_keep_shopping' => array('option_active', 'background_color', 'border', 'padding', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align'),
                '#pm_mc3_lpa_keep_shopping_btn' => array('border_radius', 'css3_padding', 'css3_background_flat', 'css3_button_line_height', 'css3_font_color', 'css3_background_color', 'css3_font_color_hover', 'css3_background_color_hover')
            ),
        ),
        'order_now' => array(
            'option_active' => true,
            'button' => true,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 3,
            'padding' => array(10, 10, 10, 0),
            'background_color' => array('', ''),
            'text_lang' => array(),
            'font_color' => '',
            'font_size' => 12,
            'text_align' => 'center',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'css3_padding' => array(5, 10, 5, 10),
            'css3_button_line_height' => 10,
            'css3_font_color' => '#ffffff',
            'css3_background_color' => '#1E6CCE',
            'css3_font_color_hover' => '#ffffff',
            'css3_background_color_hover' => '#0D4C9B',
            'css3_background_flat' => true,
            'css_rules' => array(
                '#pm_mc3_lpa_order_now' => array('option_active', 'background_color', 'border', 'padding', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align'),
                '#pm_mc3_lpa_order_now_btn' => array('border_radius', 'css3_padding', 'css3_background_flat', 'css3_button_line_height', 'css3_font_color', 'css3_background_color', 'css3_font_color_hover', 'css3_background_color_hover')
            ),
        ),
        'free_content_2' => array(
            'text_lang' => array(),
            'tinymce' => true,
            'border' => array(0, 0, 1, 0, 'solid', '#d0d0d0'),
            'border_radius' => 0,
            'padding' => array(0, 0, 10, 0),
            'margin' => array(0, 0, 0, 0),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('', ''),
            'css_rules' => array('#pm_mc3_lpa_free_content_2' => array('border', 'border_radius', 'padding', 'margin', 'box_shadow', 'background_color')),
        ),
        'free_shipping' => array(
            'text_lang' => array(),
            'tinymce' => true,
            'border' => array(1, 0, 0, 0, 'solid', '#D0D0D0'),
            'border_radius' => 0,
            'padding' => array(10, 0, 10, 0),
            'margin' => array(0, 0, 0, 0),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('#f4f4f4', ''),
            'css_rules' => array('#pm_mc3_lpa_free_shipping' => array('border', 'border_radius', 'padding', 'margin', 'box_shadow', 'background_color')),
        ),
        'hook_cross_selling_on_cart' => array(
            'css_rules' => array('#pm_mc3_lpa_hook_cross_selling_on_cart' => array()),
        ),
    );
    public $foreachFields = array(
        'product_image',
        'product_name',
        'product_description',
        'product_price'
    );
    public $templateParams = array(
        'css_rules' => array(
            '.mfp-content' => array('box_shadow', 'modal_width', 'modal_background_color', 'modal_border_radius', 'border', 'padding'),
            '.mfp-bg' => array('background_overlay_color', 'background_overlay_opacity')
        ),
        'add_to_cart_selector' => '.ajax_add_to_cart_button',
        'product_add_to_cart_selector' => 'body#product p#add_to_cart input, body#product p#add_to_cart button',
        'z_index' => 69997,
        'display_free_content_1' => true,
        'display_free_content_2' => true,
        'display_free_shipping' => true,
        'display_title' => true,
        'display_hook_cross_selling_on_cart' => false,
        'display_on_mobile' => false,
        'display_close_button' => false,
        'modal_width' => 600,
        'modal_background_color' => array('#ffffff', '#e4e4e4'),
        'modal_border_radius' => 5,
        'border' => array(1, 1, 1, 1, 'solid', '#dcdcdc'),
        'padding' => array(0, 0, 0, 0),
        'box_shadow' => array(0, 0, 20, 1, 'outset', '#000000'),
        'background_overlay' => true,
        'background_overlay_opacity' => 60,
        'background_overlay_color' => array('#000000', 'transparent'),
        'fields_order' => array(
            'pm_mc3_lpa_title',
            'pm_mc3_lpa_free_content_1',
            'pm_mc3_lpa_free_content_2',
            'pm_mc3_lpa_body',
            'pm_mc3_lpa_product_image',
            'pm_mc3_lpa_product_name',
            'pm_mc3_lpa_product_price',
            'pm_mc3_lpa_product_description',
            'pm_mc3_lpa_free_shipping',
            'pm_mc3_lpa_footer',
            'pm_mc3_lpa_keep_shopping',
            'pm_mc3_lpa_order_now',
            'pm_mc3_lpa_hook_cross_selling_on_cart',
        ),
    );
    public $templateKey = 'lpa';
    public $name = 'Last Product Added';
    public $templateFilename = 'last_product_added-{id_shop}.tpl';
    public function __construct()
    {
        parent::__construct();
    }
    public static function getValidationRules()
    {
        return array('validateLang' => array('text_lang' => 'isString'));
    }
    public function getProductLineFields()
    {
        return $this->foreachFields;
    }
    public function getOrderedFields()
    {
        $ordered_fields = array();
        foreach ($this->templateParams['fields_order'] as $field_name) {
            $field_name = str_replace('pm_mc3_lpa_', '', $field_name);
            if (isset($this->fields[$field_name])) {
                $ordered_fields[$field_name] = $this->fields[$field_name];
            }
        }
        return $ordered_fields;
    }
    public function generateTpl()
    {
        $generated_template = '
		<div id="pm_mc3_content" class="clearfix">
			<div id="pm_mc3_lpa_container" class="clearfix">
		';
        $started_footer_actions_buttons = false;
        $started_foreach = false;
        $shopping_button_added = false;
        $table_last_product_added = false;
        $table_needed = false;
        $product_image_position = 'left';
        $generated_template_image = '';
        foreach ($this->getProductLineFields() as $productLineField) {
            if ($this->isVisibleBlock($productLineField)) {
                $table_needed = true;
                break;
            }
        }
        foreach ($this->getOrderedFields() as $field_name => $params) {
            if ($table_needed) {
                if (in_array($field_name, $this->getProductLineFields()) && !$started_foreach) {
                    $generated_template .= '
					<table id="pm_mc3_lpa">
						<tbody>
							{assign var=\'productId\' value=$product.id_product}
							{assign var=\'productAttributeId\' value=$product.id_product_attribute}
							<tr>
					';
                    $started_foreach = true;
                } elseif (!in_array($field_name, $this->getProductLineFields()) && $started_foreach) {
                    $generated_template .= '
							</tr>
						</tbody>
					</table><!-- #pm_mc3_lpa -->';
                    $started_foreach = false;
                }
            }
            switch ($field_name) {
                case 'free_content_1':
                case 'free_content_2':
                case 'title':
                    if (isset($this->templateParams['display_'.$field_name]) && $this->templateParams['display_'.$field_name]) {
                        $generated_template .= '
						{if isset($'.$field_name.') && !empty($'.$field_name.')}
							<div id="pm_mc3_lpa_'.$field_name.'" class="pm_mc3_lpa_line">
								{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%nb_total_products%\':$nb_total_products}
								{if $priceDisplay != 1}
									{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%cart_total%\':{convertPrice price=$summary.total_price}}
								{else}
									{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%cart_total%\':{convertPrice price=$summary.total_price_without_tax}}
								{/if}
								{$'.$field_name.'}
							</div><!-- #pm_mc3_lpa_'.$field_name.' -->
						{/if}';
                    }
                    break;
                case 'free_shipping':
                    if (isset($this->templateParams['display_'.$field_name]) && $this->templateParams['display_'.$field_name]) {
                        $generated_template .= '
							{if $summary.free_ship > 0 && !$isVirtualCart}
								<div id="pm_mc3_lpa_'.$field_name.'" class="pm_mc3_lpa_line">
									{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%free_shipping%\':{convertPrice price=$summary.free_ship}}
									{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%nb_total_products%\':$nb_total_products}
									{if $priceDisplay != 1}
										{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%cart_total%\':{convertPrice price=$summary.total_price}}
									{else}
										{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%cart_total%\':{convertPrice price=$summary.total_price_without_tax}}
									{/if}
									{$'.$field_name.'}
								</div><!-- #pm_mc3_lpa_'.$field_name.' -->
							{/if}
						';
                    }
                    break;
                case 'product_name':
                    $table_last_product_added = true;
                    if ($this->getFirstProductLineField('product_image') == $field_name) {
                        $generated_template .= '{product_image_on_left}';
                    }
                    if ($this->isVisibleBlock($field_name)) {
                        $generated_template .= '
							<td class="pm_mc3_lpa_product_name">
								<span class="pm_mc3_lpa_product_name_content">' . (isset($params['link']) && $params['link'] ? '<a href="{$product.image_link}" title="{$product.name|escape:\'htmlall\':\'UTF-8\'}">' : '').'{if isset($quantity_added) && $quantity_added > 1}{$quantity_added|intval}x {/if}{$product.name|escape:\'htmlall\':\'UTF-8\'}'.(isset($params['link']) && $params['link'] ? '</a>' : '') . '</span>
								' . (isset($params['attributes']) && $params['attributes'] ? '<br/>{if isset($product.isPack) && $product.isPack}<div class="pm_mc3_attributes_summary">{$product.attributes}</div>{else}<span class="pm_mc3_attributes_summary">{$product.attributes|escape:\'htmlall\':\'UTF-8\'}</span>{/if}' : '') . '
								{if isset($customizedDatas.$productId.$productAttributeId)}
									{foreach from=$customizedDatas.$productId.$productAttributeId key=\'id_customization\' item=\'customization\'}
										{foreach from=$customization.datas key=\'type\' item=\'datas\'}
											{if $type == $CUSTOMIZE_FILE}
												<div class="customizationUploaded">
													<ul class="customizationUploaded">
														{foreach from=$datas item=\'picture\'}
															<li><img src="{$pic_dir}{$picture.value}_{$imageSize}" alt="" class="customizationUploaded" width="{$imageWidth}" height="{$imageHeight}"/></li>
														{/foreach}
													</ul>
												</div>
											{elseif $type == $CUSTOMIZE_TEXTFIELD}
												<ul class="typedText">
													{foreach from=$datas item=\'textField\' name=\'typedText\'}
														<li>{if $textField.name}{$textField.name}{else}{$customization_text_field_label}{$smarty.foreach.typedText.index+1}{/if}: {$textField.value}</li>
													{/foreach}
												</ul>
											{/if}
										{/foreach}
									{/foreach}
								{/if}
							</td>
						';
                    } else {
                        if ($this->isVisibleBlock('product_image')) {
                            $generated_template .= '<td class="pm_mc3_lpa_product_name"></td>';
                        }
                    }
                    if ($this->getLastProductLineField('product_image') == $field_name) {
                        $generated_template .= '{product_image_on_right}';
                    }
                    break;
                case 'product_description':
                    $colspan_product_description = ((int)$this->isVisibleBlock('product_name') + (int)$this->isVisibleBlock('product_price'));
                    if ($this->getLastProductLineField('product_image') == $field_name) {
                        $generated_template .= '{product_image_on_right}';
                    }
                    if ($this->isVisibleBlock($field_name)) {
                        if (isset($params['truncate_text']) && $params['truncate_text'] && isset($params['truncate_limit']) && (int)$params['truncate_limit'] > 0) {
                            $generated_template .= '<tr><td'.($colspan_product_description > 1 ? ' colspan="'.$colspan_product_description.'"' : '').'  class="pm_mc3_lpa_product_description">{mc3_truncateHTML string=$product.description_short limit='.(int)$params['truncate_limit'].'}</td></tr>';
                        } else {
                            $generated_template .= '<tr><td'.($colspan_product_description > 1 ? ' colspan="'.$colspan_product_description.'"' : '').'  class="pm_mc3_lpa_product_description">{$product.description_short}</td></tr>';
                        }
                    }
                    break;
                case 'product_price':
                    $table_last_product_added = true;
                    if ($this->getFirstProductLineField('product_image') == $field_name) {
                        $generated_template .= '{product_image_on_left}';
                    }
                    if ($this->isVisibleBlock($field_name)) {
                        $generated_template .= '
						<td class="pm_mc3_lpa_product_price">
							{if $priceDisplay != 1}
								{convertPrice price=($product.price_wt + $product.ecotax_wt)}
							{else}
								{convertPrice price=($product.price + $product.ecotax_wt)}
							{/if}
							{if isset($product.ecotax) && $product.ecotax > 0}
								<br /><small>({$including_ecotax|replace:\'%ecotax%\':{convertPrice price=$product.ecotax_wt}})</small>
							{/if}
						</td>';
                    }
                    if ($this->getLastProductLineField('product_image') == $field_name) {
                        $generated_template .= '{product_image_on_right}';
                    }
                    break;
                case 'product_image':
                    if ($this->isVisibleBlock($field_name)) {
                        $generated_template_image = '
						<td'.($this->isVisibleBlock('product_description') ? ' rowspan="2"' : '').' id="pm_mc3_lpa_product_image">
							'.(isset($params['link']) &&  $params['link'] ? '<a href="{$product.image_link}" title="{$product.name|escape:\'htmlall\':\'UTF-8\'}">' : '').'
							<img src="{$product.image_src}" alt="{$product.name|escape:\'htmlall\':\'UTF-8\'}" />
							'.(isset($params['link']) &&  $params['link'] ? '</a>' : '').'
						</td>';
                        if ($table_last_product_added) {
                            $product_image_position = 'right';
                        } else {
                            $product_image_position = 'left';
                        }
                    }
                    break;
                case 'hook_cross_selling_on_cart':
                    if (pm_modalcart3::isCrossSellingInstalled() && isset($this->templateParams['display_hook_cross_selling_on_cart']) && $this->templateParams['display_hook_cross_selling_on_cart']) {
                        $generated_template .= '
						{if isset($'.$field_name.') && !empty($'.$field_name.')}
							<div id="pm_mc3_lpa_'.$field_name.'" class="pm_mc3_lpa_line">
								{$'.$field_name.'}
								{literal}
								<script type="text/javascript">
									$(document).ready(function(){
										modalAjaxCart.overrideButtonsInThePage();
									});
								</script>
								{/literal}
							</div><!-- #pm_mc3_lpa_'.$field_name.' -->
						{/if}
						';
                    }
                    break;
                case 'keep_shopping':
                    if (!$started_footer_actions_buttons) {
                        $started_footer_actions_buttons = $field_name;
                        $generated_template .= '<div id="pm_mc3_lpa_footer_actions_container" class="clearfix pm_mc3_lpa_line">';
                    }
                    if ($this->isVisibleBlock($field_name)) {
                        $generated_template .= '
						<div id="pm_mc3_lpa_keep_shopping_container" class="clearfix pm_mc3_button_'.(!$shopping_button_added ? 'left' : 'right').'">
							<div id="pm_mc3_lpa_'.$field_name.'" class="clearfix pm_mc3_button_'.(!$shopping_button_added ? 'left' : 'right').'_sub">
								<a href="javascript:mc3_continueShopping();" id="pm_mc3_lpa_'.$field_name.'_btn" class="'.(isset($params['css3_background_flat']) && $params['css3_background_flat'] ? 'pm_mc3_actions_flat_button' : 'pm_mc3_actions_gradient_button').'">{$'.$field_name.'}</a>
							</div><!-- #pm_mc3_lpa_'.$field_name.' -->
						</div><!-- #pm_mc3_lpa_keep_shopping_container -->';
                        $shopping_button_added = true;
                    }
                    if ($started_footer_actions_buttons !== false && $started_footer_actions_buttons != $field_name) {
                        $generated_template .= '</div><!-- #pm_mc3_lpa_footer_actions_container" -->';
                    }
                    break;
                case 'order_now':
                    if (!$started_footer_actions_buttons) {
                        $started_footer_actions_buttons = $field_name;
                        $generated_template .= '<div id="pm_mc3_lpa_footer_actions_container" class="clearfix pm_mc3_lpa_line">';
                    }
                    if ($this->isVisibleBlock($field_name)) {
                        $generated_template .= '
						<div id="pm_mc3_lpa_order_now_container" class="clearfix pm_mc3_button_'.(!$shopping_button_added ? 'left' : 'right').'">
							<div id="pm_mc3_lpa_'.$field_name.'" class="clearfix pm_mc3_button_'.(!$shopping_button_added ? 'left' : 'right').'_sub">
								<a href="{$order_page_link}" id="pm_mc3_lpa_'.$field_name.'_btn" class="'.(isset($params['css3_background_flat']) && $params['css3_background_flat'] ? 'pm_mc3_actions_flat_button' : 'pm_mc3_actions_gradient_button').'">{$'.$field_name.'}</a>
							</div><!-- #pm_mc3_lpa_'.$field_name.' -->
						</div><!-- #pm_mc3_lpa_order_now_container -->';
                        $shopping_button_added = true;
                    }
                    if ($started_footer_actions_buttons !== false && $started_footer_actions_buttons != $field_name) {
                        $generated_template .= '</div><!-- #pm_mc3_lpa_footer_actions_container -->';
                    }
                    break;
                default:
                    if ((isset($params['option_active']) && $params['option_active']) || !isset($params['option_active'])) {
                        $generated_template .= '{if isset($'.$field_name.') && !empty($'.$field_name.')}<div id="pm_mc3_lpa_'.$field_name.'" class="pm_mc3_lpa_line">{$'.$field_name.'}</div><!-- #pm_mc3_lpa_'.$field_name.' -->{/if}';
                    }
                    break;
            }
        }
        if ($product_image_position == 'right') {
            $generated_template = str_replace('{product_image_on_left}', '', $generated_template);
            $generated_template = str_replace('{product_image_on_right}', $generated_template_image, $generated_template);
        } else {
            $generated_template = str_replace('{product_image_on_right}', '', $generated_template);
            $generated_template = str_replace('{product_image_on_left}', $generated_template_image, $generated_template);
        }
        $generated_template .= '
			</div><!-- #pm_mc3_lpa_container -->
		</div><!-- #pm_mc3_content -->
		';
        return $this->writeGeneratedTpl($generated_template);
    }
    public function getBackOfficeCSS()
    {
        return '
			ul#pm_mc3_lpa_global_sort {text-align: center;}
			ul#pm_mc3_lpa_global_sort li {background-color: white; border: 1px solid black;}
			#pm_mc3_lpa_cart_summary {margin: 0 auto; width: 90%; border-spacing: 3px; border-collapse: separate;}
			#pm_mc3_lpa_cart_summary tr td {border: 1px solid black;}
			#pm_mc3_lpa_product_line_sort_1 li {float:left;}
			#pm_mc3_lpa_product_line_sort_2 li {float:left;}
			#pm_mc3_lpa_footer_sort li {float:left; width:40%; margin-left: 5%;}
			#pm_mc3_lpa_footer_sort:after{content:"";display:block;clear:both;}
			#pm_mc3_lpa_product_image { height: 64px; width: 15%; }
			#pm_mc3_lpa_product_line_sort_1 { margin-left: auto !important; margin-right: auto !important; width: 89%; }
			#pm_mc3_lpa_product_line_sort_2_container { height: 64px; width: 80% }
			#pm_mc3_lpa_product_name, #pm_mc3_lpa_product_price { width: 40%; margin-left: 5% }
			#pm_mc3_lpa_product_description { width: 98% }
			#pm_mc3_lpa_product_description:after{content:"";display:block;clear:both;}
			#pm_mc3_lpa_body { height: 75px; }
		';
    }
    public function getBackOfficeTemplate()
    {
        $pm_mc3_lpa_product_line_sort_content_1 = array(
            'pm_mc3_lpa_product_image' => '
				<li id="pm_mc3_lpa_product_image" class="product_image'.(!$this->isVisibleBlock('product_image') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_image" class="pm_translate">{translate_product_image}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_image" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
        );
        $pm_mc3_lpa_product_line_sort_content_2 = array(
            'pm_mc3_lpa_product_name' => '
				<li id="pm_mc3_lpa_product_name" class="product_name'.(!$this->isVisibleBlock('product_name') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_name" class="pm_translate">{translate_product_name}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_name" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
            'pm_mc3_lpa_product_description' => '
				<li id="pm_mc3_lpa_product_description" class="product_description'.(!$this->isVisibleBlock('product_description') ? ' pm_mc3_hidden_block' : '').'">
					<span id="translate_product_description" class="pm_translate">{translate_product_description}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_description" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
            'pm_mc3_lpa_product_price' => '
				<li id="pm_mc3_lpa_product_price" class="product_price'.(!$this->isVisibleBlock('product_price') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_price" class="pm_translate">{translate_product_price}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_price" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
        );
        $pm_mc3_lpa_footer_sort_content = array(
            'pm_mc3_lpa_keep_shopping' => '
				<li id="pm_mc3_lpa_keep_shopping" class="'.(!$this->isVisibleBlock('keep_shopping') ? 'pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_keep_shopping" class="pm_translate">{translate_keep_shopping}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=keep_shopping" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
            'pm_mc3_lpa_order_now' => '
				<li id="pm_mc3_lpa_order_now" class="'.(!$this->isVisibleBlock('order_now') ? 'pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_order_now" class="pm_translate">{translate_order_now}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=order_now" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
        );
        $global_items = array(
            'pm_mc3_lpa_free_content_1' => '
				<li id="pm_mc3_lpa_free_content_1">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_free_content_1" class="pm_translate">{translate_free_content_1}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=free_content_1" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>',
            'pm_mc3_lpa_title' => '
				<li id="pm_mc3_lpa_title">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_title" class="pm_translate">{translate_title}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=title" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>',
            'pm_mc3_lpa_body' => '
				<li id="pm_mc3_lpa_body">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<a rel="860_500_1" href="{config_url}&amp;item=cart_summary" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
					<ul id="pm_mc3_lpa_product_line_sort_1" class="ui-sortable">
						{pm_mc3_lpa_product_line_sort_content_1_before}
						<li id="pm_mc3_lpa_product_line_sort_2_container">
							<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
							<ul id="pm_mc3_lpa_product_line_sort_2" class="ui-sortable">
								{pm_mc3_lpa_product_line_sort_content_2}
							</ul>
						</li>
						{pm_mc3_lpa_product_line_sort_content_1_after}
					</ul>
				</li>
			',
            'pm_mc3_lpa_footer' => '
				<li id="pm_mc3_lpa_footer">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<a rel="860_500_1" href="{config_url}&amp;item=footer_actions_container" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
					<ul id="pm_mc3_lpa_footer_sort" class="ui-sortable">
						{pm_mc3_lpa_footer_sort_content}
					</ul>
				</li>
			',
            'pm_mc3_lpa_free_content_2' => '
				<li id="pm_mc3_lpa_free_content_2">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_free_content_2" class="pm_translate">{translate_free_content_2}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=free_content_2" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
            'pm_mc3_lpa_hook_cross_selling_on_cart' => (pm_modalcart3::isCrossSellingInstalled() ? '
				<li id="pm_mc3_lpa_hook_cross_selling_on_cart">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_hook_cross_selling_on_cart" class="pm_translate">{translate_hook_cross_selling_on_cart}</span>
					<a rel="860_500_1" href="#ui-tabs-2" onclick="$jqPm(\'div#MC3_Panel>ul li:eq(1)>a\').trigger(\'click\'); return false;" class="ui-icon ui-icon-wrench pm_config_icon"></a>
				</li>
			' : ''),
            'pm_mc3_lpa_free_shipping' => '
				<li id="pm_mc3_lpa_free_shipping">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_free_shipping" class="pm_translate">{translate_free_shipping}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=free_shipping" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
        );
        $template = '<ul id="pm_mc3_lpa_global_sort" class="ui-sortable">';
        foreach ($this->templateParams['fields_order'] as $field_name) {
            if (isset($global_items[$field_name])) {
                if ($field_name == 'pm_mc3_lpa_body') {
                    $pm_mc3_lpa_product_line_sort_1 = '';
                    $pm_mc3_lpa_product_line_sort_2 = '';
                    $image_added = false;
                    $switch = false;
                    foreach ($this->templateParams['fields_order'] as $field_name2) {
                        if (isset($pm_mc3_lpa_product_line_sort_content_1[$field_name2])) {
                            $image_added = true;
                            $pm_mc3_lpa_product_line_sort_1 .= $pm_mc3_lpa_product_line_sort_content_1[$field_name2];
                        }
                        if (isset($pm_mc3_lpa_product_line_sort_content_2[$field_name2])) {
                            $pm_mc3_lpa_product_line_sort_2 .= $pm_mc3_lpa_product_line_sort_content_2[$field_name2];
                            if (!$image_added) {
                                $switch = true;
                            }
                        }
                    }
                    if ($switch) {
                        $template_tmp = str_replace('{pm_mc3_lpa_product_line_sort_content_1_after}', $pm_mc3_lpa_product_line_sort_1, $global_items[$field_name]);
                        $template_tmp = str_replace('{pm_mc3_lpa_product_line_sort_content_1_before}', '', $template_tmp);
                    } else {
                        $template_tmp = str_replace('{pm_mc3_lpa_product_line_sort_content_1_before}', $pm_mc3_lpa_product_line_sort_1, $global_items[$field_name]);
                        $template_tmp = str_replace('{pm_mc3_lpa_product_line_sort_content_1_after}', '', $template_tmp);
                    }
                    $template .= str_replace('{pm_mc3_lpa_product_line_sort_content_2}', $pm_mc3_lpa_product_line_sort_2, $template_tmp);
                } elseif ($field_name == 'pm_mc3_lpa_footer') {
                    $pm_mc3_lpa_footer_sort = '';
                    foreach ($this->templateParams['fields_order'] as $field_name2) {
                        if (isset($pm_mc3_lpa_footer_sort_content[$field_name2])) {
                            $pm_mc3_lpa_footer_sort .= $pm_mc3_lpa_footer_sort_content[$field_name2];
                        }
                    }
                    $template .= str_replace('{pm_mc3_lpa_footer_sort_content}', $pm_mc3_lpa_footer_sort, $global_items[$field_name]);
                } else {
                    $template .= $global_items[$field_name];
                }
            }
        }
        $template .= '</ul>';
        return $template;
    }
}
