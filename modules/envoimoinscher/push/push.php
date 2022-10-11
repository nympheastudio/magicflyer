<?php
/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    EnvoiMoinsCher <api@boxtal.com>
* @copyright 2007-2018 PrestaShop SA / 2011-2018 EnvoiMoinsCher
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registred Trademark & Property of PrestaShop SA
*/

require_once(realpath(dirname(__FILE__) . '/../../../config/defines.inc.php'));
require_once(_PS_MODULE_DIR_ . '/../config/config.inc.php');
require_once(_PS_MODULE_DIR_ . '/../init.php');
require_once(_PS_MODULE_DIR_ . '/envoimoinscher/envoimoinscher.php');

$emc = new Envoimoinscher();

echo (int)$emc->handlePush();
