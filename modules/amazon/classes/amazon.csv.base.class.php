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

class AmazonCSVBase
{
    /**
     * This to an array
     * @return array
     * @throws Exception
     */
    public function toArray()
    {
        $properties = $this->getProperties();
        $data = array();
        foreach ($properties as $prop) {
            $data[$prop] = $this->__get($prop);
        }

        return $data;
    }

    /**
     * Returns self properties
     * @return array
     */
    public function getProperties()
    {
        $properties = get_object_vars($this);
        $props = array();
        foreach ($properties as $k => $v) {
            $props[] = $k;
        }

        return $props;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->{$property};
        } else {
            throw new Exception(get_class($this).' Invalid property get '.$property);
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->{$property} = $value;
        } else {
            throw new Exception(get_class($this).' Invalid property set '.$property);
        }
    }

    public function __call($method, $args)
    {
        if (preg_match('/^set_(.+)$/', $method, $match)) {
            $property = $match[1];
            $this->__set($property, $args[0]);
        } else {
            if (preg_match('/^get_(.+)$/', $method, $match)) {
                $property = $match[1];

                return $this->__get($property);
            } else {
                throw new Exception(get_class($this).' Invalid method '.$method);
            }
        }

        return null;
    }

    public function hasProperty($property)
    {
        return property_exists($this, $property) ? true : false;
    }
}
