<?php
/**
* 2007-2015 PrestaShop
*
* Jms Ajax Search
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

if (!defined('_PS_VERSION_'))
	exit;

class JmsAjaxsearch extends Module
{
	private $_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'jmsajaxsearch';
		$this->tab = 'front_office_features';
		$this->version = '1.1.0';
		$this->author = 'Joommasters';
		$this->need_instance = 0;
		$this->morecharacter = $this->l('Please enter at least 3 characters');
		$this->no_products = $this->l('There is no product');
		parent::__construct();

		$this->displayName = $this->l('JMS AJAX Search');
		$this->description = $this->l('AJAX Search module');
	}

	public function install()
	{
		$res = true;
		if (parent::install() && $this->registerHook('header')) 
		{ 			
			$res &= Configuration::updateValue('JMS_AJAXSEARCH_COUNT', '5');			
			$res &= Configuration::updateValue('JMS_AJAXSEARCH_SHOW_DESC', '0');
			$res &= Configuration::updateValue('JMS_AJAXSEARCH_DESC_COUNT', '100');
			$res &= Configuration::updateValue('JMS_AJAXSEARCH_SHOW_PRICE', '1');
			$res &= Configuration::updateValue('JMS_AJAXSEARCH_SHOW_IMAGE', '1');			
			return $res;
		}		
		return false;	
	}
	public function uninstall()
	{
		/* Deletes Module */
		$res = true;
		if (parent::uninstall())
		{			
			/* Unsets configuration */
			$res &= Configuration::deleteByName('JMS_AJAXSEARCH_COUNT');			
			$res &= Configuration::deleteByName('JMS_AJAXSEARCH_SHOW_DESC');
			$res &= Configuration::deleteByName('JMS_AJAXSEARCH_DESC_COUNT');
			$res &= Configuration::deleteByName('JMS_AJAXSEARCH_SHOW_PRICE');
			$res &= Configuration::deleteByName('JMS_AJAXSEARCH_SHOW_IMAGE');
			return (bool)$res;
		}

		return false;
	}
	public function getContent()
	{
		$errors = array();
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitConfig'))
		{
			$count = (int)(Tools::getValue('count'));
			if (!$count || $count <= 0 || !Validate::isInt($count)) 
				$errors[] = $this->l('An invalid number of products has been specified.');			
			else 
			{				
				Configuration::updateValue('JMS_AJAXSEARCH_COUNT', (int)(Tools::getValue('count')));		
				Configuration::updateValue('JMS_AJAXSEARCH_SHOW_DESC', (int)(Tools::getValue('show_desc')));
				Configuration::updateValue('JMS_AJAXSEARCH_DESC_COUNT', (int)(Tools::getValue('desc_count')));
				Configuration::updateValue('JMS_AJAXSEARCH_SHOW_PRICE', (int)(Tools::getValue('show_price')));				
				Configuration::updateValue('JMS_AJAXSEARCH_SHOW_IMAGE', (int)(Tools::getValue('show_image')));
			}	
				
			if (isset($errors) && count($errors))
				$output .= $this->displayError(implode('<br />', $errors));
			else
				$output .= $this->displayConfirmation($this->l('Your settings have been updated.'));
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{		
		$output = '
		<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>				
				<label>'.$this->l('Number of products to be displayed').'</label>
				<div class="margin-form">
					<input type="text" size="5" name="count" value="'.Tools::safeOutput(Tools::getValue('count', (int)(Configuration::get('JMS_AJAXSEARCH_COUNT')))).'" />				
				</div>	
				<label>'.$this->l('Show Description').'</label>
				<div class="margin-form">
					<input type="radio" name="show_desc" id="show_desc_on" value="1" '.(Tools::getValue('show_desc', Configuration::get('JMS_AJAXSEARCH_SHOW_DESC')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="show_desc_on">'.$this->l('Yes').'</label>
					<input type="radio" name="show_desc" id="show_desc_off" value="0" '.(!Tools::getValue('show_desc', Configuration::get('JMS_AJAXSEARCH_SHOW_DESC')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="show_desc_off">'.$this->l('No').'</label>
				</div>	
				<label>'.$this->l('Description character limit').'</label>
				<div class="margin-form">
					<input type="text" size="5" name="desc_count" value="'.Tools::safeOutput(Tools::getValue('desc_count', (int)(Configuration::get('JMS_AJAXSEARCH_DESC_COUNT')))).'" />				
				</div>		
				<label>'.$this->l('Show Price').'</label>
				<div class="margin-form">
					<input type="radio" name="show_price" id="show_price_on" value="1" '.(Tools::getValue('show_price', Configuration::get('JMS_AJAXSEARCH_SHOW_PRICE')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="show_price_on">'.$this->l('Yes').'</label>
					<input type="radio" name="show_price" id="show_price_off" value="0" '.(!Tools::getValue('show_price', Configuration::get('JMS_AJAXSEARCH_SHOW_PRICE')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="show_price_off">'.$this->l('No').'</label>
				</div>	
				<label>'.$this->l('Show Image').'</label>
				<div class="margin-form">
					<input type="radio" name="show_image" id="show_image_on" value="1" '.(Tools::getValue('show_image', Configuration::get('JMS_AJAXSEARCH_SHOW_IMAGE')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="show_image_on">'.$this->l('Yes').'</label>
					<input type="radio" name="show_image" id="show_image_off" value="0" '.(!Tools::getValue('show_image', Configuration::get('JMS_AJAXSEARCH_SHOW_IMAGE')) ? 'checked="checked" ' : '').'/>
					<label class="t" for="show_image_off">'.$this->l('No').'</label>
				</div>		
				<center><input type="submit" name="submitConfig" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
		return $output;
	}
	public function hookDisplayHeader()	
	{	
		$this->context->controller->addJS(($this->_path).'views/js/ajaxsearch.js', 'all');
		$this->context->controller->addCSS(($this->_path).'views/css/style.css', 'all');
	}
	public function hookdisplayTop()	
	{
		$root_url = _PS_BASE_URL_.__PS_BASE_URI__;
		$this->context->controller->addCSS(($this->_path).'views/css/style.css', 'all');
		
		$this->smarty->assign(array(
			'root_url' => $root_url
		));				
		return $this->display(__FILE__, 'jmsajaxsearch.tpl');
	}	
	
	public function hookRightColumn()
	{
		$root_url = _PS_BASE_URL_.__PS_BASE_URI__;
		$this->context->controller->addCSS(($this->_path).'views/css/style.css', 'all');
		
		$this->smarty->assign(array(
			'root_url' => $root_url
		));				
		return $this->display(__FILE__, 'jmsajaxsearch-right.tpl');
	}
}
