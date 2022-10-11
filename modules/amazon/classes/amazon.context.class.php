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

require_once(dirname(__FILE__).'/../classes/amazon.configuration.class.php');

class AmazonShop extends Shop
{
    public static function setShop($shop)
    {
        self::$context_id_shop = $shop->id;
        self::$context_id_shop_group = $shop->id_shop_group;
        self::$context = self::CONTEXT_SHOP;
    }
}

class AmazonContext
{
    /**
     * Restore shop context for ajax scripts
     * @param $context
     * @param null $shop
     * @param bool|false $debug
     * @return bool
     */
    public static function restore(&$context, $shop = null, $debug = false)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            if (!Shop::isFeatureActive()) {
                $context = Context::getContext();
                if (!property_exists($context, 'controller') || !is_object($context->controller)) {
                    $context->controller = new FrontController();
                }

                return (true);
            }

            $storedContexts = unserialize(AmazonTools::decode(AmazonConfiguration::getGlobalValue('AMAZON_CONTEXT_DATA')));

            if ($shop instanceof Shop) {
                $context_key = self::getKey($shop);
            } else {
                $context_key = Tools::getValue('context_key');
            }
            
            if (!is_array($storedContexts) || !count($storedContexts) || !is_string($context_key)) {
                if ($debug) {
                    printf('%s(#%d): Wrong context, please configure your module first', basename(__FILE__), __LINE__);
                }

                return (false);
            }

            if (!isset($storedContexts[$context_key]) || !$storedContexts[$context_key] || !is_object($storedContexts[$context_key])) {
                if ($debug) {
                    printf('%s(#%d): Wrong context, please configure your module first', basename(__FILE__), __LINE__);
                }

                return (false);
            }

            $id_shop = (int)$storedContexts[$context_key]->shop->id;

            if ((int)$id_shop && is_numeric($id_shop)) {
                $context->shop = new Shop($id_shop);
            }

            $context->employee = $storedContexts[$context_key]->employee;
            $context->currency = $storedContexts[$context_key]->currency;
            $context->country = $storedContexts[$context_key]->country;
            $context->language = $storedContexts[$context_key]->language;
            $context->controller = isset($storedContexts[$context_key]->controller) && is_object($storedContexts[$context_key]->controller) ? $storedContexts[$context_key]->controller : new FrontController();

            AmazonShop::setShop($context->shop);
        }

        return (true);
    }

    /**
     * Generate an unique key to store the context
     * @param $shop
     * @return null|string
     */
    public static function getKey($shop)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            return (null);
        }

        if (!Shop::isFeatureActive()) {
            return (null);
        }

        if (!$shop instanceof Shop && !$shop instanceof StdClass) {
            return (null);
        }

        $id_shop = (int)$shop->id;
        $id_shop_group = (int)$shop->id_shop_group;

        $context_key = dechex(crc32(sprintf('%02d_%02d', $id_shop, $id_shop_group))); // create a short key

        return ($context_key);
    }

    /**
     * Save store context
     * @param $context
     * @param null $employee
     * @param bool|false $debug
     * @return bool
     */
    public static function save($context, $employee = null, $debug = false)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $storedContexts = unserialize(AmazonTools::decode(AmazonConfiguration::getGlobalValue('AMAZON_CONTEXT_DATA')));

            if (is_array($storedContexts) && count($storedContexts)) {
                $amazonContexts = $storedContexts;
            } else {
                $amazonContexts = array();
            }

            $contextData = new Context();
            $contextData->shop = $context->shop;

            if (Validate::isLoadedObject($employee)) {
                $contextData->employee = $employee;
            } else {
                $contextData->employee = $context->employee;
            }

            $contextData->shop = $context->shop;
            $contextData->currency = $context->currency;
            $contextData->country = $context->country;
            $contextData->language = $context->language;
            $contextData->controller = $context->controller;

            $contextKey = self::getKey($contextData->shop);

            $contextData = Tools::jsonDecode(Tools::jsonEncode($contextData));//convert all as a stdClass

            if (!isset($amazonContexts[$contextKey]) || !is_array($amazonContexts[$contextKey])) {
                $amazonContexts[$contextKey] = array();
            }

            $amazonContexts[$contextKey] = $contextData;

            return (AmazonConfiguration::updateGlobalValue('AMAZON_CONTEXT_DATA', AmazonTools::encode(serialize($amazonContexts))));
        }

        return (true);
    }
}
