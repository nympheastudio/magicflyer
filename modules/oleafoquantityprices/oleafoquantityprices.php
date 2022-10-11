<?php

/**
 * ---------------------------------------------------------------------------------
 *
 * This file is part of the 'oleafoquantityprices' module feature
 * Developped for Prestashop platform.
 * You are not allowed to use it on several site
 * You are not allowed to sell || redistribute this module
 * This header must not be removed
 *
 * @category XXX
 * @author OleaCorner <contact@oleacorner.com> <www.oleacorner.com>
 * @copyright OleaCorner
 * @version 1.0
 * @license XXX
 *
 *          ---------------------------------------------------------------------------------
 *
 */
class Oleafoquantityprices extends Module
{

	protected static $_cacheGetDiscountedPrices = null;

	/* Only one entry key needed as the method is call from hook on the same page with same parameters */
	protected static $_addtocart_column_possible = false;

	protected static $_has_customer_price;

	public function __construct()
	{
		$this->name = 'oleafoquantityprices';
		$this->version = '1.8.0';
		$this->ps_versions_compliancy['min'] = '1.5.0.0';
		$this->tab = 'front_office_features';
		$this->author = 'Oleacorner';

		$this->module_key = '76a4e16adfef3db260b76ba370c12c01';
		parent::__construct();

		$this->displayName = $this->l('FO Quantity Prices');
		$this->description = $this->l('Display quantity prices in FO per product and per combination');
	}

	public function install()
	{
		if (!parent::install())
			return false;

		if (!$this->registerHook('productTab') || !$this->registerHook('productTabContent') || !$this->registerHook('productFooter') || !$this->registerHook('header') || !$this->registerHook('footer') || !$this->registerHook('displayRightColumnProduct') || !$this->registerHook('oleaqtyTopProduct'))
			return false;

		Configuration::updateValue('OLEA_FOQTY_KEEP_NOMINAL', 0);
		Configuration::updateValue('OLEA_FOQTY_AJAXINLISTING', 1);
		Configuration::updateValue('OLEA_FOQTY_DISP_PRODUCTS', 1);
		Configuration::updateValue('OLEA_FOQTY_DISP_PROD_INTAB', 0);
		Configuration::updateValue('OLEA_FOQTY_DISP_COMBINATIONS', 1);
		Configuration::updateValue('OLEA_FOQTY_DISP_COMB_INTAB', 1);
		Configuration::updateValue('OLEA_FOQTY_DISP_COMB_QTY1', 1);
		Configuration::updateValue('OLEA_FOQTY_DISP_COMB_REFERENCE', 0);
		Configuration::updateValue('OLEA_FOQTY_DISP_COMB_EAN13', 0);
		Configuration::updateValue('OLEA_FOQTY_DISP_COMB_ADDTOCART', 1);

		require_once dirname(__FILE__).'/upgrade/install-1.7.7.php';
		if (!upgrade_module_1_7_7($this))
			return false;

		require_once dirname(__FILE__).'/upgrade/install-1.7.8.php';
		if (!upgrade_module_1_7_8($this))
			return false;

		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;

		if (!$this->unregisterHook('productTab') || !$this->unregisterHook('productTabContent') || !$this->unregisterHook('productFooter') || !$this->unregisterHook('header') || !$this->unregisterHook('footer'))
			return false;

		if (!Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'foqty_caracteristicname'))
			return false;

		if (!Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'foqty_caracteristicvalue'))
			return false;

		return true;
	}

	/* --------------- Core functions ------------------------------------------- */
	private function _getQuantityDiscounts($id_product, $customer, $cookie)
	{
		$discounts_nb = array();
		if (version_compare('1.5', _PS_VERSION_) <= 0)
		{
			// $context = Context::getContext();
			$id_customer = (isset($this->context->customer) ? (int)$this->context->customer->id : 0);
			$id_group = (isset($this->context->customer) ? $this->context->customer->id_default_group : _PS_DEFAULT_CUSTOMER_GROUP_);
			$id_country = (int)$id_customer ? Customer::getCurrentCountry($id_customer) : Configuration::get('PS_COUNTRY_DEFAULT');
			$id_currency = (int)$this->context->cookie->id_currency;
			$id_shop = $this->context->shop->id;

			$quantity_discounts = SpecificPrice::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group, null, true, $id_customer);
			$discounts_nb = array();
			foreach ($quantity_discounts as $quantity_info)
				$discounts_nb[$quantity_info['from_quantity']] = $quantity_info['from_quantity'];
		}
		elseif (version_compare('1.4', _PS_VERSION_) <= 0)
		{
			if ($customer->id == 0)
			{
				$id_country = Country::getDefaultCountryId();
				$id_group = 1;
			}
			else
			{
				$id_country = Customer::getCurrentCountry($customer->id);
				$id_group = $customer->id_default_group;
			}
			$id_shop = Shop::getCurrentShop();
			$id_currency = $cookie->id_currency;

			$quantity_discounts = SpecificPrice::getQuantityDiscounts($id_product, $id_shop, $id_currency, $id_country, $id_group);
			$discounts_nb = array();
			foreach ($quantity_discounts as $quantity_info)
				$discounts_nb[$quantity_info['from_quantity']] = $quantity_info['from_quantity'];
		}
		else
		{
			$sqlReq = 'SELECT dc.quantity
						FROM `'._DB_PREFIX_.'discount_quantity` dc
						WHERE id_product = '.(int)$id_product;
			$res = Db::getInstance()->ExecuteS($sqlReq);
			foreach ($res as $v)
				$discounts_nb[$v['quantity']] = $v['quantity'];
		}

		asort($discounts_nb);
		return $discounts_nb;
	}

	private static function _get_attributes_infos($all_attributes_ids)
	{
		if (!is_array($all_attributes_ids) || count($all_attributes_ids) == 0)
			return array();
		$sqlReq = 'SELECT a.id_attribute, CONCAT(LPAD(ag.position,6,"0"), "_", LPAD(a.position,6,"0")) as position_key, a.color as color
	               FROM `'._DB_PREFIX_.'attribute` a
	               LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON (a.id_attribute_group = ag.id_attribute_group)
	               WHERE id_attribute IN ('.implode(',', $all_attributes_ids).')';
		$res = Db::getInstance()->ExecuteS($sqlReq);

		$retour = array();
		foreach ($res as $info)
			$retour[$info['id_attribute']] = $info;
		return $retour;
	}

	private static function _get_attributes_position_keys($all_attributes_ids)
	{
		if (!is_array($all_attributes_ids) || count($all_attributes_ids) == 0)
			return array();
		$sqlReq = 'SELECT a.id_attribute, CONCAT(LPAD(ag.position,6,"0"), "_", LPAD(a.position,6,"0")) as position_key
	               FROM `'._DB_PREFIX_.'attribute` a
	               LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON (a.id_attribute_group = ag.id_attribute_group)
	               WHERE id_attribute IN ('.implode(',', $all_attributes_ids).')';
		$res = Db::getInstance()->ExecuteS($sqlReq);

		$retour = array();
		foreach ($res as $info)
			$retour[$info['id_attribute']] = $info['position_key'];
		return $retour;
	}

	public static function _getAttributeImageAssociations($id_product_attributes)
	{
		if (!is_array($id_product_attributes) || count($id_product_attributes) == 0)
			return array();

		$combination_images = array();
		$data = Db::getInstance()->executeS('
			SELECT `id_image`, `id_product_attribute`
			FROM `'._DB_PREFIX_.'product_attribute_image`
			WHERE `id_product_attribute` IN (0, '.implode(',', $id_product_attributes).')');
		foreach ($data as $row)
			if (!isset($combination_images[$row['id_product_attribute']]))
				$combination_images[$row['id_product_attribute']] = (int)$row['id_image'];
		return $combination_images;
	}

	private function _getDiscountedPrices($id_product, $cookie, $is_for_ajax = false)
	{
		$is_15 = (version_compare('1.5', _PS_VERSION_) <= 0) ? true : false;

		if (self::$_cacheGetDiscountedPrices == null)
		{

			$id_cart = 0;
			if (version_compare(_PS_VERSION_, '1.4') >= 0)
				$id_address_vat = null;
			else
				$id_address_vat = $cookie->id_address_delivery;

			$customer = new Customer((int)$cookie->id_customer);
			if (Product::getTaxCalculationMethod($customer->id) == PS_TAX_EXC)
				$withTaxes = false;
			else
				$withTaxes = (Tax::excludeTaxeOption() ? false : true);

			$discounts_nb = $this->_getQuantityDiscounts($id_product, $customer, $cookie);

			$product_qty_prices = array();
			$combinations_qty_prices = array();
			$sort_combinations_qty_prices = array();
			$product_qty_prices_of_group = array();
			$specific_price_output = array();
			$has_customer_price = false;

			$product = new Product($id_product, true, $cookie->id_lang);

			if (!(int)Configuration::get('OLEA_FOQTY_DISP_COMB_VERTICAL') || $is_for_ajax)
			{
				/* Classical display */
				$attributeCombinations = ($is_15) ? $product->getAttributeCombinations($cookie->id_lang) : $product->getAttributeCombinaisons($cookie->id_lang);
			}
			else
			{
				if ($is_15)
					$attributeCombinations = $product->getAttributeCombinationsById($product->getDefaultIdProductAttribute(), $cookie->id_lang);
				else
					$attributeCombinations = self::getAttributeCombinaisonsById($id_product, Product::getDefaultAttribute($id_product), $cookie->id_lang);
			}

			if (version_compare('1.5', _PS_VERSION_) <= 0)
			{
				$group = new Group($customer->id_default_group);
				$group_price = $group->show_prices;
			}
			else
				$group_price = true;

			if ($product->show_price && $group_price)
			{
				if (count($attributeCombinations))
				{
					if (/*count($discounts_nb) AND*/ (bool)Configuration::get('OLEA_FOQTY_DISP_COMB_QTY1'))
					{
						$discounts_nb[1] = 1;
						asort($discounts_nb);
					}
					if (count($discounts_nb))
					{
						// Features
						$featurescombi_values = array();
						if (version_compare('1.5', _PS_VERSION_) <= 0)
							$tmpres = Hook::exec('oleafeaturescombiGetValues', array(
								'oleafcb_id_product' => $id_product,
								'oleafcb_values' => &$featurescombi_values,
								'oleafcb_id_lang' => $cookie->id_lang
							));
						else
							$tmpres = Module::hookExec('oleafeaturescombiGetValues', array(
								'oleafcb_id_product' => $id_product,
								'oleafcb_values' => &$featurescombi_values,
								'oleafcb_id_lang' => $cookie->id_lang
							));
						$oleafcb_featuresnames = array();
						$oleafcb_featuresvalues = array();
						foreach ($featurescombi_values as $info)
						{
							if ((int)$info['id_feature'])
							{
								$oleafcb_featuresnames[$info['id_feature']] = $info['feature_name'];
								$oleafcb_featuresvalues[$info['id_feature']][$info['id_product_attribute']] = $info['value'];
							}
						}

						if (version_compare('1.5', _PS_VERSION_) <= 0)
							$id_lang = Context::getContext()->language->id;
						else
						{
							// global $cookie;
							// $id_lang = $cookie->id_lang;
							$id_lang = 1;
						}

						$attributes_groups = $product->getAttributesGroups($id_lang);
						$groups_names = array();
						$groups_infos = array();
						$all_attributes_ids = array();
						$position = 0;
						foreach ($attributes_groups as $info)
						{
							$groups_names[$info['id_attribute_group']] = $info['public_group_name'];
							if (!isset($groups_infos[$info['id_attribute_group']]))
							{
								$info['position'] = $position;
								$groups_infos[$info['id_attribute_group']] = $info;
								$position += 1;
							}
						}

						foreach ($attributeCombinations as $attributeCombination)
						{
							$id_product_attribute = $attributeCombination['id_product_attribute'];
							if (!array_key_exists($id_product_attribute, $combinations_qty_prices))
							{
								$combinations_qty_prices[$id_product_attribute]['id_product_attribute'] = $id_product_attribute;
								$combinations_qty_prices[$id_product_attribute]['reference'] = $attributeCombination['reference'];
								$combinations_qty_prices[$id_product_attribute]['ean13'] = $attributeCombination['ean13'];
								$combinations_qty_prices[$id_product_attribute]['minimal_quantity'] = $attributeCombination['minimal_quantity'];
								foreach ($discounts_nb as $qty)
								{
									if ((int)Configuration::get('OLEA_FOQTY_DISP_COMB_VERTICAL') && !$is_for_ajax)
									{
										$combinations_qty_prices[$id_product_attribute]['prices'][$qty]['unit_ttc'] = Product::getPriceStatic($product->id, true, $id_product_attribute, 2, null, false, true, $qty, false, $customer->id, 0/*$id_cart*/, $id_address_vat);
										$combinations_qty_prices[$id_product_attribute]['prices'][$qty]['unit_ht'] = Product::getPriceStatic($product->id, false, $id_product_attribute, 2, null, false, true, $qty, false, $customer->id, 0/*$id_cart*/, $id_address_vat);
										$combinations_qty_prices[$id_product_attribute]['prices'][$qty]['ttc'] = round($combinations_qty_prices[$id_product_attribute]['prices'][$qty]['unit_ttc'] * $qty, 2);
										$combinations_qty_prices[$id_product_attribute]['prices'][$qty]['ht'] = round($combinations_qty_prices[$id_product_attribute]['prices'][$qty]['unit_ht'] * $qty, 2);
									}
									else
									{
										$combinations_qty_prices[$id_product_attribute]['prices'][$qty] = Product::getPriceStatic($product->id, $withTaxes, $id_product_attribute, 2, null, false, true, $qty, false, $customer->id, 0/*$id_cart*/, $id_address_vat, $specific_price_output);
										$has_customer_price = $has_customer_price || (isset($specific_price_output['id_customer']) && (int)$specific_price_output['id_customer']);
										if ($customer->id)
											$combinations_qty_prices[$id_product_attribute]['prices_of_group'][$qty] = Product::getPriceStatic($product->id, $withTaxes, $id_product_attribute, 2, null, false, true, $qty, false, $customer->id, 0/*$id_cart*/, $id_address_vat, $specific_price_output, true/*eco*/, true/*gp_reduc*/, null/*context*/, false/*no custprice*/);
									}
								}
							}

							// $combinations_qty_prices[$id_product_attribute]['attributes'][$groups_names[$attributeCombination['id_attribute_group']]] = $attributeCombination['attribute_name'];
							$combinations_qty_prices[$id_product_attribute]['attributes'][$groups_infos[$attributeCombination['id_attribute_group']]['position']] = array(
								'group' => $groups_names[$attributeCombination['id_attribute_group']],
								'name' => $attributeCombination['attribute_name'],
								'is_color_group' => $attributeCombination['is_color_group'],
								'id_attribute' => $attributeCombination['id_attribute']
							);
							ksort($combinations_qty_prices[$id_product_attribute]['attributes']);

							$combinations_qty_prices[$id_product_attribute]['attributes_ids'][$groups_infos[$attributeCombination['id_attribute_group']]['position']] = $attributeCombination['id_attribute'];
							ksort($combinations_qty_prices[$id_product_attribute]['attributes_ids']);
							$all_attributes_ids[] = $attributeCombination['id_attribute'];

							$combinations_qty_prices[$id_product_attribute]['features'] = array();
							foreach ($oleafcb_featuresnames as $id_feature => $featurename)
								$combinations_qty_prices[$id_product_attribute]['features'][$featurename] = isset($oleafcb_featuresvalues[$id_feature][$id_product_attribute]) ? $oleafcb_featuresvalues[$id_feature][$id_product_attribute] : '';
						}

						$attributes_images = self::_getAttributeImageAssociations(array_keys($combinations_qty_prices));
						$attributes_infos = self::_get_attributes_infos($all_attributes_ids);

						foreach ($combinations_qty_prices as $product_attribute_info)
						{
							$key = '';
							foreach ($product_attribute_info['attributes_ids'] as $id)
								$key .= $attributes_infos[$id]['position_key'].'_';
							foreach ($product_attribute_info['attributes'] as &$info)
								if ($info['is_color_group'])
									$info['color'] = $attributes_infos[$id]['color'];
							$product_attribute_info['id_image'] = isset($attributes_images[$product_attribute_info['id_product_attribute']]) ? $attributes_images[$product_attribute_info['id_product_attribute']] : 0;
							$sort_combinations_qty_prices[$key] = $product_attribute_info;
						}
						ksort($sort_combinations_qty_prices);
					}
				}
				else
				{
					if (count($discounts_nb) || $is_for_ajax)
					{
						$discounts_nb[1] = 1;
						asort($discounts_nb);
					}
					foreach ($discounts_nb as $qty)
					{
						$product_qty_prices[$qty] = Product::getPriceStatic($product->id, $withTaxes, 0, 2, null, false, true, $qty, false, $customer->id, 0/*$id_cart*/, $id_address_vat, $specific_price_output);
						$has_customer_price = $has_customer_price || (isset($specific_price_output['id_customer']) && (int)$specific_price_output['id_customer']);
						if ($customer->id)
							$product_qty_prices_of_group[$qty] = Product::getPriceStatic($product->id, $withTaxes, 0, 2, null, false, true, $qty, false, $customer->id, 0/*$id_cart*/, $id_address_vat, $specific_price_output, true/*eco*/, true/*gp_reduc*/, null/*context*/, false/*no custprice*/);
					}
				}
			}

			self::$_cacheGetDiscountedPrices = array();
//			self::$_cacheGetDiscountedPrices['product_qty_prices'] = ((int)Configuration::get('OLEA_FOQTY_DISP_PRODUCTS')) ? $product_qty_prices : array();
//			self::$_cacheGetDiscountedPrices['combinations_qty_prices'] = ((int)Configuration::get('OLEA_FOQTY_DISP_COMBINATIONS')) ? $sort_combinations_qty_prices : array();
			self::$_cacheGetDiscountedPrices['product_qty_prices'] = $product_qty_prices;
			self::$_cacheGetDiscountedPrices['combinations_qty_prices'] = $sort_combinations_qty_prices;
			self::$_cacheGetDiscountedPrices['product_qty_prices_of_group'] = $product_qty_prices_of_group;
			// self::$_cacheGetDiscountedPrices = array('product_qty_prices'=> $product_qty_prices, 'combinations_qty_prices'=>$combinations_qty_prices);
			self::$_has_customer_price = $has_customer_price;

			if (version_compare('1.5', _PS_VERSION_) <= 0)
				self::$_addtocart_column_possible = ($product->available_for_order && !(bool)Configuration::get('PS_CATALOG_MODE'));
			else
				self::$_addtocart_column_possible = (!(bool)Configuration::get('PS_CATALOG_MODE'));
		}

		return self::$_cacheGetDiscountedPrices;
	}

	public static function isProductDisplayIsAllowed($id_product)
	{
		static $_cache = null;

		if (!isset($_cache[$id_product]))
		{
			$_cache[$id_product] = (count(array_intersect(Product::getProductCategories((int)$id_product), array_diff(explode('_', Configuration::get('OLEA_FOQTY_EXCLUDEDCATS')), array(
				0
			)))) > 0) ? false : true;
		}

		return $_cache[$id_product];
	}

	/* --------------- HOOKS ------------------------------------------- */
	public function hookHeader($params)
	{
		if (version_compare('1.5', _PS_VERSION_) <= 0)
		{
			$this->context->controller->addCSS(($this->_path).'views/css/foqtyprices.css', 'all');
			$this->context->controller->addJs(_PS_JS_DIR_.'tools.js');
			$this->context->controller->addJs(($this->_path).'views/js/foqtyprices.js');

			if (version_compare(_PS_VERSION_, '1.6') < 0)
			{
				$this->context->controller->addCSS(_PS_CSS_DIR_.'jquery.fancybox-1.3.4.css', 'screen');
				$this->context->controller->addJqueryPlugin(array(
					'fancybox'
				));
				// $this->context->controller->addJs(($this->_path).'nyromodal/jquery.nyroModal.custom.min.js');
				// $this->context->controller->addJs(($this->_path).'nyromodal/jquery.nyroModal-ie6.min.js');
				// $this->context->controller->addCSS(($this->_path).'nyromodal/nyroModal.css', 'all');
			}
		}
		else
		{
			Tools::addCSS(($this->_path).'views/css/foqtyprices.css', 'all');
			Tools::addJS(_THEME_JS_DIR_.'tools.js');
			Tools::addJS(($this->_path).'views/js/foqtyprices.js');

			Tools::addJs(($this->_path).'nyromodal/jquery.nyroModal.custom.min.js');
			Tools::addJs(($this->_path).'nyromodal/jquery.nyroModal-ie6.min.js');
			Tools::addCSS(($this->_path).'nyromodal/nyroModal.css', 'all');
		}
	}

	public function hookDisplayShoppingCart($params)
	{
		$this->smarty->assign(array(
			'oleafoqty_isforajax' => Tools::getValue('ajax'),
			'oleafoqty_multi_of_minimal' => (int)Configuration::get('OLEA_FOQTY_MULTI_OF_MINIMAL'),
		));
		return $this->display(__FILE__, 'shopping_cart.tpl');
	}

	public function hookFooter($params)
	{
		$this->smarty->assign(array(
			'oleaqty_popupinlist' => (int)Configuration::get('OLEA_FOQTY_AJAXINLISTING'),
			'oleaqty_post15' => (version_compare('1.5', _PS_VERSION_) <= 0) ? 1 : 0,
			'oleaqty_istpl16' => (version_compare('1.6', _PS_VERSION_) <= 0) ? 1 : 0,
			'oleaQtyPricesPath' => $this->_path,
			'oleaQtyPricesWidth' => 800,
			'currencySign' => $this->context->currency->sign,
			'currencyRate' => $this->context->currency->conversion_rate,
			'currencyFormat' => $this->context->currency->format,
			'currencyBlank' => $this->context->currency->blank
		));

		return $this->display(__FILE__, 'footer.tpl');
	}

	public function hookProductTab($params)
	{
		$id_product = (int)Tools::getValue('id_product');
		if ($id_product == 0 || !self::isProductDisplayIsAllowed($id_product))
			return '';
		$qty_prices = self::_getDiscountedPrices($id_product, $this->context->cookie);
		$products_prices = ((bool)Configuration::get('OLEA_FOQTY_DISP_PROD_INTAB') && (bool)Configuration::get('OLEA_FOQTY_DISP_PRODUCTS')) ? $qty_prices['product_qty_prices'] : array();
		$combinations_prices = ((bool)Configuration::get('OLEA_FOQTY_DISP_COMB_INTAB') && (bool)Configuration::get('OLEA_FOQTY_DISP_COMBINATIONS')) ? $qty_prices['combinations_qty_prices'] : array();

		$this->smarty->assign(array(
			'olea_has_qty_prices' => (count($products_prices) || count($combinations_prices)) ? 1 : 0,
			'oleaqty_istpl16' => (version_compare('1.6', _PS_VERSION_) <= 0) ? 1 : 0
		));

		return $this->display(__FILE__, 'product_tab.tpl');
	}

	public function hookProductTabContent($params)
	{
		$id_product = (int)Tools::getValue('id_product');
		if ($id_product == 0 || !self::isProductDisplayIsAllowed($id_product))
			return '';

		$qty_prices = self::_getDiscountedPrices($id_product, $this->context->cookie);

		$products_prices = ((bool)Configuration::get('OLEA_FOQTY_DISP_PROD_INTAB') && (bool)Configuration::get('OLEA_FOQTY_DISP_PRODUCTS')) ? $qty_prices['product_qty_prices'] : array();
		$combinations_prices = ((bool)Configuration::get('OLEA_FOQTY_DISP_COMB_INTAB') && (bool)Configuration::get('OLEA_FOQTY_DISP_COMBINATIONS')) ? $qty_prices['combinations_qty_prices'] : array();

		$product = new Product($id_product);
		$this->_common_smarty_assign($id_product, $products_prices, $combinations_prices, $qty_prices, $product);

		if (!(int)Configuration::get('OLEA_FOQTY_DISP_COMB_VERTICAL'))
		{
			/* classical display */
			$this->smarty->assign('prices_info_tpl_path', './prices_info.tpl');
		}
		else
			$this->smarty->assign('prices_info_tpl_path', './prices_info_vertical.tpl');

		return $this->display(__FILE__, 'product_tab_content.tpl');
	}

	public function hookProductFooter($params)
	{
		$id_product = (int)Tools::getValue('id_product');
		if ($id_product == 0 || !self::isProductDisplayIsAllowed($id_product))
			return '';

		$qty_prices = self::_getDiscountedPrices($id_product, $this->context->cookie);

		if (!(int)CONFIGURATION::get('OLEA_FOQTY_KEEP_NOMINAL'))
			Context::getContext()->smarty->assign('quantity_discounts', null); // ERASE smarty variable

		$products_prices = ((bool)Configuration::get('OLEA_FOQTY_DISP_PROD_INFOOTER') && (bool)Configuration::get('OLEA_FOQTY_DISP_PRODUCTS')) ? $qty_prices['product_qty_prices'] : array();
		$combinations_prices = ((bool)Configuration::get('OLEA_FOQTY_DISP_COMB_INFOOTER') && (bool)Configuration::get('OLEA_FOQTY_DISP_COMBINATIONS')) ? $qty_prices['combinations_qty_prices'] : array();

		$product = new Product($id_product);
		$this->_common_smarty_assign($id_product, $products_prices, $combinations_prices, $qty_prices, $product);

		if (!(int)Configuration::get('OLEA_FOQTY_DISP_COMB_VERTICAL'))
		{
			/* classical display */
			$this->smarty->assign('prices_info_tpl_path', './prices_info.tpl');
		}
		else
			$this->smarty->assign('prices_info_tpl_path', './prices_info_vertical.tpl');

		return $this->display(__FILE__, 'product_footer.tpl');
	}

	public function hookDisplayRightColumnProduct($params)
	{
		if ((int)Configuration::get('OLEA_FOQTY_DISP_COMB_VERTICAL'))
		{
			/* classical display */
			return '';
		}

		$id_product = (int)Tools::getValue('id_product');
		if ($id_product == 0 || !self::isProductDisplayIsAllowed($id_product))
			return '';

		$qty_prices = self::_getDiscountedPrices($id_product, $this->context->cookie);

		if (!(int)CONFIGURATION::get('OLEA_FOQTY_KEEP_NOMINAL'))
			Context::getContext()->smarty->assign('quantity_discounts', null); // ERASE smarty variable

		$products_prices = ((bool)Configuration::get('OLEA_FOQTY_DISP_PROD_INCENTER') && (bool)Configuration::get('OLEA_FOQTY_DISP_PRODUCTS')) ? $qty_prices['product_qty_prices'] : array();
		$combinations_prices = ((bool)Configuration::get('OLEA_FOQTY_DISP_COMB_INCENTER') && (bool)Configuration::get('OLEA_FOQTY_DISP_COMBINATIONS')) ? $qty_prices['combinations_qty_prices'] : array();

		$product = new Product($id_product);
		$this->_common_smarty_assign($id_product, $products_prices, $combinations_prices, $qty_prices, $product);
		$product = new Product($id_product, false, $this->context->cookie->id_lang);
		$this->smarty->assign('oleaqty_defaultcombi', (int)$product->getDefaultIdProductAttribute());

		return $this->display(__FILE__, 'product_columnright.tpl');
	}

	public function hookOleaqtyTopProduct($params)
	{
		$id_product = (int)Tools::getValue('id_product');
		if ($id_product == 0 || !self::isProductDisplayIsAllowed($id_product))
			return '';

		$qty_prices = self::_getDiscountedPrices($id_product, $this->context->cookie);
		if (!(int)CONFIGURATION::get('OLEA_FOQTY_KEEP_NOMINAL'))
			Context::getContext()->smarty->assign('quantity_discounts', null); // ERASE smarty variable

		$products_prices = ((bool)Configuration::get('OLEA_FOQTY_DISP_PROD_INHOOK') && (bool)Configuration::get('OLEA_FOQTY_DISP_PRODUCTS')) ? $qty_prices['product_qty_prices'] : array();
		$combinations_prices = ((bool)Configuration::get('OLEA_FOQTY_DISP_COMB_INHOOK') && (bool)Configuration::get('OLEA_FOQTY_DISP_COMBINATIONS')) ? $qty_prices['combinations_qty_prices'] : array();

		$product = new Product($id_product);
		$this->_common_smarty_assign($id_product, $products_prices, $combinations_prices, $qty_prices, $product);

		if (!(int)Configuration::get('OLEA_FOQTY_DISP_COMB_VERTICAL'))
		{
			/* classical display */
			$this->smarty->assign('prices_info_tpl_path', './prices_info.tpl');
		}
		else
			$this->smarty->assign('prices_info_tpl_path', './prices_info_vertical.tpl');

		return $this->display(__FILE__, 'product_footer.tpl');
	}

	private function _common_smarty_assign($id_product, $products_prices, $combinations_prices, $qty_prices, $product)
	{
		$this->smarty->assign(array(
			'olea_foqty_isforajax' => 0,
			'oleaqty_post15' => (version_compare('1.5', _PS_VERSION_) <= 0) ? 1 : 0,
			'oleaqty_istpl16' => (version_compare('1.6', _PS_VERSION_) <= 0) ? 1 : 0,
			'olea_has_qty_prices' => (count($products_prices) || count($combinations_prices)) ? 1 : 0,
			'olea_product_qty_prices' => $products_prices,
			'olea_combinations_qty_prices' => $combinations_prices,
			'olea_display_combination_images' => (int)Configuration::get('OLEA_FOQTY_DISP_COMB_IMAGES'),
			'olea_display_combination_reference' => (int)Configuration::get('OLEA_FOQTY_DISP_COMB_REFERENCE'),
			'olea_display_combination_ean13' => (int)Configuration::get('OLEA_FOQTY_DISP_COMB_EAN13'),
			'olea_display_product_addtocart' => (int)((bool)Configuration::get('OLEA_FOQTY_DISP_PROD_ADDTOCART') && self::$_addtocart_column_possible),
			'olea_display_combination_addtocart' => (int)((bool)Configuration::get('OLEA_FOQTY_DISP_COMB_ADDTOCART') && self::$_addtocart_column_possible),
			'olea_product_qty_prices_of_group' => $qty_prices['product_qty_prices_of_group'],
			'olea_display_group_price' => self::$_has_customer_price && (int)Configuration::get('OLEA_FOQTY_DISPLAYGROUP_PRICE', 0),
			'olea_ps_qty_on_combination' => (int)Configuration::get('PS_QTY_DISCOUNT_ON_COMBINATION'),
			'olea_id_product' => $id_product,
			'oleafoqty_minimal_quantity' => $product->minimal_quantity,
			'oleafoqty_multi_of_minimal' => (int)Configuration::get('OLEA_FOQTY_MULTI_OF_MINIMAL'),
			'oleafoqty_change_price_display' => (int)Configuration::get('OLEA_FOQTY_CHANGE_PRICE_DISPLAY'),
		));

	}

	public function hookDisplayProductButtons($params)
	{
		if (0 && !(int)Configuration::get('OLEA_FOQTY_MULTI_OF_MINIMAL'))
			return '';

		$id_product = (int)Tools::getValue('id_product');
		if ($id_product == 0 || !self::isProductDisplayIsAllowed($id_product))
			return '';

		$this->smarty->assign(array(
			'olea_product' => $params['product']
		));

		$qty_prices = self::_getDiscountedPrices($id_product, $this->context->cookie);

		if (!(int)CONFIGURATION::get('OLEA_FOQTY_KEEP_NOMINAL'))
			Context::getContext()->smarty->assign('quantity_discounts', null); // ERASE smarty variable

		$products_prices = $qty_prices['product_qty_prices'];
		$combinations_prices = $qty_prices['combinations_qty_prices'];

		$product = $params['product'];
		$this->_common_smarty_assign($id_product, $products_prices, $combinations_prices, $qty_prices, $product);

		return $this->display(__FILE__, 'productbuttons.tpl');
	}

	public function hookDisplayProductPriceBlock($params)
	{
		if ($params['type'] <> 'price' || Context::getContext()->controller->php_self == 'product2' || Context::getContext()->controller->php_self == 'products-comparison' || !(bool)Configuration::get('OLEA_FOQTY_DISPLAY_BESTPRICE'))
			return;

		static $cache = array();
		$id_product = (is_array($params['product'])) ?$params['product']['id_product'] :$params['product']->id;

		if (!isset($cache[$id_product]))
		{
			$id_address_vat = Context::getContext()->cookie->id_address_delivery;

			if (Product::getTaxCalculationMethod(Context::getContext()->customer->id) == PS_TAX_EXC)
				$withTaxes = false;
			else
				$withTaxes = (Tax::excludeTaxeOption() ? false : true);
			$specific_price_output = null;
			$price = Product::getPriceStatic($id_product, $withTaxes, null, 2, null, false, true, 10000000, false, Context::getContext()->customer->id, 0/*$id_cart*/, $id_address_vat, $specific_price_output);

			if ($specific_price_output == null || (isset($specific_price_output['from_quantity']) && $specific_price_output['from_quantity'] == 1))
				$cache[$id_product] = '';
			else
			{
				Context::getContext()->smarty->assign(array('oleafoqty_bestprice' => $price, 'oleafoqty_bestquantity' => $specific_price_output['from_quantity']));
				$cache[$id_product] = Context::getContext()->smarty->fetch($this->local_path.'views/templates/hook/product_price_block.tpl');
			}
		}

		return $cache[$id_product];
	}

	/* --------------- FIN HOOKS --------------------------------------- */

	/* --------------- AJAX --------------------------------------------- */
	public function ajaxDisplayPricesTab($id_product)
	{
		$id_product = (int)$id_product;
		if ($id_product == 0)
			return '';

		$qty_prices = self::_getDiscountedPrices($id_product, $this->context->cookie, true);

		if (!(int)CONFIGURATION::get('OLEA_FOQTY_KEEP_NOMINAL'))
			Context::getContext()->smarty->assign('quantity_discounts', null); // ERASE smarty variable

		$products_prices = $qty_prices['product_qty_prices'];
		$combinations_prices = $qty_prices['combinations_qty_prices'];

		$product = new Product($id_product, false, $this->context->cookie->id_lang);

		$this->_common_smarty_assign($id_product, $products_prices, $combinations_prices, $qty_prices, $product);
		$this->smarty->assign(array(
			'oleaqty_productname' => $product->name,
			'olea_foqty_isforajax' => 1,
			'oleafoqty_ajaxidproduct' => (int)$id_product
		));

		$this->smarty->assign('prices_info_tpl_path', './prices_info.tpl');

		return $this->display(__FILE__, 'ajaxtab.tpl');
	}

	/* ---------------- DIVERS ------------------------------------------ */
	private function _displayOption($label, $helpSentence = null, $configOptionName, $html_name)
	{
		$output = '
			<label>'.$label.'</label>
				<div class="margin-form">
					<input type="radio" name="'.$html_name.'" id="'.$html_name.'_on" value="1" '.(Tools::getValue(''.$html_name.'', Configuration::get($configOptionName)) ? 'checked="checked" ' : '').'/>
					<label class="t" for="'.$html_name.'_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
					<input type="radio" name="'.$html_name.'" id="'.$html_name.'_off" value="0" '.(!Tools::getValue(''.$html_name.'', Configuration::get($configOptionName)) ? 'checked="checked" ' : '').'/>
					<label class="t" for="'.$html_name.'_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>';
		if ($helpSentence)
			$output .= '<p class="clear">'.$helpSentence.'</p>';
		$output .= '</div>';
		return $output;
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('submitFOQTYSettings'))
		{
			Configuration::updateValue('OLEA_FOQTY_EXCLUDEDCATS', implode('_', array_map('intval', (array)Tools::getValue('categoryBox'))));

			foreach (array(
				'keep_nominal' => 'OLEA_FOQTY_KEEP_NOMINAL',
				'ajax_inlisting' => 'OLEA_FOQTY_AJAXINLISTING',
				'multi_of_minimal' => 'OLEA_FOQTY_MULTI_OF_MINIMAL',
				'change_price_display' => 'OLEA_FOQTY_CHANGE_PRICE_DISPLAY',
				'display_best_price' => 'OLEA_FOQTY_DISPLAY_BESTPRICE',
				'display_group_price' => 'OLEA_FOQTY_DISPLAYGROUP_PRICE',
				'disp_product' => 'OLEA_FOQTY_DISP_PRODUCTS',
				'disp_product_intab' => 'OLEA_FOQTY_DISP_PROD_INTAB',
				'disp_product_infooter' => 'OLEA_FOQTY_DISP_PROD_INFOOTER',
				'disp_product_inhook' => 'OLEA_FOQTY_DISP_PROD_INHOOK',
				'disp_product_incenter' => 'OLEA_FOQTY_DISP_PROD_INCENTER',
				'disp_product_addtocart' => 'OLEA_FOQTY_DISP_PROD_ADDTOCART',
				'disp_combination' => 'OLEA_FOQTY_DISP_COMBINATIONS',
				'disp_combination_intab' => 'OLEA_FOQTY_DISP_COMB_INTAB',
				'disp_combination_infooter' => 'OLEA_FOQTY_DISP_COMB_INFOOTER',
				'disp_combination_inhook' => 'OLEA_FOQTY_DISP_COMB_INHOOK',
				'disp_combination_incenter' => 'OLEA_FOQTY_DISP_COMB_INCENTER',
				'disp_combination_images' => 'OLEA_FOQTY_DISP_COMB_IMAGES',
				'disp_combination_qty1' => 'OLEA_FOQTY_DISP_COMB_QTY1',
				'disp_combination_reference' => 'OLEA_FOQTY_DISP_COMB_REFERENCE',
				'disp_combination_ean13' => 'OLEA_FOQTY_DISP_COMB_EAN13',
				'disp_combination_addtocart' => 'OLEA_FOQTY_DISP_COMB_ADDTOCART',
				'disp_combination_vertical' => 'OLEA_FOQTY_DISP_COMB_VERTICAL'
			) as $html_key => $option)
			{
				$val = Tools::getValue($html_key);
				Configuration::updateValue($option, (int)$val);
			}

			$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}

		$output .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
				<legend>'.$this->l('General settings').'</legend>';
		if (version_compare('1.4', _PS_VERSION_) <= 0 && version_compare(_PS_VERSION_, '1.5') < 0)
		{
			$output .= $this->_displayOption($this->l('Keep nominal discount display'), $this->l('Keep the nominal discount display in Front Office (no effect on presta 1.3)'), 'OLEA_FOQTY_KEEP_NOMINAL', 'keep_nominal');
			$output .= '<center><input type="submit" name="submitFOQTYSettings" value="'.$this->l('Save').'" class="button" /></center>';
		}
		elseif (version_compare(_PS_VERSION_, '1.4') <= 0)
		{ // Pour 1.3
			$output .= '
			<label>'.$this->l('Nominal discount display/specific hook').'</label>
				<div class="margin-form">
					<p onClick="$(\'.displaysuppress\').toggle();return false;" style="cursor:pointer;" >'.$this->l('Click to view').'</p>
				</div>
			<div class="margin-form displaysuppress" style="display:none;" >
				<p>'.$this->l('If you want to suppress the nominal discounts block display on the product page:').'</p>
				<p>'.$this->l('- Open the file product.tpl in your theme').'</p>
				<p>'.$this->l('- Search the line (around line number 450):').'</p>
				<p>{if (isset($quantity_discounts) && count($quantity_discounts) > 0)}</p>
				<p>'.$this->l('- Replace it by :').'</p>
				<p>{if (0 AND isset($quantity_discounts) && count($quantity_discounts) > 0)}</p>
				<p>'.$this->l('- Just before this line, you can add the following hook call :').'</p>
				<p>{hook h=\'oleaqtyTopProduct\'}</p>
				 </div>';
			if (version_compare('1.5', _PS_VERSION_) <= 0 && version_compare(_PS_VERSION_, '1.6') <= 0)
			{
				$output .= '
			<label>'.$this->l('Core bug fix').'</label>
				<div class="margin-form">
					<p onClick="$(\'.bugfix1\').toggle();return false;" style="cursor:pointer;" >'.$this->l('Click to view').'</p>
				</div>
				<div class="margin-form bugfix1" style="display:none;" >
				<p>'.$this->l('Up to version 1.5.3.1, a bug in the core causes the quantity prices to not be displayed correctly').'</p>
				<p>'.$this->l('It is tracked here :').'  http://forge.prestashop.com/browse/PSCFV-7484</p>
				<p>'.$this->l('To fix it :').'</p>
				<p>'.$this->l('- Open the file /classes/Product.php').'</p>
				<p>'.$this->l('- Search the following lines located in method priceCalculation (around line number 2557:').'</p>
				<p>$cache_id = $id_product.\'-\'.$id_shop.\'-\'.$id_currency.\'-\'.$id_country.\'-\'.$id_state.\'-\'.$zipcode.\'-\'.$id_group.<br/>
			       	\'-\'.$quantity.\'-\'.$id_product_attribute.\'-\'.($use_tax?\'1\':\'0\').\'-\'.$decimals.\'-\'.($only_reduc?\'1\':\'0\').<br/>
					\'-\'.($use_reduc?\'1\':\'0\').\'-\'.$with_ecotax.\'-\'.$id_customer;</p>
				<p>'.$this->l('- Replace it by completing the last line :').'</p>
				<p>$cache_id = $id_product.\'-\'.$id_shop.\'-\'.$id_currency.\'-\'.$id_country.\'-\'.$id_state.\'-\'.$zipcode.\'-\'.$id_group.<br/>
			       	\'-\'.$quantity.\'-\'.$id_product_attribute.\'-\'.($use_tax?\'1\':\'0\').\'-\'.$decimals.\'-\'.($only_reduc?\'1\':\'0\').<br/>
					\'-\'.($use_reduc?\'1\':\'0\').\'-\'.$with_ecotax.\'-\'.$id_customer.\'-\'.(int)$use_group_reduction.\'-\'.(int)$id_cart.\'-\'.(int)$real_quantity;</p>
				</div>';

				$output .= '
			<label>'.$this->l('Core bug fix 2').'</label>
				<div class="margin-form">
					<p onClick="$(\'.bugfix2\').toggle();return false;" style="cursor:pointer;" >'.$this->l('Click to view').'</p>
				</div>
				<div class="margin-form bugfix2" style="display:none;" >
				<p>'.$this->l('Depending of your 1.5 edition, there may be an issue in front office, for which all the quantity columns of combination display the same prices').'</p>
				<p>'.$this->l('It is tracked here :').'  http://www.prestashop.com/forums/index.php?/topic/240677-probleme-remise-quantite-et-declinaisons/page__view__findpost__p__1204700</p>
				<p>'.$this->l('To fix it :').'</p>
				<p>'.$this->l('- Open the file /classes/SpecificPrice.php').'</p>
				<p>'.$this->l('- Search the following lines located in method getSpecificPrice():').'</p>
				<p>(($real_quantity != 0 && !Configuration::get(\'PS_QTY_DISCOUNT_ON_COMBINATION\')) ? \' AND IF(`from_quantity` > 1, `from_quantity`, 0) <= IF(id_product_attribute=0,\'.(int)$quantity.\' ,\'.(int)$real_quantity.\')\' : \'AND `from_quantity` <= \'.(int)$real_quantity).\'</p>
				<p>'.$this->l('- Modify it by replacing the $quantity by $real_quantity :').'</p>
				<p>(($real_quantity != 0 && !Configuration::get(\'PS_QTY_DISCOUNT_ON_COMBINATION\')) ? \' AND IF(`from_quantity` > 1, `from_quantity`, 0) <= IF(id_product_attribute=0,\'.(int)/*$quantity*/ ((int)$id_cart ? (int)$quantity : (int)$real_quantity ).\' ,\'.(int)$real_quantity.\')\' : \'AND `from_quantity` <= \'.(int)$real_quantity).\'</p>
				</div>';
			}
		}
		$output .= $this->_displayOption($this->l('Ajax AddtoCart in list'), $this->l('In products listings in product, propose or not the ajax popup for adding to cart'), 'OLEA_FOQTY_AJAXINLISTING', 'ajax_inlisting');
		$output .= $this->_displayOption($this->l('Display group prices'), $this->l('In all table prices, display or not the price, as stroked, for the group of the customer'), 'OLEA_FOQTY_DISPLAYGROUP_PRICE', 'display_group_price');
		$output .= $this->_displayOption($this->l('Multiple of minimal quantity'), $this->l('In table prices, manage the quantities as multiple of minimal quantity'), 'OLEA_FOQTY_MULTI_OF_MINIMAL', 'multi_of_minimal');
		$output .= $this->_displayOption($this->l('Change price display'), $this->l('In product page, change the global price display when quantity is over a quantity discount'), 'OLEA_FOQTY_CHANGE_PRICE_DISPLAY', 'change_price_display');
		$output .= $this->_displayOption($this->l('Display best quantity price'), $this->l('In products list pages, display the best quantity discount price (of default combination for product with combination)'), 'OLEA_FOQTY_DISPLAY_BESTPRICE', 'display_best_price');
		if (version_compare('1.5', _PS_VERSION_) <= 0)
			$output .= $this->_displayOption($this->l('Keep nominal discount display'), $this->l('Keep the nominal discount display in Front Office'), 'OLEA_FOQTY_KEEP_NOMINAL', 'keep_nominal');
		$output .= '<center><input type="submit" name="submitFOQTYSettings" value="'.$this->l('Save').'" class="button" /></center>';

		$output .= '
				</fieldset><p class="clear"></p>';

		$output .= '
				<fieldset>
				<legend>'.$this->l('Products settings').'</legend>';
		$output .= $this->_displayOption($this->l('Display product prices'), $this->l('Display products quantity prices'), 'OLEA_FOQTY_DISP_PRODUCTS', 'disp_product');
		$output .= $this->_displayOption($this->l('Display product prices in tab'), $this->l('Display products quantity prices in product tabs'), 'OLEA_FOQTY_DISP_PROD_INTAB', 'disp_product_intab');
		$output .= $this->_displayOption($this->l('Display product prices in product footer'), $this->l('Display products quantity prices in product footer'), 'OLEA_FOQTY_DISP_PROD_INFOOTER', 'disp_product_infooter');
		$output .= $this->_displayOption($this->l('Display product prices in specific hook'), $this->l('Display products quantity prices in specific hook of product page').'<br/>'.$this->l('To use it, place the following code where you want in your product page tpl:')."<br/>{hook h='oleaqtyTopProduct'}", 'OLEA_FOQTY_DISP_PROD_INHOOK', 'disp_product_inhook');
		$output .= $this->_displayOption($this->l('Display product prices in center'), $this->l('Display products quantity prices as vertical array in product page center'), 'OLEA_FOQTY_DISP_PROD_INCENTER', 'disp_product_incenter');
		$output .= $this->_displayOption($this->l('Display add to car column'), $this->l('Display add to card colum in the prices quantity table'), 'OLEA_FOQTY_DISP_PROD_ADDTOCART', 'disp_product_addtocart');

		$output .= '<center><input type="submit" name="submitFOQTYSettings" value="'.$this->l('Save').'" class="button" /></center>
				</fieldset><p class="clear"></p><fieldset>
				<legend>'.$this->l('Combinations settings').'</legend>';
		$output .= $this->_displayOption($this->l('Display combination prices'), $this->l('Display combination quantity prices'), 'OLEA_FOQTY_DISP_COMBINATIONS', 'disp_combination');
		$output .= $this->_displayOption($this->l('Display combination prices in tab'), $this->l('Display combination quantity prices in product tab'), 'OLEA_FOQTY_DISP_COMB_INTAB', 'disp_combination_intab');
		$output .= $this->_displayOption($this->l('Display combination prices in product footer'), $this->l('Display combination quantity prices in product footer'), 'OLEA_FOQTY_DISP_COMB_INFOOTER', 'disp_combination_infooter');
		$output .= $this->_displayOption($this->l('Display combination prices in specific hook'), $this->l('Display combination quantity prices in specific hook of product page').'<br/>'.$this->l('To use it, place the following code where you want in your product page tpl:')."<br/>{hook h='oleaqtyTopProduct'}", 'OLEA_FOQTY_DISP_COMB_INHOOK', 'disp_combination_inhook');
		$output .= $this->_displayOption($this->l('Display combination prices in center'), $this->l('Display combination quantity prices as vertical array in product page center').'<br/>'.$this->l('Only the default combination prices are displayed'), 'OLEA_FOQTY_DISP_COMB_INCENTER', 'disp_combination_incenter');
		$output .= $this->_displayOption($this->l('Display column image'), $this->l('Display a column with the combination image'), 'OLEA_FOQTY_DISP_COMB_IMAGES', 'disp_combination_images');
		$output .= $this->_displayOption($this->l('Display column qty=1'), $this->l('Always display the column for quantity=1'), 'OLEA_FOQTY_DISP_COMB_QTY1', 'disp_combination_qty1');
		$output .= $this->_displayOption($this->l('Display reference column'), $this->l('Display the column indicating the combination reference'), 'OLEA_FOQTY_DISP_COMB_REFERENCE', 'disp_combination_reference');
		$output .= $this->_displayOption($this->l('Display ean13 column'), $this->l('Display the column indicating the combination ean13'), 'OLEA_FOQTY_DISP_COMB_EAN13', 'disp_combination_ean13');
		$output .= $this->_displayOption($this->l('Display add to cart column'), $this->l('Display the AddToCart column'), 'OLEA_FOQTY_DISP_COMB_ADDTOCART', 'disp_combination_addtocart');
		//$output .= $this->_displayOption($this->l('Display HT/TTC/UNIT'), $this->l('Specific table display with total TTC/HT and unit price'), 'OLEA_FOQTY_DISP_COMB_VERTICAL', 'disp_combination_vertical');

		$output .= '<center><input type="submit" name="submitFOQTYSettings" value="'.$this->l('Save').'" class="button" /></center>';

		$output .= '
				</fieldset><p class="clear"></p>';

		$output .= '
				<fieldset>
				<legend>'.$this->l('Categories exclusion').'</legend>';

		$output .= '
					<div class="margin-form">
					<p style="padding:0px; margin:10px 0px 10px 0px;">'.$this->l('Categories for which the prices tables will NOT be displayed').'</p>
							<table cellspacing="0" cellpadding="0" class="table" style="width: 29.5em;">
									<tr>
										<th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \'categoryBox[]\', this.checked)" /></th>
										<th>'.$this->l('ID').'</th>
										<th>'.$this->l('Name').'</th>
									</tr>';
		$index = array();
		$categories = Category::getCategories((int)($this->context->cookie->id_lang), false);
		$categoriesNotOrdered = Category::getCategories((int)($this->context->cookie->id_lang), false, false);
		$indexedCategories = explode('_', Configuration::get('OLEA_FOQTY_EXCLUDEDCATS'));
		if (version_compare(_PS_VERSION_, '1.4') <= 0)
			$output .= $this->recurseCategoryForInclude_ps13($indexedCategories, $categories, $categories[0][1], 1);
		else
			$output .= $this->recurseCategoryForInclude_ps14((int)(Tools::getValue($this->identifier)), $indexedCategories, $categories, $categories[0][1], 1);
		$output .= '</table>
			';
		$output .= '<center><input type="submit" name="submitFOQTYSettings" value="'.$this->l('Save').'" class="button" /></center>';

		$output .= '</fieldset>
				</form>';

		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		$retour = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">';

		$cookie = Context::getContext()->cookie;
		$path = '../modules/'.$this->name.'/';
		$lang = new Language($cookie->id_lang);
		$filename = 'readme_'.$lang->iso_code.'.pdf';
		if (!file_exists(dirname(__FILE__).'/'.$filename))
		{
			$filename = 'readme_en.pdf';
			if (!file_exists(dirname(__FILE__).'/'.$filename))
				$filename = '';
		}

		$support_link = ($lang->iso_code == 'fr') ? 'https://addons.prestashop.com/fr/ecrire-au-developpeur?id_product=3129' : 'https://addons.prestashop.com/en/write-to-developper?id_product=3129';
		$retour .= '
			<p class="clear"></p>
			<fieldset class="width3"><legend>'.$this->l('Information').'</legend>
			<p>'.$this->l('Module version').' '.$this->version.' '.$this->l('developped by').' <b style="font-size:1.2em;" >Oleacorner.Com</b>
			    <a class="button" target="_blank" href="'.$support_link.'" ><b>'.$this->l('Support', __CLASS__).'</b> </a>  ';
		if ($filename != '')
			$retour .= '<b><a class="button" href="../modules/'.$this->name.'/'.$filename.'" target="_blank" >Documentation</a></b>';
		$retour .= ' <a class="button" target="_blank" href="http://addons.prestashop.com/'.(($lang->iso_code == 'fr') ? 'fr' : 'en').'/8_oleacorner" ><b>'.$this->l('Our modules', __CLASS__).'</b> </a>
		    </p></fieldset>

		</form>';
		return $retour;
	}

	public function getL($str)
	{
		switch ($str)
		{
			// case 'XXX': return $this->l('XXX');
			default:
				return $str;
		}
	}

	public static function recurseCategoryForInclude_ps14($id_obj, $indexedCategories, $categories, $current, $id_category = 1, $id_category_default = null, $has_suite = array())
	{
		static $done;
		static $irow;
		$retour = '';

		if (!isset($done[$current['infos']['id_parent']]))
			$done[$current['infos']['id_parent']] = 0;
		$done[$current['infos']['id_parent']] += 1;

		$todo = count($categories[$current['infos']['id_parent']]);
		$doneC = $done[$current['infos']['id_parent']];

		$level = $current['infos']['level_depth'] + 1;

		$retour .= '
		<tr class="'.($irow ++ % 2 ? 'alt_row' : '').'">
			<td>
				<input type="checkbox" name="categoryBox[]" class="categoryBox'.($id_category_default == $id_category ? ' id_category_default' : '').'" id="categoryBox_'.$id_category.'" value="'.$id_category.'"'.((in_array($id_category, $indexedCategories)) ? ' checked="checked"' : '').' />
			</td>
			<td>
				'.$id_category.'
			</td>
			<td>';
		for ($i = 2; $i < $level; $i ++)
			$retour .= '<img src="../img/admin/lvl_'.$has_suite[$i - 2].'.gif" alt="" style="vertical-align: middle;"/>';
		$retour .= '<img src="../img/admin/'.($level == 1 ? 'lv1.gif' : 'lv2_'.($todo == $doneC ? 'f' : 'b').'.gif').'" alt="" style="vertical-align: middle;"/> &nbsp;
			<label for="categoryBox_'.$id_category.'" class="t">'.Tools::stripslashes($current['infos']['name']).'</label></td>
		</tr>';

		if ($level > 1)
			$has_suite[] = ($todo == $doneC ? 0 : 1);
		if (isset($categories[$id_category]))
			foreach ($categories[$id_category] as $key => $row)
				if ($key != 'infos')
					$retour .= self::recurseCategoryForInclude_ps14($id_obj, $indexedCategories, $categories, $categories[$id_category][$key], $key, $id_category_default, $has_suite);

		return $retour;
	}

	public static function getAttributeCombinaisonsById($id_product, $id_product_attribute, $id_lang)
	{
		$res = Db::getInstance()->ExecuteS('
		SELECT pa.*, ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, al.`name` AS attribute_name, a.`id_attribute`, pa.`unit_price_impact`
		FROM `'._DB_PREFIX_.'product_attribute` pa
		LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
		LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
		LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
		LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)($id_lang).')
		WHERE pa.`id_product` = '.(int)($id_product).'
				AND  pa.`id_product_attribute` = '.(int)($id_product_attribute).'
				AND pa.`id_product_attribute` = '.(int)$id_product_attribute.'
				GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
				ORDER BY pa.`id_product_attribute`');

		return $res;
	}
}
