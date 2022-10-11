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

class JmsProf extends ObjectModel
{
	public $title;
	public $class_suffix;	
	public $profile_type;

	public static $definition = array(
		'table' => 'jmsadv_prof',
		'primary' => 'id_prof',		
		'fields' => array(
			'title'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
			'class_suffix'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),			
			'profile_type'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
		)
	);

	public	function __construct($id_prof = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id_prof, $id_lang, $id_shop);
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
