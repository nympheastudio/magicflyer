<?php
class searchDeliverySlot
{
    public $callerTool; // string
  public $productType; // string
  public $shipperAdress1; // string
  public $shipperAdress2; // string
  public $shipperZipCode; // string
  public $shipperCity; // string
  public $shipperCountry; // string
  public $recipientAdress1; // string
  public $recipientAdress2; // string
  public $recipientZipCode; // string
  public $recipientCity; // string
  public $recipientCountry; // string
  public $injectionSite; // string
  public $weight; // int
  public $dateBegin; // dateTime
  public $dateEnd; // dateTime
  public $shipperDeliverySlotClosed; // string
  public $currency; // string
  public $rateN1; // string
  public $rateN2; // string
  public $rateN3; // string
  public $rateN4; // string
  public $rateLevelsNotShow; // string
  public $isDeliveryDate; // boolean
  public $slotType; // string
}

class searchDeliverySlotResponse
{
    public $return; // deliverySlotResponse
}

class deliverySlotResponse
{
    public $meshCode; // string
  public $slotList; // slot
  public $transactionID; // string
}

class wsResponse
{
    public $code; // int
  public $message; // string
}

class slot
{
    public $deliverySlotCode; // string
  public $deliveryDate; // string
  public $dayOfWeek; // int
  public $startHour; // int
  public $startMinutes; // int
  public $endHour; // int
  public $endMinutes; // int
  public $tariffLevel; // string
  public $status; // string
  public $codeStatus; // string
  public $note; // int
  public $incentiveFlag; // boolean
  public $rawRank; // int
  public $rank; // int
}

class getAdresseGeocodage
{
    public $adresse1; // string
  public $adresse2; // string
  public $zipCode; // string
  public $city; // string
}

class getAdresseGeocodageResponse
{
    public $return; // geocodageResponse
}

class geocodageResponse
{
    public $lat; // double
  public $lon; // double
  public $niveauQualite; // int
}

class confirmDeliverySlotV2
{
    public $callerTool; // string
  public $productType; // string
  public $codeSlot; // string
  public $meshCode; // string
  public $transactionID; // string
  public $rank; // string
  public $position; // string
  public $dateSelected; // dateTime
}

class confirmDeliverySlotResponse
{
    public $return; // serviceResponse
}

class serviceResponse
{
    public $productService; // productService
}

class productServiceV2
{
    public $productCode; // string
    public $serviceCode; // string
    public $asCode; // string
}


/**
 * CreneauWS class
 *
 *
 *
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class CreneauWS extends SoapClient
{
    private static $classmap = array(
        'searchDeliverySlot'          => 'searchDeliverySlot',
        'searchDeliverySlotResponse'  => 'searchDeliverySlotResponse',
        'deliverySlotResponse'        => 'deliverySlotResponse',
        'wsResponse'                  => 'wsResponse',
        'slot'                        => 'slot',
        'getAdresseGeocodage'         => 'getAdresseGeocodage',
        'getAdresseGeocodageResponse' => 'getAdresseGeocodageResponse',
        'geocodageResponse'           => 'geocodageResponse',
        'confirmDeliverySlotV2'       => 'confirmDeliverySlotV2',
        'confirmDeliverySlotResponse' => 'confirmDeliverySlotResponse',
        'serviceResponse'             => 'serviceResponse',
        'productServiceV2'            => 'productServiceV2',
                                   );

    public function CreneauWS($wsdl = "https://www.chronopost.fr/rdv-cxf/services/CreneauServiceWS?wsdl", $options = array())
    {
        foreach (self::$classmap as $key => $value) {
            if (!isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }
        parent::__construct($wsdl, $options);
    }

  /**
   *
   *
   * @param searchDeliverySlot $parameters
   * @return searchDeliverySlotResponse
   */
  public function searchDeliverySlot(searchDeliverySlot $parameters)
  {
      return $this->__soapCall('searchDeliverySlot', array($parameters), array(
            'uri' => 'http://cxf.soap.ws.creneau.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param getAdresseGeocodage $parameters
   * @return getAdresseGeocodageResponse
   */
  public function getAdresseGeocodage(getAdresseGeocodage $parameters)
  {
      return $this->__soapCall('getAdresseGeocodage', array($parameters), array(
            'uri' => 'http://cxf.soap.ws.creneau.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }

  /**
   *
   *
   * @param confirmDeliverySlotV2 $parameters
   *
   * @return confirmDeliverySlotResponse
   */
  public function confirmDeliverySlot(confirmDeliverySlotV2 $parameters)
  {
      return $this->__soapCall('confirmDeliverySlotV2', array($parameters), array(
            'uri' => 'http://cxf.soap.ws.creneau.chronopost.fr/',
            'soapaction' => ''
           )
      );
  }
}
