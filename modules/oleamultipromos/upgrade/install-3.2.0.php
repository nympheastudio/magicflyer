<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_2_0($object, $install = false)
{
	$res = true;
	error_log('Installing version 3.2.0 of OleaMultiPromo module');
	
	// Add new columns in cart table
	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'oleapromo` LIKE \'attribution_maxamount\'';
	$found_columns = Db::getInstance()->ExecuteS($query);
	if (sizeof($found_columns) == 0) {
		$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo` 
	 				ADD COLUMN `attribution_maxamount` decimal(10,2)';
	 	$res = $res AND Db::getInstance()->Execute($query);
	}
		
	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'oleapromo` LIKE \'attribution_maxamount_id_currency\'';
	$found_columns = Db::getInstance()->ExecuteS($query);
	if (sizeof($found_columns) == 0) {
		$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo` 
	 				ADD COLUMN `attribution_maxamount_id_currency` INT(10)';
	 	$res = $res AND Db::getInstance()->Execute($query);
	}
		
	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'oleapromo` LIKE \'attribution_maxamount_withtaxes\'';
	$found_columns = Db::getInstance()->ExecuteS($query);
	if (sizeof($found_columns) == 0) {
		$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo` 
	 				ADD COLUMN `attribution_maxamount_withtaxes` TINYINT(1)';
	 	$res = $res AND Db::getInstance()->Execute($query);
	}

	Configuration::updateValue('OLEA_MLTPROM_MODULEUPGRADEOK', "3.2.0");
	return $res;
}