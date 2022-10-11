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

/**
 * @param LaNavettePickup $module
 * @return bool
 */
function upgrade_module_2_1_0($module)
{
    $sql = sql_upgrade_2_1_0();

    $logger = null;
    if ((bool)Configuration::get('LNP2_LOGS')) {
        $logger = new Lnp2Logger();
    }

    if (!empty($sql) && is_array($sql)) {
        foreach ($sql as $request) {
            try {
                if (!Db::getInstance()->execute($request)) {
                    if ($logger) {
                        $logger->logError('[UPGRADE 2.1.0] Erreur requette sql : '.$request);
                        $module->addError('message error : '.$request);
                    }

                    return false;
                }
            } catch (\PrestaShopDatabaseException $e) {
                if ($logger) {
                    $logger->logError('[UPGRADE 2.1.0] Erreur requette sql : '.$request.'
                    message error : '.$e->getMessage());
                    $module->addError('message error : '.$e->getMessage());
                }

                return false;
            }
        }
    }
    if (!$module->unregisterHook('displayFooter')) {
        return false;
    }

    return true;
}

function sql_upgrade_2_1_0()
{
    $sql   = array();
    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'lnp2_cart`
      ADD COLUMN `insurance` TINYINT(1) NOT NULL AFTER `shipping_cost`';

    return $sql;
}
