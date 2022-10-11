<?php
/* For PS 1.4.X */

if (!defined('_PS_VERSION_')) {
    include(dirname(__FILE__).'/../../../../config/config.inc.php');
}
if (version_compare(_PS_VERSION_, '1.5.0.0', '<') && !class_exists('ModuleFrontController')) {
    class ModuleFrontController extends FrontController
    {
        public $ajax = true;
        public function __construct()
        {
            parent::__construct();
            $this->ssl = (Tools::usingSecureMode() ? true : false);
        }
    }
}
class pm_modalcart3ajax_frontModuleFrontController extends ModuleFrontController
{
    protected $context;
    private $mc3Action;
    private $mc3IdProduct;
    private $mc3IdProductAttribute;
    public $ajax = true;
    public $ssl = true;
    private $ap5ModuleInstance = false;
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->ssl = (Tools::usingSecureMode() ? true : false);
        $this->content_only = true;
        $this->display_header = false;
        $this->display_footer = false;
        $this->display_column_left = false;
        $this->display_column_right = false;
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $this->context = Context::getContext();
        } else {
            $this->context = (object) null;
        }
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            $this->ap5ModuleInstance = Module::getInstanceByName('pm_advancedpack');
        }
    }
    public function init()
    {
        parent::init();
        if (version_compare(_PS_VERSION_, '1.5.0.0', '<')) {
            $this->context->cookie = self::$cookie;
            $this->context->smarty = self::$smarty;
        }
    }
    public function run()
    {
        $this->init();
        $this->process();
        $this->displayAjax();
    }
    public function process()
    {
        $this->mc3Action = Tools::getValue('action', false);
        if ($this->mc3Action == 'pack_add') {
            $this->mc3IdProduct = (int)Tools::getValue('id_product', false);
        } else {
            $this->mc3IdProduct = (int)Tools::getValue('id_product', false);
        }
        if (!Tools::getIsset('id_product_attribute') || (Tools::getIsset('id_product_attribute') && Tools::getValue('id_product_attribute') == 'null')) {
            if (Validate::isLoadedObject($this->ap5ModuleInstance) && AdvancedPack::isValidPack($this->mc3IdProduct)) {
                $lastProduct = $this->context->cart->getLastProduct();
                $this->mc3IdProductAttribute = (int)$lastProduct['id_product_attribute'];
                $this->mc3Action = 'pack_add';
            } else {
                $this->mc3IdProductAttribute = (int)Product::getDefaultAttribute($this->mc3IdProduct);
            }
        } else {
            $this->mc3IdProductAttribute = (int)Tools::getValue('id_product_attribute', 0);
        }
        if ($this->mc3Action != 'refresh' && $this->mc3Action != 'pack_add' && !$this->mc3IdProduct) {
            die('Error');
        }
    }
    public function displayAjax()
    {
        switch ($this->mc3Action) {
            case 'product_add':
                echo Module::getInstanceByName('pm_modalcart3')->getModalToDisplay($this->mc3IdProduct, $this->mc3IdProductAttribute);
                break;
            case 'refresh':
                echo Module::getInstanceByName('pm_modalcart3')->getModalToDisplay(null, null);
                break;
            case 'pack_add':
                echo Module::getInstanceByName('pm_modalcart3')->getModalToDisplay($this->mc3IdProduct, $this->mc3IdProductAttribute, true);
                break;
            default:
                die;
        }
        die;
    }
}
if (version_compare(_PS_VERSION_, '1.5.0.0', '<')) {
    ControllerFactory::getController('pm_modalcart3ajax_frontModuleFrontController')->run();
}
