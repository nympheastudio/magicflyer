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
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.products_report.class.php');
require_once(dirname(__FILE__).'/../classes/libs/convertor-master/convertor.php');

class AmazonProductsImport extends AmazonProductsReport
{
    const TYPE_TITLE            = 1;
    const TYPE_MANUFACTURER     = 2;
    const TYPE_BRAND            = 3;
    const TYPE_CATEGORY         = 4;
    const TYPE_PART_NUMBER      = 5;
    const TYPE_ITEM_DIMENSIONS  = 6;
    const TYPE_PACKAGE_DIMENSIONS = 7;
    const TYPE_FEATURE          = 8;
    const TYPE_IMAGE            = 9;
    const TYPE_COLOR            = 10;
    const TYPE_SIZE             = 11;
    const TYPE_DEPARTMENT       = 12;
    const TYPE_ECOTAX           = 13;
    const TYPE_PACKAGE_QTY      = 14;
    const TYPE_OTHER            = 99;

    const AMAZON_CATEGORY       = 'Amazon';

    const PRODUCT_ID_TYPE_ASIN  = 1;
    const PRODUCT_ID_TYPE_ISBN  = 2;
    const PRODUCT_ID_TYPE_UPC   = 3;
    const PRODUCT_ID_TYPE_EAN   = 4;

    public static $default_keys = array('features', 'package', 'item', 'variant', 'attributes');
    public static $offers = array();
    public static $variants = array();
    public static $process = false;
    public static $languages = array();
    public static $end = false;

    public static $expected = array(
                'en' => array('item-name', 'seller-sku', 'price', 'quantity', 'product-id', 'product-id-type', 'item-note', 'item-condition', 'status', 'status'),
                'fr' => array('nom-produit', 'sku-vendeur', 'prix', 'quantite', 'id-produit', 'type-id-produit', 'note-etat-article', 'etat-produit', 'status', 'statut'),
                'it' => array('nome dell\'articolo', 'sku venditore', 'prezzo', 'quantita', 'identificativo del prodotto', 'tipo di identificativo del prodotto', 'note sull\'articolo', 'condizione dell\'articolo', 'status'),
                'es' => array('titulo del producto', 'sku del vendedor', 'precio', 'cantidad', 'identificador del producto', 'tipo de identificador de producto', 'nota sobre el producto', 'estado del producto', 'status')
        );

    public static $amazon_attributes = array(
        'Actor' => self::TYPE_OTHER,
        'Artist' => self::TYPE_OTHER,
        'AspectRatio' => self::TYPE_OTHER,
        'AudienceRating' => self::TYPE_OTHER,
        'Author' => self::TYPE_OTHER,
        'BackFinding' => self::TYPE_OTHER,
        'BandMaterialType' => self::TYPE_OTHER,
        'Binding' => self::TYPE_CATEGORY,
        'BlurayRegion' => self::TYPE_OTHER,
        'Brand' => self::TYPE_BRAND,
        'CEROAgeRating' => self::TYPE_OTHER,
        'ChainType' => self::TYPE_OTHER,
        'ClaspType' => self::TYPE_OTHER,
        'Color' => self::TYPE_COLOR,
        'CPUManufacturer' => self::TYPE_OTHER,
        'CPUSpeed' => self::TYPE_OTHER,
        'CPUType' => self::TYPE_OTHER,
        'Creator' => self::TYPE_OTHER,
        'Department' => self::TYPE_OTHER,
        'Director' => self::TYPE_OTHER,
        'DisplaySize' => self::TYPE_OTHER,
        'Edition' => self::TYPE_OTHER,
        'EpisodeSequence' => self::TYPE_OTHER,
        'ESRBAgeRating' => self::TYPE_OTHER,
        'Feature' => self::TYPE_FEATURE,
        'Flavor' => self::TYPE_OTHER,
        'Format' => self::TYPE_OTHER,
        'GemType' => self::TYPE_OTHER,
        'Genre' => self::TYPE_OTHER,
        'GolfClubFlex' => self::TYPE_OTHER,
        'GolfClubLoft' => self::TYPE_OTHER,
        'HandOrientation' => self::TYPE_OTHER,
        'HardDiskInterface' => self::TYPE_OTHER,
        'HardDiskSize' => self::TYPE_OTHER,
        'HardwarePlatform' => self::TYPE_OTHER,
        'HazardousMaterialType' => self::TYPE_OTHER,
        'ItemDimensions' => self::TYPE_OTHER,
        'IsAdultProduct' => self::TYPE_OTHER,
        'IsAutographed' => self::TYPE_OTHER,
        'IsEligibleForTradeIn' => self::TYPE_OTHER,
        'IsMemorabilia' => self::TYPE_OTHER,
        'IssuesPerYear' => self::TYPE_OTHER,
        'ItemPartNumber' => self::TYPE_OTHER,
        'Label' => self::TYPE_OTHER,
        'Languages' => self::TYPE_OTHER,
        'LegalDisclaimer' => self::TYPE_OTHER,
        'ListPrice' => self::TYPE_OTHER,
        'Manufacturer' => self::TYPE_MANUFACTURER,
        'ManufacturerMaximumAge' => self::TYPE_OTHER,
        'ManufacturerMinimumAge' => self::TYPE_OTHER,
        'ManufacturerPartsWarrantyDescription' => self::TYPE_OTHER,
        'MaterialType' => self::TYPE_OTHER,
        'MaximumResolution' => self::TYPE_OTHER,
        'MediaType' => self::TYPE_OTHER,
        'MetalStamp' => self::TYPE_OTHER,
        'MetalType' => self::TYPE_OTHER,
        'Model' => self::TYPE_OTHER,
        'NumberOfDiscs' => self::TYPE_OTHER,
        'NumberOfIssues' => self::TYPE_OTHER,
        'NumberOfItems' => self::TYPE_OTHER,
        'NumberOfPages' => self::TYPE_OTHER,
        'NumberOfTracks' => self::TYPE_OTHER,
        'OperatingSystem' => self::TYPE_OTHER,
        'OpticalZoom' => self::TYPE_OTHER,
        'PackageDimensions' => self::TYPE_PACKAGE_DIMENSIONS,
        'PackageQuantity' => self::TYPE_PACKAGE_QTY,
        'PartNumber' => self::TYPE_PART_NUMBER,
        'PegiRating' => self::TYPE_OTHER,
        'Platform' => self::TYPE_OTHER,
        'ProcessorCount' => self::TYPE_OTHER,
        'ProductGroup' => self::TYPE_OTHER,
        'ProductTypeName' => self::TYPE_OTHER,
        'ProductTypeSubcategory' => self::TYPE_OTHER,
        'PublicationDate' => self::TYPE_OTHER,
        'Publisher' => self::TYPE_OTHER,
        'RegionCode' => self::TYPE_OTHER,
        'ReleaseDate' => self::TYPE_OTHER,
        'RingSize' => self::TYPE_OTHER,
        'RunningTime' => self::TYPE_OTHER,
        'ShaftMaterial' => self::TYPE_OTHER,
        'Scent' => self::TYPE_OTHER,
        'SeasonSequence' => self::TYPE_OTHER,
        'SeikodoProductCode' => self::TYPE_OTHER,
        'Size' => self::TYPE_SIZE,
        'SizePerPearl' => self::TYPE_OTHER,
        'SmallImage' => self::TYPE_IMAGE,
        'Studio' => self::TYPE_OTHER,
        'SubscriptionLength' => self::TYPE_OTHER,
        'SystemMemorySize' => self::TYPE_OTHER,
        'SystemMemoryType' => self::TYPE_OTHER,
        'TheatricalReleaseDate' => self::TYPE_OTHER,
        'Title' => self::TYPE_TITLE,
        'TotalDiamondWeight' => self::TYPE_OTHER,
        'TotalGemWeight' => self::TYPE_OTHER,
        'Warranty' => self::TYPE_OTHER,
        'WEEETaxValue' => self::TYPE_ECOTAX,
    );

    public function __construct()
    {
        $this->inventory_type = self::MERCHANT_ACTIVE_LISTINGS_DATA;
        $this->report_type = '_GET_MERCHANT_LISTINGS_ALL_DATA_';
        $this->id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');

        parent::__construct();

        ob_start();
        AmazonContext::restore($this->context);
        ob_get_clean();
    }

    public function dispatch()
    {
        ob_start();

        $this->import = $this->path.'import/';

        $token = Tools::getValue('instant_token');

        if (!$token || $token != Configuration::get('AMAZON_INSTANT_TOKEN', null, 0, 0)) {
            print 'Wrong token';
            die;
        }
        self::$languages = Language::getLanguages(false);

        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
        switch (Tools::getValue('action')) {
            case 'get-products':
                $this->getProducts();
                break;
            case 'parse-products':
                $this->parseProducts();
                break;
            default:
                die('Wrong action');
                break;
        }
    }


    public function xpathSimpleQuery($simple_xml_element, $query)
    {
        $xpath_result = $simple_xml_element->xpath($query);

        if (is_array($xpath_result) && is_array(reset($xpath_result))) {
            return(reset($xpath_result));
        } elseif (is_array($xpath_result) && count($xpath_result)) {
            if (is_array($xpath_result) && is_object(reset($xpath_result)) && count(reset($xpath_result)->attributes())) {
                return($xpath_result);
            } elseif (is_array($xpath_result) && count($xpath_result) >= 1 && is_object(reset($xpath_result))) {
                foreach ($xpath_result as $key => $result) {
                    $xpath_result[$key] = (string)$result;
                }
                return($xpath_result);
            } else {
                return((string)reset($xpath_result));
            }
        } else {
            return(null);
        }
    }


    public function parseProducts()
    {
        $result = null;
        $count = 0;
        $continue = false;
        $pass = true;
        $action = 'parse-products';
        $process = false;
        $count_offers = 0;
        $variants = array();
        $stop = false;
        $items_count = 0;

        if ($this->initDownload()) {
            if (!$this->processOffersInventory(false)) {
                self::$errors[] = $debug = $this->l('An error happened during the parsing');

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - error: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
                }
            } else {
                $count_offers = count(self::$offers);
                $amazon_products = array();
                //$amazon_products = Tools::jsonDecode(Tools::getValue('offers'), true);
                $offset = Tools::getValue('offset', 0);

                if ($offset && $offset >= $count_offers) {
                    self::$messages[] = $debug = $this->l('Import done successfully');
                    $stop = true;
                    $continue = false;
                } elseif (is_array(self::$offers) && $count_offers) {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s(#%d): %s - %d offers'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, count(self::$offers)));
                    }

                    $continue = true;
                    $process = true;

                    $getMatchingProductLimit = 5;

                    // grouping SKU by 5 for GetMatchingProduct
                    $items = array_slice(self::$offers, $offset, $getMatchingProductLimit);

                    $items_count = count($items);
                    $sku_list = array();
                    $parent_asins = array();

                    if (is_array($items) && count($items)) {
                        // item/item parsing
                        $count = 0;
                        foreach ($items as $key => $item) {
                            $sku = (string)$item['sku'];

                            if (Amazon::$debug_mode) {
                                CommonTools::p($item);
                            }
                            $sku_list[] = $item['sku'];
                        }
                        $count++;
                    }

                    if (count($sku_list)) {
                        $ufn = md5(serialize($sku_list));
                        $fn = 'a.'.$ufn.'.out';

                        if (file_exists($fn)) {
                            $result = simplexml_load_file($fn);
                        } else {
                            $result = $this->ws->getMatchingProductForId($sku_list, 'SellerSKU');
                        }
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $result));
                        }
                        if (!$result instanceof SimpleXMLElement) {
                            if (Amazon::$debug_mode) {
                                CommonTools::p('getMatchingProductForId: error - ');
                                CommonTools::p($result);
                            }
                            self::$errors[] = $debug = sprintf('Error: %s', print_r($result, true));
                            return (false);
                        }

                        $ns = $result->getNamespaces(true);
                        if (isset($ns[''])) {
                            $result->registerXPathNamespace('ns', $ns['']);
                        }
                        if (isset($ns['ns2'])) {
                            $result->registerXPathNamespace('ns2', $ns['ns2']);
                        }

                        $xpath_base = $result->xpath('/ns:GetMatchingProductForIdResponse/ns:GetMatchingProductForIdResult');

                        foreach ($xpath_base as $xpath_product) {
                            if (isset($xpath_product->Error->Message) && Tools::strlen($xpath_product->Error->Message)) {
                                self::$errors[] = $debug = sprintf('Marketplace Error: %s', $xpath_product->Error->Message);

                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
                                }

                                continue;
                            }

                            if (isset($ns[''])) {
                                $xpath_product->registerXPathNamespace('ns', $ns['']);
                            }
                            if (isset($ns['ns2'])) {
                                $xpath_product->registerXPathNamespace('ns2', $ns['ns2']);
                            }

                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('%s(#%d): %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__));
                                CommonTools::p(html_entity_decode($this->debugXML($xpath_product)));
                            }

                            $sku = (string)$xpath_product->attributes()->Id;

                            if (!Tools::strlen($sku)) {
                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s(#%d): %s - SKU not found'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__));
                                }
                                continue;
                            }

                            $xpath_identifier = $xpath_product->xpath('ns:Products/ns:Product/ns:Identifiers/ns:MarketplaceASIN/ns:ASIN');

                            if (!is_array($xpath_identifier)) {
                                self::$errors[] = $debug = sprintf('%s: %s', $this->l('Unable to find identifier'));

                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
                                }
                                continue;
                            }
                            $asin = (string)reset($xpath_identifier);

                            $xpath_categories = $xpath_product->xpath('ns:Products/ns:Product/ns:SalesRankings/ns:SalesRank/ns:ProductCategoryId');
                            $browse_node_id = null;

                            if (is_array($xpath_categories)) {
                                $xpath_category = array();
                                foreach ($xpath_categories as $xpath_category) {
                                    $value = (string)reset($xpath_category);
                                    if (preg_match('/^[0-9]*$/', $value)) {
                                        $browse_node_id = $value;
                                    }
                                }
                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($xpath_category, true)));
                                }
                            }

                            $xpath_variation_parent = $xpath_product->xpath('ns:Products/ns:Product/ns:Relationships/ns:VariationParent/ns:Identifiers/ns:MarketplaceASIN/ns:ASIN');

                            if (Tools::strlen($asin)) {
                                self::$messages[] = sprintf('%s SKU: %s ASIN: %s', $this->l('Parsing product'), $sku, $asin);
                            } else {
                                self::$messages[] = sprintf('%s SKU: %s', $this->l('Missing ASIN, product skipped'), $sku);
                                continue;
                            }

                            if (is_array($xpath_variation_parent) && count($xpath_variation_parent)) {
                                $parent_asin = (string)reset($xpath_variation_parent);
                            } else {
                                $parent_asin = null;
                            }

                            $attributes_tag = 'ns:Products/ns:Product/ns:AttributeSets/ns2:ItemAttributes';
                            $title = $this->xpathSimpleQuery($xpath_product, $attributes_tag.'/ns2:Title');
                            $product_lang = null;
                            $product_id_lang = null;

                            $lang_xpath = $xpath_product->xpath('ns:Products/ns:Product/ns:AttributeSets/ns2:ItemAttributes/@xml:lang');

                            if (is_array($lang_xpath) && count($lang_xpath)) {
                                $lang_result = reset($lang_xpath);
                                $lang_string = (string)$lang_result;
                                if (strstr($lang_string, '-')) {
                                    $product_lang = explode('-', $lang_string)[0];
                                } else {
                                    $product_lang = $lang_string;
                                }
                                $product_id_lang = Language::getIdByIso($product_lang);
                            } else {
                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s(#%d): %s - Failed to parse language'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__));
                                }
                                continue;
                            }


                            $amazon_product = array_fill_keys(self::$default_keys, array());
                            $amazon_product['reference'] = $sku;
                            $amazon_product['asin'] = $asin;
                            $amazon_product['parent_asin'] = $parent_asin;

                            $amazon_product['lang'] = $product_lang;
                            $amazon_product['id_lang'] = $product_id_lang;
                            $amazon_product['browse_node_id'] = $browse_node_id;

                            if ($parent_asin && !in_array($parent_asin, $parent_asins)) {
                                $parent_asins[] = $parent_asin;
                            }

                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('%s(#%d): %s - Parent ASIN: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $parent_asin));
                            }

                            foreach (self::$amazon_attributes as $amazon_attribute => $type) {
                                $value = $this->xpathSimpleQuery($xpath_product, $query = $attributes_tag.'/ns2:'.$amazon_attribute);

                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s(#%d): %s - Xpath query: %s Result: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $query, print_r($value, true)));
                                }

                                if (!$value) {
                                    continue;
                                }

                                // Oct-11-2018: Remove unused variable

                                if (in_array($value, array('true', 'false'))) {
                                    $value = $value == 'false' ? false : true;
                                }
                                if (Amazon::$debug_mode) {
                                    //CommonTools::p(sprintf('%s(#%d): %s - Attribute: %s Value: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $type, print_r($value, true)));
                                }

                                switch ($type) {
                                    case self::TYPE_FEATURE:
                                        if (is_array($value)) {
                                            $amazon_product['features'] = $value;
                                        } else {
                                            $amazon_product['features'] = null;
                                        }
                                        break;
                                    case self::TYPE_TITLE:
                                        if (is_array($value)) {
                                            $amazon_product['title'] = reset($value);
                                        } else {
                                            $amazon_product['title'] = null;
                                        }
                                        break;
                                    case self::TYPE_MANUFACTURER:
                                        if (is_array($value)) {
                                            $amazon_product['manufacturer'] = reset($value);
                                        } else {
                                            $amazon_product['manufacturer'] = null;
                                        }
                                        break;
                                    case self::TYPE_BRAND:
                                        if (is_array($value)) {
                                            $amazon_product['brand'] = reset($value);
                                        } else {
                                            $amazon_product['brand'] = null;
                                        }
                                        break;
                                    case self::TYPE_CATEGORY:
                                        if (is_array($value)) {
                                            $amazon_product['category'] = reset($value);
                                        } else {
                                            $amazon_product['category'] = null;
                                        }
                                        break;
                                    case self::TYPE_PART_NUMBER:
                                        if (is_array($value)) {
                                            $amazon_product['part_number'] = reset($value);
                                        } else {
                                            $amazon_product['part_number'] = null;
                                        }
                                        break;
                                    case self::TYPE_ITEM_DIMENSIONS:
                                    case self::TYPE_PACKAGE_DIMENSIONS:
                                        $height = $this->xpathSimpleQuery($xpath_product, $attributes_tag.'/ns2:'.$amazon_attribute.'/ns2:Height');

                                        if (is_array($height)) {
                                            $height_unit = (string)reset($height)->attributes()->Units;
                                            $height = (string)reset($height);
                                        } else {
                                            $height_unit = null;
                                            $height = null;
                                        }

                                        $length = $this->xpathSimpleQuery($xpath_product, $attributes_tag.'/ns2:'.$amazon_attribute.'/ns2:Length');

                                        if (is_array($length)) {
                                            $length_unit = (string)reset($length)->attributes()->Units;
                                            $length = (string)reset($length);
                                        } else {
                                            $length_unit = null;
                                            $length = null;
                                        }

                                        $width = $this->xpathSimpleQuery($xpath_product, $attributes_tag.'/ns2:'.$amazon_attribute.'/ns2:Width');

                                        if (is_array($width)) {
                                            $width_unit = (string)reset($width)->attributes()->Units;
                                            $width = (string)reset($width);
                                        } else {
                                            $width_unit = null;
                                            $width = null;
                                        }

                                        $weight_ent = $this->xpathSimpleQuery($xpath_product, $attributes_tag.'/ns2:'.$amazon_attribute.'/ns2:Weight');

                                        if (is_array($weight_ent)) {
                                            $weight_arr =  array_map('strval', $weight_ent);
                                            $weight_unit = (string)reset($weight_ent)->attributes()->Units;
                                            $weight = (string)reset($weight_arr);
                                        } else {
                                            $weight = null;
                                            $weight_unit = null;
                                        }
                                        if ($amazon_attribute == 'ItemDimensions') {
                                            $target = 'item';
                                        } else {
                                            $target = 'package';
                                        }
                                        $amazon_product[$target] = array();
                                        $amazon_product[$target]['height'] = array();
                                        $amazon_product[$target]['height']['value'] = $height;
                                        $amazon_product[$target]['height']['unit'] = $height_unit;

                                        $amazon_product[$target]['length'] = array();
                                        $amazon_product[$target]['length']['value'] = $length;
                                        $amazon_product[$target]['length']['unit'] = $length_unit;

                                        $amazon_product[$target]['width'] = array();
                                        $amazon_product[$target]['width']['value'] = $width;
                                        $amazon_product[$target]['width']['unit'] = $width_unit;

                                        $amazon_product[$target]['weight'] = array();
                                        $amazon_product[$target]['weight']['value'] = $weight;
                                        $amazon_product[$target]['weight']['unit'] = $weight_unit;

                                        break;
                                    case self::TYPE_IMAGE:
                                        $value = $this->xpathSimpleQuery($xpath_product, $query = $attributes_tag.'/ns2:'.$amazon_attribute.'/ns2:URL');

                                        if (Amazon::$debug_mode) {
                                            CommonTools::p(sprintf('%s(#%d): %s - Query: %s Value: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $query, print_r($value, true)));
                                        }
                                        if (is_array($value)) {
                                            $image = reset($value);

                                            if (Tools::strlen($image)) {
                                                $image = preg_replace('/\._[A-Z]{2}[0-9]{2}_/', '', $image);
                                            }
                                        } else {
                                            $image = null;
                                        }
                                        if (Amazon::$debug_mode) {
                                            CommonTools::p(sprintf('%s(#%d): %s - Image: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $image));
                                        }
                                        if (Tools::strlen($image) && !preg_match('/\/no-img/', $image)) {
                                            $amazon_product['image'] = $image;
                                        } else {
                                            $amazon_product['image'] = null;
                                        }
                                        break;
                                    case self::TYPE_COLOR:
                                        if (is_array($value)) {
                                            $amazon_product['color'] = reset($value);
                                        } else {
                                            $amazon_product['color'] = null;
                                        }
                                        break;
                                    case self::TYPE_SIZE:
                                        if (is_array($value)) {
                                            $amazon_product['size'] = reset($value);
                                        } else {
                                            $amazon_product['size'] = null;
                                        }
                                        break;
                                    case self::TYPE_ECOTAX:
                                        if (is_array($value)) {
                                            $amazon_product['ecotax'] = reset($value);
                                        } else {
                                            $amazon_product['ecotax'] = null;
                                        }
                                        break;
                                    case self::TYPE_PACKAGE_QTY:
                                        if (is_array($value)) {
                                            $amazon_product['package_qty'] = reset($value);
                                        } else {
                                            $amazon_product['package_qty'] = null;
                                        }
                                        break;
                                    default:
                                        if (is_array($value)) {
                                            $amazon_product['attributes'][$amazon_attribute] = reset($value);
                                        } else {
                                            $amazon_product['attributes'][$amazon_attribute] = $value;
                                        }
                                }
                            }

                            $amazon_products[$sku] = $amazon_product;

                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('%s(#%d): %s - Attributes: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($amazon_product, true)));
                            }
                        }
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s(#%d): %s - parent ASINs: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($parent_asins, true)));
                        }
                        if (is_array($parent_asins) && count($parent_asins)) {
                            $ufn = md5(serialize($parent_asins));
                            $fn = 'b.'.$ufn.'.out';

                            if (file_exists($fn)) {
                                $result = simplexml_load_file($fn);
                            } else {
                                $result = $this->ws->getMatchingProductForId($parent_asins, 'ASIN');
                                file_put_contents('b.'.$ufn.'.out', $result->asXML());
                            }

                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('%s(#%d): %s - ASIN file: %s content: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $fn, print_r($result, true)));
                            }
                            $ns = $result->getNamespaces(true);
                            if (isset($ns[''])) {
                                $result->registerXPathNamespace('ns', $ns['']);
                            }
                            if (isset($ns['ns2'])) {
                                $result->registerXPathNamespace('ns2', $ns['ns2']);
                            }


                            if ($result instanceof SimpleXMLElement) {
                                $xpath_base = $result->xpath('/ns:GetMatchingProductForIdResponse/ns:GetMatchingProductForIdResult/ns:Products/ns:Product');
                                $product_titles = array();
                                $product_images = array();

                                if (is_array($xpath_base) && count($xpath_base)) {
                                    foreach ($xpath_base as $xpath_product_key => $xpath_product) {
                                        $ns = $xpath_product->getNamespaces(true);
                                        if (isset($ns[''])) {
                                            $xpath_product->registerXPathNamespace('ns', $ns['']);
                                        }
                                        if (isset($ns['ns2'])) {
                                            $xpath_product->registerXPathNamespace('ns2', $ns['ns2']);
                                        }
                                        $asin_xpath = $xpath_product->xpath('ns:Identifiers/ns:MarketplaceASIN/ns:ASIN');
                                        $parent_asin = (string)reset($asin_xpath);

                                        $variation_attributes = $xpath_product->xpath('ns:Relationships/ns2:VariationChild/ns2:*');
                                        $product_title_xpath = $xpath_product->xpath('ns:AttributeSets/ns2:ItemAttributes/ns2:Title');
                                        $product_titles[$parent_asin] = (string)reset($product_title_xpath);
                                        $product_image_xpath = $xpath_product->xpath('ns:AttributeSets/ns2:ItemAttributes/ns2:SmallImage/ns2:URL');

                                        if (is_array($product_image_xpath) && !preg_match('/\/no-img/', reset($product_image_xpath))) {
                                            $product_images[$parent_asin] = (string)reset($product_image_xpath);
                                        } else {
                                            $product_images[$parent_asin] = null;
                                        }

                                        $variants[$parent_asin] = array();

                                        if (Amazon::$debug_mode) {
                                            CommonTools::p(sprintf('%s(#%d): %s - variation_attributes: %s (%s)'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($variation_attributes, true), $xpath_product_key));
                                        }
                                        $children_xpath = $xpath_product->xpath('ns:Relationships/ns2:VariationChild');

                                        if (is_array($children_xpath) && count($children_xpath) > 1) {
                                            foreach ($children_xpath as $children_xpath_key => $child) {
                                                $variation_attributes = $child->xpath('ns2:*');
                                                $asin = (string)$child->Identifiers->MarketplaceASIN->ASIN;


                                                if (Amazon::$debug_mode) {
                                                    CommonTools::p(sprintf('%s(#%d): %s - child: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($child, true)));
                                                }
                                                foreach ($variation_attributes as $variation_attribute_key => $variation_attribute) {

                                                    //$asin = (string)$children_xpath[$xpath_product_key];
                                                    $attribute_name = $variation_attribute->getName();
                                                    $attribute_value = (string)$variation_attribute;
                                                    $variants[$parent_asin][$asin][$attribute_name] = $attribute_value;

                                                    if (Amazon::$debug_mode) {
                                                        //CommonTools::p(sprintf('%s(#%d): %s - Attribute: %s (%s)'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($variation_attribute, true), $variation_attribute_key));
                                                        CommonTools::p(sprintf('%s(#%d): %s - Parent ASIN: %s ASIN:%s Attribute: %s Value: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $parent_asin, $asin, $variation_attribute, $attribute_value));
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if (Amazon::$debug_mode) {
                                        CommonTools::p(sprintf('%s(#%d): %s - Variants: %s (%s)'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($variants, true), $xpath_product_key));
                                    }

                                    if (is_array($variants) && count($variants)) {
                                        foreach ($variants as $parent_asin => $variant) {
                                            if (!is_array($variant) || count($variant) <= 1) { // prevent to create only 1 variant
                                                continue;
                                            }
                                            self::$variants[$parent_asin] = array();
                                            foreach ($variant as $child_asin => $attributes) {
                                                foreach ($amazon_products as $sku => $amazon_product) {
                                                    if ($amazon_product['asin'] == $child_asin) {
                                                        if ((float)self::$offers[$sku]['price']) {
                                                            if (!isset(self::$variants[$parent_asin][$child_asin])) {
                                                                self::$variants[$parent_asin][$child_asin] = array();
                                                            }
                                                            self::$variants[$parent_asin][$child_asin]['price'] = self::$offers[$sku]['price'];
                                                        }
                                                        foreach ($attributes as $attribute_name => $attribute_value) {
                                                            if (Amazon::$debug_mode) {
                                                                CommonTools::p(sprintf('%s(#%d): %s - Variation: %s Parent/Child: %s/%s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $attribute_value, $parent_asin, $child_asin));
                                                            }
                                                            if (isset($amazon_products[$sku]['attributes'][$attribute_name])) {
                                                                unset($amazon_products[$sku]['attributes'][$attribute_name]);
                                                            }
                                                            $amazon_products[$sku]['variant'][$attribute_name] = $attribute_value;
                                                            $amazon_products[$sku]['parent_title'] = $product_titles[$parent_asin];
                                                            $amazon_products[$sku]['parent_image'] = $product_images[$parent_asin];

                                                            if (Tools::strlen($amazon_products[$sku]['parent_image'])) {
                                                                $amazon_products[$sku]['parent_image'] = preg_replace('/\._[A-Z]{2}[0-9]{2}_/', '._UL1500_', $amazon_products[$sku]['parent_image']);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if (Amazon::$debug_mode) {
                                        CommonTools::p(sprintf('%s(#%d): %s - Variants: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($variants, true)));
                                    }
                                }
                            }
                        }

                        if (count($amazon_products)) {
                            foreach ($amazon_products as $sku => $amazon_product) {
                                self::$offers[$sku]['amazon'] = $amazon_product;
                            }

                            self::$messages[] = $debug = sprintf('%d %s', count($amazon_products), $this->l('product/s parsed...'));

                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('%s(#%d): %s - %s: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug, print_r($amazon_product, true)));
                            }
                        }

                        if ($count >= 20) { // prevents throttling
                            sleep(1);
                        }
                    }
                    // to create:
                    $to_create = array_slice(self::$offers, $offset, count($amazon_products));

                    $this->importProducts($to_create);

                    $stop = false;
                    $continue = true;
                } else {
                    self::$messages[] = $debug = $this->l('No offers to import');

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
                    }
                }
            }
        } else {
            self::$messages[] = $debug = $this->l('No offers to import');

            $stop = 1;
            $continue = false;

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
            }
        }

        if (Amazon::$debug_mode) {
            printf('%s(#%d): %s - End', basename(__FILE__), __LINE__, __FUNCTION__);
        }
        $offset += $items_count;
        $result =
            array(
                'error' => (count(self::$errors) ? true : false),
                'errors' => self::$errors,
                'message' => count(self::$messages) ? true : false,
                'messages' => self::$messages,
                'continue' => $continue,
                'pass' => $pass,
                'debug' => Amazon::$debug_mode,
                'output' => ob_get_clean(),
                'process' => !$stop && count(self::$offers) && ($items_count ? true : false),
                'offset' => $offset && $items_count ? $offset : count(self::$offers),
                'action' => $action,
                'end' => self::$end
            );
        $json = Tools::jsonEncode($result);

        if ($callback = Tools::getValue('callback')) {
            if ($callback == '?') {
                $callback = 'jsonp_'.time();
            }
            echo (string)$callback.'('.$json.')';
        } else {
            CommonTools::d($result);
        }
    }

    public function createCategory($category_name)
    {
        static $categories = array();
        static $id_amazon_category = null;
        if (!count($categories)) {
            $categories = Category::getSimpleCategories($this->id_lang_default);
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - id_amazon_category: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $id_amazon_category));
            CommonTools::p(sprintf('%s(#%d): %s - Category: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $category_name));
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - categories: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($categories, true)));
        }
        foreach ($categories as $category) {
            if (AmazonTools::toKey($category['name']) == AmazonTools::toKey($category_name)) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - Returning existing category: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($category, true)));
                }
                if ($category_name == self::AMAZON_CATEGORY) {
                    $id_amazon_category = (int)$category['id_category'];
                }
                return((int)$category['id_category']);
            }
        }

        $category = new Category();
        foreach (Language::getLanguages(false, false, true) as $id_lang) {
            $category->name[$id_lang] = $category_name;
            $category->link_rewrite[$id_lang] = AmazonTools::getFriendlyUrl($category_name);
            $category->meta_keywords[$id_lang] = str_replace('-', ' ', AmazonTools::getFriendlyUrl($category_name));
        }
        if ($category_name != self::AMAZON_CATEGORY) {
            $category->id_parent = $id_amazon_category;
        } else {
            if (method_exists('Category', 'getRootCategory')) {
                $category->id_parent = Category::getRootCategory()->id_category;
            } else {
                $category->id_parent = (int)Configuration::get('PS_ROOT_CATEGORY');
            }
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - parent category: %d'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, (int)$category->id_parent));
        }
        $category->active = true;
        $category->add();

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - Adding category: %s(%d)'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $category_name, $category->id));
        }

        if (Validate::isLoadedObject($category)) {
            if ($category_name == self::AMAZON_CATEGORY) {
                $id_amazon_category = $category->id;
            }
            $categories[] = array('name' => $category_name, 'id_category' => $category->id);
            return($category->id);
        }
        self::$errors[] = $debug = sprintf('%s: "%s"', $this->l('Unable to create category'), $category_name);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
        }
        return(false);
    }

    public function importProducts(&$products)
    {
        static $attributes = null;
        static $attributes_names = null;
        static $attributes_values_names = null;

        if (!is_array($products)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): %s - not an array: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($products, true)));
            }
            return(false);
        }

        $languages = self::$languages;

        if ($attributes == null) {
            $attributes = array();
            $attributes_names = array();
            $attributes_values_names = array();

            foreach ($languages as $language) {
                $id_lang = $language['id_lang'];
                $attribute_groups = AttributeGroup::getAttributesGroups($id_lang);

                foreach ($attribute_groups as $attribute_group) {
                    $id_attribute_group = (int)$attribute_group['id_attribute_group'];
                    $attributes[$id_lang][$id_attribute_group] = $attribute_group;
                    $attributes[$id_lang][$id_attribute_group]['values'] = array();
                    $attributes_names[AmazonTools::toKey($attribute_group['name'])] = $id_attribute_group;
                }

                $attributes = Attribute::getAttributes($id_lang);

                foreach ($attributes as $attribute) {
                    $id_attribute_group = $attribute['id_attribute_group'];

                    $keys = array('attribute_group', 'public_name', 'group_type', 'position', 'is_color_group', 'id_lang', 'id_attribute_group');
                    $attribute = array_diff_key($attribute, array_flip($keys));
                    $attributes[$id_lang][$id_attribute_group]['values'][] = $attribute;
                    $attributes_values_names[AmazonTools::toKey($attribute['name'])] = $attribute['id_attribute'];
                }
            }
        }
        foreach ($products as $amazon_product) {
            if (!isset($amazon_product['amazon'])) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - no data: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($amazon_product, true)));
                }
                return(false);
            }

            $amazon_data = $amazon_product['amazon'];
            $product_id_lang = is_numeric($amazon_data['id_lang']) ? $amazon_data['id_lang'] : $this->id_lang_default;

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): %s - amazon_product: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($amazon_product, true)));
            }

            if (isset($amazon_data['parent_asin'])) {
                if (isset(self::$variants[$amazon_data['parent_asin']])
                    && is_array(self::$variants[$amazon_data['parent_asin']])
                    && count(self::$variants[$amazon_data['parent_asin']]) > 1) { // create combinations only if there is more than one child
                    $is_variant = true;
                } else {
                    $is_variant = false;
                    $amazon_data['parent_asin'] = null;
                }

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - variant product: %s/%s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $amazon_data['parent_asin'], $amazon_data['asin']));
                }
            } else {
                $is_variant = false;
            }

            $product = null;
            if ($is_variant) {
                $product = $this->importProduct($amazon_product);

                if (Validate::isLoadedObject($product)) {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s(#%d): %s - Product is variant and loaded: %d/%s, amazon: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id, $amazon_data['title'], print_r($amazon_data, true)));
                    }
                    $amazon_data = $amazon_product['amazon'];

                    if (isset($amazon_data['variant']) && count($amazon_data['variant'])) {
                        $product_combination = array();

                        foreach ($amazon_data['variant'] as $amazon_attribute => $amazon_attribute_value) {
                            $id_attribute_group = null;
                            $id_attribute = null;

                            if (!isset($attributes_names[AmazonTools::toKey($amazon_attribute)])) {
                                $attribute_group_names = array();
                                foreach ($languages as $language) {
                                    $id_lang = (int)$language['id_lang'];
                                    $attribute_group_names[$id_lang] = $amazon_attribute;
                                }

                                $attribute_group = new AttributeGroup();
                                $attribute_group->name = $attribute_group_names;
                                $attribute_group->public_name = $attribute_group_names;
                                if (method_exists('AttributeGroup', 'getHigherPosition')) {
                                    $attribute_group->position = AttributeGroup::getHigherPosition()+1;
                                }
                                $attribute_group->is_color_group = false;
                                $attribute_group->group_type = 'select';

                                if ($attribute_group->validateFields(false, false)) {
                                    $attribute_group->add();
                                }

                                if (Validate::isLoadedObject($attribute_group)) {
                                    $id_attribute_group = (int)$attribute_group->id;

                                    if (Amazon::$debug_mode) {
                                        CommonTools::p(sprintf('%s(#%d): %s - Created attribute group: %d/%s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $id_attribute_group, $amazon_attribute));
                                    }
                                } else {
                                    self::$errors[] = $debug = sprintf('%s: "%s"', $this->l('Unable to create attribute group'), $amazon_attribute);

                                    if (Amazon::$debug_mode) {
                                        CommonTools::p(sprintf('%s(#%d): %s - '.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
                                    }
                                }
                            } else {
                                $id_attribute_group = $attributes_names[AmazonTools::toKey($amazon_attribute)];

                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s(#%d): %s - Existing attribute group: %d/%s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $id_attribute_group, $amazon_attribute));
                                }
                            }

                            if (!isset($attributes_values_names[AmazonTools::toKey($amazon_attribute_value)]) && $id_attribute_group) {
                                $attribute_value_names = array();
                                foreach ($languages as $language) {
                                    $id_lang = (int)$language['id_lang'];
                                    $attribute_value_names[$id_lang] = $amazon_attribute_value;
                                }

                                $attribute = new Attribute();
                                $attribute->id_attribute_group = $id_attribute_group;
                                $attribute->name = $attribute_value_names;
                                if (method_exists('Attribute', 'getHigherPosition')) {
                                    $attribute->position = Attribute::getHigherPosition($id_attribute_group)+1;
                                }
                                if ($attribute->validateFields(false, false)) {
                                    $attribute->add();

                                    if (Validate::isLoadedObject($attribute)) {
                                        $id_attribute = (int)$attribute->id;

                                        if (Amazon::$debug_mode) {
                                            CommonTools::p(sprintf('%s(#%d): %s - Created attribute value: %d/%s/%s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $id_attribute, $amazon_attribute, $amazon_attribute_value));
                                        }
                                    } else {
                                        self::$errors[] = $debug = sprintf('%s: "%s/%s"', $this->l('Unable to create attribute value'), $amazon_attribute, $amazon_attribute_value);

                                        if (Amazon::$debug_mode) {
                                            CommonTools::p(sprintf('%s(#%d): %s - '.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
                                        }
                                    }
                                } else {
                                    self::$errors[] = $debug = sprintf('%s: "%s/%s"', $this->l('Unable to create attribute value'), $amazon_attribute, $amazon_attribute_value);

                                    if (Amazon::$debug_mode) {
                                        CommonTools::p(sprintf('%s(#%d): %s - '.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
                                    }
                                }
                            } else {
                                $id_attribute = $attributes_values_names[AmazonTools::toKey($amazon_attribute_value)];

                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s(#%d): %s - Existing attribute value: %d/%s - '.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $id_attribute, $amazon_attribute_value));
                                }
                            }
                            if ($id_attribute_group && $id_attribute) {
                                $product_combination[$id_attribute_group] = $id_attribute;
                            }
                        }

                        if (is_array($product_combination) && count($product_combination)) {
                            $price = sprintf('%.02f', $amazon_product['price']);
                            if (isset($amazon_data['item']['weight'])) {
                                $weight = sprintf('%.02f', $amazon_data['item']['weight']);
                            } elseif (isset($amazon_data['package']['weight'])) {
                                $weight = sprintf('%.02f', $amazon_data['package']['weight']);
                            } else {
                                $weight = 0;
                            }
                            if (isset($amazon_data['id_images'])) {
                                $images = $amazon_data['id_images'];
                            } else {
                                $images = array();
                            }

                            if (Validate::isReference($amazon_data['reference'])) {
                                $reference = $amazon_data['reference'];
                            } else {
                                $reference = null;
                            }

                            $ean13 = $upc = $isbn = null;

                            if (Tools::strlen($amazon_data['ean13']) && AmazonTools::eanUpcCheck($amazon_data['ean13'])) {
                                $ean13 = sprintf('%013s', $amazon_data['ean13']);
                            } elseif (Tools::strlen($amazon_data['upc']) && AmazonTools::eanUpcCheck($amazon_data['upc'])) {
                                $upc = sprintf('%012s', $amazon_data['upc']);
                            } elseif (Tools::strlen($amazon_data['isbn']) && AmazonTools::eanUpcCheck($amazon_data['isbn'])) {
                                $isbn = sprintf('%013s', $amazon_data['ean13']);
                            }
                            $attribute_combinations = $product->getAttributeCombinations($this->id_lang_default);

                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('%s(#%d): %s - Product combination: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($product_combination, true)));
                                CommonTools::p(sprintf('%s(#%d): %s - Attribute combinations: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($attribute_combinations, true)));
                            }
                            $prices = array();
                            $id_product_attribute = null;
                            $current_combinations = array();
                            $attribute_combination_exists = false;
                            $current_id_product_attribute = null;
                            $is_default_combination = false;

                            foreach ($attribute_combinations as $key => $attribute_combination) {
                                $id_attribute = $attribute_combination['id_attribute'];
                                $id_attribute_group = $attribute_combination['id_attribute_group'];
                                $id_product_attribute = $attribute_combination['id_product_attribute'];
                                if (!isset($current_combinations[$id_product_attribute][$id_attribute_group])) {
                                    $current_combinations[$id_product_attribute][$id_attribute_group] = array();
                                }

                                $current_combinations[$id_product_attribute][$id_attribute_group] = $id_attribute;
                                $prices[] = $price;
                            }

                            foreach ($current_combinations as $id_product_attribute => $current_combination) {
                                ksort($current_combination);
                                ksort($product_combination);

                                if ($current_combination == $product_combination) {
                                    $attribute_combination_exists = true;
                                    $current_id_product_attribute = $id_product_attribute;
                                }
                            }
                            //$amazon
                            $parent_asin = $amazon_data['parent_asin'];
                            $asin = $amazon_data['asin'];
                            $impact = 0;

                            if (isset(self::$variants[$parent_asin][$asin]['price'])) {
                                $prices = array();
                                foreach (self::$variants[$parent_asin] as $variant_data) {
                                    if (!isset($variant_data['price'])) {
                                        continue;
                                    }
                                    $prices[] = $variant_data['price'];
                                }
                                if (count($prices)) {
                                    $min_price = min($prices);
                                } else {
                                    $min_price = $price;
                                }

                                if (Tools::getValue('update-price')) {
                                    // Tax calculation
                                    $tax = new Tax();
                                    $product_tax_rate = $tax->getProductTaxRate($product->id);
                                    $unit_price_tax_excl = $product_tax_rate ? Tools::ps_round($min_price / ((100 + $product_tax_rate) / 100), 2) : Tools::ps_round($min_price, 2);
                                    $product->tax_rate = $product_tax_rate;
                                    $product->price = $unit_price_tax_excl;
                                    $impact = $product_tax_rate ? Tools::ps_round(($price - $min_price) / ((100 + $product_tax_rate) / 100), 2) : Tools::ps_round(($price - $min_price), 2);

                                    if (Amazon::$debug_mode) {
                                        CommonTools::p(sprintf('%s(#%d): %s - update price and tax for: %d/%d, price: %.02f/%.02f tax: %.02f'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id, $id_product_attribute, $unit_price_tax_excl, $impact, $product_tax_rate));
                                    }
                                    $product->update();
                                }
                            }
                            if (!$attribute_combination_exists) {
                                $id_product_attribute = $product->addCombinationEntity(null, $impact, $weight, null, null, null, $images, $reference, null, $ean13, $is_default_combination, null, $upc);

                                // create combinations
                                foreach ($product_combination as $id_attribute_group => $id_attribute) {
                                    $result = null;

                                    // create attribute combinations
                                    if (is_numeric($id_product_attribute)) {
                                        $result = Db::getInstance()->execute(
                                            '
                                                    INSERT IGNORE INTO '._DB_PREFIX_.'product_attribute_combination (id_attribute, id_product_attribute)
                                                    VALUES ('.(int)$id_attribute.','.(int)$id_product_attribute.')', false
                                            );
                                    }

                                    if (Amazon::$debug_mode) {
                                        CommonTools::p(sprintf('%s(#%d): %s - Create combination result: for %d/%d - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $id_attribute_group, $id_attribute, print_r($result, true)));
                                    }
                                }
                            } else {
                                // update attribute combinations
                                $id_product_attribute = null;
                                foreach ($attribute_combinations as $attribute_combination) {
                                    $id_product_attribute = $attribute_combination['id_product_attribute'];
                                    $id_attribute_group = $attribute_combination['id_attribute_group'];
                                    $id_attribute = $attribute_combination['id_attribute'];

                                    if ($current_id_product_attribute != $id_product_attribute) {
                                        continue;
                                    }
                                    if (!isset($product_combination[$id_attribute_group])) {
                                        continue;
                                    }

                                    if ($id_product_attribute) {
                                        $product->updateAttribute($id_product_attribute, null, $impact, $weight, null, null, $images, $reference, $ean13, $is_default_combination, null, $upc, 1);

                                        if (Amazon::$debug_mode) {
                                            CommonTools::p(sprintf('%s(#%d): %s - Update attribute result: for %d/%d'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $id_attribute_group, $id_attribute));
                                        }
                                    } else {
                                        if (Amazon::$debug_mode) {
                                            CommonTools::p(sprintf('%s(#%d): %s - Missing attribute "%d" for product id "%d"'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $id_attribute, $product->id));
                                        }
                                    }
                                }
                            }

                            if (Tools::getValue('update-quantity')) {
                                StockAvailable::setQuantity((int)$product->id, $id_product_attribute, (int)$amazon_product['qty'], $this->context->shop->id);
                            }

                            if (isset($amazon_data['asin']) && Tools::strlen($amazon_data['asin'])) {
                                AmazonProduct::updateProductOptions($product->id, $product_id_lang, 'asin1', $amazon_data['asin']);
                            }

                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('%s(#%d): %s - update stock and price for: %d qty: %d, price: %.02f'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id, (int)$amazon_product['qty'], $price));
                            }
                        }
                    }
                } else {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s(#%d): %s - validation failed: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($amazon_product, true)));
                    }
                }
            } else {
                // generate the stand alone product
                $product = $this->importProduct($amazon_product);

                if (Validate::isLoadedObject($product)) {
                    if (Tools::getValue('update-quantity')) {
                        StockAvailable::setQuantity((int)$product->id, null, (int)$amazon_product['qty'], $this->context->shop->id);
                    }

                    if (isset($amazon_data['asin']) && Tools::strlen($amazon_data['asin'])) {
                        AmazonProduct::updateProductOptions($product->id, $product_id_lang, 'asin1', $amazon_data['asin']);
                    }

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s(#%d): %s - update stock and price for: %d qty: %d'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id, (int)$amazon_product['qty']));
                    }
                }
            }
        }
    }

    public function addFeatures($product, $amazon_product)
    {
        static $features = null;
        static $features_values = null;
        static $existing_product_features = array();

        if (!is_array($amazon_product) || !Validate::isLoadedObject($product)) {
            return(false);
        }

        $amazon_data = $amazon_product['amazon'];
        $product_id_lang = is_numeric($amazon_data['id_lang']) ? $amazon_data['id_lang'] : $this->id_lang_default;

        $product_features = $product->getFeatures();

        if (is_array($product_features) && count($product_features)) {
            foreach ($product_features as $product_feature) {
                $id_feature = $product_feature['id_feature'];
                $id_feature_value = $product_feature['id_feature_value'];
                if (!isset($existing_product_features[$product->id][$id_feature]) || !is_array($existing_product_features[$product->id][$id_feature])) {
                    $existing_product_features[$product->id][$id_feature] = array();
                }
                $existing_product_features[$product->id][$id_feature][$id_feature_value] = true;
            }
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): %s - existing product features: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($existing_product_features, true)));
            }
        }

        $languages = self::$languages;

        if ($features == null) {
            $features = array();

            foreach ($languages as $language) {
                $id_lang = $language['id_lang'];
                $all_features = Feature::getFeatures($id_lang);
                $features[$id_lang] = array();

                if (is_array($all_features) && count($all_features)) {
                    foreach ($all_features as $feature) {
                        $id_feature = (int)$feature['id_feature'];
                        $features[$id_lang][$id_feature] = array();
                        $features[$id_lang][$id_feature] = $feature;
                        $features[$id_lang][$id_feature]['values'] = array();

                        $all_features_values = FeatureValue::getFeatureValuesWithLang($id_lang, $id_feature, true);

                        if (is_array($all_features_values) && count($all_features_values)) {
                            foreach ($all_features_values as $feature_value) {
                                $id_feature_value = $feature_value['id_feature_value'];
                                $features[$id_lang][$id_feature]['values'][$id_feature_value] = $feature_value;
                            }
                        }
                    }
                }
            }
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - existing features: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($features, true)));
            CommonTools::p(sprintf('%s(#%d): %s - existing product features: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($existing_product_features, true)));
        }


        $existing_id_feature = null;
        $existing_id_feature_value = null;

        if (is_array($amazon_data) && isset($amazon_data['attributes']) && count($amazon_data['attributes'])) {
            foreach ($amazon_data['attributes'] as $attribute_name => $attribute_value) {
                if (!Tools::strlen($attribute_value) || !Tools::strlen($attribute_name)) {
                    continue;
                }
                if (!Validate::isGenericName($attribute_value) || !Validate::isGenericName($attribute_name)) {
                    continue;
                }
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - attribute: %s value: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $attribute_name, $attribute_value));
                }

                if (is_array($features) && count($features)) {
                    $existing_id_feature = null;
                    foreach ($features as $id_lang => $features_lang) {
                        foreach ($features_lang as $id_feature => $feature) {
                            $feature_name = $feature['name'];
                            if (AmazonTools::toKey($feature_name) == AmazonTools::toKey($attribute_name)) {
                                $existing_id_feature = $id_feature;

                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s(#%d): %s - matching amazon attribute: %s feature: %s(%d) lang: %d'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $attribute_name, $feature_name, $id_feature, $id_lang));
                                }
                            }
                        }
                    }
                    $existing_id_feature_value = null;

                    if ($existing_id_feature) {
                        foreach ($languages as $language) {
                            $id_lang = $language['id_lang'];
                            $current_feature = $features[$id_lang][$existing_id_feature];
                            if (isset($current_feature['values']) && count($current_feature['values'])) {
                                foreach ($current_feature['values'] as $id_feature_value => $feature_value) {
                                    if (AmazonTools::toKey($attribute_value) == AmazonTools::toKey($feature_value['value'])) {
                                        // existing
                                        $existing_id_feature_value = $id_feature_value;

                                        if (Amazon::$debug_mode) {
                                            CommonTools::p(sprintf('%s(#%d): %s - matching amazon attribute value: %s feature value: %s(%d) lang: %d'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $attribute_value, $feature_value['value'], $id_feature_value, $id_lang));
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (!$existing_id_feature) {
                        $feature_title = trim(preg_replace('/([A-Z])/', ' \1', $attribute_name));

                        if (!Tools::strlen($feature_title)) {
                            continue;
                        }
                        if (!Validate::isGenericName($feature_title)) {
                            continue;
                        }
                        $feature = new Feature();

                        foreach ($languages as $language) {
                            $id_lang = $language['id_lang'];
                            $feature->name[$id_lang] = $feature_title;
                            if (method_exists('Feature', 'getHigherPosition')) {
                                $feature->position = Feature::getHigherPosition() + 1;
                            }
                        }

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s(#%d): %s - add feature: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r(get_object_vars($feature), true)));
                        }
                        $feature->add();

                        if (Validate::isLoadedObject($feature)) {
                            $existing_id_feature = $feature->id;

                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('%s(#%d): %s - feature added: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r(get_object_vars($feature), true)));
                            }
                        }
                    }
                    $feature_value = null;

                    if (!$existing_id_feature_value && $existing_id_feature) {
                        $feature_value = new FeatureValue();
                        $feature_value->id_feature = (int)$existing_id_feature;
                        $feature_value->custom = false;

                        foreach ($languages as $language) {
                            $id_lang = $language['id_lang'];
                            $feature_value->value[$id_lang] = $attribute_value;
                        }
                        $feature_value->add();

                        if (Validate::isLoadedObject($feature_value)) {
                            $existing_id_feature_value = $feature_value->id;
                        }

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s(#%d): %s - add feature: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r(get_object_vars($feature), true)));
                        }
                    }
                }

                $product_has_feature_value = isset($existing_product_features[$product->id][$existing_id_feature]);

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - product_has_feature_value: %d'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product_has_feature_value));
                }
                if (!$product_has_feature_value) {
                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s(#%d): %s - add product feature value: %d/%d/%d\''.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id, $existing_id_feature, $existing_id_feature_value));
                    }
                    $product->addFeaturesToDB($existing_id_feature, $existing_id_feature_value);
                    $existing_product_features[$product->id][$existing_id_feature][$existing_id_feature_value] = true;
                }
            }
        }
    }

    public function importProduct(&$amazon_product)
    {
        static $categories = null;
        $id_amazon_category = $this->createCategory(self::AMAZON_CATEGORY);
        $amazon_data = &$amazon_product['amazon'];
        $product_id_lang = is_numeric($amazon_data['id_lang']) ? $amazon_data['id_lang'] : $this->id_lang_default;

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - importing product: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r($amazon_product, true)));
        }

        if ($amazon_data['parent_asin'] && isset(self::$variants[$amazon_data['parent_asin']])
            && is_array(self::$variants[$amazon_data['parent_asin']])
            && count(self::$variants[$amazon_data['parent_asin']]) > 1) {
            $has_variant = true;
            $sku = $amazon_data['parent_asin'];
            $child_sku = $amazon_product['sku'];
        } else {
            $has_variant = false;
            $sku = trim(Tools::substr($amazon_product['sku'], 0, 32));
            $child_sku = null;
        }

        if (!Validate::isReference($sku)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): %s - SKU validation failed: "%s"'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $sku));
            }
            return(false);
        }

        if (AmazonProduct::checkProduct($sku, $this->context->shop->id)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): %s - "%s" already exists in database'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $sku));
            }
            // existing product
            $product = new AmazonProduct($sku, false, $this->id_lang_default);

            if (!Validate::isLoadedObject($product)) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - "%s" unable to load product'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $sku));
                }
                return(false);
            }

            if (!Tools::getValue('update-existing')) {
                self::$messages[] = $debug = sprintf('%s: %s/%s', $this->l('Ignoring existing product'), $product->reference, $product->name);
                return(false);
            }
        } elseif ($child_sku) {
            // Maybe it exists as combination
            if (AmazonProduct::checkProduct($child_sku, $this->context->shop->id)) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - "%s" already exists in database'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $child_sku));
                }
                // existing product
                $product = new AmazonProduct($child_sku, false, $this->id_lang_default);

                if (!Tools::getValue('update-existing')) {
                    self::$messages[] = $debug = sprintf('%s: %s/%s', $this->l('Ignoring existing product'), $product->reference, $product->name);
                    return(false);
                }
            } else {
                $product = new Product();
            }
        } else {
            $product = new Product();
        }

        if (isset($amazon_data['package_qty']) && (int)$amazon_data['package_qty']) {
            $product->unity = $amazon_data['package_qty'];
        }
        if (isset($amazon_data['package']['height']) && (float)$amazon_data['package']['height']['value']) {
            $product->height = $this->convertUnit((float)$amazon_data['package']['height']['value'], $amazon_data['package']['height']['unit']);
        }
        if (isset($amazon_data['package']['length']) && (float)$amazon_data['package']['length']['value']) {
            $product->depth = $this->convertUnit((float)$amazon_data['package']['length']['value'], $amazon_data['package']['length']['unit']);
        }
        if (isset($amazon_data['package']['width']) && (float)$amazon_data['package']['width']['value']) {
            $product->width = $this->convertUnit((float)$amazon_data['package']['width']['value'], $amazon_data['package']['width']['unit']);
        }
        if (isset($amazon_data['package']['weight']) && (float)$amazon_data['package']['weight']['value']) {
            $product->weight = $this->convertUnit((float)$amazon_data['package']['weight']['value'], $amazon_data['package']['weight']['unit']);
        }
        $amazon_data['isbn'] = $isbn = null;
        $amazon_data['upc'] = $upc = null;
        $amazon_data['ean13'] = $ean13 = null;

        switch ($amazon_product['product-id-type']) {
            case self::PRODUCT_ID_TYPE_ASIN:
                break;
            case self::PRODUCT_ID_TYPE_ISBN:
                $isbn = $amazon_data['isbn'] = $amazon_product['product-id'];
                break;
            case self::PRODUCT_ID_TYPE_UPC:
                $upc = $amazon_data['upc'] = sprintf('%.012s', $amazon_product['product-id']);
                break;
            case self::PRODUCT_ID_TYPE_EAN:
                $ean13 = $amazon_data['ean13'] = sprintf('%.013s', $amazon_product['product-id']);
                break;
        }

        if (!$has_variant) {
            if ($isbn && !Tools::strlen($product->ean13)) {
                $product->ean13 = $isbn;
            }
            if ($ean13 && !Tools::strlen($product->ean13)) {
                $product->ean13 = $ean13;
            }
            if ($upc && !Tools::strlen($product->upc)) {
                $product->upc= $upc;
            }
        }

        if (!Tools::strlen($product->reference)) {
            $product->reference = $sku;
        }
        if (Tools::getValue('visbility-hidden')) {
            $product->visibility = 'none';
        }
        if (Tools::strlen($amazon_data['parent_asin']) && $amazon_data['asin'] != $amazon_data['parent_asin'] && isset(self::$variants[$amazon_data['parent_asin']])
            && is_array(self::$variants[$amazon_data['parent_asin']])
            && count(self::$variants[$amazon_data['parent_asin']]) > 1) {
            $is_variant = true;
        } else {
            $is_variant = false;
        }
        $amazon_data['is_variant'] = $is_variant;


        if (isset($amazon_data['attributes']['PartNumber'])) {
            $product->supplier_reference = $amazon_data['attributes']['PartNumber'];
            unset($amazon_data['attributes']['PartNumber']);
        }

        if (isset($amazon_data['manufacturer'])) {
            if (Tools::strlen($amazon_data['manufacturer']) && Validate::isCatalogName($amazon_data['manufacturer'])) {
                if ($id_manufacturer = Manufacturer::getIdByName($amazon_data['manufacturer'])) {
                    $product->id_manufacturer = (int)$id_manufacturer;
                } else {
                    $manufacturer = new Manufacturer();
                    $manufacturer->name = trim($amazon_data['manufacturer']);
                    $manufacturer->active = true;

                    if ($manufacturer->validateFields(false) && $manufacturer->validateFieldsLang(false) && $manufacturer->add()) {
                        $product->id_manufacturer = (int)$manufacturer->id;
                    } else {
                        self::$errors[] = $debug = sprintf('%s: "%s"', $this->l('Unable to create manufacturer', $manufacturer->name));

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
                        }
                    }
                }
            }
        }
        $id_product_category = null;

        if (isset($amazon_data['category']) && Tools::strlen($amazon_data['category'])) {
            $id_product_category = $this->createCategory($amazon_data['category']);
        }
        if ($id_product_category) {
            $product->id_category_default = $id_product_category;
        } else {
            $product->id_category_default = $id_amazon_category;
        }

        $product->available_date = date('Y-m-d');

        if ($id_amazon_category || $id_product_category) {
            // add the category to module configuration
            if ($categories === null) {
                $categories = AmazonConfiguration::get('categories');
            }

            if (is_array($categories) && !in_array($id_amazon_category, $categories)) {
                $categories[] = $id_amazon_category;
                AmazonConfiguration::updateValue('categories', $categories);
            }
            if (is_array($categories) && !in_array($id_product_category, $categories)) {
                $categories[] = $id_product_category;
                AmazonConfiguration::updateValue('categories', $categories);
            }
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - id_category_default: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id_category_default));
        }
        $languages = self::$languages;

        $name_array = array();
        $link_array = array();
        $title = isset($amazon_data['parent_title']) && Tools::strlen($amazon_data['parent_title']) ? html_entity_decode($amazon_data['parent_title']) : html_entity_decode($amazon_data['title']);

        foreach ($languages as $language) {
            $id_lang = (int)$language['id_lang'];
            $overide = true;

            $name_array[$id_lang] = Tools::substr(str_replace(array('<', '>', ';', '=', '#', '{', '}'), '/', $title), 0, 128);
            $link_array[$id_lang] = Tools::substr(Tools::link_rewrite($name_array[$id_lang]), 0, 128) ;

            // Existing product in multilang
            if (is_numeric($amazon_data['id_lang']) && $amazon_data['id_lang'] == $id_lang && is_array($product->name)) {
                foreach ($product->name as $target_id_lang => $name) {
                    if (Tools::strlen($product->name[$target_id_lang]) && $target_id_lang != $product_id_lang) {
                        continue;
                    }
                    if (Tools::strlen($name)) {
                        $name_array[$target_id_lang] = $name;
                    }
                }
                foreach ($product->link_rewrite as $target_id_lang => $link_rewrite) {
                    if (Tools::strlen($product->link_rewrite) && $target_id_lang != $product_id_lang) {
                        continue;
                    }
                    if (Tools::strlen($link_rewrite)) {
                        $name_array[$target_id_lang] = $link_rewrite;
                    }
                }
            }
        }
        $tags = str_replace('-', ',', Tools::link_rewrite($name_array[$this->id_lang_default]));
        if (count($name_array)) {
            $product->name = $name_array;
        }
        if (count($link_array)) {
            $product->link_rewrite = $link_array;
        }
        if (count($name_array)) {
            $product->meta_title = $name_array;
            $product->meta_description = $name_array;
        }
        if (count($tags) && !Tools::strlen($product->meta_keywords)) {
            $product->meta_keywords = $tags;
        }
        if (!$product->ecotax) {
            $product->ecotax = isset($amazon_data['attributes']['WEEETaxValue']) ? $amazon_data['attributes']['WEEETaxValue'] : null;
        }

        if (!$product->validateFields(false, false)) {
            self::$errors[] = $debug = sprintf('%s: "%s"', $this->l('Unable to create product', $amazon_data['title']));

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
            }
            return(false);
        }
        if (method_exists('Product', 'getIdTaxRulesGroupMostUsed') && !$product->id_tax_rules_group) {
            $product->id_tax_rules_group = (int)Product::getIdTaxRulesGroupMostUsed();
        }

        if (Validate::isLoadedObject($product)) {
            $update = true;
        } else {
            $update = false;
        }

        try {
            if ($update) {
                self::$messages[] = sprintf('%s: %s - %s', $this->l('Updating product'), $sku, $title);
                $this->deleteImages($product);
                $product->update();

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - product id: %d'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id));
                }
            } else {
                self::$messages[] = sprintf('%s: %s - %s', $this->l('Creating product'), $sku, $title);
                $product->add();

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - product id: %d'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id));
                }
            }
        } catch (Exception $e) {
            self::$errors[] = $debug = 'Exception: '.  $e->getMessage(). "\n";

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
            }
            return(false);
        }

        if ($update && Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - updating existing product: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r(get_object_vars($product), true)));
        }

        if (!$update && method_exists('Product', 'addToCategories')) {
            $product->addToCategories($id_product_category);
        }
        if (method_exists('Tag', 'addTags')) {
            if ($update) {
                Tag::deleteTagsForProduct($product->id);
            }
            foreach ($languages as $language) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - updating tags: %d/%d %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id, $language['id_lang'], print_r($tags, true)));
                }
                Tag::addTags($language['id_lang'], $product->id, $tags);
            }
        }
        if (method_exists('Product', 'loadStockData')) {
            $product->loadStockData();
        }

        if (isset($amazon_data['features'])) {
            foreach ($amazon_data['features'] as $index => $bullet_point) {
                if (!Tools::strlen($bullet_point)) {
                    continue;
                }
                foreach ($languages as $language) {
                    $id_lang = (int)$language['id_lang'];

                    $product_options = AmazonProduct::getProductOptionsV4($product->id, null, $id_lang);

                    $field = 'bullet_point'.($index+1);

                    if (is_array($product_options)) {
                        $product_option = reset($product_options);
                        if (is_array($product_option) && isset($product_option[$field]) && Tools::strlen($product_option[$field])) {
                            continue;
                        }
                        AmazonProduct::updateProductOptions($product->id, $id_lang, $field, $amazon_data['features'][$index]);
                    }
                }
            }
        }
        AmazonProduct::updateProductOptions($product->id, $product_id_lang, 'nopexport', true);

        if (isset($amazon_data['browse_node_id']) && (int)$amazon_data['browse_node_id']) {
            AmazonProduct::updateProductOptions($product->id, $product_id_lang, 'browsenode', $amazon_data['browse_node_id']);
        }
        if (isset($amazon_product['item-note']) && Tools::strlen($amazon_product['item-note'])) {
            AmazonProduct::updateProductOptions($product->id, $product_id_lang, 'text', $amazon_product['item-note']);
        }

        if (Tools::getValue('update-price')) {
            // Tax calculation
            if (!$product->tax_rate) {
                $tax = new Tax();
                $product_tax_rate = $tax->getProductTaxRate($product->id);
                $product->tax_rate = $product_tax_rate;
            }
            $unit_price_tax_excl = $product->tax_rate  ? Tools::ps_round($amazon_product['price'] / ((100 + $product->tax_rate) / 100), 2) : Tools::ps_round($amazon_product['price'], 2);

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): %s - update tax for: %d tax: %.02f'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id, $product_tax_rate));
            }

            if (!$product->price && $is_variant) {
                $product->price = 0;
            } elseif (!$product->price) {
                $product->price = $unit_price_tax_excl;
            }

            $product->update();
        }

        if (Tools::getValue('import-features')) {
            $this->addFeatures($product, $amazon_product);
        }

        $this->addImages($product, $amazon_product);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - final product: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, print_r(get_object_vars($product), true)));
        }
        return($product);
    }

    protected function deleteImages(&$product)
    {
        $existing_images = Image::getImages($this->context->language->id, (int)$product->id);
        $product_has_images = (bool)$existing_images;

        if ($product_has_images) {
            foreach ($existing_images as $existing_image) {
                $image = new Image($existing_image['id_image']);
                $image->delete();
            }
        }
    }
    protected function addImages(&$product, &$amazon_product)
    {
        $amazon_data = &$amazon_product['amazon'];
        $languages = self::$languages;

        $image = new Image();
        $image->id_product = (int)$product->id;
        $image->position = Image::getHighestPosition($product->id) + 1;

        if (!Image::getCover((int)$product->id)) {
            $image->cover = 1;
        } else {
            $image->cover = 0;
        }

        foreach ($languages as $language) {
            $id_lang = $language['id_lang'];
            $image->legend[$id_lang] = isset($amazon_data['parent_title']) && Tools::strlen($amazon_data['parent_title']) ? $amazon_data['parent_title'] : $amazon_data['title'];
            ;
        }

        $image_url = isset($amazon_data['parent_image']) && Tools::strlen($amazon_data['parent_image']) ? $amazon_data['parent_image'] : $amazon_data['image'];
        $amazon_data['id_images'] = array();

        if (($field_error = $image->validateFields(false, true)) === true &&
        ($lang_field_error = $image->validateFieldsLang(false, true)) === true && $image->add()) {
            $path = $image->getPathForCreation().'.'.$image->image_format;
            $tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
            $copy_succeed = Tools::copy($image_url, $tmpfile);

            if (!$copy_succeed) {
                $image->delete();
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - failed to copy image: %d src: %s dst: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id, $tmpfile, $path));
                }
            } else {
                copy($tmpfile, $path);
                $amazon_data['id_images'][] = (int)$image->id;
            }
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): %s - image: %d src: %s dst: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id, $tmpfile, $path));
            }

            if (method_exists('ImageManager', 'resize')) {
                $imagesTypes = ImageType::getImagesTypes('products');
                foreach ($imagesTypes as $k => $image_type) {
                    $dstfile = $image->getPathForCreation().'-'.Tools::stripslashes($image_type['name']).'.'.$image->image_format;

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s(#%d): %s - product: %d src: %s dst: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $product->id, $tmpfile, $dstfile));
                    }
                    ImageManager::resize($tmpfile, $dstfile, $image_type['width'], $image_type['height'], $image->image_format);
                }
            }
            if (file_exists($tmpfile)) {
                unlink($tmpfile);
            }
        }
    }


    public function convertUnit($value, $source_unit)
    {
        $weight = Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT'));
        $dimensions = Tools::strtolower(Configuration::get('PS_DIMENSION_UNIT'));

        switch ($source_unit) {
            case 'MM':
            case 'CM':
            case 'M':
            case 'IN':
            case 'FT':
                $unit = Tools::strtolower($source_unit);
                $target_unit = $dimensions;
                break;
            case 'inches':
                $unit = 'in';
                $target_unit = $dimensions;
                break;
            case 'feet':
                $unit = 'ft';
                $target_unit = $dimensions;
                break;
            case 'meters':
                $unit = 'm';
                $target_unit = $dimensions;
                break;
            case 'decimeters':
                $unit = 'dm';
                $target_unit = $dimensions;
                break;
            case 'centimeters':
                $unit = 'cm';
                $target_unit = $dimensions;
                break;
            case 'millimeters':
                $unit = 'mm';
                $target_unit = $dimensions;
                break;
            case 'micrometers':
                $unit = 'μm';
                $target_unit = $dimensions;
                break;
            case 'nanometers':
                $unit = 'nm';
                $target_unit = $dimensions;
                break;
            case 'picometers':
                $unit = 'pm';
                $target_unit = $dimensions;
                break;
            case 'GR':
            case 'KG':
            case 'OZ':
            case 'LB':
            case 'MG':
                $unit = Tools::strtolower($source_unit);
                $target_unit = $weight;
                break;
            case 'pounds':
                $unit = 'lb';
                $target_unit = $weight;
                break;
            case 'kilograms':
                $unit = 'kg';
                $target_unit = $weight;
                break;
            case 'grams':
                $unit = 'gr';
                $target_unit = $weight;
                break;
            case 'ounces':
                $unit = 'oz';
                $target_unit = $weight;
                break;
            default:
                self::$errors[] = $debug = sprintf('%s: "%s"', $this->l('Unknown unit of mesure'), $source_unit);

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
                }
                return(false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - value: %s, unit: %s target: %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $value, $unit, $target_unit));
        }

        $simpleConvertor = new Convertor($value, $unit);
        $converted = $simpleConvertor->to($target_unit); //returns converted value

        if (!$converted) {
            self::$errors[] = $debug = sprintf($this->l('Unable to convert from "%" to "%s"', $source_unit, $target_unit));

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s(#%d): %s - %s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $debug));
            }
            return(false);
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s(#%d): %s - result: %.02f/%s'.Amazon::LF, basename(__FILE__), __LINE__, __FUNCTION__, $converted, $target_unit));
        }

        return(is_float($converted) ? sprintf('%.02f', $converted) : (int)$converted);
    }

    public function getProducts()
    {
        ob_start();

        $pass = false;
        $continue = false;
        $action='get-products';

        if ($this->initDownload()) {
            if ($offers = Tools::getValue('process')) {
                $pass = false;
                self::$offers = null;
                self::$process = false;

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf(
                        '%s(#%d): %s - Json Data: %d bytes - error: %s'.Amazon::LF,
                        basename(__FILE__),
                        __LINE__,
                        __FUNCTION__,
                        is_array($offers) ? count($offers) : 0,
                        json_last_error_msg()
                    ));
                    CommonTools::p($offers);
                }

                $continue = false;
                $action = 'parse-products';
                self::$end = true;
                $pass = true;
            } elseif (file_exists($this->file_inventory) && filesize($this->file_inventory) && filemtime($this->file_inventory) > time() - self::EXPIRE) {
                self::$messages[] = $message = sprintf($this->l('Using existings file: "%s" - Expires: %s'), basename($this->file_inventory), date('Y-m-d H:i:s', filemtime($this->file_inventory) + self::EXPIRE));

                // Inventory Exists, and downloaded, process the report
                $result = $this->processOffersInventory();

                if ($result) {
                    self::$messages[] = $message = sprintf('%d %s', count(self::$offers), $this->l('offers retrieved with success'));
                    $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $message);

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                    }
                    self::$end = true;
                    $pass = true;
                    $continue = false;
                } else {
                    self::$messages[] = $message = $this->l('No offers to process...');
                    $continue = false;
                }
            } elseif (file_exists($this->file_inventory) && !filesize($this->file_inventory)) {
                // Inventory Exists, but has not been downloaded

                // Check Timestamp
                // 1 - if timestamp more than 2 minutes; get report
                // 2 - if less ; ask to wait

                $request_time = filemtime($this->file_inventory);
                $now = time();
                $elapsed = $now - $request_time;

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s(#%d): %s - Request Time: "%s", elapsed: %d', basename(__FILE__), __LINE__, __FUNCTION__, date('c', $request_time), $elapsed));
                }

                if ($elapsed > 60 * 60) {
                    $error = $this->l('Delay to download report is expired');
                    $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
                    self::$errors[] = $error;

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                        CommonTools::p(sprintf('%s(#%d): %s - ERROR: Request Time: "%s", elapsed: %d - delay expired', basename(__FILE__), __LINE__, __FUNCTION__, date('c', $request_time), $elapsed));
                    }
                    unlink($this->file_inventory);
                    $continue = false;
                    $pass = false;
                } elseif ($elapsed < 60 * 2) {
                    $continue = true;
                    $pass = true;

                    self::$messages[] = $message = $this->l('Waiting a while for the report to be ready for download');
                    $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $message);

                    sleep(20);

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                    }
                } else {
                    $reportRequestId = $this->reportRequestList();

                    if ($reportRequestId) {
                        if ($this->getReport($reportRequestId)) {
                            self::$messages[] = $message = sprintf('%s (%s)', $this->l('Downloading Report ID'), $reportRequestId);
                            $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $message);

                            if (Amazon::$debug_mode) {
                                CommonTools::p($debug);
                            }

                            $continue = true;
                            $pass = true;
                        } else {
                            $error = $this->l('Failed to download the Report');
                            $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
                            self::$errors[] = $error;

                            if (Amazon::$debug_mode) {
                                CommonTools::p($debug);
                            }
                            $continue = false;
                            $pass = false;
                        }
                    } elseif ($reportRequestId === null) {
                        self::$messages[] = sprintf('%s (%s)', $this->l('Waiting for the report to be available... this operation could take time'), $reportRequestId);

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s(#%d): %s - A report has been already requested and there is not any available report yet', basename(__FILE__), __LINE__, __FUNCTION__));
                        }
                        touch($this->file_inventory);
                        $continue = true;
                        $pass = true;

                        sleep(20);
                    } elseif ($reportRequestId === false) {
                        self::$messages[] = sprintf('%s'.Amazon::LF, $this->l('Unexpected error'));
                        $pass = false;
                        $continue = false;
                        unlink($this->file_inventory);
                    }
                }
            } else {
                // File doesn't exist
                // 1 - Create the file
                // 2 - Request the Report

                if (!AmazonTools::isDirWriteable($this->import)) {
                    $error = sprintf('"%s" %s', $this->import, $this->l('is not a writable directory, please check directory permissions'));
                    $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error);
                    self::$errors[] = $error;

                    if (Amazon::$debug_mode) {
                        CommonTools::p("Error:$debug");
                    }
                    $continue = false;
                    $pass = false;
                }
                if (file_put_contents($this->file_inventory, null) === false) {
                    $error = sprintf('%s: "%s"', $this->import, $this->l('failed to create file'), $this->file_inventory);
                    $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error);
                    self::$errors[] = $error;

                    if (Amazon::$debug_mode) {
                        CommonTools::p("Error:$debug");
                    }
                    $continue = false;
                    $pass = false;
                }

                if ($reportRequestId = $this->reportRequest()) {
                    touch($this->file_inventory);

                    self::$messages[] = sprintf($this->l('Report has been requested (%s), please wait a while'), $reportRequestId);

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s(#%d): %s - Report Request ID: "%s"', basename(__FILE__), __LINE__, __FUNCTION__, $reportRequestId));
                    }
                    $continue = true;
                    $pass = true;

                    sleep(20);
                } else {
                    $error = $this->l('Request Report failed, please review your module configuration');
                    $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error);
                    self::$errors[] = $error;

                    if (Amazon::$debug_mode) {
                        CommonTools::p($debug);
                    }
                    $continue = false;
                    $pass = false;
                }
            }
        }

        $result =
            array(
                'error' => (count(self::$errors) ? true : false),
                'errors' => self::$errors,
                'message' => count(self::$messages) ? true : false,
                'messages' => self::$messages,
                'continue' => $continue,
                'pass' => $pass,
                'debug' => Amazon::$debug_mode,
                'output' => ob_get_clean(),
                'process' => self::$process,
                //'offers' => self::$offers,
                'action' => $action,
                'end' => self::$end,
            );


        $json = Tools::jsonEncode($result);

        if ($callback = Tools::getValue('callback')) {
            if ($callback == '?') {
                $callback = 'jsonp_'.time();
            }
            echo (string)$callback.'('.$json.')';
        } else {
            CommonTools::d($result);
        }
    }

    protected function processOffersInventory($display = true)
    {
        $offers = array();
        $conditions = array_flip(Amazon::$conditions);

        if (Amazon::$debug_mode) {
            printf('%s(#%d): processOffersInventory()'.nl2br(Amazon::LF), basename(__FILE__), __LINE__, __FUNCTION__);
        }

        if (!file_exists($this->file_inventory)) {
            $error = sprintf('%s: "%s"', $this->l('File not found'), $this->file_inventory);
            $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        if (($result = AmazonTools::fileGetContents($this->file_inventory)) === false) {
            $error = $this->l('Unable to read input file');
            $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        if ($result == null or empty($result)) {
            $error = $this->l('Inventory is empty !');
            $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        $lines = explode("\n", $result);

        if (!is_array($lines) || !count($lines)) {
            $error = $this->l('Inventory is empty !');
            $debug = sprintf('%s(#%d): %s - %s (%s)', basename(__FILE__), __LINE__, __FUNCTION__, $error, $this->file_inventory);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(str_repeat('-', 160));
            CommonTools::p(sprintf('Inventory: %s products'.nl2br(Amazon::LF), count($lines) - 1));  // -1: less the header
        }

        $header = reset($lines);

        if (!Tools::strlen($header)) {
            $error = $this->l('No header, file might be corrupted');
            $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }

        $found = null;
        $scores = array();

        $columns = explode("\t", AmazonTools::noAccents(Tools::strtolower(utf8_encode($header))));

        foreach (self::$expected as $key => $expected) {
            $array_keys = array_diff($columns, $expected);
            $scores[$key] = count(array_diff($columns, $expected));
        }
        asort($scores);
        reset($scores);
        $found = key($scores);

        // Header, display to the user he doesn't have merchant shipping group
        if (!$found) {
            $error = sprintf('%s: %s', $this->l('Header incorrect, please contact the support with a screenshot of this page'), print_r($columns));
            $debug = sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, __FUNCTION__, $error);
            self::$errors[] = $error;

            if (Amazon::$debug_mode) {
                CommonTools::p("Error:$debug");
            }
            return (false);
        }
        $count = 0;

        $columns_keys = array_flip($columns);

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            if ($count++ < 1) {
                continue;
            }

            $result = explode("\t", AmazonTools::fixEncoding(rtrim($line)));

            if (!isset($result[0]) || !Tools::strlen($result[0])) {
                continue;
            }

            $values = array();

            // We got
            // item-name	listing-id	seller-sku	price	quantity	open-date	product-id-type	item-note	item-condition	will-ship-internationally	expedited-shipping	product-id	pending-quantity	fulfillment-channel	merchant-shipping-group
            foreach (self::$expected[$found] as $key => $to_search) {
                if (isset($columns_keys[$to_search]) && isset(self::$expected[$found][$key])) {
                    $column_name = self::$expected['en'][$key];
                    $target_key = $columns_keys[$to_search];
                    $values[$column_name] = $result[$target_key];
                }
            }

            // We get
            // Cells referenced by self::$expected
            $condition_id = (int)$values['item-condition'];

            $item_name = trim(addslashes($values['item-name']));
            $seller_sku = trim(addslashes($values['seller-sku']));
            $qty = (int)$values['quantity'];
            $price = (float)$values['price'];
            $product_id = (string)$values['product-id'];
            $product_id_type = (string)$values['product-id-type'];
            $condition = isset(Amazon::$conditions[$condition_id]) ? Amazon::$conditions[$condition_id] : null;
            $active = (string)$values['status'];
            $item_node = (string)$values['item-note'];

            if (!strstr($active, 'Active')) {
                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('Inactive Item: %s'.nl2br(Amazon::LF), print_r($values, true)));
                }
                continue;
            }

            if ($count < 100 && $display) {
                $output = sprintf('%s %s %s', $seller_sku, $condition, $item_name);
                echo htmlspecialchars(trim($output), ENT_NOQUOTES).nl2br(Amazon::LF);
            } elseif ($count == 100 && $display) {
                echo nl2br($this->l('And many others...').Amazon::LF);
            }

            if ($qty <= 0) {
                //continue;
            }
            // Create collection
            $offer = array();
            $offer['name'] = $item_name;
            $offer['sku'] = $seller_sku;
            $offer['qty'] = $qty;
            $offer['price'] = $price;
            $offer['condition'] = $condition;
            $offer['product-id'] = $product_id;
            $offer['product-id-type'] = $product_id_type;
            $offer['item-note'] = $item_node;

            $offers[$seller_sku] = $offer;
        }
        self::$offers = $offers;
        self::$process = true;

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Processed Items: %s'.nl2br(Amazon::LF), print_r($count, true)));
        }

        if (count($offers)) {
            return (true);
        } else {
            return(false);
        }
    }
}

$amazonProductsImport = new AmazonProductsImport();
$amazonProductsImport->dispatch();
