<?php
/**
* Paybox by Verifone PrestaShop Module
*
* Feel free to contact Paybox by Verifone at support@paybox.com for any
* question.
*
* LICENSE: This source file is subject to the version 3.0 of the Open
* Software License (OSL-3.0) that is available through the world-wide-web
* at the following URI: http://opensource.org/licenses/OSL-3.0. If
* you did not receive a copy of the OSL-3.0 license and are unable 
* to obtain it through the web, please send a note to
* support@paybox.com so we can mail you a copy immediately.
*
*  @category  Module / payments_gateways
*  @version   2.1.0
*  @author    BM Services <contact@bm-services.com>
*  @copyright 2012-2016 Paybox
*  @license   http://opensource.org/licenses/OSL-3.0
*  @link      http://www.paybox.com/
*/

if (!defined('_PS_VERSION_')) {
    exit;
}
 
function upgrade_module_2_1_0($object)
{
    if (version_compare(_PS_VERSION_, '1.5', '>=')) {
        $sql = array();
        $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'paybox_order` ADD `initial_amount` int(10) unsigned NOT NULL';

        foreach ($sql as $query) 
            if (!Db::getInstance()->Execute($query))
                return false;
    }

    return $object->registerHook('actionObjectOrderUpdateAfter');
}
