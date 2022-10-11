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

if (!defined('MODULE_AMAZON')) {
    define('MODULE_AMAZON', 'amazon');
}


require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.product.class.php');
require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.context.class.php');
require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.configuration.class.php');

@define('AMAZON_EXPERIMENTAL_FEATURES', in_array($_SERVER['SERVER_ADDR'], array('91.121.46.68', 'w.x.y.z')) || isset($_SERVER['DropBox']));

class Amazon extends Module
{
    const MARKETPLACE = 'amazon';

    const ADD = 'a';
    const REMOVE = 'd';
    const UPDATE = 'u';
    const REPRICE = 'r';

    const FORMAT_TITLE = 1;
    const FORMAT_MANUFACTURER_TITLE = 2;
    const FORMAT_MANUFACTURER_TITLE_REFERENCE = 3;

    const FIELD_DESCRIPTION_SHORT = 'description_short';
    const FIELD_DESCRIPTION_LONG = 'description';
    const FIELD_DESCRIPTION_BOTH = 'both';
    const FIELD_DESCRIPTION_NONE = 'none';

    const BULLET_POINT_STRATEGY_ATTRIBUTES = 1;
    const BULLET_POINT_STRATEGY_ATTRIBUTES_FEATURES = 2;
    const BULLET_POINT_STRATEGY_FEATURES = 3;
    const BULLET_POINT_STRATEGY_DESC = 4;
    const BULLET_POINT_STRATEGY_DESC_ATTRIBUTES_FEATURES = 5;
    const BULLET_POINT_STRATEGY_DESC_FEATURES = 6;

    const ACCOUNT_TYPE_GLOBAL = 1;
    const ACCOUNT_TYPE_INDIVIDUAL = 2;

    const PAYMENT_METHOD_TITLE = 'Amazon MarketPlace';

    const ENABLE_EXPERIMENTAL_FEATURES = AMAZON_EXPERIMENTAL_FEATURES;

    const SHIPPING_OVERRIDE_ADDITIVE = 'Additive';
    const SHIPPING_OVERRIDE_EXCLUSIVE = 'Exclusive';

    const EXEMPTION_NONE = 0;
    const EXEMPTION_COMPATIBILITY = 1;  /*For backward compatibility*/
    const EXEMPTION_MODEL_NUMBER = 2;
    const EXEMPTION_MODEL_NAME = 3;
    const EXEMPTION_MFR_PART_NUMBER = 4;
    const EXEMPTION_CATALOG_NUMBER = 5;
    const EXEMPTION_STYLE_NUMBER = 6;
    const EXEMPTION_ATTR_EAN = 7;
    const EXEMPTION_GENERIC = 10;

    const FBA_STOCK_SWITCH = 1;
    const FBA_STOCK_SYNCH = 2;

    const FBA_NOTIFICATION_SHOP = 1;
    const FBA_NOTIFICATION_CUSTOMER = 2;
    const FBA_NOTIFICATION_BOTH = 3;

    const ORDER_STATE_STANDARD = 'STD';
    const ORDER_STATE_PREORDER = 'PRE';
    const ORDER_STATE_PRIMEORDER = 'PRIME';

    const AFN = 'AFN';
    const MFN = 'MFN';

    const LENGTH_TITLE = 500;
    const LENGTH_BULLET_POINT = 500;
    const LENGTH_DESCRIPTION = 2000;

    const LF = "\n";
    const BR = '<br />';
    const TEMPLATE_HEADER = 1;
    const TEMPLATE_FOOTER = 2;
    const TEMPLATE_LICENSE = 3;
    const TEMPLATE_VALIDATE = 4;
    const TEMPLATE_CONFIGURE = 5;
    const TEMPLATE_TABS = 10;
    const TEMPLATE_TAB_SETTINGS = 19;
    const TEMPLATE_TAB_TOOLS = 20;
    const TEMPLATE_TAB_CRON = 21;
    const TEMPLATE_TAB_FILTERS = 22;
    const TEMPLATE_TAB_PARAMETERS = 23;
    const TEMPLATE_TAB_CATEGORIES = 24;
    const TEMPLATE_TAB_PROFILES = 25;
    const TEMPLATE_TAB_MAPPING = 26;
    const TEMPLATE_TAB_INFO = 27;
    const TEMPLATE_TAB_SHIPPING = 28;
    const TEMPLATE_TAB_MESSAGING = 29;
    const TEMPLATE_TAB_FBA = 30;
    const TEMPLATE_TAB_FEATURES = 31;
    const TEMPLATE_TAB_REPRICING = 32;
    const TEMPLATE_ORDER_DETAIL = 42;
    const TEMPLATE_TAB_AMAZON = 43;
    const TEMPLATE_TAB_GLOSSARY = 44;

    private static $templates = array(
        self::TEMPLATE_TABS => 'views/templates/admin/configure/tabs.tpl',
        self::TEMPLATE_HEADER => 'views/templates/admin/configure/header.tpl',
        self::TEMPLATE_FOOTER => 'views/templates/admin/configure/footer.tpl',
        self::TEMPLATE_VALIDATE => 'views/templates/admin/configure/validate.tpl',
        self::TEMPLATE_CONFIGURE => 'views/templates/admin/configure/configure.tpl',
        self::TEMPLATE_LICENSE => 'views/templates/admin/configure/license.tpl',
        self::TEMPLATE_TAB_SETTINGS => 'views/templates/admin/configure/settings.tab.tpl',
        self::TEMPLATE_TAB_TOOLS => 'views/templates/admin/configure/tools.tab.tpl',
        self::TEMPLATE_TAB_CRON => 'views/templates/admin/configure/cron.tab.tpl',
        self::TEMPLATE_TAB_FILTERS => 'views/templates/admin/configure/filters.tab.tpl',
        self::TEMPLATE_TAB_PARAMETERS => 'views/templates/admin/configure/parameters.tab.tpl',
        self::TEMPLATE_TAB_PROFILES => 'views/templates/admin/configure/profiles.tab.tpl',
        self::TEMPLATE_TAB_CATEGORIES => 'views/templates/admin/configure/categories.tab.tpl',
        self::TEMPLATE_TAB_MAPPING => 'views/templates/admin/configure/mapping.tab.tpl',
        self::TEMPLATE_TAB_INFO => 'views/templates/admin/configure/informations.tab.tpl',
        self::TEMPLATE_TAB_FEATURES => 'views/templates/admin/configure/features.tab.tpl',
        self::TEMPLATE_TAB_SHIPPING => 'views/templates/admin/configure/shipping.tab.tpl',
        self::TEMPLATE_TAB_MESSAGING => 'views/templates/admin/configure/messaging.tab.tpl',
        self::TEMPLATE_TAB_FBA => 'views/templates/admin/configure/fba.tab.tpl',
        self::TEMPLATE_TAB_REPRICING => 'views/templates/admin/configure/repricing.tab.tpl',
        self::TEMPLATE_TAB_AMAZON => 'views/templates/admin/configure/amazon.tab.tpl',
        self::TEMPLATE_TAB_GLOSSARY => 'views/templates/admin/configure/glossary.tpl',
        self::TEMPLATE_ORDER_DETAIL => 'views/templates/admin/AdminOrderDetailAmazon.tpl'
    );

    const ROUNDING_ONE_DIGIT = 1;
    const ROUNDING_TWO_DIGITS = 2;
    const ROUNDING_SMART = 3;
    const ROUNDING_NONE = 4;

    const SORT_ORDER_FIRSTNAME_LASTNAME = 1;
    const SORT_ORDER_LASTNAME_FIRSTNAME = 2;

    const MAX_PROFILES = 10;

    const RECOMMENDED_IMAGE_SIZE = 1000;

    /**
     * Table name definition
     */
    const TABLE_MARKETPLACE_STRATEGIES = 'marketplace_strategies';
    const TABLE_MARKETPLACE_ACTIONS = 'marketplace_product_action';
    const TABLE_MARKETPLACE_CONFIGURATION = 'marketplace_configuration';
    const TABLE_MARKETPLACE_NEW_CONFIGURATION = 'amazon_configuration';
    const TABLE_MARKETPLACE_ORDERS = 'marketplace_orders';
    const TABLE_MARKETPLACE_ORDER_ITEMS = 'marketplace_order_items';
    const TABLE_MARKETPLACE_OPTIONS = 'marketplace_product_option';
    const TABLE_MARKETPLACE_ORDER_ADDRESS = 'marketplace_order_address';
    const TABLE_MARKETPLACE_STATS = 'marketplace_stats';
    const TABLE_MARKETPLACE_VAT = 'marketplace_vat_report';
    const TABLE_MARKETPLACE_TAXES = 'marketplace_taxes';

    /**
     * Configuration key definition
     */
    const CONFIG_MASTER = 'MASTER';
    const CONFIG_DEFAULT_TAX_RULE_FOR_MP = 'DEFAULT_TAX_RULE';

    const TRASH_DOMAIN = 'amazon.mp.common-services.com';

    public $id_lang;
    public $name = 'amazon';

    public $shipping_overrides_std = array();
    public $shipping_overrides_exp = array();
    public $extra_carrier_codes    = array();

    public $carrier_fba = array(
        'Standard',
        'Expedited',
        'Priority'
    );

    public $path;
    public $url;
    public $base;
    public $images;
    public $ps16x = false;
    public $ps17x = false;
    public $ps15x = false;
    public $ps14x = false;

    private $_html       = '';
    private $_postErrors = array();
    private $config      = array();
    private $profiles    = null;

    private static $carriers = null;

    private $_config     = array(
        'AMAZON_CURRENT_VERSION' => null,
        'AMAZON_ACTIVE' => null,
        'AMAZON_EMPLOYEE' => null,
        'AMAZON_MERCHANT_ID' => null,
        'AMAZON_MARKETPLACE_ID' => null,
        'AMAZON_AWS_KEY_ID' => null,
        'AMAZON_SECRET_KEY' => null,
        'AMAZON_CURRENCY' => null,
        'AMAZON_REGION' => null,
        'AMAZON_OUT_OF_STOCK' => null,
        'AMAZON_PRICE_RULE' => null,
        'AMAZON_CUSTOMER_ID' => null,
        'AMAZON_CARRIER' => null, /* Prestashop Side Carrier*/
        'AMAZON_AMAZON_CARRIER' => null, /* Amazon Side Carrier*/
        /*Orders Statuses*/
        'AMAZON_ORDER_STATE' => null,
        'AMAZON_PREPARATION_STATE' => null,
        'AMAZON_SENT_STATE' => null,
        'AMAZON_CANCELED_STATE' => null,
        'AMAZON_CARRIER_DEFAULT' => null,
        /*Amazon Europe*/
        'AMAZON_MASTER' => null,
        /*Options*/
        'AMAZON_EMAIL' => null,
        'AMAZON_DEBUG_MODE' => null,
        'AMAZON_FIELD' => null,
        'AMAZON_USE_ASIN' => null,
        'AMAZON_STOCK_ONLY' => null,
        'AMAZON_PRICES_ONLY' => null,
        'AMAZON_PRICE_ROUNDING' => null,
        'AMAZON_SAFE_ENCODING' => null,
        // Aug-23-2018: Remove Carriers/Module option
        /*Generic Parameters*/
        'AMAZON_SPECIALS' => null,
        /*2014-06-28*/
        'AMAZON_SPECIALS_APPLY_RULES' => null,
        'AMAZON_PREORDER' => null,
        'AMAZON_TAXES' => null,
        'AMAZON_DELETE_PRODUCTS' => null,
        'AMAZON_UPDATE_PRODUCTS' => null,
        'AMAZON_HTML_DESCRIPTIONS' => null,
        /*Reports*/
        'AMAZON_REPORT_ID' => null,
        /*Hidden Settings*/
        'AMAZON_HIDDEN_SETTINGS' => false,
        /*Shipping Rules for trader module*/
        'AMAZON_SHIPPING_RULES' => null,
        /*Condition*/
        'AMAZON_CONDITION_MAP' => null,
        /*Account Type*/
        'AMAZON_ACCOUNT_TYPE' => false,
        'AMAZON_TITLE_FORMAT' => null,
        'AMAZON_AUTO_CREATE' => null,
        'AMAZON_IMAGE_TYPE' => null,
        'AMAZON_DESCRIPTION_FIELD' => self::FIELD_DESCRIPTION_LONG,
        /*Shipping Overrides*/
        'AMAZON_SHIPPING_OVERRIDES_STD' => null,
        'AMAZON_SHIPPING_OVERRIDES_EXP' => null,
        /*Crazy Features*/
        'AMAZON_BRUTE_FORCE' => false,
        /*Module Environment*/
        'AMAZON_API_TIMER' => null, /*use to store the antithrottling timer*/
        /*Mappings*/
        'AMAZON_MAPPING' => null,
        /*Cron Variables*/
        'AMAZON_CRON_TOKEN' => null,
        /*FBA Settings*/
        'AMAZON_FBA_ORDER_STATE' => null,
        'AMAZON_FBA_SENT_STATE' => null,
        'AMAZON_FBA_MULTICHANNEL_STATE' => null,
        'AMAZON_FBA_MULTICHANNEL_SENT' => null,
        'AMAZON_FBA_PRICE_FORMULA' => null,
        'AMAZON_FBA_MULTICHANNEL' => null,
        'AMAZON_FBA_MULTICHANNEL_AUTO' => null,
        'AMAZON_FBA_DECREASE_STOCK' => null,
        'AMAZON_CARRIER_MULTICHANNEL' => null,
        'AMAZON_WAREHOUSE' => null,
        /*2013-08-17*/
        'AMAZON_EXCLUDED_MANUFACTURERS' => null,
        'AMAZON_EXCLUDED_SUPPLIERS' => null,
        /* 2014-06-17 */
        'AMAZON_PRICE_FILTER' => null,
        /*2013-09-15*/
        'AMAZON_MAIL_INVOICE' => null,
        'AMAZON_MAIL_REVIEW' => null,
        /*2013-12-21*/
        'AMAZON_CONTEXT' => null,
        /*2013-12-24 - Shipping configuration*/
        'AMAZON_SHIPPING' => null,
        'AMAZON_SHIPPING_METHODS' => null,
        'AMAZON_PRODUCTS_CREATION' => null,
        /*2014-06-16*/
        'AMAZON_WIZARD_MATCHING_STATE' => null,
        'AMAZON_WIZARD_CREATION_STATE' => null,
        'AMAZON_INACTIVE_LANGUAGES' => null,
        /*2015-04-25*/
        'AMAZON_FBA_STOCK_BEHAVIOUR' => null,
        'AMAZON_FEATURES' => null,
        /*2015-09-09*/
        'AMAZON_PRODUCT_OPTION_FIELDS' => null,
        'AMAZON_INSTANT_TOKEN' => null,
        /*2016-03-09*/
        'AMAZON_FBA_NOTIFICATION' => null,
        /*2013-03-24*/
        'AMAZON_SORT_ORDER' => null,
        /*2016-04-14*/
        'AMAZON_CUSTOMER_THREAD' => array(),
        /*2017-06-22*/
        'AMAZON_CHECKSTOCK_REQUEST' => '',
        'AMAZON_FIXORDERS_REQUEST' => ''
    );
    private $_platforms  = array(
        'au' => 'Australia',
        'ca' => 'Canada',
        'cn' => 'China',
        'de' => 'Germany',
        'es' => 'Spain',
        'fr' => 'France',
        'it' => 'Italy',
        'in' => 'India',
        'jp' => 'Japan',
        'br' => 'Brazil',
        'mx' => 'Mexico',
        'uk' => 'United Kingdom',
        'us' => 'United States'
    );
    public static $conditions = array(
        11 => 'New',
        1 => 'UsedLikeNew',
        2 => 'UsedVeryGood',
        3 => 'UsedGood',
        4 => 'UsedAcceptable',
        5 => 'CollectibleLikeNew',
        6 => 'CollectibleVeryGood',
        7 => 'CollectibleGood',
        8 => 'CollectibleAcceptable',
        98 => 'Refurbished', // condition code unknown yet
        99 => 'Club' // condition code unknown yet
    );

    public static $features        = array();
    public static $features_values = array();

    public static $attributes        = array();
    public static $attributes_groups = array();

    public static $amazon_default_features = array(
        'module',
        'creation',
        'offers',
        'wizard',
        'prices_rules',
        'second_hand',
        'filters',
        'import_products',
        'amazon_europe',
        'worldwide',
        'messaging',
        'smart_shipping',
        'shipping',
        'tools',
        'fba',
        'repricing',
        'orders',
        'orders_reports',
        'gcid',
        'expert_mode',
        'demo_mode',
        'remote_cart',
        'cancel_orders',
        'business'
    );

    public $amazon_features = null;

    public static $amazon_default_features_default = array(
        'offers'         => true,
        'prices_rules'   => true,
        'orders'         => true,
        'orders_reports' => true
    );

    public static $mwsops_required = array(
            'ATVPDKIKX0DER', // US
            'A13V1IB3VIYZZH', // France
            'A1RKKUPIHCS9HS', // Spain
            'A1PA6795UKMFR9', // Germany
            'APJ6JRA9NG5V4',  // Italy
            'A1F83G8C2ARO7P', // Uk
            'A2Q3Y263D00KWC', // Brasil
            'A2EUQ1WTGCTBG2', // Canada
            'A1AM78C64UM0Y8', // Mexico
            'A39IBJ37TRP1C6', // Australia
            'AAHKV2X7AFYLW', // China
        );
    public static $debug_mode = false;
    
    public static $usefull_urls = array();

    public function __construct()
    {
        $this->page = basename(__FILE__, '.php');
        $this->tab = 'market_place';
        $this->version = '4.4.094';
        $this->author = 'Common-Services';
        $this->author_address = '0x96116FE33A6268AE9E878Dbc609A02BdCcc285E0';
        $this->module_key = 'bd88475a00b7e8a2c2c3c8b89680922d';
        //Amazon Regular: bd88475a00b7e8a2c2c3c8b89680922d //Amazon Lite: 95bf55040245698af47e08725394866e
        $this->bootstrap = true;
        $this->need_instance = false;
        $this->name = 'amazon';
        $this->path = _PS_MODULE_DIR_.$this->name.'/';

        parent::__construct();


        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
            require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }

        $this->path_pdf = $this->path.'pdf/';
        $this->path_mail = $this->path.'mails/';

        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.tools.class.php');
        require_once(_PS_MODULE_DIR_.'/amazon/validate/AmazonXSD.php'); //gets code for Amazon XML Schemas
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.tools.class.php');
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.support.class.php');
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.specificfield.class.php');
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.valid_values.class.php');
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.csv.references.class.php');
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.carrier.class.php');
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.repricing.class.php');
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.taxes.class.php');
    
        $this->url = AmazonTools::getShopUrl();
        $this->displayName = $this->l('Amazon');
        $this->description = $this->l('This extension allow to sell products and retrieve orders from the Amazon MarketPlace');
        $this->images = $this->url.'views/img/';
        $this->initContext();

        $this->amazon_features = $this->getAmazonFeatures();

        if (isset($this->amazon_features['name'])) {
            $this->displayName = $this->amazon_features['name'];
        }

        self::$debug_mode = (bool)Configuration::get('AMAZON_DEBUG_MODE') || (bool)Tools::getValue('debug');
    }

    private function initContext()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->ps17x = $this->ps16x = true;
        } elseif (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $this->ps16x = true;
        } elseif (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $this->ps15x = true;
        } else {
            $this->ps14x = true;
        }

        $this->context = Context::getContext();

        $id_lang = (int)Tools::getValue('id_lang');

        if ($id_lang) {
            // id_lang for ajax script

            $language = new Language($id_lang);

            if (Validate::isLoadedObject($language) && $this->context) {
                $this->context->language = $language;
            }
        }

        if (isset($this->context->language->id)) {
            $this->id_lang = (int)$this->context->language->id;
        } else {
            $this->id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        }
    }

    public static function getAmazonFeatures()
    {
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.settings.class.php');

        // TODO: Implement allowed features
        $allowed_features = AmazonSettings::getSubscribedFeatures();
        $amazon_features = AmazonConfiguration::get('FEATURES');

        if ($amazon_features === false) {
            $amazon_features = array_fill_keys(self::$amazon_default_features, false);

            foreach (array_keys(self::$amazon_default_features_default) as $feature_default) {
                $amazon_features[$feature_default] = true;
            }
        } elseif (is_array($amazon_features)) {
            $amazon_features = array_merge(array_fill_keys(self::$amazon_default_features, false), $amazon_features);
        } else {
            $amazon_features = array_merge(array_fill_keys(self::$amazon_default_features, false));
        }

        if (is_array($allowed_features)&& count($allowed_features)) {
            foreach ($amazon_features as $feature => $value) {
                if (array_key_exists($feature, $allowed_features) && $allowed_features[$feature] == false) {
                    $amazon_features[$feature] = $allowed_features[$feature];
                }
            }
            $amazon_features['module'] = $allowed_features['module'];
            if (isset($allowed_features['name'])) {
                $amazon_features['name'] = $allowed_features['name'];
            }
        }

        return ($amazon_features);
    }

    public function install()
    {
        $pass = true;

        foreach ($this->_config as $key => $value) {
            if (is_null($value)) {
                $value = '';
            }

            if (is_array($value)) {
                $value = AmazonTools::encode(serialize($value));
            }

            if (!Configuration::updateValue($key, $value)) {
                $this->_errors[] = sprintf('%s - key: %s, value: %s', $this->l('Unable to install : Some configuration values'), $key, nl2br(print_r($value, true)));
                $pass = false;
            }
        }
        if (!$pass) {
            $this->_errors[] = $this->l('Unable to install : Some configuration values');
        }

        if (!parent::install()) {
            $this->_errors[] = $this->l('Unable to install: parent()') && $pass = false;
        }

        $this->tabSetup(Amazon::ADD);

        if (!$this->createCustomer()) {
            $this->_errors[] = $this->l('Unable to install: createCustomer()');
            $pass = false;
        }
        if (!$this->addMarketPlaceTables()) {
            $this->_errors[] = $this->l('Unable to install: addMarketPlaceTables()');
            $pass = false;
        }
        if (!$this->addMarketPlaceField()) {
            $this->_errors[] = $this->l('Unable to install: addMarketPlaceField()');
            $pass = false;
        }
        if (!$this->addConfigurationTable()) {
            $this->_errors[] = $this->l('Unable to install: _addConfigurationTable()');
            $pass = false;
        }

        // Save initial context
        if ($pass) {
            require_once(dirname(__FILE__).'/classes/amazon.context.class.php');
            $pass = AmazonContext::save($this->context);
        }
        // Initialize instant token
        $this->setInstantToken();

        // Hooks
        $this->_hookSetup(self::ADD);

        return ((bool)$pass);
    }

    public function tabSetup($action)
    {
        $pass = true;
        $adminOrders = $this->ps17x ? 'AdminParentOrders'  : 'AdminOrders';
        
        // Adding Tab
        switch ($action) {
            case Amazon::ADD:
                // For PS 1.5+
                if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                    if (Tab::getIdFromClassName('AdminAmazonProducts') && Tab::getIdFromClassName('AdminAmazonOrders') && Tab::getIdFromClassName('AdminAmazonStats')) {
                        //
                        return (true);
                    }
                    
                    if (!$this->installModuleTab('AdminAmazonProducts', 'Amazon', Tab::getIdFromClassName('AdminCatalog'))) {
                        $this->_errors[] = $this->l('Unable to install: ProductsAmazon)');
                        $pass = false;
                    }
                    if (!$this->installModuleTab('AdminAmazonOrders', 'Amazon', Tab::getIdFromClassName($adminOrders))) {
                        $this->_errors[] = $this->l('Unable to install: OrdersAmazon');
                        $pass = false;
                    }
                    if (self::ENABLE_EXPERIMENTAL_FEATURES) {
                        if (!$this->installModuleTab('AdminAmazonStats', 'Amazon', Tab::getIdFromClassName('AdminStats'))) {
                            $this->_errors[] = $this->l('Unable to install: StatsAmazon');
                            $pass = false;
                        }
                    }
                } else {
                    // For PS < 1.5
                    if (Tab::getIdFromClassName('ProductsAmazon') && Tab::getIdFromClassName('OrdersAmazon') && Tab::getIdFromClassName('StatsAmazon')) {
                        //
                        return (true);
                    }

                    if (!$this->installModuleTab('ProductsAmazon', 'Amazon', Tab::getIdFromClassName('AdminCatalog'))) {
                        $this->_errors[] = $this->l('Unable to install: ProductsAmazon)');
                        $pass = false;
                    }
                    if (!$this->installModuleTab('OrdersAmazon', 'Amazon', Tab::getIdFromClassName($adminOrders))) {
                        $this->_errors[] = $this->l('Unable to install: OrdersAmazon');
                        $pass = false;
                    }
                    if (!$this->installModuleTab('StatsAmazon', 'Amazon', Tab::getIdFromClassName('AdminStats'))) {
                        $this->_errors[] = $this->l('Unable to install: StatsAmazon');
                        $pass = false;
                    }
                }
                break;
            case Amazon::UPDATE:
                if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                    // Removing Old AdminTabs
                    //
                    if (Tab::getIdFromClassName('ProductsAmazon') && Tab::getIdFromClassName('OrdersAmazon')) {
                        if (!$this->uninstallModuleTab('ProductsAmazon')) {
                            $this->_errors[] = $this->l('Unable to uninstall: ProductsAmazon Tab');
                            $pass = false;
                        }
                        if (!$this->uninstallModuleTab('OrdersAmazon')) {
                            $this->_errors[] = $this->l('Unable to uninstall: OrdersAmazon Tab');
                            $pass = false;
                        }
                    }

                    if (Tab::getIdFromClassName('StatsAmazon')) {
                        if (!$this->uninstallModuleTab('StatsAmazon')) {
                            $this->_errors[] = $this->l('Unable to uninstall: StatsAmazon Tab');
                            $pass = false;
                        }
                    }

                    // Adding New
                    return ($this->tabSetup(Amazon::ADD));
                }
                break;
            case Amazon::REMOVE:
                // Removing New AdminTabs
                //
                if (Tab::getIdFromClassName('AdminAmazonOrders') && Tab::getIdFromClassName('AdminAmazonProducts')) {
                    if (!$this->uninstallModuleTab('AdminAmazonProducts')) {
                        $this->_errors[] = $this->l('Unable to uninstall: ProductsAmazon Tab');
                        $pass = false;
                    }
                    if (!$this->uninstallModuleTab('AdminAmazonOrders')) {
                        $this->_errors[] = $this->l('Unable to uninstall: OrdersAmazon Tab');
                        $pass = false;
                    }
                }
                if (self::ENABLE_EXPERIMENTAL_FEATURES) {
                    if (Tab::getIdFromClassName('AdminAmazonStats')) {
                        if (!$this->uninstallModuleTab('AdminAmazonStats')) {
                            $this->_errors[] = $this->l('Unable to uninstall: StatsAmazon Tab');
                            $pass = false;
                        }
                    }
                }
                // Removing Old AdminTabs
                //
                if (Tab::getIdFromClassName('ProductsAmazon') && Tab::getIdFromClassName('OrdersAmazon')) {
                    if (!$this->uninstallModuleTab('ProductsAmazon')) {
                        $this->_errors[] = $this->l('Unable to uninstall: ProductsAmazon Tab');
                        $pass = false;
                    }
                    if (!$this->uninstallModuleTab('OrdersAmazon')) {
                        $this->_errors[] = $this->l('Unable to uninstall: OrdersAmazon Tab');
                        $pass = false;
                    }
                }
                if (Tab::getIdFromClassName('StatsAmazon')) {
                    if (!$this->uninstallModuleTab('StatsAmazon')) {
                        $this->_errors[] = $this->l('Unable to uninstall: StatsAmazon Tab');
                        $pass = false;
                    }
                }
                break;
        }

        return ($pass);
    }

    private function installModuleTab($tabClass = null, $tabName = null, $idTabParent = 0)
    {
        $tabNameLang = array();

        if (Tab::getIdFromClassName($tabClass)) {
            return (true);
        }

        foreach (Language::getLanguages(false) as $language) {
            $tabNameLang[$language['id_lang']] = $tabName;
        }

        $tab = new Tab();
        $tab->name = $tabNameLang;
        $tab->class_name = $tabClass;
        $tab->module = $this->name;
        $tab->id_parent = (int)$idTabParent;

        return ($tab->save());
    }

    private function uninstallModuleTab($tabClass)
    {
        $pass = true;
        @unlink(_PS_IMG_DIR_.'t/'.$tabClass.'.gif');
        $idTab = Tab::getIdFromClassName($tabClass);
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $pass = $tab->delete();
        }

        return ($pass);
    }

    private function createCustomer()
    {
        if (($id_customer = (int)Configuration::get('AMAZON_CUSTOMER_ID'))) {
            $customer = new Customer($id_customer);

            if (Validate::isLoadedObject($customer)) {
                return ($customer->id);
            }
        }
        $var = explode('@', Configuration::get('PS_SHOP_EMAIL'));
        $max = 10;

        while ($max--) {
            $email = 'no-reply-'.rand().'@'.$var[1];

            if (!Validate::isEmail($email)) {
                return(false);
            }

            if (!Customer::customerExists($email)) {
                $customer = new Customer();
                $customer->firstname = 'Amazon';
                $customer->lastname = 'Amazon Market Place';
                $customer->company = 'Amazon';
                $customer->email = $email;
                $customer->birthday = '1970-01-01';
                $customer->newsletter = false;
                $customer->optin = false;
                $customer->passwd = md5(rand());
                $customer->active = true;
                $customer->date_add = date('Y-m-d H:i:s');
                $customer->date_upd = $customer->date_add;
                $customer->add();

                if (Validate::isLoadedObject($customer)) {
                    Configuration::updateValue('AMAZON_CUSTOMER_ID', $customer->id);
                    return($customer->id);
                } else {
                    return(false);
                }
            }
        }
        return (false);
    }

    private function addMarketPlaceTables()
    {
        $pass = true;
        $errors = null;

        require_once(dirname(__FILE__).'/classes/amazon.order_info.class.php');
        require_once(dirname(__FILE__).'/classes/amazon.order.class.php');

        $currentVersion = Configuration::get('AMAZON_CURRENT_VERSION', null, 0, 0);

        if (!AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_STRATEGIES)) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_STRATEGIES.'` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `id_product` int(11) NOT NULL,
                `id_product_attribute` INT NULL DEFAULT NULL,
                `id_lang` int(11) NOT NULL,
                `minimum_price` float NOT NULL,
                `actual_price` float NOT NULL,
                `target_price` float NOT NULL,
                `gap` float NOT NULL,
                PRIMARY KEY (`id`),
                KEY `id_product` (`id_product`),
                KEY `id_product_lang` (`id_product`,`id_lang`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

            if (!Db::getInstance()->execute($sql)) {
                $errors .= 'ERROR: '.$sql.nl2br(Amazon::LF);
                $errors .= Db::getInstance()->getMsgError();
                $pass = false;
            }
        }

        if (!AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS)) {
            $sql = '
                    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS.'` (
                    `id_product` int(11) NOT NULL,
                    `id_product_attribute` int(11) DEFAULT NULL,
                    `id_lang` int(11) NOT NULL,
                    `sku` varchar(64) DEFAULT NULL,
                    `marketplace` varchar(12) NOT NULL,
                    `action` char(1) NOT NULL,
                    `date_add` datetime DEFAULT NULL,
                    `date_upd` datetime DEFAULT NULL,
                    UNIQUE KEY `id_product` (`id_product`,`id_lang`,`marketplace`,`action`),
                    KEY `id_lang` (`id_lang`,`marketplace`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

            if (!Db::getInstance()->execute($sql)) {
                $errors .= 'ERROR: '.$sql.nl2br("");
                $errors .= Db::getInstance()->getMsgError();
                $pass = false;
            }
        }

        if (!AmazonTools::tableExists(_DB_PREFIX_.self::TABLE_MARKETPLACE_ORDER_ITEMS)) {
            require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.order_item.class.php');

            if (!AmazonOrderItem::createTable()) {
                $errors .= explode(Amazon::LF, AmazonOrderItem::$errors);
                $pass = false;
            }
        }
        if (!AmazonTools::tableExists(_DB_PREFIX_.self::TABLE_MARKETPLACE_ORDER_ADDRESS)) {
            require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.address.class.php');

            if (!AmazonAddress::createTable()) {
                $errors .= explode(Amazon::LF, AmazonAddress::$errors);
                $pass = false;
            }
        }
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.stat.class.php');
        if (!AmazonTools::tableExists(_DB_PREFIX_.self::TABLE_MARKETPLACE_STATS)) {
            if (!AmazonStat::createTable()) {
                $errors .= explode(Amazon::LF, 'Cannot create marketplace_stat table');
                $pass = false;
            }
        } else {
            if (!AmazonStat::addStatTaxColumns()) {
                $errors .= explode(Amazon::LF, 'Cannot update marketplace_stat table');
                $pass = false;
            }
        }
        if (!AmazonTools::tableExists(_DB_PREFIX_.self::TABLE_MARKETPLACE_VAT)) {
            require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.stat.class.php');

            if (!AmazonStat::createVatTable()) {
                $errors .= explode(Amazon::LF, 'Cannot create marketplace_vat table');
                $pass = false;
            }
        }

        if (!AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS)) {
            require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.order.class.php');

            if (!AmazonOrder::createTable()) {
                $errors .= explode(Amazon::LF, AmazonOrder::$errors);
                $pass = false;
            }
        } elseif (!AmazonTools::fieldExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS, 'sales_channel') && !AmazonTools::fieldExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS, 'latest_delivery_date')) {
            require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.order.class.php');

            if (!AmazonOrder::updateTable()) {
                $errors .= explode(Amazon::LF, AmazonOrder::$errors);
                $pass = false;
            }
        }
        if (AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS)) {
            require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.order.class.php');

            if (!AmazonOrder::fixIndex()) {
                $errors .= explode(Amazon::LF, AmazonOrder::$errors);
                $pass = false;
            }
        }
        
        if (AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS)) {
            $sqls = array();

            $fields = array();

            // Amazon Update - Add new fields
            //
            $query = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'`');
            if ($query) {
                foreach ($query as $row) {
                    $fields[$row['Field']] = 1;
                }
            }

            // For versions before 3.8 - Added on 2013-09-21
            //
            if (version_compare($currentVersion, '3.8', '<')) {
                if (!isset($fields['nopexport'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `nopexport` TINYINT NULL DEFAULT NULL AFTER `force`';
                }
                if (!isset($fields['noqexport'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `noqexport` TINYINT NULL DEFAULT NULL AFTER `nopexport`';
                }
                if (!isset($fields['latency'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `latency` TINYINT NULL DEFAULT NULL AFTER `noqexport`';
                }
                if (!isset($fields['disable'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `disable` TINYINT NULL DEFAULT NULL AFTER `latency`';
                }
                if (!isset($fields['price'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `price` FLOAT NULL DEFAULT NULL AFTER `disable`';
                }
                if (!isset($fields['asin1'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `asin1` VARCHAR(16) NULL DEFAULT NULL AFTER `price`';
                }
                if (!isset($fields['asin2'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `asin2` VARCHAR(16) NULL DEFAULT NULL AFTER `asin1`';
                }
                if (!isset($fields['asin3'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `asin3` VARCHAR(16) NULL DEFAULT NULL AFTER `asin2`';
                }
                if (!isset($fields['shipping'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `shipping` FLOAT NULL DEFAULT NULL AFTER `asin3`';
                }
                if (!isset($fields['shipping_type'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `shipping_type` FLOAT NULL DEFAULT NULL AFTER `shipping`';
                }
                if (!isset($fields['fba'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `fba` TINYINT NULL DEFAULT NULL AFTER `noqexport`';
                }
                if (!isset($fields['fba_value'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `fba_value` FLOAT NULL DEFAULT NULL AFTER `fba`';
                }
            }

            // For versions before 3.9 - Added on 2013-09-21
            //
            if (version_compare($currentVersion, '3.9', '<')) {
                if (!isset($fields['bullet_point1'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `bullet_point1` VARCHAR('.self::LENGTH_BULLET_POINT.') NULL DEFAULT NULL AFTER `text`';
                }
                if (!isset($fields['bullet_point2'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `bullet_point2` VARCHAR('.self::LENGTH_BULLET_POINT.') NULL DEFAULT NULL AFTER `bullet_point1`';
                }
                if (!isset($fields['bullet_point3'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `bullet_point3` VARCHAR('.self::LENGTH_BULLET_POINT.') NULL DEFAULT NULL AFTER `bullet_point2`';
                }
                if (!isset($fields['bullet_point4'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `bullet_point4` VARCHAR('.self::LENGTH_BULLET_POINT.') NULL DEFAULT NULL AFTER `bullet_point3`';
                }
                if (!isset($fields['bullet_point5'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `bullet_point5` VARCHAR('.self::LENGTH_BULLET_POINT.') NULL DEFAULT NULL AFTER `bullet_point4`';
                }
                if (!isset($fields['id_product_attribute'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `id_product_attribute` INT(11) NULL DEFAULT NULL AFTER `id_lang`';
                }
            }

            // For versions before 4.0 - Added on 2014-10-15
            //
            if (version_compare($currentVersion, '4.0', '<')) {
                if (!isset($fields['gift_wrap'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `gift_wrap` TINYINT NULL DEFAULT NULL AFTER `shipping_type`';
                }
                if (!isset($fields['gift_message'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `gift_message` TINYINT NULL DEFAULT NULL AFTER `gift_wrap`';
                }
            }

            if (version_compare($currentVersion, '4', '<')) {
                $query = Db::getInstance()->executeS('SHOW INDEX FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'`');

                $ASIN_Index = false;
                $Product_Index = false;
                $Old_Product_Index1 = false;
                $Old_Product_Index2 = false;

                foreach ($query as $table_item) {
                    if ($table_item['Key_name'] === 'ASIN') {
                        $ASIN_Index = true;
                    } elseif ($table_item['Key_name'] === 'id_product') {
                        $Old_Product_Index1 = true;
                    } elseif ($table_item['Key_name'] === 'id_product_lang_attribute') {
                        $Old_Product_Index2 = true;
                    } elseif ($table_item['Key_name'] === 'PRIMARY') {
                        $Product_Index = true;
                    }
                }

                if ($Old_Product_Index1) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` DROP INDEX  `id_product` ';
                }

                if ($Old_Product_Index2) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` DROP INDEX  `id_product_lang_attribute`';
                }

                if (!$ASIN_Index) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD INDEX `ASIN` (  `asin1` )';
                }

                if (!$Product_Index) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD PRIMARY KEY `product_index` (`id_product`, `id_product_attribute`, `id_lang`)';
                }
            }

            // For very older versions - Added on 2013-09-21
            //
            if (version_compare($currentVersion, '3.5', '<')) {
                // For Next updates - for old module installations - the scheme has changed
                //
                if (isset($fields['latency'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` CHANGE  `latency`  `latency` TINYINT NULL DEFAULT NULL';
                }
                if (isset($fields['nopexport'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` CHANGE  `nopexport`  `nopexport` TINYINT NULL DEFAULT NULL';
                }
                if (isset($fields['noqexport'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` CHANGE  `noqexport`  `noqexport` TINYINT NULL DEFAULT NULL';
                }
                if (isset($fields['price'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` CHANGE  `price`  `price` FLOAT NULL DEFAULT NULL';
                }
                if (isset($fields['asin1'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` CHANGE  `asin1`  `asin1` VARCHAR(16) NULL DEFAULT NULL';
                }
                if (isset($fields['asin2'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` CHANGE  `asin2`  `asin2` VARCHAR(16) NULL DEFAULT NULL';
                }
                if (isset($fields['asin3'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` CHANGE  `asin3`  `asin3` VARCHAR(16) NULL DEFAULT NULL';
                }
                if (isset($fields['disable'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` CHANGE  `disable`  `disable` TINYINT NULL DEFAULT NULL';
                }
                if (isset($fields['force'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` CHANGE  `force`  `force` TINYINT NULL DEFAULT NULL';
                }
                if (isset($fields['text'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` CHANGE  `text`  `text` VARCHAR(256) NULL DEFAULT NULL';
                }

                // Added on 2012/11/26
                //
                if (!isset($fields['id_product_attribute'])) {
                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD  `id_product_attribute` INT NULL DEFAULT NULL AFTER `id_lang`';

                    $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'`
                               ADD UNIQUE  `id_product_lang_attribute` (  `id_product` ,  `id_lang` ,  `id_product_attribute` )';
                }
            }

            if (!isset($fields['browsenode'])) {
                $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD `browsenode` varchar(16) DEFAULT NULL AFTER `gift_message`';
            }
            if (!isset($fields['repricing_min'])) {
                $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD `repricing_min` FLOAT NULL DEFAULT NULL AFTER `browsenode`';
            }
            if (!isset($fields['repricing_max'])) {
                $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD `repricing_max` FLOAT NULL DEFAULT NULL AFTER `repricing_min`';
            }
            if (!isset($fields['repricing_gap'])) {
                $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD `repricing_gap` FLOAT NULL DEFAULT NULL AFTER `repricing_max`';
            }
            if (!isset($fields['shipping_group'])) {
                $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD `shipping_group` varchar(32) DEFAULT NULL AFTER `repricing_gap`';
            }
            if (!isset($fields['alternative_title'])) {
                $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD `alternative_title` varchar(255) DEFAULT NULL AFTER `shipping_group`';
            }
            if (!isset($fields['alternative_description'])) {
                $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ADD `alternative_description` TEXT DEFAULT NULL AFTER `alternative_title`';
            }

            foreach ($sqls as $sql) {
                if (!Db::getInstance()->execute($sql)) {
                    $errors .= 'ERROR: '.$sql.nl2br(Amazon::LF);
                    $pass = false;
                }
            }
        } else {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` (
                  `id_product` int(11) NOT NULL,
                  `id_lang` int(11) NOT NULL,
                  `id_product_attribute` int(11) NOT NULL DEFAULT 0,
                  `force` tinyint(4) DEFAULT NULL,
                  `nopexport` tinyint(4) DEFAULT NULL,
                  `noqexport` tinyint(4) DEFAULT NULL,
                  `fba` tinyint(4) DEFAULT NULL,
                  `fba_value` FLOAT DEFAULT NULL,
                  `latency` tinyint(4) DEFAULT NULL,
                  `disable` tinyint(4) DEFAULT NULL,
                  `price` FLOAT DEFAULT NULL,
                  `asin1` varchar(16) DEFAULT NULL,
                  `asin2` varchar(16) DEFAULT NULL,
                  `asin3` varchar(16) DEFAULT NULL,
                  `text` varchar(256) DEFAULT NULL,
                  `bullet_point1` varchar('.self::LENGTH_BULLET_POINT.') DEFAULT NULL,
                  `bullet_point2` varchar('.self::LENGTH_BULLET_POINT.') DEFAULT NULL,
                  `bullet_point3` varchar('.self::LENGTH_BULLET_POINT.') DEFAULT NULL,
                  `bullet_point4` varchar('.self::LENGTH_BULLET_POINT.') DEFAULT NULL,
                  `bullet_point5` varchar('.self::LENGTH_BULLET_POINT.') DEFAULT NULL,
                  `shipping` float DEFAULT NULL,
                  `shipping_type` tinyint(4) DEFAULT NULL,
                  `gift_wrap` tinyint(4) DEFAULT NULL,
                  `gift_message` tinyint(4) DEFAULT NULL,
                  `browsenode` varchar(16) DEFAULT NULL,
                  `repricing_min` FLOAT DEFAULT NULL,
                  `repricing_max` FLOAT DEFAULT NULL,
                  `repricing_gap` FLOAT DEFAULT NULL,
                  `shipping_group` varchar(32) DEFAULT NULL,
                  `alternative_title` varchar(255) DEFAULT NULL,
                  `alternative_description` TEXT DEFAULT NULL,
                  PRIMARY KEY `product_index` (`id_product`, `id_product_attribute`, `id_lang`),
                  KEY `ASIN` (`asin1`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

            if (!Db::getInstance()->execute($sql)) {
                $errors .= 'ERROR: '.$sql.nl2br(Amazon::LF);
                $errors .= Db::getInstance()->getMsgError();
                $pass = false;
            }
        }

        // Amazon Product Options - Save available fields
        //
        $fields = array();
        $query = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'`');
        if ($query) {
            foreach ($query as $row) {
                $fields[] = $row['Field'];
            }
        }

        if (is_array($fields) && count($fields)) {
            Configuration::UpdateValue('AMAZON_PRODUCT_OPTION_FIELDS', implode(',', $fields), false, 0, 0);
        }

        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.valid_values.class.php');

        if (!AmazonValidValues::tableCreate()) {
            $errors .= 'ERROR: Unable to create valid values table'.nl2br(Amazon::LF);
            $errors .= Db::getInstance()->getMsgError();
            $pass = false;
        }

        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.remote_cart.class.php');


        if ($this->amazon_features['remote_cart'] && !AmazonRemoteCart::tableCreate()) {
            $errors .= 'ERROR: Unable to create remote cart table'.nl2br(Amazon::LF);
            $errors .= Db::getInstance()->getMsgError();
            $pass = false;
        }

        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.taxes.class.php');

        if (!AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_TAXES)) {
            if (!AmazonTaxes::createTable()) {
                $errors .= 'ERROR: ';
                $errors .= Db::getInstance()->getMsgError();
                $pass = false;
            } else {
                if (!AmazonTaxes::populatePtc()) {
                    $errors .= 'ERROR: ';
                    $errors .= Db::getInstance()->getMsgError();
                    $pass = false;
                }
            }
        } else {
            $sql = 'SELECT count(ptc) as count FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_TAXES.'`';
            $count = Db::getInstance()->getValue($sql);
            if (!$count) {
                if (!AmazonTaxes::populatePtc()) {
                    $errors .= 'ERROR: ';
                    $errors .= Db::getInstance()->getMsgError();
                    $pass = false;
                }
            }
        }

        if ($errors) {
            $this->_errors[] = $errors;
        }

        return ($pass);
    }

    private function addMarketPlaceField()
    {
        $pass = true;

        // For Updates : Set All Order to checked to avoid to  send many orders statuses to Amazon
        //
        require_once(dirname(__FILE__).'/classes/amazon.order_info.class.php');
        require_once(dirname(__FILE__).'/classes/amazon.order.class.php');

        if (AmazonTools::fieldExists(_DB_PREFIX_.'orders', 'mp_status')) {
            $query = Db::getInstance()->getRow('SELECT count(`id_order`) as `count` FROM `'._DB_PREFIX_.'orders` WHERE `mp_status` = '.AmazonOrder::CHECKED.' AND (`module` = "amazon" OR `module` = "Amazon")');

            if (isset($query['count']) && $query['count'] == 0) {
                Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'orders` SET `mp_status` =  '.(int)AmazonOrder::CHECKED.'  WHERE `module` = "amazon" OR `module` = "Amazon"');
            }
        }
        return ($pass);
    }

    /**
     * Add amazon_configuration table
     * @return bool
     */
    private function addConfigurationTable()
    {
        $config_table      = _DB_PREFIX_ . AmazonConfiguration::$configuration_table;
        $config_lang_table = $config_table . '_lang';
        $result = true;

        if (! AmazonTools::tableExists($config_table)) {
            $sql = "CREATE TABLE `{$config_table}` (
                    `id_configuration` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `id_shop_group` INT(11) UNSIGNED DEFAULT NULL,
                    `id_shop` INT(11) UNSIGNED DEFAULT NULL,
                    `name` VARCHAR(254) NOT NULL,
                    `value` LONGTEXT,
                    `date_add` DATETIME NOT NULL,
                    `date_upd` DATETIME NOT NULL,
                    PRIMARY KEY (`id_configuration`),
                    KEY `name` (`name`),
                    KEY `id_shop` (`id_shop`),
                    KEY `id_shop_group` (`id_shop_group`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

            $result &= Db::getInstance()->execute($sql);
        }

        if (! AmazonTools::tableExists($config_lang_table)) {
            $sql = "CREATE TABLE `{$config_lang_table}` (
                  `id_configuration` int(10) unsigned NOT NULL,
                  `id_lang` int(10) unsigned NOT NULL,
                  `value` text,
                  `date_upd` datetime DEFAULT NULL,
                  PRIMARY KEY (`id_configuration`,`id_lang`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $result &= Db::getInstance()->execute($sql);
        }

        return $result;
    }

    private function _hookSetup($action)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $expectedHooks = array(
                'addproduct',
                'deleteProduct',
                'updateProductAttribute',
                'updateQuantity',
                'updateproduct',
                'updateCarrier',
                'adminOrder',
                'backOfficeHeader',
                'postUpdateOrderStatus',
                'paymentConfirm',
            );
        } else {
            $expectedHooks = array(
                'actionObjectProductUpdateAfter',
                'displayAdminOrder',
                'displayBackOfficeHeader',
                'actionCarrierUpdate',
                'actionUpdateQuantity',
                'actionProductAttributeDelete',
                'deleteProductAttribute',
                'actionProductDelete',
                'actionAttributeGroupDelete',
                'actionProductAdd',
                'actionProductUpdate',
                'actionAttributeGroupSave',
                'actionOrderStatusPostUpdate',
                'actionOrderHistoryAddAfter',
                'displayAdminProductsExtra',
                'actionEmailAddAfterContent',
                'actionObjectStockAvailableUpdateAfter',
                'actionAdminOrdersListingFieldsModifier',
                'displayPDFInvoice',
            );
        }
        // GDPR compliant
        $expectedHooks[] = 'registerGDPRConsent';
        $expectedHooks[] = 'actionDeleteGDPRCustomer';
        $expectedHooks[] = 'actionExportGDPRData';

        $pass = true;

        if ($action == Amazon::ADD) {
            foreach ($expectedHooks as $expectedHook) {
                if (!$this->isRegisteredInHook($expectedHook)) {
                    if (!$this->registerHook($expectedHook)) {
                        $this->_errors[] = $this->l('Unable to Register Hook').':'.$expectedHook;
                        $pass = false;
                    }
                }
            }
        }
        if ($action == Amazon::REMOVE) {
            foreach ($expectedHooks as $expectedHook) {
                if ($this->isRegisteredInHook($expectedHook)) {
                    if (!$this->unregisterHook($expectedHook)) {
                        $this->_errors[] = $this->l('Unable to Unregister Hook').':'.$expectedHook;
                        $pass = false;
                    }
                }
            }
        }

        return ($pass);
    }

    public function uninstall()
    {
        $pass = true;

        // Remove Hooks
        //
        $this->_hookSetup(self::REMOVE);

        // Remove Tabs
        //
        $this->tabSetup(self::REMOVE);

        if (!parent::uninstall()) {
            $this->_errors[] = $this->l('Unable to uninstall: parent()') && $pass = false;
        }

        if (!$this->_deleteCustomer()) {
            $this->_errors[] = $this->l('Unable to uninstall: Amazon Customer') && $pass = false;
        }

        if (!$this->_removeMarketPlaceTables()) {
            $this->_errors[] = $this->l('Unable to uninstall: MarketPlace Tables') && $pass = false;
        }

        foreach ($this->_config as $key => $value) {
            if (!Configuration::deleteByName($key)) {
                $pass = $pass && false;
            }
        }

        if (!$pass) {
            $this->_errors[] = $this->l('Unable to uninstall : Some configuration values');
        }

        return ($pass);
    }

    private function _deleteCustomer()
    {
        $customer = new Customer(Configuration::get('AMAZON_CUSTOMER_ID'));

        if (Customer::customerExists($customer->email)) {
            //
            return ($customer->delete());
        } else {
            //
            return (true);
        }
    }

    private function _removeMarketPlaceTables()
    {
        $pass = true;
        return($pass);
        /*
         *
         * require_once(dirname(__FILE__).'/classes/amazon.order.class.php');
         *
        // Check if exists
        //
        $tables = array();
        $query = Db::getInstance()->executeS('SHOW TABLES');
        foreach ($query as $rows) {
            foreach ($rows as $table) {
                $tables[$table] = 1;
            }
        }

        foreach (array(
                     'marketplace_product_action',
                     'marketplace_configuration',
                     'marketplace_product_option',
                     'marketplace_strategies'
                 ) as $marketplace_table) {
            if (isset($tables[_DB_PREFIX_.$marketplace_table])) {
                $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$marketplace_table.'` ; ';

                if (!Db::getInstance()->execute($sql)) {
                    $pass = false;
                }
            }
        }

        return ($pass);
        */
    }

    public function getContent()
    {
        if ($this->ps17x) {
            $this->_html .= $this->_autoAddCSS($this->url.'views/css/amazon16.css');
            $this->_html .= $this->_autoAddCSS($this->url.'views/css/amazon17.css');
        } elseif ($this->ps16x) {
            $this->_html .= $this->_autoAddCSS($this->url.'views/css/amazon16.css');
        } else {
            $this->_html .= $this->_autoAddCSS($this->url.'views/css/amazon.css');
        }

        $this->versionCheck();

        /*Actions from Tools Menu*/
        if (Tools::isSubmit('tools_code_import_submit')) {
            $this->toolsCodeImport();
        }

        /*Action from Configuration*/
        if (Tools::isSubmit('submit')) {
            $this->_postValidation();

            $this->_postProcess();
        }

        // July-16-2018: Must keep this, although already get feature in construct. Because they may change after save.
        // Maybe in construct, it's used for children class
        $this->amazon_features = $this->getAmazonFeatures();

        // Unactivate worldwide if necessary
        if (!$this->amazon_features['worldwide']) {
            foreach (array_keys($this->_platforms) as $cc) {
                if (!AmazonTools::isUnifiedAccount($cc)) {
                    unset($this->_platforms[$cc]);
                }
            }
        }

        $this->_displayForm();

        return $this->_html;
    }

    protected function _autoAddJS($url)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            if (isset($this->context->controller) && method_exists($this->context->controller, 'addJquery')) {
                $this->context->controller->addJquery();
            }

            return ($this->context->controller->addJS($url) && '');
        }

        return (sprintf(html_entity_decode('&lt;script type="text/javascript" src="%s"&gt;&lt;/script&gt;')."\n", $url));
    }

    protected function _autoAddCSS($url, $media = 'all')
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            return ($this->context->controller->addCSS($url, $media) && '');
        }

        return (sprintf(html_entity_decode('&lt;link rel="stylesheet" type="text/css" href="%s"&gt;')."\n", $url));
    }

    protected function _addJQueryUI($name)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $this->context->controller->addJqueryUI($name);
            return;
        }

        return;
    }

    public function versionCheck()
    {
        $currentVersion = Configuration::get('AMAZON_CURRENT_VERSION', null, 0, 0);

        if ($currentVersion == null && !Tools::isSubmit('submit')) {
            $this->smarty->assign(array(
                'versionCheckText1' => $this->l('Be effective, do not waste your time :'),
                'versionCheckText2' => $this->l('To start, Supposing you only have basis knowledge on it, please use basis functions'),
                'versionCheckText3' => $this->l('Do not try to hard tune the module. Almost all parameters are correctly configured by default.'),
                'ps16x' => $this->ps16x
            ));

            $this->_html .= $this->display(__FILE__, 'views/templates/admin/support/version_check.tpl');
        }

        return (false);
    }


    private function toolsCodeImport()
    {
        $replace = (int)Tools::getValue('tools_code_import_replace');

        if (isset($_FILES['tools_code_import']) && isset($_FILES['tools_code_import']['tmp_name']) && isset($_FILES['tools_code_import']['error']) && !$_FILES['tools_code_import']['error']) {
            $refImport = new AmazonCSVReferences($_FILES['tools_code_import']['tmp_name']);

            $result = $refImport->getData();

            if (!$result || !is_array($result) || !count($result)) {
                $this->_postErrors[] = $this->l('Unable to process the file, please verify your file and read instructions');

                return (false);
            }
            $validation_failed = 0;
            $validation_lang_failed = 0;

            if ($result) {
                $count = 0;
                foreach ($result as $refitem) {
                    // Parent
                    //
                    if (!(int)$refitem->id_product_attribute) {
                        $product = new Product($refitem->id_product);

                        if (!Validate::isLoadedObject($product)) {
                            continue;
                        }

                        if (!$product->validateFields(false, false)) {
                            $validation_failed++;
                            continue;
                        }

                        $pass = false;
                        foreach (array('ean13', 'upc', 'reference') as $field) {
                            if (is_numeric($product->{$field}) && (int)$product->{$field} > 0 && $replace == false) {
                                continue;
                            }

                            if (!is_numeric($product->{$field}) && !empty($product->{$field}) && $replace == false) {
                                continue;
                            }

                            if ($field === 'reference' && !AmazonTools::validateSKU(trim($product->reference))) {
                                continue;
                            }

                            $pass = true;
                            $product->{$field} = trim($refitem->{$field});
                        }
                        if (Tools::strlen($refitem->manufacturer) && Validate::isCatalogName($refitem->manufacturer)) {
                            if ($id_manufacturer = Manufacturer::getIdByName($refitem->manufacturer)) {
                                $product->id_manufacturer = (int)$id_manufacturer;
                            } else {
                                $manufacturer = new Manufacturer();
                                $manufacturer->name = trim($refitem->manufacturer);
                                $manufacturer->active = true;

                                if ($manufacturer->validateFields(false) && $manufacturer->validateFieldsLang(false) && $manufacturer->add()) {
                                    $product->id_manufacturer = (int)$manufacturer->id;
                                }
                            }
                        }

                        if (!$product->validateFieldsLang(false, false)) {
                            $validation_failed++;
                            continue;
                        }

                        if ($pass) {
                            $product->update();
                        }

                        $count++;
                    } else {
                        // Children

                        foreach (array('ean13', 'upc', 'reference') as $field) {
                            if ($replace) {
                                $condition = '1';
                            } else {
                                $condition = ($field === 'reference') ? 'NOT `'.pSQL($field).'` > ""' : ' NOT `'.pSQL($field).'` > 0';
                            }

                            if ($field === 'reference' && !AmazonTools::validateSKU(trim($refitem->reference))) {
                                continue;
                            }

                            // We have to use a query because the combinations functions are a messup between differents Prestashop versions (PS 1.3, 1.4, 1.5)
                            Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'product_attribute` SET `'.pSQL($field).'`="'.pSQL(trim($refitem->{$field})).'" WHERE `id_product`='.(int)$refitem->id_product.' AND `id_product_attribute`='.(int)$refitem->id_product_attribute.' AND '.$condition);
                        }
                        // as we write directly into the db, trigger a hook
                        if (version_compare(_PS_VERSION_, '1.5', '<')) {
                            Hook::updateProductAttribute((int)$refitem->id_product_attribute);
                        } else {
                            Hook::exec('actionProductAttributeUpdate', array('id_product_attribute' => (int)$refitem->id_product_attribute));
                        }

                        $count++;
                    }
                }

                if ($validation_failed || $validation_lang_failed) {
                    $this->warning = sprintf('Notice: %d validation errors %d lang validation errors', $validation_failed, $validation_lang_failed);
                }

                if (!$this->_postErrors && $count) {
                    $this->_html .= html_entity_decode('&lt;div class="conf confirm '.($this->ps16x ? 'alert alert-success' : '').'"&gt;')
                                    .sprintf($this->l('Products References File: %d products updated'), $count).html_entity_decode('&lt;/div&gt;');
                }
            }
        }

        return (false);
    }

    private function _postValidation()
    {
        $actives = Tools::getValue('actives');

        $merchantIds = Tools::getValue('merchantId');
        $marketPlaceIds = Tools::getValue('marketPlaceId');
        $awsKeyIds = Tools::getValue('awsKeyId');
        $awsSecretKeys = Tools::getValue('awsSecretKey');
        $mwsTokens = Tools::getValue('mwsToken');

        $currencies = Tools::getValue('marketPlaceCurrency');
        $regions = Tools::getValue('marketPlaceRegion');

        $carriers = Tools::getValue('carrier');
        $amazon_carriers = Tools::getValue('amazon_carrier');
        $carrier_default = Tools::getValue('carrier_default');

        $categories = Tools::getValue('category');

        $order_state = Tools::getValue('order_state');
        $send_state = Tools::getValue('sent_state');

        $amazonEurope = Tools::getValue('amazonEurope');
        $marketPlaceMaster = Tools::getValue('marketPlaceMaster');

        $condition_map = Tools::getValue('condition_map');
        $profiles = Tools::getValue('profiles');

        $amazon_features = array_fill_keys(self::$amazon_default_features, false);
        $features = array_merge($amazon_features, Tools::getValue('features', array()));

        $mapping = Tools::getValue('mapping', array(
            'features' => array(),
            'attributes' => array(),
            'fixed' => array(),
            'ungroup' => false
        ));

        $merchantId = array();
        $marketPlaceId = array();
        $awsKeyId = array();
        $awsSecretKey = array();
        $mwsToken = array();

        $currency = array();
        $region = array();

        $carrier = array();

        $error = null;
        $warning = null;

        $price_rules = Tools::getValue('price_rule');
        $pass = false;

        foreach (AmazonTools::languages() as $language) {
            $id_lang = $language['id_lang'];

            if (!(isset($actives[$id_lang]) && (int)$actives[$id_lang])) {
                continue;
            }
            $pass = true;

            $merchantId[$id_lang] = isset($merchantIds[$id_lang]) ? trim($merchantIds[$id_lang]) : '';
            $marketPlaceId[$id_lang] = isset($marketPlaceIds[$id_lang]) ? trim($marketPlaceIds[$id_lang]) : '';
            $awsKeyId[$id_lang] = isset($awsKeyIds[$id_lang]) ? trim($awsKeyIds[$id_lang]) : '';
            $awsSecretKey[$id_lang] = isset($awsSecretKeys[$id_lang]) ? trim($awsSecretKeys[$id_lang]) : '';
            $mwsToken[$id_lang] = isset($mwsTokens[$id_lang]) ? trim($mwsTokens[$id_lang]) : '';

            $carrier[$id_lang] = isset($carriers[$id_lang]) ? $carriers[$id_lang] : '';

            $currency[$id_lang] = isset($currencies[$id_lang]) ? $currencies[$id_lang] : '';
            $region[$id_lang] = isset($regions[$id_lang]) ? $regions[$id_lang] : '';

            $tokenEurope = 1;

            if (!empty($merchantId[$id_lang])) {
                if (((int)$amazonEurope && $marketPlaceMaster == $regions[$id_lang]) || !$amazonEurope) {
                    if (!$mwsToken[$id_lang] && empty($awsKeyId[$id_lang]) && $id_lang == $language['id_lang']) {
                        $this->_postErrors[]= $this->l('You must fill your Amazon Web Service ID').' - ('.$language['name'].')';
                    }

                    if (empty($awsSecretKey[$id_lang]) && !$mwsToken[$id_lang]) {
                        $this->_postErrors[]= $this->l('You must fill your Amazon Secret Key').' - ('.$language['name'].')';
                    }

                    if (!$mwsToken[$id_lang] && (empty($merchantId[$id_lang])  || !preg_match('/([0-9A-Z]{12,})/', $merchantId[$id_lang]))) {
                        $this->_postErrors[]= $this->l('Merchant ID is invalid').' - ('.$language['name'].')';
                    }

                    $tokenEurope = 1;
                }

                if (empty($marketPlaceId[$id_lang]) && $id_lang == $language['id_lang']) {
                    $this->_postErrors[]= $this->l('You must fill your MarketPlace ID').' ('.$language['name'].')';
                }

                if (empty($region[$id_lang])) {
                    $this->_postErrors[]= $this->l('Region must be selected').' ('.$language['name'].')';
                }

                if (empty($currency[$id_lang])) {
                    $this->_postErrors[]= $this->l('Currency must be selected').' ('.$language['name'].')';
                }

                $check_price_rules = $this->_checkPriceRules($price_rules);
                if (Tools::strlen($check_price_rules)) {
                    $this->_postErrors[]= $check_price_rules;
                }

                if ($this->amazon_features['orders']) {
                    if (!is_array($carrier) || !max($carrier[$id_lang]) || !isset($carrier_default[$id_lang]['prestashop']) || !max($carrier_default[$id_lang]['prestashop'])) {
                        $this->_postErrors[]= $this->l('You must choose a Carrier').' ('.$language['name'].')';
                    }

                    if (!is_array($amazon_carriers) || !max($amazon_carriers[$id_lang]) || !isset($carrier_default[$id_lang]['amazon']) || !max($carrier_default[$id_lang]['amazon'])) {
                        $this->_postErrors[]= $this->l('You must choose an Amazon Carrier').' ('.$language['name'].')';
                    }
                }

                $region_values = array_values($regions);
                $region_values_unique = array_unique($region_values);
                if (is_array($region) && is_array($region_values) && count($region_values) > 1
                    && (is_array($region_values_unique) && count($region_values) != count($region_values_unique))) {
                    $this->_postErrors[] = $this->l('Platform is used several times, platform has to be used only one time');
                }
            }
        }

        if ($pass && !is_array($categories) || !max($categories)) {
            $this->_postErrors[]= $this->l('Categories Tab must be configured !');
        }

        if ($this->amazon_features['orders']) {
            if ($pass && ((is_array($order_state) && !max($order_state)) || (!is_array($order_state) && !(int)$order_state))) {
                $this->_postErrors[]= $this->l('Incoming order status must be set in the parameters tab !');
            }

            if ($pass && ((is_array($send_state) && !max($send_state)) || (!is_array($send_state) && !(int)$send_state))) {
                $this->_postErrors[]= $this->l('Sent order status must be set in the parameters tab !');
            }
        }

        if (!isset($condition_map['New']) || empty($condition_map['New']) && $this->amazon_features['second_hand']) {
            $this->_postErrors[]= $this->l('The condition map must be filled');
        }

        if (isset($amazonEurope) && (int)$amazonEurope && isset($marketPlaceMaster) && empty($marketPlaceMaster)) {
            $this->_postErrors[]= $this->l('Amazon Europe is set, you must define a Master Platform !');
        }

        if (isset($features['creation']) && $features['creation']) {
            $languages = AmazonTools::languages();

            if ($profiles && isset($profiles['name']) && is_array($profiles['name'])) {
                if (is_array($mapping) && isset($mapping['mandatory']) && is_array($mapping['mandatory']) && count($mapping['mandatory'])) {
                    foreach ($mapping['mandatory'] as $field_type => $mapping_l1) {
                        foreach ($mapping['mandatory'][$field_type] as $mapping_type => $mapping_l2) {
                            foreach ($mapping_l2 as $id_lang => $mapping_l3) {
                                if (!is_array($mapping_l3)) {
                                    continue;
                                }

                                foreach (array_unique(array_keys($mapping_l3)) as $group) {
                                    $has_mapping = isset($mapping[$field_type])
                                        && isset($mapping[$field_type][$mapping_type])
                                        && isset($mapping[$field_type][$mapping_type][$id_lang])
                                        && isset($mapping[$field_type][$mapping_type][$id_lang][$group])
                                        && is_array($mapping[$field_type][$mapping_type][$id_lang][$group])
                                        && count($mapping[$field_type][$mapping_type][$id_lang][$group]);

                                    if (!$has_mapping) {
                                        if ($field_type === 'features') {
                                            $field_type_label = $this->l('Feature');
                                        } elseif ($field_type === 'attributes') {
                                            $field_type_label = $this->l('Attribute');
                                        } else {
                                            continue;
                                        }
                                        $lang_name = $languages[$id_lang]['name'];

                                        $this->_postErrors[]= sprintf($this->l('Mappings tab: %s mapping for "%s" is mandatory (%s)'), $field_type_label, $group, $lang_name);
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($profiles['name'] as $id => $profile) {
                    if (empty($profiles['name'][$id])) {
                        continue;
                    }

                    $name = $profiles['name'][$id];

                    if (!Tools::strlen($profile_key = AmazonTools::toKey($name))) {
                        continue;
                    }

                    foreach (AmazonTools::languages() as $language) {
                        $id_lang = $language['id_lang'];
                        $lang_name = AmazonTools::ucfirst($language['name']);

                        if (!isset($profiles['universe'][$id][$id_lang]) || empty($profiles['universe'][$id][$id_lang])) {
                            if (!isset($profiles['product_type'][$id][$id_lang]) || empty($profiles['product_type'][$id][$id_lang])) {
                                continue;
                            }

                            $this->_postErrors[]= sprintf($this->l('Profiles Tab: The "Universe" for Profile "%s" is not selected.'), $profiles['name'][$id]).' - ';
                            $this->_postErrors[]= $lang_name;
                        }

                        if (!isset($profiles['product_type'][$id][$id_lang]) || empty($profiles['product_type'][$id][$id_lang])) {
                            $this->_postErrors[]= sprintf($this->l('Profiles Tab: The \"Product Type\" for Profile \"%s\" has not been selected.'), $profiles['name'][$id]).' - ';
                            $this->_postErrors[]= $lang_name;
                        }

                        if (isset($profiles['extra'][$profile_key][$id_lang]) && is_array($profiles['extra'][$profile_key][$id_lang]) && count($profiles['extra'][$profile_key][$id_lang])) {
                            if (!isset($profiles['extra'][$profile_key][$id_lang]['field'])) {
                                $profiles['extra'][$profile_key][$id_lang]['field'] = array();
                            }

                            $has_choice_required = false ;
                            $missing_choice_selected = true;

                            // check choices field
                            if (isset($profiles['extra'][$profile_key][$id_lang]['choices_required'])) {
                                $has_choice_required = $profiles['extra'][$profile_key][$id_lang]['choices_required'];
                            }

                            foreach (array_keys($profiles['extra'][$profile_key][$id_lang]['field']) as $field) {
                                if (!isset($profiles['extra'][$profile_key][$id_lang]['required'])
                                    || !isset($profiles['extra'][$profile_key][$id_lang]['choices'])) {
                                    continue;
                                }

                                if (!isset($profiles['extra'][$profile_key][$id_lang]['required'][$field]) &&
                                    !isset($profiles['extra'][$profile_key][$id_lang]['choices'][$field])) {
                                    continue;
                                }

                                $has_value = isset($profiles['extra'][$profile_key][$id_lang]['field'][$field])
                                    && Tools::strlen($profiles['extra'][$profile_key][$id_lang]['field'][$field]);
                                $is_default = $has_value && AmazonSpecificField::isDefault($profiles['extra'][$profile_key][$id_lang]['field'][$field]);

                                if (!$has_value && $is_default) {
                                    $this->_postErrors[]= sprintf($this->l('Profiles Tab: The "%s" value for Profile "%s" is required.'), $field, $profiles['name'][$id]).' - ';
                                    $this->_postErrors[]= $lang_name;
                                }

                                if ($has_choice_required) {
                                    if (isset($profiles['extra'][$profile_key][$id_lang]['choices'][$field])
                                        && isset($profiles['extra'][$profile_key][$id_lang]['field'][$field])
                                        && Tools::strlen($profiles['extra'][$profile_key][$id_lang]['field'][$field])) {
                                        $missing_choice_selected = false;
                                    }
                                }
                            }

                            // one of choice field required
                            if ($has_choice_required && $missing_choice_selected) {
                                $this->_postErrors[]= sprintf($this->l('Profiles Tab : One of (%s) for Profile "%s" is expected.'), implode(", ", array_keys($profiles['extra'][$profile_key][$id_lang]['choices'])), $profiles['name'][$id]).' - ';
                                $this->_postErrors[]= $lang_name;
                            }

                            // Check variations
                            if (isset($profiles['extra'][$profile_key][$id_lang]['variant']) && Tools::strlen($profiles['extra'][$profile_key][$id_lang]['variant'])) {
                                $variationTheme = $profiles['extra'][$profile_key][$id_lang]['variant'];

                                if (strpos($variationTheme, '-')) {
                                    $variationFields = explode('-', $variationTheme);
                                } else {
                                    $variationFields = array($variationTheme);
                                }

                                foreach ($variationFields as $variationField) {
                                    if (!isset($profiles['extra'][$profile_key][$id_lang]['field'][$variationField]) || !Tools::strlen($profiles['extra'][$profile_key][$id_lang]['field'][$variationField])) {
                                        $this->_postErrors[]= sprintf($this->l('Profiles Tab: %s: Variant "%s" is selected, then field "%s" is required.'), $profiles['name'][$id], $variationTheme, $variationField).' - ';
                                        $this->_postErrors[]= $lang_name;
                                    }
                                }
                            }
                        }

                        $marketPlaceIdChk = isset($marketPlaceIds[$id_lang]) ? trim($marketPlaceIds[$id_lang]) : null;

                        if ($marketPlaceIdChk && !AmazonTools::isEuropeMarketplaceId($marketPlaceIdChk)) {
                            continue;
                        }

                        $pass = true;
                        $warn = false;

                        if (isset($profiles['browsenode'][$id][$id_lang]) && preg_match('/[,; ]/', $profiles['browsenode'][$id][$id_lang])) {
                            $result = preg_split('/[,; ]/', $profiles['browsenode'][$id][$id_lang]);

                            if (is_array($result)) {
                                foreach ($result as $browsenode) {
                                    if (empty($browsenode)) {
                                        continue;
                                    }

                                    if (!is_numeric(trim($browsenode))) {
                                        $pass = false;
                                    }
                                }
                            } else {
                                $pass = false;
                            }
                        } elseif (isset($profiles['browsenode'][$id][$id_lang]) && !empty($profiles['browsenode'][$id][$id_lang])) {
                            if (!is_numeric(trim($profiles['browsenode'][$id][$id_lang]))) {
                                $pass = false;
                            }
                        } elseif (isset($profiles['browsenode'][$id][$id_lang]) && empty($profiles['browsenode'][$id][$id_lang])) {
                            if (!is_numeric(trim($profiles['browsenode'][$id][$id_lang]))) {
                                $warn = true;
                            }
                        }

                        if (!$pass) {
                            //
                            $this->_postErrors[]= sprintf($this->l('Profiles Tab: Incorrect value for Browse Node(%s) for Profile "%s" - The value can be only numeric or a list of numerics'), Tools::strtoupper($language['iso_code']), $profiles['name'][$id]).' - ';
                            $this->_postErrors[]= $lang_name;
                        } elseif ($warn) {
                            $warning .= sprintf($this->l('Profiles Tab: Browse Node(%s) for Profile "%s" - this value is highly recommended'), Tools::strtoupper($language['iso_code']), $profiles['name'][$id]).' - ';
                            $warning .= $lang_name.Amazon::BR;
                        }
                    }
                }
            }
        }

        if (!Tools::getValue('post-check')) {
            $post_count = Tools::getValue('post-count');
            $this->_postErrors[]= sprintf($this->l('Post check failed, expecting %d values, please check max_input_vars configuration'), $post_count);
        }

        if (!empty($warning)) {
            $this->warning = $warning;
        }
    }

    private function _checkPriceRules($price_rules)
    {
        if (!is_array($price_rules)) {
            //
            return ($this->l('An error occured with price rules, not an array.'));
        }

        $error = '';
        $actives = Tools::getValue('actives');

        foreach (AmazonTools::languages() as $language) {
            $id_lang = $language['id_lang'];
            $rule = isset($price_rules[$id_lang]['rule']) ? $price_rules[$id_lang]['rule'] : null;
            $type = isset($price_rules[$id_lang]['type']) ? $price_rules[$id_lang]['type'] : null;

            if (!(isset($actives[$id_lang]) && (int)$actives[$id_lang])) {
                continue;
            }

            if (!isset($rule) || !is_array($rule)) {
                continue;
            }

            if (!is_array($rule['from']) || !count($rule['from']) || !is_array($rule['to']) || !count($rule['to'])) {
                continue;
            }

            if (!reset($rule['from']) && !reset($rule['to'])) {
                continue;
            }

            if ((reset($rule['from']) && !reset($rule['to'])) || (!reset($rule['from']) && reset($rule['from']) != '0' && reset($rule['to']))) {
                $error .= sprintf('%s %s => %s'.nl2br(Amazon::LF), $this->l('Price rule incomplete for'), $language['name'], $this->l('Missing range element'));
                continue;
            }

            if (($type === 'percent' && !reset($rule['percent'])) || ($type === 'value' && !reset($rule['value']))) {
                $error .= sprintf('%s %s => %s'.nl2br(Amazon::LF), $this->l('Price rule incomplete for'), $language['name'], $this->l('Missing value'));
                continue;
            }

            $prev_from = -1;
            $prev_to = -1;

            foreach ($rule['from'] as $key => $val) {
                if (max($prev_from, $val) == $prev_from) {
                    $error .= sprintf('%s (%s) => %s %d'.nl2br(Amazon::LF), $this->l('Your range FROM is lower than the previous one'), $language['name'], $this->l('Rule ligne'), $key + 1);
                    break;
                } else {
                    if ($rule['to'][$key] && max($prev_to, $rule['to'][$key]) == $prev_to) {
                        $error .= sprintf('%s (%s) => %s %d'.nl2br(Amazon::LF), $this->l('Your range TO is lower than the previous one'), $language['name'], $this->l('Rule ligne'), $key + 1);
                        break;
                    } else {
                        if ($rule['to'][$key] && max($rule['to'][$key], $val) == $val) {
                            $error .= sprintf('%s (%s) => %s %d'.nl2br(Amazon::LF), $this->l('Your range TO is lower than your range FROM'), $language['name'], $this->l('Rule ligne'), $key + 1);
                            break;
                        }
                    }
                }

                $prev_from = $val;
                $prev_to = $rule['to'][$key];
            }
        }

        return ($error);
    }

    private function _postProcess()
    {
        // Initialize instant token
        $this->setInstantToken();

        // Prestashop bug fixes
        //
        $this->_fixPrestashopIssues();

        // Tables Updates
        //
        if (!$this->addMarketPlaceTables()) {
            $this->_errors[] = 'install tables failed';
        }

        if (!$this->addMarketPlaceField()) {
            $this->_errors[] = 'install marketplace fields failed';
        }

        if (!$this->addConfigurationTable()) {
            $this->_errors[] = 'install configuration table failed';
        } else {
            //clear cache of static variable inside function tableExists, please also note we call the CommonTools, not AmazonTools
            CommonTools::tableExists(_DB_PREFIX_.AmazonConfiguration::$configuration_table, false);
        }

        // Hooks
        $this->_hookSetup(self::ADD);

        // Update Tabs
        //
        $this->tabSetup(self::UPDATE);

        $actives = Tools::getValue('actives');

        $merchantIds = Tools::getValue('merchantId');
        $marketPlaceIds = Tools::getValue('marketPlaceId');
        $awsKeyIds = Tools::getValue('awsKeyId');
        $awsSecretKeys = Tools::getValue('awsSecretKey');
        $mwsTokens = Tools::getValue('mwsToken');

        $currencies = Tools::getValue('marketPlaceCurrency');
        $regions = Tools::getValue('marketPlaceRegion');

        $price_rules = Tools::getValue('price_rule');
        $outofstocks = Tools::getValue('outofstock');
        $roundings = Tools::getValue('rounding');
        $ptcs = Tools::getValue('ptc');
        $default_tax_rules = Tools::getValue('default_tax_rule');
        $sort_orders = Tools::getValue('sort_order');
        $carriers = Tools::getValue('carrier');
        $amazon_carrier = Tools::getValue('amazon_carrier');
        $synch_fields = Tools::getValue('synch_field');
        $use_asins = Tools::getValue('use_asin');

        $categories = Tools::getValue('category');

        $order_state = Tools::getValue('order_state');
        $send_state = Tools::getValue('sent_state');
        $canceled_state = Tools::getValue('canceled_state');
        $preparation_state = Tools::getValue('preparation_state');

        $fba_order_state = Tools::getValue('fba_order_state');
        $fba_multichannel_state = Tools::getValue('fba_multichannel_state');
        $fba_decrease_stock = Tools::getValue('fba_decrease_stock');
        $fba_multichannel_sent_state = Tools::getValue('fba_multichannel_sent_state');
        $fba_multichannel_done_state = Tools::getValue('fba_multichannel_done_state');
        $fba_stock_behaviour = (int)Tools::getValue('fba_stock_behaviour');
        $fba_notification = (int)Tools::getValue('fba_notification');

        $warehouse = Tools::getValue('warehouse');
        $id_employee = Tools::getValue('employee');
        $id_group = Tools::getValue('id_group');
        $id_business_group = Tools::getValue('id_business_group');

        $carrier_default = Tools::getValue('carrier_default');
        $carrier_multichannel = Tools::getValue('carrier_multichannel');

        $marketPlaceMaster = Tools::getValue('marketPlaceMaster');

        $debugMode = (bool)Tools::getValue('debug_mode');
        $email = (bool)Tools::getValue('email');
        $features = Tools::getValue('features');

        $fba_price_formula = Tools::getValue('fba_formula');
        $fba_multichannel = (bool)Tools::getValue('fba_multichannel');
        $fba_multichannel_auto = (bool)Tools::getValue('fba_multichannel_auto');

        $specials = (bool)Tools::getValue('specials');
        $specials_apply_rules = (bool)Tools::getValue('specials_apply_rules');
        $preorder = (bool)Tools::getValue('preorder');
        $taxes = (int)Tools::getValue('taxes');
        $delete_products = (bool)Tools::getValue('delete_products');
        $stock_only = (bool)Tools::getValue('stock_only');
        $html_descriptions = (bool)Tools::getValue('html_descriptions');
        $prices_only = (bool)Tools::getValue('prices_only');
        $payment_region = (bool)Tools::getValue('payment_region');

        $description_field = Tools::getValue('description_field');

        $image_type = Tools::getValue('image_type');
        $safe_encoding = (bool)Tools::getValue('safe_encoding');
        // Aug-23-2018: Remove Carriers/Modules option

        $auto_create = (bool)Tools::getValue('auto_create');

        $condition_map = Tools::getValue('condition_map');

        $account_type = Tools::getValue('account_type');
        $title_format = Tools::getValue('title_format');

        $override_stds = Tools::getValue('overrides_std');
        $override_exps = Tools::getValue('overrides_exp');

        $brute_force = (bool)Tools::getValue('brute_force');

        $repricing = Tools::getValue('repricing', array());
        $default_strategies = Tools::getValue('default_strategy', array());

        $custom_mapping = Tools::getValue('custom_mapping');
        $mapping = Tools::getValue('mapping', array(
            'features' => array(),
            'attributes' => array(),
            'fixed' => array(),
            'ungroup' => false
        ));

        $excluded_manufacturers = Tools::getValue('selected-manufacturers');
        $excluded_suppliers = Tools::getValue('selected-suppliers');
        $price_filter = Tools::getValue('price_filter');

        $inactive_languages = (bool)Tools::getValue('inactive_languages');
        $alternative_content = (bool)Tools::getValue('alternative_content');
        $disable_ssl_check = (bool)Tools::getValue('disable_ssl_check');

        $shipping_params = Tools::getValue('shipping');
        $shipping_methods = Tools::getValue('shipping_method');

        $profiles = Tools::getValue('profiles');
        $profile2category = Tools::getValue('profile2category');

        $repricing_strategies = Tools::getValue('strategies');

        $active = array();

        $merchantId = array();
        $marketPlaceId = array();
        $awsKeyId = array();
        $awsSecretKey = array();

        $currency = array();
        $region = array();

        $outofstock = array();
        $rounding = array();
        $ptc = array();
        $default_tax_rule = array();
        $sort_order = array();

        $carrier = array();
        $synch_field = array();
        $use_asin = array();

        $shipping_rule = array();

        $override_exp = array();
        $override_std = array();

        $amazonTokens = array();

        // For post treatments ....
        //
        foreach (AmazonTools::languages() as $language) {
            $id_lang = $language['id_lang'];

            if (!(isset($actives[$id_lang]) && (int)$actives[$id_lang])) {
                continue;
            }

            $active[$id_lang] = isset($actives[$id_lang]) ? (int)$actives[$id_lang] : '';

            $merchantId[$id_lang] = isset($merchantIds[$id_lang]) ? trim($merchantIds[$id_lang]) : '';
            $marketPlaceId[$id_lang] = isset($marketPlaceIds[$id_lang]) ? trim($marketPlaceIds[$id_lang]) : '';
            $awsKeyId[$id_lang] = isset($awsKeyIds[$id_lang]) ? trim($awsKeyIds[$id_lang]) : '';
            $awsSecretKey[$id_lang] = isset($awsSecretKeys[$id_lang]) ? trim($awsSecretKeys[$id_lang]) : '';

            $currency[$id_lang] = isset($currencies[$id_lang]) ? $currencies[$id_lang] : '';

            if (is_array($region) && array_key_exists($id_lang, $regions)) {
                $region_to_id_lang = array_flip($region);

                if (isset($region_to_id_lang[$regions[$id_lang]])) {
                    $pass = false;
                } else {
                    $pass = true;
                }
            } else {
                $pass = true;
            }

            if ($pass) {
                $region[$id_lang] = isset($regions[$id_lang]) ? $regions[$id_lang] : '';
            }

            $outofstock[$id_lang] = isset($outofstocks[$id_lang]) ? trim($outofstocks[$id_lang]) : '';

            $rounding[$id_lang] = isset($roundings[$id_lang]) ? (int)$roundings[$id_lang] : '1';
            $ptc[$id_lang] = isset($ptcs[$id_lang]) ? $ptcs[$id_lang] : null;
            $default_tax_rule[$id_lang] = isset($default_tax_rules[$id_lang]) ? $default_tax_rules[$id_lang] : null;
            $sort_order[$id_lang] = isset($sort_orders[$id_lang]) ? (int)$sort_orders[$id_lang] : '1';

            $carrier[$id_lang] = isset($carriers[$id_lang]) ? $carriers[$id_lang] : '';
            $amazon_carrier[$id_lang] = isset($amazon_carrier[$id_lang]) ? $amazon_carrier[$id_lang] : '';

            $synch_field[$id_lang] = isset($synch_fields[$id_lang]) ? $synch_fields[$id_lang] : '';

            $use_asin[$id_lang] = isset($use_asins[$id_lang]) ? $use_asins[$id_lang] : '';

            $override_std[$id_lang] = isset($override_stds[$id_lang]) ? $override_stds[$id_lang] : '';
            $override_exp[$id_lang] = isset($override_exps[$id_lang]) ? $override_exps[$id_lang] : '';

            if (isset($price_rules[$id_lang]) && is_array($price_rules[$id_lang]) && isset($price_rules[$id_lang]['rule']['from']) && is_array($price_rules[$id_lang]['rule']['from']) && is_array($price_rules[$id_lang]['rule']['to'])) {
                foreach ($price_rules[$id_lang]['rule']['from'] as $index => $val) {
                    if (!is_numeric($val)) {
                        unset($price_rules[$id_lang]['rule']['from'][$index]);
                        unset($price_rules[$id_lang]['rule']['to'][$index]);
                        unset($price_rules[$id_lang]['rule']['percent'][$index]);
                        unset($price_rules[$id_lang]['rule']['value'][$index]);
                    }
                }
                $price_rules[$id_lang]['rule']['from'] = isset($price_rules[$id_lang]['rule']['from']) ? array_values($price_rules[$id_lang]['rule']['from']) : '';
                $price_rules[$id_lang]['rule']['to'] = isset($price_rules[$id_lang]['rule']['to']) ? array_values($price_rules[$id_lang]['rule']['to']) : '';
                $price_rules[$id_lang]['rule']['percent'] = isset($price_rules[$id_lang]['rule']['percent']) ? array_values($price_rules[$id_lang]['rule']['percent']) : '';
                $price_rules[$id_lang]['rule']['value'] = isset($price_rules[$id_lang]['rule']['value']) ? array_values($price_rules[$id_lang]['rule']['value']) : '';
            }

            // Cron Token
            //
            if (isset($awsSecretKeys[$id_lang])) {
                $amazonTokens[$id_lang] = md5($awsSecretKeys[$id_lang]);
            }
        }

        AmazonConfiguration::updateValue('ACTIVE', $active);
        AmazonConfiguration::updateValue('MERCHANT_ID', $merchantId);
        AmazonConfiguration::updateValue('MARKETPLACE_ID', $marketPlaceId);
        AmazonConfiguration::updateValue('AWS_KEY_ID', $awsKeyId);
        AmazonConfiguration::updateValue('SECRET_KEY', $awsSecretKey);
        AmazonConfiguration::updateValue('MWS_TOKEN', $mwsTokens);
        
        AmazonConfiguration::updateValue('CURRENCY', $currency);
        AmazonConfiguration::updateValue('REGION', $region);

        AmazonConfiguration::updateValue('OUT_OF_STOCK', $outofstock);
        AmazonConfiguration::updateValue('PRICE_RULE', $price_rules);
        AmazonConfiguration::updateValue('PRICE_ROUNDING', $rounding);
        AmazonConfiguration::updateValue('PTC', $ptc);
        AmazonConfiguration::updateValue(self::CONFIG_DEFAULT_TAX_RULE_FOR_MP, $default_tax_rule);
        AmazonConfiguration::updateValue('SORT_ORDER', $sort_order);
        AmazonConfiguration::updateValue('CARRIER', $carrier);
        AmazonConfiguration::updateValue('AMAZON_CARRIER', $amazon_carrier);

        AmazonConfiguration::updateValue('FIELD', $synch_field);
        AmazonConfiguration::updateValue('USE_ASIN', $use_asin);

        AmazonConfiguration::updateValue('ORDER_STATE', $order_state);
        AmazonConfiguration::updateValue('PREPARATION_STATE', $preparation_state);
        AmazonConfiguration::updateValue('SENT_STATE', $send_state);
        AmazonConfiguration::updateValue('CANCELED_STATE', $canceled_state);

        Configuration::updateValue('AMAZON_FBA_ORDER_STATE', (int)$fba_order_state);
        Configuration::updateValue('AMAZON_FBA_MULTICHANNEL_STATE', (int)$fba_multichannel_state);
        Configuration::updateValue('AMAZON_FBA_MULTICHANNEL_SENT', (int)$fba_multichannel_sent_state);
        Configuration::updateValue('AMAZON_FBA_MULTICHANNEL_DONE', (int)$fba_multichannel_done_state);
        Configuration::updateValue('AMAZON_FBA_STOCK_BEHAVIOUR', (int)$fba_stock_behaviour);
        Configuration::updateValue('AMAZON_FBA_NOTIFICATION', (int)$fba_notification);

        Configuration::updateValue('AMAZON_WAREHOUSE', (int)$warehouse);

        if (!$id_employee || !is_numeric($id_employee)) {
            $id_employee = (int)$this->context->employee->id;
        }

        Configuration::updateValue('AMAZON_EMPLOYEE', (int)$id_employee);

        Configuration::updateValue('AMAZON_CUSTOMER_GROUP', (int)$id_group);
        Configuration::updateValue('AMAZON_BUSINESS_CUSTOMER_GROUP', (int)$id_business_group);

        AmazonConfiguration::updateValue('CARRIER_DEFAULT', $carrier_default);
        AmazonConfiguration::updateValue('CARRIER_MULTICHANNEL', $carrier_multichannel);

        AmazonConfiguration::updateValue(self::CONFIG_MASTER, $marketPlaceMaster);

        Configuration::updateValue('AMAZON_EMAIL', (bool)$email);
        Configuration::updateValue('AMAZON_DEBUG_MODE', (bool)$debugMode);

        AmazonConfiguration::updateValue('FEATURES', $features);
        AmazonConfiguration::updateValue('FBA_PRICE_FORMULA', $fba_price_formula);

        Configuration::updateValue('AMAZON_FBA_MULTICHANNEL', $fba_multichannel);
        Configuration::updateValue('AMAZON_FBA_MULTICHANNEL_AUTO', $fba_multichannel_auto);
        Configuration::updateValue('AMAZON_FBA_DECREASE_STOCK', (int)$fba_decrease_stock);

        AmazonConfiguration::updateValue('SPECIALS', $specials);
        Configuration::updateValue('AMAZON_SPECIALS_APPLY_RULES', (bool)$specials_apply_rules);
        Configuration::updateValue('AMAZON_PREORDER', (bool)$preorder);
        AmazonConfiguration::updateValue('TAXES', $taxes);

        AmazonConfiguration::updateValue('CONDITION_MAP', $condition_map);

        AmazonConfiguration::updateValue('ACCOUNT_TYPE', $account_type);
        AmazonConfiguration::updateValue('TITLE_FORMAT', $title_format);

        AmazonConfiguration::updateValue('SHIPPING_OVERRIDES_STD', $override_std);
        AmazonConfiguration::updateValue('SHIPPING_OVERRIDES_EXP', $override_exp);

        AmazonConfiguration::updateValue('CRON_TOKEN', $amazonTokens);

        AmazonConfiguration::updateValue('DELETE_PRODUCTS', $delete_products);
        AmazonConfiguration::updateValue('STOCK_ONLY', $stock_only);
        Configuration::updateValue('AMAZON_PRICES_ONLY', $prices_only);
        Configuration::updateValue('AMAZON_PAYMENT_REGION', $payment_region);

        AmazonConfiguration::updateValue('BRUTE_FORCE', $brute_force);

        Configuration::updateValue('AMAZON_HTML_DESCRIPTIONS', $html_descriptions);
        Configuration::updateValue('AMAZON_DESCRIPTION_FIELD', $description_field);

        Configuration::updateValue('AMAZON_AUTO_CREATE', (int)$auto_create);

        Configuration::updateValue('AMAZON_IMAGE_TYPE', $image_type);
        Configuration::updateValue('AMAZON_SAFE_ENCODING', $safe_encoding);
        // Aug-23-2018: Remove save Carriers/Modules option

        AmazonConfiguration::updateValue('mapping', is_array($mapping) ? $this->filterRecursive($mapping) : array());

        AmazonConfiguration::updateValue('EXCLUDED_MANUFACTURERS', $excluded_manufacturers);
        AmazonConfiguration::updateValue('EXCLUDED_SUPPLIERS', $excluded_suppliers);
        AmazonConfiguration::updateValue('PRICE_FILTER', $price_filter);
        Configuration::updateValue('AMAZON_INACTIVE_LANGUAGES', $inactive_languages);
        Configuration::updateValue('AMAZON_ALTERNATIVE_CONTENT', $alternative_content);
        Configuration::updateValue('AMAZON_DISABLE_SSL_CHECK', $disable_ssl_check);

        // Configure Hidden Settings - Expert Mode
        AmazonConfiguration::updateValue('MAIL_INVOICE', Tools::getValue('mail_invoice'));
        AmazonConfiguration::updateValue('MAIL_REVIEW', Tools::getValue('mail_review'));
        AmazonConfiguration::updateValue('CUSTOMER_THREAD', Tools::getValue('customer_thread'));

        // Whole Shipping Tab configuration - 2013-12-24
        AmazonConfiguration::updateValue('SHIPPING', $shipping_params);
        AmazonConfiguration::updateValue('SHIPPING_METHODS', $shipping_methods);

        AmazonConfiguration::updateValue('REPRICING', $repricing);

        //
        // Context management - save the full shop context
        //
        $employee = new Employee($id_employee);

        AmazonContext::save($this->context, $employee);

        //
        // RepricingStrategies
        //
        if (isset($repricing_strategies) && is_array($repricing_strategies) && count($repricing_strategies)) {
            $this->_saveRepricingStrategies($repricing_strategies, $default_strategies);
        }

        //
        // Profiles
        //
        if (isset($profiles) && is_array($profiles) && count($profiles)) {
            $this->_saveProfiles($profiles);
        }

        if (is_array($custom_mapping) && count($custom_mapping)) {
            AmazonValidValues::saveCustomMapping($custom_mapping);
        }

        //
        // Categories & Profiles Mapping
        //
        AmazonConfiguration::updateValue('profiles_categories', (array)$profile2category);
        AmazonConfiguration::updateValue('categories', (array)$categories);

        // Customer Account and Addresses - verify/create/update
        //
        if (($id_customer = $this->createCustomer())) {
            /*Smart Shipping is Active : create or update addresses*/
            if (is_array($regions) && count($regions) && isset($shipping_params['smart_shipping']['active']) && $shipping_params['smart_shipping']['active']) {
                require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.address.class.php');

                AmazonAddress::createShippingLocations($regions, $id_customer);
            }
        }

        Configuration::deleteByName('AMAZON_CURRENT_VERSION');
        Configuration::updateValue('AMAZON_CURRENT_VERSION', $this->version, false, 0, 0);

        if (!$this->_postErrors) {
            $this->_html .= html_entity_decode('&lt;div class="conf confirm '.($this->ps16x ? 'alert alert-success' : '').'"&gt;'.$this->l('Configuration updated').'&lt;/div&gt;');
        }
    }

    private function _fixPrestashopIssues()
    {
        // Create an empty mail translation file if it is empty to fix the issue :
        // http://forge.prestashop.com/browse/PSCFV-10380
        //
        $mail_invoice = AmazonConfiguration::get('MAIL_INVOICE');

        if (isset($mail_invoice['active']) && $mail_invoice['active']) {
            foreach (AmazonTools::languages() as $language) {
                $iso_code = $language['iso_code'];

                $dir = _PS_MAIL_DIR_.$iso_code;
                $file = _PS_MAIL_DIR_.$iso_code.'/lang.php';

                if (!is_dir($dir)) {
                    @mkdir($dir);
                }

                if (!file_exists($file)) {
                    @file_put_contents($file, null);
                }
            }
        }
    }

    private function filterNull($var)
    {
        return ($var !== null && $var !== false && $var !== '');
    }

    private function filterRecursive($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = $this->filterRecursive($value);
            }
        }

        return array_filter($array, array($this, 'filterNull'));
    }

    private function _saveRepricingStrategies($strategies, $default_strategies)
    {
        $out_strategies = array();

        if (is_array($strategies) && count($strategies) && isset($strategies['name']) && is_array($strategies['name']) && count($strategies['name'])) {
            foreach ($strategies['name'] as $id_lang => $names) {
                if (!is_array($names) || !count($names)) {
                    continue;
                }

                foreach ($names as $index => $name) {
                    $key = AmazonTools::toKey($name);

                    foreach ($strategies as $conf_item => $conf) {
                        $has_lang = is_array($conf) && isset($conf[$id_lang]) && is_array($conf[$id_lang]) && count($conf[$id_lang]);
                        $has_data = isset($conf[$id_lang][$index]);

                        if ($has_lang && $has_data) {
                            $out_strategies[$conf_item][$id_lang][$key] = $conf[$id_lang][$index];
                        }
                    }
                }
            }
        }
        AmazonConfiguration::updateValue('strategies', $strategies);
        AmazonConfiguration::updateValue('default_strategies', $default_strategies);
    }

    private function _saveProfiles($profiles)
    {
        require_once(_PS_MODULE_DIR_.'/amazon/validate/Node.php');
        require_once(_PS_MODULE_DIR_.'/amazon/validate/XmlDataType.php');
        require_once(_PS_MODULE_DIR_.'/amazon/validate/XmlRestriction.php');
        require_once(_PS_MODULE_DIR_.'/amazon/validate/AmazonXSD.php'); //gets code for Amazon XML Schemas
        require_once(_PS_MODULE_DIR_.'/amazon/validate/AmazonValidator.php');
        require_once(_PS_MODULE_DIR_.'/amazon/validate/Tools.php');

        $profiles['parameters'] = array();
        $profiles['attributes'] = array();


        if (isset($profiles['name']) && is_array($profiles['name'])) {
            if (!isset($profiles['version4'])) {
                return (null);
            }

            foreach ($profiles['name'] as $id => $profile) {
                if (!isset($profiles['name'][$id]) || empty($profiles['name'][$id])) {
                    continue;
                }

                $languages = AmazonTools::languages();

                foreach ($languages as $language) {
                    $id_lang = $language['id_lang'];

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s:#%d universe: %s'."\n", basename(__FILE__), __LINE__, print_r($profiles['universe'][$id][$id_lang], true)));
                    }
                    if (!array_key_exists($id_lang, $profiles['universe'][$id]) || empty($profiles['universe'][$id][$id_lang])) {
                        continue;
                    }

                    $category = $profiles['universe'][$id][$id_lang];


                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s:#%d category: %s id_lang: %d , id: %d', basename(__FILE__), __LINE__, $category, $id_lang, $id));
                    }

                    try {
                        $productFactory = new AmazonXSD($category.'.xsd');
                        $productInstance = $productFactory->getInstance();

                        $referenceElement = $profiles['product_type'][$id][$id_lang] ? $profiles['product_type'][$id][$id_lang] : null;

                        if ($referenceElement && Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s:#%d reference element: %s', basename(__FILE__), __LINE__, print_r($referenceElement, true)));
                        }

                        if ($category != 'CE') {
                            $productFactory->filterProductType($category, $referenceElement);

                            foreach (array('ProductType', 'ClothingType', 'VariationData', 'Parentage', 'VariationTheme') as $searchedElement) {
                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s:#%d lookup, id: %d, element: %s, reference element: %s'."\n", basename(__FILE__), __LINE__, $id, $searchedElement, $referenceElement));
                                }

                                if (($path = AmazonXSDTools::searchPath($productInstance, $searchedElement, $referenceElement))) {
                                    $profiles['parameters'][$id][$id_lang][$searchedElement] = $path;

                                    if (Amazon::$debug_mode) {
                                        CommonTools::p(sprintf('%s:#%d parameters, id: %d, element: %s, path: %s', basename(__FILE__), __LINE__, $id, $searchedElement, print_r($path, true)));
                                    }
                                }
                            }
                        } else {
                            $field = 'ProductType';
                            $product_subtype = $referenceElement;
                            $referenceElement = 'ConsumerElectronics';

                            //$productFactory->filterProductType($category, $referenceElement);

                            if (($path = AmazonXSDTools::searchPath($productInstance, $field, $referenceElement))) {
                                $profiles['parameters'][$id][$id_lang][$field] = $path;
                            }

                            $field = 'ProductSubtype';

                            if (($path = AmazonXSDTools::searchPath($productInstance, $field, $product_subtype))) {
                                $profiles['parameters'][$id][$id_lang][$field] = $path;
                            }

                            foreach (array(
                                         'VariationData',
                                         'Parentage',
                                         'VariationTheme'
                                     ) as $searchedElement) {
                                if (($path = AmazonXSDTools::searchPath($productInstance, $searchedElement, $referenceElement, true))) {
                                    $profiles['parameters'][$id][$id_lang][$searchedElement] = $path;
                                }
                            }
                        }

                        $profile_key = AmazonTools::toKey($profile);

                        $has_extra = isset($profiles['extra'])
                            && isset($profiles['extra'][$profile_key])
                            && isset($profiles['extra'][$profile_key][$id_lang])
                            && is_array($profiles['extra'][$profile_key][$id_lang])
                            && count($profiles['extra'][$profile_key][$id_lang]);
                        $has_profile_fields = $has_extra && isset($profiles['extra'][$profile_key][$id_lang]) && isset($profiles['extra'][$profile_key][$id_lang]['field']);
                        $has_variant_field = $has_extra && isset($profiles['extra'][$profile_key][$id_lang]) && isset($profiles['extra'][$profile_key][$id_lang]['variant']);

                        if ($has_extra) {
                            $p_extra = &$profiles['extra'][$profile_key][$id_lang];
                        }

                        // Add to the path the mandatory fields
                        //
                        if ($has_profile_fields) {
                            foreach ($p_extra['field'] as $field => $value) {
                                $has_default = is_array($p_extra) && isset($p_extra['default']) && isset($p_extra['default'][$field]) && (!empty($p_extra['default'][$field]) || is_numeric($p_extra['default'][$field]));

                                if (empty($value) && !is_numeric($value) && !$has_default) {
                                    continue;
                                }

                                $path = AmazonXSDTools::searchPath($productInstance, $field, $referenceElement);

                                if ($path) {
                                    $profiles['parameters'][$id][$id_lang][$field] = $path;
                                }
                            }
                        }
                        // Add path for generic variants
                        //
                        if ($has_variant_field && Tools::strlen($p_extra['variant'])) {
                            $variationTheme = $p_extra['variant'];

                            if (strpos($variationTheme, 'Color') !== false) {
                                $variationTheme .= '-ColorMap';
                            }
                            if (strpos($variationTheme, 'Size') !== false) {
                                $variationTheme .= '-SizeMap';
                            }

                            if (strpos($variationTheme, '-')) {
                                $variationFields = explode('-', $variationTheme);

                                $variationFieldsList = $variationFields;
                            } else {
                                $variationFieldsList = array($variationTheme);
                            }

                            if (!is_array($variationFieldsList) || !count($variationFieldsList)) {
                                continue;
                            }

                            foreach ($variationFieldsList as $tagName) {
                                // Rewrite particular cases
                                if (isset(AmazonXSD::$rewriteFieldsUniverse[$category]) && isset(AmazonXSD::$rewriteFieldsUniverse[$category][$tagName])) {
                                    $tagName = AmazonXSD::$rewriteFieldsUniverse[$category][$tagName];
                                } elseif (isset(AmazonXSD::$rewriteFields[$tagName])) {
                                    $tagName = AmazonXSD::$rewriteFields[$tagName];
                                }

                                $path = AmazonXSDTools::searchPath($productInstance, $tagName, $referenceElement);


                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s:#%d variation field: %s, path: %s'."\n", basename(__FILE__), __LINE__), print_r($tagName, true), print_r($path, true));
                                }

                                if ($path) {
                                    $profiles['parameters'][$id][$id_lang][$tagName] = $path;
                                }
                            }
                        }

                        $price_rule = is_array($profiles) && isset($profiles['price_rule']) ? $profiles['price_rule'][$id][$id_lang] : null;

                        if (isset($price_rule) && is_array($price_rule) && isset($price_rule['rule']['from']) && is_array($price_rule['rule']['from']) && is_array($price_rule['rule']['to'])) {
                            foreach ($price_rule['rule']['from'] as $index => $val) {
                                if (!is_numeric($val)) {
                                    unset($price_rule['rule']['from'][$index]);
                                    unset($price_rule['rule']['to'][$index]);
                                    unset($price_rule['rule']['percent'][$index]);
                                    unset($price_rule['rule']['value'][$index]);
                                }
                            }

                            if (is_array($price_rule['rule']['from']) && count($price_rule['rule']['from'])) {
                                $price_rule['rule']['from'] = array_values($price_rule['rule']['from']);
                                $price_rule['rule']['to'] = array_values($price_rule['rule']['to']);
                                $price_rule['rule']['percent'] = array_values($price_rule['rule']['percent']);
                                $price_rule['rule']['value'] = array_values($price_rule['rule']['value']);
                            }

                            $profiles['price_rule'][$id][$id_lang] = null;

                            if (isset($price_rule['rule']['from']) && is_array($price_rule['rule']['from']) && count($price_rule['rule']['from']) && is_numeric(max($price_rule['rule']['from']))) {
                                $profiles['price_rule'][$id][$id_lang] = $price_rule;
                            }
                        }

                        $profiles['category'][$id][$id_lang] = $category;
                    } catch (Exception $e) {
                        $this->_postErrors[] = $this->l('Amazon XSD Exception :').$e->getMessage();
                    }
                }
            }
        }
        if ($profiles && is_array($profiles) && count($profiles)) {
            AmazonConfiguration::updateValue('profiles', $profiles);

            if (Amazon::$debug_mode) {
                CommonTools::d(sprintf('%s:#%d profiles: %s'."\n", basename(__FILE__), __LINE__, print_r($profiles, true)));
            }
        }

        return;
    }

    private function _displayForm()
    {
        if (self::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        if (defined('Carrier::ALL_CARRIERS')) {
            $all_carriers = Carrier::ALL_CARRIERS;
        } elseif (defined('ALL_CARRIERS')) {
            $all_carriers = ALL_CARRIERS;
        } else {
            $all_carriers = 5;
        }
        self::$carriers = Carrier::getCarriers($this->id_lang, false, false, false, null, $all_carriers);

        $this->_addJQueryUI('ui.widget');
        $this->_addJQueryUI('ui.draggable');

        $this->_loadSettings();

        $amazon_support = new AmazonSupport();
        $view_params = array(
            'header' => array(
                'description' => $this->description,
                'module_path' => $this->path,
                'experimental' => self::ENABLE_EXPERIMENTAL_FEATURES,
                'widget' => isset($this->amazon_features['module']) && ($this->amazon_features['module'] == 'amazon'),
                'widget_data' => $amazon_support->getWidget($this->name, $this->displayName, $this->version),
                'documentation' => AmazonSupport::gethreflink(),
                'images_url' => $this->images,
                'module_url' => $this->url,
                'version' => $this->version,
                'debug' => (bool)$this->config['debug'],
                'debug_content' => sprintf('Memory Peak: %.02f MB Post Count: %d',
                    memory_get_peak_usage() / 1024 / 1024, is_array($_POST) ? count($_POST, COUNT_RECURSIVE) : ''),

                'error'     => false,
                'warning'   => false,
                'class_warning' => 'warn ' . ($this->ps16x ? 'alert alert-warning' : ''),
                'class_error'   => 'error ' . ($this->ps16x ? 'alert alert-danger' : ''),
                'class_success' => 'confirm ' . ($this->ps16x ? 'alert alert-success' : 'conf'),
                'class_info'    => 'hint ' . ($this->ps16x ? 'alert alert-info' : 'conf'),

                // Scripts to inject in configure header
                'scripts' => array(
                    $this->url.'views/js/amazon.js',
                    $this->url.'views/js/configure/amazon-information.js',
                    $this->url.'views/js/amazon-profile.js',
                    $this->url.'views/js/amazon-features.js',
                    $this->url.'views/js/amazon-mappings.js',
                    $this->url.'views/js/amazon-repricing.js',
                    $this->url.'views/js/amazon-shipping.js',
                    $this->url.'views/js/amazon-messaging.js',
                    $this->url.'views/js/amazon-fba.js',
                    $this->url.'views/js/configure/html2canvas.min.js',
                )
            )
        );

        if (is_array($this->_postErrors) && count($this->_postErrors)) {
            $view_params['header']['error'] = true;
            $view_params['header']['error_content'] = null;

            foreach ($this->_postErrors as $err) {
                $view_params['header']['error_content'] .= $err.html_entity_decode("&lt;br /&gt;");
            }
        }

        if ($this->warning) {
            $view_params['header']['warning'] = true;
            $view_params['header']['warning_content'] = $this->warning;
        }

        $this->context->smarty->assign($view_params);
        $this->context->smarty->assign(array('psIs16' => $this->ps16x, 'psIs15' => $this->ps15x, 'psIs14' => $this->ps14x));

        $this->_html .= $this->context->smarty->fetch($this->path.self::$templates[self::TEMPLATE_HEADER]);

        $this->_content();
    }

    private function setInstantToken()
    {
        return(Configuration::UpdateValue('AMAZON_INSTANT_TOKEN', md5(_PS_ROOT_DIR_._PS_VERSION_.isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time()), false, 0, 0));
    }

    private function _loadSettings()
    {
        $this->config['instant_token'] =  Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0);

        if (AmazonConfiguration::get('TAXES') === false) {
            $this->config['taxes'] = false;
        } else {
            $this->config['taxes'] = AmazonConfiguration::get('TAXES');
        }

        $this->config['out_of_stock'] = AmazonConfiguration::get('OUT_OF_STOCK');
        $this->config['price_rule'] = AmazonConfiguration::get('PRICE_RULE');
        $this->config['rounding'] = AmazonConfiguration::get('PRICE_ROUNDING');
        $this->config['ptc'] = AmazonConfiguration::get('PTC');
        $this->config['default_tax_rule'] = AmazonConfiguration::get(self::CONFIG_DEFAULT_TAX_RULE_FOR_MP);
        $this->config['sort_order'] = AmazonConfiguration::get('SORT_ORDER');
        $this->config['asin_has_priority'] = AmazonConfiguration::get('USE_ASIN');

        // Carrier configuration table
        //
        $this->config['incoming_carrier_amazon'] = AmazonConfiguration::get('AMAZON_CARRIER');
        $this->config['incoming_carrier_prestashop'] = AmazonConfiguration::get('CARRIER');
        $this->config['outgoing_carrier'] = AmazonConfiguration::get('CARRIER_DEFAULT');
        $this->config['multichannel_carrier'] = AmazonConfiguration::get('CARRIER_MULTICHANNEL');

        // Shipping Overrides Settings
        //
        $this->config['shipping_override_std'] = AmazonConfiguration::get('SHIPPING_OVERRIDES_STD');
        $this->config['shipping_override_exp'] = AmazonConfiguration::get('SHIPPING_OVERRIDES_EXP');
        // Field for Synch
        //
        $this->config['synch_field'] = AmazonConfiguration::get('FIELD');


        $this->config['debug'] = self::$debug_mode = Configuration::get('AMAZON_DEBUG_MODE');

        if (!is_numeric($this->config['debug'])) {
            //backward compatibility workarround

            $this->config['debug'] = false;
        } else {
            $this->config['debug'] = (bool)$this->config['debug'];
        }

        $this->config['specials'] = AmazonConfiguration::get('SPECIALS') ? true : false;
        $this->config['specials_apply_rules'] = (bool)Configuration::get('AMAZON_SPECIALS_APPLY_RULES');
        $this->config['preorder'] = (bool)Configuration::get('AMAZON_PREORDER');
        $this->config['delete_products'] = AmazonConfiguration::get('DELETE_PRODUCTS') ? true : false;
        $this->config['stocks_only'] = AmazonConfiguration::get('STOCK_ONLY') ? true : false;
        $this->config['prices_only'] = Configuration::get('AMAZON_PRICES_ONLY') ? true : false;

        $this->config['title_format'] = AmazonConfiguration::get('TITLE_FORMAT');
        $this->config['account_type'] = AmazonConfiguration::get('ACCOUNT_TYPE');

        if (!is_numeric($this->config['title_format'])) {
            $this->config['title_format'] = self::FORMAT_TITLE;
        }

        if (empty($this->config['account_type']) || !is_numeric($this->config['account_type'])) {
            $this->config['account_type'] = self::ACCOUNT_TYPE_INDIVIDUAL;
        }

        $this->config['condition_map'] = AmazonConfiguration::get('CONDITION_MAP');

        // Marketplace Settings
        //
        $this->config['actives'] = AmazonConfiguration::get('ACTIVE');
        $this->config['amazon_merchant_ids'] = AmazonConfiguration::get('MERCHANT_ID');
        $this->config['amazon_marketplace_ids'] = AmazonConfiguration::get('MARKETPLACE_ID');
        $this->config['amazon_key_ids'] = AmazonConfiguration::get('AWS_KEY_ID');
        $this->config['amazon_secret_ids'] = AmazonConfiguration::get('SECRET_KEY');
        $this->config['amazon_mws_token'] = AmazonConfiguration::get('MWS_TOKEN');

        if (!is_array($this->config['actives'])) {
            $this->config['actives'] = array();
        }

        $this->config['features']['debug_mode'] = (bool)$this->config['debug']; //retro-compatibility

        $this->config['marketplace_master'] = AmazonConfiguration::get(self::CONFIG_MASTER);

        $this->config['regions'] = AmazonConfiguration::get('REGION');
        $this->config['currencies'] = AmazonConfiguration::get('CURRENCY');

        // Security
        //
        $this->config['tokens'] = AmazonConfiguration::get('CRON_TOKEN');

        // Categories
        //
        $this->config['categories'] = AmazonConfiguration::get('categories');
        $this->config['profiles_to_categories'] = AmazonConfiguration::get('profiles_categories');
        $this->config['brute_force'] = AmazonConfiguration::get('BRUTE_FORCE');

        // Mappings
        //
        $mapping = AmazonConfiguration::get('mapping');

        if (is_array($mapping) && count($mapping)) {
            $this->config['mapping'] = $mapping;
        } else {
            $this->config['mapping'] = array();
        }


        // Orders States
        //
        $this->config['order_state'] = AmazonConfiguration::get('ORDER_STATE');
        $this->config['preparation_state'] = AmazonConfiguration::get('PREPARATION_STATE');
        $this->config['send_state'] = AmazonConfiguration::get('SENT_STATE');
        $this->config['canceled_state'] = AmazonConfiguration::get('CANCELED_STATE');

        if (!is_array($this->config['order_state']) && (int)$this->config['order_state']) {
            // compatibility with previous versions

            $previous_order_state = (int)$this->config['order_state'];
            $this->config['order_state'] = array();
            $this->config['order_state'][self::ORDER_STATE_STANDARD] = $previous_order_state;
            $this->config['order_state'][self::ORDER_STATE_PRIMEORDER] = $previous_order_state;

            if ($this->config['preorder']) {
                $this->config['order_state'][self::ORDER_STATE_PREORDER] = $previous_order_state;
            }
        } elseif (!isset($this->config['order_state']) || !is_array($this->config['order_state'])) {
            $this->config['order_state'] = array();
            $this->config['order_state'][self::ORDER_STATE_STANDARD] = defined('_PS_OS_PAYMENT_') ? _PS_OS_PAYMENT_ : (int)Configuration::get('PS_OS_PAYMENT');
            $this->config['order_state'][self::ORDER_STATE_PRIMEORDER] = defined('_PS_OS_PAYMENT_') ? _PS_OS_PAYMENT_ : (int)Configuration::get('PS_OS_PAYMENT');

            if ($this->config['preorder']) {
                $this->config['order_state'][self::ORDER_STATE_PREORDER] = defined('_PS_OS_PAYMENT_') ? _PS_OS_PAYMENT_ : (int)Configuration::get('PS_OS_PAYMENT');
            }
        }

        if (!$this->config['preorder'] && is_array($this->config['order_state'])) {
            $this->config['order_state'][self::ORDER_STATE_PREORDER] = null;
        }
        if (is_array($this->config['order_state']) && !isset($this->config['order_state'][self::ORDER_STATE_PRIMEORDER])) {
            $this->config['order_state'][self::ORDER_STATE_PRIMEORDER] = defined('_PS_OS_PAYMENT_') ? _PS_OS_PAYMENT_ : (int)Configuration::get('PS_OS_PAYMENT');
        }

        if ($this->config['preparation_state'] === false) {
            $this->config['preparation_state'] = defined('_PS_OS_PREPARATION_') ? _PS_OS_PREPARATION_ : (int)Configuration::get('PS_OS_PREPARATION');
        }

        if ($this->config['send_state'] === false) {
            $this->config['send_state'] = defined('_PS_OS_SHIPPING_') ? _PS_OS_SHIPPING_ : (int)Configuration::get('PS_OS_SHIPPING');
        }

        if ($this->config['canceled_state'] === false) {
            $this->config['canceled_state'] = defined('_PS_OS_CANCELED_') ? _PS_OS_CANCELED_ : (int)Configuration::get('PS_OS_CANCELED');
        }

        // FBA Settings
        //
        $this->config['fba_order_state'] = Configuration::get('AMAZON_FBA_ORDER_STATE');
        $this->config['fba_multichannel_state'] = Configuration::get('AMAZON_FBA_MULTICHANNEL_STATE');
        $this->config['fba_multichannel_sent_state'] = Configuration::get('AMAZON_FBA_MULTICHANNEL_SENT');
        $this->config['fba_multichannel_done_state'] = Configuration::get('AMAZON_FBA_MULTICHANNEL_DONE');

        $this->config['fba_formula'] = AmazonConfiguration::get('FBA_PRICE_FORMULA');
        $this->config['fba_multichannel'] = (bool)Configuration::get('AMAZON_FBA_MULTICHANNEL');
        $this->config['fba_multichannel_auto'] = (bool)Configuration::get('AMAZON_FBA_MULTICHANNEL_AUTO');
        $this->config['fba_decrease_stock'] = (bool)Configuration::get('AMAZON_FBA_DECREASE_STOCK');
        $this->config['fba_stock_behaviour'] = Configuration::get('AMAZON_FBA_STOCK_BEHAVIOUR');
        $this->config['fba_notification'] = Configuration::get('AMAZON_FBA_NOTIFICATION');

        if (!$this->config['fba_stock_behaviour']) {
            $this->config['fba_stock_behaviour'] = self::FBA_STOCK_SWITCH;
        }
        if (!$this->config['fba_notification']) {
            $this->config['fba_notification'] = self::FBA_NOTIFICATION_BOTH;
        }

        if ($this->config['fba_order_state'] === false) {
            $this->config['fba_order_state'] = $this->config['order_state'];
        }

        // PS 1.5 - Warehouse for Advanced stock management
        //
        $this->config['warehouse'] = Configuration::get('AMAZON_WAREHOUSE');

        $this->config['employee'] = Configuration::get('AMAZON_EMPLOYEE');

        $id_group = (int)Configuration::get('AMAZON_CUSTOMER_GROUP');

        if (!$id_group || !is_numeric($id_group)) {
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $id_group = Configuration::get('PS_CUSTOMER_GROUP');
            } else {
                $id_group = (int)_PS_DEFAULT_CUSTOMER_GROUP_;
            }
        }

        $this->config['id_group'] = $id_group;
        $this->config['id_business_group'] = (int)Configuration::get('AMAZON_BUSINESS_CUSTOMER_GROUP');

        $this->config['auto_create'] = Configuration::get('AMAZON_AUTO_CREATE');
        $this->config['image_type'] = Configuration::get('AMAZON_IMAGE_TYPE');
        // Aug-23-2018: Remove Carriers/Modules setting
        $this->config['safe_encoding'] = Configuration::get('AMAZON_SAFE_ENCODING');

        $this->config['description_field'] = Configuration::get('AMAZON_DESCRIPTION_FIELD');
        $this->config['features'] = $this->amazon_features;

        $this->config['html_descriptions'] = (bool)Configuration::get('AMAZON_HTML_DESCRIPTIONS');
        $this->config['inactive_languages'] = (bool)Configuration::get('AMAZON_INACTIVE_LANGUAGES');
        $this->config['disable_ssl_check'] = (bool)Configuration::get('AMAZON_DISABLE_SSL_CHECK');

        $this->config['payment_region'] = Configuration::get('AMAZON_PAYMENT_REGION');

        // Filters
        $this->config['excluded_manufacturers'] = AmazonConfiguration::get('EXCLUDED_MANUFACTURERS');
        $this->config['excluded_suppliers'] = AmazonConfiguration::get('EXCLUDED_SUPPLIERS');
        $this->config['price_filter'] = AmazonConfiguration::get('PRICE_FILTER');

        // Email Notification
        $this->config['email'] = (bool)Configuration::get('AMAZON_EMAIL');

        $this->config['mail_invoice'] = AmazonConfiguration::get('MAIL_INVOICE');
        $this->config['mail_review'] = AmazonConfiguration::get('MAIL_REVIEW');
        $this->config['mail_customer_thread'] = AmazonConfiguration::get('CUSTOMER_THREAD');

        // Shipping Configuration
        //
        $this->config['shipping'] = AmazonConfiguration::get('SHIPPING');
        $this->config['shipping_methods'] = AmazonConfiguration::get('SHIPPING_METHODS');

        self::loadFeatures(false, $this->config['features']['expert_mode'] ? true : false);
        self::loadAttributes();

        $this->shipping_overrides_std = AmazonSettings::getShippingMethods(AmazonCarrier::SHIPPING_STANDARD);
        $this->shipping_overrides_exp = AmazonSettings::getShippingMethods(AmazonCarrier::SHIPPING_EXPRESS);
        $this->extra_carrier_codes = AmazonSettings::getShippingMethods(AmazonCarrier::SHIPPING_CODES);

        // Repricing Configuration
        $this->config['repricing'] = AmazonConfiguration::get('REPRICING');

        $profiles = AmazonConfiguration::get('profiles');

        if (is_array($profiles) && count($profiles) && isset($profiles['name']) && is_array($profiles['name'])) {
            $this->profiles = $profiles;
        } else {
            $this->profiles = array();
            $this->profiles['name'] = array();
        }
    }

    private function _tabs(&$view_params)
    {
        $selected_tab = $this->selectedTab();

        $view_params['tabs'] = array();
        $view_params['tabs']['images_url'] = $this->images;

        $view_params['tabs']['amazon'] = 'Amazon';
        $view_params['tabs']['amazon_selected'] = ($selected_tab === 'amazon' ? 'selected' : '');

        $view_params['tabs']['informations'] = $this->l('Informations');
        $view_params['tabs']['informations_selected'] = ($selected_tab === 'informations' ? 'selected' : '');

        $view_params['tabs']['features'] = $this->l('Features');
        $view_params['tabs']['features_selected'] = ($selected_tab === 'features' ? 'selected' : '');

        $view_params['tabs']['platforms'] = array();

        $country_iso_code = Configuration::get('PS_LOCALE_COUNTRY');

        foreach (AmazonTools::languages() as $language) {
            $index = $language['iso_code'];
            $view_params['tabs']['platforms'][$index]['iso_code'] = $index;
            $view_params['tabs']['platforms'][$index]['name_short'] = preg_replace('/ .*/', '', $language['name']);
            $view_params['tabs']['platforms'][$index]['name_long'] = $language['name'];
            $view_params['tabs']['platforms'][$index]['selected'] = ($selected_tab === $language['iso_code'] ? 'selected' : '');
            $view_params['tabs']['platforms'][$index]['geo_flag'] = $this->geoFlag($language['id_lang']);
            $view_params['tabs']['platforms'][$index]['area'] = $language['area'];
            $view_params['tabs']['platforms'][$index]['display'] = Tools::strtolower($country_iso_code) === Tools::strtolower($language['country_iso_code']) ? true : false;
        }

        $view_params['tabs']['parameters'] = $this->l('Parameters');
        $view_params['tabs']['parameters_selected'] = ($selected_tab === 'parameters' ? 'selected' : '');

        $view_params['tabs']['categories'] = $this->l('Categories');
        $view_params['tabs']['categories_selected'] = ($selected_tab === 'categories' ? 'selected' : '');

        $view_params['tabs']['mapping'] = $this->l('Mappings');
        $view_params['tabs']['mapping_selected'] = ($selected_tab === 'mapping' ? 'selected' : '');

        $view_params['tabs']['profiles'] = $this->l('Profiles');
        $view_params['tabs']['profiles_selected'] = ($selected_tab === 'profiles' ? 'selected' : '');

        $view_params['tabs']['shipping'] = $this->l('Shipping');
        $view_params['tabs']['shipping_selected'] = ($selected_tab === 'shipping' ? 'selected' : '');

        $view_params['tabs']['filters'] = $this->l('Filters');
        $view_params['tabs']['filters_selected'] = ($selected_tab === 'filters' ? 'selected' : '');

        $view_params['tabs']['messaging'] = $this->l('Messaging');
        $view_params['tabs']['messaging_selected'] = ($selected_tab === 'messaging' ? 'selected' : '');

        $view_params['tabs']['fba'] = $this->l('Amazon FBA');
        $view_params['tabs']['fba_selected'] = ($selected_tab === 'fba' ? 'selected' : '');

        $view_params['tabs']['repricing'] = $this->l('Repricing');
        $view_params['tabs']['repricing_selected'] = ($selected_tab === 'repricing' ? 'selected' : '');

        $view_params['tabs']['tools'] = $this->l('Tools');
        $view_params['tabs']['tools_selected'] = ($selected_tab === 'tools' ? 'selected' : '');

        $view_params['tabs']['cron'] = $this->l('Scheduled Tasks');
        $view_params['tabs']['cron_selected'] = ($selected_tab === 'cron' ? 'selected' : '');

        $view_params['tabs']['debug'] = $this->l('Debug Mode');
        $view_params['tabs']['debug_selected'] = ($selected_tab === 'debug' ? 'selected' : '');
    }

    private function selectedTab()
    {
        return (($selected_tab = Tools::getValue('selected_tab')) ? $selected_tab : 'amazon');
    }

    public function geoFlag($id_lang)
    {
        if (isset($this->config) && isset($this->config['regions'][$id_lang]) && $this->config['regions'][$id_lang]) {
            $region = $this->config['regions'][$id_lang];
        } elseif ($id_lang) {
            $region = Language::getIsoById($id_lang);
        }

        if (!$region) {
            $region = 'na';
        }

        return ($region);
    }

    private function _content()
    {
        $view_params = array();

        // Amazon Tab Content
        //
        $this->_glossary($view_params);

        // Amazon Tab Content
        //
        $this->_amazon($view_params);

        // Informations Tab Content
        //
        $this->_informations($view_params);

        // Informations Tab Content
        //
        $this->_features($view_params);

        // Marketplaces Tabs Content
        //
        foreach (AmazonTools::languages() as $language) {
            $id_lang     = $language['id_lang'];
            $country_iso = $language['country_iso_code'];
            $id_country  = Country::getByIso($country_iso);
            $this->marketplaceTab($view_params, $id_lang, $id_country);
        }

        // Parameters Tab Content
        //
        $this->_parameters($view_params);

        // Categories Tab
        //
        $this->_categories($view_params);

        // Mappings
        //
        $this->_mapping($view_params);

        // Profiles
        //
        $this->_profiles($view_params);

        // Filters
        //
        $this->_filters($view_params);

        // Filters
        //
        $this->_shipping($view_params);

        // Messaging
        //
        $this->_messaging($view_params);

        // Tools
        //
        $this->_tools($view_params);

        // FBA
        //
        $this->_fba($view_params);

        // Repricing
        //
        $this->_repricing($view_params);

        // Crons
        //
        $this->_cron($view_params);


        // Main Tabs
        $this->_tabs($view_params);

        //
        // Render Body
        //
        $context_param = sprintf('&context_key=%s', AmazonContext::getKey($this->context->shop));

        $view_params['configure'] = array();
        $view_params['configure']['tabs'] = array();

        $view_params['configure']['form_action'] = $_SERVER['REQUEST_URI'];
        $view_params['configure']['id_lang'] = $this->id_lang;
        $view_params['configure']['check_url'] = $this->url.'functions/check.php?'.$context_param.'&instant_token='.$this->config['instant_token'];
        $view_params['configure']['check_msg_region'] = $this->l('You must select first a platform');
        $view_params['configure']['check_msg_currency'] = $this->l('You must select first a currency');
        $view_params['configure']['selected_tab'] = $this->selectedTab();

        $view_params['configure']['tabs']['amazon'] = $this->path.self::$templates[self::TEMPLATE_TAB_AMAZON];
        $view_params['configure']['tabs']['informations'] = $this->path.self::$templates[self::TEMPLATE_TAB_INFO];
        $view_params['configure']['tabs']['features'] = $this->path.self::$templates[self::TEMPLATE_TAB_FEATURES];

        $view_params['configure']['tabs']['settings'] = $this->path.self::$templates[self::TEMPLATE_TAB_SETTINGS];
        $view_params['configure']['tabs']['parameters'] = $this->path.self::$templates[self::TEMPLATE_TAB_PARAMETERS];
        $view_params['configure']['tabs']['mapping'] = $this->path.self::$templates[self::TEMPLATE_TAB_MAPPING];
        $view_params['configure']['tabs']['categories'] = $this->path.self::$templates[self::TEMPLATE_TAB_CATEGORIES];
        $view_params['configure']['tabs']['profiles'] = $this->path.self::$templates[self::TEMPLATE_TAB_PROFILES];
        $view_params['configure']['tabs']['filters'] = $this->path.self::$templates[self::TEMPLATE_TAB_FILTERS];
        $view_params['configure']['tabs']['shipping'] = $this->path.self::$templates[self::TEMPLATE_TAB_SHIPPING];
        $view_params['configure']['tabs']['messaging'] = $this->path.self::$templates[self::TEMPLATE_TAB_MESSAGING];

        $view_params['configure']['tabs']['fba'] = $this->path.self::$templates[self::TEMPLATE_TAB_FBA];

        $view_params['configure']['tabs']['repricing'] = $this->path.self::$templates[self::TEMPLATE_TAB_REPRICING];

        $view_params['configure']['tabs']['tools'] = $this->path.self::$templates[self::TEMPLATE_TAB_TOOLS];
        $view_params['configure']['tabs']['cron'] = $this->path.self::$templates[self::TEMPLATE_TAB_CRON];
        $view_params['configure']['tabs']['glossary'] = $this->path.self::$templates[self::TEMPLATE_TAB_GLOSSARY];

        $view_params['class_warning'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
        $view_params['class_error'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
        $view_params['class_success'] = 'confirm '.($this->ps16x ? 'alert alert-success' : 'conf');
        $view_params['class_info'] = 'hint '.($this->ps16x ? 'alert alert-info' : 'conf');

        //
        // Render Smarty
        //
        $this->context->smarty->assign($view_params);


        $this->_html .= $this->context->smarty->fetch($this->path.self::$templates[self::TEMPLATE_TABS]);


        $this->_html .= $this->context->smarty->fetch($this->path.self::$templates[self::TEMPLATE_CONFIGURE]);

        // Footer
        //
        $footer_params = array();
        $footer_params['footer']['text_footer'] = $this->l('This information is provided by The Amazon Marketplace. Please go to the following url to subscribe to these services');

        $this->context->smarty->assign($footer_params);

        $this->_html .= $this->context->smarty->fetch($this->path.self::$templates[self::TEMPLATE_FOOTER]);
    }

    private function _amazon(&$view_params)
    {
        $view_params['amazon']['selected_tab'] = $this->selectedTab() === 'amazon' ? true : false;
        $view_params['amazon']['images'] = $this->images;
        $view_params['amazon']['name'] = $this->displayName;
        $view_params['amazon']['description'] = $this->description;
        $view_params['amazon']['documentation'] = AmazonSupport::gethreflink();
        $view_params['amazon']['images_url'] = $this->images;
        $view_params['amazon']['version'] = $this->version;
        $view_params['amazon']['ps_version'] = _PS_VERSION_;
        $view_params['amazon']['is_lite'] = isset($this->amazon_features['module']) && $this->amazon_features['module'] == 'amazonlite';

        $current_lang = $this->context->language->iso_code;
        $view_params['amazon']['lang'] = $current_lang;
        $view_params['amazon']['lang_fr'] = $current_lang == 'fr';

        $view_params['amazon']['support_info'] = array();
        $view_params['amazon']['support_info']['subject'] = $this->l('Support for Amazon');
        $view_params['amazon']['support_info']['body'] = sprintf($this->l('Hi, I have a problem with my amazon module v%s on Pretashop v%s.'), $this->version, _PS_VERSION_);

        return ($view_params);
    }

    private function _glossary(&$view_params)
    {
        $lang_admin = Language::getIsoById($this->id_lang);

        switch ($lang_admin) {
            case 'fr':
            case 'it':
            case 'de':
            case 'es':
                break;
            default:
                $lang_admin = 'en';
        }

        $view_params['glossary'] = AmazonSettings::getGlossary($lang_admin, 'configuration');


        return ($view_params);
    }

    private function _informations(&$view_params)
    {
        require_once(dirname(__FILE__).'/classes/amazon.certificates.class.php');
        require_once(dirname(__FILE__).'/classes/amazon.configuration_check.class.php');

        if ((bool)Configuration::get('PS_FORCE_SMARTY_2') == true) {
            die(sprintf(html_entity_decode('&lt;div class="error"&gt;%s&lt;/span&gt;'), Tools::displayError('This module is not compatible with Smarty v2. Please switch to Smarty v3 in Preferences Tab.')));
        }

        $lang = Language::getIsoById($this->id_lang);

        // Display only if the module seems to be configured
        //
        $display = true;
        foreach (array(
                     'actives',
                     'amazon_merchant_ids',
                     'amazon_marketplace_ids'
                 ) as $configuration_item) {
            if (!is_array($this->config[$configuration_item]) || !count($this->config[$configuration_item]) || !max($this->config[$configuration_item])) {
                $display = false;
            }
        }

        $php_infos = array();
        $prestashop_infos = array();
        $env_infos = array();
        $module_infos = array();
        $prestashop_info_ok = $php_info_ok = true;

        $currentVersion = Configuration::get('AMAZON_CURRENT_VERSION', null, 0, 0);
        $update_mode = false;

        if ($currentVersion && version_compare($this->version, $currentVersion, '>')) {
            $module_infos['update'] = array();
            $module_infos['update']['message'] = sprintf($this->l('Module Update: Your version will be auto-updated from %s to %s after configuration changes'), $currentVersion, $this->version);
            $module_infos['update']['message'] .= ' - '.$this->l('Please verify again your settings. Please clear your Smarty and Browser caches...');
            $module_infos['update']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_AFTER_INSTALLATION);
            $module_infos['update']['level'] = 'info '.($this->ps16x ? 'alert alert-warning' : 'warn');
            $module_infos['update']['display'] = true;
            $update_mode = true;
        }

        if (!$update_mode) {
            if ($this->config['features']['creation'] && (!AmazonValidValues::tableExists() || !AmazonValidValues::lastImport())) {
                $module_infos['valid_values'] = array();
                $module_infos['valid_values']['message'] = $this->l('You should import Amazon valid values table from Tools tab');
                $module_infos['valid_values']['level'] = 'info '.($this->ps16x ? 'alert alert-info' : '');
                $module_infos['valid_values']['display'] = true;
            }

            if (!AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS)) {
                $module_infos['missing_table_options'] = array();
                $module_infos['missing_table_options']['message'] = sprintf('%s: %s', $this->l('Missing required table'), _DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS);
                $module_infos['missing_table_options']['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
                $module_infos['missing_table_options']['display'] = true;
            }
            if (!AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS)) {
                $module_infos['missing_table_action'] = array();
                $module_infos['missing_table_action']['message'] = sprintf('%s: %s', $this->l('Missing required table'), _DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS);
                $module_infos['missing_table_action']['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
                $module_infos['missing_table_action']['display'] = true;
            }
            if (!AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS)) {
                $module_infos['missing_table_orders'] = array();
                $module_infos['missing_table_orders']['message'] = sprintf('%s: %s', $this->l('Missing required table'), _DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS);
                $module_infos['missing_table_orders']['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
                $module_infos['missing_table_orders']['display'] = true;
            }


            if ($this->config['features']['expert_mode']) {
                $module_infos['expert_mode'] = array();
                $module_infos['expert_mode']['message'] = $this->l('Expert Mode is active');
                $module_infos['expert_mode']['level'] = 'info '.($this->ps16x ? 'alert alert-warning' : 'warn');
                $module_infos['expert_mode']['display'] = true;
            }

            if ($this->config['debug']) {
                $module_infos['debug'] = array();
                $module_infos['debug']['message'] = $this->l('Debug Mode is activated, what is not recommended');
                $module_infos['debug']['level'] = 'info '.($this->ps16x ? 'alert alert-warning' : 'warn');
                $module_infos['debug']['display'] = true;
            }

            if ($this->config['categories'] === false && is_array($this->config['categories']) && !count($this->config['categories'])) {
                $module_infos['categories'] = array();
                $module_infos['categories']['message'] = $this->l('You didn\'t checked yet any category, in category tab');
                $module_infos['categories']['level'] = 'info '.($this->ps16x ? 'alert alert-warning' : 'warn');
                $module_infos['categories']['display'] = true;
            }

            if (!$this->active) {
                $module_infos['inactive'] = array();
                $module_infos['inactive']['message'] = $this->l('Be careful, your module is inactive, this mode stops all pending operations for this module, please change the status to active in your module list');
                $module_infos['inactive']['level'] = 'info '.($this->ps16x ? 'alert alert-warning' : 'warn');
                $module_infos['inactive']['display'] = true;
            }

            $dirs = array(
                _PS_MODULE_DIR_.MODULE_AMAZON.''.DIRECTORY_SEPARATOR.'validate'.DIRECTORY_SEPARATOR.'xsd',
                _PS_MODULE_DIR_.MODULE_AMAZON.''.DIRECTORY_SEPARATOR.'export',
                _PS_MODULE_DIR_.MODULE_AMAZON.''.DIRECTORY_SEPARATOR.'import',
                _PS_MODULE_DIR_.MODULE_AMAZON.''.DIRECTORY_SEPARATOR.'settings'
            );

            $messages = array();

            if ($this->amazon_features['creation']) {
                foreach ($dirs as $dir) {
                    if (!AmazonTools::isDirWriteable($dir)) {
                        $messages[] = sprintf($this->l('You have to set write permissions to the %s directory'), $dir);
                    }
                }
            }

            if (is_array($messages) && count($messages)) {
                foreach ($messages as $key => $message) {
                    $module_infos['permissions_'.$key] = array();
                    $module_infos['permissions_'.$key]['message'] = $message;
                    $module_infos['permissions_'.$key]['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
                    $module_infos['permissions_'.$key]['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_PERMISSIONS);
                    $module_infos['permissions_'.$key]['display'] = true;
                }
            }

            // AJAX Checker
            //
            $env_infos['ajax'] = array();
            $env_infos['ajax']['message'] = $this->l('AJAX execution failed. Please, verify first your module configuration. If the problem persists please send a screenshot of this page to the support.');
            $env_infos['ajax']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_DOMAIN);
            $env_infos['ajax']['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
            $env_infos['ajax']['display'] = false;
            $env_infos['ajax']['script'] = array(
            'name' => 'env_check_url',
            'url' => $this->url.'functions/check_env.php?action=ajax'
            );

            // Amazon Ping
            //
            $ping_url = $this->url.'functions/check.php?id_lang='.$this->id_lang.'&instant_token='.$this->config['instant_token'].'&action=service-status';
            $ping_debug_url = AmazonTools::getHttpHost(true, true).$ping_url.'&debug=1';

            if ($display) {
                $env_infos['ping'] = array();
                $env_infos['ping']['message'] = sprintf($this->l('Unable to ping Amazon. Please provide this URL to the support: %s'), $ping_debug_url);
                $env_infos['ping']['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
                $env_infos['ping']['display'] = false;
                $env_infos['ping']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_PING);
                $env_infos['ping']['script'] = array(
                'name' => 'service_check_url',
                'url' => $ping_url
                );
            }
        }

        // max_input_var Checker
        //
        $env_infos['miv'] = array();
        $env_infos['miv']['message'] = sprintf($this->l('Your PHP configuration limits the maximum number of fields to post in a form : %s for max_input_vars. Please ask your hosting provider to increase this limit.'), ini_get('max_input_vars'));
        $env_infos['miv']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_PHP);
        $env_infos['miv']['level'] = $this->ps16x ? 'alert alert-danger' : 'error';
        $env_infos['miv']['display'] = false;
        $env_infos['miv']['script'] = array('name' => 'max_input_vars');

        if (AmazonTools::tableExists(_DB_PREFIX_.'configuration') === null) {
            $env_infos['show_tables_failed'] = array();
            $env_infos['show_tables_failed']['message'] = sprintf('%s: %s', $this->l('Your hosting doesnt allow'), 'SHOW TABLES');
            $env_infos['show_tables_failed']['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
            $env_infos['show_tables_failed']['display'] = true;
            $env_infos['show_tables_failed']['script'] = array('name' => 'show_tables');
        }

        // PHP Configuration Check
        //
        if (in_array(Tools::strtolower(@ini_get('display_errors')), array('1', 'on'))) {
            $php_infos['display_error']['message'] = $this->l('PHP display_errors is On.');
            $php_infos['display_error']['level'] = 'info '.($this->ps16x ? 'alert alert-info' : '');
        }

        if (!function_exists('curl_init')) {
            $php_infos['curl'] = array();
            $php_infos['curl']['message'] = $this->l('PHP cURL must be installed on this server. The module require the cURL library and can\'t work without it');
            $php_infos['curl']['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
            $php_infos['curl']['link'] = 'http://php.net/manual/'.$lang.'/book.curl.php';
        }
        if (!function_exists('hash_hmac')) {
            $php_infos['curl'] = array();
            $php_infos['curl']['message'] = $this->l('PHP Hash must be installed on this server. The module require the Hash library and can\'t work without it');
            $php_infos['curl']['level'] = $this->ps16x ? 'alert alert-danger' : 'error';
            $php_infos['curl']['link'] = 'http://php.net/manual/'.$lang.'/book.hash.php';
        }

        if (!function_exists('mb_convert_encoding')) {
            $php_infos['multibyte'] = array();
            $php_infos['multibyte']['message'] = $this->l('Multibyte PHP Library must be installed on this server. The module require the mb functions and can\'t work without it');
            $php_infos['multibyte']['level'] = $this->ps16x ? 'alert alert-danger' : 'error';
            $php_infos['multibyte']['link'] = 'http://php.net/manual/'.$lang.'/ref.mbstring.php';
        }

        if (!method_exists('DOMDocument', 'createElement')) {
            $php_infos['dom'] = array();
            $php_infos['dom']['message'] = $this->l('PHP DOMDocument (XML Library) must be installed on this server. The module require this library and can\'t work without it');
            $php_infos['dom']['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
            $php_infos['dom']['link'] = 'http://php.net/manual/'.$lang.'/class.domdocument.php';
        }


        if (($max_execution_time = ini_get('max_execution_time')) && $max_execution_time < 120) {
            $php_infos['max_execution_time']['message'] = sprintf($this->l('PHP value: max_execution_time recommended value is at least 120. your limit is currently set to %d'), $max_execution_time);
            $php_infos['max_execution_time']['level'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
            $php_infos['max_execution_time']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_PHP);
        }

        $disable_functions = array_map('trim', explode(',', ini_get('disable_functions')));

        if (is_array($disable_functions) && count($disable_functions)) {
            if (in_array('parse_ini_file', $disable_functions)) {
                $php_infos['parse_ini_file']['message'] = $this->l('PHP function: parse_ini_file() should be enabled. The module requires this function to display some translations.');
                $php_infos['parse_ini_file']['level'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
            }

            if (in_array('phpinfo', $disable_functions)) {
                $php_infos['parse_ini_file']['message'] = $this->l('PHP function: phpinfo() should be enabled. The module requires this function to provide some diags.');
                $php_infos['parse_ini_file']['level'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
            }
        }

        $recommended_memory_limit = 128;
        if ($memory_limit = AmazonConfigurationCheck::getMemoryLimit() < $recommended_memory_limit) {
            $php_infos['memory']['message'] = sprintf($this->l('PHP value: memory_limit recommended value is at least %sMB. your limit is currently set to %sMB'), $recommended_memory_limit, $memory_limit);
            $php_infos['memory']['level'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
            $php_infos['memory']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_PHP);
        }

        // Prestashop Configuration Check
        //
        if (!(int)Configuration::get('PS_SHOP_ENABLE')) {
            $prestashop_infos['maintenance']['message'] = $this->l('Be careful, your shop is in maintenance mode, the module might not work in that mode');
            $prestashop_infos['maintenance']['level'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
        }

        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ === true) {
            $prestashop_infos['mod_dev']['message'] = $this->l('Prestashop _PS_MODE_DEV_ is active.');
            $prestashop_infos['mod_dev']['level'] = 'info '.($this->ps16x ? 'alert alert-info' : '');
            $prestashop_infos['mod_dev']['id'] = 'prestashop-info-dev';
        }

        if ((bool)Configuration::get('PS_CATALOG_MODE')) {
            $prestashop_infos['catalog']['message'] = $this->l('Your store is in catalog mode, you won\'t be able to import orders, you can switch off this mode in Preferences > Products tab');
            $prestashop_infos['catalog']['level'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
        }

        if (AmazonConfigurationCheck::hasOverrides()) {
            $prestashop_infos['overrides']['message'] = $this->l('Your Prestashop potentially runs some overrides. This information is necessary only in case of support');
            $prestashop_infos['overrides']['level'] = 'info '.($this->ps16x ? 'alert alert-info' : '');
        }

        if (!AmazonConfigurationCheck::checkShopUrl()) {
            $prestashop_infos['wrong_domain']['message'] = $this->l('Your are currently connected with the following domain name:').
                                                           html_entity_decode(' &lt;span style="color:navy"&gt;'.$_SERVER['HTTP_HOST'].'&lt;/span&gt;').nl2br("").
                                                           $this->l('This one is different from the main store domain name set in \"Preferences > SEO & URLs\":').
                                                           html_entity_decode(' &lt;span style="color:green"&gt;'.Configuration::get('PS_SHOP_DOMAIN').'&lt;/span&gt;');
            $prestashop_infos['wrong_domain']['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
            $prestashop_infos['wrong_domain']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_DOMAIN);
        }

        if (AmazonConfiguration::shopIsFeatureActive() && in_array($this->context->shop->getContext(), array(Shop::CONTEXT_GROUP, Shop::CONTEXT_ALL))) {
            $prestashop_infos['multistore']['message'] = $this->l('You are in multishop environment. To use Amazon module, you must select a target shop.');
            $prestashop_infos['multistore']['level'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
            $prestashop_infos['multistore']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_MULTISTORE);
        }

        foreach (array('birthday', 'company', 'siret', 'optin', 'newsletter') as $field) {
            if (!AmazonConfigurationCheck::mandatoryCustomerField($field)) {
                $prestashop_infos[$field.'_issue']['message'] = sprintf($this->l('%s field is required, this is not a required value by default in Prestashop core program. This configuration is not allowed by Marketplaces modules. Please fix it!'), AmazonTools::ucfirst($field));
                $prestashop_infos[$field.'_issue']['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
            }
        }

        if (!AmazonConfigurationCheck::checkAddress()) {
            $prestashop_infos['phone_issue']['message'] = $this->l('Phone field is not required by default, but required in your configuration. This configuration is not allowed by Marketplaces modules. Please fix it !');
            $prestashop_infos['phone_issue']['level'] = $this->ps16x ? 'alert alert-danger' : 'error';
        }

        // Check Orders States
        //
        $i = 0;
        $order_states = array('ORDER_STATE', 'PREPARATION_STATE', 'SENT_STATE');

        if ($this->amazon_features['cancel_orders']) {
            $order_states[] = 'CANCELED_STATE';
        }

        foreach ($order_states as $order_state_config) {
            $id_order_state = AmazonConfiguration::get($order_state_config);

            if ($id_order_state) {
                if (is_array($id_order_state) && count($id_order_state)) {
                    // new format

                    $check_states = $id_order_state;
                } elseif (is_numeric($id_order_state)) {
                    $check_states = array($id_order_state);
                } else {
                    continue;
                }

                foreach ($check_states as $id_order_state) {
                    $order_state = new OrderState($id_order_state, $this->id_lang);

                    if (Validate::isLoadedObject($order_state)) {
                        if ($order_state->send_email) {
                            $prestashop_infos['mail'.$i]['message'] = sprintf($this->l('The order status: \"%s\" automatically send an email. This is not allowed by Amazon. Please configure another order status.'), $order_state->name);
                            $prestashop_infos['mail'.$i]['level'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
                            $prestashop_infos['mail'.$i]['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_NO_MAIL);
                            $i++;
                        }
                    }
                }
            }
        }

        if (!AmazonConfigurationCheck::checkCountryConsistency()) {
            $prestashop_infos['locale_country']['message'] = sprintf('Inconsistency in localization settings, country code: "%s"', Configuration::get('PS_LOCALE_COUNTRY'));
            $prestashop_infos['locale_country']['level'] = $this->ps16x ? 'alert alert-danger' : 'error';
        }

        $lang_iso_code = Tools::strtolower(Configuration::get('PS_LOCALE_LANGUAGE'));
        $pass = true;
        $level = $this->ps16x ? 'alert alert-danger' : 'error';

        if (!empty($lang_iso_code)) {
            if (!Validate::isLanguageIsoCode($lang_iso_code) || !Language::getIdByIso($lang_iso_code)) {
                $pass = false;
                $message = 'Localization > Locale Language setting doesnt match any lang in Prestashop tables';
            } elseif (!AmazonTools::lang2MarketplaceId($lang_iso_code)) {
                $pass = false;
                $message = 'Support Info: Localization > Locale Language setting doesnt match any Amazon platform';
                $level = 'info '.($this->ps16x ? 'alert alert-info' : '');
            }
        } else {
            $message = 'Localization > Locale Language setting is empty !';
            $pass = false;
        }

        if (!$pass) {
            $prestashop_infos['locale_lang']['message'] = sprintf('%s: "%s"', $message, $lang_iso_code);
            $prestashop_infos['locale_lang']['level'] = $level;
        }

        if (!AmazonCertificates::getDefaultCertificatePath()) {
            $prestashop_infos['certificate']['message'] = sprintf('Unable to read default certificate (%s), please countact our support', AmazonCertificates::getDefaultCertificatePath());
            $prestashop_infos['certificate']['level'] = $this->ps16x ? 'alert alert-danger' : 'error';
        }

        if (!is_array($prestashop_infos) || !count($prestashop_infos)) {
            $prestashop_info_ok = true;
        } else {
            $prestashop_info_ok = false;
        }

        if (!is_array($php_infos) || !count($php_infos)) {
            $php_info_ok = true;
        } else {
            $php_info_ok = false;
        }

        $context_key = AmazonContext::getKey($this->context->shop);

        $max_input_vars = @ini_get('max_input_vars');

        $view_params['informations']['selected_tab'] = $this->selectedTab() === 'informations' ? true : false;
        $view_params['informations']['images'] = $this->images;
        $view_params['informations']['display'] = $display;
        $view_params['informations']['module_infos'] = $module_infos;
        $view_params['informations']['env_infos'] = $env_infos;
        $view_params['informations']['php_infos'] = $php_infos;
        $view_params['informations']['php_info_ok'] = $php_info_ok;
        $view_params['informations']['prestashop_infos'] = $prestashop_infos;
        $view_params['informations']['prestashop_info_ok'] = $prestashop_info_ok;
        $view_params['informations']['mode_dev'] = defined('_PS_MODE_DEV_') && _PS_MODE_DEV_;
        $view_params['informations']['support_informations_url'] = self::$usefull_urls['check'] = $this->url.'functions/check.php?id_lang='.$this->id_lang.'&instant_token='.$this->config['instant_token'].'&context_key='.$context_key;
        $view_params['informations']['max_input_vars'] = $max_input_vars;
        $view_params['informations']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_PHP);

        return ($view_params);
    }

    private function _features(&$view_params)
    {
        $view_params['features']['selected_tab'] = $this->selectedTab() === 'features' ? true : false;
        $view_params['features']['images'] = $this->images;
        $view_params['features']['experimental'] = self::ENABLE_EXPERIMENTAL_FEATURES;
        $view_params['features']['documentation'] = AmazonSupport::gethreflink();
        $view_params['features']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_FEATURES);
        $view_params['features']['images_url'] = $this->images;
        $view_params['features']['version'] = $this->version;
        $view_params['features']['ps_version'] = _PS_VERSION_;

        $view_params['features']['links'] = array();
        $view_params['features']['links']['synchronization'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_SYNCHRONIZATION);
        $view_params['features']['links']['creation'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_CREATION);
        $view_params['features']['links']['second_hand'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_SECOND_HAND);
        $view_params['features']['links']['prices_rules'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_PRICES_RULES);
        $view_params['features']['links']['europe'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_EUROPE);
        $view_params['features']['links']['orders'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_ORDERS_IMPORT);
        $view_params['features']['links']['gcid'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_GCID);
        $view_params['features']['links']['filters'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_FILTERS);
        $view_params['features']['links']['import_products'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_IMPORT_PRODUCTS);
        $view_params['features']['links']['offers'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_OFFERS);
        $view_params['features']['links']['fba'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_FBA);
        $view_params['features']['links']['repricing'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_REPRICING);
        $view_params['features']['links']['remote_cart'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_REMOTE_CART);
        $view_params['features']['links']['shipping_template'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_SHIPPING_TEMPLATE);
        $view_params['features']['links']['messaging'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_MESSAGING);
        $view_params['features']['links']['cancel_orders'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_CANCEL_ORDERS);
        $view_params['features']['links']['expert_mode'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_EXPERT_MODE);
        $view_params['features']['links']['debug_express'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_DEBUG_EXPRESS);
        $view_params['features']['links']['business'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_BUSINESS);
        $view_params['features']['links']['orders_reports'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_BUSINESS);

        if ((bool)$this->config['features']['amazon_europe'] || (bool)AmazonConfiguration::get('EUROPE')/*retro-compatibility*/) {
            $this->config['features']['amazon_europe'] = true;
        } else {
            $this->config['features']['amazon_europe'] = false;
        }

        if ((bool)$this->config['features']['expert_mode']) {
            $this->config['features']['expert_mode'] = true;
        } else {
            $this->config['features']['expert_mode'] = false;
        }

        $this->config['features']['debug_mode'] = (bool)$this->config['debug']; //retro-compatibility

        $view_params['features']['config'] = $this->config['features'];

        $view_params['features']['config']['noway'] = in_array($this->config['features']['module'], array('amazonlite', 'ready')) ? true : false;

        $view_params['features']['validation'] = $this->_validate(true);

        return ($view_params);
    }

    /**
     * @param $view_params
     * @param int $id_lang
     * @param int $id_country
     * @return mixed
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function marketplaceTab(&$view_params, $id_lang, $id_country)
    {
        $selected_tab = $this->selectedTab();

        // Langue Active
        //
        $actives = $this->config['actives'];

        // Locales
        //
        $regions = $this->config['regions'];
        $currencies = $this->config['currencies'];
        $selected_currency = isset($currencies[$id_lang]) ? $currencies[$id_lang] : null;

        $synch_field = null;

        $active = isset($actives[$id_lang]) ? (int)$actives[$id_lang] : '';
        $region = isset($regions[$id_lang]) ? $regions[$id_lang] : null;

        $currency_tab = Currency::getCurrencies(false, false);
        $current_currency = null;

        if (is_array($currency_tab) && count($currency_tab) && isset($this->config['currencies'][$id_lang])) {
            foreach ($currency_tab as $currency) {
                if ($currency['iso_code'] == $this->config['currencies'][$id_lang] || $selected_currency == $currency['iso_code']) {
                    $current_currency = $currency;
                }
            }
        }

        // Carriers
        $incoming_carrier_amazon = isset($this->config['incoming_carrier_amazon'][$id_lang]) ? $this->config['incoming_carrier_amazon'][$id_lang] : array(null);
        $incoming_carrier_prestashop = isset($this->config['incoming_carrier_prestashop'][$id_lang]) ? $this->config['incoming_carrier_prestashop'][$id_lang] : array(null);
        $outgoing_carriers = isset($this->config['outgoing_carrier'][$id_lang]['prestashop']) && is_array($this->config['outgoing_carrier'][$id_lang]['prestashop']) ? $this->config['outgoing_carrier'][$id_lang] : array('prestashop' => array(null));

        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.preconfiguration.class.php');

        if (AmazonConfiguration::get('ACTIVE') == false) {
            if (($preconfiguration = AmazonPreconfiguration::data($id_lang))) {
                $active = true;
                $region = $preconfiguration['region'];
                $selected_currency = $preconfiguration['currency'];
                $incoming_carrier_amazon = $preconfiguration['amazon_incoming_carrier'];
                $incoming_carrier_prestashop = $preconfiguration['prestashop_incoming_carrier'];
                $outgoing_carriers = $preconfiguration['outgoing_carriers'];
                $synch_field = $preconfiguration['synchronization_field'];
            }
        }

        // First Initialization
        //
        if (!isset($view_params['settings']) || !is_array($view_params['settings'])) {
            $view_params['settings'] = array();
            $view_params['settings']['images_url'] = $this->images;
            $view_params['settings']['tutorial_1'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_SETTINGS);
            $view_params['settings']['tutorial_2'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_KEYPAIRS);

            $view_params['settings']['validate'] = array();

            $view_params['settings']['locales'] = array();
            $view_params['settings']['locales']['config'] = array();
            $view_params['settings']['locales']['platforms'] = $this->_platforms;
            $view_params['settings']['locales']['currencies'] = $currency_tab;
            $view_params['settings']['locales']['selected_tab'] = $selected_tab;

            $view_params['settings']['marketplace'] = array();
            $view_params['settings']['marketplace']['config'] = array();

            $view_params['settings']['general'] = array(
                'config'            => array(),
                'expert_mode'       => (bool)$this->config['features']['expert_mode']
            );

            $view_params['settings']['carriers'] = array();
            $view_params['settings']['carriers']['config'] = array();
            $view_params['settings']['carriers']['config']['incoming'] = array();
            $view_params['settings']['carriers']['config']['outgoing'] = array();
            $view_params['settings']['carriers']['config']['fba_multichannel'] = array();
            $view_params['settings']['carriers']['fba_multichannel'] = (bool)$this->config['fba_multichannel'];

            $view_params['settings']['overrides'] = array();
            $view_params['settings']['overrides']['standard'] = array();
            $view_params['settings']['overrides']['express'] = array();
            $view_params['settings']['overrides']['allow'] = false;
        }

        $language = new Language($id_lang);

        if (!Validate::isLoadedObject($language)) {
            return ($view_params);
        }

        // Amazon API Configuration
        //
        $merchantId = isset($this->config['amazon_merchant_ids'][$id_lang]) ? trim($this->config['amazon_merchant_ids'][$id_lang]) : '';
        $awsKeyId = isset($this->config['amazon_key_ids'][$id_lang]) ? trim($this->config['amazon_key_ids'][$id_lang]) : '';
        $awsSecretKey = isset($this->config['amazon_secret_ids'][$id_lang]) ? trim($this->config['amazon_secret_ids'][$id_lang]) : '';
        $mwsToken = isset($this->config['amazon_mws_token'][$id_lang]) ? trim($this->config['amazon_mws_token'][$id_lang]) : '';
        $marketplaceId = isset($this->config['amazon_marketplace_ids'][$id_lang]) ? trim($this->config['amazon_marketplace_ids'][$id_lang]) : '';

        $carriers = &self::$carriers;

        // Locales
        //
        $view_params['settings']['locales']['config'][$id_lang]['iso_code'] = $language->iso_code;
        $view_params['settings']['locales']['config'][$id_lang]['name'] = $language->name;
        $view_params['settings']['locales']['config'][$id_lang]['region'] = $region;
        $view_params['settings']['locales']['config'][$id_lang]['currency'] = $selected_currency;
        $view_params['settings']['locales']['config'][$id_lang]['currency_required'] = !Tools::strlen(trim($selected_currency));

        if (isset($this->_platforms[$region])) {
            $view_params['settings']['locales']['config'][$id_lang]['platform_selected'] = $region;
            $view_params['settings']['locales']['config'][$id_lang]['platform_selected_required'] = false;
        } else {
            $view_params['settings']['locales']['config'][$id_lang]['platform_selected'] = $region;
            $view_params['settings']['locales']['config'][$id_lang]['platform_selected_required'] = true;
        }

        $hasEurope = true;

        // Marketplace Settings
        //
        if ($this->config['features']['amazon_europe'] && empty($this->config['marketplace_master'])) {
            // unconsistency

            $hasEurope = false;
        }

        if ($hasEurope && $this->config['features']['amazon_europe'] && $this->config['marketplace_master'] == $region) {
            $masterPlatform = true;
        } else {
            $masterPlatform = false;
        }

        if (!$hasEurope) {
            $display = '';
            $disabled = '';
        } elseif ($this->config['features']['amazon_europe'] && !$masterPlatform && (AmazonTools::isEurope($region) || $region === 'uk')) {
            $display = 'display:none;';
            $disabled = 'disabled';
        } else {
            $display = '';
            $disabled = '';
        }

        $view_params['settings']['marketplace']['config'][$id_lang]['region'] = $region;
        $view_params['settings']['marketplace']['config'][$id_lang]['merchantId'] = $merchantId ? $merchantId : ' '; // space to avoid form autocomplete
        $view_params['settings']['marketplace']['config'][$id_lang]['merchantId_required'] = !Tools::strlen(trim($merchantId));
        // For Amazon.ca, marketplace id could be either the one from CA (old method) and the one from US (new method)
        $view_params['settings']['marketplace']['config'][$id_lang]['marketPlaceId'] = (in_array($region, array(
                    'ca',
                    'mx'
                )) && $marketplaceId) ? $marketplaceId : AmazonTools::lang2MarketplaceId($region);



        $view_params['settings']['marketplace']['config'][$id_lang]['awsKeyId'] = $awsKeyId ? $awsKeyId : ' '; // space to avoid form autocomplete
        $view_params['settings']['marketplace']['config'][$id_lang]['awsKeyId_required'] = !Tools::strlen(trim($awsKeyId));
        $view_params['settings']['marketplace']['config'][$id_lang]['awsSecretKey'] = $awsSecretKey;
        $view_params['settings']['marketplace']['config'][$id_lang]['awsSecretKey_required'] = !Tools::strlen(trim($awsSecretKey));
        $view_params['settings']['marketplace']['config'][$id_lang]['mwsToken'] = $mwsToken;

        // Determining behaviour
        if ((Tools::strlen($awsKeyId) && Tools::strlen($awsSecretKey)) || (bool)$this->config['features']['expert_mode']) {
            $view_params['settings']['marketplace']['config'][$id_lang]['displayAll'] = true;
        } else {
            $view_params['settings']['marketplace']['config'][$id_lang]['displayAll'] = false;
        }

        $view_params['settings']['marketplace']['config'][$id_lang]['active'] = $active;
        $view_params['settings']['marketplace']['config'][$id_lang]['display'] = $display;
        $view_params['settings']['marketplace']['config'][$id_lang]['disabled'] = $disabled;
        $view_params['settings']['marketplace']['config'][$id_lang]['domain'] = AmazonTools::idToDomain($id_lang);
        $view_params['settings']['marketplace']['config'][$id_lang]['flag'] = html_entity_decode('&lt;img src="'.$this->images.'geo_flags/'.$this->geoFlag($id_lang).'.gif" alt="'.$language->name.'" /&gt;');


        $view_params['settings']['marketplace']['config'][$id_lang]['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_API_KEYPAIRS);

        if (isset($this->config['out_of_stock'][$id_lang]) && is_array($this->config['out_of_stock']) && $this->config['out_of_stock'][$id_lang]) {
            $out_of_stock = $this->config['out_of_stock'][$id_lang];
        } else {
            $out_of_stock = '0';
        }

        if (isset($this->config['rounding']) && isset($this->config['rounding'][$id_lang]) && $this->config['rounding'][$id_lang]) {
            $rounding = $this->config['rounding'][$id_lang];
        } else {
            $rounding = self::ROUNDING_NONE;
        }

        if (isset($this->config['sort_order']) && isset($this->config['sort_order'][$id_lang]) && $this->config['sort_order'][$id_lang]) {
            $sort_order = $this->config['sort_order'][$id_lang];
        } else {
            $sort_order = self::SORT_ORDER_FIRSTNAME_LASTNAME;
        }

        if (isset($this->config['synch_field'][$id_lang]) && is_array($this->config['synch_field']) && $this->config['synch_field'][$id_lang]) {
            $synch_field = $this->config['synch_field'][$id_lang];
        } elseif (!$synch_field) {
            $synch_field = 'ean13';
        }

        $asin_has_priority = true;

        if (isset($this->config['asin_has_priority']) && is_array($this->config['asin_has_priority']) && isset($this->config['asin_has_priority'][$id_lang])) {
            $asin_has_priority = (bool)$this->config['asin_has_priority'][$id_lang];
        }

        $view_params['settings']['general']['config'][$id_lang] = array();
        $view_params['settings']['general']['config'][$id_lang]['out_of_stock'] = $out_of_stock;

        $view_params['settings']['general']['config'][$id_lang]['price_rule'] = array();
        $view_params['settings']['general']['config'][$id_lang]['price_rule']['currency_sign'] = isset($current_currency['sign']) ? $current_currency['sign'] : null;

        $view_params['settings']['general']['config'][$id_lang]['price_rule']['rule']['from'] = array(null);
        $view_params['settings']['general']['config'][$id_lang]['price_rule']['rule']['to'] = array(null);
        $view_params['settings']['general']['config'][$id_lang]['price_rule']['rule']['percent'] = array(null);
        $view_params['settings']['general']['config'][$id_lang]['price_rule']['rule']['value'] = array(null);
        $view_params['settings']['general']['config'][$id_lang]['price_rule']['type'] = 'percent';

        if (isset($this->config['price_rule'][$id_lang]) && isset($this->config['price_rule'][$id_lang]['type']) && $this->config['price_rule'][$id_lang]['type']) {
            $view_params['settings']['general']['config'][$id_lang]['price_rule']['type'] = $this->config['price_rule'][$id_lang]['type'];

            if (isset($this->config['price_rule'][$id_lang]['rule']['from']) && is_array($this->config['price_rule'][$id_lang]['rule']['from']) && isset($this->config['price_rule'][$id_lang]['rule']['to']) && is_array($this->config['price_rule'][$id_lang]['rule']['to'])) {
                $view_params['settings']['general']['config'][$id_lang]['price_rule']['rule'] = $this->config['price_rule'][$id_lang]['rule'];
                $price_rule_from  = $this->config['price_rule'][$id_lang]['rule']['from'];
                $price_rule_to    = $this->config['price_rule'][$id_lang]['rule']['to'];
                $price_rule_value = $this->config['price_rule'][$id_lang]['rule']['value'];
                if ((!is_array($price_rule_from) || !count($price_rule_from))
                    && (!is_array($price_rule_to) || !count($price_rule_to))
                    && (!is_array($price_rule_value) || !count($price_rule_value))) {
                    $view_params['settings']['general']['config'][$id_lang]['price_rule']['rule']['from'][0] = null;
                    $view_params['settings']['general']['config'][$id_lang]['price_rule']['rule']['to'][0] = null;
                    $view_params['settings']['general']['config'][$id_lang]['price_rule']['rule']['percent'][0] = null;
                    $view_params['settings']['general']['config'][$id_lang]['price_rule']['rule']['value'][0] = null;
                }
            }
        }

        $view_params['settings']['general']['config'][$id_lang]['rounding_1'] = ($rounding == self::ROUNDING_ONE_DIGIT ? 'checked' : '');
        $view_params['settings']['general']['config'][$id_lang]['rounding_2'] = ($rounding == self::ROUNDING_TWO_DIGITS ? 'checked' : '');
        $view_params['settings']['general']['config'][$id_lang]['rounding_3'] = ($rounding == self::ROUNDING_SMART ? 'checked' : '');
        $view_params['settings']['general']['config'][$id_lang]['rounding_4'] = ($rounding == self::ROUNDING_NONE ? 'checked' : '');


        if (isset($this->config['ptc']) && isset($this->config['ptc'][$id_lang]) && $this->config['ptc'][$id_lang]) {
            $ptc_selected = $this->config['ptc'][$id_lang];
        } else {
            $ptc_selected = null;
        }

        $view_params['settings']['general']['config'][$id_lang]['ptc'] = AmazonTaxes::getPtcList($region);
        $view_params['settings']['general']['config'][$id_lang]['ptc_selected'] = $ptc_selected;

        // Default tax rule for marketplace, it's use for FBA oder which delivery cross country
        $view_params['settings']['general']['config'][$id_lang]['default_tax_rule'] = AmazonTaxes::getTaxRuleGroupsByCountry($id_country, true);
        $default_tax_rule_selected = null;
        if (isset($this->config['default_tax_rule'], $this->config['default_tax_rule'][$id_lang]) && $this->config['default_tax_rule'][$id_lang]) {
            $default_tax_rule_selected = $this->config['default_tax_rule'][$id_lang];
        }
        $view_params['settings']['general']['config'][$id_lang]['default_tax_rule_selected'] = $default_tax_rule_selected;

        $view_params['settings']['general']['config'][$id_lang]['sort_order_1'] = ($sort_order == self::SORT_ORDER_FIRSTNAME_LASTNAME ? 'checked' : '');
        $view_params['settings']['general']['config'][$id_lang]['sort_order_2'] = ($sort_order == self::SORT_ORDER_LASTNAME_FIRSTNAME ? 'checked' : '');

        $view_params['settings']['general']['config'][$id_lang]['synch_field_ean13'] = ($synch_field === 'ean13' ? 'selected' : '');
        $view_params['settings']['general']['config'][$id_lang]['synch_field_upc'] = ($synch_field === 'upc' ? 'selected' : '');
        $view_params['settings']['general']['config'][$id_lang]['synch_field_both'] = ($synch_field === 'both' ? 'selected' : '');
        $view_params['settings']['general']['config'][$id_lang]['synch_field_reference'] = ($synch_field === 'reference' ? 'selected' : '');

        $view_params['settings']['general']['config'][$id_lang]['asin_has_priority'] = $asin_has_priority;

        $amazon_carrier_list = array_merge($this->shipping_overrides_std, $this->shipping_overrides_exp);

        $view_params['settings']['carriers']['carrier_modules_tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_CARRIERS_MODULES);
        $view_params['settings']['carriers']['config']['incoming'][$id_lang] = array();

        $has_carrier_modules = false;
        $count_carriers = is_array($incoming_carrier_amazon) ? count($incoming_carrier_amazon) : 0;
        for ($index = 0; $index < $count_carriers; $index++) {
            if ($index && !$incoming_carrier_amazon[$index] && $incoming_carrier_amazon[$index] !== null) {
                continue;
            }

            $view_params['settings']['carriers']['config']['incoming'][$id_lang][$index] = array();
            $view_params['settings']['carriers']['config']['incoming'][$id_lang][$index]['display_add'] = ($index > 0 ? 'style="display:none"' : '');
            $view_params['settings']['carriers']['config']['incoming'][$id_lang][$index]['display_del'] = ($index === 0 ? 'style="display:none"' : '');

            foreach ($amazon_carrier_list as $carrier) {
                $key = md5($carrier);

                if (empty($key)) {
                    continue;
                }

                $selected = isset($incoming_carrier_amazon[$index]) && $key === $incoming_carrier_amazon[$index] ? true : false;

                $view_params['settings']['carriers']['config']['incoming'][$id_lang][$index]['amazon_carrier'][$key]['selected'] = $selected;
                $view_params['settings']['carriers']['config']['incoming'][$id_lang][$index]['amazon_carrier'][$key]['name'] = $carrier;
            }

            foreach ($carriers as $carrier) {
                $id_carrier = (int)$carrier['id_carrier'];
                $selected = isset($incoming_carrier_prestashop[$index]) && (int)$incoming_carrier_prestashop[$index] === $id_carrier ? true : false;

                $view_params['settings']['carriers']['config']['incoming'][$id_lang][$index]['prestashop_carrier'][$id_carrier]['selected'] = $selected;
                $view_params['settings']['carriers']['config']['incoming'][$id_lang][$index]['prestashop_carrier'][$id_carrier]['name'] = $carrier['name'];
                $view_params['settings']['carriers']['config']['incoming'][$id_lang][$index]['prestashop_carrier'][$id_carrier]['is_module'] = $carrier['is_module'];

                if ($carrier['is_module']) {
                    $has_carrier_modules = true;
                }
            }
        }
        $view_params['settings']['carriers']['config']['has_carrier_modules'] = $has_carrier_modules;
        $view_params['settings']['carriers']['config']['carrier_modules_allowed'] = true;


        // Carriers Associations for outgoing orders
        //
        $view_params['settings']['carriers']['config']['outgoing'][$id_lang] = array();

        $amazon_carriers = array_merge(AmazonCarrier::$carrier_codes, $this->extra_carrier_codes);

        asort($amazon_carriers);
        $count_carriers = is_array($outgoing_carriers['prestashop']) ? count($outgoing_carriers['prestashop']) : 0;
        for ($index = 0; $index < $count_carriers; $index++) {
            if ($index && !$outgoing_carriers['prestashop'][$index] && $outgoing_carriers['prestashop'][$index] !== null) {
                continue;
            }

            $view_params['settings']['carriers']['config']['outgoing'][$id_lang][$index] = array();
            $view_params['settings']['carriers']['config']['outgoing'][$id_lang][$index]['display_add'] = ($index > 0 ? 'style="display:none"' : '');
            $view_params['settings']['carriers']['config']['outgoing'][$id_lang][$index]['display_del'] = ($index === 0 ? 'style="display:none"' : '');

            foreach ($carriers as $carrier) {
                $id_carrier = (int)$carrier['id_carrier'];
                $selected = isset($outgoing_carriers['prestashop'][$index]) && (int)$outgoing_carriers['prestashop'][$index] == $id_carrier ? true : false;

                $view_params['settings']['carriers']['config']['outgoing'][$id_lang][$index]['prestashop_carrier'][$id_carrier]['selected'] = $selected;
                $view_params['settings']['carriers']['config']['outgoing'][$id_lang][$index]['prestashop_carrier'][$id_carrier]['name'] = $carrier['name'];
                $view_params['settings']['carriers']['config']['outgoing'][$id_lang][$index]['prestashop_carrier'][$id_carrier]['is_module'] = $carrier['is_module'];


                if ($carrier['is_module']) {
                    $has_carrier_modules = true;
                }
            }

            foreach ($amazon_carriers as $carrier) {
                if (isset($outgoing_carriers['amazon'][$index]) && $carrier == $outgoing_carriers['amazon'][$index]) {
                    $selected = true;
                } else {
                    $selected = false;
                }

                $view_params['settings']['carriers']['config']['outgoing'][$id_lang][$index]['amazon_carrier'][$carrier]['selected'] = $selected;
                $view_params['settings']['carriers']['config']['outgoing'][$id_lang][$index]['amazon_carrier'][$carrier]['name'] = $carrier;
            }
        }

        // FBA Multi Channel Carriers Associations for outgoing orders
        //
        $view_params['settings']['carriers']['config']['fba_multichannel'][$id_lang] = array();

        $carrier_multichannel = isset($this->config['multichannel_carrier'][$id_lang]['prestashop']) && is_array($this->config['multichannel_carrier'][$id_lang]['prestashop']) ? $this->config['multichannel_carrier'][$id_lang] : array('prestashop' => array(null));

        if ($this->config['fba_multichannel']) {
            $count_carriers = is_array($carrier_multichannel['prestashop']) ? count($carrier_multichannel['prestashop']) : 0;
            for ($index = 0; $index < $count_carriers; $index++) {
                if ($index && !$carrier_multichannel['prestashop'][$index] && $carrier_multichannel['prestashop'][$index] !== null) {
                    continue;
                }

                $view_params['settings']['carriers']['config']['fba_multichannel'][$id_lang][$index] = array();
                $view_params['settings']['carriers']['config']['fba_multichannel'][$id_lang][$index]['display_add'] = ($index > 0 ? 'style="display:none"' : '');
                $view_params['settings']['carriers']['config']['fba_multichannel'][$id_lang][$index]['display_del'] = ($index === 0 ? 'style="display:none"' : '');

                foreach ($carriers as $carrier) {
                    $id_carrier = (int)$carrier['id_carrier'];

                    $selected = isset($carrier_multichannel['prestashop'][$index]) && $carrier_multichannel['prestashop'][$index] == $id_carrier ? true : false;

                    $view_params['settings']['carriers']['config']['fba_multichannel'][$id_lang][$index]['prestashop_carrier'][$id_carrier]['selected'] = $selected;
                    $view_params['settings']['carriers']['config']['fba_multichannel'][$id_lang][$index]['prestashop_carrier'][$id_carrier]['name'] = $carrier['name'];
                    $view_params['settings']['carriers']['config']['fba_multichannel'][$id_lang][$index]['prestashop_carrier'][$id_carrier]['is_module'] = $carrier['is_module'];
                }

                foreach ($this->carrier_fba as $carrier) {
                    $selected = isset($carrier_multichannel['amazon']) && isset($carrier_multichannel['amazon'][$index]) && $carrier == $carrier_multichannel['amazon'][$index] ? true : false;

                    $view_params['settings']['carriers']['config']['fba_multichannel'][$id_lang][$index]['amazon_carrier'][$carrier]['selected'] = $selected;
                    $view_params['settings']['carriers']['config']['fba_multichannel'][$id_lang][$index]['amazon_carrier'][$carrier]['name'] = $carrier;
                }
            }
        } // End of FBA Multi Channel Carrier Mapping


        // Shipping Overrides (deprecated)
        $view_params['settings']['overrides']['allow'] = is_array($this->config['shipping']) && isset($this->config['shipping']['allow_overrides']) && $this->config['shipping']['allow_overrides'];

        if ($view_params['settings']['overrides']['allow']) {
            $selected_override_std = isset($this->config['shipping_override_std'][$id_lang]) ? $this->config['shipping_override_std'][$id_lang] : array(null);
            $selected_override_exp = isset($this->config['shipping_override_exp'][$id_lang]) ? $this->config['shipping_override_exp'][$id_lang] : array(null);

            $view_params['settings']['overrides']['standard'][$id_lang] = array();
            $view_params['settings']['overrides']['express'][$id_lang] = array();

            foreach ($this->shipping_overrides_std as $override) {
                $selected = ($override && $override == $selected_override_std) ? true : false;

                $view_params['settings']['overrides']['standard'][$id_lang][$override]['name'] = $override;
                $view_params['settings']['overrides']['standard'][$id_lang][$override]['selected'] = $selected;
            }

            foreach ($this->shipping_overrides_exp as $override) {
                $selected = ($override && $override == $selected_override_exp) ? true : false;

                $view_params['settings']['overrides']['express'][$id_lang][$override]['name'] = $override;
                $view_params['settings']['overrides']['express'][$id_lang][$override]['selected'] = $selected;
            }
        }

        // Default Shipping Method for Platform
        //

        $view_params['settings']['shipping_methods'][$id_lang] = array();

        if (isset($this->config['shipping_methods']) && isset($this->config['shipping_methods'][$id_lang]) && $this->config['shipping_methods'][$id_lang]) {
            $default_shipping_method = $this->config['shipping_methods'][$id_lang];
        } else {
            $default_shipping_method = null;
        }

        foreach ($this->shipping_overrides_std as $shipping_method) {
            $selected = ($shipping_method && $shipping_method === $default_shipping_method) ? true : false;

            $view_params['settings']['shipping_methods'][$id_lang][$shipping_method]['name'] = $shipping_method;
            $view_params['settings']['shipping_methods'][$id_lang][$shipping_method]['selected'] = $selected;
        }

        // Validate Button
        //
        $view_params['settings']['validate'][$id_lang] = $this->_validate(true);

        return ($view_params);
    }

    private function _validate($returnHTML = false)
    {
        static $validate = null;

        if ($validate === null) {
            $validate = $this->context->smarty->fetch($this->path.self::$templates[self::TEMPLATE_VALIDATE]);
        }

        if ($returnHTML) {
            return ($validate);
        }

        $this->_html .= $validate;

        return (false);
    }

    private function _parameters(&$view_params)
    {
        $view_params['parameters'] = array();
        $view_params['parameters']['settings'] = array();
        $view_params['parameters']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_PARAMETERS);

        $view_params['parameters']['images_url'] = $this->images;
        $view_params['parameters']['selected_tab'] = ($this->selectedTab() === 'parameters' ? 'selected' : '');
        $view_params['parameters']['expert_mode'] = (bool)$this->config['features']['expert_mode'];
        $view_params['parameters']['validation'] = $this->_validate(true);

        $view_params['parameters']['features'] = array();

        if ($this->config['taxes'] === false && $this->config['marketplace_master'] == null) {
            require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.preconfiguration.class.php');

            if (($preconfiguration = AmazonPreconfiguration::data($this->id_lang))) {
                $this->config['taxes'] = $preconfiguration['taxes'];
                $this->config['marketplace_master'] = $preconfiguration['marketplace_master'];
            }
        }

        //
        // Options switches
        //

        $view_params['parameters']['settings']['discount'] = (bool)$this->config['specials'];
        $view_params['parameters']['settings']['specials_apply_rules'] = (bool)$this->config['specials_apply_rules'];
        $view_params['parameters']['settings']['preorder'] = (bool)$this->config['preorder'];
        $view_params['parameters']['settings']['taxes'] = (int)$this->config['taxes'];

        $view_params['parameters']['settings']['account_type_global_value'] = self::ACCOUNT_TYPE_GLOBAL;
        $view_params['parameters']['settings']['account_type_individual_value'] = self::ACCOUNT_TYPE_INDIVIDUAL;

        $view_params['parameters']['settings']['account_type_global_selected'] = $this->config['account_type'] == self::ACCOUNT_TYPE_GLOBAL ? true : false;
        $view_params['parameters']['settings']['account_type_individual_selected'] = $this->config['account_type'] == self::ACCOUNT_TYPE_INDIVIDUAL ? true : false;

        $view_params['parameters']['settings']['title_format_value_1'] = self::FORMAT_TITLE;
        $view_params['parameters']['settings']['title_format_value_2'] = self::FORMAT_MANUFACTURER_TITLE;
        $view_params['parameters']['settings']['title_format_value_3'] = self::FORMAT_MANUFACTURER_TITLE_REFERENCE;

        $view_params['parameters']['settings']['title_format_selected_1'] = ($this->config['title_format'] == self::FORMAT_TITLE ? true : false);
        $view_params['parameters']['settings']['title_format_selected_2'] = ($this->config['title_format'] == self::FORMAT_MANUFACTURER_TITLE ? true : false);
        $view_params['parameters']['settings']['title_format_selected_3'] = ($this->config['title_format'] == self::FORMAT_MANUFACTURER_TITLE_REFERENCE ? true : false);

        $view_params['parameters']['settings']['delete_products'] = (bool)$this->config['delete_products'];
        $view_params['parameters']['settings']['html_descriptions'] = (bool)$this->config['html_descriptions'];

        $view_params['parameters']['settings']['description_field_value_1'] = self::FIELD_DESCRIPTION_LONG;
        $view_params['parameters']['settings']['description_field_value_2'] = self::FIELD_DESCRIPTION_SHORT;
        $view_params['parameters']['settings']['description_field_value_3'] = self::FIELD_DESCRIPTION_BOTH;
        $view_params['parameters']['settings']['description_field_value_4'] = self::FIELD_DESCRIPTION_NONE;

        $view_params['parameters']['settings']['description_field_selected_1'] = ($this->config['description_field'] == self::FIELD_DESCRIPTION_LONG ? true : false);
        $view_params['parameters']['settings']['description_field_selected_2'] = ($this->config['description_field'] == self::FIELD_DESCRIPTION_SHORT ? true : false);
        $view_params['parameters']['settings']['description_field_selected_3'] = ($this->config['description_field'] == self::FIELD_DESCRIPTION_BOTH ? true : false);
        $view_params['parameters']['settings']['description_field_selected_4'] = ($this->config['description_field'] == self::FIELD_DESCRIPTION_NONE ? true : false);

        // Aug-23-2018: Remove Carriers/Modules option
        $view_params['parameters']['settings']['safe_encoding'] = (bool)$this->config['safe_encoding'];
        $view_params['parameters']['settings']['prices_only'] = (bool)$this->config['prices_only'];
        $view_params['parameters']['settings']['stock_only'] = (bool)$this->config['stocks_only'];
        $view_params['parameters']['settings']['payment_region'] = (bool)$this->config['payment_region'];

        $view_params['parameters']['settings']['auto_create'] = (bool)$this->config['auto_create'];

        $view_params['parameters']['settings']['email'] = (bool)$this->config['email'];
        $view_params['parameters']['settings']['expert_mode'] = (bool)$this->config['features']['expert_mode'];
        $view_params['parameters']['settings']['debug_mode'] = (bool)$this->config['debug'];
        $view_params['parameters']['settings']['inactive_languages'] = (bool)$this->config['inactive_languages'];
        $view_params['parameters']['settings']['disable_ssl_check'] = (bool)$this->config['disable_ssl_check'];

        $selected = $this->config['image_type'];


        foreach (ImageType::getImagesTypes() as $imageType) {
            if (!(bool)$imageType['products']) {
                continue;
            }

            $view_params['parameters']['settings']['image_type'][$imageType['name']] = ($selected === $imageType['name'] ? true : false);
        }

        //
        // Orders States
        //

        $view_params['parameters']['settings']['incoming_order_state'] = array();
        $view_params['parameters']['settings']['incoming_order_state']['standard']['prefix'] = self::ORDER_STATE_STANDARD;
        $view_params['parameters']['settings']['incoming_order_state']['standard']['title'] = $this->l('Incoming Orders');
        $view_params['parameters']['settings']['incoming_order_state']['standard']['options'] = array();

        $orderStates = OrderState::getOrderStates($this->id_lang);

        // Incoming Orders States
        //
        foreach ($orderStates as $orderState) {
            $id_order_state = (int)$orderState['id_order_state'];

            if (is_array($this->config['order_state']) && $id_order_state == $this->config['order_state'][self::ORDER_STATE_STANDARD]) {
                $selected = true;
            } else {
                $selected = false;
            }

            $view_params['parameters']['settings']['incoming_order_state']['standard']['options'][$id_order_state]['name'] = $orderState['name'];
            $view_params['parameters']['settings']['incoming_order_state']['standard']['options'][$id_order_state]['selected'] = $selected;
        }

        // Prime
        //
        $view_params['parameters']['settings']['incoming_order_state']['prime']['enabled'] = true;
        $view_params['parameters']['settings']['incoming_order_state']['prime']['active'] = true;
        $view_params['parameters']['settings']['incoming_order_state']['prime']['prefix'] = self::ORDER_STATE_PRIMEORDER;
        $view_params['parameters']['settings']['incoming_order_state']['prime']['title'] = $this->l('Prime Orders');
        $view_params['parameters']['settings']['incoming_order_state']['prime']['options'] = array();

        $current_order_state = isset($this->config['order_state'][self::ORDER_STATE_PRIMEORDER]) ? $this->config['order_state'][self::ORDER_STATE_PRIMEORDER] : null;

        foreach ($orderStates as $orderState) {
            $id_order_state = (int)$orderState['id_order_state'];

            if (is_array($this->config['order_state']) && $id_order_state == $current_order_state) {
                $selected = true;
            } else {
                $selected = false;
            }

            $view_params['parameters']['settings']['incoming_order_state']['prime']['options'][$id_order_state]['name'] = $orderState['name'];
            $view_params['parameters']['settings']['incoming_order_state']['prime']['options'][$id_order_state]['selected'] = $selected;
        }
        
        // Preorder
        //
        $view_params['parameters']['settings']['incoming_order_state']['preorder']['enabled'] = version_compare(_PS_VERSION_, '1.5', '>=');
        $view_params['parameters']['settings']['incoming_order_state']['preorder']['active'] = $this->config['preorder'];
        $view_params['parameters']['settings']['incoming_order_state']['preorder']['prefix'] = self::ORDER_STATE_PREORDER;
        $view_params['parameters']['settings']['incoming_order_state']['preorder']['title'] = $this->l('Pre-Orders');
        $view_params['parameters']['settings']['incoming_order_state']['preorder']['options'] = array();

        $current_order_state = isset($this->config['order_state'][self::ORDER_STATE_PREORDER]) ? $this->config['order_state'][self::ORDER_STATE_PREORDER] : null;

        foreach ($orderStates as $orderState) {
            $id_order_state = (int)$orderState['id_order_state'];

            if (is_array($this->config['order_state']) && $id_order_state == $current_order_state) {
                $selected = true;
            } else {
                $selected = false;
            }

            $view_params['parameters']['settings']['incoming_order_state']['preorder']['options'][$id_order_state]['name'] = $orderState['name'];
            $view_params['parameters']['settings']['incoming_order_state']['preorder']['options'][$id_order_state]['selected'] = $selected;
        }

        // Sent Orders States
        //
        $view_params['parameters']['settings']['sent_order_state'] = array();
        $view_params['parameters']['settings']['sent_order_state']['options'] = array();
        $view_params['parameters']['settings']['sent_order_state']['title'] = $this->l('Orders Sent');

        foreach ($orderStates as $orderState) {
            $id_order_state = (int)$orderState['id_order_state'];

            if ($id_order_state == $this->config['send_state']) {
                $selected = true;
            } else {
                $selected = false;
            }

            $view_params['parameters']['settings']['sent_order_state']['options'][$id_order_state]['name'] = $orderState['name'];
            $view_params['parameters']['settings']['sent_order_state']['options'][$id_order_state]['selected'] = $selected;
        }

        // Canceled Orders States
        //
        $view_params['parameters']['settings']['canceled_order_state'] = array();
        $view_params['parameters']['settings']['canceled_order_state']['options'] = array();
        $view_params['parameters']['settings']['canceled_order_state']['title'] = $this->l('Canceled Orders ');

        foreach ($orderStates as $orderState) {
            $id_order_state = (int)$orderState['id_order_state'];

            if ($id_order_state == $this->config['canceled_state']) {
                $selected = true;
            } else {
                $selected = false;
            }

            $view_params['parameters']['settings']['canceled_order_state']['options'][$id_order_state]['name'] = $orderState['name'];
            $view_params['parameters']['settings']['canceled_order_state']['options'][$id_order_state]['selected'] = $selected;
        }

        $view_params['parameters']['settings']['employee'] = array();

        // Employee::getEmployees is displayed as deprecated in PS 1.4 ... but not in PS 1.5
        foreach (@Employee::getEmployees() as $employee) {
            $id_employee = (int)$employee['id_employee'];

            if ($id_employee == $this->config['employee']) {
                $selected = true;
            } else {
                $selected = false;
            }

            $view_params['parameters']['settings']['employee'][$id_employee]['name'] = (isset($employee['name']) ? $employee['name'] : sprintf('%s %s', $employee['firstname'], $employee['lastname']));
            $view_params['parameters']['settings']['employee'][$id_employee]['selected'] = $selected;
        }

        //
        // Customer groups
        //
        $view_params['parameters']['settings']['customer_groups'] = array();
        $view_params['parameters']['settings']['business'] = $this->amazon_features['business'];

        foreach (Group::getGroups($this->context->language->id, true) as $customer_group) {
            $id_group = (int)$customer_group['id_group'];

            if ($id_group == $this->config['id_group']) {
                $selected = true;
            } else {
                $selected = false;
            }

            $view_params['parameters']['settings']['customer_groups'][$id_group]['name'] = $customer_group['name'];
            $view_params['parameters']['settings']['customer_groups'][$id_group]['selected'] = $selected;

            if ($this->amazon_features['business']) {
                if ($id_group == $this->config['id_business_group']) {
                    $business_selected = true;
                } else {
                    $business_selected = false;
                }

                $view_params['parameters']['settings']['customer_groups'][$id_group]['business_selected'] = $business_selected;
            }
        }



        //
        // Advanced Stock
        //

        if (version_compare(_PS_VERSION_, '1.5', '>=') && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $view_params['parameters']['settings']['warehouse'] = array();

            foreach (Warehouse::getWarehouses(true) as $warehouse) {
                $id_warehouse = (int)$warehouse['id_warehouse'];

                if ($id_warehouse == $this->config['warehouse']) {
                    $selected = true;
                } else {
                    $selected = false;
                }

                $view_params['parameters']['settings']['warehouse'][$id_warehouse]['name'] = $warehouse['name'];
                $view_params['parameters']['settings']['warehouse'][$id_warehouse]['selected'] = $selected;
            }
        } else {
            $view_params['parameters']['settings']['warehouse'] = null;
        }

        //
        // Products State/Condition
        //
        // Check for the condition field (Prestashop < 1.4 doesn't have this field)
        if (($conditionFields = AmazonTools::getConditionField())) {
            $view_params['parameters']['settings']['product_condition'] = true;

            // For i18n
            //
            $default_conditions = array($this->l('new'), $this->l('used'), $this->l('refurbished'));

            // Fetch columns names
            //
            preg_match_all("/'([\w ]*)'/", $conditionFields['Type'], $ps_conditions);

            $view_params['parameters']['settings']['product_conditions'] = array();

            $index = 1;
            foreach (self::$conditions as $condition) {
                $view_params['parameters']['settings']['product_conditions'][$condition] = array();
                $view_params['parameters']['settings']['product_conditions'][$condition]['index'] = $index++;
                $view_params['parameters']['settings']['product_conditions'][$condition]['selector'] = array();

                foreach ($ps_conditions[1] as $ps_condition) {
                    if (isset($this->config['condition_map'][$condition]) && !empty($this->config['condition_map'][$condition]) && $this->config['condition_map'][$condition] === $ps_condition) {
                        $selected = true;
                    } elseif ($condition === 'New' && $ps_condition === 'new' && $this->config['condition_map'][$condition] === null) {
                        // default config

                        $selected = true;
                    } else {
                        $selected = false;
                    }

                    $view_params['parameters']['settings']['product_conditions'][$condition]['selector'][$ps_condition] = array();
                    $view_params['parameters']['settings']['product_conditions'][$condition]['selector'][$ps_condition]['selected'] = $selected;
                    $view_params['parameters']['settings']['product_conditions'][$condition]['selector'][$ps_condition]['name'] = AmazonTools::ucfirst($this->l($ps_condition));
                }
            }
        } else {
            $view_params['parameters']['settings']['product_condition'] = false;
        }

        //
        // Amazon Europe
        //

        $view_params['parameters']['settings']['europe'] = array();
        $view_params['parameters']['settings']['europe']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_AMAZON_EUROPE);

        foreach ($this->_platforms as $iso_code => $platform) {
            $view_params['parameters']['settings']['europe']['selector'][$iso_code]['selected'] = ($iso_code === $this->config['marketplace_master'] ? true : false);
            $view_params['parameters']['settings']['europe']['selector'][$iso_code]['iso_code'] = $iso_code;
            $view_params['parameters']['settings']['europe']['selector'][$iso_code]['name'] = $platform;
        }

        $old_amazon_europe = (bool)AmazonConfiguration::get('EUROPE'); // retro compat

        if ($this->config['features']['amazon_europe'] || $old_amazon_europe) {
            $view_params['parameters']['settings']['europe']['active'] = true;
            $view_params['parameters']['settings']['europe']['disabled'] = false;
            $view_params['parameters']['settings']['europe']['class'] = 'master-enabled';
        } else {
            $view_params['parameters']['settings']['europe']['active'] = false;
            $view_params['parameters']['settings']['europe']['disabled'] = true;
            $view_params['parameters']['settings']['europe']['class'] = 'master-disabled';
        }

        return ($view_params);
    }

    private function _categories(&$view_params)
    {
        $view_params['categories'] = array();
        $view_params['categories']['settings'] = array();
        $view_params['categories']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_CATEGORIES);

        $view_params['categories']['images_url'] = $this->images;
        $view_params['categories']['selected_tab'] = ($this->selectedTab() === 'categories' ? 'selected' : '');
        $view_params['categories']['expert_mode'] = (bool)$this->config['features']['expert_mode'];
        $view_params['categories']['validation'] = $this->_validate(true);

        $index = array();

        $html = '';

        $categories = Category::getCategories((int)$this->id_lang, false);

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_shop = (int)$this->context->shop->id;
            $shop = new Shop($id_shop);

            $first = null;
            $root = Category::getRootCategory(null, $shop)->id_category;

            foreach ($categories as $categories1) {
                foreach ($categories1 as $category) {
                    if ($category['infos']['id_category'] == $root) {
                        $first = $category;
                        break;
                    }
                }
                if ($first) {
                    break;
                }
            }

            $default_category = $shop->id_category;
        } else {
            foreach ($categories as $first1 => $categories_array) {
                break;
            }
            foreach ($categories_array as $first2 => $categories_array2) {
                break;
            }
            $first = $categories[$first1][$first2];
            $default_category = 1;
        }

        //$html_categories = self::recurseCategoryForInclude($index, $categories, $first, $default_category, null, $this->config['categories'], $this->config['profiles_to_categories'], true);

        $view_params['categories']['expert_mode'] = (bool)$this->config['features']['expert_mode'];
        $view_params['categories']['brute_force'] = $this->config['brute_force'];
        //$view_params['categories']['html_categories'] = $html_categories;

        $html_categories = self::recurseCategoryForInclude($index, $categories, $first, $default_category, null, $this->config['categories'], $this->config['profiles_to_categories'], false);

        $view_params['categories']['list'] = $html_categories;
        $view_params['categories']['profiles'] = &$this->profiles;
    }


    /**
     * @param $indexedCategories
     * @param $categories
     * @param $current
     * @param int $id_category
     * @param null $id_category_default
     * @param array $default_categories
     * @param array $default_profiles
     * @param bool|false $next
     *
     * @return string
     */
    public function recurseCategoryForInclude($indexedCategories, $categories, $current, $id_category = 1, $id_category_default = null, $default_categories = array(), $default_profiles = array(), $next = false)
    {
        static $done;
        static $irow;
        static $categories_table;

        $categories_table = isset($categories_table) ? $categories_table : array();

        if (is_array($default_categories) && in_array($id_category, $default_categories)) {
            $checked = ' checked="checked"';
        } elseif (!is_array($default_categories) || !count($default_categories)) {
            $checked = ' checked="checked"';
        } else {
            $checked = '';
        }

        if (!isset($done[$current['infos']['id_parent']])) {
            $done[$current['infos']['id_parent']] = 0;
        }
        $done[$current['infos']['id_parent']] += 1;

        $todo = is_array($categories[$current['infos']['id_parent']]) ? count($categories[$current['infos']['id_parent']]) : 0;
        $doneC = $done[$current['infos']['id_parent']];

        $level = $current['infos']['level_depth'] + 1;
        $img = ($next === false) ? 'lv1.gif' : 'lv'.$level.'_'.($todo == $doneC ? 'f' : 'b').'.gif';
        $selected_profile = null;
        $saved_profiles = &$this->profiles['name'];

        if (is_array($saved_profiles) && count($saved_profiles)) {
            if (is_array($saved_profiles) && count($saved_profiles) === 1 && !empty($checked)) {
                $default_profile = true;
            } else {
                $default_profile = false;
            }

            foreach ($saved_profiles as $profile) {
                if (!isset($profile) || empty($profile)) {
                    continue;
                }

                if (isset($default_profiles[$id_category]) && $default_profiles[$id_category] == $profile || $default_profile) {
                    $selected_profile = $profile;
                }
            }
        }

        $categories_table[$id_category] = array(
        'level' => $level,
        'img_level' => $this->images.$img,
        'alt_row' => $irow++ % 2,
        'id_category_default' => $id_category_default == $id_category,
        'checked' => $checked,
        'name' => Tools::stripslashes($current['infos']['name']),
        'profile' => $selected_profile,
        'disabled' => !$next
        );

        if (isset($categories[$id_category])) {
            if ($categories[$id_category]) {
                foreach (array_keys($categories[$id_category]) as $key) {
                    if ($key != 'infos') {
                        self::recurseCategoryForInclude($indexedCategories, $categories, $categories[$id_category][$key], $key, $id_category_default, $default_categories, $default_profiles, true);
                    }
                }
            }
        }

        return ($categories_table);
    }

    private function _profiles(&$view_params)
    {
        $view_params['profiles'] = array();
        $view_params['profiles']['config'] = array();

        $view_params['profiles']['module_path'] = $this->path;
        $view_params['profiles']['images_url'] = $this->images;
        $view_params['profiles']['selected_tab'] = ($this->selectedTab() === 'profiles' ? 'selected' : '');
        $view_params['profiles']['expert_mode'] = (bool)$this->config['features']['expert_mode'];
        $view_params['profiles']['validation'] = $this->_validate(true);
        $view_params['profiles']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_PROFILES);


        $view_params['profiles']['xsd_path'] = str_replace('\\', '/', realpath(dirname(__FILE__))).'/xsd/';
        $view_params['profiles']['xsd_operations_url'] = $this->url.'functions/xsd_operations.php';
        $view_params['profiles']['xsd_ajax_error'] = $this->l('An unexpected server side error occured').$this->l('In most cases, this is a permission problem. Please apply write permission (777) to amazon/validate/xsd directory.');
        $view_params['profiles']['error_profile_name'] = $this->l('First, you must enter a profile name');

        $html = null;

        $exemptions = array();
        $exemptions['none'] = self::EXEMPTION_NONE;
        $exemptions['compatibility'] = self::EXEMPTION_COMPATIBILITY;
        $exemptions['model_number'] = self::EXEMPTION_MODEL_NUMBER;
        $exemptions['model_name'] = self::EXEMPTION_MODEL_NAME;
        $exemptions['mfr_part_number'] = self::EXEMPTION_MFR_PART_NUMBER;
        $exemptions['catalog_number'] = self::EXEMPTION_CATALOG_NUMBER;
        $exemptions['style_number'] = self::EXEMPTION_STYLE_NUMBER;
        $exemptions['attr_ean'] = self::EXEMPTION_ATTR_EAN;
        $exemptions['generic'] = self::EXEMPTION_GENERIC;

        $view_params['profiles']['exemptions'] = $exemptions;

        // Only Used to Fetch the XSD Files ;
        //
        AmazonXSD::getCategories();

        $view_params['profiles']['categories_english'] = (Tools::strtolower(Language::getIsoById($this->id_lang)) === 'fr') ? true : false;

        $view_params['profiles']['marketplaces'] = array();
        $view_params['profiles']['marketplaces']['countries'] = $marketplace_countries = AmazonSpecificField::countrySelector();
        $view_params['profiles']['marketplaces']['show'] = is_array($marketplace_countries) && count($marketplace_countries) > 1;

        $current_currency = Currency::getDefaultCurrency();

        $profiles = is_array($this->profiles) && count($this->profiles) ? $this->profiles : array('name' => null);

        $profiles = AmazonSpecificField::migrateProfilesFromV3($profiles);

        $view_params['profiles']['bullet_point_strategy_a'] = self::BULLET_POINT_STRATEGY_ATTRIBUTES;
        $view_params['profiles']['bullet_point_strategy_af'] = self::BULLET_POINT_STRATEGY_ATTRIBUTES_FEATURES;
        $view_params['profiles']['bullet_point_strategy_f'] = self::BULLET_POINT_STRATEGY_FEATURES;
        $view_params['profiles']['bullet_point_strategy_d'] = self::BULLET_POINT_STRATEGY_DESC;
        $view_params['profiles']['bullet_point_strategy_daf'] = self::BULLET_POINT_STRATEGY_DESC_ATTRIBUTES_FEATURES;
        $view_params['profiles']['bullet_point_strategy_df'] = self::BULLET_POINT_STRATEGY_DESC_FEATURES;

        $current_description_strategy = Configuration::get('AMAZON_DESCRIPTION_FIELD');

        if ($current_description_strategy && !in_array($current_description_strategy, array(Amazon::FIELD_DESCRIPTION_SHORT, Amazon::FIELD_DESCRIPTION_BOTH))) {
            $view_params['profiles']['bullet_point_strategy_shortd'] = true;
        } else {
            $view_params['profiles']['bullet_point_strategy_shortd'] = false;
        }


        $view_params['profiles']['universes'] = array();

        $view_params['profiles']['empty_profile_header'] = array();
        $view_params['profiles']['empty_profile_header']['name'] = null;
        $view_params['profiles']['empty_profile_header']['profile_id'] = 0;

        $empty_price_rule = array();
        $empty_price_rule['currency_sign'] = isset($current_currency->sign) ? $current_currency->sign : null;
        $empty_price_rule['type'] = 'percent';
        $empty_price_rule['rule']['from'][0] = '';
        $empty_price_rule['rule']['to'][0] = '';
        $empty_price_rule['rule']['percent'][0] = '';
        $empty_price_rule['rule']['value'][0] = '';

        $languages = AmazonTools::languages();


        foreach ($languages as $language) {
            $id_lang = $language['id_lang'];

            if (isset($this->config['regions']) && is_array($this->config['regions']) && array_key_exists($id_lang, $this->config['regions'])) {
                $region = $this->config['regions'][$id_lang];
            } else {
                $region = null;
            }

            if (!(isset($this->config['actives'][$id_lang]) && (int)$this->config['actives'][$id_lang])) {
                continue;
            }

            // Repricing Strategies
            $strategies = $this->getStrategies($id_lang);

            $view_params['profiles']['universes'][$id_lang] = AmazonSpecificField::universes($region);

            $view_params['profiles']['empty_profile'][$id_lang] = array();
            $view_params['profiles']['empty_profile'][$id_lang]['universe'] = null;
            $view_params['profiles']['empty_profile'][$id_lang]['product_type'] = null;
            $view_params['profiles']['empty_profile'][$id_lang]['product_type_translation'] = null;
            $view_params['profiles']['empty_profile'][$id_lang]['price_rule'] = $empty_price_rule;
            $view_params['profiles']['empty_profile'][$id_lang]['latency'] = null;

            if (!isset($profiles['name'])) {
                $profiles['name'] = array();
            }

            $profile_index = 0;

            foreach ($profiles['name'] as $profile_id => $profile_name) {
                if (!Tools::strlen($profile_key = AmazonTools::toKey($profile_name)) && $profile_id != 65535) {
                    continue;
                }

                // Skip empty entries
                if (empty($profile_name) && (!isset($profiles['master'][$profile_id]) || !$profiles['master'][$profile_id])) {
                    continue;
                }

                $profile_index++;

                $view_params['profiles']['header'][$profile_key] = array();
                $view_params['profiles']['header'][$profile_key]['profile_id'] = $profile_id;
                $view_params['profiles']['header'][$profile_key]['name'] = $profiles['name'][$profile_id];

                $p_universe = isset($profiles['universe'][$profile_id][$id_lang]) ? $profiles['universe'][$profile_id][$id_lang] : '';
                $p_product_type = isset($profiles['product_type'][$profile_id][$id_lang]) ? $profiles['product_type'][$profile_id][$id_lang] : '';
                $p_extra = isset($profiles['extra'][$profile_key][$id_lang]) ? $profiles['extra'][$profile_key][$id_lang] : '';

                if (!$product_type_translation = AmazonSettings::getProductTypeTranslation($region, $p_universe, $p_product_type)) {
                    $product_type_translation = $p_product_type;
                }

                if ($p_universe && $p_product_type) {
                    $type = sprintf('%s&nbsp;&gt;&nbsp;%s', $p_universe, $p_product_type);
                } else {
                    $type = $this->l('ERROR');
                }

                $view_params['profiles']['config'][$profile_key][$id_lang] = array();
                $view_params['profiles']['config'][$profile_key][$id_lang]['universe'] = $p_universe;
                $view_params['profiles']['config'][$profile_key][$id_lang]['product_type'] = $p_product_type;
                $view_params['profiles']['config'][$profile_key][$id_lang]['product_type_translation'] = $product_type_translation;
                $view_params['profiles']['config'][$profile_key][$id_lang]['type'] = $type;


                $view_params['profiles']['config'][$profile_key][$id_lang]['latency'] = isset($profiles['latency'][$profile_id][$id_lang]) ? $profiles['latency'][$profile_id][$id_lang] : '';
                $view_params['profiles']['config'][$profile_key][$id_lang]['combinations'] = isset($profiles['combinations'][$profile_id][$id_lang]) ? $profiles['combinations'][$profile_id][$id_lang] : '';

                $view_params['profiles']['config'][$profile_key][$id_lang]['code_exemption'] = isset($profiles['code_exemption'][$profile_id][$id_lang]) ? $profiles['code_exemption'][$profile_id][$id_lang] : self::EXEMPTION_NONE;

                if (isset($profiles['code_exemption'][$profile_id][$id_lang]) && $profiles['code_exemption'][$profile_id][$id_lang] == self::EXEMPTION_COMPATIBILITY) {
                    $view_params['profiles']['config'][$profile_key][$id_lang]['code_exemption_options'] = array('private_label' => true);
                } else {
                    $view_params['profiles']['config'][$profile_key][$id_lang]['code_exemption_options'] = isset($profiles['code_exemption_options'][$profile_id][$id_lang]) ? $profiles['code_exemption_options'][$profile_id][$id_lang] : null;
                }

                $view_params['profiles']['config'][$profile_key][$id_lang]['sku_as_supplier_reference'] = isset($profiles['sku_as_supplier_reference'][$profile_id][$id_lang]) ? $profiles['sku_as_supplier_reference'][$profile_id][$id_lang] : 0;
                $view_params['profiles']['config'][$profile_key][$id_lang]['sku_as_sup_ref_unconditionnaly'] = isset($profiles['sku_as_sup_ref_unconditionnaly'][$profile_id][$id_lang]) ? $profiles['sku_as_sup_ref_unconditionnaly'][$profile_id][$id_lang] : 0;

                $view_params['profiles']['config'][$profile_key][$id_lang]['item_type'] = isset($profiles['item_type'][$profile_id][$id_lang]) ? $profiles['item_type'][$profile_id][$id_lang] : '';

                if (isset($profiles['price_rule'][$profile_id][$id_lang]) && is_array($profiles['price_rule'][$profile_id][$id_lang]) && isset($profiles['price_rule'][$profile_id][$id_lang]['rule']['from']) && is_array($profiles['price_rule'][$profile_id][$id_lang]['rule']['from']) && isset($profiles['price_rule'][$profile_id][$id_lang]['rule']['to']) && is_array($profiles['price_rule'][$profile_id][$id_lang]['rule']['to'])) {
                    $view_params['profiles']['config'][$profile_key][$id_lang]['price_rule']['currency_sign'] = isset($current_currency->sign) ? $current_currency->sign : null;
                    $view_params['profiles']['config'][$profile_key][$id_lang]['price_rule']['type'] = isset($profiles['price_rule'][$profile_id][$id_lang]['type']) ? $profiles['price_rule'][$profile_id][$id_lang]['type'] : 'percent';

                    if (isset($profiles['price_rule'][$profile_id][$id_lang]['rule']['from']) && is_array($profiles['price_rule'][$profile_id][$id_lang]['rule']['from']) && isset($profiles['price_rule'][$profile_id][$id_lang]['rule']['to']) && is_array($profiles['price_rule'][$profile_id][$id_lang]['rule']['to'])) {
                        $view_params['profiles']['config'][$profile_key][$id_lang]['price_rule']['rule'] = $profiles['price_rule'][$profile_id][$id_lang]['rule'];
                        $price_rule_from  = $this->config['price_rule'][$id_lang]['rule']['from'];
                        $price_rule_to    = $this->config['price_rule'][$id_lang]['rule']['to'];
                        $price_rule_value = $this->config['price_rule'][$id_lang]['rule']['value'];
                        if ((!is_array($price_rule_from) || !count($price_rule_from))
                            && (!is_array($price_rule_to) || !count($price_rule_to))
                            && (!is_array($price_rule_value) || !count($price_rule_value))) {
                            $view_params['profiles']['config'][$profile_key][$id_lang]['price_rule']['rule']['from'][0] = '';
                            $view_params['profiles']['config'][$profile_key][$id_lang]['price_rule']['rule']['to'][0] = '';
                            $view_params['profiles']['config'][$profile_key][$id_lang]['price_rule']['rule']['percent'][0] = '';
                            $view_params['profiles']['config'][$profile_key][$id_lang]['price_rule']['rule']['value'][0] = '';
                        }
                    }
                } else {
                    // first use
                    $view_params['profiles']['config'][$profile_key][$id_lang]['price_rule'] = $empty_price_rule;
                }

                $view_params['profiles']['config'][$profile_key][$id_lang]['universe'] = $p_universe;

                if ($profile_index < self::MAX_PROFILES) {

                    $view_params['profiles']['config'][$profile_key][$id_lang]['specifics'] = AmazonSpecificField::displayFields($id_lang, $profile_name, $p_extra);
                } else {
                    $view_params['profiles']['config'][$profile_key][$id_lang]['specifics'] = array();
                    $view_params['profiles']['config'][$profile_key][$id_lang]['specifics']['has_data'] = is_array($p_extra) && count($p_extra) ? true : false;
                    $view_params['profiles']['config'][$profile_key][$id_lang]['specifics']['id_lang'] = $id_lang;
                    $view_params['profiles']['config'][$profile_key][$id_lang]['specifics']['profile_key'] = $profile_key;
                }

                $view_params['profiles']['config'][$profile_key][$id_lang]['bullet_point_strategy'] = isset($profiles['bullet_point_strategy'][$profile_id][$id_lang]) ? $profiles['bullet_point_strategy'][$profile_id][$id_lang] : null;
                $view_params['profiles']['config'][$profile_key][$id_lang]['bullet_point_labels'] = isset($profiles['bullet_point_labels'][$profile_id][$id_lang]) ? $profiles['bullet_point_labels'][$profile_id][$id_lang] : null;

                $p_browsenode = isset($profiles['browsenode'][$profile_id][$id_lang]) ? $profiles['browsenode'][$profile_id][$id_lang] : null;

                $view_params['profiles']['config'][$profile_key][$id_lang]['browse_node'] = str_replace(array(
                    ';',
                    ':',
                    '-',
                    ','
                ), ', ', $p_browsenode);

                $view_params['profiles']['config'][$profile_key][$id_lang]['strategies'] = $strategies;
                $view_params['profiles']['config'][$profile_key][$id_lang]['repricing'] = isset($profiles['repricing'][$profile_id][$id_lang]) ? $profiles['repricing'][$profile_id][$id_lang] : '';
                ;

                if ($this->config['features']['shipping']) {
                    // Shipping Templates
                    $view_params['profiles']['config'][$profile_key][$id_lang]['shipping_group'] = isset($profiles['shipping_group'][$profile_id][$id_lang]) ? $profiles['shipping_group'][$profile_id][$id_lang] : null;

                    $view_params['profiles']['shipping_templates']['enabled'] = $shipping_templates = is_array($this->config['shipping']) && isset($this->config['shipping']['shipping_templates']) && (bool)$this->config['shipping']['shipping_templates'];
                    $view_params['profiles']['shipping_templates']['groups'][$id_lang] = array();

                    if ($shipping_templates) {
                        $configured_group_names = unserialize(AmazonConfiguration::get('shipping_groups'));

                        if (is_array($configured_group_names) && count($configured_group_names)) {
                            foreach ($configured_group_names as $group_region => $group_names) {
                                if ($group_region != $region) {
                                    continue;
                                }

                                $view_params['profiles']['shipping_templates']['groups'][$id_lang] = array();

                                if (is_array($group_names) && count($group_names)) {
                                    foreach ($group_names as $group_key => $group_name) {
                                        $view_params['profiles']['shipping_templates']['groups'][$id_lang][$group_key] = $group_name;
                                    }
                                }
                            }
                        }
                    }
                }

                $view_params['profiles']['ptc'][$id_lang]= AmazonTaxes::getPtcList($region);
                $view_params['profiles']['config'][$profile_key][$id_lang]['ptc_selected'] = isset($profiles['ptc'][$profile_id][$id_lang]) ? $profiles['ptc'][$profile_id][$id_lang] : null;
            }
        }
        return ($view_params);
    }

    private function _mapping(&$view_params)
    {
        $selected_tab = $this->selectedTab();

        $languages = AmazonTools::languages();
        $lang_admin = Language::getIsoById($this->id_lang);

        $view_params['mapping'] = array();
        $view_params['mapping']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_MAPPINGS);
        $view_params['mapping']['selected_tab'] = $selected_tab === 'mapping' ? true : false;
        $view_params['mapping']['validation'] = $this->_validate(true);
        $view_params['mapping']['images_url'] = $this->images;
        $view_params['mapping']['attributes'] = array();
        $view_params['mapping']['lang'] = array();
        $view_params['mapping']['ungroup'] = $ungroup = (isset($this->config['mapping']['ungroup']) && (bool)$this->config['mapping']['ungroup'] ? true : false);
        $view_params['mapping']['feature'] = array();
        $view_params['mapping']['fixed'] = array();
        $view_params['mapping']['attribute'] = array();

        $valid_values = array();

        if (is_array($this->config['mapping']) && count($this->config['mapping'])) {
            $mapping = &$this->config['mapping'];
        } else {
            $mapping = array();
        }

        //
        // Amazon Attributes Mapping
        //
        foreach ($languages as $language) {
            $id_lang = $language['id_lang'];
            $iso_lang = $language['iso_code'];
            $id_attribute_group = null;

            $matching_entries = AmazonSpecificField::getMatchingEntries($this->profiles, $id_lang);

            if (!(isset($this->config['actives'][$id_lang]) && (int)$this->config['actives'][$id_lang])) {
                continue;
            }

            if (isset($this->config['regions']) && is_array($this->config['regions']) && isset($this->config['regions'][$id_lang])) {
                $region = $this->config['regions'][$id_lang];
            } else {
                $region = null;
            }

            if (isset(self::$attributes_groups[$id_lang])) {
                $attributes_groups = &self::$attributes_groups[$id_lang];
            } else {
                //TODO: Preserve the reference
                $attributes_groups = array();
            }

            if (isset(self::$attributes[$id_lang])) {
                $attributes = &self::$attributes[$id_lang];
            } else {
                $attributes = array();
            }

            if (is_array(self::$features) && isset(self::$features[$id_lang])) {
                $features = &self::$features[$id_lang];
            } else {
                $features = array();
            }

            if (is_array(self::$features_values) && isset(self::$features_values[$id_lang])) {
                $features_values = &self::$features_values[$id_lang];
            } else {
                $features_values = array();
            }

            //
            // 2014-11-24 International Valid Color Map
            //
            $standard_colors = array();

            foreach (AmazonXSD::$langColorMap as $color => $langColorMap) {
                if (!isset($langColorMap[$iso_lang])) {
                    continue;
                }
                $color_key = AmazonTools::toKey($langColorMap[$iso_lang]);
                if (isset(AmazonXSD::$langColorMap[$color][$iso_lang])) {
                    $standard_colors[$color_key] = AmazonXSD::$langColorMap[$color][$iso_lang];
                } elseif (isset(AmazonXSD::$langColorMap[$color]['en']) && is_array(AmazonXSD::$langColorMap[$color]['en'])) {
                    $standard_colors[$color_key] = AmazonXSD::$langColorMap[$color]['en'];
                } else {
                    $standard_colors[$color_key] = AmazonTools::ucfirst($color);
                }
                $standard_colors[$color_key] = AmazonXSD::$langColorMap[$color][$iso_lang];
            }

            $view_params['mapping']['lang'][$id_lang] = array();
            $view_params['mapping']['lang'][$id_lang]['name'] = $language['name'];
            $view_params['mapping']['lang'][$id_lang]['iso_code'] = $language['iso_code'];
            $view_params['mapping']['lang'][$id_lang]['flag'] = $this->images.'geo_flags/'.$this->geoFlag($language['id_lang']).'.gif';

            $view_params['mapping']['feature'][$id_lang] = array();

            // Matching Entry: Features
            //
            foreach ($matching_entries as $matching_entry) {
                if ($matching_entry['prestashop_type'] != 'feature') {
                    continue;
                }

                if (!is_array($features) || !count($features) || !is_array($features_values) || !count($features_values)) {
                    continue;
                }

                if (!($id_feature = (int)$matching_entry['prestashop_id'])) {
                    continue;
                }

                if (!isset($features[$id_feature]) || !is_array($features[$id_feature]) || !count($features[$id_feature])
                    || !isset($features_values[$id_feature]) || !is_array($features_values[$id_feature]) || !count($features_values[$id_feature])) {
                    continue;
                }

                $profile_key = $matching_entry['profile_key'];
                $profile_name = $matching_entry['profile_name'];

                $amazon_attr_label = trim(preg_replace('/([A-Z])/', ' \1', $matching_entry['amazon_attribute']));

                if ($ungroup) {
                    $title = sprintf('%s, %s &gt; %s', $profile_name, $amazon_attr_label, $features[$id_feature]['name']);
                    $mapping_key = $profile_key;
                } else {
                    $title = sprintf('%s, %s &gt; %s', $matching_entry['universe'], $amazon_attr_label, $features[$id_feature]['name']);
                    $mapping_key = sprintf('%s/%s', $matching_entry['universe'], $matching_entry['amazon_attribute']);
                }

                $view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature] = array();
                $view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature]['name'] = $title;
                $view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature]['left'] = array();
                $view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature]['right'] = array();
                $view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature]['mandatory'] = $matching_entry['mandatory'];


                $target_mapping = (is_array($mapping) && isset($mapping['features']) && isset($mapping['features']['free']) && isset($mapping['features']['free'][$id_lang])) ? $mapping['features']['free'][$id_lang] : array();
                $constrained_mapping = false;

                if ($this->config['features']['creation'] && Tools::strlen($matching_entry['universe']) && Tools::strlen($matching_entry['product_type']) && $region) {
                    $valid_values = AmazonValidValues::getValidValues($matching_entry['universe'], $matching_entry['amazon_attribute'], $region);

                    if (is_array($valid_values) && count($valid_values)) {
                        foreach ($valid_values as $valid_value_key => $valid_value) {
                            if (!Tools::strlen($valid_value)) {
                                continue;
                            }

                            $view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature]['right'][$valid_value_key] = $valid_value;
                        }
                        $target_mapping = (is_array($mapping) && isset($mapping['features']) && isset($mapping['features']['const']) && isset($mapping['features']['const'][$id_lang])) ? $mapping['features']['const'][$id_lang] : array();
                        $constrained_mapping = true;
                    }
                }
                $view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature]['has_valid_values'] =
                    is_array($view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature]['right'])
                    && count($view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature]['right']);

                $matched = array();

                foreach ($features_values[$id_feature] as $id_feature_value => $feature_value) {
                    $feature_value['mapping'] = null;

                    $has_key = is_array($target_mapping) && isset($target_mapping[$mapping_key]);
                    $has_feature = $has_key && isset($target_mapping[$mapping_key][$id_feature]);
                    $has_feature_value = $has_feature && isset($target_mapping[$mapping_key][$id_feature][$id_feature_value]);

                    $feature_key = AmazonTools::toKey($feature_value['value']);

                    if ($constrained_mapping && is_array($target_mapping) && count($target_mapping) && $has_feature_value) {
                        $feature_value['mapping'] = AmazonTools::toKey($target_mapping[$mapping_key][$id_feature][$id_feature_value]);
                    } elseif ($constrained_mapping) {
                        $feature_value['mapping'] = null;
                    } elseif (is_array($target_mapping) && count($target_mapping) && $has_feature_value) {
                        $feature_value['mapping'] = $target_mapping[$mapping_key][$id_feature][$id_feature_value];
                    }

                    if ($matching_entry['is_color'] && is_array($valid_values) && count($valid_values) && isset($valid_values[$feature_key])) {
                        $matched[] = $feature_value['value'];
                        continue;
                    } elseif ($matching_entry['is_color'] && isset($standard_colors[$feature_key])) {
                        $matched[] = $feature_value['value'];
                        continue;
                    }

                    $view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature]['left'][$id_feature_value] = $feature_value;
                }

                if (is_array($matched) && count($matched)) {
                    $matching_list = implode(', ', array_unique($matched));

                    if (Tools::strlen($matching_list) > 64) {
                        preg_replace('/(?<=^.{64}).{4,}(?=.{64}$)/', '...', $matching_list);
                    }

                    $view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature]['match_list'] = $matching_list;
                    $view_params['mapping']['feature'][$id_lang][$mapping_key][$id_feature]['mandatory'] = false;
                }

                if (!is_array($view_params['mapping']['feature'][$id_lang][$mapping_key])
                    || !count($view_params['mapping']['feature'][$id_lang][$mapping_key])) {
                    unset($view_params['mapping']['feature'][$id_lang][$mapping_key]);
                }
            }
            if ($iso_lang === $lang_admin) {
                $view_params['mapping']['lang'][$id_lang]['feat_collapsed'] = true;
            } else {
                $view_params['mapping']['lang'][$id_lang]['feat_collapsed'] = false;
            }

            $view_params['mapping']['attribute'][$id_lang] = array();

            // Matching Entry: Attributes
            //
            foreach ($matching_entries as $matching_entry) {
                if ($matching_entry['prestashop_type'] != 'attribute') {
                    continue;
                }

                if (!is_array($attributes) || !count($attributes) || !is_array($attributes_groups) || !count($attributes_groups)) {
                    continue;
                }

                if (!($id_attribute_group = (int)$matching_entry['prestashop_id'])) {
                    continue;
                }

                if (!isset($attributes_groups[$id_attribute_group])
                    || !is_array($attributes_groups[$id_attribute_group])
                    || !count($attributes_groups[$id_attribute_group])) {
                    continue;
                }

                $first_attribute = reset($attributes[$id_attribute_group]);

                $profile_key = $matching_entry['profile_key'];
                $profile_name = $matching_entry['profile_name'];
                $amazon_attr_label = trim(preg_replace('/([A-Z])/', ' \1', $matching_entry['amazon_attribute']));

                if ($ungroup) {
                    $title = sprintf('%s, %s &gt; %s', $profile_name, $amazon_attr_label, $first_attribute['attribute_group']);
                    $mapping_key = $profile_key;
                } else {
                    $title = sprintf('%s, %s &gt; %s', $matching_entry['universe'], $amazon_attr_label, $first_attribute['attribute_group']);
                    $mapping_key = sprintf('%s/%s', $matching_entry['universe'], $matching_entry['amazon_attribute']);
                }

                $view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group] = array();
                $view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group]['name'] = $title;
                $view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group]['left'] = array();
                $view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group]['right'] = array();
                $view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group]['mandatory'] = $matching_entry['mandatory'];

                $target_mapping = (is_array($mapping) && isset($mapping['attributes']) && isset($mapping['attributes']['free']) && isset($mapping['attributes']['free'][$id_lang])) ? $mapping['attributes']['free'][$id_lang] : array();

                $constrained_mapping = false;

                if ($this->config['features']['creation'] && Tools::strlen($matching_entry['universe']) && Tools::strlen($matching_entry['product_type']) && $region) {
                    $valid_values = AmazonValidValues::getValidValues($matching_entry['universe'], $matching_entry['amazon_attribute'], $region);

                    if (is_array($valid_values) && count($valid_values)) {
                        foreach ($valid_values as $valid_value_key => $valid_value) {
                            if (!Tools::strlen($valid_value)) {
                                continue;
                            }

                            $view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group]['right'][$valid_value_key] = $valid_value;
                        }
                        $target_mapping = (is_array($mapping) && isset($mapping['attributes']) && isset($mapping['attributes']['const']) && isset($mapping['attributes']['const'][$id_lang])) ? $mapping['attributes']['const'][$id_lang] : array();
                        $constrained_mapping = true;
                    }
                }

                $view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group]['has_valid_values'] = $has_valid_values =
                    is_array($view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group]['right'])
                    && count($view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group]['right']);

                $matched = array();

                foreach ($attributes[$id_attribute_group] as $id_attribute => $attribute) {
                    $attribute['mapping'] = null;
                    $attribute_key = AmazonTools::toKey($attribute['name']);

                    $has_key = is_array($target_mapping) && isset($target_mapping[$mapping_key]);
                    $has_attribute_group = $has_key && isset($target_mapping[$mapping_key][$id_attribute_group]);
                    $has_attribute = $has_attribute_group && isset($target_mapping[$mapping_key][$id_attribute_group][$id_attribute]);

                    if ($constrained_mapping && is_array($target_mapping) && count($target_mapping) && $has_attribute) {
                        $attribute['mapping'] = AmazonTools::toKey($target_mapping[$mapping_key][$id_attribute_group][$id_attribute]);
                    } elseif ($constrained_mapping) {
                        $attribute['mapping'] = null;
                    } elseif (is_array($target_mapping) && count($target_mapping) && $has_attribute) {
                        $attribute['mapping'] = $target_mapping[$mapping_key][$id_attribute_group][$id_attribute];
                    }

                    if ($matching_entry['is_color'] && is_array($valid_values) && count($valid_values) && isset($valid_values[$attribute_key])) {
                        $matched[] = $attribute['name'];
                        continue;
                    } elseif ($matching_entry['is_color'] && isset($valid_values[$attribute_key])) {
                        $matched[] = $attribute['name'];
                        continue;
                    } elseif ($matching_entry['is_color'] && isset($standard_colors[$attribute_key])) {
                        $matched[] = $attribute['name'];
                        continue;
                    } elseif ($matching_entry['amazon_attribute'] == 'Size' && isset($valid_values[$attribute_key])) {
                        $matched[] = $attribute['name'];
                        continue;
                    }

                    $view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group]['left'][$id_attribute] = $attribute;
                }

                if (is_array($matched) && count($matched)) {
                    $matching_list = implode(', ', array_unique($matched));

                    if (Tools::strlen($matching_list) > 64) {
                        preg_replace('/(?<=^.{64}).{4,}(?=.{64}$)/', '...', $matching_list);
                    }

                    $view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group]['match_list'] = $matching_list;
                    $view_params['mapping']['attribute'][$id_lang][$mapping_key][$id_attribute_group]['mandatory'] = false;
                }

                if (!count($view_params['mapping']['attribute'][$id_lang][$mapping_key])) {
                    unset($view_params['mapping']['attribute'][$id_lang][$mapping_key]);
                }
            }


            if ($iso_lang === $lang_admin) {
                $view_params['mapping']['lang'][$id_lang]['attr_collapsed'] = true;
            } else {
                $view_params['mapping']['lang'][$id_lang]['attr_collapsed'] = false;
            }

            $view_params['mapping']['fixed'][$id_lang] = array();

            // Matching Entry: Fixed Values
            //
            foreach ($matching_entries as $matching_entry) {
                if (!$matching_entry['fixed_value']) {
                    continue;
                }

                $amazon_attribute = $matching_entry['amazon_attribute'];
                $profile_key = $matching_entry['profile_key'];
                $profile_name = $matching_entry['profile_name'];

                $amazon_attr_label = trim(preg_replace('/([A-Z])/', ' \1', $amazon_attribute));

                if ($ungroup) {
                    $title = sprintf('%s, %s', $profile_name, $amazon_attr_label);
                    $mapping_key = $profile_key;
                } else {
                    $title = sprintf('%s, %s', $matching_entry['universe'], $amazon_attr_label);
                    $mapping_key = sprintf('%s/%s', $matching_entry['universe'], $matching_entry['amazon_attribute']);
                }

                if (is_array($mapping) && isset($mapping['fixed']) && isset($mapping['fixed'][$id_lang]) && isset($mapping['fixed'][$id_lang][$mapping_key]) && isset($mapping['fixed'][$id_lang][$mapping_key][$amazon_attribute])) {
                    $value = $mapping['fixed'][$id_lang][$mapping_key][$amazon_attribute];
                } else {
                    $value = null;
                }

                $view_params['mapping']['fixed'][$id_lang][$mapping_key][$amazon_attribute] = array();
                $view_params['mapping']['fixed'][$id_lang][$mapping_key][$amazon_attribute]['name'] = $title;
                $view_params['mapping']['fixed'][$id_lang][$mapping_key][$amazon_attribute]['value'] = $value;
            }

            if ($iso_lang === $lang_admin) {
                $view_params['mapping']['lang'][$id_lang]['fixed_collapsed'] = true;
            } else {
                $view_params['mapping']['lang'][$id_lang]['fixed_collapsed'] = false;
            }

            $view_params['mapping']['add_mapping'][$id_lang] = array();
            $view_params['mapping']['add_mapping_lang'][$id_lang] = array();

            if (is_array($matching_entries) && count($matching_entries)) {
                $used_universes_attributes = array();

                foreach ($matching_entries as $matching_entry) {
                    if (array_key_exists('fixed_value', $matching_entry) && (bool)$matching_entry['fixed_value']) {
                        continue;
                    }

                    $valid_values = AmazonValidValues::getValidValues($matching_entry['universe'], $matching_entry['amazon_attribute'], $region);

                    if (!is_array($valid_values) || !count($valid_values)) {
                        continue;
                    }

                    $universe = $matching_entry['universe'];
                    $attr_key = AmazonTools::toKey($matching_entry['amazon_attribute']);

                    $used_universes_attributes[$universe][$attr_key] = $matching_entry['amazon_attribute'];
                }
                $additionnal_mapping_values = array();

                if (is_array($used_universes_attributes) && count($used_universes_attributes)) {
                    asort($used_universes_attributes);

                    foreach ($used_universes_attributes as $universe => $mappings) {
                        if (!is_array($mappings)) {
                            continue;
                        }

                        $additionnal_mapping_values[$universe] = array();

                        foreach ($mappings as $attribute_key => $amazon_attribute) {
                            $result = AmazonValidValues::getCustomMapping($universe, $amazon_attribute, $region);

                            if (is_array($result) && count($result)) {
                                $additionnal_mapping_values[$universe][$amazon_attribute] = implode(',', $result);
                            }
                        }
                    }
                }

                $view_params['mapping']['add_mapping'][$id_lang] = $used_universes_attributes;
                $view_params['mapping']['add_mapping_values'][$id_lang] = $additionnal_mapping_values;

                $view_params['mapping']['add_mapping_lang'][$id_lang] = array();
                $view_params['mapping']['add_mapping_lang'][$id_lang]['name'] = $language['name'];
                $view_params['mapping']['add_mapping_lang'][$id_lang]['flag'] = $this->images.'geo_flags/'.$this->geoFlag($language['id_lang']).'.gif';
                $view_params['mapping']['add_mapping_lang'][$id_lang]['iso_code'] = $language['iso_code'];
                $view_params['mapping']['add_mapping_lang'][$id_lang]['region'] = $region;

                if ($iso_lang === $lang_admin) {
                    $view_params['mapping']['add_mapping_lang'][$id_lang]['attr_collapsed'] = true;
                } else {
                    $view_params['mapping']['add_mapping_lang'][$id_lang]['attr_collapsed'] = false;
                }
            }

            if (!is_array($view_params['mapping']['add_mapping_lang'][$id_lang])
                || !count($view_params['mapping']['add_mapping_lang'][$id_lang])) {
                unset($view_params['mapping']['add_mapping_lang'][$id_lang]);
            }

            if (!is_array($view_params['mapping']['add_mapping'][$id_lang])
                || !count($view_params['mapping']['add_mapping'][$id_lang])) {
                unset($view_params['mapping']['add_mapping'][$id_lang]);
            }

            if (!is_array($view_params['mapping']['fixed'][$id_lang])
                || !count($view_params['mapping']['fixed'][$id_lang])) {
                unset($view_params['mapping']['fixed'][$id_lang]);
            }

            if (!is_array($view_params['mapping']['feature'][$id_lang])
                || !count($view_params['mapping']['feature'][$id_lang])) {
                unset($view_params['mapping']['feature'][$id_lang]);
            }

            if (!is_array($view_params['mapping']['attribute'][$id_lang])
                || !count($view_params['mapping']['attribute'][$id_lang])) {
                unset($view_params['mapping']['attribute'][$id_lang]);
            }
        }
        $count_fixed     = is_array($view_params['mapping']['fixed']) ? count($view_params['mapping']['fixed']) : 0;
        $count_feature   = is_array($view_params['mapping']['feature']) ? count($view_params['mapping']['feature']) : 0;
        $count_attribute = is_array($view_params['mapping']['attribute']) ? count($view_params['mapping']['attribute']) : 0;
        $count_all = $count_fixed + $count_feature + $count_attribute;

        if (!$count_fixed) {
            unset($view_params['mapping']['fixed']);
        }

        if (!$count_feature) {
            unset($view_params['mapping']['feature']);
        }

        if (!$count_attribute) {
            unset($view_params['mapping']['attribute']);
        }

        $view_params['mapping']['count'] = $count_all;

        return ($view_params);
    }

    private function _filters(&$view_params)
    {
        $view_params['filters']['selected_tab'] = $this->selectedTab() === 'filters' ? true : false;
        $view_params['filters']['images_url'] = $this->images;
        $view_params['filters']['url'] = $this->url;
        $view_params['filters']['validation'] = $this->_validate(true);
        $view_params['filters']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_FILTERS);

        $price_filter = $this->config['price_filter'];

        // Price Filtering
        //
        $view_params['filters']['prices'] = array();
        $view_params['filters']['prices']['currency_sign'] = Currency::getDefaultCurrency()->sign;
        $view_params['filters']['prices']['gt'] = null;
        $view_params['filters']['prices']['lt'] = null;

        if (is_array($price_filter) && isset($price_filter['gt']) && (float)$price_filter['gt']) {
            $view_params['filters']['prices']['gt'] = sprintf('%.02f', $price_filter['gt']);
        }

        if (is_array($price_filter) && isset($price_filter['lt']) && (float)$price_filter['lt']) {
            $view_params['filters']['prices']['lt'] = sprintf('%.02f', $price_filter['lt']);
        }

        // Manufacturers Filtering
        //
        $manufacturers = Manufacturer::getManufacturers(false, $this->id_lang);

        $selected_manufacturers = $this->config['excluded_manufacturers'];

        $filtered_manufacturers = array();
        $available_manufacturers = array();

        if (is_array($manufacturers) && count($manufacturers)) {
            foreach ($manufacturers as $manufacturer) {
                if (is_array($selected_manufacturers) && in_array((string)$manufacturer['id_manufacturer'], $selected_manufacturers)) {
                    continue;
                }

                $available_manufacturers[$manufacturer['id_manufacturer']] = $manufacturer['name'];
            }
            if (is_array($selected_manufacturers) && count($selected_manufacturers)) {
                foreach ($manufacturers as $manufacturer) {
                    if (is_array($selected_manufacturers) && !in_array((string)$manufacturer['id_manufacturer'], $selected_manufacturers)) {
                        continue;
                    }

                    $filtered_manufacturers[$manufacturer['id_manufacturer']] = $manufacturer['name'];
                }
            }
        }
        $view_params['filters']['manufacturers'] = array();
        $view_params['filters']['manufacturers']['available'] = $available_manufacturers;
        $view_params['filters']['manufacturers']['filtered'] = $filtered_manufacturers;

        // Suppliers Filtering
        //
        $suppliers = Supplier::getSuppliers(false, $this->id_lang);

        $selected_suppliers = $this->config['excluded_suppliers'];
        $filtered_suppliers = array();
        $available_suppliers = array();

        if (is_array($suppliers) && count($suppliers)) {
            foreach ($suppliers as $supplier) {
                if (is_array($selected_suppliers) && in_array((string)$supplier['id_supplier'], $selected_suppliers)) {
                    continue;
                }

                $available_suppliers[$supplier['id_supplier']] = $supplier['name'];
            }
            if (is_array($selected_suppliers) && count($selected_suppliers)) {
                foreach ($suppliers as $supplier) {
                    if (is_array($selected_suppliers) && !in_array((string)$supplier['id_supplier'], $selected_suppliers)) {
                        continue;
                    }

                    $filtered_suppliers[$supplier['id_supplier']] = $supplier['name'];
                }
            }
        }
        $view_params['filters']['suppliers'] = array();
        $view_params['filters']['suppliers']['available'] = $available_suppliers;
        $view_params['filters']['suppliers']['filtered'] = $filtered_suppliers;
    }

    private function _shipping(&$view_params)
    {
        $view_params['shipping'] = array();
        $view_params['shipping']['selected_tab'] = $this->selectedTab() === 'shipping' ? true : false;
        $view_params['shipping']['images_url'] = $this->images;
        $view_params['shipping']['url'] = $this->url;
        $view_params['shipping']['validation'] = $this->_validate(true);
        $view_params['shipping']['shipping_url'] = $this->url.'functions/download_reports.php?instant_token='.$this->config['instant_token'].'&context_key='.AmazonContext::getKey($this->context->shop);
        $view_params['shipping']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_SHIPPING);
        $view_params['shipping']['expert_mode'] = (bool)$this->config['features']['expert_mode'];

        $view_params['shipping']['carriers'] = array();
        $view_params['shipping']['smart_shipping'] = array();
        $view_params['shipping']['smart_shipping']['mapping'] = array();

        $view_params['shipping']['smart_shipping']['kind'] = array();
        $view_params['shipping']['smart_shipping']['kind']['additive'] = self::SHIPPING_OVERRIDE_ADDITIVE;
        $view_params['shipping']['smart_shipping']['kind']['exclusive'] = self::SHIPPING_OVERRIDE_EXCLUSIVE;
        $view_params['shipping']['smart_shipping']['kind']['value'] = null;

        if (!is_array($this->config['shipping'])) {
            $this->config['shipping'] = array();
        }

        if (isset($this->config['shipping']['allow_overrides'])) {
            $view_params['shipping']['allow_overrides'] = (bool)$this->config['shipping']['allow_overrides'];
        } else {
            $view_params['shipping']['allow_overrides'] = false;
        }

        if (isset($this->config['shipping']['tare']) && (float)$this->config['shipping']['tare']) {
            $view_params['shipping']['tare'] = sprintf('%.02f', (float)$this->config['shipping']['tare']);
        } else {
            $view_params['shipping']['tare'] = 0;
        }

        if (isset($this->config['shipping']['gauge']) && (float)$this->config['shipping']['gauge']) {
            $view_params['shipping']['gauge'] = sprintf('%.02f', (float)$this->config['shipping']['gauge']);
        } else {
            $view_params['shipping']['gauge'] = 0;
        }

        $view_params['shipping']['marketplaces'] = array();
        $view_params['shipping']['marketplaces']['countries'] = $marketplace_countries = AmazonSpecificField::countrySelector();
        $view_params['shipping']['marketplaces']['show'] = is_array($marketplace_countries) && count($marketplace_countries) > 1;

        $view_params['shipping']['shipping_templates'] = array();

        if ($this->config['features']['shipping']) {
            // Shipping Templates
            $view_params['shipping']['shipping_templates']['enabled'] = $shipping_templates = is_array($this->config['shipping']) && isset($this->config['shipping']['shipping_templates']) && (bool)$this->config['shipping']['shipping_templates'];
            $view_params['shipping']['shipping_templates']['groups'] = array();

            if ($shipping_templates) {
                $configured_group_names = unserialize(AmazonConfiguration::get('shipping_groups'));

                if (is_array($configured_group_names) && count($configured_group_names)) {
                    foreach ($configured_group_names as $region => $group_names) {
                        $view_params['shipping']['shipping_templates']['groups'][$region] = array();

                        if (is_array($group_names) && count($group_names)) {
                            foreach ($group_names as $group_key => $group_name) {
                                $view_params['shipping']['shipping_templates']['groups'][$region][$group_key] = $group_name;
                            }
                        }
                    }
                }
            }
        } else {
            $view_params['shipping']['shipping_templates']['enabled'] = false;
            $view_params['shipping']['shipping_templates']['groups'] = array();
        }

        if (isset($this->config['shipping']['smart_shipping']) && is_array($this->config['shipping']['smart_shipping'])) {
            $view_params['shipping']['smart_shipping'] = $this->config['shipping']['smart_shipping'];
        }

        $view_params['shipping']['smart_shipping']['kind'] = array();
        $view_params['shipping']['smart_shipping']['kind']['additive'] = self::SHIPPING_OVERRIDE_ADDITIVE;
        $view_params['shipping']['smart_shipping']['kind']['exclusive'] = self::SHIPPING_OVERRIDE_EXCLUSIVE;
        $view_params['shipping']['smart_shipping']['kind']['value'] = null;

        if (!isset($view_params['shipping']['smart_shipping']['active'])) {
            $view_params['shipping']['smart_shipping']['active'] = false;
        }

        $carriers = &self::$carriers;

        foreach ($carriers as $carrier) {
            $view_params['shipping']['carriers'][$carrier['id_carrier']] = $carrier['name'];
        }

        if (isset($this->config['shipping_methods']) && is_array($this->config['shipping_methods'])) {
            if (isset($this->config['shipping']['smart_shipping']['kind'])) {
                $view_params['shipping']['smart_shipping']['kind']['value'] = $this->config['shipping']['smart_shipping']['kind'];
            }

            foreach ($this->config['shipping_methods'] as $shipping_method) {
                if (empty($shipping_method)) {
                    continue;
                }

                if (isset($this->config['shipping']['smart_shipping']['prestashop'][$shipping_method]) && $this->config['shipping']['smart_shipping']['prestashop'][$shipping_method]) {
                    $selected = $this->config['shipping']['smart_shipping']['prestashop'][$shipping_method];
                } else {
                    $selected = null;
                }

                $view_params['shipping']['smart_shipping']['mapping'][$shipping_method] = $selected;
            }
        }
    }

    /*Sets initial data form new SHOP when data si duplicated form Main shop - added for multistore comaptibility - Apr/6/2014 - ERT*/
    /*
        public function hookActionShopDataDuplication($params){
        $shop = new Shop((int)$params["new_id_shop"]);
        $id_shop_group = $shop->id_shop_group;
        $id_shop = $shop->id;
        foreach ($this->_config as $key => $value)
        {
            if (is_null($value))
                $value = '';

            if (is_array($value))
                $value = AmazonTools::encode(serialize($value));

            if (!self::configurationUpdateValue($key, $value, $id_shop_group, $id_shop))
            {
                $this->_errors[] = sprintf('%s - key: %s, value: %s', $this->l('Unable to duplicate data for Shop ID '. $params["new_id_shop"] .' : Some configuration values'), $key, nl2br(print_r($value, true)));
            }
        }
        }
        */

    private function _messaging(&$view_params)
    {
        require_once(dirname(__FILE__).'/classes/amazon.messaging.class.php');

        $mail_invoice = $this->config['mail_invoice'];
        $mail_review = $this->config['mail_review'];
        $mail_customer_thread = $this->config['mail_customer_thread'];

        $pass = true;
        $lang = Language::getIsoById($this->id_lang);

        $mail_templates = null;
        $mail_add_files = null;

        $view_params['messaging'] = array();

        $view_params['messaging']['selected_tab'] = $this->selectedTab() === 'messaging' ? true : false;
        $view_params['messaging']['images_url'] = $this->images;
        $view_params['messaging']['is_ps15'] = version_compare(_PS_VERSION_, '1.5', '>=');
        $view_params['messaging']['url'] = $this->url;
        $view_params['messaging']['validation'] = $this->_validate(true);
        $view_params['messaging']['account_type_is_global'] = (bool)($this->config['account_type'] == self::ACCOUNT_TYPE_GLOBAL);

        $view_params['messaging']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_MESSAGING);
        // Order State
        //
        $orderStates = OrderState::getOrderStates($this->id_lang);

        $view_params['messaging']['order_states'] = array();

        $c = 0;
        foreach ($orderStates as $orderState) {
            if (!(int)$orderState['id_order_state']) {
                continue;
            }

            if (!$orderState['invoice'] || $orderState['send_email']) {
                continue;
            }

            $view_params['messaging']['order_states'][$c]['value'] = (int)$orderState['id_order_state'];
            $view_params['messaging']['order_states'][$c]['name'] = $orderState['name'];
            $c++;
        }

        if (!is_dir($this->path_mail.$lang)) {
            $lang = 'en';
        }

        $mailDir = sprintf('%s%s/*.html', $this->path_mail, $lang);

        // Mail Template
        //
        if (is_dir($this->path_mail.$lang)) {
            $files = glob($mailDir);

            if ($files) {
                $result = preg_replace('#.*/(\w*)\.html#', '$1', $files);

                if (is_array($result)) {
                    $mail_templates = array_unique($result);
                } else {
                    $pass = false;
                }
            } else {
                $pass = false;
            }
        } else {
            $pass = false;
        }

        // Optionnal Additionnal File
        //
        if (is_dir($this->path_pdf)) {
            $files = glob($this->path_pdf.'*.pdf');

            if ($files) {
                $result = preg_replace('#.*/(\w*)#', '$1', $files);

                if (is_array($result)) {
                    $mail_add_files = array_unique($result);
                }
            }
        }

        if ($pass) {
            $view_params['messaging']['problem'] = false;
        } else {
            $view_params['messaging']['problem'] = true;
        }

        // Testing feature
        if (AmazonTools::tableExists(_DB_PREFIX_.self::TABLE_MARKETPLACE_ORDERS)) {
            $sql = 'SELECT o.`id_order`, concat(c.`firstname`, " ", c.`lastname`) as customer from `'._DB_PREFIX_.self::TABLE_MARKETPLACE_ORDERS.'` mo 
                        LEFT JOIN `'._DB_PREFIX_.'orders` o on (o.`id_order` = mo.`id_order`)
                        LEFT JOIN `'._DB_PREFIX_.'customer` c on (o.`id_customer` = c.`id_customer`)
                        ORDER BY o.`id_order` DESC LIMIT 10';

            $results = Db::getInstance()->ExecuteS($sql);

            if (is_array($results) && count($results)) {
                $view_params['messaging']['test'] = array();
                foreach ($results as $order) {
                    $id_order = $order['id_order'];
                    $view_params['messaging']['test'][$id_order] = array();
                    $view_params['messaging']['test'][$id_order]['id_order'] = $order['id_order'];
                    $view_params['messaging']['test'][$id_order]['customer'] = $order['customer'];
                }
            }
        }

        $view_params['messaging']['mail_templates'] = $mail_templates;
        $view_params['messaging']['mail_add_files'] = $mail_add_files;
        $view_params['messaging']['experimental'] = self::ENABLE_EXPERIMENTAL_FEATURES;

        $view_params['messaging']['mail_invoice'] = array();
        $view_params['messaging']['mail_invoice']['active'] = isset($mail_invoice['active']) ? $mail_invoice['active'] : false;
        $view_params['messaging']['mail_invoice']['template'] = isset($mail_invoice['template']) ? $mail_invoice['template'] : null;
        $view_params['messaging']['mail_invoice']['additionnal'] = isset($mail_invoice['additionnal']) ? $mail_invoice['additionnal'] : null;
        $view_params['messaging']['mail_invoice']['order_state'] = isset($mail_invoice['order_state']) && $mail_invoice['order_state'] ? $mail_invoice['order_state'] : null;

        $view_params['messaging']['mail_review'] = array();
        $view_params['messaging']['mail_review']['active'] = isset($mail_review['active']) ? $mail_review['active'] : false;
        $view_params['messaging']['mail_review']['template'] = isset($mail_review['template']) ? $mail_review['template'] : null;
        $view_params['messaging']['mail_review']['delay'] = isset($mail_review['delay']) ? $mail_review['delay'] : null;
        $view_params['messaging']['mail_review']['order_state'] = isset($mail_review['order_state']) && $mail_review['order_state'] ? $mail_review['order_state'] : null;

        $email_providers = AmazonMessaging::$email_providers;
        $default_email_provider = key($email_providers);

        $view_params['messaging']['customer_thread'] = array();
        $view_params['messaging']['customer_thread']['labels'] = array();

        $labels_count = 0;
        foreach ($this->config['actives'] as $id_lang => $active) {
            if (!$active) {
                continue;
            }
            $regions = &$this->config['regions'];
            if (is_array($regions) && isset($regions[$id_lang]) && !empty($regions[$id_lang])) {
                $view_params['messaging']['customer_thread']['labels'][$id_lang] = sprintf('Amazon-%s', AmazonTools::ucfirst($regions[$id_lang]));
                $labels_count++;
            }
        }
        $view_params['messaging']['customer_thread']['labels_count'] = $labels_count;
        $view_params['messaging']['customer_thread']['active'] = isset($mail_customer_thread['active']) ? $mail_customer_thread['active'] : false;
        $view_params['messaging']['customer_thread']['login'] = isset($mail_customer_thread['login']) ? $mail_customer_thread['login'] : null;
        $view_params['messaging']['customer_thread']['password'] = isset($mail_customer_thread['password']) ? $mail_customer_thread['password'] : null;

        $view_params['messaging']['customer_thread']['template'] = isset($mail_customer_thread['template']) ? $mail_customer_thread['template'] : 'reply_msg';
        $view_params['messaging']['customer_thread']['mail_provider'] = isset($mail_customer_thread['mail_provider']) ? $mail_customer_thread['mail_provider'] : $default_email_provider;
        $view_params['messaging']['customer_thread']['mail_providers'] = $email_providers;
        $view_params['messaging']['customer_thread']['imap_open'] = function_exists('imap_open');
    }

    private function _tools(&$view_params)
    {
        $view_params['tools']['request_uri'] = Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']);
        $view_params['tools']['selected_tab'] = $this->selectedTab() === 'tools' ? true : false;
        $view_params['tools']['images_url'] = $this->images;
        $view_params['tools']['url'] = $this->url;
        $view_params['tools']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_TOOLS);

        $context_key = AmazonContext::getKey($this->context->shop);

        $view_params['tools']['tools_url'] = $this->url.'functions/tools.php?instant_token='.$this->config['instant_token'].'&context_key='.$context_key;

        // Current Queue
        //
        $languages = AmazonTools::languages();
        $action_queues = AmazonProduct::getCurrentQueue();

        if (is_array($action_queues) && count($action_queues)) {
            foreach ($action_queues as $key => $action_queue) {
                if (isset($languages[$action_queue['id_lang']])) {
                    $action_queues[$key]['lang'] = $languages[$action_queue['id_lang']]['name'];
                    $action_queues[$key]['lang_iso_code'] = $languages[$action_queue['id_lang']]['iso_code'];

                    $action_queues[$key]['flag'] = $this->images.'geo_flags/'.$this->geoFlag($action_queue['id_lang']).'.gif';
                } else {
                    $action_queues[$key]['lang'] = $this->l('Inactive');
                    $action_queues[$key]['flag'] = null;
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
        } elseif ($action_queues === false) {
            $view_params['tools']['action_queue_missing'] = true;
        }

        $view_params['tools']['action_queue'] = $action_queues;

        $view_params['tools']['valid_values'] = true;

        if ($this->config['features']['creation']) {
            if (AmazonValidValues::tableExists()) {
                $view_params['tools']['valid_values_action'] = $this->l('Refresh');

                $last_import = AmazonValidValues::lastImport();

                if ($last_import) {
                    $view_params['tools']['valid_values_last_import'] = sprintf('%s: %s', $this->l('Last Import'), AmazonTools::displayDate($last_import, $this->id_lang, true));
                } else {
                    $view_params['tools']['valid_values_last_import'] = sprintf('%s: %s', $this->l('Last Import'), $this->l('Never'));
                }
            } else {
                $view_params['tools']['valid_values_action'] = $this->l('Import');
            }
        }


        $view_params['tools']['maintenance'] = true;
    }

    private function _fba(&$view_params)
    {
        $view_params['fba'] = array();
        $view_params['fba']['selected_tab'] = $this->selectedTab() === 'fba' ? true : false;
        $view_params['fba']['images_url'] = $this->images;
        $view_params['fba']['validation'] = $this->_validate(true);
        $view_params['fba']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_FBA);
        $view_params['fba']['init_stock_url'] = $this->url.'functions/fba_inventory.php?instant_token='.$this->config['instant_token'].'&context_key='.AmazonContext::getKey($this->context->shop);

        // Order State
        //
        $orderStates = OrderState::getOrderStates($this->id_lang);

        $view_params['fba']['order_states'] = array();

        $c = 0;
        foreach ($orderStates as $orderState) {
            if (!(int)$orderState['id_order_state']) {
                continue;
            }

            $view_params['fba']['order_states'][$c]['value'] = (int)$orderState['id_order_state'];
            $view_params['fba']['order_states'][$c]['name'] = $orderState['name'];
            $c++;
        }

        $view_params['fba']['formula'] = $this->config['fba_formula'];
        $view_params['fba']['multichannel'] = $this->config['fba_multichannel'];
        $view_params['fba']['multichannel_auto'] = $this->config['fba_multichannel_auto'];
        $view_params['fba']['order_state'] = $this->config['fba_order_state'];
        $view_params['fba']['multichannel_state'] = $this->config['fba_multichannel_state'];
        $view_params['fba']['multichannel_sent_state'] = $this->config['fba_multichannel_sent_state'];
        $view_params['fba']['multichannel_done_state'] = $this->config['fba_multichannel_done_state'];
        $view_params['fba']['decrease_stock'] = $this->config['fba_decrease_stock'];

        $view_params['fba']['stock_behaviour'] = $this->config['fba_stock_behaviour'];
        $view_params['fba']['stock_behaviour_synch'] = self::FBA_STOCK_SYNCH;
        $view_params['fba']['stock_behaviour_switch'] = self::FBA_STOCK_SWITCH;

        $view_params['fba']['notification'] = $this->config['fba_notification'];
        $view_params['fba']['notification_both'] = self::FBA_NOTIFICATION_BOTH;
        $view_params['fba']['notification_shop'] = self::FBA_NOTIFICATION_SHOP;
        $view_params['fba']['notification_customer'] = self::FBA_NOTIFICATION_CUSTOMER;

        

        if ($this->config['fba_stock_behaviour'] == self::FBA_STOCK_SYNCH) {
            $view_params['fba']['stock_init'] = array();
            $view_params['fba']['stock_init']['enabled'] = true;

            $view_params['fba']['marketplaces'] = array();
            $view_params['fba']['marketplaces']['countries'] = $marketplace_countries = AmazonSpecificField::countrySelector($this->amazon_features['amazon_europe']);
            $view_params['fba']['marketplaces']['show'] = is_array($marketplace_countries) && count($marketplace_countries) > 1;
        } else {
            $view_params['fba']['stock_init']['enabled'] = false;
            $view_params['fba']['stock_init']['groups'] = array();
        }
    }


    private function _repricing(&$view_params)
    {
        $view_params['repricing'] = array();
        $view_params['repricing']['selected_tab'] = $this->selectedTab() === 'repricing' ? true : false;
        $view_params['repricing']['images_url'] = $this->images;
        $view_params['repricing']['module_path'] = $this->path;
        $view_params['repricing']['validation'] = $this->_validate(true);
        $view_params['repricing']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_REPRICING);
        $view_params['repricing']['repricing_url'] = $this->url.'functions/repricing.php?instant_token='.$this->config['instant_token'].'&context_key='.AmazonContext::getKey($this->context->shop);

        $view_params['repricing']['method'] = array();
        $view_params['repricing']['method']['wholesale'] = AmazonRepricing::REPRICING_WHOLESALE_PRICE;
        $view_params['repricing']['method']['regular'] = AmazonRepricing::REPRICING_REGULAR_PRICE;

        $repricing = &$this->config['repricing'];

        if (!is_array($repricing)) {
            $repricing = array();
        }

        $view_params['repricing']['awsKeyId'] = isset($repricing['awsKeyId']) ? $repricing['awsKeyId'] : null;
        $view_params['repricing']['awsSecretKey'] = isset($repricing['awsSecretKey']) ? $repricing['awsSecretKey'] : null;

        if (!Tools::strlen(trim($view_params['repricing']['awsKeyId']))) {
            $view_params['repricing']['awsKeyId_required'] = true;
        } else {
            $view_params['repricing']['awsKeyId_required'] = false;
        }

        if (!Tools::strlen(trim($view_params['repricing']['awsKeyId']))) {
            $view_params['repricing']['awsSecretKey_required'] = true;
        } else {
            $view_params['repricing']['awsSecretKey_required'] = false;
        }

        $view_params['repricing']['marketplaces'] = array();
        $view_params['repricing']['marketplaces']['countries'] = $marketplace_countries = AmazonSpecificField::countrySelector();
        $view_params['repricing']['marketplaces']['show'] = is_array($marketplace_countries) && count($marketplace_countries) > 1;

        $model_strategy = array_fill_keys(array(
            'name',
            'active',
            'key',
            'agressivity',
            'delta_min',
            'delta_max',
            'limit',
            'base',
            'show',
            'master'
        ), null);
        $empty_strategy = $model_strategy;
        $empty_strategy['key'] = '_key_';
        $empty_strategy['show'] = false;
        $empty_strategy['active'] = true;
        $empty_strategy['master'] = true;
        $empty_strategy['base'] = 1;

        $view_params['repricing']['strategies']['empty'] = $empty_strategy;

        foreach (AmazonTools::languages() as $language) {
            $id_lang = $language['id_lang'];

            if (!(isset($this->config['actives'][$id_lang]) && (int)$this->config['actives'][$id_lang])) {
                continue;
            }
            $view_params['repricing']['strategies']['strategy'][$id_lang] = $this->getStrategies($id_lang);
        }
    }

    protected function getStrategies($id_lang)
    {
        static $strategies = null;
        static $default_strategies = null;

        $strategies_table = array();
        $model_strategy = array_fill_keys(array(
            'name',
            'active',
            'key',
            'agressivity',
            'delta_min',
            'delta_max',
            'limit',
            'base',
            'show',
            'master'
        ), null);

        if ($strategies === null) {
            $strategies = AmazonConfiguration::get('strategies');
            $default_strategies = AmazonConfiguration::get('default_strategies');
        }

        if (!is_array($strategies) || !count($strategies)) {
            return ($strategies_table);
        }

        $strategies_have_names = is_array($strategies) && isset($strategies['name']) && is_array($strategies['name']) && count($strategies['name']);
        $strategies_have_items = $strategies_have_names && isset($strategies['name'][$id_lang]) && is_array($strategies['name'][$id_lang]) && count($strategies['name'][$id_lang]);

        if ($strategies_have_items) {
            $has_active = isset($strategies['active']) && is_array($strategies['active']) && isset($strategies['active'][$id_lang]) && is_array($strategies['active'][$id_lang]);
            $has_agressivity = isset($strategies['agressivity']) && is_array($strategies['agressivity']) && isset($strategies['agressivity'][$id_lang]) && is_array($strategies['agressivity'][$id_lang]);
            $has_base = isset($strategies['base']) && is_array($strategies['base']) && isset($strategies['base'][$id_lang]) && is_array($strategies['base'][$id_lang]);
            $has_limit = isset($strategies['limit']) && is_array($strategies['limit']) && isset($strategies['limit'][$id_lang]) && is_array($strategies['limit'][$id_lang]);
            $has_delta_min = isset($strategies['delta_min']) && is_array($strategies['delta_min']) && isset($strategies['delta_min'][$id_lang]) && is_array($strategies['delta_min'][$id_lang]);
            $has_delta_max = isset($strategies['delta_max']) && is_array($strategies['delta_max']) && isset($strategies['delta_max'][$id_lang]) && is_array($strategies['delta_max'][$id_lang]);


            foreach ($strategies['name'][$id_lang] as $index => $strategy_name) {
                $strategy = $model_strategy;
                $strategy['show'] = true;
                $strategy['name'] = $strategy_name;
                $strategy['key'] = $key = AmazonTools::toKey($strategy_name);
                $strategy['active'] = $has_active && isset($strategies['active'][$id_lang][$index]) ? $strategies['active'][$id_lang][$index] : null;
                $strategy['agressivity'] = $has_agressivity && isset($strategies['agressivity'][$id_lang][$index]) ? $strategies['agressivity'][$id_lang][$index] : null;
                $strategy['base'] = $has_base && isset($strategies['base'][$id_lang][$index]) ? $strategies['base'][$id_lang][$index] : null;
                $strategy['limit'] = $has_limit && isset($strategies['limit'][$id_lang][$index]) ? $strategies['limit'][$id_lang][$index] : null;
                $strategy['delta_min'] = $has_delta_min && isset($strategies['delta_min'][$id_lang][$index]) ? $strategies['delta_min'][$id_lang][$index] : null;
                $strategy['delta_max'] = $has_delta_max && isset($strategies['delta_max'][$id_lang][$index]) ? $strategies['delta_max'][$id_lang][$index] : null;

                if (is_array($default_strategies) && count($default_strategies) && isset($default_strategies[$id_lang]) && !empty($default_strategies[$id_lang]) && $strategy['key'] == $default_strategies[$id_lang]) {
                    $strategy['default'] = true;
                } else {
                    $strategy['default'] = false;
                }

                $strategies_table[$key] = $strategy;
            }
        }

        return ($strategies_table);
    }

    private function _cron(&$view_params)
    {
        $view_params['cron'] = array();
        $view_params['cron']['selected_tab'] = $this->selectedTab() === 'cron' ? true : false;
        $view_params['cron']['images_url'] = $this->images;
        $view_params['cron']['url'] = $this->url;
        $view_params['cron']['validation'] = $this->_validate(true);
        $view_params['cron']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_CRON);
        $view_params['cron']['display'] = false;

        $base_url = AmazonTools::getHttpHost(true, true).__PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/'.$this->name;
        $marketPlaceIds = $this->config['amazon_marketplace_ids'];
        $marketPlaceMaster = $this->config['marketplace_master'];
        $actives = $this->config['actives'];
        $regions = $this->config['regions'];

        $view_params['cron']['prestashop'] = array();
        $view_params['cron']['prestashop']['exists'] = is_dir(_PS_MODULE_DIR_.'cronjobs/');
        $view_params['cron']['prestashop']['installed'] = (bool)AmazonTools::moduleIsInstalled('cronjobs');

        $view_params['cron']['products'] = array();
        $view_params['cron']['products']['synch'] = array();

        $view_params['cron']['orders'] = array();
        $view_params['cron']['orders']['status'] = array();
        $view_params['cron']['orders']['import'] = array();
        $view_params['cron']['orders']['canceled'] = array();
        $view_params['cron']['orders']['report'] = array();

        $view_params['cron']['fba']['status'] = array();
        $view_params['cron']['fba']['cancel'] = array();
        $view_params['cron']['fba']['stocks'] = array();

        $view_params['cron']['repricing'] = array();
        $view_params['cron']['repricing']['reprice'] = array();
        $view_params['cron']['repricing']['update'] = array();
        $view_params['cron']['repricing']['export'] = array();

        $view_params['cron']['messaging'] = array();
        $view_params['cron']['messaging']['grab'] = array();

        $languages = AmazonTools::languages();

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $context_key = AmazonContext::getKey($this->context->shop);
            $context_param = '&context_key='.$context_key;
        } else {
            $context_param = null;
        }
        $view_params['cron']['context_key'] = $context_key;

        // Cron for Amazon Europe
        //
        if (is_array($marketPlaceIds) && count($marketPlaceIds)) {
            foreach ($languages as $langkey => $language) {
                $id_lang = $language['id_lang'];

                if (!(isset($actives[$id_lang]) && (int)$actives[$id_lang])) {
                    continue;
                }

                if (!isset($marketPlaceIds[$id_lang]) || !AmazonTools::isEuropeMarketplaceId($marketPlaceIds[$id_lang])) {
                    continue;
                }

                $view_params['cron']['display'] = true;

                $flag = $this->images.'geo_flags/'.$this->geoFlag($language['id_lang']).'.gif';
                $lang = $language['iso_code'];

                $params = '/functions/products.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&action=update&lang='.$regions[$id_lang];

                $view_params['cron']['products']['synch'][$id_lang]['id_lang'] = $id_lang;
                $view_params['cron']['products']['synch'][$id_lang]['lang'] = $regions[$id_lang];
                $view_params['cron']['products']['synch'][$id_lang]['flag'] = $flag;
                $view_params['cron']['products']['synch'][$id_lang]['lang'] = $lang;
                $view_params['cron']['products']['synch'][$id_lang]['url'] = $base_url.$params;
                $view_params['cron']['products']['synch'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                $view_params['cron']['products']['synch'][$id_lang]['title'] = $this->l('Synchronization');
                $view_params['cron']['products']['synch'][$id_lang]['frequency'] = -1;

                $europe = ($marketPlaceMaster === $regions[$id_lang] || $marketPlaceMaster === 'uk');
                $pass = !$europe && $marketPlaceMaster;

                if (isset($regions[$id_lang]) && Tools::strlen($regions[$id_lang]) && !$pass) {
                    $params = '/functions/import.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang].'&europe='.$europe;
                    $view_params['cron']['orders']['import'][$id_lang]['id_lang'] = $id_lang;
                    $view_params['cron']['orders']['import'][$id_lang]['lang'] = $regions[$id_lang];
                    $view_params['cron']['orders']['import'][$id_lang]['flag'] = $flag;
                    $view_params['cron']['orders']['import'][$id_lang]['lang'] = $lang;
                    $view_params['cron']['orders']['import'][$id_lang]['url'] = $base_url.$params;
                    $view_params['cron']['orders']['import'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                    $view_params['cron']['orders']['import'][$id_lang]['title'] = $this->l('Orders Import');
                    $view_params['cron']['orders']['import'][$id_lang]['frequency'] = -1;

                    $params = '/functions/status.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang].'&europe='.$europe;
                    $view_params['cron']['orders']['status'][$id_lang]['id_lang'] = $id_lang;
                    $view_params['cron']['orders']['status'][$id_lang]['lang'] = $regions[$id_lang];
                    $view_params['cron']['orders']['status'][$id_lang]['flag'] = $flag;
                    $view_params['cron']['orders']['status'][$id_lang]['lang'] = $lang;
                    $view_params['cron']['orders']['status'][$id_lang]['url'] = $base_url.$params;
                    $view_params['cron']['orders']['status'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                    $view_params['cron']['orders']['status'][$id_lang]['title'] = $this->l('Orders Status');
                    $view_params['cron']['orders']['status'][$id_lang]['frequency'] = 4;

                    if (isset($this->config['canceled_state']) && (int)$this->config['canceled_state'] && $this->amazon_features['cancel_orders']) {
                        $params = '/functions/canceled.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang].'&europe='.$europe;
                        $view_params['cron']['orders']['canceled'][$id_lang]['id_lang'] = $id_lang;
                        $view_params['cron']['orders']['canceled'][$id_lang]['lang'] = $regions[$id_lang];
                        $view_params['cron']['orders']['canceled'][$id_lang]['flag'] = $flag;
                        $view_params['cron']['orders']['canceled'][$id_lang]['lang'] = $lang;
                        $view_params['cron']['orders']['canceled'][$id_lang]['url'] = $base_url.$params;
                        $view_params['cron']['orders']['canceled'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                        $view_params['cron']['orders']['canceled'][$id_lang]['title'] = $this->l('Canceled Orders');
                        $view_params['cron']['orders']['canceled'][$id_lang]['frequency'] = 2;
                    }

                    if ($this->config['fba_multichannel']) {
                        $params = '/functions/fbaorder.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&action=status&europe='.$europe;
                        $view_params['cron']['fba']['status'][$id_lang]['id_lang'] = $id_lang;
                        $view_params['cron']['fba']['status'][$id_lang]['lang'] = $regions[$id_lang];
                        $view_params['cron']['fba']['status'][$id_lang]['flag'] = $flag;
                        $view_params['cron']['fba']['status'][$id_lang]['lang'] = $lang;
                        $view_params['cron']['fba']['status'][$id_lang]['url'] = $base_url.$params;
                        $view_params['cron']['fba']['status'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                        $view_params['cron']['fba']['status'][$id_lang]['title'] = $this->l('FBA Orders Status');
                        $view_params['cron']['fba']['status'][$id_lang]['frequency'] = 2;
                    }

                    if ($this->config['features']['fba']) {
                        $script = null;

                        if ($this->config['fba_stock_behaviour'] == self::FBA_STOCK_SWITCH) {
                            $script = 'fbamanager';
                        } elseif ($this->config['fba_stock_behaviour'] == self::FBA_STOCK_SYNCH) {
                            $script = 'fbastocksynch';
                        }
                        if ($script) {
                            $params = '/functions/'.$script.'.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&action=stocks&europe='.$europe;
                            $view_params['cron']['fba']['stocks'][$id_lang]['id_lang'] = $id_lang;
                            $view_params['cron']['fba']['stocks'][$id_lang]['lang'] = $regions[$id_lang];
                            $view_params['cron']['fba']['stocks'][$id_lang]['flag'] = $flag;
                            $view_params['cron']['fba']['stocks'][$id_lang]['lang'] = $lang;
                            $view_params['cron']['fba']['stocks'][$id_lang]['url'] = $base_url.$params;
                            $view_params['cron']['fba']['stocks'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                            $view_params['cron']['fba']['stocks'][$id_lang]['title'] = $this->l('FBA Manager');
                            $view_params['cron']['fba']['stocks'][$id_lang]['frequency'] = 2;
                        }
                    }

                    if ($this->config['features']['expert_mode']) {
                        $params = '/functions/check_stock.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang];
                        $view_params['cron']['products']['fetch'][$id_lang]['id_lang'] = $id_lang;
                        $view_params['cron']['products']['fetch'][$id_lang]['lang'] = $regions[$id_lang];
                        $view_params['cron']['products']['fetch'][$id_lang]['flag'] = $flag;
                        $view_params['cron']['products']['fetch'][$id_lang]['lang'] = $lang;
                        $view_params['cron']['products']['fetch'][$id_lang]['url'] = $base_url.$params;
                        $view_params['cron']['products']['fetch'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                        $view_params['cron']['products']['fetch'][$id_lang]['title'] = $this->l('Fix Stock Issues');
                        $view_params['cron']['products']['fetch'][$id_lang]['frequency'] = 0;

                        $params = '/functions/check_stock.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang].'&fix=1';
                        $view_params['cron']['products']['fix'][$id_lang]['id_lang'] = $id_lang;
                        $view_params['cron']['products']['fix'][$id_lang]['lang'] = $regions[$id_lang];
                        $view_params['cron']['products']['fix'][$id_lang]['flag'] = $flag;
                        $view_params['cron']['products']['fix'][$id_lang]['lang'] = $lang;
                        $view_params['cron']['products']['fix'][$id_lang]['url'] = $base_url.$params;
                        $view_params['cron']['products']['fix'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                        $view_params['cron']['products']['fix'][$id_lang]['title'] = $this->l('Fix Stock Issues');
                        $view_params['cron']['products']['fix'][$id_lang]['frequency'] = 0;
                    }

                    if ($this->config['features']['orders_reports']) {
                        $params = '/functions/orders_reports.php?amazon_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&amazon_lang='.$id_lang.'&action=report&europe='.$europe;
                        $view_params['cron']['orders']['report'][$id_lang]['id_lang'] = $id_lang;
                        $view_params['cron']['orders']['report'][$id_lang]['lang'] = $regions[$id_lang];
                        $view_params['cron']['orders']['report'][$id_lang]['flag'] = $flag;
                        $view_params['cron']['orders']['report'][$id_lang]['lang'] = $lang;
                        $view_params['cron']['orders']['report'][$id_lang]['url'] = $base_url.$params;
                        $view_params['cron']['orders']['report'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                        $view_params['cron']['orders']['report'][$id_lang]['title'] = $this->l('Orders Reports');
                        $view_params['cron']['orders']['report'][$id_lang]['frequency'] = 1;
                    }
                } elseif ($regions[$id_lang] === 'uk') {
                    // UK exception

                    $params = '/functions/import.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang];
                    $view_params['cron']['orders']['import'][$id_lang]['id_lang'] = $id_lang;
                    $view_params['cron']['orders']['import'][$id_lang]['lang'] = $regions[$id_lang];
                    $view_params['cron']['orders']['import'][$id_lang]['flag'] = $flag;
                    $view_params['cron']['orders']['import'][$id_lang]['url'] = $base_url.$params;
                    $view_params['cron']['orders']['import'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                    $view_params['cron']['orders']['import'][$id_lang]['title'] = $this->l('Orders Import');
                    $view_params['cron']['orders']['import'][$id_lang]['frequency'] = -1;

                    $params = '/functions/status.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang].'&europe=1';
                    $view_params['cron']['orders']['status'][$id_lang]['id_lang'] = $id_lang;
                    $view_params['cron']['orders']['status'][$id_lang]['lang'] = $regions[$id_lang];
                    $view_params['cron']['orders']['status'][$id_lang]['flag'] = $flag;
                    $view_params['cron']['orders']['status'][$id_lang]['lang'] = $lang;
                    $view_params['cron']['orders']['status'][$id_lang]['url'] = $base_url.$params;
                    $view_params['cron']['orders']['status'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                    $view_params['cron']['orders']['status'][$id_lang]['title'] = $this->l('Orders Status');
                    $view_params['cron']['orders']['status'][$id_lang]['frequency'] = 4;


                    if ($this->config['features']['expert_mode']) {
                        $params = '/functions/check_stock.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang];
                        $view_params['cron']['products']['fetch'][$id_lang]['id_lang'] = $id_lang;
                        $view_params['cron']['products']['fetch'][$id_lang]['lang'] = $regions[$id_lang];
                        $view_params['cron']['products']['fetch'][$id_lang]['flag'] = $flag;
                        $view_params['cron']['products']['fetch'][$id_lang]['lang'] = $lang;
                        $view_params['cron']['products']['fetch'][$id_lang]['url'] = $base_url.$params;
                        $view_params['cron']['products']['fetch'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                        $view_params['cron']['products']['fetch'][$id_lang]['title'] = $this->l('Fix Stock Issues');
                        $view_params['cron']['products']['fetch'][$id_lang]['frequency'] = 0;

                        $params = '/functions/check_stock.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang].'&fix=1';
                        $view_params['cron']['products']['fix'][$id_lang]['id_lang'] = $id_lang;
                        $view_params['cron']['products']['fix'][$id_lang]['lang'] = $regions[$id_lang];
                        $view_params['cron']['products']['fix'][$id_lang]['flag'] = $flag;
                        $view_params['cron']['products']['fix'][$id_lang]['lang'] = $lang;
                        $view_params['cron']['products']['fix'][$id_lang]['url'] = $base_url.$params;
                        $view_params['cron']['products']['fix'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                        $view_params['cron']['products']['fix'][$id_lang]['title'] = $this->l('Fix Stock Issues');
                        $view_params['cron']['products']['fix'][$id_lang]['frequency'] = 0;
                    }

                    if ($this->config['features']['orders_reports']) {
                        $params = '/functions/orders_reports.php?amazon_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&amazon_lang='.$id_lang.'&action=report';
                        $view_params['cron']['orders']['report'][$id_lang]['id_lang'] = $id_lang;
                        $view_params['cron']['orders']['report'][$id_lang]['lang'] = $regions[$id_lang];
                        $view_params['cron']['orders']['report'][$id_lang]['flag'] = $flag;
                        $view_params['cron']['orders']['report'][$id_lang]['lang'] = $lang;
                        $view_params['cron']['orders']['report'][$id_lang]['url'] = $base_url.$params;
                        $view_params['cron']['orders']['report'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                        $view_params['cron']['orders']['report'][$id_lang]['title'] = $this->l('Orders Reports');
                        $view_params['cron']['orders']['report'][$id_lang]['frequency'] = 1;
                    }
                }

                if ($this->config['features']['repricing']) {
                    $params = '/functions/repricing.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&action=reprice&lang='.$regions[$id_lang];
                    $view_params['cron']['repricing']['reprice'][$id_lang]['id_lang'] = $id_lang;
                    $view_params['cron']['repricing']['reprice'][$id_lang]['lang'] = $regions[$id_lang];
                    $view_params['cron']['repricing']['reprice'][$id_lang]['flag'] = $flag;
                    $view_params['cron']['repricing']['reprice'][$id_lang]['lang'] = $lang;
                    $view_params['cron']['repricing']['reprice'][$id_lang]['url'] = $base_url.$params;
                    $view_params['cron']['repricing']['reprice'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                    $view_params['cron']['repricing']['reprice'][$id_lang]['title'] = $this->l('Repricing (Analysis)');
                    $view_params['cron']['repricing']['reprice'][$id_lang]['frequency'] = -1;

                    $params = '/functions/repricing.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&action=push&lang='.$regions[$id_lang];
                    $view_params['cron']['repricing']['update'][$id_lang]['id_lang'] = $id_lang;
                    $view_params['cron']['repricing']['update'][$id_lang]['lang'] = $regions[$id_lang];
                    $view_params['cron']['repricing']['update'][$id_lang]['flag'] = $flag;
                    $view_params['cron']['repricing']['update'][$id_lang]['lang'] = $lang;
                    $view_params['cron']['repricing']['update'][$id_lang]['url'] = $base_url.$params;
                    $view_params['cron']['repricing']['update'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                    $view_params['cron']['repricing']['update'][$id_lang]['title'] = $this->l('Repricing (Updates)');
                    $view_params['cron']['repricing']['update'][$id_lang]['frequency'] = -1;

                    $params = '/functions/repricing.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&action=export&lang='.$regions[$id_lang];
                    $view_params['cron']['repricing']['export'][$id_lang]['id_lang'] = $id_lang;
                    $view_params['cron']['repricing']['export'][$id_lang]['lang'] = $regions[$id_lang];
                    $view_params['cron']['repricing']['export'][$id_lang]['flag'] = $flag;
                    $view_params['cron']['repricing']['export'][$id_lang]['lang'] = $lang;
                    $view_params['cron']['repricing']['export'][$id_lang]['url'] = $base_url.$params;
                    $view_params['cron']['repricing']['export'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                    $view_params['cron']['repricing']['export'][$id_lang]['title'] = $this->l('Repricing (Export)');
                    $view_params['cron']['repricing']['export'][$id_lang]['frequency'] = -1;
                }

                if ($this->customerThreadIsActive()) {
                    $params = '/functions/imap.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang];
                    $view_params['cron']['messaging']['grab'][$id_lang]['id_lang'] = $id_lang;
                    $view_params['cron']['messaging']['grab'][$id_lang]['flag'] = $flag;
                    $view_params['cron']['messaging']['grab'][$id_lang]['lang'] = $regions[$id_lang];
                    ;
                    $view_params['cron']['messaging']['grab'][$id_lang]['url'] = $base_url.$params;
                    $view_params['cron']['messaging']['grab'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                    $view_params['cron']['messaging']['grab'][$id_lang]['title'] = $this->l('Messaging');
                    $view_params['cron']['messaging']['grab'][$id_lang]['frequency'] = -1;
                }

                if (!$this->amazon_features['orders']) {
                    unset($view_params['cron']['orders']);
                }

                unset($languages[$langkey]);
            }
        }

        // Remaining platforms (except Europe)
        //
        foreach ($languages as $langkey => $language) {
            $id_lang = $language['id_lang'];

            if (!isset($regions[$id_lang])) {
                continue;
            }
            if (!(isset($actives[$id_lang]) && (int)$actives[$id_lang])) {
                continue;
            }

            if (AmazonTools::isEuropeMarketplaceId($marketPlaceIds[$id_lang]) || empty($marketPlaceIds[$id_lang])) {
                continue;
            }

            $view_params['cron']['display'] = true;

            $flag = $this->images.'geo_flags/'.$this->geoFlag($language['id_lang']).'.gif';
            $lang = $language['iso_code'];

            $params = '/functions/products.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&action=update&lang='.$regions[$id_lang];
            $view_params['cron']['products']['synch'][$id_lang]['id_lang'] = $id_lang;
            $view_params['cron']['products']['synch'][$id_lang]['lang'] = $regions[$id_lang];
            $view_params['cron']['products']['synch'][$id_lang]['flag'] = $flag;
            $view_params['cron']['products']['synch'][$id_lang]['lang'] = $lang;
            $view_params['cron']['products']['synch'][$id_lang]['url'] = $base_url.$params;
            $view_params['cron']['products']['synch'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
            $view_params['cron']['products']['synch'][$id_lang]['title'] = $this->l('Synchronization');
            $view_params['cron']['products']['synch'][$id_lang]['frequency'] = 1;

            $params = '/functions/import.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang];
            $view_params['cron']['orders']['import'][$id_lang]['id_lang'] = $id_lang;
            $view_params['cron']['orders']['import'][$id_lang]['lang'] = $regions[$id_lang];
            $view_params['cron']['orders']['import'][$id_lang]['flag'] = $flag;
            $view_params['cron']['orders']['import'][$id_lang]['lang'] = $lang;
            $view_params['cron']['orders']['import'][$id_lang]['url'] = $base_url.$params;
            $view_params['cron']['orders']['import'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
            $view_params['cron']['orders']['import'][$id_lang]['title'] = $this->l('Orders Import');
            $view_params['cron']['orders']['import'][$id_lang]['frequency'] = 1;

            $params = '/functions/status.php?&cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang];
            $view_params['cron']['orders']['status'][$id_lang]['id_lang'] = $id_lang;
            $view_params['cron']['orders']['status'][$id_lang]['lang'] = $regions[$id_lang];
            $view_params['cron']['orders']['status'][$id_lang]['flag'] = $flag;
            $view_params['cron']['orders']['status'][$id_lang]['lang'] = $lang;
            $view_params['cron']['orders']['status'][$id_lang]['url'] = $base_url.$params;
            $view_params['cron']['orders']['status'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
            $view_params['cron']['orders']['status'][$id_lang]['title'] = $this->l('Orders Status');
            $view_params['cron']['orders']['status'][$id_lang]['frequency'] = 4;

            if (isset($this->config['canceled_state']) && (int)$this->config['canceled_state'] && $this->amazon_features['cancel_orders']) {
                $params = '/functions/canceled.php?&cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang];
                $view_params['cron']['orders']['canceled'][$id_lang]['id_lang'] = $id_lang;
                $view_params['cron']['orders']['canceled'][$id_lang]['lang'] = $regions[$id_lang];
                $view_params['cron']['orders']['canceled'][$id_lang]['flag'] = $flag;
                $view_params['cron']['orders']['canceled'][$id_lang]['lang'] = $lang;
                $view_params['cron']['orders']['canceled'][$id_lang]['url'] = $base_url.$params;
                $view_params['cron']['orders']['canceled'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                $view_params['cron']['orders']['canceled'][$id_lang]['title'] = $this->l('Canceled Orders');
                $view_params['cron']['orders']['canceled'][$id_lang]['frequency'] = 2;
            }

            if ($this->config['fba_multichannel']) {
                $params = '/functions/fbaorder.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang].'&action=status';
                $view_params['cron']['fba']['status'][$id_lang]['id_lang'] = $id_lang;
                $view_params['cron']['fba']['status'][$id_lang]['lang'] = $regions[$id_lang];
                $view_params['cron']['fba']['status'][$id_lang]['flag'] = $flag;
                $view_params['cron']['fba']['status'][$id_lang]['lang'] = $lang;
                $view_params['cron']['fba']['status'][$id_lang]['url'] = $base_url.$params;
                $view_params['cron']['fba']['status'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                $view_params['cron']['fba']['status'][$id_lang]['title'] = $this->l('FBA Orders Status');
                $view_params['cron']['fba']['status'][$id_lang]['frequency'] = 2;
            }

            if ($this->config['features']['fba']) {
                $script = null;

                if ($this->config['fba_stock_behaviour'] == self::FBA_STOCK_SWITCH) {
                    $script = 'fbamanager';
                } elseif ($this->config['fba_stock_behaviour'] == self::FBA_STOCK_SYNCH) {
                    $script = 'fbastocksynch';
                }

                if ($script) {
                    $params = '/functions/'.$script.'.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang].'&action=stocks';
                    $view_params['cron']['fba']['stocks'][$id_lang]['id_lang'] = $id_lang;
                    $view_params['cron']['fba']['stocks'][$id_lang]['lang'] = $regions[$id_lang];
                    $view_params['cron']['fba']['stocks'][$id_lang]['flag'] = $flag;
                    $view_params['cron']['fba']['stocks'][$id_lang]['lang'] = $lang;
                    $view_params['cron']['fba']['stocks'][$id_lang]['url'] = $base_url.$params;
                    $view_params['cron']['fba']['stocks'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                    $view_params['cron']['fba']['stocks'][$id_lang]['title'] = $this->l('FBA Manager');
                    $view_params['cron']['fba']['stocks'][$id_lang]['frequency'] = 2;
                }
            }


            if ($this->config['features']['repricing']) {
                $params = '/functions/repricing.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&action=reprice&lang='.$regions[$id_lang];
                $view_params['cron']['repricing']['reprice'][$id_lang]['id_lang'] = $id_lang;
                $view_params['cron']['repricing']['reprice'][$id_lang]['lang'] = $regions[$id_lang];
                $view_params['cron']['repricing']['reprice'][$id_lang]['flag'] = $flag;
                $view_params['cron']['repricing']['reprice'][$id_lang]['lang'] = $lang;
                $view_params['cron']['repricing']['reprice'][$id_lang]['url'] = $base_url.$params;
                $view_params['cron']['repricing']['reprice'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                $view_params['cron']['repricing']['reprice'][$id_lang]['title'] = $this->l('Repricing (Analysis)');
                $view_params['cron']['repricing']['reprice'][$id_lang]['frequency'] = 1;

                $params = '/functions/repricing.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&action=push&lang='.$regions[$id_lang];
                $view_params['cron']['repricing']['update'][$id_lang]['id_lang'] = $id_lang;
                $view_params['cron']['repricing']['update'][$id_lang]['lang'] = $regions[$id_lang];
                $view_params['cron']['repricing']['update'][$id_lang]['flag'] = $flag;
                $view_params['cron']['repricing']['update'][$id_lang]['lang'] = $lang;
                $view_params['cron']['repricing']['update'][$id_lang]['url'] = $base_url.$params;
                $view_params['cron']['repricing']['update'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                $view_params['cron']['repricing']['update'][$id_lang]['title'] = $this->l('Repricing (Updates)');
                $view_params['cron']['repricing']['update'][$id_lang]['frequency'] = -1;

                $params = '/functions/repricing.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&action=export&lang='.$regions[$id_lang];
                $view_params['cron']['repricing']['export'][$id_lang]['id_lang'] = $id_lang;
                $view_params['cron']['repricing']['export'][$id_lang]['lang'] = $regions[$id_lang];
                $view_params['cron']['repricing']['export'][$id_lang]['flag'] = $flag;
                $view_params['cron']['repricing']['export'][$id_lang]['lang'] = $lang;
                $view_params['cron']['repricing']['export'][$id_lang]['url'] = $base_url.$params;
                $view_params['cron']['repricing']['export'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                $view_params['cron']['repricing']['export'][$id_lang]['title'] = $this->l('Repricing (Export)');
                $view_params['cron']['repricing']['export'][$id_lang]['frequency'] = 1;
            }

            if ($this->customerThreadIsActive()) {
                $params = '/functions/imap.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang];
                $view_params['cron']['messaging']['grab'][$id_lang]['id_lang'] = $id_lang;
                $view_params['cron']['messaging']['grab'][$id_lang]['lang'] = $lang;
                $view_params['cron']['messaging']['grab'][$id_lang]['flag'] = $flag;
                $view_params['cron']['messaging']['grab'][$id_lang]['url'] = $base_url.$params;
                $view_params['cron']['messaging']['grab'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                $view_params['cron']['messaging']['grab'][$id_lang]['title'] = $this->l('Messaging');
                $view_params['cron']['messaging']['grab'][$id_lang]['frequency'] = -1;
            }

            if ($this->config['features']['expert_mode']) {
                $params = '/functions/check_stock.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang];
                $view_params['cron']['products']['fetch'][$id_lang]['id_lang'] = $id_lang;
                $view_params['cron']['products']['fetch'][$id_lang]['lang'] = $regions[$id_lang];
                $view_params['cron']['products']['fetch'][$id_lang]['flag'] = $flag;
                $view_params['cron']['products']['fetch'][$id_lang]['lang'] = $lang;
                $view_params['cron']['products']['fetch'][$id_lang]['url'] = $base_url.$params;
                $view_params['cron']['products']['fetch'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                $view_params['cron']['products']['fetch'][$id_lang]['title'] = $this->l('Fix Stock Issues');
                $view_params['cron']['products']['fetch'][$id_lang]['frequency'] = 0;

                $params = '/functions/check_stock.php?cron_token='.$this->config['tokens'][$id_lang].$context_param.'&cron=1&lang='.$regions[$id_lang].'&fix=1';
                $view_params['cron']['products']['fix'][$id_lang]['id_lang'] = $id_lang;
                $view_params['cron']['products']['fix'][$id_lang]['lang'] = $regions[$id_lang];
                $view_params['cron']['products']['fix'][$id_lang]['flag'] = $flag;
                $view_params['cron']['products']['fix'][$id_lang]['lang'] = $lang;
                $view_params['cron']['products']['fix'][$id_lang]['url'] = $base_url.$params;
                $view_params['cron']['products']['fix'][$id_lang]['short_url'] = preg_replace('/(?<=^.{64}).{4,}(?=.{24}$)/', '...', $base_url.$params);
                $view_params['cron']['products']['fix'][$id_lang]['title'] = $this->l('Fix Stock Issues');
                $view_params['cron']['products']['fix'][$id_lang]['frequency'] = 0;
            }
        }
    }


    protected static function loadAttributes($inactives = false)
    {
        $actives = AmazonConfiguration::get('ACTIVE');
        $languages = AmazonTools::languages();

        self::$attributes_groups = array();
        self::$attributes = array();

        if (!AmazonConfiguration::combinationIsFeatureActive()) {
            return;
        }

        foreach ($languages as $language) {
            $id_lang = $language['id_lang'];

            if (!$inactives && !(isset($actives) || !(isset($actives[$id_lang]) || !(int)$actives[$id_lang]))) {
                continue;
            }

            $attributes_groups = AttributeGroup::getAttributesGroups($id_lang);

            if (is_array($attributes_groups) && count($attributes_groups)) {
                self::$attributes_groups[$id_lang] = array();

                foreach ($attributes_groups as $attribute_group) {
                    $id_attribute_group = (int)$attribute_group['id_attribute_group'];

                    self::$attributes_groups[$id_lang][$id_attribute_group] = $attribute_group;
                }
            } else {
                self::$attributes_groups[$id_lang] = array();
            }

            $attributes = Attribute::getAttributes($id_lang, true);

            if (is_array($attributes) && count($attributes)) {
                self::$attributes[$id_lang] = array();

                foreach ($attributes as $attribute) {
                    $id_attribute_group = (int)$attribute['id_attribute_group'];
                    $id_attribute = (int)$attribute['id_attribute'];

                    self::$attributes[$id_lang][$id_attribute_group][$id_attribute] = $attribute;
                }
            } else {
                self::$attributes[$id_lang] = array();
            }
        }
    }

    protected static function loadFeatures($inactives = false, $custom = false)
    {
        $features = array();

        if (!AmazonConfiguration::featureIsFeatureActive()) {
            return;
        }
        $actives = AmazonConfiguration::get('ACTIVE');
        $languages = AmazonTools::languages();

        foreach ($languages as $language) {
            $id_lang = $language['id_lang'];

            if (!$inactives && !(isset($actives) || !(isset($actives[$id_lang]) || !(int)$actives[$id_lang]))) {
                continue;
            }

            $features = Feature::getFeatures($id_lang);

            if (is_array($features) && count($features)) {
                foreach ($features as $feature) {
                    $id_feature = (int)$feature['id_feature'];

                    $features_values = FeatureValue::getFeatureValuesWithLang($id_lang, $id_feature, $custom);

                    if (is_array($features_values) && count($features_values)) {
                        $feature['is_color_feature'] = false; // Used by Profiles and Mapping

                        self::$features[$id_lang][$id_feature] = $feature;

                        foreach ($features_values as $feature_value) {
                            $feature_value['name'] = $feature['name'];
                            self::$features_values[$id_lang][$id_feature][$feature_value['id_feature_value']] = $feature_value;
                        }
                    }
                }
            } else {
                self::$features_values[$id_lang] = array();
            }
        }
    }

    public function hookPostUpdateOrderStatus($params)
    {
        $this->hookActionOrderStatusPostUpdate($params);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        // Manage Messaging with customer (invoice)
        //
        $this->manageInvoiceOrderState($params);

        // Manage Messaging with customer (Seller Review Incentive)
        //
        $this->manageReviewIncentiveOrderState($params);

        // Manage Order Cancelations
        //
        $this->manageOrderCancelation($params);

        if (Amazon::$debug_mode && Tools::getValue('id_order')) {
            CommonTools::d(sprintf('%s:#%d hookActionOrderStatusPostUpdate - module is in debug mode, operation stopped'."\n", basename(__FILE__), __LINE__));
        }
    }

    public function hookActionEmailAddAfterContent($params)
    {
        if (!$this->customerThreadIsActive($params)) {
            return;
        }
        require_once(dirname(__FILE__).'/classes/amazon.messaging.class.php');

        $messaging = new AmazonMessaging();
        $messaging->overrideCustomerThreadEmail($params);
    }

    public function customerThreadIsActive($params = false)
    {
        if (!function_exists('imap_open')) {
            return(false);
        }

        if (!$this->amazon_features['messaging']) {
            return(false);
        }

        $customer_thread_settings = AmazonConfiguration::get('CUSTOMER_THREAD');

        if (!is_array($customer_thread_settings) || !count($customer_thread_settings)) {
            return(false);
        }

        if (is_array($customer_thread_settings) && (!isset($customer_thread_settings['active']) || !(bool)$customer_thread_settings['active'])) {
            return(false);
        }

        if (is_array($params) && $params['template'] == $customer_thread_settings['template']) {
            return(true);
        } elseif ($params != false && is_array($params)) {
            return(false);
        }

        return(true);
    }

    protected function manageInvoiceOrderState($params)
    {
        $id_order = (int)$params['id_order'];
        $test_mode = isset($params['test_mode']) && $params['test_mode'];

        // Mail/Invoice is Active ?
        //
        $mail_invoice = AmazonConfiguration::get('MAIL_INVOICE');

        if (!isset($mail_invoice['active']) || !(int)$mail_invoice['active'] || !isset($mail_invoice['order_state'])) {
            if (Amazon::$debug_mode) {
                printf('%s:#%d Amazon_Messaging::sendInvoice(%d) invoice by email is inactive'."\n", basename(__FILE__), __LINE__, $id_order);
            }

            return (false);
        }

        $order = new Order($id_order);

        if (!Validate::isLoadedObject($order)) {
            if (Amazon::$debug_mode) {
                printf('%s:#%d Amazon_Messaging::sendInvoice(%d) invoice by email: unable to load order'."\n", basename(__FILE__), __LINE__, $id_order);
            }

            return (false);
        }

        // Not an amazon order
        //
        if (Tools::strtolower($order->module) != Tools::strtolower($this->name)) {
            if (Amazon::$debug_mode) {
                printf('%s:#%d Amazon_Messaging::sendInvoice(%d) invoice by email: not an Amazon order'."\n", basename(__FILE__), __LINE__, $id_order);
            }

            return(false);
        }

        if (!$test_mode) {
            // Matching Order Status
            //
            if ($params['newOrderStatus']->id != (int)$mail_invoice['order_state']) {
                if (Amazon::$debug_mode) {
                    printf('%s:#%d Amazon_Messaging::sendInvoice(%d) invoice by email: status mismatch: %d/%d'."\n", basename(__FILE__), __LINE__, $id_order, $params['newOrderStatus']->id, (int)$mail_invoice['order_state']);
                }

                return (false);
            }
        }

        // Starting Mail/Invoice sending
        //
        require_once(dirname(__FILE__).'/classes/amazon.messaging.class.php');

        $messaging = new AmazonMessaging(Amazon::$debug_mode, $test_mode);
        $result = $messaging->sendInvoice($id_order);

        if (Amazon::$debug_mode && !$result) {
            printf('%s:#%d AmazonMessaging::sendInvoice(%d) failed'."\n", basename(__FILE__), __LINE__, $id_order);
        }

        return ($result);
    }

    protected function manageReviewIncentiveOrderState($params)
    {
        $id_order = (int)$params['id_order'];
        $test_mode = isset($params['test_mode']) && $params['test_mode'];

        // Mail/Invoice is Active ?
        //
        $mail_review = AmazonConfiguration::get('MAIL_REVIEW');

        if (!isset($mail_review['active']) || !(int)$mail_review['active'] || !isset($mail_review['order_state'])) {
            return (false);
        }

        if (!($order = new Order($id_order))) {
            return (false);
        }

        // Not an amazon order
        //
        if (Tools::strtolower($order->module) != Tools::strtolower($this->name)) {
            return (false);
        }

        // Matching Order Status
        //
        if (!$test_mode && $params['newOrderStatus']->id != (int)$mail_review['order_state']) {
            return (false);
        }

        // Starting Mail/Invoice sending
        //
        require_once(dirname(__FILE__).'/classes/amazon.messaging.class.php');

        $messaging = new AmazonMessaging(Amazon::$debug_mode, $test_mode);
        $result = $messaging->sendReviewIncentive($id_order);

        if (!$result && Amazon::$debug_mode) {
            printf('%s:#%d AmazonMessaging::manageReviewIncentiveOrderState(%d) failed'."\n", basename(__FILE__), __LINE__, $id_order);
        }

        return ($result);
    }
    
    protected function manageOrderCancelation($params)
    {
        $id_order = (int)$params['id_order'];

        if (!($order = new Order($id_order))) {
            return (false);
        }

        // Not an amazon order
        //
        if (Tools::strtolower($order->module) != Tools::strtolower($this->name)) {
            return (false);
        }

        // Matching Order Status
        //
        $canceled_state = AmazonConfiguration::get('CANCELED_STATE');
        
        if (!$canceled_state || (int)$params['newOrderStatus']->id != (int)$canceled_state) {
            return (false);
        }

        require_once(dirname(__FILE__).'/classes/amazon.order_info.class.php');
        require_once(dirname(__FILE__).'/classes/amazon.order.class.php');
        require_once(dirname(__FILE__).'/classes/amazon.order_cancel.class.php');

        $order_cancel = new AmazonOrderCancel();
        $result = $order_cancel->changeOrderStatus($id_order, AmazonOrder::TO_CANCEL);

        if (!$result && Amazon::$debug_mode) {
            printf('%s:#%d AmazonMessaging::manageOrderCancelation(%d) failed'."\n", basename(__FILE__), __LINE__, $id_order);
        }

        return ($result);
    }


    /* HOOKS FOR BACKWARD COMPATIBILITY - PRESTASHOP 1.3 and 1.4 */

    public function hookbackOfficeHeader($params)
    {
        return ($this->hookDisplayBackOfficeHeader($params));
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        $html = '';
        $amazonTab = null;

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $context_param = null;
            $amazonTab = Tools::strtolower(Tools::getValue('tab')) === 'admincatalog';
        }

        if ($amazonTab && Tools::getValue('id_product') && (Tools::getValue('addproduct') !== false || Tools::getValue('updateproduct') !== false)) {
            $context_param = '?amazon_token='.Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0);
            $html .= html_entity_decode('&lt;meta name="amazon-options" content="'.$this->url.'functions/product_options.php'.$context_param.'" /&gt;');
            $html .= $this->_autoAddJS($this->url.'views/js/product_options.js');
            $html .= $this->_autoAddCSS($this->url.'views/css/product_options.css');
        }

        return ($html);
    }

    public function hookActionProductAttributeDelete($params)
    {
        if (isset($params['product']->id)) {
            $id_product = $params['product']->id;
        } elseif (isset($params['id_product'])) {
            $id_product = $params['id_product'];
        } elseif (isset($params['product'])) {
            $id_product = $params['product']['id_product'];
        } else {
            return (false);
        }

        return (AmazonProduct::marketplaceActionRemoveAllCombinations(self::REMOVE, $id_product));
    }

    public function hookDeleteProductAttribute($params)
    {
        if (isset($params['product']->id_product_attribute)) {
            $id_product_attribute = $params['product']->id_product_attribute;
            $id_product = $params['product']->id_product;
        } elseif (isset($params['id_product_attribute'])) {
            $id_product_attribute = $params['id_product_attribute'];
            $id_product = $params['id_product'];
        } elseif (isset($params['product'])) {
            $id_product_attribute = $params['product']['id_product_attribute'];
            $id_product = $params['product']['id_product'];
        } else {
            return (false);
        }

        return (AmazonProduct::marketplaceActionRemoveCombination($id_product, $id_product_attribute));
    }

    public function hookActionProductSave($params)
    {
        if (isset($params['product']->id)) {
            $id_product = $params['product']->id;
        } elseif (isset($params['id_product'])) {
            $id_product = $params['id_product'];
        } elseif (isset($params['product'])) {
            $id_product = $params['product']['id_product'];
        } else {
            return (false);
        }

        return (AmazonProduct::marketplaceActionSet(self::UPDATE, $id_product));
    }

    public function hookActionOrderHistoryAddAfter($params)
    {
        // Manage FBA MultiChannel Orders
        //
        return ($this->_manageFbaIncomingOrder($params));
    }

    public function _manageFbaIncomingOrder($params)
    {
        if (!isset($params['order_history'])) {
            return (false);
        }

        $order_status = new OrderState((int)$params['order_history']->id_order_state);

        if (!Validate::isLoadedObject($order_status)) {
            return(false);
        }
        if (!$order_status->paid) {
            return(false);
        }
        if ($order_status->shipped) {
            return(false);
        }
        if ($order_status->delivery) {
            return(false);
        }

        // Only if FBA MultiChannel is active
        if (!(bool)Configuration::get('AMAZON_FBA_MULTICHANNEL')) {
            return (false);
        }

        if (!(bool)Configuration::get('AMAZON_FBA_MULTICHANNEL_AUTO')) {
            return (false);
        }

        if (!isset($params['order_history']->id_order)) {
            return (false);
        }

        $id_order = (int)$params['order_history']->id_order;

        require_once(dirname(__FILE__).'/classes/amazon.multichannel.class.php');
        require_once(dirname(__FILE__).'/classes/amazon.mail.logger.class.php');

        $debug = (bool)Configuration::get('AMAZON_DEBUG_MODE');
        $send_email = (bool)Configuration::get('AMAZON_EMAIL');
        $message = null;

        if ($debug && $send_email) {
            $caller = AmazonTools::callingFunction();
            $message .= sprintf('%s : %s'.self::LF, $caller, 'Testing FBA Eligibility');
        }

        if (!($order = AmazonMultiChannel::isEligible($id_order))) {
            if ($debug && $send_email) {
                $caller = AmazonTools::callingFunction();
                $message .= sprintf('%s : %s'.self::LF, $caller, 'Order is not eligible');
                AmazonMailLogger::message($message);
            }

            return (false);
        }

        if (!isset($order->id_lang) || !$order->id_lang) {
            if ($debug && $send_email) {
                $caller = AmazonTools::callingFunction();
                $message .= sprintf('%s : %s'.self::LF, $caller, 'Missing ID Lang');
                AmazonMailLogger::message($message);

                return (false);
            }
        }

        // TODO: FBA Matrix to identify the closest fullfilment center
        $id_lang = $order->id_lang;

        // Eligibility Check passed, place a new FBA Order
        //
        $amazonMultiChannelOrder = new AmazonMultiChannel($id_order);

        if (!Validate::isLoadedObject($amazonMultiChannelOrder)) {
            if ($debug && $send_email) {
                $caller = AmazonTools::callingFunction();
                $message .= sprintf('%s : %s'.self::LF, $caller, 'Unable to read object');
                AmazonMailLogger::message($message);
            }

            // abnormal
            return (false);
        }
        // Module itself
        if (Tools::strtolower($amazonMultiChannelOrder->module) === $this->name) {
            return (false);
        }

        // Already ordered, shipped or canceled
        //
        if (Tools::strlen($amazonMultiChannelOrder->marketPlaceChannelStatus)) {
            return (false);
        }

        if (!($AmazonFBAOrder = $amazonMultiChannelOrder->createFulfillmentOrder($id_lang, $debug))) {
            if ($debug && $send_email) {
                $caller = AmazonTools::callingFunction();
                $message .= sprintf('%s : %s'.self::LF, $caller, 'CreateFulfillmentOrder Failed');

                if (AmazonMultiChannel::$errors) {
                    $message .= print_r(AmazonMultiChannel::$errors, true);
                }

                $message .= print_r($AmazonFBAOrder, true);
                AmazonMailLogger::message($message);
            } elseif ($debug) {
                echo nl2br(print_r($AmazonFBAOrder, true));
            }

            return (false);
        }

        if ($send_email) {
            $mailtemplate = array();
            $mailtemplate['{order}'] = sprintf('%s : %s
                        %s : %s
                        %s : %s', $this->l('Order ID'), $id_order, $this->l('Date'), $AmazonFBAOrder['DisplayableOrderDateTime'], $this->l('Shipping'), $AmazonFBAOrder['ShippingSpeedCategory']);
            $mailtemplate['{order}'] = nl2br($mailtemplate['{order}']);

            $mailtemplate['{customer_address}'] = sprintf('
                %s
                %s
                %s
                %s
                %s %s (%s)
                %s', $AmazonFBAOrder['DestinationAddress']['Name'], $AmazonFBAOrder['DestinationAddress']['Line1'], $AmazonFBAOrder['DestinationAddress']['Line2'], $AmazonFBAOrder['DestinationAddress']['Line3'], $AmazonFBAOrder['DestinationAddress']['PostalCode'], $AmazonFBAOrder['DestinationAddress']['City'], $AmazonFBAOrder['DestinationAddress']['CountryCode'], $AmazonFBAOrder['DestinationAddress']['PhoneNumber']);
            $mailtemplate['{customer_address}'] = nl2br($mailtemplate['{customer_address}']);

            $mailtemplate['{ordered_items}'] = null;

            if (is_array($AmazonFBAOrder['Items'])) {
                foreach ($AmazonFBAOrder['Items'] as $Item) {
                    $mailtemplate['{ordered_items}'] .= sprintf('%d x %s - %s'."\n", $Item['Quantity'], $Item['SKU'], $Item['DisplayableComment']);
                }

                $mailtemplate['{ordered_items}'] = nl2br($mailtemplate['{ordered_items}']);

                $mailtemplate['{amazon_info}'] = sprintf($this->l('Order #%s submitted on %s Amazon response ID: %s'), $AmazonFBAOrder['DisplayableOrderId'], $AmazonFBAOrder['DisplayableOrderDateTime'], $AmazonFBAOrder['Response']);
            }

            $email_address = Configuration::get('PS_SHOP_EMAIL');

            if ($debug) {
                $caller = AmazonTools::callingFunction();
                $message .= sprintf('%s : %s'.self::LF, $caller, 'Amazon FBA Order Complete');
                $message .= print_r($AmazonFBAOrder, true);
                AmazonMailLogger::message($message);
            }
            Mail::Send(
                $id_lang, // id_lang
                'fba_multichannel', // template
                $this->l('Amazon FBA: A new multichannel order has been processed'), // subject
                $mailtemplate, // templateVars
                $email_address, // to
                null, // To Name
                null, // From
                null, // From Name
                null, // Attachment
                null, // SMTP
                $this->path.'mails/'
            );
        }

        if ($debug && !$send_email) {
            echo nl2br(print_r($AmazonFBAOrder, true));
        }

        return (false);
    }

    public function hookUpdateQuantity($params)
    {
        return ($this->hookActionUpdateQuantity($params));
    }

    public function hookActionUpdateQuantity($params)
    {
        if (isset($params['product']->id)) {
            $id_product = $params['product']->id;
        } elseif (isset($params['id_product'])) {
            $id_product = $params['id_product'];
        } elseif (isset($params['product'])) {
            $id_product = $params['product']['id_product'];
        } else {
            return (false);
        }

        $id_product_attribute = isset($params['id_product_attribute']) ? $params['id_product_attribute'] : null;

        AmazonProduct::marketplaceActionSet(self::UPDATE, $id_product, $id_product_attribute);
    }

    public function hookUpdateProduct($params)
    {
        $this->hookActionProductUpdate($params);
    }

    public function hookActionProductUpdate($params)
    {
        if (isset($params['product']->id)) {
            $id_product = $params['product']->id;
        } elseif (isset($params['id_product'])) {
            $id_product = $params['id_product'];
        } elseif (isset($params['product'])) {
            $id_product = $params['product']['id_product'];
        } else {
            return (false);
        }

        AmazonProduct::marketplaceActionSet(self::UPDATE, $id_product);
    }

    public function hookAfterSaveProduct($params)
    {
        return ($this->hookActionProductUpdate($params));
    }

    public function actionObjectStockAvailableUpdateAfter($params)
    {
        if (!isset($params['object']) || !$params['object']->id_product) {
            return (false);
        }

        $obj = new stdClass;
        $obj->id = (int)$params['object']->id_product;

        return ($this->hookActionProductUpdate(array('product' => $obj)));
    }

    public function hookActionObjectProductUpdateAfter($params)
    {
        if (!isset($params['object']) || !$params['object']->id) {
            return (false);
        }

        $obj = new stdClass;
        $obj->id = $params['object']->id;

        return ($this->hookActionProductUpdate(array('product' => $obj)));
    }

    public function hookUpdateProductAttribute($params)
    {
        return ($this->hookActionProductAttributeUpdate($params));
    }

    public function hookActionProductAttributeUpdate($params)
    {
        if (!isset($params['id_product_attribute'])) {
            return (false);
        }

        return (AmazonProduct::marketplaceActionUpdateCombination($params['id_product_attribute']));
    }

    public function hookDeleteProduct($params)
    {
        return ($this->hookActionProductDelete($params));
    }

    public function hookActionProductDelete($params)
    {
        if (isset($params['product']->id)) {
            $id_product = $params['product']->id;
        } elseif (isset($params['id_product'])) {
            $id_product = $params['id_product'];
        } elseif (isset($params['product'])) {
            $id_product = $params['product']['id_product'];
        } else {
            return (false);
        }

        if (!isset($params['product']->reference)) {
            return (false);
        }

        return (AmazonProduct::marketplaceActionSet(self::REMOVE, $id_product, null, $params['product']->reference));
    }

    public function hookAddProduct($params)
    {
        return ($this->hookActionProductAdd($params));
    }

    public function hookActionProductAdd($params)
    {
        if (isset($params['product']->id)) {
            $id_product = $params['product']->id;
        } elseif (isset($params['id_product'])) {
            $id_product = $params['id_product'];
        } elseif (isset($params['product'])) {
            $id_product = $params['product']['id_product'];
        } else {
            return (false);
        }

        return (AmazonProduct::marketplaceActionSet(self::ADD, $id_product));
    }

    public function hookActionCarrierUpdate($params)
    {
        $this->hookUpdateCarrier($params);
    }

    public function hookUpdateCarrier($params)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=') && Shop::isFeatureActive()) {
            //
            $shops = Shop::getShops(true, null, false);
        } else {
            $shops = array(
                0 => array(
                    'id_shop' => null,
                    'id_shop_group' => null
                )
            );
        }

        foreach ($shops as $shop) {
            // Amazon Carrier Mapping
            $carriers = AmazonConfiguration::get('CARRIER', null, $shop['id_shop_group'], $shop['id_shop']);

            if ($carriers && is_array($carriers)) {
                foreach ($carriers as $id_lang => $ps_carriers) {
                    if ($ps_carriers && is_array($ps_carriers)) {
                        foreach ($ps_carriers as $index => $carrier) {
                            if ($carriers[$id_lang][$index] == $params['id_carrier']) {
                                $carriers[$id_lang][$index] = $params['carrier']->id;
                            }
                        }
                    }
                }
            }

            AmazonConfiguration::updateValue('CARRIER', $carriers, false, $shop['id_shop_group'], $shop['id_shop']);

            // Amazon FBA MultiChannel Carrier Mapping
            $carriers_multichannel = AmazonConfiguration::get('CARRIER_MULTICHANNEL', null, $shop['id_shop_group'], $shop['id_shop']);

            if ($carriers_multichannel && is_array($carriers_multichannel)) {
                foreach ($carriers_multichannel as $id_lang => $mapping) {
                    if (isset($mapping['prestashop']) && is_array($mapping['prestashop'])) {
                        foreach ($mapping['prestashop'] as $key => $id_carrier) {
                            if ($id_carrier == $params['id_carrier']) {
                                $carriers_multichannel[$id_lang]['prestashop'][$key] = $params['carrier']->id;
                            }
                        }
                    }
                }
            }

            AmazonConfiguration::updateValue('CARRIER_MULTICHANNEL', $carriers_multichannel, false, $shop['id_shop_group'], $shop['id_shop']);

            // Amazon Outgoing Orders Carrier Mapping
            $carriers_default = AmazonConfiguration::get('CARRIER_DEFAULT', null, $shop['id_shop_group'], $shop['id_shop']);

            if ($carriers_default && is_array($carriers_default)) {
                foreach ($carriers_default as $id_lang => $mapping) {
                    if (isset($mapping['prestashop']) && is_array($mapping['prestashop'])) {
                        foreach ($mapping['prestashop'] as $key => $id_carrier) {
                            if ($id_carrier == $params['id_carrier']) {
                                $carriers_default[$id_lang]['prestashop'][$key] = $params['carrier']->id;
                            }
                        }
                    }
                }
            }

            AmazonConfiguration::updateValue('CARRIER_DEFAULT', $carriers_default, false, $shop['id_shop_group'], $shop['id_shop']);

            // Amazon Smart Shipping
            $smart_shipping = AmazonConfiguration::get('SHIPPING', null, $shop['id_shop_group'], $shop['id_shop']);

            if (is_array($smart_shipping) && isset($smart_shipping['smart_shipping']) && isset($smart_shipping['smart_shipping']['prestashop']) && is_array($smart_shipping['smart_shipping']['prestashop'])) {
                foreach ($smart_shipping['smart_shipping']['prestashop'] as $amazonShippingOption => $id_carrier) {
                    if ($id_carrier == $params['id_carrier']) {
                        $smart_shipping['smart_shipping']['prestashop'][$amazonShippingOption] = $params['carrier']->id;
                    }
                }
            }

            AmazonConfiguration::updateValue('SHIPPING', $smart_shipping, false, $shop['id_shop_group'], $shop['id_shop']);
        }
    }

    public function hookAdminOrder($params)
    {
        return ($this->hookDisplayAdminOrder($params));
    }

    public function hookDisplayAdminOrder($params)
    {
        require_once(_PS_MODULE_DIR_.$this->name.'/classes/amazon.admin_order.class.php');

        $adminOrder = new AmazonAdminOrder();
        $this->_html = $adminOrder->marketplaceOrderDisplay($params);

        return ($this->_html);
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        require_once(_PS_MODULE_DIR_.$this->name.'/classes/amazon.product_tab.class.php');

        $adminProductTab = new AmazonProductTab();

        $html = $adminProductTab->marketplaceProductTabContent($params);

        if (Tools::strlen($html)) {
            return ($html);
        } else {
            return (nl2br(Amazon::LF));
        } // Prevents error: "A server error occurred while loading the tabs: some tabs could not be loaded."
    }

    public function hookActionAdminOrdersListingFieldsModifier($params)
    {
        if (!isset($params['fields']['mp_order_id'])) {
            $params['fields']['mp_order_id'] = array(
                'title' => 'Marketplace Order ID',
                "align" => "text-center",
                "class" => "fixed-width-xs",
                "filter_key" => "amo!mp_order_id"
            );
        }
        if (isset($params['select'])) {
            $params['select'] .= ", amo.mp_order_id";
        }

        if (isset($params['join'])) {
            $params['join'] .= 'LEFT JOIN `' . _DB_PREFIX_ . 'marketplace_orders` amo ON (a.`id_order` = amo.`id_order`)';
        }
    }

    public function hookDisplayPDFInvoice($object)
    {
        require_once(_PS_MODULE_DIR_.$this->name.'/classes/amazon.admin_order.class.php');
        require_once(_PS_MODULE_DIR_.$this->name.'/classes/amazon.order_item.class.php');

        /** @var OrderInvoice $order_invoice */
        $order_invoice = $object['object'];
        $id_order = $order_invoice->id_order;
        $customizations = array();

        $marketplace_order = AmazonAdminOrder::getByOrderId($id_order);
        if (!$marketplace_order) {
            return '';
        }

        $order_detail = AmazonAdminOrder::getOrderDetailList($id_order);
        if (is_array($order_detail) && count($order_detail)) {
            foreach ($order_detail as $detail) {
                $item = AmazonOrderItem::getItemByOrderId($id_order, $detail['product_id'], $detail['product_attribute_id']);
                if ($item && is_array($item) && isset($item['sku'], $item['customization']) && is_array($item['customization'])) {
                    $customizations[$item['sku']] = $item['customization'];
                }
            }
        }

        $tpl_params = array(
            'mp_order_id'    => $marketplace_order['mp_order_id'],
            'customizations' => $customizations
        );
        $this->context->smarty->assign($tpl_params);

        return $this->context->smarty->fetch($this->path.'views/templates/admin/admin_order/invoice_additional_info.tpl');
    }

    /**
     * GDPR compliant: Export customer data
     * @param string|array $customer
     * @return string
     * @throws PrestaShopDatabaseException
     */
    public function hookActionExportGDPRData($customer)
    {
        $customer_data = $this->_extractCustomerDataForGDPR($customer);

        $mp_order_ids = AmazonTools::getAllCustomerMpOrderIds($customer_data['email'], $customer_data['name']);

        if (is_array($mp_order_ids) && count($mp_order_ids)) {
            $result = AmazonTools::getAllCustomerDataByMpOrderIds($mp_order_ids);
            return json_encode($result);
        }

        return json_encode(array());
    }

    /**
     * GDPR compliant: Delete customer data
     * @param string|array $customer
     * @return string
     * @throws PrestaShopDatabaseException
     */
    public function hookActionDeleteGDPRCustomer($customer)
    {
        $customer_data = $this->_extractCustomerDataForGDPR($customer);

        $mp_order_ids = AmazonTools::getAllCustomerMpOrderIds($customer_data['email'], $customer_data['name']);

        if (is_array($mp_order_ids) && count($mp_order_ids)) {
            $result = AmazonTools::deleteAllCustomerDataByMpOrderId($mp_order_ids);
            if ($result) {
                return json_encode(true);
            } else {
                return json_encode($this->l('Amazon: Unable to delete customer data.'));
            }
        }

        return json_encode(true);
    }

    /**
     * Parse customer input to email and name
     * @param $customer
     * @return array
     */
    private function _extractCustomerDataForGDPR($customer)
    {
        // Include needed classes
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.address.class.php');
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.order.class.php');
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.order_item.class.php');
        require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.stat.class.php');

        $email = $first_name = $last_name = '';
        if (is_string($customer)) {
            // This is email
            $email = $customer;
        } elseif (is_array($customer)) {
            // Customer data in array
            if (isset($customer['email'])) {
                $email = $customer['email'];
            }
            if (isset($customer['firstname'])) {
                $first_name = $customer['firstname'];
            }
            if (isset($customer['lastname'])) {
                $last_name = $customer['lastname'];
            }
        }

        $email  = trim($email);

        if ($first_name && $last_name && Tools::strlen($first_name) && Tools::strlen($last_name)) {
            $name = array(trim($first_name.' '.$last_name), trim($last_name.' '.$first_name));
        } else {
            $name = trim($first_name.' '.$last_name);
        }

        return array('email' => $email, 'name' => $name);
    }
}
