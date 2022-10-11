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

class FormMakerReport extends ObjectModel
{
    public $id_fm_form;
    public $id_customer;
    public $id_product;
    public $name;
    public $send;
    public $date_add;
    public $date_upd;
    
    public static $definition = array(
        'table' => 'fm_form_report',
        'primary' => 'id_fm_form_report',
        'fields' => array(
            'id_fm_form'    => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_customer'   => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_product'    => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'name'          => array('type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128),
            'send'          => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add'      => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd'      => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat')
        )
    );
    
    public static function setReport(FormMakerForm $form, $form_values, $product = false)
    {
        if (!is_array($form_values) || !count($form_values)) {
            return false;
        }
        
        $value_list = array();
        
        foreach ($form_values as $f) {
            if (!FormMakerElement::isNotAnInputBlock($f['type'])) {
                $value_list[] = array(
                    'type' => FormMakerElement::getTypeByReference($f['type']),
                    'field' => pSQL($f['field']),
                    'value' => ($f['type'] == 'selectInput'
                        || $f['type'] == 'radioInput'
                        || $f['type'] == 'checkboxInput') ? Tools::jsonEncode($f['value']) : $f['value']
                );
            }
        }
        
        if (count($value_list)) {
            $r = new FormMakerReport();
            
            $r->id_fm_form = (int)$form->id;
            $r->name = $form->name;
            $r->id_customer = Context::getContext()->customer->id;
            
            if (Validate::isLoadedObject($product)) {
                $r->id_product = $product->id;
            }
            
            if ($r->add()) {
                foreach ($value_list as $f) {
                    $f['id_fm_form_report'] = (int)$r->id;

                    Db::getInstance()->insert('fm_form_report_values', $f);
                }
                
                return (int)$r->id;
            }
        }
        
        return false;
    }
    
    public function delete()
    {
        if (parent::delete()) {
            Db::getInstance()->delete('fm_form_report_values', '`id_fm_form_report` = '.(int)$this->id);
            
            return true;
        }

        return false;
    }
    
    public function getReportData()
    {
        $fields = Db::getInstance()->ExecuteS(
            'SELECT * FROM `'._DB_PREFIX_.'fm_form_report_values` WHERE `id_fm_form_report` = '.(int)$this->id
        );
        
        if (!$fields) {
            return false;
        }
        
        foreach ($fields as &$f) {
            if (FormMakerElement::getReferenceByType($f['type']) == 'selectInput'
                || FormMakerElement::getReferenceByType($f['type']) == 'radioInput'
                || FormMakerElement::getReferenceByType($f['type']) == 'checkboxInput') {
                $f['value'] = implode(
                    ', ',
                    array_map(array('Tools', 'htmlentitiesDecodeUTF8'), Tools::jsonDecode($f['value']))
                );
            } else {
                $f['value'] = Tools::htmlentitiesDecodeUTF8($f['value']);
            }
            
            $f['reference'] = FormMakerElement::getReferenceByType($f['type']);
        }
        return $fields;
    }
}
