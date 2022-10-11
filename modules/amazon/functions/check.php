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
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.zip.class.php');
require_once(dirname(__FILE__).'/../amazon.php');

class AmazonConnexionCheck extends Amazon
{
    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        $this->amazon_features = $this->getAmazonFeatures();
    }

    public function dispatch()
    {
        ob_start();

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
        $token = Tools::getValue('instant_token');

        if (!$token || $token != Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0)) {
            print('Wrong Token');
            die;
        }
        switch (Tools::getValue('action')) {
            case 'check':
                $this->check();
                break;
            case 'status':
                $this->status();
                break;
            case 'php-info':
                $this->phpInfo();
                break;
            case 'prestashop-info':
                $this->prestashopInfo();
                break;
            case 'support-info':
                $this->supportInfo();
                break;
            case 'mode-dev':
                $this->prestashopModeDev();
                break;
            case 'participations':
                $this->participations();
                break;
            case 'service-status':
                $this->getServiceStatus();
                break;
        }
    }

    public function getServiceStatus()
    {
        $status = false;
        $callback = Tools::getValue('callback');

        if ($callback == '?' || empty($callback)) {
            $callback = 'jsonp_'.time();
        }

        $result = AmazonWebService::getServiceStatus(Amazon::$debug_mode);

        if ($result instanceof SimpleXMLElement && isset($result->GetServiceStatusResult->Status) && $result->GetServiceStatusResult->Status == 'GREEN') {
            $json = Tools::jsonEncode(array('pass' => true));

            die((string)$callback.'('.$json.')');
        }
        CommonTools::d($result);
    }

    public function prestashopModeDev()
    {
        $callback = Tools::getValue('callback');

        if ($callback == '?' || empty($callback)) {
            $callback = 'jsonp_'.time();
        }

        $message = null;
        $new_state = Tools::getValue('status');
        $new_state_text = !(bool)$new_state ? 'false' : 'true';

        if ($new_state !== '0' && $new_state !== '1') {
            die('Target status unknown');
        }

        if (!defined('_PS_CONFIG_DIR_')) {
            define('_PS_CONFIG_DIR_', _PS_ROOT_DIR_.'/config/');
        }

        $defines_inc_php = _PS_CONFIG_DIR_.'defines.inc.php';
        $defines_inc_php_bak = _PS_CONFIG_DIR_.'defines.inc.php.bak';

        if (!file_exists($defines_inc_php) || !is_writable($defines_inc_php)) {
            die('File doesnt exists or is not writeable');
        }

        if (!($md5_orig = md5_file($defines_inc_php))) {
            die(sprintf('Unable to generate md5 of file: %s', $defines_inc_php));
        }

        if (!AmazonTools::copy($defines_inc_php, $defines_inc_php_bak)) {
            die(sprintf('Unable to create a backup (from %s to %s)', $defines_inc_php, $defines_inc_php_bak));
        }

        if (!($md5_dest = md5_file($defines_inc_php_bak))) {
            die(sprintf('Unable to generate md5 of file: %s', $defines_inc_php_bak));
        }

        if (!Tools::strlen($md5_dest) || $md5_orig != $md5_dest) {
            die('md5sum mismatch, operation aborted');
        }

        $defines_inc_contents = AmazonTools::fileGetContents($defines_inc_php);

        if (!Tools::strlen($defines_inc_php)) {
            die('Unable to get file contents, operation aborted');
        }

        if (md5($defines_inc_contents) != $md5_dest) {
            die('md5sum mismatch, operation aborted');
        }

        $defines_inc_contents_out = preg_replace('/(_PS_MODE_DEV_[\"\'][\s,]*)(true|false|TRUE|FALSE)/', '$1'.$new_state_text, $defines_inc_contents);

        $length_diff = abs(Tools::strlen($defines_inc_contents) - Tools::strlen($defines_inc_contents_out));

        if ($length_diff > 1) {
            die('messup, operation aborted');
        }

        if (!file_put_contents($defines_inc_php, $defines_inc_contents_out)) {
            if (!AmazonTools::copy($defines_inc_php_bak, $defines_inc_php)) {
                die('/!\\ huge trouble: operation failed, backup restore failed too !');
            } else {
                die('operation failed backup restored');
            }
        } else {
            $message = sprintf(html_entity_decode('_PS_MODE_DEV_ switched to &lt;b&gt;%s&lt;/b&gt; with success'), !(bool)$new_state ? 'Off' : 'On');
        }

        $json = Tools::jsonEncode(array('status' => (bool)$new_state, 'message' => $message));

        echo (string)$callback.'('.$json.')';
        die;
    }

    /**
     * Create zip file contains all support information
     */
    public function supportInfo()
    {
        $ps_info     = $this->prestashopInfo(true);
        $php_info    = $this->phpInfo(true);
        $screen_shot = Tools::getValue('screenShot');

        $file_prefix        = AmazonTools::getFriendlyUrl(Configuration::get('PS_SHOP_NAME'));
        $ps_info_file       = sprintf('%ssupport/%s-ps-info.txt', $this->path, $file_prefix);
        $php_info_file      = sprintf('%ssupport/%s-php-info.html', $this->path, $file_prefix);
        $screen_shot_file   = sprintf('%ssupport/%s-screen-shot.png', $this->path, $file_prefix);
        $zip_file_path      = sprintf('%ssupport/%s-support.zip', $this->path, $file_prefix);
        $zip_file_url       = sprintf('%ssupport/%s-support.zip', $this->url, $file_prefix);

        $success = file_put_contents($ps_info_file, $ps_info)
            && file_put_contents($php_info_file, $php_info)
            && file_put_contents($screen_shot_file, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $screen_shot)));

        if (file_exists($zip_file_path)) {
            @unlink($zip_file_path);
        }

        // To create zip with relative path
        chdir(sprintf('%s/support', $this->path));
        $zip = new AmazonZip();
        $success = $success && $zip->createZip($zip_file_path, array(basename($ps_info_file), basename($php_info_file), basename($screen_shot_file)));

        $json = Tools::jsonEncode(array('success' => $success, 'url' => $zip_file_url));
        if ($callback = Tools::getValue('callback')) {
            if ($callback == '?') {
                $callback = 'jsonp_'.time();
            }
            echo (string)$callback.'('.$json.')';
        } else {
            AmazonTools::d($json);
        }
    }

    public function prestashopInfo($return_data = false)
    {
        $content = null;
        $header_errors = ob_get_clean();
        $own_url = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'].$_SERVER['REQUEST_URI'] : sprintf('%s://%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['HTTP_HOST']).$_SERVER['REQUEST_URI'];

        if ($return_data) {
            // Do not print downloadable link
        } elseif (Tools::getValue('download')) {
            header('Pragma: public');
            header('Cache-Control: no-cache');
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="'.AmazonTools::getFriendlyUrl(Configuration::get('PS_SHOP_NAME')).'-ps-infos.txt'.'"');
        } else {
            echo html_entity_decode('&lt;a href="'.$own_url.'&download=1" title="'.$this->l('Download').'" target="_blank"&gt;'.$this->l('Download').'&lt;/a&gt;');
        }

        if (version_compare(_PS_VERSION_, 1.5, '>=')) {
            $check_duplicate_sql = 'SELECT `name`, `id_shop`, `id_shop_group`, COUNT(*) as count
                FROM `'._DB_PREFIX_.'configuration` WHERE name like "%AMAZON%"
                GROUP BY `name`, `id_shop`,  `id_shop_group`
                HAVING COUNT(*) > 1';

            $results = Db::getInstance()->executeS($check_duplicate_sql);

            if (is_array($results) && count($results)) {
                $content .= Amazon::LF;
                $content .= 'Reason is: '.Amazon::LF;
                $content .= 'Duplicated configuration keys: '.Amazon::LF;

                foreach ($results as $result) {
                    $content .= $result['name'];
                    $content .= ": ";
                    $content .= $result['count'];
                    $content .= Amazon::LF;
                }
            }
        }

        if (version_compare(_PS_VERSION_, 1.5, '>=')) {
            $sort = 'ORDER by `name`,`id_shop`';
            $ps15 = true;
        } else {
            $sort = 'ORDER by `name`';
            $ps15 = false;
        }

        $results2 = null;
        $results = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'configuration` WHERE `name` LIKE "PS_%" OR `name` LIKE "AMAZON_%" '.$sort);

        if (AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_NEW_CONFIGURATION)) {
            $results2 = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_NEW_CONFIGURATION.'` WHERE `name` LIKE "AMAZON_%" ');
        }

        if (is_array($results) && is_array($results2)) {
            $new_results = array();
            foreach ($results as $result) {
                $new_results[] = $result;
            }
            foreach ($results2 as $result) {
                $new_results[] = $result;
            }
            ksort($new_results);
            $results = $new_results;
        }

        $ps_configuration = null;
        $to_ignore = array('EMAIL', 'PASSWORD', 'PASSWD', 'CONTEXT_DATA', 'WIZARD', 'AMAZON_REPRICING', 'KEY_ID', 'SECRET');
        $multistore_configurations = array();

        foreach ($results as $result) {
            $pass = true;
            foreach ($to_ignore as $ignore) {
                if (strpos($result['name'], $ignore) !== false) {
                    $pass = false;
                }
            }
            if (!$pass) {
                continue;
            }

            $value = $initial_value = $result['value'];

            if (base64_encode(base64_decode($value, true)) === $value) {
                //TODO: Validation: Use to evaluate base64 encoded values, required
                $value = base64_decode($value, true);
            } else {
                //TODO: Validation: Required by test above
                $value = $result['value'];
            }

            if ($ps15) {
                $ps_configuration .= sprintf('%-50s %03d %03d : %s'.Amazon::LF, $result['name'], $result['id_shop'], $result['id_shop_group'], $value);
            } else {
                $ps_configuration .= sprintf('%-50s : %s'.Amazon::LF, $result['name'], $value);
            }

            if (stristr($result['name'], 'AMAZON_') != false && isset($result['id_shop']) && $result['id_shop'] != null) {
                $name = $result['name'];
                if (!isset($multistore_configurations[$name]) && !empty($value)) {
                    $multistore_configurations[$name]= array();
                    $multistore_configurations[$name][] = $value;
                } else {
                    $multistore_configurations[$name][] = $value;
                }
            }
        }


        if (is_array($multistore_configurations) && count($multistore_configurations)) {
            $multistore_configurations_keys = array_keys($multistore_configurations);

            foreach ($multistore_configurations_keys as $key) {
                if (is_array($multistore_configurations[$key]) && count($multistore_configurations[$key]) > 1) {
                    $multistore_configurations[$key] = array_filter($multistore_configurations[$key]);
                    if ($multistore_configurations[$key] && $count = count($multistore_configurations[$key])) {
                        $diffs = array_unique($multistore_configurations[$key]);

                        if (count($diffs) != $count) {
                            unset($multistore_configurations_keys[$key]);
                        }
                    }
                }
            }
        }

        // Aug-23-2018: Remove ps_carriers_only option

        if (defined('Carrier::ALL_CARRIERS')) {
            $all_carriers = Carrier::ALL_CARRIERS;
        } elseif (defined('ALL_CARRIERS')) {
            $all_carriers = ALL_CARRIERS;
        } else {
            $all_carriers = 5;
        }

        $carriers = Carrier::getCarriers($this->id_lang, false, false, false, null, $all_carriers);

        $content .= html_entity_decode('&lt;h1&gt;Prestashop&lt;/h1&gt;');
        $content .= 'Version: '._PS_VERSION_.Amazon::LF;
        $content .= 'Module: '.sprintf('%s/%s', $this->name, $this->version).Amazon::LF;
        $content .= 'Expert Mode: '. ($this->amazon_features['expert_mode'] ? 'Yes' : 'No').Amazon::LF;
        $content .= 'Mode Dev: '. (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ == true ? 'Yes' : 'No').Amazon::LF;

        if (defined('_PS_OVERRIDE_DIR_') && !Configuration::get('PS_DISABLE_OVERRIDES') && ($override_content = AmazonTools::globRecursive(_PS_OVERRIDE_DIR_.'*.php'))) {
            $pass = true;

            foreach ($override_content as $fn) {
                if (preg_match('/[A-Z]\w+.php$/', $fn)) {
                    $pass = false;
                }
            }
            $content .= 'Running Overrides: '. (!$pass ? 'Yes' : 'No');
        }

        $content .= 'Live Configuration Fields: '.Tools::getValue('fields').Amazon::LF;
        $content .='Max Input Vars: '.@ini_get('max_input_vars').'/'.@get_cfg_var('max_input_vars').Amazon::LF;
        $content .='Memory Limit: '.@ini_get('memory_limit').'/'.@get_cfg_var('memory_limit').Amazon::LF;

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $content .= Amazon::LF;
            $content .= 'AdminAmazonProducts id: '.($id_tab = Tab::getIdFromClassName('AdminAmazonProducts')).Amazon::LF;
            $content .= 'AdminAmazonOrders id: '.($id_tab = Tab::getIdFromClassName('AdminAmazonOrders')).Amazon::LF;
        }

        $content .= Amazon::LF;
        $content .= 'Check URL: '.$own_url.Amazon::LF;

        if (class_exists('PrestaShopAutoload')) {
            $prestashopAutoLoad = PrestaShopAutoload::getInstance();
            $prestashopAutoLoad->generateIndex();
            $overrides = array();

            if (is_array($prestashopAutoLoad->index) && count($prestashopAutoLoad->index)) {
                foreach ($prestashopAutoLoad->index as $item) {
                    if (stripos($item['path'], 'override/') !== false) {
                        $overrides[] = $item['path'];
                    }
                }
            }
            if (is_array($overrides) && count($overrides)) {
                $content .= Amazon::LF;
                $content .= 'Overrides:'.Amazon::LF;
                foreach ($overrides as $override) {
                    $content .= $override.Amazon::LF;
                }
            }
        }

        if (version_compare(_PS_VERSION_, 1.5, '>=')) {
            $check_duplicate_sql = 'SELECT `name`, `id_shop`, `id_shop_group`, COUNT(*) as count
                FROM `'._DB_PREFIX_.'configuration` WHERE name like "%AMAZON%"
                GROUP BY `name`, `id_shop`,  `id_shop_group`
                HAVING COUNT(*) > 1';

            $results = Db::getInstance()->executeS($check_duplicate_sql);

            if (is_array($results) && count($results)) {
                $content .= Amazon::LF;
                $content .= 'Duplicated configuration keys: '.Amazon::LF;

                foreach ($results as $result) {
                    $content .= $result['name'];
                    $content .= ": ";
                    $content .= $result['count'];
                    $content .= Amazon::LF;
                }
            }
        }

        $patternTextPrintF = html_entity_decode('%-58s : &lt;b&gt;%s&lt;/b&gt;').Amazon::LF;

        $content .= Amazon::LF;
        $content .= 'Catalog: '.Amazon::LF;

        $content .= sprintf($patternTextPrintF, 'Categories', Db::getInstance()->getValue('SELECT count(`id_category`) as count FROM `'._DB_PREFIX_.'category`'));
        $content .= sprintf($patternTextPrintF, 'Products', Db::getInstance()->getValue('SELECT count(`id_product`) as count FROM `'._DB_PREFIX_.'product`'));
        $content .= sprintf($patternTextPrintF, 'Combinations', Db::getInstance()->getValue('SELECT count(`id_product`) as count FROM `'._DB_PREFIX_.'product_attribute`'));
        $content .= sprintf($patternTextPrintF, 'Attributes', Db::getInstance()->getValue('SELECT count(`id_attribute`) as count FROM `'._DB_PREFIX_.'attribute`'));
        $content .= sprintf($patternTextPrintF, 'Features', Db::getInstance()->getValue('SELECT count(`id_feature_value`) as count FROM `'._DB_PREFIX_.'feature_value`'));
        $content .= sprintf($patternTextPrintF, 'Specific Price', Db::getInstance()->getValue('SELECT count(`id_specific_price`) as count FROM `'._DB_PREFIX_.'specific_price`'));

        if (AmazonTools::tableExists(_DB_PREFIX_.self::TABLE_MARKETPLACE_ORDERS)) {
            $results = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.self::TABLE_MARKETPLACE_ORDERS.'` ORDER BY `id_order` DESC LIMIT '.(int)Tools::getValue('orders', 10));
            $content .= Amazon::LF;
            $content .= 'Last 10 Orders: '.Amazon::LF;

            if (is_array($results) && count($results)) {
                $colums = implode(',', array_keys(reset($results)));
                $content .= print_r($colums, true).Amazon::LF;
                foreach ($results as $result) {
                    $values = implode(',', $result);
                    $content .= print_r($values, true).Amazon::LF;
                }
            }
        }

        $categories = AmazonConfiguration::get('categories');
        $profiles = AmazonConfiguration::get('profiles');
        $profiles_categories = AmazonConfiguration::get('profiles_categories');
        $mapping = AmazonConfiguration::get('mapping');

        $content .= Amazon::LF;
        $content .= 'Amazon Tables: '.Amazon::LF;
        $content .= sprintf($patternTextPrintF, 'Categories', is_array($categories) ? count($categories, COUNT_RECURSIVE) : 0);
        $content .= sprintf($patternTextPrintF, 'Profiles', is_array($profiles) ? count($profiles) : 0);
        $content .= sprintf($patternTextPrintF, 'Profiles Values', count($profiles, COUNT_RECURSIVE));
        $content .= sprintf($patternTextPrintF, 'Profiles to Categories', is_array($profiles_categories) ? count($profiles_categories, COUNT_RECURSIVE) : 0);
        $content .= sprintf($patternTextPrintF, 'Mappings', is_array($mapping) ? count($mapping) : 0);
        $content .= sprintf($patternTextPrintF, 'Mappings Values', count($mapping, COUNT_RECURSIVE));

        $orders_states = OrderState::getOrderStates($this->id_lang);
        $content .= Amazon::LF;
        $content .= 'OrderStates: '.Amazon::LF;

        foreach ($orders_states as $key => $orders_state) {
            if ($key == 0) {
                $content .= implode(', ', array_keys($orders_state)).Amazon::LF;
            }
            $content .= implode(', ', $orders_state).Amazon::LF;
        }

        $content .= Amazon::LF;
        $content .= 'Carriers'.Amazon::LF;

        if (is_array($carriers) && count($carriers)) {
            foreach ($carriers as $key => $carrier) {
                if ($key == 0) {
                    $content .= implode(', ', array_keys($carrier)).Amazon::LF;
                }
                $content .= implode(', ', $carrier).Amazon::LF;
            }
        }
        $content .= Amazon::LF;


        $content .= 'Languages'.Amazon::LF;

        foreach (Language::getLanguages(false, $this->context->shop->id) as $language) {
            $id_lang = (int)$language['id_lang'];
            $active = (bool)$language['active'];
            $language_code = $language['language_code'];
            $iso_code = $language['iso_code'];
            $name = $language['name'];

            $content .= sprintf('%d: %s - %s - %s - %s'.Amazon::LF, $id_lang, $name, $iso_code, $language_code, $active ? 'Active' : 'Inactive');
        }
        $content .= Amazon::LF;

        if (is_array($multistore_configurations) && count($multistore_configurations)) {
            $content .= 'Multistore Configurations:'.Amazon::LF;
            foreach ($multistore_configurations as $key => $multistore_configuration) {
                if (count($multistore_configurations[$key]) > 1) {
                    $content .= sprintf('%-50s : %s'.Amazon::LF, $key, print_r($multistore_configuration, true));
                }
            }
            $content .= Amazon::LF;
        }

        // Current Queue
        //
        $languages = AmazonTools::languages();
        $action_queues = AmazonProduct::getCurrentQueue();
        $content .= 'Queues'.Amazon::LF;

        if (is_array($action_queues) && count($action_queues)) {
            foreach ($action_queues as $key => $action_queue) {
                if (isset($languages[$action_queue['id_lang']])) {
                    $lang = $languages[$action_queue['id_lang']]['name'];
                    $iso_code = $languages[$action_queue['id_lang']]['iso_code'];
                } else {
                    $lang = $this->l('Inactive');
                    $iso_code = null;
                }
                $date_min = AmazonTools::displayDate($action_queue['date_min'], $this->id_lang, true);
                $date_max = AmazonTools::displayDate($action_queue['date_max'], $this->id_lang, true);

                switch ($action_queue['action']) {
                    case self::ADD:
                        $action = $this->l('Add');
                        break;
                    case self::REMOVE:
                        $action = $this->l('Delete');
                        break;
                    case self::UPDATE:
                        $action = $this->l('Update');
                        break;
                    case self::REPRICE:
                        $action = $this->l('Repricing');
                        break;
                }
                $content .= sprintf('Iso: %s Lang: %s Date Min: %s Date Max: %s Action: %s Count: %d', $iso_code, $lang, $date_min, $date_max, $action, $action_queue['count']);
                $content .= Amazon::LF;
            }
        } else {
            $content .= "Empty" . Amazon::LF;
        }


        $content .= Amazon::LF;
        $content .= 'Configuration: '.Amazon::LF;
        $content .= $ps_configuration;

        $content .= Amazon::LF;
        $content .= 'Amazon Categories: '.Amazon::LF;
        $content .= print_r($categories, true);

        $content .= Amazon::LF;
        $content .= 'Amazon Profiles to Categories: '.Amazon::LF;
        $content .= print_r(AmazonConfiguration::get('profiles_categories'), true);

        $content .= Amazon::LF;
        $content .= 'Amazon Profiles:'.Amazon::LF;
        $content .= print_r($profiles, true);

        if ($return_data) {
            $content .= Amazon::LF;
            $content .= $header_errors;

            return $content;
        } else {
            AmazonTools::pre(array($content));
            CommonTools::d($header_errors);
        }
    }

    public function phpInfo($return_data = false)
    {
        $content = '';
        $header_errors = ob_get_clean();

        ob_start();
        phpinfo(INFO_ALL & ~INFO_CREDITS & ~INFO_LICENSE & ~INFO_ENVIRONMENT & ~INFO_VARIABLES);
        $php_info = ob_get_clean();
        $php_info = preg_replace('/(a:link.*)|(body, td, th, h1, h2.*)|(img.*)/', '', $php_info);

        if ($download = Tools::getValue('download')) {
            header('Pragma: public');
            header('Cache-Control: no-cache');
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="'.AmazonTools::getFriendlyUrl(Configuration::get('PS_SHOP_NAME')).'-ps-infos.txt'.'"');

            $php_info = strip_tags(preg_replace('/<(td|th)[^>]*>/i', '<$1> ', $php_info));
            $php_info .= strip_tags($header_errors);
            die($php_info);
        } else {
            $content .= html_entity_decode("&lt;/pre&gt;") . Amazon::LF . Amazon::LF;
            $content .= html_entity_decode('&lt;h1&gt;PHP&lt;/h1&gt;'.Amazon::LF);
            $content .= html_entity_decode('&lt;div class="phpinfo"&gt;');
            $content .= $php_info;
            $content .= html_entity_decode('&lt;/div&gt;');
            $content .= $header_errors;

            if (!$return_data) {
                $protocol = Tools::strtolower(Tools::substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))).'://';
                $own_url = sprintf('%s://%s', $protocol, $_SERVER['HTTP_HOST']).$_SERVER['REQUEST_URI'];
                echo html_entity_decode('&lt;a href="'.$own_url.'&download=1" title="'.$this->l('Download').'" target="_blank"&gt;'.$this->l('Download').'&lt;/a&gt;');
                die($content);
            } else {
                return $content;
            }
        }
    }

    public function check()
    {
        ob_start();

        $merchantId = Tools::getValue('merchantId');
        $marketPlaceId = Tools::getValue('marketPlaceId');
        $awsKeyId = Tools::getValue('awsKeyId');
        $awsSecretKey = Tools::getValue('awsSecretKey');
        $marketPlaceRegion = Tools::getValue('marketPlaceRegion');
        $marketPlaceCurrency = Tools::getValue('marketPlaceCurrency');
        $mwsToken = Tools::getValue('mwsToken');

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $class_error = 'alert alert-danger';
            $class_success = 'alert alert-success';
        } else {
            $class_error = 'error';
            $class_success = 'conf confirm';
        }

        $beginDivErrorTag = html_entity_decode('&lt;div class="'.$class_error.'"&gt;');
        $endDivTag = html_entity_decode('&lt;/div&gt;');

        if (!isset($merchantId) || empty($merchantId)) {
            die($beginDivErrorTag.$this->l('Merchant Id is missing...').$endDivTag);
        }

        if (!isset($marketPlaceId) || empty($marketPlaceId)) {
            die($beginDivErrorTag.$this->l('Marketplace Id is missing...').$endDivTag);
        }

        if (!$mwsToken) {
            if (!isset($awsKeyId) || empty($awsKeyId)) {
                die($beginDivErrorTag.$this->l('AWS KeyId Id is missing...').$endDivTag);
            }

            if (!isset($awsSecretKey) || empty($awsSecretKey)) {
                die($beginDivErrorTag.$this->l('AWS Secret Key Id is missing...').$endDivTag);
            }
        }
        if (!isset($marketPlaceRegion) || empty($marketPlaceRegion)) {
            die($beginDivErrorTag.$this->l('Marketplace Platform is missing...').$endDivTag);
        }

        if (!isset($marketPlaceCurrency) || empty($marketPlaceCurrency)) {
            die($beginDivErrorTag.$this->l('Marketplace Currency is missing...').$endDivTag);
        }

        $auth = array(
            'MerchantID' => trim($merchantId),
            'MarketplaceID' => trim($marketPlaceId),
            'AWSAccessKeyID' => trim($awsKeyId),
            'SecretKey' => trim($awsSecretKey),
            'mwsToken' => trim($mwsToken)
        );

        $marketPlace = array();
        $marketPlace['Currency'] = $marketPlaceCurrency;
        $marketPlace['Country'] = $marketPlaceRegion;

        if (!$amazonApi = new AmazonWebService($auth, $marketPlace, null, Amazon::$debug_mode)) {
            die($this->l('Unable to login'));
        }

        $result = $amazonApi->listMarketplaceParticipations();

        if (Amazon::$debug_mode) {
            echo nl2br(print_r($result, true));
            echo ob_get_clean();
            die;
        }

        if (isset($result->ListMarketplaceParticipationsResult->ListParticipations)) {
            die(html_entity_decode('&lt;div class="'.$class_success.'"&gt;&lt;b&gt;'.$this->l('Connection to Amazon : Ok').'&lt;/b&gt; - ('.$result->ResponseMetadata->RequestId.')'.$endDivTag));
        } elseif (is_object($result) && isset($result->Error)) {
            die($beginDivErrorTag.$this->l('Warning: Connection to Amazon Failed !').nl2br(Amazon::LF.print_r($result->Error, true)).$endDivTag);
        } else {
            die($beginDivErrorTag.$this->l('Warning: Connection to Amazon Failed !').nl2br(Amazon::LF.print_r($result, true)).$endDivTag);
        }
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }

    public function status()
    {
        $statuses = array();
        $status = true;
        $pass = true;
        $errors = null;

        $regions = AmazonConfiguration::get('REGION');
        $actives = AmazonConfiguration::get('ACTIVE');

        $merchantIds = Tools::getValue('merchantId');
        $awsKeyIds = Tools::getValue('awsKeyId');
        $awsSecretKeys = Tools::getValue('awsSecretKey');
        $marketPlaceRegions = Tools::getValue('marketPlaceRegion');


        if (!is_array($merchantIds)) {
            //
            $pass = false;
        } else {
            $merchantIds = array_unique($merchantIds);

            foreach ($merchantIds as $id_lang => $merchantId) {
                if (empty($merchantId)) {
                    continue;
                }

                if (!isset($actives[$id_lang]) || !$actives[$id_lang]) {
                    continue;
                }

                if (!isset($marketPlaceRegions[$id_lang]) || empty($marketPlaceRegions[$id_lang])) {
                    continue;
                }

                if (!isset($awsKeyIds[$id_lang]) || empty($awsKeyIds[$id_lang])) {
                    continue;
                }

                if (!isset($awsSecretKeys[$id_lang]) || empty($awsSecretKeys[$id_lang])) {
                    continue;
                }

                $auth = array(
                    'MerchantID' => $merchantId,
                    'AWSAccessKeyID' => trim($awsKeyIds[$id_lang]),
                    'SecretKey' => trim($awsSecretKeys[$id_lang])
                );

                $marketPlace = array();
                $marketPlace['Country'] = $marketPlaceRegions[$id_lang];

                if (!$amazonApi = new AmazonWebService($auth, $marketPlace, null, Amazon::$debug_mode)) {
                    $errors .= sprintf($this->l('Unable to init API for merchant: %s, please check the configuration').nl2br(Amazon::LF), $merchantId);
                    continue;
                }
                $request_time = time();
                $result = $amazonApi->serviceStatus(true);

                $statuses[$merchantId] = array();
                $statuses[$merchantId]['platform'] = 'Amazon '.Tools::strtoupper($marketPlaceRegions[$id_lang]);
                $statuses[$merchantId]['image'] = $this->images.'status_red.png';

                if (isset($result->GetServiceStatusResult)) {
                    $statuses[$merchantId]['status'] = isset($result->GetServiceStatusResult->Status) ? (string)$result->GetServiceStatusResult->Status : $this->l('Unknown');
                    $statuses[$merchantId]['datetime'] = date('Y-m-d H:i:s', (int)strtotime($result->GetServiceStatusResult->Timestamp));

                    switch ($statuses[$merchantId]['status']) {
                        case 'GREEN':
                        case 'GREEN_I':
                            $statuses[$merchantId]['image'] = $this->images.'status_green.png';
                            $status = true && $status;
                            break;
                        case 'YELLOW':
                            $statuses[$merchantId]['image'] = $this->images.'status_yellow.png';
                            $status = true && $status;
                            break;
                        default:
                            $status = false && $status;
                            break;
                    }

                    if (isset($result->GetServiceStatusResult->Timestamp)) {
                        $statuses[$merchantId]['drift'] = (int)strtotime($result->GetServiceStatusResult->Timestamp) - $request_time;
                    } else {
                        $statuses[$merchantId]['drift'] = 0;
                    }

                    $statuses[$merchantId]['messages'] = null;

                    if (isset($result->GetServiceStatusResult->Messages)) {
                        foreach ($result->GetServiceStatusResult->Messages as $message) {
                            $statuses[$merchantId]['messages'] .= (string)$message.nl2br(Amazon::LF);
                        }
                    } else {
                        $statuses[$merchantId]['messages'] .= $this->l('Everything is OK, no message has been returned').nl2br(Amazon::LF);
                    }

                    if ((int)$statuses[$merchantId]['drift'] > 30) {
                        $statuses[$merchantId]['messages'] .= sprintf($this->l('There is a time drift of %d seconds. Amazon allows a maximum of 2 minutes. Please consider this point.').nl2br(Amazon::LF), $statuses[$merchantId]['drift']);
                    }
                } else {
                    $statuses[$merchantId]['status'] = $this->l('Failed');
                    $statuses[$merchantId]['datetime'] = null;
                    $statuses[$merchantId]['drift'] = null;
                    $statuses[$merchantId]['messages'] = $this->l('Unable to connect to');
                }
            }
        }
        $callback = Tools::getValue('callback');

        if ($callback == '?' || empty($callback)) {
            $callback = 'jsonp_'.time();
        }

        die((string)$callback.'('.Tools::jsonEncode(array(
                'error' => !$pass,
                'errors' => $errors,
                'status' => $status,
                'statuses' => $statuses
            )).')');
    }

    public function participations()
    {
        $participations = array();
        $status = true;
        $pass = true;
        $errors = null;
        $result = null;

        $actives = AmazonConfiguration::get('ACTIVE');

        $marketplacesIds = AmazonConfiguration::get('MARKETPLACE_ID');

        if (is_array($marketplacesIds) && count($marketplacesIds)) {
            $current_marketplaces = array_unique(array_flip($marketplacesIds));
        } else {
            $current_marketplaces = null;
        }

        $merchantIds = Tools::getValue('merchantId');
        $awsKeyIds = Tools::getValue('awsKeyId');
        $awsSecretKeys = Tools::getValue('awsSecretKey');
        $marketPlaceRegions = Tools::getValue('marketPlaceRegion');
        $mwsToken = Tools::getValue('mwsToken');
        $results = null;

        if (!is_array($merchantIds)) {
            //
            $pass = false;
        } else {
            $merchantIds = array_unique($merchantIds);

            foreach ($merchantIds as $id_lang => $merchantId) {
                if (empty($merchantId)) {
                    continue;
                }

                if (!isset($actives[$id_lang]) || !$actives[$id_lang]) {
                    continue;
                }

                if (!isset($marketPlaceRegions[$id_lang]) || empty($marketPlaceRegions[$id_lang])) {
                    continue;
                }

                if (!isset($awsKeyIds[$id_lang]) || empty($awsKeyIds[$id_lang])) {
                    continue;
                }

                if (!isset($awsSecretKeys[$id_lang]) || empty($awsSecretKeys[$id_lang])) {
                   // continue;
                }


                $auth = array(
                    'MerchantID' => $merchantId,
                    'AWSAccessKeyID' => trim($awsKeyIds[$id_lang]),
                    'SecretKey' => trim($awsSecretKeys[$id_lang]),
                    'mwsToken' => trim($mwsToken[$id_lang])
                );

                $marketPlace = array();
                $marketPlace['Country'] = $marketPlaceRegions[$id_lang];

                if (!$amazonApi = new AmazonWebService($auth, $marketPlace, null, Amazon::$debug_mode)) {
                    $errors .= sprintf($this->l('Unable to init API for merchant: %s, please check the configuration').nl2br(Amazon::LF), $merchantId);
                    continue;
                }

                $result = $amazonApi->listMarketplaceParticipations(true);

                if ($result instanceof SimpleXMLElement) {
                    $results .= $result->asXML();
                } else {
                    $results .= print_r($result, true);
                }

                $participations[$merchantId] = array();

                if (isset($result->ListMarketplaceParticipationsResult->ListParticipations->Participation)) {
                    foreach ($result->ListMarketplaceParticipationsResult->ListParticipations->Participation as $Participation) {
                        if ((string)$Participation->SellerId != (string)$merchantId) {
                            continue;
                        }

                        if (!isset($participations[$merchantId][(string)$Participation->MarketplaceId])) {
                            $participations[$merchantId][(string)$Participation->MarketplaceId] = array();
                        }

                        $l_status = false;

                        if ($current_marketplaces) {
                            if (isset($current_marketplaces[(string)$Participation->MarketplaceId]) && $current_marketplaces[(string)$Participation->MarketplaceId] && isset($actives[$current_marketplaces[(string)$Participation->MarketplaceId]]) && $actives[$current_marketplaces[(string)$Participation->MarketplaceId]]) {
                                $l_status = true;
                            }
                        }

                        $participations[$merchantId][(string)$Participation->MarketplaceId]['l_status'] = ($l_status ? $this->l('Active') : $this->l('Inactive'));
                        $participations[$merchantId][(string)$Participation->MarketplaceId]['l_image'] = ($l_status ? $this->images.'status_green.png' : $this->images.'status_red.png');

                        $participations[$merchantId][(string)$Participation->MarketplaceId]['r_status'] = ($Participation->HasSellerSuspendedListings == 'No' ? $this->l('Valid') : $this->l('Suspended'));
                        $participations[$merchantId][(string)$Participation->MarketplaceId]['r_image'] = ($Participation->HasSellerSuspendedListings == 'No' ? $this->images.'status_green.png' : $this->images.'status_red.png');
                    }

                    foreach ($result->ListMarketplaceParticipationsResult->ListMarketplaces->Marketplace as $Marketplace) {
                        $participations[$merchantId][(string)$Marketplace->MarketplaceId]['name'] = (string)$Marketplace->Name;
                        $participations[$merchantId][(string)$Marketplace->MarketplaceId]['cc'] = (string)$Marketplace->DefaultCountryCode;
                        $participations[$merchantId][(string)$Marketplace->MarketplaceId]['currency'] = (string)$Marketplace->DefaultCurrencyCode;
                        $participations[$merchantId][(string)$Marketplace->MarketplaceId]['domain'] = (string)$Marketplace->DomainName;

                        $currency_state = false;

                        if (($id_currency = Currency::getIdByIsoCode((string)$Marketplace->DefaultCurrencyCode))) {
                            $currency = new Currency($id_currency);

                            if (Validate::isLoadedObject($currency)) {
                                $currency_state = true;
                            } else {
                                $currency_state = false;
                            }
                        } else {
                            $currency_state = false;
                        }

                        $participations[$merchantId][(string)$Marketplace->MarketplaceId]['currency_state'] = $currency_state;

                        if (!isset($participations[$merchantId][(string)$Marketplace->MarketplaceId]['l_status'])) {
                            $l_status = false;

                            if ($current_marketplaces) {
                                if (isset($current_marketplaces[(string)$Marketplace->MarketplaceId]) && $current_marketplaces[(string)$Marketplace->MarketplaceId] && isset($actives[$current_marketplaces[(string)$Marketplace->MarketplaceId]]) && $actives[$current_marketplaces[(string)$Marketplace->MarketplaceId]]) {
                                    $l_status = true;
                                }
                            }
                        }

                        if (!isset($participations[$merchantId][(string)$Marketplace->MarketplaceId]['r_status'])) {
                            $participations[$merchantId][(string)$Marketplace->MarketplaceId]['l_status'] = ($l_status ? $this->l('Active') : $this->l('Inactive'));
                            $participations[$merchantId][(string)$Marketplace->MarketplaceId]['l_image'] = ($l_status ? $this->images.'status_green.png' : $this->images.'status_red.png');

                            $participations[$merchantId][(string)$Marketplace->MarketplaceId]['r_status'] = $this->l('Unknown');
                            $participations[$merchantId][(string)$Marketplace->MarketplaceId]['r_image'] = $this->images.'status_yellow.png';
                        }
                    }
                }
            }
        }
        if (!count($participations)) {
            $pass = false;
        }

        $callback = Tools::getValue('callback');

        if ($callback == '?' || empty($callback)) {
            $callback = 'jsonp_'.time();
        }

        die((string)$callback.'('.Tools::jsonEncode(array(
                'error' => !$pass,
                'errors' => $errors,
                'status' => $status,
                'result' => $results,
                'participations' => $participations,
                'console' => ob_get_clean()
            )).')');
    }
}

$pmConnexionCheck = new AmazonConnexionCheck();
$pmConnexionCheck->dispatch();
