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

require_once(dirname(__FILE__).'/env.php');

require_once(dirname(__FILE__).'/../amazon.php');
require_once(dirname(__FILE__).'/../classes/amazon.tools.class.php');

class AmazonAutoUpdate extends Amazon
{
    public function dispatch()
    {
        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        switch (Tools::getValue('action')) {
            case 'add':
                $this->autoUpdate(Amazon::ADD);
                break;
            default:
                $this->autoUpdate(Amazon::UPDATE);
                break;
        }
    }

    public function autoUpdate($action)
    {
        $tokens = Tools::getValue('cron_token');
        $lang = Tools::getValue('lang');
        $force = (bool)Tools::getValue('force');
        $first_time = str_replace('/', '-', AmazonProduct::oldest());

        $marketPlaceRegion = AmazonConfiguration::get('REGION');

        if (!is_array($marketPlaceRegion) || !count($marketPlaceRegion)) {
            die('Module is not configured yet');
        }

        $marketLang2Region = array_flip($marketPlaceRegion);

        if (!isset($marketLang2Region[$lang]) || !$marketLang2Region[$lang]) {
            die('No selected language, nothing to do...');
        }

        $id_lang = $marketLang2Region[$lang];

        if (!AmazonTools::checkToken($tokens)) {
            die('Wrong Token');
        }

        $last_time = Configuration::get('AMAZON_AUTO_UPDATE_DATE');

        if (empty($first_time) || !Validate::isDate($first_time)) {
            die('No Products !');
        }

        if (empty($last_time) || !Validate::isDate($last_time)) {
            $last_time = $first_time;
        } elseif ($force && Validate::isDate($first_time)) {
            $last_time = $first_time;
        }

        CommonTools::p(str_repeat('-', 160));
        CommonTools::p(sprintf("Region: %s", $lang));
        CommonTools::p(sprintf("Oldest Product: %s", $first_time));
        CommonTools::p(sprintf("Last Execution Time: %s", $last_time));

        $items = AmazonProduct::marketplaceGetAllProducts($id_lang, false, $last_time);

        CommonTools::p(str_repeat('-', 160));
        CommonTools::p(sprintf("Items: %d", is_array($items) ? count($items) : 0));

        if (!is_array($items) || !count($items)) {
            die('No Products since: '.$last_time);
        }

        CommonTools::p(str_repeat('-', 160));
        CommonTools::p(sprintf("Updating: %d", is_array($items) ? count($items) : 0));

        $p = 0;

        foreach ($items as $item) {
            $id_product = (int)$item['id_product'];

            $product = new Product($id_product, false, $id_lang);

            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            $has_attributes = $product->hasAttributes();

            if (empty($product->reference) && !$has_attributes) {
                continue;
            }

            if (empty($product->id_manufacturer) || !is_numeric($product->id_manufacturer)) {
                continue;
            }

            CommonTools::p(sprintf("%08d %20.20s  %20.20s  %.80s %s", $id_product, $product->date_add, $product->date_upd, $product->name, $has_attributes && Amazon::$debug_mode ? '(+)' : null));

            AmazonProduct::marketplaceActionSet($action, $id_product);
            $p++;
        }

        CommonTools::p(str_repeat('-', 160));
        CommonTools::p(sprintf("Items Processed: %d", $p));

        Configuration::updateValue('AMAZON_AUTO_UPDATE_DATE', date('Y-m-d H:i:s'));
    }
}

$amazonAutoUpdate = new AmazonAutoUpdate();
$amazonAutoUpdate->dispatch();
