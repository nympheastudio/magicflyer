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
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2018 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
*/

require_once(dirname(__FILE__).'/env.php');
require_once(dirname(__FILE__).'/../amazon.php');

require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.multichannel.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.carrier.class.php');

class AmazonFBAOrder extends Amazon
{
    public static $errors         = array();
    public static $log            = array();
    private $europe         = null;
    private $amazon_id_lang = null;

    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        self::$debug_mode = (bool)Configuration::get('AMAZON_DEBUG_MODE') || (bool)Tools::getValue('debug');

        $this->amazon_features = $this->getAmazonFeatures();

        // Init
        //
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $employee = null;
            $id_employee = Configuration::get('AMAZON_EMPLOYEE');

            if ($id_employee) {
                $employee = new Employee($id_employee);
            }

            if (!Validate::isLoadedObject($employee)) {
                die($this->l('Wrong Employee, please save the module configuration'));
            }

            $id_group = (int)Configuration::get('AMAZON_CUSTOMER_GROUP');

            $group = new Group($id_group);

            if (!Validate::isLoadedObject($group)) {
                $id_group = null;
            }

            if (!$id_group || !is_numeric($id_group)) {
                $id_group = Configuration::get('PS_CUSTOMER_GROUP');
            }

            $this->context->customer->is_guest = true;
            $this->context->customer->id_default_group = $id_group;
            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
    }

    public function dispatch()
    {
        $callback = Tools::getValue('callback');
        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $action = Tools::getValue('action');

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('action: %s', $action));
            CommonTools::p(sprintf('callback: %s', $callback));
        }

        switch ($action) {
            case 'list':
                $this->listFulfillmentOrder();
                break;
            case 'create':
                $this->createFulfillmentOrder();
                break;
            case 'info':
                $this->getFulfillmentOrder();
                break;
            case 'cancel':
                $this->CancelFulfillmentOrder();
                break;
            default:
                $this->FulfillmentOrderStatuses();
                break;
        }
        $output = ob_get_clean();
        $json = Tools::jsonEncode(array(
            'error' => '',
            'response' => $this->l('Nothing to do'),
            'output' => $output,
            'errors' => AmazonMultiChannel::$errors
        ));
        die((string)$callback.'('.$json.')');
    }

    public function listFulfillmentOrder()
    {
        $orders = AmazonMultiChannel::orderList(Tools::getValue('days', 30));
        CommonTools::d($orders);
    }

    public function createFulfillmentOrder()
    {
        $callback = Tools::getValue('callback');
        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        ob_start();

        $this->Init();

        if (!($id_order = (int)Tools::getValue('id_order'))) {
            print('Missing mandatory parameter, id_order');

            return (false);
        }

        if (!($order = AmazonMultiChannel::isEligible($id_order))) {
            if (Amazon::$debug_mode) {
                CommonTools::p('createFulfillmentOrder() is not eligible');
            }

            return (false);
        }

        $amazonMultiChannelOrder = new AmazonMultiChannel($id_order);

        if (Amazon::$debug_mode) {
            CommonTools::p("Multichannel Order");
            CommonTools::p(get_object_vars($amazonMultiChannelOrder));
        }

        if (!Validate::isLoadedObject($amazonMultiChannelOrder)) {
            if (Amazon::$debug_mode) {
                CommonTools::p('createFulfillmentOrder(): Validate::isLoadedObject() returned false');
            }

            return (false);
        }

        // Not already ordered, shipped or canceled
        //
        if (Tools::strlen($amazonMultiChannelOrder->marketPlaceChannelStatus)) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('createFulfillmentOrder() has already a FBA/Multichannel state'.nl2br(Amazon::LF)));
            }

            return (false);
        }

        $error = false;
        $errorMessage = null;

        if (!($AmazonFBAOrder = $amazonMultiChannelOrder->createFulfillmentOrder($this->amazon_id_lang, Amazon::$debug_mode))) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('createFulfillmentOrder() failed and returns: %s'.nl2br(Amazon::LF), nl2br(print_r($AmazonFBAOrder))));
            }
            $error = true;
        }

        if (Amazon::$debug_mode) {
            CommonTools::p("Amazon FBA Order:");
            CommonTools::p($AmazonFBAOrder);
            CommonTools::p("Errors:");
            CommonTools::p(AmazonMultiChannel::$errors);
        }

        if (is_array($AmazonFBAOrder) && count($AmazonFBAOrder)) {
            $response = sprintf($this->l('Response ID: %s'), $AmazonFBAOrder['Response']);
        } else {
            $response = 'Failed';
        }


        $output = ob_get_clean();

        $errors = self::fix_encoding(AmazonMultiChannel::$errors);
        $response = self::fix_encoding($response);
        $output = self::fix_encoding($output);

        $json = Tools::jsonEncode(array(
            'error' => $error,
            'response' => $response,
            'output' => $output,
            'errors' => $errors
        ));

        die((string)$callback.'('.$json.')');
    }

    public static function fix_encoding($to_fix)
    {
        if (is_array($to_fix) && count($to_fix)) {
            foreach ($to_fix as $key => $item) {
                if (!mb_check_encoding($item, 'UTF-8')) {
                    $to_fix[$key] = mb_convert_encoding($item, "UTF-8");
                }
            }
        } elseif (is_string($to_fix)) {
            $to_fix = mb_convert_encoding($to_fix, "UTF-8");
        }

        return ($to_fix);
    }

    public function Init()
    {
        if (Amazon::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
        // Regions
        //
        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketLang2Region = array_flip($marketPlaceRegion);

        if ((int)Tools::getValue('europe')) {
            $masterMarketplace = AmazonConfiguration::get('MASTER');

            if (isset($marketLang2Region[$masterMarketplace]) && $marketLang2Region[$masterMarketplace]) {
                $id_lang = $marketLang2Region[$masterMarketplace];
            } else {
                print('The module is not yet configured for Europe');

                return (false);
            }
            $this->europe = 1;
            $this->amazon_id_lang = (int)$id_lang;
        } else {
            if (!($lang = Tools::getValue('lang'))) {
                die(Tools::displayError('Missing parameter lang'));
            }

            if (!isset($marketLang2Region[$lang]) || empty($marketLang2Region[$lang])) {
                die(Tools::displayError('Wrong parameter lang'));
            }

            $this->amazon_id_lang = $id_lang = (int)$marketLang2Region[$lang];
            $this->europe = false;
        }

        if (Amazon::$debug_mode) {
            CommonTools::p("Init");
            CommonTools::p(sprintf('regions: %s', nl2br(print_r($marketPlaceRegion, true))));
            CommonTools::p(sprintf('europe: %s', $this->europe ? 'Yes' : 'No'));
            CommonTools::p(sprintf('id_lang: %d', $this->amazon_id_lang));
        }

        //  Check Access Tokens
        //
        $tokens = Tools::getValue('cron_token') ? Tools::getValue('cron_token') : Tools::getValue('amazon_token');

        if (!AmazonTools::checkToken($tokens)) {
            die('Wrong token');
        }

        return (true);
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }

    public function getFulfillmentOrder()
    {
        $callback = Tools::getValue('callback');

        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        ob_start();

        $this->Init();

        if (!($id_order = (int)Tools::getValue('id_order'))) {
            print('Missing mandatory parameter, id_order');

            return (false);
        }

        $amazonMultiChannelOrder = new AmazonMultiChannel($id_order);

        $orderInfo = array();
        $errorMessage = null;
        $error = false;

        if (($result = $amazonMultiChannelOrder->getFulfillmentOrder($id_order, $this->amazon_id_lang))) {
            if (isset($result->Error)) {
                $error = true;

                $errorMessage .= 'Error from Amazon:';

                if (isset($result->Error->Type)) {
                    $errorMessage .= sprintf('Type: %s', $result->Error->Type);
                }
                if (isset($result->Error->Code)) {
                    $errorMessage .= sprintf('Code: %s', $result->Error->Code);
                }
                if (isset($result->Error->Message)) {
                    $errorMessage .= sprintf('Message: %s', $result->Error->Message);
                }
            } else {
                if (isset($result->GetFulfillmentOrderResult->FulfillmentOrder->ReceivedDateTime)) {
                    $orderInfo['ReceivedDateTime'] = AmazonTools::displayDate(date('Y-m-d H:i:s', strtotime((string)$result->GetFulfillmentOrderResult->FulfillmentOrder->ReceivedDateTime)), $this->amazon_id_lang, true);
                }

                if (isset($result->GetFulfillmentOrderResult->FulfillmentOrder->StatusUpdatedDateTime)) {
                    $orderInfo['StatusUpdatedDateTime'] = AmazonTools::displayDate(date('Y-m-d H:i:s', strtotime((string)$result->GetFulfillmentOrderResult->FulfillmentOrder->StatusUpdatedDateTime)), $this->amazon_id_lang, true);
                }

                if (isset($result->GetFulfillmentOrderResult->FulfillmentOrder->ShippingSpeedCategory)) {
                    $orderInfo['ShippingSpeedCategory'] = (string)$result->GetFulfillmentOrderResult->FulfillmentOrder->ShippingSpeedCategory;
                }

                if (isset($result->GetFulfillmentOrderResult->FulfillmentOrder->FulfillmentMethod)) {
                    $orderInfo['FulfillmentMethod'] = (string)$result->GetFulfillmentOrderResult->FulfillmentOrder->FulfillmentMethod;
                }

                if (isset($result->GetFulfillmentOrderResult->FulfillmentOrder->FulfillmentOrderStatus)) {
                    $orderInfo['FulfillmentOrderStatus'] = (string)$result->GetFulfillmentOrderResult->FulfillmentOrder->FulfillmentOrderStatus;
                }

                if (isset($result->GetFulfillmentOrderResult->FulfillmentOrder->DisplayableOrderId)) {
                    $orderInfo['DisplayableOrderId'] = (string)$result->GetFulfillmentOrderResult->FulfillmentOrder->DisplayableOrderId;
                }

                if (isset($result->GetFulfillmentOrderResult->FulfillmentOrderItem)) {
                    $orderInfo['Items'] = (string)$result->GetFulfillmentOrderResult->FulfillmentOrderItem->count();
                }

                if (isset($result->GetFulfillmentOrderResult->FulfillmentOrderItem->member->EstimatedShipDateTime)) {
                    $orderInfo['EstimatedShipDateTime'] = AmazonTools::displayDate(date('Y-m-d', strtotime((string)$result->GetFulfillmentOrderResult->FulfillmentOrderItem->member->EstimatedShipDateTime)), $this->amazon_id_lang);
                }

                if (isset($result->GetFulfillmentOrderResult->FulfillmentOrderItem->member->EstimatedArrivalDateTime)) {
                    $orderInfo['EstimatedArrivalDateTime'] = AmazonTools::displayDate(date('Y-m-d', strtotime((string)$result->GetFulfillmentOrderResult->FulfillmentOrderItem->member->EstimatedArrivalDateTime)), $this->amazon_id_lang);
                }

                $amazonMultiChannelOrder->updateMpChannel($orderInfo['FulfillmentOrderStatus'], AmazonMultiChannel::AMAZON_FBA_MULTICHANNEL);
            }
        } else {
            if (Amazon::$debug_mode) {
                printf('GetFulfillmentOrder() failed and returns: %s'.nl2br(Amazon::LF), nl2br(print_r($result)));
            }

            $error = true;
        }
        $output = ob_get_clean();

        $json = Tools::jsonEncode(array(
            'error' => $error,
            'info' => $orderInfo,
            'error_message' => $errorMessage,
            'output' => $output,
            'errors' => AmazonMultiChannel::$errors
        ));

        die((string)$callback.'('.$json.')');
    }

    public function CancelFulfillmentOrder()
    {
        $callback = Tools::getValue('callback');
        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $id_employee = Configuration::get('AMAZON_EMPLOYEE');

        ob_start();

        $this->Init();

        if (!($id_order = (int)Tools::getValue('id_order'))) {
            print('Missing mandatory parameter, id_order');

            return (false);
        }

        $amazonMultiChannelOrder = new AmazonMultiChannel($id_order);

        $error = false;
        $errorMessage = null;

        if (!($result = $amazonMultiChannelOrder->CancelFulfillmentOrder($id_order, $this->amazon_id_lang))) {
            if (Amazon::$debug_mode) {
                printf('GetFulfillmentOrder() failed and returns: %s'.nl2br(Amazon::LF), nl2br(print_r($result)));
            }
            $error = true;
        } else {
            if (isset($result->Error)) {
                $error = true;
                $errorMessage .= 'Error from Amazon:';

                if (isset($result->Error->Type)) {
                    $errorMessage .= sprintf('Type: %s', $result->Error->Type);
                }
                if (isset($result->Error->Code)) {
                    $errorMessage .= sprintf('Code: %s', $result->Error->Code);
                }
                if (isset($result->Error->Message)) {
                    $errorMessage .= sprintf('Message: %s', $result->Error->Message);
                }
            } else {
                $history = new AmazonOrderHistory();
                $history->id_order = (int)$id_order;
                $history->id_employee = (int)$id_employee;
                $history->changeIdOrderState(Configuration::get('PS_OS_CANCELED'), $history->id_order, true);
                $history->addWithOutEmail(true);
            }
        }

        $output = ob_get_clean();

        $json = Tools::jsonEncode(array(
            'error' => $error,
            'error_message' => $errorMessage,
            'output' => $output,
            'errors' => AmazonMultiChannel::$errors
        ));

        die((string)$callback.'('.$json.')');
    }

    public function FulfillmentOrderStatuses()
    {
        ob_start();

        $callback = Tools::getValue('callback');
        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $order_statuses = OrderState::getOrderStates($this->id_lang);
        $paid_states = array();

        if (is_array($order_statuses) && count($order_statuses)) {
            foreach ($order_statuses as $order_status) {
                if ((bool)$order_status['paid']) {
                    $paid_states[] = (int)$order_status['id_order_state'];
                }
            }
        }

        $pending_state = (int)Configuration::get('AMAZON_FBA_MULTICHANNEL_STATE');
        $sent_state = (int)Configuration::get('AMAZON_FBA_MULTICHANNEL_SENT');
        $done_state = (int)Configuration::get('AMAZON_FBA_MULTICHANNEL_DONE');
        $id_employee = (int)Configuration::get('AMAZON_EMPLOYEE');

        if (!$pending_state) {
            if (Amazon::$debug_mode) {
                printf('FulfillmentOrderStatuses() Order State is not yet configured'.nl2br(Amazon::LF));
                die;
            }

            return (false);
        }

        $this->Init();

        $amazonMultiChannel = new AmazonMultiChannel();

        if (Amazon::$debug_mode) {
            $order_state = new OrderState($pending_state);
            CommonTools::p(sprintf('FulfillmentOrderStatuses() ordersByStatus for status: '.$pending_state.nl2br(Amazon::LF)));
            CommonTools::p($order_state);

            $sent_order_state = new OrderState($sent_state);
            CommonTools::p(sprintf('FulfillmentOrderStatuses() ordersByStatus for status: '.$sent_state.nl2br(Amazon::LF)));
            CommonTools::p($sent_order_state);
        }
        $paid_state1 = Configuration::get('PS_OS_PAYMENT');
        $paid_state2 = Configuration::get('PS_OS_WS_PAYMENT');
        $statuses = array_merge($paid_states, array($pending_state, $sent_state, $paid_state1, $paid_state2));

        $result = AmazonMultiChannel::ordersByStatus($statuses, Tools::getValue('days', 7), Tools::getValue('id_order', null));

        if (!$result || !is_array($result) || !count($result)) {
            if (Amazon::$debug_mode) {
                printf('FulfillmentOrderStatuses() ordersByStatus returned nothing'.nl2br(Amazon::LF));
                die;
            }

            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Orders Returned:'));
            CommonTools::p($result);
        }

        $dateStart = $result[0]['date_add'];

        // Merge as array('id_order' => ..)
        //
        $orders = array();

        foreach ($result as $entry) {
            $id_order = $entry['id_order'];
            $orders[$id_order] = $entry;
        }

        $result = $amazonMultiChannel->ListAllFulfillmentOrders($dateStart, $this->amazon_id_lang, Amazon::$debug_mode);

        if (!$result || !is_array($result)) {
            if (Amazon::$debug_mode) {
                printf('FulfillmentOrderStatuses() ListAllFulfillmentOrders returns nothing'.nl2br(Amazon::LF));
                die;
            }

            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('ListAllFulfillmentOrders:'));
            CommonTools::p($result);
        }

        // Merge orders informations
        //
        foreach ($result as $fulfillmentOrderss) {
            foreach ($fulfillmentOrderss as $fulfillmentOrders) {
                foreach ($fulfillmentOrders as $fulfillmentOrder) {
                    if (isset($fulfillmentOrder->SellerFulfillmentOrderId) && Tools::strlen((string)$fulfillmentOrder->SellerFulfillmentOrderId)) {
                        $orders[(string)$fulfillmentOrder->SellerFulfillmentOrderId]['FulfillmentOrderStatus'] = (string)$fulfillmentOrder->FulfillmentOrderStatus;
                        $orders[(string)$fulfillmentOrder->SellerFulfillmentOrderId]['StatusUpdatedDateTime'] = (string)$fulfillmentOrder->StatusUpdatedDateTime;
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('SellerFulfillmentOrderId: %s', (string)$fulfillmentOrder->SellerFulfillmentOrderId));
                            CommonTools::p(sprintf('FulfillmentOrderStatus: %s', (string)$fulfillmentOrder->FulfillmentOrderStatus));
                            CommonTools::p(sprintf('StatusUpdatedDateTime: %s', (string)$fulfillmentOrder->StatusUpdatedDateTime));
                        }
                    }
                }
            }
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Orders Merged:'));
            CommonTools::p($orders);
        }

        foreach ($orders as $key => $order) {
            if (!isset($order['id_order'])) {
                continue;
            }
            $id_order = (int)$order['id_order'];

            if (!isset($order['FulfillmentOrderStatus'])) {
                // not listed
                if (Amazon::$debug_mode) {
                    printf('FulfillmentOrderStatus - unlisted order ID: %s'.nl2br(Amazon::LF), $id_order);
                }
                continue;
            }

            $amazonMultiChannel = new AmazonMultiChannel($id_order);

            if (!Validate::isLoadedObject($amazonMultiChannel)) {
                if (Amazon::$debug_mode) {
                    printf('FulfillmentOrderStatuses() unable to load order id: %d'.nl2br(Amazon::LF), $id_order);
                }
                continue;
            }

            if ($order['mp_channel_status'] != $order['FulfillmentOrderStatus']) {
                $amazonMultiChannel->updateMpChannel($order['FulfillmentOrderStatus'], AmazonMultiChannel::AMAZON_FBA_MULTICHANNEL);
                $orders[$id_order]['NewFulfillmentOrderStatus'] = $order['FulfillmentOrderStatus'];
            }

            $amazon_fba_order_state = Tools::strtolower($order['FulfillmentOrderStatus']);

            switch ($amazon_fba_order_state) {
                case AmazonMultiChannel::AMAZON_FBA_STATUS_PROCESSING:
                case AmazonMultiChannel::AMAZON_FBA_STATUS_COMPLETE:
                case AmazonMultiChannel::AMAZON_FBA_STATUS_COMPLETEPARTIALLED:
                case AmazonMultiChannel::AMAZON_FBA_STATUS_RECEIVED:
                    $result = $amazonMultiChannel->getFulfillmentOrder($id_order, $amazonMultiChannel->id_lang, Amazon::$debug_mode);

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf("GetFulfillmentOrder: %s", print_r($result, true)));
                    }

                    if (isset($result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentStatus) && (string)$result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentStatus != 'SHIPPED') {
                        if (Amazon::$debug_mode) {
                            CommonTools::p("GetFulfillmentOrder: Ignoring pending order");
                        }
                        continue;
                    }

                    if (isset($result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentStatus)) {
                        if (isset($result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentPackage->member->TrackingNumber)) {
                            $trackingNumber = $result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentPackage->member->TrackingNumber;
                        } else {
                            $trackingNumber = null;
                        }

                        if (isset($result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentPackage->member->CarrierCode)) {
                            $carrierCode = $result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentPackage->member->CarrierCode;
                        } else {
                            $carrierCode = null;
                        }
                        $previous_tracking_number = null;

                        if ($carrierCode && $trackingNumber) {
                            if (Amazon::$debug_mode) {
                                CommonTools::p("Carrier: $carrierCode Tracking: $trackingNumber");
                            }

                            if (!($id_carrier_fba = AmazonCarrier::FBACarrier($carrierCode))) {
                                if (!($id_carrier_fba = AmazonCarrier::FBACarrierCreate($carrierCode))) {
                                    CommonTools::p(sprintf('FulfillmentOrderStatuses() unable add carrier: %s', $carrierCode));
                                    continue;
                                } elseif (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf("id_carrier_fba: %s", print_r($id_carrier_fba, true)));
                                }
                            }
                            $previous_tracking_number = AmazonOrder::getShippingNumber($amazonMultiChannel);

                            AmazonCarrier::updateTrackingNumber($id_order, $id_carrier_fba, $trackingNumber, Amazon::$debug_mode);
                        }
                        $order_history_list = $amazonMultiChannel->getHistory($this->id_lang);
                        
                        if (is_array($order_history_list) && count($order_history_list)) {
                            $has_shipped = false;
                            foreach ($order_history_list as $order_history_item) {
                                if ((int)$order_history_item['id_order_state'] && (int)$order_history_item['id_order_state'] == $sent_state) {
                                    $has_shipped = true;
                                }
                            }
                            if (Tools::strlen($trackingNumber)) {
                                $has_shipped = true;
                            }

                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf("OrderHistory - List: %s", print_r($order_history_list, true)));
                                CommonTools::p(sprintf("OrderHistory - Has Shipped: %s", $has_shipped ? 'True' : 'False'));
                            }
                        }


                        if (!$has_shipped && isset($result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentStatus) && (string)$result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentStatus == 'SHIPPED') {
                            $new_state = $sent_state;
                        } else {
                            $new_state = $has_shipped && in_array($amazon_fba_order_state, array(AmazonMultiChannel::AMAZON_FBA_STATUS_COMPLETE)) && (int)$done_state ? (int)$done_state : (int)$sent_state;
                        }

                        $arrival_date_time1 = null;
                        $arrival_date_time2 = null;

                        // Prevent to switch the status to delivered earlier
                        if (isset($result->GetFulfillmentOrderResult->FulfillmentShipment->member->EstimatedArrivalDateTime) && Tools::strlen((string)$result->GetFulfillmentOrderResult->FulfillmentShipment->member->EstimatedArrivalDateTime)) {
                            $arrival_date_time1 = strtotime((string)$result->GetFulfillmentOrderResult->FulfillmentShipment->member->EstimatedArrivalDateTime);
                        }
                        if (isset($result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentPackage->member->EstimatedArrivalDateTime) && Tools::strlen((string)$result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentPackage->member->EstimatedArrivalDateTime)) {
                            $arrival_date_time2 = strtotime((string)$result->GetFulfillmentOrderResult->FulfillmentShipment->member->FulfillmentShipmentPackage->member->EstimatedArrivalDateTime);
                        }

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf("current_state: %s", $amazonMultiChannel->current_state));
                            CommonTools::p(sprintf("new_state: %s", $new_state));
                            CommonTools::p(sprintf("arrival_date_time1: %s", date('c', $arrival_date_time1)));
                            CommonTools::p(sprintf("arrival_date_time2: %s", date('c', $arrival_date_time2)));
                        }

                        if (($arrival_date_time1 || $arrival_date_time2) && $new_state == $done_state && max($arrival_date_time1, $arrival_date_time2) > time()) {
                            $new_state = (int)$sent_state;

                            if (Amazon::$debug_mode) {
                                CommonTools::p("Switch to sent state as order is not supposed to be arrived");
                            }
                        }
                        if (is_array($order_history_list) && count($order_history_list)) {
                            $last_history = reset($order_history_list);
                            if ((int)$last_history['id_order_state'] != (int)$new_state) {
                                $amazonMultiChannel->current_state = null;
                            }
                        }
                        
                        if ($amazonMultiChannel->current_state == $new_state) {
                            CommonTools::p(sprintf('FulfillmentOrderStatuses() order has already the same state: %d', $new_state));
                            continue;
                        }

                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf("Switching to state: %s", $new_state));
                        }
                        $amazonMultiChannel->addToHistory($id_employee, $new_state);
                        $amazonMultiChannel->current_state = $new_state;
                        $amazonMultiChannel->update();

                        $orders[$id_order]['id_order_state'] = $new_state;
                    }
                    break;

                default:
                    if (Amazon::$debug_mode) {
                        CommonTools::p("Status ignored: $amazon_fba_order_state");
                    }
            }
        }
        die;
    }
}

$amazonFBAOrder = new AmazonFBAOrder();
$amazonFBAOrder->dispatch();
