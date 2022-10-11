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

class AmazonShippingConfig extends Amazon
{
    const MERCHANT_FBA_LISTINGS_DATA = 'fba_listings_data';
    const EXPIRE = 14400; //4 hours

    public $import = null;
    public $ws = null;
    public $marketplaceId = null;
    public $region = null;
    public $file_inventory = null;
    public $merchantId = null;

    public static $errors = array();
    public static $messages = array();

    private static $targets_id_lang = array();

    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }

    public function dispatch()
    {
        $this->import = $this->path.'import/';

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
        $token = Tools::getValue('instant_token');

        if (!$token || $token != Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0)) {
            print 'Wrong token';
            die;
        }

        switch (Tools::getValue('action')) {
            case 'inventory':
                $this->getFbaInventory();
                break;
            case 'delete':
                $this->deleteCache();
                break;
            default:
                die('Wrong action');
                break;
        }
    }

    public function setFileInventory()
    {
        $fileid = floor((time() % (86400 * 365)) / self::EXPIRE); // file id valid for 4 hours

        $this->file_inventory = sprintf('%s%s_%s_%s_%s.raw', $this->import, self::MERCHANT_FBA_LISTINGS_DATA, $this->merchantId, $this->region, $fileid);
        return;
    }

    public function deleteCache()
    {
        $pass = false;

        if ($this->initFbaProcess()) {
            $file = basename($this->file_inventory);

            if (file_exists($this->file_inventory)) {
                $pass = unlink($this->file_inventory);

                if ($pass) {
                    self::$messages[] = sprintf($this->l('File "%s" deleted sucessfully'), $file);
                } else {
                    self::$errors[] = sprintf($this->l('Unable to delete this file - "%s"'), $file);
                }
            } else {
                self::$messages[] = sprintf($this->l('File "%s" doesn\'t exist'), $file);
            }
        } else {
            self::$errors[] = sprintf($this->l('An unexepected error occured'));
        }

        $result =
            array(
                'error' => (count(self::$errors) ? true : false),
                'errors' => self::$errors,
                'message' => count(self::$messages) ? true : false,
                'messages' => self::$messages,
                'continue' => false,
                'pass' => false,
                'debug' => Amazon::$debug_mode,
                'output' => ob_get_clean()
            );

        $json = Tools::jsonEncode($result);

        if ($callback = Tools::getValue('callback')) {
            if ($callback == '?') {
                $callback = 'jsonp_'.time();
            }
            echo (string)$callback.'('.$json.')';
            die;
        } else {
            CommonTools::d($result);
        }
    }
    public function getFbaInventory()
    {
        ob_start();

        $pass = false;
        $continue = false;
        $fba_entries = array();
        $id_lang = $this->context->language->id;

        $id_warehouse = (int)Configuration::get('AMAZON_WAREHOUSE');
        $id_warehouse = $id_warehouse ? $id_warehouse : null;

        $updated = 0;
        $switched = 0;
        $log = true;

        if ($this->initFbaProcess()) {
            if (file_exists($this->file_inventory) && filesize($this->file_inventory) && filemtime($this->file_inventory) > time() - self::EXPIRE) {
                self::$messages[] = sprintf($this->l('Using existings file: "%s" - Expires: %s'), basename($this->file_inventory), date('Y-m-d H:i:s', filemtime($this->file_inventory) + self::EXPIRE));

                // Inventory Exists, and downloaded, process the report
                $fba_entries = $this->processInventory();

                if (is_array($fba_entries) && count($fba_entries)) {
                    foreach ($fba_entries as $fba_entry) {
                        $SKU = $fba_entry['sku'];
                        $quantity = $fba_entry['quantity'];
                        $condition_code = $fba_entry['condition_code'];

                        if (!AmazonTools::validateSKU($SKU)) {
                            $error = sprintf('%s: "%s"', $this->l('Invalid SKU'));
                            $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);

                            if (Amazon::$debug_mode) {
                                CommonTools::p($debug);
                            }
                            continue;
                        }

                        $product = new AmazonProduct($SKU, false, $id_lang);

                        if (!Validate::isLoadedObject($product)) {
                            $error = sprintf('%s - %s(%s)', $this->l('Unable to find product'), $product->name, $SKU);
                            $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);

                            if (Amazon::$debug_mode) {
                                CommonTools::p($debug);
                            }
                            continue;
                        }
                        $id_product = (int)$product->id;
                        $id_product_attribute = (int)$product->id_product_attribute ? (int)$product->id_product_attribute : null;

                        $options = AmazonProduct::getProductOptions($id_product, $id_lang, $id_product_attribute);

                        if (is_array($options) && isset($options['disable']) && (bool)$options['disable']) {
                            $disabled = true;
                        } else {
                            $disabled = false;
                        }

                        if (is_array($options) && isset($options['fba']) && (bool)$options['fba']) {
                            $fba = true;
                        } else {
                            $fba = false;
                        }
                        if (!$quantity || $disabled) {
                            // Became out of stock

                            if ($fba) {
                                // Turns Product to MFN for all targets marketplaces
                                foreach (self::$targets_id_lang as $marketplace_id_lang) {
                                    AmazonProduct::updateProductOptions($id_product, $marketplace_id_lang, 'fba', false, $id_product_attribute);
                                }
                                $switched++;
                            }
                        } elseif ($quantity && !$disabled) {
                            if (!$fba) {
                                // Turns Product to AFN for all targets marketplaces
                                foreach (self::$targets_id_lang as $marketplace_id_lang) {
                                    AmazonProduct::updateProductOptions($id_product, $marketplace_id_lang, 'fba', true, $id_product_attribute);
                                }
                                $switched++;
                            }
                        }

                        $product_quantity = Product::getRealQuantity($id_product, $id_product_attribute, $id_warehouse, $this->context->shop->id);
                        $product_quantity_fba = $quantity;

                        if ($product_quantity < 0) {
                            $product_quantity = 0;
                        }

                        if ($product_quantity > $product_quantity_fba) {
                            $delta = ($product_quantity - $product_quantity_fba) * -1;
                        } else {
                            $delta = $product_quantity_fba - $product_quantity;
                        }

                        if ($delta == 0) {
                            if ($log) {
                                $message = sprintf('%s - %s (%d)', $SKU, $this->l('Stock already up to date'), $product_quantity);
                            }
                        } elseif (StockAvailable::updateQuantity($id_product, $id_product_attribute, $delta, $this->context->shop->id)) {
                            if ($log) {
                                $message = sprintf('%s - %s (%d/%d)', $SKU, $this->l('Stock Updated'), $product_quantity, $delta);
                            }
                            $updated++;
                        } else {
                            $message = ' - '.$this->l('Stock Update FAILED');
                        }
                        self::$messages[] = $message;

                        if (count(self::$messages) > 100) {
                            $log = false;
                            self::$messages[] = $this->l('More than 100 SKU have been logged, next messages will be ignored, but the action will be performed and summarized at the end');
                        }

                        // Log the event
                        AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $id_product);
                    }
                    self::$messages[] = $message = sprintf('%d %s - %d %s', is_array($switched) ? count($switched) : 0, $this->l('FBA offers switched'), $updated, $this->l('Stock movements'));

                    $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $message);

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                    }
                    if ($updated || $switched) {
                        if (Tools::getValue('fba_stock_behaviour') == Amazon::FBA_STOCK_SYNCH) {
                            Configuration::updateValue('AMAZON_FBA_STOCK_BEHAVIOUR', Amazon::FBA_STOCK_SYNCH);
                        }
                        $pass = true;
                    }
                } else {
                    $error = $this->l('FBA inventory is empty');
                    $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
                    self::$errors[] = $error;

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                    }
                }
                $continue = false;
            } elseif (file_exists($this->file_inventory) && !filesize($this->file_inventory)) {
                // Inventory Exists, but has not been downloaded

                // Check Timestamp
                // 1 - if timestamp more than 2 minutes; get report
                // 2 - if less ; ask to wait

                $request_time = filemtime($this->file_inventory);
                $now = time();
                $elapsed = $now - $request_time;

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - Request Time: "%s", elapsed: %d', basename(__FILE__), __LINE__, __FUNCTION__, date('c', $request_time), $elapsed));
                }

                if ($elapsed > 60 * 60) {
                    $error = $this->l('Delay to download report is expired');
                    $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
                    self::$errors[] = $error;

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                        CommonTools::p(sprintf('%s(#%d): %s - ERROR: Request Time: "%s", elapsed: %d - delay expired', basename(__FILE__), __LINE__, __FUNCTION__, date('c', $request_time), $elapsed));
                    }
                    unlink($this->file_inventory);
                    $continue = false;
                    $pass = false;
                } elseif ($elapsed < 60 * 2) {
                    $continue = true;
                    $pass = true;

                    self::$messages[] = $message = $this->l('Waiting a while for the report to be ready for download');
                    $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $message);

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                    }
                } else {
                    $reportRequestId = $this->reportRequestList();

                    if ($reportRequestId) {
                        if ($this->getReport($reportRequestId)) {
                            self::$messages[] = $message = sprintf('%s (%s)', $this->l('Downloading Report ID'), $reportRequestId);
                            $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $message);

                            if (Amazon::$debug_mode) {
                                CommonTools::p($debug);
                            }

                            $continue = true;
                            $pass = true;
                        } else {
                            $error = $this->l('Failed to download the Report');
                            $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
                            self::$errors[] = $error;

                            if (Amazon::$debug_mode) {
                                CommonTools::p($debug);
                            }
                            $continue = false;
                            $pass = false;
                        }
                    } else {
                        self::$messages[] = sprintf('%s (%s)', $this->l('Waiting for the report to be available... this operation could take time'), $reportRequestId);

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s(#%d): %s - A report has been already requested and there is not any available report yet', basename(__FILE__), __LINE__, __FUNCTION__));
                        }
                        touch($this->file_inventory);
                        $continue = true;
                        $pass = true;
                    }
                }
            } else {
                // File doesn't exist
                // 1 - Create the file
                // 2 - Request the Report

                if (!AmazonTools::isDirWriteable($this->import)) {
                    $error = sprintf('"%s" %s', $this->import, $this->l('is not a writable directory, please check directory permissions'));
                    $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error);
                    self::$errors[] = $error;

                    if (Amazon::$debug_mode) {
                        CommonTools::p("Error:$debug");
                    }
                    $continue = false;
                    $pass = false;
                }
                if (file_put_contents($this->file_inventory, null) === false) {
                    $error = sprintf('%s: "%s"', $this->import, $this->l('failed to create file'), $this->file_inventory);
                    $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error);
                    self::$errors[] = $error;

                    if (Amazon::$debug_mode) {
                        CommonTools::p("Error:$debug");
                    }
                    $continue = false;
                    $pass = false;
                }

                if ($reportRequestId = $this->reportRequest()) {
                    touch($this->file_inventory);

                    self::$messages[] = sprintf($this->l('Report has been requested (%s), please wait a while'), $reportRequestId);

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s(#%d): %s - Report Request ID: "%s"', basename(__FILE__), __LINE__, __FUNCTION__, $reportRequestId));
                    }
                    $continue = true;
                    $pass = true;
                } else {
                    $error = $this->l('Request Report failed, please review your module configuration');
                    $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error);
                    self::$errors[] = $error;

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                    }
                    $continue = false;
                    $pass = false;
                }
            }
        }
        $result =
            array(
                'error' => (count(self::$errors) ? true : false),
                'errors' => self::$errors,
                'message' => count(self::$messages) ? true : false,
                'messages' => self::$messages,
                'groups' => count($fba_entries) ? $fba_entries : null,
                'continue' => $continue,
                'pass' => $pass,
                'debug' => Amazon::$debug_mode,
                'output' => ob_get_clean()
            );

        $json = Tools::jsonEncode($result);

        if ($callback = Tools::getValue('callback')) {
            if ($callback == '?') {
                $callback = 'jsonp_'.time();
            }
            echo (string)$callback.'('.$json.')';
            die;
        } else {
            CommonTools::d($result);
        }
    }

    public function initFbaProcess()
    {
        $lang = Tools::getValue('lang');
        $token = Tools::getValue('instant_token');

        if (!$token || $token != Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0)) {
            print 'Wrong token';
            die;
        }

        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        if (!is_array($marketPlaceRegion) || !count($marketPlaceRegion)) {
            $error  = $this->l('Module is not configured yet');
            self::$errors = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$error");
            }
            return (false);
        }

        $marketLang2Region = array_flip($marketPlaceRegion);

        if (!Tools::getValue('europe') && Tools::strlen($lang)) {
            if (!isset($marketLang2Region[$lang]) || !$marketLang2Region[$lang]) {
                die('No selected language, nothing to do...');
            }
        }

        if ((int)Tools::getValue('europe')) {
            $masterMarketplace = AmazonConfiguration::get('MASTER');

            if (isset($marketLang2Region[$masterMarketplace]) && $marketLang2Region[$masterMarketplace]) {
                $id_lang = $marketLang2Region[$masterMarketplace];
            } else {
                die('The module is not yet configured for Europe');
            }

            $targets_id_lang = array();
            $targets_id_lang[$id_lang] = $id_lang; // at least contains the master marketplace

            foreach ($marketPlaceIds as $marketplace_id_lang => $marketPlaceId) {
                if (AmazonTools::isEuropeMarketplaceId($marketPlaceId)) {
                    $targets_id_lang[$marketplace_id_lang] = $marketplace_id_lang;
                }
            }
            $this->europe = 1;
        } else {
            if (!($lang = Tools::getValue('lang'))) {
                die(Tools::displayError('Missing parameter lang'));
            }

            if (!isset($marketLang2Region[$lang]) || empty($marketLang2Region[$lang])) {
                die(Tools::displayError('Wrong parameter lang'));
            }

            $id_lang = (int)$marketLang2Region[$lang];
            $this->europe = false;

            // For outside Europe contains only 1 marketplace
            $targets_id_lang = array();
            $targets_id_lang[$id_lang] = $id_lang; // at least contains the master marketplace
        }
        self::$targets_id_lang = $targets_id_lang;

        $amazon = AmazonTools::selectPlatforms($id_lang, 0);

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

            self::$errors = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$error");
            }
            return (false);
        }
        return(true);
    }

    protected function processInventory()
    {
        if (Amazon::$debug_mode) {
            printf('processInventory()'.nl2br(Amazon::LF));
        }

        if (($result = AmazonTools::fileGetContents($this->file_inventory)) === false) {
            $error = $this->l('Unable to read input file');
            $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        if ($result == null or empty($result)) {
            $error = $this->l('Inventory is empty !');
            $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        $lines = explode(Amazon::LF, $result);

        if (!is_array($lines) || !count($lines)) {
            $error = $this->l('Inventory is empty !');
            $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(str_repeat('-', 160));
            CommonTools::p(sprintf('Inventory: %s products', count($lines)));
        }

        $header = reset($lines);

        if (!Tools::strlen($header)) {
            $error = $this->l('No header, file might be corrupted');
            $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        $columns = explode("\t", AmazonTools::noAccents(str_replace(' ', '-', Tools::strtolower(utf8_encode($header)))));
        $columns = array_map('trim', $columns);

        $seller_sku_idx = $this->getColumIndex($columns, array('seller-sku'));
        $asin_idx = $this->getColumIndex($columns, array('asin'));
        $condition_code_idx = $this->getColumIndex($columns, array('warehouse-condition-code'));
        $quantity_idx = $this->getColumIndex($columns, array('quantity-available'));
        $columns_count = count($columns);

        $count = 0;
        $fba_entries = array();

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            if ($count++ < 1) {
                continue;
            }

            $result = explode("\t", $line);

            if (count($result) < $columns_count) {
                continue;
            }

            $seller_sku = $result[$seller_sku_idx];
            $asin = $result[$asin_idx];
            $condition_code = $result[$condition_code_idx];
            $quantity = (int)$result[$quantity_idx];

            $fba_entries[$seller_sku] = array('sku' => $seller_sku, 'asin' => $asin, 'condition_code' => $condition_code, 'quantity' => $quantity);
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%-30s%-16s%-16s%-4s'.Amazon::LF, 'SKU', 'ASIN', 'Condition Code', 'Quantity'));
            foreach ($fba_entries as $entry) {
                CommonTools::p(sprintf('%-30s%-16s%-16s%-4s'.Amazon::LF, $entry['sku'], $entry['asin'], $entry['condition_code'], $entry['quantity']));
            }
        }

        if (!is_array($fba_entries) || !count($fba_entries)) {
            $error = sprintf('%s(#%d): %s - Empty FBA listing', basename(__FILE__), __LINE__, __FUNCTION__);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$error");
            }
            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Processed Items: %s', print_r($fba_entries, true)));
        }

        return ($fba_entries);
    }

    private function getColumIndex($columns, $keys)
    {
        $array_keys = array_intersect($columns, $keys);

        // Header, display to the user he doesn't have merchant shipping group
        if (!is_array($array_keys) || !count($array_keys)) {
            $error = sprintf('%s: %s', $this->l('Missing Column'), print_r($keys, true));
            $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }
        $columns_keys = array_flip($columns);

        $result = $columns_keys[reset($array_keys)];

        if (!is_numeric($result)) {
            return(false);
        } else {
            return($result);
        }
    }

    private function filelistImportDirectory()
    {
        // Generic function sorting files by date
        $output_dir = sprintf('%s/', rtrim($this->import, '/'));

        if (!is_dir($output_dir)) {
            return null;
        }

        $files = glob($output_dir.self::MERCHANT_FBA_LISTINGS_DATA.'*.raw');

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
        $params['ReportType'] = '_GET_AFN_INVENTORY_DATA_';
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
        $params['ReportRequestList.Type.1'] = '_GET_AFN_INVENTORY_DATA_';
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
            CommonTools::p("Reports:");
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
                    var_dump($report_data);
                }
                if (isset($report_data->GeneratedReportId) && $report_data->GeneratedReportId) {
                    return((string)$report_data->GeneratedReportId);
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

$amazonShippingConfig = new AmazonShippingConfig();
$amazonShippingConfig->dispatch();
