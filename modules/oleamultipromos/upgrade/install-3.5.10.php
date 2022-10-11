<?php
if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_3_5_10($object, $install = false)
{
	$res = true;

	Configuration::updateValue('OLEA_MAXIPROMO_DEALDISCOUNTHT', true);

	if ($res)
		Configuration::updateValue('OLEA_MLTPROM_MODULEUPGRADEOK', "3.5.10");
	return $res;
}