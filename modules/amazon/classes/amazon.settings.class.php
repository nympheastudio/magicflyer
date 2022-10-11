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

require_once(dirname(__FILE__).'/../classes/amazon.tools.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.certificates.class.php');

class AmazonSettings extends Amazon
{
    const MANDATORY = 1;
    const RECOMMENDED = 2;

    const SOURCE_URL = 'https://common-services-public.s3.amazonaws.com/amazon/'; // Public directory on S3

    /**
     * @param $file
     * @param $local_dir
     * @param $remote_dir
     * @param $url
     * @param bool $force
     *
     * @return bool
     */
    public static function cache($file, $local_dir, $remote_dir, $url, $force = false)
    {
        if (!is_dir($local_dir)) {
            return (false);
        }

        $local_file = $local_dir.$file;
        $remote_file = $url.$remote_dir.$file;

        if (!is_writable($local_dir)) {
            chmod($local_dir, 0775);
        }

        if (!$force) {
            if (file_exists($local_file) && filesize($local_file) > 64) {
                if ((time() - filemtime($local_file)) < (60 * 60 * 24 * 15)) {
                    return (false);
                }
            } // Local file is not expired
        }

        $contents = AmazonTools::fileGetContents($remote_file, false, null, 30);

        if (Tools::strlen($contents) > 64) {
            if (file_exists($local_file)) {
                @unlink($local_file);
            }

            file_put_contents($local_file, $contents);

            return (true);
        }

        return (false);
    }


    /**
     * @return null|string
     */
    public static function getFieldsSettingsDir()
    {
        $datadir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR;

        if (!is_dir($datadir)) {
            @mkdir($datadir);
        }

        if (!is_dir($datadir)) {
            return (null);
        }

        return ($datadir);
    }

    /**
     * @param $lang
     * @param $universe
     * @param $product_type
     *
     * @return array|bool
     */
    public static function getFieldsSettings($lang, $universe, $product_type)
    {
        static $field_settings_cache = array();

        if (empty($lang) || empty($universe) || empty($product_type)) {
            return (false);
        }
        $cache_key = AmazonTools::toKey(sprintf('%s/%s/%s', $lang, $universe, $product_type));

        if (isset($field_settings_cache[$cache_key])) {
            return($field_settings_cache[$cache_key]);
        }

        $datadir = self::getFieldsSettingsDir();

        if (!$datadir) {
            return (false);
        }

        $remote_dir = 'settings/models/'.AmazonTools::toKey($universe).'/';
        $sub_dir = $datadir.AmazonTools::toKey($universe).DIRECTORY_SEPARATOR;

        if (!is_dir($sub_dir)) {
            @mkdir($sub_dir);
        }

        $filename = sprintf('%s.%s.csv.gz', AmazonTools::toKey($product_type), $lang);
        $file = sprintf('%s%s', $sub_dir, $filename);

        #if (Tools::getValue('configure') != Amazon::MARKETPLACE) // Fetch a remote file only on Ajax calls
        @self::cache($filename, $sub_dir, $remote_dir, self::SOURCE_URL);

        if (file_exists($file) && is_readable($file)) {
            if (!($csvfh = fopen('compress.zlib://'.$file, 'r'))) {
                return (false);
            }

            $field_settings = array();

            while ($data = fgetcsv($csvfh, 1024, ";")) {
                if (is_array($data) && count($data) < 5) {
                    continue;
                }
                if (!isset($data[5])) {
                    $data[5] = '';
                }

                $key = str_replace('_', '', AmazonTools::toKey($data[0]));
                $translation = $data[1];
                $description = $data[2];
                $tip = $data[3];
                $sample = $data[4];
                $type_key = AmazonTools::toKey($data[5]);

                if (!Tools::strlen($type_key)) {
                    continue;
                }

                if (in_array($type_key, array(
                        'obligatoire',
                        'mandatory',
                        'required',
                        'obbligatorio',
                        'obligatorio',
                        'erforderlich'
                    ))) {
                    $type = self::MANDATORY;
                } elseif (in_array($type_key, array(
                        'souhait',
                        'preferred',
                        'consigliato',
                        'recomendado',
                        'empfohlen',
                        'erwunscht'
                    ))) {
                    $type = self::RECOMMENDED;
                } else {
                    $type = null;
                }

                $field_setting = array();
                $field_setting['key'] = $key;
                $field_setting['translation'] = $translation;
                $field_setting['description'] = $description;
                $field_setting['tip'] = $tip;
                $field_setting['sample'] = $sample;
                $field_setting['type'] = $type;

                $field_settings[$key] = $field_setting;
            }
            $field_settings_cache[$cache_key] = $field_settings;

            if (is_array($field_settings) && count($field_settings)) {
                return ($field_settings);
            }
        }

        return (false);
    }

    /**
     * @param $lang
     * @param $universe
     * @param $product_type
     * @param $field
     *
     * @return null
     */
    public static function getFieldSettting($lang, $universe, $product_type, $field)
    {
        static $fields_settings = array();

        if (empty($lang) || empty($universe) || empty($product_type) || empty($field)) {
            return (null);
        }

        if (!isset($fields_settings[$lang]) || !is_array($fields_settings[$lang]) || !count($fields_settings[$lang])) {
            $fields_settings[$lang] = self::getFieldsSettings($lang, $universe, $product_type);
        }

        if (is_array($fields_settings[$lang]) && count($fields_settings[$lang])) {
            $key = AmazonXSD::toKey($field);

            foreach (array(
                         null,
                         'type',
                         'map',
                         'style',
                         'name',
                         'length',
                         'size',
                         'weigth',
                         'width',
                         'height',
                         'id',
                         'url',
                         'date',
                         'reason',
                         'year',
                         's'
                     ) as $ext) {
                if (isset($fields_settings[$lang][$key.$ext]) && is_array($fields_settings[$lang][$key.$ext])) {
                    return ($fields_settings[$lang][$key.$ext]);
                }
            }
        }

        return (null);
    }

    /**
     * @param $target
     *
     * @return null|string
     */
    public static function getTranslationsDir($target)
    {
        $dir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'translations'.DIRECTORY_SEPARATOR;

        if (!is_dir($dir)) {
            @mkdir($dir);
        }

        if (!is_dir($dir)) {
            return (null);
        }

        $dir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'translations'.DIRECTORY_SEPARATOR.$target.DIRECTORY_SEPARATOR;

        if (!is_dir($dir)) {
            @mkdir($dir);
        }

        if (!is_dir($dir)) {
            return (null);
        }

        return ($dir);
    }

    /**
     * @param $lang
     * @param $universe
     * @param string $target
     *
     * @return array|bool|null
     */
    public static function getTranslations($lang, $universe, $target = 'attributes')
    {
        if (empty($target) || empty($universe) || empty($lang)) {
            return (null);
        }

        $output_translations = array();
        $datadir = self::getTranslationsDir($target);

        if (!$datadir) {
            return (false);
        }

        if ($universe == 'ClothingAccessories') {
            $universe = 'ProductClothing';
        }

        $remote_dir = sprintf('settings/translations/%s/', $target);
        $filename = sprintf('%s.ini.gz', AmazonXSD::toKey($universe));

        self::cache($filename, $datadir, $remote_dir, self::SOURCE_URL);

        $file = $datadir.$filename;

        if (file_exists($file) && is_readable($file) && function_exists('parse_ini_file')) {
            if (!($ini = parse_ini_file('compress.zlib://'.$file, true))) {
                if (Amazon::$debug_mode) {
                    die(sprintf('%s(#%d): parse_ini_file failed in "%s"', basename(__FILE__), __LINE__, $file));
                }
            }

            if (!is_array($ini) || !count($ini)) {
                return (false);
            }

            if (isset($ini[$lang]) && is_array($ini[$lang]) && count($ini[$lang])) {
                $output_translations = array();

                foreach ($ini[$lang] as $key => $translation) {
                    $new_key = AmazonXSD::toKey(str_replace('_', '', $key));
                    $output_translations[$new_key] = AmazonTools::ucfirst(trim(Tools::stripslashes($translation)));
                }
            } elseif (isset($ini['en']) && is_array($ini['en']) && count($ini['en'])) {
                $output_translations = array();

                foreach ($ini['en'] as $key => $translation) {
                    $new_key = AmazonXSD::toKey(str_replace('_', '', $key));
                    $output_translations[$new_key] = AmazonTools::ucfirst(trim(Tools::stripslashes($translation)));
                }
            }

            if (is_array($output_translations) && count($output_translations)) {
                return ($output_translations);
            }
        }

        return (false);
    }

    /**
     * @param $lang
     * @param $universe
     * @param $field
     *
     * @return null
     */
    public static function getFieldTranslation($lang, $universe, $field)
    {
        static $translations = array();

        if (!isset($translations[$lang]) || !is_array($translations[$lang]) || !count($translations[$lang])) {
            $translations[$lang] = self::getTranslations($lang, $universe);
        }

        if (isset($translations[$lang]) && is_array($translations[$lang]) && count($translations[$lang])) {
            $key = AmazonXSD::toKey($field);

            foreach (array(
                         null,
                         'type',
                         'map',
                         'style',
                         'name',
                         'string',
                         'length',
                         'size',
                         'weigth',
                         'width',
                         'height',
                         'id',
                         'url',
                         'date',
                         'reason',
                         'year',
                         's'
                     ) as $ext) {
                if (isset($translations[$lang][$key.$ext]) && Tools::strlen($translations[$lang][$key.$ext])) {
                    return ($translations[$lang][$key.$ext]);
                }
            }
        }

        return (null);
    }


    /**
     * @param $lang
     * @param $universe
     * @param $product_type
     *
     * @return null
     */
    public static function getProductTypeTranslation($lang, $universe, $product_type)
    {
        static $translations = array();

        if (empty($product_type)) {
            return (null);
        }

        if (!isset($translations[$lang]) || !is_array($translations[$lang]) || !count($translations[$lang])) {
            $translations[$lang] = self::getTranslations($lang, $universe, 'product_types');
        }

        if (isset($translations[$lang]) && is_array($translations[$lang]) && count($translations[$lang])) {
            $key = AmazonXSD::toKey($product_type);

            if (isset($translations[$lang][$key]) && Tools::strlen($translations[$lang][$key])) {
                return ($translations[$lang][$key]);
            }
        }

        return (null);
    }

    /**
     * @param $lang
     *
     * @return mixed|null
     */
    public static function getUniversesTranslation($lang)
    {
        static $translations = array();

        if (empty($lang)) {
            return (null);
        }

        if (!isset($translations[$lang]) || !is_array($translations[$lang]) || !count($translations[$lang])) {
            $translations[$lang] = self::getTranslations($lang, 'universes', 'universes');
        }

        if (isset($translations[$lang]) && is_array($translations[$lang]) && count($translations[$lang])) {
            return ($translations[$lang]);
        }

        return (null);
    }

    /**
     * @param string $type
     * @param bool $force
     *
     * @return array
     */
    public static function getShippingMethods($type = AmazonCarrier::SHIPPING_STANDARD, $force = false)
    {
        if (empty($type)) {
            return (array());
        }

        $shipping_methods = array();
        $datadir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'carriers'.DIRECTORY_SEPARATOR;

        $filename = sprintf('amazon_%s_carriers.ini', $type);
        $file = $datadir.$filename;

        $remote_dir = 'settings/carriers/';

        if (!file_exists($file) || $force) {
            self::cache($filename, $datadir, $remote_dir, self::SOURCE_URL, $force);
        }

        if (file_exists($file) && is_readable($file)) {
            $content_array = file($file);

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf("AmazonSettings::getShippingMethods: %s count: %d\n", $file, is_array($content_array) ? count($content_array) : 0));
            }

            if (is_array($content_array) && count($content_array)) {
                foreach ($content_array as $line) {
                    $line = trim($line);

                    if (!preg_match('/^([^(\s;#)])[(*UTF8)[:alnum:][:punct:][:space:]]*$/u', $line)) {
                        continue;
                    }

                    $shipping_method = trim(Tools::stripslashes($line));
                    $shipping_methods[] = $shipping_method;
                }
                $shipping_methods = array_unique($shipping_methods);
            }
        }
        return (array_values($shipping_methods));
    }

    /**
     * @param $lang
     * @param $section
     *
     * @return array|bool
     */
    public static function getGlossary($lang, $section)
    {
        if (empty($section)) {
            return (false);
        }

        $sections = array();
        $dataDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'glossary'.DIRECTORY_SEPARATOR.$section.DIRECTORY_SEPARATOR;

        if (!is_dir($dataDir)) {
            return (false);
        }

        $glossary = array();
        $files = glob($dataDir.'*.txt');

        $amazon_features = Amazon::getAmazonFeatures();
        $is_amazon_lite = isset($amazon_features['module']) && $amazon_features['module'] == 'amazonlite';

        if ($files) {
            if (is_array($files)) {
                foreach ($files as $file) {
                    $shortFileName = preg_replace('/\.[a-z]{2}/', '', basename($file, '.txt'));
                    $sections[] = $shortFileName;
                }

                if (is_array($sections) && count($sections)) {
                    foreach (array_unique($sections) as $term) {
                        $target_file = $dataDir.sprintf('%s.%s.txt', $term, $lang);
                        $alt_file = $dataDir.sprintf('%s.en.txt', $term);

                        if (file_exists($target_file)) {
                            $file = $target_file;
                        } elseif (file_exists($alt_file)) {
                            $file = $alt_file;
                        } else {
                            $file = null;
                        }

                        if ($file) {
                            $glossary_content = AmazonTools::fileGetContents($file);

                            //remove link if module is lite version
                            if ($is_amazon_lite) {
                                $reg_exUrl = "/(.+)?(http|https)\:\/\/documentation.common-services.com(\/\S*)?/";
                                $glossary_content = preg_replace($reg_exUrl, '', $glossary_content);
                            }

                            // https://css-tricks.com/snippets/php/find-urls-in-text-make-links/
                            $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

                            if (preg_match($reg_exUrl, $glossary_content, $url) && isset($url[0])) {
                                $glossary_content = preg_replace($reg_exUrl, html_entity_decode('&lt;a href="'.$url[0].'" target="_blank"&gt;').$url[0].html_entity_decode('&lt;/a&gt;'), $glossary_content);
                            }

                            // change relative to absolute uri
                            $glossary_content = str_replace('src="', 'src="'.AmazonTools::getShopUrl(), $glossary_content);

                            $glossary[$term] = nl2br($glossary_content);
                        }
                    }
                }
            }
        }
        return ($glossary);
    }

    public static function getSubscribedFeatures()
    {
        $datadir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'features'.DIRECTORY_SEPARATOR;

        if (!is_dir($datadir)) {
            return (false);
        }

        $features = array();

        $real_features = array();
        $files = glob($datadir.'*.ini');

        if ($files && is_array($files)) {
            foreach ($files as $file) {
                if (!($ini = parse_ini_file($file, false))) {
                    if (Amazon::$debug_mode) {
                        Tools::displayError(sprintf('%s(#%d): parse_ini_file failed in "%s"', basename(__FILE__), __LINE__, $file));
                        return (false);
                    }
                }

                if (is_array($ini) && isset($ini['weight'])) {
                    $weight = $ini['weight'];
                    if (Amazon::ENABLE_EXPERIMENTAL_FEATURES && strstr($file, 'amazonlite')) {
                        //$weight = 190;
                    }
                    $features[$weight] = $ini;
                }
            }
        }
        krsort($features);

        if (is_array($features) && count($features)) {
            return(reset($features));
        } else {
            return(false);
        }
    }
}
