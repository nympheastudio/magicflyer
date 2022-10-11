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

require_once(dirname(__FILE__).'/../common/support.class.php');

class AmazonSupport extends CommonSupport
{
    const AMAZON_DOCUMENTATION_URL = 'http://documentation.common-services.com/amazonv4';
    const FUNCTION_IMPORT_UNKNOWN_SKU = 205;

    /* Import Orders */
    const FUNCTION_IMPORT_UNEXISTENT_SKU = 205;
    const FUNCTION_IMPORT_DUPLICATE_SKU = 4569;
    const FUNCTION_IMPORT_CARRIER_MAPPING = 474;
    const FUNCTION_IMPORT_INACTIVE_UNAVAILABLE = 4225;
    const FUNCTION_IMPORT_ORDER_STATUS = 520;
    const FUNCTION_EXPORT_DUPLICATE = 234;

    /* Export Products */
    const FUNCTION_EXPORT_NOSKU = 237;
    const FUNCTION_EXPORT_NOCODE = 241;
    const FUNCTION_EXPORT_NO_MASTER_SKU = 247;
    const FUNCTION_EXPORT_NO_PRODUCT = 444;
    const FUNCTION_EXPORT_NO_BROWSENODE = 347;
    const FUNCTION_EXPORT_NO_BRAND = 158;
    const FUNCTION_EXPORT_NO_PROFILE = 4348;
    const FUNCTION_EXPORT_INVALID_SKU = 4641;
    const TUTORIAL_GET_SUPPORT = 59;

    /* Tutorials */
    const TUTORIAL_FEATURES = 2475;
    const TUTORIAL_PARAMETERS = 2384;
    const TUTORIAL_SETTINGS = 2481;
    const TUTORIAL_KEYPAIRS = 32;
    const TUTORIAL_CATEGORIES = 2498;
    const TUTORIAL_PROFILES = 2520;
    const TUTORIAL_MAPPINGS = 2621;
    const TUTORIAL_API_KEYPAIRS = 32;
    const TUTORIAL_AMAZON_EUROPE = 257;
    const TUTORIAL_CRON = 96;
    const TUTORIAL_SYNCHRONIZATION = 75;
    const TUTORIAL_MATCHING = 1405;
    const TUTORIAL_CREATION = 2527;
    const TUTORIAL_PRICES_RULES = 631;
    const TUTORIAL_EUROPE = 257;
    const TUTORIAL_ORDERS_IMPORT = 92;
    const TUTORIAL_GCID = 1217;
    const TUTORIAL_OFFERS = 2324;
    const TUTORIAL_TOOLS = 2507;
    const TUTORIAL_REFERENCE_LOADER_TOOL = 1510;
    const TUTORIAL_FILTERS = 1672;
    const TUTORIAL_IMPORT_PRODUCTS = 6140;
    const TUTORIAL_SECOND_HAND = 2577;
    const TUTORIAL_DOMAIN = 691;
    const TUTORIAL_PHP = 2816;
    const TUTORIAL_NO_MAIL = 434;
    const TUTORIAL_FBA = 2866;
    const TUTORIAL_REPRICING = 3180;
    const TUTORIAL_PHPHANDLER = 3092;
    const TUTORIAL_CARRIERS_MODULES = 4154;
    const TUTORIAL_AFTER_INSTALLATION = 2490;
    const TUTORIAL_IMAGES = 3870;
    const TUTORIAL_PERMISSIONS = 4646;
    const TUTORIAL_REMOTE_CART = 4793;
    const TUTORIAL_SHIPPING_TEMPLATE = 4722;
    const TUTORIAL_MESSAGING = 4901;
    const TUTORIAL_MULTISTORE = 4522;
    const TUTORIAL_PING = 5080;
    const TUTORIAL_SHIPPING = 4722;
    const TUTORIAL_CANCEL_ORDERS = 5118;
    const TUTORIAL_ORDERS_REPORT = 6269;
    const TUTORIAL_EXPERT_MODE = 1387;
    const TUTORIAL_DEBUG_EXPRESS = 4047;
    const TUTORIAL_BUSINESS = 6267;

    public $product_id = '5000006781';

    public static function gethreflink($id = null, $alternate_string = null)
    {
        $amazon = new Amazon();
        $lang = self::availableLang(Language::getIsoById($amazon->id_lang));

        if ($id) {
            $url = sprintf('%s?p=%s&lang=%s', self::AMAZON_DOCUMENTATION_URL, $id, $lang);
        } elseif ($lang) {
            $url = sprintf('%s?lang=%s', self::AMAZON_DOCUMENTATION_URL, $lang);
        } else {
            $url = sprintf('%s', self::AMAZON_DOCUMENTATION_URL);
        }

        if ($alternate_string == null) {
            $alternate_string = $amazon->l('Amazon Marketplace for Prestashop Online Documentation', basename(__FILE__, '.php'), $amazon->id_lang);
        }

        $link = sprintf(html_entity_decode('&lt;a href="%s" title="%s" target="_blank"&gt;%s&lt;/a&gt;'), $url, $alternate_string, $url);

        return ($link);
    }

    public static function availableLang($lang)
    {
        switch ($lang) {
            case 'fr':
            case 'it':
            case 'es':
            case 'de':
                return ($lang);
            default:
                return ('en');
        }
    }

    public static function message($msg, $id = null)
    {
        if ($id) {
            $amazon = new Amazon();
            $lang = self::availableLang(Language::getIsoById($amazon->id_lang));

            $url = sprintf('%s?p=%s&lang=%s', self::AMAZON_DOCUMENTATION_URL, $id, $lang);

            $support_found = $amazon->l('An online support has been found on this topic', basename(__FILE__, '.php'), $amazon->id_lang);
            $click = $amazon->l('Click on this link to obtain support', basename(__FILE__, '.php'), $amazon->id_lang);

            $link = sprintf('&lt;a href="%s" title="%s" target="_blank"&gt;%s&lt;/a&gt;', $url, $amazon->l('Amazon Marketplace for Prestashop Online Documentation', basename(__FILE__, '.php'), $amazon->id_lang), $url);

            $help_msg = $msg.nl2br("");
            $help_msg .= html_entity_decode('&lt;div class="support-msg"&gt;');
            $help_msg .= sprintf('%s, '.nl2br(""), $support_found);
            $help_msg .= sprintf("%s: %s\n", $click, html_entity_decode($link));
            $help_msg .= html_entity_decode('&lt;/div&gt;');

            return ($help_msg);
        }

        return ($msg);
    }
}
