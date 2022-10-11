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

class AmazonRemoteCart
{
    const TABLE = 'amazon_remote_cart';

    /**
     * @return bool
     */
    public static function tableCreate()
    {
        $pass = true;

        if (!AmazonTools::tableExists(_DB_PREFIX_.self::TABLE)) {
            $sql = '
                    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE.'` (
					  `mp_order_id` varchar(40) NOT NULL,
					  `reference` varchar(40) NOT NULL,
					  `quantity` INT(16) NOT NULL,
					  `timestamp` timestamp,
					  `date_add` timestamp,
						UNIQUE KEY `unique` (`mp_order_id`,`reference`),
						KEY `reference` (`reference`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

            if (!($result = Db::getInstance()->execute($sql))) {
                if (Amazon::$debug_mode) {
                    AmazonTools::pre(array(
                        "Unable to create table remote cart\n",
                        sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql),
                        $sql,
                        $result
                    ));
                }
                $pass = false;
            }
        }
        return($pass);
    }

    /**
     * @return mixed|null
     */
    public static function tableExists()
    {
        return AmazonTools::tableExists(_DB_PREFIX_.self::TABLE);
    }

    /**
     * @param $order_id
     * @param $SKU
     * @param $quantity
     * @param $timestamp
     *
     * @return bool
     */
    public static function addCart($order_id, $SKU, $quantity, $timestamp)
    {
        $sql = 'REPLACE INTO `'._DB_PREFIX_.self::TABLE.'`
                          (`mp_order_id`, `reference`, `quantity`, `timestamp`, `date_add`) VALUES("'.pSQL($order_id).'", "'.pSQL($SKU).'", '.(int)$quantity.', "'.pSQL(date('Y-m-d H:i:s', $timestamp)).'", "'.pSQL(date('Y-m-d H:i:s')).'") ;';

        if (!$rq = Db::getInstance()->execute($sql)) {
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    "Unable to insert values in amazon cart\n",
                    sprintf("%s(%d): SQL Failed - '%s'\n", basename(__FILE__), __LINE__, $sql),
                    $rq
                ));
            }
            return(false);
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($rq);
        }
        return(true);
    }

    /**
     * @param $order_id
     * @param $SKU
     * @param $quantity
     * @param $timestamp
     *
     * @return bool
     */
    public static function updateCart($order_id, $SKU, $quantity, $timestamp)
    {
        $sql = 'UPDATE `'._DB_PREFIX_.self::TABLE.'` set `timestamp` = NOW() WHERE `mp_order_id`="'.pSQL($order_id).'" AND `reference`="'.pSQL($SKU).'"';

        if (!$rq = Db::getInstance()->execute($sql)) {
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    "Unable to update values in amazon cart\n",
                    sprintf("%s(%d): SQL Failed - '%s'\n", basename(__FILE__), __LINE__, $sql),
                    $rq
                ));
            }
            return(false);
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($rq);
        }
        return(true);
    }

    /**
     * @param $order_id
     * @param $SKU
     *
     * @return bool
     */
    public static function inCart($order_id, $SKU)
    {
        $sql = 'SELECT `reference` FROM `'._DB_PREFIX_.self::TABLE.'` WHERE `reference`="'.pSQL($SKU).'" AND mp_order_id="'.pSQL($order_id).'"';

        if (!$rq = Db::getInstance()->getRow($sql)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf("%s(%d): SQL returned nothing - '%s'\n", basename(__FILE__), __LINE__, $sql));
                CommonTools::p($rq);
            }
            return(false);
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($rq);
        }
        return(true);
    }

    /**
     * @param $order_id
     * @param $SKU
     *
     * @return bool
     */
    public static function removeFromCart($order_id, $SKU)
    {
        $sql = 'DELETE FROM `'._DB_PREFIX_.self::TABLE.'` WHERE `mp_order_id` = "'.pSQL($order_id).'" AND `reference` = "'.pSQL($SKU).'"';

        if (!$rq = Db::getInstance()->execute($sql)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf("%s(%d): SQL Failed - '%s'\n", basename(__FILE__), __LINE__, $sql));
                CommonTools::p($rq);
            }
            return(false);
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($rq);
        }
        return(true);
    }


    /**
     * @return array|bool|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function expiredCarts()
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.self::TABLE.'` WHERE `timestamp` < "'.pSQL(date('Y-m-d H:i:s', strtotime('now -4 hours'))).'" AND `date_add` < "'.pSQL(date('Y-m-d H:i:s', strtotime('now -4 hours'))).'"';

        if (!$result = Db::getInstance()->executeS($sql)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf("%s(%d): SQL Failed - '%s'\n", basename(__FILE__), __LINE__, $sql));
                CommonTools::p($result);
            }
            return(false);
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }
        return($result);
    }

    /**
     * @param $SKU
     *
     * @return bool|int
     */
    public static function getQuantities($SKU)
    {
        // using a date range to prevent expired carts
        $sql = 'SELECT sum(`quantity`) as quantity FROM `'._DB_PREFIX_.self::TABLE.'` WHERE 
            `reference` = "'.pSQL($SKU).'" AND
            `timestamp` > "'.pSQL(date('Y-m-d H:i:s', strtotime('now -1 day'))).'" AND `date_add` > "'.pSQL(date('Y-m-d H:i:s', strtotime('now -1 day'))).'"';

        if (!$result = Db::getInstance()->getValue($sql)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf("%s(%d): SQL Failed - '%s'\n", basename(__FILE__), __LINE__, $sql));
                CommonTools::p($result);
            }
            return(false);
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }
        return(is_numeric($result) ? (int)$result : 0);
    }
}
