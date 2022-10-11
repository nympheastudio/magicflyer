<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_5_4($object, $install = false)
{
	$res = true;

	// Add new columns in olea_promo table
	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'oleapromo` LIKE \'attribution_id_extcartrulefamily\'';
	$found_columns = Db::getInstance()->ExecuteS($query);
	if (sizeof($found_columns) == 0) {
		$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
	 				ADD COLUMN `attribution_id_extcartrulefamily` INT(10) DEFAULT 0';
	 	$res = $res AND Db::getInstance()->Execute($query);
	}

	// Add new columns in olea_promo table
	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'oleapromo` LIKE \'mail_id_extcartrulefamily\'';
	$found_columns = Db::getInstance()->ExecuteS($query);
	if (sizeof($found_columns) == 0) {
		$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
	 				ADD COLUMN `mail_id_extcartrulefamily` INT(10) DEFAULT 0';
	 	$res = $res AND Db::getInstance()->Execute($query);
	}

	// Add new columns in cart table
	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'oleapromo` LIKE \'global_cart_amount\'';
	$found_columns = Db::getInstance()->ExecuteS($query);
	if (sizeof($found_columns) == 0) {
		$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
	 				ADD COLUMN `global_cart_amount` decimal(10,2)';
		$res = $res AND Db::getInstance()->Execute($query);
	}

	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'oleapromo` LIKE \'global_cart_amount_id_currency\'';
	$found_columns = Db::getInstance()->ExecuteS($query);
	if (sizeof($found_columns) == 0) {
		$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
	 				ADD COLUMN `global_cart_amount_id_currency` INT(10)';
		$res = $res AND Db::getInstance()->Execute($query);
	}

	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'oleapromo` LIKE \'global_cart_amount_withtaxes\'';
	$found_columns = Db::getInstance()->ExecuteS($query);
	if (sizeof($found_columns) == 0) {
		$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
	 				ADD COLUMN `global_cart_amount_withtaxes` TINYINT(1)';
		$res = $res AND Db::getInstance()->Execute($query);
	}

	$res = $res && $object->registerHook('oleaextcartruleReaffectFamily');

	if ($res)
		Configuration::updateValue('OLEA_MLTPROM_MODULEUPGRADEOK', "3.5.1");
	return $res;
}