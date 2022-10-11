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
 * @package   Common-Classes
 * Support by mail:  support@common-services.com
 */

if (!class_exists('CommonConfigurationCheck')) {
    /**
     * Class CommonConfigurationCheck
     */
    abstract class CommonConfigurationCheck
    {
        /**
         * Check if phone is mandatory
         * @return bool
         */
        public static function checkAddress()
        {
            $addressCheck = new Address();
            $pass = true;

            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $addressRequiredFields = $addressCheck->getfieldsRequiredDatabase();

                if (is_array($addressRequiredFields) && count($addressRequiredFields)) {
                    foreach ($addressRequiredFields as $addressRequiredField) {
                        if (isset($addressRequiredField['field_name']) && ($addressRequiredField['field_name'] == 'phone_mobile' || $addressRequiredField['field_name'] == 'phone')) {
                            $pass = false;
                            break;
                        }
                    }
                }
            }

            $addressRules = $addressCheck->getValidationRules('Address');
            return($pass && !(is_array($addressRules['required']) && in_array(array('phone_mobile', 'phone'), $addressRules['required'])));
        }

        /**
         * Check if a customer field is mandatory
         * @return bool
         */
        public static function mandatoryCustomerField($field)
        {
            static $customRequiredFields = null;
            $pass = true;
            $customerCheck = new Customer();

            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                if ($customRequiredFields === null) {
                    $customRequiredFields = $customerCheck->getfieldsRequiredDatabase();
                }

                if (is_array($customRequiredFields) && count($customRequiredFields)) {
                    foreach ($customRequiredFields as $customRequiredField) {
                        if (isset($customRequiredField['field_name']) && $customRequiredField['field_name'] == $field) {
                            $pass = false;
                        }
                    }
                }
            }

            $customerRules = $customerCheck->getValidationRules('Customer');
            return($pass && !(is_array($customerRules['required']) && in_array($field, $customerRules['required'])));
        }

        /**
         * @return null|number
         */
        public static function getMemoryLimit()
        {
            if (!method_exists('Tools', 'getMemoryLimit')) {
                $memory_limit = ini_get('memory_limit');
                $unit = Tools::strtolower(Tools::substr($memory_limit, -1));
                $val = preg_replace('[^0-9]', '', $memory_limit);
                switch ($unit) {
                    case 'g':
                        $val = $val * 1024 * 1024 * 1024;
                        break;
                    case 'm':
                        $val = $val * 1024 * 1024;
                        break;
                    case 'k':
                        $val = $val * 1024;
                        break;
                    default:
                        $val = false;
                }
            } else {
                $val = Tools::getMemoryLimit();
            }

            if ($val <= 0) {
                $memory_limit = null;
            } else {
                // Switch to MB

                $memory_limit = abs((int)$val / (1024 * 1024));
            }
            return($memory_limit);
        }

        /**
         * @return bool
         */
        public static function hasOverrides()
        {
            $pass = false;

            if (defined('_PS_OVERRIDE_DIR_') && !Configuration::get('PS_DISABLE_OVERRIDES') && ($override_content = CommonTools::globRecursive(_PS_OVERRIDE_DIR_.'*.php'))) {
                foreach ($override_content as $fn) {
                    if (preg_match('/[A-Z]\w+.php$/', $fn)) {
                        $pass = true;
                    }
                }
            }
            return($pass);
        }

        /**
         * @return bool
         */
        public static function checkShopUrl()
        {
            $pass = true;
            $context = Context::getContext();

            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                if (Shop::isFeatureActive()) {
                    $shop = $context->shop;

                    if ($_SERVER['HTTP_HOST'] != $shop->domain && $_SERVER['HTTP_HOST'] != $shop->domain_ssl) {
                        $domain = $shop->domain;
                        $pass = false;
                    }
                } else {
                    $url = ShopUrl::getShopUrls($context->shop->id)->where('main', '=', 1)->getFirst();
                    $domain = $url->domain;

                    if ($_SERVER['HTTP_HOST'] != $url->domain && $_SERVER['HTTP_HOST'] != $url->domain_ssl) {
                        $pass = false;
                    }
                }
            } elseif (version_compare(_PS_VERSION_, '1.4', '>=')) {
                if ($_SERVER['HTTP_HOST'] != Configuration::get('PS_SHOP_DOMAIN') && $_SERVER['HTTP_HOST'] != Configuration::get('PS_SHOP_DOMAIN_SSL')) {
                    $domain = Configuration::get('PS_SHOP_DOMAIN');
                    $pass = false;
                }
            }
            return($pass);
        }

        /**
         * Check localization configuration consistency
         * @return bool
         */
        public static function checkCountryConsistency()
        {
            $country_iso_code = Tools::strtoupper(Configuration::get('PS_LOCALE_COUNTRY'));
            $pass = true;

            if (!empty($country_iso_code)) {
                if (!Validate::isLanguageIsoCode($country_iso_code) || !Country::getByIso($country_iso_code)) {
                    $pass = false;
                }
            } else {
                $pass = false;
            }
            return($pass);
        }
    }
}
