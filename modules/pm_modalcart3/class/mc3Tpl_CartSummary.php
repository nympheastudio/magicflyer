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

class mc3Tpl_CartSummary extends mc3Tpl
{
    // Every needed fields of the templates (ordered)
    public $fields = array(
        'free_content_1' => array(
            'text_lang' => array(),
            'tinymce' => true,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 0,
            'padding' => array(0, 0, 0, 0),
            'margin' => array(0, 0, 0, 0),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('', ''),
            'css_rules' => array('#pm_mc3_cs_free_content_1' => array('border', 'border_radius', 'margin', 'box_shadow', 'padding', 'background_color')),
        ),
        'title' => array(
            'text_lang' => array(),
            'font_color' => '#ffffff',
            'font_size' => 14,
            'text_align' => 'left',
            'font_style' => array('bold'),
            'text_shadow' => array(1, 1, 1, '#202020'),
            'border' => array(1, 1, 1, 1, 'solid', '#f6ad37'),
            'border_radius' => 3,
            'padding' => array(10, 20, 10, 15),
            'margin' => array(2, 2, 0, 2),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('#fde362', '#f5b928'),
            'css_rules' => array('#pm_mc3_cs_title' => array('font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'border_radius', 'margin', 'padding', 'box_shadow', 'background_color')),
        ),
        'cart_summary' => array(
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'margin' => array(0, 0, 0, 0),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('', ''),
            'css_rules' => array('#pm_mc3_cart_summary' => array('border', 'margin', 'box_shadow', 'background_color')),
        ),
        'product_image' => array(
            'option_active' => true,
            'link' => true,
            'text_align' => 'center',
            'image_size' => 'small_default',
            'border' => array(0, 0, 1, 0, 'dashed', '#d0d0d0'),
            'content_border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 0,
            'padding' => array(10, 10, 10, 10),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'width' => 45,
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array(
                '.pm_mc3_cs_product_image' => array('option_active', 'width', 'border', 'padding', 'background_color', 'text_align', 'vertical_align'),
                '.pm_mc3_cs_product_image img' => array('content_border', 'box_shadow', 'border_radius')
            ),
        ),
        'product_name' => array(
            'option_active' => true,
            'attributes' => true,
            'link' => true,
            'font_color' => '#707070',
            'font_size' => 12,
            'text_align' => 'left',
            'font_style' => array('bold'),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 1, 0, 'dashed', '#d0d0d0'),
            'padding' => array(10, 0, 10, 0),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'attributes_font_color' => '#707070',
            'attributes_font_size' => 10,
            'attributes_font_style' => array(),
            'attributes_text_shadow' => array(0, 0, 0, ''),
            'css_rules' => array(
                '.pm_mc3_cs_product_name' => array('option_active', 'width', 'text_align', 'border', 'padding', 'background_color', 'vertical_align'),
                '.pm_mc3_cs_product_name span.pm_mc3_cs_product_name_content' => array('font_color', 'font_size', 'text_shadow', 'font_style'),
                '.pm_mc3_cs_product_name a' => array('font_color', 'font_size', 'text_shadow', 'font_style'),
                '.pm_mc3_attributes_summary' => array('attributes_font_color', 'attributes_font_size', 'attributes_text_shadow', 'attributes_font_style')
            ),
        ),
        'product_availability' => array(
            'option_active' => false,
            'font_color' => '#444444',
            'font_size' => 11,
            'text_align' => 'center',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 1, 0, 'dashed', '#d0d0d0'),
            'padding' => array(10, 0, 10, 0),
            'width' => '',
            'background_color' => array('', ''),
            'css_rules' => array('.pm_mc3_cs_product_availability' => array('option_active', 'width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color')),
        ),
        'product_quantity' => array(
            'allow_quantity_update' => true,
            'option_active' => true,
            'font_color' => '#444444',
            'font_size' => 11,
            'text_align' => 'center',
            'font_style' => array('bold'),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 1, 0, 'dashed', '#d0d0d0'),
            'padding' => array(10, 10, 10, 10),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('.pm_mc3_cs_product_quantity' => array('option_active', 'width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color', 'vertical_align')),
        ),
        'product_price' => array(
            'option_active' => false,
            'font_color' => '#444444',
            'font_size' => 12,
            'text_align' => 'right',
            'font_style' => array('bold'),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 1, 0, 'dashed', '#d0d0d0'),
            'padding' => array(10, 0, 10, 0),
            'width' => '',
            'vertical_align' => 'middle',
            'css_rules' => array('.pm_mc3_cs_product_price' => array('option_active', 'width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color', 'vertical_align')),
        ),
        'product_tax' => array(
            'option_active' => false,
            'font_color' => '#444444',
            'font_size' => 11,
            'text_align' => 'left',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 1, 0, 'solid', '#d0d0d0'),
            'padding' => array(10, 0, 10, 0),
            'width' => '',
            'vertical_align' => 'middle',
            'css_rules' => array('.pm_mc3_cs_product_tax' => array('option_active', 'width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color', 'vertical_align')),
        ),
        'product_total' => array(
            'option_active' => true,
            'font_color' => '#444444',
            'font_size' => 12,
            'text_align' => 'right',
            'font_style' => array('bold'),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 1, 0, 'dashed', '#d0d0d0'),
            'padding' => array(10, 10, 10, 0),
            'width' => 80,
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('.pm_mc3_cs_product_total' => array('option_active', 'width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color', 'vertical_align')),
        ),
        'subtotal_label' => array(
            'text_lang' => array(),
            'font_color' => '#8e8e8e',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(1, 0, 0, 0, 'solid', '#d0d0d0'),
            'padding' => array(10, 10, 0, 0),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('#pm_mc3_cs_subtotal_label' => array('width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color', 'vertical_align')),
        ),
        'subtotal_value' => array(
            'force_no_tax' => false,
            'font_color' => '#8e8e8e',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(1, 0, 0, 0, 'solid', '#d0d0d0'),
            'padding' => array(10, 10, 0, 10),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('#pm_mc3_cs_subtotal_value' => array('width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color')),
        ),
        'total_label' => array(
            'text_lang' => array(),
            'font_color' => '#8e8e8e',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array('bold'),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(5, 10, 10, 0),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('#pm_mc3_cs_total_label' => array('width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color', 'vertical_align')),
        ),
        'total_value' => array(
            'font_color' => '#8e8e8e',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array('bold'),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(5, 10, 10, 10),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('#pm_mc3_cs_total_value' => array('width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color')),
        ),
        'discounts_label' => array(
            'text_lang' => array(),
            'font_color' => '#8e8e8e',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(5, 10, 0, 0),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('#pm_mc3_cs_discounts_label' => array('width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color', 'vertical_align')),
        ),
        'discounts_value' => array(
            'font_color' => '#8e8e8e',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(5, 10, 0, 10),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('#pm_mc3_cs_discounts_value' => array('width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color')),
        ),
        'total_tax_label' => array(
            'text_lang' => array(),
            'font_color' => '#8e8e8e',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(5, 10, 0, 0),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('#pm_mc3_cs_total_tax_label' => array('width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color', 'vertical_align')),
        ),
        'total_tax_value' => array(
            'font_color' => '#8e8e8e',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(5, 10, 0, 10),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('#pm_mc3_cs_total_tax_value' => array('width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color')),
        ),
        'shipping_value' => array(
            'font_color' => '#8e8e8e',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(5, 10, 0, 10),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('#pm_mc3_cs_shipping_value' => array('width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color')),
        ),
        'shipping_label' => array(
            'text_lang' => array(),
            'font_color' => '#8e8e8e',
            'font_size' => 11,
            'text_align' => 'right',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'padding' => array(5, 10, 0, 0),
            'width' => '',
            'background_color' => array('', ''),
            'vertical_align' => 'middle',
            'css_rules' => array('#pm_mc3_cs_shipping_label' => array('width', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align', 'border', 'padding', 'background_color')),
        ),
        'footer_actions_container' => array(
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 0,
            'padding' => array(0, 0, 0, 0),
            'margin' => array(0, 0, 0, 0),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('', ''),
            'css_rules' => array('#pm_mc3_cs_footer_actions_container' => array('border', 'border_radius', 'padding', 'margin', 'box_shadow', 'background_color')),
        ),
        'keep_shopping' => array(
            'option_active' => true,
            'button' => true,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 3,
            'padding' => array(20, 0, 20, 15),
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
                '#pm_mc3_cs_keep_shopping' => array('option_active', 'background_color', 'border', 'padding', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align'),
                '#pm_mc3_cs_keep_shopping_btn' => array('border_radius', 'css3_padding', 'css3_background_flat', 'css3_button_line_height', 'css3_font_color', 'css3_background_color', 'css3_font_color_hover', 'css3_background_color_hover')
            ),
        ),
        'order_now' => array(
            'option_active' => true,
            'button' => true,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 3,
            'padding' => array(20, 15, 20, 0),
            'background_color' => array('', ''),
            'text_lang' => array(),
            'font_color' => '',
            'font_size' => 12,
            'text_align' => 'center',
            'font_style' => array(),
            'text_shadow' => array(0, 0, 0, ''),
            'css3_padding' => array(5, 10, 5, 10),
            'css3_button_line_height' => 10,
            'css3_font_color' => '#000000',
            'css3_background_color' => '#ffffff',
            'css3_font_color_hover' => '#000000',
            'css3_background_color_hover' => '#fcdb46',
            'css3_background_flat' => true,
            'css_rules' => array(
                '#pm_mc3_cs_order_now' => array('option_active', 'background_color', 'border', 'padding', 'font_color', 'font_size', 'text_shadow', 'font_style', 'text_align'),
                '#pm_mc3_cs_order_now_btn' => array('border_radius', 'css3_padding', 'css3_background_flat', 'css3_button_line_height', 'css3_font_color', 'css3_background_color', 'css3_font_color_hover', 'css3_background_color_hover')
            ),
        ),
        'free_content_2' => array(
            'text_lang' => array(),
            'tinymce' => true,
            'border' => array(0, 0, 0, 0, 'solid', ''),
            'border_radius' => 0,
            'padding' => array(0, 0, 0, 0),
            'margin' => array(0, 0, 20, 0),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('', ''),
            'css_rules' => array('#pm_mc3_cs_free_content_2' => array('border', 'border_radius', 'padding', 'margin', 'box_shadow', 'background_color')),
        ),
        'free_shipping' => array(
            'text_lang' => array(),
            'tinymce' => true,
            'border' => array(1, 1, 1, 1, 'solid', '#dedede'),
            'border_radius' => 0,
            'padding' => array(5, 10, 5, 10),
            'margin' => array(10, 0, 0, 0),
            'box_shadow' => array(0, 0, 0, 0, 'outset', ''),
            'background_color' => array('#f5f5f5', ''),
            'css_rules' => array('#pm_mc3_cs_free_shipping' => array('border', 'border_radius', 'padding', 'margin', 'box_shadow', 'background_color')),
        ),
        'hook_cross_selling_on_cart' => array(
            'css_rules' => array('#pm_mc3_cs_hook_cross_selling_on_cart' => array()),
        ),
    );
    public $foreachFields = array(
        'product_image',
        'product_name',
        'product_availability',
        'product_quantity',
        'product_price',
        'product_tax',
        'product_total'
    );
    public $templateParams = array(
        'css_rules' => array(
            '.mfp-content' => array('box_shadow', 'modal_width', 'modal_background_color', 'modal_border_radius', 'border', 'padding'),
            '.mfp-bg' => array('background_overlay_color', 'background_overlay_opacity')
        ),
        'add_to_cart_selector' => '.ajax_add_to_cart_button',
        'product_add_to_cart_selector' => 'body#product p#add_to_cart input, body#product p#add_to_cart button',
        'display_free_content_1' => false,
        'display_free_content_2' => false,
        'display_free_shipping' => true,
        'display_discounts' => false,
        'display_subtotal' => true,
        'display_taxes' => true,
        'display_title' => true,
        'display_table_header' => true,
        'display_shipping' => true,
        'display_hook_cross_selling_on_cart' => false,
        'display_on_mobile' => false,
        'display_close_button' => false,
        'modal_width' => 500,
        'modal_background_color' => array('#ffffff', ''),
        'modal_border_radius' => 5,
        'border' => array(1, 1, 1, 1, 'solid', '#dcdcdc'),
        'padding' => array(0, 0, 0, 0),
        'box_shadow' => array(2, 2, 5, 0, 'outset', '#000000'),
        'background_overlay' => true,
        'background_overlay_opacity' => 80,
        'background_overlay_color' => array('#ffffff', ''),
        'fields_order' => array(
            'pm_mc3_cs_title',
            'pm_mc3_cs_free_content_1',
            'pm_mc3_cs_body',
            'pm_mc3_cs_product_image',
            'pm_mc3_cs_product_name',
            'pm_mc3_cs_product_availability',
            'pm_mc3_cs_product_price',
            'pm_mc3_cs_product_tax',
            'pm_mc3_cs_product_quantity',
            'pm_mc3_cs_product_total',
            'pm_mc3_cs_subtotal_label',
            'pm_mc3_cs_subtotal_value',
            'pm_mc3_cs_shipping_label',
            'pm_mc3_cs_shipping_value',
            'pm_mc3_cs_discounts_label',
            'pm_mc3_cs_discounts_value',
            'pm_mc3_cs_total_tax_label',
            'pm_mc3_cs_total_tax_value',
            'pm_mc3_cs_total_label',
            'pm_mc3_cs_total_value',
            'pm_mc3_cs_free_shipping',
            'pm_mc3_cs_footer',
            'pm_mc3_cs_keep_shopping',
            'pm_mc3_cs_order_now',
            'pm_mc3_cs_free_content_2',
            'pm_mc3_cs_hook_cross_selling_on_cart',
        ),
    );
    public $templateKey = 'cs';
    public $name = 'Cart Summary';
    public $templateFilename = 'cart_summary-{id_shop}.tpl';
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
            $field_name = str_replace('pm_mc3_cs_', '', $field_name);
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
			<div id="pm_mc3_cs_container" class="clearfix">
		';
        $customized_product_template = '';
        $started_foreach = false;
        $started_footer_actions_buttons = false;
        $shopping_button_added = false;
        $table_needed = false;
        $colspan = 0;
        $table_header_set = false;
        $table_header_content = '';
        $table_footer_content = '';
        if (!isset($this->templateParams['display_table_header']) || !$this->templateParams['display_table_header']) {
            $table_header_set = true;
        }
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
					<table id="pm_mc3_cart_summary">
						<thead>
							{pm_mc3_CartSummaryHeader}
						</thead>
						<tbody>
							{foreach from=$summary.products item=product}
								{assign var=\'productId\' value=$product.id_product}
								{assign var=\'productAttributeId\' value=$product.id_product_attribute}
								<tr class="pm_mc3_cs_classic_product_line">
					';
                    $started_foreach = true;
                } elseif (!in_array($field_name, $this->getProductLineFields()) && $started_foreach) {
                    $generated_template .= '
								</tr>
								{if isset($customizedDatas.$productId.$productAttributeId)}
									{foreach from=$customizedDatas.$productId.$productAttributeId key=\'id_customization\' item=\'customization\'}
										<tr class="pm_mc3_cs_customized_product_line">
											{pm_mc3_CartSummaryCustomizedTemplate}
										</tr>
									{/foreach}
								{/if}
							{/foreach}
						</tbody>
						<tfoot>
							{pm_mc3_CartSummaryFooter}
						</tfoot>
					</table><!-- #pm_mc3_cart_summary -->';
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
							<div id="pm_mc3_cs_'.$field_name.'" class="pm_mc3_cs_line">
								{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%nb_total_products%\':$nb_total_products}
								{if $priceDisplay != 1}
									{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%cart_total%\':{convertPrice price=$summary.total_price}}
								{else}
									{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%cart_total%\':{convertPrice price=$summary.total_price_without_tax}}
								{/if}
								{$'.$field_name.'}
							</div><!-- #pm_mc3_cs_'.$field_name.' -->
						{/if}';
                    }
                    break;
                case 'free_shipping':
                    if (isset($this->templateParams['display_'.$field_name]) && $this->templateParams['display_'.$field_name]) {
                        $generated_template .= '
							{if $summary.free_ship > 0 && !$isVirtualCart}
								<div id="pm_mc3_cs_'.$field_name.'" class="pm_mc3_cs_line">
									{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%free_shipping%\':{convertPrice price=$summary.free_ship}}
									{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%nb_total_products%\':$nb_total_products}
									{if $priceDisplay != 1}
										{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%cart_total%\':{convertPrice price=$summary.total_price}}
									{else}
										{assign var=\''.$field_name.'\' value=$'.$field_name.'|replace:\'%cart_total%\':{convertPrice price=$summary.total_price_without_tax}}
									{/if}
									{$'.$field_name.'}
								</div><!-- #pm_mc3_cs_'.$field_name.' -->
							{/if}
						';
                    }
                    break;
                case 'product_name':
                    if ($this->isVisibleBlock($field_name)) {
                        if (!$table_header_set) {
                            $table_header_content .= '<th>{$'.$field_name.'_thead}</th>';
                        }
                        if (isset($params['attributes']) && $params['attributes']) {
                            $generated_template .= '
							<td class="pm_mc3_cs_product_name">
								<span class="pm_mc3_cs_product_name_content">' . (isset($params['link']) && $params['link'] ? '<a href="{$product.image_link}" title="{$product.name|escape:\'htmlall\':\'UTF-8\'}">' : '').'{$product.name|escape:\'htmlall\':\'UTF-8\'}'.(isset($params['link']) && $params['link'] ? '</a>' : '') . '</span>
								' . '<br/>{if isset($product.isPack) && $product.isPack}<div class="pm_mc3_attributes_summary">{$product.attributes}</div>{else}<span class="pm_mc3_attributes_summary">{$product.attributes|escape:\'htmlall\':\'UTF-8\'}</span>{/if}
							</td>';
                        } else {
                            $generated_template .= '
							<td class="pm_mc3_cs_product_name">
								<span class="pm_mc3_cs_product_name_content">'.(isset($params['link']) && $params['link'] ? '<a href="{$product.image_link}" title="{$product.name|escape:\'htmlall\':\'UTF-8\'}">' : '').'{$product.name|escape:\'htmlall\':\'UTF-8\'}'.(isset($params['link']) && $params['link'] ? '</a>' : '') . '</span>
							</td>';
                        }
                        $customized_product_template .= '<td class="pm_mc3_cs_product_name">
							{foreach from=$customization.datas key=\'type\' item=\'datas\'}
								{if $type == $CUSTOMIZE_FILE}
									<div class="customizationUploaded">
										<ul class="customizationUploaded">
											{foreach from=$datas item=\'picture\'}<li><img src="{$pic_dir}{$picture.value}_{$imageSize}" alt="" class="customizationUploaded" width="{$imageWidth}" height="{$imageHeight}"/></li>{/foreach}
										</ul>
									</div>
								{elseif $type == $CUSTOMIZE_TEXTFIELD}
									<ul class="typedText">
										{foreach from=$datas item=\'textField\' name=\'typedText\'}<li>{if $textField.name}{$textField.name}{else}{$customization_text_field_label}{$smarty.foreach.typedText.index+1}{/if}: {$textField.value}</li>{/foreach}
									</ul>
								{/if}
							{/foreach}
						</td>';
                        $colspan++;
                    }
                    break;
                case 'product_image':
                    if ($this->isVisibleBlock($field_name)) {
                        if (!$table_header_set) {
                            $table_header_content .= '<th>{$'.$field_name.'_thead}</th>';
                        }
                        $generated_template .= '
						<td class="pm_mc3_cs_product_image">
							'.(isset($params['link']) &&  $params['link'] ? '<a href="{$product.image_link}" title="{$product.name|escape:\'htmlall\':\'UTF-8\'}">' : '').'
							<img src="{$product.image_src}" alt="{$product.name|escape:\'htmlall\':\'UTF-8\'}" />
							'.(isset($params['link']) &&  $params['link'] ? '</a>' : '').'
						</td>';
                        $customized_product_template .= '<td class="pm_mc3_cs_customized_product_empty_cell"></td>';
                        $colspan++;
                    }
                    break;
                case 'product_availability':
                    if ($this->isVisibleBlock($field_name)) {
                        if (!$table_header_set) {
                            $table_header_content .= '<th>{$'.$field_name.'_thead}</th>';
                        }
                        $generated_template .= '
						<td class="pm_mc3_cs_product_availability">
						{if version_compare($smarty.const._PS_VERSION_, \'1.6.0.0\', \'>=\')}
							<span class="availability">
								<span class="label{if $product.quantity_available <= 0 && isset($product.allow_oosp) && !$product.allow_oosp} label-danger{elseif $product.quantity_available <= 0} label-warning{else} label-success{/if}">
									{if $product.quantity_available <= 0}
										{if isset($product.allow_oosp) && $product.allow_oosp}
											{if isset($product.available_later) && $product.available_later}
												{$product.available_later}
											{else}
												{$product_is_in_stock}
											{/if}
										{else}
											{$product_is_out_of_stock}
										{/if}
									{else}
										{if isset($product.available_now) && $product.available_now}
											{$product.available_now}
										{else}
											{$product_is_in_stock}
										{/if
									}{/if}
								</span>
							</span>
						{else}
							{if $product.active AND ($product.allow_oosp OR ($product.quantity <= $product.stock_quantity)) AND $product.available_for_order AND !$PS_CATALOG_MODE}
								<img alt="{$product_is_available}" src="'._THEME_IMG_DIR_.'icon/available.gif" />
							{else}
								<img alt="{$product_is_out_of_stock}" src="'._THEME_IMG_DIR_.'icon/unavailable.gif" />
							{/if}
						{/if}
						</td>
						';
                        $customized_product_template .= '<td class="pm_mc3_cs_customized_product_empty_cell"></td>';
                        $colspan++;
                    }
                    break;
                case 'product_price':
                    if ($this->isVisibleBlock($field_name)) {
                        if (!$table_header_set) {
                            $table_header_content .= '<th>{$'.$field_name.'_thead}</th>';
                        }
                        $generated_template .= '
						<td class="pm_mc3_cs_product_price">
							{if $product.price == 0 && isset($product.gift) && $product.gift}
								{$gift_product}
							{else}
								{if $priceDisplay != 1}
									{convertPrice price=($product.price_wt + $product.ecotax_wt)}
								{else}
									{convertPrice price=($product.price + $product.ecotax_wt)}
								{/if}
								{if isset($product.ecotax) && $product.ecotax > 0}
									<br /><small>({$including_ecotax|replace:\'%ecotax%\':{convertPrice price=$product.ecotax_wt}})</small>
								{/if}
							{/if}
						</td>';
                        $customized_product_template .= '<td class="pm_mc3_cs_customized_product_empty_cell"></td>';
                        $colspan++;
                    }
                    break;
                case 'product_tax':
                    if ($this->isVisibleBlock($field_name)) {
                        if (!$table_header_set) {
                            $table_header_content .= '<th>{$'.$field_name.'_thead}</th>';
                        }
                        $generated_template .= '
						<td class="pm_mc3_cs_product_tax">
							{if $product.price == 0 && isset($product.gift) && $product.gift}
								{$gift_product}
							{else}
								{if $priceDisplay != 1}{convertPrice price=($product.price_wt-$product.price)*$product.cart_quantity}{else}{convertPrice price=0}{/if}
							{/if}
						</td>';
                        $customized_product_template .= '<td class="pm_mc3_cs_customized_product_empty_cell"></td>';
                        $colspan++;
                    }
                    break;
                case 'product_quantity':
                    if ($this->isVisibleBlock($field_name)) {
                        if (!$table_header_set) {
                            $table_header_content .= '<th>{$'.$field_name.'_thead}</th>';
                        }
                        $generated_template .= '
						<td class="pm_mc3_cs_product_quantity">
							<div class="pm_mc3_cs_product_quantity_value">{$product.cart_quantity|intval}</div>
							'.(isset($params['allow_quantity_update']) && $params['allow_quantity_update'] ? '
							{if !isset($customizedDatas.$productId) && !isset($customizedDatas.$productId.$productAttributeId)}
								<div class="pm_mc3_quantity_buttons_container">
									<span class="pm_mc3_quantity_less" onclick="return modalAjaxCart.updateModalQty(\'del\', \'{$product.id_product|intval}\', \'{$product.id_product_attribute|intval}\', \'{$product.id_address_delivery|intval}\', \'{$product.id_customization|intval}\');">-</span>
									<span class="pm_mc3_quantity_more" onclick="return modalAjaxCart.updateModalQty(\'add\', \'{$product.id_product|intval}\', \'{$product.id_product_attribute|intval}\', \'{$product.id_address_delivery|intval}\', \'{$product.id_customization|intval}\');">+</span>
									<span class="pm_mc3_quantity_delete" onclick="return modalAjaxCart.removeModalProduct(\'{$product.id_product|intval}\', \'{$product.id_product_attribute|intval}\', \'{$product.id_customization|intval}\', \'{$product.id_address_delivery|intval}\');">x</span>
								</div>
							{/if}
							' : '').'
						</td>';
                        $customized_product_template .= '
						<td class="pm_mc3_cs_product_quantity">
							<div class="pm_mc3_cs_product_quantity_value">{$customization.quantity|intval}</div>
							'.(isset($params['allow_quantity_update']) && $params['allow_quantity_update'] ? '
							<div class="pm_mc3_quantity_buttons_container">
								<span class="pm_mc3_quantity_less" onclick="return modalAjaxCart.updateModalQty(\'del\', \'{$product.id_product|intval}\', \'{$product.id_product_attribute|intval}\', \'{$product.id_address_delivery|intval}\', \'{$id_customization|intval}\');">-</span>
								<span class="pm_mc3_quantity_more" onclick="return modalAjaxCart.updateModalQty(\'add\', \'{$product.id_product|intval}\', \'{$product.id_product_attribute|intval}\', \'{$product.id_address_delivery|intval}\', \'{$id_customization|intval}\');">+</span>
								<span class="pm_mc3_quantity_delete" onclick="return modalAjaxCart.removeModalProduct(\'{$product.id_product|intval}\', \'{$product.id_product_attribute|intval}\', \'{$id_customization|intval}\', \'{$product.id_address_delivery|intval}\');">x</span>
							</div>
							' : '').'
						</td>';
                        $colspan++;
                    }
                    break;
                case 'product_total':
                    if ($this->isVisibleBlock($field_name)) {
                        if (!$table_header_set) {
                            $table_header_content .= '<th>{$'.$field_name.'_thead}</th>';
                        }
                        $generated_template .= '
						<td class="pm_mc3_cs_product_total">
							{if $product.price == 0 && isset($product.gift) && $product.gift}
								{$gift_product}
							{else}
								{if $priceDisplay != 1}
									{convertPrice price=($product.price_wt + $product.ecotax_wt) * $product.cart_quantity}
								{else}
									{convertPrice price=($product.price + $product.ecotax_wt) * $product.cart_quantity}
								{/if}
								{if isset($product.ecotax) && $product.ecotax > 0}
									<br /><small>({$including_ecotax|replace:\'%ecotax%\':{convertPrice price=$product.ecotax_wt * $product.cart_quantity}})</small>
								{/if}
							{/if}
						</td>';
                        $customized_product_template .= '<td class="pm_mc3_cs_customized_product_empty_cell"></td>';
                        $colspan++;
                    }
                    break;
                case 'subtotal_label':
                    if (isset($this->templateParams['display_subtotal']) && $this->templateParams['display_subtotal']) {
                        $table_footer_content .= '
						<tr>
							<td colspan="'.($colspan - 1).'" id="pm_mc3_cs_subtotal_label">{$'.$field_name.'}
						</td>';
                    }
                    break;
                case 'subtotal_value':
                    if (isset($this->templateParams['display_subtotal']) && $this->templateParams['display_subtotal']) {
                        if (isset($params['force_no_tax']) && $params['force_no_tax']) {
                            $table_footer_content .= '
								<td id="pm_mc3_cs_subtotal_value">
									{convertPrice price=$summary.total_products}
								</td>
							</tr>';
                        } else {
                            $table_footer_content .= '
								<td id="pm_mc3_cs_subtotal_value">
									{if $priceDisplay != 1}
										{convertPrice price=$summary.total_products_wt}
									{else}
										{convertPrice price=$summary.total_products}
									{/if}
								</td>
							</tr>';
                        }
                    }
                    break;
                case 'total_label':
                    $table_footer_content .= '
					<tr>
						<td colspan="'.($colspan - 1).'" id="pm_mc3_cs_total_label">{$'.$field_name.'}
					</td>';
                    break;
                case 'total_value':
                    $table_footer_content .= '
						<td id="pm_mc3_cs_total_value">
							{if $use_taxes}{convertPrice price=$summary.total_price}{else}{convertPrice price=$summary.total_price_without_tax}{/if}
						</td>
					</tr>';
                    break;
                case 'discounts_label':
                    if (isset($this->templateParams['display_discounts']) && $this->templateParams['display_discounts']) {
                        $table_footer_content .= '
						{if $summary.total_discounts_tax_exc != 0}
						<tr>
							<td colspan="'.($colspan - 1).'" id="pm_mc3_cs_discounts_label">{$'.$field_name.'}
						</td>
						{/if}';
                    }
                    break;
                case 'discounts_value':
                    if (isset($this->templateParams['display_discounts']) && $this->templateParams['display_discounts']) {
                        $table_footer_content .= '
						{if $summary.total_discounts_tax_exc != 0}
							<td id="pm_mc3_cs_discounts_value">
								{if $priceDisplay != 1}
									{if $summary.total_discounts < 0}{convertPrice price=$summary.total_discounts}{else}{convertPrice price=$summary.total_discounts*-1}{/if}
								{else}
									{if $summary.total_discounts_tax_exc < 0}{convertPrice price=$summary.total_discounts_tax_exc}{else}{convertPrice price=$summary.total_discounts_tax_exc*-1}{/if}
								{/if}
							</td>
						</tr>
						{/if}';
                    }
                    break;
                case 'total_tax_label':
                    if (isset($this->templateParams['display_taxes']) && $this->templateParams['display_taxes']) {
                        $table_footer_content .= '
						{if $use_taxes}
							<tr>
								<td colspan="'.($colspan - 1).'" id="pm_mc3_cs_total_tax_label">{$'.$field_name.'}</td>
						{/if}';
                    }
                    break;
                case 'total_tax_value':
                    if (isset($this->templateParams['display_taxes']) && $this->templateParams['display_taxes']) {
                        $table_footer_content .= '
						{if $use_taxes}
								<td id="pm_mc3_cs_total_tax_value">
									{convertPrice price=$summary.total_tax}
								</td>
							</tr>
						{/if}';
                    }
                    break;
                case 'shipping_label':
                    if (isset($this->templateParams['display_shipping']) && $this->templateParams['display_shipping']) {
                        $table_footer_content .= '
						{if !$isVirtualCart}
						<tr>
							<td colspan="'.($colspan - 1).'" id="pm_mc3_cs_shipping_label">{$'.$field_name.'}</td>
						{/if}';
                    }
                    break;
                case 'shipping_value':
                    if (isset($this->templateParams['display_shipping']) && $this->templateParams['display_shipping']) {
                        $table_footer_content .= '
						{if !$isVirtualCart}
							{if $summary.total_shipping_tax_exc == 0}
								<td id="pm_mc3_cs_shipping_value" class="pm_mc3_cs_free_shipping">
									{$free_shipping_on_cart}
								</td>
							{else}
								<td id="pm_mc3_cs_shipping_value">
									{if $priceDisplay != 1}
										{convertPrice price=$summary.total_shipping}
									{else}
										{convertPrice price=$summary.total_shipping_tax_exc}
									{/if}
								</td>
							{/if}
						</tr>
						{/if}';
                    }
                    break;
                case 'cart_summary':
                    break;
                case 'keep_shopping':
                    if (!$started_footer_actions_buttons) {
                        $started_footer_actions_buttons = $field_name;
                        $generated_template .= '<div id="pm_mc3_cs_footer_actions_container" class="clearfix pm_mc3_cs_line">';
                    }
                    if ($this->isVisibleBlock($field_name)) {
                        $generated_template .= '
						<div id="pm_mc3_cs_keep_shopping_container" class="clearfix pm_mc3_button_'.(!$shopping_button_added ? 'left' : 'right').'">
							<div id="pm_mc3_cs_'.$field_name.'" class="clearfix pm_mc3_button_'.(!$shopping_button_added ? 'left' : 'right').'_sub">
								<a href="javascript:mc3_continueShopping();" id="pm_mc3_cs_'.$field_name.'_btn" class="'.(isset($params['css3_background_flat']) && $params['css3_background_flat'] ? 'pm_mc3_actions_flat_button' : 'pm_mc3_actions_gradient_button').'">{$'.$field_name.'}</a>
							</div><!-- #pm_mc3_cs_'.$field_name.' -->
						</div><!-- #pm_mc3_cs_keep_shopping_container -->';
                        $shopping_button_added = true;
                    }
                    if ($started_footer_actions_buttons !== false && $started_footer_actions_buttons != $field_name) {
                        $generated_template .= '</div><!-- #pm_mc3_cs_footer_actions_container -->';
                    }
                    break;
                case 'order_now':
                    if (!$started_footer_actions_buttons) {
                        $started_footer_actions_buttons = $field_name;
                        $generated_template .= '<div id="pm_mc3_cs_footer_actions_container" class="clearfix pm_mc3_cs_line">';
                    }
                    if ($this->isVisibleBlock($field_name)) {
                        $generated_template .= '
						<div id="pm_mc3_cs_order_now_container" class="clearfix pm_mc3_button_'.(!$shopping_button_added ? 'left' : 'right').'">
							<div id="pm_mc3_cs_'.$field_name.'" class="clearfix pm_mc3_button_'.(!$shopping_button_added ? 'left' : 'right').'_sub">
								<a href="{$order_page_link}" id="pm_mc3_cs_'.$field_name.'_btn" class="'.(isset($params['css3_background_flat']) && $params['css3_background_flat'] ? 'pm_mc3_actions_flat_button' : 'pm_mc3_actions_gradient_button').'">{$'.$field_name.'}</a>
							</div><!-- #pm_mc3_cs_'.$field_name.' -->
						</div><!-- #pm_mc3_cs_order_now_container -->';
                        $shopping_button_added = true;
                    }
                    if ($started_footer_actions_buttons !== false && $started_footer_actions_buttons != $field_name) {
                        $generated_template .= '</div><!-- #pm_mc3_cs_footer_actions_container" -->';
                    }
                    break;
                case 'hook_cross_selling_on_cart':
                    if (pm_modalcart3::isCrossSellingInstalled() && isset($this->templateParams['display_hook_cross_selling_on_cart']) && $this->templateParams['display_hook_cross_selling_on_cart']) {
                        $generated_template .= '
						{if isset($'.$field_name.') && !empty($'.$field_name.')}
							<div id="pm_mc3_cs_'.$field_name.'" class="pm_mc3_cs_line">
								{$'.$field_name.'}
								{literal}
								<script type="text/javascript">
									$(document).ready(function(){
										modalAjaxCart.overrideButtonsInThePage();
									});
								</script>
								{/literal}
							<div><!-- #pm_mc3_cs_'.$field_name.' -->
						{/if}
						';
                    }
                    break;
                default:
                    if ((isset($params['option_active']) && $params['option_active']) || !isset($params['option_active'])) {
                        $generated_template .= '{if isset($'.$field_name.') && !empty($'.$field_name.')}<div id="pm_mc3_cs_'.$field_name.'" class="pm_mc3_cs_line">{$'.$field_name.'}</div><!-- #pm_mc3_cs_'.$field_name.' -->{/if}';
                    }
                    break;
            }
        }
        $generated_template .= '
			</div><!-- #pm_mc3_cs_container -->
		</div><!-- #pm_mc3_content -->
		';
        $generated_template = str_replace('{pm_mc3_CartSummaryHeader}', $table_header_content, $generated_template);
        $generated_template = str_replace('{pm_mc3_CartSummaryFooter}', $table_footer_content, $generated_template);
        $generated_template = str_replace('{pm_mc3_CartSummaryCustomizedTemplate}', $customized_product_template, $generated_template);
        return $this->writeGeneratedTpl($generated_template);
    }
    public function getBackOfficeCSS()
    {
        return '
			ul#pm_mc3_cs_global_sort {text-align: center;}
			ul#pm_mc3_cs_global_sort li {background-color: white; border: 1px solid black;}
			#pm_mc3_cs_cart_summary {margin: 0 auto; width: 90%; border-spacing: 3px; border-collapse: separate;}
			#pm_mc3_cs_cart_summary tr td {border: 1px solid black;}
			#pm_mc3_cs_cart_summary tr#pm_mc3_cs_shipping_row td {text-align: right;}
			#pm_mc3_cs_cart_summary tr#pm_mc3_cs_subtotal_row td {text-align: right;}
			#pm_mc3_cs_cart_summary tr#pm_mc3_cs_total_row td {text-align: right;}
			#pm_mc3_cs_cart_summary tr#pm_mc3_cs_total_tax_row td {text-align: right;}
			#pm_mc3_cs_cart_summary tr#pm_mc3_cs_discounts_row td {text-align: right;}
			#pm_mc3_cs_product_line_sort li {float:left;}
			#pm_mc3_cs_footer_sort li {float:left; width:40%; margin-left: 5%;}
			#pm_mc3_cs_footer_sort:after{content:"";display:block;clear:both;}
		';
    }
    public function getBackOfficeTemplate()
    {
        $pm_mc3_cs_product_line_sort_content = array(
            'pm_mc3_cs_product_image' => '
				<td id="pm_mc3_cs_product_image" class="product_image'.(!$this->isVisibleBlock('product_image') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_image" class="pm_translate">{translate_product_image}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_image" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</td>
			',
            'pm_mc3_cs_product_name' => '
				<td id="pm_mc3_cs_product_name" class="product_name'.(!$this->isVisibleBlock('product_name') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_name" class="pm_translate">{translate_product_name}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_name" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</td>
			',
            'pm_mc3_cs_product_availability' => '
				<td id="pm_mc3_cs_product_availability" class="product_availability'.(!$this->isVisibleBlock('product_availability') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_availability" class="pm_translate">{translate_product_availability}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_availability" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</td>
			',
            'pm_mc3_cs_product_price' => '
				<td id="pm_mc3_cs_product_price" class="product_price'.(!$this->isVisibleBlock('product_price') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_price" class="pm_translate">{translate_product_price}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_price" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</td>
			',
            'pm_mc3_cs_product_tax' => '
				<td id="pm_mc3_cs_product_tax" class="product_tax'.(!$this->isVisibleBlock('product_tax') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_tax" class="pm_translate">{translate_product_tax}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_tax" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</td>
			',
            'pm_mc3_cs_product_quantity' => '
				<td id="pm_mc3_cs_product_quantity" class="product_quantity'.(!$this->isVisibleBlock('product_quantity') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_quantity" class="pm_translate">{translate_product_quantity}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_quantity" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</td>
			',
            'pm_mc3_cs_product_total' => '
				<td id="pm_mc3_cs_product_total" class="product_total'.(!$this->isVisibleBlock('product_total') ? ' pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_product_total" class="pm_translate">{translate_product_total}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=product_total" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</td>
			',
        );
        $pm_mc3_cs_footer_sort_content = array(
            'pm_mc3_cs_keep_shopping' => '
				<li id="pm_mc3_cs_keep_shopping" class="'.(!$this->isVisibleBlock('keep_shopping') ? 'pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_keep_shopping" class="pm_translate">{translate_keep_shopping}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=keep_shopping" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
            'pm_mc3_cs_order_now' => '
				<li id="pm_mc3_cs_order_now" class="'.(!$this->isVisibleBlock('order_now') ? 'pm_mc3_hidden_block' : '').'">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_order_now" class="pm_translate">{translate_order_now}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=order_now" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
        );
        $global_items = array(
            'pm_mc3_cs_free_content_1' => '
				<li id="pm_mc3_cs_free_content_1">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_free_content_1" class="pm_translate">{translate_free_content_1}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=free_content_1" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>',
            'pm_mc3_cs_title' => '
				<li id="pm_mc3_cs_title">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_title" class="pm_translate">{translate_title}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=title" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>',
            'pm_mc3_cs_body' => '
				<li id="pm_mc3_cs_body">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<a rel="860_500_1" href="{config_url}&amp;item=cart_summary" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
					<table id="pm_mc3_cs_cart_summary">
						<tr id="pm_mc3_cs_product_line_sort" class="ui-sortable">
							{pm_mc3_cs_product_line_sort_content}
						</tr>
						<tr id="pm_mc3_cs_subtotal_row">
							<td colspan="6" class="cart_subtotal_label" id="pm_mc3_cs_subtotal_label">
								<span id="translate_subtotal_label" class="pm_translate">{translate_subtotal_label}</span>
								<a rel="860_500_1" href="{config_url}&amp;item=subtotal_label" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
							</td>
							<td class="cart_subtotal" id="pm_mc3_cs_subtotal_value">
								<span id="translate_subtotal_value" class="pm_translate">{translate_subtotal_value}</span>
								<a rel="860_500_1" href="{config_url}&amp;item=subtotal_value" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
							</td>
						</tr>
						<tr id="pm_mc3_cs_shipping_row">
							<td colspan="6" class="cart_shipping_label" id="pm_mc3_cs_shipping_label">
								<span id="translate_shipping_label" class="pm_translate">{translate_shipping_label}</span>
								<a rel="860_500_1" href="{config_url}&amp;item=shipping_label"	class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
							</td>
							<td class="cart_total" id="pm_mc3_cs_shipping_value">
								<span id="translate_shipping_value" class="pm_translate">{translate_shipping_value}</span>
								<a rel="860_500_1" href="{config_url}&amp;item=shipping_value" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
							</td>
						</tr>
						<tr id="pm_mc3_cs_discounts_row">
							<td colspan="6" class="cart_discounts_label" id="pm_mc3_cs_discounts_label">
								<span id="translate_discounts_label" class="pm_translate">{translate_discounts_label}</span>
								<a rel="860_500_1" href="{config_url}&amp;item=discounts_label" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
							</td>
							<td class="cart_total" id="pm_mc3_cs_discounts_value">
								<span id="translate_discounts_value" class="pm_translate">{translate_discounts_value}</span>
								<a rel="860_500_1" href="{config_url}&amp;item=discounts_value" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
							</td>
						</tr>
						<tr id="pm_mc3_cs_total_tax_row">
							<td colspan="6" class="cart_total_tax_label" id="pm_mc3_cs_total_tax_label">
								<span id="translate_total_tax_label" class="pm_translate">{translate_total_tax_label}</span>
								<a rel="860_500_1" href="{config_url}&amp;item=total_tax_label" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
							</td>
							<td class="cart_total" id="pm_mc3_cs_total_tax_value">
								<span id="translate_total_tax_value" class="pm_translate">{translate_total_tax_value}</span>
								<a rel="860_500_1" href="{config_url}&amp;item=total_tax_value" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
							</td>
						</tr>
						<tr id="pm_mc3_cs_total_row">
							<td colspan="6" class="cart_total_label" id="pm_mc3_cs_total_label">
								<span id="translate_total_label" class="pm_translate">{translate_total_label}</span>
								<a rel="860_500_1" href="{config_url}&amp;item=total_label" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
							</td>
							<td class="cart_total" id="pm_mc3_cs_total_value">
								<span id="translate_total_value" class="pm_translate">{translate_total_value}</span>
								<a rel="860_500_1" href="{config_url}&amp;item=total_value" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
							</td>
						</tr>
					</table>
				</li>
			',
            'pm_mc3_cs_footer' => '
				<li id="pm_mc3_cs_footer">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<a rel="860_500_1" href="{config_url}&amp;item=footer_actions_container" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
					<ul id="pm_mc3_cs_footer_sort" class="ui-sortable">
						{pm_mc3_cs_footer_sort_content}
					</ul>
				</li>
			',
            'pm_mc3_cs_free_content_2' => '
				<li id="pm_mc3_cs_free_content_2">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_free_content_2" class="pm_translate">{translate_free_content_2}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=free_content_2" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
            'pm_mc3_cs_hook_cross_selling_on_cart' => (pm_modalcart3::isCrossSellingInstalled() ? '
				<li id="pm_mc3_cs_hook_cross_selling_on_cart">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_hook_cross_selling_on_cart" class="pm_translate">{translate_hook_cross_selling_on_cart}</span>
					<a rel="860_500_1" href="#ui-tabs-2" onclick="$jqPm(\'div#MC3_Panel>ul li:eq(1)>a\').trigger(\'click\'); return false;" class="ui-icon ui-icon-wrench pm_config_icon"></a>
				</li>
			' : ''),
            'pm_mc3_cs_free_shipping' => '
				<li id="pm_mc3_cs_free_shipping">
					<span class="ui-icon ui-icon-arrow-4 pm_icon"></span>
					<span id="translate_free_shipping" class="pm_translate">{translate_free_shipping}</span>
					<a rel="860_500_1" href="{config_url}&amp;item=free_shipping" class="ui-icon ui-icon-wrench pm_config_icon open_on_dialog_iframe"></a>
				</li>
			',
        );
        $template = '<ul id="pm_mc3_cs_global_sort" class="ui-sortable">';
        foreach ($this->templateParams['fields_order'] as $field_name) {
            if (isset($global_items[$field_name])) {
                if ($field_name == 'pm_mc3_cs_body') {
                    $pm_mc3_cs_product_line_sort = '';
                    foreach ($this->templateParams['fields_order'] as $field_name2) {
                        if (isset($pm_mc3_cs_product_line_sort_content[$field_name2])) {
                            $pm_mc3_cs_product_line_sort .= $pm_mc3_cs_product_line_sort_content[$field_name2];
                        }
                    }
                    $template .= str_replace('{pm_mc3_cs_product_line_sort_content}', $pm_mc3_cs_product_line_sort, $global_items[$field_name]);
                } elseif ($field_name == 'pm_mc3_cs_footer') {
                    $pm_mc3_cs_footer_sort = '';
                    foreach ($this->templateParams['fields_order'] as $field_name2) {
                        if (isset($pm_mc3_cs_footer_sort_content[$field_name2])) {
                            $pm_mc3_cs_footer_sort .= $pm_mc3_cs_footer_sort_content[$field_name2];
                        }
                    }
                    $template .= str_replace('{pm_mc3_cs_footer_sort_content}', $pm_mc3_cs_footer_sort, $global_items[$field_name]);
                } else {
                    $template .= $global_items[$field_name];
                }
            }
        }
        $template .= '</ul>';
        return $template;
    }
}
