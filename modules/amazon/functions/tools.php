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
require_once(dirname(__FILE__).'/../classes/amazon.certificates.class.php');

if (file_exists(dirname(__FILE__).'/../../cronjobs/cronjobs.php')) {
    require_once(dirname(__FILE__).'/../../cronjobs/cronjobs.php');

    class CronJobsAmazon extends CronJobs
    {
        public function updateWebserviceExt()
        {
            $this->updateWebservice(true);
        }
    }
}

class AmazonToolFunction extends Amazon
{
    public static $errors    = array();
    public static $pass      = false;
    public static $file      = false;
    public static $filelink  = false;
    public static $delimiter = ';';
    public $export    = null;
    public $import    = null;

    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        $this->export = $this->path.'export/';


        self::$debug_mode = (bool)Configuration::get('AMAZON_DEBUG_MODE');

        if (Tools::getValue('debug')) {
            self::$debug_mode = true;
        }

        if (self::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    public function initTools()
    {
        //  Check Access Tokens
        //
        $token = Tools::getValue('instant_token');

        if (!$token || $token != Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0)) {
            print 'Wrong token';
            die;
        }

        return (true);
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }


    public static function productFilejsonDisplayExit()
    {
        $callback = Tools::getValue('callback');

        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $errors = null;
        $output = null;

        if (!AmazonToolFunction::$pass) {
            $result = trim(ob_get_clean());
            if ($result) {
                $errors = $result;
            }
        } else {
            $output = trim(ob_get_clean());
        }

        $json = Tools::jsonEncode(array(
            'error' => !AmazonToolFunction::$pass,
            'debug' => Amazon::$debug_mode,
            'errors' => $errors,
            'output' => $output,
            'file' => AmazonToolFunction::$file,
            'filelink' => AmazonToolFunction::$filelink
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    public function dispatch()
    {
        if (!Amazon::$debug_mode) {
            ob_start();
        }

        switch (Tools::getValue('action')) {
            case 'product-code-export':
                die($this->productFileExport());
                break;
            case 'product-code-download':
                die($this->productFileDownload());
                break;
            case 'queue-delete':
                die($this->deleteActionQueue());
                break;
            case 'queue-list':
                die($this->listQueue());
                break;
            case 'install-cron-jobs':
                die($this->InstallCronJobs());
                break;
            case 'delete-valid-values':
                $this->deleteValidValues();
                break;
            case 'download-valid-values':
                $this->downloadValidValues();
                // then install
            case 'install-valid-values':
                $this->installValidValues();
                break;
            case 'bullet-point-generator':
                $this->bulletPointGenerator();
                break;
            case 'bullet-point-encode':
                $this->bulletPointEncode();
                break;
            case 'dynamic-config':
                $this->dynamicConfig();
                break;
            case 'carriers-update':
                $this->carriersUpdate();
                break;
            case 'delete-translations':
                $this->deleteTranslations();
                break;
            case 'delete-models':
                $this->deleteModels();
                break;
            case 'test-invoice':
                $this->testInvoice();
                break;
            case 'test-review':
                $this->testReview();
                break;
            // Switch first name / last name
            case 'switch-customer-name':
                $this->switchName('customer');
                break;
            case 'switch-address-shipping-name':
                $this->switchName('shipping');
                break;
            case 'switch-address-invoice-name':
                $this->switchName('invoice');
                break;
            default:
                break;
        }
    }

    public function deleteValidValues()
    {
        require_once(dirname(__FILE__).'/../classes/amazon.valid_values.class.php');

        $console = array();
        $success = array();
        $warning = array();
        $error = array();

        $pass = true;

        $callback = Tools::getValue('callback');

        if ($pass) {
            if (AmazonValidValues::tableExists()) {
                if (!AmazonValidValues::tableClear()) {
                    $warning[] = sprintf('%s', 'Fail to delete table');
                    $pass = false;
                }
            } else {
                $warning[] = sprintf('%s', 'Table doesn\'t exist');
                $pass = false;
            }
        }

        if ($pass) {
            $success[] = $this->l('Valid values table successfully erased');
        } else {
            $error[] = $this->l('Unable to clear valid values table');
        }

        $console[] = trim(ob_get_contents());
        ob_end_clean();

        $json = Tools::jsonEncode(array(
            'error' => $error,
            'warning' => $warning,
            'success' => $success,
            'output' => $console,
            'pass' => $pass,
            'continue' => false
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    public function installValidValues()
    {
        require_once(dirname(__FILE__).'/../classes/amazon.valid_values.class.php');

        $console = null;
        $success = array();
        $warning = array();
        $error = array();

        if (Amazon::ENABLE_EXPERIMENTAL_FEATURES) {
            AmazonValidValues::$file_prefix = 'amazon_valid_values-dev';
        }

        $gzfile = AmazonValidValues::$file_prefix.AmazonValidValues::FILE_EXT_GZ;
        $sqlfile = AmazonValidValues::$file_prefix.AmazonValidValues::FILE_EXT_SQL;

        $pass = true;
        $file_content = null;
        $file_content_gz = null;

        $callback = Tools::getValue('callback');

        $pass = $pass && $this->initImportDirectory();

        if ($pass) {
            if (file_exists($this->import.$sqlfile)) {
                if (!filesize($this->import.$sqlfile)) {
                    $pass = false;
                    $warning[] = sprintf('%s: "%s"', 'File empty', $this->import.$sqlfile);
                } elseif (!AmazonValidValues::importSQL($this->import.$sqlfile)) {
                    $pass = false;
                    $warning[] = sprintf('%s: "%s"', 'SQL import failed', $this->import.$sqlfile);
                }
            } else {
                $pass = false;
                $warning[] = sprintf('%s: "%s"', 'Unable to open file', $this->import.$gzfile);
            }
        }

        if ($pass) {
            $success[] = $this->l('Valid values file successfully inserted in database');
        } else {
            $error[] = $this->l('Unable to import valid values in database');
        }

        $console = trim(ob_get_contents());
        ob_end_clean();

        $json = Tools::jsonEncode(array(
            'error' => $error,
            'warning' => $warning,
            'success' => $success,
            'output' => $console,
            'pass' => $pass,
            'continue' => false
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    public function downloadValidValues()
    {
        require_once(dirname(__FILE__).'/../classes/amazon.valid_values.class.php');

        $success = array();
        $warning = array();
        $error = array();

        $pass = true;
        $file_content = null;
        $file_content_gz = null;

        $callback = Tools::getValue('callback');

        if (Amazon::ENABLE_EXPERIMENTAL_FEATURES) {
            AmazonValidValues::$file_prefix = 'amazon_valid_values-dev';
        }

        $gzfile = AmazonValidValues::$file_prefix.AmazonValidValues::FILE_EXT_GZ;
        $md5file = AmazonValidValues::$file_prefix.AmazonValidValues::FILE_EXT_MD5;
        $sqlfile = AmazonValidValues::$file_prefix.AmazonValidValues::FILE_EXT_SQL;

        $pass = $pass && $this->initImportDirectory();

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d)", basename(__FILE__), __LINE__));
            CommonTools::p(sprintf("source: %s", AmazonValidValues::SOURCE_URL));
            CommonTools::p(sprintf("gzfile: %s", $gzfile));
            CommonTools::p(sprintf("sqlfile: %s", $sqlfile));
        }

        if ($pass) {
            $md5sum = AmazonTools::fileGetContents(AmazonValidValues::SOURCE_URL.$md5file.'?dl=1', false, null, AmazonValidValues::TIMEOUT);

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf("%s(%d)", basename(__FILE__), __LINE__));
                CommonTools::p(sprintf("md5sum: %s", $md5sum));
                CommonTools::p(sprintf("last error: %s", print_r(error_get_last(), true)));
            }

            if (!strpos($md5sum, AmazonValidValues::$file_prefix)) {
                $pass = false;
                $warning[] = sprintf('%s - "%s"', $this->l('Failed to download md5sum file on the server'), AmazonValidValues::SOURCE_URL.$md5file.'?dl=1');
            } else {
                //$md5_remote = strstr($md5sum, ' ', true); // strstr with thrird parameter is compatible only with PHP > 5.3
                $md5_remote =  Tools::substr($md5sum, 0, strpos($md5sum, ' '));

                if (preg_match('/^[a-f0-9]{32}$/', $md5_remote)) {
                    $source_file = AmazonValidValues::SOURCE_URL.$gzfile.'?dl=1';
                    $file_content_gz = AmazonTools::fileGetContents($source_file, false, null, AmazonValidValues::TIMEOUT);

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf("%s(%d)", basename(__FILE__), __LINE__));
                        CommonTools::p(sprintf("content length: %s", Tools::strlen($file_content_gz)));
                        CommonTools::p(sprintf("last error: %s", print_r(error_get_last(), true)));
                    }

                    if (file_exists($this->import.$gzfile)) {
                        if (!unlink($this->import.$gzfile)) {
                            $warning[] = sprintf('%s: "%s"', 'Failed to remove', $this->import.$gzfile);
                        }
                    }

                    if (file_put_contents($this->import.$gzfile, $file_content_gz)) {
                        $md5_local = md5_file($this->import.$gzfile);

                        if (!Tools::strlen($md5_local) && Tools::strlen($md5_remote) && $md5_local == $md5_remote) {
                            $pass = false;
                            $warning[] = sprintf('%s(%d): %s (%s / %s)', basename(__FILE__), __LINE__, 'md5 keys mismatch', $md5_local, $md5_remote);
                        } else {
                            $buffer_size = 4096;
                            $file = gzopen($this->import.$gzfile, 'rb');
                            $fout = fopen($this->import.$sqlfile, 'wb');

                            if (!$fout) {
                                $pass = false;
                                $warning[] = sprintf('%s: "%s"', 'Failed to open file for writing', $this->import.$sqlfile);
                            } else {
                                while (!gzeof($file)) {
                                    fwrite($fout, gzread($file, $buffer_size));
                                }
                                fclose($fout);
                                gzclose($file);
                            }

                            if (!file_exists($this->import.$sqlfile) || !filesize($this->import.$sqlfile)) {
                                $pass = false;
                                $warning[] = sprintf('%s: "%s"', 'Extraction failed', $this->import.$sqlfile);
                            }
                        }
                    } else {
                        $pass = false;
                        $warning[] = sprintf('Failed to save file from %s to %s', $source_file, $this->import.$gzfile);
                    }
                } else {
                    $pass = false;
                    $warning[] = sprintf('%s(%d): %s', basename(__FILE__), __LINE__, 'Wrong md5 key returned by remote server');
                }
            }
        }

        if ($pass) {
            $success[] = $this->l('Valid values file successfully downloaded and installed');
        } else {
            $error[] = $this->l('Unable to import list of valid values');
        }

        $console = trim(ob_get_contents());
        ob_end_clean();

        $json = Tools::jsonEncode(array(
            'error' => $error,
            'warning' => $warning,
            'success' => $success,
            'output' => $console,
            'pass' => $pass,
            'continue' => true
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    private function initImportDirectory()
    {
        $this->import = $this->path.'import/';

        if (!is_dir($this->import)) {
            if (!@mkdir($this->import)) {
                printf('%s(#%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to create import directory'));
                return false;
            }
        }

        @chmod($this->import, 0777);

        if (file_put_contents($this->import.'.htaccess', "deny from all\n") === false) {
            printf('%s(#%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to write into import directory'));
            return false;
        }

        return true;
    }


    public function carriersUpdate()
    {
        require_once(dirname(__FILE__).'/../classes/amazon.carrier.class.php');
        require_once(dirname(__FILE__).'/../classes/amazon.settings.class.php');

        $console = array();
        $success = array();
        $warning = array();
        $error = array();

        $pass = true;

        $callback = Tools::getValue('callback');

        $pass = $pass && AmazonSettings::getShippingMethods(AmazonCarrier::SHIPPING_STANDARD, true);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("AmazonSettings::getShippingMethods: %s - %s", AmazonCarrier::SHIPPING_STANDARD, $pass ? 'Ok' : 'Failed'));
        }

        $pass = $pass && AmazonSettings::getShippingMethods(AmazonCarrier::SHIPPING_EXPRESS, true);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("AmazonSettings::getShippingMethods: %s - %s", AmazonCarrier::SHIPPING_EXPRESS, $pass ? 'Ok' : 'Failed'));
        }

        $pass = $pass && AmazonSettings::getShippingMethods(AmazonCarrier::SHIPPING_CODES, true);


        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("AmazonSettings::getShippingMethods: %s - %s", AmazonCarrier::SHIPPING_CODES, $pass ? 'Ok' : 'Failed'));
        }

        if ($pass) {
            $success[] = $this->l('Amazon carriers successfully updated');
        } else {
            $error[] = $this->l('Unable to update Amazon carriers');
        }

        $console[] = trim(ob_get_contents());
        ob_end_clean();

        $json = Tools::jsonEncode(array(
            'error' => $error,
            'warning' => $warning,
            'success' => $success,
            'output' => $console,
            'pass' => $pass,
            'continue' => false
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }


    public function deleteTranslations()
    {
        require_once(dirname(__FILE__).'/../classes/amazon.settings.class.php');

        $console = array();
        $success = array();
        $warning = array();
        $error = array();

        $pass = true;

        $callback = Tools::getValue('callback');

        $directories = array();
        $directories[] = AmazonSettings::getTranslationsDir('attributes');
        $directories[] = AmazonSettings::getTranslationsDir('product_types');
        $directories[] = AmazonSettings::getTranslationsDir('universes');

        foreach ($directories as $directory) {
            if (!empty($directory) && is_dir($directory)) {
                $files = glob($directory.'*.gz');

                if (is_array($files) && count($files)) {
                    foreach ($files as $file) {
                        $pass = $pass && @unlink($file);
                    }
                }
            } elseif ($directory === null) {
                $warning[] = sprintf('%s: %s', $this->l('Unexisting directory'), $directory);
            }
        }

        if ($pass) {
            $success[] = $this->l('Translations files successfully removed');
        } else {
            $error[] = $this->l('Unable to remove translations files');
        }

        $console[] = trim(ob_get_contents());
        ob_end_clean();

        $json = Tools::jsonEncode(array(
            'error' => $error,
            'warning' => $warning,
            'success' => $success,
            'output' => $console,
            'pass' => $pass,
            'continue' => false
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    public function deleteModels()
    {
        require_once(dirname(__FILE__).'/../classes/amazon.settings.class.php');

        $console = array();
        $success = array();
        $warning = array();
        $error = array();

        $pass = true;

        $callback = Tools::getValue('callback');

        $directory = AmazonSettings::getFieldsSettingsDir();

        if (!empty($directory) && is_dir($directory)) {
            $sub_directories = glob($directory.'*');

            foreach ($sub_directories as $model_dir) {
                if (is_dir($model_dir)) {
                    $target_files = $model_dir.DIRECTORY_SEPARATOR;

                    $files = glob($target_files.'*.gz');

                    if (is_array($files) && count($files)) {
                        foreach ($files as $file) {
                            if (!@unlink($file)) {
                                $warning[] = sprintf('%s: %s', $this->l('Failed to remove'), $file);
                                $pass = false;
                            }
                        }
                    }
                }
            }
        } elseif ($directory == null) {
            $warning[] = sprintf('%s: %s', $this->l('Unexisting directory'), $directory);
        }

        if ($pass) {
            $success[] = $this->l('Models files successfully removed');
        } else {
            $error[] = $this->l('Unable to remove models files');
        }

        $console[] = trim(ob_get_contents());
        ob_end_clean();

        $json = Tools::jsonEncode(array(
            'error' => $error,
            'warning' => $warning,
            'success' => $success,
            'output' => $console,
            'pass' => $pass,
            'continue' => false
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    public function InstallCronJobs()
    {
        $cron_jobs_params = Tools::getValue('prestashop-cronjobs-params');
        $callback = Tools::getValue('callback');

        $this->initTools();

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_shop = (int)$this->context->shop->id;
            $id_shop_group = (int)$this->context->shop->id_shop_group;
        } else {
            $id_shop = 1;
            $id_shop_group = 1;
        }

        $pass = true;
        $count = 0;

        if (!empty($cron_jobs_params)) {
            $cronjobs_lines = explode("!", $cron_jobs_params);

            if (count($cronjobs_lines)) {
                $query = 'DELETE FROM '._DB_PREFIX_.'cronjobs WHERE `task` LIKE "%'.urlencode('/'.pSQL($this->name).'/').'%"';

                if (AmazonConfiguration::shopIsFeatureActive()) {
                    $query .= ' AND `id_shop`='.(int)$id_shop . ' AND `id_shop_group`='.(int)$id_shop_group;
                }

                Db::getInstance()->execute($query);

                foreach ($cronjobs_lines as $cronjobs_line) {
                    $params = explode("|", trim($cronjobs_line));

                    if (count($params) < 4) {
                        continue;
                    }

                    $title = trim($params[0]);
                    $lang = trim($params[1]);
                    $frequency = (int)trim($params[2]);
                    $url = trim($params[3]);
                    $hours = array();

                    // Setup the cron
                    $hour = (int)$frequency;
                    $day = (int)-1;
                    $month = (int)-1;
                    $day_of_week = (int)-1;
                    $description = sprintf('%s (%s)', $title, $lang);

                    if ($frequency > 1) {
                        for ($i = 0; $i < 24; $i += $frequency) {
                            $hours[] = $i;
                        }
                    } elseif ($frequency == -1) {
                        $hours[] = $frequency;
                    }

                    foreach ($hours as $hour) {
                        $query = 'INSERT INTO '._DB_PREFIX_.'cronjobs
						(`description`, `task`, `hour`, `day`, `month`, `day_of_week`, `updated_at`, `one_shot`, `active`, `id_shop`, `id_shop_group`)
						VALUES ("'.pSQL($description).'", "'.pSQL(urlencode($url)).'", '.pSQL($hour).', '.pSQL($day).', '.pSQL($month).', '.pSQL($day_of_week).',
							NULL, FALSE, TRUE, '.(int)$id_shop.', '.(int)$id_shop_group.')';

                        $pass &= Db::getInstance()->execute($query);

                        $count++;
                    }
                }
                if ($count && class_exists('CronJobsAmazon')) {
                    $cronJob = new CronJobsAmazon();
                    $cronJob->updateWebserviceExt();
                }
            }
        }

        if ($pass) {
            $msg = sprintf('%d %s', $count, $this->l('tasks successfully added to Prestashop Cronjobs module'));
        } else {
            $msg = $this->l('An unexpected error occured while creating tasks');
        }

        if (Amazon::$debug_mode) {
            $msg .= trim(ob_get_clean());
        } else {
            ob_get_clean();
        }

        $json = Tools::jsonEncode(array(
            'error' => !$pass,
            'count' => $count,
            'output' => $msg,
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    public function productFileExport()
    {
        $id_warehouse = null;
        $id_shop = 1;
        $handle_combinations = !(bool)Configuration::get('AMAZON_NO_COMBINATIONS');

        // Shop Configuration (PS 1.5)
        //
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_shop = (int)Configuration::get('AMAZON_SHOP');
            $id_warehouse = (int)Configuration::get('AMAZON_WAREHOUSE');

            if (!$id_shop) {
                $id_shop = 1;
            }
        }

        register_shutdown_function(array('AmazonToolFunction', 'productFilejsonDisplayExit'));

        $this->initTools();

        if (!is_dir($this->export)) {
            if (!mkdir($this->export)) {
                die(sprintf($this->l('Unable to create directory: %s'), $this->export));
            }
        }
        $filename = date('Ymd').'-amazon.csv';
        $fileout = $this->export.$filename;

        if (file_exists($fileout) && !is_writable($fileout)) {
            die(sprintf($this->l('Unable to open file for writing: %s'), $fileout));
        }

        if (!($handle = fopen($fileout, 'w'))) {
            die(sprintf($this->l('Unable to open file for writing: %s'), $fileout));
        }

        // Write header line
        //
        $CSV = array();
        $CSV[0] = $this->l('Prestashop Product ID');
        $CSV[1] = 'Reference (SKU)';
        $CSV[2] = 'UPC';
        $CSV[3] = 'EAN';
        $CSV[4] = $this->l('Product Name');
        $CSV[5] = $this->l('Manufacturer');

        fputcsv($handle, $CSV, self::$delimiter);

        $active = Tools::getValue('active') ? true : false;
        $in_stock = Tools::getValue('in_stock') ? true : false;

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $get_combination = 'getAttributeCombinaisons';
        } else {
            $get_combination = 'getAttributeCombinations';
        }

        $result = AmazonProduct::marketplaceGetAllProducts($this->id_lang, $active);

        if (!is_array($result) || !count($result)) {
            print $this->l('No products to export, exiting...');
            die;
        }
        $count = 0;

        foreach ($result as $product_item) {
            $id_product = (int)$product_item['id_product'];

            $product = new Product($id_product, false, $this->id_lang);

            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            $manufacturer_name = null;

            if ((int)$product->id_manufacturer && is_numeric($product->id_manufacturer)) {
                $manufacturer = new Manufacturer((int)$product->id_manufacturer);

                if (Validate::isLoadedObject($manufacturer)) {
                    $manufacturer_name = $manufacturer->name;
                }
            }

            // Parent
            $product_combinations = array();
            $product_combinations[0]['ean13'] = $product->ean13;
            $product_combinations[0]['upc'] = $product->upc;
            $product_combinations[0]['reference'] = $product->reference;
            $product_combinations[0]['attribute_name'] = null;

            // Children
            if ($handle_combinations && $product->hasAttributes()) {
                $combinations = $product->{$get_combination}($this->id_lang);

                foreach ($combinations as $key => $combination) {
                    $id_product_attribute = (int)$combination['id_product_attribute'];

                    $product_combinations[$id_product_attribute]['ean13'] = $combination['ean13'];
                    $product_combinations[$id_product_attribute]['upc'] = $combination['upc'];
                    $product_combinations[$id_product_attribute]['reference'] = $combination['reference'];

                    if (!isset($product_combinations[$id_product_attribute]['attribute_name'])) {
                        $product_combinations[$id_product_attribute]['attribute_name'] = null;
                    }

                    if ($combination['attribute_name']) {
                        $product_combinations[$id_product_attribute]['attribute_name'] .= sprintf('%s - ', $combination['attribute_name']);
                    }
                }
            }

            foreach ($product_combinations as $id_product_attribute => $product_combination) {
                $count++;
                if ($product_combination['attribute_name']) {
                    $attribute_name = rtrim($product_combination['attribute_name'], ' - ');
                    $product_name = sprintf('%s (%s)', $product->name, $attribute_name);
                } else {
                    $product_name = $product->name;
                }

                if ($in_stock) {
                    if (version_compare(_PS_VERSION_, '1.5', '<')) {
                        $productQuantity = Product::getQuantity((int)$id_product, $id_product_attribute ? (int)$id_product_attribute : null);
                    } else {
                        $productQuantity = Product::getRealQuantity($id_product, $id_product_attribute ? (int)$id_product_attribute : null, $id_warehouse ? $id_warehouse : null, $id_shop);
                    }

                    if ($productQuantity < 1) {
                        continue;
                    }
                }

                $CSV = array();
                $CSV[0] = sprintf('%d_%d', (int)$id_product, (int)$id_product_attribute);
                $CSV[1] = "'".$product_combination['reference'];
                $CSV[2] = ((int)$product_combination['upc'] ? "'".$product_combination['upc'] : null);
                $CSV[3] = (int)$product_combination['ean13'] ? "'".$product_combination['ean13'] : null;
                $CSV[4] = $product_name;
                $CSV[5] = $manufacturer_name;

                fputcsv($handle, $CSV, self::$delimiter);
            }
        }
        fclose($handle);

        $output = trim(ob_get_clean());

        if ($count) {
            AmazonToolFunction::$pass = true;
            AmazonToolFunction::$file = $filename;
            AmazonToolFunction::$filelink = sprintf(html_entity_decode('&lt;a href="%s" target="_blank"&gt;%s&lt;/a&gt;'), $this->url.'functions/tools.php?action=product-code-download&filename='.$filename, $filename);
        }
    }

    public function productFileDownload()
    {
        $fn = basename(Tools::getValue('filename'));
        $output = trim(ob_get_clean());

        header('Pragma: public');
        header('Cache-Control: no-cache');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$fn.'"');
        echo AmazonTools::fileGetContents($this->export.$fn);
        exit;
    }

    public function deleteActionQueue()
    {
        $pass = false;
        $count = 0;
        $queues = Tools::getValue('queue');
        $callback = Tools::getValue('callback');

        $this->initTools();

        if (is_array($queues) && count($queues)) {
            foreach ($queues as $id_lang => $queue) {
                foreach ($queue as $action => $value) {
                    AmazonProduct::marketplaceActionReset($id_lang, $action);
                    $pass = true;
                    $count++;
                }
            }
        }
        $output = trim(ob_get_clean());

        $json = Tools::jsonEncode(array(
            'error' => !$pass,
            'count' => $count,
            'output' => $output,
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    public function listQueue()
    {
        // Current Queue
        //
        $languages = AmazonTools::languages();
        $action_queues = AmazonProduct::getCurrentQueue();

        if (is_array($action_queues) && count($action_queues)) {
            foreach ($action_queues as $key => $action_queue) {
                if (isset($languages[$action_queue['id_lang']])) {
                    $action_queues[$key]['lang'] = $languages[$action_queue['id_lang']]['name'];
                    $action_queues[$key]['lang_iso_code'] = $languages[$action_queue['id_lang']]['iso_code'];
                } else {
                    $action_queues[$key]['lang'] = $this->l('Inactive');
                }
                $action_queues[$key]['date_min'] = AmazonTools::displayDate($action_queue['date_min'], $this->id_lang, true);
                $action_queues[$key]['date_max'] = AmazonTools::displayDate($action_queue['date_max'], $this->id_lang, true);

                switch ($action_queue['action']) {
                    case self::ADD:
                        $action_queues[$key]['action_name'] = $this->l('Add');
                        break;
                    case self::REMOVE:
                        $action_queues[$key]['action_name'] = $this->l('Delete');
                        break;
                    case self::UPDATE:
                        $action_queues[$key]['action_name'] = $this->l('Update');
                        break;
                    case self::REPRICE:
                        $action_queues[$key]['action_name'] = $this->l('Repricing');
                        break;
                }
            }
            var_dump($action_queues);
        }
        
        exit;
    }

    public function bulletPointGenerator()
    {
        $pass = true;
        $id = Tools::getValue('id');
        $data = Tools::getValue('data');

        require_once(dirname(__FILE__).'/../classes/amazon.bullet_point.class.php');

        $callback = Tools::getValue('callback');

        $this->initTools();

        $bulletpoint = new AmazonBulletPoint();

        $html = $bulletpoint->bulletPointEditorUi($id, $data);

        $output = trim(ob_get_clean());

        $json = Tools::jsonEncode(array(
            'error' => !$pass,
            'html' => $html,
            'id' => $id,
            'output' => $output,
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    public function bulletPointEncode()
    {
        $pass = true;
        $id = Tools::getValue('id');

        require_once(dirname(__FILE__).'/../classes/amazon.bullet_point.class.php');

        $callback = Tools::getValue('callback');

        $this->initTools();

        $bulletpoint = new AmazonBulletPoint();

        $encoded_bullet_point = $bulletpoint->bulletPointEncode();

        $output = trim(ob_get_clean());

        $json = Tools::jsonEncode(array(
            'error' => !$pass,
            'bullet_point' => $encoded_bullet_point,
            'id' => $id,
            'output' => $output,
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    public function dynamicConfig()
    {
        $callback = Tools::getValue('callback');
        $field = Tools::getValue('field');
        $value = Tools::getValue('value');
        $pass = false;

        switch ($field) {
            case 'debug_mode':
                Configuration::updateValue('AMAZON_DEBUG_MODE', (bool)$value);
                $pass = true;
                break;
            case 'demo_mode':
                $this->amazon_features['demo_mode'] = (bool)$value;
                AmazonConfiguration::updateValue('FEATURES', $this->amazon_features);
                $pass = true;
                break;
        }

        $json = Tools::jsonEncode(array(
            'error' => !$pass
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    public function testInvoice()
    {
        $pass = false;
        $callback = Tools::getValue('callback');
        $output = null;

        $params = array();
        $params['id_order'] = Tools::getValue('id_order');
        $params['test_mode'] = true;

        $result = $this->manageInvoiceOrderState($params);

        if ($result) {
            $message = sprintf('%s: %s', $this->l('Invoice has been sent successfully to'), Configuration::get('PS_SHOP_EMAIL'));
            $pass = true;
        } else {
            $message = $this->l('An error occured while preparing or sending the email');
            $pass = false;
        }
        if (!Amazon::$debug_mode) {
            $output = trim(ob_get_clean());
        }

        $json = Tools::jsonEncode(array(
        'error' => !$pass,
        'count' => 1,
        'output' => $output,
        'result' => $message
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }
    
    public function testReview()
    {
        $pass = false;
        $callback = Tools::getValue('callback');
        $output = null;
        
        $params = array();
        $params['id_order'] = Tools::getValue('id_order');
        $params['test_mode'] = true;

        $result = $this->manageReviewIncentiveOrderState($params);

        if ($result) {
            $message = sprintf('%s: %s', $this->l('Review incentive has been sent successfully to'), Configuration::get('PS_SHOP_EMAIL'));
            $pass = true;
        } else {
            $message = $this->l('An error occured while preparing or sending the email');
            $pass = false;
        }
        if (!Amazon::$debug_mode) {
            $output = trim(ob_get_clean());
        }

        $json = Tools::jsonEncode(array(
        'error' => !$pass,
        'count' => 1,
        'output' => $output,
        'result' => $message
        ));

        echo (string)$callback.'('.$json.')';
        exit;
    }

    /**
     * Switch first name and last name in customer name or address name
     * @param $type
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function switchName($type)
    {
        $success = $update_all_addresses = false;
        $msg = $html = '';

        $callback = Tools::getValue('callback');
        $id_order = Tools::getValue('id_order');
        if (!$id_order) {
            $msg = $this->l('Cannot find order');
        } else {
            $order = new Order($id_order);

            // Update customer name
            if ('customer' == $type) {
                $customer = new Customer($order->id_customer);
                $success = $this->_switchName($customer);
                if ($success) {
                    $msg = $this->l('Saved customer name');
                    $customer = new Customer($order->id_customer);
                    $html = $customer->firstname.' '.$customer->lastname;
                } else {
                    $msg = $this->l('Cannot save customer name');
                }
            }

            // Update address name
            else {
                if ('shipping' == $type) {
                    if ($order->id_address_delivery) {
                        $shipping_address = new Address($order->id_address_delivery);
                        $success = $this->_switchName($shipping_address);
                        if ($success) {
                            $msg = $this->l('Saved delivery address');
                            $shipping_address = new Address($order->id_address_delivery);
                            $html = AddressFormat::generateAddress($shipping_address, array(), '<br />');
                        } else {
                            $msg = $this->l('Cannot save delivery address');
                        }
                    }
                } elseif ('invoice' == $type) {
                    if ($order->id_address_invoice) {
                        $invoice_address = new Address($order->id_address_invoice);
                        $success = $this->_switchName($invoice_address);
                        if ($success) {
                            $msg = $this->l('Saved invoice address');
                            $invoice_address = new Address($order->id_address_invoice);
                            $html = AddressFormat::generateAddress($invoice_address, array(), '<br />');
                        } else {
                            $msg = $this->l('Cannot save invoice address');
                        }
                    }
                }

                // If order use one address for all, update all of them in view
                if ($order->id_address_delivery == $order->id_address_invoice) {
                    $update_all_addresses = true;
                    $msg = $this->l('Saved both delivery and invoice address');
                }
            }
        }

        $json = Tools::jsonEncode(array(
            'error' => !$success,
            'message' => $msg,
            'update_all_addresses' => $update_all_addresses,
            'html' => $html
        ));
        echo (string)$callback.'('.$json.')';
        exit;
    }

    /**
     * @param Customer|Address $object
     * @return bool
     * @throws PrestaShopException
     */
    private function _switchName($object)
    {
        if ($object->firstname && $object->lastname) {
            $first_name = $object->firstname;
            $object->firstname = $object->lastname;
            $object->lastname  = $first_name;
            return $object->save();
        }

        return true;
    }
}

$amazonToolsFunction = new AmazonToolFunction();
$amazonToolsFunction->dispatch();
