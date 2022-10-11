<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2015 silbersaiten
 * @version   1.1.0
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

class FormMakerElement extends ObjectModel
{
    public $id_fm_form;
    public $type;
    public $name;
    public $required;
    public $css_class;
    public $description;
    public $settings;
    public $position;
    public $date_add;
    public $date_upd;

    private static $types = array(
        'textInput'     => 1,
        'passwordInput' => 2,
        'dateInput'     => 3,
        'colorInput'    => 4,
        'fileInput'     => 5,
        'textareaInput' => 6,
        'selectInput'   => 7,
        'radioInput'    => 8,
        'checkboxInput' => 9,
        'htmlBlock'     => 10
    );

    private static $accepts_values = array(
        'selectInput', 'radioInput', 'checkboxInput'
    );
    
    private static $not_inputs = array(
        'htmlBlock'
    );

    public static $definition = array(
        'table' => 'fm_form_element',
        'primary' => 'id_fm_form_element',
        'multilang' => true,
        'fields' => array(
            'id_fm_form' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'required' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'css_class' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'settings' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'description' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
            'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'size' => 128)
        )
    );

    public function delete()
    {
        if (parent::delete()) {
            if (self::elementAcceptsValues(self::getReferenceByType($this->type))) {
                $values = Db::getInstance()->ExecuteS(
                    'SELECT `id_fm_form_element_value` FROM `'._DB_PREFIX_.
                    'fm_form_element_value` WHERE `id_fm_form_element` = '.(int)$this->id
                );

                if ($values) {
                    foreach ($values as $value) {
                        if (Validate::isLoadedObject(
                            $v = new FormMakerElementValue((int)$value['id_fm_form_element_value'])
                        )) {
                            $v->delete();
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }

    public static function getTypeByReference($reference)
    {
        return array_key_exists($reference, self::$types) ? self::$types[$reference] : false;
    }
    
    public static function isNotAnInputBlock($type)
    {
        return in_array($type, self::$not_inputs);
    }

    public static function getReferenceByType($type)
    {
        if (!Validate::isUnsignedId($type)) {
            return false;
        }

        return array_search($type, self::$types);
    }

    public static function elementAcceptsValues($reference)
    {
        return in_array($reference, self::$accepts_values);
    }

    public function getSettings()
    {
        if ($this->settings && Tools::strlen($this->settings)) {
            $settings = Tools::jsonDecode($this->settings, true);

            return is_array($settings) ? $settings : false;
        }

        return false;
    }

    public function getValueById($id_value, $id_lang = null)
    {
        if (!Validate::isUnsignedId($id_value)) {
            return false;
        }

        $value = new FormMakerElementValue((int)$id_value, $id_lang);

        if (!Validate::isLoadedObject($value) || $value->id_fm_form_element != $this->id) {
            return false;
        }

        return $value;
    }
}
