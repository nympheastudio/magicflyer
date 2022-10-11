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

function upgrade_module_4_2_282($module)
{
    $table = _DB_PREFIX_ . Amazon::TABLE_MARKETPLACE_STATS;

    if (! AmazonTools::tableExists($table)) {
        $sql = "CREATE TABLE `$table` (
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
                    `commissions` DECIMAL(10, 2) DEFAULT NULL,
                    `total_price` DECIMAL(10, 2) DEFAULT NULL,
                    `date_add` DATETIME NOT NULL,
                    `date_upd` DATETIME NOT NULL,
                    UNIQUE KEY `order` (`marketplace`, `mp_order_id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        return Db::getInstance()->execute($sql);
    }

    return true;
}
