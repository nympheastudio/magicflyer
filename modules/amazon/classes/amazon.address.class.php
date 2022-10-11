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

require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.tools.class.php');

class AmazonAddress extends Address
{
    public static $table_name = Amazon::TABLE_MARKETPLACE_ORDER_ADDRESS;

    public static $orders_fields = array(
        'order_id',
        'order_item_id',
        'purchase_date',
        'payments_date',
        'buyer_email',
        'buyer_name',
        'buyer_phone_number',
        'sku',
        'product_name',
        'quantity_purchased',
        'currency',
        'item_price',
        'item_tax',
        'shipping_price',
        'shipping_tax',
        'ship_service_level',
        'recipient_name',
        'ship_address_1',
        'ship_address_2',
        'ship_address_3',
        'ship_city',
        'ship_state',
        'ship_postal_code',
        'ship_country',
        'ship_phone_number',
        'bill_address_1',
        'bill_address_2',
        'bill_address_3',
        'bill_city',
        'bill_state',
        'bill_postal_code',
        'bill_country',
        'item_promotion_discount',
        'item_promotion_id',
        'ship_promotion_discount',
        'ship_promotion_id',
        'delivery_start_date',
        'delivery_end_date',
        'delivery_time_zone',
        'delivery_Instructions',
        'sales_channel'
    );

    public static $report_mapping = array(
        'order_id' => 'mp_order_id',
        'purchase_date' => 'date',
        'buyer_email' => 'email',
        'buyer_name' => 'billing_name',
        'buyer_phone_number' => 'billing_phone',
        'recipient_name' => 'shipping_name',
        'ship_address_1' => 'shipping_address_1',
        'ship_address_2' => 'shipping_address_2',
        'ship_address_3' => 'shipping_address_3',
        'ship_city' => 'shipping_city',
        'ship_state' => 'shipping_state',
        'ship_postal_code' => 'shipping_postcode',
        'ship_country' => 'shipping_country',
        'ship_phone_number' => 'shipping_phone',
        'bill_address_1' => 'billing_address_1',
        'bill_address_2' => 'billing_address_2',
        'bill_address_3' => 'billing_address_3',
        'bill_city' => 'billing_city',
        'bill_state' => 'billing_state',
        'bill_postal_code' => 'billing_postcode',
        'bill_country' => 'billing_country',
        'delivery_Instructions' => 'instructions',
    );

    public static $errors = array();

    /**
     * Use to manage shipping cost calculation
     *
     * @param $marketplaces
     * @param $id_customer
     * @return bool
     */
    public static function createShippingLocations($marketplaces, $id_customer)
    {
        $addressMap = array();

        $pass = false;

        if (is_array($marketplaces)) {
            $pass = true;

            foreach ($marketplaces as $region) {
                $city = null;
                $state = null;
                $iso_code = null;

                switch ($region) {
                    case 'us': // US
                        $city = 'Seattle';
                        $state = 'WA';
                        break;
                    case 'fr': // France
                        $city = 'Paris';
                        break;
                    case 'es': // Spain
                        $city = 'Madrid';
                        break;
                    case 'de': // Germany
                        $city = 'Berlin';
                        break;
                    case 'it': // Italy
                        $city = 'Rome';
                        break;
                    case 'uk': // UK
                        $iso_code = 'GB';
                        $city = 'London';
                        break;
                    case 'jp': // Japan
                        $city = 'Tokyo';
                        break;
                    case 'in': // India
                        $city = 'New Delhi';
                        break;
                    case 'ca': // Canada
                        $city = 'Toronto';
                        break;
                    case 'cn':
                        $city = 'Beijin';
                        break;
                    case 'mx':
                        $city = 'Mexico';
                        break;
                    case 'br':
                        $city = 'Brazilia';
                        break;
                    case 'cn':
                        $city = 'Beijing';
                        break;
                }
                if (!$iso_code) {
                    $iso_code = Tools::strtoupper($region);
                }

                if (!$city) {
                    continue;
                }

                $locationAlias = 'Amazon - '.Tools::strtoupper($region);

                if (($id_address = self::addressExistsByAlias($locationAlias, $id_customer))) {
                    $addressMap[$region] = $id_address;
                    continue;
                }
                $address = new Address();
                $address->alias = $locationAlias;
                $address->id_customer = $id_customer;
                $address->firstname = 'Amazon';
                $address->lastname = 'Marketplace';
                $address->address1 = 'Amazon shipping reference address';
                $address->address2 = 'Please do not remove';
                $address->postcode = '99999';
                $address->phone = '0100000000';
                $address->phone_mobile = '0100000000';
                $address->other = 'This address is used by the Amazon Marketplace Module, please do not edit or remove !';
                $address->city = $city;
                $address->state = $state;
                $address->id_country = Country::getByIso($iso_code);
                $address->add();

                if (Validate::isLoadedObject($address)) {
                    $pass = true && $pass;
                } else {
                    $pass = false && $pass;
                }

                $addressMap[$region] = $address->id;
            }
            AmazonConfiguration::updateValue('ADDRESS_MAP', $addressMap);
        }

        return ($pass);
    }

    /**
     * Specify if an address is already in base
     *
     * @param $alias
     * @param null $id_customer
     * @return id_address|null
     */
    public static function addressExistsByAlias($alias, $id_customer = null)
    {
        if ($id_customer) {
            $customer_filter = ' AND a.`id_customer`='.(int)$id_customer;
        } else {
            $customer_filter = '';
        }

        $row = Db::getInstance()->getRow('
             SELECT `id_address`
             FROM `'._DB_PREFIX_.'address` a
             WHERE a.`alias` = "'.pSQL($alias).'"'.$customer_filter);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf("Address: %s\n ", print_r($row, true)));
        }

        if (isset($row['id_address']) && $row['id_address']) {
            return ($row['id_address']);
        } else {
            return(null);
        }
    }

    /**
     * @param $object
     *
     * @return object
     */
    public function htmlToUtf8(&$object)
    {
        foreach ($object as $key => $value) {
            $object->{$key} = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
        }
        return($object);
    }

    /**
     * Creates customer address entry
     * @param $id_lang
     * @param $amazonAddress
     * @return bool|id_address|int
     * @throws PrestaShopException
     */
    public function lookupOrCreateamazonAddress($id_lang, $amazonAddress)
    {
        $alias = $this->hash($amazonAddress);

        $id_address = $this->addressExistsByAlias($alias);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf("addressExistsByAlias returned: %s\n ", print_r($id_address, true)));
        }
        
        if (!$id_address) {
            $this->htmlToUtf8($amazonAddress);

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p(sprintf("Address: %s\n ", print_r($amazonAddress, true)));
            }

            $this->id_country = Country::getByIso((string)$amazonAddress->CountryCode);

            if (!$this->id_country) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p("Unable to load country: ". (string)$amazonAddress->CountryCode);
                }
                return (false);
            }

            $this->country = Country::getNameById($id_lang, $this->id_country);

            if (!Tools::strlen($this->country)) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf("Unable retrieve country for id_lang: %d, id_country: %d, country code: %s\n ", $id_lang, $this->id_country, (string)$amazonAddress->CountryCode));
                }
                return (false);
            }

            $this->alias = $alias;

            $name = self::getAmazonName($amazonAddress->Name, $id_lang);

            $this->lastname = $this->filter($name['lastname']);
            $this->firstname = $this->filter($name['firstname']);
            $this->company = $this->filter($name['company']);

            if (empty($amazonAddress->AddressLine1)) {
                $this->address1 = preg_replace('/[!<>?=+@{}_$%]+/', '', (string)trim($amazonAddress->AddressLine2));
                $this->address2 = null;
            } else {
                $this->address1 = preg_replace('/[!<>?=+@{}_$%]+/', '', (string)trim($amazonAddress->AddressLine1));
                $this->address2 = preg_replace('/[!<>?=+@{}_$%]+/', '', (string)trim($amazonAddress->AddressLine2));
            }

            if (!Tools::strlen($this->address1) && Tools::strlen($this->address2)) {
                $this->address1 = $this->address2;
                $this->address2 = '';
            }

            if (!Tools::strlen($this->address1)) {
                $this->address1 = 'Unknown';
            }

            $this->address1 = preg_replace('/"/', "'", $this->address1);
            $this->address2 = preg_replace('/"/', "'", $this->address2);

            $this->postcode = preg_replace('/[,\/]/', '-', $amazonAddress->PostalCode);
            $this->postcode = preg_replace('/[^a-zA-Z 0-9-]/', '', $this->postcode);

            $this->city = Tools::strtoupper($this->filter((string)trim($amazonAddress->City)));

            if (empty($this->postcode)) {
                $this->postcode = 'NA';
            }

            if (empty($this->city)) {
                $this->city = 'NA';
            }

            if (empty($this->address1)) {
                $this->address1 = 'Unknown';
            }

            $this->phone = preg_replace('/,/', '-', $amazonAddress->Phone);
            $this->phone = preg_replace('/[^+0-9. ()-]/', '', $this->phone);

            // 2013-01-05 Olivier - getStatesByIdCountry is not supported by PS < 1.4
            if (version_compare(_PS_VERSION_, '1.4', '>=')) {
                /* Modif YB du 17/12/2012 - Mappage de l'etat pour les adresses US */
                if (isset($amazonAddress->StateOrRegion) && Tools::strlen($amazonAddress->StateOrRegion) && AmazonTools::toKey($amazonAddress->StateOrRegion) != AmazonTools::toKey($amazonAddress->City)) {
                    if ($this->id_country) {
                        $statesList = State::getStatesByIdCountry($this->id_country);
                        if (is_array($statesList) && count($statesList)) {
                            foreach ($statesList as $curstate) {
                                $state_or_region = AmazonTools::toKey($amazonAddress->StateOrRegion);

                                if (AmazonTools::toKey($curstate['iso_code']) == $state_or_region || AmazonTools::toKey($curstate['name']) == $state_or_region) {
                                    $this->id_state = (int)$curstate['id_state'];
                                    break;
                                }
                            }
                        }
                        if (!$this->id_state && empty($this->address2)) {
                            $this->address2 = $this->filter(trim($amazonAddress->StateOrRegion));
                        } elseif (!$this->id_state && Tools::strlen(sprintf('%s, %s', $this->address1, $this->address2)) < 128) {
                            $this->address1 = sprintf('%s, %s', $this->address1, $this->address2);
                            $this->address2 = $this->filter(trim($amazonAddress->StateOrRegion));
                        } elseif (!$this->id_state && Tools::strlen(sprintf('%s, %s', $this->address2, $this->filter(trim($amazonAddress->StateOrRegion)))) < 128) {
                            $this->address2 = sprintf('%s, %s', $this->address2, $this->filter(trim($amazonAddress->StateOrRegion)));
                        } elseif (!$this->id_state) {
                            $this->other = $this->filter(trim($amazonAddress->StateOrRegion));
                        }
                    }
                }
            }
            $this->address1 = trim(trim($this->address1), ',');
            $this->address2 = trim(trim($this->address2), ',');

            if (!Tools::strlen($this->other) && isset($amazonAddress->Instructions) && Tools::strlen($amazonAddress->Instructions)) {
                $this->other = $amazonAddress->Instructions;
            }

            //  fields sizes must match with parent Address class
            //
            foreach (array(
                         'company',
                         'firstname',
                         'lastname',
                         'address1',
                         'address2',
                         'postcode',
                         'city',
                         'phone',
                         'phone_mobile'
                     ) as $field) {
                $this->{$field} = Tools::substr($this->{$field}, 0, $this->fieldsSize[$field]);
            }

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p("address: ". print_r(get_object_vars($this), true));
            }

            $this->date_add = date('Y-m-d H:i:s');// just to pass field validation
            $this->date_upd = date('Y-m-d H:i:s');// just to pass field validation


            if (!$this->validateFields(false, false)) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p("Validate Fields Failed.");
                }
                return (false);
            }
            $this->add();

            if (Validate::isLoadedObject($this)) {
                return ($this->id);
            } else {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p("Address Creation Failed.");
                }
            }
            return(false);
        }

        return ($id_address);
    }

    /**
     * Create a unique key to prevent to save duplicate customers
     * @param $obj
     * @return string
     */
    public function hash($obj)
    {
        $str = $obj->Name.$obj->AddressLine1.$obj->AddressLine2.$obj->City.$obj->PostalCode.$obj->CountryCode;

        return (md5($str));
    }

    /**
     * Split as best as we can firstname and lastname
     *
     * @param $fullname
     * @return array
     */
    public static function getAmazonName($fullname, $id_lang = null)
    {
        static $sort_orders = null;
        $result = array();
        $result['company'] = '';

        $has_co = preg_match('/(.+)[\s,-]+[C\/O;:|\-,]+[\s,-]+(.+)/i', $fullname, $name_company1);
        $has_between_parenthesis = preg_match('/([^\(]+)[\s,:;-]+\((.+)\)$/', $fullname, $name_company2);

        if ($has_co) {
            // case: John Doe c/o Apple Inc.

            $result['company'] = trim(end($name_company1));
            $var = trim($name_company1[1]);
        } elseif ($has_between_parenthesis) {
            $var = trim($name_company2[1]);
            $result['company'] = trim(end($name_company2));
        } elseif (preg_match('/,|\//', $fullname)) {
            // Nom Prenom, Company
            //
            $parts = preg_split('/,|\//', $fullname);
            $var = trim(reset($parts));
            $result['company'] = trim(implode(',', $parts));
        } else {
            $var = self::filter($fullname);
        }

        $var = mb_ereg_replace('[0-9!<>,;?=+()@#"°{}_$%:]', '', $var);

        $reverse_fullname = self::mbStrRev($var);
        $name1 = trim(self::mbStrRev(mb_substr($reverse_fullname, mb_strpos($reverse_fullname, ' '))));
        $name2 = trim(self::mbStrRev(mb_substr($reverse_fullname, 0, mb_strpos($reverse_fullname, ' '))));

        if (empty($name1) && empty($name2)) {
            $name1 = 'unknown';
            $name2 = 'unknown';
        } elseif (empty($name1)) {
            $name1 = $name2;
        } elseif (empty($name2)) {
            $name2 = $name1;
        }

        $firstname = AmazonTools::ucfirst(mb_substr($name1, 0, 32));
        $lastname = AmazonTools::ucfirst(mb_substr($name2, 0, 32));

        if ($sort_orders === null) {
            $sort_orders = AmazonConfiguration::get('SORT_ORDER');
        }
        if (is_array($sort_orders) && count($sort_orders) && isset($sort_orders[$id_lang]) && is_numeric($sort_orders[$id_lang]) && (int)$sort_orders[$id_lang]) {
            switch ((int)$sort_orders[$id_lang]) {
                case Amazon::SORT_ORDER_LASTNAME_FIRSTNAME:
                    $result['lastname'] = self::filter($firstname);
                    $result['firstname'] = self::filter($lastname);
                    break;
                default:
                    $result['lastname'] = self::filter($lastname);
                    $result['firstname'] = self::filter($firstname);
                    break;
            }
        } else {
            $result['lastname'] = self::filter($lastname);
            $result['firstname'] = self::filter($firstname);
        }

        return ($result);
    }

    /**
     * Similar strrev but with multibyte support
     * @param $str
     * @param string $encoding
     * @return string
     */
    public static function mbStrRev($str, $encoding = 'UTF-8')
    {
        return mb_convert_encoding(strrev(mb_convert_encoding($str, 'UTF-16BE', $encoding)), $encoding, 'UTF-16LE');
    }

    /**
     * Filter unallowed characters for Prestashop
     * @param $text
     * @return mixed|string
     */
    public static function filter($text)
    {
        if (!AmazonTools::isJapanese($text)) {
            $text = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');

            $searches = array('&szlig;', '&(..)lig;', '&([aouAOU])uml;', '&(.)[^;]*;');
            $replacements = array('ss', '\\1', '\\1'.'e', '\\1');

            foreach ($searches as $key => $search) {
                $text = mb_ereg_replace($search, $replacements[$key], $text);
            }
        }

        $text = str_replace('_', '/', $text);
        $text = mb_ereg_replace('[\x00-\x1F\x21-\x2C\x3A-\x3F\x5B-\x60\x7B-\x7F\x2E\x2F]]', '', $text); // remove non printable
        $text = preg_replace('/[\x{00AA}-\x{00AF}]/u', '', $text);//unwanted utf8
        $text = preg_replace('/[\x{00B1}-\x{00BF}]/u', '', $text);
        $text = mb_ereg_replace('"', "'", $text);// remove chars rejected by Validate class
        $text = mb_ereg_replace('[!<>?=+@{}_$%;:,]*', '', $text);// remove chars rejected by Validate class

        return $text;
    }

    public static function createTable()
    {
        $pass = true;

        // Report fields:
        // order-id	order-item-id	purchase-date	payments-date	buyer-email	buyer-name	buyer-phone-number	sku	product-name	quantity-purchased	currency	item-price	item-tax	shipping-price	shipping-tax
        // ship-service-level	recipient-name	ship-address-1	ship-address-2	ship-address-3	ship-city	ship-state	ship-postal-code	ship-country	ship-phone-number	bill-address-1	bill-address-2	bill-address-3	bill-city
        // bill-state	bill-postal-code	bill-country	item-promotion-discount	item-promotion-id	ship-promotion-discount	ship-promotion-id	delivery-start-date	delivery-end-date	delivery-time-zone	delivery-Instructions	sales-channel

        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ADDRESS.'` (
                        `mp_order_id` VARCHAR(32) NULL DEFAULT NULL,
                        `date` DATETIME NULL DEFAULT NULL,
                        `email` VARCHAR(64) NULL DEFAULT NULL,
                        `billing_name` VARCHAR(64) NULL DEFAULT NULL,
                        `billing_address_1` VARCHAR(64) NULL DEFAULT NULL,
                        `billing_address_2` VARCHAR(64) NULL DEFAULT NULL,
                        `billing_address_3` VARCHAR(64) NULL DEFAULT NULL,
                        `billing_postcode` VARCHAR(16) NULL DEFAULT NULL,
                        `billing_city` VARCHAR(64) NULL DEFAULT NULL,
                        `billing_state` VARCHAR(64) NULL DEFAULT NULL,
                        `billing_country` VARCHAR(3) NULL DEFAULT NULL,
                        `billing_phone` VARCHAR(32) NULL DEFAULT NULL,
                        `shipping_name` VARCHAR(64) NULL DEFAULT NULL,
                        `shipping_address_1` VARCHAR(64) NULL DEFAULT NULL,
                        `shipping_address_2` VARCHAR(64) NULL DEFAULT NULL,
                        `shipping_address_3` VARCHAR(64) NULL DEFAULT NULL,
                        `shipping_postcode` VARCHAR(16) NULL DEFAULT NULL,
                        `shipping_city` VARCHAR(64) NULL DEFAULT NULL,
                        `shipping_state` VARCHAR(64) NULL DEFAULT NULL,
                        `shipping_country` VARCHAR(3) NULL DEFAULT NULL,
                        `shipping_phone` VARCHAR(64) NULL DEFAULT NULL,
                        `instructions` VARCHAR(128) NULL DEFAULT NULL,
                        UNIQUE KEY `mp_order_id_idx` (`mp_order_id`),
                        KEY `date_idx` (`date`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        if (!Db::getInstance()->Execute($sql)) {
            $error = 'SQL: '.$sql.Amazon::LF.'ERROR: '. Db::getInstance()->getMsgError();
            self::$errors[] = $error;
            $pass = false;
        }
        return($pass);
    }

    public static function getAmazonBillingAddress($mp_order_id)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ADDRESS.'` WHERE `mp_order_id`="'.pSQL($mp_order_id).'"';

        if (!($result = Db::getInstance()->getRow($sql))) {
            return(false);
        }
        $addresses = new StdClass();
        $pass = true;
        $keycheck = array('billing_name', 'billing_address_1', 'billing_country');

        if (Amazon::$debug_mode) {
            CommonTools::p("Addresses Result:");
            CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p($result);
        }

        foreach ($keycheck as $key) {
            if (isset($result[$key]) && Tools::strlen($result[$key])) {
                continue;
            }
            $pass = false;
        }
        if ($pass) {
            $address = new SimpleXMLElement(html_entity_decode('&lt;xml&gt;&lt;/xml&gt;'));
            $address->addChild('Name', $result['billing_name']);
            $address->addChild('AddressLine1', $result['billing_address_1']);
            $address->addChild('AddressLine2', $result['billing_address_2']);
            $address->addChild('PostalCode', $result['billing_postcode']);
            $address->addChild('City', $result['billing_city']);
            $address->addChild('CountryCode', $result['billing_country']);
            $address->addChild('StateOrRegion', $result['billing_state']);
            $address->addChild('Phone', $result['billing_phone']);
            $addresses->billing_address = new AmazonWsAddress($address);
        }

        $pass = true;
        $keycheck = array('shipping_name', 'shipping_address_1', 'shipping_country');

        foreach ($keycheck as $key) {
            if (isset($result[$key]) && Tools::strlen($result[$key])) {
                continue;
            }
            $pass = false;
        }

        if ($pass) {
            $address = new SimpleXMLElement(html_entity_decode('&lt;xml&gt;&lt;/xml&gt;'));
            $address->addChild('Name', $result['shipping_name']);
            $address->addChild('AddressLine1', $result['shipping_address_1']);
            $address->addChild('AddressLine2', $result['shipping_address_2']);
            $address->addChild('PostalCode', $result['shipping_postcode']);
            $address->addChild('City', $result['shipping_city']);
            $address->addChild('CountryCode', $result['shipping_country']);
            $address->addChild('StateOrRegion', $result['shipping_state']);
            $address->addChild('Phone', $result['shipping_phone']);
            $address->addChild('Instructions', $result['instructions']);
            $addresses->shipping_address = new AmazonWsAddress($address);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p('Addresses Result:');
            CommonTools::p(get_object_vars($addresses));
        }

        return($addresses);
    }

    /**
     * Get all rows by customer email
     * @param $email
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllMpOrderIdsByEmail($email)
    {
        $sql = 'SELECT `mp_order_id` 
                FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ADDRESS.'` 
                WHERE `email` = "'.pSQL(trim($email)).'"';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get all by list of order id
     * @param $mp_order_ids
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllByMpOrderIds($mp_order_ids)
    {
        $ids = AmazonTools::buildQueryConditionIn($mp_order_ids, false);

        $sql = 'SELECT * FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ADDRESS.'` 
                WHERE `mp_order_id` IN ('.$ids.')';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param $mp_order_ids
     * @return bool
     */
    public static function deleteAllByMpOrderIds($mp_order_ids)
    {
        $ids = AmazonTools::buildQueryConditionIn($mp_order_ids, false);

        $sql = 'DELETE FROM `'._DB_PREFIX_.self::$table_name.'` 
                WHERE `mp_order_id` IN ('.$ids.')';

        return Db::getInstance()->execute($sql);
    }
}
