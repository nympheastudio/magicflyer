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

class AdminCartValueConditionController extends ModuleAdminController
{
    protected $position_identifier = 'id_cartcon_value';
    public function __construct()
    {
        $this->table = 'cartcon_value';
        $this->className = 'CartValueCondition';
        $this->lang = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
        $this->bootstrap = true;
        $this->_orderBy = 'id_cartcon_value';


        $this->fields_list = array(
            'id_cartcon_value' => array(
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

    public function initToolbar(){
        unset($this->toolbar_btn);
        $Link = new Link();
        $this->toolbar_btn['new']=array('desc'=>$this->l('Add new'), 'href'=>$Link->getAdminLink('AdminCartValueCondition').'&addcartcon_value');
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
        if(isset($obj->id))
        {
            $this->display = 'edit';
        } else {
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
                        'query' => Group::getGroups($this->context->language->id), //array(array('name'=>$this->l('customer'), 'id_group' => 1)),
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
                    'options' => array(
                        'query'=> array(array('id'=>1, 'name' =>$this->l('Must order from selected category'))),
                        'id'=> 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Target item'),
                    'hint' => $this->l('ID of element selected as a "target" of condition (category / product)'),
                    'name' => 'c_target',
                    'required' => true,
                    'lang' => false,
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
        if ($row['c_type']==1){
            return $this->l('Must order from selected category');
        }
    }

    public function getCustomerGroup($group, $row)
    {
        $group = new Group($row['c_group'],$this->context->language->id);
        return $group->name;
        //return $this->l('Customer');
    }

    public function getConditionTarget($group, $row)
    {
        if ($row['c_type']==1) {
            if ($row['c_target']) {
                $category = new Category($row['c_target'],$this->context->language->id);
                return $category->name;
            }
            else {
                return '-';
            }
        }
    }

    public function getConditionValue($group, $row)
    {
        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        return Tools::displayPrice($row['c_value'], $currency);
    }

}