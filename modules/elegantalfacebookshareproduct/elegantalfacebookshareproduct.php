<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2018, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

require_once('initialize.php');

/**
 * Main class of the module
 */
class ElegantalFacebookShareProduct extends ElegantalFacebookShareProductModule
{

    /**
     * ID of this module as product on addons
     * @var int
     */
    protected $productIdOnAddons = 27249;

    /**
     * List of hooks to register
     * @var array
     */
    protected $hooksToRegister = array(
        'displayHeader',
        'displayShoppingCart',
    );

    /**
     * List of module settings to be saved as Configuration record
     * @var array
     */
    protected $settings = array(
        'fb_app_id' => '',
        'fb_event' => 'share', // share | like
        'display_type' => 'popup',
        'cart_rule_ids' => array(),
        'is_share_shop_url' => 1,
        'url_to_share' => '',
        'locale' => 'en_US',
        'active' => 1,
        'debug_mode' => 1,
    );

    /**
     * Supported locales: https://gist.github.com/mechastorm/3626739
     * @var array
     */
    protected $locales = array(
        'af_ZA' => 'Afrikaans',
        'sq_AL' => 'Albanian',
        'ar_AR' => 'Arabic',
        'hy_AM' => 'Armenian',
        'az_AZ' => 'Azerbaijani',
        'eu_ES' => 'Basque',
        'be_BY' => 'Belarusian',
        'bn_IN' => 'Bengali',
        'bs_BA' => 'Bosnian',
        'bg_BG' => 'Bulgarian',
        'ca_ES' => 'Catalan',
        'zh_CN' => 'Chinese',
        'hr_HR' => 'Croatian',
        'cs_CZ' => 'Czech',
        'da_DK' => 'Danish',
        'nl_NL' => 'Dutch',
        'en_US' => 'English',
        'es_ES' => 'Español',
        'eo_EO' => 'Esperanto',
        'et_EE' => 'Estonian',
        'fo_FO' => 'Faroese',
        'tl_PH' => 'Filipino',
        'fi_FI' => 'Finnish',
        'fr_FR' => 'French',
        'fy_NL' => 'Frisian',
        'gl_ES' => 'Galician',
        'ka_GE' => 'Georgian',
        'de_DE' => 'German',
        'el_GR' => 'Greek',
        'he_IL' => 'Hebrew',
        'hi_IN' => 'Hindi',
        'hu_HU' => 'Hungarian',
        'is_IS' => 'Icelandic',
        'id_ID' => 'Indonesian',
        'ga_IE' => 'Irish',
        'it_IT' => 'Italian',
        'ja_JP' => 'Japanese',
        'km_KH' => 'Khmer',
        'ko_KR' => 'Korean',
        'ku_TR' => 'Kurdish',
        'la_VA' => 'Latin',
        'lv_LV' => 'Latvian',
        'fb_LT' => 'Leet Speak',
        'lt_LT' => 'Lithuanian',
        'mk_MK' => 'Macedonian',
        'ml_IN' => 'Malayalam',
        'ms_MY' => 'Melayu',
        'ne_NP' => 'Nepali',
        'nb_NO' => 'Norwegian (bokmal)',
        'nn_NO' => 'Norwegian (nynorsk)',
        'ps_AF' => 'Pashto',
        'fa_IR' => 'Persian',
        'pl_PL' => 'Polish',
        'pt_PT' => 'Portuguese',
        'pa_IN' => 'Punjabi',
        'ro_RO' => 'Romanian',
        'ru_RU' => 'Russian',
        'sr_RS' => 'Serbian',
        'sk_SK' => 'Slovak',
        'sl_SI' => 'Slovenian',
        'sw_KE' => 'Swahili',
        'sv_SE' => 'Swedish',
        'ta_IN' => 'Tamil',
        'te_IN' => 'Telugu',
        'th_TH' => 'Thai',
        'tr_TR' => 'Turkish',
        'uk_UA' => 'Ukrainian',
        'vi_VN' => 'Vietnamese',
        'cy_GB' => 'Welsh',
    );

    /**
     * Constructor method called on each newly-created object
     */
    public function __construct()
    {
        $this->name = 'elegantalfacebookshareproduct';
        $this->tab = 'social_networks';
        $this->version = '1.3.3';
        $this->author = 'ELEGANTAL';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = 'c0974eb5f381709617515e70c432eb86';

        parent::__construct();

        $this->displayName = $this->l('Share us on Facebook and get a bonus gift');
        $this->description = $this->l('Add share button or popup on checkout page to encourage customers to share your shop on their Facebook and get a bonus gift in return.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    /**
     * This function plays controller role for the back-office page of the module
     * @return string HTML
     */
    public function getContent()
    {
        $this->setTimeLimit();

        if (_PS_VERSION_ < '1.6') {
            $this->context->controller->addCSS($this->_path . 'views/css/elegantalfacebookshareproduct-bootstrap.css', 'all');
            $this->context->controller->addCSS($this->_path . 'views/css/font-awesome.css', 'all');

            if (!in_array(Tools::getValue('event'), array('settings', 'editSettings'))) {
                $this->context->controller->addJS($this->_path . 'views/js/jquery-1.11.0.min.js');
                $this->context->controller->addJS($this->_path . 'views/js/bootstrap.js');
            }
        }

        $this->context->controller->addCSS($this->_path . 'views/css/elegantalfacebookshareproduct-back.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/elegantalfacebookshareproduct-back.js');

        $html = $this->getRedirectAlerts();

        try {
            if ($event = Tools::getValue('event')) {
                switch ($event) {
                    case 'settings':
                        $html .= $this->settings();
                        break;
                    case 'editSettings':
                        $html .= $this->editSettings();
                        break;
                    default:
                        $html .= $this->editSettings();
                        break;
                }
            } else {
                $html .= $this->editSettings();
            }
        } catch (Exception $e) {
            $this->setRedirectAlert($e->getMessage(), 'error');
            $this->redirectAdmin();
        }

        return $html;
    }

    /**
     * Render and process settings form
     * @return string HTML
     */
    protected function editSettings()
    {
        $html = "";

        if ($this->isPostRequest()) {
            $errors = array();

            if (Tools::getValue('cart_rule_ids')) {
                $this->setSetting('cart_rule_ids', Tools::getValue('cart_rule_ids'));
            } else {
                $this->setSetting('cart_rule_ids', '');
            }

            if (Tools::getValue('fb_app_id')) {
                $this->setSetting('fb_app_id', Tools::getValue('fb_app_id'));
            } else {
                $errors[] = $this->l('Facebook App ID is required.');
            }

            if (Tools::getValue('is_share_shop_url')) {
                $this->setSetting('is_share_shop_url', 1);
            } else {
                $this->setSetting('is_share_shop_url', 0);
            }

            if (Tools::getValue('url_to_share')) {
                if (Validate::isAbsoluteUrl(Tools::getValue('url_to_share'))) {
                    $this->setSetting('url_to_share', Tools::getValue('url_to_share'));
                } else {
                    $errors[] = $this->l('URL is not valid.');
                }
            } else {
                $this->setSetting('url_to_share', '');
            }

            if (Tools::getValue('display_type')) {
                $this->setSetting('display_type', Tools::getValue('display_type'));
            }

            if (Tools::getValue('fb_event')) {
                $this->setSetting('fb_event', Tools::getValue('fb_event'));
            }

            if (Tools::getValue('active')) {
                $this->setSetting('active', 1);
            } else {
                $this->setSetting('active', 0);
            }

            if (empty($errors)) {
                $this->setRedirectAlert($this->l('Settings saved successfully.'), 'success');
                $this->redirectAdmin();
            } else {
                $html .= $this->displayError(implode('<br>', $errors));
            }
        }

        $fields_value = $this->getSettings();
        $fields_value['cart_rule_ids[]'] = $fields_value['cart_rule_ids'];

        // Default Values
        if (!$fields_value['url_to_share']) {
            $fields_value['url_to_share'] = $this->context->shop->getBaseURL();
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->displayName,
                    'icon' => 'icon-gift'
                ),
                'input' => array(
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Share current shop URL'),
                        'name' => 'is_share_shop_url',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'is_share_shop_url_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'is_share_shop_url_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'hint' => $this->l('Customers will share URL of the current shop'),
                        'desc' => $this->l('Customers will share URL of the current shop'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Custom URL to share'),
                        'name' => 'url_to_share',
                        'hint' => $this->l('Enter custom URL that will be shared on Facebook'),
                        'desc' => $this->l('Enter custom URL that will be shared on Facebook'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Facebook App ID'),
                        'name' => 'fb_app_id',
                        'required' => true,
                        'hint' => $this->l('Enter your Facebook App ID. You can create an app on https://developers.facebook.com/apps'),
                        'desc' => $this->l('Enter your Facebook App ID. You can create an app on https://developers.facebook.com/apps'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Cart Rule'),
                        'name' => 'cart_rule_ids[]',
                        'multiple' => true,
                        'options' => array(
                            'query' => $this->getCartRulesForSelect(),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'hint' => $this->l('Select Cart Rule that will be applied to the shopping cart when customer shares on Facebook. You can select multiple items with SHIFT + LEFT CLICK. When you select more than one rule, customers will have an option to choose their gift.'),
                        'desc' => $this->l('Select Cart Rule that will be applied to the shopping cart when customer shares on Facebook. You can select multiple items with SHIFT + LEFT CLICK. When you select more than one rule, customers will have an option to choose their gift.'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Display Type'),
                        'name' => 'display_type',
                        'options' => array(
                            'query' => array(
                                array('key' => 'button', 'value' => $this->l('Share Button')),
                                array('key' => 'popup', 'value' => $this->l('Popup Window')),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'hint' => $this->l('Select how you want the share button to be displayed on front-end'),
                        'desc' => $this->l('Select how you want the share button to be displayed on front-end'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Event'),
                        'name' => 'fb_event',
                        'options' => array(
                            'query' => array(
                                array('key' => 'share', 'value' => $this->l('Share')),
                                array('key' => 'like', 'value' => $this->l('Like')),
                            ),
                            'id' => 'key',
                            'name' => 'value'
                        ),
                        'hint' => $this->l('Select whether you want users to share or like your page'),
                        'desc' => $this->l('Select whether you want users to share or like your page'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ < '1.6') ? 'el_switch' : 'switch',
                        'label' => $this->l('Active'),
                        'name' => 'active',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'hint' => $this->l('Module can be enabled or disabled here'),
                        'desc' => $this->l('Module can be enabled or disabled here'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            )
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'editSettings';
        $helper->name_controller = 'elegantalBootstrapWrapper';
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->getAdminUrl(array('event' => 'editSettings'));
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $lang->id,
                'iso_code' => $lang->iso_code
            ),
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $html . $helper->generateForm(array($fields_form));
    }

    /**
     * Returns cart rules for module's select box
     * @return array
     */
    protected function getCartRulesForSelect()
    {
        $result = array();
        $rules = CartRule::getCustomerCartRules($this->context->language->id, 0);
        foreach ($rules as $rule) {
            $result[] = array('key' => $rule['id_cart_rule'], 'value' => $rule['name']);
        }
        return $result;
    }

    /**
     * Returns URL to controller which will process request to add a gift to shopping cart
     * @return string URL
     */
    protected function getActionUrlToProcessGift()
    {
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;

        // Secure key in session
        $secure_key = Tools::passwdGen(8);
        $this->context->cookie->__set($this->name . '_secure_key', $secure_key);
        $params = array('secure_key' => $secure_key);

        return $this->context->link->getModuleLink($this->name, 'gift', $params, null, $id_lang, $id_shop);
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/elegantalfacebookshareproduct.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/elegantalfacebookshareproduct.js');
    }

    public function hookDisplayShoppingCart()
    {
        $settings = $this->getSettings();

        // Check module status
        if (empty($settings['active'])) {
            return;
        }

        // Check Facebook App ID
        if (empty($settings['fb_app_id'])) {
            return;
        }

        // Get cart rule IDs into array from shopping cart
        $cartRuleIds = array();
        $cartRules = $this->context->cart->getCartRules();
        foreach ($cartRules as $cartRule) {
            $cartRuleIds[] = $cartRule['id_cart_rule'];
        }

        // If any of cart rules is in cart don't display popup
        $cartRulesForSelect = array();
        $selected_cart_rule_ids = $settings['cart_rule_ids'];
        foreach ($selected_cart_rule_ids as $cart_rule_id) {
            if (in_array($cart_rule_id, $cartRuleIds)) {
                return;
            } else {
                $cartRule = new CartRule($cart_rule_id);
                if (!Validate::isLoadedObject($cartRule)) {
                    continue;
                }
                $cartRuleError = $cartRule->checkValidity($this->context, false, true);
                if ($cartRuleError && Cart::getNbProducts($this->context->cart->id)) {
                    continue;
                }

                $product_name = '';
                $product_image = '';
                if ($cartRule->gift_product) {
                    $product = new Product($cartRule->gift_product, false, $this->context->language->id);
                    if (!Validate::isLoadedObject($product)) {
                        continue;
                    }
                    $product_name = $product->name;
                    $cover_image = Image::getCover($product->id);
                    $product_image = $this->context->link->getImageLink($product->link_rewrite, $product->id . '-' . $cover_image['id_image'], null);
                }

                $currencySign = $this->context->currency->iso_code;
                if ($cartRule->reduction_currency && $cartRule->reduction_currency != $this->context->currency->id) {
                    $currency = new Currency($cartRule->reduction_currency);
                    if (Validate::isLoadedObject($currency)) {
                        $currencySign = $currency->iso_code;
                    }
                }

                $cartRulesForSelect[] = array(
                    'cart_rule_id' => $cart_rule_id,
                    'product_name' => $product_name,
                    'product_image' => $product_image,
                    'free_shipping' => $cartRule->free_shipping,
                    'reduction_percent' => $cartRule->reduction_percent,
                    'reduction_amount' => $cartRule->reduction_amount,
                    'reduction_currency' => $currencySign,
                );
            }
        }

        // Check Cart Rules
        if (empty($cartRulesForSelect)) {
            return;
        }

        // URL to be shared
        $url_to_share = $this->context->shop->getBaseURL();
        if (!$settings['is_share_shop_url'] && Validate::isAbsoluteUrl($settings['url_to_share'])) {
            $url_to_share = $settings['url_to_share'];
        }

        $locale = $settings['locale'];
        if (isset($this->context->language->iso_code)) {
            $context_locale = explode('-', $this->context->language->iso_code);
            if ($context_locale && isset($context_locale[0])) {
                foreach ($this->locales as $supported_locale_code => $supported_locale_name) {
                    $supported_locale = explode('_', $supported_locale_code);
                    if (isset($supported_locale[0]) && Tools::strtolower($supported_locale[0]) == Tools::strtolower($context_locale[0])) {
                        $locale = $supported_locale_code;
                        break;
                    }
                }
            }
        }

        $this->context->smarty->assign(array(
            'fb_app_id' => $settings['fb_app_id'],
            'locale' => $locale,
            'url_to_share' => $url_to_share,
            'process_action_url' => $this->getActionUrlToProcessGift(),
            'display_type' => $settings['display_type'],
            'fb_event' => $settings['fb_event'],
            'cart_rules' => $cartRulesForSelect,
            'debug_mode' => $settings['debug_mode'],
        ));

        return $this->display(__FILE__, 'front.tpl');
    }

    /**
     * Ajax action to add gift product to shopping cart
     */
    public function addGiftToCart()
    {
        $result = array('success' => false);

        $settings = $this->getSettings();
        $cart_rule_id = null;
        if (Tools::getValue('cart_rule_id')) {
            $cart_rule_id = Tools::getValue('cart_rule_id');
        } else {
            $cart_rule_ids = $settings['cart_rule_ids'];
            $cart_rule_id = reset($cart_rule_ids);
        }

        if (empty($this->context->cookie->{$this->name . '_secure_key'})) {
            $result['error'] = 'Secure key was not found.';
        } elseif (!Tools::getValue('secure_key')) {
            $result['error'] = 'Secure key is not valid.';
        } elseif (empty($settings['cart_rule_ids'])) {
            $result['error'] = 'Module has no cart rules.';
        } elseif (empty($settings['active'])) {
            $result['error'] = 'Module is disabled.';
        } elseif (!Tools::getValue('post_id')) {
            $result['error'] = 'Facebook post_id is required.';
        } elseif (empty($cart_rule_id)) {
            $result['error'] = 'Cart Rule is required.';
        }

        if (empty($result['error'])) {
            $cartRule = new CartRule($cart_rule_id);
            if (Validate::isLoadedObject($cartRule)) {
                $cartRuleError = $cartRule->checkValidity($this->context, false, true);
                if (empty($cartRuleError) || !Cart::getNbProducts($this->context->cart->id)) {
                    $result['success'] = true;
                    $this->context->cart->addCartRule($cartRule->id);
                } else {
                    $result['error'] = $cartRuleError;
                }
            }
        }

        die(Tools::jsonEncode($result));
    }

    public function hookDisplayHome()
    {
        return $this->hookDisplayShoppingCart();
    }
}
