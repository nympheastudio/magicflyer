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

class JmsCategory extends ObjectModel
{
	public $title;
	public $alias;
	public $description;
	public $image;
	public $active;	
	public $ordering;
	public $parent;
	public $category_id;

	public static $definition = array(
		'table' => 'jmsblog_categories',
		'primary' => 'category_id',
		'multilang' => true,
		'fields' => array(
			'active' 		=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'ordering' 		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'parent' 		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'description' 	=>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000),
			'title' 		=>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
			'alias' 		=>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
			'image' 		=>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 255),
		)
	);

	public	function __construct($category_id = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($category_id, $id_lang, $id_shop);
	}

	public function add($autodate = true, $null_values = false)
	{
		$res = true;
		$res = parent::add($autodate, $null_values);
		return $res;
	}
	
	public function delete()
	{
		$res = true;

		$images = $this->image;
		foreach ($images as $image)
		{
			if (preg_match('/sample/', $image) === 0)
				if ($image && file_exists(dirname(__FILE__).'/views/img/'.$image)) 
				{
					$res &= @unlink(dirname(__FILE__).'/views/img/'.$image);
					$res &= @unlink(dirname(__FILE__).'/views/img/resized_'.$image);
					$res &= @unlink(dirname(__FILE__).'/views/img/sthumb_'.$image);
					$res &= @unlink(dirname(__FILE__).'/views/img/thumb_'.$image);
				}	
		}
		$res &= parent::delete();
		return $res;
	}

	public function reOrderPositions()
	{	
		$rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`ordering` as ordering, hss.`category_id` as category_id
			FROM `'._DB_PREFIX_.'jmsblog_categories` hss
			WHERE hss.`ordering` > '.(int)$this->ordering
		);
		foreach ($rows as $row)
		{
			$current_item = new JmsCategory($row['category_id']);
			--$current_item->position;
			$current_item->update();
			unset($current_item);
		}
		return true;
	}
}
