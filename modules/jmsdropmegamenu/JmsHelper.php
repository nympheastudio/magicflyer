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

class JmsHelper 
{
	var $treearr = array();
	public function tree($parent, $ident, $tree, $current_id = 0)
	{
		$context = Context::getContext();
		$id_lang = $context->language->id;
		$sql = 'SELECT a.*,b.name FROM '._DB_PREFIX_.'jmsdropmegamenu AS a ';
		$sql .= 'INNER JOIN '._DB_PREFIX_.'jmsdropmegamenu_lang AS b ON a.mitem_id = b.mitem_id ';
		$sql .= 'WHERE a.parent_id ='.(int)$parent.' AND b.id_lang ='.$id_lang;
		if ($current_id != 0)
		$sql .= ' AND a.mitem_id != '.$current_id;
		$sql .= ' ORDER BY ordering';
		$rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		foreach ($rows as $v)
		{			
			$v['name'] = $ident.'.&nbsp;&nbsp;<sup>L</sup>&nbsp;'.$v['name'];
			$v['name'] = str_replace('.&nbsp;&nbsp;<sup>L</sup>&nbsp;', '.&nbsp;&nbsp;&nbsp;&nbsp;', $v['name']);
			$x = strrpos($v['name'], '.&nbsp;&nbsp;&nbsp;&nbsp;');
			$v['name'] = substr_replace($v['name'], '.&nbsp;&nbsp;<sup>L</sup>&nbsp;', $x, 7);
			$this->treearr[] = $v;
			$this->tree($v['mitem_id'], $ident.'.&nbsp;&nbsp;<sup>L</sup>&nbsp;', $tree, $current_id);
		}
	}
	public function getListTree($menus)
	{
		$tree = array();
		foreach ($menus as $v)
		{
			$ident = '';
			$this->treearr[] = $v;
			$this->delptree($v['mitem_id'], $ident, $tree);
		}
		$tree = array_slice($this->treearr, 0);
		return $tree;
	}
	public function delptree($parent, $level, $tree)
	{
		$context = Context::getContext();
		$id_lang = $context->language->id;
		$sql = 'SELECT a.*,b.name FROM '._DB_PREFIX_.'jmsdropmegamenu AS a ';
		$sql .= 'INNER JOIN '._DB_PREFIX_.'jmsdropmegamenu_lang AS b ON a.mitem_id = b.mitem_id ';
		$sql .= 'WHERE a.parent_id ='.$parent.' AND b.id_lang ='.$id_lang;
		$sql .= ' ORDER BY ordering';
		$rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		foreach ($rows as $v)
		{
			$v['level'] = $level + 1;
			$this->treearr[] = $v;
			$this->delptree($v['mitem_id'], $level + 1, $tree);
		}
	}
	public function getMenuTree($menus)
	{
		$tree = array();
		foreach ($menus as $v)
		{
			$level = 0;
			$v['level'] = 0;
			$this->treearr[] = $v;
			$this->delptree($v['mitem_id'], $level, $tree);
		}
		$tree = array_slice($this->treearr, 0);
		return $tree;
	}	
	public function getParentList($current_parent = 0, $mitem_id = 0)
	{
		$context = Context::getContext();
		$id_shop = $context->shop->id;
		$id_lang = $context->language->id;
		$sql = 'SELECT *';
		$sql .= '	FROM '._DB_PREFIX_.'jmsdropmegamenu AS a';
		$sql .= '	INNER JOIN '._DB_PREFIX_.'jmsdropmegamenu_lang AS b	';
		$sql .= '	ON a.mitem_id = b.mitem_id 	';
		$sql .= '	WHERE parent_id = 0 AND a.mitem_id != '.$mitem_id.' AND (a.id_shop = '.(int)$id_shop.')';
		$sql .= '	AND b.id_lang = '.(int)$id_lang;
		$sql .= ' ORDER BY a.ordering';
		$rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		$tree = array();
		$this->treearr[] = array('mitem_id' => 0, 'name' => '--SELECT PARENT--');
		foreach ($rows as $v)
		{
			$ident = '';
			$this->treearr[] = $v;
			$this->tree($v['mitem_id'], $ident, $tree, $mitem_id);
		}
		$tree = array_slice($this->treearr, 0);				
		return $tree;		
	}	
	public static function orderUpIcon($i, $condition = true, $link)
	{
		if ($condition)
		$html = '<a title = "Move Up" onclick = "changeOrder(\'cb'.$i.'\',\'orderup\',\''.$link.'\')" class = "jgrid"><span class = "state uparrow"></span></a>';
		else
		$html = '&#160;';
		return $html;
	}
	public static function orderDownIcon($i, $condition = true, $link)
	{
		if ($condition)
			$html = '<a title = "Move Down" onclick = "changeOrder(\'cb'.$i.'\',\'orderdown\',\''.$link.'\')" class = "jgrid"><span class = "state downarrow"></span></a>';
		else
			$html = '&#160;';
		return $html;
	}
	
	public static function reorder($mitem_id, $direction)
	{
		$sql = 'SELECT ordering FROM '._DB_PREFIX_.'jmsdropmegamenu ';
		$sql .= 'WHERE mitem_id =  $mitem_id ORDER BY ordering ASC ';
		$ordering = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
		
		$sql = 'UPDATE '._DB_PREFIX_.'jmsdropmegamenu ';
		if ($direction == 'orderdown') 
			$new_ordering = $ordering + 1;
		
		else
			if ($ordering >= 1)
				$new_ordering = $ordering - 1;
			else 
				$new_ordering = 0;
			
		
		$sql .= ' SET ordering = '.$new_ordering;
		$sql .= ' WHERE mitem_id = '.$mitem_id;
		
		$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
		return $res;
	}
	
	public function getCMSPages()
	{
		$id_shop = (int)Context::getContext()->shop->id;
		$id_lang = (int)Context::getContext()->language->id;

		$sql = 'SELECT c.`id_cms`, cl.`meta_title`, cl.`link_rewrite`
			FROM `'._DB_PREFIX_.'cms` c
			INNER JOIN `'._DB_PREFIX_.'cms_shop` cs
			ON (c.`id_cms` = cs.`id_cms`)
			INNER JOIN `'._DB_PREFIX_.'cms_lang` cl
			ON (c.`id_cms` = cl.`id_cms`)
			WHERE cs.`id_shop` = '.(int)$id_shop.'
			AND cl.`id_lang` = '.(int)$id_lang.'
			AND c.`active` = 1
			ORDER BY `position`';
		
		return Db::getInstance()->executeS($sql);
	}
	public function getModules()
	{
		$this->context = Context::getContext();
		$id_shop = $this->context->shop->id;

		return Db::getInstance()->ExecuteS('
		SELECT m.*
		FROM `'._DB_PREFIX_.'module` m
		JOIN `'._DB_PREFIX_.'module_shop` ms ON (m.`id_module` = ms.`id_module` AND ms.`id_shop` = '.(int)($id_shop).')
		');
	}	
	public function getManufacturers()
	{
		$id_lang = (int)Context::getContext()->language->id;
		$manufacturers = Manufacturer::getManufacturers(false, $id_lang);
		return $manufacturers;
	}
	public function getSuppliers()
	{
		$id_lang = (int)Context::getContext()->language->id;
		$suppliers = Supplier::getSuppliers(false, $id_lang);
		return $suppliers;
	}
	public static function checkModuleCallable($id_module) 
	{
		if (!($moduleInstance = Module::getInstanceByID($id_module)))
			return false;
		$hooks = array();
		$hookAssign = array('rightcolumn','leftcolumn','home','top','footer');
		foreach ($hookAssign as $hook)
		{
			$retro_hook_name = Hook::getRetroHookName($hook);
			if (is_callable(array($moduleInstance, 'hook'.$hook)) || is_callable(array($moduleInstance, 'hook'.$retro_hook_name)))
				$hooks[] = $retro_hook_name;
		}
		$results = self::getHookByArrName( $hooks );
		return $results;

	}
	public static function getHookByArrName($arrName)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_hook`, `name`
		FROM `'._DB_PREFIX_.'hook` 
		WHERE `name` IN (\''.implode("','", $arrName).'\')');
		return $result;
	}
	public function getJmsBlogPost() 
	{
		$id_shop = (int)Context::getContext()->shop->id;
		$id_lang = (int)Context::getContext()->language->id;

		$sql = 'SELECT pl.`title`, p.`post_id`
			FROM `'._DB_PREFIX_.'jmsblog_posts` p
			INNER JOIN `'._DB_PREFIX_.'jmsblog_posts_lang` pl
			ON (p.`post_id` = pl.`post_id`)			
			WHERE pl.`id_lang` = '.(int)$id_lang.'			
			AND p.`active` = 1
			ORDER BY p.`post_id` DESC';
		//echo $sql; exit;
		return Db::getInstance()->executeS($sql);
	}
	public function getJmsBlogCategory() 
	{
		$id_shop = (int)Context::getContext()->shop->id;
		$id_lang = (int)Context::getContext()->language->id;

		$sql = 'SELECT cl.`title`, c.`category_id`
			FROM `'._DB_PREFIX_.'jmsblog_categories` c
			INNER JOIN `'._DB_PREFIX_.'jmsblog_categories_lang` cl
			ON (c.`category_id` = cl.`category_id`)			
			WHERE cl.`id_lang` = '.(int)$id_lang.'			
			AND c.`active` = 1
			ORDER BY c.`category_id` DESC';
		//echo $sql; exit;
		return Db::getInstance()->executeS($sql);
	}
}
