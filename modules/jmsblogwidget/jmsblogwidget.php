<?php
/**
* 2007-2015 PrestaShop
*
* Jms Blog Widget
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'jmsblog/class/JmsBlogHelper.php');
include_once(_PS_MODULE_DIR_.'jmsblog/jmsblog.php');
class JmsBlogWidget extends Module
{
	public function __construct()
	{
		$this->name = 'jmsblogwidget';
		$this->tab = 'front_office_features';
		$this->version = '2.5.5';
		$this->author = 'Joommasters';
		$this->need_instance = 0;
		$this->bootstrap = true;		
		$this->child = array();
		$this->gens = array();
		$this->treearr = array();
		$this->menu = '';
		parent::__construct();

		$this->displayName = $this->l('Jms Blog Widget');
		$this->description = $this->l('Home and Sidebar Widget For Jms Blog.');
	}

	public function install()
	{
		$res = true;	
		if (parent::install() && $this->registerHook('header')) 
		{	
			$res &= Configuration::updateValue('JBW_ITEM_TOTAL', 6);
			$res &= Configuration::updateValue('JBW_ITEM_SHOW', 3);
			$res &= Configuration::updateValue('JBW_AUTOPLAY', 1);
			$res &= Configuration::updateValue('JBW_REWIND', 1);
			$res &= Configuration::updateValue('JBW_SCOLLPERPAGE', 0);
			$res &= Configuration::updateValue('JBW_SHOW_CATEGORY', 0);
			$res &= Configuration::updateValue('JBW_SHOW_MEDIA', 1);
			$res &= Configuration::updateValue('JBW_SHOW_INTROTEXT', 1);
			$res &= Configuration::updateValue('JBW_INTROTEXT_CLIMIT', 250);
			$res &= Configuration::updateValue('JBW_SHOW_READMORE', 1);			
			$res &= Configuration::updateValue('JBW_SHOW_CREATED', 1);
			$res &= Configuration::updateValue('JBW_SHOW_COMMENT', 1);
			$res &= Configuration::updateValue('JBW_SHOW_VIEWS', 1);
			$res &= Configuration::updateValue('JBW_SB_ITEM_SHOW', 5);
			$res &= Configuration::updateValue('JBW_SB_SHOW_POPULAR', 1);
			$res &= Configuration::updateValue('JBW_SB_SHOW_RECENT', 1);
			$res &= Configuration::updateValue('JBW_SB_SHOW_LATESTCOMMENT', 1);
			$res &= Configuration::updateValue('JBW_SB_COMMENT_LIMIT', 50);
			$res &= Configuration::updateValue('JBW_SB_SHOW_CATEGORYMENU', 1);
			$res &= Configuration::updateValue('JBW_SB_SHOW_ARCHIVES', 1);
			
			return $res;
		}
		return false;
	}	
	public function uninstall()
	{
		$res = true;
		/* Deletes Module */
		if (parent::uninstall())
		{
			$res &= Configuration::deleteByName('JBW_ITEM_TOTAL');
			$res &= Configuration::deleteByName('JBW_ITEM_SHOW');
			$res &= Configuration::deleteByName('JBW_AUTOPLAY');
			$res &= Configuration::deleteByName('JBW_REWIND');
			$res &= Configuration::deleteByName('JBW_SCOLLPERPAGE');
			$res &= Configuration::deleteByName('JBW_SHOW_CATEGORY');
			$res &= Configuration::deleteByName('JBW_SHOW_MEDIA');
			$res &= Configuration::deleteByName('JBW_SHOW_INTROTEXT');
			$res &= Configuration::deleteByName('JBW_INTROTEXT_CLIMIT');
			$res &= Configuration::deleteByName('JBW_SHOW_READMORE');
			$res &= Configuration::deleteByName('JBW_SHOW_CREATED');
			$res &= Configuration::deleteByName('JBW_SHOW_COMMENT');
			$res &= Configuration::deleteByName('JBW_SHOW_VIEWS');			
			$res &= Configuration::deleteByName('JBW_SB_ITEM_SHOW');			
			$res &= Configuration::deleteByName('JBW_SB_SHOW_POPULAR');			
			$res &= Configuration::deleteByName('JBW_SB_SHOW_RECENT');			
			$res &= Configuration::deleteByName('JBW_SB_SHOW_LATESTCOMMENT');			
			$res &= Configuration::deleteByName('JBW_SB_COMMENT_LIMIT');			
			$res &= Configuration::deleteByName('JBW_SB_SHOW_CATEGORYMENU');			
			$res &= Configuration::deleteByName('JBW_SB_SHOW_ARCHIVES');			
			
			return $res;
		}
		return false;
	}
	
	public function getContent()
	{
		if (Tools::isSubmit('submitConfig'))
		{
			$res = true;			
			$res &= Configuration::updateValue('JBW_ITEM_TOTAL', (int)Tools::getValue('JBW_ITEM_TOTAL'));
			$res &= Configuration::updateValue('JBW_ITEM_SHOW', (int)Tools::getValue('JBW_ITEM_SHOW'));
			$res &= Configuration::updateValue('JBW_AUTOPLAY', (int)Tools::getValue('JBW_AUTOPLAY'));
			$res &= Configuration::updateValue('JBW_REWIND', (int)Tools::getValue('JBW_REWIND'));
			$res &= Configuration::updateValue('JBW_SCROLLPERPAGE', (int)Tools::getValue('JBW_SCROLLPERPAGE'));
			$res &= Configuration::updateValue('JBW_SHOW_CATEGORY', (int)Tools::getValue('JBW_SHOW_CATEGORY'));
			$res &= Configuration::updateValue('JBW_SHOW_MEDIA', (int)Tools::getValue('JBW_SHOW_MEDIA'));
			$res &= Configuration::updateValue('JBW_SHOW_INTROTEXT', (int)Tools::getValue('JBW_SHOW_INTROTEXT'));
			$res &= Configuration::updateValue('JBW_INTROTEXT_CLIMIT', (int)Tools::getValue('JBW_INTROTEXT_CLIMIT'));
			$res &= Configuration::updateValue('JBW_SHOW_READMORE', (int)Tools::getValue('JBW_SHOW_READMORE'));
			$res &= Configuration::updateValue('JBW_SHOW_CREATED', (int)Tools::getValue('JBW_SHOW_CREATED'));
			$res &= Configuration::updateValue('JBW_SHOW_COMMENT', (int)Tools::getValue('JBW_SHOW_COMMENT'));			
			$res &= Configuration::updateValue('JBW_SHOW_VIEWS', (int)Tools::getValue('JBW_SHOW_VIEWS'));			
			$res &= Configuration::updateValue('JBW_SB_ITEM_SHOW', (int)Tools::getValue('JBW_SB_ITEM_SHOW'));			
			$res &= Configuration::updateValue('JBW_SB_SHOW_POPULAR', (int)Tools::getValue('JBW_SB_SHOW_POPULAR'));			
			$res &= Configuration::updateValue('JBW_SB_SHOW_RECENT', (int)Tools::getValue('JBW_SB_SHOW_RECENT'));			
			$res &= Configuration::updateValue('JBW_SB_SHOW_LATESTCOMMENT', (int)Tools::getValue('JBW_SB_SHOW_LATESTCOMMENT'));			
			$res &= Configuration::updateValue('JBW_SB_COMMENT_LIMIT', (int)Tools::getValue('JBW_SB_COMMENT_LIMIT'));			
			$res &= Configuration::updateValue('JBW_SB_SHOW_CATEGORYMENU', (int)Tools::getValue('JBW_SB_SHOW_CATEGORYMENU'));			
			$res &= Configuration::updateValue('JBW_SB_SHOW_ARCHIVES', (int)Tools::getValue('JBW_SB_SHOW_ARCHIVES'));			
		}
		return $this->displayForm();
	}
	
	public function displayForm()
	{	
		$context = Context::getContext();		
		$general_fields = array();
		$general_fields = array(	
			array(
				'type' => 'text',
				'label' => $this->l('Total number of items'),
				'name' => 'JBW_ITEM_TOTAL',
				'class' => 'fixed-width-xl',
				'tab' => 'home'	
			),	
			array(
				'type' => 'text',
				'label' => $this->l('Number of items to show'),
				'name' => 'JBW_ITEM_SHOW',
				'class' => 'fixed-width-xl',
				'tab' => 'home'	
			),				
			array(
				'type' => 'switch',
				'label' => $this->l('Auto Play'),
				'name' => 'JBW_AUTOPLAY',
				'desc' => $this->l('Slider Auto Play.'),
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'home'	
			),	
			array(
				'type' => 'switch',
				'label' => $this->l('Rewind'),
				'name' => 'JBW_REWIND',
				'desc' => $this->l('Slider Rewind.'),
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'home'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Scroll Per Page'),
				'name' => 'JBW_SCROLLPERPAGE',
				'desc' => $this->l('Slider Scroll Per Page.'),
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'home'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Show Category'),
				'name' => 'JBW_SHOW_CATEGORY',		
				'desc' => $this->l('Show or Hide Category.'),				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'home'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Show Media'),
				'name' => 'JBW_SHOW_MEDIA',		
				'desc' => $this->l('Show or Hide Image/video.'),				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'home'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Show Introtext'),
				'name' => 'JBW_SHOW_INTROTEXT',				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'home'	
			),
			array(
				'type' => 'text',
				'label' => $this->l('Introtext character limit'),
				'name' => 'JBW_INTROTEXT_CLIMIT',
				'class' => 'fixed-width-xl',
				'tab' => 'home'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Show Readmore'),
				'name' => 'JBW_SHOW_READMORE',		
				'desc' => $this->l('Show or Hide readmore button.'),				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'home'	
			),			
			array(
				'type' => 'switch',
				'label' => $this->l('Show Created Time'),
				'name' => 'JBW_SHOW_CREATED',		
				'desc' => $this->l('Show or Hide created time.'),				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'home'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Show Comment Number'),
				'name' => 'JBW_SHOW_COMMENT',		
				'desc' => $this->l('Show or Hide comment number.'),				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'home'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Show Views Number'),
				'name' => 'JBW_SHOW_VIEWS',		
				'desc' => $this->l('Show or Hide hit/view number.'),				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'home'	
			),
			array(
				'type' => 'text',
				'label' => $this->l('Number of items show'),
				'name' => 'JBW_SB_ITEM_SHOW',
				'desc' => $this->l('Number of item(popular, recent post, latest comments) to show.'),		
				'class' => 'fixed-width-xl',
				'tab' => 'sidebar'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Show Popular Post'),
				'name' => 'JBW_SB_SHOW_POPULAR',		
				'desc' => $this->l('Show or Hide Popular Post.'),				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'sidebar'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Show Recent Post'),
				'name' => 'JBW_SB_SHOW_RECENT',		
				'desc' => $this->l('Show or Hide Recent Post.'),				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'sidebar'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Show Latest Comment'),
				'name' => 'JBW_SB_SHOW_LATESTCOMMENT',		
				'desc' => $this->l('Show or Hide Latest Comment.'),				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'sidebar'	
			),
			array(
				'type' => 'text',
				'label' => $this->l('Comment character limit'),
				'name' => 'JBW_SB_COMMENT_LIMIT',
				'desc' => $this->l('Maximum number of comment character.'),		
				'class' => 'fixed-width-xl',
				'tab' => 'sidebar'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Show Blog Category Menu'),
				'name' => 'JBW_SB_SHOW_CATEGORYMENU',		
				'desc' => $this->l('Show or Hide Category Menu.'),				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'sidebar'	
			),
			array(
				'type' => 'switch',
				'label' => $this->l('Show Archives'),
				'name' => 'JBW_SB_SHOW_ARCHIVES',		
				'desc' => $this->l('Show or Hide Archives.'),				
				'values'    => array(
					array('id'    => 'active_on','value' => 1,'label' => $this->l('YES')),
					array('id'    => 'active_off','value' => 0,'label' => $this->l('NO'))
				),
				'tab' => 'sidebar'	
			),
		);
		$this->fields_options[0]['form'] = array(
			'tinymce' => true,
			'tabs' => array('home' => 'Home Widget','sidebar' => 'SideBar Widget'),
			'legend' => array('title' => '<span class="label label-info">'.$this->l('Jms Blog Widget Setting').'</span>','icon' => 'icon-cogs',),
			'description' => 'Home page Widget Setting, Sidebar Widget Setting',
			'input' => $general_fields,
			'submit' => array('title' => $this->l('Save'), 'class' => 'button btn btn-primary'),
		);
		
		$helper = new HelperForm();
		$helper->show_toolbar = false;		
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitConfig';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');		
		
		$helper->tpl_vars = array(			
			'base_url' => $this->context->shop->getBaseURL(),			
			'fields_value' => $this->getConfigFieldsValues(),						
		);

		$helper->override_folder = '/';				
		return $helper->generateForm($this->fields_options);
	}
	
	public function getConfigFieldsValues()
	{
		return array(
			'JBW_ITEM_TOTAL' => Tools::getValue('JBW_ITEM_TOTAL', Configuration::get('JBW_ITEM_TOTAL')),
			'JBW_ITEM_SHOW' => Tools::getValue('JBW_ITEM_SHOW', Configuration::get('JBW_ITEM_SHOW')),
			'JBW_AUTOPLAY' => Tools::getValue('JBW_AUTOPLAY', Configuration::get('JBW_AUTOPLAY')),
			'JBW_REWIND' => Tools::getValue('JBW_REWIND', Configuration::get('JBW_REWIND')),
			'JBW_SCROLLPERPAGE' => Tools::getValue('JBW_SCROLLPERPAGE', Configuration::get('JBW_SCROLLPERPAGE')),
			'JBW_SHOW_CATEGORY' => Tools::getValue('JBW_SHOW_CATEGORY', Configuration::get('JBW_SHOW_CATEGORY')),			
			'JBW_SHOW_MEDIA' => Tools::getValue('JBW_SHOW_MEDIA', Configuration::get('JBW_SHOW_MEDIA')),			
			'JBW_SHOW_INTROTEXT' => Tools::getValue('JBW_SHOW_INTROTEXT', Configuration::get('JBW_SHOW_INTROTEXT')),
			'JBW_INTROTEXT_CLIMIT' => Tools::getValue('JBW_INTROTEXT_CLIMIT', Configuration::get('JBW_INTROTEXT_CLIMIT')),		
			'JBW_SHOW_READMORE' => Tools::getValue('JBW_SHOW_READMORE', Configuration::get('JBW_SHOW_READMORE')),						
			'JBW_SHOW_CREATED' => Tools::getValue('JBW_SHOW_CREATED', Configuration::get('JBW_SHOW_CREATED')),						
			'JBW_SHOW_COMMENT' => Tools::getValue('JBW_SHOW_COMMENT', Configuration::get('JBW_SHOW_COMMENT')),						
			'JBW_SHOW_VIEWS' => Tools::getValue('JBW_SHOW_VIEWS', Configuration::get('JBW_SHOW_VIEWS')),						
			'JBW_SB_ITEM_SHOW' => Tools::getValue('JBW_SB_ITEM_SHOW', Configuration::get('JBW_SB_ITEM_SHOW')),						
			'JBW_SB_SHOW_POPULAR' => Tools::getValue('JBW_SB_SHOW_POPULAR', Configuration::get('JBW_SB_SHOW_POPULAR')),						
			'JBW_SB_SHOW_RECENT' => Tools::getValue('JBW_SB_SHOW_RECENT', Configuration::get('JBW_SB_SHOW_RECENT')),						
			'JBW_SB_SHOW_LATESTCOMMENT' => Tools::getValue('JBW_SB_SHOW_LATESTCOMMENT', Configuration::get('JBW_SB_SHOW_LATESTCOMMENT')),						
			'JBW_SB_COMMENT_LIMIT' => Tools::getValue('JBW_SB_COMMENT_LIMIT', Configuration::get('JBW_SB_COMMENT_LIMIT')),
			'JBW_SB_SHOW_CATEGORYMENU' => Tools::getValue('JBW_SB_SHOW_CATEGORYMENU', Configuration::get('JBW_SB_SHOW_CATEGORYMENU')),						
			'JBW_SB_SHOW_ARCHIVES' => Tools::getValue('JBW_SB_SHOW_ARCHIVES', Configuration::get('JBW_SB_SHOW_ARCHIVES')),						
			
		);
	}
	public function getPosts() 
	{
		$this->context = Context::getContext();		
		$id_lang = $this->context->language->id;		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`post_id`,hss.`link_video`, hssl.`image`,hss.`category_id`, hss.`ordering`, hss.`active`, hssl.`title`, hss.`created`, hss.`modified`, hss.`views`,
			hssl.`alias`, hssl.`fulltext`, hssl.`introtext`,hssl.`meta_desc`, hssl.`meta_key`, hssl.`key_ref`, catsl.`title` AS category_name, catsl.`alias` AS category_alias
			FROM '._DB_PREFIX_.'jmsblog_posts hss			
			LEFT JOIN '._DB_PREFIX_.'jmsblog_posts_lang hssl ON (hss.post_id = hssl.post_id)
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang catsl ON (catsl.category_id = hss.category_id)
			WHERE hss.active = 1 AND hssl.id_lang = '.(int)$id_lang.' AND catsl.id_lang = '.(int)$id_lang.			
			' GROUP BY hss.post_id	
			ORDER BY hss.created DESC
			LIMIT 0,'.Configuration::get('JBW_ITEM_TOTAL')
		);
	}
	public function hookHeader($params)
	{
		$this->context->controller->addCSS($this->context->shop->getBaseURL().'modules/jmsblog/views/css/style.css', 'all');
		$this->context->controller->addJS($this->context->shop->getBaseURL().'modules/jmsblog/views/js/categorymenu.js', 'all');				
	}
	
	public function hookdisplayHome($params)
	{	
		$widget_setting = $this->getConfigFieldsValues();
		$posts = $this->getPosts();
		for($i = 0; $i < count($posts); $i++)
		{
			$posts[$i]['introtext'] = JmsBlogHelper::genIntrotext($posts[$i]['introtext'],$widget_setting['JBW_INTROTEXT_CLIMIT']);
			$posts[$i]['comment_count'] = JmsBlogHelper::getCommentCount($posts[$i]['post_id']);
		}
		$this->smarty->assign(
		array(
			'posts' => $posts,	
			'widget_setting' => $widget_setting,
			'image_baseurl' => $this->context->shop->getBaseURL().'modules/jmsblog/views/img/'			
		));
		return $this->display(__FILE__, 'home-widget.tpl');
	}
	
	public function delptree($parent, $level, $tree)
	{
		$context = Context::getContext();
		$id_lang = $context->language->id;
		$sql = 'SELECT a.*,b.title FROM '._DB_PREFIX_.'jmsblog_categories AS a ';
		$sql .= 'INNER JOIN '._DB_PREFIX_.'jmsblog_categories_lang AS b ON a.category_id = b.category_id ';
		$sql .= 'WHERE a.active = 1 AND a.parent ='.$parent.' AND b.id_lang ='.$id_lang;
		$sql .= ' ORDER BY ordering';
		$rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		foreach ($rows as $v)
		{
			$v['level'] = $level + 1;
			$this->treearr[] = $v;
			$this->delptree($v['category_id'], $level + 1, $tree);
		}
	}
	public function getMenuTree($menus)
	{
		$tree = array();
		foreach ($menus as $v)
		{
			$level = 0;
			$v['level'] = $level;
			$this->treearr[] = $v;
			$this->delptree($v['category_id'], $level, $tree);
		}
		$tree = array_slice($this->treearr, 0);
		return $tree;
	}
	public function genMenu($category) 
	{
		if (!in_array($category['category_id'], $this->gens))
		{	
			$this->menu .= '<li';
			if ($category['level'] == 0 && isset($this->child[$category['category_id']]))
			$this->menu .= ' class="haschild"';					
			$this->menu .= '>';
			$params = array(
				'category_id' => $category['category_id'],
				'slug' => $category['alias']
			);
			$_link = JmsBlog::getPageLink('jmsblog-category', $params);
			$this->menu .= '<a href="'.$_link.'">';				
			$this->menu .=	$category['title'];
			if ($category['level'] == 0 && isset($this->child[$category['category_id']]))
				$this->menu .= '<span class="child-icon"></span>';					
			$this->menu .= '</a>';					
			if (isset($this->child[$category['category_id']]))
			{
				$this->menu .='<ul>';				
				$this->genSubs($this->child[$category['category_id']]);					
				$this->menu .='</ul>';	
			}
			$this->menu .= '</li>';
		}	
		$this->gens[] = $category['category_id'];		
	}
	public function genSubs($subs)
	{	
		foreach ($subs as $sub)						
			$this->genMenu($sub);
	}
	public function genCategoryMenu() 
	{
		$context = Context::getContext();
		$id_lang = $context->language->id;
		$rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`category_id` as category_id, hssl.`image`, hss.`ordering`, hss.`active`, hssl.`title`, hss.`parent`, hssl.`alias`		
			FROM '._DB_PREFIX_.'jmsblog_categories hss
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang hssl ON (hss.category_id = hssl.category_id)
			WHERE hssl.id_lang = '.(int)$id_lang.
			' AND hss.`parent` = 0 
			ORDER BY hss.`category_id` ASC'
		);	
		$categories = $this->getMenuTree($rows);	
		foreach ($categories as &$category)
		{	
			$parent = isset($this->child[$category['parent']]) ? $this->child[$category['parent']] : array();
			$parent[] = $category;
			$this->child[$category['parent']] = $parent;
		}			
		foreach ($categories as &$category)
			$this->genMenu($category); 	
		return $this->menu;	
	}
	public function hookdisplayLeftColumn($params)
	{	
		$widget_setting = $this->getConfigFieldsValues();	
		$category_menu = $this->genCategoryMenu();
		$archives = JmsBlogHelper::getArchives();		
		$popularpost = JmsBlogHelper::getPopularPost();
		$latestpost = JmsBlogHelper::getLatestPost();
		$latestcomment = JmsBlogHelper::getLatestComment();
		for($i = 0; $i < count($latestcomment); $i++)
		{
			$latestcomment[$i]['comment'] = JmsBlogHelper::genIntrotext($latestcomment[$i]['comment'], $widget_setting['JBW_SB_COMMENT_LIMIT']);
		}	
		$this->smarty->assign(
			array(
				'category_menu' => $category_menu,	
				'archives' => $archives,			
				'popularpost' => $popularpost,
				'latestpost' => $latestpost,
				'latestcomment' => $latestcomment,
				'widget_setting' => $widget_setting,
				'image_baseurl' => $this->context->shop->getBaseURL().'modules/jmsblog/views/img/'			
			)
		);
		return $this->display(__FILE__, 'sidebar-widget.tpl');
	}	
}
?>
