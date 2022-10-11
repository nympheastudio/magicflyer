<?php
/**
* 2007-2015 PrestaShop
*
* Jms Blog
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

if (!defined('_PS_VERSION_'))
	exit;

class JmsBlog extends Module
{
	public function __construct()
	{
		$this->name = 'jmsblog';
		$this->tab = 'front_office_features';
		$this->version = '2.5.5';
		$this->author = 'Joommasters';
		$this->need_instance = 0;
		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Jms Blog');
		$this->description = $this->l('Blog For Prestashop.');
	}

	public function install()
	{
		$res = true;	
		$this->addMeta('module-jmsblog-category', 'Jms Blog Category', 'jmsblog-category');
		$this->addMeta('module-jmsblog-post', 'Jms Blog Post', 'jmsblog-post');
		$this->addMeta('module-jmsblog-tag', 'Jms Blog Tag', 'jmsblog-tag');
		$this->addMeta('module-jmsblog-archive', 'Jms Blog Archive', 'jmsblog-archive');		
		$this->addMeta('module-jmsblog-categories', 'Jms Blog Categories', 'jmsblog-categories');		
		if (parent::install() && $this->registerHook('moduleRoutes')) 
		{			
			include(dirname(__FILE__).'/install/install.php');
			$install_demo = new JmsInstall();
			$install_demo->createTable();						
			$install_demo->installSamples();			
			$id_tab1 = $this->addTab('Jms Blog', 'dashboard');			
			$this->addTab('Categories', 'categories', $id_tab1);
			$this->addTab('Post', 'post', $id_tab1);
			$this->addTab('Comments', 'comment', $id_tab1);
			$this->addTab('Setting', 'setting', $id_tab1);
			$res &= Configuration::updateValue('JMSBLOG_INTROTEXT_LIMIT', 300);
			$res &= Configuration::updateValue('JMSBLOG_SHOW_CATEGORY', 1);
			$res &= Configuration::updateValue('JMSBLOG_SHOW_VIEWS', 1);
			$res &= Configuration::updateValue('JMSBLOG_SHOW_COMMENTS', 1);
			$res &= Configuration::updateValue('JMSBLOG_SHOW_MEDIA', 1);
			$res &= Configuration::updateValue('JMSBLOG_IMAGE_WIDTH', 1000);
			$res &= Configuration::updateValue('JMSBLOG_IMAGE_HEIGHT', 1000);
			$res &= Configuration::updateValue('JMSBLOG_IMAGE_THUMB_WIDTH', 300);
			$res &= Configuration::updateValue('JMSBLOG_IMAGE_THUMB_HEIGHT', 300);
			$res &= Configuration::updateValue('JMSBLOG_COMMENT_ENABLE', 1);
			$res &= Configuration::updateValue('JMSBLOG_FACEBOOK_COMMENT', 0);
			$res &= Configuration::updateValue('JMSBLOG_ALLOW_GUEST_COMMENT', 1);
			$res &= Configuration::updateValue('JMSBLOG_COMMENT_DELAY', 120);
			$res &= Configuration::updateValue('JMSBLOG_AUTO_APPROVE_COMMENT', 0);
			$res &= Configuration::updateValue('JMSBLOG_SHOW_SOCIAL_SHARING', 1);
			$res &= Configuration::updateValue('JMSBLOG_SHOW_FACEBOOK', 1);
			$res &= Configuration::updateValue('JMSBLOG_SHOW_TWITTER', 1);
			$res &= Configuration::updateValue('JMSBLOG_SHOW_GOOGLEPLUS', 1);
			$res &= Configuration::updateValue('JMSBLOG_SHOW_LINKEDIN', 1);
			$res &= Configuration::updateValue('JMSBLOG_SHOW_PINTEREST', 1);
			$res &= Configuration::updateValue('JMSBLOG_SHOW_EMAIL', 1);
			
			
			return $res;
		}
		return false;
	}	
	public function uninstall()
	{
		$res = true;
		/* Deletes Module */
		$this->controllers = array('category','post','archive','tag');
		if (parent::uninstall())
		{
			$sql = array();
			include(dirname(__FILE__).'/install/uninstall.php');
			foreach ($sql as $s)
				Db::getInstance()->execute($s);												
			$this->removeTab('categories');
			$this->removeTab('post');
			$this->removeTab('comment');
			$this->removeTab('setting');			
			$this->removeTab('dashboard');	
			
			$res &= Configuration::deleteByName('JMSBLOG_INTROTEXT_LIMIT');
			$res &= Configuration::deleteByName('JMSBLOG_SHOW_CATEGORY');
			$res &= Configuration::deleteByName('JMSBLOG_SHOW_VIEWS');
			$res &= Configuration::deleteByName('JMSBLOG_SHOW_COMMENTS');
			$res &= Configuration::deleteByName('JMSBLOG_SHOW_MEDIA');
			$res &= Configuration::deleteByName('JMSBLOG_IMAGE_WIDTH');
			$res &= Configuration::deleteByName('JMSBLOG_IMAGE_HEIGHT');
			$res &= Configuration::deleteByName('JMSBLOG_IMAGE_THUMB_WIDTH');
			$res &= Configuration::deleteByName('JMSBLOG_IMAGE_THUMB_HEIGHT');
			$res &= Configuration::deleteByName('JMSBLOG_COMMENT_ENABLE');
			$res &= Configuration::deleteByName('JMSBLOG_FACEBOOK_COMMENT');
			$res &= Configuration::deleteByName('JMSBLOG_ALLOW_GUEST_COMMENT');
			$res &= Configuration::deleteByName('JMSBLOG_COMMENT_DELAY');
			$res &= Configuration::deleteByName('JMSBLOG_AUTO_APPROVE_COMMENT');
			$res &= Configuration::deleteByName('JMSBLOG_SHOW_SOCIAL_SHARING');
			$res &= Configuration::deleteByName('JMSBLOG_SHOW_FACEBOOK');
			$res &= Configuration::deleteByName('JMSBLOG_SHOW_TWITTER');
			$res &= Configuration::deleteByName('JMSBLOG_SHOW_GOOGLEPLUS');
			$res &= Configuration::deleteByName('JMSBLOG_SHOW_LINKEDIN');
			$res &= Configuration::deleteByName('JMSBLOG_SHOW_PINTEREST');
			$res &= Configuration::deleteByName('JMSBLOG_SHOW_EMAIL');
			
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
	private function addMeta($page, $title, $url_rewrite, $desc = '', $keywords = '')
	{
		$themes = Theme::getThemes();
		$theme_meta_value = array();
		$result = Db::getInstance()->getValue('SELECT * FROM '._DB_PREFIX_.'meta WHERE page="'.pSQL($page).'"');
		if ((int)$result > 0)
			return true;
		$_meta = new MetaCore();		
		$_meta->page = $page;
		$_meta->configurable = 1;		
		$langs = Language::getLanguages(false);
		foreach ($langs as $l)
		{
			$_meta->title[$l['id_lang']] = $title;
			$_meta->description[$l['id_lang']] = $desc;
			$_meta->keywords[$l['id_lang']] = $keywords;
			$_meta->url_rewrite[$l['id_lang']] = $url_rewrite;
		}	
		
		$_meta->add();		
		if ((int)$_meta->id > 0)
		{
			foreach ($themes as $theme)
			{				
				$theme_meta_value[] = array(
					'id_theme' => $theme->id,
					'id_meta' => $_meta->id,
					'left_column' => (int)$theme->default_left_column,
					'right_column' => (int)$theme->default_right_column
				);
			}		
			if (count($theme_meta_value) > 0)
				return Db::getInstance()->insert('theme_meta', $theme_meta_value);
		}	
		else 
			return false;
	}	
	
	public static function getJmsBlogUrl() 
	{		
		$ssl_enable = Configuration::get('PS_SSL_ENABLED');
		$id_shop = (int)Context::getContext()->shop->id;
		//$rewrite_set = 1;
		$relative_protocol = false;
		$ssl = null;
		static $force_ssl = null;
		if ($ssl === null)
		{
			if ($force_ssl === null)
				$force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
			$ssl = $force_ssl;
		}
		if (1 && $id_shop !== null)
			$shop = new Shop($id_shop);
		else
			$shop = Context::getContext()->shop;
		if (!$relative_protocol)
			$base = '//'.($ssl && $ssl_enable ? $shop->domain_ssl : $shop->domain);
		else
			$base = (($ssl && $ssl_enable) ? 'https://'.$shop->domain_ssl : 'http://'.$shop->domain);				
		return $base.$shop->getBaseURI();
	}
	
	public static function getPageLink($rewrite = 'jmsblog', $params = null, $id_lang = null)
	{
		$url = jmsblog::getJmsBlogUrl();				
		$dispatcher = Dispatcher::getInstance();
		if ($params != null)
			return str_replace('&', '&amp;', $url.$dispatcher->createUrl($rewrite, $id_lang, $params));
		else
			return str_replace('&', '&amp;', $url.$dispatcher->createUrl($rewrite));
	}	
	public function hookModuleRoutes($params)
	{	
		$usehtml = (int)Configuration::get('smartusehtml');
		if ($usehtml != 0)
			$html = '.html';
		else
			$html = '';
		return array(
			'jmsblog-categories' => array(
				'controller' => 'categories',
				'rule' => 'jmsblog/categories'.$html,
				'keywords' => array(
				),
				'params' => array(
					'fc' => 'module',
					'module' => 'jmsblog'
				)				
			),
			'jmsblog-post' => array(
				'controller' => 'post',
				'rule' => 'jmsblog/{category_slug}/{post_id}_{slug}'.$html,
				'keywords' => array(
					'post_id' => array('regexp' => '[\d]+','param' => 'post_id'),					
					'category_slug' => array('regexp' => '[\w]+','param' => 'category_slug'),
					'slug' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				),
				'params' => array(
					'fc' => 'module',
					'module' => 'jmsblog'
				)				
			),
			'jmsblog-category' => array(
				'controller' => 'category',
				'rule' => 'jmsblog/{category_id}_{slug}'.$html,
				'keywords' => array(					
					'category_id' => array('regexp' => '[\w]+','param' => 'category_id'),
					'slug' =>	array('regexp' => '[_a-zA-Z0-9-\pL]*'),
				),
				'params' => array(
					'fc' => 'module',
					'module' => 'jmsblog'
				)				
			),
			'jmsblog-archive' => array(
				'controller' => 'archive',
				'rule' => 'jmsblog/archive-month/{archive}'.$html,
				'keywords' => array(					
					'archive' => array('regexp' => '[_a-zA-Z0-9-\pL]*','param' => 'archive')
				),
				'params' => array(
					'fc' => 'module',
					'module' => 'jmsblog'
				)				
			),
			'jmsblog-tag' => array(
				'controller' => 'tag',
				'rule' => 'jmsblog/tag/{tag}'.$html,
				'keywords' => array(					
					'tag' => array('regexp' => '[\w]+','param' => 'tag')
				),
				'params' => array(
					'fc' => 'module',
					'module' => 'jmsblog'
				)				
			)	
		);	
	}
}
?>
