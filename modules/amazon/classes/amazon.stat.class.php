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

class AmazonStat extends ObjectModel
{
    public static $stat_table = Amazon::TABLE_MARKETPLACE_STATS;
    public static $vat_table  = Amazon::TABLE_MARKETPLACE_VAT;

    // report field => table field
    public static $order_mapping = array(
        'order_id'              => 'mp_order_id',
        'purchase_date'         => 'purchase_date',
        'payments_date'         => 'payments_date',
        'buyer_email'           => 'buyer_email',
        'buyer_name'            => 'buyer_name',
        'buyer_phone_number'    => 'buyer_phone_number',
        'currency'              => 'currency',
        'id_currency'           => 'id_currency',
        'delivery_start_date'   => 'delivery_start_date',
        'delivery_end_date'     => 'delivery_end_date',
        'delivery_time_zone'    => 'delivery_time_zone',
        'delivery_Instructions' => 'delivery_instructions',
        'is_business_order'     => 'is_business_order',
        'purchase_order_number' => 'purchase_order_number',
        'price_designation'     => 'price_designation',
        'shipping_price'        => 'shipping_price',
        'sales_channel'         => 'marketplace',
        'total_price'           => 'total_price'                // Can calculate total price from order
    );

    public static $settle_mapping = array(
        'marketplace_name' => 'marketplace',
        'order_id'    => 'mp_order_id',
        'commissions' => 'commissions'
    );

    public static $tax_mapping = array(
        'marketplace'                       => 'marketplace',
        'order_id'                          => 'mp_order_id',
        'order_date'                        => 'order_date',
        'tax_calculated_date (utc)'         => 'tax_date',
        'tax_collection_model'              => 'tax_model',
        'tax_collection_responsible_party'  => 'tax_responsible_party',
        'currency'                          => 'currency',
        'display_price'                     => 'display_price',
        'display_price_tax_inclusive'       => 'display_price_tax_inclusive',
        'taxexclusive_selling_price'        => 'selling_price_tax_exclusive',
        'total_tax'                         => 'total_tax',
        'display_promo_amount'              => 'display_promo_amount',
        'display_promo_tax_inclusive'       => 'display_promo_tax_inclusive',
    );

    public static function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_STATS.'` (
                    `marketplace` VARCHAR(32) NOT NULL,
                    `mp_order_id` VARCHAR(32) NOT NULL,
                    `purchase_date` DATETIME DEFAULT NULL,
                    `payments_date` DATETIME DEFAULT NULL,
                    `buyer_email` VARCHAR(64) DEFAULT NULL,
                    `buyer_name` VARCHAR(128) DEFAULT NULL,
                    `buyer_phone_number` VARCHAR(32) DEFAULT NULL,
                    `currency` VARCHAR(5) DEFAULT NULL,
                    `id_currency` INT(10) UNSIGNED DEFAULT NULL,
                    `delivery_start_date` DATETIME DEFAULT NULL,
                    `delivery_end_date` DATETIME DEFAULT NULL,
                    `delivery_time_zone` VARCHAR(32) DEFAULT NULL,
                    `delivery_instructions` TEXT DEFAULT NULL,
                    `is_business_order` TINYINT(1) DEFAULT 0,
                    `purchase_order_number` VARCHAR(32) DEFAULT NULL,
                    `price_designation` DECIMAL(10, 2) DEFAULT NULL,
                    `shipping_price` DECIMAL(10, 2) DEFAULT NULL,
                    `shipping_tax` DECIMAL(10, 2) DEFAULT NULL,
                    `commissions` DECIMAL(10, 2) DEFAULT NULL,
                    `total_price` DECIMAL(10, 2) DEFAULT NULL,
                    `total_tax` DECIMAL(10, 2) DEFAULT NULL,
                    `date_add` DATETIME NOT NULL,
                    `date_upd` DATETIME NOT NULL,
                    UNIQUE KEY `order` (`marketplace`, `mp_order_id`)
			    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Add tax columns for stat table
     * @return bool
     */
    public static function addStatTaxColumns()
    {
        $pass = true;
        $table = _DB_PREFIX_.Amazon::TABLE_MARKETPLACE_STATS;
        $update_fields = array(
            'total_tax'     => '`total_tax` DECIMAL(10, 2) DEFAULT NULL AFTER `total_price`',
            'shipping_tax'  => '`shipping_tax` DECIMAL(10, 2) DEFAULT NULL AFTER `shipping_price`'
        );

        foreach ($update_fields as $field => $sql) {
            if (!AmazonTools::fieldExists($table, $field)) {
                $alter = 'ALTER TABLE `'.$table.'` ADD COLUMN '.$sql;
                $pass = $pass && Db::getInstance()->execute($alter);
            }
        }

        return $pass;
    }

    public static function createVatTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_VAT.'` (
                    `marketplace` VARCHAR(32) NOT NULL,
                    `mp_order_id` VARCHAR(32) NOT NULL,
                    `order_date` DATETIME DEFAULT NULL,
                    `tax_date` DATETIME DEFAULT NULL,
                    `tax_model` VARCHAR(32) DEFAULT NULL,
                    `tax_responsible_party` VARCHAR(32) DEFAULT NULL,
                    `currency` VARCHAR(5) DEFAULT NULL,
                    `id_currency` INT(10) UNSIGNED DEFAULT NULL,
                    `display_price` DECIMAL(10, 2) DEFAULT NULL,
                    `display_price_tax_inclusive` TINYINT(1) DEFAULT NULL,
                    `selling_price_tax_exclusive` DECIMAL(10, 2) DEFAULT NULL,
                    `total_tax` DECIMAL(10, 2) DEFAULT NULL,
                    `display_promo_amount` DECIMAL(10, 2) DEFAULT NULL,
                    `display_promo_tax_inclusive` DECIMAL(10, 2) DEFAULT NULL,
                    `date_add` datetime NOT NULL,
                    `date_upd` datetime NOT NULL,
                    UNIQUE KEY `order` (`marketplace`, `mp_order_id`)
			    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        return Db::getInstance()->execute($sql);
    }


    /**
     * Get Amazon orders
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAll()
    {
        return Db::getInstance()->executeS("SELECT * FROM `"._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_STATS."` 
                                                WHERE `marketplace` = 'Amazon'");
    }

    /**
     * Get sales data
     * @param $from
     * @param $to
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public static function getSales($from, $to)
    {
        $find = "SUM(`total_price`)";
        return self::groupByDay($find, $from, $to);
    }

    /**
     * Get order number data
     * @param $from
     * @param $to
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public static function getOrdersNum($from, $to)
    {
        $find = "COUNT(`mp_order_id`)";
        return self::groupByDay($find, $from, $to);
    }

    /**
     * Group data by day
     * @param $find
     * @param $from
     * @param $to
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function groupByDay($find, $from, $to)
    {
        $currency = Context::getContext()->currency->id;

        $sql = "SELECT $find AS `data`, UNIX_TIMESTAMP(DATE_FORMAT(`payments_date`, '%y-%m-%d')) AS `date` 
                FROM `"._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_STATS."` 
                WHERE `marketplace` LIKE 'Amazon%' AND `id_currency` = ".(int)$currency." 
                AND `payments_date` >= '".pSQL($from)."' AND `payments_date` <= '".pSQL($to)."' 
                GROUP BY `date`";

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Update / Insert order info
     * @param $data
     * @param $type
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public static function updateOrder($data, $type)
    {
        if (!isset($data['mp_order_id']) || !Tools::strlen($data['mp_order_id'])) {
            return false;
        }

        // Get target table
        switch ($type) {
            case AmazonOrdersReports::TYPE_ORDER_REPORT:
            case AmazonOrdersReports::TYPE_SETTLEMENT_REPORT:
                $table = Amazon::TABLE_MARKETPLACE_STATS;
                break;
            case AmazonOrdersReports::TYPE_VAT_REPORT:
                $table = Amazon::TABLE_MARKETPLACE_VAT;
                break;
            default:
                return false;
        }

        $now = date('Y-m-d H:i:s');
        if (!isset($data['marketplace']) || !Tools::strlen($data['marketplace'])) {
            $data['marketplace'] = 'Amazon';
        }
        $data['date_upd'] = $now;

        // Sanity data
        $sanity_data = array();
        foreach ($data as $key => $value) {
            if (true === $value || 'true' === strtolower($value)) {
                $sanity_data[pSQL($key)] = 1;
            } elseif (false === $value || 'false' === strtolower($value)) {
                $sanity_data[pSQL($key)] = 0;
            } elseif (Tools::strlen($value)) {
                $sanity_data[pSQL($key)] = pSQL($value);
            }
        }

        $mp_order_id = $data['mp_order_id'];
        if (self::_orderIsSaved($mp_order_id, $table)) {
            // Update
            return Db::getInstance()->update($table, $sanity_data, "`mp_order_id` = '".pSQL($mp_order_id)."'");
        } else {
            // Insert
            $sanity_data['date_add'] = $now;
            return Db::getInstance()->insert($table, $sanity_data);
        }
    }

    /**
     * Get stat row by buyer email
     * @param $email
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllStatMpOrderIdsByBuyerEmail($email)
    {
        $sql = 'SELECT `mp_order_id` 
                FROM `'._DB_PREFIX_.self::$stat_table.'` 
                WHERE `buyer_email` = "'.pSQL(trim($email)).'"';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Get stat row by buyer name
     * @param $name
     * @return array|bool|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllStatMpOrderIdsByBuyerName($name)
    {
        $names = AmazonTools::buildQueryConditionIn($name, true);

        $sql = 'SELECT `mp_order_id` 
                FROM `'._DB_PREFIX_.self::$stat_table.'` 
                WHERE LOWER(`buyer_name`) IN ('.$names.')';

        return Db::getInstance()->executeS($sql);
    }

    public static function getAllStatsByMpOrderIds($mp_order_ids)
    {
        return self::_getAllByMpOrderIds($mp_order_ids, self::$stat_table);
    }

    public static function getAllVatsByMpOrderIds($mp_order_ids)
    {
        return self::_getAllByMpOrderIds($mp_order_ids, self::$vat_table);
    }

    public static function deleteAllStatByMpOrderIds($mp_order_ids)
    {
        return self::_deleteAllByMpOrderIds($mp_order_ids, self::$stat_table);
    }

    public static function deleteAllVatByMpOrderIds($mp_order_ids)
    {
        return self::_deleteAllByMpOrderIds($mp_order_ids, self::$vat_table);
    }

    /**
     * @param array $mp_order_ids
     * @param $table
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    private static function _getAllByMpOrderIds($mp_order_ids, $table)
    {
        $ids = AmazonTools::buildQueryConditionIn($mp_order_ids, false);
        $sql = 'SELECT * FROM `'._DB_PREFIX_.$table.'` WHERE `mp_order_id` IN ('.$ids.')';
        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param $mp_order_ids
     * @param $table
     * @return bool
     */
    private static function _deleteAllByMpOrderIds($mp_order_ids, $table)
    {
        $ids = AmazonTools::buildQueryConditionIn($mp_order_ids, false);
        $sql = 'DELETE FROM `'._DB_PREFIX_.$table.'` WHERE `mp_order_id` IN ('.$ids.')';
        return Db::getInstance()->execute($sql);
    }

    /**
     * Check if order exist
     * @param $mp_order_id
     * @param $table
     *
     * @return bool
     */
    private static function _orderIsSaved($mp_order_id, $table)
    {
        $sql = "SELECT * FROM `"._DB_PREFIX_.$table."` 
                WHERE `mp_order_id` = '".pSQL($mp_order_id)."'";

        $order = Db::getInstance()->getRow($sql);

        return (bool) $order;
    }
}
