<?php
/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-2020 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */
class OrderOpcController extends OrderOpcControllerCore
{
    /*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    public $php_self = 'order-opc';
    /*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    public $isLogged;
    /*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    protected $ajax_refresh = false;
    /*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    public function getCustomerGroups()
    {
        $customer_groups = array();
        if (isset($this->context->cart->id_customer)) {
            if ($this->context->cart->id_customer == 0) {
                $customer_groups[1] = 1;
            } else {
                foreach (Customer::getGroupsStatic($this->context->cart->id_customer) as $group) {
                    $customer_groups[$group] = 1;
                }
            }
        } elseif ($this->context->customer->is_guest == 1) {
            $customer_groups[1] = 2;
        } else {
            $customer_groups[1] = 1;
        }
        if (count($customer_groups) > 0) {
            return $customer_groups;
        } else {
            return false;
        }
    }
    /*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    public static function checkUserExclusions()
    {
        if (isset(Context::getContext()->customer->id)) {
            if (in_array(Context::getContext()->customer->id, explode(',', Configuration::get('CARTCON_CUSTOMERS')))) {
                return 1;
            }
        }
        return 0;
    }
    /*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    public static function returnConditionsAssociations()
    {
        if (self::checkUserExclusions() == 1) {
            return array();
        }
        $record = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'cartcon_ass` WHERE active="1" AND id_shop="' . Context::getContext()->shop->id . '"');
		
        return $record;
    }
    /*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    public static function returnConditions()
    {
        if (self::checkUserExclusions() == 1) {
            return array();
        }
        $record = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'cartcon` WHERE active="1" AND id_shop="' . Context::getContext()->shop->id . '"');
        return $record;
    }
    
	/*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    public static function returnConditionsCartValues()
    {
        if (self::checkUserExclusions() == 1) {
            return array();
        }
        $record = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'cartcon_value` WHERE active="1" AND id_shop="' . Context::getContext()->shop->id . '"');
        return $record;
    }
    /*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    public static function returnConditionsCartQuantity()
    {
        if (self::checkUserExclusions() == 1) {
            return array();
        }
        $record = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'cartcon_quantity` WHERE active="1" AND id_shop="' . Context::getContext()->shop->id . '"');
        return $record;
    }
    /*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    public static function returnConditionsCartCountry()
    {
        if (self::checkUserExclusions() == 1) {
            return array();
        }
        $record = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'cartcon_country` WHERE active="1"  AND id_shop="' . Context::getContext()->shop->id . '"');
        return $record;
    }
    /*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    public static function returnVersion($version)
    {
        $version = (int)str_replace(".", "", $version);
        if (strlen($version) == 4) {
            $version = (int)$version . "0";
        }
        if (strlen($version) == 3) {
            $version = (int)$version . "00";
        }
        if (strlen($version) == 2) {
            $version = (int)$version . "000";
        }
        if (strlen($version) == 1) {
            $version = (int)$version . "0000";
        }
        return (int)$version;
    }
    /**
     * Initialize order opc controller
     * @see FrontController::init()
     */
    /*
    * module: cartcon
    * date: 2020-12-18 14:45:52
    * version: 2.0.1
    */
    protected function _getPaymentMethods()
    {
        if (!$this->isLogged) {
            return '<p class="warning">' . Tools::displayError('Please sign in to see payment methods.') . '</p>';
        }
        if ($this->context->cart->OrderExists()) {
            return '<p class="warning">' . Tools::displayError('Error: This order has already been validated.') . '</p>';
        }
        if (!$this->context->cart->id_customer || !Customer::customerIdExistsStatic($this->context->cart->id_customer) || Customer::isBanned($this->context->cart->id_customer)) {
            return '<p class="warning">' . Tools::displayError('Error: No customer.') . '</p>';
        }
        $address_delivery = new Address($this->context->cart->id_address_delivery);
        $address_invoice = ($this->context->cart->id_address_delivery == $this->context->cart->id_address_invoice ? $address_delivery : new Address($this->context->cart->id_address_invoice));
        if (!$this->context->cart->id_address_delivery || !$this->context->cart->id_address_invoice || !Validate::isLoadedObject($address_delivery) || !Validate::isLoadedObject($address_invoice) || $address_invoice->deleted || $address_delivery->deleted) {
            return '<p class="warning">' . Tools::displayError('Error: Please select an address.') . '</p>';
        }
        if (count($this->context->cart->getDeliveryOptionList()) == 0 && !$this->context->cart->isVirtualCart()) {
            if ($this->context->cart->isMultiAddressDelivery()) {
                return '<p class="warning">' . Tools::displayError('Error: None of your chosen carriers deliver to some of the addresses you have selected.') . '</p>';
            } else {
                return '<p class="warning">' . Tools::displayError('Error: None of your chosen carriers deliver to the address you have selected.') . '</p>';
            }
        }
        if (!$this->context->cart->getDeliveryOption(null, false) && !$this->context->cart->isVirtualCart()) {
            return '<p class="warning">' . Tools::displayError('Error: Please choose a carrier.') . '</p>';
        }
        if (!$this->context->cart->id_currency) {
            return '<p class="warning">' . Tools::displayError('Error: No currency has been selected.') . '</p>';
        }
        if (!$this->context->cookie->checkedTOS && Configuration::get('PS_CONDITIONS')) {
            return '<p class="warning">' . Tools::displayError('Please accept the Terms of Service.') . '</p>';
        }
        
        if (is_array($product = $this->context->cart->checkQuantities(true))) {
            return '<p class="warning">' . sprintf(Tools::displayError('An item (%s) in your cart is no longer available in this quantity. You cannot proceed with your order until the quantity is adjusted.'), $product['name']) . '</p>';
        }
        if (method_exists($this->context->cart, 'checkProductsAccess')) {
            if ((int)$id_product = $this->context->cart->checkProductsAccess()) {
                return '<p class="warning">' . sprintf(Tools::displayError('An item in your cart is no longer available (%s). You cannot proceed with your order.'), Product::getProductName((int)$id_product)) . '</p>';
            }
        }
        
        $currency = Currency::getCurrency((int)$this->context->cart->id_currency);
        $minimal_purchase = Tools::convertPrice((float)Configuration::get('PS_PURCHASE_MINIMUM'), $currency);
        if ($this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) < $minimal_purchase) {
            return '<p class="alert alert-warning">' . sprintf(Tools::displayError('A minimum purchase total of %1s (tax excl.) is required to validate your order, current purchase total is %2s (tax excl.).'), Tools::displayPrice($minimal_purchase, $currency), Tools::displayPrice($this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS), $currency)) . '</p>';
        }
        
        
        
        
        
        if (Configuration::get('CARTCON_FUTURE') == 1) {
            $timestamp_now = date("U");
            $a_dates = array();
            foreach ($this->context->cart->getProducts() AS $product) {
                if ($product['stock_quantity'] <= 0) {
                    $product['available_date'] = Product::getAvailableDate($product['id_product'], ($product['id_product_attribute'] == 0 ? null : $product['id_product_attribute']));
                    if ($timestamp_now < strtotime($product['available_date'])) {
                        $a_dates[$product['available_date']] = 1;
                    }
                }
            }
            if (count($a_dates) > 1) {
                return '<p class="alert alert-warning">' . sprintf(Tools::displayError('Availability dates of products in your cart are different. You cant place an order.')) . '</p>';
            }
        }
        foreach ($this->returnConditionsAssociations() AS $condition) {
            $customer_groups = $this->getCustomerGroups();
            foreach ($customer_groups as $ky => $gr) {
                if ($condition['c_group'] == $ky) {
                    if ($condition['c_type'] == 1) {
                        foreach ($this->context->cart->getProducts() AS $product) {
                            if ($product['id_product'] == $condition['c_target1']) {
                                if ($condition['c_value'] == 1) {
                                    $associations_ok = 0;
                                    foreach ($this->context->cart->getProducts() AS $product_check) {
                                        if ($condition['c_target2'] == $product_check['id_product']) {
                                            $associations_ok = 1;
                                        }
                                    }
                                    if ($associations_ok == 0) {
                                        $product1 = new Product($condition['c_target1'], false, $this->context->language->id);
                                        $product2 = new Product($condition['c_target2'], false, $this->context->language->id);
                                        $this->step = 0;
                                        $this->errors[] = sprintf(Tools::displayError('If your cart has a product: %1s you must order also product: %2s.'), $product1->name, '<a class="alert-link" href="' . Context::getContext()->link->getProductLink($product2) . '">' . $product2->name . '</a>');
                                    }
                                } elseif ($condition['c_value'] == 2) {
                                    $associations_ok = 0;
                                    foreach ($this->context->cart->getProducts() AS $product_check) {
                                        if ($condition['c_target2'] == $product_check['id_product']) {
                                            $associations_ok = 1;
                                        }
                                    }
                                    if ($associations_ok == 1) {
                                        $product1 = new Product($condition['c_target1'], false, $this->context->language->id);
                                        $product2 = new Product($condition['c_target2'], false, $this->context->language->id);
                                        $this->step = 0;
                                        $this->errors[] = sprintf(Tools::displayError('If your cart has a product: %1s you can\'t place an order for: %2s.'), $product1->name, '<a class="alert-link" href="' . Context::getContext()->link->getProductLink($product2) . '">' . $product2->name . '</a>');
                                    }
                                } elseif ($condition['c_value'] == 3) {
                                    $associations_ok = 0;
                                    foreach ($this->context->cart->getProducts() AS $product_check) {
                                        if ($condition['c_target1'] != $product_check['id_product']) {
                                            $associations_ok = 1;
                                        }
                                    }
                                    if ($associations_ok == 1) {
                                        $product1 = new Product($condition['c_target1'], false, $this->context->language->id);
                                        $this->step = 0;
                                        $this->errors[] = sprintf(Tools::displayError('If your cart has a product: %1s you can\'t place an order for any other product.'), "<strong>" . $product1->name . "</strong>");
                                    }
                                }
                            }
                        }
                    } elseif ($condition['c_type'] == 2) {
						
                        foreach ($this->context->cart->getProducts() AS $product) {
                            if ($condition['c_target1'] == $product['id_product']) {
								
								
                                $product_categories = array();
                                foreach ($this->context->cart->getProducts() AS $product) {
                                    foreach (Product::getProductCategories($product['id_product']) AS $product_category) {
                                        $product_categories[$product_category] = true;
                                    }
                                }
								
								//coeur magique
								if($condition['c_target1']==117){
											
											
											//echo 'coeur magique cart condition';exit;
											
											foreach ($this->context->cart->getProducts() AS $product) {
												
												
												if($product['id_product']==117){
													
													$qty_coeur = $product['quantity'];
													//$panier_coeur_ok = 0;
													$qty_pap_coeur_minimale =   $qty_coeur * 30;
													$qty_totale_pap_current = 0;
													$nb_pap = 0;
													
												}
												
												
												
												foreach (Product::getProductCategories($product['id_product']) AS $product_category) {
													
																										

													
													
if($product_category==$condition['c_target2']){
	
$nb_pap++;

$qty_totale_pap_current  += $product['quantity'];


$qty_pap_coeur_minimale_current = $qty_pap_coeur_minimale / $nb_pap;
//echo $qty_pap_coeur_minimale_current ;exit;
/*
$alert_coeur =  ', la quantité minimale de papillon avec le coeur est de :'.$product['id_product']
.'--qty:'.$product['quantity']
.'--au lieu de :'.$qty_coeur*30;*/

$alert_coeur =  ', la nombre total de papillon doit etre de '.$qty_pap_coeur_minimale;



}
												}
											}
											
	if( $qty_totale_pap_current < $qty_pap_coeur_minimale){
		
	$this->step = 0;
	$panier_coeur_ok = 0;

/*

<div class="alert alert-danger">
		<p>Il y a 1 erreur</p>
		<ol>
					<li>Pour commander  , vous devez également commander  <a class="alert-link" href="https://www.magicflyer.com/_proto/fr/-"></a>, la nombre total de papillon doit etre de 30</li>
				</ol>
			</div>
			*/
	
	$this->errors[] =	sprintf(Tools::displayError('If your cart has a product: %1s you must also buy a product from category: %2s.'), $product->name, '<a class="alert-link" rel="danger" href="' . Context::getContext()->link->getCategoryLink($category) . '">' . $category->name . '</a>'.$alert_coeur)
	;
	
	
	}											
										}
								
								
                                if ($condition['c_value'] == 1) {
                                    if (!isset($product_categories[$condition['c_target2']])) {
										
										
                                        $product = new Product($condition['c_target1'], false, $this->context->language->id);
                                        $category = new Category($condition['c_target2'], $this->context->language->id);
                                        $this->step = 0;
										

                                        $this->errors[] = sprintf(Tools::displayError('If your cart has a product: %1s you must also buy a product from category: %2s.'), $product->name, '<a class="alert-link" href="' . Context::getContext()->link->getCategoryLink($category) . '">' . $category->name . '</a>');
										
										
                                    }
                                } elseif ($condition['c_value'] == 2) {
                                    if (isset($product_categories[$condition['c_target2']])) {
                                        $product = new Product($condition['c_target1'], false, $this->context->language->id);
                                        $category = new Category($condition['c_target2'], $this->context->language->id);
                                        $this->step = 0;
                                        $this->errors[] = sprintf(Tools::displayError('If your cart has a product: %1s you can\'t buy a product from category: %2s.'), $product->name, '<a class="alert-link" href="' . Context::getContext()->link->getCategoryLink($category) . '">' . $category->name . '</a>');
                                    }
                                } elseif ($condition['c_value'] == 3) {
                                    $associations_ok = 0;
                                    foreach ($this->context->cart->getProducts() AS $product_check) {
                                        if ($condition['c_target1'] != $product_check['id_product']) {
                                            $associations_ok = 1;
                                        }
                                    }
                                    if ($associations_ok == 1) {
                                        $product1 = new Product($condition['c_target1'], false, $this->context->language->id);
                                        $this->step = 0;
                                        $this->errors[] = sprintf(Tools::displayError('If your cart has a product: %1s you can\'t place an order for any other product.'), "<strong>" . $product1->name . "</strong>");
                                    }
                                }
                            }
                        }
                    } elseif ($condition['c_type'] == 3) {
                        $product_categories = array();
                        $children_categories_target = array();
                        $children_categories_Associated = array();
                        $children_categories_target[] = $condition['c_target1'];
                        $children_categories_Associated[] = $condition['c_target2'];
                        if ($condition['subcatt'] == 1) {
                            $rootCategoryCondition = new Category($condition['c_target1']);
                            foreach ($rootCategoryCondition->getAllChildren($this->context->language->id) AS $children) {
                                $children_categories_target[] = $children->id;
                            }
                        }
                        if ($condition['subcata'] == 1) {
                            $rootCategoryConditionAssociated = new Category($condition['c_target2']);
                            foreach ($rootCategoryConditionAssociated->getAllChildren($this->context->language->id) AS $children) {
                                $children_categories_Associated[] = $children->id;
                            }
                        }
                        foreach ($this->context->cart->getProducts() AS $product) {
                            foreach (Product::getProductCategories($product['id_product']) AS $product_category) {
                                $product_categories[$product_category] = true;
                            }
                        }
                        foreach ($this->context->cart->getProducts() AS $product) {
                            if (in_array($condition['c_target1'], Product::getProductCategories($product['id_product']))) {
                                if ($condition['c_value'] == 1) {
                                    if (!isset($product_categories[$condition['c_target2']])) {
                                        $category1 = new Category($condition['c_target1'], $this->context->language->id);
                                        $category2 = new Category($condition['c_target2'], $this->context->language->id);
                                        $this->step = 0;
                                        $this->errors[] = sprintf(Tools::displayError('If your cart has a product from category: %1s (%2s) you must also buy a product from category: %3s.'), $category1->name, $product['name'], '<a class="alert-link" href="' . Context::getContext()->link->getCategoryLink($category2) . '">' . $category2->name . '</a>');
                                    }
                                } elseif ($condition['c_value'] == 2) {
                                    if (isset($product_categories[$condition['c_target2']])) {
                                        $category1 = new Category($condition['c_target1'], $this->context->language->id);
                                        $category2 = new Category($condition['c_target2'], $this->context->language->id);
                                        $this->step = 0;
                                        $this->errors[] = sprintf(Tools::displayError('If your cart has a product from category: %1s (%2s) you can\'t purchase a product from category: %3s.'), $category1->name, $product['name'], '<a class="alert-link" href="' . Context::getContext()->link->getCategoryLink($category2) . '">' . $category2->name . '</a>');
                                    }
                                } elseif ($condition['c_value'] == 3) {
                                    $associations_ok = 0;
                                    foreach ($this->context->cart->getProducts() AS $product_check) {
                                        if ($product['id_product'] != $product_check['id_product']) {
                                            $associations_ok = 1;
                                        }
                                    }
                                    if ($associations_ok == 1) {
                                        $category1 = new Category($condition['c_target1'], $this->context->language->id);
                                        $this->step = 0;
                                        $this->errors[] = sprintf(Tools::displayError('If your cart has a product from category: %1s (%2s) you can\'t place an order for any other product.'), "<strong>" . $category1->name . "</strong>", $product['name']);
                                    }
                                } elseif ($condition['c_value'] == 4) {
                                    $associations_ok = 0;
                                    foreach ($this->context->cart->getProducts() AS $product_check) {
                                        if (count(array_intersect($children_categories_target, Product::getProductCategories($product_check['id_product'])))) {
                                            $associations_ok = 1;
                                        } else {
                                            $category1 = new Category($condition['c_target1'], $this->context->language->id);
                                            $this->step = 0;
                                            $this->errors[] = sprintf(Tools::displayError('If your cart has a product from category: %1s (%2s) you can\'t place an order for product %3s'), "<strong>" . $category1->name . "</strong>", $product['name'], '<strong>' . $product_check['name'] . '<strong>');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
		
		//var_dump($this->errors);exit;
        foreach ($this->returnConditionsCartCountry() AS $condition) {
            $cart = Context::getContext()->cart;
            $tax = ($condition['c_tax'] == 1 ? true : false);
            $tax_text = ($tax == 1 ? Tools::displayError('tax incl.') : Tools::displayError('tax excl.'));
            if ($condition['c_cartValueType'] == 1) {
                $cart_total = $cart->getOrderTotal($tax, Cart::BOTH);
            } elseif ($condition['c_cartValueType'] == 2) {
                $cart_total = $cart->getOrderTotal($tax, Cart::BOTH_WITHOUT_SHIPPING);
            }
            $default_currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            $customer_groups = $this->getCustomerGroups();
            foreach ($customer_groups as $ky => $gr) {
                if ($condition['c_group'] == $ky) {
                    if ($condition['c_type'] == 1) {
                        $condition['c_value'] = Tools::convertPriceFull($condition['c_value'], $default_currency, $this->context->currency);
                        if (isset($cart->id_address_delivery)) {
                            $address = new Address($cart->id_address_delivery);
                            if ($condition['c_target'] == $address->id_country) {
                                if ($cart_total < $condition['c_value']) {
                                    $limit[$address->id_country] = $cart_total;
                                }
                            }
                            if (isset($limit)) {
                                foreach ($limit AS $key => $value) {
                                    if ($key == $condition['c_target']) {
                                        if ($limit[$key] < $condition['c_value']) {
                                            $this->step = 0;
                                            $countryObject = new Country($key, $this->context->language->id);;
                                            $this->errors[] = sprintf(Tools::displayError('To place an order from country: %1s your cart must be worth at least %2s, while you cart is worth %3s'), $countryObject->name, Tools::displayPrice($condition['c_value']) . ' ' . $tax_text, Tools::displayPrice($limit[$key]));
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($condition['c_type'] == 2) {
                        $condition['c_value'] = Tools::convertPriceFull($condition['c_value'], $default_currency, $this->context->currency);
                        if (isset($cart->id_address_delivery)) {
                            $address = new Address($cart->id_address_delivery);
                            if ($condition['c_target'] == $address->id_country) {
                                if ($cart_total > $condition['c_value']) {
                                    $limit[$address->id_country] = $cart_total;
                                }
                            }
                            if (isset($limit)) {
                                foreach ($limit AS $key => $value) {
                                    if ($key == $condition['c_target']) {
                                        if ($limit[$key] > $condition['c_value']) {
                                            $this->step = 0;
                                            $countryObject = new Country($key, $this->context->language->id);;
                                            $this->errors[] = sprintf(Tools::displayError('To place an order from country: %1s your cart can\'t be worth more than %2s, while you cart is worth %3s'), $countryObject->name, Tools::displayPrice($condition['c_value']) . ' ' . $tax_text, Tools::displayPrice($limit[$key]));
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unset($limit);
                }
            }
        }
        unset($limit);
        if (count($this->errors) > 0) {
            $errors = '';
            foreach ($this->errors AS $error) {
                $errors .= '<p class="warning">' . $error . '</p>';
            }
            return $errors;
        }
        foreach ($this->returnConditions() AS $condition) {
            if ($condition['c_type'] == 1) {
                $categoryObject = new Category ($condition['c_value'], $this->context->language->id);
                $category_in_cart = 0;
                foreach ($this->context->cart->getProducts() AS $product) {
                    foreach (Product::getProductCategories((int)$product['id_product']) as $category) {
                        if ($category == $condition['c_value']) {
                            $category_in_cart = 1;
                        }
                    }
                }
                if ($condition['c_option'] == 1) {
                    if ($category_in_cart == 0) {
                        $this->step = 0;
                        return '<p class="warning">' . sprintf(Tools::displayError('To place an order you must order product from category: %1s.'), $categoryObject->name) . '</p>';
                    }
                } elseif ($condition['c_option'] == 2) {
                    if ($category_in_cart == 1) {
                        $this->step = 0;
                        return '<p class="warning">' . sprintf(Tools::displayError('Your cart contains product from forbidden category: %1s. To continue order please remove it from cart.'), $categoryObject->name) . '</p>';
                    }
                }
            }
            if ($condition['c_type'] == 2) {
                $productObject = new Product($condition['c_value'], false, $this->context->language->id);
                $product_in_cart = 0;
                foreach ($this->context->cart->getProducts() AS $product) {
                    if ($product['id_product'] == $condition['c_value']) {
                        $product_in_cart = 1;
                    }
                }
                if ($condition['c_option'] == 1) {
                    if ($product_in_cart == 0) {
                        $this->step = 0;
                        return '<p class="warning">' . sprintf(Tools::displayError('To place an order you must order product: %1s.'), $productObject->name) . '</p>';
                    }
                } elseif ($condition['c_option'] == 2) {
                    if ($product_in_cart == 1) {
                        $this->step = 0;
                        return '<p class="warning">' . sprintf(Tools::displayError('Your cart contains forbidden product: %1s. To continue order please remove it from cart.'), $productObject->name) . '</p>';
                    }
                }
            }
            if ($condition['c_type'] == 3) {
                $nbOfProducts = 0;
                foreach ($this->context->cart->getProducts() AS $product) {
                    $nbOfProducts = $nbOfProducts + $product['quantity'];
                }
                if ($condition['c_option'] == 1) {
                    if ($nbOfProducts < $condition['c_value']) {
                        $this->step = 0;
                        return '<p class="warning">' . sprintf(Tools::displayError('Your cart contains less products than required (%1s). To continue order please increase quantity of products in your cart'), $condition['c_value']) . '</p>';
                    }
                } elseif ($condition['c_option'] == 2) {
                    if ($nbOfProducts > $condition['c_value']) {
                        $this->step = 0;
                        return '<p class="warning">' . sprintf(Tools::displayError('Your cart contains more products than allowed (%1s). To continue order please decrease quantity of products in your cart'), $condition['c_value']) . '</p>';
                    }
                }
            }
        }
        foreach ($this->returnConditionsCartValues() AS $condition) {
            $customer_groups = $this->getCustomerGroups();
            foreach ($customer_groups as $ky => $gr) {
                if ($condition['c_group'] == $ky) {
                    if ($condition['c_type'] == 1) {
                        foreach ($this->context->cart->getProducts() AS $product) {
                            foreach (Product::getProductCategories((int)$product['id_product']) as $category) {
                                if ($category == $condition['c_target']) {
                                    if (!isset($limit[$category])) {
                                        $limit[$category] = 0;
                                    }
                                    $limit[$category] = $limit[$category] + $product['total_wt'];
                                }
                            }
                        }
                        if (isset($limit)) {
                            foreach ($limit AS $key => $value) {
                                if ($key == $condition['c_target']) {
                                    if ($limit[$key] < Tools::convertPrice($condition['c_value'], $this->context->currency->id, new Currency(Configuration::get('PS_CURRENCY_DEFAULT')))) {
                                        $categoryObject = new Category ($key, $this->context->language->id);
                                        $this->step = 0;
                                        return '<p class="warning">' . sprintf(Tools::displayError('To place an order you must order for: %1s from category: %2s.'), Tools::displayPrice(Tools::convertPrice($condition['c_value'], $this->context->currency->id, new Currency(Configuration::get('PS_CURRENCY_DEFAULT'))), $this->context->currency->id), $categoryObject->name) . '</p>';
                                    }
                                }
                            }
                        }
                    }
                    unset($limit);
                }
            }
        }
        unset($limit);
        unset($limitp);
        foreach ($this->returnConditionsCartQuantity() AS $condition) {
            $customer_groups = $this->getCustomerGroups();
            foreach ($customer_groups as $ky => $gr) {
                if ($condition['c_group'] == $ky) {
                    $limitgmin = 0;
                    $limitgmax = 0;
                    $limitmaxattribute = 0;
                    if ($condition['c_type'] == 0) {
                        foreach ($this->context->cart->getProducts() AS $product) {
                            foreach (Product::getProductCategories((int)$product['id_product']) as $category) {
                                if ($category == $condition['c_target']) {
                                    if (!isset($limit[$category])) {
                                        $limit[$category] = 0;
                                    }
                                    $limit[$category] = $limit[$category] + $product['quantity'];
                                }
                            }
                        }
                        if (isset($limit)) {
                            foreach ($limit AS $key => $value) {
                                if ($key == $condition['c_target']) {
                                    if ($limit[$key] > $condition['c_value']) {
                                        $categoryObject = new Category ($key, $this->context->language->id);
                                        $this->step = 0;
                                        return '<p class="warning">' . sprintf(Tools::displayError('To place an order you can\'t have more than %1s products from category: %2s in your cart.'), $condition['c_value'], $categoryObject->name) . '</p>';
                                    }
                                }
                            }
                        }
                    }
                    unset($limit);
                    if ($condition['c_type'] == 1) {
                        foreach ($this->context->cart->getProducts() AS $product) {
                            foreach (Product::getProductCategories((int)$product['id_product']) as $category) {
                                if ($category == $condition['c_target']) {
                                    if (!isset($limit[$category])) {
                                        $limit[$category] = 0;
                                    }
                                    $limit[$category] = $limit[$category] + $product['quantity'];
                                }
                            }
                        }
                        if (isset($limit)) {
                            foreach ($limit AS $key => $value) {
                                if ($key == $condition['c_target']) {
                                    if ($limit[$key] < $condition['c_value']) {
                                        $categoryObject = new Category ($key, $this->context->language->id);
                                        $this->step = 0;
                                        return '<p class="warning">' . sprintf(Tools::displayError('To place an order you must order at least %1s products from category: %2s.'), $condition['c_value'], $categoryObject->name) . '</p>';
                                    }
                                }
                                if ($condition['multiply'] == 1) {
                                    if ($limit[$key] % $condition['c_value']) {
                                        $categoryObject = new Category ($key, $this->context->language->id);
                                        $this->step = 0;
                                        return '<p class="warning">' . sprintf(Tools::displayError('To place an order your cart must contain multiplied quantity (%1s) of products from category: %2s.'), $condition['c_value'], $categoryObject->name) . '</p>';
                                    }
                                }
                            }
                        }
                    }
                    unset($limit);
                    if ($condition['c_type'] == 2) {
                        foreach ($this->context->cart->getProducts() AS $product) {
                            if ($product['id_product'] == $condition['c_target']) {
                                if (!isset($limitp[$product['id_product']])) {
                                    $limitp[$product['id_product']] = 0;
                                }
                                $limitp[$product['id_product']] = $limitp[$product['id_product']] + $product['quantity'];
                            }
                        }
                        if (isset($limitp)) {
                            foreach ($limitp AS $key => $value) {
                                $productObject = new Product($key, false, $this->context->language->id);
                                if ($condition['c_value'] > $value) {
                                    $this->step = 0;
                                    return '<p class="warning">' . sprintf(Tools::displayError('To place an order you must order at least %1s quantity of product %2s. Your current quantity is %3s'), $condition['c_value'], $productObject->name, $value) . '</p>';
                                }
                            }
                        }
                    }
                    unset($limitp);
                    if ($condition['c_type'] == 3) {
                        foreach ($this->context->cart->getProducts() AS $product) {
                            if ($product['id_product'] == $condition['c_target']) {
                                if (!isset($limitp[$product['id_product']])) {
                                    $limitp[$product['id_product']] = 0;
                                }
                                $limitp[$product['id_product']] = $limitp[$product['id_product']] + $product['quantity'];
                            }
                        }
                        if (isset($limitp)) {
                            foreach ($limitp AS $key => $value) {
                                $productObject = new Product($key, false, $this->context->language->id);
                                if ($condition['c_value'] < $value) {
                                    $this->step = 0;
                                    return '<p class="warning">' . sprintf(Tools::displayError('You can\'t order more than %1s quantity of product %2s. Your current quantity is %3s'), $condition['c_value'], $productObject->name, $value) . '</p>';
                                }
                            }
                        }
                    }
                    unset($limitp);
                    if ($condition['c_type'] == 4) {
                        foreach ($this->context->cart->getProducts() AS $product) {
                            if (!isset($limitgmin)) {
                                $limitgmin = 0;
                            }
                            $limitgmin = $limitgmin + $product['quantity'];
                        }
                        if ($condition['c_value'] > $limitgmin) {
                            $this->step = 0;
                            return '<p class="warning">' . sprintf(Tools::displayError('To place an order you must order more than %1s products. Your current quantity is %2s'), $condition['c_value'], $limitgmin) . '</p>';
                        }
                        if ($condition['multiply'] == 1) {
                            if ($limitgmin % $condition['c_value']) {
                                $this->step = 0;
                                return '<p class="warning">' . sprintf(Tools::displayError('We accept only multiplied products quantity by %1s. Your current products quantity is %2s while required quantity is %3s'), $condition['c_value'], $limitgmin, $condition['c_value'] * ceil(($limitgmin / $condition['c_value']))) . '</p>';
                            }
                        }
                    }
                    unset($limitgmin);
                    if ($condition['c_type'] == 5) {
                        foreach ($this->context->cart->getProducts() AS $product) {
                            if (!isset($limitgmax)) {
                                $limitgmax = 0;
                            }
                            $limitgmax = $limitgmax + $product['quantity'];
                        }
                        if ($condition['c_value'] < $limitgmax) {
                            $this->step = 0;
                            return '<p class="warning">' . sprintf(Tools::displayError('To place an order you can\'t order more than %1s products. Your current quantity is %2s'), $condition['c_value'], $limitgmax) . '</p>';
                        }
                    }
                    unset($limitgmax);
                    if ($condition['c_type'] == 6) {
                        foreach ($this->context->cart->getProducts() AS $product) {
                            $product_object = new Product((int)$product['id_product'], false, $this->context->language->id);
                            if ($product_object->id_manufacturer == $condition['c_target']) {
                                if (!isset($limit[$product_object->id_manufacturer])) {
                                    $limit[$product_object->id_manufacturer] = 0;
                                }
                                $limit[$product_object->id_manufacturer] = $limit[$product_object->id_manufacturer] + $product['quantity'];
                            }
                        }
                        if (isset($limit)) {
                            foreach ($limit AS $key => $value) {
                                if ($key == $condition['c_target']) {
                                    if ($limit[$key] < $condition['c_value']) {
                                        $manufacturerObject = new Manufacturer($key, $this->context->language->id);
                                        $this->step = 0;
                                        return '<p class="alert alert-warning">' . sprintf(Tools::displayError('To place an order you must order at least %1s products from manufacturer: %2s.'), $condition['c_value'], $manufacturerObject->name) . '</p>';
                                    }
                                }
                                if ($condition['multiply'] == 1) {
                                    if ($limit[$key] % $condition['c_value']) {
                                        $manufacturerObject = new Manufacturer($key, $this->context->language->id);
                                        $this->step = 0;
                                        return '<p class="alert alert-warning">' . sprintf(Tools::displayError('To place an order your cart must contain multiplied quantity (%1s) of products from manufacturer: %2s.'), $condition['c_value'], $manufacturerObject->name) . '</p>';
                                    }
                                }
                            }
                        }
                    }
                    unset($limit);
                    if ($condition['c_type'] == 7) {
                        foreach ($this->context->cart->getProducts() AS $product) {
                            $combination = new Combination($product['id_product_attribute']);
                            foreach ($combination->getAttributesName($this->context->language->id) AS $attribute) {
                                if ($condition['c_target'] == $attribute['id_attribute']) {
                                    $limitmaxattribute = $limitmaxattribute + $product['quantity'];
                                }
                            }
                        }
                        if ($condition['c_value'] < $limitmaxattribute) {
                            $attribute = new Attribute($condition['c_target'], $this->context->language->id);
                            $attributeGroup = new AttributeGroup($attribute->id_attribute_group);
                            $condition_target = $attributeGroup->public_name[$this->context->language->id] . ': ' . $attribute->name;
                            $this->step = 0;
                            return '<p class="alert alert-warning">' . sprintf(Tools::displayError('You reached quantity limit of products with %1s, the quantity limit is: %2s while your cart contains %3s items.'), $condition_target, $condition['c_value'], $limitmaxattribute);
                        }
                    }
                    unset($limitmaxattribute);
                }
            }
        }
        unset($limit);
        unset($limitp);
        
        
        
        
        
        
        if ($this->context->cart->getOrderTotal() <= 0) {
            return '<p class="center"><button class="button btn btn-default button-medium" name="confirmOrder" id="confirmOrder" onclick="confirmFreeOrder();" type="submit"> <span>' . Tools::displayError('I confirm my order.') . '</span></button></p>';
        }
        $return = Hook::exec('displayPayment');
        if (!$return) {
            return '<p class="warning">' . Tools::displayError('No payment method is available for use at this time. ') . '</p>';
        }
        return $return;
    }
}
?>