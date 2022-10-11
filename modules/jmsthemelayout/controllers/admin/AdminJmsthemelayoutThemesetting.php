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
include_once(_PS_MODULE_DIR_.'jmsthemelayout/params.php');
class AdminJmsthemelayoutThemesettingController extends ModuleAdminControllerCore {
	
	private $_themeskins = array();	
	private $_producthovers = array();	
	private $_productboxs = array();		
	public function __construct()
	{
		$this->name = 'jmsthemelayout';
		$this->tab = 'front_office_features';
		$this->bootstrap = true;
		$this->lang = true;
		$this->context = Context::getContext();
		$this->secure_key = Tools::encrypt($this->name);
		parent::__construct();
		if(_JMS_THEME_SKINS_)
			$this->_themeskins = explode(",", _JMS_THEME_SKINS_);
		if(_JMS_PRODUCT_HOVERS_) {
			$hover_strs = explode(",", _JMS_PRODUCT_HOVERS_);
			foreach($hover_strs as $hover_str ) {
				$_fields = explode(":", $hover_str);
				$this->_producthovers[$_fields[0]] = $_fields[1];				
			}		
		}	
		if(_JMS_PRODUCT_BOXS_)
			$this->_productboxs = explode(",", _JMS_PRODUCT_BOXS_);
	}
	
	public function renderList()
	{		
		$this->_html = $this->headerHTML();
		
		/* Validate & process */
		if (Tools::isSubmit('submitConfig'))
		{	
			if ($this->_postValidation())
			{
				$this->_postProcess();				
				$this->_html .= $this->renderForm();							
			} 
			else
				$this->_html .= $this->renderForm();
			
		}
		else
			$this->_html .= $this->renderForm();		
		return $this->_html;		
	}
	
	private function _postValidation()
	{	
		return true;
	}
	private function _postProcess()
	{
		$errors = array();
		/* Processes Slider */
		if (Tools::isSubmit('submitConfig'))
		{
			$res = Configuration::updateValue('JMSSETTING_SKIN', Tools::getValue('JMSSETTING_SKIN'));			
			$res &= Configuration::updateValue('JMSADV_HOMEPAGE', Tools::getValue('JMSADV_HOMEPAGE'));			
			$res &= Configuration::updateValue('JMSSETTING_PRODUCTHOVER', Tools::getValue('JMSSETTING_PRODUCTHOVER'));
			$res &= Configuration::updateValue('JMSSETTING_PRODUCTBOX', Tools::getValue('JMSSETTING_PRODUCTBOX'));	
			$res &= Configuration::updateValue('JMSSETTING_RTL', Tools::getValue('JMSSETTING_RTL'));	
			$res &= Configuration::updateValue('JMSSETTING_TOOLS', (int)(Tools::getValue('JMSSETTING_TOOLS')));
		}				
		if (!$res)
				$errors[] = $this->displayError($this->l('The configuration could not be updated.'));			
		else 
		{	
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsthemelayoutThemesetting', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		}	
	}
	
	public function getHomePages() 
	{		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM '._DB_PREFIX_.'jmsadv_homepages
			ORDER BY ordering');	
	}	
	
	public function renderForm()
	{
		$homepages = $this->getHomePages();
		$_phovers = array();	
		foreach ($this->_producthovers as $phkey => $phvalue)
			$_phovers[] = array('id' => $phkey, 'name' => $phvalue);		
		$_pboxs = array();	
		foreach ($this->_productboxs as $pbkey => $pbvalue)
			$_pboxs[] = array('id' => $pbkey, 'name' => $pbvalue);
		$input_arr = array();		
		if(count($this->_themeskins) > 0) {
			$input_arr[] =  array(
				'type' => 'theme_skin',
				'label' => $this->l('Theme Skin'),
				'name' => 'JMSSETTING_SKIN',						
				'img_list' => $this->_themeskins,
				'selected' => Configuration::get('JMSSETTING_SKIN'),
				'default_img' => true,	
				'img_url' => $this->context->shop->getBaseURL().'themes/'._THEME_NAME_.'/skin-icons'
			);
		}	
		$input_arr[] = 	array(
				'type' => 'select',
				'label' => $this->l('Home Page'),
				'name' => 'JMSADV_HOMEPAGE',						
				'options' => array('query' => $homepages,'id' => 'id_homepage','name' => 'title')
			);
		if(count($_phovers) > 0) {	
			$input_arr[] = 	array(
				'type' => 'select',
				'label' => $this->l('Product Image Hover'),
				'name' => 'JMSSETTING_PRODUCTHOVER',						
				'options' => array('query' => $_phovers,'id' => 'id','name' => 'name')
			);	
		}	
		if(count($_pboxs) > 0) {	
			$input_arr[] = 	array(
				'type' => 'select',
				'label' => $this->l('Product Box'),
				'name' => 'JMSSETTING_PRODUCTBOX',
				'desc' => $this->l('3 Type of Product Box'),
				'class' => ' fixed-width-xl',
				'options' => array('query' => $_pboxs,'id' => 'id','name' => 'name')
			);
		}	
		$input_arr[] = 	array(
				'type' => 'switch',
				'label' => $this->l('RTL'),
				'name' => 'JMSSETTING_RTL',
				'desc' => $this->l('Direction : Right to Left.'),
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('Enabled')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('Disabled'))
				)
			);
		$input_arr[] = 	array(
				'type' => 'switch',
				'label' => $this->l('Setting Panel'),
				'name' => 'JMSSETTING_TOOLS',
				'desc' => $this->l('Show or Hide setting panel on front.'),
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('Enabled')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('Disabled'))
				)
			);
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Position informations'),
				'icon' => 'icon-cogs'
			),
		'input' => $input_arr,
		'submit' => array(
			'title' => $this->l('Save'),
			'name' => 'submitConfig'
		)
		);
		
		$this->fields_value = $this->getConfigFieldsValues();
		return adminController::renderForm();	

	}

	public function getConfigFieldsValues()
	{
		return array(
			'JMSSETTING_SKIN' => Tools::getValue('JMSSETTING_SKIN', Configuration::get('JMSSETTING_SKIN')),			
			'JMSADV_HOMEPAGE' => Tools::getValue('JMSADV_HOMEPAGE', Configuration::get('JMSADV_HOMEPAGE')),			
			'JMSSETTING_PRODUCTHOVER' => Tools::getValue('JMSSETTING_PRODUCTHOVER', Configuration::get('JMSSETTING_PRODUCTHOVER')),
			'JMSSETTING_PRODUCTBOX' => Tools::getValue('JMSSETTING_PRODUCTBOX', Configuration::get('JMSSETTING_PRODUCTBOX')),		
			'JMSSETTING_RTL' => Tools::getValue('JMSSETTING_RTL', Configuration::get('JMSSETTING_RTL')),		
			'JMSSETTING_TOOLS' => Tools::getValue('JMSSETTING_TOOLS', Configuration::get('JMSSETTING_TOOLS'))	
		);
	}
	
	public function headerHTML()
	{
		if (Tools::getValue('controller') != 'AdminJmsthemelayoutHeader' && Tools::getValue('configure') != $this->name)
			return;
		$this->context->controller->addJqueryUI('ui.resizable');		
		$this->context->controller->addJqueryUI('ui.sortable');
		$this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/views/js/resize_script.js', 'all');
		/* Style & js for fieldset 'rows configuration' */
		$html = '<script type="text/javascript">
			$(function() {
				var $myProf = $("#prof_list");
				$myProf.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updateProfOrdering";						
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}
				});
				$myProf.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
				var $myRows = $(".rowlist");
				$myRows.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updateRowsOrdering";						
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}	
				});
				$myRows.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
				var $myposition = $(".row-positions");
				$myposition.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updatePositionsOrdering";
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}	
				});
				
				$myposition.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
				var $myblocks = $(".pos-blocks");
				$myblocks.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updateBlocksOrdering";
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}					
				});
				$myblocks.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
				var $myLinks = $("#links");
				$myLinks.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updateLinksOrdering";
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}
				});
				$myLinks.hover(function() {
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