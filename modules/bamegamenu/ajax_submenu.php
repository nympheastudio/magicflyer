<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@buy-addons.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@buy-addons.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 * @since 1.6
 */

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('bamegamenu.php');
include_once('classes/EditMegaMenu.php');
include_once('classes/SubMegaMenu.php');
$context = Context::getContext();

$product = Tools::getValue('product');
$hook = Tools::getValue('hook');
$type = Tools::getValue('type');
$id = Tools::getValue('id');
$row = Tools::getValue('row');

$bamegamenu = new BAMegaMenu();
$cookiekey = $bamegamenu->cookiekeymodule();
$batoken = Tools::getValue("batoken");

$cookie = new Cookie('psAdmin');
$id_employee = $cookie->id_employee;
if ($batoken == $cookiekey && !empty($id_employee)) {
    $ajax = new SubMegaMenu;
    $html = '';
    if ($product == 'productlist') {
        $html .=$ajax->choicesSubSelectCategory();
        $html .='<br><input type="text" id="product_' . $id . '" name="sub[' . $row . '][' . $id
        . '][]" value="" placeholder="Number Product">';
    }
    if ($type == 'multiple') {
        $html .=$ajax->choicesSubSelect();
    }
    if ($hook == 'loadhook') {
        $html .=$ajax->loadHook($row, $id);
    }
    echo $html;
} else {
    echo $bamegamenu->l("You do not have permission to access it.");
}
die;
