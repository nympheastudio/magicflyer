<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Artem B, Olivier B., Eric Turcios
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
*/

if (!defined('AMAZON_MARKETPLACE_VERSION')) {
    define('AMAZON_MARKETPLACE_VERSION', '4.0');
}
require_once(dirname(__FILE__).'/../classes/amazon.certificates.class.php');

/**
 * Class AmazonWebService
 */
class AmazonWebService
{
    /**
     *
     */
    const DROP_THROTLLED_QUERIES = 1;
    /**
     *
     */
    const OPERATIONS_UPDATE = 1;
    /**
     *
     */
    const OPERATIONS_CREATE = 2;
    /**
     *
     */
    const OPERATIONS_DELETE = 3;
    /**
     *
     */
    const MWS_DO_NOT_SEND = 1;
    /**
     *
     */
    const MWS_SEND = 2;

    /**
     * @var int
     */
    public $MWS_Action;
    /**
     * @var bool
     */
    public $displayXML = false;
    /**
     * @var bool
     */
    public $demo = false;

    /**
     * @var bool
     */
    private $_debug;

    /* Anti Throttling*/
    /**
     * @var string
     */
    private $_cr;
    /**
     * @var string
     */
    private $_att;

    /* Handler for database read/write throttling timer*/
    /**
     * @var false|null|string
     */
    private $region;
    /**
     * @var
     */
    private $Currency;
    /**
     * @var
     */
    private $mid;
    /**
     * @var
     */
    protected $mpid;
    /**
     * @var
     */
    private $awsak;
    /**
     * @var
     */
    private $sk;
    /**
     * @var
     */
    private $tk;

    /*Amazon Europe*/
    /**
     * @var array|null
     */
    private $MarketPlaces = array();

    /** @var array $curlInfo */
    private $curlInfo = array();

    /**
     * @var string
     */
    private static $mwsops_active = false;
    private static $mwsops = 'https://mwsops.common-services.com';
    private static $mwsops_version = 'v1';

    /**
     *
     * @param <type> $auth - associative massive
     * auth['MerchantID'] - string, MerchantID
     * auth['MarketplaceID'] - string, MarketplaceID
     * auth['AWSAccessKeyID'] - string, AWSAccessKeyID
     * auth['SecretKey'] - string, SecretKey
     * auth['mwsToken'] - string, Token
     * @param <type> $from associative array
     * from['Country'] - country code, must be one of the following:
     * us, uk, de, fr, jp, cn, ca .
     * That codes means:
     * us - United States, uk - United Kingdom, de - Germany, fr - France, jp - Japan, cn - China, ca - Canada
     * from['Currency'] - must be one of the following:
     *
     * @param <bool> $debug
     */
    public function __construct($auth, $from, $marketPlaces = null, $debug = false, $cr = "")
    {
        if (! $cr) {
            $cr = nl2br(Amazon::LF);
        }
        $region = null;

        if ($debug == true) {
            $this->_debug = true;
        } else {
            $this->_debug = false;
        }

        $this->_cr = $cr; //new line symbol
        //this variable uses when debuging mode is on and error happened
        $this->_att = $this->htmlTag('font', array('color' => 'red')) . '!!!' . $this->htmlTag('/font');
        //This is to draw attention to an error

        // Creation or Update
        //
        $this->_operationMode = self::OPERATIONS_UPDATE;

        // Do not simulate
        $this->MWS_Action = self::MWS_SEND;

        $this->setAuth($auth);

        if ($marketPlaces) {
            $this->MarketPlaces = $marketPlaces;
        } else {
            $this->MarketPlaces = null;
        }

        //get Region
        if (isset($from['Country'])) {
            $region = $this->retRegion($from['Country']);
        } elseif (isset($from['Country']) && $region == false) {
            if ($this->_debug) {
                CommonTools::p('retRegion() function returns false to constructor of the Service class. It means, which that function comleted incorrectly. Object of the Service class can not be created now.');
            }
        }


        $this->region = $region;

        if (isset($from['Currency'])) {
            if ($this->setCurrency($from['Currency']) == false) {
                if ($this->_debug) {
                    CommonTools::p('setCurrency() function returns false to constructor of the Service class. It means, which that function comleted incorrectly. Object of the Service class can not be created now.');
                }
            }
        }
        if ($this->_debug) {
            CommonTools::p('Constructor Completed successfully');
        }


        if ($this->_debug) {
            CommonTools::p('Constructor. Object of the Service class created succesfully');
        }
    }

    /**
     * @param $auth
     *
     * @return bool
     */
    private function setAuth($auth)
    {
        if ($this->_debug) {
            CommonTools::p(sprintf('setAuth() call - called by %s', $this->_caller()));
        }

        if ($auth['MerchantID'] != null) {
            $this->mid = $auth['MerchantID'];
        } else {
            if ($this->_debug) {
                CommonTools::p(sprintf('setAuth function. %s Error MerchantID== null . Invalid value.', $this->_att));
            }

            return false;
        }

        if (isset($auth['MarketplaceID'])) {
            if ($auth['MarketplaceID'] != null) {
                $this->mpid = $auth['MarketplaceID'];
            } else {
                if ($this->_debug) {
                    CommonTools::p(sprintf('setAuth function. %s Error MarketplaceID == null . Invalid value.', $this->_att));
                }

                return false;
            }
        }
        if ($auth['AWSAccessKeyID'] != null) {
            $this->awsak = $auth['AWSAccessKeyID'];
        }
        if ($auth['SecretKey'] != null) {
            $this->sk = $auth['SecretKey'];
        }
        if (isset($auth['mwsToken']) && $auth['mwsToken'] != null) {
            $this->tk = $auth['mwsToken'];
        }
        // Activate webservice queries proxying
        if (strlen($this->tk) && !strlen($this->awsak && $this->mpid && in_array($this->mpid, Amazon::$mwsops_required))) {
            self::$mwsops_active = true;
        }
        return (true);
    }

    /**
     * @return string
     */
    private function _caller()
    {
        $trace = debug_backtrace();
        $caller = $trace[2];

        $ret = 'called by '.$caller['function'].'() ';
        if (isset($caller['class'])) {
            $ret .= 'in '.$caller['class'];
        }

        return ($ret);
    }

    /**
     *
     * @param <string> $countryFrom country code.
     * Must be one of the following: us, uk, de, fr, jp, cn, ca
     * @return string|false - Feeds Web Service Url of false if unsuccessful
     */
    private function retRegion($countryFrom)
    {
        if ($this->_debug) {
            printf('retRegion() call. countryFrom = %s%s', $countryFrom, $this->_cr);
        }

        switch ($countryFrom) {
            case 'au':
                return 'com.au';
            case 'us':
                return 'com';
            case 'uk':
                return 'co.uk';
            case 'de':
                return 'de';
            case 'fr':
                return 'fr';
            case 'it':
                return 'it';
            case 'in':
                return 'in';
            case 'es':
                return 'es';
            case 'be':
                return 'be';
            case 'ja':
                return 'jp';
            case 'jp':
                return 'jp';
            case 'cn':
                return 'cn';
            case 'ca':
                return 'ca';
            case 'mx':
                return 'mx';
            case 'br':
                return '.com.br';
        }
        if ($this->_debug) {
            printf('%sgenerateFeedServiceUrl() function. %sError. Incorrect Country Code "%s" . Please verify and try again.', $this->_cr, $this->_att, $countryFrom, $this->_cr);
        }

        return false;
    }

    /**
     * @param $curr
     *
     * @return bool
     */
    private function setCurrency($curr)
    {
        if ($this->_debug) {
            printf('setCurrency() call. curr = %s%s', $curr, $this->_cr);
        }

        if (preg_match('/USD|GBP|EUR|JPY|CAD|INR|MXN|AUD|CNY|BRL/i', $curr)) {
            $this->Currency = Tools::strtoupper($curr);

            return true;
        }
        if ($this->_debug) {
            printf('setCurrency() function. %sError. You sent invalid currency:
                  %s Please verify this and try again. %s', $this->_att, $curr, $this->_cr);
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->mid;
    }

    /**
     * @param $operationMode
     *
     * @return bool
     */
    public function setOperationMode($operationMode)
    {
        switch ((int)$operationMode) {
            case self::OPERATIONS_UPDATE:
                $this->_operationMode = self::OPERATIONS_UPDATE;
                break;
            case self::OPERATIONS_CREATE:
                $this->_operationMode = self::OPERATIONS_CREATE;
                break;
            case self::OPERATIONS_DELETE:
                $this->_operationMode = self::OPERATIONS_DELETE;
                break;
            default:
                if ($this->_debug) {
                    printf("$this->_cr setOperationMode() unknown value".$this->_cr);
                }

                return (false);
        }

        return (true);
    }

    /**
     * @param bool $returnXML
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public function serviceStatus($returnXML = false)
    {
        if ($this->_debug == true) {
            printf("$this->_cr ConnectionCheck call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'GetServiceStatus';

        $API = 'Sellers';

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->GetServiceStatusResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }

    /**
     * @param $params
     * @param $API
     * @param bool $returnXML
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public function simpleCallWS($params, $API, $returnXML = true, $feedContent = null)
    {
        $header = array();
        $handle = null;
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';

        $params['AWSAccessKeyId'] = $this->awsak;
        if ($this->tk) {
            $params['MWSAuthToken'] = $this->tk;
        }

        $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');

        if (!isset($params['Version'])) {
            $params['Version'] = '2011-07-01';
        }

        $uri = '/'.$API;
        $is_feed = false;
        switch ($API) {
            case 'Feeds':
                $is_feed = true;
                $params['Version'] = '2009-01-01';
                $uri = '/';
                if ($feedContent) {
                    $md5 = base64_encode(md5($feedContent, true));
                    $header += array(
                        'Expect: ',
                        'Accept: ',
                        'Transfer-Encoding: chunked',
                        'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                        'Content-MD5: '.$md5
                    );

                    $handle = @fopen('php://temp', 'rw+');
                    fwrite($handle, $feedContent);
                    rewind($handle);
                } else {
                    $md5 = null;
                    $is_feed = false;
                }
                break;
            case 'Reports':
            case 'ReportsDownload':
                $params['Version'] = '2009-01-01';
                $uri = '/';
                break;
            case 'Products':
                $params['Version'] = '2011-10-01';
                $uri = '/Products';
                break;
            case 'Orders':
                $params['Version'] = '2013-09-01';
                $uri = '/Orders';
                break;
        }

        ksort($params);

        $method = 'POST';
        $host = $this->_endPointURL();

        // create the canonicalized query
        $canonicalized_query = array();

        foreach ($params as $param => $value) {
            $param = str_replace('%7E', '~', rawurlencode($param));
            $value = str_replace('%7E', '~', rawurlencode($value));
            $canonicalized_query[] = $param.'='.$value;
        }

        $canonicalized_query = implode('&', $canonicalized_query);

        // create the string to sign
        $string_to_sign = $method.Amazon::LF.$host.Amazon::LF.$uri.Amazon::LF.$canonicalized_query;

        // calculate HMAC with SHA256 and base64-encoding
        $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $this->sk, true));//TODO: Validation: API Requirement

        // encode the signature for the request
        $signature = str_replace('%7E', '~', rawurlencode($signature));

        $curlOptions = array(
            CURLOPT_POST => true,
            CURLOPT_USERAGENT => 'Common-Services/Amazon Marketplace/'.AMAZON_MARKETPLACE_VERSION.' (Language=PHP/'.phpversion().')',
            CURLOPT_RETURNTRANSFER => true,
        );

        if ($is_feed) {
            $curlOptions[CURLOPT_HTTPHEADER] = $header;
            $curlOptions[CURLOPT_INFILE] = $handle;
            $curlOptions[CURLOPT_URL] = $final_url = 'https://'.$host.$uri.'?'.$canonicalized_query.'&Signature='.$signature;
        } else {
            $curlOptions[CURLOPT_URL] = $final_url = 'https://'.$host.$uri;
            $curlOptions[CURLOPT_POSTFIELDS] = $canonicalized_query.'&Signature='.$signature;
        }

        if ($this->_debug) {
            $verbose = true;
        }
        if (self::$mwsops_active) {
            $curlOptions[CURLOPT_URL] = sprintf('%s/post%s?uri=%s', self::$mwsops, self::$mwsops_version, base64_encode($final_url));
        }

        $disable_ssl_check = (bool)Configuration::get('AMAZON_DISABLE_SSL_CHECK');

        if (!$disable_ssl_check) {
            $cert = AmazonCertificates::getCertificate();

            $curlOptions[CURLOPT_SSL_VERIFYHOST] = 2;
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = 1;
            $curlOptions[CURLOPT_CAINFO] = $cert;
            $curlOptions[CURLOPT_CAPATH] = dirname($cert);
        } else {
            $curlOptions[CURLOPT_SSL_VERIFYHOST] = 0;
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = 0;
        }

        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, $curlOptions);

        if (defined('CURLOPT_ENCODING')) {
            $header[] = 'Accept-Encoding: gzip,deflate';
            curl_setopt($curlHandle, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $header);
        }

        if ($this->_debug) {
            $verbose = true;
        } else {
            $verbose = false;
        }

        if ($verbose) {
            curl_setopt($curlHandle, CURLOPT_VERBOSE, true);

            $verbose = fopen('php://temp', 'w+');
            curl_setopt($curlHandle, CURLOPT_STDERR, $verbose);
        }


        if ($this->displayXML && Tools::strlen($feedContent)) {
            $XML = new DOMDocument();
            $XML->loadXML($feedContent);
            $XML->formatOutput = true;

            echo $this->htmlTag('b', array('class' => 'amazon-url')) . $curlOptions[CURLOPT_URL] . $this->htmlTag('/b');
            echo $this->htmlTag('pre', array('class' => 'amazon-xml')) . htmlentities($XML->saveXML()) . $this->htmlTag('/pre');
        }

        if ($this->MWS_Action == self::MWS_SEND) {
            if (($result = curl_exec($curlHandle)) == false) {
                $pass = false;
            } else {
                $pass = true;
            }
        } else {
            // Simulate true result
            $result = true;
            $pass = true;
        }

        if ($verbose) {
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);

            echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
        }

        $this->curlInfo = curl_getinfo($curlHandle);

        if ($this->_debug) {
            CommonTools::p($this->_caller());
            CommonTools::p("Header:".print_r($header, true));
            CommonTools::p("Params: ");
            CommonTools::p($curlOptions);
            CommonTools::p("CURL Data: ");
            CommonTools::p($params);
            CommonTools::p($this->curlInfo);                                // get error info
            CommonTools::p('cURL error number:'.curl_errno($curlHandle));   // print error info
            CommonTools::p('cURL error:'.curl_error($curlHandle));
        }
        if (!$pass) {
            return (false);
        }

        curl_close($curlHandle);
        if ($is_feed) {
            fclose($handle);
        }

        if ($returnXML && !is_bool($result)) {
            try {
                $xml = new SimpleXMLElement($result);

                return ($xml);
            } catch (Exception $e) {
                CommonTools::p('Exception Caught: '.$e->getMessage());

                return false;
            }
        }

        return ($result);
    }


    /**
     * @param $debug
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public static function getServiceStatus($debug)
    {
        $header = array();
        $params = array();
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
        $params['Version'] = '2011-07-01';
        $params['SellerId'] = '';
        $params['Action'] = 'GetServiceStatus';
        $uri = '/Sellers';

        ksort($params);

        $method = 'POST';
        $host = 'mws.amazonservices.com';

        // create the canonicalized query
        $canonicalized_query = array();

        foreach ($params as $param => $value) {
            $param = str_replace('%7E', '~', rawurlencode($param));
            $value = str_replace('%7E', '~', rawurlencode($value));
            $canonicalized_query[] = $param.'='.$value;
        }

        $canonicalized_query = implode('&', $canonicalized_query);

        // create the string to sign
        $string_to_sign = $method.Amazon::LF.$host.Amazon::LF.$uri.Amazon::LF.$canonicalized_query;


        // calculate HMAC with SHA256 and base64-encoding
        $signature = base64_encode(hash_hmac('sha256', $string_to_sign, '', true));//TODO: Validation: API Requirement

        // encode the signature for the request
        $signature = str_replace('%7E', '~', rawurlencode($signature));

        $curlOptions = array(
        CURLOPT_POST => true,
        CURLOPT_USERAGENT => 'Common-Services/Amazon Marketplace/'.AMAZON_MARKETPLACE_VERSION.' (Language=PHP/'.phpversion().')',
        CURLOPT_RETURNTRANSFER => true,
        );

        $curlOptions[CURLOPT_URL] = $url = 'https://'.$host.$uri;

        if (self::$mwsops_active) {
            $curlOptions[CURLOPT_URL] = sprintf('%s/post%s?uri=%s', self::$mwsops, self::$mwsops_version, base64_encode($url));
        }
        $curlOptions[CURLOPT_POSTFIELDS] = $canonicalized_query.'&Signature='.$signature;
        $curlOptions[CURLOPT_VERBOSE] = false;

        $disable_ssl_check = (bool)Configuration::get('AMAZON_DISABLE_SSL_CHECK');

        if (!$disable_ssl_check) {
            $cert = AmazonCertificates::getCertificate();

            $curlOptions[CURLOPT_SSL_VERIFYHOST] = 2;
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = 1;
            $curlOptions[CURLOPT_CAINFO] = $cert;
            $curlOptions[CURLOPT_CAPATH] = dirname($cert);
        } else {
            $curlOptions[CURLOPT_SSL_VERIFYHOST] = 0;
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = 0;
        }

        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, $curlOptions);


        if (defined('CURLOPT_ENCODING')) {
            $header[] = 'Accept-Encoding: gzip,deflate';
            curl_setopt($curlHandle, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $header);
        }

        if (($result = curl_exec($curlHandle)) == false) {
            $pass = false;
        } else {
            $pass = true;
        }

        if ($debug) {
            CommonTools::p("Params:");
            CommonTools::p($curlOptions);
            CommonTools::p("CURL Data:");
            CommonTools::p($params);
            CommonTools::p(curl_getinfo($curlHandle));
            CommonTools::p('cURL error number:'.curl_errno($curlHandle));
            CommonTools::p('cURL error:'.curl_error($curlHandle));
        }
        if (!$pass) {
            return (false);
        }

        curl_close($curlHandle);

        try {
            $xml = new SimpleXMLElement($result);

            return ($xml);
        } catch (Exception $e) {
            return $result;
        }
    }


    /**
     * @return null|string
     */
    private function _endPointURL()
    {
        switch (Tools::strtolower(trim($this->region))) {
            case 'com':
                return ('mws.amazonservices.com');
            case 'us':
                return ('mws.amazonservices.com');
            case 'ca':
                return ('mws.amazonservices.ca');
            case 'jp':
            case 'ja':
                return ('mws.amazonservices.jp');
            case 'cn':
                return ('mws.amazonservices.com.cn');
            case 'in':
                return ('mws.amazonservices.in');
            case 'mx':
                return ('mws.amazonservices.com.mx');
            case 'co.uk':
                return ('mws-eu.amazonservices.com');
            case 'com.au':
                return ('mws.amazonservices.com.au');
            case 'com.br':
                return ('mws.amazonservices.com');
            case 'de':
            case 'es':
            case 'fr':
            case 'it':
            case 'uk':
                return ('mws-eu.amazonservices.com');
            default:
                if ($this->_debug) {
                    printf('%s_endPointURL() function. Error. Incorrect Region Code "%s" . Please verify and try again.%s', $this->_att, $this->region, $this->_cr);
                    die;
                }

                return (null);
        }
    }

    /**
     * @param bool $returnXML
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public function listMarketplaceParticipations($returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr ListMarketplaceParticipations call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'ListMarketplaceParticipations';

        $API = 'Sellers';

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->ListMarketplaceParticipationsResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }

    /**
     * @param bool $returnXML
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public function listSubscriptions($returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr ListSubscriptions call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'ListSubscriptions';
        $params['Version'] = '2013-07-01';

        $params['MarketplaceId'] = $this->mpid;

        $API = 'Subscriptions';

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->ListSubscriptionsResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }

    /**
     * @param $URL
     * @param string $notificationType
     * @param bool $returnXML
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public function getSubscription($URL, $notificationType = 'AnyOfferChanged', $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr GetSubscription call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'GetSubscription';
        $params['Version'] = '2013-07-01';

        $params['MarketplaceId'] = $this->mpid;
        $params['NotificationType'] = $notificationType;

        $params['Destination.DeliveryChannel'] = 'SQS';
        $params['Destination.AttributeList.member.1.Key'] = 'sqsQueueUrl';
        $params['Destination.AttributeList.member.1.Value'] = $URL;

        $API = 'Subscriptions';

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->ListSubscriptionsResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }

    /**
     * @param $URL
     * @param string $notificationType
     * @param bool $returnXML
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public function createSubscription($URL, $notificationType = 'AnyOfferChanged', $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr CreateSubscription call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'CreateSubscription';
        $params['Version'] = '2013-07-01';

        $params['MarketplaceId'] = $this->mpid;
        $params['Subscription.NotificationType'] = $notificationType;
        $params['Subscription.IsEnabled'] = 'true';

        $params['Subscription.Destination.DeliveryChannel'] = 'SQS';
        $params['Subscription.Destination.AttributeList.member.1.Key'] = 'sqsQueueUrl';
        $params['Subscription.Destination.AttributeList.member.1.Value'] = $URL;

        $API = 'Subscriptions';

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->ListSubscriptionsResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }

    /**
     * @param $URL
     * @param string $notificationType
     * @param bool $returnXML
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public function deleteSubscription($URL, $notificationType = 'AnyOfferChanged', $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr DeleteSubscription call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'DeleteSubscription';
        $params['Version'] = '2013-07-01';

        $params['MarketplaceId'] = $this->mpid;
        $params['NotificationType'] = $notificationType;
        $params['Subscription.IsEnabled'] = 'true';

        $params['Destination.DeliveryChannel'] = 'SQS';
        $params['Destination.AttributeList.member.1.Key'] = 'sqsQueueUrl';
        $params['Destination.AttributeList.member.1.Value'] = $URL;

        $API = 'Subscriptions';

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->ListSubscriptionsResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }

    /**
     * @param $URL
     * @param bool $returnXML
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public function sendTestNotificationToDestination($URL, $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr SendTestNotificationToDestination call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'SendTestNotificationToDestination';
        $params['Version'] = '2013-07-01';

        $params['MarketplaceId'] = $this->mpid;
        $params['Destination.DeliveryChannel'] = 'SQS';
        $params['Destination.AttributeList.member.1.Key'] = 'sqsQueueUrl';
        $params['Destination.AttributeList.member.1.Value'] = $URL;

        $API = 'Subscriptions';

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->SendTestNotificationToDestinationResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }

    /**
     * @param $URL
     * @param bool $returnXML
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public function RegisterDestination($URL, $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr RegisterDestination call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'RegisterDestination';
        $params['Version'] = '2013-07-01';

        $params['MarketplaceId'] = $this->mpid;
        $params['Destination.DeliveryChannel'] = 'SQS';
        $params['Destination.AttributeList.member.1.Key'] = 'sqsQueueUrl';
        $params['Destination.AttributeList.member.1.Value'] = $URL;

        $API = 'Subscriptions';

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->RegisterDestinationResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }


    /**
     * @param $URL
     * @param bool $returnXML
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public function DeregisterDestination($URL, $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr DeregisterDestination call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'DeregisterDestination';
        $params['Version'] = '2013-07-01';

        $params['MarketplaceId'] = $this->mpid;
        $params['Destination.DeliveryChannel'] = 'SQS';
        $params['Destination.AttributeList.member.1.Key'] = 'sqsQueueUrl';
        $params['Destination.AttributeList.member.1.Value'] = $URL;

        $API = 'Subscriptions';

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->DeregisterDestinationResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }


    /**
     * @param bool $returnXML
     *
     * @return bool|mixed|SimpleXMLElement
     */
    public function listRegisteredDestinations($returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr ConnectionCheck call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'ListRegisteredDestinations';
        $params['Version'] = '2013-07-01';
        $params['MarketplaceId'] = $this->mpid;

        $API = 'Subscriptions';

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->ListRegisteredDestinationsResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }

    /**
     * @param $ASINs
     * @param bool $returnXML
     *
     * @return bool|mixed|null|SimpleXMLElement
     */
    public function getMyPriceForASIN($ASINs, $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr GetMyPriceForASIN call. $this->_cr");
        }

        if (!is_array($ASINs) || !count($ASINs)) {
            return (null);
        }

        $params = array();
        $params['Action'] = 'GetMyPriceForASIN';
        $params['MarketplaceId'] = $this->mpid;

        $API = 'Products';

        $i = 0;

        foreach ($ASINs as $asin) {
            $i++;
            $params['ASINList.ASIN.'.$i] = $asin;
        }

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->GetMyPriceForASINResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }

    /**
     * @param $SKUs
     * @param bool $returnXML
     *
     * @return bool|mixed|null|SimpleXMLElement
     */
    public function getCompetitivePricingForSKU($SKUs, $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr   GetCompetitivePricingForSKU call. $this->_cr");
        }

        if (!is_array($SKUs) || !count($SKUs)) {
            return (null);
        }

        $params = array();
        $params['Action'] = 'GetCompetitivePricingForSKU';
        $params['MarketplaceId'] = $this->mpid;

        $API = 'Products';

        $i = 0;

        foreach ($SKUs as $asin) {
            $i++;
            $params['SellerSKUList.SellerSKU.'.$i] = $asin;
        }

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->GetCompetitivePricingForSKUResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }

    /**
     * @param $SKUs
     * @param bool $returnXML
     *
     * @return bool|mixed|null|SimpleXMLElement
     */
    public function getMyPriceForSKU($SKUs, $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr   GetMyPriceForSKU call. $this->_cr");
        }

        if (!is_array($SKUs) || !count($SKUs)) {
            return (null);
        }

        $params = array();
        $params['Action'] = 'GetMyPriceForSKU';
        $params['MarketplaceId'] = $this->mpid;

        $API = 'Products';

        $i = 0;

        foreach ($SKUs as $sku) {
            $i++;
            $params['SellerSKUList.SellerSKU.'.$i] = $sku;
        }

        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->GetMyPriceForSKUResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }


    /**
     * @param $codes
     * @param string $type
     * @param bool $returnXML
     *
     * @return bool|mixed|null|SimpleXMLElement
     */
    public function getMatchingProductForId($codes, $type = 'EAN', $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr getMatchingProductForId call. $this->_cr");
        }

        if (!is_array($codes) || !count($codes)) {
            return (null);
        }

        $params = array();
        $params['Action'] = 'GetMatchingProductForId';
        $params['IdType'] = $type;
        $params['MarketplaceId'] = $this->mpid;

        $API = 'Products';

        $i = 0;

        foreach ($codes as $code) {
            $i++;
            $params['IdList.Id.'.$i] = $code;
        }


        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->GetMatchingProductForIdResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }
    /**
     * @param $asins
     * @param bool $returnXML
     *
     * @return bool|mixed|null|SimpleXMLElement
     */
    public function getMatchingProduct($asins, $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr GetMatchingProduct call. $this->_cr");
        }

        if (!is_array($asins) || !count($asins)) {
            return (null);
        }

        $params = array();
        $params['Action'] = 'GetMatchingProduct';
        $params['MarketplaceId'] = $this->mpid;

        $API = 'Products';

        $i = 0;

        foreach ($asins as $asin) {
            $i++;
            $params['ASINList.ASIN.'.$i] = $asin;
        }


        $xml = $this->simpleCallWS($params, $API, $returnXML);

        if (isset($xml->GetMatchingProductResult)) {
            if ($returnXML) {
                return ($xml);
            } else {
                return (true);
            }
        }

        return ($xml);
    }


    /**
     * @param $relationShip
     *
     * @return array
     */
    public function updateRelationships($relationShip)
    {
        if ($this->_debug) {
            printf("$this->_cr updateRelationships() function. %s $this->_cr", $this->_caller());
        }

        $Submissions = null;
        $SubmitIDs = array();

        $Submissions = $this->productRelationship($relationShip);

        if (isset($Submissions) && is_object($Submissions)) {
            if ($this->_debug) {
                printf("$this->_cr updateRelationships() function. productRelationship returned : %s $this->_cr", nl2br(print_r($Submissions->SubmitFeedResult, true)));
            }

            $SubmitIDs['relations'] = (string)$Submissions->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }
        if ($this->_debug) {
            printf("$this->_cr updateRelationships() function. Returns: %s $this->_cr", nl2br(print_r($SubmitIDs, true)));
        }

        return ($SubmitIDs);
    }

    /**
     *
     * @param <type> $relations - object array
     * @return mixed - FeedSubmissionID or false (if an error occurred)
     */
    public function productRelationship($relations)
    {
        if ($this->_debug) {
            printf("$this->_cr productRelationship() call, checking input arguments ...  $this->_cr");
        }

        //checking input arguments
        $i = 0;
        if ($relations) {
            foreach ($relations as $id_product => $Relationship) {
                if ($this->_debug) {
                    printf("$this->_cr productRelationship() function. \$products[$i] checking...  $this->_cr");
                }
                $SKUValue = $Relationship['parent'];

                if (!$this->checkSKU($SKUValue)) {
                    if ($this->_debug) {
                        printf("$this->_cr productRelationship() function. $this->_att Warning. \$products[$i]['SKU'] = $SKUValue is incorrect. Please verify this... Function skips this product and continue excution.  $this->_cr");
                    }

                    $relations[$id_product] = null;
                    continue;
                }

                foreach ($Relationship['children'] as $key => $ChildSKU) {
                    if (!$this->checkSKU($ChildSKU)) {
                        if ($this->_debug) {
                            printf("$this->_cr productRelationship() function. $this->_att Warning. \$ChildSKU = $ChildSKU is incorrect. Please verify this... Function skips this product and continue excution.  $this->_cr");
                        }

                        $relations[$id_product]['children'][$key] = null;
                        continue;
                    }
                }
                $i++;
            }
        }

        $Document = new DOMDocument();
        $Messages = array();

        if ($this->_debug) {
            printf("$this->_cr  productRelationship() function. Creation of the QuantityMessages starts here  $this->_cr");
        }

        $m = 0;
        if ($relations) {
            foreach ($relations as $id_product => $Relationship) {
                if ($Relationship === null) {
                    if ($this->_debug) {
                        printf("$this->_cr  productRelationship() function. Incorrect product. Creation of the Messages skipped  $this->_cr");
                    }

                    continue;
                }
                $SKUValue = $Relationship['parent'];
                $ChildrenSKUs = array();

                foreach ($Relationship['children'] as $key => $ChildSKU) {
                    if (!$ChildSKU) {
                        continue;
                    }

                    $ChildrenSKUs[] = $ChildSKU;
                }

                if ($ChildrenSKUs) {
                    $Messages[$m] = $this->createProductRelationshipMessage($Document, $SKUValue, $ChildrenSKUs, $m + 1);
                    $m++;
                }
            }
        }

        if (!is_array($Messages) || !count($Messages)) {
            if ($this->_debug) {
                printf("$this->_cr productRelationship() no relations $this->_cr");
            }

            return (true);
        }

        $feedDOM = $this->CreateFeed($Document, 'Relationship', $Messages);

        if ($this->_debug) {
            $feedDOM->formatOutput = true;
        }

        $feed = $feedDOM->saveXML();

        $data = $this->processFeed('_POST_PRODUCT_RELATIONSHIP_DATA_', $feed);

        if ($this->_debug) {
            echo $this->htmlTag('pre', array('class' => 'amazon-xml')) . htmlentities($feed) . $this->htmlTag('/pre');
            if ($data === false || $data === null) {
                printf("$this->_cr productRelationship() function is finished $this->_att with an error $this->_cr");
            } else {
                printf("$this->_cr productRelationship() function is finished successfully here $this->_cr");
            }
        }

        return $data;
    }

    /**
     * @param $SKU
     *
     * @return bool
     */
    private function checkSKU($SKU)
    {
        return ($SKU != null && Tools::strlen($SKU) && preg_match('/[\x00-\xFF]{1,40}/', $SKU) && preg_match('/[^ ]$/', $SKU));
    }

    /**
     * @param DOMDocument $Document
     * @param $SKU
     * @param $ChildrenSKU
     * @param $MessageID
     *
     * @return DOMElement
     */
    private function createProductRelationshipMessage(DOMDocument $Document, $SKU, $ChildrenSKU, $MessageID)
    {
        switch ($this->_operationMode) {
            case self::OPERATIONS_CREATE:
            case self::OPERATIONS_UPDATE:
                $updatingType = 'Update';
                break;
            case self::OPERATIONS_DELETE:
                $updatingType = 'Delete';
                break;
            default:
                echo "$this->_cr createProductRelationshipMessage call. undefined value for updatingType . $this->_cr";

                return (false);
        }
        if ($this->_debug) {
            printf("$this->_cr createProductRelationshipMessage call \$SKU = $SKU \$ChildrenSKU = %s \$MessageID = $MessageID $this->_cr", print_r($ChildrenSKU, true));
        }

        if ($this->_debug) {
            $Document->formatOutput = true;
        }

        $Message = $Document->createElement('Message');
        $MessageIDX = $Document->createElement('MessageID');
        $Message->appendChild($MessageIDX);
        $MessageIDText = $Document->createTextNode($MessageID);
        $MessageIDX->appendChild($MessageIDText);
        $OperationType = $Document->createElement('OperationType', $updatingType);
        $Message->appendChild($OperationType);


        $Relationship = $Document->createElement('Relationship');
        $Message->appendChild($Relationship);
        $SKUX = $Document->createElement('ParentSKU', $SKU);
        $Relationship->appendChild($SKUX);

        foreach ($ChildrenSKU as $ChildSKU) {
            $Relation = $Document->createElement('Relation');
            $Relationship->appendChild($Relation);

            $RelationSKU = $Document->createElement('SKU', $ChildSKU);
            $Relation->appendChild($RelationSKU);

            $RelationType = $Document->createElement('Type', 'Variation');
            $Relation->appendChild($RelationType);
        }

        $xml = $Document->saveXML($Message);

        if ($this->_debug) {
            $XML = new DOMDocument();
            $XML->loadXML($xml);
            $XML->formatOutput = true;

            echo $this->htmlTag('pre', array('class' => 'amazon-xml')) . htmlentities($XML->saveXML()) . $this->htmlTag('/pre');
        }

        return $Message;
    }

    /**
     * @param DOMDocument $Document
     * @param $MessageType
     * @param $Messages
     * @param bool $PurgeAndReplace
     *
     * @return DOMDocument
     */
    private function CreateFeed(DOMDocument $Document, $MessageType, $Messages, $PurgeAndReplace = false)
    {
        if ($this->_debug) {
            printf("$this->_cr CreateFeed() call $this->_cr");
            $Document->formatOutput = true;
        }

        $FeedXmlDocument = $Document;
        $FeedXmlRootElement = $FeedXmlDocument->createElement('AmazonEnvelope');
        $FeedXmlRootElement->setAttribute('xsi:noNamespaceSchemaLocation', 'amzn-envelope.xsd');
        $FeedXmlRootElement->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $FeedXmlDocument->appendChild($FeedXmlRootElement);
        $header = $FeedXmlDocument->createElement('Header');
        $DocumentVersion = $FeedXmlDocument->createElement('DocumentVersion');
        $header->appendChild($DocumentVersion);
        $DocumentVersionText = $FeedXmlDocument->createTextNode('1.01');
        $DocumentVersion->appendChild($DocumentVersionText);
        $FeedXmlRootElement->appendChild($header);
        $MerchantIdentifier = $FeedXmlDocument->createElement('MerchantIdentifier');
        $header->appendChild($MerchantIdentifier);
        $MerchantIdentifierText = $FeedXmlDocument->createTextNode($this->mid);
        $MerchantIdentifier->appendChild($MerchantIdentifierText);
        $MessageTypeX = $FeedXmlDocument->createElement('MessageType');
        $FeedXmlRootElement->appendChild($MessageTypeX);
        $MessageTypeText = $FeedXmlDocument->createTextNode($MessageType);
        $MessageTypeX->appendChild($MessageTypeText);

        if ($PurgeAndReplace) {
            $PurgeAndReplace = $FeedXmlDocument->createElement('PurgeAndReplace');
            $FeedXmlRootElement->appendChild($PurgeAndReplace);
            $PurgeAndReplace->appendChild($FeedXmlDocument->createTextNode('true'));
        }
        $message_count = is_array($Messages) ? count($Messages) : 0;
        for ($i = 0; $i < $message_count; $i++) {
            $FeedXmlRootElement->appendChild($Messages[$i]);
        }

        return $FeedXmlDocument;
    }

    /**
     * @param $feedType
     * @param $feedContent
     *
     * @return bool|SimpleXMLElement
     */
    private function processFeed($feedType, $feedContent)
    {
        if ($this->_debug) {
            print("$this->_cr processFeed() function started.  $this->_cr Now we send following request to the WebService: $this->_cr \$feedType : $feedType $this->_cr \$feedContent:    $this->_cr $feedContent  $this->_cr");
        }

        $feedHandle = @fopen('php://temp', 'rw+');
        fwrite($feedHandle, $feedContent);
        rewind($feedHandle);

        $parameters = array(
            'Action' => 'SubmitFeed',
            'MarketplaceIdList.Id.1' => $this->mpid,
            //'Marketplace' => $this->mpid,
            'Merchant' => $this->mid,
            'FeedType' => $feedType,
            'SignatureVersion' => 2,
            'SignatureMethod' => 'HmacSHA256',

        );

        // Amazon Europe
        //
        if (isset($this->MarketPlaces) && is_array($this->MarketPlaces) && count($this->MarketPlaces)) {
            $i = 0;

            foreach ($this->MarketPlaces as $marketPlace) {
                $i++;
                $parameters['MarketplaceIdList.Id.'.$i] = $marketPlace;
            }
        }

        //$response = $this->_callWSs('Feeds', $parameters, $feedContent);
        $response = $this->simpleCallWS($parameters, 'Feeds', true, $feedContent);

        if ($response === false) {
            if ($this->_debug) {
                printf("response is false: %s $this->_cr", $this->_caller());
            }

            return false;
        } else {
            if ($this->_debug) {
                printf("response is true: %s $this->_cr", nl2br(print_r($response, true)));
            }
        }

        return ($response);
    }

    /**
     * @param $WSTR
     * @param $params
     * @param null $feedContent
     * @param bool $returnXML
     *
     * @return bool|SimpleXMLElement
     */
    public function _callWSs($WSTR, $params, $feedContent = null, $returnXML = false)
    {
        if ($this->_debug) {
            printf("$this->_cr _callWSs() fonction starts here $this->_cr");
        }

        $contentMD5 = null;
        $handle = null;

        if (!($feedContent === null)) {
            if ($this->_debug) {
                printf("$this->_cr We have feed content. It seems, that this operation is to submit feed. Calculating MD5 for this feed... $this->_cr");
            }

            $contentMD5 = base64_encode(md5($feedContent, true));//TODO: Validation - API Requirement
            if ($this->_debug) {
                printf("$this->_cr Now we start to work we handle to upload data to the server $this->_cr");
            }

            $handle = @fopen('php://temp', 'rw+');
            fwrite($handle, $feedContent);
            rewind($handle);
        }

        $method = 'POST';

        $host = $this->_endPointURL();
        $uri = '/';

        // additional parameters
        $params['AWSAccessKeyId'] = $this->awsak;

        if ($this->tk) {
            $params['MWSAuthToken'] = $this->tk;
        }

        // GMT timestamp
        $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');

        $header = null;

        switch ($WSTR) {
            case 'Feeds':
                $header = array(
                    'Expect: ',
                    'Accept: ',
                    'Transfer-Encoding: chunked',
                    'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
                    'Content-MD5: '.$contentMD5
                );
                // Nobreak
            case 'Reports':
            case 'ReportsDownload':
                $params['Version'] = '2009-01-01';
                $uri = '/';
                break;
            case 'Orders':
                $params['Version'] = '2013-09-01';
                $uri = '/Orders';
                break;
            case 'Products':
                $params['Version'] = '2011-10-01';
                $uri = '/Products';
                break;
            case 'FulfillmentInventory':
                $params['Version'] = '2010-10-01';
                $uri = '/FulfillmentInventory';
                break;
            case 'FulfillmentOutboundShipment':
                $params['Version'] = '2010-10-01';
                $uri = '/FulfillmentOutboundShipment';
                break;
            default:
                if ($this->_debug) {
                    printf('ERROR! Wrong Service'.$this->_cr);
                }

                return (false);
        }

        if (!isset($params['SignatureVersion'])) {
            $params['SignatureVersion'] = '2';
        }

        if (!isset($params['SignatureMethod'])) {
            $params['SignatureMethod'] = 'HmacSHA256';
        }

        if (!isset($params['SellerId'])) {
            $params['SellerId'] = $this->mid;
        }

        // sort the parameters
        ksort($params);

        // create the canonicalized query
        $canonicalized_query = array();

        foreach ($params as $param => $value) {
            $param = str_replace('%7E', '~', rawurlencode($param));
            $value = str_replace('%7E', '~', rawurlencode($value));
            $canonicalized_query[] = $param.'='.$value;
        }

        $canonicalized_query = implode('&', $canonicalized_query);

        // create the string to sign
        $string_to_sign = $method.Amazon::LF.$host.Amazon::LF.$uri.Amazon::LF.$canonicalized_query;

        // calculate HMAC with SHA256 and base64-encoding
        $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $this->sk, true));//TODO: Validation: API Requirement

        // encode the signature for the request
        $signature = str_replace('%7E', '~', rawurlencode($signature));

        $curlOptions = array(
            CURLOPT_POST => true,
            CURLOPT_USERAGENT => 'Common-Services/Amazon Marketplace/'.AMAZON_MARKETPLACE_VERSION.' (Language=PHP/'.phpversion().')',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_DNS_CACHE_TIMEOUT => 5,
        );

        $disable_ssl_check = (bool)Configuration::get('AMAZON_DISABLE_SSL_CHECK');

        if (!$disable_ssl_check) {
            $cert = AmazonCertificates::getCertificate();

            $curlOptions[CURLOPT_SSL_VERIFYHOST] = 2;
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = 1;
            $curlOptions[CURLOPT_CAINFO] = $cert;
            $curlOptions[CURLOPT_CAPATH] = dirname($cert);
        } else {
            $curlOptions[CURLOPT_SSL_VERIFYHOST] = 0;
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = 0;
        }

        if (in_array($WSTR, array('Feeds'))) {
            $curlOptions[CURLOPT_HTTPHEADER] = $header;

            if ($handle) {
                $curlOptions[CURLOPT_INFILE] = $handle;
            }

            #2014-07-18 / To verify / O.B.
            #else
            #    $curlOptions[CURLOPT_POSTFIELDS] = '' ;  // 2014-06-24 http://www.milk-hub.net/blog/2008/08/26/curl_error_26

            $curlOptions[CURLOPT_URL] = $url = 'https://'.$host.$uri.'?'.$canonicalized_query.'&Signature='.$signature;

            if (self::$mwsops_active) {
                $curlOptions[CURLOPT_URL] = sprintf('%s/post%s?uri=%s', self::$mwsops, self::$mwsops_version, base64_encode($url));
            }
        } else {
            $curlOptions[CURLOPT_URL] = $url = 'https://'.$host.$uri;
            if (self::$mwsops_active) {
                $curlOptions[CURLOPT_URL] = sprintf('%s/post%s?uri=%s', self::$mwsops, self::$mwsops_version, base64_encode($url));
            }
            $curlOptions[CURLOPT_POSTFIELDS] = $canonicalized_query.'&Signature='.$signature;

            if ($WSTR === 'ReportsDownload') {
                $this->responseBodyContents = @fopen('php://memory', 'rw+');
                $this->headerContents = @fopen('php://memory', 'rw+');
                $this->errorResponseBody = @fopen('php://memory', 'rw+');

                $curlOptions[CURLOPT_WRITEFUNCTION] = array($this, 'responseCallback');
                $curlOptions[CURLOPT_HEADERFUNCTION] = array($this, 'headerCallback');
            }
        }

        if ($this->_debug) {
            echo "$this->_cr Request is: ".$curlOptions[CURLOPT_URL]."$this->_cr";
        }

        if ($this->_debug) {
            $curlOptions[CURLOPT_VERBOSE] = true;
        }

        $this->curlHandle = curl_init();

        curl_setopt_array($this->curlHandle, $curlOptions);


        if ($this->_debug) {
            $verbose = true;
        } else {
            $verbose = false;
        }

        if ($verbose) {
            curl_setopt($this->curlHandle, CURLOPT_VERBOSE, true);

            $verbose = fopen('php://temp', 'w+');
            curl_setopt($this->curlHandle, CURLOPT_STDERR, $verbose);
        }

        if (defined('CURLOPT_ENCODING')) {
            $header[] = 'Accept-Encoding: gzip,deflate';
            curl_setopt($this->curlHandle, CURLOPT_ENCODING, 'gzip');
        }

        if ($this->displayXML) {
            $XML = new DOMDocument();
            $XML->loadXML($feedContent);
            $XML->formatOutput = true;

            echo $this->htmlTag('b', array('class' => 'amazon-url')) . $curlOptions[CURLOPT_URL] . $this->htmlTag('/b');
            echo $this->htmlTag('pre', array('class' => 'amazon-xml')) . htmlentities($XML->saveXML()) . $this->htmlTag('/pre');
        }

        if ($this->MWS_Action == self::MWS_SEND) {
            // Send Request
            $result = curl_exec($this->curlHandle);
        } else {
            // Simulate true result
            $result = true;
        }

        if ($verbose) {
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);

            echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
        }
        if ($this->_debug) {
            CommonTools::p(sprintf("%s/s%s _callWSs", basename(__FILE__), __LINE__));
            CommonTools::p("URL:".$curlOptions[CURLOPT_URL]);
            CommonTools::p("Headers:".print_r($header, true));
            CommonTools::p("Options:".print_r($curlOptions, true));
            CommonTools::p($this->_caller());
            CommonTools::p("Params: ");
            CommonTools::p($params);
            CommonTools::p("CURL Data: ");
            CommonTools::p(htmlentities($result));
            CommonTools::p(curl_getinfo($this->curlHandle));
            CommonTools::p('cURL error number:'.curl_errno($this->curlHandle));
            CommonTools::p('cURL error:'.curl_error($this->curlHandle));
        }

        if ($WSTR === 'Feeds' && $handle) {
            //
            fclose($handle);
        }
        if ($WSTR === 'ReportsDownload') {
            rewind($this->headerContents);
            $header = stream_get_contents($this->headerContents);
            //$this->parseHttpHeader($header);

            rewind($this->responseBodyContents);
            $content = stream_get_contents($this->responseBodyContents);

            @fclose($this->headerContents);
            @fclose($this->errorResponseBody);

            if ($this->_debug) {
                printf("$this->_cr Request completed successfully. Function returns Text element. Response is $this->_cr $content $this->_cr");
            }

            return $content;
        }

        curl_close($this->curlHandle);

        if ($this->MWS_Action == self::MWS_DO_NOT_SEND) {
            // Do NOT send to amazon
            return (true);
        }

        if ($result === false) {
            if ($this->_debug) {
                CommonTools::p(sprintf("%s/%s _callWSs ERROR $this->_att", basename(__FILE__), __LINE__));
                CommonTools::p($this->_caller());
                CommonTools::p("Params: ");
                CommonTools::p($params);
                CommonTools::p("CURL Data: ");
                CommonTools::p(htmlentities($result));
                CommonTools::p(curl_getinfo($this->curlHandle));
                CommonTools::p('cURL error number:'.curl_errno($this->curlHandle));
                CommonTools::p('cURL error:'.curl_error($this->curlHandle));
            }

            return false;
        }

        // No XML data
        //
        if ($result && $params['Action'] == 'GetReport') {
            if ($this->_debug) {
                printf('Data is not XML');
            }

            return ($result);
        }

        try {
            $pxml = new SimpleXMLElement($result);
        } catch (Exception $e) {
            CommonTools::p('Exception Caught: '.$e->getMessage());

            return false;
        }

        if ($pxml === false) {
            if ($this->_debug) {
                printf('ERROR! no xml (\$pxml === false)');
            }

            return false; // no xml
        }

        if ($returnXML) {
            return ($pxml);
        }

        if ($pxml->getName() === 'ErrorResponse') {
            if ($this->_debug) {
                $r = htmlentities($pxml->saveXML());
                CommonTools::p("WebService returns error response. Function _callWSs() returns false. We have got following error response:");
                CommonTools::p($r);
            }
            self::DisplayXMLError($pxml, $curlOptions[CURLOPT_URL]);

            return false;
        }

        if ($this->_debug) {
            $r = htmlentities($pxml->saveXML());
            CommonTools::p("Request completed successfully. Function returns simpleXml element. Response is");
            CommonTools::p($r);
        }

        return $pxml;
    }

    /**
     * @param $xmle
     * @param bool $query
     */
    public static function DisplayXMLError($xmle, $query = false)
    {
        if (isset($xmle->Error)) {
            echo self::htmlTag('div', array('style' => 'border:1px solid red;margin:10px;padding:10px;'));

            echo self::htmlTag('h1', array('style' => 'color:red')) . 'Error' . self::htmlTag('/h1');

            if ($query) {
                CommonTools::p('Query: '.$query);
            }

            foreach ($xmle->Error->children() as $key => $val) {
                switch ($key) {
                    case 'Type':
                        $color = 'navy';
                        break;
                    case 'Code':
                        $color = 'brown';
                        break;
                    case 'Message':
                        $color = 'red';
                        break;
                    default:
                        $color = 'black';
                        break;
                }
                echo self::htmlTag('span') . "$key:" . self::htmlTag('/span');
                echo self::htmlTag('span', array("style" => "font-weight:bold;color:$color")) . $val . self::htmlTag('/span');
                CommonTools::p("");
            }

            if (isset($xmle->RequestID)) {
                CommonTools::p("Request ID:");
                CommonTools::p($xmle->RequestID);
            }

            echo self::htmlTag('/div');
        } else {
            if ($query) {
                CommonTools::p('Query: '.$query);
            }

            echo nl2br(print_r($xmle->Error, true));
        }
    }


    /**
     * @param $products
     *
     * @return bool|null|string
     */
    public function updatePricesFeed($products)
    {
        if ($this->_debug) {
            printf("$this->_cr updatePricesFeed() function. %s $this->_cr", $this->_caller());
        }

        $Submissions = null;
        $SubmitID = null;

        $Submissions = $this->updatePrices($products);

        if ($Submissions === false) {
            if ($this->_debug) {
                printf("$this->_cr updatePricesFeed() function. $this->_att Error. There are no prices to update. All products are skipped. Function terminated. $this->_cr");
            }

            return false;
        }

        if (isset($Submissions) && is_object($Submissions)) {
            if ($this->_debug) {
                printf("$this->_cr updatePricesFeed() function. updatePrices returned : %s $this->_cr", nl2br(print_r($Submissions[2]->SubmitFeedResult, true)));
            }

            $SubmitID = (string)$Submissions->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }

        if ($this->_debug) {
            printf("$this->_cr updatePricesFeed() function. Returns: %s $this->_cr", nl2br(print_r($SubmitID, true)));
        }

        return ($SubmitID);
    }

    /**
     * @param $products
     * @param null $relationShip
     * @param null $has_product_feed
     * @param null $has_quantity_feed
     * @param null $has_price_feed
     * @param $has_image_feed
     *
     * @return array
     */
    public function updateProducts($products, $relationShip = null, $has_product_feed = null, $has_quantity_feed = null, $has_price_feed = null, $has_image_feed = null)
    {
        if ($this->_debug) {
            printf("$this->_cr updateProducts() function. %s $this->_cr", $this->_caller());
        }

        $Submissions = array();
        $SubmitIDs = array();

        if ($has_product_feed) {
            $Submissions[0] = $this->partiallyUpdateProducts($products);
        } else {
            $Submissions[0] = null;
        }

        if ($has_quantity_feed) {
            $Submissions[1] = $this->updateQuantities($products);
        } else {
            $Submissions[1] = null;
        }

        if ($has_price_feed) {
            $Submissions[2] = $this->updatePrices($products);
        } else {
            $Submissions[2] = null;
        }

        $Submissions[3] = $this->overrideShipping($products);

        if ($has_image_feed) {
            $Submissions[4] = $this->productImage($products);
        } else {
            $Submissions[4] = null;
        }

        if ($relationShip) {
            $Submissions[5] = $this->productRelationship($relationShip);
        }

        if ($Submissions[0] === false && $Submissions[1] === false && $Submissions[2] === false && $Submissions[4] === false) {
            if ($this->_debug) {
                printf("$this->_cr updateProducts() function. $this->_att Error. There are no products to update. All products are skipped. Function terminated. $this->_cr");
            }

            return false;
        }

        if ($has_product_feed && !isset($Submissions[0]->SubmitFeedResult->FeedSubmissionInfo)) {
            if ($this->_debug) {
                printf("$this->_cr updateProducts() function. $this->_att Error. No submission feed data. Return is: %s $this->_cr", nl2br(print_r($Submissions[0], true)));
            }

            return (false);
        } elseif ($has_product_feed && !isset($Submissions[0]->SubmitFeedResult)) {
            if ($this->_debug) {
                printf("$this->_cr updateProducts() function. $this->_att Error. No submission feed info. Return is: %s $this->_cr", nl2br(print_r($Submissions[0], true)));
            }

            return (false);
        }

        if ($has_product_feed && isset($Submissions[0]) && is_object($Submissions[0])) {
            if ($this->_debug) {
                printf("$this->_cr updateProducts() function. partiallyUpdateProducts returned : %s $this->_cr", nl2br(print_r($Submissions[0]->SubmitFeedResult, true)));
            }

            $SubmitIDs['products'] = (string)$Submissions[0]->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }

        if ($has_quantity_feed && isset($Submissions[1]) && is_object($Submissions[1])) {
            if ($this->_debug) {
                printf("$this->_cr updateProducts() function. updateQuantities returned : %s $this->_cr", nl2br(print_r($Submissions[1]->SubmitFeedResult, true)));
            }

            $SubmitIDs['inventory'] = (string)$Submissions[1]->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }
        if ($has_price_feed && isset($Submissions[2]) && is_object($Submissions[2])) {
            if ($this->_debug) {
                printf("$this->_cr updateProducts() function. updatePrices returned : %s $this->_cr", nl2br(print_r($Submissions[2]->SubmitFeedResult, true)));
            }

            $SubmitIDs['prices'] = (string)$Submissions[2]->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }
        if (isset($Submissions[3]) && is_object($Submissions[3])) {
            if ($this->_debug) {
                printf("$this->_cr updateProducts() function. overrideShipping returned : %s $this->_cr", nl2br(print_r($Submissions[3]->SubmitFeedResult, true)));
            }

            $SubmitIDs['overrides'] = (string)$Submissions[3]->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }
        if ($has_image_feed && isset($Submissions[4]) && is_object($Submissions[4])) {
            if ($this->_debug) {
                printf("$this->_cr updateProducts() function. productImage returned : %s $this->_cr", nl2br(print_r($Submissions[4]->SubmitFeedResult, true)));
            }

            $SubmitIDs['images'] = (string)$Submissions[4]->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }
        if ($relationShip && isset($Submissions[5]) && is_object($Submissions[5])) {
            if ($this->_debug) {
                printf("$this->_cr updateProducts() function. productRelationship returned : %s $this->_cr", nl2br(print_r($Submissions[5]->SubmitFeedResult, true)));
            }

            $SubmitIDs['relations'] = (string)$Submissions[5]->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }
        if ($this->_debug) {
            printf("$this->_cr updateProducts() function. Returns: %s $this->_cr", nl2br(print_r($SubmitIDs, true)));
        }

        return ($SubmitIDs);
    }

    /**
     *
     * @param <type> $products - object array:
     * $products[$i]['SKU'] - string, unique product identifier in the seller account. Must be specified.
     * $products[$i]['$ProductIDType'] - [optional] (string) Product Identification type. Must be one of the fellowing:
     *                          "ISBN", "UPC", "EAN", "ASIN", "GTIN"
     *               If $UpdatingType is equal to "Update", this field must be specified.
     * $products[$i]['$ProductIDCode'] - [optional] Product Code, based on the ProductType.
     *                    If specified, $products[$i]['$ProductIDType'] must be also specified
     *                    If null, $products[$i]['$ProductIDType'] must be also null
     *               If $UpdatingType is equal to "Update", this field must be specified.
     * $products[$i]['ConditionType'] - [optional] string, one of the following: "New",
     *      "UsedLikeNew", "UsedVeryGood", "UsedGood", "UsedAcceptable",
     *      "CollectibleLikeNew", "CollectibleVeryGood", "CollectibleGood", "CollectibleAcceptable"
     *      "Refurbished", "Club"
     *
     * $products[$i]['ConditionNote'] - [optional] any string, up to 2000 symbols
     *                if $products[$i]['ConditionNote'] specified, $products[$i]['ConditionType'] must be also specified
     *
     * @param <string> $UpdatingType - [optional] must be equal to "Update" or to "PartialUpdate".
     *         If you do not specify value of this field, this function uses "PartialUpdate" by default
     *
     *         If you use Update, all specified information overwrites any existing information.
     *         Any unspecified information is erased.
     *         If you use PartialUpdate, all specified information overwrites any existing information,
     *         but unspecified information is unaffected.
     * @param bool $ch check flag
     * @return mixed - FeedSubmissionID or false (if an error occurred)
     */
    public function partiallyUpdateProducts($products, $updatingType = null, $ch = true)
    {
        //checking input arguments
        if ($this->_debug) {
            echo "$this->_cr partiallyUpdateProducts call. checking of the input arguments starts here. $this->_cr";
        }

        //updating or partial updating ?
        switch ($this->_operationMode) {
            case self::OPERATIONS_CREATE:
                $updatingType = 'Update';
                break;
            case self::OPERATIONS_UPDATE:
                $updatingType = 'PartialUpdate';
                break;
            case self::OPERATIONS_DELETE:
                $updatingType = 'Delete';
                break;
            default:
                echo "$this->_cr partiallyUpdateProducts call. undefined value for updatingType . $this->_cr";

                return (false);
        }
        if ($ch == true) {
            $count_products = is_array($products) ? count($products) : 0;
            for ($i = 0; $i < $count_products; $i++) {
                $r = $this->partiallyCheckProduct($products[$i]);
                if ($r == false) {
                    $products[$i] = null;
                }
            }
        }

        if ($this->_debug) {
            printf('partiallyUpdateProducts function. Checking input arguments is finished.%s', $this->_cr);
        }

        //if there are no products to add, function will be terminated
        $count_products = is_array($products) ? count($products) : 0;
        for ($i = 0; $i < $count_products; $i++) {
            if ($products[$i] != null) {
                break;
            }
            $count_products = is_array($products) ? count($products) : 0;
            if ($i == $count_products - 1) {
                if ($this->_debug) {
                    printf('partiallyUpdateProducts function. We have no products to add (or they all are skipped). Function is finished with $this->_att an Error.%s', $this->_cr);
                }

                return false;
            }
        }

        $Document = new DOMDocument();
        $Messages = array();

        if ($this->_debug) {
            printf("$this->_cr partiallyUpdateProducts function. Creating messages starts here. $this->_cr");
        }

        $m = 0;
        $count_products = is_array($products) ? count($products) : 0;
        for ($i = 0; $i < $count_products; $i++) {
            if ($this->_debug) {
                printf("$this->_cr partiallyUpdateProducts function. Creating $i-th message starts here $this->_cr");
            }

            if ($products[$i] != null) {
                if (isset($products[$i]['NoProductFeed']) && $products[$i]['NoProductFeed']) {
                    continue;
                }

                $Messages[$m] = $this->createProductMessage($Document, $products[$i]['SKU'], isset($products[$i]['ProductIDType']) ? $products[$i]['ProductIDType'] : '', isset($products[$i]['ProductIDCode']) ? $products[$i]['ProductIDCode'] : '', isset($products[$i]['ConditionType']) ? $products[$i]['ConditionType'] : '', isset($products[$i]['ConditionNote']) ? $products[$i]['ConditionNote'] : null, isset($products[$i]['ProductData']) ? $products[$i]['ProductData'] : '', isset($products[$i]['ProductDescription']) ? $products[$i]['ProductDescription'] : '', $m + 1, $updatingType);

                $m++;
            } else {
                if ($this->_debug) {
                    printf("$this->_cr partiallyUpdateProducts function. $i-th product is incorrect. We skip it. $this->_cr");
                }
            }
        }

        if (is_array($Messages) && count($Messages)) {
            if ($this->_debug) {
                printf("$this->_cr partiallyUpdateProducts function. Messages creating finished. $this->_cr");
            }

            $feedDOM = $this->CreateFeed($Document, 'Product', $Messages);

            if ($this->_debug) {
                $feedDOM->formatOutput = true;
            }

            $feed = $feedDOM->saveXML($feedDOM);

            if ($this->_debug) {
                printf("$this->_cr partiallyUpdateProducts function. Now function starts sending query to the WebService (this->processFeed function calling).$this->_cr");

                echo nl2br(str_replace(' ', '&nbsp;', htmlentities($feed)));
            }
            $data = $this->processFeed('_POST_PRODUCT_DATA_', $feed);

            if ($this->_debug) {
                $state = null;
                if ($data === false || $data === null) {
                    //
                    $state = "with an $this->_att error";
                } else {
                    //
                    $state = 'succesfully';
                }
                printf("$this->_cr partiallyUpdateProducts function. Function execution is completed $state $this->_cr");
            }

            return $data;
        } else {
            return (true);
        }
    }

    /**
     * @param $product
     *
     * @return bool
     */
    private function partiallyCheckProduct($product/*, $updatingType = null*/)
    {
        if ($this->_debug) {
            printf('partiallyCheckProduct() call. Checking product starts here.%s', $this->_cr);
        }
        /*
        //updating or partial updating ?
        switch ($this->_operationMode)
        {
            case self::OPERATIONS_CREATE:
                $updatingType = 'Update';
                break;
            case self::OPERATIONS_UPDATE:
                $updatingType = 'PartialUpdate';
                break;
            case self::OPERATIONS_DELETE:
                $updatingType = 'Delete';
                break;
            default:
                echo "$this->_cr partiallyCheckProduct() - Undefined value for updatingType . $this->_cr";

                return (false);
        }
        */
        if (!$this->checkSKU($product['SKU'])) {
            if ($this->_debug) {
                printf('partiallyCheckProduct() function. %sWarning. product["SKU"] : "%s" invalid value. Skipping the product and continue.%s', $this->_att, $product['SKU'], $this->_cr);
            }

            return false;
        }

        if (isset($product['ProductIDType']) && $product['ProductIDType'] != null && $product['ProductIDCode'] != null) {
            if ($product['ProductIDType'] == null || $product['ProductIDCode'] == null) {
                if ($this->_debug) {
                    $val1 = $product['ProductIDType'];
                    $val2 = $product['ProductIDCode'];
                    printf('partiallyCheckProduct() function.  %sWarning. You specify:%s
                              product["ProductIDType"] : %s %s
                              product["ProductIDCode"] : %s %s
                              If one of that fields specified the other one must be also specified.%s', $this->_att, $this->_cr, $val1, $this->_cr, $val2, $this->_cr);
                }

                return false;
            }
            if (!$this->checkProductIDType($product['ProductIDType'])) {
                if ($this->_debug) {
                    $pridt = $product['ProductIDType'];
                    printf('partiallyCheckProduct() function. %sWarning. product["ProductIDType"] = %s. Incorrect Value. Function skips the product and continue execution. %s', $this->_att, $pridt, $this->_cr);
                }

                return false;
            }
        }
        if (isset($product['ConditionType']) && $product['ConditionType'] != null) {
            if (!$this->checkConditionType($product['ConditionType'])) {
                if ($this->_debug) {
                    $val1 = $product['ConditionType'];
                    printf('partiallyCheckProduct() function. %sWarning
                       products[$i]["ConditionType"] = %s . It is incorrect $this->_cr', $this->_att, $val1, $this->_cr);
                }

                return false;
            }
        }
        if (isset($product['ConditionNote']) && $product['ConditionNote'] != null) {
            if ($product['ConditionType'] == null) {
                if ($this->_debug) {
                    $value = $product['ConditionNote'];
                    printf('partiallyCheckProduct() function. %sWarning. You specify
                             products[$i]["ConditionNote"] = %s, but products[$i]["ConditionType"] is not specified. It is incorrect. Function skips the product and continue execution', $this->_att, $value, $this->_cr);
                }

                return false;
            }
            if (!$this->checkConditionNote($product['ConditionNote'])) {
                if ($this->_debug) {
                    printf('partiallyCheckProduct() function. %sError. product["ConditionNote"] : invalid value.%s', $this->_att, $this->_cr);
                }

                return false;
            }
        }
        if ($this->_debug) {
            printf('partiallyCheckProduct() function successfully finished.', $this->_cr);
        }

        return true;
    }

    /**
     * @param $ProductIDType
     *
     * @return bool
     */
    private function checkProductIDType($ProductIDType)
    {
        return (true);
        /*
        if ($this->_debug)
            printf("$this->_cr  checkProductIDType call \$ProductIDType = $ProductIDType $this->_cr");

        if ($ProductIDType == 'ISBN' || $ProductIDType == 'UPC' || $ProductIDType == 'EAN' || $ProductIDType == 'ASIN' || $ProductIDType == 'GTIN')
        {
            if ($this->_debug)
                printf("$this->_cr  checkProductIDType function is finished successfully. $this->_cr");

            return true;
        }
        if ($this->_debug)
            printf("$this->_cr  checkProductIDType function is finished with $this->_att an error. You sent incorrect input argument.  \$ProductIDType = $ProductIDType . Please verify it $this->_cr");

        return false;
        */
    }

    /**
     * @param $ConditionType
     *
     * @return bool
     */
    private function checkConditionType($ConditionType)
    {
        if ($this->_debug) {
            printf("$this->_cr  checkConditionType call \$ConditionType = $ConditionType $this->_cr");
        }

        if ($ConditionType == 'New' || $ConditionType == 'UsedLikeNew' || $ConditionType == 'UsedVeryGood' || $ConditionType == 'UsedGood' || $ConditionType == 'UsedAcceptable' || $ConditionType == 'CollectibleLikeNew' || $ConditionType == 'CollectibleVeryGood' || $ConditionType == 'CollectibleGood' || $ConditionType == 'CollectibleAcceptable' || $ConditionType == 'Refurbished' || $ConditionType == 'Club') {
            if ($this->_debug) {
                printf("$this->_cr checkConditionType function is finished successfully. $this->_cr");
            }

            return true;
        }
        if ($this->_debug) {
            printf("$this->_cr checkConditionType function is finished with $this->_att an error. You sent incorrect input argument.  \$ConditionType = $ConditionType . Please verify this $this->_cr");
        }

        return false;
    }

    /**
     * @param $ConditionNote
     *
     * @return bool
     */
    private function checkConditionNote($ConditionNote)
    {
        if ($this->_debug) {
            printf("$this->_cr checkConditionNote call.$this->_cr");
        }

        if (Tools::strlen($ConditionNote) < 2000) {
            if ($this->_debug) {
                printf("$this->_cr checkConditionNote function is finished successfully. $this->_cr");
            }

            return true;
        }
        if ($this->_debug) {
            printf("$this->_cr checkConditionNote function is finished $this->_att with an error. Input argument: \$ConditionNote = $ConditionNote $this->_cr");
        }

        return false;
    }

    /**
     * @param DOMDocument $Document
     * @param $SKU
     * @param $ProductIDType
     * @param $ProductIDCode
     * @param $ConditionType
     * @param $ConditionNote
     * @param $ProductData
     * @param $ProductDescription
     * @param $MessageID
     * @param null $updatingType
     *
     * @return DOMElement
     */
    private function createProductMessage(DOMDocument $Document, $SKU, $ProductIDType, $ProductIDCode, $ConditionType, $ConditionNote, $ProductData, $ProductDescription, $MessageID, $updatingType = null)
    {
        if ($this->_debug) {
            printf("$this->_cr createProductMessage call \$SKU = $SKU \$ProductIDType = $ProductIDType \$ConditionType = $ConditionType \$MessageID = $MessageID \$updatingType = $updatingType $this->_cr");
        }

        //updating or partial updating ?
        switch ($this->_operationMode) {
            case self::OPERATIONS_CREATE:
                $updatingType = 'Update';
                break;
            case self::OPERATIONS_UPDATE:
                $updatingType = 'PartialUpdate';
                break;
            case self::OPERATIONS_DELETE:
                $updatingType = 'Delete';
                break;

            default:
                echo "$this->_cr partiallyUpdateProducts call. undefined value for updatingType . $this->_cr";

                return (false);
        }

        $Message = $Document->createElement('Message');
        $MessageIDX = $Document->createElement('MessageID');
        $Message->appendChild($MessageIDX);
        $MessageIDText = $Document->createTextNode($MessageID);
        $MessageIDX->appendChild($MessageIDText);
        $OperationType = $Document->createElement('OperationType');
        $Message->appendChild($OperationType);
        $OperationTypeText = $Document->createTextNode($updatingType);
        $OperationType->appendChild($OperationTypeText);
        $Product = $Document->createElement('Product');
        $Message->appendChild($Product);
        $SKUX = $Document->createElement('SKU');
        $Product->appendChild($SKUX);
        $SKUText = $Document->createTextNode($SKU);
        $SKUX->appendChild($SKUText);
        if ($ProductIDType != null && $ProductIDCode != null) {
            $StandardProductID = $Document->createElement('StandardProductID');
            $Product->appendChild($StandardProductID);
            $Type = $Document->createElement('Type');
            $StandardProductID->appendChild($Type);
            $TypeText = $Document->createTextNode($ProductIDType);
            $Type->appendChild($TypeText);
            $Value = $Document->createElement('Value');
            $StandardProductID->appendChild($Value);
            $ValueText = $Document->createTextNode($ProductIDCode);
            $Value->appendChild($ValueText);
        }

        // 2018-05-29 Adding ItemPackageQuantity
        //
        if ($ProductData && isset($ProductData['ProductTaxCode'])) {
            $ProductTaxCodeTag = $Document->createElement('ProductTaxCode');
            $ProductTaxCodeTag->appendChild($Document->createTextNode($ProductData['ProductTaxCode']));
            $Product->appendChild($ProductTaxCodeTag);
        }

        if ($ConditionType != null) {
            $ConditionX = $Document->createElement('Condition');
            $Product->appendChild($ConditionX);
            $ConditionTypeX = $Document->createElement('ConditionType');
            $ConditionX->appendChild($ConditionTypeX);
            $ConditionTypeText = $Document->createTextNode($ConditionType);
            $ConditionTypeX->appendChild($ConditionTypeText);
            if ($ConditionNote != null) {
                $ConditionNoteX = $Document->createElement('ConditionNote');
                $ConditionX->appendChild($ConditionNoteX);
                $ConditionNoteText = $Document->createTextNode($ConditionNote);
                $ConditionNoteX->appendChild($ConditionNoteText);
            }
        }

        // 2013-03-29 Adding ItemPackageQuantity
        //
        if ($ProductData && isset($ProductData['ItemPackageQuantity'])) {
            $ItemPackageQuantityTag = $Document->createElement('ItemPackageQuantity');
            $ItemPackageQuantityTag->appendChild($Document->createTextNode($ProductData['ItemPackageQuantity']));
            $Product->appendChild($ItemPackageQuantityTag);
        }


        // 2014-02-26 Adding NumberOfItems
        //
        if ($ProductData && isset($ProductData['NumberOfItems'])) {
            $NumberOfItemsTag = $Document->createElement('NumberOfItems');
            $NumberOfItemsTag->appendChild($Document->createTextNode($ProductData['NumberOfItems']));
            $Product->appendChild($NumberOfItemsTag);
        }

        // Send Product Informations
        //
        if (is_array($ProductDescription)) {
            $DescriptionDataTag = $this->createDescriptionDataNode($Document, $ProductData);

            if ($DescriptionDataTag == null) {
                $DescriptionDataTag = $Document->createElement('DescriptionData');
            }
            if (isset($ProductDescription['Title'])) {
                $TitleTag = $Document->createElement('Title');
                $DescriptionDataTag->appendChild($TitleTag);

                $TitleText = $Document->createTextNode($ProductDescription['Title']);
                $TitleTag->appendChild($TitleText);
            }

            if (isset($ProductDescription['Brand']) && !empty($ProductDescription['Brand'])) {
                $BrandTag = $Document->createElement('Brand');
                $DescriptionDataTag->appendChild($BrandTag);
                $BrandText = $Document->createTextNode($ProductDescription['Brand']);
                $BrandTag->appendChild($BrandText);
            }

            if (isset($ProductDescription['Title']) && isset($ProductDescription['Description'])) {
                $DescriptionTag = $Document->createElement('Description');
                $DescriptionDataTag->appendChild($DescriptionTag);

                $DescriptionText = $Document->createTextNode($ProductDescription['Description']);
                $DescriptionTag->appendChild($DescriptionText);
            }


            if (isset($ProductDescription['BulletPoint']) && is_array($ProductDescription['BulletPoint'])) {
                $count = 1;
                foreach ($ProductDescription['BulletPoint'] as $BulletPoint) {
                    if ($count > 5) {
                        break;
                    }

                    $BulletPointTag = $Document->createElement('BulletPoint');
                    $BulletPointTag->appendChild($Document->createTextNode($BulletPoint));

                    $DescriptionDataTag->appendChild($BulletPointTag);
                    $count++;
                }
            }
            if (isset($ProductDescription['ItemDimensions']) && is_array($ProductDescription['ItemDimensions'])) {
                $ItemDimensionsTag = $Document->createElement('ItemDimensions');
                $DescriptionDataTag->appendChild($ItemDimensionsTag);

                if (isset($ProductDescription['ItemDimensions']['Length']['value'])) {
                    $ItemDimensionsLengthTag = $Document->createElement('Length');
                    $ItemDimensionsTag->appendChild($ItemDimensionsLengthTag);

                    $ItemDimensionsLengthTag->appendChild($Document->createTextNode($ProductDescription['ItemDimensions']['Length']['value']));
                    $ItemDimensionsLengthTag->setAttribute('unitOfMeasure', $ProductDescription['ItemDimensions']['Length']['unitOfMeasure']);
                }

                if (isset($ProductDescription['ItemDimensions']['Width']['value'])) {
                    $ItemDimensionsWidthTag = $Document->createElement('Width');
                    $ItemDimensionsTag->appendChild($ItemDimensionsWidthTag);

                    $ItemDimensionsWidthTag->appendChild($Document->createTextNode($ProductDescription['ItemDimensions']['Width']['value']));
                    $ItemDimensionsWidthTag->setAttribute('unitOfMeasure', $ProductDescription['ItemDimensions']['Width']['unitOfMeasure']);
                }

                if (isset($ProductDescription['ItemDimensions']['Height']['value'])) {
                    $ItemDimensionsHeightTag = $Document->createElement('Height');
                    $ItemDimensionsTag->appendChild($ItemDimensionsHeightTag);

                    $ItemDimensionsHeightTag->appendChild($Document->createTextNode($ProductDescription['ItemDimensions']['Height']['value']));
                    $ItemDimensionsHeightTag->setAttribute('unitOfMeasure', $ProductDescription['ItemDimensions']['Height']['unitOfMeasure']);
                }

                if (isset($ProductDescription['ItemDimensions']['Weight']['value'])) {
                    $ItemDimensionsWeightTag = $Document->createElement('Weight');
                    $ItemDimensionsTag->appendChild($ItemDimensionsWeightTag);

                    $ItemDimensionsWeightTag->appendChild($Document->createTextNode($ProductDescription['ItemDimensions']['Weight']['value']));
                    $ItemDimensionsWeightTag->setAttribute('unitOfMeasure', $ProductDescription['ItemDimensions']['Weight']['unitOfMeasure']);
                }
            }

            if (isset($ProductDescription['PackageDimensions']) && is_array($ProductDescription['PackageDimensions'])) {
                $PackageDimensionsTag = $Document->createElement('PackageDimensions');
                $DescriptionDataTag->appendChild($PackageDimensionsTag);

                if (isset($ProductDescription['PackageDimensions']['Length']['value'])) {
                    $PackageDimensionsLengthTag = $Document->createElement('Length');
                    $PackageDimensionsTag->appendChild($PackageDimensionsLengthTag);

                    $PackageDimensionsLengthTag->appendChild($Document->createTextNode($ProductDescription['PackageDimensions']['Length']['value']));
                    $PackageDimensionsLengthTag->setAttribute('unitOfMeasure', $ProductDescription['PackageDimensions']['Length']['unitOfMeasure']);
                }

                if (isset($ProductDescription['PackageDimensions']['Width']['value'])) {
                    $PackageDimensionsWidthTag = $Document->createElement('Width');
                    $PackageDimensionsTag->appendChild($PackageDimensionsWidthTag);

                    $PackageDimensionsWidthTag->appendChild($Document->createTextNode($ProductDescription['PackageDimensions']['Width']['value']));
                    $PackageDimensionsWidthTag->setAttribute('unitOfMeasure', $ProductDescription['PackageDimensions']['Width']['unitOfMeasure']);
                }

                if (isset($ProductDescription['PackageDimensions']['Height']['value'])) {
                    $PackageDimensionsHeightTag = $Document->createElement('Height');
                    $PackageDimensionsTag->appendChild($PackageDimensionsHeightTag);

                    $PackageDimensionsHeightTag->appendChild($Document->createTextNode($ProductDescription['PackageDimensions']['Height']['value']));
                    $PackageDimensionsHeightTag->setAttribute('unitOfMeasure', $ProductDescription['PackageDimensions']['Height']['unitOfMeasure']);
                }

                if (isset($ProductDescription['PackageDimensions']['Weight']['value'])) {
                    $PackageDimensionsWeightTag = $Document->createElement('Weight');
                    $PackageDimensionsTag->appendChild($PackageDimensionsWeightTag);

                    $PackageDimensionsWeightTag->appendChild($Document->createTextNode($ProductDescription['PackageDimensions']['Weight']['value']));
                    $PackageDimensionsWeightTag->setAttribute('unitOfMeasure', $ProductDescription['PackageDimensions']['Weight']['unitOfMeasure']);
                }
            }

            if (isset($ProductDescription['PackageWeight']) && !empty($ProductDescription['PackageWeight'])) {
                $PackageWeightTag = $Document->createElement('PackageWeight', $ProductDescription['PackageWeight']);
                $PackageWeightTag->setAttribute('unitOfMeasure', $ProductDescription['PackageWeightUnit']);
                $DescriptionDataTag->appendChild($PackageWeightTag);
            }

            if (isset($ProductDescription['ShippingWeight']) && !empty($ProductDescription['ShippingWeight'])) {
                $PackageWeightTag = $Document->createElement('ShippingWeight', $ProductDescription['ShippingWeight']);
                $PackageWeightTag->setAttribute('unitOfMeasure', $ProductDescription['ShippingWeightUnit']);
                $DescriptionDataTag->appendChild($PackageWeightTag);
            }

            if (isset($ProductDescription['MerchantCatalogNumber']) && !empty($ProductDescription['MerchantCatalogNumber'])) {
                $MerchantCatalogNumber = $Document->createElement('MerchantCatalogNumber');
                $MerchantCatalogNumber->appendChild($Document->createTextNode(mb_substr($ProductDescription['MerchantCatalogNumber'], 0, 40)));
                $DescriptionDataTag->appendChild($MerchantCatalogNumber);
            }

            if (isset($ProductDescription['Manufacturer']) && !empty($ProductDescription['Manufacturer'])) {
                $ManufacturerTag = $Document->createElement('Manufacturer');
                $DescriptionDataTag->appendChild($ManufacturerTag);
                $ManufacturerText = $Document->createTextNode($ProductDescription['Manufacturer']);
                $ManufacturerTag->appendChild($ManufacturerText);
            }

            if (isset($ProductDescription['MfrPartNumber']) && !empty($ProductDescription['MfrPartNumber'])) {
                $MfrPartNumberTag = $Document->createElement('MfrPartNumber');
                $DescriptionDataTag->appendChild($MfrPartNumberTag);
                $MfrPartNumberText = $Document->createTextNode($ProductDescription['MfrPartNumber']);
                $MfrPartNumberTag->appendChild($MfrPartNumberText);
            }

            // Added : 2013/05/15
            //
            if (isset($ProductDescription['SearchTerms'])) {
                if (is_array($ProductDescription['SearchTerms']) && count($ProductDescription['SearchTerms'])) {
                    foreach ($ProductDescription['SearchTerms'] as $searchTerms) {
                        $SearchTermsTag = $Document->createElement('SearchTerms');
                        $DescriptionDataTag->appendChild($SearchTermsTag);
                        $SearchTermsText = $Document->createTextNode($searchTerms);
                        $SearchTermsTag->appendChild($SearchTermsText);
                    }
                } elseif (is_string($ProductDescription['SearchTerms']) && !empty($ProductDescription['SearchTerms'])) {
                    $SearchTermsTag = $Document->createElement('SearchTerms');
                    $DescriptionDataTag->appendChild($SearchTermsTag);
                    $SearchTermsText = $Document->createTextNode($ProductDescription['SearchTerms']);
                    $SearchTermsTag->appendChild($SearchTermsText);
                }
            }

            // Added : 2014/03/13
            //
            if (isset($ProductDescription['ItemType']) && !empty($ProductDescription['ItemType'])) {
                $ItemTypeTag = $Document->createElement('ItemType');
                $DescriptionDataTag->appendChild($ItemTypeTag);
                $ItemTypeText = $Document->createTextNode($ProductDescription['ItemType']);
                $ItemTypeTag->appendChild($ItemTypeText);
            }

            // Added : 2014/10/15
            //
            if (isset($ProductDescription['IsGiftWrapAvailable'])) {
                $IsGiftWrapAvailableTag = $Document->createElement('IsGiftWrapAvailable');
                $DescriptionDataTag->appendChild($IsGiftWrapAvailableTag);
                $IsGiftWrapAvailableText = $Document->createTextNode($ProductDescription['IsGiftWrapAvailable'] ? 'true' : 'false');
                $IsGiftWrapAvailableTag->appendChild($IsGiftWrapAvailableText);

                $IsGiftMessageAvailableTag = $Document->createElement('IsGiftMessageAvailable');
                $DescriptionDataTag->appendChild($IsGiftMessageAvailableTag);
                $IsGiftMessageAvailableText = $Document->createTextNode($ProductDescription['IsGiftMessageAvailable'] ? 'true' : 'false');
                $IsGiftMessageAvailableTag->appendChild($IsGiftMessageAvailableText);
            }

            if (isset($ProductDescription['RecommendedBrowseNode']) && !empty($ProductDescription['RecommendedBrowseNode'])) {
                $result = preg_split('/[,; ]/', $ProductDescription['RecommendedBrowseNode']);

                if (is_array($result)) {
                    $count = 1;
                    foreach ($result as $browsenode) {
                        if (empty($browsenode) || !is_numeric($browsenode)) {
                            continue;
                        }

                        $RecommendedBrowseNodeTag = $Document->createElement('RecommendedBrowseNode');
                        $DescriptionDataTag->appendChild($RecommendedBrowseNodeTag);
                        $RecommendedBrowseNodeText = $Document->createTextNode($browsenode);
                        $RecommendedBrowseNodeTag->appendChild($RecommendedBrowseNodeText);

                        if ($count++ >= 2) {
                            break;
                        }
                    }
                }
            }
            // Added : 2015/12/17
            //
            if (isset($ProductDescription['MerchantShippingGroupName'])) {
                $merchantShippingGroupNameTag = $Document->createElement('MerchantShippingGroupName');
                $DescriptionDataTag->appendChild($merchantShippingGroupNameTag);
                $merchantShippingGroupNameText = $Document->createTextNode($ProductDescription['MerchantShippingGroupName']);
                $merchantShippingGroupNameTag->appendChild($merchantShippingGroupNameText);
            }

            if (isset($ProductData['Parameters']['xsd']) && is_array($ProductData['Parameters']['xsd'])) {
                $descriptionDataNode = $this->sortProductData($Document, $DescriptionDataTag, $ProductData['Parameters']['xsd']);
            }
            $Product->appendChild($descriptionDataNode);
        }

        // Clothes exception
        if (isset($ProductData['ClassificationData'])) {
            $productDataNode = $this->clothingToXmlNode($Document, $ProductData, 'ClassificationData');
        } else {
            $productDataNode = $this->convertToXmlNode($Document, $ProductData, 'ProductData');
        }

        //ORDER ELEMENTS ACCORDING TO XSD
        if ($productDataNode && isset($ProductData['Parameters']) && isset($ProductData['Parameters']['xsd'])
            && is_array($ProductData['Parameters']['xsd']) && count($ProductData['Parameters']['xsd'])) {
            $productDataNode = $this->sortProductData($Document, $productDataNode, $ProductData['Parameters']['xsd']);
        }

        // 2016-02-28 Promotag
        //
        if ($ProductData && isset($ProductData['PromoTag']) && Tools::strlen($ProductData['PromoTag'])) {
            $PromoTag = $Document->createElement('PromoTag');
            $PromoTag->appendChild($promoTagType = $Document->createElement('PromoTagType'));
            $PromoTag->appendChild($effectiveFromDate = $Document->createElement('EffectiveFromDate'));
            $PromoTag->appendChild($promoTagType = $Document->createElement('EffectiveThroughDate'));

            $promoTagType->appendChild($Document->createTextNode($ProductData['PromoTag']));
            $promoTagType->appendChild($Document->createTextNode($ProductData['EffectiveFromDate']));
            $promoTagType->appendChild($Document->createTextNode($ProductData['EffectiveThroughDate']));

            $Product->appendChild($PromoTag);
        }

        if ($productDataNode) {
            $Product->appendChild($productDataNode);
        }

        // 2016-02-28 EnhancedImageURL
        //
        if ($ProductData && isset($ProductData['EnhancedImageURL']) && Tools::strlen($ProductData['EnhancedImageURL'])) {
            $EnhancedImageURLTag = $Document->createElement('EnhancedImageURL');
            $EnhancedImageURLTag->appendChild($Document->createTextNode($ProductData['EnhancedImageURL']));
            $Product->appendChild($EnhancedImageURLTag);
        }
        // 2013-03-23 Adding EAN/UPC Exemption
        //
        if ($ProductData && isset($ProductData['RegisteredParameter'])) {
            $RegisteredParameterTag = $Document->createElement('RegisteredParameter');
            $RegisteredParameterTag->appendChild($Document->createTextNode($ProductData['RegisteredParameter']));
            $Product->appendChild($RegisteredParameterTag);
        }

        return $Message;
    }

    /**
     * @param DOMDocument $Document
     * @param $arrayOfData
     * @param string $parentTagName
     *
     * @return bool|DOMElement
     */
    private function clothingToXmlNode(DOMDocument $Document, $arrayOfData, $parentTagName = 'ProductData')
    {
        $details = 0;
        $param = 'Parameters';
        if (is_array($arrayOfData)) {
            $root = $Document->createElement('ProductData');
            $clothing = $root->appendChild($Document->createElement('Clothing'));

            if (isset($arrayOfData['Parentage'])) {
                $clothing->appendChild($Document->createElement('VariationData'));
            }

            $element = $clothing->appendChild($Document->createElement($parentTagName));
            $parentElement = $root;

            /*
            * if [Parameters] is not set in the array or has zero (0) elements,
            * then no XML element is generated
            */
            if (!isset($arrayOfData[$param]) || !is_array($arrayOfData[$param]) || count($arrayOfData[$param]) == 0) {
                return false;
            }

            foreach ($arrayOfData as $key => $value) {
                if ($key == $param) {
                    continue;
                }

                if (is_array($arrayOfData[$key])) {
                    $this->convertArrayToXmlNode($Document, $arrayOfData[$key], $element);
                } elseif (!isset($arrayOfData[$param][$key])) {
                    continue;
                } elseif (is_array($arrayOfData[$param][$key])) {
                    $xPrevQuery = '';
                    $xQuery = '';
                    $first = true;
                    $x = new DOMXPath($Document);

                    //Generates Nodes to be added
                    foreach ($arrayOfData[$param][$key] as $tagName) {
                        $xPrevQuery = $xQuery;
                        if ($first) {
                            $first = false;
                            $xQuery = $tagName; //assign
                        } else {
                            $xQuery .= '/'.$tagName;
                        } //concatenate

                        $exists = $x->query($xQuery, $parentElement)->length;
                        if ($exists == 0) {
                            if ($xPrevQuery != '') {
                                $result = $x->query($xPrevQuery, $parentElement);
                                /**
                                 * Before we check if it has text as child, and removes it
                                 */
                                foreach ($result as $r) {
                                    if ($r->hasChildNodes()) {
                                        foreach ($r->childNodes as $child) {
                                            if ($child->nodeName == '#text') {
                                                $child->parentNode->removeChild($child);
                                            }
                                        }
                                    }
                                }

                                foreach ($result as $r) {
                                    $node = $Document->createElement($tagName);
                                    $this->setElementAttributes($node, $arrayOfData);
                                    $r->appendChild($node);
                                    break; //only one time
                                }
                            } else {
                                $node = $Document->createElement($tagName);
                                $this->setElementAttributes($node, $arrayOfData);
                                try {
                                    $parentElement->insertBefore($node, $element);
                                } catch (Exception $e) {
                                    $parentElement->appendChild($node);
                                }
                            };
                        }
                    }

                    $lastChild = $x->query($xQuery, $parentElement);

                    if ($lastChild) {
                        foreach ($lastChild as $l) {
                            if ($tagName != 'ProductType') {
                                $l->appendChild($Document->createTextNode($value));
                            } else {
                                $l->appendChild($Document->createElement(trim($value)));
                            }
                            break;
                        }
                        $details++;
                    }
                }
            }

            return $root;
        }

        return false;
    }

    /**
     * @param DOMDocument $Document
     * @param $array
     * @param DOMElement $root
     *
     * @return string
     */
    private function convertArrayToXmlNode(DOMDocument $Document, $array, DOMElement $root)
    {
        $xPrevQuery = '';
        $xQuery = '';
        $first = true;
        $x = new DOMXPath($Document);
        $newElement = false;
        $path = '';

        foreach ($array as $tagName => $tagChild) {
            $xPrevQuery = $xQuery;
            if ($first) {
                $first = false;
                $xQuery = $tagName; //assign
            } else {
                $xQuery .= '/'.$tagName;
            } //concatenate

            $exists = $x->query($xQuery, $root)->length;

            if ($exists == 0) {
                if ($xPrevQuery != '') {
                    $result = $x->query($xPrevQuery, $root);
                    /**
                     * Before we check if it has text as child, and removes it
                     */
                    foreach ($result as $r) {
                        if ($r->hasChildNodes()) {
                            foreach ($r->childNodes as $child) {
                                if ($child->nodeName == '#text') {
                                    $child->parentNode->removeChild($child);
                                }
                            }
                        }
                    }

                    foreach ($result as $r) {
                        $newElement = $Document->createElement($tagName);
                        $this->setElementAttributes($newElement, $array);
                        $r->appendChild($newElement);
                        break; //only one time
                    }
                } else {
                    $newElement = $Document->createElement($tagName);
                    $this->setElementAttributes($newElement, $array);
                    $root->appendChild($newElement);
                }

                if (is_array($tagChild) && $newElement) {
                    $this->convertArrayToXmlNode($Document, $array[$tagChild], $newElement);
                } elseif ($newElement) {
                    $lastChild = $x->query($xQuery, $root);
                    if ($lastChild) {
                        foreach ($lastChild as $l) {
                            if ($tagName != 'ProductType') {
                                $node = $Document->createTextNode($tagChild);
                                $l->appendChild($node);
                            } else {
                                $node = $Document->createElement(trim($tagChild));
                                $this->setElementAttributes($node, $array);
                                $l->appendChild($node);
                            }
                            break;
                        }
                    }
                }
            }
        }

        return $path;
    }

    /**
     * @param DOMElement $node
     * @param array $productData
     */
    private function setElementAttributes(DOMElement $node, array $productData)
    {
        if (isset($productData['Attributes'][$node->nodeName])) {
            foreach ($productData['Attributes'][$node->nodeName] as $attr => $value) {
                $node->setAttribute($attr, $value);
            }
        }
    }

    /**
     *
     * @param DOMDocument $Document
     * @param array $arrayOfData Array containing "Elements" to be added and their corresponding path in "Parameters"
     * @param string $parentTagName the name of the tag where elements will be appended, for example:"ProductData"
     * @return boolean
     */
    private function convertToXmlNode(DOMDocument $Document, $arrayOfData, $parentTagName = 'ProductData')
    {
        $details = 0;
        $param = 'Parameters';
        $attributes = 'Attributes';

        if (is_array($arrayOfData)) {
            $parentElement = $Document->createElement($parentTagName);

            /*
            * if [Parameters] is not set in the array or has zero (0) elements,
            * then no XML element is generated
            */
            if (!isset($arrayOfData[$param]) || !is_array($arrayOfData[$param]) || count($arrayOfData[$param]) == 0) {
                return false;
            }
            foreach ($arrayOfData as $key => $value) {
                if ($key == $param || $key == $attributes) {
                    continue;
                }

                if (!isset($arrayOfData[$param][$key])) {
                    continue;
                }

                if (is_array($arrayOfData[$param][$key])) {
                    $xPrevQuery = '';
                    $xQuery = '';
                    $first = true;
                    $x = new DOMXPath($Document);
                    //Generates Nodes to be added
                    foreach ($arrayOfData[$param][$key] as $tagName) {
                        $xPrevQuery = $xQuery;
                        if ($first) {
                            $first = false;
                            $xQuery = $tagName; //assign
                        } else {
                            $xQuery .= '/'.$tagName;
                        } //concatenate

                        $exists = $x->query($xQuery, $parentElement)->length;

                        if ($exists == 0) {
                            if ($xPrevQuery != '') {
                                $result = $x->query($xPrevQuery, $parentElement);

                                /**
                                 * Before we check if it has text as child, and removes it
                                 */
                                foreach ($result as $r) {
                                    if ($r->hasChildNodes()) {
                                        foreach ($r->childNodes as $child) {
                                            if ($child->nodeName == '#text') {
                                                $child->parentNode->removeChild($child);
                                            }
                                        }
                                    }
                                }


                                foreach ($result as $r) {
                                    // Support case:
                                    // https://support.common-services.com/helpdesk/tickets/42626
                                    // We should not send ClassificationData for parents
                                    if (isset($r->tagName) && $r->tagName == 'ClassificationData' && isset($arrayOfData['Parentage']) && $arrayOfData['Parentage'] == 'parent' && (isset($arrayOfData['Parameters']['ClothingType']) && $arrayOfData['Parameters']['ClothingType'][0] != 'Shoes' && $tagName != 'ClothingType')) {
                                        continue;
                                    }

                                    $node = $Document->createElement($tagName);
                                    $this->setElementAttributes($node, $arrayOfData);
                                    $r->appendChild($node);
                                    break; //only one time
                                }
                            } else {
                                $node = $Document->createElement($tagName);
                                $this->setElementAttributes($node, $arrayOfData);
                                $parentElement->appendChild($node);
                            }
                        }
                    }


                    $lastChild = $x->query($xQuery, $parentElement);

                    if ($lastChild) {
                        if (is_array($value)) {
                            foreach ($value as $detail) {
                                foreach ($lastChild as $l) {
                                    if ($l->nodeValue == null) {
                                        //
                                        $l->nodeValue = $detail;
                                    } else {
                                        $el = $Document->createElement(trim($tagName));
                                        $el->nodeValue = $detail;
                                        $l->parentNode->appendChild($el);
                                    }
                                    break;
                                }
                            }
                        } else {
                            foreach ($lastChild as $l) {
                                if ($tagName != 'ProductType') {
                                    //
                                    $l->appendChild($Document->createTextNode($value));
                                } else {
                                    // If is not a complex type

                                    if (in_array($arrayOfData['Definition'], array(
                                            'ToysBaby',
                                            'Luggage',
                                            'Sports',
                                            'Miscellaneous'
                                        ))) {
                                        $l->appendChild($Document->createTextNode(trim($value)));
                                    } else {
                                        $l->appendChild($Document->createElement(trim($value)));
                                    }
                                }
                                break;
                            }
                        }
                        $details++;
                    }
                }
            }

            if ($details > 0) {
                return $parentElement;
            }
        }

        return false;
    }
    /**
     *
     * @param DOMDocument $Document
     * @param array $arrayOfData Array containing "Elements" to be added and their corresponding path in "Parameters"
     * @param string $parentTagName the name of the tag where elements will be appended, for example:"DescriptionData"
     * @return boolean
     */
    private function createDescriptionDataNode(DOMDocument $Document, &$arrayOfData, $parentTagName = 'DescriptionData')
    {
        $details = 0;
        $param = 'Parameters';
        $attributes = 'Attributes';
        $excluded = array('VariationData', 'ClassificationData', 'ProductType', 'ProductSubtype');
        $universe = null;

        if (is_array($arrayOfData)) {
            $parentElement = $Document->createElement($parentTagName);

            /*
            * if [Parameters] is not set in the array or has zero (0) elements,
            * then no XML element is generated
            */
            if (!isset($arrayOfData[$param]) || !is_array($arrayOfData[$param]) || count($arrayOfData[$param]) == 0) {
                return false;
            }

            foreach ($arrayOfData as $key => $value) {
                if ($key == $param || $key == $attributes) {
                    continue;
                }

                if (!isset($arrayOfData[$param][$key])) {
                    continue;
                }
                // Ensure this is a description data item
                $first_item = isset($arrayOfData[$param][$key][0]) ? $arrayOfData[$param][$key][0] : array();
                $second_item = isset($arrayOfData[$param][$key][1]) ? $arrayOfData[$param][$key][1] : array();
                $third_item = isset($arrayOfData[$param][$key][2]) ? $arrayOfData[$param][$key][2] : array();

                // Exclude Universe
                if (in_array($second_item, $excluded) && !in_array($first_item, $excluded)) {
                    $excluded[] = $first_item;
                }

                // Exclude non description data tags
                if (is_array($arrayOfData[$param][$key]) && count($arrayOfData[$param][$key]) > 2
                    || in_array($first_item, $excluded) || in_array($second_item, $excluded) || in_array($third_item, $excluded)) {
                    continue;
                }

                if (is_array($arrayOfData[$param][$key])) {
                    $xPrevQuery = '';
                    $xQuery = '';
                    $first = true;
                    $x = new DOMXPath($Document);
                    //Generates Nodes to be added
                    foreach ($arrayOfData[$param][$key] as $tagName) {
                        $xPrevQuery = $xQuery;
                        if ($first) {
                            $first = false;
                            $xQuery = $tagName; //assign
                        } else {
                            $xQuery .= '/'.$tagName;
                        } //concatenate

                        $exists = $x->query($xQuery, $parentElement)->length;

                        if ($exists == 0) {
                            if ($xPrevQuery != '') {
                                $result = $x->query($xPrevQuery, $parentElement);

                                /**
                                 * Before we check if it has text as child, and removes it
                                 */
                                foreach ($result as $r) {
                                    if ($r->hasChildNodes()) {
                                        foreach ($r->childNodes as $child) {
                                            if ($child->nodeName == '#text') {
                                                $child->parentNode->removeChild($child);
                                            }
                                        }
                                    }
                                }


                                foreach ($result as $r) {
                                    $node = $Document->createElement($tagName);
                                    $this->setElementAttributes($node, $arrayOfData);
                                    $r->appendChild($node);
                                    break; //only one time
                                }
                            } else {
                                $node = $Document->createElement($tagName);
                                $this->setElementAttributes($node, $arrayOfData);
                                $parentElement->appendChild($node);
                            }
                        }
                    }


                    $lastChild = $x->query($xQuery, $parentElement);

                    if ($lastChild) {
                        if (is_array($value)) {
                            foreach ($value as $detail) {
                                foreach ($lastChild as $l) {
                                    if ($l->nodeValue == null) {
                                        //
                                        $l->nodeValue = $detail;
                                    } else {
                                        $el = $Document->createElement(trim($tagName));
                                        $el->nodeValue = $detail;
                                        $l->parentNode->appendChild($el);
                                    }
                                    break;
                                }
                            }
                        } else {
                            foreach ($lastChild as $l) {
                                $l->appendChild($Document->createTextNode(trim($value)));
                                break;
                            }
                        }
                        $details++;
                    }
                    unset($arrayOfData[$tagName]);
                    unset($arrayOfData[$param][$key]);
                }
            }

            if ($details > 0) {
                return $parentElement;
            }
        }

        return false;
    }
    /*
    * Get result of data submission
    */

    /**
     * @param DOMDocument $document
     * @param DOMElement $element
     * @param array $xsdStructure
     *
     * @return DOMElement|DOMNode
     */
    public function sortProductData(DOMDocument $document, DOMElement $element, array $xsdStructure, $xpath = '/')
    {
        $d = new DOMDocument();
        $newElement = $d->importNode($element->cloneNode(true), true);
        $d->appendChild($newElement);

        $node = $this->setSortedElements($d, $newElement, $xsdStructure, $xpath);

        if ($node) {
            return $document->importNode($node, true);
        } else {
            return $element;
        }
    }

    /**
     * @param DOMDocument $d
     * @param DOMElement $element
     * @param array $xsdStructure
     * @param null $xPath
     *
     * @return bool|DOMElement
     */
    public function setSortedElements(DOMDocument $d, DOMElement $element, array $xsdStructure, $xPath = null)
    {
        $children = array();
        if ($element == null) {
            return (false);
        }

        $x = new DOMXPath($d);
        $query = $element->getNodePath().'/*';
        $result = $x->query($query);
        //As root element is ProductData, we look for its children like "Computers" or "Shoes"
        foreach ($result as $r) {
            if (isset($children[$r->nodeName])) {
                if (!is_array($children[$r->nodeName])) {
                    $children[$r->nodeName] = array($children[$r->nodeName]);
                }
                $children[$r->nodeName][] = $r->parentNode->removeChild($r);
            } else {
                $children[$r->nodeName] = $r->parentNode->removeChild($r);
            }
        }

        //append child according to structure order
        foreach ($xsdStructure as $key => $value) {
            if (isset($children[$key])) {
                if (is_array($children[$key])) {
                    foreach ($children[$key] as $e) {
                        $element->appendChild($e);
                        if (is_array($value) && count($value) > 0) {
                            // $children[$key] replaced by e 2018/09/30
                            $this->setSortedElements($d, $e, $xsdStructure[$key]);
                        }
                    }
                } else {
                    $element->appendChild($children[$key]);
                    if (is_array($value) && count($value) > 0) {
                        $children[$key] = $this->setSortedElements($d, $children[$key], $xsdStructure[$key]);
                    }
                }
            }
        }

        return $element;
    }

    /**
     *
     * @param <type> $products - object array
     * $products[$i]['SKU'] - must be not null
     * $products[$i]['Quantity'] - must be not null
     * @return mixed - FeedSubmissionID or false (if an error occurred)
     */
    public function updateQuantities($products)
    {
        if ($this->_debug) {
            printf("$this->_cr updateQuantities() call, checking input arguments ...  $this->_cr");
        }

        // Skip "Do not export quantity" products
        //
        $count_products = is_array($products) ? count($products) : 0;
        for ($i = 0; $i < $count_products; $i++) {
            if (isset($products[$i]['NoQtyExport']) && $products[$i]['NoQtyExport'] === true) {
                if ($this->_debug) {
                    printf("$this->_cr updateQuantities() call, skipping product[$i]: %s (NoQtyExport is sets) $this->_cr", $products[$i]['SKU']);
                }
                unset($products[$i]);
            }
        }

        $products = array_values($products);


        $count_products = is_array($products) ? count($products) : 0;

        if (!$count_products) {
            $products = null;
        }

        //checking input arguments
        for ($i = 0; $i < $count_products; $i++) {
            if ($this->_debug) {
                printf("$this->_cr updateQuantities() function. \$products[$i] checking...  $this->_cr");
            }

            $SKUValue = isset($products[$i]['SKU']) ? trim($products[$i]['SKU']) : null;

            if (!$this->checkSKU($SKUValue)) {
                if ($this->_debug) {
                    printf("$this->_cr updateQuantities() function. $this->_att Warning. \$products[$i]['SKU'] = $SKUValue is incorrect. Please verify this... Function skips this product and continue excution. %s  $this->_cr", $this->_caller());
                }

                $products[$i] = null;
                continue;
            }
            if (!isset($products[$i]['Quantity']) || !$this->checkQuantity($products[$i]['Quantity'])) {
                $QuantityValue = isset($products[$i]['Quantity']) ? $products[$i]['Quantity'] : null;
                if ($this->_debug) {
                    printf("$this->_cr updateQuantities() function. $this->_att Warning. \$products[$i]['Quantity'] = $QuantityValue is incorrect. Please verify this... Function skips this product and continue excution. $this->_cr");
                }

                $products[$i] = null;
                continue;
            }
        }

        $count_products = is_array($products) ? count($products) : 0;

        //if there is no Quantities to update, function will be terminated
        for ($i = 0; $i < $count_products; $i++) {
            if ($products[$i] != null) {
                break;
            }

            if ($i == count($products) - 1) {
                if ($this->_debug) {
                    printf("$this->_cr updateQuantities() function. \$products[$i] - We have no Quantities to update (or all the instances are skipped). Function is finished with $this->_att an Error. $this->_cr");
                }

                return false;
            }
        }

        $Document = new DOMDocument();
        $Messages = array();

        if ($this->_debug) {
            printf("$this->_cr  updateQuantities() function. Creation of the QuantityMessages starts here  $this->_cr");
        }

        $count_products = is_array($products) ? count($products) : 0;
        $m = 0;
        for ($i = 0; $i < $count_products; $i++) {
            if ($products[$i] != null) {
                $Messages[$m] = $this->createQuantityMessage($Document, $products[$i]['SKU'], $products[$i]['Quantity'], $products[$i], $m + 1);
                $m++;
            } else {
                if ($this->_debug) {
                    printf("$this->_cr  updateQuantities() function. \$products[$i] - Incorrect product. Creation of the QuantityMessages skipped  $this->_cr");
                }
            }
        }

        if (!$m) {
            if ($this->_debug) {
                printf("$this->_cr updateQuantities() function. $this->_att No Quantity Messages to Send ... $this->_cr ");
            }

            return (false);
        }
        $feedDOM = $this->CreateFeed($Document, 'Product', $Messages);

        if ($this->_debug) {
            $feedDOM->formatOutput = true;
        }

        $feed = $feedDOM->saveXML();
        $data = $this->processFeed('_POST_INVENTORY_AVAILABILITY_DATA_', $feed);
        if ($this->_debug) {
            CommonTools::p(htmlentities($feed));

            if ($data === false || $data === null) {
                printf("$this->_cr updateQuantities() function is finished $this->_att with an error $this->_cr");
            } else {
                printf("$this->_cr updateQuantities() result is: %s $this->_cr", nl2br(print_r($data, true)));
            }
        }

        return $data;
    }

    /**
     * @param $Quantity
     *
     * @return bool
     */
    private function checkQuantity($Quantity)
    {
        if ($this->_debug) {
            printf("$this->_cr checkQuantity call $this->_cr");
        }

        if ($Quantity === null) {
            return false;
        }

        if ($Quantity == 0 || $Quantity == '0') {
            return true;
        }

        if (!is_numeric($Quantity)) {
            if ($this->_debug) {
                printf("$this->_cr checkQuantity function. \$Quantity must be numeric You sent incorrect argument \$Quantity = $Quantity . Please verify this and try again. Function is finished with an error. $this->_cr");
            }

            return false;
        }
        $quan = (int)$Quantity;

        if ($quan > 0) {
            if ($this->_debug) {
                printf("$this->_cr checkQuantity function is completed successfully $this->_cr");
            }

            return true;
        }
        if ($this->_debug) {
            printf("$this->_cr checkQuantity function is completed with $this->_att an Error. It is incorrect input argument Quantity = $Quantity . Must be not negative integer. $this->_cr");
        }

        return false;
    }

    /**
     * @param DOMDocument $Document
     * @param $SKU
     * @param $quantity
     * @param $options
     * @param $MessageID
     *
     * @return DOMElement
     */
    private function createQuantityMessage(DOMDocument $Document, $SKU, $quantity, $options, $MessageID)
    {
        switch ($this->_operationMode) {
            case self::OPERATIONS_CREATE:
            case self::OPERATIONS_UPDATE:
                $updatingType = 'Update';
                break;
            case self::OPERATIONS_DELETE:
                $updatingType = 'Delete';
                break;
            default:
                echo "$this->_cr createQuantityMessage call. undefined value for updatingType . $this->_cr";

                return (false);
        }

        if ($this->_debug) {
            printf("$this->_cr createQuantityMessage call \$SKU = $SKU \$quantity = $quantity \$MessageID = $MessageID $this->_cr");
        }

        $Message = $Document->createElement('Message');
        $MessageIDX = $Document->createElement('MessageID');
        $Message->appendChild($MessageIDX);
        $MessageIDX->appendChild($Document->createTextNode($MessageID));
        $OperationType = $Document->createElement('OperationType');
        $Message->appendChild($OperationType);
        $OperationType->appendChild($Document->createTextNode($updatingType));

        $Inventory = $Document->createElement('Inventory');
        $Message->appendChild($Inventory);
        $SKUX = $Document->createElement('SKU');
        $Inventory->appendChild($SKUX);
        $SKUX->appendChild($Document->createTextNode($SKU));

        if ($options && isset($options['FBA'])) {
            $FBA = true;
        } else {
            $FBA = false;
        }

        // FBA:  ignore quantities
        if ($FBA) {
            $FBA = $Document->createElement('FulfillmentCenterID');
            $Inventory->appendChild($FBA);
            $FulfillmentCenterID = $Document->createTextNode($options['FBA']);
            $FBA->appendChild($FulfillmentCenterID);

            $Lookup = $Document->createElement('Lookup');
            $Inventory->appendChild($Lookup);
            $Lookup->appendChild($Document->createTextNode('FulfillmentNetwork'));
        } else {
            $Quantity = $Document->createElement('Quantity');
            $Inventory->appendChild($Quantity);
            $Quantity->appendChild($Document->createTextNode($quantity));
        }

        if ($options && isset($options['RestockDate'])) {
            $restockdate = $options['RestockDate'];
        } else {
            $restockdate = null;
        }

        if ($restockdate) {
            $RestockDate = $Document->createElement('RestockDate');
            $Inventory->appendChild($RestockDate);
            $RestockDate->appendChild($Document->createTextNode($restockdate));
        }

        if ($options && isset($options['FulfillmentLatency'])) {
            $fulfillmentLatency = (int)$options['FulfillmentLatency'];
        } else {
            $fulfillmentLatency = false;
        }

        if ($fulfillmentLatency) {
            $FulfillmentLatency = $Document->createElement('FulfillmentLatency');
            $Inventory->appendChild($FulfillmentLatency);
            $FulfillmentLatency->appendChild($Document->createTextNode($fulfillmentLatency));
        }

        return $Message;
    }

    /**
     * @param $products
     *
     * @return bool|SimpleXMLElement
     */
    public function updatePrices($products)
    {
        if ($this->_debug) {
            printf("$this->_cr updatePrices() call, checking input arguments... $this->_cr");
        }

        $products = array_values($products);

        if (!is_array($products) || !count($products)) {
            return (false);
        }

        $count_products = is_array($products) ? count($products) : 0;
        for ($i = 0; $i < $count_products; $i++) {
            if (isset($products[$i]['NoPriceExport']) && $products[$i]['NoPriceExport'] == true) {
                if ($this->_debug) {
                    printf("$this->_cr updatePrices() function. Skipping Price Update... $this->_cr");
                }

                $products[$i] = null;
                continue;
            }
            if ($this->_debug) {
                printf("$this->_cr updatePrices() function. Checking \$products[$i] $this->_cr");
            }

            if ($this->checkSKU($products[$i]['SKU']) == false || !isset($products[$i]['Price']) || ($products[$i]['Price'] = $this->checkPrice($products[$i]['Price'])) == false) {
                if ($this->_debug) {
                    $value1 = $products[$i]['SKU'];
                    $value2 = isset($products[$i]['Price']) ? $products[$i]['Price'] : 0;
                    printf(
                        "$this->_cr updatePrices() function. $this->_att an Warning occured during the test of the $i-th argument
						There are values:$this->_cr
						\$products[\$i]['SKU']: $value1 $this->_cr
						\$products[\$i]['Price']: $value2 $this->_cr
						Function skips this item and continue execution %s $this->_cr",
                        $this->_caller()
                    );
                }
                $products[$i] = null;
                continue;
            }
        }

        if ($this->_debug) {
            printf("$this->_cr updatePrices() function. Checking input arguments completed $this->_cr");
        }

        $count_products = is_array($products) ? count($products) : 0;

        //if there is no Prices to update, function will be terminated
        for ($i = 0; $i < $count_products; $i++) {
            if ($products[$i] != null) {
                break;
            }

            $count_products = is_array($products) ? count($products) : 0;

            if ($i == $count_products - 1) {
                if ($this->_debug) {
                    printf("$this->_cr updatePrices() function. We have no Prices to update (or all the instances are skipped). Function is finished with $this->_att an Error. $this->_cr");
                }

                return false;
            }
        }

        $Document = new DOMDocument();
        $Messages = array();
        if ($this->_debug) {
            printf("$this->_cr updatePrices() function. Creating messages here (based on the input arguments) $this->_cr");
        }

        $m = 0;
        $count_products = is_array($products) ? count($products) : 0;

        for ($i = 0; $i < $count_products; $i++) {
            if ($products[$i] != null) {
                if ($this->_debug) {
                    printf("$this->_cr updatePrices() function. The $i-th message creation... $this->_cr");
                }

                $Messages[$m] = $this->createPriceMessage($Document, $products[$i]['SKU'], $products[$i]['Price'], $products[$i]['Sales'], $products[$i]['Business'], $m + 1);

                if (!$Messages[$m]) {
                    continue;
                }

                $m++;
            } else {
                if ($this->_debug) {
                    printf("$this->_cr updatePrices() function. The $i-th message creation skipped $this->_cr");
                }
            }
        }

        if (!$m) {
            if ($this->_debug) {
                printf("$this->_cr updatePrices() function. $this->_att No Price Messages to Send ... $this->_cr ");
            }

            return (false);
        }

        if ($this->_debug) {
            printf("$this->_cr updatePrices() function. Calling the CreateFeed() function ... $this->_cr ");
        }

        $feedDOM = $this->CreateFeed($Document, 'Price', $Messages);

        if ($this->_debug) {
            $feedDOM->formatOutput = true;
        }

        $feed = $feedDOM->saveXML();

        if ($this->_debug) {
            printf("$this->_cr updatePrices() function. Calling processFeed() function here $this->_cr");
            CommonTools::p(htmlentities($feed));
        }

        $data = $this->processFeed('_POST_PRODUCT_PRICING_DATA_', $feed);

        if ($this->_debug) {
            if ($data === false || $data === null) {
                printf("$this->_cr updatePrices() function is finished $this->_att with an error $this->_cr");
            } else {
                printf("$this->_cr updatePrices() function is finished successfully here $this->_cr");
            }
        }

        return $data;
    }

    /**
     * @param $price
     *
     * @return bool|mixed
     */
    private function checkPrice($price)
    {
        if ($this->_debug) {
            printf("$this->_cr checkPrice call. \$price=$price $this->_cr");
        }

        if (strpos($price, ',') != false) {
            $price = str_replace(',', '.', $price);
        }

        if (is_numeric($price)) {
            if ($price >= 0) {
                if ($this->_debug) {
                    printf("$this->_cr checkPrice() function is completed successfully. $this->_cr");
                }

                return $price;
            }
        }
        if ($this->_debug) {
            printf("$this->_cr checkPrice() function is completed $this->_att with an error. You sent incorrect input argument \$price = $price $this->_cr");
        }

        return false;
    }

    /**
     * @param DOMDocument $Document
     * @param $SKU
     * @param $Price
     * @param $Sales
     * @param $MessageID
     *
     * @return DOMElement
     */
    private function createPriceMessage(DOMDocument $Document, $SKU, $Price, $Sales, $Business, $MessageID)
    {
        switch ($this->_operationMode) {
            case self::OPERATIONS_CREATE:
            case self::OPERATIONS_UPDATE:
                break;
            case self::OPERATIONS_DELETE:
                return (null);
            default:
                echo "$this->_cr createPriceMessage call. undefined value for updatingType . $this->_cr";

                return (false);
        }
        if ($this->_debug) {
            printf("$this->_cr createPriceMessage call. \$SKU = $SKU \$Price = $Price \$MessageID = $MessageID Current currency: $this->Currency $this->_cr");
        }

        $Message = $Document->createElement('Message');
        $MessageIDX = $Document->createElement('MessageID');
        $Message->appendChild($MessageIDX);
        $MessageIDText = $Document->createTextNode($MessageID);
        $MessageIDX->appendChild($MessageIDText);
        $PriceX = $Document->createElement('Price');
        $Message->appendChild($PriceX);
        $SKUX = $Document->createElement('SKU');
        $PriceX->appendChild($SKUX);
        $SKUText = $Document->createTextNode($SKU);
        $SKUX->appendChild($SKUText);
        $StandardPrice = $Document->createElement('StandardPrice');
        $StandardPrice->setAttribute('currency', $this->Currency);

        $PriceX->appendChild($StandardPrice);

        if (is_array($Business) && count($Business)) {
            $BusinessPrice = $Document->createElement('BusinessPrice', sprintf('%.02f', $Business['BusinessPrice']));
            $PriceX->appendChild($BusinessPrice);

            if (isset($Business['rules']) && is_array($Business['rules']) && count($Business['rules'])) {
                $QuantityPriceType = $Document->createElement('QuantityPriceType', $Business['QuantityPriceType']);
                $PriceX->appendChild($QuantityPriceType);

                $QuantityPriceTag = $Document->createElement('QuantityPrice');
                $is_percentage = ($Business['QuantityPriceType'] == 'percent');

                foreach (range(1, 3) as $index) {
                    if (!isset($Business['rules']['QuantityPrice'.$index])) {
                        continue;
                    }
                    $rules = $Business['rules'];

                    $QuantityPrice = $Document->createElement('QuantityPrice'.$index, $is_percentage ? (int)$rules['QuantityPrice'.$index] : sprintf('%.02f', $rules['QuantityPrice'.$index]));
                    $QuantityLowerBound = $Document->createElement('QuantityLowerBound'.$index, $is_percentage ? (int)$rules['QuantityLowerBound'.$index] : sprintf('%.02f', $rules['QuantityLowerBound'.$index]));

                    $QuantityPriceTag->appendChild($QuantityPrice);
                    $QuantityPriceTag->appendChild($QuantityLowerBound);
                }
                $PriceX->appendChild($QuantityPriceTag);
            }
        }
        /*
         * array (size=3)
  'BusinessPrice' => float 4.94
  'QuantityPriceType' => string 'percent' (length=7)
  'rules' =>
    array (size=4)
      'QuantityPrice1' => int 10
      'QuantityLowerBound1' => string '3' (length=1)
      'QuantityPrice2' => int 15
      'QuantityLowerBound2' => string '6' (length=1)
         */


        if ($Sales && isset($Sales['dateStart']) && isset($Sales['dateEnd']) && isset($Sales['salePrice'])) {
            $SaleTag = $Document->createElement('Sale');
            $dateStart = $Document->createElement('StartDate', $Sales['dateStart']);
            $dateEnd = $Document->createElement('EndDate', $Sales['dateEnd']);
            $salePrice = $Document->createElement('SalePrice', str_replace(',', '.', $Sales['salePrice']));
            $salePrice->setAttribute('currency', $this->Currency);
            $SaleTag->appendChild($dateStart);
            $SaleTag->appendChild($dateEnd);
            $SaleTag->appendChild($salePrice);
            $PriceX->appendChild($SaleTag);
        }

        $StandardPriceText = $Document->createTextNode($Price);
        $StandardPrice->appendChild($StandardPriceText);

        return $Message;
    }

    /**
     *
     * @param <type> $products - object array
     * $products[$i]['SKU'] - must be not null
     * $products[$i]['ShippingPrice'] - must be not null
     * @return mixed - FeedSubmissionID or false (if an error occurred)
     */
    public function overrideShipping($products)
    {
        if ($this->_debug) {
            printf("$this->_cr overrideShipping() call, checking input arguments ...  $this->_cr");
        }

        $count_product = is_array($products) ? count($products) : 0;

        for ($i = 0; $i < $count_product; $i++) {
            if ($this->_debug) {
                printf("$this->_cr overrideShipping() function. \$products[$i] checking...  $this->_cr");
            }

            if (!$this->checkSKU($products[$i]['SKU'])) {
                $SKUValue = $products[$i]['SKU'];
                if ($this->_debug) {
                    printf("$this->_cr overrideShipping() function. $this->_att Warning. \$products[$i]['SKU'] = $SKUValue is incorrect. Please verify this... Function skips this product and continue excution.  $this->_cr");
                }

                $products[$i] = null;
                continue;
            }
        }

        $count_product = is_array($products) ? count($products) : 0;

        for ($i = 0; $i < $count_product; $i++) {
            if ($products[$i] != null) {
                break;
            }

            if ($i == count($products) - 1) {
                if ($this->_debug) {
                    printf("$this->_cr overrideShipping() function. We have no Shipping to update (or all the instances are skipped). Function is finished with $this->_att an Error. $this->_cr");
                }

                return true;
            }
        }

        $Document = new DOMDocument();
        $Messages = array();

        if ($this->_debug) {
            printf("$this->_cr  overrideShipping() function. Creation of the Quantity. Messages starts here  $this->_cr");
        }

        $count_product = is_array($products) ? count($products) : 0;
        $m = 0;
        for ($i = 0; $i < $count_product; $i++) {
            if ($products[$i] != null) {
                if (!isset($products[$i]['ShippingPrice'])) {
                    continue;
                }

                if (!isset($products[$i]['ShippingOption']) || !$products[$i]['ShippingOption']) {
                    continue;
                }

                if (!isset($products[$i]['ShippingType']) || !$products[$i]['ShippingType']) {
                    continue;
                }

                $Messages[$m] = $this->createShippingOverrideMessage($Document, $products[$i]['SKU'], $products[$i]['ShippingPrice'], $products[$i]['ShippingOption'], $products[$i]['ShippingType'], $m + 1);
                $m++;
            } else {
                if ($this->_debug) {
                    printf("$this->_cr  overrideShipping() function. Incorrect product. Creation of the QuantityMessages skipped  $this->_cr");
                }
            }
        }

        if (!count($Messages)) {
            if ($this->_debug) {
                printf("$this->_cr overrideShipping() nothing to override $this->_cr");
            }

            return (true);
        }

        $feedDOM = $this->CreateFeed($Document, 'Override', $Messages);

        if ($this->_debug) {
            $feedDOM->formatOutput = true;
        }

        $feed = $feedDOM->saveXML();
        $data = $this->processFeed('_POST_PRODUCT_OVERRIDES_DATA_', $feed);

        if ($this->_debug) {
            CommonTools::p(htmlentities($feed));

            if ($data === false || $data === null) {
                printf("$this->_cr overrideShipping() function is finished $this->_att with an error $this->_cr");
            } else {
                printf("$this->_cr overrideShipping() function is finished successfully here $this->_cr");
            }
        }

        return $data;
    }

    /**
     * @param DOMDocument $Document
     * @param $SKU
     * @param $shippingPrice
     * @param $shippingOption
     * @param $shippingType
     * @param $MessageID
     *
     * @return DOMElement
     */
    private function createShippingOverrideMessage(DOMDocument $Document, $SKU, $shippingPrice, $shippingOption, $shippingType, $MessageID)
    {
        switch ($this->_operationMode) {
            case self::OPERATIONS_CREATE:
            case self::OPERATIONS_UPDATE:
                $updatingType = 'Update';
                break;
            case self::OPERATIONS_DELETE:
                $updatingType = 'Delete';
                break;
            default:
                echo "$this->_cr createShippingOverrideMessage call. undefined value for updatingType . $this->_cr";

                return (false);
        }
        if ($this->_debug) {
            printf("$this->_cr createShippingOverrideMessage call \$SKU = $SKU \$shippingPrice = $shippingPrice \$MessageID = $MessageID $this->_cr");
        }

        if ($this->_debug) {
            $Document->formatOutput = true;
        }

        if ($shippingPrice === '') {
            $delete = 1;
        } else {
            $delete = 0;
        }

        $Message = $Document->createElement('Message');
        $MessageIDX = $Document->createElement('MessageID');
        $Message->appendChild($MessageIDX);
        $MessageIDText = $Document->createTextNode($MessageID);
        $MessageIDX->appendChild($MessageIDText);
        $OperationType = $Document->createElement('OperationType', $delete ? 'Delete' : $updatingType);
        $Message->appendChild($OperationType);


        $Override = $Document->createElement('Override');
        $Message->appendChild($Override);
        $SKUX = $Document->createElement('SKU', $SKU);
        $Override->appendChild($SKUX);

        $ShippingOverride = $Document->createElement('ShippingOverride');
        $Override->appendChild($ShippingOverride);


        $ShipOption = $Document->createElement('ShipOption', $shippingOption);
        $ShippingOverride->appendChild($ShipOption);

        if ($shippingType) {
            $Type = $Document->createElement('Type', $shippingType);
            $ShippingOverride->appendChild($Type);
        }

        $ShipAmount = $Document->createElement('ShipAmount', $delete ? 0 : $shippingPrice);
        $ShipCurrency = $Document->createAttribute('currency');
        $ShipCurrency->value = $this->Currency;
        $ShipAmount->appendChild($ShipCurrency);
        $ShippingOverride->appendChild($ShipAmount);

        $xml = $Document->saveXML($Message);

        if ($this->_debug) {
            CommonTools::p(htmlentities($xml));
        }

        return $Message;
    }

    /**
     *
     * @param <type> $products - object array
     * $products[$i]['SKU'] - must be not null
     * $products[$i]['ProductImage'] - must be not null
     * @return <int> - FeedSubmissionID or false (if an error occurred)
     */
    public function productImage($products)
    {
        if ($this->_debug) {
            printf("$this->_cr productImage() call, checking input arguments ...  $this->_cr");
        }

        $count_products = is_array($products) ? count($products) : 0;
        //checking input arguments
        for ($i = 0; $i < $count_products; $i++) {
            if ($this->_debug) {
                printf("$this->_cr productImage() function. \$products[$i] checking...  $this->_cr");
            }

            if (!$this->checkSKU($products[$i]['SKU'])) {
                $SKUValue = $products[$i]['SKU'];
                if ($this->_debug) {
                    printf("$this->_cr productImage() function. $this->_att Warning. \$products[$i]['SKU'] = $SKUValue is incorrect. Please verify this... Function skips this product and continue excution.  $this->_cr");
                }

                $products[$i] = null;
                continue;
            }
        }

        $count_products = count($products);

        for ($i = 0; $i < $count_products; $i++) {
            if ($products[$i] != null) {
                break;
            }

            if ($i == count($products) - 1) {
                if ($this->_debug) {
                    printf("$this->_cr productImage() function. We have no Shipping to update (or all the instances are skipped). Function is finished with $this->_att an Error. $this->_cr");
                }

                return (false);
            }
        }

        $Document = new DOMDocument();
        $Messages = array();

        if ($this->_debug) {
            printf("$this->_cr  productImage() function. Creation of the Messages starts here  $this->_cr");
        }

        $m = 0;
        $count_products = is_array($products) ? count($products) : 0;

        for ($i = 0; $i < $count_products; $i++) {
            if ($products[$i] != null) {
                if (!isset($products[$i]['ProductData']['ProductImage'])) {
                    continue;
                }

                $index = 0;
                foreach ($products[$i]['ProductData']['ProductImage'] as $image) {
                    /*
                    if ($index == 0) {
                        $Messages[$m] = $this->createProductImageMessage($Document, $products[$i]['SKU'], $image, $m + 1, $index, 'OfferImage1');
                        $m++;
                    }
                    */
                    $Messages[$m] = $this->createProductImageMessage($Document, $products[$i]['SKU'], $image, $m + 1, $index);
                    $m++;
                    $index++;
                }
            } else {
                if ($this->_debug) {
                    printf("$this->_cr  productImage() function. Incorrect product. Creation of the QuantityMessages skipped  $this->_cr");
                }
            }
        }

        if (!count($Messages)) {
            if ($this->_debug) {
                printf("$this->_cr productImage() no images $this->_cr");
            }

            return (false);
        }

        $feedDOM = $this->CreateFeed($Document, 'ProductImage', $Messages);

        if ($this->_debug) {
            $feedDOM->formatOutput = true;
        }

        $feed = $feedDOM->saveXML();
        $data = $this->processFeed('_POST_PRODUCT_IMAGE_DATA_', $feed);
        if ($this->_debug) {
            CommonTools::p(htmlentities($feed));

            if ($data === false || $data === null) {
                printf("$this->_cr productImage() function is finished $this->_att with an error $this->_cr");
            } else {
                printf("$this->_cr productImage() function is finished successfully here $this->_cr");
            }
        }

        return $data;
    }

    /**
     * @param DOMDocument $Document
     * @param $SKU
     * @param $productImage
     * @param $MessageID
     * @param $index
     * @param $imageType
     *
     * @return DOMElement
     */
    private function createProductImageMessage(DOMDocument $Document, $SKU, $productImage, $MessageID, $index, $imageType = null)
    {
        switch ($this->_operationMode) {
            case self::OPERATIONS_CREATE:
            case self::OPERATIONS_UPDATE:
                $updatingType = 'Update';
                break;
            case self::OPERATIONS_DELETE:
                $updatingType = 'Delete';
                break;
            default:
                echo "$this->_cr createProductImageMessage call. undefined value for updatingType . $this->_cr";

                return (false);
        }
        if ($this->_debug) {
            printf("$this->_cr createProductImageMessage call \$SKU = $SKU \$productImage = %s \$MessageID = $MessageID $this->_cr", print_r($productImage, true));
        }

        if ($this->_debug) {
            $Document->formatOutput = true;
        }

        $Message = $Document->createElement('Message');
        $MessageIDX = $Document->createElement('MessageID');
        $Message->appendChild($MessageIDX);
        $MessageIDText = $Document->createTextNode($MessageID);
        $MessageIDX->appendChild($MessageIDText);
        $OperationType = $Document->createElement('OperationType', $updatingType);
        $Message->appendChild($OperationType);

        $Image = $Document->createElement('ProductImage');
        $Message->appendChild($Image);

        $SKUTag = $Image->appendChild($Document->createElement("SKU"));
        $SKU = $Document->createTextNode($SKU);
        $SKUTag->appendChild($SKU);

        if ($imageType) {
            $typeOf = $imageType;
        } elseif ($index == 0) {
            $typeOf = 'Main';
        } else {
            $typeOf = 'PT'.$index;
        }

        $ImageType = $Document->createElement('ImageType');
        $Image->appendChild($ImageType);

        $ImageTypeText = $Document->createTextNode($typeOf);
        $ImageType->appendChild($ImageTypeText);


        $ImageLocation = $Document->createElement('ImageLocation');
        $Image->appendChild($ImageLocation);

        $ImageLocationURL = $Document->createTextNode($productImage);
        $ImageLocation->appendChild($ImageLocationURL);

        $xml = $Document->saveXML($Message);

        if ($this->_debug) {
            CommonTools::p(htmlentities($xml));
        }

        return $Message;
    }

    /**
     * @param $products
     *
     * @return int|string
     */
    public function updatePriceOnly($products)
    {
        if ($this->_debug) {
            printf("$this->_cr updatePriceOnly() function. %s $this->_cr", $this->_caller());
        }

        $Submissions = $this->updatePrices($products);
        $SubmitID = 0;

        if (isset($Submissions) && is_object($Submissions)) {
            if ($this->_debug) {
                printf("$this->_cr updatePriceOnly() function. updatePrices returned : %s $this->_cr", nl2br(print_r($Submissions->SubmitFeedResult, true)));
            }

            $SubmitID = (string)$Submissions->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }

        if ($this->_debug) {
            printf("$this->_cr updatePriceInventory() function. Returns: %s $this->_cr", $SubmitID);
        }

        return ($SubmitID);
    }

    /**
     * Get all currently unshipped orders, that were placed by customer before specified date
     * @param <string> $createdAfterDate - Date in format "yyy-mm-dd" (including this date)
     * @param <string> $createdBeforeDate - [optional] Date in format "yyy-mm-dd" (excluding this date)
     * @return PlacedOrder[] Massive of instances of the PlecedOrder class,
     *                         that represents all unshipped orders
     */

    public function partiallyUpdateProduct($SKU, $ProductIDType, $ProductIDCode, $ConditionType, $ConditionNote, $updatingType)
    {
        /*Status can be All, UNSHIPPED, SHIPPED, Canceled, PartiallyShipped, Pending (Order placed, but payment not authorized, not ready for shipment)
        UNSHIPPED orders include also PartiallyShipped Orders, because this two statuses must be used together in Orders API*/
        if ($this->_debug) {
            printf("$this->_cr partiallyUpdateProduct() call. All checkings and operations will made in the partiallyUpdateProducts() function. Now an object array creation...  $this->_cr");
        }

        $products = array();
        $products[0]['SKU'] = $SKU;
        $products[0]['ProductIDType'] = $ProductIDType;
        $products[0]['ProductIDCode'] = $ProductIDCode;
        $products[0]['ConditionType'] = $ConditionType;
        $products[0]['ConditionNote'] = $ConditionNote;

        if ($this->_debug) {
            printf("$this->_cr partiallyUpdateProduct() function. Calling of the partiallyUpdateProducts() function... $this->_cr");
        }

        $data = $this->partiallyUpdateProducts($products, $updatingType);
        if ($this->_debug) {
            if ($data == false) {
                printf("$this->_cr partiallyUpdateProduct() function is finished $this->_att with an error $this->_cr");
            } else {
                printf("$this->_cr partiallyUpdateProduct() function is finished successfully here $this->_cr");
            }
        }

        return $data;
    }

    /**
     * @param $SKU
     * @param $ProductIDType
     * @param $ProductIDCode
     * @param $ConditionType
     * @param $ConditionNote
     * @param $Quantity
     * @param $Price
     *
     * @return array
     */
    public function addProduct($SKU, $ProductIDType, $ProductIDCode, $ConditionType, $ConditionNote, $Quantity, $Price)
    {
        if ($this->_debug) {
            printf("$this->_cr addProduct call. It will construct an object massive and sends it to the addProducts() function. All verifications (checkings) and operations will be performed there. $this->_cr");
        }

        if ($this->_debug) {
            printf("$this->_cr addProduct function. Creation of the object array starts here. (\$products[0]['SKU'] = \$SKU , etc.)...$this->_cr");
        }

        $products = array();
        $products[0]['SKU'] = $SKU;
        $products[0]['ProductIDType'] = $ProductIDType;
        $products[0]['ProductIDCode'] = $ProductIDCode;
        $products[0]['ConditionType'] = $ConditionType;
        $products[0]['ConditionNote'] = $ConditionNote;
        $products[0]['Quantity'] = $Quantity;
        $products[0]['Price'] = $Price;

        if ($this->_debug) {
            printf("$this->_cr addProduct function. Creation of the object array is finished. Calling addProducts() function... $this->_cr");
        }

        $data = $this->addProducts($products);
        if ($this->_debug) {
            $state = null;

            if ($data == false) {
                $state = "$this->_att with an error";
            } else {
                $state = 'successfully';
            }

            printf("$this->_cr addProduct function. Function execution is completed $state $this->_cr");
        }

        return $data;
    }

    /**
     *All parameters must be specified, except of conditionNote. You can use another functions (partiallyUpdateProducts(), updateQuantities(), updatePrices())
     * if you want to partially update products
     * You can also update (overwrite) exesting product information using this function (AddProducts) if you want to.
     *
     * @param <type> $products - massive format:
     * $products[$i]['SKU'] - string, unique product identifier in the seller account
     * $products[$i]['ProductIDType'] - (string) Product Identification type. Must be one of the fellowing:
     *                          "ISBN", "UPC", "EAN", "ASIN", "GTIN"
     * $products[$i]['ProductIDCode'] - Product Code, based on the ProductType
     *
     * $products[$i]['ConditionType'] - string, one of the following: "New",
     *      "UsedLikeNew", "UsedVeryGood", "UsedGood", "UsedAcceptable",
     *      "CollectibleLikeNew", "CollectibleVeryGood", "CollectibleGood", "CollectibleAcceptable"
     *      "Refurbished", "Club"
     *
     * $products[$i]['ConditionNote'] - any string, up to 2000 symbols
     * $products[$i]['Quantity'] - number of items. that available for selling
     * $products[$i]['Price'] - price of the product
     * @return <int[]> Web Service Submission identifiers (3), or false, if unsuccessful
     */
    public function addProducts($products)
    {
        //when we add products all fields must be not null, except of $products[$i]['ConditionNote']
        if ($this->_debug) {
            echo "addProducts() call. $this->_cr";
            echo "addProducts() function. Checking for arguments... $this->_cr";
        }

        $count_products = is_array($products) ? count($products) : 0;

        for ($i = 0; $i < $count_products; $i++) {
            if ($this->_debug) {
                echo "addProducts() function. Checking of the $i-th product $this->_cr";
            }

            if (isset($products[$i]['NoPriceExport']) && $products[$i]['NoPriceExport'] == true) {
                if ($this->_debug) {
                    printf("$this->_cr addProducts() call, skipping product: %s (NoPriceExport is sets) $this->_cr", $products[$i]['SKU']);
                }

                unset($products[$i]);
                continue;
            }

            if ($products[$i]['SKU'] == null || $products[$i]['ConditionType'] == null || $products[$i]['Quantity'] == null || $products[$i]['Price'] == null) {
                if ($this->_debug) {
                    echo "addProducts() function. $this->_att Warning. One of the fields is null (or not set) in the $i-th Product (SKU : $products[$i]['SKU']). Function skips the product and continue execution. $this->_cr";
                }

                $products[$i] = null;
                continue;
            }
            //check Quantity and Price in order to avoid partial data corruption
            if (!$this->checkQuantity($products[$i]['Quantity'])) {
                if ($this->_debug) {
                    printf('addProducts() function. %sQuantity Warning in the %d-th Product:
                         Function skips the product and continue execution (SKU : %s).%s', $this->_att, $i, $products[$i]['SKU'], $this->_cr);
                }

                $products[$i] = null;
                continue;
            }
            if (!$this->checkPrice($products[$i]['Price'])) {
                if ($this->_debug) {
                    printf('addProducts() function. Warning. %s Invalid specified price, in the %d-th Product.
                          Function skips the product and continue execution  (SKU : %s). %s', $this->_att, $i, $products[$i]['SKU'], $this->_cr);
                }

                $products[$i] = null;
                continue;
            }

            if (!$this->partiallyCheckProduct($products[$i])) {
                if ($this->_debug) {
                    printf('addProducts() function. %sWarning. %d-th product is partially incorrect. We skip it (SKU : %s). %s', $this->_att, $i, $products[$i]['SKU'], $this->_cr);
                }
                $products[$i] = null;
                continue;
            }
        }

        //if there is no products to add we terminate the function
        //we have to skip empty products
        $eproducts = array();
        if ($this->_debug) {
            $n = count($products);
            printf('addProducts() function. You sent %d products%s', $n, $this->_cr);
        }
        $count_products = is_array($products) ? count($products) : 0;
        for ($i = 0; $i < $count_products; $i++) {
            if ($products[$i] != null) {
                $eproducts[] = $products[$i];
            }
        }

        if ($this->_debug) {
            $n = count($eproducts);
            printf('addProducts() function. %d products remaining after skippings%s', $n, $this->_cr);
        }

        if (!is_array($eproducts) || count($eproducts) == 0) {
            if ($this->_debug) {
                printf('addProducts() function. No products to add (or they are all skipped !). %sStop here with an Error.%s', $this->_att, $this->_cr);
            }

            return false;
        }

        $products = $eproducts;

        $Submissions = array();
        $Submissions[0] = $this->partiallyUpdateProducts($products, 'Update', false);
        $Submissions[1] = $this->updateQuantities($products);
        $Submissions[2] = $this->updatePrices($products);
        $Submissions[3] = $this->overrideShipping($products);
        $Submissions[4] = $this->productImage($products);

        if ($Submissions[0] === false && $Submissions[1] === false && $Submissions[2] === false) {
            if ($this->_debug) {
                printf("$this->_cr addProducts() function. $this->_att Error. There are no products to update. All products are skipped. Function terminated. $this->_cr");
            }

            return false;
        }
        $SubmitIDs = array();

        if (!isset($Submissions[0]->SubmitFeedResult->FeedSubmissionInfo)) {
            if ($this->_debug) {
                printf("$this->_cr addProducts() function. $this->_att Error. No submission feed data. Return is: %s $this->_cr", nl2br(print_r($Submissions[0], true)));
            }

            return (false);
        } elseif (!isset($Submissions[0]->SubmitFeedResult)) {
            if ($this->_debug) {
                printf("$this->_cr addProducts() function. $this->_att Error. No submission feed info. Return is: %s $this->_cr", nl2br(print_r($Submissions[0], true)));
            }

            return (false);
        }

        if (isset($Submissions[0]) && is_object($Submissions[0])) {
            if ($this->_debug) {
                printf("$this->_cr addProducts() function. partiallyUpdateProducts returned : %s $this->_cr", nl2br(print_r($Submissions[0]->SubmitFeedResult, true)));
            }

            $SubmitIDs['products'] = (string)$Submissions[0]->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }
        if (isset($Submissions[1]) && is_object($Submissions[1])) {
            if ($this->_debug) {
                printf("$this->_cr addProducts() function. updateQuantities returned : %s $this->_cr", nl2br(print_r($Submissions[1]->SubmitFeedResult, true)));
            }

            $SubmitIDs['inventory'] = (string)$Submissions[1]->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }
        if (isset($Submissions[2]) && is_object($Submissions[2])) {
            if ($this->_debug) {
                printf("$this->_cr addProducts() function. updatePrices returned : %s $this->_cr", nl2br(print_r($Submissions[2]->SubmitFeedResult, true)));
            }

            $SubmitIDs['prices'] = (string)$Submissions[2]->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }
        if (isset($Submissions[3]) && is_object($Submissions[3])) {
            if ($this->_debug) {
                printf("$this->_cr addProducts() function. overrideShipping returned : %s $this->_cr", nl2br(print_r($Submissions[3]->SubmitFeedResult, true)));
            }

            $SubmitIDs['overrides'] = (string)$Submissions[3]->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }

        if ($this->_debug) {
            printf("$this->_cr addProducts() function. Returns: %s $this->_cr", nl2br(print_r($SubmitIDs, true)));
        }

        if (isset($Submissions[4]) && is_object($Submissions[4])) {
            if ($this->_debug) {
                printf("$this->_cr addProducts() function. productImage returned : %s $this->_cr", nl2br(print_r($Submissions[4]->SubmitFeedResult, true)));
            }

            $SubmitIDs['images'] = (string)$Submissions[4]->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        }

        if ($this->_debug) {
            printf("$this->_cr addProducts() function. Returns: %s $this->_cr", nl2br(print_r($SubmitIDs, true)));
        }

        return ($SubmitIDs);
    }

    /**
     *Update condition of existing single product
     * @param <type> $cond - object massive
     * $cond[$i]['SKU']
     * $cond[$i]['ConditionType']
     * $cond[$i]['ConditionNote']
     * @return <int> - SubmissionID or false if unsuccessful
     */
    public function updateCondition($SKU, $ConditionType, $ConditionNote)
    {
        if ($this->_debug) {
            printf("$this->_cr updateCondition() function. starts here $this->_cr");
        }

        $products = array();
        $products[0]['SKU'] = $SKU;
        $products[0]['ConditionType'] = $ConditionType;
        $products[0]['ConditionNote'] = $ConditionNote;

        if ($this->_debug) {
            printf("$this->_cr  function. $this->_cr");
        }

        $data = $this->partiallyUpdateProducts($products);

        if ($this->_debug) {
            if ($data == false) {
                printf("$this->_cr updateCondition() function is finished $this->_att with an error $this->_cr");
            } else {
                printf("$this->_cr updateCondition() function is finished successfully here $this->_cr");
            }
        }

        return $data;
    }

    /**
     * @param $SKU
     * @param $Quantity
     *
     * @return mixed
     */
    public function updateQuantity($SKU, $Quantity)
    {
        if ($this->_debug) {
            printf("$this->_cr updateQuantity() call, creating an object array... $this->_cr");
        }

        $products = array();
        $products[0]['SKU'] = $SKU;
        $products[0]['Quantity'] = $Quantity;

        if ($this->_debug) {
            printf("$this->_cr updateQuantity() function. Calling the updateQuantities() function. Product SKU: %s - Quantity: %d $this->_cr", $SKU, $Quantity);
        }

        $data = $this->updateQuantities($products);
        if ($this->_debug) {
            if ($data === false || $data === null) {
                if ($this->_debug) {
                    printf("$this->_cr updateQuantity() function is finished with $this->_att an error. $this->_cr");
                }
            }
        }

        return $data;
    }

    /**
     * @param $SKU
     * @param $Price
     *
     * @return bool|SimpleXMLElement
     */
    public function updatePrice($SKU, $Price)
    {
        if ($this->_debug) {
            printf("$this->_cr updatePrice() call. $this->_cr");
        }

        $products = array();
        $products[0]['SKU'] = $SKU;
        $products[0]['Price'] = $Price;

        if ($this->_debug) {
            printf("$this->_cr updatePrice() function. updatePrices() function is calling here. SKU: %s Price: %s $this->_cr", $SKU, $Price);
        }

        $data = $this->updatePrices($products);

        if ($this->_debug) {
            if ($data === false || $data === null) {
                printf("$this->_cr updatePrice() function is finished $this->_att with an error %s $this->_cr", $this->_caller());
            }
        }

        return $data;
    }

    /**
     * @param $product
     *
     * @return bool|SimpleXMLElement
     */
    public function deleteProduct($product)
    {
        if ($this->_debug) {
            //$skuu = $product[$i]['SKU'];
            printf("$this->_cr deleteProduct() call \$product['SKU'] = ?? .All checks and operations will be performed after creation of the array $this->_cr");
        }
        if ($this->_debug) {
            printf("$this->_cr deleteProduct() function. creates an array... $this->_cr");
        }

        $products = array();
        $products[0] = $product;
        if ($this->_debug) {
            printf("$this->_cr deleteProduct() function. finish to create array. Calling deleteProducts() function... $this->_cr");
        }

        $State = $this->deleteProducts($products);
        if ($State == false) {
            if ($this->_debug) {
                printf("$this->_cr deleteProduct() function is finished with $this->_att an error. $this->_cr");
            }
        }

        return $State;
    }

    /**
     *Delete products from seller account
     * @param <type[]> $products - array of unique products
     * @return <int> - FeedSubmissionId or false if an error occured
     */
    public function deleteProducts($products)
    {
        if ($this->_debug) {
            printf("$this->_cr deleteProducts() call. $this->_cr");
        }

        $count_products = is_array($products) ? count($products) : 0;
        for ($i = 0; $i < $count_products; $i++) {
            if (!$this->checkSKU($products[$i]['SKU'])) {
                if ($this->_debug) {
                    printf("$this->_cr deleteProducts() function. $this->_att Warning. You sent product with incorrect SKU. \$products[$i]['SKU'] . Please verify it. We skip this product $this->_cr");
                }

                $products[$i] = null;
            }
        }

        $eSKUs = array();
        if ($this->_debug) {
            $n = is_array($products) ? count($products) : 0;
            printf("$this->_cr deleteProducts() function. You sent $n Products $this->_cr");
        }

        $count_products = is_array($products) ? count($products) : 0;

        for ($i = 0; $i < $count_products; $i++) {
            if ($products[$i]['SKU'] != null) {
                $eSKUs[] = $products[$i]['SKU'];
            }
        }

        if ($this->_debug) {
            $n = count($eSKUs);
            $n = is_array($eSKUs) ? count($eSKUs) : 0;
            printf("$this->_cr deleteProducts() function. After skipping remains $n SKUs $this->_cr");
        }
        if (count($eSKUs) == 0) {
            if ($this->_debug) {
                printf("$this->_cr deleteProducts() function. $this->_att Error. There is no products to delete. Function terminated. $this->_cr");
            }

            return false;
        }
        $SKUs = $eSKUs;
        $count_sku = is_array($eSKUs) ? count($eSKUs) : 0;

        $xml = new DOMDocument();
        $Messages = array();
        for ($i = 0; $i < $count_sku; $i++) {
            if ($this->_debug) {
                printf("$this->_cr deleteProducts() function. Creation of the $i-th message. $this->_cr");
            }

            $Messages[$i] = $this->createDelMessage($xml, $i + 1, $SKUs[$i]);
        }
        if ($this->_debug) {
            printf("$this->_cr deleteProducts() function. Calling the CreateFeed() function here $this->_cr");
        }

        $feedDOM = $this->CreateFeed($xml, 'Product', $Messages);

        if ($this->_debug) {
            $feedDOM->formatOutput = true;
        }

        $feed = $feedDOM->saveXML();

        if ($this->_debug) {
            CommonTools::p("deleteProducts() function. Start processing feed here");
            CommonTools::p(htmlentities($feed));
        }

        $data = $this->processFeed('_POST_PRODUCT_DATA_', $feed);

        if ($this->_debug) {
            if ($data === false || $data === null) {
                printf("$this->_cr deleteProducts() function is finished $this->_att with an error $this->_cr");
            } else {
                printf("$this->_cr deleteProducts() function is finished successfully here $this->_cr");
            }
        }

        return $data;
    }

    /**
     * @param DOMDocument $Document
     * @param $MessageID
     * @param $SKU
     *
     * @return DOMElement
     */
    private function createDelMessage(DOMDocument $Document, $MessageID, $SKU)
    {
        if ($this->_debug) {
            printf("$this->_cr createDelMessage call \$SKU = $SKU \$MessageID = $MessageID $this->_cr");
        }

        $xml = $Document;
        $message = $xml->createElement('Message');
        $MessageIDX = $xml->createElement('MessageID');
        $message->appendChild($MessageIDX);
        $MessageIDText = $xml->createTextNode($MessageID);
        $MessageIDX->appendChild($MessageIDText);
        $OperationType = $xml->createElement('OperationType');
        $message->appendChild($OperationType);
        $OperationTypeText = $xml->createTextNode('Delete');
        $OperationType->appendChild($OperationTypeText);
        $Product = $xml->createElement('Product');
        $message->appendChild($Product);
        $SKUX = $xml->createElement('SKU');
        $Product->appendChild($SKUX);
        $SKUText = $xml->createTextNode($SKU);
        $SKUX->appendChild($SKUText);

        return $message;
    }

    /**
     * @param $amazonOrderId
     *
     * @return bool|PlacedOrder
     */
    public function getOrderById($amazonOrderId)
    {
        $params = array();
        $params['Action'] = 'GetOrder';
        $params['AmazonOrderId.Id.1'] = $amazonOrderId;
        $params['MarketplaceId.Id.1'] = $this->mpid;
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';

        // Amazon Europe
        //
        if (isset($this->MarketPlaces) && count($this->MarketPlaces)) {
            $i = 1;
            foreach ($this->MarketPlaces as $marketPlace) {
                if (empty($marketPlace)) {
                    continue;
                }

                if ($marketPlace == $this->mpid) {
                    continue;
                }

                $i++;
                $params['MarketplaceId.Id.'.$i] = $marketPlace;
            }
        }

        $data = $this->simpleCallWS($params, 'Orders', true);

        if ($data === false || $data === null) {
            if ($this->_debug) {
                printf("$this->_cr $this->_att An error happened during GetOrder request $this->_cr");
            }

            return false;
        }

        if ($this->_debug) {
            $d = $data->saveXML();
            printf("$this->_cr getOrderById function got following response from the Orders WebService: $this->_cr $d $this->_cr");
        }

        $data = $data->GetOrderResult->Orders;

        if ($data->Order[0] === null) {
            if ($this->_debug) {
                printf("$this->_cr getOrderById function. $this->_att No orders available (It seems, that you sent incorrect OrderId). Function returns false $this->_cr");
            }

            return false; // no orders available
        }

        $itemsXml = $this->getOrderItemsXml($data->Order[0]->AmazonOrderId);

        if ($itemsXml === false) {
            if ($this->_debug) {
                printf("$this->_cr $this->_att An error happened during items xml request $this->_cr");
            }

            return false;
        }

        if ($this->_debug == true) {
            $d = htmlentities($itemsXml->saveXML());
            CommonTools::p("getOrderById function(). We get next Item xml from the order:");
            CommonTools::p($d);
        }

        $ItemsSimpleXMLElement = $itemsXml->ListOrderItemsResult->OrderItems;

        $Order = new PlacedOrder($data->Order[0], $ItemsSimpleXMLElement, $this->_debug);

        return $Order;
    }

    /**
     * Get ordered items (products) from the concrete order
     * @param <string> $AmazonOrderId
     * @return <string>Xml presented in the string
     */
    private function getOrderItemsXml($AmazonOrderId)
    {
        $params = array();
        $params['Action'] = 'ListOrderItems';
        $params['AmazonOrderId'] = $AmazonOrderId;
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';

        $response = $this->simpleCallWS($params, 'Orders', true);

        if ($this->_debug) {
            $d = null;
            if (!($response === false)) {
                $d = htmlentities($response->saveXML());
            } else {
                $d = false;
            }
            CommonTools::p("getOrderItemsXml() function. WebService response is:");
            CommonTools::p($d);
        }

        return $response;
    }

    /**
     * @param $amazonOrderId
     *
     * @return array|bool|null
     */
    public function getOrderItems($amazonOrderId)
    {
        $itemsXml = null;
        $pass = true;

        if ($this->demo) {
            $itemsXml = $this->returnDemo(__FUNCTION__, $amazonOrderId);
            $pass = false;
        }

        if ($itemsXml == null) {
            $itemsXml = $this->getOrderItemsXml($amazonOrderId);

            if ($this->demo && !$pass) {
                $this->saveDemo(__FUNCTION__, $amazonOrderId, $itemsXml);
            }
        }

        if ($itemsXml === false) {
            if ($this->_debug) {
                printf("$this->_cr $this->_att $this->_cr getOrderItems function(). An error happened during items xml request $this->_cr");
            }

            return false;
        }

        if ($this->_debug == true) {
            $d = htmlentities($itemsXml->saveXML());
            CommonTools::p("getOrderItems function(). We get next Item xml from the order:");
            CommonTools::p($d);
        }

        if (!isset($itemsXml->ListOrderItemsResult->OrderItems)) {
            if ($this->_debug == true) {
                CommonTools::p("getOrderItems function(). Unable to fetch items returned value is:");
                CommonTools::p($itemsXml);
            }

            return (false);
        }
        $ItemsSimpleXMLElement = $itemsXml->ListOrderItemsResult->OrderItems;

        if ($ItemsSimpleXMLElement !== null) {
            $Items = array();
            for ($i = 0; $i > -1; $i++) {
                if ($ItemsSimpleXMLElement->OrderItem[$i] !== null) {
                    $Items[$i] = new OrderedItem($ItemsSimpleXMLElement->OrderItem[$i]);
                } else {
                    break;
                }
            }
        } else {
            $Items = null;
        }

        return ($Items);
    }


    /**
     * @param $amazonOrderIds
     *
     * @return array|bool
     */
    public function getOrders($amazonOrderIds)
    {
        $data = null;
        $params = array();
        $params['Action'] = 'GetOrder';
        $params['MarketplaceId.Id.1'] = $this->mpid;
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';

        // Amazon Europe
        //
        if (isset($this->MarketPlaces) && is_array($this->MarketPlaces) && count($this->MarketPlaces)) {
            $i = 1;
            foreach ($this->MarketPlaces as $marketPlace) {
                if (empty($marketPlace)) {
                    continue;
                }

                if ($marketPlace == $this->mpid) {
                    continue;
                }

                $i++;
                $params['MarketplaceId.Id.'.$i] = $marketPlace;
            }
        }

        $i = 0;

        if (is_array($amazonOrderIds) && count($amazonOrderIds)) {
            foreach ($amazonOrderIds as $amazonOrderId) {
                if (empty($amazonOrderId)) {
                    continue;
                }

                $i++;
                $params['AmazonOrderId.Id.'.$i] = $amazonOrderId;
                if ($i >= 50) {
                    break;
                }
            }
        }

        if (!$i) {
            if ($this->_debug) {
                printf("%s/%s getOrders ERROR $this->_att count is null $this->_cr", basename(__FILE__), __LINE__);
            }

            return false;
        }

        $pass = true;

        if ($this->demo) {
            if (!$data = $this->returnDemo(__FUNCTION__)) {
                $pass = false;
            }
        }

        if (is_null($data)) {
            $data = $this->simpleCallWS($params, 'Orders', true);
        }

        if (!$pass && $this->demo && !is_null($data)) {
            $this->saveDemo(__FUNCTION__, null, $data);
        }

        if ($data === false || $data === null) {
            if ($this->_debug) {
                printf("$this->_cr $this->_att An error happened during GetOrder request $this->_cr");
            }

            return false;
        }

        if ($this->_debug) {
            $d = $data->saveXML();
            printf("$this->_cr getOrders function got following response from the Orders WebService: $this->_cr $d $this->_cr");
            echo self::htmlTag('pre', array('class' => 'amazon-xml')) . htmlentities($d) . self::htmlTag('/pre');
        }

        if (isset($data->ListOrdersResult->NextToken)) {
            $nextToken = $data->ListOrdersResult->NextToken;
        } else {
            $nextToken = null;
        }

        if (isset($data->GetOrderResult)) {
            $data = $data->GetOrderResult->Orders;
        }

        if ($data === null) {
            printf("$this->_cr getOrders : No order available $this->_cr");

            return false; // no orders available
        }

        $Orders = array();
        if ($this->_debug == true) {
            printf("$this->_cr getOrders function(). Start creating orders class instances. $this->_cr");
        }

        for ($i = 0; $i > -1; $i++) {
            if (!isset($data->Order)) {
                if ($this->_debug == true) {
                    printf("$this->_cr getOrders - Error or No Pending Order");
                }

                break;
            }
            if ($data->Order[$i] === null) {
                if ($i == 0) {
                    printf("$this->_cr getOrders : No order available (2) $this->_cr");

                    return false; // no orders available
                }
                break; //orders ended
            }
            if ($this->_debug) {
                CommonTools::p(sprintf('Order Fetched: %s'.$this->_cr, nl2br(print_r($data->Order[$i], true))));
            }

            $Orders[$i] = new PlacedOrder($data->Order[$i], null, $this->_debug);
        }
        while ($nextToken) {
            $nextToken = $this->GetOrdersByNextToken($nextToken, $Orders);
        }

        return $Orders;
    }

    /**
     * @param $token
     * @param $Orders
     *
     * @return bool|null
     */
    public function GetOrdersByNextToken($token, &$Orders)
    {
        if ($this->_debug == true) {
            printf("$this->_cr GetOrdersByNextToken call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'ListOrdersByNextToken';
        $params['NextToken'] = $token;
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['MarketplaceId.Id.1'] = $this->mpid;

        if ($this->_debug == true) {
            printf("$this->_cr GetOrdersByNextToken function. Start request - Token: %s $this->_cr", $token);
        }

        $data = $this->simpleCallWS($params, 'Orders', true);

        if ($this->_debug) {
            if ($data instanceof SimpleXMLElement) {
                $data = htmlentities($data->asXML());
                CommonTools::p($data);
            } else {
                print_r($data);
            }
        }

        if (isset($data->ListOrdersByNextTokenResult->NextToken)) {
            $nextToken = $data->ListOrdersByNextTokenResult->NextToken;
        } else {
            $nextToken = null;
        }

        if (isset($data->ListOrdersByNextTokenResult)) {
            $data = $data->ListOrdersByNextTokenResult->Orders;
        }

        if ($data === null) {
            // no orders available
            return false;
        }

        if ($this->_debug == true) {
            printf("$this->_cr GetOrdersByNextToken function(). Start creating orders class instances. $this->_cr");
        }

        $count_orders = is_array($Orders) ? count($Orders) : 0;
        for ($i = $count_orders, $j = 0; $i > -1; $i++, $j++) {
            if (!isset($data->Order) || $data->Order[$j] === null) {
                if ($j == 0) {
                    // no orders available
                    return false;
                }
                break; //orders ended
            }
            $Orders[$i] = new PlacedOrder($data->Order[$j], null, $this->_debug);
        }

        return ($nextToken);
    }

    /**
     * @param $SubmissionId
     *
     * @return bool|SimpleXMLElement
     */
    public function getFeedSubmissionResult($SubmissionId)
    {
        if ($this->_debug == true) {
            printf("$this->_cr getFeedSubmissionResult call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'GetFeedSubmissionResult';
        $params['Marketplace'] = $this->mpid;
        $params['Merchant'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['FeedSubmissionId'] = $SubmissionId;

        $response = $this->simpleCallWS($params, 'Feeds', true);

        if (!$response) {
            if ($this->_debug) {
                printf("%s/%s getFeedSubmissionResult ERROR $this->_att response is false or null - response is: %s $this->_cr", basename(__FILE__), __LINE__, nl2br(print_r($response, true)));
            }

            return (false);
        }

        if (!is_object($response)) {
            if ($this->_debug) {
                printf("%s/%s getFeedSubmissionResult ERROR $this->_att response must be an object - response is: %s $this->_cr", basename(__FILE__), __LINE__, nl2br(print_r($response, true)));
            }

            return (false);
        }

        return ($response);
    }

    /**
     * @param $code
     *
     * @return array|bool
     */
    public function getASIN($code)
    {
        $params = array();
        $params['Action'] = 'ListMatchingProducts';
        $params['MarketplaceId'] = $this->mpid;
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['Query'] = $code;

        $ASINs = array();
        $data = $this->_callWSs('Products', $params);

        if ($data === false) {
            if ($this->_debug) {
                printf("$this->_cr $this->_att An error happened during getASIN request $this->_cr");
            }

            return false;
        }

        if (!is_object($data)) {
            if ($this->_debug) {
                printf("$this->_cr $this->_att getASIN - Unexpected data: %s $this->_cr", nl2br(print_r($data, true)));
            }

            return false;
        }

        if (!isset($data->ListMatchingProductsResult) || !isset($data->ListMatchingProductsResult->Products)) {
            if ($this->_debug) {
                printf("$this->_cr $this->_att getASIN - No Matching Products: %s $this->_cr", nl2br(print_r($data, true)));
            }

            return false;
        }

        if ($data->ListMatchingProductsResult->Products) {
            foreach ($data->ListMatchingProductsResult->Products as $product_list) {
                foreach ($product_list->Product as $product) {
                    if ($this->_debug) {
                        echo nl2br(print_r($product->asXML(), true));
                    }

                    if (isset($product->Identifiers->MarketplaceASIN)) {
                        foreach ($product->Identifiers->MarketplaceASIN as $ASIN_Entity) {
                            $ASINs[] = (string)$ASIN_Entity->ASIN;
                        }
                    }
                }
            }
        } else {
            if ($this->_debug) {
                printf("$this->_cr $this->_att getASIN - No Products: %s $this->_cr", nl2br(print_r($data, true)));
            }

            return false;
        }

        return $ASINs;
    }

    /**
     * @param $Date
     * @param int $maxQueries
     *
     * @return array|bool
     */
    public function ListAllFulfillmentOrders($Date, $maxQueries = 10)
    {
        $params = array();
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['Version'] = '2010-10-01';

        $querytime = strtotime($Date);
        $now = time();
        $last_month = time() - (86400 * 30);

        if ($querytime > $now || $querytime < $last_month) {
            if ($this->_debug) {
                printf('ListAllFulfillmentOrders() function. %sWarning. Wrong Date.%s', $this->_att, $Date, $this->_cr);
            }

            return (false);
        }

        $params['QueryStartDateTime'] = gmdate('Y-m-d\TH:i:s\Z', $querytime);

        $dataset = array();
        $index = 0;
        $nextToken = 1;

        for ($i = 0; $i < $maxQueries && $nextToken; $i++) {
            if ($index == 0) {
                $params['Action'] = 'ListAllFulfillmentOrders';
                $data = $this->_callWSs('FulfillmentOutboundShipment', $params, null, true);
                $responseItem = 'ListAllFulfillmentOrdersResult';
            } else {
                $params['Action'] = 'ListAllFulfillmentOrdersByNextToken';
                $params['NextToken'] = $nextToken;
                $data = $this->_callWSs('FulfillmentOutboundShipment', $params);
                $responseItem = 'ListAllFulfillmentOrdersByNextTokenResult';
            }
            if ($data === false) {
                if ($this->_debug) {
                    printf("$this->_cr $this->_att An error happened during ListAllFulfillmentOrders request $this->_cr");
                }

                return false;
            }

            if (isset($data->{$responseItem}->NextToken) && $data->{$responseItem}->NextToken) {
                $nextToken = $data->{$responseItem}->NextToken;
            } else {
                $nextToken = null;
            }

            if (!isset($data->{$responseItem})) {
                if ($this->_debug) {
                    printf("ListAllFulfillmentOrders() function failed $this->_att An error happened during request... $this->_cr");
                }

                return false;
            }

            if ($this->_debug) {
                if ($data instanceof SimpleXMLElement) {
                    CommonTools::p(htmlentities(print_r($data->asXML(), true)));
                } else {
                    echo nl2br(print_r($data, true));
                }
            }

            if (!isset($data->{$responseItem})) {
                return false;
            }

            if (!isset($data->{$responseItem}->FulfillmentOrders->member)) {
                if ($this->_debug) {
                    printf("ListAllFulfillmentOrders()  $this->_att empty inventory... $this->_cr");
                }

                return false;
            }
            $dataset[$index++] = $data->{$responseItem}->FulfillmentOrders;
        }

        return ($dataset);
    }

    /**
     * @param $orderId
     *
     * @return bool|SimpleXMLElement
     */
    public function cancelFulfillmentOrder($orderId)
    {
        $params = array();
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['Action'] = 'CancelFulfillmentOrder';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['Version'] = '2010-10-01';
        $params['SellerFulfillmentOrderId'] = $orderId;

        $data = $this->_callWSs('FulfillmentOutboundShipment', $params, null, true);

        if ($data === false) {
            if ($this->_debug) {
                printf("$this->_cr $this->_att An error happened during GetFulfillmentOrder request $this->_cr");
            }

            return false;
        }

        if ($this->_debug) {
            if ($data instanceof SimpleXMLElement) {
                CommonTools::p(htmlentities(print_r($data->asXML(), true)));
            } else {
                echo nl2br(print_r($data, true));
            }
        }

        if (isset($data->Error->Code)) {
            if ($this->_debug) {
                printf("GetFulfillmentOrder()  $this->_att some error in the request answer... $this->_cr");
            }
        } elseif (!isset($data->ResponseMetadata->RequestId)) {
            if ($this->_debug) {
                printf("GetFulfillmentOrder()  $this->_att some other error in the request answer... $this->_cr");
            }

            return false;
        }

        return ($data);
    }

    /**
     * @param $orderId
     *
     * @return bool|SimpleXMLElement
     */
    public function getFulfillmentOrder($orderId)
    {
        $params = array();
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['Action'] = 'GetFulfillmentOrder';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['Version'] = '2010-10-01';
        $params['SellerFulfillmentOrderId'] = $orderId;

        $data = $this->_callWSs('FulfillmentOutboundShipment', $params, null, true);

        if ($data === false) {
            if ($this->_debug) {
                printf("$this->_cr $this->_att An error happened during GetFulfillmentOrder request $this->_cr");
            }

            return false;
        }

        if ($this->_debug) {
            if ($data instanceof SimpleXMLElement) {
                CommonTools::p(htmlentities(print_r($data->asXML(), true)));
            } else {
                echo nl2br(print_r($data, true));
            }
        }

        if (isset($data->Error->Code)) {
            if ($this->_debug) {
                printf("GetFulfillmentOrder()  $this->_att some error in the request answer... $this->_cr");
            }
        } elseif (!isset($data->ResponseMetadata->RequestId)) {
            if ($this->_debug) {
                printf("GetFulfillmentOrder()  $this->_att some other error in the request answer... $this->_cr");
            }

            return false;
        }

        return ($data);
    }

    /**
     * @param $PackageNumber
     *
     * @return bool|SimpleXMLElement
     */
    public function getPackageTrackingDetails($PackageNumber)
    {
        $params = array();
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['Action'] = 'GetPackageTrackingDetails';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['Version'] = '2010-10-01';
        $params['PackageNumber'] = $PackageNumber;

        $data = $this->_callWSs('FulfillmentOutboundShipment', $params, null, true);

        if ($data === false) {
            if ($this->_debug) {
                printf("$this->_cr $this->_att An error happened during GetFulfillmentOrder request $this->_cr");
            }

            return false;
        }

        if ($this->_debug) {
            if ($data instanceof SimpleXMLElement) {
                CommonTools::p(htmlentities(print_r($data->asXML(), true)));
            } else {
                echo nl2br(print_r($data, true));
            }
        }

        if (isset($data->Error->Code)) {
            if ($this->_debug) {
                printf("GetFulfillmentOrder()  $this->_att some error in the request answer... $this->_cr");
            }
        } elseif (!isset($data->ResponseMetadata->RequestId)) {
            if ($this->_debug) {
                printf("GetFulfillmentOrder()  $this->_att some other error in the request answer... $this->_cr");
            }

            return false;
        }

        return ($data);
    }

    /**
     * @param $order
     * @param bool $returnXML
     *
     * @return bool|string
     */
    public function createFulfillmentOrder($order, $returnXML = false)
    {
        $params = array();
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['Action'] = 'CreateFulfillmentOrder';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['Version'] = '2010-10-01';

        // Testing all required fields are present
        //
        $pass = true;
        foreach (array(
                     'SellerFulfillmentOrderId',
                     'DisplayableOrderId',
                     'DisplayableOrderDateTime',
                     'DisplayableOrderComment',
                     'ShippingSpeedCategory',
                     'DestinationAddress',
                     'Items'
                 ) as $field) {
            if (!isset($order[$field]) || empty($order[$field]) || (is_array($order[$field]) && !count($order[$field]))) {
                $pass = false;
                if ($this->_debug) {
                    printf('createFulfillmentOrder() function. %sError. One of the required parameters is missing: %s.%s', $this->_att, $field, $this->_cr);
                }
            }
        }
        if (!$pass) {
            return (false);
        }

        $params['SellerFulfillmentOrderId'] = mb_substr($order['SellerFulfillmentOrderId'], 0, 40);
        $params['DisplayableOrderId'] = trim(mb_substr($order['DisplayableOrderId'], 0, 40));
        $params['DisplayableOrderDateTime'] = $order['DisplayableOrderDateTime'];
        $params['DisplayableOrderComment'] = mb_substr($order['DisplayableOrderComment'], 0, 1000);
        $params['ShippingSpeedCategory'] = $order['ShippingSpeedCategory'];
        /* Enum:
        • Standard
        • Expedited
        • Priority
        */

        // Emails
        if (isset($order['NotificationEmailList']) && is_array($order['NotificationEmailList']) && count($order['NotificationEmailList'])) {
            $count = 1;
            foreach ($order['NotificationEmailList'] as $email) {
                if (function_exists('filter_var') && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                $params['NotificationEmailList.member.'.$count] = Tools::substr($email, 0, 64);
                $count++;
            }
        }

        // Format Address :
        //
        $pass = true;
        foreach (array('Name', 'Line1', 'City', 'StateOrProvinceCode', 'CountryCode') as $field) {
            if (!isset($order['DestinationAddress'][$field]) || empty($order['DestinationAddress'][$field])) {
                $pass = false;
                if ($this->_debug) {
                    printf('createFulfillmentOrder() function. %sError. One of the Address required parameters is missing: %s.%s', $this->_att, $field, $this->_cr);
                }
            }
        }
        if (!$pass) {
            return (false);
        }

        $params['DestinationAddress.Name'] = mb_substr($order['DestinationAddress']['Name'], 0, 50);
        $params['DestinationAddress.Line1'] = mb_substr($order['DestinationAddress']['Line1'], 0, 50);

        if (isset($order['DestinationAddress']['Line2']) && !empty($order['DestinationAddress']['Line2'])) {
            $params['DestinationAddress.Line2'] = mb_substr($order['DestinationAddress']['Line2'], 0, 60);
        }

        if (isset($order['DestinationAddress']['Line3']) && !empty($order['DestinationAddress']['Line3'])) {
            $params['DestinationAddress.Line3'] = mb_substr($order['DestinationAddress']['Line3'], 0, 60);
        }

        if (isset($order['DestinationAddress']['DistrictOrCounty']) && !empty($order['DestinationAddress']['DistrictOrCounty'])) {
            $params['DestinationAddress.DistrictOrCounty'] = mb_substr($order['DestinationAddress']['DistrictOrCounty'], 0, 150);
        }

        if (isset($order['DestinationAddress']['City']) && !empty($order['DestinationAddress']['City'])) {
            $params['DestinationAddress.City'] = mb_substr($order['DestinationAddress']['City'], 0, 50);
        }

        if (isset($order['DestinationAddress']['StateOrProvinceCode']) && !empty($order['DestinationAddress']['StateOrProvinceCode'])) {
            $params['DestinationAddress.StateOrProvinceCode'] = mb_substr($order['DestinationAddress']['StateOrProvinceCode'], 0, 50);
        }

        if (isset($order['DestinationAddress']['CountryCode']) && !empty($order['DestinationAddress']['CountryCode'])) {
            $params['DestinationAddress.CountryCode'] = Tools::strtoupper(mb_substr($order['DestinationAddress']['CountryCode'], 0, 2));
        }

        if (isset($order['DestinationAddress']['PostalCode']) && !empty($order['DestinationAddress']['PostalCode'])) {
            $params['DestinationAddress.PostalCode'] = mb_substr($order['DestinationAddress']['PostalCode'], 0, 20);
        }

        if (isset($order['DestinationAddress']['PhoneNumber']) && !empty($order['DestinationAddress']['PhoneNumber'])) {
            $params['DestinationAddress.PhoneNumber'] = mb_substr($order['DestinationAddress']['PhoneNumber'], 0, 20);
        }

        if (!is_array($order['Items']) || !count($order['Items'])) {
            if ($this->_debug) {
                printf('createFulfillmentOrder() function. %sWarning. Items: empty list - nothing to do.%s', $this->_att, $this->_cr);
            }

            return (false);
        }

        $itemCount = 0;
        foreach ($order['Items'] as $Item) {
            if (!$this->checkSKU($Item['SKU'])) {
                printf('createFulfillmentOrder() function. Warning. SKU: invalid value. Skipping the product and continue.%s', $this->_cr);
                continue;
            }

            // Check for required field
            //
            $pass = true;
            foreach (array('SellerFulfillmentOrderItemId', 'Quantity') as $field) {
                if (!isset($Item[$field]) || empty($Item[$field])) {
                    $pass = false;
                    printf('createFulfillmentOrder() function. %sError. One of the Item required parameters is missing: %s.%s', $this->_att, $field, $this->_cr);
                }
            }
            if (!$pass) {
                continue;
            }

            $itemCount++;

            $params['Items.member.'.$itemCount.'.SellerSKU'] = $Item['SKU'];
            $params['Items.member.'.$itemCount.'.SellerFulfillmentOrderItemId'] = $Item['SellerFulfillmentOrderItemId'];
            $params['Items.member.'.$itemCount.'.Quantity'] = $Item['Quantity'];

            if (isset($Item['PerUnitDeclaredValue.Value']) && !empty($Item['PerUnitDeclaredValue.Value'])) {
                $params['Items.member.'.$itemCount.'.PerUnitDeclaredValue.Value'] = $Item['PerUnitDeclaredValue.Value'];
                $params['Items.member.'.$itemCount.'.PerUnitDeclaredValue.CurrencyCode'] = $Item['PerUnitDeclaredValue.CurrencyCode'];
            }
            if (isset($Item['GiftMessage']) && !empty($Item['GiftMessage'])) {
                $params['Items.member.'.$itemCount.'.GiftMessage'] = mb_substr($Item['GiftMessage'], 0, 512);
            }

            if (isset($Item['DisplayableComment']) && !empty($Item['DisplayableComment'])) {
                $params['Items.member.'.$itemCount.'.DisplayableComment'] = mb_substr($Item['DisplayableComment'], 0, 250);
            }

            if (isset($Item['GiftMessage']) && !empty($Item['GiftMessage'])) {
                $params['Items.member.'.$itemCount.'.GiftMessage'] = mb_substr($Item['GiftMessage'], 0, 512);
            }

            if (isset($Item['FulfillmentNetworkSKU']) && !empty($Item['FulfillmentNetworkSKU'])) {
                $params['Items.member.'.$itemCount.'.FulfillmentNetworkSKU'] = $Item['FulfillmentNetworkSKU'];
            }

            if (isset($Item['OrderItemDisposition']) && !empty($Item['OrderItemDisposition'])) {
                $params['Items.member.'.$itemCount.'.OrderItemDisposition'] = $Item['OrderItemDisposition'];
            }
        }

        if (is_array($order['Items']) && count($order['Items']) != $itemCount) {
            printf('createFulfillmentOrder() function. %sError. Items Count: Expected item count differ, order aborted.%s', $this->_att, $this->_cr);

            return (false);
        }

        $data = $this->_callWSs('FulfillmentOutboundShipment', $params, null, true);

        if ($returnXML) {
            //
            return ($data);
        } else {
            if ($data === false) {
                if ($this->_debug) {
                    printf("$this->_cr $this->_att An error happened during CreateFulfillmentOrder request $this->_cr");
                }

                return false;
            }

            if ($this->_debug) {
                if ($data instanceof SimpleXMLElement) {
                    CommonTools::p(htmlentities(print_r($data->asXML(), true)));
                } else {
                    echo nl2br(print_r($data, true));
                }
            }

            if (!isset($data->ResponseMetadata->RequestId)) {
                if ($this->_debug) {
                    printf("createFulfillmentOrder()  $this->_att some error in the request answer... $this->_cr");
                }

                return false;
            }

            return ((string)$data->ResponseMetadata->RequestId);
        }
    }

    /**
     * @param $SKUs
     *
     * @return array|bool
     */
    public function ListInventoryBySKU($SKUs)
    {
        $params = array();

        if (!is_array($SKUs) || !count($SKUs)) {
            if ($this->_debug) {
                printf('ListInventorySKU() function. %sWarning. SKU: empty list - nothing to do.%s', $this->_att, $this->_cr);
            }

            return (false);
        }

        $count = 1;

        foreach ($SKUs as $SKU) {
            if (!$this->checkSKU($SKU)) {
                if ($this->_debug) {
                    printf('ListInventorySKU() function. Warning. SKU: "%s" invalid value. Skipping the product and continue.%s', $SKU, $this->_cr);
                }
                continue;
            }

            $params['SellerSkus.member.'.$count] = (string)$SKU;
            $count++;

            if ($count > 50) {
                if ($this->_debug) {
                    printf('ListInventorySKU() function. Warning. This function is restricted to 50 items.%s', $this->_cr);
                }
                break;
            }
        }

        if (!($datasets = $this->_ListInventorySupply($params))) {
            if ($this->_debug) {
                printf('ListInventoryBySKU() function. _ListInventorySupply failed.%s', $this->_cr);
            }
        }
        if ($this->_debug) {
            printf(
                'ListInventoryBySKU() function. _ListInventorySupply returned %d dataset.%s',
                is_array($datasets) ? count($datasets) : 0,
                $this->_cr
            );
        }

        $result = array();
        foreach ($datasets as $dataset) {
            foreach ($dataset as $inventoryItem) {
                $result[(string)$inventoryItem->SellerSKU] = array();
                $result[(string)$inventoryItem->SellerSKU]['SKU'] = (string)$inventoryItem->SellerSKU;
                $result[(string)$inventoryItem->SellerSKU]['InStockSupplyQuantity'] = (int)$inventoryItem->InStockSupplyQuantity;
                $result[(string)$inventoryItem->SellerSKU]['TotalSupplyQuantity'] = (int)$inventoryItem->TotalSupplyQuantity;
            }
        }

        return ($result);
    }

    /**
     * @param $params
     * @param int $maxQueries
     *
     * @return array|bool
     */
    private function _ListInventorySupply($params, $maxQueries = 10)
    {
        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['ResponseGroup'] = 'Basic';
        $params['Version'] = '2010-10-01';

        $dataset = array();
        $index = 0;
        $nextToken = 1;

        for ($i = 0; $i < $maxQueries && $nextToken; $i++) {
            if ($index == 0) {
                $params['Action'] = 'ListInventorySupply';
                $data = null;
                $pass = true;

                if ($this->demo) {
                    if (!$data = $this->returnDemo($params['Action'])) {
                        $pass = false;
                    }
                }

                if (is_null($data)) {
                    $data = $this->_callWSs('FulfillmentInventory', $params);
                }

                if (!$pass && $this->demo && !is_null($data)) {
                    $this->saveDemo($params['Action'], null, $data);
                }

                $responseItem = 'ListInventorySupplyResult';
            } else {
                $params['Action'] = 'ListInventorySupplyByNextToken';
                $params['NextToken'] = $nextToken;
                $data = null;
                $pass = true;

                if ($this->demo) {
                    if (!$data = $this->returnDemo($params['Action'], $index)) {
                        $pass = false;
                    }
                }

                if (is_null($data)) {
                    $data = $this->_callWSs('FulfillmentInventory', $params);
                }

                if (!$pass && $this->demo && !is_null($data)) {
                    $this->saveDemo($params['Action'], $index, $data);
                }

                $responseItem = 'ListInventorySupplyByNextTokenResult';
            }
            if ($data === false) {
                if ($this->_debug) {
                    printf("$this->_cr $this->_att An error happened during ListInventorySupply request $this->_cr");
                }

                return false;
            }

            if (isset($data->{$responseItem}->NextToken) && $data->{$responseItem}->NextToken) {
                $nextToken = $data->{$responseItem}->NextToken;
            } else {
                $nextToken = null;
            }

            if (!isset($data->{$responseItem})) {
                if ($this->_debug) {
                    printf("ListInventorySupply() function failed $this->_att An error happened during request... $this->_cr");
                }

                return false;
            }

            if ($this->_debug) {
                if ($data instanceof SimpleXMLElement) {
                    CommonTools::p(htmlentities(print_r($data->asXML(), true)));
                } else {
                    echo nl2br(print_r($data, true));
                }
            }

            if (!isset($data->{$responseItem})) {
                return false;
            }

            if (!isset($data->{$responseItem}->InventorySupplyList->member)) {
                if ($this->_debug) {
                    printf("ListInventorySupply()  $this->_att empty inventory... $this->_cr");
                }

                return false;
            }
            $dataset[$index++] = $data->{$responseItem}->InventorySupplyList->member;
        }

        return ($dataset);
    }

    /**
     * @param $Date
     *
     * @return array|bool
     */
    public function ListInventoryByDate($Date)
    {
        $data = null;
        $params = array();

        $querytime = strtotime($Date);
        $now = time();
        $last_month = time() - (86400 * 30);

        if ($querytime > $now || $querytime < $last_month) {
            if ($this->_debug) {
                printf('ListInventoryByDate() function. %sWarning. Wrong Date.%s', $this->_att, $Date, $this->_cr);
            }

            return (false);
        }

        $params['QueryStartDateTime'] = gmdate('Y-m-d\T00:00:00\Z', $querytime);

        if ($this->_debug) {
            printf('ListInventoryByDate() function. _ListInventorySupply params: %s.%s', $this->_cr, print_r($params));
        }

        if (!($datasets = $this->_ListInventorySupply($params))) {
            if ($this->_debug) {
                printf('ListInventoryByDate() function. _ListInventorySupply failed.%s', $this->_cr);
            }
        }
        if ($this->_debug) {
            printf(
                'ListInventoryByDate() function. _ListInventorySupply returned %d dataset.%s',
                is_array($datasets) ? count($datasets) : 0,
                $this->_cr
            );
        }

        $result = array();
        if (is_array($datasets)) {
            foreach ($datasets as $dataset) {
                foreach ($dataset as $inventoryItem) {
                    $result[(string)$inventoryItem->SellerSKU] = array();
                    $result[(string)$inventoryItem->SellerSKU]['SKU'] = (string)$inventoryItem->SellerSKU;
                    $result[(string)$inventoryItem->SellerSKU]['InStockSupplyQuantity'] = (int)$inventoryItem->InStockSupplyQuantity;
                    $result[(string)$inventoryItem->SellerSKU]['TotalSupplyQuantity'] = (int)$inventoryItem->TotalSupplyQuantity;
                }
            }
        }

        return ($result);
    }

    /**
     * @param $createdAfterDate
     * @param null $createdBeforeDate
     * @param bool $FBA
     * @param bool $returnXML
     *
     * @return array|bool
     */
    public function GetCanceledOrdersList($createdAfterDate, $createdBeforeDate = null, $FBA = false, $returnXML = true)
    {
        if ($this->_debug == true) {
            printf("$this->_cr GetCanceledOrdersList call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'ListOrders';
        $params['CreatedAfter'] = $createdAfterDate;

        if ($createdBeforeDate != null) {
            $params['CreatedBefore'] = $createdBeforeDate;
        }

        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['MarketplaceId.Id.1'] = $this->mpid;

        // Amazon Europe
        //
        if (isset($this->MarketPlaces) && count($this->MarketPlaces)) {
            $i = 1;
            foreach ($this->MarketPlaces as $marketPlace) {
                if (empty($marketPlace)) {
                    continue;
                }
                if ($marketPlace == $this->mpid) {
                    continue;
                }

                $i++;
                $params['MarketplaceId.Id.'.$i] = $marketPlace;
            }
        }

        $params['OrderStatus.Status.1'] = 'Canceled';
        $params['MaxResultsPerPage'] = 100;

        if ($this->_debug == true) {
            printf("$this->_cr GetCanceledOrdersList function. Start request $this->_cr");
        }

        $data = $this->simpleCallWS($params, 'Orders', true);

        if ($this->_debug) {
            if (is_object($data)) {
                CommonTools::p($data->asXML());
            } else {
                CommonTools::p($data);
            }
        }

        if (isset($data->Error) && $returnXML) {
            return ($data);
        }

        if (isset($data->ListOrdersResult->NextToken)) {
            $nextToken = $data->ListOrdersResult->NextToken;
        } else {
            $nextToken = null;
        }

        if (isset($data->ListOrdersResult)) {
            $data = $data->ListOrdersResult->Orders;
        }

        if ($data === null) {
            return false;
        }// no orders available

        $Orders = array();
        if ($this->_debug == true) {
            printf("$this->_cr GetCanceledOrdersList function(). Start creating orders class instances. $this->_cr");
        }

        for ($i = 0; $i > -1; $i++) {
            if (!isset($data->Order)) {
                if ($this->_debug == true) {
                    printf("$this->_cr GetCanceledOrdersList - Error or No Pending Order");
                }
                break;
            }
            if ($data->Order[$i] === null) {
                if ($i == 0) {
                    return false;
                } // no orders available
                break; //orders ended
            }

            $Orders[$i] = new PlacedOrder($data->Order[$i], null, $this->_debug);
        }

        while ($nextToken) {
            $nextToken = $this->GetOrdersByNextToken($nextToken, $Orders);
        }

        return $Orders;
    }

    /**
     * @param $createdAfterDate
     * @param null $createdBeforeDate
     * @param string $Status
     * @param bool $FBA
     * @param bool $returnXML
     *
     * @return array|bool
     */
    public function GetUnshippedOrdersList($createdAfterDate, $createdBeforeDate = null, $Status = 'All', $FBA = false, $returnXML = false)
    {
        if ($this->_debug == true) {
            printf("$this->_cr GetUnshippedOrdersList call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'ListOrders';

        $params['CreatedAfter'] = $createdAfterDate;

        if ($createdBeforeDate != null) {
            $params['CreatedBefore'] = $createdBeforeDate;
        }

        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['MarketplaceId.Id.1'] = $this->mpid;

        if (!empty($FBA) && is_string($FBA) && !is_numeric($FBA)) {
            $params['FulfillmentChannel.Channel.1'] = $FBA;
        }

        // Amazon Europe
        //
        if (isset($this->MarketPlaces) && is_array($this->MarketPlaces) && count($this->MarketPlaces)) {
            $i = 1;
            foreach ($this->MarketPlaces as $marketPlace) {
                if (empty($marketPlace)) {
                    continue;
                }
                if ($marketPlace == $this->mpid) {
                    continue;
                }

                $i++;
                $params['MarketplaceId.Id.'.$i] = $marketPlace;
            }
        }

        $params['OrderStatus.Status.1'] = 'Pending';

        if ($FBA) {
            $params['OrderStatus.Status.2'] = 'Unshipped';
            $params['OrderStatus.Status.3'] = 'PartiallyShipped';
            $params['OrderStatus.Status.4'] = 'Shipped';
        } elseif ($Status == 'Shipped') {
            //
            $params['OrderStatus.Status.2'] = 'Shipped';
        } elseif ($Status == 'Unshipped' || $Status == 'PartiallyShipped') {
            $params['OrderStatus.Status.2'] = 'Unshipped';
            $params['OrderStatus.Status.3'] = 'PartiallyShipped';
        } else {
            if ($Status != 'All') {
                $params['OrderStatus.Status.2'] = $Status;
            } else {
                unset($params['OrderStatus.Status.1']);
            }
        }

        $params['MaxResultsPerPage'] = 100;

        if ($this->_debug == true) {
            printf("$this->_cr GetUnshippedOrdersList function. Start request $this->_cr");
        }

        $data = $this->_callWSs('Orders', $params, null, $returnXML);

        if ($this->_debug) {
            if (is_object($data)) {
                echo nl2br(print_r($data->asXML(), true));
            } else {
                echo nl2br(print_r($data, true));
            }
        }

        if (isset($data->Error) && $returnXML) {
            return ($data);
        }

        if (isset($data->ListOrdersResult->NextToken)) {
            $nextToken = $data->ListOrdersResult->NextToken;
        } else {
            $nextToken = null;
        }

        if (isset($data->ListOrdersResult)) {
            $data = $data->ListOrdersResult->Orders;
        }

        if ($data === null) {
            return false;
        }// no orders available

        $Orders = array();
        if ($this->_debug == true) {
            printf("$this->_cr GetUnshippedOrdersList function(). Start creating orders class instances. $this->_cr");
        }

        for ($i = 0; $i > -1; $i++) {
            if (!isset($data->Order)) {
                if ($this->_debug == true) {
                    printf("$this->_cr GetUnshippedOrdersList - Error or No Pending Order");
                }
                break;
            }
            if ($data->Order[$i] === null) {
                if ($i == 0) {
                    return false;
                } // no orders available
                break; //orders ended
            }

            $Orders[$i] = new PlacedOrder($data->Order[$i], null, $this->_debug);
        }

        while ($nextToken) {
            $nextToken = $this->GetOrdersByNextToken($nextToken, $Orders);
        }

        return $Orders;
    }


    /**
     * @param $createdAfterDate
     * @param null $createdBeforeDate
     * @param string $Status
     * @param bool $FBA
     * @param bool $returnXML
     *
     * @return array|bool
     */
    public function GetUnshippedOrdersListv4($createdAfterDate, $createdBeforeDate = null, $Status = 'All', $FBA = false, $returnXML = false, $cronMode = false)
    {
        $data = null;
        if ($this->_debug == true) {
            printf("$this->_cr GetUnshippedOrdersListv4 call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'ListOrders';

       /* if (!$FBA) {
            $params['LastUpdatedAfter'] = $createdAfterDate;
            if (!$cronMode) {
                $params['LastUpdatedBefore'] = $createdBeforeDate;
            }
        } else {
            $params['CreatedAfter'] = $createdAfterDate;
            if (!$cronMode) {
                $params['CreatedBefore'] = $createdBeforeDate;
            }
        }*/
        if (!$cronMode) {
            $params['CreatedAfter'] = $createdAfterDate;
            $params['CreatedBefore'] = $createdBeforeDate;
        } else {
            $params['LastUpdatedAfter'] = $createdAfterDate;
        }


        $params['SellerId'] = $this->mid;
        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';
        $params['MarketplaceId.Id.1'] = $this->mpid;

        if (!empty($FBA) && is_string($FBA) && !is_numeric($FBA)) {
            $params['FulfillmentChannel.Channel.1'] = $FBA;
        }

        // Amazon Europe
        //
        if (isset($this->MarketPlaces) && is_array($this->MarketPlaces) && count($this->MarketPlaces)) {
            $i = 1;
            foreach ($this->MarketPlaces as $marketPlace) {
                if (empty($marketPlace)) {
                    continue;
                }
                if ($marketPlace == $this->mpid) {
                    continue;
                }

                $i++;
                $params['MarketplaceId.Id.'.$i] = $marketPlace;
            }
        }

        $params['OrderStatus.Status.1'] = 'Pending';

        if ($FBA) {
            $params['OrderStatus.Status.2'] = 'Unshipped';
            $params['OrderStatus.Status.3'] = 'PartiallyShipped';
            $params['OrderStatus.Status.4'] = 'Shipped';
        } elseif ($Status == 'Shipped') {
            //
            $params['OrderStatus.Status.2'] = 'Shipped';
        } elseif ($Status == 'Unshipped' || $Status == 'PartiallyShipped') {
            $params['OrderStatus.Status.2'] = 'Unshipped';
            $params['OrderStatus.Status.3'] = 'PartiallyShipped';
        } elseif ($Status != 'Pending') {
            if ($Status != 'All') {
                $params['OrderStatus.Status.2'] = $Status;
            } else {
                unset($params['OrderStatus.Status.1']);
            }
        }

        $params['MaxResultsPerPage'] = 100;

        if ($this->_debug == true) {
            printf("$this->_cr GetUnshippedOrdersListv4 function. Start request $this->_cr");
        }

        $pass = true;
        if ($this->demo) {
            if (!$data = $this->returnDemo(__FUNCTION__)) {
                $pass = false;
            }
        }

        if (is_null($data)) {
            $data = $this->_callWSs('Orders', $params, null, $returnXML);
        }

        if (!$pass && $this->demo && !is_null($data)) {
            $this->saveDemo(__FUNCTION__, null, $data);
        }

        if ($this->_debug) {
            if (is_object($data)) {
                echo nl2br(print_r($data->asXML(), true));
            } else {
                echo nl2br(print_r($data, true));
            }
        }

        if (isset($data->Error) && $returnXML) {
            return ($data);
        }

        if (isset($data->ListOrdersResult->NextToken)) {
            $nextToken = $data->ListOrdersResult->NextToken;
        } else {
            $nextToken = null;
        }

        if (isset($data->ListOrdersResult)) {
            $data = $data->ListOrdersResult->Orders;
        }

        if ($data === null) {
            return false;
        }// no orders available

        $Orders = array();
        if ($this->_debug == true) {
            printf("$this->_cr GetUnshippedOrdersListv4 function(). Start creating orders class instances. $this->_cr");
        }

        for ($i = 0; $i > -1; $i++) {
            if (!isset($data->Order)) {
                if ($this->_debug == true) {
                    printf("$this->_cr GetUnshippedOrdersListv4 - Error or No Pending Order");
                }
                break;
            }
            if ($data->Order[$i] === null) {
                if ($i == 0) {
                    return false;
                } // no orders available
                break; //orders ended
            }

            $Orders[$i] = new PlacedOrder($data->Order[$i], null, $this->_debug);
        }

        while ($nextToken) {
            $nextToken = $this->GetOrdersByNextToken($nextToken, $Orders);
        }

        return $Orders;
    }

    /**
     *
     * !!!!Please Note, that this operation have to send time information to the WebService
     * !!!!Time represents moment, when order were shipped
     * !!!!This functions send time, that are on one and a half hour before current system date
     * !!!!If system time is incorrect and function sends date from the future,
     * !!!!Error will be occured and operation will be not completed correctly.
     *
     * @param <type> $Orders
     * @return <int> SumbissionId or false if unsuccessful
     */
    public function confirmOrderBase($amazonOrderId)
    {
        return $this->confirmOrder($amazonOrderId, null, null, null);
    }

    /**
     * @param $amazonOrderId
     * @param $carrierCode
     * @param $carrierName
     * @param $shippingMethod
     * @param $shipperTrackingNumber
     *
     * @return bool|SimpleXMLElement
     */
    public function confirmOrder($amazonOrderId, $carrierCode, $carrierName, $shippingMethod, $shipperTrackingNumber)
    {
        if ($this->_debug == true) {
            printf("$this->_cr confirmOrder call. $this->_cr");
        }

        $Document = new DOMDocument();
        $Messages = array();

        if ($this->_debug == true) {
            printf("$this->_cr confirmOrder function. Create messages here $this->_cr");
        }

        $Messages[0] = $this->createOrderFulfillmentMessage($Document, $amazonOrderId, $carrierCode, $carrierName, $shippingMethod, $shipperTrackingNumber, 1);

        $feedDOM = $this->CreateFeed($Document, 'OrderFulfillment', $Messages);

        $feed = $feedDOM->saveXML();

        if ($this->_debug == true) {
            printf("$this->_cr confirmOrder function. Now we send following query to WebService:  $this->_cr $feed $this->_cr");
            echo self::htmlTag('pre', array('class' => 'amazon-xml')) . htmlentities($feed) . self::htmlTag('/pre');
        }

        $data = $this->processFeed('_POST_ORDER_FULFILLMENT_DATA_', $feed);

        if ($data === false && $this->_debug == true) {
            //
            printf("$this->_cr confirmShipmentOrders function finished with an error because of the prblem with query sending.. $this->_cr");
        } elseif ($this->_debug == true) {
            printf("$this->_cr confirmShipmentOrders function finished successfuly $this->_cr");
        }

        return $data;
    }

    /**
     * @param DOMDocument $Document
     * @param $AmazonOrderIDI
     * @param $CarrierCodeI
     * @param $CarrierNameI
     * @param $ShippingMethodI
     * @param $ShipperTrackingNumberI
     * @param $messageID
     * @param null $timestamp
     *
     * @return DOMElement
     */
    private function createOrderFulfillmentMessage(DOMDocument $Document, $AmazonOrderIDI, $CarrierCodeI, $CarrierNameI, $ShippingMethodI, $ShipperTrackingNumberI, $messageID, $timestamp = null)
    {
        $message = $Document->createElement('Message');
        $messageIDX = $Document->createElement('MessageID');
        $message->appendChild($messageIDX);
        $messageIDText = $Document->createTextNode($messageID);
        $messageIDX->appendChild($messageIDText);
        $OrderFulfillment = $Document->createElement('OrderFulfillment');
        $message->appendChild($OrderFulfillment);
        $AmazonOrderID = $Document->createElement('AmazonOrderID');
        $OrderFulfillment->appendChild($AmazonOrderID);
        $AmazonOrderIDText = $Document->createTextNode($AmazonOrderIDI);
        $AmazonOrderID->appendChild($AmazonOrderIDText);
        $FulfillmentDate = $Document->createElement('FulfillmentDate');
        $OrderFulfillment->appendChild($FulfillmentDate);

        if ($timestamp == null || !is_numeric($timestamp)) {
            $t = time() - 5400;
            if (!date_default_timezone_get()) {
                date_default_timezone_set('Europe/Helsinki');
            }
        } else {
            $t = $timestamp;
        }

        $date = date('c', $t);
        $FulfillmentDateText = $Document->createTextNode($date);
        $FulfillmentDate->appendChild($FulfillmentDateText);

        //FulfillmentData
        if ($CarrierCodeI !== null || $CarrierNameI !== null) {
            $FulfillmentData = $Document->createElement('FulfillmentData');
            $OrderFulfillment->appendChild($FulfillmentData);
            if (!($CarrierCodeI === null) && $CarrierCodeI) {
                $CarrierCode = $Document->createElement('CarrierCode');
                $FulfillmentData->appendChild($CarrierCode);
                $CarrierCodeText = $Document->createTextNode($CarrierCodeI);
                $CarrierCode->appendChild($CarrierCodeText);
            } elseif (!($CarrierNameI === null) && $CarrierNameI) {
                $CarrierName = $Document->createElement('CarrierName');
                $FulfillmentData->appendChild($CarrierName);
                $CarrierNameText = $Document->createTextNode($CarrierNameI);
                $CarrierName->appendChild($CarrierNameText);
            }
            if (!($ShippingMethodI === null)) {
                $ShippingMethod = $Document->createElement('ShippingMethod');
                $FulfillmentData->appendChild($ShippingMethod);
                $ShippingMethodText = $Document->createTextNode($ShippingMethodI);
                $ShippingMethod->appendChild($ShippingMethodText);
            }
            if (!empty($ShipperTrackingNumberI)) {
                $ShipperTrackingNumber = $Document->createElement('ShipperTrackingNumber');
                $FulfillmentData->appendChild($ShipperTrackingNumber);
                $ShipperTrackingNumberText = $Document->createTextNode($ShipperTrackingNumberI);
                $ShipperTrackingNumber->appendChild($ShipperTrackingNumberText);
            }
        }

        return $message;
    }

    /**
     * @param $amazonShippings
     *
     * @return bool|SimpleXMLElement
     */
    public function confirmMultipleOrders($amazonShippings)
    {
        if ($this->_debug == true) {
            printf("$this->_cr confirmMultipleOrders call. $this->_cr");
        }

        $Document = new DOMDocument();
        $Messages = array();

        if ($this->_debug == true) {
            printf("$this->_cr confirmMultipleOrders function. Create messages here $this->_cr");
        }

        $m = 0;
        if (is_array($amazonShippings) && count($amazonShippings)) {
            foreach ($amazonShippings as $amazonShipping) {
                if (!isset($amazonShipping['order_id']) || empty($amazonShipping['order_id'])) {
                    if ($this->_debug == true) {
                        printf("$this->_cr Missing order_id: %s $this->_cr", print_r($amazonShipping, true));
                    }
                    continue;
                }

                if ((!isset($amazonShipping['carrier']) || empty($amazonShipping['carrier'])) && (!isset($amazonShipping['carrier_name']) || empty($amazonShipping['carrier_name']))) {
                    if ($this->_debug == true) {
                        printf("$this->_cr Missing carrier info: %s $this->_cr", print_r($amazonShipping, true));
                    }
                    continue;
                }

                $Messages[$m] = $this->createOrderFulfillmentMessage($Document, $amazonShipping['order_id'], $amazonShipping['carrier'], $amazonShipping['carrier_name'], null, $amazonShipping['shipping_number'], $m + 1, $amazonShipping['timestamp']);
                $m++;
            }
        }
        if (!$m) {
            if ($this->_debug == true) {
                printf("$this->_cr confirmMultipleOrders function. No Message to Send, returning to main function $this->_cr");
            }

            return (false);
        }
        $feedDOM = $this->CreateFeed($Document, 'OrderFulfillment', $Messages);

        if ($this->_debug) {
            $feedDOM->formatOutput = true;
        }

        $feed = $feedDOM->saveXML();

        if ($this->_debug) {
            echo nl2br(str_replace(' ', '&nbsp;', htmlentities($feed)));

            printf('Complete Feed is:'.$this->_cr);
            echo self::htmlTag('pre', array('class' => 'amazon-xml')) . htmlentities($feed) . self::htmlTag('/pre');
        }

        if ($this->_debug == true) {
            printf("$this->_cr confirmMultipleOrders function. Now we send following query to WebService:  $this->_cr $feed $this->_cr");
        }

        $data = $this->processFeed('_POST_ORDER_FULFILLMENT_DATA_', $feed);

        if ($data === false && $this->_debug == true) {
            //
            printf("$this->_cr confirmMultipleOrders function finished with an error because of the prblem with query sending.. $this->_cr");
        } else {
            if ($this->_debug == true) {
                printf("$this->_cr confirmMultipleOrders function finished successfuly $this->_cr");
            }
        }

        return $data;
    }


    /**
     * @param $orders
     *
     * @return bool|string
     */
    public function acknowledgeOrders($orders)
    {
        $Messages = array();

        if ($this->_debug == true) {
            printf("$this->_cr acknowledgeOrders() call. Creating Messages. $this->_cr");
        }

        $Document = new DOMDocument();

        if (!is_array($orders) || !count($orders)) {
            if ($this->_debug == true) {
                printf("$this->_cr acknowledgeOrders() no orders to acknowledge.. $this->_cr");
            }

            return (false);
        }

        $m = 0;
        foreach ($orders as $mp_order_id => $order) {
            if (!isset($order['status']) || !$order['status']) {
                continue;
            }
            if (!isset($order['merchant_order_id'])) {
                continue;
            }
            $m++;

            if ($this->_debug == true) {
                printf("$this->_cr acknowledgeOrders() call. Creating Message $m for OrderID $mp_order_id. $this->_cr");
            }
            $Messages[] = $this->createOrderAcknowledgementMessage($Document, $mp_order_id, $m, $order['merchant_order_id']);
        }

        if (!is_array($Messages) || !count($Messages)) {
            printf("$this->_cr acknowledgeOrders() no orders to acknowledge.. $this->_cr");

            return (false);
        }

        $feedDOM = $this->CreateFeed($Document, 'OrderAcknowledgement', $Messages);
        $feed = $feedDOM->saveXML();

        if ($this->_debug == true) {
            printf("$this->_cr acknowledgeOrders() function. Now function creates the following feed: $this->_cr");
            echo self::htmlTag('pre', array('class' => 'amazon-xml')) . htmlentities($feed) . self::htmlTag('/pre');
        }

        $data = $this->processFeed('_POST_ORDER_ACKNOWLEDGEMENT_DATA_', $feed);

        if ($data === false && $this->_debug == true) {
            printf("$this->_cr acknowledgeOrders() function finished with an error because of the prblem with query sending.. $this->_cr");
        } elseif ($this->_debug == true) {
            printf("$this->_cr acknowledgeOrders() function finished successfuly $this->_cr");
        }

        if (isset($data->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId) && (int)$data->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId) {
            return (string)$data->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
        } else {
            return(false);
        }
    }

    /**
     * @param DOMDocument $Document
     * @param $OrderID
     * @param $messageID
     * @param $merchand_order_id
     *
     * @return DOMElement
     */
    private function createOrderAcknowledgementMessage(DOMDocument $Document, $OrderID, $messageID, $merchand_order_id)
    {
        $message = $Document->createElement('Message');
        $messageIDX = $Document->createElement('MessageID');
        $message->appendChild($messageIDX);
        $messageIDText = $Document->createTextNode($messageID);
        $messageIDX->appendChild($messageIDText);
        $OrderAcknowledgement = $Document->createElement('OrderAcknowledgement');
        $message->appendChild($OrderAcknowledgement);

        $AmazonOrderID = $Document->createElement('AmazonOrderID');
        $OrderAcknowledgement->appendChild($AmazonOrderID);
        $AmazonOrderIDText = $Document->createTextNode($OrderID);
        $AmazonOrderID->appendChild($AmazonOrderIDText);

        $MerchandOrderID = $Document->createElement('MerchantOrderID');
        $OrderAcknowledgement->appendChild($MerchandOrderID);
        $MerchandOrderIDText = $Document->createTextNode($merchand_order_id);
        $MerchandOrderID->appendChild($MerchandOrderIDText);

        $StatusCode = $Document->createElement('StatusCode');
        $OrderAcknowledgement->appendChild($StatusCode);
        $StatusCodeText = $Document->createTextNode('Success');
        $StatusCode->appendChild($StatusCodeText);

        return $message;
    }


    private function createEnhancedOrderAcknowledgementMessage(DOMDocument $Document, $order, $messageID, $status = 'Success')
    {
        $message = $Document->createElement('Message');
        $messageIDX = $Document->createElement('MessageID');
        $message->appendChild($messageIDX);
        $messageIDText = $Document->createTextNode($messageID);
        $messageIDX->appendChild($messageIDText);
        $OrderAcknowledgement = $Document->createElement('OrderAcknowledgement');
        $message->appendChild($OrderAcknowledgement);

        $AmazonOrderID = $Document->createElement('AmazonOrderID');
        $OrderAcknowledgement->appendChild($AmazonOrderID);
        $AmazonOrderIDText = $Document->createTextNode($order['mp_order_id']);
        $AmazonOrderID->appendChild($AmazonOrderIDText);

        $MerchandOrderID = $Document->createElement('MerchantOrderID');
        $OrderAcknowledgement->appendChild($MerchandOrderID);
        $MerchandOrderIDText = $Document->createTextNode($order['merchant_order_id']);
        $MerchandOrderID->appendChild($MerchandOrderIDText);

        $StatusCode = $Document->createElement('StatusCode');
        $OrderAcknowledgement->appendChild($StatusCode);
        $StatusCodeText = $Document->createTextNode($status);
        $StatusCode->appendChild($StatusCodeText);


        if (is_array($order['items']) && count($order['items'])) {
            $OrderAcknowledgement->appendChild($Items = $Document->createElement('Items'));

            foreach ($order['items'] as $item) {
                $Items->appendChild($Item = $Document->createElement('Item'));

                if (isset($item['order_item_id']) && Tools::strlen($item['order_item_id'])) {
                    $Item->appendChild($Document->createElement('AmazonOrderItemCode', $item['order_item_id']));
                }
                if (isset($item['merchant_item_id']) && Tools::strlen($item['merchant_item_id'])) {
                    $Item->appendChild($Document->createElement('MerchantOrderItemID', $item['merchant_item_id']));
                }
                if (isset($item['reason']) && Tools::strlen($item['reason'])) {
                    $Item->appendChild($Document->createElement('CancelReason', $item['reason']));
                }
            }
        }
        return $message;
    }


    /**
     *Orders Cancelation
     * @param <type> $orders
     * @return <type> - Feed Submission ID
     */
    public function cancelOrders($orders)
    {
        if ($this->_debug == true) {
            printf("$this->_cr cancelOrders() call. Creating Messages. $this->_cr");
        }

        $Document = new DOMDocument();
        $i = 0;
        foreach ($orders as $order) {
            $i++;
            if ($this->_debug == true) {
                $m = $i + 1;
                printf("$this->_cr cancelOrders() call. Creating Message $m for OrderID %s. $this->_cr", $order['mp_order_id']);
            }
            $mess = $this->createEnhancedOrderAcknowledgementMessage($Document, $order, $i + 1, 'Failure');
        }

        $Messages = array();
        $Messages[0] = $mess;
        $feedDOM = $this->CreateFeed($Document, 'OrderAcknowledgement', $Messages);
        $feed = $feedDOM->saveXML();

        if ($this->_debug == true) {
            printf("$this->_cr cancelOrders() function. Now function creates the following feed: $this->_cr");
            echo self::htmlTag('pre', array('class' => 'amazon-xml')) . htmlentities($feed) . self::htmlTag('/pre');
        }
        $data = null;
        $pass = true;

        if ($this->demo) {
            $data = $this->returnDemo(__FUNCTION__);
            if ($data) {
                $pass = false;
            }
        }

        if ($data == null) {
            $data = $this->processFeed('_POST_ORDER_ACKNOWLEDGEMENT_DATA_', $feed);

            if ($data == false && $this->_debug == true) {
                printf("$this->_cr cancelOrders() function finished with an error because of the prblem with query sending.. $this->_cr");
            } elseif ($this->_debug == true) {
                printf("$this->_cr cancelOrders() function finished successfuly $this->_cr");
            }

            if ($this->demo && !$pass) {
                $this->saveDemo(__FUNCTION__, false, $data);
            }
        }

        return $data;
    }

    /**
     * @param null $startDate
     * @param null $endDate
     *
     * @return null
     */
    public function requestReport($startDate = null, $endDate = null)
    {
        if ($this->_debug == true) {
            printf("$this->_cr RequestReport call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'RequestReport';
        $params['Marketplace'] = $this->mpid;
        $params['Merchant'] = $this->mid;
        $params['ReportType'] = '_GET_FLAT_FILE_OPEN_LISTINGS_DATA_';

        if ($startDate != null) {
            $params['StartDate'] = $this->getFormattedTimestamp($startDate);
        }

        if ($endDate != null) {
            $params['EndDate'] = $this->getFormattedTimestamp($endDate);
        }

        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';

        if ($this->_debug == true) {
            printf("$this->_cr requestReport function. Start request $this->_cr");
        }

        $response = $this->_callWSs('Reports', $params);

        if ($response->RequestReportResult->ReportRequestInfo->ReportProcessingStatus == '_SUBMITTED_') {
            $reportRequestId = $response->RequestReportResult->ReportRequestInfo->ReportRequestId;
            //$submittedDate = $response->RequestReportResult->ReportRequestInfo->SubmittedDate;

            return $reportRequestId;
        }

        return null;
    }

    /*
    * 3.get reporst Ids of all reports available
    */

    /**
     * @param $reportRequestId
     *
     * @return bool|SimpleXMLElement
     */
    public function getProducts($reportRequestId)
    {
        if ($reportRequestId != null) {
            sleep(2);
            //if the reportreqquestid is not null,check the status of the report
            $status = $this->getReportRequestList($reportRequestId);

            if ($status == '_DONE_') {
                //if the status is _DONE_,that means the report has been ready,we can download it now.
                //get the report id
                sleep(1);
                $reportId = $this->getReportList($reportRequestId);

                if ($reportId != null) {
                    sleep(1);
                    $response = $this->getReport($reportId);

                    //parse the $response,and retrieve the details from product advertising API
                    //$products = explode(' ',$response);
                    //$products = preg_split('/\s+/', $response);

                    return $response;
                }
            }
        }

        return (false);
    }

    /*
    * 4.download a report
    */

    /**
     * @param $reportRequestId
     *
     * @return bool
     */
    private function getReportRequestList($reportRequestId)
    {
        if ($this->_debug == true) {
            printf("$this->_cr getReportRequestList call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'GetReportRequestList';
        $params['Marketplace'] = $this->mpid;
        $params['Merchant'] = $this->mid;
        $params['ReportRequestIdList.Id.1'] = $reportRequestId;

        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';

        if ($this->_debug == true) {
            printf("$this->_cr getReportRequestList function. Start request $this->_cr");
        }

        $response = $this->_callWSs('Reports', $params);

        if ($response->GetReportRequestListResult->ReportRequestInfo[0]->ReportProcessingStatus == '_DONE_') {
            //
            return $response->GetReportRequestListResult->ReportRequestInfo[0]->ReportProcessingStatus;
        } elseif ($response->GetReportRequestListResult->ReportRequestInfo[0]->ReportProcessingStatus == '_PROCESSING_' || $response->GetReportRequestListResult->ReportRequestInfo[0]->ReportProcessingStatus == '_SUBMITTED_') {
            $sta = $response->GetReportRequestListResult->ReportRequestInfo[0]->ReportProcessingStatus;
            while ($sta != '_DONE_') {
                unset($params);
                $params['Action'] = 'GetReportRequestList';
                $params['Marketplace'] = $this->mpid;
                $params['Merchant'] = $this->mid;
                $params['ReportRequestIdList.Id.1'] = $reportRequestId;

                $params['SignatureVersion'] = '2';
                $params['SignatureMethod'] = 'HmacSHA256';

                if ($this->_debug == true) {
                    printf("$this->_cr getReportRequestList function. Start request $this->_cr");
                }

                $response = $this->_callWSs('Reports', $params);

                if (isset($response->GetReportRequestListResult->ReportRequestInfo[0]->ReportProcessingStatus) && $response->GetReportRequestListResult->ReportRequestInfo[0]->ReportProcessingStatus == '_DONE_') {
                    //
                    return $response->GetReportRequestListResult->ReportRequestInfo[0]->ReportProcessingStatus;
                } elseif (isset($response->GetReportRequestListResult->ReportRequestInfo[0]->ReportProcessingStatus)) {
                    //
                    $sta = $response->GetReportRequestListResult->ReportRequestInfo[0]->ReportProcessingStatus;
                }
            }
            sleep(5);
            $this->getReportRequestList($reportRequestId);
        }

        return (false);
    }

    /**
     * @param $reportRequestId
     *
     * @return mixed
     */
    private function getReportList($reportRequestId)
    {
        if ($this->_debug == true) {
            printf("$this->_cr getReportList call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'GetReportList';
        $params['Marketplace'] = $this->mpid;
        $params['Merchant'] = $this->mid;
        $params['ReportRequestIdListId.1'] = $reportRequestId;
        $params['Acknowledged'] = 'false';

        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';

        if ($this->_debug == true) {
            printf("$this->_cr getReportList function. Start request $this->_cr");
        }

        $response = $this->_callWSs('Reports', $params);

        return $response->GetReportListResult->ReportInfo[0]->ReportId;
    }

    /*
     * sign request
     */

    /**
     * @param $ReportId
     *
     * @return bool|SimpleXMLElement
     */
    private function getReport($ReportId)
    {
        if ($this->_debug == true) {
            printf("$this->_cr getReport call. $this->_cr");
        }

        $params = array();
        $params['Action'] = 'GetReport';
        $params['Marketplace'] = $this->mpid;
        $params['Merchant'] = $this->mid;
        $params['ReportId'] = $ReportId;

        $params['SignatureVersion'] = '2';
        $params['SignatureMethod'] = 'HmacSHA256';

        if ($this->_debug == true) {
            printf("$this->_cr getReport function. Start request $this->_cr");
        }

        $response = $this->_callWSs('ReportsDownload', $params);

        return $response;
    }

    /*
    * header callback
    */
    /**
     * @param $dateTime
     *
     * @return mixed
     */
    private function getFormattedTimestamp($dateTime)
    {
        return $dateTime->format(DATE_ISO8601);
    }

    /**
     * @param $action
     * @param null $param
     *
     * @return null|SimpleXMLElement
     */
    public function returnDemo($action, $param = null)
    {
        $data = null;
        $directory = dirname(__FILE__).'/../demo';

        switch ($action) {
            case 'cancelOrders':
                $file = sprintf('%s/%s-%s.xml', $directory, 'cancel_orders', $this->mid);
                if (file_exists($file)) {
                    $data = simplexml_load_file($file);
                }
                break;
            case 'getOrders':
                $file = sprintf('%s/%s-%s.xml', $directory, 'get_orders', $this->mid);
                if (file_exists($file)) {
                    $data = simplexml_load_file($file);
                }
                break;
            case 'GetUnshippedOrdersListv4':
                $file = sprintf('%s/%s-%s.xml', $directory, 'get_order_list', $this->mid);
                if (file_exists($file)) {
                    $data = simplexml_load_file($file);
                }
                break;
            case 'getOrderItems':
                $file = sprintf('%s/%s-%s-%s.xml', $directory, 'order_items', $this->mid, $param);
                if (file_exists($file)) {
                    $data = simplexml_load_file($file);
                }
                break;
            case 'ListInventorySupply':
            case 'ListInventorySupplyByNextToken':
                if ($param) {
                    $file = sprintf('%s/%s-%s-%s.xml', $directory, Tools::strtolower($action), $param, $this->mid);
                } else {
                    $file = sprintf('%s/%s-%s.xml', $directory, Tools::strtolower($action), $this->mid);
                }
                if (file_exists($file)) {
                    $data = simplexml_load_file($file);
                }
                break;
        }
        return($data);
    }

    /**
     * @param $action
     * @param $param
     * @param $xml
     *
     * @return null
     */
    public function saveDemo($action, $param, $xml)
    {
        $data = null;
        $directory = dirname(__FILE__).'/../demo';

        switch ($action) {
            case 'getOrders':
                $file = sprintf('%s/%s-%s.xml', $directory, 'get_orders', $this->mid);
                file_put_contents($file, $xml->asXML());
                break;
            case 'cancelOrders':
                $file = sprintf('%s/%s-%s.xml', $directory, 'cancel_orders', $this->mid);
                file_put_contents($file, $xml->asXML());
                break;
            case 'GetUnshippedOrdersListv4':
                $file = sprintf('%s/%s-%s.xml', $directory, 'get_order_list', $this->mid);
                file_put_contents($file, $xml->asXML());
                break;
            case 'getOrderItems':
                $file = sprintf('%s/%s-%s-%s.xml', $directory, 'order_items', $param, $this->mid);
                file_put_contents($file, $xml->asXML());
                break;
            case 'ListInventorySupply':
            case 'ListInventorySupplyByNextToken':
                if ($param) {
                    $file = sprintf('%s/%s-%s-%s.xml', $directory, Tools::strtolower($action), $param, $this->mid);
                } else {
                    $file = sprintf('%s/%s-%s.xml', $directory, Tools::strtolower($action), $this->mid);
                }
                file_put_contents($file, $xml->asXML());
                break;
        }
        return($data);
    }

    /**
     * Get curl info
     * @return array
     */
    public function getCurlInfo()
    {
        return $this->curlInfo;
    }

    /**
     * Return encoded html
     * @param string $tag
     * @param array $attrs
     *
     * @return string
     */
    protected static function htmlTag($tag, $attrs = array())
    {
        $output = "&lt;$tag";
        foreach ($attrs as $name => $value) {
            $output .= " $name=&quot;$value&quot;";
        }
        $output .= "&gt;";
        return html_entity_decode($output);
    }
}

/*PlacedOrder class.
Items of this class can be generated from GetOrdersList() function of the Service class.*/

/**
 * Class PlacedOrder
 */
class PlacedOrder
{
    /**
     * @var string
     */
    public $AmazonOrderId; /*String, This value can be used for order shipment or for order cancelation*/
    /**
     * @var string
     */
    public $OrderStatus;
    /**
     * @var string
     */
    public $PurchaseDate; /*String, The date when the customer placed the order.*/    /**
     * @var string
     */
    public $LastUpdateDate;
    /**
     * @var string
     */
    public $OrderTotalCurrency;
    /**
     * @var string
     */
    public $OrderTotalAmount;
    /**
     * @var string
     */
    public $ShipServiceLevel; /*String.*/
    /**
     * @var string
     */
    public $FulfillmentChannel; /*String.*/
    /**
     * @var null|string
     */
    public $ShipmentServiceLevelCategory; /*String.*/
    /**
     * @var string
     */
    public $SalesChannel;
    /**
     * @var string
     */
    public $OrderChannel;
    /**
     * @var string
     */
    public $NumberOfItemsUnshipped;
    /**
     * @var string
     */
    public $NumberOfItemsShipped;
    /**
     * @var string
     */
    public $MarketPlaceId;
    /**
     * @var bool
     */
    public $IsPrime;
    /**
     * @var bool
     */
    public $IsPremiumOrder;
    /**
     * @var bool
     */
    public $IsBusinessOrder;
    /**
     * @var string
     */
    public $BuyerEmail;
    /**
     * @var string
     */
    public $BuyerName;
    /**
     * @var string
     */
    public $EarliestShipDate;
    /**
     * @var string
     */
    public $LatestShipDate;
    /**
     * @var string
     */
    public $EarliestDeliveryDate;
    /**
     * @var string
     */
    public $LatestDeliveryDate;
    /**
     * @var string
     */
    public $OrderType;
    /**
     * @var AmazonWsAddress
     */
    public $Address; /*Type: AmazonWsAddress. Instance Of the AmazonWsAddress class. Address where we send our order*/
    /**
     * @var BillingAddress
     */
    public $BillingAddress; /*Type: AmazonWsAddress. Instance Of the AmazonWsAddress class. Address where we bill our order*/
    /**
     * @var null
     */
    public $Items; /*Type: Array of the OrderedItem class instances. Ordered products, array of the OrderedItem class instances.*/
    /**
     * @var bool
     */
    public $_debug;

    /**
     * PlacedOrder constructor.
     *
     * @param SimpleXMLElement $order
     * @param $Items
     * @param bool $debug
     */
    public function __construct(SimpleXMLElement $order, $Items, $debug = false)
    {
        if ($debug == true) {
            $this->_debug = true;
        } else {
            $this->_debug = false;
        }

        $this->AmazonOrderId = (string)$order->AmazonOrderId;
        $this->OrderStatus = (string)$order->OrderStatus;
        $this->PurchaseDate = (string)$order->PurchaseDate;
        $this->LastUpdateDate = (string)$order->LastUpdateDate;
        $this->OrderTotalCurrency = (string)$order->OrderTotal->CurrencyCode;
        $this->OrderTotalAmount = (string)$order->OrderTotal->Amount;
        $this->ShipServiceLevel = (string)$order->ShipServiceLevel;
        $this->ShipmentServiceLevelCategory = isset($order->ShipmentServiceLevelCategory) && !empty($order->ShipmentServiceLevelCategory) ? (string)$order->ShipmentServiceLevelCategory : null;
        $this->IsPremiumOrder = isset($order->IsPremiumOrder) && $order->IsPremiumOrder == 'true' ? true : false;
        $this->IsPrime = isset($order->IsPrime) && (string)$order->IsPrime == 'true' ? true : false;
        $this->IsBusinessOrder = isset($order->IsBusinessOrder) && (string)$order->IsBusinessOrder == 'true' ? true : false;
        $this->FulfillmentChannel = (string)$order->FulfillmentChannel;
        $this->SalesChannel = isset($order->SalesChannel) && Tools::strlen((string)$order->SalesChannel) ? (string)$order->SalesChannel : null;
        $this->OrderChannel = isset($order->OrderChannel) && Tools::strlen((string)$order->OrderChannel) ? (string)$order->OrderChannel : null;
        $this->NumberOfItemsUnshipped = (string)$order->NumberOfItemsUnshipped;
        $this->NumberOfItemsShipped = (string)$order->NumberOfItemsShipped;
        $this->MarketPlaceId = (string)$order->MarketplaceId;
        $this->BuyerEmail = (string)$order->BuyerEmail;
        $this->BuyerName = (string)$order->BuyerName;
        $this->EarliestShipDate = isset($order->EarliestShipDate) && Tools::strlen((string)$order->EarliestShipDate) ? date('Y-m-d H:i:s', strtotime((string)$order->EarliestShipDate)) : null;
        $this->LatestShipDate = isset($order->LatestShipDate) && Tools::strlen((string)$order->LatestShipDate) ? date('Y-m-d H:i:s', strtotime((string)$order->LatestShipDate)) : null;
        $this->EarliestDeliveryDate = isset($order->EarliestDeliveryDate) && Tools::strlen((string)$order->EarliestDeliveryDate) ? date('Y-m-d H:i:s', strtotime((string)$order->EarliestDeliveryDate)) : null;
        $this->LatestDeliveryDate = isset($order->LatestDeliveryDate) && Tools::strlen((string)$order->LatestDeliveryDate) ? date('Y-m-d H:i:s', strtotime((string)$order->LatestDeliveryDate)) : null;
        $this->OrderType = isset($order->OrderType) && Tools::strlen((string)$order->OrderType) ? (string)$order->OrderType : null;

        $this->Address = new AmazonWsAddress($order->ShippingAddress);

        if ((string)$order->BuyerName != (string)$order->BillingAddress->Name) {
            $this->BillingAddress = new AmazonWsAddress($order->ShippingAddress);
            $this->BillingAddress->Name = (string)$order->BuyerName;
        } else {
            $this->BillingAddress = $this->Address;
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s()/#%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf('Item count: %d', is_array($Items) ? count($Items) : 0));
        }

        //Set Ordered items (products)
        if ($Items !== null) {
            $this->Items = array();
            for ($i = 0; $i > -1; $i++) {
                if ($Items->OrderItem[$i] !== null) {
                    $this->Items[$i] = new OrderedItem($Items->OrderItem[$i]);
                } else {
                    break;
                }
            }
        } else {
            $this->Items = null;
        }

        if ($this->_debug) {
            $this->printing();
        }
    }

    /**
     *
     */
    public function printing()
    {
        CommonTools::p("Order:");
        CommonTools::p("AmazonOrderId: $this->AmazonOrderId, PurchaseDate: $this->PurchaseDate, ShipServiceLevel: $this->ShipServiceLevel, FullFillmentChannel: $this->FulfillmentChannel, NumberOfItemsUnshipped: $this->NumberOfItemsUnshipped");
        //shipping address printing
        $this->Address->printing();

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s()/#%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf('Items: %s', print_r($this->Items, true)));
        }
    }
}

/**
 * Class AmazonWsAddress
 */
class AmazonWsAddress
{
    /**
     * @var string
     */
    public $Name;
    /**
     * @var string
     */
    public $AddressLine1;
    /**
     * @var string
     */
    public $AddressLine2;
    /**
     * @var string
     */
    public $City;
    /**
     * @var string
     */
    public $StateOrRegion;
    /**
     * @var string
     */
    public $PostalCode;
    /**
     * @var string
     */
    public $CountryCode;
    /**
     * @var string
     */
    public $Phone;
    /**
     * @var string
     */
    public $Instructions;

    /**
     * AmazonWsAddress constructor.
     *
     * @param SimpleXMLElement $Address
     */
    public function __construct(SimpleXMLElement $Address)
    {
        $this->Name = (string)$Address->Name;
        $this->AddressLine1 = (string)$Address->AddressLine1;
        $this->AddressLine2 = (string)$Address->AddressLine2;
        $this->City = (string)$Address->City;
        $this->StateOrRegion = (string)$Address->StateOrRegion;
        $this->PostalCode = (string)$Address->PostalCode;
        $this->CountryCode = (string)$Address->CountryCode;
        $this->Phone = (string)$Address->Phone;

        if (isset($Address->Instructions) && Tools::strlen($Address->Instructions)) {
            $this->Instructions = $Address->Instructions;
        } else {
            $this->Instructions = null;
        }
    }

    /**
     *
     */
    public function printing()
    {
        CommonTools::p("Address:");
        CommonTools::p("Name: $this->Name, AddressLine1: $this->AddressLine1, AddressLine2: $this->AddressLine2,
				City: $this->City, StateOrRegion: $this->StateOrRegion, PostalCode: $this->PostalCode,
				CountryCode: $this->CountryCode, Phone: $this->Phone");
    }
}

/**
 * Class OrderedItem
 */
class OrderedItem
{
    /**
     * @var string
     */
    public $ASIN;
    /**
     * @var string
     */
    public $SKU;
    /**
     * @var string
     */
    public $Title;
    /**
     * @var string
     */
    public $QuantityOrdered;
    /**
     * @var string
     */
    public $ItemPriceCurrency;
    /**
     * @var string
     */
    public $ItemPriceAmount;
    /**
     * @var string
     */
    public $ShippingPriceCurrency;
    /**
     * @var string
     */
    public $ShippingPriceAmount;
    /**
     * @var string
     */
    public $QuantityShipped;
    /**
     * @var null
     */
    public $GifWrapPrice;
    /**
     * @var null
     */
    public $GiftMessageText;
    /**
     * @var null
     */
    public $PromotionDiscountAmount;
    /**
     * @var null
     */
    public $PromotionDiscountCurrency;
    /**
     * @var null
     */
    public $ShippingDiscountAmount;
    /**
     * @var null
     */
    public $ShippingDiscountCurrency;
    /**
     * @var InfoTaxesItems
     */
    public $TaxesInformation;
    /**
     * @var bool|mixed
     */
    public $Customization;

    /**
     * OrderedItem constructor.
     *
     * @param SimpleXMLElement $Item
     */
    public function __construct(SimpleXMLElement $Item)
    {
        $this->ASIN = (string)$Item->ASIN;
        $this->SKU = (string)$Item->SellerSKU;
        $this->OrderItemId = (string)$Item->OrderItemId;
        $this->Title = (string)$Item->Title;
        $this->QuantityOrdered = (string)$Item->QuantityOrdered;
        $this->QuantityShipped = (string)$Item->QuantityShipped;
        $this->ItemPriceCurrency = (string)$Item->ItemPrice->CurrencyCode;
        $this->ItemPriceAmount = (string)$Item->ItemPrice->Amount;
        $this->ShippingPriceCurrency = (string)$Item->ShippingPrice->CurrencyCode;
        $this->ShippingPriceAmount = (string)$Item->ShippingPrice->Amount;

        if (isset($Item->GiftWrapPrice->Amount) && (float)$Item->GiftWrapPrice->Amount) {
            $this->GifWrapPrice = (string)$Item->GiftWrapPrice->Amount;
        } else {
            $this->GifWrapPrice = null;
        }

        if (isset($Item->GiftMessageText) && Tools::strlen((string)$Item->GiftMessageText)) {
            $this->GiftMessageText = (string)$Item->GiftMessageText;
        } else {
            $this->GiftMessageText = null;
        }

        if (isset($Item->PromotionDiscount) && isset($Item->PromotionDiscount->Amount) && (float)$Item->PromotionDiscount->Amount) {
            $this->PromotionDiscountAmount = (string)$Item->PromotionDiscount->Amount;
            $this->PromotionDiscountCurrency = (string)$Item->PromotionDiscount->CurrencyCode;
        } else {
            $this->PromotionDiscountAmount = null;
            $this->PromotionDiscountCurrency = null;
        }


        if (isset($Item->ShippingDiscount) && isset($Item->ShippingDiscount->Amount) && (float)$Item->ShippingDiscount->Amount) {
            $this->ShippingDiscountAmount = (string)$Item->ShippingDiscount->Amount;
            $this->ShippingDiscountCurrency = (string)$Item->ShippingDiscount->CurrencyCode;
        } else {
            $this->ShippingDiscountAmount = null;
            $this->ShippingDiscountCurrency = null;
        }

        $this->TaxesInformation = new InfoTaxesItems($Item->ItemTax->CurrencyCode, $Item->ItemTax->Amount, $Item->ShippingTax->CurrencyCode, $Item->ShippingTax->Amount, $Item->GiftWrapTax->CurrencyCode, $Item->GiftWrapTax->Amount, false);

        if (isset($Item->BuyerCustomizedInfo, $Item->BuyerCustomizedInfo->CustomizedURL) && $this->OrderItemId) {
            $this->Customization = $this->getCustomizedData($Item->BuyerCustomizedInfo->CustomizedURL);
        }
    }

    /**
     * Get customization data from URL
     * @param $customizedUrl
     *
     * @return mixed|null|string
     */
    public function getCustomizedData($customizedUrl)
    {
        $result = null;
        $directory = _PS_DOWNLOAD_DIR_ . 'amazon/customization/';
        $temp_file = $directory . $this->OrderItemId . '.zip';

        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        if (copy($customizedUrl, $temp_file)) {
            $zip = new ZipArchive();
            if ($zip->open($temp_file) === true) {
                $content = $zip->getFromName($this->OrderItemId . '.json');
                if ($content !== false) {
                    $result = json_decode($content, true);
                    $result = serialize($result);
                }
            }
            $zip->close();
        }

        return $result;
    }

    /**
     *
     */
    public function printing()
    {
        CommonTools::p("Item in the order:");
        CommonTools::p("ASIN: $this->ASIN, SKU: $this->SKU, Title: $this->Title, QuantityOrdered: $this->QuantityOrdered, ItemPriceCurrency: $this->ItemPriceCurrency, ItemPriceAmount: $this->ItemPriceAmount, ShippingPriceCurrency: $this->ShippingPriceCurrency, ShippingPriceAmount: $this->ShippingPriceAmount.");
        CommonTools::p("Tax information for this item:");
        $this->TaxesInformation->printing();
        CommonTools::p("Item output ends here.");
    }
}

/**
 * Class InfoTaxesItems
 */
class InfoTaxesItems
{
    /**
     * @var string
     */
    public $ItemTaxCurrencyCode;
    /**
     * @var string
     */
    public $ItemTaxAmount;
    /**
     * @var string
     */
    public $ShippingTaxCurrencyCode;
    /**
     * @var string
     */
    public $ShippingTaxAmount;
    /**
     * @var string
     */
    public $GiftWrapTaxCurrencyCode;
    /**
     * @var string
     */
    public $GiftWrapTaxAmount;

    /**
     * @var bool
     */
    public $_debug;
    /**
     * @var string
     */
    public $_cr;

    /**
     * InfoTaxesItems constructor.
     *
     * @param $itcc
     * @param $ita
     * @param $stcc
     * @param $sta
     * @param $gwtcc
     * @param $gwta
     * @param bool $debug
     */
    public function __construct($itcc, $ita, $stcc, $sta, $gwtcc, $gwta, $debug = false)
    {
        if ($debug === true) {
            $this->_debug = true;
        } else {
            $this->_debug = false;
        }

        $this->_cr = nl2br(Amazon::LF);

        if ($this->_debug) {
            printf("$this->_cr __construct starts to create instance of the Taxes class. $this->_cr");
        }

        $this->ItemTaxCurrencyCode = (string)$itcc;
        $this->ItemTaxAmount = (string)$ita;
        $this->ShippingTaxCurrencyCode = (string)$stcc;
        $this->ShippingTaxAmount = (string)$sta;
        $this->GiftWrapTaxCurrencyCode = (string)$gwtcc;
        $this->GiftWrapTaxAmount = (string)$gwta;

        if ($this->_debug) {
            printf("$this->_cr Created object: $this->_cr");
            $this->printing();
            printf("$this->_cr Construct function finishes here $this->_cr");
        }
    }

    /**
     *
     */
    public function printing()
    {
        printf(
            "$this->_cr printing() function output starts here. It writes information, that contain object (NULL if not presentd): $this->_cr
            ItemTaxCurrencyCode: $this->ItemTaxCurrencyCode $this->_cr
            ItemTaxAmount $this->ItemTaxAmount $this->_cr
            ShippingTaxCurrencyCode: $this->ShippingTaxCurrencyCode $this->_cr
            ShippingTaxAmount: $this->ShippingTaxAmount $this->_cr
            GiftWrapTaxCurrencyCode $this->GiftWrapTaxCurrencyCode $this->_cr
            GiftWrapTaxAmount: $this->GiftWrapTaxAmount $this->_cr
            printing() function output finishes here. $this->_cr"
        );
    }
}
