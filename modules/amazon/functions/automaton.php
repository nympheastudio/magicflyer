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

class AmazonAutomatonProcess
{
    public $requestReportTime;
    public $reportTime;
    public $requestId;
    public $reportProcessingStatus;
    public $reportId;
    public $step;
    public $title;
    public $message;
    public $flag;
    public $marketplace;
    public $resubmitTimer;
    public $completedDate;
    public $loader;
    public $hide;
}

class AmazonAutomaton extends Amazon
{
    const MERCHANT_OPEN_LISTINGS_DATA = 'open_listings_data';
    const ACTION_WIZARD_CREATION_MODE = 1;
    const ACTION_WIZARD_MATCHING_MODE = 2;
    const STEP_REPORT_REQUEST = 1;
    const STEP_GET_REPORT_REQUEST_LIST = 2;
    const STEP_GET_REPORT = 3;
    const STEP_PROCESS_REPORT = 4;
    const STEP_POST_PROCESS = 5;
    const STEP_COMPLETED = 6;
    const STEP_COMPLETED_NOTHING = 7;
    const STEP_NOTHING = 9;
    const ONE_DAY = 86400;
    const ONE_HOUR = 3600;
    const TWO_HOURS = 7200;
    const ONE_MINUTE = 60;
    const TWO_MINUTES = 120;
    const FIVE_MINUTES = 300;
    const NOW = 1;

    protected static $errors   = array();
    protected static $log      = array();
    protected static $xml      = null;
    protected static $process  = null;
    protected static $products = array();
    protected static $abort    = false;

    protected static $steps          = array(
        self::STEP_REPORT_REQUEST => 'REPORT_REQUEST',
        self::STEP_GET_REPORT_REQUEST_LIST => 'GET_REPORT_REQUEST_LIST',
        self::STEP_GET_REPORT => 'GET_REPORT',
        self::STEP_PROCESS_REPORT => 'STEP_PROCESS_REPORT',
        self::STEP_POST_PROCESS => 'STEP_POST_PROCESS',
        self::STEP_COMPLETED => 'STEP_COMPLETED',
        self::STEP_COMPLETED_NOTHING => 'STEP_COMPLETED_NOTHING',
        self::STEP_NOTHING => 'NOTHING'
    );
    protected $import         = null;
    protected $ws             = null;
    protected $file_inventory = null;
    protected $amazon_id_lang = null;
    protected $marketplaceId  = null;
    protected $region         = null;

    public function __construct()
    {
        register_shutdown_function(array('AmazonAutomaton', 'jsonDisplayExit'));

        ob_start();

        parent::__construct();

        $this->initImportDirectory();

        $this->debug = (bool)Configuration::get('AMAZON_DEBUG_MODE');

        if (Tools::getValue('debug')) {
            $this->debug = true;
        }

        if (!AmazonTools::checkToken(Tools::getValue('amazon_token'))) {
            die('Wrong Token');
        }

        if ($this->debug) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        if (!($this->amazon_id_lang = Tools::getValue('amazon_lang'))) {
            self::$errors[] = sprintf('%s(#%d): %s', basename(__FILE__), __LINE__, $this->l('Required parameter: amazon_lang'));
            self::$abort = true;

            return;
        }

        $amazon = AmazonTools::selectPlatforms($this->amazon_id_lang, 0);

        if ($this->debug) {
            printf('Webservice Params: %s'.nl2br(Amazon::LF), nl2br(print_r($amazon, true)));
        }

        if (!($this->ws = new AmazonWebService($amazon['auth'], $amazon['params'], null, $this->debug))) {
            self::$errors[] = sprintf('%s(#%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to init Amazon Service'));
            self::$abort = true;

            return;
        }

        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');


        if (!isset($marketPlaceIds[$this->amazon_id_lang]) || !$marketPlaceIds[$this->amazon_id_lang]) {
            $lang = new Language($this->amazon_id_lang);
            self::$errors[] = sprintf('%s(#%d): %s "%s"', basename(__FILE__), __LINE__, $this->l('Marketplace is not yet configured for'), $lang->name);
            self::$abort = true;

            return;
        }
        $this->marketplaceId = trim($marketPlaceIds[$this->amazon_id_lang]);

        $this->region = $amazon['params']['Country'];
        $this->merchantId = trim($amazon['auth']['MerchantID']);

        $this->cleanupImportDirectory();
        $this->file_inventory = sprintf('%s%s_%s_%s.raw', $this->import, self::MERCHANT_OPEN_LISTINGS_DATA, $this->merchantId, $this->region);

        $use_ssl = false; // (bool)Configuration::get('PS_SSL_ENABLED_EVERYWHERE'); for images, use of ssl is not supported yet, source: https://images-na.ssl-images-amazon.com/images/G/02/rainier/help/Feeds_Error_Messages.pdf

        // Absolute URL to images
        //
        $baseurl = sprintf('%s://%s%s', $use_ssl ? 'https' : 'http', htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8'), __PS_BASE_URI__);
        $this->images_url = $baseurl.'img/p/';
    }

    private function filelistImportDirectory()
    {
        // Generic function sorting files by date
        $output_dir = sprintf('%s/', rtrim($this->import, '/'));
        //$timestamp = 0;

        if (!is_dir($output_dir)) {
            return null;
        }

        $files = glob($output_dir.'*.raw');

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

    private function initImportDirectory()
    {
        $this->import = $this->path.'import/';

        if (!is_dir($this->import)) {
            if (!@mkdir($this->import)) {
                self::$errors[] = sprintf('%s(#%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to create import directory'));
                self::$abort = true;

                return false;
            }
        }

        @chmod($this->import, 0777);

        if (file_put_contents($this->import.'.htaccess', "deny from all".Amazon::LF) === false) {
            self::$errors[] = sprintf('%s(#%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to write into import directory'));
            self::$abort = true;

            return false;
        }

        return true;
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }

    public static function jsonDisplayExit()
    {
        if ($result = ob_get_clean()) {
            $output = $result;
        } else {
            $output = null;
        }

        $json = Tools::jsonEncode(array(
            'error' => (count(AmazonAutomaton::$errors) ? true : false),
            'errors' => AmazonAutomaton::$errors,
            'products' => AmazonAutomaton::$products,
            'abort' => AmazonAutomaton::$abort,
            'xml' => AmazonAutomaton::$xml,
            'process' => self::$process,
            'output' => $output
        ));

        if ($callback = Tools::getValue('callback')) {
            // jquery

            if ($callback == '?') {
                $callback = 'jsonp_'.time();
            }

            echo (string)$callback.'('.$json.')';
            die;
        } else {
            // cron
            CommonTools::d($json);
        }
    }

    public function dispatch()
    {
        switch (Tools::getValue('action')) {
            case 'creation-wizard':
                $this->startCreationWizard();
                break;
            case 'matching-wizard':
                $this->startMatchingWizard();
                break;
            case 'match-products':
                $this->matchProducts();
                break;
            case 'confirm-products':
                $this->confirmProducts();
                break;
        }
    }

    public function startCreationWizard()
    {
        if ($this->debug) {
            printf('startCreationWizard()'.nl2br(Amazon::LF));
        }


        $flag = html_entity_decode('&lt;img src="'.$this->images.'geo_flags/'.$this->region.'.gif" alt="Amazon '.Tools::strtoupper($this->region).'" /&gt;');
        $title = sprintf('%s %s', $this->l('Amazon Marketplace Automaton started on'), AmazonTools::displayDate(date('Y-m-d h:i:s'), $this->id_lang, true));

        $amazon_synch = AmazonConfiguration::get('WIZARD_CREATION_STATE');

        if (!is_array($amazon_synch)) {
            $amazon_synch = array();
        }

        if (isset($amazon_synch[$this->amazon_id_lang])) {
            self::$process = $amazon_synch[$this->amazon_id_lang];

            if (!self::$process instanceof AmazonAutomatonProcess) {
                self::$process = new AmazonAutomatonProcess();
            }
        } else {
            self::$process = new AmazonAutomatonProcess();
        }

        if (self::$process->requestReportTime && self::isExpired(self::$process->requestReportTime, self::ONE_DAY)) {
            self::$process = new AmazonAutomatonProcess();
        }

        $this->creationWizardProcess();

        self::$process->title = $title;
        self::$process->flag = $flag;
        self::$process->marketplace = sprintf('Amazon %s', Tools::strtoupper($this->region));

        $amazon_synch[$this->amazon_id_lang] = self::$process;

        AmazonConfiguration::updateValue('WIZARD_CREATION_STATE', $amazon_synch, true);
    }

    protected static function isExpired($timestamp, $expiration = 3600)
    {
        $now = time();

        if (!$timestamp) {
            return (true);
        }

        if ($now - $timestamp > $expiration) {
            return (true);
        }

        return (false);
    }

    public function creationWizardProcess()
    {
        if ($this->debug) {
            printf('creationWizardProcess()'.nl2br(Amazon::LF));
        }

        switch (self::$process->step) {
            case self::STEP_NOTHING:
                break;

            case self::STEP_COMPLETED_NOTHING:
                self::$process = new AmazonAutomatonProcess();
                self::$process->resubmitTimer = 0;
                self::$process->step = self::STEP_REPORT_REQUEST;
                self::$process->loader = 0;
                self::$process->hide = 1;
                break;

            case self::STEP_COMPLETED:
                $message = $this->l('Process completed, you can send your feed to Amazon.');
                self::$process = new AmazonAutomatonProcess();
                self::$process->resubmitTimer = 0;
                self::$process->step = self::STEP_REPORT_REQUEST;
                self::$process->loader = 0;
                self::$process->hide = 0;
                self::$process->message = $message;
                break;

            case self::STEP_POST_PROCESS:
                if (self::$process->completedDate && !self::isExpired(self::$process->completedDate, self::ONE_DAY)) {
                    $productCount = $this->postProcessMarkProductsForCreation();

                    self::$process->completedDate = time();

                    if ($productCount) {
                        self::$process->message = $productCount.' '.$this->l('items were marked as to be created...');
                        self::$process->hide = 0;
                        self::$process->loader = 1;
                        self::$process->resubmitTimer = self::ONE_MINUTE;
                        self::$process->step = self::STEP_COMPLETED;
                    } else {
                        self::$process->message = $this->l('No items found, process completed. No new items to be created.');
                        self::$process->hide = 0;
                        self::$process->loader = 0;
                        self::$process->resubmitTimer = self::FIVE_MINUTES;
                        self::$process->step = self::STEP_COMPLETED_NOTHING;
                    }
                } else {
                    self::$process = new AmazonAutomatonProcess();
                    self::$process->resubmitTimer = self::ONE_HOUR;
                    self::$process->loader = 0;
                    self::$process->hide = 1;
                }
                break;

            case self::STEP_PROCESS_REPORT:
                if ($this->processInventory()) {
                    self::$process->resubmitTimer = self::NOW;
                    self::$process->completedDate = time();
                    self::$process->step = self::STEP_POST_PROCESS;
                    self::$process->message = $this->l('Inventory processing completed successfully.');
                    self::$process->hide = 0;
                    self::$process->loader = 1;
                } else {
                    self::$process = new AmazonAutomatonProcess();
                    self::$process->resubmitTimer = self::ONE_HOUR;
                    self::$process->loader = 1;
                    self::$process->hide = 1;
                }
                break;

            case self::STEP_GET_REPORT:
                if ($this->steps(self::STEP_GET_REPORT)) {
                    self::$process->step = self::STEP_PROCESS_REPORT;
                    self::$process->resubmitTimer = self::NOW;
                    self::$process->message = $this->l('File downloaded successfully, wait for processing');
                } else {
                    self::$process = new AmazonAutomatonProcess();
                    self::$process->resubmitTimer = self::TWO_HOURS;
                    self::$process->message = $this->l('Unable to find any inventory, the process will restart in 2 hours');
                }
                self::$process->loader = 1;
                self::$process->hide = 0;
                break;

            case self::STEP_GET_REPORT_REQUEST_LIST:
                if ($this->steps(self::STEP_GET_REPORT_REQUEST_LIST)) {
                    if (self::$process->reportId) {
                        self::$process->message = $this->l('The inventory is ready, waiting for processing');
                        self::$process->step = self::STEP_GET_REPORT;
                    } else {
                        self::$process->message = $this->l('Wait for the inventory, it should take a while, up to one hour');
                    }
                } else {
                    $message = $this->l('Unable to find any inventory, the process will restart in 2 hours');
                    self::$process = new AmazonAutomatonProcess();
                    self::$process->resubmitTimer = self::TWO_HOURS;
                    self::$process->message = $message;
                }
                self::$process->loader = 1;
                self::$process->hide = 0;
                break;

            //case self::STEP_REPORT_REQUEST:
            default:
                if (Tools::getValue('status')) {
                    self::$process->step = self::STEP_REPORT_REQUEST;
                    self::$process->hide = 1;
                    self::$process->loader = 0;
                    break;
                }
                self::$process->step = self::STEP_REPORT_REQUEST;
                self::$process->message = $this->l('Requesting an inventory to Amazon');

                if (file_exists($this->file_inventory) && !self::isExpired(filemtime($this->file_inventory), self::ONE_DAY)) {
                    // self::TWO_HOURS) )

                    self::$process->step = self::STEP_PROCESS_REPORT;
                    self::$process->message = $this->l('Inventory file already exists  and is not expired, reprocessing this feed...');
                    self::$process->resubmitTimer = self::NOW;
                    self::$process->loader = 1;
                } else {
                    if ($this->steps(self::STEP_REPORT_REQUEST)) {
                        if (self::$process->requestId) {
                            self::$process->step = self::STEP_GET_REPORT_REQUEST_LIST;
                            self::$process->message = $this->l('Request has been accepted, waiting for processing');
                            self::$process->resubmitTimer = self::ONE_MINUTE;
                            self::$process->loader = 1;
                        } else {
                            self::$process->message = $this->l('Request failed, the request will be resubmitted in a while');
                            self::$process->resubmitTimer = self::TWO_HOURS;
                        }
                    }
                }
                break;
        }
    }

    public function postProcessMarkProductsForCreation()
    {
        if ($this->debug) {
            printf('postProcessMarkProductsForCreation()'.nl2br(Amazon::LF));
        }

        $count = 0;

        $productsToCreate = AmazonProduct::getProductsToCreate($this->amazon_id_lang);

        if (is_array($productsToCreate) && count($productsToCreate)) {
            $productsToCreateUniq = array();

            foreach ($productsToCreate as $product) {
                $productsToCreateUniq[$product['id_product']] = true;
            }

            foreach ($productsToCreateUniq as $id_product => $is_true) {
                AmazonProduct::marketplaceActionSet(Amazon::ADD, (int)$id_product, null, null, $this->amazon_id_lang);
            }

            $count = count($productsToCreateUniq);
        }

        if ($this->debug) {
            printf('Products to create: %s'.nl2br(Amazon::LF), nl2br(print_r($productsToCreate, true)));
        }

        return ($count);
    }

    /*
     * Product Matching Wizard
     */

    protected function processInventory()
    {
        if ($this->debug) {
            printf('processInventory()'.nl2br(Amazon::LF));
        }

        if (($result = AmazonTools::fileGetContents($this->file_inventory)) === false) {
            //TODO: VALIDATION - Malfunctions with AmazonTools::file_get_contents.

            self::$errors[] = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Unable to read input file'), $this->file_inventory);

            return (false);
        }
        if ($result == null or empty($result)) {
            self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Inventory is empty !'));

            return (false);
        }

        $lines = explode(Amazon::LF, $result);

        if (!is_array($lines) || !count($lines)) {
            self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Inventory is empty !'));

            return (false);
        }
        if ($this->debug) {
            printf('Inventory: %s'.nl2br(Amazon::LF), nl2br(print_r($lines, true)));
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

            $ASIN = trim($ASIN);
            $SKU = trim($SKU);

            if (AmazonTools::validateSKU($SKU) && AmazonTools::validateASIN($ASIN)) {
                $amazonItems[$SKU] = $ASIN;
            } else {
                self::$errors[] = sprintf('%s(#%d): %s - %s "%s/%s"', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Wrong ASIN or SKU'), $SKU, $ASIN);
            }
        }

        if (!is_array($amazonItems) || !count($amazonItems)) {
            if ($this->debug) {
                self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Inventory is empty !'));
            }
        }
        if ($this->debug) {
            printf('Processed Items: %s'.nl2br(Amazon::LF), nl2br(print_r($amazonItems, true)));
        }
        AmazonProduct::populateProductOptions();

        // Set ASIN in product_options
        //
        foreach ($amazonItems as $SKU => $ASIN) {
            if (AmazonProduct::getIdByAsin($this->amazon_id_lang, $ASIN)) {
                continue;
            }

            $product = new AmazonProduct($SKU, false, $this->amazon_id_lang);

            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            $count++;
            AmazonProduct::updateProductOptions($product->id, $this->amazon_id_lang, 'asin1', $ASIN, $product->id_product_attribute);
        }

        return (true);
    }

    protected function steps($step)
    {
        $pass = false;

        self::$process->resubmitTimer = null;

        if ($this->debug) {
            printf('steps(%s)'.nl2br(Amazon::LF), $step);
        }

        switch ($step) {
            // Request Report Generation
            //
            case self::STEP_REPORT_REQUEST:

                if (!$this->reportRequest()) {
                    self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Failed'));

                    return (false);
                }
                $pass = true;
                break;

            // Wait for the Report Id
            //
            case self::STEP_GET_REPORT_REQUEST_LIST:

                if (!$this->reportRequestList()) {
                    self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Failed'));

                    return (false);
                }
                switch (self::$process->reportProcessingStatus) {
                    case '_SUBMITTED_':
                    case '_IN_PROGRESS_':
                        self::$process->resubmitTimer = self::TWO_MINUTES;
                        $pass = true;

                        return (true);
                    case '_CANCELLED_':
                    case '_DONE_NO_DATA_':
                        self::$process = new AmazonAutomatonProcess();
                        $pass = false;
                        exit;

                    case '_DONE_':
                        self::$process->resubmitTimer = self::NOW;
                        self::$process->message = $this->l('File downloaded successfully, wait for processing');
                        $pass = true;
                        break;
                }
                break;

            // Download Report
            //
            case self::STEP_GET_REPORT:

                if (!$this->getReport()) {
                    self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Failed'));

                    return (false);
                }
                $pass = true;
                break;

        }

        return ($pass);
    }

    protected function reportRequest()
    {
        $params = array();
        $params['Action'] = 'RequestReport';
        $params['ReportType'] = '_GET_FLAT_FILE_OPEN_LISTINGS_DATA_';
        $params['Version'] = '2009-01-01';
        $params['MarketplaceIdList.Id.1'] = $this->marketplaceId;

        if ($this->debug) {
            printf('reportRequest()'.nl2br(Amazon::LF));
        }

        $xml = $this->ws->simpleCallWS($params, 'Reports');

        if (!$xml instanceof SimpleXMLElement or isset($xml->Error)) {
            self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Failed'));

            return (false);
        }

        self::$xml = $this->debugXML($xml);

        if (!isset($xml->RequestReportResult->ReportRequestInfo->ReportProcessingStatus) || !isset($xml->RequestReportResult->ReportRequestInfo->ReportRequestId)) {
            self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Failed'));

            return (false);
        }
        if ($xml->RequestReportResult->ReportRequestInfo->ReportProcessingStatus == '_SUBMITTED_') {
            self::$process->requestReportTime = time();
            self::$process->requestId = (string)$xml->RequestReportResult->ReportRequestInfo->ReportRequestId;

            return (true);
        } else {
            return (false);
        }
    }

    public function debugXML($xml)
    {
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return AmazonTools::pre(array(htmlspecialchars($dom->saveXML())), true);
    }

    /*
     * Product Creation Wizard
     */

    protected function reportRequestList()
    {
        $params = array();
        $params['Version'] = '2009-01-01';
        $params['Action'] = 'GetReportRequestList';
        $params['ReportRequestList.Type.1'] = '_GET_FLAT_FILE_OPEN_LISTINGS_DATA_';
        $params['ReportRequestIdList.Id.1'] = self::$process->requestId;

        if ($this->debug) {
            printf('reportRequestList()'.nl2br(Amazon::LF));
        }

        $xml = $this->ws->simpleCallWS($params, 'Reports');

        if (!$xml instanceof SimpleXMLElement or isset($xml->Error)) {
            self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Failed'));

            return (false);
        }

        self::$xml = $this->debugXML($xml);

        if (!isset($xml->GetReportRequestListResult->ReportRequestInfo->ReportProcessingStatus)) {
            self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Failed'));

            return (false);
        }

        self::$process->reportProcessingStatus = (string)$xml->GetReportRequestListResult->ReportRequestInfo->ReportProcessingStatus;

        if (isset($xml->GetReportRequestListResult->ReportRequestInfo->GeneratedReportId) && (int)$xml->GetReportRequestListResult->ReportRequestInfo->GeneratedReportId) {
            self::$process->reportTime = time();
            self::$process->reportId = (string)$xml->GetReportRequestListResult->ReportRequestInfo->GeneratedReportId;
        }

        return (true);
    }

    protected function getReport()
    {
        $params = array();
        $params['Version'] = '2009-01-01';
        $params['Action'] = 'GetReport';
        $params['ReportId'] = self::$process->reportId;

        if ($this->debug) {
            printf('getReport()'.nl2br(Amazon::LF));
        }

        $result = $this->ws->simpleCallWS($params, 'Reports', false);

        if (empty($result)) {
            self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Inventory is empty'));

            return (false);
        }

        if (file_put_contents($this->file_inventory, $result) === false) {
            self::$errors[] = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, self::$steps[self::$process->step], $this->l('Unable to write to output file'), $this->file_inventory);

            return (false);
        }

        return (true);
    }

    public function startMatchingWizard()
    {
        if (Tools::getValue('force') == true) {
            $forceUpdate = true;
        } else {
            $forceUpdate = false;
        }

        if ($this->debug) {
            printf('startMatchingWizard()'.nl2br(Amazon::LF));
        }

        $flag = html_entity_decode('&lt;img src="'.$this->images.'geo_flags/'.$this->region.'.gif" alt="Amazon '.Tools::strtoupper($this->region).'" /&gt;');
        $title = sprintf('%s %s', $this->l('Amazon Marketplace Automaton started on'), AmazonTools::displayDate(date('Y-m-d h:i:s'), $this->id_lang, true));

        $amazon_synch = AmazonConfiguration::get('WIZARD_MATCHING_STATE');

        if (!is_array($amazon_synch)) {
            $amazon_synch = array();
        }

        if (isset($amazon_synch[$this->amazon_id_lang])) {
            self::$process = $amazon_synch[$this->amazon_id_lang];

            if (!self::$process instanceof AmazonAutomatonProcess) {
                self::$process = new AmazonAutomatonProcess();
            }
        } else {
            self::$process = new AmazonAutomatonProcess();
        }

        if (self::$process->requestReportTime && self::isExpired(self::$process->requestReportTime, self::ONE_DAY)) {
            self::$process = new AmazonAutomatonProcess();
        }

        $this->matchingWizardProcess();

        self::$process->title = $title;
        self::$process->flag = $flag;
        self::$process->marketplace = sprintf('Amazon %s', Tools::strtoupper($this->region));

        $amazon_synch[$this->amazon_id_lang] = self::$process;

        AmazonConfiguration::updateValue('WIZARD_MATCHING_STATE', $amazon_synch, true);
    }

    /*
     *
     * PART 1 - Get Asynchronously the Inventory Report From Amazon
     *
     */

    public function matchingWizardProcess()
    {
        switch (self::$process->step) {
            case self::STEP_NOTHING:
                break;

            case self::STEP_COMPLETED_NOTHING:
                self::$process = new AmazonAutomatonProcess();
                self::$process->resubmitTimer = 0;
                self::$process->step = null;
                self::$process->loader = 0;
                self::$process->hide = 1;
                break;

            case self::STEP_COMPLETED:
                $message = $this->l('Processing completed, you can send your feed to Amazon.');
                self::$process = new AmazonAutomatonProcess();
                self::$process->resubmitTimer = self::ONE_MINUTE;
                self::$process->step = self::STEP_COMPLETED_NOTHING;
                self::$process->loader = 0;
                self::$process->hide = 0;
                self::$process->message = $message;
                break;

            case self::STEP_POST_PROCESS:
                if (self::$process->completedDate && !self::isExpired(self::$process->completedDate, self::ONE_DAY)) {
                    $productsToSynch = $this->postProcessMarkProductsForSynch();

                    self::$process->completedDate = time();

                    if (is_array($productsToSynch) && count($productsToSynch)) {
                        self::$process->message = count($productsToSynch).' '.$this->l('items were detected, please wait for next step...');
                        self::$process->hide = 0;
                        self::$process->loader = 1;
                        self::$process->resubmitTimer = self::NOW;
                        self::$process->step = self::STEP_COMPLETED;

                        self::$products = $productsToSynch;
                    } else {
                        self::$process->message = $this->l('Sorry, no items found, process completed. No items to be synchronized.');
                        self::$process->hide = 0;
                        self::$process->loader = 0;
                        self::$process->resubmitTimer = self::FIVE_MINUTES;
                        self::$process->step = self::STEP_COMPLETED_NOTHING;
                    }
                } else {
                    self::$process = new AmazonAutomatonProcess();
                    self::$process->resubmitTimer = self::ONE_HOUR;
                    self::$process->loader = 0;
                    self::$process->hide = 1;
                }
                break;

            case self::STEP_PROCESS_REPORT:
                if ($this->processInventory()) {
                    self::$process->resubmitTimer = self::NOW;
                    self::$process->completedDate = time();
                    self::$process->step = self::STEP_POST_PROCESS;
                    self::$process->message = $this->l('Inventory processing completed.');
                    self::$process->hide = 0;
                    self::$process->loader = 1;
                } else {
                    self::$process = new AmazonAutomatonProcess();
                    self::$process->resubmitTimer = self::ONE_HOUR;
                    self::$process->loader = 1;
                    self::$process->hide = 1;
                }
                break;

            case self::STEP_GET_REPORT:

                if ($this->steps(self::STEP_GET_REPORT)) {
                    self::$process->step = self::STEP_PROCESS_REPORT;
                    self::$process->resubmitTimer = self::NOW;
                    self::$process->message = $this->l('File downloaded successfully, wait for processing');
                } else {
                    self::$process = new AmazonAutomatonProcess();
                    self::$process->resubmitTimer = self::TWO_HOURS;
                    self::$process->message = $this->l('Unable to find any inventory, the process will restart in 2 hours');
                }
                self::$process->loader = 1;
                self::$process->hide = 0;
                break;

            case self::STEP_GET_REPORT_REQUEST_LIST:

                if ($this->steps(self::STEP_GET_REPORT_REQUEST_LIST)) {
                    if (self::$process->reportId) {
                        self::$process->message = $this->l('The inventory is ready, wait for processing');
                        self::$process->step = self::STEP_GET_REPORT;
                    } else {
                        self::$process->message = $this->l('Wait for the inventory, it should take a while, up to one hour');
                    }
                } else {
                    $message = $this->l('Unable to find any inventory, the process will restart in 2 hours');
                    self::$process = new AmazonAutomatonProcess();
                    self::$process->resubmitTimer = self::TWO_HOURS;
                    self::$process->message = $message;
                }
                self::$process->loader = 1;
                self::$process->hide = 0;
                break;

            case self::STEP_REPORT_REQUEST:
            default:
                if (Tools::getValue('status')) {
                    self::$process->step = self::STEP_REPORT_REQUEST;
                    self::$process->hide = 0;
                    self::$process->loader = 0;
                    break;
                }
                self::$process->step = self::STEP_REPORT_REQUEST;
                self::$process->message = $this->l('Requesting an inventory to Amazon');

                if (file_exists($this->file_inventory) && !self::isExpired(filemtime($this->file_inventory), self::ONE_DAY * 15)) {
                    // self::TWO_HOURS) )

                    self::$process->step = self::STEP_PROCESS_REPORT;
                    self::$process->message = $this->l('Inventory file exists already and is not expired, reprocessing this feed...');
                    self::$process->resubmitTimer = self::NOW;
                    self::$process->loader = 1;
                    self::$process->hide = 0;
                } else {
                    if ($this->steps(self::STEP_REPORT_REQUEST)) {
                        if (self::$process->requestId) {
                            self::$process->step = self::STEP_GET_REPORT_REQUEST_LIST;
                            self::$process->message = $this->l('Request has been accepted, wait for processing');
                            self::$process->resubmitTimer = self::ONE_MINUTE;
                            self::$process->loader = 1;
                        } else {
                            self::$process->message = $this->l('Request failed, the request will be resubmitted in a while');
                            self::$process->resubmitTimer = self::TWO_HOURS;
                        }
                    }
                }
                break;
        }
    }

    /*
    <RequestReportResponse xmlns="http://mws.amazonaws.com/doc/2009-01-01/">
      <RequestReportResult>
        <ReportRequestInfo>
          <ReportRequestId>6859793590</ReportRequestId>
          <ReportType>_GET_MERCHANT_OPEN_LISTINGS_DATA_</ReportType>
          <StartDate>2013-11-15T10:03:30+00:00</StartDate>
          <EndDate>2013-11-15T10:03:30+00:00</EndDate>
          <Scheduled>false</Scheduled>
          <SubmittedDate>2013-11-15T10:03:30+00:00</SubmittedDate>
          <ReportProcessingStatus>_SUBMITTED_</ReportProcessingStatus>
        </ReportRequestInfo>
      </RequestReportResult>
      <ResponseMetadata>
        <RequestId>c9771d62-972f-4a02-8c3e-f19fd066431e</RequestId>
      </ResponseMetadata>
    </RequestReportResponse>
     */

    public function postProcessMarkProductsForSynch()
    {
        if ($this->debug) {
            printf('postProcessMarkProductsForSynch()'.nl2br(Amazon::LF));
        }

        $productsToSynch = AmazonProduct::getProductsToSynch($this->amazon_id_lang);

        if (is_array($productsToSynch) && count($productsToSynch)) {
            $productsToSynchUniq = array();

            foreach ($productsToSynch as $product) {
                $productCode = $product['ean13'] > 0 ? $product['ean13'] : $product['upc'];

                if (AmazonTools::validateSKU($product['reference']) && !AmazonTools::eanUpcisPrivate($productCode) && AmazonTools::eanUpcCheck($productCode)) {
                    if (version_compare(_PS_VERSION_, '1.5', '<')) {
                        $quantityFunction = 'getQuantity';
                    } else {
                        $quantityFunction = 'getRealQuantity';
                    }

                    $productQuantity = Product::$quantityFunction((int)$product['id_product'], $product['id_product_attribute'] ? (int)$product['id_product_attribute'] : null);

                    if (!$productQuantity) {
                        continue;
                    }

                    $productsToSynchUniq[$product['reference']] = $product;
                    $productsToSynchUniq[$product['reference']]['checked'] = false;

                    $productsToSynchUniq[$product['reference']]['name'] = self::encodeText(AmazonProduct::getProductName($product['id_product'], $product['id_product_attribute'], $this->id_lang));
                    $productsToSynchUniq[$product['reference']]['manufacturer'] = Manufacturer::getNameById((int)$product['id_manufacturer']);

                    $productImages = AmazonTools::getProductImages($product['id_product'], $product['id_product_attribute'], $this->id_lang);

                    if (isset($productImages[0]) && !empty($productImages[0])) {
                        $productsToSynchUniq[$product['reference']]['image_url'] = $this->images_url.$productImages[0];
                    } else {
                        $productsToSynchUniq[$product['reference']]['image_url'] = null;
                    }
                }
            }

            return ($productsToSynchUniq);
        }

        return (null);
    }

    /*
    <GetReportRequestListResult>
      <NextToken/>
      <HasNext>false</HasNext>
      <ReportRequestInfo>
        <ReportRequestId>6839626392</ReportRequestId>
        <ReportType>_GET_MERCHANT_OPEN_LISTINGS_DATA_</ReportType>
        <StartDate>2013-11-13T06:21:51+00:00</StartDate>
        <EndDate>2013-11-13T06:21:51+00:00</EndDate>
        <Scheduled>false</Scheduled>
        <SubmittedDate>2013-11-13T06:21:51+00:00</SubmittedDate>
        <ReportProcessingStatus>_DONE_</ReportProcessingStatus>
        <GeneratedReportId>27152286424</GeneratedReportId>
        <StartedProcessingDate>2013-11-13T06:56:01+00:00</StartedProcessingDate>
        <CompletedDate>2013-11-13T06:56:03+00:00</CompletedDate>
      </ReportRequestInfo>
    */

    public static function encodeText($text)
    {
        return (str_replace(array('&', '"', "'", '\\', '<', '>'), '', $text));
    }

    /*
     * Returns raw result ; CSV Tab separated file
     */

    public function matchProducts()
    {
        $params = array();
        $params['Action'] = 'getMatchingProductForId';
        $params['MarketplaceId'] = $this->marketplaceId;
        $this->debug = true;
        if ($this->debug) {
            printf('matchProducts()'.nl2br(Amazon::LF));
        }

        if (($type = Tools::getValue('type')) == 'ean13') {
            $params['IdType'] = 'EAN';
        } elseif ($type == 'upc') {
            $params['IdType'] = 'UPC';
        } else {
            self::$errors[] = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $this->l('Failed'));
            self::$products = null;

            return (false);
        }

        $json = Tools::getValue('products');

        $products = Tools::jsonDecode($json, true);

        if ($this->debug) {
            printf('Input products: %s', nl2br(print_r($products, true)));
        }

        $productCount = 0;
        $productList = array();
        $productUniq = array();
        $productTypeValueUniq = array();

        // mark as to be matched
        if (is_array($products) && count($products)) {
            foreach ($products as $key => $product) {
                if (empty($product[$type])) {
                    continue;
                }

                if (isset($productUniq[$key]) || isset($productTypeValueUniq[$product[$type]])) {
                    unset($products[$key]);
                    continue;
                }
                $productUniq[$key] = true;
                $productTypeValueUniq[$product[$type]] = true;

                // Previously checked, remove from the list
                if (isset($products[$key]['checked']) && $products[$key]['checked']) {
                    unset($products[$key]);
                    continue;
                }
            }
            foreach ($products as $key => $product) {
                if (empty($product[$type])) {
                    continue;
                }

                $products[$key]['checked'] = true;
                $products[$key]['amazon'] = array();
                $products[$key]['amazon']['name'] = null;
                $products[$key]['amazon']['image_url'] = null;
                $products[$key]['amazon']['asin'] = null;

                $productList[] = $key;

                $params['IdList.Id.'.($productCount + 1)] = sprintf('%013s', $product[$type]);

                if (++$productCount >= 5) {
                    break;
                }
            }
        }
        if ($this->debug) {
            printf('Product Count: %s'.nl2br(Amazon::LF), $productCount);
            printf('Marked products: %s'.nl2br(Amazon::LF), nl2br(print_r($products, true)));
        }


        if ($productCount) {
            sleep(1); // anti-throttling - 1 second is efficient because recovery time is 5 products per second (1 query)

            $xml = $this->ws->simpleCallWS($params, 'Products');

            if ($this->debug) {
                self::$xml .= $xml;
            }

            if (!$xml instanceof SimpleXMLElement or isset($xml->Error) or !isset($xml->GetMatchingProductForIdResult)) {
                if (isset($xml->Error->Message)) {
                    $error_msg = (string)$xml->Error->Message;
                } elseif (!isset($xml->GetMatchingProductForIdResult) || !$xml->GetMatchingProductForIdResult instanceof SimpleXMLElement) {
                    $error_msg = 'XML GetMatchingProductForIdResult was expected';
                } else {
                    $error_msg = 'XML was expected';
                }

                self::$errors[] = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $this->l('Failed'), $error_msg);
                self::$products = null;

                return (false);
            }

            foreach ($xml->GetMatchingProductForIdResult as $result) {
                $attributes = $result->attributes();
                $Status = (string)$attributes->status;

                if ($Status != 'Success') {
                    continue;
                }

                $ProductId = (string)$attributes->Id;

                foreach ($products as $key => $product) {
                    // product matched
                    if ($product[$type] == $ProductId) {
                        $Item = $result->Products->Product->AttributeSets->children('ns2', true);
                        $Identifiers = $result->Products->Product->Identifiers;

                        $products[$key]['amazon'] = array();

                        if (isset($Item->ItemAttributes->SmallImage->URL)) {
                            $products[$key]['amazon']['image_url'] = trim((string)$Item->ItemAttributes->SmallImage->URL);
                        } else {
                            $products[$key]['amazon']['image_url'] = '';
                        }

                        if (isset($Item->ItemAttributes->Brand)) {
                            $products[$key]['amazon']['brand'] = self::encodeText(trim((string)$Item->ItemAttributes->Brand));
                        } else {
                            $products[$key]['amazon']['brand'] = '';
                        }

                        if (preg_replace('/[^A-Za-z0-9]/', '', $products[$key]['amazon']['brand']) != preg_replace('/[^A-Za-z0-9]/', '', $products[$key]['manufacturer'])) {
                            $products[$key]['brand_mismatch'] = true;
                        } else {
                            $products[$key]['brand_mismatch'] = false;
                        }

                        if (isset($Item->ItemAttributes->Title)) {
                            $products[$key]['amazon']['name'] = self::encodeText(trim((string)$Item->ItemAttributes->Title));
                        } else {
                            $products[$key]['amazon']['name'] = '';
                        }

                        if (isset($Identifiers->MarketplaceASIN->ASIN)) {
                            $products[$key]['amazon']['asin'] = trim((string)$Identifiers->MarketplaceASIN->ASIN);
                            $products[$key]['matched'] = true;
                        } else {
                            $products[$key]['amazon']['asin'] = '';
                        }

                        break;
                    }
                }
            }

            // Mark unmatched as processed, but unmatched
            foreach ($productList as $key) {
                if (!isset($products[$key]['matched']) || !$products[$key]['matched']) {
                    $products[$key]['matched'] = false;
                }
            }
        }
        self::$products = $products;

        if ($this->debug) {
            printf('Returned products: %s'.nl2br(Amazon::LF), nl2br(print_r($products, true)));
        }

        return (true);
    }

    /*
     *
     * PART 2 - Manage the Inventory
     *
     */

    public function confirmProducts()
    {
        if ($this->debug) {
            printf('confirmProducts()'.nl2br(Amazon::LF));
        }

        $products = Tools::getValue('matched');
        $products_result = array();

        sleep(2);

        if (is_array($products) && count($products)) {
            foreach ($products as $reference => $product) {
                if (!$product['id_product']) {
                    continue;
                } elseif (!$product['asin']) {
                    continue;
                }

                if (AmazonProduct::updateProductOptions((int)$product['id_product'], $this->amazon_id_lang, 'asin1', $product['asin'], (int)$product['id_product_attribute'])) {
                    if (AmazonProduct::marketplaceActionSet(Amazon::UPDATE, (int)$product['id_product'], null, null, $this->amazon_id_lang)) {
                        $product['update'] = true;
                        $products_result[$reference] = $product;
                    }
                }
            }
        }
        if (is_array($products_result) && count($products_result)) {
            self::$products = $products_result;
        } else {
            self::$products = null;
        }

        return (true);
    }
}

$amazonAutomaton = new AmazonAutomaton();
$amazonAutomaton->dispatch();
