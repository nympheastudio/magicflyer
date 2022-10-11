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

class JmsRow extends ObjectModel
{	
	public $id_prof;
	public $title;	
	public $class;
	public $fullwidth;
	public $active;
	public $ordering;		

	public static $definition = array(
		'table' => 'jmsadv_rows',
		'primary' => 'id_row',		
		'fields' => array(						
			'id_prof'		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'title'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
			'class'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => false, 'size' => 50),
			'fullwidth'		=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'active'		=>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'ordering'		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
		)
	);

	public	function __construct($id_row = null, $id_lang = null)
	{
		parent::__construct($id_row, $id_lang);
	}

	public function add($autodate = true, $null_values = false)
	{
		$res = true;
		$res &= parent::add($autodate, $null_values);		
		return $res;
	}
	
	public function delete()
	{
		$res = true;
		$res &= parent::delete();
		return $res;
	}

}
