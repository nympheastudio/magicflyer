<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2018, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_3_1($module)
{
    // Save for each shop
    if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
        $shop_groups = Shop::getTree();
        foreach ($shop_groups as $shop_group) {
            foreach ($shop_group['shops'] as $shop) {
                $cart_rule_ids_module = $module->getSetting('cart_rule_ids', false, $shop['id_shop_group'], $shop['id_shop']);
                if ($cart_rule_ids_module && !is_array($cart_rule_ids_module)) {
                    $cart_rule_ids_module = ElegantalFacebookShareProductTools::unserialize($cart_rule_ids_module);
                    if ($cart_rule_ids_module && is_array($cart_rule_ids_module)) {
                        $module->setSetting('cart_rule_ids', $cart_rule_ids_module, $shop['id_shop_group'], $shop['id_shop']);
                    } else {
                        $module->setSetting('cart_rule_ids', array(), $shop['id_shop_group'], $shop['id_shop']);
                    }
                } else {
                    $module->setSetting('cart_rule_ids', array(), $shop['id_shop_group'], $shop['id_shop']);
                }
            }
        }
    }

    // Save for all shops
    $cart_rule_ids_module = $module->getSetting('cart_rule_ids', false, '', '');
    if ($cart_rule_ids_module && !is_array($cart_rule_ids_module)) {
        $cart_rule_ids_module = ElegantalFacebookShareProductTools::unserialize($cart_rule_ids_module);
        if ($cart_rule_ids_module && is_array($cart_rule_ids_module)) {
            $module->setSetting('cart_rule_ids', $cart_rule_ids_module, '', '');
        } else {
            $module->setSetting('cart_rule_ids', array(), '', '');
        }
    } else {
        $module->setSetting('cart_rule_ids', array(), '', '');
    }

    return true;
}
