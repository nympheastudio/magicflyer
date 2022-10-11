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
require('../chronopost.php');

error_reporting(E_ALL);
/* Check secret */
if (!Tools::getIsset('shared_secret') || Tools::getValue('shared_secret') != Configuration::get('CHRONOPOST_SECRET')) {
    die('Secret does not match.');
}

if (!Tools::getIsset('code')) {
    die('Parameter Error');
}

$carrier = Chronopost::createCarrier(Tools::getValue('code'));
if (!$carrier) {
    die('KO');
}

echo 'OK';
