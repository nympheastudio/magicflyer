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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_.'amazon/classes/amazon.tools.class.php');
require_once(_PS_MODULE_DIR_.'amazon/classes/amazon.configuration.class.php');

/**
 * Add column to store customization for each item in Amazon order
 * @param Amazon $module
 * @return bool
 */
function upgrade_module_4_2_279($module)
{
    $table = _DB_PREFIX_ . $module::TABLE_MARKETPLACE_ORDER_ITEMS;

    if (CommonTools::tableExists($table) && !AmazonTools::amazonFieldExists($table, 'customization')) {
        $sql = "ALTER TABLE `" . pSQL($table) . "` ADD COLUMN `customization` TEXT NULL DEFAULT NULL AFTER `reason`";
        return Db::getInstance()->execute($sql);
    }

    return true;
}
