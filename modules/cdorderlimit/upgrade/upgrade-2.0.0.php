<?php
/**
* cdorderlimit :: Autoriser les commandes sous certaines conditions
*
* @author    contact@cleanpresta.com (www.cleanpresta.com)
* @copyright 2013-2016 cleandev.net
* @license   You only can use module, nothing more!
*/

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_2_0_0($module)
{
	$module->registerHook('actionBeforeCartUpdateQty');
	return true;
}
