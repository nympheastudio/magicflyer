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

class AbandonedCartActionModuleFrontController extends ModuleFrontController
{
    private $is_cart_live = true;

    public function initContent()
    {
        parent::initContent();
        $hash_key = Tools::getValue('hash_key');
        $cart_data = $this->extractInformationFromHash($hash_key);
        $data = array();
        $data['action'] = Tools::getValue('action');
        $data['id_cart'] = $cart_data[0];
        $data['id_customer'] = $cart_data[1];
        $data['id_abandon'] = $cart_data[2];
        $data['discount_code'] = $cart_data[3];
        $data['customer_email'] = urldecode($cart_data[4]);
        $data['customer_secure_key'] = $cart_data[5];
        $data['id_product'] = $cart_data[6];
        $customer_obj = new Customer((int) $data['id_customer']);
        if ($customer_obj->id == Customer::customerExists($data['customer_email'], true)) {
            if ($customer_obj->secure_key == $data['customer_secure_key']) {
                $cart_obj = new Cart((int) $data['id_cart']);
                if ($cart_obj->id_customer == $data['id_customer']) {
                    $this->processCartWithLogin($data);
                } else {
                    Tools::redirect('index.php');
                }
                unset($cart_obj);
            } else {
                Tools::redirect('index.php');
            }
        } else {
            Tools::redirect('index.php');
        }
        unset($customer_obj);
        if ($data['action'] == 'direct_checkout' && $this->is_cart_live) {
            Tools::redirect('index.php?controller=order');
        } elseif ($data['action'] == 'single_product') {
            Tools::redirect($this->context->link->getProductLink($data['id_product']));
        }
        Tools::redirect($this->context->link->getBaseLink());
    }

    private function extractInformationFromHash($hash_key)
    {
        $decoded_hash = str_rot13($hash_key);
//		$hash_chunks = explode('~^', $decoded_hash);
//		if ($hash_chunks[1] == Configuration::get('VELSOF_ABD_SECURE_KEY'))
        $cart_data = explode('|', $decoded_hash);
//		else
//			$cart_data = false;
        return $cart_data;
    }

    private function getProductsToAdd($cart_id)
    {
        $cart = new Cart((int) $cart_id);
        $detail = $cart->getProducts();
        $products_to_add = array();
        $count = 0;
        foreach ($detail as $product) {
            $products_to_add[$count]['id_product'] = $product['id_product'];
            $products_to_add[$count]['id_product_attribute'] = $product['id_product_attribute'];
            $products_to_add[$count]['id_customization'] = $product['id_customization'];
            $products_to_add[$count]['id_address_delivery'] = $product['id_address_delivery'];
            $products_to_add[$count]['quantity'] = $product['quantity'];
            $count++;
        }
        unset($detail);
        unset($cart);
        return $products_to_add;
    }

    private function processCartWithLogin($data)
    {
        if (!$this->context->cookie->isLogged()) {
            if ($data['id_customer'] != 0) {
                $customer = new Customer($data['id_customer']);
                $this->context->smarty->assign('confirmation', 1);
                $this->context->cookie->id_customer = (int) $customer->id;
                $this->context->cookie->customer_lastname = $customer->lastname;
                $this->context->cookie->customer_firstname = $customer->firstname;
                $this->context->cookie->passwd = $customer->passwd;
                $this->context->cookie->logged = 1;
                $this->context->cookie->email = $customer->email;
                $this->context->cookie->is_guest = $customer->is_guest;

                // Add customer to the context
                $this->context->customer = $customer;
                $id_cart = (int) Cart::lastNoneOrderedCart($this->context->customer->id);
            } else {
                $id_cart = (int) $data['id_cart'];
            }
            $cart = new Cart($id_cart);

            if ($id_cart > 0 && $id_cart != (int) $data['id_cart']) {
                $this->context->cart = $cart;
                $products = $this->getProductsToAdd((int) $data['id_cart']);
                foreach ($products as $pro) {
                    $this->context->cart->updateQty(
                        (int) $pro['quantity'],
                        (int) $pro['id_product'],
                        (int) $pro['id_product_attribute'],
                        (int) $pro['id_customization'],
                        'up',
                        (int) $pro['id_address_delivery'],
                        null,
                        false
                    );
                }
            } elseif ($id_cart > 0 && $id_cart == (int) $data['id_cart']) {
                $this->context->cart = $cart;
            } else {
                $cart = new Cart((int) $data['id_cart']);
                if ($cart->orderExists()) {
                    $this->is_cart_live = false;
                } else {
                    $this->context->cart = $cart;
                }
            }

            if ($this->is_cart_live && Configuration::get('PS_ORDER_PROCESS_TYPE')) {
                $delivery_option = array(
                    $this->context->cart->id_address_delivery => (int) $this->context->cart->id_carrier . ','
                );
                $this->context->cart->setDeliveryOption($delivery_option);
            }
        } else {
            if (Cart::getNbProducts($this->context->cookie->id_cart) == 0) {
                $cart = new Cart((int) $data['id_cart']);
                if ($cart->orderExists()) {
                    $this->is_cart_live = false;
                } else {
                    $this->context->cart = $cart;
                }
            }
        }

        if ($this->is_cart_live) {
            $this->context->cart->save();
            $this->context->cookie->id_cart = (int) $this->context->cart->id;
            $this->context->cookie->write();
            $this->context->cart->autosetProductAddress();

            if (CartRule::cartRuleExists($data['discount_code'])) {
                $cart_rule = new CartRule(CartRule::getIdByCode($data['discount_code']));
                $validity = $cart_rule->checkValidity($this->context, false, false);
                if (count($this->context->cart->getCartRules()) == 0 && $validity) {
                    $this->context->cart->addCartRule((int) CartRule::getIdByCode($data['discount_code']));
                }
                $this->context->cart->update();
            }
            Hook::exec('actionAuthentication');

            // Login information have changed, so we check if the cart rules still apply
            CartRule::autoRemoveFromCart($this->context);
            CartRule::autoAddToCart($this->context);
        }
    }
}
