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

class AdminCartCountryConditionController extends ModuleAdminController
{
    protected $position_identifier = 'id_cartcon_country';

    public function __construct()
    {
        $this->table = 'cartcon_country';
        $this->className = 'CartCountryCondition';
        $this->lang = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();
        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
        $this->bootstrap = true;
        $this->_orderBy = 'id_cartcon_country';

        $this->fields_list = array(
            'id_cartcon_country' => array(
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
                'title' => $this->l('Condition'),
                'width' => 'auto',
                'orderby' => true,
                //'desc' => $this->l('read').'<a href="">'.$this->l('how to get Product ID').'</a>',
                'callback' => 'getConditionType',
                'filter_key' => 'c_type',
            ),


            'c_target' => array(
                'title' => $this->l('Country'),
                'width' => 'auto',
                'orderby' => true,
                //'desc' => $this->l('read').'<a href="">'.$this->l('how to get Product ID').'</a>',
                'callback' => 'getConditionTarget',
                'filter_key' => 'c_target',
            ),

            'c_value' => array(
                'title' => $this->l('Value'),
                'width' => 'auto',
                'orderby' => true,
                //'desc' => $this->l('read').'<a href="">'.$this->l('how to get Product ID').'</a>',
                'callback' => 'getConditionValue',
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
        $this->toolbar_btn['new'] = array('desc' => $this->l('Add new'), 'href' => $Link->getAdminLink('AdminCartCountryCondition') . '&addcartcon_country');
    }

    public function initFormToolBar()
    {
    }

    public function renderForm()
    {
        $this->initFormToolBar();
        if (!$this->loadObject(true))
            return;
        $cover = false;
        $obj = $this->loadObject(true);
        if (isset($obj->id)) {
            $this->display = 'edit';
        } else {
            $this->display = 'add';
        }
        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Cart Condition'),
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Customer Group'),
                    'name' => 'c_group',
                    'required' => true,
                    'lang' => false,
                    'options' => array(
                        'query' => Group::getGroups($this->context->language->id), //array(array('name'=>$this->l('customer'), 'id_group' => 1)),
                        'id' => 'id_group',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Target country'),
                    'name' => 'c_target',
                    'required' => true,
                    'options' => array(
                        'query' => Country::getCountries($this->context->language->id),
                        'id' => 'id_country',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Condition'),
                    'name' => 'c_type',
                    'required' => true,
                    'lang' => false,
                    'options' => array(
                        'query' => array(
                            array('id' => 1, 'name' => $this->l('Cart must be worth at least ...')),
                            array('id' => 2, 'name' => $this->l('Cart can\'t be worth more than ...'))
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Condition tax'),
                    'name' => 'c_tax',
                    'required' => true,
                    'lang' => false,
                    'options' => array(
                        'query' => array(
                            array('id' => 1, 'name' => $this->l('Tax included')),
                            array('id' => 2, 'name' => $this->l('Tax excluded'))
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Cart value type'),
                    'name' => 'c_cartValueType',
                    'required' => true,
                    'lang' => false,
                    'options' => array(
                        'query' => array(
                            array('id' => 1, 'name' => $this->l('Products + shipping')),
                            array('id' => 2, 'name' => $this->l('Products'))
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Value'),
                    'hint' => $this->l('Value of order for selected condition in default currency'),
                    'name' => 'c_value',
                    'prefix' => $currency->sign,
                    'required' => true,
                    'lang' => false,
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
        return parent::renderForm();
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
        if ($row['c_type'] == 1) {
            return $this->l('Cart must be worth more than defined value');
        } elseif ($row['c_type'] == 2) {
            return $this->l('Cart can\'t be worth more than defined value');
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
        if ($row['c_type'] == 1 || $row['c_type'] == 2) {
            if ($row['c_target']) {
                $country = new country($row['c_target'], $this->context->language->id);
                return $country->name;
            } else {
                return '-';
            }
        }
    }

    public function getConditionValue($group, $row)
    {
        $tax = ($row['c_tax'] == 1 ? $this->l('tax incl.') : $this->l('tax excl.'));
        $type = ($row['c_cartValueType'] == 1 ? $this->l('Products value + Shipping cost') : $this->l('Products value'));
        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        return Tools::displayPrice($row['c_value'], $currency) . ' ' . $tax . ' (' . $type . ')';
    }

}