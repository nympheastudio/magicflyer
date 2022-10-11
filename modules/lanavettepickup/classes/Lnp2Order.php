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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Lnp2Order extends ObjectModel
{
    public $id;
    public $id_order;
    public $navette_code;
    public $navette_pdf_url;
    public $lnp_order_number;
    public $delivery_id;
    public $delivery_name;
    public $delivery_address;
    public $delivery_city;
    public $delivery_country_code;
    public $delivery_zip_code;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table'     => 'lnp2_orders',
        'primary'   => 'id_lnp2_order',
        'multilang' => false,
        'fields'    => array(
            'id_order'              => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'navette_code'          => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'navette_pdf_url'       => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'lnp_order_number'      => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'delivery_id'           => array('type' => self::TYPE_INT, 'validate' => 'isString'),
            'delivery_name'         => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'delivery_address'      => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'delivery_city'         => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'delivery_country_code' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'delivery_zip_code'     => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_add'              => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd'              => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    public static function install()
    {
        // Create Category Table in Database
        $sql   = array();
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::$definition['table'].'` (
				  	`'.self::$definition['primary'].'` int(16) NOT NULL AUTO_INCREMENT,
                    `id_order` int(16) NOT NULL,
				 	`navette_code` VARCHAR(255) NOT NULL,
				 	`navette_pdf_url` VARCHAR(255) NOT NULL,
				 	`lnp_order_number` VARCHAR(255) NOT NULL,
                    `delivery_id` int(16) NOT NULL,
                    `delivery_name` VARCHAR(70) NOT NULL,
                    `delivery_address` VARCHAR(255) NOT NULL,
                    `delivery_city` VARCHAR(70) NOT NULL,
                    `delivery_country_code` VARCHAR(70) NOT NULL,
                    `delivery_zip_code` VARCHAR(25) NOT NULL,
				 	date_add DATETIME NOT NULL,
					date_upd DATETIME NOT NULL,
					UNIQUE(`'.self::$definition['primary'].'`),
				  	PRIMARY KEY  ('.self::$definition['primary'].')
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';


        foreach ($sql as $q) {
            if (!Db::getInstance()->execute($q)) {
                return false;
            }
        }

        return true;
    }

    public static function uninstall()
    {
        /* LET'S KEEP THE ORDER IN CASE THE MODULE IS UNINSTALLED
		// Create Category Table in Database
		$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::$definition['table'].'`';
		

		foreach ($sql as $q) 
		{
			if (!Db::getInstance()->Execute($q))
				return false;
		}
        */

        return true;
    }

    /**
     * Get lnp_order by its order id
     *
     * @param integer $id_order Order id
     * @return Lnp2Order details
     */
    public static function getLnpOrderByOrderId($id_order)
    {
        $sql    = 'SELECT *
				FROM `'._DB_PREFIX_.'lnp2_orders`
				WHERE `id_order` = '.(int)$id_order;
        $result = Db::getInstance()->getRow($sql);

        if (!$result || !isset($result['id_lnp2_order'])) {
            return null;
        }

        $lnp2_order     = new Lnp2Order();
        $lnp2_order->id = $result[self::$definition['primary']];
        foreach ($result as $key => $value) {
            if (property_exists($lnp2_order, $key)) {
                $lnp2_order->{$key} = $value;
            }
        }

        return $lnp2_order;
    }
}
