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

require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');

class AmazonFBAManager extends Amazon
{
    public static $warnings = array();
    public static $log      = array();
    private $_debug   = false;

    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $employee = null;
            $id_employee = Configuration::get('AMAZON_EMPLOYEE');

            if ($id_employee) {
                $employee = new Employee($id_employee);
            }

            if (!Validate::isLoadedObject($employee)) {
                die($this->l('Wrong Employee, please save the module configuration'));
            }

            $this->context->customer->is_guest = true;
            $this->context->customer->id_default_group = (int)Configuration::get('AMAZON_CUSTOMER_GROUP');
            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
    }

    public function dispatch()
    {
        switch (Tools::getValue('action')) {
            default:
                $this->manageStocks();
                break;
        }
    }

    public function manageStocks()
    {
        $log = null;

        if (Tools::getValue('debug') == true) {
            $this->_debug = true;
        } else {
            $this->_debug = (bool)Configuration::get('AMAZON_DEBUG_MODE');
        }

        if ($this->_debug) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        $forceUpdate = (bool)Tools::getValue('force', false);
        $anticipate = (bool)Tools::getValue('anticipate', false);
        $days = Tools::getValue('days', 1);
        $ignore_fba_value = (bool)Tools::getValue('ignore-fba-value', false);

        if ($this->_debug) {
            CommonTools::p(sprintf("%s(%d): Parameters", basename(__FILE__), __LINE__));
            CommonTools::p(sprintf("Days - '%s'", $days));
            CommonTools::p(sprintf("Anticipate - '%s'", $anticipate ? 'true' : 'false'));
            CommonTools::p(sprintf("Ignore FBA Value - '%s'", $ignore_fba_value ? 'true' : 'false'));
            CommonTools::p(sprintf("Force Update - '%s'", $forceUpdate ? 'true' : 'false'));
        }


        // Regions
        //
        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketLang2Region = array_flip($marketPlaceRegion);

        // Identify the marketplace's country
        //
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');

        if ((int)Tools::getValue('europe')) {
            $masterMarketplace = AmazonConfiguration::get('MASTER');

            if (isset($marketLang2Region[$masterMarketplace]) && $marketLang2Region[$masterMarketplace]) {
                $id_lang = $marketLang2Region[$masterMarketplace];
            } else {
                die('The module is not yet configured for Europe');
            }

            $targets_id_lang = array();
            $targets_id_lang[$id_lang] = $id_lang; // at least contains the master marketplace

            foreach ($marketPlaceIds as $marketplace_id_lang => $marketPlaceId) {
                if (AmazonTools::isEuropeMarketplaceId($marketPlaceId)) {
                    $targets_id_lang[$marketplace_id_lang] = $marketplace_id_lang;
                }
            }
            $this->europe = 1;
        } else {
            if (!($lang = Tools::getValue('lang'))) {
                die(Tools::displayError('Missing parameter lang'));
            }

            if (!isset($marketLang2Region[$lang]) || empty($marketLang2Region[$lang])) {
                die(Tools::displayError('Wrong parameter lang'));
            }

            $id_lang = (int)$marketLang2Region[$lang];
            $this->europe = false;

            // For outside Europe contains only 1 marketplace
            $targets_id_lang = array();
            $targets_id_lang[$id_lang] = $id_lang; // at least contains the master marketplace
        }

        //  Check Access Tokens
        //
        $tokens = Tools::getValue('cron_token');

        if (!AmazonTools::checkToken($tokens)) {
            die($this->l('Wrong Token'));
        }

        // Init
        //
        $amazon = AmazonTools::selectPlatforms($id_lang, $this->_debug);

        if ($this->_debug) {
            echo nl2br(print_r($amazon['auth'], true).print_r($amazon['params'], true).print_r($amazon['platforms'], true));
        }

        $pass = true;

        if (!($this->_amazonApi = new AmazonWebService($amazon['auth'], $amazon['params'], $amazon['platforms'], $this->_debug))) {
            AmazonFBAManager::$warnings[] = $this->l('Unable to login');
            $pass = false;
        }

        if ($this->amazon_features['demo_mode']) {
            $this->_amazonApi->demo = true;
        }

        /*
          $SKUs = array('0583215013169', '0074427814427');
          $result = $this->_amazonApi->ListInventoryBySKU($SKUs) ; // By SKU
         */
        if ($pass) {
            $result = $this->_amazonApi->ListInventoryByDate(date('Y-m-d', time() - (86400 * $days)));
        } else {
            $result = null;
        }

        /*
          $result['nano1'] = array() ;
          $result['nano1']['SKU'] = 'nano1';
          $result['nano1']['InStockSupplyQuantity'] = '1' ;
          $result['nano1']['TotalSupplyQuantity'] = '0';

          'InStockSupplyQuantity' => int 10 : Expédiable
          'TotalSupplyQuantity' => int 17 : Expédiable + En Transit
         */
        if ($pass && $result && is_array($result) && count($result)) {
            foreach ($result as $SKU => $item) {
                $product = new AmazonProduct($SKU, false, $id_lang);

                if (!Validate::isLoadedObject($product)) {
                    AmazonFBAManager::$warnings[] = sprintf('%s - %s(%s)', $this->l('Unable to find product'), $product->name, $SKU);
                    continue;
                }

                if ($this->_debug) {
                    CommonTools::p(sprintf("%s(%d): Product - '%s'", basename(__FILE__), __LINE__, $product->name));
                    CommonTools::p(get_object_vars($product));
                }

                $options = AmazonProduct::getProductOptions($product->id, $id_lang);

                if ($product->id_product_attribute) {
                    $combination_options = AmazonProduct::getProductOptions($product->id, $id_lang, $product->id_product_attribute);

                    if (is_array($combination_options) && count($combination_options) && max($combination_options)) {
                        $options = $combination_options;
                    }
                }

                if (!is_array($options) && !count($options) && !max($options)) {
                    $options = AmazonProduct::getDefaultOptions();
                }


                if ($this->_debug) {
                    CommonTools::p(sprintf("%s(%d): Product Options - '%s'", basename(__FILE__), __LINE__, $product->name));
                    CommonTools::p($options);
                }

                // ignore fba value if sets
                $options['fba_value'] = max($ignore_fba_value, $options['fba_value']);

                if ($anticipate && isset($item['TotalSupplyQuantity']) && $item['TotalSupplyQuantity'] > $item['InStockSupplyQuantity']) {
                    $quantityConsidered = $item['TotalSupplyQuantity'];
                } else {
                    $quantityConsidered = $item['InStockSupplyQuantity'];
                }

                if ($this->_debug) {
                    CommonTools::p(sprintf("%s(%d): Quantity Considered - '%d'", basename(__FILE__), __LINE__, (int)$quantityConsidered));
                    CommonTools::p(sprintf("%s(%d): FBA - '%s'", basename(__FILE__), __LINE__, $options['fba']));
                    CommonTools::p(sprintf("%s(%d): FBA Value - '%s'", basename(__FILE__), __LINE__, $options['fba_value']));
                    CommonTools::p(sprintf("%s(%d): Ignore FBA Value - '%s'", basename(__FILE__), __LINE__, $ignore_fba_value ? 'true' : 'false'));
                    CommonTools::p(sprintf("%s(%d): Force Update - '%s'", basename(__FILE__), __LINE__, $forceUpdate ? 'true' : 'false'));
                }

                if ($quantityConsidered == 0) {
                    // Became out of stock

                    // Product is set as FBA
                    //
                    if ($options['fba'] || $forceUpdate) {
                        // Turns Product to MFN for all targets marketplaces
                        foreach ($targets_id_lang as $marketplace_id_lang) {
                            AmazonProduct::updateProductOptions($product->id, $marketplace_id_lang, 'fba', 0, $product->id_product_attribute);
                        }

                        // Log the event
                        AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $product->id);

                        AmazonFBAManager::$log[] = $log = sprintf('%s(%s) - %s', $product->name, $SKU, $this->l('Product became out of stock, switching to MFN'));

                        if ($this->_debug) {
                            CommonTools::p(sprintf("%s(%d): Log - '%s'", basename(__FILE__), __LINE__, $log));
                        }
                    }
                } else {
                    // FBA - In Stock

                    // Product is not set as FBA, but Amazon have it in stock
                    //
                    if ((!$options['fba'] || $forceUpdate) && (is_numeric($options['fba_value']) || $ignore_fba_value)) {
                        // Turns Product to AFN for all targets marketplaces
                        foreach ($targets_id_lang as $marketplace_id_lang) {
                            AmazonProduct::updateProductOptions($product->id, $marketplace_id_lang, 'fba', 1, $product->id_product_attribute);
                        }

                        // Log the event
                        AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $product->id);

                        AmazonFBAManager::$log[] = $log = sprintf('%s(%s) - %s', $product->name, $SKU, $this->l('Product in stock (FBA), switching to AFN'));

                        if ($this->_debug) {
                            CommonTools::p(sprintf("%s(%d): Log - '%s'", basename(__FILE__), __LINE__, $log));
                        }
                    } elseif (!$options['fba'] && (!$options['fba_value'] || !$ignore_fba_value)) {
                        foreach ($targets_id_lang as $marketplace_id_lang) {
                            AmazonProduct::updateProductOptions($product->id, $marketplace_id_lang, 'fba', 0, $product->id_product_attribute);
                        }

                        // Log the event
                        AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $product->id);

                        AmazonFBAManager::$log[] = $log = sprintf('%s(%s) - %s', $product->name, $SKU, $this->l('Product in stock (FBA), but not valued for FBA, switching to MFN'));

                        if ($this->_debug) {
                            CommonTools::p(sprintf("%s(%d): Log - '%s'", basename(__FILE__), __LINE__, $log));
                        }
                    }
                }
            }
        }


        if ($this->_debug) {
            CommonTools::p(sprintf("%s(%d): Logs - '%s'", basename(__FILE__), __LINE__, $log));
            CommonTools::p(sprintf("%s(%d): %s", basename(__FILE__), __LINE__, print_r(AmazonFBAManager::$log, true)));
            CommonTools::p(sprintf("%s(%d): Warnings - '%s'", basename(__FILE__), __LINE__, $log));
            CommonTools::p(sprintf("%s(%d): %s", basename(__FILE__), __LINE__, print_r(AmazonFBAManager::$warnings, true)));
        }

        if (count(AmazonFBAManager::$warnings) || count(AmazonFBAManager::$log)) {
            $mailcontent = array();
            $mailcontent['{events}'] = null;
            $mailsend = false;

            if (AmazonFBAManager::$log) {
                $mailcontent['{events}'] = $this->l('Events').": ".nl2br(Amazon::LF);
                $mailsend = (bool)Configuration::get('AMAZON_EMAIL');

                foreach (AmazonFBAManager::$log as $log) {
                    $mailcontent['{events}'] .= $log.nl2br(Amazon::LF);
                }
            }
            $mailcontent['{errors}'] = null;

            if (AmazonFBAManager::$warnings) {
                $mailcontent['{errors}'] = $this->l('Warnings').": ".nl2br(Amazon::LF);

                foreach (AmazonFBAManager::$warnings as $warning) {
                    $mailcontent['{errors}'] .= $warning.nl2br(Amazon::LF);
                }
            }

            if ($mailsend) {
                Mail::Send(
                    $id_lang, // id_lang
                    'fba_stocks', // template
                    $this->l('Amazon FBA: You have new events from your store'), // subject
                    $mailcontent, // templateVars
                    Configuration::get('PS_SHOP_EMAIL'), // to
                    null, // To Name
                    null, // From
                    null, // From Name
                    null, // Attachment
                    null, // SMTP
                    $this->path.'mails/'
                );
            }
        }
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }
}

$amazonFBAManager = new AmazonFBAManager();
$amazonFBAManager->dispatch();
