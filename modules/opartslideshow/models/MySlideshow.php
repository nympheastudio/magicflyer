<?php
class MySlideshow  extends ObjectModel
{
 	/** @var string Name */
	public $name;
	public $active;
	public $width;
	public $height;
	public $spw;
	public $sph;
	public $delay;
	public $sDelay;
	public $opacity;
	public $titleSpeed;
	public $effect;
	public $navigation;
	public $links;
	public $hoverpause;
	public $home;
	public $hook;
	public $showOnCat;
	public $showOnProd;
	public $showOnCms;
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'opartslideshow_slideshow',
		'primary' => 'id_opartslideshow_slideshow',
		'multilang' => true,
		'fields' => array(
			// Lang fields
			'name' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 64),
			'active' => 	array('type' => self::TYPE_BOOL, 'valide'=>'isBool'),
			'width' => 	array('type' => self::TYPE_INT, 'valide'=>'isInt','required' => true),
			'height' => array('type' => self::TYPE_INT, 'valide'=>'isInt','required' => true),
			'spw' => array('type' => self::TYPE_INT, 'valide'=>'isInt','required' => true),
			'sph' => array('type' => self::TYPE_INT, 'valide'=>'isInt','required' => true),
			'delay' => array('type' => self::TYPE_INT, 'valide'=>'isInt','required' => true),
			'sDelay' => array('type' => self::TYPE_INT, 'valide'=>'isInt','required' => true),
			'opacity' => array('type' => self::TYPE_FLOAT, 'valide'=>'isFloat','required' => true),
			'effect' => array('type' => self::TYPE_INT, 'valide'=>'isInt','required' => true),
			'titleSpeed' => array('type' => self::TYPE_INT, 'valide'=>'isInt','required' => true),
			'navigation' => array('type' => self::TYPE_BOOL, 'valide'=>'isBool','required' => true),
			'links' => array('type' => self::TYPE_BOOL, 'valide'=>'isBool','required' => true),
			'hoverpause' => array('type' => self::TYPE_BOOL, 'valide'=>'isBool','required' => true),
			'home' => array('type' => self::TYPE_BOOL, 'valide'=>'isBool','required' => true),
			'hook' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 64),
			'showOnCat' => array('type' => self::TYPE_BOOL, 'valide'=>'isBool','required' => true),
			'showOnProd' => array('type' => self::TYPE_BOOL, 'valide'=>'isBool','required' => true),
			'showOnCms' => array('type' => self::TYPE_BOOL, 'valide'=>'isBool','required' => true)
		),
	);
}