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

class AmazonAutoUpdate extends Amazon
{
    const MERCHANT_OPEN_LISTINGS_DATA = 'open_listings_data';
    const FILE_MAX_TIME = 3600;

    public $import = null;
    public $ws = null;
    public $marketplaceId = null;
    public $region = null;
    public $file_inventory = null;
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
                $this->autoUpdate(Amazon::ADD);
                break;
            default:
                $this->autoUpdate(Amazon::UPDATE);
                break;
        }
    }

    public function autoUpdate($action)
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

        $this->file_inventory = sprintf('%s%s_%s_%s.raw', $this->import, self::MERCHANT_OPEN_LISTINGS_DATA, $this->merchantId, $this->region);

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

        $last_request = Configuration::get('AMAZON_CHECKSTOCK_REQUEST');
        $pass = false ;

        if (file_exists($this->file_inventory)) {
            $filetime = filemtime($this->file_inventory);

            // file has less than one hour, we use it
            if ($filetime > time() - (self::FILE_MAX_TIME)) {
                $pass = true;
            }
        }

        if ($pass == false && $last_request && $last_request > time() - (self::FILE_MAX_TIME)) {
            // A reports has been requested less than one hour ago we do not request, we check if there is an available report
            //
            $reportRequestId = $this->reportRequestList();

            if ($reportRequestId) {
                if ($this->getReport($reportRequestId)) {
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

        $this->processInventory();
    }

    protected function processInventory()
    {
        $fix_asin = (bool)Tools::getValue('fix-asin');

        $repricing = $this->amazon_features['repricing'];


        if (Amazon::$debug_mode) {
            printf('processInventory()'.nl2br(Amazon::LF));
        }

        if (($result = AmazonTools::fileGetContents($this->file_inventory)) === false) {
            self::$errors[] = sprintf('%s(#%d): processInventory - Unable to read input file (%s)', basename(__FILE__), __LINE__, $this->file_inventory);

            return (false);
        }

        if ($result == null or empty($result)) {
            printf('%s(#%d): processInventory -Inventory is empty !', basename(__FILE__), __LINE__);
            return (false);
        }

        $lines = explode(Amazon::LF, $result);

        if (!is_array($lines) || !count($lines)) {
            printf('%s(#%d): processInventory - Inventory is empty !', basename(__FILE__), __LINE__);
            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(str_repeat('-', 160));
            CommonTools::p(sprintf('Inventory: %s products', count($lines)));
        }


        $amazonItems = array();
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

            list($SKU, $ASIN, $Price, $Qty) = $result;

            if (empty($Qty) || $Qty <= 0) {
                continue;
            }

            $ASIN = trim($ASIN);
            $SKU = trim($SKU);

            if (AmazonTools::validateSKU($SKU) && AmazonTools::validateASIN($ASIN)) {
                $amazonItems[$SKU] = (int)$Qty;
            } else {
                printf('%s(#%d): processInventory - Wrong ASIN or SKU "%s/%s"', $this->l('Wrong ASIN or SKU'), $SKU, $ASIN);
            }
        }

        if (!is_array($amazonItems) || !count($amazonItems)) {
            if (Amazon::$debug_mode) {
                printf('%s(#%d): processInventory - Inventory is empty !', basename(__FILE__), __LINE__);
            }
        }

        if (Amazon::$debug_mode) {
            // printf('Processed Items: %s', nl2br(print_r($amazonItems, true)));
        }

        $quantity_mismatch = array();

        foreach ($amazonItems as $SKU => $AmazonQty) {
            $productCheck = AmazonProduct::checkProduct($SKU, $this->id_shop);

            if ($productCheck == 0) {
                //printf('%s(#%d): processInventory - SKU/Reference not found in your database. Please check existance of this product: "%s"', basename(__FILE__), __LINE__, $SKU);
                continue;
            } elseif ($productCheck > 1) {
                //printf('%s(#%d): processInventory - Unable to import duplicate product "%s" - Please remove the duplicate product in your database.',basename(__FILE__), __LINE__, $SKU);
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

            if ($fix_asin) {
                AmazonProduct::updateProductOptions($id_product, $this->amazon_id_lang, 'asin1', $ASIN, $id_product_attribute);
            }

            if ($AmazonQty <= 0 && $quantity <= 0) {
                continue;
            }

            if ($AmazonQty != $quantity) {
                $quantity_mismatch[$SKU] = array('amazon' => $AmazonQty, 'prestashop' => $quantity);

                if (Tools::getValue('fix')) {
                    AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $id_product);

                    if ($repricing) {
                        AmazonProduct::marketplaceActionSet(Amazon::REPRICE, $id_product, $id_product_attribute, $SKU, $this->amazon_id_lang);
                    }
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

        $files = glob($output_dir.self::MERCHANT_OPEN_LISTINGS_DATA.'*.raw');

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
                if (strstr($file, self::MERCHANT_OPEN_LISTINGS_DATA) === false) {
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
        $params['ReportType'] = '_GET_FLAT_FILE_OPEN_LISTINGS_DATA_';
        $params['Version'] = '2009-01-01';
        $params['MarketplaceIdList.Id.1'] = $this->marketplaceId;

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

        if (!isset($xml->RequestReportResult->ReportRequestInfo->ReportProcessingStatus) || !isset($xml->RequestReportResult->ReportRequestInfo->ReportRequestId)) {
            printf('%s(#%d): %s - reportRequest Failed', basename(__FILE__), __LINE__);

            return (false);
        }

        if ($xml->RequestReportResult->ReportRequestInfo->ReportProcessingStatus == '_SUBMITTED_') {
            $requestId = (string)$xml->RequestReportResult->ReportRequestInfo->ReportRequestId;
            Configuration::updateValue('AMAZON_CHECKSTOCK_REQUEST', time());
            return ($requestId);
        } else {
            return (false);
        }
    }

    protected function reportRequestList()
    {
        $params = array();
        $params['Version'] = '2009-01-01';
        $params['Action'] = 'GetReportRequestList';
        $params['ReportRequestList.Type.1'] = '_GET_FLAT_FILE_OPEN_LISTINGS_DATA_';
        $params['ReportProcessingStatusList.Status.1'] = '_DONE_';
        $params['RequestedFromDate'] = gmdate('Y-m-d\TH:i:s\Z', strtotime('now -1 hour'));

        if (Amazon::$debug_mode) {
            printf('reportRequestList()'.nl2br(Amazon::LF));
        }

        $xml = $this->ws->simpleCallWS($params, 'Reports');

        if (!$xml instanceof SimpleXMLElement or isset($xml->Error)) {
            printf('%s(#%d): reportRequestList Failed', basename(__FILE__), __LINE__);
            return (false);
        }

        if (Amazon::$debug_mode) {
            printf('reportRequestList() - report'.nl2br(Amazon::LF));
            echo $this->debugXML($xml);
        }

        $xml->registerXPathNamespace('xmlns', 'http://mws.amazonaws.com/doc/2009-01-01/');

        $xpath_result = $xml->xpath('//xmlns:GetReportRequestListResponse/xmlns:GetReportRequestListResult/xmlns:ReportRequestInfo');

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): reportRequestList result: %s', basename(__FILE__), __LINE__, nl2br(print_r($xpath_result, true))));
        }

        if (is_array($xpath_result) && !count($xpath_result)) {
            return(false);
        } else {
            // the report is available, take the first one :
            $report_data = reset($xpath_result);

            if ($report_data instanceof SimpleXMLElement) {
                if (Amazon::$debug_mode) {
                    CommonTools::p("Selected Report:");
                    CommonTools::p($report_data);
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


    protected function getReport($reportRequestId)
    {
        $params = array();
        $params['Version'] = '2009-01-01';
        $params['Action'] = 'GetReport';
        $params['ReportId'] = $reportRequestId;

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
            printf('%s(#%d): getReport - Inventory is empty', basename(__FILE__), __LINE__);
            return (false);
        }

        if (file_put_contents($this->file_inventory, $result) === false) {
            printf('%s(#%d): getReport - Unable to write to output file: %s', basename(__FILE__), __LINE__, $this->file_inventory);
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

$amazonAutoUpdate = new AmazonAutoUpdate();
$amazonAutoUpdate->dispatch();
