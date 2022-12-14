<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/*
 * Interface
 */
require_once(dirname(__FILE__) . '/IMondialRelayWSMethod.php');

/*
 * Allow to create tickets - 'WSI2_CreationEtiquette'
 */

class MRCreateTickets implements IMondialRelayWSMethod
{
    public $class_name = __CLASS__;

    private $_fields = array(
        'id_mr_selected' => 0,
        'list'           => array(
            'Enseigne'     => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[0-9A-Z]{2}[0-9A-Z ]{6}$#'
            ),
            'ModeCol'      => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^(CCC|CDR|CDS|REL)$#'
            ),
            'ModeLiv'      => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^(LCC|LD1|LDS|24R|ESP|DRI|HOM)$#'
            ),
            'NDossier'     => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(|[0-9A-Z_ -]{0,15})$#'
            ),
            'NClient'      => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(|[0-9A-Z]{0,9})$#'
            ),
            'Expe_Langage' => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[A-Z]{2}$#'
            ),
            'Expe_Ad1'     => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[0-9A-Z_\-\'., /]{2,32}$#'
            ),
            'Expe_Ad2'     => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^[0-9A-Z_\-\'., /]{0,32}$#'
            ),
            'Expe_Ad3'     => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[0-9A-Z_\-\'., /]{0,32}$#'
            ),
            'Expe_Ad4'     => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^[0-9A-Z]{2}[0-9A-Z ]{6}$#'
            ),
            'Expe_Ville'   => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[A-Z_\-\' 0-9]{2,26}$#'
            ),
            'Expe_CP'      => array(
                'required'         => true,
                'value'            => '',
                'params'           => array(),
                'methodValidation' => 'checkZipcodeByCountry'
            ),
            'Expe_Pays'    => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[A-Z]{2}$#'
            ),
            'Expe_Tel1'    => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^((00|\+)[1-9]{2}|0)[0-9][0-9]{7,8}$#'
            ),
            'Expe_Tel2'    => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^((00|\+)[1-9]{2}|0)[0-9][0-9]{7,8}$#'
            ),
            'Expe_Mail'    => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^[\w\-\.\@_]{0,70}$#'
            ),
            'Dest_Langage' => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[A-Z]{2}$#'
            ),
            'Dest_Ad1'     => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[0-9A-Z_\-\'., /]{2,32}$#'
            ),
            'Dest_Ad2'     => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^[0-9A-Z_\-\'., /]{2,32}$#'
            ),
            'Dest_Ad3'     => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[0-9A-Z_\-\'., /]{2,32}$#'
            ),
            'Dest_Ad4'     => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^[0-9A-Z_\-\'., /]{0,32}$#'
            ),
            'Dest_Ville'   => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[A-Z_\-\' 0-9]{2,26}$#'
            ),
            'Dest_CP'      => array(
                'required'         => true,
                'value'            => '',
                'params'           => array(),
                'methodValidation' => 'checkZipcodeByCountry'
            ),
            'Dest_Pays'    => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[A-Z]{2}$#'
            ),
            'Dest_Tel1'    => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^((00|\+)[1-9]{2}|0)[0-9][0-9]{7,8}$#'
            ),
            'Dest_Tel2'    => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^((00|\+)[1-9]{2}|0)[0-9][0-9]{7,8}$#'
            ),
            'Dest_Mail'    => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^[\w\-\.\@_]{0,70}$#'
            ),
            'Poids'        => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[0-9]{3,7}$#'
            ),
            'Longueur'     => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^[0-9]{0,3}$#'
            ),
            'Taille'       => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(XS|S|M|L|XL|XXL|3XL)$#'
            ),
            'NbColis'      => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[0-9]{1,2}$#'
            ),
            'CRT_Valeur'   => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[0-9]{1,7}$#'
            ),
            'CRT_Devise'   => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(|EUR)$#'
            ),
            'Exp_Valeur'   => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^[0-9]{0,7}$#'
            ),
            'Exp_Devise'   => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(|EUR)$#'
            ),
            'COL_Rel_Pays' => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^[A-Z]{2}$#'
            ),
            'COL_Rel'      => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(|[0-9]{6})$#'
            ),
            'LIV_Rel_Pays' => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^[A-Z]{2}$#'
            ),
            'LIV_Rel'      => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(|[0-9]{6})$#'
            ),
            'TAvisage'     => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(|O|N)$#'
            ),
            'TReprise'     => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(|O|N)$#'
            ),
            'Montage'      => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(|[0-9]{1,3})$#'
            ),
            'TRDV'         => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(|O|N)$#'
            ),
            'Assurance'    => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^(|[0-9A-Z]{1})$#'
            ),
            'Instructions' => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^[0-9A-Z_\-\'., /]{0,31}#'
            ),
            'Security'     => array(
                'required'        => true,
                'value'           => '',
                'regexValidation' => '#^[0-9A-Z]{32}$#'
            ),
            'Texte'        => array(
                'required'        => false,
                'value'           => '',
                'regexValidation' => '#^([^<>&\']{3,30})(\(cr\)[^<>&\']{0,30}){0,9}$#'
            )
        )
    );

    private $_orderListId = null;
    private $_totalOrder = 0;
    private $_weightList = null;
    private $_insuranceList = null;
    private $_mondialrelay = null;
    private $_fieldsList = array();


    private $_resultList = array(
        'error'   => array(),
        'success' => array()
    );

    private $_webserviceURL = '';

    public function __construct($params, $object)
    {
        $this->_orderListId = $params['orderIdList'];
        $this->_totalOrder = $params['totalOrder'];
        $this->_weightList = $params['weightList'];
        $this->_insuranceList = isset($params['insuranceList']) ? $params['insuranceList'] : '';
        $this->_mondialrelay = $object;
        if (isset($params['id_shop_selected'])) {
            $this->id_shop_selected = $params['id_shop_selected'];
        }

     //   $this->_webServiceKey = $this->_mondialrelay->account_shop['MR_KEY_WEBSERVICE'];
      //  $this->_markCode = $this->_mondialrelay->account_shop['MR_CODE_MARQUE'];
        $this->class_name = Tools::strtolower($this->class_name);

        $this->_webserviceURL = MondialRelay::MR_URL . 'webservice/Web_Services.asmx?WSDL';
    }

    public function __destruct()
    {
        unset($this->_mondialrelay);
    }

    /*
     * Build a correct weight format (NNNNN)
     */
    private function _weightFormat($weight)
    {
        return sprintf("%05s", $weight);
    }

    /*
     * Set the default value to the order paramaters
     */
    private function _setRequestDefaultValue()
    {

        $this->_fields['list']['Expe_Ad1']['value'] = Configuration::get('PS_SHOP_NAME');
        $this->_fields['list']['Expe_Ad3']['value'] = Configuration::get('PS_SHOP_ADDR1');
        // Deleted, cause to many failed for the process
        // $this->_fields['list']['Expe_Ad4']['value'] = Configuration::get('PS_SHOP_ADDR2');
        $this->_fields['list']['Expe_Ville']['value'] = Configuration::get('PS_SHOP_CITY');
        $this->_fields['list']['Expe_CP']['value'] = Configuration::get('PS_SHOP_CODE');
        $this->_fields['list']['Expe_CP']['params']['id_country'] = Configuration::get('PS_COUNTRY_DEFAULT');

        if (version_compare(_PS_VERSION_, '1.4', '>=')) {
            $this->_fields['list']['Expe_Pays']['value'] = Country::getIsoById(Configuration::get('PS_SHOP_COUNTRY_ID'));
            $this->_fields['list']['Expe_CP']['params']['id_country'] = Configuration::get('PS_SHOP_COUNTRY_ID');
        } else {
            $this->_fields['list']['Expe_Pays']['value'] = Tools::substr(Configuration::get('PS_SHOP_COUNTRY'), 0, 2);
        }
        $this->_fields['list']['Expe_Tel1']['value'] = MRTools::getFormatedPhone(Configuration::get('PS_SHOP_PHONE'));
        $this->_fields['list']['Expe_Mail']['value'] = Configuration::get('PS_SHOP_EMAIL');
        $this->_fields['list']['NbColis']['value'] = 1;
        $this->_fields['list']['CRT_Valeur']['value'] = 0;
        $this->_fields['list']['CRT_Devise']['value'] = 'EUR';
    }

    /*
     * Initiate the data needed to be send properly
     * Can manage a list of data for multiple request
     */
    public function init()
    {
        if ($this->_totalOrder == 0) {
            throw new Exception($this->_mondialrelay->l('Please select at least one order', $this->class_name));
        }

        $this->_setRequestDefaultValue();

        if (count($orderListDetails = $this->_mondialrelay->getOrders($this->_orderListId, MondialRelay::NO_FILTER, 0, $this->id_shop_selected))) {
            foreach ($orderListDetails as $orderDetail) {
                if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                    $this->_mondialrelay->account_shop = unserialize((string)Configuration::get('MR_ACCOUNT_DETAIL', null, null, $orderDetail['id_shop']));
                } else {
                    $this->_mondialrelay->account_shop = unserialize((string)Configuration::get('MR_ACCOUNT_DETAIL'));
                }

                // Storage temporary
                $base = $this->_fields;
                $tmp = &$base['list'];

                $tmp['Enseigne']['value'] = $this->_mondialrelay->account_shop['MR_ENSEIGNE_WEBSERVICE'];
                $tmp['Expe_Langage']['value'] = $this->_mondialrelay->account_shop['MR_LANGUAGE'];

                $deliveriesAddress = new Address($orderDetail['id_address_delivery']);
                $customer = new Customer($orderDetail['id_customer']);

                // Store the weight order set by the user

                foreach ($this->_weightList as $orderWeightInfos) {
                    $detail = explode('-', $orderWeightInfos);
                    if (count($detail) == 2 && $detail[1] == $orderDetail['id_order']) {
                        $tmp['Poids']['value'] = (float)$this->_weightFormat($detail[0]);
                    }
                }
                foreach ($this->_insuranceList as $insurance) {
                    $detail = explode('-', $insurance);
                    if ($detail[1] == $orderDetail['id_order']) {
                        $tmp['Assurance']['value'] = (count($detail) == 2) ? (int)($detail[0]) : $orderDetail['mr_ModeAss'];
                    }
                }
                $dest_tel = (!empty($deliveriesAddress->phone)) ? MRTools::getFormatedPhone($deliveriesAddress->phone) : '';
                $dest_tel2 = (!empty($deliveriesAddress->phone_mobile)) ? MRTools::getFormatedPhone($deliveriesAddress->phone_mobile) : '';
                $destIsoCode = Country::getIsoById($deliveriesAddress->id_country);
                $tmp['ModeCol']['value'] = $orderDetail['mr_ModeCol'];
                $tmp['ModeLiv']['value'] = $orderDetail['mr_ModeLiv'];
                $tmp['NDossier']['value'] = $orderDetail['id_order'];
                $tmp['NClient']['value'] = $orderDetail['id_customer'];
                $tmp['Dest_Langage']['value'] = 'FR'; //Language::getIsoById($orderDetail['id_lang']);
                $tmp['Dest_Ad1']['value'] = preg_replace(
                    MRTools::REGEX_CLEAN_ADDR,
                    '',
                    Tools::substr(MRTools::removeAccents($deliveriesAddress->firstname . ' ' . $deliveriesAddress->lastname), 0, 32)
                );
                $tmp['Dest_Ad2']['value'] = preg_replace(
                    MRTools::REGEX_CLEAN_ADDR,
                    '',
                    Tools::substr(MRTools::removeAccents($deliveriesAddress->address2), 0, 32)
                );
                $tmp['Dest_Ad3']['value'] = preg_replace(
                    MRTools::REGEX_CLEAN_ADDR,
                    '',
                    Tools::substr(MRTools::removeAccents($deliveriesAddress->address1), 0, 32)
                );
                $tmp['Dest_Ville']['value'] = $deliveriesAddress->city;
                $tmp['Dest_CP']['value'] = $deliveriesAddress->postcode;
                $tmp['Dest_CP']['params']['id_country'] = $deliveriesAddress->id_country;
                $tmp['Dest_Pays']['value'] = $destIsoCode;
                $tmp['Dest_Tel1']['value'] = $dest_tel;
                $tmp['Dest_Tel2']['value'] = $dest_tel2;
                $tmp['Dest_Mail']['value'] = $customer->email;
                if ($orderDetail['mr_ModeLiv'] != 'LD1' && $orderDetail['mr_ModeLiv'] != 'LDS' && $orderDetail['mr_ModeLiv'] != 'HOM') {
                    $tmp['LIV_Rel_Pays']['value'] = $orderDetail['MR_Selected_Pays'];
                    $tmp['LIV_Rel']['value'] = $orderDetail['MR_Selected_Num'];
                }

                // Store the necessary information to the root case table
                $base['id_mr_selected'] = $orderDetail['id_mr_selected'];

                // Add the temporary values to a field list for multiple request
                $this->_fieldsList[] = $base;
                unset($deliveriesAddress);
                unset($customer);
                $this->_generateMD5SecurityKey($this->_mondialrelay->account_shop);
            }
        }
    }

    /*
     * Generate the MD5 key for each param list
     */
    private function _generateMD5SecurityKey($account_shop)
    {
        // RootCase is the array case where the main information are stored
        // it's an array containing id_mr_selected and an array with the necessary fields
        foreach ($this->_fieldsList as &$rootCase) {
            $concatenationValue = '';
            foreach ($rootCase['list'] as $paramName => &$valueDetailed) {
                if ($paramName != 'Texte' && $paramName != 'Security') {
                    // Mac server make an empty string instead of a cleaned string
                    // TODO : test on windows and linux server
                    $cleanedString = MRTools::removeAccents($valueDetailed['value']);
                    $valueDetailed['value'] = !empty($cleanedString) ? Tools::strtoupper($cleanedString) : Tools::strtoupper($valueDetailed['value']);

                    // Call a pointer function if exist to do different test
                    if (isset($valueDetailed['methodValidation'])
                        && method_exists('MRTools', $valueDetailed['methodValidation'])
                        && isset($valueDetailed['params'])
                        && call_user_func('MRTools::'.$valueDetailed['methodValidation'], $valueDetailed['value'], $valueDetailed['params'])) {
                        $concatenationValue .= $valueDetailed['value'];
                    } elseif (isset($valueDetailed['regexValidation'])
                        && preg_match($valueDetailed['regexValidation'], $valueDetailed['value'], $matches)
                    ) {
                        // Use simple Regex test given by MondialRelay
                        $concatenationValue .= $valueDetailed['value'];
                    } else { // If the key is required, we set an error, else it's skipped
                        if ((!Tools::strlen($valueDetailed['value']) && $valueDetailed['required']) || Tools::strlen($valueDetailed['value'])) {
                            if (empty($valueDetailed['value'])) {
                                $error = $this->_mondialrelay->l('This key', $this->class_name)
                                    . ' [' . $paramName . '] '
                                    . $this->_mondialrelay->l('is empty and need to be filled', $this->class_name);
                            } else {
                                $error = 'This key [' . $paramName . '] hasn not a valid value format : ' . $valueDetailed['value'];
                            }
                            $this->_resultList['error'][$rootCase['list']['NDossier']['value']][] = $error;
                        }
                    }
                }
            }
            $concatenationValue .= $account_shop['MR_KEY_WEBSERVICE'];
            $rootCase['list']['Security']['value'] = Tools::strtoupper(md5($concatenationValue));
        }
    }

    /*
     * Update the tables used and send mail with the order history
     */
    private function _updateTable($params, $expeditionNum, $ticketURL, $trackingURL, $id_mr_selected)
    {
        $sql = '
            UPDATE `' . _DB_PREFIX_ . 'mr_selected`
            SET `MR_poids` = \'' . pSQL($params['Poids']) . '\',
                `MR_insurance` = \'' . pSQL($params['Assurance']) . '\',
                    `exp_number` = \'' . pSQL($expeditionNum) . '\',
                    `url_etiquette` = \'' . pSQL($ticketURL) . '\',
                    `url_suivi` = \'' . pSQL($trackingURL) . '\'
            WHERE id_mr_selected = ' . (int)$id_mr_selected;
        Db::getInstance()->execute($sql);

        // NDossier contains the id_order
        $order = new Order($params['NDossier']);

        // Update the database for order and orderHistory
        $order->shipping_number = $expeditionNum;
        $order->update();

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            //Retrieve Order Carrier
            $sql = 'SELECT `id_order_carrier`
                    FROM `' . _DB_PREFIX_ . 'order_carrier`
                    WHERE `id_order` = ' . (int)$order->id;

            $id_order_carrier = Db::getInstance()->getValue($sql);

            if ($id_order_carrier) {
                $order_carrier = new OrderCarrier((int)$id_order_carrier);
                if (Validate::isLoadedObject($order_carrier)) {
                    $order_carrier->tracking_number = pSQL($expeditionNum);
                    $order_carrier->update();
                }
            }
        }

        $templateVars = array('{followup}' => $trackingURL);
        $orderState = (Configuration::get('PS_OS_SHIPPING')) ?
            Configuration::get('PS_OS_SHIPPING') :
            _PS_OS_SHIPPING_;

        $history = new OrderHistory();
        $history->id_order = (int)$params['NDossier'];
        if (version_compare(_PS_VERSION_, '1.5.2', '>=')) {
            $history->changeIdOrderState((int)$orderState, $order);
        } else {
            $history->changeIdOrderState((int)$orderState, (int)($params['NDossier']));
        }
        $history->id_employee = (isset(Context::getContext()->employee->id) ? (int)Context::getContext()->employee->id : '');
        $history->addWithemail(true, $templateVars);
        unset($order);
        unset($order_carrier);
        unset($history);
    }

    /*
     * Manage the return value of the webservice, handle the errors or build the
     * succeed message
     */
    private function _parseResult($client, $result, $params, $id_mr_selected)
    {
        $errors = &$this->_resultList['error'][$params['NDossier']];
        $success = &$this->_resultList['success'][$params['NDossier']];
        $result = $result->WSI2_CreationEtiquetteResult;
        if (($errorNumber = $result->STAT) != 0) {
            $errors[] = $this->_mondialrelay->l('There is an error number : ', $this->class_name) . $errorNumber;
            $errors[] = $this->_mondialrelay->l('Details : ', $this->class_name) .
                $this->_mondialrelay->getErrorCodeDetail($errorNumber);
        } else {
            $order = new Order($params['NDossier']);
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $this->_mondialrelay->account_shop = unserialize((string)Configuration::get('MR_ACCOUNT_DETAIL', null, null, $order->id_shop));
            } else {
                $this->_mondialrelay->account_shop = unserialize((string)Configuration::get('MR_ACCOUNT_DETAIL'));
            }
            $baseURL = MondialRelay::MR_URL;
            $expedition = $result->ExpeditionNum;
            $securityKey = Tools::strtoupper(md5(
                '<' . $params['Enseigne'] . $this->_mondialrelay->account_shop['MR_CODE_MARQUE'] .
                '>' . $expedition . '<' . $this->_mondialrelay->account_shop['MR_KEY_WEBSERVICE'] . '>'
            ));
            $ticketURL = $baseURL . $result->URL_Etiquette;
            $trackingURL = $baseURL .
                'public/permanent/tracking.aspx?ens=' . $params['Enseigne'] . $this->_mondialrelay->account_shop['MR_CODE_MARQUE'] . '&exp=' . $expedition . '&language=' . Configuration::get('PS_LANG_DEFAULT') . '&crc=' . $securityKey;

            $success['displayExpedition'] = $this->_mondialrelay->l('Expedition Number : ', $this->class_name) . $expedition;
            $success['displayTicketURL'] = $this->_mondialrelay->l('Ticket URL : ', $this->class_name) . $ticketURL;
            $success['displayTrackingURL'] = $this->_mondialrelay->l('Tracking URL: ', $this->class_name) . $trackingURL;
            $success['expeditionNumber'] = $expedition;

            $this->_updateTable($params, $expedition, $ticketURL, $trackingURL, $id_mr_selected);
        }
    }

    /*
     * Send one or multiple request to the webservice
     */
    public function send()
    {
        if ($client = new SoapClient($this->_webserviceURL)) {
            $client->soap_defencoding = 'UTF-8';
            $client->decode_utf8 = false;

            foreach ($this->_fieldsList as $rootCase) {
                $params = $this->_getSimpleParamArray($rootCase['list']);
                $result = $client->WSI2_CreationEtiquette($params);
                $this->_parseResult($client, $result, $params, $rootCase['id_mr_selected']);
            }
            unset($client);
            Configuration::updateValue('MONDIALRELAY_CONFIGURATION_OK', true);
        } else {
            throw new Exception($this->_mondialrelay->l('The Mondial Relay webservice is not currently reliable', $this->class_name));
        }
    }

    /*
    ** Check if the shop parameter are currently well configured
    */
    public function checkPreValidation()
    {
        $errorList = array('error' => array(), 'warn' => array());

        $list = array(
            'Expe_Langage' => array(
                'value' => $this->_mondialrelay->account_shop['MR_LANGUAGE'],
                'error' => $this->_mondialrelay->l('Please check your language configuration', $this->class_name)
            ),
            'Expe_Ad1'     => array(
                'value' => Configuration::get('PS_SHOP_NAME'),
                'error' => $this->_mondialrelay->l('Please check your shop name configuration', $this->class_name)
            ),
            'Expe_Ad3'     => array(
                'value' => Configuration::get('PS_SHOP_ADDR1'),
                'error' => $this->_mondialrelay->l('Please check your address 1 configuration', $this->class_name)
            ),
            'Expe_Ville'   => array(
                'value' => Configuration::get('PS_SHOP_CITY'),
                'error' => $this->_mondialrelay->l('Please check your city configuration', $this->class_name)
            ),
            'Expe_CP'      => array(
                'value' => Configuration::get('PS_SHOP_CODE'),
                'error' => $this->_mondialrelay->l('Please check your zipcode configuration', $this->class_name)
            ),
            'Expe_Pays'    => array(
                'value' => ((version_compare(_PS_VERSION_, '1.4', '>=')) ?
                    Country::getIsoById(Configuration::get('PS_SHOP_COUNTRY_ID')) :
                    Tools::substr(Configuration::get('PS_SHOP_COUNTRY'), 0, 2)),
                'error' => $this->_mondialrelay->l('Please check your country configuration', $this->class_name)
            ),
            'Expe_Tel1'    => array(
                'value' => MRTools::getFormatedPhone(Configuration::get('PS_SHOP_PHONE')),
                'error' => $this->_mondialrelay->l('Please check your Phone configuration', $this->class_name)
            ),
            'Expe_Mail'    => array(
                'value' => Configuration::get('PS_SHOP_EMAIL'),
                'error' => $this->_mondialrelay->l('Please check your mail configuration', $this->class_name)
            )
        );

        foreach ($list as $name => $tab) {
            // Mac server make an empty string instead of a cleaned string
            // TODO : test on windows and linux server
            $cleanedString = MRTools::removeAccents($tab['value']);
            $tab['value'] = !empty($cleanedString) ? Tools::strtoupper($cleanedString) : Tools::strtoupper($tab['value']);

            if ($name == 'Expe_CP') {
                if (version_compare(_PS_VERSION_, '1.4', '>=')) {
                    if (!(MRTools::checkZipcodeByCountry($tab['value'], array(
                        'id_country' => Configuration::get('PS_SHOP_COUNTRY_ID')
                    )))
                    ) {
                        $errorList['error'][$name] = $tab['error'];
                    }
                } else {
                    $errorList['warn'][$name] = $this->_mondialrelay->l('Post code cannot be validated with PrestaShop versions older than 1.4', $this->class_name);
                }
            } elseif (isset($this->_fields['list'][$name]['regexValidation'])
                && (!preg_match($this->_fields['list'][$name]['regexValidation'], $tab['value'], $matches))
            ) {
                $errorList['error'][$name] = $tab['error'];
            }
        }

        return $errorList;
    }

    /*
         * Get the values with associated fields name
         * @fields : array containing multiple values information
         */
    private function _getSimpleParamArray($fields)
    {
        $params = array();

        foreach ($fields as $keyName => $valueDetailed) {
            $params[$keyName] = $valueDetailed['value'];
        }

        return $params;
    }

    /*
     * Return the fields list
     */
    public function getFieldsList()
    {
        return $this->_fieldsList['list'];
    }

    /*
     * Return the result of one or multiple sent requests
     */
    public function getResult()
    {
        return $this->_resultList;
    }

    /*
     * Return which number order of the list is currently managed
     */
    public static function getCurrentRequestUnderTraitment()
    {
        // TODO: Build a SQL Query to know how many request have been executed
    }
}
