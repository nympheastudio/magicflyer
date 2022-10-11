<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2016 silbersaiten
 * @version   1.2.7
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_2_7($object)
{
    return Db::getInstance()->execute(
        'ALTER TABLE `'._DB_PREFIX_.'fm_form_lang` ADD `submit_button` varchar(100) DEFAULT NULL AFTER `message_on_completed`'
    );
}
