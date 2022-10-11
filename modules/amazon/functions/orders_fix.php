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
require_once(dirname(__FILE__).'/../classes/amazon.mail.logger.class.php');

class AmazonOrdersFix extends Amazon
{
    const MERCHANT_REPORT_NAME = 'orders_data';
    const FILE_MAX_TIME = 3600;
    const PERIOD = 604800;

    protected static $orders_fields = array(
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

    public $import = null;
    public $ws = null;
    public $marketplaceId = null;
    public $region = null;
    public $file_orders = null;
    public $merchantId = null;
    public $amazon_id_lang = null;
    public $id_shop = 1;
    public $id_warehouse = 1;


    public function dispatch()
    {
        $this->import = $this->path.'import/';

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        switch (Tools::getValue('action')) {
            case 'add':
                $this->fixOrders(Amazon::ADD);
                break;
            default:
                $this->fixOrders(Amazon::UPDATE);
                break;
        }
    }

    public function fixOrders($action)
    {
        $this->id_shop = null;

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            if (AmazonConfiguration::shopIsFeatureActive()) {
                $this->id_shop = (int)$this->context->shop->id;
                //$id_shop_group = (int)$this->context->shop->id_shop_group;
            } else {
                $this->id_shop = 1;
            }
            $this->id_warehouse = (int)Configuration::get('AMAZON_WAREHOUSE');
        }

        $tokens = Tools::getValue('cron_token');
        $lang = Tools::getValue('lang');

        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        if (!is_array($marketPlaceRegion) || !count($marketPlaceRegion)) {
            die('Module is not configured yet');
        }

        $marketLang2Region = array_flip($marketPlaceRegion);

        if (!isset($marketLang2Region[$lang]) || !$marketLang2Region[$lang]) {
            die('No selected language, nothing to do...');
        }

        $id_lang = $this->amazon_id_lang = $marketLang2Region[$lang];

        if (!AmazonTools::checkToken($tokens)) {
            die('Wrong Token');
        }

        $amazon = AmazonTools::selectPlatforms($id_lang, 0);

        $this->marketplaceId = trim($marketPlaceIds[$id_lang]);
        $this->region = trim($amazon['params']['Country']);
        $this->merchantId = trim($amazon['auth']['MerchantID']);

        $this->file_orders = sprintf('%s%s_%s_%s.raw', $this->import, self::MERCHANT_REPORT_NAME, $this->merchantId, $this->region);

        $this->cleanupImportDirectory();

        if (Amazon::$debug_mode) {
            printf('Webservice Params: %s'.nl2br(Amazon::LF), nl2br(print_r($amazon, true)));
        }
        $this->ws = new AmazonWebService($amazon['auth'], $amazon['params'], null, Amazon::$debug_mode);

        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        if (!isset($marketPlaceIds[$id_lang]) || !$marketPlaceIds[$id_lang]) {
            $lang = new Language($id_lang);
            die(sprintf('%s(#%d): %s "%s"', basename(__FILE__), __LINE__, $this->l('Marketplace is not yet configured for'), $lang->name));
        }

        $last_request = Configuration::get('AMAZON_FIXORDERS_REQUEST');
        $pass = false ;

        if (file_exists($this->file_orders)) {
            $filetime = filemtime($this->file_orders);

            // file has less than one hour, we use it
            if ($filetime > time() - (self::FILE_MAX_TIME)) {
                $pass = true;
            }
        }

        if ($pass == false && $last_request && $last_request > time() - (self::FILE_MAX_TIME)) {
            // A reports has been requested less than one hour ago we do not request, we check if there is an available report
            //
            $reportId = $this->reportRequestList();

            if ($reportId) {
                if ($this->getReport($reportId)) {
                    $pass = true;
                } else {
                    die('Error: failed to get report');
                }
            } else {
                die('A report has been already requested and there is not any available report yet');
            }
        } elseif ($pass == false) {
            if ($this->reportRequest()) {
                die('Report in preparation, please start the script again in a while.');
            }
        }


        if (!$pass) {
            die('No available report');
        }

        $this->processOrders();
    }

    protected function processOrders()
    {
        if (Amazon::$debug_mode) {
            printf('processOrders()'.nl2br(Amazon::LF));
        }

        if (($result = AmazonTools::fileGetContents($this->file_orders)) === false) {
            self::$errors[] = sprintf('%s(#%d): processOrders - Unable to read input file (%s)', basename(__FILE__), __LINE__, $this->file_orders);

            return (false);
        }

        if ($result == null or empty($result)) {
            printf('%s(#%d): processOrders -Orders is empty !', basename(__FILE__), __LINE__);
            return (false);
        }

        $lines = explode(Amazon::LF, $result);

        if (!is_array($lines) || !count($lines)) {
            printf('%s(#%d): processOrders - Orders is empty !', basename(__FILE__), __LINE__);
            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(str_repeat('-', 160));
            CommonTools::p(sprintf('Orders: %s lines', count($lines)));
        }

        $header = reset($lines);


        if (Amazon::$debug_mode) {
            CommonTools::p(str_repeat('-', 160));
            CommonTools::p(sprintf('Header: %s', nl2br(print_r(explode("\t", $header), true))));
        }

        $amazonOrders = array();
        $count = 0;

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            if ($count++ < 1) {
                continue;
            }

            $result = explode("\t", $line);

            if (count(array_keys($result)) < 4) {
                continue;
            }
            $order = array();

            foreach (self::$orders_fields as $key => $order_field) {
                $order[$order_field] = $result[$key];
            }

            if (Amazon::$debug_mode) {
                CommonTools::p(str_repeat('-', 160));
                CommonTools::p(sprintf('Order: %s', nl2br(print_r($order, true))));
            }
        }

        if (!is_array($amazonOrders) || !count($amazonOrders)) {
            if (Amazon::$debug_mode) {
                printf('%s(#%d): processOrders - Orders is empty !', basename(__FILE__), __LINE__);
            }
        }

        if (Amazon::$debug_mode) {
            // printf('Processed Items: %s', nl2br(print_r($amazonOrders, true)));
        }

        $quantity_mismatch = array();

        foreach ($amazonOrders as $SKU => $AmazonQty) {
            $productCheck = AmazonProduct::checkProduct($SKU, $this->id_shop);

            if ($productCheck == 0) {
                //printf('%s(#%d): processOrders - SKU/Reference not found in your database. Please check existance of this product: "%s"', basename(__FILE__), __LINE__, $SKU);
                continue;
            } elseif ($productCheck > 1) {
                //printf('%s(#%d): processOrders - Unable to import duplicate product "%s" - Please remove the duplicate product in your database.',basename(__FILE__), __LINE__, $SKU);
                continue;
            }
            $product = new AmazonProduct($SKU, false, $this->amazon_id_lang, 'reference', $this->id_shop);

            if (!Validate::isLoadedObject($product)) {
                continue;
            }
            $id_product = $product->id;
            $id_product_attribute = $product->id_product_attribute;
            $quantity = 0;

            $product_options = AmazonProduct::getProductOptions($id_product, $this->id_lang, $id_product_attribute);
            $combination_options = array();

            if ($product->id_product_attribute) {
                $combination_options = AmazonProduct::getProductOptions($id_product, $this->id_lang, $id_product_attribute);
            }

            if (count($combination_options)) {
                $options = &$combination_options;
            } else {
                $options = &$product_options;
            }

            if (isset($options['fba']) && (bool)$options['fba']) {
                continue;
            }
            if (isset($options['disable']) && (bool)$options['disable']) {
                continue;
            }

            if (isset($options['force']) && (bool)$options['force']) {
                $quantity = 999;
            }

            if (!$quantity) {
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $quantity = Product::getQuantity((int)$id_product, $id_product_attribute ? $id_product_attribute : null);
                } else {
                    $quantity = Product::getRealQuantity($id_product, $id_product_attribute ? $id_product_attribute : null, $this->id_warehouse, $this->id_shop);
                }
            }

            if ($AmazonQty <= 0 && $quantity <= 0) {
                continue;
            }

            if ($AmazonQty != $quantity) {
                $quantity_mismatch[$SKU] = array('amazon' => $AmazonQty, 'prestashop' => $quantity);

                if (Tools::getValue('fix')) {
                    AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $id_product);
                }
            }
        }

        if (count($quantity_mismatch)) {
            $report = null;
            $report .= 'Mismatching quantities report:'.self::LF;
            $report .= sprintf('%-40s %-12s %-12s'.self::LF, 'SKU', 'Prestashop', 'Amazon');

            foreach ($quantity_mismatch as $SKU => $report_array) {
                $report .= sprintf('%-40s %-12s %-12s'.self::LF, $SKU, $report_array['prestashop'], $report_array['amazon']);
            }

            if (Tools::getValue('fix')) {
                $report .= count($quantity_mismatch) . ' unconsistencies automatically fixed'.self::LF;
            }
            if ((bool)Configuration::get('AMAZON_EMAIL')) {
                AmazonMailLogger::message($report);
            }

            CommonTools::p($report);
        } else {
            CommonTools::p("No Mismatch");
        }

        return (true);
    }

    private function filelistImportDirectory()
    {
        // Generic function sorting files by date
        $output_dir = sprintf('%s/', rtrim($this->import, '/'));

        if (!is_dir($output_dir)) {
            return null;
        }

        $files = glob($output_dir.self::MERCHANT_REPORT_NAME.'*.raw');

        if (!is_array($files) || !count($files)) {
            return null;
        }

        // Sort by date
        foreach ($files as $key => $file) {
            $files[filemtime($file)] = $file;
            unset($files[$key]);
        }
        ksort($files);

        return $files;
    }

    private function cleanupImportDirectory()
    {
        // Cleanup oldest files
        $files = $this->filelistImportDirectory();
        $now = time();

        if (is_array($files) && count($files)) {
            foreach ($files as $timestamp => $file) {
                if (strstr($file, self::MERCHANT_REPORT_NAME) === false) {
                    continue;
                }
                if ($now - $timestamp > 86400) {
                    unlink($file);
                }
            }
        }
    }


    protected function reportRequest()
    {
        $params = array();
        $params['Action'] = 'RequestReport';
        $params['ReportType'] = '_GET_FLAT_FILE_ORDERS_DATA_';
        $params['Version'] = '2009-01-01';
        $params['MarketplaceIdList.Id.1'] = $this->marketplaceId;
        $params['StartDate'] = gmdate('Y-m-d\TH:i:s\Z', time() - self::PERIOD);

        if (Amazon::$debug_mode) {
            printf('reportRequest()'.nl2br(Amazon::LF));
        }

        $xml = $this->ws->simpleCallWS($params, 'Reports');

        if (!$xml instanceof SimpleXMLElement or isset($xml->Error)) {
            printf('%s(#%d): %s - reportRequest Failed', basename(__FILE__), __LINE__);

            return (false);
        }

        if (Amazon::$debug_mode) {
            echo  $this->debugXML($xml);
        }

        if (!isset($xml->RequestReportResult->ReportInfo->ReportProcessingStatus) || !isset($xml->RequestReportResult->ReportInfo->ReportId)) {
            printf('%s(#%d): %s - reportRequest Failed', basename(__FILE__), __LINE__);

            return (false);
        }

        if ($xml->RequestReportResult->ReportInfo->ReportProcessingStatus == '_SUBMITTED_') {
            $requestId = (string)$xml->RequestReportResult->ReportInfo->ReportId;
            Configuration::updateValue('AMAZON_FIXORDERS_REQUEST', time());
            return ($requestId);
        } else {
            return (false);
        }
    }

    protected function reportRequestList()
    {
        $params = array();
        $params['Version'] = '2009-01-01';
        $params['Action'] = 'GetReportList';
        $params['ReportTypeList.Type.1'] = '_GET_FLAT_FILE_ORDERS_DATA_';
        $params['ReportProcessingStatusList.Status.1'] = '_DONE_';
        $params['RequestedFromDate'] = gmdate('Y-m-d\TH:i:s\Z', strtotime('now -1 hour'));
        
        if (Amazon::$debug_mode) {
            printf('reportList()'.nl2br(Amazon::LF));
        }

        $xml = $this->ws->simpleCallWS($params, 'Reports');

        if (!$xml instanceof SimpleXMLElement or isset($xml->Error)) {
            printf('%s(#%d): reportList Failed', basename(__FILE__), __LINE__);
            return (false);
        }

        if (Amazon::$debug_mode) {
            printf('reportList() - report'.nl2br(Amazon::LF));
            echo $this->debugXML($xml);
        }

        $xml->registerXPathNamespace('xmlns', 'http://mws.amazonaws.com/doc/2009-01-01/');

        $xpath_result = $xml->xpath('//xmlns:GetReportListResponse/xmlns:GetReportListResult/xmlns:ReportInfo');

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): reportList result: %s', basename(__FILE__), __LINE__, nl2br(print_r($xpath_result, true))));
        }

        if (is_array($xpath_result) && !count($xpath_result)) {
            return(false);
        } else {
            // the report is available, take the first one :
            $report_data = reset($xpath_result);

            if ($report_data instanceof SimpleXMLElement) {
                if (Amazon::$debug_mode) {
                    CommonTools::p("Selected Report:");
                    var_dump($report_data);
                }
                if (isset($report_data->GeneratedReportId) && $report_data->GeneratedReportId) {
                    return((string)$report_data->GeneratedReportId);
                }
                if (isset($report_data->ReportId) && $report_data->ReportId) {
                    return((string)$report_data->ReportId);
                }
            } else {
                return(false);
            }
        }

        return (false);
    }


    protected function getReport($reportId)
    {
        $params = array();
        $params['Version'] = '2009-01-01';
        $params['Action'] = 'GetReport';
        $params['ReportId'] = $reportId;

        if (Amazon::$debug_mode) {
            printf('getReport()'.nl2br(Amazon::LF));
        }

        $result = $this->ws->simpleCallWS($params, 'Reports', false);

        if ($result instanceof SimpleXMLElement) {
            printf('%s(#%d): getReport - An error occur', basename(__FILE__), __LINE__);
            var_dump($result);
            die;
        }

        if (empty($result)) {
            printf('%s(#%d): getReport - Orders is empty', basename(__FILE__), __LINE__);
            return (false);
        }

        if (file_put_contents($this->file_orders, $result) === false) {
            printf('%s(#%d): getReport - Unable to write to output file: %s', basename(__FILE__), __LINE__, $this->file_orders);
            return (false);
        }

        return (true);
    }


    public function debugXML($xml)
    {
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return AmazonTools::pre(array(htmlspecialchars($dom->saveXML())), true);
    }
}

$amazonOrdersFix = new AmazonOrdersFix();
$amazonOrdersFix->dispatch();
