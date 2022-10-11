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

require_once(dirname(__FILE__).'/../../amazon.php');
require_once(dirname(__FILE__).'/../../classes/amazon.tools.class.php');
require_once(dirname(__FILE__).'/../../classes/amazon.orders_reports.class.php');
require_once(dirname(__FILE__).'/../../classes/amazon.stat.class.php');

class AdminAmazonStatsController extends AdminStatsController
{
    const MENU_ORDERS_REPORTS   = 'orders_reports';
    const MENU_VAT_REPORTS      = 'vat_reports';

    public $module = 'amazon';
    public $name   = 'amazon';
    public $amazon = null;

    public $ps15x = false;

    protected $processListData = false;

    /** @var string Current module name */
    protected $current_module;

    // If order way is ASC
    protected $_orderWayAsc = true;

    protected $kpis = array(
        'sales_30_days'     => 0,
        'income_30_days'    => 0,
        'avg_order_value'   => 0,
        'avg_order_per_day' => 0
    ); //todo: should store to table `ps_configuration_kpi`????

    public function __construct()
    {
        $this->amazon = new Amazon();

        $this->lang = false;
        $this->deleted = false;

        $this->colorOnBackground = false;

        // Version detection
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->ps15x = true;
        }

        $this->bootstrap = true;

        parent::__construct();

        // Set current menu
        $this->initCurrentMenu();

        // Set our controller's params after parent construct
        $this->className        = $this->amazon->name;
        $this->identifier       = 'mp_order_id';
        $this->identifier_name  = "amazon_order_id";        // Unknown property
        $this->display          = 'list';
        // AdminPreferencesController construct will init _defaultOrderBy by identifier, which is not set yet.
        // We set it here to override
        $this->_defaultOrderBy  = $this->identifier;
    }

    /**
     * Prevent AdminStatsTabControllerCore::init to override display & action
     */
    public function init()
    {
        AdminController::init();
    }

    public function setMedia()
    {
        parent::setMedia();

        $assetPath = _MODULE_DIR_.$this->amazon->name.'/views/';
        $this->addCSS($assetPath . 'css/admin_stat.css');
        $this->addJS($assetPath .'js/adminstats.js');
    }

    /** June-14: Remove old functions */

    /**
     * July-03-2018: Remove parse data functions
     */

    /** June-14: Remove old functions */

    /**
     * July-03-2018: Remove parse data functions
     */

    /**
     * @param $statsData
     */
    protected function setKPI($statsData)
    {
        $this->kpis['avg_order_per_day'] = (float) ($statsData['total_orders'] / 30);
        $this->kpis['avg_order_value'] = (float) ($statsData['total_amount'] / $statsData['total_orders']);
        $this->kpis['income_30_days'] = $statsData['total_amount'];
        $this->kpis['sales_30_days'] = $statsData['total_qty'];
    }

    /**
     * Handle date range change, filter change
     * @return bool|void
     */
    public function postProcess()
    {
        // Date range change, only affect chart
        $this->processDateRange();

        // AdminStatsTabController will override this function, and ignore filter process in AdminController
        AdminController::postProcess();
    }

    /**
     * Overview and chart.
     * Not render if version below 1.6
     * @return string
     * @throws Exception
     * @throws SmartyException
     */
    public function renderKpis()
    {
        if ($this->ps15x) {
            return null;
        }

        $tpl = $this->createTemplate('kpi.tpl');
        $tpl->assign(array(
            'kpis'      => $this->kpis,
            'show_kpi'  => self::MENU_ORDERS_REPORTS == $this->current_module
        ));

        // June-14: Remove calendar helper

        return $tpl->fetch();
    }

    /**
     * Render chart
     * Not render if version below 1.6
     * @return string
     * @throws SmartyException
     */
    public function renderChart()
    {
        if ($this->ps15x) {
            return null;
        }

        $context = $this->context;
        $currency = $context->currency;
        $from = $context->employee->stats_date_from;
        $to   = $context->employee->stats_date_to;

        $sales  = AmazonStat::getSales($from, $to);
        $orders = AmazonStat::getOrdersNum($from, $to);

        // Assign js variable
        Media::addJsDef(array(
            'chart_data' => array(
                'sales'  => $sales,
                'orders' => $orders
            ),
            'currency' => $currency,
            'priceDisplayPrecision' => _PS_PRICE_DISPLAY_PRECISION_,
        ));

        $tpl = $this->createTemplate('chart.tpl');
        $tpl->assign(array(
            'chart_total' => array(
                'sales' => $this->_sumChartData($sales),
                'orders' => $this->_sumChartData($orders)
            )
        ));

        return $tpl->fetch();
    }

    /**
     * July-03-2018: Remove renderView() function, which is render view of 1 record in list
     */

    /**
     * Display master menu
     * @return string
     * @throws Exception
     * @throws SmartyException
     */
    public function displayMenu()
    {
        $tpl = $this->createTemplate('menu.tpl');
        $tpl->assign(array(
            'current' => self::$currentIndex,
            'current_module_name' => $this->current_module,
            'token' => $this->token,
            'modules' => array(
                array('name' => 'orders_reports', 'display_name' => $this->l('Orders Reports')),
                array('name' => 'vat_reports', 'display_name' => $this->l('VAT Reports'))
            )
        ));

        return $tpl->fetch();
    }

    /**
     * June-20-2018: Bring kpi above calendar
     * @return string
     * @throws SmartyException
     */
    public function displayCalendar()
    {
        $content = $this->renderKpis();

        if (self::MENU_ORDERS_REPORTS == $this->current_module || $this->ps15x) {
            // Calendar in ps1.5 is empty
            $content .= parent::displayCalendar();
        }

        return $content;
    }

    /**
     * Display master content
     * @return string
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function displayStats()
    {
        $content = '';

        switch ($this->current_module) {
            case self::MENU_ORDERS_REPORTS:
                $content .= $this->renderChart();
                $content .= $this->renderList();
                break;
            case self::MENU_VAT_REPORTS:
                $content .= $this->renderList();
                break;
            default:
                $content .= '<p>Please select a module from the left column.</p>';
                break;
        }

        return $content;
    }

    /**
     * Get template for this controller
     * @param string $tpl_name
     * @return object|Smarty_Internal_Template
     */
    public function createTemplate($tpl_name)
    {
        $override_folder = $this->override_folder;
        // adm/themes/default/template/controllers/stats/   Use default base on PS version
        $stats_default_path = _PS_ADMIN_DIR_.'/themes/default/template/controllers/stats/'.$tpl_name;

        $sub_path    = $this->amazon->name.'/views/templates/admin/'.$override_folder;
        // modules/amazon/views/templates/admin/amazon_stats/   Override in module level
        $module_path = _PS_MODULE_DIR_.$sub_path.($this->ps15x ? 'ps15x/' : 'ps16x/').$tpl_name;
        // themes/default-bootstrap/modules/amazon/views/templates/admin/amazon_stats/      Allow override in theme
        $theme_path  = _PS_THEME_DIR_.'modules/'.$sub_path.$tpl_name;

        if ($this->viewAccess()) {
            if (file_exists($theme_path)) {
                return $this->context->smarty->createTemplate($theme_path, $this->context->smarty);
            } elseif (file_exists($module_path)) {
                return $this->context->smarty->createTemplate($module_path, $this->context->smarty);
            } elseif (file_exists($stats_default_path)) {
                return $this->context->smarty->createTemplate($stats_default_path, $this->context->smarty);
            }
        }

        return parent::createTemplate($tpl_name);
    }

    /**
     * Version compatibility, below 1.6
     * Lower version hasn't broken this function yet
     */
    public function processDateRange()
    {
        if (!$this->ps15x) {
            parent::processDateRange();
        }
    }

    /**
     * Fields list of order reports
     */
    protected function fieldListOrderReport()
    {
        $this->fields_list = array(
            'mp_order_id' => array(
                'title' => 'Amazon Order ID',
                'align' => 'text-center',
                'orderby' => false,
            ),
            'buyer_name' => array(
                'title' => 'Buyer Name',
                'orderby' => false,
            ),
            'shipping_price' => array(
                'title' => 'Shipping Price',
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'orderby' => false,
                'search' => false
            ),
            'shipping_tax' => array(
                'title' => 'Shipping Tax',
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'orderby' => false,
                'search' => false
            ),
            'commissions' => array(
                'title' => 'Amazon Fee',
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'orderby' => false,
                'search' => false
            ),
            'total_price' => array(
                'title' => 'Price',
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'search' => false
            ),
            'total_tax' => array(
                'title' => 'Price tax',
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'search' => false
            ),
            'purchase_date' => array(
                'title' => 'Purchase Date',
                'type' => 'datetime',
            ),
            'payments_date' => array(
                'title' => 'Payment Date',
                'type' => 'datetime',
            ),
        );
    }

    /**
     * Fields list of VAT reports
     */
    protected function fieldListVatReport()
    {
        $this->fields_list = array(
            'mp_order_id' => array(
                'title' => 'Amazon Order ID',
                'align' => 'text-center',
                'orderby' => false,
            ),
            'tax_model' => array(
                'title' => 'Tax Model',
                'orderby' => true
            ),
            'display_price' => array(
                'title' => 'Price',
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'search' => false
            ),
            'total_tax' => array(
                'title' => 'Total Tax',
                'align' => 'text-right',
                'type' => 'price',
                'currency' => true,
                'search' => false
            ),
            'order_date' => array(
                'title' => 'Order Date',
                'type' => 'datetime',
                'orderby' => true,
            ),
            'tax_date' => array(
                'title' => 'Tax Date',
                'type' => 'datetime',
                'orderby' => true
            ),
        );
    }

    /**
     * Version compatibility, below 1.6
     * Lower version also has this engine. But we don't need them
     * @return null
     */
    protected function displayEngines()
    {
        if ($this->ps15x) {
            return '</div>';
        }

        return parent::displayEngines();
    }

    /**
     * Calculate total value for charts
     * @param $input
     *
     * @return int
     */
    protected function _sumChartData($input)
    {
        $sum = 0;

        foreach ($input as $value) {
            $sum += $value['data'];
        }

        return $sum;
    }

    /**
     * Select active menu
     * Set field list, cookie and table name according active menu
     */
    protected function initCurrentMenu()
    {
        if (Tools::getIsset('module')) {
            $this->current_module = Tools::getValue('module');
        } else {
            if ($this->context->cookie->amazon_stats_current_menu) {
                $this->current_module = $this->context->cookie->amazon_stats_current_menu;
            }
        }
        if (!$this->current_module) {
            $this->current_module = self::MENU_ORDERS_REPORTS;
        }
        $this->context->cookie->amazon_stats_current_menu = $this->current_module;

        // field_list, cookie id, table name must be together
        if ($this->current_module == self::MENU_ORDERS_REPORTS) {
            $this->fieldListOrderReport();
            $this->table   = 'marketplace_stats';
            $this->list_id = 'amazon_order_reports';        // Store sort, filter, pagination
        } else {
            $this->fieldListVatReport();
            $this->table   = 'marketplace_vat_report';
            $this->list_id = 'amazon_vat_reports';          // Store sort, filter, pagination
        }
    }

    // June-14: Remove calendar helper
    // June-14: Remove processDateRange
}
