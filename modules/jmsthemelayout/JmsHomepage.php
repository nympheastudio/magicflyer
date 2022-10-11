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

class JmsHomepage extends ObjectModel
{
	public $title;
	public $id_header;
	public $id_homebody;
	public $id_footer;
	public $css_file;
	public $font_url;
	public $ordering;

	public static $definition = array(
		'table' => 'jmsadv_homepages',
		'primary' => 'id_homepage',		
		'fields' => array(
			'title'			=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 100),
			'id_header'		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'id_homebody'	=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'id_footer'		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'css_file'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 30),
			'font_url'		=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
			'ordering'		=>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
		)
	);

	public	function __construct($id_homepage = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id_homepage, $id_lang, $id_shop);
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
