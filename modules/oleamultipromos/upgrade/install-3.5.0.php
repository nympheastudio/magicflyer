<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_5_0($object, $install = false)
{
	$res = true;
	//error_log('Installing version 3.5.0 of OleaMultiPromo module');
	
	// Add new columns in olea_promo table
	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'oleapromo` LIKE \'attribution_block_cart_line\'';
	$found_columns = Db::getInstance()->ExecuteS($query);
	if (sizeof($found_columns) == 0) {
		$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo` 
	 				ADD COLUMN `attribution_block_cart_line` TINYINT(1) DEFAULT 0';
	 	$res = $res AND Db::getInstance()->Execute($query);
	}
			
	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'oleapromo` LIKE \'attribution_nb_cart_lines_required\'';
	$found_columns = Db::getInstance()->ExecuteS($query);
	if (sizeof($found_columns) == 0) {
	    $query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
	 				ADD COLUMN `attribution_nb_cart_lines_required` INT(10) DEFAULT 0';
	    $res = $res AND Db::getInstance()->Execute($query);
	}
	
	
	if ($res)
		Configuration::updateValue('OLEA_MLTPROM_MODULEUPGRADEOK', "3.5.0");
	return $res;
}