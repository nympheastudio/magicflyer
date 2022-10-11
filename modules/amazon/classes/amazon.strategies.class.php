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

class AmazonStrategy extends AmazonProduct
{
    public $id_lang;
    public $id_product_attribute = null;

    protected $ASIN = null;

    public function __construct($id, $full = false, $id_lang = false, $reference = 'reference')
    {
        $this->id_lang = $id_lang;

        return (parent::__construct($id, $full, $id_lang, $reference));
    }


    public static function getStrategy($ASIN, $id_lang = null)
    {
        if (AmazonTools::getConditionField()) {
            $condition_sql = 'p.condition, ';
        } else {
            $condition_sql = '"new" as `condition`, ';
        }

        if ($id_lang) {
            $id_lang_sql = ' AND pl.`id_lang`='.(int)$id_lang;
        } else {
            $id_lang_sql = null;
        }

        $sql = '
                SELECT pl.name, p.`reference`, pa.`reference` as `combination_reference`, po.`asin1` as ASIN, pl.`id_lang`, ms.`minimum_price`, ms.`actual_price`, ms.`target_price`, ms.`gap`, if (pa.`weight`, pa.`weight`, p.`weight`) as `weight`, '.$condition_sql.'po.`fba`, po.`shipping`, po.`disable`, p.`id_product`, pa.`id_product_attribute`
                    FROM `'._DB_PREFIX_.'product` p
                        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.id_product = pl.id_product)
                        LEFT JOIN `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_STRATEGIES.'` ms ON (p.id_product = ms.id_product AND ms.id_lang = pl.id_lang)
                        LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.id_product = pa.id_product AND pa.id_product_attribute = ms.id_product_attribute)
                        LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa2 ON (p.id_product = pa2.id_product)
                        LEFT JOIN `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_OPTIONS.'` po on (p.id_product = po.id_product AND po.id_lang = pl.id_lang)
                    WHERE p.active = 1 AND ms.`asin` = "'.pSQL($ASIN).'" '.$id_lang_sql.'
                        GROUP BY p.id_product, pa.id_product_attribute, pl.id_lang
                        HAVING `reference` > "" AND (`disable` != 1 OR `disable` IS NULL OR `disable` = 0)';

        return (Db::getInstance()->executeS($sql));
    }


    public static function getProductStrategyV4($id_product, $id_product_attribute, $id_lang)
    {
        if ($id_product_attribute) {
            $attribute_sql = ' AND `id_product_attribute`='.(int)$id_product_attribute;
        } else {
            $attribute_sql = ' AND (`id_product_attribute` IS NULL OR `id_product_attribute` = 0)';
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_STRATEGIES.'`
                  WHERE `id_product` = '.(int)$id_product.' and `id_lang` = '.(int)$id_lang.$attribute_sql.' LIMIT 1 ; ';

        $result = Db::getInstance()->executeS($sql);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        return ($result);
    }


    public function setIdProductAttribute($id_product_attribute)
    {
        if ((int)$id_product_attribute) {
            $this->id_product_attribute = (int)$id_product_attribute;
        }
    }
}
