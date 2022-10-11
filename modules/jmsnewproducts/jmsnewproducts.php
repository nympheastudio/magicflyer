<?php
/**
* 2007-2014 PrestaShop
*
* Jms New Products
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2014 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

if (!defined('_PS_VERSION_'))
	exit;

class JmsNewProducts extends Module
{
	protected static $cache_jms_newproducts;

	public function __construct()
	{
		$this->name = 'jmsnewproducts';
		$this->tab = 'front_office_features';
		$this->version = '1.1.0';
		$this->author = 'Joommasters';
		$this->need_instance = 0;

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Jms New products');
		$this->description = $this->l('Displays a block featuring your store\'s newest products.');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}

	public function install()
	{
		$success = (parent::install()
			&& $this->registerHook('header')
			&& $this->registerHook('addproduct')
			&& $this->registerHook('updateproduct')
			&& $this->registerHook('deleteproduct')
			&& Configuration::updateValue('JMS_NEWPRODUCTS_NBR', 12)
		);

		$this->_clearCache('*');

		return $success;
	}

	public function uninstall()
	{
		$this->_clearCache('*');

		return parent::uninstall();
	}

	public function getContent()
	{
		$output = '';
		if (Tools::isSubmit('submitJmsNewProducts'))
		{
			if (!($productNbr = Tools::getValue('JMS_NEWPRODUCTS_NBR')) || empty($productNbr))
				$output .= $this->displayError($this->l('Please complete the "products to display" field.'));
			elseif ((int)($productNbr) == 0)
				$output .= $this->displayError($this->l('Invalid number.'));
			else
			{
				Configuration::updateValue('PS_NB_DAYS_NEW_PRODUCT', (int)(Tools::getValue('PS_NB_DAYS_NEW_PRODUCT')));
				Configuration::updateValue('PS_JMS_NEWPRODUCTS_DISPLAY', (int)(Tools::getValue('PS_JMS_NEWPRODUCTS_DISPLAY')));
				Configuration::updateValue('JMS_NEWPRODUCTS_NBR', (int)($productNbr));
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		return $output.$this->renderForm();
	}

	
	public function hookdisplayHome() 
	{	
		$new_products = Product::getNewProducts((int)$this->context->language->id, 0, (int)Configuration::get('JMS_NEWPRODUCTS_NBR'));			
		$this->smarty->assign(array(
				'new_products' => $new_products,
				'mediumSize' => Image::getSize(ImageType::getFormatedName('medium')),
				'homeSize' => Image::getSize(ImageType::getFormatedName('home'))
		));
		

		return $this->display(__FILE__, 'jmsnewproducts.tpl', $this->getCacheId('jmsnewproducts'));
	}

	public function hookHeader()
	{
		if (isset($this->context->controller->php_self) && $this->context->controller->php_self == 'index')
			$this->context->controller->addCSS(_THEME_CSS_DIR_.'product_list.css');

		$this->context->controller->addCSS($this->_path.'views/css/style.css', 'all');
	}

	public function hookAddProduct()
	{
		$this->_clearCache('*');
	}

	public function hookUpdateProduct()
	{
		$this->_clearCache('*');
	}

	public function hookDeleteProduct()
	{
		$this->_clearCache('*');
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
						'label' => $this->l('Products to display'),
						'name' => 'JMS_NEWPRODUCTS_NBR',
						'class' => 'fixed-width-xs',
						'desc' => $this->l('Define the number of products to be displayed in this block.')
					),
					array(
						'type'  => 'text',
						'label' => $this->l('Number of days for which the product is considered \'new\''),
						'name'  => 'PS_NB_DAYS_NEW_PRODUCT',
						'class' => 'fixed-width-xs',
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Always display this block'),
						'name' => 'PS_JMS_NEWPRODUCTS_DISPLAY',
						'desc' => $this->l('Show the block even if no new products are available.'),
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
					)
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
		$helper->submit_action = 'submitJmsNewProducts';
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
			'PS_NB_DAYS_NEW_PRODUCT' => Tools::getValue('PS_NB_DAYS_NEW_PRODUCT', Configuration::get('PS_NB_DAYS_NEW_PRODUCT')),
			'PS_JMS_NEWPRODUCTS_DISPLAY' => Tools::getValue('PS_JMS_NEWPRODUCTS_DISPLAY', Configuration::get('PS_JMS_NEWPRODUCTS_DISPLAY')),
			'JMS_NEWPRODUCTS_NBR' => Tools::getValue('JMS_NEWPRODUCTS_NBR', Configuration::get('JMS_NEWPRODUCTS_NBR')),
		);
	}
}
