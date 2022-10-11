<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_5_1($object, $install = false)
{
	$res = true;
	//error_log('Installing version 3.5.0 of OleaMultiPromo module');
	
	// Add new columns in olea_promo table
	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'oleapromo` LIKE \'attribution_carriers_fdp\'';
	$found_columns = Db::getInstance()->ExecuteS($query);
	if (sizeof($found_columns) == 0) {
		$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo` 
	 				ADD COLUMN `attribution_carriers_fdp` text DEFAULT ""';
	 	$res = $res AND Db::getInstance()->Execute($query);
	}
			
	if ($res)
		Configuration::updateValue('OLEA_MLTPROM_MODULEUPGRADEOK', "3.5.1");
	return $res;
}