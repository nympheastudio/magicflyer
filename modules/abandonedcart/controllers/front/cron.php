<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * @author    knowband.com <support@knowband.com>
 * @copyright 2017 Knowband
 * @license   see file: LICENSE.txt
 * @category  PrestaShop Module
 *
 *
 * Description
 *
 * Updates quantity in the cart
 */

class AbandonedCartCronModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $abd_obj = new Abandonedcart();
        if (!Tools::isSubmit('ajax')) {
            if (Tools::getValue('secure_key')) {
                $secure_key = Configuration::get('VELSOF_ABD_SECURE_KEY');
                if ($secure_key == Tools::getValue('secure_key')) {
                    if (Tools::getValue('cron') == 'send_mails') {
                        $abd_obj->sendAutomaticIncentiveMails(true);
                    }
                    if (Tools::getValue('cron') == 'update_carts') {
                        $abd_obj->updateAbandonList(true);
                    }
                    /* Start - Code added by RS on 07-Sept-2017 for adding a button to update cart totals in case the module has been updated */
                    if (Tools::getValue('cron') == 'update_analytics') {
                        $abd_obj->updateAbandonList(true, true);
                    }
                    /* End - Code added by RS on 07-Sept-2017 for adding a button to update cart totals in case the module has been updated */
                } else {
                    echo $this->module->l('You are not authorized to access this page');
                    die;
                }
            } else {
                echo $this->module->l('You are not authorized to access this page');
                die;
            }
        }
    }

    public function postProcess()
    {
        parent::postProcess();
        //Handle Ajax request
        if (Tools::isSubmit('ajax')) {
            if (Tools::isSubmit('action')) {
                if ($this->context->cart->id != 0) {
                    $customer_data = array();
                    $action = Tools::getValue('action');
                    if ($action == 'add_guest_email') {
                        $customer_data['email'] = Tools::getValue('email');
                        $customer_data['fname'] = Tools::getValue('fname');
                        $customer_data['lname'] = Tools::getValue('lname');
                        $customer_data['cart_id'] = (int) $this->context->cart->id;
                        $this->addToTrackingTable($customer_data);
                    } elseif ($action == 'add_email') {
                        $customer_data['email'] = Tools::getValue('email');
                        $customer_data['cart_id'] = (int) $this->context->cart->id;
                        $customer_data['fname'] = Tools::getValue('fname');
                        $customer_data['lname'] = Tools::getValue('lname');
                        $this->addToTrackingTable($customer_data);
                    }
                }
            }
        }
    }

    private function addToTrackingTable($customer)
    {
        $check_query = 'select email from ' . _DB_PREFIX_ . AbandonedCartCore::ABD_TRACK_CUSTOMERS_TABLE_NAME .
                ' where id_cart = ' . (int) $customer['cart_id'];
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($check_query);

        if ($results && count($results) > 0) {
            $query = 'UPDATE ' . _DB_PREFIX_ . AbandonedCartCore::ABD_TRACK_CUSTOMERS_TABLE_NAME . ' set';
            if (isset($customer['fname']) && $customer['fname'] != '') {
                $query .= ' firstname = "' . pSQL($customer['fname']) . '",';
            }
            if (isset($customer['lname']) && $customer['lname'] != '') {
                $query .= ' lastname = "' . pSQL($customer['lname']) . '",';
            }
            $query .= ' email = "' . pSQL($customer['email']) . '", 
                date_upd = now() WHERE id_cart = ' . (int) $customer['cart_id'];
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($query);
        } else {
            $query = 'INSERT INTO ' . _DB_PREFIX_ . AbandonedCartCore::ABD_TRACK_CUSTOMERS_TABLE_NAME .
                    ' (id_cart, firstname,
                    lastname, email, date_add, date_upd) values('
                    . (int) $customer['cart_id'] . ', "'
                    . pSQL($customer['fname']) . '", "'
                    . pSQL($customer['lname']) . '","' . pSQL($customer['email']) . '", now(), now())';

            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($query);
        }
    }
}
