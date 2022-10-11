<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2016 silbersaiten
 * @version   1.3.3
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

class ContactController extends ContactControllerCore
{

	/*
	* module: formmaker
	* date: 2017-10-11 12:00:10
	* version: 1.3.11
	*/
    public function initContent()
    {
        if (Module::isInstalled('formmaker')
            && Module::isEnabled('formmaker')
            && Configuration::get('FM_CONTACT_FORM')
            && version_compare('1.7.0.0', _PS_VERSION_, '>')) {

            $module = Module::getInstanceByName('formmaker');
            $form_contact = (int)Configuration::get('FM_CONTACT_FORM');

            $form = new FormMakerForm($form_contact, $this->context->language->id);
            $form_data = $form->getFormData($this->context->language->id);
            $this->process();

            if (!Validate::isLoadedObject($form)
                || !$form->active
                || !$form_data
                || !$form->checkCustomerAccess($this->context->customer)
                || (Shop::isFeatureActive() && !$form->isAssociatedToShop($this->context->shop->id))) {
                return parent::initContent();
            }

            if (!isset($this->context->cart)) {
                $this->context->cart = new Cart();
            }

            if (!$this->useMobileTheme()) {
                $this->context->smarty->assign(array(
                    'HOOK_HEADER'       => Hook::exec('displayHeader'),
                    'HOOK_TOP'          => Hook::exec('displayTop'),
                    'HOOK_LEFT_COLUMN'  => ($this->display_column_left  ? Hook::exec('displayLeftColumn') : ''),
                    'HOOK_RIGHT_COLUMN' => ($this->display_column_right ? Hook::exec('displayRightColumn', array('cart' => $this->context->cart)) : ''),
                ));
            } else {
                $this->context->smarty->assign('HOOK_MOBILE_HEADER', Hook::exec('displayMobileHeader'));
            }

            

            if ($form && $form_data) {
                $this->context->smarty->assign(array(
                    'form'      => $form,
                    'form_data' => $form_data,
                    'captcha_path' => $form->captcha ? $module->getPathUri().'captcha.php' : false,
                    'form_template' => _PS_MODULE_DIR_.$module->name.'/views/templates/front/form.tpl'
                ));

                if (version_compare('1.7.0.0', _PS_VERSION_, '>')) {
                    $this->setTemplate(_PS_MODULE_DIR_.$module->name.'/views/templates/front/form_wrapper.tpl');
                } else {
                    $this->setTemplate('module:formmaker/views/templates/front/t17/form_wrapper.tpl');
                }
            }
        } else {
            parent::initContent();
        }
    }
}
