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

class AmazonProductsReport extends Amazon
{
    const MERCHANT_ACTIVE_LISTINGS_DATA = 'active_listings_data';
    const MERCHANT_LISTINGS_ALL_DATA = 'listings_data';
    const EXPIRE = 14400; //4 hours

    public static $errors = array();
    public static $messages = array();

    public $import = null;
    public $ws = null;
    public $marketplaceId = null;
    public $region = null;
    public $file_inventory = null;
    public $inventory_type = null;
    public $merchantId = null;
    public $report_type = null;
    public $id_lang_default = null;


    public function __construct()
    {
        parent::__construct();
    }

    public function setFileInventory()
    {
        $fileid = floor((time() % (86400 * 365)) / self::EXPIRE); // file id valid for 4 hours

        $this->file_inventory = sprintf('%s%s_%s_%s_%s.raw', $this->import, $this->inventory_type ? $this->inventory_type : self::MERCHANT_ACTIVE_LISTINGS_DATA, $this->merchantId, $this->region, $fileid);
        return;
    }

    public function initDownload()
    {
        $id_lang = Tools::getValue('amazon_lang');
        $token = Tools::getValue('instant_token');

        if (!$token || $token != Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0)) {
            self::$errors[] = 'Wrong token';
            return(false);
        }

        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        if (!is_array($marketPlaceRegion) || !count($marketPlaceRegion)) {
            $error  = $this->l('Module is not configured yet');
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$error");
            }
            return (false);
        }

        if (!isset($marketPlaceRegion[$id_lang]) || !Tools::strlen($marketPlaceRegion[$id_lang])) {
            self::$errors[] = 'No selected language, nothing to do...';
            return(false);
        }

        $amazon = AmazonTools::selectPlatforms($id_lang, 0);

        if (!isset($marketPlaceIds[$id_lang])) {
            self::$errors[] = 'No selected language, nothing to do...';
            return(false);
        }

        $this->marketplaceId = trim($marketPlaceIds[$id_lang]);
        $this->region = trim($amazon['params']['Country']);
        $this->merchantId = trim($amazon['auth']['MerchantID']);

        $this->cleanupImportDirectory();

        $this->setFileInventory();

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Webservice Params: %s', print_r($amazon, true)));
        }
        $this->ws = new AmazonWebService($amazon['auth'], $amazon['params'], null, Amazon::$debug_mode);

        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        if (!isset($marketPlaceIds[$id_lang]) || !$marketPlaceIds[$id_lang]) {
            $lang = new Language($id_lang);
            $error = sprintf('%s(#%d): %s "%s"', basename(__FILE__), __LINE__, $this->l('Marketplace is not yet configured for'), $lang->name);

            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$error");
            }
            return (false);
        }
        return(true);
    }

    private function filelistImportDirectory()
    {
        // Generic function sorting files by date
        $output_dir = sprintf('%s/', rtrim($this->import, '/'));

        if (!is_dir($output_dir)) {
            return null;
        }

        $files = glob($output_dir.$this->file_inventory.'*.raw');

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
                if ($now - $timestamp > 86400 * 30) {
                    unlink($file);
                }
            }
        }
    }

    protected function reportRequest()
    {
        $params = array();
        $params['Action'] = 'RequestReport';
        $params['ReportType'] = $this->report_type;
        $params['Version'] = '2009-01-01';
        $params['MarketplaceIdList.Id.1'] = $this->marketplaceId;

        if (Amazon::$debug_mode) {
            CommonTools::p('reportRequest()');
        }

        $xml = $this->ws->simpleCallWS($params, 'Reports');

        if (!$xml instanceof SimpleXMLElement or isset($xml->Error)) {
            printf('%s(#%d): %s - reportRequest Failed', basename(__FILE__), __LINE__, print_r($xml->Error->Message, true));

            return (false);
        }

        if (Amazon::$debug_mode) {
            echo  $this->debugXML($xml);
        }

        if (!isset($xml->RequestReportResult->ReportRequestInfo->ReportProcessingStatus) || !isset($xml->RequestReportResult->ReportRequestInfo->ReportRequestId)) {
            printf('%s(#%d): %s - reportRequest Failed', basename(__FILE__), __LINE__, print_r($xml, true));

            return (false);
        }

        if ($xml->RequestReportResult->ReportRequestInfo->ReportProcessingStatus == '_SUBMITTED_') {
            $requestId = (string)$xml->RequestReportResult->ReportRequestInfo->ReportRequestId;
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
        $params['ReportRequestList.Type.1'] = $this->report_type;
        $params['ReportProcessingStatusList.Status.1'] = '_DONE_';
        $params['RequestedFromDate'] = gmdate('Y-m-d\TH:i:s\Z', strtotime('now -1 hour'));

        if (Amazon::$debug_mode) {
            CommonTools::p('reportRequestList()');
        }

        $xml = $this->ws->simpleCallWS($params, 'Reports');

        if (!$xml instanceof SimpleXMLElement or isset($xml->Error)) {
            printf('%s(#%d): reportRequestList Failed: %s'.Amazon::LF, basename(__FILE__), __LINE__, $xml->Error->Message);
            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p('reportRequestList() - report');
            echo $this->debugXML($xml);
        }

        $xml->registerXPathNamespace('xmlns', 'http://mws.amazonaws.com/doc/2009-01-01/');

        $xpath_result = $xml->xpath('//xmlns:GetReportRequestListResponse/xmlns:GetReportRequestListResult/xmlns:ReportRequestInfo');

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): reportRequestList result: %s'.Amazon::LF, basename(__FILE__), __LINE__, nl2br(print_r($xpath_result, true))));
        }

        if (is_array($xpath_result) && !count($xpath_result)) {
            return(null);
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
            } else {
                return(null);
            }
        }

        return (null);
    }


    protected function getReport($reportRequestId)
    {
        $params = array();
        $params['Version'] = '2009-01-01';
        $params['Action'] = 'GetReport';
        $params['ReportId'] = $reportRequestId;

        if (Amazon::$debug_mode) {
            CommonTools::p('getReport()');
        }

        $result = $this->ws->simpleCallWS($params, 'Reports', false);

        if ($result instanceof SimpleXMLElement) {
            printf('%s(#%d): getReport - An error occur', basename(__FILE__), __LINE__);
            var_dump($result);
            return(false);
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
