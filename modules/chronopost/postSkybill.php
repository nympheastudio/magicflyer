<?php
/**
* MODULE PRESTASHOP OFFICIEL CHRONOPOST
*
* LICENSE : All rights reserved - COPY AND REDISTRIBUTION FORBIDDEN WITHOUT PRIOR CONSENT FROM OXILEO
* LICENCE : Tous droits réservés, le droit d'auteur s'applique - COPIE ET REDISTRIBUTION INTERDITES
* SANS ACCORD EXPRES D'OXILEO
*
* @author    Oxileo SAS <contact@oxileo.eu>
* @copyright 2001-2018 Oxileo SAS
* @license   Proprietary - no redistribution without authorization
*/

require('../../config/config.inc.php');

if (!Tools::getIsset('orderid') && !Tools::getIsset('orders')) {
    die('<h1>Informations de commande non transmises</h1>');
}

require_once('chronopost.php');
include('libraries/ShippingServiceWSService.php');
include_once('libraries/PointRelaisServiceWSService.php');

if (Shop::isFeatureActive()) {
    Shop::setContext(Shop::CONTEXT_ALL);
}
        

$multi = array();

if (!Tools::getIsset('shared_secret') || Tools::getValue('shared_secret') != Configuration::get('CHRONOPOST_SECRET')) {
    die('Secret does not match.');
}


if (Tools::strlen(Configuration::get('CHRONOPOST_GENERAL_ACCOUNT')) < 8) {
    die('Erreur : veuillez configurer le module avant de procéder à l\'édition des étiquettes.');
}

$return = false;

if (Tools::getIsset('multi')) {
    $multi = Tools::getValue('multi');
    //$multi = Tools::jsonDecode($multi, true);
    $multi = json_decode($multi, true);
} else {
    $multi = array();
}

if (Tools::getIsset('orders')) {
    $orders = Tools::getValue('orders');
    $orders = explode(';', $orders);
} else {
    $orders = array(Tools::getValue('orderid'));
    if (Tools::getIsset('return')) {
        $return = true;
    }
    if (Tools::getIsset('multiOne')) {
        $multi = array($orders[0]=>Tools::getValue('multiOne'));
    }
}


if (count($orders) == 0) {
    die('<h1>Aucune commande sélectionnée</h1>');
}
require_once('libraries/PDFMerger.php');
@$pdf = new PDFMerger;

foreach ($orders as $orderid) {
    if (is_array($multi) && array_key_exists($orderid, $multi)) {
        $nb = $multi[$orderid];
    } else {
        $nb = 1;
    }

    $totalnb = $nb;

    while ($nb > 0) {
        $lt = createLT($orderid, $totalnb, $return);
        $file = 'skybills/'.$lt->skybillNumber.'.pdf';
        $fp = fopen($file, 'w');

        if ($lt->pdfEtiquette === null) {
            /* error, skip it */
            $nb--;
            continue;
        }

        fwrite($fp, $lt->pdfEtiquette);
        fclose($fp);

        @$pdf->addPDF($file, 'all');
        $nb--;
    }
}

try {
    if (isset($_SERVER['HTTP_REFERER']) && preg_match('#AdminOrders#', $_SERVER['HTTP_REFERER'])) {
        header('Refresh: 0; url=' . $_SERVER['HTTP_REFERER']);
    }

    $pdf->merge('download', 'Chronopost-LT-'.date('Ymd-Hi').'.pdf');
} catch (Exception $e) {
    echo '<p>Le fichier généré est invalide.</p>';
    echo '<p>Vérifiez la configuration du module et que les commandes visées disposent d\'adresses de livraison 
valides.</p>';
}


function createLT($orderid, $totalnb = 1, $isReturn = false)
{
    $o = new Order($orderid);
    $a = new Address($o->id_address_delivery);
    $cust = new Customer($o->id_customer);

    // at least 2 skybills for orders >= 30kg
    $o = new Order($orderid);
    if ($o->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF') >= 30 && $totalnb == 1) {
        echo '<script>alert(\'Vous devez générer au moins 2 étiquettes pour les commandes de plus de 30kg\');
        history.back();</script>';
        exit();
    }

    $recipient = new recipientValue();
    $recipient->recipientAdress1 = Tools::substr($a->address1, 0, 35);
    $recipient->recipientAdress2 = Tools::substr($a->address2, 0, 35);
    $recipient->recipientCity = Tools::substr($a->city, 0, 30);
    $recipient->recipientCivility = 'M';
    $recipient->recipientContactName = Tools::substr($a->firstname.' '.$a->lastname, 0, 35);
    $c = new Country($a->id_country);
    $recipient->recipientCountry = $c->iso_code;
    $recipient->recipientName = Tools::substr($a->company, 0, 35);
    $recipient->recipientName2 = Tools::substr($a->firstname.' '.$a->lastname, 0, 35);
    $recipient->recipientZipCode = $a->postcode;
    $recipient->recipientPhone = $a->phone_mobile == null ? $a->phone : $a->phone_mobile;
    $recipient->recipientMobilePhone = $a->phone_mobile;
    $recipient->recipientEmail = $cust->email;

    if ($isReturn) {
        if (Tools::getValue('return_address') == chronopost::$RETURN_ADDRESS_RETURN) {
            $addressKey = 'RETURN';
        } elseif (Tools::getValue('return_address') == chronopost::$RETURN_ADDRESS_INVOICE) {
            $addressKey = 'CUSTOMER';
        } elseif (Tools::getValue('return_address') == chronopost::$RETURN_ADDRESS_SHIPPING) {
            $addressKey = 'SHIPPER';
        }
        $recipient->recipientAdress1 = Configuration::get('CHRONOPOST_'. $addressKey .'_ADDRESS');
        $recipient->recipientAdress2 = Configuration::get('CHRONOPOST_'. $addressKey .'_ADDRESS2');
        $recipient->recipientCity = Configuration::get('CHRONOPOST_'. $addressKey .'_CITY');
        $recipient->recipientCivility = Configuration::get('CHRONOPOST_'. $addressKey .'_CIVILITY');
        $recipient->recipientContactName = Configuration::get('CHRONOPOST_'. $addressKey .'_CONTACTNAME');
        $recipient->recipientCountry = Configuration::get('CHRONOPOST_'. $addressKey .'_COUNTRY');
        $recipient->recipientName = Configuration::get('CHRONOPOST_'. $addressKey .'_NAME');
        $recipient->recipientName2 = Configuration::get('CHRONOPOST_'. $addressKey .'_NAME2');
        $recipient->recipientZipCode = Configuration::get('CHRONOPOST_'. $addressKey .'_ZIPCODE');
    }


    $esd = new esdValue();
    $esd->specificInstructions = 'aucune';
    
    $esd->height = '';
    $esd->width = '';
    $esd->length = '';

    $header = new headerValue();
    $params = new shippingV7();
    $skybill = new skybillValue();
    $skybill->evtCode = 'DC';
    $skybill->objectType = 'MAR';

    // Ships with Chrono 13 by default
    $skybill->productCode = Chronopost::$carriers_definitions['CHRONO13']['product_code'];
    // Service code 0 by default
    $skybill->service = '0';


    if (Tools::getIsset('advalorem') && Tools::getValue('advalorem') == 'yes') {
        $skybill->insuredValue = (int)Tools::getValue('advalorem_value')*100;
    }

    $header->accountNumber = Configuration::get('CHRONOPOST_GENERAL_ACCOUNT');
    $header->subAccount = Configuration::get('CHRONOPOST_GENERAL_SUBACCOUNT');
    $params->password = Configuration::get('CHRONOPOST_GENERAL_PASSWORD');

    $header->idEmit = 'PREST';

    $shipper = new shipperValue();
    $shipper->shipperAdress1 = Configuration::get('CHRONOPOST_SHIPPER_ADDRESS');
    $shipper->shipperAdress2 = Configuration::get('CHRONOPOST_SHIPPER_ADDRESS2');
    $shipper->shipperCity = Configuration::get('CHRONOPOST_SHIPPER_CITY');
    $shipper->shipperCivility = Configuration::get('CHRONOPOST_SHIPPER_CIVILITY');
    $shipper->shipperContactName = Configuration::get('CHRONOPOST_SHIPPER_CONTACTNAME');
    $shipper->shipperCountry = Configuration::get('CHRONOPOST_SHIPPER_COUNTRY');
    $shipper->shipperName = Configuration::get('CHRONOPOST_SHIPPER_NAME');
    $shipper->shipperName2 = Configuration::get('CHRONOPOST_SHIPPER_NAME2');
    $shipper->shipperZipCode = Configuration::get('CHRONOPOST_SHIPPER_ZIPCODE');

    if ($isReturn) {
        $shipper = new shipperValue();
        $shipper->shipperAdress1 = Tools::substr($a->address1, 0, 35);
        $shipper->shipperAdress2 = Tools::substr($a->address2, 0, 35);
        $shipper->shipperCity = Tools::substr($a->city, 0, 30);
        $shipper->shipperCivility = 'M';
        $shipper->shipperContactName = Tools::substr($a->firstname.' '.$a->lastname, 0, 35);
        $shipper->shipperCountry = Configuration::get('CHRONOPOST_SHIPPER_COUNTRY');
        $shipper->shipperPhone = Configuration::get('CHRONOPOST_SHIPPER_PHONE');
        $shipper->shipperName = Tools::substr($a->company, 0, 35);
        $shipper->shipperName2 = Tools::substr($a->firstname.' '.$a->lastname, 0, 35);
        $shipper->shipperZipCode = $a->postcode;
    }

    $customer = new customerValue();
    $customer->customerAdress1 = Configuration::get('CHRONOPOST_CUSTOMER_ADDRESS');
    $customer->customerAdress2 = Configuration::get('CHRONOPOST_CUSTOMER_ADDRESS2');
    $customer->customerCity = Configuration::get('CHRONOPOST_CUSTOMER_CITY');
    $customer->customerCivility = Configuration::get('CHRONOPOST_CUSTOMER_CIVILITY');
    $customer->customerContactName = Configuration::get('CHRONOPOST_CUSTOMER_CONTACTNAME');
    $customer->customerCountry = Configuration::get('CHRONOPOST_CUSTOMER_COUNTRY');
    $customer->customerName = Configuration::get('CHRONOPOST_CUSTOMER_NAME');
    $customer->customerName2 = Configuration::get('CHRONOPOST_CUSTOMER_NAME2');
    $customer->customerZipCode = Configuration::get('CHRONOPOST_CUSTOMER_ZIPCODE');

    $ref = new refValue();
    $ref->recipientRef = $a->postcode;

    // Skybill details per carrier
    $skybill_details = Chronopost::getSkybillDetails($o, $isReturn);
    $skybill->productCode = $skybill_details['productCode'];
    $skybill->service = $skybill_details['service'];
    if (isset($skybill_details['as'])) {
        $skybill->as = $skybill_details['as'];
    }

    if (array_key_exists('recipientRef', $skybill_details)) {
        $ref->recipientRef = $skybill_details['recipientRef'];
    }

    if (array_key_exists('timeSlot', $skybill_details)) {
        $params->scheduledValue = new scheduledValue();
        $params->scheduledValue->appointmentValue = new appointmentValue();
        $params->scheduledValue->appointmentValue->timeSlotStartDate = $skybill_details['timeSlotStartDate'];
        $params->scheduledValue->appointmentValue->timeSlotEndDate = $skybill_details['timeSlotEndDate'];
        $params->scheduledValue->appointmentValue->timeSlotTariffLevel = $skybill_details['timeSlotTariffLevel'];
    }
    // end carrier-specific part

    $ref->shipperRef = sprintf('%06d', $orderid);

    $skybill->shipDate = date('Y-m-d\TH:i:s');
    $skybill->shipHour = date('H');

    // weight 0 when multishipping
    $skybill->weight = 0;
    // Only 1 skybill, put real weight.
    if ($totalnb == 1) {
        $skybill->weight = $o->getTotalWeight() * Configuration::get('CHRONOPOST_GENERAL_WEIGHTCOEF');
    }

    $skybill->weightUnit = 'KGM';
    $skybill->height = 22.9;
    $skybill->length = 16.2;
    $skybill->width = 0;

    $skybillParams = new skybillParamsValue();
    $skybillParams->mode = Configuration::get('CHRONOPOST_GENERAL_PRINTMODE');

    $skybillParams->withReservation = 0;

    $params->esdValue = $esd;
    $params->headerValue = $header;
    $params->shipperValue = $shipper;
    $params->customerValue = $customer;
    $params->recipientValue = $recipient;
    $params->refValue = $ref;
    $params->skybillValue = $skybill;

    $params->skybillParamsValue = $skybillParams;

    $service = new ShippingServiceWSService();
    $r = $service->shippingV7($params)->return;

    if ($r->errorCode != 0) {
        return null;
    }

    if (Tools::getIsset('advalorem') && Tools::getValue('advalorem') == 'yes') {
        $skybill->insuredValue = (int)Tools::getValue('advalorem_value');
    }

    // MAIL::SEND is bugged in 1.5 !
    // http://forge.prestashop.com/browse/PNM-754 (Unresolved as of 2013-04-15)
    // Context fix (it's that easy)
    Context::getContext()->link = new Link();

    if ($isReturn) {
        $customer = new Customer($o->id_customer);
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            Mail::Send(
                $o->id_lang,
                'return',
                'Lettre de transport Chronopost pour le retour de votre commande',
                array(
                    '{id_order}' => $o->id,
                    '{firstname}' => $customer->firstname,
                    '{lastname}' => $customer->lastname
                ),
                $customer->email,
                $customer->firstname.' '.$customer->lastname,
                null,
                null,
                array(
                    'content' => $r->skybill,
                    'mime' => 'application/pdf',
                    'name' => $r->skybillNumber.'.pdf'
                ),
                null,
                'mails/',
                true
            );
        } else {
            Mail::Send(
                $o->id_lang,
                'return',
                'Lettre de transport Chronopost pour le retour de votre commande',
                array(
                    '{id_order}' => $o->id,
                    '{firstname}' => $customer->firstname,
                    '{lastname}' => $customer->lastname
                ),
                $customer->email,
                $customer->firstname.' '.$customer->lastname,
                null,
                null,
                array(
                    'content' => $r->skybill,
                    'mime' => 'application/pdf',
                    'name' => $r->skybillNumber.'.pdf'
                ),
                null,
                '/modules/chronopost/mails/',
                true
            );
        }
    } else {
        // Store LT for history
        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'chrono_lt_history` VALUES (
				'.(int)$o->id.', 
				"'.pSQL($r->skybillNumber).'", 
				"'.pSQL($skybill->productCode).'",
				"'.pSQL($recipient->recipientZipCode).'",
				"'.pSQL($recipient->recipientCountry).'",
				"'.(isset($skybill->insuredValue) ? (int)$skybill->insuredValue : 0).'",
				"'.pSQL($recipient->recipientCity).'",
				NULL
			)');

        Chronopost::trackingStatus($o->id, $r->skybillNumber);
    }

    return $r;
}
