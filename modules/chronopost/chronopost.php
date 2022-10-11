<?php
/**
 * MODULE PRESTASHOP OFFICIEL CHRONOPOST
 *
 * LICENSE : All rights reserved - COPY && REDISTRIBUTION FORBIDDEN WITHOUT PRIOR CONSENT FROM OXILEO
 * LICENCE : Tous droits réservés, le droit d'auteur s'applique - COPIE ET REDISTRIBUTION INTERDITES
 * SANS ACCORD EXPRES D'OXILEO
 *
 * @author    Oxileo SAS <contact@oxileo.eu>
 * @copyright 2001-2018 Oxileo SAS
 * @license   Proprietary - no redistribution without authorization
 */

define('MIN_VERSION', '1.5');
define('MAX_VERSION', '1.8');

class Chronopost extends CarrierModule
{
    private $postErrors = array();
    public $id_carrier;

    // Carriers' configuration
    public static $id_tax_rules_group = 1;

    public static $tracking_url = 'http://www.chronopost.fr/tracking-no-cms/suivi-page?listeNumerosLT=@&langue=fr';

    public static $carriers_definitions = array(
        'CHRONO10' => array(
            'product_code' => '02',
            'name' => 'Chronopost - Livraison express à domicile avant 10h',
            'product_code_bal' => '02',
            'delay' => array(
                'fr' =>'Colis livré le lendemain matin avant 10h à votre domicile. La veille de la livraison,
                 vous êtes averti par e-mail et SMS.',
                'en'=>'Parcels delivered the next day before 10am at your home. The day before delivery,
                 You\'ll be notified by e-mail and SMS.'
            ),
        ),
        'CHRONO13' => array(
            'product_code' => '01',
            'name' => 'Chronopost - Livraison express à domicile avant 13h',
            'product_code_bal' => '01',
            'delay' => array(
                'fr' =>'Colis livré le lendemain matin avant 13h à votre domicile. La veille de la livraison,
                 vous êtes averti par e-mail et SMS.',
                'en'=>'Parcels delivered the next day before 13pm at your home. The day before delivery,
                 You\'ll be notified by e-mail and SMS.'
            ),
        ),
        'CHRONO18' => array(
            'product_code' => '16',
            'name' => 'Chronopost - Livraison express à domicile avant 18h',
            'product_code_bal' => '16',
            'delay' => array(
                'fr' =>'Colis livré le lendemain matin avant 18h à votre domicile. La veille de la livraison,
                 vous êtes averti par e-mail et SMS.',
                'en'=>'Parcels delivered the next day before 18pm at your home. The day before delivery,
                 You\'ll be notified by e-mail and SMS.'
            ),
        ),
        'CHRONORELAIS' => array(
            'product_code' => '86',
            'name' => 'Chronopost - Livraison express en point relais',
            'product_code_bal' => '86',
            'delay' => array(
                'fr' =>'Colis livré le lendemain avant 13 h dans le relais Pickup de votre choix.
                 Vous serez averti par e-mail et SMS.',
                'en'=>'Parcels delivered the next day before 1pm in the Pickup relay of your choice.
                 You\'ll be notified by e-mail and SMS.'
            ),
        ),
        'CHRONOCLASSIC' => array(
            'product_code' => '44',
            'name' => 'Chronopost - Livraison à domicile',
            'product_code_bal' => '44',
            'delay' => array(
                'fr' =>'Colis livré en 1 à 3 jours vers l\'Europe.', 'Parcels delivered to Europe in 1 to 3 days'
            ),
        ),
        'CHRONOEXPRESS' => array(
            'product_code' => '17',
            'name' => 'Chronopost - Livraison express à domicile',
            'product_code_bal' => '17',
            'delay' => array(
                'fr' =>'Colis livré en 1 à 3 jours vers l\'Europe, en 48h vers les DOM et en 2 à 5 jours vers le
                 reste du monde.',
                'en'=>'Parcels delivered to Europe in 1 to 3 days, 48 hours to the DOM and 2 to 5 days to the
                 rest of the world.'),
        ),
        'RELAISEUROPE' => array(
            'product_code' => '49',
            'name' => 'Chronopost - Livraison Europe en point relais',
            'product_code_bal' => '49',
            'delay' => array(
                'fr' =>'Colis livré en 1 à 3 jours vers l\'Europe dans le point relais de votre choix.',
                'en'=>'Parcels delivered to Europe in 1 to 3 days in the Pickup point of your choice.'
            ),
        ),
        'RELAISDOM' => array(
            'product_code' => '4P',
            'name' => 'Chronopost - Livraison DOM en point relais',
            'product_code_bal' => '4P',
            'delay' => array(
                'fr' =>'Colis livré en 1 à 3 jours vers les DOM dans le point relais de votre choix.',
                'en'=>'Parcels delivered to DOM in 1 to 3 days in the Pickup point of your choice.'
            ),
        ),
        'SAMEDAY' => array(
            'product_code' => '4I',
            'name' => 'Chronopost - Livraison Sameday',
            'product_code_bal' => '4I',
            'delay' => array('fr' => 'Livraison le jour même.')
        ),
        'CHRONORDV' => array(
            'product_code' => '2O',
            'name' => 'Chronopost - Livraison express sur rendez-vous',
            'product_code_bal' => '2O',
            'delay' => array('fr' => 'Livraison sur rendez-vous.')
        ),
        /*'DIMANCHEBAL' => array(
            'product_code' => '5A',
            'name' => 'Chronopost - Dimanche BAL',
            'product_code_bal' => '5A',
            'delay' => array('fr' => 'Livraison le dimanche (BAL).')
        ),*/
    );

    public static $RETURN_ADDRESS_RETURN = 0;
    public static $RETURN_ADDRESS_INVOICE = 1;
    public static $RETURN_ADDRESS_SHIPPING = 2;

    public function __construct()
    {
        $this->name = 'chronopost';
        $this->tab = 'shipping_logistics';

        $this->version = '4.6.0';
        $this->bootstrap = true;
        $this->author = $this->l('Chronopost Official');
        $this->module_key = 'ed72dc5234f171ec266a664a8088d8ef';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.8');

        parent::__construct();

        $this->displayName = $this->l('Chronopost');
        $this->description = $this->l('Manage Chronopost and Chronopost Pickup relay');
        $this->confirmUninstall = $this->l('Remember, once this module is uninstalled , you won\'t be able to
        edit Chronopost waybills or propose Pickup delivery point to your customers. Are you sure you wish to proceed?');

        // Check is SOAP is available
        if (!extension_loaded('soap')) {
            $this->warning .= $this->l('The SOAP extension is not available or configured on the server ; The
            module will not work without this extension ! Please contact your host to activate it in your PHP
            installation.');
        }
        if (!self::checkPSVersion()) {
            $this->warning .= $this->l('This module is incompatible with your Prestashop installation. You can visit
the <a href =
"http://www.chronopost.fr/transport-express/livraison-colis/accueil/produits-tarifs/expertise-sectorielle/e-commerce/plateformes">Chronopost.fr
</a>website to download a comptible version.');
        }

        // Check is module is properly configured
        if (Tools::strlen(Configuration::get('CHRONOPOST_GENERAL_ACCOUNT')) < 8) {
            $this->warning .= $this->l('You have to configure the module with your Chronopost contract number. If you
 don\'t have one, please sign in to the following address <a href =
 "http://www.chronopost.fr/transport-express/livraison-colis/accueil/produits-tarifs/expertise-sectorielle/pid/8400"
 target = "_blank">www.mychrono.chronopost.fr</a>');
        }
    }

    public function preInstall()
    {
        if (!self::checkPSVersion()) {
            $this->context->controller->errors[] = 'This module is incompatible with your Prestashop installation. You
can visit the <a href =
"http://www.chronopost.fr/transport-express/livraison-colis/accueil/produits-tarifs/expertise-sectorielle/e-commerce/plateformes">Chronopost.fr </a>
website to download a comptible version.';
            return false;
        }

        // Check for SOAP
        if (!extension_loaded('soap')) {
            $this->context->controller->errors[] = $this->l('The SOAP extension is not available or configured on
             the server ; The module will not work without this extension ! Please contact your host to activate it
              in your PHP installation.');
            return false;
        }

        if (!parent::install()) {
            return false;
        }

        // Admin tab
        if (!$this->adminInstall()) {
            return false;
        }

        // register hooks
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            if (!$this->registerHook('extraCarrier') || // For point relais GMap
                !$this->registerHook('updateCarrier') || // For update of carrier IDs
                !$this->registerHook('newOrder') || // Processing of selected BT, NOTE : processCarrier apparently not what we want
                !$this->registerHook('header') || //
                !$this->registerHook('backOfficeHeader') || //
                !$this->registerHook('adminOrder')) {
                return false;
            }
        } else {
            if (!$this->registerHook('displayAfterCarrier') || // For point relais GMap
                !$this->registerHook('actionCarrierUpdate') || // For update of carrier IDs
                !$this->registerHook('newOrder') || // Processing of selected BT, NOTE : processCarrier apparently not what we want
                !$this->registerHook('displayHeader') || //
                !$this->registerHook('backOfficeHeader') || //
                !$this->registerHook('adminOrder')) {
                return false;
            }
        }
        return true;
    }

    /** INSTALLATION-RELATED FUNCTIONS **/
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // new in 3.8.0
        Configuration::updateValue('CHRONOPOST_SATURDAY_DAY_START', -1);


        DB::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'chrono_calculateproducts_cache2` (
             `id` int(11) NOT null AUTO_INCREMENT,
             `postcode` varchar(10) NOT null,
             `country` varchar(2) NOT null,
             `chrono10` tinyint(1) NOT null,
             `chrono18` tinyint(1) NOT null,
             `chronoclassic` tinyint(1) NOT null,
             `relaiseurope` tinyint(1) NOT null,
             `relaisdom` tinyint(1) NOT null,
             `rdv` tinyint(1) NOT null,
             `sameday` tinyint(1) NOT null,
             `dimanchebal` INT NOT NULL,
             `last_updated` int(11) NOT null,
             PRIMARY KEY (`id`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1 ;');

        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'chrono_cart_relais` (
                `id_cart` int(10) NOT null,
                `id_pr` varchar(10) NOT null,
                PRIMARY KEY (`id_cart`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8;');


        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'chrono_cart_creneau` (
                `id_cart` int(10) NOT NULL,
                `rank` int(10) NOT NULL,
                `delivery_date` varchar(29) NOT NULL,
                `delivery_date_end` VARCHAR(29) NULL,
                `slot_code` varchar(10) NOT NULL,
                `tariff_level` int(10) NOT NULL,
                `transaction_id` varchar(60) NOT NULL,
                `fee` decimal(20,6) NOT NULL,
                `product_code` VARCHAR(2) NULL DEFAULT NULL,
                `service_code` VARCHAR(6) NULL DEFAULT NULL,
                `as_code` VARCHAR(6) NULL DEFAULT NULL,
                PRIMARY KEY (`id_cart`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');


        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'chrono_lt_history` (
                `id_order` int(10) NOT null,
                `lt` varchar(20) NOT null,
                `product` varchar(2) NOT null,
                `zipcode` varchar(10) NOT null,
                `country` varchar(2) NOT null,
                `insurance` int(10) NOT null,
                `city` varchar(32) NOT null,
                PRIMARY KEY (`id_order`, `lt`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8;');


        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'chrono_quickcost_cache` (
                `id` int(11) NOT null AUTO_INCREMENT,
                `product_code` varchar(2) NOT null,
                `arrcode` varchar(10) NOT null,
                `weight` decimal(10,2) NOT null,
                `price` decimal(10,2) NOT null,
                `last_updated` int(11) NOT null,
                PRIMARY KEY (`id`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1 ;');


        // pre install
        if (!$this->preInstall()) {
            return false;
        }

        // init config
        if (!Configuration::updateValue('CHRONOPOST_SECRET', sha1(microtime(true).mt_rand(10000, 90000)))
            || !Configuration::updateValue('CHRONOPOST_CORSICA_SUPPLEMENT', '19.60')
            || !Configuration::updateValue('CHRONOPOST_RDV_DELAY', '1')) {
            return false;
        }

        return true;
    }

    private function adminInstall()
    {
        $tab_export = new Tab();
        $tab_export->class_name = 'AdminExportChronopost';
        $tab_export->id_parent = Tab::getIdFromClassName('AdminParentShipping');
        $tab_export->module = 'chronopost';
        foreach (Language::getLanguages(false) as $language) {
            $tab_export->name[$language['id_lang']] = $this->l('Chronopost Export');
        }

        $tab_import = new Tab();
        $tab_import->class_name = 'AdminImportChronopost';
        $tab_import->id_parent = Tab::getIdFromClassName('AdminParentShipping');
        $tab_import->module = 'chronopost';
        foreach (Language::getLanguages(false) as $language) {
            $tab_import->name[$language['id_lang']] = $this->l('Chronopost Import');
        }

        $tab_bordereau = new Tab();
        $tab_bordereau->class_name = 'AdminBordereauChronopost';
        $tab_bordereau->id_parent = Tab::getIdFromClassName('AdminParentShipping');
        $tab_bordereau->module = 'chronopost';
        foreach (Language::getLanguages(false) as $language) {
            $tab_bordereau->name[$language['id_lang']] = $this->l('Daily docket');
        }

        return $tab_import->add() && $tab_export->add() && $tab_bordereau->add();
    }


    public static function checkPSVersion()
    {
        return ((version_compare(_PS_VERSION_, MIN_VERSION) >= 0) &&
            (version_compare(_PS_VERSION_, MAX_VERSION) < 0));
    }


    public static function createCarrier($code)
    {
        Shop::setContext(Shop::CONTEXT_ALL);

        require_once(dirname(__FILE__).'/libraries/range/RangePrice.php');
        require_once(dirname(__FILE__).'/libraries/range/RangeWeight.php');

        if (!array_key_exists($code, self::$carriers_definitions)) {
            echo "Code incorrect.";
            return false;
        }
        $carrier = new Carrier();
        $carrier->name = self::$carriers_definitions[$code]['name'];
        $carrier->id_tax_rules_group = self::$id_tax_rules_group;
        $carrier->url = self::$tracking_url;
        $carrier->active = true;
        $carrier->deleted = 0;
        $carrier->delay = self::$carriers_definitions[$code]['delay'];
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->is_module = false;
        $carrier->shipping_external = true;
        $carrier->external_module_name = 'chronopost';
        $carrier->need_range = true;

        foreach (Language::getLanguages(true) as $language) {
            if (array_key_exists($language['iso_code'], self::$carriers_definitions[$code]['delay'])) {
                $carrier->delay[$language['id_lang']] =self::$carriers_definitions[$code]['delay'][$language['iso_code']];
            } else {
                $carrier->delay[$language['id_lang']] = self::$carriers_definitions[$code]['delay']['fr'];
            }
        }
        if ($carrier->add()) { // ASSIGN GROUPS
            $groups = Group::getgroups(true);
            foreach ($groups as $group) {
                Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'carrier_group
                    VALUE (\''.(int)($carrier->id).'\',\''.(int)($group['id_group']).'\')');
            }

            // ASSIGN ZONES
            $zones = Zone::getZones();
            foreach ($zones as $zone) {
                $carrier->addZone($zone['id_zone']);
            }

            // RANGE PRICE
            $rp = new RangePrice();
            $rp->id_carrier = $carrier->id;
            $rp->delimiter1 = 0;
            $rp->delimiter2 = 100000;
            $rp->add();

            $fp = null;

            if (file_exists(dirname(__FILE__).'/csv/'.Tools::strtolower($code).'.csv')) {
                $fp = fopen(dirname(__FILE__).'/csv/'.Tools::strtolower($code).'.csv', 'r');
            }

            // fails silently if no CSV
            if ($fp) {
                // insert prices per weight range
                while ($line = fgetcsv($fp)) {
                    $rangeWeight = new RangeWeight();
                    $rangeWeight->id_carrier = $carrier->id;
                    $rangeWeight->delimiter1 = $line[0];
                    $rangeWeight->delimiter2 = $line[1];
                    $rangeWeight->price_to_affect = $line[2];
                    $rangeWeight->add();
                }
            }

            //copy logo


            if (!@copy(dirname(__FILE__).'/views/img/carriers/'.Tools::strtolower($code).'.jpg',
                _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg')) {
                if (!@copy(dirname(__FILE__).'/views/img/carriers/chronopost2.jpg',
                    _PS_SHIP_IMG_DIR_.'/'.(int)$carrier->id.'.jpg')) {
                    return false;
                }
            }
        } else {
            echo "No creation.";
            return false;
        }

        return Configuration::updateValue('CHRONOPOST_'.Tools::strtoupper($code).'_ID', (int)$carrier->id);
    }

    public function uninstall()
    {
        // Remove admin tabs
        $tab = new Tab(Tab::getIdFromClassName('AdminExportChronopost'));
        if (!$tab->delete()) {
            return false;
        }

        $tab = new Tab(Tab::getIdFromClassName('AdminImportChronopost'));
        if (!$tab->delete()) {
            return false;
        }

        $tab = new Tab(Tab::getIdFromClassName('AdminBordereauChronopost'));
        if (!$tab->delete()) {
            return false;
        }

        // Cleanup
        Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'chrono_calculateproducts_cache2`');

        return parent::uninstall();
    }


    public static function gettingReadyForSaturday($carrier)
    {
        if (Configuration::get('CHRONOPOST_SATURDAY_ACTIVE') != 'yes') {
            return false;
        }

        if ($carrier->id_reference == Configuration::get('CHRONOPOST_DIMANCHEBAL_ID')) {
            return false;
        }

        $start = new DateTime('last sun');
        // COMPAT < 5.36 : no chaining (returns null)
        $start->modify('+'.Configuration::get('CHRONOPOST_SATURDAY_DAY_START').' days');
        $start->modify('+'.Configuration::get('CHRONOPOST_SATURDAY_HOUR_START').' hours');
        $start->modify('+'.Configuration::get('CHRONOPOST_SATURDAY_MINUTE_START').' minutes');
        $end = new DateTime('last sun');
        $end->modify('+'.Configuration::get('CHRONOPOST_SATURDAY_DAY_END').' days');
        $end->modify('+'.Configuration::get('CHRONOPOST_SATURDAY_HOUR_END').' hours');
        $end->modify('+'.Configuration::get('CHRONOPOST_SATURDAY_MINUTE_END').' minutes');

        if ($end < $start) {
            $end->modify('+1 week');
        }
        $now = new DateTime();

        if ($start <= $now && $now <= $end) {
            return true;
        }

        return false;
    }

    public static function isSaturdayOptionApplicable()
    {
        if (Configuration::get('CHRONOPOST_SATURDAY_CHECKED') != 'yes') {
            return false;
        } else {
            return self::gettingReadyForSaturday();
        }
    }

    public static function trackingStatus($id_order, $shipping_number)
    {
        // MAIL::SEND is bugged in 1.5 !
        // http://forge.prestashop.com/browse/PNM-754 (Unresolved as of 2013-04-15)
        // Context fix (it's that easy)
        Context::getContext()->link = new Link();
        // Fix context by adding employee
        $cookie = new Cookie('psAdmin');
        Context::getContext()->employee = new Employee($cookie->id_employee);

        $o = new Order($id_order);
        $o->shipping_number = $shipping_number;
        $o->save();

        // New in 1.5
        //$id_order_carrier = self::getIdOrderCarrier($o);
        /*$order_carrier = new OrderCarrier($id_order_carrier);
        $order_carrier->tracking_number = $shipping_number;
        $order_carrier->id_order = $id_order;
        $order_carrier->id_carrier = $o->id_carrier;
        $order_carrier->update();*/
        $id_order_carrier = self::getIdOrderCarrier($o);
        //$id_order_invoice = self::getIdOrderInvoice($o);
        $order_carrier = new OrderCarrier($id_order_carrier);

        if ($order_carrier->tracking_number=="" || $order_carrier->tracking_number==null) {
            $order_carrier->tracking_number = $shipping_number;
            $order_carrier->id_order = $id_order;
            $order_carrier->id_carrier = $o->id_carrier;
            $order_carrier->update();
        }

        /*else{
            $order_carrier = new OrderCarrier();
            $order_carrier->tracking_number = $shipping_number;
            $order_carrier->id_order = $id_order;
            $order_carrier->id_carrier = $o->id_carrier;
            //$order_carrier->id_order_invoice = $id_order_invoice;
            $order_carrier->save();
        }*/

        // No, there is no method in Order to retrieve the orderCarrier object(s)

        if ($o->getCurrentState() != _PS_OS_SHIPPING_) {
            $history = new OrderHistory();
            $history->id_order = (int)($o->id);
            $history->id_order_state = _PS_OS_SHIPPING_;
            $history->changeIdOrderState(_PS_OS_SHIPPING_, $o->id);
            $history->save();
        }

        $customer = new Customer($o->id_customer);
        $carrier = new Carrier($o->id_carrier);
        $tracking_url = str_replace('@', $o->shipping_number, $carrier->url);

        $templateVars = array(
            '{tracking_link}' => '<a href = "'.$tracking_url.'">'.$o->shipping_number.'</a>',
            '{tracking_code}' => $o->shipping_number,
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{id_order}' => (int)($o->id)
        );

        $subject = 'Tracking number for your order';

        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            Mail::Send(
                $o->id_lang,
                'tracking',
                $subject,
                $templateVars,
                $customer->email,
                $customer->firstname.' '.$customer->lastname,
                null,
                null,
                null,
                null,
                dirname(__FILE__).'/mails/',
                true
            );
        } else {
            Mail::Send(
                $o->id_lang,
                'tracking',
                $subject,
                $templateVars,
                $customer->email,
                $customer->firstname.' '.$customer->lastname,
                null,
                null,
                null,
                null,
                _PS_MODULE_DIR_ . 'chronopost/mails/',
                true
            );
        }
    }

    public static function getSelectedPickupPoint($id_cart)
    {
        $row = Db::getInstance()->getRow(
            'SELECT id_pr FROM '._DB_PREFIX_.'chrono_cart_relais WHERE id_cart='.$id_cart
        );
        return $row['id_pr'];
    }

    public static function getSelectedSlot($id_cart)
    {
        $row = Db::getInstance()->getRow('SELECT slot_code FROM '._DB_PREFIX_.'chrono_cart_creneau
            WHERE id_cart='.$id_cart);
        return $row['slot_code'];
    }

    public static function errorStatus()
    {
        /*
        Must be kept as a placeholder for customized deployments.

        $history = new OrderHistory();
        $history->id_order = (int)($id_order);
        $history->changeIdOrderState(10, (int)($id_order)); // TODO with conf value
        $history->save();
        */
    }

    /**
     * @param Order $order
     * @param bool  $is_return
     *
     * @return array
     */
    public static function getSkybillDetails($order, $is_return = false)
    {
        include_once dirname(__FILE__).'/libraries/webservicesHelper.php';
        $wsHelper = new webservicesHelper();
        $carrier = new Carrier($order->id_carrier);

        $result = array();

        // Ships with Chrono 13 by default
        $result['productCode'] = Chronopost::$carriers_definitions['CHRONO13']['product_code'];
        // Service code 0 by default
        $result['service'] = '0';

        switch ($carrier->id_reference) {
            case Configuration::get('CHRONOPOST_CHRONORELAIS_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13
                $result['productCode'] = Chronopost::$carriers_definitions['CHRONORELAIS']['product_code'];
                $result['recipientRef'] = Chronopost::getSelectedPickupPoint($order->id_cart);
                break;

            case Configuration::get('CHRONOPOST_CHRONOEXPRESS_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13
                $result['productCode'] = Chronopost::$carriers_definitions['CHRONOEXPRESS']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_CHRONO13_ID'):
                $result['productCode'] = Chronopost::$carriers_definitions['CHRONO13']['product_code'];
                if (Configuration::get('CHRONOPOST_BAL_ENABLED') == 1 && !$is_return) {
                    $result['productCode'] = '58';
                } // CHRONO 13 + BAL
                break;

            case Configuration::get('CHRONOPOST_CHRONO18_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13
                $result['productCode'] = Chronopost::$carriers_definitions['CHRONO18']['product_code'];
                if (Configuration::get('CHRONOPOST_BAL_ENABLED') == 1) {
                    $result['productCode'] = '2M';
                } // CHRONO 18 + BAL
                break;

            case Configuration::get('CHRONOPOST_CHRONO10_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13
                $result['productCode'] = Chronopost::$carriers_definitions['CHRONO10']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_CHRONOCLASSIC_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13
                $result['productCode'] = Chronopost::$carriers_definitions['CHRONOCLASSIC']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_SAMEDAY_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13
                $result['productCode'] = Chronopost::$carriers_definitions['SAMEDAY']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_DIMANCHEBAL_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13
                $result['service'] = '514';
                $result['as'] = 'B34';
                $result['productCode'] = Chronopost::$carriers_definitions['DIMANCHEBAL']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_CHRONORDV_ID'):
                if ($is_return) {
                    break;
                } // returns are Chrono13

                $res = DB::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'chrono_cart_creneau
                    WHERE id_cart='.(int)$order->id_cart);


                $timeSlot = new DateTime($res[0]['delivery_date']);
                $timeSlot->modify('+2 hours');

                $result['productCode'] = Chronopost::$carriers_definitions['CHRONORDV']['product_code'];
                $result['service'] = $res[0]['service_code'];
                $result['as'] = $res[0]['as_code'];
                $result['timeSlot'] = true;
                $result['timeSlotStartDate'] = $res[0]['delivery_date'];
                $result['timeSlotEndDate'] = $res[0]['delivery_date_end'];
                $result['timeSlotTariffLevel'] = $res[0]['tariff_level'];
                break;


            case Configuration::get('CHRONOPOST_RELAISEUROPE_ID'):
                if ($is_return) {
                    // returns are a specific product !
                    $result['productCode'] = '3T';
                    break;
                }

                $result['productCode'] = Chronopost::$carriers_definitions['RELAISEUROPE']['product_code'];
                $result['recipientRef'] = Chronopost::getSelectedPickupPoint($order->id_cart);

                // service is dependant on weight
                $result['service'] = 337;
                if ($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF')>3) {
                    $result['service'] = 338;
                }
                break;

            case Configuration::get('CHRONOPOST_RELAISDOM_ID'):
                if ($is_return) {
                    // returns are Chrono Express
                    $result['productCode'] = Chronopost::$carriers_definitions['CHRONOEXPRESS']['product_code'];
                    break;
                }

                $result['productCode'] = Chronopost::$carriers_definitions['RELAISDOM']['product_code'];
                $result['recipientRef'] = Chronopost::getSelectedPickupPoint($order->id_cart);
                $result['service'] = 368;

                break;
        }


        // Service code for Saturday deliveries
        if (
        in_array(
            $carrier->id_reference,
            array(
                Configuration::get('CHRONOPOST_CHRONORELAIS_ID'),
                Configuration::get('CHRONOPOST_CHRONO13_ID'),
                Configuration::get('CHRONOPOST_CHRONO10_ID'),
                Configuration::get('CHRONOPOST_CHRONO18_ID'),
                Configuration::get('CHRONOPOST_RELAISDOM_ID')
            )
        )
        ) {
            // International carriers never do deliveries on saturday

            // Called from hookAdminOrder
            if (Tools::getIsset('shipSaturday')) {
                $result['service'] = '6';
                if ($carrier->id_reference == Configuration::get('CHRONOPOST_RELAISDOM_ID')) {
                    $result['service'] = 369;
                }
            }
            // Called from export admin
            if (Tools::getIsset('orders') && Chronopost::isSaturdayOptionApplicable()) {
                $result['service'] = '6';
                if ($carrier->id_reference == Configuration::get('CHRONOPOST_RELAISDOM_ID')) {
                    $result['service'] = 369;
                }
            }

            // Called from orders pane
            if (Tools::getIsset('orderid') && Chronopost::isSaturdayOptionApplicable()) {
                $result['service'] = '6';
                if ($carrier->id_reference == Configuration::get('CHRONOPOST_RELAISDOM_ID')) {
                    $result['service'] = 369;
                }
            }

            // Could be shipping for saturday but is not
            if (Chronopost::gettingReadyForSaturday() && $result['service'] != '6' && $result['service'] != 369
                && $result['service'] != 368) {
                $result['service'] = '1';
            }
        }

        if ($is_return) {
            $shippingAddress = new Address($order->id_address_delivery);
            /* @todo réactiver ce code quand les WS auront été mis à jour
            $result['service'] = $wsHelper->getReturnServiceCode($result['productCode']);
             */
            $result['productCode'] = $wsHelper->getReturnProductCode($shippingAddress);
            if ($result['service'] == 6 || $result['service'] == 368 || $result['service'] == 369) {
                $result['service'] = 1;
            } else {
                $result['service'] = 0;
            }
        }

        return $result;
    }

    public static function buildControllerWhereQuery()
    {
        $query = '';
        foreach (array_keys(self::$carriers_definitions) as $code) {
            if ($query=='') {
                $query = 'AND (ca.id_reference='.((int)Configuration::get('CHRONOPOST_'.$code.'_ID'));
            } else {
                $query .= ' OR ca.id_reference='.((int)Configuration::get('CHRONOPOST_'.$code.'_ID'));
            }
        }
        return $query.') ';
    }

    public static function amountToInsure($id_order)
    {
        if (Configuration::get('CHRONOPOST_ADVALOREM_ENABLED') == 0) {
            return -1;
        }

        if ($recapLT = DB::getInstance()->getRow('SELECT insurance FROM ' ._DB_PREFIX_.'chrono_lt_history WHERE
        id_order='.$id_order)) {
            if ($recapLT["insurance"] < (float)Configuration::get('CHRONOPOST_ADVALOREM_MINVALUE')) {
                return (float)Configuration::get('CHRONOPOST_ADVALOREM_MINVALUE');
            } else {
                return $recapLT["insurance"];
            }
        } else {
            return (float)Configuration::get('CHRONOPOST_ADVALOREM_MINVALUE');
        }
    }

    //prestashop 1.7
    public function hookActionCarrierUpdate($params)
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        // Ensures Chrono18 && Chrono13 not selected at the same time
        $c18 = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONO18_ID'));
        $c13 = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONO13_ID'));

        if (($params['carrier']->id_reference == Configuration::get('CHRONOPOST_CHRONO13_ID')
                && (int)$params['carrier']->active == 1 && $c18->active == 1)
            || ($params['carrier']->id == Configuration::get('CHRONOPOST_CHRONO18_ID')
                && (int)$params['carrier']->active == 1    && $c13->active == 1)) {
            $params['carrier']->active = 0;
            $params['carrier']->save();

            echo '<script>alert("'.$this->l('You can\'t activate simultaneously Chronopost before 13h and before 18h.'
                ).'");
                history.back();
            </script>';
            exit();
        }
    }

    //Pour les versions avant 1.7
    public function hookUpdateCarrier($params)
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // Ensures Chrono18 && Chrono13 not selected at the same time
        $c18 = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONO18_ID'));
        $c13 = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONO13_ID'));

        if (($params['carrier']->id_reference == Configuration::get('CHRONOPOST_CHRONO13_ID')
                && (int)$params['carrier']->active == 1 && $c18->active == 1)
            || ($params['carrier']->id == Configuration::get('CHRONOPOST_CHRONO18_ID')
                && (int)$params['carrier']->active == 1    && $c13->active == 1)) {
            $params['carrier']->active = 0;
            $params['carrier']->save();

            echo '<script>alert("'.$this->l('You can\'t activate simultaneously Chronopost before 13h and before 18h.'
                ).'");
                history.back();
            </script>';
            exit();
        }
    }

    public function hookNewOrder($params)
    {
        //exit();
        if (Chronopost::isRelais($params['order']->id_carrier)) {
            $relais = Db::getInstance()->getValue('SELECT id_pr FROM `'._DB_PREFIX_.'chrono_cart_relais`
                WHERE id_cart = '.(int)$params['cart']->id);
            if (!$relais) {
                return;
            }

            include_once _PS_MODULE_DIR_.'/chronopost/libraries/PointRelaisServiceWSService.php';

            // Update order delivery address to PR address (new in 2.8.4 per support #300)

            // Data
            $cart = $params['cart'];
            if (!Validate::isLoadedObject($cart)) {
                return false;
            }

            $current_address = new Address($cart->id_address_delivery);

            // Getting relais details
            // We have to use PointRelaisService so we are in Chronopost's most up-to-date environnement
            $ws = new PointRelaisServiceWSService();
            $paramsw = new rechercheDetailPointChronopost();
            $paramsw->accountNumber = Configuration::get('CHRONOPOST_GENERAL_ACCOUNT');
            $paramsw->password = Configuration::get('CHRONOPOST_GENERAL_PASSWORD');
            $paramsw->identifiant = $relais;
            $bt = $ws->rechercheDetailPointChronopost($paramsw)->return->listePointRelais;

            // Populate Address object
            $a = new Address();
            $a->alias = 'Point ChronoRelais '.$bt->identifiant;
            $a->id_customer = $cart->id_customer;
            $a->id_country = Country::getByIso($bt->codePays);
            $a->company = Tools::substr($bt->nom, 0, 32);
            $a->lastname = $current_address->lastname;
            $a->firstname = $current_address->firstname;
            $a->address1 = $bt->adresse1;
            $a->address2 = isset($bt->adresse2) ? $bt->adresse2 : '';
            $a->postcode = $bt->codePostal;
            $a->city = $bt->localite;
            $a->phone = $current_address->phone;
            $a->phone_mobile = $current_address->phone_mobile;
            $a->other = $bt->identifiant; // ID Point Relais
            $a->active = 0;
            $a->deleted = 1;
            $a->id_customer = null;
            $a->id_manufacturer = null;

            // Save && assign to cart
            $a->save();
            $params['order']->id_address_delivery = $a->id;
            $params['order']->save();

            return;
        }

        if (Chronopost::isRDV($params['order']->id_carrier)) {
            include_once(dirname(__FILE__).'/libraries/CreneauWS.php');

            // Data
            $cart = $params['cart'];
            if (!Validate::isLoadedObject($cart)) {
                return false;
            }

            $current_address = new Address($cart->id_address_delivery);

            $ws = new CreneauWS();
            $header = array();
            $header[] = new SoapHeader(
                'http://cxf.soap.ws.creneau.chronopost.fr/',
                'accountNumber',
                Configuration::get('CHRONOPOST_GENERAL_ACCOUNT'),
                false
            );
            $header[] = new SoapHeader(
                'http://cxf.soap.ws.creneau.chronopost.fr/',
                'password',
                Configuration::get('CHRONOPOST_GENERAL_PASSWORD'),
                false
            );
            $ws->__setSoapHeaders($header);

            $query = new confirmDeliverySlotV2();
            $query->callerTool = 'RDVPRE';
            $query->productType = 'RDV'; // normal product
            $query->meshCode = $current_address->postcode;

            $res = DB::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'chrono_cart_creneau
                WHERE id_cart='.(int)$params['cart']->id);

            $query->rank = $res[0]['rank'];
            $query->dateSelected = $res[0]['delivery_date'];
            $query->transactionID = $res[0]['transaction_id'];
            $query->codeSlot = $res[0]['slot_code'];

            $res = $ws->confirmDeliverySlotV2($query);

            if ($res->return->code != 0) {
                return false;
            }

            DB::getInstance()->execute('UPDATE '._DB_PREFIX_.'chrono_cart_creneau SET
                product_code="'.pSQL((string)$res->return->productServiceV2->productCode).'",
                service_code="'.pSQL((string)$res->return->productServiceV2->serviceCode).'",
                as_code="'.pSQL((string)$res->return->productServiceV2->asCode).'"
                WHERE id_cart='.(int)$params['cart']->id);
        }
    }


    public function hookBackOfficeHeader($params)
    {
        if (version_compare(_PS_VERSION_, 1.6) < 0) {
            $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/backoffice15.css', 'all');
        }

        $file = Tools::getValue('controller');
        if (!in_array($file, array('AdminOrders'))) {
            return;
        }

        return '<script src="https://maps.googleapis.com/maps/api/js?key='. Configuration::get('CHRONOPOST_MAP_APIKEY')
            .'"></script><script>
            $(document).ready(function() {
                $.get("'._MODULE_DIR_.'chronopost/async/updateTracking.php");
            });
        </script>';
    }

    //version 1.7
    public function hookDisplayHeader($params)
    {
        // check if on right page

        $file = Tools::getValue('controller');
        $module_uri = _MODULE_DIR_.$this->name;

        if($file == 'orderdetail'){
            $this->context->controller->addJS($module_uri.'/views/js/orderHistory.js');
            return;
        }
        elseif (!in_array($file, array('order-opc', 'order', 'orderopc'))) {
            return;
        }


        $this->context->controller->addCSS($module_uri.'/views/css/chronorelais.css', 'all');
        $this->context->controller->addCSS($module_uri.'/views/css/chronordv.css', 'all');
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $this->context->controller->addJS($module_uri.'/views/js/chronorelais.js');
            $this->context->controller->addJS($module_uri.'/views/js/chronordv.js');
        } else {
            $this->context->controller->addJS($module_uri.'/views/js/jquery-1.11.0.min.js');
            $this->context->controller->addJS($module_uri.'/views/js/chronorelais-17.js');
            $this->context->controller->addJS($module_uri.'/views/js/chronordv-17.js');
        }
        return '<script src="https://maps.googleapis.com/maps/api/js?key='. Configuration::get('CHRONOPOST_MAP_APIKEY')
            .'"></script>';
    }

    public function hookHeader($params)
    {
        // check if on right page

        $file = Tools::getValue('controller');
        if (!in_array($file, array('order-opc', 'order', 'orderopc'))) {
            return;
        }

        $module_uri = _MODULE_DIR_.$this->name;
        $this->context->controller->addCSS($module_uri.'/views/css/chronorelais.css', 'all');
        $this->context->controller->addCSS($module_uri.'/views/css/chronordv.css', 'all');
        $this->context->controller->addJS($module_uri.'/views/js/chronorelais.js');
        $this->context->controller->addJS($module_uri.'/views/js/chronordv.js');
        return '<script src="https://maps.googleapis.com/maps/api/js?key='. Configuration::get('CHRONOPOST_MAP_APIKEY')
            .'"></script>';
    }

    //Début Version 1.7
    public function hookDisplayAfterCarrier($params)
    {
        $address = new Address($params['cart']->id_address_delivery);
        $country = new Country($address->id_country);

        $chronorelais_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORELAIS_ID'));
        $relaiseurope_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_RELAISEUROPE_ID'));
        $relaisdom_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_RELAISDOM_ID'));
        $rdv_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORDV_ID'));

        $chronorelais_id = $chronorelais_carrier ? $chronorelais_carrier->id : -1;
        $relaiseurope_id = $relaiseurope_carrier ? $relaiseurope_carrier->id : -1;
        $relaisdom_id = $relaisdom_carrier ? $relaisdom_carrier->id : -1;
        $rdv_id = $rdv_carrier ? $rdv_carrier->id : -1;

        if ($rdv_id == -1 && $chronorelais_id == -1 && $relaiseurope_id == -1 && $relaisdom_id == -1) {
            return ;
        }

        $this->context->smarty->assign(
            array(
                'module_uri' =>__PS_BASE_URI__.'modules/'.$this->name,
                'cust_codePostal' => $address->postcode,
                'cust_firstname' => $address->firstname,
                'cust_lastname' => $address->lastname,
                'cartID' => $params['cart']->id,
                'CHRONORELAIS_ID' => $chronorelais_id,
                'CHRONORELAIS_ID_INT' => (string)Cart::intifier($chronorelais_id),
                'RELAISEUROPE_ID' => $relaiseurope_id,
                'RELAISEUROPE_ID_INT' => (string)Cart::intifier($relaiseurope_id),
                'RELAISDOM_ID' => $relaisdom_id,
                'RELAISDOM_ID_INT' => (string)Cart::intifier($relaisdom_id),
                'cust_address' => $address->address1.' '.$address->address2.' '
                    .$address->postcode.' '.$address->city,
                'cust_address_clean' => $address->address1.' '.$address->address2.' ',
                'cust_city' => $address->city,
                'cust_country' => Country::getIsoById($address->id_country),
                'map_enabled' => Configuration::get('CHRONOPOST_MAP_ENABLED'),
                'map_apikey' => Configuration::get('CHRONOPOST_MAP_APIKEY')
            )
        );
        $r = $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/hook/chronorelais-17.tpl');

        if ($rdv_id == -1 || ($country->iso_code != 'FR' && $country->iso_code != 'FX')) {
            return $r;
        }

        // TODO allow for either one to be activated
        // Currently chronordv needs chronorelais's JS, hence ChronoRelais is always included

        // call WS !
        include_once(dirname(__FILE__).'/libraries/CreneauWS.php');
        $query = new searchDeliverySlot();
        $query->callerTool = 'RDVPRE';
        $query->productType = 'RDV'; // normal product
        $query->recipientZipCode = $address->postcode;
        $query->shipperAdress1 = Configuration::get('CHRONOPOST_SHIPPER_ADDRESS');
        $query->shipperAdress2 = Configuration::get('CHRONOPOST_SHIPPER_ADDRESS2');
        $query->shipperCity = Configuration::get('CHRONOPOST_SHIPPER_CITY');
        $query->shipperCountry = Configuration::get('CHRONOPOST_SHIPPER_COUNTRY');
        $query->shipperName = Configuration::get('CHRONOPOST_SHIPPER_NAME');
        $query->shipperName2 = Configuration::get('CHRONOPOST_SHIPPER_NAME2');
        $query->shipperZipCode = Configuration::get('CHRONOPOST_SHIPPER_ZIPCODE');
        $query->recipientAdress1 = $address->address1;
        $query->recipientAdress2 = $address->address2;
        $query->recipientCity = $address->city;
        $query->recipientCountry = 'FR';
        $query->recipientZipCode = $address->postcode;
        $query->weight = 1;

        // Calculate earliest possible shipping date
        $date = $this->getNextDay(
            Configuration::get('CHRONOPOST_RDV_DAY_ON'),
            Configuration::get('CHRONOPOST_RDV_HOUR_ON'),
            Configuration::get('CHRONOPOST_RDV_MINUTE_ON')
        );

        if ($date == null) {
            $date = new DateTime();
            $date->modify('+ '.(int)Configuration::get('CHRONOPOST_RDV_DELAY').' days');
        }

        $query->dateBegin = $date->format('Y-m-d\TH:i:s');
        $date->modify('+ 7 days');
        $query->dateEnd = $date->format('Y-m-d\TH:i:s');

        // Calculate next closing period
        $close_start = $this->getNextDay(
            Configuration::get('CHRONOPOST_RDV_DAY_CLOSE_ST'),
            Configuration::get('CHRONOPOST_RDV_HOUR_CLOSE_ST'),
            Configuration::get('CHRONOPOST_RDV_MINUTE_CLOSE_ST')
        );
        $close_end = $this->getNextDay(
            Configuration::get('CHRONOPOST_RDV_DAY_CLOSE_END'),
            Configuration::get('CHRONOPOST_RDV_HOUR_CLOSE_END'),
            Configuration::get('CHRONOPOST_RDV_MINUTE_CLOSE_END')
        );

        if ($close_start != null && $close_end != null && $close_start != $close_end) {
            $query->customerDeliverySlotClosed = $close_start->format('Y-m-d\TH:i:s\Z')
                .'/'.$close_end->format('Y-m-d\TH:i:s\Z');
        }

        $ws = new CreneauWS();

        $header = array();
        $header[] = new SoapHeader(
            'http://cxf.soap.ws.creneau.chronopost.fr/',
            'accountNumber',
            Configuration::get('CHRONOPOST_GENERAL_ACCOUNT'),
            false
        );
        $header[] = new SoapHeader(
            'http://cxf.soap.ws.creneau.chronopost.fr/',
            'password',
            Configuration::get('CHRONOPOST_GENERAL_PASSWORD'),
            false
        );
        $ws->__setSoapHeaders($header);

        $res = $ws->searchDeliverySlot($query);

        if (!$res->return->slotList) {
            return $r;
        }

        // group by hour then days
        $ordered_slots = array();
        $days = array();

        foreach ($res->return->slotList as $slot) {
            if ($slot->startHour < 10) {
                $slot->startHour = '0'.$slot->startHour;
            }
            $hour_idx = $slot->startHour.'H - '.$slot->endHour.'H';
            $when = new DateTime($slot->deliveryDate);
            $day_idx = $when->format("d/m/Y");
            if (!array_key_exists($hour_idx, $ordered_slots)) {
                $ordered_slots[$hour_idx] = array();
            }
            if (!in_array($day_idx, $days)) {
                $days[] = $day_idx;
            }

            $deliveryDateTime = new DateTime($slot->deliveryDate);
            $deliveryDateTime->setTime($slot->startHour, $slot->startMinutes);

            $deliveryDateTimeEnd = clone $deliveryDateTime;
            $deliveryDateTimeEnd->setTime($slot->endHour, $slot->endMinutes);

            $tariffLevel = Tools::substr($slot->tariffLevel, 1);
            $price = Chronopost::getRDVCost($params['cart']->id, $tariffLevel);

            $slot->tariffLevel = $tariffLevel;
            $slot->price = Tools::displayPrice($price);
            $slot->fee = $price;
            $slot->deliveryDateTime = date_format($deliveryDateTime, 'Y-m-d\TH:i:s');
            $slot->deliveryDateTimeEnd = date_format($deliveryDateTimeEnd, 'Y-m-d\TH:i:s');
            $slot->enable = Configuration::get('CHRONOPOST_RDV_STATE'.$tariffLevel);

            // For sundays, we let the WS drive the enabled status
            if ($slot->dayOfWeek == 7) {
                $slot->enable = (bool) ($slot->status == 'O');
            }

            $ordered_slots[$hour_idx][$day_idx] = $slot;
        }

        ksort($ordered_slots);

        $this->context->smarty->assign(
            array(
                'rdv_ordered_slots' => $ordered_slots,
                'rdv_days' => $days,
                'rdv_carrierID' => $rdv_id,
                'rdv_carrierIntID' => (string)Cart::intifier($rdv_id),
                'rdv_transactionID' => (string)$res->return->transactionID
            )
        );

        return $r.$this->context->smarty->fetch(dirname(__FILE__).'/views/templates/hook/chronordv-17.tpl');
    }
    //Fin Version 1.7

    //Pour les versions inférieures à 1.7
    public function hookExtraCarrier($params)
    {
        $address = new Address($params['cart']->id_address_delivery);
        $country = new Country($address->id_country);

        $chronorelais_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORELAIS_ID'));
        $relaiseurope_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_RELAISEUROPE_ID'));
        $relaisdom_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_RELAISDOM_ID'));
        $rdv_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORDV_ID'));

        $chronorelais_id = $chronorelais_carrier ? $chronorelais_carrier->id : -1;
        $relaiseurope_id = $relaiseurope_carrier ? $relaiseurope_carrier->id : -1;
        $relaisdom_id = $relaisdom_carrier ? $relaisdom_carrier->id : -1;
        $rdv_id = $rdv_carrier ? $rdv_carrier->id : -1;

        if ($rdv_id == -1 && $chronorelais_id == -1 && $relaiseurope_id == -1 && $relaisdom_id == -1) {
            return ;
        }

        $this->context->smarty->assign(
            array(
                'module_uri' =>__PS_BASE_URI__.'modules/'.$this->name,
                'cust_codePostal' => $address->postcode,
                'cust_firstname' => $address->firstname,
                'cust_lastname' => $address->lastname,
                'cartID' => $params['cart']->id,
                'CHRONORELAIS_ID' => $chronorelais_id,
                'CHRONORELAIS_ID_INT' => (string)Cart::intifier($chronorelais_id),
                'RELAISEUROPE_ID' => $relaiseurope_id,
                'RELAISEUROPE_ID_INT' => (string)Cart::intifier($relaiseurope_id),
                'RELAISDOM_ID' => $relaisdom_id,
                'RELAISDOM_ID_INT' => (string)Cart::intifier($relaisdom_id),
                'cust_address' => $address->address1.' '.$address->address2.' '
                    .$address->postcode.' '.$address->city,
                'cust_address_clean' => $address->address1.' '.$address->address2.' ',
                'cust_city' => $address->city,
                'cust_country' => Country::getIsoById($address->id_country),
                'map_enabled' => Configuration::get('CHRONOPOST_MAP_ENABLED'),
                'map_apikey' => Configuration::get('CHRONOPOST_MAP_APIKEY')
            )
        );
        $r = $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/hook/chronorelais.tpl');

        if ($rdv_id == -1 || ($country->iso_code != 'FR' && $country->iso_code != 'FX')) {
            return $r;
        }

        // TODO allow for either one to be activated
        // Currently chronordv needs chronorelais's JS, hence ChronoRelais is always included

        // call WS !
        include_once(dirname(__FILE__).'/libraries/CreneauWS.php');
        $query = new searchDeliverySlot();
        $query->callerTool = 'RDVPRE';
        $query->productType = 'RDV'; // normal product
        $query->recipientZipCode = $address->postcode;
        $query->shipperAdress1 = Configuration::get('CHRONOPOST_SHIPPER_ADDRESS');
        $query->shipperAdress2 = Configuration::get('CHRONOPOST_SHIPPER_ADDRESS2');
        $query->shipperCity = Configuration::get('CHRONOPOST_SHIPPER_CITY');
        $query->shipperCountry = Configuration::get('CHRONOPOST_SHIPPER_COUNTRY');
        $query->shipperName = Configuration::get('CHRONOPOST_SHIPPER_NAME');
        $query->shipperName2 = Configuration::get('CHRONOPOST_SHIPPER_NAME2');
        $query->shipperZipCode = Configuration::get('CHRONOPOST_SHIPPER_ZIPCODE');
        $query->recipientAdress1 = $address->address1;
        $query->recipientAdress2 = $address->address2;
        $query->recipientCity = $address->city;
        $query->recipientCountry = 'FR';
        $query->recipientZipCode = $address->postcode;
        $query->weight = 1;

        // Calculate earliest possible shipping date
        $date = $this->getNextDay(
            Configuration::get('CHRONOPOST_RDV_DAY_ON'),
            Configuration::get('CHRONOPOST_RDV_HOUR_ON'),
            Configuration::get('CHRONOPOST_RDV_MINUTE_ON')
        );

        if ($date == null) {
            $date = new DateTime();
            $date->modify('+ '.(int)Configuration::get('CHRONOPOST_RDV_DELAY').' days');
        }

        $query->dateBegin = $date->format('Y-m-d\TH:i:s');
        $date->modify('+ 7 days');
        $query->dateEnd = $date->format('Y-m-d\TH:i:s');

        // Calculate next closing period
        $close_start = $this->getNextDay(
            Configuration::get('CHRONOPOST_RDV_DAY_CLOSE_ST'),
            Configuration::get('CHRONOPOST_RDV_HOUR_CLOSE_ST'),
            Configuration::get('CHRONOPOST_RDV_MINUTE_CLOSE_ST')
        );
        $close_end = $this->getNextDay(
            Configuration::get('CHRONOPOST_RDV_DAY_CLOSE_END'),
            Configuration::get('CHRONOPOST_RDV_HOUR_CLOSE_END'),
            Configuration::get('CHRONOPOST_RDV_MINUTE_CLOSE_END')
        );

        if ($close_start != null && $close_end != null && $close_start != $close_end) {
            $query->customerDeliverySlotClosed = $close_start->format('Y-m-d\TH:i:s\Z')
                .'/'.$close_end->format('Y-m-d\TH:i:s\Z');
        }

        $ws = new CreneauWS();

        $header = array();
        $header[] = new SoapHeader(
            'http://cxf.soap.ws.creneau.chronopost.fr/',
            'accountNumber',
            Configuration::get('CHRONOPOST_GENERAL_ACCOUNT'),
            false
        );
        $header[] = new SoapHeader(
            'http://cxf.soap.ws.creneau.chronopost.fr/',
            'password',
            Configuration::get('CHRONOPOST_GENERAL_PASSWORD'),
            false
        );
        $ws->__setSoapHeaders($header);

        $res = $ws->searchDeliverySlot($query);

        if (!$res->return->slotList) {
            return $r;
        }

        // group by hour then days
        $ordered_slots = array();
        $days = array();

        foreach ($res->return->slotList as $slot) {
            if ($slot->startHour < 10) {
                $slot->startHour = '0'.$slot->startHour;
            }
            $hour_idx = $slot->startHour.'H - '.$slot->endHour.'H';
            $when = new DateTime($slot->deliveryDate);
            $day_idx = $when->format("d/m/Y");
            if (!array_key_exists($hour_idx, $ordered_slots)) {
                $ordered_slots[$hour_idx] = array();
            }
            if (!in_array($day_idx, $days)) {
                $days[] = $day_idx;
            }

            $deliveryDateTime = new DateTime($slot->deliveryDate);
            $deliveryDateTime->setTime($slot->startHour, $slot->startMinutes);

            $deliveryDateTimeEnd = clone $deliveryDateTime;
            $deliveryDateTimeEnd->setTime($slot->endHour, $slot->endMinutes);

            $tariffLevel = Tools::substr($slot->tariffLevel, 1);
            $price = Chronopost::getRDVCost($params['cart']->id, $tariffLevel);

            $slot->tariffLevel = $tariffLevel;
            $slot->price = Tools::displayPrice($price);
            $slot->fee = $price;
            $slot->deliveryDateTime = date_format($deliveryDateTime, 'Y-m-d\TH:i:s');
            $slot->deliveryDateTimeEnd = date_format($deliveryDateTimeEnd, 'Y-m-d\TH:i:s');
            $slot->enable = Configuration::get('CHRONOPOST_RDV_STATE'.$tariffLevel);

            // For sundays, we let the WS drive the enabled status
            if ($slot->dayOfWeek == 7) {
                $slot->enable = (bool) ($slot->status == 'O');
            }

            $ordered_slots[$hour_idx][$day_idx] = $slot;
        }

        ksort($ordered_slots);

        $this->context->smarty->assign(
            array(
                'rdv_ordered_slots' => $ordered_slots,
                'rdv_days' => $days,
                'rdv_carrierID' => $rdv_id,
                'rdv_carrierIntID' => (string)Cart::intifier($rdv_id),
                'rdv_transactionID' => (string)$res->return->transactionID
            )
        );

        return $r.$this->context->smarty->fetch(dirname(__FILE__).'/views/templates/hook/chronordv.tpl');
    }

    private function getNextDay($day)
    {
        if ($day == -1) {
            return null;
        }
        $date = new DateTime();

        switch ($day) {
            case 0:
                $date->modify('next Sunday');
                break;
            case 1:
                $date->modify('next Monday');
                break;
            case 2:
                $date->modify('next Tuesday');
                break;
            case 3:
                $date->modify('next Wednesday');
                break;
            case 4:
                $date->modify('next Thursday');
                break;
            case 5:
                $date->modify('next Friday');
                break;
            case 6:
                $date->modify('next Saturday');
                break;
        }

        $date->modify('+ '.Configuration::get('CHRONOPOST_RDV_HOUR_ON').' hours '.
            Configuration::get('CHRONOPOST_RDV_MINUTE_ON').' minutes');

        return $date;
    }


    public static function getPointRelaisAddress($orderid)
    {
        $order = new Order($orderid);
        include_once dirname(__FILE__).'/libraries/PointRelaisServiceWSService.php';

        if ($order->id_carrier != Configuration::get('CHRONOPOST_CHRONORELAIS_ID')) {
            return null;
        }


        $btid = Db::getInstance()->getRow('SELECT id_pr FROM `'._DB_PREFIX_.'chrono_cart_relais`
            WHERE id_cart = '.$order->id_cart);
        $btid = $btid['id_pr'];

        // Fetch BT object
        $ws = new PointRelaisServiceWSService();

        $p = new rechercheBtAvecPFParIdChronopostA2Pas();
        $p->id = $btid;
        $bt = $ws->rechercheBtAvecPFParIdChronopostA2Pas($p)->return;

        return $bt;
    }

    public static function minNumberOfPackages($orderid)
    {
        $order = new Order($orderid);
        $nblt = 1;


        if ($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') > 20
            && Chronopost::isRelais($order->id_carrier)) {
            $nblt = ceil($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') / 20);
        }

        if ($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') > 30) {
            $nblt = ceil($order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') / 30);
        }

        return $nblt;
    }

    public static function isChrono($id_carrier)
    {
        $carrier = new Carrier($id_carrier);

        return $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONO13_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONORELAIS_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONOEXPRESS_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONO10_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONO18_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONOCLASSIC_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_RELAISEUROPE_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_RELAISDOM_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONORDV_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_SAMEDAY_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_DIMANCHEBAL_ID');
    }

    public static function isRelais($id_carrier)
    {
        if (!self::isChrono($id_carrier)) {
            return false;
        }

        $carrier = new Carrier($id_carrier);

        return $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONORELAIS_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_RELAISEUROPE_ID')
            || $carrier->id_reference == Configuration::get('CHRONOPOST_RELAISDOM_ID');
    }

    public static function isRDV($id_carrier)
    {
        if (!self::isChrono($id_carrier)) {
            return false;
        }

        $carrier = new Carrier($id_carrier);

        return $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONORDV_ID');
    }

    public static function getRDVCost($id_cart, $tariffLevel = null, $shipping_cost = 0)
    {
        $cart = new Cart($id_cart);
        $sub_tariff = 1;

        if (self::isRDV($cart->id_carrier)) {
            // then carrier is already selected
            // return cost for selected slot
            $res = DB::getInstance()->executeS('SELECT fee, tariff_level FROM '._DB_PREFIX_.'chrono_cart_creneau
                WHERE id_cart='.(int)$id_cart);

            if ($tariffLevel == null && $res && $res[0]['fee'] > 0) {
                return $res[0]['fee'];
            } elseif ($res) {
                $sub_tariff = (int)$res[0]['tariff_level'];
            }
        }

        // other price display
        if ($tariffLevel == null) {
            $tariffLevel = 1;
        }

        if ($shipping_cost == 0) {
            $rdv_carrier = Carrier::getCarrierByReference(Configuration::get('CHRONOPOST_CHRONORDV_ID'));
            $rdv_id = $rdv_carrier ? $rdv_carrier->id : -1;

            $shipping_cost = $cart->getOrderShippingCost($rdv_id) - Configuration::get('CHRONOPOST_RDV_PRICE'.
                    $sub_tariff);
        }

        return Configuration::get('CHRONOPOST_RDV_PRICE'.$tariffLevel) + $shipping_cost;
    }


    public static function isReturnAvailable($order)
    {
        $address = new Address($order->id_address_delivery);
        $country = new Country($address->id_country);
        if ($country->iso_code == 'FR') {
            return true;
        }
        if ($country->id_zone == 7) {
            return true;
        }
        include_once(dirname(__FILE__).'/libraries/QuickcostServiceWSService.php');

        $ws = new QuickcostServiceWSService();
        $cp = new calculateProducts();

        $cp->accountNumber = Configuration::get('CHRONOPOST_GENERAL_ACCOUNT');
        $cp->password = Configuration::get('CHRONOPOST_GENERAL_PASSWORD');
        $cp->depZipCode = $address->postcode;
        $cp->depCountryCode = $country->iso_code;
        $cp->weight = $order->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') + 0.1;
        $cp->arrCountryCode = Configuration::get('CHRONOPOST_SHIPPER_COUNTRY');
        $cp->arrZipCode = Configuration::get('CHRONOPOST_SHIPPER_ZIPCODE');
        $cp->type = 'M';

        try {
            $cpres = $ws->calculateProducts($cp);
        } catch (Exception $e) {
            return false;
        }

        if (empty($cpres->return->productList)) {
            return false;
        }

        if (!is_array($cpres->return->productList)) {
            $cpres->return->productList=array($cpres->return->productList);
        }

        foreach ($cpres->return->productList as $product) {
            if ($product->productCode == '3T') {
                return true;
            }
        }
    }

    public function hookAdminOrder($params)
    {
        $order = new Order((int)$params['id_order']);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }
        if (!self::isChrono($order->id_carrier)) {
            return '';
        }

        $return_available = self::isReturnAvailable($order);
        $carrier = new Carrier($order->id_carrier);


        $id_order_carrier = self::getIdOrderCarrier($order);
        $order_carrier = new OrderCarrier($id_order_carrier);

        $LTHistory = self::getAllTrackingNumbers($params['id_order']);

        $trackingUrl = false;

        if (count($LTHistory) > 0) {
            $lt_history = array();
            foreach ($LTHistory as $number) {
                $lt_history[$number] = str_replace('@', $number, self::$tracking_url);
            }
        }


        $this->context->smarty->assign(
            array(
                'module_uri' =>__PS_BASE_URI__.'modules/'.$this->name,
                'id_order' => $params['id_order'],
                'chronopost_secret' => Configuration::get('CHRONOPOST_SECRET'),
                'bal' => Configuration::get('CHRONOPOST_BAL_ENABLED') == 1
                && (
                    $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONO13_ID')
                    || $carrier->id_reference == Configuration::get('CHRONOPOST_CHRONO18_ID')
                ) ? 1 : 0,
                'saturday' => self::gettingReadyForSaturday($carrier) ? 1 : 0,
                'saturday_ok' => self::isSaturdayOptionApplicable() ? 1 : 0,
                'to_insure' =>  self::amountToInsure($params['id_order']),
                'nbwb' => self::minNumberOfPackages($params['id_order']),
                'return' => $return_available ? 1 : 0,
                'return_default' => Configuration::get('CHRONOPOST_RETURN_DEFAULT'),
                'lt' => $order_carrier->tracking_number,
                'lt_history' => json_encode($LTHistory),
                'lt_history_link' => json_encode($lt_history),
            )
        );
        if (version_compare(_PS_VERSION_, 1.6) >= 0) {
            return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/hook/adminOrder-16.tpl');
        } else {
            return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/hook/adminOrder-15.tpl');
        }
    }

    public static function calculateProducts($cart)
    {
        $a = new Address($cart->id_address_delivery);
        $c = new Country($a->id_country);

        // TODO TMP : relaisdom always true
        $res = array('chrono10' => false, 'chronoclassic' => false, 'chrono18' => false, 'relaiseurope' => false,
            'relaisdom' => true, 'rdv' => false, 'sameday' => false, 'dimanchebal' => false);

        $cache = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'chrono_calculateproducts_cache2`
            WHERE postcode = "'.pSQL($a->postcode).'" AND country = "'.pSQL($c->iso_code).'"');

        if (empty($cache) || $cache[0]['last_updated'] + 24 * 3600 < time()) {
            // QUICKCOST & CALCULATE PRODUCTS
            include_once(dirname(__FILE__).'/libraries/QuickcostServiceWSService.php');

            $ws = new QuickcostServiceWSService();
            $cp = new calculateProducts();

            $cp->accountNumber = Configuration::get('CHRONOPOST_GENERAL_ACCOUNT');
            $cp->password = Configuration::get('CHRONOPOST_GENERAL_PASSWORD');
            $cp->depZipCode = Configuration::get('CHRONOPOST_SHIPPER_ZIPCODE');
            $cp->depCountryCode = Configuration::get('CHRONOPOST_SHIPPER_COUNTRY');
            $cp->weight = $cart->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') + 0.1;
            $cp->arrCountryCode = $c->iso_code;
            $cp->arrZipCode = $a->postcode;
            $cp->type = 'M';

            try {
                $cpres = $ws->calculateProducts($cp);
            } catch (Exception $e) {
                return $res;
            }

            if (empty($cpres->return->productList)) {
                return $res;
            }

            if (!is_array($cpres->return->productList)) {
                $cpres->return->productList=array($cpres->return->productList);
            }

            foreach ($cpres->return->productList as $product) {
                if ($product->productCode == 2) {
                    $res['chrono10'] = true;
                }

                if ($product->productCode == 16) {
                    $res['chrono18'] = true;
                }

                if ($product->productCode == 44) {
                    $res['chronoclassic'] = true;
                }

                if ($product->productCode == '4P') {
                    $res['relaisdom'] = true;
                }

                if ($product->productCode == 49) {
                    $res['relaiseurope'] = true;
                }

                if ($product->productCode == '2O') {
                    $res['rdv'] = true;
                }

                if ($product->productCode == '4I') {
                    $res['sameday'] = true;
                }

                if ($product->productCode == '5A') {
                    $res['dimanchebal'] = true;
                }
            }

            // INSERT cache
            if (empty($cache)) {
                $sql = 'INSERT INTO `'._DB_PREFIX_.'chrono_calculateproducts_cache2`
                    (`postcode`,`country`, `chrono10`,`chrono18`, `chronoclassic`, `relaiseurope`, `relaisdom`, `rdv`,
                    `sameday`, `dimanchebal`, `last_updated`) VALUES
                    ("'.pSQL($a->postcode).'",
                    "'.pSQL($c->iso_code).'",
                    '.($res['chrono10'] == true? 1 : 0).',
                    '.($res['chrono18'] == true ? 1 : 0).',
                    '.($res['chronoclassic'] == true ? 1 : 0).',
                    '.($res['relaiseurope'] == true ? 1 : 0).',
                    '.($res['relaisdom'] == true ? 1 : 0).',
                    '.($res['rdv'] == true ? 1 : 0).',
                    '.($res['sameday'] == true ? 1 : 0).',
                    '.($res['dimanchebal'] == true ? 1 : 0).',
                    '.time().')';
                Db::getInstance()->Execute($sql);
            } else { // UPDATE cache
                Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'chrono_calculateproducts_cache2`
                    SET `chrono10` = '.($res['chrono10'] == true ? 1 : 0).',
                    `chrono18` = '.($res['chrono18'] == true ? 1 : 0).',
                    `chronoclassic` = '.($res['chronoclassic'] == true ? 1 : 0).',
                    `relaiseurope` = '.($res['relaiseurope'] == true ? 1 : 0).',
                    `relaisdom` = '.($res['relaisdom'] == true ? 1 : 0).',
                    `rdv` = '.($res['rdv'] == true ? 1 : 0).',
                    `sameday` = '.($res['sameday'] == true ? 1 : 0).',
                    `dimanchebal` = '.($res['dimanchebal'] == true ? 1 : 0).',
                    `last_updated` = '.time().'
                    WHERE postcode = "'.pSQL($a->postcode).'" && country = "'.pSQL($c->iso_code).'"');
            }
        } else {
            return $cache[0];
        }

        return $res;
    }


    /** CARRIER-RELATED FUNCTIONS **/
    public function getOrderShippingCost($cart, $shipping_cost)
    {
        $productCode = 1;
        $classicAvailable = true;
        $relaisAvailable = true;
        $calculatedProducts = null;

        if (!self::isChrono($this->id_carrier)) {
            return $shipping_cost;
        }

        if ($cart->id_address_delivery == 0) {
            return $shipping_cost;
        } // CASE NOT LOGGED IN

        $a = new Address($cart->id_address_delivery);
        $c = new Country($a->id_country);

        $carrier = new Carrier($this->id_carrier);

        foreach ($cart->getProducts() as $p) {
            // check if no product > 20 kg
            if ($p['weight'] * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') > 20) {
                $relaisAvailable = false;
            }

            if ($p['weight'] * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') > 30) {
                $classicAvailable = false;
                break;
            }
        }

        if (!$classicAvailable) {
            return false;
        }


        // CALCULATE PRODUCTS
        $calculatedProducts = self::calculateProducts($cart);

        switch ($carrier->id_reference) {
            case Configuration::get('CHRONOPOST_CHRONORELAIS_ID'):
                $productCode = self::$carriers_definitions['CHRONORELAIS']['product_code'];
                if ($c->iso_code != 'FR' && $c->iso_code != 'FX' && $c->iso_code != 'MC') {
                    return false;
                }

                if (!$relaisAvailable) {
                    return false;
                }
                break;

            case Configuration::get('CHRONOPOST_RELAISEUROPE_ID'):
                $productCode = self::$carriers_definitions['RELAISEUROPE']['product_code'];

                if (!$relaisAvailable) {
                    return false;
                }
                if ($calculatedProducts['relaiseurope'] == false) {
                    return false;
                }
                break;

            case Configuration::get('CHRONOPOST_RELAISDOM_ID'):
                $productCode = self::$carriers_definitions['RELAISDOM']['product_code'];
                if (!$relaisAvailable) {
                    return false;
                }
                if ($calculatedProducts['relaisdom'] == false) {
                    return false;
                }
                break;

            case Configuration::get('CHRONOPOST_CHRONO13_ID'):
                $productCode = self::$carriers_definitions['CHRONO13']['product_code'];
                if ($c->iso_code != 'FR' && $c->iso_code != 'FX') {
                    return false;
                }
                break;

            case Configuration::get('CHRONOPOST_CHRONO10_ID'):
                if ($c->iso_code != 'FR' && $c->iso_code != 'FX' && $c->iso_code != 'MC') {
                    return false;
                }
                if ($calculatedProducts['chrono10'] == false) {
                    return false;
                }
                $productCode = self::$carriers_definitions['CHRONO10']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_CHRONO18_ID'):
                if ($c->iso_code != 'FR' && $c->iso_code != 'FX' && $c->iso_code != 'MC') {
                    return false;
                }
                if ($calculatedProducts['chrono18'] == false) {
                    return false;
                }
                $productCode = self::$carriers_definitions['CHRONO18']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_CHRONORDV_ID'):
                if ($c->iso_code != 'FR' && $c->iso_code != 'FX') {
                    return false;
                }

                if ($calculatedProducts['rdv'] == false) {
                    return false;
                }

                return Chronopost::getRDVCost($cart->id, null, $shipping_cost);

            case Configuration::get('CHRONOPOST_CHRONOEXPRESS_ID'):
                if ($c->iso_code == 'FR' || $c->iso_code == 'FX' || $c->iso_code == 'MC') {
                    return false;
                }
                $productCode = self::$carriers_definitions['CHRONOEXPRESS']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_CHRONOCLASSIC_ID'):
                if ($calculatedProducts['chronoclassic'] == false) {
                    return false;
                }
                $productCode = self::$carriers_definitions['CHRONOCLASSIC']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_SAMEDAY_ID'):
                if ($calculatedProducts['sameday'] == false) {
                    return false;
                }
                $productCode = self::$carriers_definitions['SAMEDAY']['product_code'];
                break;

            case Configuration::get('CHRONOPOST_DIMANCHEBAL_ID'):
                if ($calculatedProducts['dimanchebal'] == false) {
                    return false;
                }
                $productCode = self::$carriers_definitions['DIMANCHEBAL']['product_code'];
                break;
        }

        if (Configuration::get('CHRONOPOST_QUICKCOST_ENABLED') == 0) {
            if ($c->iso_code == 'FR' && $a->postcode >= 20000 && $a->postcode < 21000) {
                return $shipping_cost + (float)Configuration::get('CHRONOPOST_CORSICA_SUPPLEMENT');
            }

            // Let's just use Prestashop's native calculations
            return $shipping_cost;
        }

        $arrcode = (($c->iso_code == 'FR' || $c->iso_code == 'FX')?$a->postcode:$c->iso_code);
        $cache = Db::getInstance()->executeS(
            'SELECT price, last_updated FROM `'._DB_PREFIX_.'chrono_quickcost_cache` '
            .'WHERE arrcode = "'.pSQL($arrcode).'" && product_code="'.pSQL($productCode).'"'
            .' && weight="'.$cart->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') .'"'
        );

        if (!empty($cache) && $cache[0]['last_updated'] + 24 * 3600 > time()) {
            // return from cache
            return $cache[0]['price'] * (1 + Configuration::get('CHRONOPOST_QUICKCOST_SUPPLEMENT') / 100);
        }

        include_once(dirname(__FILE__).'/libraries/QuickcostServiceWSService.php');
        $ws = new QuickcostServiceWSService();
        $qc = new quickCost();
        $qc->accountNumber = Configuration::get('CHRONOPOST_GENERAL_ACCOUNT');
        $qc->password = Configuration::get('CHRONOPOST_GENERAL_PASSWORD');
        $qc->depCode = Configuration::get('CHRONOPOST_SHIPPER_ZIPCODE');
        $qc->arrCode = $arrcode;
        $qc->weight = $cart->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF');
        if ($qc->weight == 0) {
            $qc->weight = 0.1;
        } // 0 yeilds an error

        $qc->productCode = $productCode;

        $qc->type = 'M';

        try {
            $res = $ws->quickCost($qc);
        } catch (Exception $e) {
            return $shipping_cost;
        }
        if ($res->return->amountTTC != 0) {
            if (empty($cache)) {
                DB::getInstance()->query('INSERT INTO '._DB_PREFIX_.'chrono_quickcost_cache (product_code, arrcode,
                 weight, price, last_updated) VALUES (
                        "'.pSQL($productCode).'",
                        "'.pSQL($arrcode).'",
                        "'.(float)$cart->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') .'",
                        "'.(float)$res->return->amountTTC.'",
                        "'.time().'")
                ');
            } else {
                DB::getInstance()->query('UPDATE '._DB_PREFIX_.'chrono_quickcost_cache
                    SET price="'.(float)$res->return->amount.'", last_updated='.time().'
                    WHERE arrcode="'.pSQL($arrcode).'"
                    and product_code="'.pSQL($productCode).'"
                    AND weight="'.(float)$cart->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF').'"
                ');
            }

            return $res->return->amountTTC * (1 + Configuration::get('CHRONOPOST_QUICKCOST_SUPPLEMENT') / 100);
        }
        if ($res->return->amount != 0) {
            return $res->return->amount * (1 + Configuration::get('CHRONOPOST_QUICKCOST_SUPPLEMENT') / 100);
        }


        return $shipping_cost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return $this->getOrderShippingCost($params, 0);
    }

    /** ADMINISTRATION **/
    private function generateChronoForm($prefix)
    {
        $prefix = Tools::strtolower($prefix);
        $var_name = Tools::strtoupper($prefix);
        $vars = array(
            'civility',
            'name',
            'name2',
            'address',
            'address2',
            'zipcode',
            'city',
            'contactname',
            'email',
            'phone',
            'mobile',
            'country'
        );
        $smarty = array();
        $smarty['prefix'] = $prefix;

        foreach ($vars as $var) {
            $smarty[$var] = Configuration::get('CHRONOPOST_'.$var_name.'_'.Tools::strtoupper($var));
        }

        $this->context->smarty->assign($smarty);
        return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/contact.tpl');
    }

    private function dayField($fieldName, $default = 0, $group_name = 'saturday')
    {
        $selected = Configuration::get('CHRONOPOST_'.Tools::strtoupper($group_name).'_'.Tools::strtoupper($fieldName));
        if ($selected === false) {
            $selected = $default;
        }

        $this->context->smarty->assign(
            array(
                'selected' => $selected,
                'field_name' => $fieldName,
                'group_name' => $group_name
            )
        );

        return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/days.tpl');
    }

    private function hourField($fieldName, $default = 0, $group_name = 'saturday')
    {
        $selected = Configuration::get('CHRONOPOST_'.Tools::strtoupper($group_name).'_'.Tools::strtoupper($fieldName));
        if ($selected === false) {
            $selected = $default;
        }

        // Smarty is so painful
        $this->context->smarty->assign(
            array(
                'selected' => $selected,
                'field_name' => $fieldName,
                'group_name' => $group_name
            )
        );

        return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/hours.tpl');
    }


    private function minuteField($fieldName, $default = 0, $group_name = 'saturday')
    {
        $selected = Configuration::get('CHRONOPOST_'.Tools::strtoupper($group_name).'_'.Tools::strtoupper($fieldName));
        if ($selected === false) {
            $selected = $default;
        }

        // Can't stop the pain
        $this->context->smarty->assign(
            array(
                'selected' => $selected,
                'field_name' => $fieldName,
                'group_name' => $group_name
            )
        );

        return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/minutes.tpl');
    }

    private function carrierForm($code)
    {
        $carriers = Carrier::getCarriers($this->context->language->id);
        $selected = Configuration::get('CHRONOPOST_'.Tools::strtoupper($code).'_ID');

        $this->context->smarty->assign(
            array(
                'carriers' => $carriers,
                'selected' => $selected,
                'code' => $code
            )
        );

        return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/carrier.tpl');
    }

    private function postValidation()
    {
        return true;
    }

    private function postProcess()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        DB::getInstance()->Execute('TRUNCATE TABLE '._DB_PREFIX_.'chrono_calculateproducts_cache2');
        if (Tools::getValue('createnewcarrier')=="") {
            foreach (Tools::getValue('chronoparams') as $prefix => $var) {
                foreach ($var as $varname => $value) {
                    Configuration::updateValue('CHRONOPOST_'.Tools::strtoupper($prefix).'_'.
                        Tools::strtoupper($varname), $value);
                }
            }
        }
        return true;
    }

    public function getContent()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $html = '';
        if (Tools::isSubmit('submitChronoConfig')) {
            if ($this->postValidation() && $this->postProcess()) {
                $html .= Module::displayConfirmation($this->l('Settings updated.'));
            }
        }
        return $html.$this->displayForm();
    }

    public function displayForm()
    {
        $printMode = array(
            'PDF' => $this->l('PDF file'),
            'THE' => $this->l('Thermal printer'),
            'SPD' => $this->l('PDF without delivery proof')
            //'SER' =>'Imprimante thermique Chronopost'
        );

        $unitCoef = array(
            'KG' => '1',
            'G' => '0.001'
        );

        $carriers_tpl=array();
        foreach (array_keys(self::$carriers_definitions) as $code) {
            $carriers_tpl[$code] = $this->carrierForm($code);
        }

        // smarty-chain !
        $this->context->smarty->assign(
            array(
                'post_uri' => $_SERVER['REQUEST_URI'],
                'chronopost_secret' => Configuration::get('CHRONOPOST_SECRET'),
                'print_modes' => $printMode,
                'selected_print_mode' => Configuration::get('CHRONOPOST_GENERAL_PRINTMODE'),
                'weights' => $unitCoef,
                'selected_weight' => Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF'),
                'module_dir' => _MODULE_DIR_,
                'general_account' => Configuration::get('CHRONOPOST_GENERAL_ACCOUNT'),
                'general_subaccount' => Configuration::get('CHRONOPOST_GENERAL_SUBACCOUNT'),
                'general_password' => Configuration::get('CHRONOPOST_GENERAL_PASSWORD'),
                'saturday_active' => Configuration::get('CHRONOPOST_SATURDAY_ACTIVE'),
                'saturday_checked' => Configuration::get('CHRONOPOST_SATURDAY_CHECKED'),
                'day_start' => $this->dayField('day_start', 4),
                'hour_start' => $this->hourField('hour_start', 18),
                'minute_start' => $this->minuteField('minute_start'),
                'day_rdv_on' => $this->dayField('day_on', 0, 'rdv'),
                'hour_rdv_on' => $this->hourField('hour_on', 0, 'rdv'),
                'minute_rdv_on' => $this->minuteField('minute_on', 0, 'rdv'),
                'day_rdv_close_start' => $this->dayField('day_close_st', 0, 'rdv'),
                'hour_rdv_close_start' => $this->hourField('hour_close_st', 0, 'rdv'),
                'minute_rdv_close_start' => $this->minuteField('minute_close_st', 0, 'rdv'),
                'day_rdv_close_end' => $this->dayField('day_close_end', 0, 'rdv'),
                'hour_rdv_close_end' => $this->hourField('hour_close_end', 0, 'rdv'),
                'minute_rdv_close_end' => $this->minuteField('minute_close_end', 0, 'rdv'),
                'day_end' => $this->dayField('day_end', 5),
                'hour_end' => $this->hourField('hour_end', 16),
                'minute_end' => $this->minuteField('minute_end'),
                'carriers_tpl' => $carriers_tpl,
                'rdv_delay' => Configuration::get('CHRONOPOST_RDV_DELAY'),
                'map_enabled' => Configuration::get('CHRONOPOST_MAP_ENABLED'),
                'map_apikey' => Configuration::get('CHRONOPOST_MAP_APIKEY'),
                'corsica_supplement' => Configuration::get('CHRONOPOST_CORSICA_SUPPLEMENT'),
                'quickcost_enabled' => Configuration::get('CHRONOPOST_QUICKCOST_ENABLED'),
                'quickcost_supplement' => Configuration::get('CHRONOPOST_QUICKCOST_SUPPLEMENT'),
                'advalorem_enabled' => Configuration::get('CHRONOPOST_ADVALOREM_ENABLED'),
                'advalorem_minvalue' => Configuration::get('CHRONOPOST_ADVALOREM_MINVALUE'),
                'bal_enabled' => Configuration::get('CHRONOPOST_BAL_ENABLED'),
                'rdv_price1' => Configuration::get('CHRONOPOST_RDV_PRICE1'),
                'rdv_price2' => Configuration::get('CHRONOPOST_RDV_PRICE2'),
                'rdv_price3' => Configuration::get('CHRONOPOST_RDV_PRICE3'),
                'rdv_price4' => Configuration::get('CHRONOPOST_RDV_PRICE4'),
                'rdv_state1' => Configuration::get('CHRONOPOST_RDV_STATE1'),
                'rdv_state2' => Configuration::get('CHRONOPOST_RDV_STATE2'),
                'rdv_state3' => Configuration::get('CHRONOPOST_RDV_STATE3'),
                'rdv_state4' => Configuration::get('CHRONOPOST_RDV_STATE4'),
                'shipper_form' => $this->generateChronoForm('shipper'),
                'customer_form' => $this->generateChronoForm('customer'),
                'return_form' => $this->generateChronoForm('return'),
                'return_default' => Configuration::get('CHRONOPOST_RETURN_DEFAULT')
            )
        );

        return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/config.tpl');
    }

    /**
     * For retro-compatibility
     * @param $order
     *
     * @return int
     */
    public static function getIdOrderCarrier($order)
    {
        if (version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
            return $order->getIdOrderCarrier();
        } else {
            return (int)Db::getInstance()->getValue('
                SELECT `id_order_carrier`
                FROM `'._DB_PREFIX_.'order_carrier`
                WHERE `id_order` = '.(int)$order->id);
        }
    }

    public static function getIdOrderInvoice($order)
    {
        return (int)Db::getInstance()->getValue('
            SELECT `id_order_invoice`
            FROM `'._DB_PREFIX_.'order_invoice`
            WHERE `id_order` = '.(int)$order->id);
    }

    /**
     * @param int $orderId
     *
     * @return array An array with all tracking numbers
     * @throws PrestaShopDatabaseException
     */
    public static function getAllTrackingNumbers($orderId)
    {
        $LTHistory = array();
        $LTRequest = DB::getInstance()->executeS(
            'SELECT lt FROM '
            ._DB_PREFIX_.'chrono_lt_history WHERE id_order = ' . (int) $orderId . ' AND `cancelled` IS NULL'
        );
        foreach ($LTRequest as $LT) {
            $LTHistory[] = $LT['lt'];
        }
        return $LTHistory;
    }
}
