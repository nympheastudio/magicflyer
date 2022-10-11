<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_7_8($object)
{
	$upgrade_version = '1.7.8';

	$object->upgrade_detail[$upgrade_version] = array();

	$update_ok = $object->registerHook('displayShoppingCart') && $object->registerHook('displayProductPriceBlock');

	if (!$update_ok)
		$object->upgrade_detail[$upgrade_version][] = $object->l('Unable to register to hook displayShoppingCart');

	return (bool)!count($object->upgrade_detail[$upgrade_version]);
}