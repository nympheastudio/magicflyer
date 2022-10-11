<?php
/**
* ---------------------------------------------------------------------------------
*
* This file is part of the 'oleafoquantityprices' module feature
* Developped for Prestashop platform.
* You are not allowed to use it on several site
* You are not allowed to sell || redistribute this module
* This header must not be removed
*
* @category  XXX
* @author    OleaCorner <contact@oleacorner.com> <www.oleacorner.com>
* @copyright OleaCorner
* @version   1.0
* @license   XXX
*         
* ---------------------------------------------------------------------------------
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

if (Tools::getValue('type') == 'declinationstable')
{
	$id_product = (int)Tools::getValue('id_product');
	if ($id_product)
	{
		$module = Module::getInstanceByName('oleafoquantityprices');
		if ($module)
			echo $module->ajaxDisplayPricesTab($id_product);
	}
}