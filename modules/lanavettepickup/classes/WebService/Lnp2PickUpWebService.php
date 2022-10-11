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

class Lnp2PickUpWebService
{
    private $pickupRESTLibrary = null;

    /**
     * [__construct description]
     * @param string $url Correspond à l'url du webservice. La gestion dev / prod est gérée dans le module
     */
    public function __construct($url)
    {
        $this->pickupRESTLibrary = new Lnp2PickUpRESTLibrary($url);
    }

    public function hasToken()
    {
        return $this->pickupRESTLibrary->getToken();
    }

    public function getPudoList($params)
    {
        $getPudoListRequestWS = new Lnp2GetPudoListRequestWS($params);
        if (count($getPudoListRequestWS->validation_errors)) {
            return $getPudoListRequestWS->validation_errors;
        }

        return $this->pickupRESTLibrary->execute('GetPudoList', $getPudoListRequestWS, 'POST');
    }

    public function getPudoListFromCoordinates($params)
    {
        $getPudoListFromCoordinatesRequestWS = new Lnp2GetPudoListFromCoordinatesRequestWS($params);
        if (count($getPudoListFromCoordinatesRequestWS->validation_errors)) {
            return $getPudoListFromCoordinatesRequestWS->validation_errors;
        }

        return $this->pickupRESTLibrary->execute('GetPudoListFromCoordinates', $getPudoListFromCoordinatesRequestWS, 'POST');
    }

    public function getPudoDetails($params)
    {
        $getPudoDetailsRequestWS = new Lnp2GetPudoDetailsRequestWS($params);
        if (count($getPudoDetailsRequestWS->validation_errors)) {
            return $getPudoDetailsRequestWS->validation_errors;
        }

        return $this->pickupRESTLibrary->execute('GetPudoDetails', $getPudoDetailsRequestWS, 'POST');
    }

    public function order($params)
    {
        $orderRequestWS = new Lnp2OrderRequestWS($params);
        if (count($orderRequestWS->validation_errors)) {
            return $orderRequestWS->validation_errors;
        }

        return $this->pickupRESTLibrary->execute('Order', $orderRequestWS, 'POST');
    }

    public function getParcelTracking($params)
    {
        $getParcelTrackingRequestWS = new Lnp2GetParcelTrackingRequestWS($params);
        if (count($getParcelTrackingRequestWS->validation_errors)) {
            return $getParcelTrackingRequestWS->validation_errors;
        }

        return $this->pickupRESTLibrary->execute('GetParcelTracking', $getParcelTrackingRequestWS, 'POST');
    }

    public function getLabel($params)
    {
        $getLabelRequestWS = new Lnp2GetLabelRequestWS($params);
        if (count($getLabelRequestWS->validation_errors)) {
            return $getLabelRequestWS->validation_errors;
        }

        return $this->pickupRESTLibrary->execute('GetLabel', $getLabelRequestWS, 'POST');
    }
}
