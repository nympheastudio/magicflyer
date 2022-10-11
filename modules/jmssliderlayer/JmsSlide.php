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

class JmsSlide extends ObjectModel
{
	public $title;
	public $transition;
	public $slotamount;	
	public $masterspeed;	
	public $delay;
	public $link;
	public $target;
	public $bg_type;	
	public $main_img;
	public $bg_color;
	public $thumb_img;
	public $kenburns;
	public $duration;
	public $ease;
	public $bgrepeat;
	public $bgfit;
	public $bgfitend;
	public $bgposition;
	public $bgpositionend;
	public $ordering;
	public $active;

	public static $definition = array(
		'table' => 'jms_sliderlayer_slides',
		'primary' => 'slide_id',		
		'fields' => array(			
			'active' =>			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'ordering' =>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'transition' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 30),			 
			'slotamount' =>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', ),
			'masterspeed' =>	array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', ),
			'delay' =>			array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', ),
			'title' =>			array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),			
			'link' =>			array('type' => self::TYPE_STRING, 'validate' => 'isUrl', 'size' => 255),
			'target' =>			array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
			'bg_type' =>		array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'main_img' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
			'bg_color' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 20),
			'thumb_img' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
			'kenburns' =>		array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'duration' =>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', ),
			'ease' =>			array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 100),
			'bgrepeat' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 20),
			'bgfit' =>			array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 20),
			'bgfitend' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 20),
			'bgposition' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 30),
			'bgpositionend' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 30),
		)
	);

	public	function __construct($slide_id = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($slide_id, $id_lang, $id_shop);			 
	}

	public function add($autodate = true, $null_values = false)
	{
		$res = true;
		$context = Context::getContext();
		$id_shop = $context->shop->id;
		
		$res = parent::add($autodate, $null_values);
		$res &= Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'jms_sliderlayer` (`slide_id`,`id_shop` )
			VALUES('.(int)$this->id.','.(int)$id_shop.')'
		);
		
		return $res;
	}
	
	public function delete()
	{
		$res = true;
		
		$res &= $this->reOrderPositions();

		$res &= Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'jms_sliderlayer`
			WHERE `slide_id` = '.(int)$this->id
		);		
		
		$res &= parent::delete();
		return $res;
	}

	public function reOrderPositions()
	{
		$slide_id = $this->id;
		$context = Context::getContext();
		$id_shop = $context->shop->id;

		$max = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT MAX(hss.`ordering`) as ordering
			FROM `'._DB_PREFIX_.'jms_sliderlayer_slides` hss, `'._DB_PREFIX_.'jms_sliderlayer` hs
			WHERE hss.`slide_id` = hs.`slide_id` AND hs.`id_shop` = '.(int)$id_shop
		);

		if ((int)$max == (int)$slide_id)
			return true;

		$rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`ordering` as ordering, hss.`slide_id` as slide_id
			FROM `'._DB_PREFIX_.'jms_sliderlayer_slides` hss
			LEFT JOIN `'._DB_PREFIX_.'jms_sliderlayer` hs ON (hss.`slide_id` = hs.`slide_id`)
			WHERE hs.`id_shop` = '.(int)$id_shop.' AND hss.`ordering` > '.(int)$this->ordering
		);

		foreach ($rows as $row)
		{
			$current_slide = new JmsSlide($row['slide_id']);
			--$current_slide->ordering;
			$current_slide->update();
			unset($current_slide);
		}

		return true;
	}
	
	

}
