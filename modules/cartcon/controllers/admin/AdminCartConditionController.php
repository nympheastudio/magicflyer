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

class AdminCartConditionController extends ModuleAdminController
{
    protected $position_identifier = 'id_cartcon';
    public function __construct()
    {
        $this->table = 'cartcon';
        $this->className = 'CartCondition';
        $this->lang = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
        $this->bootstrap = true;
        $this->_orderBy = 'id_cartcon';
      

        $this->fields_list = array(
            'id_cartcon' => array(
                'title' => $this->l('ID'), 
                'align' => 'center', 
                'orderby' => true,
                'width' => 20
            ),
            
            'c_option' => array(
                'title' => $this->l('Cart condition'), 
                'width' => 'auto', 
                'orderby' => true, 
                //'desc' => $this->l('read').'<a href="">'.$this->l('how to get Product ID').'</a>',
                'callback' => 'getConditionOption',
                'filter_key' => 'c_option',
            ),
            
            'c_type' => array(
                'title' => $this->l('Target type'), 
                'width' => 'auto', 
                'orderby' => true, 
                //'desc' => $this->l('read').'<a href="">'.$this->l('how to get Product ID').'</a>',
                'callback' => 'getConditionType',
                'filter_key' => 'c_type',
            ),
            
            'c_value' => array(
                'title' => $this->l('Target item'), 
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
        $this->toolbar_btn['new']=array('desc'=>$this->l('Add new'), 'href'=>$Link->getAdminLink('AdminCartCondition').'&addcartcon');
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
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Cart Condition'),
                'image' => '../img/admin/cog.gif'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Condition'),
                    'name' => 'c_option',
                    'required' => true,
                    'lang' => false,
                    'options' => array(
                        'query'=> array(array('id'=>1, 'name' =>$this->l('Must have')),array('id'=>2, 'name' =>$this->l('Can\'t have'))),
                        'id'=> 'id',
                        'name' => 'name'
                    ),
                ),

                array(
                    'type' => 'select',
                    'label' => $this->l('Target type'),
                    'name' => 'c_type',
                    'required' => true,
                    'lang' => false,
                    'options' => array(
                        'query'=> array(array('id'=>1, 'name' =>$this->l('Category')),array('id'=>2, 'name' =>$this->l('Product')),array('id'=>3, 'name' =>$this->l('More than defined number of products'))),
                        'id'=> 'id',
                        'name' => 'name'
                    ),
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('Target item'),
                    'hint' => $this->l('ID of element selected as a "target" of condition (product, category), or number of products'),
                    'name' => 'c_value',
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
    
    public function getConditionValue($group, $row)
    {
        if ($row['c_type']==1){
            if ($row['c_value']){
                $category = new Category($row['c_value'],$this->context->language->id);
                return $category->name;
            } else {
                return '-';
            }
        } elseif ($row['c_type']==2){
            if ($row['c_value']){
                $product = new Product($row['c_value'], false, $this->context->language->id);
                return $product->name;
            } else {
                return '-';
            }
        } elseif ($row['c_type']==3){
            return $row['c_value'].' '.$this->l('Products');
        }
    }
    
    public function getConditionOption($group, $row)
    {
        if ($row['c_option']==1){
            return $this->l('Must have');
        } elseif ($row['c_option']==2){
            return $this->l('Can\'t have');
        }
    }
    
    public function getConditionType($group, $row)
    {
        if ($row['c_type']==1){
            return $this->l('Category');
        } elseif ($row['c_type']==2){
            return $this->l('Product');
        } elseif ($row['c_type']==3){
            return $this->l('More than defined number of products');
        }
    }    
}