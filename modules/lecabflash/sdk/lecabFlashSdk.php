<?php

require_once dirname(__FILE__).'/config.php';

require_once dirname(__FILE__).'/lecabFlashLog.php';
require_once dirname(__FILE__).'/lecabFlashApi.php';
require_once dirname(__FILE__).'/lecabFlashTests.php';

class LecabFlashSdk
{
    public $api;

    public function __construct($api_key, $debug = false)
    {
        $this->api = new LecabFlashApi($api_key, $debug);
    }

    public function checkExpressAvailable($config,$datetime=null,$timezone=null) {
        // $weekday = ISO-8601 du jour de la semaine 1 (pour Lundi) à 7 (pour Dimanche)
        /*
        $config = array(
            1 => array(1,'09:00',null,null,'18:00'), 
            2 => array(1,'09:00',null,null,'18:00'),
            3 => array(1,'09:00',null,null,'18:00'),
            4 => array(1,'09:00',null,null,'18:00'),
            5 => array(1,'09:00',null,null,'18:00'),
            6 => array(1,'09:00',null,null,'18:00'),
            7 => array(0,null,null,null,null),
        ); 
        */
        if (!$datetime) {
            $datetime = new DateTime();
            //$datetime->setTimezone($tz_object);    
        }

        $weekday = $datetime->format('N');
        $today = $datetime->format('Y-m-d');

        $conf = isset($config[$weekday]) ? $config[$weekday] : null;

        if ($conf && !empty($conf)) {

            $active = $conf[0];
            if (!$active)
                return false;

            $am_open = $conf[1];
            $am_close = $conf[2];
            $pm_open = $conf[3];
            $pm_close = $conf[4];

            $format = 'Y-m-d H:i';
            $shoptime_am_open = $am_open ? DateTime::createFromFormat($format, $today.' '.$am_open) : null;
            $shoptime_am_close = $am_close ? DateTime::createFromFormat($format, $today.' '.$am_close) : null;
            $shoptime_pm_open = $pm_open ? DateTime::createFromFormat($format, $today.' '.$pm_open) : null;
            $shoptime_pm_close = $pm_close ? DateTime::createFromFormat($format, $today.' '.$pm_close): null;

            //todo ajouter delta ouverture+fermeture (-15mn ?)

            $check_day = ($shoptime_am_open && !$shoptime_am_close && !$shoptime_pm_open && $shoptime_pm_close) ? ( $datetime>=$shoptime_am_open && $datetime<=$shoptime_pm_close ) : false;
            $check_am = ($shoptime_am_open) ? ( $datetime>=$shoptime_am_open && $datetime<=$shoptime_am_close ) : false;
            $check_pm = ($shoptime_pm_open) ? ( $datetime>=$shoptime_pm_open && $datetime<=$shoptime_pm_close ) : false;

            $check = $check_am || $check_pm || $check_day;

            return $check;
        }
        return false;
    }


    public static function checkApiKey($api_key) {
        // Envoyer une requête jobs/confirm sans renseigner de body et vérifier qu'il ne retourne pas une erreur 401 (clé invalide).
        $sdk = new LecabFlashSdk($api_key);
        $res = $sdk->api->jobConfirm(null);
        return !($res->code == 401);
    }


    private function getLocation($address) {
        // check pickup address compatibility 
        $payload = array(
            'location'=> array(
                'address'=> $address,
            ),
            'limit'=> 5
        );
        $res = $this->api->searchLocation($payload);
        if ( !$res->error && $res->data && (sizeof($res->data->locations) >= 1) ) {
            foreach ($res->data->locations as $location) {
                if ($location->type=='LEAF') {
                    //toconfirm : we stop at the first leaf
                    return array(
                        'latitude'=> $location->latitude,
                        'longitude'=> $location->longitude,
                    );
                }
            }
        } else {
            return null;
        }

        return null;
    }

    public function checkAddress($address) {
        $payload = array(
            'location'=> array(
                'address'=> $address,
            ),
        );
        $res = $this->api->availableServices($payload);
        $services = isset($res->data) && isset($res->data->services) ? $res->data->services : array();
        if ($res->code==200 && in_array( 'CRS', $services) ) {
            return array(
                'id'=>$res->data->location->id,
                'latitude'=> $res->data->location->latitude,
                'longitude'=> $res->data->location->longitude,
            );
        }
        if($res->code==200 && !in_array( 'CRS', $services)) {
            return array(
                'CRS_error'=> -11,
            );
        }

        return null;
    }


    public static function checkDimensions($data) {

        $max_width = LECABFLASH_MAX_WIDTH;
        $max_height = LECABFLASH_MAX_HEIGHT;
        $max_depth = LECABFLASH_MAX_DEPTH;
        $max_weight = LECABFLASH_MAX_WEIGHT;
        $max_vol = LECABFLASH_MAX_VOL;

        $width  = $data['width'];
        $height = $data['height'];
        $depth  = $data['depth'];
        $weight = $data['weight'];

        // check weight
        if ($weight>$max_weight) return false;

        // check dims limits

        $maxs = array(LECABFLASH_MAX_WIDTH,LECABFLASH_MAX_HEIGHT,LECABFLASH_MAX_DEPTH);
        rsort($maxs);

        $dims = array($width,$height,$depth);
        rsort($dims);

        if (max($dims)>max($maxs))
            return false;

        // check dims vs maxs
        foreach($dims as $dim) {
            foreach($maxs as $max) {
                if (floatval($dim)>floatval($max)) {
                    // is max value available 
                    if (empty($maxs) || floatval($dim)>floatval(array_shift($maxs)) )
                        return false;
                    break; // we already use a slot so pass this value
                }
            }
        }
        return true;
    }



    public function checkShippingAvailability($data) {

        // step1: checking size/weight compatibility

        // 50cmx39x15cm, 30L, 8kg

        //todo: conversion des unités ! 
        //todo: filtrage independant w,h,d  3 valeurs (une des 3 ne dois pas depasser 50, etc ..)
        //todo: que faire si 0 ??

        $limit_width = 50;
        $limit_height = 39;
        $limit_depth = 15;
        $limit_weight = 8;

        $max_width = 0;
        $max_height = 0;
        $max_depth = 0;
        $total_weight = 0;

        foreach ($data['products'] as $product) {

            if ($product['width'] == "0.000000") {
                $product['width'] = "30.000000";
            }

            if ($product['height'] == "0.000000") {
                $product['height'] = "30.000000";
            }

            if ($product['depth'] == "0.000000") {
                $product['depth'] = "15.000000";
            }
            $max_width = max( $max_width, $product['width']);
            $max_height = max( $max_height, $product['height']);
            $max_depth = max( $max_depth, $product['depth']);
            $total_weight += $product['weight'];
        }
        $dims_to_check = array(
            'width'=> $max_width,
            'height'=> $max_height,
            'depth'=> $max_depth,
            'weight'=> $total_weight,
        );

        if (!$this->checkDimensions($dims_to_check))
            return array('code'=> -1 );


        // step3: checking address compatibility + get lat,long of dropping address

        $drop_address = $data['drop']['address'];

        $drop_location = $this->checkAddress($drop_address);
        if (!$drop_location)
            return array('code'=> -10, 'address'=>$drop_location, 'input'=>$data);

        if (isset($drop_location['CRS_error']))
            return array('code'=> -11, 'address'=>$drop_location, 'input'=>$data);

        // step3: getting quotation 
        $pickup_latitude = $data['pickup']['latitude'];
        $pickup_longitude = $data['pickup']['longitude'];

        $drop_latitude = $drop_location['latitude'];
        $drop_longitude = $drop_location['longitude'];

        $payload = array(
            'pickup'=> array(
                'latitude'=>    floatval($pickup_latitude),
                'longitude'=>   floatval($pickup_longitude),
            ),
            'drop'=> array(
                'latitude'=>    floatval($drop_latitude),
                'longitude'=>   floatval($drop_longitude),
            ),
            'service'=> 'CRS',

        );
        if (isset($data['date'])) {
            $payload['date'] = $data['date'];
            $payload['date_location'] = 'DROP';
        }

        $res = $this->api->jobEstimate($payload);

        if ( $res->error || !$res->data )
            return array('code'=> -20 ,'error'=> $res->error );

        if ($res->code!=200)
            return array('code'=> $res->code ,'error'=> $res->data->errors, 'request'=>$payload);


        $estimation = $res->data;
        LecabFlashLog::info('Estimation : '.Tools::jsonEncode($estimation));

        $estimate_id = $res->data->estimate_id;
        $estimate_price = floatval($res->data->price);
        $estimate_delay = (int)$res->data->delay;
        $estimate_duration = (int)$res->data->duration_max;
        $estimate_duration = (!$estimate_duration) ? (int)$res->data->duration_min : $estimate_duration;

        // step3: shipper disponibility

        $price_fixed = $data['config']['price_fixed'];
        $price_rate = $data['config']['price_rate'];
        $use_tva = $data['config']['use_tva'];

        $estimate_price = floatval($estimate_price);
        $price_fixed = floatval($price_fixed);
        $price_rate = floatval($price_rate);
        $price = ($estimate_price + $price_fixed) + ($estimate_price*$price_rate/100);
        $price = ($price < 0) ? 0 : $price;

        // estimate dates pickup and drop

        $is_rdv = (bool)(isset($data['rdv']) && $data['rdv']);
        $now = new DateTime();

        if (isset($data['date']) && $data['date']) {
            $datebase = DateTime::createFromFormat( DateTime::ISO8601, $data['date']);
        } else {
            $datebase = clone $now;
        }
        if ($datebase < $now->modify('+1 hour') && $is_rdv) {
            if ((int)$estimate_delay > 60) {
                $now->modify('+'.((int)$estimate_delay - 60).' minutes'); # we already add 1 hour.
            }
            $datebase = clone $now;
        }
        $datebase_pickup = clone $datebase;

        $total_delay = (int)$estimate_delay + LECABFLASH_DATE_DROP_OFFSET;

        if ($is_rdv) {
            $datebase_pickup->modify('-'.(int)$estimate_duration.' minutes');
        } else {
            $datebase_pickup->modify('+'.(int)$total_delay.' minutes');
        }
        $date_pickup = $datebase_pickup->format('c');

        $offset = (int)$total_delay + (int)$estimate_duration;
        $datebase_drop = clone $datebase;
        if (!$is_rdv) {
            $datebase_drop->modify('+'.(int)$offset.' minutes');
        }
        $date_drop = $datebase_drop->format('c');
        return array(
            'estimate_id'=> $estimate_id,
            'delay' => (int)$res->data->delay,
            'duration_min' => (int)$res->data->duration_min,
            'duration_max' => (int)$res->data->duration_max,
            'price'=> $price,
            'date_pickup'=> $date_pickup,
            'date_drop'=> $date_drop,
        );
    }

    public function confirmJob($payload) {
        $res = $this->api->jobConfirm($payload);
        return $res;
    }



}
