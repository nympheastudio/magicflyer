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

include('../../config/config.inc.php');
require_once('chronopost.php');

if (!defined('_MYDIR_')) {
    define('_MYDIR_', dirname(__FILE__));
}

if (!Tools::getIsset('shared_secret') || Tools::getValue('shared_secret') != Configuration::get('CHRONOPOST_SECRET')) {
    die('Secret does not match.');
}

$cible = Tools::getValue('cible');

if ($cible) {
    /* export */
    header('Content-Disposition: attachment; filename="export'.$cible.date('Ymd').'.csv"');
    if ($cible == 'CSS') {
        header('Content-Type: text/plain; charset=ISO-8859-1');
        include_once _MYDIR_.'/libraries/PointRelaisServiceWSService.php';
        foreach (_getChronoOrders() as $o) {
            /* Ingredients */
            $address = new Address($o->id_address_delivery);
            $country = new Country($address->id_country);
            $customer = new Customer($o->id_customer); /* for email address */
            $carrier = new Carrier($o->id_carrier);


            $details = Chronopost::getSkybillDetails($o);
            
            $bt = '';
            if (Chronopost::isRelais($o->id_carrier)) {
                $bt = $address->other;
            } /* strlen(Depot [...])=20 */

            /* Stir everything together */
            echo $o->id.';';

            if ($address->company != '') {
                echo _c($address->company).';';
            } else {
                echo _c($address->firstname.' '.$address->lastname).';';
            }

            echo _c($address->firstname.' '.$address->lastname).';';
            echo ';'; // name 2

            echo _c($address->address1).';'._c($address->address2).';';
            echo ';'; // digicode

            echo _c($address->postcode).';';
            echo _c($address->city).';';
            echo $country->iso_code.';';
            if ($address->phone != '') {
                echo $address->phone.';';
            } else {
                echo $address->phone_mobile.';';
            }
            echo $customer->email.';';
            
            echo $o->id.';';
            echo ';'; /* ref expé 2 */
            
            echo $details['productCode'];
            echo ';';

            echo Configuration::get('CHRONOPOST_GENERAL_ACCOUNT').';';
            echo Configuration::get('CHRONOPOST_GENERAL_SUBACCOUNT').';';
            echo Chronopost::amountToInsure($o->id) > 0 ? Chronopost::amountToInsure($o->id) . ';' : ';';
            echo ';'; // Customs value
            echo 'M;';
            echo ';'; // Contents description
            if (Chronopost::isSaturdayOptionApplicable()) {
                echo 'Y;';
            } else {
                echo 'N;';
            }
            echo $bt.';';
            echo($o->getTotalWeight() == 0 ? 1 : ($o->getTotalWeight())).';';
            echo ';'; // width
            echo ';'; // length
            echo ';'; // height
            echo '1;'; // warn recipient
            echo Chronopost::minNumberOfPackages($o->id).';';
            echo date("d/m/Y").';';
            echo 'Y;';
            echo 'N;';
            echo ';'; // best before

            // RDV below
            if (array_key_exists('timeSlotStartDate', $details)) {
                $dateStart = new DateTime($details['timeSlotStartDate']);
                $dateEnd = new DateTime($details['timeSlotEndDate']);
                echo $dateStart->format('dmyHi') . ';';
                echo $dateEnd->format('dmyHi') . ';';
                echo $details['as'].';';
            } else {
                echo ';;;';
            }

            echo $details['service'];
            echo ';'."\r\n";
        }
    }

    if ($cible == 'CSO') {
        header('Content-Type: text/plain; charset=US-ASCII');

        foreach (_getChronoOrders(false) as $o) {
            /* Ingredients */
            $address = new Address($o->id_address_delivery);
            $country = new Country($address->id_country);
            $customer = new Customer($o->id_customer); /* for email address */
            $carrier = new Carrier($o->id_carrier);

            /* Stir everything together */
            echo ';'; /* "code destinataire" left empty */

            if ($address->company != '') {
                echo _c($address->company).';';
            } else {
                echo ';';
            }
            echo ';'; /* "suite raison sociale" (?) */
            echo _c($address->address1).';'._c($address->address2).';';
            echo ';'; /* "code porte" */

            echo $country->iso_code.';';
            echo _c($address->postcode).';';
            echo _c($address->city).';';
            echo _c($address->lastname).';';
            echo _c($address->firstname).';';
            if ($address->phone != '') {
                echo $address->phone.';';
            } else {
                echo $address->phone_mobile.';';
            }
            echo $customer->email.';';
            echo ';'; /* "numero tva" */

            if ($carrier->id_reference == Configuration::get('CHRONOPOST_CHRONO13_ID')) {
                echo '1';
            }
            if ($carrier->id_reference == Configuration::get('CHRONOPOST_CHRONOEXPRESS_ID')) {
                echo '4';
            }
            echo ';';
            
            echo $o->id.';';
            echo($o->getTotalWeight() == 0 ? 1 : ($o->getTotalWeight() * 1000)).';';

            echo ';;;';
            echo Configuration::get('CHRONOPOST_GENERAL_SUBACCOUNT').';';
            echo '1;Commande '.$o->id.';';
            echo ';';
            echo $o->total_paid.';';

            if (Chronopost::isSaturdayOptionApplicable()) {
                echo '1;';
            } else {
                echo '2;';
            }
            echo "\r\n";
        }
    }
}

function _c($value)
{
    return utf8_decode(str_replace('"', ' ', str_replace(';', ' ', strip_tags($value))));
}

function _getChronoOrders($withRelais = true)
{
    if (Tools::getIsset('multi')) {
        $multi = json_decode(Tools::getValue('multi'), true);
    } else {
        $multi = array();
    }

    $r = array();
    if (Tools::getIsset('orders')) {
        $o = explode(';', Tools::getValue('orders'));
    } else {
        $o = Order::getOrdersIdByDate(date('Y-m-d H:i:s', 0), date('Y-m-d H:i:s'));
    }

    foreach ($o as $i) {
        $or = new Order($i);

        if ($withRelais) {
            if (Chronopost::isChrono($or->id_carrier)) {
                if (array_key_exists($i, $multi)) {
                    $cpt = $multi[$i];
                } else {
                    $cpt = 1;
                }

                for (; $cpt > 0; $cpt--) {
                    $r[] = $or;
                }
            }
        } else { // no relais export for CSO
            if (Chronopost::isChrono($or->id_carrier) && !Chronopost::isRelais($or->id_carrier)) {
                if (array_key_exists($i, $multi)) {
                    $cpt = $multi[$i];
                } else {
                    $cpt = 1;
                }

                for (; $cpt > 0; $cpt--) {
                    $r[] = $or;
                }
            }
        }
    }
    return $r;
}
