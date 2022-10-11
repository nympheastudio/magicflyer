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
include_once '../../../config/config.inc.php';

if (!Tools::getIsset('transactionID') || !Tools::getIsset('fee')
    || !Tools::getIsset('cartID') || !Tools::getIsset('deliveryDate')
    || !Tools::getIsset('rank') || !Tools::getIsset('slotCode')
    || !Tools::getIsset('tariffLevel')) {
    die('Parameter Error');
}

$cart=new Cart((int)Tools::getValue('cartID'));

if ($cart->id_customer != (int)Context::getContext()->customer->id) {
    die('KO');
}

$sql='INSERT INTO `'._DB_PREFIX_.'chrono_cart_creneau` VALUES ('
    .(int)Tools::getValue('cartID').', 
	"'.(int)(Tools::getValue('rank')).'", 
	"'.pSQL(Tools::getValue('deliveryDate')).'", 
	"'.pSQL(Tools::getValue('deliveryDateEnd')).'", 
	"'.pSQL(Tools::getValue('slotCode')).'", 
	"'.pSQL(Tools::getValue('tariffLevel')).'", 
	"'.pSQL(Tools::getValue('transactionID')).'", 
	"'.(float)Tools::getValue('fee').'",
	NULL,
	NULL,
	NULL) 
	ON DUPLICATE KEY UPDATE rank="'.(int)(Tools::getValue('rank')).'", 
		delivery_date="'.pSQL(Tools::getValue('deliveryDate')).'", 
		delivery_date_end="'.pSQL(Tools::getValue('deliveryDateEnd')).'", 
		slot_code="'.pSQL(Tools::getValue('slotCode')).'", 
		tariff_level="'.pSQL(Tools::getValue('tariffLevel')).'",
		transaction_id="'.pSQL(Tools::getValue('transactionID')).'",
		fee="'.(float)Tools::getValue('fee').'"';

Db::getInstance()->execute($sql);

echo 'OK';
