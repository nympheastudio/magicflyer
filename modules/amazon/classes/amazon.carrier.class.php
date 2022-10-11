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

require_once(dirname(__FILE__).'/../classes/amazon.settings.class.php');

class AmazonCarrier
{
    const SHIPPING_STANDARD = 'standard';
    const SHIPPING_EXPRESS = 'express';
    const SHIPPING_CODES = 'codes';

    /* https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_1_9/amzn-base.xsd*/
    public static $carrier_codes = array(
        'USPS',
        'UPS',
        'UPSMI',
        'FedEx',
        'DHL',
        'Fastway',
        'GLS',
        'GO!',
        'Hermes Logistik Gruppe',
        'Royal Mail',
        'Parcelforce',
        'City Link',
        'TNT',
        'Target',
        'SagawaExpress',
        'NipponExpress',
        'YamatoTransport',
        'DHL Global Mail',
        'UPS Mail Innovations',
        'FedEx SmartPost',
        'OSM',
        'OnTrac',
        'Streamlite',
        'Newgistics',
        'Canada Post',
        'Blue Package',
        'Chronopost',
        'Deutsche Post',
        'DPD',
        'La Poste',
        'Parcelnet',
        'Poste Italiane',
        'SDA',
        'Smartmail',
        'FEDEX_JP',
        'JP_EXPRESS',
        'NITTSU',
        'SAGAWA',
        'YAMATO',
        'BlueDart',
        'AFL/Fedex',
        'Aramex',
        'India Post',
        'Professional',
        'DTDC',
        'Overnite Express',
        'First Flight',
        'Delhivery',
        'Lasership',
        'Yodel',
        'Other'
    );

    private static $additionnal_carriers_codes = array();

    private static $amazon_carriers = null;
    private static $carrier_template = array(
        'name' => '',
        'id_tax' => 1,
        'id_tax_rules_group' => 1,
        'url' => null,
        'active' => true,
        'deleted' => 0,
        'shipping_handling' => false,
        'range_behavior' => 0,
        'is_module' => false,
        'id_zone' => 1,
        'shipping_external' => true,
        'external_module_name' => 'amazon',
        'need_range' => true
    );

    public static function FBACarrier($carrierName)
    {
        $privateName = 'amazon_'.self::toPrivateName($carrierName);

        $sql = 'SELECT `id_carrier` FROM `'._DB_PREFIX_.'carrier` WHERE `external_module_name` = "'.pSQL($privateName).'"';

        $row = Db::getInstance()->getRow($sql);

        if (isset($row['id_carrier']) && (int)$row['id_carrier']) {
            return ($row['id_carrier']);
        }

        return (false);
    }

    public static function toPrivateName($name)
    {
        $text = html_entity_decode($name, ENT_NOQUOTES, 'UTF-8');
        $text = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');
        $text = preg_replace(array('/&szlig;/', '/&(..)lig;/', '/&([aoueAOUE])uml;/', '/&(.)[^;]*;/'), array(
                'ss',
                '$1',
                '$1'.'e',
                '$1'
            ), $text);
        $text = preg_replace('/[!<>?=+@{}_$%]*$/u', '', $text); // remove non printable
        $text = preg_replace('/\s+/', '_', $text);

        return (Tools::strtolower($text));
    }

    public static function FBACarrierCreate($carrierName, $state = false)
    {
        $privateName = 'amazon_'.self::toPrivateName($carrierName);

        $carrier = new Carrier();

        foreach (self::$carrier_template as $k => $v) {
            $carrier->{$k} = $v;
        }

        $carrier->name = self::toPublicName($carrierName);
        $carrier->active = (int)$state;
        $carrier->external_module_name = $privateName;

        foreach (Language::getLanguages(false) as $language) {
            $carrier->delay[$language['id_lang']] = $carrier->name.' via Amazon';
        }

        if (!$carrier->add()) {
            echo Tools::displayError('Unable to create carrier');

            return (false);
        }

        return ((int)$carrier->id);
    }

    public static function toPublicName($name)
    {
        return (html_entity_decode($name, ENT_NOQUOTES, 'UTF-8'));
    }

    /* Is a Carrier Code or Carrier Name ?*/

    public static function isCarrierCode($CarrierCode)
    {
        return (in_array(trim($CarrierCode), array_merge(self::$carrier_codes, self::$additionnal_carriers_codes)));
    }

    public static function getAmazonCarrierById($id_carrier, $id_lang, $debug = false)
    {
        $key = null;

        if (!self::$amazon_carriers) {
            // Default Carrier selected in the module configuration
            //
            self::$amazon_carriers = AmazonConfiguration::get('CARRIER_DEFAULT');
        }

        if (!is_array(self::$amazon_carriers) || !count(self::$amazon_carriers)) {
            if ($debug) {
                CommonTools::p(sprintf('%s:%d %s', basename(__FILE__), __LINE__, 'Empty carrier list'));
            }

            return (false);
        }

        $amazon_carrier = isset(self::$amazon_carriers[$id_lang]) ? self::$amazon_carriers[$id_lang] : false;

        if (!is_array($amazon_carrier) || !isset($amazon_carrier['prestashop'])
            || !is_array($amazon_carrier['prestashop']) || !count($amazon_carrier['prestashop'])) {
            if ($debug) {
                CommonTools::p(sprintf('%s:%d %s', basename(__FILE__), __LINE__, 'Empty carrier list'));
            }

            return (false);
        }

        $pass = false;
        foreach ($amazon_carrier['prestashop'] as $key => $amazon_id_carrier) {
            $carrierIdList = self::carrierIdList($id_carrier);

            if (in_array($amazon_id_carrier, $carrierIdList)) {
                $pass = true;
                break;
            }
        }
        if (!$pass) {
            if ($debug) {
                CommonTools::p(sprintf('%s:%d %s id_carrier: %d / id_lang: %d', basename(__FILE__), __LINE__, 'No matched entry for', $id_carrier, $id_lang));
            }

            return (false);
        } elseif ($debug) {
            CommonTools::p(sprintf('%s:%d id_carrier: %d - matched in %s', basename(__FILE__), __LINE__, $id_carrier, print_r($carrierIdList, true)));
        }

        return (is_numeric($key) ? $amazon_carrier['amazon'][$key] : false);
    }

    /*
     * Work arround to fetch carrier and deleted carriers since PS 1.5
     */
    public static function carrierIdList($id_carrier)
    {
        $carrier_id_list = array();

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $sql = 'SELECT DISTINCT c2.`id_carrier` FROM `'._DB_PREFIX_.'carrier` c LEFT JOIN `'._DB_PREFIX_.'carrier` c2 on (c.`id_reference` = c2.`id_reference`)
                      WHERE c.id_carrier = '.(int)$id_carrier;

            $rows = Db::getInstance()->executeS($sql);

            if (is_array($rows) && count($rows)) {
                $carrier_id_list = array();
                foreach ($rows as $item) {
                    if (isset($item['id_carrier'])) {
                        $carrier_id_list[] = $item['id_carrier'];
                    }
                }
                if (!in_array($id_carrier, $carrier_id_list)) {
                    $carrier_id_list[] = $id_carrier;
                }
            } else {
                $carrier_id_list[] = $id_carrier;
            }
        } else {
            $carrier_id_list[] = $id_carrier;
        }

        return ($carrier_id_list);
    }

    public static function updateTrackingNumber($id_order, $id_carrier, $trackingNumber, $debug = false)
    {
        $order = new Order((int)$id_order);

        if (!Validate::isLoadedObject($order)) {
            if ($debug) {
                CommonTools::p(sprintf('%s:%d %s id_order: %d', basename(__FILE__), __LINE__, 'Unable to load Order', $id_order));
            }

            return (false);
        }
        if (!$trackingNumber) {
            if ($debug) {
                CommonTools::p(sprintf('%s:%d %s id_order: %d', basename(__FILE__), __LINE__, 'Empty tracking number', $id_order));
            }

            return (false);
        }

        // New fashioned
        //
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            /*
            // Adding an entry in order_carrier table
            if ($order->id_carrier != $id_carrier) {
                $order_carrier = new OrderCarrier();
                $order_carrier->id_order = (int)$order->id;
                $order_carrier->id_carrier = (int)$id_carrier;
                $order_carrier->weight = (float)$order->getTotalWeight();
                $order_carrier->shipping_cost_tax_excl = 0;
                $order_carrier->shipping_cost_tax_incl = 0;
                $order_carrier->tracking_number = $trackingNumber;
                $order_carrier->add();
            } else {
            */
            $sql = 'SELECT `id_order_carrier`
                        FROM `'._DB_PREFIX_.'order_carrier`
                        WHERE `id_order` = '.(int)$id_order.'
                        AND `tracking_number`=""';

            // Update order_carrier
            $id_order_carrier = Db::getInstance()->getValue($sql);

            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    "updateTrackingNumber",
                    sprintf("SQL: %s", print_r($sql, true)),
                    sprintf("id_order_carrier: %s\n", print_r($id_order_carrier, true)),
                    sprintf("tracking_number: %s\n", print_r($trackingNumber, true))
                ));
            }

            if ($id_order_carrier) {
                $order_carrier = new OrderCarrier($id_order_carrier);
                $order_carrier->id_carrier = $id_carrier;
                $order_carrier->tracking_number = $trackingNumber;
                $order_carrier->update();
            }
            /*}*/
        }

        // PS 1.5 < compat
        $order->id_carrier = (int)$id_carrier;
        $order->shipping_number = $trackingNumber;

        $order->id_carrier = (int)$id_carrier;

        return ($order->update());
    }

    public static function shippingQuoteByWeight($weight, $id_carrier, $id_address, $useTaxes)
    {
        $carrier_tax_rate = 0;
        $carrier = null;

        // Carrier Taxes
        //
        if ($useTaxes && method_exists('Carrier', 'getTaxesRate')) {
            $carrier = new Carrier((int)$id_carrier);

            if (Validate::isLoadedObject($carrier)) {
                $tax_address = new Address((int)$id_address);

                if (Validate::isLoadedObject($tax_address)) {
                    $carrier_tax_rate = (float)$carrier->getTaxesRate($tax_address);
                }
            }
        } else {
            if ($useTaxes && method_exists('Tax', 'getCarrierTaxRate')) {
                if ($id_carrier) {
                    $carrier = new Carrier($id_carrier);

                    if (Validate::isLoadedObject($carrier)) {
                        $carrier_tax_rate = (float)Tax::getCarrierTaxRate((int)$id_carrier, (int)$id_address);
                    }
                }
            }
        }

        if ($carrier instanceof Carrier && method_exists('Carrier', 'getDeliveryPriceByWeight')) {
            $address = new Address((int)$id_address);

            if (!Validate::isLoadedObject($address)) {
                return (null);
            }

            if (($shipping_tax_excl = $carrier->getDeliveryPriceByWeight($weight, Country::getIdZone($address->id_country))) === false) {
                return (null);
            }

            $shipping_tax_incl = ((((float)$carrier_tax_rate * (float)$shipping_tax_excl) / 100) + (float)$shipping_tax_excl);

            return ($shipping_tax_incl);
        }

        return (null);
    }
}
