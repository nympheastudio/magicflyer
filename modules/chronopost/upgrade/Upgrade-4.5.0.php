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

function upgrade_module_4_5_0($object)
{
    if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
        return ($this->registerHook('displayAfterCarrier') && // For point relais GMap
            $this->registerHook('actionCarrierUpdate') && // For update of carrier IDs
            $this->registerHook('displayHeader'));
    } else {
        return true;
    }
}
