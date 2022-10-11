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

class Lnp2PickupOrderPageController extends ModuleAdminController
{
    protected $statuses_array = array();

    public function __construct()
    {
        $this->table     = 'order';
        $this->className = 'Order';

        $this->lang      = false;
        $this->bootstrap = true;
        $this->addRowAction('viewOrder');
        $this->bulk_actions['print'] = array(
            'text'    => $this->l('Print label(s)'),
            'confirm' => $this->l('You will print in one file all labels for selected orders'),
        );

        $this->explicitSelect = true;
        $this->allow_export   = true;
        $this->deleted        = false;
        $this->context        = Context::getContext();

        $carrier_ids     = array();
        $carrier_ids_tmp = explode('|', Configuration::get('LNP2_CARRIER_ID_HIST'));
        foreach ($carrier_ids_tmp as $carrier_id) {
            if ($carrier_id) {
                $carrier_ids[] = $carrier_id;
            }
        }

        $this->_select = 'a.`id_order`, a.reference, a.`total_paid` AS `total`, a.`id_currency`, osl.`name` AS `status`, a.`date_add` AS `date`, CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`, oc.`tracking_number`, lo.`delivery_name`, a.`total_shipping`, os.`color`';

        $this->_join = 'INNER JOIN `'._DB_PREFIX_.'order_state` os ON a.`current_state` = os.`id_order_state`
                INNER JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (a.`current_state` = osl.`id_order_state`
                    AND osl.`id_lang` = '.(int)$this->context->language->id.')
                INNER JOIN `'._DB_PREFIX_.'customer` c ON a.`id_customer` = c.`id_customer`
                INNER JOIN `'._DB_PREFIX_.'order_carrier` oc ON a.`id_order` = oc.`id_order`
                    AND oc.`id_carrier` IN (\''.implode('\',\'', $carrier_ids).'\')
                INNER JOIN `'._DB_PREFIX_.'lnp2_orders` lo ON lo.`id_order` = a.`id_order`';

        //$this->_where = 'AND a.`id_carrier` = '.(int)Configuration::get('LNP2_CARRIER_ID');

        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        // PS 1.5 does not translate string in module admin controllers.
        // We have to use a trick and translate them in pickup.php
        $is_16 = (version_compare(_PS_VERSION_, '1.6.0', '>=') === true);
        if (!$is_16) {
            include_once dirname(__FILE__).'/../../lanavettepickup.php';
            $pickup             = new LaNavettePickup();
            $title_translations = $pickup->getOrderPageControllerTitles();
        }

        $this->fields_list = array(

            'id_order'        => array(
                'title' => ($is_16 ? $this->l('ID') : $title_translations['id_order']),
            ),
            'reference'       => array(
                'title' => ($is_16 ? $this->l('Reference') : $title_translations['reference']),
            ),
            'customer'        => array(
                'title'        => ($is_16 ? $this->l('Client') : $title_translations['customer']),
                'havingFilter' => true,
            ),
            'total'           => array(
                'title'        => ($is_16 ? $this->l('Total') : $title_translations['total']),
                'callback'     => 'getTotal',
                'havingFilter' => true,
            ),
            'status'          => array(
                'title'       => ($is_16 ? $this->l('Status') : $title_translations['status']),
                'callback'    => 'getStatus',
                'type'        => 'select',
                'color'       => 'color',
                'list'        => $this->statuses_array,
                'filter_key'  => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key'   => 'osname',

            ),
            'date_add'        => array(
                'title'      => ($is_16 ? $this->l('Date') : $title_translations['date_add']),
                'align'      => 'text-right',
                'type'       => 'datetime',
                'filter_key' => 'a!date_add',
            ),
            'tracking_number' => array(
                'title' => ($is_16 ? $this->l('Tracking Number') : $title_translations['tracking_number']),
            ),
            'delivery_name'   => array(
                'title' => ($is_16 ? $this->l('Delivery Site Name') : $title_translations['delivery_name']),
            ),
            'total_shipping'  => array(
                'title'    => ($is_16 ? $this->l('Shipping cost') : $title_translations['total_shipping']),
                'callback' => 'getTotalShipping',
                'type'     => 'price',
                'currency' => true,
            ),

        );

        $this->_orderBy  = 'id_order';
        $this->_orderWay = 'DESC';

        $this->fieldImageSettings = array();

        parent::__construct();
    }

    public function getStatus($echo, $row)
    {
        return $row['status'];
    }

    public function getTotal($echo, $row)
    {
        return Tools::displayPrice($row['total']);
    }

    public function getTotalShipping($echo, $row)
    {
        return Tools::displayPrice($row['total_shipping']);
    }

    public function getDate($echo, $row)
    {
        $timestamp = strtotime($row['date_add']);

        return date('d/m/Y H:i:s', $timestamp);
    }

    public function renderList()
    {
        $this->addRowAction('label');

        $context = Context::getContext();
        if ($context->cookie->error_messages) {
            $this->errors                    = Tools::jsonDecode($context->cookie->error_messages);
            $context->cookie->error_messages = "";
            $context->cookie->write();
        }

        return parent::renderList();
    }

    public function renderForm()
    {
        if (Validate::isLoadedObject(($this->object))) {
            $id_order = (int)$this->object->id;
        } else {
            $id_order = (int)Tools::getValue('id_order');
        }

        $this->redirectToAdminOrder($id_order);
    }

    public function displayViewOrderLink($token, $id_order)
    {
        $this->context->smarty->assign(array(
            'id_order'    => $id_order,
            'modules_dir' => _MODULE_DIR_,
        ));

        $order = new Order($id_order);
        if (in_array($order->current_state, array(2))) {
            #Payment accepted : print or print and change state to
            return $this->createTemplate('../../../../modules/lanavettepickup/views/templates/admin/_print_label_icon.tpl')->fetch();
        } elseif (in_array($order->current_state, array(3, Configuration::get('LNP2_PREPARATION_ORDER_STATE')))) {
            #Processing in progress : change state to Shipped
            return $this->createTemplate('../../../../modules/lanavettepickup/views/templates/admin/_print_order_sent_icon.tpl')->fetch();
        }

        return $this->createTemplate('../../../../modules/lanavettepickup/views/templates/admin/_view_order_icon.tpl')->fetch();
    }

    public function initProcess()
    {
        parent::initProcess();

        $action = Tools::getValue('submitAction');
        if ('' != $action && false != $action) {
            $this->action = $action;
        }
    }

    /**
     * Controller's action according to parameters
     * @return bool
     */
    public function postProcess()
    {
        if (Tools::getValue('generate_tracking') == 'true') {
            $from_order_page = (Tools::getValue('order_page') == 1 ? true : false);
            $id_order        = (int)Tools::getValue('id_order');
            $this->processGenerateLnpOrder($id_order, $from_order_page);
        }
        if (Tools::getValue('print_pdf') == 'true' && Tools::getValue('id_order') != '' && Tools::getValue('order_page') == 1) {
            $this->processPrintLabel(Tools::getValue('id_order'), '', true);
        } elseif (Tools::getValue('print_pdf') == 'true' && Tools::getValue('id_order') != '') {
            $this->processPrintLabel(Tools::getValue('id_order'), Tools::getValue('change_status'));
        } elseif (Tools::getValue('change_status') == '1' && Tools::getValue('id_order') != '') {
            $this->changeOrderStatus(array((int)Tools::getValue('id_order')));
            $this->confirmations[] = "Status successfully changed";
        } elseif (Tools::getValue('preparation_state') == 1 && Tools::getValue('id_order') != '') {
            $this->updateOrderSent(Tools::getValue('id_order'));
        }

        return parent::postProcess();
    }

    #####################################################################################################
    # Log functions                                                                                     #
    #####################################################################################################

    private function myLog($message)
    {
        $logs_activated = (bool)Configuration::get('LNP2_LOGS');
        if ($logs_activated) {
            $filename = 'log-PickupLabelPage.txt';
            $handle   = fopen(dirname(__FILE__).'/logs/'.$filename, 'a+');
            fwrite($handle, '['.date('Y-m-d H:i:s').']{'.$_SERVER['REMOTE_ADDR'].'} '.$message."\n");
            fclose($handle);
        }
    }

    private function myLogOrder($id_order, $message)
    {
        $logs_activated = (bool)Configuration::get('LNP2_LOGS');
        if ($logs_activated) {
            $filename = 'log-id_order-'.$id_order.'.txt';
            $handle   = fopen(dirname(__FILE__).'/logs/'.$filename, 'a+');
            fwrite($handle, '['.date('Y-m-d H:i:s').']{'.$_SERVER['REMOTE_ADDR'].'} '.$message."\n");
            fclose($handle);
        }
    }

    #####################################################################################################
    # Labels functions                                                                                  #
    #####################################################################################################

    /*
    * $id_orders: list of orders for which to change the order status
    */
    protected function changeOrderStatus($id_orders)
    {
        $this->myLog('changeOrderStatus : start');

        foreach ($id_orders as $id_order) {
            $this->myLog('changeOrderStatus : $id_order = '.$id_order);
            $order = new Order($id_order);
            $this->myLog('changeOrderStatus : order instantiated > '.$id_order);
            $order->setCurrentState((int)Configuration::get('LNP2_PREPARATION_ORDER_STATE'));
            $this->myLog('changeOrderStatus : order change status (setCurrentState) > '.Configuration::get('LNP2_PREPARATION_ORDER_STATE'));

        }
        $this->myLog('changeOrderStatus : stop');
        /*
        // update status to "in preparation"
		$result = Db::getInstance()->query('UPDATE `'._DB_PREFIX_.'orders`
            SET `current_state` = '.(int)Configuration::get('LNP2_PREPARATION_ORDER_STATE').'
            WHERE id_order IN ('.implode(',', array_map('intval', $id_orders)).')');
        */
    }

    /**
     * Generate New LnpOrder
     * @param      $id_order
     * @param bool $from_order_page
     */
    protected function processGenerateLnpOrder($id_order, $from_order_page = false)
    {
        /** @var LaNavettePickup $lanavette */
        $lanavette = Module::getInstanceByName('lanavettepickup');
        if ($lanavette) {
            $order                 = new Order($id_order);
            $params                = array();
            $params['cart']        = new Cart($order->id_cart);
            $params['order']       = $order;
            $params['customer']    = new Customer($order->id_customer);
            $params['currency']    = new Currency($order->id_currency);
            $params['orderStatus'] = new OrderState($order->current_state);

            if (false == $lanavette->hookActionValidateOrder($params)) {
                $this->errors[] = $this->l('Error during generation');
            }
        } else {
            $this->errors[] = $this->l('Module lanavettepickup cannot be instantiate');
        }
        if (empty($this->errors)) {
            $this->confirmations[] = $this->l('New tracking number has been correctly generated.');
        }
        if ($from_order_page) {
            $this->redirectToAdminOrder($id_order);
        }
    }

    /**
     * Print Pickup Order
     * @param      $id_order
     * @param      $change_status
     * @param bool $from_order_page
     */
    protected function processPrintLabel($id_order, $change_status, $from_order_page = false)
    {
        $this->myLog('processPrintLabel : start');
        $this->myLog('$id_order = '.$id_order);
        $this->myLog('$change_status = '.$change_status);

        if ($change_status == 1) {
            $this->changeOrderStatus(array($id_order));
            $this->confirmations[] = $this->l('Status successfully changed');
        }

        $pickup = new LaNavettePickup();
        $this->myLog('$pickup instanciated');

        $pickup_label = $pickup->getLabel($id_order);
        if (false == $pickup_label || (isset($pickup_label->success) && false === $pickup_label->success)) {
            $message        = (isset($pickup_label->responseMessage)) ? ' Message : '.$pickup_label->responseMessage : '';
            $this->errors[] = $this->l('Error with the webservice, we cannot succeed to get the ticket. Please Try later.').$message;
            if ($from_order_page) {
                $this->redirectToAdminOrder($id_order, true);
            }

            return;
        }
        $this->myLog('processPrintLabel : stop > return PDF via http header response application/pdf');
        $pickup->generatePDF($pickup_label->pdfBase64);
    }

    protected function processBulkPrint()
    {
        $this->myLog('proccessBulkPrint : start');
        $this->myLog('$id_orders = '.Tools::jsonEncode($this->boxes));


        $path = _PS_MODULE_DIR_.'lanavettepickup/pdf/';
        include_once(_PS_MODULE_DIR_.'lanavettepickup/lib/PDFMerger/PDFMerger.php');

        $pdf             = @new PDFMerger();
        $list_temp_pdf   = array();
        $nb_pdf_to_merge = 0;
        $success_message = $this->l('The following id orders labels have been printed : ');

        $pickup = new LaNavettePickup();
        $this->myLog('$pickup instanciated');
        if (false === $this->boxes) {
            $this->warnings[] = $this->l('No order has been selected. Please select at least one order before applying "Print label(s)" action');

            return;
        }
        foreach ($this->boxes as $id_order) {
            $pickup_label = $pickup->getLabel($id_order);
            if (false == $pickup_label || (isset($pickup_label->success) && false === $pickup_label->success)) {
                $message        = (isset($pickup_label->responseMessage)) ? ' Message : '.$pickup_label->responseMessage : '';
                $this->errors[] = $this->l('Order ').$id_order.' : '.$this->l('Error with the webservice, we cannot succeed to get the ticket. Please Try later.').$message;
                continue;
            }
            $current_path_pdf = $path.'temp_'.time().'_'.$id_order.'.pdf';

            $data   = base64_decode($pickup_label->pdfBase64);
            $result = file_put_contents($current_path_pdf, $data);
            if (false === $result) {
                $this->errors[] = $this->l('Order ').$id_order.' : '.$this->l('Error while creating pdf file.');
                continue;
            }
            $list_temp_pdf[] = $current_path_pdf;
            try {
                $pdf->addPDF($current_path_pdf, 'all');
                $success_message .= ' '.$id_order.',';
                $nb_pdf_to_merge++;
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        if ($nb_pdf_to_merge > 0) {
            try {
                $name = 'orders';
                $pdf->merge('file', $path.$name.'.pdf');
                $this->confirmations[] .= Tools::substr($success_message, 0, Tools::strlen($success_message) - 1).'<script type="application/javascript">$(document).ready(function(){window.location = "'.$this->context->link->getAdminLink('Lnp2PickupOrderPage').'&submitAction=generatePDF"});</script>';
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }
        foreach ($list_temp_pdf as $temp_pdf) {
            if (Tools::file_exists_no_cache($temp_pdf)) {
                unlink($temp_pdf);
            } else {
                $this->errors[] = $this->l('We cannot find this file : ').$temp_pdf;
            }
        }
    }

    public function processGeneratePDF()
    {
        $path = _PS_MODULE_DIR_.'lanavettepickup/pdf/';
        $name = 'orders.pdf';

        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$name.'"');
        readfile($path.$name);
    }

    public function redirectToAdminOrder($id_order, $error = false)
    {
        $context = Context::getContext();

        $context->cookie->error_messages        = Tools::jsonEncode($this->errors);
        $context->cookie->confirmation_messages = Tools::jsonEncode($this->confirmations);
        $context->cookie->write();
        $this->errors        = array();
        $this->confirmations = array();

        if ($error) {
            $error = '&error_lnp=1';
        }
        Tools::redirectAdmin('?tab=AdminOrders&token='.Tools::getAdminTokenLite('AdminOrders').'&vieworder&id_order='.$id_order.$error);
    }

    #####################################################################################################
    # Preparation functions                                                                             #
    #####################################################################################################

    public function updateOrderSent($id_orders)
    {
        if (!is_array($id_orders)) {
            $id_orders = array($id_orders);
        }

        $id_orders = array_map('intval', $id_orders);

        // update status to "sent"
        $new_order_state = (int)Configuration::get('LNP2_SHIPPED_ORDER_STATE');

        $is_PS16 = (version_compare(_PS_VERSION_, '1.6.1', '>=') === true);

        // update order state and tracking code
        $result = Db::getInstance()->executeS('SELECT `id_order`, `navette_code` FROM `'._DB_PREFIX_.'lnp2_orders` WHERE id_order IN ('.pSQL(implode(',', $id_orders)).')');
        foreach ($result as $row) {
            $order = new Order($row['id_order']);
            if ($order->getCurrentState() != (int)$new_order_state) {
                $order->setCurrentState((int)$new_order_state);
            }

            if ($is_PS16) {
                if ($row['navette_code'] != $order->getWsShippingNumber()) {
                    $order->setWsShippingNumber($row['navette_code']);
                }
            } else {
                if ($row['navette_code'] != $this->getWsShippingNumber($row['id_order'])) {
                    $this->setWsShippingNumber($row['id_order'], $row['navette_code']);
                }
            }
        }
        $this->confirmations[] = "Status successfully changed";
    }

    /* For PS 1.5 methods */

    public function getWsShippingNumber($id_order)
    {
        $id_order_carrier = Db::getInstance()->getValue('
			SELECT `id_order_carrier`
			FROM `'._DB_PREFIX_.'order_carrier`
			WHERE `id_order` = '.(int)$id_order);
        if ($id_order_carrier) {
            $order_carrier = new OrderCarrier($id_order_carrier);

            return $order_carrier->tracking_number;
        }
        $order = new Order($id_order);

        return $order->shipping_number;
    }

    public function setWsShippingNumber($id_order, $shipping_number)
    {
        $id_order_carrier = Db::getInstance()->getValue('
			SELECT `id_order_carrier`
			FROM `'._DB_PREFIX_.'order_carrier`
			WHERE `id_order` = '.(int)$id_order);
        if ($id_order_carrier) {
            $order_carrier                  = new OrderCarrier($id_order_carrier);
            $order_carrier->tracking_number = $shipping_number;
            $order_carrier->update();
        } else {
            $order                  = new Order($id_order);
            $order->shipping_number = $shipping_number;
            $order->update();
        }

        return true;
    }

    #####################################################################################################
    # Lnp2PickupOrderPageController functions                                                           #
    #####################################################################################################

    /**
     * @param $menu_id
     * @param $module_name
     * @return bool
     */
    public static function install($menu_id, $module_name)
    {
        $tab             = new Tab();
        $tab->active     = 1;
        $tab->name       = array();
        $tab->class_name = 'Lnp2PickupOrderPage';
        $tab->module     = $module_name;
        $tab->id_parent  = (int)Tab::getIdFromClassName('AdminParentOrders');

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'La Navette Pickup';
        }

        $tab->add();

        return true;
    }
}
