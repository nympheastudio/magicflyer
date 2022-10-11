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
class CartCondition extends ObjectModel {

    public $id_cartcon;
    public $c_type;
    public $c_option;
    public $c_value;
    public $active;
    public $id_shop;
    public static $definition = array(
        'table' => 'cartcon',
        'primary' => 'id_cartcon',
        'multilang' => false,
        'fields' => array(
            'id_cartcon' => array('type' => ObjectModel :: TYPE_INT),
            'id_shop' => array('type' => ObjectModel :: TYPE_INT),
            'c_type' => array('type' => ObjectModel :: TYPE_STRING),
            'c_option' => array('type' => ObjectModel :: TYPE_INT),
            'c_value' => array('type' => ObjectModel :: TYPE_INT),
            'active' => array('type' => ObjectModel :: TYPE_INT),
        ),
    );

    public function returnAllActive() {

        $record = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'cartcon` WHERE active="1"');
        return $record;
    }

    public function __construct($id_cartcon = NULL) {

        parent::__construct($id_cartcon);
    }
}