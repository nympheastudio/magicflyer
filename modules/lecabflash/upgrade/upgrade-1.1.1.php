<?php
/**
 * 2009-2017 202 ecommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    202 ecommerce <support@202-ecommerce.com>
 * @copyright 2009-2017 202 ecommerce SARL
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @param LecabFlash $module
 * @return bool
 */
function upgrade_module_1_1_1($module)
{
    $sql = sql_upgrade_1_1_1();

    if (!empty($sql) && is_array($sql)) {
        foreach ($sql as $request) {
            try {
                if (!Db::getInstance()->execute($request)) {
                    return false;
                }
            } catch (\PrestaShopDatabaseException $e) {
                return false;
            }
        }
    }

    Tools::clearSmartyCache();

    return true;
}

function sql_upgrade_1_1_1()
{
    $sql   = array();
    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'lecabflash_carts`
      ADD `estimate_delay` INT UNSIGNED NULL DEFAULT NULL AFTER `estimate_response`,
      ADD `estimate_duration_min` INT UNSIGNED NULL DEFAULT NULL AFTER `estimate_delay`,
      ADD `estimate_duration_max` INT UNSIGNED NULL DEFAULT NULL AFTER `estimate_duration_min`,
      ADD `confirm_delay` INT UNSIGNED NULL DEFAULT NULL AFTER `confirm_id`
      ADD `last_error` int(10) unsigned DEFAULT NULL';

    return $sql;
}
