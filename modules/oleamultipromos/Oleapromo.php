<?php


require_once (dirname(__FILE__).'/oleamultipromos.php');

/**
  * ---------------------------------------------------------------------------------
  *
  * This file is part of the 'OleaMultiPromos' module feature
  * Developped for Prestashop platform.
  * You are not allowed to use it on several site
  * You are not allowed to sell or redistribute this module
  * This header must not be removed
  *
  * @category
  * @author OleaCorner <contact@oleacorner.com> <www.oleacorner.com>
  * @copyright OleaCorner
  * @version 1.0
  *
  * ---------------------------------------------------------------------------------
  */

if (version_compare(_PS_VERSION_, '1.3') < 0) {
	define('PS_TAX_EXC', 1);
	define('PS_TAX_INC', 0);
}

class Oleapromo extends ObjectModel {

	const 		CRITERIA_TYPE_PRODUCT_NUMBER = 0;
	const		CRITERIA_TYPE_PRODUCT_AMOUNT = 1;

	const 		ATTRIBUTION_TYPE_PERCENT = 0;
	const 		ATTRIBUTION_TYPE_AMOUNT = 1;
	const 		ATTRIBUTION_TYPE_AMOUNT_PRICES_RANGE = 3;
	const 		ATTRIBUTION_TYPE_FDP = 4;
	const		ATTRIBUTION_TYPE_FINAL_PRICE = 5;

	const		ORDER_TYPE_ALLORDERS = 0;
	const		ORDER_TYPE_FIRSTORDER = 1;
	const		ORDER_TYPE_REORDER = 2;

	const		ASSOCIATION_TYPE_CATEGORY  = 0;
	const		ASSOCIATION_TYPE_PRODUCT   = 1;
	const		ASSOCIATION_TYPE_ATTRIBUTE = 2;

	const 		SENDING_METHOD_CURRENT_CART = 0;
	const 		SENDING_METHOD_MAIL         = 1;

	const		MAIL_DISCOUNT_TYPE_COMPUTED = 0;
	const		MAIL_DISCOUNT_TYPE_PERCENT  = 1;
	const		MAIL_DISCOUNT_TYPE_AMOUNT   = 2;
	const		MAIL_DISCOUNT_TYPE_FDP      = 3;


	public		$id;

	public		$name;
	public		$position;
	public 		$comments;
	public 		$discountobj_description;
	public		$global_cumulable_with_discounts=1; // for 1.4
	public		$global_cart_rule_priority=1; // For 1.5 only
	public 		$global_cart_rule_exclusion=''; // For 1.5 only
	public		$global_date_from;
	public		$global_date_to;
	public 		$global_order_type;
	public		$global_groups;
	public		$global_family;
	public		$global_allows_others=1;
	public		$global_allows_family=1;
	public 		$global_cart_amount;
	public 		$global_cart_amount_id_currency;
	public 		$global_cart_amount_withtaxes=1;
	public 		$criteria_type=Oleapromo::CRITERIA_TYPE_PRODUCT_AMOUNT;
	public 		$criteria_association_type=Oleapromo::ASSOCIATION_TYPE_CATEGORY;
	public 		$criteria_products_number=1;
	public 		$criteria_products_amount;
	public 		$criteria_id_currency;
	public 		$criteria_amount_withtaxes=1;
	public		$criteria_repetitions;
	public 		$criteria_product_with_reductions=1;
	public 		$criteria_product_with_quantity_price = 1;
	public 		$criteria_categories;
	public 		$criteria_manufacturers;
	public 		$criteria_suppliers;
	public 		$attribution_type=Oleapromo::ATTRIBUTION_TYPE_PERCENT;
	public 		$attribution_nb_impacted_products=0;
	public 		$attribution_percent;
	public 		$attribution_amount;
	public 		$attribution_id_currency;
	public 		$attribution_amount_withtaxes=1;
	public 		$attribution_maxamount;
	public 		$attribution_maxamount_id_currency;
	public 		$attribution_maxamount_withtaxes=1;
	public 		$attribution_product_with_reductions=1;
	public 		$attribution_product_with_quantity_price = 1;
	public 		$attribution_products_of_criteria=1;
	public 		$attribution_categories_of_criteria=1;
	public 		$attribution_manufacturers_of_criteria=1;
	public 		$attribution_suppliers_of_criteria=1;
	public 		$attribution_categories;
	public 		$attribution_manufacturers;
	public 		$attribution_suppliers;
	public 		$attribution_zones_fdp;
	public 		$attribution_carriers_fdp;
	public      $attribution_block_cart_line=0;
	public      $attribution_nb_cart_lines_required=0;
	public      $attribution_id_extcartrulefamily;
	public 		$sending_method = 0;
	public		$active=true;
	public		$communication_extra_right;
	public		$communication_product_footer;
	public 		$mail_discount_type = Oleapromo::MAIL_DISCOUNT_TYPE_COMPUTED;
	public 		$mail_discount_value = 0;
	public 		$mail_description; // Voucher description when sent by email
	public		$mail_message;     // message set in the email when voucher is sent
	public 		$mail_minimal = 0;
	public 		$mail_cumulable=1;
	public 		$mail_cumulable_reduction=1;
	public 		$mail_categories;
	public 		$mail_validity_days=30; //by default 30 days, not configurable
	public 		$mail_date_from_of_order = 1;
	public		$mail_date_from;  // if mail_date_from_order==1, date_from is the date of the order else this is the date fixed in the promotion rule
	public 		$mail_date_to;    // if mail_validatity_days==0, date_to is the date fixed in the promotion rule, otherwise, this is date_to+validatidy_days
	public      $mail_id_extcartrulefamily;
	public 		$date_add;
	public 		$date_upd;

	public 		$table_discount; // For inter compatibility 1.4 / 1.5

	protected	$fieldsRequired = array('name', 'global_cumulable_with_discounts', 'global_date_from', 'global_date_to', 'global_order_type', 'global_allows_others', 'global_allows_family',
										'criteria_type', 'criteria_association_type', 'criteria_products_number', 'criteria_products_amount', 'criteria_id_currency', 'criteria_amount_withtaxes', 'criteria_repetitions', 'criteria_product_with_reductions', 'criteria_product_with_quantity_price',
										'attribution_type', 'attribution_nb_impacted_products','attribution_percent',
										'attribution_amount', 'attribution_id_currency', 'attribution_amount_withtaxes',
										'attribution_maxamount', 'attribution_maxamount_id_currency', 'attribution_maxamount_withtaxes',
										'attribution_product_with_reductions', 'attribution_product_with_quantity_price', 'attribution_products_of_criteria', 'attribution_categories_of_criteria', 'attribution_manufacturers_of_criteria', 'attribution_suppliers_of_criteria'  /*, 'attribution_zones_fdp'*/,
										'sending_method' );
	protected	$fieldsSize = array('name'=>64, 'global_family'=>32);
	protected	$fieldsValidate = array('name'=> 'isGenericName', 'comments' => 'isCleanHTML', 'global_cumulable_with_discounts'=>'isBool', 'global_cart_rule_priority'=>'isInt', 'global_cart_rule_exclusion'=>'isCleanHtml',
										'global_date_from' => 'isDate', 'global_date_to' => 'isDate', 'global_order_type' => 'isInt', 'global_groups' => 'isCleanHTML', 'global_family'=> 'isGenericName', 'global_allows_others' => 'isBool', 'global_allows_family' => 'isBool',
										'global_cart_amount' => 'isFloat', 'global_cart_amount_id_currency' => 'isUnsignedInt','global_cart_amount_withtaxes' => 'isUnsignedInt',
										'criteria_type' => 'isUnsignedInt', 'criteria_association_type' => 'isUnsignedInt', 'criteria_products_number' => 'isUnsignedInt', 'criteria_products_amount' => 'isFloat',
										'criteria_id_currency' => 'isUnsignedInt', 'criteria_repetitions' => 'isUnsignedInt', 'criteria_amount_withtaxes' => 'isUnsignedInt', 'criteria_product_with_reductions' => 'isUnsignedInt', 'criteria_product_with_quantity_price' => 'isUnsignedInt', 'criteria_categories' => 'isCleanHTML', 'criteria_manufacturers' => 'isCleanHTML', 'criteria_suppliers' => 'isCleanHTML',
										'attribution_type' => 'isUnsignedInt', 'attribution_nb_impacted_products' => 'isUnsignedInt','attribution_percent' => 'isFloat',
										'attribution_amount' => 'isFloat', 'attribution_id_currency' => 'isUnsignedInt', 'attribution_amount_withtaxes' => 'isUnsignedInt',
										'attribution_maxamount' => 'isFloat', 'attribution_maxamount_id_currency' => 'isUnsignedInt', 'attribution_maxamount_withtaxes' => 'isUnsignedInt',
										'attribution_product_with_reductions' => 'isUnsignedInt', 'attribution_product_with_quantity_price' => 'isUnsignedInt', 'attribution_products_of_criteria' => 'isBool', 'attribution_categories_of_criteria' => 'isBool','attribution_manufacturers_of_criteria' => 'isBool','attribution_suppliers_of_criteria' => 'isBool', 'attribution_categories' => 'isCleanHTML', 'attribution_manufacturers' => 'isCleanHTML', 'attribution_suppliers' => 'isCleanHTML', 'attribution_zones_fdp' => 'isCleanHTML', 'attribution_carriers_fdp' => 'isCleanHTML',
	                                    'attribution_block_cart_line' => 'isUnsignedInt', 'attribution_nb_cart_lines_required' => 'isUnsignedInt', 'attribution_id_extcartrulefamily'=> 'isUnsignedInt',
	                                    'sending_method' => 'isUnsignedInt',
										'mail_discount_type' => 'isInt', 'mail_discount_value' => 'isFloat',
										'mail_minimal' => 'isFloat', 'mail_cumulable' => 'isBool', 'mail_cumulable_reduction' => 'isBool', 'mail_categories' => 'isCleanHTML', 'mail_validity_days' => 'isUnsignedInt',
										'mail_date_from_of_order'=> 'isInt', 'mail_id_extcartrulefamily'=> 'isUnsignedInt', 'mail_date_from' => 'isDate', 'mail_date_to' => 'isDate' );

 	protected $fieldsRequiredLang = array('discountobj_description');
	protected $fieldsSizeLang = array('discountobj_description' => 128, 'mail_description' => 128, 'mail_message' => 256);
	protected $fieldsValidateLang = array('discountobj_description' => 'isVoucherDescription', 'communication_extra_right' => 'isCleanHTML', 'communication_product_footer' => 'isCleanHTML',
										 'mail_description' => 'isVoucherDescription', 'mail_message' => 'isCleanHTML');

	protected 	$table = 'oleapromo';
	protected 	$identifier = 'id_oleapromo';

	static private $_cart_lines_blocked = array();

	public function __construct($id = null, $id_lang = null, $id_shop = null)
	{
		$this->discount_table = (version_compare('1.5', _PS_VERSION_)<=0) ?'cart_rule' :'discount';
		$this->attribution_id_extcartrulefamily = (int)Configuration::get('OLEA_MAXIPROMO_EXTRULEFAMILY'); // Default family of ExtCartRule module if it exists
		$this->mail_id_extcartrulefamily = (int)Configuration::get('OLEA_MAXIPROMO_EXTRULEFAMILY'); // Default family of ExtCartRule module if it exists
		return parent::__construct($id, $id_lang, $id_shop);
	}

	public function getFields()
	{
		parent::validateFields();
		//if (isset($this->id))
		//	$fields['id_oleapromo'] = intval($this->id);
		$fields['name'] = pSQL($this->name);
		$fields['position'] = (int)($this->position);
		$fields['comments'] = pSQL($this->comments);
		$fields['global_date_from'] = pSQL($this->global_date_from);
		$fields['global_date_to'] = pSQL($this->global_date_to);
		$fields['global_order_type'] = (int)($this->global_order_type);
		$fields['global_cumulable_with_discounts'] = (int)$this->global_cumulable_with_discounts;
		$fields['global_cart_rule_priority'] = (int)$this->global_cart_rule_priority;
		$fields['global_cart_rule_exclusion'] = pSQL($this->global_cart_rule_exclusion);
		$fields['global_groups'] = pSQL($this->global_groups);
		$fields['global_family'] = pSQL($this->global_family);
		$fields['global_allows_others'] = (int)($this->global_allows_others);
		$fields['global_allows_family'] = (int)($this->global_allows_family);
		$fields['global_cart_amount'] = (float)($this->global_cart_amount);
		$fields['global_cart_amount_id_currency'] = (int)($this->global_cart_amount_id_currency);
		$fields['global_cart_amount_withtaxes'] = (int)($this->global_cart_amount_withtaxes);
		$fields['criteria_type'] = (int)($this->criteria_type);
		$fields['criteria_association_type'] = (int)($this->criteria_association_type);
		$fields['criteria_products_number'] = (int)($this->criteria_products_number);
		$fields['criteria_products_amount'] = (float)($this->criteria_products_amount);
		$fields['criteria_id_currency'] = (int)($this->criteria_id_currency);
		$fields['criteria_amount_withtaxes'] = (int)($this->criteria_amount_withtaxes);
		$fields['criteria_repetitions'] = (int)($this->criteria_repetitions);
		$fields['criteria_product_with_reductions'] = (int)($this->criteria_product_with_reductions);
		$fields['criteria_product_with_quantity_price'] = (int)($this->criteria_product_with_quantity_price);
		$fields['criteria_categories'] = pSQL($this->criteria_categories);
		$fields['criteria_manufacturers'] = pSQL($this->criteria_manufacturers);
		$fields['criteria_suppliers'] = pSQL($this->criteria_suppliers);
		$fields['attribution_type'] = (int)($this->attribution_type);
		$fields['attribution_nb_impacted_products'] = (int)($this->attribution_nb_impacted_products);
		$fields['attribution_percent'] = (float)($this->attribution_percent);
		$fields['attribution_amount'] = (float)($this->attribution_amount);
		$fields['attribution_id_currency'] = (int)($this->attribution_id_currency);
		$fields['attribution_amount_withtaxes'] = (int)($this->attribution_amount_withtaxes);
		$fields['attribution_maxamount'] = (float)($this->attribution_maxamount);
		$fields['attribution_maxamount_id_currency'] = (int)($this->attribution_maxamount_id_currency);
		$fields['attribution_maxamount_withtaxes'] = (int)($this->attribution_maxamount_withtaxes);
		$fields['attribution_product_with_reductions'] = (int)($this->attribution_product_with_reductions);
		$fields['attribution_product_with_quantity_price'] = (int)($this->attribution_product_with_quantity_price);
		$fields['attribution_products_of_criteria'] = (int)($this->attribution_products_of_criteria);
		$fields['attribution_categories_of_criteria'] = (int)($this->attribution_categories_of_criteria);
		$fields['attribution_manufacturers_of_criteria'] = (int)($this->attribution_manufacturers_of_criteria);
		$fields['attribution_suppliers_of_criteria'] = (int)($this->attribution_suppliers_of_criteria);
		$fields['attribution_categories'] = pSQL($this->attribution_categories);
		$fields['attribution_manufacturers'] = pSQL($this->attribution_manufacturers);
		$fields['attribution_suppliers'] = pSQL($this->attribution_suppliers);
		$fields['attribution_zones_fdp'] = pSQL($this->attribution_zones_fdp);
		$fields['attribution_carriers_fdp'] = pSQL($this->attribution_carriers_fdp);
		$fields['attribution_block_cart_line'] = (int)($this->attribution_block_cart_line);
		$fields['attribution_nb_cart_lines_required'] = (int)($this->attribution_nb_cart_lines_required);
		$fields['attribution_id_extcartrulefamily'] = (int)($this->attribution_id_extcartrulefamily);
		$fields['sending_method'] = (int)($this->sending_method);
		$fields['mail_discount_type'] = (int)($this->mail_discount_type);
		$fields['mail_discount_value'] = (float)($this->mail_discount_value);
		$fields['mail_minimal'] = (float)($this->mail_minimal);
		$fields['mail_cumulable'] = (int)($this->mail_cumulable);
		$fields['mail_cumulable_reduction'] = (int)($this->mail_cumulable_reduction);
		$fields['mail_categories'] = pSQL($this->mail_categories);
		$fields['mail_validity_days'] = (int)($this->mail_validity_days);
		$fields['mail_date_from_of_order'] = (int)($this->mail_date_from_of_order);
		$fields['mail_date_from'] = pSQL($this->mail_date_from);
		$fields['mail_date_to'] = pSQL($this->mail_date_to);
		$fields['mail_id_extcartrulefamily'] = (int)($this->mail_id_extcartrulefamily);
		$fields['active'] = (int)$this->active;
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);

		return $fields;
	}

	public function getTranslationsFieldsChildNotUsed()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('discountobj_description', 'communication_extra_right' , 'communication_product_footer'));
	}

	public function getTranslationsFieldsChild()
	{
		$fieldsArray = array('discountobj_description', 'communication_extra_right', 'communication_product_footer', 'mail_description', 'mail_message');
		$fields = array();
		$languages = Language::getLanguages(false);
		$defaultLanguage = Configuration::get('PS_LANG_DEFAULT');
		foreach ($languages as $language)
		{
			$fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
			$fields[$language['id_lang']][$this->identifier] = (int)($this->id);
			$fields[$language['id_lang']]['discountobj_description'] = (isset($this->discountobj_description[$language['id_lang']])) ? pSQL($this->discountobj_description[$language['id_lang']], true) : '';
			$fields[$language['id_lang']]['communication_extra_right'] = (isset($this->communication_extra_right[$language['id_lang']])) ? pSQL($this->communication_extra_right[$language['id_lang']], true) : '';
			$fields[$language['id_lang']]['communication_product_footer'] = (isset($this->communication_product_footer[$language['id_lang']])) ? pSQL($this->communication_product_footer[$language['id_lang']], true) : '';
			$fields[$language['id_lang']]['mail_description'] = (isset($this->mail_description[$language['id_lang']])) ? pSQL($this->mail_description[$language['id_lang']], true) : '';
			$fields[$language['id_lang']]['mail_message'] = (isset($this->mail_message[$language['id_lang']])) ? pSQL($this->mail_message[$language['id_lang']], true) : '';
			foreach ($fieldsArray as $field)
			{
				if (!Validate::isTableOrIdentifier($field))
					die(Tools::displayError());

				/* Check fields validity */
				if (isset($this->{$field}[$language['id_lang']]) AND !empty($this->{$field}[$language['id_lang']]))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$language['id_lang']], true);
				elseif (in_array($field, $this->fieldsRequiredLang))
					$fields[$language['id_lang']][$field] = pSQL($this->{$field}[$defaultLanguage]);
				else
					$fields[$language['id_lang']][$field] = '';

			}
		}

		return $fields;
	}

	//----------------------------------------------------------------------------
	//   Add / Update / Delete
	//----------------------------------------------------------------------------

	public function add($autodate = true, $nullValues = false)
	{
		$this->position = Oleapromo::getLastPosition();
		return parent::add($autodate, true);
	}

	public function update($nullValues = false)
	{
		if (parent::update($nullValues))
			return $this->cleanPositions();
		return false;
	}

	public function delete()
	{
	 	if (parent::delete())
			return $this->cleanPositions();
		return false;
	}

	//----------------------------------------------------------------------------
	//   Requests management
	//----------------------------------------------------------------------------

	static public function getAllActive($groups, $cart_has_discounts, $nb_orders_customer, $cart) {
		if (version_compare('1.5', _PS_VERSION_) <= 0)
			$cart_has_discounts = false;  // True value not supported in 1.5

		self::oleaAddObjectTableAssociation('oleapromo');

		$sqlReq = 'SELECT op.id_oleapromo
				FROM `'._DB_PREFIX_.'oleapromo` op
				'.Shop::addSqlAssociation('oleapromo', 'op').'
				WHERE `active` = 1 '
				.(($cart_has_discounts) ?' AND global_cumulable_with_discounts=1' :'')
				.' AND (global_date_from <= NOW() AND NOW() <= global_date_to)
				'.Shop::addSqlRestriction(false, 'oleapromo_shop').'
				ORDER BY op.position ASC';
		$res = Db::getInstance()->ExecuteS($sqlReq);

		$id_lang = $cart->id_lang;
		if ($id_lang == 0)
			$id_lang = Configuration::get('PS_LANG_DEFAULT');
		$retour = array();

		$cart_amount_ti = $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
		$cart_amount_te = $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);

		foreach ((array)$res as $info) {
			$promo = new self($info['id_oleapromo'], $id_lang);
			if (count(array_intersect($promo->getGlobalGroups(), $groups))
				&& ($nb_orders_customer == null
					OR ($promo->global_order_type == Oleapromo::ORDER_TYPE_ALLORDERS)
					OR ($promo->global_order_type == Oleapromo::ORDER_TYPE_FIRSTORDER AND $nb_orders_customer==0)
					OR ($promo->global_order_type == Oleapromo::ORDER_TYPE_REORDER AND $nb_orders_customer > 0))
				&& (   ( $promo->global_cart_amount_withtaxes && $cart_amount_ti >= $promo->global_cart_amount)
					|| (!$promo->global_cart_amount_withtaxes && $cart_amount_te >= $promo->global_cart_amount))
				)
				{
					$retour[$info['id_oleapromo']] = $promo;
				}
		}
		return $retour;
	}

	public function getGlobalGroups() {
		return explode('_', $this->global_groups);
	}

	public function setGlobalGroups ($groups) {
		if (!is_array($groups))
			error_log('incorrect param in OleaPromo::setGlobalGroups()');
		else
			$this->global_groups = implode ('_', $groups);
	}

	//----------- C A T E G O R I E S ----------------------------------------
	public function getCriteriaCategories() {
		if ($this->criteria_categories == '')
			return array();
		else
			return explode('_', $this->criteria_categories);
	}

	public function setCriteriaCategories ($categories) {
		if (!is_array($categories))
			error_log('incorrect param in OleaPromo::setCriteriaCategories()');
		else
			$this->criteria_categories = implode ('_', $categories);
	}

	public function getAttributionCategories() {
		if ($this->attribution_categories == '')
			return array();
		else
			return explode('_', $this->attribution_categories);
	}

	public function setAttributionCategories ($categories) {
		if (!is_array($categories))
			error_log('incorrect param in OleaPromo::setAttributionCategories()');
		else
			$this->attribution_categories = implode ('_', $categories);
	}

	public function getMailCategories() {
		if ($this->mail_categories == '')
			return array();
		else
			return explode('_', $this->mail_categories);
	}

	public function setMailCategories ($categories) {
		if (!is_array($categories))
			error_log('incorrect param in OleaPromo::setMailCategories()');
		else
			$this->mail_categories = implode ('_', $categories);
	}

	//----------- M A N U F A C T U R E R S ----------------------------------------
	public function getCriteriaManufacturers() {
		if ($this->criteria_manufacturers == '')
			return array();
		else
			return explode('_', $this->criteria_manufacturers);
	}

	public function setCriteriaManufacturers ($manufacturers) {
		if (!is_array($manufacturers))
			error_log('incorrect param in OleaPromo::setCriteriaManufacturers()');
		else
			$this->criteria_manufacturers = implode ('_', $manufacturers);
	}

	public function getAttributionManufacturers() {
		if ($this->attribution_manufacturers == '')
			return array();
		else
			return explode('_', $this->attribution_manufacturers);
	}

	public function setAttributionManufacturers ($manufacturers) {
		if (!is_array($manufacturers))
			error_log('incorrect param in OleaPromo::setAttributionManufacturers()');
		else
			$this->attribution_manufacturers = implode ('_', $manufacturers);
	}

	//----------- S U P P L I E R S ----------------------------------------
	public function getCriteriaSuppliers() {
		if ($this->criteria_suppliers == '')
			return array();
		else
			return explode('_', $this->criteria_suppliers);
	}

	public function setCriteriaSuppliers ($suppliers) {
		if (!is_array($suppliers))
			error_log('incorrect param in OleaPromo::setCriteriaSuppliers()');
		else
			$this->criteria_suppliers = implode ('_', $suppliers);
	}

	public function getAttributionSuppliers() {
		if ($this->attribution_suppliers == '')
			return array();
		else
			return explode('_', $this->attribution_suppliers);
	}

	public function setAttributionSuppliers ($suppliers) {
		if (!is_array($suppliers))
			error_log('incorrect param in OleaPromo::setAttributionSuppliers()');
		else
			$this->attribution_suppliers = implode ('_', $suppliers);
	}

	//----------- Z O N E S ----------------------------------------
	public function getZonesFDP() {
		if ($this->attribution_zones_fdp == '')
			return array();
		else
			return explode('_', $this->attribution_zones_fdp);
	}

	public function setZonesFDP ($zones) {
		if (!is_array($zones))
			error_log('incorrect param in OleaPromo::setZonesFDP()');
		else
			$this->attribution_zones_fdp = implode ('_', $zones);
	}

	//----------- C A R R I E R S   F D P ----------------------------------------
	public function getCarriersFDP() {
		return self::static_getCarriersFDP($this->attribution_carriers_fdp);
	}

	static public function static_getCarriersFDP ($str) {
	    if ($str == '')
	        return array();
	    else
	        return explode('_', $str);
	}

	public function setCarriersFDP ($carriers) {
		if (!is_array($carriers))
			error_log('incorrect param in OleaPromo::setCarrierFDP()');
		else
			$this->attribution_carriers_fdp = implode ('_', $carriers);
	}

	//----------- C A R T   R U L E S ----------------------------------------
	public function getGlobalCartrulesExclusion() {
		if ($this->global_cart_rule_exclusion == '')
			return array();
		else
			return explode('_', $this->global_cart_rule_exclusion);
	}

	public function setGlobalCartrulesExclusion ($cartrule_exclusion) {
		if (!is_array($cartrule_exclusion))
			error_log('incorrect param in OleaPromo::setGlobalCartrulesExclusion()');
		else
			$this->global_cart_rule_exclusion = implode ('_', $cartrule_exclusion);
	}

	//----------------------------------------------------------------------------
	//   Position management
	//----------------------------------------------------------------------------

	public static function getLastPosition()
	{
		return (Db::getInstance()->getValue('SELECT MAX(position)+1
							FROM `'._DB_PREFIX_.'oleapromo`'));
	}

	public function updatePosition($way, $position)
	{
		if (!$res = Db::getInstance()->ExecuteS('
			SELECT op.`id_oleapromo`, op.`position`
			FROM `'._DB_PREFIX_.'oleapromo` op
			WHERE 1
			ORDER BY op.`position` ASC'
		))
			return false;

		foreach ($res AS $oleapromo)
			if ((int)($oleapromo['id_oleapromo']) == (int)($this->id))
				$movedOleapromo = $oleapromo;

		if (!isset($movedOleapromo) || !isset($position))
			return false;

		// < and > statements rather than BETWEEN operator
		// since BETWEEN is treated differently according to databases
		return (Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'oleapromo`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position`
			'.($way
				? '> '.(int)($movedOleapromo['position']).' AND `position` <= '.(int)($position)
				: '< '.(int)($movedOleapromo['position']).' AND `position` >= '.(int)($position)).'
			')
		AND Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'oleapromo`
			SET `position` = '.(int)($position).'
			WHERE `id_oleapromo` = '.(int)($movedOleapromo['id_oleapromo']).'
			'));
	}

	public static function cleanPositions()
	{
		$result = Db::getInstance()->ExecuteS('
				SELECT `id_oleapromo`
				FROM `'._DB_PREFIX_.'oleapromo`
				WHERE 1
				ORDER BY `position`');
		$sizeof = sizeof($result);
		for ($i = 0; $i < $sizeof; ++$i){
				$sql = '
				UPDATE `'._DB_PREFIX_.'oleapromo`
				SET `position` = '.(int)($i).'
				WHERE `id_oleapromo` = '.(int)($result[$i]['id_oleapromo']);
				Db::getInstance()->Execute($sql);
			}
		return true;
	}

	//***********************************************************************
	//     FUNCTIONAL TOOLS
	//***********************************************************************
	static public function static_computePromoValues ($promo_obj, &$promo_effective, &$cart_elements, $tax_mode, $cart) {
	    $carrier = isset($cart->id_carrier) ?$cart->id_carrier :-1;
//error_log(date('dd/mm H:i:s').', id='.$promo_obj->id.':'.print_r($cart_elements, true)."\n", 3, _PS_ROOT_DIR_.'/log/oleadbg.txt');
	    $dbg = "\n********\nPromoID = ".$promo_obj->id."\n";

        $amount = 0;
		$amounts = array();
		$free_shipping = false;

		// Return now if not applicable
		/* For test Only
		if (0 && count($cart->getProducts()) > 5)
		{
			$promo_effective[] = array('id_oleapromo'=> $promo_obj->id,
				'amount'=>0,
				'free_shipping' => 0,
				'cumul_on_global'=> 0,
				'promo' => $promo_obj,
				'id_association'=>0,
				'nb_attributions' => 1,
			);
			return;
		}
		*/

		if ($promo_obj->criteria_amount_withtaxes)
			usort($cart_elements, array('self', 'cartelem_compare_priceWT_asc'));
		else
			usort($cart_elements, array('self', 'cartelem_compare_price_asc'));

		if ($cart->id_currency == 0)
			$cart_currency = new Currency((int)Configuration::get('PS_DEFAULT_CURRENCY'));
		else
			$cart_currency = new Currency((int)$cart->id_currency);

		/* products to which apply the criterias */
		$criteria_categories = $promo_obj->getCriteriaCategories();
		$attribution_categories = ($promo_obj->attribution_categories_of_criteria==1) ?$criteria_categories :$promo_obj->getAttributionCategories();

		$criteria_manufacturers = $promo_obj->getCriteriaManufacturers();
		$attribution_manufacturers = ($promo_obj->attribution_manufacturers_of_criteria==1) ?$criteria_manufacturers :$promo_obj->getAttributionManufacturers();

		$criteria_suppliers = $promo_obj->getCriteriaSuppliers();
		$attribution_suppliers = ($promo_obj->attribution_suppliers_of_criteria==1) ?$criteria_suppliers :$promo_obj->getAttributionSuppliers();

		$cart_elems_of_criteria = array();
		$cart_elems_from_criteria = array();
		$criteria_association = $promo_obj->criteria_association_type;

		$total_wt = 0;
		$total = 0;
		$criteria_amount = array();
		$criteria_amount_wt = array();
		foreach ($cart_elements as $id_cart_elem=>$cart_elem) {
			if (	count(array_intersect($criteria_categories, $cart_elem['categories'])) // Must belong to appropriated categories
				AND (in_array(0, $criteria_manufacturers) OR in_array($cart_elem['manufacturer'], $criteria_manufacturers) OR ($cart_elem['manufacturer']==0 AND count($criteria_manufacturers)==0))  // manuf=0 => pas de check
				AND (in_array(0, $criteria_suppliers) OR in_array($cart_elem['supplier'], $criteria_suppliers) OR ($cart_elem['supplier']==0 AND count($criteria_suppliers)==0)) // supplier=0 => pas de check
				AND ($promo_obj->criteria_product_with_reductions OR (!$promo_obj->criteria_product_with_reductions AND !((bool)$cart_elem['reduction_applies']))) // Must not be discounted if discounted not included
				AND ($promo_obj->criteria_product_with_quantity_price OR (!$promo_obj->criteria_product_with_quantity_price AND !$cart_elem['quantity_price_applies'])) // Must not have qty_price if qty_price excluded
//				AND (!$cart_elem['used_as_criteria'])
//				AND !(self::$_cart_lines_blocked[$cart_elem['id_product']][$cart_elem['id_product_attribute']]) // cart line for this attribute must not be blocked by previous rule
			    ) {
					$cart_elems_of_criteria[] = $id_cart_elem;
					$total += $cart_elem['price'];
					$total_wt += $cart_elem['price_wt'];
					$tmp_criteria_key = ( ($criteria_association == Oleapromo::ASSOCIATION_TYPE_PRODUCT) ? $cart_elem['id_product']
										:(($criteria_association == Oleapromo::ASSOCIATION_TYPE_ATTRIBUTE) ? $cart_elem['id_product'].'_'.$cart_elem['id_product_attribute']
										:0));
					$cart_elems_from_criteria[$tmp_criteria_key][] = $id_cart_elem;
					if (!array_key_exists($tmp_criteria_key, $criteria_amount))
						$criteria_amount[$tmp_criteria_key] = 0;
					$criteria_amount[$tmp_criteria_key] += $cart_elem['price'];
					if (!array_key_exists($tmp_criteria_key, $criteria_amount_wt))
						$criteria_amount_wt[$tmp_criteria_key] = 0;
					$criteria_amount_wt[$tmp_criteria_key] += $cart_elem['price_wt'];
				}
				// Note : as cart_elem is already sorted, the cart_elems_from_criterias sub-arrays are also sorted
		}
		$dbg .= "\n*** cart_elems_from_criteria = ".print_r($cart_elems_from_criteria, true)."\n";

		$nb_criteria_groups = array();
		$nb_attribution_of_discount = array();
		foreach ($cart_elems_from_criteria as $criteria_index=>$criteria_elems) {

			// Compute groups of criteria
			$nb_criteria_groups[$criteria_index] = 0;
			switch ($promo_obj->criteria_type) {
				case Oleapromo::CRITERIA_TYPE_PRODUCT_NUMBER:
					if ((int)$promo_obj->criteria_products_number==0)
						$nb_criteria_groups[$criteria_index] = 0;
					else
						switch ($promo_obj->criteria_repetitions) {
							case 0: // Max possible
								$nb_criteria_groups[$criteria_index] = floor(count($criteria_elems) / (int)$promo_obj->criteria_products_number);
							break;
							case 1: // Only one time
								if (count($criteria_elems) >= (int)$promo_obj->criteria_products_number) {
									$nb_criteria_groups[$criteria_index] = 1;
								}
							break;
							default: // limited number of time
								if (count($criteria_elems) >= ((int)$promo_obj->criteria_products_number * $promo_obj->criteria_repetitions))
									$nb_criteria_groups[$criteria_index] = (int)$promo_obj->criteria_repetitions;
							break;
						}

					// criterias are based on the highest prices
					$i_min = count($criteria_elems) - ($nb_criteria_groups[$criteria_index] * (int)$promo_obj->criteria_products_number)  ;
					for ($i=$i_min; $i<=count($criteria_elems)-1; $i++)
						$cart_elements[$criteria_elems[$i]]['used_as_criteria_potential'] = true;
					break;

				case Oleapromo::CRITERIA_TYPE_PRODUCT_AMOUNT:
					if ($promo_obj->attribution_id_currency <> (int)Configuration::get('PS_CURRENCY_DEFAULT'))
						error_log('Code to be review, promo with amount not in default currency not supported');
					$criteria_products_amount = Tools::convertPrice($promo_obj->criteria_products_amount, $cart_currency);
					$cart_total = ($promo_obj->criteria_amount_withtaxes) ?$total_wt :$total ;
					$elems_amount[$criteria_index] = ($promo_obj->criteria_amount_withtaxes) ?$criteria_amount_wt[$criteria_index] :$criteria_amount[$criteria_index] ;
					switch ($promo_obj->criteria_repetitions) {
						case 0: // Max possible
							if ($criteria_products_amount == 0)
								$nb_criteria_groups[$criteria_index] = 1;
							else {
								$nb_criteria_groups[$criteria_index] = floor($elems_amount[$criteria_index] / $criteria_products_amount);
							}
						break;
						case 1: // Only one time
							if ($elems_amount[$criteria_index] >= $criteria_products_amount) {
								$nb_criteria_groups[$criteria_index] = 1;
							}
						break;
						default: // limited number of time
							if ($elems_amount[$criteria_index] >= ($criteria_products_amount * $promo_obj->criteria_repetitions))
								$nb_criteria_groups[$criteria_index] = (int)$promo_obj->criteria_repetitions;
						break;
					}
					$marked_products_amount = 0;
					$i = count($criteria_elems) -1;
					while (0<=$i AND $marked_products_amount<$criteria_products_amount) {  // A revoir, ca taggue des produits qui ne sont pas dan sles filtres de catgories/marques/fournisseurs
						$marked_products_amount += (($promo_obj->criteria_amount_withtaxes) ?$cart_elements[$i]['price_wt'] :$cart_elements[$i]['price']);
						$cart_elements[$criteria_elems[$i]]['used_as_criteria_potential'] = true;
						$i = $i - 1;
					}
					break;
			}
		}   // End foreach cart_elem_from_criteria

		$dbg .= "\n*** nb_criteria_group = ".print_r($nb_criteria_groups, true)."\n";
		// Compute amount
		if (array_sum($nb_criteria_groups) > 0) {
			if ($promo_obj->attribution_type == Oleapromo::ATTRIBUTION_TYPE_FDP) {
				if (	isset($cart->id_address_delivery)
					AND $cart->id_address_delivery
					AND Customer::customerHasAddress($cart->id_customer, $cart->id_address_delivery)) {
						$id_zone = Address::getZoneById((int)($cart->id_address_delivery));
						$free_shipping = (bool)in_array($id_zone, $promo_obj->getZonesFDP());
					} else {
						$free_shipping = true;
					}
					if ($cart->id_carrier) {
    					$carrier = new Carrier($cart->id_carrier);
	   				    $carriers_fdp = $promo_obj->getCarriersFDP();
					    $free_shipping = $free_shipping
					                   && (count($carriers_fdp)==0 || in_array($carrier->id_reference, $carriers_fdp));
					}
			} else {
				if ($promo_obj->attribution_products_of_criteria)
					$cart_elems_for_attribution = $cart_elems_from_criteria;
				elseif (0 AND $promo_obj->attribution_categories_of_criteria
					AND $promo_obj->attribution_manufacturers_of_criteria
					AND $promo_obj->attribution_suppliers_of_criteria)
						$cart_elems_for_attribution = $cart_elems_from_criteria;
				else {
					$cart_elems_for_attribution = array();
					foreach ($cart_elements as $id_cart_elem=>$cart_elem) {
						if (	count(array_intersect($attribution_categories, $cart_elem['categories']))>0 // Must belong to appropriated categories
							AND (in_array(0, $attribution_manufacturers) OR in_array($cart_elem['manufacturer'], $attribution_manufacturers) OR ($cart_elem['manufacturer']==0 AND count($attribution_manufacturers)==0))  // manuf=0 => pas de check
							AND (in_array(0, $attribution_suppliers) OR in_array($cart_elem['supplier'], $attribution_suppliers) OR ($cart_elem['supplier']==0 AND count($attribution_suppliers)==0)) // supplier=0 => pas de check
							AND ($promo_obj->attribution_product_with_reductions OR (!$promo_obj->attribution_product_with_reductions AND !((bool)$cart_elem['reduction_applies']))) // Must not be discounted if discounted not included
							AND ($promo_obj->attribution_product_with_quantity_price OR (!$promo_obj->attribution_product_with_quantity_price AND !$cart_elem['quantity_price_applies'])) // Must not be with quantity_price if qty_price not included
									AND $cart_elem['price'] > 0
									AND !((bool)$cart_elem['used_as_attribution'])
									) {
							$tmp_attribution_key = 0;
							$cart_elems_for_attribution[$tmp_attribution_key][] = $id_cart_elem;
						}
					}
				}
				//$dbg .= "cart_elems_for_attribution = ".print_r($cart_elems_for_attribution, true)."\n";
				$nb_cart_lines_to_deal = $promo_obj->attribution_nb_cart_lines_required;

                $nb_cart_lines_found = 0;
                $current_cart_line = '';

				$potential_attributions_index = array();
				$cart_elements_tmp = array_reverse($cart_elements, true);
				foreach ($cart_elements_tmp as $i => $cart_elem) {
					if ($current_cart_line <> $cart_elem['id_product'].'_'.$cart_elem['id_product_attribute']) {
				        $current_cart_line = $cart_elem['id_product'].'_'.$cart_elem['id_product_attribute'];
				        $nb_cart_lines_found += 1;
				    }
				    if (   !$cart_elem['used_as_attribution']
				        && !(self::$_cart_lines_blocked[$cart_elem['id_product']][$cart_elem['id_product_attribute']]) // cart line for this attribute must not be blocked by previous rule
				        && ($nb_cart_lines_to_deal==0 || $nb_cart_lines_found <= $nb_cart_lines_to_deal)) {
				            $potential_attributions_index[] = $i;
				    }
				}
				//$cart_elements = array_reverse($cart_elements, true);
				foreach ($cart_elems_for_attribution as $i_id_cart_elem_for_attribution => $i_attribution_keys)
				    foreach ($i_attribution_keys as $i_id_attribution_key => $i_attribution_key) {
				        if (! in_array($i_attribution_key, $potential_attributions_index)
				            || (0 < $nb_cart_lines_to_deal && $nb_cart_lines_found < $nb_cart_lines_to_deal)
				            ) {
				            unset($cart_elems_for_attribution[$i_id_cart_elem_for_attribution][$i_id_attribution_key]);
				            if (count($cart_elems_for_attribution[$i_id_cart_elem_for_attribution]) == 0)
				                unset($cart_elems_for_attribution[$i_id_cart_elem_for_attribution]);
				        }
				    }

				foreach ($nb_criteria_groups as $nb_criteria_group_index=>$nb_criteria_group) {
					$dbg .= "Dealing index $nb_criteria_group_index / $nb_criteria_group\n";
					$attribution_index = ($promo_obj->attribution_products_of_criteria) ?$nb_criteria_group_index :0;
					$attribution_elems = (isset($cart_elems_for_attribution[$attribution_index]) ?$cart_elems_for_attribution[$attribution_index] :array());

					if (! $nb_criteria_group) {
						$amounts[$nb_criteria_group_index] = 0;
						continue;
					}
					if ($promo_obj->attribution_nb_impacted_products == 0)
						$nb_attribution = count($attribution_elems);
					else {
						$nb_attribution_groups = ceil(count($attribution_elems) / $promo_obj->attribution_nb_impacted_products);
						$nb_attribution = min($nb_attribution_groups, $nb_criteria_groups[$nb_criteria_group_index]) * $promo_obj->attribution_nb_impacted_products;
						if ($nb_attribution > count($attribution_elems))
							$nb_attribution = count($attribution_elems);
					}
					$nb_attribution_of_discount[$nb_criteria_group_index] = $nb_attribution;

					$sum = 0;
					$sum_wt = 0;
					// $cart_elements = array_reverse($cart_elements, true); // in reverse order, i.e. by prices ascending
					$nb_attribution_dealed = 0;
					foreach ($cart_elements as $i => $cart_elem) {
						$dbgtmp = '';
 						$dbgtmp .= '********Looping cart elem '.$i.', test = '.print_r(array('in_array'=>in_array($i, $attribution_elems),
 																			      'used_as_criteria'=>(int)($cart_elem['used_as_criteria']),
 																				  'isset crit for promo' =>(int)isset($cart_elem['used_as_criteria_forpromo']),
 																				  'same promo' => (int)(isset($cart_elem['used_as_criteria_forpromo']) && (int)($cart_elem['used_as_criteria_forpromo']== $promo_obj->id)),
 																				  'used as attrib' => (int)$cart_elem['used_as_attribution'],
 						 )
 													,true)."\n";
					    if (in_array($i, $attribution_elems)
					    	&& (/*!$cart_elem['used_as_criteria'] || */ !isset($cart_elem['used_as_criteria_forpromo']) ||  (isset($cart_elem['used_as_criteria_forpromo']) && $cart_elem['used_as_criteria_forpromo']== $promo_obj->id))
					    	&& !$cart_elem['used_as_attribution'] ) {
    					    //$dbg .= 'Attribution dealed = '.$nb_attribution_dealed.' / '.$nb_attribution."\n";
					    	if ($nb_attribution_dealed < $nb_attribution) {
    					    	$dbg .= "\nsum + : price=".$cart_elements[$i]['price'].", price_wt=".$cart_elements[$i]['price_wt']."\n";
        						$sum += $cart_elements[$i]['price'];
        	   					$sum_wt += $cart_elements[$i]['price_wt'];
        		  				$cart_elements[$i]['used_as_attribution_potential'] = true;
    					    }
    					    $nb_attribution_dealed += 1;
					    }
					}
					// $cart_elements = array_reverse($cart_elements, true);
					$dbg .= "Totaux : $sum, $sum_wt\n";
					$ratio = ($sum==0) ?1 :($sum_wt/$sum);
					//error_log('cust='.Context::getContext()->customer->id."PS_TAX_EXC=".PS_TAX_EXC.', tax_mode='.$tax_mode, 3, _PS_ROOT_DIR_.'/log/log.txt');
					switch ($promo_obj->attribution_type) {
						case Oleapromo::ATTRIBUTION_TYPE_PERCENT:
							if (version_compare('1.5', _PS_VERSION_) <= 0 && (int)Configuration::get('OLEA_MAXIPROMO_DEALDISCOUNTHT')) { // si 1.5 et nouveau calcul des montants HT/TTC
								$amounts[$nb_criteria_group_index] = (($tax_mode == PS_TAX_EXC) ?$sum :$sum_wt) * ($promo_obj->attribution_percent / 100);
							} elseif (version_compare('1.3', _PS_VERSION_) <= 0) { //1.3 et 1.4 et 1.5
								$amounts[$nb_criteria_group_index] = $sum_wt * ($promo_obj->attribution_percent / 100);
							} else {
								/* Bug in 1.2, voucher must always be with taxes included
								$amount = ($tax_mode == PS_TAX_EXC) ?($sum * ($promo_obj->attribution_percent / 100)) :($sum_wt * ($promo_obj->attribution_percent / 100));
								*/
								$amounts[$nb_criteria_group_index] = $sum_wt * ($promo_obj->attribution_percent / 100);
							}
						break;
						case Oleapromo::ATTRIBUTION_TYPE_AMOUNT:
							if (version_compare('1.5', _PS_VERSION_) <= 0 && (int)Configuration::get('OLEA_MAXIPROMO_DEALDISCOUNTHT')) { // si 1.5 et nouveau calcul des montants HT/TTC
								if ($tax_mode == PS_TAX_EXC) {
									if ($promo_obj->attribution_amount_withtaxes)
										$amounts[$nb_criteria_group_index] = $nb_attribution * $promo_obj->attribution_amount / $ratio;
									else
										$amounts[$nb_criteria_group_index] = $nb_attribution * $promo_obj->attribution_amount;
									$amounts[$nb_criteria_group_index] = min ($amounts[$nb_criteria_group_index], $sum);
								} else {
									if ($promo_obj->attribution_amount_withtaxes)
										$amounts[$nb_criteria_group_index] = $nb_attribution * $promo_obj->attribution_amount;
									else
										$amounts[$nb_criteria_group_index] = $nb_attribution * $promo_obj->attribution_amount * $ratio;
									$amounts[$nb_criteria_group_index] = min ($amounts[$nb_criteria_group_index], $sum_wt);
								}
							} elseif (version_compare('1.3', _PS_VERSION_) <= 0) { // case 1.3 / 1.4 et suivantes avant support des discounts HT
								if ($tax_mode == PS_TAX_EXC) {
									if ($promo_obj->attribution_amount_withtaxes)
										$amounts[$nb_criteria_group_index] = $nb_attribution * $promo_obj->attribution_amount ;
									else
										$amounts[$nb_criteria_group_index] = $nb_attribution * $promo_obj->attribution_amount * $ratio;
									$amounts[$nb_criteria_group_index] = min ($amounts[$nb_criteria_group_index], $sum_wt);
								} else {
									if ($promo_obj->attribution_amount_withtaxes)
										$amounts[$nb_criteria_group_index] = $nb_attribution * $promo_obj->attribution_amount ;
									else
										$amounts[$nb_criteria_group_index] = $nb_attribution * $promo_obj->attribution_amount * $ratio;
									$amounts[$nb_criteria_group_index] = min ($amounts[$nb_criteria_group_index], $sum);
								}
							} else { // Case presta 1.2
								// FO managed with ou without taxes, amount is always with taxes
								if ($promo_obj->attribution_amount_withtaxes)
									$amounts[$nb_criteria_group_index] = $nb_attribution * $promo_obj->attribution_amount ;
								else
									$amounts[$nb_criteria_group_index] = $nb_attribution * $promo_obj->attribution_amount * $ratio;
							}
							$amounts[$nb_criteria_group_index] = Tools::convertPrice($amounts[$nb_criteria_group_index], $cart_currency);
						break;
						case Oleapromo::ATTRIBUTION_TYPE_AMOUNT_PRICES_RANGE:
							if (version_compare('1.5', _PS_VERSION_) <= 0 && (int)Configuration::get('OLEA_MAXIPROMO_DEALDISCOUNTHT')) { // si 1.5 et nouveau calcul des montants HT/TTC
								$dbg .= 'Tranche, tax FO = '.(($tax_mode == PS_TAX_EXC) ?'Excl' :'Incl').', tax promo = '.(($promo_obj->attribution_amount_withtaxes) ?'Yes' :'No')."\n";
								if ($tax_mode == PS_TAX_EXC) {
									if ($promo_obj->attribution_amount_withtaxes)
										$amounts[$nb_criteria_group_index] = ($nb_criteria_groups[$nb_criteria_group_index] * $promo_obj->attribution_amount) / $ratio ;
									else
										$amounts[$nb_criteria_group_index] = $nb_criteria_groups[$nb_criteria_group_index] * $promo_obj->attribution_amount;
									$amounts[$nb_criteria_group_index] = Tools::convertPrice($amounts[$nb_criteria_group_index], $cart_currency);
								} else {
									if ($promo_obj->attribution_amount_withtaxes)
										$amounts[$nb_criteria_group_index] = $nb_criteria_groups[$nb_criteria_group_index] * $promo_obj->attribution_amount ;
									else
										$amounts[$nb_criteria_group_index] = $nb_criteria_groups[$nb_criteria_group_index] * $promo_obj->attribution_amount * $ratio;
									$amounts[$nb_criteria_group_index] = Tools::convertPrice($amounts[$nb_criteria_group_index], $cart_currency);
								}
							} else {
								if ($promo_obj->attribution_amount_withtaxes)
									$amounts[$nb_criteria_group_index] = $nb_criteria_groups[$nb_criteria_group_index] * $promo_obj->attribution_amount ;
								else
									$amounts[$nb_criteria_group_index] = $nb_criteria_groups[$nb_criteria_group_index] * $promo_obj->attribution_amount * $ratio;
								$amounts[$nb_criteria_group_index] = Tools::convertPrice($amounts[$nb_criteria_group_index], $cart_currency);
							}
						break;
						case Oleapromo::ATTRIBUTION_TYPE_FINAL_PRICE:
							if (version_compare('1.5', _PS_VERSION_) <= 0 && (int)Configuration::get('OLEA_MAXIPROMO_DEALDISCOUNTHT')) { // si 1.5 et nouveau calcul des montants HT/TTC
								$dbg .= 'Final price, tax FO = '.(($tax_mode == PS_TAX_EXC) ?'Excl' :'Incl').', tax promo = '.(($promo_obj->attribution_amount_withtaxes) ?'Yes' :'No')."\n";
								if ($tax_mode == PS_TAX_EXC) {
									$dbg .= "Dealing tax Excl, ";
									if ($promo_obj->attribution_amount_withtaxes) {
										$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										$difference_amount = $sum_wt - $expected_amount;
										$amounts[$nb_criteria_group_index] = (($difference_amount)>0 ?$difference_amount :0) / $ratio;
										$dbg .= "promo with taxes, ".print_r(array('expected'=>$expected_amount, 'sum'=>$sum_wt ,'diff'=>$difference_amount), true)."\n";
									} else {
										$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										$difference_amount = $sum - $expected_amount;
										$amounts[$nb_criteria_group_index] = (($difference_amount)>0 ?$difference_amount :0);
										$dbg .= "promo without taxes, ".print_r(array('expected'=>$expected_amount, 'sum'=>$sum ,'diff'=>$difference_amount), true)."\n";
									}
								} else {
									$dbg .= "Dealing tax Incl\n";
									if ($promo_obj->attribution_amount_withtaxes) {
										$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										$difference_amount = $sum_wt - $expected_amount;
										$amounts[$nb_criteria_group_index] = (($difference_amount)>0 ?$difference_amount :0);
										$dbg .= "promo with taxes, ".print_r(array('expected'=>$expected_amount, 'sum'=>$sum_wt ,'diff'=>$difference_amount), true)."\n";
									} else {
										$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										$difference_amount = $sum - $expected_amount;
										$amounts[$nb_criteria_group_index] = $ratio *(($difference_amount)>0 ?$difference_amount :0);
										$dbg .= "promo without taxes, ".print_r(array('expected'=>$expected_amount, 'sum'=>$sum ,'diff'=>$difference_amount), true)."\n";
									}
								}
							} elseif (version_compare('1.3', _PS_VERSION_) <= 0) { // case 1.3 / 1.4
								if ($tax_mode == PS_TAX_EXC) {
									if ($promo_obj->attribution_amount_withtaxes) {
										$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										$difference_amount = $sum_wt - $expected_amount;
										$amounts[$nb_criteria_group_index] = (($difference_amount)>0 ?$difference_amount :0);
									} else {
										$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										$difference_amount = $sum - $expected_amount;
										$amounts[$nb_criteria_group_index] = $ratio *(($difference_amount)>0 ?$difference_amount :0);
									}
								} else {
									if ($promo_obj->attribution_amount_withtaxes) {
										$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										$difference_amount = $sum_wt - $expected_amount;
										$amounts[$nb_criteria_group_index] = (($difference_amount)>0 ?$difference_amount :0);
									} else {
										$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										$difference_amount = $sum - $expected_amount;
										$amounts[$nb_criteria_group_index] = $ratio *(($difference_amount)>0 ?$difference_amount :0);
									}
								}
							} else { // Case presta 1.2
								if ($tax_mode == PS_TAX_EXC) {
									if ($promo_obj->attribution_amount_withtaxes) {
										//$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										//$difference_amount = $sum_wt - $expected_amount;
										//$amounts[$nb_criteria_group_index] = (($difference_amount)>0 ?$difference_amount :0);
										error_log('Discount not supported');
										$amounts[$nb_criteria_group_index] = 0;
									} else {
										//$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										//$difference_amount = $sum - $expected_amount;
										//$amounts[$nb_criteria_group_index] = (($difference_amount)>0 ?$difference_amount :0);
										error_log('Discount not supported');
										$amounts[$nb_criteria_group_index] = 0;
									}
								} else {
									if ($promo_obj->attribution_amount_withtaxes) {
										$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										$difference_amount = $sum_wt - $expected_amount;
										$amounts[$nb_criteria_group_index] = (($difference_amount)>0 ?$difference_amount :0);
									} else {
										//$expected_amount = $nb_attribution * $promo_obj->attribution_amount;
										//$difference_amount = $sum - $expected_amount;
										//$amounts[$nb_criteria_group_index] = $ratio *(($difference_amount)>0 ?$difference_amount :0);
										error_log('Discount not supported');
										$amounts[$nb_criteria_group_index] = 0;
									}
								}
							}
						break;
					}

					$dbg .= "MIDDLE LOOP, cart_elems = " . self::dump_cartelems($cart_elements);

					foreach ($cart_elements as &$cart_elem) {
						if (array_key_exists('used_as_criteria_potential', $cart_elem) AND $cart_elem['used_as_criteria_potential'] == true) {
							if ($amounts[$nb_criteria_group_index] > 0) {
//19/12/2015								$cart_elem['used_as_criteria'] = true;
								$cart_elem['used_as_criteria_forpromo'] = $promo_obj->id;
							}
							unset($cart_elem['used_as_criteria_potential']);
						}
						if (array_key_exists('used_as_attribution_potential', $cart_elem) AND $cart_elem['used_as_attribution_potential'] == true) {
							if ($amounts[$nb_criteria_group_index] > 0) {
								$cart_elem['used_as_attribution'] = true;
								$cart_elem['used_as_attribution_forpromo'] = $promo_obj->id;
								if ((bool)$promo_obj->attribution_block_cart_line)
								    self::$_cart_lines_blocked[$cart_elem['id_product']][$cart_elem['id_product_attribute']] = true;
							}
							unset($cart_elem['used_as_attribution_potential']);
						}
					}

					if ($promo_obj->attribution_maxamount > 0 ) {
						if ($promo_obj->attribution_maxamount_id_currency <>(int)Configuration::get('PS_CURRENCY_DEFAULT')) {
							error_log('OleaquoteModule : Code to be review, promo with maxamount not in default currency not supported');
						} else {
							$tmp_attribution_max = ($promo_obj->attribution_maxamount_withtaxes)
																				? $promo_obj->attribution_maxamount
																				: $promo_obj->attribution_maxamount * $ratio ;
							if ($amounts[$nb_criteria_group_index] > $tmp_attribution_max)
								$amounts[$nb_criteria_group_index] = $tmp_attribution_max;
						}
					}

					$dbg .= "END LOOP, cart_elems = " . self::dump_cartelems($cart_elements);

				} // end foreach
			}
		}
		if (false && class_exists('DebugFileLogger'))
			DebugFileLogger::getInstance()->log($dbg);

				// Return value
		foreach ($amounts as $id_association=>$amount) {
			$promo_effective[] = array('id_oleapromo'=> $promo_obj->id,
										'amount'=>$amount,
										'free_shipping' => $free_shipping,
										'cumul_on_global'=> 0,
										'promo' => $promo_obj,
										'id_association'=>$id_association,
										'nb_attributions' => ( (array_key_exists($id_association, $nb_attribution_of_discount) && $promo_obj->attribution_nb_impacted_products>0) ?$nb_attribution_of_discount[$id_association] :1)
										//'nb_attributions' => 1; //(int)$nb_criteria_groups[$id_association]
										);
		}
		if ($free_shipping)
			$promo_effective[] = array('id_oleapromo'=> $promo_obj->id,
										'amount'=>0,
										'free_shipping' => true,
										'cumul_on_global'=> 0,
										'promo' => $promo_obj,
										'id_association'=>0,
										//'nb_attributions' => ( (array_key_exists($id_association, $nb_attribution_of_discount)) ?$nb_attribution_of_discount[$id_association] :1)
										'nb_attributions' => 1
										);
	}

	public static function dump_cartelems ($cart_elems) {
		$dump = '';
		$dump .= "\n+------+------+---------+---------+---+---+---+---+----+----+----+----+";
		$dump .= "\n| Prod | Attr |   P HT  |  P TTC  |RED|QTY|Cri|Atr|IdCr|IdAt|PotC|PotA|";
		$dump .= "\n+------+------+---------+---------+---+---+---+---+----+----+----+----+";
				foreach ($cart_elems as $elem)
		{
			$dump .= sprintf ("\n|%6d|%6d|%9.2f|%9.2f| %s | %s | %s | %s |%4d|%4d|%4d|%4d|",
								$elem['id_product'], $elem['id_product_attribute'], $elem['price'], $elem['price_wt'],
								($elem['reduction_applies']) ?'Y' :'N', ($elem['quantity_price_applies']) ?'Y' :'N',
								($elem['used_as_criteria']) ?'Y' :'N', ($elem['used_as_attribution']) ?'Y' :'N',
								isset($elem['used_as_criteria_forpromo']) ?$elem['used_as_criteria_forpromo'] :null,
								isset($elem['used_as_attribution_forpromo']) ?$elem['used_as_attribution_forpromo'] :null,
								isset($elem['used_as_criteria_potential']) ?$elem['used_as_criteria_potential'] :null,
								isset($elem['used_as_attribution_potential']) ?$elem['used_as_attribution_potential'] :null
			);
		}
		$dump .= "\n+------+------+---------+---------+---+---+---+---+----+----+----+----+";

		return $dump."\n";
	}

	public function computePromoValues (&$promo_effective, &$cart_elements, $tax_mode, $cart) {
		Oleapromo::static_computePromoValues($this, $promo_effective, $cart_elements, $tax_mode, $cart);
	}

	static public function updateMailDiscountOfCart($cart, $discounts) {
		$ids = array(0); // 0 to have at least one element
		foreach ((array)$discounts as $d)
			$ids[] = $d->id;

		$table_discount = (version_compare('1.5', _PS_VERSION_)) ?'cart_rule' :'discount';
		$id_discount_name = (version_compare('1.5', _PS_VERSION_)) ?'id_cart_rule' :'id_discount';
		$sqlReq = 'UPDATE `'._DB_PREFIX_.$table_discount.'`
					SET oleamultipromo_is_sent_by_email = ( IF('.$id_discount_name.' IN ('.implode(',',$ids).'), '.(int)OleaDiscountMulti::SEND_PENDING.', '.(int)OleaDiscountMulti::SEND_NONE.' ))
					WHERE oleamultipromo_id_cart_generating = '.(int)$cart->id;
		Db::getInstance()->Execute($sqlReq);

	}

	static public function reaffect_extcartrulefamily ($id_old, $id_new)
	{
		$sqlReq = 'UPDATE `'._DB_PREFIX_.'oleapromo`
					SET attribution_id_extcartrulefamily = '.(int)$id_new.'
					WHERE attribution_id_extcartrulefamily = '.(int)$id_old;
		Db::getInstance()->Execute($sqlReq);

		$sqlReq = 'UPDATE `'._DB_PREFIX_.'oleapromo`
					SET mail_id_extcartrulefamily = '.(int)$id_new.'
					WHERE mail_id_extcartrulefamily = '.(int)$id_old;
		Db::getInstance()->Execute($sqlReq);
	}


	public function isApplicableToProduct($id_product) {

		$product_categories = Oleapromo::getProductCategories($id_product);
		$criteria_categories = $this->getCriteriaCategories();
	 	$attribution_categories = ($this->attribution_categories_of_criteria==1) ?$criteria_categories :$this->getAttributionCategories();
		if (count(array_intersect($criteria_categories, $product_categories))>0
			OR count(array_intersect($attribution_categories, $product_categories))>0)
			$retour = true;
		else
			$retour = false;

		return $retour;
	}

	public static function oleaCheckCartRulesIsForCart($id_cart_rule, $id_cart)
	{
		$table_discount = (version_compare('1.5', _PS_VERSION_)) ?'cart_rule' :'discount';
		$id_discount_name = (version_compare('1.5', _PS_VERSION_)) ?'id_cart_rule' :'id_discount';
		$res = Db::getInstance()->getRow('SELECT oleamultipromo_id_cart_generating, is_for_oleamultipromo
											FROM `'._DB_PREFIX_.$table_discount.'`
											WHERE '.$id_discount_name.' = '.(int)$id_cart_rule);

		// considere cart_rule as OK in case of error
		if ($res === false)
			return true;

		if (isset($res['is_for_oleamultipromo']))
			return (!$res['is_for_oleamultipromo'] || $res['oleamultipromo_id_cart_generating'] == (int)$id_cart) ?true : false;
		else
			return true; // Don't know, considere as OK
	}

	static public function getInfoForFront (Oleapromo $promo, $product) {
		$retour = array('isInfoSignificant' => false);

		// This can only retrieve information for promotion where :
		//   - criteria is per product number, computed per product or per product combination
		//   - computation amount is in amount or in percent, on strictly the same product than criteria
		if (	($promo->criteria_type == Oleapromo::CRITERIA_TYPE_PRODUCT_NUMBER)
			AND ($promo->criteria_association_type == Oleapromo::ASSOCIATION_TYPE_PRODUCT OR $promo->criteria_association_type == Oleapromo::ASSOCIATION_TYPE_ATTRIBUTE)
			AND ($promo->attribution_type == Oleapromo::ATTRIBUTION_TYPE_AMOUNT OR $promo->attribution_type == Oleapromo::ATTRIBUTION_TYPE_PERCENT)
			AND ((bool)$promo->attribution_products_of_criteria == true)
			)
			{
				$is_significant = true;
				$price = $product->price_forfront;
				if ($promo->attribution_type == Oleapromo::ATTRIBUTION_TYPE_AMOUNT) {
					$reduc_amount = min ($price, $promo->attribution_amount);
					$price_reduced = $price - $reduc_amount;
					$reduc_percent = ($price>0) ?($reduc_amount*100/$price) :0;
				} elseif ($promo->attribution_type == Oleapromo::ATTRIBUTION_TYPE_PERCENT) {
					$price_reduced = $price * (1 - $promo->attribution_percent/100);
					$reduc_amount = $price - $price_reduced;
					$reduc_percent = $promo->attribution_percent;
				} else {
					$price_reduced = $price;
					$reduc_amount = 0;
					$reduc_percent = 0;
				}

				$nb_all_products = $promo->criteria_products_number;
				$nb_at_reduced_price = $promo->attribution_nb_impacted_products;
				if ($nb_at_reduced_price == 0)
					$nb_at_reduced_price = $nb_all_products;
				$nb_at_reduced_price = min ($nb_at_reduced_price, $nb_all_products);
				$nb_at_full_price = $nb_all_products - $nb_at_reduced_price ;

				$price_final = ($nb_all_products)	? ( ( $nb_at_full_price * $price + $nb_at_reduced_price * $price_reduced) / $nb_all_products) :$price;
				if (version_compare('1.3', _PS_VERSION_) <= 0) {
					$retour ['price_final'] = Tools::displayPrice($price_final);
					$retour ['price_reduced'] = Tools::displayPrice($price_reduced);
					$retour ['price_base'] = Tools::displayPrice($price);
					$retour ['reduc_amount'] = Tools::displayPrice($reduc_amount);
				} else {
					$cookie = Context::getContext()->cookie;
					$currency = new Currency(isset($cookie->id_currency) ?$cookie->id_currency :Configuration::get('PS_CURRENCY_DEFAULT'));
					$retour ['price_final'] = Tools::displayPrice($price_final, $currency);
					$retour ['price_reduced'] = Tools::displayPrice($price_reduced, $currency);
					$retour ['price_base'] = Tools::displayPrice($price, $currency);
					$retour ['reduc_amount'] = Tools::displayPrice($reduc_amount, $currency);
				}
				$retour ['nb_all_products'] = $nb_all_products;
				$retour ['nb_full_price'] = $nb_at_full_price;
				$retour ['nb_reduced_price'] = $nb_at_reduced_price;
				$retour ['reduc_percent'] = $reduc_percent;
				$retour ['hidden'] = 'visible';
			} else {
				$retour ['price_final'] = '--';
				$retour ['price_reduced'] = '--';
				$retour ['price_base'] = '--';
				$retour ['reduc_amount'] = '--';
				$retour ['nb_all_products'] = '--';
				$retour ['nb_full_price'] = '--';
				$retour ['nb_reduced_price'] = '--';
				$retour ['reduc_percent'] = '--';
				$retour ['hidden'] = 'hidden';
			}

		return $retour;
	}

	static public function sendPromoByEmailForOrder ($id_order) {
		$order = new Order (intval($id_order));
		if (! Validate::isLoadedObject ($order))
			return false;

		$customer = new Customer ($order->id_customer);
		if (! Validate::isLoadedObject ($customer) OR !Validate::isEmail($customer->email))
			return false;

		$discounts_list = self::getDiscountsToSendForOrder($order);
		if (count((array)$discounts_list) == 0)
			return false;

		$discounts_html = '';
		$discounts_txt = '';

		$omp = new oleamultipromos();
		$ids = array(0);
		$message_txt = '';

		$id_discount_name = (version_compare('1.5', _PS_VERSION_)) ?'id_cart_rule' :'id_discount';
		foreach ((array)$discounts_list as $discount_info) {
			if (version_compare('1.5', _PS_VERSION_) <= 0) {
				$condition = $discount_info['name'];
				/*
				$currency_info = (int)$discount_info['reduction_currency'];
				if ($discount_info['reduction_percent'] > 0) { // Percent
					$pattern = $omp->getL('pattern_mail_percent', $order->id_lang);
					$condition = sprintf($pattern, $discount_info['reduction_percent'], Tools::displayPrice($discount_info['minimal'], $currency_info), Tools::displayDate($discount_info['date_to'], $order->id_lang));
				} elseif ($discount_info['reduction_amount'] > 0) { // Amount
					$pattern = $omp->getL('pattern_mail_amount', $order->id_lang);
					$condition = sprintf($pattern, Tools::displayPrice($discount_info['reduction_amount'], $currency_info), Tools::displayPrice($discount_info['minimal'], $currency_info), Tools::displayDate($discount_info['date_to'], $order->id_lang));
				} elseif ($discount_info['free_shipping'] == 1) { // Free fdp
					$pattern = $omp->getL('pattern_mail_port', $order->id_lang);
					$condition = sprintf($pattern, Tools::displayPrice($discount_info['minimal'], $currency_info), Tools::displayDate($discount_info['date_to'], $order->id_lang));
				} else {
					$pattern = $omp->getL('pattern_mail_other', $order->id_lang);
					$condition = $pattern;
				}
				*/
				$discounts_html .= '<tr><td>'.$discount_info['code'].'</td><td>'.$condition.'</td></tr>';
				$discounts_txt .= $discount_info['code'].' : '.$condition."\n";
				$message_txt .= $discount_info['code'].' ';
			} else {
				$currency_info = ((version_compare('1.3', _PS_VERSION_) <= 0) ?(int)$discount_info['id_currency'] :new Currency(Configuration::get('PS_CURRENCY_DEFAULT')));
				if ($discount_info['id_discount_type'] == 1) { // Percent
					$pattern = $omp->getL('pattern_mail_percent', $order->id_lang);
					$condition = sprintf($pattern, $discount_info['value'], Tools::displayPrice($discount_info['minimal'], $currency_info), Tools::displayDate($discount_info['date_to'], $order->id_lang));
				} elseif ($discount_info['id_discount_type'] == 2) { // Amount
					$pattern = $omp->getL('pattern_mail_amount', $order->id_lang);
					$condition = sprintf($pattern, Tools::displayPrice($discount_info['value'], $currency_info), Tools::displayPrice($discount_info['minimal'], $currency_info), Tools::displayDate($discount_info['date_to'], $order->id_lang));
				} elseif ($discount_info['id_discount_type'] == 3) { // Free fdp
					$pattern = $omp->getL('pattern_mail_port', $order->id_lang);
					$condition = sprintf($pattern, Tools::displayPrice($discount_info['minimal'], $currency_info), Tools::displayDate($discount_info['date_to'], $order->id_lang));
				} else {
					$pattern = $omp->getL('pattern_mail_other', $order->id_lang);
					$condition = $pattern;
				}

				$discounts_html .= '<tr><td>'.$discount_info['name'].'</td><td>'.$condition.'</td></tr>';
				$discounts_txt .= $discount_info['name'].' : '.$condition."\n";
				$message_txt .= $discount_info['name'].' ';
			}
			$ids[] = $discount_info[$id_discount_name];
		}

		if ($message_txt <> '') {
			$message = new Message();
			$message->message = $omp->getL('Associated discounts sent by mail:', intval($order->id_lang)).' '.$message_txt.' by module '.$omp->displayName;
			$message->id_order = (int)$id_order;
			$message->private = true;
			if (!$message->add())
				error_log('Error saving message of order');
		}

		$data = array(
					'{firstname}' => $customer->firstname,
					'{lastname}' => $customer->lastname,
					'{email}' => $customer->email,
					'{order_name}' => sprintf("#%06d", intval($order->id)),
					'{date}' => Tools::displayDate($order->date_add, intval($order->id_lang), false),
					'{discounts_html}' => $discounts_html,
					'{discounts_txt}' => $discounts_txt
			);

		@$res = Mail::Send(intval($order->id_lang), // Id_lang
					'order_discounts', // Template
					$omp->getL('Your vouchers', intval($order->id_lang)), //Subject
					$data, // templateVars
					$customer->email, // to_email
					$customer->firstname.' '.$customer->lastname, // to_name
					NULL, //from email
					NULL, // from_name
					NULL,  //file_attachment
					NULL, // modeSMTP
					dirname(__FILE__).'/mails/'  //TemplatePath
					);
		if ($res) {
			/*
			$sqlReq = 'UPDATE `'._DB_PREFIX_.$this->table_discount.'`
					SET oleamultipromo_is_sent_by_email = '.(int)OleaDiscountMulti::SEND_DONE.',
						date_to = adddate(NOW(),datediff(date_to, date_from)),
						is_for_oleamultipromo = 0
					WHERE id_discount IN ('.implode(',', $ids).')';
			*/
			$table_discount = (version_compare('1.5', _PS_VERSION_)) ?'cart_rule' :'discount';
			$id_discount_name = (version_compare('1.5', _PS_VERSION_)) ?'id_cart_rule' :'id_discount';
			$sqlReq = 'UPDATE `'._DB_PREFIX_.$table_discount.'`
					SET oleamultipromo_is_sent_by_email = '.(int)OleaDiscountMulti::SEND_DONE.',
						date_from = IF(oleamultipromo_date_from_of_order=1, "'.$order->date_add.'", date_from),
						date_to = IF(oleamultipromo_validity_days=0, date_to, adddate(date_from, oleamultipromo_validity_days)),
						is_for_oleamultipromo = 0, active = 1
					WHERE '.$id_discount_name.' IN ('.implode(',', $ids).')';
			Db::getInstance()->Execute($sqlReq);
		} else
			error_log('OleaMaxiPromo : Error sending email with discounts');
		return $res;
	}

	static public function getDiscountsToSendForOrder ($order) {
		require_once dirname(__FILE__).'/OleaDiscountMulti.php';
		$table_discount = (version_compare('1.5', _PS_VERSION_)) ?'cart_rule' :'discount';
		$id_discount_name = (version_compare('1.5', _PS_VERSION_)) ?'id_cart_rule' :'id_discount';
		$sqlReq = '
			SELECT d.*, dl.*
			FROM `'._DB_PREFIX_.$table_discount.'` d
			LEFT JOIN `'._DB_PREFIX_.$table_discount.'_lang` dl ON (dl.'.$id_discount_name.' = d.'.$id_discount_name.' AND dl.id_lang = '.(int)$order->id_lang.')
			WHERE d.`oleamultipromo_id_order_generating` = '.(int)$order->id.'
				AND d.`is_for_oleamultipromo` = 1
				AND d.`oleamultipromo_sending_method` = '.(int)OleaDiscountMulti::SENDING_METHOD_MAIL.'
				AND d.`oleamultipromo_is_sent_by_email` = '. (int)OleaDiscountMulti::SEND_PENDING
			;

		$res = Db::getInstance()->ExecuteS($sqlReq);

		return $res;
	}

	//***********************************************************************
	//     FUNCTIONAL TOOLS
	//***********************************************************************

	static public function buildCartElements ($cart, &$cart_elements) {
		if (version_compare('1.4', _PS_VERSION_) <= 0) // case 1.4
			$cart_products = $cart->getProducts();
		else
			$cart_products = $cart->getProducts(true); // with refresh in 1.2/1.3

		foreach ($cart_products as $product) {
			$elem = array();
			$elem['id_product'] = $product['id_product'];
			$elem['id_product_attribute'] = $product['id_product_attribute'];
			$elem['categories'] = Oleapromo::getProductCategories($product['id_product']);
			$elem['manufacturer'] = $product['id_manufacturer'];
			$elem['supplier'] = $product['id_supplier'];
			$elem['price'] = $product['price'];
			$elem['price_wt'] = $product['price_wt'];
			if (version_compare('1.4', _PS_VERSION_) <= 0)
				$elem['reduction_applies'] = $product['reduction_applies'];
			else
				$elem['reduction_applies'] = Product::getReductionValue($product['reduction_price'], $product['reduction_percent'],
												$product['reduction_from'], $product['reduction_to'], $product['price'], false, $product['rate']);
			$elem['quantity_price_applies'] = (version_compare('1.5', _PS_VERSION_) <= 0)
								? (($product['quantity_discount_applies']==1) ?1 :0)
								: ((isset($product['quantity_price_applies']) AND $product['quantity_price_applies']==1) ?1 :0); // Patch to be done in Cart.php
			$elem['used_as_criteria'] = 0;
			$elem['used_as_attribution'] = 0;
			for ($i=0; $i<$product['cart_quantity']; $i++)
				$cart_elements[] = $elem;
			self::$_cart_lines_blocked[$elem['id_product']][$elem['id_product_attribute']] = false;
		}
		//usort($cart_elements, array('self', 'cartelem_compare_price_asc'));
	}

	static public function cartelem_compare_priceWT_desc ($elem1, $elem2) {
		if ($elem1['price_wt'] == $elem2['price_wt'])
			return 0;
		return ($elem1['price_wt'] > $elem2['price_wt']) ?-1 :1;
	}

	static public function cartelem_compare_priceWT_asc ($elem1, $elem2) {
		if ($elem1['price_wt'] == $elem2['price_wt'])
			return 0;
		return ($elem1['price_wt'] < $elem2['price_wt']) ?-1 :1;
	}

	static public function cartelem_compare_price_desc ($elem1, $elem2) {
		if ($elem1['price'] == $elem2['price'])
			return 0;
		return ($elem1['price'] > $elem2['price']) ?-1 :1;
	}

	static public function cartelem_compare_price_asc ($elem1, $elem2) {
		if ($elem1['price'] == $elem2['price'])
			return 0;
		return ($elem1['price'] < $elem2['price']) ?-1 :1;
	}

	static public function getProductCategories($id_product) {
		if (version_compare('1.6', _PS_VERSION_) <= 0) {
			$cats = Product::getProductCategoriesFull($id_product);
			$ret = [];
			foreach ((array)$cats as $val)
					$ret[] = $val['id_category'];
			return $ret;
		} elseif (version_compare('1.4', _PS_VERSION_) <= 0) {
			return Product::getProductCategories($id_product);
		} else {
			$ret = array();
			if ($row = Db::getInstance()->ExecuteS('
							SELECT `id_category` FROM `'._DB_PREFIX_.'category_product`
							WHERE `id_product` = '.(int)$id_product)
						)
				foreach ($row as $val)
					$ret[] = $val['id_category'];
			return $ret;
		}
	}

	protected function setDefinitionRetrocompatibility()
	{
		$res = parent::setDefinitionRetrocompatibility();

		foreach ($this->{'fieldsValidateLang'} as $field => $validate)
			$this->def['fields'][$field]['lang'] = true;

		foreach ($this->{'fieldsRequiredLang'} as $field)
			$this->def['fields'][$field]['lang'] = true;

		foreach ($this->{'fieldsSizeLang'} as $field => $size)
			$this->def['fields'][$field]['lang'] = true;

		return $res;
	}

	static function oleaAddObjectTableAssociation ($table_name) {
		return shop::addTableAssociation($table_name, array('type' => 'shop'));
	}

}

?>