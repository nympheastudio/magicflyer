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
require_once(dirname(__FILE__).'/../classes/amazon.batch.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.carrier.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.tag.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.support.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.specificfield.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.remote_cart.class.php');

require_once(_PS_MODULE_DIR_.'/amazon/validate/Node.php');
require_once(_PS_MODULE_DIR_.'/amazon/validate/XmlDataType.php');
require_once(_PS_MODULE_DIR_.'/amazon/validate/XmlRestriction.php');
require_once(_PS_MODULE_DIR_.'/amazon/validate/AmazonXSD.php'); /*gets code for Amazon XML Schemas*/
require_once(_PS_MODULE_DIR_.'/amazon/validate/AmazonValidator.php');
require_once(_PS_MODULE_DIR_.'/amazon/validate/Tools.php');

if (!isset($start_time)) {
    //TODO: Yes, it is defined, we need it to calculate allowed execution_time
    $start_time = microtime(true);
}//TODO: VALIDATION: start_time from init

/**
 * Class AmazonExportProducts
 */
class AmazonExportProducts extends Amazon
{
    const NOTICE = 1;
    const WARNING = 1;
    const ERROR = 2;

    private $_amazonApi = array();
    private $params     = null;

    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        $this->amazon_features = $this->getAmazonFeatures();
    }

    public function doIt($start_time)
    {
        $cr = nl2br(Amazon::LF); // carriage return

        $current_version = Configuration::get('AMAZON_CURRENT_VERSION', null, 0, 0);

        if (version_compare($current_version, $this->version, '<')) {
            //$this->errorOutput(AmazonSupport::message($this->l('Module version and configuration mismatch, please edit and save your module configuration'), AmazonSupport::TUTORIAL_AFTER_INSTALLATION), self::ERROR);
            //die;
        }

        //
        // Cron Mode
        //
        if (Tools::getValue('cron')) {
            $lang = Tools::getValue('lang');
            $tokens = Tools::getValue('cron_token');


            echo $this->l('Starting Update in WS API/Cron Mode').' - '.date('Y-m-d H:i:s').$cr;

            // Regions
            //
            $marketPlaceRegion = AmazonConfiguration::get('REGION');
            $marketLang2Region = array_flip($marketPlaceRegion);

            if (!isset($marketLang2Region[$lang]) || !$marketLang2Region[$lang]) {
                die($this->l('No selected language, nothing to do...'));
            }

            $id_lang = $marketLang2Region[$lang];

            $cronMode = true;
            $sendToAmazon = true;
        } else {
            //
            // Web Mode
            //

            $importDate = Tools::getValue('importDate');

            echo $this->l('Starting Update in WS API/Web Mode').' - '.date('Y-m-d H:i:s');

            if ($importDate) {
                echo ' - '.$importDate.$cr;
            } else {
                echo $cr;
            }

            $id_lang = (int)Tools::getValue('amazon_lang');

            $tokens = Tools::getValue('amazon_token');

            if (!(int)$id_lang) {
                echo $this->l('No selected language, nothing to do...');
                die;
            }
            $cronMode = false;
            $sendToAmazon = false;
        }

        //
        //
        // Global Parameters
        //
        $this->params = new Params($id_lang);
        $params = &$this->params;
        $params->cronMode = $cronMode;
        $params->sendToAmazon = $sendToAmazon;
        $params->extendedDatas = (int)Tools::getValue('extended-datas') ? true : false;
        $params->relationShipsOnly = (int)Tools::getValue('relations-only') ? true : false;
        $params->xmlOnly = (int)Tools::getValue('xml-only') ? true : false;
        $params->action = Tools::getValue('action');

        $params->start_time = $start_time;
        $params->max_execution_time = max(0, (int)ini_get('max_execution_time'));
        $params->memory_limit = max(0, AmazonTools::convertBytes(ini_get('memory_limit')));
        $params->php_limits = $params->max_execution_time || $params->memory_limit ? true : false;

        // Options
        //
        if ($params->relationShipsOnly) {
            $params->create = true;
        }

        //
        // Attributes & Features
        //
        parent::loadAttributes();
        parent::loadFeatures(false, $params->expert);

        if (isset(self::$attributes[$params->id_lang])) {
            $attributes = self::$attributes[$params->id_lang];
        } else {
            $attributes = array();
        }

        if (isset(self::$features[$params->id_lang])) {
            $features_values = self::$features_values[$params->id_lang];
        } else {
            $features_values = array();
        }

        // Security Check
        //
        if (!AmazonTools::checkToken($tokens)) {
            die('Wrong Token');
        }

        // Init
        //
        $params->platform = AmazonTools::selectPlatform($params->id_lang, Amazon::$debug_mode);

        $params->toCurrency = new Currency(Currency::getIdByIsoCode($params->platform['params']['Currency']));
        $params->fromCurrency = new Currency((int)(Configuration::get('PS_CURRENCY_DEFAULT')));

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $cart = $this->context->cart;
            $cookie = $this->context->cookie;
            $cart->id_currency = $cookie->id_currency = $params->fromCurrency->id;
        } else {
            $employee = null;
            $id_employee = Configuration::get('AMAZON_EMPLOYEE');

            if ($id_employee) {
                $employee = new Employee($id_employee);
            }

            if (!Validate::isLoadedObject($employee)) {
                die($this->l('Wrong Employee, please save the module configuration'));
            }

            $id_customer = (int)Configuration::get('AMAZON_CUSTOMER_ID');

            if ($id_customer) {
                $this->context->customer = new Customer($id_customer);
            }

            if (!Validate::isLoadedObject($this->context->customer)) {
                $this->context->customer = new Customer();
            }

            $this->context->customer->is_guest = true;
            $this->context->customer->id_default_group = $params->id_group;
            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s%s%s', print_r($params->platform['auth'], true), print_r($params->platform['params'], true), print_r($params->platform['platforms'], true)));
        }

        $params->marketplaceID = $params->platform['auth']['MarketplaceID'];
        $params->merchantID = $params->platform['auth']['MerchantID'];
        $params->fullfillmentCenterId = AmazonTools::marketplaceIdToFullfillmentCenterId($params->platform['auth']['MarketplaceID']);

        if (!($this->_amazonApi = new AmazonWebService($params->platform['auth'], $params->platform['params'], null, Amazon::$debug_mode))) {
            CommonTools::p("");
            echo $this->l('Unable to login').$cr;
            CommonTools::d("");
        }

        $batches_times = array();

        if ($params->repricing && !$params->forcePriceFeed) {
            // Repricing Check
            //
            $batches = new AmazonBatches('batch_repricing');
            $last_repricing = $batches->getLastForRegion($params->region);

            if ($last_repricing) {
                $repricingTimeOut = 14400; // 4 hours
                $repricingLastTime = strtotime($last_repricing); // 4 hours
                $repricingElapsed = time() - $repricingLastTime;

                if ($repricingElapsed && $repricingElapsed > $repricingTimeOut) {
                    $repricingElapsedHours = $repricingElapsed / (60 * 60);

                    printf($this->l('Repricing is active but feed has not been sent since %s hours, sending price feed to be safe').$cr, (int)$repricingElapsedHours);
                    $params->repricing = null;
                }
            } else {
                printf($this->l('Repricing is active but has never been performed, sending price feed, even so').$cr);
                $params->repricing = null;
            }

            $batches_types = array('batch_products_cron', 'batch_products', 'batch_offers_cron', 'batch_offers');

            foreach ($batches_types as $batch_key) {
                $batches = new AmazonBatches($batch_key);
                $last_batch_date_time = $batches->getLastForRegion($params->region);

                $timestamp = strtotime($last_batch_date_time);
                if ((int)$timestamp) {
                    $batches_times[] = $timestamp;
                }
            }
        }

        if (Tools::getValue('entire-catalog')) {
            $params->entireCatalog = true;
        }

        if (Tools::getValue('price-feed')) {
            $params->forcePriceFeed = true;
        }

        if ($params->bruteForce) {
            $params->sendActives = false;
            CommonTools::p("Operating in brute force mode!");
        }
        if (!$params->categories || !count($params->categories)) {
            echo $this->l('Categories Settings not saved yet, nothing to do...').$cr;
            die;
        }

        $params->syncBothMode = false;

        switch ($params->synchField) {
            case 'ean13':
                $ps_code = 'ean13';
                $az_code = 'EAN';
                break;
            case 'upc':
                $ps_code = 'upc';
                $az_code = 'UPC';
                break;
            case 'reference':
                $ps_code = 'reference';
                $az_code = 'SKU';
                break;
            case 'both':
            default:
                $ps_code = null;
                $az_code = null;
                $params->syncBothMode = true;
                break;
        }

        if (Amazon::$debug_mode) {
            if ($params->syncBothMode == true) {
                printf($this->l('Prestashop Synchronizing field are: UPC/EAN (both)').$cr);
            } else {
                printf($this->l('Prestashop Synchronizing field: %s / Amazon: %s').$cr, $ps_code, $az_code);
            }
        }

        // Synch with ASIN
        //
        if (Amazon::$debug_mode && $params->asinHasPriority) {
            printf($this->l('But ASIN has the priority !').$cr);
        }

        $productsUpdate = array();
        $productsDelete = array();
        $u = 0;
        $d = 0;
        $duplicate = 0;
        $skipped = 0;
        $relations = 0;
        $history = array();
        $Relationship = array();
        $parsedXSD = array();
        $synchProductsIds = array();
        $warn_browsenode = array();
        $categories = array();
        $fba = false;
        $images_count = 0;
        $has_product_feed = null;
        $has_price_feed = false;
        $has_quantity_feed = false;

        switch ($params->action) {
            case 'update':
                $params->sendToAmazon = true;
                break;
            case 'update-verify':
                $params->sendToAmazon = false;
                break;
            case 'create-verify':
                $params->sendToAmazon = false;
                break;
            case 'create-export':
                $params->sendToAmazon = true;
                break;
            case 'delete-verify':
                if ((bool)Tools::getValue('delete-confirm')) {
                    $params->deleteProducts = true;
                    $params->deleteConfirmed = true;
                    $params->sendToAmazon = false;
                } else {
                    $params->deleteProducts = true;
                    $params->deleteConfirmed = false;
                    $params->sendToAmazon = false;
                }
                if ((bool)Tools::getValue('delete-overrides')) {
                    $params->deleteShippingOverrides = true;
                }
                break;
            case 'delete-export':
                if ((bool)Tools::getValue('delete-confirm')) {
                    $params->deleteProducts = true;
                    $params->deleteConfirmed = true;
                    $params->sendToAmazon = true;
                } else {
                    $params->deleteProducts = true;
                    $params->sendToAmazon = false;
                }
                if ((bool)Tools::getValue('delete-overrides')) {
                    $params->deleteShippingOverrides = true;
                }
                break;
            default:
                $params->sendToAmazon = false;
                break;
        }

        // Creation Mode
        //
        if ($params->create) {
            if (Amazon::$debug_mode) {
                printf('%s:%d - '.$this->l('Starting in creation mode').$cr, basename(__FILE__), __LINE__);
            }

            if (Tools::getValue('limit')) {
                $params->limit = (int)Tools::getValue('limit');
            }

            $this->_amazonApi->setOperationMode(AmazonWebService::OPERATIONS_CREATE);

            // Force Send Title & Description on Creation
            //
            $params->extendedDatas = true;

            if (!is_array($params->profile2category) || !max($params->profile2category)) {
                $message = AmazonSupport::message($this->l('For creation mode you must configure or select at least one profile'), AmazonSupport::FUNCTION_EXPORT_NO_PROFILE);
                $this->errorOutput($message, self::ERROR);
                die;
            }

            if (!AmazonTools::tableExists(_DB_PREFIX_.AmazonValidValues::TABLE)) {
                $this->errorOutput($this->l('It is highly recommended to download valid values table from Tools tab (module configuration) prior to use creation mode'), self::ERROR);
            }
        }

        //  Products Update
        //
        $i = 0;

        // Envoi global ou mode brute
        //

        if ($params->bruteForce || $params->entireCatalog) {
            // Clear Action Table
            //
            if (!$params->create && $params->sendToAmazon) {
                AmazonProduct::marketplaceActionReset($params->id_lang);
            }

            $p = AmazonProduct::marketplaceGetAllProducts($params->id_lang, $params->sendActives, $params->dateSince);
        } else {
            if ($params->deleteProducts) {
                $p = AmazonProduct::marketplaceActionList($params->id_lang, Amazon::REMOVE, $params->limit);
            } elseif ($params->create) {
                $p = AmazonProduct::marketplaceActionList($params->id_lang, Amazon::ADD, $params->limit);
            } else {
                $p = AmazonProduct::marketplaceActionList($params->id_lang, null, $params->limit);
            }
        }
        $params->loop_start_time = microtime(true);

        $parsedXSD = array();

        if (Amazon::$debug_mode) {
            if ($params->priceRulesEnabled) {
                CommonTools::p(sprintf('Price Rules: %s'.Amazon::LF, print_r($params->priceRules, true)));
            } else {
                CommonTools::p("Price Rules: are disabled".Amazon::LF);
            }
        }

        if ($p) {
            foreach ($p as $key => $val) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(str_repeat('-', 160));
                    CommonTools::p("New Product: ".$val['id_product']);
                    CommonTools::p("Memory:".memory_get_usage(true) / (1024 * 1024));
                }

                if ($params->php_limits) {
                    $loop_average = (microtime(true) - $params->loop_start_time) / ($i + 1);
                    $estimated = (($params->loop_start_time - $params->start_time) + $loop_average * $i * 1.3);

                    if ($params->max_execution_time && $estimated >= $params->max_execution_time) {
                        $this->errorOutput(sprintf('%s (%d/%d)', $this->l('PHP max_execution_time was about to be reached, process interrupted.'), $params->max_execution_time, $estimated), self::ERROR);
                        die;
                    }

                    if ($params->memory_limit && memory_get_peak_usage() * 1.3 > $params->memory_limit) {
                        $this->errorOutput(sprintf('%s (%d/%d)', $this->l('PHP memory_limit was about to be reached, process interrupted.'), memory_get_peak_usage(), $params->memory_limit), self::ERROR);
                        die;
                    }
                }

                // Action for this Product
                //
                $action = isset($val['action']) ? $val['action'] : Amazon::ADD;

                $id_product = (int)$val['id_product'];
                $id_product_attribute = isset($val['id_product_attribute']) ? (int)$val['id_product_attribute'] : null;

                // Product
                //
                if (AmazonConfiguration::shopIsFeatureActive() && $params->id_shop) {
                    $details = new Product($id_product, false, $params->id_lang, $params->id_shop);
                } else {
                    $details = new Product($id_product, false, $params->id_lang);
                }
                if (!Validate::isLoadedObject($details)) {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('Product %d is unavailable in this shop', $id_product));
                    }
                    continue;
                }

                // For combination removal
                //
                if ($action == Amazon::REMOVE && $id_product_attribute && AmazonTools::validateSKU($val['sku'])) {
                    $details = new Product();
                    $details->id = $id_product;
                    $details->id_product_attribute = $id_product_attribute;
                    $details->reference = $val['sku'];
                } elseif (!Validate::isLoadedObject($details)) {
                    // For product removal
                    //
                    $details = new Product();
                    $details->id = $id_product;
                } else {
                    if (isset($details->available_for_order) && !$details->available_for_order) {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Product %d is tagged as unavailable for order', $id_product));
                        }
                        $message = AmazonSupport::message(sprintf($this->l('This product is not exported as afterward the order could not be imported.'). ' - id_product: %d', $id_product), AmazonSupport::FUNCTION_IMPORT_INACTIVE_UNAVAILABLE);
                        $this->errorOutput($message, self::WARNING);
                        $details->active = false;
                    }
                }

                // Product Options
                //
                $product_options = AmazonProduct::getProductOptions($id_product, $params->id_lang);

                // Profiles
                //
                $product_data = null;
                $manufacturer = null;

                $profile_name = null;
                $profile_id = null;

                $variant = false;

                $supplier_reference = null;

                $p_name = null;
                $p_universe = null;
                $p_product_type = null;
                $p_browsenode = null;
                $p_shipping_group = null;
                $p_field_size = null;
                $p_field_color = null;
                $p_field_size_type = null;
                $p_field_color_type = null;
                $p_parameters = null;
                $p_category = null;
                $p_extra = null;
                $p_code_exemption = null;
                $p_sku_as_supplier_reference = null;
                $p_sku_as_sup_ref_unconditionnaly = null;
                $p_item_type = null;
                $p_latency = null;
                $p_price_rules = null;
                $p_bullet_point_strategy = null;
                $p_bullet_point_labels = null;
                $product_data = null;
                $skip = false;
                $id_category = null;
                $alternate_id_category = null;
                $p_ptc = null;

                if (is_array($params->profile2category) && isset($params->profile2category[(int)$details->id_category_default])) {
                    $id_category_default_has_profile = true;
                } else {
                    $id_category_default_has_profile = false;
                }
                // switch to the right category
                if ((int)$details->id_category_default && $id_category_default_has_profile) {
                    $product_categories = AmazonProduct::marketplaceGetCategory($id_product);
                    $category_set = is_array($product_categories) && count($product_categories) ? array_merge(array((int)$details->id_category_default), $product_categories) : array((int)$details->id_category_default);
                } else {
                    $category_set = AmazonProduct::marketplaceGetCategory($id_product);
                }

                if (is_array($category_set) && !$id_category_default_has_profile) {
                    $cindex = array_search($details->id_category_default, $category_set);
                    if ($cindex !== false) {
                        unset($category_set[$cindex]);
                    }
                }

                $default_category_matches = is_array($params->categories) && in_array((int)$details->id_category_default, $params->categories);

                if (!$default_category_matches && is_array($category_set) && count($category_set)) {
                    $matching_categories = array_intersect($category_set, $params->categories);

                    if (is_array($matching_categories)) {
                        $id_category = reset($matching_categories);
                    } else {
                        $id_category = reset($category_set);
                    }

                    if (is_array($category_set) && count($category_set) > 1) {
                        if (in_array($id_category, $category_set) && $matching_categories) {
                            $alternate_id_category = $id_category;
                        }

                        if (in_array($details->id_category_default, $category_set) && !$alternate_id_category && $id_category_default_has_profile) {
                            $id_category = (int)$details->id_category_default;
                        } elseif (is_array($params->profile2category) && is_array($params->categories)) {
                            // Product has multiple categories in category selection
                            if (count(array_intersect($category_set, $params->categories)) > 1 && !in_array($details->id_category_default, $category_set)) {
                                if (count(array_unique(array_intersect($category_set, array_keys($params->profile2category)))) > 1) {
                                    $this->errorOutput(sprintf($this->l('Product "%s" has several profiles in several categories !'), $id_product), self::WARNING);
                                }
                            }
                        }
                    }
                } elseif ($details->id_category_default) {
                    $id_category = (int)$details->id_category_default;
                } else {
                    if (Amazon::$debug_mode) {
                        printf('Product has no category: %d'.$cr, $id_product);
                    }
                    continue;
                }

                // Check category is in current shop
                if ((int)$id_category && is_array($params->shop_categories) && count($params->shop_categories) && !isset($params->shop_categories[$id_category])) {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('Category %d is not in the current shop', $id_category, $this->context->shop->id));
                    }
                    continue;
                }

                if ($params->extendedDatas && $id_category) {
                    if (isset($categories[$id_category])) {
                        $category_name = $categories[$id_category];
                    } else {
                        $category = new Category($id_category);
                        $category_name = $category->name[$id_lang];
                        $categories[$id_category] = $category_name;
                    }
                } else {
                    $category_name = null;
                    $category = null;
                }

                if ($id_category && !in_array($id_category, $params->categories) && !$alternate_id_category) {
                    if (Amazon::$debug_mode) {
                        printf('Product is not in selected categories: %d %d'.$cr, $id_product, $id_category);
                    }
                    continue;
                } elseif ($id_category && !in_array($id_category, $params->categories) && $alternate_id_category) {
                    $id_category = $alternate_id_category;
                } elseif ($id_category && !in_array($id_category, $params->categories)) {
                    if (Amazon::$debug_mode) {
                        printf('Product is not in selected categories: %d %d'.$cr, $id_product, $id_category);
                    }
                    continue;
                } elseif (!$id_category) {
                    if (Amazon::$debug_mode) {
                        printf('Product has no matching category: %d'.$cr, $id_product);
                    }
                    continue;
                }

                if (isset($params->profile2category[$id_category])) {
                    if (in_array($id_category, $params->categories)) {
                        $profile_name = $params->profile2category[$id_category];
                        $profile_id = false;

                        if ($params->profile['name']) {
                            foreach ($params->profile['name'] as $profile_id => $profile) {
                                if ($profile == $profile_name) {
                                    break;
                                }
                            }
                        }

                        if ($profile_id !== false && !empty($profile_name)) {
                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('Using profile [%s] ID: %s', $profile_name, $profile_id));
                            }
                        } elseif ($params->create) {
                            $skip = true;
                        }
                    } else {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Profil is not in profiles list [%s] id: %s', $profile_name, $profile_id));
                        }
                        if ($params->create) {
                            $skip = true;
                        }
                    }
                } elseif ($params->create) {
                    $skip = true;
                }

                if (!$skip && $params->create && !$profile_name) {
                    $message = AmazonSupport::message(sprintf($this->l('Your category "%s" doesn\'t have a selected profile. For creation mode, you must select a profile.'), $category->name[$id_lang]), AmazonSupport::FUNCTION_EXPORT_NO_PROFILE);
                    $this->errorOutput($message, self::ERROR);
                    continue;
                } elseif (!$skip && $params->create && (!isset($params->profile['name'][$profile_id]) || !Tools::strlen($params->profile['name'][$profile_id]))) {
                    $this->errorOutput(sprintf($this->l('Invalid or missing profile for category "%s"'), $category->name[$id_lang]), self::ERROR);
                    continue;
                }

                if ($params->create && is_numeric($profile_id)) {
                    if (!isset($params->profile['version4'])) {
                        $this->errorOutput(sprintf($this->l('Wrong profiles version, please save module configuration first'), $profile_name), self::ERROR);
                        die;
                    }
                    if (!isset($params->profile['product_type'][$profile_id]) || !isset($params->profile['product_type'][$profile_id][$id_lang]) || empty($params->profile['product_type'][$profile_id][$id_lang])) {
                        $this->errorOutput(sprintf($this->l('Missing field: "%s", please check your profile configuration').' - "%s"', 'Product Type', $profile_name), self::ERROR);
                        continue;
                    }

                    if (!isset($params->profile['universe'][$profile_id]) || !isset($params->profile['universe'][$profile_id][$id_lang]) || empty($params->profile['universe'][$profile_id][$id_lang])) {
                        $this->errorOutput(sprintf($this->l('Missing field: "%s", please check your profile configuration').' - "%s"', 'Universe', $profile_name), self::ERROR);
                        continue;
                    }
                }

                if (!$skip && $profile_name && is_numeric($profile_id)) {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('Using profile : %s', $profile_name));
                        CommonTools::p("Memory:".memory_get_usage(true) / (1024 * 1024));
                    }

                    $p_product_type = $params->profile['product_type'][$profile_id][$id_lang];
                    $p_name = isset($params->profile['name'][$profile_id][$id_lang]) ? $params->profile['name'][$profile_id][$id_lang] : (isset($params->profile['name'][$profile_id][$this->id_lang]) ? $params->profile['name'][$profile_id][$this->id_lang] : null);
                    $p_universe = $params->profile['universe'][$profile_id][$id_lang];
                    $p_browsenode = isset($params->profile['browsenode'][$profile_id][$id_lang]) ? $params->profile['browsenode'][$profile_id][$id_lang] : null;
                    $p_shipping_group = isset($params->profile['shipping_group'][$profile_id][$id_lang]) ? $params->profile['shipping_group'][$profile_id][$id_lang] : null;
                    $p_key = AmazonTools::toKey($profile_name);

                    $p_parameters = isset($params->profile['parameters'][$profile_id][$id_lang]) ? $params->profile['parameters'][$profile_id][$id_lang] : array();
                    $p_category = isset($params->profile['category'][$profile_id][$id_lang]) ? $params->profile['category'][$profile_id][$id_lang] : null;
                    $p_code_exemption = isset($params->profile['code_exemption'][$profile_id][$id_lang]) && $params->profile['code_exemption'][$profile_id][$id_lang] ? $params->profile['code_exemption'][$profile_id][$id_lang] : false;
                    $p_code_exemption_options = isset($params->profile['code_exemption_options'][$profile_id][$id_lang]) && (is_array($params->profile['code_exemption_options'][$profile_id][$id_lang]) || is_numeric($params->profile['code_exemption_options'][$profile_id][$id_lang])) ? $params->profile['code_exemption_options'][$profile_id][$id_lang] : false;
                    $p_latency = isset($params->profile['latency'][$profile_id][$id_lang]) && (int)$params->profile['latency'][$profile_id][$id_lang] ? (int)$params->profile['latency'][$profile_id][$id_lang] : null;
                    $p_sku_as_supplier_reference = isset($params->profile['sku_as_supplier_reference'][$profile_id][$id_lang]) && $params->profile['sku_as_supplier_reference'][$profile_id][$id_lang] ? true : false;
                    $p_sku_as_sup_ref_unconditionnaly = isset($params->profile['sku_as_sup_ref_unconditionnaly'][$profile_id][$id_lang]) && $params->profile['sku_as_sup_ref_unconditionnaly'][$profile_id][$id_lang] ? true : false;
                    $p_item_type = isset($params->profile['item_type'][$profile_id][$id_lang]) ? $params->profile['item_type'][$profile_id][$id_lang] : null;
                    $p_price_rules = isset($params->profile['price_rule'][$profile_id][$id_lang]) && (int)$params->profile['price_rule'][$profile_id][$id_lang] ? $params->profile['price_rule'][$profile_id][$id_lang] : null;
                    $p_bullet_point_strategy = isset($params->profile['bullet_point_strategy'][$profile_id][$id_lang]) && (int)$params->profile['bullet_point_strategy'][$profile_id][$id_lang] ? $params->profile['bullet_point_strategy'][$profile_id][$id_lang] : null;
                    $p_bullet_point_labels = isset($params->profile['bullet_point_labels'][$profile_id][$id_lang]) && (int)$params->profile['bullet_point_labels'][$profile_id][$id_lang] ? $params->profile['bullet_point_labels'][$profile_id][$id_lang] : null;
                    $p_repricing = isset($params->profile['repricing'][$profile_id][$id_lang]) && !empty($params->profile['repricing'][$profile_id][$id_lang]) ? $params->profile['repricing'][$profile_id][$id_lang] : null;
                    $p_ptc = isset($params->profile['ptc'][$profile_id][$id_lang]) && !empty($params->profile['ptc'][$profile_id][$id_lang]) ? $params->profile['ptc'][$profile_id][$id_lang] : null;

                    if (empty($p_browsenode) && !isset($warn_browsenode[$profile_id][$id_lang])) {
                        $warn_browsenode[$profile_id][$id_lang] = true;
                        $message = AmazonSupport::message(sprintf($this->l('Browsenode is missing for Profile "%s" - this value is highly recommended'), $profile_name), AmazonSupport::FUNCTION_EXPORT_NO_BROWSENODE);
                        $this->errorOutput($message, self::WARNING);
                    }
                }

                if (!$skip && $params->extendedDatas && Tools::strlen($profile_name)) {
                    // Load XSD into an array - will be used to parse sort the XML as defined in the XSD
                    //
                    $xsdStructure = array();

                    if ($p_category && !isset($parsedXSD[$p_category])) {
                        try {
                            $productFactory = new AmazonXSD($p_category.'.xsd');
                            $productInstance = $productFactory->getInstance();
                        } catch (Exception $e) {
                            die($this->l('Amazon XSD Exception :').$e->getMessage());
                        }

                        $parsedXSD[$p_category] = $productFactory->getInstanceElementsArray($xsdStructure, $productInstance->DescriptionData); // Added for additionnal DescriptionData (for optional fields)
                        $parsedXSD[$p_category] += $productFactory->getInstanceElementsArray($xsdStructure, $productInstance->ProductData);
                        $p_parameters['xsd'] = &$parsedXSD[$p_category];

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Creation mode: parsing XSD for category : %s', $p_category));
                            CommonTools::p("Memory:".memory_get_usage(true) / (1024 * 1024));
                        }
                    } elseif ($p_category && isset($parsedXSD[$p_category])) {
                        $p_parameters['xsd'] = &$parsedXSD[$p_category];

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Creation mode: using parsed XSD for category : %s', $p_category));
                            CommonTools::p("Memory:".memory_get_usage(true) / (1024 * 1024));
                        }
                    } else {
                        $this->errorOutput(sprintf('Unable to parse XSD definitions for profile %s'.$cr, $profile_name), self::ERROR);
                        continue;
                    }

                    if (isset($params->profile['extra'][$p_key]) && is_array($params->profile['extra'][$p_key]) && isset($params->profile['extra'][$p_key][$id_lang])
                        && is_array($params->profile['extra'][$p_key][$id_lang])
                        && count($params->profile['extra'][$p_key][$id_lang])) {
                        $p_extra = $params->profile['extra'][$p_key][$id_lang];
                    } else {
                        $p_extra = null;
                    }

                    if (!$p_product_type) {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Skipped: product has no product type - %s', $id_product));
                        }
                        $skip = true;
                    }
                } elseif ($params->create) {
                    $skip = true;
                }

                if ($skip) {
                    // mode creation - we filter the product which not have a profile

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('Skipped: product has no or wrong profile - %s', $id_product));
                    }
                    continue;
                }

                $manufacturer = null;

                // Fields required for product sheet creation
                //
                if ($details->id_manufacturer) {
                    if ($params->filters && in_array($details->id_manufacturer, $params->excludedManufacturers)) {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Skipped: product is in manufacturers exclude list - %s', $details->id_manufacturer));
                        }
                        continue;
                    }

                    if ($params->extendedDatas) {
                        $manufacturer = Manufacturer::getNameById($details->id_manufacturer);
                    }
                }

                if ($params->extendedDatas && !Tools::strlen($manufacturer)) {
                    $message = AmazonSupport::message(sprintf($this->l('Skipping : %d - Field Manufacturer/Brand is mandatory'), $details->id), AmazonSupport::FUNCTION_EXPORT_NO_BRAND);
                    $this->errorOutput($message, self::ERROR);
                    $skipped++;
                    continue;
                }


                if ($params->extendedDatas && version_compare(_PS_VERSION_, '1.5', '>=')) {
                    $supplier_reference = ProductSupplier::getProductSupplierReference($id_product, $id_product_attribute, $details->id_supplier);
                } elseif ($params->extendedDatas) {
                    $supplier_reference = $details->supplier_reference;
                }

                if ($details->id_supplier) {
                    if ($params->filters && in_array($details->id_supplier, $params->excludedSuppliers)) {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Skipped: product is in suppliers exclude list - %s', $details->id_supplier));
                        }
                        continue;
                    }
                }

                // Product Combinations
                //
                $combinations = null;

                if ($params->combinations) {
                    if (version_compare(_PS_VERSION_, '1.5', '<') && !$id_product_attribute) {
                        //
                        $combinations = $details->getAttributeCombinaisons($params->id_lang);
                    } elseif (!$id_product_attribute) {
                        //
                        $combinations = $details->getAttributeCombinations($params->id_lang);
                    }
                }

                if (isset($val['sku']) && !empty($val['sku'])) {
                    //
                    $reference = $val['sku'];
                } else {
                    //
                    $reference = $details->reference;
                }

                if (!isset($combinations) or !is_array($combinations) or empty($combinations)) {
                    $combinations = array(
                        0 => array(
                            'reference' => $reference,
                            'price' => 0,
                            'quantity' => isset($details->quantity) ? $details->quantity : 0,
                            'ean13' => $details->ean13,
                            'upc' => $details->upc,
                            'id_product_attribute' => 0,
                            'minimal_quantity' => $details->minimal_quantity,
                            'meta_title' => $details->meta_title,
                            'unity' => $details->unity,
                            'weight' => 0,
                            'meta_description' => $details->meta_description,
                            'available_date' => isset($details->available_date) ? $details->available_date : null,
                            'default_on' => 0
                        )
                    );
                    $hasCombinations = false;
                } else {
                    $hasCombinations = true;
                }

                $product_features = array();

                if (!AmazonConfiguration::featureIsFeatureActive()) {
                    $product_features = $details->getFeatures();
                } elseif (AmazonConfiguration::featureIsFeatureActive()) {
                    $product_features = $details->getFeatures();
                }

                $group_details = array();
                $color = $size = null;

                asort($combinations);

                $skip_product = false; // to skip combination with duplicate parent/child references
                $overall_quantities = array();
                $overall_quantities[$reference] = 0;

                // Grouping Combinations
                //
                foreach ($combinations as $combination) {
                    $id_product_attribute = $combination['id_product_attribute'];

                    if ($skip_product) {
                        continue;
                    }

                    if (!isset($group_details[$id_product_attribute])) {
                        $group_details[$id_product_attribute] = array();
                    }

                    if ($id_product_attribute && !empty($details->reference) && $combination['reference'] == $details->reference) {
                        $message = AmazonSupport::message(sprintf($this->l('Duplicate entry for product %s(%d/%d) - Previously used by: %s - This is not allowed - Product skipped...'), $combination['reference'], $id_product, $id_product_attribute, sprintf('%d/%d', $id_product, 0)), AmazonSupport::FUNCTION_EXPORT_DUPLICATE);
                        $this->errorOutput($message, self::WARNING);
                        $skip_product = true;
                        continue;
                    }

                    $group_details[$id_product_attribute]['reference'] = $combination['reference'];

                    // Attributes
                    //
                    $id_attribute_group = isset($combination['id_attribute_group']) ? $combination['id_attribute_group'] : null;
                    $id_attribute = isset($combination['id_attribute']) ? $combination['id_attribute'] : null;

                    if ($id_attribute) {
                        $group_details[$id_product_attribute]['attributes'][$id_attribute_group][$id_attribute] = array(
                            'name' => $combination['attribute_name'],
                            'group' => $combination['group_name']
                        );
                    }

                    if (!isset($group_details[$id_product_attribute]['attribute_name'])) {
                        $group_details[$id_product_attribute]['attribute_name'] = null;
                    }

                    if (isset($combination['attribute_name']) && !empty($combination['attribute_name'])) {
                        $group_details[$id_product_attribute]['attribute_name'] .= sprintf('%s, ', $combination['attribute_name']);
                    }

                    $group_details[$id_product_attribute]['quantity'] = $combination['quantity'];
                    $group_details[$id_product_attribute]['minimal_quantity'] = isset($combination['minimal_quantity']) ? $combination['minimal_quantity'] : $details->minimal_quantity;
                    $group_details[$id_product_attribute]['meta_title'] = isset($combination['meta_title']) ? $combination['meta_title'] : $details->meta_title;
                    $group_details[$id_product_attribute]['meta_description'] = isset($combination['meta_description']) ? $combination['meta_description'] : $details->meta_description;
                    $group_details[$id_product_attribute]['available_date'] = isset($combination['available_date']) ? $combination['available_date'] : $details->available_date;
                    $group_details[$id_product_attribute]['default_on'] = $combination['default_on'];
                    $group_details[$id_product_attribute]['unity'] = $details->unity;
                    $group_details[$id_product_attribute]['weight'] = $combination['weight'];

                    // Synch Field (EAN, UPC, SKU ...)
                    //

                    // Both Synch Mode (EAN/UPC)
                    //
                    if ($params->syncBothMode) {
                        $az_code = 'EAN';
                        $ps_code = 'ean13';

                        if (isset($combination['ean13']) && !empty($combination['ean13'])) {
                            $az_code = 'EAN';
                            $ps_code = 'ean13';
                        } elseif (isset($combination['upc']) && !empty($combination['upc'])) {
                            $az_code = 'UPC';
                            $ps_code = 'upc';
                        }
                    }

                    $group_details[$id_product_attribute][$ps_code] = $combination[$ps_code];
                }
                if ($skip_product) {
                    continue;
                }

                $masterProduct = 0;
                $productIndex = 0;

                $details->name_attributes = null;

                $skip_product = false;

                // Export Combinations or Products Alone
                //
                foreach ($group_details as $id_product_attribute => $combination) {
                    if ($skip_product) {
                        continue;
                    }
                    $product_weight = $combination['weight'] = $details->weight + $combination['weight'];

                    if (Amazon::$debug_mode) {
                        CommonTools::p($combination);
                        CommonTools::p("Memory:".memory_get_usage(true) / (1024 * 1024));
                    }

                    if ($id_product_attribute && !$productIndex) {
                        $masterProduct = 1;
                    } else {
                        $masterProduct = 0;
                    }

                    $productIndex++;

                    // Both Synch Mode (EAN/UPC)
                    //
                    if ($params->syncBothMode) {
                        $az_code = 'EAN';
                        $ps_code = 'ean13';

                        if (isset($combination['ean13']) && !empty($combination['ean13'])) {
                            $az_code = 'EAN';
                            $ps_code = 'ean13';
                        } elseif (isset($combination['upc']) && !empty($combination['upc'])) {
                            $az_code = 'UPC';
                            $ps_code = 'upc';
                        }
                    }
                    $product_data = array();
                    $removeTagForParent = array();
                    $removeTagForParent[] = 'SpecialFeature';

                    // Product Options or Combinations Options

                    $options = $product_options;

                    if ($id_product_attribute) {
                        $combination_options = AmazonProduct::getProductOptions($id_product, $params->id_lang, $id_product_attribute);

                        if (is_array($combination_options) && count($combination_options) && max($combination_options)) {
                            $options = $combination_options;
                        }
                    }

                    if (in_array($p_universe, array('Jewelry')) && !$p_code_exemption) {
                        $p_code_exemption = Amazon::EXEMPTION_MFR_PART_NUMBER;
                    }


                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('Product Options: %s', print_r($options, true)));
                        CommonTools::p("Memory:".memory_get_usage(true) / (1024 * 1024));
                    }

                    // Synchronizing by ASIN (ASIN override)
                    //
                    if ($params->asinHasPriority && isset($options['asin1']) && !empty($options['asin1'])) {
                        $params->asinOverride = true;
                    } else {
                        $params->asinOverride = false;
                    }

                    if (Amazon::$debug_mode && $params->asinOverride) {
                        CommonTools::p("Using ASIN override mode");
                    }

                    if ($details->name) {
                        printf($cr.'%s - ID: %d/%d - SKU: %s - %s: %s'.$cr, $details->name, $id_product, $id_product_attribute, isset($combination['reference']) ? $combination['reference'] : '#?#', $az_code, isset($combination[$ps_code]) && !empty($combination[$ps_code]) ? $combination[$ps_code] : '#?#');

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Name: %s', $details->name));
                            CommonTools::p(sprintf('Product ID: %d/%d', $id_product, $id_product_attribute));
                            CommonTools::p(sprintf('Reference: %s', $combination['reference']));
                            CommonTools::p(sprintf('Using: %s/%s', $az_code, $ps_code));
                            CommonTools::p(sprintf('Code: %s', isset($combination[$ps_code]) && !empty($combination[$ps_code]) ? $combination[$ps_code] : '#?#'));
                        }
                    }

                    if ($action != Amazon::REMOVE && isset($combination['reference']) && !empty($combination['reference']) && isset($history[$combination['reference']])) {
                        $message = AmazonSupport::message(sprintf($this->l('Duplicate entry for product %s(%d/%d) - Previously used by: %s - This is not allowed - Product skipped...'), $combination['reference'], $id_product, $id_product_attribute, $history[$combination['reference']]), AmazonSupport::FUNCTION_EXPORT_DUPLICATE);
                        $this->errorOutput($message, self::WARNING);
                        $productIndex--;
                        if ($masterProduct) {
                            $skip_product = true;
                        }
                        $skipped++;
                        continue;
                    }
                    $history[$combination['reference']] = sprintf('%d/%d', $details->id, $id_product_attribute);

                    if (empty($combination['reference']) && $ps_code != 'reference') {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s:%s - ', basename(__FILE__), __LINE__));
                        }

                        $message = AmazonSupport::message(sprintf($this->l('Skipping Product ID: %d/%d - Has No Reference'), $details->id, $id_product_attribute), AmazonSupport::FUNCTION_EXPORT_NOSKU);
                        $this->errorOutput($message, self::WARNING);

                        if ($masterProduct) {
                            $skip_product = true;
                        }

                        $productIndex--;
                        $skipped++;
                        continue;
                    }

                    if (!AmazonTools::validateSKU($combination['reference'])) {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s:%s - ', basename(__FILE__), __LINE__));
                        }

                        $message = AmazonSupport::message(sprintf($this->l('Skipping Product ID: %d/%d - Reference "%s" is invalid'), $details->id, $id_product_attribute, $combination['reference']), AmazonSupport::FUNCTION_EXPORT_INVALID_SKU);
                        $this->errorOutput($message, self::WARNING);

                        if ($masterProduct) {
                            $skip_product = true;
                        }

                        $productIndex--;
                        $skipped++;
                        continue;
                    }

                    if (empty($combination[$ps_code]) && !$params->asinOverride && $action != Amazon::REMOVE && $ps_code != 'reference' && !$p_code_exemption && !$masterProduct) {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s:%s - ', basename(__FILE__), __LINE__));
                        }

                        $message = AmazonSupport::message(sprintf($this->l('Skipping Product ID: %d/%d - Has No %s'), $details->id, $id_product_attribute, Tools::strtoupper($ps_code)), AmazonSupport::FUNCTION_EXPORT_NOCODE);
                        $this->errorOutput($message, self::WARNING);

                        $productIndex--;
                        $skipped++;
                        continue;
                    }

                    if ($params->synchField != 'reference') {
                        $EAN_UPC = isset($combination[$ps_code]) && !empty($combination[$ps_code]) ? $combination[$ps_code] : (isset($details->$ps_code) && !empty($details->$ps_code) ? $details->$ps_code : null);

                        $EAN_UPC = trim($EAN_UPC);

                        if (!$EAN_UPC && !$p_code_exemption && $p_code_exemption != Amazon::EXEMPTION_ATTR_EAN && $p_code_exemption != Amazon::EXEMPTION_MFR_PART_NUMBER) {
                            if ($action != Amazon::REMOVE) {
                                $this->errorOutput(sprintf('Skipping product %s which has no %s', $id_product, $ps_code), self::WARNING);
                            }

                            $productIndex--;
                            $skipped++;
                            continue;
                        } elseif (!$EAN_UPC && $p_code_exemption && $p_code_exemption != Amazon::EXEMPTION_ATTR_EAN) {
                            $EAN_UPC = null;
                        }

                        $is_private = AmazonTools::eanUpcisPrivate($EAN_UPC);

                        // Check if the code is a "reserved for local use" code.
                        //
                        if ($is_private && !$p_code_exemption) {
                            $this->errorOutput(sprintf($this->l('Inconsistency for product %s(%d/%d) - Product code %s(%s) is reserved for local use - Skipping product'), $combination['reference'], $id_product, $id_product_attribute, $ps_code, $EAN_UPC), self::ERROR);
                            $productIndex--;
                            $skipped++;
                            continue;
                        } elseif ($is_private && $p_code_exemption && $p_code_exemption != Amazon::EXEMPTION_ATTR_EAN) {
                            $EAN_UPC = null;
                        }


                        $is_wrong_code = !AmazonTools::eanUpcCheck($EAN_UPC);

                        // Check EAN/UPC consistency
                        //
                        if ($is_wrong_code && !$p_code_exemption) {
                            $this->errorOutput(sprintf($this->l('Inconsistency for product %s(%d/%d) - Product code %s(%s) seems to be wrong - Skipping product'), $combination['reference'], $id_product, $id_product_attribute, $ps_code, $EAN_UPC), self::ERROR);
                            $productIndex--;
                            $skipped++;
                            continue;
                        } elseif ($is_wrong_code && $p_code_exemption && $p_code_exemption != Amazon::EXEMPTION_ATTR_EAN) {
                            $EAN_UPC = null;
                        }
                    }

                    if (is_numeric($profile_id)) {
                        //
                        // Product Has Features and one of those could be the color or size
                        //
                        if (is_array($product_features) && count($product_features)) {
                            if (Amazon::$debug_mode) {
                                CommonTools::p("Product Features");
                                CommonTools::p($product_features);
                            }
                            $combination['features'] = array();

                            foreach ($product_features as $feature) {
                                $id_feature = (int)$feature['id_feature'];
                                $id_feature_value = (int)$feature['id_feature_value'];

                                // Is a custom value, load custom value
                                if (isset($feature['custom']) && (bool)$feature['custom'] && !isset($features_values[$id_feature][$id_feature_value])) {
                                    $custom_features_values = FeatureValue::getFeatureValuesWithLang($params->id_lang, $id_feature, true);

                                    foreach ($custom_features_values as $custom_feature_value) {
                                        if ($custom_feature_value['id_feature_value'] != $id_feature_value) {
                                            continue;
                                        }

                                        $has_custom_value = is_array($features_values) && isset($features_values[$id_feature]) && is_array($features_values) && isset($features_values[$id_feature][$id_feature_value]);

                                        if ($has_custom_value && isset($features_values[$id_feature][$id_feature_value]['name'])) {
                                            $custom_feature_value['name'] = $features_values[$id_feature][$id_feature_value]['name'];
                                            $features_values[$id_feature][$id_feature_value] = $custom_feature_value;
                                        }
                                        break;
                                    }
                                }
                                if (isset($features_values[$id_feature]) && isset($features_values[$id_feature][$id_feature_value])) {
                                    $combination['features'][$id_feature] = &$features_values[$id_feature][$id_feature_value];
                                }
                            }

                            if (Amazon::$debug_mode && isset($combination['features'])) {
                                CommonTools::p("Combination Features");
                                CommonTools::p($combination['features']);
                            }
                        }
                    }

                    if (isset($combination['attribute_name'])) {
                        $details->name_attributes = $combination['attribute_name'];
                    }

                    if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                        $quantity = Product::getRealQuantity($details->id, $id_product_attribute ? $id_product_attribute : null, $params->id_warehouse);
                    } else {
                        $quantity = Product::getQuantity($details->id, $id_product_attribute ? $id_product_attribute : null);
                    }

                    if ((int)$params->outOfStock) {
                        $quantity -= $params->outOfStock;
                    }

                    if ($params->remote_cart) {
                        $remote_cart_quantity = AmazonRemoteCart::getQuantities($combination['reference']);
                    } else {
                        $remote_cart_quantity = 0;
                    }

                    $product_data['ProductImage'] = array();

                    // Send Images
                    //
                    if ($params->images) {
                        if (!Amazon::ENABLE_EXPERIMENTAL_FEATURES && ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') && !$params->xmlOnly) {
                            $this->errorOutput(sprintf('Warning : %s'."$cr", $this->l('Unable to send images from localhost')));
                            $params->images = null;
                        } else {
                            $imageIndex = 1;

                            foreach (AmazonTools::getProductImages($id_product, $id_product_attribute, $id_lang, $this->context) as $image) {
                                $file_image = _PS_PROD_IMG_DIR_ . $image ;

                                if (! file_exists($file_image)) {
                                    $this->errorOutput(sprintf('%s(#%d):' . $this->l('Unable to find image %s in %s'), basename(__FILE__), __LINE__, $image, _PS_PROD_IMG_DIR_), self::WARNING);
                                    continue;
                                }

                                $product_data['ProductImage'][] = $params->images_url.$image;
                                if ($imageIndex++ > 8) {
                                    break;
                                }
                            }

                            $main_image = reset($product_data['ProductImage']);

                            if (Tools::strlen($main_image)) {
                                $infos = getimagesize($file_image);

                                if ($infos && is_array($infos) && count($infos)) {
                                    $width = reset($infos);
                                    if ($width >= Amazon::RECOMMENDED_IMAGE_SIZE) {
                                        $product_data['EnhancedImageURL'] = $main_image;
                                    }
                                }
                            }

                            if ($product_data['ProductImage']) {
                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('Products Images: %s', print_r($product_data['ProductImage'], true)));
                                }
                            }
                        }
                    }

                    // Get price only if the product is not to be removed from Amazon
                    if (Validate::isLoadedObject($details)) {
                        $stdPrice = $details->getPrice($params->useTax, ($id_product_attribute ? $id_product_attribute : null), 6, null, false, !$details->on_sale && $params->specials);

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Regular Price: %s', $stdPrice));
                            CommonTools::p(sprintf('Tax applied: %s', $params->useTax ? 'Yes' : 'No'));
                        }

                        // Price Filter
                        //
                        if ($params->filters && $params->priceFilter && isset($params->priceFilter['gt']) && (int)$params->priceFilter['gt'] && (float)$stdPrice > (float)$params->priceFilter['gt']) {
                            $this->errorOutput(sprintf($this->l('Skipping filtered product: price %.2f > %.2f'), $stdPrice, $params->priceFilter['gt']), self::WARNING);
                            continue;
                        } elseif ($params->filters && $params->priceFilter && isset($params->priceFilter['lt']) && (int)$params->priceFilter['lt'] && (float)$stdPrice < (float)$params->priceFilter['lt']) {
                            $this->errorOutput(sprintf($this->l('Skipping filtered product: price %.2f < %.2f'), $stdPrice, $params->priceFilter['lt']), self::WARNING);
                            continue;
                        }


                        if ($params->priceRulesEnabled) {
                            $newPrice = $this->getPriceWithRule($p_price_rules, $params->priceRules, $stdPrice);
                        } else {
                            $newPrice = $stdPrice;
                        }

                        $sales = array();
                        $specificPrice = null;
                        $business_params = array();

                        if ($params->id_business_group) {
                            $business_price = SpecificPrice::getSpecificPrice($id_product, $params->id_shop, $params->fromCurrency->id, $params->id_country_default, $params->id_business_group, 1, $id_product_attribute);

                            if (is_array($business_price) && count($business_price)) {
                                if ($params->priceRulesEnabled) {
                                    $business_price = $this->getPriceWithRule($p_price_rules, $params->priceRules, $business_price['price']);
                                }

                                $business_params['BusinessPrice'] = $business_price;
                            }

                            $business_rules = AmazonProduct::getBusinessPriceRulesBreakdown($id_product, $id_product_attribute, $params->id_shop, $params->fromCurrency->id, $params->id_country_default, $params->id_business_group);

                            if (is_array($business_rules) && count($business_rules)) {
                                $id_rule = 1;
                                $percentage = null;
                                foreach ($business_rules as $business_rule) {
                                    if ($percentage === null) {
                                        if ($business_rule['reduction_type'] == 'percentage') {
                                            $business_params['QuantityPriceType'] = 'percent';
                                            $percentage = true;
                                        } else {
                                            $business_params['QuantityPriceType'] = 'fixed';
                                            $percentage = false;
                                        }
                                    }
                                    if (!is_numeric($business_rule['reduction'])) {
                                        continue;
                                    }
                                    $business_params['rules']['QuantityPrice'.$id_rule] = $percentage ? (int)($business_rule['reduction'] * 100)  : sprintf('%.02f', $business_rule['reduction']) ;
                                    $business_params['rules']['QuantityLowerBound'.$id_rule] = $business_rule['from_quantity'];
                                    $id_rule++;
                                }
                            }
                            if (!$business_price) {
                                $business_params = array();
                            }
                        }

                        // Apply Sales for PS > 1.4
                        //
                        if ($params->specials) {
                            $specificPrice = SpecificPrice::getSpecificPrice($id_product, $params->id_shop, $params->fromCurrency->id, $params->id_country_default, $params->id_group, 1, $id_product_attribute, 0, 0, 1);

                            // Sales
                            //
                            if ($details->on_sale && $specificPrice && isset($specificPrice['reduction_type']) && isset($specificPrice['from']) && isset($specificPrice['to']) && (int)$specificPrice['from'] && (int)$specificPrice['to']) {
                                // ISO 8601
                                $dateStart = date('c', strtotime($specificPrice['from']));
                                $dateEnd = date('c', strtotime($specificPrice['to']));

                                $salePrice = $details->getPrice($params->useTax, ($id_product_attribute ? $id_product_attribute : null), 6, null, false, $params->specials);

                                if ($params->priceRulesEnabled) {
                                    $salePrice = $this->getPriceWithRule($p_price_rules, $params->priceRules, $salePrice);
                                }
                                if ($params->rounding && in_array($params->rounding, array(Amazon::ROUNDING_ONE_DIGIT, Amazon::ROUNDING_TWO_DIGITS))) {
                                    $salePrice = Tools::ps_round($salePrice, $params->rounding);
                                } elseif ($params->rounding && $params->rounding == Amazon::ROUNDING_SMART) {
                                    $salePrice = AmazonTools::smartRounding($salePrice);
                                } else {
                                    $salePrice = Tools::ps_round($salePrice, 2);
                                }


                                printf('%s : %s - %s : %s %s %s %s'.$cr, $this->l('Apply Sale Price'), $dateStart, $dateEnd, $this->l('Old Price'), Tools::displayPrice($stdPrice, $params->toCurrency), $this->l('Sale Price'), $salePrice);

                                // Convert to platform currency
                                //
                                if ($params->fromCurrency->iso_code != $params->toCurrency->iso_code) {
                                    $oldSalePrice = $salePrice;
                                    $salePrice = Tools::convertPrice($salePrice, $params->toCurrency);

                                    if ($params->rounding && in_array($params->rounding, array(Amazon::ROUNDING_ONE_DIGIT, Amazon::ROUNDING_TWO_DIGITS))) {
                                        $salePrice = Tools::ps_round($salePrice, $params->rounding);
                                    } elseif ($params->rounding && $params->rounding == Amazon::ROUNDING_SMART) {
                                        $salePrice = AmazonTools::smartRounding($salePrice);
                                    } else {
                                        $salePrice = Tools::ps_round($salePrice, 2);
                                    }

                                    if (Amazon::$debug_mode) {
                                        CommonTools::p(sprintf('Convert sale price from currency: %s to currency: %s - price is: %s and was %s', $params->fromCurrency->iso_code, $params->toCurrency->iso_code, $salePrice, $oldSalePrice));
                                    }
                                }


                                $sales['dateStart'] = $dateStart;
                                $sales['dateEnd'] = $dateEnd;
                                $sales['salePrice'] = $salePrice;

                                /* For future use
                                if ($salePrice) {
                                    //2016-02-28 newTags: PromoTag  -  EffectiveFromDate - EffectiveThroughDate;
                                    $product_data['PromoTag'] = 'Sale';
                                    $product_data['EffectiveFromDate'] =  gmdate('Y-m-d', strtotime($specificPrice['from']));
                                    $product_data['EffectiveThroughDate'] = gmdate('Y-m-d', strtotime($specificPrice['to']));;

                                    $removeTagForParent[] = 'PromoTag';
                                    $removeTagForParent[] = 'EffectiveFromDate';
                                    $removeTagForParent[] = 'EffectiveThroughDate';
                                }
                                */
                            }
                        }

                        if (Amazon::$debug_mode) {
                            CommonTools::p("Price Issues:");
                            CommonTools::p("Regular Price:".$stdPrice);
                            CommonTools::p(sprintf('Calc. Parameters: id_shop: %d id_currency:%d, id_country: %d, id_group: %d', $params->id_shop, $params->fromCurrency->id, (int)Configuration::get('PS_COUNTRY_DEFAULT'), $params->id_group));
                            CommonTools::p("Actual Price:".$newPrice);
                            CommonTools::p("Sale Price: ".(isset($salePrice) ? $salePrice : 0));
                            CommonTools::p("Sale: ".print_r($sales, true));
                            CommonTools::p("Specific Price: ".print_r($specificPrice, true));
                            CommonTools::p(sprintf('Final Price - price : %s was : %s', Tools::displayPrice($newPrice, $params->toCurrency), Tools::displayPrice($stdPrice, $params->toCurrency)));
                        }
                    } else {
                        $stdPrice = $newPrice = null;
                    }

                    if (empty($stdPrice) || !is_numeric($stdPrice)) {
                        $this->errorOutput(sprintf($this->l('Inconsistency for product %d/%d - price is not set'), $details->id, $id_product_attribute), self::ERROR);
                        $pass = false;
                        continue;
                    }

                    $disabled = (bool)$options['disable'];
                    $force = (bool)$options['force'];
                    $text = $options['text'];

                    $shipping = $options['shipping'];
                    $shipping_type = (int)$options['shipping_type'];
                    $nopexport = (bool)$options['nopexport'] ? (bool)$options['nopexport'] : $params->stockOnly;
                    $noqexport = (bool)$options['noqexport'] ? (bool)$options['noqexport'] : $params->pricesOnly;

                    if ((int)$options['latency']) {
                        $latency = (int)$options['latency'];
                    } elseif ((int)$p_latency) {
                        $latency = $p_latency;
                    } else {
                        $latency = null;
                    }

                    if ($params->FBA) {
                        $fba = (bool)$options['fba'];
                    }

                    if (isset($options['gift_wrap']) && (bool)$options['gift_wrap']) {
                        $gift_wrap = true;
                        $gift_message = (bool)$options['gift_message'] ? true : false;
                    } else {
                        $gift_wrap = false;
                        $gift_message = false;
                    }

                    if (isset($options['browsenode']) && Tools::strlen($options['browsenode']) && is_numeric($options['browsenode'])) {
                        $browsenode_override = trim($options['browsenode']);
                    } else {
                        $browsenode_override = null;
                    }

                    // Shipping Group (Shipping Templates) handling
                    //
                    $has_default_group = $params->shippingTemplates && Tools::strlen($p_shipping_group);
                    $has_template_override = $params->shippingTemplates && isset($options['shipping_group']) && Tools::strlen($options['shipping_group']);
                    $has_option_group_name = $has_template_override && isset($params->shippingTemplates[$params->region]) && isset($params->shippingTemplates[$params->region][$options['shipping_group']]);
                    $has_valid_default_group_name = $has_default_group && isset($params->shippingTemplates[$params->region]) && isset($params->shippingTemplates[$params->region][$p_shipping_group]);

                    if ($has_option_group_name) {
                        $shipping_group = $params->shippingTemplates[$params->region][$options['shipping_group']];
                    } elseif ($has_valid_default_group_name) {
                        $shipping_group = $params->shippingTemplates[$params->region][$p_shipping_group];
                    } else {
                        $shipping_group = null;
                    }

                    if (Amazon::$debug_mode) {
                        CommonTools::p("Shipping Group:");
                        CommonTools::p("Has default group: ".($has_default_group ? 'Yes' : 'No'));
                        CommonTools::p("Default Group Name: ".$p_shipping_group);
                        CommonTools::p("Has Option Group : ".($has_option_group_name ? 'Yes' : 'No'));
                        CommonTools::p("Option Group Name: ".(isset($options['shipping_group']) ? $options['shipping_group'] : null));
                        CommonTools::p("Current Group Name: ".$shipping_group);
                    }

                    if (!isset($options['asin1']) || !AmazonTools::validateASIN($options['asin1'])) {
                        $options['asin1'] = null;
                    }

                    if (Amazon::$debug_mode) {
                        CommonTools::p("Stock Issues:");
                        CommonTools::p("Stock Management: ".$params->stock_management);
                        CommonTools::p("Out Of Stock param: ".$params->outOfStock);
                        CommonTools::p("Quantity: ".$quantity);
                        CommonTools::p("Force: ".($force ? 'On' : 'Off'));
                        CommonTools::p("Disabled: ".($disabled ? 'On' : 'Off'));
                        CommonTools::p("Active: ".($details->active ? 'Yes' : 'No'));
                        CommonTools::p("To Remove: ".($action == Amazon::REMOVE ? 'Yes' : 'No'));
                        CommonTools::p("Preorder: ".($params->preorder ? 'Yes' : 'No'));
                        CommonTools::p("Remote Cart Quantity: ".$remote_cart_quantity);
                        CommonTools::p("Date Restock: ".(isset($combination['available_date']) ? isset($combination['available_date']) : null));
                    }

                    // Out of Stock policies
                    //

                    if (!$params->stock_management) {
                        $quantity = 100;
                    } elseif ($quantity <= 0 && isset($details->out_of_stock) && !Product::isAvailableWhenOutOfStock($details->out_of_stock) && !$force) {
                        $quantity = 0;
                    } elseif ($quantity <= 0 && !$force) {
                        $quantity = 0;
                    } elseif ($force) {
                        $quantity = 999;
                    }

                    if (!$details->active) {
                        $quantity = 0;
                    } elseif ($action == Amazon::REMOVE) {
                        $quantity = 0;
                    }

                    // Add the current reference to an array in case of combination to remove the parent if necessary (all combinations have quantity = 0
                    $overall_quantities[$combination['reference']] = $quantity;
                    $generate_product_feed = true;

                    if (!$params->create && (!$quantity || !$details->active || $disabled && !$fba)) {
                        //
                        $generate_product_feed = false;
                    }

                    // Restock Date > Pre-Sales
                    //
                    if (isset($combination['available_date']) && Validate::isDate($combination['available_date'])) {
                        $psDateRestock = strtotime($combination['available_date']);
                        $dateNow = time();

                        if ($params->preorder == false && $psDateRestock && $psDateRestock > $dateNow) {
                            //
                            $quantity = 0;
                        } elseif ($params->preorder == true && $psDateRestock && $psDateRestock + 86400 > $dateNow) {
                            // restock should be later, at least 1 day

                            $dateRestock = gmdate('Y-m-d', $psDateRestock);
                            $productsUpdate[$u]['RestockDate'] = $dateRestock;
                        }
                    }

                    $bulletPoints = array();

                    if ($generate_product_feed) {
                        if ($params->extendedDatas) {
                            $combination['supplier_reference'] = trim($supplier_reference);
                        }

                        $combination['category_name'] = $category_name;
                        $combination['manufacturer'] = $manufacturer;

                        $productDataAttr = array();

                        if (!$p_sku_as_supplier_reference && isset($removeTagForParent['MfrPartNumber'])) {
                            $removeTagForParent['MfrPartNumber'];
                        }


                        if ($params->extendedDatas) {
                            $product_data['Parameters'] = $p_parameters;
                            $product_data['Attributes'] = $productDataAttr;

                            switch ($p_universe) {
                                case 'CE':
                                    if ($params->create) {
                                        $product_data['ProductType'] = 'ConsumerElectronics';
                                        $product_data['Definition'] = $p_universe;
                                        $product_data['ProductSubtype'] = $p_product_type;
                                        $product_data['Parentage'] = 'child';
                                    }
                                    break;

                                case 'ProductClothing':
                                case 'ClothingAccessories':
                                case 'Shoes':
                                    $product_data['ClothingType'] = $p_product_type;
                                    $product_data['Definition'] = $p_universe;

                                    if (AmazonTools::isEuropeMarketplaceId($params->marketplaceID) && in_array($p_universe, array('ProductClothing', 'Shoes'))) {
                                        $special_feature = in_array($p_universe, array('ProductClothing')) ? 'SpecialFeature' : 'SpecialFeatures';
                                        // Path to
                                        $product_data['Parameters'][$special_feature] = array($p_universe == 'ProductClothing' ? 'Clothing' : $p_universe, 'ClassificationData', $special_feature);
                                    }
                                    break;

                                case 'LargeAppliances':
                                    $product_data['Parameters']['ProductName'] = array($p_universe, 'ProductName');
                                    $product_data['Parameters']['Manufacturer'] = array($p_universe, 'Manufacturer');
                                    $product_data['ProductName'] = null;
                                    $product_data['Manufacturer'] = null;
                                    break;

                                default:
                                    if ($params->create) {
                                        $product_data['ProductType'] = $p_product_type;
                                        $product_data['Definition'] = $p_universe;
                                    }
                                    break;
                            }

                            if (Amazon::$debug_mode) {
                                //CommonTools::p("product_data: ");
                                //var_dump($product_data);
                            }
                        }

                        // Handling Special Cases for Product Sheet Creation
                        //
                        if ($params->create || $params->extendedDatas) {
                            if ($params->create && isset($p_parameters['Parentage'])) {
                                $variant = true;
                            } else {
                                $variant = false;
                            }

                            if (Amazon::$debug_mode) {
                                $this->errorOutput(sprintf('Creation mode: using main category: %s product has variant: %s', $p_universe, $variant ? 'Yes' : 'No'), self::WARNING);
                            }
                            $variation_theme = null;

                            // Incomplete Item
                            //
                            if ($variant && isset($product_data['Variation']) && $product_data['Variation'] === false) {
                                $productIndex--;
                                continue;
                            }

                            // Extra Fields - cf xsd_operations.php
                            //
                            if ($p_extra) {
                                // This is a specific variant (eg: Jewelry) description
                                //
                                if (isset($p_extra['variant']) && Tools::strlen($p_extra['variant'])) {
                                    $variation_theme = $p_extra['variant'];

                                    if (strpos($variation_theme, '-')) {
                                        $variation_fields = explode('-', $variation_theme);

                                        $variation_field_list = $variation_fields;
                                    } else {
                                        $variation_field_list = array($variation_theme);
                                    }

                                    if ($variant && $hasCombinations) {
                                        $product_data['Variation'] = true;
                                        //if ($hasCombinations)
                                        $product_data['Parentage'] = $hasCombinations && $id_product_attribute ? 'child' : 'parent';
                                        $product_data['VariationTheme'] = $variation_theme;
                                    }


                                    // Size/Color exception for some XSD
                                    if (in_array($variation_theme, array(
                                            'Size-Color',
                                            'Color-Size'
                                        )) && $hasCombinations
                                    ) {
                                        switch ($p_universe) {
                                            case 'Sports':
                                                $variation_theme = str_replace(array(
                                                    'Color-Size'
                                                ), array(
                                                    'ColorSize',
                                                ), $p_extra['variant']);
                                                $product_data['VariationTheme'] = $variation_theme;
                                                break;
                                            case 'Luggage':
                                                $variation_theme = str_replace(array(
                                                    'Size-Color',
                                                    'Color-Size'
                                                ), array(
                                                    'SizeName-ColorName',
                                                ), $p_extra['variant']);
                                                $product_data['VariationTheme'] = $variation_theme;
                                                break;
                                            case 'LargeAppliances':
                                            case 'Motorcycles':
                                            case 'MusicalInstruments':
                                            case 'Office':
                                            case 'PetSupplies':
                                            case 'Toys':
                                            case 'ProductClothing':
                                            case 'ClothingAccessories':
                                            case 'Shoes':
                                                $variation_theme = str_replace(array(
                                                    'Size-Color',
                                                    'Color-Size'
                                                ), array(
                                                    'SizeColor',
                                                    'ColorSize'
                                                ), $p_extra['variant']);
                                                $product_data['VariationTheme'] = $variation_theme;
                                        }
                                    }
                                }
                                // Context: Ticket: http://support.common-services.com/helpdesk/tickets/10940
                                if (in_array($p_universe, array('Jewelry')) && isset($p_extra['field']['MetalType']) && !empty($p_extra['field']['MetalType']) && !isset($product_data['Parentage'])) {
                                    $product_data['Parentage'] = 'child';
                                }

                                if ($params->extendedDatas && isset($p_extra['multiple'])) {
                                    $pass = true;

                                    $has_choice_required = isset($p_extra['choices_required']) ? true : false;
                                    $missing_choice_selected = true;
                                    $selected_choice = array();
                                    
                                    foreach (array_keys($p_extra['multiple']) as $field) {
                                        $field_value = isset($p_extra['field'][$field]) ? $p_extra['field'][$field] : null;

                                        $mapping = $this->getMappingValue($field, $combination, $p_extra, $p_key);

                                        if (!max($mapping)) {
                                            //no value

                                            continue;
                                        }

                                        if (isset($p_extra['required']) && isset($p_extra['required'][$field])) {
                                            $really_required = true;
                                        } else {
                                            $really_required = false;
                                        }

                                        if (Amazon::$debug_mode) {
                                            CommonTools::p("Field: ".$field);
                                            CommonTools::p("Really Required: ".$really_required);
                                            CommonTools::p("Has Combinations: ".$hasCombinations);
                                            CommonTools::p("Mapping Result: ");
                                            CommonTools::p($mapping);
                                        }
                                        // Explode field - example: https://support.common-services.com/a/tickets/49765
                                        $target_definition = $this->search_key($field, $productInstance);

                                        if (!$mapping['has_mapping'] && Tools::strlen($mapping['value']) && $target_definition instanceof stdClass) {
                                            if (Amazon::$debug_mode) {
                                                CommonTools::p("Target Definition: ".print_r($target_definition, true));
                                                CommonTools::p("Field:".print_r($mapping['value']));
                                            }

                                            if (($target_definition->limit > 1 || $target_definition->limit == null) && isset($target_definition->maxLength) && $target_definition->maxLength && Tools::strlen($mapping['value']) > $target_definition->maxLength && substr_count($mapping['value'], ',') > 1) {
                                                $results = array_unique(explode("\n", wordwrap(preg_replace('/\s{2,}/', ' ', str_replace(',', ' ', $mapping['value'])), $target_definition->maxLength, "\n")));

                                                if (is_array($results) && count($results)) {
                                                    $product_data[$field] = array();
                                                    $i=0;
                                                    foreach ($results as $result) {
                                                        $product_data[$field][] = str_replace(' ', ',', trim($result));
                                                        if ($target_definition->limit && $i++ >= $target_definition->limit) {
                                                            break;
                                                        }
                                                    }
                                                    continue;
                                                } elseif (isset($target_definition->maxLength) && $target_definition->maxLength) {
                                                    $product_data[$field] = Tools::substr($mapping['value'], 0, $target_definition->maxLength-1) ;
                                                    continue;
                                                }
                                            } else {
                                                $product_data[$field] = $mapping['value'];
                                                continue;
                                            }
                                        }

                                        if ($mapping['is_variant_field'] && $mapping['is_missing'] && $hasCombinations) {
                                            // Missing mapping for variant

                                            $this->errorOutput(sprintf($this->l('Inconsistency for product %d/%d - %s mapping is missing for variant'), $details->id, $id_product_attribute, $field), self::ERROR);
                                            $pass = false;
                                            break;
                                            continue;
                                        } elseif ($mapping['required'] && $mapping['is_missing'] && $really_required) {
                                            $this->errorOutput(sprintf($this->l('Inconsistency for product %d/%d - %s is a required field'), $details->id, $id_product_attribute, $field), self::ERROR);
                                            $pass = false;
                                            break;
                                        } elseif ($mapping['is_missing'] && $mapping['value']) {
                                            // Missing but export is allowed

                                            $this->errorOutput(sprintf($this->l('Inconsistency for product %d/%d - %s mapping is missing'), $details->id, $id_product_attribute, $field), self::WARNING);
                                            continue;
                                        }

                                        // Rewrite particular cases
                                        if (isset(AmazonXSD::$rewriteFieldsUniverse[$p_universe]) && isset(AmazonXSD::$rewriteFieldsUniverse[$p_universe][$field])) {
                                            $field = AmazonXSD::$rewriteFieldsUniverse[$p_universe][$field];
                                        } elseif (isset(AmazonXSD::$rewriteFields[$field])) {
                                            $field = AmazonXSD::$rewriteFields[$field];
                                        }

                                        //Color/Size Exception
                                        if ($mapping['is_variant_field'] && in_array($field, array('Color', 'ColorName', 'Size', 'SizeName'))) {
                                            $field_map = in_array($field, array('Color', 'ColorName')) ? 'ColorMap' : 'SizeMap';

                                            if ($mapping['has_mapping'] && Tools::strlen($mapping['value'])) {
                                                $product_data[$field_map] = $mapping['mapping'];
                                                $product_data[$field] = $mapping['value'];

                                                if ($hasCombinations) {
                                                    $removeTagForParent[] = $field_map;
                                                }
                                            }
                                        } elseif ($mapping['is_map_required'] && in_array($field, array('Color', 'ColorName', 'Size', 'SizeName'))) {
                                            $field_map = in_array($field, array('Color', 'ColorName')) ? 'ColorMap' : 'SizeMap';

                                            if ($mapping['has_mapping'] && Tools::strlen($mapping['value'])) {
                                                $product_data[$field_map] = $mapping['mapping'];
                                                $product_data[$field] = $mapping['value'];

                                                $product_data['Parameters'][$field_map] = array($p_product_type, $field_map);

                                                if ($hasCombinations) {
                                                    $removeTagForParent[] = $field_map;
                                                }
                                            }
                                        } elseif ($mapping['has_mapping'] && $mapping['is_multiple']) {
                                            $product_data[$field] = explode(',', $mapping['value']);
                                        } elseif ($mapping['has_mapping']) {
                                            $product_data[$field] = $mapping['mapping'];
                                        } elseif ($mapping['value']) {
                                            $product_data[$field] = $mapping['value'];
                                        }

                                        if ($mapping['is_variant_field']) { // || !$mapping['is_map_required'] added on 2017-04-24
                                            $removeTagForParent[] = $field;
                                        }

                                        // Adding Attributes
                                        $has_amazon_attribute = isset($p_extra['attributes']) && isset($p_extra['attributes'][$field]) && is_array($p_extra['attributes'][$field]) && count($p_extra['attributes'][$field]);

                                        if (isset($product_data[$field]) && $has_amazon_attribute) {
                                            $amazon_attribute_key = key($p_extra['attributes'][$field]);
                                            $amazon_attribute_value = reset($p_extra['attributes'][$field]);

                                            if (Tools::strlen($amazon_attribute_key) && Tools::strlen($amazon_attribute_value)) {
                                                $productDataAttr[$field] = $p_extra['attributes'][$field];

                                                if (($new_value = $this->validateField($productInstance, $p_parameters['xsd'], $p_universe, $p_product_type, $field, $product_data[$field])) === false) {
                                                    $this->errorOutput(sprintf('SKU: %s, Amazon Attribute: %s, Value: %s - %s', $reference, $field, $product_data[$field], $this->l('Field validation failed, value skipped')), self::WARNING);
                                                    unset($product_data[$field]);
                                                } elseif (Tools::strlen($new_value)) {
                                                    $product_data[$field] = $new_value;
                                                    if ($amazon_attribute_value) {
                                                        $product_data['Attributes'][$field][$amazon_attribute_key] = $amazon_attribute_value;
                                                    }
                                                } else {
                                                    unset($product_data[$field]);
                                                }
                                            } else {
                                                unset($product_data[$field]);
                                            }
                                        } elseif (isset($product_data[$field]) && Tools::strlen($product_data[$field])) {
                                            if (($new_value = $this->validateField($productInstance, $p_parameters['xsd'], $p_universe, $p_product_type, $field, $product_data[$field])) === false) {
                                                $this->errorOutput(sprintf('SKU: %s, Amazon Attribute: %s, Value: %s - %s', $reference, $field, $product_data[$field], $this->l('Field validation failed, value skipped')), self::WARNING);
                                                unset($product_data[$field]);
                                            } elseif (Tools::strlen($new_value)) {
                                                $product_data[$field] = $new_value;
                                            } else {
                                                unset($product_data[$field]);
                                            }
                                        } elseif ($mapping['is_multiple']) {
                                            // do not do anything :)
                                        } elseif (isset($product_data[$field]) && !Tools::strlen($product_data[$field])) {
                                            if ($field == 'TargetGender') {
                                                unset($product_data[$field]);
                                            }
                                        }

                                        if (isset($product_data[$field]) && $really_required && $has_amazon_attribute && !isset($productDataAttr[$field])) {
                                            $this->errorOutput(sprintf($this->l('Inconsistency for product %d/%d - Missing mandatory Amazon attribute for %s'), $details->id, $id_product_attribute, $field), self::WARNING);
                                            unset($product_data[$field]);
                                            $pass = false;
                                            break;
                                        }

                                        if ($has_choice_required) {
                                            if (isset($p_extra['choices'][$field]) && isset($field_value) && Tools::strlen($field_value)) {
                                                $missing_choice_selected = false;
                                                $selected_choice[$field] = true;

                                                if (isset($p_extra['choice_allowed_values'][$field])) {
                                                    $choice_allowed_values = unserialize(AmazonTools::decode($p_extra['choice_allowed_values'][$field]));

                                                    if (isset($mapping['has_mapping']) && $mapping['has_mapping']) {
                                                        if (!in_array($mapping['mapping'], $choice_allowed_values)) {
                                                            $this->errorOutput(sprintf($this->l('SKU: %s, %s is not an allowed value.'), $reference, $mapping['mapping']), self::ERROR);
                                                            $pass = false;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    // one of choice field required
                                    if ($has_choice_required && $missing_choice_selected) {
                                        $this->errorOutput(sprintf($this->l('SKU: %s, One of (%s) is expected.'), $reference, implode(", ", array_keys($p_extra['choices']))), self::ERROR);
                                        $pass = false;
                                    }

                                    if (count($selected_choice) > 1) {
                                        $this->errorOutput(sprintf($this->l('SKU: %s, can not have choice tag more than one field, current fields : (%s).'), $reference, implode(", ", array_keys($selected_choice))), self::ERROR);
                                        $pass = false;
                                    }
                                    
                                    if (!$pass) {
                                        $skipped++;
                                        continue;
                                    }
                                }
                            }

                            $productTag = isset($product_data['ClothingType']) ? 'ClassificationData' : 'ProductType';

                            if (isset($p_code_exemption_options['private_label']) && (int)$p_code_exemption_options['private_label']) {
                                // Exemption Selector
                                $product_data['RegisteredParameter'] = 'PrivateLabel';
                            } elseif ($p_code_exemption == Amazon::EXEMPTION_COMPATIBILITY) {
                                // 2013-03-23 - Added : EAN/UPC Exemption support for products outside Jewelry and Handycraft
                                $product_data['RegisteredParameter'] = 'PrivateLabel';
                            }

                            switch ($p_code_exemption) {
                                case Amazon::EXEMPTION_MODEL_NUMBER:

                                    switch ($p_universe) {
                                        case 'ProductClothing':
                                            $product_data['Parameters']['ModelNumber'] =  array('Clothing', $productTag, 'ModelNumber');
                                            break;
                                        case 'Jewelry':
                                            $product_data['Parameters']['ModelNumber'] =  array('Jewelry', 'ModelNumber');
                                            break;
                                        default:
                                            $product_data['Parameters']['ModelNumber'] =  array($p_universe, $productTag, 'ModelNumber');
                                            break;
                                    }
                                    $product_data['ModelNumber'] = $combination['reference'];
                                    break;
                                case Amazon::EXEMPTION_MODEL_NAME:
                                    $product_data['Parameters']['ModelName'] = array(
                                        (($p_universe == 'ProductClothing') ? 'Clothing' : $p_universe),
                                        $productTag,
                                        'ModelName'
                                    );
                                    $product_data['ModelName'] = $combination['reference'];
                                    break;
                                case Amazon::EXEMPTION_MFR_PART_NUMBER:
                                    if ($p_sku_as_sup_ref_unconditionnaly) {
                                        $product_data['MfrPartNumber'] = $combination['reference'];
                                    } elseif (!empty($supplier_reference)) {
                                        $product_data['MfrPartNumber'] = $supplier_reference;
                                    } else {
                                        $product_data['MfrPartNumber'] = $combination['reference'];
                                    }
                                    break;
                                case Amazon::EXEMPTION_CATALOG_NUMBER:
                                    $product_data['MerchantCatalogNumber'] = $combination['reference'];
                                    break;
                                case Amazon::EXEMPTION_STYLE_NUMBER:
                                    $product_data['Parameters']['StyleNumber'] = array(
                                        (($p_universe == 'ProductClothing') ? 'Clothing' : $p_universe),
                                        $productTag,
                                        'StyleNumber'
                                    );
                                    $product_data['StyleNumber'] = $combination['reference'];
                                    break;
                                case Amazon::EXEMPTION_GENERIC:
                                    // Only Private Label is forced and sent
                                    $p_code_exemption_options['private_label'] = true;
                                    break;
                            }

                            // Product Tax Code
                            //
                            if (Tools::strlen($p_ptc)) {
                                $product_data['ProductTaxCode'] = $p_ptc;
                                $removeTagForParent[] = 'ProductTaxCode';
                            } elseif (isset($params->defaultPtc) && Tools::strlen($params->defaultPtc)) {
                                $product_data['ProductTaxCode'] = $params->defaultPtc;
                                $removeTagForParent[] = 'ProductTaxCode';
                            }

                            // 2013-03-29 - Added : AutoAccessory / ItemPackageQuantity - this is required i don't know why ?
                            //
                            if ($p_universe == 'AutoAccessory' && !isset($product_data['ItemPackageQuantity']) && !isset($product_data['NumberOfItems'])) {
                                $product_data['Parameters']['Count'] = array(
                                    $p_universe,
                                    'ProductType',
                                    $p_product_type,
                                    'ItemPackageQuantity'
                                );
                                $product_data['ItemPackageQuantity'] = 1;
                            } elseif (!isset($product_data['NumberOfItems']) && !isset($product_data['ItemPackageQuantity']) && in_array($p_universe, array('Industrial', 'FoodServiceAndJanSan'))) {
                                // 2013-06-14 - Added : Industrial / NumberOfItems - this is required.
                                //
                                // Industrial and FoodServiceAndJanSan
                                $product_data['Parameters']['NumberOfItems'] = array(
                                    $p_universe,
                                    'ProductType',
                                    $p_product_type,
                                    'NumberOfItems'
                                );
                                $product_data['NumberOfItems'] = 1;
                            } elseif (!isset($product_data['UnitCount']) && !isset($product_data['ItemPackageQuantity']) && in_array($p_universe, array('Beauty', 'Health'))) {
                                // 2015-02-11 - Added : Count is now mandatory for Beauty/Health
                                //
                                $product_data['Parameters']['Count'] = array(
                                $p_universe,
                                'ProductType',
                                $p_product_type,
                                'Count'
                                );
                                $product_data['Count'] = 1;

                                $product_data['Parameters']['UnitCount'] = array(
                                    $p_universe,
                                    'ProductType',
                                    $p_product_type,
                                    'UnitCount'
                                );
                                $product_data['UnitCount'] = 1;
                            } elseif (!isset($product_data['UnitCount']) && in_array($p_universe, array('FoodAndBeverages'))) {
                                // 2015-09-16 - Added : UnitCount is now mandatory for FoodAndBeverages
                                //
                                $product_data['Parameters']['Count'] = array(
                                    $p_universe,
                                    'ProductType',
                                    $p_product_type,
                                    'Count'
                                );
                                $product_data['Parameters']['UnitCount'] = array(
                                    $p_universe,
                                    'ProductType',
                                    $p_product_type,
                                    'UnitCount'
                                );
                                $product_data['Count'] = 1;
                                $product_data['UnitCount'] = 1;
                            }
                            if (isset($product_data['UnitCount'])) {
                                $product_data['Attributes']['UnitCount']['unitOfMeasure'] = 'ct';
                            }

                            //
                            // Added on 24/11/2014 for Luggage Exception
                            //
                            if ($params->create && in_array($p_universe, array('Luggage'))) {
                                $pass = false;

                                if (isset($product_data['VolumeCapacityName']) && isset($product_data['Weight'])) {
                                    if (in_array(Tools::strtoupper($params->weightUnit), array('KG', 'GR', 'G'))) {
                                        $product_data['Attributes']['VolumeCapacityName']['unitOfMeasure'] = 'liter';

                                        $productsUpdate[$u]['ProductDescription']['ItemDimensions'] = array();
                                        $productsUpdate[$u]['ProductDescription']['ItemDimensions']['Weight']['value'] = round((float)$product_data['Weight'], 2);
                                        $productsUpdate[$u]['ProductDescription']['ItemDimensions']['Weight']['unitOfMeasure'] = Tools::strtoupper($params->weightUnit);

                                        $pass = true;
                                    }
                                }

                                if (!$pass) {
                                    $this->errorOutput(sprintf($this->l('Warning : Please contact your Common-Services technical support and indicate you are trying to export to Luggage category an unexpected case (%s)')."$cr", $params->weightUnit));
                                }
                            }

                            // The goal is the first combination is the parent and the subsequent the childrens
                            //
                            if ($variant) {
                                if (empty($details->reference)) {
                                    $message = AmazonSupport::message(sprintf($this->l('Inconsistency for product %d/%d - Is a Combination/Variant and has no Master Reference - Skipping product'), $details->id, $id_product_attribute), AmazonSupport::FUNCTION_EXPORT_NO_MASTER_SKU);
                                    $this->errorOutput($message, self::WARNING);
                                    $skipped++;
                                    continue;
                                }

                                if ($hasCombinations && isset($product_data['Variation']) && $product_data['Variation'] && !isset($Relationship[$details->reference])) {
                                    // Adding children to the relationships - first child
                                    $Relationship[$details->reference] = array();
                                    $Relationship[$details->reference]['parent'] = trim($details->reference);
                                    $Relationship[$details->reference]['children'] = array();
                                    $Relationship[$details->reference]['children'][] = trim($combination['reference']);
                                    $relations++;
                                } elseif ($hasCombinations && isset($product_data['Variation']) && $product_data['Variation']) {
                                    // Adding children to the relationships - children
                                    $Relationship[$details->reference]['children'][] = trim($combination['reference']);
                                    $relations++;
                                }
                            }
                        }
                    }
                    if (($params->repricing || $params->repricing === null) && (isset($p_repricing) || $params->default_strategy)) {
                        if (max(array(strtotime($details->date_add), strtotime($details->date_upd))) > min($batches_times)) {
                            // queue a fake notification when a product has been modified since the last update
                            AmazonProduct::marketplaceActionSet(Amazon::REPRICE, $id_product, $id_product_attribute, $combination['reference'], $params->id_lang);
                        }
                    }

                    if ($params->extendedDatas) {
                        $generated_bullet_points = array();
                        $short_description_used_as_bullet_point = false;

                        if (!isset($options['bullet_point1']) || empty($options['bullet_point1']) && (int)$p_bullet_point_strategy) {
                            switch ($p_bullet_point_strategy) {
                                case Amazon::BULLET_POINT_STRATEGY_DESC:
                                case Amazon::BULLET_POINT_STRATEGY_DESC_ATTRIBUTES_FEATURES:
                                case Amazon::BULLET_POINT_STRATEGY_DESC_FEATURES:
                                    if (Tools::strlen($details->description_short) && Tools::strlen($details->description_short) < Amazon::LENGTH_BULLET_POINT) {
                                        $generated_bullet_points[] = AmazonTools::cleanStripTags($details->description_short, false);
                                        $short_description_used_as_bullet_point = true;
                                    }
                                    break;
                            }
                            switch ($p_bullet_point_strategy) {
                                case Amazon::BULLET_POINT_STRATEGY_ATTRIBUTES:
                                case Amazon::BULLET_POINT_STRATEGY_ATTRIBUTES_FEATURES:
                                case Amazon::BULLET_POINT_STRATEGY_DESC_ATTRIBUTES_FEATURES:
                                    if (isset($combination['attributes']) && is_array($combination['attributes']) && count($combination['attributes'])) {
                                        if (Amazon::$debug_mode) {
                                            CommonTools::p("Bullet Points Strategy: ".$p_bullet_point_strategy);
                                            CommonTools::p("Bullet Points Data:");
                                            CommonTools::p($combination['attributes']);
                                        }

                                        foreach ($combination['attributes'] as $attribute) {
                                            if (!is_array($attribute)) {
                                                continue;
                                            }

                                            $attribute_content = reset($attribute);
                                            $attribute_group = $attribute_content['group'];
                                            $attribute_name = isset($attribute_content['mapping']) && Tools::strlen($attribute_content['mapping']) ? $attribute_content['mapping'] : $attribute_content['name'];

                                            if (!Tools::strlen($attribute_name)) {
                                                continue;
                                            }

                                            if ($p_bullet_point_labels) {
                                                $generated_bullet_points[] = sprintf('%s: %s', $attribute_group, $attribute_name);
                                            } else {
                                                $generated_bullet_points[] = $attribute_name;
                                            }
                                        }
                                    }
                                    break;
                            }
                            switch ($p_bullet_point_strategy) {
                                case Amazon::BULLET_POINT_STRATEGY_FEATURES:
                                case Amazon::BULLET_POINT_STRATEGY_ATTRIBUTES_FEATURES:
                                case Amazon::BULLET_POINT_STRATEGY_DESC_ATTRIBUTES_FEATURES:
                                case Amazon::BULLET_POINT_STRATEGY_DESC_FEATURES:
                                    if (isset($combination['features']) && is_array($combination['features']) && count($combination['features'])) {
                                        if (Amazon::$debug_mode) {
                                            CommonTools::p("Bullet Points Strategy: ".$p_bullet_point_strategy);
                                            CommonTools::p("Bullet Points Data:");
                                            CommonTools::p($combination['features']);
                                        }

                                        foreach ($combination['features'] as $feature) {
                                            if (!is_array($feature)) {
                                                continue;
                                            }

                                            $feature_value = isset($feature['mapping']) && Tools::strlen($feature['mapping']) ? $feature['mapping'] : $feature['value'];

                                            if (!Tools::strlen($feature_value)) {
                                                continue;
                                            }

                                            if ($p_bullet_point_labels) {
                                                $generated_bullet_points[] = sprintf('%s: %s', $feature['name'], $feature_value);
                                            } else {
                                                $generated_bullet_points[] = $feature_value;
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                        $special_feature = in_array($p_universe, array('ProductClothing')) ? 'SpecialFeature' : 'SpecialFeatures';

                        //
                        // Special Features, Replacement for Bullet Points for Shoes and Clothing (2014-09-11)
                        //
                        if (AmazonTools::isEuropeMarketplaceId($params->marketplaceID) && in_array($p_universe, array('ProductClothing', 'Shoes'))) {
                            $product_data[$special_feature] = array();
                            $c = 1;
                            $index = 0;

                            foreach (array(
                                         'bullet_point1',
                                         'bullet_point2',
                                         'bullet_point3',
                                         'bullet_point4',
                                         'bullet_point5'
                                     ) as $bullet_item) {
                                if ((!isset($options[$bullet_item]) || empty($options[$bullet_item])) && (!isset($generated_bullet_points[$index]) || empty($generated_bullet_points[$index]))) {
                                    continue;
                                }

                                $line = isset($options[$bullet_item]) && Tools::strlen($options[$bullet_item]) ? $options[$bullet_item] : $generated_bullet_points[$index];

                                $bullet_point = Tools::substr(AmazonTools::encodeText($line, $params->safeEncoding), 0, Amazon::LENGTH_BULLET_POINT);

                                if (Tools::strlen($bullet_point) > 4) {
                                    $index++;
                                    $product_data[$special_feature][] = $bullet_point;
                                }

                                if ($c++ >= 3) {
                                    break;
                                }
                            }
                            if (!is_array($product_data[$special_feature]) || !count($product_data[$special_feature])) {
                                unset($product_data[$special_feature]);
                            }
                        } else {
                            //
                            // Bullet Points (2013-08-19)
                            //
                            $index = 0;
                            foreach (array(
                                         'bullet_point1',
                                         'bullet_point2',
                                         'bullet_point3',
                                         'bullet_point4',
                                         'bullet_point5'
                                     ) as $bullet_item) {
                                if ((!isset($options[$bullet_item]) || empty($options[$bullet_item])) && (!isset($generated_bullet_points[$index]) || empty($generated_bullet_points[$index]))) {
                                    continue;
                                }

                                $line = isset($options[$bullet_item]) && Tools::strlen($options[$bullet_item]) ? $options[$bullet_item] : $generated_bullet_points[$index];

                                $bullet_point = Tools::substr(AmazonTools::encodeText($line, $params->safeEncoding), 0, Amazon::LENGTH_BULLET_POINT);

                                if (Tools::strlen($bullet_point) > 4) {
                                    $bulletPoints[] = $bullet_point;
                                    $index++;
                                }
                            }
                            if (!count($bulletPoints)) {
                                $bulletPoints = array();
                            }
                        }
                    }

                    if ($params->repricing === null && (isset($p_repricing) || $params->default_strategy)) {
                        // queue a fake notification
                        AmazonProduct::marketplaceActionSet(Amazon::REPRICE, $id_product, $id_product_attribute, $combination['reference'], $params->id_lang);
                    } elseif ($params->forcePriceFeed && $params->repricing) {
                        // queue a fake notification
                        AmazonProduct::marketplaceActionSet(Amazon::REPRICE, $id_product, $id_product_attribute, $combination['reference'], $params->id_lang);
                    }

                    if ((isset($p_repricing) || $params->default_strategy) && $params->repricing && !$params->create) {
                        // Uncomment to do not export prices on products updates
                        if (Amazon::$debug_mode) {
                            CommonTools::p('Repricing is active. Price are not exported');
                        }

                        // in case of an FBA product, we send the price (for cases of product switching from MFN > FBA)
                        if (!($fba && $options['fba_value']) && !$params->forcePriceFeed) {
                            $nopexport = true;

                            if ($params->repricing === null) {
                                if (Amazon::$debug_mode) {
                                    CommonTools::p('Tag as to be repriced');
                                }
                                AmazonProduct::marketplaceActionSet(Amazon::REPRICE, $id_product, $id_product_attribute, $combination['reference'], $params->id_lang);
                            }
                        }
                    }


                    if (!empty($options['price']) && (float)$options['price']) {
                        $newPrice = (float)$options['price'];

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Price Override: %.02f', $newPrice));
                        }
                    }

                    // Using Fulfilment By Amazon
                    //
                    if ($params->FBA && $fba) {
                        $beforeFBAPrice = $newPrice;

                        if (Amazon::$debug_mode) {
                            CommonTools::p('Using FBA');
                        }

                        // FBA Value Added
                        if ((float)$options['fba_value']) {
                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('FBA Value Added: %.02f', (float)$options['fba_value']));
                            }

                            $newPrice += (float)$options['fba_value'];

                            if ($params->rounding && in_array($params->rounding, array(
                                    Amazon::ROUNDING_ONE_DIGIT,
                                    Amazon::ROUNDING_TWO_DIGITS
                                ))
                            ) {
                                $newPrice = Tools::ps_round($newPrice, $params->rounding);
                            } elseif ($params->rounding && $params->rounding == Amazon::ROUNDING_SMART) {
                                $newPrice = AmazonTools::smartRounding($newPrice);
                            } else {
                                $newPrice = sprintf('%.02f', $newPrice);
                            }

                            if (isset($sales['salePrice']) && $sales['salePrice']) {
                                $sales['salePrice'] += (float)$options['fba_value'];

                                if ($params->rounding && in_array($params->rounding, array(
                                        Amazon::ROUNDING_ONE_DIGIT,
                                        Amazon::ROUNDING_TWO_DIGITS
                                    ))
                                ) {
                                    $sales['salePrice'] = Tools::ps_round($sales['salePrice'], $params->rounding);
                                } elseif ($params->rounding && $params->rounding == Amazon::ROUNDING_SMART) {
                                    $sales['salePrice'] = AmazonTools::smartRounding($sales['salePrice']);
                                } else {
                                    $sales['salePrice'] = Tools::ps_round($sales['salePrice'], 4);
                                }
                            }
                        } elseif ($params->fbaFormula) {
                            // FBA formula
                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('Apply FBA formula: %s', $params->fbaFormula));
                            }

                            $newPrice = AmazonTools::formula($newPrice, $params->fbaFormula);

                            if ($params->rounding && in_array($params->rounding, array(
                                    Amazon::ROUNDING_ONE_DIGIT,
                                    Amazon::ROUNDING_TWO_DIGITS
                                ))
                            ) {
                                $newPrice = Tools::ps_round($newPrice, $params->rounding);
                            } elseif ($params->rounding && $params->rounding == Amazon::ROUNDING_SMART) {
                                $newPrice = number_format(round($newPrice, 0) - 0.01, 2, '.', '');
                            }

                            if ($params->priceRulesEnabled && $params->specialsApplyRules && isset($sales['salePrice']) && $sales['salePrice']) {
                                $sales['salePrice'] = AmazonTools::formula($sales['salePrice'], $params->fbaFormula);

                                if ($params->rounding && in_array($params->rounding, array(
                                        Amazon::ROUNDING_ONE_DIGIT,
                                        Amazon::ROUNDING_TWO_DIGITS
                                    ))
                                ) {
                                    $sales['salePrice'] = Tools::ps_round($sales['salePrice'], $params->rounding);
                                } elseif ($params->rounding && $params->rounding == Amazon::ROUNDING_SMART) {
                                    $sales['salePrice'] = AmazonTools::smartRounding($sales['salePrice']);
                                } else {
                                    $sales['salePrice'] = Tools::ps_round($sales['salePrice'], $params->rounding);
                                }
                            }
                        }
                    }

                    // Convert to platform currency
                    //
                    if ($params->fromCurrency->iso_code != $params->toCurrency->iso_code) {
                        $oldPrice = $newPrice;
                        $newPrice = Tools::convertPrice($newPrice, $params->toCurrency);

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Convert from currency: %s to currency: %s - price is: %s and was %s', $params->fromCurrency->iso_code, $params->toCurrency->iso_code, $newPrice, $oldPrice));
                        }
                    }

                    if ($params->rounding && in_array($params->rounding, array(Amazon::ROUNDING_ONE_DIGIT, Amazon::ROUNDING_TWO_DIGITS))) {
                        $newPrice = Tools::ps_round($newPrice, $params->rounding);
                    } elseif ($params->rounding && $params->rounding == Amazon::ROUNDING_SMART) {
                        $newPrice = AmazonTools::smartRounding($newPrice);
                    } else {
                        $newPrice = sprintf('%.02f', $newPrice);
                    }

                    $delete = false;

                    if ((!$params->create) && $params->authorizeToDelete && ($quantity == 0 || !$details->active)) {
                        $delete = true;
                    }


                    if ((!$params->create) && in_array($action, array(Amazon::UPDATE, Amazon::ADD, Amazon::REMOVE)) && $params->authorizeToDelete && ($disabled || !$details->active)) {
                        $delete = true;
                    }

                    if ($action == Amazon::REMOVE && $params->deleteConfirmed) {
                        $delete = true;
                    }

                    if ($disabled && ($params->bruteForce || $params->entireCatalog) && $params->authorizeToDelete) {
                        //
                        $delete = true;
                    } elseif ($disabled) {
                        printf($this->l('Skipped,disabled product').$cr);
                        $productIndex--;
                        $skipped++;
                        continue;
                    }

                    if ($action == Amazon::ADD && $params->repricing == true && (isset($p_repricing) || $params->default_strategy) && $details->active && !$delete) {
                        // queue a fake notification
                        AmazonProduct::marketplaceActionSet(Amazon::REPRICE, $id_product, $id_product_attribute, $combination['reference'], $params->id_lang);
                    }

                    if (!$params->create && $params->authorizeToDelete && $params->deleteProducts && ($quantity == 0 && ($combination[$ps_code] || $params->asinOverride)) || $delete) {
                        $productsDelete[$d]['SKU'] = trim($combination['reference']);

                        if (!isset($productsDelete[$d]['SKU']) || empty($productsDelete[$d]['SKU'])) {
                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('Skipping : %d - Has No SKU !', $details->id_product));
                            }
                            $skipped++;
                            continue;
                        }

                        if ($delete) {
                            $reason = 'Deleted';
                        } else {
                            $reason = 'OutOfStock';
                        }

                        printf('Delete() : %s %s %s - %s'."$cr", isset($productsDelete[$d]['ProductIDType']) ? $productsDelete[$d]['ProductIDType'] : '', isset($productsDelete[$d]['ProductIDCode']) ? $productsDelete[$d]['ProductIDCode'] : '-', $productsDelete[$d]['SKU'], $reason);

                        $productIndex--;
                        $synchProductsIds[] = $id_product;

                        if (Tools::strlen($details->reference) && count($overall_quantities) > 1 && !array_filter($overall_quantities)) {
                            //all combinations have 0 quantity

                            $is_last_combination = $group_details[count($group_details)-1]['reference'] == $combination['reference'];

                            if ($is_last_combination) {
                                $productsDelete[$d+1] = $productsDelete[$d];
                                $productsDelete[$d+1]['SKU'] = $details->reference;
                                $d++;
                            }
                        }

                        $d++;
                    } elseif ($action == Amazon::REMOVE) {
                        $productsUpdate[$u]['SKU'] = trim($combination['reference']);

                        if ($params->asinOverride && $options['asin1']) {
                            $productsUpdate[$u]['ProductIDType'] = 'ASIN';
                            $productsUpdate[$u]['ProductIDCode'] = trim($options['asin1']);
                        } elseif ($combination[$ps_code]) {
                            $productsUpdate[$u]['ProductIDType'] = 'SKU';
                            $productsUpdate[$u]['ProductIDCode'] = trim($reference);
                        }

                        $productsUpdate[$u]['Quantity'] = 0;
                        $productsUpdate[$u]['NoPriceExport'] = true;
                        $productsUpdate[$u]['NoProductFeed'] = true;

                        if ($has_product_feed != true) {
                            $has_product_feed = false;
                        }

                        $sent_quantity = isset($productsUpdate[$u]['Quantity']) ? ((bool)$noqexport ? 'n/a' : $productsUpdate[$u]['Quantity']) : 'n/a';
                        $sent_price = 'n/a';

                        $has_quantity_feed = true;

                        $actionType = $this->l('Update');
                        $actionIdType = 'SKU';
                        $actionIdCode = trim($combination['reference']);

                        printf('%s: %s %s %s - '.$this->l('Qty').': %s'."$cr", $actionType, $actionIdType, $actionIdCode, $productsUpdate[$u]['SKU'], 0);

                        $u++;
                    } elseif ($action == Amazon::UPDATE || $action == Amazon::ADD) {
                        $productsUpdate[$u]['SKU'] = trim($combination['reference']);

                        if (!isset($productsUpdate[$u]['SKU']) || empty($productsUpdate[$u]['SKU'])) {
                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('Skipping : %d - Has No SKU !', $details->id_product));
                            }

                            $skipped++;
                            continue;
                        }

                        // If this is a new product, quantity = 0 and deleteProducts is sets to On (configuration)
                        // Do not export the product to delete it the next time !
                        //
                        if ($action == Amazon::ADD && $params->authorizeToDelete && !$quantity && !$params->create) {
                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('Skipping : %d - Has a quantity of 0 and deleteProducts is sets to on', $details->id_product));
                            }

                            $skipped++;
                            continue;
                        }

                        if (!$generate_product_feed) {
                            if ($has_product_feed != true) {
                                $has_product_feed = false;
                            }
                            $productsUpdate[$u]['ProductIDType'] = null;
                            $productsUpdate[$u]['ProductIDCode'] = null;
                            $productsUpdate[$u]['NoProductFeed'] = true;

                            if ($has_product_feed != true) {
                                $has_product_feed = false;
                            }
                        } else {
                            $has_product_feed = true;

                            if ($params->asinOverride && $options['asin1'] && $p_code_exemption != Amazon::EXEMPTION_ATTR_EAN) {
                                $productsUpdate[$u]['ProductIDType'] = 'ASIN';
                                $productsUpdate[$u]['ProductIDCode'] = trim($options['asin1']);
                            } elseif (!$params->create && !$params->createOffers) {
                                // we do not force offer creation by default

                                $productsUpdate[$u]['ProductIDType'] = null;
                                $productsUpdate[$u]['ProductIDCode'] = null;
                            } elseif ($ps_code != 'reference' && $combination[$ps_code]) {
                                $productsUpdate[$u]['ProductIDType'] = $az_code;
                                $productsUpdate[$u]['ProductIDCode'] = trim($combination[$ps_code]);
                            }
                        }

                        if ($params->secondHand) {
                            $productCondition = isset($details->condition) && !empty($details->condition) && isset($params->conditionMap[$details->condition]) && !empty($params->conditionMap[$details->condition]) ? $params->conditionMap[$details->condition] : 'New';
                        } else {
                            $productCondition = 'New';
                        }

                        // Price for external repricing solutions
                        if (method_exists('Product', 'externalPrice')) {
                            $externalPrice = Product::externalPrice($params->id_lang, $id_product, $id_product_attribute, $this->name, $params->region);

                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('Product::externalPrice(%d, %d, %d, %s, %s) returned: %s', $params->id_lang, $id_product, $id_product_attribute, $this->name, $params->region, $externalPrice));
                            }

                            if (Validate::isPrice($externalPrice)) {
                                $productsUpdate[$u]['Price'] = $newPrice = $externalPrice;
                            }
                        }

                        $productsUpdate[$u]['ConditionType'] = $productCondition;
                        if ($productCondition != 'New') {
                            $productsUpdate[$u]['ConditionNote'] = $text;
                        }
                        $productsUpdate[$u]['Quantity'] = max(0, (int)$quantity);
                        $productsUpdate[$u]['Price'] = sprintf('%.02f', $newPrice);
                        $productsUpdate[$u]['NoPriceExport'] = $nopexport ? true : false;
                        $productsUpdate[$u]['NoQtyExport'] = $noqexport ? true : false;
                        $productsUpdate[$u]['Sales'] = $sales;
                        $productsUpdate[$u]['Business'] = $business_params;
                        $productsUpdate[$u]['id_product'] = $id_product;

                        if (!$nopexport) {
                            $has_price_feed = true;
                        }
                        if (!$noqexport) {
                            $has_quantity_feed = true;
                        }

                        // Product Sheet Elements
                        //
                        if ($generate_product_feed && $params->extendedDatas) {
                            if (!isset($productsUpdate[$u]['ProductDescription'])) {
                                $productsUpdate[$u]['ProductDescription'] = array();
                            }

                            $productsUpdate[$u]['ProductDescription']['Manufacturer'] = AmazonTools::encodeText(trim($manufacturer), $params->safeEncoding);

                            $master_name = trim(mb_substr($details->name, 0, Amazon::LENGTH_TITLE));

                            if (isset($details->name_attributes) && !empty($details->name_attributes) && $id_product_attribute) {
                                $standard_name = trim(mb_substr(sprintf('%s - %s', $details->name, rtrim($details->name_attributes, ', ')), 0, Amazon::LENGTH_TITLE));
                            } else {
                                $standard_name = $master_name;
                            }

                            switch ($params->titleFormat) {
                                case Amazon::FORMAT_MANUFACTURER_TITLE:
                                    if (!$manufacturer) {
                                        $name = $standard_name;
                                        break;
                                    }
                                    $master_name = trim(mb_substr(sprintf('%s - %s', $manufacturer, $details->name), 0, Amazon::LENGTH_TITLE));

                                    if (isset($details->name_attributes) && !empty($details->name_attributes) && $id_product_attribute) {
                                        $name = trim(mb_substr(sprintf('%s - %s - %s', $manufacturer, $details->name, rtrim($details->name_attributes, ', ')), 0, Amazon::LENGTH_TITLE));
                                    } else {
                                        $name = $master_name;
                                    }
                                    break;

                                case Amazon::FORMAT_MANUFACTURER_TITLE_REFERENCE:
                                    if (!$manufacturer) {
                                        $name = $standard_name;
                                        break;
                                    }
                                    $master_name = trim(mb_substr(sprintf('%s - %s - %s', $manufacturer, $details->name, trim($details->reference)), 0, Amazon::LENGTH_TITLE));

                                    if (isset($details->name_attributes) && !empty($details->name_attributes) && $id_product_attribute) {
                                        $name = trim(mb_substr(sprintf('%s - %s - %s - %s', $manufacturer, $details->name, trim($details->reference), rtrim($details->name_attributes, ', ')), 0, Amazon::LENGTH_TITLE));
                                    } else {
                                        $name = $master_name;
                                    }
                                    break;
                                default:
                                    $name = $standard_name;
                                    break;
                            }

                            if (method_exists('Product', 'externalName')) {
                                $productsUpdate[$u]['ProductDescription']['Title'] = AmazonTools::encodeText(trim(Product::externalName($params->id_lang, $id_product, $id_product_attribute, trim($name))), $params->safeEncoding);
                            } else {
                                $productsUpdate[$u]['ProductDescription']['Title'] = AmazonTools::encodeText(trim($name), $params->safeEncoding);
                            }

                            // 2014-02-27 : Added > Concat Description Short and Long
                            //
                            switch ($params->descriptionField) {
                                case Amazon::FIELD_DESCRIPTION_SHORT:
                                    $description = $details->{Amazon::FIELD_DESCRIPTION_SHORT};
                                    break;
                                case Amazon::FIELD_DESCRIPTION_BOTH:
                                    if (Tools::strlen($details->{Amazon::FIELD_DESCRIPTION_LONG}) && Tools::strlen($details->{Amazon::FIELD_DESCRIPTION_SHORT}) && !$short_description_used_as_bullet_point) {
                                        $description = $details->{Amazon::FIELD_DESCRIPTION_SHORT};
                                        $description .= nl2br(Amazon::LF) . nl2br(Amazon::LF);
                                        $description .= $details->{Amazon::FIELD_DESCRIPTION_LONG};
                                    } elseif (Tools::strlen($details->{Amazon::FIELD_DESCRIPTION_LONG})) {
                                        //
                                        $description = $details->{Amazon::FIELD_DESCRIPTION_LONG};
                                    } elseif (!$short_description_used_as_bullet_point) {
                                        //
                                        $description = $details->{Amazon::FIELD_DESCRIPTION_SHORT};
                                    }
                                    break;
                                case Amazon::FIELD_DESCRIPTION_NONE:
                                    $description = null;
                                    break;
                                default:
                                    $description = $details->{Amazon::FIELD_DESCRIPTION_LONG};
                                    break;
                            }

                            if ($params->sendHTMLDescriptions) {
                                // HTML Description
                                $description = trim($description);
                            } else {
                                // Text Description
                                $description = AmazonTools::cleanStripTags($description);
                            }

                            if (method_exists('Product', 'externalDescription')) {
                                $description = Tools::substr(Product::externalDescription($params->id_lang, $id_product, $params->descriptionField), 0, Amazon::LENGTH_DESCRIPTION);
                                $productsUpdate[$u]['ProductDescription']['Description'] = mb_substr(AmazonTools::encodeText($description, $params->safeEncoding), 0, Amazon::LENGTH_DESCRIPTION);
                            } else {
                                $productsUpdate[$u]['ProductDescription']['Description'] = mb_substr(AmazonTools::encodeText($description, $params->safeEncoding), 0, Amazon::LENGTH_DESCRIPTION);
                            }

                            if (count($bulletPoints)) {
                                $productsUpdate[$u]['ProductDescription']['BulletPoint'] = $bulletPoints;
                            }

                            if ($params->alternative_content && isset($options['alternative_title'])) {
                                $override_title = mb_substr(AmazonTools::encodeText($options['alternative_title'], $params->safeEncoding), 0, Amazon::LENGTH_DESCRIPTION);
                                $override_description = mb_substr(AmazonTools::encodeText($options['alternative_description'], $params->safeEncoding), 0, Amazon::LENGTH_DESCRIPTION);

                                if (Tools::strlen($override_title)) {
                                    $productsUpdate[$u]['ProductDescription']['Title'] = $override_title;
                                }
                                if (Tools::strlen($override_description)) {
                                    $productsUpdate[$u]['ProductDescription']['Description'] = $override_description;
                                }
                            }
                        }
                        // Send Images
                        //
                        if ($params->images && isset($product_data['ProductImage']) && is_array($product_data['ProductImage']) && count($product_data['ProductImage'])) {
                            if (!isset($productsUpdate[$u]['ProductData'])) {
                                $productsUpdate[$u]['ProductData'] = array();
                            }

                            $productsUpdate[$u]['ProductData']['ProductImage'] = $product_data['ProductImage'];
                            $images_count = count($product_data['ProductImage']);
                        }

                        // Product Sheet Creation
                        //
                        if ($generate_product_feed && $params->extendedDatas) {
                            if (!isset($productsUpdate[$u]['ProductDescription'])) {
                                $productsUpdate[$u]['ProductDescription'] = array();
                            }

                            if (isset($product_data['MerchantCatalogNumber'])) {
                                $productsUpdate[$u]['ProductDescription']['MerchantCatalogNumber'] = $product_data['MerchantCatalogNumber'];
                            }

                            // Gift Wrap and Gift Message (2014/10/15)
                            //
                            if ($gift_wrap) {
                                $productsUpdate[$u]['ProductDescription']['IsGiftWrapAvailable'] = 'true';
                                $productsUpdate[$u]['ProductDescription']['IsGiftMessageAvailable'] = $gift_message ? 'true' : false;
                            } else {
                                $productsUpdate[$u]['ProductDescription']['IsGiftWrapAvailable'] = 'false';
                                $productsUpdate[$u]['ProductDescription']['IsGiftMessageAvailable'] = 'false';
                            }

                            // In most cases it is same than manufacturer
                            if ($manufacturer) {
                                $productsUpdate[$u]['ProductDescription']['Brand'] = AmazonTools::encodeText(trim($manufacturer), $params->safeEncoding);
                            }

                            // 2016-04-11 Fix for LargeAppliance
                            if (isset($product_data['ProductName'])) {
                                $product_data['ProductName'] = $productsUpdate[$u]['ProductDescription']['Title'];
                            }
                            // 2016-04-11 Fix for LargeAppliance
                            if (isset($product_data['Manufacturer'])) {
                                $product_data['Manufacturer'] = AmazonTools::encodeText(trim($manufacturer), $params->safeEncoding);
                            }

                            if ($product_data) {
                                if (!isset($productsUpdate[$u]['ProductData'])) {
                                    $productsUpdate[$u]['ProductData'] = array();
                                }

                                $productsUpdate[$u]['ProductData'] = $product_data;
                            }

                            // 2013/03/16
                            // Arnaud Lempereur / Amazon France said:
                            // MfrPartNumber is not mandatory
                            // 2013/03/22
                            // Arnaud Lempereur / Amazon France said: MfrPartNumber is only mandatory for Office > PaperProducts
                            // 2013/03/29
                            // Arnaud Lempereur / Amazon France said: MfrPartNumber is mandatory also for Auto Accessory
                            //

                            if (!isset($product_data['MfrPartNumber']) && in_array($p_product_type, AmazonXSD::$requireMfrPartNumber)) {
                                if (empty($supplier_reference)) {
                                    $p_sku_as_supplier_reference = true;
                                } else {
                                    $productsUpdate[$u]['ProductDescription']['MfrPartNumber'] = $supplier_reference;
                                }
                            } elseif (isset($product_data['MfrPartNumber'])) {
                                // GCID from Custom File

                                $productsUpdate[$u]['ProductDescription']['MfrPartNumber'] = $product_data['MfrPartNumber'];
                            }

                            if ($p_sku_as_supplier_reference && $p_sku_as_sup_ref_unconditionnaly) {
                                $productsUpdate[$u]['ProductDescription']['MfrPartNumber'] = $combination['reference'];
                            } elseif ($p_sku_as_supplier_reference && empty($supplier_reference)) {
                                $productsUpdate[$u]['ProductDescription']['MfrPartNumber'] = $combination['reference'];
                            } elseif ($p_sku_as_supplier_reference && !empty($supplier_reference)) {
                                $productsUpdate[$u]['ProductDescription']['MfrPartNumber'] = $supplier_reference;
                            }

                            // Fill Item Type for U.S. or BrowseNode for Europe - 2014/03/13
                            //
                            if (AmazonTools::isUSMarketplaceId($params->marketplaceID) && !empty($p_item_type)) {
                                $productsUpdate[$u]['ProductDescription']['ItemType'] = $p_item_type;
                            } elseif ($browsenode_override) {
                                $productsUpdate[$u]['ProductDescription']['RecommendedBrowseNode'] = $browsenode_override;
                            } elseif (!empty($p_browsenode)) {
                                $productsUpdate[$u]['ProductDescription']['RecommendedBrowseNode'] = $p_browsenode;
                            }

                            if (!empty($shipping_group)) {
                                $productsUpdate[$u]['ProductDescription']['MerchantShippingGroupName'] = $shipping_group;
                            }

                            // Product Tag
                            //
                            $productsUpdate[$u]['ProductDescription']['SearchTerms'] = AmazonTag::getMarketplaceTags($details, $id_lang);

                            // Product Weight
                            //
                            if ($params->weightUnit && (float)$product_weight) {
                                switch (Tools::strtoupper($params->weightUnit)) {
                                    case 'GR':
                                    case 'KG':
                                    case 'OZ':
                                    case 'LB':
                                    case 'MG':
                                        $unit = $params->weightUnit;
                                        if ((float)$product_weight - (float)$params->shippingTare) {
                                            $productsUpdate[$u]['ProductDescription']['PackageWeight'] = number_format((float)$product_weight - (float)$params->shippingTare, 2, '.', '');
                                            $productsUpdate[$u]['ProductDescription']['PackageWeightUnit'] = $unit;
                                        }
                                        // Note: maybe we have to add handling weight/packing weight to this value
                                        $productsUpdate[$u]['ProductDescription']['ShippingWeight'] = number_format((float)$product_weight, 2, '.', '');
                                        $productsUpdate[$u]['ProductDescription']['ShippingWeightUnit'] = $unit;
                                        break;
                                    // case another one : change the value to a known amz value
                                    default:
                                        $this->errorOutput(sprintf($this->l('Warning : %d - Amazon couldn\'t match this weight unit: %s for product: %d')."$cr", $u, $params->weightUnit, $details->id));
                                        break;
                                }
                            }

                            $product_has_length = false;
                            $product_has_width = false;
                            $product_has_height = false;
                            $product_has_weight = false;

                            // Product Dimension are filled in the profile
                            foreach (array('Length', 'Width', 'Height', 'Weight') as $dimension) {
                                foreach (array('ItemDisplay', '') as $master_dimension) {
                                    $target_dimension = $master_dimension.$dimension;

                                    if (isset($product_data[$target_dimension]) && isset($product_data['Parameters'][$target_dimension]) && isset($product_data['Attributes'][$target_dimension]) && is_array($product_data['Attributes'][$target_dimension])) {
                                        $dimension_lowered = Tools::strtolower($dimension);

                                        $dimension_value = number_format((float)$product_data[$target_dimension], 2, '.', '');
                                        $dimension_unit = $product_data['Attributes'][$target_dimension]['unitOfMeasure'];
                                        if ($dimension_unit && $dimension_value) {
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions'][$dimension] = array();
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions'][$dimension]['unitOfMeasure'] = $dimension_unit;
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions'][$dimension]['value'] = number_format((float)$dimension_value, 2, '.', '');

                                            $product_has_{$dimension_lowered}
                                            = true;
                                        }
                                    }
                                }
                            }

                            // Product Dimension
                            //
                            $package_length = number_format((float)$details->depth, 2, '.', '');
                            $package_height = number_format((float)$details->height, 2, '.', '');
                            $package_width = number_format((float)$details->width, 2, '.', '');

                            if ($params->dimensionUnit && ((float)$package_length && (float)$package_height && (float)$package_width)) {
                                $unit = Tools::strtoupper($params->dimensionUnit);

                                switch ($unit) {
                                    case 'CM':
                                    case 'MM':
                                    case 'M':
                                    case 'IN':
                                    case 'FT':
                                    case 'inches':
                                    case 'feet':
                                    case 'meters':
                                    case 'decimeters':
                                    case 'centimeters':
                                    case 'millimeters':
                                    case 'micrometers':
                                    case 'nanometers':
                                    case 'picometers':
                                        if (!isset($productsUpdate[$u]['ProductDescription']['ItemDimensions'])) {
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions'] = array();
                                        }
                                        if (!isset($productsUpdate[$u]['ProductDescription']['PackageDimensions'])) {
                                            $productsUpdate[$u]['ProductDescription']['PackageDimensions'] = array();
                                        }
                                        break;
                                    // case another one : change the value to a known amz value
                                    default:
                                        $this->errorOutput(sprintf($this->l('Warning : %d - Amazon couldn\'t match this weight unit: %s for product: %d')."$cr", $u, $params->weightUnit, $details->id));
                                        break;
                                }


                                if (isset($productsUpdate[$u]['ProductDescription']['PackageDimensions'])) {
                                    $productsUpdate[$u]['ProductDescription']['PackageDimensions']['Length'] = array();
                                    $productsUpdate[$u]['ProductDescription']['PackageDimensions']['Length']['unitOfMeasure'] = $unit;
                                    $productsUpdate[$u]['ProductDescription']['PackageDimensions']['Length']['value'] = number_format((float)$package_length, 2, '.', '');

                                    $productsUpdate[$u]['ProductDescription']['PackageDimensions']['Width'] = array();
                                    $productsUpdate[$u]['ProductDescription']['PackageDimensions']['Width']['unitOfMeasure'] = $unit;
                                    $productsUpdate[$u]['ProductDescription']['PackageDimensions']['Width']['value'] = number_format((float)$package_width, 2, '.', '');

                                    $productsUpdate[$u]['ProductDescription']['PackageDimensions']['Height'] = array();
                                    $productsUpdate[$u]['ProductDescription']['PackageDimensions']['Height']['unitOfMeasure'] = $unit;
                                    $productsUpdate[$u]['ProductDescription']['PackageDimensions']['Height']['value'] = number_format((float)$package_height, 2, '.', '');
                                }

                                if ($params->shippingGauge && isset($productsUpdate[$u]['ProductDescription']['ItemDimensions']) && !count($productsUpdate[$u]['ProductDescription']['ItemDimensions'])) {
                                    if (max((float)$package_length - (float)$params->shippingGauge, number_format((float)$package_length - (float)$params->shippingGauge, (float)$package_height - (float)$params->shippingGauge, '.', ''))) {
                                        if (!$product_has_length) {
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions']['Length'] = array();
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions']['Length']['unitOfMeasure'] = $unit;
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions']['Length']['value'] = number_format((float)$package_length - (float)$params->shippingGauge, 2, '.', '');
                                        }

                                        if (!$product_has_width) {
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions']['Width'] = array();
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions']['Width']['unitOfMeasure'] = $unit;
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions']['Width']['value'] = number_format((float)$package_width - (float)$params->shippingGauge, 2, '.', '');
                                        }

                                        if (!$product_has_height) {
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions']['Height'] = array();
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions']['Height']['unitOfMeasure'] = $unit;
                                            $productsUpdate[$u]['ProductDescription']['ItemDimensions']['Height']['value'] = number_format((float)$package_height - (float)$params->shippingGauge, 2, '.', '');
                                        }
                                    }
                                }
                            }
                        }

                        if ($params->shippingAllowOverride) {
                            // Shipping Override
                            //
                            if ($params->deleteShippingOverrides) {
                                $productsUpdate[$u]['ShippingPrice'] = '';
                                $productsUpdate[$u]['ShippingOption'] = $shipping_type == 2 ? $params->shippingOverrideExps : $params->shippingOverrideStds;
                                $productsUpdate[$u]['ShippingType'] = 'Exclusive';

                                if ($params->deleteConfirmed) {
                                    AmazonProduct::updateProductOptions($id_product, $id_lang, 'shipping', null);
                                }

                                $shipping_text = sprintf('- '.$this->l('Delete Shipping Charges Overrides'));
                            } elseif ($shipping !== null && is_numeric($shipping)) {
                                $shipping_override_option = $shipping_type == 2 ? $params->shippingOverrideExps : $params->shippingOverrideStds;

                                if (empty($shipping_override_option)) {
                                    $this->errorOutput(sprintf($this->l('Warning : Incontistency - You configured a shipping override but there are no associated mappings in the module configuration for this region')), self::WARNING);
                                    $shipping_text = null;
                                } else {
                                    $productsUpdate[$u]['ShippingPrice'] = $shipping;
                                    $productsUpdate[$u]['ShippingOption'] = $shipping_type == 2 ? $params->shippingOverrideExps : $params->shippingOverrideStds;
                                    $productsUpdate[$u]['ShippingType'] = 'Exclusive';

                                    $shipping_text = sprintf('- '.$this->l('Overriding Shipping Charges: %s'), $shipping);
                                }
                            } elseif ($shipping === null) {
                                $productsUpdate[$u]['ShippingPrice'] = '';
                                $productsUpdate[$u]['ShippingOption'] = $shipping_type == 2 ? $params->shippingOverrideExps : $params->shippingOverrideStds;
                                $productsUpdate[$u]['ShippingType'] = 'Exclusive';

                                if (Amazon::$debug_mode) {
                                    $shipping_text = sprintf('- '.$this->l('Overriding Shipping Charges: void'), $shipping);
                                } else {
                                    $shipping_text = '';
                                }
                            } else {
                                $shipping_text = null;
                            }

                            if (Amazon::$debug_mode) {
                                CommonTools::p("Shipping Overrides: Active");
                                CommonTools::p(sprintf('ShippingPrice: %s', $productsUpdate[$u]['ShippingPrice']));
                                CommonTools::p(sprintf('ShippingOption: %s', $productsUpdate[$u]['ShippingOption']));
                                CommonTools::p(sprintf('ShippingType: %s', $productsUpdate[$u]['ShippingType']));
                            }
                        } elseif ($params->smartShipping && $params->smartShippingOption) {
                            if (Amazon::$debug_mode) {
                                CommonTools::p("Smart Shipping:");
                                CommonTools::p($params->smartShipping);
                                CommonTools::p($params->smartShippingOption);
                            }
                            // Shipping Override
                            //
                            if ($params->deleteShippingOverrides) {
                                $productsUpdate[$u]['ShippingPrice'] = '';
                                $productsUpdate[$u]['ShippingOption'] = $params->smartShippingOption;
                                $productsUpdate[$u]['ShippingType'] = $params->smartShippingMethod ? $params->smartShippingMethod : 'Additive';

                                $shipping_text = sprintf('- '.$this->l('Delete Shipping Override'));
                            } else {
                                $shipping_cost = AmazonCarrier::shippingQuoteByWeight($product_weight, $params->id_carrier, $params->id_address, $params->useTax);

                                if (Amazon::$debug_mode) {
                                    CommonTools::p("Smart Shipping:");
                                    CommonTools::p(sprintf('weight: %s', $product_weight));
                                    CommonTools::p(sprintf('id_carrier: %s', $params->id_carrier));
                                    CommonTools::p(sprintf('id_address: %s', $params->id_address));
                                    CommonTools::p(sprintf('useTax: %s', $params->useTax));
                                }

                                if ($shipping_cost !== null) {
                                    if ($params->fromCurrency->iso_code != $params->toCurrency->iso_code) {
                                        $shipping_cost = Tools::convertPrice($shipping_cost, $params->toCurrency);
                                    }

                                    $shipping_text = sprintf('- '.$this->l('Using Smart Shipping: %.2f'), $shipping_cost);

                                    $productsUpdate[$u]['ShippingOption'] = $params->smartShippingOption;
                                    $productsUpdate[$u]['ShippingPrice'] = number_format($shipping_cost, 2, '.', '');
                                    $productsUpdate[$u]['ShippingType'] = $params->smartShippingMethod ? $params->smartShippingMethod : 'Additive';
                                } else {
                                    $shipping_text = '';
                                }
                            }
                            if (Amazon::$debug_mode) {
                                CommonTools::p("Smart Shipping values: Active");
                                CommonTools::p(sprintf('ShippingPrice: %s', $productsUpdate[$u]['ShippingPrice']));
                                CommonTools::p(sprintf('ShippingOption: %s', $productsUpdate[$u]['ShippingOption']));
                                CommonTools::p(sprintf('ShippingType: %s', $productsUpdate[$u]['ShippingType']));
                            }
                        } else {
                            $shipping_text = null;
                        }

                        if ($params->FBA && $fba) {
                            $fba_text = '- '.$this->l('FBA');
                        } else {
                            $fba_text = '';
                        }

                        // Options
                        //
                        if ($latency) {
                            $productsUpdate[$u]['FulfillmentLatency'] = $latency;
                        }

                        if ($params->FBA && $fba) {
                            $productsUpdate[$u]['FBA'] = $params->fullfillmentCenterId;
                        }

                        $actionType = $profile_name && $params->create ? $this->l('Create') : $this->l('Update');

                        if ($params->asinOverride) {
                            $actionIdType = 'ASIN';
                            $actionIdCode = trim($options['asin1']);
                        } else {
                            $actionIdType = isset($productsUpdate[$u]['ProductIDType']) ? $productsUpdate[$u]['ProductIDType'] : null;
                            $actionIdCode = isset($productsUpdate[$u]['ProductIDCode']) ? $productsUpdate[$u]['ProductIDCode'] : null;
                        }
                        $sent_quantity = isset($productsUpdate[$u]['Quantity']) ? ((bool)$noqexport ? 'n/a' : $productsUpdate[$u]['Quantity']) : 'n/a';
                        $sent_price = (bool)$nopexport ? 'n/a' : Tools::displayPrice($newPrice, $params->toCurrency);

                        printf('%s: %s %s %s - '.$this->l('Qty').': %s - '.$this->l('Price').': %s %s '.$this->l('Sent').' %s %s %s'."$cr", $actionType, $actionIdType, $actionIdCode, $productsUpdate[$u]['SKU'], $sent_quantity, $params->fromCurrency->iso_code, Tools::displayPrice($stdPrice, $params->fromCurrency), $sent_price, $fba_text, $shipping_text);

                        // Create the master product for Variations
                        // NOTE : isset($product_data['Variation']) && $product_data['Variation'] added on 2013/05/15 - do not create parent/child relation if there is not variation data
                        if ($params->create && $masterProduct && $variant && $productIndex == 1 && isset($product_data['Variation']) && $product_data['Variation']) {
                            $firstProduct = $productsUpdate[$u];

                            $productsUpdate[$u]['SKU'] = trim($details->reference);
                            $productsUpdate[$u]['ProductData']['Parentage'] = 'parent';

                            if (method_exists('Product', 'externalName')) {
                                $productsUpdate[$u]['ProductDescription']['Title'] = AmazonTools::encodeText(trim(Product::externalName($params->id_lang, $id_product, $id_product_attribute, $master_name)), $params->safeEncoding);
                            } else {
                                $productsUpdate[$u]['ProductDescription']['Title'] = $master_name;
                            }

                            if (isset($product_data['MfrPartNumber']) && Tools::strlen($product_data['MfrPartNumber'])) {
                                $productsUpdate[$u]['ProductDescription']['MfrPartNumber'] = $details->reference;
                            }
                            // Unset unwanted tags for parent
                            //
                            foreach ($removeTagForParent as $tagName) {
                                if (isset($productsUpdate[$u]['ProductData'][$tagName])) {
                                    unset($productsUpdate[$u]['ProductData'][$tagName]);
                                }
                                if (isset($productsUpdate[$u]['ProductDescription'][$tagName])) {
                                    unset($productsUpdate[$u]['ProductDescription'][$tagName]);
                                }
                            }

                            foreach (array('ConditionType', 'ConditionNote', 'Quantity', 'Price', 'ShippingOption', 'ShippingType', 'ProductIDType', 'ProductIDCode') as $tagName) {
                                unset($productsUpdate[$u][$tagName]);
                            }
                            unset($removeTagForParent);

                            // Do not export quantity and price for parent
                            $productsUpdate[$u]['NoPriceExport'] = true;
                            $productsUpdate[$u]['NoQtyExport'] = true;

                            $u++;

                            $productsUpdate[$u] = $firstProduct;
                        }
                        $synchProductsIds[] = $id_product;
                        $u++;
                    }
                }

                $i++;
            }
        }
        echo $cr;

        $mwsSuccess = false;

        // Action For Acknowledge
        if ($params->create) {
            $action = Amazon::ADD;
        } elseif ($params->deleteProducts) {
            $action = Amazon::REMOVE;
        } else {
            $action = Amazon::UPDATE;
        }

        // For customer debug purpose
        //
        if ($params->xmlOnly) {
            if (!$params->sendToAmazon) {
                $this->_amazonApi->MWS_Action = AmazonWebService::MWS_DO_NOT_SEND;
            }
            $this->_amazonApi->displayXML = true;
        }

        if (!count($productsUpdate)) {
            $message = AmazonSupport::message($this->l('No products to send on Amazon'), AmazonSupport::FUNCTION_EXPORT_NO_PRODUCT);
            $this->errorOutput($message, self::NOTICE);
            //
        } else {
            if (Amazon::$debug_mode && isset($productsUpdate)) {
                CommonTools::p("Memory:");
                CommonTools::p("Used:".memory_get_usage(true) / (1024 * 1024));
                CommonTools::p("Peak:".memory_get_peak_usage(true) / (1024 * 1024));
                CommonTools::p("Product Data:");
                CommonTools::p(print_r($productsUpdate, true));
            }
            if ($params->sendToAmazon || $params->xmlOnly) {
                if ($params->relationShipsOnly && count($Relationship)) {
                    printf($this->l('Updating relationships only - %s items').$cr, count($productsUpdate));

                    if (!$datas = $this->_amazonApi->updateRelationships($Relationship)) {
                        if (!$params->xmlOnly) {
                            printf('%s/%s: '.$this->l('Error : query failed (%s)'.$cr), basename(__FILE__), __LINE__, print_r($datas, true));
                        }
                    }

                    if (Amazon::$debug_mode) {
                        CommonTools::p("Returned Data:");
                        CommonTools::p($datas);
                    }
                } else {
                    if ($params->delete) {
                        printf(date('c') . ' - ' . $this->l('%s products to delete on Amazon').$cr, count($productsUpdate));
                    } elseif ($params->create) {
                        printf(date('c') . ' - ' . $this->l('%s products to create on Amazon').$cr, count($productsUpdate));
                    } else {
                        printf(date('c') . ' - ' . $this->l('%s products to synchronize with Amazon').$cr, count($productsUpdate));
                    }

                    // Operation Override
                    if ($params->delete) {
                        $this->_amazonApi->setOperationMode(AmazonWebService::OPERATIONS_DELETE);
                    }

                    if (!$datas = $this->_amazonApi->updateProducts($productsUpdate, $Relationship, $has_product_feed, $has_quantity_feed, $has_price_feed, $params->images)) {
                        if (!$params->xmlOnly) {
                            printf('%s/%s: '.$this->l('Error : query failed (%s)'.$cr), basename(__FILE__), __LINE__, print_r($datas, true));
                        }
                    }
                }
                if (Amazon::$debug_mode) {
                    CommonTools::p("Returned Data:");
                    CommonTools::p($datas);
                }

                $mwsSuccess = true;

                if (isset($datas['products']) && (int)$datas['products']) {
                    $mwsSuccess = $mwsSuccess && true;
                    printf($this->l('Products Feed - Submission ID: %s').$cr, $datas['products']);

                    // Product is being created, mark it as to be sent (offers)
                    if ($params->create && count($productsUpdate)) {
                        foreach ($productsUpdate as $product_item) {
                            if (isset($product_item['id_product'])) {
                                AmazonProduct::marketplaceActionSet(Amazon::UPDATE, $product_item['id_product'], null, null, $id_lang);
                            }
                        }
                    }
                } else {
                    $mwsSuccess = $mwsSuccess && false;
                }

                if (isset($datas['inventory']) && (int)$datas['inventory']) {
                    printf($this->l('Inventory Feed - Submission ID: %s').$cr, $datas['inventory']);
                } else {
                    $mwsSuccess = $mwsSuccess && false;
                }

                if (Amazon::$debug_mode) {
                    CommonTools::p("Returned Submission IDs:");
                    CommonTools::p($datas);
                }

                if (isset($datas['prices']) && (int)$datas['prices']) {
                    printf($this->l('Price Feed - Submission ID: %s').$cr, $datas['prices']);
                }

                if (isset($datas['overrides']) && (int)$datas['overrides']) {
                    printf($this->l('Overrides Feed - Submission ID: %s').$cr, $datas['overrides']);
                }

                if (isset($datas['images']) && (int)$datas['images']) {
                    printf($this->l('Images Feed - Submission ID: %s').$cr, $datas['images']);
                }

                if (isset($datas['relations']) && (int)$datas['relations']) {
                    printf($this->l('Relationship Feed - Submission ID: %s').$cr, $datas['relations']);
                }

                if (is_array($datas) && count($datas)) {
                    if ($params->create && $params->cronMode) {
                        $batch_key = 'batch_products_cron';
                    } elseif ($params->create && !$params->cronMode) {
                        $batch_key = 'batch_products';
                    } elseif (!$params->create && $params->cronMode) {
                        $batch_key = 'batch_offers_cron';
                    } else {
                        $batch_key = 'batch_offers';
                    }

                    $batches = new AmazonBatches($batch_key);

                    // Save Batches - 1 per Feed
                    foreach (array_keys($datas) as $feedtype) {
                        if (!is_numeric($datas[$feedtype])) {
                            continue;
                        }

                        if ($feedtype == 'images') {
                            $count = $images_count;
                        } elseif ($feedtype == 'relations') {
                            $count = $relations;
                        } else {
                            $count = count($productsUpdate);
                        }

                        $batch = new AmazonBatch($params->timestart);
                        $batch->id = $datas[$feedtype];
                        $batch->timestop = time();
                        $batch->type = $feedtype;
                        $batch->region = $params->region;
                        $batch->created = $params->create ? $count : 0;
                        $batch->updated = !$params->create ? $count : 0;
                        $batch->deleted = count($productsDelete);

                        if (Amazon::$debug_mode) {
                            CommonTools::p("Batch:");
                            CommonTools::p($batch);
                        }

                        $batches->add($batch);
                    }
                    $batches->save();
                }

                // We sucessfully exports to Amazon
                //
                if ($mwsSuccess === true) {
                    printf($this->l('Products were successfully submitted to Amazon...').$cr);

                    // Update Marketplace Product Action Table
                    //
                    if (!$params->xmlOnly && is_array($synchProductsIds) && count($synchProductsIds)) {
                        AmazonProduct::marketplaceActionAcknowledgde($action, $params->id_lang, $synchProductsIds, $params->currentDate);
                    }
                } elseif (!$params->sendToAmazon && $params->xmlOnly) {
                    printf($this->l('Display XML only...').$cr);
                } else {
                    printf($this->l('Products: Nothing to Update !').$cr);
                }
            } else {
                printf(date('c') . ' - ' . $this->l('%s products to synchronize with Amazon').$cr, count($productsUpdate));

            }
        }

        if ($params->sendToAmazon && !count($productsDelete)) {
            printf($this->l('No products to delete').$cr);
        } elseif ($params->deleteProducts || $params->authorizeToDelete) {
            if (Amazon::$debug_mode) {
                echo print_r($productsDelete, true);
            }

            if ($params->sendToAmazon || $params->xmlOnly) {
                if (!($datas = $this->_amazonApi->deleteProducts($productsDelete))) {
                    if (!$params->xmlOnly) {
                        printf($this->l('%s/%s: '.'Error : query failed (%s)'.$cr), basename(__FILE__), __LINE__, print_r($datas));
                    }
                    if (Amazon::$debug_mode) {
                        CommonTools::p("Returned Data:");
                        CommonTools::p($datas);
                    }
                } else {
                    // Update Marketplace Product Action Table
                    //
                    if (!$params->xmlOnly && is_array($synchProductsIds) && count($synchProductsIds)) {
                        AmazonProduct::marketplaceActionAcknowledgde($action, $params->id_lang, $synchProductsIds, $params->currentDate);
                    }

                    printf($this->l('%s deleted products').$cr, count($productsDelete));
                }
            } elseif (!$params->sendToAmazon && $params->xmlOnly) {
                printf($this->l('Display XML only...').$cr);
            } else {
                printf($this->l('%s products to delete on Amazon').$cr, count($productsDelete));
            }
        }

        if ($skipped) {
            printf($this->l('%d skipped products').$cr, $skipped);
        }

        if ($params->sendToAmazon) {
            if (!$i) {
                printf($this->l('No products to export !').$cr);
            } elseif ($params->sendToAmazon && !$params->deleteProducts) {
                printf($this->l('%s products sent').$cr, count($productsUpdate));
            }

            if (isset($duplicate) && $duplicate) {
                printf($this->l('%s duplicated products').$cr, $duplicate);
            }
        }


        // Save Session
        $batches = new AmazonBatches($params->create ? 'session_products' : 'session_offers');
        $batch = new AmazonBatch($params->timestart);
        $batch->id = uniqid();
        $batch->timestop = time();
        $batch->type = $params->cronMode ? $this->l('Cron') : $this->l('Interactive');
        $batch->region = $params->region;
        $batch->created = $mwsSuccess && $params->create ? count($productsUpdate) : 0;
        $batch->updated = $mwsSuccess && !$params->create ? count($productsUpdate) : 0;
        $batch->deleted = $mwsSuccess ? count($productsDelete) : 0;
        $batches->add($batch);
        $batches->save();
        $elapsed = $batch->timestop - $params->timestart;

        printf('%.02f"'.$cr, $elapsed);
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $this->id_lang));
    }

    public function errorOutput($message, $level = false)
    {
        if ($level == self::NOTICE) {
            $color = 'magenta';
        } elseif ($level == self::WARNING) {
            $color = 'orange';
        } elseif ($level == self::ERROR) {
            $color = 'red';
        } else {
            $color = 'black';
        }

        if ($level && (!isset($this->params->cronMode) || !$this->params->cronMode)) {
            printf(html_entity_decode("&lt;pre style='color:%s;'&gt;"), $color);
        }

        print $message;

        if ($level && (!isset($this->params->cronMode) || !$this->params->cronMode)) {
            echo html_entity_decode('&lt;/pre&gt;');
        }
    }

    public function search_key($needle_key, &$object)
    {
        foreach (get_object_vars($object) as $key=>$value) {
            if ($key == $needle_key) {
                return $value;
            }
            if (is_object($value)) {
                if (($result = $this->search_key($needle_key, $value)) !== false) {
                    return $result;
                }
            } else {
                if ($key == $needle_key) {
                    return($value);
                }
            }
        }
        return false;
    }
    public function validateField(&$productInstance, &$xsd_array, $p_universe, $p_product_type, $field, $value)
    {
        $target_definition = $this->search_key($field, $productInstance);

        if ($target_definition instanceof stdClass) {
            if (property_exists($target_definition, 'attr') && property_exists($target_definition->attr, 'unitOfMeasure')) {
                return((float)$value);
            }
            $target_definition->{$field} = $value;

            if ($target_definition->type == 'dateTime') {
                if ($timestamp = strtotime($value)) {
                    $iso_date = date('c', $timestamp);

                    if (AmazonTools::isIso8601Date($iso_date)) {
                        return ($iso_date);
                    }
                }

                return (false);
            } elseif ($target_definition->type == 'number') {
                // this is a number, we filter alpha chars
                $value = preg_replace('/[^0-9\.]/', '', str_replace(',', '.', $value));

                if (property_exists($target_definition, 'minValue') && is_numeric($target_definition->minValue) && $target_definition->minValue > 0) {
                    if ($value < $target_definition->minValue) {
                        return (false);
                    }
                }
                if (property_exists($target_definition, 'maxValue') && is_numeric($target_definition->maxValue) && $target_definition->maxValue > 0) {
                    if ($value > $target_definition->maxValue) {
                        return (false);
                    }
                }
            } else {
                if (property_exists($target_definition, 'minLength') && is_numeric($target_definition->minLength) && $target_definition->minLength > 0) {
                    if (Tools::strlen($value) < $target_definition->minLength) {
                        return (false);
                    }
                }
                if (property_exists($target_definition, 'maxLength') && is_numeric($target_definition->maxLength) && $target_definition->maxLength > 0) {
                    if (Tools::strlen($value) > $target_definition->maxLength) {
                        $value = Tools::substr($value, 0, $target_definition->maxLength);
                    }
                }
            }
        }

        return ($value);
    }


    public function getMappingValue($field, &$combination, &$p_extra, $p_key)
    {
        static $valid_values = array();
        $mapping = array_fill_keys(array(
            'id_attribute',
            'id_feature_value',
            'value',
            'mapping',
            'required',
            'default',
            'fixed',
            'has_mapping',
            'is_missing',
            'is_variant_field',
            'is_map_required',
            'is_multiple'
        ), null);


        if (Amazon::$debug_mode) {
            CommonTools::p("Field: ".$field);
            CommonTools::p("Combination: ".print_r($combination, true));
        }
        if (!is_array($p_extra) || !count($p_extra)) {
            return ($mapping);
        }

        $field_value = isset($p_extra['field'][$field]) ? $p_extra['field'][$field] : null;
        $required = isset($p_extra['required']) && isset($p_extra['required'][$field]) ? true : false;
        $p_universe = $p_extra['universe'];
        $variation_field_list = array();

        if (isset($p_extra['variant']) && Tools::strlen($p_extra['variant'])) {
            $variation_theme = $p_extra['variant'];

            if (strpos($variation_theme, '-')) {
                $variation_fields = explode('-', $variation_theme);

                $variation_field_list = $variation_fields;
            } else {
                $variation_field_list = array($variation_theme);
            }

            if (in_array($field, $variation_field_list)) {
                $required = true;
                $mapping['is_variant_field'] = true;
            }
        }

        if (in_array($field, array('Color', 'ColorName', 'Size', 'SizeName')) && in_array($p_universe, AmazonXSD::$isMapRequired)) {
            $mapping['is_map_required'] = true;
        }

        $selected_attribute = (int)AmazonSpecificField::getAttributeId($field_value);
        $selected_feature = (int)AmazonSpecificField::getFeatureId($field_value);
        $selected_field = (int)AmazonSpecificField::getFieldId($field_value);
        $has_fixed = (bool)AmazonSpecificField::isFixed($field_value);
        $has_default = (bool)AmazonSpecificField::isDefault($field_value);
        $has_allowed = (bool)AmazonSpecificField::isAllowedValue($field_value);

        $params = &$this->params;
        $id_lang = &$this->params->id_lang;

        if (isset($params->mapping['ungroup']) && (bool)$params->mapping['ungroup']) {
            $mapping_key = $p_key;
        } else {
            $mapping_key = sprintf('%s/%s', $p_universe, $field);
        }

        $default_value = null;

        if (!max($selected_attribute, $selected_feature, $has_fixed, $has_default, $has_allowed, $required, $selected_field)) {
            return ($mapping);
        }

        $mapping['field'] = $field;
        $mapping['required'] = $required;
        if ($has_allowed && isset($p_extra['allowed_value']) && isset($p_extra['allowed_value'][$field]) && Tools::strlen($p_extra['allowed_value'][$field])) {
            $default_value = $p_extra['allowed_value'][$field];
            $mapping['is_multiple'] = isset($p_extra['allowed_values_multiple'][$field]) ? $p_extra['allowed_values_multiple'][$field] : false;
        } elseif ($has_default && isset($p_extra['default']) && isset($p_extra['default'][$field]) && Tools::strlen($p_extra['default'][$field])) {
            $default_value = $p_extra['default'][$field];
        } else {
            $has_default = false;
        }

        if ($has_fixed) {
            $has_fixed_mapping = isset($params->mapping['fixed'][$id_lang]) && isset($params->mapping['fixed'][$id_lang][$mapping_key]) && isset($params->mapping['fixed'][$id_lang][$mapping_key][$field]);

            if ($has_fixed_mapping && Tools::strlen($params->mapping['fixed'][$id_lang][$mapping_key][$field])) {
                $mapping['mapping'] = $mapping['value'] = AmazonTools::encodeText($params->mapping['fixed'][$id_lang][$mapping_key][$field]);
                $mapping['has_mapping'] = true;
            } elseif ($has_fixed_mapping) {
                $mapping['is_missing'] = true;
            }
        } elseif ($selected_attribute) {
            $has_target_attribute = isset($combination['attributes']) && isset($combination['attributes'][$selected_attribute]) && is_array($combination['attributes'][$selected_attribute]) && count($combination['attributes'][$selected_attribute]);


            if ($has_target_attribute) {
                $array_key = key($combination['attributes'][$selected_attribute]);
                $target_attribute_array = &$combination['attributes'][$selected_attribute][$array_key];
                $target_attribute = $target_attribute_array['name'];
                $attribute_key = AmazonTools::toKey($target_attribute);
                $target_id_attribute = key($combination['attributes'][$selected_attribute]);

                $has_const_mapping = isset($params->mapping['attributes']['const']) && isset($params->mapping['attributes']['const'][$id_lang]);
                $has_free_mapping = isset($params->mapping['attributes']['free']) && isset($params->mapping['attributes']['free'][$id_lang]);

                $target_mapping_value = null;
                $target_mapping_index = null;
                $has_target_mapping_attribute = null;

                if (!(isset($valid_values[$p_universe]) && isset($valid_values[$p_universe][$field]))) {
                    $valid_values[$p_universe][$field] = AmazonValidValues::getValidValues($p_universe, $field, $params->region);
                }

                if ($has_const_mapping) {
                    $has_target_mapping_group = is_array($params->mapping['attributes']['const'][$id_lang]) && isset($params->mapping['attributes']['const'][$id_lang][$mapping_key]) && is_array($params->mapping['attributes']['const'][$id_lang][$mapping_key]);

                    if ($has_target_mapping_group) {
                        $has_target_mapping_attribute = isset($params->mapping['attributes']['const'][$id_lang][$mapping_key][$selected_attribute]) && isset($params->mapping['attributes']['const'][$id_lang][$mapping_key][$selected_attribute][$target_id_attribute]);

                        if ($has_target_mapping_attribute) {
                            $target_mapping_index = $params->mapping['attributes']['const'][$id_lang][$mapping_key][$selected_attribute][$target_id_attribute];
                        }

                        if (isset($valid_values[$p_universe][$field][$target_mapping_index])) {
                            $target_mapping_value = $valid_values[$p_universe][$field][$target_mapping_index];
                        }
                    }

                    if (!Tools::strlen($target_mapping_value)) {
                        // Search for an unmapped attribute but matching a valid value
                        if (isset($valid_values[$p_universe][$field][$attribute_key])) {
                            $has_target_mapping_attribute = true;
                            $target_mapping_value = $valid_values[$p_universe][$field][$attribute_key];
                        }

                        if (!Tools::strlen($target_mapping_value) && in_array($field, array('Color', 'ColorName'))) {
                            $target_mapping_value = AmazonXSD::getStandardColor($target_attribute, $params->region);
                            $has_target_mapping_attribute = Tools::strlen($target_mapping_value);

                            if (!$has_target_mapping_attribute) {
                                $target_mapping_value = AmazonXSD::getStandardColor($target_attribute, 'en');
                                $has_target_mapping_attribute = Tools::strlen($target_mapping_value);
                            }
                        }
                    }
                } else {
                    // Search for an unmapped attribute but matching a valid value
                    if (isset($valid_values[$p_universe][$field][$attribute_key])) {
                        $has_target_mapping_attribute = true;
                        $target_mapping_value = $valid_values[$p_universe][$field][$attribute_key];
                    }
                    if (!Tools::strlen($target_mapping_value) && in_array($field, array('Color', 'ColorName'))) {
                        $target_mapping_value = AmazonXSD::getStandardColor($target_attribute, $params->region);
                        $has_target_mapping_attribute = Tools::strlen($target_mapping_value);

                        if (!$has_target_mapping_attribute) {
                            $target_mapping_value = AmazonXSD::getStandardColor($target_attribute, 'en');
                            $has_target_mapping_attribute = Tools::strlen($target_mapping_value);
                        }
                    }
                }

                if ($has_free_mapping && !$has_target_mapping_attribute) {
                    $has_target_mapping_group = is_array($params->mapping['attributes']['free'][$id_lang]) && isset($params->mapping['attributes']['free'][$id_lang][$mapping_key]) && is_array($params->mapping['attributes']['free'][$id_lang][$mapping_key]);

                    if ($has_target_mapping_group) {
                        $has_target_mapping_attribute = isset($params->mapping['attributes']['free'][$id_lang][$mapping_key][$selected_attribute]) && isset($params->mapping['attributes']['free'][$id_lang][$mapping_key][$selected_attribute][$target_id_attribute]);

                        if ($has_target_mapping_attribute) {
                            $target_mapping_value = $params->mapping['attributes']['free'][$id_lang][$mapping_key][$selected_attribute][$target_id_attribute];
                        }
                    }
                }

                if (!Tools::strlen($target_mapping_value) && Tools::strlen($default_value)) {
                    $has_target_mapping_attribute = true;
                    $target_mapping_value = $default_value;
                }

                if ($has_target_mapping_attribute && Tools::strlen($target_mapping_value)) {
                    $mapping['id_attribute'] = $target_id_attribute;
                    $mapping['value'] = $target_attribute;
                    $mapping['mapping'] = AmazonTools::encodeText($target_mapping_value);
                    $mapping['has_mapping'] = true;

                    if (is_array($target_attribute_array)) {
                        $target_attribute_array['mapping'] = $mapping['mapping'];
                    }
                } elseif ($has_target_mapping_attribute) {
                    $mapping['is_missing'] = true;
                }
            }
        } elseif ($selected_feature) {
            $has_target_feature = isset($combination['features'][$selected_feature]) && is_array($combination['features'][$selected_feature]) && count($combination['features'][$selected_feature]);

            if ($has_target_feature) {
                $target_feature = &$combination['features'][$selected_feature];
                $target_id_feature = $target_feature['id_feature'];
                $target_id_feature_value = $target_feature['id_feature_value'];
                $target_feature_value = $target_feature['value'];
                $target_feature_value_key = AmazonTools::toKey($target_feature['value']);

                $has_const_mapping = isset($params->mapping['features']['const']) && isset($params->mapping['features']['const'][$id_lang]);
                $has_free_mapping = isset($params->mapping['features']['free']) && isset($params->mapping['features']['free'][$id_lang]);
                $target_mapping_value = null;
                $target_mapping_index = null;
                $has_target_mapping_feature = null;

                if (!(isset($valid_values[$p_universe]) && isset($valid_values[$p_universe][$field]))) {
                    $valid_values[$p_universe][$field] = AmazonValidValues::getValidValues($p_universe, $field, $params->region);
                }

                if ($has_const_mapping) {
                    $has_target_mapping_group = is_array($params->mapping['features']['const'][$id_lang]) && isset($params->mapping['features']['const'][$id_lang][$mapping_key]) && is_array($params->mapping['features']['const'][$id_lang][$mapping_key]);

                    if ($has_target_mapping_group) {
                        $has_target_mapping_feature = isset($params->mapping['features']['const'][$id_lang][$mapping_key][$selected_feature]) && isset($params->mapping['features']['const'][$id_lang][$mapping_key][$selected_feature][$target_id_feature_value]);

                        if ($has_target_mapping_feature) {
                            $target_mapping_index = $params->mapping['features']['const'][$id_lang][$mapping_key][$selected_feature][$target_id_feature_value];
                        }

                        if ($target_mapping_index && isset($valid_values[$p_universe][$field][$target_mapping_index])) {
                            $target_mapping_value = $valid_values[$p_universe][$field][$target_mapping_index];
                        }
                    }

                    if (!Tools::strlen($target_mapping_value)) {
                        // Search for an unmapped feature but matching a valid value
                        if (in_array(AmazonTools::toKey($target_feature_value_key), array_keys($valid_values[$p_universe][$field]))) {
                            $has_target_mapping_feature = true;
                            $target_mapping_value = $valid_values[$p_universe][$field][AmazonTools::toKey($target_feature_value_key)];
                        }

                        if (!Tools::strlen($target_mapping_value) && in_array($field, array('Color', 'ColorName'))) {
                            $target_mapping_value = AmazonXSD::getStandardColor($target_feature_value, $params->region);
                            $has_target_mapping_feature = Tools::strlen($target_mapping_value);

                            if (!$has_target_mapping_feature) {
                                $target_mapping_value = AmazonXSD::getStandardColor($target_feature_value, 'en');
                                $has_target_mapping_feature = Tools::strlen($target_mapping_value);
                            }
                        }
                    }
                } else {
                    // Search for an unmapped feature but matching a valid value
                    if (in_array(AmazonTools::toKey($target_feature_value_key), array_keys($valid_values[$p_universe][$field]))) {
                        $has_target_mapping_feature = true;
                        $target_mapping_value = $valid_values[$p_universe][$field][AmazonTools::toKey($target_feature_value_key)];
                    }
                    if (!Tools::strlen($target_mapping_value) && in_array($field, array('Color', 'ColorName'))) {
                        $target_mapping_value = AmazonXSD::getStandardColor($target_feature_value, $params->region);
                        $has_target_mapping_feature = Tools::strlen($target_mapping_value);

                        if (!$has_target_mapping_feature) {
                            $target_mapping_value = AmazonXSD::getStandardColor($target_feature_value, 'en');
                            $has_target_mapping_feature = Tools::strlen($target_mapping_value);
                        }
                    }
                }

                if ($has_free_mapping && !$has_target_mapping_feature) {
                    $has_target_mapping_group = is_array($params->mapping['features']['free'][$id_lang]) && isset($params->mapping['features']['free'][$id_lang][$mapping_key]) && is_array($params->mapping['features']['free'][$id_lang][$mapping_key]);

                    if ($has_target_mapping_group) {
                        $has_target_mapping_feature = isset($params->mapping['features']['free'][$id_lang][$mapping_key][$selected_feature]) && isset($params->mapping['features']['free'][$id_lang][$mapping_key][$selected_feature][$target_id_feature_value]);

                        if ($has_target_mapping_feature) {
                            $target_mapping_value = $params->mapping['features']['free'][$id_lang][$mapping_key][$selected_feature][$target_id_feature_value];
                        }
                    }
                }

                if (!Tools::strlen($target_mapping_value) && Tools::strlen($default_value)) {
                    $has_target_mapping_feature = true;
                    $target_mapping_value = $default_value;
                }

                if ($has_target_mapping_feature && Tools::strlen($target_mapping_value)) {
                    $mapping['id_feature'] = $target_id_feature;
                    $mapping['value'] = $target_feature_value;
                    $mapping['mapping'] = AmazonTools::encodeText($target_mapping_value);
                    $mapping['has_mapping'] = true;

                    if (is_array($target_feature)) {
                        $target_feature['mapping'] = $mapping['mapping'];
                    }
                } elseif ($target_feature_value) {
                    $mapping['id_feature'] = $target_id_feature;
                    $mapping['value'] = $target_feature_value;
                    $mapping['has_mapping'] = false;
                } else {
                    $mapping['is_missing'] = true;
                }
            }
        } elseif ($selected_field) {
            switch ($selected_field) {
                case AmazonSpecificField::REFERENCE:
                    $value = $combination['reference'];
                    break;
                case AmazonSpecificField::SUPPLIER_REFERENCE:
                    $value = $combination['supplier_reference'];
                    break;
                case AmazonSpecificField::MANUFACTURER:
                    $value = $combination['manufacturer'];
                    break;
                case AmazonSpecificField::CATEGORY:
                    $value = $combination['category_name'];
                    break;
                case AmazonSpecificField::META_TITLE:
                    $value = $combination['meta_title'];
                    break;
                case AmazonSpecificField::META_DESCRIPTION:
                    $value = $combination['meta_description'];
                    break;
                case AmazonSpecificField::UNITY:
                    $value = $combination['unity'];
                    break;
                case AmazonSpecificField::WEIGHT:
                    $value = $combination['weight'];
                    break;
                default:
                    return ($mapping);
            }
            if (!empty($value) || is_numeric($value)) {
                $mapping['mapping'] = AmazonTools::encodeText($value);
                $mapping['has_mapping'] = true;
            }
        } elseif ($has_default || $has_allowed) {
            $mapping['mapping'] = $mapping['value'] = AmazonTools::encodeText($default_value);
            $mapping['has_mapping'] = true;
        }

        if ($mapping['required'] && !Tools::strlen($mapping['value']) && !$mapping['has_mapping']) {
            $mapping['is_missing'] = true;
        }

        return ($mapping);
    }

    public function getPriceWithRule($profile_price_rule, $marketplace_price_rule, $price)
    {
        $current_price_rule = null;

        $check = AmazonTools::priceRule($price, $marketplace_price_rule);
        $marketplace_price_rule_check = (is_array($marketplace_price_rule) && $check != 0 && ($check > $price || $check < $price));

        $check = AmazonTools::priceRule($price, $profile_price_rule);
        $profile_price_rule_check = (is_array($profile_price_rule) && $check != 0 && ($check > $price || $check < $price));

        if ($profile_price_rule_check) {
            $newPrice = AmazonTools::priceRule($price, $profile_price_rule);
            $current_price_rule = $profile_price_rule;
        } elseif ($marketplace_price_rule_check) {
            $newPrice = AmazonTools::priceRule($price, $marketplace_price_rule);
            $current_price_rule = $marketplace_price_rule;
        } else {
            $newPrice = $price;
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Profile Price Rules: %s'.Amazon::LF, print_r($profile_price_rule, true)));
            CommonTools::p("Rule Applied: ".($profile_price_rule_check ? 'Profile' : ($marketplace_price_rule_check ? 'Marketplace' : 'None')));
            CommonTools::p("Rule Details: ".print_r($current_price_rule, true));
            CommonTools::p("Old Price: ".$price);
            CommonTools::p("New Price: ".$newPrice);
        }
        return($newPrice);
    }
}

class Params extends AmazonExportProducts
{
    public $timestart;
    public $categories;
    public $shop_categories       = null;
    public $cronMode              = false;
    public $extendedDatas         = false;
    public $sendHTMLDescriptions  = false;
    public $relationShipsOnly     = false;
    public $entireCatalog         = false;
    public $forcePriceFeed         = false;
    public $xmlOnly               = false;
    public $sendActives           = true;
    public $action;
    public $limit                 = false;
    public $syncBothMode          = false;
    public $sendToAmazon          = false;
    public $deleteConfirmed       = false;
    public $id_lang;
    public $platform;
    public $toCurrency;
    public $fromCurrency;
    public $images;
    public $asinOverride          = false;

    protected $debug;
    protected $outOfStock;
    protected $rounding;
    protected $fbaFormula;
    protected $FBA;
    protected $synchField;
    protected $specials;
    protected $preorder;
    protected $useTax;
    protected $conditionMap;
    protected $shippingOverrideStds;
    protected $shippingOverrideExps;
    protected $deleteProducts;
    protected $authorizeToDelete;
    protected $stockOnly;
    protected $pricesOnly;
    protected $asinHasPriority;
    protected $currentDate;
    protected $profile;
    protected $profile2category;
    protected $create;
    protected $dateSince;
    protected $weightUnit;
    protected $dimensionUnit;
    protected $descriptionField;
    protected $fullfillmentCenterId;
    protected $marketplaceID;
    protected $merchantID;
    protected $id_shop               = null;
    protected $id_warehouse          = null;
    protected $excludedManufacturers = array();
    protected $excludedSuppliers     = array();
    protected $priceFilter           = array();
    protected $createOffers          = false;
    protected $priceRules            = false;
    protected $noCombinations        = false;
    protected $defaultPtc            = false;

    protected $shipping                = null;
    protected $shippingTemplates       = null;
    protected $shippingAllowOverride   = false;
    protected $shippingTare            = null;
    protected $shippingGauge           = null;
    protected $smartShipping           = false;
    protected $smartShippingOption     = null;
    protected $amazonAddresses         = null;
    protected $id_address              = null;
    protected $id_carrier              = null;
    protected $deleteShippingOverrides = false;
    protected $default_strategy = null;
    protected $use_ssl = false;
    protected $stock_management = true;
    protected $remote_cart = false;
    protected $id_group = false;
    protected $id_business_group = false;
    protected $id_country_default = false;
    protected $alternative_content = false;

    public function __construct($id_lang)
    {
        parent::__construct();

        $this->timestart = time();
        $this->id_lang = $id_lang;

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
        $this->use_ssl = false; // (bool)Configuration::get('PS_SSL_ENABLED_EVERYWHERE'); for images, use of ssl is not supported yet, source: https://images-na.ssl-images-amazon.com/images/G/02/rainier/help/Feeds_Error_Messages.pdf
        $this->id_country_default = (int)Configuration::get('PS_COUNTRY_DEFAULT');
        // Regions
        //
        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $this->region = $marketPlaceRegion[$id_lang];

        // Stock
        //
        $params = AmazonConfiguration::get('OUT_OF_STOCK');
        $this->outOfStock = $params[$id_lang];

        // Price Rules (Replaces formula)
        //
        $params = AmazonConfiguration::get('PRICE_RULE');
        $this->priceRules = isset($params[$id_lang]) && is_array($params[$id_lang]) && isset($params[$id_lang]['type']) && isset($params[$id_lang]['rule']) && is_array($params[$id_lang]['rule']) ? $params[$id_lang] : null;

        $params = AmazonConfiguration::get('PRICE_ROUNDING');
        $this->rounding = isset($params[$id_lang]) && is_numeric($params[$id_lang]) ? $params[$id_lang] : null;

        // Field used for synchronisation - EAN / UPC / SKU and why not ISBN ...
        //
        $params = AmazonConfiguration::get('FIELD');
        $this->synchField = $params[$id_lang];

        // Default Product Tax code
        //
        $params = AmazonConfiguration::get('PTC');
        $this->defaultPtc = isset($params[$id_lang]) && Tools::strlen($params[$id_lang]) ? $params[$id_lang] : null;

        $this->alternative_content = (bool)Configuration::get('AMAZON_ALTERNATIVE_CONTENT');
        // Use Reduc / specials price
        //
        $this->specials = (int)AmazonConfiguration::get('SPECIALS') ? true : false;
        $this->specialsApplyRules = (bool)Configuration::get('AMAZON_SPECIALS_APPLY_RULES');
        $this->preorder = (bool)Configuration::get('AMAZON_PREORDER');
        $this->useTax = (int)AmazonConfiguration::get('TAXES') ? true : false;

        // State / Condition
        //
        $this->conditionMap = array_flip(AmazonConfiguration::get('CONDITION_MAP'));

        // Shipping Overrides
        //
        $params = AmazonConfiguration::get('SHIPPING_OVERRIDES_STD');
        $this->shippingOverrideStds = isset($params[$id_lang]) ? $params[$id_lang] : '';

        $params = AmazonConfiguration::get('SHIPPING_OVERRIDES_EXP');
        $this->shippingOverrideExps = isset($params[$id_lang]) ? $params[$id_lang] : '';

        $params = AmazonConfiguration::get('DELETE_PRODUCTS');
        $this->authorizeToDelete = (int)$params ? true : false;

        $params = AmazonConfiguration::get('STOCK_ONLY');
        $this->stockOnly = (int)$params ? true : Tools::getValue('stocks', false);

        $params = Configuration::get('AMAZON_PRICES_ONLY');
        $this->pricesOnly = (int)$params ? true : Tools::getValue('prices', false);

        $this->stock_management = (bool)Configuration::get('PS_STOCK_MANAGEMENT');

        // Title Formatage
        $params = AmazonConfiguration::get('TITLE_FORMAT');
        $this->titleFormat = $params;

        // Brute Force, as as named !
        //
        $this->bruteForce = AmazonConfiguration::get('BRUTE_FORCE');

        // Saved Categories
        //
        $this->categories = AmazonConfiguration::get('categories');

        // Profiles
        //
        $this->profile = AmazonConfiguration::get('profiles');
        $this->profile2category = AmazonConfiguration::get('profiles_categories');

        // ASIN
        //
        $params = AmazonConfiguration::get('USE_ASIN');
        $this->asinHasPriority = isset($params[$id_lang]) && (int)$params[$id_lang] ? true : false;

        // ASIN
        //
        $this->combinations = !(bool)Configuration::get('AMAZON_NO_COMBINATIONS');

        $this->currentDate = date('Y-m-d H:i:s');

        $this->images = (int)Tools::getValue('images');
        $this->create = (int)Tools::getValue('create');
        $this->delete = (int)Tools::getValue('delete-xml');

        // Absolute URL to images
        //
        $baseurl = sprintf('%s://%s%s', $this->use_ssl ? 'https' : 'http', htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8'), __PS_BASE_URI__);
        $this->images_url = $baseurl.'img/p/';

        // Amazon Attributes Mappings
        $stored_mapping = AmazonConfiguration::get('mapping');
        $default_mapping = array_fill_keys(array('fixed', 'attributes', 'features'), array());
        $this->mapping = array_merge($default_mapping, is_array($stored_mapping) ? $stored_mapping : array());

        $this->dateSince = Tools::getValue('since');

        $this->weightUnit = Tools::strtoupper(preg_replace('/[^A-Za-z]/', '', Configuration::get('PS_WEIGHT_UNIT')));
        $this->dimensionUnit = Tools::strtoupper(preg_replace('/[^A-Za-z]/', '', Configuration::get('PS_DIMENSION_UNIT')));

        $params = Configuration::get('AMAZON_DESCRIPTION_FIELD');
        $this->descriptionField = $params ? $params : Amazon::FIELD_DESCRIPTION_LONG;

        if (AmazonConfiguration::shopIsFeatureActive()) {
            $this->id_shop = (int)Validate::isLoadedObject($this->context->shop) ? $this->context->shop->id : 1;
        } else {
            $this->id_shop = 1;
        }

        $this->id_warehouse = (int)Configuration::get('AMAZON_WAREHOUSE');
        $this->id_group = (int)Configuration::get('AMAZON_CUSTOMER_GROUP');
        $this->id_business_group = (int)Configuration::get('AMAZON_BUSINESS_CUSTOMER_GROUP');

        $group = new Group($this->id_group);

        if (!Validate::isLoadedObject($group)) {
            $this->id_group = null;
        }

        if (!$this->id_group || !is_numeric($this->id_group)) {
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $this->id_group = Configuration::get('PS_CUSTOMER_GROUP');
            } else {
                $this->id_group = (int)_PS_DEFAULT_CUSTOMER_GROUP_;
            }
        }

        // FBA
        $this->FBA = (bool)$this->amazon_features['fba'];

        $params = AmazonConfiguration::get('FBA_PRICE_FORMULA');

        if (!empty($params) || $params != '@' && $this->FBA) {
            $this->fbaFormula = $params;
        } else {
            $this->fbaFormula = null;
        }

        // HTML Descriptions (2013/04/23)
        $this->sendHTMLDescriptions = (bool)Configuration::get('AMAZON_HTML_DESCRIPTIONS');

        // Exclude Manufacturers & Suppliers (2013/08/17)
        $params = AmazonConfiguration::get('EXCLUDED_MANUFACTURERS');
        $this->excludedManufacturers = is_array($params) && count($params) ? $params : array();

        $params = AmazonConfiguration::get('EXCLUDED_SUPPLIERS');
        $this->excludedSuppliers = is_array($params) && count($params) ? $params : array();

        $params = AmazonConfiguration::get('PRICE_FILTER');
        $this->priceFilter = is_array($params) && count($params) ? $params : array();

        $params = Configuration::get('AMAZON_SAFE_ENCODING');
        $this->safeEncoding = $params ? true : false;

        $params = AmazonConfiguration::get('SHIPPING');
        $this->shipping = $params;

        $this->createOffers = $this->amazon_features['offers'];
        $this->priceRulesEnabled = $this->amazon_features['prices_rules'];
        $this->secondHand = $this->amazon_features['second_hand'];
        $this->filters = $this->amazon_features['filters'];
        $this->repricing = $this->amazon_features['repricing'];
        $this->remote_cart = $this->amazon_features['remote_cart'];
        $this->expert = $this->amazon_features['expert_mode'];
        $this->default_strategy = null;

        if ($this->repricing) {
            $this->strategies = $this->getStrategies($id_lang);

            if (is_array($this->strategies) && count($this->strategies)) {
                foreach ($this->strategies as $strategy) {
                    if ($strategy['default']) {
                        $this->default_strategy = $strategy;
                    }
                }
            }
        } else {
            $this->strategies = array();
        }

        if (is_array($this->shipping) && isset($this->shipping['shipping_templates']) && $this->shipping['shipping_templates']) {
            $configured_group_names = unserialize(AmazonConfiguration::get('shipping_groups'));

            if (is_array($configured_group_names) && count($configured_group_names)) {
                foreach ($configured_group_names as $group_region => $group_names) {
                    if (!is_array($group_names) || !count($group_names)) {
                        unset($configured_group_names[$group_region]);
                    }
                }
                if (!is_array($configured_group_names) || !count($configured_group_names)) {
                    $configured_group_names = null;
                }
            }

            $this->shippingTemplates = $configured_group_names;
        } elseif (is_array($this->shipping)) {
            $shippingOptions = AmazonConfiguration::get('SHIPPING_METHODS');

            if (is_array($shippingOptions) && isset($shippingOptions[$id_lang])) {
                $shippingOption = $shippingOptions[$id_lang];

                if (isset($this->shipping['allow_overrides']) && $this->shipping['allow_overrides']) {
                    $this->shippingAllowOverride = true;
                }

                // Active Smart Shipping Feature (Shipping rules with shipping weight by using shipping overrides)
                //
                if (isset($this->shipping['smart_shipping']['active']) && $this->shipping['smart_shipping']['active'] && $shippingOption) {
                    $this->smartShipping = true;

                    if (isset($this->shipping['smart_shipping']['kind']) && $this->shipping['smart_shipping']['kind']) {
                        $this->smartShippingMethod = $this->shipping['smart_shipping']['kind'];
                    }

                    if (isset($this->shipping['smart_shipping']['prestashop'][$shippingOption]) && $this->shipping['smart_shipping']['prestashop'][$shippingOption]) {
                        $this->id_carrier = $this->shipping['smart_shipping']['prestashop'][$shippingOption];
                    }

                    $this->smartShippingOption = $shippingOption;

                    $params = AmazonConfiguration::get('ADDRESS_MAP');

                    if (is_array($params) && isset($params[$this->region]) && $shippingOption && (int)$this->id_carrier) {
                        $this->id_address = (int)$params[$this->region];
                    } else {
                        $this->smartShipping = false;
                    }
                }
            }
        }

        if (is_array($this->shipping) && isset($this->shipping['tare']) && (float)$this->shipping['tare']) {
            $this->shippingTare = (float)$this->shipping['tare'];
        }

        if (is_array($this->shipping) && isset($this->shipping['gauge']) && (float)$this->shipping['gauge']) {
            $this->shippingGauge = (float)$this->shipping['gauge'];
        }


        // We build a category table only on PS 1.5+ because of the multishop feature
        if (version_compare(_PS_VERSION_, '1.5', '>=') && Shop::isFeatureActive()) {
            $categories = Category::getCategories((int)$this->id_lang, false);

            $id_shop = Validate::isLoadedObject($this->context->shop) ? $this->context->shop->id : 1;

            $shop = new Shop($id_shop);
            $first = null;

            foreach ($categories as $categories1) {
                foreach ($categories1 as $category) {
                    if ($category['infos']['id_category'] == Category::getRootCategory(null, $shop)->id_category) {
                        $first = $category;
                    }
                }
            }

            $this->shop_categories = Amazon::recurseCategoryForInclude(array(), $categories, $first, $shop->id_category);
        }
        
        if (Amazon::$debug_mode) {
            CommonTools::p("Shipping Options:");
            CommonTools::p(sprintf('Allow Overrides: %s', $this->shippingAllowOverride ? 'Yes' : 'No'));
            CommonTools::p(sprintf('Smart Shipping: %s', $this->smartShipping));
            CommonTools::p(sprintf('Shipping Templates: %s', print_r($this->shippingTemplates, true)));
            CommonTools::p(sprintf('id_address: %s', $this->id_address));
        }
    }
}

$amazonExportProduct = new AmazonExportProducts;
$amazonExportProduct->doIt($start_time);
