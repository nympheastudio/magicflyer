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
require_once(dirname(__FILE__).'/../amazon.php');

require_once(dirname(__FILE__).'/../classes/amazon.order_info.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');

class AmazonProductOptionsJSON extends Amazon
{
    private $cr;

    private $auth;
    private $region;

    public function __construct()
    {
        parent::__construct();

        $this->cr = nl2br(Amazon::LF);

        AmazonContext::restore($this->context);

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    public function doIt()
    {
        $pass = false;

        if (!AmazonTools::checkToken(Tools::getValue('amazon_token'))) {
            die(Tools::displayError($this->l('Wrong Token')));
        }

        $callback = Tools::getValue('callback');

        if (empty($callback) || $callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $version = Tools::getValue('version');

        if (version_compare($version, '4', '>=')) {
            ob_start();
        }

        $langs = Tools::getValue('amazon_option_lang');

        if (!is_array($langs) && is_numeric($langs)) {
            $lang = (int)$langs;
            $langs = array($lang);
        }

        $id_product = (int)Tools::getValue('id_product');
        $id_product_attribute = null;
        $id_lang = (int)Tools::getValue('id_lang');
        $complex_id_product = Tools::getValue('complex_id_product');

        if (strpos($complex_id_product, '_') !== false) {
            $split_combination = explode('_', $complex_id_product);
            $id_product_attribute = (int)$split_combination[1];
        } elseif (is_numeric(trim($complex_id_product))) {
            $id_product_attribute = null;
        }

        if (empty($id_product) || !is_numeric($id_product)) {
            $action = null;
        } else {
            $product = new Product($id_product);

            if (Validate::isLoadedObject($product)) {
                $id_category = $product->id_category_default;
                $id_manufacturer = $product->id_manufacturer;
                $id_supplier = $product->id_supplier;
            }
            $action = Tools::getValue('action');
        }

        if ($amazon_action = Tools::getValue('amz-action-'.(int)$id_lang)) {
            $amazon_action = in_array($amazon_action, array(
                    Amazon::ADD,
                    Amazon::REMOVE,
                    Amazon::UPDATE
                )) ? $amazon_action : Amazon::UPDATE;

            if (!AmazonProduct::marketplaceActionSet($amazon_action, $id_product, null, null, $id_lang)) {
                $pass = false;
            }
        }

        switch (Tools::getValue('action')) {
            case 'delete-product-option':
                $json = Tools::jsonEncode(array(
                    'error' => !AmazonProduct::marketplaceOptionDelete($id_product, $id_product_attribute, $id_lang)
                ));

                die($callback.'('.$json.')');
                break;
            case 'get-v4': // Set Product Option
                $pass = true;
                $message = null;


                if ($pass) {
                    $result = AmazonProduct::getProductOptionsV4($id_product, $id_product_attribute, $id_lang);

                    if (!(is_array($result) && count($result) && array_key_exists('asin1', reset($result)))) {
                        $product_options = array();
                        $pass = false;
                    } else {
                        $product_options = reset($result1);
                    }
                }

                $json = Tools::jsonEncode(array('error' => !$pass, $message, 'product_options' => $product_options));

                die($callback.'('.$json.')');
                break;

            case 'set': // Set Product Opton
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $disable = (bool)Tools::getValue('amz-disable-'.(int)$id_lang) ? 1 : 0;
                    $force = (bool)Tools::getValue('amz-force-'.(int)$id_lang) ? 1 : 0;
                    $price = (float)str_replace(',', '.', Tools::getValue('amz-price-'.(int)$id_lang));
                    $text = Tools::getValue('amz-text-'.(int)$id_lang);
                    $nopexport = (bool)Tools::getValue('amz-nopexport-'.(int)$id_lang);
                    $noqexport = (bool)Tools::getValue('amz-noqexport-'.(int)$id_lang);
                    $fba = (bool)Tools::getValue('amz-fba-'.(int)$id_lang);
                    $fba_value = (float)str_replace(',', '.', Tools::getValue('amz-fbavalue-'.(int)$id_lang));
                    $latency = (int)Tools::getValue('amz-latency-'.(int)$id_lang);
                    $asin1 = Tools::getValue('amz-asin-'.(int)$id_lang);
                    $asin2 = Tools::getValue('amz-asin-2-'.(int)$id_lang);
                    $asin3 = Tools::getValue('amz-asin-3-'.(int)$id_lang);
                    $shipping = Tools::getValue('amz-shipping-'.(int)$id_lang);
                    $shipping_type = (int)Tools::getValue('amz-overridetype-'.(int)$id_lang);
                    $shipping_group = Tools::getValue('amz-shipping_group-'.(int)$id_lang);
                    $description = Tools::getValue('amz-description-'.(int)$id_lang);
                    $title = Tools::getValue('amz-title-'.(int)$id_lang);

                    $browsenode = Tools::getValue('amz-browsenode-'.(int)$id_lang);
                    $repricing_min = (float)str_replace(',', '.', Tools::getValue('amz-repricing_min-'.(int)$id_lang));
                    $repricing_max = (float)str_replace(',', '.', Tools::getValue('amz-repricing_max-'.(int)$id_lang));


                    if ($bullet_points = Tools::getValue('amz-bulletpoint-'.(int)$id_lang)) {
                        // compatibility

                        $bullet_point1 = isset($bullet_points[0]) && !empty($bullet_points[0]) ? Tools::substr($bullet_points[0], 0, Amazon::LENGTH_BULLET_POINT) : null;
                        $bullet_point2 = isset($bullet_points[1]) && !empty($bullet_points[1]) ? Tools::substr($bullet_points[1], 0, Amazon::LENGTH_BULLET_POINT) : null;
                        $bullet_point3 = isset($bullet_points[2]) && !empty($bullet_points[2]) ? Tools::substr($bullet_points[2], 0, Amazon::LENGTH_BULLET_POINT) : null;
                        $bullet_point4 = isset($bullet_points[3]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[3], 0, Amazon::LENGTH_BULLET_POINT) : null;
                        $bullet_point5 = isset($bullet_points[4]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[4], 0, Amazon::LENGTH_BULLET_POINT) : null;
                    } else {
                        foreach (array(
                                     'bullet_point1',
                                     'bullet_point2',
                                     'bullet_point3',
                                     'bullet_point4',
                                     'bullet_point5'
                                 ) as $bullet_point) {
                            ${$bullet_point} = Tools::substr(Tools::getValue($bullet_point), 0, Amazon::LENGTH_BULLET_POINT);
                        }
                    }

                    $gift_wrap = (bool)Tools::getValue('amz-giftwrap-'.(int)$id_lang);
                    $gift_message = (bool)Tools::getValue('amz-giftmessage-'.(int)$id_lang);

                    $shipping = str_replace(',', '.', $shipping);

                    if (is_numeric($shipping) && $shipping == 0) {
                        $shipping = (float)0;
                    } elseif (empty($shipping)) {
                        $shipping = null;
                    } else {
                        $shipping = (float)$shipping;
                    }

                    // Same action for all languages
                    $action = Tools::getValue('amz-action-'.(int)$id_lang);
                    $action = in_array($action, array(
                            Amazon::ADD,
                            Amazon::REMOVE,
                            Amazon::UPDATE
                        )) ? $action : Amazon::UPDATE;

                    $options = array(
                        'force' => $force,
                        'nopexport' => $nopexport,
                        'noqexport' => $noqexport,
                        'fba' => $fba,
                        'fba_value' => $fba_value,
                        'latency' => $latency,
                        'disable' => $disable,
                        'price' => $price,
                        'asin1' => $asin1,
                        'asin2' => $asin2,
                        'asin3' => $asin3,
                        'text' => $text,
                        'bullet_point1' => $bullet_point1,
                        'bullet_point2' => $bullet_point2,
                        'bullet_point3' => $bullet_point3,
                        'bullet_point4' => $bullet_point4,
                        'bullet_point5' => $bullet_point5,
                        'shipping' => $shipping,
                        'shipping_type' => $shipping_type,
                        'gift_wrap' => $gift_wrap,
                        'gift_message' => $gift_message,
                        'browsenode' => $browsenode,
                        'repricing_min' => $repricing_min,
                        'repricing_max' => $repricing_max,
                        'shipping_group' => $shipping_group,
                        'alternative_title' => $title,
                        'alternative_description' => $description,
                    );

                    if (!AmazonProduct::setProductOptions($id_product, $id_lang, $options, $id_product_attribute)) {
                        $pass = false;
                    }
                }
                break;

            case 'propagate-action-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                $action = null;
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    $action = Tools::getValue('amz-action-'.(int)$id_lang);
                    $action = in_array($action, array(
                            Amazon::ADD,
                            Amazon::REMOVE,
                            Amazon::UPDATE
                        )) ? $action : Amazon::UPDATE;

                    break; // once ok
                }
                if ($action) {
                    if (!AmazonProduct::propagateProductActionToCategory($id_product, $id_category, pSQL($action))) {
                        $pass = false;
                    }
                }
                break;

            case 'propagate-action-shop':
                $pass = true;
                $action = null;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $action = Tools::getValue('amz-action-'.(int)$id_lang);
                    $action = in_array($action, array(
                            Amazon::ADD,
                            Amazon::REMOVE,
                            Amazon::UPDATE
                        )) ? $action : Amazon::UPDATE;

                    break;
                }

                if ($action) {
                    if (!AmazonProduct::propagateProductActionToShop($id_product, pSQL($action))) {
                        $pass = false;
                    }
                }
                break;

            case 'propagate-action-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    echo $id_manufacturer;
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $action = Tools::getValue('amz-action-'.(int)$id_lang);
                        $action = in_array($action, array(
                                Amazon::ADD,
                                Amazon::REMOVE,
                                Amazon::UPDATE
                            )) ? $action : Amazon::UPDATE;

                        break;
                    }

                    if ($action) {
                        if (!AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, $action)) {
                            $pass = false;
                        }
                    }
                }
                break;

            case 'propagate-action-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $action = Tools::getValue('amz-action-'.(int)$id_lang);
                    $action = in_array($action, array(
                            Amazon::ADD,
                            Amazon::REMOVE,
                            Amazon::UPDATE
                        )) ? $action : Amazon::UPDATE;

                    break;
                }
                if ($action) {
                    if (!AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, $action)) {
                        $pass = false;
                    }
                }
                break;


            case 'propagate-text-cat':
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $text = Tools::getValue('amz-text-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'text', $text)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;
            case 'propagate-text-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $text = Tools::getValue('amz-text-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'text', $text)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-text-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $text = Tools::getValue('amz-text-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'text', $text)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            case 'propagate-text-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $text = Tools::getValue('amz-text-'.(int)$id_lang);

                    $options = array('text' => $text);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'text', pSQL($text))) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;


            case 'propagate-bulletpoint-cat':
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                    break;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        if ($bullet_points = Tools::getValue('amz-bulletpoint-'.(int)$id_lang)) {
                            // compatibility

                            $bullet_point1 = isset($bullet_points[0]) && !empty($bullet_points[0]) ? Tools::substr($bullet_points[0], 0, 500) : null;
                            $bullet_point2 = isset($bullet_points[1]) && !empty($bullet_points[1]) ? Tools::substr($bullet_points[1], 0, 500) : null;
                            $bullet_point3 = isset($bullet_points[2]) && !empty($bullet_points[2]) ? Tools::substr($bullet_points[2], 0, 500) : null;
                            $bullet_point4 = isset($bullet_points[3]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[3], 0, 500) : null;
                            $bullet_point5 = isset($bullet_points[4]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[4], 0, 500) : null;
                        } else {
                            foreach (array(
                                         'bullet_point1',
                                         'bullet_point2',
                                         'bullet_point3',
                                         'bullet_point4',
                                         'bullet_point5'
                                     ) as $bullet_point) {
                                ${$bullet_point} = Tools::substr(Tools::getValue($bullet_point), 0, Amazon::LENGTH_BULLET_POINT);
                            }
                        }


                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'bullet_point1', $bullet_point1)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'bullet_point2', $bullet_point2)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'bullet_point3', $bullet_point3)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'bullet_point4', $bullet_point4)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'bullet_point5', $bullet_point5)) {
                            $pass = false;
                        }

                        AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                    }
                }
                break;

            case 'propagate-bulletpoint-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    if ($bullet_points = Tools::getValue('amz-bulletpoint-'.(int)$id_lang)) {
                        // compatibility

                        $bullet_point1 = isset($bullet_points[0]) && !empty($bullet_points[0]) ? Tools::substr($bullet_points[0], 0, 500) : null;
                        $bullet_point2 = isset($bullet_points[1]) && !empty($bullet_points[1]) ? Tools::substr($bullet_points[1], 0, 500) : null;
                        $bullet_point3 = isset($bullet_points[2]) && !empty($bullet_points[2]) ? Tools::substr($bullet_points[2], 0, 500) : null;
                        $bullet_point4 = isset($bullet_points[3]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[3], 0, 500) : null;
                        $bullet_point5 = isset($bullet_points[4]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[4], 0, 500) : null;
                    } else {
                        foreach (array(
                                     'bullet_point1',
                                     'bullet_point2',
                                     'bullet_point3',
                                     'bullet_point4',
                                     'bullet_point5'
                                 ) as $bullet_point) {
                            ${$bullet_point} = Tools::substr(Tools::getValue($bullet_point), 0, Amazon::LENGTH_BULLET_POINT);
                        }
                    }
                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'bullet_point1', $bullet_point1)) {
                        $pass = false;
                    }

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'bullet_point2', $bullet_point2)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'bullet_point3', $bullet_point3)) {
                        $pass = false;
                    }

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'bullet_point4', $bullet_point4)) {
                        $pass = false;
                    }

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'bullet_point5', $bullet_point5)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-bulletpoint-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    if ($bullet_points = Tools::getValue('amz-bulletpoint-'.(int)$id_lang)) {
                        // compatibility

                        $bullet_point1 = isset($bullet_points[0]) && !empty($bullet_points[0]) ? Tools::substr($bullet_points[0], 0, 500) : null;
                        $bullet_point2 = isset($bullet_points[1]) && !empty($bullet_points[1]) ? Tools::substr($bullet_points[1], 0, 500) : null;
                        $bullet_point3 = isset($bullet_points[2]) && !empty($bullet_points[2]) ? Tools::substr($bullet_points[2], 0, 500) : null;
                        $bullet_point4 = isset($bullet_points[3]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[3], 0, 500) : null;
                        $bullet_point5 = isset($bullet_points[4]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[4], 0, 500) : null;
                    } else {
                        foreach (array(
                                     'bullet_point1',
                                     'bullet_point2',
                                     'bullet_point3',
                                     'bullet_point4',
                                     'bullet_point5'
                                 ) as $bullet_point) {
                            ${$bullet_point} = Tools::substr(Tools::getValue($bullet_point), 0, Amazon::LENGTH_BULLET_POINT);
                        }
                    }

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'bullet_point1', $bullet_point1)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'bullet_point2', $bullet_point2)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'bullet_point3', $bullet_point3)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'bullet_point4', $bullet_point4)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'bullet_point5', $bullet_point5)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            case 'propagate-bulletpoint-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    if ($bullet_points = Tools::getValue('amz-bulletpoint-'.(int)$id_lang)) {
                        // compatibility

                        $bullet_point1 = isset($bullet_points[0]) && !empty($bullet_points[0]) ? Tools::substr($bullet_points[0], 0, 500) : null;
                        $bullet_point2 = isset($bullet_points[1]) && !empty($bullet_points[1]) ? Tools::substr($bullet_points[1], 0, 500) : null;
                        $bullet_point3 = isset($bullet_points[2]) && !empty($bullet_points[2]) ? Tools::substr($bullet_points[2], 0, 500) : null;
                        $bullet_point4 = isset($bullet_points[3]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[3], 0, 500) : null;
                        $bullet_point5 = isset($bullet_points[4]) && !empty($bullet_points[3]) ? Tools::substr($bullet_points[4], 0, 500) : null;
                    } else {
                        foreach (array(
                                     'bullet_point1',
                                     'bullet_point2',
                                     'bullet_point3',
                                     'bullet_point4',
                                     'bullet_point5'
                                 ) as $bullet_point) {
                            ${$bullet_point} = Tools::substr(Tools::getValue($bullet_point), 0, Amazon::LENGTH_BULLET_POINT);
                        }
                    }

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'bullet_point1', $bullet_point1)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'bullet_point2', $bullet_point2)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'bullet_point3', $bullet_point3)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'bullet_point4', $bullet_point4)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'bullet_point5', $bullet_point5)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-fba-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fba = Tools::getValue('amz-fba-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'fba', $fba)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                break;

            case 'propagate-fba-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fba = Tools::getValue('amz-fba-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'fba', $fba)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-fba-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $fba = (int)Tools::getValue('amz-fba-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'fba', $fba)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }
                break;

            case 'propagate-fba-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fba = (int)Tools::getValue('amz-fba-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'fba', $fba)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            case 'propagate-fbavalue-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fba_value = Tools::getValue('amz-fbavalue-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'fba_value', $fba_value)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                break;

            case 'propagate-fbavalue-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fba_value = Tools::getValue('amz-fbavalue-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'fba_value', $fba_value)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-fbavalue-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $fbavalue = (int)Tools::getValue('amz-fbavalue-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'fba_value', $fbavalue)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-fbavalue-supplier':
                $pass = true;

                if (!$id_supplier) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $fbavalue = (int)Tools::getValue('amz-fbavalue-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'fba_value', $fbavalue)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                }
                break;

            case 'propagate-latency-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $latency = (int)Tools::getValue('amz-latency-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'latency', $latency)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                break;

            case 'propagate-latency-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $latency = (int)Tools::getValue('amz-latency-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'latency', $latency)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-latency-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $latency = (int)Tools::getValue('amz-latency-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'latency', $latency)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }

                break;

            case 'propagate-latency-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $latency = (int)Tools::getValue('amz-latency-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'latency', $latency)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            case 'propagate-browsenode-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $browsenode = Tools::getValue('amz-browsenode-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'browsenode', $browsenode)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                break;

            case 'propagate-browsenode-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $browsenode = Tools::getValue('amz-browsenode-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'browsenode', $browsenode)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-browsenode-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $browsenode = Tools::getValue('amz-browsenode-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'browsenode', $browsenode)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }

                break;

            case 'propagate-browsenode-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $browsenode = Tools::getValue('amz-browsenode-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'browsenode', $browsenode)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;


            case 'propagate-shipping-cat':
                $pass = true;

                if (!$id_category) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $shipping = Tools::getValue('amz-shipping-'.(int)$id_lang);
                    $shipping_type = (int)Tools::getValue('amz-overridetype-'.(int)$id_lang);

                    $shipping = str_replace(',', '.', $shipping);

                    if (is_numeric($shipping) && $shipping == 0) {
                        $shipping = (float)0;
                    } elseif (empty($shipping)) {
                        $shipping = null;
                    } else {
                        $shipping = (float)$shipping;
                    }

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, $shipping)) {
                        $pass = false;
                    }

                    if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'shipping_type', $shipping_type)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                break;

            case 'propagate-shipping-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $shipping = Tools::getValue('amz-shipping-'.(int)$id_lang);
                    $shipping_type = (int)Tools::getValue('amz-overridetype-'.(int)$id_lang);

                    $shipping = str_replace(',', '.', $shipping);

                    if (is_numeric($shipping) && $shipping == 0) {
                        $shipping = (float)0;
                    } elseif (empty($shipping)) {
                        $shipping = null;
                    } else {
                        $shipping = (float)$shipping;
                    }

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'shipping', $shipping)) {
                        $pass = false;
                    }

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'shipping_type', $shipping_type)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-shipping-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $shipping = Tools::getValue('amz-shipping-'.(int)$id_lang);
                        $shipping_type = (int)Tools::getValue('amz-overridetype-'.(int)$id_lang);

                        $shipping = str_replace(',', '.', $shipping);

                        if (is_numeric($shipping) && $shipping == 0) {
                            $shipping = (float)0;
                        } elseif (empty($shipping)) {
                            $shipping = null;
                        } else {
                            $shipping = (float)$shipping;
                        }

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'shipping', $shipping)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'shipping_type', $shipping_type)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }
                break;

            case 'propagate-shipping-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $shipping = Tools::getValue('amz-shipping-'.(int)$id_lang);
                        $shipping_type = (int)Tools::getValue('amz-overridetype-'.(int)$id_lang);

                        $shipping = str_replace(',', '.', $shipping);

                        if (is_numeric($shipping) && $shipping == 0) {
                            $shipping = (float)0;
                        } elseif (empty($shipping)) {
                            $shipping = null;
                        } else {
                            $shipping = (float)$shipping;
                        }

                        if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'shipping', $shipping)) {
                            $pass = false;
                        }

                        if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'shipping_type', $shipping_type)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                }
                break;

            case 'propagate-disable-cat':
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $disable = (int)Tools::getValue('amz-disable-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'disable', $disable)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;

            case 'propagate-disable-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $disable = (int)Tools::getValue('amz-disable-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'disable', $disable)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-disable-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $disable = (int)Tools::getValue('amz-disable-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'disable', $disable)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            // Force
            //
            case 'propagate-force-cat': // Propagate product option force
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $force = (int)Tools::getValue('amz-force-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'force', $force)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;

            case 'propagate-force-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $force = (int)Tools::getValue('amz-force-'.(int)$id_lang);

                    $options = array('force' => $force);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'force', $force)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-force-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $force = (int)Tools::getValue('amz-force-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'force', $force)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }
                break;

            case 'propagate-force-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $force = (int)Tools::getValue('amz-force-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'force', $force)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            case 'propagate-gift-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;

                    $gift_wrap = (int)Tools::getValue('amz-giftwrap-'.(int)$id_lang);
                    $gift_message = (int)Tools::getValue('amz-giftmessage-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'gift_wrap', $gift_wrap)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'gift_message', $gift_message)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-gift-cat':
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $gift_wrap = (int)Tools::getValue('amz-giftwrap-'.(int)$id_lang);
                        $gift_message = (int)Tools::getValue('amz-giftmessage-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'gift_wrap', $gift_wrap)) {
                            $pass = false;
                        }
                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'gift_message', $gift_message)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;

            case 'propagate-gift-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $gift_wrap = (int)Tools::getValue('amz-giftwrap-'.(int)$id_lang);
                    $gift_message = (int)Tools::getValue('amz-giftmessage-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'gift_wrap', $gift_wrap)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'gift_message', $gift_message)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                break;

            case 'propagate-gift-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $gift_wrap = (int)Tools::getValue('amz-giftwrap-'.(int)$id_lang);
                    $gift_message = (int)Tools::getValue('amz-giftmessage-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'gift_wrap', $gift_wrap)) {
                        $pass = false;
                    }
                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'gift_message', $gift_message)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;

            // shipping_group
            //
            case 'propagate-shipping_group-cat': // Propagate product option shipping_group
                $pass = true;

                if (!$id_category) {
                    printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
                    $pass = false;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $shipping_group = Tools::getValue('amz-shipping_group-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToCategory($id_product, $id_lang, $id_category, 'shipping_group', $shipping_group)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToCategory($id_product, $id_category, Amazon::UPDATE);
                }
                break;

            case 'propagate-shipping_group-shop':
                $pass = true;

                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $shipping_group = Tools::getValue('amz-shipping_group-'.(int)$id_lang);

                    $options = array('shipping_group' => $shipping_group);

                    if (!AmazonProduct::propagateProductOptionToShop($id_product, $id_lang, 'shipping_group', $shipping_group)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToShop($id_product, Amazon::UPDATE);
                break;

            case 'propagate-shipping_group-manufacturer':
                $pass = true;

                if (!$id_manufacturer) {
                    $pass = false;
                    break;
                } else {
                    foreach ($langs as $key => $val) {
                        $id_lang = (int)$val;
                        $shipping_group = Tools::getValue('amz-shipping_group-'.(int)$id_lang);

                        if (!AmazonProduct::propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, 'shipping_group', $shipping_group)) {
                            $pass = false;
                        }
                    }
                    AmazonProduct::propagateProductActionToManufacturer($id_product, $id_manufacturer, Amazon::UPDATE);
                }
                break;

            case 'propagate-shipping_group-supplier':
                $pass = true;

                if (!$id_supplier) {
                    $pass = false;
                    break;
                }
                foreach ($langs as $key => $val) {
                    $id_lang = (int)$val;
                    $shipping_group = Tools::getValue('amz-shipping_group-'.(int)$id_lang);

                    if (!AmazonProduct::propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, 'shipping_group', $shipping_group)) {
                        $pass = false;
                    }
                }
                AmazonProduct::propagateProductActionToSupplier($id_product, $id_supplier, Amazon::UPDATE);
                break;
            
            
            case 'ean2asin':
                $id_lang = Tools::getValue('id_lang');
                $pass = true;
                $errors = null;
                $output = null;
                $asin = '';

                ob_start();
                $this->initAmazon($id_lang);

                if (!($amazonApi = new AmazonWebService($this->auth, $this->region, false, Amazon::$debug_mode))) {
                    echo 'Unable to login';
                    die;
                }

                if ($lookup = Tools::getValue('ean13')) {
                    $result = $amazonApi->getASIN($lookup);

                    if (isset($result[0]) && !empty($result[0])) {
                        echo $this->l('ASIN successfully fetched').$this->cr;
                        $asin = $result[0];
                    }
                } elseif ($lookup = (int)Tools::getValue('upc')) {
                    $result = $amazonApi->getASIN($lookup);

                    if (isset($result[0]) && !empty($result[0])) {
                        echo $this->l('ASIN successfully fetched').$this->cr;
                        $asin = $result[0];
                    }
                }

                if (!$asin) {
                    echo sprintf('%s %s %s', $this->l('Unable to fetch ASIN for'), $lookup, $this->cr);
                    $pass = false;
                }
                $output = ob_get_clean();


                $json = Tools::jsonEncode(array('output' => $output, 'error' => !$pass, 'asin' => $asin));

                echo Tools::getValue('callback').'('.$json.')';
                die;
                break;

            case 'asin-probe':
                $pass = $this->asinProbe($id_lang);
                break;

            case 'update-field':
                $field = Tools::getValue('field');
                $value = Tools::getValue('value');
                $pass = false;

                switch ($field) {
                    case 'ean13':
                    case 'upc':
                        if (Tools::strlen($value) && !is_numeric($value)) {
                            die;
                        }
                    //TODO: DO NOT BREAK HERE

                    case 'reference':
                        $sql = null;

                        if ($id_product_attribute) {
                            $sql = 'UPDATE `'._DB_PREFIX_.'product_attribute` set `'.pSQL($field).'` = "'.pSQL($value).'" WHERE `id_product`='.(int)$id_product.' and `id_product_attribute` = '.(int)$id_product_attribute;
                        } elseif ($id_product) {
                            $sql = 'UPDATE `'._DB_PREFIX_.'product` set `'.pSQL($field).'` = "'.pSQL($value).'" WHERE `id_product`='.(int)$id_product;
                        }

                        if ($sql) {
                            if (Db::getInstance()->execute($sql)) {
                                $pass = true;
                            }
                        }
                        break;
                    case 'asin':
                        AmazonProduct::updateProductOptions($id_product, $id_lang, 'asin1', $value, $id_product_attribute);
                }
                break;


            default:
                $pass = false;
                break;
        }
        if (version_compare($version, '4', '<')) {
            if ($pass) {
                echo $this->l('Parameters successfully saved');
            } else {
                printf('#%d: %s', __LINE__, $this->l('Unable to save parameters...'));
            }
        }

        if (version_compare($version, '4', '>=')) {
            $output = ob_get_clean();

            $json = Tools::jsonEncode(array('error' => !$pass, 'output' => $output));

            die($callback.'('.$json.')');
        }
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }

    public function asinProbe($id_lang)
    {
        $pass = true;
        $callback = Tools::getValue('callback');
        $id_product = Tools::getValue('id_product');
        $data = Tools::getValue('data');

        if (!Tools::strlen($data)) {
            return (false);
        }

        $productDatas = Tools::jsonDecode($data);

        if (!$productDatas instanceof stdClass) {
            return (false);
        }

        $this->initAmazon($id_lang);

        $amazonApi = new AmazonWebService($this->auth, $this->region, false, Amazon::$debug_mode);

        $code_check = array();
        $code_check['ean'] = array();
        $code_check['upc'] = array();

        foreach ($productDatas as $complex_id_product => $productData) {
            if (empty($productData->ean13->code) && empty($productData->upc->code)) {
                continue;
            }

            if (Tools::strlen($productData->ean13->code) && !AmazonTools::eanUpcisPrivate($productData->ean13->code) && AmazonTools::eanUpcCheck($productData->ean13->code)) {
                $code_check['ean'][$complex_id_product] = sprintf('%013s', $productData->ean13->code);
            }

            if (Tools::strlen($productData->upc->code) && !AmazonTools::eanUpcisPrivate($productData->upc->code) && AmazonTools::eanUpcCheck($productData->upc->code)) {
                $code_check['upc'][$complex_id_product] = sprintf('%013s', $productData->upc->code);
            }
        }

        foreach (array('ean' => 'EAN', 'upc' => 'UPC') as $local_type => $amazon_type) {
            $remaining = $code_check[$local_type];

            while (is_array($remaining) && count($remaining)) {
                $slice = array_slice($remaining, 0, 5);
                $remaining = array_slice($remaining, 5);

                if (count($slice)) {
                    $xml = $amazonApi->getMatchingProductForId($slice, $amazon_type);

                    if ($xml instanceof SimpleXMLElement && isset($xml->GetMatchingProductForIdResult)
                        && is_array($xml->GetMatchingProductForIdResult) && count($xml->GetMatchingProductForIdResult)) {
                        $indexes = array_keys($slice);
                        $index = -1;
                        $type = $local_type == 'ean' ? 'ean13' : 'asin';

                        foreach ($xml->GetMatchingProductForIdResult as $getMatchingProductForId) {
                            $index++;

                            #if(! isset($indexes[$index]))
                            #	continue;

                            if (!$getMatchingProductForId instanceof SimpleXMLElement) {
                                continue;
                            }

                            $attributes = $getMatchingProductForId->attributes();

                            if (!property_exists($attributes, 'status')) {
                                continue;
                            }

                            /*
                            array (size=3)
                              'Id' => string '3700840902485' (length=13)
                              'IdType' => string 'EAN' (length=3)
                              'status' => string 'Success' (length=7)
                            */

                            if ((string)$attributes->status == 'Success') {
                                $identifiers = reset($getMatchingProductForId->Products);
                                $asinElement = reset($identifiers);
                                $ASIN = (string)$asinElement->MarketplaceASIN->ASIN;
                                $complex_id_product = $indexes[$index];

                                $productDatas->{$complex_id_product}->{$type}->asin = $ASIN;

                                // Save Data
                                if (strpos($complex_id_product, '_') !== false) {
                                    $split_combination = explode('_', $complex_id_product);
                                    $id_product_attribute = (int)$split_combination[1];
                                } elseif (is_numeric(trim($complex_id_product))) {
                                    $id_product_attribute = null;
                                }
                                $pass = $pass && AmazonProduct::updateProductOptions($id_product, $id_lang, 'asin1', $ASIN, $id_product_attribute);
                            } elseif (isset($getMatchingProductForId->Error->Message)) {
                                $productDatas->{$complex_id_product}->{$type}->error = (string)$getMatchingProductForId->Error->Message;
                            }
                        }
                    }
                }
            }
        }
        $json = Tools::jsonEncode(array('error' => !$pass, 'product_data' => $productDatas));

        die($callback.'('.$json.')');
    }

    public function initAmazon($id_lang)
    {
        // Amazon Europe overidding
        //
        $amazon_features = Amazon::getAmazonFeatures();
        $amazonEurope = $amazon_features['amazon_europe'];

        $marketPlaceMaster = AmazonConfiguration::get('MASTER');
        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        // Acc???s API Amazon
        //
        $merchantIds = AmazonConfiguration::get('MERCHANT_ID');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');
        $awsKeyIds = AmazonConfiguration::get('AWS_KEY_ID');
        $awsSecretKeys = AmazonConfiguration::get('SECRET_KEY');

        $amazonCurrency = AmazonConfiguration::get('CURRENCY');

        // Currencies
        //
        $currencies = AmazonConfiguration::get('CURRENCY');

        $this->platforms = array();

        if ((int)$amazonEurope) {
            foreach ($marketPlaceRegion as $language_id => $region) {
                // Identify the Master Marketplace
                //
                if ($marketPlaceMaster == $marketPlaceRegion[$language_id]) {
                    $mp_id_lang = $language_id;   // Default Platform - Language

                    $merchantId = trim($merchantIds[$language_id]);
                    $marketPlaceId = trim($marketPlaceIds[$language_id]);
                    $awsKeyId = trim($awsKeyIds[$language_id]);
                    $awsSecretKey = trim($awsSecretKeys[$language_id]);

                    $this->auth = array(
                        'MerchantID' => $merchantId,
                        'MarketplaceID' => $marketPlaceId,
                        'AWSAccessKeyID' => $awsKeyId,
                        'SecretKey' => $awsSecretKey
                    );
                }
                $this->params[$currencies[$language_id]] = array();
                $this->params[$currencies[$language_id]]['Currency'] = $amazonCurrency[$language_id];
                $this->params[$currencies[$language_id]]['Country'] = $marketPlaceMaster;

                $this->platforms[$currencies[$language_id]][] = $marketPlaceIds[$language_id];
            }

            if (!isset($mp_id_lang)) {
                die($this->l('Amazon Europe : missing Master Platform configuration'));
            }
        } else {
            $merchantId = trim($merchantIds[$id_lang]);
            $marketPlaceId = trim($marketPlaceIds[$id_lang]);
            $awsKeyId = trim($awsKeyIds[$id_lang]);
            $awsSecretKey = trim($awsSecretKeys[$id_lang]);

            $this->auth = array(
                'MerchantID' => $merchantId,
                'MarketplaceID' => $marketPlaceId,
                'AWSAccessKeyID' => $awsKeyId,
                'SecretKey' => $awsSecretKey
            );
            $this->platforms = array();
            $this->platforms[$currencies[$id_lang]][] = $marketPlaceIds[$id_lang];

            $this->params[$currencies[$id_lang]]['Currency'] = $amazonCurrency[$id_lang];
            $this->params[$currencies[$id_lang]]['Country'] = $marketPlaceRegion[$id_lang];
        }
        $this->region = $this->params[$currencies[$id_lang]];
    }
}

$apoJSON = new AmazonProductOptionsJSON();
$apoJSON->doIt();
