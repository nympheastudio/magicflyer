<?php


class LecabFlashApi
{
  
    public function __construct($api_key, $debug = false)
    {
       
        if ($debug) {
            $this->api_endpoint = LECABFLASH_API_TEST.'/'.LECABFLASH_API_VERSION.'/';
            $this->api_key = $api_key;
        } else {
            $this->api_endpoint = LECABFLASH_API_PROD.'/'.LECABFLASH_API_VERSION.'/';
            $this->api_key = $api_key;
        }
    }


    public static function validate($api_key, $address) {
        // check api_key and pickup address compatibility 
        $payload = array(
            'location'=> array(
                'address'=> $address,
            ),     
            'limit'=> 5
        );    
        $api = new LecabFlashApi($api_key);
        $res = $api->searchLocation($payload);

        $validated = ( !$res->error && $res->data && (sizeof($res->data->locations) >= 1) );
        return $validated;
    }


    public function searchLocation($payload) {
        return $this->request('POST','locations/search',$payload);
    }

    public function availableServices($payload) {
        return $this->request('POST','services/available',$payload);
    }

    public function jobEstimate($payload) {
        return $this->request('POST','jobs/estimate',$payload);
    }

    public function jobConfirm($payload) {
        return $this->request('POST','jobs/confirm',$payload);
    }

    private function request($method, $path, $payload = null)
    {
        $req = curl_init($this->api_endpoint.$path);

        // POST payload
        $jsonPayload = null;
        if (!is_null($payload)) {
            $jsonPayload = json_encode($payload);
        }

        // HTTP headers
        $headers = array(
            "Content-Type: application/json",
            "Authorization: X-Api-Key ".$this->api_key,
            "Cache-Control: no-cache",
        );
        if ($jsonPayload) {
            $headers[] = "Content-Length: ".strlen($jsonPayload);
        }

     
        curl_setopt($req, CURLOPT_HTTPHEADER, $headers);

        // set cURL options
        curl_setopt($req, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($req, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($req, CURLOPT_TIMEOUT, 10);

        $result     = curl_exec($req);
        $status     = (int)curl_getinfo($req, CURLINFO_HTTP_CODE);
        $curlError  = curl_error($req);

        LecabFlashLog::debug('==== REQUEST '.$this->api_endpoint.$path);
        LecabFlashLog::debug($jsonPayload);

        curl_close($req);

        LecabFlashLog::debug('==== RESPONSE '.$this->api_endpoint.$path);
        LecabFlashLog::debug($result);

        $result = ($result && strlen($result)) ?  json_decode($result) : null;
        $error = ($curlError) ? array("type" =>'curl',"msg"=>$curlError,'code'=>null) : null;
        
        $result = array(
            "code"    => $status,
            "error"     => $error,
            "data"  => $error ? null : $result,  
        );


        
        return (object)$result;
    }

   
}
