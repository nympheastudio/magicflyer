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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2018 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class MondialrelayConnexionModuleFrontController extends ModuleFrontController
{

    public function postProcess()
    {
        $statCode = array();
        include_once _PS_MODULE_DIR_.'mondialrelay/errorCode.php';
        try {
            if (!Tools::getValue('token')) {
                die(Tools::jsonEncode(array("error" => "Security error")));
            }
            if (sha1('mr'._COOKIE_KEY_.'Back') != Tools::getValue('token')) {
                die(Tools::jsonEncode(array("error" => "Security error")));
            }

            $webservice = MondialRelay::MR_URL.'webservice/Web_Services.asmx?WSDL';
            $client = new SoapClient($webservice);
            $params = array();
            $params['Enseigne'] = Tools::getValue('enseigne');
            $params['Poids'] = '';
            $params['Taille'] = '';
            $params['CP'] = (Configuration::get('PS_SHOP_CODE')) ? Configuration::get('PS_SHOP_CODE') : '75000';
            $params['Ville'] = '';
            $id_country = (Configuration::get('PS_SHOP_COUNTRY_ID')) ? Configuration::get('PS_SHOP_COUNTRY_ID') : Configuration::get('PS_COUNTRY_DEFAULT');
            $params['Pays'] = Country::getIsoById($id_country);
            $params['Action'] = '';
            $params['RayonRecherche'] = '';
            $concat = $params['Enseigne'].$params['Pays'].$params['Ville'].$params['CP'].$params['Poids'].Tools::getValue('key');
            $params['Security'] = Tools::strtoupper(md5($concat));
            $result_mr = $client->WSI2_RecherchePointRelais($params);

            if (($errorNumber = $result_mr->WSI2_RecherchePointRelaisResult->STAT) != 0) {
                die(Tools::jsonEncode(array("error" => str_replace('"', '', $statCode[$errorNumber]))));
            }
             die(Tools::jsonEncode(array("success" => true)));
        } catch (Exception $e) {
            die(Tools::jsonEncode(array("error" => str_replace('"', '', $statCode[99]))));
        }
    }
}
