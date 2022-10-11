<?php
class Cart extends CartCore
{
    /*
    * module: lgfreeshippingzones
    * date: 2018-07-25 14:52:44
    * version: 1.2.5
    */
    public function getPackageShippingCost(
        $id_carrier = null,
        $use_tax = true,
        Country $default_country = null,
        $product_list = null,
        $id_zone = null,
        $id_shop = null
    ) {
        if (Module::isInstalled('lgfreeshippingzones')) {
            if (!$id_zone) {
                $addr = new Address($this->id_address_delivery);
                if (Validate::isLoadedObject($addr)) {
                    $id_zone = State::getIdZone($addr->id_state);
                    if (!$id_zone) {
                        $id_zone = CountryCore::getIdZone($addr->id_country);
                    }
                }
            } if (!$id_zone && $default_country) {
                $id_zone = $default_country->id_zone;
            } if ($id_zone == false) {
                $id_country = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));
                $id_zone = Db::getInstance()->getValue(
                    'SELECT id_zone FROM '._DB_PREFIX_.'country '.
                    'WHERE id_country = '.(int)$id_country
                );
            }
            if ($id_carrier == false) {
                $id_carrier = (int)(Configuration::get('PS_CARRIER_DEFAULT'));
            }
            $cache_id = 'Cart::getPackageShippingCost'.md5($id_carrier.$use_tax.$id_zone);
            include_once(_PS_MODULE_DIR_.'lgfreeshippingzones/lgfreeshippingzones.php');
            $lgfsz = new LGFreeshippingzones();
            if ((int)Configuration::get('PS_LGFREESHIPPINGZONES_TAX') == 1) {
                if (
                    $lgfsz->FSCheck(
                        $id_zone,
                        $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, null, null, false),
                        $this->getTotalWeight(),
                        $id_carrier,
                        $id_shop
                    )
                 ) {
                    $shipping_cost = 0;
                    Cache::store($cache_id, $shipping_cost);
                    return Cache::retrieve($cache_id);
                }
            } if ((int)Configuration::get('PS_LGFREESHIPPINGZONES_TAX') == 0) {
                if (
                    $lgfsz->FSCheck(
                        $id_zone,
                        $this->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING, null, null, false),
                        $this->getTotalWeight(),
                        $id_carrier,
                        $id_shop
                    )
                ) {
                    $shipping_cost = 0;
                    Cache::store($cache_id, $shipping_cost);
                    return Cache::retrieve($cache_id);
                }
            }
        }
        return parent::getPackageShippingCost($id_carrier, $use_tax, $default_country, $product_list, $id_shop);
    }
	
	
    public function getProducts($refresh = false, $id_product = false, $id_country = null)
    {
        if (!$this->id) {
            return array();
        }
        if ($this->_products !== null && !$refresh) {
            if (is_int($id_product)) {
                foreach ($this->_products as $product) {
                    if ($product['id_product'] == $id_product) {
                        return array($product);
                    }
                }
                return array();
            }
            return $this->_products;
        }
        $sql = new DbQuery();
        $sql->select('cp.`id_coeur`,cp.`id_product_attribute`, cp.`id_product`, cp.`quantity` AS cart_quantity, cp.id_shop, pl.`name`, p.`is_virtual`,
						pl.`description_short`, pl.`available_now`, pl.`available_later`, product_shop.`id_category_default`, p.`id_supplier`,
						p.`id_manufacturer`, product_shop.`on_sale`, product_shop.`ecotax`, product_shop.`additional_shipping_cost`,
						product_shop.`available_for_order`, product_shop.`price`, product_shop.`active`, product_shop.`unity`, product_shop.`unit_price_ratio`,
						stock.`quantity` AS quantity_available, p.`width`, p.`height`, p.`depth`, stock.`out_of_stock`, p.`weight`,
						p.`date_add`, p.`date_upd`, IFNULL(stock.quantity, 0) as quantity, pl.`link_rewrite`, cl.`link_rewrite` AS category,
						CONCAT(LPAD(cp.`id_product`, 10, 0), LPAD(IFNULL(cp.`id_product_attribute`, 0), 10, 0), IFNULL(cp.`id_address_delivery`, 0)) AS unique_id, cp.id_address_delivery,
						product_shop.advanced_stock_management, ps.product_supplier_reference supplier_reference');
        $sql->from('cart_product', 'cp');
        $sql->leftJoin('product', 'p', 'p.`id_product` = cp.`id_product`');
        $sql->innerJoin('product_shop', 'product_shop', '(product_shop.`id_shop` = cp.`id_shop` AND product_shop.`id_product` = p.`id_product`)');
        $sql->leftJoin('product_lang', 'pl', '
			p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$this->id_lang.Shop::addSqlRestrictionOnLang('pl', 'cp.id_shop')
        );
        $sql->leftJoin('category_lang', 'cl', '
			product_shop.`id_category_default` = cl.`id_category`
			AND cl.`id_lang` = '.(int)$this->id_lang.Shop::addSqlRestrictionOnLang('cl', 'cp.id_shop')
        );
        $sql->leftJoin('product_supplier', 'ps', 'ps.`id_product` = cp.`id_product` AND ps.`id_product_attribute` = cp.`id_product_attribute` AND ps.`id_supplier` = p.`id_supplier`');
        $sql->join(Product::sqlStock('cp', 'cp'));
        $sql->where('cp.`id_cart` = '.(int)$this->id);
        if ($id_product) {
            $sql->where('cp.`id_product` = '.(int)$id_product);
        }
        $sql->where('p.`id_product` IS NOT NULL');
        $sql->orderBy('p.`id_category_default` ASC');
        if (Customization::isFeatureActive()) {
            $sql->select('cu.`id_customization`, cu.`quantity` AS customization_quantity');
            $sql->leftJoin('customization', 'cu',
                'p.`id_product` = cu.`id_product` AND cp.`id_product_attribute` = cu.`id_product_attribute` AND cu.`id_cart` = '.(int)$this->id);
            $sql->groupBy('cp.`id_product_attribute`, cp.`id_product`, cp.`id_shop`');
        } else {
            $sql->select('NULL AS customization_quantity, NULL AS id_customization');
        }
        if (Combination::isFeatureActive()) {
            $sql->select('
				product_attribute_shop.`price` AS price_attribute, product_attribute_shop.`ecotax` AS ecotax_attr,
				IF (IFNULL(pa.`reference`, \'\') = \'\', p.`reference`, pa.`reference`) AS reference,
				(p.`weight`+ pa.`weight`) weight_attribute,
				IF (IFNULL(pa.`ean13`, \'\') = \'\', p.`ean13`, pa.`ean13`) AS ean13,
				IF (IFNULL(pa.`upc`, \'\') = \'\', p.`upc`, pa.`upc`) AS upc,
				IFNULL(product_attribute_shop.`minimal_quantity`, product_shop.`minimal_quantity`) as minimal_quantity,
				IF(product_attribute_shop.wholesale_price > 0,  product_attribute_shop.wholesale_price, product_shop.`wholesale_price`) wholesale_price
			');
            $sql->leftJoin('product_attribute', 'pa', 'pa.`id_product_attribute` = cp.`id_product_attribute`');
            $sql->leftJoin('product_attribute_shop', 'product_attribute_shop', '(product_attribute_shop.`id_shop` = cp.`id_shop` AND product_attribute_shop.`id_product_attribute` = pa.`id_product_attribute`)');
        } else {
            $sql->select(
                'p.`reference` AS reference, p.`ean13`,
				p.`upc` AS upc, product_shop.`minimal_quantity` AS minimal_quantity, product_shop.`wholesale_price` wholesale_price'
            );
        }
        $sql->select('image_shop.`id_image` id_image, il.`legend`');
        $sql->leftJoin('image_shop', 'image_shop', 'image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$this->id_shop);
        $sql->leftJoin('image_lang', 'il', 'il.`id_image` = image_shop.`id_image` AND il.`id_lang` = '.(int)$this->id_lang);
        $result = Db::getInstance()->executeS($sql);
        $products_ids = array();
        $pa_ids = array();
        if ($result) {
            foreach ($result as $key => $row) {
                $products_ids[] = $row['id_product'];
                $pa_ids[] = $row['id_product_attribute'];
                $specific_price = SpecificPrice::getSpecificPrice($row['id_product'], $this->id_shop, $this->id_currency, $id_country, $this->id_shop_group, $row['cart_quantity'], $row['id_product_attribute'], $this->id_customer, $this->id);
                if ($specific_price) {
                    $reduction_type_row = array('reduction_type' => $specific_price['reduction_type']);
                } else {
                    $reduction_type_row = array('reduction_type' => 0);
                }
                $result[$key] = array_merge($row, $reduction_type_row);
            }
        }
        Product::cacheProductsFeatures($products_ids);
        Cart::cacheSomeAttributesLists($pa_ids, $this->id_lang);
        $this->_products = array();
        if (empty($result)) {
            return array();
        }
        $ecotax_rate = (float)Tax::getProductEcotaxRate($this->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
        $apply_eco_tax = Product::$_taxCalculationMethod == PS_TAX_INC && (int)Configuration::get('PS_TAX');
        $cart_shop_context = Context::getContext()->cloneContext();
        foreach ($result as &$row) {
            if (isset($row['ecotax_attr']) && $row['ecotax_attr'] > 0) {
                $row['ecotax'] = (float)$row['ecotax_attr'];
            }
            $row['stock_quantity'] = (int)$row['quantity'];
            $row['quantity'] = (int)$row['cart_quantity'];
            if (isset($row['id_product_attribute']) && (int)$row['id_product_attribute'] && isset($row['weight_attribute'])) {
                $row['weight'] = (float)$row['weight_attribute'];
            }
            if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
                $address_id = (int)$this->id_address_invoice;
            } else {
                $address_id = (int)$row['id_address_delivery'];
            }
            if (!Address::addressExists($address_id)) {
                $address_id = null;
            }
            if ($cart_shop_context->shop->id != $row['id_shop']) {
                $cart_shop_context->shop = new Shop((int)$row['id_shop']);
            }
            $address = Address::initialize($address_id, true);
            $id_tax_rules_group = Product::getIdTaxRulesGroupByIdProduct((int)$row['id_product'], $cart_shop_context);
            $tax_calculator = TaxManagerFactory::getManager($address, $id_tax_rules_group)->getTaxCalculator();
            $row['price_without_reduction'] = Product::getPriceStatic(
                (int)$row['id_product'],
                true,
                isset($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null,
                6,
                null,
                false,
                false,
                $row['cart_quantity'],
                false,
                (int)$this->id_customer ? (int)$this->id_customer : null,
                (int)$this->id,
                $address_id,
                $specific_price_output,
                true,
                true,
                $cart_shop_context
            );
            $row['price_with_reduction'] = Product::getPriceStatic(
                (int)$row['id_product'],
                true,
                isset($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null,
                6,
                null,
                false,
                true,
                $row['cart_quantity'],
                false,
                (int)$this->id_customer ? (int)$this->id_customer : null,
                (int)$this->id,
                $address_id,
                $specific_price_output,
                true,
                true,
                $cart_shop_context
            );
            $row['price'] = $row['price_with_reduction_without_tax'] = Product::getPriceStatic(
                (int)$row['id_product'],
                false,
                isset($row['id_product_attribute']) ? (int)$row['id_product_attribute'] : null,
                6,
                null,
                false,
                true,
                $row['cart_quantity'],
                false,
                (int)$this->id_customer ? (int)$this->id_customer : null,
                (int)$this->id,
                $address_id,
                $specific_price_output,
                true,
                true,
                $cart_shop_context
            );
            switch (Configuration::get('PS_ROUND_TYPE')) {
                case Order::ROUND_TOTAL:
                    $row['total'] = $row['price_with_reduction_without_tax'] * (int)$row['cart_quantity'];
                    $row['total_wt'] = $row['price_with_reduction'] * (int)$row['cart_quantity'];
                    break;
                case Order::ROUND_LINE:
                    $row['total'] = Tools::ps_round($row['price_with_reduction_without_tax'] * (int)$row['cart_quantity'], _PS_PRICE_COMPUTE_PRECISION_);
                    $row['total_wt'] = Tools::ps_round($row['price_with_reduction'] * (int)$row['cart_quantity'], _PS_PRICE_COMPUTE_PRECISION_);
                    break;
                case Order::ROUND_ITEM:
                default:
                    $row['total'] = Tools::ps_round($row['price_with_reduction_without_tax'], _PS_PRICE_COMPUTE_PRECISION_) * (int)$row['cart_quantity'];
                    $row['total_wt'] = Tools::ps_round($row['price_with_reduction'], _PS_PRICE_COMPUTE_PRECISION_) * (int)$row['cart_quantity'];
                    break;
            }
            $row['price_wt'] = $row['price_with_reduction'];
            $row['description_short'] = Tools::nl2br($row['description_short']);
            if ($row['id_product_attribute']) {
                $row2 = Image::getBestImageAttribute($row['id_shop'], $this->id_lang, $row['id_product'], $row['id_product_attribute']);
                if ($row2) {
                    $row = array_merge($row, $row2);
                }
            }
            $row['reduction_applies'] = ($specific_price_output && (float)$specific_price_output['reduction']);
            $row['quantity_discount_applies'] = ($specific_price_output && $row['cart_quantity'] >= (int)$specific_price_output['from_quantity']);
            $row['id_image'] = Product::defineProductImage($row, $this->id_lang);
            $row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
            $row['features'] = Product::getFeaturesStatic((int)$row['id_product']);
            if (array_key_exists($row['id_product_attribute'].'-'.$this->id_lang, self::$_attributesLists)) {
                $row = array_merge($row, self::$_attributesLists[$row['id_product_attribute'].'-'.$this->id_lang]);
            }
            $row = Product::getTaxesInformations($row, $cart_shop_context);
            $this->_products[] = $row;
        }
        return $this->_products;
    }
	
	 
    public function deleteProduct($id_product, $id_product_attribute = null, $id_customization = null, $id_address_delivery = 0, $auto_add_cart_rule = true)
    {
        if (isset(self::$_nbProducts[$this->id])) {
            unset(self::$_nbProducts[$this->id]);
        }
        if (isset(self::$_totalWeight[$this->id])) {
            unset(self::$_totalWeight[$this->id]);
        }
        if ((int)$id_customization) {
            $product_total_quantity = (int)Db::getInstance()->getValue(
                'SELECT `quantity`
				FROM `'._DB_PREFIX_.'cart_product`
				WHERE `id_product` = '.(int)$id_product.'
				AND `id_cart` = '.(int)$this->id.'
				AND `id_product_attribute` = '.(int)$id_product_attribute);
            $customization_quantity = (int)Db::getInstance()->getValue('
			SELECT `quantity`
			FROM `'._DB_PREFIX_.'customization`
			WHERE `id_cart` = '.(int)$this->id.'
			AND `id_product` = '.(int)$id_product.'
			AND `id_product_attribute` = '.(int)$id_product_attribute.'
			'.((int)$id_address_delivery ? 'AND `id_address_delivery` = '.(int)$id_address_delivery : ''));
            if (!$this->_deleteCustomization((int)$id_customization, (int)$id_product, (int)$id_product_attribute, (int)$id_address_delivery)) {
                return false;
            }
            $this->_products = $this->getProducts(true);
            return ($customization_quantity == $product_total_quantity && $this->deleteProduct((int)$id_product, (int)$id_product_attribute, null, (int)$id_address_delivery));
        }
        
        $result = Db::getInstance()->getRow('
			SELECT SUM(`quantity`) AS \'quantity\'
			FROM `'._DB_PREFIX_.'customization`
			WHERE `id_cart` = '.(int)$this->id.'
			AND `id_product` = '.(int)$id_product.'
			AND `id_product_attribute` = '.(int)$id_product_attribute);
        if ($result === false) {
            return false;
        }
        
        if (Db::getInstance()->NumRows() && (int)$result['quantity']) {
            return Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.'cart_product`
				SET `quantity` = '.(int)$result['quantity'].'
				WHERE `id_cart` = '.(int)$this->id.'
				AND `id_product` = '.(int)$id_product.
                ($id_product_attribute != null ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '')
            );
        }
		if(
		
		$id_product== 117 

		
		){
			
			$result = Db::getInstance()->execute('
		DELETE FROM `'._DB_PREFIX_.'cart_product`
		WHERE `id_coeur` = '.(int)$id_product.' AND `id_cart` = '.(int)$this->id.' ');
			
		}
		
				if(

		$id_product== 228 ||
		$id_product== 309 ||
		$id_product== 310 ||
		$id_product== 174 ||
		$id_product== 183 ||
		$id_product== 253 ||
		$id_product== 184 ||
		$id_product== 173 ||
		$id_product== 311 
		
		){
			
			$result = Db::getInstance()->execute('
		DELETE FROM `'._DB_PREFIX_.'cart_product`
		WHERE  `id_cart` = '.(int)$this->id.' ');
			
		}
		
        
        $result = Db::getInstance()->execute('
		DELETE FROM `'._DB_PREFIX_.'cart_product`
		WHERE `id_product` = '.(int)$id_product.'
		'.(!is_null($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').'
		AND `id_cart` = '.(int)$this->id.'
		'.((int)$id_address_delivery ? 'AND `id_address_delivery` = '.(int)$id_address_delivery : ''));
        if ($result) {
            $return = $this->update();
            $this->_products = $this->getProducts(true);
            CartRule::autoRemoveFromCart();
            if ($auto_add_cart_rule) {
                CartRule::autoAddToCart();
            }
            return $return;
        }
        return false;
    }
	
	
	
    public function updateQty($quantity, $id_product, $id_product_attribute = null, $id_customization = false,
        $operator = 'up', $id_address_delivery = 0, Shop $shop = null, $auto_add_cart_rule = true, $id_coeur = 0)
    {
        if (!$shop) {
            $shop = Context::getContext()->shop;
        }
        if (Context::getContext()->customer->id) {
            if ($id_address_delivery == 0 && (int)$this->id_address_delivery) { // The $id_address_delivery is null, use the cart delivery address
                $id_address_delivery = $this->id_address_delivery;
            } elseif ($id_address_delivery == 0) { // The $id_address_delivery is null, get the default customer address
                $id_address_delivery = (int)Address::getFirstCustomerAddressId((int)Context::getContext()->customer->id);
            } elseif (!Customer::customerHasAddress(Context::getContext()->customer->id, $id_address_delivery)) { // The $id_address_delivery must be linked with customer
                $id_address_delivery = 0;
            }
        }
        $quantity = (int)$quantity;
        $id_product = (int)$id_product;
        $id_product_attribute = (int)$id_product_attribute;
        $product = new Product($id_product, false, Configuration::get('PS_LANG_DEFAULT'), $shop->id);
        if ($id_product_attribute) {
            $combination = new Combination((int)$id_product_attribute);
            if ($combination->id_product != $id_product) {
                return false;
            }
        }
        
        if (!empty($id_product_attribute)) {
            $minimal_quantity = (int)Attribute::getAttributeMinimalQty($id_product_attribute);
        } else {
            $minimal_quantity = (int)$product->minimal_quantity;
        }
       
        if (isset(self::$_nbProducts[$this->id])) {
            unset(self::$_nbProducts[$this->id]);
        }
        if (isset(self::$_totalWeight[$this->id])) {
            unset(self::$_totalWeight[$this->id]);
        }
        Hook::exec('actionBeforeCartUpdateQty', array(
            'cart' => $this,
            'product' => $product,
            'id_product_attribute' => $id_product_attribute,
            'id_customization' => $id_customization,
            'quantity' => $quantity,
            'operator' => $operator,
            'id_address_delivery' => $id_address_delivery,
            'shop' => $shop,
            'auto_add_cart_rule' => $auto_add_cart_rule,
        ));
        if ((int)$quantity <= 0) {
            return $this->deleteProduct($id_product, $id_product_attribute, (int)$id_customization, 0, $auto_add_cart_rule);
        } elseif (!$product->available_for_order || (Configuration::get('PS_CATALOG_MODE') && !defined('_PS_ADMIN_DIR_'))) {
            return false;
        } else {
            
            $result = $this->containsProduct($id_product, $id_product_attribute, (int)$id_customization, (int)$id_address_delivery);
            
            if ($result) {
                if ($operator == 'up') {
                    $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
							FROM '._DB_PREFIX_.'product p
							'.Product::sqlStock('p', $id_product_attribute, true, $shop).'
							WHERE p.id_product = '.$id_product;
                    $result2 = Db::getInstance()->getRow($sql);
                    $product_qty = (int)$result2['quantity'];
                    if (Pack::isPack($id_product)) {
                        $product_qty = Pack::getQuantity($id_product, $id_product_attribute);
                    }
                    $new_qty = (int)$result['quantity'] + (int)$quantity;
                    $qty = '+ '.(int)$quantity;
                    if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock'])) {
                        if ($new_qty > $product_qty) {
                            return false;
                        }
                    }
                } elseif ($operator == 'down') {
                    $qty = '- '.(int)$quantity;
                    $new_qty = (int)$result['quantity'] - (int)$quantity;
                    if ($new_qty < $minimal_quantity && $minimal_quantity > 1) {
                        return -1;
                    }
                } else {
                    return false;
                }
                
                if ($new_qty <= 0) {
                    return $this->deleteProduct((int)$id_product, (int)$id_product_attribute, (int)$id_customization, 0, $auto_add_cart_rule);
                } elseif ($new_qty < $minimal_quantity) {
                    return -1;
                } else {
                    Db::getInstance()->execute('
						UPDATE `'._DB_PREFIX_.'cart_product`
						SET `quantity` = `quantity` '.$qty.', `date_add` = NOW()
						WHERE `id_product` = '.(int)$id_product.
                        (!empty($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '').'
						AND `id_cart` = '.(int)$this->id.(Configuration::get('PS_ALLOW_MULTISHIPPING') && $this->isMultiAddressDelivery() ? ' AND `id_address_delivery` = '.(int)$id_address_delivery : '').'
						LIMIT 1'
                    );
                }
            }
            
            elseif ($operator == 'up') {
                $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
						FROM '._DB_PREFIX_.'product p
						'.Product::sqlStock('p', $id_product_attribute, true, $shop).'
						WHERE p.id_product = '.$id_product;
                $result2 = Db::getInstance()->getRow($sql);
                if (Pack::isPack($id_product)) {
                    $result2['quantity'] = Pack::getQuantity($id_product, $id_product_attribute);
                }
                if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock'])) {
                    if ((int)$quantity > $result2['quantity']) {
                        return false;
                    }
                }
                if ((int)$quantity < $minimal_quantity) {
                    return -1;
                }
				
				
				
				
                $result_add = Db::getInstance()->insert('cart_product', array(
                    'id_product' =>            (int)$id_product,
                    'id_product_attribute' =>    (int)$id_product_attribute,
                    'id_cart' =>                (int)$this->id,
                    'id_address_delivery' =>    (int)$id_address_delivery,
                    'id_shop' =>                $shop->id,
                    'quantity' =>                (int)$quantity,
                    'date_add' =>                date('Y-m-d H:i:s'),
                    'id_coeur' =>                $id_coeur
                ));
                if (!$result_add) {
                    return false;
                }
            }
        }
        $this->_products = $this->getProducts(true);
        $this->update();
        $context = Context::getContext()->cloneContext();
        $context->cart = $this;
        Cache::clean('getContextualValue_*');
        if ($auto_add_cart_rule) {
            CartRule::autoAddToCart($context);
        }
        if ($product->customizable) {
            return $this->_updateCustomizationQuantity((int)$quantity, (int)$id_customization, (int)$id_product, (int)$id_product_attribute, (int)$id_address_delivery, $operator);
        } else {
            return true;
        }
    }
	/*
    * module: oleamultipromos
    * date: 2018-08-07 14:19:56
    * version: 3.5.14
    */
    public function addCartRule($id_cart_rule)
	{
		
		$oleamultipromos_module = Module::getInstanceByName('oleamultipromos');
		if (Validate::isLoadedObject($oleamultipromos_module) && $oleamultipromos_module->active)
			if (! $oleamultipromos_module->oleaCheckCartRulesIsForCart($id_cart_rule, $this->id))
				return false;
		
		return parent::addCartRule($id_cart_rule);
	}
}
