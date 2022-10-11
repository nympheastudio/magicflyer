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

include 'Node.php';

class AmazonXmlParse
{
    private $referenceTag   = 'Product';
    private $tagsForParsing = array('CompetitivePricing', 'SalesRankings', 'LowestOfferListings');
    private $tagsAsArray    = array('LowestOfferListings', 'Weight', 'ns2:Weight');
    private $parsedInstance = array();

    public function __construct(SimpleXMLElement $simpleXmlElement, $newReferenceTag = null, $newTagsForParsing = null)
    {
        if ($newReferenceTag != null) {
            $this->referenceTag = $newReferenceTag;
        }

        if ($newTagsForParsing != null) {
            $this->tagsForParsing = $newTagsForParsing;
        }

        $xmlFile = dom_import_simplexml($simpleXmlElement)->ownerDocument;
        $productsArray = array();

        $parents = $xmlFile->getElementsByTagName($this->referenceTag);

        //get parents elements
        foreach ($parents as $p) {
            $parentName = $p->nodeName;
            $instance = new stdClass();
            $instance->$parentName = new stdClass();
            $instance->$parentName = $this->getElementAttributes($p, new stdClass());

            //looks for every defined tag
            foreach ($this->tagsForParsing as $target) {
                $result = $p->getElementsByTagName($target);

                if ($result->length > 0) {
                    $instance->$parentName->$target = new stdClass();
                }

                foreach ($result as $element) {
                    if (!AmazonNode::hasNoData($element->nodeName)) {
                        $name = $element->nodeName;
                        if (in_array($name, $this->tagsAsArray)) {
                            $array = array();

                            if (isset($instance->$parentName->$target->$name)) {
                                $array = $instance->$parentName->$target->$name;
                            } else {
                                $instance->$parentName->$target->$name = array();
                            }

                            $array[] = $this->getElementInstance($element, true);
                            $instance->$parentName->$target->$name = $array;
                        } else {
                            $instance->$parentName->$target->$name = $this->getElementInstance($element, false);
                        }
                    }
                }
            }
            $productsArray[] = $instance;
        }

        $this->parsedInstance = $productsArray;
    }

    private function getElementAttributes(DOMElement $element, stdClass $instance)
    {
        if (!$element->hasAttributes()) {
            return $instance;
        }

        $instance->attr = array();

        foreach ($element->attributes as $attr) {
            $attrName = $attr->nodeName;
            $attrValue = $attr->nodeValue;
            $instance->attr[$attrName] = $attrValue;
        }

        return $instance;
    }

    private function getElementInstance(DOMElement $element, $asArray = false)
    {
        //$elementInstance = new stdClass();
        $elementInstance = $this->getElementAttributes($element, new stdClass());
        $array = array();
        $arraysFound = array();

        foreach ($element->childNodes as $child) {
            $childName = $child->nodeName;
            if (!AmazonNode::hasNoData($childName) && $asArray) {
                if (!in_array($childName, $arraysFound)) {
                    $arraysFound[] = $childName;
                }

                if (in_array($childName, $this->tagsAsArray)) {
                    $array[$childName][] = $this->getElementInstance($element, true);
                } else {
                    $array[$childName][] = $this->getElementInstance($element, false);
                }
            } else {
                if (!AmazonNode::hasNoData($childName)) {
                    if (in_array($childName, $this->tagsAsArray)) {
                        $elementInstance->$childName = $this->getElementInstance($child, true);
                    } else {
                        $elementInstance->$childName = $this->getElementInstance($child, false);
                    }
                } elseif (trim($child->textContent) != '') {
                    $elementInstance->value = $child->nodeValue;
                }
            }
        }

        foreach ($arraysFound as $r) {
            $elementInstance->$r = $array[$r];
        }

        return $elementInstance;
    }

    public static function getSingleTagValues(SimpleXMLElement $simpleXmlElement, $searchedTags, $baseTag = null)
    {
        $values = array();
        $parent = $baseTag;

        if ($parent == null) {
            foreach ($searchedTags as $searchedTag) {
                if ($searchedTag == null) {
                    return $values;
                } else {
                    //convert simple xml to DOM

                    $xmlFile = dom_import_simplexml($simpleXmlElement)->ownerDocument;
                    $nodes = $xmlFile->getElementsByTagName($searchedTag);
                    foreach ($nodes as $node) {
                        $i = count($values);
                        $values[] = array();
                        if (trim($node->textContent) != '') {
                            $values[$i]['value'] = $node->textContent;
                        }

                        if ($node->hasAttributes()) {
                            $values[$i]['attr'] = array();
                            foreach ($node->attributes as $attr) {
                                $attrName = $attr->nodeName;
                                $attrValue = $attr->nodeValue;
                                $values[$i]['attr'][$attrName] = $attrValue;
                            }
                        }
                    }
                }
            }
        } else {/* convert simple xml to DOM*/
            $xmlFile = dom_import_simplexml($simpleXmlElement)->ownerDocument;
            $parents = $xmlFile->getElementsByTagName($parent);

            foreach ($parents as $p) {
                $parentValues = array();
                foreach ($searchedTags as $searchedTag) {
                    $nodes = $p->getElementsByTagName($searchedTag);
                    foreach ($nodes as $node) {
                        $i = count($parentValues);

                        if (trim($node->textContent) != '') {
                            $parentValues[$i]['value'] = $node->textContent;
                        }


                        if ($node->hasAttributes()) {
                            $parentValues[$i]['attr'] = array();
                            foreach ($node->attributes as $attr) {
                                $attrName = $attr->nodeName;
                                $attrValue = $attr->nodeValue;
                                $parentValues[$i]['attr'][$attrName] = $attrValue;
                            }
                        }
                    }
                }
                $values[] = $parentValues;
            }
        }

        return $values;
    }

    public function getParsedInstance()
    {
        return $this->parsedInstance;
    }
}
