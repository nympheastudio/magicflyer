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

require_once(dirname(__FILE__).'/env.php');

require_once(dirname(__FILE__).'/../classes/amazon.tools.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.specificfield.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.valid_values.class.php');

require_once(dirname(__FILE__).'/../amazon.php');

require_once(dirname(__FILE__).'/../validate/Node.php');
require_once(dirname(__FILE__).'/../validate/XmlDataType.php');
require_once(dirname(__FILE__).'/../validate/XmlRestriction.php');
require_once(dirname(__FILE__).'/../validate/AmazonValidator.php');
require_once(dirname(__FILE__).'/../validate/AmazonXSD.php');
require_once(dirname(__FILE__).'/../validate/Tools.php');

class AmazonXSDOperations extends Amazon
{
    private $productType;
    private $universe;
    public $debug;

    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        if ((int)Tools::getValue('id_lang')) {
            $this->id_lang = (int)Tools::getValue('id_lang');
        }

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    public function dispatch()
    {
        $callback = Tools::getValue('callback');
        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        ob_start();
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s:%s - ', basename(__FILE__), __LINE__));
        }

        switch (Tools::getValue('action')) {
            case 'load':
                $this->loadSpecificsFields();
                break;
            case 'fetch':
                $this->fetchProductTypes();
                break;
            case 'extrafields':
                $this->extraFields();
                break;
            case 'optionals':
                $this->extraFields(true);
                break;
            case 'getkey':
                $this->getProfileKey();
                break;
            default:
                die($this->l('No action was choose'));
        }
        $console = ob_get_clean();

        $data = array('fields' => null, 'errors' => $this->errors, 'console' => $console);

        echo $callback.'('.Tools::jsonEncode($data).')';
        die;
    }

    public function loadSpecificsFields()
    {
        $callback = Tools::getValue('callback');
        $specific_fields = null;

        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $profile_key = Tools::getValue('profile_key');
        $id_lang = Tools::getValue('id_lang');
        $attribute = Tools::getValue('attribute');
        $json_output = Tools::strlen($attribute) ? true : false;

        $profiles = AmazonConfiguration::get('profiles');

        if (!Tools::strlen($attribute) && is_array($profiles) && count($profiles)) {
            $profiles = AmazonSpecificField::migrateProfilesFromV3($profiles);

            if (!isset($profiles['name'])) {
                $profiles['name'] = array();
            }

            foreach ($profiles['name'] as $profile_id => $profile_name) {
                if (!Tools::strlen($stored_profile_key = AmazonTools::toKey($profile_name)) && $profile_id != 65535) {
                    continue;
                }

                if ($stored_profile_key != $profile_key) {
                    continue;
                }

                $p_extra = isset($profiles['extra'][$profile_key][$id_lang]) ? $profiles['extra'][$profile_key][$id_lang] : null;

                if (is_array($p_extra) && count($p_extra)) {
                    $specific_fields = AmazonSpecificField::displayFields($id_lang, $profile_name, $p_extra, $attribute, $json_output);
                }
            }
        }
        $console = ob_get_clean();

        $data = array('error' => true, 'result' => $specific_fields, 'console' => $console);

        if ($json_output) {
            echo $callback.'('.Tools::jsonEncode($data).')';
        } else {
            echo $specific_fields;
        }
        die;
    }

    public function getProfileKey()
    {
        $callback = Tools::getValue('callback');

        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $profile_name = Tools::getValue('profile_name');

        $profile_key = AmazonTools::toKey($profile_name);

        $console = ob_get_clean();

        $data = array('error' => false, 'new_key' => $profile_key, 'console' => $console);

        echo $callback.'('.Tools::jsonEncode($data).')';
        die;
    }

    public function fetchProductTypes()
    {
        $this->errors[] = array();
        $data = array();
        $lang = Tools::getValue('lang');

        if (!($selected = Tools::getValue('selected'))) {
            $this->errors[] = sprintf('%s/%d: %s', basename(__FILE__), __LINE__, $this->l('Missing Parameter: selected'));

            return (false);
        }

        $file = $selected.'.xsd';

        if (!$file) {
            $this->errors[] = sprintf('%s/%d: %s', basename(__FILE__), __LINE__, $this->l('Unable to find the XSD file').': '.$selected);

            return (false);
        }

        //$exclude = array('Home');
        $exclude = array();

        $customElements = array(
            'ProductClothing' => array('ClothingType' => 'Clothing->ClassificationData->ClothingType')
        );

        //get ProductTypes

        $arr = array($selected);

        foreach ($arr as $key => $category) {
            $productTypes = array();

            try {
                $productFactory = new AmazonXSD($category.'.xsd');
                $productInstance = $productFactory->getInstance();

                if ($category == 'CE') {
                    $productType = 'ProductSubtype';
                } else {
                    $productType = 'ProductType';
                }

                $item = new stdClass();
                $item->productType = AmazonXSD::getVariationData($productInstance, $productType);

                if ($item->productType === false) {
                    if ($category == 'Shoes') {
                        $item->productType = AmazonXSD::getVariationData($productInstance, 'ClothingType');
                    }
                }

                if (isset($customElements[$category]) && $item) {
                    foreach ($customElements[$category] as $keyName => $el) {
                        $e = explode('->', $el);
                        $obj = $productInstance->ProductData;

                        foreach ($e as $tag) {
                            $obj = $obj->$tag;
                        }

                        $item->$keyName = $obj;
                    }
                }

                if (!$item) {
                    $item = AmazonXSD::getVariationData($productInstance, 'ProductType');
                }

                if (isset($productInstance->ProductData->Shoes->ClothingType->allowedValues)) {
                    foreach ($productInstance->ProductData->Shoes->ClothingType->allowedValues as $val) {
                        $productTypes[] = (string)$val;
                    }
                } elseif (isset($productInstance->ProductData->Clothing->ClassificationData->ClothingType->allowedValues)) {
                    foreach ($productInstance->ProductData->Clothing->ClassificationData->ClothingType->allowedValues as $val) {
                        $productTypes[] = (string)$val;
                    }
                } elseif (isset($item->productType->allowedValues)) {
                    // Added 2012-01-04 O.B.
                    foreach ($item->productType->allowedValues as $val) {
                        $productTypes[] = (string)$val;
                    }
                } elseif (isset($item->productType) && !empty($item->productType)) {
                    foreach ($item->productType as $key => $val) {
                        if (!preg_match('/^[A-Z]/', (string)$key)) {
                            continue;
                        }

                        $productTypes[] = (string)$key;
                    }
                } elseif (isset(AmazonXSD::$product_type_duplicated_exception[$selected])) {
                    $productTypes[] = $selected;
                } else {
                    // No Types
                    $productTypes = null;
                }
            } catch (Exception $e) {
                $this->errors[] = sprintf('XML Error:'.$e->getMessage());
            }
        }


        if (count($productTypes)) {
            echo html_entity_decode('&lt;option value=""&gt;').$this->l('Choose').html_entity_decode('&lt;/option&gt;');

            foreach ($productTypes as $productType) {
                if (in_array($productType, $exclude)) {
                    continue;
                }

                $translation = null;

                if (Tools::strlen($lang)) {
                    $translation = AmazonSettings::getProductTypeTranslation($lang, $selected, $productType);
                }

                if ($translation) {
                    echo html_entity_decode('&lt;option value="'.$productType.'" &gt;').sprintf('%s (%s)', $translation, $productType).html_entity_decode('&lt;/option&gt;').Amazon::LF;
                } else {
                    echo html_entity_decode('&lt;option value="'.$productType.'" &gt;').$productType.html_entity_decode('&lt;/option&gt;').Amazon::LF;
                }
            }
        } else {
            echo html_entity_decode('&lt;option value=""&gt;').$this->l('No Data').html_entity_decode('&lt;/option&gt;');
        }

        die;
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }

    /*
     * Amazon specific fields
     */

    public function extraFields($json_output = false)
    {
        $profile = urldecode(Tools::getValue('profile'));
        $universe = Tools::getValue('universe');
        $productType = Tools::getValue('selected', Tools::getValue('product_type'));
        $callback = Tools::getValue('callback');
        $attribute = Tools::getValue('attribute');
        $specificFields = array();
        $specificFields[$profile]['universe'] = $universe;
        $specificFields[$profile]['product_type'] = $productType;
        $specificFields[$profile]['version'] = '4.0';

        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        if (!Tools::strlen($universe) || !Tools::strlen($productType)) {
            die;
        }

        $productFactory = new AmazonXSD($universe.'.xsd');
        $p = $productFactory->getInstance();

        if ($universe == 'SWVG') {
            $universe = 'SoftwareVideoGames';
        }

        $this->universe = $universe;
        $this->productType = $productType;

        $required = AmazonXSD::getMandatoryFields($p->ProductData);
        $errors = ob_get_contents();

        if (Amazon::$debug_mode) {
            CommonTools::p('Parameters');
            CommonTools::p(sprintf('%s(%d)', basename(__FILE__), __LINE__));
            CommonTools::p(sprintf("Universe: %s", $universe));
            CommonTools::p(sprintf("Product Type: %s", $productType));

            CommonTools::p('Required Fields');
            CommonTools::p($required);
        }

        /*
         * Parse Variations/Variation Themes
         */
        $specificFields[$profile]['variation'] = array();

        if ($specificFields[$profile]['variation'] = AmazonXSDTools::parseVariationData($p, $universe, $productType)) {
            foreach ($specificFields[$profile]['variation'] as $variationThemeItem) {
                if (is_array($variationThemeItem) && count($variationThemeItem)) {
                    foreach ($variationThemeItem['fields'] as $variationField) {
                        $specificFields[$profile]['field'][$variationField] = null;

                        if (array_key_exists('attributes', $variationThemeItem) && array_key_exists($variationField, $variationThemeItem['attributes'])) {
                            $specificFields[$profile]['attributes'][$variationField] = array();
                            $specificFields[$profile]['attributes'][$variationField]['unitOfMeasure'] = true;
                            $specificFields[$profile]['values'][$variationField] = $variationThemeItem['attributes'][$variationField];
                        }
                    }
                }
            }
        }

        if (Amazon::$debug_mode) {
            CommonTools::p('Variation');
            CommonTools::p(sprintf('%s(%d)', basename(__FILE__), __LINE__));
            CommonTools::p($specificFields[$profile]['variation']);
        }


        if ($universe == 'Beauty') {
            // Optionnal Settings
            //
            $this->optionnalFields($specificFields[$profile], $p->ProductData->Beauty);

            $this->otherFields($specificFields[$profile], $p->ProductData->Beauty);
        } elseif ($universe == 'Luggage') {
            $specificFields[$profile]['field']['Department'] = null;
            $specificFields[$profile]['required']['Department'] = true;

            $specificFields[$profile]['field']['Weight'] = null;
            $specificFields[$profile]['required']['Weight'] = true;

            $specificFields[$profile]['field']['VolumeCapacityName'] = null;
            $specificFields[$profile]['required']['VolumeCapacityName'] = true;

            // Optionnal Settings
            //
            $this->optionnalFields($specificFields[$profile], $p->ProductData->Luggage);

            $this->otherFields($specificFields[$profile], $p->ProductData->Luggage);
        } elseif ($universe == 'ClothingAccessories') {
            // ClothingAccessories
            $specificFields[$profile]['field']['Department'] = null;
            $specificFields[$profile]['required']['Department'] = true;

            // Optionnal Settings
            //
            $this->optionnalFields($specificFields[$profile], $p->ProductData->ClothingAccessories->ClassificationData);

            $this->otherFields($specificFields[$profile], $p->ProductData->ClothingAccessories->ClassificationData);
        } elseif (isset($p->ProductData->Shoes->ClassificationData->Department)) {
            // Shoes
            $specificFields[$profile]['field']['Department'] = null;
            $specificFields[$profile]['required']['Department'] = true;

            // Optionnal Settings
            //
            $this->optionnalFields($specificFields[$profile], $p->ProductData->Shoes->ClassificationData);

            $this->otherFields($specificFields[$profile], $p->ProductData->Shoes->ClassificationData);
        } elseif (isset($p->ProductData->Clothing->ClassificationData)) {
            // Clothes
            $specificFields[$profile]['field']['Department'] = null;
            $specificFields[$profile]['required']['Department'] = true;
            $specificFields[$profile]['field']['CareInstructions'] = null;

            // Optionnal Settings
            //
            $this->optionnalFields($specificFields[$profile], $p->ProductData->Clothing->ClassificationData);

            $this->otherFields($specificFields[$profile], $p->ProductData->Clothing->ClassificationData);
        } elseif (isset($p->ProductData->Jewelry)) {
            // Jewelry
            $specificFields[$profile]['field']['DepartmentName'] = null;

            // Optionnal Settings
            //
            $this->optionnalFields($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType);

            // Optionnal Settings
            //
            $this->otherFields($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType);

            if (isset($p->ProductData->$universe->ProductType->$productType->Stone)) {
                // Extra-Optionnal Settings
                //
                $this->otherFields($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType->Stone);
            }
        } elseif (isset($p->ProductData->FoodAndBeverages)) {
            // Optionnal Settings
            //
            $this->optionnalFields($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType);

            // Optionnal Settings
            //
            $this->otherFields($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType->NutritionalFactsGroup);

            // Optionnal Settings
            //
            $this->otherFields($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType);
        } elseif (isset($p->ProductData->Music)) {
            // Optionnal Settings
            //
            $this->optionnalFields($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType);

            if (isset($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType->VinylRecordDetails)) {
                // Optionnal Settings
                //
                $this->otherFields($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType->VinylRecordDetails);
            }

            // Optionnal Settings
            //
            $this->otherFields($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType);
        } elseif (isset($p->ProductData->$universe->ProductType->$productType) && $p->ProductData->$universe->ProductType->$productType instanceof stdClass) {
            // most products

            $f = 0;

            foreach ($p->ProductData->$universe->ProductType->$productType as $name => $tag) {
                if (isset($specificFields[$profile]['field'][$name])) {
                    continue;
                }
                if (AmazonXSD::isMandatoryField($tag) && $tag instanceof stdClass && isset($tag->type)) {
                    if (in_array($tag->type, array('text', 'number', 'boolean', 'dateTime'))) {
                        $specificFields[$profile]['field'][$name] = null;
                        $specificFields[$profile]['required'][$name] = true;
                        $specificFields[$profile]['type'][$name] = $tag->type;

                        if (isset($tag->attr->unitOfMeasure) && isset($tag->attr->unitOfMeasure->allowedValues)
                            && is_array($tag->attr->unitOfMeasure->allowedValues)
                            && count($tag->attr->unitOfMeasure->allowedValues)) {
                            $specificFields[$profile]['attributes'][$name] = array();
                            $specificFields[$profile]['attributes'][$name]['unitOfMeasure'] = true;
                            $specificFields[$profile]['values'][$name] = AmazonTools::encode(serialize($tag->attr->unitOfMeasure->allowedValues));
                        }
                        $f++;
                    }
                }

                // choice
                if (isset($tag->choice) && $tag->choice) {
                    $specificFields[$profile]['choices_required'] = $tag->mandatory;
                    $specificFields[$profile]['choice_allowed_values'][$name] =  AmazonTools::encode(serialize($tag->allowedValues));
                }
            }

            // Optionnal Settings
            //
            $this->optionnalFields($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType);

            $this->otherFields($specificFields[$profile], $p->ProductData->$universe->ProductType->$productType);

            $this->otherFields($specificFields[$profile], $p->ProductData->$universe);

            // Some Cases (eg: Toys - Minimum Age)
            //
            foreach ($required as $requirements) {
                if (is_object($requirements)) {
                    foreach ($requirements as $key => $tag) {
                        if ($key == 'ProductType') {
                            continue;
                        }

                        if ($tag instanceof stdClass) {
                            if (AmazonXSD::isParentWithChildren($key)) {
                                $children = get_object_vars($requirements->$key);
                                foreach ($children as $keyName => $tagValue) {
                                    if ($tagValue instanceof stdClass) {
                                        $specificFields[$profile]['field'][$keyName] = null;
                                        $specificFields[$profile]['type'][$keyName] = isset($tag->type) ? $tag->type : null;
                                        $specificFields[$profile]['required'][$keyName] = true;

                                        if (isset($tagValue->attr->unitOfMeasure) && isset($tagValue->attr->unitOfMeasure->allowedValues)
                                            && is_array($tagValue->attr->unitOfMeasure->allowedValues)
                                            && count($tagValue->attr->unitOfMeasure->allowedValues)) {
                                            $specificFields[$profile]['attributes'][$keyName] = array();
                                            $specificFields[$profile]['attributes'][$keyName]['unitOfMeasure'] = 0;
                                            $specificFields[$profile]['values'][$keyName] = AmazonTools::encode(serialize($tagValue->attr->unitOfMeasure->allowedValues));
                                        }
                                    }
                                }
                            } else {
                                $specificFields[$profile]['field'][$key] = null;
                                $specificFields[$profile]['type'][$key] = isset($tag->type) ? $tag->type : null;
                                $specificFields[$profile]['required'][$key] = true;

                                if (isset($tag->attr->unitOfMeasure) && isset($tag->attr->unitOfMeasure->allowedValues)
                                    && is_array($tag->attr->unitOfMeasure->allowedValues)
                                    && count($tag->attr->unitOfMeasure->allowedValues)) {
                                    $specificFields[$profile]['attributes'][$key] = array();
                                    $specificFields[$profile]['attributes'][$key]['unitOfMeasure'] = 0;
                                    $specificFields[$profile]['values'][$key] = AmazonTools::encode(serialize($tag->attr->unitOfMeasure->allowedValues));
                                }
                            }
                        }
                    }
                }
            }
        } elseif (isset($p->ProductData->$universe->ProductType->allowedValues) && in_array($productType, $p->ProductData->$universe->ProductType->allowedValues)) {
            // Other Products

            foreach ($p->ProductData->$universe as $name => $tag) {
                if (isset($specificFields[$profile]['field'][$name])) {
                    continue;
                }
                if ($name == 'ProductType') {
                    continue;
                }

                if ($tag instanceof stdClass && isset($tag->type)) {
                    if (in_array($tag->type, array('text', 'number', 'boolean', 'dateTime'))) {
                        $specificFields[$profile]['field'][$name] = null;
                        $specificFields[$profile]['type'][$name] = $tag->type;

                        if (isset($tag->attr->unitOfMeasure) && isset($tag->attr->unitOfMeasure->allowedValues)
                            && is_array($tag->attr->unitOfMeasure->allowedValues)
                            && count($tag->attr->unitOfMeasure->allowedValues)) {
                            $specificFields[$profile]['attributes'][$name] = array();
                            $specificFields[$profile]['attributes'][$name]['unitOfMeasure'] = true;
                            $specificFields[$profile]['values'][$name] = AmazonTools::encode(serialize($tag->attr->unitOfMeasure->allowedValues));
                        }
                    }
                }
            }
        }

        // Additional fields
        $this->otherFields($specificFields[$profile], $p->ProductData, AmazonXSD::$descriptionDataOptionalFields);
        $this->otherFields($specificFields[$profile], $p->DescriptionData, AmazonXSD::$descriptionDataOptionalFields);

        if ($json_output && !Tools::strlen($attribute)) {
            $data = array('fields' => AmazonSpecificField::getFields($this->id_lang, $profile, $specificFields[$profile]), 'errors' => $errors);
        } elseif ($json_output && Tools::strlen($attribute)) {
            $data = AmazonSpecificField::displayFields($this->id_lang, $profile, $specificFields[$profile], $attribute);
        } else {
            echo AmazonSpecificField::displayFields($this->id_lang, $profile, $specificFields[$profile]);


            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(%d)', basename(__FILE__), __LINE__));
                CommonTools::p($specificFields);
            }

            $fields = ob_get_clean();
            $data = array('fields' => $fields, 'errors' => $errors);
        }

        echo $callback.'('.Tools::jsonEncode(AmazonTools::fixEncoding($data)).')';
        die;
    }

    /*
     * Fetch categories
     */

    public function optionnalFields(&$specificFields, $productDefinition = null)
    {

        if (is_object($productDefinition) && count($productDefinition)) {
            $universe = $this->universe == 'ClothingAccessories' ? 'ProductClothing' : $this->universe;
            $default_fields = AmazonValidValues::getAttributesForUniverse($universe);

            foreach ($default_fields as $key => $default_field) {
                if (!Tools::strlen($default_field)) {
                    continue;
                }

                if (!property_exists($productDefinition, $default_field)) {
                    unset($default_fields[$key]);
                    continue;
                } else {
                    $specificFields['has_valid_values'][$default_field] = true;
                }
            }
            $default_fields = array_values($default_fields);
        } else {
            $default_fields = AmazonValidValues::getAttributesForProductType($this->universe, $this->productType);
        }

        if (is_array($default_fields) && count($default_fields)) {
            foreach ($default_fields as $field) {
                if (!isset($productDefinition->$field)) {
                    continue;
                }

                $specificFields['field'][$field] = null;
                $specificFields['type'][$field] = isset($productDefinition->{$field}->type) ? $productDefinition->{$field}->type : null;
                $specificFields['attributes'][$field] = array();

                if (isset($productDefinition->$field->attr->unitOfMeasure) && isset($productDefinition->$field->attr->unitOfMeasure->allowedValues)
                    && is_array($productDefinition->$field->attr->unitOfMeasure->allowedValues)
                    && count($productDefinition->$field->attr->unitOfMeasure->allowedValues)) {
                    $specificFields['attributes'][$field]['unitOfMeasure'] = true;
                    /*
                     * Serialize possible values for the form
                     * MM, CM, etc...
                     */
                    $specificFields['values'][$field] = AmazonTools::encode(serialize($productDefinition->$field->attr->unitOfMeasure->allowedValues));
                }
            }
        }

        if (isset(AmazonXSD::$recommendedPerUniverseFields[$this->universe])) {
            foreach (AmazonXSD::$recommendedPerUniverseFields[$this->universe] as $field) {
                if (isset($specificFields['field'][$field])) {
                    continue;
                }
                if (!isset($productDefinition->$field)) {
                    continue;
                }

                $specificFields['field'][$field] = null;
                $specificFields['type'][$field] = isset($productDefinition->{$field}->type) ? $productDefinition->{$field}->type : null;
                $specificFields['attributes'][$field] = array();

                if (isset($productDefinition->$field->attr->unitOfMeasure) && isset($productDefinition->$field->attr->unitOfMeasure->allowedValues)
                    && is_array($productDefinition->$field->attr->unitOfMeasure->allowedValues)
                    && count($productDefinition->$field->attr->unitOfMeasure->allowedValues)) {
                    $specificFields['attributes'][$field]['unitOfMeasure'] = true;
                    /*
                     * Serialize possible values for the form
                     * MM, CM, etc...
                     */
                    $specificFields['values'][$field] = AmazonTools::encode(serialize($productDefinition->$field->attr->unitOfMeasure->allowedValues));
                }
            }
        }

        if (isset(AmazonXSD::$recommendedPerTypeFields[$this->productType])) {
            foreach (AmazonXSD::$recommendedPerTypeFields[$this->productType] as $field) {
                if (isset($specificFields['field'][$field])) {
                    continue;
                }
                if (!isset($productDefinition->$field)) {
                    continue;
                }

                $specificFields['field'][$field] = null;
                $specificFields['type'][$field] = isset($productDefinition->{$field}->type) ? $productDefinition->{$field}->type : null;
                $specificFields['attributes'][$field] = array();

                if (isset($productDefinition->$field->attr->unitOfMeasure) && isset($productDefinition->$field->attr->unitOfMeasure->allowedValues)
                    && is_array($productDefinition->$field->attr->unitOfMeasure->allowedValues)
                    && count($productDefinition->$field->attr->unitOfMeasure->allowedValues)) {
                    $specificFields['attributes'][$field]['unitOfMeasure'] = true;
                    /*
                     * Serialize possible values for the form
                     * MM, CM, etc...
                     */
                    $specificFields['values'][$field] = AmazonTools::encode(serialize($productDefinition->$field->attr->unitOfMeasure->allowedValues));
                }
            }
        }

        return (true);
    }
    public function search_get_combos($array = array())
    {
        // thanks https://stackoverflow.com/questions/16310553/php-find-all-somewhat-unique-combinations-of-an-array
        sort($array);
        $terms = array();

        for ($dec = 1; $dec < pow(2, count($array)); $dec++) {
            $curterm = array();
            foreach (str_split(strrev(decbin($dec))) as $i => $bit) {
                if ($bit) {
                    $curterm[] = $array[$i];
                }
            }
            if (!in_array($curterm, $terms)) {
                $terms[] = $curterm;
            }
        }
        /*$results = array();
        foreach($terms as $term) {
            $results[] = implode(',', $term);
        }
        return $results;*/
        return($terms);
    }
    public function combinations_set($array, $limit = null)
    {
        $results = array();
        $initial_content = $array;
        $i=0;
        $array_count = is_array($array) ? count($array) : 0;
        $last = null;
        for ($i=0; $i < $array_count * $array_count;$i++) {
            $results[$i] = null;
            foreach ($array as $key => $value2) {
                $results[$i] .= ','.$value2;
                $results[$i] = trim($results[$i], ',');
            }
            unset($array[$key]);
            if ($limit && count($results) >= $array_count + $limit) {
                break;
            }
        }
        foreach ($initial_content as $item) {
            $results[] = $item;
        }
        asort($results);

        return $results;
    }

    public function otherFields(&$specificFields, $productDefinition = null, $inclusion = null)
    {
        foreach ($productDefinition as $name => $tag) {
            if (isset($specificFields['field'][$name])) {
                continue;
            }
            if (is_array($inclusion) && !in_array($name, $inclusion)) {
                continue;
            }

            if ($tag instanceof stdClass && isset($tag->type)) {
                if (isset($tag->allowedValues) && is_array($tag->allowedValues) && count($tag->allowedValues)) {
                    $specificFields['allowed_value'][$name] = null;
                    $specificFields['allowed_values'][$name] = AmazonTools::encode(serialize($tag->allowedValues));
                    $specificFields['allowed_values_multiple'][$name] = false;

                    if ($tag->limit == null || $tag->limit > 1) {
                        $multiple = true;
                        $allowedValues = $this->combinations_set($tag->allowedValues, $tag->limit);
                    } else {
                        $multiple = false;
                        $allowedValues = $tag->allowedValues;
                    }
                    $specificFields['allowed_values'][$name] = AmazonTools::encode(serialize($allowedValues));
                    $specificFields['allowed_values_multiple'][$name] = $multiple;
                }

                if (in_array($tag->type, array('text', 'number', 'boolean', 'dateTime', 'StringNotNull'))) {
                    $specificFields['field'][$name] = null;
                    $specificFields['type'][$name] = $tag->type;

                    if (isset($tag->attr->unitOfMeasure) && isset($tag->attr->unitOfMeasure->allowedValues)
                        && is_array($tag->attr->unitOfMeasure->allowedValues)
                        && count($tag->attr->unitOfMeasure->allowedValues)) {
                        $specificFields['attributes'][$name] = array();
                        $specificFields['attributes'][$name]['unitOfMeasure'] = true;
                        $specificFields['values'][$name] = AmazonTools::encode(serialize($tag->attr->unitOfMeasure->allowedValues));
                    }
                }
            } elseif ($tag instanceof stdClass) {
                foreach (get_object_vars($tag) as $name => $element) {
                    $productDefinition = new StdClass;

                    if (isset($element->type) && isset($element->is_child) && $element->is_child) {
                        $productDefinition->$name = $element;
                    }
                    $this->otherFields($specificFields, $productDefinition, $inclusion);
                }
            }
        }
    }
}

$amazonXSDOperations = new AmazonXSDOperations();
$amazonXSDOperations->dispatch();
