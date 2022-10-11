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
class JmsThemeLayout extends Module
{
	private $hookspos = array();
	private $hookalias = array();
	private $jmsHooks = array();
	private $_themeskins = array();
	private $_producthovers = array();
	private $_productboxs = array();
	private $fields_options = array();	
	public function __construct()
	{
		$this->name = 'jmsthemelayout';
		$this->tab = 'front_office_features';
		$this->version = '2.5.5';
		$this->author = 'Joommasters';
		$this->need_instance = 0;
		$this->bootstrap = true;
		$this->id_header = 0;
		$this->id_homebody = 0;
		$this->id_footer = 0;
		parent::__construct();
		$this->displayName = $this->l('Jms Page Builder');
		$this->description = $this->l('Home Page Builder For Prestashop Theme.');
		
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

	public function install()
	{		
		if (parent::install() && $this->registerHook('header') && $this->registerHook('displayTop') && $this->registerHook('displayHome') && $this->registerHook('displayFooter')) 
		{			
			$res = Configuration::updateValue('JMSSETTING_SKIN', '');						
			$res &= Configuration::updateValue('JMSSETTING_PRODUCTHOVER', '');	
			$res &= Configuration::updateValue('JMSSETTING_PRODUCTBOX', '');	
			$res &= Configuration::updateValue('JMSSETTING_RTL', '0');				
			$res &= Configuration::updateValue('JMSSETTING_TOOLS', '1');						
			
			include(dirname(__FILE__).'/install/jmsinstall.php');
			$install_demo = new JmsLayoutInstall();
			$install_demo->createTable();			
			$install_demo->InstallDemo();
						
			$id_tab1 = $this->addTab('Jms Page Builder', 'dashboard');
			$this->addTab('Header', 'header', $id_tab1);
			$this->addTab('Body', 'homebody', $id_tab1);
			$this->addTab('Footer', 'footer', $id_tab1);
			$this->addTab('Home Pages', 'homepage', $id_tab1);
			$this->addTab('Theme Setting', 'themesetting', $id_tab1);			
			return $res;
		}
		return false;
	}
	public function getCurrentHomePage()
	{
		if ($this->context->cookie->jmshomepage != '')
			$homepage_id = $this->context->cookie->jmshomepage;
		else
			$homepage_id = Configuration::get('JMSADV_HOMEPAGE');				
		$homepage = $this->getHomePage($homepage_id);
		return $homepage;		
	}
	public function uninstall()
	{
		/* Deletes Module */
		if (parent::uninstall())
		{
			$res = Configuration::deleteByName('JMSSETTING_SKIN');					
			$res &= Configuration::deleteByName('JMSSETTING_PRODUCTHOVER');				
			$res &= Configuration::deleteByName('JMSSETTING_PRODUCTBOX');
			$res &= Configuration::deleteByName('JMSSETTING_RTL');			
			$res &= Configuration::deleteByName('JMSSETTING_TOOLS');						
			$sql = array();
			include(dirname(__FILE__).'/install/uninstall.php');
			foreach ($sql as $s)
				Db::getInstance()->execute($s);						
			Configuration::deleteByName('JMSADV_HOMEPAGE');
			$this->removeTab('header');
			$this->removeTab('homebody');
			$this->removeTab('footer');
			$this->removeTab('homepage');
			$this->removeTab('themesetting');			
			$this->removeTab('dashboard');	
			return $res;
		}
		return false;
	}

	private function addTab($title, $class_sfx = '', $parent_id = 0)
	{
		$class = 'Admin'.Tools::ucfirst($this->name).Tools::ucfirst($class_sfx);
		@Tools::copy(_PS_MODULE_DIR_.$this->name.'/logo.gif', _PS_IMG_DIR_.'t/'.$class.'.gif');
		$_tab = new Tab();
		$_tab->class_name = $class;
		$_tab->module = $this->name;
		$_tab->id_parent = $parent_id;
		$langs = Language::getLanguages(false);
		foreach ($langs as $l)
			$_tab->name[$l['id_lang']] = $title;
		if ($parent_id == -1)
		{
			$_tab->id_parent = -1;
			$_tab->add();
		}
		else
			$_tab->add(true, false);
		return $_tab->id;
	}

	private function removeTab($class_sfx = '')
	{
		$tabClass = 'Admin'.Tools::ucfirst($this->name).Tools::ucfirst($class_sfx);
		$idTab = Tab::getIdFromClassName($tabClass);
		if ($idTab != 0)
		{
			$tab = new Tab($idTab);
			$tab->delete();
			return true;
		}
		return false;
	}
	
	public function getHomePage($id_homepage) 
	{	
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM '._DB_PREFIX_.'jmsadv_homepages
			WHERE id_homepage = '.$id_homepage
		);	
	}
	
	public function getHomePages() 
	{		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM '._DB_PREFIX_.'jmsadv_homepages
			ORDER BY ordering');	
	}
	
	private function setting_reset() 
	{		
		$this->context->cookie->__unset('jmsskin');		
		$this->context->cookie->__unset('jmshomepage');		
		$this->context->cookie->__unset('jmsphover');		
		$this->context->cookie->__unset('jmspbox');		
		$this->context->cookie->__unset('jmsrtl');		
		
	}
	
	public function hookDisplayHeader()	
	{	
		if (Tools::isSubmit('settingdemo') && (int)(Tools::getValue('settingdemo')) == 1) 
		{
			if (Tools::isSubmit('jmsskin')) $this->context->cookie->jmsskin = Tools::getValue('jmsskin');									
			if (Tools::isSubmit('jmshomepage')) $this->context->cookie->jmshomepage = Tools::getValue('jmshomepage');			
			if (Tools::isSubmit('jmsphover')) $this->context->cookie->jmsphover = Tools::getValue('jmsphover');	
			if (Tools::isSubmit('jmspbox')) $this->context->cookie->jmspbox = Tools::getValue('jmspbox');
			if (Tools::isSubmit('jmsrtl')) $this->context->cookie->jmsrtl = Tools::getValue('jmsrtl');
			
			Tools::redirect($this->context->shop->getBaseURL());
		} 
		elseif (Tools::isSubmit('settingreset')) 
		{
			$this->setting_reset();
			Tools::redirect($this->context->shop->getBaseURL());
		}
		
		if ($this->context->cookie->jmsskin != '') 
			$skin = $this->context->cookie->jmsskin;
		else 		
			$skin = Configuration::get('JMSSETTING_SKIN');					
		if ($skin == 'default' || $skin == 'preset1') 
			$skin = '';
		if ($this->context->cookie->jmshomepage != '') 
			$homepage = $this->context->cookie->jmshomepage;
		else 		
			$homepage = Configuration::get('JMSADV_HOMEPAGE');				
		if ($this->context->cookie->jmsphover != '') 		
			$phover = $this->context->cookie->jmsphover;			
		else
			$phover = Configuration::get('JMSSETTING_PRODUCTHOVER');
		if ($phover == 'image_swap') 
		{	
			$this->context->controller->addJS(($this->_path).'views/js/runajax.js');					
		}
		if ($this->context->cookie->jmspbox != '') 		
			$pbox = $this->context->cookie->jmspbox;			
		else
			$pbox = Configuration::get('JMSSETTING_PRODUCTBOX');
		if ($this->context->cookie->jmsrtl != '') 		
			$rtl = $this->context->cookie->jmsrtl;			
		else
			$rtl = Configuration::get('JMSSETTING_RTL');		
		
		$homepages = $this->getHomePages();				
		$this->context->smarty->assign('phover', $phover);
		$this->context->smarty->assign('homepage', $homepage);
		$this->context->smarty->assign('pbox', $pbox);		
		$this->context->smarty->assign('rtl', $rtl);
		$this->context->smarty->assign('themename', _THEME_NAME_);			
		$this->context->smarty->assign('themeskins', $this->_themeskins);				
		$this->context->smarty->assign('skin', $skin);				
		if (count($homepages)) 
			$this->context->smarty->assign('homepages', $homepages);
		if (count($this->_producthovers)) 
			$this->context->smarty->assign('producthovers', $this->_producthovers);		
		if (count($this->_productboxs)) 
			$this->context->smarty->assign('productboxs', $this->_productboxs);	
		
		$this->context->smarty->assign('tools', Configuration::get('JMSSETTING_TOOLS'));				
	}
	
	public function hookTop()
	{
		$jms_exc = new JmsExecute();
		$this->context->controller->addCSS(($this->_path).'views/css/style.css', 'all');
		$homepage = $this->getCurrentHomePage();
		if ($homepage['id_header'])
			$rows = $jms_exc->getRows(1, $homepage['id_header']);
		foreach ($rows as $key => $row)
		{
			$rows[$key]['positions'] = $jms_exc->getIdPositions($row['id_row'], 1);
			foreach ($rows[$key]['positions'] as $number => $position)
			{
				$values = $jms_exc->getActBlocks($position['id_position']);
				$rows[$key]['positions'][$number]['blocks'] = $values;
				foreach ($values as $count => $value)
					$rows[$key]['positions'][$number]['blocks'][$count]['block_type'] = $value['block_type'];
			}
		}

		$profile_class = $jms_exc->getProfClass($homepage['id_header']);
		$this->smarty->assign(array(
			'rows_header' => $rows,
			'class_header' => $profile_class,
		));

		return $this->display(__FILE__, 'jmsadvheader.tpl');
	}
	public function hookdisplayHome()
	{
		$jms_exc = new JmsExecute();
		$homepage = $this->getCurrentHomePage();
		if ($homepage['id_homebody'])
			$rows = $jms_exc->getRows(1, $homepage['id_homebody']);
		
		foreach ($rows as $key => $row)
		{
			$rows[$key]['positions'] = $jms_exc->getIdPositions($row['id_row'], 1);
			foreach ($rows[$key]['positions'] as $number => $position)
				$rows[$key]['positions'][$number]['blocks'] = $jms_exc->getActBlocks($position['id_position']);
		}

		$profile_class = $jms_exc->getProfClass($homepage['id_homebody']);
		$font_url = $jms_exc->getFontUrl($homepage['id_homepage']);
		$css_file = $jms_exc->getHomeCssFile($homepage['id_homepage']);
		if ($font_url)
			$this->context->controller->addCSS($font_url, 'text/css');	
		if ($css_file)
			$this->context->controller->addCSS($this->context->shop->getBaseURL().'themes/'._THEME_NAME_.'/css/'.$css_file, 'all');	
		$this->smarty->assign(array(
			'rows' => $rows,
			'class_home' => $profile_class
		));

		return $this->display(__FILE__, 'jmsadvhomebody.tpl');
	}
	
	public function hookFooter()
	{
		$jms_exc = new JmsExecute();
		$homepage = $this->getCurrentHomePage();
		if ($homepage['id_footer'])		
			$rows = $jms_exc->getRows(1, $homepage['id_footer']);

		foreach ($rows as $key => $row)
		{
			$rows[$key]['positions'] = $jms_exc->getIdPositions($row['id_row'], 1);
			foreach ($rows[$key]['positions'] as $number => $position)
				$rows[$key]['positions'][$number]['blocks'] = $jms_exc->getActBlocks($position['id_position']);
		}

		$profile_class = $jms_exc->getProfClass($homepage['id_footer']);
		
		$this->context->smarty->assign(array(
			'rows_footer' => $rows,
			'class_footer' => $profile_class
		));
		
		
		return $this->display(__FILE__, 'jmsadvfooter.tpl');
	}
	
	public function getAllImages($id_lang, $where, $order)
	{
		$sql = 'SELECT i.`id_product`, image_shop.`cover`, i.`id_image`, il.`legend`, i.`position`,pl.`link_rewrite`
				FROM `'._DB_PREFIX_.'image` i
				'.Shop::addSqlAssociation('image', 'i').'
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (i.`id_product` = pl.`id_product`) 
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')'.$where.' '.$order;
		
		return Db::getInstance()->executeS($sql);
	}
	
	public function getSecondImgs($productids)
	{
		$link = $this->context->link;
		$id_lang = Context::getContext()->language->id;
		$where  = ' WHERE i.`id_product` IN ('.$productids.') AND (i.`cover` IS NULL OR i.`cover` = 0)';
		$order  = ' ORDER BY i.`id_product`,`position`';
		$limit  = ' LIMIT 0,1';
		//get product info\
		$listImg = $this->getAllImages($id_lang, $where, $order, $limit);
		$savedImg = array();
		$obj = array();
		$this->smarty->assign(array('homeSize' => Image::getSize(ImageType::getFormatedName('home')),'mediumSize' => Image::getSize(ImageType::getFormatedName('medium')),'smallSize' => Image::getSize(ImageType::getFormatedName('small'))));
		foreach ($listImg as $product)
		{
			if (!in_array($product['id_product'], $savedImg))
				$obj[] = array('id'=>$product['id_product'],'content'=>($link->getImageLink($product['link_rewrite'], $product['id_image'], 'home_default')));
			$savedImg[] = $product['id_product'];
		}
		return $obj;
	}
}
?>
