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

class AmazonValidValues
{
    const TABLE = 'amazon_valid_values';
    const TABLE_CUSTOM = 'amazon_valid_values_custom';

    public static $file_prefix = 'amazon_valid_values';

    const FILE_EXT_GZ = '.sql.gz';
    const FILE_EXT_SQL = '.sql';
    const FILE_EXT_MD5 = '.md5';

    const SOURCE_URL = 'https://s3-us-west-2.amazonaws.com/common-services-public/amazon/data/'; // Public directory on S3

    const TIMEOUT = 120;
    
    public static function tableCreate()
    {
        $pass = true;

        if (!AmazonTools::tableExists(_DB_PREFIX_.self::TABLE)) {
            $sql = '
                    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_CUSTOM.'` (
					  `region` varchar(3) NOT NULL,
					  `universe` varchar(32) NOT NULL,
					  `product_type` varchar(64) DEFAULT NULL,
					  `attribute_field` varchar(64) NOT NULL,
					  `valid_value` varchar(255) NOT NULL,
					  `date_upd` datetime DEFAULT NULL,
						KEY `IDX1` (`region`,`universe`,`product_type`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

            if (!($result = Db::getInstance()->execute($sql))) {
                if (Amazon::$debug_mode) {
                    AmazonTools::pre(array(
                        "Unable to create table valid values\n",
                        "SQL:\n",
                        $sql,
                        $result
                    ));
                }
                $pass = false;
            }
        }
        return($pass);
    }

    public static function tableExists()
    {
        return AmazonTools::tableExists(_DB_PREFIX_.self::TABLE);
    }

    public static function tableClear()
    {
        return $pass = Db::getInstance()->execute('TRUNCATE `'._DB_PREFIX_.self::TABLE.'`;');
    }

    public static function lastImport()
    {
        $sql = 'SELECT MAX(`date_upd`) as last_import FROM `'._DB_PREFIX_.self::TABLE.'`';

        $results = Db::getInstance()->executeS($sql);

        if (is_array($results) && count($results)) {
            $result = reset($results);

            if (isset($result['last_import']) && !empty($result['last_import'])) {
                return ($result['last_import']);
            }
        }

        return (null);
    }

    public static function importSQL($file_sql)
    {
        $pass = true;

        if (AmazonTools::tableExists(_DB_PREFIX_.self::TABLE)) {
            $pass = Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE.'`');
        }

        if (!file_exists($file_sql)) {
            return false;
        } else {
            if (!$sql = AmazonTools::fileGetContents($file_sql)) {
                return false;
            }
        }

        $sql = str_replace(array('_DB_PREFIX_'), array(_DB_PREFIX_), $sql);
        $sql = preg_split("/;\s*[\r\n]+/", trim($sql));

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute(trim($query))) {
                $pass = false;
            }
        }

        return ($pass);
    }

    public static function getAttributesForProductType($universe, $product_type)
    {
        static $attributes = array();

        $universe_condition = '`universe` = "'.pSQL($universe).'"' ;

        if (in_array($universe, array('ClothingAccessories', 'ProductClothing'))) {
            $universe_condition = '`universe` IN ("ClothingAccessories", "ProductClothing", "Apparel")';
        }


        $key = sprintf('%s/%s', $universe, $product_type);

        if (isset($attributes[$key])) {
            return ($attributes[$key]);
        } elseif (AmazonTools::tableExists(_DB_PREFIX_.self::TABLE)) {
            $sql = 'SELECT `attribute_field` FROM `'._DB_PREFIX_.self::TABLE.'` WHERE '.$universe_condition.' AND `product_type` = "'.pSQL($product_type).'" and `attribute_field` NOT IN ("SizeMap", "ColorMap")
				GROUP BY `attribute_field`';

            $result = Db::getInstance()->executeS($sql);

            if (is_array($result) && count($result)) {
                foreach ($result as $field) {
                    $attributes[$key][] = $field['attribute_field'];
                }
                return ($attributes[$key]);
            }
        }

        return (array());
    }

    public static function getAttributesForUniverse($universe, $region = null)
    {
        static $attributes = array();

        $universe_condition = '`universe` = "'.pSQL($universe).'"' ;

        if (in_array($universe, array('ClothingAccessories', 'ProductClothing'))) {
            $universe_condition = '`universe` IN ("ClothingAccessories", "ProductClothing", "Apparel")';
        }

        $key = sprintf('%s/%s', $universe, $region);

        if (isset($attributes[$key])) {
            return ($attributes[$key]);
        } elseif (AmazonTools::tableExists(_DB_PREFIX_.self::TABLE)) {
            $where_sql = $universe_condition.' AND `attribute_field` NOT IN ("SizeMap", "ColorMap") ';

            if ($region) {
                $where_sql .= 'AND `region`="'.pSQL($region).'"';
            }

            $sql = 'SELECT `attribute_field` FROM `'._DB_PREFIX_.self::TABLE.'` WHERE '.$where_sql.' GROUP BY `attribute_field`';

            $result = Db::getInstance()->executeS($sql);

            if (is_array($result) && count($result)) {
                foreach ($result as $field) {
                    $attributes[$key][] = $field['attribute_field'];
                }

                return ($attributes[$key]);
            }
        }

        return (array());
    }

    public static function getValidValues($universe, $attribute, $region = null)
    {
        static $attributes = array();

        $initial_attribute = $attribute;
        $initial_universe = $universe;

        $universe_condition = '`universe` = "'.pSQL($universe).'"' ;

        if (in_array($universe, array('ClothingAccessories', 'ProductClothing'))) {
            $universe_condition = '`universe` IN ("ClothingAccessories", "ProductClothing", "Apparel")';
        } elseif (in_array($universe, array('Toys'))) {
            $universe_condition = '`universe` IN ("Toys", "ToysBaby")';
        }

        $main_key = sprintf('%s/%s/%s', $universe, $attribute, $region);

        if (array_key_exists($main_key, $attributes)) {
            $custom_mappings = self::getCustomMapping($initial_universe, $initial_attribute, $region);

            return (AmazonTools::arrayReplace($attributes[$main_key], $custom_mappings));
        } elseif (AmazonTools::tableExists(_DB_PREFIX_.self::TABLE)) {
            if ($attribute == 'Color') {
                $attribute = 'ColorMap';
            } elseif ($attribute == 'Size') {
                $attribute = 'SizeMap';
            }

            $where_sql = 'WHERE '.$universe_condition.' AND `attribute_field` ="'.pSQL($attribute).'" ';

            if ($region) {
                $where_sql .= 'AND `region`="'.pSQL($region).'"';
            }

            $sql = 'SELECT `valid_value` FROM `'._DB_PREFIX_.self::TABLE.'` '.$where_sql;

            $result = Db::getInstance()->executeS($sql);

            $attributes[$main_key] = array();

            if (is_array($result) && count($result)) {
                foreach ($result as $field) {
                    if (!Tools::strlen($field['valid_value'])) {
                        continue;
                    }
                    $key = (string)AmazonTools::toKey($field['valid_value']);
                    $attributes[(string)$main_key][(string)$key] = (string)$field['valid_value'];
                }
            }

            $custom_mappings = self::getCustomMapping($initial_universe, $initial_attribute, $region);

            return (AmazonTools::filterRecursive(AmazonTools::arrayReplace($attributes[$main_key], $custom_mappings)));
        }

        return (array());
    }

    public static function getCustomMapping($universe, $amazon_attribute, $region = null)
    {
        static $custom_mapping = array();

        if (!AmazonTools::tableExists(_DB_PREFIX_.self::TABLE_CUSTOM)) {
            return ($custom_mapping);
        }

        $key = sprintf('%s/%s/%s', $universe, $amazon_attribute, $region);

        if (isset($custom_mapping[$key])) {
            return ($custom_mapping[$key]);
        }

        if ($region) {
            $region_sql = ' AND `region`="'.pSQL($region).'" ';
        } else {
            $region_sql = '';
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.self::TABLE_CUSTOM.'`
									WHERE `universe`="'.pSQL($universe).'" AND `attribute_field`="'.pSQL($amazon_attribute).'"'.$region_sql;

        $results = Db::getInstance()->executeS($sql);

        $custom_mapping[$key] = array();

        if (is_array($results) && count($results)) {
            foreach ($results as $result) {
                $custom_mapping[$key][AmazonTools::toKey($result['valid_value'])] = $result['valid_value'];
            }
            $custom_mapping[$key] = AmazonTools::filterRecursive($custom_mapping[$key]);
        }

        return ($custom_mapping[$key]);
    }

    public static function saveCustomMapping($custom_mapping)
    {
        if (AmazonTools::tableExists(_DB_PREFIX_.self::TABLE_CUSTOM)) {
            if (is_array($custom_mapping) && count($custom_mapping)) {
                foreach ($custom_mapping as $universe => $mappings1) {
                    if (!is_array($mappings1) || !count($mappings1)) {
                        continue;
                    }

                    foreach ($mappings1 as $amazon_attribute => $mappings2) {
                        if (!is_array($mappings2) || !count($mappings2)) {
                            continue;
                        }

                        foreach ($mappings2 as $region => $values) {
                            $mappings = explode(',', $values);

                            $sql = 'DELETE FROM `'._DB_PREFIX_.self::TABLE_CUSTOM.'`
									WHERE `region`="'.pSQL($region).'" AND  `universe`="'.pSQL($universe).'" AND `attribute_field`="'.pSQL($amazon_attribute).'";';

                            Db::getInstance()->execute($sql);

                            if (!is_array($mappings) || !count($mappings)) {
                                continue;
                            }

                            foreach ($mappings as $value) {
                                $sql = 'INSERT INTO `'._DB_PREFIX_.self::TABLE_CUSTOM.'`
									(`region`, `universe`, `attribute_field`, `valid_value`, `date_upd` ) VALUES
									("'.pSQL($region).'","'.pSQL($universe).'","'.pSQL($amazon_attribute).'","'.pSQL($value).'", NOW())';

                                Db::getInstance()->execute($sql);
                            }
                        }
                    }
                }
            }
        }
    }
}
