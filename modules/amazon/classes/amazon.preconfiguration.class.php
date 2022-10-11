<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
*/

class AmazonPreconfiguration
{
    public static $preconfiguration = array(
        'de' => array(
            'region' => 'de',
            'currency' => 'EUR',
            '_amazon_incoming_carrier' => 'Std DE Dom',
            '_amazon_outgoing_carrier' => 'Deutsche Post',
            'synchronization_field' => 'ean13',
            'taxes' => true,
            'marketplace_master' => 'de'
        ),
        'es' => array(
            'region' => 'es',
            'currency' => 'EUR',
            '_amazon_incoming_carrier' => 'Std ES Dom',
            '_amazon_outgoing_carrier' => 'Correos',
            'synchronization_field' => 'ean13',
            'taxes' => true,
            'marketplace_master' => 'es'
        ),
        'fr' => array(
            'region' => 'fr',
            'currency' => 'EUR',
            '_amazon_incoming_carrier' => 'Std FR Dom',
            '_amazon_outgoing_carrier' => 'La Poste',
            'synchronization_field' => 'ean13',
            'taxes' => true,
            'marketplace_master' => 'fr'
        ),
        'it' => array(
            'region' => 'it',
            'currency' => 'EUR',
            '_amazon_incoming_carrier' => 'Std IT Dom',
            '_amazon_outgoing_carrier' => 'Poste Italiane',
            'synchronization_field' => 'ean13',
            'taxes' => true,
            'marketplace_master' => 'it'
        ),
        'gb' => array(
            'region' => 'uk',
            'currency' => 'GBP',
            '_amazon_incoming_carrier' => 'Std UK Dom',
            '_amazon_outgoing_carrier' => 'Royal Mail',
            'synchronization_field' => 'ean13',
            'taxes' => true,
            'marketplace_master' => 'uk'
        ),
        'us' => array(
            'region' => 'us',
            'currency' => 'USD',
            '_amazon_incoming_carrier' => 'Std US Dom',
            '_amazon_outgoing_carrier' => 'USPS',
            'synchronization_field' => 'upc',
            'taxes' => false,
            'marketplace_master' => null
        ),
        'mx' => array(
            'region' => 'mx',
            'currency' => 'MXN',
            '_amazon_incoming_carrier' => 'Std MX Dom',
            '_amazon_outgoing_carrier' => '',
            'synchronization_field' => 'upc',
            'taxes' => true,
            'marketplace_master' => null
        ),
        'ca' => array(
            'region' => 'ca',
            'currency' => 'CAD',
            '_amazon_incoming_carrier' => 'Std CA Dom',
            '_amazon_outgoing_carrier' => 'Canada Post',
            'synchronization_field' => 'upc',
            'taxes' => false,
            'marketplace_master' => null
        )
    );

    public static function data($id_lang)
    {
        if (!$id_lang) {
            return (null);
        }

        $country_iso_code = Tools::strtolower(Configuration::get('PS_LOCALE_COUNTRY'));

        if (!$country_iso_code) {
            return (null);
        }

        if (!isset(self::$preconfiguration[$country_iso_code])) {
            return (null);
        }

        $language = null;

        switch ($country_iso_code) {
            case 'fr':
                $language = 'fr';
                break;
            case 'es':
                $language = 'es';
                break;
            case 'it':
                $language = 'it';
                break;
            case 'de':
                $language = 'de';
                break;
            case 'mx':
                $language = 'es';
                break;
            case 'en':
            case 'uk':
            case 'gb':
                $language = 'en';
                break;
        }
        if (!$language) {
            return (null);
        }

        $_target_id_lang = Language::getIdByIso($language);

        if ($_target_id_lang != $id_lang) {
            return (null);
        }

        $preconfiguration_data = self::$preconfiguration[$country_iso_code];
        $preconfiguration_data['_carrier'] = Configuration::get('PS_CARRIER_DEFAULT');

        $preconfiguration_data['amazon_incoming_carrier'] = array(0 => md5($preconfiguration_data['_amazon_incoming_carrier']));
        $preconfiguration_data['prestashop_incoming_carrier'] = array(0 => $preconfiguration_data['_carrier']);
        $preconfiguration_data['outgoing_carriers'] = array(
            'amazon' => array(0 => $preconfiguration_data['_amazon_outgoing_carrier']),
            'prestashop' => array(0 => $preconfiguration_data['_carrier'])
        );

        return ($preconfiguration_data);
    }
}
