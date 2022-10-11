<?php

class OleaDiscountMulti15 extends CartRule {

	const SENDING_METHOD_CURRENT_CART = 0;
	const SENDING_METHOD_MAIL = 1;

	const SEND_NONE = 0;
	const SEND_PENDING = 1;
	const SEND_DONE = 2;

	public $is_for_oleamultipromo;
	public $oleamultipromo_sending_method = self::SENDING_METHOD_CURRENT_CART;
	public $oleamultipromo_id_cart_generating = 0;
	public $oleamultipromo_id_order_generating = 0;
	public $oleamultipromo_is_sent_by_email = self::SEND_NONE;
	public $oleamultipromo_date_from_of_order = 1;
	public $oleamultipromo_validity_days = 365;
	public $oleamultipromo_mail_message;
	public $oleamultipromo_discount_key;

	public function __construct($id = NULL, $id_lang = NULL) {
		$this->is_for_oleamultipromo = 1;

		parent::$definition['fields'] = array_merge (
			parent::$definition['fields'],
			array(
				'is_for_oleamultipromo' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
				'oleamultipromo_sending_method' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
				'oleamultipromo_id_order_generating' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
				'oleamultipromo_id_cart_generating' => 	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
				'oleamultipromo_is_sent_by_email' => 	array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
				'oleamultipromo_date_from_of_order' => 	array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
				'oleamultipromo_validity_days' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
				'oleamultipromo_mail_message' => 		array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 256), // voluntary not multi-lang here, in the cart language
				'oleamultipromo_discount_key' => 		array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'size' => 256),
			));
		parent::__construct($id, $id_lang);
	}

	static public function getAllDiscountsInCart($id_cart) {
		$sqlReq = 'SELECT DISTINCT ccr.id_cart_rule
				FROM `'._DB_PREFIX_.'cart_cart_rule` ccr
				WHERE ccr.id_cart = '.(int)$id_cart;
		$res = Db::getInstance()->ExecuteS($sqlReq);

		$retour = array();
		foreach ($res as $info)
			$retour[$info['id_cart_rule']] = new self($info['id_cart_rule']);
		return $retour;
	}

	static public function getMultiDiscountsOfCart($id_cart, $id_lang=null) {

		if ((int)$id_lang == 0)
			$id_lang = Configuration::get('PS_LANG_DEFAULT');
		$sqlReq = 'SELECT  cr.*, crl.*, count(cr.id_cart_rule) as occurrence
				FROM `'._DB_PREFIX_.'cart_rule` cr
				LEFT JOIN `'._DB_PREFIX_.'cart_rule_lang` crl ON (cr.id_cart_rule = crl.id_cart_rule)
				LEFT JOIN `'._DB_PREFIX_.'cart_cart_rule` ccr ON (ccr.id_cart_rule = cr.id_cart_rule)
				WHERE cr.oleamultipromo_id_cart_generating = '.(int)$id_cart.'
					AND crl.id_lang = '.(int)$id_lang.'
				GROUP BY cr.id_cart_rule';
		$res = Db::getInstance()->ExecuteS($sqlReq);
		return $res;
	}

	static public function getAllPendingOrSentMailDiscountOfOrder ($id_order, $id_lang=null) {
		if ((int)$id_lang == 0)
			$id_lang = Configuration::get('PS_LANG_DEFAULT');
		$sqlReq = 'SELECT cr.*, crl.*
				FROM `'._DB_PREFIX_.'cart_rule` cr
				LEFT JOIN `'._DB_PREFIX_.'cart_rule_lang` crl ON (crl.id_cart_rule = cr.id_cart_rule)
				WHERE cr.oleamultipromo_id_order_generating = '.(int)$id_order.'
					AND cr.oleamultipromo_sending_method = '.(int)self::SENDING_METHOD_MAIL.'
					AND oleamultipromo_is_sent_by_email IN ( '.(int)self::SEND_PENDING.', '.(int)self::SEND_DONE.')
					AND crl.id_lang = '.(int)$id_lang;
		$res = Db::getInstance()->ExecuteS($sqlReq);

		return (array)$res;
	}

	static public function buildDiscountOfPromoinfo ($discount_name, $promoinfo, $oleadiscounts_in_cart, $cart, $oleadiscounts_associated_to_cart) {
		if (!Validate::isDiscountName($discount_name))
			return null;

		$returned_discount = null;
		foreach ($oleadiscounts_in_cart as $discount) {
			if ($discount->oleamultipromo_discount_key == $discount_name)
				$returned_discount = $discount;
		}
		if ($returned_discount == null) {
			foreach ($oleadiscounts_associated_to_cart as $discount) {
				if ($discount['oleamultipromo_discount_key'] == $discount_name)
					$returned_discount = new self((int)$discount['id_cart_rule']);
			}
		}

		if ($returned_discount == null)
			$returned_discount = new self((int)CartRule::getIdByCode($discount_name));

		$languages = Language::getLanguages();
		$customer = new Customer($cart->id_customer);
		require_once dirname(__FILE__).'/Oleapromo.php';
		$promo = $promoinfo['promo'];


		$returned_discount->oleamultipromo_id_cart_generating = (int)$cart->id;
		$returned_discount->description = 'Automatic generation by oleamultipromos module for cart='.$cart->id.', customer='.(int)($cart->id_customer).', oleamultipromorule='.$promo->id;
		$returned_discount->oleamultipromo_discount_key = $discount_name; // OK 1.5
		//$returned_discount->code = $discount_name; // OK 1.5
		$returned_discount->id_customer = (int)($cart->id_customer); // OK 1.5
		//$groups = Customer::getGroupsStatic((int)($cart->id_customer));
		$returned_discount->id_group = 0;/*((int)($cart->id_customer)) ?$groups[0] :0;*/
		$returned_discount->id_currency = (int)($cart->id_currency);
		$returned_discount->quantity = 1;// OK 1.5
		$returned_discount->quantity_per_user = 1;// OK 1.5
		$returned_discount->priority = 1; // OK 1.5
		$returned_discount->partial_use = 0;// OK 1.5
		$returned_discount->active = 1;// OK 1.5
		$returned_discount->minimum_amount = 0; // OK 1.5
		$returned_discount->minimum_amount_tax = 0; // OK 1.5
		$returned_discount->minimum_amount_currency = (int)$cart->id_currency;// OK 1.5
		$returned_discount->minimum_amount_shipping = 0;// OK 1.5
		$returned_discount->highlight = 0; // OK 1.5
		$returned_discount->behavior_not_exhausted = 1; // reduce voucher to order amount
		$returned_discount->reduction_currency = (int)$cart->id_currency;
		$returned_discount->reduction_tax = ((int)Configuration::get('OLEA_MAXIPROMO_DEALDISCOUNTHT'))
													?(Group::getPriceDisplayMethod((int)(($customer->id) ?$customer->id_default_group :1)) == PS_TAX_EXC ?0 :1)
													:1;
		$returned_discount->free_shipping = 0;
		$returned_discount->reduction_amount = 0;
		$returned_discount->reduction_percent = 0;
		$returned_discount->priority = $promo->global_cart_rule_priority;

		if ($promo->sending_method == Oleapromo::SENDING_METHOD_MAIL) {
			if ($promo->mail_discount_type==Oleapromo::MAIL_DISCOUNT_TYPE_COMPUTED) {
				$returned_discount->free_shipping = 0;
				$returned_discount->reduction_amount = round($promoinfo['amount'], 2);
				$returned_discount->reduction_percent = 0;
				$hashV = Tools::displayPrice($returned_discount->reduction_amount);
			} elseif ($promo->mail_discount_type==Oleapromo::MAIL_DISCOUNT_TYPE_AMOUNT) {
				$returned_discount->free_shipping = 0;
				$returned_discount->reduction_amount = round(($promo->mail_discount_value<0) ?0 :$promo->mail_discount_value, 2);
				$returned_discount->reduction_percent = 0;
				$hashV = Tools::displayPrice($returned_discount->reduction_amount);
			} elseif ($promo->mail_discount_type==Oleapromo::MAIL_DISCOUNT_TYPE_PERCENT) {
				$returned_discount->free_shipping = 0;
				$returned_discount->reduction_amount = 0;
				$discount_value = round($promo->mail_discount_value, 2);
				$returned_discount->reduction_percent = ($discount_value<0) ?0 :(($discount_value<100) ?$discount_value :100);
				$hashV = $returned_discount->reduction_percent.'%';
			} elseif ($promo->mail_discount_type==Oleapromo::MAIL_DISCOUNT_TYPE_FDP) {
				$returned_discount->free_shipping = 1;
				$returned_discount->reduction_amount = 0;
				$returned_discount->reduction_percent = 0;
				$returned_discount->value = 0;
				$hashV = '';
			} else {
				$returned_discount->free_shipping = 0;
				$returned_discount->reduction_amount = 0;
				$returned_discount->reduction_percent = 0;
				$hashV = '--';
			}
		} else {
			if ($promoinfo['free_shipping']) {
				$returned_discount->free_shipping = 1;
				$returned_discount->reduction_amount = 0;
				$returned_discount->reduction_percent = 0;
			} else { // Always amount
				$returned_discount->free_shipping = 0;
				$returned_discount->reduction_amount = round($promoinfo['amount'], 2);
				$returned_discount->reduction_percent = 0;
			}
		}

		if ($promo->attribution_type == Oleapromo::ATTRIBUTION_TYPE_FDP) {
		    $carriers_fdp = $promo->getCarriersFDP();
		    if (count($carriers_fdp)) {
		        $returned_discount->carrier_restriction = true;
		        static $db_update_done = false;
		        if (1 || ! $db_update_done) {
		            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'cart_rule_carrier` WHERE id_cart_rule = '.(int)$returned_discount->id);
	   	            $values = array();
		            foreach ($carriers_fdp as $c) {
		                if ((int)$c > 0)
    		                $values[] = '('.(int)$returned_discount->id.', '.(int)$c.')';
	       	        }
		            if (count($values))
    		            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'cart_rule_carrier` VALUES '.implode(',', $values));
		        }
		    } else
		        $returned_discount->carrier_restriction = false;
		} else {
		    $returned_discount->carrier_restriction = false;
		}

		$now = time()-10;
		$returned_discount->date_from = date('Y-m-d H:i:s', $now);
		//$mail_categories = false;
		if ($promo->sending_method == Oleapromo::SENDING_METHOD_MAIL) {
			$returned_discount->date_from = $promo->mail_date_from;//date('Y-m-d H:i:s', $promo->mail_date_from);
			$returned_discount->date_to = $promo->mail_date_to;//date('Y-m-d H:i:s', $promo->mail_date_to);
			$returned_discount->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * $promo->mail_validity_days));
			$returned_discount->oleamultipromo_sending_method = self::SENDING_METHOD_MAIL;
			$returned_discount->active = 0; // Sholud be desactivated to not be added ti cart by the autoCartRules method.
			$returned_discount->oleamultipromo_date_from_of_order = $promo->mail_date_from_of_order;
			$returned_discount->oleamultipromo_validity_days = $promo->mail_validity_days;
			foreach ($languages as $language) // OK 1.5
				$returned_discount->name[$language['id_lang']] = (($promo->mail_description<>'') ?str_replace('#V#', $hashV, $promo->mail_description) :'Automatic'); /*(isset($promo->discountobj_description[$language['id_lang']])) ?$promo->discountobj_description[$language['id_lang']] :'--';*/

			$returned_discount->oleamultipromo_mail_message = str_replace('#V#', $hashV, $promo->mail_message);
			//$returned_discount->cumulable = (int)$promo->mail_cumulable;
			//$returned_discount->cumulable_reduction = (int)$promo->mail_cumulable_reduction;
			$returned_discount->minimum_amount = (float)$promo->mail_minimal;
			//$mail_categories = $promo->getMailCategories();
		} else {
			$returned_discount->active = 1;
			$returned_discount->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * 365));
			foreach ($languages as $language)
				$returned_discount->name[$language['id_lang']] = $promo->discountobj_description.(($promoinfo['nb_attributions']>1) ?' (x'.$promoinfo['nb_attributions'].')' :''); /*(isset($promo->discountobj_description[$language['id_lang']])) ?$promo->discountobj_description[$language['id_lang']] :'--';*/
			//$returned_discount->cumulable = 1;
			//$returned_discount->cumulable_reduction = 1;
			$returned_discount->minimum_amount = 0;
			$returned_discount->oleamultipromo_date_from_of_order = 0;
			$returned_discount->oleamultipromo_validity_days = 0;
		}

		$returned_discount->shop_restriction = 1;
		$is_new_discount = ($returned_discount->id == 0);
		$sav = $returned_discount->save();
		if (!$sav)
			error_log('error saving cart_rule ('.(int)$returned_discount->id.')');

		if ($is_new_discount) {
			Db::getInstance()->execute('
							INSERT INTO `'._DB_PREFIX_.'cart_rule_shop` (`id_cart_rule`, `id_shop`)
							VALUES ('.(int)$returned_discount->id.', '.(int)Context::getContext()->shop->id.')');
		}

		$oleaextcartrulemodule = Module::getInstanceByName('oleaextcartrulemodule');
		if (Validate::isLoadedObject($oleaextcartrulemodule) && $oleaextcartrulemodule->active)
			if ($promo->sending_method == Oleapromo::SENDING_METHOD_MAIL)
				$oleaextcartrulemodule->setFamily($returned_discount->id, (int)$promo->mail_id_extcartrulefamily);
			else
				$oleaextcartrulemodule->setFamily($returned_discount->id, (int)$promo->attribution_id_extcartrulefamily);

		/* Cache Refesh */
		Cache::clean('Cart::getCartRules'.$cart->id.'-'.CartRule::FILTER_ACTION_ALL);
		Cache::clean('Cart::getCartRules'.$cart->id.'-'.CartRule::FILTER_ACTION_SHIPPING);
		Cache::clean('Cart::getCartRules'.$cart->id.'-'.CartRule::FILTER_ACTION_REDUCTION);
		Cache::clean('Cart::getCartRules'.$cart->id.'-'.CartRule::FILTER_ACTION_GIFT);
		if (version_compare('1.5.4', _PS_VERSION_) <= 0)
			Cache::clean('Cart::getCartRules'.$cart->id.'-'.CartRule::FILTER_ACTION_ALL_NOCAP);

		Cache::clean('Cart::getCartRules_'.$cart->id.'-'.CartRule::FILTER_ACTION_ALL). '-ids';
		Cache::clean('Cart::getCartRules_'.$cart->id.'-'.CartRule::FILTER_ACTION_SHIPPING). '-ids';
		Cache::clean('Cart::getCartRules_'.$cart->id.'-'.CartRule::FILTER_ACTION_REDUCTION). '-ids';
		Cache::clean('Cart::getCartRules_'.$cart->id.'-'.CartRule::FILTER_ACTION_GIFT). '-ids';

		//		Cache::clean('getContextualValue_'.(int)$returned_discount->id.'_*');
		Cache::clean('getContextualValue_*');
		/* End cache refresh */

		// Management of Cart Rules Restrictions
		/* Not managed for now... Need core behavior confirmation
		Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'cart_rule_combination`
							WHERE (id_cart_rule_1 = '.$returned_discount->id.' ) OR (id_cart_rule_2 = '.$returned_discount->id.')');
		$cart_rule_exclusions_for_sql = array();
		foreach ($promo->getGlobalCartrulesExclusion() as $id_cart_rule)
			$cart_rule_exclusions_for_sql[] = ($returned_discount->id < $id_cart_rule)
						?'( '.(int)$returned_discount->id.', '.(int)$id_cart_rule     .')'
						:'( '.(int)$id_cart_rule     .', '.(int)$returned_discount->id.')';
		if (count($cart_rule_exclusions_for_sql))
			Db::getInstance()->execute('INSERT IGNORE INTO `'._DB_PREFIX_.'cart_rule_combination`
							(id_cart_rule_1, id_cart_rule_2) VALUES '.
							implode(',', $cart_rule_exclusions_for_sql));
		*/
		return $returned_discount;
	}

	static public function buildDiscountName ($id_promo, $id_cart, $id_association) {
		list($id_product, $id_product_attribute) = explode('_', $id_association.'_0');
		return Tools::strtoupper('PR'.base_convert($id_promo, 10, 34).'CZ'.base_convert($id_cart, 10, 34).'Z'.base_convert((int)$id_product, 10, 34).'Z'.base_convert((int)$id_product_attribute, 10, 34));
		//return 'PR'.(int)$id_promo.'CA'.(int)$id_cart.'A'.$id_association;  // Should be less than 32 characters
	}

	static public function countOldDiscount ($nb_days) {
		$sql = 'SELECT count(*) as total
				FROM `'._DB_PREFIX_.'cart_rule` cr
				WHERE   (cr.is_for_oleamultipromo = 1)
					AND (cr.oleamultipromo_sending_method = '.self::SENDING_METHOD_CURRENT_CART.')
					AND (cr.date_from <= SUBDATE(NOW(), '.(int)$nb_days.'))
					';

		$total = Db::getInstance()->getValue($sql);
		return $total;
	}

	static public function deleteOldDiscount ($nb_days) {

		$sql = 'SELECT cr.id_cart_rule
				FROM `'._DB_PREFIX_.'cart_rule` cr
				WHERE   (cr.is_for_oleamultipromo = 1)
					AND (cr.oleamultipromo_sending_method = '.self::SENDING_METHOD_CURRENT_CART.')
					AND (cr.date_from <= SUBDATE(NOW(), '.(int)$nb_days.'))
					';
		$res = Db::getInstance()->executeS($sql);

		if ($res AND is_array($res)) {
			$ids = array();
			foreach ($res as $line)
				$ids[] = $line['id_cart_rule'];
			$ids_str = implode(',', $ids);
			$sql1 = 'DELETE FROM `'._DB_PREFIX_.'cart_cart_rule`
					WHERE id_cart_rule IN ('.$ids_str.')';
			$res1 = Db::getInstance()->execute($sql1);

			$sql3 = 'DELETE FROM `'._DB_PREFIX_.'cart_rule`
					WHERE id_cart_rule IN ('.$ids_str.')';
			$res3 = Db::getInstance()->execute($sql3);

			$sql3 = 'DELETE FROM `'._DB_PREFIX_.'cart_rule_combination`
					WHERE (id_cart_rule_1 IN ('.$ids_str.')) OR (id_cart_rule_2 IN ('.$ids_str.'))';
			$res3 = Db::getInstance()->execute($sql3);

		}

		return true;
	}

	public function update($null_values = false)
	{
		$tmp = new parent($this->id);
		$tmp->clearCache();
		return parent::update($null_values);
	}

}