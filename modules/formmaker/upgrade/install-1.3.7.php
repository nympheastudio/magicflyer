<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2017 silbersaiten
 * @version   1.3.7
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_3_7($object)
{
    return Db::getInstance()->execute(
        'ALTER TABLE `'._DB_PREFIX_.'fm_form_report` ADD `send` tinyint(1) NOT NULL DEFAULT \'0\' AFTER `name`'
    ) && Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'fm_form_report` SET `send` = 1');
}
