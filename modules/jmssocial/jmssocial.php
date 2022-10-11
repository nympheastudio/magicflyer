<?php
/**
* 2007-2014 PrestaShop
*
* Jms Social Networking block
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2014 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;
	
class JmsSocial extends Module
{
	public function __construct()
	{
		$this->name = 'jmssocial';
		$this->tab = 'front_office_features';
		$this->version = '1.1.0';
		$this->author = 'Joommasters';

		$this->bootstrap = true;
		parent::__construct();	

		$this->displayName = $this->l('Jms Social networking block');
		$this->description = $this->l('Allows you to add information about your brand\'s social networking accounts.');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}
	
	public function install()
	{
		return (parent::install() && Configuration::updateValue('JMS_FACEBOOK', '#') && Configuration::updateValue('JMS_TWITTER', '#') && Configuration::updateValue('JMS_LINKEDIN', '#') && Configuration::updateValue('JMS_YOUTUBE', '#') && Configuration::updateValue('JMS_GOOGLE_PLUS', '#') && Configuration::updateValue('JMS_PINTEREST', '#') && $this->registerHook('header'));
	}
	
	public function uninstall()
	{
		//Delete configuration
		return (Configuration::deleteByName('JMS_FACEBOOK') && Configuration::deleteByName('JMS_TWITTER') && Configuration::deleteByName('JMS_LINKEDIN') && Configuration::deleteByName('JMS_YOUTUBE') && Configuration::deleteByName('JMS_GOOGLE_PLUS') && Configuration::deleteByName('JMS_PINTEREST') && parent::uninstall());
	}
	
	public function getContent()
	{
		// If we try to update the settings
		$output = '';
		if (Tools::isSubmit('submitModule'))
		{	
			Configuration::updateValue('JMS_FACEBOOK', Tools::getValue('jms_facebook', ''));
			Configuration::updateValue('JMS_TWITTER', Tools::getValue('jms_twitter', ''));
			Configuration::updateValue('JMS_LINKEDIN', Tools::getValue('jms_linkedin', ''));
			Configuration::updateValue('JMS_YOUTUBE', Tools::getValue('jms_youtube', ''));
			Configuration::updateValue('JMS_GOOGLE_PLUS', Tools::getValue('jms_google_plus', ''));
			Configuration::updateValue('JMS_PINTEREST', Tools::getValue('jms_pinterest', ''));
			$this->_clearCache('jmssocial.tpl');
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&conf=4&module_name='.$this->name);
		}
		
		return $output.$this->renderForm();
	}
	
		
	public function hookDisplayTop()
	{		
		if (!$this->isCached('jmssocial.tpl', $this->getCacheId()))
			$this->smarty->assign(array(
				'facebook_url' => Configuration::get('JMS_FACEBOOK'),
				'twitter_url' => Configuration::get('JMS_TWITTER'),
				'linkedin_url' => Configuration::get('JMS_LINKEDIN'),
				'youtube_url' => Configuration::get('JMS_YOUTUBE'),
				'google_plus_url' => Configuration::get('JMS_GOOGLE_PLUS'),
				'pinterest_url' => Configuration::get('JMS_PINTEREST'),
			));

		return $this->display(__FILE__, 'jmssocial.tpl', $this->getCacheId());
	}	
	public function hookDisplayTopColumn()
	{
		return $this->hookDisplayTop();
	}
	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Facebook URL'),
						'name' => 'jms_facebook',
						'desc' => $this->l('Your Facebook fan page.'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Twitter URL'),
						'name' => 'jms_twitter',
						'desc' => $this->l('Your official Twitter accounts.'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('LinkedIn URL'),
						'name' => 'jms_linkedin',
						'desc' => $this->l('Your Linkedin Page.'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('YouTube URL'),
						'name' => 'jms_youtube',
						'desc' => $this->l('Your official YouTube account.'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Google Plus URL:'),
						'name' => 'jms_google_plus',
						'desc' => $this->l('You official Google Plus page.'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Pinterest URL:'),
						'name' => 'jms_pinterest',
						'desc' => $this->l('Your official Pinterest account.'),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);
		
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitModule';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}
	
	public function getConfigFieldsValues()
	{
		return array(
			'jms_facebook' => Tools::getValue('jms_facebook', Configuration::get('JMS_FACEBOOK')),
			'jms_twitter' => Tools::getValue('jms_twitter', Configuration::get('JMS_TWITTER')),
			'jms_linkedin' => Tools::getValue('jms_linkedin', Configuration::get('JMS_LINKEDIN')),
			'jms_youtube' => Tools::getValue('jms_youtube', Configuration::get('JMS_YOUTUBE')),
			'jms_google_plus' => Tools::getValue('jms_google_plus', Configuration::get('JMS_GOOGLE_PLUS')),
			'jms_pinterest' => Tools::getValue('jms_pinterest', Configuration::get('JMS_PINTEREST')),
		);
	}

}
