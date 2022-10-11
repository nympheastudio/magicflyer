<?php
/**
* 2007-2014 PrestaShop
*
* Slider Layer module for prestashop
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2014 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

class JmsLayer extends ObjectModel
{
	public $title;
	public $slide_id;
	public $layer_class;
	public $parallax_class;
	public $data_x;	
	public $data_y;	
	public $speed;
	public $start;
	public $easing;	
	public $endspeed;
	public $end;
	public $endeasing;
	public $incoming_class;
	public $outgoing_class;
	public $customin;
	public $customout;
	public $special_class;
	public $splitin;
	public $splitout;
	public $elementdelay;
	public $endelementdelay;
	public $data_type;
	public $layer_img;
	public $img_ww;
	public $img_hh;
	public $layer_video;
	public $video_width;
	public $video_height;
	public $video_autoplay;
	public $video_fullscreen;
	public $layer_text;	
	public $ordering;
	public $active;

	public static $definition = array(
		'table' => 'jms_sliderlayer_layers',
		'primary' => 'layer_id',			
		'fields' => array(			
			'active' =>			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'ordering' =>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'title' =>			array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
			'slide_id' =>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'layer_class' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),						 
			'parallax_class' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 30),
			'data_x' =>			array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 20 ),
			'data_y' =>			array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 20 ),
			'speed' =>			array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt' ),
			'start' =>			array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt' ),
			'easing' =>			array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
			'endspeed' =>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', ),			
			'end' =>			array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', ),
			'endeasing' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),	
			'incoming_class' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 20),
			'outgoing_class' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 20),
			'special_class' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
			'customin' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 300),
			'customout' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 300),			
			'splitin' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 20),	
			'splitout' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 20),
			'elementdelay' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat' ),
			'endelementdelay' =>array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat' ),
			'linktoslide' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 20 ),	
			'data_type' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 30),
			'layer_img' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
			'img_ww' =>			array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt' ),
			'img_hh' =>			array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt' ),
			'layer_video' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 50),
			'video_width' =>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt' ),
			'video_height' =>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt' ),
			'video_autoplay' =>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'video_fullscreen' =>array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),			
			'layer_text' =>		array('type' => self::TYPE_HTML,'validate' => 'isCleanHtml', 'size' => 4000),
			
		)
	);

	public	function __construct($layer_id = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($layer_id, $id_lang, $id_shop);			 
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
		
		$res &= Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'jms_sliderlayer_layers`
			WHERE `layer_id` = '.(int)$this->id
		);
		$res &= parent::delete();
		return $res;
	}

}
