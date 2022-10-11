<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2015 silbersaiten
 * @version   1.0.3
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

class FormMakerElementValue extends ObjectModel
{
    public $id_fm_form_element;
    public $position;
    public $date_add;
    public $date_upd;
    public $name;

    public static $definition = array(
        'table' => 'fm_form_element_value',
        'primary' => 'id_fm_form_element_value',
        'multilang' => true,
        'fields' => array(
            'id_fm_form_element'    => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'position'              => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_add'              => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd'              => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'name'                  => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128)
        )
    );
}
