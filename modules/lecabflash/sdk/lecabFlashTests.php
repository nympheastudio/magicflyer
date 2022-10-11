<?php

function assert_handler($file, $line, $message)
{
   throw new Exception('TEST FAIL (line '.$line.') : '.$message);
}

class LecabFlashTests {


    public function __construct($api_key=null) {

        assert_options(ASSERT_ACTIVE,   true);
        assert_options(ASSERT_BAIL,     false);
        assert_options(ASSERT_WARNING,  true);
        assert_options(ASSERT_CALLBACK, 'assert_handler');

        $this->sdk = new LecabFlashSdk(LECABFLASH_API_TEST_KEY, true);
        $this->api = new LecabFlashApi(LECABFLASH_API_TEST_KEY, true);

        $this->tests_plan = array();
        foreach ( get_class_methods($this) as $name) {
            if (substr( $name, 0, 5 ) === "test_") {
                $this->tests_plan[] = $name;
            }
        }
    }

    public function run($test) {
        if ($test) {
            $testsplan = array($test);
        } else {
            $testsplan = $this->tests_plan;
            $testsplan = array(
                //'test_sdk_checkAddress',
                //'test_sdk_checkShippingAvailability',
                'test_api_jobConfirm'
            );
        }

        $result = array();
        foreach ($testsplan as $name) {
            ob_start();
            try {
                $this->$name();
                $res = 'OK';
            } catch (Exception $e) {
                $res = $e->getMessage();
            } 

            $logs = ob_get_contents();
            echo $logs;
            ob_end_clean();  
            $result[$name] = array( 
                'result'=> $res,
                'logs'=> $logs,
            );
        }
        return $result;
    }


    /*  
    TESTS CASES
    */

    

    public function test_api_searchLocation() {
        $payload = array(
            'location'=> array(
                'address'=> '116 Av. des Champs-Élysées',
            ),     
            'limit'=> 5
        );    
        $res = $this->api->searchLocation($payload);
        assert( $res->code==200 );
        assert( count($res->data->locations)==5 );
    }


    public function test_api_availableServices() {
        $payload = array(
            'location'=> array(
                'address'=> '116 Av. des Champs-Élysées',
            ),     
        );    
        $res = $this->api->availableServices($payload);  
        assert( $res->code==200 );
        assert( $res->data->location->longitude && $res->data->location->latitude );
        assert( in_array( 'CRS', $res->data->services) );
    }



    public function test_api_searchLocationLeaf() {

        // step1: find a node
        $payload = array(
            'location'=> array(
                'address'=> 'Terminal 2A, ADP - Charles de Gaulle',
            ),     
            'limit'=> 1
        );    
        $res = $this->api->searchLocation($payload);

        assert( $res->code==200 );
        assert( $res->data->locations[0]->type=='NODE' );

        // For the estimation we can use this node location, but for the booking, we'll have to find the correct leaf.
        // step2: search the leaf a node

        $res = $this->api->searchLocation(array(
            'location'=> array(
              'latitude'=> $res->data->locations[0]->latitude,
              'longitude'=> $res->data->locations[0]->longitude,
            ),   
        ));
        assert( $res->code==200 );
        assert( count($res->data->locations)>1 );
    }


    public function test_api_jobEstimate() {

        $payload = array(
            'pickup'=> array(
              'latitude'=> 48.8723063,
              'longitude'=> 2.3009149
            ),
            'drop'=> array(
              'latitude'=> 48.8723063,
              'longitude'=> 2.3009149
            ),
            'service'=> 'CRS',
        );    
        var_dump($payload,true);
        $res = $this->api->jobEstimate($payload);
        var_dump($res,true);
        assert( $res->code==200 , 'HTTP '.$res->code.' '.implode(' ',(array)$res->error));
        assert( $res->data->price > 0 );
        assert( $res->data->estimate_id != '' , '');
        return (string)$res->data->estimate_id; // used for next test
    }
    


    public function test_api_jobConfirm() {

        // step1: find a estimation
        $estimate_id = $this->test_api_jobEstimate();

        // step1: confirm it !
        $payload = array(
            'estimate_id'=> $estimate_id,
            'contacts'=> array(
                'global'=> array(
                    'firstname'=> 'John',
                    'lastname'=> 'Snow',
                    'phone'=> '+33561561568',
                    'email'=> 'test@test.com',
                )
            ),
            'payment'=> array(
                'type'=> 'INVOICE' //'INVOICE'
            ), 
            'notes'=> array(
              'drop'=> 'Thrid floor apt B'
            ),
            //'transports'=> array(),
        );    
        var_dump($payload,true);

        $res = $this->api->jobConfirm($payload);

        var_dump($res,true);

        assert( $res->code==200 );
        assert( $res->data->id != '');
        assert( $res->data->number );
        assert( $res->data->followurl != '' );
        assert( $res->data->price );
        assert( $res->data->price_net );
	

    }
    

    public function test_sdk_checkExpressAvailable() {
        $config = array(
            1 => array(1,null,null,'14:00','18:00'),          
            2 => array(1,'09:00','12:00','14:00','18:00'),
            3 => array(1,'09:00',null,null,'18:00'),
            4 => array(1,'09:00','12:00','14:00','18:00'),
            5 => array(1,'09:00','12:00','14:00','18:00'),
            6 => array(1,'10:00','12:00',null,null),
            7 => array(0,null,null,null,null),
        ); 
        $tests = array(
            '2016-09-19 08:30'=> false, //2016-09-19 is a Monday (1)
            '2016-09-19 09:30'=> false,
            '2016-09-19 14:30'=> true,
            '2016-09-19 19:30'=> false,
            '2016-09-20 08:00'=> false,  //2016-09-20 is a Tuesday (2)
            '2016-09-20 10:30'=> true,  
            '2016-09-20 12:30'=> false,  
            '2016-09-20 14:30'=> true, 
            '2016-09-20 19:30'=> false, 
            '2016-09-21 09:30'=> true, //2016-09-21 is a Wednesday (3)
            '2016-09-21 12:30'=> true,
            '2016-09-21 13:30'=> true,
            '2016-09-21 23:30'=> false,
            '2016-09-24 09:30'=> false, //2016-09-24 is a Saturday (6)
            '2016-09-24 11:30'=> true,
            '2016-09-24 14:30'=> false,
            '2016-09-25 10:30'=> false, //2016-09-25 is a Sunday (7)
            '2016-09-25 14:30'=> false,
        );
        foreach ($tests as $key=>$val) {
            $format = 'Y-m-d H:i';
            $datetime = DateTime::createFromFormat($format, $key); 
            echo '<br>'.$key;
            $res = $this->sdk->checkExpressAvailable($config,$datetime);
            echo '\n'.$key.' '.(int)$val.' => '.(int)$res;
            assert( $res==$val );
        }
        
    }


    public function test_sdk_checkApiKey() {

        $sdk = new LecabFlashSdk($this->API_TEST_KEY);
        assert( $this->sdk->checkApiKey()==true );

        $sdk = new LecabFlashSdk('wrongapikey');
        assert( $this->sdk->checkApiKey()==false );
    }

    public function test_sdk_checkAddress() {

        assert( $this->sdk->checkAddress('116 Av. des Champs-Élysées, Paris, France') );
        assert( !$this->sdk->checkAddress('bad address, somewhere') );

    }

    public function test_sdk_checkDimensions() {
        // maxvalues = 50cmx39x15cm, 30L, 8kg

        $uses_cases_dims = array(
            array(12,30,10,7,true),
            array(12,40,50,7,false),
            array(12,5,10,7,true),
            array(12,5,10,17,false),
            array(60,5,10,17,false),
            array(50,5,50,1,false),
            array(40,15,10,1,true),
            array(55,55,55,100,false),
            array(50,39,15,8,true),
        );

        foreach ($uses_cases_dims as $test) {

            $res = $this->sdk->checkDimensions(array(
                'width'=> $test[0],
                'height'=> $test[1],
                'depth'=> $test[2],
                'weight'=> $test[3],
            ));
            $expected_result = $test[4];
            echo '<br>checking '.implode(',',$test).' => '.(int)$res;
            assert( (bool)$res==(bool)$expected_result );
        }
    }

    public function test_sdk_checkShippingAvailability() {

        $data = array(
            'config'=> array(
                'price_fixed'   => 0,
                'price_rate'    => 0,
                'use_tva'       => true,
            ),
            'pickup'=> array(
                'latitude'=> 48.8723063,
                'longitude'=> 2.3009149
            ),
            'drop'=> array(
                'address'   => '11 rue de milan, 75009 Paris, France',
            ),
            'products'=> array(
                array(
                    'width'=> 12,
                    'height'=> 10,
                    'depth'=> 3,
                    'weight'=> 5,
                )
            ),
        );


        $res = $this->sdk->checkShippingAvailability($data);
        assert(isset($res['estimate_id']));
        assert($res['price']);
    }





}



