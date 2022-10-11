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

class AmazonOrderInfo
{
    public $is_extended_feature_available = false;
    public $is_standard_feature_available = false;

    public static $standard_columns = array('id_order', 'mp_order_id', 'mp_status', 'channel', 'channel_status');

    public $id_order = null;
    public $mp_order_id = null;
    public $mp_status = null;
    public $channel = null;
    public $channel_status = null;
    public $marketplace_id = null;
    public $buyer_name = null;
    public $sales_channel = null;
    public $order_channel = null;
    public $ship_service_level = null;
    public $ship_category = null;
    public $is_prime = null;
    public $is_premium = null;
    public $is_business = null;
    public $earliest_ship_date = null;
    public $latest_ship_date = null;
    public $earliest_delivery_date = null;
    public $latest_delivery_date = null;

    public function __construct($id = null, $id_lang = null)
    {
        if ((int)$id) {
            $this->id_order = (int)$id;
        }

        if (AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS) && AmazonTools::fieldExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS, 'sales_channel') && AmazonTools::fieldExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS, 'latest_delivery_date')) {
            $this->is_standard_feature_available = true;
            $this->is_extended_feature_available = true;
        } elseif (AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS)) {
            $this->is_standard_feature_available = true;
        }
    }

    public static function getColumns()
    {
        static $fields = null;
        
        if ($fields === null) {
            $fields = self::$standard_columns;

            $sql = 'SHOW COLUMNS FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS.'`';

            // Extra columns
            $query = Db::getInstance()->executeS($sql);
            if (is_array($query) && count($query)) {
                foreach ($query as $row) {
                    $fields[] = $row['Field'];
                }
            }
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    sprintf('sql: %s'.Amazon::LF, $sql),
                    sprintf('result: %s'.Amazon::LF, print_r($fields, true))
                ));
            }
        }
        
        return($fields);
    }

    public function saveOrderInfo()
    {
        $columns = self::getColumns();
        $to_insert = array();
        
        $available_fields = get_object_vars($this);

        foreach ($available_fields as $field => $value) {
            if (in_array($field, $columns) && !empty($value)) {
                $to_insert[$field] = $value;
            }
        }
        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                "To Insert:\n",
                nl2br(print_r($to_insert, true))
            ));
        }

        if (is_array($to_insert) && count($to_insert)) {
            if ($this->getOrderInfo()) {
                $sql_insert = null;
                foreach ($to_insert as $field => $value) {
                    $final_value = is_numeric($value) || is_bool($value) ? (int)$value : sprintf('"%s"', pSQL($value));
                    $sql_insert .= sprintf('`%s`=%s, ', $field, $final_value);
                }

                $sql = 'UPDATE IGNORE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS.'` '.sprintf('SET %s', rtrim($sql_insert, ', '));
                $sql .= sprintf(' WHERE `id_order`=%d', (int)$this->id_order);
                $result = Db::getInstance()->execute($sql);
            } else {
                $fields = null;
                $values = null;

                foreach ($to_insert as $field => $value) {
                    $fields .= '`'.pSQL($field).'`, ';
                    $values .= is_numeric($value) || is_bool($value) ? sprintf('%d, ', (int)$value) : sprintf('"%s", ', pSQL($value));
                }
                $fields = rtrim($fields, ', ');
                $values = rtrim($values, ', ');

                $sql = 'INSERT INTO `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS.'` ('.$fields.') VALUES ('.$values.')';
                $result = Db::getInstance()->execute($sql);
            }

            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    sprintf('sql: %s'.Amazon::LF, $sql),
                    sprintf('result: %s'.Amazon::LF, print_r($result, true))
                ));
            }

            if (!$result) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function getOrderInfo()
    {
        $colums = self::getColumns();
        $pass = false;
        
        $sql = 'SELECT * FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS.'` WHERE `id_order` = '.(int)$this->id_order.' LIMIT 1 ;';

        if ($result = Db::getInstance()->executeS($sql)) {
            $result = array_shift($result);

            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    sprintf('sql: %s'.Amazon::LF, $sql),
                    sprintf('result: %s'.Amazon::LF, print_r($result, true))
                ));
            }
            foreach ($result as $field => $value) {
                if (in_array($field, $colums) && property_exists($this, $field)) {
                    $this->{$field} = $value;
                    if (!$pass) {
                        $pass = true;
                    }
                }
            }
        }
        return($pass);
    }
}
