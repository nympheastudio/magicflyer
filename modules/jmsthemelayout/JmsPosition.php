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

class JmsPosition extends ObjectModel
{	
	public $id_row;	
	public $title;
	public $class_suffix;
	public $col_lg;
	public $col_md;
	public $col_sm;
	public $col_xs;
	public $active;
	public $ordering;

	public static $definition = array(
		'table' => 'jmsadv_position',
		'primary' => 'id_position',
		'fields' => array(
			'id_row'		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'title'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
			'class_suffix'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 30),
			'col_lg' 		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
			'col_md' 		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
			'col_sm' 		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
			'col_xs' 		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
			'active' 		=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'ordering'		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
		)
	);

	public	function __construct($id_position = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id_position, $id_lang, $id_shop);
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
