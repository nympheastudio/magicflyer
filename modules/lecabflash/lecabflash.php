<?php
/**
* 2009-2017 202 ecommerce
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*  @author    202 ecommerce <support@202-ecommerce.com>
*  @copyright 2009-2017 202 ecommerce SARL
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}


require_once _PS_MODULE_DIR_.DIRECTORY_SEPARATOR.'lecabflash'.DIRECTORY_SEPARATOR.'sdk/lecabFlashSdk.php';

class LecabFlash extends Module
{

    protected $context;
    private $debug = false;
    private $current_quotation = null;
    private $current_quotation_drop = null;

    public function __construct()
    {
        $this->name                   = 'lecabflash';
        $this->tab                    = 'shipping_logistics';
        $this->version                = '1.1.2';
        $this->author                 = 'LeCabFlash';
        $this->need_instance          = 0;
        $this->ps_versions_compliancy = array('min' => '1.6');
        $this->bootstrap              = true;
        $this->ajax                   = true;
        $this->module_key = '7be65b03d758523d71a1afc7882ed414';

        parent::__construct();

        $this->displayName      = $this->l('LeCabFlash');
        $this->description      = $this->l('Choose the best express shipping under one hour available in Paris and around');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module ?');

        // init SDK
        if ($this->debug) {
            $this->sdk = new LecabFlashSdk(Configuration::get('LECABFLASH_API_KEY'), true);
        } else {
            $this->sdk = new LecabFlashSdk(Configuration::get('LECABFLASH_API_KEY'));
        }

        // init logger
        $log_level = ($this->debug==true) ? 'DEBUG' : 'INFO';
        LecabFlashLog::init($log_level, array($this,'log'));

        //
        $this->current_quotation = null;
    }

    public function log($severity, $msg)
    {
        $filepath = dirname(__FILE__).'/logs/lecabflash.log';

        $info = array(
            date('Y-m-d H:i:s'),
            '['.Tools::strtoupper($severity).']',
            $msg,
            "\n"
        );
       

        file_put_contents($filepath, implode("\t", $info), FILE_APPEND | LOCK_EX);
    }

    /* INSTALL */
    public function install()
    {
        if (!extension_loaded('curl')) {
            $this->_errors[] = $this->l('lecabflash requires curl php extension');
            return false;
        }

        if (parent::install()
            && $this->installDb()
            && $this->hookRegistration()
            && $this->createConfiguration()
            && $this->createLecabFlashCarrier()) {
            return true;
        }

        # clear smarty cache
        Tools::clearSmartyCache();

        return false;
    }

    private function installDb()
    {
        $return = true;
        $sql = array();

        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lecabflash_carts` (
            `id_cart` int(10) unsigned NOT NULL,
            `estimate_id` varchar(100) DEFAULT NULL,
            `price` float DEFAULT NULL,
            `pickup_date` datetime DEFAULT NULL,
            `drop_date` datetime DEFAULT NULL,
            `estimate_context` text,
            `estimate_response` text,
            `estimate_delay` int(10) unsigned DEFAULT NULL,
            `estimate_duration_min` int(10) unsigned DEFAULT NULL,
            `estimate_duration_max` int(10) unsigned DEFAULT NULL,
            `confirm_request` text,
            `confirm_response` text,
            `confirm_id` varchar(100) DEFAULT NULL,
            `confirm_delay` int(10) unsigned DEFAULT NULL,
            `confirm_number` INT(11) DEFAULT NULL,
            `confirm_url` varchar(255) DEFAULT NULL,
            `drop_address` varchar(255) DEFAULT NULL,
            `error_code` varchar(10) DEFAULT NULL,
            `error_msg` varchar(255) DEFAULT NULL,
            `pickup_info` varchar(255) DEFAULT NULL,
            `last_error` int(10) unsigned DEFAULT NULL,
            PRIMARY KEY (`id_cart`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lecabflash_hours` (
            `id_lecabflash_hours` int(11) NOT NULL AUTO_INCREMENT,
            `id_shop` INT(11) NOT NULL,
            `weekday` TINYINT(1) UNSIGNED NOT NULL,
            `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
            `am_open` VARCHAR(5) NOT NULL,
            `am_close` VARCHAR(5) NOT NULL,
            `pm_open` VARCHAR(5) NOT NULL,
            `pm_close` VARCHAR(5) NOT NULL,
            PRIMARY KEY (`id_lecabflash_hours`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        foreach ($sql as $s) {
            $return &= Db::getInstance()->execute($s);
        }
        return $return;
    }


    public function hookRegistration()
    {
        return ($this->registerHook('updateCarrier')
        && $this->registerHook('displayCarrierList')
        && $this->registerHook('displayBeforeCarrier')
        && $this->registerHook('displayOrderDetail')
        && $this->registerHook('displayAdminOrder')
        && $this->registerHook('actionAdminControllerSetMedia')
        && $this->registerHook('displayHeader')
        && $this->registerHook('actionCarrierProcess')
        && $this->registerHook('actionValidateOrder'));
    }


    public function createLecabFlashCarrier()
    {
        $zoneList  = Zone::getZones();
        $groupList = Group::getGroups($this->context->language->id);

        if (!Configuration::get('LECABFLASH_CARRIER_ID')) {
            $carrier          = new Carrier();
            $carrier->name    = 'LeCabFlash';
            $carrier->delay[Configuration::get('PS_LANG_DEFAULT')] = 'Votre commande chez vous en une heure !';
            $carrier->active  = false;
            $carrier->is_free = false;
            $carrier->shipping_external = true;
            $carrier->need_range = true;
            $carrier->is_module = true;
            $carrier->external_module_name = pSQL($this->name);
            $carrier->max_weight = LECABFLASH_MAX_WEIGHT;
            $carrier->save();
            //
            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = LECABFLASH_MAX_WEIGHT;
            $rangeWeight->add();

            // ZONE
            foreach ($zoneList as $zone) {
                $carrier->addZone($zone['id_zone']);
            }
            $carrier->save();

            $carrier->id_reference = $carrier->id;
            $carrier->save();
            foreach ($groupList as $group) {
                Db::getInstance()->insert('carrier_group', array(
                    'id_carrier' => (int) $carrier->id,
                    'id_group'   => (int) $group['id_group'],
                ));
            }
            Configuration::updateValue('LECABFLASH_CARRIER_ID', (int)$carrier->id_reference);

            // LOGO
            if (!copy(dirname(__FILE__).'/views/img/logo-lecab.png', _PS_SHIP_IMG_DIR_.'/'.$carrier->id.'.jpg')) {
                return false;
            }
        }
        return true;
    }




    /* UNINSTALL */

    public function uninstall()
    {
        if (parent::uninstall()
            && $this->deletelecabflashCarriers()
            && $this->deleteConfiguration()
            && $this->uninstallDb()
        ) {
            return true;
        }
        return false;
    }

    public function enable($force_all = false)
    {
        $res = parent::enable($force_all);
        if ($res && Configuration::get('LECABFLASH_CARRIER_ID')) {
            $this->enableLecabFlash();
        }

        return $res;
    }

    public function disable($force_all = false)
    {
        $this->disableLecabFlash();
        parent::disable($force_all);
    }

    private function createConfiguration()
    {
        Configuration::updateValue('LECABFLASH_PRICE_REAL', 1);
        Configuration::updateValue('LECABFLASH_USE_TVA', 1);
        Configuration::updateValue('LECABFLASH_PRICE_FIXED', 0);
        Configuration::updateValue('LECABFLASH_PRICE_RATE', 0);
        Configuration::updateValue('LECABFLASH_API_KEY', 'cEst4ZV3IHExh99XrF9vsOkjJtf5OONS');
        Configuration::updateValue('LECABFLASH_TOKEN_HASH', md5(time()));

        return true;
    }

    private function deleteConfiguration()
    {
        $keys = array(
            'LECABFLASH_ACTIVE',
            'LECABFLASH_CARRIER_ID',
            'LECABFLASH_API_KEY',
            'LECABFLASH_TOKEN_HASH',
            'LECABFLASH_PRICE_FIXED',
            'LECABFLASH_PRICE_RATE',
            'LECABFLASH_USE_TVA',
            'LECABFLASH_PICKUP_ADDRESS',
            'LECABFLASH_PICKUP_INFO',
            'LECABFLASH_PICKUP_LATITUDE',
            'LECABFLASH_PICKUP_LONGITUDE',
            'LECABFLASH_PRICE_REAL',
            'LECABFLASH_SHOW_CGU'
        );
        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }
        return true;
    }


    private function deletelecabflashCarriers()
    {
        $carrier = Carrier::getCarrierByReference(Configuration::get('LECABFLASH_CARRIER_ID'));
        if ($carrier && $carrier->external_module_name === $this->name) {
            $carrier->deleted = true;
            $carrier->save();
        }
        return true;
    }

    public function uninstallDb()
    {
        Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."lecabflash_carts");
        Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."lecabflash_hours");
        return true;
    }



    /* CORE PLUGIN */


    private function enableLecabFlash()
    {
        Configuration::updateValue('LECABFLASH_ACTIVE', true);
        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'carrier SET active=1 WHERE id_carrier = '.(int)Configuration::get('LECABFLASH_CARRIER_ID'));
    }

    private function disableLecabFlash()
    {
        Configuration::updateValue('LECABFLASH_ACTIVE', false);
        Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'carrier SET active=0 WHERE id_carrier = '.(int)Configuration::get('LECABFLASH_CARRIER_ID'));
    }


    public function handleAjax()
    {
        $action = Tools::getValue('action');

        switch ($action) {
            case "updateSettingsKey":
                if (!Validate::isString(Tools::getValue('lecabflash_api_key'))) {
                    return array(
                        'success'=> false,
                        'message'=> $this->l('The input format is invalid. Please try again.')
                    );
                }
                $api_key  = (string) Tools::getValue('lecabflash_api_key');
                $old_api_key = Configuration::get('LECABFLASH_API_KEY');
                if ($api_key!=$old_api_key) {
                    if (LecabFlashSdk::checkApiKey($api_key)) {
                        Configuration::updateValue('LECABFLASH_API_KEY', $api_key);
                        return array(
                            'success'=> true,
                            'message'=> $this->l('API key updated ')
                        );
                    } else {
                        return array(
                            'success'=> false,
                            'message'=> $this->l('API key incorrect ')
                        );
                    }
                }
                break;
            case "updateSettingsAddr":
                if (!Validate::isString(Tools::getValue('lecabflash_pickup_address_info'))
                && !Validate::isString(Tools::getValue('lecabflash_pickup_address'))) {
                    return array(
                        'success'=> false,
                        'message'=> $this->l('The input format is invalid. Please try again.')
                    );
                }
                $pickup_info  = (string)Tools::getValue('lecabflash_pickup_address_info');
                Configuration::updateValue('LECABFLASH_PICKUP_INFO', $pickup_info);

                $pickup_address  = (string)Tools::getValue('lecabflash_pickup_address');
                $old_pickup_address = Configuration::get('LECABFLASH_PICKUP_ADDRESS');
                $pickup_address_tel = (string)Tools::getValue('lecabflash_pickup_telephone');
                $is_phone = Validate::isPhoneNumber($pickup_address_tel);
                $old_pickup_address_tel = Configuration::get('LECABFLASH_PICKUP_PHONE');

                if (Tools::strlen(Tools::substr($pickup_address_tel, 1)) != 11) {
                    $is_phone = false;
                }
                if ($pickup_address != $old_pickup_address || $pickup_address_tel != $old_pickup_address_tel) {
                    $check_address = $this->sdk->checkAddress($pickup_address);
                    
                    if ($check_address && !isset($check_address['CRS_error'])) {
                        if (!$is_phone) {
                            return array(
                                'success'=> false,
                                'message'=> $this->l('You phone number is incorrect, please respect the following format : +33612345678')
                            );
                        }
                        Configuration::updateValue('LECABFLASH_PICKUP_PHONE', $pickup_address_tel);
                        Configuration::updateValue('LECABFLASH_PICKUP_ADDRESS', $pickup_address);
                        Configuration::updateValue('LECABFLASH_PICKUP_LATITUDE', $check_address['latitude']);
                        Configuration::updateValue('LECABFLASH_PICKUP_LONGITUDE', $check_address['longitude']);
                        return array(
                            'success'=> true,
                            'message'=> $this->l('Address updated')
                        );
                    } else {
                        return array(
                            'success'=> false,
                            'message'=> $this->l('Your address does not match our shipping zone in Paris and inner suburbs.')
                        );
                    }
                }
                break;
            case "updateSettingsHours":
                $weekdays = array('monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6,'sunday'=>7);
                foreach ($weekdays as $name => $weekday) {
                    if (Tools::getValue($name . '_am_open_hour') != '' && !preg_match('/^[0-9]+$/', Tools::getValue($name . '_am_open_hour'))
                        || Tools::getValue($name . '_am_open_min') != '' && !preg_match('/^[0-9]+$/', Tools::getValue($name . '_am_open_min'))
                        || Tools::getValue($name . '_am_close_hour') != '' && !preg_match('/^[0-9]+$/', Tools::getValue($name . '_am_close_hour'))
                        || Tools::getValue($name . '_am_close_min') != '' && !preg_match('/^[0-9]+$/', Tools::getValue($name . '_am_close_min'))
                        || Tools::getValue($name . '_pm_open_hour') != '' && !preg_match('/^[0-9]+$/', Tools::getValue($name . '_pm_open_hour'))
                        || Tools::getValue($name . '_pm_open_min') != '' && !preg_match('/^[0-9]+$/', Tools::getValue($name . '_pm_open_min'))
                        || Tools::getValue($name . '_pm_close_hour') != '' && !preg_match('/^[0-9]+$/', Tools::getValue($name . '_pm_close_hour'))
                        || Tools::getValue($name . '_pm_close_min') != '' && !preg_match('/^[0-9]+$/', Tools::getValue($name . '_pm_close_min'))) {
                        return array(
                            'success' => false,
                            'message' => $this->l('The input format is invalid. Please try again.')
                        );
                    }


                    $active = (Tools::getValue($name)=='true');

                    $amoh = (int)Tools::getValue($name.'_am_open_hour');
                    $amom = (int)Tools::getValue($name.'_am_open_min');
                    $am_open = $amoh ? sprintf("%'.02d:%'.02d", $amoh, $amom) : null;

                    $amch = (int)Tools::getValue($name.'_am_close_hour');
                    $amcm = (int)Tools::getValue($name.'_am_close_min');
                    $am_close = $amch ? sprintf("%'.02d:%'.02d", $amch, $amcm) : null;

                    $pmoh = (int)Tools::getValue($name.'_pm_open_hour');
                    $pmom = (int)Tools::getValue($name.'_pm_open_min');
                    $pm_open = $pmoh ? sprintf("%'.02d:%'.02d", $pmoh, $pmom) : null;

                    $pmch = (int)Tools::getValue($name.'_pm_close_hour');
                    $pmcm = (int)Tools::getValue($name.'_pm_close_min');
                    $pm_close = $pmch ? sprintf("%'.02d:%'.02d", $pmch, $pmcm) : null;

                    Db::getInstance()->delete('lecabflash_hours', 'weekday='.(int)$weekday.' AND id_shop='.(int)$this->context->shop->id);
                    Db::getInstance()->insert(
                        'lecabflash_hours',
                        array(
                            'id_shop'   => (int)$this->context->shop->id,
                            'weekday'   => (int)$weekday,
                            'active'    => $active,
                            'am_open'   => pSQL($am_open),
                            'am_close'  => pSQL($am_close),
                            'pm_open'   => pSQL($pm_open),
                            'pm_close'  => pSQL($pm_close),
                        )
                    );
                }
                return array(
                    'success'=> true,
                    'message'=> $this->l('Your schedule has been updated')
                );
                break;
            case "updateSettingsPrices":
                $price_fixed = Tools::getValue('lecabflash_price_fixed');
                $price_rate = Tools::getValue('lecabflash_price_rate');

                if (!is_numeric($price_fixed)&&is_numeric($price_rate)) {
                    return array(
                        'success'=> false,
                        'message'=> $this->l('The input format is invalid. Please try again.')
                    );
                }

                Configuration::updateValue('LECABFLASH_PRICE_REAL', Tools::getValue('lecabflash_price_real'));
                Configuration::updateValue('LECABFLASH_PRICE_FIXED', Tools::getValue('lecabflash_price_fixed'));
                Configuration::updateValue('LECABFLASH_PRICE_RATE', Tools::getValue('lecabflash_price_rate'));

                if (Configuration::get('LECABFLASH_API_KEY') && Configuration::get('LECABFLASH_PICKUP_ADDRESS')) {
                    $this->enableLecabFlash();
                }
                //Configuration::updateValue('LECABFLASH_USE_TVA', Tools::getValue('lecabflash_use_tva'));
                //Configuration::updateValue('LECABFLASH_SHOW_CGU', Tools::getValue('lecabflash_show_cgu'));
                return array(
                    'success'=> true,
                    'message'=> $this->l('Update done')
                );
                break;
            case "getquote":
                $idcart = $this->context->cart->id;
                
                $date = Tools::getValue('date');

                if (Tools::getValue('info') != '' && !Validate::isMessage(Tools::getValue('info'))) {
                    return array(
                        'error_msg' => $this->l('The input format is invalid. Please try again.')
                    );
                }

                $pickup_info = Tools::getValue('info') ? Tools::getValue('info') : null;

                return $this->setQuotation((int)$idcart, $date, $pickup_info);
                break;
            case "runtest":
                if ($this->debug) {
                    $tests = new LecabFlashTests();
                    $test = Tools::getValue('test');
                    return $tests->run($test);
                }
                break;
            case "logs":
                if ($this->debug) {
                    return nl2br(Tools::file_get_contents(dirname(__FILE__).'/logs/lecabflash.log'));
                }
                break;
        }
    }


    private function logLecabFlashInfo($id_cart, $field, $data)
    {
        Db::getInstance()->update(
            'lecabflash_carts',
            array(
                $field => pSQL(Tools::jsonEncode((array)$data)),
            ),
            '`id_cart` = '.(int)$id_cart
        );
    }

    private function logEstimateJobContext($id_cart, $request)
    {
        return $this->logLecabFlashInfo($id_cart, 'estimate_context', $request);
    }

    private function logEstimateJobResponse($id_cart, $response)
    {
        return $this->logLecabFlashInfo($id_cart, 'estimate_response', $response);
    }

    private function logConfirmJobRequest($id_cart, $request)
    {
        return $this->logLecabFlashInfo($id_cart, 'confirm_request', $request);
    }

    private function logConfirmJobResponse($id_cart, $response)
    {
        return $this->logLecabFlashInfo($id_cart, 'confirm_response', $response);
    }
  

    private function getLecabHoursConfig()
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'lecabflash_hours ORDER BY weekday ASC';
        $rows = Db::getInstance()->ExecuteS($sql);
        $config = array();
        foreach ($rows as $row) {
            $weekday = (int) $row['weekday'];
            $active = (int) $row['active'];
            $am_open = $active ? $row['am_open'] : null;
            $am_close = $active ? $row['am_close'] : null;
            $pm_open = $active ? $row['pm_open'] : null;
            $pm_close = $active ? $row['pm_close'] : null;
            $config[ $weekday ] = array($active, $am_open, $am_close, $pm_open, $pm_close);
        }
        return $config;
    }


    private function getDatetime($date)
    {
        $datetime = DateTime::createFromFormat(DateTime::ISO8601, $date);
        if (!$datetime) {
            $datetime = DateTime::createFromFormat('Y-m-d H:i', $date);
        }
        if (!$datetime) {
            $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        }
        return $datetime;
    }

    private function translateDate($date)
    {
        $mapping_days = array('',$this->l('Monday'),$this->l('Tuesday'),$this->l('Wednesday'),$this->l('Thursday'),$this->l('Friday'),$this->l('Saturday'),$this->l('Sunday'));
        $mapping_month = array('',$this->l('January'),$this->l('February'),$this->l('March'),$this->l('April'),$this->l('May'),$this->l('June'),$this->l('July'),$this->l('August'),$this->l('September'),$this->l('October'),$this->l('November'),$this->l('December'));
        $datetime = $this->getDatetime($date);
        if ($datetime) {
            $strday = $mapping_days[(int)$datetime->format('N')];
            $strmonth = $mapping_month[(int)$datetime->format('n')];
            $d = $datetime->format('d');
            $y = $datetime->format('Y');
            $hour = $datetime->format('H:i');
            return $strday.' '.$d.' '.$strmonth.' '.$y.' '.$hour;
        }
        return '';
    }


    private function checkExpressAvailable($date = null)
    {
        // $weekday = ISO-8601 du jour de la semaine 1 (pour Lundi) à 7 (pour Dimanche)
        if ($date) {
            $datetime = $this->getDatetime($date);
        } else {
            $datetime = new DateTime();
        }

        //$now->setTimezone($tz_object);
        // $weekday = $datetime->format('N');
        $config = $this->getLecabHoursConfig();

        return $this->sdk->checkExpressAvailable($config, $datetime) ;
    }


    /**
     * @param int    $id_cart
     * @param string $date
     * @param string $pickup_info
     * @param bool $is_estimate
     * @return bool|null
     */
    private function setQuotation($id_cart, $date = null, $pickup_info = null, $is_estimate = false)
    {
        $cart = new Cart((int)$id_cart);
        $id_address_delivery = (int)$cart->id_address_delivery;
        
        if (!$id_address_delivery) {
            /* prevent quotations without adresses */
            return false;
        }

        $address = new Address($id_address_delivery);
        $products = $cart->getProducts();

        $street1    = $address->address1;
        $street2    = $address->address2;
        $city       = $address->city;
        $zipcode    = $address->postcode;
        $country    = $address->country;

        $drop_address = $street1.' '.$street2.' ,'.$zipcode.' '.$city.','.$country;

        $data = array(
            'config'=> array(
                'price_fixed'   => Configuration::get('LECABFLASH_PRICE_FIXED'),
                'price_rate'    => Configuration::get('LECABFLASH_PRICE_RATE'),
                'use_tva'       => Configuration::get('LECABFLASH_USE_TVA'),
            ),
            'pickup'=>array(
                'latitude'      => Configuration::get('LECABFLASH_PICKUP_LATITUDE'),
                'longitude'     => Configuration::get('LECABFLASH_PICKUP_LONGITUDE'),
            ),
            'drop'=> array(
                'address'   => $drop_address,
            ),
            'products'=> array(),

        );
        if ($date) {
            $datetime = DateTime::createFromFormat('Y-m-d H:i', $date);
            $now = new DateTime('+30 min');
            if ($datetime < $now) {
                $datetime = clone $now;
            }
            // $store_open = $this->checkExpressAvailable($datetime->format('c'));
            // if (!$store_open) {
            //     return array(
            //         'error_msg' => $this->l('Unfortunately our shop will be closed on this day. Please try another day.')
            //     ); // date non compatible
            // }
            $data['date'] = $datetime->format('c'); // dont use DateTime::ISO8601(provide +0200 and we want +02:00)
        }

        $is_rdv = Tools::getValue('rdv') == 'true' ? true : false;
        $data['rdv'] = $is_rdv;

        # 50cmx39x15cm, 30L, 8kg
        foreach ($products as $product) {
            $data['products'][] = array(
                'width'=> $product['width'],
                'height'=> $product['height'],
                'depth'=> $product['depth'],
                'weight'=> $product['weight'],
            );
        }

        // retreiving information about Availability & Quotation
        $result = $this->sdk->checkShippingAvailability($data);

        // set result for this cart for checkout
        $context = (array)$data;
        $result = (array)$result;
        $error_msg = null;
        $error_code = null;

        if (isset($result['error'])) {
            $error_msgs = array();
            foreach ((array)$result['error'] as $key => $val) {
                $error_msgs[] = $key.' : '.$val[0];
            }
            $error_msg = implode('\n', $error_msgs);
            $error_code = isset($result['error']) ? $result['code'] : null;
        }

        if (isset($result['code']) && $result['code'] == -10) {
            $error_code = -10;
        }

        $estimate_id = isset($result['estimate_id']) ? $result['estimate_id'] : null;
        $price = isset($result['price']) ? $result['price'] : null;
        $date_pickup = isset($result['date_pickup']) ? $result['date_pickup'] : null;
        $date_drop = isset($result['date_drop']) ? $result['date_drop'] : null;

        if (!$is_estimate) {
            if ($date_pickup) {
                $store_open = $this->checkExpressAvailable($date_pickup);
                if (!$store_open) {
                    return array(
                        'error_msg' => $this->l('Unfortunately our shop will be closed on this day. Please try another day.')
                    ); // date non compatible
                }
            }
        }

        // logs/journal
        $exist = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'lecabflash_carts WHERE id_cart = '.(int)$id_cart);
        if (!$exist) {
            LecabFlashLog::info('INSERT QUOTATION FOR CART '.$id_cart);
            Db::getInstance()->insert(
                'lecabflash_carts',
                array(
                    'id_cart'      => (int)$id_cart,
                    'estimate_id'  => pSQL($estimate_id),
                    'price'        => $price,
                    'pickup_date'  => $date_pickup,
                    'drop_date'    => $date_drop,
                    'drop_address' => pSQL(Tools::substr($drop_address, 0, 255)),
                    'error_code'   => pSQL($error_code),
                    'error_msg'    => pSQL($error_msg),
                    'pickup_info'  => pSQL($pickup_info),
                    'estimate_delay' => (int)$result['delay'],
                    'estimate_duration_min' => (int)$result['duration_min'],
                    'estimate_duration_max' => (int)$result['duration_max'],
                    'last_error' => ($error_code) ? time() : null
                )
            );
            $this->current_quotation = null;
        } else {
            // update pickup_info
            if ($pickup_info == null) {
                $pickup_info = $exist['pickup_info'];
            } else {
                Db::getInstance()->update(
                    'lecabflash_carts',
                    array(
                        'pickup_info'          => pSQL($pickup_info),
                    ),
                    'id_cart = '.(int)$id_cart
                );
                $this->current_quotation = null;
                return $this->getQuotation($id_cart);
            }

            LecabFlashLog::info('UPDATE QUOTATION FOR CART '.$id_cart);
            Db::getInstance()->update(
                'lecabflash_carts',
                array(
                    'estimate_id'  => pSQL($estimate_id),
                    'price'        => $price,
                    'pickup_date'  => $date_pickup,
                    'drop_date'    => $date_drop,
                    'drop_address' => pSQL(Tools::substr($drop_address, 0, 255)),
                    'error_code'   => pSQL($error_code),
                    'error_msg'    => pSQL($error_msg),
                    'pickup_info'  => pSQL($pickup_info),
                    'estimate_delay' => (int)$result['delay'],
                    'estimate_duration_min' => (int)$result['duration_min'],
                    'estimate_duration_max' => (int)$result['duration_max'],
                    'last_error' => ($error_code) ? time() : null
                ),
                'id_cart = '.(int)$id_cart
            );
            $this->current_quotation = null;
        }

        // logs/journal

        $this->logEstimateJobContext($id_cart, $context);
        //$this->logEstimateJobRequest($id_cart, $data);
        $this->logEstimateJobResponse($id_cart, $result);

        //

        return $this->getQuotation($id_cart, true);
    }


    private function getQuotation($id_cart, $no_retry = false, $is_estimate = false)
    {
        $cart = new Cart((int)$id_cart);
        $id_address_delivery = (int)$cart->id_address_delivery;

        if (!$id_address_delivery) {
            return false;
        }

        $address      = new Address($id_address_delivery);
        $drop_address = $address->address1.' '.$address->address2.' ,'.$address->postcode.' '.$address->city.','.$address->country;
        $drop_address = pSQL(Tools::substr($drop_address, 0, 255));

        if ($this->current_quotation === null && $this->current_quotation_drop != $drop_address) {
            LecabFlashLog::info('GET QUOTATION FOR CART '.$id_cart);
            $sql  = 'SELECT * FROM '._DB_PREFIX_.'lecabflash_carts WHERE id_cart = '.(int)$id_cart.' AND `drop_address` LIKE "'.$drop_address.'"';
            $quotation = Db::getInstance()->getRow($sql);
            if ($quotation) {
                $this->current_quotation      = $quotation;
                $this->current_quotation_drop = $drop_address;
            } else {
                $this->current_quotation      = false;
                $this->current_quotation_drop = null;
            }
        }

        if ($is_estimate && $this->current_quotation['error_code'] == 422 && time() - $this->current_quotation['last_error'] > 120) {
            $no_retry = false;
            $this->current_quotation = false;
        }

        if (!$no_retry && !$this->current_quotation) {
            return $this->setQuotation($id_cart, null, null, $is_estimate);
        }

        return $this->current_quotation;
    }



    private function confirmJob($id_cart)
    {
        $cart = new Cart((int)$id_cart);
        $shipping_address = new Address((int)$cart->id_address_delivery);
        $drop_firstname = $shipping_address->company ? $shipping_address->company : $shipping_address->firstname;
        $drop_lastname = $shipping_address->company ? $shipping_address->firstname.' '.$shipping_address->lastname : $shipping_address->lastname;
        $drop_phone = $shipping_address->phone_mobile ? $shipping_address->phone_mobile : $shipping_address->phone;
        $drop_info = (string)$shipping_address->other;

        $info = $this->getQuotation($id_cart);
        $estimate_id = $info['estimate_id'];

        $payload = array(
            'estimate_id'=> $estimate_id,
            'contacts'=> array(
                'global'=> array(
                    'email'=> Configuration::get('PS_SHOP_EMAIL'),
                    'lastname'=> Configuration::get('PS_SHOP_NAME'),
                    'phone'=> Configuration::get('LECABFLASH_PICKUP_PHONE'),
                ),
                'drop'=> array(
                    'firstname'=> $drop_firstname,
                    'lastname'=> $drop_lastname,
                    'phone'=> $drop_phone,
                )
            ),
            'notes'=> array(
                'pickup'=> Configuration::get('LECABFLASH_PICKUP_INFO'),
                'drop'=> $drop_info.' '.$info['pickup_info'],
                
            ),
            'payment'=> array(
                'type'=> "INVOICE",
            ),

        );

        $this->logConfirmJobRequest($id_cart, $payload);

        $res = $this->sdk->confirmJob($payload);

        $this->logConfirmJobResponse($id_cart, $res);


        if ($res->code==200) {
            $id = (string)$res->data->id;
            $number = (int)$res->data->number;
            $followurl = (string)$res->data->followurl;
            $delay = (int)$res->data->delay;

            $data_sql = array(
                'confirm_id'        => pSQl($id),
                'confirm_number'    => (int)$number,
                'confirm_url'       => pSQL($followurl),
                'confirm_delay'     => (int)$delay,
            );

            if ($delay > 0) {
                LecabFlashLog::info('RECALCULATE DATES WITH DELAY OF JOB CONFIRM '.$id_cart);
                $estimate_duration = (int)$info['estimate_duration_max'];
                $estimate_duration = (!$estimate_duration) ? (int)$info['estimate_duration_min'] : $estimate_duration;

                $datebase = new DateTime();
                $datebase_pickup = clone $datebase;

                // $total_delay = (int)$delay + LECABFLASH_DATE_DROP_OFFSET; # LECABFLASH_DATE_DROP_OFFSET est défini seulement dans le SDK...
                $total_delay = (int)$delay;

                $datebase_pickup->modify('+'.(int)$total_delay.' minutes');
                $date_pickup = $datebase_pickup->format('c');

                $offset = (int)$total_delay + (int)$estimate_duration;
                $datebase_drop = clone $datebase;
                $datebase_drop->modify('+'.(int)$offset.' minutes');
                $date_drop = $datebase_drop->format('c');

                $data_sql['pickup_date'] = $date_pickup;
                $data_sql['drop_date'] = $date_drop;
            }

            Db::getInstance()->update(
                'lecabflash_carts',
                $data_sql,
                'id_cart = '.(int)$id_cart
            );
            return $res->data;
        }
    }

    /* CARRIER */

    public function getOrderShippingCost($params, $shipping_cost)
    {
        $id_cart = $params->id;
        LecabFlashLog::info('getOrderShippingCost '.$id_cart);
        if ((int)Configuration::get('LECABFLASH_ACTIVE')) {
            $real_price = (bool)Configuration::get('LECABFLASH_PRICE_REAL');
            $quotation = $this->getQuotation($id_cart, false, true);
//            if (!$quotation) {
//                $quotation = $this->setQuotation($id_cart);
//            }

            if ($quotation && $quotation['estimate_id']) {
                return $real_price ? $quotation['price'] : $shipping_cost;
            }
        }
        if ($this->debug) {
            return '99999999';
        }
        return false;
    }

    public function getOrderShippingCostExternal($params)
    {
        return $this->getOrderShippingCost($params, 0);
    }




    /* HOOKS */


    public function hookUpdateCarrier($params)
    {
        $id_carrier_old = (int)$params['id_carrier'];
        $id_carrier_new = (int)$params['carrier']->id;
        if ($this->isLecabFlashCarrier($id_carrier_old)) {
            Configuration::updateValue('LECABFLASH_CARRIER_ID', $id_carrier_new);
        }
    }

    public function hookDisplayBeforeCarrier($params)
    {
        if ((int)Configuration::get('LECABFLASH_ACTIVE')) {
            $id_cart = $params['cart']->id;

//            $this->setQuotation($id_cart);
//            $this->current_quotation=null;

            LecabFlashLog::info('hookDisplayBeforeCarrier '.$id_cart);
            $quotation = $this->getQuotation($id_cart);
        }
    }

    public function removeAccents($str, $charset = 'utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
        $str = preg_replace('#&[^;]+;#', '', $str);
        $str = str_replace(" ", '', $str);

        return $str;
    }

    public function hookDisplayCarrierList($params)
    {
        if ((int)Configuration::get('LECABFLASH_ACTIVE')) {
            $cart_id = $params['cart']->id;
            $address_delivery = new Address($params['cart']->id_address_delivery);
            $ville = $address_delivery->city;
            $pays = $address_delivery->country;
            $address = $address_delivery->address1;
            $code_postale = $address_delivery->postcode;
            $delivery_adress =  $address.', '.$code_postale.' '.$ville.', '.$pays;


            $payload = array(
                'location'=> array(
                    'address'=> $delivery_adress,
                ),
            );
            if ($this->debug) {
                $api = new LecabFlashApi(Configuration::get('LECABFLASH_API_KEY'), true);
            } else {
                $api = new LecabFlashApi(Configuration::get('LECABFLASH_API_KEY'));
            }
            $res = $api->availableServices($payload);

            if (!empty($res->data->location)) {
                $api_address = $res->data->location->address;
                $delivery_adress_str = $this->removeAccents($delivery_adress);
                $api_address_str = $this->removeAccents($api_address);

                if (strcasecmp($api_address_str, $delivery_adress_str) != 0) {
                    $payload = array(
                        'location'=> array(
                            'address'=> $delivery_adress,
                        ),
                        'limit'=> 5
                    );
                    $address_result = $api->searchLocation($payload);
                }
            } else {
                $payload = array(
                    'location'=> array(
                        'address'=> $delivery_adress,
                    ),
                    'limit'=> 5
                );
                $address_result = $api->searchLocation($payload);
            }
            if (isset($address_result)) {
                $addres_new = array();
                $addres_new['cart_id'] = $cart_id;
                foreach ($address_result->data->locations as $add) {
                    $addres_new['address'][] = $add->address;
                }
            }

            $express_available = false;
            $quotation = $this->getQuotation($cart_id);
            LecabFlashLog::info('hookDisplayCarrierList');
            LecabFlashLog::info($quotation);

            if ($quotation && $quotation['estimate_id'] && $quotation['price']) {
                $date_pickup = $quotation['pickup_date'];
                $express_available = $this->checkExpressAvailable($date_pickup);
            }

            $date_default_rdv = new DateTime();
            $date_default_rdv->modify('+30 minutes');


            $url = Tools::getHttpHost(true);

            $this->context->smarty->assign(array(
                'modulePath'=> $url.$this->_path,
                'lecabflash_carrier_id' => (int)Configuration::get('LECABFLASH_CARRIER_ID'),
                'lecabflash_price_real'=> (int)Configuration::get('LECABFLASH_PRICE_REAL'),
                'express_available'=> $express_available,
                'price'=> $quotation['price'],
                'pickup_date'=> $quotation['pickup_date'],
                'drop_date'=> $quotation['drop_date'],
                'date_default_rdv'=> $date_default_rdv->format('Y-m-d H:i'),
                'debug'=> $this->debug ? $quotation : false,
                'error_address'=> isset($addres_new) ? $addres_new : null,
                'lecabflash_pickup_address_info' => isset($quotation['pickup_info'])?$quotation['pickup_info']:'',
                'ajax_token' => $this->_generateToken('front'),
            ));

            $this->context->controller->addJS($this->_path.'views/js/moment-with-locales.js');
            $this->context->controller->addJS($this->_path.'views/js/bootstrap-datetimepicker.js');
            $this->context->controller->addCSS($this->_path.'views/css/bootstrap-datetimepicker.css');

            $this->context->controller->addJS($this->_path.'views/js/bootstrap-modal.min.js');
            $this->context->controller->addCSS($this->_path.'views/css/bootstrap-modal.min.css');

            $this->context->controller->addCSS($this->_path.'views/css/order-carrier.css');
            return $this->display(__FILE__, 'views/templates/hook/order-carrier.tpl');
        }
    }



    public function hookActionCarrierProcess($params)
    {
        if ((int)Configuration::get('LECABFLASH_ACTIVE')) {
            $id_cart = $params['cart']->id;
            $id_carrier = $params['cart']->id_carrier;
            if ($this->isLecabFlashCarrier($id_carrier)) {
                // we check if all ok
                $info = $this->getQuotation($id_cart);
                $lecab_estimate_id = ($info && isset($info['estimate_id'])) ? $info['estimate_id'] : null;
                $lecab_price =  ($info && isset($info['price'])) ? $info['price'] : null;
                if (!$lecab_estimate_id || !$lecab_price) {
                    // there is a pb here -> we redirect
                    if (Configuration::get('PS_ORDER_PROCESS_TYPE')) {
                        Tools::redirect($this->context->link->getPageLink('order-opc', null, null, array()));
                    } else {
                        Tools::redirect($this->context->link->getPageLink('order', null, null, array()));
                    }
                    return false;
                }
            }
        }
    }


    public function hookActionValidateOrder($params)
    {
        if ((int)Configuration::get('LECABFLASH_ACTIVE')) {
            $id_cart = $params['cart']->id;
            $orderStatus = $params['orderStatus'];
            $confirmjob = ($orderStatus->id==Configuration::get('PS_OS_WS_PAYMENT') || $orderStatus->id==Configuration::get('PS_OS_PAYMENT') || $this->debug);
            if ($confirmjob && $this->isLecabFlashCarrier($params['cart']->id_carrier)) {
                $confirmation = $this->confirmJob($id_cart);
                if ($confirmation) {
                    $order = $params['order'];
                    $order->setWsShippingNumber($confirmation->number);
                }
            }
        }
    }

    public function hookDisplayHeader()
    {

        if (($this->context->controller instanceof OrderController && $this->context->controller->step == 2)
            || $this->context->controller instanceof OrderOpcController) {
            $this->context->controller->addJS($this->getPathUri().'views/js/checkout.js');
        }
    }


    private function isLecabFlashCarrier($id_carrier)
    {
        return (int)$id_carrier==Configuration::get('LECABFLASH_CARRIER_ID');
    }


    public function hookDisplayAdminOrder($params)
    {
        $id_cart = $params['cart']->id;
        $id_carrier = $params['cart']->id_carrier;
        if ($this->isLecabFlashCarrier($id_carrier)) {
            $quotation = $this->getQuotation($id_cart);
            $this->context->smarty->assign(array(
                'lecabflash'=> $quotation,
                'pickup_date_translated'=> $this->translateDate($quotation['pickup_date']),
                'drop_date_translated'=> $this->translateDate($quotation['drop_date']),
                'debug'=> $this->debug
            ));
            return $this->display(__FILE__, 'views/templates/admin/order-detail-admin.tpl');
        }
    }

    public function hookDisplayOrderDetail($params)
    {
        $id_cart = $params['order']->id_cart;
        $id_carrier = $params['order']->id_carrier;
        if ($this->isLecabFlashCarrier($id_carrier)) {
            $quotation = $this->getQuotation($id_cart);
            $this->context->smarty->assign(array(
                'lecabflash'=> $quotation,
                'pickup_date_translated'=> $this->translateDate($quotation['pickup_date']),
                'drop_date_translated'=> $this->translateDate($quotation['drop_date']),
                'debug'=> $this->debug
            ));
            return $this->display(__FILE__, 'views/templates/hook/order-detail.tpl');
        }
    }

    /* BO CONFIGURATION */

    public function hookActionAdminControllerSetMedia($params)
    {
        if (get_class($this->context->controller) == 'AdminModulesController' && Tools::getValue('configure')==$this->name) {
            $this->context->controller->addJS($this->_path.'views/js/admin.js');
            $this->context->controller->addCSS($this->_path.'views/css/admin.css', 'all');
        }
    }
    public function getContent()
    {
        $output = "";
        $active = Configuration::get('LECABFLASH_ACTIVE');
        // tableau de conf d'ouverture
        $config = $this->getLecabHoursConfig();
        $weekdays = array(1=>'monday',2=>'tuesday',3=>'wednesday',4=>'thursday',5=>'friday',6=>'saturday',7=>'sunday');
        $hours = array();
        foreach ($weekdays as $weekday => $name) {
            if (isset($config[$weekday])) {
                $info = $config[$weekday];
            } else {
                $info = array(0,null,null,null,null);
            }
            $active = $info[0];
            $am_open_info   = $info[1] ? explode(':', $info[1]) : array('','');
            $am_close_info  = $info[2] ? explode(':', $info[2]) : array('','');
            $pm_open_info   = $info[3] ? explode(':', $info[3]) : array('','');
            $pm_close_info  = $info[4] ? explode(':', $info[4]) : array('','');

            $hours[$name] = $active;
            $hours[$name.'_am_open_hour']   = $am_open_info[0];
            $hours[$name.'_am_open_min']    = $am_open_info[1];
            $hours[$name.'_am_close_hour']  = $am_close_info[0];
            $hours[$name.'_am_close_min']   = $am_close_info[1];
            $hours[$name.'_pm_open_hour']   = $pm_open_info[0];
            $hours[$name.'_pm_open_min']    = $pm_open_info[1];
            $hours[$name.'_pm_close_hour']  = $pm_close_info[0];
            $hours[$name.'_pm_close_min']   = $pm_close_info[1];
        }
        $this->context->smarty->assign(array(
            'modulePath'        => $this->_path,
            'active'            => Configuration::get('LECABFLASH_ACTIVE'), // api_key ok + pickup address ok
            'hours'             => $hours,
            'moduleLink'        => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name,
            'ajax_token'        => $this->_generateToken('back'),
            'employee_id'       => Context::getContext()->employee->id,
            'token_admin_perf'  => Tools::getAdminToken('AdminPerformance'.(int)(Tab::getIdFromClassName('AdminPerformance')).(int)(Context::getContext()->employee->id)),
        ));
        $this->context->smarty->assign(array(
            'html_tab_key'     => $this->displayTabKey(),
            'html_tab_addr' => $this->displayTabAddr(),
            'html_tab_hours' => $this->displayTabHours(),
            'html_tab_prices' => $this->displayTabPrice(),
            'html_tab_journal'  => $this->displayTabJournal(),
            'html_tab_debug'    => $this->displayTabDebug(),
        ));
        $output .= $this->display(__FILE__, 'views/templates/admin/tabs.tpl');
        //$output .= $this->displayForm1();
        //$output .= $this->displayForm2();
        return $output;
    }



    public function displayTabKey()
    {
        // Init Fields form array
        $fields_form            = array();
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('API Key'),
            ),
            'description' => $this->l('Create an account through').' <a href="https://www.lecab.fr/flashforprestashop.html" target="_blank">'.$this->l('this link').'</a>'.$this->l(' to generate an API Key. Enter it to access the next step.'),
            'input'  => array(
                array(
                    'type'     => 'text',
                    'label'    => $this->l('LeCabFlash API key'),
                    'name'     => 'lecabflash_api_key',
                    'size'     => 20,
                    'class'    => 'input-md',
                    'required' => true,
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'lecabflash_token',
                    'required' => true,
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'employee_id',
                    'required' => true,
                ),
            ),
            'submit' => array(
                'name'  => 'general_settings',
                'title' => $this->l('Save'),
                'class' => 'button pull-right',
                'id'    => 'btn_key',
            ),
        );


        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module          = $this;
        $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language    = $this->context->language->id;
        $helper->allow_employee_form_lang = $this->context->language->id;

        // Title and toolbar
        $helper->title          = $this->displayName;
        $helper->show_toolbar   = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action  = 'submit'.$this->name;


        // Load currents values
        if (Configuration::get('LECABFLASH_API_KEY') == 'cEst4ZV3IHExh99XrF9vsOkjJtf5OONS' && !$this->debug) {
            $api_key = '';
        } else {
            $api_key = Configuration::get('LECABFLASH_API_KEY');
        }
 
        $helper->fields_value['lecabflash_api_key'] = $api_key;
        $helper->fields_value['lecabflash_token'] = $this->_generateToken('back');
        $helper->fields_value['employee_id'] = Context::getContext()->employee->id;
 
        return $helper->generateForm($fields_form);
    }

    public function displayTabAddr()
    {
        // Init Fields form array
        $fields_form            = array();
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Depot address'),
            ),
            'description' => $this->l('Enter your depot address where our courier will come to pick up packages. If your depot is out of Paris and inner suburbs, you can‘t use our service.'),
            'input'  => array(
                array(
                    'type'     => 'text',
                    'label'    => $this->l('Address'),
                    'desc'     => $this->l('ex : 5 rue des Trois Frères, 75018 Paris, France'),
                    'name'     => 'lecabflash_pickup_address',
                    'size'     => 10,
                    'required' => true,
                ),
                array(
                    'type'     => 'textarea',
                    'label'    => $this->l('Phone number'),
                    'name'     => 'lecabflash_pickup_telephone',
                    'required' => true,
                ),
                array(
                    'type'     => 'textarea',
                    'label'    => $this->l('Complementary informations'),
                    'desc'     => $this->l('Access, codes ...'),
                    'name'     => 'lecabflash_pickup_address_info',
                    'class'    => 'textarea-y3',
                    'required' => false,
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'lecabflash_token',
                    'required' => true,
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'employee_id',
                    'required' => true,
                ),
            ),
            'submit' => array(
                'name'  => 'general_settings',
                'title' => $this->l('Save'),
                'class' => 'button pull-right',
                'id'    => 'btn_key',
            ),
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module          = $this;
        $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language    = $this->context->language->id;
        $helper->allow_employee_form_lang = $this->context->language->id;

        // Title and toolbar
        $helper->title          = $this->displayName;
        $helper->show_toolbar   = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action  = 'submit'.$this->name;

        // Load currents values
        if (!Configuration::get('LECABFLASH_PICKUP_ADDRESS')) {
            $shop_addr1 = Configuration::get('PS_SHOP_ADDR1');
            $shop_addr2 = Configuration::get('PS_SHOP_ADDR2');
            $shop_zipcode = Configuration::get('PS_SHOP_CODE');
            $shop_city = Configuration::get('PS_SHOP_CITY');
            $shop_country = Configuration::get('PS_SHOP_COUNTRY');
            $shop_phone = Configuration::get('PS_SHOP_PHONE');
            $pickup_address = $shop_addr1.' '.$shop_addr2.' ,'.$shop_zipcode.' '.$shop_city.','.$shop_country;
        } else {
            $pickup_address = Configuration::get('LECABFLASH_PICKUP_ADDRESS');
            $shop_phone = Configuration::get('LECABFLASH_PICKUP_PHONE');
        }

        $pickup_address_info = Configuration::get('LECABFLASH_PICKUP_INFO');

        $helper->fields_value['lecabflash_pickup_address'] = $pickup_address;
        $helper->fields_value['lecabflash_pickup_address_info'] = $pickup_address_info;
        $helper->fields_value['lecabflash_pickup_telephone'] = $shop_phone;
        $helper->fields_value['lecabflash_token'] = $this->_generateToken('back');
        $helper->fields_value['employee_id'] = Context::getContext()->employee->id;

        return $helper->generateForm($fields_form);
    }

    public function displayTabHours()
    {
        // assign data

        // display tpl
        return $this->display(__FILE__, 'views/templates/admin/tab_hours.tpl');
    }


    public function displayTabPrice()
    {

        // Init Fields form array
        $default_lang           = $this->context->language->id;
        $fields_form            = array();
        // $fields_form[0]['form'] = array(
        //     'legend' => array(
        //         'title' => $this->l('Delivery Address'),
        //    ),
        //     'input'  => array(
        //         array(
        //             'type'     => 'switch',
        //             'label'    => $this->l('Show the TOS in front'),
        //             'name'     => 'lecabflash_show_cgu',
        //             'required' => true,
        //             'is_bool' => true,
        //             'values' => array(
        //                 array(
        //                     'id' => 'active_on',
        //                     'value' => true,
        //                     'label' => $this->l('Enabled')
        //                 ),
        //                 array(
        //                     'id' => 'active_off',
        //                     'value' => false,
        //                     'label' => $this->l('Disabled')
        //                 )
        //             ),
        //         ),
        //         array(
        //             'type'     => 'switch',
        //             'label'    => $this->l('Does your society use VAT?'),
        //             'name'     => 'lecabflash_use_tva',
        //             'required' => true,
        //             'is_bool' => true,
        //             'values' => array(
        //                 array(
        //                     'id' => 'active_on',
        //                     'value' => true,
        //                     'label' => $this->l('Enabled')
        //                 ),
        //                 array(
        //                     'id' => 'active_off',
        //                     'value' => false,
        //                     'label' => $this->l('Disabled')
        //                 )
        //             ),
        //         ),
        //    ),
        //);

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Fare price'),
            ),
            'description' => $this->l('By default, the prices are the one sent by LeCabFlash You can add an extra fix price or a fix pourcentage which will change the price displayed and paid by consumers. You‘ll be charged the original price.'),
            'input'  => array(
                array(
                    'type'     => 'switch',
                    'label'    => $this->l('Use LeCabFlash prices'),
                    'name'     => 'lecabflash_price_real',
                    'desc'     => $this->l('To use prices from the PrestaShop carrier, check no.'),
                    'required' => true,
                    'is_bool'   => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Real prices')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Carrier prices')
                        )
                    ),
                ),
                array(
                    'type'     => 'text',
                    'class'    => 'js_floats_only',
                    'label'    => $this->l('Additional fixed amount (including VAT)'),
                    'desc'     => $this->l('Example : For a fare charged 8€, you can add a 2€ fee. The total fare amount will be 10€.'),
                    'name'     => 'lecabflash_price_fixed',
                    'prefix'   => '€',
                    'size'     => 4
                ),
                array(
                    'type'        => 'text',
                    'class'       => 'js_floats_only',
                    'label'       => $this->l('Percentage increase (including VAT)'),
                    'desc'        => $this->l('Example : For a fare charged 8€, you can add a 20% fee. The total fare amount will be 8 + (8x20%) = 9,60€.'),
                    'name'        => 'lecabflash_price_rate',
                    'prefix'      => '%',
                    'size'        => 4
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'lecabflash_token',
                    'required' => true,
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'employee_id',
                    'required' => true,
                ),
            ),
            'submit' => array(
                'name'  => 'advanced_settings',
                'title' => $this->l('Save'),
                'class' => 'button pull-right',
                'id'    => 'btn_key',
            ),
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module          = $this;
        $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language    = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title          = $this->displayName;
        $helper->show_toolbar   = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action  = 'submit'.$this->name;

        // Load current value
        $helper->fields_value['lecabflash_show_cgu'] = Configuration::get('LECABFLASH_SHOW_CGU');
        $helper->fields_value['lecabflash_use_tva'] = Configuration::get('LECABFLASH_USE_TVA');
        $helper->fields_value['lecabflash_price_real'] = Configuration::get('LECABFLASH_PRICE_REAL');
        $helper->fields_value['lecabflash_price_fixed'] = Configuration::get('LECABFLASH_PRICE_FIXED');
        $helper->fields_value['lecabflash_price_rate'] = Configuration::get('LECABFLASH_PRICE_RATE');
        $helper->fields_value['lecabflash_token'] = $this->_generateToken('back');
        $helper->fields_value['employee_id'] = Context::getContext()->employee->id;

        return $helper->generateForm($fields_form);
    }

    public function displayTabJournal()
    {
        /*

        //$tests = new LecabFlashTests();
        //return $tests->run(@$_GET['test']);

        $fields_list = array(
            'id_cart' => array(
                'title' => $this->l('Id Cart'),
                'width' => 140,
                'type' => 'text',
            ),
            'id_carrier' => array(
                'title' => $this->l('Name'),
                'width' => 140,
                'type' => 'text',
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = True;
        $helper->identifier = 'id_cart';
        $helper->show_toolbar = false;
        $helper->title = 'Cart';
        $helper->table = 'test';
       
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        //$helper->currentIndex = $_SERVER['REQUEST_URI'];
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $html = $helper->generateList($rows, $fields_list);
        return $html;

        return $this->display(__FILE__, 'views/templates/admin/tab_journal.tpl');
        */
    }

    public function displayTabDebug()
    {
        $sql = 'SELECT *,'._DB_PREFIX_.'cart.id_cart FROM '._DB_PREFIX_.'cart
                LEFT JOIN '._DB_PREFIX_.'lecabflash_carts
                ON '._DB_PREFIX_.'cart.id_cart = '._DB_PREFIX_.'lecabflash_carts.id_cart
                ORDER BY '._DB_PREFIX_.'cart.id_cart DESC
                LIMIT 10
        ';
        $rows = Db::getInstance()->ExecuteS($sql);

        $tests = new LecabFlashTests();


        $this->context->smarty->assign(array(
            'tests' => $tests->tests_plan,
            'carts' => $rows,
        ));

        return $this->display(__FILE__, 'views/templates/admin/tab_debug.tpl');
    }

    public function changeCartAddress($address, $id_cart)
    {
        $new_address = explode(",", $address);
        $cart = new Cart($id_cart);
        $address_delivery_old = new Address($cart->id_address_delivery);
        $address_delivery = new Address();
        $address_delivery->firstname = $address_delivery_old->firstname;
        $address_delivery->lastname = $address_delivery_old->lastname;
        $address_delivery->phone = $address_delivery_old->phone;
        $address_delivery->phone_mobile = $address_delivery_old->phone_mobile;
        $country_id = Country::getIdByName($cart->id_lang, $new_address[2]);
        $address_delivery->id_country =  $country_id?$country_id:$address_delivery_old->id_country;
        $address_delivery->id_customer = $address_delivery_old->id_customer;
        $address_delivery->country = ltrim($new_address[2]);
        $address_delivery->address1 = $new_address[0];
        $new_country = explode(" ", ltrim($new_address[1]));
        $address_delivery->city = $new_country[1];
        $address_delivery->postcode = $new_country[0];
        $address_delivery->alias = 'new_LeCabFlash';
        $address_delivery->save();

        //$cart->updateAddressId($cart->id_address_delivery,$address_delivery->id);


        $sql = 'UPDATE `'._DB_PREFIX_.'cart_product`
        SET `id_address_delivery` = '.$address_delivery->id.'
        WHERE  `id_cart` = '.$cart->id.'
            AND `id_address_delivery` = '.$cart->id_address_delivery;
        Db::getInstance()->execute($sql);

        $sql = 'UPDATE `'._DB_PREFIX_.'customization`
            SET `id_address_delivery` = '.$address_delivery->id.'
            WHERE  `id_cart` = '.$cart->id.'
                AND `id_address_delivery` = '.$cart->id_address_delivery;
        Db::getInstance()->execute($sql);

        $cart->id_address_delivery = $address_delivery->id;
        $cart->save();


        $this->getQuotation($id_cart);
        
        return $cart;
    }
    
    private function _generateToken($place)
    {
        if ($place == 'front') {
            return md5(Configuration::get('LECABFLASH_TOKEN_HASH') . Context::getContext()->cart->id . date('Ymd'));
        } else {
            return sha1(Configuration::get('LECABFLASH_TOKEN_HASH') . Context::getContext()->employee->id . date('Ymd') . __PS_BASE_URI__);
        }
    }

    public function addError($error)
    {
        $this->_errors[] = $error;
    }

    public function setErrors($errors)
    {
        if (is_array($errors)) {
            $this->_errors = $errors;
        } else {
            $this->_errors = array($errors);
        }
    }
    ############################################################################################################
    # Logger // Debug
    ############################################################################################################
    /**
     * Fonction de log
     *
     * Enregistre dans /modules/lecabflash/logs/debug.log
     *
     * @param     $object
     * @param int $error_level
     */
    public static function debug($object, $error_level = 0, $advanced_log = false)
    {
        $error_type  = array(
            0 => "[ALL]",
            1 => "[DEBUG]",
            2 => "[INFO]",
            3 => "[WARN]",
            4 => "[ERROR]",
            5 => "[FATAL]",
        );
        $module_name = "lecabflash";
        $backtrace   = debug_backtrace();
        $date        = date("<Y-m-d(H:i:s)>");
        $file        = $backtrace[0]['file'].":".$backtrace[0]['line'];
        if ($advanced_log) {
            $file .= ' {';
            if (count($backtrace) > 3) {
                $file .= $backtrace[3]['class'].'::'.$backtrace[3]['function'].' -> '.$backtrace[2]['class'].'::'.$backtrace[2]['function'].' -> '.$backtrace[1]['class'].'::'.$backtrace[1]['function'].' -> '.$backtrace[0]['class'].'::'.$backtrace[0]['function'];
            } elseif (count($backtrace) > 2) {
                $file .= $backtrace[2]['function'].' -> '.$backtrace[1]['class'].'::'.$backtrace[1]['function'].' -> '.$backtrace[0]['class'].'::'.$backtrace[0]['function'];
            } elseif (count($backtrace) > 1) {
                $file .= $backtrace[1]['class'].'::'.$backtrace[1]['function'].' -> '.$backtrace[0]['class'].'::'.$backtrace[0]['function'];
            } else {
                $file .= $backtrace[0]['file'].":".$backtrace[0]['line'];
            }
            $file .= "}";
        }
        $stderr = fopen(_PS_MODULE_DIR_.'/'.$module_name.'/logs/debug.log', 'a');
        fwrite($stderr, $error_type[$error_level]." ".$date." ".$file."\n".print_r($object, true)."\n\n");
        fclose($stderr);
    }
}
