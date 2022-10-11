<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class Lnp2WSObject
{
    public $validation_errors = array();

    public function __construct($params)
    {
        if (!isset($this->fields)) {
            throw new LogicException(get_class($this).' must have a $fields');
        }
        $this->validateParams($params);
        if (count($this->validation_errors) == 0) {
            $this->assignParams($params);
        }
    }


    public function assignParams($params)
    {
        foreach ($this->fields as $field => $value) {
            if (isset($params[$field])) {
                $this->$field = $params[$field];
            }
        }
    }

    public function validateParams($params)
    {
        //Verify that all required fields are in params
        foreach ($this->fields as $field => $value) {
            if (isset($value['required']) && $value['required'] && !isset($params[$field])) {
                $this->validation_errors[] = $field.' empty';
            }
        }
    }
}
