<?php
class MyImage  extends ObjectModel
{
 	/** @var string Name */
	public $id_opartslideshow_slideshow;
	public $targeturl;
	public $active;
	public $name;
	public $description;
	public $filename;
	public $position;
	
	public function __construct($id = null, $id_lang = null, $id_shop = null) {
		$this->filename="image_".time().".jpg";
		parent::__construct($id, $id_lang, $id_shop);
	}
	
	public function add($autodate = true, $null_values = false)
	{
		$this->position = $this->getLastPosition((int)$this->id_opartslideshow_slideshow);
		return parent::add($autodate, true);
	}
	
	public function delete()
	{
		if (parent::delete())
			return $this->cleanPositions($this->id_opartslideshow_slideshow);
		return false;
	}
	
	public static function getLastPosition($id_opartslideshow_slideshow)
	{
		$sql = '
		SELECT MAX(position) + 1
		FROM `'._DB_PREFIX_.'opartslideshow_slideshow_image`
		WHERE `id_opartslideshow_slideshow` = '.(int)$id_opartslideshow_slideshow;
	
		return (Db::getInstance()->getValue($sql,true));
	}
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'opartslideshow_slideshow_image',
		'primary' => 'id_opartslideshow_slideshow_image',
		'multilang' => true,
		'fields' => array(
			'id_opartslideshow_slideshow' => array('type' => self::TYPE_INT, 'validate'=>'isInt'),
			'targeturl' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAnything', 'required' => false,  'size' => 255),
			'description' => 	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => false, 'size' => 500),
			'active' => 	array('type' => self::TYPE_BOOL, 'valide'=>'isBool'),
			'name' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 64),
			'filename' => array('type' => self::TYPE_STRING),
			'position' => array('type' => self::TYPE_INT)		
		),
	);
	
	
	public function updatePosition($way, $position)
	{
		if (!$res = Db::getInstance()->executeS('
				SELECT i.`id_opartslideshow_slideshow_image`, i.`position`, i.`id_opartslideshow_slideshow`
				FROM `'._DB_PREFIX_.'opartslideshow_slideshow_image` i
				WHERE i.`id_opartslideshow_slideshow` = '.(int)$this->id_opartslideshow_slideshow.'
				ORDER BY i.`position` ASC'
		))
			return false;
	
		foreach ($res as $object)
			if ((int)$object['id_opartslideshow_slideshow_image'] == (int)$this->id)
			$moved_object = $object;
	
		if (!isset($moved_object) || !isset($position))
			return false;
	
		return (Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.'opartslideshow_slideshow_image`
				SET `position`= `position` '.($way ? '- 1' : '+ 1').'
				WHERE `position`
				'.($way
						? '> '.(int)$moved_object['position'].' AND `position` <= '.(int)$position
						: '< '.(int)$moved_object['position'].' AND `position` >= '.(int)$position).'
				AND `id_opartslideshow_slideshow`='.(int)$moved_object['id_opartslideshow_slideshow'])
				&& Db::getInstance()->execute('
						UPDATE `'._DB_PREFIX_.'opartslideshow_slideshow_image`
						SET `position` = '.(int)$position.'
						WHERE `id_opartslideshow_slideshow_image` = '.(int)$moved_object['id_opartslideshow_slideshow_image'].'
						AND `id_opartslideshow_slideshow`='.(int)$moved_object['id_opartslideshow_slideshow']));
	}
	
	public static function cleanPositions($id_opartslideshow_slideshow)
	{
		$sql = '
		SELECT `id_opartslideshow_slideshow_image`
		FROM `'._DB_PREFIX_.'opartslideshow_slideshow_image`
		WHERE `id_opartslideshow_slideshow` = '.(int)$id_opartslideshow_slideshow.'
		ORDER BY `position`';
	
		$result = Db::getInstance()->executeS($sql);
	
		for ($i = 0, $total = count($result); $i < $total; ++$i)
		{
			$sql = 'UPDATE `'._DB_PREFIX_.'opartslideshow_slideshow_image`
			SET `position` = '.(int)$i.'
			WHERE `id_opartslideshow_slideshow` = '.(int)$id_opartslideshow_slideshow.'
			AND `id_opartslideshow_slideshow_image` = '.(int)$result[$i]['id_opartslideshow_slideshow_image'];
			Db::getInstance()->execute($sql);
		}
		return true;
	}
	
	public function copyFromPost()
	{
		/* Classical fields */
		foreach ($_POST AS $key => $value)
			if (key_exists($key, $this) AND $key != 'id_'.$this->table)
			$this->{$key} = $value;
	
		/* Multilingual fields */
		if (sizeof($this->fieldsValidateLang))
		{
			$languages = Language::getLanguages(false);
			foreach ($languages AS $language)
				foreach ($this->fieldsValidateLang AS $field => $validation)
				if (isset($_POST[$field.'_'.(int)($language['id_lang'])]))
				$this->{$field}[(int)($language['id_lang'])] = $_POST[$field.'_'.(int)($language['id_lang'])];
		}
	}
}