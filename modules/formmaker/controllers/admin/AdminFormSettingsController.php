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

require_once(dirname(__FILE__).'/../../classes/FormMakerForm.php');
require_once(dirname(__FILE__).'/../../classes/FormMakerElement.php');
require_once(dirname(__FILE__).'/../../classes/FormMakerElementValue.php');

class AdminFormSettingsController extends ModuleAdminController
{
    public $form_assigned_to_all;
    
    public function __construct()
    {
        $this->table = 'fm_form';
        $this->className = 'FormMakerForm';
        $this->lang = true;
        $this->bootstrap = true;
        if (method_exists('Context', 'getTranslator')) {
            $this->translator = Context::getContext()->getTranslator();
        }

        $this->addRowAction('edit');
        $this->addRowAction('duplicateform');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );
        
        $this->form_assigned_to_all = (int)Configuration::getGlobalValue('FM_FORM_ASSIGNED_TO_ALL');
        
        $this->_select = 'a.`id_fm_form` as `assign_to_all`';

        $this->fields_list = array(
            'id_fm_form' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 20
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto'
            ),
            'page_title' => array(
                'title' => $this->l('URL'),
                'width' => 'auto',
                'remove_onclick' => true,
                'callback' => 'getFormURL'
            ),
            'assign_to_all' => array(
                'title' => $this->l('Assign to All'),
                'width' => 'auto',
                'remove_onclick' => true,
                'callback' => 'getAssignToAll'
            ),
            'active' => array(
                'title' => $this->l('Enabled'),
                'align' => 'text-center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'filter_key' => 'a!active'
            ),
            'date_add' => array(
                'title' => $this->l('Created'),
                'type' => 'date',
                'align' => 'text-left'
            ),
        );

        parent::__construct();
    }

    public function displayDuplicateformLink($token, $id)
    {
        unset($token);

        $this->context->smarty->assign(array(
            'href' => 'index.php?controller=AdminFormSettings&duplicatefm_form&id_fm_form='.
                (int)$id.'&token='.Tools::getAdminTokenLite('AdminFormSettings', $this->context),
            'action' => $this->l('Duplicate the form'),
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.'formmaker/views/templates/admin/list_action_dupl.tpl'
        );
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->context->controller->addJqueryUI(array(
            'ui.draggable',
            'ui.sortable',
            'ui.droppable'
        ));

        $this->context->controller->addJqueryPlugin('autocomplete');

        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/fixto.min.js');
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/formbuilder.js');
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/spectrum.js');
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/portamento-min.js');
        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/formbuilder.css');
        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/spectrum.css');
    }

    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return;
        }

        $this->context->controller->redirect_after = true;
        $this->context->controller->submit_action = 'submitAddfm_formAndStay';

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Form'),
                'icon' => 'icon-tasks'
            ),
            'input' => array(
                array(
                    'type' => 'free',
                    'label' => $this->l('Form Preview'),
                    'name' => 'form_preview'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Form Title:'),
                    'name' => 'name',
                    'size' => 33,
                    'required' => true,
                    'lang' => true,
                    'hint' => $this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Page Title:'),
                    'name' => 'page_title',
                    'size' => 33,
                    'required' => true,
                    'lang' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Friendly URL:'),
                    'name' => 'link_rewrite',
                    'size' => 33,
                    'required' => true,
                    'lang' => true,
                    'hint' => $this->l('Only letters, numbers, underscore (_) and the minus (-) character are allowed.')
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description:'),
                    'name' => 'description',
                    'autoload_rte' => true,
                    'lang' => true,
                    'hint' => $this->l('Invalid characters:').' <>;=#{}',
                    'desc' => $this->l('Will be displayed just above the form')
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('"Thank you" page content:'),
                    'name' => 'message_on_completed',
                    'autoload_rte' => true,
                    'lang' => true,
                    'hint' => $this->l('Invalid characters:').' <>;=#{}',
                    'desc' => $this->l('Leave this empty to display a default message.')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Redirect to "Thank you" page:'),
                    'name' => 'redirect_on_success',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'redirect_on_success_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'redirect_on_success_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('When activated, the customer will be redirected to the "Thank you" page upon completing the form.')
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Receivers:'),
                    'name' => 'receivers',
                    'cols' => 10,
                    'rows' => 3,
                    'class' => false,
                    'required' => true,
                    'hint' => $this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:',
                    'desc' => $this->l('Comma-separated list of emails that the form data should be emailed to')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Send autoresponse:'),
                    'name' => 'send_autoresponse',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'send_autoresponse_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'send_autoresponse_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('When activated, the customer will receive an email upon submitting the form.')
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Display in products'),
                    'name' => 'form_products',
                    'desc' => $this->l('If you want this form to display on product page, select those products here.')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Submit Delay:'),
                    'name' => 'submit_delay',
                    'size' => 3,
                    'desc' => $this->l('How much time (in seconds) your customer should wait before he can submit this form again'),
                    'default' => 30,
                    'cast' => 'intval',
                    'class' => 'fixed-width-xxl',
                    'hint' => $this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Labeling for submit button:'),
                    'name' => 'submit_button',
                    'desc' => $this->l('Alternative labeling for submit button. Leave blank to keep the default "Submit" value'),
                    'size' => 100,
                    'required' => false,
                    'lang' => true,
                    'hint' => $this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Meta Description:'),
                    'name' => 'meta_description',
                    'size' => 33,
                    'required' => false,
                    'lang' => true,
                    'hint' => $this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta Keywords:'),
                    'name' => 'meta_keywords',
                    'size' => 100,
                    'required' => false,
                    'lang' => true,
                    'hint' => $this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta Title:'),
                    'name' => 'meta_title',
                    'size' => 100,
                    'required' => false,
                    'lang' => true,
                    'hint' => $this->l('Forbidden characters:').' 0-9!<>,;?=+()@#"�{}_$%:'
                ),
                // array(
                //     'type' => 'switch',
                //     'label' => $this->l('Use CAPTCHA:'),
                //     'name' => 'captcha',
                //     'required' => false,
                //     'class' => 't',
                //     'is_bool' => true,
                //     'values' => array(
                //     array(
                //         'id' => 'captcha_on',
                //         'value' => 1,
                //         'label' => $this->l('Enabled')
                //     ),
                //     array(
                //         'id' => 'captcha_off',
                //         'value' => 0,
                //         'label' => $this->l('Disabled')
                //     )
                //     ),
                //     'hint' => $this->l('"CAPTCHA" stands for "Completely Automated Public Turing test to tell Computers and Humans Apart". It will display a picture with random text that a user will be asked to type into a text field.'),
                //     'desc' => $this->l('When activated, this form will require a customer to enter captcha from a picture in order to submit the data. Paired with "Submit delay" option this offers a somewhat decent protection against spam.')
                // ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Use CAPTCHA:'),
                    'required' => false,
                    'name' => 'captcha',
                    'hint' => $this->l('"CAPTCHA" stands for "Completely Automated Public Turing test to tell Computers and Humans Apart". It will display a picture with random text that a user will be asked to type into a text field.'),
                    'desc' => $this->l('When activated, this form will require a customer to enter captcha from a picture in order to submit the data. Paired with "Submit delay" option this offers a somewhat decent protection against spam.'),
                    // 'name' => 'new_captcha'
                ),                
                array(
                    'type' => 'switch',
                    'label' => $this->l('Override contact form:'),
                    'name' => 'override_contact_form',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'override_contact_form_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'override_contact_form_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'free',
                    'label' => null,
                    'required' => true,
                    'name' => 'form_contents'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Status:'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable or disable this form')
                ),
            )
        );
        
        if (Group::isFeatureActive()) {
            $groups = Group::getGroups($this->default_form_language, true);
            
            $this->fields_form['input'] = array_merge(
                $this->fields_form['input'],
                array(
                    array(
                        'type' => 'group',
                        'label' => $this->l('Group access'),
                        'name' => 'groupBox',
                        'values' => $groups,
                        'required' => true,
                        'col' => '6',
                        'hint' => $this->l('This form will only be available to the customers who are in the selected groups')
                    )
                )
            );
            
            $form_group_ids = $obj->getFormGroups();
            $all_groups_selected = (!Validate::isLoadedObject($obj) && !Tools::getIsset('submitAddfm_formAndStay'));
            $html_groups = Tools::getValue('groupBox');
            
            foreach ($groups as $group) {
                $this->fields_value['groupBox_'.$group['id_group']] =
                    Tools::getValue(
                        'groupBox_'.$group['id_group'],
                        ($all_groups_selected || (is_array($html_groups) && in_array($group['id_group'], $html_groups)))
                    ) || in_array($group['id_group'], $form_group_ids);
            }
        }

        if (Tools::getIsset('input')) {
            $this->context->smarty->assign('post_fields', Tools::jsonEncode(Tools::getValue('input')));
        } elseif (Validate::isLoadedObject($obj) && $form_data = $obj->getFormData()) {
            $this->context->smarty->assign('post_fields', Tools::jsonEncode($form_data));
        }

        $this->context->smarty->assign(array(
            'languages'          => Tools::jsonEncode(Language::getLanguages()),
            'default_language'   => $this->default_form_language,
            'current_language'   => $this->context->language->id,
            'form_products'      => $this->getFormProducts($obj),
            'missing_templates'  => $this->module->collectMissingEmailTemplates(),
            'validation_methods' => $this->module->validation_methods,
            'captcha'            => array(
                'status' => Tools::getValue('captcha', $obj->captcha),
                'count' => array(
                    'min' => 5, // $min_length in class FormMakerCaptcha
                    'max' => 12 // $max_length in class FormMakerCaptcha
                ),
                'type' => array(
                    // $flag Output type (NUMERIC, ALPHANUMERIC, NO_NUMERIC, RANDOM) in Tools::
                    'NUMERIC' => $this->l('Only numbers'),
                    'NO_NUMERIC' => $this->l('Uppercase letters'),
                    // 'RANDOM' => $this->l('Random characters'),
                    'ALPHANUMERIC' => $this->l('Letters and numbers'),
                ),
                'FM_CAPTCHA_NUMBER_CHAR' => Configuration::getGlobalValue('FM_CAPTCHA_NUMBER_CHAR'),
                'FM_CAPTCHA_TYPE' => Configuration::getGlobalValue('FM_CAPTCHA_TYPE'),
            ),
            'preview'            => Validate::isLoadedObject($obj) ? $this->context->link->getModuleLink(
                $this->module->name,
                'form',
                array(
                    'id_form' => $obj->id,
                    'rewrite' => $obj->link_rewrite[$this->context->language->id],
                    'adtoken' => Tools::getAdminToken(
                        'AdminFormSettings'
                        .(int)Tab::getIdFromClassName('AdminFormSettings').(int)$this->context->employee->id
                    ),
                    'id_employee' => (int)$this->context->employee->id
                )
            ) : false,
        ));

        $this->fields_value['form_products'] = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/hook/formproducts.tpl'
        );
        $this->fields_value['form_contents'] = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/hook/formbuilder.tpl'
        );
        $this->fields_value['captcha'] = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/hook/captcha.tpl'
        );
        $this->fields_value['form_preview'] = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/hook/formpreview.tpl'
        );

        $this->fields_value['override_contact_form'] = ((int)$obj->id == (int)Configuration::get('FM_CONTACT_FORM') ? 1 : 0);

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association:'),
                'name' => 'checkBoxShopAsso',
            );
        }

        $this->fields_form['submit'] = array(
            'id' => 'submitAddfm_formAndStay',
            'title' => $this->l('Save')
        );

        return parent::renderForm();
    }

    protected function updateAssoShop($id_object)
    {
        $assos_shop = array();

        if (Shop::isFeatureActive()) {
            $assos_shop = Tools::getValue('checkBoxShopAsso_fm_form');
        } else {
            $assos_shop[$this->context->shop->id] = $this->context->shop->id;
        }

        if (empty($assos_shop)) {
            $this->errors[] = Tools::displayError('Select at least one Shop association!');
            return false;
        }

        Db::getInstance()->delete($this->table.'_shop', '`'.$this->identifier.'` = '.(int)$id_object);

        foreach (array_keys($assos_shop) as $id_shop) {
            Db::getInstance()->insert('fm_form_shop', array(
                'id_fm_form' => (int)$id_object,
                'id_shop' => (int)$id_shop,
            ));
        }

        return true;
    }
    
    public function postProcess()
    {
        $this->tabAccess = Profile::getProfileAccess(
            $this->context->employee->id_profile,
            Tab::getIdFromClassName('AdminFormSettings')
        );
        if (Tools::getValue('FM_CAPTCHA_NUMBER_CHAR') !== false && Tools::getValue('FM_CAPTCHA_NUMBER_CHAR') !== 0) {
            Configuration::updateGlobalValue('FM_CAPTCHA_NUMBER_CHAR', Tools::getValue('FM_CAPTCHA_NUMBER_CHAR'));
        }
        if (Tools::getValue('FM_CAPTCHA_TYPE') !== false && Tools::getValue('FM_CAPTCHA_TYPE') !== 0) {
            Configuration::updateGlobalValue('FM_CAPTCHA_TYPE', Tools::getValue('FM_CAPTCHA_TYPE'));
        }
        if (Tools::getValue('setSelectedForm') !== false) {
            Configuration::updateGlobalValue('FM_FORM_ASSIGNED_TO_ALL', (int)Tools::getValue('setSelectedForm'));
            
            die(Tools::jsonEncode(array('id_fm_form' => Configuration::getGlobalValue('FM_FORM_ASSIGNED_TO_ALL'))));
        } elseif (Tools::getIsset('duplicate'.$this->table)) {
            if ($this->tabAccess['edit'] === '1') {
                $id = (int)Tools::getValue('id_'.$this->table);
                $obj = new FormMakerForm($id);
                
                if (!Validate::isLoadedObject($obj)) {
                    $this->errors[] = Tools::displayError('Unable to load this Form.');
                } else {
                    if ($new_obj = $obj->duplicateObject()) {
                        Tools::redirectAdmin(
                            self::$currentIndex.'&id_'.$this->table.'='.$new_obj->id.'&conf=4&token='.$this->token
                        );
                    } else {
                        $this->errors[] = Tools::displayError('Unable to clone this Form.');
                    }
                }
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        } else {
            parent::postProcess();
        }
    }

    public function getInputTranslation($input_name)
    {
        $inputs = array(
            'htmlBlock'     => $this->l('html block'),
            'textInput'     => $this->l('text input'),
            'passwordInput' => $this->l('password input'),
            'dateInput'     => $this->l('date input'),
            'colorInput'    => $this->l('color input'),
            'fileInput'     => $this->l('file input'),
            'textareaInput' => $this->l('textarea input'),
            'selectInput'   => $this->l('select input'),
            'radioInput'    => $this->l('radio input'),
            'checkboxInput' => $this->l('checkbox input')
        );

        return strtr($input_name, $inputs);
    }

    private function getFormProducts($obj)
    {
        $post_products = Tools::getValue('inputFormProducts', null);
        $post_names = Tools::getValue('nameFormProducts');
        $obj_products = Validate::isLoadedObject($obj) ? $obj->getFormProducts($this->context->language->id) : array();

        if (Tools::isEmpty($post_products)) {
            return $obj_products;
        }

        $post_products = explode('-', $post_products);
        $post_names = explode('¤', $post_names);
        $r = array();

        foreach ($post_products as $k => $post_product) {
            if (Validate::isUnsignedId($post_product)) {
                array_push($r, array(
                    'id_product' => (int)$post_product,
                    'name' => $post_names[$k],
                    'reference' => null
                ));
            }
        }

        return $r;
    }

    private function getLanguageFields()
    {
        $r = array();

        foreach ($_POST as $k => $v) {
            if (self::stringStartsWith($k, 'fmaker_translate')) {
                preg_match('/fmaker_translate_([A-z]*)_(element_[0-9]*)[_]([0-9]*)[_]?([0-9]*)?[_]?([0-9]*)?/', $k, $m);

                // Element Value
                if (isset($m[5]) && Validate::isUnsignedId($m[5])) {
                    $field_name   = $m[1];
                    $element_name = $m[2];
                    $value_id     = (int)$m[3];
                    $value_db_id  = (int)$m[4];
                    $language_id  = (int)$m[5];
                } else {
                    $field_name   = $m[1];
                    $element_name = $m[2];
                    $language_id  = (int)$m[3];
                    $value_id     = false;
                    $value_db_id  = false;
                }

                if (!array_key_exists($element_name, $r)) {
                    $r[$element_name] = array();
                }

                if (!array_key_exists($field_name, $r[$element_name])) {
                    $r[$element_name][$field_name] = array();
                }

                if ($value_id !== false) {
                    if (!array_key_exists('value_'.$value_id, $r[$element_name][$field_name])) {
                        $r[$element_name][$field_name]['value_'.$value_id] = array(
                            'id' => (int)$value_db_id,
                            'name' => array()
                        );
                    }

                    $r[$element_name][$field_name]['value_'.$value_id]['name'][$language_id] = $v;
                } else {
                    $r[$element_name][$field_name][$language_id] = $v;
                }
            }
        }

        return count($r) ? $r : false;
    }

    private static function stringStartsWith($h, $n)
    {
        return $n === '' || strrpos($h, $n, -Tools::strlen($h)) !== false;
    }

    private static function getIndexedLanguageList()
    {
        $languages = Language::getLanguages();
        $indexed = array();

        foreach ($languages as $language) {
            $indexed[$language['id_lang']] = $language;
        }

        return $indexed;
    }

    public function validateRules($class_name = false)
    {
        parent::validateRules($class_name);

        $languages = self::getIndexedLanguageList();
        $receivers = Tools::getValue('receivers');

        if (Tools::isEmpty($receivers)) {
            $this->errors['receivers'] = $this->l('"Receivers" field is required');
        } else {
            foreach (explode(',', $receivers) as $email) {
                if (!Validate::isEmail($email)) {
                    $this->errors['receivers'] = sprintf($this->l('"%s" is not a valid email address'), $email);
                }
            }
        }

        $form_fields = Tools::getValue('input');

        $lang_fields = $this->getLanguageFields();

        if ($lang_fields && is_array($form_fields)) {
            $form_fields = array_merge_recursive($form_fields, $lang_fields);
        } elseif ($lang_fields) {
            $form_fields = $lang_fields;
        }

        if (!is_array($form_fields)) {
            $this->errors['input'] = $this->l('The form can not be empty');
        } else {
            $field_counter = 1;

            foreach ($form_fields as &$data) {
                if (isset($data['deleted']) && $data['deleted']) {
                    continue;
                }

                $data['required'] = isset($data['required']) && (int)$data['required'] == 1;

                if ($data['type'] != 'htmlBlock') {
                    if (Tools::isEmpty($data['label'])) {
                        $this->errors['input'] = sprintf(
                            $this->l('The %s label can not be empty in form field #%d'),
                            $this->getInputTranslation($data['type']),
                            $field_counter
                        );
                    } else {
                        if (Tools::isEmpty($data['label'][Configuration::get('PS_LANG_DEFAULT')])) {
                            $this->errors['input'] = sprintf(
                                $this->l('The %s label is required at least for %s'),
                                $this->getInputTranslation($data['type']),
                                $languages[Configuration::get('PS_LANG_DEFAULT')]['name']
                            );
                        } else {
                            foreach ($data['label'] as &$label) {
                                if (Tools::isEmpty($label)) {
                                    $label = $data['label'][Configuration::get('PS_LANG_DEFAULT')];
                                }
                            }
                        }
                    }
                }
                
                if (!Tools::isEmpty($data['description'])) {
                    foreach ($data['description'] as &$description) {
                        if (Tools::isEmpty($description)
                            && !Tools::isEmpty($data['description'][Configuration::get('PS_LANG_DEFAULT')])) {
                            $description = $data['description'][Configuration::get('PS_LANG_DEFAULT')];
                        }
                    }
                }

                if (in_array($data['type'], array('selectInput', 'radioInput', 'checkboxInput'))) {
                    if (!isset($data['values']) || !is_array($data['values']) || !count($data['values'])) {
                        $this->errors['input'] = sprintf(
                            $this->l('The %s field must contain at least one value in form field #%d'),
                            $this->getInputTranslation($data['type']),
                            $field_counter
                        );
                    } else {
                        foreach ($data['values'] as &$value) {
                            if (!isset($value['name'])
                                || !is_array($value['name'])
                                || !isset($value['name'][Configuration::get('PS_LANG_DEFAULT')])
                                || Tools::isEmpty($value['name'][Configuration::get('PS_LANG_DEFAULT')])) {
                                $this->errors['input'] = sprintf(
                                    $this->l('The %s field\'s value is required at least in %s'),
                                    $this->getInputTranslation($data['type']),
                                    $languages[Configuration::get('PS_LANG_DEFAULT')]['name']
                                );
                            } else {
                                foreach ($value['name'] as &$value_name) {
                                    if (Tools::isEmpty($value_name)) {
                                        $value_name = $value[Configuration::get('PS_LANG_DEFAULT')];
                                    }
                                }
                            }
                        }
                    }
                }

                $field_counter++;
            }
        }

        $this->validateFormProducts(
            Tools::getValue('id_fm_form'),
            Tools::getValue('inputFormProducts', null),
            Tools::getValue('nameFormProducts')
        );

        $_POST['input'] = $form_fields;
    }

    private function validateFormProducts($id_form, $form_product_ids, $form_product_names)
    {
        if (Tools::isEmpty($form_product_ids)) {
            return true;
        }

        $form_product_ids = explode('-', $form_product_ids);
        $form_product_names = explode('¤', $form_product_names);

        foreach ($form_product_ids as $k => $form_product) {
            if (Validate::isUnsignedId($form_product)
                && ! FormMakerForm::canBeAssociatedWithProduct($id_form, (int)$form_product)) {
                $this->errors['input'] = sprintf(
                    $this->l('You can\'t associate "%s" product with this form, because it\'s already associated with another form.'),
                    $form_product_names[$k]
                );
            }
        }
    }

    public function getFormURL($name, $form)
    {
        $link = $this->context->link->getModuleLink(
            $this->module->name,
            'form',
            array('id_form' => $form['id_fm_form'], 'rewrite' => $form['link_rewrite'])
        );
        return '<a href="'.$link.'" target="_blank">'.$link.'</a>';
    }
    
    public function getAssignToAll($name, $form)
    {
        $this->context->smarty->assign(array(
            'id_fm_form' => $form['id_fm_form'],
            'selected_form' => $this->form_assigned_to_all
        ));
        
        return $this->module->display($this->module->getLocalPath(), 'assign_to_all.tpl');
    }
}
