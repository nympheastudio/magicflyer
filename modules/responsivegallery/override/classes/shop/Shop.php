<?php
/**
 * Responsive gallery
 *
 * @author    Studio Kiwik
 * @copyright Studio Kiwik 2013-2015
 * @license   http://licences.studio-kiwik.fr/responsivegallery
 */

class Shop extends ShopCore
{
    /**
     * Add table associated to shop
     *
     * @param string $table_name
     * @param array $table_details
     * @return bool
     */
    public static function addTableAssociation($table_name, $table_details)
    {
        if (!isset(Shop::$asso_tables[$table_name])) {
            Shop::$asso_tables[$table_name] = $table_details;
        } else {
            return false;
        }

        return true;
    }
}
