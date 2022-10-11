<?php
/**
 * ShippingServiceWSService class file
 *
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */

/**
 * shipperValue class
 */
require_once 'shipperValue.php';
/**
 * resultFaisabiliteESD class
 */
require_once 'resultFaisabiliteESD.php';
/**
 * esdValue class
 */
require_once 'esdValue.php';
/**
 * headerValue class
 */
require_once 'headerValue.php';
/**
 * customerValue class
 */
require_once 'customerValue.php';
/**
 * recipientValue class
 */
require_once 'recipientValue.php';
/**
 * refValue class
 */
require_once 'refValue.php';
/**
 * skybillValue class
 */
require_once 'skybillValue.php';
/**
 * routingInformation class
 */
require_once 'routingInformation.php';
/**
 * skybillParamsValue class
 */
require_once 'skybillParamsValue.php';
/**
 * resultExpeditionValue class
 */
require_once 'resultExpeditionValue.php';
/**
 * resultGetReservedSkybillWithTypeValue class
 */
require_once 'resultGetReservedSkybillWithTypeValue.php';
/**
 * scheduledValue class
 */
require_once 'scheduledValue.php';
/**
 * appointmentValue class
 */
require_once 'appointmentValue.php';
/**
 * resultGetReservedSkybillValue class
 */
require_once 'resultGetReservedSkybillValue.php';
/**
 * esdWithRefClientValue class
 */
require_once 'esdWithRefClientValue.php';
/**
 * skybillWithDimensionsValue class
 */
require_once 'skybillWithDimensionsValue.php';
/**
 * resultReservationMultiParcelExpeditionValue class
 */
require_once 'resultReservationMultiParcelExpeditionValue.php';
/**
 * resultParcelValue class
 */
require_once 'resultParcelValue.php';
/**
 * resultReservationExpeditionValue class
 */
require_once 'resultReservationExpeditionValue.php';
/**
 * esdResultContraintesAgenceValue class
 */
require_once 'esdResultContraintesAgenceValue.php';
/**
 * esdContraintesAgence class
 */
require_once 'esdContraintesAgence.php';
/**
 * faisabiliteESD class
 */
require_once 'faisabiliteESD.php';
/**
 * faisabiliteESDResponse class
 */
require_once 'faisabiliteESDResponse.php';
/**
 * shippingV2 class
 */
require_once 'shippingV2.php';
/**
 * shippingV2Response class
 */
require_once 'shippingV2Response.php';
/**
 * getReservedSkybillWithType class
 */
require_once 'getReservedSkybillWithType.php';
/**
 * getReservedSkybillWithTypeResponse class
 */
require_once 'getReservedSkybillWithTypeResponse.php';
/**
 * shippingV7 class
 */
require_once 'shippingV7.php';
/**
 * shippingV7Response class
 */
require_once 'shippingV7Response.php';
/**
 * shipping class
 */
require_once 'shipping.php';
/**
 * shippingResponse class
 */
require_once 'shippingResponse.php';
/**
 * getReservedSkybill class
 */
require_once 'getReservedSkybill.php';
/**
 * getReservedSkybillResponse class
 */
require_once 'getReservedSkybillResponse.php';
/**
 * shippingMultiParcelWithReservation class
 */
require_once 'shippingMultiParcelWithReservation.php';
/**
 * shippingMultiParcelWithReservationResponse class
 */
require_once 'shippingMultiParcelWithReservationResponse.php';
/**
 * shippingWithReservation class
 */
require_once 'shippingWithReservation.php';
/**
 * shippingWithReservationResponse class
 */
require_once 'shippingWithReservationResponse.php';
/**
 * rechercherContraintesEnlevement class
 */
require_once 'rechercherContraintesEnlevement.php';
/**
 * rechercherContraintesEnlevementResponse class
 */
require_once 'rechercherContraintesEnlevementResponse.php';
/**
 * getReservedSkybillWithTypeAndMode class
 */
require_once 'getReservedSkybillWithTypeAndMode.php';
/**
 * getReservedSkybillWithTypeAndModeResponse class
 */
require_once 'getReservedSkybillWithTypeAndModeResponse.php';
/**
 * shippingWithReservationAndESDWithRefClient class
 */
require_once 'shippingWithReservationAndESDWithRefClient.php';
/**
 * shippingWithReservationAndESDWithRefClientResponse class
 */
require_once 'shippingWithReservationAndESDWithRefClientResponse.php';
/**
 * shippingWithReservationAndESDWithRefClientPC class
 */
require_once 'shippingWithReservationAndESDWithRefClientPC.php';
/**
 * shippingWithReservationAndESDWithRefClientPCResponse class
 */
require_once 'shippingWithReservationAndESDWithRefClientPCResponse.php';
/**
 * shippingWithESDOnly class
 */
require_once 'shippingWithESDOnly.php';
/**
 * shippingWithESDOnlyResponse class
 */
require_once 'shippingWithESDOnlyResponse.php';

/**
 * ShippingServiceWSService class
 *
 *
 *
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class ShippingServiceWSService extends SoapClient
{
    public function __construct($wsdl = "https://ws.chronopost.fr/shipping-cxf/ShippingServiceWS?wsdl", $options = array())
    {
        parent::__construct($wsdl, $options);
    }

  /**
   *
   *
   * @param faisabiliteESD $parameters
   * @return faisabiliteESDResponse
   */
  public function faisabiliteESD(faisabiliteESD $parameters)
  {
      return $this->__call('faisabiliteESD', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param getReservedSkybillWithType $parameters
   * @return getReservedSkybillWithTypeResponse
   */
  public function getReservedSkybillWithType(getReservedSkybillWithType $parameters)
  {
      return $this->__call('getReservedSkybillWithType', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param shippingV2 $parameters
   * @return shippingV2Response
   */
  public function shippingV2(shippingV2 $parameters)
  {
      return $this->__call('shippingV2', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param shippingV7 $parameters
   *
   * @return shippingV7Response
   */
  public function shippingV7(shippingV7 $parameters)
  {
      return $this->__call('shippingV7', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param shipping $parameters
   * @return shippingResponse
   */
  public function shipping(shipping $parameters)
  {
      return $this->__call('shipping', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param getReservedSkybill $parameters
   * @return getReservedSkybillResponse
   */
  public function getReservedSkybill(getReservedSkybill $parameters)
  {
      return $this->__call('getReservedSkybill', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param shippingMultiParcelWithReservation $parameters
   * @return shippingMultiParcelWithReservationResponse
   */
  public function shippingMultiParcelWithReservation(shippingMultiParcelWithReservation $parameters)
  {
      return $this->__call('shippingMultiParcelWithReservation', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param shippingWithReservation $parameters
   * @return shippingWithReservationResponse
   */
  public function shippingWithReservation(shippingWithReservation $parameters)
  {
      return $this->__call('shippingWithReservation', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param rechercherContraintesEnlevement $parameters
   * @return rechercherContraintesEnlevementResponse
   */
  public function rechercherContraintesEnlevement(rechercherContraintesEnlevement $parameters)
  {
      return $this->__call('rechercherContraintesEnlevement', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param getReservedSkybillWithTypeAndMode $parameters
   * @return getReservedSkybillWithTypeAndModeResponse
   */
  public function getReservedSkybillWithTypeAndMode(getReservedSkybillWithTypeAndMode $parameters)
  {
      return $this->__call('getReservedSkybillWithTypeAndMode', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param shippingWithReservationAndESDWithRefClient $parameters
   * @return shippingWithReservationAndESDWithRefClientResponse
   */
  public function shippingWithReservationAndESDWithRefClient(shippingWithReservationAndESDWithRefClient $parameters)
  {
      return $this->__call('shippingWithReservationAndESDWithRefClient', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param shippingWithReservationAndESDWithRefClientPC $parameters
   * @return shippingWithReservationAndESDWithRefClientPCResponse
   */
  public function shippingWithReservationAndESDWithRefClientPC(shippingWithReservationAndESDWithRefClientPC $parameters)
  {
      return $this->__call('shippingWithReservationAndESDWithRefClientPC', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param shippingWithESDOnly $parameters
   * @return shippingWithESDOnlyResponse
   */
  public function shippingWithESDOnly(shippingWithESDOnly $parameters)
  {
      return $this->__call('shippingWithESDOnly', array(
            new SoapParam($parameters, 'parameters')
      ),
      array(
            'uri' => 'http://cxf.shipping.soap.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }
}
