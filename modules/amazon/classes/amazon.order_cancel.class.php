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

class AmazonOrderCancel extends Amazon
{
    const NO_INVENTORY = 1;
    const SHIPPING_ADDRESS_UNDELIVERABLE = 2;
    const CUSTOMER_EXCHANGE = 3;
    const BUYER_CANCELED = 4;
    const GENERAL_ADJUSTMENT = 5;
    const CARRIER_CREDIT_DECISION = 6;
    const RISK_ASSESSMENT_INFORMATION_NOT_VALID = 7;
    const CARRIER_COVERAGE_FAILURE = 8;
    const CUSTOMER_RETURN = 9;
    const MERCHANDISE_NOT_RECEIVED = 10;

    public static $reasons = array(
        self::NO_INVENTORY => 'NoInventory',
        self::SHIPPING_ADDRESS_UNDELIVERABLE => 'ShippingAddressUndeliverable',
        self::CUSTOMER_EXCHANGE => 'CustomerExchange',
        self::BUYER_CANCELED => 'BuyerCanceled',
        self::GENERAL_ADJUSTMENT => 'GeneralAdjustment',
        self::CARRIER_CREDIT_DECISION => 'CarrierCreditDecision',
        self::RISK_ASSESSMENT_INFORMATION_NOT_VALID => 'RiskAssessmentInformationNotValid',
        self::CARRIER_COVERAGE_FAILURE => 'CarrierCoverageFailure',
        self::CUSTOMER_RETURN => 'CustomerReturn',
        self::MERCHANDISE_NOT_RECEIVED => 'MerchandiseNotReceived'
    );
    public static $available_reasons = array(self::NO_INVENTORY, self::SHIPPING_ADDRESS_UNDELIVERABLE, self::CUSTOMER_EXCHANGE, self::BUYER_CANCELED, self::GENERAL_ADJUSTMENT, self::CUSTOMER_RETURN, self::CUSTOMER_RETURN, self::MERCHANDISE_NOT_RECEIVED);



    public function l($string, $specific = false, $id_lang = null)
    {
        return (parent::l($string, basename(__FILE__, '.php'), $id_lang));
    }

    public function getReasons()
    {
        $reasons = array();

        foreach (self::$reasons as $key => $reason) {
            if (!in_array($key, self::$available_reasons)) {
                continue;
            }
            switch ($key) {
                case self::NO_INVENTORY:
                    $reason = $this->l('Out of stock');
                    break;
                case self::SHIPPING_ADDRESS_UNDELIVERABLE:
                    $reason = $this->l('Address undeliverable');
                    break;
                case self::CUSTOMER_EXCHANGE:
                    $reason = $this->l('Customer exchange');
                    break;
                case self::BUYER_CANCELED:
                    $reason = $this->l('Buyer canceled');
                    break;
                case self::GENERAL_ADJUSTMENT:
                    $reason = $this->l('General adjustement');
                    break;
                case self::CARRIER_COVERAGE_FAILURE:
                    $reason = $this->l('Carrier coverage failure');
                    break;
                case self::CUSTOMER_RETURN:
                    $reason = $this->l('Customer return');
                    break;
                case self::CUSTOMER_EXCHANGE:
                    $reason = $this->l('Customer exchange');
                    break;
                case self::MERCHANDISE_NOT_RECEIVED:
                    $reason = $this->l('Merchandise not received');
                    break;
            }
            $reasons[$key] = $reason;
        }
        return($reasons);
    }

    public function changeOrderStatus($id_order, $status, $reason = null)
    {
        $order = new AmazonOrder($id_order);

        if (!Validate::isLoadedObject($order)) {
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    'Unable to load order, id: '.$id_order.Amazon::LF
                ));
            }
            return(false);
        }

        if (!property_exists($order, 'amazon_order_info') || !$order->amazon_order_info instanceof AmazonOrderInfo || !$order->amazon_order_info->is_standard_feature_available) {
            return(false);
        }

        if (!Tools::strlen($order->amazon_order_info->mp_order_id)) {
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    'Missing marketplace order id: '.$id_order.Amazon::LF
                ));
            }

            return(false);
        }

        if ($status == $order->amazon_order_info->mp_status) {
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    'Order has already the same status: '.$id_order.Amazon::LF
                ));
            }

            return(false);
        }
        if ($order->amazon_order_info->channel == Amazon::AFN) {
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    'No way to cancel AFN fulfilled order: '.$id_order.Amazon::LF
                ));
            }
            return(false);
        }
        
        if (!in_array($status, array(AmazonOrder::TO_CANCEL, AmazonOrder::CANCELED, AmazonOrder::PROCESS_CANCEL, AmazonOrder::REVERT_CANCEL))) {
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    'Wrong status for order id: '.$id_order.Amazon::LF
                ));
            }
            return(false);
        }

        switch ($status) {
            case AmazonOrder::TO_CANCEL:
                // Flag the order as to be canceled (order_canceled.php)
                $order->amazon_order_info->mp_status = $status;
                break;
            
            case AmazonOrder::PROCESS_CANCEL:
                if (!$reason || !is_numeric($reason)) {
                    if (Amazon::$debug_mode) {
                        AmazonTools::pre(array(
                            sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                            'Wrong reason for order id: '.$id_order.' reason: '.print_r($reason, true).Amazon::LF
                        ));
                    }

                    return(false);
                }
                $order->amazon_order_info->mp_status = $status;
                $this->updateOrderedItems($order->amazon_order_info->mp_order_id, $reason);
                break;
            
            case AmazonOrder::REVERT_CANCEL:
                $order->amazon_order_info->mp_status = $status;
                $this->updateOrderedItems($order->amazon_order_info->mp_order_id, null);
                break;

            case AmazonOrder::CANCELED:
                break;
            
            default:
                if (Amazon::$debug_mode) {
                    AmazonTools::pre(array(
                        sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                        'Wrong status for order id: '.$id_order.Amazon::LF
                    ));
                }
                return(false);
        }
        return($order->amazon_order_info->saveOrderInfo());
    }

    public function updateOrderedItems($mp_order_id, $reason)
    {
        $ordered_items = AmazonOrderItem::getOrderItems($mp_order_id);

        if (!is_array($ordered_items) || !count($ordered_items)) {
            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    'AmazonOrderItem::getOrderItems return nothing for order: '.$mp_order_id.Amazon::LF
                ));
            }
            return(false);
        }
        
        foreach ($ordered_items as $order_item_id) {
            $order_item = new AmazonOrderItem($mp_order_id, $order_item_id);

            if ($order_item->order_item_id == $order_item_id) {
                $order_item->reason = (int)$reason ? self::$reasons[$reason] : null;
                $order_item->saveOrderItem();
            }
        }
    }

    public function getOrders($id_lang_list)
    {
        $delay = 3;
        $id_canceled_state = AmazonConfiguration::get('CANCELED_STATE');

        if (AmazonTools::tableExists(_DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS)) {
            $sql = 'SELECT o.`id_order`, mp.`mp_order_id` FROM `'._DB_PREFIX_.'orders` o
                    LEFT JOIN `'._DB_PREFIX_.Amazon::TABLE_MARKETPLACE_ORDERS.'` mp ON (o.`id_order` = mp.`id_order`)
                    WHERE (o.`module` = "amazon" OR o.`module` = "Amazon") AND o.`current_state` = '.(int)$id_canceled_state.' AND o.`id_lang` IN ('.pSQL($id_lang_list).') AND mp.`mp_order_id` > "" AND mp.`mp_status` = '.(int)AmazonOrder::PROCESS_CANCEL.'
                    AND o.`date_upd` > DATE_ADD(NOW(), INTERVAL -'.(int)$delay.' DAY)
                    GROUP by o.`id_order`, mp.`mp_order_id`';

            $result = Db::getInstance()->executeS($sql);

            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'.Amazon::LF, basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    'SQL: '.$sql.Amazon::LF,
                    'result: '.print_r($result, true).Amazon::LF
                ));
            }

            return($result);
        } else {
            return(false);
        }
    }
}
