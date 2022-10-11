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

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/lanavettepickup.php');
require_once(dirname(__FILE__).'/classes/WebService/Lnp2PickUpWebService.php');
require_once(dirname(__FILE__).'/classes/WebService/Lnp2PickUpRESTLibrary.php');
require_once(dirname(__FILE__).'/classes/WebService/Lnp2GetPudoListRequestWS.php');
require_once(dirname(__FILE__).'/classes/WebService/Lnp2GetTokenRequestWS.php');
require_once(dirname(__FILE__).'/classes/WebService/Lnp2GetPudoListFromCoordinatesRequestWS.php');
require_once(dirname(__FILE__).'/classes/WebService/Lnp2GetPudoDetailsRequestWS.php');
require_once(dirname(__FILE__).'/classes/WebService/Lnp2OrderRequestWS.php');
require_once(dirname(__FILE__).'/classes/WebService/Lnp2GetParcelTrackingRequestWS.php');
require_once(dirname(__FILE__).'/classes/WebService/Lnp2GetLabelRequestWS.php');
require_once(dirname(__FILE__).'/classes/helper/Lnp2Logger.php');

$ws = new Lnp2PickUpWebService("https://c2cfrontservice-uat.pickup-services.com/C2CFrontApis/FrontApi.svc");

# which method to test :
$test_it = array(
    'getPudoList'                => false,
    'getPudoListFromCoordinates' => false,
    'getPudoDetails'             => false,
    'order'                      => false,
    'getParcelTracking'          => false,
    'getLabel'                   => true,
);

# getPudoList
if ($test_it['getPudoList']) {
//    $getPudoList = $ws->getPudoList(array(
//        'partnerName'       => '202ecommerce',
//        'address'           => array(
//            'addressLine' => '10 rue Vivienne',
//            'zipCode'     => '75002',
//            'city'        => 'Paris',
//            'countryCode' => 'FRA'
//        ),
//        'pudoType'          => 'destination',
//        'maxPudoNumber'     => 10,
//        'maxDistanceSearch' => 50000,
//    ));

    $getPudoList = $ws->getPudoList(array(
        'partnerName'       => '202ecommerce',
        'address'           => array(
            'addressLine' => '142 avenue des Corsaires',
            'zipCode'     => '17000',
            'city'        => 'La Rochelle',
            'countryCode' => 'FRA',
        ),
        'pudoType'          => 'destination',
        'maxPudoNumber'     => 10,
        'maxDistanceSearch' => 50000,
    ));

    echo "<pre>";
    var_dump($getPudoList);
    echo "</pre>";
}

# getPudoListFromCoordinates
if ($test_it['getPudoListFromCoordinates']) {
    $getPudoListFromCoordinates = $ws->getPudoListFromCoordinates(array(
        'partnerName'       => '202ecommerce',
        'pudoType'          => 'departure',
        'gpsCoordinates'    => array(
            'longitude' => 6.209612,
            'latitude'  => 45.577917,
        ),
        'maxPudoNumber'     => 10,
        'maxDistanceSearch' => 50000,
    ));
    $getPudoListFromCoordinates = $ws->getPudoListFromCoordinates(array(
        'partnerName'       => '202ecommerce',
        'pudoType'          => 'departure',
        'gpsCoordinates'    => array(
            'longitude' => -1.1715223,
            'latitude'  => 46.169453,
        ),
        'maxPudoNumber'     => 10,
        'maxDistanceSearch' => 50000,
    ));
    echo "<pre>";
    var_dump($getPudoListFromCoordinates);
    echo "</pre>";
}

# getPudoDetails
if ($test_it['getPudoDetails']) {
    $getPudoDetails = $ws->getPudoDetails(array(
        'partnerName' => '202ecommerce',
        'pudoType'    => 'departure',
        'pudoId'      => '4002R',
//        'pudoId'      => '2879U',
    ));

    echo "<pre>";
    print_r($getPudoDetails);
    echo "</pre>";
}

# order
if ($test_it['order']) {
    $pickup = new LaNavettePickup();
    # Get Pickup Order with an Order Id
//    $order = $pickup->getPickUpOrder(9);

    # Get Pickup Order with sample information
    $order = $ws->order(array(
        'partnerName'          => '202ecommerce',
        'orderExt'             => 'EXT155844',
        'height'               => 10,
        'width'                => 10,
        'length'               => 10,
        'weight'               => 1,
        'pudo'                 => '2879R',
        'pudoName'             => 'CHARPIN ELECTRICITE',
        'pudoAdress1'          => 'LE RECOSSET',
        'pudoAdress2'          => 'RD 925 CIDEX 411 PROCHE DE LA MAIRIE',
        'pudoZipCode'          => '73390',
        'pudoCity'             => 'BOURGNEUF',
        'pudoCountry'          => 'France',
        'pudoCountryCode'      => 'FRA',
        'recipientContactName' => 'Mouhamad mbaye',
        'recipientEmail'       => 'a.durnat@toto.fr',
        'recipientPhone'       => '00.04.05.06.12',
        'senderCivility'       => 'M',
        'senderMail'           => 'ebaudet@2020-ecommerce.com',
        'senderPhone'          => '06.47.12.78.02',
        'senderName'           => 'Sphip name',
        'senderName2'          => 'Ship name 2',
        'senderAdress1'        => 'Avenue de la Reine 226',
        'senderAdress2'        => 'appt 15',
        'senderZipCode'        => '1020',
        'senderCity'           => 'Bruxelle',
        'senderCountryCode'    => 'BEL',
        'senderCountryName'    => 'Belgique',
        'content'              => 'marchandise',
        'insuranceEnabled'     => true,
        'contentPrice'         => 10.25,
    ));
    # avec une adresse belge
    /*
     $order = $ws->order(array(
        'partnerName'          => '202ecommerce',
        'orderExt'             => 'EXT155844',
        'height'               => 10,
        'width'                => 10,
        'length'               => 10,
        'weight'               => 1,
        'pudo'                 => '1866A',
        'pudoName'             => 'FOTO - STUDIO P. NOLLET',
        'pudoAdress1'          => 'KAPITEINSTRAAT',
        'pudoAdress2'          => '',
        'pudoZipCode'          => '9000',
        'pudoCity'             => 'GENT',
        'pudoCountry'          => 'Belgique',
        'pudoCountryCode'      => 'BEL',
        'recipientContactName' => 'Mouhamad mbaye',
        'recipientEmail'       => 'a.durnat@toto.fr',
        'recipientPhone'       => '00.04.05.06.12',
        'senderCivility'       => 'M',
        'senderMail'           => 'ebaudet@2020-ecommerce.com',
        'senderPhone'          => '06.47.12.78.02',
        'senderName'           => 'Sphip name',
        'senderName2'          => 'Ship name 2',
        'senderAdress1'        => 'Avenue de la Reine 226',
        'senderAdress2'        => 'appt 15',
        'senderZipCode'        => '1020',
        'senderCity'           => 'Bruxelle',
        'senderCountryCode'    => 'BEL',
        'senderCountryName'    => 'Belgique',
        'content'              => 'marchandise',
    ));
    */

    # avec une adresse pays-bas
//    $order = $ws->order(array(
//        'partnerName'          => '202ecommerce',
//        'orderExt'             => 'EXT155844',
//        'height'               => 10,
//        'width'                => 10,
//        'length'               => 10,
//        'weight'               => 1,
//        'pudo'                 => '9390B',
//        'pudoName'             => 'REE-ZO ELECTRO ARNHEM',
//        'pudoAdress1'          => 'GEITENKAMP',
//        'pudoAdress2'          => '',
//        'pudoZipCode'          => '6823 HG',
//        'pudoCity'             => 'ARNHEM',
//        'pudoCountry'          => 'Pays-Bas',
//        'pudoCountryCode'      => 'NLD',
//        'recipientContactName' => 'Mouhamad mbaye',
//        'recipientEmail'       => 'a.durnat@toto.fr',
//        'recipientPhone'       => '00.04.05.06.12',
//        'senderCivility'       => 'M',
//        'senderMail'           => 'ebaudet@2020-ecommerce.com',
//        'senderPhone'          => '06.47.12.78.02',
//        'senderName'           => 'Sphip name',
//        'senderName2'          => 'Ship name 2',
//        'senderAdress1'        => 'Avenue de la Reine 226',
//        'senderAdress2'        => 'appt 15',
//        'senderZipCode'        => '1020',
//        'senderCity'           => 'Bruxelle',
//        'senderCountryCode'    => 'BEL',
//        'senderCountryName'    => 'Belgique',
//        'content'              => 'marchandise',
//    ));

    # avec une adresse allemande
    /*
    $order = $ws->order(array(
         'partnerName'          => '202ecommerce',
         'orderExt'             => 'EXT155844',
         'height'               => 10,
         'width'                => 10,
         'length'               => 10,
         'weight'               => 1,
         'pudo'                 => '0136D',
         'pudoName'             => 'Kiosk Kaspian',
         'pudoAdress1'          => 'Oederweg',
         'pudoAdress2'          => '',
         'pudoZipCode'          => '60318',
         'pudoCity'             => 'FRANKFURT AM MAIN',
         'pudoCountry'          => 'Allemagne',
         'pudoCountryCode'      => 'DEU',
         'recipientContactName' => 'Mouhamad mbaye',
         'recipientEmail'       => 'a.durnat@toto.fr',
         'recipientPhone'       => '00.04.05.06.12',
         'senderCivility'       => 'M',
         'senderMail'           => 'ebaudet@2020-ecommerce.com',
         'senderPhone'          => '06.47.12.78.02',
         'senderName'           => 'Sphip name',
         'senderName2'          => 'Ship name 2',
         'senderAdress1'        => 'Avenue de la Reine 226',
         'senderAdress2'        => 'appt 15',
         'senderZipCode'        => '1020',
         'senderCity'           => 'Bruxelle',
         'senderCountryCode'    => 'BEL',
         'senderCountryName'    => 'Belgique',
         'content'              => 'marchandise',
     ));
    */
    if (!$order) {
        echo "No order<br/>";
    } else {
        # download the PDF.
//        $pickup = new LaNavettePickup();
//        $pickup->generatePDF($order->pdfDataBase64, 'orderTest');

        # save the PDF in logs folder.
        $std_file = fopen(_PS_MODULE_DIR_.'/lanavettepickup/logs/order.pdf', 'w');
        fwrite($std_file, base64_decode($order->pdfDataBase64));
        fclose($std_file);
        echo 'The Pickup Order has been created <a href="/modules/lanavettepickup/logs/order.pdf" target="_blank">here</a>';
    }
    echo "<pre>";
    var_dump($order);
    echo "</pre>";
}

# getParcelTracking
if ($test_it['getParcelTracking']) {
    $getParcelTracking = $ws->getParcelTracking(array(
        'partnerName' => '202ecommerce',
        'reference'   => 'XY525383256FR',
    ));
    echo "<pre>";
    var_dump($getParcelTracking);
    echo "</pre>";
}

# getLabel
if ($test_it['getLabel']) {
    $getLabel = $ws->getLabel(array(
        'partnerName' => '202ecommerce',
        'orderNumber' => '201701XY538746847FR-202',
    ));
    echo "<pre>";
    var_dump($getLabel);
    echo "</pre>";
}
