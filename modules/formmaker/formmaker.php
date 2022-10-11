<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2017 silbersaiten
 * @version   1.3.11
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

class FormMaker extends Module
{
    private static $upload_path = '';
    private $current_product_form = false;
    public $validation_methods = array();
    public $vt = 't17';

    private static $queries = array(
    'fm_form' => '
        CREATE TABLE IF NOT EXISTS `%PREFIX%fm_form` (
        `id_fm_form` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `active` tinyint(1) NOT NULL DEFAULT \'0\',
        `redirect_on_success` tinyint(1) NOT NULL DEFAULT \'0\',
        `send_autoresponse` tinyint(1) NOT NULL DEFAULT \'0\',
        `receivers` text NOT NULL,
        `submit_delay` int(10) unsigned NOT NULL,
        `captcha` tinyint(1) NOT NULL DEFAULT \'0\',
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`id_fm_form`)
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8',

    'fm_form_lang' => '
        CREATE TABLE IF NOT EXISTS `%PREFIX%fm_form_lang` (
        `id_fm_form` int(10) unsigned NOT NULL,
        `id_lang` int(10) unsigned NOT NULL,
        `name` varchar(128) NOT NULL,
        `page_title` varchar(128) DEFAULT NULL,
        `link_rewrite` varchar(128) NOT NULL,
        `description` text,
        `message_on_completed` text,
        `submit_button` varchar(100) DEFAULT NULL,
        `meta_description` varchar(255) DEFAULT NULL,
        `meta_keywords` varchar(255) DEFAULT NULL,
        `meta_title` varchar(128) DEFAULT NULL,
        PRIMARY KEY (`id_fm_form`, `id_lang`)
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8',
        
    'fm_form_group' => '
        CREATE TABLE IF NOT EXISTS `%PREFIX%fm_form_group` (
        `id_fm_form` int(10) unsigned NOT NULL,
        `id_group` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_fm_form`, `id_group`)
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8',

    'fm_form_products' => '
        CREATE TABLE IF NOT EXISTS `%PREFIX%fm_form_products` (
        `id_fm_form` int(10) unsigned NOT NULL,
        `id_product` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_fm_form`, `id_product`)
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8',

    'fm_form_element' => '
        CREATE TABLE IF NOT EXISTS `%PREFIX%fm_form_element` (
        `id_fm_form_element` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_fm_form` int(10) unsigned NOT NULL,
        `type` int(10) unsigned NOT NULL DEFAULT \'0\',
        `required` tinyint(1) NOT NULL DEFAULT \'0\',
        `css_class` varchar(255) DEFAULT NULL,
        `settings` text DEFAULT NULL,
        `position` int(10) unsigned NOT NULL,
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`id_fm_form_element`)
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8',

    'fm_form_element_lang' => '
        CREATE TABLE IF NOT EXISTS `%PREFIX%fm_form_element_lang` (
        `id_fm_form_element` int(10) unsigned NOT NULL,
        `id_lang` int(10) unsigned NOT NULL,
        `name` varchar(128) NOT NULL,
        `description` text DEFAULT NULL,
        PRIMARY KEY (`id_fm_form_element`, `id_lang`)
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8',

    'fm_form_element_value' => '
        CREATE TABLE IF NOT EXISTS `%PREFIX%fm_form_element_value` (
        `id_fm_form_element_value` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_fm_form_element` int(10) unsigned NOT NULL,
        `position` int(10) unsigned NOT NULL,
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`id_fm_form_element_value`)
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8',

    'fm_form_element_value_lang' => '
        CREATE TABLE IF NOT EXISTS `%PREFIX%fm_form_element_value_lang` (
        `id_fm_form_element_value` int(10) unsigned NOT NULL,
        `id_lang` int(10) unsigned NOT NULL,
        `name` varchar(128) NOT NULL,
        PRIMARY KEY (`id_fm_form_element_value`, `id_lang`)
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8',

    'fm_form_shop' => '
        CREATE TABLE IF NOT EXISTS `%PREFIX%fm_form_shop` (
        `id_fm_form` int(10) NOT NULL,
        `id_shop` int(10) NOT NULL,
        PRIMARY KEY (`id_fm_form`,`id_shop`),
        KEY `id_shop` (`id_shop`)
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8',
        
    'fm_form_report' => '
        CREATE TABLE IF NOT EXISTS `%PREFIX%fm_form_report` (
        `id_fm_form_report` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_fm_form` int(10) unsigned NOT NULL,
        `id_customer` int(10) unsigned NOT NULL,
        `id_product` int(10) unsigned DEFAULT NULL,
        `name` varchar(128) NOT NULL,
        `send` tinyint(1) NOT NULL DEFAULT \'0\',
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`id_fm_form_report`)
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8',
        
    'fm_form_report_values' => '
        CREATE TABLE IF NOT EXISTS `%PREFIX%fm_form_report_values` (
        `id_fm_form_report` int(10) unsigned NOT NULL,
        `type` int(10) unsigned NOT NULL DEFAULT \'0\',
        `field` varchar(128) NOT NULL,
        `value` text
        ) ENGINE=%ENGINE% DEFAULT CHARSET=utf8',
    );

    public function __construct()
    {
        $this->name = 'formmaker';
        $this->version = '1.3.11';
        $this->tab = 'front_office_features';
        $this->author = 'Silbersaiten';
        $this->module_key = '065b3a3bf8645bef7ad06ee954b6857c';
        $this->controllers = array('form', 'formsuccess');
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Form Maker');
        $this->description = $this->l('Form Maker');

        self::$upload_path = dirname(__FILE__).'/uploads/';

        require_once(dirname(__FILE__).'/classes/FormMakerForm.php');
        require_once(dirname(__FILE__).'/classes/FormMakerElement.php');
        require_once(dirname(__FILE__).'/classes/FormMakerElementValue.php');
        require_once(dirname(__FILE__).'/classes/FormMakerReport.php');
        require_once(dirname(__FILE__).'/classes/FormMakerCaptcha.php');

        if (version_compare('1.7.0.0', _PS_VERSION_, '>')) {
            $this->vt = 't16';
        }

        if (!isset($this->context->smarty->registered_plugins['function']['displayForm'])) {
            smartyRegisterFunction(
                $this->context->smarty,
                'function',
                'displayForm',
                array('FormMaker', 'displayForm')
            );
        }
        
        $this->validation_methods = array(
            'isEmail' => array(
                'name' => $this->l('Email'),
                'error_text' => $this->l('Please enter a valid email address')
            ),
            'isFloat' => array(
                'name' => $this->l('Number'),
                'error_text' => $this->l('Invalid value, number is expected')
            ),
            'isUrl' => array(
                'name' => $this->l('URL'),
                'error_text' => $this->l('Invalid value, URL is expected')
            ),
            'isGenericName' => array(
                'name' => $this->l('Generic Alphanumeric Value'),
                'error_text' => $this->l('Invalid value, please use only alphanumeric characters')
            ),
            'isPostCode' => array(
                'name' => $this->l('Post Code'),
                'error_text' => $this->l('Invalid value, please enter a valid post code')
            ),
            'isPhoneNumber' => array(
                'name' => $this->l('Phone number'),
                'error_text' => $this->l('Invalid value, please enter a valid phone number')
            ),
            'isMessage' => array(
                'name' => $this->l('Text (no html allowed)'),
                'error_text' => $this->l('Invalid value, please do not use any special characters and/or html tags')
            ),
            'isCleanHtml' => array(
                'name' => $this->l('Valid HTML'),
                'error_text' => $this->l('Invalid value, please use valid HTML only')
            ),
            'isBool' => array(
                'name' => $this->l('Boolean (yes/no, 1/0) value'),
                'error_text' => $this->l('Invalid value, expected yes/no')
            )
        );
    }

    public function install($delete_params = true)
    {
        if (parent::install()
            && $this->registerHook('onFormSubmit')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('displayProductTab')
            && $this->registerHook('displayProductTabContent')
            && $this->registerHook('displayAdminAdCmsMenu')
            && $this->registerHook('displayAdminAdCmsBlockContents')
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('actionBlockDataPrefilter')) {
            if ($delete_params) {
                foreach (self::$queries as $query) {
                    $query = strtr($query, array('%PREFIX%' => _DB_PREFIX_, '%ENGINE%' => _MYSQL_ENGINE_));

                    if (!Db::getInstance()->Execute($query)) {
                        $this->uninstall();

                        return false;
                    }
                }
            }
            Configuration::updateValue('FM_CONTACT_FORM', 0);
            $this->installTab('AdminFormSettings', 'Forms');
            $this->installTab('AdminFormReport', 'Form Reports', false);

            return true;
        }

        return false;
    }

    public function uninstall($delete_params = true)
    {
        if (parent::uninstall()) {
            if ($delete_params) {
                foreach (array_keys(self::$queries) as $table) {
                    Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.pSQL($table).'`');
                }
            }

            $this->uninstallTab('AdminFormSettings');

            if (version_compare('1.7.0.0', _PS_VERSION_, '<=')) {
                $this->changeFiles();
            }

            return true;
        }

        return false;
    }

    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }
        if (!$this->install(false)) {
            return false;
        }

        return true;
    }

    public function installTab($tab_class, $tab_name, $active = true, $parent = 'AdminParentPreferences')
    {
        $tab = new Tab();
        $tab->active = $active;
        $tab->class_name = $tab_class;
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tab_name;
        }

        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->module = $this->name;

        return $tab->add();
    }

    public function uninstallTab($tab_class)
    {
        $id_tab = (int)Tab::getIdFromClassName($tab_class);

        if ($id_tab) {
            $tab = new Tab($id_tab);

            return $tab->delete();
        }

        return false;
    }

    public function changeFiles() {
        $files = array(
            'contact.tpl' => 'templates/'
        );
        foreach ($files as $file => $path) {
            $new_file = _PS_MODULE_DIR_.$this->name.'/override_tpl/'.$file;
            $old_file = _PS_THEME_DIR_.$path.$file;
            if (file_exists($new_file) && file_exists($old_file)) {
                $nl_file = $old_file.'_nl';
                if (!file_exists($nl_file)) {
                    rename($old_file, $nl_file);
                }
                copy($new_file, $old_file);
            }
        }
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminFormSettings', true));
    }
    
    public function collectMissingEmailTemplates($lang = false)
    {
        $template_list = array(
            'form_email' => $this->l('Form Email Template'),
            'autoresponse' => $this->l('Automatic Response Template')
        );
        
        if (!$lang) {
            $languages = Language::getLanguages(true);
        } else {
            $languages = array();
            $languages[] = $lang;
        }
        
        
        $missing = array();
        
        foreach ($languages as $lang) {
            $path = dirname(__FILE__).'/mails/'.$lang['iso_code'].'/';
            
            if (!file_exists($path)) {
                foreach ($template_list as $template => $template_name) {
                    $missing[] = array(
                        'language'      => $lang['name'],
                        'template'      => $template,
                        'template_name' => $template_name
                    );
                }
            } else {
                foreach ($template_list as $template => $template_name) {
                    if (!file_exists($path.$template.'.html') || !file_exists($path.$template.'.txt')) {
                        $missing[] = array(
                            'language'      => $lang['name'],
                            'template'      => $template,
                            'template_name' => $template_name
                        );
                    }
                }
            }
        }
        
        return count($missing) ? $missing : false;
    }
    
    public function hookModuleRoutes($params)
    {
        unset($params);
        return array(
            'module-formmaker-form' => array(
                'controller' => 'form',
                'rule' => 'forms{/:id_form}{/:rewrite}',
                'keywords' => array(
                    'id_form' => array('regexp' => '[0-9]+', 'param' => 'id_form'),
                    'rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]+', 'param' => 'rewrite'),
                    'module' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
                    'controller' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'formmaker',
                    'controller' => 'form',
                )
            )
        );
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        unset($params);
        if ($this->context->controller instanceof AdminFormSettingsController) {
            return '
            <script type="text/javascript">
            var productSearchPath = "'.$this->_path.'ajax_search.php";
            var controllerPath = "index.php?controller=AdminFormSettings&token='.Tools::getAdminTokenLite('AdminFormSettings', $this->context).'";

            var formmakerTranslate = {
                "Field Settings": "'.$this->l('Field Settings').'",
                "Field Values": "'.$this->l('Field Values').'",
                "Label": "'.$this->l('Label').'",
                "Required": "'.$this->l('Required').'",
                "Class": "'.$this->l('Class').'",
                "Field": "'.$this->l('Field').'",
                "Description": "'.$this->l('Description').'",
                "HTML Block": "'.$this->l('HTML Block').'",
                "Text Input": "'.$this->l('Text Input').'",
                "Password Input": "'.$this->l('Password Input').'",
                "Date Picker": "'.$this->l('Date Picker').'",
                "Color Picker": "'.$this->l('Color Picker').'",
                "File Upload": "'.$this->l('File Upload').'",
                "Textarea": "'.$this->l('Textarea').'",
                "Select": "'.$this->l('Select').'",
                "Radio Group": "'.$this->l('Radio Group').'",
                "Checkbox Group": "'.$this->l('Checkbox Group').'",
                "Unknown Input": "'.$this->l('Unknown Input').'",
                "Time": "'.$this->l('Time').'"
            }
            var PS_ALLOW_ACCENTED_CHARS_URL = '.(int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL').';
            </script>';
        }

        if ($this->context->controller instanceof AdminAdvancedCMSSettingsController) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/adcms.js');
        }
    }

    public function hookDisplayHeader($params)
    {
        unset($params);
        if ($this->context->controller instanceof ProductController) {
            $this->current_product_form = FormMakerForm::getProductForm(
                Tools::getValue('id_product'),
                (int)$this->context->shop->id
            );
        }

        $this->context->controller->addJqueryUI('ui.datepicker');
        $this->context->controller->addJqueryUI('ui.slider');

        if ($this->vt == 't16') {
            $this->context->controller->addJquery();

            $this->context->controller->addJS(_PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.js');
            $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/jquery.ui.widget.js');
            $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/jquery.iframe-transport.js');
            $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/jquery.fileupload.js');
            $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/formfront.js');
            $this->context->controller->addJS(_MODULE_DIR_.$this->name.'/views/js/spectrum.js');
            $this->context->controller->addCSS(_PS_JS_DIR_.'jquery/plugins/timepicker/jquery-ui-timepicker-addon.css');
            $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/formbuilder.front.css');
            $this->context->controller->addCSS(_MODULE_DIR_.$this->name.'/views/css/spectrum.css');
        } else {
            $this->context->controller->registerJavascript(
                'modules-formmaker-widget',
                'modules/'.$this->name.'/views/js/jquery.ui.widget.js',
                array('position' => 'bottom', 'priority' => 150)
            );
            $this->context->controller->registerJavascript(
                'modules-formmaker-iframe',
                'modules/'.$this->name.'/views/js/jquery.iframe-transport.js',
                array('position' => 'bottom', 'priority' => 150)
            );
            $this->context->controller->registerJavascript(
                'modules-formmaker-fileupload',
                'modules/'.$this->name.'/views/js/jquery.fileupload.js',
                array('position' => 'bottom', 'priority' => 150)
            );
            $this->context->controller->registerJavascript(
                'modules-formmaker-front',
                'modules/'.$this->name.'/views/js/t17/formfront.js',
                array('position' => 'bottom', 'priority' => 150)
            );
            $this->context->controller->registerJavascript(
                'modules-formmaker-spectrum',
                'modules/'.$this->name.'/views/js/spectrum.js',
                array('position' => 'bottom', 'priority' => 150)
            );
            $this->context->controller->registerJavascript(
                'jquery-ui-datetimepicker',
                '/js/jquery/plugins/timepicker/jquery-ui-timepicker-addon.js',
                array('position' => 'bottom', 'priority' => 150)
            );

            $this->context->controller->registerStylesheet(
                'jquery-ui-datetimepicker',
                '/js/jquery/plugins/timepicker/jquery-ui-timepicker-addon.css',
                array('media' => 'all', 'priority' => 150)
            );
            $this->context->controller->registerStylesheet(
                'modules-formmaker-front',
                'modules/'.$this->name.'/views/css/t17/formbuilder.front.css',
                array('media' => 'all', 'priority' => 150)
            );
            $this->context->controller->registerStylesheet(
                'modules-formmaker-spectrum',
                'modules/'.$this->name.'/views/css/spectrum.css',
                array('media' => 'all', 'priority' => 150)
            );
        }

        $this->context->smarty->registerFilter('output', array($this, 'parseFormmakerTags'));

        $js = '<script type="text/javascript">
            var formmakerPath = "'.$this->context->link->getModuleLink($this->name, 'form', array('id_form' => 0, 'rewrite' => 'upload')).'";
            var titleFileUploadFM = "'.$this->l('No file selected').'";';

        if ($this->vt == 't16') {
            $js .= 'var buttonFileUploadFM = "'.$this->l('Choose file').'";
            if (typeof($.uniform) != "undefined") {
                $.uniform.defaults.fileDefaultHtml = titleFileUploadFM;
                $.uniform.defaults.fileButtonHtml = buttonFileUploadFM;
            }';
        }

        $js .= '</script>';

        return $js;
    }
    
    public function parseFormmakerTags($tpl_output, Smarty_Internal_Template $template)
    {
        preg_match_all('/\<p\>\[\s?displayForm\sid\s?=\s?([0-9]*)\s?\]\<\/p\>/', $tpl_output, $m, PREG_SET_ORDER);
        
        if (count($m)) {
            foreach ($m as $form_call) {
                $id_form = (int)$form_call[1];
                
                $form_tpl = self::displayForm(array('id' => $id_form));
                $tpl_output = str_replace($form_call[0], $form_tpl, $tpl_output);
            }
        }
        
        return $tpl_output;
    }

    public function hookDisplayReassurance($params)
    {
    }

    public function hookDisplayFooterProduct($params)
    {
        // if ($this->current_product_form) {
        //     $this->context->smarty->assign(array(
        //     'form'         => $this->current_product_form,
        //     'form_product' => (gettype($params['product']) == 'array' ? $params['product']['id'] : $params['product']->id),
        //     'form_data'    => $this->current_product_form->getFormData($this->context->language->id),
        //     'captcha_path' => $this->current_product_form->captcha ? $this->getPathUri().'captcha.php' : false,
        //     'form_path'    => dirname(__FILE__).'/views/templates/front/'.(version_compare('1.7.0.0', _PS_VERSION_, '>') ? '' : 't17/').'form.tpl'
        //     ));

        //     if (version_compare('1.7.0.0', _PS_VERSION_, '>')) {
        //         $this->setTemplate('form_wrapper.tpl');
        //     } else {
        //         return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/front/'.(version_compare('1.7.0.0', _PS_VERSION_, '>') ? '' : 't17/').'form_product.tpl');
        //     }
        // }
        if (version_compare('1.7.0.0', _PS_VERSION_, '<=') && $this->current_product_form) {
            return self::displayForm(array('id' => $this->current_product_form->id));
        }
    }

    public function hookDisplayProductTab($params)
    {
        unset($params);
        // if ($this->current_product_form)
        // {
            // Tabs do not work as they should, so the tab heading is moved to product_tab_content.tpl
            /*$this->context->smarty->assign('form', $this->current_product_form);

            return $this->display(__FILE__, 'product_tab.tpl');*/
        // }
    }

    public function hookDisplayProductTabContent($params)
    {
        if ($this->current_product_form) {
            $this->context->smarty->assign(array(
            'form'         => $this->current_product_form,
            'form_product' => (gettype($params['product']) == 'array' ? $params['product']['id'] : $params['product']->id),
            'form_data'    => $this->current_product_form->getFormData($this->context->language->id),
            'captcha_path' => $this->current_product_form->captcha ? $this->getPathUri().'captcha.php' : false,
            'form_path'    => dirname(__FILE__).'/views/templates/front/'.(version_compare('1.7.0.0', _PS_VERSION_, '>') ? '' : 't17/').'form.tpl'
            ));

            return $this->display(__FILE__, 'product_tab_content.tpl');
        }
    }

    public function checkFormExists($id_form)
    {
        if (!Validate::isUnsignedId($id_form) || !Validate::isLoadedObject(
            $form = new FormMakerForm((int)$id_form, $this->context->language->id)
        )) {
            return false;
        }

        return $form->active ? $form : false;
    }

    public function checkElementExistsInForm(FormMakerForm $form, $id_element, $id_lang = null)
    {
        return $form->getElementById($id_element, $id_lang);
    }

    public function checkFieldType(FormMakerElement $element, $expected_type = 'textInput')
    {
        return $element->getReferenceByType($element->type) == $expected_type;
    }

    public function checkExtension($file, $allowed_extensions)
    {
        if (!is_array($allowed_extensions)) {
            return true;
        }

        $extension = $this->getExtension($file);

        foreach ($allowed_extensions as $allowed_extension) {
            if ($extension == Tools::strtolower(trim($allowed_extension))) {
                return true;
            }
        }

        return false;
    }

    public function getExtension($file)
    {
        return Tools::strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }

    private static function removeExtension($file)
    {
        $extension = self::getExtension($file);

        if (!$extension) {
            return $file;
        }

        return Tools::substr($file, 0, (Tools::strlen($file) - (Tools::strlen($extension) + 1)));
    }

    public function generateNewFileName($file)
    {
        return implode('.', array(uniqid(), $this->getExtension($file)));
    }

    public function uploadFile($id_form, $id_element, $input_name, $current_file)
    {
        $return = array(
            'status' => 'success',
            'message' => 'false',
            'filename' => 0,
            'id_form' => $id_form,
            'id_element' => $id_element,
            'input_name' => $input_name);

        $form = $this->checkFormExists($id_form);
        $element = false;

        if ($form) {
            $element = $this->checkElementExistsInForm($form, $id_element);
        }

        if (!$form) {
            $return['status'] = 'error';
            $return['message'] = $this->l('Unable to find the form you\'re trying to submit');
        } elseif (!$element || !$this->checkFieldType($element, 'fileInput')) {
            $return['status'] = 'error';
            $return['message'] = $this->l('An error occurred, please reload the page');
        } else {
            $file = $_FILES[$input_name];
            $settings = $element->getSettings();
            $extensions = ($settings && array_key_exists('extensions', $settings)
                && !Tools::isEmpty($settings['extensions'])) ? explode(',', $settings['extensions']) : false;
            $filename = $this->generateNewFileName($file['name']);

            if (!$this->checkExtension($file['name'], $extensions)) {
                $return['status'] = 'error';
                $return['message'] = $this->l('Invalid file extension');
            } elseif (!move_uploaded_file($file['tmp_name'], self::$upload_path.$filename)) {
                $return['status'] = 'error';
                $return['message'] = $this->l('Unable to upload file').' "'.$file['name'].'"';
            } else {
                if ($current_file) {
                    self::deleteFilesByName(self::removeExtension($current_file));
                }

                $return['filename'] = $filename;
            }
        }

        return Tools::jsonEncode($return);
    }

    private static function deleteFilesByName($name)
    {
        $name = self::$upload_path.$name;
        $info = pathinfo($name);

        if (!empty($info['extension'])) {
            @unlink($name);

            return true;
        }

        $filename = $info['filename'];
        $len      = Tools::strlen($filename);
        $dh       = opendir($info['dirname']);

        if (!$dh) {
            return false;
        }

        while (($file = readdir($dh)) !== false) {
            if (strncmp($file, $filename, $len) === 0) {
                if (Tools::strlen($name) > $len) {
                    $name = Tools::substr($name, 0, Tools::strlen($name) - $len).$file;
                } else {
                    $name = $file;
                }

                closedir($dh);
                @unlink($name);

                return true;
            }
        }

        closedir($dh);

        return false;
    }

    private function deleteFormFiles($form_data)
    {
        $id_form = $form_data['id_form'];
        $elements = $form_data['elements'];

        if (!count($elements) || !$form = $this->checkFormExists($id_form)) {
            return true;
        }

        foreach ($elements as $element) {
            $element_obj = $this->checkElementExistsInForm(
                $form,
                $element['id_element'],
                $this->context->language->id
            );

            if ($element_obj
                && $element_obj->getReferenceByType($element_obj->type) == 'fileInput'
                && $element['value']
                && Validate::isFileName($element['value'])
                && file_exists(self::$upload_path.$element['value'])) {
                    unlink(self::$upload_path.$element['value']);
            }
        }

        return true;
    }

    public function submitForm($form_data, $page_data)
    {
        $id_form = $form_data['id_form'];
        $id_product = $form_data['id_product'];
        $elements = $form_data['elements'];
        $captcha = isset($form_data['captcha']) ? $form_data['captcha'] : false;
        $form = $this->checkFormExists($id_form);
        $product = new Product((int)$id_product, false, $this->context->language->id);
        $product = Validate::isLoadedObject($product) ? $product : false;
        
        $send_form = array();
        $errors = array();

        if (!count($elements)) {
            array_push($errors, $this->l('The form is empty'));
        } elseif (!$form) {
            array_push($errors, $this->l('This form doesn\'t exist'));
        } else {
            if ($form->captcha
                && (!$this->context->cookie->__isset('captchaText_'.$id_form)
                    || $this->context->cookie->__get('captchaText_'.$id_form) != $captcha)) {
                array_push($errors, $this->l('You have entered an invalid captcha. Please try again.'));
            }
            
            if ($form->captcha) {
                $this->context->cookie->__unset('captchaText_'.$id_form);
            }

            foreach ($elements as $element) {
                $element_obj = $this->checkElementExistsInForm(
                    $form,
                    $element['id_element'],
                    $this->context->language->id
                );

                if (!$element_obj) {
                    array_push($errors, $this->l('Element doesn\'t exist'));
                } else {
                    $type = $element_obj->getReferenceByType($element_obj->type);

                    $send_form[$element_obj->id] = array(
                        'field' => $element_obj->name,
                        'type' => $type,
                        'value' => false,
                        'css' => false
                    );

                    switch ($type) {
                        case 'checkboxInput':
                        case 'radioInput':
                        case 'selectInput':
                            if (!is_array($element['value']) && Validate::isUnsignedId($element['value'])) {
                                $element['value'] = array($element['value']);
                            }

                            if ($element_obj->required
                                && (!is_array($element['value']) || !count($element['value']))) {
                                array_push(
                                    $errors,
                                    sprintf($this->l('Please select a value for "%s" field'), $element_obj->name)
                                );
                            } else {
                                foreach ((array)$element['value'] as $value_id) {
                                    if ($value_id != '') {
                                        if (!$value_obj = $element_obj->getValueById(
                                            $value_id,
                                            $this->context->language->id
                                        )) {
                                            array_push(
                                                $errors,
                                                sprintf(
                                                    $this->l('Element "%s" doesn\'t have a value with an ID of "%d"'),
                                                    $element_obj->name,
                                                    (int)$value_id
                                                )
                                            );
                                        } else {
                                            if (!is_array($send_form[$element_obj->id]['value'])) {
                                                $send_form[$element_obj->id]['value'] = array();
                                            }

                                            array_push(
                                                $send_form[$element_obj->id]['value'],
                                                Tools::safeOutput($value_obj->name)
                                            );
                                        }
                                    }
                                }
                            }
                            break;
                        case 'fileInput':
                            if ($element_obj->required && Tools::isEmpty($element['value'])) {
                                array_push(
                                    $errors,
                                    sprintf($this->l('Please upload the file in "%s" field'), $element_obj->name)
                                );
                            } elseif (!Tools::isEmpty($element['value'])
                                && !Validate::isFileName($element['value'])) {
                                array_push(
                                    $errors,
                                    sprintf(
                                        $this->l('"%s" is not a valid filename in "%s" field'),
                                        $element['value'],
                                        $element_obj->name
                                    )
                                );
                            } elseif ($element_obj->required
                                && !file_exists(self::$upload_path.$element['value'])) {
                                array_push(
                                    $errors,
                                    sprintf($this->l('File has not been uploaded in "%s" field'), $element_obj->name)
                                );
                            } elseif (!Tools::isEmpty($element['value'])
                                && file_exists(self::$upload_path.$element['value'])) {
                                $send_form[$element_obj->id]['value'] = Tools::getHttpHost(true, true)
                                    ._MODULE_DIR_.$this->name.'/uploads/'.$element['value'];
                            }
                            break;
                        default:
                            if ($element_obj->required && Tools::isEmpty($element['value'])) {
                                array_push(
                                    $errors,
                                    sprintf($this->l('Please select a value in "%s" field'), $element_obj->name)
                                );
                            } else {
                                $send_form[$element_obj->id]['value'] = Tools::safeOutput($element['value']);
                                $send_form[$element_obj->id]['css'] = $element_obj->css_class;
                            }
                            break;
                    }
                }
                
                if (isset($send_form[$element_obj->id]['value'])) {
                    $settings = $element_obj->getSettings();
                    
                    if (is_array($settings)
                        && isset($settings['validation'])
                        && $settings['validation']
                        && method_exists('Validate', $settings['validation'])) {
                        if (!call_user_func(
                            array('Validate', $settings['validation']),
                            $element['value'],
                            (int)Configuration::get('PS_ALLOW_HTML_IFRAME')
                        )) {
                            array_push(
                                $errors,
                                sprintf(
                                    '"%s": '.$this->validation_methods[$settings['validation']]['error_text'],
                                    $element_obj->name
                                )
                            );
                        }
                    }
                }
            }
        }

        if ($form && !self::checkFormToPreventSpam($id_form, (int)$form->submit_delay)) {
            $this->deleteFormFiles($form_data);

            array_push(
                $errors,
                $this->l('You have already submitted this form recently. Please wait before submitting it again.')
            );
        }

        $is_processed_elsewhere = false;
        $redirect = false;

        Hook::exec('onFormSubmit', array(
            'form'                => $form,
            'product'             => $product,
            'redirect'            => &$redirect,
            'rawInfo'             => $form_data,
            'validatedInfo'       => &$send_form,
            'formErrors'          => &$errors,
            'processedSeparately' => &$is_processed_elsewhere,
        ));

        if (!count($errors)) {
            $k = 'formmaker_'.(int)$id_form.'_time';

            $this->context->cookie->{$k} = time();
        }

        if (count($errors)) {
            return Tools::jsonEncode(array('errors' => $errors));
        } else {
        	$report = FormMakerReport::setReport($form, $send_form, $product);

        	if ($is_processed_elsewhere || $this->sendEmail($form, $send_form, $page_data, $product, $report)) {
	            $success_redirect = false;
	            Db::getInstance()->Execute(
                    'UPDATE `'._DB_PREFIX_.'fm_form_report`
                    SET `send` = 1
                    WHERE `id_fm_form_report` = '.(int)$report
                );
	            if ($form->redirect_on_success) {
	                $success_redirect = $this->context->link->getModuleLink(
	                    $this->name,
	                    'formsuccess',
	                    array('id_form' => $form->id)
	                );
	            }
	            
	            return Tools::jsonEncode(
	                array(
	                    'success' => $this->l('The form has been successfully submitted'),
	                    'redirect' => $redirect, 'success_redirect' => $success_redirect
	                )
	            );
	        } else {
	            return Tools::jsonEncode(array('errors' => array($this->l('Unable to submit the form'))));
	        }
        } 
    }

    public function checkFormToPreventSpam($id_form, $delay)
    {
        if (!$delay) {
            return true;
        }

        $key = 'formmaker_'.(int)$id_form;

        $family = $this->context->cookie->getFamily($key);

        if (!$family) {
            return true;
        }

        $submit_time = $family[$key.'_time'];
        $current_time = time();

        return ($current_time - $submit_time >= $delay);
    }

    public function sendEmail($form, $form_data, $page_data, $product = false, $report = false)
    {
        $receiver_list = explode(',', $form->receivers);

        $this->context->smarty->assign(array(
            'product'   => $product,
            'form_data' => $form_data,
            'color'     => Tools::safeOutput(Configuration::get('PS_MAIL_COLOR'))
        ));

        $form_template = $this->display(__FILE__, 'form_template.tpl');
        $customer = Validate::isLoadedObject($this->context->customer) ? $this->context->customer : false;

        $extra_vars = array(
            '{customer_name}' => $customer ? $customer->firstname.' '.$customer->lastname : $this->l('Guest'),
            '{form_name}' => $form->name,
            '{form_data}' => $form_template,
            '{id_report}' => ($report == false ? '' : $report)
        );

        $lang_for_mail = $this->context->language->id;
        $check_lang = $this->collectMissingEmailTemplates(Language::getLanguage($this->context->language->id));

        if ($check_lang) {
            if (Language::getIdByIso('en')) {
                $lang_for_mail = Language::getIdByIso('en');
            }
        }

        $usermail = array();
        foreach ($form_data as $fd) {
            if (strripos($fd['css'], 'user-mail') !== false && Validate::isEmail($fd['value'])) {
                $usermail[] = $fd['value'];
            }
        }
        $usermails = implode(',', $usermail);

        // $extra_vars['{origin}'] = 'http'.(isset($_SERVER['HTTPS']) ? 's' : '')
            // .'://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'];
        $extra_vars['{origin}'] = $page_data['page'];
        $extra_vars['{page_param}'] = self::pageParams($page_data);
        $extra_vars['{lang_name}'] = $this->context->language->name;
        
        if ($form->send_autoresponse && ($customer || $usermails != '')) {
            Mail::Send(
                $lang_for_mail,
                'autoresponse',
                $this->l('Thank you for submitting the form!'),
                $extra_vars,
                $customer ? $customer->email : $usermails,
                null,
                null,
                null,
                null,
                null,
                dirname(__FILE__).'/mails/'
            );
        }

        return Mail::Send(
            $lang_for_mail,
            'form_email',
            sprintf($this->l('Form %1$s submitted'), $form->name),
            $extra_vars,
            $receiver_list,
            null,
            $customer ? $customer->email : $usermails,
            null,
            null,
            null,
            dirname(__FILE__).'/mails/'
        );
    }

    public static function pageParams($page)
    {
        $text = '';

        if (isset($page['controller']) && Validate::isControllerName($page['controller'])) {
            $text = $page['controller'];

            if (!array_key_exists('id_lang', $page)) {
                $lang = (int)Configuration::get('PS_LANG_DEFAULT');
            } else {
                $lang = (int)$page['id_lang'];
            }

            switch ($page['controller']) {
                case 'cms':
                    $cms = new CMS((int)$page['id_cms'], $lang);

                    $text .= ' - ' . $cms->meta_title .' (' . (int)$page['id_cms'] . ')';
                    break;

                case 'category':
                    $category = new Category((int)$page['id_category']);

                    $text .= ' - ' . $category->getName($lang) .' (' . (int)$page['id_category'] . ')';
                    break;

                case 'product':
                    $product = new Product((int)$page['id_product'], false, $lang);

                    $text .= ' - ' . $product->name .' (' . (int)$page['id_product'] . ')';
                    break;

                case 'form':
                    $text .= ' (' . (int)$page['id_form'] . ')';
                    break;
                
                default:
                    break;
            }
        }

        return $text;
    }
    
    public static function displayForm($params)
    {
        if (isset($params['id'])) {
            $context = Context::getContext();

            $form = new FormMakerForm((int)$params['id'], $context->language->id);

            if (Validate::isLoadedObject($form)
                && $form->active
                && $form->checkCustomerAccess(Context::getContext()->customer)) {
                $form_data = $form->getFormData($context->language->id);

                if ($form_data) {
                    $context->smarty->assign(array(
                        'form'      => $form,
                        'form_data' => $form_data,
                        'captcha_path' => $form->captcha ? __PS_BASE_URI__.'modules/formmaker/captcha.php' : false
                    ));

                    return $context->smarty->fetch(dirname(__FILE__).'/views/templates/front/'.(version_compare('1.7.0.0', _PS_VERSION_, '>') ? '' : 't17/').'form.tpl');
                }
            }
        }
    }

    public function hookDisplayAdminAdCmsMenu($params)
    {
        unset($params);
        return $this->display(__FILE__, 'adcms_menu.tpl');
    }

    public function hookDisplayAdminAdCmsBlockContents($params)
    {
        if ($params['type'] == 'form') {
            $form_list = FormMakerForm::getFormsList();

            $this->context->smarty->assign(array(
                'form_list' => $form_list
            ));

            return $this->display(__FILE__, 'adcms_block.tpl');
        }
    }

    public function hookActionBlockDataPrefilter($params)
    {
        if ($params['type'] == 'form') {
            $form_id = $params['contents'];

            if (Validate::isUnsignedId($form_id)) {
                $context = Context::getContext();

                if (Validate::isLoadedObject($form = new FormMakerForm((int)$form_id, $context->language->id))
                    && $form->active && $form_data = $form->getFormData($context->language->id)) {
                    $context->smarty->assign(array(
                        'form'      => $form,
                        'form_data' => $form_data,
                        'captcha_path' => $form->captcha ? __PS_BASE_URI__.'modules/formmaker/captcha.php' : false
                    ));

                    $params['contents'] = $context->smarty->fetch(dirname(__FILE__).'/views/templates/front/'.(version_compare('1.7.0.0', _PS_VERSION_, '>') ? '' : 't17/').'form.tpl');
                } else {
                    $params['contents'] = null;
                }
            }
        }
    }
    
    private function registerFormReport($form_data)
    {
        
    }
}
