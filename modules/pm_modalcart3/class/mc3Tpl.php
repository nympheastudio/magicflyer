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

class mc3Tpl
{
    public $name;
    public $templateKey;
    public $fields = array();
    public $defaultFields = array();
    public $defaultParams = array();
    public $templateParams = array();
    public $templateFilename;
    private $css_mapping_rules = array(
        'font_color'            => 'color',
        'jgrowl_font_color'        => 'color',
        'font_size'                => 'font_size',
        'jgrowl_font_size'        => 'font_size',
        'font_style'            => 'style',
        'text_align'            => 'text_align',
        'vertical_align'        => 'vertical_align',
        'border'                => 'border',
        'content_border'        => 'border',
        'padding'                => 'padding',
        'margin'                => 'margin',
        'background_color'        => 'bg_gradient',
        'background_color_hover'=> 'bg_gradient',
        'option_active'            => 'display',
        'attributes_font_color' => 'color',
        'attributes_font_size'    => 'font_size',
        'attributes_font_style' => 'style',
        'width'                    => 'width',
        'z_index'                => 'z_index',
        'background_overlay_color'        => 'bg_gradient',
        'background_overlay_opacity'    => 'opacity',
        'modal_width'                    => 'width',
        'modal_background_color'        => 'bg_gradient',
        'modal_border_radius'            => 'border_radius',
        'border_radius'                    => 'border_radius',
        'box_shadow'                    => 'box_shadow',
        'text_shadow'                    => 'text_shadow',
        'attributes_text_shadow'        => 'text_shadow',
        'css3_background_color'            => 'css3button',
        'css3_background_color_hover'        => 'css3button',
        'css3_padding'                    => 'padding',
        'css3_font_color'                => 'color',
        'css3_font_color_hover'            => 'color',
        'css3_button_height'            => 'height',
        'css3_button_line_height'        => 'line_height',
    );
    public function __construct()
    {
        $this->defaultFields = $this->fields;
        $this->defaultParams = $this->templateParams;
        $this->templateFilename = str_replace('{id_shop}', (version_compare(_PS_VERSION_, '1.5.0.0', '<') ? 1 : Context::getContext()->shop->id), $this->templateFilename);
        if (version_compare(_PS_VERSION_, '1.5.0.0', '<') && isset($this->fields['product_image']['image_size']) && !empty($this->fields['product_image']['image_size'])) {
            $this->fields['product_image']['image_size'] = str_replace('_default', '', $this->fields['product_image']['image_size']);
        }
        $this->fields = $this->getTplFields();
        $this->templateParams = $this->getTplParams();
    }
    private function refreshParams()
    {
        $this->fields = $this->defaultFields;
        $this->templateParams = $this->defaultParams;
        if (version_compare(_PS_VERSION_, '1.5.0.0', '<') && isset($this->fields['product_image']['image_size']) && !empty($this->fields['product_image']['image_size'])) {
            $this->fields['product_image']['image_size'] = str_replace('_default', '', $this->fields['product_image']['image_size']);
        }
        $this->fields = $this->getTplFields();
        $this->templateParams = $this->getTplParams();
    }
    public function getTplFields($field = null)
    {
        $savedFields = $this->getSavedFields();
        if ($field == null) {
            foreach ($this->fields as $field_name => $params) {
                foreach ($params as $k => $v) {
                    if (isset($savedFields[$field_name][$k])) {
                        $this->fields[$field_name][$k] = $savedFields[$field_name][$k];
                    }
                }
            }
            return $this->fields;
        } elseif ($field != null && isset($this->fields[$field])) {
            foreach ($this->fields[$field] as $k => $v) {
                if (isset($savedFields[$field][$k])) {
                    $this->fields[$field][$k] = $savedFields[$field][$k];
                }
            }
            return $this->fields[$field];
        }
        return false;
    }
    public function getTplParams()
    {
        $savedParams = $this->getSavedTplParams();
        foreach ($this->templateParams as $k => $v) {
            if (isset($savedParams[$k])) {
                $this->templateParams[$k] = $savedParams[$k];
            }
        }
        if (isset($this->templateParams['display_hook_cross_selling_on_cart']) && !in_array('pm_mc3_'.$this->templateKey.'_hook_cross_selling_on_cart', $this->templateParams['fields_order'])) {
            $this->templateParams['fields_order'][] = 'pm_mc3_'.$this->templateKey.'_hook_cross_selling_on_cart';
        }
        return $this->templateParams;
    }
    public function getSavedTplParams()
    {
        $savedParams = Configuration::get('PM_'.pm_modalcart3::$_module_prefix.'_'.Tools::strtoupper($this->templateKey).'_CONF');
        if ($savedParams !== false) {
            return json_decode($savedParams, true);
        }
        return array();
    }
    public function setTplParams($newParams)
    {
        $newParamsToOverride = array();
        foreach ($this->defaultParams as $key => $val) {
            if (!isset($newParams[$key])) {
                continue;
            }
            if (is_array($this->defaultParams[$key]) && sizeof($this->array_diff_assoc_recursive($newParams[$key], $val))) {
                $newParamsToOverride[$key] = $newParams[$key];
            } elseif (!is_array($this->defaultParams[$key]) && $newParams[$key] != $val) {
                $newParamsToOverride[$key] = $newParams[$key];
            }
        }
        $savedParams = $this->getSavedTplParams();
        if (sizeof($savedParams)) {
            foreach ($savedParams as $key => $val) {
                if (!isset($newParamsToOverride[$key])) {
                    unset($savedParams[$key]);
                }
            }
        }
        if (sizeof($newParamsToOverride)) {
            foreach ($newParamsToOverride as $key => $val) {
                $savedParams[$key] = $val;
            }
        }
        if (sizeof($savedParams)) {
            foreach ($savedParams as $key => $val) {
                if (is_array($val) && !sizeof($val)) {
                    unset($savedParams[$key]);
                }
            }
        }
        Configuration::updateValue('PM_'.pm_modalcart3::$_module_prefix.'_'.Tools::strtoupper($this->templateKey).'_CONF', json_encode($savedParams), true);
        $this->refreshParams();
    }
    public function getSavedFields()
    {
        $savedFields = Configuration::get('PM_'.pm_modalcart3::$_module_prefix.'_'.Tools::strtoupper($this->templateKey).'_FIELDS');
        if ($savedFields !== false) {
            return json_decode($savedFields, true);
        }
        return array();
    }
    public function setTplFields($field, $newParams)
    {
        $newParamsToOverride = array();
        foreach ($this->defaultFields[$field] as $key => $val) {
            if (!isset($newParams[$key])) {
                continue;
            }
            if (is_array($this->defaultFields[$field][$key]) && (sizeof($this->array_diff_assoc_recursive($newParams[$key], $val)) || ($key == 'font_style' && !sizeof($newParams[$key]) && sizeof($val)))) {
                $newParamsToOverride[$field][$key] = $newParams[$key];
            } elseif (!is_array($this->defaultFields[$field][$key]) && $newParams[$key] != $val) {
                $newParamsToOverride[$field][$key] = $newParams[$key];
            }
        }
        $savedFields = $this->getSavedFields();
        if (isset($savedFields[$field]) && sizeof($savedFields[$field])) {
            foreach ($savedFields[$field] as $key => $val) {
                if (!isset($newParamsToOverride[$field][$key])) {
                    unset($savedFields[$field][$key]);
                }
            }
        }
        if (isset($newParamsToOverride[$field]) && is_array($newParamsToOverride[$field]) && sizeof($newParamsToOverride[$field])) {
            foreach ($newParamsToOverride[$field] as $key => $val) {
                $savedFields[$field][$key] = $val;
            }
        }
        if (sizeof($savedFields)) {
            foreach ($savedFields as $key => $val) {
                if (is_array($val) && !sizeof($val)) {
                    unset($savedFields[$key]);
                }
            }
        }
        if (version_compare(_PS_VERSION_, '1.6.0.11', '>')) {
            if (Configuration::get('PS_USE_HTMLPURIFIER')) {
                Configuration::updateValue('PS_USE_HTMLPURIFIER', false);
                Configuration::updateValue('PM_'.pm_modalcart3::$_module_prefix.'_'.Tools::strtoupper($this->templateKey).'_FIELDS', Tools::jsonEncode($savedFields), true);
                Configuration::updateValue('PS_USE_HTMLPURIFIER', true);
            } else {
                Configuration::updateValue('PM_'.pm_modalcart3::$_module_prefix.'_'.Tools::strtoupper($this->templateKey).'_FIELDS', Tools::jsonEncode($savedFields), true);
            }
        } else {
            Configuration::updateValue('PM_'.pm_modalcart3::$_module_prefix.'_'.Tools::strtoupper($this->templateKey).'_FIELDS', Tools::jsonEncode($savedFields), true);
        }
        $this->refreshParams();
    }
    public function getFullTplPath()
    {
        return $this->getTplPath().'/'.$this->getTplName();
    }
    public function getTplPath()
    {
        return dirname(__FILE__).'/../tpl';
    }
    public function getTplName()
    {
        return $this->templateFilename;
    }
    public function writeGeneratedTpl($tplContent)
    {
        $tplDestination = $this->getFullTplPath();
        if (!file_exists($tplDestination) || (file_exists($tplDestination) && md5(file_get_contents($tplDestination)) != md5($tplContent))) {
            pm_modalcart3::_clearCompiledTpl($tplDestination);
            return (file_put_contents($tplDestination, $tplContent) !== false);
        }
        return true;
    }
    public function isVisibleBlock($field)
    {
        if (isset($this->fields[$field]) && isset($this->fields[$field]['option_active']) && $this->fields[$field]['option_active']) {
            return true;
        }
        return false;
    }
    public function generateCSS()
    {
        $css_rules_array = array();
        $css_rules = '';
        $this->refreshParams();
        $params = $this->templateParams;
        $fields = $this->fields;
        if (isset($params['background_overlay']) && $params['background_overlay'] == 0) {
            if (isset($params['background_overlay_color'])) {
                unset($params['background_overlay_color']);
            }
            if (isset($params['background_overlay_opacity'])) {
                unset($params['background_overlay_opacity']);
            }
        }
        foreach ($fields as $field) {
            if (isset($field['css_rules']) && modalCart3CoreClass::_isFilledArray($field['css_rules'])) {
                foreach ($field['css_rules'] as $selector => $rules) {
                    if (in_array('css3_button_height', $rules) && isset($field['css3_button_height'])) {
                        $field['css3_button_line_height'] = $field['css3_button_height'];
                    }
                    foreach ($rules as $param_key) {
                        $value = '';
                        $css_mapping_rules_override = false;
                        if ($param_key == 'css3_background_flat') {
                            continue;
                        }
                        if ($param_key == 'css3_background_color' || $param_key == 'css3_background_color_hover') {
                            if (in_array('css3_background_flat', $rules) && isset($field['css3_background_flat']) && $field['css3_background_flat'] == true) {
                                if ($param_key == 'css3_background_color') {
                                    $field['background_color'] = array($field[$param_key], '');
                                    $param_key = 'background_color';
                                    $css_mapping_rules_override = 'css3flatbutton';
                                } elseif ($param_key == 'css3_background_color_hover') {
                                    $field['background_color_hover'] = array($field[$param_key], '');
                                    $param_key = 'background_color_hover';
                                    $css_mapping_rules_override = 'css3flatbutton';
                                }
                            }
                        }
                        if (preg_match('/hover/', $param_key)) {
                            if (isset($field[$param_key])) {
                                $value = $field[$param_key];
                            }
                            if (is_array($value)) {
                                $value = implode(' ', $value);
                            }
                            if (Tools::strlen($value) > 0) {
                                modalCart3CoreClass::_getCssRule($selector.':hover', ($css_mapping_rules_override !== false ? $css_mapping_rules_override : $this->css_mapping_rules[$param_key]), $value, true, false, $css_rules_array);
                            }
                        } else {
                            if (isset($field[$param_key])) {
                                $value = $field[$param_key];
                            }
                            if (is_array($value)) {
                                $value = implode(' ', $value);
                            }
                            if (Tools::strlen($value) > 0) {
                                modalCart3CoreClass::_getCssRule($selector, ($css_mapping_rules_override !== false ? $css_mapping_rules_override : $this->css_mapping_rules[$param_key]), $value, true, false, $css_rules_array);
                            }
                        }
                    }
                }
            }
        }
        if (isset($params['css_rules']) && modalCart3CoreClass::_isFilledArray($params['css_rules'])) {
            foreach ($params['css_rules'] as $selector => $rules) {
                foreach ($rules as $param_key) {
                    $value = '';
                    if (preg_match('/hover/', $param_key)) {
                        $value = $params[$param_key];
                        modalCart3CoreClass::_getCssRule($selector.':hover', $this->css_mapping_rules[$param_key], $value, true, false, $css_rules_array);
                    } else {
                        if (isset($params[$param_key])) {
                            $value = $params[$param_key];
                        }
                        if (is_array($value)) {
                            $value = implode(' ', $value);
                        }
                        if (Tools::strlen($value) > 0) {
                            modalCart3CoreClass::_getCssRule($selector, $this->css_mapping_rules[$param_key], $value, true, false, $css_rules_array);
                        }
                    }
                }
            }
        }
        if (modalCart3CoreClass::_isFilledArray($css_rules_array)) {
            foreach ($css_rules_array as $selector => $rules) {
                if (modalCart3CoreClass::_isFilledArray($rules)) {
                    $css_rules .= $selector.' {'.implode('', $rules).'}'."\n";
                }
                $dynamic_css_file = dirname(__FILE__). '/../' . str_replace('.css', '-'.(version_compare(_PS_VERSION_, '1.5.0.0', '<') ? 1 : Context::getContext()->shop->id).'.css', modalCart3CoreClass::DYNAMIC_CSS);
            }
        }
        $advanced_styles = (modalCart3CoreClass::_getAdvancedStylesDb() !== false ? "\n".trim(modalCart3CoreClass::_getAdvancedStylesDb()) : '');
        return (modalCart3CoreClass::_saveFileCleanSpace($dynamic_css_file, $css_rules . $advanced_styles) !== false);
    }
    protected function getFirstProductLineField($ignore_field = null)
    {
        $fields = array_keys($this->getOrderedFields());
        foreach ($fields as $field) {
            if ((($ignore_field != null && $field != $ignore_field) || $ignore_field == null) && in_array($field, $this->getProductLineFields())) {
                return $field;
            }
        }
        return null;
    }
    protected function getLastProductLineField($ignore_field = null)
    {
        $fields = array_reverse(array_keys($this->getOrderedFields()));
        foreach ($fields as $field) {
            if ((($ignore_field != null && $field != $ignore_field) || $ignore_field == null) && in_array($field, $this->getProductLineFields())) {
                return $field;
            }
        }
        return null;
    }
    protected function array_diff_assoc_recursive($a1, $a2)
    {
        $d = array();
        foreach ($a1 as $k => $v) {
            if (is_array($v)) {
                if (!array_key_exists($k, $a2) || !is_array($a2[$k])) {
                    $d[$k] = $v;
                } else {
                    $nd = $this->array_diff_assoc_recursive($v, $a2[$k]);
                    if (!empty($nd)) {
                        $d[$k] = $nd;
                    }
                }
            } elseif (!array_key_exists($k, $a2) || $a2[$k] !== $v) {
                $d[$k] = $v;
            }
        }
        return $d;
    }
}
