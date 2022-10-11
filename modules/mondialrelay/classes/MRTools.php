<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/*
** Some tools using used in the module
*/
class MRTools
{
    const REGEX_CLEAN_ADDR = '/[^a-zA-Z0-9-\s\'\!\,\|\(\)\.\*\&\#\/\:]/';

    const REGEX_CLEAN_PHONE = '/[^0-9+\(\)]*/';

    /**
     * Check if a string could be UTF8 one
     *
     * @static
     * @param $str
     * @return bool
     */
    public static function seemsUTF8($str)
    {
        $length = Tools::strlen($str);

        for ($i = 0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) {
                $n = 0;
            } elseif (($c & 0xE0) == 0xC0) {
                # 0bbbbbbb
                $n = 1;
            } elseif (($c & 0xF0) == 0xE0) {
                # 110bbbbb
                $n = 2;
            } elseif (($c & 0xF8) == 0xF0) {
                # 1110bbbb
                $n = 3;
            } elseif (($c & 0xFC) == 0xF8) {
                # 11110bbb
                $n = 4;
            } elseif (($c & 0xFE) == 0xFC) {
                # 111110bb
                $n = 5;
            } else {
                # 1111110b
                return false;
            } # Does not match any model
            for ($j = 0; $j < $n; $j++) {
                # n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Converts all accent characters to ASCII characters.
     *
     * If there are no accent characters, then the string given is just returned.
     *
     * @param string $string Text that might have accent characters
     * @return string Filtered string with replaced "nice" characters.
     */
    public static function removeAccents($string)
    {

        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        $double_chars  = array(
            chr(197).chr(146) => 'OE',
            chr(197).chr(147) => 'oe',
        );

        $string = strtr($string, $double_chars);

        $string = utf8_decode($string);

        $double_chars_decoded  = array(
            chr(198) => 'AE',
            chr(208) => 'DH',
            chr(222) => 'TH',
            chr(223) => 'ss',
            chr(230) => 'ae',
            chr(240) => 'dh',
            chr(254) => 'th',
        );

        $string = strtr($string, $double_chars_decoded);


        $chars_simple = array(
            chr(128) => 'E',
            chr(131) => 'f',
            chr(138) => 'S',
            chr(142) => 'Z',
            chr(154) => 's',
            chr(158) => 'z',
            chr(159) => 'Y',
            chr(162) => 'c',
            chr(165) => 'Y',
            chr(181) => 'u',
            chr(192) => 'A',
            chr(193) => 'A',
            chr(194) => 'A',
            chr(195) => 'A',
            chr(196) => 'A',
            chr(197) => 'A',
            chr(199) => 'C',
            chr(200) => 'E',
            chr(201) => 'E',
            chr(202) => 'E',
            chr(203) => 'E',
            chr(204) => 'I',
            chr(205) => 'I',
            chr(206) => 'I',
            chr(207) => 'I',
            chr(209) => 'N',
            chr(210) => 'O',
            chr(211) => 'O',
            chr(212) => 'O',
            chr(213) => 'O',
            chr(214) => 'O',
            chr(216) => 'O',
            chr(217) => 'U',
            chr(218) => 'U',
            chr(219) => 'U',
            chr(220) => 'U',
            chr(221) => 'Y',
            chr(224) => 'a',
            chr(225) => 'a',
            chr(226) => 'a',
            chr(227) => 'a',
            chr(228) => 'a',
            chr(229) => 'a',
            chr(231) => 'c',
            chr(232) => 'e',
            chr(233) => 'e',
            chr(234) => 'e',
            chr(235) => 'e',
            chr(236) => 'i',
            chr(237) => 'i',
            chr(238) => 'i',
            chr(239) => 'i',
            chr(241) => 'n',
            chr(242) => 'o',
            chr(243) => 'o',
            chr(244) => 'o',
            chr(245) => 'o',
            chr(246) => 'o',
            chr(248) => 'o',
            chr(249) => 'u',
            chr(250) => 'u',
            chr(251) => 'u',
            chr(252) => 'u',
            chr(253) => 'y',
            chr(255) => 'y'
        );

        $string = strtr($string, $chars_simple);

        return $string;
    }

    /* Add for 1.3 compatibility and avoid duplicate code */
    public static function jsonEncode($result)
    {
        return (method_exists('Tools', 'jsonEncode')) ?
            Tools::jsonEncode($result) : Tools::jsonEncode($result);
    }

    /*
    ** Fix security and compatibility for PS < 1.4.5
    */
    public static function bqSQL($string)
    {
        return str_replace('`', '\`', pSQL($string));
    }

    /*
    ** Check zip code by country
    */
    public static function checkZipcodeByCountry($zipcode, $params)
    {
        $id_country = $params['id_country'];

        $zipcodeFormat = Db::getInstance()->getValue('
                SELECT `zip_code_format`
                FROM `'._DB_PREFIX_.'country`
                WHERE `id_country` = '.(int)$id_country);

        // -1 to warn user that no layout exist
        if (!$zipcodeFormat) {
            return -1;
        }

        $regxMask = str_replace(
            array('N', 'C', 'L'),
            array(
                '[0-9]',
                Country::getIsoById((int)$id_country),
                '[a-zA-Z]'
            ),
            $zipcodeFormat
        );
        if (preg_match('/'.$regxMask.'/', $zipcode)) {
            return true;
        }

        return false;
    }

    public static function getFormatedPhone($phone_number)
    {
        $begin      = Tools::substr($phone_number, 0, 3);
        $pad_number = (strpos($begin, '+3') !== false) ? 12 :
            (strpos($begin, '00') ? 13 : 10);

        return str_pad(
            Tools::substr(preg_replace(MRTools::REGEX_CLEAN_PHONE, '', $phone_number), 0, $pad_number),
            $pad_number,
            '0',
            STR_PAD_LEFT
        );
    }
}
