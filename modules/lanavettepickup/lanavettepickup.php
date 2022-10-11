<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class LaNavettePickup extends CarrierModule
{
    /**
     * Module link in BO
     * @var String
     */
    private $_link;

    /** @var bool $dev : Define if we are in dev mode */
    public static $dev = false;

    private $ws = null;

    public $media_version = '';

    private $google_maps_key = 'AIzaSyAahkHYmToXDC5MCDnWHbGZbGPolpxPajM';

    private $config = array(
        'name'                 => 'La Navette Pickup',
        'carrier_name'         => 'La Navette Pickup',
        'id_tax_rules_group'   => 0,
        'url'                  => 'https://lanavette.pickup.fr/Suivi/@',
        'active'               => true,
        'deleted'              => 0,
        'shipping_handling'    => false,
        'range_behavior'       => 0,
        'is_module'            => true,
        'delay'                => array(
            'fr' => 'Livrez vos colis en relais, en 48h',
            'en' => 'Deliver your parcel to any Pickup parcelshop within 48h!'),
        'id_zone'              => 1,
        'shipping_external'    => true,
        'external_module_name' => 'lanavettepickup',
        'need_range'           => true,
    );

    /**
     * Constructor of module
     */
    public function __construct()
    {
        $this->name       = 'lanavettepickup';
        $this->tab        = 'shipping_logistics';
        $this->version    = '2.1.2';
        $this->author     = 'La Navette Pickup';
        $this->module_key = '7a53e916a7559845cf9893b4330ac47e';
        $this->bootstrap  = true;

        parent::__construct();

        $this->includeFiles();

        $this->displayName = $this->l('La Navette Pickup');
        $this->description = $this->l('Deliver your parcel to any Pickup parcelshop within 48h');

        if (Configuration::get('LNP2_DROP_OFF_UNAVAILABLE')) {
            $this->warning = $this->l('URGENT: Your drop off point is not available');
        }

        if (version_compare(_PS_VERSION_, '1.6.1', '>=')) {
            $this->media_version = '?ver='.$this->version;
        }
    }

    private function includeFiles()
    {
        $path_original = $this->getLocalPath().'classes/';
        $path          = $path_original;
        foreach (scandir($path) as $class) {
            if ($class != "index.php" && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                if ($class_name != 'index' && !class_exists($class_name)) {
                    require_once($path.$class_name.'.php');
                }
            }
        }

        $path = $path_original.'helper/';
        foreach (scandir($path) as $class) {
            if ($class != "index.php" && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                if ($class_name != 'index' && !class_exists($class_name)) {
                    require_once($path.$class_name.'.php');
                }
            }
        }

        $path = $path_original.'WebService/';
        foreach (scandir($path) as $class) {
            if ($class != "index.php" && is_file($path.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                if ($class_name != 'index' && !class_exists($class_name)) {
                    require_once($path.$class_name.'.php');
                }
            }
        }
    }


    ############################################################################################################
    # Install / Upgrade / Uninstall
    ############################################################################################################

    /**
     * Module install
     * @return boolean if install was successfull
     */
    public function install()
    {
        # Install default
        if (!parent::install()) {
            return false;
        }

        # Install DataBase
        if (!$this->installSQL()) {
            return false;
        }

        # Install tabs
        if (!$this->installTabs()) {
            return false;
        }

        # Create Carrier
        if (!$this->createCarrier()) {
            return false;
        }

        # Registration hook
        if (!$this->registrationHook()) {
            return false;
        }

        if (!$this->createDefaultConfig()) {
            return false;
        }

        return true;
    }

    /**
     * Module uninstall
     * @return boolean if uninstall was successfull
     */
    public function uninstall()
    {
        $pickup_id      = (int)Configuration::get('LNP2_CARRIER_ID');
        $pickup_carrier = new Carrier($pickup_id);

        foreach (array(
                     'LNP2_CARRIER_ID',
                     'LNP2_EMAIL_SENT_LAST_DATE',
                     'LNP2_DROP_OFF_UNAVAILABLE') as $var) {
            Configuration::deleteByName($var);
        }

        # Uninstall DataBase
        if (!$this->uninstallSQL()) {
            return false;
        }

        # Uninstall Hooks
        if (!$this->unregisterHooks()) {
            return false;
        }

        # Delete tabs
        if (!$this->uninstallTabs()) {
            return false;
        }

        # Uninstall default
        if (!parent::uninstall()) {
            return false;
        }

        # If pickup carrier is default set other one as default
        if (Configuration::get('PS_CARRIER_DEFAULT') == (int)$pickup_carrier->id) {
            $carriers_d = Carrier::getCarriers($this->context->language->id);
            foreach ($carriers_d as $carrier_d) {
                if ($carrier_d['active'] && !$carrier_d['deleted'] && ($carrier_d['name'] != $this->config['name'])) {
                    Configuration::updateValue('PS_CARRIER_DEFAULT', $carrier_d['id_carrier']);
                }
            }
        }

        # Save carrier id in list
//		Configuration::updateValue('LNP2_CARRIER_ID_HIST', Configuration::get('LNP2_CARRIER_ID_HIST').'|'.(int)$pickup_carrier->id);

        # REMOVE CARRIER
        # Delete So Carrier
        $pickup_carrier->deleted = 1;
        if (!$pickup_carrier->update()) {
            return false;
        }

        return true;
    }

    /**
     * Activate current module.
     *
     * @param bool $forceAll If true, enable module for all shop
     * @return bool
     */
    public function enable($forceAll = false)
    {
        if (!parent::enable($forceAll)) {
            return false;
        }

        $pickup_id      = (int)Configuration::get('LNP2_CARRIER_ID');
        $pickup_carrier = new Carrier($pickup_id);
        if (!$pickup_carrier->active) {
            $pickup_carrier->active = true;
            $pickup_carrier->update();
        }

        return true;
    }

    /**
     * Desactivate current module.
     *
     * @param bool $forceAll If true, disable module for all shop
     */
    public function disable($forceAll = false)
    {
        parent::disable($forceAll);

        $pickup_id      = (int)Configuration::get('LNP2_CARRIER_ID');
        $pickup_carrier = new Carrier($pickup_id);
        if ($pickup_carrier->name && $pickup_carrier->active) {
            $pickup_carrier->active = false;
            $pickup_carrier->update();
        }

    }


    ############################################################################################################
    # Tabs
    ############################################################################################################

    /**
     * Initialisation to install / uninstall
     */
    private function installTabs()
    {

        $menu_id = 10;

        # Install All Tabs directly via controller's install function
        $controllers = scandir($this->getLocalPath().'/controllers/admin');
        foreach ($controllers as $controller) {
            if ($controller != 'index.php' && is_file($this->getLocalPath().'/controllers/admin/'.$controller)) {
                require_once($this->getLocalPath().'/controllers/admin/'.$controller);
                $controller_name = Tools::substr($controller, 0, -4);
                # Check if class_name is an existing Class or not
                if (class_exists($controller_name)) {
                    if (method_exists($controller_name, 'install')) {
                        if (!call_user_func(array($controller_name, 'install'), $menu_id, $this->name)) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Delete hooks
     * @return  boolean if successfull
     */
    public function unregisterHooks()
    {
        if (!$this->unregisterHook('displayBeforeCarrier')
            || !$this->unregisterHook('displayCarrierList')
            || !$this->unregisterHook('updateCarrier')
            || !$this->unregisterHook('actionValidateOrder')
            || !$this->unregisterHook('actionOrderStatusUpdate')
            || !$this->unregisterHook('displayAdminOrder')
            || !$this->unregisterHook('displayBackOfficeTop')
            || !$this->unregisterHook('actionCartSave')
            || !$this->unregisterHook('displayPaymentTop')
            || !$this->unregisterHook('displayHeader')
        ) {
            return false;
        }

        return true;
    }


    /**
     * Delete tab
     * @return  boolean if successfull
     */
    public function uninstallTabs()
    {
        return Lnp2TotAdminTabHelper::deleteAdminTabs($this->name);
    }

    ############################################################################################################
    # SQL
    ############################################################################################################

    /**
     * Install DataBase table
     * @return boolean if install was successfull
     */
    private function installSQL()
    {
        # Install All Object Model SQL via install function
        $classes = scandir($this->getLocalPath().'/classes');
        foreach ($classes as $class) {
            if ($class != 'index.php' && is_file($this->getLocalPath().'/classes/'.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                # Check if class_name is an existing Class or not
                if (class_exists($class_name)) {
                    if (method_exists($class_name, 'install')) {
                        if (!call_user_func(array($class_name, 'install'))) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Uninstall DataBase table
     * @return boolean if install was successfull
     */
    private function uninstallSQL()
    {
        # Uninstall All Object Model SQL via install function
        $classes = scandir($this->getLocalPath().'/classes');
        foreach ($classes as $class) {
            if ($class != 'index.php' && is_file($this->getLocalPath().'/classes/'.$class)) {
                $class_name = Tools::substr($class, 0, -4);
                # Check if class_name is an existing Class or not
                if (class_exists($class_name)) {
                    if (method_exists($class_name, 'uninstall')) {
                        if (!call_user_func(array($class_name, 'uninstall'))) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Create carrier
     * @return boolean if creation was successfull
     */
    public function createCarrier()
    {
        $is_PS16 = (version_compare(_PS_VERSION_, '1.6.0', '>=') === true);

        $carrier                       = new Carrier();
        $carrier->name                 = $this->config['carrier_name'];
        $carrier->id_tax_rules_group   = $this->config['id_tax_rules_group'];
        $carrier->id_zone              = $this->config['id_zone'];
        $carrier->url                  = $this->config['url'];
        $carrier->active               = $this->config['active'];
        $carrier->deleted              = $this->config['deleted'];
        $carrier->delay                = $this->config['delay'];
        $carrier->shipping_handling    = $this->config['shipping_handling'];
        $carrier->range_behavior       = $this->config['range_behavior'];
        $carrier->is_module            = $this->config['is_module'];
        $carrier->shipping_external    = $this->config['shipping_external'];
        $carrier->external_module_name = $this->config['external_module_name'];
        $carrier->need_range           = $this->config['need_range'];

        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $carrier->delay[$language['id_lang']] = $this->config['delay'][$language['iso_code']];
            }
            if ($language['iso_code'] == 'en') {
                $carrier->delay[$language['id_lang']] = $this->config['delay'][$language['iso_code']];
            }
        }

        if ($carrier->add()) {
            if (Shop::isFeatureActive()) {
                Shop::setContext(Shop::CONTEXT_ALL);
            }

            Configuration::updateValue('LNP2_CARRIER_ID', (int)$carrier->id);
            $groups = Group::getGroups(true);

            foreach ($groups as $group) {
                Db::getInstance()->execute(
                    'INSERT INTO '._DB_PREFIX_.'carrier_group VALUE (\''.(int)$carrier->id.'\',\''.(int)$group['id_group'].'\')'
                );
            }


            $carrier->addZone($this->config['id_zone']);

            $price_list = array(
                array('delimiter1' => '0', 'delimiter2' => '0.5', 'price' => '0',),
                array('delimiter1' => '0.5', 'delimiter2' => '1', 'price' => '0',),
                array('delimiter1' => '1', 'delimiter2' => '2', 'price' => '0',),
                array('delimiter1' => '2', 'delimiter2' => '3', 'price' => '0',),
                array('delimiter1' => '3', 'delimiter2' => '4', 'price' => '0',),
                array('delimiter1' => '4', 'delimiter2' => '5', 'price' => '0',),
                array('delimiter1' => '5', 'delimiter2' => '6', 'price' => '0',),
                array('delimiter1' => '6', 'delimiter2' => '7', 'price' => '0',),
                array('delimiter1' => '7', 'delimiter2' => '8', 'price' => '0',),
                array('delimiter1' => '8', 'delimiter2' => '9', 'price' => '0',),
                array('delimiter1' => '9', 'delimiter2' => '10', 'price' => '0',),
                array('delimiter1' => '10', 'delimiter2' => '11', 'price' => '0',),
                array('delimiter1' => '11', 'delimiter2' => '12', 'price' => '0',),
                array('delimiter1' => '12', 'delimiter2' => '13', 'price' => '0',),
                array('delimiter1' => '13', 'delimiter2' => '14', 'price' => '0',),
                array('delimiter1' => '14', 'delimiter2' => '15', 'price' => '0',),
                array('delimiter1' => '15', 'delimiter2' => '16', 'price' => '0',),
                array('delimiter1' => '16', 'delimiter2' => '17', 'price' => '0',),
                array('delimiter1' => '17', 'delimiter2' => '18', 'price' => '0',),
                array('delimiter1' => '18', 'delimiter2' => '19', 'price' => '0',),
                array('delimiter1' => '19', 'delimiter2' => '20', 'price' => '0',),
            );

            foreach ($price_list as $range) {
                $range_weight             = new RangeWeight();
                $range_weight->id_carrier = $carrier->id;
                $range_weight->delimiter1 = $range['delimiter1'];
                $range_weight->delimiter2 = $range['delimiter2'];
                $range_weight->add();

                $sql = 'UPDATE `'._DB_PREFIX_.'delivery` SET `price` = '.pSQL($range['price']).',
                    `id_shop` = null,
                    `id_shop_group` = null,
                    `id_range_price` = null
                    WHERE `id_carrier` = '.(int)$carrier->id.'
                    AND `id_range_weight` = '.(int)$range_weight->id.'
                    AND `id_zone` = '.(int)$this->config['id_zone'];
                Db::getInstance()->execute($sql);
            }

            Configuration::updateValue(
                'LNP2_CARRIER_ID_HIST',
                Configuration::get('LNP2_CARRIER_ID_HIST').'|'.(int)$carrier->id
            );

            # copy logo
            if (!copy(dirname(__FILE__).'/views/img/logo-carrier.png', _PS_SHIP_IMG_DIR_.'/'.$carrier->id.'.jpg')) {
                return false;
            }

            return true;
        }

        return false;
    }

    ############################################################################################################
    # Hook
    ############################################################################################################

    /**
     * [registrationHook description]
     * @return [type] [description]
     */
    private function registrationHook()
    {

        if (!$this->registerHook('displayBeforeCarrier')
            || !$this->registerHook('displayCarrierList')
            || !$this->registerHook('updateCarrier')
            || !$this->registerHook('actionValidateOrder')
            || !$this->registerHook('actionOrderStatusUpdate')
            || !$this->registerHook('displayAdminOrder')
            || !$this->registerHook('displayBackOfficeTop')
            || !$this->registerHook('actionCartSave')
            || !$this->registerHook('displayPaymentTop')
            || !$this->registerHook('displayHeader')
        ) {
            return false;
        }

        return true;
    }

    private function createDefaultConfig()
    {

        $config = Configuration::getMultiple(array('LNP2_PREPARATION_ORDER_STATE', 'LNP2_SHIPPED_ORDER_STATE'));

        if (!$config['LNP2_PREPARATION_ORDER_STATE']) {
            Configuration::updateValue('LNP2_PREPARATION_ORDER_STATE', Db::getInstance()->getValue('SELECT `id_order_state`
            FROM `'._DB_PREFIX_.'order_state`
            WHERE `hidden` = 0
            AND `logable` = 1
            AND `delivery` = 1
            AND `shipped` = 0
            AND `paid` = 1
            AND `deleted` = 0'));
        }

        if (!$config['LNP2_SHIPPED_ORDER_STATE']) {
            Configuration::updateValue('LNP2_SHIPPED_ORDER_STATE', Db::getInstance()->getValue('SELECT `id_order_state`
            FROM `'._DB_PREFIX_.'order_state`
            WHERE `hidden` = 0
            AND `logable` = 1
            AND `delivery` = 1
            AND `shipped` = 1
            AND `paid` = 1
            AND `deleted` = 0'));
        }

        Configuration::updateValue('LNP2_SECURITY_TOKEN', Tools::passwdGen(30));

        return true;
    }

    /**
     * ensure the webservice is up and running with a mock call
     *
     */
    public function isWebServiceAvailable()
    {
        $ws  = $this->getWs();
        $res = $ws->hasToken();

        if (!$res) {
            return false;
        }

        return true;
    }

    public function hookDisplayBeforeCarrier($params)
    {
        /** @var Cart $cart */
        $cart = $params['cart'];
        $this->myLog($cart->id, 'hookDisplayBeforeCarrier : start');
        $pickup_carrier_id = (int)Configuration::get('LNP2_CARRIER_ID');
        $this->myLog($cart->id, 'hookDisplayBeforeCarrier : Pickup carrier is : '.$this->config['carrier_name'].'('.$pickup_carrier_id.')');

        foreach ($params['carriers'] as $carrier) {
            $carrier_id = Cart::desintifier($carrier['id_carrier'], '');

            $this->myLog($cart->id, 'hookDisplayBeforeCarrier : carrier : '.$carrier['name'].' ('.$carrier_id.')');
            if ($carrier_id == $pickup_carrier_id) {
                if ($this->isWebServiceAvailable()) {
                    $this->context->cookie->__set('lnp2_available', true);
                    $this->myLog($cart->id, 'hookDisplayBeforeCarrier : webservice available');
                } else {
                    $this->context->cookie->__set('lnp2_available', false);
                    $this->myLog($cart->id, 'hookDisplayBeforeCarrier : webservice not available');
                }
                $this->context->cookie->__set('lnp2_free', ($carrier['price'] == 0));

                return;
            }
        }
        $this->context->cookie->__set('lnp2_available', false);
        $this->myLog($cart->id, 'hookDisplayBeforeCarrier : lnp2 carrier not found for this product/zone.');
    }

    /**
     * returns true if the module is configured, the order can fit the pouch, the products are not excluded and the
     * delivery address is in France. Returns false otherwise
     * @param Cart $cart
     * @return bool
     */
    private function isCartEligible($cart)
    {

        $config = Configuration::getMultiple(array('LNP2_CARRIER_ID', 'LNP2_DROP_OFF_SITE', 'LNP2_DROP_OFF_UNAVAILABLE'));

        if (!$config['LNP2_DROP_OFF_SITE']
            || !Tools::jsonDecode(str_replace('|', '"', $config['LNP2_DROP_OFF_SITE']))
        ) {
            $this->myLog($cart->id, 'isCartEligible : No drop off point configured');

            return false; # no drop off point configured
        }

//        if ($config['LNP2_DROP_OFF_UNAVAILABLE']) {
//            $this->myLog($cart->id, 'isCartEligible : Drop off point unavailable');
//
//            return false; #
//        }

        # no delivery address
        if (!$cart->id_address_delivery) {
            $this->myLog($cart->id, 'isCartEligible : No delivery address');

            return false;
        }

        # check that address is in France
        $delivery_address = new Address($cart->id_address_delivery);
        $country_iso      = Country::getIsoById($delivery_address->id_country);
        if (!$this->isCountryCodeAvailable($country_iso)) {
            $this->myLog($cart->id, 'isCartEligible : Country not in available list');

            return false;
        }

        $this->myLog($cart->id, 'isCartEligible : Instanciate pickup carrier : '.$config['LNP2_CARRIER_ID']);
        $carrier = new Carrier((int)$config['LNP2_CARRIER_ID']);
        //$this->myLog($cart->id, 'isCartEligible : Pickup carrier is : '.print_r($carrier, true));

        # we ensure here that the order can be shipped via la Navette Pickup

        # weight
        # @todo : gérer avec le poids de prestashop
        $weight = $cart->getTotalWeight();
        if ($carrier->max_weight > 0 && $weight > $carrier->max_weight) {
            $this->myLog($cart->id, 'isCartEligible : Cart above limit : $weight ('.$weight.') > $carrier->max_weight ('.$carrier->max_weight.')');

            return false;
        }

        # order is eligible
        $this->myLog($cart->id, 'isCartEligible : all fine, cart is eligible');

        return true;

    }

    public function hookDisplayHeader($params)
    {
        if (!isset($this->context->controller->php_self)
            || !in_array($this->context->controller->php_self, array('order', 'order-opc', 'orderopc'))
        ) {
            return;
        }

        $this->context->controller->addCSS($this->_path.'views/css/vendor/grid12.css'.$this->media_version, 'all', null, false);
        if (version_compare(_PS_VERSION_, '1.6.0', '<')) {
            $this->context->controller->addCSS($this->_path.'views/css/ps15front.css'.$this->media_version, 'all', null, false);
        }

        $this->context->controller->addCSS($this->_path.'views/css/front.css'.$this->media_version, 'all', null, false);
        $this->context->controller->addCSS($this->_path.'views/css/map.css'.$this->media_version, 'all', null, false);

        $this->context->controller->addJS($this->_path.'views/js/map.js'.$this->media_version);
    }

    /**
     * @param array $params ['address']
     * @return bool|string
     */
    public function hookDisplayCarrierList($params)
    {
        $result = '';

        $this->context->smarty->assign(array(
            'pickup_dir'            => $this->_path,
            'google_maps_key'       => $this->google_maps_key,
            'ps_order_process_type' => (1 == Configuration::get('PS_ORDER_PROCESS_TYPE')) ? true : false,
        ));
        $result .= $this->display(__FILE__, 'views/templates/hook/loadScript.tpl');

        /** @var Cart $cart */
        $cart = $params['cart'];
        $this->myLog($cart->id, 'hookDisplayCarrierList : start');
        $config            = Configuration::getMultiple(array('LNP2_CARRIER_ID', 'LNP2_LOGS', 'LNP2_SECURITY_TOKEN', 'LNP2_OPTIONS_CORSE', 'LNP2_DROP_OFF_SITE'));
        $pickup_carrier_id = (int)$config['LNP2_CARRIER_ID'];

        $package_shipping_cost = $cart->getPackageShippingCost($pickup_carrier_id);
        $this->myLog($cart->id, 'hookDisplayCarrierList :  package SHIPPING COST = '.$package_shipping_cost);

        $is_free_shipping = ($package_shipping_cost == 0); # determine pickup shipping costs according to carriers preferences
        if ($is_free_shipping) {
            $this->myLog($cart->id, 'hookDisplayCarrierList :  Current cart TOTAL SHIPPING is FREE');
        } else {
            $this->myLog($cart->id, 'hookDisplayCarrierList :  Current cart TOTAL SHIPPING is NOT FREE');
        }

        $pickup_carrier = new Carrier($pickup_carrier_id);
        $lnp2_free      = $pickup_carrier->is_free; # determine if pickup is free or not whatever the cart shipping cost is
        if ($lnp2_free) {
            $this->myLog($cart->id, 'hookDisplayCarrierList : PICKUP carrier is FREE');
        } else {
            $this->myLog($cart->id, 'hookDisplayCarrierList : PICKUP carrier is NOT FREE');
        }


        if (!$is_free_shipping && !$lnp2_free) {
            # cart rules have to checked
            $is_cart_rule_free_shipping = false;
            $cart_rules                 = $cart->getCartRules(CartRule::FILTER_ACTION_SHIPPING); # check if cart rules for free shipping exist
            foreach ($cart_rules as $key => $cart_rule) {
                $id_cart_rule = $cart_rule['id_discount'];
                $this->myLog($cart->id, 'hookActionCarrierProcess > a CART RULE for Free Shipping is found, $id_cart_rule = '.$id_cart_rule);
                $is_cart_rule_free_shipping = true;
            }
        }

        $is_PS16 = (version_compare(_PS_VERSION_, '1.6.0', '>=') === true);

        $data = array(
            'pickup_carrier_id' => $pickup_carrier_id,
            'is_PS16'           => $is_PS16,
        );

        $lnp2_available = $this->context->cookie->lnp2_available;
        if ($lnp2_available) {
            $this->myLog($cart->id, 'hookDisplayCarrierList : PICKUP is available');
        } else {
            $this->myLog($cart->id, 'hookDisplayCarrierList : PICKUP is NOT available');
        }

        $cart_eligible = $this->isCartEligible($cart);
        if ($cart_eligible) {
            $this->myLog($cart->id, 'hookDisplayCarrierList : CART is eligible');
        } else {
            $this->myLog($cart->id, 'hookDisplayCarrierList : CART is NOT eligible');
        }


        if ($is_free_shipping && (!$this->context->cookie->lnp2_available || !$this->isCartEligible($cart))) {
            # free shipping: we test the cart
            $this->myLog($cart->id, 'hookDisplayCarrierList : hide pickup carrier');
            $data['hide_pickup_carrier'] = true;
        } else {
            $this->myLog($cart->id, 'hookDisplayCarrierList : pickup carrier is not hidden');
            $delivery_address = new Address($cart->id_address_delivery);

            $delivery_address_str = $delivery_address->address1;
            if ($delivery_address->address2) {
                $delivery_address_str .= ' '.$delivery_address->address2;
            }
            $delivery_address_str .= ' '.$delivery_address->postcode.' '.$delivery_address->city;

            # don't do anything if Pickup is not an available carrier
            if (!$this->context->cookie->lnp2_available) {
                $this->myLog($cart->id, 'hookDisplayCarrierList : pickup carrier is not available');

                return $result;
            }

            if (!$config['LNP2_DROP_OFF_SITE']
                || !($drop_off_pudo = Tools::jsonDecode(str_replace('|', '"', $config['LNP2_DROP_OFF_SITE']), true))
            ) {
                $this->myLog($cart->id, 'hookDisplayCarrierList : no drop off point configured');

                return null;
            }

            $data = array_merge($data, array(
                'lnp2_free'        => ($lnp2_free || $is_free_shipping || $is_cart_rule_free_shipping),
                'corse_price'      => $config['LNP2_OPTIONS_CORSE'] == '' ? (float)-1 : (float)$config['LNP2_OPTIONS_CORSE'],
                'general_price'    => $package_shipping_cost,
                'lnp_corse_paid'   => (bool)(isset($this->context->cookie->lnp2_price_add) && $this->context->cookie->lnp2_price_add),
                'drop_off_pudo_id' => $drop_off_pudo['id'],
                'security_token'   => $config['LNP2_SECURITY_TOKEN'],
                'delivery_address' => str_replace("'", "\'", $delivery_address_str),
                'delivery_zip'     => $delivery_address->postcode.', France', # in case address is not recognized by Google Maps Geocoder
                'cart_id'          => $cart->id,
                'ws_ok'            => true,
            ));
        }

        $this->context->smarty->assign($data);

        $result .= $this->display(__FILE__, 'views/templates/hook/displayCarrierList.tpl');

        return $result;
    }

    public function hookDisplayPaymentTop($params)
    {
        $this->myLog($params['cart']->id, 'hookActionCarrierProcess : start');

        # store cart's pudo info to lnp_cart table
        $id_cart = $params['cart']->id;

        $pudo = Tools::jsonDecode($this->context->cookie->cart_lnp2_site, true);
        if (is_array($pudo)) {
            foreach ($pudo as $key => $value) {
                $this->myLog($params['cart']->id, 'hookActionCarrierProcess > $pudo->'.$key.' = '.$value);
            }
        } else {
            $this->myLog($params['cart']->id, 'hookActionCarrierProcess > $pudo IS NOT SET');

            return;
        }

        $pudo_address_line = $pudo['address1'];
        $this->myLog($params['cart']->id, 'hookActionCarrierProcess > $pudo_address_line = '.$pudo_address_line);

        $pudo_city_name = $pudo['city'];
        $this->myLog($params['cart']->id, 'hookActionCarrierProcess > $pudo_city_name = '.$pudo_city_name);

        $pudo_country_code = $pudo['countryCode'];
        $this->myLog($params['cart']->id, 'hookActionCarrierProcess > $pudo_country_code = '.$pudo_country_code);

        $pudo_country_name = $this->getCountryNameFromPU($pudo['countryCode']);
        $this->myLog($params['cart']->id, 'hookActionCarrierProcess > $pudo_country_name = '.$pudo_country_name);

        $pudo_name = $pudo['name'];
        $this->myLog($params['cart']->id, 'hookActionCarrierProcess > $pudo_name = '.$pudo_name);

        $pudo_id = $pudo['id'];
        $this->myLog($params['cart']->id, 'hookActionCarrierProcess > $pudo_id = '.$pudo_id);

        $pudo_zip_code = $pudo['zipCode'];
        $this->myLog($params['cart']->id, 'hookActionCarrierProcess > $pudo_zip_code = '.$pudo_zip_code);

        $shipping_cost = $this->getOrderShippingCost($params['cart'], -1);
        $this->myLog($params['cart']->id, 'hookActionCarrierProcess > $shipping_cost = '.$shipping_cost);

        # save cart in table
        $lnp_cart                    = new Lnp2Cart();
        $lnp_cart->id_cart           = $id_cart;
        $lnp_cart->pudo_address_line = (string)$pudo_address_line;
        $lnp_cart->pudo_city_name    = (string)$pudo_city_name;
        $lnp_cart->pudo_country_code = (string)$pudo_country_code;
        $lnp_cart->pudo_country_name = (string)$pudo_country_name;
        $lnp_cart->pudo_name         = (string)$pudo_name;
        $lnp_cart->pudo_id           = (string)$pudo_id;
        $lnp_cart->pudo_zip_code     = (string)$pudo_zip_code;
        $lnp_cart->shipping_cost     = (string)$shipping_cost;
        $lnp_cart->insurance         = (bool)Configuration::get('LNP2_INSURANCE');
        $lnp_cart->save();
        $this->myLog($params['cart']->id, 'hookActionCarrierProcess > cart is saved to table');

        $this->myLog($params['cart']->id, 'hookActionCarrierProcess : stop');
    }

    /**
     * Action at status update
     * Add tracking number when status = LNP2_SHIPPED_ORDER_STATE
     * @param $params
     */
    public function hookActionOrderStatusUpdate($params)
    {
        /** @var OrderState $new_os */
        $new_os = $params['newOrderStatus'];
        /** @var int $id_order */
        $id_order = $params['id_order'];

        if ($new_os->id == (int)Configuration::get('LNP2_SHIPPED_ORDER_STATE')) {
            # update order state and tracking code
            $lnp_order = Lnp2Order::getLnpOrderByOrderId($id_order);
            if ($lnp_order) {
                $shipping_number = $lnp_order->navette_code;
                $order           = new Order($id_order);
                $is_PS16         = (version_compare(_PS_VERSION_, '1.6.1', '>=') === true);

                if ($is_PS16) {
                    if ($order->getWsShippingNumber() !== $shipping_number) {
                        $order->setWsShippingNumber($shipping_number);
                    }
                } else {
                    $id_order_carrier = Db::getInstance()->getValue('
                    SELECT `id_order_carrier`
                    FROM `'._DB_PREFIX_.'order_carrier`
                    WHERE `id_order` = '.(int)$id_order);
                    if ($id_order_carrier) {
                        $order_carrier = new OrderCarrier($id_order_carrier);
                        if ($order_carrier->tracking_number != $shipping_number) {
                            $order_carrier->tracking_number = $shipping_number;
                            $order_carrier->update();
                        }
                    } else {
                        if ($order->shipping_number != $shipping_number) {
                            $order->shipping_number = $shipping_number;
                            $order->update();
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $params ['cart','order','customer','currency','orderStatus']
     * @return bool
     */
    public function hookActionValidateOrder($params)
    {
        $this->myLog($params['cart']->id, 'hookActionValidateOrder : start');
        $pickup_carrier_id      = (int)Configuration::get('LNP2_CARRIER_ID');
        $olds_pickup_carrier_id = explode('|', Configuration::get('LNP2_CARRIER_ID_HIST'));

        # ensures that LaNavettePickup is the selected carrier
        if ($pickup_carrier_id != $params['cart']->id_carrier && !in_array($params['cart']->id_carrier, $olds_pickup_carrier_id)) {
            $this->myLog($params['cart']->id, 'hookActionValidateOrder : bad carrier : '.$pickup_carrier_id);

            return false;
        }

        foreach ($params['order'] as $key => $value) {
            $this->myLog($params['cart']->id, 'hookActionValidateOrder > BEFORE UPDATE $params[\'order\']->'.$key.' = '.(($value == (string)$value) ? $value : 'object'));
        }

        # get pudo infos from last stored cart in lnp2_cart
        /** @var Lnp2Cart $lnp_cart */
        $lnp_cart = Lnp2Cart::getLnpCartByCartId((int)$params['cart']->id);

        $this->myLog($params['cart']->id, 'hookActionValidateOrder > $lnp_cart = '.$lnp_cart);

        $delivery_address = new Address($params['order']->id_address_delivery);
        if (Tools::strlen($delivery_address->firstname.' '.$delivery_address->lastname) > 32) {
            $delivery_name = Tools::strtoupper($delivery_address->firstname[0]).'. '.$delivery_address->lastname;
        } else {
            $delivery_name = $delivery_address->firstname.' '.$delivery_address->lastname;
        }
        $address = str_split($lnp_cart->pudo_address_line, 128);

        $price = $lnp_cart->shipping_cost;

        # Create a new address for the pudo
        $pudo_address = new Address();

        $name_validate         = '/[0-9!<>,;?=+()@#"°{}_$%:]/';
        $generic_name_validate = '/[<>={}]/';
        $address_validate      = '/[!<>?=+@{}_$%]/';
        $postcode_validate     = '/[^a-zA-Z 0-9-]/';
        $city_name_validate    = '/[!<>;?=+@#"°{}_$%]/';
        $phone_number_validate = '/[^+0-9. ()-]/';
        $message_validate      = '/[<>{}]/';

        $pudo_address->id_country = (int)Country::getByIso($this->puCodeToPsCodeCountry($lnp_cart->pudo_country_code));
        $pudo_address->alias      = preg_replace($generic_name_validate, ' ', Tools::substr($lnp_cart->pudo_name, 0, 32));
        $pudo_address->lastname   = preg_replace($name_validate, ' ', Tools::substr($lnp_cart->pudo_name, 0, 32));
        $pudo_address->firstname  = ' ';
        $pudo_address->address1   = preg_replace($address_validate, ' ', $address[0]);
        if (isset($address[1])) {
            $pudo_address->address2 = preg_replace($address_validate, ' ', $address[1]);
        }

        $pudo_address->postcode = preg_replace($postcode_validate, ' ', $lnp_cart->pudo_zip_code);
        $pudo_address->city     = preg_replace($city_name_validate, ' ', $lnp_cart->pudo_city_name);

        $pudo_address->id_customer  = (int)$delivery_address->id_customer;
        $pudo_address->phone        = preg_replace($phone_number_validate, ' ', $delivery_address->phone);
        $pudo_address->phone_mobile = preg_replace($phone_number_validate, ' ', $delivery_address->phone_mobile);
        $pudo_address->other        = preg_replace($message_validate, ' ', Tools::substr($delivery_name, 0, 32));
        $pudo_address->vat_number   = preg_replace($generic_name_validate, ' ', $delivery_address->vat_number);
        $pudo_address->vat_number   = preg_replace($generic_name_validate, ' ', $delivery_address->dni);
        $pudo_address->save();

        # update order delivery address
        $params['order']->id_address_delivery = $pudo_address->id;
        $params['order']->save();
        $this->myLog($params['cart']->id, 'hookActionValidateOrder > order saved');

        # update order shipping cost
        $pickup_carrier         = new Carrier($pickup_carrier_id);
        $is_pickup_carrier_free = $pickup_carrier->is_free;
        # Check if TOTAL SHIPPING is free
        $package_shipping_cost = $params['cart']->getPackageShippingCost($pickup_carrier_id);
        $is_free_shipping      = ($package_shipping_cost == 0); # determine shipping cost of the cart if pickup carrier is selected
        if ($is_free_shipping) {
            $this->myLog($params['cart']->id, 'hookDisplayCarrierList :  Current cart TOTAL SHIPPING is FREE');
        } else {
            $this->myLog($params['cart']->id, 'hookDisplayCarrierList :  Current cart TOTAL SHIPPING is NOT FREE');
        }

        if (!$is_pickup_carrier_free && !$is_free_shipping) { # need to check is PICKUP is set to free AND if order shipping is free (cart rule, shipping pref...)
            $params['order']->updateShippingCost($price); # order has to be saved before shipping cost is updated otherwhise order is not yet created, no need to save again as updateShippingCost ends by un update of the order
            $this->myLog($params['cart']->id, 'hookActionValidateOrder > order updateShippingCost to '.$price);
        } else {
            $params['order']->updateShippingCost(0); # order has to be saved before shipping cost is updated otherwhise order is not yet created, no need to save again as updateShippingCost ends by un update of the order
            $this->myLog($params['cart']->id, 'hookActionValidateOrder > order updateShippingCost to 0');
        }

        $pickup_order = $this->getPickUpOrder($params['order']->id);

        # save order in table
        $lnp_order = Lnp2Order::getLnpOrderByOrderId($params['order']->id);
        if (!$lnp_order) {
            $lnp_order = new Lnp2Order();
        }

        if (!$pickup_order) {
            $this->myLog($params['cart']->id, 'hookActionValidateOrder > pickup Order was not generated.');
        } else {
            $lnp_order->navette_code     = $pickup_order->parcelNumber;
            $lnp_order->lnp_order_number = $pickup_order->order;
        }
        $lnp_order->id_order              = $params['order']->id;
        $lnp_order->navette_pdf_url       = '';
        $lnp_order->delivery_id           = $lnp_cart->pudo_id;
        $lnp_order->delivery_name         = $lnp_cart->pudo_name;
        $lnp_order->delivery_address      = $lnp_cart->pudo_address_line;
        $lnp_order->delivery_city         = $lnp_cart->pudo_city_name;
        $lnp_order->delivery_country_code = $lnp_cart->pudo_country_code;
        $lnp_order->delivery_zip_code     = $lnp_cart->pudo_zip_code;
        $lnp_order->save();
        $this->myLog($params['cart']->id, 'hookActionValidateOrder > order is stored in lnp2_order table');

        $this->cleanCookieCartInfo();
        $this->myLog($params['cart']->id, 'hookActionValidateOrder > cookies cleaning');

//         $purge_status = Lnp2Cart::purgeLnpCartByCartId($id_cart); # TO DO ???

        # the pudo address has to be deleted otherwise it is saved as a manufacturer address (ticket 6131)
        $pudo_address->delete();

        return true;
    }

    public function validateFormatForWS($type, $data)
    {
        switch ($type) {
            case 'mobile': # only numerical digits, no accented character, no special character, lenght = 10
                if (preg_match('/^[0-9]{10}$/', $data)) {
                    return $data; # phone number contains 10 numerical digits starting with 0 > OK
                } else {
                    $phone = preg_replace('/[^0-9]/', '', $data); # remove all non numerical digit
                    $phone = Tools::substr($phone, -9); # keep only 9 last numerical digits
                    if (Tools::substr($phone, 0, 1) == '0') {
                        return '0648484848'; # otherwise return fake phone
                    } else {
                        $phone = '0'.$phone;
                    }
                    if (preg_match('/^[0-9]{10}$/', $phone)) {
                        return $phone; # re-check phone format
                    } else {
                        return '0648484848'; # otherwise return fake phone
                    }
                }
                break;
            case 'phone': # only numerical digits, no accented character, no special character, lenght = 10
                if (preg_match('/^[0-9]{10}$/', $data)) {
                    return $data; # phone number contains 10 numerical digits starting with 0 > OK
                } else {
                    $phone = preg_replace('/[^0-9]/', '', $data); # remove all non numerical digit
                    $phone = Tools::substr($phone, -9); # keep only 9 last numerical digits
                    if (Tools::substr($phone, 0, 1) == '0') {
                        return '0148484848'; # otherwise return fake phone
                    } else {
                        $phone = '0'.$phone;
                    }
                    if (preg_match('/^[0-9]{10}$/', $phone)) {
                        return $phone; # re-check phone format
                    } else {
                        return '0148484848'; # otherwise return fake phone
                    }
                }
                break;
            case 'name': # no numeric charater only alphabetical, accented character ok, no special character, lenght = 1 to 30
                $name = preg_replace('/[0-9]/', '', $data); # remove numeric characters
                $name = preg_replace('#[^.\p{L}\s]+#ui', ' ', $name);
                $name = Tools::substr($name, 0, 30); # truncate if longer than 30 characters
                return $name;
                break;
            case 'address': # alphabetic or numeric charater, accented character ok, no special character, lenght = 1 to 35
                $address = preg_replace('#[^.A-Za-z]+#ui', ' ', $data);
                $address = Tools::substr($address, 0, 35); # truncate if longer than 30 characters
                return $address;
                break;
        }
    }

    public function wdRemoveAccents($str, $charset = 'utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
    }

    private function cleanCookieCartInfo()
    {
        unset($this->context->cookie->cart_lnp2_site);
        unset($this->context->cookie->lnp2_available);
        unset($this->context->cookie->lnp2_not_available_reason);
        unset($this->context->cookie->lnp2_free);
        unset($this->context->cookie->lnp2_cart_signature);
    }

    public function hookDisplayAdminOrder($params)
    {
        $this->myLog($params['cart']->id, 'hookDisplayAdminOrder : start');
        # check if it a Pickup order (LNP2_CARRIER_ID may have changed so check all LNP2_CARRIER_IDs list)
        $pickup_order = false;
        $carrier_ids  = explode('|', Configuration::get('LNP2_CARRIER_ID_HIST'));
        foreach ($carrier_ids as $carrier_id) {
            if ($carrier_id == $params['cart']->id_carrier) {
                $this->myLog($params['cart']->id, 'hookDisplayAdminOrder : it is a Pickup order');
                $pickup_order = true;
            }
        }

        if (!$pickup_order) {
            $this->myLog($params['cart']->id, 'hookDisplayAdminOrder : it is NOT a Pickup order');

            return;
        }

        $id_order = (int)$params['id_order'];
        $this->myLog($params['cart']->id, 'hookDisplayAdminOrder : id_order = '.$id_order);

        $lnp_order = Lnp2Order::getLnpOrderByOrderId($id_order);
        $lnp_cart  = Lnp2Cart::getLnpCartByCartId($params['cart']->id);

        $lnp_load = '';

        if (!$lnp_order && !$lnp_cart) {
            # lnp order cannot be found
            $this->myLog($params['cart']->id, 'hookDisplayAdminOrder : order '.$id_order.' is NOT found in Lnp2Order and not in Lnp2Cart table');

            return;
        } elseif (!$lnp_order) {
            # lnp order cannot be found
            $this->myLog($params['cart']->id, 'hookDisplayAdminOrder : order '.$id_order.' is NOT found in Lnp2Order table');
            $lnp_load = 'cart';

            $lnp_order = array(
                'navette_code'          => '',
                'delivery_name'         => '',
                'delivery_address'      => '',
                'delivery_city'         => '',
                'delivery_country_code' => '',
                'delivery_zip_code'     => '',
            );
        } else {
            $lnp_load = 'all';
            $this->myLog($params['cart']->id, 'hookDisplayAdminOrder : order '.$id_order.' is found in Lnp2Order table');
            $this->myLog($params['cart']->id, 'hookDisplayAdminOrder : order '.$id_order.' navette_code = '.$lnp_order->navette_code);
            $this->myLog($params['cart']->id, 'hookDisplayAdminOrder : order '.$id_order.' navette_pdf_url = '.$lnp_order->navette_pdf_url);

        }

        $is_PS16 = (version_compare(_PS_VERSION_, '1.6.0', '>=') === true);

        $error_messages = array();
        /** @var Context $context */
        $context = Context::getContext();
        if ($context->cookie->error_messages) {
            $error_messages                  = Tools::jsonDecode($context->cookie->error_messages);
            $context->cookie->error_messages = "";
            $context->cookie->write();
        }

        $confirmation_messages = array();
        if ($context->cookie->confirmation_messages) {
            $confirmation_messages                  = Tools::jsonDecode($context->cookie->confirmation_messages);
            $context->cookie->confirmation_messages = "";
            $context->cookie->write();
        }

        $this->context->smarty->assign(array(
            'lnp_load'              => $lnp_load,
            'navette_code'          => $lnp_order->navette_code,
            'delivery_name'         => $lnp_order->delivery_name,
            'delivery_address'      => $lnp_order->delivery_address,
            'delivery_city'         => $lnp_order->delivery_city,
            'delivery_country_code' => $lnp_order->delivery_country_code,
            'delivery_zip_code'     => $lnp_order->delivery_zip_code,
            'id_order'              => Tools::getValue('id_order'),
            'is_PS16'               => $is_PS16,
            'error_messages'        => $error_messages,
            'confirmation_messages' => $confirmation_messages,
            'insurance'             => (bool)$lnp_cart->insurance,
        ));

        if (Tools::getValue('error_lnp') == 1) {
            return $this->display(__FILE__, 'views/templates/hook/displayAdminOrder.tpl').$this->display(__FILE__, 'views/templates/admin/tryLaterPopup.tpl');
        }

        return $this->display(__FILE__, 'views/templates/hook/displayAdminOrder.tpl');
    }


    private function sendUnavailabilityEmail($pudo, $unavailability = false, $date_start = '', $date_end = '')
    {
        $config = Configuration::getMultiple(array('PS_LANG_DEFAULT', 'LNP2_NAME', 'PS_SHOP_EMAIL', 'PS_SHOP_NAME'));

        $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $vars = array(
            '{logo_url}'     => _PS_BASE_URL_.'/modules/'.$this->name.'/views/img/pickup.jpg',
            '{name}'         => $config['LNP2_NAME'],
            '{pudo_name}'    => $pudo['name'],
            '{pudo_address}' => $pudo['address1'],
            '{pudo_zipCode}' => $pudo['zipCode'],
            '{pudo_city}'    => $pudo['city'],
            '{date_start}'   => $date_start,
            '{date_end}'     => $date_end,
        );

        if (!$unavailability == true) {
            Mail::Send(
                $id_lang,
                'unavailablePudo',
                Mail::l('[la Navette Pickup] URGENT: Your drop off point is not available', $id_lang),
                $vars,
                (string)$config['PS_SHOP_EMAIL'],
                null,
                (string)$config['PS_SHOP_EMAIL'],
                (string)$config['PS_SHOP_NAME'],
                null,
                null,
                dirname(__FILE__).'/views/templates/mails/'
            );
        } else {
            Mail::Send(
                $id_lang,
                'unavailablePudoDate',
                Mail::l('[la Navette Pickup] URGENT: Your drop off point is not available', $id_lang),
                $vars,
                (string)$config['PS_SHOP_EMAIL'],
                null,
                (string)$config['PS_SHOP_EMAIL'],
                (string)$config['PS_SHOP_NAME'],
                null,
                null,
                dirname(__FILE__).'/views/templates/mails/'
            );
        }
    }

    /**
     * Create new Pickup Order object (order, parcelNumber, pdfDataBase64)
     * @param int $id_order
     * @param int $lang_id : Language - by default, current language
     * @return array|bool|mixed
     */
    public function getPickUpOrder($id_order, $lang_id = -1)
    {
        $order   = new Order($id_order);
        $cart_id = $order->id_cart;
        $this->myLog($cart_id, 'getPickUpOrder : start');

        $customer         = new Customer($order->id_customer);
        $customer_address = new Address($order->id_address_delivery);
        $invoice_address  = new Address($order->id_address_invoice);

        # get pudo infos from last stored cart in lnp2_cart
        $lnp_cart = Lnp2Cart::getLnpCartByCartId($order->id_cart);

        if ($lnp_cart <> null) {

            $config = Configuration::getMultiple(array('LNP2_NAVETTE_PARTNER_ID', 'LNP2_NAME', 'LNP2_MAIL', 'LNP2_PHONE', 'LNP2_DROP_OFF_SITE'));

            # drop-off site
            $drop_off_pudo = Tools::jsonDecode(str_replace('|', '"', $config['LNP2_DROP_OFF_SITE']), true);

            $ws = $this->getWs();

            # get Pudo Client Details
            $pudo_details = array(
                'partnerName' => $config['LNP2_NAVETTE_PARTNER_ID'],
                'pudoId'      => $lnp_cart->pudo_id,
                'pudoType'    => 'destination',
            );

            foreach ($pudo_details as $key => $value) {
                $this->myLog($cart_id, 'getPickUpOrder > getPudoDetails call with pudo_details '.$key.' = '.$value);
            }

            $pudo_client = $ws->getPudoDetails($pudo_details);

            if (!$pudo_client || !isset($pudo_client->pudo) || !$pudo_client->pudo) {
                $error = isset($pudo_client->message) ? ' : '.$pudo_client->message : '';
                $this->myLog($cart_id, 'getPickUpOrder > getPudoDetails return an ERROR'.$error);

                if ($pudo_client->message) {
                    return Tools::jsonDecode(
                        Tools::jsonEncode(
                            array(
                                'error'   => true,
                                'message' => $pudo_client->message,
                            )
                        )
                    );
                }

                return false;
            }
            $pudo_client = $pudo_client->pudo;

            $recipientPhone = '';
            $this->myLog($cart_id, 'getPickUpOrder > recipient DELIVERY mobile phone = '.$customer_address->phone_mobile.' and phone = '.$customer_address->phone);
            $phone = $this->validateFormatForWS('mobile', $customer_address->phone_mobile);
            if ($phone !== '0648484848') {
                $recipientPhone = $phone;
            } else {
                $phone = $this->validateFormatForWS('phone', $customer_address->phone);
                if ($phone !== '0148484848') {
                    $recipientPhone = $phone;
                } else {
                    # if no phones found in delivery address, we try with invoice address information
                    $this->myLog($cart_id, 'getPickUpOrder > recipient INVOICE mobile phone = '.$invoice_address->phone_mobile.' and phone = '.$invoice_address->phone);
                    $phone = $this->validateFormatForWS('mobile', $invoice_address->phone_mobile);
                    if ($phone !== '0648484848') {
                        $recipientPhone = $phone;
                    } else {
                        $phone = $this->validateFormatForWS('phone', $invoice_address->phone);
                        if ($phone !== '0148484848') {
                            $recipientPhone = $phone;
                        }
                    }
                }
            }
            $delivery_address = new Address($order->id_address_delivery);

            if ($lang_id < 0) {
                # if no specified language, we use the current language
                $lang_id = Context::getContext()->language->id;
            }

            # Create Order

            $order_details = array(
                'partnerName'          => $config['LNP2_NAVETTE_PARTNER_ID'],
                'orderExt'             => $order->reference,
                'height'               => 1,
                'width'                => 1,
                'length'               => 1,
                'weight'               => (float)$order->getTotalWeight() <= 0 ? (float)1 : (float)$order->getTotalWeight(),
                'pudo'                 => $pudo_client->id,
                'pudoName'             => $pudo_client->name,
                'pudoAdress1'          => $pudo_client->address1,
                'pudoAdress2'          => $pudo_client->address2,
                'pudoZipCode'          => $pudo_client->zipCode,
                'pudoCity'             => $pudo_client->city,
                'pudoCountry'          => $this->getCountryNameFromPU($pudo_client->countryCode, $lang_id),
                'pudoCountryCode'      => $pudo_client->countryCode,
                'recipientContactName' => $this->validateFormatForWS('name', $delivery_address->other),
                'recipientEmail'       => $customer->email,
                'recipientPhone'       => $recipientPhone,
                'senderMail'           => $config['LNP2_MAIL'],
                'senderPhone'          => $config['LNP2_PHONE'],
                'senderCivility'       => 'M',
                'senderName'           => $config['LNP2_NAME'],
                'senderName2'          => $drop_off_pudo['name'],
                'senderAdress1'        => $drop_off_pudo['address1'],
                'senderAdress2'        => isset($drop_off_pudo['address2']) && $drop_off_pudo['address2'] ? $drop_off_pudo['address2'] : '',
                'senderZipCode'        => $drop_off_pudo['zipCode'],
                'senderCity'           => $drop_off_pudo['city'],
                'senderCountryCode'    => $drop_off_pudo['countryCode'],
                'senderCountryName'    => $this->getCountryNameFromPU($drop_off_pudo['countryCode'], $lang_id),
                'content'              => '',
                'insuranceEnabled'     => $lnp_cart->insurance,
                'contentPrice'         => $order->getTotalPaid(),
            );

            foreach ($order_details as $key => $value) {
                $this->myLog($cart_id, 'getPickUpOrder > Order call with OrderDetails '.$key.' = '.$value);
            }

            $order_pickup = $ws->order($order_details);

            $this->myLog($cart_id, 'getPickUpOrder > Order called to confirm order');


            if ($order_pickup && isset($order_pickup->pdfDataBase64)) {
                $this->myLog($cart_id, 'getPickUpOrder > Order parcelNumber = '.$order_pickup->parcelNumber);
                if (isset($order_pickup->pdfDataBase64) && $order_pickup->pdfDataBase64) {
                    $this->myLog($cart_id, 'getPickUpOrder > Order pdfDataBase64 HAS CONTENT');
                } else {
                    $this->myLog($cart_id, 'getPickUpOrder > Order pdfDataBase64 IS EMPTY');
                }
                $this->myLog($cart_id, 'getPickUpOrder > Order order = '.$order_pickup->order);

                # update lnp2order
                $lnp_order = Lnp2Order::getLnpOrderByOrderId($id_order);
                if (!$lnp_order) {
                    $lnp_order = new Lnp2Order();
                }
                $lnp_order->lnp_order_number = $order_pickup->order;
                $lnp_order->navette_code     = $order_pickup->parcelNumber;
                $lnp_order->update();
                $this->myLog($cart_id, 'getPickUpOrder > update lnp_order = '.$lnp_order->id);
                $this->myLog($cart_id, 'getPickUpOrder : stop');

                return $order_pickup;
            } else {
                $this->myLog($cart_id, 'getPickUpOrder > Order return an ERROR : '.Tools::jsonEncode($order_pickup));
            }
        }

        return false;
    }

    /**
     * Get Label for a given order with LNP WS
     * @param $id_order
     * @return object
     */
    public function getLabel($id_order)
    {
        $lnp2_carrier_ids = explode('|', Configuration::get('LNP2_CARRIER_ID_HIST'));
        $order            = new Order($id_order);
        $cart             = new Cart($order->id_cart);
        if (!in_array($cart->id_carrier, $lnp2_carrier_ids)) {
            # Only work for lnp2 carrier.
            return null;
        }

        $lnp_order = Lnp2Order::getLnpOrderByOrderId($id_order);

        if ($lnp_order == null || $lnp_order->lnp_order_number == '') {
            $lnp_cart = Lnp2Cart::getLnpCartByCartId($order->id_cart);
            if ($lnp_cart <> null) {
                $params                = array();
                $params['cart']        = $cart;
                $params['order']       = $order;
                $params['customer']    = new Customer($order->id_customer);
                $params['currency']    = new Currency($order->id_currency);
                $params['orderStatus'] = new OrderState($order->current_state);

                if (false == $this->hookActionValidateOrder($params)) {
                    $this->myLog($order->id_cart, 'getLabel : Error during lnp_order generation.');
                }

                $lnp_order = Lnp2Order::getLnpOrderByOrderId($id_order);
            } else {
                $this->myLog($order->id_cart, 'getLabel : No information of lnp_cart have been saved.');
            }
        }

        if ($lnp_order <> null) {
            $config = Configuration::getMultiple(array('LNP2_NAVETTE_PARTNER_ID'));
            $ws     = $this->getWs();

            $label_details = array(
                'partnerName' => $config['LNP2_NAVETTE_PARTNER_ID'],
                'orderNumber' => $lnp_order->lnp_order_number.'-PSH',
            );

            $label = $ws->getLabel($label_details);

            return $label;
        }

        return null;
    }

    private function isPudoAvailable($pudo)
    {
        if (isset($pudo->available) && !$pudo->available) {
            return false;
        }
        if (!$pudo->holidays || count($pudo->holidays) == 0) {
            return true;
        }

        $vacationStartDateTimestamp = strtotime($pudo->holidays[0]->startDate);
        $vacationEndDateTimestamp   = strtotime($pudo->holidays[0]->endDate);

        $nowTimestamp = time();

        if (($vacationStartDateTimestamp - $nowTimestamp) < 604800) {
            # < 7 days
            return false;
        } elseif (($vacationStartDateTimestamp >= $nowTimestamp) && ($vacationEndDateTimestamp <= $nowTimestamp)) {
            return false;
        }

        return true;
    }

    public function hookDisplayBackOfficeTop($params)
    {
        $config = Configuration::getMultiple(array('LNP2_DROP_OFF_SITE', 'LNP2_EMAIL_SENT_LAST_DATE', 'LNP2_DROP_OFF_SITE'));

        if (!$config['LNP2_DROP_OFF_SITE']) {
            return;
        }
        $emails_last_date = $config['LNP2_EMAIL_SENT_LAST_DATE'];

        # send emails once a day
        if ($emails_last_date
            && ($emails_last_date > date('Y-m-d\TH:i:s', strtotime('-1 day')).'.000Z')
        ) {
            return;
        }

        $drop_off_pudo    = Tools::jsonDecode(str_replace('|', '"', $config['LNP2_DROP_OFF_SITE']), true);
        $drop_off_pudo_id = $drop_off_pudo['id'];

        # check drop off point
        $ws  = $this->getWs();
        $res = $ws->getPudoDetails(array(
            'partnerName' => Configuration::get('LNP2_NAVETTE_PARTNER_ID'),
            'pudoId'      => (string)$drop_off_pudo_id,
            'pudoType'    => 'departure',
        ));

        if ($res) {
            if (!isset($res->pudo) || !$res->pudo) {
                # drop off point has disappeared
                $this->sendUnavailabilityEmail($drop_off_pudo);
                Configuration::updateValue('LNP2_DROP_OFF_UNAVAILABLE', true);
            } elseif (!$this->isPudoAvailable($res->pudo)) {
                Configuration::updateValue('LNP2_DROP_OFF_UNAVAILABLE', true);
                $pudo = $res->pudo;

                $date_start = date('d/m/Y', strtotime(str_replace('/', '-', $pudo->holidays[0]->startDate)));
                $date_end   = date('d/m/Y', strtotime(str_replace('/', '-', $pudo->holidays[0]->endDate)));

                $this->sendUnavailabilityEmail($drop_off_pudo, true, $date_start, $date_end);
            }
        }

        Configuration::updateValue('LNP2_EMAIL_SENT_LAST_DATE', date('Y-m-d\TH:i:s.000\Z'));
    }

    /**
     * Return an unique string of :
     * - product ids (present several times if several times the same product)
     * - id product attributes
     * - shop
     * - address id
     * @param Cart $cart
     * @return null|string
     */
    public function getCartSignature($cart)
    {
        if (!$cart) {
            return null;
        }

        $str = '';

        $products = $cart->getProducts();
        foreach ($products as $product) {
            $str .= $product['id_product'].$product['id_product_attribute'].$product['cart_quantity'].$product['id_shop'].';';
        }

        $str .= $cart->id_address_delivery;

        return $str;
    }

    public function hookActionCartSave($params)
    {
        # compute cart signature.
        # With this signature we can figure out if the products or the address have changed. And if that's the case refresh the price
        $signature = $this->getCartSignature($params['cart']);

        if ($this->context->cookie->lnp2_cart_signature &&
            ($signature != $this->context->cookie->lnp2_cart_signature)
        ) {
            $this->cleanCookieCartInfo();
        }
    }



    ############################################################################################################
    # Administration
    ############################################################################################################

    /**
     * Admin display
     * @return String Display admin content
     */
    public function getContent()
    {
        $this->postProcess();

        # page name and urls
        $this->context->smarty->assign(array(
            'screen'        => Tools::getValue('screen', 'config'),
            'config_url'    => $this->_getUrl(array('screen' => 'config')),
            'help_url'      => $this->_getUrl(array('screen' => 'help')),
            'logs_page_url' => $this->_getUrl(array('screen' => 'logs')),
            'logs_url'      => _PS_BASE_URL_."/modules/".$this->name.'/logs',
            'module_path'   => $this->_path,
            'mode_dev'      => self::$dev,
        ));

        $this->context->controller->addCSS($this->_path.'views/css/vendor/bootstrap.min.css'.$this->media_version, 'all', null, false);
        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === false) {
            $this->context->controller->addCSS($this->_path.'views/css/ps15.css'.$this->media_version, 'all', null, false);
        }

        return $this->displayConfigurationScreen();
    }

    /**
     * return true / false depending if the drop off pudo is available
     *
     */
    private function isDropOffPudoAvailable()
    {
        $pudo_is_available = true;

        $config_pickup_drop_off_site = Configuration::get('LNP2_DROP_OFF_SITE');

        $pickup_drop_off_site = ($config_pickup_drop_off_site ? Tools::jsonDecode(str_replace('|', '"', $config_pickup_drop_off_site), true) : null);
        if ($pickup_drop_off_site) {
            $ws  = $this->getWs();
            $res = $ws->getPudoDetails(array(
                'partnerName' => (string)Configuration::get('LNP2_NAVETTE_PARTNER_ID'),
                'pudoId'      => (string)$pickup_drop_off_site['id'],
                'pudoType'    => 'departure',
            ));

            if (!$res || !(isset($res->pudo->id) && $res->pudo->id == (string)$pickup_drop_off_site['id'])) {
                $pudo_is_available = false;
            } elseif (!$this->isPudoAvailable($res->pudo)) {
                $pudo_is_available = false;
            }
        }

        return $pudo_is_available;
    }

    private function formAdvanced()
    {
        $helper                  = new HelperOptions();
        $helper->id              = $this->id;
        $helper->module          = $this;
        $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex.'&configure='.$this->name.'&config_tab=advanced_parameters';
        $helper->show_toolbar    = false;

        $fields_options = array(
            'log'     => array(
                'title'  => $this->l('Your logs'),
                'image'  => $this->_path.'views/img/pickup-cube-small.png',
                'fields' => array(
                    'LNP2_LOGS' => array(
                        'title'      => $this->l('Activate the logs'),
                        'desc'       => $this->l('In case of technical issues, please activate the logs by selecting "Yes". This will enable us to analyze the issue and get back to you shortly.'),
                        'type'       => 'select',
                        'identifier' => 'value',
                        'list'       => array(
                            array(
                                'value' => 1,
                                'name'  => $this->l('Yes'),
                            ),
                            array(
                                'value' => 0,
                                'name'  => $this->l('No'),
                            ),
                        ),
                        'default'    => Configuration::get('LNP2_LOGS'),
                    ),
                ),
            ),
            'options' => array(
                'title'  => $this->l('Your options'),
                'image'  => $this->_path.'views/img/pickup-cube-small.png',
                'fields' => array(
                    'LNP2_OPTIONS_CORSE' => array(
                        'title'      => $this->l('Extra charge for Corsica'),
                        'suffix'     => $this->l('€ VAT included'),
                        'desc'       => $this->l('(If empty, La Navette Pickup carrier cost will be applied)'),
                        'type'       => 'text',
                        'identifier' => 'value',
                    ),
                    'LNP2_INSURANCE'     => array(
                        'title'      => $this->l('Ad Valorem Insurance Option'),
                        'desc'       => $this->l('By choosing this option, you are insuring all your parcels at the articles’ sales price (up to 800€ VAT included) in accordance with our General Terms and Conditions. Without insurance, the compensation in case of loss or damage is limited to 25€ VAT included per parcel.'),
                        'type'       => 'select',
                        'identifier' => 'value',
                        'list'       => array(
                            array(
                                'value' => 1,
                                'name'  => $this->l('Yes'),
                            ),
                            array(
                                'value' => 0,
                                'name'  => $this->l('No'),
                            ),
                        ),
                        'default'    => Configuration::get('LNP2_INSURANCE'),
                    ),
                ),
            ),
        );

        return $helper->generateOptions($fields_options);
    }

    private function formWSId()
    {
        $helper                  = new HelperOptions();
        $helper->id              = $this->id;
        $helper->module          = $this;
        $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex.'&configure='.$this->name.'&config_tab=shipping';
        $helper->show_toolbar    = false;

        $error_form = false;

        if (Lnp2PickUpRESTLibrary::isConfigured()) {
            $ws = $this->getWs();
            if (!$ws->hasToken()) {
                $error_form = true;
            }
        }
        $fields_options = array(
            'general' => array(
                'title'       => $this->l('Your login'),
                'image'       => $this->_path.'views/img/pickup-cube-small.png',
                'description' => $this->l('This information will be provided by la Navette Pickup once you have subscribed'),
                'class'       => $error_form ? 'has-error' : '',
                'info'        => $error_form ? $this->display(__FILE__, 'alertidentifier.tpl') : '',
                'fields'      => array(
                    'LNP2_NAVETTE_PARTNER_ID'  => array(
                        'title'      => $this->l('Web Services Login'),
                        'type'       => 'text',
                        'cast'       => 'strval',
                        'identifier' => 'value',
                        'required'   => true,
                    ),
                    'LNP2_NAVETTE_PARTNER_PWD' => array(
                        'title'      => $this->l('Web Services Password'),
                        'type'       => 'text',
                        'cast'       => 'strval',
                        'identifier' => 'value',
                        'required'   => true,
                    ),
                ),
                'submit'      => array(
                    'title' => $this->l('Save'),
                    'name'  => 'submitPickupWS',
                ),
            ),
        );

        return $helper->generateOptions($fields_options);
    }

    private function formStates($config)
    {
        $order_states                   = OrderState::getOrderStates($this->context->language->id);
        $states_helper                  = new HelperOptions();
        $state_fields_options           = array(
            'general' => array(
                'title'  => $this->l('Personnalize the Prestashop status of your orders'),
                'image'  => $this->_path.'views/img/pickup-cube-small.png',
                'fields' => array(

                    'LNP2_PREPARATION_ORDER_STATE' => array(
                        'title'      => $this->l('Shortcut button \"Change status\"'),
                        'type'       => 'select',
                        'identifier' => 'id_order_state',
                        'list'       => $order_states,
                        'default'    => $config['LNP2_PREPARATION_ORDER_STATE'],
                        'desc'       => $this->l('Click on the \"Change the status\" shortcut to change the order status to this preset choice'),
                    ),

                    'LNP2_SHIPPED_ORDER_STATE' => array(
                        'title'      => $this->l('Shortcut button \"Order shipped\"'),
                        'type'       => 'select',
                        'identifier' => 'id_order_state',
                        'list'       => $order_states,
                        'default'    => $config['LNP2_SHIPPED_ORDER_STATE'],
                        'desc'       => $this->l('Click on the \"Order shipped\" shortcut to change the order status to this preset choice'),
                    ),
                ),
            ),
        );
        $states_helper->currentIndex    = AdminController::$currentIndex.'&configure='.$this->name.'&config_tab=shipments';
        $states_helper->token           = Tools::getAdminTokenLite('AdminModules');
        $states_helper->module          = $this;
        $states_helper->id              = $this->id;
        $states_helper->name_controller = $this->name;
        $states_helper->show_toolbar    = false;

        return $states_helper->generateOptions($state_fields_options);
    }

    private function formSenderDetails()
    {
        $helper         = new HelperOptions();
        $fields_options = array(
            'general' => array(
                'title'       => $this->l('Sender details'),
                'image'       => $this->_path.'views/img/pickup-cube-small.png',
                'description' => $this->l('This name will be written as sender on the transport label printed'),
                'fields'      => array(
                    'LNP2_NAME'              => array(
                        'title'    => $this->l('Sender Name'),
                        'type'     => 'text',
                        'class'    => '',
                        'required' => true,
                        'size'     => 50,
                    ),
                    'LNP2_MAIL'              => array(
                        'title'    => $this->l('Sender Mail'),
                        'type'     => 'text',
                        'class'    => '',
                        'required' => true,
                        'size'     => 50,
                    ),
                    'LNP2_PHONE'             => array(
                        'title'    => $this->l('Sender Phone'),
                        'type'     => 'text',
                        'class'    => '',
                        'required' => true,
                        'size'     => 50,
                        'desc'     => $this->l('Format example (+33XXXXXXXXX)'),
                    ),
                    'LNP2_DROP_OFF_SITE'     => array(
                        'type' => 'hidden',
                    ),
                    'LNP2_DROP_OFF_PUDO_LAT' => array(
                        'type' => 'hidden',
                    ),
                    'LNP2_DROP_OFF_PUDO_LNG' => array(
                        'type' => 'hidden',
                    ),
                ),
            ),
        );

        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&config_tab=shipping';
        $helper->token        = Tools::getAdminTokenLite('AdminModules');
        $helper->module       = $this;
        //$helper->image = $this->_path.'views/img/pickupRed.png';

        $helper->show_toolbar = false;

        $helper->id              = $this->id;
        $helper->name_controller = $this->name;

        return $helper->generateOptions($fields_options);
    }

    private function displayConfigurationScreen()
    {
        $config = Configuration::getMultiple(array('LNP2_PREPARATION_ORDER_STATE', 'LNP2_SHIPPED_ORDER_STATE', 'LNP2_CARRIER_ID', 'LNP2_DROP_OFF_SITE'));

        $is_drop_off_pudo_available = $this->isDropOffPudoAvailable();

        if ($is_drop_off_pudo_available) {
            Configuration::deleteByName('LNP2_DROP_OFF_UNAVAILABLE');
        } else {
            Configuration::updateValue('LNP2_DROP_OFF_UNAVAILABLE', true);
        }

        $ws_ok = false;
        if (Lnp2PickUpRESTLibrary::isConfigured()) {
            $ws = $this->getWs();
            if ($ws->hasToken()) {
                $ws_ok = true;
            }
        }

        $pickup_drop_off_site = ($config['LNP2_DROP_OFF_SITE'] ? Tools::jsonDecode(str_replace('|', '"', $config['LNP2_DROP_OFF_SITE']), true) : null);

        # general
        $this->context->smarty->assign(
            array(
                'form'                 => $this->formSenderDetails(),
                'ws_form'              => $this->formWSId(),
                'ws_ok'                => (bool)$ws_ok,
                'advanced_form'        => $this->formAdvanced(),
                'states_form'          => $this->formStates($config),
                'pickup_drop_off_site' => $pickup_drop_off_site,
                'pudo_is_available'    => $is_drop_off_pudo_available,
            )
        );

        $id_carrier = (int)$config['LNP2_CARRIER_ID'];
        $carrier    = new Carrier($id_carrier);
        $this->context->smarty->assign(array(
            'id_carrier'     => $id_carrier,
            'max_weight'     => $carrier->max_weight,
            'max_width'      => $carrier->max_width,
            'max_height'     => $carrier->max_height,
            'max_depth'      => $carrier->max_depth,
            'config_tab'     => Tools::getValue('config_tab'),
            'is_PS16'        => (version_compare(_PS_VERSION_, '1.6.0', '>=') === true),
            'security_token' => Configuration::get('LNP2_SECURITY_TOKEN'),
            'modules_dir'    => _MODULE_DIR_,
        ));

        $http_protocol = (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == "on" ? 'https' : 'http');

        if (version_compare(_PS_VERSION_, '1.6.0', '>=') === true) {
            $this->context->controller->addJS($http_protocol.'://maps.googleapis.com/maps/api/js?libraries=places&key='.$this->google_maps_key);
            $this->context->controller->addJS($this->_path.'views/js/vendor/markerwithlabel/markerwithlabel.min.js'.$this->media_version);
            $this->context->controller->addJS($this->_path.'views/js/vendor/infobox/infobox.min.js'.$this->media_version);
        }
        $this->context->controller->addJS($this->_path.'views/js/map.js'.$this->media_version);
        $this->context->controller->addCSS($this->_path.'views/css/admin.css'.$this->media_version, 'all', null, false);
        $this->context->controller->addCSS($this->_path.'views/css/map.css'.$this->media_version, 'all', null, false);

        $this->context->smarty->assign(array(
            'pickup_dir'            => $this->_path,
            'google_maps_key'       => $this->google_maps_key,
            'ids_filled'            => (bool)Lnp2PickUpRESTLibrary::isConfigured(),
            'ps_order_process_type' => (1 == Configuration::get('PS_ORDER_PROCESS_TYPE')) ? true : false,
        ));

        return $this->display(__FILE__, 'views/templates/admin/configurationScreen.tpl');

    }

    /**
     * Processing post in BO
     */
    public function postProcess()
    {
        if (Tools::getValue('LNP2_LOGS', -1) !== -1) {
            if (!(bool)Tools::getValue('LNP2_LOGS')) {
                @unlink($this->getLocalPath().'logs/success.log');
                @unlink($this->getLocalPath().'logs/error.log');
            }

            Configuration::updateValue('LNP2_LOGS', (bool)Tools::getValue('LNP2_LOGS'));
            Configuration::updateValue('LNP2_OPTIONS_CORSE', (float)Tools::getValue('LNP2_OPTIONS_CORSE'));
            Configuration::updateValue('LNP2_INSURANCE', (bool)Tools::getValue('LNP2_INSURANCE'));
            $this->context->smarty->assign('save_advanced_parameters', true);
        } elseif (Tools::getValue('LNP2_NAME')) { # Save config screen - test first field

            $vals = array('LNP2_NAME', 'LNP2_MAIL', 'LNP2_PHONE', 'LNP2_DROP_OFF_PUDO_LAT', 'LNP2_DROP_OFF_PUDO_LNG');

            foreach ($vals as $val) {
                Configuration::updateValue($val, Tools::getValue($val));
            }

            # since double quotes create an issue when reloading the var in the config HelperOptions, we remove them
            Configuration::updateValue('LNP2_DROP_OFF_SITE', str_replace('"', '', Tools::getValue('LNP2_DROP_OFF_SITE')));
            $this->context->smarty->assign('save_shipping', true);
        } elseif (Tools::isSubmit('submitPickupWS')) {
            foreach (array('LNP2_NAVETTE_PARTNER_ID', 'LNP2_NAVETTE_PARTNER_PWD') as $val) {
                Configuration::updateValue($val, Tools::getValue($val));
            }
            $ws = $this->getWs();
            if ($ws->hasToken()) {
                $this->context->smarty->assign('save_shipping', true);
            }
        } elseif (Tools::getValue('LNP2_PREPARATION_ORDER_STATE')) { # Save order states - test first field
            foreach (array('LNP2_PREPARATION_ORDER_STATE', 'LNP2_SHIPPED_ORDER_STATE') as $val) {
                Configuration::updateValue($val, Tools::getValue($val));
            }
            $this->context->smarty->assign('save_shipments', true);
        }
    }

    public function getURL()
    {
        if (self::$dev) {
            return 'https://c2cfrontservice-uat.pickup-services.com/C2CFrontApis/FrontApi.svc';
        } else {
            return 'https://c2c.pickup-services.com/C2CFrontApis/FrontApi.svc';
        }
    }

    public static function getTokenAPI()
    {
        if (self::$dev) {
            return 'https://apis-uat.pickup-services.com/identity/issue/simple';
        } else {
            return 'https://apis.pickup-services.com/identity/issue/simple';
        }
    }

    /**
     *
     * CarrierModule methods
     *
     */
    public function hookUpdateCarrier($params)
    {
        $id_carrier_old = (int)($params['id_carrier']);
        $id_carrier_new = (int)($params['carrier']->id);

        if ($id_carrier_old == (int)Configuration::get('LNP2_CARRIER_ID')) {
            Configuration::updateValue('LNP2_CARRIER_ID', $id_carrier_new);
            Configuration::updateValue(
                'LNP2_CARRIER_ID_HIST',
                Configuration::get('LNP2_CARRIER_ID_HIST').'|'.$id_carrier_new
            );
        }
    }

    /**
     * We ensure here that the order can be shipped via la Navette Pickup
     * and return a shipping cost if so
     *
     * @param Cart $cart
     * @param      $shipping_cost
     * @return bool|float|mixed
     */
    public function getOrderShippingCost($cart, $shipping_cost)
    {
        $this->myLog($cart->id, 'getOrderShippingCost : start');

        # drop-off site
        $drop_off_pudo = Tools::jsonDecode(str_replace('|', '"', Configuration::get('LNP2_DROP_OFF_SITE')), true);
        if (!$drop_off_pudo) {
            $this->myLog($cart->id, 'getOrderShippingCost : DropOff Pudo empty');

            return false;
        }

//        if (!$this->isDropOffPudoAvailable()) {
//            $this->myLog($cart->id, 'getOrderShippingCost : DropOff Pudo not available');
//
//            return false;
//        }

        if (!$this->isCartEligible($cart)) {
            $this->myLog($cart->id, 'getOrderShippingCost : Cart not eligible');

            return false;
        }

        //if (!$cart || !$cart->id_carrier) # cannot use this test since sometimes id_carrier == 0 for strange reasons
        if (!$cart) {
            $this->myLog($cart->id, 'getOrderShippingCost : $cart is NOT available > return false');

            return false;
        }


        if (!isset($this->context->cookie->cart_lnp2_site)) {

            $this->myLog($cart->id, 'getOrderShippingCost : COOKIE is NOT available > try SQL');

            $lnp_cart = Lnp2Cart::getLnpCartByCartId($cart->id);

            if ($lnp_cart) {
                $pudo       = array();
                $pudo['id'] = $lnp_cart->pudo_id;
                $this->myLog($cart->id, 'getOrderShippingCost : SQL result : pudo id = '.$pudo['id']);
            }

        } else {
            $pudo = Tools::jsonDecode($this->context->cookie->cart_lnp2_site, true);
            $this->myLog($cart->id, 'getOrderShippingCost : COOKIE result : pudo id = '.$pudo['id']);
        }

        $additional_cost = 0;
        if (isset($pudo) && $pudo) {
            $corse_price = (float)Configuration::get('LNP2_OPTIONS_CORSE');

            if (-1 !== $corse_price) {
                if (isset($pudo['pudo_zip_code']) && (int)$pudo['pudo_zip_code'] >= 20000 && (int)$pudo['pudo_zip_code'] < 22000) {
                    $additional_cost += $corse_price;
                    $this->context->cookie->__set('lnp2_price_add', true);
                } elseif (isset($pudo['zipCode']) && (int)$pudo['zipCode'] >= 20000 && (int)$pudo['zipCode'] < 22000) {
                    $additional_cost += $corse_price;
                    $this->context->cookie->__set('lnp2_price_add', true);
                } else {
                    $this->context->cookie->__unset('lnp2_price_add');
                }
            }
        }

        # if $shipping_cost == -1, we get the price from cart with prestashop method.
        if (-1 == (float)$shipping_cost) {
            $shipping_cost = $cart->getTotalShippingCost();
        }
        $carrier = new Carrier(Configuration::get('LNP2_CARRIER_ID'));
        $tax     = new Tax($carrier->getIdTaxRulesGroup());
        if ($tax->rate) {
            # if a tax is defined for the carrier, we use a additional price for corse in duty-free.
            $additional_cost = ($additional_cost / (100 + $tax->rate)) * 100;
        }

        $total_price = (float)$shipping_cost + $additional_cost;

        $this->myLog($cart->id, 'getOrderShippingCost : all fine, returning price : '.$total_price);

        return $total_price;
    }

    public function getOrderShippingCostExternal($params)
    {
        return;
    }

    /* For PS 1.5 methods */

    public function getExcludedProductsPageControllerTitles()
    {
        return array(
            'id_product'    => $this->l('ID'),
            'image'         => $this->l('Photo'),
            'name'          => $this->l('Name'),
            'reference'     => $this->l('Reference'),
            'shopname'      => $this->l('Default shop:'),
            'name_category' => $this->l('Category'),
            'price'         => $this->l('Base price'),
            'price_final'   => $this->l('Final price'),
            'sav_quantity'  => $this->l('Quantity'),
            'active'        => $this->l('Status'),
        );

    }

    public function getLabelPageControllerTitles()
    {
        return array(
            'id_order' => $this->l('ID'),
            'customer' => $this->l('Client'),
            'payment'  => $this->l('Payment'),
            'status'   => $this->l('Status'),
            'date_add' => $this->l('Date'),
        );
    }

    public function getPreparationPageControllerTitles()
    {
        return array(
            'id_order' => $this->l('ID'),
            'customer' => $this->l('Client'),
            'payment'  => $this->l('Payment'),
            'status'   => $this->l('Status'),
            'date_add' => $this->l('Date'),
        );
    }

    public function getOrderPageControllerTitles()
    {
        return array(
            'id_order'        => $this->l('ID'),
            'reference'       => $this->l('Reference'),
            'customer'        => $this->l('Client'),
            'total'           => $this->l('Total'),
            'status'          => $this->l('Status'),
            'date_add'        => $this->l('Date'),
            'tracking_number' => $this->l('Tracking Number'),
            'delivery_name'   => $this->l('Parcelshop'),
            'total_shipping'  => $this->l('Delivery costs (paid by the customer)'),
        );
    }

    /* END For PS 1.5 methods */

    /*
     * private methods
     *
     */
    private function _getUrl($extra_vars = array())
    {
        $url_vars = array(
            'controller'  => Tools::getValue('controller'),
            'configure'   => Tools::getValue('configure'),
            'token'       => Tools::getValue('token'),
            'tab_module'  => Tools::getValue('tab_module'),
            'module_name' => Tools::getValue('module_name'),
        );

        return 'index.php?'.http_build_query(array_merge($url_vars, $extra_vars));
    }

    /**
     * Save log in logs/ if LNP2_LOGS is defined
     * @param $id_cart
     * @param $message
     */
    private function myLog($id_cart, $message)
    {
        $logs_activitated = (bool)Configuration::get('LNP2_LOGS');
        if ($logs_activitated) {
            $filename = 'log-id_cart-'.$id_cart.'.txt';
            $handle   = fopen(dirname(__FILE__).'/logs/'.$filename, 'a+');
            fwrite($handle, '['.date('Y-m-d H:i:s').']{'.$_SERVER['REMOTE_ADDR'].'} '.$message."\n");
            fclose($handle);
        }
    }

    public function getWs()
    {
        if ($this->ws === null || !($this->ws instanceof Lnp2PickUpWebService)) {
            $this->ws = new Lnp2PickUpWebService($this->getURL());
        }

        return $this->ws;
    }

    /**
     * Return a countryCode for PickUp
     * @param $ps_country_code
     * @return string
     */
    public function psCodeToPuCodeCountry($ps_country_code)
    {
        $rows = file(dirname(__FILE__).'/data/iso3to2.csv');
        foreach ($rows as $row) {
            if (Tools::strtoupper(Tools::substr($row, 4, 2)) == Tools::strtoupper($ps_country_code)) {
                return Tools::strtoupper(Tools::substr($rows, 0, 3));
            }
        }

        return null;
    }

    public function getCountryNameFromPU($pu_country_code, $lang_id = -1)
    {
        if ($lang_id < 0) {
            $context = Context::getContext();
            $lang_id = $context->language->id;
        }

        return Country::getNameById($lang_id, Country::getByIso($this->puCodeToPsCodeCountry($pu_country_code)));
    }

    /**
     * Return a countryCode for Prestashop
     * @param $pu_country_code
     * @return string
     */
    public function puCodeToPsCodeCountry($pu_country_code)
    {
        $rows = file(dirname(__FILE__).'/data/iso3to2.csv');
        foreach ($rows as $row) {
            if (Tools::strtoupper(Tools::substr($row, 0, 3)) == Tools::strtoupper($pu_country_code)) {
                return Tools::strtoupper(Tools::substr($row, 4, 2));
            }
        }

        return null;
    }


    public function generatePDF($pdf_base64, $name = '')
    {
        $binary = base64_decode($pdf_base64);
        if ($name == '') {
            $name = 'order'.time();
        }
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$name.'.pdf"');
        echo $binary;
    }

    /**
     * Return true if country_code is in the list of available countries
     * @param $country_code
     * @return bool
     */
    public static function isCountryCodeAvailable($country_code)
    {
        $countries_available = array('FRA', 'BEL', 'DEU', 'LUX', 'NLD', 'FR', 'BE', 'DE', 'LU', 'NL');

        return in_array($country_code, $countries_available);
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
     * Enregistre dans /modules/lanavettepickup/logs/debug.log
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
        $module_name = "lanavettepickup";
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
