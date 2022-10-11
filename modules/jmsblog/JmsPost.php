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

class JmsPost extends ObjectModel
{
	public $title;
	public $alias;
	public $introtext;
	public $fulltext;	
	public $meta_desc;
	public $meta_key;
	public $key_ref;
	public $image;			
	public $active;	
	public $ordering;
	public $category_id;
	public $created;
	public $modified;
	public $link_video;
	public $tags;
	public $views;

	public static $definition = array(
		'table' => 'jmsblog_posts',
		'primary' => 'post_id',
		'multilang' => true,
		'fields' => array(			
			'active' 		=>		array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'ordering' 		=>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),			
			'category_id' 	=>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'created' 		=>		array('type' => self::TYPE_DATE),
			'modified' 		=>		array('type' => self::TYPE_DATE),			
			'views' 		=>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
			'introtext' 	=>		array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 4000),
			'fulltext' 		=>		array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 4000),
			'title' 		=>		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),			
			'alias' 		=>		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),			
			'image' 		=>		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 255),			
			'meta_desc' 	=>		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 500),
			'meta_key' 		=>		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 500),
			'key_ref' 		=>		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 200),
			'tags' 			=>		array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 4000),
			'link_video'	=>		array('type' => self::TYPE_HTML, 'size' => 1000),
		)
	);

	public	function __construct($post_id = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($post_id, $id_lang, $id_shop);
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
				}	
		}						
		$res &= parent::delete();
		return $res;
	}
}