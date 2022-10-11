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

class AdminCartAssociationsConditionController extends ModuleAdminController
{
    protected $position_identifier = 'id_cartcon_value';

    public function __construct()
    {
        $this->table = 'cartcon_ass';
        $this->className = 'CartAssociationsCondition';
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
        $this->_orderBy = 'id_cartcon_ass';


        $this->fields_list = array(
            'id_cartcon_ass' => array(
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

            'c_target1' => array(
                'title' => $this->l('Target item (1)'),
                'width' => 'auto',
                'orderby' => true,
                //'desc' => $this->l('read').'<a href="">'.$this->l('how to get Product ID').'</a>',
                'callback' => 'getConditionTargetItem',
                'filter_key' => 'c_target1',
            ),

            'c_target2' => array(
                'title' => $this->l('Associated item (2)'),
                'width' => 'auto',
                'orderby' => true,
                //'desc' => $this->l('read').'<a href="">'.$this->l('how to get Product ID').'</a>',
                'callback' => 'getConditionAssociatedItem',
                'filter_key' => 'c_target2',
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
            'href' => $Link->getAdminLink('AdminCartAssociationsCondition') . '&addcartcon_ass'
        );
    }

    public function initFormToolBar()
    {
    }

    public function renderForm()
    {
        $this->initFormToolBar();
        if (!$this->loadObject(true)) {
            return;
        }
        $cover = false;
        $obj = $this->loadObject(true);

        if (isset($obj->id)) {
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
                array(
                    'type' => 'select',
                    'label' => $this->l('Type of condition'),
                    'name' => 'c_type',
                    'required' => true,
                    'lang' => false,
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 1,
                                'name' => $this->l('Associations between product and product')
                            ),
                            array(
                                'id' => 2,
                                'name' => $this->l('Associations between product and category')
                            ),
                            array(
                                'id' => 3,
                                'name' => $this->l('Associations between category and category')
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('For what customer group'),
                    'name' => 'c_group',
                    'required' => true,
                    'lang' => false,
                    'options' => array(
                        'query' => Group::getGroups($this->context->language->id),
                        //array(array('name'=>$this->l('customer'), 'id_group' => 1)),
                        'id' => 'id_group',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Target item (1)'),
                    'hint' => $this->l('ID of element selected as a "target" of condition (category / product)'),
                    'desc' => $this->l('Condition will take an effect if customer will put this object to cart') . ' (<span class="object"></span>)',
                    'name' => 'c_target1',
                    'required' => true,
                    'lang' => false,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Include subcategories of') . ' ' . $this->l('Target item (1)'),
                    'name' => 'subcatt',
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
                    'type' => 'select',
                    'label' => $this->l('Condition'),
                    'name' => 'c_value',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 1,
                                'name' => $this->l('If customer will add to cart Target item (1) then it must order Associated item (2)')
                            ),
                            array(
                                'id' => 2,
                                'name' => $this->l('If customer will add to cart Target item (1) then it will be forbidden to order Associated item (2)')
                            ),
                            array(
                                'id' => 3,
                                'name' => $this->l('If customer will add to cart Target item (1) then it will be forbidden to add any other product to cart')
                            ),
                            array(
                                'id' => 4,
                                'name' => $this->l('If customer will add to cart Target item (1) then it will be forbidden to add products from categories other than Target item (1)')
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Associated item (2)'),
                    'hint' => $this->l('ID of element selected as a "target" of condition (category / product)'),
                    'name' => 'c_target2',
                    'desc' => '-',
                    'required' => true,
                    'lang' => false,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Include subcategories of') . ' ' . $this->l('Associated item (2)'),
                    'name' => 'subcata',
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
        return parent::renderForm() . $this->script();
    }

    public function script()
    {
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'cartcon/views/templates/admin/AdminCartAssociationsCondition.tpl');
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
            if ($row['c_value'] == 1) {
                $condition_details = '<br>' . $this->l('If customer will add to cart Target item (1) then it must order Associated item (2)');
            } elseif ($row['c_value'] == 2) {
                $condition_details = '<br>' . $this->l('If customer will add to cart Target item (1) then it will be forbidden to order Associated item (2)');
            } elseif ($row['c_value'] == 3) {
                $condition_details = '<br>' . $this->l('If customer will add to cart Target item (1) then it will be forbidden to add any other product to cart');
            } else {
                $condition_details = '';
            }
            return '<strong>' . $this->l('Association between products') . '</strong>' . $condition_details;
        } elseif ($row['c_type'] == 2) {
            if ($row['c_value'] == 1) {
                $condition_details = '<br>' . $this->l('If customer will add to cart product (Target item 1) then it must order product from category (Associated item 2)');
            } elseif ($row['c_value'] == 2) {
                $condition_details = '<br>' . $this->l('If customer will add to cart Product (Target item 1) then it will be forbidden to order product from category (Associated item 2)');
            } elseif ($row['c_value'] == 3) {
                $condition_details = '<br>' . $this->l('If customer will add to cart Target item (1) then it will be forbidden to add any other product to cart');
            } else {
                $condition_details = '';
            }
            return '<strong>' . $this->l('Association between product and category') . '</strong>' . $condition_details;
        } elseif ($row['c_type'] == 3) {
            if ($row['c_value'] == 1) {
                $condition_details = '<br>' . $this->l('If customer will add to cart product from category (Target item 1) then it must order product from category (Associated item 2)');
            } elseif ($row['c_value'] == 2) {
                $condition_details = '<br>' . $this->l('If customer will add to cart product from category (Target item 1) then it will be forbidden to order product from category (Associated item 2)');
            } elseif ($row['c_value'] == 3) {
                $condition_details = '<br>' . $this->l('If customer will add to cart product from category (Target item 1) then it will be forbidden to add any other product to cart');
            } elseif ($row['c_value'] == 4) {
                $condition_details = '<br>' . $this->l('If customer will add to cart product from category (Target item 1) then it will be forbidden to add products from other categories than category (Target item 1)');
            } else {
                $condition_details = '';
            }
            return '<strong>' . $this->l('Association between category and category') . '</strong>' . $condition_details;
        }
    }

    public function getCustomerGroup($group, $row)
    {
        $group = new Group($row['c_group'], $this->context->language->id);
        return $group->name;
        //return $this->l('Customer');
    }

    public function getConditionTargetItem($group, $row)
    {
        if ($row['c_type'] == "1" || $row['c_type'] == "2") {
            if ($row['c_target1']) {
                $product = new Product($row['c_target1'], false, $this->context->language->id);
                return $product->name;
            } else {
                return '-';
            }
        } elseif ($row['c_type'] == "3") {
            if ($row['c_target1']) {
                $category = new Category($row['c_target1'], $this->context->language->id);
                return $this->l('Category:') . ' ' . $category->name;
            } else {
                return '-';
            }
        } elseif ($row['c_type'] == "4") {
            if ($row['c_target1']) {
                $category = new Category($row['c_target1'], $this->context->language->id);
                return $this->l('Category:') . ' ' . $category->name . ($row['subcatt'] == 1 ? '<br/>' . $this->l('Including subcategories') : '');
            } else {
                return '-';
            }
        }
    }

    public function getConditionAssociatedItem($group, $row)
    {
        if ($row['c_value'] != "3") {
            if ($row['c_type'] == "1") {
                if ($row['c_target2']) {
                    $product = new Product($row['c_target2'], false, $this->context->language->id);
                    return $product->name;
                } else {
                    return '-';
                }
            } elseif ($row['c_type'] == "2" || $row['c_type'] == "3") {
                if ($row['c_value'] == 4) {
                    if ($row['c_target1']) {
                        $category = new Category($row['c_target1'], $this->context->language->id);
                        return $this->l('All other products from categories different than:') . ' ' . $category->name . ($row['subcatt'] == 1 ? '<br/>' . $this->l('Including subcategories') : '');
                    } else {
                        return '-';
                    }
                } else {
                    if ($row['c_target2']) {
                        $category = new Category($row['c_target2'], $this->context->language->id);
                        return $this->l('Category:') . ' ' . $category->name;
                    } else {
                        return '-';
                    }
                }
            }
        } else {
            return $this->l('All other products');
        }
    }

}