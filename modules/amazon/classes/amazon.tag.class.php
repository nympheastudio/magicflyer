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

class AmazonTag extends Tag
{
    const TAG_MAX_LENGTH = 50 ;
    const TAG_MAX_TAGS = 5 ;

    public static function getMarketplaceTags($product, $id_lang, $backward_compatibility = false)
    {
        if (!Validate::isLoadedObject($product)) {
            return (null);
        }

        if (!method_exists('Tag', 'getProductTags') || $backward_compatibility) {
            return (AmazonTools::encodeText(self::friendlyKeywords(strip_tags($product->meta_keywords))));
        } else {
            $tags = parent::getProductTags($product->id);

            if (isset($tags[$id_lang]) && is_array($tags[$id_lang]) && count($tags[$id_lang])) {
                $items = array_filter(array_unique($tags[$id_lang]));

                $tags_array = array();

                // first way: there are less/equal than max tags , all lines are less than TAG_MAX_LENGTH, we export as is.
                if (is_array($items) && count($items) <= self::TAG_MAX_TAGS) {
                    $pass = true;

                    foreach ($items as $item) {
                        if (Tools::strlen(AmazonTools::encodeText($item)) > self::TAG_MAX_LENGTH) {
                            $pass = false;
                        }

                        continue;
                    }

                    if ($pass) {
                        foreach ($items as $item) {
                            $tags_array[] = AmazonTools::encodeText($item);
                        }
                    }
                }

                // second way: remodeling the tags
                if (!is_array($tags_array) || !count($tags_array)) {
                    for ($i = 0; $i < self::TAG_MAX_TAGS; $i++) {
                        if (!is_array($items) || !count($items)) {
                            continue;
                        }

                        $all_tags = implode(' ', $items);

                        $tag_line = wordwrap(preg_replace('/([\s]){1,}/', '$1', $all_tags), self::TAG_MAX_LENGTH, '|', true);
                        $tag_lines = preg_split("/\|/", $tag_line);

                        if (is_array($tag_lines) && count($tag_lines)) {
                            $tags_array[] = Tools::substr(AmazonTools::encodeText(reset($tag_lines)), 0, self::TAG_MAX_LENGTH);
                            $items = array_slice($tag_lines, 1);
                        } else {
                            $items = array();
                        }
                    }
                }
                return ($tags_array);
            } else {
                // Previously (v3.9 <=), we were using only meta_keywords
                // returns like in previous versions for compatibility
                return (self::getMarketplaceTags($product, $id_lang, true));
            }
        }
    }

    /*
     * Old method / PS 1.3 and 1.4
     */
    public static function friendlyKeywords($text)
    {
        $text = html_entity_decode($text);
        $text = preg_replace(array('/&szlig;/', '/&(..)lig;/', '/&([aouAOU])uml;/', '/&(.)[^;]*;/'), array(
                'ss',
                '$1',
                '$1'.'e',
                '$1'
            ), $text);
        $text = preg_replace('/[\x00-\x1F\x21-\x2B\x3A-\x3F\x5B-\x60\x7B-\x7F]/', '', $text); // remove non printable
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = str_replace(array('_', ',', '.', '/', '+', '?', '&', '=', '-'), ' ', $text);
        $text = preg_replace('/\b[^\s]{1,3}\b/u', '', $text);
        $text = preg_replace('/[ ,]+/', ',', $text);
        $text = preg_replace('/[ ,]$/', '', $text);

        $items = explode(',', $text);
        if ($items) {
            $items = array_unique($items);
            array_splice($items, self::TAG_MAX_TAGS);
            $text = implode(',', $items);
        }
        $text = Tools::strtolower(trim(str_replace(',', ' ', $text)));

        return ($text);
    }
}
