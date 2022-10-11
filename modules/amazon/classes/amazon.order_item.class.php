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

class AmazonOrderItem
{
    public static $errors = array();
    public static $table = Amazon::TABLE_MARKETPLACE_ORDER_ITEMS;

    public $mp_order_id = null;
    public $order_item_id = null;
    public $id_order = null;
    public $id_product = null;
    public $id_product_attribute = null;
    public $quantity = null;
    public $sku = null;
    public $asin = null;
    public $carrier_code = null;
    public $carrier_name = null;
    public $shipping_method = null;
    public $tracking_number = null;
    public $item_status = null;
    public $reason = null;//For cancelation
    public $customization = null;

    protected static $required_values = array('mp_order_id', 'order_item_id', 'id_order', 'id_product', 'sku');

    
    public function __construct($mp_order_id = null, $order_item_id = null)
    {
        if (Tools::strlen($mp_order_id) && Tools::strlen($order_item_id)) {
            $this->setOrderItem($mp_order_id, $order_item_id);
        }
    }

    public function saveOrderItem()
    {
        $values = self::$required_values;
        self::$errors = array();
        $table = _DB_PREFIX_ . Amazon::TABLE_MARKETPLACE_ORDER_ITEMS;

        if (!AmazonTools::tableExists($table)) {
            self::$errors[] = "Missing table: $table";
            return(false);
        }

        if (! $this->_compatibility($table)) {
            return false;
        }

        foreach ($values as $value) {
            if ($this->{$value} == null) {
                self::$errors[] = sprintf('%s: "%s"', 'Missing value', $value);
            }
        }
        if (is_array(self::$errors) && count(self::$errors)) {
            return(false);
        }

        $sql = 'REPLACE INTO `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ITEMS.'` (`mp_order_id`, `order_item_id`, `id_order`, `id_product`, `id_product_attribute`, `quantity`, `sku`, `asin`, `carrier_code`, `carrier_name`, `shipping_method`, `tracking_number`, `item_status`, `reason`, `customization`) 
            VALUES (
                "'.pSQL($this->mp_order_id).'",
                "'.pSQL($this->order_item_id).'",
                '.(int)$this->id_order.',
                '.(int)$this->id_product.',
                '.($this->id_product_attribute == null ? 'NULL' : (int)$this->id_product_attribute).',
                '.($this->quantity == null ? 'NULL' : (int)$this->quantity).',
                "'.pSQL($this->sku).'",
                "'.pSQL($this->asin).'",
                "'.pSQL($this->carrier_code).'",
                "'.pSQL($this->carrier_name).'",
                "'.pSQL($this->shipping_method).'",
                "'.pSQL($this->tracking_number).'",
                '.($this->item_status == null ? 'NULL' : (int)$this->item_status).',
                "'.pSQL($this->reason).'",
                "'.pSQL($this->customization).'"
            )';

        $result = Db::getInstance()->execute($sql);

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                'SQL: '.print_r($sql, true).Amazon::LF,
                'Result: '.print_r($result, true).Amazon::LF
            ));
        }


        return($result);
    }

    private function setOrderItem($mp_order_id, $order_item_id)
    {
        self::$errors = array();

        if (!AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ITEMS)) {
            self::$errors[] = 'Missing table: '._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ITEMS;
            return(false);
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ITEMS.'` WHERE `mp_order_id`="'.pSQL($mp_order_id).'" AND `order_item_id`="'.pSQL($order_item_id).'"';

        $result = Db::getInstance()->getRow($sql);

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                'SQL: '.print_r($sql, true).Amazon::LF,
                'Result: '.print_r($result, true).Amazon::LF
            ));
        }

        if (!is_array($result) || !count($result)) {
            return(false);
        }

        $this->mp_order_id = $result['mp_order_id'];
        $this->order_item_id = $result['order_item_id'];
        $this->id_order = (int)$result['id_order'];
        $this->id_product = (int)$result['id_product'];
        $this->id_product_attribute = (int)$result['id_product_attribute'];
        $this->quantity = (int)$result['quantity'];
        $this->sku = $result['sku'];
        $this->asin = $result['asin'];
        $this->carrier_code = $result['carrier_code'];
        $this->carrier_name = $result['carrier_name'];
        $this->shipping_method = $result['shipping_method'];
        $this->tracking_number = $result['tracking_number'];
        $this->item_status = $result['item_status'];
        $this->reason = $result['reason'];

        return(true);
    }
    
    public static function getOrderItems($mp_order_id)
    {
        self::$errors = array();
        $ordered_items_id = array();

        if (!AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ITEMS)) {
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    'Missing Table: '._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ITEMS.Amazon::LF
                ));
            }
            return(false);
        }

        $sql = 'SELECT `order_item_id` FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ITEMS.'` WHERE `mp_order_id`="'.pSQL($mp_order_id).'"';

        $result = Db::getInstance()->ExecuteS($sql);

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                'SQL: '.print_r($sql, true).Amazon::LF,
                'Result: '.print_r($result, true).Amazon::LF
            ));
        }

        if (!is_array($result) || !count($result)) {
            return(false);
        } else {
            foreach ($result as $order_item) {
                $ordered_items_id[] = $order_item['order_item_id'];
            }
        }

        return($ordered_items_id);
    }

    public static function createTable()
    {
        $pass = true;

        $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDER_ITEMS.'` (
                        `mp_order_id` VARCHAR(32) NULL DEFAULT NULL,
                        `order_item_id` VARCHAR(32) NULL DEFAULT NULL,
                        `id_order` INT NOT NULL,
                        `id_product` INT NULL DEFAULT NULL,
                        `id_product_attribute` INT NULL DEFAULT NULL ,
                        `quantity` INT NULL DEFAULT NULL,
                        `sku` VARCHAR(32) NULL DEFAULT NULL,
                        `asin` VARCHAR(16) NULL DEFAULT NULL,
                        `carrier_code` VARCHAR(16) NULL DEFAULT NULL,
                        `carrier_name` VARCHAR(32) NULL DEFAULT NULL,
                        `shipping_method` VARCHAR(16) NULL DEFAULT NULL,
                        `tracking_number` VARCHAR(24) NULL DEFAULT NULL,
                        `item_status` TINYINT NULL DEFAULT NULL,
                        `reason` VARCHAR(40) NULL DEFAULT NULL,
                        `customization` TEXT NULL DEFAULT NULL,
                        UNIQUE KEY `order_items_idx` (`mp_order_id`, `order_item_id`),
                        KEY `id_order_idx` (`id_order`),
                        KEY `mp_order_id_idx` (`mp_order_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

        if (!Db::getInstance()->Execute($sql)) {
            $error = 'SQL: '.$sql.Amazon::LF.'ERROR: '. Db::getInstance()->getMsgError();
            self::$errors[] = $error;
            $pass = false;
        }
        return($pass);
    }

    /**
     * @param $id_order
     * @param $id_product
     * @param null $id_product_attribute
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public static function getItemByOrderId($id_order, $id_product, $id_product_attribute = null)
    {
        $table = _DB_PREFIX_ . Amazon::TABLE_MARKETPLACE_ORDER_ITEMS;
        $sql = "SELECT * FROM `".pSQL($table)."` WHERE `id_order` = ".(int)$id_order." AND `id_product` = ".(int)$id_product;
        if ($id_product_attribute) {
            $sql .= " AND `id_product_attribute` = ".(int)$id_product_attribute;
        }

        $result = Db::getInstance()->getRow($sql);


        if (isset($result['customization']) && Tools::strlen($result['customization'])) {
            $customization = unserialize($result['customization']);
            if (isset($customization['customizationInfo'])) {
                $result['customization'] = self::_parseCustomization($customization['customizationInfo']);
            } else {
                $result['customization'] = null;
            }
        }
        return $result;
    }

    /**
     * @param $mp_order_ids
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getAllByMpOrderIds($mp_order_ids)
    {
        $ids = AmazonTools::buildQueryConditionIn($mp_order_ids, false);

        $sql = 'SELECT * FROM `'._DB_PREFIX_.self::$table.'` 
                WHERE `mp_order_id` IN ('.$ids.')';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param $mp_order_ids
     * @return bool
     */
    public static function deleteAllByMpOrderIds($mp_order_ids)
    {
        $ids = AmazonTools::buildQueryConditionIn($mp_order_ids, false);

        $sql = 'DELETE FROM `'._DB_PREFIX_.self::$table.'` 
                WHERE `mp_order_id` IN ('.$ids.')';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Parse product customization recursively
     * @param $input
     *
     * @return null|array
     */
    public static function _parseCustomization($input)
    {
        if (is_array($input)) {
            foreach ($input as $item) {
                // {label}: {optionValue} or {label}: {text}
                if (isset($item['label']) && (isset($item['optionValue']) || isset($item['text']))) {
                    return $input;
                } else {
                    $recursive = self::_parseCustomization($item);
                    if ($recursive) {
                        return $recursive;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Compatibility with PS 1.5 and earlier
     * @param $table
     *
     * @return bool
     */
    private function _compatibility($table)
    {
        if (! AmazonTools::amazonFieldExists($table, 'customization')) {
            $sql = "ALTER TABLE `" . pSQL($table) . "` ADD COLUMN `customization` TEXT NULL DEFAULT NULL AFTER `reason`";
            if (! Db::getInstance()->execute($sql)) {
                self::$errors[] = "Cannot insert column customization";
                return false;
            }
        }

        return true;
    }
}
