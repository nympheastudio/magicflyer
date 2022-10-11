<?php
/**
* 2007-2014 PrestaShop
*
* Jms Drop Mega menu module
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2014 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

class JmsMegamenu {
	protected $children = array();
	protected $_items = array();
	protected $gens = array();
	protected $menu = '';
	protected $page_name;
	
	public function __construct()
	{
		$context = Context::getContext();
		$id_shop = $context->shop->id;
		$id_lang = $context->language->id;
		$this->page_name = Dispatcher::getInstance()->getController();
		$sql = 'SELECT *
				FROM '._DB_PREFIX_.'jmsdropmegamenu AS a
				INNER JOIN '._DB_PREFIX_.'jmsdropmegamenu_lang AS b
				ON a.mitem_id = b.mitem_id
				WHERE a.active = 1 AND parent_id = 0 AND (a.id_shop = '.(int)$id_shop.')
				AND b.id_lang = '.(int)$id_lang.
				' ORDER BY a.ordering';
			
		$rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		$jmshelper = new JmsHelper();
		$items = $jmshelper->getMenuTree($rows);		
		$request_uri = $_SERVER['REQUEST_URI'];
		$php_self = $_SERVER['PHP_SELF'];
		$request_params = Tools::substr($request_uri, Tools::strlen($php_self) - 9, Tools::strlen($request_uri) - Tools::strlen($php_self) + 9);
		foreach ($items as &$item)
		{
			switch ($item['type'])
			{
				case 'product' :
					$item['selected'] = ($this->page_name == 'product' && (Tools::getValue('id_product') == (int)$item['value'])) ? true : false;
					$product = new Product((int)$item['value'], true, (int)$id_lang);
					$item['link'] = $product->getLink();
					break;
				case 'category' :
					$item['selected'] = ($this->page_name == 'category' && ((int)Tools::getValue('id_category') == (int)$item['value'])) ? true : false;
					$category = new Category((int)$item['value'], (int)$id_lang);
					$item['link'] = $category->getLink();
					
					break;
				case 'link' :
					$item['link'] = $item['value'];
					if (trim($item['value']) == $request_params) $item['selected'] = true;
					break;
				case 'cms' :
					if (Tools::substr($item['value'], 0, 7) == 'CMS_CAT')
					{
						$id_cmscat = (int)Tools::substr($item['value'].'&controller=cms', 7, Tools::strlen($item['value']) - 7);
						$item['selected'] = ($this->page_name == 'cms' && (Tools::getValue('id_cms_category') == $id_cmscat)) ? true : false;
						$category = new CMSCategory($id_cmscat, (int)$id_lang);
						$item['link'] = $category->getLink();
					}
					else
					{
						$id_cms = (int)Tools::substr($item['value'].'&controller=cms', 3, Tools::strlen($item['value']) - 3);
						$item['selected'] = ($this->page_name == 'cms' && (Tools::getValue('id_cms') == $id_cms)) ? true : false;
						$cms = CMS::getLinks((int)$id_lang, array($id_cms));
						$item['link'] = $cms[0]['link'];
					}
					break;
				case 'manufacturer' :
					$item['selected'] = ($this->page_name == 'manufacturer' && (Tools::getValue('id_manufacturer') == (int)$item['value'])) ? true : false;
					$manufacturer = new Manufacturer((int)$item['value'], (int)$id_lang);
					if (!is_null($manufacturer->id))
					{
						if ((int)Configuration::get('PS_REWRITING_SETTINGS'))
							$manufacturer->link_rewrite = Tools::link_rewrite($manufacturer->name, false);
						else
							$manufacturer->link_rewrite = 0;
						$link = new Link;
						$item['link'] = $link->getManufacturerLink((int)$item['value'], $manufacturer->link_rewrite);
					}
					break;
				case 'supplier' :
					$item['selected'] = ($this->page_name == 'supplier' && (Tools::getValue('id_supplier') == (int)$item['value'])) ? true : false;
					$supplier = new Supplier((int)$item['value'], (int)$id_lang);
					if (!is_null($supplier->id))
					{
						$link = new Link;
						$item['link'] = $link->getSupplierLink((int)$item['value'], $supplier->link_rewrite);
					}
					break;
				case 'module' :
					$item['link'] = '';
					$_arr = explode('-', $item['value']);
					$item['content'] = $this->MNexec($_arr[0], array(), $_arr[1]);
					break;
				case 'seperator' :
					$item['link'] = '#';
					break;
				case 'html' :
					$item['link'] = '';
					$item['content'] = $item['html_content'];
					break;
				case 'jmsblog-categories' :
					$item['selected'] = ($this->page_name == 'module-jmsblog-categories') ? true : false;					
					$link = new Link;
					$item['link'] = 'index.php?fc=module&module=jmsblog&controller=categories';					
					break;	
				case 'jmsblog-singlepost' :	
					$item['link'] = 'index.php?fc=module&module=jmsblog&controller=post&post_id='.$item['value'];
					break;	
				case 'jmsblog-category' :	
					$item['link'] = 'index.php?fc=module&module=jmsblog&controller=category&category_id='.$item['value'];
					break;
				case 'jmsblog-tag' :	
					$item['link'] = 'index.php?fc=module&module=jmsblog&controller=tag&tag='.$item['value'];
					break;
				case 'jmsblog-archive' :	
					$item['link'] = 'index.php?fc=module&module=jmsblog&controller=archive&archive='.$item['value'];
					break;		
			}			
			if ((int)$item['show_title'] == 1)
				$item['show_title'] = 1;
			else 
				$item['show_title'] = 0;
			//}
			$item['fullwidth'] = (int)$item['fullwidth'];
			$parent = isset($this->children[$item['parent_id']]) ? $this->children[$item['parent_id']] : array();
			$parent[] = $item;
			$this->children[$item['parent_id']] = $parent;
			
			$this->_items[$item['mitem_id']] = $item;
		}
				
		foreach ($items as &$item)
		{
			$item['mega'] = 0;
			$item['dropdown'] = 0;
			if ((isset($this->children[$item['mitem_id']])))
				$item['dropdown'] = 1;
				$item['mega'] = $item['group'] || $item['dropdown'];
				$item['title'] = htmlspecialchars($item['name'], ENT_COMPAT, 'UTF-8', false);
				$this->_items[$item['mitem_id']] = $item;
		}				
		
	}

	public function render()
	{
		$this->menu = '';
		$this->beginmenu();
		$this->nav();
		$this->endmenu();
		return $this->menu;
	}
	public static function MNexec($hook_name, $hookArgs = array(), $module_name = null)
	{		
		if (empty($module_name) || !Validate::isHookName($hook_name))			
			die(Tools::displayError());
		
		$context = Context::getContext();
		if (!isset($hookArgs['cookie']) || !$hookArgs['cookie'])
			$hookArgs['cookie'] = $context->cookie;
		if (!isset($hookArgs['cart']) || !$hookArgs['cart'])
			$hookArgs['cart'] = $context->cart;

		if (!($moduleInstance = Module::getInstanceByName($module_name)))
			return;
		$retro_hook_name = Hook::getRetroHookName($hook_name);
		
		$hook_callable = is_callable(array($moduleInstance, 'hook'.$hook_name));
		$hook_retro_callable = is_callable(array($moduleInstance, 'hook'.$retro_hook_name));
		$output = '';
		
		if (($hook_callable || $hook_retro_callable) && Module::preCall($moduleInstance->name))
		{
			if ($hook_callable)
				$output = $moduleInstance->{'hook'.$hook_name}($hookArgs);
			else if ($hook_retro_callable)
				$output = $moduleInstance->{'hook'.$retro_hook_name}($hookArgs);
		}
		return $output;
	}
	public function beginmenu()
	{
		$this->menu .= '<div id="jmsmenuwrap" class="jms-megamenu"><ul id="jms-megamenu" class="nav level0">';
	}
	public function endmenu()
	{
		$this->menu .= '</ul></div>';
	}
	
	public function nav()
	{
		$items = $this->_items;
		foreach ($items as &$item)
			$this->genItem($item); 
			
	}
	public function curPageURL()
	{
		$pageURL = 'http';
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') $pageURL .= 's';
		$pageURL .= '://';
		if ($_SERVER['SERVER_PORT'] != '80')
		$pageURL .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
		else
		$pageURL .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		return $pageURL;
	}
	
	public function genClass($item)
	{
		$cls = '';
		if (isset($this->children[$item['mitem_id']]))
		$cls .= ' haschild';
		if ($item['group'])
		$cls .= ' group';
		if (isset($item['selected']) && $item['selected'])
		$cls .= ' active';
		if ($item['mclass'])
		$cls .= ' '.$item['mclass'];
		if ($item['fullwidth'] && $item['level'] == 0)
		$cls .= ' fw';
		return $cls;
	}
	public function genItem($item)
	{
		if (!in_array($item['mitem_id'], $this->gens))
		{
			$cls = $this->genClass($item);
			$cls_str = '';
			if ($cls != '') $cls_str = ' class="'.$cls.'"';
			if ($item['level'] == 0)
			$this->menu .= '<li data-cols="'.$item['cols'].'"'.$cls_str.'>';
			if ($item['level'] > 0)
			$this->menu .= '<li'.$cls_str.'>';
			if ($item['show_title'])
			{
				$this->menu .= '<a href="'.str_replace('&', '&amp;', $item['link']).'" target="'.$item['target'].'">';
				if ($item['menu_icon'] == 1)
					$this->menu .= '<span class="'.$item['icon_class'].'"></span>';
				$this->menu .=	$item['name'];
				if ($item['level'] == 0 && isset($this->children[$item['mitem_id']]))
					$this->menu .= '<span class="mega-child-icon"></span>';
					
				$this->menu .= '</a>';
			} 
			else
			{
				if ($item['menu_icon'] == 1)
				{
					$this->menu .= '<a href="'.str_replace('&', '&amp;', $item['link']).'">';
					if ($item['menu_icon'] == 1)
						$this->menu .= '<span class="'.$item['icon_class'].'"></span>';
					$this->menu .= '</a>';
				}
			}
			if ($item['type'] == 'module' || $item['type'] == 'html')
				$this->menu .= '<div class="mod-content">'.$item['content'].'</div>';
			if (isset($this->children[$item['mitem_id']]))
			{
				if ($item['level'] == 0) $this->beginDropdown($item['cols']);
				if ($item['level'] >= 1) $this->beginSub($item);			
				$this->genSubs($this->children[$item['mitem_id']], $item);
				if ($item['level'] >= 1) $this->endSub();
				if ($item['level'] == 0) $this->endDropdown();
			}
			$this->menu .= '</li>';
		}	
		$this->gens[] = $item['mitem_id'];
	}
	public function beginDropdown($cols)
	{
		if ($cols == 1) $cls = 'dropdown-inner no-mega';
		else $cls = 'dropdown-inner';
		$this->menu .= '<div class="dropdown-menu"><div class="'.$cls.'">';
	}
	public function endDropdown()
	{
		$this->menu .= '</div></div>';
	}
	public function beginRow()
	{
		$this->menu .= '<div class="row">';
	}
	public function endRow()
	{
		$this->menu .= '</div>';
	}
	public function beginCol($item, $ncols)
	{
		$ul_class = 'mega-nav level'.$item['level'];
		if ((int)$item['width'] > 0)
			$col_sm = $item['width'];
		else
			$col_sm = 12 / $ncols;
					
		if ($item['level'] == 1) $this->menu .= '<div class="col-sm-'.$col_sm.'">';
		$this->menu .= '<ul class="'.$ul_class.'">';
	}
	public function endCol($item)
	{
		$this->menu .= '</ul>';
		if ($item['level'] == 1) $this->menu .= '</div>';
	}
	
	public function genSubs($subs, $_parent)
	{
		$cols = (int)$_parent['cols'];
		$i = 0;
		foreach ($subs as $sub)
		{
			$level = $sub['level'];
			if ($i % $cols == 0 && ($level == 1))	$this->beginRow();
			if (($level == 1)) $this->beginCol($sub, $cols);									
			$this->genItem($sub);
			if (($level == 1)) $this->endCol($sub);
			if (((($i % $cols == ($cols - 1)) || ($i == count($subs) - 1)) && ($level == 1)))	$this->endRow();
			$i++;
		}
	}

	public function beginSub()
	{
		$this->menu .= '<ul>';
	}
	public function endSub()
	{
		$this->menu .= '</ul>';
	}
}