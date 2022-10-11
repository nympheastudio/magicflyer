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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Lnp2PickUpRESTLibrary
{
    protected $base_url;
    protected $token_url = null;
    protected $token_realm = null;
    protected $pickup_username = null;
    protected $pickup_password = null;
    protected $url;
    protected $curl_error = null;
    protected $curl_info = null;
    protected $logger = null;
    private $count = 0;

    public function __construct($url)
    {
        $this->base_url        = $url;
        $this->token_url       = LaNavettePickup::getTokenAPI();
        $this->token_realm     = 'http://pickup.c2c.fr';
        $this->pickup_username = Configuration::get('LNP2_NAVETTE_PARTNER_ID');
        $this->pickup_password = Configuration::get('LNP2_NAVETTE_PARTNER_PWD');
        if ((bool)Configuration::get('LNP2_LOGS')) {
            $this->logger = new Lnp2Logger();
        }
    }

    /**
     * @return bool
     */
    public static function isConfigured()
    {
        $username = Configuration::get('LNP2_NAVETTE_PARTNER_ID');
        $password = Configuration::get('LNP2_NAVETTE_PARTNER_PWD');

        return (bool)($username & $password);
    }

    /**
     * Get Token
     *
     * @param bool $new
     * @param bool $recall
     * @return bool|string
     */
    public function getToken($new = false, $recall = false)
    {
        if (!self::isConfigured()) {
            return false;
        }
        $token = Configuration::get('LNP2_ACCESS_TOKEN');
        if ((null == $token || true == $new) && $this->count < 5) {
            $this->count++;
            $params            = array('realm' => $this->token_realm);
            $getTokenRequestWS = new Lnp2GetTokenRequestWS($params);
            if (count($getTokenRequestWS->validation_errors)) {
                if ($this->logger) {
                    $this->logger->logError('validation error : '.print_r($getTokenRequestWS->validation_errors, true));
                }

                return $getTokenRequestWS->validation_errors;
            }

            $endpoint   = '';
            $method     = 'GET';
            $need_token = false;
            $headers    = array();

            $access_token = $this->execute($endpoint, $params, $method, $need_token, $headers);

            if ($access_token == false || !(isset($access_token->access_token))) {
                if (!$recall && $this->curl_info && isset($this->curl_info['http_code']) && $this->curl_info['http_code'] == 400) {
                    sleep(2);

                    return $this->getToken(true, true);
                }

                return false;
            }
            Configuration::updateValue('LNP2_ACCESS_TOKEN', $access_token->access_token);

            return $access_token->access_token;
        } else {
            if ($this->count == 5 && $this->logger) {
                $this->logger->logError('reach maximum getToken without response.');
            }

            return $token;
        }
    }

    /**
     * @param       $endpoint
     * @param       $params
     * @param       $method
     * @param bool  $need_token
     * @param array $headers
     *
     * @return mixed
     */
    public function execute($endpoint, $params, $method, $need_token = true, $headers = array())
    {
        if (!self::isConfigured()) {
            return false;
        }
        if ($need_token == true) {
            $url   = $this->base_url.'/'.$endpoint.'';
            $token = $this->getToken();
            if (!$token || $token == '') {
                if ($this->logger) {
                    $this->backTraceError('no token found for ');
                }
            }
            $headers = array('Authorization: Basic '.$token);
        } else {
            if ($endpoint) {
                $url = $this->token_url.'/'.$endpoint.'';
            } else {
                $url = $this->token_url;
            }
        }
        $this->url = $url;

        $response = $this->callApi($url, $params, $method, $need_token, $headers);

        # token expired
        if (401 == $this->curl_info['http_code']) {
            Configuration::updateValue('LNP2_ACCESS_TOKEN', null);
            if ($this->logger) {
                $this->logger->logError('Lnp2PickUpRESTLibrary::execute: token has expired, try to get a new one');
            }
            $token = $this->getToken(true);
            if (!$token || $token == '') {
                if ($this->logger) {
                    $this->backTraceError('no token found for ');
                }
            }
            if (true == $need_token) {
                $headers = array('Authorization: Basic '.$token);
            }
            $response = $this->callApi($url, $params, $method, $need_token, $headers);
        }

        if (!$response || $this->curl_error || $this->curl_info['http_code'] == 400) {
            if ($this->logger) {
                $this->logger->logError('Lnp2PickUpRESTLibrary::execute : response : '.print_r($response, true));
                $this->logger->logError("Lnp2PickUpRESTLibrary::execute REQUEST: Endpoint : ".$url."\n, method : ".$method.",\n Header : ".print_r($headers, true).",\n PostFields : ".print_r($params, true));
                $this->logger->logError("Lnp2PickUpRESTLibrary::execute -> curl_error : ".print_r($this->curl_error, true));
                $this->logger->logError("Lnp2PickUpRESTLibrary::execute -> curl_info : ".print_r($this->curl_info, true));
            }

            if (false !== $response) {
                return Tools::jsonDecode($response);
            }

            return false;
        }

        return Tools::jsonDecode($response);
    }

    /**
     * @param       $url
     * @param       $params
     * @param       $method
     * @param bool  $need_token
     * @param array $headers
     *
     * @return mixed
     */
    public function callApi($url, $params, $method, $need_token = false, $headers = array())
    {
        if (!self::isConfigured()) {
            return false;
        }
//        if ($need_token) {
//            $token = $this->getToken();
//        }

        if (isset($params->fields)) {
            unset($params->fields);
        }

        if (isset($params->validation_errors)) {
            unset($params->validation_errors);
        }

        if (is_object($params)) {
            $params = get_object_vars($params);
        }

        $postFields = http_build_query($params);

        $curl = curl_init();

        if ($method == 'GET') {
            if (!preg_match('#\?#', $url)) {
                $url .= '?';
            }
            curl_setopt($curl, CURLOPT_HTTPGET, 1);
            $url .= $postFields;
            if ($this->logger != null) {
                $this->logger->logSuccess("REQUEST: GET : ".$url);
            }
            $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
        } else {
            $data_string = Tools::jsonEncode($params);
            if ($this->logger != null) {
                $this->logger->logSuccess("REQUEST: POST : DATA : ".$data_string);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            $headers[] = 'Content-Type: application/json; charset=utf-8';
        }

        if (!$need_token) {
            # when we don't need token, it's when we create it. We have to be identified.
            curl_setopt($curl, CURLOPT_USERPWD, $this->pickup_username.":".$this->pickup_password);
        }

        if ($headers && count($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => "",
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            )
        );

        $response         = curl_exec($curl);
        $this->curl_error = curl_error($curl);
        $this->curl_info  = curl_getinfo($curl);
        curl_close($curl);

        if ($this->logger) {
            if (!$response) {
                $this->logger->logError("REQUEST: Endpoint : ".$url."\n, method : ".$method.",\n Header : ".print_r($headers, true).",\n PostFields : ".print_r($params, true));
                $this->logger->logError("EXCEPTION: ".$this->curl_error);
            } else {
                $this->logger->logSuccess("REQUEST: Endpoint : ".$url."\n, method : ".$method.",\n Header : ".print_r($headers, true).",\n PostFields : ".print_r($params, true));
                $this->logger->logSuccess("RESPONSE: ".print_r(Tools::jsonDecode($response), true));
            }
        }

        return $response;
    }

    private function backTraceError($msg)
    {
        $backtrace = debug_backtrace();
        if (count($backtrace) > 4) {
            $this->logger->logError($msg.$backtrace[4]['function'].' -> '.$backtrace[3]['function'].' -> '.$backtrace[2]['function'].' -> '.$backtrace[1]['function']);
        } elseif (count($backtrace) > 3) {
            $this->logger->logError($msg.$backtrace[3]['function'].' -> '.$backtrace[2]['function'].' -> '.$backtrace[1]['function']);
        } elseif (count($backtrace) > 2) {
            $this->logger->logError($msg.$backtrace[2]['function'].' -> '.$backtrace[1]['function']);
        } elseif (count($backtrace) > 1) {
            $this->logger->logError($msg);
        }
    }
}
