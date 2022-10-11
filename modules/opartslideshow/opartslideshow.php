<?php
// Security
if (!defined('_PS_VERSION_'))
	exit;

// Loading Models
require_once(_PS_MODULE_DIR_ . 'opartslideshow/models/MySlideshow.php');
require_once(_PS_MODULE_DIR_ . 'opartslideshow/models/MyImage.php');

class Opartslideshow extends Module
{			
	
  public function __construct()
  {  		  	
	  $this->name = 'opartslideshow';
	  $this->tab = 'front_office_features';
	  $this->version = '2.0.2';
	  
	  $this->author = 'Op\'art - Olivier CLEMENCE';
	  $this->need_instance = 0;
	  parent::__construct();
	
	  $this->displayName = $this->l('Op\'art slideshow');
	  $this->description = $this->l('multiple sideshow');
	  	  
	  $this->confirmUninstall = $this->l('Are you sure you want to delete this module ?');
	  
	  if ($this->active && Configuration::get('OPART_SLIDESHOW_CONF') == 'ok')
			$this->warning = $this->l('You have to configure your module');
  }
  

	public function install()
	{
		// Install SQL
		include(dirname(__FILE__).'/sql/install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
								
		// Install Tabs
		$parent_tab = new Tab();
		//$parent_tab->name = 'Op\'art Slideshow';
		$parent_tab->name = array();
		foreach (Language::getLanguages() as $language)
			$parent_tab->name[$language['id_lang']] = 'Op\'art Slideshow';
		$parent_tab->class_name = 'AdminMainOpart';
		$parent_tab->id_parent = 0;
		$parent_tab->module = $this->name;
		$parent_tab->add();
		
		
		$tab1 = new Tab();
		//$tab1->name = 'Slideshow';
		$tab1->name = array();
		foreach (Language::getLanguages() as $language)
			$tab1->name[$language['id_lang']] = 'Slideshow';
		$tab1->class_name = 'AdminSlideshow';
		$tab1->id_parent = $parent_tab->id;
		$tab1->module = $this->name;
		$tab1->add();
		
		$tab2 = new Tab();
		//$tab2->name = 'Images';
		$tab2->name = array();
		foreach (Language::getLanguages() as $language)
			$tab2->name[$language['id_lang']] = 'Images';
		$tab2->class_name = 'AdminSlideshowImage';
		$tab2->id_parent = $parent_tab->id;
		$tab2->module = $this->name;
		$tab2->add();
		
		//Init
		Configuration::updateValue('OPART_SLIDESHOW_CONF', '');	
		
		// Install Module  
		if (
			parent::install() == false 
			OR !$this->registerHook('displayTop')
			OR !$this->registerHook('displayTopColumn')
			OR !$this->registerHook('displayLeftColumn')
			OR !$this->registerHook('displayRightColumn')
			OR !$this->registerHook('displayHome')
			OR !$this->registerHook('displayOpartSlideshowHook')
			OR !$this->registerHook('displayHeader')
			OR !$this->registerHook('displayFooter')
		)
			return false;
		return true;  
					
  }    
  
  public function uninstall()
	{
		// Uninstall SQL
		include(dirname(__FILE__).'/sql/uninstall.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
				
		Configuration::deleteByName('OPART_SLIDESHOW_CONF');

		// Uninstall Tabs
		$tab = new Tab((int)Tab::getIdFromClassName('AdminSlideshow'));
		$tab->delete();
		
		$tab = new Tab((int)Tab::getIdFromClassName('AdminSlideshowImage'));
		$tab->delete();
		
		$tab = new Tab((int)Tab::getIdFromClassName('AdminMainOpart'));
		$tab->delete();
		
		//delete image
		$uploadDir=_PS_ROOT_DIR_.'/modules/opartslideshow/upload/';
		$photos=$this->searchPhoto($uploadDir);
		foreach($photos as $photo) {
			if(file_exists($uploadDir.$photo))
				unlink($uploadDir.$photo);
		}
		// Uninstall Module
		if (!parent::uninstall())
			return false;
		return true;
	}
	
	private function searchPhoto($dir){
		$photoArray=array();
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($photo = readdir($dh)) !== false) {
					if($photo!="." && $photo!=".." && is_file($dir.'/'.$photo)) {
						$photoArray[]=$photo;
					}
				}
				closedir($dh);
			}
		}
		return $photoArray;
	}
	
	private function prepareHook($hookName) {
		$addToFrom="";
		$addToWhere="";		
		$showSlide=false;
		if(get_class($this->context->controller)=="IndexController") {
			$addToWhere=' AND s.home=1';
			$showSlide=true;
		}		
		if(get_class($this->context->controller)=="CategoryController") {
			$addToFrom='LEFT JOIN '._DB_PREFIX_.'opartslideshow_slideshow_category sc ON (s.id_opartslideshow_slideshow = sc.id_opartslideshow_slideshow)';
			$addToWhere=' AND (sc.id_category='.(int)Tools::getValue('id_category').' OR s.showOnCat=1)';
			$showSlide=true;
		}
		if(get_class($this->context->controller)=="ProductController") {
			$idProd=(int)Tools::getValue('id_product');
			$product=new Product($idProd);
			$idCat=$product->id_category_default;	
			$addToFrom='LEFT JOIN '._DB_PREFIX_.'opartslideshow_slideshow_product sp ON (s.id_opartslideshow_slideshow = sp.id_opartslideshow_slideshow)';
			$addToFrom.=' LEFT JOIN '._DB_PREFIX_.'opartslideshow_slideshow_category sc ON (s.id_opartslideshow_slideshow = sc.id_opartslideshow_slideshow)';
			$addToWhere=' AND (sp.id_product='.$idProd.' OR sc.id_category='.$idCat.' OR s.showOnCat=1 OR s.showOnProd=1)';
			$showSlide=true;
		}
	if(get_class($this->context->controller)=="CmsController") {
			$addToFrom='LEFT JOIN '._DB_PREFIX_.'opartslideshow_slideshow_cms scm ON (s.id_opartslideshow_slideshow = scm.id_opartslideshow_slideshow)';
			$addToWhere=' AND (scm.id_cms='.(int)Tools::getValue('id_cms').' OR s.showOnCms=1)';
			$showSlide=true;
		}
		if($showSlide==false)
			return false;
		$sqlSlide='
		SELECT s.id_opartslideshow_slideshow,s.active,s.width,s.height,s.spw,s.sph,s.delay,s.sDelay,s.opacity,s.titleSpeed,s.effect,s.navigation,s.links,s.hoverpause,sl.name
		FROM '._DB_PREFIX_.'opartslideshow_slideshow s
		LEFT JOIN '._DB_PREFIX_.'opartslideshow_slideshow_lang sl ON (s.id_opartslideshow_slideshow=sl.id_opartslideshow_slideshow)
		'.$addToFrom.'
		WHERE s.active=1 AND hook="'.$hookName.'" AND sl.id_lang = '.(int)$this->context->language->id.$addToWhere.'
		ORDER BY sl.name		
		';
				
		//echo $sqlSlide."<br />";
		if (!$results = Db::getInstance()->ExecuteS($sqlSlide))
			return false;
		$addToWhereImages="";
		$slides=array();
		foreach($results as $result) {
			$slides[$result['id_opartslideshow_slideshow']]=$result;
			$addToWhereImages.=($addToWhereImages=="")?"i.id_opartslideshow_slideshow=".$result['id_opartslideshow_slideshow']:" OR i.id_opartslideshow_slideshow=".$result['id_opartslideshow_slideshow'];
		}
		if($addToWhereImages!="")
			$addToWhereImages=" AND (".$addToWhereImages.")";
		
		$sqlImage='
		SELECT i.id_opartslideshow_slideshow,i.id_opartslideshow_slideshow_image,i.filename,il.name,il.targeturl,il.description
		FROM '._DB_PREFIX_.'opartslideshow_slideshow_image i
		LEFT JOIN '._DB_PREFIX_.'opartslideshow_slideshow_image_lang il ON (i.id_opartslideshow_slideshow_image=il.id_opartslideshow_slideshow_image)
		WHERE il.id_lang = '.(int)$this->context->language->id.' AND i.active=1'.$addToWhereImages.' 
		ORDER BY i.position
		';

		$results = Db::getInstance()->ExecuteS($sqlImage);
		foreach($results as $result) {
			$slides[$result['id_opartslideshow_slideshow']]['images'][]=$result;
		}
		
		
		$effectNames=array("random","swirl", "rain", "straight");
		$this->smarty->assign('effectNames', $effectNames);
		$this->smarty->assign('slides', $slides);
		return true;
	}
	
	public function hookDisplayHeader() {
		
		$this->context->controller->addJS($this->_path.'js/coin-slider.js');
		$this->context->controller->addCSS($this->_path.'css/coin-slider-styles.css');
	}
	
	public function displayHook($tplFile='slideshow.tpl') {
		/*if ($this->context->getMobileDevice() != false)
			return false;*/
		
		//$this->context->controller->addJS(_PS_JS_DIR_.'jquery/jquery-1.7.2.min.js');
		/*$this->context->controller->addJS($this->_path.'js/coin-slider.js');
		$this->context->controller->addCSS($this->_path.'css/coin-slider-styles.css');*/
		
		//$this->displayHeader();
		return $this->display(__FILE__, $tplFile);
	}
	
	public function hookDisplayTop()
	{
		if(!$this->prepareHook('displayTop'))
			return false;		
		return $this->displayHook('slideshowTop.tpl');
	}
	
        public function hookDisplayTopColumn()
	{
		if(!$this->prepareHook('displayTopColumn'))
			return false;		
		return $this->displayHook('slideshowTop.tpl');
	}
        
	public function hookDisplayOpartSlideshowHook()
	{
		if(!$this->prepareHook('displayOpartSlideshowHook'))
			return false;
		return $this->displayHook();
	}
	
	public function hookDisplayHome()
	{
		if(!$this->prepareHook('displayHome'))
			return false;		
		return $this->displayHook();
	}
	
	public function hookLeftColumn()
	{
		if(!$this->prepareHook('displayLeftColumn'))
			return false;		
		return $this->displayHook();
	}
	
	public function hookRightColumn()
	{
		if(!$this->prepareHook('displayRightColumn'))
			return false;		
		return $this->displayHook();
	}
	
	public function hookFooter()
	{
		if(!$this->prepareHook('displayFooter'))
			return false;		
		return $this->displayHook();
	}
	
	public function getContent()
	{
		$this->_html=$this->display(__FILE__, 'views/templates/admin/configure.tpl');
		return $this->_html;
	}
}