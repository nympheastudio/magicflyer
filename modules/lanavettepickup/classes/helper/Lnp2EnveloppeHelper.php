<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class Lnp2EnveloppeHelper
{

    /**
     * array (max height, max width, max depth)
     *
     */
    private static $dimensions = array(
        array(11, 11, 35),
        array(12, 12, 35),
        array(13, 13, 35),
        array(14, 14, 35),
        array(15, 15, 35),
        array(16, 16, 35),
        array(17, 17, 35),
        array(18, 18, 35),
        array(19, 19, 33),
        array(20, 20, 30),
        array(21, 21, 27),
        array(22, 22, 25),
        array(23, 23, 23),
        array(24, 24, 21),
        array(25, 25, 19),
        array(26, 26, 18),
        array(27, 27, 16),
        array(28, 28, 15),
        array(29, 29, 14),
        array(30, 30, 13),
        array(31, 31, 12),
        array(32, 32, 12),
        array(33, 33, 11),
        array(34, 34, 10),
        array(35, 35, 10),
        array(36, 36, 9),
        array(37, 37, 9),
        array(38, 38, 8),
        array(39, 39, 7),
        array(40, 40, 7),
        array(41, 41, 5),
        array(42, 42, 3),
        array(43, 43, 3),
        array(44, 44, 3),
        array(45, 45, 3),
        array(46, 46, 2),
        array(46, 46, 3),
        array(47, 47, 2),
        array(48, 48, 2),
        array(49, 49, 2),
    );

    public static function canFit($width, $height, $depth)
    {

        foreach (self::$dimensions as $dimension) {
            if (
                ($height <= $dimension[0])
                &&
                ($width <= $dimension[1])
                &&
                ($depth <= $dimension[2])
            ) {
                return true;
            }
        }

        return false;
    }
}
