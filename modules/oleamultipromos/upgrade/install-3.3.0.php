<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_3_0($object, $install = false)
{
	$res = true;
	error_log('Installing version 3.3.0 of OleaMultiPromo module');
	
	$query = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'oleapromo_shop (
			`id_oleapromo` int(10) NOT NULL AUTO_INCREMENT, 
			`id_shop` int(11) NOT NULL,
			PRIMARY KEY(`id_oleapromo`, `id_shop`), 
			KEY `id_shop` (`id_shop`))
			ENGINE='._MYSQL_ENGINE_.' default CHARSET=utf8';
	
 	$res = $res AND Db::getInstance()->Execute($query);
		
 	$query = 'INSERT IGNORE INTO '._DB_PREFIX_.'oleapromo_shop (id_shop, id_oleapromo)
 				SELECT 1, id_oleapromo FROM '._DB_PREFIX_.'oleapromo WHERE 1';
	$res = $res AND Db::getInstance()->Execute($query);
	
	if ($res)
		Configuration::updateValue('OLEA_MLTPROM_MODULEUPGRADEOK', "3.3.0");
	return $res;
}