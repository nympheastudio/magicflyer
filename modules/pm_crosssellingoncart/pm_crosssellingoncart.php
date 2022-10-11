<?php
/**
 * pm_crosssellingoncart
 *
 * @author    Presta-Module.com <support@presta-module.com> - http://www.presta-module.com
 * @copyright Presta-Module 2017 - http://www.presta-module.com
 * @license   Commercial
 * @version   2.4.0
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
class pm_crosssellingoncart extends Module
{
    public static $modulePrefix = 'CSOC';
    public $baseConfigUrl;
    private $defaultLanguage;
    private $isoLang;
    public $prefixFieldsOptions;
    protected $copyrightLink = array(
        'link'    => '',
        'img'    => '//www.presta-module.com/img/logo-module.JPG'
    );
    const DYNAMIC_CSS = 'views/css/pm_crosssellingoncart_dynamic.css';
    const DEFAULT_NB_ACCESSORIES = 3;
    const DEFAULT_NB_CROSSSELLING = 3;
    public function __construct($prefixFieldsOptions = 'PM_CSOC')
    {
        $this->name = 'pm_crosssellingoncart';
        $this->tab = 'merchandizing';
        $this->version = '2.4.0';
        $this->author = 'Presta-Module';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '4cf8891dfa30ed7ae18f4cc37d612b24';
        $this->ps_versions_compliancy = array('min' => '1.5.0.0', 'max' => '1.7.99.99');
        parent::__construct();
        $this->languages = Language::getLanguages(false);
        $this->isoLang = Language::getIsoById($this->context->cookie->id_lang);
        $this->defaultLanguage = (int)Configuration::get('PS_LANG_DEFAULT');
        $controller = Tools::getValue('controller');
        $this->baseConfigUrl = $_SERVER['SCRIPT_NAME'].(!empty($controller) ? '?controller=' . $controller : '') . '&configure=' . $this->name . '&token=' . Tools::getValue('token');
        $this->prefixFieldsOptions = $prefixFieldsOptions;
        if ($this->prefixFieldsOptions == 'PM_MC_CSOC') {
            self::$modulePrefix = 'MC_CSOC';
        }
        $this->displayName = $this->l('Cross Selling On Cart');
        $this->description = $this->l('Display a selection of products on the cart summary');
        $docUrlTab = array();
        $docUrlTab['fr'] = '#/fr/cross-selling-on-cart/';
        $docUrlTab['en'] = '#/en/cross-selling-on-cart-3/';
        $docUrl = $docUrlTab['en'];
        if ($this->isoLang == 'fr') {
            $docUrl = $docUrlTab['fr'];
        }
        $forumUrlTab = array();
        $forumUrlTab['fr'] = 'http://www.prestashop.com/forums/topic/102385-module-pm-cross-selling-on-cart-est-maintenant-compatible-avec-modalcart/';
        $forumUrlTab['en'] = 'http://www.prestashop.com/forums/topic/102388-module-cross-selling-on-cart/';
        $forumUrl = $forumUrlTab['en'];
        if ($this->isoLang == 'fr') {
            $forumUrl = $forumUrlTab['fr'];
        }
        $this->_support_link = array(
            array('link' => $forumUrl, 'target' => '_blank', 'label' => $this->l('Forum topic')),
            array('link' => $docUrl, 'target' => '_blank', 'label' => $this->l('Online documentation')),
            array('link' => 'http://addons.prestashop.com/contact-community.php?id_product=2412', 'target' => '_blank', 'label' => $this->l('Support contact')),
        );
    }
    public function install()
    {
        if ($this->prefixFieldsOptions == 'PM_CSOC') {
            Configuration::updateValue('PM_' . self::$modulePrefix . '_DISPLAY_IN_PRODUCT', false);
            Configuration::updateValue('PM_' . self::$modulePrefix . '_DISPLAY_IN_CART', true);
            Configuration::updateValue('PM_' . self::$modulePrefix . '_ACCESSORIES_IN_PRODUCT', false);
        }
        Configuration::updateValue('PM_' . self::$modulePrefix . '_TITLE_BLOC', '');
        Configuration::updateValue('PM_' . self::$modulePrefix . '_IDENTICAL_PRODUCTY', false);
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            Configuration::updateValue('PM_' . self::$modulePrefix . '_NB_PRODUCT', 4);
            Configuration::updateValue('PM_' . self::$modulePrefix . '_NB_PRODUCT_CART', 2);
            Configuration::updateValue('PM_' . self::$modulePrefix . '_IMAGE_SIZE', 'home_default');
        } else {
            Configuration::updateValue('PM_' . self::$modulePrefix . '_NB_PRODUCT', 3);
            Configuration::updateValue('PM_' . self::$modulePrefix . '_NB_PRODUCT_CART', 3);
            Configuration::updateValue('PM_' . self::$modulePrefix . '_IMAGE_SIZE', 'medium_default');
        }
        Configuration::updateValue('PM_' . self::$modulePrefix . '_NB_PRODUCT_TABLET', 2);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_NB_PRODUCT_MOBILE', 1);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_DISPLAY_IMG', true);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_DISPLAY_TITLE', true);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_DISPLAY_PRICE', true);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_DISPLAY_BUTTON', true);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_DISPLAY_AVAILABILITY', true);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_ACCESSORIES', true);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_NB_ACCESSORIES', self::DEFAULT_NB_ACCESSORIES);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_CROSSSELLING', true);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_NB_CROSSSELLING', self::DEFAULT_NB_CROSSSELLING);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_CROSSSELLING_NB_DAYS', 160);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_ADVANCED_STYLES', '');
        Configuration::updateValue('PM_' . self::$modulePrefix . '_LAST_VERSION', $this->version);
        Configuration::updateValue('PM_' . self::$modulePrefix . '_PRODUCT_SELECTION', serialize(array()));
        return parent::install() &&
        $this->registerHook('displayShoppingCartFooter') &&
        $this->registerHook('displayHeader') &&
        $this->registerHook('displayFooterProduct');
    }
    public function uninstall()
    {
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_TITLE_BLOC');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_IMAGE_SIZE');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_DISPLAY_IN_PRODUCT');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_DISPLAY_IN_CART');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_IDENTICAL_PRODUCTY');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_NB_PRODUCT');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_NB_PRODUCT_CART');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_NB_PRODUCT_TABLET');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_NB_PRODUCT_MOBILE');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_DISPLAY_IMG');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_DISPLAY_TITLE');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_DISPLAY_PRICE');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_DISPLAY_BUTTON');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_DISPLAY_AVAILABILITY');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_ACCESSORIES');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_NB_ACCESSORIES');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_ACCESSORIES_IN_PRODUCT');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_CROSSSELLING');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_NB_CROSSSELLING');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_CROSSSELLING_NB_DAYS');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_PRODUCT_SELECTION');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_ADVANCED_STYLES');
        Configuration::deleteByName('PM_' . self::$modulePrefix . '_LAST_VERSION');
        return parent::uninstall();
    }
    private function getDataSerialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_encode', array($data));
        } else {
            return current(array_map($type . '_encode', array($data)));
        }
    }
    private static function getDataUnserialized($data, $type = 'base64')
    {
        if (is_array($data)) {
            return array_map($type . '_decode', array($data));
        } else {
            return current(array_map($type . '_decode', array($data)));
        }
    }
    protected function saveAdvancedStyles($content = false)
    {
        $content = $content ? $content : Tools::getValue('PM_' . self::$modulePrefix . '_ADVANCED_STYLES');
        Configuration::updateValue('PM_'.self::$modulePrefix.'_ADVANCED_STYLES', $this->getDataSerialized($content));
        if (Shop::isFeatureActive()) {
            $contextShops = Shop::getContextListShopID();
        } else {
            $contextShops = array(1);
        }
        foreach ($contextShops as $idShop) {
            $dynamicCssFile = str_replace('.css', '-'.$idShop.'.css', dirname(__FILE__) . '/' . self::DYNAMIC_CSS);
            if (self::getAdvancedStylesDb($idShop) !== false) {
                $content = self::getAdvancedStylesDb($idShop);
            }
            file_put_contents($dynamicCssFile, $content);
        }
    }
    public static function getAdvancedStylesDb($idShop = null)
    {
        if ($idShop != null) {
            $advancedCssFileDb = Configuration::get('PM_' . self::$modulePrefix . '_ADVANCED_STYLES', null, null, $idShop);
        } else {
            $advancedCssFileDb = Configuration::get('PM_' . self::$modulePrefix . '_ADVANCED_STYLES');
        }
        if ($advancedCssFileDb !== false) {
            return trim(self::getDataUnserialized($advancedCssFileDb));
        }
        return false;
    }
    private function prepareProductForTemplate(array $rawProduct)
    {
        $productAssembler = new ProductAssembler($this->context);
        $product = $productAssembler->assembleProduct($rawProduct);
        $factory = new ProductPresenterFactory($this->context, new TaxConfiguration());
        $presenter = $factory->getPresenter();
        $settings = $factory->getPresentationSettings();
        $product = $presenter->present(
            $settings,
            $product,
            $this->context->language
        );
        if (Configuration::get('PM_' . self::$modulePrefix . '_DISPLAY_AVAILABILITY')) {
            if (Tools::isEmpty($product['availability_message'])) {
                if ($product['availability'] == 'available') {
                    $product['availability_message'] = $this->l('Available');
                } else {
                    $product['availability_message'] = $this->l('Unavailable');
                }
            }
            $product['flags'][] = array(
                'type' => $product['availability'],
                'label' => $product['availability_message'],
            );
        }
        return $product;
    }
    protected function prepareMultipleProductsForTemplate(array $products)
    {
        return array_map(array($this, 'prepareProductForTemplate'), $products);
    }
    public function getImageType($includePixelsSize = false)
    {
        $result = array();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT it. `id_image_type`, it.`name`, it.`products`, it.`width`, it.`height`
			FROM `' . _DB_PREFIX_ . 'image_type` it
			WHERE it.`products` = 1
		');
        $image = array();
        foreach ($result as $k => $img) {
            $image[$k] = array();
            $image[$k]['value'] = $img['name'];
            $image[$k]['name'] = $img['name'].($includePixelsSize ? ' ('.$img ['width'].'px * '.$img ['height'].' px)' : '');
        }
        return $image;
    }
    public function postProcess()
    {
        $formValues = $this->getConfigFormValues();
        $languages = Language::getLanguages(false);
        foreach (array_keys($formValues) as $key) {
            if ($key == 'PM_' . self::$modulePrefix . '_TITLE_BLOC') {
                $content = array();
                foreach ($languages as $lang) {
                    $idLang = $lang['id_lang'];
                    $content[$idLang] = Tools::getValue('PM_' . self::$modulePrefix . '_TITLE_BLOC' . '_' . $idLang);
                }
                Configuration::updateValue($key, $content);
            } elseif ($key == 'PM_' . self::$modulePrefix . '_PRODUCT_SELECTION') {
                $productList = explode('-', Tools::getValue('PM_CSOC_inputProducts'));
                $productListSerialized = serialize(array_filter($productList, 'strlen'));
                Configuration::updateValue($key, $productListSerialized);
            } elseif ($key == 'PM_' . self::$modulePrefix . '_ADVANCED_STYLES') {
                $this->saveAdvancedStyles();
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
        if (Tools::getIsset('dismissRating')) {
            Configuration::updateGlobalValue('PM_'.self::$modulePrefix.'_DISMISS_RATING', 1);
        }
    }
    public function loadAssets()
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $this->context->controller->addJS($this->_path . 'views/js/admin17.js');
        } else {
            $this->context->controller->addJS($this->_path . 'views/js/admin.js');
        }
        $this->context->controller->addJS($this->_path . 'views/js/codemirror/codemirror.js');
        $this->context->controller->addJS($this->_path . 'views/js/codemirror/css.js');
        $this->context->controller->addCSS($this->_path . 'views/css/codemirror/codemirror.css');
        $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
        if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            $this->context->controller->addCSS($this->_path . 'views/css/admin15.css');
        }
    }
    public function getContent()
    {
        $this->loadAssets();
        if ((bool)(Tools::isSubmit((self::$modulePrefix == 'MC_CSOC' ? 'submitOptions_PM_MC_CSOC' : 'submitpm_crosssellingoncartModule'))) == true) {
            $this->postProcess();
            if (empty($this->context->controller->errors)) {
                $this->context->controller->confirmations[] = $this->l('Configuration has successfully been saved');
            }
        }
        return $this->showRating(true) . $this->renderForm() . $this->displaySupport();
    }
    public function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        if (self::$modulePrefix == 'MC_CSOC') {
            $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure=pm_modalcart3&tab_module='.$this->tab.'&module_name=pm_modalcart3';
            $helper->submit_action = 'submitOptions_PM_MC_CSOC';
        } else {
            $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
            $helper->submit_action = 'submitpm_crosssellingoncartModule';
        }
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'module_dir' => $this->_path,
            'module_prefix' => self::$modulePrefix,
            'id_shop' => $this->context->shop->id,
            'ps_version' => _PS_VERSION_,
        );
        return $helper->generateForm(array($this->getConfigForm()));
    }
    public function unsetUselessValuesFromConfigForm($configForm)
    {
        unset($configForm['form']['input'][3]);
        unset($configForm['form']['input'][4]);
        unset($configForm['form']['input'][8]);
        unset($configForm['form']['input'][20]);
        unset($configForm['form']['input'][27]);
        unset($configForm['form']['input'][28]);
        return $configForm;
    }
    public function getConfigFormValues()
    {
        $csocTitleBloc = array();
        foreach ($this->languages as $lang) {
            $csocTitleBloc[(int)$lang['id_lang']] = (string)Configuration::get('PM_' . self::$modulePrefix . '_TITLE_BLOC', (int)$lang['id_lang']);
        }
        return array(
            'PM_' . self::$modulePrefix . '_TITLE_BLOC' => $csocTitleBloc,
            'PM_' . self::$modulePrefix . '_IMAGE_SIZE' => trim(Configuration::get('PM_' . self::$modulePrefix . '_IMAGE_SIZE')),
            'PM_' . self::$modulePrefix . '_DISPLAY_IN_PRODUCT' => (int)Configuration::get('PM_' . self::$modulePrefix . '_DISPLAY_IN_PRODUCT'),
            'PM_' . self::$modulePrefix . '_DISPLAY_IN_CART' => (int)Configuration::get('PM_' . self::$modulePrefix . '_DISPLAY_IN_CART'),
            'PM_' . self::$modulePrefix . '_IDENTICAL_PRODUCTY' => (int)Configuration::get('PM_' . self::$modulePrefix . '_IDENTICAL_PRODUCTY'),
            'PM_' . self::$modulePrefix . '_NB_PRODUCT' => (int)Configuration::get('PM_' . self::$modulePrefix . '_NB_PRODUCT'),
            'PM_' . self::$modulePrefix . '_NB_PRODUCT_CART' => (int)Configuration::get('PM_' . self::$modulePrefix . '_NB_PRODUCT_CART'),
            'PM_' . self::$modulePrefix . '_NB_PRODUCT_TABLET' => (int)Configuration::get('PM_' . self::$modulePrefix . '_NB_PRODUCT_TABLET'),
            'PM_' . self::$modulePrefix . '_NB_PRODUCT_MOBILE' => (int)Configuration::get('PM_' . self::$modulePrefix . '_NB_PRODUCT_MOBILE'),
            'PM_' . self::$modulePrefix . '_DISPLAY_IMG' => (int)Configuration::get('PM_' . self::$modulePrefix . '_DISPLAY_IMG'),
            'PM_' . self::$modulePrefix . '_DISPLAY_TITLE' => (int)Configuration::get('PM_' . self::$modulePrefix . '_DISPLAY_TITLE'),
            'PM_' . self::$modulePrefix . '_DISPLAY_PRICE' => (int)Configuration::get('PM_' . self::$modulePrefix . '_DISPLAY_PRICE'),
            'PM_' . self::$modulePrefix . '_DISPLAY_BUTTON' => (int)Configuration::get('PM_' . self::$modulePrefix . '_DISPLAY_BUTTON'),
            'PM_' . self::$modulePrefix . '_DISPLAY_AVAILABILITY' => (int)Configuration::get('PM_' . self::$modulePrefix . '_DISPLAY_AVAILABILITY'),
            'PM_' . self::$modulePrefix . '_ACCESSORIES' => (int)Configuration::get('PM_' . self::$modulePrefix . '_ACCESSORIES'),
            'PM_' . self::$modulePrefix . '_NB_ACCESSORIES' => (int)Configuration::get('PM_' . self::$modulePrefix . '_NB_ACCESSORIES'),
            'PM_' . self::$modulePrefix . '_ACCESSORIES_IN_PRODUCT' => (int)Configuration::get('PM_' . self::$modulePrefix . '_ACCESSORIES_IN_PRODUCT'),
            'PM_' . self::$modulePrefix . '_CROSSSELLING' => (int)Configuration::get('PM_' . self::$modulePrefix . '_CROSSSELLING'),
            'PM_' . self::$modulePrefix . '_NB_CROSSSELLING' => (int)Configuration::get('PM_' . self::$modulePrefix . '_NB_CROSSSELLING'),
            'PM_' . self::$modulePrefix . '_CROSSSELLING_NB_DAYS' => (int)Configuration::get('PM_' . self::$modulePrefix . '_CROSSSELLING_NB_DAYS'),
            'PM_' . self::$modulePrefix . '_PRODUCT_SELECTION' => trim(Configuration::get('PM_' . self::$modulePrefix . '_PRODUCT_SELECTION')),
            'PM_' . self::$modulePrefix . '_ADVANCED_STYLES' => $this->getAdvancedStylesDb(),
        );
    }
    protected function getConfigForm()
    {
        $configForm = array(
            'form' => array(
                'tabs' => array(
                    'settings' => $this->l('Settings'),
                    'style' => $this->l('Advanced Styles'),
                ),
                'input' => array(
                    array(
                        'type' => 'html',
                        'html_content' => '<h2>'. $this->l('General options') .'</h2>',
                        'name' => '',
                        'tab' => 'settings',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'PM_' . self::$modulePrefix . '_TITLE_BLOC',
                        'label' => $this->l('Title block'),
                        'lang' => true,
                        'tab' => 'settings',
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'PM_' . self::$modulePrefix . '_IMAGE_SIZE',
                        'label' => $this->l('Product image size'),
                        'desc' => $this->l('Choose the product image size'),
                        'tab'=> 'settings',
                        'class' => 'fixed-width-lg',
                        'options' => array(
                            'query' => self::getImageType(true),
                            'id' => 'value',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'name' => 'PM_' . self::$modulePrefix . '_DISPLAY_IN_PRODUCT',
                        'label' => $this->l('Display cross-selling on product pages'),
                        'desc' => $this->l('When on, a cross selling block is displayed at the bottom of product pages'),
                        'is_bool' => true,
                        'tab' => 'settings',
                        'values' => array(
                            array(
                              'id'    => 'active_on',
                              'value' => true,
                              'label' => $this->l('Yes'),
                            ),
                            array(
                              'id'    => 'active_off',
                              'value' => false,
                              'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display cross-selling on cart pages'),
                        'name' => 'PM_' . self::$modulePrefix . '_DISPLAY_IN_CART',
                        'is_bool' => true,
                        'tab' => 'settings',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display products that are already in the cart'),
                        'name' => 'PM_' . self::$modulePrefix . '_IDENTICAL_PRODUCTY',
                        'is_bool' => true,
                        'tab' => 'settings',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'html',
                        'html_content' => '<h2>'. $this->l('Number of products to be displayed simultaneously') .'</h2>',
                        'name' => '',
                        'tab' => 'settings',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'PM_' . self::$modulePrefix . '_NB_PRODUCT',
                        'label' => $this->l('On computers (product page)'),
                        'tab' => 'settings',
                        'class' => 'fixed-width-sm',
                        'suffix' => $this->l('products'),
                        'maxlength' => 2,
                        'form_group_class' => 'pm_csoc_product_options',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'PM_' . self::$modulePrefix . '_NB_PRODUCT_CART',
                        'label' => $this->l('On computers (cart page)'),
                        'tab' => 'settings',
                        'class' => 'fixed-width-sm',
                        'suffix' => $this->l('products'),
                        'maxlength' => 2,
                        'form_group_class' => 'pm_csoc_cart_options',
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'PM_' . self::$modulePrefix . '_NB_PRODUCT_TABLET',
                        'label' => $this->l('On tablets'),
                        'tab' => 'settings',
                        'class' => 'fixed-width-sm',
                        'suffix' => $this->l('products'),
                        'maxlength' => 2,
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'PM_' . self::$modulePrefix . '_NB_PRODUCT_MOBILE',
                        'label' => $this->l('On mobiles'),
                        'tab' => 'settings',
                        'class' => 'fixed-width-sm',
                        'suffix' => $this->l('products'),
                        'maxlength' => 2,
                    ),
                    array(
                        'type' => 'html',
                        'html_content' => '<h2>'. $this->l('Product appearance') .'</h2>',
                        'name' => '',
                        'tab' => 'settings',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display image ?'),
                        'name' => 'PM_' . self::$modulePrefix . '_DISPLAY_IMG',
                        'is_bool' => true,
                        'tab' => 'settings',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display title ?'),
                        'name' => 'PM_' . self::$modulePrefix . '_DISPLAY_TITLE',
                        'is_bool' => true,
                        'tab' => 'settings',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display price ?'),
                        'name' => 'PM_' . self::$modulePrefix . '_DISPLAY_PRICE',
                        'is_bool' => true,
                        'tab' => 'settings',
                        'class' => 'col-lg-2',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display "Add to Cart" button ?'),
                        'name' => 'PM_' . self::$modulePrefix . '_DISPLAY_BUTTON',
                        'is_bool' => true,
                        'tab' => 'settings',
                        'class' => 'col-lg-2',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display availability ?'),
                        'name' => 'PM_' . self::$modulePrefix . '_DISPLAY_AVAILABILITY',
                        'is_bool' => true,
                        'tab' => 'settings',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'html',
                        'html_content' => '<h2>'. $this->l('Accessories') .'</h2>',
                        'name' => '',
                        'tab' => 'settings',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display products\' accessories ?'),
                        'name' => 'PM_' . self::$modulePrefix . '_ACCESSORIES',
                        'is_bool' => true,
                        'tab' => 'settings',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'PM_' . self::$modulePrefix . '_NB_ACCESSORIES',
                        'label' => $this->l('Maximum number of accessories to be displayed'),
                        'tab' => 'settings',
                        'class' => 'fixed-width-xs',
                        'maxlength' => 2,
                        'form_group_class' => 'pm_csoc_accessories_options',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Force display of accessories in product page ?'),
                        'name' => 'PM_' . self::$modulePrefix . '_ACCESSORIES_IN_PRODUCT',
                        'is_bool' => true,
                        'tab' => 'settings',
                        'form_group_class' => 'pm_csoc_accessories_options',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'html',
                        'html_content' => '<h2>'. $this->l('Products often purchased together') .'</h2>',
                        'name' => '',
                        'tab' => 'settings',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Suggest products that are often purchased together ?'),
                        'name' => 'PM_' . self::$modulePrefix . '_CROSSSELLING',
                        'is_bool' => true,
                        'tab' => 'settings',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'PM_' . self::$modulePrefix . '_NB_CROSSSELLING',
                        'label' => $this->l('Maximum number of products to be displayed'),
                        'tab' => 'settings',
                        'class' => 'fixed-width-xs',
                        'form_group_class' => 'pm_csoc_crossselling_options',
                        'maxlength' => 2,
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'PM_' . self::$modulePrefix . '_CROSSSELLING_NB_DAYS',
                        'label' => $this->l('Use valid orders less than X days old'),
                        'desc' => $this->l('Min: 1 - Max: 200'),
                        'tab' => 'settings',
                        'class' => 'fixed-width-xs',
                        'suffix' => $this->l('days'),
                        'form_group_class' => 'pm_csoc_crossselling_options',
                        'maxlength' => 3,
                    ),
                    array(
                        'type' => 'html',
                        'html_content' => '<h2>'. $this->l('Compulsory products') .'</h2>',
                        'name' => '',
                        'tab' => 'settings',
                    ),
                    array(
                        'type' => 'produitsimposes',
                        'name' => 'PM_' . self::$modulePrefix . '_PRODUCT_SELECTION',
                        'label' => $this->l('Products to be imposed'),
                        'tab' => 'settings',
                        'values' => $this->getCompulsoryProductList(),
                        'form_group_class' => 'clearfix',
                    ),
                    array(
                        'type' => 'html',
                        'html_content' => '<h2>' . $this->l('Advanced Styles (CSS)') . '</h2><p class="help-block">'. $this->l('Enter your CSS rules here :') .'</p>',
                        'desc' => $this->l('Enter your CSS rules here :'),
                        'name' => '',
                        'tab' => 'style',
                    ),
                    array(
                        'type' => 'advancedstyles',
                        'id' => 'test',
                        'name' => 'PM_'. self::$modulePrefix . '_ADVANCED_STYLES',
                        'tab' => 'style',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        if (self::$modulePrefix == 'MC_CSOC') {
            return $this->unsetUselessValuesFromConfigForm($configForm);
        } else {
            return $configForm;
        }
    }
    protected function getUnserializedProductList()
    {
        return Tools::unSerialize(Configuration::get('PM_' . self::$modulePrefix . '_PRODUCT_SELECTION'));
    }
    protected function getCompulsoryProductList()
    {
        $productList = $this->getUnserializedProductList();
        $listToBeDisplayed = array();
        if (!empty($productList)) {
            foreach ($productList as $idProduct) {
                $product = new Product($idProduct, false, $this->context->employee->id_lang);
                if (Validate::isLoadedObject($product)) {
                    $listToBeDisplayed[] = array(
                        'id' => $product->id,
                        'name' => $product->name . " (ref: " . $product->reference . ")",
                        'id_image' => $product->getCoverWs(),
                    );
                }
            }
        }
        return $listToBeDisplayed;
    }
    public function getElementProducts($products, $idLang, $getProductsProperties = true)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, MAX(product_attribute_shop.id_product_attribute) id_product_attribute, product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity, pl.`description`, pl.`description_short`, pl.`available_now`,
				pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, MAX(image_shop.`id_image`) id_image,
				il.`legend`, m.`name` AS manufacturer_name, cl.`name` AS category_default,
				DATEDIFF(product_shop.`date_add`, DATE_SUB("'.date('Y-m-d').' 00:00:00",
				INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new,
				product_shop.price AS orderprice
			FROM `' . _DB_PREFIX_ . 'product` p
			LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product`)
			' . $this->context->shop->addSqlAssociation('product', 'p') . '
			' . Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1') . '
			' . Product::sqlStock('p', 'product_attribute_shop', false, $this->context->shop) . '
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
				p.`id_product` = pl.`id_product`
				AND pl.`id_lang` = ' . (int)$idLang . $this->context->shop->addSqlRestrictionOnLang('pl') . '
			)
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (
				p.`id_category_default` = cl.`id_category`
				AND cl.`id_lang` = ' . (int)$idLang . $this->context->shop->addSqlRestrictionOnLang('cl') . '
			)
			LEFT JOIN `'._DB_PREFIX_.'image` i ON (p.`id_product` = i.`id_product`)
			' . Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1') . '
			LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)$idLang . ')
			LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
			LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
				AND tr.`id_country` = ' . (int)$this->context->country->id . '
				AND tr.`id_state` = 0
			)
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
			LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = ' . (int)$idLang . ')
			WHERE product_shop.`id_shop` = ' . (int)$this->context->shop->id . '
			AND product_shop.`active` = 1
			AND product_shop.`available_for_order` = 1 
			AND product_shop.`id_product` IN (' . implode(',', array_map('intval', $products)) . ')
			AND product_shop.`visibility` IN ("both", "catalog") '
            . (Configuration::get('PS_STOCK_MANAGEMENT') ? '
			AND IF (
				(stock.`id_product_attribute` = 0 AND pa.`id_product_attribute` IS NULL)
				OR 
				(product_attribute_shop.`id_product_attribute` IS NOT NULL AND stock.`id_product_attribute`=product_attribute_shop.`id_product_attribute`), 1, 0
			)
			AND IF (stock.`quantity` > 0, 1, IF (stock.`out_of_stock` = 2, ' . (int)Configuration::get('PS_ORDER_OUT_OF_STOCK') . ' = 1, stock.`out_of_stock` = 1))
			' : '') . ' 
			GROUP BY product_shop.`id_product`
			ORDER BY FIELD(product_shop.`id_product`, '.implode(',', array_map('intval', $products)) . ')
		');
        if ($getProductsProperties && version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $result = $this->prepareMultipleProductsForTemplate($result);
        }
        if ($getProductsProperties) {
            return Product::getProductsProperties($idLang, $result);
        } else {
            return $result;
        }
    }
    private function getAccessoriesLight($productList, $limit, $ignoreList = array())
    {
        if (!self::isFilledArray($productList)) {
            return false;
        }
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT `id_product_2` AS id_product
		FROM `'._DB_PREFIX_.'accessory` a
		LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = a.`id_product_2`)
		LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = p.`id_category_default` AND cp.`id_product` = a.`id_product_2`)
		LEFT JOIN `'._DB_PREFIX_.'category_group` ctg ON (ctg.`id_category` = cp.`id_category` AND ctg.`id_group` = '.(int)Group::getCurrent()->id.')
		WHERE `id_product_1` IN ('.(implode(',', array_map('intval', $productList))).')
		'.(self::isFilledArray($ignoreList) ? 'AND `id_product_2` NOT IN ('.(implode(',', array_map('intval', $ignoreList))).')' : '').'
		AND ctg.id_group IS NOT NULL
		ORDER BY RAND()
		LIMIT '.(int)$limit);
    }
    private function getCrossSellingLight($productList, $limit)
    {
        if (!self::isFilledArray($productList)) {
            return false;
        }
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT DISTINCT od.product_id 
			FROM '._DB_PREFIX_.'order_detail od 
			LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = od.`product_id`)
			LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = p.`id_category_default` AND cp.`id_product` = od.`product_id`)
			LEFT JOIN `'._DB_PREFIX_.'category_group` ctg ON (ctg.`id_category` = cp.`id_category` AND ctg.`id_group` = '.(int)Group::getCurrent()->id.')
			WHERE od.product_id NOT IN ('.implode(',', array_map('intval', $productList)).') AND od.id_order IN (
				SELECT od.id_order FROM '._DB_PREFIX_.'orders o
				JOIN '._DB_PREFIX_.'order_detail od ON (od.id_order = o.id_order)
				WHERE o.valid = 1 AND od.product_id IN ('.implode(',', array_map('intval', $productList)).') AND TO_DAYS("'.date('Y-m-d').' 00:00:00") - TO_DAYS(o.date_add) <= 182
			)
		 	AND ctg.id_group IS NOT NULL 
			LIMIT '.(int)$limit);
    }
    public static function isFilledArray($array)
    {
        return ($array && is_array($array) && sizeof($array));
    }
    protected function showRating($show = false)
    {
        $dismiss = (int)Configuration::getGlobalValue('PM_'.self::$modulePrefix.'_DISMISS_RATING');
        if ($show && $dismiss != 1 && $this->getNbDaysModuleUsage() >= 3) {
            return $this->fetchTemplate('core/rating.tpl');
        }
        return '';
    }
    private function getNbDaysModuleUsage()
    {
        $sql = 'SELECT DATEDIFF(NOW(),date_add)
                FROM '._DB_PREFIX_.'configuration
                WHERE name = \''.pSQL('PM_'.self::$modulePrefix.'_LAST_VERSION').'\'
                ORDER BY date_add ASC';
        return (int)Db::getInstance()->getValue($sql);
    }
    protected function fetchTemplate($tpl, $customVars = array(), $configOptions = array())
    {
        $this->context->smarty->assign(array(
            'ps_major_version' => Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2),
            'module_name' => $this->name,
            'module_path' => $this->_path,
            'current_iso_lang' => $this->context->language->iso_code,
            'current_id_lang' => (int)$this->context->language->id,
            'options' => $configOptions,
            'languages' => $this->languages,
            'base_config_url' => $this->baseConfigUrl,
            'default_language' => $this->defaultLanguage,
        ));
        if (sizeof($customVars)) {
            $this->context->smarty->assign($customVars);
        }
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/' . $tpl);
    }
    
    private function getPMdata()
    {
        $param = array();
        $param[] = 'ver-'._PS_VERSION_;
        $param[] = 'current-'.$this->name;
        
        $result = $this->getPMAddons();
        if ($result && is_array($result) && sizeof($result)) {
            foreach ($result as $moduleName => $moduleVersion) {
                $param[] = $moduleName . '-' . $moduleVersion;
            }
        }
        return $this->getDataSerialized(implode('|', $param));
    }
    private function getPMAddons()
    {
        $pmAddons = array();
        $result = Db::getInstance()->ExecuteS('SELECT DISTINCT name FROM '._DB_PREFIX_.'module WHERE name LIKE "pm_%"');
        if ($result && is_array($result) && sizeof($result)) {
            foreach ($result as $module) {
                $instance = Module::getInstanceByName($module['name']);
                if ($instance && isset($instance->version)) {
                    $pmAddons[$module['name']] = $instance->version;
                }
            }
        }
        return $pmAddons;
    }
    private function doHttpRequest($data = array(), $c = 'prestashop', $s = 'api.addons')
    {
        $data = array_merge(array(
            'version' => _PS_VERSION_,
            'iso_lang' => Tools::strtolower($this->context->language->iso_code),
            'iso_code' => Tools::strtolower(Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'))),
            'module_key' => $this->module_key,
            'method' => 'contributor',
            'action' => 'all_products',
        ), $data);
        $postData = http_build_query($data);
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => $postData,
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'timeout' => 15,
            )
        ));
        $response = Tools::file_get_contents('https://' . $s . '.' . $c . '.com', false, $context);
        if (empty($response)) {
            return false;
        }
        $responseToJson = Tools::jsonDecode($response);
        if (empty($responseToJson)) {
            return false;
        }
        return $responseToJson;
    }
    private function getAddonsModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$modulePrefix . '_AM');
        $modules_date = Configuration::get('PM_' . self::$modulePrefix . '_AMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return Tools::jsonDecode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest();
        if (empty($jsonResponse->products)) {
            return array();
        }
        $dataToStore = array();
        foreach ($jsonResponse->products as $addonsEntry) {
            $dataToStore[(int)$addonsEntry->id] = array(
                'name' => $addonsEntry->name,
                'displayName' => $addonsEntry->displayName,
                'url' => $addonsEntry->url,
                'compatibility' => $addonsEntry->compatibility,
                'version' => $addonsEntry->version,
                'description' => $addonsEntry->description,
            );
        }
        Configuration::updateValue('PM_' . self::$modulePrefix . '_AM', Tools::jsonEncode($dataToStore));
        Configuration::updateValue('PM_' . self::$modulePrefix . '_AMD', time());
        return Tools::jsonDecode(Configuration::get('PM_' . self::$modulePrefix . '_AM'), true);
    }
    private function getPMModulesFromApi()
    {
        $modules = Configuration::get('PM_' . self::$modulePrefix . '_PMM');
        $modules_date = Configuration::get('PM_' . self::$modulePrefix . '_PMMD');
        if ($modules && strtotime('+2 day', $modules_date) > time()) {
            return Tools::jsonDecode($modules, true);
        }
        $jsonResponse = $this->doHttpRequest(array('list' => $this->getPMAddons()), 'presta-module', 'api-addons');
        if (empty($jsonResponse)) {
            return array();
        }
        Configuration::updateValue('PM_' . self::$modulePrefix . '_PMM', Tools::jsonEncode($jsonResponse));
        Configuration::updateValue('PM_' . self::$modulePrefix . '_PMMD', time());
        return Tools::jsonDecode(Configuration::get('PM_' . self::$modulePrefix . '_PMM'), true);
    }
    private function shuffleArray(&$a)
    {
        if (is_array($a) && sizeof($a)) {
            $ks = array_keys($a);
            shuffle($ks);
            $new = array();
            foreach ($ks as $k) {
                $new[$k] = $a[$k];
            }
            $a = $new;
            return true;
        }
        return false;
    }
    private function displaySupport()
    {
        $pm_addons_products = $this->getAddonsModulesFromApi();
        $pm_products = $this->getPMModulesFromApi();
        if (!is_array($pm_addons_products)) {
            $pm_addons_products = array();
        }
        if (!is_array($pm_products)) {
            $pm_products = array();
        }
        $this->shuffleArray($pm_addons_products);
        if (is_array($pm_addons_products)) {
            if (!empty($pm_products['ignoreList']) && is_array($pm_products['ignoreList']) && sizeof($pm_products['ignoreList'])) {
                foreach ($pm_products['ignoreList'] as $ignoreId) {
                    if (isset($pm_addons_products[$ignoreId])) {
                        unset($pm_addons_products[$ignoreId]);
                    }
                }
            }
            $addonsList = $this->getPMAddons();
            if ($addonsList && is_array($addonsList) && sizeof($addonsList)) {
                foreach (array_keys($addonsList) as $moduleName) {
                    foreach ($pm_addons_products as $k => $pm_addons_product) {
                        if ($pm_addons_product['name'] == $moduleName) {
                            unset($pm_addons_products[$k]);
                            break;
                        }
                    }
                }
            }
        }
        $vars = array(
            'support_links' => (is_array($this->_support_link) && sizeof($this->_support_link) ? $this->_support_link : array()),
            'copyright_link' => (is_array($this->copyrightLink) && sizeof($this->copyrightLink) ? $this->copyrightLink : false),
            'pm_module_version' => $this->version,
            'pm_data' => $this->getPMdata(),
            'pm_products' => $pm_products,
            'pm_addons_products' => $pm_addons_products,
        );
        return $this->fetchTemplate('core/support.tpl', $vars);
    }
    public function displayShareConfig($baseConfigUrl)
    {
        if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            $adminMC3CSS = '<link rel="stylesheet" type="text/css" href="../modules/pm_crosssellingoncart/views/css/adminMC315.css" />';
        } else {
            $adminMC3CSS = '<link rel="stylesheet" type="text/css" href="../modules/pm_crosssellingoncart/views/css/adminMC3.css" />';
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $adminCSOCMCJS = '<script type="text/javascript" src="../modules/pm_crosssellingoncart/views/js/admin17.js"></script>';
        } else {
            $adminCSOCMCJS = '<script type="text/javascript" src="../modules/pm_crosssellingoncart/views/js/admin.js"></script>';
        }
        return $adminMC3CSS . $adminCSOCMCJS .  $this->renderForm();
    }
    public function saveConfig()
    {
        return $this->postProcess();
    }
    public function hookDisplayHeader($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $this->context->smarty->unregisterPlugin('function', 'displayWtPrice');
            $this->context->smarty->registerPlugin('function', 'displayWtPrice', array('Product', 'displayWtPrice'));
            $this->context->smarty->unregisterPlugin('function', 'convertPrice');
            $this->context->smarty->registerPlugin('function', 'convertPrice', array('Product', 'convertPrice'));
            $hookParams = array();
            $hookParams['display'] = false;
            if ($this->context->controller instanceof ProductController) {
                $hookParams['on_product_page'] = true;
                $hookParams['id_product'] = Tools::getValue('id_product', 0);
            }
            $jsDefs = $this->hookDisplayShoppingCartFooter($hookParams);
            Media::addJsDef(array('pm_crosssellingoncart' => $jsDefs));
            $this->context->controller->registerJavascript('modules-pm_crosssellingoncart-front', 'modules/pm_crosssellingoncart/views/js/front.js', array('server' => 'local', 'position' => 'bottom', 'priority' => 150));
        }
        $this->context->controller->addJS($this->_path . 'views/js/owl-carousel/owl.carousel.min.js');
        if (isset($this->context->controller->php_self) && ($this->context->controller->php_self == 'order' || $this->context->controller->php_self == 'order-opc')) {
            $this->context->controller->addJS($this->_path . 'views/js/pm_crosssellingoncart.js');
        }
        $this->context->controller->addCSS($this->_path . 'views/css/owl-carousel/owl.carousel.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/owl-carousel/owl.theme.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/pm_crosssellingoncart.css', 'all');
        $this->context->controller->addCSS($this->_path .str_replace('.css', '-'.$this->context->shop->id.'.css', self::DYNAMIC_CSS), 'all');
    }
    public function hookDisplayShoppingCartFooter($params)
    {
        if ($this->prefixFieldsOptions != 'PM_MC_CSOC' && !Configuration::get('PM_' . self::$modulePrefix . '_DISPLAY_IN_CART') && !isset($params['id_product'])) {
            return false;
        }
        if (isset($params['products'])) {
            $cart_products = $params['products'];
        } else {
            $cart = $this->context->cart;
            $cart_products = $cart->getProducts(true);
        }
        $csoc_display = Configuration::getMultiple(array(
            'PM_' . self::$modulePrefix . '_DISPLAY_IMG',
            'PM_' . self::$modulePrefix . '_DISPLAY_TITLE',
            'PM_' . self::$modulePrefix . '_DISPLAY_PRICE',
            'PM_' . self::$modulePrefix . '_DISPLAY_BUTTON',
            'PM_' . self::$modulePrefix . '_DISPLAY_AVAILABILITY',
        ));
        if (!$csoc_display['PM_' . self::$modulePrefix . '_DISPLAY_IMG']
            && !$csoc_display['PM_' . self::$modulePrefix . '_DISPLAY_TITLE']
            && !$csoc_display['PM_' . self::$modulePrefix . '_DISPLAY_PRICE']
            && !$csoc_display['PM_' . self::$modulePrefix . '_DISPLAY_BUTTON']
            && !$csoc_display['PM_' . self::$modulePrefix . '_DISPLAY_AVAILABILITY']
        ) {
            return false;
        }
        $displayProductAlreadyInCart = (bool)Configuration::get('PM_' . self::$modulePrefix . '_IDENTICAL_PRODUCTY');
        $nbProductSelection = Configuration::get('PM_' . self::$modulePrefix . '_NB_PRODUCT');
        $nbProductSelectionCart = Configuration::get('PM_' . self::$modulePrefix . '_NB_PRODUCT_CART');
        $nbProductSelectionTablet = Configuration::get('PM_' . self::$modulePrefix . '_NB_PRODUCT_TABLET');
        $nbProductSelectionMobile = Configuration::get('PM_' . self::$modulePrefix . '_NB_PRODUCT_MOBILE');
        $nbAccessories = Configuration::get('PM_' . self::$modulePrefix . '_NB_ACCESSORIES');
        if (!$nbAccessories) {
            $nbAccessories = (int)self::DEFAULT_NB_ACCESSORIES;
        }
        $nbCrossSelling = Configuration::get('PM_' . self::$modulePrefix . '_NB_CROSSSELLING');
        if (!$nbCrossSelling) {
            $nbCrossSelling = (int)self::DEFAULT_NB_CROSSSELLING;
        }
        $imageSize = Configuration::get('PM_' . self::$modulePrefix . '_IMAGE_SIZE');
        $blockTitle = Configuration::get('PM_' . self::$modulePrefix . '_TITLE_BLOC', $this->context->cookie->id_lang);
        $postProductSelection = @array_filter(unserialize(Configuration::get('PM_' . self::$modulePrefix . '_PRODUCT_SELECTION')));
        $productSelection = array();
        foreach ($postProductSelection as $k => $idProduct) {
            if (version_compare(_PS_VERSION_, '1.6.0.12', '>=')) {
                if (!Product::checkAccessStatic((int)$idProduct, false)) {
                    unset($postProductSelection[$k]);
                }
            } else {
                $product = new Product((int)$idProduct);
                if (!$product->checkAccess(false)) {
                    unset($postProductSelection[$k]);
                }
            }
        }
        $productsOnCart = array();
        foreach ($cart_products as $k => $v) {
            $productsOnCart[] = $v['id_product'];
        }
        $productArray = array();
        if (isset($params['id_product'])) {
            $productArray[] = (int)$params['id_product'];
            $productsOnCart[] = (int)$params['id_product'];
        }
        $productsOnCart = array_unique($productsOnCart);
        $ForceShowAccessories = true;
        if ((isset($params['on_product_page']) && $params['on_product_page'] && !(int)Configuration::get('PM_' . self::$modulePrefix . '_ACCESSORIES_IN_PRODUCT'))) {
            $ForceShowAccessories = false;
        }
        if ($ForceShowAccessories && Configuration::get('PM_' . self::$modulePrefix . '_ACCESSORIES')) {
            if (isset($params['on_product_page']) && $params['on_product_page']) {
                $productAccessories = $this->getAccessoriesLight($productArray, $nbAccessories);
            } else {
                $productAccessories = $this->getAccessoriesLight($productsOnCart, $nbAccessories, $productArray);
            }
            if (self::isFilledArray($productAccessories)) {
                foreach ($productAccessories as $product) {
                    $postProductSelection[] = (int)$product['id_product'];
                }
            }
        }
        if (Configuration::get('PM_' . self::$modulePrefix . '_CROSSSELLING')) {
            $productsCrossSelling = $this->getCrossSellingLight($productsOnCart, $nbCrossSelling);
            if (self::isFilledArray($productsCrossSelling)) {
                foreach ($productsCrossSelling as $product) {
                    $postProductSelection[] = (int)$product['product_id'];
                }
            }
        }
        if (self::isFilledArray($postProductSelection)) {
            $postProductSelection = array_unique($postProductSelection);
            if (!$displayProductAlreadyInCart) {
                $postProductSelection = array_diff($postProductSelection, $productsOnCart);
            }
            if (isset($params['id_product']) && $params['id_product']) {
                $key = array_search((int)$params['id_product'], $postProductSelection);
                if ($key !== false) {
                    unset($postProductSelection[(int)$key]);
                }
            }
            if (self::isFilledArray($postProductSelection)) {
                $productSelection = $this->getElementProducts($postProductSelection, $this->context->cookie->id_lang);
            }
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            foreach ($productSelection as $id => $product) {
                $productSelection[$id]['price'] = Tools::displayPrice($product['price']);
            }
        }
        if (count($productSelection) <= 0) {
            return false;
        }
        $this->context->smarty->assign(array(
            'csoc_order_page_link' => $this->context->link->getPageLink((isset($this->context->controller->php_self) && ($this->context->controller->php_self == 'order' || $this->context->controller->php_self == 'order-opc') ? $this->context->controller->php_self : 'order')),
            'csoc_product_selection' => $productSelection,
            'csoc_bloc_title' => $blockTitle,
            'csoc_products_quantity' => (isset($params['on_product_page']) ? (int)$nbProductSelection : (int)$nbProductSelectionCart),
            'csoc_products_quantity_tablet' => $nbProductSelectionTablet,
            'csoc_products_quantity_mobile' => $nbProductSelectionMobile,
            'csoc_prefix' => 'PM_' . self::$modulePrefix,
            'tax_enabled' => Configuration::get('PS_TAX'),
            'imageSize' => $imageSize,
            'ps_version' => _PS_VERSION_,
            'csoc_static_token' => Tools::getToken(false),
            'csoc_display' => $csoc_display,
            'on_product_page' => (isset($params['on_product_page']) ? $params['on_product_page'] : false),
        ));
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $imageType = array('type' => $imageSize);
            $this->context->smarty->assign(array(
                'widthSize' => Image::getWidth($imageType),
            ));
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            if (isset($params['display']) && !$params['display']) {
                $jsDefs = array();
                $jsDefs['prefix'] = '#'.$this->prefixFieldsOptions;
                $jsDefs['product_selection'] = $productSelection;
                $jsDefs['products_quantity'] = (isset($params['on_product_page']) && $params['on_product_page'] ? (int)$nbProductSelection : (int)$nbProductSelectionCart);
                $jsDefs['products_quantity_tablet'] = (int)$nbProductSelectionTablet;
                $jsDefs['products_quantity_mobile'] = (int)$nbProductSelectionMobile;
                $jsDefs['order_page_link'] = $this->context->link->getPageLink((isset($this->context->controller->php_self) && ($this->context->controller->php_self == 'order' || $this->context->controller->php_self == 'order-opc') ? $this->context->controller->php_self : 'order'));
                $jsDefs['nbItems'] = ((int)count($productSelection) < (int)$jsDefs['products_quantity'] ? (int)count($productSelection) : (int)$jsDefs['products_quantity']);
                return $jsDefs;
            } else {
                return $this->display(__FILE__, 'pm_crosssellingoncart-17.tpl');
            }
        } elseif (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            return $this->display(__FILE__, 'pm_crosssellingoncart-16.tpl');
        } else {
            return $this->display(__FILE__, 'pm_crosssellingoncart.tpl');
        }
    }
    public function hookMCBelow($params)
    {
        $this->prefixFieldsOptions = 'PM_MC_CSOC';
        self::$modulePrefix = 'MC_CSOC';
        return $this->hookDisplayShoppingCartFooter($params);
    }
    public function hookDisplayFooterProduct()
    {
        if (Configuration::get('PM_' . self::$modulePrefix . '_DISPLAY_IN_PRODUCT')) {
            return $this->hookDisplayShoppingCartFooter(array('on_product_page' => true, 'id_product' => Tools::getValue('id_product')));
        }
    }
}
