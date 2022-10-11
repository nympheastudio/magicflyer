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

require_once(dirname(__FILE__).'/../classes/amazon.address.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_item.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_info.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.orderhistory.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.cart.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.payment.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.support.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.mail.logger.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.remote_cart.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.orders_reports.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.batch.class.php');
require_once(dirname(__FILE__).'/../common/order.class.php');

class AmazonImportOrder extends Amazon
{
    public static $errors     = array();
    public static $warnings   = array();
    public static $messages   = array();
    public static $send_email = false;
    public static $orders     = array();

    private $_amazonApi = array();

    // To check if order is special FBA
    private $_marketplaces;         // Amazon languages
    private $_default_tax_rule;     // Default tax rule for each marketplace, use for special FBA order

    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        $this->amazon_features = $this->getAmazonFeatures();
    }

    public static function jsonDisplayExit()
    {
        $output = null;

        if (Amazon::$debug_mode) {
            $output = ob_get_contents();
        }
        if (!count(AmazonImportOrder::$orders)) {
            if (!Amazon::$debug_mode) {
                $result = trim(ob_get_clean());
            } else {
                $result = null;
            }

            if ($result) {
                AmazonImportOrder::$warnings[] = $result;
            }
        } else {
            if (!Amazon::$debug_mode) {
                $result = trim(ob_get_clean());
            } else {
                $result = null;
            }

            if ($result) {
                AmazonImportOrder::$messages[] = $result;
            }
        }

        if ((self::$send_email && count(AmazonImportOrder::$errors) && Tools::getValue('cron')) || (self::$send_email && Amazon::$debug_mode)) {
            $email = null;

            if (count(AmazonImportOrder::$messages)) {
                $email .= sprintf('Messages : ').self::LF;
                foreach (AmazonImportOrder::$messages as $message) {
                    $email .= ' - '.$message.self::LF;
                }
            }
            if (count(AmazonImportOrder::$warnings)) {
                $email .= sprintf('Warnings : ').self::LF;
                foreach (AmazonImportOrder::$warnings as $warning) {
                    $email .= ' - '.$warning.self::LF;
                }
            }
            if (count(AmazonImportOrder::$errors)) {
                $email .= sprintf('Errors : ').self::LF;
                foreach (AmazonImportOrder::$errors as $error) {
                    $email .= ' - '.$error.self::LF;
                }
            }

            if ($output) {
                $email .= sprintf('Orders : ').self::LF;
                $email .= print_r(AmazonImportOrder::$orders, true);

                $email .= sprintf('Output : ').self::LF;
                $email .= $output;
            }
            AmazonMailLogger::message($email);
        }

        foreach (array(
                     AmazonImportOrder::$errors,
                     AmazonImportOrder::$warnings,
                     AmazonImportOrder::$messages
                 ) as $key => $tofix_array) {
            if (is_array($tofix_array) && count($tofix_array)) {
                // Fix rare issues

                $tofix_array[$key] = self::fixEncoding($tofix_array[$key]);
            }
        }

        $json = Tools::jsonEncode(array(
            'orders' => AmazonImportOrder::$orders,
            'count' => count(AmazonImportOrder::$orders),
            'error' => (count(AmazonImportOrder::$errors) ? true : false),
            'errors' => AmazonImportOrder::$errors,
            'warning' => (count(AmazonImportOrder::$warnings) ? true : false),
            'warnings' => AmazonImportOrder::$warnings,
            'message' => count(AmazonImportOrder::$messages),
            'messages' => AmazonImportOrder::$messages
        ));

        if (($callback = Tools::getValue('callback'))) {
            // jquery

            echo (string)$callback.'('.$json.')';
        } else {
            // cron

            echo $json;
        }
    }

    public static function fixEncoding(&$array_to_fix)
    {
        if (is_array($array_to_fix) && count($array_to_fix)) {
            foreach ($array_to_fix as $key => $item) {
                if (!mb_check_encoding($item, 'UTF-8')) {
                    $array_to_fix[$key] = mb_convert_encoding($item, "UTF-8");
                }
            }
        }

        return ($array_to_fix);
    }

    public function dispatch($action)
    {
        switch ($action) {
            default:
                $this->import();
        }
    }


    private function createProduct($ASIN, $sku, $name, $price)
    {
        if (!AmazonTools::validateSKU($sku) || !AmazonTools::validateASIN($ASIN)) {
            return(false);
        }
        if (AmazonConfiguration::shopIsFeatureActive()) {
            $id_shop = (int)$this->context->shop->id;
        } else {
            $id_shop = null;
        }
        $product = new AmazonProduct($sku, false, (int)$this->context->language->id, 'reference', $id_shop);

        if (Validate::isLoadedObject($product)) {
            return($product);
        }

        $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages(false);
        $language_array = array();
        $language_array[$id_lang_default] = null;

        $name_array = array();
        $link_array = array();

        foreach ($languages as $language) {
            $id_lang = (int)$language['id_lang'];
            $name_array[$id_lang] = Tools::substr(str_replace(array('<', '>', ';', '=', '#', '{', '}'), '/', $name), 0, 128);
            $link_array[$id_lang] = Tools::substr(Tools::link_rewrite($name_array[$id_lang]), 0, 128) ;
        }
        $reference = Tools::substr($sku, 0, 32);

        if (!Validate::isReference($sku)) {
            return(false);
        }

        $product = new Product();
        $product->name = $name_array;
        $product->reference = $reference;
        $product->active = true;
        $product->available_for_order = true;
        $product->visibility = 'none';
        $product->id_tax_rules_group = 0;
        $product->is_virtual = 0;
        $product->tax_name = null;
        $product->tax_rate = 0;
        $product->price = (float)$price;
        $product->link_rewrite = $link_array;
        $product->id_product_attribute = null;
        if (method_exists('Product', 'getIdTaxRulesGroupMostUsed')) {
            $product->id_tax_rules_group = (int)Product::getIdTaxRulesGroupMostUsed();
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p("New Product: ".print_r(get_object_vars($product), true));
        }

        if ($product->validateFields(false, true)) {
            $product->add();

            if (!Validate::isLoadedObject($product)) {
                return(false);
            }

            if (method_exists('StockAvailable', 'setProductOutOfStock')) {
                StockAvailable::setProductOutOfStock((int)$product->id, 1);
            }
            AmazonProduct::updateProductOptions($product->id, $id_lang_default, 'asin1', $ASIN);

            return($product);
        } else {
            return(false);
        }
    }

    public function import()
    {
        $import_count = 0;
        $timestart = time();
        $handle_combinations = !(bool)Configuration::get('AMAZON_NO_COMBINATIONS');
        $orders_ids = array();

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
        self::$send_email = (bool)Configuration::get('AMAZON_EMAIL');
        $convert_currency = (bool)Configuration::get('AMAZON_CONVERT_CURRENCY');

        ob_start();
        
        register_shutdown_function(array('AmazonImportOrder', 'jsonDisplayExit'));

        $cronMode = 0;
        $currentDate = date('Y-m-d H:i:s');
        $shipping_price = 0;

        // Regions
        //
        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketLang2Region = array_flip($marketPlaceRegion);
        $main_region = '??';

        // WGET MODE
        if (Tools::getValue('cron')) {
            $lang = Tools::getValue('lang');
            $cronToken = Tools::getValue('cron_token');

            $cr = nl2br(Amazon::LF); // carriage return

            $cronMode = 1;
            echo 'Starting Order Query in WS API/Cron Mode'.' - '.$currentDate.$cr;

            $date1 = date('c', strtotime('now - 1 day'));
            $date2 = date('c', strtotime('now - 15 min'));

            if (!isset($marketLang2Region[$lang]) || !$marketLang2Region[$lang]) {
                die($this->l('No selected language, nothing to do...'));
            }

            $id_lang = $marketLang2Region[$lang];
            $main_region = $marketPlaceRegion[$id_lang];

            if (!AmazonTools::checkToken($cronToken)) {
                die('Wrong Token');
            }

            $status = Tools::getValue('status', 'Unshipped');
        } else {
            //
            // Web Mode
            //
            $cr = nl2br(Amazon::LF); // carriage return

            $status = (string)Tools::getValue('statuses');
            $id_lang = (int)Tools::getValue('amazon_lang');
            $tokens = Tools::getValue('amazon_token');

            if ((int)Tools::getValue('europe')) {
                $masterMarketplace = AmazonConfiguration::get(self::CONFIG_MASTER);

                if (isset($marketLang2Region[$masterMarketplace]) && $marketLang2Region[$masterMarketplace]) {
                    $id_lang = $marketLang2Region[$masterMarketplace];
                } else {
                    $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
                }
                $main_region = 'eu';
                $europe = 1;
            } else {
                if (isset($marketPlaceRegion[$id_lang]) && $marketPlaceRegion[$id_lang]) {
                    $main_region = $marketPlaceRegion[$id_lang];
                }
                $europe = 0;
            }


            if (!AmazonTools::checkToken($tokens)) {
                die($this->l('Wrong Token'));
            }

            if (!(int)$id_lang) {
                die($this->l('No selected language, nothing to do...'));
            }
        }

        $log_filename = $this->logInit($main_region);

        $batches = new AmazonBatches('batch_acknowledge');
        $last_import = $batches->getLastForRegion($main_region);

        $i = 0;

        $tokenOrders = Tools::getValue('token_order');

        $id_warehouse = null;
        $id_shop = null;

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $employee = null;
            $id_employee = Configuration::get('AMAZON_EMPLOYEE');

            if ($id_employee) {
                $employee = new Employee($id_employee);
            }

            if (!Validate::isLoadedObject($employee)) {
                die($this->l('Wrong Employee, please save the module configuration'));
            }

            $this->context->customer->is_guest = true;
            $this->context->customer->id_default_group = (int)Configuration::get('AMAZON_CUSTOMER_GROUP');
            $this->context->cart = new Cart();
            $this->context->link = new Link(); // added for Mail Alert
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            $this->context->cart->id_currency = $this->context->currency->id;
            $this->context->cart->id_lang = $this->id_lang;

            if (AmazonConfiguration::shopIsFeatureActive()) {
                $id_shop = (int)$this->context->shop->id;
                $id_shop_group = (int)$this->context->shop->id_shop_group;
            } else {
                $id_shop = null;
            }

            $id_warehouse = (int)Configuration::get('AMAZON_WAREHOUSE');
        }

        $origin_currency = $this->context->currency->iso_code;
        $id_currency = (int)$this->context->currency->id;

        $opensi = AmazonTools::moduleIsInstalled('opensi');

        // Configuration customer Group or default customer group

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_default_customer_group = Configuration::get('PS_CUSTOMER_GROUP');
        } else {
            $id_default_customer_group = (int)_PS_DEFAULT_CUSTOMER_GROUP_;
        }

        // Customer Group
        $id_customer_group = (int)Configuration::get('AMAZON_CUSTOMER_GROUP');
        $amazon_fba_decrease_stock = Configuration::get('AMAZON_FBA_DECREASE_STOCK');

        if ((int)$id_customer_group && is_numeric($id_customer_group)) {
            $group = new Group($id_customer_group);

            if (!Validate::isLoadedObject($group)) {
                $id_customer_group =  $id_default_customer_group;
            }

            unset($group);
        } else {
            $id_customer_group = $id_default_customer_group;
        }

        // Carriers
        //
        $carriers = AmazonConfiguration::get('CARRIER');
        $amazon_carriers = AmazonConfiguration::get('AMAZON_CARRIER');

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p("amazon carriers: ". print_r($amazon_carriers, true));
        }

        // Currency
        //
        $currencies = AmazonConfiguration::get('CURRENCY');

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p("currencies: ". print_r($currencies, true));
        }

        // Fulfillment by Amazon (FBA)
        //
        $FBA = (bool)$this->amazon_features['fba'];

        // PreOrders is activated - manage differently the order state
        $preorder = (bool)Configuration::get('AMAZON_PREORDER');
        $preorder_switch = false;

        // Order State
        //
        $order_states = AmazonConfiguration::get('ORDER_STATE');
        $id_order_states = array();

        $id_order_states[Amazon::AFN] = (int)Configuration::get('AMAZON_FBA_ORDER_STATE');

        if (is_array($order_states) && isset($order_states[Amazon::ORDER_STATE_STANDARD]) && $order_states[Amazon::ORDER_STATE_STANDARD]) {
            // New version 4.0

            $id_order_states[Amazon::MFN] = $order_states[Amazon::ORDER_STATE_STANDARD];
        } elseif ((int)$order_states) {
            $id_order_states[Amazon::MFN] = (int)$order_states;
        } else {
            die($this->l('Incoming order state must be configured - Modules > Amazon > Parameters > Orders States'));
        }

        if ((bool)Configuration::get('PS_CATALOG_MODE')) {
            die($this->l('Your shop is in catalog mode, you can\'t import orders'));
        }

        if (!$FBA) {
            $id_order_states[Amazon::AFN] = $id_order_states[Amazon::MFN];
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p("id_order_states: ". print_r($id_order_states, true));
        }

        // Use Taxes - Amazon Config overrides PS Config
        //
        $useTaxes = false;

        if (Tax::excludeTaxeOption()) {
            $useTaxes = false;
        }

        if ((int)AmazonConfiguration::get('TAXES')) {
            $useTaxes = true;
        }

        // Import unknown products as a new product
        //
        $auto_create = (bool)Configuration::get('AMAZON_AUTO_CREATE');

        // Add Region to the payment title (UK, FR, US etc..)
        //
        $paymentRegion = Configuration::get('AMAZON_PAYMENT_REGION') ? true : false;

        // Stock Management
        $stock_management = (bool)Configuration::get('PS_STOCK_MANAGEMENT');

        // Init
        //
        $amazon = AmazonTools::selectPlatforms($id_lang, Amazon::$debug_mode);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf('%s%s%s', print_r($amazon['auth'], true), print_r($amazon['params'], true), print_r($amazon['platforms'], true)));
        }

        if (!$this->_amazonApi = new AmazonWebService($amazon['auth'], $amazon['params'], $amazon['platforms'], Amazon::$debug_mode, nl2br(Amazon::LF))) {
            echo $this->l('Unable to login').$cr;
            die;
        }
        if ($this->amazon_features['demo_mode']) {
            $this->_amazonApi->demo = true;
        }

        $current_version = Configuration::get('AMAZON_CURRENT_VERSION', null, 0, 0);

        if (version_compare($current_version, $this->version, '<')) {
            //die(AmazonSupport::message($this->l('Module version and configuration mismatch, please edit and save your module configuration'), AmazonSupport::TUTORIAL_AFTER_INSTALLATION));
        }

        // Check the server's clock drift
        $request_time = time();
        $to_date = $request_time - 120;

        $result = $this->_amazonApi->serviceStatus(true);

        if (isset($result->GetServiceStatusResult)) {
            if (isset($result->GetServiceStatusResult->Timestamp)) {
                $to_date = min(strtotime((string)$result->GetServiceStatusResult->Timestamp) - 120, $request_time);
            }
        }

        $channel = Tools::strtoupper(trim(Tools::getValue('channel')));

        if (!in_array($channel, array(self::AFN, self::MFN))) {
            $channel = null;
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf('Channel: %s', $channel));
        }


        if ($channel) {
            $FBA = $channel;
        }


        // Orders reports management
        $orders_reports_management = $this->amazon_features['orders_reports'];
        $date_max = null;

        if ($orders_reports_management) {
            $sql = 'SELECT MAX(`date`) as date_max FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ADDRESS.'`';
            $result = Db::getInstance()->getValue($sql);

            if (Tools::strlen($result)) {
                $timestamp = strtotime($result);
                $now = time();

                if ($timestamp + 86400 < $now) { // cron order reports is not executed or no orders in the table ?
                    $orders_reports_management = false;
                } else {
                    $date_max = date('c', max($now - 86400, $timestamp));
                }
            } else {
                $orders_reports_management = false;
            }
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): - Order Reports management: %s, date_max: %s', basename(__FILE__), __LINE__, $orders_reports_management ? 'Yes' : 'No', $date_max));
        }

        if ($cronMode) {
            $amazonOrders = null;
            $returnXML = true;

            if (Tools::getValue('recheck')) {
                // override default parameters - recheck and reimport late orders

                $status = 'Shipped';
                $FBA = self::AFN;
                $date1 = date('c', strtotime($date1.' -15 days'));
                $date2 = date('c', strtotime($date2.' -2 days'));
            } elseif (Tools::getValue('doublecheck')) {
                // override default parameters - recheck and reimport late orders

                $status = 'Unshipped';
                $date1 = date('c', strtotime($date1.' -7 days'));
                $date2 = date('c', strtotime($date2.' -1 days'));
            } elseif (!empty($last_import)) {
                if ($this->amazon_features['remote_cart']) {
                    $time_gap = 60 * 60 * 4;//4 hours
                } else {
                    $time_gap = 60 * 15;
                }

                $date1 = date('c', max(strtotime($last_import), time() - 86400) - $time_gap); // last import less drift
                $date2 = date('c', $to_date);
            } else {
                $date2 = date('c', $to_date);
            }

            if ($date_max) {
                if (strtotime($date1) >= strtotime($date2)) {
                    sprintf('%s (due to parameter "Order Reports")'.$cr, $this->l('No orders fetched from Amazon'));

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p(sprintf('order reports active: date1: %s, date2: %s', $date1, $date_max));
                    }
                    die;
                }
                $date1 = date('c', strtotime($date_max.' -1 days'));
                $date2 = $date_max;
            }

            echo "Fetching orders from $date1 to $date2 ".$cr;

            $date1 = gmdate('Y-m-d\TH:i:s\Z', strtotime($date1));
            $date2 = gmdate('Y-m-d\TH:i:s\Z', strtotime($date2));

            if (!$amazonOrders = $this->_amazonApi->GetUnshippedOrdersListv4($date1, (!$cronMode ? $date2 : null), $status, $FBA, $returnXML, $cronMode)) {
                printf($this->l('No orders fetched from Amazon').$cr);
                die;
            }

            if (isset($amazonOrders->Error)) {
                $caller = AmazonTools::callingFunction();

                $message = sprintf('%s : %s', 'Error From', $caller).self::LF;
                $message .= sprintf('%s :', $this->l('Error while retrieving orders')).self::LF;
                $message .= sprintf('Type : %s', $amazonOrders->Error->Type).self::LF;
                $message .= sprintf('Code : %s', $amazonOrders->Error->Code).self::LF;
                $message .= sprintf('Message : %s', $amazonOrders->Error->Message).self::LF;
                $message .= sprintf('Request ID : %s', $amazonOrders->RequestID).self::LF;

                if (self::$send_email) {
                    AmazonMailLogger::message($message);
                }
                die($message);
            }
        } else {
            $orders_ids = Tools::getValue('order_id');

            if (!is_array($orders_ids) || !count($orders_ids)) {
                printf($this->l('Nothing to import...').$cr);
                die;
            }

            if (!$amazonOrders = $this->_amazonApi->GetOrders($orders_ids)) {
                printf($this->l('No orders fetched from Amazon').$cr);
                die;
            }
        }

        // Identify the order's country
        //
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');
        $marketPlace2IsoId = array_flip($marketPlaceIds);
        $count = 0;

        foreach ($amazonOrders as $key => $order) {
            $order_id = (string)$order->AmazonOrderId;

            if (!$cronMode && !in_array($order_id, $orders_ids)) {
                continue;
            }

            AmazonImportOrder::$orders[$order_id] = array();
            AmazonImportOrder::$orders[$order_id]['products'] = array();
            AmazonImportOrder::$orders[$order_id]['status'] = false;

            $preorder_switch = false;

            // Skipping Cancelled Orders
            //
            if ((string)$order->OrderStatus == AmazonOrder::ORDER_CANCELED) {
                AmazonImportOrder::$warnings[] = basename(__FILE__).': '.__LINE__.' - '.$this->l('Skipping Canceled Order').' #'.$order_id;
                continue;
            }

            if ((string)$order->OrderStatus == AmazonOrder::ORDER_PENDING) {
                $pending_order = true;
            } else {
                $pending_order = false;
            }

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p("pending order:".$pending_order);
                CommonTools::p($order);
            }

            if (AmazonOrder::checkByMpId($order_id)) {
                AmazonImportOrder::$warnings[] = sprintf($this->l('Order ID (%s) has already been imported...').$cr, $order_id);
                continue;
            }

            if (!($Items = $this->_amazonApi->getOrderItems($order_id))) {
                AmazonImportOrder::$errors[] = $this->l('Unable to retrieve items for Amazon Order').': '.$order_id;
                continue;
            }

            $checkpass = true;

            // Precheck
            /** @var OrderedItem $item */
            foreach ($Items as $item_key => $item) {
                $quantity = (int)$item->QuantityOrdered;

                if ($quantity <= 0) {
                    AmazonImportOrder::$warnings[] = AmazonSupport::message(sprintf('%s#%d: '.$this->l('Skipping zero quantity item for order #%s product SKU: %s'), basename(__FILE__), __LINE__, $order->AmazonOrderId, trim((string)$item->SKU)), null);
                    unset($Items[$item_key]);
                } else {
                    $SKU = trim((string)$item->SKU);

                    $productCheck = AmazonProduct::checkProduct($SKU, $id_shop);

                    if ($productCheck == 0 && !$auto_create) {
                        AmazonImportOrder::$errors[] = AmazonSupport::message(sprintf($this->l('SKU/Reference not found in your database. Please check existance of this product: "%s"'), $SKU), AmazonSupport::FUNCTION_IMPORT_UNEXISTENT_SKU);
                        $checkpass = false;
                    } elseif ($productCheck > 1) {
                        AmazonImportOrder::$errors[] = AmazonSupport::message(sprintf($this->l('Unable to import duplicate product "%s" - Please remove the duplicate product in your database.'), $SKU), AmazonSupport::FUNCTION_IMPORT_DUPLICATE_SKU);
                        $checkpass = false;
                    }
                }
            }
            if (!$checkpass) {
                continue;
            }
            $id_currency_from = null;

            if (!$pending_order) {
                // Langue de la Commande
                //
                if (isset($marketPlace2IsoId[$order->MarketPlaceId])) {
                    $id_lang = $marketPlace2IsoId[$order->MarketPlaceId];
                }

                // Amazon Region (fr, de, ...)
                //
                if (isset($marketPlaceRegion[$id_lang])) {
                    $region = Tools::strtoupper($marketPlaceRegion[$id_lang]);
                } else {
                    $region = null;
                }

                if (empty($order->Address->Name) || empty($order->Address)) {
                    $order->Address->Name = $order->BuyerName;
                    $order->Address->AddressLine1 = $this->l('No Address');
                    $order->Address->City = $this->l('Unknown');
                    $order->Address->PostalCode = '1000';
                    $order->Address->CountryCode = $region;
                }

                if (!isset($order->BuyerEmail) || empty($order->BuyerEmail)) {
                    $order->BuyerEmail = sprintf('amz-%s@%s', $order->AmazonOrderId, str_replace('@', '', Amazon::TRASH_DOMAIN));
                }

                $id_currency = (int)Currency::getIdByIsoCode($order->OrderTotalCurrency);

                if ($id_currency) {
                    $currency = new Currency($id_currency);

                    if (Validate::isLoadedObject($currency)) {
                        $this->context->currency = $currency;
                    } else {
                        AmazonImportOrder::$errors[] = basename(__FILE__).': '.__LINE__.' - '.$this->l('Unable to load currrency').': '.$order->OrderTotalCurrency.$cr;
                        continue;
                    }
                } else {
                    AmazonImportOrder::$errors[] = basename(__FILE__).': '.__LINE__.' - '.$this->l('Unable to load currrency').': '.$order->OrderTotalCurrency.$cr;
                    continue;
                }

                if ($this->context->cart->id_currency != $id_currency && $convert_currency) {
                    $id_currency_from = $id_currency;
                    $id_currency = $this->context->cart->id_currency;
                } else {
                    $id_currency_from = $id_currency;
                }

                if ($orders_reports_management) {
                    $addresses = AmazonAddress::getAmazonBillingAddress($order_id);

                    if (isset($addresses->shipping_address) && $addresses->shipping_address instanceof AmazonWsAddress) {
                        $order->Address = $addresses->shipping_address;

                        if (Amazon::$debug_mode) {
                            CommonTools::p("Delivery Address:");
                            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                            CommonTools::p(get_object_vars($addresses->shipping_address));
                        }
                    }
                }

                // Amazon Global Customer Account ID
                //
                $id_customer = Configuration::get('AMAZON_CUSTOMER_ID');
                $account_type = AmazonConfiguration::get('ACCOUNT_TYPE');

                if (empty($account_type) || !is_numeric($account_type)) {
                    // backward compatibility issue.
                    $account_type = Amazon::ACCOUNT_TYPE_INDIVIDUAL;
                }

                // Customer individual account
                //
                if ($account_type == Amazon::ACCOUNT_TYPE_INDIVIDUAL && isset($order->BuyerEmail) && !empty($order->BuyerEmail)) {
                    $email_address = (string)$order->BuyerEmail;

                    $customer = new Customer();

                    if (empty($email_address)) {
                        AmazonImportOrder::$errors[] = basename(__FILE__).': '.__LINE__.' - '.$this->l('Order').': #'.$order_id.' - '.$this->l('Couldn\'t add this customer').' : '.'('.$email_address.')';
                        continue;
                    }
                    $customer->getByEmail($email_address);

                    if ($customer->id) {
                        // Existing
                        $id_customer = $customer->id;
                    } else {
                        $email_address = (string)$order->BuyerEmail;

                        $name = AmazonAddress::getAmazonName((string)$order->Address->Name);

                        $customer->firstname = $name['firstname'];
                        $customer->lastname = $name['lastname'];
                        $customer->newsletter = false;
                        $customer->optin = false;
                        $customer->email = $email_address;
                        $customer->passwd = md5(rand());
                        $customer->id_default_group = $id_customer_group;

                        if (AmazonTools::propertyIsAccessible('Customer', 'id_lang')) {
                            // Added: 2014-04-16
                            $customer->id_lang = $id_lang;
                        }

                        if (!Validate::isName($customer->firstname) || !Validate::isName($customer->lastname) || !Validate::isEmail($customer->email)) {
                            AmazonImportOrder::$errors[] = basename(__FILE__).': '.__LINE__.' - '.$this->l('Order').': #'.$order_id.' - '.$this->l('Couldn\'t add this customer').' : '.print_r($name, true).'('.$email_address.')';
                            continue;
                        } else {
                            $pass = true;
                            $line = false;

                            if (!$customer->validateFields(false, false)) {
                                $pass = false;
                                $line = __LINE__;
                            } elseif (!$customer->add()) {
                                $pass = false;
                                $line = __LINE__;
                            }

                            if (!$pass) {
                                AmazonImportOrder::$errors[] = basename(__FILE__).': '.$line.' - '.$this->l('Order').': #'.$order_id.' - '.$this->l('Couldn\'t add this customer').' : '.print_r($name, true).'('.$email_address.')';
                                continue;
                            } else {
                                $id_customer = $customer->id;
                            }
                        }
                    }

                    if (Amazon::$debug_mode) {
                        CommonTools::p("Customer:");
                        CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p(get_object_vars($customer));
                    }
                }

                // FROM AMZON to PRESTASHOP !
                //
                $shipping_address = new AmazonAddress();
                $shipping_address->id_customer = $id_customer;
                $shipping_address_id = $shipping_address->lookupOrCreateamazonAddress($id_lang, $order->Address);
                $billing_address_id = $shipping_address_id;

                if (!$shipping_address_id || !$billing_address_id) {
                    AmazonImportOrder::$errors[] = sprintf($this->l('Address creation failed for order #%s').$cr, $order->AmazonOrderId);
                    continue;
                }

                if ($orders_reports_management && isset($addresses->billing_address) && $addresses->billing_address instanceof SimpleXMLElement) {
                    $billing_address = new AmazonAddress();
                    $billing_address->id_customer = $id_customer;
                    $billing_address_id = $billing_address->lookupOrCreateamazonAddress($id_lang, $addresses->billing_address);
                }

                $date_add = date('Y-m-d H:i:s', strtotime($order->PurchaseDate));

                // Associate Shipping Method
                //
                $shipping_method = trim((string)$order->ShipServiceLevel);

                if (!array_key_exists($id_lang, $amazon_carriers)) {
                    AmazonImportOrder::$errors[] = sprintf('%s#%d: '.'Missing carrier mapping for lang id %d', basename(__FILE__), __LINE__, $id_lang);
                    continue;
                }
                $count_carrier = is_array($amazon_carriers[$id_lang]) ? count($amazon_carriers[$id_lang]) : 0;

                for ($i = 0, $id_carrier = 0; $i < $count_carrier; $i++) {
                    if (array_key_exists($i, $amazon_carriers[$id_lang]) && md5($shipping_method) == $amazon_carriers[$id_lang][$i]) {
                        $id_carrier = $carriers[$id_lang][$i];
                    }
                }

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p("carriers: ". print_r($amazon_carriers[$id_lang], true));
                    CommonTools::p("shipping method: ". print_r($shipping_method, true));
                    CommonTools::p("id_carrier: ". print_r($id_carrier, true));
                }

                if (!$id_carrier) {
                    AmazonImportOrder::$errors[] = AmazonSupport::message(sprintf($this->l('Unable to associate the carrier (%s) for order #%s').$cr, $shipping_method, $order->AmazonOrderId), AmazonSupport::FUNCTION_IMPORT_CARRIER_MAPPING);
                    continue;
                }
            }
            $channel = (string)$order->FulfillmentChannel;
            $sales_channel = (string)$order->SalesChannel;
            $order_channel = (string)$order->OrderChannel;
            $marketplace_id = (string)$order->MarketPlaceId;
            $buyer_name = (string)$order->BuyerName;
            $is_europe = AmazonTools::isEuropeMarketplaceId($marketplace_id);

            $earliest_ship_date = $order->EarliestShipDate;
            $latest_ship_date = $order->LatestShipDate;
            $earliest_delivery_date = $order->EarliestDeliveryDate;
            $latest_delivery_date = $order->LatestDeliveryDate;
            
            $ship_category = $order->ShipmentServiceLevelCategory;
            $is_prime = $order->IsPrime;
            $is_premium = $order->IsPremiumOrder;
            $is_business = $order->IsBusinessOrder;

            $status = (string)$order->OrderStatus;
            
            $itemDetails = array();

            if (!$pending_order) {
                // Building Cart
                //
                $cart = new AmazonCart();
                $cart->id_address_delivery = $shipping_address_id;
                $cart->id_address_invoice = $billing_address_id;
                $cart->id_carrier = $id_carrier;
                $cart->id_currency = $id_currency;
                $cart->id_customer = $id_customer;
                $cart->id_lang = $id_lang;

                if (($validation_message = $cart->validateFields(false, true)) !== true) {
                    AmazonImportOrder::$errors[] = sprintf('%s#%d: '.'Field Validation failed for this cart (Order: %s) - Reason: %s', basename(__FILE__), __LINE__, $order->AmazonOrderId, $validation_message);

                    if (Validate::isLoadedObject($cart)) {
                        $cart->delete();
                    }
                    continue;
                }

                $cart->amazon_order_info = new AmazonOrderInfo;
                $cart->amazon_order_info->mp_order_id = $order_id;
                $cart->amazon_order_info->mp_status = $status;
                $cart->amazon_order_info->channel = $channel;
                $cart->amazon_order_info->marketplace_id = Tools::substr($marketplace_id, 0, 16);
                $cart->amazon_order_info->buyer_name = Tools::substr($buyer_name, 0, 32);
                $cart->amazon_order_info->sales_channel = Tools::substr($sales_channel, 0, 32);
                $cart->amazon_order_info->order_channel = Tools::substr($order_channel, 0, 32);
                $cart->amazon_order_info->ship_service_level = Tools::substr($shipping_method, 0, 32);
                $cart->amazon_order_info->is_prime = (bool)$is_prime;
                $cart->amazon_order_info->is_premium = (bool)$is_premium;
                $cart->amazon_order_info->is_business = (bool)$is_business;
                $cart->amazon_order_info->earliest_ship_date = $earliest_ship_date;
                $cart->amazon_order_info->latest_ship_date = $latest_ship_date;
                $cart->amazon_order_info->earliest_delivery_date = $earliest_delivery_date;
                $cart->amazon_order_info->latest_delivery_date = $latest_delivery_date;

                $cart->add();
            }

            $totalQuantity = $totalSaleableQuantity = 0;
            
            $mpStatusId = constant('AmazonOrder::'.Tools::strtoupper($status));

            /*
            if ($channel != Amazon::AFN) {
                $shipping_discount = (float)$item->ShippingDiscountAmount;
            } else {
                $shipping_discount = 0;
            }
            */

            $total_shipping = 0;
            
            $i = 0;
            foreach ($Items as $item) {
                $quantity = (int)$item->QuantityOrdered;
                $shipping_price = 0;

                if ((float)$item->ShippingDiscountAmount && is_numeric($item->ShippingDiscountAmount)) {
                    $shipping_discount = (float)$item->ShippingDiscountAmount;
                } else {
                    $shipping_discount = 0;
                }

                if ($id_currency_from != $id_currency && $convert_currency) {
                    $from_currency = new Currency($id_currency_from);

                    $id_currency = $this->context->cart->id_currency;
                    $discount = Tools::ps_round(Tools::convertPrice((float)$item->PromotionDiscountAmount, $from_currency, false), 2);
                    $price = Tools::ps_round(Tools::convertPrice((float)($item->ItemPriceAmount - ($discount ? $discount : 0)) / $quantity, $from_currency, false), 2);
                    $giftwrap = $item->GifWrapPrice ? Tools::ps_round(Tools::convertPrice((float)$item->GifWrapPrice, $from_currency, false), 2) : null;
                    $shipping_price = (float)$item->ShippingPriceAmount ? Tools::ps_round(Tools::convertPrice((float)$item->ShippingPriceAmount - $shipping_discount, $from_currency, false), 2) : null;
                    $item_tax = (float)$item->TaxesInformation->ItemTaxAmount ? Tools::ps_round(Tools::convertPrice((float)$item->TaxesInformation->ItemTaxAmount, $from_currency, false), 2) : null;
                    $shipping_tax = (float)$item->TaxesInformation->ShippingTaxAmount ? Tools::ps_round(Tools::convertPrice((float)$item->TaxesInformation->ShippingTaxAmount, $from_currency, false), 2) : null;
                    $giftwrap_tax = (float)$item->TaxesInformation->GiftWrapTaxAmount ? Tools::ps_round(Tools::convertPrice((float)$item->TaxesInformation->GiftWrapTaxAmount, $from_currency, false), 2) : null;

                    if (Amazon::$debug_mode) {
                        CommonTools::p("Cart:");
                        CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p(sprintf('currency conversion: %d/%d', $id_currency_from, $id_currency));
                        CommonTools::p(sprintf('price: %.02f/%.02f', (float)($item->ItemPriceAmount - $discount) / $quantity, $price));
                    }
                } else {
                    $discount = (float)$item->PromotionDiscountAmount;
                    $price = (float)($item->ItemPriceAmount - $discount) / $quantity;
                    $giftwrap = $item->GifWrapPrice ? (float)$item->GifWrapPrice : null;
                    $shipping_price = (float)$item->ShippingPriceAmount - $shipping_discount;
                    $item_tax = (float)$item->TaxesInformation->ItemTaxAmount;
                    $shipping_tax = (float)$item->TaxesInformation->ShippingTaxAmount;
                    $giftwrap_tax = (float)$item->TaxesInformation->GiftWrapTaxAmount;
                }
                $total_shipping += $shipping_price;
                $totalQuantity += (int)$item->QuantityOrdered;
                $product_name = (string)$item->Title;
                $SKU = trim((string)$item->SKU);
                $ASIN = trim((string)$item->ASIN);
                $giftmsg = $item->GiftMessageText ? (string)$item->GiftMessageText : null;
                $order_item_id = $item->OrderItemId;
                $auto_create_import = false;

                $product = new AmazonProduct($SKU, false, $id_lang, 'reference', $id_shop);

                if ($auto_create && !Validate::isLoadedObject($product)) {
                    $new_product = $this->createProduct($ASIN, $SKU, $product_name, $price);

                    if (!Validate::isLoadedObject($new_product)) {
                        AmazonImportOrder::$errors[] = sprintf('%s#%d: '.$this->l('Unable to create product for order #%s product ASIN: %s SKU: %s'), basename(__FILE__), __LINE__, $order->AmazonOrderId, $item->ASIN, $SKU);
                        unset($itemDetails[$SKU]);
                        continue;
                    }
                    $product = $new_product;
                    $auto_create_import = true;
                }

                if (!Validate::isLoadedObject($product)) {
                    /*
                     * This error can happen if the product has not been loaded, recurring case: the field title or description is not filled for the target language.
                     */
                    AmazonImportOrder::$errors[] = AmazonSupport::message(sprintf('%s#%d: '.$this->l('Unable to find the expected product for order #%s product ASIN: %s SKU: %s'), basename(__FILE__), __LINE__, $order->AmazonOrderId, $item->ASIN, $SKU), AmazonSupport::FUNCTION_IMPORT_UNKNOWN_SKU);
                    unset($itemDetails[$SKU]);
                    continue;
                }
                $id_product = (int)$product->id;

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p("Product: ".print_r(get_object_vars($product), true));
                }

                if (($validation_message = $product->validateFields(false, true)) !== true) {
                    AmazonImportOrder::$errors[] = sprintf('%s#%d: '.'Field Validation failed for this product (Order: %s, SKU: %s) - Reason: %s', basename(__FILE__), __LINE__, $order->AmazonOrderId, $SKU, $validation_message);

                    unset($itemDetails[$SKU]);
                    continue;
                }

                if ($product->active === '') {
                    AmazonImportOrder::$errors[] = sprintf('%s(%d): Invalid Product Sheet - product: "%s".', basename(__FILE__), __LINE__, $SKU);
                    unset($itemDetails[$SKU]);
                    continue;
                }

                if (!(bool)$product->active) {
                    AmazonImportOrder::$errors[] = AmazonSupport::message(sprintf($this->l('Unable to import inactive product "%s" - Please activate this product prior to import the order.'), $SKU), AmazonSupport::FUNCTION_IMPORT_INACTIVE_UNAVAILABLE);
                    unset($itemDetails[$SKU]);
                    continue;
                }

                if (isset($product->available_for_order) && !$product->available_for_order) {
                    AmazonImportOrder::$errors[] = AmazonSupport::message(sprintf($this->l('Unable to import unavailable product "%s" - Please set "available product" to yes for this product prior to import the order.'), $SKU), AmazonSupport::FUNCTION_IMPORT_INACTIVE_UNAVAILABLE);
                    unset($itemDetails[$SKU]);
                    continue;
                }

                if (!$product->id_product_attribute && isset($product->minimal_quantity) && $product->minimal_quantity > 1) {
                    AmazonImportOrder::$errors[] = AmazonSupport::message(sprintf($this->l('Unable to import product \"%s\" for order #%s. The product has a minimum orderable quantity, that is not compatible with marketplaces modules'), $SKU, $order->AmazonOrderId), AmazonSupport::FUNCTION_IMPORT_INACTIVE_UNAVAILABLE);
                    unset($itemDetails[$SKU]);
                    continue;
                }

                $cart_management = $this->amazon_features['remote_cart'] && (!($FBA && $channel == Amazon::AFN) || $channel == Amazon::AFN  && $amazon_fba_decrease_stock) && $stock_management && AmazonRemoteCart::tableExists();

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p("FBA: ". ($FBA ? 'Yes' : 'No'));
                    CommonTools::p("Channel: ".$channel);
                    CommonTools::p("Remote Cart: ". (AmazonRemoteCart::tableExists() ? 'Yes' : 'No'));
                    CommonTools::p("cart management: ". ($cart_management ? 'Yes' : 'No'));
                    CommonTools::p("stock management: ". ($stock_management ? 'Yes' : 'No'));
                    CommonTools::p("log file: ".$log_filename);
                    CommonTools::p("pending order: ".($pending_order ? 'Yes' : 'No'));
                }

                // Handle Remote Cart - reserve product for pending orders on Amazon
                //
                if ($cart_management && $pending_order) {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p("case: cart_management & pending_order");
                    }

                    if (!AmazonRemoteCart::inCart($order_id, $SKU)) {
                        if ($log_filename) {
                            file_put_contents($log_filename, sprintf('%s - Adding product to remote cart - order: %s, sku: %s, quantity: %s, date: %s'.Amazon::LF, date('c'), $order_id, $SKU, $quantity, $order->PurchaseDate), FILE_APPEND);
                        }

                        AmazonRemoteCart::addCart($order_id, $SKU, $quantity, strtotime($order->PurchaseDate));

                        // Decrease stock
                        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                            StockAvailable::updateQuantity($id_product, $product->id_product_attribute ? $product->id_product_attribute : null, $quantity * -1, $id_shop);
                        } else {
                            $productQuantity = Product::getQuantity((int)$id_product, $product->id_product_attribute ? $product->id_product_attribute : null);
                            AmazonProduct::updateProductQuantity($id_product, $product->id_product_attribute ? $product->id_product_attribute : null, $productQuantity - $quantity);
                        }
                    } else {
                        if ($log_filename) {
                            file_put_contents($log_filename, sprintf('%s - Keep product in cart - order: %s, sku: %s, quantity: %s, date: %s'.Amazon::LF, date('c'), $order_id, $SKU, $quantity, $order->PurchaseDate), FILE_APPEND);
                        }

                        AmazonRemoteCart::updateCart($order_id, $SKU, $quantity, strtotime($order->PurchaseDate));
                    }
                    continue; // Important, we do not proceed the order as it is in pending state.
                } elseif ($cart_management) {
                    if (AmazonRemoteCart::inCart($order_id, $SKU)) {
                        AmazonRemoteCart::removeFromCart($order_id, $SKU);

                        if ($log_filename) {
                            file_put_contents($log_filename, sprintf('%s - Remove from cart - order: %s, sku: %s, quantity: %s, date: %s'.Amazon::LF, date('c'), $order_id, $SKU, $quantity, $order->PurchaseDate), FILE_APPEND);
                        }

                        // Restore stock
                        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                            StockAvailable::updateQuantity($id_product, $product->id_product_attribute ? $product->id_product_attribute : null, $quantity, $id_shop);
                        } else {
                            $productQuantity = Product::getQuantity((int)$id_product, $product->id_product_attribute ? $product->id_product_attribute : null);
                            AmazonProduct::updateProductQuantity($id_product, $product->id_product_attribute ? $product->id_product_attribute : null, $productQuantity + $quantity);
                        }
                    }
                }

                if ($pending_order) { // !!! Important, do not import pending orders !
                    continue;
                }

                if ($this->context->cart->id_currency != $id_currency_from && $convert_currency) {
                    $display_currency = sprintf('%s &gt; %s', $item->ItemPriceCurrency, $origin_currency);
                } else {
                    $display_currency = $item->ItemPriceCurrency;
                }
                AmazonImportOrder::$orders[$order_id]['products'][$i] = array();
                AmazonImportOrder::$orders[$order_id]['products'][$i]['SKU'] = $SKU;
                AmazonImportOrder::$orders[$order_id]['products'][$i]['ASIN'] = $item->ASIN;
                AmazonImportOrder::$orders[$order_id]['products'][$i]['OrderItemId'] = $order_item_id;
                AmazonImportOrder::$orders[$order_id]['products'][$i]['product'] = $product_name;
                AmazonImportOrder::$orders[$order_id]['products'][$i]['quantity'] = $quantity;
                AmazonImportOrder::$orders[$order_id]['products'][$i]['currency'] = $display_currency;
                AmazonImportOrder::$orders[$order_id]['products'][$i]['price'] = Tools::displayPrice($price * $quantity, $id_currency);
                AmazonImportOrder::$orders[$order_id]['products'][$i]['id_product'] = $product->id;
                AmazonImportOrder::$orders[$order_id]['products'][$i]['id_product_attribute'] = $product->id_product_attribute;

                // Two products but with the same reference !
                //
                if (isset($itemDetails[$SKU])) {
                    $itemDetails[$SKU]['qty'] += $quantity;
                    $itemDetails[$SKU]['shipping'] += $shipping_price;
                    $itemDetails[$SKU]['giftwrap'] += $giftwrap;
                    $itemDetails[$SKU]['amazon_item_tax'] += $item_tax;
                    $itemDetails[$SKU]['amazon_shipping_tax'] += $shipping_tax;
                    $itemDetails[$SKU]['amazon_giftwrap_tax'] += $giftwrap_tax;
                    $itemDetails[$SKU]['is_business'] = $is_business;
                } else {
                    $itemDetails[$SKU] = array(
                        'id' => $SKU,
                        'qty' => $quantity,
                        'price' => $price,
                        'name' => $product_name,
                        'dummy' => $auto_create_import,
                        'giftwrap' => $giftwrap,
                        'giftmsg' => $giftmsg,
                        'shipping' => $shipping_price,
                        'fees' => 0,
                        'amazon_has_tax' => ($item_tax || $shipping_tax || $giftwrap_tax) ? true : false,
                        'amazon_item_tax' => $item_tax,
                        'amazon_shipping_tax' => $shipping_tax,
                        'amazon_giftwrap_tax' => $giftwrap_tax,
                        'order_item_id' => $order_item_id,
                        'asin' => (string)$item->ASIN,
                        'europe' => $is_europe,
                        'is_business' => $is_business
                    );
                }

                $vat_is_applicable = ($is_business && $item_tax) || !$is_business;

                if ($useTaxes && $vat_is_applicable) {
                    if (method_exists('Tax', 'getProductTaxRate')) {
                        $product_tax_rate = (float)(Tax::getProductTaxRate($product->id, $shipping_address_id));
                    } else {
                        $product_tax_rate = (float)(Tax::getApplicableTax($product->id_tax, $product->tax_rate, $shipping_address_id));
                    }
                } else {
                    $product_tax_rate = 0;
                }

                $itemDetails[$SKU]['tax_rate'] = $vat_is_applicable ? $product_tax_rate : null;
                $itemDetails[$SKU]['id_tax'] = $vat_is_applicable && isset($product->id_tax) ? $product->id_tax : false;
                $itemDetails[$SKU]['id_tax_rules_group'] = $vat_is_applicable && isset($product->id_tax_rules_group) ? $product->id_tax_rules_group : (int)Product::getIdTaxRulesGroupMostUsed();
                $itemDetails[$SKU]['id_product'] = $product->id;
                $itemDetails[$SKU]['id_address_delivery'] = $shipping_address_id;
                $itemDetails[$SKU]['customization'] = $item->Customization;


                if (Amazon::$debug_mode) {
                    CommonTools::p("Order Info:");
                    CommonTools::p(sprintf('%s - %s::%s - line #%d - itemDetails', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p($itemDetails[$SKU]);
                }

                // Product Combinations
                //
                if ($handle_combinations && version_compare(_PS_VERSION_, '1.5', '<')) {
                    $combinations = $product->getAttributeCombinaisons($id_lang);
                } else {
                    $combinations = $product->getAttributeCombinations($id_lang);
                }

                if (Amazon::$debug_mode) {
                    CommonTools::p("Combinations:");
                    CommonTools::p(sprintf('%s - %s::%s - line #%d - %s', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__, print_r($combinations, true)));
                    CommonTools::p($itemDetails[$SKU]);
                }
                $id_product_attribute = 0;
                $minimal_quantity = $product->minimal_quantity;

                if ($combinations) {
                    foreach ($combinations as $combination) {
                        if (trim($combination['reference']) == $SKU) {
                            $id_product_attribute = (int)$combination['id_product_attribute'];
                            $itemDetails[$SKU]['id_product_attribute'] = $id_product_attribute;
                            $minimal_quantity = $combination['minimal_quantity'];
                            break;
                        }
                    }
                }

                if ($minimal_quantity > 1) {
                    AmazonImportOrder::$errors[] = AmazonSupport::message(sprintf('%s - %s (%d/%d)', $this->l('Couldn\'t import a product with a minimal quantity greater than 1'), $order_id, $id_product, $id_product_attribute), AmazonSupport::FUNCTION_IMPORT_INACTIVE_UNAVAILABLE);
                    unset($itemDetails[$SKU]);
                    continue;
                }

                if (!$stock_management) {
                    $productQuantity = $quantity;
                } else {
                    if (version_compare(_PS_VERSION_, '1.5', '<')) {
                        $productQuantity = Product::getQuantity((int)$id_product, $id_product_attribute ? $id_product_attribute : null);
                    } else {
                        $productQuantity = Product::getRealQuantity($id_product, $id_product_attribute ? $id_product_attribute : null, $id_warehouse, $id_shop);
                    }
                }

                $restock = false;

                if ($stock_management) { // removed on 2018-01-05 : && $channel != Amazon::AFN
                    $force_import = !max(0, $productQuantity) && Product::isAvailableWhenOutOfStock($product->out_of_stock);
                } else {
                    $force_import = true;
                }

                if (Amazon::$debug_mode) {
                    CommonTools::p("Stock issues:");
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p("stock_management: $stock_management");
                    CommonTools::p("isavailablewhenoutofstock: ".Product::isAvailableWhenOutOfStock($product->out_of_stock));
                    CommonTools::p("out_of_stock rule: $product->out_of_stock");
                    CommonTools::p("force_import: $force_import");
                    CommonTools::p("order quantity: $quantity");
                    CommonTools::p("stock quantity: $productQuantity");
                    CommonTools::p("channel: $channel");
                    CommonTools::p("FBA: $FBA");
                    CommonTools::p("dummy: $auto_create_import");
                }

                if ($stock_management) {
                    if ($auto_create_import) {
                        //
                        $restock = true;
                    } elseif ($FBA && $channel == Amazon::AFN && !$force_import && $productQuantity - $quantity < 0) {
                        AmazonImportOrder::$errors[] = sprintf('%s ASIN: %s SKU: %s Order: #%s', $this->l('Not enough stock to import this product'), $item->ASIN, $SKU, $order_id);
                        unset($itemDetails[$SKU]);
                        continue;
                    } elseif ($FBA && $channel == Amazon::AFN) {
                        // In case of FBA restock in all cases
                        if (!$amazon_fba_decrease_stock || $force_import) {
                            $restock = true;
                        }
                    } elseif (!$force_import && $productQuantity - $quantity < 0) {
                        AmazonImportOrder::$errors[] = sprintf('%s ASIN: %s SKU: %s Order: #%s', $this->l('Not enough stock to import this product'), $item->ASIN, $SKU, $order_id);
                        unset($itemDetails[$SKU]);
                        continue;
                    } elseif ($productQuantity - $quantity < 0) {
                        //
                        $restock = true;
                    }

                    if (Amazon::$debug_mode) {
                        CommonTools::p("Cart:");
                        CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p('restock: '.($restock ? 'true' : 'false'));
                    }

                    if ($restock) {
                        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                            StockAvailable::updateQuantity($id_product, $id_product_attribute ? $id_product_attribute : null, $quantity, $id_shop);
                        } else {
                            AmazonProduct::updateProductQuantity($id_product, $id_product_attribute ? $id_product_attribute : null, $productQuantity + $quantity);
                        }
                    }
                }

                // is a preorder, is an unavailable product, order states for preorder is configured
                if ($preorder && version_compare(_PS_VERSION_, '1.5', '>=') && Validate::isDate($product->available_date) && is_array($order_states) && isset($order_states[Amazon::ORDER_STATE_PREORDER]) && (int)$order_states[Amazon::ORDER_STATE_PREORDER]) {
                    $dateNow = time();
                    $psDateRestock = strtotime($product->available_date);
                    $dateOrder = strtotime($order->PurchaseDate);

                    if ($psDateRestock && $psDateRestock > $dateNow && $psDateRestock > $dateOrder) {
                        $preorder_switch = true;
                    }
                }

                if ($cart->updateQty($quantity, $id_product, $id_product_attribute) < 0) {
                    AmazonImportOrder::$errors[] = $this->l('Couldn\'t update cart quantity: not enough stock ?').' (ASIN:'.$item->ASIN.' SKU:'.$SKU.' Order: #'.$order->AmazonOrderId.')';
                    unset($itemDetails[$SKU]);
                    continue;
                }

                if (Amazon::$debug_mode) {
                    CommonTools::p("Cart:");
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(get_object_vars($cart));
                }

                $i++;

                $totalSaleableQuantity += (int)$item->QuantityOrdered;
            }

            if ($pending_order) { // order in cart on amazon side, do not import
                continue;
            }

            if ($totalQuantity != $totalSaleableQuantity) {
                AmazonImportOrder::$errors[] = $this->l('Skipping Order: Product count mismatch').' ('.$order->AmazonOrderId.')';

                if (Validate::isLoadedObject($cart)) {
                    $cart->delete();
                }

                continue;
            }

            if (!count($itemDetails)) {
                AmazonImportOrder::$errors[] = $this->l('Skipping Order: No products for this order').' ('.$order->AmazonOrderId.')';

                if (Validate::isLoadedObject($cart)) {
                    $cart->delete();
                }

                continue;
            }

            // Using price, shipping details etc... from the Market Place
            //
            $cart->amazonProducts = $itemDetails;
            $cart->amazonShipping += $total_shipping;
            $cart->amazonChannel = $channel;
            $cart->tax_for_fba = $this->taxForFBA($sales_channel, $channel, $id_lang);

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p($itemDetails);
                CommonTools::p("Cart:");
                CommonTools::p(get_object_vars($cart));
            }

            // Payment Title
            //
            if ($paymentRegion) {
                $paymentTitle = trim(sprintf('%s %s', Amazon::PAYMENT_METHOD_TITLE, $region));
            } else {
                $paymentTitle = Amazon::PAYMENT_METHOD_TITLE;
            }

            // duplication du panier, important !!!
            //
            $acart = $cart;

            if (($validation_message = $acart->validateFields(false, true)) !== true) {
                AmazonImportOrder::$errors[] = sprintf('%s#%d: '.'Field Validation failed for this cart (Order: %s) - Reason: %s', basename(__FILE__), __LINE__, $order->AmazonOrderId, $validation_message);

                if (Validate::isLoadedObject($acart)) {
                    $acart->delete();
                }
                continue;
            }

            $cart_result = $acart->getProducts(false, $id_product);

            if (!is_array($cart_result) || !count($cart_result)) {
                AmazonImportOrder::$errors[] = sprintf('%s#%d: '.$this->l('Cart validation failed for order: %s, product: %s - please type to purcharse this product on the front-office'), basename(__FILE__), __LINE__, $order->AmazonOrderId, $SKU);

                if (Validate::isLoadedObject($acart)) {
                    $acart->delete();
                }
                continue;
            }

            $payment = new AmazonPaymentModule();

            // PreOrder : switch to preorder configured order state
            //
            if ($preorder_switch) {
                $id_order_state = $order_states[Amazon::ORDER_STATE_PREORDER];
            } else {
                if ($is_prime && is_array($order_states) && isset($order_states[Amazon::ORDER_STATE_PRIMEORDER])) {
                    $id_order_state = $order_states[Amazon::ORDER_STATE_PRIMEORDER];
                } else {
                    $id_order_state = $id_order_states[$channel];
                }
            }

            if (empty($id_order_state) || !is_numeric($id_order_state)) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p("id_order_state: $id_order_state");
                    CommonTools::p("preorder_switch: $preorder_switch");
                    CommonTools::p("is_prime: $is_prime");
                    CommonTools::p($order_states);
                }

                AmazonImportOrder::$errors[] = AmazonSupport::message($this->l('Please configure order statuses in module configuration'), AmazonSupport::FUNCTION_IMPORT_ORDER_STATUS);

                if (Validate::isLoadedObject($cart)) {
                    $cart->delete();
                }

                continue;
            }

            if (!$i) {
                AmazonImportOrder::$errors[] = $this->l('Skipping Order: No products for this order').' ('.$order->AmazonOrderId.')';

                if (Validate::isLoadedObject($cart)) {
                    $cart->delete();
                }

                continue;
            } elseif (($newOrderId = $payment->validateMarketplaceOrder($cart->id, $id_order_state, $paymentTitle, $order_id, $mpStatusId, $acart, $useTaxes, $opensi ? null : $date_add))) {
                if ($tokenOrders) {
                    $url = '?tab=AdminOrders&id_order='.$newOrderId.'&vieworder&token='.$tokenOrders;
                    $order_link = html_entity_decode('&lt;a href="'.$url.'" title="" target="_blank" &gt;'.$order->AmazonOrderId.' ('.$newOrderId.')&lt;/a&gt;');
                    AmazonImportOrder::$orders[$order_id]['link'] = $order_link;
                }

                AmazonImportOrder::$orders[$order_id]['status'] = true;
                AmazonImportOrder::$orders[$order_id]['merchant_order_id'] = $newOrderId;

                $import_count++;
            } else {
                AmazonImportOrder::$errors[] = $this->l('Error while importing this order ID').': '.$order->AmazonOrderId;

                if (Validate::isLoadedObject($cart)) {
                    $cart->delete();
                }
            }
        }

        // Save Session
        $batches = new AmazonBatches('batch_acknowledge');
        $batch = new AmazonBatch($timestart);
        $batch->id = uniqid();
        $batch->timestop = time();
        $batch->type = $this->l('Cron');
        $batch->region = $main_region;
        $batch->created = $import_count;
        $batch->updated = 0;
        $batch->deleted = 0;
        $batches->add($batch);
        $batches->save();

        // Acknowledge
        if (is_array(AmazonImportOrder::$orders) && count(AmazonImportOrder::$orders) && $import_count) {
            $submissionFeedId = null;

            if ($this->amazon_features['demo_mode'] || isset($_SERVER['DropBox'])) {
                $submissionFeedId  = '123456789';
            } else {
                $submissionFeedId = $this->_amazonApi->acknowledgeOrders(AmazonImportOrder::$orders);
            }

            if ($submissionFeedId && $this->amazon_features['expert_mode'] && !$this->amazon_features['demo_mode']) {
                $batches = new AmazonBatches('batch_acknowledge');
                $batch = new AmazonBatch($timestart);
                $batch->id = $submissionFeedId;
                $batch->timestop = time();
                $batch->type = 'Acknowledge (Orders)';
                $batch->region = $main_region;
                $batch->created = 0;
                $batch->updated = $import_count;
                $batch->deleted = 0;
                $batches->add($batch);
                $batches->save();
            }
        }

        // Remote Cart - cleanup
        if ($this->amazon_features['remote_cart'] && $stock_management && AmazonRemoteCart::tableExists()) {
            $expireds = AmazonRemoteCart::expiredCarts();

            if (is_array($expireds) && count($expireds)) {
                foreach ($expireds as $expired) {
                    $mp_order_id = $expired['mp_order_id'];
                    $sku = $expired['reference'];
                    $quantity = $expired['quantity'];

                    $product = new AmazonProduct($sku, false, $id_lang, 'reference', $id_shop);

                    if (!Validate::isLoadedObject($product)) {
                        continue;
                    }

                    // Restore stock
                    if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                        StockAvailable::updateQuantity($product->id, $product->id_product_attribute ? $product->id_product_attribute : null, $quantity, $id_shop);
                    } else {
                        $productQuantity = Product::getQuantity($product->id, $product->id_product_attribute ? $product->id_product_attribute : null);
                        AmazonProduct::updateProductQuantity($product->id, $product->id_product_attribute ? $product->id_product_attribute : null, $productQuantity + $quantity);
                    }

                    if ($log_filename) {
                        file_put_contents($log_filename, sprintf('%s - Remove expired product from cart  - order: %s, sku: %s, quantity: %s'.Amazon::LF, date('c'), $mp_order_id, $sku, $quantity), FILE_APPEND);
                    }

                    AmazonRemoteCart::removeFromCart($mp_order_id, $sku);
                }
            }
        }
    }

    public function logInit($region)
    {
        $action = 'import';
        $output_dir = _PS_MODULE_DIR_.'/amazon/log/';
        $log = $output_dir.date('Ymd_His').'.'.$action.'-'.$region.'.log';

        if (!is_dir($output_dir)) {
            mkdir($output_dir);
        }
        if (is_dir($output_dir) && AmazonTools::isDirWriteable($output_dir)) {
            $files = glob($output_dir.'*.'.$action.'-'.$region.'.log');

            if (is_array($files) && count($files)) {
                foreach ($files as $key => $file) {
                    if (filemtime($file) < time() - (86400 * 3)) {
                        unlink($file);
                    }
                }
            }
            return($log);
        }
        return(null);
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }

    /**
     * Check if order is FBA, and destination is different from Amazon_master
     * @param $sales_channel
     * @param $fulfilment_channel
     * @param int $id_lang
     * @return object
     */
    private function taxForFBA($sales_channel, $fulfilment_channel, $id_lang)
    {
        $result                 = new stdClass();
        $result->active         = false;
        $result->id_tax_rule    = null;
        $result->id_country     = null;

        // 1. Check if this order is special
        // If order is Amazon Fulfillment
        if ($fulfilment_channel != Amazon::AFN) {
            return $result;                 // Order is not FBA
        }

        // Check if sales channel is different from MASTER marketplace
        $master_mp = strtolower(AmazonConfiguration::get(self::CONFIG_MASTER));
        if (strpos($master_mp, $sales_channel) !== false) {
            $sales_channel_segment = explode('.', $sales_channel);
            if (count($sales_channel_segment) === 2) {
                $marketplace = trim(strtolower($sales_channel_segment[1]));
                if ($marketplace == $master_mp) {
                    return $result;           // Destination is the same with Amazon_master
                }
            }
        }

        // 2. For custom tax calculation, need (1) override tax rule and (2) country to apply tax rule
        if (!$this->_default_tax_rule) {
            $this->_default_tax_rule = AmazonConfiguration::get(Amazon::CONFIG_DEFAULT_TAX_RULE_FOR_MP);
        }
        if (!$this->_marketplaces) {
            $this->_marketplaces = AmazonTools::languages();
        }

        $id_tax_rule = $id_country = null;
        if (isset($this->_default_tax_rule[$id_lang])) {
            $id_tax_rule = $this->_default_tax_rule[$id_lang];
        }
        if (isset($this->_marketplaces[$id_lang])) {
            $country_iso = $this->_marketplaces[$id_lang]['country_iso_code'];
            $id_country  = Country::getByIso($country_iso);
        }

        if (isset($id_tax_rule, $id_country)) {
            $result->active         = true;
            $result->id_tax_rule    = $id_tax_rule;
            $result->id_country     = $id_country;
        }

        return $result;
    }
}

$amazonImportOrder = new AmazonImportOrder();
$amazonImportOrder->dispatch('');
