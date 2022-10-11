<?php
/**
* MODULE PRESTASHOP OFFICIEL CHRONOPOST
*
* LICENSE : All rights reserved - COPY AND REDISTRIBUTION FORBIDDEN WITHOUT PRIOR CONSENT FROM OXILEO
* LICENCE : Tous droits réservés, le droit d'auteur s'applique - COPIE ET REDISTRIBUTION INTERDITES
* SANS ACCORD EXPRES D'OXILEO
*
* @author    Oxileo SAS <contact@oxileo.eu>
* @copyright 2001-2018 Oxileo SAS
* @license   Proprietary - no redistribution without authorization
*/

header('Content-type: text/plain');
require('../../../config/config.inc.php');
include_once '../libraries/PointRelaisServiceWSService.php';

$ws = new PointRelaisServiceWSService();
$params = new recherchePointChronopost();
$params->zipCode = Tools::getValue('codePostal');
$params->accountNumber = Configuration::get('CHRONOPOST_GENERAL_ACCOUNT');
$params->password = Configuration::get('CHRONOPOST_GENERAL_PASSWORD');
$params->address = Tools::getValue('address');

if (Tools::getIsset('city') && Tools::getValue('city') != 'unknown') {
    $params->city = Tools::getValue('city');
}

if (Tools::getIsset('country') && Tools::getValue('country') != 'unknown') {
    $params->countryCode = Tools::getValue('country');
} else {
    $params->countryCode = 'FR';
}

// 3-letter ISO codes for DOM
$dom = array(
    'RE' => 'REU',
    'MQ' => 'MTQ',
    'GP' => 'GLP',
    'YT' => 'MYT',
    'GF' => 'GUF');


if (array_key_exists($params->countryCode, $dom)) {
    $params->countryCode = $dom[$params->countryCode];
}

$params->type = 'P';
$params->service = 'L';
$params->weight = 0;
$params->shippingDate = date('d/m/Y');
$params->maxPointChronopost = 10;
$params->maxDistanceSearch = 40;
$params->holidayTolerant = 1;

if ($params->countryCode == 'FR' || $params->countryCode == 'FX' || $params->countryCode == 'MC') {
    echo Tools::jsonEncode($ws->recherchePointChronopost($params)->return);
} else {
    echo Tools::jsonEncode($ws->recherchePointChronopostInter($params)->return);
}
