<?php
/**
* MODULE PRESTASHOP OFFICIEL CHRONOPOST
*
* LICENSE : All rights reserved - COPY && REDISTRIBUTION FORBIDDEN WITHOUT PRIOR CONSENT FROM OXILEO
* LICENCE : Tous droits réservés, le droit d'auteur s'applique - COPIE ET REDISTRIBUTION INTERDITES
* SANS ACCORD EXPRES D'OXILEO
*
* @author    Oxileo SAS <contact@oxileo.eu>
* @copyright 2001-2018 Oxileo SAS
* @license   Proprietary - no redistribution without authorization
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_1_0()
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'chrono_cart_creneau` 
        ADD `slot_code` VARCHAR(10) NOT NULL AFTER `delivery_date`;');

    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'chrono_cart_creneau` 
        CHANGE `service_code` `service_code` VARCHAR(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;');

    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'chrono_cart_creneau` 
        ADD `tariff_level` INT NOT NULL AFTER `slot_code`;');

    return Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'chrono_calculateproducts_cache2`');
}
