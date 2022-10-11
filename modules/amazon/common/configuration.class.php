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
 * @package   Common-Classes
 * Support by mail:  support@common-services.com
 */

if (! class_exists('CommonConfiguration')) {
    abstract class CommonConfiguration extends Configuration
    {
        // Override-able
        public static $module;
        public static $configuration_table = 'marketplace_configuration';

        public static $definition_backup;

        // Override Configuration definition
        public static function setDefinition()
        {
            self::$definition_backup = Configuration::$definition;
            Configuration::$definition['table'] = static::$configuration_table;
        }

        // Restore Configuration definition
        public static function unsetDefinition()
        {
            Configuration::$definition = self::$definition_backup;
        }

        /**
         * Update configuration value
         *
         * @param string $configuration_key
         * @param mixed $data
         * @param bool $html
         * @param null $id_shop_group
         * @param null $id_shop
         *
         * @return bool
         */
        public static function updateValue($configuration_key, $data, $html = false, $id_shop_group = null, $id_shop = null)
        {
            $key = Tools::strtoupper(sprintf(static::$module.'_%s', $configuration_key));
            if (!Validate::isConfigName($key)) {
                die(Tools::displayError());
            }

            // Get multi-shop id for modern versions
            if (CommonTools::getPsVersion() != '1.4') {
                $id_shop = Shop::getContextShopID(true);
                $id_shop_group = Shop::getContextShopGroupID(true);
            }

            // Sanitize data
            $data          = serialize($data);
            $id_shop_group = (int)$id_shop_group ? (int)$id_shop_group : null;
            $id_shop       = (int)$id_shop ? (int)$id_shop : null;
            $now           = date('Y-m-d H:i:s');

            // Update database
            $db = Db::getInstance();

            // 1 - Try to update to module specific table static:$configuration_table
            if (CommonTools::tableExists(_DB_PREFIX_.static::$configuration_table)) {
                if (CommonTools::getPsVersion() == '1.7') {
                    return self::_updateValuePs17($key, $data, $html, $id_shop_group, $id_shop);
                } elseif (CommonTools::getPsVersion() == '1.6') {
                    // Fix hard code bug in PS-1.6.1.1: ConfigurationCore:446
                    self::setDefinition();
                    if (!Configuration::hasKey($key, null, $id_shop_group, $id_shop)) {
                        if (!Configuration::getIdByName($key, $id_shop_group, $id_shop)) {
                            $update_data = array(
                                'name' => pSQL($key),
                                'value' => pSQL($data),
                                'date_add' => pSQL($now),
                                'date_upd' => pSQL($now),
                                'id_shop_group' => $id_shop_group,
                                'id_shop' => $id_shop
                            );
                            $db->insert(static::$configuration_table, $update_data, true);
                        }
                    }
                    $result = Configuration::updateValue($key, $data, $html, $id_shop_group, $id_shop);
                    self::unsetDefinition();
                } else {
                    // DbCore in Ps1.4 does not have update() or insert()
                    $where = '`name` = "'.pSQL($key).'"';
                    if (CommonTools::getPsVersion() == '1.5') {
                        $where .= Configuration::sqlRestriction($id_shop_group, $id_shop);
                    }
                    $row = $db->getRow('SELECT * FROM `'.pSQL(_DB_PREFIX_.static::$configuration_table).'` WHERE '.$where);
                    if ($row) {
                        $result = $db->execute('UPDATE `'.pSQL(_DB_PREFIX_.static::$configuration_table).'` SET `value` = "'.pSQL($data, $html).'", `date_upd` = "'.pSQL($now).'" WHERE '.$where);
                    } else {
                        $result = $db->execute('INSERT INTO `'.pSQL(_DB_PREFIX_.static::$configuration_table).'`(`name`, `value`, `date_add`, `date_upd`) VALUES("'.pSQL($key).'", "'.pSQL($data, $html).'", "'.pSQL($now).'", "'.pSQL($now).'")');
                    }
                }

                return $result;
            }

            // Old table need to save with base64_encode
            $marketplace_configuration_key = Tools::strtolower($configuration_key);
            $data_encode = base64_encode($data);

            // 2 - Update on general table if specific table not exist
            if (CommonTools::tableExists(_DB_PREFIX_.self::$configuration_table)) {
                $sql = 'REPLACE INTO `'.pSQL(_DB_PREFIX_.self::$configuration_table).'`(`marketplace`, `configuration`, `value`) 
                        VALUES("'.pSQL(static::$module).'", "'.pSQL($marketplace_configuration_key).'", "'.pSQL($data_encode).'")';

                return Db::getInstance()->execute($sql);
            }

            // 3 - Last try on ps table
            if (CommonTools::getPsVersion() == '1.4') {
                return Configuration::updateValue($key, $data_encode, $html);
            } else {
                return Configuration::updateValue($key, $data_encode, $html, $id_shop_group, $id_shop);
            }
        }

        /**
         * Get configuration from default table, if not exist get from ps table
         *
         * @param string $configuration_key
         * @param null $id_lang
         * @param null $id_shop_group
         * @param null $id_shop
         * @param bool $default
         *
         * @return mixed|string
         */
        public static function get($configuration_key, $id_lang = null, $id_shop_group = null, $id_shop = null, $default = false)
        {
            $prestashop_configuration_key = Tools::strtoupper(sprintf(static::$module.'_%s', $configuration_key));
            $marketplace_configuration_key = Tools::strtolower($configuration_key);
            $db = DB::getInstance();

            // Query on child table, use static::$configuration_table
            if (CommonTools::tableExists(_DB_PREFIX_.static::$configuration_table)) {
                $sql = "SELECT `value` FROM `"._DB_PREFIX_.static::$configuration_table."` WHERE `name` = '".pSQL($prestashop_configuration_key)."'";
                if (CommonTools::getPsVersion() == '1.6' || CommonTools::getPsVersion() == '1.7') {
                    self::setDefinition();
                    if (!isset(self::$_cache[self::$definition['table']])) {
                        // Fix for PS 1.7
                        self::loadConfiguration();
                    }
                    $result = Configuration::get($prestashop_configuration_key, $id_lang, $id_shop_group, $id_shop, $default);
                    self::unsetDefinition();
                } else {
                    if (CommonTools::getPsVersion() == '1.5') {
                        $result = $db->getValue($sql.Configuration::sqlRestriction($id_shop_group, $id_shop));
                    } else {
                        $result = $db->getValue($sql);
                    }
                }
                if ($result) {
                    return unserialize(self::returnValue($result));
                }
            }

            // If specific module table does not contain the value, go up to general table
            if (CommonTools::tableExists(_DB_PREFIX_.self::$configuration_table)) {
                $sql = 'SELECT `value`
                    FROM `'.pSQL(_DB_PREFIX_.self::$configuration_table).'`
                    WHERE `marketplace` = "'.pSQL(static::$module).'"
                    AND `configuration` = "'.pSQL($marketplace_configuration_key).'"';
                $result = Db::getInstance()->getRow($sql);

                if ($result && isset($result, $result['value'])) {
                    return unserialize(self::returnValue($result['value']));
                }
            }

            // Old table also doesn't have the value, last try on ps table
            if (CommonTools::getPsVersion() == '1.4') {
                return unserialize(self::returnValue(Configuration::get($prestashop_configuration_key, $id_lang)));
            } else {
                return unserialize(self::returnValue(Configuration::get($prestashop_configuration_key, $id_lang, $id_shop_group, $id_shop, $default)));
            }
        }

        /**
         * Check if value is base64 encoded and returns it decoded
         * @param $configuration
         *
         * @return bool|string
         */
        public static function returnValue($configuration)
        {
            if (!is_string($configuration)) {
                return $configuration;
            }
            if (base64_encode(base64_decode($configuration, true)) === $configuration) {
                //TODO: Validation: Use to evaluate base64 encoded values, required
                $value = base64_decode($configuration, true);
            } else {
                //TODO: Validation: Required by test above
                $value = $configuration;
            }
            return($value);
        }

        /**
         * Check if Combination is active
         *
         * @return bool
         */
        public static function combinationIsFeatureActive()
        {
            return CommonTools::getPsVersion() == '1.4' ? true : Combination::isFeatureActive();
        }

        /**
         * Check if Feature if active
         *
         * @return bool
         */
        public static function featureIsFeatureActive()
        {
            return CommonTools::getPsVersion() == '1.4' ? true : Feature::isFeatureActive();
        }

        /**
         * Check if multishop is active
         *
         * @return bool
         */
        public static function shopIsFeatureActive()
        {
            return CommonTools::getPsVersion() == '1.4' ? false : Shop::isFeatureActive();
        }

        /**
         * Update value for PrestaShop version 1.7
         *
         * @param $key
         * @param $value
         * @param $html
         * @param $id_shop_group
         * @param $id_shop
         *
         * @return bool
         */
        protected static function _updateValuePs17($key, $value, $html, $id_shop_group, $id_shop)
        {
            self::setDefinition();
            if (! isset(self::$_cache[self::$definition['table']])) {
                self::loadConfiguration();
            }

            $now = date('Y-m-d H:i:s');
            if (! self::_configHasKeyPs17($key, null, $id_shop_group, $id_shop)) {
                $data = array(
                    'id_shop_group' => (int)$id_shop_group,
                    'id_shop'       => (int)$id_shop,
                    'name'          => pSQL($key),
                    'value'         => pSQL($value),
                    'date_add'      => pSQL($now),
                    'date_upd'      => pSQL($now),
                );
                $result = Db::getInstance()->insert(self::$definition['table'], $data, true);
            } else {
                $result = Db::getInstance()->update(self::$definition['table'], array(
                    'value'     => pSQL($value),
                    'date_upd'  => pSQL($now),
                ), '`name` = \''.$key.'\''.Configuration::sqlRestriction($id_shop_group, $id_shop), 1, true);
            }
            self::set($key, $value, $id_shop_group, $id_shop);
            self::unsetDefinition();

            return $result;
        }

        // Check if cache configurations contain specific key, ConfigurationCore::hasKey - PS1.6
        protected static function _configHasKeyPs17($key, $id_lang = null, $id_shop_group = null, $id_shop = null)
        {
            if (!is_int($key) && !is_string($key)) {
                return false;
            }

            $id_lang = (int)$id_lang;

            if ($id_shop) {
                return isset(self::$_cache[self::$definition['table']][$id_lang]['shop'][$id_shop])
                       && (isset(self::$_cache[self::$definition['table']][$id_lang]['shop'][$id_shop][$key])
                           || array_key_exists($key, self::$_cache[self::$definition['table']][$id_lang]['shop'][$id_shop]));
            } elseif ($id_shop_group) {
                return isset(self::$_cache[self::$definition['table']][$id_lang]['group'][$id_shop_group])
                       && (isset(self::$_cache[self::$definition['table']][$id_lang]['group'][$id_shop_group][$key])
                           || array_key_exists($key, self::$_cache[self::$definition['table']][$id_lang]['group'][$id_shop_group]));
            }

            return isset(self::$_cache[self::$definition['table']][$id_lang]['global'])
                   && (isset(self::$_cache[self::$definition['table']][$id_lang]['global'][$key])
                       ||  array_key_exists($key, self::$_cache[self::$definition['table']][$id_lang]['global']));
        }
    }
}
