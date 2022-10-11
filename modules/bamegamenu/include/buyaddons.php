<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@buy-addons.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@buy-addons.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 * @since 1.6
 */

function MegamenuReturnVersion()
{
    $vs = explode('.', _PS_VERSION_);
    $version = $vs[0] . "." . $vs[1];
    return $version;
}

function MegamenuReturnLanguage()
{
    $defaultLanguage = (int) Configuration::get('PS_LANG_DEFAULT');
    $languages = Language::getLanguages(false);
    foreach ($languages as $language) {
        if ($language['id_lang'] == $defaultLanguage) {
            return $language;
        }
    }
    return false;
}

function MegamenugetImage($directory)
{
    if ($directory != '.') {
        $directory = rtrim($directory, '/') . '/';
    }
    $image = array();
    $handle=null;
    if ($handle == opendir($directory)) {
        $i = 0;
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                if ((strpos($file, ".jpg")) || (strpos($file, ".png")) || (strpos($file, ".gif"))) {
                    $image[str_replace(array(".png", ".jpg", ".gif"), "", $file)] = $file;
                }
            }
            $i++;
        }

        closedir($handle);
    }
    return $image;
}

function Category__getNestedCategories($root_category = null, $id_lang = false, $active = true, $groups = null, $use_shop_restriction = true, $sql_filter = '', $sql_sort = '', $sql_limit = '')
{
    if (isset($root_category) && !Validate::isInt($root_category)) {
        die(Tools::displayError());
    }

    if (!Validate::isBool($active)) {
        die(Tools::displayError());
    }
    if (isset($groups) && Group::isFeatureActive() && !is_array($groups)) {
        $groups = (array) $groups;
    }
    $cache_id_tmp=(isset($groups) && Group::isFeatureActive() ? implode('', $groups) : '');
    $cache_id = 'Category::getNestedCategories_'
                .md5((int) $root_category . (int) $id_lang . (int) $active . (int) $active .$cache_id_tmp);

    if (!Cache::isStored($cache_id)) {
        $sql = '
            SELECT c.*, cl.*
            FROM `' . _DB_PREFIX_ . 'category` c
            ' . ($use_shop_restriction ? Shop::addSqlAssociation('category', 'c') : '') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`'
              . Shop::addSqlRestrictionOnLang('cl') . '
            ' . (isset($groups) && Group::isFeatureActive() ? 'LEFT JOIN `' . _DB_PREFIX_
              . 'category_group` cg ON c.`id_category` = cg.`id_category`' : '') . '
            ' . (isset($root_category) ? 'RIGHT JOIN `' . _DB_PREFIX_ . 'category` c2 ON c2.`id_category` = '
              . (int) $root_category
              . ' AND c.`nleft` >= c2.`nleft` AND c.`nright` <= c2.`nright`' : '') . '
            WHERE 1 ' . $sql_filter . ' ' . ($id_lang ? 'AND `id_lang` = ' . (int) $id_lang : '') . '
            ' . ($active ? ' AND c.`active` = 1' : '') . '
            ' . (isset($groups) && Group::isFeatureActive() ? ' AND cg.`id_group` IN ('
              . implode(',', $groups) . ')' : '') . '
            ' . (!$id_lang || (isset($groups) && Group::isFeatureActive()) ? ' GROUP BY c.`id_category`' : '') . '
            ' . ($sql_sort != '' ? $sql_sort : ' ORDER BY c.`level_depth` ASC') . '
            ' . ($sql_sort == '' && $use_shop_restriction ? ', category_shop.`position` ASC' : '') . '
            ' . ($sql_limit != '' ? $sql_limit : '');
        $result = Db::getInstance()->executeS($sql);

        $categories = array();
        $buff = array();

        if (!isset($root_category)) {
            $root_category = Category::getRootCategory()->id;
        }

        foreach ($result as $row) {
            $current = &$buff[$row['id_category']];
            $current = $row;

            if ($row['id_category'] == $root_category) {
                $categories[$row['id_category']] = &$current;
            } else {
                $buff[$row['id_parent']]['children'][$row['id_category']] = &$current;
            }
        }

        Cache::store($cache_id, $categories);
    }

    return Cache::retrieve($cache_id);
}
function array_map_string($v)
{
    return pSQL($v);
}
