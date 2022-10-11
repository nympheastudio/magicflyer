<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2016 silbersaiten
 * @version   1.3.0
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

require_once(dirname(__FILE__).'/../../classes/FormMakerForm.php');
require_once(dirname(__FILE__).'/../../classes/FormMakerElement.php');
require_once(dirname(__FILE__).'/../../classes/FormMakerElementValue.php');

class FormMakerFormModuleFrontController extends ModuleFrontController
{
    public $form = false;
    public $form_data = false;

    public function init()
    {
        parent::init();

        if (Tools::getValue('action') == 'upload') {
            die($this->module->uploadFile(
                Tools::getValue('form_id'),
                Tools::getValue('input_id'),
                Tools::getValue('input_name'),
                Tools::getValue('current_filename')
            ));
        } elseif (Tools::getValue('action') == 'submit') {
            die($this->module->submitForm(Tools::getValue('form'), Tools::getValue('pageparam')));
        } else {
            $preview_token = Tools::getAdminToken(
                'AdminFormSettings'
                .(int)Tab::getIdFromClassName('AdminFormSettings').(int)Tools::getValue('id_employee')
            );

            if (!Validate::isLoadedObject(
                    $form = new FormMakerForm((int)Tools::getValue('id_form'), $this->context->language->id)
                ) || $form->link_rewrite != Tools::getValue('rewrite')
                || (!$form->active && Tools::getValue('adtoken') != $preview_token)
                || !$form_data = $form->getFormData($this->context->language->id)) {
                    Tools::redirect('index.php');
            }

            if (Shop::isFeatureActive()) {
                if (!$form->isAssociatedToShop($this->context->shop->id)) {
                    Tools::redirect('index.php');
                }
            }
            
            if (!$form->checkCustomerAccess($this->context->customer)) {
                Tools::redirect('index.php');
            }

            $this->form = $form;
            $this->form_data = $form_data;
        }

        $metas = Meta::getHomeMetas($this->context->language->id, null);

        if (!Tools::isEmpty($this->form->meta_title)) {
            $metas['meta_title'] = $this->form->meta_title.' - '.$metas['meta_title'];
        }

        if (!Tools::isEmpty($this->form->meta_description)) {
            $metas['meta_description'] = $this->form->meta_description;
        }

        if (!Tools::isEmpty($this->form->meta_keywords)) {
            $metas['meta_keywords'] = $this->form->meta_keywords;
        }

        $this->context->smarty->assign($metas);
    }

    public function initContent()
    {
        parent::initContent();

        if ($this->form && $this->form_data) {
            $this->context->smarty->assign(array(
                'form'      => $this->form,
                'form_data' => $this->form_data,
                'captcha_path' => $this->form->captcha ? $this->module->getPathUri().'captcha.php' : false,
                'form_template' => _PS_MODULE_DIR_.$this->module->name.'/views/templates/front/form.tpl'
            ));

            if (version_compare('1.7.0.0', _PS_VERSION_, '>')) {
                $this->setTemplate('form_wrapper.tpl');
            } else {
                $this->setTemplate('module:formmaker/views/templates/front/t17/form_wrapper.tpl');
            }
        }
    }
}
