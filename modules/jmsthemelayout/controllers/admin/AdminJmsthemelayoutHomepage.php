<?php
/**
* 2007-2015 PrestaShop
*
* Jms Theme Layout
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'jmsthemelayout/class/JmsExecute.php');
include_once(_PS_MODULE_DIR_.'jmsthemelayout/class/JmsImportExport.php');
class AdminJmsthemelayoutHomepageController extends ModuleAdminControllerCore {
	public function __construct()
	{
		$this->name = 'jmsthemelayout';
		$this->tab = 'front_office_features';
		$this->bootstrap = true;
		$this->lang = true;
		$this->context = Context::getContext();
		$this->secure_key = Tools::encrypt($this->name);
		parent::__construct();
	}

	public function renderList()
	{
		$jms_exc = new JmsExecute();
		$this->_html = $this->headerHTML();
		/* Validate & process */

		if (Tools::isSubmit('submitCancelAddForm'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsthemelayoutHomepage', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_homepage='.Tools::getValue('id_homepage'));
		elseif (Tools::isSubmit('submitConf'))
		{
			Configuration::updateValue('JMSADV_HOMEPAGE', Tools::getValue('JMSADV_HOMEPAGE'));			
			$this->_html .= $this->renderListHomes();
		}
		elseif (Tools::isSubmit('addHome') || (Tools::isSubmit('edit_id_homepage') && $jms_exc->homeExists((int)Tools::getValue('id_homepage'))))
		{
			$this->_html .= $this->renderNavigation();
			$this->_html .= $this->renderAddHome();
		}
		elseif (Tools::isSubmit('submitHome') || Tools::isSubmit('delete_id_homepage') || (Tools::isSubmit('export_id_homepage') && $jms_exc->homeExists((int)Tools::getValue('export_id_homepage'))))
		{
			if ($this->_postValidation())
			{
				$this->_postProcess();				
				$this->_html .= $this->renderListHome();
			}
			else
			{
				$this->_html .= $this->renderNavigation();
				$this->_html .= $this->renderAddHome();
			}
		}			
		else
		{			
			$view = Tools::getValue('view', 'categories');
			if ($view == 'config')
				$this->_html .= $this->renderConfig();
			else
				$this->_html .= $this->renderListHome();
		}
		return $this->_html;
	}
	
	private function _postValidation()
	{
		$errors = array();
		$jms_exc = new JmsExecute();

		/* Validation for configuration */	
		if (Tools::isSubmit('submitHome'))
		{
			if (Tools::strlen(Tools::getValue('title')) > 255)
				$errors[] = $this->l('The title is too long.');
			if (Tools::strlen(Tools::getValue('title')) == 0)
				$errors[] = $this->l('The title is not set.');
		}				
		elseif (Tools::isSubmit('changeHomePageStatus')) 
		{
			if (!Validate::isInt(Tools::getValue('id_homepage')))
				$errors[] = $this->l('Invalid Home Page');
		}		
		elseif (Tools::isSubmit('delete_id_homepage'))
			if	((!Validate::isInt(Tools::getValue('delete_id_homepage')) || !$jms_exc->homeExists((int)Tools::getValue('delete_id_homepage'))))
				$errors[] = $this->l('Invalid id_homepage');		
		elseif (Tools::isSubmit('export_id_homepage'))
			if	((!Validate::isInt(Tools::getValue('export_id_homepage')) || !$jms_exc->homeExists((int)Tools::getValue('export_id_homepage'))))
				$errors[] = $this->l('Invalid id_homepage');		
		/* Display errors if needed */
		if (count($errors))
		{
			$this->_html .= Tools::displayError(implode('<br />', $errors));
			return false;
		}

		/* Returns if validation is ok */
		return true;
	}
	private function _postProcess()
	{
		$jms_exc = new JmsExecute();
		$errors = array();
		if (Tools::isSubmit('submitHome'))
		{
			if (Tools::getValue('id_homepage'))
				$id_homepage = Tools::getValue('id_homepage');
			else
				$id_homepage = null;
			
			$res = $jms_exc->addUpdateHome($id_homepage, Tools::getValue('title'), Tools::getValue('id_header'), Tools::getValue('id_homebody'), Tools::getValue('id_footer'), Tools::getValue('css_file'), Tools::getValue('font_url'));
			if ($res)
				$errors[] = $res;
		}
		elseif (Tools::isSubmit('delete_id_homepage'))
		{
			$res = $jms_exc->deleteHome((int)Tools::getValue('delete_id_homepage'));
			if (!$res)
				$this->_html .= Tools::displayError('Could not delete');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsthemelayoutHomepage', true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		}
		elseif (Tools::isSubmit('export_id_homepage'))
		{			
			$jms_importexport = new JmsImportExport();
			$res = $jms_importexport->exportHomepage(Tools::getValue('export_id_homepage'));			
		}				
		
		if (count($errors))
			$this->_html .= Tools::displayError(implode('<br />', $errors));
		elseif (Tools::isSubmit('submitHome') && Tools::getValue('id_homepage'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsthemelayoutHomepage', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		elseif (Tools::isSubmit('delete_id_homepage') && Tools::getValue('delete_id_homepage'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsthemelayoutHomepage', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		elseif (Tools::isSubmit('changeHomePageStatus') && Tools::getValue('id_homepage'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsthemelayoutHomepage', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);		
	}

	public function renderConfig()
	{
		$jms_exc = new JmsExecute();
		$homepages = $jms_exc->getHomes();
		$this->fields_form = array(
				'legend' => array(
					'title' => $this->l('Config'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'select',
						'label' => $this->l('Home Page Default '),
						'name' => 'JMSADV_HOMEPAGE',
						'desc' => $this->l('Select HomePage'),
						'options' => array('query' => $homepages,'id' => 'id_homepage','name' => 'title'
						)
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
					'name' => 'submitConf'
				)
		);

		$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_homepage');
		$this->fields_value = $this->getConfFieldsValues();
		return adminController::renderForm();
	}

	public function getConfFieldsValues()
	{
		return array(
			'JMSADV_HOMEPAGE' => Tools::getValue('JMSADV_HOMEPAGE', Configuration::get('JMSADV_HOMEPAGE'))
		);
	}

	public function renderPathway()
	{
		$tpl = $this->createTemplate('path.tpl');
		$tpl->assign(
			array(
				'view' => Tools::getValue('view'),
				'link' => $this->context->link
			)
		);
		return $tpl->fetch();
	}

	public function renderListHome()
	{
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');
		$jms_exc = new JmsExecute();
		$homepages = $jms_exc->getHomes();
		$tpl = $this->createTemplate('listHome.tpl');
		$tpl->assign( array(
			'link' => $this->context->link,
			'homepages' => $homepages,
		));
		return $tpl->fetch();
	}
	public function renderNavigation()
	{
		$html = '<div class="navigation">';
		$html .= '<a class="btn btn-default" href="'.AdminController::$currentIndex.
			'&configure='.$this->name.'
				&token='.Tools::getAdminTokenLite('AdminJmsthemelayoutHomepage').'" title="Back to Dashboard"><i class="icon-home"></i>Back to Dashboard</a>';
		$html .= '</div>';
		return $html;
	}
	public function renderAddHome()
	{
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');
		$jms_exc = new JmsExecute();
		$headers = $jms_exc->getProf('header');		
		$homebodys = $jms_exc->getProf('homebody');
		$footers = $jms_exc->getProf('footer');
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Homepage Informations'),
				'icon' => 'icon-cogs'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Title'),
					'name' => 'title',
					'class' => 'fixed-width-xl',
				),
				array(
					'type' => 'select',
					'label' => $this->l('Select Header'),
					'name' => 'id_header',
					'desc' => $this->l('Select Header for homepage.'),
					'options' => array('query' => $headers,'id' => 'id_prof','name' => 'title')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Select Footer'),
					'name' => 'id_homebody',
					'desc' => $this->l('Select Body for homepage.'),
					'options' => array('query' => $homebodys,'id' => 'id_prof','name' => 'title')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Select Footer'),
					'name' => 'id_footer',
					'desc' => $this->l('Select Footer for homepage.'),
					'options' => array('query' => $footers,'id' => 'id_prof','name' => 'title')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Css File'),
					'name' => 'css_file',
					'class' => 'fixed-width-xl',
				),
				array(
					'type' => 'text',
					'label' => $this->l('Font Url'),
					'name' => 'font_url',
					'class' => 'fixed-width-xl',
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'name' => 'submitHome'
			)
		);
		if (Tools::isSubmit('edit_id_homepage'))
			$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_homepage');
		$this->fields_value = $this->getHomeFieldsValues();

		return adminController::renderForm();
	}
	public function getHomeFieldsValues()
	{
		$jms_exc = new JmsExecute();
		return $jms_exc->getHomeFieldsValues((int)Tools::getValue('edit_id_homepage'));
	}

	public function headerHTML()
	{
		if (Tools::getValue('controller') != 'AdminJmsthemelayoutHomepage' && Tools::getValue('configure') != $this->name)
			return;
		$this->context->controller->addJqueryUI('ui.resizable');		
		$this->context->controller->addJqueryUI('ui.sortable');		
		/* Style & js for fieldset 'rows configuration' */
		$html = '<script type="text/javascript">
			$(function() {
				var $myhomepages = $(".homepage");
				
				$myhomepages.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {						
						var order = $(this).sortable("serialize") + "&action=updateHomesOrdering";												
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}	
				});
				$myhomepages.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});					
			});
		</script>';

		return $html;
	}
}
?>