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

function upgrade_module_4_0_0()
{
    // update carrier config values
    $map = array('CHRONORELAIS_CARRIER_ID' => 'CHRONOPOST_CHRONORELAIS_ID',
        'CHRONOPOST_CARRIER_ID' => 'CHRONOPOST_CHRONO13_ID',
        'CHRONO10_CARRIER_ID' => 'CHRONOPOST_CHRONO10_ID',
        'CHRONO18_CARRIER_ID' => 'CHRONOPOST_CHRONO18_ID',
        'CHRONOEXPRESS_CARRIER_ID' => 'CHRONOPOST_CHRONOEXPRESS_ID',
        'CHRONOCLASSIC_CARRIER_ID' => 'CHRONOPOST_CHRONOCLASSIC_ID'
    );

    foreach ($map as $old => $new) {
        $carrier = new Carrier(Configuration::get($old));
        Configuration::updateValue($new, $carrier->id_reference);
        Configuration::updateValue($old, '');
    }

    Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'chrono_calculateproducts_cache2`');
    Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `ps_chrono_calculateproducts_cache2` (
          `id` int(11) NOT NULL,
          `postcode` varchar(10) NOT NULL,
          `country` varchar(2) NOT NULL,
          `chrono10` tinyint(1) NOT NULL,
          `chrono18` tinyint(1) NOT NULL,
          `chronoclassic` tinyint(1) NOT NULL,
          `relaiseurope` tinyint(1) NOT NULL DEFAULT \'0\',
          `relaisdom` tinyint(1) NOT NULL DEFAULT \'0\',
          `rdv` tinyint(1) NOT NULL DEFAULT \'0\',
          `sameday` tinyint(1) NOT NULL DEFAULT \'0\',
          `last_updated` int(11) NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
    
    Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'chrono_cart_creneau` (
				`id_cart` int(10) NOT NULL,
				`rank` int(10) NOT NULL,
				`delivery_date` varchar(29) NOT NULL,
 				`transaction_id` varchar(60) NOT NULL,
				`fee` decimal(20,6) NOT NULL,
				`product_code` VARCHAR(2) NULL DEFAULT NULL,
				`service_code` VARCHAR(2) NULL DEFAULT NULL,
 				PRIMARY KEY (`id_cart`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

    Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'chrono_lt_history` (
			`id_order` int(10) NOT null,
			`lt` varchar(20) NOT null,
			`product` varchar(2) NOT null,
			`zipcode` varchar(10) NOT null,
			`country` varchar(2) NOT null,
			`insurance` int(10) NOT null,
			`city` varchar(32) NOT null,
			PRIMARY KEY (`id_order`, `lt`)
		) ENGINE = MyISAM DEFAULT CHARSET = utf8;');

    return true;
}
