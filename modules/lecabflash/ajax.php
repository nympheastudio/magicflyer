<?php
/**
* 2009-2017 202 ecommerce
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*  @author    202 ecommerce <support@202-ecommerce.com>
*  @copyright 2009-2017 202 ecommerce SARL
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

global $useSSL;
$useSSL = true;

require_once dirname(__FILE__).'/../../config/config.inc.php';
require_once dirname(__FILE__).'/../../init.php';

// Actions du front
$actions_front = array('getquote', 'adresse_confirm');

$token = Tools::getValue('lecabflash_token');

if (in_array(Tools::getValue('action'), $actions_front)) {
    // Vérification front
    $verif_token = md5(Configuration::get('LECABFLASH_TOKEN_HASH') . Context::getContext()->cart->id . date('Ymd'));

    if ($verif_token != $token) {
        die('Unauthorized');
    }
} else {
    // Vérifications back
    $verif_token = sha1(Configuration::get('LECABFLASH_TOKEN_HASH') . Tools::getValue('employee_id') . date('Ymd') . __PS_BASE_URI__);

    if ($verif_token != $token) {
        die('Unauthorized');
    }
}

$module = Module::getInstanceByName(basename(dirname(__FILE__)));
if (Tools::getValue('action') == 'adresse_search') {
        $res = new LecabFlashApi(LECABFLASH_API_TEST_KEY);
        $payload = array(
            'location'=> array(
                'address'=> Tools::getValue('adresse'),
            ),
            'limit'=> 5
        );

    $response = $res->searchLocation($payload);
    die(Tools::jsonEncode($response));
}
if (Tools::getValue('action') == 'adresse_confirm') {
    $context = Context::getContext();

    $res = $module->ChangeCartAddress(Tools::getValue('adresse'), $context->cart->id);



    $context->cart->id_address_delivery = $res->id_address_delivery;

    die(Tools::jsonEncode($res));
}



if ($module && $module->active) {
    ob_start();
    $response = $module->handleAjax();
    ob_end_clean();
    die(Tools::jsonEncode($response));
} else {
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    die(Tools::jsonEncode(array(
        'message' => 'Module is not active',
        'type'    => 'Deactivated',
    )));
}
