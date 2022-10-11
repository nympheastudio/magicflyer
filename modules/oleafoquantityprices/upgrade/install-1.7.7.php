<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_7_7($object)
{
	$upgrade_version = '1.7.7';

	$object->upgrade_detail[$upgrade_version] = array();

	$update_ok = $object->registerHook('displayProductButtons');

	if (!$update_ok)
		$object->upgrade_detail[$upgrade_version][] = $object->l('Unable to register to hook displayProductButtons');

	return (bool)!count($object->upgrade_detail[$upgrade_version]);
}