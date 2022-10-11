<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 *
 *
 * Description
 *
 * Updates quantity in the cart
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/abandonedcart_core.php';

/**
 * The parent class is extending the "Module" core class.
 * So no need to extend "Module" core class here in this class.
 */
class Abandonedcart extends AbandonedCartCore
{
    private $shopping_settings = array();
    protected $admin_path;

    public function __construct()
    {
        $this->name = 'abandonedcart';
        $this->tab = 'advertising_marketing';
        $this->version = '1.1.13';
        $this->author = 'Knowband';
        $this->need_instance = 0;
        $this->module_key = '3205b56afee05c629b485725f73b0c68';
        $this->ps_versions_compliancy = array('min' => '1.6.0.4', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        $this->displayName = $this->l('Abandoned Cart');
        $this->description = $this->l('This module will convert the abandoned carts into sales.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        parent::__construct();
    }

    public function getErrors()
    {
        return $this->custom_errors;
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install()) {
            return false;
        }

        $this->installModel();
        if (!Configuration::get('VELSOF_ABANDONED_CART_MAIL_CHECK')) {
            //Tools::chmodr(_PS_MODULE_DIR_ . 'abandonedcart/mails', 0755);
            $mail_dir = dirname(__FILE__) . '/mails/en';

            if (Context::getContext()->language->iso_code != 'en') {
                $new_dir = dirname(__FILE__) . '/mails/' . Context::getContext()->language->iso_code;
                $this->copyfolder($mail_dir, $new_dir);
            }

            Configuration::updateGlobalValue('VELSOF_ABANDONED_CART_MAIL_CHECK', 1);
            Configuration::updateGlobalValue(
                'VELSOF_ABANDONED_CART_DEFAULT_TEMPLATE_LANG',
                Context::getContext()->language->iso_code
            );
        }

        $check_column = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = "' . _DB_PREFIX_ . self::INCENTIVE_MAPPING_TABLE_NAME .
            '" AND COLUMN_NAME = "quantity"';
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($check_column)) {
            $create_column = 'ALTER TABLE ' . _DB_PREFIX_ . self::INCENTIVE_MAPPING_TABLE_NAME .
                ' ADD quantity int(11) NOT NULL DEFAULT -1 AFTER id_incentive';
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($create_column);
        }

        $this->shopping_settings = $this->getDefaultSettings();

        Configuration::updateGlobalValue('VELSOF_ABANDONEDCART', serialize($this->shopping_settings));

        if (!Configuration::get('VELSOF_ABANDONEDCART_START_DATE')) {
            Configuration::updateGlobalValue('VELSOF_ABANDONEDCART_START_DATE', date('Y-m-d H:i:s'));
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    public function getContent()
    {
        if (Tools::isSubmit('ajax') && Tools::getValue('ajax')) {
            $this->doAjaxProcess();
        }

        $output = null;
        if (Tools::isSubmit('abd_configuration_form')) {
            $settings = Tools::getValue('velsof_abandoncart');
            if (!$settings || empty($settings)) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                $tmp = $this->getDefaultSettings();
                $settings['plugin_id'] = $tmp['plugin_id'];
                Configuration::updateValue('VELSOF_ABANDONEDCART', serialize($settings));
                $output .= $this->displayConfirmation($this->l('The settings have been updated.'));
            }
        } elseif ($cart_re_display_val = Tools::getValue('enable_cart_redisplay')) {
            if (Validate::isBool($cart_re_display_val) && Validate::isCleanHtml($cart_re_display_val)) {
                Configuration::updateValue('PS_CART_FOLLOWING', $cart_re_display_val);
                $output .= $this->displayConfirmation($this->l('Cart Re-Display has been enabled.'));
            } else {
                $output .= $this->displayError($this->l('Invalid Cart Re-Display Submission'));
            }
        }

        if (!Configuration::get('VELSOF_ABANDONEDCART') || Configuration::get('VELSOF_ABANDONEDCART') == '') {
            $settings = $this->getDefaultSettings();
        } else {
            $settings = unserialize(Configuration::get('VELSOF_ABANDONEDCART'));
        }

        $this->shopping_settings = $settings;

        /* Start - Code Modified by RS on 06-Sept-2017 for solving the problem of time delay on page load when there are a lot of carts (There is no need for this function to be called here as it is already called thorugh CRON) */
//        $this->updateAbandonList(); //Added to update abandonded cart list on page load
        /* End - Code Modified by RS on 06-Sept-2017 for solving the problem of time delay on page load when there are a lot of carts (There is no need for this function to be called here as it is already called thorugh CRON) */
        $from = date('Y-m-d', strtotime('-90 days'));
        $to = date('Y-m-d');

        if (!is_writable($this->getTemplateDir())) {
            //Tools::chmodr(_PS_MODULE_DIR_ . 'abandonedcart/mails', 0755);
            if (!is_writable($this->getTemplateDir())) {
                $output .= $this->displayError(
                    $this->l('Please give read/write permission to ') . '"' . $this->getTemplateDir() .
                    '"' . $this->l(' directory.')
                );
            }
        }

        $cron_link = $this->context->link->getModuleLink('abandonedcart', 'cron');
        $dot_found = 0;
        $needle = 'index.php';
        $dot_found = strpos($cron_link, $needle);
        if ($dot_found !== false) {
            $ch = '&';
        } else {
            $ch = '?';
        }

        $custom_ssl_var = 0;

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $custom_ssl_var = 1;
        }

        if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
            $module_dir = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        } else {
            $module_dir = _PS_BASE_URL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_);
        }

        /* Start - Code Modified by RS for handing the `cart_total` column in case the column is added through module update */
        $cart_total_column_added = 0;
        if (Configuration::get('VELSOF_ABD_CART_TOTAL_ADDED')) {
            $cart_total_column_added = Configuration::get('VELSOF_ABD_CART_TOTAL_ADDED');
        }
        $this->smarty->assign(array(
            'cancel_action' => AdminController::$currentIndex . '&token=' .
                Tools::getAdminTokenLite('AdminModules'),
            'action' => AdminController::$currentIndex . '&token=' .
                Tools::getAdminTokenLite('AdminModules') . '&configure=' . $this->name,
            'velsof_abandoncart' => $settings,
            'languages' => Language::getLanguages(true),
            'email_templates' => $this->getEmailTemplateList(),
            'dropdown_template_list' => $this->loadEmailTemplates(false),
            'dropdown_tran_template_list' => $this->loadEmailTemplates(),
            'incentive_list' => $this->getIncentiveList(),
            'email_types' => $this->getEmailTypeArray(),
            'default_email_type' => $this->getDefaultEmailType(),
            'discount_types' => $this->getDiscountTypeArray(),
            'default_discount_type' => $this->getDefaultDiscountType(),
            'incentive_statuses' => $this->getIncentiveStatuses(),
            'default_incentive_status' => $this->getDefaultIncentiveStatus(),
            'non_discount_email_value' => parent::NON_DISCOUNT_EMAIL,
            'default_language' => $this->context->language->id,
            'currency_format' => $this->context->currency->format,
            'currency_blank' => $this->context->currency->blank,
            'currency_sign' => $this->context->currency->sign,
            'cart_redisplay' => (Configuration::get('PS_CART_FOLLOWING')) ? true : false,
            'start_date' => $from,
            'end_date' => $to,
            'path' => $module_dir.$this->name.'/',
            'admin_path' => _PS_BASE_URL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', _PS_ADMIN_DIR_),
            'root_path' => _PS_BASE_URL_ . __PS_BASE_URI__ . str_replace(_PS_ROOT_DIR_ . '/', '', ''),
            'abandon_list' => $this->getAbandonList(),
            'converted_carts' => $this->getConvertedList(),
            'token' => Tools::getAdminTokenLite('AdminModules'),
            'plugin_shop_url' => $this->getPluginShopUrl(),
            'front_cron_url' => $cron_link . $ch,
            'image_path' => $module_dir . 'abandonedcart/views/img/admin/',
            'secure_key' => Configuration::get('VELSOF_ABD_SECURE_KEY'),
            'cart_total_column_added' => $cart_total_column_added
        ));
        /* End - Code Modified by RS for handing the `cart_total` column in case the column is added through module update */

        $this->loadMedia();

        $output .= $this->display(__FILE__, 'views/templates/admin/abandonedcart.tpl');
        return $output;
    }

    private function doAjaxProcess()
    {
        $json = array();
        switch (Tools::getValue('method')) {
            case 'getnewemailtemplate':
                $json = $this->loadNewEmailTemplate();
                break;
            case 'saveemailtemplate':
                $json = $this->saveEmailTemplate();
                break;
            case 'rememailtemplate':
                $json = $this->remEmailTemplate(Tools::getValue('key_template'));
                break;
            case 'getemailtemplatetranslation':
                $json = $this->loadEmailTemplateTranslation(
                    Tools::getValue('id_template'),
                    Tools::getValue('id_lang')
                );
                break;
            case 'saveemailtemplatetranslation':
                $json = $this->updateEmailTemplateTranslation(Tools::getValue('email_template_translation'));
                break;
            case 'updatetemplatename':
                $json = $this->updateTemplateName(Tools::getValue('id_template'), Tools::getValue('changed_tml_name'));
                break;
            case 'gettemplatelist':
                $json = $this->getEmailTemplateList();
                break;
            case 'getincentivedetail':
                $json = $this->loadIncentivebyId(Tools::getValue('key_incentive'));
                break;
            case 'saveincentive':
                $json = $this->saveIncentive();
                break;
            case 'remincentive':
                $json = $this->remIncentive(Tools::getValue('key'));
                break;
            case 'getincentivelist':
                $json = $this->getIncentiveList();
                break;
            case 'changeincentivestatus':
                $json = $this->changeIncentiveStatus(Tools::getValue('incentive'));
                break;
            case 'getabandonlist':
                /* Start - Code Modified by RS on 06-Sept-2017 for solving the problem of time delay on page load when there are a lot of carts (There is no need for this function to be called here as it is already called thorugh CRON) */
                if (Tools::getIsset('refresh_list') && Tools::getValue('refresh_list')) {
                    $this->updateAbandonList();
                }
                /* End - Code Modified by RS on 06-Sept-2017 for solving the problem of time delay on page load when there are a lot of carts (There is no need for this function to be called here as it is already called thorugh CRON) */
                $json = $this->getAbandonList();
                break;
            case 'gettemplate':
                $json = $this->loadEmailTemplateTranslation(0, 0, Tools::getValue('id_template_content'));
                break;
            case 'getCouponDetail':
                $json = $this->getCustomerCouponDetail(Tools::getValue('id_customer'), Tools::getValue('email'));
                break;
            case 'getCustomerDetail':
                $json = $this->getCustomerDetail(Tools::getValue('id_customer'));
                break;
            case 'getCustomerCartDetail':
                $json = $this->getCustomerCartDetail(Tools::getValue('id_customer'), Tools::getValue('id_cart'));
                break;
            case 'sendreminder':
                $data = Tools::getValue('email_reminder');
                $data['subject'] = Tools::getValue('single_email_subject');
                $data['body'] = Tools::getValue('single_email_body');
                $data['cart_template'] = Tools::getValue('cart_template');
                if ($this->sendReminder($data, false) == 1) {
                    $json = array('status' => true, 'message' => $this->l('Reminder email sent successfully.'));
                } elseif ($this->sendReminder($data, false) == -1) {
                    $json = array('status' => -1, 'message' => $this->l('Unable to send email. Permission error on ') .$this->getTemplateDir());
                } elseif ($this->sendReminder($data, false) == -2) {
                    $json = array('status' => -2, 'message' => $this->l('The cart is empty, not able to send reminder email.'));
                } else {
                    $json = array('status' => false, 'message' => $this->l('Not able to send reminder email.'));
                }
                break;
            case 'senddiscountemail':
                $data = Tools::getValue('email_discount');
                $data['subject'] = Tools::getValue('single_email_subject');
                $data['body'] = Tools::getValue('single_email_body');
                $data['cart_template'] = Tools::getValue('cart_template');
                if ($this->sendDiscountEmail($data, false) == 1) {
                    $json = array('status' => true, 'message' => $this->l('Discount email sent successfully.'));
                } elseif ($this->sendDiscountEmail($data, false) == -1) {
                    $json = array('status' => -1, 'message' => $this->l('Unable to send email. Permission error on '). $this->getTemplateDir());
                } elseif ($this->sendDiscountEmail($data, false) == -2) {
                    $json = array('status' => -2, 'message' => $this->l('The cart is empty, not able to send discount email.'));
                } else {
                    $json = array('status' => false, 'message' => $this->l('Not able to send discount email.'));
                }
                break;
            case 'deleteabandon':
                if ($this->deleteAbandonCart(Tools::getValue('id_abandon'))) {
                    $json = array(
                        'status' => true,
                        'message' => $this->l('Requested abandon cart deleted successfully.'));
                } else {
                    $json = array('status' => false,
                        'message' => $this->l('Not able to delete requested abandon cart.'));
                }
                break;
            case 'getconvertedlist':
                $json = $this->getConvertedList();
                break;
            case 'refreshtemplatedropwn':
                $json = array(
                    'templates' => $this->loadEmailTemplates(false),
                    'trans_template_discount' => $this->loadEmailTemplates(
                        true,
                        array('type' => parent::DISCOUNT_EMAIL)
                    ),
                    'trans_template_ndiscount' => $this->loadEmailTemplates(
                        true,
                        array('type' => parent::NON_DISCOUNT_EMAIL)
                    )
                );
                break;
            case 'getPieChartsData':
                $json = $this->getPieChartsData();
                break;
            case 'getChartData':
                $start_date = Tools::getValue('start');
                $end_date = Tools::getValue('end');

                $json = $this->graph($start_date, $end_date);
                break;
            case 'checkTemplateType':
                $template_id = Tools::getValue('template_id');
                $json = $this->checkTemplateType($template_id);
                break;
            case 'getdefaultlanguage':
                echo $this->context->language->id;
                die;
        }

        header('Content-Type: application/json', true);
        echo Tools::jsonEncode($json);
        die;
    }

    public function hookDisplayHeader()
    {
        if ($this->context->cookie->logged) {
            return;
        }
        if (Configuration::get('VELSOF_ABANDONEDCART')) {
            $abd_settings = Tools::unSerialize(Configuration::get('VELSOF_ABANDONEDCART'));
            if (isset($abd_settings['enable']) && $abd_settings['enable'] == 1) {
                $page_name = $this->context->smarty->tpl_vars['page_name']->value;
                if ($page_name == 'supercheckout' ||
                    ($page_name == 'module-supercheckout-supercheckout') ||
                    $page_name == 'order-opc' ||
                    $page_name == 'authentication' ||
                    $page_name == 'order') {
                    $ajax_path = __PS_BASE_URI__ . 'index.php?fc=module&module=abandonedcart&controller=cron';
                    $this->context->smarty->assign('ajax_path', $ajax_path);
                    $this->context->controller->addJs($this->_path . 'views/js/abandonedcart_front.js');
                    return $this->display(__FILE__, 'views/templates/front/abandonedcart_front.tpl');
                }
            }
        }
    }

    public function getCartHtml($cart_data)
    {
        $cart_html = '';
        $cart = new Cart((int) $cart_data['id_cart']);
        $lang = new Language($cart->id_lang);
        $iso_code = $lang->iso_code;
//		$iso_code = $lang->getIsoById((int)$cart_data['id_lang']);
//		$iso_code = $lang->getIsoById((int)$cart_data['id_lang']);

        if ($cart->nbProducts()) {
            if ($cart_data['cart_template'] == 1) {
                if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                    $seperator_path = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ .
                        str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_) .
                        $this->name . '/views/img/seperator.jpg';
                } else {
                    $seperator_path = _PS_BASE_URL_ . __PS_BASE_URI__ .
                        str_replace(_PS_ROOT_DIR_ . '/', '', _PS_MODULE_DIR_) .
                        $this->name . '/views/img/seperator.jpg';
                }
                $cart_html = '<table class="table table-recap" style="width:100%; border-collapse:collapse">
				<tbody><tr><td colspan="2"><img alt="Seperator" src="' . $seperator_path . '" width="100%" 
				style="background:#0c528b;color:#ffffff;font-size:9px;max-height:50px"></td></tr>';

                $cart = new Cart((int) $cart_data['id_cart']);
                $detail = $cart->getProducts();
                $sno = 1;
                $link = new Link();
                foreach ($detail as $products) {
                    if (!isset($products['attributes'])) {
                        $products['attributes'] = ' ';
                    }
                    if (!isset($products['name'])) {
                        $products['name'] = ' ';
                    }
                    if (!isset($products['description_short'])) {
                        $products['description_short'] = ' ';
                    }
                    $row = '';
                    if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                        $img_path = 'https://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    } else {
                        $img_path = 'http://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    }
                    $action_url = $this->getFrontActionLink('single', $cart_data, $products['id_product']);
                    $row .= '<tr>';
                    $row .= '<td align="center" width="160" valign="baseline">';
                    $row .= '<a href="' . $action_url . '" target="_blank">';
                    $row .= '<img alt="Product Image" src="' . $img_path . '" 
					style="background:#0c528b;color:#ffffff;font-size:9px;max-height:200px" class="CToWUd">';
                    $row .= '</a></td>';
                    $row .= '<td align="center" width="160" valign="middle">';
                    $row .= '<a style="font-family:Helvetica,Arial,sans-serif;font-weight:800;font-size:18px;
                        line-height:20px;color:#333333" 
                        href="' . $action_url . '" target="_blank">';
                    $row .= $products['name'] . '</a><br>' . $products['attributes'] . '<br></td></tr>';
                    $row .= '<tr><td colspan="2">';
                    $row .= '<img alt="Seperator" src="' . $seperator_path .
                        '" width="100%" style="background:#0c528b;color:#ffffff;font-size:9px;max-height:50px">';
                    $row .= '</td></tr>';
                    $cart_html .= $row;
                    $sno++;
                }

                $direct_checkout_url = $this->getFrontActionLink('direct', $cart_data);
                $cart_html .= '</tbody></table>';
                $cart_html .= '<br><p style="float: right;"><a href="' . $direct_checkout_url . '" target="_blank"';
                $cart_html .= ' style="font-family: sans-serif; color: #FFFFFF !important; font-size: 15px; ';
                $cart_html .= 'padding: 8px 10px; border-radius: 2px; -webkit-border-radius: 2px; border-radius: 2px;';
                
                $dir_chk = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Direct Checkout',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= 'background: #f78828; text-decoration: none; cursor: pointer;">'
                    . $dir_chk . '</a>';
            } elseif ($cart_data['cart_template'] == 2) {
                $cart = new Cart((int) $cart_data['id_cart']);
                $detail = $cart->getProducts();
                $sno = 1;
                $link = new Link();
                $cart_html .= "<hr style='background-color: black;height: 2px;'>
						<div><ul style='list-style-type: none;padding:0px;margin:0px auto;width:80%;'>";
                foreach ($detail as $products) {
                    if (!isset($products['attributes'])) {
                        $products['attributes'] = ' ';
                    }
                    if (!isset($products['name'])) {
                        $products['name'] = ' ';
                    }
                    if (!isset($products['description_short'])) {
                        $products['description_short'] = ' ';
                    }

                    if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                        $img_path = 'https://' . $link->getImageLink(
                            $products['link_rewrite'],
                            $products['id_image']
                        );
                    } else {
                        $img_path = 'http://' . $link->getImageLink(
                            $products['link_rewrite'],
                            $products['id_image']
                        );
                    }

                    $action_url = $this->getFrontActionLink('single', $cart_data, $products['id_product']);

                    $cart_html .= "<li style='float:left;margin:5px;'>
                        <div style='border:1px solid rgba(0, 0, 0, 0.45);'>
                        <a href='" . $action_url . "'><img style='width:200px' src='" .
                        $img_path . "'></a></div>";
                    $cart_html .= "<div style='color: rgba(0, 0, 0, 0.73);text-align: center;margin-top: 2px;";
                    $cart_html .= "font-size: 16px;padding: 5px;font-weight: 700;font-family: sans-serif;'>";
                    $cart_html .= '<span style="display:inline-block;max-width:184px;text-overflow: ellipsis;'
                        . 'white-space:nowrap;overflow:hidden">'
                        . $products['name'] . '</span></div>';
                    $cart_html .= '<div style="font-size: 14px;text-align: center;margin-top: 6px;"><span>'
                        . $products['attributes'] . '</span></div></li>';
                }
                $cart_html .= "</ul></div><div style='clear:both'></div>";
                $cart_html .= "<hr style='background-color: black;height: 2px;'>";

                $direct_checkout_url = $this->getFrontActionLink('direct', $cart_data);
                $cart_html .= '<div style="float:right;margin:20px auto;"><a href="'
                    . $direct_checkout_url . '" target="_blank" style="font-weight: bold;font-family: sans-serif;';
                $drct_check_ts = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Direct Checkout',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= 'color: #FFFFFF !important;font-size: 20px;padding: 10px;-webkit-border-radius: 2px;
                    background: #f78828;text-decoration: none;cursor: pointer;">'
                    . $drct_check_ts . '</a></div>';
            } elseif ($cart_data['cart_template'] == 3) {
                $cart = new Cart((int) $cart_data['id_cart']);
                $detail = $cart->getProducts();
                $sno = 1;
                $link = new Link();
                $direct_checkout_url = $this->getFrontActionLink('direct', $cart_data);
                
                $dir_chk_tran = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Direct Checkout',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= "<div style='width:200px;margin:20px auto;'><a href='"
                    . $direct_checkout_url . "' target='_blank' style='font-weight: bold;
                    font-family: sans-serif;color: #FFFFFF !important;
					font-size: 20px;padding: 10px;border-radius: 2px;-webkit-border-radius: 2px;
					background: rgb(95, 158, 160);text-decoration: none;cursor: pointer;'>"
                    . $dir_chk_tran . '</a></div>';
                $cart_html .= '<hr><div style="text-align: center;font-size: 20px;color: cadetblue;"><span>'
                    . $this->getModuleTranslationByLanguage('abandonedcart', 'YOUR BASKET', 'abandonedcart', $iso_code)
                    . '</span></div>
                    <hr><div style=""><ul style="list-style-type: none;padding:0px;margin:0px auto;">';

                foreach ($detail as $products) {
                    if (!isset($products['attributes'])) {
                        $products['attributes'] = ' ';
                    }
                    if (!isset($products['name'])) {
                        $products['name'] = ' ';
                    }

                    if (!isset($products['description_short'])) {
                        $products['description_short'] = ' ';
                    }

                    if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                        $img_path = 'https://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    } else {
                        $img_path = 'http://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    }
                    $action_url = $this->getFrontActionLink('single', $cart_data, $products['id_product']);
                    $cart_html .= "<li style='margin:5px;text-align:center;'>";
                    $cart_html .= "<div style='display:inline-block;float:left;width:30%;'><a href='" .
                        $action_url . "'>
                        <img  style='border:1px solid rgba(95, 158, 160, 0.66);max-width:100%;' src='" .
                        $img_path . "'></a></div>";
                    $cart_html .= '<div style="display:inline-block;margin:10% auto;width:70%;'
                        . 'font-weight: bold;font-size:18px;"><span>'
                        . $products['name'] . '</span><br><div style="font-weight:100;'
                        . 'font-size:14px;text-align:center"><span>'
                        . $products['attributes'] . '</span></div></div>';
                    $cart_html .= "<div style='clear:both'></div>";
                }
                $direct_ch_trn = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Direct Checkout',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= '<hr><div style="width:200px;margin:20px auto;"><a href="' .
                    $direct_checkout_url . '" target="_blank" 
                    style="font-weight: bold;font-family: sans-serif;color: #FFFFFF 
                    !important;font-size: 20px;padding: 10px;border-radius: 2px;-webkit-border-radius: 2px;
                    background: rgb(95, 158, 160);text-decoration: none;cursor: pointer;">'
                    . $direct_ch_trn . '</a></div>';
            } elseif ($cart_data['cart_template'] == 4) {
                $cart = new Cart((int) $cart_data['id_cart']);
                $detail = $cart->getProducts();
                $sno = 1;
                $link = new Link();
                $direct_checkout_url = $this->getFrontActionLink('direct', $cart_data);
                $cart_html .= "<table style='width:100%;border-collapse:collapse'><thead><tr>";
                $cart_html .= '<td style="width:30%;font-family: sans-serif;
                    background: rgb(69, 162, 69);text-align: center;
                    padding: 6px;font-size: 16px;font-weight: bold;color: white;border: 1px solid white">'
                    . $this->getModuleTranslationByLanguage('abandonedcart', 'IMAGE', 'abandonedcart', $iso_code)
                    . '</td>';
                $cart_html .= '<td style="width:30%;font-family: sans-serif;
                    background: rgb(69, 162, 69);text-align: center;
                    padding: 6px;font-size: 16px;font-weight: bold;color: white;border: 1px solid white">'
                    . $this->getModuleTranslationByLanguage('abandonedcart', 'DESCRIPTION', 'abandonedcart', $iso_code)
                    . '</td>';
                $cart_html .= '<td style="width:20%;font-family: sans-serif;
                    background: rgb(69, 162, 69);text-align: center;
                    padding: 6px;font-size: 16px;font-weight: bold;color: white;border: 1px solid white">'
                    . $this->getModuleTranslationByLanguage('abandonedcart', 'QUANTITY', 'abandonedcart', $iso_code)
                    . '</td>';
                $cart_html .= '<td style="width:20%;font-family: sans-serif;
                    background: rgb(69, 162, 69);text-align: center;
                    padding: 6px;font-size: 16px;font-weight: bold;color: white;border: 1px solid white">'
                    . $this->getModuleTranslationByLanguage('abandonedcart', 'PRICE', 'abandonedcart', $iso_code)
                    . '</td></tr>';
                $cart_html .= '</thead>';

                foreach ($detail as $products) {
                    if (!isset($products['attributes'])) {
                        $products['attributes'] = ' ';
                    }

                    if (!isset($products['name'])) {
                        $products['name'] = ' ';
                    }

                    if (!isset($products['description_short'])) {
                        $products['description_short'] = ' ';
                    }

                    if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                        $img_path = 'https://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    } else {
                        $img_path = 'http://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    }
                    $action_url = $this->getFrontActionLink('single', $cart_data, $products['id_product']);
                    $cart_html .= "<tr><td style='width:30%;text-align: center;'><a href='"
                        . $action_url . "'><div style='width:100%'><img src='" . $img_path .
                        "' style='max-width:100%'>
                        </div></a></td>";
                    $cart_html .= '<td style="width:30%;text-align: center;"><div style="text-align: center;">
										<span style="font-weight: bolder;text-transform: capitalize;font-size:16px">'
                            . $products['name'] . '</span><br>';
                    $cart_html .= "<span style='text-transform: capitalize;font-size: 12px;'>" .
                        $products['attributes'] . '</span>';
                    $cart_html .= '</div></td><td style="width:20%;text-align: center;font-weight:bold;">' .
                        $products['cart_quantity'] . '</td>';
                    $cart_html .= '<td style="width:20%;text-align: center;font-weight: 600;font-size: 16px;">'
                        . Tools::displayPrice($products['cart_quantity'] * $products['price']) . '</td></tr>';
                }
                $direct_ch_tran = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Direct Checkout',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= "</table><div style='float:right;text-align: center;
                    padding: 10px;background: rgba(0, 128, 0, 0.73);
                    color: white;font-weight: bold;font-size: 16px;'>
                    <a style='text-decoration:none;color:white' href='"
                    . $direct_checkout_url . "'><span>"
                    . $direct_ch_tran
                    . '</span></a></div>';
            } elseif ($cart_data['cart_template'] == 5) {
                $cart = new Cart((int) $cart_data['id_cart']);
                $detail = $cart->getProducts();
                $sno = 1;
                $link = new Link();
                $direct_checkout_url = $this->getFrontActionLink('direct', $cart_data);
                $item_cart_tr = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Items In Your Cart...',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= "<div style='text-align:left;padding:10px;background: red;
                    background: -webkit-linear-gradient(orange, #FF5722);
                    background: -o-linear-gradient(orange, #FF5722);
                    background: -moz-linear-gradient(orange, #FF5722);
                    background: linear-gradient(orange, #FF5722);'>
                    <span style='font-size:24px;font-weight:bold;color:white;'>"
                    . $item_cart_tr . "</span><span  style='float: right;
                    font-size: 15px;font-weight: bold;color: white;
                    vertical-align: middle;line-height: 22px;'><a href='"
                    . $direct_checkout_url . "' style='color:white;text-decoration:underline;'>"
                    . $this->getModuleTranslationByLanguage('abandonedcart', 'View Cart', 'abandonedcart', $iso_code)
                    . '</a></span></div>';

                foreach ($detail as $products) {
                    if (!isset($products['attributes'])) {
                        $products['attributes'] = ' ';
                    }
                    if (!isset($products['name'])) {
                        $products['name'] = ' ';
                    }
                    if (!isset($products['description_short'])) {
                        $products['description_short'] = ' ';
                    }

                    if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                        $img_path = 'https://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    } else {
                        $img_path = 'http://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    }

                    $action_url = $this->getFrontActionLink('single', $cart_data, $products['id_product']);
                    $cart_html .= "<div style='overflow:auto;border: 1px solid rgba(255, 140, 10, 0.28);'>";
                    $cart_html .= "<div style='width: 20%;padding: 2%;text-align: center;float:left;'><a href='" .
                        $action_url . "'>
                        <img style='border: 1px solid rgba(255, 140, 10, 0.28);max-width:100%' src='" .
                        $img_path . "'></a></div>";
                    $cart_html .= "<div style='padding: 2%;float:left;width:66%;'>
                        <div style='font-weight: bold;padding: 5px;
                        font-size: 16px'>" . $products['name'] . '</div>';
                    $cart_html .= "<div style='color: rgba(0, 0, 0, 0.79);height:30px;display: -webkit-box;
                        -webkit-line-clamp: 2;
                        -webkit-box-orient: vertical;overflow: hidden;text-overflow: ellipsis;'>" .
                        trim($products['description_short'], '<p></p>') . '</div>';
                    $cart_html .= "<div style='margin-top:2%;font-size: 14px;font-weight: bold;'>Qty: "
                        . $products['cart_quantity'] . ' | Price: '
                        . Tools::displayPrice($products['cart_quantity'] * $products['price']) . '</div></div></div>';
                }
                $direct_check_tra = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Direct Checkout',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= "<div href='" . $direct_checkout_url . "' style='float:right;cursor:pointer;
                    font-weight: bold;margin-top: 1%;
					text-align: center;margin: 10px auto;
					font-family: Arial;color: #ffffff;font-size: 20px;padding: 10px 10px 10px 10px;
                    text-decoration: none;background:rgba(255, 141, 0, 0.97);'>
					<span><a style='text-decoration:none;color:white' href='" . $direct_checkout_url . "'>"
                    . $direct_check_tra .
                    '</a></span></div>';
            } elseif ($cart_data['cart_template'] == 6) {
                $cart = new Cart((int) $cart_data['id_cart']);
                $detail = $cart->getProducts();
                $sno = 1;
                $link = new Link();
                $direct_checkout_url = $this->getFrontActionLink('direct', $cart_data);
                $n = 'Open Sans';
                $your_cart_tran = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Your Cart Items',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= "<div style='position: relative;padding: 0;border-bottom: 3px solid #e9e9e9;"
                    . "background: #f6f6f6;margin: 0;font: 600 18px/22px "
                    . $n . ", sans-serif;text-transform: uppercase;color: #484848;display: block;"
                    . "padding: 10px;border-bottom: 3px solid #e9e9e9;list-style: none;'>"
                    . $your_cart_tran .
                    '</div>';
                $action_url = $this->getFrontActionLink('single', $cart_data, $products['id_product']);
                foreach ($detail as $products) {
                    if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                        $img_path = 'https://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    } else {
                        $img_path = 'http://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    }

                    $cart_html .= "<div style='overflow:auto;position: relative;padding: 0;
                        border-bottom: 3px solid #e9e9e9;
                        background: rgba(246, 246, 246, 0.59);margin: 0;font: 600 18px/22px "
                        . $n . ", sans-serif;text-transform: uppercase;color: #484848;"
                        . "display: block;padding: 10px;list-style: none;'>";
                    $cart_html .= "<div style='width:30%;float:left;'><a href='" .
                            $action_url . "'><img  width='100' src='" . $img_path . "'></a></div>";
                    $cart_html .= '<div style="width:60%;float:left;margin: 3% auto;">
                        <span style="width:40%;float:left;font-size: 16px;
                        font-weight: 100;text-transform:capitalize;">'
                        . $products['name'] . '</span>';
                    $cart_html .= '<span style="width:40%;float:left;font-size: 16px;font-weight: 100;'
                        . 'text-align:center;text-transform:lowercase">x'
                        . $products['cart_quantity'] . '</span></div></div>';
                }
                $direct_check_tr = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Direct Checkout',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= '<div style="cursor:pointer;float: right;margin-top: 1%;cursor:pointer;
                    background: #3498db;background-image: -webkit-linear-gradient(top, #3498db, #2980b9);
                    background-image: -moz-linear-gradient(top, #3498db, #2980b9);
                    background-image: -ms-linear-gradient(top, #3498db, #2980b9);
                    background-image: -o-linear-gradient(top, #3498db, #2980b9);
                    background-image: linear-gradient(to bottom, #3498db, #2980b9);-webkit-border-radius: 0;
                    -moz-border-radius: 0;border-radius: 0px;font-family: Arial;color: #ffffff;font-size: 20px;
                    padding: 10px 20px 10px 20px;text-decoration: none;">
                    <a style="text-decoration:none;color:white" href="' .
                    $direct_checkout_url . '">'
                    . $direct_check_tr . '</a></div>';
            } elseif ($cart_data['cart_template'] == 7) {
                $cart = new Cart((int) $cart_data['id_cart']);
                $detail = $cart->getProducts();
                $sno = 1;
                $link = new Link();
                $direct_checkout_url = $this->getFrontActionLink('direct', $cart_data);
                $n = 'Open Sans';
                $item_trans = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Items In Your Cart',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= "<div style='color:white;position: relative;padding: 0;border-bottom: 3px solid #e9e9e9;
                    background: rgba(6, 51, 134, 0.61);margin: 0;font: 600 18px/22px "
                    . $n . ", sans-serif;text-transform: uppercase;color: white;display: block;"
                    . "padding: 10px;border-bottom: 3px solid #e9e9e9;list-style: none;'>"
                    . $item_trans . '</div>';

                foreach ($detail as $products) {
                    if (!isset($products['attributes'])) {
                        $products['attributes'] = ' ';
                    }
                    if (!isset($products['name'])) {
                        $products['name'] = ' ';
                    }
                    if (!isset($products['description_short'])) {
                        $products['description_short'] = ' ';
                    }
                    if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                        $img_path = 'https://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    } else {
                        $img_path = 'http://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    }

                    $action_url = $this->getFrontActionLink('single', $cart_data, $products['id_product']);
                    $cart_html .= "<div style='overflow:auto;position: relative;padding: 0;
                        border-bottom: 3px solid #6682B5;margin: 0;font: 600 18px/22px '" .
                        $n . "', sans-serif;
                        text-transform: uppercase;color: #484848;display: block;padding: 10px;list-style: none;'>";
                    $cart_html .= "<div style='width:30%;float:left;'><a href='" . $action_url .
                        "'><img  width='100' src='" . $img_path . "'></a></div>";
                    $cart_html .= "<div style='width:30%;float:left;margin: 3% auto;text-align:center'>
                        <div style='font-size: 16px;font-weight: 100;
                        text-transform:capitalize;color: rgba(3, 26, 97, 0.56);font-weight: bold'>" .
                        $products['name'] . '</div>';
                    $cart_html .= "<div style='font-size: 12px;text-transform:capitalize;"
                        . "color: black;font-weight: bold'>"
                        . $products['attributes'] . '</div></div>';
                    $cart_html .= "<div style='width:30%;float:left;margin: 3% auto;"
                        . "text-align:center;text-transform:lowercase;'>x"
                        . $products['cart_quantity'] . '</div></div>';
                }
                $direct_check_out = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Direct Checkout',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= '<div style="float: right;margin-top: 1%;cursor:pointer;background: #6682B5;
                    background-image: -webkit-linear-gradient(top, #6682B5, #6682B5);
                    background-image: -moz-linear-gradient(top, #6682B5, #6682B5);
                    background-image: -ms-linear-gradient(top, #6682B5, #6682B5);
                    background-image: -o-linear-gradient(top, #6682B5, #6682B5);
                    background-image: linear-gradient(to bottom, #6682B5, #6682B5);-webkit-border-radius: 0;
                    -moz-border-radius: 0;border-radius: 0px;font-family: Arial;color: #ffffff;
                    font-size: 20px;padding: 10px 20px 10px 20px;text-decoration: none;">
                    <a style="text-decoration:none;color:white" href="'
                    . $direct_checkout_url . '">'
                    . $direct_check_out . '</a></div>';
            } elseif ($cart_data['cart_template'] == 8) {
                $cart = new Cart((int) $cart_data['id_cart']);
                $detail = $cart->getProducts();
                $sno = 1;
                $link = new Link();
                $direct_checkout_url = $this->getFrontActionLink('direct', $cart_data);

                foreach ($detail as $products) {
                    if (!isset($products['attributes'])) {
                        $products['attributes'] = ' ';
                    }
                    if (!isset($products['name'])) {
                        $products['name'] = ' ';
                    }
                    if (!isset($products['description_short'])) {
                        $products['description_short'] = ' ';
                    }
                    if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                        $img_path = 'https://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    } else {
                        $img_path = 'http://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    }

                    $action_url = $this->getFrontActionLink('single', $cart_data, $products['id_product']);
                    $cart_html .= "<div style='padding: 2%;'>";
                    $cart_html .= "<div style='text-align:center'><div style='padding:1%'>";
                    $cart_html .= "<a href='" . $action_url . "'><img style='max-width:40%' src='" .
                        $img_path . "'></a></div>";
                    $cart_html .= "<div style='border: 1px solid black;font-weight: bold;padding: 1%;"
                        . "font-size: 150%;margin: 0px auto;'>"
                        . $products['name'] . '</div></div></div>';
                    $cart_html .= "<div style='color: black;font-size: 120%;font-family: sans-serif;padding:1%;'>" .
                        $products['attributes'] . '</div>';
                }
                $direct_checkout_trans = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Direct Checkout',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= '<div style="margin-top: 2%;cursor:pointer;background: #4CAF50;
                    -webkit-border-radius: 0;-moz-border-radius: 0;
                    border-radius: 0px;font-family: Arial;color: #ffffff;font-size: 20px;
                    padding: 10px 20px 10px 20px;text-decoration: none;">
                    <a style="text-decoration:none;color:white" href="' . $direct_checkout_url . '">'
                    . $direct_checkout_trans . '</a></div>';
            } elseif ($cart_data['cart_template'] == 9) {
                $cart = new Cart((int) $cart_data['id_cart']);
                $detail = $cart->getProducts();
                $sno = 1;
                $link = new Link();
                $module_trans_item = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'ITEMS IN YOUR CART',
                    'abandonedcart',
                    $iso_code
                );
                $direct_checkout_url = $this->getFrontActionLink('direct', $cart_data);
                $cart_html .= "<hr><div style='color: #E91E63;display: block;font-size: 20px;"
                    . "text-align: center;margin: 0px auto;padding: 5px;line-height: 30px'>"
                    . $module_trans_item . "</div>
					<hr><ul style='list-style-type: none;padding:0px;overflow:auto;'>";

                foreach ($detail as $products) {
                    if (!isset($products['attributes'])) {
                        $products['attributes'] = ' ';
                    }
                    if (!isset($products['name'])) {
                        $products['name'] = ' ';
                    }
                    if (!isset($products['description_short'])) {
                        $products['description_short'] = ' ';
                    }
                    if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                        $img_path = 'https://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    } else {
                        $img_path = 'http://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    }
                    $module_trans_view_more = $this->getModuleTranslationByLanguage(
                        'abandonedcart',
                        'View More',
                        'abandonedcart',
                        $iso_code
                    );
                    $action_url = $this->getFrontActionLink('single', $cart_data, $products['id_product']);
                    $cart_html .= "<li style='width:45%;float:left;margin-bottom:2%;margin-right:2%;'>
                        <div style='text-align:center;border:1px solid gray;'><a href='"
                        . $action_url . "'><img style='max-width:100%' src='"
                        . $img_path . "'></a></div>";
                    $cart_html .= "<div style='text-align: center;margin: 5% auto;font-size: 16px;
                        font-weight: bolder;max-height:20px;text-overflow: ellipsis;
                        white-space:nowrap;overflow:hidden'><span>"
                        . $products['name'] . '</span></div>';
                    $cart_html .= "<div style='background: #00BCD4;color: #fff;display: block;
                        font-size: 120%;text-align: center;
                        margin: 2% auto;padding: 1%;line-height: 30px'>
                        <a style='text-decoration:none;color:white' href='" . $action_url . "'>"
                        . $module_trans_view_more . '</a></div></li>';
                }
                
                $module_trans_checkout = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Direct Checkout',
                    'abandonedcart',
                    $iso_code
                );
                
                $cart_html .= '</ul><hr><div style="float:right;background: #F44336;
                    color: #fff;display: block;font-size: 150%;
					text-align: center;margin: 0px auto;padding: 1%;line-height: 30px">
					<a style="text-decoration:none;color:white" href="'
                    . $direct_checkout_url . '">'
                    . $module_trans_checkout . '</a></div>';
            } elseif ($cart_data['cart_template'] == 10) {
                $cart = new Cart((int) $cart_data['id_cart']);
                $detail = $cart->getProducts();
                $sno = 1;
                $module_translation = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Items In Your Cart',
                    'abandonedcart',
                    $iso_code
                );
                $link = new Link();
                $direct_checkout_url = $this->getFrontActionLink('direct', $cart_data);
                $cart_html .= "<hr><div style='display: block;font-size: 20px;"
                        . "text-align: center;margin: 0px auto;padding: 5px;line-height: 30px'>"
                        . $module_translation . "</div>
						<hr><ul style='list-style-type: none;padding:0px;overflow:auto;'>";

                foreach ($detail as $products) {
                    if (!isset($products['attributes'])) {
                        $products['attributes'] = ' ';
                    }
                    if (!isset($products['name'])) {
                        $products['name'] = ' ';
                    }
                    if (!isset($products['description_short'])) {
                        $products['description_short'] = ' ';
                    }
                    if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                        $img_path = 'https://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    } else {
                        $img_path = 'http://' . $link->getImageLink($products['link_rewrite'], $products['id_image']);
                    }
                    $action_url = $this->getFrontActionLink('single', $cart_data, $products['id_product']);
                    $cart_html .= "<li style='overflow:auto;padding:1%;margin:0px auto;'>
                        <div style='text-align:center;width:28%;padding:1%;
                        float:left;border:1px solid gray;'><a href='"
                        . $action_url . "'><img style='max-width:100%' src='" . $img_path . "'></a></div>";
                    $cart_html .= "<div style='text-align:center;float:left;width:67%;padding:1%'>
                        <div style='font-size: 18px;
                        font-weight: bolder;padding:2%;
                        max-height:20px;text-overflow: ellipsis;white-space:nowrap;overflow:hidden'><span>" .
                        $products['name'] . "</span></div>
                        <div style='max-height:100%;text-overflow: ellipsis;overflow:hidden'>"
                        . trim($products['description_short'], '<p></p>') . '</div>';
                    $cart_html .= "<div style='background: #d14836;color: #fff;display: block;
                        font-size: 14px;width: 30%;
                        text-align: center;margin: 2% auto;line-height: 30px'>
                        <a style='text-decoration:none;color:white' href='"
                        . $action_url . "'>" .
                        $this->getModuleTranslationByLanguage(
                            'abandonedcart',
                            'View More',
                            'abandonedcart',
                            $iso_code
                        )
                        . '</a></div></div></li>';
                }
                $dr_ck_tr = $this->getModuleTranslationByLanguage(
                    'abandonedcart',
                    'Direct Checkout',
                    'abandonedcart',
                    $iso_code
                );
                $cart_html .= '</ul><hr><div style="float:right;background: #35AC19;
                    color: #fff;display: block;font-size: 150%;
                    text-align: center;margin: 0px auto;padding: 1%;
                    line-height: 30px"><a style="text-decoration:none;color:white" href="'
                    . $direct_checkout_url . '">' .
                    $dr_ck_tr .
                    '</a></div>';
            }
        }
        return $cart_html;
    }

    public function getFrontActionLink($mode, $cart_data, $id_product = 0)
    {
        $cart = new Cart($cart_data['id_cart']);
        $params = array();
        $ssl = (bool)Configuration::get('PS_SSL_ENABLED');
        $contoller_link = $this->context->link->getModuleLink(
            'abandonedcart',
            'action',
            $params,
            $ssl,
            $cart->id_lang
        );
        $dot_found = 0;
        $needle = 'index.php';
        $dot_found = strpos($contoller_link, $needle);
        if ($dot_found !== false) {
            $ch = '&';
        } else {
            $ch = '?';
        }

        $product_str = '';
        if ($mode == 'single') {
            $action = 'single_product';
            $product_str = '|' . $id_product;
        } else {
            $action = 'direct_checkout';
            $product_str = '|0';
        }
        $final_url = $contoller_link . $ch . 'action=' . $action;
        $hash_key = '';

        $hash_key .= $cart_data['id_cart'];
        $hash_key .= '|' . $cart_data['id_customer'];
        $hash_key .= '|' . $cart_data['id_abandon'];
        if (isset($cart_data['discount_code'])) {
            $hash_key .= '|' . $cart_data['discount_code'];
        } else {
            $hash_key .= '|0';
        }
        $hash_key .= '|' . urlencode($cart_data['customer_email']);
        $hash_key .= '|' . $cart_data['customer_secure_key'];
        $hash_key .= $product_str;

//		$hash_key .= '~^'.Configuration::get('VELSOF_ABD_SECURE_KEY');
        $final_url .= '&hash_key=' . str_rot13($hash_key);
        return $final_url;
    }

    protected function sendReminder($data, $use_saved_template = true)
    {
        if ($use_saved_template) {
            $id_template_content = $data['id_template_content'];
            $template_data = $this->loadEmailTemplateTranslation(0, 0, $id_template_content);
            $data['subject'] = $template_data['subject'];
            $data['body'] = $template_data['body'];
            $data['cart_template'] = $template_data['cart_template'];
        }

        $directory = $this->getTemplateDir();
        //Tools::chmodr($directory, 0755);
        if (is_writable($directory)) {
            $html_template = self::REMINDER_TEMPLATE_NAME . '.html';
            $txt_template = self::REMINDER_TEMPLATE_NAME . '.txt';

            $base_html = $this->getTemplateBaseHtml();

            $template_html = str_replace('{template_content}', $data['body'], $base_html);

            $file = fopen($directory . $html_template, 'w+');
            fwrite($file, $template_html);
            fclose($file);

            $file = fopen($directory . $txt_template, 'w+');
            fwrite($file, $template_html);
            fclose($file);

            $cart = new Cart($data['id_cart']);
            if (!$cart->nbProducts()) {
                return -2;
            }
            if ($data['id_customer'] != 0) {
                $customer = new Customer($data['id_customer']);
                $data['customer_email'] = $customer->email;
                $data['customer_fname'] = $customer->firstname;
                $data['customer_lname'] = $customer->lastname;
                $data['customer_secure_key'] = $customer->secure_key;
                unset($customer);
            } else {
                $fetch_qry = 'select firstname, lastname, email from ' . _DB_PREFIX_
                        . self::ABD_TRACK_CUSTOMERS_TABLE_NAME . ' where id_cart = ' . (int) $data['id_cart'];
                $user_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($fetch_qry);
                $aceg = ($user_data['firstname'] != '' && $user_data['lastname'] != '') ? $user_data['firstname'] : '';
                $bdjk = ($user_data['firstname'] != '' && $user_data['lastname'] != '') ? $user_data['lastname'] : '';
                if ($user_data && count($user_data) > 0) {
                    $data['customer_fname'] = $aceg;
                    $data['customer_lname'] = $bdjk;
                    $data['customer_email'] = $user_data['email'];
                    $data['customer_secure_key'] = 'none';
                } else {
                    return false;
                }
                unset($user_data);
            }

            $lang = new Language($cart->id_lang);
            $cart_lang = $lang->iso_code;
            if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $custom_ssl_var = 1;
            }
            if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
                $uri_path = _PS_BASE_URL_SSL_ . __PS_BASE_URI__;
            } else {
                $uri_path = _PS_BASE_URL_ . __PS_BASE_URI__;
            }
            $template_vars = array(
                '{shop_url_link}' => $uri_path . $cart_lang . '/',
                '{firstname}' => $data['customer_fname'],
                '{lastname}' => $data['customer_lname'],
                '{cart_content}' => $this->getCartHtml($data),
                '{total_amount}' => Cart::getTotalCart((int) $data['id_cart']),
                '{discount_value}' => '',
                '{discount_code}' => '',
                '{date_end}' => '',
            );

            $lang_iso = Configuration::get('VELSOF_ABANDONED_CART_DEFAULT_TEMPLATE_LANG');
            $id_lang = Language::getIdByIso($lang_iso);

            $config = Configuration::get('VELSOF_ABANDONEDCART');
            $this->my_module_settings = Tools::unSerialize($config);

            if (isset($this->my_module_settings['enable_test']) && $this->my_module_settings['enable_test'] == 1) {
                $data['customer_email'] = $this->my_module_settings['testing_email_id'];
            }

            $if_check1 = Mail::Send(
                $id_lang,
                self::REMINDER_TEMPLATE_NAME,
                $data['subject'],
                $template_vars,
                $data['customer_email'],
                $data['customer_fname'] . ' ' . $data['customer_lname'],
                Configuration::get('PS_SHOP_EMAIL'),
                Configuration::get('PS_SHOP_NAME'),
                null,
                null,
                _PS_MODULE_DIR_ . 'abandonedcart/mails/',
                false,
                $this->context->shop->id
            );

            if ($if_check1) {
                $mark_reminder_sent = 'update ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME . ' set
					reminder_sent= "' . (int) self::REMINDER_SENT . '" where id_abandon=' .
                        (int) $data['id_abandon'];
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($mark_reminder_sent);
                return true;
            } else {
                return false;
            }
        } else {
            return -1;
        }
    }

    public function sendDiscountEmail($data, $use_saved_template = true)
    {
        if ($use_saved_template) {
            $id_template_content = $data['id_template_content'];

            $template_data = $this->loadEmailTemplateTranslation(0, 0, $id_template_content);
            $data['subject'] = $template_data['subject'];
            $data['body'] = $template_data['body'];
            $data['cart_template'] = $template_data['cart_template'];
        }

        $directory = $this->getTemplateDir();
        //Tools::chmodr($directory, 0755);
        if (is_writable($directory)) {
            $html_template = self::DISCOUNT_TEMPLATE_NAME . '.html';
            $txt_template = self::DISCOUNT_TEMPLATE_NAME . '.txt';

            $base_html = $this->getTemplateBaseHtml();

            $template_html = str_replace('{template_content}', $data['body'], $base_html);
            $file = fopen($directory . $html_template, 'w+');
            fwrite($file, $template_html);
            fclose($file);

            $file = fopen($directory . $txt_template, 'w+');
            fwrite($file, $template_html);
            fclose($file);

            //Disable all previous coupons for passed customer
            $customer_info = new Customer((int) $data['id_customer']);
            $cart = new Cart((int) $data['id_cart']);
            if (!$cart->nbProducts()) {
                return -2;
            }
            $coupon_disable = 'Delete FROM ' . _DB_PREFIX_ . 'cart_rule where description ="ABD[' .
                    pSQL($customer_info->email) . ']"';
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($coupon_disable);

            if ($data['discount_type'] == parent::DISCOUNT_FIXED) {
                $is_used_partial = 1;
                $fixed_reduction = $data['discount_value'];
                $percent_reduction = 0;
            } else {
                $is_used_partial = 0;
                $fixed_reduction = 0;
                $percent_reduction = $data['discount_value'];
            }


            if ($data['min_cart_value'] <= 0 || $data['min_cart_value'] == '') {
                $data['min_cart_value'] = 0;
            }

            $rule_desc = Tools::htmlentitiesUTF8('ABD[' . $customer_info->email . ']');
            $coupon_code = $this->generateCouponCode();
            $coupon_expiry_date = date('Y-m-d 23:59:59', strtotime('+' . $data['coupon_validity'] . ' days'));

            //insert coupon details
            $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'cart_rule  SET
				id_customer = ' . (int) $data['id_customer'] . ',
				date_from = "' . pSQL(date('Y-m-d H:i:s', time())) . '",
				date_to = "' . pSQL($coupon_expiry_date) . '",
				description = "' . pSQL($rule_desc) . '",
				quantity = 1, quantity_per_user = 1, priority = 1, partial_use = ' . (int) $is_used_partial . ',
				code = "' . pSQL($coupon_code) . '", minimum_amount = ' . (float) $data['min_cart_value'] .
                    ', minimum_amount_tax = 0, 
				minimum_amount_currency = ' . (int) $cart->id_currency . ', minimum_amount_shipping = 0,
				country_restriction = 0, carrier_restriction = 0, group_restriction = 0, cart_rule_restriction = 0, 
				product_restriction = 0, shop_restriction = 1, 
				free_shipping = ' . (int) $data['has_free_shipping'] . ',
				reduction_percent = ' . (float) $percent_reduction . ', reduction_amount = ' .
                    (float) $fixed_reduction . ',
				reduction_tax = 1, reduction_currency = ' . (int) $cart->id_currency . ', 
				reduction_product = 0, gift_product = 0, gift_product_attribute = 0,
				highlight = 0, active = 1, 
				date_add = "' . pSQL(date('Y-m-d H:i:s', time())) . '", date_upd = "' .
                    pSQL(date('Y-m-d H:i:s', time())) . '"';

            Db::getInstance()->execute($sql);
            $cart_rule_id = Db::getInstance()->Insert_ID();

            Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'cart_rule_shop
				set id_cart_rule = ' . (int) $cart_rule_id . ', id_shop = ' . (int) $customer_info->id_shop);

            Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'cart_rule_lang
				set id_cart_rule = ' . (int) $cart_rule_id . ', id_lang = ' . (int) $customer_info->id_lang . ', 
				name = "' . pSQL($rule_desc) . '"');
            Configuration::updateGlobalValue('PS_CART_RULE_FEATURE_ACTIVE', '1');
            if ($data['discount_type'] == parent::DISCOUNT_FIXED) {
                $formatted_discount = Tools::displayprice($data['discount_value']);
            } else {
                $formatted_discount = Tools::ps_round($data['discount_value'], 2) . ' %';
            }

            $data['discount_code'] = $coupon_code;
            $data['customer_email'] = $customer_info->email;
            $data['customer_secure_key'] = $customer_info->secure_key;
            $lang = new Language($cart->id_lang);
            $cart_lang = $lang->iso_code;
            if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $custom_ssl_var = 1;
            }
            if ((bool) Configuration::get('PS_SSL_ENABLED') && $custom_ssl_var == 1) {
                $uri_path = _PS_BASE_URL_SSL_ . __PS_BASE_URI__;
            } else {
                $uri_path = _PS_BASE_URL_ . __PS_BASE_URI__;
            }
            $template_vars = array(
                '{shop_url_link}' => $uri_path . $cart_lang . '/',
                '{firstname}' => $customer_info->firstname,
                '{lastname}' => $customer_info->lastname,
                '{email}' => $customer_info->email,
                '{cart_content}' => $this->getCartHtml($data),
                '{discount_value}' => $formatted_discount,
                '{discount_code}' => $coupon_code,
                '{date_end}' => Tools::displayDate($coupon_expiry_date, null, true)
            );

            if ($data['min_cart_value'] <= 0 || $data['min_cart_value'] == '') {
                $template_vars['{total_amount}'] = Cart::getTotalCart((int) $data['id_cart']);
            } else {
                $template_vars['{total_amount}'] = Tools::displayPrice($data['min_cart_value']);
            }

            $lang_iso = Configuration::get('VELSOF_ABANDONED_CART_DEFAULT_TEMPLATE_LANG');
            $template_lng = Language::getIdByIso($lang_iso);

            $config = Configuration::get('VELSOF_ABANDONEDCART');
            $this->my_module_settings = Tools::unSerialize($config);

            if (isset($this->my_module_settings['enable_test']) && $this->my_module_settings['enable_test'] == 1) {
                $data['customer_email'] = $this->my_module_settings['testing_email_id'];
            }

            $if_check = Mail::Send(
                $template_lng,
                self::DISCOUNT_TEMPLATE_NAME,
                $data['subject'],
                $template_vars,
                $data['customer_email'],
                $customer_info->firstname . ' ' . $customer_info->lastname,
                Configuration::get('PS_SHOP_EMAIL'),
                Configuration::get('PS_SHOP_NAME'),
                null,
                null,
                _PS_MODULE_DIR_ . 'abandonedcart/mails/',
                false,
                $this->context->shop->id
            );

            if ($if_check) {
                if (isset($data['auto_email']) && $data['auto_email'] == 1) {
                    $no_auto_mail = 'update ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME .
                            ' set auto_email= "0" where id_abandon=' . (int) $data['id_abandon'];
                    Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($no_auto_mail);
                }
                $mark_reminder_sent = 'update ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME . ' set
					reminder_sent= "' . (int) self::REMINDER_SENT . '" where id_abandon=' .
                        (int) $data['id_abandon'];
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($mark_reminder_sent);
                return true;
            } else {
                return false;
            }
        } else {
            return -1;
        }
    }

    private function getCustomerDetail($id_customer)
    {
        $data = array();
        $customer = new Customer($id_customer);
        $data = array(
            'id_customer' => $id_customer,
            'fname' => $customer->firstname,
            'lname' => $customer->lastname,
            'email' => $customer->email
        );
        return $data;
    }

    private function getCustomerCouponDetail($id_customer, $email)
    {
        $data = array();
        $qry = 'select code, minimum_amount,date_from,date_to,reduction_percent,reduction_amount
			from ' . _DB_PREFIX_ . 'cart_rule as cr INNER JOIN ' . _DB_PREFIX_ . 'cart_rule_lang as crl 
			on (cr.id_cart_rule = crl.id_cart_rule)
			where cr.active = "1" and cr.id_customer = ' . (int) $id_customer .
                ' AND crl.name = "ABD[' . (string) $email . ']" 
			AND crl.id_lang = ' . (int) $this->context->language->id;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($qry);

        if ($result && count($result) > 0) {
            foreach ($result as $row) {
                $row['reduction_format'] = Tools::displayprice($row['reduction_amount']);
                $row['minimum_amount'] = Tools::displayprice($row['minimum_amount']);
                $data[] = $row;
            }
        }
        return $data;
    }

    private function getCustomerCartDetail($id_customer, $id_cart)
    {
        $data = array('customer' => array(), 'cart_total' => 0, 'products' => array());
        $cart = new Cart($id_cart);
        $detail = $cart->getProducts();
        $link = new Link();
        if ($detail && count($detail) > 0) {
            foreach ($detail as $product) {
                if ((bool) Configuration::get('PS_SSL_ENABLED')) {
                    $img_path = 'https://' . $link->getImageLink($product['link_rewrite'], $product['id_image']);
                } else {
                    $img_path = 'http://' . $link->getImageLink($product['link_rewrite'], $product['id_image']);
                }

                $product['pro_link'] = $this->context->link->getProductLink($product['id_product']);
                $product['img_link'] = $img_path;
                $product['price_wt'] = Tools::displayPrice($product['price_wt']);
                $product['total_wt'] = Tools::displayPrice($product['total_wt']);
                $data['products'][] = $product;
            }
            $data['cart_total'] = Tools::displayPrice($cart->getordertotal());
        }

        $data['customer'] = $this->getCustomerDetail($id_customer);
        return $data;
    }

    public function deleteAbandonCart($id_abandon)
    {
        $sql = 'update ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME . ' set shows= "0" where id_abandon=' .
                (int) $id_abandon;
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public function hookDisplayOrderConfirmation($params = null)
    {
        $order = $params['objOrder'];
        $check_abandon_sql = 'select * from ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME . ' where id_cart = ' .
                (int) $order->id_cart
                . ' AND reminder_sent = "' . (int) self::REMINDER_SENT . '"';
        $check_abandon = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($check_abandon_sql);
        if (is_array($check_abandon) && count($check_abandon) > 0) {
            $is_converted = 'update ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME . ' 
				set is_converted= "1", date_upd = "' . pSQL(date('Y-m-d H:i:s', time())) . '" 
				where id_abandon=' . (int) $check_abandon['id_abandon'];
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($is_converted);
        }

        $check_abandon_sql = 'select * from ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME . ' where id_cart = ' .
                (int) $order->id_cart
                . ' AND reminder_sent = "' . (int) self::REMINDER_NOT_SENT . '"';
        $check_cart = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($check_abandon_sql);
        if (is_array($check_cart) && count($check_cart) > 0) {
            $is_deleted = 'update ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME . ' 
				set shows= "0", date_upd = "' . pSQL(date('Y-m-d H:i:s', time())) . '" 
				where id_abandon=' . (int) $check_cart['id_abandon'];
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($is_deleted);
        }
    }

    public function hookActionCartSave($params = null)
    {
        if (isset($params['cart']) && !empty($params['cart'])) {
            $quantity = Db::getInstance()->getRow('select SUM(quantity) as total from '
                    . _DB_PREFIX_ . 'cart_product where id_cart=' . (int) $params['cart']->id);
            $check_query = 'select * from ' . _DB_PREFIX_ . self::INCENTIVE_MAPPING_TABLE_NAME . ' where
				id_cart=' . (int) $params['cart']->id . ' and quantity!=' . (int) $quantity['total'];
            if (Db::getInstance()->getRow($check_query)) {
                $delete_cart_mapping = 'DELETE from ' . _DB_PREFIX_ . self::INCENTIVE_MAPPING_TABLE_NAME .
                        ' where id_cart=' . (int) $params['cart']->id . ' and quantity!=-1';
                Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($delete_cart_mapping);
            }
        }
    }

    /*
     * Function modified by RS on 07-Sept-2017 for optimization purpose and also updating the Cart Totals in case the version has been updated
     */
    public function updateAbandonList($cron = false, $update_cart_total = false)
    {
        /* Start - Code Added by RS on 06-Sept-2017 for adding the memory limit and time limit before executing the code so that it doesn't times out */
        ini_set("memory_limit", "-1");
        set_time_limit(10000);
        /* End - Code Added by RS on 06-Sept-2017 for adding the memory limit and time limit before executing the code so that it doesn't times out */
        /*
         * Check the guest cart which are turned into registed user cart later
         */
        $configurations = unserialize(Configuration::get('VELSOF_ABANDONEDCART'));
        if ($configurations['enable'] != 1) {
            return false;
        }
        $qry = 'Select * from ' . _DB_PREFIX_ . parent::ABANDON_TABLE_NAME . ' where id_customer = 0';
        $guest_carts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($qry);
        if (is_array($guest_carts) && count($guest_carts) > 0) {
            foreach ($guest_carts as $c) {
                $t = 'Select id_customer from ' . _DB_PREFIX_ . 'cart 
					where id_cart = ' . (int) $c['id_cart'] . ' AND id_customer > 0';
                if ($id_customer = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($t)) {
                    $check_guest = 'select is_guest from ' . _DB_PREFIX_ . 'customer where id_customer = ' .
                            (int) $id_customer;
                    $is_guest = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($check_guest);
                    $up_qry = 'Update ' . _DB_PREFIX_ . parent::ABANDON_TABLE_NAME
                            . ' set id_customer = ' . (int) $id_customer . ', is_guest = "' .
                            (int) $is_guest['is_guest'] . '"
						WHERE id_cart = ' . (int) $c['id_cart'] . ' AND id_abandon = ' .
                            (int) $c['id_abandon'];
                    Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($up_qry);
                }
            }
        }

        $velsof_abandoncart_start_date = Configuration::get('VELSOF_ABANDONEDCART_START_DATE');

        $delay = (int) $configurations['delay_hours'] + (24 * (int) $configurations['delay_days']);
        $delay_time = date('Y-m-d H:i:s', strtotime('-' . $delay . ' hours'));
        /* Start - Code Modified by RS on 06-Sept-2017 for solving the problem of time delay on cron run when there are a lot of carts */
        $delay_time_hour = date('Y-m-d H:i:s', strtotime('-' . ($delay+1) . ' hours'));
        $update_analytics_condition = '';
        if (!$update_cart_total) {
            $update_analytics_condition = ' AND c.date_upd >= "' . pSQL($delay_time_hour) . '"';
        }
        $sql = 'select c.*,o.id_cart as ordered from ' . _DB_PREFIX_ . 'cart as c left JOIN '._DB_PREFIX_.'orders o on (o.id_cart=c.id_cart)
			WHERE c.date_upd >= "' . pSQL(date('Y-m-d H:i:s', strtotime($velsof_abandoncart_start_date))) . '"
            AND c.date_upd <= "' . pSQL($delay_time) . '"'.$update_analytics_condition; //combine
        /* End - Code Modified by RS on 06-Sept-2017 for solving the problem of time delay on cron run when there are a lot of carts */

        $carts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $carts_added = 0;
        if (is_array($carts) && count($carts) > 0) {
            foreach ($carts as $cart) {
                if (is_null($cart['ordered']) || $update_cart_total) {
                    /* Start - Code Modified by RS on 06-Sept-2017 for combining two queries in a single query ($consider_abandon_sql query has been removed and is combined in $sql) + Adding Order Total in the Abandoned Cart Table */
//                    $consider_abandon_sql = 'Select count(*) as row from ' . _DB_PREFIX_ . 'cart 
//						WHERE id_cart = ' . (int) $cart['id_cart'] . ' AND date_upd <= "' . pSQL($delay_time) . '"';
//                    $consider_abandon = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($consider_abandon_sql);
//                    if (is_array($consider_abandon) && $consider_abandon['row'] > 0) {
                    $cart_obj = new Cart((int) $cart['id_cart']);
                    $is_exist_sql = 'Select count(*) as row from ' . _DB_PREFIX_ . parent::ABANDON_TABLE_NAME .
                            ' where id_cart =' . (int) $cart['id_cart'];
                    $is_exist = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($is_exist_sql);
                    if (!is_array($is_exist) || $is_exist['row'] <= 0) {
                        $customer = new Customer((int) $cart['id_customer']);
                        $insert_abandon_data = 'INSERT into ' . _DB_PREFIX_ . parent::ABANDON_TABLE_NAME
                                . ' (id_cart,id_shop,id_lang,id_customer,is_guest,cart_total,date_add,date_upd) values ('
                                . (int) $cart['id_cart'] . ', '
                                . (int) $cart['id_shop'] . ', '
                                . (int) $cart['id_lang'] . ', '
                                . (int) $cart['id_customer'] . ', "' . (int) $customer->isGuest() . '", "'
                                . pSQL($cart_obj->getOrderTotal()) . '", "'
                                . pSQL($cart['date_add']) . '", "' . pSQL($cart['date_upd']) . '")';

                        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($insert_abandon_data);
                        $carts_added++;
                    } else {
                        $update_date_update = 'update ' . _DB_PREFIX_ . parent::ABANDON_TABLE_NAME . ' set
                                                date_upd = "' . pSQL($cart['date_upd']) . '", id_customer = "'.(int) $cart['id_customer'].'", cart_total = "'.pSQL($cart_obj->getOrderTotal()).'"  where id_cart = ' . (int) $cart['id_cart'];

                        Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($update_date_update);
                    }
//                    }
                    /* End - Code Modified by RS on 06-Sept-2017 for combining two queries in a single query ($consider_abandon_sql query has been removed and is combined in $sql) + Adding Order Total in the Abandoned Cart Table */
                }
            }
        }
        if ($cron && !$update_cart_total) {
            echo '<div class="text_to_show" style="font-size:16px;padding: 10px;">' . $this->l('Total') . ' '
            . $carts_added . ' ' . $this->l('new Carts added.') . '</div>';
            die;
        }
        if ($update_cart_total) {
            Configuration::updateGlobalValue('VELSOF_ABD_CART_TOTAL_ADDED', 0);
            echo '<div class="text_to_show" style="font-size:16px;padding: 10px;">' . $this->l('Analytics Data Updated, please refresh the admin panel.') . '</div>';
            die;
        }
    }

    /*
     * Function Modified by RS on 06-Sept-2017 for solving the problem of time delay on cron run when there are a lot of carts (changed the query in $abd_query variable)
     * Added the logic to process carts for sending emails on hourly basis, the logic will now pick the carts that qualifies the delay for the last hour only.
     */
    public function sendAutomaticIncentiveMails($triggered_from = false)
    {
        /* Start - Code Added by RS on 06-Sept-2017 for adding the memory limit and time limit before executing the code so that it doesn't times out */
        ini_set("memory_limit", "-1");
        set_time_limit(10000);
        /* End - Code Added by RS on 06-Sept-2017 for adding the memory limit and time limit before executing the code so that it doesn't times out */
        $settings = unserialize(Configuration::get('VELSOF_ABANDONEDCART'));
        if ($triggered_from) {
            if ($settings['enable'] == 1) {
                if ($settings['schedule'] != 1) {
                    echo '<div class="text_to_show" style="font-size:16px;padding: 5px;">'.
                        $this->l('`Enable Auto Email` setting has to be enabled to use this functionality.') . '</div>';
                    die;
                }
            } else {
                echo '<div class="text_to_show" style="font-size:16px;padding: 5px;">'.
                    $this->l('Please enable the Abandoned Cart module first.') . '</div>';
                die;
            }
        }
        $query = 'Select * from ' . _DB_PREFIX_ . self::INCENTIVE_TABLE_NAME .
                ' WHERE status = ' . (int) parent::INCENTIVE_ENABLE . '
			ORDER by delay_days DESC, delay_hrs DESC';

        $discounts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        $mails_sent = 0;
        if (count($discounts) > 0) {
            $abandon_id_to_be_skipped = array();
            foreach ($discounts as $discount) {
                $delay_in_hrs = (24 * (int) $discount['delay_days']) + (int) $discount['delay_hrs'];

                $abd_query = 'select abd.* from ' . _DB_PREFIX_ . self::ABANDON_TABLE_NAME .
                        ' as abd INNER JOIN ' . _DB_PREFIX_ . 'cart_product as cp on (abd.id_cart = cp.id_cart) 
					LEFT JOIN ' . _DB_PREFIX_ . 'customer as c on (abd.id_customer = c.id_customer) 
					where (abd.is_converted = "0" AND abd.shows = "1" AND abd.auto_email = "1"
					AND abd.id_shop = ' . (int) $this->context->shop->id . '
					AND abd.date_upd <= "' . pSQL(date('Y-m-d H:i:s', strtotime('-' .
                                                $delay_in_hrs . ' hours'))) . '"
                    AND abd.date_upd >= "' . pSQL(date('Y-m-d H:i:s', strtotime('-' .
                                                ($delay_in_hrs+1) . ' hours'))) . '")';
                $abd_carts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($abd_query);

                if (count($abd_carts) > 0) {
                    foreach ($abd_carts as $abd_cart) {
                        $cart_temp = new Cart($abd_cart['id_cart']);
                        if ($cart_temp->getOrderTotal() > $discount['min_cart_value_for_mails']) {
                            if (empty($abd_cart['id_customer']) || $abd_cart['id_customer'] == 0) {
                                if ($discount['incentive_type'] == self::NON_DISCOUNT_EMAIL) {
                                    $fetch_qry = 'select firstname, lastname, email from ' . _DB_PREFIX_
                                            . self::ABD_TRACK_CUSTOMERS_TABLE_NAME . ' where id_cart = ' .
                                            (int) $abd_cart['id_cart'];
                                    $user_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($fetch_qry);
                                    if (!$user_data) {
                                        continue;
                                    }
                                } else {
                                    continue;
                                }
                            }

                            if (in_array($abd_cart['id_abandon'], $abandon_id_to_be_skipped)) {
                                continue;
                            }

                            $check_incentive_status = 'select * from ' . _DB_PREFIX_ .
                                    self::INCENTIVE_MAPPING_TABLE_NAME .
                                    ' where id_cart = ' . (int) $abd_cart['id_cart'] . ' and
								id_incentive = ' . (int) $discount['id_incentive'];

                            $already_sent = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($check_incentive_status);
                            if (is_array($already_sent) && count($already_sent) > 0) {
                                continue;
                            }

                            $abandon_id_to_be_skipped[] = $abd_cart['id_abandon'];
                            $data = array();
                            if (!empty($discount['id_template']) && $discount['id_template'] > 0) {
                                $template_sql = 'SELECT * from ' . _DB_PREFIX_ . parent::TEMPLATE_CONTENT_TABLE_NAME .
                                        ' where id_template = ' . (int) $discount['id_template'] . ' AND id_lang = ' .
                                        (int) $abd_cart['id_lang'];
                                $template = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($template_sql);
                                if (is_array($template) && count($template) > 0) {
                                    $data['subject'] = Tools::htmlentitiesDecodeUTF8($template['subject']);
                                    $data['body'] = Tools::htmlentitiesDecodeUTF8($template['body']);
                                    $data['cart_template'] = $template['cart_template'];
                                } else {
                                    $data['subject'] = Tools::htmlentitiesDecodeUTF8(parent::DEFAULT_TEMPLATE_SUBJECT);
                                    $data['body'] = Tools::htmlentitiesDecodeUTF8($this->getDefaultEmailTemplate(1));
                                    $data['cart_template'] = 1;
                                }
                            } else {
                                $data['subject'] = Tools::htmlentitiesDecodeUTF8(parent::DEFAULT_TEMPLATE_SUBJECT);
                                $data['body'] = Tools::htmlentitiesDecodeUTF8($this->getDefaultEmailTemplate(1));
                                $data['cart_template'] = 1;
                            }
                            $mail_sent = false;
                            if ($discount['incentive_type'] == self::DISCOUNT_EMAIL) {
                                $data['id_customer'] = $abd_cart['id_customer'];
                                $data['discount_type'] = $discount['discount_type'];
                                $data['discount_value'] = $discount['discount_value'];
                                $data['id_cart'] = $abd_cart['id_cart'];
                                $data['min_cart_value'] = $discount['min_cart_value'];
                                $data['coupon_validity'] = $discount['coupon_validity'];
                                $data['has_free_shipping'] = $discount['has_free_shipping'];
                                $data['id_abandon'] = $abd_cart['id_abandon'];
                                $data['id_lang'] = $abd_cart['id_lang'];
                                $mail_sent = $this->sendDiscountEmail($data, false);
                            } elseif ($discount['incentive_type'] == self::NON_DISCOUNT_EMAIL) {
                                $data['id_customer'] = $abd_cart['id_customer'];
                                $data['id_cart'] = $abd_cart['id_cart'];
                                $data['id_abandon'] = $abd_cart['id_abandon'];
                                $data['id_lang'] = $abd_cart['id_lang'];
                                $mail_sent = $this->sendReminder($data, false);
                            }
                            if ($mail_sent) {
                                if ((int) $mail_sent == -1) {
                                    continue;
                                }

                                $quantity = Db::getInstance()->getRow('select SUM(quantity) as total from '
                                        . _DB_PREFIX_ . 'cart_product where id_cart=' . (int) $abd_cart['id_cart']);

                                $sql = 'INSERT INTO ' . _DB_PREFIX_ . self::INCENTIVE_MAPPING_TABLE_NAME . ' (id_cart, 
								id_customer, id_incentive, quantity, date_add) values('
                                        . (int) $abd_cart['id_cart'] . ',' . (int) $abd_cart['id_customer'] . ',
								' . (int) $discount['id_incentive'] . ', ' . (int) $quantity['total'] . ', now())';
                                Db::getInstance()->execute($sql);
                                
                                $less_delay_inc = $this->getIncentivesHavingLessDelay($discount['id_incentive'], $delay_in_hrs);
                                if (count($less_delay_inc) > 0) {
                                    foreach ($less_delay_inc as $less_inc) {
                                        $check_inc_status = 'select * from ' . _DB_PREFIX_ .
                                                self::INCENTIVE_MAPPING_TABLE_NAME .
                                                ' where id_cart = ' . (int) $abd_cart['id_cart'] . ' and
                                            id_incentive = ' . (int) $less_inc;

                                        $inc_sent = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($check_inc_status);
                                        if (is_array($inc_sent) && count($inc_sent) > 0) {
                                            continue;
                                        }
                                        $sql = 'INSERT INTO ' . _DB_PREFIX_ . self::INCENTIVE_MAPPING_TABLE_NAME . ' (id_cart, 
                                        id_customer, id_incentive, quantity, date_add) values('
                                                . (int) $abd_cart['id_cart'] . ',' . (int) $abd_cart['id_customer'] . ',
                                        ' . (int) $less_inc . ', ' . (int) $quantity['total'] . ', now())';
                                        Db::getInstance()->execute($sql);
                                    }
                                }
                                echo '<div class="text_to_show" style="font-size:14px;padding: 5px;">' .
                                $this->l('Mail Successfully Sent to Customer')
                                . ' (id #' . $abd_cart['id_customer'] . ') </div><br>';
                                $mails_sent++;
                            }
                        }
                    }
                }
            }
        }
        echo '<div class="text_to_show" style="font-size:16px;padding: 5px;">'
        . $this->l('Total') . ' ' . $mails_sent . ' ' . $this->l('Mails sent successfully.') . '</div>';
        die;
    }

    /*
     * Code Modified by RS on 07-Sept-2017 to Remove the analytics() function as it is not used any more.
     */

    /*
     * Function Modified by RS on 07-Sept-2017 to Optimize the Analytics Process
     */
    public function graph($from, $to)
    {
        $data = array();
        $data_form = unserialize(Configuration::get('VELSOF_ABANDONEDCART'));
        $total_delay = $data_form['delay_hours'] + ( 24 * $data_form['delay_days']);
        $delay_date = date('Y-m-d H:i:s', strtotime('-' . $total_delay . ' hours'));

        $velsof_abandoncart_start_date = (Configuration::get('VELSOF_ABANDONEDCART_START_DATE'));

        $start_datetime = strtotime($from);

        $end_datetime = strtotime($to);

        $from_date = explode('-', $from);
        $to_date = explode('-', $to);

        $range = '';
        if ($from_date[0] == $to_date[0] && $from_date[1] == $to_date[1] && $from_date[2] == $to_date[2]) {
            $range = 'hour';
        } elseif ($from_date[0] == $to_date[0] && $from_date[1] == $to_date[1]) {
            $range = 'day';
        } elseif ($from_date[0] == $to_date[0]) {
            $range = 'month';
        } else {
            $range = 'year';
        }

        $filter_string = '';
        switch ($range) {
            case 'hour':
                $data['stats']['type'] = 'Hour';
                $data['stats']['from'] = $from;
                $data['stats']['to'] = $to;
                $date = date('Y-m-d', strtotime($from));
                for ($i = 0; $i < 24; $i++) {
                    $filter_string = ' and date_upd > "' . pSQL($velsof_abandoncart_start_date) . '" 
					and HOUR(date_upd) ="' . (int) $i . '" 
					and date(date_upd) between "' . pSQL($from) . '" and "' . pSQL($to) . '"
					and date_upd < "' . pSQL($delay_date) . '"';

                    $total_carts = $this->getCartsBasedOnFilters($filter_string);
                    $total_cart_abandon = 0;
                    $total_cart_converted = 0;
                    $total_abandon = 0;
                    $total_converted = 0;
                    foreach ($total_carts as $cart) {
                        if ((int) $cart['is_converted'] == 1) {
                            $total_converted += (float) $cart['cart_total'];
                            $total_cart_converted++;
                        } else if ((int) $cart['is_converted'] == 0) {
                            $total_abandon += (float) $cart['cart_total'];
                            $total_cart_abandon++;
                        }
                    }
                    if ($total_cart_abandon) {
                        $data['stats']['abandon_carts'][] = array(
                            date('h A', mktime($i, 0, 0, date('n'), date('j'), date('Y'))),
                            $total_cart_abandon
                        );
                    } else {
                        $data['stats']['abandon_carts'][] = array(
                            date('h A', mktime($i, 0, 0, date('n'), date('j'), date('Y'))),
                            0
                        );
                    }
                    if ($total_cart_converted) {
                        $data['stats']['converted_carts'][] = array(
                            date('h A', mktime($i, 0, 0, date('n'), date('j'), date('Y'))),
                            $total_cart_converted
                        );
                    } else {
                        $data['stats']['converted_carts'][] = array(
                            date('h A', mktime($i, 0, 0, date('n'), date('j'), date('Y'))),
                            0
                        );
                    }
                    if ($total_abandon) {
                        $data['stats']['abandon_amount'][] = array(
                            date('h A', mktime($i, 0, 0, date('n'), date('j'), date('Y'))),
                            $total_abandon
                        );
                    } else {
                        $data['stats']['abandon_amount'][] = array(
                            date('h A', mktime($i, 0, 0, date('n'), date('j'), date('Y'))),
                            0
                        );
                    }
                    if ($total_converted) {
                        $data['stats']['converted_amount'][] = array(
                            date('h A', mktime($i, 0, 0, date('n'), date('j'), date('Y'))),
                            $total_converted
                        );
                    } else {
                        $data['stats']['converted_amount'][] = array(
                            date('h A', mktime($i, 0, 0, date('n'), date('j'), date('Y'))),
                            0
                        );
                    }
                }
                break;

            case 'day':
                $data['stats']['type'] = 'Day';
                for ($i = date('d', $start_datetime); $i <= date('d', $end_datetime); $i++) {
                    $date = date('Y', $start_datetime) . '-' . date('m', $start_datetime) . '-' . $i;
                    $filter_string = 'and date_upd > "' . pSQL($velsof_abandoncart_start_date) . '" 
					and DATE(date_upd) ="' . pSQL($date) . '" 
					and date(date_upd) between "' . pSQL($from) . '" and "' . pSQL($to) . '" 
					and date_upd < "' . pSQL($delay_date) . '"';

                    $total_carts = $this->getCartsBasedOnFilters($filter_string);
                    $total_cart_abandon = 0;
                    $total_cart_converted = 0;
                    $total_abandon = 0;
                    $total_converted = 0;
                    foreach ($total_carts as $cart) {
                        if ((int) $cart['is_converted'] == 1) {
                            $total_converted += (float) $cart['cart_total'];
                            $total_cart_converted++;
                        } else if ((int) $cart['is_converted'] == 0) {
                            $total_abandon += (float) $cart['cart_total'];
                            $total_cart_abandon++;
                        }
                    }
                    if ($total_cart_abandon) {
                        $data['stats']['abandon_carts'][] = array(date('M j', strtotime($date)), $total_cart_abandon);
                    } else {
                        $data['stats']['abandon_carts'][] = array(date('M j', strtotime($date)), 0);
                    }
                    if ($total_cart_converted) {
                        $data['stats']['converted_carts'][] = array(date('M j', strtotime($date)), $total_cart_converted);
                    } else {
                        $data['stats']['converted_carts'][] = array(date('M j', strtotime($date)), 0);
                    }
                    if ($total_abandon) {
                        $data['stats']['abandon_amount'][] = array(
                            date('M j', strtotime($date)),
                            $total_abandon
                        );
                    } else {
                        $data['stats']['abandon_amount'][] = array(date('M j', strtotime($date)), 0);
                    }
                    if ($total_converted) {
                        $data['stats']['converted_amount'][] = array(date('M j', strtotime($date)), $total_converted);
                    } else {
                        $data['stats']['converted_amount'][] = array(date('M j', strtotime($date)), 0);
                    }
                }
                break;

            case 'month':
                $data['stats']['type'] = 'Month';
                for ($i = date('m', $start_datetime); $i <= date('m', $end_datetime); $i++) {
                    $date = date('Y', $start_datetime) . '-' . date('m', $start_datetime) . '-' . $i;
                    $filter_string = 'and date_upd > "' . pSQL($velsof_abandoncart_start_date) . '"
					and YEAR(date_upd) = "' . pSQL(date('Y', $start_datetime)) . '" AND MONTH(date_upd) = "' .
                    (int) $i . '"
					and date(date_upd) between "' . pSQL($from) . '" and "' . pSQL($to) . '"
					and date_upd < "' . pSQL($delay_date) . '"';

                    $total_carts = $this->getCartsBasedOnFilters($filter_string);
                    $total_cart_abandon = 0;
                    $total_cart_converted = 0;
                    $total_abandon = 0;
                    $total_converted = 0;
                    foreach ($total_carts as $cart) {
                        if ((int) $cart['is_converted'] == 1) {
                            $total_converted += (float) $cart['cart_total'];
                            $total_cart_converted++;
                        } else if ((int) $cart['is_converted'] == 0) {
                            $total_abandon += (float) $cart['cart_total'];
                            $total_cart_abandon++;
                        }
                    }
                    if ($total_cart_abandon) {
                        $data['stats']['abandon_carts'][] = array(
                            date('M', mktime(0, 0, 0, $i, 1, date('Y'))),
                            $total_cart_abandon
                        );
                    } else {
                        $data['stats']['abandon_carts'][] = array(
                            date('M', mktime(0, 0, 0, $i, 1, date('Y'))),
                            0
                        );
                    }
                    if ($total_cart_converted) {
                        $data['stats']['converted_carts'][] = array(
                            date('M', mktime(0, 0, 0, $i, 1, date('Y'))),
                            $total_cart_converted
                        );
                    } else {
                        $data['stats']['converted_carts'][] = array(
                            date('M', mktime(0, 0, 0, $i, 1, date('Y'))),
                            0
                        );
                    }
                    if ($total_abandon) {
                        $data['stats']['abandon_amount'][] = array(
                            date('M', mktime(0, 0, 0, $i, 1, date('Y'))),
                            $total_abandon
                        );
                    } else {
                        $data['stats']['abandon_amount'][] = array(date('M', mktime(0, 0, 0, $i, 1, date('Y'))), 0);
                    }
                    if ($total_converted) {
                        $data['stats']['converted_amount'][] = array(
                            date('M', mktime(0, 0, 0, $i, 1, date('Y'))),
                            $total_converted);
                    } else {
                        $data['stats']['converted_amount'][] = array(date('M', mktime(0, 0, 0, $i, 1, date('Y'))), 0);
                    }
                }
                break;
            case 'year':
                $data['stats']['type'] = 'Year';
                for ($i = date('Y', $start_datetime); $i <= date('Y', $end_datetime); $i++) {
                    $filter_string = 'and date_upd > "' . pSQL($velsof_abandoncart_start_date) . '" 
					and  YEAR(date_upd) = "' . (int) $i . '" 
					and date(date_upd) between "' . pSQL($from) . '" and "' . pSQL($to) . '" 
					and date_upd < "' . pSQL($delay_date) . '"';

                    $total_carts = $this->getCartsBasedOnFilters($filter_string);
                    $total_cart_abandon = 0;
                    $total_cart_converted = 0;
                    $total_abandon = 0;
                    $total_converted = 0;
                    foreach ($total_carts as $cart) {
                        if ((int) $cart['is_converted'] == 1) {
                            $total_converted += (float) $cart['cart_total'];
                            $total_cart_converted++;
                        } else if ((int) $cart['is_converted'] == 0) {
                            $total_abandon += (float) $cart['cart_total'];
                            $total_cart_abandon++;
                        }
                    }
                    if ($total_cart_abandon) {
                        $data['stats']['abandon_carts'][] = array($i, $total_cart_abandon);
                    } else {
                        $data['stats']['abandon_carts'][] = array($i, 0);
                    }
                    if ($total_cart_converted) {
                        $data['stats']['converted_carts'][] = array($i, $total_cart_converted);
                    } else {
                        $data['stats']['converted_carts'][] = array($i, 0);
                    }
                    if ($total_abandon) {
                        $data['stats']['abandon_amount'][] = array($i, $total_abandon);
                    } else {
                        $data['stats']['abandon_amount'][] = array($i, 0);
                    }
                    if ($total_converted) {
                        $data['stats']['converted_amount'][] = array($i, $total_converted);
                    } else {
                        $data['stats']['converted_amount'][] = array($i, 0);
                    }
                }
                break;
        }
        return $data;
    }

    public function getModuleTranslationByLanguage($module, $string, $source, $language, $sprintf = null, $js = false)
    {
        $modules = array();
        $langadm = array();

        $translations_merged = array();
        $name = $module instanceof Module ? $module->name : $module;
        if (!isset($translations_merged[$name]) && isset(Context::getContext()->language)) {
            $files_by_priority = array(
                _PS_MODULE_DIR_ . $name . '/translations/' . $language . '.php'
            );

            foreach ($files_by_priority as $file) {
                if (file_exists($file)) {
                    include($file);
                    /* No need to define $_MODULE as it is defined in the above included file. */
                    $modules = $_MODULE;
                    $translations_merged[$name] = true;
                }
            }
        }

        $string = preg_replace("/\\\*'/", "\'", $string);
        $key = md5($string);

        if ($modules == null) {
            if ($sprintf !== null) {
                $string = Translate::checkAndReplaceArgs($string, $sprintf);
            }

            return str_replace('"', '&quot;', $string);
        }

        $current_key = Tools::strtolower('<{' . $name . '}' . _THEME_NAME_ . '>' . $source) . '_' . $key;
        $default_key = Tools::strtolower('<{' . $name . '}prestashop>' . $source) . '_' . $key;

        if ('controller' == Tools::substr($source, -10, 10)) {
            $file = Tools::substr($source, 0, -10);
            $current_key_file = Tools::strtolower('<{' . $name . '}' . _THEME_NAME_ . '>' . $file) . '_' . $key;
            $default_key_file = Tools::strtolower('<{' . $name . '}prestashop>' . $file) . '_' . $key;
        }

        if (isset($current_key_file) && !empty($modules[$current_key_file])) {
            $ret = Tools::stripslashes($modules[$current_key_file]);
        } elseif (isset($default_key_file) && !empty($modules[$default_key_file])) {
            $ret = Tools::stripslashes($modules[$default_key_file]);
        } elseif (!empty($modules[$current_key])) {
            $ret = Tools::stripslashes($modules[$current_key]);
        } elseif (!empty($modules[$default_key])) {
            $ret = Tools::stripslashes($modules[$default_key]);
            // if translation was not found in module, look for it in AdminController or Helpers
        } elseif (!empty($langadm)) {
            $ret = Tools::stripslashes(Translate::getGenericAdminTranslation($string, $key, $langadm));
        } else {
            $ret = Tools::stripslashes($string);
        }

        if ($sprintf !== null) {
            $ret = Translate::checkAndReplaceArgs($ret, $sprintf);
        }

        if ($js) {
            $ret = addslashes($ret);
        } else {
            $ret = htmlspecialchars($ret, ENT_COMPAT, 'UTF-8');
        }
        return $ret;
    }
    
    /*
     * Function added by RS on 07-Sept-2017 for adding additional translations to language file.
     */
    public function includeAdditionalTranslations()
    {
        $this->l('Direct Checkout');
    }
}
