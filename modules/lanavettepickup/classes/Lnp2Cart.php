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

class Lnp2Cart extends ObjectModel
{
    public $id_cart;
    public $pudo_address_line;
    public $pudo_city_name;
    public $pudo_country_code;
    public $pudo_country_name;
    public $pudo_name;
    public $pudo_id;
    public $pudo_zip_code;
    public $shipping_cost;
    public $insurance;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table'     => 'lnp2_cart',
        'primary'   => 'id_lnp2_cart',
        'multilang' => false,
        'fields'    => array(
            'id_cart'           => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'pudo_address_line' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'pudo_city_name'    => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'pudo_country_code' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'pudo_country_name' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'pudo_name'         => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'pudo_id'           => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'pudo_zip_code'     => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'shipping_cost'     => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'insurance'         => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add'          => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd'          => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    public function __toString()
    {
        $toPrint = array(
            'id_lnp2_cart'      => $this->id,
            'id_cart'           => $this->id_cart,
            'pudo_address_line' => $this->pudo_address_line,
            'pudo_city_name'    => $this->pudo_city_name,
            'pudo_country_code' => $this->pudo_country_code,
            'pudo_country_name' => $this->pudo_country_name,
            'pudo_name'         => $this->pudo_name,
            'pudo_id'           => $this->pudo_id,
            'pudo_zip_code'     => $this->pudo_zip_code,
            'shipping_cost'     => $this->shipping_cost,
            'insurance'         => $this->insurance,
            'date_add'          => $this->date_add,
            'date_upd'          => $this->date_upd,
        );

        return Tools::jsonEncode($toPrint);
    }

    public static function install()
    {
        // Create Table in Database
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::$definition['table'].'` (
				  	`'.self::$definition['primary'].'` int(16) NOT NULL AUTO_INCREMENT,
                    `id_cart` int(16) NOT NULL,
				 	`pudo_address_line` VARCHAR(255) NOT NULL,
				 	`pudo_city_name` VARCHAR(255) NOT NULL,
				 	`pudo_country_code` VARCHAR(8) NOT NULL,
                    `pudo_country_name` VARCHAR(32) NOT NULL,
                    `pudo_name` VARCHAR(255) NOT NULL,
                    `pudo_id` VARCHAR(16) NOT NULL,
                    `pudo_zip_code` VARCHAR(8) NOT NULL,
                    `shipping_cost` VARCHAR(8) NOT NULL,
                    `insurance` TINYINT(1) NOT NULL,
				 	`date_add` DATETIME NOT NULL,
					`date_upd` DATETIME NOT NULL,
					UNIQUE(`'.self::$definition['primary'].'`),
				  	PRIMARY KEY  ('.self::$definition['primary'].')
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    public static function uninstall()
    {
        /* LET'S KEEP THE CARTs IN CASE THE MODULE IS UNINSTALLED */

        return true;
    }

    /**
     * Get lnp2_cart by its cart id
     *
     * @param integer $id_cart Cart id
     * @return Lnp2Cart details
     */
    public static function getLnpCartByCartId($id_cart)
    {
        $sql = 'SELECT *
				FROM `'._DB_PREFIX_.self::$definition['table'].'`
				WHERE `id_cart` = '.(int)$id_cart.'
				ORDER BY `date_upd` DESC';

        $result = Db::getInstance()->getRow($sql);

        if (!$result || !isset($result['id_lnp2_cart'])) {
            return null;
        }

        $lnp2_cart     = new Lnp2Cart();
        $lnp2_cart->id = $result[self::$definition['primary']];
        foreach ($result as $key => $value) {
            if (property_exists($lnp2_cart, $key)) {
                $lnp2_cart->{$key} = $value;
            }
        }

        return $lnp2_cart;
    }

    /**
     * Purge lnp2_cart by its cart id, called once order is created and updated
     *
     * @param integer $id_cart Cart id
     * @return array Lnp2Cart details
     */
    public static function purgeLnpCartByCartId($id_cart)
    {
        /*
                $sql = 'SELECT *
                        FROM `'._DB_PREFIX_.self::$definition['table'].'`
                        WHERE `id_cart` = '.(int)$id_cart;
                $result = Db::getInstance()->getRow($sql);
        
                return isset($result['id_lnp2_cart']) ? $result : false;
        */
    }
}
