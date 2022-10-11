<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2016 silbersaiten
 * @version   1.2.5
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($object)
{
    return (Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fm_form_group` (
        `id_fm_form`       int(10)         unsigned NOT NULL,
        `id_group`          int(10)         unsigned NOT NULL,
        PRIMARY KEY (`id_fm_form`,`id_group`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')
        && Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fm_form_report` (
        `id_fm_form_report` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_fm_form` int(10) unsigned NOT NULL,
        `id_customer` int(10) unsigned NOT NULL,
        `id_product` int(10) unsigned DEFAULT NULL,
        `name` varchar(128) NOT NULL,
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`id_fm_form_report`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')
        && Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fm_form_report_values` (
        `id_fm_form_report` int(10) unsigned NOT NULL,
        `type` int(10) unsigned NOT NULL DEFAULT \'0\',
        `field` varchar(128) NOT NULL,
        `value` text
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8')
        && Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'fm_form_lang` ADD `link_rewrite` varchar(128) NOT NULL AFTER `page_title`')
        && Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'fm_form` ADD `captcha` tinyint(1) NOT NULL DEFAULT \'0\' AFTER `submit_delay`')
        && $object->registerHook('moduleRoutes')
    );
}
