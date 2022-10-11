<?php

class Shop extends ShopCore
{
	
	protected static function init()
	{
		parent::init();
	
		// Oleacorner for module MaxiPromo
		Shop::addTableAssociation('oleapromo', array('type' => 'shop'));
		// End Oleacorner
	
	}
	
	// Oleacorner : function before 1.5.2
	public static function addTableAssociation($table_name, $table_details)
	{
		if (version_compare ('1.5.3', _PS_VERSION_) <= 0)
			return parent::addTableAssociation($table_name, $table_details);
	
		else
		{
			if (!isset(Shop::$asso_tables[$table_name]))
				Shop::$asso_tables[$table_name] = $table_details;
			else
				return false;
			return true;
		}
			
	}
}

