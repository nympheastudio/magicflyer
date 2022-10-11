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

function upgrade_module_4_6_0($object)
{
    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'chrono_cart_creneau` 
        ADD `as_code` VARCHAR(6) NULL AFTER `service_code`;');

    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'chrono_cart_creneau` 
        ADD `delivery_date_end` VARCHAR(29) NULL AFTER `delivery_date`;');

    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'chrono_calculateproducts_cache2` 
        ADD `dimanchebal` INT NOT NULL AFTER `sameday`;');

    Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'chrono_lt_history` 
        ADD `cancelled` INT NULL AFTER `city`;');

    return true;
}
