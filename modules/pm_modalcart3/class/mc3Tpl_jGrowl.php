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

class mc3Tpl_jGrowl extends mc3Tpl
{
    // Every needed fields of the templates (ordered)
    public $fields = array(
        'free_content_1' => array(
            'text_lang' => array(),
            'tinymce' => true,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 0,
            'padding' => array(0, 0, 10, 0),
            'background_color' => array('', ''),
            'css_rules' => array('#pm_mc3_jgrowl_free_content_1' => array('border', 'border_radius', 'padding', 'background_color')),
        ),
        'cart_summary' => array(
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'margin' => array(0, 0, 0, 0),
            'background_color' => array('', ''),
            'css_rules' => array('#pm_mc3_jgrowl_table_container' => array('border', 'margin', 'background_color')),
        ),
        'product_image' => array(
            'option_active' => true,
            'link' => true,
            'text_align' => 'center',
            'image_size' => 'small_default',
            'width' => 45,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'content_border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 0,
            'padding' => array(10, 10, 0, 10),
            'box_shadow' => array(0, 0, 2, 0, 'outset', '#656565'),
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array(
                '#pm_mc3_jgrowl_product_image' => array('option_active', 'width', 'border', 'padding', 'background_color', 'text_align', 'vertical_align'),
                '#pm_mc3_jgrowl_product_image img' => array('content_border', 'box_shadow', 'border_radius')
            ),
        ),
        'product_name' => array(
            'option_active' => true,
            'attributes' => true,
            'link' => true,
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'width' => '',
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(0, 0, 0, 0),
            'background_color' => array('', ''),
            'font_color' => '',
            'font_size' => 13,
            'text_align' => 'left',
            'vertical_align' => 'middle',
            'attributes_font_color' => '',
            'attributes_font_size' => 11,
            'attributes_font_style' => array('italic'),
            'attributes_text_shadow' => array(0, 0, 0, ''),
            'css_rules' => array(
                '.pm_mc3_jgrowl_product_name' => array('option_active', 'width', 'text_align', 'border', 'padding', 'background_color', 'vertical_align'),
                '.pm_mc3_jgrowl_product_name span.pm_mc3_jgrowl_product_name_content' => array('font_color', 'font_size', 'text_shadow', 'font_style'),
                '.pm_mc3_jgrowl_product_name a' => array('font_color', 'font_size', 'text_shadow', 'font_style'),
                '.pm_mc3_attributes_summary' => array('attributes_font_color', 'attributes_font_size', 'attributes_text_shadow', 'attributes_font_style')
            ),
        ),
        'product_price' => array(
            'option_active' => true,
            'font_color' => '',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array('italic'),
            'text_shadow' => array(0, 0, 0, ''),
            'width' => 65,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(0, 10, 0, 0),
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('.pm_mc3_jgrowl_product_price' => array('option_active', 'width', 'border', 'padding', 'background_color', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'vertical_align')),
        ),
        'order_now' => array(
            'option_active' => true,
            'button' => true,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 3,
            'padding' => array(0, 0, 5, 0),
            'background_color' => array('', ''),
            'text_lang' => array(),
            'font_color' => '',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array('bold'),
            'text_shadow' => array(0, 0, 0, ''),
            'css3_padding' => array(2, 8, 2, 8),
            'css3_button_line_height' => 10,
            'css3_font_color' => '#303030',
            'css3_background_color' => '#f4f4f4',
            'css3_font_color_hover' => '#303030',
            'css3_background_color_hover' => '#facd2c',
            'css3_background_flat' => true,
            'css_rules' => array(
                '#pm_mc3_jgrowl_order_now' => array('option_active', 'background_color', 'border', 'padding', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align'),
                '#pm_mc3_jgrowl_order_now_btn' => array('border_radius', 'css3_padding', 'css3_background_flat', 'css3_button_line_height', 'css3_font_color', 'css3_background_color', 'css3_font_color_hover', 'css3_background_color_hover')
            ),
        ),
        'free_content_2' => array(
            'text_lang' => array(),
            'tinymce' => true,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 0,
            'padding' => array(0, 0, 0, 0),
            'background_color' => array('', ''),
            'css_rules' => array('#pm_mc3_jgrowl_free_content_2' => array('border', 'border_radius', 'padding', 'background_color')),
        )
    );
    public $foreachFields = array(
        'product_image',
        'product_name',
        'product_price'
    );
    public $templateParams = array(
        'css_rules' => array(
            'div#jGrowl' => array('margin'),
            'div#jGrowl div.jGrowl-notification.ui-state-jGrowl-theme-mc3' => array('modal_width', 'modal_background_color', 'modal_border_radius', 'border', 'padding', 'box_shadow'),
            'div#jGrowl div.jGrowl-notification, div#jGrowl div.jGrowl-closer' => array('modal_width', 'jgrowl_font_color'),
            'div#jGrowl div.jGrowl-notification.ui-state-jGrowl-theme-mc3 div.jGrowl-message' => array('jgrowl_font_color', 'jgrowl_font_size', 'jgrowl_font_style')
        ),
        'add_to_cart_selector' => '.ajax_add_to_cart_button',
        'product_add_to_cart_selector' => 'body#product p#add_to_cart input, body#product p#add_to_cart button',
        'display_free_content_1' => false,
        'display_free_content_2' => false,
        'modal_width' => 300,
        'modal_background_color' => array('#f5f6f7', '#d9d9d9'),
        'modal_border_radius' => 5,
        'border' => array(1, 0, 0, 0, 'solid', '#fefefe'),
        'padding' => array(6, 7, 7, 6),
        'margin' => array(11, 15, 0, 15),
        'box_shadow' => array(0, 0, 7, 0, 'outset', '#656565'),
        'jgrowl_position' => 'top-right',
        'jgrowl_sticky' => false,
        'jgrowl_lifetime' => 3000,
        'jgrowl_display_order_btn' => true,
        'jgrowl_font_color' => '#4d4d4d',
        'jgrowl_font_size' => 13,
        'jgrowl_font_style' => array(),
        'fields_order' => array(
            'pm_mc3_jgrowl_free_content_1',
            'pm_mc3_jgrowl_body',
            'pm_mc3_jgrowl_product_image',
            'pm_mc3_jgrowl_product_name',
            'pm_mc3_jgrowl_product_price',
            'pm_mc3_jgrowl_free_content_2',
            'pm_mc3_jgrowl_order_now',
        ),
    );
    public $templateKey = 'jgrowl';
    public $name = 'jGrowl';
    public $templateFilename = 'jgrowl-{id_shop}.tpl';
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
            $field_name = str_replace('pm_mc3_jgrowl_', '', $field_name);
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
			<div id="pm_mc3_jgrowl_container" class="clearfix">
				<table id="pm_mc3_jgrowl_table_container">
		';
        $colspan = 0;
        foreach ($this->getOrderedFields() as $field_name => $params) {
            switch ($field_name) {
                case 'product_image':
                case 'product_name':
                case 'product_price':
                    if ($this->isVisibleBlock($field_name)) {
                        $colspan++;
                    }
                    break;
            }
        }
        foreach ($this->getOrderedFields() as $field_name => $params) {
            switch ($field_name) {
                case 'product_image':
                    if ($this->isVisibleBlock($field_name)) {
                        $generated_template .= '
						<td id="pm_mc3_jgrowl_product_image">
							'.(isset($params['link']) &&  $params['link'] ? '<a href="{$product.image_link}" title="{$product.name|escape:\'htmlall\':\'UTF-8\'}">' : '').'
							<img src="{$product.image_src}" alt="{$product.name|escape:\'htmlall\':\'UTF-8\'}" />
							'.(isset($params['link']) &&  $params['link'] ? '</a>' : '').'
						</td>';
                    }
                    break;
                case 'product_name':
                    if ($this->isVisibleBlock($field_name)) {
                        if (isset($params['attributes']) && $params['attributes']) {
                            $generated_template .= '
							<td class="pm_mc3_jgrowl_product_name">
								<span class="pm_mc3_jgrowl_product_name_content">' . (isset($params['link']) && $params['link'] ? '<a href="{$product.image_link}" title="{$product.name|escape:\'htmlall\':\'UTF-8\'}">' : '').'{$product.name|escape:\'htmlall\':\'UTF-8\'}'.(isset($params['link']) && $params['link'] ? '</a>' : '') . '</span>
								' . '<br/>{if isset($product.isPack) && $product.isPack}<div class="pm_mc3_attributes_summary">{$product.attributes}</div>{else}<span class="pm_mc3_attributes_summary">{$product.attributes|escape:\'htmlall\':\'UTF-8\'}</span>{/if}
							</td>';
                        } else {
                            $generated_template .= '
							<td class="pm_mc3_jgrowl_product_name">
								<span class="pm_mc3_jgrowl_product_name_content">'.(isset($params['link']) && $params['link'] ? '<a href="{$product.image_link}" title="{$product.name|escape:\'htmlall\':\'UTF-8\'}">' : '').'{$product.name|escape:\'htmlall\':\'UTF-8\'}'.(isset($params['link']) && $params['link'] ? '</a>' : '') . '</span>
							</td>';
                        }
                    }
                    break;
                case 'product_price':
                    if ($this->isVisibleBlock($field_name)) {
                        $generated_template .= '
						<td class="pm_mc3_jgrowl_product_price">
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
                    break;
                case 'order_now':
                    if ($this->isVisibleBlock($field_name)) {
                        $generated_template .= '
						<tr id="pm_mc3_jgrowl_'.$field_name.'_container" class="pm_mc3_jgrowl_line">
							<td colspan="'.$colspan.'" id="pm_mc3_jgrowl_'.$field_name.'">
								<a href="{$order_page_link}" class="'.(isset($params['css3_background_flat']) && $params['css3_background_flat'] ? 'pm_mc3_actions_flat_button' : 'pm_mc3_actions_gradient_button').'" id="pm_mc3_jgrowl_'.$field_name.'_btn">{$'.$field_name.'}</a>
							</td><!-- #pm_mc3_jgrowl_'.$field_name.' -->
						</tr><!-- #pm_mc3_jgrowl_'.$field_name.'_container -->';
                    }
                    break;
                case 'free_content_1':
                case 'free_content_2':
                    if (isset($this->templateParams['display_'.$field_name]) && $this->templateParams['display_'.$field_name]) {
                        $generated_template .= '
							<tr id="pm_mc3_jgrowl_'.$field_name.'_container" class="pm_mc3_jgrowl_line">
								{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%nb_total_products%\':$nb_total_products}
								{if $priceDisplay != 1}
									{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%cart_total%\':{convertPrice price=$summary.total_price}}
								{else}
									{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%cart_total%\':{convertPrice price=$summary.total_price_without_tax}}
								{/if}
								<td colspan="'.$colspan.'" id="pm_mc3_jgrowl_'.$field_name.'">{$'.$field_name.'}</td><!-- #pm_mc3_jgrowl_'.$field_name.' -->
							</tr>
							<!-- #pm_mc3_jgrowl_'.$field_name.'_container -->';
                    }
                    break;
                default:
                    if ((isset($params['option_active']) && $params['option_active']) || !isset($params['option_active'])) {
                        $generated_template .= '
							{if isset($'.$field_name.') && !empty($'.$field_name.')}
								<tr id="pm_mc3_jgrowl_'.$field_name.'_container" class="pm_mc3_jgrowl_line">
									<td colspan="'.$colspan.'" id="pm_mc3_jgrowl_'.$field_name.'">{$'.$field_name.'}</td><!-- #pm_mc3_jgrowl_'.$field_name.' -->
								</tr>
								<!-- #pm_mc3_jgrowl_'.$field_name.'_container -->
							{/if}
						';
                    }
                    break;
            }
        }
        $generated_template .= '
				</table><!-- #pm_mc3_jgrowl_table_container -->
			</div><!-- #pm_mc3_jgrowl_container -->
		</div><!-- #pm_mc3_content -->
		';
        return $this->writeGeneratedTpl($generated_template);
    }
    public function getBackOfficeCSS()
    {
        return '
			ul#pm_mc3_jgrowl_global_sort {text-align: center;}
			ul#pm_mc3_jgrowl_global_sort li {background-color: white; border: 1px solid black;}
			#pm_mc3_jgrowl_cart_summary {margin: 0 auto; width: 90%; border-spacing: 3px; border-collapse: separate;}
			#pm_mc3_jgrowl_cart_summary tr td {border: 1px solid black;}
			#pm_mc3_jgrowl_product_line_sort li {float:left;}
		';
    }
    public function getBackOfficeTemplate()
    {
        $pm_mc3_jgrowl_product_line_sort_content = array(
            'pm_mc3_jgrowl_product_image' => '
				<td id="pm_mc3_jgrowl_product_image" class="product_image'.(!$this->isVisibleBlock('product_image') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_image" class="pm_translate">{translate_product_image}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_image" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</td>
			',
            'pm_mc3_jgrowl_product_name' => '
				<td id="pm_mc3_jgrowl_product_name" class="product_name'.(!$this->isVisibleBlock('product_name') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_name" class="pm_translate">{translate_product_name}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_name" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</td>
			',
            'pm_mc3_jgrowl_product_price' => '
				<td id="pm_mc3_jgrowl_product_price" class="product_price'.(!$this->isVisibleBlock('product_price') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_price" class="pm_translate">{translate_product_price}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_price" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</td>
			',
        );
        $global_items = array(
            'pm_mc3_jgrowl_free_content_1' => '
				<li id="pm_mc3_jgrowl_free_content_1">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_free_content_1" class="pm_translate">{translate_free_content_1}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=free_content_1" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>',
            'pm_mc3_jgrowl_body' => '
				<li id="pm_mc3_jgrowl_body">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<a rel="860_500_1" href="{config_url}&amp;item=cart_summary" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
					<table id="pm_mc3_jgrowl_cart_summary">
						<tr id="pm_mc3_jgrowl_product_line_sort" class="ui-sortable">
							{pm_mc3_jgrowl_product_line_sort_content}
						</tr>
					</table>
				</li>
			',
            'pm_mc3_jgrowl_order_now' => '
				<li id="pm_mc3_jgrowl_order_now">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_free_content_2" class="pm_translate">{translate_order_now}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=order_now" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
            'pm_mc3_jgrowl_free_content_2' => '
				<li id="pm_mc3_jgrowl_free_content_2">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_free_content_2" class="pm_translate">{translate_free_content_2}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=free_content_2" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
        );
        $template = '<ul id="pm_mc3_jgrowl_global_sort" class="ui-sortable">';
        foreach ($this->templateParams['fields_order'] as $field_name) {
            if (isset($global_items[$field_name])) {
                if ($field_name == 'pm_mc3_jgrowl_body') {
                    $pm_mc3_jgrowl_product_line_sort = '';
                    foreach ($this->templateParams['fields_order'] as $field_name2) {
                        if (isset($pm_mc3_jgrowl_product_line_sort_content[$field_name2])) {
                            $pm_mc3_jgrowl_product_line_sort .= $pm_mc3_jgrowl_product_line_sort_content[$field_name2];
                        }
                    }
                    $template .= str_replace('{pm_mc3_jgrowl_product_line_sort_content}', $pm_mc3_jgrowl_product_line_sort, $global_items[$field_name]);
                } else {
                    $template .= $global_items[$field_name];
                }
            }
        }
        $template .= '</ul>';
        return $template;
    }
}
