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

include_once('Lnp2WSObject.php');

class Lnp2OrderRequestWS extends Lnp2WSObject
{
    public $fields = array(
        'partnerName'          => array('required' => true),
        'orderExt'             => array('required' => true),
        'height'               => array('required' => true),
        'width'                => array('required' => true),
        'length'               => array('required' => true),
        'weight'               => array('required' => true),
        'pudo'                 => array('required' => true),
        'pudoName'             => array('required' => true),
        'pudoAdress1'          => array('required' => true),
        'pudoAdress2'          => array('required' => true),
        'pudoZipCode'          => array('required' => true),
        'pudoCity'             => array('required' => true),
        'pudoCountry'          => array('required' => true),
        'pudoCountryCode'      => array('required' => true),
        'recipientContactName' => array('required' => true),
        'recipientEmail'       => array('required' => true),
        'recipientPhone'       => array('required' => true),
        'senderCivility'       => array('required' => true),
        'senderMail'           => array('required' => true),
        'senderPhone'          => array('required' => true),
        'senderName'           => array('required' => true),
        'senderName2'          => array('required' => true),
        'senderAdress1'        => array('required' => true),
        'senderAdress2'        => array('required' => true),
        'senderZipCode'        => array('required' => true),
        'senderCity'           => array('required' => true),
        'senderCountryCode'    => array('required' => true),
        'senderCountryName'    => array('required' => true),
        'content'              => array('required' => false),
        'insuranceEnabled'     => array('required' => false),
        'contentPrice'         => array('required' => false),
    );

    public function validateParams($params)
    {
        parent::validateParams($params);

        if (!Validate::isString($params['partnerName'])) {
            $this->validation_errors[] = 'partnerName is not a alphanumeric string';
        }
        if (!Validate::isString($params['orderExt'])) {
            $this->validation_errors[] = 'orderExt is not a alphanumeric string';
        }
        if (!Validate::isFloat($params['height'])) {
            $this->validation_errors[] = 'height is not a float';
        }
        if (!Validate::isFloat($params['width'])) {
            $this->validation_errors[] = 'width is not a float';
        }
        if (!Validate::isFloat($params['length'])) {
            $this->validation_errors[] = 'length is not a float';
        }
        if (!Validate::isFloat($params['weight'])) {
            $this->validation_errors[] = 'weight is not a float';
        }
        if (!Validate::isString($params['pudo'])) {
            $this->validation_errors[] = 'pudo is not a alphanumeric string';
        }
        if (!Validate::isString($params['pudoName'])) {
            $this->validation_errors[] = 'pudoName is not a alphanumeric string';
        }
        if (!Validate::isString($params['pudoAdress1'])) {
            $this->validation_errors[] = 'pudoAddress1 is not a alphanumeric string';
        }
        if (!Validate::isString($params['pudoAdress2'])) {
            $this->validation_errors[] = 'pudoAddress2 is not a alphanumeric string';
        }
        if (!Validate::isString($params['pudoZipCode'])) {
            $this->validation_errors[] = 'pudoZipCode is not a alphanumeric string';
        }
        if (!Validate::isString($params['pudoCity'])) {
            $this->validation_errors[] = 'pudoCity is not a alphanumeric string';
        }
        if (!Validate::isString($params['pudoCountry'])) {
            $this->validation_errors[] = 'pudoCountry is not a alphanumeric string';
        }
        if (!Validate::isString($params['pudoCountryCode'])) {
            $this->validation_errors[] = 'pudoCountryCode is not a alphanumeric string';
        }
        if (!Validate::isString($params['recipientContactName'])) {
            $this->validation_errors[] = 'recipientContactName is not a alphanumeric string';
        }
        if (!Validate::isString($params['recipientEmail'])) {
            $this->validation_errors[] = 'recipientEmail is not a alphanumeric string';
        }
        if (!Validate::isString($params['recipientPhone'])) {
            $this->validation_errors[] = 'recipientPhone is not a alphanumeric string';
        }
        if (!Validate::isString($params['senderCivility'])) {
            $this->validation_errors[] = 'senderCivility is not a alphanumeric string';
        }
        if (!Validate::isString($params['senderMail'])) {
            $this->validation_errors[] = 'senderMail is not a alphanumeric string';
        }
        if (!Validate::isString($params['senderPhone'])) {
            $this->validation_errors[] = 'senderPhone is not a alphanumeric string';
        }
        if (!Validate::isString($params['senderName'])) {
            $this->validation_errors[] = 'senderName is not a alphanumeric string';
        }
        if (!Validate::isString($params['senderName2'])) {
            $this->validation_errors[] = 'senderName2 is not a alphanumeric string';
        }
        if (!Validate::isString($params['senderAdress1'])) {
            $this->validation_errors[] = 'senderAdress1 is not a alphanumeric string';
        }
        if (!Validate::isString($params['senderAdress2'])) {
            $this->validation_errors[] = 'senderAdress2 is not a alphanumeric string';
        }
        if (!Validate::isString($params['senderZipCode'])) {
            $this->validation_errors[] = 'senderZipCode is not a alphanumeric string';
        }
        if (!Validate::isString($params['senderCountryCode'])) {
            $this->validation_errors[] = 'senderCountryCode is not a alphanumeric string';
        }
        if (!Validate::isString($params['senderCountryName'])) {
            $this->validation_errors[] = 'senderCountryName is not a alphanumeric string';
        }
        if (!Validate::isString($params['content'])) {
            $this->validation_errors[] = 'content is not a alphanumeric string';
        }
        if (!Validate::isBool($params['insuranceEnabled'])) {
            $this->validation_errors[] = 'insuranceEnabled is not a boolean';
        }
        if (!Validate::isFloat($params['contentPrice'])) {
            $this->validation_errors[] = 'contentPrice is not a float';
        }
    }
}
