<?php
/**
* Paybox by Verifone PrestaShop Module
*
* Feel free to contact Paybox by Verifone at support@paybox.com for any
* question.
*
* LICENSE: This source file is subject to the version 3.0 of the Open
* Software License (OSL-3.0) that is available through the world-wide-web
* at the following URI: http://opensource.org/licenses/OSL-3.0. If
* you did not receive a copy of the OSL-3.0 license and are unable 
* to obtain it through the web, please send a note to
* support@paybox.com so we can mail you a copy immediately.
*
*  @category  Module / payments_gateways
*  @version   3.0.0
*  @author    BM Services <contact@bm-services.com>
*  @copyright 2012-2016 Paybox
*  @license   http://opensource.org/licenses/OSL-3.0
*  @link      http://www.paybox.com/
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__).'/classes/common.inc.php');

class Epayment extends PaymentModule
{
    private $_requestId;
    private $_config;
    private $_helper;
    private $_html = '';
    private $_message = '';
    private $_documentation;

    public function __construct()
    {
        $this->_requestId = time();
        $this->_config = new PayboxConfig();
        $this->_helper = new PayboxHelper($this);

        $this->name = 'epayment';
        $this->tab = 'payments_gateways';
        $this->version = '3.0.0';
        $this->author = 'Paybox by Verifone';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Paybox by Verifone';
        $this->description = $this->l('In one integration, offer many payment methods, get a customized secure payment page, multi-lingual and multi-currency and offer debit on delivery or in 3 installments without charges for your customers.');

        $this->_documentation = 'manuel-module-prestashop-v2.pdf';
    }

    public function getConfig()
    {
        return $this->_config;
    }

    public function getContent()
    {
        $html = '';

        require_once(dirname(__FILE__) . '/classes/PayboxAdminConfig.php');

        $admin = new PayboxAdminConfig($this);
        if (Tools::isSubmit('admin_action')) {
            $html .= $admin->processAction();
        }

        $html .= $admin->getContent();

        return $html;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getHelper()
    {
        return $this->_helper;
    }

    public function getDocPath()
    {
        return $this->getPath().'doc/';
    }

    public function getCurrentDocPath()
    {
        return $this->getDocPath().$this->_documentation;
    }

    public function getImagePath()
    {
        return $this->getPath().'img/';
    }

    public function getMethodImageUrl($type)
    {
        $ext = 'png';
        $file = dirname(__FILE__).'/img/'.$type.'.';

        foreach (array('png', 'gif', 'jpg') as $item) {
            if (is_file($file . $item)) {
                $ext = $item;
            }
        }

        return $this->getImagePath() . $type . '.' . $ext;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function hookAdminOrder($params)
    {
        require_once dirname(__FILE__) . '/classes/PayboxAdminOrder.php';

        $admin = new PayboxAdminOrder($this);

        $w = new PayboxHtmlWriter();

        // Process actions if needed
        if (Tools::isSubmit('order_action')) {
            $admin->processAction($w);
        }

        // Generate view
        $admin->getContent($w, $params);

        return (string) $w;
    }

    public function hookCancelProduct($params)
    {
        return true;
/*
        $helper = $this->getHelper();

        // Direct method must be enabled
        if (!$helper->isDirectEnabled()) {
            return false;
        }

        // Ignore any click to the generate discount button
        if (Tools::isSubmit('generateDiscount')) {
            return false;
        }

        // Payment module must by this one
        if ($params['order']->module != $this->name) {
            return false;
        }

        // We need the order, a valid one
        if (empty($params['order']) || !Validate::isLoadedObject($params['order'])) {
            return false;
        }

        $order = $params['order'];
        $orderId = $order->id;

        // We need a valid order detail
        $orderDetail = new OrderDetail((int) ($params['id_order_detail']));
        if (!Validate::isLoadedObject($orderDetail)) {
            return false;
        }
        $orderDetailId = (int) ($orderDetail->id_order_detail);

        // Currency informations
        $currency = new Currency(intval($order->id_currency));
        $amountScale = $helper->getCurrencyScale($order);

        $products = $order->getProducts();
        $product = $products[$orderDetailId];
        $cancelQuantity = (int) ($_POST['cancelQuantity'][$orderDetailId]);

        if ($product['product_quantity_discount'] == 0) {
            $amount = $product['product_price_wt'] * $cancelQuantity;
        }
        else {
            $amount = $product['product_quantity_discount'] * $cancelQuantity;
        }

        // Order can be captured?
        if ($helper->canCapture($orderId)) {
            // Update database
            $sql = 'UPDATE `%spaybox_order` SET `amount` = `amount` - %d'
                    . ' WHERE `id_order` = %d';
            $sql = sprintf($sql, _DB_PREFIX_, round($amount * $amountScale), $order->id);
            if (!Db::getInstance()->Execute($sql)) {
                // TODO ???
            }
        }

        // Order can be refunded?
        else if ($order->hasBeenPaid() && $helper->canRefund($orderId)) {
            // Make refund
            $details = $helper->getOrderDetails($orderId);
            $result = $helper->makeRefundAmount($order, $details, $amount);

            switch ($result) {
                case 1:
                    $this->context->controller->errors[] = Tools::displayError('Refund request unsuccessful. Please see log message!');
                    return false;

                case 2:
                    $this->context->controller->errors[] = Tools::displayError('Error when making refund request');
                    return false;

                case 3:
                    $this->context->controller->errors[] = Tools::displayError('The refund amount is too high.');
                    return false;
            }
        }
*/
    }

    /**
     * On payment selection page generation
     */
    public function hookPayment($params)
    {
        global $smarty, $cart, $cookie;

        // Load methods
        $methods = $this->getHelper()->getActivePaymentMethods();
        $debitTypeForCard = $this->getConfig()->getDebitTypeForCard();
        $recurringCards = array();
        $cards = array();
        foreach ($methods as $method) {
            // Remove non compatible cards
            if (0 === (int)$method['debit_'.$debitTypeForCard]) {
                continue;
            }

            $params = array(
                'a' => 'r',
                'method' => $method['id_card'],
            );

            $params = http_build_query($params);

            $card = array(
                'id' => $method['id_card'],
                'payment' => $method['type_payment'],
                'card' => $method['type_card'],
                'label' => $method['label'],
                'url' => $this->getPath().'?'.$params,
                'image' => $this->getMethodImageUrl($method['type_card']),
            );
            $cards[] = $card;

            if ($this->getConfig()->isRecurringCard($method)) {
                $recurringCards[] = $card;
            }

            $cardTypes[] = $method['type_card'];
        }

        $smarty->assign('payboxImagePath', $this->getImagePath());
        $smarty->assign('payboxCards', $cards);
        $smarty->assign('payboxProduction', $this->getConfig()->isProduction());

        // Define recurring information
        if ($this->getConfig()->isRecurringEnabled()) {
            if ($cart->getOrderTotal() >= $this->getConfig()->getRecurringMinimalAmount()) {
                $smarty->assign('payboxRecurring', $recurringCards);
            }
        }

        $reason = Tools::getValue('payboxReason');
        if (($reason != 'cancel') && ($reason != 'error')) {
            $reason = null;
        }
        $smarty->assign('payboxReason', $reason);

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                Tools::addCss($this->_path . 'views/css/payment.css', 'all');
            } else {
                $this->context->controller->addCSS($this->_path . 'views/css/payment.css', 'all');
            }
            $html = $this->fetchTemplate('payment.tpl');
        } else {
            $this->context->controller->addCSS($this->_path . 'views/css/payment-rwd.css', 'all');
            $html = $this->display(__FILE__, 'payment-rwd.tpl');
        }

        return $html;
    }

    public function hookDisplayPaymentEU($params)
    {
        if (!$this->active) {
            return;
        }

        $payment_options = array();

        // Load methods
        $methods = $this->getHelper()->getActivePaymentMethods();
        $debitTypeForCard = $this->getConfig()->getDebitTypeForCard();
        $recurringCards = array();
        $cards = array();
        foreach ($methods as $method) {
            // Remove non compatible cards
            if (0 === (int)$method['debit_'.$debitTypeForCard]) {
                continue;
            }

            $params = array(
                'a' => 'r',
                'method' => $method['id_card'],
            );

            $params = http_build_query($params);

            $card = array(
                'id' => $method['id_card'],
                'payment' => $method['type_payment'],
                'card' => $method['type_card'],
                'label' => $method['label'],
                'url' => $this->getPath().'?'.$params,
                'image' => $this->getMethodImageUrl($method['type_card']),
            );
            $cards[] = $card;

            if ($this->getConfig()->isRecurringCard($method)) {
                $recurringCards[] = $card;
            }

            $cardTypes[] = $method['type_card'];
        }

        // Create payment option for each allowed card
        foreach ($cards as $card) {
            $payment_option = array(
                'cta_text' => $card['label'],
                'logo' => $card['image'],
                'action' => $card['url'],
            );
            $payment_options[] = $payment_option;
        }

        // Define recurring information
        if ($this->getConfig()->isRecurringEnabled()) {
            if ($this->context->cart->getOrderTotal() >= $this->getConfig()->getRecurringMinimalAmount()) {
                foreach ($recurringCards as $card) {
                    $payment_option = array(
                        'cta_text' => $card['label'].' '.$this->l('card 3 times without fees'),
                        'logo' => $card['image'],
                        'action' => $card['url'].'&recurring=1',
                    );
                    $payment_options[] = $payment_option;
                }
            }
        }

        return $payment_options;
    }

    public function fetchTemplate($name)
    {
        if (_PS_VERSION_ < '1.4') {
            $this->context->smarty->currentTemplate = $name;
        } else if (_PS_VERSION_ < '1.5') {
            $views = 'views/templates/';
            if (@filemtime(dirname(__FILE__) . '/' . $name)) {
                return $this->display(__FILE__, $name);
            } elseif (@filemtime(dirname(__FILE__) . '/' . $views . 'hook/' . $name)) {
                return $this->display(__FILE__, $views . 'hook/' . $name);
            } elseif (@filemtime(dirname(__FILE__) . '/' . $views . 'front/' . $name)) {
                return $this->display(__FILE__, $views . 'front/' . $name);
            } elseif (@filemtime(dirname(__FILE__) . '/' . $views . 'back/' . $name)) {
                return $this->display(__FILE__, $views . 'back/' . $name);
            }
        }

        return $this->display(__FILE__, $name);
    }

    public function hookPaymentReturn($params)
    {
        global $smarty;

        // Payment method must be enabled
        if (!$this->active) {
            return;
        }

        // Must have an order
        if (empty($params['objOrder'])) {
            return;
        }

        // Must be order paid with Payment platform
        $details = $this->getHelper()->getOrderDetails($params['objOrder']->id);
        if (empty($details)) {
            return;
        }

        $lang = $this->context->language;
        if (!empty($lang) && !empty($lang->iso_code)) {
            $template = $this->getTemplatePath('payment_return.' . $lang->iso_code . '.tpl');
            if (!is_null($template)) {
                return $this->fetchTemplate('payment_return.' . $lang->iso_code . '.tpl');
            }
        }

        return $this->fetchTemplate('payment_return.tpl');
    }

    /**
     * On order state change, do capture if needed
     */
    public function hookUpdateOrderStatus($params)
    {
        $orderId = $params['id_order'];

        // Payment must be "standard" method
        $details = $this->getHelper()->getOrderDetails($orderId);
        if (empty($details) || ($details['payment_by'] != 'Paybox')) {
            return;
        }

        // Auto capture state must be defined
        $state = $this->getConfig()->getAutoCaptureState();
        if ($state <= -1) {
            return;
        }

        // New state must be the auto capture state
        $orderState = $params['newOrderStatus'];
        if ($state != $orderState->id) {
            return;
        }

        // Capture must be possible
        if (!$this->getHelper()->canCapture($orderId)) {
            return;
        }

        // Load order
        $order = new Order($orderId);
        if (!Validate::isLoadedObject($order)) {
            $w->alertError($this->l('Error when making capture request'));
            return;
        }

        $this->getHelper()->makeCaptureAll($order, $details, false);
    }

    public function isBackOfficeOrderController()
    {
        $controller = Tools::getValue('controller', '');
        if (('AdminOrders' == $controller) || defined('SC_VERSION')) {
            if (0 != Tools::getValue('id_order', 0)) {
                return Tools::getValue('id_order');
            }
        }

        return false;
    }

    /**
     * Process Order update to modify payment
     * @param array $params 
     * @return null
     */
    public function hookActionObjectOrderUpdateAfter($params)
    {
        $orderId = $this->isBackOfficeOrderController();
        if (false !== $orderId) {
            $helper = $this->getHelper();

            // Payment must be "standard" method
            $details = $this->getHelper()->getOrderDetails($orderId);
            if (empty($details) || ($details['payment_by'] != 'Paybox')) {
                return;
            }

            // Load order
            $order = new Order($orderId);
            if (!Validate::isLoadedObject($order)) {
                return;
            }

            $result = $helper->processPaymentModified($order, $details);

            if ($result['status'] == 0) {
                if (is_array($result['error'])) {
                    foreach ($result['error'] as $error) {
                        $this->context->controller->errors[] = Tools::displayError($error);
                    }
                } else {
                    $this->context->controller->errors[] = Tools::displayError($result['error']);
                }
                return false;
            }
        }

        return;
    }

    /**
     * Add JavaScript on Admin Order Controller
     * @param array $params 
     * @return null
     */
    public function hookActionAdminControllerSetMedia($params)
    {
        $orderId = $this->isBackOfficeOrderController();
        if (false !== $orderId) {
            $helper = $this->getHelper();

            // Payment must be "standard" method
            $details = $this->getHelper()->getOrderDetails($orderId);
            if (empty($details) || ($details['payment_by'] != 'Paybox')) {
                return;
            }

            // Load order
            $order = new Order($orderId);
            if (!Validate::isLoadedObject($order)) {
                return;
            }

            $this->context->controller->addJs($this->_path.('views/js/admin/orders.js'));
        }

        return;
    }

    /**
     * Handle Order Slip creation to make payment platform refund (if asked)
     * @param array $params 
     * @return null
     */
    public function hookActionOrderSlipAdd($params)
    {
        if (Tools::isSubmit('payboxRefund')) {
            $orderSlipToProcess = null;

            $order = $params['order'];
            $productList = $params['productList'];
            $qtyList = $params['qtyList'];

            // Payment must be "standard" method
            $helper = $this->getHelper();
            $details = $this->getHelper()->getOrderDetails($order->id);
            if (empty($details) || ($details['payment_by'] != 'PayboxSystem')) {
                return;
            }

            // Get Order Details in $params
            $orderDetails = array();
            foreach ($productList as $product) {
                $orderDetails[$product] = $qtyList[$product];
            }

            // Get Order Slips to compare
            $ordersSlip = OrderSlip::getOrdersSlip($order->id_customer, $order->id);
            foreach ($ordersSlip as $orderSlip) {
                // Get Order Slip Details
                $orderSlipDetails = OrderSlip::getOrdersSlipDetail($orderSlip['id_order_slip']);
                $orderSlipProducts = array();
                foreach ($orderSlipDetails as $orderSlipDetail) {
                    $orderSlipProducts[$orderSlipDetail['id_order_detail']] = $orderSlipDetail['product_quantity'];
                }

                // Check if refund of shipping cost or not
                if (!Tools::isSubmit('shippingBack') || (Tools::isSubmit('shippingBack') && $orderSlip['shipping_cost'] == 1)) {
                    // Check if it is the correct OrderSlip
                    $arrayDiff = array_diff_assoc($orderDetails, $orderSlipProducts);
                    if (count($arrayDiff) == 0) {
                        $orderSlipToProcess = $orderSlip;
                    }
                }
            }

            // Order Slip found => refund of the amount tax incl. of the Order Slip
            if (null !== $orderSlipToProcess) {
                // total_products_tax_incl & total_shipping_tax_incl appeared in PS 1.6.0.11, before amount contains products + shipping
                if (isset($orderSlipToProcess['total_products_tax_incl'])) {
                    $amount = $orderSlipToProcess['total_products_tax_incl'];
                } else {
                    $amount = $orderSlipToProcess['amount'];
                }
                if ($orderSlipToProcess['shipping_cost'] == 1) {
                    if (isset($orderSlipToProcess['total_shipping_tax_incl'])) {
                        $amount += $orderSlipToProcess['total_shipping_tax_incl'];
                    }
                }
                $result = $helper->processPaymentModified($order, $details, $amount);

                if ($result['status'] == 0) {
                    if (is_array($result['error'])) {
                        foreach ($result['error'] as $error) {
                            $this->context->controller->errors[] = Tools::displayError($error);
                        }
                    } else {
                        $this->context->controller->errors[] = Tools::displayError($result['error']);
                    }
                    return;
                }
            }
        }

        return;
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $installer = new PayboxInstaller();

        if (!$installer->install($this)) {
            return false;
        }

        return true;
    }

    /**
     * On IPN call for a standard payment
     */
    public function onStandardIPNSuccess(Cart $cart, array $params)
    {
        $this->logDebug(sprintf('Cart %d: Standard IPN', $cart->id));

        $amount = $params['amount'];
        $amountScale = pow(10, $this->getHelper()->getCurrencyDecimals($cart));
        $amount = floatval($amount) / $amountScale;

        if ($params['paymentType'] == 'KWIXO') {
            $state = $this->_config->getKwixoSuccessState();
        } else {
            $state = $this->_config->getSuccessState();
        }

        if ($this->_config->getDebitType() == 'receive') {
            $type = 'authorization';
            $message = $this->l('Payment was authorized by PaymentPlatform.');
        } else {
            $type = 'capture';
            $message = $this->l('Payment was authorized and captured by PaymentPlatform.');
        }

        $this->logDebug(sprintf('Cart %d: Validating order', $cart->id));
        $result = parent::validateOrder($cart->id, $state, $amount, $this->displayName, $message, array('transaction_id' => $params['transaction']), null, false, $cart->secure_key);

        if (!$result) {
            $this->logFatal(sprintf('Cart %d: Unable to validate order', $cart->id));
            throw new Exception('Unable to validate order');
        }

        $order = new Order($this->currentOrder);
        $this->getHelper()->addOrderPayment($order, $type, $params, 'Paybox');
        $this->logDebug(sprintf('Cart %d: Order %d / %s', $cart->id, $order->id, $message));
    }

    /**
     * On IPN call for a recurring payment
     */
    public function onThreetimeIPNSuccess(Cart $cart, array $params)
    {
        $this->logDebug(sprintf('Cart %d: Threetime IPN', $cart->id));

        $orderId = Order::getOrderByCartId($cart->id);
        $orderAmount = $cart->getOrderTotal();

        $platformAmount = $params['amount'];
        $amountDecimals = $this->getHelper()->getCurrencyDecimals($cart);
        $amountFormat = '%.' . $amountDecimals . 'f';
        $amountScale = pow(10, $amountDecimals);
        $amount = floatval($platformAmount) / $amountScale;
        $currency = new Currency(intval($cart->id_currency));

        $amounts = $this->getHelper()->computeThreetimePayments($cart->getOrderTotal(), $amountScale);

        // First payment
        if (empty($orderId)) {
            $this->logDebug(sprintf('Cart %d: First payment', $cart->id));
            $state = Configuration::get('PAYBOX_MIDDLE_STATE_NX');

            $message = $this->l('Payment was authorized by PaymentPlatform.') . "\r\n";
            $message .= sprintf($this->l('First payment capture of %s %s done.'), sprintf($amountFormat, $amount), $currency->sign) . "\r\n";
            $message .= $this->l('Next payments will be:') . "\r\n";
            $nextAmount = sprintf($amountFormat, floatval($amounts['PBX_2MONT1']) / $amountScale);
            $message .= $amounts['PBX_DATE1'] . ' ' . $nextAmount . ' ' . $currency->sign . "\r\n";
            $nextAmount = sprintf($amountFormat, floatval($amounts['PBX_2MONT2']) / $amountScale);
            $message .= $amounts['PBX_DATE2'] . ' ' . $nextAmount . ' ' . $currency->sign . "\r\n";

            $this->logDebug(sprintf('Cart %d: Validating order', $cart->id));
            $result = parent::validateOrder($cart->id, $state, $orderAmount, $this->displayName, $message, $params, null, false, $cart->secure_key);

            if (!$result) {
                $this->logFatal(sprintf('Cart %d: Unable to validate order', $cart->id));
                throw new Exception('Unable to validate order');
            }

            $order = new Order($this->currentOrder);

            // Save payment information
            $this->getHelper()->addOrderPayment($order, 'capture', $params, 'PayboxRecurring');
            $this->getHelper()->addOrderRecurringDetails($order, $platformAmount);
//            $this->getHelper()->addOrderNote($order, $message);
            $this->logDebug(sprintf('Cart %d: Order %d / %s', $cart->id, $order->id, $message));

            // Send a mail to the customer
            $title = $this->l('Recurring payment is approved');
            $customer = new Customer(intval($cart->id_customer));
            $customerName = $customer->firstname . ' ' . $customer->lastname;
            $varsTpl = array(
                '{lastname}' => $customer->lastname,
                '{firstname}' => $customer->firstname,
                '{id_order}' => $orderId,
                '{message}' => $message
            );

            Mail::Send(intval($order->id_lang), 'payment_recurring', $title, $varsTpl, $customer->email, $customerName, null, null, null, null, dirname(__FILE__) . '/mails/');
        }

        // Other payments
        else {
            $order = new Order($orderId);
            $details = $this->getHelper()->getOrderRecurringDetails($orderId);

            if (empty($details)) {
                // There must be details, this is an unrecovable error
                $message = $this->l('Invalid IPN call for recurring payment');
                $this->getHelper()->addOrderNote($order, $message);
                $this->logFatal(sprintf('Cart %d: Invalid IPN call for recurring payment', $cart->id));
                throw new Exception('Invalid IPN call for recurring payment');
            }

            switch ($details['number_term']) {
                // Second payment
                case 2:
                    $this->logDebug(sprintf('Cart %d: Second payment', $cart->id));
                    // Update recurring payment details
                    $this->getHelper()->updateOrderRecurringDetails($order, $details['amount_paid'] + $platformAmount, 1);

                    // Info
                    $message = sprintf($this->l('Second payment capture of %s %s done.'), sprintf($amountFormat, $amount), $currency->sign) . "\r\n";
                    $message .= $this->l('Next payment will be:') . "\r\n";
                    $nextAmount = sprintf($amountFormat, floatval($amounts['PBX_2MONT2']) / $amountScale);
                    $message .= $amounts['PBX_DATE2'] . ' ' . $nextAmount . ' ' . $currency->sign . "\r\n";
                    $this->getHelper()->addOrderNote($order, $message);
                    $this->logDebug(sprintf('Cart %d: %s', $cart->id, $message));

                    // Send a mail to the customer
                    $title = $this->l('Recurring payment is approved');
                    $customer = new Customer(intval($cart->id_customer));
                    $customerName = $customer->firstname . ' ' . $customer->lastname;
                    $varsTpl = array(
                        '{lastname}' => $customer->lastname,
                        '{firstname}' => $customer->firstname,
                        '{id_order}' => $orderId,
                        '{message}' => $message
                    );
                    Mail::Send(intval($order->id_lang), 'payment_recurring', $title, $varsTpl, $customer->email, $customerName, null, null, null, null, dirname(__FILE__) . '/mails/');
                    break;

                // Third payment
                case 1:
                    $this->logDebug(sprintf('Cart %d: Third payment', $cart->id));
                    // Update recurring payment details
                    $this->getHelper()->updateOrderRecurringDetails($order, $details['amount_paid'] + $platformAmount, 0);

                    // Info
                    $message .= sprintf($this->l('Third payment capture of %s %s done.'), sprintf($amountFormat, $amount), $currency->sign) . "\r\n";
                    $message .= $this->l('No more capture is pending.') . "\r\n";
                    $this->getHelper()->addOrderNote($order, $message);
                    $this->logDebug(sprintf('Cart %d: %s', $cart->id, $message));

                    // Change status
                    $changeHistory = new OrderHistory();
                    $changeHistory->id_order = $order->id;
                    $changeHistory->changeIdOrderState(Configuration::get('PAYBOX_LAST_STATE_NX'), $changeHistory->id_order);
                    $changeHistory->addWithemail();
                    break;

                default:
                    // There must be details, this is an unrecovable error
                    $message = $this->l('Invalid IPN call for recurring payment');
                    $this->getHelper()->addOrderNote($order, $message);
                    $this->logFatal(sprintf('Cart %d: Invalid IPN call for recurring payment', $cart->id));
                    throw new Exception('Invalid IPN call for recurring payment');
            }
        }
    }

    /**
     * On module uninstall
     */
    public function uninstall()
    {
        $installer = new PayboxInstaller();
        if (!$installer->uninstall($this)) {
            return false;
        }

        return parent::uninstall();
    }

    public function logDebug($message)
    {
        $this->_log($message, 'DEBUG');
    }

    public function logWarning($message)
    {
        $this->_log($message, 'WARN');
    }

    public function logError($message)
    {
        $this->_log($message, 'ERROR');
    }

    public function logFatal($message)
    {
        $this->_log($message, 'FATAL');
    }

    private function _log($message, $level)
    {
        $date = date('Y-m-d H:i:s');
        $message = sprintf('%s %010d %5s: %s' . PHP_EOL, $date, $this->_requestId, $level, $message);
        $file = sprintf('%s/logs/log_%s.log', dirname(__FILE__), date('Y-m-d'));
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        file_put_contents($file, $message, FILE_APPEND);
    }
}
