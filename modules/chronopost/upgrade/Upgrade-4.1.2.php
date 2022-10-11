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

function upgrade_module_4_1_2()
{
    return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'configuration` 
        WHERE name LIKE "CHRONOPOST%" AND (id_shop_group != NULL OR id_shop!=NULL)');
}
