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

require_once(_PS_MODULE_DIR_.'/amazon/common/tools.class.php');

class AmazonTools extends CommonTools
{
    /**
     * @param $str
     *
     * @return bool|mixed|string
     */
    public static function toKey($str)
    {
        if (is_array($str)) {
            return(self::arrayMapRecursive(array('AmazonTools', 'toKey'), $str));
        } else {
            if (self::isJapanese($str)) {
                $str = mb_convert_encoding($str, 'HTML-ENTITIES', 'UTF-8');
            }
            $str = str_replace(array('-', ',', '.', '/', '+', '.', ':', ';', '>', '<', '?', '(', ')', '!', '&'), array(
            '_',
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n'
            ), $str);
            $str = Tools::strtolower(preg_replace('/[^A-Za-z0-9_]/', '', $str));

            return $str;
        }
    }
    /**
     * Source: http://stackoverflow.com/questions/2856942/how-to-check-if-the-word-is-japanese-or-english-using-php
     *
     * @param $word
     * @return int
     */
    public static function isJapanese($word)
    {
        return preg_match('/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $word);
    }

    /**
     * @param $configuration
     *
     * @return string
     */
    public static function encode($configuration)
    {
        return base64_encode($configuration);//TODO: Validation: Configuration Requirement
    }

    /**
     * @param $configuration
     *
     * @return string
     */
    public static function decode($configuration)
    {
        return base64_decode($configuration);//TODO: Validation: Configuration Requirement
    }

    /**
     * @param $tokens
     *
     * @return bool
     */
    public static function checkToken($tokens)
    {
        $pass = true;
        $amazonTokens = AmazonConfiguration::get('CRON_TOKEN');

        if (!$tokens) {
            return (false);
        }

        if (!is_array($tokens)) {
            $tokens = array($tokens);
        }

        foreach ($tokens as $token) {
            $pass = in_array($token, $amazonTokens) && $pass;
        }

        return ($pass);
    }

    /**
     * @param bool $debug
     *
     * @return array
     */
    public static function selectEuropeanPlatforms($debug = false)
    {
        $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
        $id_lang_prefered = null;
        $id_lang_selected = null;

        $marketPlaceMaster = AmazonConfiguration::get('MASTER');
        $marketPlaceRegion = AmazonConfiguration::get('REGION');

        // Amazon API keypairs
        //
        $merchantIds = AmazonConfiguration::get('MERCHANT_ID');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');
        $awsKeyIds = AmazonConfiguration::get('AWS_KEY_ID');
        $awsSecretKeys = AmazonConfiguration::get('SECRET_KEY');
        $mwsTokens = AmazonConfiguration::get('MWS_TOKEN');

        if (is_array($marketPlaceRegion)) {
            $marketPlaceRegion2Lang = array_flip($marketPlaceRegion);
        }

        $id_lang_master = isset($marketPlaceRegion2Lang[$marketPlaceMaster]) ? $marketPlaceRegion2Lang[$marketPlaceMaster] : null;
        $platforms = array();

        foreach ($marketPlaceRegion as $marketplace_id_lang => $iso_code) {
            if (!isset($marketPlaceIds[$marketplace_id_lang]) || empty($marketPlaceIds[$marketplace_id_lang])) {
                continue;
            }

            if (!isset($marketPlaceRegion[$marketplace_id_lang]) || empty($marketPlaceRegion[$marketplace_id_lang])) {
                continue;
            }

            if (!self::isEuroMarketplaceId($marketPlaceIds[$marketplace_id_lang])) {
                continue;
            }

            $platforms[] = trim($marketPlaceIds[$marketplace_id_lang]);

            if ($id_lang_master == $marketplace_id_lang && !$id_lang_prefered) {
                $id_lang_prefered = $marketplace_id_lang;
            } elseif ($id_lang_default == $marketplace_id_lang && !$id_lang_prefered) {
                $id_lang_prefered = $marketplace_id_lang;
            }
        }

        // Use first available marketplace in Europe except UK
        if (!$id_lang_prefered) {
            foreach ($marketPlaceRegion as $marketplace_id_lang => $iso_code) {
                if (self::isEuroMarketplaceId($marketPlaceIds[$marketplace_id_lang])) {
                    $id_lang_prefered = $marketplace_id_lang;
                }
            }
        }

        if ($id_lang_prefered) {
            $id_lang_selected = $id_lang_prefered;
        } elseif ($id_lang_default && $id_lang_default != $id_lang_master) {
            $id_lang_selected = $id_lang_default;
        } elseif ($id_lang_master && $id_lang_master != 'uk') {
            $id_lang_selected = $id_lang_master;
        }

        if (!$id_lang_selected) {
            printf('%s(#%d): authArray: %s'.Amazon::LF, basename(__FILE__), __LINE__, 'Unable to select a valid id_lang');

            return (null);
        }

        // Force to use the ID of master marketplace
        if ($id_lang_master) {
            $id_lang_main = $id_lang_master;
        } else {
            $id_lang_main = $id_lang_selected;
        }

        $auth = self::authArray($merchantIds[$id_lang_main], $marketPlaceIds[$id_lang_selected], $awsKeyIds[$id_lang_main], $awsSecretKeys[$id_lang_main], $mwsTokens[$id_lang_main], $debug);

        if (is_array($platforms) && count($platforms)) {
            foreach ($platforms as $key => $platform) {
                if ($marketPlaceIds[$id_lang_selected] == $platform) {
                    unset($platforms[$key]);
                }
            }
        }

        $amzCurrency = array();
        $amzCurrency['Currency'] = 'EUR';
        $amzCurrency['Country'] = $marketPlaceRegion[$id_lang_selected];

        return (array('params' => $amzCurrency, 'auth' => $auth, 'platforms' => $platforms));
    }

    /* Select platform or select platforms for Europe*/

    /**
     * @param $marketplaceID
     *
     * @return bool
     */
    public static function isEuroMarketplaceId($marketplaceID)
    {
        return (in_array($marketplaceID, array('A13V1IB3VIYZZH', 'A1RKKUPIHCS9HS', 'A1PA6795UKMFR9', 'APJ6JRA9NG5V4')));
    }

    /**
     * @param $merchantId
     * @param $marketPlaceId
     * @param $awsKeyId
     * @param $awsSecretKey
     * @param $mwsToken
     * @param bool $debug
     *
     * @return array|null
     */
    public static function authArray($merchantId, $marketPlaceId, $awsKeyId, $awsSecretKey, $mwsToken, $debug = false)
    {
        if (empty($merchantId) || empty($marketPlaceId)) {
            if ($debug) {
                printf('%s(#%d): %s from %s'.Amazon::LF, basename(__FILE__), __LINE__, 'Empty parameter', self::callingFunction());
            }

            return (null);
        }
        $auth = array(
            'MerchantID' => trim($merchantId),
            'MarketplaceID' => trim($marketPlaceId),
            'AWSAccessKeyID' => trim($awsKeyId),
            'SecretKey' => trim($awsSecretKey),
            'mwsToken' => trim($mwsToken)
        );

        if ($debug) {
            printf('%s(#%d): Auth Array: %s'.Amazon::LF, basename(__FILE__), __LINE__, nl2br(print_r($auth, true)));
        }

        return ($auth);
    }

    /**
     * @return string
     */
    public static function callingFunction()
    {
        $trace = debug_backtrace();
        $caller = $trace[1];
        $ret = null;

        if (isset($caller['line']) && isset($caller['file'])) {
            $ret = sprintf('%s(#%d): %s()', basename($caller['file']), $caller['line'], $caller['function']);
        } else {
            $ret = sprintf('%s()', $caller['function']);
        }

        if (isset($caller['class'])) {
            $ret .= sprintf(' in %s', $caller['class']);
        }

        if ($ret) {
            $ret .= "\n";
        }

        return ($ret);
    }

    /**
     * @param $id_lang
     * @param bool $debug
     *
     * @return array|bool
     */
    public static function selectPlatforms($id_lang, $debug = false)
    {
        $amazon_features = Amazon::getAmazonFeatures();

        $marketPlaceId = null;

        // Amazon Europe overidding
        //
        $amazonEurope = $amazon_features['amazon_europe'];
        $marketPlaceMaster = AmazonConfiguration::get('MASTER');
        $marketPlaceRegion = AmazonConfiguration::get('REGION');

        // Amazon API keypairs
        //
        $merchantIds = AmazonConfiguration::get('MERCHANT_ID');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');
        $awsKeyIds = AmazonConfiguration::get('AWS_KEY_ID');
        $awsSecretKeys = AmazonConfiguration::get('SECRET_KEY');
        $mwsTokens = AmazonConfiguration::get('MWS_TOKEN');

        // Currencies
        //
        $currencies = AmazonConfiguration::get('CURRENCY');


        // Platform list
        $platforms = array();

        // Amazon Marketplace Europe values
        //
        $europeanPlatforms = array(
            'A13V1IB3VIYZZH',
            'A1RKKUPIHCS9HS',
            'A1PA6795UKMFR9',
            'APJ6JRA9NG5V4',
            'A1F83G8C2ARO7P'
        );

        if (is_array($marketPlaceRegion)) {
            $marketPlaceRegion2Lang = array_flip($marketPlaceRegion);
        }

        $marketPlaceMasterLangId = false;

        if (isset($marketPlaceRegion2Lang[$marketPlaceMaster])) {
            $marketPlaceMasterLangId = $marketPlaceRegion2Lang[$marketPlaceMaster];
        }

        if ($marketPlaceMasterLangId && $amazonEurope && isset($marketPlaceIds[$id_lang]) && in_array($marketPlaceIds[$id_lang], $europeanPlatforms)) {
            // auth
            $merchantId = trim($merchantIds[$marketPlaceMasterLangId]);
            $awsKeyId = trim($awsKeyIds[$marketPlaceMasterLangId]);
            $awsSecretKey = trim($awsSecretKeys[$marketPlaceMasterLangId]);
            $mwsToken = trim($mwsTokens[$marketPlaceMasterLangId]);

            foreach ($marketPlaceIds as $amazon_lang => $currentMarketPlaceId) {
                if ($amazon_lang == $id_lang) {
                    $selected = $id_lang;
                } else {
                    $selected = '';
                }

                if (!in_array($currentMarketPlaceId, $europeanPlatforms)) {
                    continue;
                }

                // UK Exception
                if ($currentMarketPlaceId != 'A1F83G8C2ARO7P') {
                    $platforms[] = $currentMarketPlaceId;
                }

                if (isset($selected) && $selected == $id_lang) {
                    $marketPlaceId = trim($marketPlaceIds[$id_lang]);
                }
            }

            // UK Exception
            if ($marketPlaceId == 'A1F83G8C2ARO7P') {
                $platforms = array();
            }

            // Clear Duplicates
            if ($platforms) {
                foreach ($platforms as $idx => $platform) {
                    if ($platform == $marketPlaceId) {
                        unset($platforms[$idx]);
                    }
                }
                $platforms = array_values($platforms);
            }

            // Return values
            $amzCurrency = array();
            $amzCurrency['Currency'] = $currencies[$id_lang];
            $amzCurrency['Country'] = $marketPlaceRegion[$id_lang];

            $auth = self::authArray($merchantId, $marketPlaceId, $awsKeyId, $awsSecretKey, $mwsToken, $debug);
        } else {
            if (!isset($merchantIds[$id_lang]) || !isset($marketPlaceIds[$id_lang]) || !isset($awsKeyIds[$id_lang]) || !isset($awsSecretKeys[$id_lang])) {
                return (false);
            }

            $auth = self::authArray($merchantIds[$id_lang], $marketPlaceIds[$id_lang], $awsKeyIds[$id_lang], $awsSecretKeys[$id_lang], $mwsTokens[$id_lang]);

            $platforms = array();

            $amzCurrency = array();
            $amzCurrency['Currency'] = $currencies[$id_lang];
            $amzCurrency['Country'] = $marketPlaceRegion[$id_lang];
        }
        if (isset($debug) && $debug) {
            echo nl2br(print_r($auth, true));
            echo nl2br(print_r($platforms, true));
            echo nl2br(print_r($amzCurrency, true));
        }

        return (array('params' => $amzCurrency, 'auth' => $auth, 'platforms' => $platforms));
    }

    /**
     * @param $id_lang
     * @param bool $debug
     *
     * @return array|bool
     */
    public static function selectPlatform($id_lang, $debug = false)
    {
        $amazon_features = Amazon::getAmazonFeatures();
        $amazonEurope = $amazon_features['amazon_europe'];

        $marketPlaceMaster = AmazonConfiguration::get('MASTER');
        $marketPlaceRegion = AmazonConfiguration::get('REGION');

        // Amazon API keypairs
        //
        $merchantIds = AmazonConfiguration::get('MERCHANT_ID');
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');
        $awsKeyIds = AmazonConfiguration::get('AWS_KEY_ID');
        $awsSecretKeys = AmazonConfiguration::get('SECRET_KEY');
        $mwsTokens = AmazonConfiguration::get('MWS_TOKEN');

        if (!isset($marketPlaceIds[$id_lang])) {
            return (false);
        }

        // Currencies
        //
        $currencies = AmazonConfiguration::get('CURRENCY');


        // Platform list
        $platforms = array();

        // Amazon Marketplace Europe values
        //
        $europeanPlatforms = array(
            'A13V1IB3VIYZZH',
            'A1RKKUPIHCS9HS',
            'A1PA6795UKMFR9',
            'APJ6JRA9NG5V4',
            'A1F83G8C2ARO7P'
        );

        $marketPlaceRegion2Lang = array_flip($marketPlaceRegion);
        if (isset($marketPlaceRegion2Lang[$marketPlaceMaster])) {
            $marketPlaceMasterLangId = $marketPlaceRegion2Lang[$marketPlaceMaster];
        } else {
            $marketPlaceMasterLangId = null;
        }

        if ($amazonEurope && in_array($marketPlaceIds[$id_lang], $europeanPlatforms)) {
            if (!isset($merchantIds[$marketPlaceMasterLangId])) {
                return (false);
            }
            if (!isset($awsKeyIds[$marketPlaceMasterLangId])) {
                return (false);
            }
            if (!isset($awsSecretKeys[$marketPlaceMasterLangId])) {
                return (false);
            }
            // auth
            $merchantId = trim($merchantIds[$marketPlaceMasterLangId]);
            $awsKeyId = trim($awsKeyIds[$marketPlaceMasterLangId]);
            $awsSecretKey = trim($awsSecretKeys[$marketPlaceMasterLangId]);
            $mwsToken = trim($mwsTokens[$marketPlaceMasterLangId]);

            foreach ($marketPlaceIds as $amazon_lang => $currentMarketPlaceId) {
                if ($amazon_lang == $id_lang) {
                    $selected = $id_lang;
                } else {
                    $selected = '';
                }

                if (!in_array($currentMarketPlaceId, $europeanPlatforms)) {
                    continue;
                }

                if (isset($selected) && $selected == $id_lang) {
                    $marketPlaceId = trim($marketPlaceIds[$id_lang]);
                    break;
                }
            }


            // Return values
            $amzCurrency = array();
            $amzCurrency['Currency'] = $currencies[$id_lang];
            $amzCurrency['Country'] = $marketPlaceRegion[$id_lang];

            $auth = self::authArray($merchantId, $marketPlaceId, $awsKeyId, $awsSecretKey, $mwsToken, $debug);
        } else {
            $auth = self::authArray($merchantIds[$id_lang], $marketPlaceIds[$id_lang], $awsKeyIds[$id_lang], $awsSecretKeys[$id_lang], $mwsTokens[$id_lang], $debug);

            $platforms = array();

            $amzCurrency = array();
            $amzCurrency['Currency'] = $currencies[$id_lang];
            $amzCurrency['Country'] = $marketPlaceRegion[$id_lang];
        }
        if (isset($debug) && $debug) {
            echo nl2br(print_r($auth, true));
            echo nl2br(print_r($platforms, true));
            echo nl2br(print_r($amzCurrency, true));
        }

        return (array('params' => $amzCurrency, 'auth' => $auth, 'platforms' => $platforms));
    }

    /**
     * @param $html
     * @param bool $allow_restricted_html
     *
     * @return mixed|string
     */
    public static function cleanStripTags($html, $allow_restricted_html = true)
    {
        $text = $html;

        $liTag = html_entity_decode('&lt;/li&gt;');
        $li1Tag= html_entity_decode('&lt;/LI&gt;');
        $text = str_replace(array($li1Tag, $liTag), Amazon::LF.$liTag, $text);
        $text = str_replace(array('<BR', '<br'), "\n<br", $text);

        $text = strip_tags($text);

        $text = str_replace('&#39;', "'", $text);

        $text = mb_convert_encoding($text, 'HTML-ENTITIES');
        $text = str_replace('&nbsp;', ' ', $text);
        $text = html_entity_decode($text, ENT_NOQUOTES, 'UTF-8');

        $text = str_replace('&', '&amp;', $text);
        $text = str_replace('"', "&#34;", $text);

        $text = preg_replace('#\s+[\n|\r]+$#i', '', $text); // empty
        $text = preg_replace('#[\n|\r]+#i', "\n", $text); // multiple-return
        $text = preg_replace('#(\s)\n+#i', "\n", $text); // multiple-return
        $text = preg_replace('#^[\n\r\s]#i', '', $text);

        $text = preg_replace('/[\x{0001}-\x{0009}]/u', '', $text);
        $text = preg_replace('/[\x{000b}-\x{001f}]/u', '', $text);
        $text = preg_replace('/[\x{0080}-\x{009F}]/u', '', $text);
        $text = preg_replace('/[\x{0600}-\x{FFFF}]/u', '', $text);

        $text = preg_replace('/\x{000a}/', "\n", $text);

        if ($allow_restricted_html) {
            $text = preg_replace('/\n/', Amazon::BR, $text);
            $text = preg_replace('/$/', Amazon::BR, $text);
        } else {
            $text = str_replace("\n", ', ', $text);
            $text = trim(rtrim($text, ', '));
        }
        if (!Tools::strlen(trim(strip_tags($text)))) {
            return(null);
        }
        
        return ($text);
    }


    /**
     * @param $price
     * @param $rule
     *
     * @return float
     */
    public static function priceRule($price, $rule)
    {

        // Integrity check
        if (!isset($rule['rule']) || !isset($rule['rule']['from']) || !isset($rule['rule']['to'])) {
            return ((float)$price);
        }

        if (!is_array($rule['rule']) || !is_array($rule['rule']['from']) || !is_array($rule['rule']['to'])) {
            return ((float)$price);
        }

        if ($rule['type'] == 'percent' && !(isset($rule['rule']['percent']) || !is_array($rule['rule']['percent']) || !max($rule['rule']['percent']))) {
            return ((float)$price);
        }

        if ($rule['type'] == 'value' && !(isset($rule['rule']['value']) || !is_array($rule['rule']['value']) || !max($rule['rule']['value']))) {
            return ((float)$price);
        }

        $index = null;

        if (is_array($rule['rule']['to']) && is_array($rule['rule']['from']) && count($rule['rule']['to']) && count($rule['rule']['from'])) {
            end($rule['rule']['to']);
            $max_key = key($rule['rule']['to']);
            reset($rule['rule']['to']);

            foreach ($rule['rule']['from'] as $key => $val1) {
                if (empty($rule['rule']['to'][$max_key])) {
                    $rule['rule']['to'][$max_key] = $price;
                }

                if ((float)$price >= (float)$val1 && (float)$price <= (float)$rule['rule']['to'][$key]) {
                    $index = $key;
                }
            }
        }

        if ($index === null) {
            return ((float)$price);
        }

        if ($rule['type'] == 'value') {
            $price += (float)$rule['rule']['value'][$index];
        } elseif ($rule['type'] == 'percent') {
            $price += $price * ((float)$rule['rule']['percent'][$index] / 100);
        }

        return ((float)$price);
    }

    /**
     * @param $id_lang
     * @param $orderID
     *
     * @return bool|string
     */
    public static function sellerCentralUrl($id_lang, $orderID)
    {
        if (!($tld = self::idToDomain($id_lang))) {
            return (false);
        }

        return ('https://sellercentral.amazon.'.$tld.'/gp/orders-v2/details?ie=UTF8&orderID='.$orderID);
    }

    /**
     * @param $id_lang
     *
     * @return bool|string
     */
    public static function idToDomain($id_lang)
    {
        $marketPlaceRegion = AmazonConfiguration::get('REGION');

        if (!isset($marketPlaceRegion[$id_lang])) {
            return (false);
        }

        switch ($marketPlaceRegion[$id_lang]) {
            case 'au':
                return ('com.au');
            case 'uk':
                return ('co.uk');
            case 'us':
                return ('com');
            case 'jp':
                return ('co.jp');
            case 'mx':
                return ('com.mx');
            default:
                return ($marketPlaceRegion[$id_lang]);
        }
    }

    /**
     * @param $id_lang
     * @param $asin
     *
     * @return bool|string
     */
    public static function goToProductPage($id_lang, $asin)
    {
        if (!($tld = self::idToDomain($id_lang))) {
            return (false);
        }

        return ('http://www.amazon.'.$tld.'/gp/product/'.$asin);
    }

    /**
     * @param $id_lang
     *
     * @return bool|string
     */
    public static function goToSellerReviewPage($id_lang)
    {
        if (!($tld = self::idToDomain($id_lang))) {
            return (false);
        }

        return ('https://www.amazon.'.$tld.'/gp/feedback/');
    }

    /**
     * @param bool $force_display
     *
     * @return array
     */
    public static function languages($force_display = false)
    {
        static $display_inactive = null;
        static $languages = null;
        static $available_languages = array();

        if ($available_languages) {
            return ($available_languages);
        }

        if ($display_inactive === null) {
            $display_inactive = (bool)Configuration::get('AMAZON_INACTIVE_LANGUAGES');
        }

        $country_iso_code = Tools::strtolower(Configuration::get('PS_LOCALE_COUNTRY'));

        if (!$languages) {
            $languages = Language::getLanguages(false);
        }

        foreach ($languages as $language) {
            // For active languages
            //
            if ((!$force_display && $display_inactive == false) && $language['active'] == false) {
                continue;
            }

            $language['active'] = true;

            $pass = true;

            // Allow only available platforms
            //
            switch ($language['iso_code']) {
                case 'au':
                    $language['country_iso_code'] = 'au';
                    $language['area'] = 'ww';
                    break;
                case 'fr':
                    $language['country_iso_code'] = 'fr';
                    $language['area'] = 'eu';
                    break;
                case 'de':
                    $language['country_iso_code'] = 'de';
                    $language['area'] = 'eu';
                    break;
                case 'us':
                    $language['country_iso_code'] = 'us';
                    $language['area'] = 'ww';
                    break;
                case 'gb':
                    $language['country_iso_code'] = 'gb';
                    $language['area'] = 'eu';
                    break;
                case 'en':
                    if ($country_iso_code == 'us') {
                        $language['country_iso_code'] = 'us';
                        $language['area'] = 'ww';
                    } elseif ($country_iso_code == 'mx') {
                        $language['country_iso_code'] = 'mx';
                        $language['area'] = 'ww';
                    } elseif ($country_iso_code == 'ca') {
                        $language['country_iso_code'] = 'ca';
                        $language['area'] = 'ww';
                    } else {
                        $language['country_iso_code'] = 'gb';
                        $language['area'] = 'eu';
                    }
                    break;
                case 'it':
                    $language['country_iso_code'] = 'it';
                    $language['area'] = 'eu';
                    break;
                case 'in':
                    $language['country_iso_code'] = 'in';
                    $language['area'] = 'ww';
                    break;
                case 'es':
                    if ($country_iso_code == 'mx') {
                        $language['country_iso_code'] = 'mx';
                        $language['area'] = 'ww';
                    } else {
                        $language['country_iso_code'] = 'es';
                        $language['area'] = 'eu';
                    }
                    break;
                case 'ca':
                    $language['country_iso_code'] = 'ca';
                    $language['area'] = 'ww';
                    break;
                case 'ja':
                    $language['country_iso_code'] = 'ja';
                    $language['area'] = 'ww';
                    break;
                case 'mx':
                    $language['country_iso_code'] = 'mx';
                    $language['area'] = 'ww';
                    break;
                case 'be':
                    $language['country_iso_code'] = 'fr';
                    $language['area'] = 'eu';
                    break;
                case 'cn':
                    $language['country_iso_code'] = 'cn';
                    $language['area'] = 'ww';
                    break;
                default:
                    $pass = false;
                    continue;
            }
            if (!$pass) {
                continue;
            }

            $available_languages[$language['id_lang']] = $language;
        }

        return ($available_languages);
    }

    /**
     * @param $iso_code
     *
     * @return bool
     */
    public static function isEurope($iso_code)
    {
        switch ($iso_code) {
            case 'fr':
            case 'de':
            case 'be':
            case 'it':
            case 'es':
                return (true);
            default:
                return (false);
        }
    }

    /**
     * @param $marketplaceID
     *
     * @return bool
     */
    public static function isUSMarketplaceId($marketplaceID)
    {
        return (trim($marketplaceID) == 'ATVPDKIKX0DER');
    }

    /**
     * @param $iso_code
     *
     * @return bool
     */
    public static function isUnifiedAccount($iso_code)
    {
        switch ($iso_code) {
            case 'fr':
            case 'uk':
            case 'de':
            case 'it':
            case 'es':
                return (true);
            default:
                return (false);
        }
    }

    /**
     * @param $marketplaceID
     *
     * @return bool
     */
    public static function isEuropeMarketplaceId($marketplaceID)
    {
        return (in_array($marketplaceID, array(
                'A13V1IB3VIYZZH',
                'A1RKKUPIHCS9HS',
                'A1PA6795UKMFR9',
                'APJ6JRA9NG5V4',
                'A1F83G8C2ARO7P'
            )));
    }

    /**
     * @param $lang
     *
     * @return null|string
     */
    public static function lang2MarketplaceId($lang)
    {
        switch ($lang) {
            case 'en': //
            case 'us': // US
                return ('ATVPDKIKX0DER');
            case 'fr': // France
                return ('A13V1IB3VIYZZH');
            case 'es': // Spain
                return ('A1RKKUPIHCS9HS');
            case 'de': // Germany
                return ('A1PA6795UKMFR9');
            case 'it': // Italy
                return ('APJ6JRA9NG5V4');
            case 'uk': // UK
            case 'gb': // UK
                return ('A1F83G8C2ARO7P');
            case 'jp': // Japan
                return ('A1VC38T7YXB528');
            case 'in': // India
                return ('A21TJRUUN4KGV');
            case 'br': // Brasil
                return ('A2Q3Y263D00KWC');
            case 'ca': // Canada
                return ('A2EUQ1WTGCTBG2');
            case 'mx': // Mexico
                return ('A1AM78C64UM0Y8');
            case 'au': // Australia
                return ('A39IBJ37TRP1C6');
            case 'cn': // China
                return ('AAHKV2X7AFYLW');
        }

        return (null);
    }


    /**
     * @param $country
     *
     * @return string
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    public static function countryToLanguage($country)
    {
        switch ($country) {
            case 'us':
                return 'en';
                break;
            case 'fr':
                return 'fr';
                break;
            case 'it':
                return 'it';
                break;
            case 'de':
                return 'de';
                break;
            case 'es':
                return 'es';
                break;
            case 'gb':
                return 'en';
                break;
            case 'uk':
                return 'en';
                break;
            case 'jp':
                return 'ja';
                break;
            case 'cn':
                return 'cn';
                break;
            case 'in':
                return 'in';
                break;
            case 'br':
                return 'br';
                break;
            case 'ca':
                return 'en';
                break;
            case 'mx':
                return 'es';
                break;
            case 'au':
                return 'au';
                break;
        }
    }

    /**
     * @param $marketplaceID
     *
     * @return string
     */
    public static function marketplaceIdToFullfillmentCenterId($marketplaceID)
    {
        switch ($marketplaceID) {
            case 'A13V1IB3VIYZZH':
            case 'A1RKKUPIHCS9HS':
            case 'A1PA6795UKMFR9':
            case 'APJ6JRA9NG5V4':
            case 'A1F83G8C2ARO7P': // Europe
                $fullfillmentCenterId = 'AMAZON_EU';
                break;
            case 'ATVPDKIKX0DER': // US
                $fullfillmentCenterId = 'AMAZON_NA';
                break;
            case 'A2EUQ1WTGCTBG2': // Canada
                $fullfillmentCenterId = '?';
                break;
            case 'A1VC38T7YXB528': // Japan
                $fullfillmentCenterId = 'AMAZON_JP';
                break;
            case 'A21TJRUUN4KGV': // India
                $fullfillmentCenterId = 'AMAZON_IN'; // to be verified
                break;
            case 'A2Q3Y263D00KWC': // Brasil
                $fullfillmentCenterId = 'AMAZON_BR'; // to be verified
                break;
            default:
                $fullfillmentCenterId = '?';
                break;
        }

        return ($fullfillmentCenterId);
    }

    /**
     * @param $price
     * @param null $to_currency
     *
     * @return mixed
     */
    public static function toCurrency($price, $to_currency = null)
    {
        $c_rate = (is_array($to_currency) ? $to_currency['conversion_rate'] : $to_currency->conversion_rate);

        if ($to_currency) {
            $price /= $c_rate;
        }

        return $price;
    }

    /**
     * @param $id_product
     * @param $id_product_attribute
     * @param $id_lang
     * @param $context
     *
     * @return array|bool
     */
    public static function getProductImages($id_product, $id_product_attribute, $id_lang, $context = null)
    {
        $product = new Product($id_product, false, $id_lang, isset($context->shop->id) && $context->shop->id ? $context->shop->id : null, $context);

        if (($cover = Product::getCover($id_product, $context))) {
            $id_image_cover = (int)$cover['id_image'];
        } else {
            $id_image_cover = null;
        }

        $images = $product->getImages($id_lang, $context);

        if (is_array($images) && count($images)) {
            $image_set = array();
            foreach ($images as $image) {
                $image_set[] = $image['id_image'];
            }
        } else {
            $image_set = array();
        }

        if ((int)$id_product_attribute) {
            $images = $product->getCombinationImages($id_lang);
            $id_images = array();

            if (is_array($images) && count($images)) {
                if (isset($images[$id_product_attribute])) {
                    foreach ($images[$id_product_attribute] as $image) {
                        if ($id_image_cover && $image['id_image'] == $id_image_cover) {
                            array_splice($id_images, 0, 0, array($image['id_image']));
                        } else {
                            $id_images[] = $image['id_image'];
                        }
                    }
                } else {
                    $id_images = false;
                }
            } else {
                $images = $product->getImages($id_lang, $context);
                if (is_array($images) && count($images)) {
                    foreach ($images as $image) {
                        if ($id_image_cover && $image['id_image'] == $id_image_cover) {
                            array_splice($id_images, 0, 0, array($image['id_image']));
                        } else {
                            $id_images[] = $image['id_image'];
                        }
                    }
                } else {
                    $id_images = false;
                }
            }
        } else {
            $id_images = array();
            $images = $product->getImages($id_lang, $context);
            if (is_array($images) && count($images)) {
                foreach ($images as $image) {
                    if ($id_image_cover && $image['id_image'] == $id_image_cover) {
                        array_splice($id_images, 0, 0, array($image['id_image']));
                    } else {
                        $id_images[] = $image['id_image'];
                    }
                }
            } else {
                $id_images = false;
            }
        }
        $images = array();

        if ($id_images) {
            foreach ($id_images as $id_image) {
                if (is_array($image_set) && in_array($id_image, $image_set)) { // multistore workarround: getCombinationImages returns images from other shops
                    $images[] = self::getImageUrl($id_image, $id_product);
                }
            }
        }

        return ($images);
    }

    /**
     * @param $id_image
     * @param $productid
     *
     * @return bool|string
     */
    public static function getImageUrl($id_image, $productid)
    {
        $image_type = null;
        $ext = 'jpg';
        $image_obj = new Image($id_image);

        // PS > 1.4.3
        if (method_exists($image_obj, 'getExistingImgPath')) {
            $img_path = $image_obj->getExistingImgPath();
            $imageurl = $img_path;
        } else {
            $imageurl = $productid.'-'.$id_image;
        }

        if (method_exists('ImageType', 'getFormatedName')) {
            $image_type = Configuration::get('AMAZON_IMAGE_TYPE');
        }

        if (Tools::strlen($image_type)) {
            $imageurl = sprintf('%s-%s.%s', $imageurl, $image_type, $ext);
        } else {
            $imageurl = sprintf('%s.%s', $imageurl, $ext);
        }


        return $imageurl;
    }

    /* Check if the condition field is present in the DB (for Prestashop < 1.4) */

    /**
     * @return array|bool|false|mixed|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getConditionField()
    {
        // Products Condition/State
        //
        $sql = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'product` where Field = "condition"';
        $query = Db::getInstance()->executeS($sql);

        if (is_array($query)) {
            $query = array_shift($query);
        }

        if (isset($query['Field']) && $query['Field'] == 'condition') {
            return ($query);
        } else {
            return (false);
        }
    }

    /*
      found there :
      http://fr.php.net/eval
      David Schumann
      04-Nov-2003 08:17
      To evaluate math expressions (multiply, divide, addition, subtraction, percentages),
      use the following function, based on Taras Young's 'evalsum' function posted earlier
      MERCI !
     */

    /**
     * @param $ASIN
     *
     * @return bool
     */
    public static function validateASIN($ASIN)
    {
        return ($ASIN != null && Tools::strlen($ASIN) && preg_match('/^[A-Z0-9]{10}$/', $ASIN));
    }

    /**
     * @param $SKU
     *
     * @return bool
     */
    public static function validateSKU($SKU)
    {
        return ($SKU != null && Tools::strlen($SKU) && preg_match('/[\x00-\xFF]{1,40}/', $SKU) && preg_match('/[^ ]$/', $SKU) && preg_match('/^[^ ]/', $SKU));
    }

    /* http://stackoverflow.com/questions/336127/calculate-business-days
    The function returns the no. of business days between two dates and it skips the holidays*/

    /**
     * @param $startDate
     * @param $endDate
     * @param array $holidays
     *
     * @return float|int
     */
    public static function getWorkingDays($startDate, $endDate, $holidays = array())
    {
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);

        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
        $days = ($endDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date('N', $startDate);
        $the_last_day_of_week = date('N', $endDate);

        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) {
                $no_remaining_days--;
            }

            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) {
                $no_remaining_days--;
            }
        } else {
            // (edit by Tokes to fix an edge case where the start day was a Sunday
            // and the end day was NOT a Saturday)
            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            } else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }

        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
        //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0) {
            $workingDays += $no_remaining_days;
        }

        //We subtract the holidays
        foreach ($holidays as $holiday) {
            $time_stamp = strtotime($holiday);
            //If the holiday doesn't fall in weekend
            if ($startDate <= $time_stamp && $time_stamp <= $endDate && date('N', $time_stamp) != 6 && date('N', $time_stamp) != 7) {
                $workingDays--;
            }
        }

        return $workingDays;
    }


    /**
     * @param $id_lang
     * @param string $type
     *
     * @return string
     */
    public static function documentation($id_lang, $type = 'readme')
    {
        $url = __PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/amazon/documentation';
        $path = _PS_MODULE_DIR_.'/amazon/documentation';

        $current_lang = Tools::strtolower(Language::getIsoById($id_lang));

        $target_file = sprintf('%s/%s_%s.pdf', $path, $type, $current_lang);
        $target_link = sprintf('%s/%s_%s.pdf', $url, $type, $current_lang);

        if (file_exists($target_file)) {
            return ($target_link);
        }

        return (sprintf('%s/%s_en.pdf', $url, $type));
    }

    /**
     * @param $string
     * @param bool $verySafe
     *
     * @return string
     */
    public static function encodeText($string, $verySafe = false)
    {
        if ($verySafe) {
            $string = str_replace('???', "'", $string);
            $string = @utf8_encode(utf8_decode($string));
            $string = html_entity_decode($string, ENT_COMPAT, 'UTF-8');
            $string = self::stripInvalidXml($string);
            $string = str_replace('&#39;', "'", $string);
        }
        if (!Tools::strlen(trim(strip_tags($string)))) {
            return(null);
        }

        return (trim($string));
    }


    /**
     * @param $value
     *
     * @return string
     */
    public static function stripInvalidXml($value)
    {
        $ret = '';
        $current = null;
        if (empty($value)) {
            return $ret;
        }

        $length = strlen($value); //TODO: Multibyte dance, do not replace by Tools::strlen !
        for ($i = 0; $i < $length; $i++) {
            $current = ord($value{$i});
            if (($current == 0x9) || ($current == 0xA) || ($current == 0xD) || (($current >= 0x20) && ($current <= 0xD7FF)) || (($current >= 0xE000) && ($current <= 0xFFFD)) || (($current >= 0x10000) && ($current <= 0x10FFFF))) {
                $ret .= chr($current);
            } else {
                $ret .= ' ';
            }
        }

        return $ret;
    }

    /**
     * @param $moduleName
     *
     * @return bool|null
     * @throws PrestaShopDatabaseException
     */
    public static function moduleIsInstalled($moduleName)
    {
        if (method_exists('Module', 'isInstalled')) {
            return (Module::isInstalled($moduleName));
        } else {
            Db::getInstance()->executeS('SELECT `id_module` FROM `'._DB_PREFIX_.'module` WHERE `name` = \''.pSQL($moduleName).'\'');

            return (bool)Db::getInstance()->NumRows();
        }
    }

    /*
     * Return if a property is accessible (ie: not protected or private)
     */
    /**
     * @param $class
     * @param $property
     *
     * @return bool
     */
    public static function propertyIsAccessible($class, $property)
    {
        if (!method_exists($class, '__construct')) {
            return (false);
        }

        $obj = new $class;
        $vars = get_object_vars($obj);

        return (isset($vars[$property]));
    }

    /**
     * @return string
     */
    public static function getShopUrl()
    {
        $context = Context::getContext();
        $module = Amazon::MARKETPLACE;

        if (Tools::strlen(($name = Tools::getValue('configure')))) {
            $module = $name;
        }

        $url = __PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/'.$module.'/';

        if (method_exists('ShopUrl', 'getShopUrls') && Shop::isFeatureActive()) {
            $shop_url = ShopUrl::getShopUrls($context->shop->id)->where('main', '=', 1)->getFirst();

            if ($shop_url instanceof ShopUrl && Tools::strlen($shop_url->virtual_uri)) {
                $url = $shop_url->physical_uri.$shop_url->virtual_uri.basename(_PS_MODULE_DIR_).'/'.$module.'/';
            }
        }
        return($url);
    }


    /**
     * @param $price
     * @param $formula
     *
     * @return int
     */
    public static function formula($price, $formula)
    {
        if (empty($price)) {
            return ($price);
        }
        if (strpos($formula, '@') === false) {
            return ($price);
        }

        $formula = trim(str_replace(',', '.', $formula));
        $formula = preg_replace("/\\n/i", '', $formula);
        $formula = preg_replace("/\\r/i", '', $formula);

        if (preg_match('#([0-9\., ]*)%#', $formula, $result)) {
            $toPercent = $price * ((float)$result[1] / 100);
            $formula = preg_replace('#([0-9\., ]*)%#', $toPercent, $formula);
        }
        $formula = str_replace('%', '', $formula);
        $equation = str_replace('@', $price ? $price : 0, $formula);

        $result = self::_matheval($equation);

        return ($result);
    }

    /**
     * @param $equation
     *
     * @return int
     */
    private static function _matheval($equation)
    {
        $equation = preg_replace('/[^0-9+\-.*\/()%]/', '', $equation);
        $equation = preg_replace('/([+-])([0-9]+)(%)/', '*(1\$1.\$2)', $equation);
        // you could use str_replace on this next line
        // if you really, really want to fine-tune this equation
        $equation = preg_replace('/([0-9]+)(%)/', '.\$1', $equation);
        if ($equation == '') {
            $return = 0;
        } else {
            eval("\$return=".$equation.";");
        };//TODO: Validation: Backward compatibility requirement, will be deprecated and removed soon

        return $return;
    }

    /**
     * @param $price
     *
     * @return string
     */
    public static function smartRounding($price)
    {
        // Smart Price
        $plain = floor($price);
        $decimals = $price - $plain;
        $decimal_part = (int)((string)$decimals * 100); // https://www.google.fr/search?hl=fr&output=search&sclient=psy-ab&q=php+floor+bug&btnG=&gws_rd=ssl

        if (!$decimals || ($decimal_part % 10) == 0) {
            $rounded = $decimal_part;
        } else {
            $rounded = sprintf('%02d', ((number_format(round($decimals, 1) - 0.1, 2, '.', '') * 100) - 1) + 10);
        }

        $smart_price = sprintf('%d.%02d', $plain, max(0, $rounded));

        return ($smart_price);
    }


    /**
     * @param $source
     * @param $destination
     * @param null $stream_context
     *
     * @return bool|int
     */
    public static function copy($source, $destination, $stream_context = null)
    {
        if (method_exists('Tools', 'copy')) {
            if (is_null($stream_context) && !preg_match('/^https?:\/\//', $source)) {
                return @copy($source, $destination);
            } //TODO: Validation - PS1.4 compat
            return @file_put_contents($destination, AmazonTools::fileGetContents($source, false, $stream_context));//TODO: Validation - PS1.4 compat
        } else {
            return @copy($source, $destination);
        }
    }

    /**
     * @param $func
     * @param $arr
     *
     * @return array
     */
    public static function arrayMapRecursive($func, $arr)
    {
        $a = array();
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                $a[$k] = is_array($v) ? self::arrayMapRecursive($func, $v) : call_user_func($func, $v);
            }
        }
        return $a;
    }

    // Regex from http://www.pelagodesign.com/blog/2009/05/20/iso-8601-date-validation-that-doesnt-suck/

    /**
     * @param $dateStr
     *
     * @return bool
     */
    public static function isIso8601Date($dateStr)
    {
        if (preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/', $dateStr) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * http://php.net/manual/fr/function.array-replace.php is available only since PHP 5.3
     * @return array|null
     */
    public static function arrayReplace()
    {
        $args = func_get_args();
        $num_args = func_num_args();
        $res = array();
        for ($i=0; $i<$num_args; $i++) {
            if (is_array($args[$i])) {
                foreach ($args[$i] as $key => $val) {
                    $res[$key] = $val;
                }
            } else {
                trigger_error(__FUNCTION__ .'(): Argument #'.($i+1).' is not an array', E_USER_WARNING);
                return null;
            }
        }
        return $res;
    }

    /**
     * @param $url
     * @param bool $use_include_path
     * @param null $stream_context
     * @param int $curl_timeout
     * @param null $certificate
     *
     * @return bool|mixed|string
     */
    public static function fileGetContents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 30, $certificate = null, $disable_ssl_check = null)
    {
        if (!$disable_ssl_check) {
            $disable_ssl_check = (bool)Configuration::get('AMAZON_DISABLE_SSL_CHECK');
        }

        return(AmazonTools::file_get_contents($url, $use_include_path, $stream_context, $curl_timeout, $certificate, $disable_ssl_check));
    }

    /**
     * Convert a shorthand byte value from a PHP configuration directive to an integer value (copy from PS 1.5 class for PS1.4 compat)
     * @param string $value value to convert
     * @return int
     */
    public static function convertBytes($value)
    {
        if (is_numeric($value)) {
            return $value;
        } else {
            $value_length = Tools::strlen($value);
            $qty = (int)Tools::substr($value, 0, $value_length - 1);
            $unit = Tools::strtolower(Tools::substr($value, $value_length - 1));
            switch ($unit) {
                case 'k':
                    $qty *= 1024;
                    break;
                case 'm':
                    $qty *= 1048576;
                    break;
                case 'g':
                    $qty *= 1073741824;
                    break;
            }
            return $qty;
        }
    }

    /**
     * @param $str
     *
     * @return string
     */
    public static function noAccents($str)
    {
        $str = mb_convert_encoding($str, 'HTML-ENTITIES', 'UTF-8');
        $searches = array('&szlig;', '&(..)lig;', '&([aouAOU])uml;', '&(.)[^;]*;');
        $replacements = array('ss', '\\1', '\\1'.'e', '\\1');
        foreach ($searches as $key => $search) {
            $str = mb_ereg_replace($search, $replacements[$key], $str);
        }
        return($str);
    }


    /**
     * @param $text
     *
     * @return bool|string
     */
    public static function getFriendlyUrl($text)
    {
        $text = htmlentities($text);
        $text = preg_replace(array('/&szlig;/', '/&(..)lig;/', '/&([aouAOU])uml;/', '/&(.)[^;]*;/'), array(
        'ss',
        '$1',
        '$1'.'e',
        '$1'
        ), $text);
        $text = preg_replace('/[\x00-\x1F\x21-\x2B\x3A-\x3F\x5B-\x60\x7B-\x7F]/', '', $text); // remove non printable
        $text = preg_replace('/[ \t]+/', '-', $text);
        $text = str_replace(array('_', ',', '.', '/', '+', '?', '&', '='), '-', $text);

        return Tools::strtolower(trim($text));
    }


    /**
     * @param $val
     *
     * @return array|string
     */
/*
    public static function fixEncoding($val)
    {
        if (is_array($val)) {
            foreach ($val as $key => $str) {
                $val[$key] = self::fixEncoding($val[$key]);
            }
            return($val);
        } else {
            if (function_exists('iconv')) {
                $result = iconv("ISO-8859-1", "UTF-8", $val);
            } else {
                $result = mb_convert_encoding($val, "UTF-8", mb_detect_encoding($val, "auto"));
            }
            return($result);
        }
    }
    */

    public static function fixEncoding($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = self::fixEncoding($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        }
        return $mixed;
    }
    public static function fixEncodingOld(&$tofix)
    {
        if (is_array($tofix)) {
            foreach ($tofix as $key => $item) {
                self::fixEncoding($item);
            }
        } else {
            if (!mb_check_encoding($tofix, 'UTF-8')) {
                $tofix = mb_convert_encoding($tofix, "UTF-8");
            }
        }

        return ($tofix);
    }

    /**
     * Same as AmazonTools::pre()
     * todo: Remove this function after all modules use same CommonTools
     * @param array $content
     * @param bool $return
     *
     * @return string
     */
    public static function pre($content, $return = false)
    {
        $result = '';
        $result .= html_entity_decode("&lt;pre&gt;");
        foreach ($content as $string) {
            $result .= print_r($string, true);
        }
        $result .= html_entity_decode('&lt;/pre&gt;');

        if (! $return) {
            print_r($result);
        }
        return $result;
    }

    /**
     * CommonTools::fieldExists not working
     * @param $table
     * @param $field
     *
     * @return bool
     */
    public static function amazonFieldExists($table, $field)
    {
        $query = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `'.pSQL($table).'`');

        if (!is_array($query) || !count($query)) {
            return false;
        }

        foreach ($query as $row) {
            if ($row['Field'] == $field) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param $var
     *
     * @return bool
     */
    public static function filterNull(&$var)
    {
        return ($var !== null && $var !== false && $var !== '');
    }

    /**
     * @param $array
     *
     * @return array
     */
    public static function filterRecursive($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = self::filterRecursive($value);
            }
        }

        return array_filter($array, array(__CLASS__, 'filterNull'));
    }

    /**
     * Build condition for query, this use in case: WHERE IN [result_of_this_function]
     * @param string|array $input
     * @param $looseCase
     * @return bool|string
     */
    public static function buildQueryConditionIn($input, $looseCase)
    {
        if (is_string($input)) {
            $input = array($input);
        }

        if (!is_array($input) || !count($input)) {
            return false;
        }

        $sanity_input = array();
        foreach ($input as $value) {
            if ($looseCase) {
                $sanity_input[] = '"'.pSQL(strtolower(trim($value))).'"';
            } else {
                $sanity_input[] = '"'.pSQL(trim($value)).'"';
            }
        }

        return implode(',', $sanity_input);
    }

    /**
     * Get all from db related to customer's email or name
     * marketplace_order_address: email
     * marketplace_orders: buyer_name
     * marketplace_stats: buyer_email, buyer_name
     * @param $email
     * @param string|array $name
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public static function getAllCustomerMpOrderIds($email, $name)
    {
        $orderAddress = $order = $statByEmail = $statByName = array();
        if ($email && Tools::strlen($email)) {
            $orderAddress   = AmazonAddress::getAllMpOrderIdsByEmail($email);
            $statByEmail    = AmazonStat::getAllStatMpOrderIdsByBuyerEmail($email);
        }
        if ((is_array($name) && count($name)) || (is_string($name) && Tools::strlen($name))) {
            $order          = AmazonOrder::getAllMpOrderIdsByBuyerName($name);
            $statByName     = AmazonStat::getAllStatMpOrderIdsByBuyerName($name);
        }

        $orderIds = array();
        foreach (array($orderAddress, $order, $statByEmail, $statByName) as $source) {
            if (is_array($source) && count($source)) {
                foreach ($source as $record) {
                    $orderIds[] = $record['mp_order_id'];
                }
            }
        }
        $orderIds = array_unique($orderIds);

        return $orderIds;
    }

    /**
     * Get all from db related to `mp_order_id` extract from first step
     * marketplace_order_address, marketplace_order_items, marketplace_orders, marketplace_stats, marketplace_vat_report
     * @param $orderIds
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public static function getAllCustomerDataByMpOrderIds($orderIds)
    {
        $addressData    = AmazonAddress::getAllByMpOrderIds($orderIds);
        $orderData      = AmazonOrder::getAllByMpOrderIds($orderIds);
        $orderItemData  = AmazonOrderItem::getAllByMpOrderIds($orderIds);
        $statData       = AmazonStat::getAllStatsByMpOrderIds($orderIds);
        $vatData        = AmazonStat::getAllVatsByMpOrderIds($orderIds);

        $result = array(
            'address'    => $addressData ? $addressData : array(),
            'order'      => $orderData ? $orderData : array(),
            'order_item' => $orderItemData ? $orderItemData : array(),
            'stat'       => $statData ? $statData : array(),
            'vat'        => $vatData ? $vatData : array()
        );

        return $result;
    }

    /**
     * Delete all from db related to `mp_order_id` extract from first step
     * @param $orderIds
     * @return bool
     */
    public static function deleteAllCustomerDataByMpOrderId($orderIds)
    {
        $result = AmazonAddress::deleteAllByMpOrderIds($orderIds)
            && AmazonOrderItem::deleteAllByMpOrderIds($orderIds)
            && AmazonOrder::deleteAllByMpOrderIds($orderIds)
            && AmazonStat::deleteAllStatByMpOrderIds($orderIds)
            && AmazonStat::deleteAllVatByMpOrderIds($orderIds);

        return $result;
    }
}
