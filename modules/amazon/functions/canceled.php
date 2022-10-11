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
require_once(dirname(__FILE__).'/../classes/amazon.tools.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_cancel.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_item.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order_info.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.order.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.mail.logger.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.orderhistory.class.php');
require_once(dirname(__FILE__).'/../classes/amazon.batch.class.php');

class AmazonBulkCancelMode extends Amazon
{
    public function __construct()
    {
        parent::__construct();

        AmazonContext::restore($this->context);

        $this->_debug = (bool)Configuration::get('AMAZON_DEBUG_MODE');

        if (Tools::getValue('debug')) {
            $this->_debug = true;
        }

        if ($this->_debug) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }
    
    public function dispatch()
    {
        $action = Tools::getValue('action');

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('action: %s', $action));
        }

        switch ($action) {
            case 'cancel':
                $this->switchToCancel();
                break;
            default:
                $this->bulkCancel();
                break;
        }
    }

    public function switchToCancel()
    {
        //  Check Access Tokens
        //
        $tokens = Tools::getValue('amazon_token');
        $error = false;
        $status = Tools::getValue('cancel_status');

        if (!AmazonTools::checkToken($tokens)) {
            die('Wrong token');
        }

        $callback = Tools::getValue('callback');
        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        $id_order = (int)Tools::getValue('id_order');
        $reason = (int)Tools::getValue('reason');

        if (!$status) {
            die(Tools::displayError('Missing status'));
        }
        if (!$id_order) {
            die(Tools::displayError('Missing id_order'));
        }
        if (!$reason && $status == AmazonOrder::PROCESS_CANCEL) {
            die(Tools::displayError('Missing reason'));
        }

        $order = new AmazonOrder($id_order);

        if (!Validate::isLoadedObject($order)) {
            die(Tools::displayError('Unable to load order id:'.$id_order));
        }

        $message = null;

        switch ($status) {
            case AmazonOrder::PROCESS_CANCEL:
                $order_cancel = new AmazonOrderCancel();
                if (!$result = $order_cancel->changeOrderStatus($id_order, $status, $reason)) {
                    $message = $this->l('Unable to change the status');
                    $error = true;
                } else {
                    $message = $this->l('Order cancellation has been successfully scheduled');
                }
                break;
            case AmazonOrder::REVERT_CANCEL:
                $order_cancel = new AmazonOrderCancel();
                if (!$result = $order_cancel->changeOrderStatus($id_order, $status)) {
                    $message = $this->l('Unable to change the status');
                    $error = true;
                } else {
                    $message = $this->l('Order cancellation has been suspended');
                }
                break;
        }
        
        $json = Tools::jsonEncode(array(
            'error' => !$result || $error,
            'response' => $this->l('Nothing to do'),
            'result' => $result ? $message : ob_get_clean(),
        ));
        die((string)$callback.'('.$json.')');
    }

    public function bulkCancel()
    {
        $message = null;

        $tokens = Tools::getValue('cron_token');
        $amazon_lang = Tools::getValue('lang');
        $europe = (int)Tools::getValue('europe');
        $timestart = time();

        $id_lang = Language::getIdByIso($amazon_lang);

        if (!AmazonTools::checkToken($tokens)) {
            die('Wrong Token');
        }

        if (!$amazon_lang) {
            echo $this->l('No selected language, nothing to do...');
            die;
        }
        $debug = $this->_debug;

        $actives = AmazonConfiguration::get('ACTIVE');

        // Orders States
        //
        $id_canceled_state = AmazonConfiguration::get('CANCELED_STATE');

        $pass = true;

        if ($id_canceled_state) {
            $order_state = new OrderState($id_canceled_state);

            if (!Validate::isLoadedObject($order_state)) {
                $pass = false;
            }
        } else {
            $pass = false;
        }

        if (!$pass) {
            printf('Please configure canceled order state in your module configuration first.');
            die;
        }

        if (!$this->amazon_features['cancel_orders']) {
            printf('Feature is not active, you can activate it in Features tab.');
            die;
        }

        $send_email = (bool)Configuration::get('AMAZON_EMAIL');
        $notify = false;

        // Regions
        //
        $marketPlaceRegion = AmazonConfiguration::get('REGION');
        $marketLang2Region = array_flip($marketPlaceRegion);

        $id_lang_list = '';

        // Making id_lang_list to select orders
        //
        if ($europe) {
            foreach (AmazonTools::languages() as $language) {
                if (!isset($actives[$language['id_lang']]) || !$actives[$language['id_lang']]) {
                    continue;
                }

                if (!AmazonTools::isUnifiedAccount($marketPlaceRegion[$language['id_lang']])) {
                    continue;
                }

                $id_lang_list .= $language['id_lang'].',';
            }
            $id_lang_list = rtrim($id_lang_list, ',');
        } else {
            if (!isset($marketLang2Region[$amazon_lang])) {
                die('No selected language, nothing to do...');
            }

            $id_lang_list = array($marketLang2Region[$amazon_lang]);
        }

        $id_lang = Language::getIdByIso($amazon_lang);

        if ($debug) {
            printf('Parameters: %s'.self::LF, $id_canceled_state);
        }

        // Init Amazon
        //
        $platform = AmazonTools::selectPlatform($id_lang, $debug);

        if ($debug) {
            echo print_r($platform['auth'], true).print_r($platform['params'], true).print_r($platform['platforms'], true);
        }
        $pass = true;

        if (!($amazonApi = new AmazonWebService($platform['auth'], $platform['params'], null, $debug))) {
            echo $this->l('Unable to login').self::LF;
            ;
            $pass = false;
        }
        $amazonApi->demo = $this->amazon_features['demo_mode'];

        $createdAfterDate = date('c', strtotime('now - 1 week'));
        $createdBeforeDate = date('c', strtotime('now - 15 min'));

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s()/#%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf('Date range: %s - %s', $createdAfterDate, $createdBeforeDate));
        }
        
        if ($pass) {
            // Send canceled orders to the marketplacce
            $order_cancel = new AmazonOrderCancel();
            $orders = $order_cancel->getOrders($id_lang_list);

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s()/#%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p(sprintf('Orders: %s', print_r($orders, true)));
            }

            $to_cancel = array();

            if (is_array($orders) && count($orders)) {
                foreach ($orders as $order_item) {
                    $id_order = (int)$order_item['id_order'];
                    $mp_order_id = $order_item['mp_order_id'];

                    if (!$id_order || !Tools::strlen($mp_order_id)) {
                        continue;
                    }
                    $ordered_items = AmazonOrderItem::getOrderItems($mp_order_id);

                    if (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p('Ordered Items: '.print_r($ordered_items, true).Amazon::LF);
                    }

                    if (is_array($ordered_items) && count($ordered_items)) {
                        $to_cancel[$mp_order_id] = array();
                        $to_cancel[$mp_order_id]['merchant_order_id'] = $id_order;
                        $to_cancel[$mp_order_id]['mp_order_id'] = $mp_order_id;
                        $to_cancel[$mp_order_id]['items'] = array();
                        ;

                        foreach ($ordered_items as $ordered_item) {
                            if (!Tools::strlen($ordered_item)) {
                                continue;
                            }

                            $order_item = new AmazonOrderItem($mp_order_id, $ordered_item);

                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                                CommonTools::p('Order Item: '.print_r($order_item, true).Amazon::LF);
                            }

                            if ($order_item instanceof AmazonOrderItem && Tools::strlen($order_item->order_item_id)) {
                                $to_cancel[$mp_order_id]['items'][$ordered_item] = array();
                                $to_cancel[$mp_order_id]['items'][$ordered_item]['order_item_id'] = $order_item->order_item_id;
                                $to_cancel[$mp_order_id]['items'][$ordered_item]['merchant_item_id'] = $order_item->sku;
                                $to_cancel[$mp_order_id]['items'][$ordered_item]['reason'] = $order_item->reason;

                                if (Amazon::$debug_mode) {
                                    CommonTools::p(sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                                    CommonTools::p('To Cancel: '.print_r($to_cancel[$mp_order_id]['items'][$ordered_item], true).Amazon::LF);
                                }
                            }
                        }
                    }
                }
            }
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s()/#%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p(sprintf('To Cancel: %s', print_r($to_cancel, true)));
            }
            
            if (is_array($to_cancel) && count($to_cancel)) {
                $result = $amazonApi->cancelOrders($to_cancel);

                if (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s()/#%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(sprintf('Cancel result: %s', print_r($result, true)));
                }
                
                if ($result instanceof SimpleXMLElement && isset($result->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId)) {
                    $submission_feed_if = (int)$result->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;

                    if ($submission_feed_if) {
                        $batches = new AmazonBatches('batch_cancel');
                        $batch = new AmazonBatch($timestart);
                        $batch->id = $submission_feed_if;
                        $batch->timestop = time();
                        $batch->type = 'Cancel (Orders)';
                        $batch->region = $europe ? 'eu' : $marketLang2Region[$id_lang];
                        $batch->created = 0;
                        $batch->updated = count($to_cancel);
                        $batch->deleted = 0;
                        $batches->add($batch);
                        $batches->save();

                        foreach ($to_cancel as $canceled_order) {
                            $id_order = $canceled_order['merchant_order_id'];
                            
                            if ((int)$id_order) {
                                $order = new AmazonOrder($id_order);

                                if (Validate::isLoadedObject($order)) {
                                    $order->amazon_order_info->mp_status = AmazonOrder::CANCELED;
                                    $order->amazon_order_info->saveOrderInfo();
                                }
                            }
                        }
                        
                        if ($send_email) {
                            $notify = true;

                            foreach ($to_cancel as $order) {
                                $message .= date('c').self::LF;
                                $message .= sprintf('%s', $this->l('Canceled Order')).self::LF;
                                $message .= sprintf('Amazon Order ID : %s', $order['mp_order_id']).self::LF;
                                $message .= sprintf('Prestashop Order ID : %s', $order['merchant_order_id']).self::LF;
                                $message .= sprintf('Products : %s', is_array($order['items']) ? count($order['items']) : 0).self::LF;
                                $message .= self::LF;
                            }
                        }
                    } elseif (Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p('Error: '.print_r($result, true).Amazon::LF);
                    }
                } elseif (Amazon::$debug_mode) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p('Error: '.print_r($result, true).Amazon::LF);
                }
            }
            
            // Get canceled orders from the marketplace
            $canceledOrders = $amazonApi->GetCanceledOrdersList($createdAfterDate, $createdBeforeDate);

            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s()/#%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p(sprintf('Canceled Orders from Amazon: %s', print_r($canceledOrders, true)));
            }
            
            if (is_array($canceledOrders) && count($canceledOrders)) {
                foreach ($canceledOrders as $canceledOrder) {
                    if (!property_exists($canceledOrder, 'AmazonOrderId')) {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s - %s::%s()/#%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                            CommonTools::p(sprintf('Missing property AmazonOrderId'));
                        }
                        continue;
                    }

                    $amazon_order_id = (string)$canceledOrder->AmazonOrderId;

                    if (!($id_order = AmazonOrder::checkByMpId($amazon_order_id))) {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('%s - %s::%s()/#%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                            CommonTools::p(sprintf('Unable to find order: %s', $amazon_order_id));
                        }
                        continue;
                    }

                    $order = new Order($id_order);

                    if (!Validate::isLoadedObject($order)) {
                        if ($debug) {
                            printf('Unable to load order: %d', $id_order);
                        }
                        continue;
                    }

                    $order_history = $order->getHistory($id_lang);

                    $cancelable = true;
                    $canceled = false;

                    if (is_array($order_history) && count($order_history)) {
                        foreach ($order_history as $order_state) {
                            if ((int)$id_canceled_state && (int)$order_state['id_order_state'] == (int)$id_canceled_state) {
                                $canceled = true;
                            }
                            if (isset($order_state['shipped']) && (bool)$order_state['shipped']) {
                                $cancelable = false;
                            }
                            if (isset($order_state['deleted']) && (bool)$order_state['deleted']) {
                                $cancelable = false;
                            }
                        }
                    }

                    $do_cancel = false;
                    $alert = null;

                    if (!$canceled && $cancelable) {
                        $do_cancel = true;
                        $alert = $this->l('Canceling Order');
                    } elseif (!$canceled && !$cancelable) {
                        $do_cancel = false;
                        $alert = $this->l('Warning: unable to cancel this order');
                    } elseif ($canceled) {
                        continue;
                    }
                    if ($alert && Amazon::$debug_mode) {
                        CommonTools::p(sprintf('%s - %s::%s()/#%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                        CommonTools::p(sprintf('Alert: %s', $alert));
                        CommonTools::p(sprintf('Do cancel: %s', $do_cancel ? 'Yes' : 'No'));
                    }

                    if ($do_cancel) {
                        $this->addToHistory($id_order, $id_canceled_state);

                        if ($send_email) {
                            $notify = true;
                            $message .= date('c').self::LF;
                            $message .= sprintf('%s', $this->l('Canceled Order')).self::LF;
                            $message .= sprintf('Amazon Order ID : %s', $amazon_order_id).self::LF;
                            $message .= sprintf('Prestashop Order ID : %s', $id_order).self::LF;
                            $message .= sprintf('Ordered on : %s', $canceledOrder->PurchaseDate).self::LF;
                            if (property_exists($canceledOrder, 'SalesChannel')) {
                                $message .= sprintf('From : %s', $canceledOrder->SalesChannel).self::LF;
                            }
                            if ($alert) {
                                $message .= sprintf('Action : %s', $alert).self::LF;
                            }
                            $message .= self::LF;
                        }
                    }
                }
            }
        }
        if ($notify) {
            AmazonMailLogger::message($message);
        }
        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s()/#%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf('Notify by email: %s', $notify ? 'Yes' : 'No'));
            CommonTools::p(sprintf('Email message: %s', print_r($message, true)));
        }
        die($message);
    }

    private function addToHistory($id_order, $id_order_state)
    {
        $id_employee = Configuration::get('AMAZON_EMPLOYEE');
        // Add History
        $new_history = new AmazonOrderHistory();
        $new_history->id_order = (int)$id_order;
        $new_history->id_employee = (int)$id_employee ? (int)$id_employee : 1;
        $new_history->changeIdOrderState($id_order_state, $id_order);
        $new_history->addWithOutEmail(true);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s()/#%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(sprintf('Adding to history: %s', print_r(get_object_vars($new_history), true)));
        }

        return;
    }

    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }
}

$amazonBulkMode = new AmazonBulkCancelMode;
$amazonBulkMode->dispatch();
