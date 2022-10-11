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

require_once(dirname(__FILE__).'/../functions/env.php');
require_once(dirname(__FILE__).'/../amazon.php');
require_once(dirname(__FILE__).'/../classes/amazon.tools.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.products_report.class.php');

class AmazonShippingConfig extends AmazonProductsReport
{
    public static $offers = array();
    public static $process = false;
    public static $end = false;

    public function __construct()
    {
        $this->inventory_type = self::MERCHANT_ACTIVE_LISTINGS_DATA;
        $this->report_type = '_GET_MERCHANT_LISTINGS_DATA_';

        parent::__construct();

        ob_start();
        AmazonContext::restore($this->context);
        ob_get_clean();
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $this->id_lang));
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
            case 'groups':
                $this->getShippingGroupNames();
                break;
            case 'get-products':
                $this->getProducts();
                break;

            default:
                die('Wrong action');
                break;
        }
    }

    public function setFileInventory()
    {
        $fileid = floor((time() % (86400 * 365)) / self::EXPIRE); // file id valid for 4 hours

        $this->file_inventory = sprintf('%s%s_%s_%s_%s.raw', $this->import, $this->inventory_type ? $this->inventory_type : self::MERCHANT_ACTIVE_LISTINGS_DATA, $this->merchantId, $this->region, $fileid);
        return;
    }

    public function getShippingGroupNames()
    {
        ob_start();

        $pass = false;
        $continue = false;
        $group_names = array();

        if ($this->initDownload()) {
            if (file_exists($this->file_inventory) && filesize($this->file_inventory) && filemtime($this->file_inventory) > time() - self::EXPIRE) {
                self::$messages[] = $message = sprintf($this->l('Using existings file: "%s" - Expires: %s'), basename($this->file_inventory), date('Y-m-d H:i:s', filemtime($this->file_inventory) + self::EXPIRE));

                // Inventory Exists, and downloaded, process the report
                $group_names = $this->processShippingGroupsListing();

                if (is_array($group_names) && count($group_names)) {
                    $configured_group_names = unserialize(AmazonConfiguration::get('shipping_groups'));

                    if (is_array($configured_group_names) && count($configured_group_names)) {
                        unset($configured_group_names[$this->region]);
                        $configured_group_names[$this->region] = $group_names;
                    } else {
                        $configured_group_names = array();
                        $configured_group_names[$this->region] = $group_names;
                    }
                    AmazonConfiguration::updateValue('shipping_groups', serialize($configured_group_names));

                    self::$messages[] = $message = sprintf('%d / %s', count($group_names), $this->l('shipping groups have been retrieve with success'));
                    $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $message);

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                    }

                    $pass = true;
                } else {
                    $configured_group_names = unserialize(AmazonConfiguration::get('shipping_groups'));

                    if (is_array($configured_group_names) && count($configured_group_names)) {
                        //unset($configured_group_names[$this->region]);
                    }

                    AmazonConfiguration::updateValue('shipping_groups', serialize($configured_group_names));

                    $error = $this->l('Not any existing shipping groups have been found from the inventory');
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
                    sleep(20);
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
                'groups' => count($group_names) ? $group_names : null,
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

    protected function processShippingGroupsListing()
    {
        if (Amazon::$debug_mode) {
            printf('processShippingGroupsListing()'.nl2br(Amazon::LF));
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
            CommonTools::p(sprintf('Inventory: %s products'.nl2br(Amazon::LF), count($lines)));
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

        $columns = explode("\t", AmazonTools::noAccents(Tools::strtolower(utf8_encode($header))));
        $to_search = array('merchant-shipping-group', 'gruppo-spedizione-venditore', 'groupe-expedition-vendeur', 'haendlerversandgruppe');
        $array_keys = array_intersect($columns, $to_search);

        // Header, display to the user he doesn't have merchant shipping group
        if (!is_array($array_keys) || !count($array_keys)) {
            $error = $this->l('No merchant shipping groups detected, it seems your are not using shipping templates');
            $debug = sprintf('%s(#%d): %s - %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error, print_r($columns));
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }
        $columns_keys = array_flip($columns);
        $merchant_shipping_group_key = $columns_keys[reset($array_keys)];

        $count = 0;
        $group_names = array();
        $matching_errors = array();
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            if ($count++ < 1) {
                continue;
            }

            $result = explode("\t", $line);

            if (count($result) < $merchant_shipping_group_key + 1) {
                continue;
            }
            $merchant_group_name = utf8_encode($result[$merchant_shipping_group_key]);

            if (!preg_match('/^[\w\s]*$/iu', $merchant_group_name)) {
                if (isset($matching_errors[$merchant_shipping_group_key])) {
                    continue;
                } else {
                    $matching_errors[$merchant_shipping_group_key] = true;
                }
                $error = sprintf($this->l('Invalid shipping group name: "%s", shipping group names must only containing text'), $merchant_group_name);
                self::$errors[] = $error;

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error));
                }
                continue;
            }
            $group_key = AmazonTools::toKey($result[$merchant_shipping_group_key]);
            $group_names[$group_key] = $merchant_group_name ;
        }

        if (!is_array($group_names) || !count($group_names)) {
            $error = sprintf('%s(#%d): %s - Not any group name found', basename(__FILE__), __LINE__, __FUNCTION__);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$error");
            }
            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Processed Items: %s', print_r($group_names, true)));
        }

        return ($group_names);
    }
}

$amazonShippingConfig = new AmazonShippingConfig();
$amazonShippingConfig->dispatch();
