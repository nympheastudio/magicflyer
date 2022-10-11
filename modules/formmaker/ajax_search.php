<?php
/**
 * Formmaker
 *
 * @category  Module
 * @author    silbersaiten <info@silbersaiten.de>
 * @support   silbersaiten <support@silbersaiten.de>
 * @copyright 2016 silbersaiten
 * @version   1.1.1
 * @link      http://www.silbersaiten.de
 * @license   See joined file licence.txt
 */

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

$query = Tools::getValue('q', false);
if (!$query || $query == '' || Tools::strlen($query) < 1) {
    die();
}

if ($pos = strpos($query, ' (ref:')) {
    $query = Tools::substr($query, 0, $pos);
}

$exclude_ids = Tools::getValue('excludeIds', false);
if ($exclude_ids && $exclude_ids != 'NaN') {
    $exclude_ids = implode(',', array_map('intval', explode(',', $exclude_ids)));
} else {
    $exclude_ids = '';
}

$sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`,
            MAX(image_shop.`id_image`) id_image, il.`legend`, p.`cache_default_attribute`
        FROM `'._DB_PREFIX_.'product` p
            '.Shop::addSqlAssociation('product', 'p').'
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON 
                (pl.id_product = p.id_product
                AND pl.id_lang = '.(int)Context::getContext()->language->id.Shop::addSqlRestrictionOnLang('pl').')
            LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
            Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
            LEFT JOIN `'._DB_PREFIX_.'image_lang` il
            ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)Context::getContext()->language->id.')
            WHERE (pl.name LIKE \'%'.pSQL($query).'%\' OR p.reference LIKE \'%'.pSQL($query).'%\')'.
            (!empty($exclude_ids) ? ' AND p.id_product NOT IN ('.$exclude_ids.') ' : ' ').
            ' GROUP BY p.id_product';

$items = Db::getInstance()->executeS($sql);

if ($items) {
    $results = array();
    foreach ($items as $item) {
        $product = array(
            'id' => (int)$item['id_product'],
            'name' => $item['name'],
            'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
            'image' => str_replace(
                'http://',
                Tools::getShopProtocol(),
                Context::getContext()->link->getImageLink(
                    $item['link_rewrite'],
                    $item['id_image'],
                    (method_exists('ImageType', 'getFormatedName') ? ImageType::getFormatedName('small') : ImageType::getFormattedName('small'))
                )
            ),
        );
        array_push($results, $product);
    }
    $results = array_values($results);
    echo Tools::jsonEncode($results);
} else {
    Tools::jsonEncode(new stdClass);
}
