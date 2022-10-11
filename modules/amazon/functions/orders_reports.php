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
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.orders_reports.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.support.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.remote_cart.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.batch.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.stat.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.address.class.php');

class AmazonOrdersReport extends Amazon
{
    const ACTION_CHECK               = 'check';
    const ACTION_ACTIVATE            = 'activate';
    const ACTION_ERROR               = 'error';
    const ACTION_REPORT_REQUEST      = 'report-request';
    const ACTION_REPORT_REQUEST_LIST = 'report-request-list';
    const ACTION_REPORT_GET          = 'report-get';
    const ACTION_REPORT_PARSE        = 'report-parse';
    const ACTION_REPORT              = 'report';

    /** @var AmazonOrdersReports */
    public $ws             = null;
    public $marketplaceId  = null;
    public $region         = null;
    public $merchantId     = null;
    public $amazon_id_lang = null;
    public $europe         = null;

    public static $errors       = array();
    public static $warnings     = array();
    public static $messages     = array();
    public static $next_action  = array();




    public static function jsonDisplayExit()
    {
        $output = null;
        $data = array(
            'error' => (count(AmazonOrdersReport::$errors) ? true : false),
            'errors' => AmazonOrdersReport::$errors,
            'message' => (count(self::$messages) ? true : false),
            'messages' => self::$messages,
            'output' => $output,
            'next_action' => self::$next_action
        );

        $json = Tools::jsonEncode($data);

        if (Amazon::$debug_mode) {
            die;
        } elseif ($callback = Tools::getValue('callback')) {
            // jquery

            echo (string)$callback.'('.$json.')';
            die;
        } else {
            // cron

            echo $json;
            die;
        }
    }

    public function dispatch()
    {
        register_shutdown_function(array('AmazonOrdersReport', 'jsonDisplayExit'));

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        if (! $this->init()) {
            return false;
        }

        $action = Tools::getValue('action');
        if ($action == self::ACTION_REPORT) {
            $this->manageReports();
            $this->processPreviousOrders();
            return true;
        } else {
            return false;
        }
    }


    public function init()
    {
        $tokens = Tools::getValue('amazon_token');

        $id_lang = (int)Tools::getValue('amazon_lang');


        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');
        $marketLang2Region = array_flip($marketPlaceRegion);

        if ((int)Tools::getValue('europe')) {
            $masterMarketplace = AmazonConfiguration::get('MASTER');

            if (isset($marketLang2Region[$masterMarketplace]) && $marketLang2Region[$masterMarketplace]) {
                $id_lang = $marketLang2Region[$masterMarketplace];
            } else {
                $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
            }

            $this->europe = 1;
        } else {
            $this->europe = 0;
        }

        if (!is_array($marketPlaceRegion) || !count($marketPlaceRegion)) {
            self::$errors[] = $error = $this->l('Module is not configured yet');

            $this->_printDebug(sprintf('%s(#%d): init(): Failed - %s', basename(__FILE__), __LINE__, $error));
            die;
        }

        if (!isset($marketPlaceRegion[$id_lang]) || !$marketPlaceRegion[$id_lang]) {
            self::$errors[] = $error =  $this->l('No selected language, nothing to do...');

            $this->_printDebug(sprintf('%s(#%d): init(): Failed - %s', basename(__FILE__), __LINE__, $error));
            die;
        }


        if (!AmazonTools::checkToken($tokens)) {
            self::$errors[] = $error = $this->l('Wrong Token');

            $this->_printDebug(sprintf('%s(#%d): init(): Failed - %s', basename(__FILE__), __LINE__, $error));
            die;
        }

        if ($this->europe) {
            $amazon = AmazonTools::selectEuropeanPlatforms(Amazon::$debug_mode);
        } else {
            $amazon = AmazonTools::selectPlatforms($id_lang, Amazon::$debug_mode);
        }

        $this->marketplaceId = trim($marketPlaceIds[$id_lang]);
        $this->region = trim($amazon['params']['Country']);
        $this->merchantId = trim($amazon['auth']['MerchantID']);

        if (Amazon::$debug_mode) {
            printf('Webservice Params: %s'.Amazon::LF, nl2br(print_r($amazon, true)));
        }
        $this->ws = new AmazonOrdersReports($amazon['auth'], $amazon['params'], null, Amazon::$debug_mode);

        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        if (!isset($marketPlaceIds[$id_lang]) || !$marketPlaceIds[$id_lang]) {
            $lang = new Language($id_lang);

            self::$errors[] = $error = sprintf('%s "%s"', $this->l('Marketplace is not yet configured for'), $lang->name);

            $this->_printDebug(sprintf('%s(#%d): %s', basename(__FILE__), __LINE__, $error));
            die;
        }
        return(true);
    }

    /**
     * Get reports and import to db
     */
    protected function manageReports()
    {
        if (! $this->amazon_features['orders_reports']) {
            return;
        }

        $this->_printDebug(sprintf('%s(#%d): - Order Report Request:', basename(__FILE__), __LINE__));

        // Get start time to get order report
        $batches = new AmazonBatches(AmazonBatches::ORDER_REPORT);
        $last_import = $batches->getLastForRegion($this->region);
        if ($last_import) {
            $last_import = gmdate('c', strtotime($last_import. ' -5 min'));
        } else {
            $last_import = gmdate('c', strtotime('first day of this month midnight'));
        }

        $request_list_order      = $this->ws->reportList($last_import, AmazonOrdersReports::TYPE_ORDER_REPORT);
        $request_list_settlement = $this->ws->reportList(/*gmdate('c', strtotime('first day of previous month'))*/null, AmazonOrdersReports::TYPE_SETTLEMENT_REPORT);
        $request_list_tax        = $this->ws->reportList(null, AmazonOrdersReports::TYPE_VAT_REPORT);

        $this->_printDebug(sprintf('%s(#%d): - Order Report Request List: %s', basename(__FILE__), __LINE__, print_r($request_list_order, true)));
        $this->_printDebug(sprintf('%s(#%d): - Order Settlement Report Request List: %s', basename(__FILE__), __LINE__, print_r($request_list_settlement, true)));
        $this->_printDebug(sprintf('%s(#%d): - Tax Report Request List: %s', basename(__FILE__), __LINE__, print_r($request_list_tax, true)));

        // Schedule order report if there are no orders
        if (!is_array($request_list_order) || !count($request_list_order)) {
            $this->scheduleOrderReport($last_import);
        }

        // Process report
        $this->processReports($request_list_order);
        $this->processReports($request_list_settlement);
        $this->processReports($request_list_tax);
    }

    /**
     * @param $request_list
     * @throws PrestaShopDatabaseException
     */
    protected function processReports($request_list)
    {
        if (!is_array($request_list)) {
            $this->_printDebug($this->l('Report list not well format'));
            printf('request_list: %s', print_r($request_list, true));
            return;
        }

        foreach ($request_list as $requested_report) {
            $report_id   = (string)$requested_report->ReportId;
            $report_type = $requested_report->ReportType;

            if (!$report_id) {
                $this->_printDebug(sprintf('%s(#%d): - Order Report: Report not yet available', basename(__FILE__), __LINE__));
            } else {
                $report_data = $this->ws->getOrdersReport($report_id, $report_type);
                $this->_printDebug(array(
                    str_repeat('-', 160).Amazon::LF,
                    "Report Id: $report_id".Amazon::LF,
                    $report_data
                ));

                if (Tools::strlen($report_data)) {
                    if (AmazonOrdersReports::TYPE_ORDER_REPORT == $report_type || AmazonOrdersReports::TYPE_SETTLEMENT_REPORT == $report_type) {
                        $this->processReportAddress($report_data);
                    }
                    $this->saveOrderStat($report_data, $report_type);

                    $this->ws->ackReport($report_id);
                }
            }
        }
    }

    /**
     * Store to marketplace_order_address
     * @param $result
     *
     * @return bool
     */
    protected function processReportAddress($result)
    {
        if ($result == null or empty($result)) {
            AmazonListOrder::$errors[] = sprintf('%s(#%d): processReport - Report is empty !', basename(__FILE__), __LINE__);
            return (false);
        }

        if (strstr($result, html_entity_decode('&lt;Error%gt;')) !== false) {
            AmazonListOrder::$errors[] = sprintf('%s(#%d): processReport - Error: %s', basename(__FILE__), __LINE__, print_r($result));
            return (false);
        }

        $lines = explode(Amazon::LF, utf8_encode($result));

        if (!is_array($lines) || !count($lines)) {
            AmazonListOrder::$errors[] = sprintf('%s(#%d): processReport - Report is empty !', basename(__FILE__), __LINE__);
            return (false);
        }

        $this->_printDebug(array(
            str_repeat('-', 160).Amazon::LF,
            sprintf('Orders: %s lines', count($lines))
        ));

        $header = reset($lines);

        $this->_printDebug(array(
            str_repeat('-', 160).Amazon::LF,
            sprintf('Header: %s', nl2br(print_r(explode("\t", $header), true)))
        ));

        $amazon_orders = array();
        $count = 0;
        $orders_fields_count = count(AmazonAddress::$orders_fields);

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }
            if ($count++ < 1) {
                continue;
            }

            $order = array();
            $result = explode("\t", $line);

            if (count(array_keys($result)) < $orders_fields_count) {
                continue;
            }
            $mp_order_id = $result[0];

            foreach (AmazonAddress::$orders_fields as $key => $order_field) {
                if (array_key_exists($order_field, AmazonAddress::$report_mapping)) {
                    $order[$order_field] = $result[$key];
                }
            }
            $amazon_orders[$mp_order_id] = $order;

            $this->_printDebug(array(
                str_repeat('-', 160),
                sprintf('Order: %s', print_r($order, true))
            ));
        }

        if (is_array($amazon_orders) && count($amazon_orders)) {
            // Cosmetic
            $mappings = array();
            $db_values = array();

            foreach (AmazonAddress::$report_mapping as $key => $mapping) {
                $mappings[$key] = pSQL($mapping);
            }
            $fields = implode('`, `', $mappings);

            foreach ($amazon_orders as $mp_order_id => $order) {
                foreach ($order as $key => $value) {
                    if (strstr($key, 'date')) {
                        $db_values[$key] = pSQL(date('Y-m-d H:i:s', strtotime($value)));
                    } else {
                        $db_values[$key] = pSQL($value);
                    }
                }
                $values = implode('", "', $db_values);
                $sql = sprintf('REPLACE INTO `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ADDRESS.'` (`%s`) values("%s");', $fields, $values);

                $this->_printDebug(array(
                    str_repeat('-', 160),
                    sprintf('SQL: %s', print_r($sql, true))
                ));

                Db::getInstance()->execute($sql);
            }
        }

        return true;
    }

    /**
     * Save order stat to marketplace_stats table
     * @param $result
     * @param $type
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function saveOrderStat($result, $type)
    {
        // Parse order data
        $report = $this->ws->parseReport($result, $type);
        if (AmazonOrdersReports::TYPE_ORDER_REPORT == $type) {
            $report = $this->parseOrderData($report);
            $mappings = AmazonStat::$order_mapping;
        } elseif (AmazonOrdersReports::TYPE_SETTLEMENT_REPORT == $type) {
            $report = $this->parseSettleData($report);
            $mappings = AmazonStat::$settle_mapping;
        } elseif (AmazonOrdersReports::TYPE_VAT_REPORT == $type) {
            $report = $this->parseVatReport($report);
            $mappings = AmazonStat::$tax_mapping;
        } else {
            return;
        }

        $this->_printDebug(array(
            str_repeat('-', 160).Amazon::LF,
            "After parse report data, type = $type".Amazon::LF,
            $report
        ));

        // Update marketplace_order_stat
        foreach ($report as $mp_order_id => $order) {
            $update = array();
            foreach ($mappings as $order_key => $db_field) {
                if (isset($order[$order_key])) {
                    $update[$db_field] = $order[$order_key];
                }
            }
            AmazonStat::updateOrder($update, $type);
        }
    }

    /*
     * parse data from Order Report
     * - convert datetime iso 8601 to format 'Y-m-d H:i:s'
     * - group multiple purchased product to one order id + calculate total price
     * - statistic
     *
     * @param array $data
     * @return array
     */
    protected function parseOrderData($data)
    {
        $processedData = array();
        $formatDateTime = 'Y-m-d h:i:s';

        // field need to be unset when grouping product to one order record, and others
        $unsetFields = array(
            // Product info
            'order_item_id',
            'item_price',
            'item_tax',
            'product_name',
            'sku',
            'quantity_purchased',
            'customized_url',
            'customized_page',

            // Shipping info already store in other table
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
        );

        foreach ($data as $key => $itemInfo) {
            $orderAmazonId = $itemInfo['order_id'];

            //convert datetime
            $itemInfo['purchase_date'] = date($formatDateTime, strtotime($itemInfo['purchase_date']));
            $itemInfo['payments_date'] = date($formatDateTime, strtotime($itemInfo['payments_date']));

            //group order id, count total_price and shipping_price
            if (!isset($processedData[$orderAmazonId])) {
                $orderInfo = $itemInfo;
                $orderInfo['shipping_price'] = $itemInfo['shipping_price'];
                $orderInfo['shipping_tax']   = $itemInfo['shipping_tax'];
                $orderInfo['total_price'] = $itemInfo['item_price'];
                $orderInfo['total_tax']   = $itemInfo['item_tax'];
            } else {
                $orderInfo = $processedData[$orderAmazonId];
                // Ignore shipping price
                $orderInfo['shipping_price'] += $itemInfo['shipping_price'];
                $orderInfo['shipping_tax']   += $itemInfo['shipping_tax'];
                $orderInfo['total_price'] += $itemInfo['item_price'];
                $orderInfo['total_tax']   += $itemInfo['item_tax'];
            }

            //retrieve product info
            $productInfo = array();
            foreach ($unsetFields as $unsetField) {
                if (isset($itemInfo[$unsetField])) {
                    $productInfo[$unsetField] = $itemInfo[$unsetField];
                    unset($orderInfo[$unsetField]);
                }
            }

            // Ignore product info

            //currency
            if (isset($orderInfo['currency'])) {
                $id_currency = $this->_getCurrencyByCode($orderInfo['currency']);
                if ($id_currency) {
                    $orderInfo['id_currency'] = $id_currency;
                }
            }

            $processedData[$orderAmazonId] = $orderInfo;
        }

        return $processedData;
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected function parseSettleData($data)
    {
        $processedSettleData = array();

        foreach ($data as $key => $settleInfo) {
            $orderId = $settleInfo['order_id'];
            $orderItemId = $settleInfo['order_item_code'];

            if ($settleInfo['total_amount']) {
                continue;
            }

            if (!isset($processedSettleData[$orderId])) {
                $processedSettleData[$orderId] = array(
                    'order_id'          => $orderId,
                    'shipment_id'       => $settleInfo['shipment_id'],
                    'marketplace_name'  => $settleInfo['marketplace_name'],
                    'posted_date'       => $settleInfo['posted_date'],
                    'commissions'       => 0,
                    'items'             => array(),
                );
            }

            if ($settleInfo['price_type'] == 'Principal' && $settleInfo['price_amount']) {
                $processedSettleData[$orderId]['total_price'] = $settleInfo['price_amount'];
            }

            if ($settleInfo['price_type'] == 'Shipping' && $settleInfo['price_amount']) {
                $processedSettleData[$orderId]['shipping_price'] = $settleInfo['price_amount'];
            }

            if ($settleInfo['item_related_fee_type'] == 'Commission' && $settleInfo['item_related_fee_amount']) {
                $commission = $settleInfo['item_related_fee_amount'];
                $processedSettleData[$orderId]['items'][$orderItemId]['item_commission'] = $commission;
                $processedSettleData[$orderId]['commissions'] += $commission;
            }

            if ($settleInfo['item_related_fee_type'] == 'ShippingHB' && $settleInfo['item_related_fee_amount']) {
                $processedSettleData[$orderId]['items'][$orderItemId]['item_fee_shipping'] = $settleInfo['item_related_fee_amount'];
            }
        }

        return $processedSettleData;
    }

    /**
     * Get tax data
     * @param $data
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function parseVatReport($data)
    {
        $processedVatData = array();

        foreach ($data as $taxInfo) {
            $orderId = $taxInfo['order_id'];

            if ($orderId && !isset($processedVatData[$orderId])) {
                // New order
                $processedVatData[$orderId] = $taxInfo;
                if (isset($taxInfo['currency'])) {
                    $idCurrency = $this->_getCurrencyByCode($taxInfo['currency']);
                    if ($idCurrency) {
                        $processedVatData[$orderId]['id_currency'] = $idCurrency;
                    }
                }
            } else {
                if (isset($processedVatData[$orderId])) {
                    // Append to exist order
                    $processOrderId = $orderId;
                } else {
                    // Append to previous order
                    end($processedVatData);
                    $processOrderId = key($processedVatData);
                    if (!$processOrderId) {
                        continue;
                    }
                    reset($processedVatData);
                }

                $sum_fields = array(
                    'display_price',
                    'taxexclusive_selling_price',
                    'total_tax',
                    'display_promo_amount',
                    'display_promo_tax_inclusive'
                );
                foreach ($sum_fields as $sum_field) {
                    if (isset($taxInfo[$sum_field])) {
                        $processedVatData[$processOrderId][$sum_field] += $taxInfo[$sum_field];
                    }
                }
            }
        }

        return $processedVatData;
    }

    /**
     * Schedule to generate an order report request
     * @param int $last_import
     */
    protected function scheduleOrderReport($last_import)
    {
        if (strtotime($last_import) < (time() - AmazonOrdersReports::LAG)) {
            // Request a report for the period
            $date_max = Db::getInstance()->getValue('SELECT MAX(`date`) FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ADDRESS.'`');
            if (!Tools::strlen($date_max)) {
                $date_max = gmdate('c', strtotime('yesterday'));
            }

            $report_request_id = $this->ws->reportRequest($date_max);

            if (!$report_request_id) {
                self::$errors[] = sprintf('%s (%d/%s) - %d', $this->l('Failed to request a report'), __LINE__, 'Report Request', $report_request_id);
                die;
            } else {
                $batches = new AmazonBatches(AmazonBatches::ORDER_REPORT);
                $batch = new AmazonBatch(time());
                $batch->id = $report_request_id;
                $batch->timestop = time();
                $batch->type = "Report (Orders)";
                $batch->region = $this->region;
                $batch->created = 0;
                $batch->updated = 0;
                $batch->deleted = 0;
                $batches->add($batch);
                $batches->save();

                printf('%s... (%d)', $this->l('An order report has been requested, please wait a while'), $report_request_id);
            }
        }
    }

    /**
     * Print debug message
     * @param $messages
     */
    private function _printDebug($messages)
    {
        if (Amazon::$debug_mode) {
            if (! is_array($messages)) {
                $messages = array($messages);
            }
            CommonTools::p($messages);
        }
    }

    private function processPreviousOrders()
    {
        $date_min = Db::getInstance()->getValue('SELECT MAX(`date`) FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ADDRESS.'`');

        if (!Tools::strlen($date_min)) {
            $date_min = date('Y-m-d 00:00:00', strtotime('yesterday'));
        } else {
            $date_min = date('Y-m-d 00:00:00', strtotime($date_min));
        }

        $mp_orders = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ADDRESS.'` WHERE date >= "'.pSQL($date_min).'"');

        if (Amazon::$debug_mode) {
            $this->_printDebug(sprintf('%s(#%d): - Orders: %s', basename(__FILE__), __LINE__, print_r($mp_orders, true)));
        }

        if (is_array($mp_orders) && count($mp_orders)) {
            foreach ($mp_orders as $mp_order) {
                $mp_order_id = $mp_order['mp_order_id'];

                $order_id = AmazonOrder::checkByMpId($mp_order_id);

                if (!(int)$order_id) {
                    continue;
                }

                $order = new Order($order_id);

                $marketplace_addresses = AmazonAddress::getAmazonBillingAddress($mp_order_id);

                if (isset($marketplace_addresses->shipping_address) && $marketplace_addresses->shipping_address instanceof AmazonWsAddress) {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p("Delivery Address:");
                        CommonTools::p(get_object_vars($marketplace_addresses->shipping_address));
                    }
                } else {
                    continue;
                }
                $shipping_address = new AmazonAddress();
                $shipping_address->id_customer = $order->id_customer;
                $shipping_address_id = $shipping_address->lookupOrCreateamazonAddress($order->id_lang, $marketplace_addresses->shipping_address);

                if ($shipping_address_id != $order->id_address_delivery) {
                    $order->id_address_delivery = (int)$shipping_address_id;
                    $order->update();

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s - line #%d - Delivery Address updated: %d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__, $shipping_address_id));
                    }
                }
            }
        }
    }

    /**
     * Get currency id from code
     * @param string $currency_code
     * @return bool|int
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function _getCurrencyByCode($currency_code)
    {
        $currency = Currency::getIdByIsoCode($currency_code);

        if (is_int($currency)) {
            $currency = new Currency($currency);
        }

        if ($currency) {
            return $currency->id;
        } else {
            // July-02-2018: If currency not found, just ignore it
            $this->_printDebug($this->l('Currency not found in shop: '.$currency_code));
            return false;
        }
    }
}

$amazonOrdersReport = new AmazonOrdersReport();
$amazonOrdersReport->dispatch();
