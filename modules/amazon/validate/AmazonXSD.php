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
 * @author    Erick Turcios
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */

require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.tools.class.php');

/**
 * Class AmazonXSD
 */
class AmazonXSD
{
    /**
     *
     */
    const DEFINITION_URL = 'https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd';
    /**
     *
     */
    const MAIN_URL = 'https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_4_1/Product.xsd';
    /**
     *
     */
    const BASE_XSD = 'https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_4_1/amzn-base.xsd';
    /**
     *
     */
    const RELEASE = 'release_1_9';

    /**
     * @var array
     */
    public static $requireMfrPartNumber = array(
        'AutoAccessoryMisc',
        'AutoPart',
        'PowersportsPart',
        'PowersportsVehicle',
        'ProtectiveGear',
        'Helmet',
        'RidingApparel',
        'Tire',
        'Rims',
        'TireAndWheel',
        'Vehicle',
        'Motorcycles',
        'Motorcyclepart',
        'Motorcycleaccessory',
        'Parts',
        'Ridinggloves',
        'Ridingboots',
        'PaperProducts',
        'PhoneAccessory',
        'Phone',
        'Gifts_and_Occasions',
        'ApplianceAccessory',
        'Kitchen',
        'PersonalCareAppliances',
        'EntertainmentMemorabilia',
        'Musical_Instruments',
        'PetSuppliesMisc',
        'PersonalCareAppliances',
        'ReceiverOrAmplifier',
        'WritingInstruments',
        'FilmCamera',
        'Camcorder',
        'DigitalCamera',
        'DigitalFrame',
        'Binocular',
        'SurveillanceSystem',
        'Telescope',
        'Microscope',
        'Darkroom',
        'Lens',
        'LensAccessory',
        'Filter',
        'Film',
        'BagCase',
        'BlankMedia',
        'PhotoPaper',
        'Cleaner',
        'Flash',
        'TripodStand',
        'Lighting',
        'Projection',
        'PhotoStudio',
        'LightMeter',
        'PowerSupply',
        'OtherAccessory',
        'ComputerComponent'
    );

    /**
     * @var array
     */
    public static $langColorMap = array(
        'beige' => array('fr' => 'Beige', 'de' => 'Beige', 'it' => 'Beige', 'es' => 'Beige'),
        'black' => array('fr' => 'Noir', 'de' => '???', 'it' => 'Nero', 'es' => 'Negro'),
        'blue' => array('fr' => 'Bleu', 'de' => 'Blau', 'it' => 'Blue', 'es' => 'Azul'),
        'brown' => array('fr' => 'Marron', 'de' => 'Braun', 'it' => 'Marrone', 'es' => 'Marrón'),
        'gold' => array('fr' => 'Or', 'de' => 'Gold', 'it' => 'Oro', 'es' => 'Dorado'),
        'grey' => array('fr' => 'Gris', 'de' => 'Grau', 'it' => 'Grigio', 'es' => 'Gris'),
        'green' => array('fr' => 'Vert', 'de' => 'Grün', 'it' => 'Verde', 'es' => 'Verde'),
        'multicolored' => array(
            'en' => 'multi-colored',
            'fr' => 'Multicolore',
            'de' => 'Mehrfarbig',
            'it' => 'Multicolore',
            'es' => 'Multicolor'
        ),
        'offwhite' => array(
            'en' => 'off-white',
            'fr' => 'Ecru',
            'de' => 'Elfenbein',
            'it' => 'Avorio',
            'es' => 'Marfil'
        ),
        'orange' => array('fr' => 'Orange', 'de' => 'Orange', 'it' => 'Arancione', 'es' => 'Naranja'),
        'pink' => array('fr' => 'Rose', 'de' => 'Rosa', 'it' => 'Rosa', 'es' => 'Rosa'),
        'purple' => array('fr' => 'Violet', 'de' => 'Violett', 'it' => 'Viola'),
        'red' => array('fr' => 'Rouge', 'de' => 'Rot', 'it' => 'Rosso', 'es' => 'Rojo'),
        'silver' => array('fr' => 'Argent', 'de' => 'Silber', 'it' => 'Argento', 'es' => 'Plateado'),
        'white' => array('fr' => 'Blanc', 'de' => 'Weiß', 'it' => 'Bianco', 'es' => 'Blanco'),
        'yellow' => array('fr' => 'Jaune', 'de' => 'Gelb', 'it' => 'Giallo', 'es' => 'Amarillo')
        /*
        'brass' => array('fr' => 'noir', 'de' => 'schwarz', 'it' => '', 'es' => ''),
        'bronze' => array('fr' => '???', 'de' => '???', 'it' => '', 'es' => ''),
        'burst' => array('fr' => '???', 'de' => '???', 'it' => '', 'es' => ''),
        'chrome' => array('fr' => '???', 'de' => '???', 'it' => '', 'es' => ''),
        'clear' => array('fr' => '???', 'de' => '???', 'it' => '', 'es' => ''),
        'metallic' => array('fr' => '???', 'de' => '???', 'it' => '', 'es' => ''),
        'natural' => array('fr' => '???', 'de' => '???', 'it' => '', 'es' => ''),
        */
    );

    /**
     * @var array
     */
    public static $recommendedPerUniverseFields = array( // Obsolete
                                                         //'AutoAccessory'	  => array('ModelYear', 'Season', 'ManufacturerWarrantyDescription'),
    );
    /**
     * @var array
     */
    public static $recommendedPerTypeFields     = array( // Obsolete
                                                         //'Watch' => array('BandColor', 'BandMaterial', 'BandLength', 'BandWidth', 'DialColor', 'MovementType', 'WaterResistantDepth', 'ModelYear', 'Season', 'TargetGender'),
    );

    /**
     * @var array
     */
    public static $rewriteFields = array(
        'Itempackagequantity' => 'ItemPackageQuantity',
        'Patternname' => 'PatternName'
    );

    /**
     * @var array
     */
    public static $rewriteFieldsUniverse = array(
        'CE' => array('ColorName' => 'Color', 'SizeName' => 'Size'),
        'Home' => array('ColorName' => 'Color', 'SizeName' => 'Size'),
        'Baby' => array('Color' => 'ColorName', 'Size' => 'SizeName'),
        'Luggage' => array('ColorName' => 'Color', 'SizeName' => 'Size'),
        'Sports' => array('Color-Size' => 'ColorSize')
    );

    /**
     * @var array
     */
    public static $isMapRequired = array(
    'Luggage' => true
    );

    public static $descriptionDataOptionalFields = array(
        'Designer',
        'MerchantCatalogNumber',
        'MSRP',
        'MSRPWithTax',
        'MaxOrderQuantity',
        'SerialNumberRequired',
        'CPSIAWarning',
        'CPSIAWarningDescription',
        'LegalDisclaimer',
        'TargetAudience',
        'TSDAgeWarning',
        'TSDWarning',
        'TSDLanguage',
        'OptionalPaymentTypeExclusion',
        'Battery',
        'AreBatteriesIncluded',
        'AreBatteriesRequired',
        'BatterySubgroup',
        'NumberOfBatteries',
        'BatteryType',
        'BatteryCellType',
        'BatteryWeight',
        'NumberOfLithiumMetalCells',
        'NumberOfLithiumIonCells',
        'LithiumBatteryPackaging',
        'LithiumBatteryEnergyContent',
        'LithiumBatteryWeight',
        'SupplierDeclaredDGHZRegulation',
        'CaliforniaProposition65ComplianceType'
    );

    /**
     * @var array
     */
    public static $ceTypes = array(
        'Antenna',
        'AudioVideoAccessory',
        'AVFurniture',
        'BarCodeReader',
        'CEBinocular',
        'CECamcorder',
        'CameraBagsAndCases',
        'CEBattery',
        'CEBlankMedia',
        'CableOrAdapter',
        'CECameraFlash',
        'CameraLenses',
        'CameraOtherAccessories',
        'CameraPowerSupply',
        'CarAlarm',
        'CarAudioOrTheater',
        'CarElectronics',
        'ConsumerElectronics',
        'CEDigitalCamera',
        'DigitalPictureFrame',
        'DigitalVideoRecorder',
        'DVDPlayerOrRecorder',
        'CEFilmCamera',
        'GPSOrNavigationAccessory',
        'GPSOrNavigationSystem',
        'HandheldOrPDA',
        'Headphones',
        'HomeTheaterSystemOrHTIB',
        'KindleAccessories',
        'KindleEReaderAccessories',
        'KindleFireAccessories',
        'MediaPlayer',
        'MediaPlayerOrEReaderAccessory',
        'MediaStorage',
        'MiscAudioComponents',
        'PC',
        'PDA',
        'Phone',
        'PhoneAccessory',
        'PhotographicStudioItems',
        'PortableAudio',
        'PortableAvDevice',
        'PowerSuppliesOrProtection',
        'RadarDetector',
        'RadioOrClockRadio',
        'ReceiverOrAmplifier',
        'RemoteControl',
        'Speakers',
        'StereoShelfSystem',
        'CETelescope',
        'Television',
        'Tuner',
        'TVCombos',
        'TwoWayRadio',
        'VCR',
        'CEVideoProjector',
        'VideoProjectorsAndAccessories'
    );

    /**
     * @var array
     */
    public static $excludedFields = array('ProductSubtype', 'ColorMap', 'SizeMap', 'ProductName', 'Manufacturer', 'Parentage', 'VariationTheme');

    /**
     * @var array
     */
    public static $product_type_duplicated_exception = array('ClothingAccessories' => 1);
    /**
     * @var null
     */
    private $mainDom;

    /**
     * @var
     */
    private $baseDom;
    /**
     * @var null
     */
    private $productDom;
    /**
     * @var stdClass
     */
    private $productInstance;
    /**
     * @var type
     */
    private $productName;
    /**
     * @var array
     */
    private $productType;
    /**
     * @var array
     */
    private $attributeTypes = array(
        'type' => 'type',
        'mandatory' => 'mandatory',
        'limit' => 'limit',
        'maxDigits' => 'maxDigits',
        'maxLength' => 'maxLength',
        'minLength' => 'minLength',
        'pattern' => 'pattern',
        'minValue' => 'minValue',
        'maxValue' => 'maxValue',
        'allowedValues' => 'allowedValues',
        'value' => 'value',
        'attr' => 'attr'
    );

    /**
     * @var array
     */
    private static $exceptions = array('LargeAppliances.xsd' => 'http://g-ecx.images-amazon.com/images/G/01/rainier/help/xsd/release_4_1/LargeAppliances.xsd');

    /**
     * @var array
     */
    public static $product_instance_cache = array();

    /**
     * Set initial values from XSD files
     * @param type $product
     */
    public function __construct($product)
    {
        $this->productName = $product;
        $this->productInstance = new stdClass();
        $this->setDomDocuments($product);

        $query = '//xsd:schema/xsd:element[@name="Product"]/xsd:complexType/xsd:sequence/*';

        $x = new DOMXPath($this->mainDom);
        $content = $x->query($query);
        $this->productInstance = $this->getElements($content);

        $this->mainDom = null;
        $this->urlDom = null;
        $this->productDom = null;
    }

    public static function getStandardColor($color, $region)
    {
        $source_color_key = AmazonTools::toKey($color);
        if (Amazon::$debug_mode) {
            CommonTools::p("getStandardColor, Color:".$color);
            CommonTools::p("getStandardColor, Region: ".$region);
        }
        foreach (self::$langColorMap as $color_key => $color_map) {
            if (Tools::strlen($source_color_key) && isset($color_map[$region]) && AmazonTools::toKey($color_map[$region]) == $source_color_key) {
                if (Amazon::$debug_mode) {
                    CommonTools::p("getStandardColor, Returns: ".$color);
                }
                return($color);
            }
        }
        return(null);
    }

    /**
     * Added by Olivier 2012/11/27
     * Loads Main, Base and Product XSD schemas
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    private function setDomDocuments($product)
    {
        static $dom_cache = array();

        if (!Tools::strlen($product)) {
            return (false);
        }

        //Load Main XSD
        $this->mainDom = new DOMDocument();
        $this->mainDom->loadXML(self::cache(self::MAIN_URL));
        //Load Base XSD
        $this->urlDom = new DOMDocument();
        $this->urlDom->loadXML(self::cache(self::BASE_XSD));
        //Load Product XSD
        $this->productDom = new DOMDocument();

        if (isset(self::$exceptions[$product])) {
            $file = self::$exceptions[$product];
        } else {
            $file = self::DEFINITION_URL.'/'.self::RELEASE.'/'.$product;
        }

        $content = self::cache($file);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s:%s - ', basename(__FILE__), __LINE__));
            CommonTools::p(sprintf('File: %s', $file));
            CommonTools::p(sprintf('Content: %s....', Tools::substr($content, 0, 256)));
        }
        if (!$content) {
            return(false);
        }

        if (!array_key_exists($product, $dom_cache)) {
            $this->productDom->loadXML($content);
            $dom_cache[$product] = $this->productDom;
        } else {
            $this->productDom = $dom_cache[$product];
        }

        foreach (array('productDom', 'urlDom', 'mainDom') as $dom_to_inspect) {
            $x = new DOMXPath($this->$dom_to_inspect);
            $content = $x->query('//xsd:annotation|//text()');

            foreach ($content as $node) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    /**
     * @param $URL
     *
     * @return bool|mixed
     */
    public static function cache($URL)
    {
        $dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'xsd';

        $gz_file = $dir.DIRECTORY_SEPARATOR.basename($URL).'.gz';

        if (file_exists($gz_file) && filesize($gz_file) > 512) {
            if ((time() - filemtime($gz_file)) < ((60 * 60 * 24 * 30) + rand(86400, 86400 * 7))) {
                return (AmazonTools::fileGetContents('compress.zlib://'.$gz_file));
            }
        }

        $contents = AmazonTools::fileGetContents($URL, false, null, 30);

        if (file_exists($gz_file) && filesize($gz_file) > 512 && empty($gz_file)) {
            return (AmazonTools::fileGetContents($gz_file));
        } elseif (empty($contents)) {
            return (false);
        }

        if (file_exists($gz_file)) {
            unlink($gz_file);
        }

        if (!is_dir($dir)) {
            if (!mkdir($dir)) {
                return (false);
            }
        }

        if (!is_writable($dir)) {
            chmod($dir, 0775);
        }

        if (!file_put_contents('compress.zlib://'.$gz_file, $contents)) {
            return (AmazonTools::fileGetContents('compress.zlib://'.$gz_file));
        }

        touch($gz_file);

        return ($contents);
    }


    /**
     * Analize every child element to obtain its data detail
     * @param DOMNodeList $elements
     * @param bool $ProductData
     *
     * @return stdClass
     */
    private function getElements(DOMNodeList &$elements, &$ProductData = false)
    {
        //This will contain the custom Product
        $result = new stdClass();
        $instance = new stdClass();
        //Evaluates every node from the Node List
        foreach ($elements as $element) {
            $name = $element->getAttribute('name');
            $ref = $element->getAttribute('ref');

            $result = $this->getElementStructure($element);

            //deletes unnecesary Tags
            $n = '';
            if ($ref != null) {
                $instance->ref = true;
                $n = $ref;
            } else {
                $instance->ref = false;
                $n = $name;
            }
            //if ($n == 'TargetGender') { print_r($element);  }

            if ($result != null) {
                if (isset($instance->ProductType->$n)) {
                    continue;
                }

                $instance->mandatory = null;
                $instance->value = null;
                $instance->$n = $result;
                $instance->$n->ref = $instance->ref;
            }
        }

        return $instance;
    }

    /**
     * Obtains the structure of an XSD Element
     * @param DOMElement $element
     * @param type $parentNode
     * @return null
     */
    private function getElementStructure(DOMElement &$element, $parentNode = null)
    {
        //These Node Types are omitted by default
        if (AmazonNode::hasNoData($element->nodeName) || !AmazonNode::isElement($element->nodeName)) {
            return null;
        }

        if (!$element->hasChildNodes() && XmlDataType::isXmlDataType($element->getAttribute('type'))) {
            $object = new stdClass();
            $object->mandatory = 'false';
            $object->value = null;
            $object->type = XmlDataType::getHtmlEquivalent($element->getAttribute('type'));
            $object->limit = '';

            $minOccurs = $element->getAttribute('minOccurs');

            if (isset($minOccurs) && $minOccurs >= 1) {
                $object->mandatory = 'true';
            }

            $maxOccurs = $element->getAttribute('maxOccurs');
            if (isset($maxOccurs) && $maxOccurs > '0') {
                $object->limit = $maxOccurs;
            }

            $this->getRestriction($element->getAttribute('name'), $element, $object);

            return $object;
        }

        $min = $element->getAttribute('minOccurs');
        $element = $this->getRefElement($element, isset($min) ? $min : null);

        if (!$element) {
            return null;
        }

        $name = $element->getAttribute('name');
        $type = $element->getAttribute('type');

        $elementWithChildren = $element;
        $typeDetail = $this->getTypeStructure($type, $element);

        if ($typeDetail != null) {
            $element->removeAttribute('type');
            $element->appendChild($typeDetail);
            $elementWithChildren = $element;
        }

        //
        if (!$elementWithChildren) {
            return null;
        }
        $parent_node = $element->parentNode;
        $is_choice = ($parent_node && $parent_node->nodeName == "xsd:choice") ? true : false ;

        if (AmazonNode::isElement($elementWithChildren->nodeName)) {
            $object = new stdClass();
            $object->mandatory = null;
            $object->choice = null;
            $object->value = null;
            $object->is_child = true;

            if ($elementWithChildren->hasChildNodes()) {
                $value = 'true';
                $mandatory = $elementWithChildren->getAttribute('minOccurs');
                
                if (($mandatory != null && $mandatory == 0) || $is_choice) {
                    $value = 'false';
                }

                $object = $this->getChildrenProperties($elementWithChildren, $parentNode, $name);

                if ($object != null) {
                    $object->mandatory = $value;
                }

                $maxOccurs = $elementWithChildren->getAttribute('maxOccurs');
                if (isset($maxOccurs) && $maxOccurs > 0) {
                    $object->limit = $maxOccurs;
                }
            } else {
                $object->mandatory = 'false';
                $object->type = XmlDataType::getHtmlEquivalent($elementWithChildren->getAttribute('type'));
                $object->limit = '';
               
                $minOccurs = $elementWithChildren->getAttribute('minOccurs');
                     
                if (isset($minOccurs) && $minOccurs >= 1 && !$is_choice) {
                    $object->mandatory = 'true';
                }

                $maxOccurs = $elementWithChildren->getAttribute('maxOccurs');
                if (isset($maxOccurs) && $maxOccurs > 0) {
                    $object->limit = $maxOccurs;
                }
            }

            if ($is_choice) {
                $object->choice = 'true';
                $minOccurs = $parent_node->getAttribute('minOccurs');
                if ($minOccurs == "" || $minOccurs > 0) {
                    $object->mandatory = 'true';
                }
            }

            return $object;
        }

        return null;
    }

    /**
     * @param $name
     * @param DOMElement $restriction
     * @param $obj
     *
     * @return mixed
     */
    private function getRestriction($name, DOMElement $restriction, $obj)
    {
        //$obj = new stdClass();
        $allowedValues = array();
        /*
        $obj->maxDigits = '';
        $obj->maxLength = '';
        $obj->minLength = '';
        $obj->pattern = '';
        $obj->minValue = '';
        $obj->maxValue = '';
        $obj->allowedValues = '';
         */
        if (!isset($obj->maxDigits)) {
            $obj->maxDigits = '';
        }

        if (!isset($obj->maxLength)) {
            $obj->maxLength = '';
        }

        if (!isset($obj->minLength)) {
            $obj->minLength = '';
        }

        if (!isset($obj->pattern)) {
            $obj->pattern = '';
        }

        if (!isset($obj->minValue)) {
            $obj->minValue = '';
        }

        if (!isset($obj->maxValue)) {
            $obj->maxValue = '';
        }

        if (!isset($obj->allowedValues)) {
            $obj->allowedValues = '';
        }


        foreach ($restriction->childNodes as $r) {
            if (!AmazonNode::hasNoData($r->nodeName)) {
                $attrValue = null;
                try {
                    $attrValue = $r->getAttribute('value');
                } catch (Exception $e) {
                    $attrValue = null;
                }

                switch ($r->nodeName) {
                    //Defines a list of acceptable values
                    case 'xsd:enumeration':
                        if ($attrValue != null) {
                            $allowedValues[] = $attrValue;
                        }

                        break;
                    //Specifies the maximum number of decimal places allowed. Must be equal to or greater than zero
                    case 'xsd:fractionDigits':
                        if ($attrValue != null) {
                            $obj->maxDigits = $attrValue;
                        }

                        break;
                    //Specifies the exact number of characters or list items allowed. Must be equal to or greater than zero
                    case 'xsd:length':
                        if ($attrValue != null) {
                            $obj->maxLength = $attrValue;
                        }

                        break;
                    //Specifies the minimum number of characters or list items allowed. Must be equal to or greater than zero
                    case 'xsd:minLength':
                        if ($attrValue != null) {
                            $obj->minLength = $attrValue;
                        }

                        break;
                    //Specifies the maximum number of characters or list items allowed. Must be equal to or greater than zero
                    case 'xsd:maxLength':
                        if ($attrValue != null) {
                            $obj->maxLength = $attrValue;
                        }

                        break;
                    //Defines the exact sequence of characters that are acceptable
                    case 'xsd:pattern':
                        if ($attrValue != null) {
                            $obj->pattern = $attrValue;
                        }

                        break;
                    //Specifies the lower bounds for numeric values (the value must be greater than or equal to this value)
                    case 'xsd:minInclusive':
                        if ($attrValue != null) {
                            $obj->minValue = $attrValue;
                        }

                        break;
                    //Specifies the lower bounds for numeric values (the value must be greater than this value)
                    case 'xsd:minExclusive':
                        if ($attrValue != null) {
                            $obj->minValue = $attrValue + 1;
                        }

                        break;
                    //Specifies the upper bounds for numeric values (the value must be less than or equal to this value)
                    case 'xsd:maxInclusive':
                        if ($attrValue != null) {
                            $obj->maxValue = $attrValue;
                        }

                        break;
                    //Specifies the upper bounds for numeric values (the value must be less than this value)
                    case 'xsd:maxExclusive':
                        if ($attrValue != null) {
                            $obj->maxValue = $attrValue - 1;
                        }

                        break;
                    //Specifies the exact number of digits allowed. Must be greater than zero
                    case 'xsd:totalDigits':
                        if ($attrValue != null) {
                            $obj->maxLength = $attrValue;
                        }

                        break;
                    //Specifies how white space (line feeds, tabs, spaces, and carriage returns) is handled
                    case 'xsd:whiteSpace':
                        break;
                    default:
                        break;//throw new Exception($r->nodeName . ' was not considered');
                }
            }
        }

        if (count($allowedValues) > 0) {
            $obj->allowedValues = $allowedValues;
        }

        return $obj;
    }

    /**
     * Searches in XSD files related (Base or Product) for the $ref Definition
     * @param DOMElement $element
     * @param null $min
     *
     * @return bool|DOMElement
     */
    private function getRefElement(DOMElement $element, $min = null)
    {
        $ref = $element->getAttribute('ref');

        if ($ref == null) {
            return $element;
        }

        $query = 'xsd:element[@name="'.$ref.'"]';
        $xsd = array('main' => $this->mainDom, 'base' => $this->urlDom, 'prod' => $this->productDom);

        foreach ($xsd as $schema) {
            $x = new DOMXPath($schema);
            $result = $x->query($query);
            if ($result) {
                foreach ($result as $node) {
                    if ($min != null) {
                        $node->setAttribute('minOccurs', $min);
                    }

                    return $this->mainDom->importNode($node->cloneNode(true), true);
                }
            }
        }

        return false;
    }

    /**
     *
     * @param type $type
     * @param DOMElement $element
     * @param type $found
     * @return null|\DOMElement|boolean
     * @throws Exception
     */
    private function getTypeStructure($type, DOMElement &$element, $forced = false)
    {
        if ($type == null || XmlDataType::isXmlDataType($type)) {
            return null;
        }

        if ((!$forced && $element->hasChildNodes() && !XmlDataType::isXmlDataType($type))) {
            return null;
        }

        $arrTags = array('xsd:simpleType', 'xsd:complexType');
        $domDocuments = array('main' => $this->mainDom, 'base' => $this->urlDom, 'prod' => $this->productDom);

        foreach ($arrTags as $tag) {
            $queryTypeElement = $tag.'[@name="'.(string)$type.'"]';
            foreach ($domDocuments as $dom) {
                $x = new DOMXPath($dom);
                $result = $x->query($queryTypeElement);
                foreach ($result as $node) {
                    if (!AmazonNode::hasNoData($node->nodeName)) {
                        return $this->mainDom->importNode($node->cloneNode(true), true);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Obtains properties from child simpleType/complexType Elements
     * @param DOMElement $element
     * @param type $parentNode
     * @param type $name
     * @return null
     */
    private function getChildrenProperties(DOMElement &$element, $parentNode, $name)
    {
        foreach ($element->childNodes as $node) {
            $nodeName = str_replace('xsd:', '', $node->nodeName);
            if (!AmazonNode::hasNoData($nodeName)) {
                switch ($nodeName) {
                    case 'simpleType':
                        return $this->getSimpleType($name, $node);
                        break;
                    case 'complexType':
                        return $this->getComplexType($name, $node, $parentNode);
                        break;
                    case 'simpleContent':
                        return $this->getSimpleContent($name, $node);
                        break;
                    default:
                        return null;
                }
            }
        }

        return (null);
    }

    /**
     * @param $name
     * @param DOMElement $element
     *
     * @return null|stdClass
     */
    private function getSimpleType($name, DOMElement $element)
    {
        $object = new stdClass();
        foreach ($element->childNodes as $node) {
            if (!AmazonNode::hasNoData($node->nodeName)) {
                switch ($node->nodeName) {
                    case 'xsd:union':
                        $object->mandatory = 'false';
                        $object->value = null;
                        $object->type = 'text';
                        $arrTypes = explode(' ', $node->getAttribute('memberTypes'));
                        $el = $this->mainDom->createElement('xsd:element');

                        foreach ($arrTypes as $t) {
                            $el->setAttribute('type', $t);
                            $typeDetail = $this->getTypeStructure($t, $el, true);

                            if ($typeDetail != null) {
                                foreach ($typeDetail->childNodes as $det) {
                                    if ($det->hasChildNodes()) {
                                        foreach ($det->childNodes as $toAdd) {
                                            if (!AmazonNode::hasNoData($toAdd->nodeName)) {
                                                try {
                                                    $el->appendChild($toAdd->cloneNode(true));
                                                } catch (Exception $ex) {
                                                    null;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        ////////////////////////////////////////////////////////////////
                        if (!isset($object->limit)) {
                            $object->limit = '';
                        }

                        $minOccurs = $element->getAttribute('minOccurs');
                        if (isset($minOccurs) && $minOccurs >= 1) {
                            $object->mandatory = 'true';
                        }

                        $maxOccurs = $element->getAttribute('maxOccurs');
                        if (isset($maxOccurs) && $maxOccurs > '0') {
                            $object->limit = $maxOccurs;
                        }

                        $this->getRestriction($name, $el, $object);

                        return $object;
                        break;
                    case 'xsd:restriction':
                        $object->mandatory = 'false';
                        $object->value = null;
                        $object->type = XmlDataType::getHtmlEquivalent($node->getAttribute('base'));
                        ////////////////////////////////////////////////////////////////
                        //added on Oct 16th, 2012. Some types are extensions of Custom Types
                        if ($object->type != null && !XmlDataType::isHtmlEquivalent($object->type)) {
                            $typeDetail = $this->getTypeStructure($object->type, $node, true);
                            if ($typeDetail != null) {
                                foreach ($typeDetail->childNodes as $det) {
                                    if ($det->hasChildNodes()) {
                                        $object->type = XmlDataType::getHtmlEquivalent($det->getAttribute('base'));
                                        foreach ($det->childNodes as $toAdd) {
                                            if (!AmazonNode::hasNoData($toAdd->nodeName)) {
                                                try {
                                                    $node->appendChild($toAdd->cloneNode(true));
                                                } catch (Exception $ex) {
                                                    null;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        ////////////////////////////////////////////////////////////////
                        $object->limit = '';

                        $minOccurs = $element->getAttribute('minOccurs');
                        if (isset($minOccurs) && $minOccurs >= 1) {
                            $object->mandatory = 'true';
                        }

                        $maxOccurs = $element->getAttribute('maxOccurs');
                        if (isset($maxOccurs) && $maxOccurs > '0') {
                            $object->limit = $maxOccurs;
                        }

                        $this->getRestriction($name, $node, $object);

                        return $object;
                    default:
                        break;// throw new Exception($node->nodeName . ' was not considered');
                }
            }
        }

        return null;
    }

    /**
     * @param $name
     * @param DOMElement $element
     * @param $parentNode
     *
     * @return null|stdClass
     */
    private function getComplexType($name, DOMElement &$element, $parentNode)
    {
        foreach ($element->childNodes as $node) {
            if (!AmazonNode::hasNoData($node->nodeName) && $node->nodeName == 'xsd:simpleContent') {
                $return = $this->getSimpleContent($name, $node);
                if ($return != null) {
                    return $return;
                    break;
                }
            }
        }

        $x = new DOMXPath($this->mainDom);
        $query = '*/xsd:element';
        //current element defined as context
        $content = $x->query($query, $element);
        if ($content) {
            $complexType = $this->getElements($content);
        }

        return $complexType;
    }

    /**
     * @param $name
     * @param DOMElement $element
     *
     * @return null|stdClass
     */
    private function getSimpleContent($name, DOMElement &$element)
    {
        $object = new stdClass();
        $object->mandatory = null;
        foreach ($element->childNodes as $node) {
            if (!AmazonNode::hasNoData($node->nodeName)) {
                switch ($node->nodeName) {
                    case 'xsd:extension':
                        $arrTags = array('xsd:simpleType', 'xsd:complexType');
                        $baseType = $node->getAttribute('base');
                        $arrDom = array($this->mainDom, $this->urlDom, $this->productDom);

                        foreach ($arrTags as $tag) {
                            $querySimple = $tag.'[@name="'.$baseType.'"]';
                            foreach ($arrDom as $dom) {
                                $x = new DOMXPath($dom);
                                $e = $x->query($querySimple);
                                foreach ($e as $typeFound) {
                                    $object = $this->getSimpleType($name, $typeFound);
                                    break;
                                }
                            }
                        }

                        $attributes = null;
                        $object->attr = new stdClass();

                        foreach ($node->childNodes as $attr) {
                            if ($attr->nodeName == 'xsd:attribute') {
                                $attrName = $attr->getAttribute('name');
                                $attribute = $attr->getAttribute('type');
                                $required = 'false';

                                if ($attr->getAttribute('use') === 'required') {
                                    $required = 'true';
                                }

                                if ($attrName != null) {
                                    foreach ($arrTags as $tag) {
                                        $querySimple = $tag.'[@name="'.$attribute.'"]';
                                        foreach ($arrDom as $dom) {
                                            $x = new DOMXPath($dom);
                                            $e = $x->query($querySimple);
                                            foreach ($e as $typeFound) {
                                                $attrClass = $this->getSimpleType($name, $typeFound);
                                                break;
                                            }
                                        }
                                    }
                                    if (!isset($attrClass) || !is_object($attrClass)) {
                                        $attrClass = new stdClass();
                                    }

                                    $attrClass->mandatory = $required;
                                    $object->attr->$attrName = $attrClass;
                                }
                            }
                        }

                        return $object;
                        break;
                    default:
                        break;//throw new Exception($node->nodeName . ' was not considered');
                }
            }
        }

        return $object;
    }

    /**
     * @return array
     */
    public static function getCategories()
    {
        $dom = new DOMDocument();

        $content = self::cache(self::MAIN_URL);

        if (!Tools::strlen($content)) {
            return(false);
        }

        $dom->loadXML($content);

        $x = new DOMXPath($dom);
        $xclude = array(
            'AdditionalProductInformation.xsd',
            'FBA.xsd',
            'amzn-base.xsd',
            'Amazon.xsd',
            'MaterialHandling.xsd'
        );

        $query = '//xsd:schema/xsd:include';
        $categories = $x->query($query);
        $cat = array();
        foreach ($categories as $category) {
            $val = $category->getAttribute('schemaLocation');
            if (isset($val) && !in_array($val, $xclude)) {
                $cat[] = str_replace('.xsd', '', $val);
            }
        }

        return $cat;
    }

    /**
     * @param $elements
     * @param bool $mandatoryOnly
     *
     * @return stdClass
     */
    public static function getFields(&$elements, $mandatoryOnly = false)
    {
        $productInstanceMandatoryFields = new stdClass();
        if ($elements instanceof stdClass) {
            $var = get_object_vars($elements);
            foreach ($var as $key => $val) {
                if (self::isParentWithChildren($key)) {
                    $productInstanceMandatoryFields->$key = self::getFieldsChildren($elements->$key, true, $mandatoryOnly);
                } else {
                    $productInstanceMandatoryFields->$key = self::getFields($elements->$key, $mandatoryOnly);
                }
            }
        } else {
            return $elements;
        }
        return $productInstanceMandatoryFields;
    }

    /**
     * @param $tagName
     *
     * @return bool
     */
    public static function isParentWithChildren($tagName)
    {
        $mandatoryParentsWithChildren = array('AgeRecommendation');

        if (in_array($tagName, $mandatoryParentsWithChildren)) {
            return true;
        }

        return false;
    }

    /**
     * @param $elements
     * @param bool $parentMandatory
     * @param bool $mandatoryOnly
     *
     * @return stdClass
     */
    public static function getFieldsChildren($elements, $parentMandatory = false, $mandatoryOnly = false)
    {
        $productInstanceMandatoryFields = new stdClass();
        if ($elements instanceof stdClass) {
            $var = get_object_vars($elements);
            foreach ($var as $key => $val) {
                if ($mandatoryOnly && self::isMandatoryField($elements->$key) || $parentMandatory) {
                    $productInstanceMandatoryFields->$key = self::getMandatoryFieldsChildren($elements->$key, true);
                } elseif (!$mandatoryOnly) {
                    $productInstanceMandatoryFields->$key = self::getFieldsChildren($elements->$key, true, $mandatoryOnly);
                }
            }
        } else {
            return $elements;
        }

        return $productInstanceMandatoryFields;
    }

    /**
     * @param $item
     *
     * @return bool
     */
    public static function isMandatoryField(&$item)
    {
        if (isset($item->mandatory) && (!$item->mandatory || $item->mandatory == 'false')) {
            return false;
        } else {
            if (isset($item->choice) && ($item->choice || $item->choice == 'true')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $elements
     * @param bool $parentMandatory
     *
     * @return stdClass
     */
    public static function getMandatoryFieldsChildren(&$elements, $parentMandatory = false)
    {
        $productInstanceMandatoryFields = new stdClass();
        if ($elements instanceof stdClass) {
            $var = get_object_vars($elements);
            foreach ($var as $key => $val) {
                if (self::isMandatoryField($elements->$key) || $parentMandatory) {
                    $productInstanceMandatoryFields->$key = self::getMandatoryFieldsChildren($elements->$key, true);
                }
            }
        } else {
            return $elements;
        }

        return $productInstanceMandatoryFields;
    }

    /**
     * @param $elements
     *
     * @return stdClass
     */
    public static function getMandatoryFields(&$elements)
    {
        $productInstanceMandatoryFields = new stdClass();
        if ($elements instanceof stdClass) {
            $var = get_object_vars($elements);
            foreach ($var as $key => $val) {
                if (self::isMandatoryField($elements->$key)) {
                    if (self::isParentWithChildren($key)) {
                        $productInstanceMandatoryFields->$key = self::getMandatoryFieldsChildren($elements->$key, true);
                    } else {
                        $productInstanceMandatoryFields->$key = self::getMandatoryFields($elements->$key);
                    }
                }
            }
        } else {
            return $elements;
        }

        return $productInstanceMandatoryFields;
    }

    /**
     * @param $productInstance
     * @param $type
     * @param null $name
     *
     * @return bool
     */
    public static function getVariationData(&$productInstance, $type, $name = null)
    {
        if ($name == null) {
            foreach (get_object_vars($productInstance->ProductData) as $key => $val) {
                if ($val instanceof stdClass) {
                    $name = $key;
                    break;
                }
            }

            if ($name == null) {
                return false;
            }
        }

        $category = self::getProductNameEquivalent($name);

        if (isset($productInstance->ProductData->$category->$type)) {
            return $productInstance->ProductData->$category->$type;
        } elseif (isset($productInstance->ProductData->$category->VariationData->$type)) {
            return $productInstance->ProductData->$category->VariationData->$type;
        } else {
            //checks if has one only Child

            if (isset($productInstance->ProductData->$category->ProductType)) {
                $pt = get_object_vars($productInstance->ProductData->$category->ProductType);

                if (is_array($pt) && count($pt) <= 3) {
                    foreach ($pt as $key => $val) {
                        if (isset($productInstance->ProductData->$category->ProductType->$key->$type)) {
                            return $productInstance->ProductData->$category->ProductType->$key->$type;
                        } elseif (isset($productInstance->ProductData->$category->ProductType->$key->VariationData->$type)) {
                            return $productInstance->ProductData->$category->ProductType->$key->VariationData->$type;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $category
     *
     * @return mixed
     */
    public static function getProductNameEquivalent($category)
    {
        $arr = array('ProductClothing' => 'Clothing', 'SWVG' => 'SoftwareVideoGames');

        if (isset($arr[$category])) {
            return $arr[$category];
        }

        return $category;
    }

    /**
     * @param $productInstance
     * @param $searchedElement
     * @param null $refElement
     * @param bool $recursiveSearch
     * @param null $parentPath
     * @param bool $casesensitive
     *
     * @return bool|string
     */
    public static function getPathToElement(&$productInstance, $searchedElement, $refElement = null, $recursiveSearch = true, $parentPath = null, $casesensitive = true)
    {
        static $path_cache = array();
        $arr = array();
        $path = '';

        $cache_key = self::toKey(sprintf('%s/%s/%s', md5(serialize($productInstance)), $refElement, $searchedElement));

        //if ($searchedElement == 'MetalType') var_dump($cache_key, $refElement, $path_cache);

        if (isset($path_cache[$cache_key]) && Tools::strlen($path_cache[$cache_key]) && !$recursiveSearch) {
            return($path_cache[$cache_key]);
        }

        if ($refElement != null) {
            $refPath = self::getPathToElement($productInstance, $refElement);
            if (!$refPath) {
                return(false);
            }

            $refElements = explode('->', $refPath);
            $product = $productInstance;
            foreach ($refElements as $key => $val) {
                $product = $product->$val;
            }

            $complement = self::getPathToElement($product, $searchedElement);

            if ($complement) {
                return($path_cache[$cache_key] = $refPath.'->'.$complement);
            } else {
                return(false);
            }
        }

        if ($parentPath != null) {
            $path = $parentPath.'->';
        }

        if ($productInstance instanceof stdClass) {
            $arr = get_object_vars($productInstance);
        } elseif (is_array($productInstance)) {
            $arr = $productInstance;
        } else {
            return(false);
        }

        foreach ($arr as $key => $val) {
            if ($casesensitive && $searchedElement == $key) {
                return($path_cache[$cache_key] = $path.$key);
            } elseif (!$casesensitive && Tools::strtolower($searchedElement) == Tools::strtolower($key)) {
                return($path_cache[$cache_key] = $path.$key);
            } else {
                if (!($val instanceof stdClass) or !$recursiveSearch) {
                    continue;
                }

                $childFound = self::getPathToElement($val, $searchedElement, null, true, $path.$key, $casesensitive);

                if ($childFound) {
                    return(($path_cache[$cache_key] = $childFound));
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        $arr = get_object_vars($this);

        return $arr;
    }

    /**
     * @param array $structure
     * @param null $object
     * @param string $name
     *
     * @return array|null|stdClass
     */
    public function getInstanceElementsArray(&$structure, $object = null, $name = 'AmazonXSD')
    {
        $arr = array();
        if ($object == null && $name == 'AmazonXSD') {
            $object = $this->getInstance();
        }

        if ($object instanceof stdClass) {
            $arr = get_object_vars($object);
        } elseif (!($object instanceof stdClass) && !(is_array($object))) {
            //it's a single value

            return $object;
        } elseif (is_array($object)) {
            $arr = $object;
        } else {
            return null;
        }

        foreach ($arr as $key => $value) {
            //it's not an attribute like value, mandatory, etc

            if (!$this->isAttribute($key)) {
                $structure[$key] = array();
                $structure[$key] = $this->getInstanceElementsArray($structure[$key], $value, $key);

                if ($structure[$key] == null) {
                    unset($structure[$key]);
                    $structure[$key] = $value;
                }
            }
        }

        return $structure;
    }

    /**
     * @return stdClass
     */
    public function getInstance()
    {
        return $this->productInstance;
    }

    /**
     * @param $universe
     * @param $product_type
     */
    public function filterProductType($universe, $product_type)
    {
        if (isset($this->productInstance->ProductData->{$universe}->ProductType)) {
            foreach ($this->productInstance->ProductData->{$universe}->ProductType as $name => $product_type_item) {
                if (is_object($product_type_item) && (string)$name != $product_type && $product_type != 'ConsumerElectronics') {
                    unset($this->productInstance->ProductData->{$universe}->ProductType->{$name});
                }
            }
        }
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function isAttribute($key)
    {
        return isset($this->attributeTypes[$key]);
    }

    /**
     * @param $str
     *
     * @return bool|mixed|string
     */
    public static function toKey($str)
    {
        $str = str_replace(array('-', ',', '.', '/', '+', '.', ':', ';', '>', '<', '?', '(', ')', '!'), array(
                '_',
                'a',
                'b',
                'c',
                'd',
                'e',
                'f',
                'g',
                'h',
                'i',
                'j',
                'k',
                'l',
                'm'
            ), $str);
        $str = Tools::strtolower(preg_replace('/[^A-Za-z0-9_]/', '', $str));

        return $str;
    }
}
