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

class JmsBlock extends ObjectModel
{	
	public $id_position;
	public $title;
	public $show_title;
	public $html_content;
	public $block_type;
	public $hook_name;	
	public $module_name;
	public $active;
	public $ordering;

	public static $definition = array(
		'table' => 'jmsadv_blocks',
		'primary' => 'id_block',
		'multilang' => true,
		'fields' => array(
			'id_position'	=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'title'			=>	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
			'show_title'	=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'html_content'	=>	array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000),
			'block_type'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 30),
			'hook_name'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => false, 'size' => 30),			
			'module_name'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => false, 'size' => 100),
			'active'		=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'ordering'		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
		)
	);

	public	function __construct($id_block = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id_block, $id_lang, $id_shop);
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
		$res &= parent::delete();
		return $res;
	}
}
