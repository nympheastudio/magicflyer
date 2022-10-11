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

class AmazonProduct extends Product
{
    const FILEFORMAT_REFERENCE = 1;
    const FILEFORMAT_ID = 2;
    public $id_product_attribute = null;

    public function __construct($SKU = null, $full = false, $id_lang = null, $reference = 'reference', $id_shop = null)
    {
        $id_product_attribute = null;

        // get combination first
        $sql = 'SELECT p.`id_product`, p.`id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute` p ';
        $sql .= $this->getIdShopAssociation($id_shop);
        $sql .= 'WHERE `'.$reference.'` = "'.pSQL(trim($SKU)).'"';

        $result = Db::getInstance()->getRow($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        if (!$result || !$result['id_product']) {
            $sql = 'SELECT p.`id_product` FROM `'._DB_PREFIX_.'product` p ';
            $sql .= $id_shop ? ' JOIN `'._DB_PREFIX_.'product_shop` ps on (ps.`id_shop` = '.(int)$id_shop.' and ps.`id_product` = p.`id_product`) ' : null;
            $sql .= 'WHERE `'.$reference.'` = "'.pSQL(trim($SKU)).'"';

            $result = Db::getInstance()->getRow($sql);

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
                CommonTools::p($result);
            }

            if (!$result || !$result['id_product']) {
                return false;
            }
        } else {
            $id_product_attribute = (int)$result['id_product_attribute'];
        }

        parent::__construct((int)$result['id_product'], $full, $id_lang, $id_shop);

        if (Validate::isLoadedObject($this)) {
            $this->id_product_attribute = $id_product_attribute;

            return ($this->id);
        }

        return (false);
    }

    public static function getIdShopAssociation($id_shop = null)
    {
        /// Temporary workaround
        if (version_compare(_PS_VERSION_, '1.5', '>=') && !$id_shop) {
            if (Shop::isFeatureActive() && !$id_shop) {
                $context = Context::getContext();
                $id_shop = (int)Validate::isLoadedObject($context->shop) ? $context->shop->id : 1;
            } else {
                $id_shop = null;
            }
        }

        if ($id_shop) {
            return(' JOIN `'._DB_PREFIX_.'product_shop` ps on (ps.`id_shop` = '.(int)$id_shop.' and ps.`id_product` = p.`id_product`) ');
        }

        return(null);
    }

    public static function checkProduct($SKU, $id_shop = null)
    {
        $count = 0;

        /// Temporary workaround
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            if (Shop::isFeatureActive()) {
                $context = Context::getContext();
                $id_shop = (int)Validate::isLoadedObject($context->shop) ? $context->shop->id : 1;
            } else {
                $id_shop = null;
            }
        }

        $sql = 'SELECT count(p.`id_product`) as count FROM `'._DB_PREFIX_.'product_attribute` p ';
        $sql .= $id_shop ? ' JOIN `'._DB_PREFIX_.'product_shop` ps on (ps.`id_shop` = '.(int)$id_shop.' AND ps.`id_product` = p.`id_product`) ' : null;
        $sql .= 'WHERE `reference` = "'.pSQL(trim($SKU)).'"';

        $result = Db::getInstance()->getRow($sql);

        if (isset($result['count']) && (int)$result['count']) {
            $count += (int)$result['count'];
        }

        $sql = 'SELECT count(p.`id_product` ) as count FROM `'._DB_PREFIX_.'product` p ';
        $sql .= $id_shop ? ' JOIN `'._DB_PREFIX_.'product_shop` ps on (ps.`id_shop` = '.(int)$id_shop.' AND ps.`id_product` = p.`id_product`) ' : null;
        $sql .= 'WHERE `reference` = "'.pSQL(trim($SKU)).'"';

        $result = Db::getInstance()->getRow($sql);

        if (isset($result['count'])) {
            $count += (int)$result['count'];
        }

        return ($count);
    }

    public static function getProductById($id_product, $id_product_attribute = null)
    {
        if ($id_product_attribute == null) {
            $sql = 'SELECT `reference` FROM `'._DB_PREFIX_.'product` where id_product = '.(int)$id_product.' ;';

            $rq = Db::getInstance()->executeS($sql);
        } else {
            $sql = 'SELECT `reference` FROM `'._DB_PREFIX_.'product_attribute` where id_product = '.(int)$id_product.' and id_product_attribute = '.(int)$id_product_attribute;

            $rq = Db::getInstance()->executeS($sql);
        }
        if (!isset($rq[0]['reference'])) {
            return (false);
        }

        return ($rq[0]['reference']);
    }

    public static function checkAsin($id_lang, $ASIN)
    {
        $sql = 'SELECT id_product, id_product_attribute FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` p WHERE `asin1` = "'.pSQL(trim($ASIN)).'" AND id_lang = "'.(int)$id_lang.'"';

        $rq = Db::getInstance()->getRow($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($rq);
        }

        if (!$rq) {
            return (null);
        }

        if (!is_array(array_keys($rq)) || !count(array_keys($rq))) {
            return (null);
        }

        return (true);
    }

    public static function getIdByAsin($id_lang, $ASIN)
    {
        $sql = 'SELECT id_product, id_product_attribute FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` p WHERE `asin1` = "'.pSQL(trim($ASIN)).'" AND id_lang = "'.(int)$id_lang.'"';

        $rq = Db::getInstance()->getRow($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($rq);
        }

        if (!$rq) {
            return (null);
        }

        if (!is_array(array_keys($rq)) || !count(array_keys($rq))) {
            return (null);
        }

        $obj = new stdClass;
        $obj->id_product = (int)$rq['id_product'];
        $obj->id_product_attribute = (int)$rq['id_product_attribute'] ? (int)$rq['id_product_attribute'] : null;

        return ($obj);
    }

    public static function getProductName($id_product, $id_product_attribute = null, $id_lang = null)
    {
        if (method_exists('Product', 'getProductName')) {
            return (Product::getProductName($id_product, $id_product_attribute, $id_lang));
        }

        $sql = 'SELECT `name` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product` = '.(int)$id_product.' AND `id_lang`='.(int)$id_lang;

        $ret = Db::getInstance()->getRow($sql);

        if (!isset($ret['name'])) {
            return (false);
        }

        $product_name = $ret['name'];

        if ($id_product_attribute) {
            $sql = 'SELECT al.`name` attribute_name
                FROM `'._DB_PREFIX_.'product_attribute` pa
                LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.`id_product_attribute` = pa.`id_product_attribute`)
                LEFT JOIN `'._DB_PREFIX_.'attribute` a ON (a.`id_attribute` = pac.`id_attribute`)
                LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON (ag.`id_attribute_group` = a.`id_attribute_group`)
                LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$id_lang.')
                LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)$id_lang.')
            WHERE pa.`id_product` =  '.(int)$id_product.' and pa.`id_product_attribute` = '.(int)$id_product_attribute;

            $ret = Db::getInstance()->executeS($sql);

            if (is_array($ret) && count($ret)) {
                $attributes = '';
                foreach ($ret as $attribute) {
                    $attributes .= $attribute['attribute_name'].' - ';
                }
                $attributes = rtrim($attributes, ' - ');

                if (Tools::strlen($attributes)) {
                    $product_name = sprintf('%s (%s)', $product_name, $attributes);
                }
            }

            return ($product_name);
        }

        return ($product_name);
    }

    public static function getProductOptionsV4($id_product, $id_product_attribute = null, $id_lang = null)
    {
        if ($id_lang !== null) {
            $sql_lang = ' AND `id_lang`='.(int)$id_lang;
        } else {
            $sql_lang = '';
        }

        if ($id_product_attribute !== null) {
            $sql_attribute = ' AND `id_product_attribute`='.(int)$id_product_attribute;
        } else {
            $sql_attribute = '';
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` p WHERE `id_product` = '.(int)$id_product.$sql_attribute.$sql_lang;

        $result = Db::getInstance()->executeS($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        return ($result);
    }

    public static function getProductOptions($id_product, $id_lang, $id_product_attribute = null, $return_default = true)
    {
        if ($id_product_attribute !== null) {
            $sql_attribute = ' and id_product_attribute='.(int)$id_product_attribute;
        } else {
            $sql_attribute = '';
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` p where id_product = '.(int)$id_product.' and id_lang = '.(int)$id_lang.$sql_attribute;

        $rq = Db::getInstance()->executeS($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($rq);
        }

        if ($rq) {
            return (array_shift($rq));
        } elseif ($id_product_attribute) {
            return(self::getProductOptions($id_product, $id_lang, null));
        } elseif ($return_default) {
            return (self::getDefaultOptions());
        } else {
            return(false);
        }
    }

    public static function productOptionCheck($id_product, $id_lang, $id_product_attribute = null)
    {
        if ($id_product_attribute !== null) {
            $sql_attribute = ' and id_product_attribute='.(int)$id_product_attribute;
        } else {
            $sql_attribute = '';
        }

        $sql = 'SELECT id_product FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` p where id_product = '.(int)$id_product.' and id_lang = '.(int)$id_lang.$sql_attribute;

        $rq = Db::getInstance()->executeS($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($rq);
        }

        if ($rq) {
            return (array_shift($rq));
        } else {
            return (false);
        }
    }


    public static function getProductOptionFields()
    {
        static $additionnal_fields = array();

        if (is_array($additionnal_fields) && count($additionnal_fields)) {
            return ($additionnal_fields);
        }

        // Extra Fields / New Fields
        $additionnal_fields_config = Configuration::get('AMAZON_PRODUCT_OPTION_FIELDS', null, 0, 0);

        if ($additionnal_fields_config && strpos($additionnal_fields_config, ',')) {
            $additionnal_fields = explode(',', $additionnal_fields_config);

            if (!is_array($additionnal_fields) || !count($additionnal_fields)) {
                $additionnal_fields = array();
            }
        }

        return ($additionnal_fields);
    }


    public static function getDefaultOptions()
    {
        $default_options = array(
            'force' => 0,
            'nopexport' => 0,
            'noqexport' => 0,
            'fba' => 0,
            'fba_value' => 0,
            'latency' => 0,
            'disable' => 0,
            'price' => '',
            'asin1' => '',
            'asin2' => '',
            'asin3' => '',
            'text' => '',
            'shipping' => '',
            'shipping_type' => '',
            'gift_wrap' => '',
            'gift_message' => '',
            'bullet_point1' => '',
            'bullet_point2' => '',
            'bullet_point3' => '',
            'bullet_point4' => '',
            'bullet_point5' => ''
        );

        $option_list = self::getProductOptionFields();

        foreach ($option_list as $option) {
            if (!isset($default_options[$option])) {
                $default_options[$option] = null;
            }
        }

        return ($default_options);
    }

    public static function updateProductOptions($id_product, $id_lang, $field, $value, $id_product_attribute = 0, $create = false)
    {
        if (is_numeric($value) && is_int($value)) {
            if (is_float((float)$value)) {
                $set = '='.(float)$value;
            } else {
                $set = '='.(int)$value;
            }
        } else {
            if (is_null($value)) {
                $set = '=NULL ';
            } else {
                $set = '="'.pSQL($value).'"';
            }
        }

        if (self::productOptionCheck($id_product, $id_lang, $id_product_attribute)) {
            $sql = 'UPDATE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` SET `'.pSQL($field).'` '.$set.' WHERE `id_product`='.(int)$id_product.' AND `id_lang`='.(int)$id_lang.' AND `id_product_attribute`='.($id_product_attribute ? (int)$id_product_attribute : 0);

            $rq = Db::getInstance()->execute($sql);

            return ($rq);
        } else {
            return (self::setProductOptions($id_product, $id_lang, array_merge(self::getDefaultOptions(), array($field => $value)), $id_product_attribute));
        }
    }

    public static function setProductOptions($id_product, $id_lang, $options, $id_product_attributes = null)
    {
        $option_fields = self::getProductOptionFields();
        $fields_sql = null;

        foreach ($option_fields as $field) {
            $fields_sql .= sprintf('`%s`, ', $field);
        }
        $fields_sql = rtrim($fields_sql, ', ');

        //
        // NB: Shipping : We don't cast to float the shipping value: this value could be 0 or > 0 or NULL !
        //
        $sql = 'REPLACE INTO `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` ('.$fields_sql.') values(';

        $insert_statement = null;

        foreach ($option_fields as $field) {
            switch ($field) {
                case 'id_product':
                    $insert_statement .= (int)$id_product.', ';
                    break;
                case 'id_product_attribute':
                    $insert_statement .= (int)$id_product_attributes.', ';
                    break;
                case 'id_lang':
                    $insert_statement .= (int)$id_lang.', ';
                    break;
                case 'browsenode': // int32 issue ! http://support.common-services.com/helpdesk/tickets/26582
                    $insert_statement .= sprintf('"%s", ', pSQL($options[$field]));
                    break;
                default:
                    if (isset($options[$field])) {
                        if (is_bool($options[$field])) {
                            $insert_statement .= ((bool)$options[$field] ? 1 : 0).', ';
                        } elseif (is_float($options[$field])) {
                            $insert_statement .= (float)$options[$field].', ';
                        } elseif (is_int($options[$field]) && is_int($options[$field])) {
                            $insert_statement .= (int)$options[$field].', ';
                        } elseif (is_numeric($options[$field]) && is_int($options[$field])) {
                            $insert_statement .= (int)$options[$field].', ';
                        } elseif (empty($options[$field])) {
                            $insert_statement .= 'null, ';
                        } else {
                            $insert_statement .= '"'.pSQL($options[$field]).'", ';
                        }
                    } else {
                        $insert_statement .= 'null, ';
                    }
            }
        }

        $sql .= rtrim($insert_statement, ' ,').');';

        $rq = Db::getInstance()->execute($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($rq);
        }

        return ($rq);
    }

    public static function setProductASIN($id_product, $id_lang, $asins)
    {
        $pass = true;

        if (!is_array($asins)) {
            $asins = array(0 => $asins);
        }

        $asin_count = is_array($asins) ? count($asins) : 0;

        for ($i = 0; $i < $asin_count && $i < 3; $i++) {
            if (!isset($asins[$i]) || !empty($asins[$i])) {
                continue;
            }

            $sql = 'SELECT `id_product` FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` WHERE `id_product` ='.(int)$id_product.' AND `id_lang`= '.(int)$id_lang;

            if (Db::getInstance()->getRow($sql)) {
                $sql = 'UPDATE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` set `asin'.($i + 1).'`="'.pSQL($asins[$i]).'"'.' WHERE `id_product` = '.(int)$id_product.' AND `id_lang`= '.(int)$id_lang;

                if (!$rq = Db::getInstance()->execute($sql)) {
                    $pass = false && $pass;
                }
            } else {
                $sql = 'REPLACE INTO `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'`
                          (`id_product`, `id_lang`, `asin'.($i + 1).'`) values('.pSQL($id_product).', '.pSQL($id_lang).', "'.pSQL($asins[$i]).'") ;';

                if (!$rq = Db::getInstance()->execute($sql)) {
                    $pass = false && $pass;
                }
            }
        }
        $pass = self::marketplaceActionSet(Amazon::UPDATE, $id_product) && $pass;

        if ($pass) {
            return ($rq);
        }

        return (false);
    }

    public static function marketplaceActionSet($action, $id_product, $id_product_attribute = null, $sku = null, $id_lang = null)
    {
        if (!$id_product) {
            return (false);
        }

        // Remove option entry
        if ($action == Amazon::REMOVE) {
            self::marketplaceOptionDelete($id_product);
        }

        // In Marketplace Categories
        //
        if (!self::marketplaceInCategories($id_product)) {
            return (false);
        }

        // Get Actives Platforms
        //
        $actives = AmazonConfiguration::get('ACTIVE');

        if (!is_array($actives)) {
            return (false);
        }

        if ($id_lang) {
            $actives = array($id_lang => $id_lang);
        }

        // On ne traite pas les attribute sur les updates !
        //
        if ($action == Amazon::UPDATE) {
            $id_product_attribute = null;
        }

        $pass = true;

        foreach (array_keys($actives) as $id_lang) {
            $sql = 'REPLACE INTO `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS.'`
                        (`id_product`, `id_product_attribute`, `id_lang`, `sku`, `marketplace`, `action`, `date_add`, `date_upd`) values('.(int)$id_product.', '.(!$id_product_attribute ? 'NULL' : $id_product_attribute).', '.(int)$id_lang.', "'.pSQL($sku).'", "'.pSQL(Amazon::MARKETPLACE).'", "'.pSQL($action).'", "'.pSQL(date('Y-m-d H:i:s')).'", NULL) ';

            $pass &= Db::getInstance()->execute($sql);
        }

        $sql = 'UPDATE `'._DB_PREFIX_.'product` set `date_upd` = "'.pSQL(date('Y-m-d H:i:s')).'" where `id_product`='.(int)$id_product;
        Db::getInstance()->execute($sql);

        return ($pass);
    }

    public static function marketplaceOptionDelete($id_product, $id_product_attribute = null, $id_lang = null)
    {
        $sql = 'DELETE FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` WHERE `id_product` = '.(int)$id_product;

        if ($id_product_attribute) {
            $sql .= ' AND `id_product_attribute`='.(int)$id_product_attribute;
        }

        if ($id_lang) {
            $sql .= ' AND `id_lang`='.(int)$id_lang;
        }

        return (Db::getInstance()->execute($sql));
    }

    public static function marketplaceInCategories($id_product)
    {
        // Categories Lookup
        //
        static $categories = null;

        if ($categories == null) {
            $categories = AmazonConfiguration::get('categories');
        }

        if (!is_array($categories)) {
            return(false);
        }

        $categories = array_filter($categories);

        if (!$categories || !is_array($categories) || !count($categories)) {
            return (false);
        }

        $list = rtrim(implode(',', $categories), ',');

        $sql = 'SELECT `id_product` FROM `'._DB_PREFIX_.'category_product` WHERE `id_product` = '.(int)$id_product.' AND `id_category` IN('.pSQL($list).')';

        if (!$rq = Db::getInstance()->getRow($sql)) {
            return (false);
        }

        return (true);
    }

    public static function populateProductOptions()
    {
        $pass = true;

        $sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` (`id_product`, `id_lang`, `id_product_attribute`)
                        (SELECT DISTINCT `id_product`, `id_lang`, 0 from `'._DB_PREFIX_.'product_lang`)';

        $pass = $pass && Db::getInstance()->execute($sql);

        $sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` (`id_product`, `id_product_attribute`, `id_lang`)
                        (SELECT DISTINCT pa.`id_product`, pa.`id_product_attribute`, pl.`id_lang` FROM `'._DB_PREFIX_.'product_lang` pl LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.`id_product` = pl.`id_product`))';

        $pass = $pass && Db::getInstance()->execute($sql);

        return ($pass);
    }

    public static function propagateProductActionToCategory($id_product, $id_category, $action)
    {
        $pass = true;

        // Add in the queue
        //
        $sql = 'SELECT  p.`id_product` FROM `'._DB_PREFIX_.'product` p'.
                        self::getIdShopAssociation().'
                        WHERE p.`id_category_default` = '.(int)$id_category.'
                        GROUP by p.`id_product`';

        $result = Db::getInstance()->executeS($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        if (!$result) {
            return (false);
        }

        if (is_array($result)) {
            foreach ($result as $product) {
                self::marketplaceActionSet($action, $product['id_product']);
            }
        }

        return ($pass);
    }

    public static function propagateProductActionToManufacturer($id_product, $id_manufacturer, $action)
    {
        $pass = true;

        // Add in the queue
        //
        $sql = 'SELECT p.`id_product` FROM `'._DB_PREFIX_.'product` p'.
                        self::getIdShopAssociation().'
                        WHERE p.`id_manufacturer` = '.(int)$id_manufacturer.'
                        GROUP by p.`id_product`';

        $result = Db::getInstance()->executeS($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        if (!$result) {
            return (false);
        }

        if (is_array($result)) {
            foreach ($result as $product) {
                self::marketplaceActionSet($action, $product['id_product']);
            }
        }

        return ($pass);
    }

    public static function propagateProductActionToSupplier($id_product, $id_supplier, $action)
    {
        $pass = true;

        // Add in the queue
        //
        $sql = 'SELECT p.`id_product` FROM `'._DB_PREFIX_.'product` p '.
                        self::getIdShopAssociation().'
                        WHERE p.`id_supplier` = '.(int)$id_supplier.'
                        GROUP by p.`id_product`';

        $result = Db::getInstance()->executeS($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        if (!$result) {
            return (false);
        }

        if (is_array($result)) {
            foreach ($result as $product) {
                self::marketplaceActionSet($action, $product['id_product']);
            }
        }

        return ($pass);
    }

    public static function propagateProductActionToShop($id_product, $action)
    {
        $pass = true;

        // Add in the queue
        //
        $sql = 'SELECT p.`id_product`
        FROM `'._DB_PREFIX_.'product` p '.
        self::getIdShopAssociation().'
        GROUP by p.`id_product`';

        $result = Db::getInstance()->executeS($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        if (!$result) {
            return (false);
        }

        if (is_array($result)) {
            foreach ($result as $product) {
                self::marketplaceActionSet($action, $product['id_product']);
            }
        }

        return ($pass);
    }

    public static function propagateProductOptionToCategory($id_product, $id_lang, $id_category, $field, $value)
    {
        $pass = true && self::fillProductOptions($id_product, $id_lang);

        if (is_float($value)) {
            $sql_value = (float)$value;
        } elseif (is_numeric($value) && is_int($value)) {
            $sql_value = (int)$value;
        } elseif (empty($value)) {
            $sql_value = 'NULL';
        } elseif (is_null($value)) {
            $sql_value = 'NULL';
        } else {
            $sql_value = '"'.pSQL($value).'"';
        }

        $sql = 'UPDATE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` mpo, `'._DB_PREFIX_.'product` p
                    SET mpo.`'.pSQL($field).'` = '.$sql_value.'
					WHERE mpo.`id_lang` = '.(int)$id_lang.' AND mpo.`id_product` IN 
					(SELECT p.`id_product` FROM `'._DB_PREFIX_.'product` p'.
                    self::getIdShopAssociation().'
					WHERE p.`id_category_default` = '.(int)$id_category.')';

        $result = Db::getInstance()->execute($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        $pass = $pass && $result;

        return ($pass);
    }

    public static function fillProductOptions($id_product, $id_lang)
    {
        $sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` (id_product, id_lang)
                        SELECT id_product, '.(int)$id_lang.' FROM `'._DB_PREFIX_.'product`';

        return (Db::getInstance()->execute($sql));
    }

    public static function propagateProductOptionToShop($id_product, $id_lang, $field, $value)
    {
        $pass = true && self::fillProductOptions($id_product, $id_lang);

        if (is_float($value)) {
            $sql_value = (float)$value;
        } elseif (is_numeric($value) && is_int($value)) {
            $sql_value = (int)$value;
        } elseif (empty($value)) {
            $sql_value = 'NULL';
        } elseif (is_null($value)) {
            $sql_value = 'NULL';
        } else {
            $sql_value = '"'.pSQL($value).'"';
        }

        $sql = 'UPDATE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` mpo, `'._DB_PREFIX_.'product` p '.
                    self::getIdShopAssociation().'
                    SET mpo.`'.pSQL($field).'` = '.$sql_value.'
                    WHERE p.`id_product` = mpo.`id_product` AND mpo.`id_lang` = '.(int)$id_lang;

        $result = Db::getInstance()->execute($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        $pass = $pass && $result;

        return ($pass);
    }

    public static function propagateProductOptionToManufacturer($id_product, $id_lang, $id_manufacturer, $field, $value)
    {
        $pass = true && self::fillProductOptions($id_product, $id_lang);

        if (is_float($value)) {
            $sql_value = (float)$value;
        } elseif (is_numeric($value) && is_int($value)) {
            $sql_value = (int)$value;
        } elseif (empty($value)) {
            $sql_value = 'NULL';
        } elseif (is_null($value)) {
            $sql_value = 'NULL';
        } else {
            $sql_value = '"'.pSQL($value).'"';
        }

        $sql = 'UPDATE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` mpo, `'._DB_PREFIX_.'product` p'.
                    self::getIdShopAssociation().'
                    SET mpo.`'.pSQL($field).'` = '.$sql_value.'
                    WHERE mpo.`id_lang` = '.(int)$id_lang.' AND mpo.`id_product` IN (SELECT `id_product` FROM `'._DB_PREFIX_.'product` WHERE `id_manufacturer` = '.(int)$id_manufacturer.')';

        $result = Db::getInstance()->execute($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        $pass = $pass && $result;

        return ($pass);
    }

    public static function propagateProductOptionToSupplier($id_product, $id_lang, $id_supplier, $field, $value)
    {
        $pass = true && self::fillProductOptions($id_product, $id_lang);

        if (is_float($value)) {
            $sql_value = (float)$value;
        } elseif (is_numeric($value) && is_int($value)) {
            $sql_value = (int)$value;
        } elseif (empty($value)) {
            $sql_value = 'NULL';
        } elseif (is_null($value)) {
            $sql_value = 'NULL';
        } else {
            $sql_value = '"'.pSQL($value).'"';
        }

        $sql = 'UPDATE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` mpo, `'._DB_PREFIX_.'product` p '.
                    self::getIdShopAssociation().'
                    SET mpo.`'.pSQL($field).'` = '.$sql_value.'
                    WHERE p.`id_product` = mpo.`id_product` AND mpo.`id_lang` = '.(int)$id_lang.' AND p.`id_supplier` = '.(int)$id_supplier;

        $result = Db::getInstance()->execute($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        $pass = $pass && $result;

        return ($pass);
    }

    public static function getAllProducts($id_lang, $id_category = false, $only_active = false)
    {
        $sql = 'SELECT p.id_product
                  FROM `'._DB_PREFIX_.'product` p '.($id_category ? 'LEFT JOIN `'._DB_PREFIX_.'category_product` c ON (c.`id_product` = p.`id_product`)' : '').'
                  LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)
                  WHERE pl.`id_lang` = '.(int)$id_lang.($id_category ? ' AND c.`id_category` = '.(int)$id_category : '').($only_active ? ' AND p.`active` = 1' : '').'
                  GROUP by p.id_product ORDER BY p.date_add desc ';

        $rq = Db::getInstance()->executeS($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($rq);
        }

        return ($rq);
    }

    public static function oldest()
    {
        $sql = '
                  SELECT MIN(date_add) as date_min FROM `'._DB_PREFIX_.'product`;';
        if (($rq = Db::getInstance()->executeS($sql)) && is_array($rq)) {
            $result = array_shift($rq);

            return ($result['date_min']);
        }

        return (false);
    }

    public static function marketplaceActionGet($id_product, $id_lang)
    {
        if ($id_product && !self::marketplaceInCategories($id_product)) {
            return (false);
        }

        $sql = '
                SELECT `action` FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS.'`
                    WHERE `marketplace` = "'.Amazon::MARKETPLACE.'" AND `id_lang` = '.(int)$id_lang.' AND `id_product`='.(int)$id_product.'
                    AND (`date_upd` = "" OR `date_upd` = NULL OR `date_upd` IS NULL)
                    ORDER by `action` LIMIT 1 ';

        if (!($rq = Db::getInstance()->executeS($sql))) {
            return (false);
        }

        return ((isset($rq[0]['action']) ? $rq[0]['action'] : false));
    }

    public static function marketplaceActionDelete($id_product)
    {
        $sql = '
                DELETE FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS.'`
                    WHERE `marketplace` = "'.pSQL(Amazon::MARKETPLACE).'" AND `id_product`='.(int)$id_product.'
                    AND (`date_upd` = "" OR `date_upd` = NULL OR `date_upd` IS NULL) ';

        if (!($rq = Db::getInstance()->execute($sql))) {
            return (false);
        }

        return (true);
    }

    public static function marketplaceActionList($id_lang, $action = null, $limiter = false)
    {
        if ($action) {
            $restrict = ' AND `action`="'.pSQL($action).'"';
        } else {
            $restrict = ' AND (`action`!="'.Amazon::ADD.'" AND `action`!="'.pSQL(Amazon::REPRICE).'")' ;
        }

        if ((int)$limiter) {
            $limit = ' LIMIT '.(int)$limiter;
        } else {
            $limit = null;
        }

        $sql = '
                SELECT `id_product`, `id_product_attribute`, `action`, `sku`, min(`date_add`) as date_start, max(`date_add`) as date_stop FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS.'`
                    WHERE `marketplace` = "'.Amazon::MARKETPLACE.'" AND id_lang = '.(int)$id_lang.'
                    AND (`date_upd` = "" OR `date_upd` = NULL OR `date_upd` IS NULL)'.$restrict.'
                    GROUP by `id_product`
                    ORDER by `date_add` ASC'.$limit;

        if (!($rq = Db::getInstance()->executeS($sql))) {
            return (false);
        }

        return ($rq);
    }

    public static function marketplaceActionUpdateCombination($id_product_attribute)
    {
        $sql = '
                        SELECT id_product
                        FROM `'._DB_PREFIX_.'product_attribute` pa
                        WHERE pa.`id_product_attribute` = '.(int)$id_product_attribute.' AND pa.`reference` > "" OR pa.`upc` > "")
                        GROUP by pa.`id_product` ';

        if (!($rq = Db::getInstance()->getRow($sql))) {
            return (false);
        }

        self::marketplaceActionSet(Amazon::UPDATE, $rq['id_product']) || $pass = false;

        return ($pass);
    }

    public static function marketplaceActionRemoveCombination($id_product, $id_product_attribute)
    {
        $sql = '
                        SELECT pa.`reference`
                        FROM `'._DB_PREFIX_.'product_attribute` pa
                        WHERE pa.`id_product_attribute` = '.(int)$id_product_attribute.' AND pa.`id_product` = '.(int)$id_product.'
                        GROUP by pa.`reference` ';

        if (!($rq = Db::getInstance()->getRow($sql))) {
            return (false);
        }

        if (empty($rq['reference'])) {
            return (false);
        }

        return (self::marketplaceActionSet(Amazon::REMOVE, $id_product, $id_product_attribute, $rq['reference']));
    }


    public static function marketplaceActionRemoveAllCombinations($action, $id_product)
    {
        $pass = true;

        $sql = '
                        SELECT reference, id_product_attribute
                        FROM `'._DB_PREFIX_.'product_attribute` pa
                        WHERE pa.`id_product` = '.(int)$id_product.' AND pa.`reference` > "" AND (pa.`ean13` > "" OR pa.`upc` > "")
                        GROUP by pa.`id_product`, pa.`id_product_attribute`, pa.`reference` ';

        if (!($rq = Db::getInstance()->executeS($sql))) {
            return (false);
        }

        foreach ($rq as $combination) {
            self::marketplaceActionSet(Amazon::REMOVE, $id_product, $combination['id_product_attribute'], $combination['reference']) || $pass = false;
        }

        return ($pass);
    }

    public static function marketplaceGetCategory($id_product)
    {
        static $categories = null;
        static $id_product_to_categories = null;

        if ($categories === null) {
            $categories = AmazonConfiguration::get('categories');
        }

        if (!$categories || !is_array($categories) || !count($categories)) {
            return (false);
        }

        if ($id_product_to_categories === null) {
            $id_product_to_categories = array();

            $sql = 'SELECT `id_product`, `id_category` FROM `'._DB_PREFIX_.'category_product`';

            if (!$results = Db::getInstance()->executeS($sql)) {
                return (false);
            }
            if (is_array($results) && count($results)) {
                foreach ($results as $result) {
                    if (isset($result['id_product']) && $result['id_product']) {
                        $id_productx = $result['id_product'];
                        $id_product_to_categories[$id_productx][] = $result['id_category'];
                    }
                }
            }
        }

        if (isset($id_product_to_categories[$id_product])
            && is_array($id_product_to_categories[$id_product])
            && count($id_product_to_categories[$id_product])) {
            return($id_product_to_categories[$id_product]);
        } else {
            return(false);
        }
    }

    public static function marketplaceActionGetSku($id_product)
    {
        $sql = '
                        SELECT `reference`
                        FROM `'._DB_PREFIX_.'product` p
                        WHERE p.`id_product` = '.(int)$id_product.' AND p.`reference` > "" AND (p.`ean13` > "" OR p.`upc` > "")';

        $rq = Db::getInstance()->getRow($sql);

        if (isset($rq['reference']) && !empty($rq['reference'])) {
            return ($rq['reference']);
        }

        return (false);
    }

    public static function marketplaceActionGetCombinationSku($id_product)
    {
        $sql = '
                        SELECT reference, id_product_attribute
                        FROM `'._DB_PREFIX_.'product_attribute` pa
                        WHERE pa.`id_product` = '.(int)$id_product.' AND pa.`reference` > "" AND (pa.`ean13` > "" OR pa.`upc` > "")
                        GROUP by pa.`id_product`, pa.`id_product_attribute` ';

        $rq = Db::getInstance()->getRow($sql);

        if (isset($rq['reference']) && !empty($rq['reference'])) {
            return ($rq['reference']);
        }

        return (false);
    }

    public static function marketplaceGetAllProducts($id_lang, $only_active = false, $since = false)
    {
        // Categories Lookup
        //
        $categories = AmazonConfiguration::get('categories');

        if (!$categories || !is_array($categories) || !count($categories)) {
            return (false);
        }

        $list = implode(',', $categories);

        if ($since) {
            $dateRange = ' AND (p.`date_add` >= "'.pSQL($since).'" OR p.`date_upd` >= "'.pSQL($since).'") ';
        } else {
            $dateRange = '';
        }

        $sql = 'SELECT p.`id_product` FROM `'._DB_PREFIX_.'product` p
                        LEFT JOIN `'._DB_PREFIX_.'category_product` c ON (c.`id_product` = p.`id_product`)
                        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)
                        WHERE pl.`id_lang` = '.(int)$id_lang.$dateRange.($only_active ? ' AND p.`active` = 1' : '').'
                        AND c.id_category IN ('.pSQL($list).')
                        GROUP by p.id_product ORDER BY p.date_add desc ';

        $result = Db::getInstance()->executeS($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        return ($result);
    }

    public static function marketplaceCountProducts()
    {
        if (version_compare(_PS_VERSION_, '1.4', '<')) {
            $upc = false;
        } else {
            $upc = true;
        }

        // Categories Lookup
        //
        $categories = AmazonConfiguration::get('categories');

        if (!$categories || !is_array($categories) || !count($categories) || !array_filter($categories)) {
            return (false);
        }

        $list = implode(',', array_filter($categories));

        $sql = '
                        SELECT count(p.id_product) as products FROM `'._DB_PREFIX_.'product` p
                            LEFT JOIN `'._DB_PREFIX_.'category_product` c ON (c.`id_product` = p.`id_product`)
                            WHERE p.`active` = 1
                            AND p.`reference` > "" AND (p.`ean13` > "" '.($upc ? 'OR p.`upc` > ""' : '').')
                            AND c.id_category IN ('.pSQL($list).')';

        $rq1 = Db::getInstance()->executeS($sql);

        $sql = '
                        SELECT count(pa.id_product) as attributes FROM `'._DB_PREFIX_.'product_attribute` pa
                            LEFT JOIN `'._DB_PREFIX_.'category_product` c ON (c.`id_product` = pa.`id_product`)
                            WHERE
                              pa.`reference` > "" AND (pa.`ean13` > "" '.($upc ? 'OR pa.`upc` > ""' : '').')
                              AND c.id_category IN ('.pSQL($list).')
                            ';

        $rq2 = Db::getInstance()->executeS($sql);

        if (empty($rq1) or !is_array($rq1)) {
            $rq1 = array();
            $rq1[0] = array('products' => 0, 'attributes' => 0);
        }
        if (empty($rq2) or !is_array($rq2)) {
            $rq2 = array();
            $rq2[0] = array('products' => 0, 'attributes' => 0);
        }

        return (array_merge(array_shift($rq1), array_shift($rq2)));
    }

    public static function marketplaceActionReset($id_lang, $action = null)
    {
        if ($action) {
            $and_action = ' AND `action` = "'.trim($action).'"';
        } else {
            $and_action = '';
        }

        $sql = '
                        DELETE FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS.'` WHERE `id_lang`='.(int)$id_lang.$and_action;

        return (Db::getInstance()->execute($sql));
    }

    public static function marketplaceActionAcknowledgde($action, $id_lang, $productList, $dateAcknowledgde, $revert = false)
    {
        switch ($action) {
            case Amazon::ADD:
                $restrict = ' AND `action` = "'.pSQL(Amazon::ADD).'" ';
                break;
            case Amazon::REMOVE:
                $restrict = ' AND `action` = "'.pSQL(Amazon::REMOVE).'" ';
                break;
            case Amazon::REPRICE:
                $restrict = ' AND `action` = "'.pSQL(Amazon::REPRICE).'" ';
                break;
            default:
                $restrict = ' AND `action` = "'.pSQL(Amazon::UPDATE).'" ';
                break;
        }

        if (!is_array($productList) || !count($productList)) {
            return (false);
        }

        $list = implode('", "', array_map('pSQL', $productList));

        $sql = 'UPDATE `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS.'` SET `date_upd`="'.pSQL($dateAcknowledgde).'" WHERE `id_product` IN ("'.$list.'") AND `id_lang`='.(int)$id_lang.$restrict;

        Db::getInstance()->execute($sql);

        // Cleaning old entries
        //
        if (in_array($action, array(Amazon::ADD, Amazon::REMOVE, Amazon::UPDATE, Amazon::REPRICE))) {
            $sql = 'DELETE FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS.'` WHERE `date_upd` < DATE_ADD(NOW(), INTERVAL -7 DAY) AND `action`="'.pSQL($action).'"';

            Db::getInstance()->execute($sql);
        }

        return (true);
    }

    public static function updateProductQuantity($id_product, $id_product_attribute, $quantity)
    {
        $pass = true;

        if ($id_product_attribute) {
            $sql = '
                      UPDATE `'._DB_PREFIX_.'product_attribute` set quantity = '.(int)$quantity.' where id_product='.(int)$id_product.' and id_product_attribute = '.(int)$id_product_attribute;

            if (!$rq = Db::getInstance()->execute($sql)) {
                $pass = $pass && false;
            }

            $sql = '
                      UPDATE `'._DB_PREFIX_.'product` set quantity = '.(int)$quantity.' where id_product='.(int)$id_product;

            if (!$rq = Db::getInstance()->execute($sql)) {
                $pass = $pass && false;
            }
        } else {
            $sql = '
                      UPDATE `'._DB_PREFIX_.'product` set quantity = "'.(int)$quantity.'" where id_product='.(int)$id_product;

            if (!$rq = Db::getInstance()->execute($sql)) {
                $pass = $pass && false;
            }
        }

        return ($pass);
    }

    public static function getProductCode($type = self::FILEFORMAT_REFERENCE, $code = 'ean13')
    {
        $categories = AmazonConfiguration::get('categories');

        if (!$categories || !is_array($categories) || !count($categories)) {
            return (false);
        }

        $category_list = implode(',', $categories);

        $code_string = sprintf('if (pa.id_product_attribute, pa.`%s`, p.`%s`) AS `%s`', $code, $code, $code);

        if ($type == self::FILEFORMAT_REFERENCE) {
            $type_string = 'p.`id_product`, pa.`id_product_attribute`';
        } elseif ($type == self::FILEFORMAT_ID) {
            $type_string = 'if (pa.id_product_attribute, pa.`reference`, p.`reference`) AS `reference`';
        }

        $sql = 'SELECT '.$type_string.', '.$code_string.', pl.`name`
                         FROM `'._DB_PREFIX_.'product` p
                             LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.id_product = pl.id_product)
                             LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.id_product = pa.id_product)
                             LEFT JOIN `'._DB_PREFIX_.'category_product` cp on (p.id_product = cp.id_product)
                         WHERE p.active = 1 AND cp.id_category IN ('.pSQL($category_list).')
                             GROUP BY p.id_product, pa.id_product_attribute';

        return (Db::getInstance()->executeS($sql));
    }

    public static function getCurrentQueue()
    {
        // Check if exists
        //
        $tables = Db::getInstance()->executeS('SHOW tables LIKE "%'.Amazon::TABLE_MARKETPLACE_ACTIONS.'%"');

        if (is_array($tables) && count($tables)) {
            $sql = '
                SELECT COUNT(id_product) as count, id_lang, action, MIN(date_add) as date_min, MAX(date_add) as date_max FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ACTIONS.'`
                 WHERE `marketplace` = "'.pSQL(Amazon::MARKETPLACE).'" and `date_upd` IS NULL
                 GROUP by id_lang, action';

            $result = Db::getInstance()->executeS($sql);

            return ($result ? $result : array());
        }

        return (false);
    }

    public static function getProductsToSynch($id_lang)
    {
        // Categories Lookup
        //
        $categories = AmazonConfiguration::get('categories');

        if (!$categories || !is_array($categories) || !count($categories)) {
            return (false);
        }

        $list = rtrim(implode(',', $categories), ',');

        $sql = 'SELECT p.`id_product`, pa.`id_product_attribute`, IF (pa.`ean13`, pa.`ean13`, p.`ean13`) as ean13, IF (pa.`upc`, pa.`upc`, p.`upc`) as upc, IF (pa.reference > "", pa.reference, p.reference) as reference, p.`id_manufacturer` FROM `'._DB_PREFIX_.'product` p
                    LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa on (p.id_product = pa.id_product)
                    LEFT JOIN `'._DB_PREFIX_.'category_product` cp on (p.id_product = cp.id_product)
                    LEFT JOIN `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` po on (po.`id_product` = p.`id_product` AND pa.`id_product_attribute` = po.`id_product_attribute` AND po.`id_lang` = '.(int)$id_lang.')
                    WHERE   p.`active` = 1 AND NOT po.`disable` IS NOT NULL
                            AND NOT `asin1` IS NOT NULL AND (p.`ean13` or p.`upc` or pa.`ean13` or pa.`upc`) IS NOT NULL
                            AND cp.`id_category` IN ('.pSQL($list).') AND po.`id_lang` = '.(int)$id_lang.'
                    GROUP  BY p.`id_product`, pa.`id_product_attribute`
                    HAVING reference > ""';

        return (Db::getInstance()->executeS($sql));
    }

    public static function getProductsToCreate($id_lang)
    {
        // Categories Lookup
        //
        $categories = AmazonConfiguration::get('categories');

        if (!$categories || !is_array($categories) || !count($categories)) {
            return (false);
        }

        $list = rtrim(implode(',', $categories), ',');

        $sql = 'SELECT p.`id_product`, pa.`id_product_attribute`, IF (pa.`ean13`, pa.`ean13`, p.`ean13`) as ean13, IF (pa.`upc`, pa.`upc`, p.`upc`) as upc, po.`asin1`, IF (pa.reference > "", pa.reference, p.reference) as reference FROM `'._DB_PREFIX_.'product` p
                    LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa on (p.id_product = pa.id_product)
                    LEFT JOIN `'._DB_PREFIX_.'category_product` cp on (p.id_product = cp.id_product)
                    LEFT JOIN `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` po on (po.`id_product` = p.`id_product` AND pa.`id_product_attribute` = po.`id_product_attribute` AND po.`id_lang` = '.(int)$id_lang.')
                    WHERE   p.`active` = 1
                            AND NOT `asin1` IS NOT NULL AND (p.`ean13` or p.`upc` or pa.`ean13` or pa.`upc`) IS NOT NULL
                            AND cp.`id_category` IN ('.pSQL($list).')
                    GROUP  BY p.`id_product`
                    HAVING reference > ""';

        return (Db::getInstance()->executeS($sql));
    }

    public static function getBusinessPriceRulesBreakdown($id_product, $id_product_attribute, $id_shop, $id_currency, $id_country, $id_group)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'specific_price` WHERE `id_product` IN (0, '.(int)$id_product.')  AND `from_quantity` > 0
				AND `id_product_attribute` IN (0, '.(int)$id_product_attribute.')
				AND `id_shop` IN (0, '.(int)$id_shop.')
				AND `id_currency` IN (0, '.(int)$id_currency.')
				AND `id_country` IN (0, '.(int)$id_country.')
				AND `id_group` IN (0, '.(int)$id_group.')
				AND NOW() > `from` AND `to` > NOW() + INTERVAL 1 DAY ORDER BY `from_quantity` LIMIT 3;
                ';
        return (Db::getInstance()->executeS($sql));
    }
}
