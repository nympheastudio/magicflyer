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

class AmazonValidator
{
    private $throwException      = false;
    private $errors              = array();/*stores exceptions raised, when $throwException = false*/
    private $elementRestrictions = array(
        'type',
        'mandatory',
        'limit',
        'maxDigits',
        'maxLength',
        'minLength',
        'pattern',
        'minValue',
        'maxValue'
    );
    private $allowedValues       = 'allowedValues';
    private $elementValue        = 'value';
    private $elementAttr         = 'attr';

    private $instance;

    public function __construct($object, $throwException = false)
    {
        if ($throwException) {
            $this->throwException = true;
        }

        $obj = clone $object;
        $this->instance = get_object_vars($obj);
    }

    /**
     * Receives an Instance of a Class to valide according to mainStructure defined in constructor
     * @param type $object
     */
    public function validateInstance($object)
    {
        $productAttributes = get_object_vars($object);

        foreach ($this->instance as $element => $baseValue) {
            $this->validateBaseStructure($element, $baseValue, $productAttributes[$element], $element);
        }
    }

    /**
     * This function is created to be called recursively when validating.
     * @param type $baseElement
     * @param type $baseValue
     * @param type $instanceValue
     * @param type $origin
     * @return boolean
     */
    private function validateBaseStructure($baseElement, $baseValue, $instanceValue, $origin)
    {
        $x = null;
        $y = null;

        //Any attribute is a stdClass
        if ($baseValue instanceof stdClass) {
            $x = get_object_vars($baseValue);
            $this->validateElement($baseValue, $instanceValue, $origin);
        }

        if (is_array($baseValue)) {
            $x = $baseValue;
        }
    }

    public function validateElement($baseValue, $instanceValue, $origin)
    {
        $conditions = get_object_vars($baseValue);
        $mandatory = false;
        $hasChildren = false;
        foreach ($conditions as $key => $val) {
            if ($val instanceof stdClass && $key != 'attr') {
                $hasChildren = true;
                break;
            }
        }

        //Verifies if this array contains structure data
        foreach ($conditions as $key => $val) {
            if ($key == 'mandatory' && $val == 'true') {
                $mandatory = true;
            } elseif ($key == 'mandatory' && $val == 'false') {
                $mandatory = false;
            }

            $value = $this->getValue($instanceValue);
            if (in_array($key, $this->elementRestrictions)) {
                $this->validateRestriction($key, $val, $value, $this->getSequence($origin, $key), $mandatory, $hasChildren);
            }
        }

        foreach ($conditions as $key => $val) {
            $instance = null;

            if ($key == 'mandatory' && $val == 'true') {
                $mandatory = true;
            } elseif ($key == 'mandatory' && $val == 'false') {
                $mandatory = false;
            }

            //Set value from instancex class
            if (is_array($instanceValue)) {
                $arr = $instanceValue;
                if (isset($arr[$key])) {
                    $instance = $arr[$key];
                } else {
                    $instance = null;
                }
            } elseif ($instanceValue instanceof stdClass) {
                $arr = get_object_vars($instanceValue);
                if (isset($arr[$key])) {
                    $instance = $arr[$key];
                } else {
                    $instance = null;
                }
            } else {
                $instance = $instanceValue;
            }

            if (is_array($val)) {
                $this->validateBaseStructure($key, $val, $instance, $this->getSequence($origin, $key));
            }

            if ($val instanceof stdClass) {
                $this->validateElement($val, $instance, $this->getSequence($origin, $key));
            }
        }
    }

    private function getValue($instanceValue)
    {
        $value = null;
        if (isset($instanceValue->value)) {
            $value = $instanceValue->value;
        } else {
            if (!is_array($instanceValue) && !($instanceValue instanceof stdClass)) {
                $value = $instanceValue;
            }
        }

        if ($instanceValue instanceof stdClass) {
            $x = get_object_vars($instanceValue);
            foreach ($x as $child) {
                if ($child instanceof stdClass) {
                    $value = 'Has children atributes';
                } //just to indicate it has a value, this is not displayed
            }
        }

        return $value;
    }

    private function validateRestriction($key, $val, $instanceValue, $origin, $parentMandatory, $hasChildren)
    {
        switch ($key) {
            case 'mandatory':
                if ($val == 'true' && $instanceValue == null) {
                    $this->raiseException('Mandatory value must be entered', $origin);
                }
                break;
            case 'type':
                $this->validateType($val, $instanceValue, $origin);
                break;
            case 'limit': //pending;
                break;
            case 'maxDigits':
                if ($val == null) {
                    break;
                }

                $p = strpos($instanceValue, '.'); //decimal position

                if ($p > 0 && Tools::strlen(Tools::substr($instanceValue, $p + 1)) > $val) {
                    $this->raiseException('Wrong number of digits.', $origin);
                }

                break;
            case 'maxLength':
                if ($val != null && Tools::strlen($instanceValue) > $val) {
                    $this->raiseException('Max Length is: '.$val, $origin);
                }
                break;
            case 'minLength':
                if ($val != null && Tools::strlen($instanceValue) < $val) {
                    $this->raiseException('Min Length is: '.$val, $origin);
                }
                break;
            case 'pattern':
                if ($val != null && preg_match('\''.trim($val).'\'', $instanceValue) == 0) {
                    $this->raiseException('Not a valid pattern ('.$val.') for this field. ', $origin);
                }

                break;
            case 'minValue':
                if ($val != null && $instanceValue < $val) {
                    $this->raiseException('Min Value is:'.$val, $origin);
                }
                break;
            case 'maxValue':
                if ($val != null && $instanceValue > $val) {
                    $this->raiseException('Max Length is :'.$val, $origin);
                }
                break;
            case 'allowedValues':
                $arr = null;
                if (!is_array($val)) {
                    $arr = array($val);
                } else {
                    $arr = $val;
                }

                if (empty($arr)) {
                    break;
                }

                if ($arr != null && !in_array($instanceValue, $arr)) {
                    $str = implode(',', $arr);
                    $this->raiseException($instanceValue.' is not any of permitted values: '.$str, $origin);
                }
                break;
        }
    }

    private function raiseException($error, $sequence)
    {
        if ($this->throwException) {
            throw new Exception($error.' Reference: '.$sequence);
        } else {
            $arr = array($error, $sequence);
            if (!in_array($arr, $this->errors)) {
                $this->errors[] = $arr;
            }
        }
    }

    private function validateType($type, &$value, $origin)
    {
        $t = $type;
        $textPattern = '/[\0x\0x00\0x01\0x02\0x03\0x04\0x05\0x06\0x07\0x08\0x0B\0x0C\0x0E\0x0F\0x10\0x11\0x12\0x13\0x14\0x15\0x16\0x17\0x18\0x19\0x1A\0x1B\0x1C\0x1D\0x1E\0x1F\0x7F]+/';
        $numberPattern = '/[^0-9\.]+/';
        $date = array(
            'date',
            'time',
            'dateTime',
            'duration',
            'gDay',
            'gMonth',
            'gMonthDay',
            'gYear',
            'gYearMonth'
        );
        if (in_array($t, $date)) {
            $t = 'date';
        }
        switch ($t) {
            case 'text':
                preg_replace($textPattern, '', $value);
                break;
            case 'number':
                if (preg_match($numberPattern, $value) > 0) {
                    $this->raiseException('Number format Not Valid: '.$value, $origin);
                }
                break;
            case 'date':
                $this->validateDate($type, $value, $origin);
                break;
            case 'boolean':
                if (Tools::strtolower($value) != 'true' && Tools::strtolower($value) != 'false') {
                    $this->raiseException('Value must TRUE or FALSE, found: '.$value, $origin);
                }
                break;
            default:
                $this->raiseException('Type "'.$type.'" is not defined in this validation.', $origin);
        }
    }

    private function validateDate($type, $value, $origin)
    {
        switch ($type) {
            case 'date':
                if (preg_match('/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $value) == 0) {
                    $this->raiseException('Date format Not Valid: '.$value, $origin);
                }
                break;

            case 'time':
                if (preg_match('/[0-2]{2}:[0-9]{2}:[0-9]{2}$/', $value) == 0) {
                    $this->raiseException('Date format Not Valid: '.$value, $origin);
                }
                break;
            case 'dateTime':
                if (preg_match('/[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1] [0-2]{2}:[0-9]{2}:[0-9]{2})$/', $value) == 0) {
                    $this->raiseException('Date Time format Not Valid: '.$value, $origin);
                }
                break;
            case 'duration':
                break; //need the regex to be included here
            case 'gDay':
                if (preg_match('(\d{2})', $value) == 0) {
                    $this->raiseException('Day format Not Valid: '.$value, $origin);
                }
                break;

            case 'gMonth':
                if (preg_match('(\d{2})', $value) == 0) {
                    $this->raiseException('Month format Not Valid: '.$value, $origin);
                }
                break;
            case 'gMonthDay':
                if (preg_match('(\d{2})-(\d{2})', $value) == 0) {
                    $this->raiseException('Month-Day format Not Valid: '.$value, $origin);
                }
                break;
            case 'gYear':
                if (preg_match('(\d{4})', $value) == 0) {
                    $this->raiseException('Year format Not Valid: '.$value, $origin);
                }
                break;
            case 'gYearMonth':
                if (preg_match('(\d{4})-(\d{2})', $value) == 0) {
                    $this->raiseException('Year-Month format Not Valid: '.$value, $origin);
                }
                break;
            default:
                $this->raiseException('Unknown Date format: '.$value, $origin);
        }
    }

    private function getSequence($previousCall, $currentCall)
    {
        if (in_array($currentCall, $this->elementRestrictions) || $currentCall == $this->elementAttr) {
            return $previousCall;
        }

        return $previousCall.'->'.$currentCall;
    }

    public function getErrors()
    {
        if (!$this->throwException) {
            return $this->errors;
        }

        return null;
    }
}
