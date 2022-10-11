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

class AmazonPaymentModule extends PaymentModule
{
    /*
     * 2013/07/13
     * Fix Notice: previously the name was "Amazon" renamed to "amazon" because causing a bug with Module::getInstanceByName
     * for previous version users, in cas of update, that'll require to UPDATE ps_orders and change "Amazon to amazon" in the module column
     */
    public $name = 'amazon';

    /**
     * @param $id_cart
     * @param $id_order_state
     * @param string $paymentMethod
     * @param null $mpOrderId
     * @param null $mpOrderStatus
     * @param AmazonCart $amazonCart
     * @param bool $useTaxes
     * @param bool $date_add
     * @return bool|int
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function validateMarketplaceOrder($id_cart, $id_order_state, $paymentMethod = 'Unknown', $mpOrderId = null, $mpOrderStatus = null, $amazonCart = null, $useTaxes = false, $date_add = false)
    {
        // Copying data from cart
        $order = new AmazonOrder();

        $order->id_carrier = (int)$amazonCart->id_carrier;
        $order->id_customer = (int)$amazonCart->id_customer;
        $order->id_address_invoice = (int)$amazonCart->id_address_invoice;
        $order->id_address_delivery = (int)$amazonCart->id_address_delivery;
        $order->id_currency = (int)$amazonCart->id_currency;
        $order->id_lang = (int)$amazonCart->id_lang;
        $order->id_cart = (int)$amazonCart->id;

        $this->removeCartRuleWithoutCode($amazonCart);

        $customer = new Customer((int)$order->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            echo Tools::displayError(sprintf('%s(#%d): %s', basename(__FILE__), __LINE__, 'Customer is wrong.'));

            return (false);
        }

        $order->secure_key = pSQL($customer->secure_key);
        $order->secure_key = pSQL($customer->secure_key);
        if (!$order->secure_key) {
            $order->secure_key = md5(time());
        }

        $order->send_email = false;
        $order->payment = Tools::substr($paymentMethod, 0, 32);
        $order->module = $this->name;
        $order->recyclable = Configuration::get('AMAZON_RECYCLABLE_PACK') !== false ? (bool)Configuration::get('AMAZON_RECYCLABLE_PACK') : (bool)Configuration::get('PS_RECYCLABLE_PACK');

        $order->total_products = (float)$amazonCart->getOrderTotal(false, 1);
        $order->total_products_wt = (float)$amazonCart->getOrderTotal($useTaxes, 1);
        $order->total_discounts = (float)abs($amazonCart->getOrderTotal(false, 2));
        $order->total_shipping = (float)$amazonCart->getOrderTotal($useTaxes, 5);
        $order->total_wrapping = (float)abs($amazonCart->getOrderTotal(false, 6));
        $order->total_paid_real = (float)$amazonCart->getOrderTotal(true, 3);
        $order->total_paid = (float)$amazonCart->getOrderTotal(true, 3);

        $order->id_order_state = $id_order_state;
        $order->shipping_number = '';
        $order->delivery_number = 0;
        $order->exported = '';
        $order->carrier_tax_rate = $amazonCart->marketplaceGetCarrierTaxRate();

        $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
        $order->round_type = Configuration::get('PS_ROUND_TYPE');
        
        $id_warehouse = 0;
        $id_shop = 1;

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            AmazonContext::restore($this->context);

            $order->reference = Order::generateReference();

            $order->total_paid_tax_excl = (float)$amazonCart->getOrderTotal(false, 3);
            $order->total_paid_tax_incl = (float)$amazonCart->getOrderTotal(true, 3);

            $order->total_shipping_tax_excl = (float)$amazonCart->getOrderTotal(false, 5);
            $order->total_shipping_tax_incl = (float)$amazonCart->getOrderTotal(true, 5);

            $order->total_paid_real = 0;

            $order->current_state = (int)$id_order_state;

            $id_shop = (int)$this->context->shop->id;
            $id_shop_group = (int)$this->context->shop->id_shop_group;

            $id_warehouse = (int)Configuration::get('AMAZON_WAREHOUSE');

            if ($id_shop) {
                $order->id_shop = $id_shop;
                $order->id_shop_group = $id_shop_group;
            } else {
                $order->id_shop = 1;
                $order->id_shop_group = 1;
            }
        } else {
            $order->id_shop = $id_shop;
        }

        if ($date_add) {
            $order->date_add = $date_add;
            $order->date_upd = $date_add;
            $autodate = false;
        } else {
            $autodate = true;
        }

        if (!Validate::isLoadedObject($amazonCart)) {
            echo Tools::displayError(sprintf('%s(#%d): %s', basename(__FILE__), __LINE__, 'Amazon Cart is wrong.'));

            return (false);
        }
        $null_date = '0000-00-00 00:00:00';
        $order->invoice_date = $null_date;
        $order->delivery_date = $null_date;

        $currency = new Currency($amazonCart->id_currency);
        $order->conversion_rate = $currency->conversion_rate ? $currency->conversion_rate : 1;

        $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
        $order->round_type = Configuration::get('PS_ROUND_TYPE');

        $total_wrapping_tax_incl = 0;
        $total_wrapping_tax_excl = 0;

        $order_weight = 0;

        if (!($products = $amazonCart->getProducts())) {
            AmazonTools::pre(array(
                sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                Tools::displayError(sprintf('%s(#%d): %s - %s', basename(__FILE__), __LINE__, 'Unable to get product from cart.', print_r($amazonCart->amazonProducts, true)))
            ));

            return (false);
        }
        // Check For Cart Mismatch
        //
        foreach ($products as $product) {
            $SKU = trim((string)$product['reference']);

            if (!isset($amazonCart->amazonProducts[$SKU])) {
                AmazonTools::pre(array(
                    sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                    Tools::displayError(sprintf('%s(#%d): %s', basename(__FILE__), __LINE__, 'Product cart mismatch.')),
                    "Cart:".print_r($products, true),
                    "Amazon Cart:".print_r($amazonCart->amazonProducts, true)
                ));

                return (false);
            }
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Cart Content: %s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p($products);
        }

        // Prevent to import duplicate order
        usleep(rand(100, 1000));

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Duplicate check: %s - %s::%s - line #%d - %s %s %s %s'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__, $date_add, $order->total_paid, $paymentMethod, $this->name));
            CommonTools::p($products);
        }


        // Prevent duplicates
        if ($amazonCart->amazonChannel != Amazon::AFN && !$autodate && ($id_order = $order->isExistingOrder($date_add, $order->total_paid, $paymentMethod, $this->name))) {
            if (Amazon::$debug_mode) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            }
            CommonTools::p(sprintf($this->l('Order ID (%s) has already been imported...').' - id_order: %d', $mpOrderId, $id_order));

            return (false);
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(get_object_vars($order));
        }

        if (!$order->validateFields(false, false)) {
            echo Tools::displayError('Validation Failed.');

            return (false);
        }

        if ($amazonCart->amazon_order_info instanceof AmazonOrderInfo) {
            $available_fields = get_object_vars($amazonCart->amazon_order_info);

            foreach ($available_fields as $field => $value) {
                if (Tools::strlen($value)) {
                    $order->amazon_order_info->{$field} = $value;
                }
            }
        }

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Order Info: - %s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(get_object_vars($order->amazon_order_info));
        }

        // Add Amazon Order
        //
        $order->add($autodate, null, $mpOrderId, $mpOrderStatus, $amazonCart->amazonChannel);

        // Next !
        if (Validate::isLoadedObject($order)) {
            if ($order->amazon_order_info instanceof AmazonOrderInfo) {
                $order->amazon_order_info->id_order = (int)$order->id;
                $order->amazon_order_info->saveOrderInfo();
            }

            $outOfStock = false;
            foreach ($products as $product) {
                // Main SKU / Reference
                $SKU = trim((string)$product['reference']);

                $id_product = (int)$product['id_product'];
                $id_product_attribute = $product['id_product_attribute'] ? (int)$product['id_product_attribute'] : null;

                // Must be always true
                //
                $update_stocks = (bool)Configuration::get('PS_STOCK_MANAGEMENT');

                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $productQuantity = (int)AmazonProduct::getQuantity($id_product, $id_product_attribute);
                    $quantityInStock = ($productQuantity - (int)$product['cart_quantity'] < 0) ? $productQuantity : (int)$product['cart_quantity'];

                    if ($update_stocks) {
                        if ((($updateResult = Product::updateQuantity($product)) === false or $updateResult === -1)) {
                            $outOfStock = true;
                        }
                    }

                    if ($id_product_attribute) {
                        $product['quantity_attribute'] -= $product['cart_quantity'];
                    }

                    $product['stock_quantity'] -= $product['cart_quantity'];

                    if ($product['stock_quantity'] < 0) {
                        $product['stock_quantity'] = 0;
                    }
                } else {
                    $productQuantity = Product::getRealQuantity($id_product, $id_product_attribute, $id_warehouse, $order->id_shop);
                    $quantityInStock = $productQuantity - $product['cart_quantity'];

                    // updates stock in shops PS 1.5

                    if ($update_stocks) {
                        if (Amazon::$debug_mode) {
                            CommonTools::p(sprintf('Cart: %s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                            CommonTools::p('stock update: '.($product['cart_quantity'] * -1));
                        }
                        StockAvailable::updateQuantity($id_product, $id_product_attribute, $product['cart_quantity'] * -1, $order->id_shop);
                    }
                }

                // default taxes informations
                $product['id_tax'] = 0;
                $product['tax'] = null;

                $id_tax_rules_group = 0;
                $tax_rate = 0;

                $quantity = (int)$product['cart_quantity'];
                
                
                // Include VAT (Prestashop 1.5);
                if (!Tax::excludeTaxeOption() || $useTaxes == true) {
                    if (isset($amazonCart->amazonProducts[$SKU]['tax_rate']) && $amazonCart->amazonProducts[$SKU]['tax_rate']) {
                        $tax_rate = $amazonCart->amazonProducts[$SKU]['tax_rate'];
                        $id_tax_rules_group = $amazonCart->amazonProducts[$SKU]['id_tax_rules_group'];

                        $product['id_tax'] = $amazonCart->amazonProducts[$SKU]['id_tax'];
                        $product['rate'] = $amazonCart->amazonProducts[$SKU]['tax_rate'];
                    }
                }

                if (!$amazonCart->amazonProducts[$SKU]['amazon_has_tax']) {
                    $unit_price_tax_incl = Tools::ps_round((float)$amazonCart->amazonProducts[$SKU]['price'], 2);
                    $unit_price_tax_excl = Tools::ps_round($unit_price_tax_incl / (1 + ($product['rate'] / 100)), 2);

                    $total_price_tax_incl = Tools::ps_round($unit_price_tax_incl * $quantity, 2);
                    $total_price_tax_excl = Tools::ps_round($unit_price_tax_excl * $quantity, 2);

                    $unit_wrapping_tax_excl = Tools::ps_round((float)$amazonCart->amazonProducts[$SKU]['giftwrap'] / ((100 + $product['rate']) / 100), 2);
                    $unit_wrapping_tax_incl = Tools::ps_round((float)$amazonCart->amazonProducts[$SKU]['giftwrap'], 2);
                } else {
                    if ($amazonCart->amazonProducts[$SKU]['europe']) {
                        $unit_price_tax_excl = Tools::ps_round((float)$amazonCart->amazonProducts[$SKU]['price'] - ((float)$amazonCart->amazonProducts[$SKU]['amazon_item_tax'] / $quantity), 2);
                        $unit_price_tax_incl = Tools::ps_round((float)$amazonCart->amazonProducts[$SKU]['price'], 2);
                    } else {
                        // why this case: https://support.common-services.com/helpdesk/tickets/36978
                        // seems in USA, the product price in the feed is tax excluded
                        $unit_price_tax_excl = Tools::ps_round((float)$amazonCart->amazonProducts[$SKU]['price'], 2);
                        $unit_price_tax_incl = Tools::ps_round((float)$amazonCart->amazonProducts[$SKU]['price'] + ((float)$amazonCart->amazonProducts[$SKU]['amazon_item_tax'] / $quantity), 2);
                    }

                    $total_price_tax_incl = Tools::ps_round($unit_price_tax_incl * $quantity, 2);
                    $total_price_tax_excl = Tools::ps_round($unit_price_tax_excl * $quantity, 2);

                    $unit_wrapping_tax_excl = Tools::ps_round(((float)$amazonCart->amazonProducts[$SKU]['giftwrap'] / $quantity) / ((100 + $product['rate']) / 100), 2);
                    $unit_wrapping_tax_incl = Tools::ps_round(((float)$amazonCart->amazonProducts[$SKU]['giftwrap'] / $quantity), 2);
                }

                if ($amazonCart->amazonProducts[$SKU]['giftwrap']) {
                    $order->gift = true;
                }

                if (Tools::strlen($amazonCart->amazonProducts[$SKU]['giftmsg'])) {
                    $order->gift_message = sprintf('%s - ', $order->gift_message, $amazonCart->amazonProducts[$SKU]['giftmsg']);
                }

                $total_wrapping_tax_incl += $unit_wrapping_tax_incl;
                $total_wrapping_tax_excl += $unit_wrapping_tax_excl;

                $product_name = $product['name'].((isset($product['attributes']) && $product['attributes'] != null) ? ' - '.$product['attributes'] : '');

                //
                // Order Detail entry
                //
                $order_detail = new OrderDetail(null, null, isset($this->context) ? $this->context : null);

                $order_detail->date_add = $date_add;
                $order_detail->date_upd = $date_add;

                // order details
                $order_detail->id_order = (int)$order->id;

                // product informations
                $order_detail->product_name = $product_name;
                $order_detail->product_id = $id_product;
                $order_detail->product_attribute_id = $id_product_attribute;

                // quantities
                $order_detail->product_quantity = (int)$product['cart_quantity'];
                $order_detail->product_quantity_in_stock = (int)$quantityInStock;

                $products_weight = (float)Tools::ps_round($product['id_product_attribute'] && $product['weight_attribute'] ? $product['weight_attribute'] : $product['weight'], 4);

                // product references
                $order_detail->product_price = (float)$unit_price_tax_excl;
                $order_detail->product_ean13 = $product['ean13'] ? $product['ean13'] : null;
                $order_detail->product_upc = $product['upc'] ? $product['upc'] : null;
                $order_detail->product_reference = $SKU;
                $order_detail->product_supplier_reference = $product['supplier_reference'] ? $product['supplier_reference'] : null;
                $order_detail->product_weight = $products_weight;
                $order_weight += $products_weight;

                // taxes
                $order_detail->tax_name = Tools::substr($product['tax'], 0, 16); // deprecated - has bug also; size in ps_order_detail: 16 - in tax_lang: 32
                $order_detail->tax_rate = (float)$tax_rate;
                $order_detail->id_tax_rules_group = (int)$id_tax_rules_group;
                $order_detail->ecotax = $product['ecotax'];

                // For PS 1.4
                $order_detail->download_deadline = $null_date;

                // For PS 1.5+
                // price details
                $order_detail->total_price_tax_incl = (float)$total_price_tax_incl;
                $order_detail->total_price_tax_excl = (float)$total_price_tax_excl;
                $order_detail->unit_price_tax_incl = (float)$unit_price_tax_incl;
                $order_detail->unit_price_tax_excl = (float)$unit_price_tax_excl;
                $order_detail->tax_computation_method = $amazonCart->marketplaceCalculationMethod();

                $order_detail->original_product_price = (float)$unit_price_tax_excl;
                $order_detail->purchase_supplier_price = isset($product['wholesale_price']) ? Tools::ps_round((float)$product['wholesale_price'], 2) : 0;

                // shop and warehouse
                $order_detail->id_shop = (int)$order->id_shop;
                $order_detail->id_warehouse = (int)$id_warehouse;

                if (Amazon::$debug_mode) {
                    CommonTools::p("Order Details:");
                    CommonTools::p(get_object_vars($order_detail));
                }

                // add into db
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $order_detail->add(Tools::strlen($date_add) ? false : true);

                    if (!Validate::isLoadedObject($order_detail)) {
                        print Tools::displayError('OrderDetail::add() - Failed');
                        die;
                    }
                } else {
                    $order_detail->add(Tools::strlen($date_add) ? false : true);

                    if (!Validate::isLoadedObject($order_detail)) {
                        print Tools::displayError('OrderDetail::add() - Failed');
                        die;
                    }

                    $id_order_detail = $order_detail->id;

                    if ($tax_rate) {
                        $address_delivery = new Address($order->id_address_delivery);
    
                        if (Validate::isLoadedObject($address_delivery)) {
                            $id_tax = $this->getIdTax($address_delivery->id_country, $id_tax_rules_group);
    
                            $tax_query = 'INSERT INTO `'._DB_PREFIX_.'order_detail_tax` (id_order_detail, id_tax, unit_amount, total_amount) VALUES '.sprintf('(%d, %d, %f, %f) ;', $id_order_detail, $id_tax, $total_price_tax_excl, $total_price_tax_incl - $total_price_tax_excl);
    
                            if (!($tax_result = Db::getInstance()->execute($tax_query))) {
                                AmazonTools::pre(array(
                                    "Order:\n",
                                    sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__),
                                    nl2br(print_r($tax_query, true)),
                                    Tools::displayError('Failed to add tax details.')
                                ));
                            }
    
                            if (Amazon::$debug_mode) {
                                CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                                CommonTools::p('Tax Query'.$tax_query);
                                CommonTools::p("Result:".(!$tax_result ? 'Failed' : 'OK'));
                            }
                        }
                    }
                }

                if (!Validate::isLoadedObject($order_detail)) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p(Tools::displayError('OrderDetail::add() - Failed'));
                    die;
                }

                $order_item = new AmazonOrderItem();
                $order_item->mp_order_id = $mpOrderId;
                $order_item->order_item_id = $amazonCart->amazonProducts[$SKU]['order_item_id'];
                $order_item->id_order = (int)$order->id;
                $order_item->id_product = $id_product;
                $order_item->id_product_attribute = $id_product_attribute;
                $order_item->sku = $SKU;
                $order_item->asin = $amazonCart->amazonProducts[$SKU]['asin'];
                $order_item->quantity = $order_detail->product_quantity;
                $order_item->customization = $amazonCart->amazonProducts[$SKU]['customization'];

                if (!$order_item->saveOrderItem()) {
                    print Tools::displayError(sprintf('%s - %s (%d/%d)', $this->l('Unable to add item to ordered item table'), $order->id, $order_item->id_product, $order_item->id_product_attribute));
                }
            } // end foreach ($products)

            $order_update = false;

            // Update Order for Wrapping Fees / Gift
            if ($total_wrapping_tax_incl) {
                $order->total_wrapping = $total_wrapping_tax_incl;
                $order->total_wrapping_tax_incl = $total_wrapping_tax_incl;
                $order->total_wrapping_tax_excl = $total_wrapping_tax_excl;
                $order_update = true;
            }


            if (Tools::strlen($order->gift_message)) {
                $order->gift_message = rtrim($order->gift_message, ' - ');
                $order->gift_message = preg_replace('/[^<>{}]/i', '', $order->gift_message);
                $order_update = true;
            }

            if ($order_update) {
                $order->update();
            }

            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                // Adding an entry in order_carrier table
                if ($order->id_carrier) {
                    $order_carrier = new OrderCarrier();
                    $order_carrier->id_order = (int)$order->id;
                    $order_carrier->date_add = $date_add;
                    $order_carrier->date_upd = $date_add;
                    $order_carrier->id_carrier = $order->id_carrier;
                    $order_carrier->weight = (float)$order->getTotalWeight();
                    $order_carrier->shipping_cost_tax_excl = $order->total_shipping_tax_excl;
                    $order_carrier->shipping_cost_tax_incl = $order->total_shipping_tax_incl;
                    $order_carrier->add(Tools::strlen($date_add) ? false : true);

                    if (Amazon::$debug_mode) {
                        CommonTools::p("Order Carrier:");
                        CommonTools::p(get_object_vars($order_carrier));
                    }
                }
            }

            // New Order Status
            $orderStatus = new OrderState((int)$id_order_state);

            // Hook New Order
            if (Validate::isLoadedObject($orderStatus)) {
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    Hook::newOrder($amazonCart, $order, $customer, $currency, $orderStatus);
                } else {
                    // Hook validate order
                    Hook::exec('actionValidateOrder', array(
                        'cart' => $amazonCart,
                        'order' => $order,
                        'customer' => $customer,
                        'currency' => $currency,
                        'orderStatus' => $orderStatus
                    ));
                }
                foreach ($amazonCart->getProducts() as $product) {
                    if ($orderStatus->logable) {
                        ProductSale::addProductSale((int)$product['id_product'], (int)$product['cart_quantity']);
                    }
                }
            }

            // Order is reloaded because the status just changed
            // @see class PaymentModule.php
            $order = new Order($order->id);

            if (!Validate::isLoadedObject($order)) {
                echo Tools::displayError(sprintf('%s(#%d): %s', basename(__FILE__), __LINE__, 'Order creation failed.'));
                return (false);
            }

            if (Amazon::$debug_mode) {
                AmazonTools::pre(array(
                    "Add To History \n",
                    "autodate:".$autodate."\n",
                    "date:".$order->date_add."\n"
                ));
            }

            $this->addToHistory($order->id, $id_order_state, $autodate ? null : $order->date_add);

            // Update payment date
            if (version_compare(_PS_VERSION_, '1.5', '>') && Tools::strlen($order->reference) && Tools::strlen($date_add)) {
                Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'order_payment` SET `date_add` = "'.pSQL($date_add).'" WHERE `order_reference` = "'.pSQL($order->reference).'"');
            }

            // updates stock in shops
            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                foreach ($products as $key => $product) {
                    if (StockAvailable::dependsOnStock((int)$product['id_product'])) {
                        StockAvailable::synchronize((int)$product['id_product'], $order->id_shop);
                    }
                }
            }

            $this->currentOrder = (int)$order->id;

            return $this->currentOrder;
        } else {
            echo Tools::displayError(sprintf('%s(#%d): %s', basename(__FILE__), __LINE__, 'Order creation failed.'));

            return (false);
        }
    }


    public function getIdTax($id_country, $id_tax_rules_group)
    {
        $sql = 'SELECT `id_tax` FROM `'._DB_PREFIX_.'tax_rule` WHERE `id_tax_rules_group`= '.(int)$id_tax_rules_group.' AND `id_country`= '.(int)$id_country;

        $id_tax = (int)Db::getInstance()->getValue($sql);

        if (Amazon::$debug_mode) {
            AmazonTools::pre(array(
                "SQL: ".print_r($sql, true)."\n",
                "id_tax:".print_r($id_tax, true)."\n"
            ));
        }

        return($id_tax);
    }
    private function addToHistory($id_order, $id_order_state, $date_add)
    {
        $id_employee = Configuration::get('AMAZON_EMPLOYEE');
        // Add History
        $new_history = new AmazonOrderHistory();
        $new_history->id_order = (int)$id_order;
        $new_history->id_employee = (int)$id_employee ? (int)$id_employee : 1;
        $new_history->date_add = $date_add;
        $new_history->date_upd = $date_add;
        $new_history->changeIdOrderState($id_order_state, $id_order);
        $new_history->addWithOutEmail(Tools::strlen($date_add) ? false : true);

        if (Amazon::$debug_mode) {
            CommonTools::p(sprintf('Order History: %s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p(get_object_vars($new_history));
        }

        return;
    }

    public function removeCartRuleWithoutCode(&$cart)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>') && Validate::isLoadedObject($cart)) {
            $cart_rules = $cart->getCartRules();

            if (is_array($cart_rules) && count($cart_rules)) {
                foreach ($cart_rules as $cr) {
                    if (!Tools::strlen($cr['code'])) {
                        $cart->removeCartRule($cr['id_cart_rule']);
                    }
                }
            }
            $cart->update();
        }
    }
}
