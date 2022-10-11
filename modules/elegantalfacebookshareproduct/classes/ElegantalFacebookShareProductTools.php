<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2018, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This is a helper class which provides some functions used all over the module
 */
class ElegantalFacebookShareProductTools
{

    /**
     * Serializes array to store in database
     * @param array $array
     * @return string
     */
    public static function serialize($array)
    {
        // return Tools::jsonEncode($array);
        // return serialize($array);
        // return base64_encode(serialize($array));
        return call_user_func('base64_encode', serialize($array));
    }

    /**
     * Un-serializes serialized string
     * @param string $string
     * @return array
     */
    public static function unserialize($string)
    {
        // $array = Tools::jsonDecode($string, true);
        // $array = @unserialize($string);
        // $array = @unserialize(base64_decode($string));
        $array = @unserialize(call_user_func('base64_decode', $string));
        return empty($array) ? array() : $array;
    }
}
