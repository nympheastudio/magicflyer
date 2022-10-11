<?php
class webservicesHelper
{
    const CHRONOPOST_REVERSE_R = '4R'; // for Chronopost Reverse 9
    const CHRONOPOST_REVERSE_S = '4S'; // for Chronopost Reverse 10
    const CHRONOPOST_REVERSE_T = '4T'; // for Chronopost Reverse 13
    const CHRONOPOST_REVERSE_U = '4U'; // for Chronopost Reverse 18
    const CHRONOPOST_REVERSE_DEFAULT = '01'; // for Chronopost Reverse 18

    const CHRONOPOST_REVERSE_R_SERVICE = '885'; // for Chronopost Reverse 9
    const CHRONOPOST_REVERSE_S_SERVICE = '180'; // for Chronopost Reverse 10
    const CHRONOPOST_REVERSE_T_SERVICE = '898'; // for Chronopost Reverse 13
    const CHRONOPOST_REVERSE_U_SERVICE = '835'; // for Chronopost Reverse 18
    const CHRONOPOST_REVERSE_DEFAULT_SERVICE = '226';  // for Chronopost Reverse 18
    protected $methodsAllowed = false;

    /* Calcul codePruct and codeService for Reverse (return) */

    /**
     * @param Address $adress
     *
     * @return int|string
     */
    public function getReturnProductCode($adress)
    {
        $productCodes = $this->getMethods($adress);
        $productReturnCodes = $this->getReturnProductCodesAllowed($productCodes);
        sort($productReturnCodes, SORT_STRING);

        foreach ($this->getMatriceReturnCode() as $code => $combinaisonCodes) {
            if (in_array($productReturnCodes, $combinaisonCodes)) {
                return $code;
            }
        }

        return static::CHRONOPOST_REVERSE_DEFAULT;
    }

    protected function getReturnProductCodesAllowed($productCodes)
    {
        $possibleReturnProductCode = array(
            static::CHRONOPOST_REVERSE_R,
            static::CHRONOPOST_REVERSE_S,
            static::CHRONOPOST_REVERSE_T,
            static::CHRONOPOST_REVERSE_U
        );
        $returnProductCode = array();
        foreach ($productCodes as $code) {
            if (in_array($code, $possibleReturnProductCode)) {
                array_push($returnProductCode, $code);
            }
        }

        return (sizeof($returnProductCode) > 0) ? $returnProductCode : array(static::CHRONOPOST_REVERSE_DEFAULT);

    }

    public function getReturnServiceCode($code)
    {
        switch ($code) {
            case static::CHRONOPOST_REVERSE_R:
                return static::CHRONOPOST_REVERSE_R_SERVICE;
                break;
            case static::CHRONOPOST_REVERSE_S:
                return static::CHRONOPOST_REVERSE_S_SERVICE;
                break;
            case static::CHRONOPOST_REVERSE_T:
                return static::CHRONOPOST_REVERSE_T_SERVICE;
                break;
            case static::CHRONOPOST_REVERSE_U:
                return static::CHRONOPOST_REVERSE_U_SERVICE;
                break;
            case static::CHRONOPOST_REVERSE_DEFAULT:
                return static::CHRONOPOST_REVERSE_DEFAULT_SERVICE;
                break;
            default :
                return static::CHRONOPOST_REVERSE_DEFAULT_SERVICE;
                break;
        }
    }

    public function getMatriceReturnCode()
    {
        return array(
            static::CHRONOPOST_REVERSE_R       => array(
                array(static::CHRONOPOST_REVERSE_R),
                array(static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_U)
            ),
            static::CHRONOPOST_REVERSE_S       => array(
                array(static::CHRONOPOST_REVERSE_S),
                array(static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_S),
                array(static::CHRONOPOST_REVERSE_S, static::CHRONOPOST_REVERSE_U),
                array(static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_S, static::CHRONOPOST_REVERSE_U)
            ),
            static::CHRONOPOST_REVERSE_U       => array(
                array(static::CHRONOPOST_REVERSE_U)
            ),
            static::CHRONOPOST_REVERSE_T       => array(
                array(static::CHRONOPOST_REVERSE_T),
                array(static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_T),
                array(static::CHRONOPOST_REVERSE_S, static::CHRONOPOST_REVERSE_T),
                array(static::CHRONOPOST_REVERSE_T, static::CHRONOPOST_REVERSE_U),
                array(static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_S, static::CHRONOPOST_REVERSE_T),
                array(static::CHRONOPOST_REVERSE_R, static::CHRONOPOST_REVERSE_T, static::CHRONOPOST_REVERSE_U),
                array(static::CHRONOPOST_REVERSE_S, static::CHRONOPOST_REVERSE_T, static::CHRONOPOST_REVERSE_U),
                array(
                    static::CHRONOPOST_REVERSE_R,
                    static::CHRONOPOST_REVERSE_S,
                    static::CHRONOPOST_REVERSE_T,
                    static::CHRONOPOST_REVERSE_U
                )
            ),
            static::CHRONOPOST_REVERSE_DEFAULT => array(
                array(static::CHRONOPOST_REVERSE_DEFAULT)
            )
        );
    }

    /**
     * @param Address $address
     *
     * @return array|bool
     */
    public function getMethods($address)
    {
        try {
            if ($this->methodsAllowed === false) {
                $country = new Country($address->id_country);
                $this->methodsAllowed = array();
                $client = new SoapClient("https://www.chronopost.fr/quickcost-cxf/QuickcostServiceWS?wsdl",
                    array('trace' => 0, 'connection_timeout' => 10));
                $params = array(
                    'accountNumber'  => $this->getConfigurationAccountNumber(),
                    'password'       => $this->getConfigurationAccountPass(),
                    'depCountryCode' => $this->getConfigurationShipperInfo('country'),
                    'depZipCode'     => $this->getConfigurationShipperInfo('zipcode'),
                    'arrCountryCode' => $this->getFilledValue($country->iso_code),
                    'arrZipCode'     => $this->getFilledValue($address->postcode),
                    'arrCity'        => $address->city ? $this->getFilledValue($address->city) : '-',
                    'type'           => 'M',
                    'weight'         => 1
                );
                $webservbt = $client->calculateProducts($params);
                if ($webservbt->return->errorCode == 0 && $webservbt->return->productList) {
                    if (is_array($webservbt->return->productList)) {
                        foreach ($webservbt->return->productList as $product) {
                            $this->methodsAllowed[] = $product->productCode;
                        }
                    } else { /* cas ou il y a un seul résultat */
                        $product = $webservbt->return->productList;
                        $this->methodsAllowed[] = $product->productCode;
                    }
                }
            }

            return $this->methodsAllowed;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getConfigurationAccountNumber() {
        return Configuration::get('CHRONOPOST_GENERAL_ACCOUNT');
    }
    public function getConfigurationAccountPass() {
        return Configuration::get('CHRONOPOST_GENERAL_PASSWORD');
    }
    public function getConfigurationShipperInfo($param) {
        return Configuration::get('CHRONOPOST_SHIPPER_' . strtoupper($param));
    }

    public function getFilledValue($value)
    {
        if ($value) {
            return $this->removeaccents(trim($value));
        } else {
            return '';
        }
    }

    public function removeaccents($string)
    {
        $stringToReturn = str_replace(
            array(
                'à',
                'á',
                'â',
                'ã',
                'ä',
                'ç',
                'è',
                'é',
                'ê',
                'ë',
                'ì',
                'í',
                'î',
                'ï',
                'ñ',
                'ò',
                'ó',
                'ô',
                'õ',
                'ö',
                'ù',
                'ú',
                'û',
                'ü',
                'ý',
                'ÿ',
                'À',
                'Á',
                'Â',
                'Ã',
                'Ä',
                'Ç',
                'È',
                'É',
                'Ê',
                'Ë',
                'Ì',
                'Í',
                'Î',
                'Ï',
                'Ñ',
                'Ò',
                'Ó',
                'Ô',
                'Õ',
                'Ö',
                'Ù',
                'Ú',
                'Û',
                'Ü',
                'Ý',
                '/',
                '\xa8'
            ), array(
            'a',
            'a',
            'a',
            'a',
            'a',
            'c',
            'e',
            'e',
            'e',
            'e',
            'i',
            'i',
            'i',
            'i',
            'n',
            'o',
            'o',
            'o',
            'o',
            'o',
            'u',
            'u',
            'u',
            'u',
            'y',
            'y',
            'A',
            'A',
            'A',
            'A',
            'A',
            'C',
            'E',
            'E',
            'E',
            'E',
            'I',
            'I',
            'I',
            'I',
            'N',
            'O',
            'O',
            'O',
            'O',
            'O',
            'U',
            'U',
            'U',
            'U',
            'Y',
            ' ',
            'e'
        ), $string);
        // Remove all remaining other unknown characters
        $stringToReturn = preg_replace('/[^a-zA-Z0-9\-]/', ' ', $stringToReturn);
        $stringToReturn = preg_replace('/^[\-]+/', '', $stringToReturn);
        $stringToReturn = preg_replace('/[\-]+$/', '', $stringToReturn);
        $stringToReturn = preg_replace('/[\-]{2,}/', ' ', $stringToReturn);

        return $stringToReturn;
    }
}