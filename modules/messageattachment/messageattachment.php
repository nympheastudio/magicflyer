<?php
	/**
	 * 2007-2016 PrestaShop
	 *
	 * NOTICE OF LICENSE
	 *
	 * This source file is subject to the Academic Free License (AFL 3.0)
	 * that is bundled with this package in the file LICENSE.txt.
	 * It is also available through the world-wide-web at this URL:
	 * http://opensource.org/licenses/afl-3.0.php
	 * If you did not receive a copy of the license and are unable to
	 * obtain it through the world-wide-web, please send an email
	 * to license@prestashop.com so we can send you a copy immediately.
	 *
	 * DISCLAIMER
	 *
	 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
	 * versions in the future. If you wish to customize PrestaShop for your
	 * needs please refer to http://www.prestashop.com for more information.
	 *
	 * @author    PrestaShop SA <contact@prestashop.com>
	 * @copyright 2007-2016 PrestaShop SA
	 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
	 *  International Registered Trademark & Property of PrestaShop SA
	 */

	if (!defined('_PS_VERSION_'))
	{
		exit;
	}

	class Messageattachment extends Module
	{
		protected $config_form = false;

		public function __construct()
		{
			$this->name = 'messageattachment';
			$this->tab = 'administration';
			$this->version = '1.4.4';
			$this->author = 'RM RM';
			$this->need_instance = 0;
			$this->module_key ="f07f3152334238008f8cb814ed3101cb";

			/**
			 * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
			 *  extra_right
			 */
			$this->bootstrap = true;

			parent::__construct();

			$this->displayName = $this->l('Message Attachment');
			$this->description = $this->l('Send orders messages with attachment : images, pdf, SVG or other to the customers');
		}

		/**
		 * Don't forget to create update methods if needed:
		 * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
		 */
		public function install()
		{
			Configuration::updateValue('messageattachment_LIVE_MODE', false);
			Configuration::updateValue('messageattachment_VIEW', 1);
			Configuration::updateValue('messageattachment_POSITION', 1);

			include(dirname(__FILE__).'/sql/install.php');


			$this->installTpl();
			$this->installOverrideOrder();
			$this->installOverrideMail();


			@copy(dirname(__FILE__).'/logo.png', _PS_UPLOAD_DIR_.'logoreplace.png');

			if (file_exists(dirname(__FILE__).'/../../cache/class_index.php'))
			{
				@unlink(dirname(__FILE__).'/../../cache/class_index.php');
			}
				if (file_exists(dirname(__FILE__).'/../../app/cache/dev/class_index.php'))
				{
					@unlink(dirname(__FILE__).'/../../app/cache/dev/class_index.php');
				}
				if (file_exists(dirname(__FILE__).'/../../app/cache/prod/class_index.php'))
				{
					@unlink(dirname(__FILE__).'/../../app/cache/prod/class_index.php');
				}

			return parent::install() &&  $this->registerHook('displayBackOfficeFooter') &&  $this->registerHook('displayAdminOrderContentOrder');
		}

		private function geturlAdminOrders()
		{
			$f=dirname(__FILE__).'/../../override/controllers/admin/AdminOrdersController.php';
			return $f;
		}

		private function geturlAdminMail()
		{
			$f=dirname(__FILE__).'/../../override/classes/Mail.php';
			return $f;
		}

		public function uninstall()
		{
			Configuration::deleteByName('messageattachment_LIVE_MODE');

			$f=$this->geturlAdminOrders();
			if (file_exists($f))
			{
				@unlink($f);
			}
			$f=$this->geturlAdminMail();
			if (file_exists($f))
			{
				@unlink($f);
			}

			include(dirname(__FILE__).'/sql/uninstall.php');


			return parent::uninstall();
		}

		/**
		 * Load the configuration form
		 */
		public function getContent()
		{
			/**
			 * If values have been submitted in the form, process.
			 */

			if (((bool)Tools::isSubmit('submitmessageattachmentModule')) == true)
			{
				$this->postProcess();
			}

			$this->context->smarty->assign('module_dir', $this->_path);
			$this->context->smarty->assign(array('translate' => $this->get_translation()));
			$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

			return $output;
		}

		public static function getFilesAttachement($id_order)
		{
			$list_attachment=array();
			$sql = 'SELECT * FROM `'._DB_PREFIX_.'order_attachment` WHERE `id_order`=\''.(int)$id_order.'\'';
			$tmp = Db::getInstance()->ExecuteS($sql);

			foreach ($tmp as $val)
			{
				if (file_exists(_PS_UPLOAD_DIR_.'/'.$val['name']))
				{
					$file_attachement = array();
					$file_attachement['content']=Tools::file_get_contents(_PS_UPLOAD_DIR_.'/'.$val['name']);
					$file_attachement['name'] = $val['name'];
					$file_attachement['mime'] = 'application/octet-stream';
					$list_attachment[] = $file_attachement;
				}
			}

			return $list_attachment;
		}

		public function hookdisplayAdminOrderContentOrder($params)
		{
		
			if (Tools::getValue('controller')=='AdminOrders' && Tools::getIsset('vieworder') && Tools::getIsset('id_order'))
			{
				$list_attachment=array();

				$sql = 'SELECT * FROM `'._DB_PREFIX_.'order_attachment` WHERE `id_order`=\''.(int)Tools::getValue('id_order').'\'';
	
				$tmp = Db::getInstance()->ExecuteS($sql);
		
				foreach ($tmp as $val)
				{
					if ($val['type']=='image/png' || $val['type']=='image/jpg' || $val['type']=='image/jpeg' || $val['type']=='image/gif' || $val['type']=='image/svg+xml')
					{
						$list_attachment[$val['id_attachment']] = ''.$val['name'];
					}
					else
					{
						$list_attachment[$val['id_attachment']] = 'logoreplace.png';
					}
				}
				$this->context->smarty->assign(array('messageattach'=>$this->get_translation(),'post_max_size'=>Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024,'url1'=>Context::getContext()->link->getAdminLink('AdminOrders').'&ajax=1&id_order='.(int)Tools::getValue('id_order').'&action=AddAttachment&time'.time(),'list_attachment_render'=>$list_attachment,'linkattach'=>Context::getContext()->link->getAdminLink('AdminOrders').'&ajax=1'));
				
				
				
				$attachment_uploader = new HelperUploader('attachment_file');
				$attachment_uploader->setMultiple(false)->setUseAjax(true)->setUrl(Context::getContext()->link->getAdminLink('AdminOrders').'&ajax=1&id_order='.(int)Tools::getValue('id_order').'&action=AddAttachment&time'.time())->setPostMaxSize((Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024))->setTemplate('attachment_ajax.tpl');
				return $attachment_uploader->render();
			}
		}
		public function installOverrideMail()
		{
			$f=$this->geturlAdminMail();
			$source=dirname(__FILE__).'/preinstall/Mail1.6.php';
			$source1=dirname(__FILE__).'/preinstall/Mail.php';
			$source2=dirname(__FILE__).'/preinstall/Mail1.6.0.11.php';
			$source3=dirname(__FILE__).'/preinstall/Mail1_7.2.php';
			
			if (file_exists($f))
			{
				@rename($f, $f.'.bak');
			}
			if (version_compare(_PS_VERSION_, '1.6.0.11', '='))
			{
				@copy($source2, $f);
			}
			else
			{
				if (version_compare(_PS_VERSION_, '1.6.0.0', '>') && version_compare(_PS_VERSION_, '1.6.1.6', '<'))
				{
					@copy($source, $f);
				}
				else
				{
					if (version_compare(_PS_VERSION_, '1.7.1.0', '>='))
					{
						@copy($source3, $f);
					}
					else
					{
					@copy($source1, $f);
					}
				}
			}
		}

		public function installOverrideOrder()
		{
			$f=$this->geturlAdminOrders();
			if (file_exists($f))
			{
				@rename($f, $f.'.bak');
				@copy(dirname(__FILE__).'/preinstall/AdminOrdersController.php',$f);

			}
			else
			{
				@copy(dirname(__FILE__).'/preinstall/AdminOrdersController.php',$f);
			}
		}

		public function installTpl()
		{
			$f2=_PS_ADMIN_DIR_.'/themes/default/template/helpers/uploader/attachment_ajax.tpl';
			if (file_exists($f2))
			{
				if (file_exists($f2.'.bak'))
				{
					@unlink($f2.'.bak');
				}
				@rename($f2, $f2.'.bak');
			}
			@copy(dirname(__FILE__).'/views/templates/admin/attachment_ajax.tpl',$f2);


			$f2=_PS_ADMIN_DIR_.'/themes/default/template/helpers/uploader/attachment_ajax_sav.tpl';
			if (file_exists($f2))
			{
				if (file_exists($f2.'.bak'))
				{
					@unlink($f2.'.bak');
				}
				@rename($f2, $f2.'.bak');
			}
			@copy(dirname(__FILE__).'/views/templates/admin/attachment_ajax_sav.tpl',$f2);
		}

		/**
		 * Create the form that will be displayed in the configuration of your module.
		 */


		protected function renderForm()
		{
			if (version_compare(_PS_VERSION_, '1.6', '>='))
			{
				$fields_form = array();

				$helper = new HelperForm();
				$helper->show_toolbar = false;
				$helper->table = $this->table;
				$helper->module = $this;
				$helper->default_form_language = $this->context->language->id;
				$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

				$helper->identifier = $this->identifier;
				$helper->submit_action = 'submit_BUTTONPRODUCT_Module';
				$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
				$helper->token = Tools::getAdminTokenLite('AdminModules');

				$helper->tpl_vars = array('fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
					'languages' => $this->context->controller->getLanguages(), 'id_language' => $this->context->language->id,);

				return $helper->generateForm(array($fields_form));
			}
			else
			{
				$output = '	';
				return $output;
			}
		}

		/**
		 * Create the structure of your form.
		 */
		protected function getConfigForm()
		{
			return array('form' => array('legend' => array('title' => $this->l('Settings'), 'icon' => 'icon-cogs',), 'input' => array(array('type' => 'switch', 'label' => $this->l('Live mode'), 'name' => 'BOOKING_LIVE_MODE', 'is_bool' => true, 'desc' => $this->l('Use this module in live mode'), 'values' => array(array('id' => 'active_on', 'value' => true, 'label' => $this->l('Enabled')), array('id' => 'active_off', 'value' => false, 'label' => $this->l('Disabled'))),), array('col' => 3, 'type' => 'text', 'prefix' => '<i class="icon icon-envelope"></i>', 'desc' => $this->l('Enter a valid email address'), 'name' => 'BOOKING_ACCOUNT_EMAIL', 'label' => $this->l('Email'),), array('type' => 'password', 'name' => 'BOOKING_ACCOUNT_PASSWORD', 'label' => $this->l('Password'),),), 'submit' => array('title' => $this->l('Save'),),),);
		}

		/**
		 * Set values for the inputs.
		 */
		protected function getConfigFormValues()
		{
			return array('messageattachment_POSITION' => Configuration::get('messageattachment_POSITION'), 'messageattachment_VIEW' => Configuration::get('BOOKING_VIEW'), 'messageattachment_LIVE_MODE' => Configuration::get('BOOKING_LIVE_MODE', true), 'messageattachment_ACCOUNT_EMAIL' => Configuration::get('messageattachment_ACCOUNT_EMAIL', 'contact@prestashop.com'), 'messageattachment_ACCOUNT_PASSWORD' => Configuration::get('messageattachment_ACCOUNT_PASSWORD', null),);
		}

		/**
		 * Save form data.
		 */
		protected function postProcess()
		{
			$form_values = $this->getConfigFormValues();

			foreach (array_keys($form_values) as $key)
			{
				Configuration::updateValue($key, Tools::getValue($key));
			}
		}

		public function HookdisplayBackOfficeFooter($params)
		{

			if (Tools::getValue('controller')=='AdminCustomerThreads' && Tools::getIsset('viewcustomer_thread'))
			{
				$list_attachment=array();

				$sql = 'SELECT * FROM `'._DB_PREFIX_.'order_attachment` WHERE `id_order`=\'7777'.(int)Tools::getValue('id_customer_thread').'\'';

				$tmp = Db::getInstance()->ExecuteS($sql);

				foreach ($tmp as $val)
				{
					if ($val['type']=='image/png' || $val['type']=='image/jpg' || $val['type']=='image/jpeg' || $val['type']=='image/gif' || $val['type']=='image/svg+xml')
					{
						$list_attachment[$val['id_attachment']] = ''.$val['name'];
					}
					else
					{
						$list_attachment[$val['id_attachment']] = 'logoreplace.png';
					}
				}

				$bo=new AdminControllerCore();
				$this->context->smarty->assign(array('local'=>__PS_BASE_URI__,'linkscript'=>__PS_BASE_URI__.$bo->admin_webpath.'/themes/'.$this->context->employee->bo_theme,'messageattach'=>$this->get_translation(),'post_max_size'=>Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024,'url1'=>Context::getContext()->link->getAdminLink('AdminOrders').'&ajax=1&id_order=7777'.(int)Tools::getValue('id_customer_thread').'&action=AddAttachment&time'.time(),'list_attachment_render'=>$list_attachment,'linkattach'=>Context::getContext()->link->getAdminLink('AdminOrders').'&ajax=1'));

				$attachment_uploader = new HelperUploader('attachment_file');
				$attachment_uploader->setMultiple(false)->setUseAjax(true)->setUrl(Context::getContext()->link->getAdminLink('AdminOrders').'&ajax=1&id_order=7777'.(int)Tools::getValue('id_customer_thread').'&action=AddAttachment&time'.time())->setPostMaxSize((Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024))->setTemplate('attachment_ajax_sav.tpl');
				return $attachment_uploader->render();
			}
		}

		private function get_translation()
		{

			$translate = array();
			$translate['ok']=$this->l('Upload successful');
			$translate['error']=$this->l('Only Format accepted :').' gif, png, jpg, tiff, pdf, xml, svg, ai, psd, zip';
			$translate['delete']=$this->l('Delete successful');
			return $translate;
		}
		

	}
