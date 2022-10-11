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
 * Get context config from db (ps_marketplace_configuration),
 * save it with new format to new table (ps_amazon_configuration)
 *
 * This work to make sure first get context will not fail when use new function.
 *
 * New format is serialize + encode + serialize + encode (double transform)
 * Because context contain HTML, serialize alone not working
 * @param $module
 * @return bool
 */
function upgrade_module_4_2_259($module)
{
    // Old value is serialize + encode
    $marketplace_table = _DB_PREFIX_ . CommonConfiguration::$configuration_table;
    $marketplace_configuration_key = Tools::strtolower('context');

    if (CommonTools::tableExists($marketplace_table)) {
        $sql = "SELECT `value` FROM `$marketplace_table`
                WHERE `marketplace` = '".pSQL(AmazonConfiguration::$module)."'
                AND `configuration` = '".pSQL($marketplace_configuration_key)."'";
        $result = Db::getInstance()->getRow($sql);

        if ($result && isset($result, $result['value'])) {
            $storedContexts = unserialize(base64_decode($result['value']));
            if (CommonTools::tableExists(_DB_PREFIX_. AmazonConfiguration::$configuration_table, false)) {
                return AmazonConfiguration::updateValue('context', base64_encode(serialize($storedContexts)));
            }
        }
    }

    return true;
}
