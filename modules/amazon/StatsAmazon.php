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

if (version_compare(_PS_VERSION_, '1.5', '<') && defined('PS_ADMIN_DIR') && file_exists(PS_ADMIN_DIR.'/../classes/AdminTab.php')) {
    include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
}

require_once(dirname(__FILE__).'/amazon.php');
require_once(dirname(__FILE__).'/classes/amazon.tools.class.php');
require_once(dirname(__FILE__).'/classes/amazon.support.class.php');
require_once(dirname(__FILE__).'/classes/amazon.orders_reports.class.php');

class StatsAmazon extends AdminTab
{
    public $name = 'amazon';
    public $id_lang;
    private $amazon = null;

    // Backward compatibility for 1.4 - Copy from AdminCOntrollerCore
    /** @var string */
    public $list_id;
    /** @var string */
    protected $_defaultOrderWay = 'ASC';
    /** @var array Errors displayed after post processing */
    public $errors = array();
    /** @var array List to be generated */
    protected $fields_list;
    /** @var int Default number of results in list per page */
    protected $_default_pagination = 50;

    // Backward compatibility for 1.4 - Copy from AdminTab
    public $fieldsDisplay = array();
    public $table = '';

    protected $context;

    public $ps17x = false;
    public $ps16x = false;

    protected $processListData = false;

    // If order way is ASC
    protected $_orderWayAsc = true;

    protected $kpis = array(
        'sales_30_days'     => 0,
        'income_30_days'    => 0,
        'avg_order_value'   => 0,
        'avg_order_per_day' => 0
    ); //todo: should store to table `ps_configuration_kpi`????

    public function __construct()
    {
        $this->amazon = new Amazon();

        $this->className = $this->amazon->name;
        $this->display = 'list';

        $this->context = Context::getContext();
        $this->id_lang = (int)$this->context->language->id;

        $this->lang = true;
        $this->deleted = false;

        $this->list_id = $this->table = 'amazon_stats';
        $this->identifier = 'order_id';
        $this->identifier_name = "amazon_order_id";

        $this->colorOnBackground = false;

        $this->url = $this->amazon->url;
        $this->images = $this->amazon->images;
        $this->path = $this->amazon->path;

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->ps17x = true;
            $this->ps16x = true;
        } elseif (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $this->ps16x = true;
        } else {
            $this->ps16x = false;
        }

        $this->fieldsDisplay = $this->fields_list = array(
            'order_id' => array(
                'title' => 'Order ID',
                'align' => 'text-center',
                'orderby' => false,
            ),
            'buyer_name' => array(
                'title' => 'Buyer Name',
                'orderby' => false,
            ),
            'total_price' => array(
                'title' => 'Price',
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'width' => 30 // For 1.4
            ),
            'shipping_price' => array(
                'title' => 'Shipping Price',
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'orderby' => false,
                'width' => 30 // For 1.4
            ),
            'commissions' => array(
                'title' => 'Amazon Fee',
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'orderby' => false,
                'width' => 30 // For 1.4
            ),
            'purchase_date' => array(
                'title' => 'Purchase Date',
                'type' => 'datetime',
                'width' => 180 // For 1.4
            ),
            'payments_date' => array(
                'title' => 'Payment Date',
                'type' => 'datetime',
                'width' => 180 // For 1.4
            ),
        );

        $this->bootstrap = true;

        parent::__construct();
    }

//    public function setMedia()
//    {
//        parent::setMedia();
//
//        $assetPath = _MODULE_DIR_.$this->module->name.'/views/';
//
//        //chartist-js, todo: need to have the flag like $this->chart to allow render chart or not
    ////        $this->addCSS($assetPath . 'vendors/chartist/chartist.min.css');
    ////        $this->addJS($assetPath . 'vendors/chartist/chartist.min.js');
    ////        $this->addJS($assetPath .'js/adminstats.js');
//    }

    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ) {
        if ($this->processListData) {
            return false;
        }

        // Get sort params
        $this->_getSortParams($id_lang, $order_by, $order_way);

        $orderData = $this->parseData($id_lang);

        //filter data with search and sort
        $orderData = $this->filter($orderData);

        /*echo "<pre>";
        print_r($orderData);
        echo "</pre>"; die;*/

        $displayData = $this->_paginate($orderData);

        $this->_list      = $displayData;
        $this->_listTotal = is_array($orderData) ? count($orderData) : 0;

        $this->processListData = true;
    }

    /**
     * Get order way and order by
     * Copy from AdminController->getList
     * @param $id_lang
     * @param $order_by
     * @param $order_way
     *
     * @throws Exception
     */
    protected function _getSortParams($id_lang, $order_by, $order_way)
    {
        $cookie = Context::getContext()->cookie;
        $prefix = str_replace(array('admin', 'controller'), '', Tools::strtolower(get_class($this)));

        if (empty($order_by)) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                $order_by = $cookie->__get($this->table.'Orderby') ? $cookie->__get($this->table.'Orderby') : $this->_defaultOrderBy;
            } else {
                // Get from cookie
                if ($this->context->cookie->{$prefix.$this->list_id.'Orderby'}) {
                    $order_by = $this->context->cookie->{$prefix.$this->list_id.'Orderby'};
                } elseif ($this->_orderBy) {
                    $order_by = $this->_orderBy;
                } else {
                    $order_by = $this->_defaultOrderBy;
                }
            }
        }
        $this->_orderBy = $order_by;

        if (empty($order_way)) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                $order_way = $cookie->__get($this->table.'Orderway') ? $cookie->__get($this->table.'Orderway') : 'ASC';
            } else {
                if ($this->context->cookie->{$prefix.$this->list_id.'Orderway'}) {
                    $order_way = $this->context->cookie->{$prefix.$this->list_id.'Orderway'};
                } elseif ($this->_orderWay) {
                    $order_way = $this->_orderWay;
                } else {
                    $order_way = $this->_defaultOrderWay;
                }
            }
        }
        $this->_orderWay = Tools::strtoupper($order_way);
        $this->_orderWayAsc = ($this->_orderWay !== 'DESC');

        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way) || !Validate::isUnsignedId($id_lang)) {
            throw new Exception('get list params is not valid');
        }
    }

    /**
     * Parse content from data
     * @param int $id_lang
     *
     * @return array
     */
    protected function parseData($id_lang)
    {
        $marketplace_setup = AmazonTools::selectPlatforms($id_lang);

        if (empty($marketplace_setup)) {
            die(Tools::displayError('Please configure the Marketplace tab'));
        }

        $orderReportFile = _PS_DOWNLOAD_DIR_.'demo-order.csv';
        $settleReportFile = _PS_DOWNLOAD_DIR_.'demo-settle-report.csv';

        if (!file_exists($orderReportFile) || !file_exists($settleReportFile)) {
            die(Tools::displayError('Missing order report file ('.$orderReportFile.') or settle report file ('.$settleReportFile.')'));
        }

        $amazon_report_ws = new AmazonOrdersReports($marketplace_setup['auth'], $marketplace_setup['params'], null, Amazon::$debug_mode);
        $data = $amazon_report_ws->parseReport(_PS_DOWNLOAD_DIR_.'demo-order.csv', 'order');
        $settleReportData = $amazon_report_ws->parseReport(_PS_DOWNLOAD_DIR_.'demo-settle-report.csv', 'settle');

        /**
         * The file contains 1 line per purchased product, the key is the Amazon order id
         * -> you can have many lines with only 1 order ID, this is order with multiple items.
         * group to order info
         */
        $orderData = $this->parseOrderData($data);

        //parse settle report and merge order data to order report data, also calculate the stats
        $orderData = $this->parseSettleReportData($settleReportData, $orderData);

        return $orderData;
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

        //field need to be unset when grouping product to one order record
        $unsetFields = array(
            'order_item_id',
            'item_price',
            'item_tax',
            'product_name',
            'sku',
            'quantity_purchased'
        );

        //caching currency
        $currency = null;
        $default_currency = Currency::getDefaultCurrency();

        foreach ($data as $key => $itemInfo) {
            $orderAmazonId = $itemInfo['order_id'];

            //convert datetime
            $itemInfo['original_purchase_date'] = $itemInfo['purchase_date'];
            $itemInfo['purchase_date'] = date($formatDateTime, strtotime($itemInfo['purchase_date']));
            $itemInfo['original_payments_date'] = $itemInfo['payments_date'];
            $itemInfo['payments_date'] = date($formatDateTime, strtotime($itemInfo['payments_date']));

            //group order id
            if (!isset($processedData[$orderAmazonId])) {
                $orderInfo = $itemInfo;
                $orderInfo['total_price'] = $itemInfo['item_price'] + $itemInfo['item_tax']
                                            + $itemInfo['shipping_price'] + $itemInfo['shipping_tax'];
            } else {
                $orderInfo = $processedData[$orderAmazonId];
                $orderInfo['total_price'] += $itemInfo['item_price'] + $itemInfo['item_tax'];
            }

            //retrieve product info
            $productInfo = array();
            foreach ($unsetFields as $unsetField) {
                $productInfo[$unsetField] = $itemInfo[$unsetField];
                unset($orderInfo[$unsetField]);
            }

            $productId = $productInfo['order_item_id'];
            $orderInfo['items'][$productId] = $productInfo;

            //currency
            if (isset($orderInfo['currency'])) {
                if (!$currency) { //init object when $currency is not available
                    $currency = (int) Currency::getIdByIsoCode($orderInfo['currency']);
                    $currency = new Currency($currency);
                }
                if ($currency) {
                    $orderInfo['id_currency'] = $currency->id;
                }
            }

            if (empty($orderInfo['id_currency'])) {
                $orderInfo['id_currency'] = $default_currency->id;
            }

            $processedData[$orderAmazonId] = $orderInfo;
        }

        return $processedData;
    }

    /**
     * filter + sort + paginate data
     *
     * @param $data
     *
     * @return array
     */
    protected function filter($data)
    {

        //search/filter value
        $data = $this->_search($data);

        //sort
        $data = $this->_sort($data);

        return $data;
    }

    /**
     * filter the input array data
     *
     * @param $inputData
     *
     * @return array
     */
    protected function _search($inputData)
    {
        //return $inputData;
        if (empty($inputData)) {
            return $inputData;
        }

        $filters = $this->_collectFiltersFromCookie();

        $outputData = array();

        foreach ($inputData as $orderId => $orderInfo) {
            $match = true;

            foreach ($filters as $field => $filterValue) {
                if (empty($filterValue)) {
                    continue;
                }

                if (is_array($filterValue)) { //datetime

                    if (isset($filterValue[0]) && !empty($filterValue[0])) { //from
                        if (!Validate::isDate($filterValue[0])) {
                            $this->errors[] = $this->trans('The \'From\' date format is invalid (YYYY-MM-DD)', array(), 'Admin.Notifications.Error');
                        } else {
                            $match = $match && (strtotime($orderInfo[$field]) > strtotime($filterValue[0]));
                        }
                    }


                    if (isset($filterValue[1]) && !empty($filterValue[1])) { //to
                        if (!Validate::isDate($filterValue[1])) {
                            $this->errors[] = $this->trans('The \'From\' date format is invalid (YYYY-MM-DD)', array(), 'Admin.Notifications.Error');
                        } else {
                            $match = $match && (strtotime($orderInfo[$field]) < strtotime($filterValue[1]));
                        }
                    }
                } else {
                    $filterValue = Tools::strtolower((string)$filterValue);
                    $match = $match && (strpos($orderInfo[$field], $filterValue) !== false);
                }
            }

            if ($match) {
                $outputData[$orderId] = $orderInfo;
            }
        }

        return $outputData;
    }

    protected function _sort($data)
    {
        if ($this->_orderBy) {
            $sortFunc = '_cmp' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->_orderBy)));
            $callable = array($this, $sortFunc);
            if (is_callable($callable)) {
                uasort($data, $callable);
            }
        }
        return $data;
    }

    // User-defined functions for custom sort
    protected function _cmpTotalPrice($a, $b)
    {
        $field = 'total_price';
        return $this->_cmpNumber((float)$a[$field], (float)$b[$field]);
    }

    protected function _cmpPurchaseDate($a, $b)
    {
        $field = 'purchase_date';
        return $this->_cmpNumber(strtotime($a[$field]), strtotime($b[$field]));
    }

    protected function _cmpPaymentsDate($a, $b)
    {
        $field = 'payments_date';
        return $this->_cmpNumber(strtotime($a[$field]), strtotime($b[$field]));
    }

    protected function _cmpNumber($x, $y)
    {
        if ($x < $y) {
            return $this->_orderWayAsc ? -1 : 1;
        } elseif ($x > $y) {
            return $this->_orderWayAsc ? 1 : -1;
        }
        return 0;
    }

    /**
     * get filters from cookie
     * @return array
     */
    protected function _collectFiltersFromCookie()
    {
        if (!$this->fields_list) {
            return array();
        }

        $prefix = $this->table.'Filter_';
        $cookieFilters = $this->context->cookie->getFamily($prefix);
        $filters = array();

        foreach ($cookieFilters as $field => $filterValue) {
            $field = str_replace($prefix, "", $field);
            if (array_key_exists($field, $this->fields_list)) {
                $isDateField = isset($this->fields_list[$field]['type']) && $this->fields_list[$field]['type'] == 'datetime';

                if ($isDateField) {
                    $filterValue = unserialize($filterValue);
                    if ($filterValue[0] || $filterValue[1]) {
                        $filters[$field] = $filterValue;
                    }
                } else {
                    $filters[$field] = $filterValue;
                }
            }
        }

        return $filters;
    }

    /**
     * Set the filters used for the list display
     */
    protected function getCookieFilterPrefix()
    {
        return str_replace(array('admin', 'controller'), '', Tools::strtolower(get_class($this)));
    }

    /**
     * paginate data for order report
     *
     * @param $data
     *
     * @return array
     */
    protected function _paginate($data)
    {
        $pagination = isset($this->_default_pagination) ? $this->_default_pagination : $this->_pagination[1];

        if (in_array((int)Tools::getValue($this->list_id.'_pagination'), $this->_pagination)) {
            $pagination = (int)Tools::getValue($this->list_id.'_pagination');
        } elseif (isset($this->context->cookie->{$this->list_id.'_pagination'}) && $this->context->cookie->{$this->list_id.'_pagination'}) {
            $pagination = $this->context->cookie->{$this->list_id.'_pagination'};
        }

        $listTotal = is_array($data) ? count($data) : 0;
        $total_pages = max(1, ceil($listTotal / $pagination));

        $page = (int)Tools::getValue('submitFilter'.$this->list_id);

        if (!$page) {
            $page = 1;
        }

        if ($page > $total_pages) {
            $page = $total_pages;
        }

        $selected_pagination = Tools::getValue(
            $this->list_id.'_pagination',
            isset($this->context->cookie->{$this->list_id.'_pagination'}) ? $this->context->cookie->{$this->list_id.'_pagination'} : $pagination
        );

        $offset = ($selected_pagination * ($page - 1)); //because we are in array, so 0-49, 50-99, ....
        $limit = $selected_pagination;

//        dump($page, $selected_pagination, $total_pages, $listTotal);
//        dump($offset, $limit);

        return array_slice($data, $offset, $limit);
    }

    /**
     * @param $settleReportData
     * @param $orderData
     *
     * @return array
     */
    protected function parseSettleReportData($settleReportData, $orderData)
    {
        $statsData = array(
            'total_amount' => 0,
            'total_commissions' => 0,
            'total_qty' => 0,
            'dates' => array(),
        );
        $processedSettleData = array();

        //parse settle data base on order_id and collect data for stats data
        foreach ($settleReportData as $key => $settleInfo) {
            $orderId = $settleInfo['order_id'];
            $orderItemId = $settleInfo['order_item_code'];
            $orderDate = date('Y-m-d', strtotime($settleInfo['posted_date']));

            if ($settleInfo['total_amount']) {
                $statsData['total_amount'] = $settleInfo['total_amount'];
                continue;
            }

            if ($orderId) {
                if (! isset($statsData['dates'][$orderDate])) {
                    $statsData['dates'][$orderDate] = array($orderId);
                } elseif (! array_search($orderId, $statsData['dates'][$orderDate])) {
                    $statsData['dates'][$orderDate][] = $orderId;
                }
            }

            if (!isset($processedSettleData[$orderId])) {
                $processedSettleData[$orderId] = array(
                    'shipment_id' => $settleInfo['shipment_id'],
                    'marketplace_name' => $settleInfo['marketplace_name'],
                    'posted_date' => $settleInfo['posted_date'],
                    'commissions' => 0,
                    'items' => array(),
                );
            }

            if ($settleInfo['quantity_purchased']) {
                $statsData['total_qty'] += $settleInfo['quantity_purchased'];
            }

            if ($settleInfo['price_type'] == 'Principal' && $settleInfo['price_amount']) {
                $processedSettleData[$orderId]['items'][$orderItemId]['item_settle_price'] = $settleInfo['price_amount'];
            }

            if ($settleInfo['price_type'] == 'Shipping' && $settleInfo['price_amount']) {
                $processedSettleData[$orderId]['items'][$orderItemId]['item_settle_shipping'] = $settleInfo['price_amount'];
            }

            if ($settleInfo['item_related_fee_type'] == 'Commission' && $settleInfo['item_related_fee_amount']) {
                $commission = $settleInfo['item_related_fee_amount'];
                $processedSettleData[$orderId]['items'][$orderItemId]['item_commission'] = $commission;
                $processedSettleData[$orderId]['commissions'] += $commission;
                $statsData['total_commissions'] += $commission;
            }

            if ($settleInfo['item_related_fee_type'] == 'ShippingHB' && $settleInfo['item_related_fee_amount']) {
                $processedSettleData[$orderId]['items'][$orderItemId]['item_fee_shipping'] = $settleInfo['item_related_fee_amount'];
            }
        }


        //merge settle data with order data and update more stats
        $statsData['total_commissions'] = 0;
        foreach ($orderData as $orderId => $orderInfo) {
            if (!isset($processedSettleData[$orderId])) {
                continue;
            }

            $orderData[$orderId] = array_merge_recursive($orderInfo, $processedSettleData[$orderId]);
        }

        //set kpi data to render KPI section
        $statsData['total_orders'] = is_array($processedSettleData) ? count($processedSettleData) : 0;
        $this->setKPI($statsData);

        return $orderData;
    }

    /**
     * @param $statsData
     */
    protected function setKPI($statsData)
    {
        $this->kpis['avg_order_per_day'] = (float) ($statsData['total_orders'] / 30);
        $this->kpis['avg_order_value'] = (float) ($statsData['total_amount'] / $statsData['total_orders']);
        $this->kpis['income_30_days'] = $statsData['total_amount'];
        $this->kpis['sales_30_days'] = $statsData['total_qty'];
    }

    public function displayListContent1($token = null)
    {
        $this->getList($this->context->language->id);

        $this->context->smarty->assign('kpis', $this->kpis);

        $template_path =_PS_MODULE_DIR_.$this->amazon->name.'/views/templates/admin/';
        return $this->context->smarty->fetch($template_path.'stats/kpi.tpl');
    }

    /**
     * For 1.4
     * Default row is 50
     * @param null $token
     */
    public function displayListHeader($token = null)
    {
        $cookie = &$this->context->cookie;
        $pagination = $this->table . '_pagination';
        $cookie->$pagination = Tools::getValue(
            'pagination',
            (isset($cookie->{$this->table.'_pagination'}) ? $cookie->{$this->table.'_pagination'} : $this->_pagination[1])
        );
        parent::displayListHeader($token);
    }

//    public function renderView() {
//        $orderData = $this->parseData($this->context->language->id);
//        $key = Tools::getValue($this->identifier);
//        $view = array();
//
//        if (isset($orderData[$key])) {
//            $view = $orderData[$key];
//            foreach ($orderData[$key] as $k => $v) {
//                if (is_array($v) || is_object($v)) {
//                    unset($view[$k]);
//                }
//            }
//        } else {
//            $view['error'] = $this->l("Order not found", "amazon");
//        }
//        $this->tpl_view_vars = array('view' => $view);
//        return parent::renderView();
//    }
}
