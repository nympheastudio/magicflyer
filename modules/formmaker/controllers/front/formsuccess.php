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

require_once(dirname(__FILE__).'/../../classes/FormMakerForm.php');
require_once(dirname(__FILE__).'/../../classes/FormMakerElement.php');
require_once(dirname(__FILE__).'/../../classes/FormMakerElementValue.php');

class FormMakerFormSuccessModuleFrontController extends ModuleFrontController
{
    public $form = false;
    public $form_data = false;

    public function init()
    {
        parent::init();

        $preview_token = Tools::getAdminToken(
            'AdminFormSettings'.(int)Tab::getIdFromClassName('AdminFormSettings').(int)Tools::getValue('id_employee')
        );

        if (!Validate::isLoadedObject(
            $form = new FormMakerForm((int)Tools::getValue('id_form'), $this->context->language->id)
        ) || (!$form->active && Tools::getValue('adtoken') != $preview_token)) {
                Tools::redirect('index.php');
        }

        if (Shop::isFeatureActive()) {
            if (!$form->isAssociatedToShop($this->context->shop->id)) {
                Tools::redirect('index.php');
            }
        }

        $this->form = $form;

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

        if ($this->form) {
            $this->context->smarty->assign(array(
                'form'      => $this->form,
                'base_dir'  => _PS_BASE_URL_.__PS_BASE_URI__,
            ));

            if (version_compare('1.7.0.0', _PS_VERSION_, '>')) {
                $this->setTemplate('form_success.tpl');
            } else {
                $this->setTemplate('module:formmaker/views/templates/front/t17/form_success.tpl');
            }
        }
    }
}
