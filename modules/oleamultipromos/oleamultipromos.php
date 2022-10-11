<?php

/**
  * ---------------------------------------------------------------------------------
  *
  * This file is part of the 'OleaMultiPromos' module feature
  * Developped for Prestashop  platform.
  * You are not allowed to use it on several site
  * You are not allowed to sell or redistribute this module
  * This header must not be removed
  *
  * @category XXX
  * @author OleaCorner <contact@oleacorner.com> <www.oleacorner.com>
  * @copyright OleaCorner
  * @version 1.0
  *
  * ---------------------------------------------------------------------------------
  */


if (version_compare(_PS_VERSION_, '1.3.3') >= 0 AND !defined('_CAN_LOAD_FILES_'))
	exit;

class oleamultipromos extends Module
{
	//const static_version = '3.5.6';

	private $_cache;
	private $_front_values = null;
	public $discount_table;

	function __construct()
	{
		$cookie = Context::getContext()->cookie;
		$this->name = 'oleamultipromos';
		$this->version = '3.5.14';
		$this->author = 'Oleacorner';
		$this->tab = 'pricing_promotion';
		$this->module_key = "25228888e4e70edcaec92927f5fa68aa";
		$this->ps_versions_compliancy['min'] = '1.5.0.0';

		$this->discount_table = (version_compare('1.5', _PS_VERSION_)<=0) ?'cart_rule' :'discount';
		parent::__construct();

		$this->displayName = $this->l('Olea Multi-Promos');
		$this->description = $this->l('Management of different kind of promotions');
		//$this->confirmUninstall = $this->l('Are you sure you want to uninstall ?');

		if (!$this->is_version_ok())
			$this->warning = $this->l('The new installed version must be activated');
		if (isset($cookie->id_employee) && $cookie->id_employee && count($this->getPatchesErrors()))
			$this->warning = $this->l('Core patches are missing, see documentation');
		if (isset($cookie->id_employee) && $cookie->id_employee
				&& $this->getModuleUpgradeFixExplanation())
			$this->warning = $this->l('Module databases not correctly installed, see config tab of the module');
		$this->_cache = array();
	}

	//***********************************************************************
	//     INSTALL
	//***********************************************************************
	function install()
	{
		if (version_compare(_PS_VERSION_, '1.5.3') < 0)
			copy (dirname(__FILE__).'/override/classes/shop/Shop_ps152.php', dirname(__FILE__).'/override/classes/shop/Shop.php');

		//$this->version = '0.0'; // to force usage of upgrade files, see PSCFV-5314
		if (!parent::install())
			return false;

		/* Install new admin tab */
		$this->_create_tab('AdminMultiPromos', Tab::getIdFromClassName((version_compare('1.5', _PS_VERSION_)<=0) ?'AdminPriceRule' :'AdminCatalog'),
 							array('en'=>'Maxi Promos', 'fr'=>'Maxi Promos')); // under Catalog

 		/* Create new hook */
		/*
		$query = "INSERT IGNORE INTO `"._DB_PREFIX_."hook`
	 		(`id_hook`, `name`, `title`, `description`, `position`) VALUES
	 		(NULL, 'oleaCartRefreshForMultiPromo', 'Hook for OleaMultiPromos Module', NULL, '0')";
	 	if (!Db::getInstance()->Execute($query))
	 		return false;
	 	*/
		if (
			!$this->registerHook('oleaCartRefreshForMultiPromo')
			)
			return false;

		if (!defined('_MYSQL_ENGINE_'))
			define('_MYSQL_ENGINE_', 'MyISAM');

		/* Create database tables */
 		$query = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'oleapromo` (
	  				`id_oleapromo` 				int(10) unsigned NOT NULL auto_increment,
	  				`name`		 				varchar(64),
	 				`position` 					int(10) unsigned,
	 				`comments`					text,
	 				`global_date_from`			date NOT NULL,
	  				`global_date_to` 			date NOT NULL,
	  				`global_cumulable_with_discounts` 	tinyint(1),
	 				`global_order_type` 		int(10),
	 				`global_groups`				text,
	 				`global_allows_others`		tinyint(1),
	  				`criteria_type`				int(10) unsigned,
	  				`criteria_products_number`	int(10) unsigned,
	  				`criteria_products_amount`	decimal(10,2),
	  				`criteria_id_currency`		int(10) unsigned,
	  				`criteria_amount_withtaxes`	tinyint(1),
	  				`criteria_repetitions`		int(10) unsigned,
	  				`criteria_product_with_reductions`	tinyint(1),
	  				`criteria_categories`		text,
	  				`attribution_type`			int(10) unsigned,
	  				`attribution_nb_impacted_products` int(10) unsigned,
	  				`attribution_percent`		decimal(10,3),
	  				`attribution_amount`		decimal(10,2),
	  				`attribution_id_currency`	int(10) unsigned,
	  				`attribution_amount_withtaxes`		 tinyint(1),
	  				`attribution_product_with_reductions` tinyint(1),
	  				`attribution_categories_of_criteria` tinyint(1),
	  				`attribution_categories`	text,
	  				`active` 					tinyint(1) unsigned,
	 				`date_add` 					datetime NOT NULL,
	  				`date_upd` 					datetime NOT NULL,
	 				PRIMARY KEY  (`id_oleapromo`)
				  	)
				ENGINE='._MYSQL_ENGINE_.' default CHARSET=utf8';

	 	if (!Db::getInstance()->Execute($query))
	 		return false;

		/* Create database tables */
 		$query = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'oleapromo_lang` (
	  				`id_oleapromo` 				int(10) unsigned,
	  				`id_lang` 					int(10) unsigned,
	  				`discountobj_description` 	text,
	 				PRIMARY KEY  (`id_oleapromo`, `id_lang`)
				  	)
				ENGINE=MyISAM default CHARSET=utf8';

	 	if (!Db::getInstance()->Execute($query))
	 		return false;

	 	/* Update discount table */
	 	$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.$this->discount_table.'` LIKE \'is_for_oleamultipromo\'';
		$res = Db::getInstance()->ExecuteS($query);
		if (sizeof($res) == 0) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.$this->discount_table.'`
		 				ADD COLUMN `is_for_oleamultipromo` tinyint DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return false;
		}

		if (version_compare('1.4',	_PS_VERSION_) <= 0 && version_compare(_PS_VERSION_, '1.5') < 0) {
			$patches_dir = dirname(__FILE__).'/patches/presta1.4/';
			$override_dir = _PS_ROOT_DIR_.'/override/';
			foreach (array('classes/', 'controllers/') as $subdir) {
				if ($handle = @opendir($patches_dir.$subdir)) {
				    while (false !== ($file = readdir($handle))) {
				        if ($file != "." AND $file != ".." AND !file_exists($override_dir.$subdir.$file)) {
				            @copy($patches_dir.$subdir.$file, $override_dir.$subdir.$file);
				        }
				    }
				    closedir($handle);
				}
			}
		}

		// Upgrade
	 	$res_upgrade = $this->doUpgrade();
	 	if ($res_upgrade <> '') {
	 		error_log($res_upgrade);
	 		return false;
	 	}

	 	$this->called_from_install = true;

		require_once dirname(__FILE__).'/upgrade/install-3.2.0.php';
		if (!upgrade_module_3_2_0($this))
			return false;

		require_once dirname(__FILE__).'/upgrade/install-3.3.0.php';
		if (!upgrade_module_3_3_0($this))
			return false;

		require_once dirname(__FILE__).'/upgrade/install-3.5.0.php';
		if (!upgrade_module_3_5_0($this))
			return false;

		require_once dirname(__FILE__).'/upgrade/install-3.5.1.php';
		if (!upgrade_module_3_5_1($this))
			return false;

		require_once dirname(__FILE__).'/upgrade/install-3.5.4.php';
		if (!upgrade_module_3_5_4($this))
			return false;

		return true;
	}

	function uninstall()
	{
		if (!parent::uninstall())
			return false;

		$this->_delete_tab('AdminMultiPromos');

		if (!$this->unregisterHook('oleaCartRefreshForMultiPromo')
		OR	!$this->unregisterHook('extraRight')
		/*OR 	!$this->unregisterHook('extraLeft')*/
		OR 	!$this->unregisterHook('productFooter')
		OR 	!$this->unregisterHook('productTab')
		OR 	!$this->unregisterHook('productTabContent')
		OR 	!$this->unregisterHook('productActions')
		OR 	!$this->unregisterHook('newOrder')
		OR	!$this->unregisterHook('postUpdateOrderStatus')
		)
			return false;

		if (1) {
		if (!Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'hook WHERE `name`=\'oleaCartRefreshForMultiPromo\' '))
	 		return false;

		if (!Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'oleapromo'))
	 		return false;

		if (!Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'oleapromo_lang'))
	 		return false;

		if (!Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'oleapromo_shop'))
	 		return false;
		}
	 	return true;
	}

	public function doUpgrade()
	{
		// First part of this doUpgrade has to be kept in 1.5 for module installation (as in 1.4)
		// Second part is reserved for 1.4, the equivalent for 1.5 is then managed by install directory files
		if (
			!$this->registerHook('header')
		OR 	!$this->registerHook('extraRight')
		OR 	!$this->registerHook('extraLeft')
		OR 	!$this->registerHook('productFooter')
		OR 	!$this->registerHook('productTab')
		OR 	!$this->registerHook('productTabContent')
		OR  !$this->registerHook('productActions')
		OR  !$this->registerHook('newOrder')
		OR	!$this->registerHook('postUpdateOrderStatus')
		OR	!$this->registerHook('adminOrder')
		OR	!$this->registerHook('shoppingCart')
		)
			return 'Error upgrading hooks';

		$tables_infos = array();
		foreach (array('oleapromo_lang', 'oleapromo', $this->discount_table) as $table){
			$tables_infos[$table] = array();
			$query = 'SHOW COLUMNS FROM `'._DB_PREFIX_.$table.'`';
			$res = Db::getInstance()->ExecuteS($query);
			foreach ((array)$res as $info)
				$tables_infos[$table][$info['Field']] = $info;
		}

		if (! array_key_exists('communication_extra_right', $tables_infos['oleapromo_lang'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo_lang`
		 				ADD COLUMN `communication_extra_right` text';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('communication_product_footer', $tables_infos['oleapromo_lang'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo_lang`
		 				ADD COLUMN `communication_product_footer` text';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		// New in 2.0
		if (! array_key_exists('criteria_manufacturers', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `criteria_manufacturers` text';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
			$query = 'UPDATE `'._DB_PREFIX_.'oleapromo`
		 				SET `criteria_manufacturers` = "0"
		 				WHERE (criteria_manufacturers IS NULL)';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('attribution_manufacturers_of_criteria', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `attribution_manufacturers_of_criteria` tinyint(1) DEFAULT 1';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('attribution_manufacturers', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `attribution_manufacturers` text';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
			$query = 'UPDATE `'._DB_PREFIX_.'oleapromo`
		 				SET `attribution_manufacturers` = "0"
		 				WHERE (attribution_manufacturers IS NULL)';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('criteria_suppliers', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `criteria_suppliers` text ';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
			$query = 'UPDATE `'._DB_PREFIX_.'oleapromo`
		 				SET `criteria_suppliers` = "0"
		 				WHERE (criteria_suppliers IS NULL)';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('attribution_suppliers_of_criteria', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `attribution_suppliers_of_criteria` tinyint(1) DEFAULT 1';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('attribution_suppliers', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `attribution_suppliers` text';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
			$query = 'UPDATE `'._DB_PREFIX_.'oleapromo`
		 				SET `attribution_suppliers` = "0"
		 				WHERE (attribution_suppliers IS NULL)';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('attribution_zones_fdp', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `attribution_zones_fdp` text';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		 	$zones = array();
			foreach (Zone::getZones() as $zone)
				$zones[] = (int)($zone['id_zone']);
			$query = 'UPDATE `'._DB_PREFIX_.'oleapromo`
		 				SET `attribution_zones_fdp` = "'.implode('_', $zones).'"
		 				WHERE (attribution_zones_fdp IS NULL)';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (Configuration::get('OLEA_MAXIPROMO_NBDAYS') == '')
			Configuration::updateValue('OLEA_MAXIPROMO_NBDAYS', 30);

		if (! array_key_exists('global_family', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `global_family` VARCHAR(32)';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('global_allows_family', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `global_allows_family` TINYINT(1) DEFAULT 1';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('criteria_association_type', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `criteria_association_type` TINYINT(1) DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('attribution_products_of_criteria', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `attribution_products_of_criteria` TINYINT(1) ';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
			$query = 'UPDATE `'._DB_PREFIX_.'oleapromo`
		 				SET `attribution_products_of_criteria` =  `attribution_categories_of_criteria`
		 				WHERE (attribution_products_of_criteria IS NULL)';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}
			///XXXXXXXX
		if (! array_key_exists('criteria_product_with_quantity_price', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `criteria_product_with_quantity_price` TINYINT(1) DEFAULT 1';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
			$query = 'UPDATE `'._DB_PREFIX_.'oleapromo`
		 				SET `criteria_product_with_quantity_price` =  1
		 				WHERE 1';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('attribution_product_with_quantity_price', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `attribution_product_with_quantity_price` TINYINT(1) DEFAULT 1';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
			$query = 'UPDATE `'._DB_PREFIX_.'oleapromo`
		 				SET `attribution_product_with_quantity_price` =  1
		 				WHERE 1';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}
			/////XXXXXXXX
		/* Update discount table */
		if (! array_key_exists('oleamultipromo_sending_method', $tables_infos[$this->discount_table])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.$this->discount_table.'`
		 				ADD COLUMN `oleamultipromo_sending_method` TINYINT(1) DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('oleamultipromo_id_cart_generating', $tables_infos[$this->discount_table])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.$this->discount_table.'`
		 				ADD COLUMN `oleamultipromo_id_cart_generating` INT(10) DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('oleamultipromo_id_order_generating', $tables_infos[$this->discount_table])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.$this->discount_table.'`
		 				ADD COLUMN `oleamultipromo_id_order_generating` INT(10) DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('oleamultipromo_is_sent_by_email', $tables_infos[$this->discount_table])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.$this->discount_table.'`
		 				ADD COLUMN `oleamultipromo_is_sent_by_email` TINYINT(1) DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('oleamultipromo_date_from_of_order', $tables_infos[$this->discount_table])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.$this->discount_table.'`
		 				ADD COLUMN `oleamultipromo_date_from_of_order` TINYINT(1) DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('oleamultipromo_validity_days', $tables_infos[$this->discount_table])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.$this->discount_table.'`
		 				ADD COLUMN `oleamultipromo_validity_days` INT(10) DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}


		if (! array_key_exists('sending_method', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `sending_method` TINYINT(1) DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (version_compare('1.4', _PS_VERSION_) <= 0)
		 	Configuration::updateValue('OLEA_MAXIPROMO_MAIL_ORDERSTATE', Configuration::get('PS_OS_PAYMENT'));
		else
			Configuration::updateValue('OLEA_MAXIPROMO_MAIL_ORDERSTATE', _PS_OS_PAYMENT_);


		if (! array_key_exists('mail_description', $tables_infos['oleapromo_lang'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo_lang`
		 				ADD COLUMN `mail_description` TEXT';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('mail_message', $tables_infos['oleapromo_lang'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo_lang`
		 				ADD COLUMN `mail_message` VARCHAR(256)';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('oleamultipromo_mail_message', $tables_infos[$this->discount_table])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.$this->discount_table.'`
		 				ADD COLUMN `oleamultipromo_mail_message` VARCHAR(256)';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('mail_discount_type', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `mail_discount_type` TINYINT(1)  DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('mail_discount_value', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `mail_discount_value` DECIMAL(10,2)  DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('mail_minimal', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `mail_minimal` DECIMAL(10,2) DEFAULT 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('mail_cumulable', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `mail_cumulable` TINYINT(1) DEFAULT 1';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('mail_cumulable_reduction', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `mail_cumulable_reduction` TINYINT(1) DEFAULT 1';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('mail_categories', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `mail_categories` TEXT';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
			$query = 'UPDATE `'._DB_PREFIX_.'oleapromo`
		 				SET `mail_categories` = ""
		 				WHERE (mail_categories IS NULL)';
		 	if (!Db::getInstance()->Execute($query))
		 		return $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('mail_validity_days', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `mail_validity_days` INT(6) default 0';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('mail_date_from_of_order', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `mail_date_from_of_order` TINYINT(1) DEFAULT 1';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('mail_date_from', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `mail_date_from` DATE';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}
		if (! array_key_exists('mail_date_to', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `mail_date_to` DATE';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}

		// Following fields are not used on 1.4, but keeped for common ObjectModel declaration with 1.5
		if (! array_key_exists('oleamultipromo_discount_key', $tables_infos[$this->discount_table])) { // Not used on 1.4, but keep for common ObjectModel declaration with 1.5
			$query = 'ALTER TABLE `'._DB_PREFIX_.$this->discount_table.'`
		 				ADD COLUMN `oleamultipromo_discount_key` VARCHAR(256)';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('global_cart_rule_priority', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `global_cart_rule_priority` INT(6) default 1';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}

		if (! array_key_exists('global_cart_rule_exclusion', $tables_infos['oleapromo'])) {
			$query = 'ALTER TABLE `'._DB_PREFIX_.'oleapromo`
		 				ADD COLUMN `global_cart_rule_exclusion` TEXT';
		 	if (!Db::getInstance()->Execute($query))
		 		return  $this->l('Error upgrading database').'('.__LINE__.')';
		}

		/* Create new hook */
		/*
		$query = "INSERT IGNORE INTO `"._DB_PREFIX_."hook`
	 		(`id_hook`, `name`, `title`, `description`, `position`) VALUES
	 		(NULL, 'oleaMultiPromoProductsProperties', 'Hook for OleaMultiPromos Module managing Product::getProductsProperties', NULL, '0')";
	 	if (!Db::getInstance()->Execute($query))
	 		return $this->l('Error upgrading database').'('.__LINE__.')';
	 	*/
		if (
			!$this->registerHook('oleaMultiPromoProductsProperties')
			)
			return $this->l('Error upgrading database').'('.__LINE__.')';

		// -------------------  SECOND PART ----------------------------
		if (version_compare(_PS_VERSION_, '1.5') < 0) {
			// In this branch, updates for 1.4 after common files between 1.4 and 1.5
			// Update for 1.5 are in the install directory
		}

		Configuration::updateValue('OLEA_MLTPROM_VERSION', $this->version);
		return '';
	}

	public function is_version_ok() {
		if (version_compare('1.5', _PS_VERSION_)<0)
			return true;
		$db_version = Configuration::get('OLEA_MLTPROM_VERSION');
		if (version_compare ($db_version, $this->version) < 0)
			return false;
		else
			return true;
	}

	public function getPatchesErrors () {
		$errors = array();

		if (version_compare('1.5', _PS_VERSION_) <= 0) {
			$scans = array( /*    Method            Directory          	 	  	File                        Seached String  */
							array('autoAddToCart',	'override/classes',			'CartRule.php',	 	'oleaCartRefreshForMultiPromo15'),
							);
		} elseif (version_compare('1.4', _PS_VERSION_) <= 0) {
			$scans = array( /*    Method            Directory             File                        Seached String  */
							array('preProcess',	'controllers',			'CartController.php',		'oleaCartRefreshForMultiPromo'),
							array('preProcess',	'override/controllers',	'OrderController.php',	 	'oleaCartRefreshForMultiPromo'),
							array('preProcess',	'override/controllers',	'OrderController.php',	 	'if ($this->step <= 2)'),
							array('preProcess',	'override/controllers',	'OrderOpcController.php',	'oleaCartRefreshForMultiPromo'),
							array('refreshVouchers',	'modules/blockcart',	'ajax-cart.js',		'ajaxCart.oleaAddVouchers'),
							array('updateCartSummary',	'themes/'._THEME_NAME_.'/js',	'cart-summary.js',		'Oleacorner'),
							);
		} elseif (version_compare('1.3', _PS_VERSION_) <= 0)  {
			$scans = array( /*    Method            Directory             File                        Seached String  */
							array('[global file]',		'',				'cart.php',				'oleaCartRefreshForMultiPromo'),
							array('[global file]',		'',				'order.php',	 		'oleaCartRefreshForMultiPromo'),
							array('refreshVouchers',	'modules/blockcart', 'ajax-cart.js',	'ajaxCart.oleaAddVouchers'),
							/*array('updateCartSummary',	'themes/'._THEME_NAME_.'/js',	'cart-summary.js',		'Oleacorner'),*/
							);
		} else {
			$scans = array( /*    Method            Directory             File                        Seached String  */
							array('[global file]',		'',				'cart.php',				'oleaCartRefreshForMultiPromo'),
							array('[global file]',		'',				'order.php',	 		'oleaCartRefreshForMultiPromo'),
							array('refreshVouchers',	'modules/blockcart', 'ajax-cart.js',	'ajaxCart.oleaAddVouchers'),
							/*array('updateCartSummary',	'themes/'._THEME_NAME_.'/js',	'cart-summary.js',		'Oleacorner'),*/
							);
		}

		foreach ($scans as $scan) {
			$fname = _PS_ROOT_DIR_.'/'.$scan[1].'/'.$scan[2];
			$is_ok = false;
			if ($handle = @fopen($fname, 'r')) {
				$content = fread ($handle, filesize($fname));
				$pos = strpos ($content, $scan[3]);
		        if ($pos !== false)
		        	$is_ok = true;
				fclose ($handle);
			}
			if (!$is_ok)
				$errors [] = $scan;
		}


		return $errors;
	}

	function _create_tab($class_name, $id_parent, $tab_names, $logo_name='') {
		$id_tab = Tab::getIdFromClassName($class_name);

		$languages = Language::getLanguages(false);
		$en_name = array_key_exists('en', $tab_names) ? $tab_names['en'] :'NewTab';

		$maintab = new Tab($id_tab);
		$maintab->id_parent = $id_parent;
		$maintab->class_name = $class_name;
		$maintab->module = $this->name;
		foreach ($languages AS $lang)
			$maintab->name[$lang['id_lang']] = (array_key_exists($lang['iso_code'], $tab_names)
							? $tab_names[$lang['iso_code']]
							: $en_name);
		if (!$maintab->save())
			return 0;
		if (version_compare(_PS_VERSION_, '1.3') < 0) {
			if ($logo_name == '')
				$logo_name = 'logo.gif';
 			@copy (dirname(__FILE__).'/'.$logo_name, _PS_ROOT_DIR_.'/img/t/'.$class_name.'.gif');
 			@copy (dirname(__FILE__).'/'.$logo_name, _PS_ROOT_DIR_.'/img/t/'.$maintab->id.'.gif');
		}
		return $maintab->id;
	}

	function _delete_tab($class_name) {
		$tab = new Tab(Tab::getIdFromClassName ($class_name));
		if ($tab->id)
			$tab->delete();
	}



	//***********************************************************************
	//     DISPLAYS
	//***********************************************************************

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('submitDoUpgrade')) {
			$result_upgrade = $this->doUpgrade();
			if ($result_upgrade <>'')
				$output = $this->displayError($result_upgrade).$output;
		}
		if (! self::is_version_ok()) {
			return $output.$this->displayFormForUpgrade();
		} else {
			return $output.$this->displayForm();
		}
	}

	public function displayFormForUpgrade ()
	{
		$retour = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
			<p>'.$this->l('New version of the module has been installed').'<br />
			'.$this->l('You need to upgrade it').'</p>
			<center><input type="submit" name="submitDoUpgrade" value="'.$this->l('Upgrade').'" class="button" /></center>
			</fieldset>
		</form>';

		return $retour;
	}

	public function getModuleUpgradeFixExplanation () {
		if (Configuration::get('OLEA_MLTPROM_MODULEUPGRADEOK'))
			return '';

		$lang = new Language(Context::getContext()->cookie->id_lang);

		$fh = @fopen(dirname(__FILE__).'/fixexplanation/fixexplanation_'.$lang->iso_code.'.html', 'r');
		if (! $fh)
			$fh = @fopen(dirname(__FILE__).'/fixexplanation/fixexplanation_en.html', 'r');

		if ($fh) {
			$retour = fread ($fh,1000000);
			fclose ($fh);
		} else {
			$retour = 'See /modules/'.dirname(__FILE__).'/explanation directory content to know haow to fix this issue';
		}

		return $retour;
	}

	public function getOverrideModifs351Explanation()
	{
		if (! preg_match('/oleamultipromo_id_cart_generating/', Tools::file_get_contents(_PS_OVERRIDE_DIR_.'classes/CartRule.php')))
			return '';

		$lang = new Language(Context::getContext()->cookie->id_lang);

		$retour = Tools::file_get_contents(dirname(__FILE__).'/fixexplanation/overridesmodifs351_'.$lang->iso_code.'.html');
		if ($retour === false)
			$retour = Tools::file_get_contents(dirname(__FILE__).'/fixexplanation/overridesmodifs351_en.html');
		if ($retour === false)
			$retour = 'See /modules/'.basename(dirname(__FILE__)).'/explanation directory content to know how to modify the overrides';

		return $retour;
	}
	public function displayForm()
	{

		$retour = '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">';

		$errors = $this->getPatchesErrors();
		if (count($errors)) {
			$retour .= '<fieldset class="width3"><legend>'.$this->l('Patches to be done').'</legend>';
			foreach ($errors as $the_error)  {
				$pos = strpos($the_error[1], 'override');
				$retour .= '<p>'.$this->l('The method ').' '.$the_error[0].'() '.(($pos===0) ?$this->l('must be overridden in file ') :$this->l('must be patched in file ')).' /'.$the_error[1].'/'.$the_error[2].'</p>';
			}
			$retour .= '</fieldset>';
		}

		$fixExplanation = $this->getModuleUpgradeFixExplanation();
		if ($fixExplanation) {
			$retour .= '<fieldset class=" "><legend>'.$this->l('Core fix to be done').'</legend>';
			$retour .= $fixExplanation;
			$retour .= '</fieldset>';
		}

		$overrideExplanation = $this->getOverrideModifs351Explanation();
		if ($overrideExplanation) {
			$retour .= '<fieldset class=" "><legend>'.$this->l('Override modification to be done').'</legend>';
			$retour .= $overrideExplanation;
			$retour .= '</fieldset>';
		}

		$cookie = Context::getContext()->cookie;
		$path = '../modules/'.$this->name.'/';
		$lang = new Language($cookie->id_lang);
		$filename = 'readme_'.$lang->iso_code.'.pdf';
		if (!file_exists(dirname(__FILE__).'/'.$filename)) {
			$filename = 'readme_en.pdf';
			if (!file_exists(dirname(__FILE__).'/'.$filename))
				$filename = '';
		}
		if (version_compare('1.5', _PS_VERSION_) <= 0) {
			$subtab = Tab::getInstanceFromClassName('AdminMultiPromos');
			$maintab = new Tab($subtab->id_parent);
			$menu_path = ' : '.$maintab->name[$this->context->cookie->id_lang].'>>'.$subtab->name[$this->context->cookie->id_lang];
		}
		else
			$menu_path = $this->l('Catalog>>MaxiPromo');

		$support_link = ($lang->iso_code == 'fr')
		? 'https://addons.prestashop.com/fr/ecrire-au-developpeur?id_product=3462'
		    : 'https://addons.prestashop.com/en/write-to-developper?id_product=3462';
		$retour .= '
			<p class="clear"></p>
			<fieldset class="width3"><legend>'.$this->l('Information').'</legend>
			<p>'.$this->l('Configuration done through one of the admin tab').$menu_path.'</p>
			<p>'.$this->l('Module version').' '.$this->version.' '.$this->l('developped by').' <b style="font-size:1.2em;" >Oleacorner.Com</b>
			    <a class="button" target="_blank" href="'.$support_link.'" ><b>'.$this->l('Support',__CLASS__).'</b> </a>  ';
		if ($filename <> '')
			$retour .= '<b><a class="button" href="../modules/'.$this->name.'/'.$filename.'" target="_blank" >Documentation</a></b>';
		$retour .= ' <a class="button" target="_blank" href="http://addons.prestashop.com/'.(($lang->iso_code == 'fr') ?'fr' :'en').'/8_oleacorner" ><b>'.$this->l('Our modules',__CLASS__).'</b> </a>
		    </p></fieldset>

		</form>';

		return $retour;
	}

	//***********************************************************************
	//     HOOKS
	//***********************************************************************
	public function oleaCartRefreshForMultiPromo15 ($params) {

		if (version_compare(_PS_VERSION_, '1.5') < 0) {
			error_log('Method '.__METHOD__.' not callable before ps1.5, line '.__LINE__.' of file '.__FILE__);
			return;
		}
		if (!$this->is_version_ok())
			return;
		//$memory_init = memory_get_usage();
		//$memory_init_peak = memory_get_peak_usage();
		$cart = $params['cart'];

		if (!Validate::isLoadedObject($cart) OR (int)$cart->id==0 OR (int)$cart->id_currency==0)
			return;
		include_once (dirname(__FILE__).'/Oleapromo.php');
		include_once (dirname(__FILE__).'/OleaDiscountMulti.php');
		$oleadiscounts_in_cart = OleaDiscountMulti::getAllDiscountsInCart($cart->id);

		// In case customer is modifying discounts list, all oleadiscount have to be removed first. They will be readded later, by another hookcall
		if (isset($params['removeOleapromo']) AND $params['removeOleapromo'] == 1) {
			foreach ($oleadiscounts_in_cart as $discount) {
				if ($discount->is_for_oleamultipromo==1 AND $discount->oleamultipromo_is_sent_by_email!=OleaDiscountMulti::SEND_DONE)
					$cart->deleteDiscount($discount->id);
			}
			$d = $cart->getDiscounts(false, true);
			return;
		}
		$oleadiscounts_associated_to_cart = OleaDiscountMulti::getMultiDiscountsOfCart($cart->id, $cart->id_lang); // Including discounts by email

		// In case of double-click on '+' or '-' in the order-summary page, it may happen that the discount is added in double
		/* No more needed in 1.5
		foreach ($oleadiscounts_associated_to_cart as $discount_info) {
			if ($discount_info['occurrence'] > 1) {
				Db::getInstance()->ExecuteS('DELETE FROM `'._DB_PREFIX_.'cart_discount` where id_discount='.(int)$discount_info['id_discount'].' AND id_cart='.(int)$cart->id);
				Db::getInstance()->ExecuteS('INSERT INTO `'._DB_PREFIX_.'cart_discount` (id_discount, id_cart) VALUES ('.(int)$discount_info['id_discount'].','.(int)$cart->id.')');
			}
		}
		*/

		// Tester si des discount non oleadiscounts sont non cumulables avec d'autres bons
		/* No more necessary. Will be replaced by the cart_rule compatibility parameter
		$cart_has_non_cumulative_discounts = false;
		$cart_has_discounts = false;
		foreach ($oleadiscounts_in_cart as $discount) {
			if ($discount->is_for_oleamultipromo<>1) {
				$cart_has_discounts = true;
				if (!$discount->cumulable)
					$cart_has_non_cumulative_discounts = true;
			}
		}
		*/
		$cart_has_discounts = false;
		$cart_has_non_cumulative_discounts = false;
		$this->_init_cache($cart, $cart_has_discounts, $cart_has_non_cumulative_discounts);
		$customer = $this->_cache['customer'];

		//$nb_orders_placed = Order::getCustomerNbOrders((int)$cart->id_customer);
		//$promos_active = ($cart_has_non_cumulative_discounts)
		//			? array()
		//			: Oleapromo::getAllActive($this->_cache['groups'], $cart_has_discounts, $this->_cache['nb_orders_placed']); // si des bons non cumulables, ne récuperer que les active cumulables aux bons
		$promos_active = $this->_cache['promos_active'];
		$cart_elements = array();
		Oleapromo::buildCartElements ($cart, $cart_elements);

		$promoinfo_effective = array();
		foreach ($promos_active as $id_promo=>$promo) {
			if (version_compare('1.3', _PS_VERSION_) <= 0 ) // case 1.3/1.4/1.5
				$promo->computePromoValues ($promoinfo_effective, $cart_elements, Group::getPriceDisplayMethod((int)(($customer->id) ?$customer->id_default_group :1)), $cart);
			else
				$promo->computePromoValues ($promoinfo_effective, $cart_elements, (CONFIGURATION::get('PS_PRICE_DISPLAY')==1) ?PS_TAX_EXC :PS_TAX_INC, $cart);
		}

		$discounts_to_add = array();
		$discounts_by_mail = array();
		$promoinfo_for_global = array();
		$allows_others = true;
		$family_to_stop = array();
		$has_free_shipping = false;
		foreach ($promoinfo_effective as $promoinfo) {
			if ($allows_others AND !in_array($promoinfo['promo']->global_family, $family_to_stop)) {
				$discount_name = OleaDiscountMulti::buildDiscountName($promoinfo['id_oleapromo'], $cart->id, $promoinfo['id_association']);
				$discount = OleaDiscountMulti::buildDiscountOfPromoinfo ($discount_name, $promoinfo, $oleadiscounts_in_cart, $cart, $oleadiscounts_associated_to_cart);
				switch ($promoinfo['promo']->sending_method) {
					case Oleapromo::SENDING_METHOD_MAIL:
						if ($promoinfo['amount']>0 OR $promoinfo['free_shipping']) {
							$discounts_by_mail[] = $discount;
							$allows_others = ($promoinfo['promo']->global_allows_others==1) ?true : false;
							if ($promoinfo['promo']->global_allows_family<>1)
								$family_to_stop[] = $promoinfo['promo']->global_family;
						}
						break;
					default:
						if ($promoinfo['cumul_on_global']==0) {
							if ($promoinfo['amount']>0 OR ($promoinfo['free_shipping'] AND !$has_free_shipping)) {
								$discounts_to_add[] = $discount;
								$allows_others = ($promoinfo['promo']->global_allows_others==1) ?true : false;
								if ($promoinfo['free_shipping'])
									$has_free_shipping = true;
								if ($promoinfo['promo']->global_allows_family<>1)
									$family_to_stop[] = $promoinfo['promo']->global_family;
							}
						} else {
							$promoinfo_for_global[] = $promo;
						}
						break;
				}
			}
		}
//		DebugFileLogger::getInstance()->log("XXXX module AddtoCart, nb_discounts =".count($discounts_to_add));
//		$dbg = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,4);
//		DebugFileLogger::getInstance()->log("XXXX module AddtoCart, backtrace =".print_r($dbg, true));
		foreach ($discounts_to_add as $discount) {
			if (array_key_exists($discount->id, $oleadiscounts_in_cart)) {
				//error_log ('Already in cart, id_discount='.$discount->id);
				unset($oleadiscounts_in_cart[$discount->id]);
			} else {
				//error_log('Adding to cart id_discount='.$discount->id);
				if ($discount->id<>0) {
					$check = $discount->checkValidity(Context::getContext(), false, false);
					if ($check)
						$v = $cart->addCartRule($discount->id);
					//error_log('Stat adding rule = '.(int)$v);
				}
			}
		}

		foreach ($oleadiscounts_in_cart as $oleadiscount) {
			if ($oleadiscount->is_for_oleamultipromo==1 AND $oleadiscount->oleamultipromo_is_sent_by_email!=OleaDiscountMulti::SEND_DONE) {
				$cart->removeCartRule($oleadiscount->id);
				$oleadiscount->active = 0;
				$oleadiscount->save();
			}
		}

		/* Cache refresh */
		Cache::clean('Cart::getCartRules'.$this->id.'-'.CartRule::FILTER_ACTION_ALL);
		Cache::clean('Cart::getCartRules'.$this->id.'-'.CartRule::FILTER_ACTION_SHIPPING);
		Cache::clean('Cart::getCartRules'.$this->id.'-'.CartRule::FILTER_ACTION_REDUCTION);
		Cache::clean('Cart::getCartRules'.$this->id.'-'.CartRule::FILTER_ACTION_GIFT);

		Cache::clean('Cart::getCartRules_'.$this->id.'-'.CartRule::FILTER_ACTION_ALL). '-ids';
		Cache::clean('Cart::getCartRules_'.$this->id.'-'.CartRule::FILTER_ACTION_SHIPPING). '-ids';
		Cache::clean('Cart::getCartRules_'.$this->id.'-'.CartRule::FILTER_ACTION_REDUCTION). '-ids';
		Cache::clean('Cart::getCartRules_'.$this->id.'-'.CartRule::FILTER_ACTION_GIFT). '-ids';

		Cache::clean('getContextualValue_'.$cart->id.'_*');

		Oleapromo::updateMailDiscountOfCart($cart, $discounts_by_mail);


		//error_log('Memoy used by hook = '. ((memory_get_usage()-$memory_init)/1024).'ko');
		//error_log('Memoy peak by hook = '. ((memory_get_peak_usage()-$memory_init_peak)/1024).'ko');
	}

	public function oleaCheckCartRulesIsForCart($id_cart_rule, $id_cart)
	{
		include_once (dirname(__FILE__).'/Oleapromo.php');

		return Oleapromo::oleaCheckCartRulesIsForCart((int)$id_cart_rule, (int)$id_cart);
	}

	function hookHeader($params)
	{
		// OK 1.5
		if (version_compare('1.5', _PS_VERSION_) <= 0) {
			$this->context->controller->addCSS($this->_path.'multipromos.css', 'all');
		}
	}


	public function hookExtraRight ($params) {
		// OK 1.5

		$id_product = (int)Tools::getValue('id_product');
		if ($id_product ==0)
			return;

		$cart = $params['cart'];
		$this->_init_cache($cart, false, false, $id_product);
		$this->_init_cache_promos_of_product ($id_product);

		$promos_extra_right = array();
		foreach ($this->_cache['promos_of_product_'.$id_product] as $promo) {
			if ($promo->communication_extra_right <> '') {
				$promo->communication_extra_right_modified = $this->replaceString($promo->communication_extra_right, $promo->id);
				$promos_extra_right[$promo->id] = $promo;
			}
		}

		$this->context->smarty->assign(array(
				'olea_promo_extra_right' => $promos_extra_right,
		        'olea_isps16' => (int) (version_compare('1.6', _PS_VERSION_) <= 0),
			));

		return $this->display(__FILE__, 'extra_right.tpl');
	}

	public function hookProductFooter ($params) {
		// OK 1.5

		$id_product = (int)Tools::getValue('id_product');
		if ($id_product ==0)
			return;

		$cart = $params['cart'];
		$this->_init_cache($cart, false, false, $id_product);
		$this->_init_cache_promos_of_product ($id_product);

		$promos_product_footer = array();
		foreach ($this->_cache['promos_of_product_'.$id_product] as $promo) {
			if ($promo->communication_product_footer <> '') {
				$promo->communication_product_footer_modified = $this->replaceString($promo->communication_product_footer, $promo->id);
				$promos_product_footer[$promo->id] = $promo;
			}
		}

		$this->context->smarty->assign(array(
				'olea_promo_product_footer' => $promos_product_footer,
		        'olea_isps16' => (int) (version_compare('1.6', _PS_VERSION_) <= 0),
		));

		return $this->display(__FILE__, 'product_footer.tpl');
	}

	public function hookNewOrder ($params) {
		// OK 1.5
		$id_order = (int)$params['order']->id;

		if (version_compare('1.5', _PS_VERSION_)<=0) {
			$update_code = ' , code = oleamultipromo_discount_key ';  // Should not reactivate the cart_rule sent by email
			if ((int)$params['orderStatus']->id  == (int)Configuration::get('OLEA_MAXIPROMO_MAIL_ORDERSTATE'))
				$update_code .= ' , active=1 ';
			else
				$update_code .= ' , active=0 ';
		} else
			$update_code = '';

		$sqlReq = 'UPDATE `'._DB_PREFIX_.$this->discount_table.'`
					SET oleamultipromo_id_order_generating = '.(int)$params['order']->id
					.$update_code.'
					WHERE oleamultipromo_id_cart_generating = '.(int)$params['order']->id_cart;
			Db::getInstance()->Execute($sqlReq);

	}

	public function hookPostUpdateOrderStatus ($params) {
		// OK 1.5
		require_once (dirname(__FILE__).'/Oleapromo.php');

		$state = (int)Configuration::get('OLEA_MAXIPROMO_MAIL_ORDERSTATE');
		if (intval($params['newOrderStatus']->id) == $state)
			$has_validated_state = true;
		else {
			$has_validated_state = Db::getInstance()->getValue('
				SELECT `id_order_state`
				FROM `'._DB_PREFIX_.'order_history`
				WHERE `id_order` = '.(int)($params['id_order']).' AND `id_order_state` = '.(int)$state.'
				ORDER BY `date_add` DESC, `id_order_history` DESC');
		}

		if ($has_validated_state) {
			Oleapromo::sendPromoByEmailForOrder ((int)$params['id_order']);
		}
	}

	public function hookAdminOrder ($params) {

		require_once (dirname(__FILE__).'/OleaDiscountMulti.php');

		$discounts = OleaDiscountMulti::getAllPendingOrSentMailDiscountOfOrder($params['id_order']);

		if (count($discounts) == 0) {
			return '';
		}

		if (version_compare('1.5', _PS_VERSION_) <= 0) {
			$retour = '<table class="table" cellspacing=0 cellpadding=0 ><tr><th colspan="2">'.$this->l('Discounts sent by mail to customer').'</th></tr>';
			foreach ($discounts as $discount) {
				if ($discount['reduction_percent'] > 0) {
					$value = number_format($discount['reduction_percent'],2).'%';
				} elseif ($discount['reduction_amount'] > 0) {
					$value = Tools::displayPrice($discount['reduction_amount'], (int)$discount['reduction_currency']);
				} elseif ($discount['free_shipping'] == 1) {
					$value = $this->l('Port fee offered');
				} else
					$value = '--';
				$retour .= '<tr><td><b>'.$discount['code'].'</b></td>
								<td>'.(($discount['oleamultipromo_is_sent_by_email']==OleaDiscountMulti::SEND_DONE) ?$this->l('Sent') :$this->l('Not sent yet')).'</td>
								</tr>
							<tr><td style="text-align:center">'.$value.'</td><td >'.$discount['name'].'</td></tr>';
				}
			$retour .= '</table>';
		} else {
			$retour = '<table class="table" cellspacing=0 cellpadding=0 style="width:350px"><tr><th colspan="2">'.$this->l('Discounts sent by mail to customer').'</th></tr>';
			foreach ($discounts as $discount) {
				//$retour .= '<p><b>'.$discount['name'].'&nbsp:</b><br />
				//		'.$discount['description'].'</p>';
				if ($discount['id_discount_type'] == 1) {
					$value = number_format($discount['value'],2).'%';
				} elseif ($discount['id_discount_type'] == 2) {
					if (version_compare('1.3', _PS_VERSION_) <= 0)
						$value = Tools::displayPrice($discount['value'], (int)$discount['id_currency']);
					else
						$value = Tools::displayPrice($discount['value'], new Currency(Configuration::get('PS_CURRENCY_DEFAULT')));
				} elseif ($discount['id_discount_type'] == 3) {
					$value = $this->l('Port fee offered');
				} else
					$value = '--';
				$retour .= '<tr><td><b>'.$discount['name'].'</b></td>
								<td>'.(($discount['oleamultipromo_is_sent_by_email']==OleaDiscountMulti::SEND_DONE) ?$this->l('Sent') :$this->l('Not sent yet')).'</td>
								</tr>
							<tr><td style="text-align:center">'.$value.'</td><td >'.$discount['description'].'</td></tr>';
				}
			$retour .= '</table>';
		}

		return '<br /><div >
				<fieldset >
				<legend><img alt="'.$this->name.'" src="../modules/'.$this->name.'/logo.gif">
					'.$this->displayName.'
				</legend>'
				.$retour
				.'</fieldset></div>';
	}

	public function hookShoppingCart ($params) {
		require_once dirname(__FILE__).'/OleaDiscountMulti.php';

		require_once dirname(__FILE__).'/OleaDiscountMulti.php';
		$all_discounts = OleaDiscountMulti::getMultiDiscountsOfCart($params['cart']->id);

		$discounts = array();
		$total = 0;
		foreach ($all_discounts as $discount) {
			if ($discount['oleamultipromo_sending_method'] == OleaDiscountMulti::SENDING_METHOD_MAIL
				AND $discount['oleamultipromo_is_sent_by_email'] == OleaDiscountMulti::SEND_PENDING) {
				$discounts[] = $discount;
				if (version_compare('1.5', _PS_VERSION_) <= 0) {
					$total += $discount['reduction_amount']; // necessarely in cart currency
				} else {
					if ($discount['id_discount_type'] == 2)
						$total += $discount['value'];
				}
			}
		}
		if (version_compare(_PS_VERSION_, '1.3') < 0) {
			$currency = new Currency($params['cookie']->id_currency);
			$total = Tools::convertPrice($total, $currency);
		}

		$this->context->smarty->assign(array(
				'olea_promo_total_by_mail' => $total,
				'olea_promo_id_currency' => $params['cookie']->id_currency,
				'olea_promo_discounts' => $discounts,
		        'olea_isps16' => (int) (version_compare('1.6', _PS_VERSION_) <= 0),
		));

		return $this->display(__FILE__, 'shoppingcart.tpl');
	}

	public function hookOleaMultiPromoProductsProperties ($params) {
		$cart = Context::getContext()->cart;

		if (version_compare('1.5', _PS_VERSION_) <= 0) {
			error_log('Method '.__CLASS__.' not used in 1.5, at line '.__LINE__.' in '.__FILE__);
			return '';
		}

		$id_lang = (int)$params['id_lang'];
		if ($id_lang == 0)
			$id_lang = Configuration::get('PS_LANG_DEFAULT');
		$query_result = (array)$params['query_result'];

		require_once (_PS_ROOT_DIR_.'/modules/oleamultipromos/Oleapromo.php');
		$this->_init_cache($cart, false, false);
		foreach ($query_result AS &$row) {
			$this->_init_cache_promos_of_product($row['id_product']);
			$row['oleapromo_family'] = $this->_cache['promos_of_product_'.$row['id_product']];
		}

		return serialize($query_result);
	}

	public function hookOleaextcartruleReaffectFamily ($params)
	{
		if ((int)Configuration::get('OLEA_MAXIPROMO_EXTRULEFAMILY') == $params['id_old_family'])
			Configuration::updateValue('OLEA_MAXIPROMO_EXTRULEFAMILY', (int)$params['id_new_family']);

		require_once (_PS_ROOT_DIR_.'/modules/oleamultipromos/Oleapromo.php');
		Oleapromo::reaffect_extcartrulefamily($params['id_old_family'], $params['id_new_family']);
	}

	//***********************************************************************
	//     TOOLS
	//***********************************************************************

	private function _init_cache ($cart, $cart_has_discounts, $cart_has_non_cumulative=false, $id_product=null) {
		// OK 1.5
		if (! isset($this->_cache['customer']))
			$this->_cache['customer'] = new Customer ((int)$cart->id_customer);
		$customer = $this->_cache['customer'];

		if (! isset($this->_cache['groups']))
			$this->_cache['groups'] = ($customer->id)
					? (((int)Configuration::get('OLEA_MAXIPROMO_ONLYDEFAULTGROUPS')) ?array($customer->id_default_group => $customer->id_default_group) :$customer->getGroups())
					: array(1=>1);

		if (! isset($this->_cache['nb_orders_placed']))
			$this->_cache['nb_orders_placed'] = ((int)$cart->id_customer) ?Order::getCustomerNbOrders((int)$cart->id_customer) :0;

		include_once (dirname(__FILE__).'/Oleapromo.php');
		if (! isset($this->_cache['promos_active']))
			if ($cart_has_non_cumulative)
				$this->_cache['promos_active'] = array();
			else
				$this->_cache['promos_active'] = Oleapromo::getAllActive($this->_cache['groups'], $cart_has_discounts, $this->_cache['nb_orders_placed'], $cart);

		if ($id_product != null) {
			if  (! isset($this->_cache['product'])) {
				$this->_cache['product'] = new Product($id_product);
			}
		}
	}

	private function _init_cache_promos_of_product ($id_product) {
		// OK 1.5
		if (! isset($this->_cache['promos_of_product_'])) {
			$this->_cache['promos_of_product_'.$id_product] = array();

			$found_family = array();
			foreach ($this->_cache['promos_active'] as $promo) {
				if ($promo->isApplicableToProduct($id_product)
					AND ($promo->global_family=='' OR !isset($found_family[$promo->global_family]))) {
						$this->_cache['promos_of_product_'.$id_product][$promo->id] = $promo;
						$found_family[$promo->global_family] = 1;
				}
			}
		}
	}

	function filterCartRulesForAdmin (&$cart_rules_to_filter) {
		foreach ($cart_rules_to_filter['selected'] as $k=>$v) {
			if (! ((int)$v['is_for_oleamultipromo']==0))
				unset($cart_rules_to_filter['selected'][$k]);
		}
		foreach ($cart_rules_to_filter['unselected'] as $k=>$v) {
			if (! ((int)$v['is_for_oleamultipromo']==0))
				unset($cart_rules_to_filter['unselected'][$k]);
		}
	}

	public function replaceString ($chaine, $id_promo) {
		// OK 1.5
		if (strpos($chaine, '#') === false)
			return $chaine;

		if ($this->_front_values == null) {
			$this->_front_values = array();
			// product_price computation placed here for performance reason.
			if (version_compare('1.3', _PS_VERSION_) <= 0)
				$use_tax = Product::$_taxCalculationMethod == PS_TAX_INC;
			else
				$use_tax = ((Configuration::get('PS_PRICE_DISPLAY') == 1) ?false :true);

			$this->_cache['product']->price_forfront = $this->_cache['product']->getPrice();

			foreach ($this->_cache['promos_of_product_'.$this->_cache['product']->id] as $promo) {
				$this->_front_values[$promo->id] = Oleapromo::getInfoForFront($promo, $this->_cache['product']);
			}
		}

		$values = $this->_front_values[$id_promo];
		$retour = str_replace('#NT#', $values['nb_all_products'],
						str_replace('#NPR#', $values['nb_reduced_price'],
						str_replace('#NPF#', $values['nb_full_price'],
						str_replace('#RM#', $values['reduc_amount'],
						str_replace('#R%0#', number_format ((float)$values['reduc_percent'],0).'%',
						str_replace('#R%1#', number_format ((float)$values['reduc_percent'],1).'%',
						str_replace('#R%2#', number_format ((float)$values['reduc_percent'],2).'%',
						str_replace('#PB#', $values['price_base'],
						str_replace('#PR#', $values['price_reduced'],
						str_replace('#PF#', $values['price_final'],
						str_replace('#HD#', $values['hidden'],
						$chaine)))))))))));

		return $retour;
	}

	public static function getAdminTokenLite($tab)
	{
		// OK 1.5
		$cookie = Context::getContext()->cookie;
		return Tools::getAdminToken($tab.(int)(Tab::getIdFromClassName($tab)).(int)($cookie->id_employee));
	}

	public function getL($str, $id_lang) {
		// OK 1.5
		switch ($str) {
		//case 'XXX':			return $this->l('XXX');
		case 'pattern_mail_amount' : return $this->l('%s offered for an order minimum of %s, up to %s', false, $id_lang);
		case 'pattern_mail_percent' : return $this->l('%d% offered for an order minimum of %s, up to %s', false, $id_lang);
		case 'pattern_mail_port' : return $this->l('Port fee offered for an order minimum of %s, up to %s', false, $id_lang);
		case 'pattern_mail_other' : return $this->l('Contact us for the conditions', false, $id_lang);
		case 'Your vouchers' : return $this->l('Your vouchers', false, $id_lang);
		case 'Associated discounts sent by mail:' : return $this->l('Associated discounts sent by mail:', false, $id_lang);
		default : return $str;
		}
	}


}

?>