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

function upgrade_module_3_6_4()
{
    return Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'chrono_quickcost_cache` (
			`id` int(11) NOT null AUTO_INCREMENT,
			`product_code` varchar(2) NOT null,
			`arrcode` varchar(10) NOT null,
			`weight` decimal(10,2) NOT null,
			`price` decimal(10,2) NOT null,
			`last_updated` int(11) NOT null,
			PRIMARY KEY (`id`)
			) ENGINE = MyISAM DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1 ;');
}
