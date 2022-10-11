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

require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.order_cancel.class.php');
require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.order_info.class.php');
require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.order.class.php');
require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.tools.class.php');
require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.multichannel.class.php');
require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.order_item.class.php');

class AmazonAdminOrder extends Amazon
{
    private $_tokens;
    private $_debug;
    private $_send_email;

    /**
     * Displays Order Informations
     * @param $params
     * @return string
     */
    public function marketplaceOrderDisplay($params)
    {
        $multichannel = (bool)Configuration::get('AMAZON_FBA_MULTICHANNEL');
        $canceled_state = AmazonConfiguration::get('CANCELED_STATE');

        $this->context = Context::getContext();

        $id_order = (int)$params['id_order'];

        $amazonOrder = new AmazonOrder($id_order);

        if (!Validate::isLoadedObject($amazonOrder)) {
            if (Amazon::$debug_mode) {
                die(Tools::displayError(sprintf('%s/%d: Unable to load order: %d', basename(__FILE__), __LINE__, $id_order)));
            }

            return (false);
        }

        if (Tools::strtolower($amazonOrder->module) != 'amazon' && $amazonOrder->marketPlaceChannel != AmazonMultiChannel::AMAZON_FBA_MULTICHANNEL && !$multichannel) {
            return (false);
        }

        $this->_tokens = AmazonConfiguration::get('CRON_TOKEN');
        $this->_send_email = (bool)Configuration::get('AMAZON_EMAIL');
        $this->_order_id_lang = $this->id_lang;
        $this->_multichannel = $multichannel;

        $cancel_stage = false;

        if ($amazonOrder->id_lang) {
            $this->_order_id_lang = $amazonOrder->id_lang;
        }

        $view_params = array();
        $view_params['context_key'] = null;
        $view_params['debug'] = false;
        $view_params['marketplace_order_id'] = null;

        $view_params['class_warning'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
        $view_params['class_error'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
        $view_params['class_success'] = 'confirm '.($this->ps16x ? 'alert alert-success' : 'conf');
        $view_params['class_info'] = 'hint '.($this->ps16x ? 'alert alert-info' : 'conf');

        if (isset($this->context) && $this->context instanceof Context && file_exists(_PS_MODULE_DIR_.'/amazon/classes/amazon.context.class.php')) {
            require_once(_PS_MODULE_DIR_.'/amazon/classes/amazon.context.class.php');
            $view_params['context_key'] = AmazonContext::getKey($this->context->shop);
        } else {
            $view_params['context_key'] = null;
        }

        if (Amazon::$debug_mode) {
            $view_params['debug'] = true;
        }

        if ($amazonOrder->marketPlaceOrderId) {
            $view_params['marketplace_order_id'] = $amazonOrder->marketPlaceOrderId;
        }

        $view_params['marketplace_channel'] = null;

        if (isset($amazonOrder->marketPlaceChannel) && $amazonOrder->marketPlaceChannel) {
            switch ($amazonOrder->marketPlaceChannel) {
                case AmazonMultiChannel::AMAZON_FBA_AMAZON:
                    $view_params['marketplace_channel'] = $this->l('Fulfilled By Amazon');
                    break;
                case AmazonMultiChannel::AMAZON_FBA_MULTICHANNEL:
                    $view_params['marketplace_channel'] = $this->l('Multi-Channel Order / Fulfilled By Amazon');
                    break;
            }
        }

        if ($amazonOrder->amazon_order_info instanceof AmazonOrderInfo && $amazonOrder->amazon_order_info->is_extended_feature_available && $amazonOrder->amazon_order_info->getOrderInfo()) {
            $view_params['amazon_order_info'] = array();
            if ($amazonOrder->amazon_order_info->is_prime) {
                $view_params['amazon_order_info']['is_prime']['label'] = $this->l('Prime Order', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['is_prime']['value'] = $this->l('Yes', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['is_prime']['bold'] = true;
                $view_params['amazon_order_info']['is_prime']['color'] = 'red';
            }

            if ($amazonOrder->amazon_order_info->is_premium) {
                $view_params['amazon_order_info']['is_premium']['label'] = $this->l('Premium Order', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['is_premium']['value'] = $this->l('Yes', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['is_premium']['bold'] = true;
                $view_params['amazon_order_info']['is_premium']['color'] = 'red';
            }

            if ($amazonOrder->amazon_order_info->is_business) {
                $view_params['amazon_order_info']['is_business']['label'] = $this->l('Business Order', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['is_business']['value'] = $this->l('Yes', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['is_business']['bold'] = true;
                $view_params['amazon_order_info']['is_business']['color'] = 'darkblue';
            }

            if ($amazonOrder->amazon_order_info->sales_channel) {
                $view_params['amazon_order_info']['sales_channel']['label'] = $this->l('Sales Channel', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['sales_channel']['value'] = $amazonOrder->amazon_order_info->sales_channel;
                $view_params['amazon_order_info']['sales_channel']['bold'] = false;
                $view_params['amazon_order_info']['sales_channel']['color'] = null;
            }

            if ($amazonOrder->amazon_order_info->order_channel) {
                $view_params['amazon_order_info']['order_channel']['label'] = $this->l('Order Channel', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['order_channel']['value'] = $amazonOrder->amazon_order_info->order_channel;
                $view_params['amazon_order_info']['order_channel']['bold'] = false;
                $view_params['amazon_order_info']['order_channel']['color'] = null;
            }

            if ($amazonOrder->amazon_order_info->ship_service_level) {
                $view_params['amazon_order_info']['ship_service_level']['label'] = $this->l('Ship Service Level', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['ship_service_level']['value'] = $amazonOrder->amazon_order_info->ship_service_level;
                $view_params['amazon_order_info']['ship_service_level']['bold'] = false;
                $view_params['amazon_order_info']['ship_service_level']['color'] = null;
            }

            if ($amazonOrder->amazon_order_info->earliest_ship_date) {
                $view_params['amazon_order_info']['earliest_ship_date']['label'] = $this->l('Earliest Ship Date', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['earliest_ship_date']['value'] = $amazonOrder->amazon_order_info->earliest_ship_date;
                $view_params['amazon_order_info']['earliest_ship_date']['bold'] = true;
                $view_params['amazon_order_info']['earliest_ship_date']['color'] = time() > strtotime($amazonOrder->amazon_order_info->earliest_ship_date) ? 'red' : 'green';
            }

            if ($amazonOrder->amazon_order_info->latest_ship_date) {
                $view_params['amazon_order_info']['latest_ship_date']['label'] = $this->l('Latest Ship Date', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['latest_ship_date']['value'] = $amazonOrder->amazon_order_info->latest_ship_date;
                $view_params['amazon_order_info']['latest_ship_date']['bold'] = true;
                $view_params['amazon_order_info']['latest_ship_date']['color'] = time() > strtotime($amazonOrder->amazon_order_info->latest_ship_date) ? 'red' : 'green';
            }

            if ($amazonOrder->amazon_order_info->earliest_delivery_date) {
                $view_params['amazon_order_info']['earliest_delivery_date']['label'] = $this->l('Earliest Delivery Date', basename(__FILE__, '.php'), $this->context->language->id);
                $view_params['amazon_order_info']['earliest_delivery_date']['value'] = $amazonOrder->amazon_order_info->earliest_delivery_date;
                $view_params['amazon_order_info']['earliest_delivery_date']['bold'] = true;
                $view_params['amazon_order_info']['earliest_delivery_date']['color'] = time() > strtotime($amazonOrder->amazon_order_info->earliest_delivery_date) ? 'red' : 'green';
            }

            if ((int)$amazonOrder->amazon_order_info->mp_status && in_array($amazonOrder->amazon_order_info->mp_status, array(AmazonOrder::TO_CANCEL, AmazonOrder::CANCELED, AmazonOrder::PROCESS_CANCEL))) {
                if ((int)$canceled_state && $amazonOrder->current_state == $canceled_state) {
                    $cancel_stage = true;
                }
            }
        }
        if (Amazon::$debug_mode) {
            CommonTools::p('order: ');
            CommonTools::p(get_object_vars($amazonOrder));
        }
        
        $token = (is_array($this->_tokens) ? max($this->_tokens) : null);

        if (!$token) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('id_lang: %s'."\n", $this->id_lang));
                CommonTools::p(sprintf('order id_lang: %s'."\n", $this->_order_id_lang));
                CommonTools::p(sprintf('tokens: %s'."\n", print_r($this->_tokens, true)));
                die(Tools::displayError(sprintf('%s/%d: Unavailable Amazon token', basename(__FILE__), __LINE__)));
            }

            return (false);
        }

        $view_params['id_order'] = $amazonOrder->id;
        $view_params['id_lang'] = $this->_order_id_lang;

        $view_params['amazon_token'] = $token;

        $view_params['marketplace_region'] = AmazonTools::idToDomain($this->_order_id_lang);

        $view_params['images_url'] = $this->images;
        $view_params['css_url'] = $this->url.'views/css/admin_order.css';

        $view_params['ps_version_is_16'] = version_compare(_PS_VERSION_, '1.6', '>=');
        $view_params['ps_version_is_15'] = version_compare(_PS_VERSION_, '1.5', '>=') && version_compare(_PS_VERSION_, '1.6', '<');

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                "Amazon, Debug Mode" . Amazon::LF,
                sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                nl2br(print_r($view_params, true))
            ));
        }

        if ($cancel_stage) {
            //
            // Standard Amazon Order
            //
            return ($this->marketplaceOrderDisplayToCancelOrder($view_params, $amazonOrder, $params));
        } elseif (Tools::strtolower($amazonOrder->module) != 'amazon' && $amazonOrder->marketPlaceChannel != AmazonMultiChannel::AMAZON_FBA_MULTICHANNEL && $this->_multichannel) {
            // Normal Order - Possible to convert it to a multi-channel order
            //
            return ($this->marketplaceOrderDisplayFbaEligibleToMultichannel($view_params, $amazonOrder, $params));
        } elseif ($amazonOrder->marketPlaceChannel == AmazonMultiChannel::AMAZON_FBA_MULTICHANNEL) {
            // Multi-Channel Order
            //
            return ($this->marketplaceOrderDisplayFbaMultichannel($view_params, $amazonOrder, $params));
        } else {
            //
            // Standard Amazon Order
            //
            return ($this->marketplaceOrderDisplayStandardOrder($view_params, $amazonOrder, $params));
        }
    }

    /**
     * Displays options for a FBA-multichannel eligible order
     * @param $view_params
     * @param $order
     * @param $params
     * @return bool|string
     * @throws Exception
     * @throws SmartyException
     */
    private function marketplaceOrderDisplayFbaEligibleToMultichannel(&$view_params, &$order, &$params)
    {
        if (!($mc_order = AmazonMultiChannel::isEligible($order->id))) {
            if (Amazon::$debug_mode) {
                echo Tools::displayError(sprintf('%s/%d: This order is not eligible to FBA Multichannel: %d', basename(__FILE__), __LINE__, $order->id));
            }

            return (false);
        } else {
            $order = $mc_order;
        }

        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');
        $europe = false;

        if (isset($marketPlaceIds[$order->id_lang]) && $marketPlaceIds[$order->id_lang]) {
            $europe = AmazonTools::isEuropeMarketplaceId($marketPlaceIds[$order->id_lang]);
        }

        $view_params['js_urls'] = array($this->url.'views/js/adminorderfba.js');
        $this->_marketplaceOrderDetail($view_params, $params);

        if ($europe) {
            $view_params['fbaorder_url'] = $this->url.'functions/fbaorder.php?europe=1';
            $view_params['marketplace_flag'] = $this->images.'geo_flags_web2/flag_eu_32px.png';
        } else {
            $marketPlaceRegion = AmazonConfiguration::get('REGION');

            if (isset($marketPlaceRegion[$this->id_lang])) {
                $lang = 'lang='.$marketPlaceRegion[$this->id_lang];
            } else {
                $lang = null;
            }

            $view_params['fbaorder_url'] = $this->url.'functions/fbaorder.php?'.$lang;
            $view_params['marketplace_flag'] = $this->images.'geo_flags_web2/flag_'.$view_params['marketplace_region'].'_32px.png';
        }

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                "Amazon, Debug Mode" . Amazon::LF,
                sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                nl2br(print_r($view_params, true))
            ));
        }

        $this->context->smarty->assign($view_params);
        $html = $this->context->smarty->fetch($this->path.'views/templates/admin/admin_order/AdminOrderMultichannelEligible.tpl');

        return ($html);
    }

    /**
     * Displays an automatic FBA multichannel order
     * @param $view_params
     * @param $order
     * @param $params
     * @return string
     * @throws Exception
     * @throws SmartyException
     */
    private function marketplaceOrderDisplayFbaMultichannel(&$view_params, &$order, &$params)
    {
        if (!Validate::isLoadedObject($order)) {
            return (false);
        }

        $multiChannelOrder = new AmazonMultiChannel($order->id);

        if (!Validate::isLoadedObject($multiChannelOrder)) {
            if (Amazon::$debug_mode) {
                Tools::displayError(sprintf('%s/%d: This order an invalid FBA Multichannel order: %d', basename(__FILE__), __LINE__, $order->id));
            }

            return (false);
        }

        switch (Tools::strtolower($multiChannelOrder->marketPlaceChannelStatus)) {
            case AmazonMultiChannel::AMAZON_FBA_STATUS_SUBMITED:
                $currentStatus = $this->l('Submited');
                break;
            case AmazonMultiChannel::AMAZON_FBA_STATUS_RECEIVED:
                $currentStatus = $this->l('Received');
                break;
            case AmazonMultiChannel::AMAZON_FBA_STATUS_INVALID:
                $currentStatus = $this->l('Invalid');
                break;
            case AmazonMultiChannel::AMAZON_FBA_STATUS_PLANNING:
                $currentStatus = $this->l('Planning');
                break;
            case AmazonMultiChannel::AMAZON_FBA_STATUS_PROCESSING:
                $currentStatus = $this->l('Processing');
                break;
            case AmazonMultiChannel::AMAZON_FBA_STATUS_CANCELLED:
                $currentStatus = $this->l('Canceled');
                break;
            case AmazonMultiChannel::AMAZON_FBA_STATUS_COMPLETE:
                $currentStatus = $this->l('Complete');
                break;
            case AmazonMultiChannel::AMAZON_FBA_STATUS_COMPLETEPARTIALLED:
                $currentStatus = $this->l('Partially Completed');
                break;
            case AmazonMultiChannel::AMAZON_FBA_STATUS_UNFULFILLABLE:
                $currentStatus = $this->l('Unfulfillable');
                break;
            default:
                $currentStatus = $this->l('Unknown');
        }

        switch (Tools::strtolower($multiChannelOrder->marketPlaceChannelStatus)) {
            case AmazonMultiChannel::AMAZON_FBA_STATUS_CANCELLED:
            case AmazonMultiChannel::AMAZON_FBA_STATUS_UNFULFILLABLE:
            case AmazonMultiChannel::AMAZON_FBA_STATUS_INVALID:
                $canceled = true;
                break;
            default:
                $canceled = false;
                break;
        }
        $marketPlaceIds = AmazonConfiguration::get('MARKETPLACE_ID');
        $europe = false;

        if (isset($marketPlaceIds[$order->id_lang]) && $marketPlaceIds[$order->id_lang]) {
            $europe = AmazonTools::isEuropeMarketplaceId($marketPlaceIds[$order->id_lang]);
        }

        $view_params['js_urls'] = array($this->url.'views/js/adminorderfba.js');
        $this->_marketplaceOrderDetail($view_params, $params);

        if ($europe) {
            $view_params['fbaorder_url'] = $this->url.'functions/fbaorder.php?europe=1';
            $view_params['marketplace_flag'] = $this->images.'geo_flags_web2/flag_eu_32px.png';
        } else {
            $marketPlaceRegion = AmazonConfiguration::get('REGION');

            if (isset($marketPlaceRegion[$this->id_lang])) {
                $lang = 'lang='.$marketPlaceRegion[$this->id_lang];
            } else {
                $lang = null;
            }

            $view_params['fbaorder_url'] = $this->url.'functions/fbaorder.php?'.$lang;
            $view_params['marketplace_flag'] = $this->images.'geo_flags_web2/flag_'.$view_params['marketplace_region'].'_32px.png';
        }

        $view_params['marketplace_status'] = $currentStatus;
        $view_params['marketplace_canceled'] = $canceled;

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                "Amazon, Debug Mode" . Amazon::LF,
                sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                nl2br(print_r($view_params, true))
            ));
        }

        $this->context->smarty->assign($view_params);
        $html = $this->context->smarty->fetch($this->path.'views/templates/admin/admin_order/AdminOrderMultichannel.tpl');

        return ($html);
    }

    /**
     * Displays a standard order
     * @param $view_params
     * @param AmazonOrder $order
     * @param $params
     * @return string
     * @throws Exception
     * @throws SmartyException
     */
    private function marketplaceOrderDisplayStandardOrder(&$view_params, &$order, &$params)
    {
        $view_params['js_urls'] = array($this->url.'views/js/adminorder.js');
        $this->_marketplaceOrderDetail($view_params, $params);

        $view_params['marketplace_url'] = AmazonTools::sellerCentralUrl($this->_order_id_lang, $order->marketPlaceOrderId);

        $view_params['marketplace_flag'] = $this->images.'geo_flags_web2/flag_'.$this->geoFlag($this->_order_id_lang).'_32px.png';
        $view_params['tracking_number'] = AmazonOrder::getShippingNumber($order);
        $view_params['endpoint'] = $this->url.'functions/tools.php?id_lang='.$this->id_lang;

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                "Amazon, Debug Mode".Amazon::LF,
                sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                nl2br(print_r($view_params, true))
            ));
        }

        $this->context->smarty->assign($view_params);
        $html = $this->context->smarty->fetch($this->path.'views/templates/admin/admin_order/AdminOrderStandard.tpl');

        //$html .= $this->marketplaceOrderDisplayEditOrder($order, $params) ;

        return ($html);
    }

    /**
     * Displays a cancelable order
     * @param $view_params
     * @param $order
     * @param $params
     * @return string
     * @throws Exception
     * @throws SmartyException
     */
    private function marketplaceOrderDisplayToCancelOrder(&$view_params, &$order, &$params)
    {
        $view_params['js_urls'] = array($this->url.'views/js/adminordercancel.js');
        $this->_marketplaceOrderDetail($view_params, $params);
        $view_params['cancel_url'] = $this->url.'functions/canceled.php?id_lang='.$this->id_lang;
        
        $view_params['marketplace_url'] = AmazonTools::sellerCentralUrl($this->_order_id_lang, $order->marketPlaceOrderId);
        $view_params['marketplace_flag'] = $this->images.'geo_flags_web2/flag_'.$view_params['marketplace_region'].'_32px.png';
        $view_params['scenario'] = null;
        

        $amazon_order_cancel = new AmazonOrderCancel();

        switch ((int)$order->amazon_order_info->mp_status) {
            case AmazonOrder::PROCESS_CANCEL:
                $view_params['scenario'] = 'cancel_cancel';
                $view_params['cancel_status'] = AmazonOrder::REVERT_CANCEL;
                break;
            case AmazonOrder::TO_CANCEL:
                $view_params['scenario'] = 'to_cancel';
                $view_params['cancel_status'] = AmazonOrder::PROCESS_CANCEL;
                break;
            case AmazonOrder::CANCELED:
                $view_params['scenario'] = 'canceled';
                $view_params['cancel_status'] = AmazonOrder::CANCELED;
                break;
        }
        $view_params['reasons'] = $amazon_order_cancel->getReasons();

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                "Amazon, Debug Mode".Amazon::LF,
                sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                nl2br(print_r($view_params, true))
            ));
        }

        $this->context->smarty->assign($view_params);
        $html = $this->context->smarty->fetch($this->path.'views/templates/admin/admin_order/AdminOrderCancel.tpl');

        return ($html);
    }

    /**
     * @param $id_order
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public static function getOrderDetailList($id_order)
    {
        if (method_exists('OrderDetail', 'getList')) {
            return(OrderDetail::getList($id_order));
        } else {
            return Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'order_detail` WHERE `id_order` = '.(int)$id_order);
        }
    }

    /**
     * Assign marketplace detail to template
     * @param $view_params
     * @param $params
     */
    private function _marketplaceOrderDetail(&$view_params, $params)
    {
        if (isset($params['id_order'])) {
            $id_order = $params['id_order'];
            $order_detail = self::getOrderDetailList($id_order);
            if (is_array($order_detail) && count($order_detail)) {
                foreach ($order_detail as $key => $detail) {
                    $marketplace_detail = AmazonOrderItem::getItemByOrderId($id_order, $detail['product_id'], $detail['product_attribute_id']);
                    if ($marketplace_detail) {
                        $order_detail[$key]['marketplace_detail'] = $marketplace_detail;
                    }
                }
            }
            $view_params['marketplace_detail'] = $order_detail;
            $view_params['template_path'] = $this->path.'views/templates/admin/admin_order/';
        }

        // Push additional js file
        $view_params['js_urls'][] = $this->url.'views/js/admin-order-customization.js';
    }

    /**
     * Live-Edit an Amazon order options - for future use
     * @param $order
     * @param $params
     * @return bool|string
     * @throws Exception
     * @throws SmartyException
     */
    private function marketplaceOrderDisplayEditOrder(&$order, &$params)
    {
        $html = null;

        $view_params = array();
        $view_params['js_url'] = $this->url.'views/js/admineditorder.js';
        $view_params['edit_order_url'] = $this->url.'functions/editorder.php?europe=1';
        $view_params['id_order'] = $order->id;

        $address_delivery = new Address($order->id_address_delivery);
        $address_invoice = new Address($order->id_address_invoice);

        if (!Validate::isLoadedObject($address_delivery) || !Validate::isLoadedObject($address_invoice)) {
            return (false);
        }

        $view_params['address_delivery'] = get_object_vars($address_delivery);
        $view_params['address_invoice'] = get_object_vars($address_invoice);

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                "Amazon, Debug Mode".Amazon::LF,
                sprintf('%s, line %d'."\n", basename(__FILE__), __LINE__),
                nl2br(print_r($view_params, true))
            ));
        }

        $this->context->smarty->assign($view_params);
        $html .= $this->_autoAddJS($this->url.'views/js/edit_order.js');

        $html .= $this->context->smarty->fetch($this->path.'views/templates/admin/admin_order/AdminEditOrder.tpl');

        return ($html);
    }

    /**
     * @param $id
     *
     * @return array|bool|null|object
     */
    public static function getByOrderId($id)
    {
        $sql = "SELECT * FROM " . _DB_PREFIX_ . pSQL(self::TABLE_MARKETPLACE_ORDERS) . " WHERE `id_order` = " . (int)$id;
        return Db::getInstance()->getRow($sql);
    }
}
