<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
*/

if (!class_exists('Amazon')) {
    // PS1.4 compat
    require_once(dirname(__FILE__).'/../amazon.php');
}

require_once(dirname(__FILE__).'/../classes/amazon.settings.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.tools.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.valid_values.class.php');
require_once(dirname(__FILE__).'/../validate/Tools.php');

class AmazonSpecificField extends Amazon
{
    const SUPPLIER_REFERENCE = 1;
    const REFERENCE = 2;
    const CATEGORY = 3;
    const MANUFACTURER = 4;
    const UNITY = 5;
    const META_TITLE = 6;
    const META_DESCRIPTION = 7;
    const WEIGHT = 8;

    public static $prestashop_fields = array(
        self::SUPPLIER_REFERENCE,
        self::REFERENCE,
        self::CATEGORY,
        self::MANUFACTURER,
        self::UNITY,
        self::META_TITLE,
        self::META_DESCRIPTION,
        self::WEIGHT
    );

    public static function displayFields($id_lang, $profile_name, &$extraFieldsArray, $target_attribute = null, $json_output = false)
    {
        static $parent_instance = null;
        static $context = null;
        static $regions = null;

        if (!Tools::strlen($profile_name)) {
            return (false);
        }

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $ps16x = true;
        } else {
            $ps16x = false;
        }


        $profile_key = AmazonTools::toKey($profile_name);


        if ($parent_instance === null) {
            $parent_instance = new Amazon();
        }
        if ($context === null) {
            $context = Context::getContext();
        }
        if ($regions === null) {
            $regions = AmazonConfiguration::get('REGION');
        }
        // Lookup for an optional attribute, we return JSON only
        if (Tools::strlen($target_attribute)) {
            $json_output = true;
        }

        $specific_field_variants_view = array();
        $attributes_field_view = array();
        $attributes_field_view['fields'] = array();
        $attributes_field_view['path'] = str_replace('\\', '/', _PS_MODULE_DIR_).'amazon/views/templates/admin';

        $header_view = array();
        $header_view['fields'] = array();
        $header_view['path'] =str_replace('\\', '/', _PS_MODULE_DIR_).'amazon/views/templates/admin';

        $variation_fields = array();
        $variation_selected_fields = array();
        $selected_fields = array();
        $field_exists = array();

        if (!isset($extraFieldsArray['field']) || !is_array($extraFieldsArray['field']) || !count($extraFieldsArray['field'])) {
            return (null);
        }


        if (is_array($regions) && isset($regions[$id_lang]) && Tools::strlen($regions[$id_lang])) {
            $region = $regions[$id_lang];
        } else {
            $region = null;
        }

        $languages = AmazonTools::languages();

        $iso_code = isset($languages[$id_lang]['iso_code']) ? $languages[$id_lang]['iso_code'] : null;

        // Load Features Once
        if (!is_array(self::$features) || !count(self::$features)) {
            parent::loadFeatures(true);
        }

        if (!is_array(self::$attributes) || !count(self::$attributes)) {
            parent::loadAttributes(true);
        }

        $html = '';
        $header = '';

        if (isset($extraFieldsArray['version']) && version_compare($extraFieldsArray['version'], '4.0', '>=')) {
            $version4 = true;
            $universe = $extraFieldsArray['universe'];
            $product_type = $extraFieldsArray['product_type'];
        } else {
            $universe = null;
            $product_type = null;
            $version4 = false;
        }

        if (!Tools::strlen($target_attribute) && array_key_exists('variation', $extraFieldsArray)
            && is_array($extraFieldsArray['variation']) && count($extraFieldsArray['variation'])) {
            $selected = null;
            $options = array();
            foreach ($extraFieldsArray['variation'] as $variationName => $variationTheme) {
                $variant_title = str_replace('-', ' - ', trim(preg_replace('/([A-Z])/', ' \1', $variationName)));

                $current_variant = isset($extraFieldsArray['variant']) && Tools::strlen($extraFieldsArray['variant']) ? $extraFieldsArray['variant'] : null;
                $selected = $current_variant && $current_variant == $variationName ? true : false;

                foreach ($variationTheme['fields'] as $variationField) {
                    if (Tools::strtoupper($variationField) == $variationField) {
                        $variationField = AmazonTools::ucfirst(Tools::strtolower($variationField));
                    } elseif (Tools::strtolower($variationField) == $variationField) {
                        $variationField = AmazonTools::ucfirst(Tools::strtolower($variationField));
                    }

                    $variation_fields[$variationField] = true;

                    if ($selected) {
                        $variation_selected_fields[$variationField] = true;
                    }

                    $header_view['fields']['variation'][$variationName.$variationField] = array(
                        'comment' => sprintf('%d: specific field/multiple/hidden input  - %s: %s', __LINE__, $profile_name, $variant_title),
                        'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][variation]['.$variationName.'][fields][]',
                        'value' => $variationField
                    );
                }
                $field_list = implode(',', $variationTheme['fields']);

                $options[] = array(
                    'value' => $variationName,
                    'name' => $variant_title,
                    'selected' => ($variationName == $selected ? true : false),
                    'rel' => $field_list
                );

                $header_view['fields']['variant'] = array(
                    'comment' => sprintf('%d: specific field/variant/variant select - %s : %s/%s', __LINE__, $profile_name, $variant_title, $variationField),
                    'class' => $selected ? 'variation-selected' : 'variation',
                    'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][variant]',
                    'options' => $options
                );
            }
        }

        $required = array();
        $choice = array();
        $recommended = array();
        $featured = array();
        $preferred = array();
        $variations = array();
        $others = array();
        $fields_settings = array();

        foreach ($extraFieldsArray['field'] as $field => $value) {
            if (in_array($field, array('ColorMap', 'SizeMap'))) {
                continue;
            }

            if (isset($variation_fields[$field])) {
                $variations[$field] = $value;
                continue;
            }

            // Call from xsd operation for an optional attribute, we need only 1 item
            if (Tools::strlen($target_attribute) && $field != $target_attribute) {
                continue;
            }

            if (isset($fields_settings[$field])) {
                continue;
            }

            $field_settings = AmazonSettings::getFieldSettting($region, $universe, $product_type, $field);

            if (is_array($field_settings) && (int)$field_settings['type']) {
                switch ($field_settings['type']) {
                    case AmazonSettings::RECOMMENDED:
                        $recommended[$field] = $value;
                        break;
                    case AmazonSettings::MANDATORY:
                        $required[$field] = $value;
                        break;
                }
            }
            $fields_settings[$field] = $field_settings;

            if (isset($extraFieldsArray['required'][$field]) && $extraFieldsArray['required'][$field]) {
                $required[$field] = $value;
                continue;
            }

            if (isset($extraFieldsArray['choice_allowed_values'][$field])) {
                $options = array();
                $choice_data = '';
                $choice_options = unserialize(AmazonTools::decode($extraFieldsArray['choice_allowed_values'][$field]));
                
                if (isset($choice_options) && is_array($choice_options)) {
                    // Choice Selected Value
                    $choice_selected_value = null;
                    $display_choice_option = false;
                    if (isset($extraFieldsArray['field'][$field])) {
                        if ($extraFieldsArray['field'][$field] == '^d') {
                            $choice_selected_value = isset($extraFieldsArray['default'][$field]) ? $extraFieldsArray['default'][$field] : null ;
                        } else {
                            $choice_selected_value = isset($extraFieldsArray['field'][$field]) && Tools::strlen($extraFieldsArray['field'][$field]) ? $extraFieldsArray['field'][$field] : null ;
                        }
                    }

                    foreach ($choice_options as $option) {
                        if (isset($choice_selected_value) && $option == $choice_selected_value) {
                            $display_choice_option = true;
                        }
                        $options[] = array(
                            'value'=>$option,
                            'name'=>$option,
                            'selected' => (isset($choice_selected_value) && $option == $choice_selected_value) ? 'selected' : false
                        );
                    }

                    $choice_data = array(
                        'comment' => sprintf('%d: specific field/choice/select - %s : %s "%s"', __LINE__, $profile_name, $field, $value),
                        'class' => 'profile-attribute',
                        'style' => ($display_choice_option) ? '' : 'display:none',
                        'rel' => 'default',
                        'title' => $field,
                        'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][default]['.$field.']',
                        'options' => $options,
                        'choice_allowed_values' => $choice_options,
                    );
                }
                
                $choice[$field] = $choice_data;
                continue;
            }

            if (isset(AmazonXSD::$recommendedPerUniverseFields[$universe]) && in_array($field, AmazonXSD::$recommendedPerUniverseFields[$universe])) {
                $recommended[$field] = $value;
                continue;
            }

            if (isset(AmazonXSD::$recommendedPerTypeFields[$product_type]) && in_array($field, AmazonXSD::$recommendedPerTypeFields[$product_type])) {
                $recommended[$field] = $value;
                continue;
            }

            if (isset($extraFieldsArray['has_valid_values'][$field])) {
                $featured[$field] = $value;
                continue;
            }

            $others[$field] = $value;
        }

        $fields = array_merge($variations, $required, $preferred, $recommended, $choice, $featured, $others);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(%d) - Fields'."\n", basename(__FILE__), __LINE__));
            CommonTools::p($fields);
        }

        foreach ($fields as $field => $value) {
            $field_key = AmazonTools::toKey($field);

            // Prevent duplicates
            if (isset($field_exists[$field_key])) {
                continue;
            }

            if (in_array($field, AmazonXSD::$excludedFields)) {
                continue;
            }

            $field_exists[$field_key] = true;

            $is_required = array_key_exists($field, $required);
            $is_choice = array_key_exists($field, $choice);
            $is_variation = array_key_exists($field, $variations);
            $is_featured = array_key_exists($field, $featured);
            $is_preferred = array_key_exists($field, $preferred);
            $is_recommended = array_key_exists($field, $recommended);

            $is_optionnal = !$is_recommended && !$is_featured && !$is_preferred && !$is_variation && !$is_required && !$is_choice;


            // The app requires only one attribute for optional attribute through xsd_operations.php
            if (!Tools::strlen($target_attribute) && ($is_optionnal && !$is_featured) && !Tools::strlen($value)) {
                continue;
            }

            if ($field == 'BatteryCellComposition') {
                //var_dump($fields_settings[$field], $field, $is_required, $is_featured, $is_recommended, $required);
                //die;
            }

            $title = trim(preg_replace('/([A-Z])/', ' \1', $field));
            $long_title = $title;

            if (in_array($field, array('Color', 'ColorName'))) {
                $title = $translation = $parent_instance->l('Color');
            } elseif (in_array($field, array('Size', 'SizeName'))) {
                $title = $translation = $parent_instance->l('Size');
            } elseif ($translation = AmazonSettings::getFieldTranslation($region, $universe, $field)) {
                $title = $translation;
            }

            if (array_key_exists($field, $fields_settings) && Tools::strlen($fields_settings[$field]['sample'])) {
                $sample = sprintf('(%s: %s)', $parent_instance->l('Eg'), $fields_settings[$field]['sample']);
            } else {
                $sample = null;
            }

            if (array_key_exists($field, $fields_settings) && Tools::strlen($fields_settings[$field]['description'])) {
                $tip = $fields_settings[$field]['description'];
            } else {
                $tip = null;
            }

            if (empty($translation) && !$tip && in_array($iso_code, array('en', 'gb', 'us', 'uk'))) {
                $tip2 = $parent_instance->l('Translation is not yet available for this label').nl2br("");
                $tip2 .= $parent_instance->l('However, you can find more explanation about this label in the template/flat file');
            } else {
                $tip2 = null;
            }


            if (AmazonTools::toKey($title) != AmazonTools::toKey($field)) {
                $long_title = sprintf('%s (%s)', $title, $field);
            }

            $field_value = $extraFieldsArray['field'][$field];

            $selected_attribute = self::getAttributeId($field_value);
            $selected_feature = self::getFeatureId($field_value);
            $selected_field = self::getFieldId($field_value);
            $is_fixed = self::isFixed($field_value);
            $is_default = self::isDefault($field_value);
            $is_allowed = self::isAllowedValue($field_value);

            if (!$selected_attribute && !$selected_feature && (int)$field_value && is_numeric($field_value)) {
                // retro-compat

                $selected_feature = (int)$field_value;
                $selected_attribute = null;
            }

            $specific_field_html = null;
            $attributes_field_html = null;

            $specific_field_view = array();
            $specific_field_view['type'] = 'multiple';
            $specific_field_view['required'] = $is_required;
            $specific_field_view['path'] = str_replace('\\', '/', _PS_MODULE_DIR_).'amazon/views/templates/admin';
            $specific_field_view['title'] = $is_variation ? $long_title : $title;
            $specific_field_view['rel'] = $is_variation ? $field : null;
            $specific_field_view['class'] = $is_variation ? 'variation' : null;
            $specific_field_view['variation'] = $is_variation;
            $specific_field_view['variation_selected'] = array_key_exists($field, $variation_selected_fields);
            $specific_field_view['sample'] = $sample;
            $specific_field_view['tip'] = $tip;
            $specific_field_view['tip2'] = $tip2;

            $specific_field_view['fields']['hidden'] = array(
                'comment' => sprintf('%d: specific field/multiple/hidden input  - %s: %s', __LINE__, $profile_name, $title),
                'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][multiple]['.$field.']',
                'value' => 1,
            ); // preserved for compatibility

            $options = array();

            if (is_array(self::$features) && array_key_exists($id_lang, self::$features)
                && is_array(self::$features[$id_lang]) && count(self::$features[$id_lang])) {
                $options[] = array('name' => $parent_instance->l('Features'), 'disabled' => true);

                foreach (self::$features[$id_lang] as $feature) {
                    if ($feature['id_feature'] == $selected_feature) {
                        $selected_fields[$field] = true;
                        $selected = true;
                    } else {
                        $selected = false;
                    }

                    $options[] = array(
                        'value' => 'f-'.$feature['id_feature'],
                        'name' => '&nbsp;&nbsp;'.$feature['name'],
                        'selected' => $selected
                    );
                }
            }

            if (is_array(self::$attributes_groups) && array_key_exists($id_lang, self::$attributes_groups)
                && is_array(self::$attributes_groups[$id_lang]) && count(self::$attributes_groups[$id_lang])) {
                $options[] = array('name' => $parent_instance->l('Attributes'), 'disabled' => true);

                foreach (self::$attributes_groups[$id_lang] as $attribute_group) {
                    $id_attribute_group = (int)$attribute_group['id_attribute_group'];

                    if ($selected = $selected_attribute == $id_attribute_group) {
                        $selected_fields[$field] = true;
                    }

                    $options[] = array(
                        'value' => 'a-'.$id_attribute_group,
                        'name' => '&nbsp;&nbsp;'.$attribute_group['name'],
                        'selected' => $selected
                    );
                }
            }

            $options[] = array(
                'name' => $parent_instance->l('Field (Expert Mode)'),
                'disabled' => true,
                'expert' => true
            );

            foreach (self::$prestashop_fields as $id_prestashop_field) {
                switch ($id_prestashop_field) {
                    case self::SUPPLIER_REFERENCE:
                        $name = $parent_instance->l('Supplier Reference');
                        break;
                    case self::REFERENCE:
                        $name = $parent_instance->l('Reference');
                        break;
                    case self::CATEGORY:
                        $name = $parent_instance->l('Category');
                        break;
                    case self::MANUFACTURER:
                        $name = $parent_instance->l('Manufacturer');
                        break;
                    case self::META_TITLE:
                        $name = $parent_instance->l('Meta Title');
                        break;
                    case self::META_DESCRIPTION:
                        $name = $parent_instance->l('Meta Description');
                        break;
                    case self::UNITY:
                        $name = $parent_instance->l('Unity');
                        break;
                    case self::WEIGHT:
                        $name = $parent_instance->l('Weight');
                        break;
                    default:
                        $name = null;
                        break;
                }
                if ($name == null) {
                    continue;
                }

                if ($selected = $selected_field == $id_prestashop_field) {
                    $selected_fields[$field] = true;
                }

                $options[] = array(
                    'value' => 'p-'.$id_prestashop_field,
                    'name' => '&nbsp;&nbsp;'.$name,
                    'expert' => true,
                    'selected' => $selected
                );
            }


            $default = isset($extraFieldsArray['default'][$field]) && Tools::strlen($extraFieldsArray['default'][$field]) ? (string)$extraFieldsArray['default'][$field] : null;

            if (isset($extraFieldsArray['allowed_values'][$field])) {
                $options[] = array(
                'value' => '^v',
                'name' => '['.$parent_instance->l('Allowed Value').']',
                'rel' => 'allowed',
                'style' => 'color:LightSkyBlue;',
                'selected' => $is_allowed
                );
            } elseif (!isset($extraFieldsArray['has_valid_values'][$field])) {
                if (Tools::strlen($default) && !$selected_attribute && !$selected_feature) {
                    $selected_fields[$field] = true;
                    $selected = true;
                } else {
                    $selected = false;
                }

                $options[] = array(
                    'value' => '^d',
                    'name' => '['.$parent_instance->l('Default Value').']',
                    'rel' => 'default',
                    'style' => 'color:LightSkyBlue;',
                    'selected' => $is_default
                );
            } else {
                $options[] = array(
                    'value' => '^x',
                    'name' => $parent_instance->l('Fixed Value').' (mapping)',
                    'rel' => 'fixed',
                    'style' => 'color:navy;',
                    'selected' => $is_fixed
                );
            }

            $specific_field_view['fields']['multiple'] = array(
                'comment' => sprintf('%d: specific field/multiple/select - %s : %s "%s"', __LINE__, $profile_name, $field, (!$is_choice) ? $value : ''),
                'class' => $is_choice ? 'choice-attribute profile-attribute' : 'profile-attribute',
                'rel' => $field,
                'title' => $field,
                'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][field]['.$field.']',
                'options' => $options
            );

            $specific_field_view['featured'] = $is_featured;
            $specific_field_view['preferred'] = $is_preferred;
            $specific_field_view['recommended'] = false;

            if (isset($extraFieldsArray['allowed_values'][$field])) {
                $allowedValues = unserialize(AmazonTools::decode($extraFieldsArray['allowed_values'][$field]));
                $currentAllowedValue = $extraFieldsArray['allowed_value'][$field];

                $select_options = array();

                if (is_array($allowedValues)) {
                    foreach ($allowedValues as $allowedValue) {
                        if ($currentAllowedValue === $allowedValue) {
                            $selected = true;
                        } else {
                            $selected = '';
                        }

                        $select_options[] = array('value' => $allowedValue, 'name' => $allowedValue, 'selected' => $selected);
                    }

                    $specific_field_view['fields']['encoded_valid_values'][] = array(
                        'comment' => sprintf('%d: specific field/allowed values/encoded value - %s : %s', __LINE__, $profile_name, $field),
                        'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][allowed_values]['.$field.']',
                        'value' => $extraFieldsArray['allowed_values'][$field],
                    );
                    $specific_field_view['fields']['encoded_valid_values'][] = array(
                        'comment' => sprintf('%d: specific field/allowed values/encoded value - %s : %s', __LINE__, $profile_name, $field),
                        'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][allowed_values_multiple]['.$field.']',
                        'value' => (isset($extraFieldsArray['allowed_values_multiple'][$field]) ? $extraFieldsArray['allowed_values_multiple'][$field] : false),
                    );

                    $specific_field_view['fields']['allowed'] = array(
                        'comment' => sprintf('%d: specific field/with valid value - %s: "%s"', __LINE__, $profile_name, $field, $allowedValue),
                        'title' => htmlentities($title, ENT_QUOTES),
                        'class' => 'extra-option',
                        'rel' => $field,
                        'display' => $is_allowed && $field_value,
                        'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][allowed_value]['.$field.']',
                        'options' => $select_options
                    );
                }
            } elseif ($is_choice) {
                $specific_field_view['choice'] = $is_choice;
               
                if (isset($value) && is_array($value)) {
                    $specific_field_view['choice_data'] = $value;
                } else {
                    $specific_field_view['fields']['default'] = array(
                        'comment' => sprintf('%d: specific field/multiple/input - %s : %s(%s) "%s"', __LINE__, $profile_name, $title, $field, $default),
                        'class' => 'extra-input',
                        'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][default]['.$field.']',
                        'value' => $default,
                        'style' => self::isDefault($field_value) ? null : 'display:none',
                        'rel' => 'default',
                        'placeholder' => $parent_instance->l('Default value')
                    );
                }
                
                $specific_field_view['fields']['choices'][] = array(
                    'comment' => sprintf('%d: specific field/anyfield/hidden input (choices) - %s: %s', __LINE__, $profile_name, $field),
                    'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][choices]['.$field.']',
                    'value' => 1,
                );

                if (!isset($specific_field_view['choices_required'])) {
                    $specific_field_view['choices_required'] = array(
                        'comment' => sprintf('%d: specific field/anyfield/hidden input (is choices required) - %s', __LINE__, $profile_name),
                        'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][choices_required]',
                        'value' => (isset($extraFieldsArray['choices_required']) && $extraFieldsArray['choices_required'] == 'true') ? 1 : 0,
                    );
                }

                if (isset($value['choice_allowed_values'])) {
                    $specific_field_view['fields']['choice_allowed_values'][] = array(
                        'comment' => sprintf('%d: specific field/anyfield/hidden input (choices value) - %s: %s', __LINE__, $profile_name, $field),
                        'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][choice_allowed_values]['.$field.']',
                        'value' =>  AmazonTools::encode(serialize($value['choice_allowed_values'])),
                    );
                }
            } elseif (!isset($extraFieldsArray['has_valid_values'][$field])) {
                $specific_field_view['recommended'] = $is_recommended;

                $specific_field_view['fields']['default'] = array(
                    'comment' => sprintf('%d: specific field/multiple/input - %s : %s(%s) "%s"', __LINE__, $profile_name, $title, $field, $default),
                    'class' => 'extra-input',
                    'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][default]['.$field.']',
                    'value' => $default,
                    'style' => self::isDefault($field_value) ? null : 'display:none',
                    'rel' => 'default',
                    'placeholder' => $parent_instance->l('Default value')
                );
            } elseif ($is_recommended) {
                $specific_field_view['fields']['default'] = null;
                $specific_field_view['recommended'] = true;
            } elseif ($is_featured) {
                $specific_field_view['fields']['default'] = null;
                $specific_field_view['featured'] = $is_featured;
            } elseif ($is_preferred) {
                $specific_field_view['fields']['default'] = null;
                $specific_field_view['preferred'] = $is_preferred;
            }

            if ($is_optionnal) {
                $specific_field_view['optionnal'] = $is_optionnal;
            }

            if ($specific_field_view['variation_selected']) {
                $specific_field_variants_view[] = $specific_field_view;
            }

            if (isset($extraFieldsArray['attributes'][$field])
                && is_array($extraFieldsArray['attributes'][$field]) && count($extraFieldsArray['attributes'][$field])) {
                $kind = null;

                // Selected Value
                $selected_value = 0;

                if (isset($extraFieldsArray['attributes'][$field]['unitOfMeasure'])) {
                    $selected_value = $extraFieldsArray['attributes'][$field]['unitOfMeasure'];
                    $kind = 'unitOfMeasure';
                }

                if (isset($extraFieldsArray['values'][$field]) && $kind) {
                    $specific_field_view['fields']['encoded_valid_values'][] = array(
                        'comment' => sprintf('%d: specific field/attributes/encoded value - %s : %s', __LINE__, $profile_name, $field),
                        'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][values]['.$field.']',
                        'value' => $extraFieldsArray['values'][$field]
                    );

                    $allowedValues = unserialize(AmazonTools::decode($extraFieldsArray['values'][$field]));

                    $options = array();

                    if (is_array($allowedValues)) {
                        foreach ($allowedValues as $allowedValue) {
                            if ($selected_value === $allowedValue) {
                                $selected = true;
                            } else {
                                $selected = '';
                            }

                            $options[] = array('value' => $allowedValue, 'name' => $allowedValue, 'selected' => $selected);
                        }

                        if (in_array($field, array_keys($selected_fields))) {
                            $display = true;
                        } else {
                            $display = false;
                        }

                        $attributes_field_view['fields']['attributes'][] = array(
                            'comment' => sprintf('%d: specific field/attributes(amazon) - %s: "%s"', __LINE__, $profile_name, $field, $allowedValue),
                            'title' => htmlentities($title, ENT_QUOTES),
                            'class' => 'extra-option',
                            'rel' => $field,
                            'display' => $display,
                            'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][attributes]['.$field.']['.$kind.']',
                            'options' => $options
                        );
                    }
                }
            }

            if ($is_required) {
                $specific_field_view['fields']['required'][] = array(
                    'comment' => sprintf('%d: specific field/anyfield/hidden input (required) - %s: %s', __LINE__, $profile_name, $field),
                    'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][required]['.$field.']',
                    'value' => 1,
                );
            }

            $context->smarty->assign('data', $specific_field_view);

            $html .= $specific_field_html = $context->smarty->fetch(_PS_MODULE_DIR_.'/amazon/views/templates/admin/configure/profiles/specific_field.inc.tpl');
        }
        $html .= $context->smarty->fetch(_PS_MODULE_DIR_.'/amazon/views/templates/admin/configure/profiles/specific_field_tail.inc.tpl');


        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(%d) - Specific Fields'."\n", basename(__FILE__), __LINE__));
            CommonTools::p($specific_field_view);
        }

        if (array_key_exists('has_valid_values', $extraFieldsArray)
            && is_array($extraFieldsArray['has_valid_values']) && count($extraFieldsArray['has_valid_values'])) {
            foreach (array_keys($extraFieldsArray['has_valid_values']) as $field_having_valid_values) {
                $header_view['fields']['has_valid_values'][] = array(
                    'comment' => sprintf('%d: specific field/has_valid_values/hidden input - %s: %s', __LINE__, $profile_name, $field_having_valid_values),
                    'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][has_valid_values]['.$field_having_valid_values.']',
                    'value' => 1,
                );
            }
        }

        if (array_key_exists('is_variation', $extraFieldsArray)
            && is_array($extraFieldsArray['is_variation']) && count($extraFieldsArray['is_variation'])) {
            foreach (array_keys($extraFieldsArray['is_variation']) as $field_having_variation) {
                $header_view['fields']['has_valid_values'][] = array(
                    'comment' => sprintf('%d: specific field/has_variations/hidden input - %s: %s', __LINE__, $profile_name, $field_having_variation),
                    'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][is_variation]['.$field_having_variation.']',
                    'value' => 1,
                );
            }
        }

        $header_view['universe'] = array(
            'comment' => sprintf('%d: specific field/universe - %s: %s', __LINE__, $profile_name, $universe),
            'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][universe]',
            'value' => $universe,
        );

        $header_view['product_type'] = array(
            'comment' => sprintf('%d: specific field/universe - %s: %s', __LINE__, $profile_name, $product_type),
            'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][product_type]',
            'value' => $product_type,
        );

        $header_view['version'] = array(
            'name' => 'profiles[extra]['.$profile_key.']['.$id_lang.'][version]',
            'value' => $version4 ? '4.0' : '3.0',
        );


        $message_class = array();
        $message_class['class_warning'] = 'warn '.($ps16x ? 'alert alert-warning' : '');
        $message_class['class_error'] = 'error '.($ps16x ? 'alert alert-danger' : '');
        $message_class['class_success'] = 'confirm '.($ps16x ? 'alert alert-success' : 'conf');
        $message_class['class_info'] = 'hint '.($ps16x ? 'alert alert-info' : 'conf');


        $context->smarty->assign('info_classes', $message_class);
        $context->smarty->assign('data', $header_view);
        $context->smarty->assign('variants', $specific_field_variants_view);
        $context->smarty->assign('version4', $version4);
        $context->smarty->assign('experimental', self::ENABLE_EXPERIMENTAL_FEATURES);

        $header .= $context->smarty->fetch(_PS_MODULE_DIR_.'/amazon/views/templates/admin/configure/profiles/profile_header.inc.tpl');

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(%d) - Attributes Fields'."\n", basename(__FILE__), __LINE__));
            CommonTools::p($attributes_field_view);
        }

        if (is_array($attributes_field_view) && count($attributes_field_view)) {
            $context->smarty->assign('data', $attributes_field_view);
            $context->smarty->assign('display_header', Tools::strlen($target_attribute) ? false : true);

            $html .= $attributes_field_html = $context->smarty->fetch(_PS_MODULE_DIR_.'/amazon/views/templates/admin/configure/profiles/amazon_attributes.inc.tpl');
        }


        if ($json_output) {
            $data = array(
            'specific_fields' => $specific_field_view,
            'specific_fields_html' => $specific_field_html,
            'attributes_fields' => $attributes_field_view,
            'attributes_fields_html' => $attributes_field_html,
            );
            return($data);
        }
        return ($header.$html);
    }

    public static function getFields($id_lang, $profile_name, &$extraFieldsArray, $json_output = false)
    {
        static $parent_instance = null;
        static $context = null;
        static $regions = null;

        if (!Tools::strlen($profile_name)) {
            return (false);
        }

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $ps16x = true;
        } else {
            $ps16x = false;
        }


        $profile_key = AmazonTools::toKey($profile_name);

        if ($parent_instance === null) {
            $parent_instance = new Amazon();
        }
        if ($context === null) {
            $context = Context::getContext();
        }
        if ($regions === null) {
            $regions = AmazonConfiguration::get('REGION');
        }

        if (!isset($extraFieldsArray['field']) || !is_array($extraFieldsArray['field']) || !count($extraFieldsArray['field'])) {
            return (null);
        }

        if (is_array($regions) && isset($regions[$id_lang]) && Tools::strlen($regions[$id_lang])) {
            $region = $regions[$id_lang];
        } else {
            $region = null;
        }

        $languages = AmazonTools::languages();

        $iso_code = isset($languages[$id_lang]['iso_code']) ? $languages[$id_lang]['iso_code'] : null;

        $universe = $extraFieldsArray['universe'];
        $product_type = $extraFieldsArray['product_type'];

        $choice = array();
        $featured = array();
        $preferred = array();
        $others = array();
        $fields_settings = array();

        foreach ($extraFieldsArray['field'] as $field => $value) {
            if (in_array($field, array('ColorMap', 'SizeMap', 'Color', 'Size'))) {
                continue;
            }
            $field_key = AmazonTools::toKey($field);
            $field_settings = AmazonSettings::getFieldSettting($region, $universe, $product_type, $field);

            if (is_array($field_settings) && (int)$field_settings['type']) {
                switch ($field_settings['type']) {
                    case AmazonSettings::RECOMMENDED:
                        continue;
                    case AmazonSettings::MANDATORY:
                        continue;
                }
            }
            $fields_settings[$field] = $field_settings;
            $others[$field] = $value;
        }

        $fields = array_merge($others);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(%d) - Fields'."\n", basename(__FILE__), __LINE__));
            CommonTools::p($fields);
        }

        foreach ($fields as $field => $value) {
            $field_key = AmazonTools::toKey($field);

            // Prevent duplicates
            if (isset($field_exists[$field_key])) {
                continue;
            }

            if (in_array($field, AmazonXSD::$excludedFields)) {
                continue;
            }

            $field_exists[$field_key] = true;


            $title = trim(preg_replace('/([A-Z])/', ' \1', $field));
            $long_title = $title;

            if ($translation = AmazonSettings::getFieldTranslation($region, $universe, $field)) {
                $title = $translation;
            }

            if (array_key_exists($field, $fields_settings) && Tools::strlen($fields_settings[$field]['sample'])) {
                $sample = sprintf('(%s: %s)', $parent_instance->l('Eg'), $fields_settings[$field]['sample']);
            } else {
                $sample = null;
            }

            if (array_key_exists($field, $fields_settings) && Tools::strlen($fields_settings[$field]['description'])) {
                $tip = $fields_settings[$field]['description'];
            } else {
                $tip = null;
            }

            if (empty($translation) && !$tip && in_array($iso_code, array('en', 'gb', 'us', 'uk'))) {
                $tip2 = $parent_instance->l('Translation is not yet available for this label').nl2br("");
                $tip2 .= $parent_instance->l('However, you can find more explanation about this label in the template/flat file');
            } else {
                $tip2 = null;
            }

            if (AmazonTools::toKey($title) != AmazonTools::toKey($field)) {
                $long_title = sprintf('%s (%s)', $title, $field);
            }
            $context->smarty->assign('field', htmlentities($field, ENT_QUOTES));
            $context->smarty->assign('title', htmlentities($title, ENT_QUOTES));
            $context->smarty->assign('long_title', htmlentities($long_title, ENT_QUOTES));
            $context->smarty->assign('sample', htmlentities($sample, ENT_QUOTES));
            $context->smarty->assign('tip', htmlentities($tip, ENT_QUOTES));
            $context->smarty->assign('tip2', htmlentities($tip2, ENT_QUOTES));
            $context->smarty->assign('images_url', AmazonTools::getShopUrl().'views/img');
            $html = $context->smarty->fetch(_PS_MODULE_DIR_.'/amazon/views/templates/admin/configure/helpers/optional_field_selector.inc.tpl');

            $fields[$field]['field'] = $field;
            $fields[$field]['title'] = $title;
            $fields[$field]['long_title'] = $long_title;
            $fields[$field]['sample'] = $sample;
            $fields[$field]['tip'] = $tip;
            $fields[$field]['tip2'] = $tip2;
            $fields[$field]['html'] = $html;

            if (Amazon::$debug_mode) {
                CommonTools::p($fields[$field]);
            }
        }
        $json = Tools::jsonEncode(array('specific_fields' => AmazonTools::fixEncoding($fields)));
        //$json = Tools::jsonEncode(array('specific_fields' => $fields));

        return($json);
    }

    public static function getMatchingEntries(&$profiles, $target_id_lang)
    {
        $matching_entries = array();

        $profiles = self::migrateProfilesFromV3($profiles);

        if (is_array($profiles) && count($profiles) && isset($profiles['name'])
            && is_array(array_filter($profiles['name'])) && count(array_filter($profiles['name']))) {
            foreach ($profiles['name'] as $profile_index => $profile_name) {
                if (!Tools::strlen($profile_name)) {
                    continue;
                }

                if (!Tools::strlen($profile_key = AmazonTools::toKey($profile_name))) {
                    continue;
                }

                // Filtering profiles having specifics fields only
                if (!array_key_exists('extra', $profiles) || !array_key_exists($profile_key, $profiles['extra']) || !is_array($profiles['extra'][$profile_key])) {
                    continue;
                }

                foreach (AmazonTools::languages() as $language) {
                    $id_lang = $language['id_lang'];

                    if ($id_lang != $target_id_lang) {
                        continue;
                    }

                    $specific_fields = &$profiles['extra'][$profile_key][$id_lang];
                    $universe = isset($profiles['universe'][$profile_index][$id_lang]) ? $profiles['universe'][$profile_index][$id_lang] : null;
                    $product_type = isset($profiles['product_type'][$profile_index][$id_lang]) ? $profiles['product_type'][$profile_index][$id_lang] : null;

                    $mapping_fields = is_array($specific_fields) && array_key_exists('field', $specific_fields)
                    && is_array($specific_fields['field']) && count($specific_fields['field']) ? array_keys($specific_fields['field']) : array();

                    // For those fields, checking if an attribute or a feature is selected, couple Field(with valid value)+Attribute or Feature = Mapping Entry
                    foreach ($mapping_fields as $mapping_field) {
                        if (is_array($specific_fields['field']) && array_key_exists($mapping_field, $specific_fields['field'])) {
                            $feature_or_attr_field = $specific_fields['field'][$mapping_field];

                            $selected_attribute = (int)self::getAttributeId($feature_or_attr_field);
                            $selected_feature = (int)self::getFeatureId($feature_or_attr_field);

                            if (!$selected_feature && !$selected_attribute && !self::isFixed($feature_or_attr_field)) {
                                continue;
                            }

                            $required = false;

                            if (array_key_exists('variation', $specific_fields) && array_key_exists('variant', $specific_fields)) {
                                $variation_theme = $specific_fields['variant'];
                                $variant_fields = array_key_exists($variation_theme, $specific_fields['variation']) && array_key_exists('fields', $specific_fields['variation'][$variation_theme]) ? $specific_fields['variation'][$variation_theme]['fields'] : array();
                                if (in_array($mapping_field, $variant_fields)) {
                                    $required = true;
                                }
                            }

                            $index = AmazonTools::toKey(sprintf('%s_%s_%s', $profile_key, $mapping_field, $specific_fields['field'][$mapping_field]));

                            $matching_entries[$index] = array(
                                'profile_name' => $profile_name,
                                'profile_key' => $profile_key,
                                'amazon_attribute' => $mapping_field,
                                'prestashop_type' => $selected_attribute ? 'attribute' : 'feature',
                                'prestashop_id' => $selected_attribute ? $selected_attribute : ($selected_feature ? $selected_feature : 0),
                                'fixed_value' => self::isFixed($feature_or_attr_field),
                                'universe' => $universe,
                                'product_type' => $product_type,
                                'is_color' => in_array(Tools::strtolower($mapping_field), array('color', 'colormap')),
                                'mandatory' => $required
                            );
                        }
                    }
                }
            }
        }

        return ($matching_entries);
    }

    public static function isAllowedValue($field_value)
    {
        if (preg_match('/^\^v$/', $field_value)) {
            return (true);
        }

        return (false);
    }
    public static function isDefault($field_value)
    {
        if (preg_match('/^\^d$/', $field_value)) {
            return (true);
        }

        return (false);
    }

    public static function isFixed($field_value)
    {
        if (preg_match('/^\^x$/', $field_value)) {
            return (true);
        }

        return (false);
    }

    public static function getFeatureId($field_value)
    {
        if (preg_match('/^[af]-[0-9]*/', $field_value)) {
            $field_attr = preg_split('/-/', $field_value);
            $type = $field_attr[0];
            $id = $field_attr[1];

            if ($type == 'f') {
                return ((int)$id);
            }
        }

        return (false);
    }

    public static function getFieldId($field_value)
    {
        if (preg_match('/^p-[0-9]*/', $field_value)) {
            $field_attr = preg_split('/-/', $field_value);
            $id = $field_attr[1];

            return ((int)$id);
        }

        return (false);
    }

    public static function getAttributeId($field_value)
    {
        static $attribute_id = array();

        if (isset($attribute_id[$field_value])) {
            return($attribute_id[$field_value]);
        }

        if (preg_match('/^[af]-[0-9]*/', $field_value)) {
            $field_attr = preg_split('/-/', $field_value);
            $type = $field_attr[0];
            $id = $field_attr[1];

            if ($type == 'a') {
                return ($attribute_id[$field_value] = (int)$id);
            }
        }

        return ($attribute_id[$field_value] = false);
    }


    public static function universes($lang)
    {
        static $amazon_universes_translated_cache = null;

        if (isset($amazon_universes_translated_cache[$lang])) {
            return($amazon_universes_translated_cache[$lang]);
        }
        // Known categories
        // https://catalog-mapper-fr.amazon.fr/catm/classifier/ProductClassifier.amzn
        // https://sellercentral.amazon.fr/gp/help/help-page.html/ref=pt_200956770_cont_scsearch?ie=UTF8&itemID=200956770
        //
        $amazon_universes = array();
        $amazon_universes['ClothingAccessories'] = 'Clothing Accessories';
        $amazon_universes['ProductClothing'] = 'Product Clothing';
        $amazon_universes['CameraPhoto'] = 'Camera Photo';
        $amazon_universes['Home'] = 'Home';
        $amazon_universes['Sports'] = 'Sports';
        $amazon_universes['SportsMemorabilia'] = 'Sports Memorabilia';
        $amazon_universes['EntertainmentCollectibles'] = 'Entertainment Collectibles';
        $amazon_universes['HomeImprovement'] = 'Home Improvement';
        $amazon_universes['FoodAndBeverages'] = 'Food And Beverages';
        $amazon_universes['Gourmet'] = 'Gourmet';
        $amazon_universes['Jewelry'] = 'Jewelry';
        $amazon_universes['Health'] = 'Health';
        $amazon_universes['CE'] = 'Consumers Electronics';
        $amazon_universes['Computers'] = 'Computers';
        $amazon_universes['SWVG'] = 'Software and Video Games';
        $amazon_universes['Wireless'] = 'Wireless';
        $amazon_universes['Beauty'] = 'Beauty';
        $amazon_universes['Office'] = 'Office';
        $amazon_universes['MusicalInstruments'] = 'Musical Instruments';
        $amazon_universes['AutoAccessory'] = 'Auto Accessory';
        $amazon_universes['PetSupplies'] = 'Pet Supplies';
        $amazon_universes['Toys'] = 'Toys';
        $amazon_universes['Baby'] = 'Baby';
        $amazon_universes['TiresAndWheels'] = 'Tires And Wheels';
        $amazon_universes['Music'] = 'Music';
        $amazon_universes['Video'] = 'Video';
        $amazon_universes['Lighting'] = 'Lighting';
        $amazon_universes['LargeAppliances'] = 'Large Appliances';
        $amazon_universes['Toys'] = 'Toys';
        $amazon_universes['GiftCards'] = 'Gift Cards';
        $amazon_universes['LabSupplies'] = 'Lab Supplies';
        $amazon_universes['RawMaterials'] = 'Raw Materials';
        $amazon_universes['PowerTransmission'] = 'Power Transmission';
        $amazon_universes['Industrial'] = 'Industrial';
        $amazon_universes['Shoes'] = 'Shoes';
        $amazon_universes['Motorcycles'] = 'Motorcycles';
        $amazon_universes['MechanicalFasteners'] = 'Mechanical Fasteners';
        $amazon_universes['FoodServiceAndJanSan'] = 'Food Service And Jan San';
        $amazon_universes['WineAndAlcohol'] = 'Wine And Alcohol';
        $amazon_universes['Books'] = 'Books';
        $amazon_universes['Luggage'] = 'Luggage';
        $amazon_universes['Arts'] = 'Arts';

        $translations = AmazonSettings::getUniversesTranslation($lang);

        $amazon_universes_translated = array();

        foreach ($amazon_universes as $universe => $universe_name) {
            $universe_key = AmazonTools::toKey($universe);

            if (is_array($translations) && isset($translations[$universe_key]) && Tools::strlen($translations[$universe_key])) {
                $amazon_universes_translated[$universe] = sprintf('%s (%s)', $translations[$universe_key], $universe_name);
            } else {
                $amazon_universes_translated[$universe] = $universe_name;
            }
        }

        asort($amazon_universes_translated);

        return ($amazon_universes_translated_cache[$lang] = $amazon_universes_translated);
    }

    public static function countrySelector($europe = false)
    {
        $images = __PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/amazon/views/img/';

        $actives = AmazonConfiguration::get('ACTIVE');
        $regions = AmazonConfiguration::get('REGION');
        $masterMarketplace = AmazonConfiguration::get('MASTER');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');
        $marketLang2Region  = null;
        
        if (is_array($regions)) {
            $marketLang2Region = array_flip($regions);
        }
        if ($europe && isset($marketLang2Region[$masterMarketplace]) && $marketLang2Region[$masterMarketplace]) {
            $master_id_lang = $marketLang2Region[$masterMarketplace];
        } else {
            $master_id_lang = null;
        }

        $marketplaces = array();

        if (is_array($actives)) {
            $default = true;

            foreach (AmazonTools::languages() as $language) {
                $id_lang = $language['id_lang'];

                if (!isset($actives[$id_lang]) || !$actives[$id_lang]) {
                    continue;
                }

                if (!isset($regions[$id_lang]) || empty($regions[$id_lang])) {
                    continue;
                }
                if (isset($marketPlaceIds[$id_lang]) && Tools::strlen($marketPlaceIds[$id_lang])) {
                    $marketPlaceId = $marketPlaceIds[$id_lang];
                } else {
                    $marketPlaceId = null;
                }

                if ($europe && AmazonTools::isEuropeMarketplaceId($marketPlaceId) && $id_lang != $master_id_lang) {
                    continue;
                } elseif ($europe && $id_lang == $master_id_lang) {
                    $flag = 'flag_eu_32px.png';
                    $name_short = 'Amazon Europe';
                    $name_long = 'Europe';
                } else {
                    $flag = 'flag_'.$regions[$id_lang].'_64px.png';
                    $name_short = preg_replace('/ .*/', '', $language['name']);
                    $name_long = $language['name'];
                }

                $marketplaces[$id_lang] = array();
                $marketplaces[$id_lang]['default'] = $default;
                $marketplaces[$id_lang]['name'] = sprintf('www.amazon.%s', AmazonTools::idToDomain($id_lang));
                $marketplaces[$id_lang]['region'] = $regions[$id_lang];
                $marketplaces[$id_lang]['id_lang'] = $id_lang;
                $marketplaces[$id_lang]['europe'] = $europe;
                $marketplaces[$id_lang]['iso_code'] = $language['iso_code'];
                $marketplaces[$id_lang]['active'] = $language['active'];
                $marketplaces[$id_lang]['image'] = $images.'geo_flags_web2/'.$flag;
                $marketplaces[$id_lang]['name_short'] = $name_short;
                $marketplaces[$id_lang]['name_long'] = $name_long;
                $default = false;
            }
        }

        return ($marketplaces);
    }

    public static function migrateProfilesFromV3(&$profiles)
    {
        // Migrate Profiles from V3 to V4
        if (is_array($profiles) && !isset($profiles['version4'])) {
            $languages = AmazonTools::languages();

            $original_profiles = $profiles;
            $profiles = array();

            if (isset($original_profiles['name']) && is_array($original_profiles)) {
                $keys = array(
                    'universe',
                    'product_type',
                    'latency',
                    'combinations',
                    'code_exemption',
                    'code_exemption_options',
                    'sku_as_supplier_reference',
                    'sku_as_sup_ref_unconditionnaly',
                    'item_type',
                    'price_rule',
                    'bullet_point_strategy',
                    'bullet_point_labels'
                );

                $profiles = array_fill_keys($keys, array());
                $profiles['extra'] = array();

                if (isset($original_profiles['name']) && is_array($original_profiles['name']) && count($original_profiles['name'])) {
                    foreach ($original_profiles['name'] as $id_profile => $name) {
                        $profiles['name'][$id_profile] = $name;

                        foreach ($languages as $language) {
                            $id_lang = $language['id_lang'];

                            foreach ($keys as $key) {
                                if (isset($original_profiles[$key])) {
                                    $profiles[$key][$id_profile][$id_lang] = isset($original_profiles[$key][$id_profile]) ? $original_profiles[$key][$id_profile] : null;
                                }
                            }

                            $p_browsenode = isset($original_profiles['browsenode_'.$id_lang][$id_profile]) ? $original_profiles['browsenode_'.$id_lang][$id_profile] : null;
                            $profiles['browsenode'][$id_profile][$id_lang] = $p_browsenode;

                            $profile_key = AmazonTools::toKey($name);

                            if (isset($original_profiles['extra']) && isset($original_profiles['extra'][$profile_key])
                                && is_array($original_profiles['extra'][$profile_key])
                                && count($original_profiles['extra'][$profile_key])) {
                                $p_extra = $original_profiles['extra'][$profile_key];
                            } elseif (isset($original_profiles['extra']) && isset($original_profiles['extra'][$name])
                                && is_array($original_profiles['extra'][$name])
                                && count($original_profiles['extra'][$name])) {
                                //retro-compat with 3.9 versions

                                $p_extra = $original_profiles['extra'][$name];
                            } else {
                                $p_extra = array();
                            }

                            $profiles['extra'][$profile_key][$id_lang] = $p_extra;
                        }
                    }
                }
            }
        }

        return ($profiles);
    }
}
