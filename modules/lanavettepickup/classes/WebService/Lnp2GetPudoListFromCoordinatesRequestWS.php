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

class Lnp2GetPudoListFromCoordinatesRequestWS extends Lnp2WSObject
{
    public $fields = array(
        'partnerName'       => array('required' => true),
        'pudoType'          => array('required' => true),
        'gpsCoordinates'    => array('required' => true),
        'maxPudoNumber'     => array('required' => true),
        'maxDistanceSearch' => array('required' => true),
    );

    public function validateParams($params)
    {
        parent::validateParams($params);

        if (!Validate::isString($params['partnerName'])) {
            $this->validation_errors[] = 'partnerName is not a alphanumeric string';
        }
        if ($params['pudoType'] != 'departure' && $params['pudoType'] != 'destination') {
            $this->validation_errors[] = 'pudoType is not departure or destination';
        }
        if (
            !isset($params['gpsCoordinates']['longitude'])
            || !isset($params['gpsCoordinates']['latitude'])
        ) {
            $this->validation_errors[] = 'gpsCoordinates should have an longitude and a latitude';
        } else {
            $regex_lat = "/^(\\-?\\d+(\\.\\d+)?)$/";
            if (!preg_match($regex_lat, $params['gpsCoordinates']['longitude'])) {
                $this->validation_errors[] = 'longitude is not a longitude';
            }
            if (!preg_match($regex_lat, $params['gpsCoordinates']['latitude'])) {
                $this->validation_errors[] = 'latitude is not a latitude';
            }
        }
        if (!is_numeric($params['maxPudoNumber'])) {
            $this->validation_errors[] = 'maxPudoNumber is not a numeric string';
        }
        if (!is_numeric($params['maxDistanceSearch'])) {
            $this->validation_errors[] = 'maxDistanceSearch is not a numeric string';
        }
    }
}
