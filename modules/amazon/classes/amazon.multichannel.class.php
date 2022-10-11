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

require_once(dirname(__FILE__).'/../classes/amazon.orderhistory.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_info.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.tools.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.product.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');

class AmazonMultiChannel extends AmazonOrder
{
    const AMAZON_FBA_MULTICHANNEL = 'MAFN';
    const AMAZON_FBA_AMAZON = 'AFN';
    const AMAZON_FBA_MERCHANT = 'MFN';
    const AMAZON_FBA_STATUS_SUBMITED = 'submited';
    const AMAZON_FBA_STATUS_RECEIVED = 'received';
    const AMAZON_FBA_STATUS_INVALID = 'invalid';
    const AMAZON_FBA_STATUS_PLANNING = 'planning';
    const AMAZON_FBA_STATUS_PROCESSING = 'processing';
    const AMAZON_FBA_STATUS_CANCELLED = 'cancelled';
    const AMAZON_FBA_STATUS_COMPLETE = 'complete';
    const AMAZON_FBA_STATUS_COMPLETEPARTIALLED = 'completepartialled';
    const AMAZON_FBA_STATUS_UNFULFILLABLE = 'unfulfillable';

    public static $errors                   = array();
    public $marketPlaceChannelStatus = null;

    protected static $allowed_deliveries_for_countries_iso_codes = array('CA', 'MX', 'US', 'IN', 'JP', 'CN', 'DE', 'BE', 'FR', 'IE', 'IT', 'LU', 'NL', 'PT', 'GB', 'AU', 'BE', 'BG', 'CY', 'DK', 'EE', 'FI', 'FR', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'PL', 'CZ', 'RO', 'SK', 'SI', 'SE',);

    public function __construct($id = null, $id_lang = null)
    {
        $this->context = Context::getContext();

        parent::__construct($id, $id_lang);

        AmazonContext::restore($this->context);

        // Init
        //
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $employee = null;
            $id_employee = Configuration::get('AMAZON_EMPLOYEE');

            if ($id_employee) {
                $employee = new Employee($id_employee);
            }

            if (!Validate::isLoadedObject($employee)) {
                die($this->l('Wrong Employee, please save the module configuration'));
            }

            $id_group = (int)Configuration::get('AMAZON_CUSTOMER_GROUP');

            $group = new Group($id_group);

            if (!Validate::isLoadedObject($group)) {
                $id_group = null;
            }

            if (!$id_group || !is_numeric($id_group)) {
                $id_group = Configuration::get('PS_CUSTOMER_GROUP');
            }

            $this->context->customer->is_guest = true;
            $this->context->customer->id_default_group = $id_group;
            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }

        if ($id) {
            $this->_getMpStatus();
        }
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    private function _getMpStatus()
    {
        if ($this->amazon_order_info->is_standard_feature_available) {
            if ($this->amazon_order_info->getOrderInfo()) {
                // For compatibility
                $this->marketPlaceOrderId = $this->amazon_order_info->mp_order_id;
                $this->marketPlaceOrderStatus = $this->amazon_order_info->mp_status;
                $this->marketPlaceChannel = $this->amazon_order_info->channel;
                $this->marketPlaceChannelStatus = $this->amazon_order_info->channel_status;

                return (true);
            }
        }

        // For compatibility
        if (!Tools::strlen($this->marketPlaceChannel) && AmazonTools::fieldExists(_DB_PREFIX_.'orders', 'mp_order_id')) {
            $sql = 'SELECT `mp_order_id`, `mp_status`, `mp_channel`, `mp_channel_status` FROM `'._DB_PREFIX_.'orders`
                    WHERE `id_order` = "'.(int)$this->id.'" LIMIT 1 ;';

            if ($result = Db::getInstance()->executeS($sql)) {
                $result = array_shift($result);

                if (Tools::strlen($result['mp_order_id'])) {
                    $this->marketPlaceOrderId = $result['mp_order_id'];
                }
                if (Tools::strlen($result['mp_status'])) {
                    $this->marketPlaceOrderStatus = $result['mp_status'];
                }
                if (Tools::strlen($result['mp_channel'])) {
                    $this->marketPlaceChannel = $result['mp_channel'];
                }
                if (Tools::strlen($result['mp_channel_status'])) {
                    $this->marketPlaceChannelStatus = $result['mp_channel_status'];
                }

                return (true);
            }
        }
        return (false);
    }

    /**
     * @param int $days
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function orderList($days = 30)
    {
        $result = array();
        $result = array();
        $sql = null;

        if (AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS)) {
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'orders` o 
            LEFT JOIN `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS.'` mp ON (o.`id_order` = mp.`id_order`)
            WHERE `date_add` > DATE_ADD(NOW(), INTERVAL -'.(int)$days.' DAY)
            ORDER by `date_add` ASC';

            if (!($result = Db::getInstance()->executeS($sql))) {
                $result = array();
            }
        }

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                "Amazon, Debug Mode".Amazon::LF,
                sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                "SQL1: $sql\n",
                "orderList returned\n",
                $result
            ));
        }

        return ($result);
    }

    /**
     * @param $ps_status
     * @param int $days
     *
     * @return array|bool|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function ordersByStatus($ps_status, $days = 30, $id_order = null)
    {
        if (!is_array($ps_status) || !count($ps_status)) {
            return(false);
        }
        $result = array();
        $result1 = array();
        $result2 = array();

        $statuses = rtrim(implode(', ', $ps_status), ', ');

        $amazon_channels = sprintf('"%s", "%s"', AmazonMultiChannel::AMAZON_FBA_AMAZON, self::AMAZON_FBA_MULTICHANNEL);

        if (is_numeric($id_order)) {
            $filter = ' AND o.`id_order` = '.(int)$id_order;
        } else {
            $filter = null;
        }
        
        if (AmazonTools::fieldExists(_DB_PREFIX_.'orders', 'mp_channel')) {
            $sql = 'SELECT o.`id_order`, o.`mp_channel_status`, o.`shipping_number`, o.`mp_order_id`, o.`date_add` FROM `'._DB_PREFIX_.'orders` o WHERE
            `mp_channel` IN ('.$amazon_channels.')
            AND (SELECT oh.id_order_state FROM `'._DB_PREFIX_.'order_history` oh WHERE o.id_order = oh.id_order ORDER BY oh.date_add DESC, oh.id_order_history DESC LIMIT 1) IN ('.pSQL($statuses).')
            AND `date_add` > DATE_ADD(NOW(), INTERVAL -'.(int)$days.' DAY)'.$filter.'
            ORDER by `date_add` ASC';

            if (!($result1 = Db::getInstance()->executeS($sql))) {
                $result1 = array();
            }
        }

        if (AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS)) {
            $sql = 'SELECT o.`id_order`, mp.`channel_status` as mp_channel_status, o.`shipping_number`, mp.`mp_order_id`, o.`date_add` FROM `'._DB_PREFIX_.'orders` o 
            LEFT JOIN `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS.'` mp ON (o.`id_order` = mp.`id_order`)
            WHERE mp.`channel` IN ('.$amazon_channels.')
            AND (SELECT oh.id_order_state FROM `'._DB_PREFIX_.'order_history` oh WHERE o.id_order = oh.id_order ORDER BY oh.date_add DESC, oh.id_order_history DESC LIMIT 1) IN ('.pSQL($statuses).')
            AND `date_add` > DATE_ADD(NOW(), INTERVAL -'.(int)$days.' DAY)'.$filter.'
            ORDER by `date_add` ASC';

            if (!($result2 = Db::getInstance()->executeS($sql))) {
                $result2 = array();
            }
        }

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                "Amazon, Debug Mode".Amazon::LF,
                sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                "SQL: $sql\n",
                "ordersByStatus returned\n",
                $result1,
                $result2
            ));
        }

        if (is_array($result1) && count($result1)) {
            $result = $result1;
        }
        if (is_array($result2) && count($result2)) {
            $result = array_merge($result, $result2);
        }
        return ($result);
    }

    /**
     * @param $id_order
     *
     * @return bool|Order
     */
    public static function isEligible($id_order)
    {
        $order = new Order($id_order);

        if (!Validate::isLoadedObject($order)) {
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    "Amazon, Debug Mode".Amazon::LF,
                    sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                    'Unable to load Order'.$id_order."\n"
                ));
            }

            return (false);
        }
        $id_lang = $order->id_lang;

        // Check FBA-MultiChannel Eligibility
        $products = $order->getProducts();

        if (!$products || !is_array($products) || !count($products)) {
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    "Amazon, Debug Mode".Amazon::LF,
                    sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                    'Order returned no products ('.$id_order.")\n"
                ));
            }

            return (false);
        }

        $carriers_multichannel = AmazonConfiguration::get('CARRIER_MULTICHANNEL');

        if (Amazon::$debug_mode) {
            echo "Amazon, Debug Mode\n";
            printf('%s, line %d'."\n", basename(__FILE__), __LINE__);
            echo "Order id_lang: $order->id_lang\n";
            echo "Multichannel Carriers:\n";
            echo nl2br(print_r($carriers_multichannel, true));
        }

        if (!isset($carriers_multichannel[$id_lang]) || !is_array($carriers_multichannel[$id_lang])
            || !is_array($carriers_multichannel[$id_lang]['amazon'])
            || !count($carriers_multichannel[$id_lang]['amazon'])) {
            $error = 'FBA Multi-Channel Carrier Mapping is not or not correctly configured';

            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    "Amazon, Debug Mode".Amazon::LF,
                    sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                    $error . "\n"
                ));
            }
            self::$errors[] = $error;

            return (false);
        }

        $pass = false;
        foreach ($carriers_multichannel[$id_lang]['prestashop'] as $key => $prestashop_id_carrier) {
            if ($prestashop_id_carrier == $order->id_carrier) {
                $pass = true;
                break;
            }
        }

        if (!$pass) {
            $error = sprintf('Carrier Mapping not found for this entry - id_order: %d - id_lang: %d - id_carrier: %d', $order->id, $id_lang, $order->id_carrier);

            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    "Amazon, Debug Mode".Amazon::LF,
                    sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                    $error . "\n"
                ));
            }

            return (false);
        }

        // Require all the ordered products are FBA
        //
        foreach ($products as $product) {
            if (!AmazonTools::validateSKU($product['product_reference'])) {
                if (Amazon::$debug_mode) {
                    AmazonTools::pre(array(
                        "Amazon, Debug Mode".Amazon::LF,
                        sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                        'AmazonTools::validateSKU, invalid SKU ('.$product['product_reference'].")\n"
                    ));
                }

                return (false);
            }
            $id_product_attribute = (int)$product['product_attribute_id'] ? $product['product_attribute_id'] : null;

            if (!($options = AmazonProduct::getProductOptions($product['product_id'], $order->id_lang, $id_product_attribute))) {
                if (Amazon::$debug_mode) {
                    AmazonTools::pre(array(
                        "Amazon, Debug Mode".Amazon::LF,
                        sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                        'No product options available for this product ('.$product['product_reference'].")\n"
                    ));
                }

                return (false);
            }

            if (!isset($options['fba']) || !(bool)$options['fba']) {
                if (Amazon::$debug_mode) {
                    AmazonTools::pre(array(
                        "Amazon, Debug Mode".Amazon::LF,
                        sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                        'FBA flag is not set to on ('.$product['product_reference'].")\n"
                    ));
                }

                return (false);
            }
        }

        return ($order);
    }

    /**
     * @param $order_id
     * @param $id_lang
     * @param bool $debug
     *
     * @return bool|SimpleXMLElement
     */
    public function CancelFulfillmentOrder($order_id, $id_lang, $debug = false)
    {
        // Init
        //
        $amazon = AmazonTools::selectPlatforms($id_lang, $debug);

        if ($debug) {
            echo nl2br(print_r($amazon['auth'], true).print_r($amazon['params'], true).print_r($amazon['platforms'], true));
        }

        if (!($amazonAPI = new AmazonWebService($amazon['auth'], $amazon['params'], $amazon['platforms'], $debug))) {
            $error = 'Unable to login';
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        $result = $amazonAPI->cancelFulfillmentOrder($order_id);

        if (!$result) {
            $error = 'Impossible to retrieve the order from Amazon';
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        $this->updateMpChannel(self::AMAZON_FBA_STATUS_CANCELLED, self::AMAZON_FBA_MULTICHANNEL);

        return ($result);
    }

    /**
     * @param $status
     * @param null $channel
     *
     * @return bool
     */
    public function updateMpChannel($status, $channel = null)
    {
        $this->amazon_order_info->getOrderInfo();

        $this->marketPlaceChannelStatus = $status; // compat
        if ($channel) {
            $this->marketPlaceChannel = $channel; // compat;
        }

        $this->amazon_order_info->id_order = $this->id;

        if ($channel) {
            $this->amazon_order_info->channel = $channel;
        }
        $this->amazon_order_info->channel_status = $status;

        return($this->amazon_order_info->saveOrderInfo());
    }

    /**
     * @param $PackageNumber
     * @param $id_lang
     * @param bool $debug
     *
     * @return bool|SimpleXMLElement
     */
    public function getPackageTrackingDetails($PackageNumber, $id_lang, $debug = false)
    {
        // Init
        //
        $amazon = AmazonTools::selectPlatforms($id_lang, $debug);

        if ($debug) {
            echo nl2br(print_r($amazon['auth'], true).print_r($amazon['params'], true).print_r($amazon['platforms'], true));
        }

        if (!($amazonAPI = new AmazonWebService($amazon['auth'], $amazon['params'], $amazon['platforms'], $debug))) {
            $error = 'Unable to login';
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        $result = $amazonAPI->getPackageTrackingDetails($PackageNumber);

        if (!$result) {
            $error = 'Impossible to retrieve the order from Amazon';
            if ($debug) {
                CommonTools::p($error);
            }

            return (false);
        }

        return ($result);
    }

    /**
     * @param $order_id
     * @param $id_lang
     * @param bool $debug
     *
     * @return bool|SimpleXMLElement
     */
    public function getFulfillmentOrder($order_id, $id_lang, $debug = false)
    {
        // Init
        //
        $amazon = AmazonTools::selectPlatforms($id_lang, $debug);

        if ($debug) {
            echo nl2br(print_r($amazon['auth'], true).print_r($amazon['params'], true).print_r($amazon['platforms'], true));
        }

        if (!($amazonAPI = new AmazonWebService($amazon['auth'], $amazon['params'], $amazon['platforms'], $debug))) {
            $error = 'Unable to login';
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        $result = $amazonAPI->getFulfillmentOrder($order_id);

        if (!$result) {
            $error = 'Impossible to retrieve the order from Amazon';
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        return ($result);
    }

    /**
     * @param $id_lang
     * @param bool $debug
     *
     * @return array|bool
     */
    public function createFulfillmentOrder($id_lang, $debug = false)
    {
        $carriers_multichannel = AmazonConfiguration::get('CARRIER_MULTICHANNEL');
        $useTax = (int)AmazonConfiguration::get('TAXES') ? true : false;
        $specials = (int)AmazonConfiguration::get('SPECIALS') ? true : false;
        $id_order_state = (int)Configuration::get('AMAZON_FBA_MULTICHANNEL_STATE');

        $error = null;

        if (!Validate::isLoadedObject($this)) {
            $error = sprintf('Unable to load order');
            if ($debug) {
                CommonTools::p($error);
                debug_print_backtrace(false);
            }
            self::$errors[] = $error;

            return (false);
        }

        $id_order = $this->id;

        if (!$id_order_state) {
            $error = 'Order state for FBA is not yet configured';
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        if (Amazon::$debug_mode) {
            echo "Amazon, Debug Mode\n";
            printf('%s, line %d'."\n", basename(__FILE__), __LINE__);
            echo "Order id_lang: $id_lang\n";
            echo "Multichannel Carriers:\n";
            echo nl2br(print_r($carriers_multichannel, true));
        }

        if (!isset($carriers_multichannel[$id_lang]) || !is_array($carriers_multichannel[$id_lang])
            || !is_array($carriers_multichannel[$id_lang]['amazon'])
            || !count($carriers_multichannel[$id_lang]['amazon'])) {
            $error = 'FBA Multi-Channel Carrier Mapping is not or not correctly configured';
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }
        $pass = false;
        foreach ($carriers_multichannel[$id_lang]['prestashop'] as $key => $prestashop_id_carrier) {
            if ($prestashop_id_carrier == $this->id_carrier) {
                $ShippingSpeedCategory = $carriers_multichannel[$id_lang]['amazon'][$key];
                $pass = true;
                break;
            }
        }
        if (!$pass) {
            $error = sprintf('Carrier Mapping not found for this entry - id_order: %d - id_lang: %d - id_carrier: %d', $this->id, $id_lang, $this->id_carrier);
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        $currency = new Currency((int)(Configuration::get('PS_CURRENCY_DEFAULT')));
        $shop_name = Configuration::get('PS_SHOP_NAME');
        $id_customer = (int)$this->id_customer;

        $amazon = AmazonTools::selectPlatforms($id_lang, $debug);

        if ($debug) {
            echo nl2br(print_r($amazon['auth'], true).print_r($amazon['params'], true).print_r($amazon['platforms'], true));
        }

        if (!($amazonAPI = new AmazonWebService($amazon['auth'], $amazon['params'], $amazon['platforms'], $debug))) {
            $error = 'Unable to login';
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        $customer = new Customer($id_customer);

        if (!Validate::isLoadedObject($customer)) {
            $error = sprintf('%s - %d', 'Unable to find customer', $id_customer);
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        $address = new Address($this->id_address_delivery);

        if (!Validate::isLoadedObject($address)) {
            $error = sprintf('%s - %d', 'Unable to find address - 2', $id_customer);
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        $AmazonOrder = array();
        $AmazonOrder['DisplayableOrderDateTime'] = gmdate('Y-m-d\TH:i:s\Z', time());
        $AmazonOrder['SellerFulfillmentOrderId'] = $id_order;
        $AmazonOrder['DisplayableOrderId'] = $id_order;
        $AmazonOrder['DisplayableOrderComment'] = self::filter(sprintf('Order #%s from %s', $id_order, $shop_name));
        $AmazonOrder['ShippingSpeedCategory'] = $ShippingSpeedCategory;

        $AmazonOrder['NotificationEmailList'] = array();

        $fba_notification = Configuration::get('AMAZON_FBA_NOTIFICATION');
        $shop_email = self::filter(Configuration::get('PS_SHOP_EMAIL'));

        switch ($fba_notification) {
            case Amazon::FBA_NOTIFICATION_CUSTOMER:
                $AmazonOrder['NotificationEmailList'][] = $customer->email;
                break;
            case Amazon::FBA_NOTIFICATION_SHOP:
                if (Tools::strlen($shop_email)) {
                    $AmazonOrder['NotificationEmailList'][] = $shop_email;
                }
                break;
            case Amazon::FBA_NOTIFICATION_BOTH:
            default:
                if (Tools::strlen($shop_email)) {
                    $AmazonOrder['NotificationEmailList'][] = $shop_email;
                }
                $AmazonOrder['NotificationEmailList'][] = $customer->email;
                break;
        }

        $AmazonOrder['DestinationAddress'] = array();
        $AmazonOrder['DestinationAddress']['Name'] = self::filter(sprintf('%s %s', $address->firstname, $address->lastname));

        if ($address->company) {
            $AmazonOrder['DestinationAddress']['Line1'] = self::filter($address->company);
            $AmazonOrder['DestinationAddress']['Line2'] = self::filter($address->address1);
            $AmazonOrder['DestinationAddress']['Line3'] = self::filter($address->address2);
        } else {
            $AmazonOrder['DestinationAddress']['Line1'] = self::filter($address->address1);
            $AmazonOrder['DestinationAddress']['Line2'] = self::filter($address->address2);
            $AmazonOrder['DestinationAddress']['Line3'] = null;
        }

        $country_iso_code = Country::getIsoById($address->id_country);

        if (Tools::strlen($country_iso_code) && !in_array($country_iso_code, self::$allowed_deliveries_for_countries_iso_codes)) {
            $error = sprintf('%s - "%s"', 'Country is not eligible for FBA delivery', $country_iso_code);
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        } elseif (!Tools::strlen($country_iso_code)) {
            $error = sprintf('%s - %d', 'Missig Country ISO Code for order', $id_order);
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        $AmazonOrder['DestinationAddress']['City'] = self::filter($address->city);
        $AmazonOrder['DestinationAddress']['PostalCode'] = self::filter($address->postcode);
        $AmazonOrder['DestinationAddress']['CountryCode'] = $country_iso_code;
        $AmazonOrder['DestinationAddress']['PhoneNumber'] = Tools::strlen($address->phone_mobile) ? self::filter($address->phone_mobile) : self::filter($address->phone);

        // Mandatory: Required by Amazon
        if ($address->id_state) {
            $state = new State($address->id_state);
            $AmazonOrder['DestinationAddress']['StateOrProvinceCode'] = $state->iso_code ? $state->iso_code : $state->name;
        } else {
            $AmazonOrder['DestinationAddress']['StateOrProvinceCode'] = Country::getNameById($id_lang, $address->id_country);
        }

        foreach ($AmazonOrder['DestinationAddress'] as $key => $val) {
            if (function_exists('filter_var')) {
                $sanitized = filter_var($val, FILTER_SANITIZE_STRING);
            } else {
                $sanitized = $val;
            }

            $AmazonOrder['DestinationAddress'][$key] = $sanitized;
        }

        $AmazonOrder['Items'] = array();

        $products = $this->getProducts();

        if (!$products || !is_array($products) || !count($products)) {
            $error = sprintf('%s - %d', 'Empty or wrong cart for order:', $id_order);
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        // Search for the suitable currency
        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketPlaceCurrency = AmazonConfiguration::get('CURRENCY');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        if (!is_array($marketPlaceRegion) || !is_array($marketPlaceCurrency)) {
            $error = sprintf('Lack of configuration: marketplace regions: %s marketplace currencies: %s', print_r($marketPlaceRegion, true), print_r($marketPlaceCurrency, true));
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }
        $marketLang2Region = array_flip($marketPlaceRegion);

        $target_country = $amazon['params']['Country'];

        if (!isset($marketLang2Region[$target_country])) {
            $error = sprintf('Lack of configuration: marketplace regions: %s', print_r($marketLang2Region, true));
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }
        $target_id_lang = $marketLang2Region[$target_country];
        $target_id_currency = Currency::getIdByIsoCode($marketPlaceCurrency[$target_id_lang]);
        $target_marketplace = $marketPlaceIds[$target_id_lang];

        if (!(int)$target_id_currency) {
            $error = sprintf('Missing currency: %s', $marketPlaceCurrency[$target_id_lang]);
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }
        
        if ($debug) {
            AmazonTools::pre(array(
                "target country: $target_country \n",
                "target id_lang: $target_id_lang \n",
                "target id_currency: $target_id_currency \n",
                "shop id_currency: $currency->id \n"
            ));
        }


        if ($this->amazon_order_info->is_standard_feature_available) {
            $this->amazon_order_info->channel = self::AMAZON_FBA_MULTICHANNEL;
            $this->amazon_order_info->sales_channel = AmazonTools::encodeText($shop_name);
            $this->amazon_order_info->ship_category = $ShippingSpeedCategory;
            $this->amazon_order_info->marketplace_id = $target_marketplace;
        }

        $SKUCheck = array();
        $index = 0;
        foreach ($products as $cart_product) {
            $SKU = $cart_product['product_reference'];

            if (empty($SKU)) {
                $error = sprintf('Missing Reference(SKU) for product: %d/%d', $cart_product['product_id'], $cart_product['product_attribute_id']);
                if ($debug) {
                    CommonTools::p($error);
                }
                self::$errors[] = $error;
                continue;
            }

            $product = new AmazonProduct($SKU, false, $id_lang);

            if (!Validate::isLoadedObject($product)) {
                $error = print(sprintf('%s - %s', 'Unable to find product', $SKU));

                if ($debug) {
                    CommonTools::p($error);
                }
                self::$errors[] = $error;

                continue;
            }

            if (!($options = AmazonProduct::getProductOptions((int)$product->id, $id_lang, $product->id_product_attribute))) {
                $error = sprintf('%s - %d', 'Uneligible product: %d/%d', $cart_product['product_id'], $cart_product['product_attribute_id']);
                if ($debug) {
                    CommonTools::p($error);
                }
                continue;
            }

            if (!isset($options['fba']) || !(bool)$options['fba']) {
                $error = sprintf('%s - %d', 'Not FBA product: %d/%d', $product->id, $product->id_product_attribute);
                if ($debug) {
                    CommonTools::p($error);
                }
                self::$errors[] = $error;
                continue;
            }
            $SellerID = sprintf('i-%d-%d-%d', (int)$id_order, (int)$product->id, (int)$product->id_product_attribute);
            $Price = $product->getPrice($useTax, $product->id_product_attribute, 6, null, false, !$product->on_sale && $specials);
            $Quantity = isset($cart_product['product_quantity']) ? (int)$cart_product['product_quantity'] : 1;

            $SKUCheck[$index] = $SKU;
            $AmazonOrder['Items'][$index]['SKU'] = self::filter($SKU);
            $AmazonOrder['Items'][$index]['SellerSKU'] = self::filter($SKU);
            $AmazonOrder['Items'][$index]['SellerFulfillmentOrderItemId'] = self::filter($SellerID);
            $AmazonOrder['Items'][$index]['Quantity'] = $Quantity;

            if ($currency->id != $target_id_currency) {
                $target_currency = new Currency($target_id_currency);

                if (!Validate::isLoadedObject($target_currency)) {
                    $error = sprintf('%s - %d', 'Unable to load currency id: %d', $this->id_currency);
                    if ($debug) {
                        CommonTools::p($error);
                    }
                    self::$errors[] = $error;
                    continue;
                }

                $newPrice = Tools::convertPrice($Price, $target_currency);
                $AmazonOrder['Items'][$index]['PerUnitDeclaredValue.CurrencyCode'] = $target_currency->iso_code;
                $AmazonOrder['Items'][$index]['PerUnitDeclaredValue.Value'] = Tools::ps_round($newPrice, 2);
            } else {
                $AmazonOrder['Items'][$index]['PerUnitDeclaredValue.CurrencyCode'] = $amazon['params']['Currency'];
                $AmazonOrder['Items'][$index]['PerUnitDeclaredValue.Value'] = Tools::ps_round($Price, 2);
            }
            $AmazonOrder['Items'][$index]['DisplayableComment'] = self::filter($product->name);

            if (Tools::strlen($this->gift_message)) {
                $AmazonOrder['Items'][$index]['GiftMessage'] = self::filter($this->gift_message);
                // On Prestashop we can't send per item gift message, thus we send the message for all ordered items.
            }
            $index++;
        }

        // Check availability of products
        //
        $result = $amazonAPI->ListInventoryBySKU($SKUCheck);

        if (!$result || !is_array($result)) {
            $error = sprintf('Product availability check failed for order id: %s', $id_order);

            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }


        if (!is_array($result) || !count($result)) {
            $error = sprintf('Product availability, no items available for order id: %s', $id_order);

            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }
        $indexes = array_flip($SKUCheck);


        // Verify Quantities
        foreach ($result as $Item) {
            if (!isset($indexes[$Item['SKU']])) {
                continue;
            }

            $index = $indexes[$Item['SKU']];
            if (isset($Item['InStockSupplyQuantity']) && $Item['InStockSupplyQuantity'] >= $AmazonOrder['Items'][$index]['Quantity']) {
                if ($debug) {
                    $message = sprintf('Availability Check: %s - Quantity: %s', $Item['SKU'], $Item['InStockSupplyQuantity']);
                    CommonTools::p($message);
                }
                unset($SKUCheck[$index]);
            }
        }

        // Remaining products in SKUCheck: unavailable products or not enough stock
        if (is_array($SKUCheck) && count($SKUCheck)) {
            $error = sprintf('Product availability, not enough stock to fulfill the order: %s', $id_order);
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        if ($debug) {
            CommonTools::p($AmazonOrder);
        }

        $result = $amazonAPI->createFulfillmentOrder($AmazonOrder, true);

        if (isset($result->ResponseMetadata->RequestId) && preg_match('/([0-9A-Fa-f]{4,16}[\-]{0,}){5}/', (string)$result->ResponseMetadata->RequestId)) {
            $AmazonOrder['Response'] = (string)$result->ResponseMetadata->RequestId;

            // Restock Product
            //
            foreach ($products as $product) {
                $id_product = (int)$product['product_id'];
                $id_product_attribute = (int)$product['product_attribute_id'] ? (int)$product['product_attribute_id'] : null;

                $SellerID = sprintf('i-%d-%d-%d', (int)$id_order, (int)$id_product, (int)$id_product_attribute);

                foreach ($AmazonOrder['Items'] as $key => $Item) {
                    if ($Item['SellerFulfillmentOrderItemId'] == $SellerID) {
                        break;
                    }
                }
            }

            // New Order History
            $this->updateMpChannel(self::AMAZON_FBA_STATUS_SUBMITED, self::AMAZON_FBA_MULTICHANNEL);
            $this->addToHistory($id_employee, $id_order_state);

            return ($AmazonOrder);
        } elseif (isset($result->Error)) {
            if ($debug) {
                $error = sprintf('Amazon Error - Code:%s Message:%s', $result->Error->Code, $result->Error->Message);
            }
            self::$errors[] = $error;

            return (false);
        } else {
            if ($debug) {
                $error = sprintf('%s(#%d): Unknown Error, content: {%s}', basename(__FILE__), __LINE__, print_r($result, true));
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }
    }

    /**
     * @param $text
     *
     * @return string
     */
    public static function filter($text)
    {
        $text = mb_ereg_replace('[!<>?=+{}_$%&]*$', '', $text);// remove chars rejected by Validate class

        return $text;
    }

    /**
     * @param $id_employee
     * @param $id_order_state
     */
    public function addToHistory($id_employee, $id_order_state)
    {
        // Add History
        $new_history = new AmazonOrderHistory();
        $new_history->id_order = (int)$this->id;
        $new_history->id_employee = (int)$id_employee;
        $new_history->changeIdOrderState($id_order_state, $this->id);
        $new_history->addWithemail(true);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("AmazonOrderHistory: %s", print_r(get_object_vars($new_history), true)));
        }
        return;
    }

    /**
     * @param $date
     * @param $id_lang
     * @param bool $debug
     *
     * @return array|bool
     */
    public function ListAllFulfillmentOrders($date, $id_lang, $debug = false)
    {
        // Init
        //
        $amazon = AmazonTools::selectPlatforms($id_lang, $debug);

        if ($debug) {
            echo nl2br(print_r($amazon['auth'], true).print_r($amazon['params'], true).print_r($amazon['platforms'], true));
        }

        if (!($amazonAPI = new AmazonWebService($amazon['auth'], $amazon['params'], $amazon['platforms'], $debug))) {
            $error = 'Unable to login';
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        $result = $amazonAPI->ListAllFulfillmentOrders($date);

        if (!$result) {
            $error = 'Impossible to retrieve the order from Amazon';
            if ($debug) {
                CommonTools::p($error);
            }
            self::$errors[] = $error;

            return (false);
        }

        return ($result);
    }
}
