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

include(dirname(__FILE__).'/../../../config/config.inc.php');
include('../lanavettepickup.php');

if (!Configuration::get('LNP2_SECURITY_TOKEN') || Tools::getValue('token') != Configuration::get('LNP2_SECURITY_TOKEN')) {
    echo Tools::safeOutput(Tools::getValue('not_logged_str'));

    return;
}

$drop_off_pudo = Tools::jsonDecode(str_replace('|', '"', Configuration::get('LNP2_DROP_OFF_SITE')), true);

$pickup = new LaNavettePickup();
$url = $pickup->getURL();
$ws = new Lnp2PickUpWebService($url);
$res = $ws->getPudoListFromCoordinates(array(
    'partnerName'       => Configuration::get('LNP2_NAVETTE_PARTNER_ID'),
    'pudoType'          => Tools::getValue('type') == 'd' ? 'departure' : 'destination',
    'gpsCoordinates'    => array(
        'longitude' => (string)Tools::substr(Tools::getValue('lng'), 0, 16),
        'latitude'  => (string)Tools::substr(Tools::getValue('lat'), 0, 16)
    ),
    'maxPudoNumber'     => 10,
    'maxDistanceSearch' => 50000,
));

if (!$res || !isset($res->pudos)) {
    echo Tools::jsonEncode(array());

    return;
}

if (is_array($res->pudos)) {
    $pudos = $res->pudos;
} else {
    $pudos = $res->pudos;
}

if (Tools::getValue('type') == 'a') {

    $ids = array();
    $pudosById = array();
    foreach ($pudos as $pudo) {
        $ids[] = $pudo->id;
        $pudosById[$pudo->id] = $pudo;
    }

    echo Tools::jsonEncode($pudosById);

} else {
    echo Tools::jsonEncode($pudos);
}
