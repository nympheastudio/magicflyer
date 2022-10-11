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
$context = Context::getContext();

$bamegamenu = new BAMegaMenu();
$cookiekey = $bamegamenu->cookiekeymodule();
$batoken = Tools::getValue("batoken");

$cookie = new Cookie('psAdmin');
$id_employee = $cookie->id_employee;
if ($batoken == $cookiekey && !empty($id_employee)) {
    $typelink = Tools::getValue('typelink', "");
    $ajax = new EditMegaMenu();
    switch ($typelink) {
        case 'url':
            echo $ajax->inputMenu();
            break;
        case 'existlinks':
            echo $ajax->selectedMenu();
            break;
        case 'customhtml':
            echo $ajax->textareaSubMenu();
            break;
        case 'loadhook':
            echo $ajax->loadHook();
            break;
        case 'product':
            echo $ajax->inputSubMenu();
            break;
        case 'productlist':
            echo $ajax->selectedMenuProductList();
            break;
        case 'link':
            echo $ajax->selectedMenu();
            break;
        case 'externallink':
            echo $ajax->inputMenu(1);
            break;
        case 'systemlink':
            echo $ajax->selectedMenu(1);
            break;
        case 'htmllink':
            echo $ajax->textareaMenu();
            break;
        default:
            echo"";
    }
} else {
    echo $bamegamenu->l("You do not have permission to access it.");
}
die;
