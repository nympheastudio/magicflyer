<?php
/**
 * 2007-2017 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

include_once(dirname(__FILE__).'/../../../config/config.inc.php');
include_once dirname(__FILE__).'/../../../init.php';
include('../lanavettepickup.php');

if (!Configuration::get('LNP2_SECURITY_TOKEN')
    || Tools::getValue('token') != Configuration::get('LNP2_SECURITY_TOKEN')
) {
    echo Tools::safeOutput(Tools::getValue('not_logged_str'));

    return;
}

$context = Context::getContext();

if (Tools::getValue('pudo', null) !== null) {/* set selected pudo */
    $context->cookie->__set('cart_lnp2_site', Tools::getValue('pudo'));
}

if (Tools::getValue('shipping_price', null) !== null) {/* set selected pudo */
    $context->cookie->__set('lnp2_shipping_price', Tools::getValue('shipping_price'));

    $cart_id = Tools::getValue('cart_id', null);
    if ($cart_id !== null) {
        $pickup = new LaNavettePickup();
        $cart = new Cart($cart_id);
        $context->cookie->__set('lnp2_cart_signature', $pickup->getCartSignature($cart));
    }
}

echo '1';
