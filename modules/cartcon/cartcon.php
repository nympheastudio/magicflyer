<?php
/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-2020 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */
require_once _PS_MODULE_DIR_ . 'cartcon/models/CartCondition.php';
require_once _PS_MODULE_DIR_ . 'cartcon/models/CartValueCondition.php';
require_once _PS_MODULE_DIR_ . 'cartcon/models/CartQuantityCondition.php';
require_once _PS_MODULE_DIR_ . 'cartcon/models/CartAssociationsCondition.php';
require_once _PS_MODULE_DIR_ . 'cartcon/models/CartCountryCondition.php';

class cartcon extends Module
{

    function __construct()
    {
        $this->name = 'cartcon';
        $this->tab = 'advertising_marketing';
        $this->author = 'prestashop';
        $this->version = '2.0.1';
        $this->module_key = '1abb6178d31e1832f0a64e1b4e3bc62a';
        $this->mypresta_link = '#';
        parent::__construct();
        $this->bootstrap = true;
        $this->dir = '/modules/cartcon/';
        $this->displayName = $this->l('Cart Conditions');
        $this->description = $this->l('Create cart conditions required to continue order process');
        $this->addproduct = $this->l('Add');
        $this->noproductsfound = $this->l('No products found');
        $this->checkforupdates();
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $this->context->controller->addJqueryPlugin('typeWatch');
        $this->context->controller->addJqueryPlugin('highlight');
        //for update purposes
    }

    public function checkforupdates($display_msg = 0, $form = 0)
    {
        // ---------- //
        // ---------- //
        // VERSION 16 //
        // ---------- //
        // ---------- //
        $this->mkey = "nlc";
        if (@file_exists('../modules/' . $this->name . '/key.php')) {
            @require_once('../modules/' . $this->name . '/key.php');
        } else {
            if (@file_exists(dirname(__FILE__) . $this->name . '/key.php')) {
                @require_once(dirname(__FILE__) . $this->name . '/key.php');
            } else {
                if (@file_exists('modules/' . $this->name . '/key.php')) {
                    @require_once('modules/' . $this->name . '/key.php');
                }
            }
        }
        if ($form == 1) {
            return '
            <div class="panel" id="fieldset_myprestaupdates" style="margin-top:20px;">
            ' . ($this->psversion() == 6 || $this->psversion() == 7 ? '<div class="panel-heading"><i class="icon-wrench"></i> ' . $this->l('MyPresta updates') . '</div>' : '') . '
			<div class="form-wrapper" style="padding:0px!important;">
            <div id="module_block_settings">
                    <fieldset id="fieldset_module_block_settings">
                         ' . ($this->psversion() == 5 ? '<legend style="">' . $this->l('MyPresta updates') . '</legend>' : '') . '
                        <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                            <label>' . $this->l('Check updates') . '</label>
                            <div class="margin-form">' . (Tools::isSubmit('submit_settings_updates_now') ? ($this->inconsistency(0) ? '' : '') . $this->checkforupdates(1) : '') . '
                                <button style="margin: 0px; top: -3px; position: relative;" type="submit" name="submit_settings_updates_now" class="button btn btn-default" />
                                <i class="process-icon-update"></i>
                                ' . $this->l('Check now') . '
                                </button>
                            </div>
                            <label>' . $this->l('Updates notifications') . '</label>
                            <div class="margin-form">
                                <select name="mypresta_updates">
                                    <option value="-">' . $this->l('-- select --') . '</option>
                                    <option value="1" ' . ((int)(Configuration::get('mypresta_updates') == 1) ? 'selected="selected"' : '') . '>' . $this->l('Enable') . '</option>
                                    <option value="0" ' . ((int)(Configuration::get('mypresta_updates') == 0) ? 'selected="selected"' : '') . '>' . $this->l('Disable') . '</option>
                                </select>
                                <p class="clear">' . $this->l('Turn this option on if you want to check MyPresta.eu for module updates automatically. This option will display notification about new versions of this addon.') . '</p>
                            </div>
                            <label>' . $this->l('Module page') . '</label>
                            <div class="margin-form">
                                <a style="font-size:14px;" href="' . $this->mypresta_link . '" target="_blank">' . $this->displayName . '</a>
                                <p class="clear">' . $this->l('This is direct link to official addon page, where you can read about changes in the module (changelog)') . '</p>
                            </div>
                            <div class="panel-footer">
                                <button type="submit" name="submit_settings_updates"class="button btn btn-default pull-right" />
                                <i class="process-icon-save"></i>
                                ' . $this->l('Save') . '
                                </button>
                            </div>
                        </form>
                    </fieldset>
                    <style>
                    #fieldset_myprestaupdates {
                        display:block;clear:both;
                        float:inherit!important;
                    }
                    </style>
                </div>
            </div>
            </div>';
        } else {
            if (defined('_PS_ADMIN_DIR_')) {
                if (Tools::isSubmit('submit_settings_updates')) {
                    Configuration::updateValue('mypresta_updates', Tools::getValue('mypresta_updates'));
                }
                if (Configuration::get('mypresta_updates') != 0 || (bool)Configuration::get('mypresta_updates') != false) {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = cartconUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                    if (cartconUpdate::version($this->version) < cartconUpdate::version(Configuration::get('updatev_' . $this->name)) && Tools::getValue('ajax','false') == 'false') {
                        $this->context->controller->warnings[] = '<strong>' . $this->displayName . '</strong>: ' . $this->l('New version available, check http://MyPresta.eu for more informations') . ' <a href="' . $this->mypresta_link . '">' . $this->l('More details in changelog') . '</a>';
                        $this->warning                         = $this->context->controller->warnings[0];
                    }
                } else {
                    if (Configuration::get('update_' . $this->name) < (date("U") - 259200)) {
                        $actual_version = cartconUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version);
                    }
                }
                if ($display_msg == 1) {
                    if (cartconUpdate::version($this->version) < cartconUpdate::version(cartconUpdate::verify($this->name, (isset($this->mkey) ? $this->mkey : 'nokey'), $this->version))) {
                        return "<span style='color:red; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('New version available!') . "</span>";
                    } else {
                        return "<span style='color:green; font-weight:bold; font-size:16px; margin-right:10px;'>" . $this->l('Module is up to date!') . "</span>";
                    }
                }
            }
        }
    }


    private function maybeUpdateDatabase($table, $column, $type = "int(8)", $default = "1", $null = "NULL")
    {
        $sql = 'DESCRIBE ' . _DB_PREFIX_ . $table;
        $columns = Db::getInstance()->executeS($sql);
        $found = false;
        foreach ($columns as $col)
        {
            if ($col['Field'] == $column)
            {
                $found = true;
                break;
            }
        }
        if (!$found)
        {
            if (!Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . $table . '` ADD `' . $column . '` ' . $type . ' DEFAULT ' . $default . ' ' . $null))
            {
                return false;
            }
        }
        return true;
    }

    public function inconsistency($ret)
    {
        $this->maybeUpdateDatabase('cartcon_ass', 'id_shop', "int(1)", 1, "NOT NULL");
        $this->maybeUpdateDatabase('cartcon', 'id_shop', "int(1)", 1, "NOT NULL");
        $this->maybeUpdateDatabase('cartcon_quantity', 'id_shop', "int(1)", 1, "NOT NULL");
        $this->maybeUpdateDatabase('cartcon_quantity', 'multiply', "int(1)", 0, "NOT NULL");
        $this->maybeUpdateDatabase('cartcon_value', 'id_shop', "int(1)", 1, "NOT NULL");
        $this->maybeUpdateDatabase('cartcon_country', 'id_shop', "int(1)", 1, "NOT NULL");
        $this->maybeUpdateDatabase('cartcon_country', 'c_tax', "int(1)", 1, "NOT NULL");
        $this->maybeUpdateDatabase('cartcon_country', 'c_cartValueType', "int(1)", 1, "NOT NULL");
        $this->maybeUpdateDatabase('cartcon_ass', 'subcatt', "int(1)", 0, "NOT NULL");
        $this->maybeUpdateDatabase('cartcon_ass', 'subcata', "int(1)", 0, "NOT NULL");

        return;
    }

    public static function psversion($part = 1)
    {
        $version = _PS_VERSION_;
        $exp = $explode = explode(".", $version);
        if ($part == 1)
        {
            return $exp[1];
        }
        if ($part == 2)
        {
            return $exp[2];
        }
        if ($part == 3)
        {
            return $exp[3];
        }
    }

    function install()
    {
        if ($this->psversion() == 5 || $this->psversion() == 6)
        {
            if (!parent::install() or
                !$this->registerHook('displayBackOfficeHeader') OR
                !$this->registerHook('ActionAdminControllerSetMedia') OR
                !$this->registerHook('ProductActions') OR
                !$this->registerHook('header') OR
                !$this->installdb() or
                !$this->createMenu())
            {
                return false;
            }
        }
        return true;
    }

    public function createMenu()
    {
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
        {
            $tab->name[$lang['id_lang']] = 'Cart Conditions';
        }
        $tab->class_name = 'AdminCartCondition';
        $tab->id_parent = Tab::getIdFromClassName('AdminPriceRule');
        $tab->module = $this->name;
        $tab->add();

        foreach (Language::getLanguages(true) as $lang)
        {
            $tab->name[$lang['id_lang']] = 'Cart Value Conditions';
        }
        $tab->class_name = 'AdminCartValueCondition';
        $tab->id_parent = Tab::getIdFromClassName('AdminPriceRule');
        $tab->module = $this->name;
        $tab->add();

        foreach (Language::getLanguages(true) as $lang)
        {
            $tab->name[$lang['id_lang']] = 'Cart Quantity Conditions';
        }
        $tab->class_name = 'AdminCartQuantityCondition';
        $tab->id_parent = Tab::getIdFromClassName('AdminPriceRule');
        $tab->module = $this->name;
        $tab->add();

        foreach (Language::getLanguages(true) as $lang)
        {
            $tab->name[$lang['id_lang']] = 'Cart Associations Conditions';
        }
        $tab->class_name = 'AdminCartAssociationsCondition';
        $tab->id_parent = Tab::getIdFromClassName('AdminPriceRule');
        $tab->module = $this->name;
        $tab->add();

        foreach (Language::getLanguages(true) as $lang)
        {
            $tab->name[$lang['id_lang']] = 'Cart value by country conditions';
        }
        $tab->class_name = 'AdminCartCountryCondition';
        $tab->id_parent = Tab::getIdFromClassName('AdminPriceRule');
        $tab->module = $this->name;
        $tab->add();
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall())
        {
            return false;
        }
        // Tabs
        $idTabs = array();
        $idTabs[] = Tab::getIdFromClassName('AdminCartCondition');
        $idTabs[] = Tab::getIdFromClassName('AdminCartValueCondition');
        $idTabs[] = Tab::getIdFromClassName('AdminCartQuantityCondition');
        $idTabs[] = Tab::getIdFromClassName('AdminCartAssociationsCondition');
        $idTabs[] = Tab::getIdFromClassName('AdminCartCountryCondition');
        foreach ($idTabs as $idTab)
        {
            if ($idTab)
            {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }
        return true;
    }

    private function installdb()
    {
        $prefix = _DB_PREFIX_;
        $engine = _MYSQL_ENGINE_;
        $statements = array();
        $statements[] = "CREATE TABLE IF NOT EXISTS `${prefix}cartcon` (" . '`id_cartcon` int(10) NOT NULL AUTO_INCREMENT,' . '`c_type` VARCHAR(200) NOT NULL, ' . '`c_value` INT(10) NOT NULL, ' . '`c_option` INT(10),' . '`active` int(1) NOT NULL DEFAULT 1,' . 'PRIMARY KEY (`id_cartcon`)' . ")";
        $statements[] = "CREATE TABLE IF NOT EXISTS `${prefix}cartcon_value` (" . '`id_cartcon_value` int(10) NOT NULL AUTO_INCREMENT,' . '`c_type` VARCHAR(200) NOT NULL, ' . '`c_target` INT(10) NOT NULL, ' . '`c_value` INT(10),' . '`active` int(1) NOT NULL DEFAULT 1, `c_only_if_in_cart` int(1) NOT NULL DEFAULT 0, `c_group` int(1) NOT NULL DEFAULT 0, ' . 'PRIMARY KEY (`id_cartcon_value`)' . ")";
        $statements[] = "CREATE TABLE IF NOT EXISTS `${prefix}cartcon_ass` (" . '`id_cartcon_ass` int(10) NOT NULL AUTO_INCREMENT,' . '`c_type` VARCHAR(200) NOT NULL, ' . '`c_target1` INT(10) NOT NULL, ' . '`c_target2` INT(10) NOT NULL, ' . '`c_value` INT(10),' . '`active` int(1) NOT NULL DEFAULT 1,  `c_group` int(1) NOT NULL DEFAULT 0, ' . 'PRIMARY KEY (`id_cartcon_ass`)' . ")";
        $statements[] = "CREATE TABLE IF NOT EXISTS `${prefix}cartcon_quantity` (" . '`id_cartcon_quantity` int(10) NOT NULL AUTO_INCREMENT,' . '`c_type` VARCHAR(200) NOT NULL, ' . '`c_target` INT(10) NOT NULL, ' . '`c_value` INT(10),' . '`active` int(1) NOT NULL DEFAULT 1, `c_only_if_in_cart` int(1) NOT NULL DEFAULT 0, `c_group` int(1) NOT NULL DEFAULT 0, ' . 'PRIMARY KEY (`id_cartcon_quantity`)' . ")";
        $statements[] = "CREATE TABLE IF NOT EXISTS `${prefix}cartcon_country` (" . '`id_cartcon_country` int(10) NOT NULL AUTO_INCREMENT,' . '`c_type` VARCHAR(200) NOT NULL, ' . '`c_target` INT(10) NOT NULL, ' . '`c_value` INT(10),' . '`active` int(1) NOT NULL DEFAULT 1, `c_group` int(1) NOT NULL DEFAULT 0, ' . 'PRIMARY KEY (`id_cartcon_country`)' . ")";

        foreach ($statements as $statement)
        {
            if (!Db::getInstance()->Execute($statement))
            {
                return false;
            }
        }
        $this->inconsistency(0);
        return true;
    }

    public function getCustomerGroups()
    {
        $customer_groups = array();
        if (isset($this->context->cart->id_customer))
        {
            if ($this->context->cart->id_customer == 0)
            {
                $customer_groups[1] = 1;
            }
            else
            {
                foreach (Customer::getGroupsStatic($this->context->cart->id_customer) as $group)
                {
                    $customer_groups[$group] = 1;
                }
            }
        }
        elseif ($this->context->customer->is_guest == 1)
        {
            $customer_groups[1] = 2;
        }
        else
        {
            $customer_groups[1] = 1;
        }
        if (count($customer_groups) > 0)
        {
            return $customer_groups;
        }
        else
        {
            return false;
        }
    }

    public function displayGlobalForm()
    {
        if (Tools::isSubmit('btnFutureSubmit')) {
            Configuration::updateValue('CARTCON_FUTURE', Tools::getValue('CARTCON_FUTURE'));
            $this->context->controller->confirmations[] = $this->l('Saved');
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Global conditions'),
                    'icon' => 'icon-wrench'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Block order of products with Various availabiltiy date'),
                        'desc' => $this->l('In PrestaShop you can allow to place an order for items that will be available in the future. With this option you can decide if you want to block purchase of products with various availability date'),
                        'name' => 'CARTCON_FUTURE',
                        'required' => true,
                        'lang' => false,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('On')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Off')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = 'cartcon_future';
        $helper->submit_action = 'btnFutureSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getContent()
    {
        $this->context->controller->informations[]= $this->l('Management of this module is located under Price Rules menu. You can manage there cart conditions that module allows to create.').'<br/>'.
        '<a href="'.$this->context->link->getAdminLink('AdminCartCondition').'">'.$this->l('Cart Conditions').'</a><br/>' .
        '<a href="'.$this->context->link->getAdminLink('AdminCartValueCondition').'">'.$this->l('Cart Value Conditions').'</a><br/>' .
        '<a href="'.$this->context->link->getAdminLink('AdminCartQuantityCondition').'">'.$this->l('Cart Quantity Conditions').'</a><br/>' .
        '<a href="'.$this->context->link->getAdminLink('AdminCartAssociationsCondition').'">'.$this->l('Cart Associations Conditions').'</a><br/>';
        '<a href="'.$this->context->link->getAdminLink('AdminCartCountryCondition').'">'.$this->l('Cart value by country conditions').'</a><br/>';
        return $this->displayCustomersForm() . $this->displayGlobalForm() . $this->checkforupdates(0, 1);
    }

    public static function returnConditionsCartQuantity($product)
    {
        $record = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'cartcon_quantity` WHERE active="1"  AND id_shop="' . Context::getContext()->shop->id . '" AND c_type="2" AND c_target=' . $product);
        return $record;
    }

    public function displaySearchFieldCustomer($name)
    {
        $customers = false;
        if (Configuration::get('CARTCON_CUSTOMERS') != false && Configuration::get('CARTCON_CUSTOMERS') != null && Configuration::get('CARTCON_CUSTOMERS') != "") {
            $customers_explode = explode(',', Configuration::get('CARTCON_CUSTOMERS'));

            if (count($customers_explode) > 0) {
                $products = array();
                foreach ($customers_explode AS $customer) {
                    $new = new Customer($customer);
                    $customers[] = $new;
                }
            }
        }

        $this->context->smarty->assign(array(
            'version' => _PS_VERSION_,
            'input_array_name' => $name,
            'id_langg' => $this->context->language->id,
            'linkk' => $this->context->link,
            'customers' => $customers
        ));
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cartcon/views/templates/admin/adminSearchCustomer.tpl');
    }

    public function displayCustomersForm()
    {
        if (Tools::isSubmit('btnSearchCustomersSubmit')) {
            Configuration::updateValue('CARTCON_CUSTOMERS', implode(',', Tools::getValue('CARTCON_CUSTOMERS')));
            $this->context->controller->confirmations[] = $this->l('Saved');
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Exclude customers'),
                    'icon' => 'icon-wrench'
                ),
                'input' => array(
                    array(
                        'type' => 'html',
                        'label' => $this->l('Exclude customers from conditions'),
                        'name' => 'CARTCON_CUSTOMERS',
                        'html_content' => $this->displaySearchFieldCustomer('CARTCON_CUSTOMERS'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = 'cartcon_customers';
        $helper->submit_action = 'btnSearchCustomersSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'CARTCON_CUSTOMERS' => Tools::getValue('CARTCON_CUSTOMERS', Configuration::get('CARTCON_CUSTOMERS')),
            'CARTCON_FUTURE' => Tools::getValue('CARTCON_FUTURE', Configuration::get('CARTCON_FUTURE')),
        );
    }

    public function hookProductActions($params)
    {
        $condition = $this->returnConditionsCartQuantity(Tools::getValue('id_product'));
        $restriction = false;
        foreach ($condition AS $condition)
        {
            $customer_groups = $this->getCustomerGroups();
            foreach ($customer_groups as $ky => $gr)
            {
                if ($condition['c_group'] == $ky)
                {
                    $restriction['quantity'] = $condition['c_value'];
                }
            }
        }

        $this->context->smarty->assign('restriction', $restriction);
        return $this->display(__file__, 'views/templates/front/productActions.tpl');
    }

    public function hookdisplayHeader($params)
    {
        $this->context->controller->addCSS(($this->_path) . 'views/css/cartcon.css', 'all');
    }

    public function hookDisplayBackOfficeHeader()
    {
    }
}

class cartconUpdate extends cartcon
{
    public static function version($version)
    {
        $version = (int)str_replace(".", "", $version);
        if (strlen($version) == 3)
        {
            $version = (int)$version . "0";
        }
        if (strlen($version) == 2)
        {
            $version = (int)$version . "00";
        }
        if (strlen($version) == 1)
        {
            $version = (int)$version . "000";
        }
        if (strlen($version) == 0)
        {
            $version = (int)$version . "0000";
        }
        return (int)$version;
    }

    public static function encrypt($string)
    {
        return base64_encode($string);
    }

    public static function verify($module, $key, $version)
    {
        if (ini_get("allow_url_fopen"))
        {
            if (function_exists("file_get_contents"))
            {
                $actual_version = 'test';//@file_get_contents('http://dev.mypresta.eu/update/get.php?module=' . $module . "&version=" . self::encrypt($version) . "&lic=$key&u=" . self::encrypt(_PS_BASE_URL_ . __PS_BASE_URI__));
            }
        }
        Configuration::updateValue("update_" . $module, date("U"));
        Configuration::updateValue("updatev_" . $module, $actual_version);
        return $actual_version;
    }
}

?>