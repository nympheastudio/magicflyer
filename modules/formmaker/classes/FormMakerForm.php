<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2016 silbersaiten
 * @version   1.1.1
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

class FormMakerForm extends ObjectModel
{
    public $name;
    public $receivers;
    public $submit_delay;
    public $captcha;
    public $page_title;
    public $description;
    public $submit_button;
    public $message_on_completed;
    public $meta_description;
    public $redirect_on_success;
    public $send_autoresponse;
    public $meta_keywords;
    public $meta_title;
    public $active;
    public $link_rewrite;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table'     => 'fm_form',
        'primary'   => 'id_fm_form',
        'multilang' => true,
        'fields'    => array(
            'active'               => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'captcha'              => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add'             => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd'             => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'receivers'            => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'description'          => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
            'message_on_completed' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'),
            'send_autoresponse'    => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'redirect_on_success'  => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'submit_delay'         => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false),
            'submit_button'        => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 100),
            'meta_description'     => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'meta_keywords'        => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'meta_title'           => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
            'name'                 => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128),
            'page_title'           => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128),
            'link_rewrite'         => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 128)
        )
    );

    public function add($autodate = true, $null_values = false)
    {
        if (parent::add($autodate, $null_values)) {
            $this->updateFormProducts();
            $this->setFormGroups(Tools::getValue('groupBox'));

            $fields = Tools::getValue('input');

            if ((int)Tools::getValue('override_contact_form') == 1) {
                Configuration::updateValue('FM_CONTACT_FORM', $this->id);
            }

            if ($fields && is_array($fields)) {
                $position = 1;

                foreach ($fields as $field) {
                    $settings = array_key_exists('settings', $field) ? $field['settings'] : false;
                    $validation = (array_key_exists('validation', $field) && $field['validation'] != 'false')
                        ? $field['validation']
                        : false;
                    
                    if (isset($field['deleted']) && $field['deleted'] == 1) {
                        continue;
                    }
                    
                    if (!$settings && $validation) {
                        $settings = array();
                    }
                    
                    if (is_array($settings)) {
                        $settings['validation'] = $validation;
                    }

                    $element_object = new FormMakerElement();

                    $element_object->id_fm_form = $this->id;
                    $element_object->position = $position;
                    $element_object->required = (bool)$field['required'];
                    $element_object->type = FormMakerElement::getTypeByReference($field['type']);
                    
                    if (!FormMakerElement::isNotAnInputBlock($field['type'])) {
                        $element_object->name = $field['label'];
                    }
                    
                    $element_object->css_class = $field['class'];
                    $element_object->description = $field['description'];
                    $element_object->settings = $settings ? Tools::jsonEncode($settings) : false;

                    if ($element_object->add()) {
                        if (FormMakerElement::elementAcceptsValues($field['type'])
                            && isset($field['values'])
                            && count($field['values'])) {
                            $value_position = 1;

                            foreach ($field['values'] as $value) {
                                if (!isset($value['deleted']) || !$value['deleted']) {
                                    $value_object = new FormMakerElementValue();
    
                                    $value_object->id_fm_form_element = $element_object->id;
                                    $value_object->name = $value['name'];
                                    $value_object->position = $value_position;
    
                                    if ($value_object->add()) {
                                        $value_position++;
                                    }
                                }
                            }
                        }
                    }

                    $position++;
                }
            }

            return true;
        }

        return false;
    }

    public function duplicateObject()
    {
        $old_obj = $this;
        
        if ($new_object = parent::duplicateObject()) {
            $elements = $old_obj->getFormElements();

            if ($elements) {
                foreach ($elements as $element) {
                    $element_obj = new FormMakerElement((int)$element['id_fm_form_element']);

                    if (Validate::isLoadedObject($element_obj)) {

                        unset($element['id_fm_form_element']);
                        
                        $element['id_fm_form']  = $new_object->id;
                        $element['date_add']    = $new_object->date_add;
                        $element['date_upd']    = $new_object->date_upd;
                        $element['type']        = $element_obj->type;
                        $element['css_class']   = $element_obj->css_class;
                        $element['settings']    = $element_obj->settings;
                        $element['position']    = $element_obj->position;
                        
                        if (Db::getInstance()->insert('fm_form_element', $element)) {
                            $id_element = Db::getInstance()->Insert_ID();

                            $lang_data = array();
                            $lang_el_name = $element_obj->name;
                            $lang_el_desc = $element_obj->description;
                                
                            foreach ($lang_el_name as $id_lang => $name) {
                                $lang_data[$id_lang] = array(
                                    'id_fm_form_element' => (int)$id_element,
                                    'id_lang' => (int)$id_lang,
                                    'name' => pSQL($name),
                                    'description' => pSQL($lang_el_desc[(int)$id_lang])
                                );
                            }
                                
                            Db::getInstance()->insert('fm_form_element_lang', $lang_data);

                            $reference = FormMakerElement::getReferenceByType($element_obj->type);

                            if (FormMakerElement::elementAcceptsValues($reference)) {
                                $values = Db::getInstance()->ExecuteS(
                                    'SELECT * FROM `'._DB_PREFIX_.
                                    'fm_form_element_value`
                                    WHERE `id_fm_form_element` = '.(int)$element_obj->id.' ORDER BY `position`'
                                );

                                if ($values) {

                                    foreach ($values as $value) {
                                        if (Validate::isLoadedObject(
                                            $value_obj = new FormMakerElementValue(
                                                (int)$value['id_fm_form_element_value']
                                            )
                                        )) {
                                            $new_value = array(
                                                'id_fm_form_element' => (int)$id_element,
                                                'position'    => (int)$value_obj->position,
                                                'date_add'    => $new_object->date_add,
                                                'date_upd'    => $new_object->date_upd

                                            );

                                            Db::getInstance()->insert('fm_form_element_value', $new_value);
                                            $id_value = Db::getInstance()->Insert_ID();

                                            $value_lang = array();
                                            foreach ($value_obj->name as $id_lang_v => $name_v) {
                                                $value_lang[] = array(
                                                    'id_fm_form_element_value' => (int)$id_value,
                                                    'id_lang' => (int)$id_lang_v,
                                                    'name' => pSQL($name_v)
                                                );
                                            }

                                            if (count($value_lang)) {
                                                Db::getInstance()->insert('fm_form_element_value_lang', $value_lang);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $this->addToDB('group', $old_obj->id, $new_object->id);
            $this->addToDB('shop', $old_obj->id, $new_object->id);
            $this->addToDB('product', $old_obj->id, $new_object->id, 'products');

            return $new_object;
        }
        
        return false;
    }

    public function addToDB($param, $id, $new_id, $names = false)
    {
        $values = Db::getInstance()->ExecuteS(
            'SELECT * FROM `'._DB_PREFIX_.
            'fm_form_'.($names ? $names : $param).'`
            WHERE `id_fm_form` = '.(int)$id
        );

        if (count($values)) {
            foreach ($values as $value) {
                $new_value = array(
                    'id_fm_form' => (int)$new_id,
                    'id_'.$param => (int)$value['id_'.$param]
                );
                Db::getInstance()->insert('fm_form_'.($names ? $names : $param), $new_value);
            }
        }
    }

    public function update($null_values = false)
    {
        if (parent::update($null_values)) {
            $this->updateFormProducts();
            if (!Tools::isSubmit('statusfm_form')) {
                $this->setFormGroups(Tools::getValue('groupBox'));
            }

            if ((int)Tools::getValue('override_contact_form') == 1) {
                Configuration::updateValue('FM_CONTACT_FORM', $this->id);
            } else {
                if ((int)Configuration::get('FM_CONTACT_FORM') == $this->id) {
                    Configuration::updateValue('FM_CONTACT_FORM', 0);
                }
            }

            $fields = Tools::getValue('input');

            if ($fields && is_array($fields)) {
                $position_index = array();

                foreach ($fields as $field) {
                    $settings = array_key_exists('settings', $field) ? $field['settings'] : false;
                    $validation = (array_key_exists('validation', $field) && $field['validation'] != 'false')
                        ? $field['validation']
                        : false;
                    
                    if (!$settings && $validation) {
                        $settings = array();
                    }
                    
                    if (is_array($settings)) {
                        $settings['validation'] = $validation;
                    }

                    if ($field['id']) {
                        $element_object = new FormMakerElement((int)$field['id']);

                        if (Validate::isLoadedObject($element_object)) {
                            if ((int)$field['deleted'] == 1) {
                                $element_object->delete();

                                continue;
                            }
                        } else {
                            $element_object = false;
                        }
                    } elseif ((int)$field['deleted'] == 1) {
                        continue;
                    } else {
                        $element_object = new FormMakerElement();
                    }

                    if ($element_object) {
                        $element_object->id_fm_form = $this->id;
                        $element_object->required = (bool)$field['required'];
                        $element_object->type = FormMakerElement::getTypeByReference($field['type']);
                        
                        if (!FormMakerElement::isNotAnInputBlock($field['type'])) {
                            $element_object->name = $field['label'];
                        }
                        
                        $element_object->css_class = $field['class'];
                        $element_object->description = $field['description'];
                        $element_object->settings = $settings ? Tools::jsonEncode($settings) : false;

                        if ($element_object->save()) {
                            array_push($position_index, $element_object->id);

                            if (FormMakerElement::elementAcceptsValues($field['type'])
                                && isset($field['values'])
                                && count($field['values'])) {
                                foreach ($field['values'] as $value) {
                                    if ($value['id']) {
                                        $value_object = new FormMakerElementValue((int)$value['id']);

                                        if (Validate::isLoadedObject($value_object)) {
                                            if ((int)$value['deleted'] == 1) {
                                                $value_object->delete();

                                                continue;
                                            }
                                        } else {
                                            $value_object = false;
                                        }
                                    } else {
                                        $value_object = new FormMakerElementValue();
                                    }
                                    
                                    if ($value_object) {
                                        $value_object->id_fm_form_element = $element_object->id;
                                        $value_object->name = $value['name'];
    
                                        $value_object->save();
                                    }
                                }
                            }
                        }
                    }
                }

                if (count($position_index)) {
                    $position = 1;

                    foreach ($position_index as $id_form_element) {
                        if (Validate::isLoadedObject($element_object = new FormMakerElement((int)$id_form_element))) {
                            $element_object->position = $position;

                            $element_object->save();

                            $position++;
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }

    public function delete()
    {
        if (parent::delete()) {
            Db::getInstance()->delete('fm_form_products', '`id_fm_form` = '.(int)$this->id);
            Db::getInstance()->delete('fm_form_group', '`id_fm_form` = '.(int)$this->id);

            $elements = $this->getFormElements();

            if ($elements) {
                foreach ($elements as $element) {
                    $element_obj = new FormMakerElement((int)$element['id_fm_form_element']);

                    if (Validate::isLoadedObject($element_obj)) {
                        $element_obj->delete();
                    }
                }
            }

            return true;
        }

        return false;
    }
    
    public function getFormGroups()
    {
        $r = Db::getInstance()->ExecuteS(
            'SELECT `id_group` FROM `'._DB_PREFIX_.'fm_form_group` WHERE `id_fm_form` = '.(int)$this->id
        );
        $list = array();
        
        if ($r) {
            foreach ($r as $k => $v) {
                $list[] = (int)$v['id_group'];
            }
        }
        
        return $list;
    }
    
    public function setFormGroups($list)
    {
        Db::getInstance()->delete('fm_form_group', '`id_fm_form` = '.(int)$this->id);
        
        if (Group::isFeatureActive() && is_array($list) && count($list)) {
            foreach ($list as $id_group) {
                if (Validate::isUnsignedId($id_group)) {
                    Db::getInstance()->insert(
                        'fm_form_group',
                        array('id_fm_form' => (int)$this->id, 'id_group' => (int)$id_group)
                    );
                }
            }
        }
    }

    public function getFormElements()
    {
        return Db::getInstance()->ExecuteS(
            'SELECT
            `id_fm_form_element`
            FROM
            `'._DB_PREFIX_.'fm_form_element`
            WHERE
            `id_fm_form` = '.(int)$this->id.' ORDER BY `position`'
        );
    }

    public function getFormData($id_lang = null)
    {
        $r = array();
        $i = 0;

        $elements = $this->getFormElements();

        if ($elements) {
            foreach ($elements as $element) {
                $element_obj = new FormMakerElement((int)$element['id_fm_form_element'], $id_lang);

                if (Validate::isLoadedObject($element_obj)) {
                    $reference = FormMakerElement::getReferenceByType($element_obj->type);

                    $r[$i] = array(
                        'id'          => $element_obj->id,
                        'label'       => $element_obj->name,
                        'required'    => (bool)$element_obj->required,
                        'type'        => $reference,
                        'description' => $element_obj->description,
                        'settings'    => $element_obj->settings ? Tools::jsonDecode($element_obj->settings) : false,
                        'css_class'   => $element_obj->css_class
                    );

                    if (FormMakerElement::elementAcceptsValues($reference)) {
                        $values = Db::getInstance()->ExecuteS(
                            'SELECT `id_fm_form_element_value` FROM `'._DB_PREFIX_.
                            'fm_form_element_value`
                            WHERE `id_fm_form_element` = '.(int)$element_obj->id.' ORDER BY `position`'
                        );

                        if ($values) {
                            foreach ($values as $value) {
                                if (Validate::isLoadedObject(
                                    $value_obj = new FormMakerElementValue(
                                        (int)$value['id_fm_form_element_value'],
                                        $id_lang
                                    )
                                )) {
                                    if (!array_key_exists('values', $r[$i])) {
                                        $r[$i]['values'] = array();
                                    }

                                    array_push($r[$i]['values'], array(
                                        'id'   => $value_obj->id,
                                        'name' => $value_obj->name
                                    ));
                                }
                            }
                        }
                    }

                    $i++;
                }
            }
        }

        return count($r) ? $r : false;
    }

    public function updateFormProducts()
    {
        $form_product_ids = Tools::getValue('inputFormProducts', null);

        Db::getInstance()->delete('fm_form_products', '`id_fm_form` = '.(int)$this->id);

        if (!Tools::isEmpty($form_product_ids)) {
            $form_product_ids = explode('-', $form_product_ids);

            foreach ($form_product_ids as $form_product) {
                if (Validate::isUnsignedId($form_product)
                    && FormMakerForm::canBeAssociatedWithProduct($this->id, (int)$form_product)) {
                    Db::getInstance()->insert(
                        'fm_form_products',
                        array('id_fm_form' => (int)$this->id, 'id_product' => (int)$form_product)
                    );
                }
            }
        }
    }

    public function getFormProducts($id_lang)
    {
        return Db::getInstance()->executeS(
            'SELECT
            fp.`id_product`,
            p.`reference`,
            pl.`name`
            FROM
            `'._DB_PREFIX_.'fm_form_products` fp
            LEFT JOIN
            `'._DB_PREFIX_.'product` p ON (p.`id_product`= fp.`id_product`)
            '.Shop::addSqlAssociation('product', 'p').'
            LEFT JOIN
            `'._DB_PREFIX_.'product_lang` pl ON (
                p.`id_product` = pl.`id_product`
                AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
            )
            WHERE
            fp.`id_fm_form` = '.(int)$this->id
        );
    }

    public function getElementById($id_element, $id_lang = null)
    {
        if (!Validate::isUnsignedId($id_element)
            || !Validate::isLoadedObject($element = new FormMakerElement($id_element, $id_lang))) {
            return false;
        }

        return $element->id_fm_form == $this->id ? $element : false;
    }

    public static function getFormsList($active = true, $id_lang = null)
    {
        if (!Validate::isUnsignedId($id_lang)) {
            $id_lang = Context::getContext()->language->id;
        }

        if (!Validate::isBool($active)) {
            return false;
        }

        return Db::getInstance()->executeS(
            'SELECT
            f.`id_fm_form`,
            fl.`name`
            FROM
            `'._DB_PREFIX_.'fm_form` f
            LEFT JOIN
            `'._DB_PREFIX_.'fm_form_lang` fl
            ON (f.`id_fm_form` = fl.`id_fm_form` AND fl.`id_lang` = '.(int)$id_lang.')'.
            ($active ? ' WHERE f.`active` = 1' : '')
        );
    }

    public static function canBeAssociatedWithProduct($id_form, $id_product)
    {
        return !Db::getInstance()->getValue(
            'SELECT
            `id_product`,
            `id_fm_form`
            FROM
            `'._DB_PREFIX_.'fm_form_products`
            WHERE
            `id_product` = '.(int)$id_product.'
            AND
            `id_fm_form` != '.(int)$id_form
        );
    }

    public static function getProductForm($id_product, $id_shop, $id_lang = null, $active = true)
    {
        $assigned_to_all = Configuration::getGlobalValue('FM_FORM_ASSIGNED_TO_ALL');
        
        if ($assigned_to_all) {
            $id_form = Db::getInstance()->getValue(
                'SELECT
                `id_fm_form`
                FROM
                `'._DB_PREFIX_.'fm_form_shop`
                WHERE `id_fm_form` = '.(int)$assigned_to_all.' AND `id_shop` = ' . (int)$id_shop
            );
        } else {
            $id_form = Db::getInstance()->getValue(
                'SELECT
                f.`id_fm_form`
                FROM
                `'._DB_PREFIX_.'fm_form_products` fp
                LEFT JOIN `'._DB_PREFIX_.'fm_form` f ON (f.`id_fm_form` = fp.`id_fm_form`)
                LEFT JOIN `'._DB_PREFIX_.'fm_form_shop` fs ON (f.`id_fm_form` = fs.`id_fm_form`)
                WHERE fp.`id_product` = '.(int)$id_product.
                ($active ? ' AND f.`active` = 1' : '')
            );
        }

        if ($id_form) {
            return new FormMakerForm(
                (int)$id_form,
                Validate::isUnsignedId($id_lang) ? $id_lang : Context::getContext()->language->id,
                (int)$id_shop
            );
        }

        return false;
    }
    
    public function checkCustomerAccess(Customer $customer)
    {
        if (!Group::isFeatureActive()) {
            return true;
        }
        
        if (!Validate::isLoadedObject($customer)) {
            $groups = array($customer->id_default_group);
        } else {
            $groups = $customer->getGroups();
        }
        
        $form_groups = $this->getFormGroups();
        
        if (!count($form_groups) || !is_array($groups) || !count($groups)) {
            return false;
        }
        
        $i = array_intersect($form_groups, $groups);
        
        return count($i) > 0;
    }
}
