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

require_once _PS_MODULE_DIR_ . 'cartcon/cartcon.php';

class AdminCartQuantityConditionController extends ModuleAdminController
{
    protected $position_identifier = 'id_cartcon_value';

    public function __construct()
    {
        $this->table = 'cartcon_quantity';
        $this->className = 'CartQuantityCondition';
        $this->lang = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?')
            )
        );
        $this->bootstrap = true;
        $this->_orderBy = 'id_cartcon_quantity';


        $this->fields_list = array(
            'id_cartcon_quantity' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'orderby' => true,
                'width' => 20
            ),

            'c_group' => array(
                'title' => $this->l('Customer group'),
                'width' => 'auto',
                'orderby' => true,
                //'desc' => $this->l('read').'<a href="">'.$this->l('how to get Product ID').'</a>',
                'callback' => 'getCustomerGroup',
                'filter_key' => 'c_group',
            ),

            'c_type' => array(
                'title' => $this->l('Cart condition'),
                'width' => 'auto',
                'orderby' => true,
                //'desc' => $this->l('read').'<a href="">'.$this->l('how to get Product ID').'</a>',
                'callback' => 'getConditionType',
                'filter_key' => 'c_type',
            ),

            'c_target' => array(
                'title' => $this->l('Target'),
                'width' => 'auto',
                'orderby' => true,
                //'desc' => $this->l('read').'<a href="">'.$this->l('how to get Product ID').'</a>',
                'callback' => 'getConditionTarget',
                'filter_key' => 'c_target',
            ),

            'c_value' => array(
                'title' => $this->l('Quantity'),
                'width' => 'auto',
                'orderby' => true,
                //'desc' => $this->l('read').'<a href="">'.$this->l('how to get Product ID').'</a>',
                'filter_key' => 'c_value',
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'width' => 50,
                'orderby' => true,
                'type' => 'bool',
                'active' => 'status',
            ),
        );
        parent::__construct();
    }

    public function renderList()
    {
        $this->initToolbar();
        return parent::renderList();
    }

    public function init()
    {
        $this->_where = 'AND a.id_shop=' . Context::getContext()->shop->id;
        parent::init();
    }

    public function initToolbar()
    {
        unset($this->toolbar_btn);
        $Link = new Link();
        $this->toolbar_btn['new'] = array(
            'desc' => $this->l('Add new'),
            'href' => $Link->getAdminLink('AdminCartQuantityCondition') . '&addcartcon_quantity'
        );
    }

    public function initFormToolBar()
    {
    }

    public function renderForm()
    {
        $this->initFormToolBar();
        if (!$this->loadObject(true))
        {
            return;
        }
        $cover = false;
        $obj = $this->loadObject(true);
        if (isset($obj->id))
        {
            $this->display = 'edit';
        }
        else
        {
            $this->display = 'add';
        }
        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Cart Condition'),
                'image' => '../img/admin/cog.gif'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Customer Group'),
                    'name' => 'c_group',
                    'required' => true,
                    'lang' => false,
                    'options' => array(
                        'query' => Group::getGroups($this->context->language->id), //array(array('name'=>$this->l('customer'), 'id_group' => 1))
                        'id' => 'id_group',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Condition'),
                    'name' => 'c_type',
                    'required' => true,
                    'lang' => false,
                    'desc' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cartcon/views/templates/admin/search-attribute-value.tpl'),
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 1,
                                'name' => $this->l('Must order at least X products from selected category')
                            ),
                            array(
                                'id' => 0,
                                'name' => $this->l('Can\'t more than X products from selected category')
                            ),
                            array(
                                'id' => 6,
                                'name' => $this->l('Must order at least X products from selected manufacturer')
                            ),
                            array(
                                'id' => 2,
                                'name' => $this->l('Must order at least X quantity of selected product')
                            ),
                            array(
                                'id' => 3,
                                'name' => $this->l('Cant order more than X quantity of selected product')
                            ),
                            array(
                                'id' => 7,
                                'name' => $this->l('Can\'t order more than X products with the same attribute value')
                            ),
                            array(
                                'id' => 4,
                                'name' => $this->l('Cart must have at least X products')
                            ),
                            array(
                                'id' => 5,
                                'name' => $this->l('Cart can\'t have more than X products')
                            )
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Target item'),
                    'hint' => $this->l('ID of element selected as a "target" of condition (category ID or product ID)'),
                    'name' => 'c_target',
                    'required' => true,
                    'lang' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Quantity'),
                    'hint' => $this->l('Quantity of products from selected category required to place an order'),
                    'name' => 'c_value',
                    'required' => true,
                    'lang' => false,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Accept only multiplied qty'),
                    'desc' => $this->l('Option when enabled will accept only multiplied quantity values, for example').' <span class="multi_expample">6, 12, 18, 24, ...</span>',
                    'name' => 'multiply',
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
                array(
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
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
        );
        return parent::renderForm().$this->script();
    }

    public function script()
    {
        $this->context->smarty->assign('cartQuantityConditionLink', $this->context->link->getAdminLink('AdminCartQuantityCondition', true));
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cartcon/views/templates/admin/script.tpl');
    }

    public function displayAjax()
    {
        if (Tools::getValue('ajax') == 1 && Tools::getValue('searchForAttributeValue')) {
            $searchResults = Db::getInstance()->executeS('SELECT a.id_attribute, al.name AS attribute_value, agl.public_name AS attribute_group FROM 
            `' . _DB_PREFIX_ . 'attribute` a
            INNER JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute`)
            INNER JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (a.`id_attribute_group` = agl.`id_attribute_group`)
			WHERE (al.`name` like "%' . (string)Tools::getValue('searchQuery') . '%") AND al.`id_lang` = "' . $this->context->language->id . '"
			GROUP BY a.id_attribute');
            $this->context->smarty->assign('searchResults', $searchResults);
            die($this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cartcon/views/templates/admin/search-attribute-value-result.tpl'));
        }
        return parent::displayAjax();
    }

    public function processAdd()
    {
        $_POST['id_shop'] = $this->context->shop->id;
        $object = parent::processAdd();
        return true;
    }

    public function processUpdate()
    {
        $object = parent::processUpdate();
        return true;
    }

    public function postProcess()
    {
        return parent::postProcess();
    }

    public function getConditionType($group, $row)
    {
        if ($row['c_type'] == 0)
        {
            return $this->l('Can\'t order more than defined quantity of products from category');
        }
        if ($row['c_type'] == 1)
        {
            return $this->l('Must order at least defined quantity of products from category');
        }
        if ($row['c_type'] == 2)
        {
            return $this->l('Must order at least defined quantity of product');
        }
        if ($row['c_type'] == 3)
        {
            return $this->l('Can\'t order more than defined quantity of product');
        }
        if ($row['c_type'] == 4)
        {
            return $this->l('Cart must have at least defined quantity of products');
        }
        if ($row['c_type'] == 5)
        {
            return $this->l('Cart can\'t have more than defined quantity of products');
        }
        if ($row['c_type'] == 6)
        {
            return $this->l('Must order at least defined quantity of products from manufacturer');
        }
        if ($row['c_type'] == 7) {
            return $this->l('Can\'t order more than X products with the same attribute value');
        }
    }

    public function getCustomerGroup($group, $row)
    {
        $group = new Group($row['c_group'], $this->context->language->id);
        return $group->name;
        //return $this->l('Customer');
    }

    public function getConditionTarget($group, $row)
    {
        if ($row['c_type'] == 5 || $row['c_type'] == 4)
        {
            return $this->l('Cart');
        }

        if ($row['c_type'] == 1 || $row['c_type'] == 0)
        {
            if ($row['c_target'])
            {
                $category = new Category($row['c_target'], $this->context->language->id);
                return $category->name;
            }
            else
            {
                return '-';
            }
        }
        if ($row['c_type'] == 2 || $row['c_type'] == 3)
        {
            if ($row['c_target'])
            {
                $product = new Product($row['c_target'], false, $this->context->language->id);
                return $product->name;
            }
            else
            {
                return '-';
            }
        }
        if ($row['c_type'] == 6)
        {
            if ($row['c_target'])
            {
                $manufacturer = new Manufacturer($row['c_target'], $this->context->language->id);
                return $manufacturer->name;
            }
            else
            {
                return '-';
            }
        }

        if ($row['c_type'] == 7) {
            $attribute = new Attribute($row['c_target'], $this->context->language->id);
            $attributeGroup = new AttributeGroup($attribute->id_attribute_group);
            return $attributeGroup->public_name[$this->context->language->id] . ': ' . $attribute->name;
        }
    }


}