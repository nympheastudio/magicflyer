<?php
/**
* 2007-2014 PrestaShop
*
* Jms Adv Product Filter
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

if (!defined('_PS_VERSION_'))
	exit;

class JmsProductFilter extends Module
{
	public function __construct()
	{
		$this->name = 'jmsproductfilter';
		$this->tab = 'front_office_features';
		$this->version = '1.1.0';
		$this->author = 'Joommasters';
		$this->need_instance = 0;
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Jms Product Filter');
		$this->description = $this->l('show Product');
	}
	public function install()
	{
		$res = true;
		if (parent::install() && $this->registerHook('header'))
		{
			$res &= Configuration::updateValue('JMS_PF_CATEGORIES', 2);
			$res &= Configuration::updateValue('JMS_PF_TOTALS_NUMBERS', 16);
			$res &= Configuration::updateValue('JMS_PF_ROWS_NUMBERS', 4);
			$res &= Configuration::updateValue('JMS_PF_COLS_NUMBERS', 4);
			$res &= Configuration::updateValue('JMS_PF_SCROLL', 1);
			$res &= Configuration::updateValue('JMS_PF_PRICES', 1);
			$res &= Configuration::updateValue('JMS_PF_FEATURED', 1);
			$res &= Configuration::updateValue('JMS_PF_NEW', 1);
			$res &= Configuration::updateValue('JMS_PF_TOPSELLER', 1);
			$res &= Configuration::updateValue('JMS_PF_SPECIAL', 1);
			$res &= Configuration::updateValue('JMS_PF_ONSALE', 0);
			return $res;
		}
		return false;
	}
	public function uninstall()
	{
		/* Deletes Module */
		$res = true;
		if (parent::uninstall())
		{
			/* Unsets configuration */
			$res &= Configuration::deleteByName('JMS_PF_CATEGORIES');
			$res &= Configuration::deleteByName('JMS_PF_TOTALS_NUMBERS');
			$res &= Configuration::deleteByName('JMS_PF_ROWS_NUMBERS');
			$res &= Configuration::deleteByName('JMS_PF_COLS_NUMBERS');
			$res &= Configuration::deleteByName('JMS_PF_SCROLL');
			$res &= Configuration::deleteByName('JMS_PF_PRICES');
			$res &= Configuration::deleteByName('JMS_PF_FEATURED');
			$res &= Configuration::deleteByName('JMS_PF_NEW');
			$res &= Configuration::deleteByName('JMS_PF_TOPSELLER');
			$res &= Configuration::deleteByName('JMS_PF_SPECIAL');
			$res &= Configuration::deleteByName('JMS_PF_ONSALE');			
			
			return (bool)$res;
		}

		return false;
	}
	public function getContent()
	{
		if (Tools::isSubmit('submitConfig'))
		{
			$res = true;
			$list_categories = implode(',', Tools::getValue('JMS_PF_CATEGORIES'));
			$res &= Configuration::updateValue('JMS_PF_CATEGORIES', $list_categories);			
			$res &= Configuration::updateValue('JMS_PF_TOTALS_NUMBERS', Tools::getValue('JMS_PF_TOTALS_NUMBERS'));
			$res &= Configuration::updateValue('JMS_PF_PRICES', Tools::getValue('JMS_PF_PRICES'));
			$res &= Configuration::updateValue('JMS_PF_SCROLL', Tools::getValue('JMS_PF_SCROLL'));
			$res &= Configuration::updateValue('JMS_PF_ROWS_NUMBERS', Tools::getValue('JMS_PF_ROWS_NUMBERS'));
			$res &= Configuration::updateValue('JMS_PF_COLS_NUMBERS', Tools::getValue('JMS_PF_COLS_NUMBERS'));
			$res &= Configuration::updateValue('JMS_PF_FEATURED', Tools::getValue('JMS_PF_FEATURED'));
			$res &= Configuration::updateValue('JMS_PF_NEW', Tools::getValue('JMS_PF_NEW'));
			$res &= Configuration::updateValue('JMS_PF_TOPSELLER', Tools::getValue('JMS_PF_TOPSELLER'));
			$res &= Configuration::updateValue('JMS_PF_SPECIAL', Tools::getValue('JMS_PF_SPECIAL'));			
			$res &= Configuration::updateValue('JMS_PF_ONSALE', Tools::getValue('JMS_PF_ONSALE'));			
		}
		return $this->displayForm();
	}
	public function hookHeader($params)
	{
		if (!isset($this->context->controller->php_self) || $this->context->controller->php_self != 'index')
			return;
		$this->context->controller->addCSS($this->_path.'views/css/style.css', 'all');		
	}
	public function getFeaturedProduct($categories_ids)
	{
		$category = new Category((int)Configuration::get('HOME_FEATURED_CAT'), (int)Context::getContext()->language->id);		
		$products = $category->getProducts((int)Context::getContext()->language->id, 1, Configuration::get('JMS_PF_TOTALS_NUMBERS'), 'position');		
		if(count($categories_ids) == 0) 
			return $products;
		$result = array();
		foreach($products as $product) 			
			if(in_array($product['id_category_default'], $categories_ids)) 
				$result[] =  $product;
		return $result;
	}
	public function getNewProduct($categories_ids)
	{
		if (!Configuration::get('NEW_PRODUCTS_NBR'))
			return;
		$newProducts = false;		
		$newProducts = Product::getNewProducts((int) $this->context->language->id, 0, Configuration::get('JMS_PF_TOTALS_NUMBERS'));

		if(count($categories_ids) == 0) 
			return $newProducts;
		$result = array();
		foreach($newProducts as $product) 			
			if(in_array($product['id_category_default'], $categories_ids)) 
				$result[] =  $product;
		return $result;
	}	
	
	public function getonSaleProducts($categories_ids)
	{		
		$id_lang = Context::getContext()->language->id;
		$sql = 'SELECT p.*, product_shop.*, pl.*,MAX(image_shop.`id_image`) id_image 
				FROM `'._DB_PREFIX_.'product` p
				'.Shop::addSqlAssociation('product', 'p').'
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
				LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.id_product = pl.id_product)				
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
				WHERE  pl.`id_lang` = '.(int)$id_lang.' AND p.`on_sale` = 1 AND p.`active` = 1';
				if(count($categories_ids)) 
					$sql .= ' AND cp.id_category IN ('.implode(",", $categories_ids).')';
				$sql .= ' GROUP BY p.`id_product`
				LIMIT 0,'.Configuration::get('JMS_PF_TOTALS_NUMBERS');	
				//print_r($sql); exit;	
		
		$result = Db::getInstance()->executeS($sql);
		return Product::getProductsProperties((int)Context::getContext()->language->id, $result);				
		
	}
	public function getspecialProduct($number_product)
	{
		return Product::getPricesDrop($this->context->language->id, 0, $number_product);
	}
	protected function getTopSellerProduct($categories_ids, $number, $params)
	{	
		if (!($result = $this->sqlCategoryProduct($this->context->language->id, $categories_ids, 0, $number)  ))
		$currency = null;		
		$currency = new Currency($params['cookie']->id_currency);
		$usetax = (Product::getTaxCalculationMethod((int)$this->context->customer->id) != PS_TAX_EXC);
		foreach ($result as &$row)
			$row['price'] = Tools::displayPrice(Product::getPriceStatic((int)$row['id_product'], $usetax), $currency);
		return $result;
	}

	public function displayForm()
	{
		$options = array(array('id_product'=> 1,'name' => 'Featured Product','val' => 1),array('id_product'=> 2,'name' => 'New Product','val' => 2),
		array('id_product'=> 3,'name' => 'Best Seller','val' => 3),array('id_product'=> 4,'name' => 'Special Product','val' => 4),array('id_product'=> 5,'name' => 'On Sale Product','val' => 5));		
		$context = Context::getContext();		
		$selected_categories = explode(',', Configuration::get('JMS_PF_CATEGORIES'));
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Number of product per row'),
						'name' => 'JMS_PF_ROWS_NUMBERS',
					),
					array(
						'type' => 'text',
						'label' => $this->l('Number of product per cols'),
						'name' => 'JMS_PF_COLS_NUMBERS',
					),
					array(
						'type' => 'text',
						'label' => $this->l('Totals number of product'),
						'name' => 'JMS_PF_TOTALS_NUMBERS',
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Featured Products'),
						'name' => 'JMS_PF_FEATURED',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Show')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Hide')
							)
						),
					),					
					array(
						'type' => 'switch',
						'label' => $this->l('New Products'),
						'name' => 'JMS_PF_NEW',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Show')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Hide')
							)
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('TopSeller Products'),
						'name' => 'JMS_PF_TOPSELLER',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Show')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Hide')
							)
						),
					),	
					array(
						'type' => 'switch',
						'label' => $this->l('Special Products'),
						'name' => 'JMS_PF_SPECIAL',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Show')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Hide')
							)
						),
					),	
					array(
						'type' => 'switch',
						'label' => $this->l('OnSale Products'),
						'name' => 'JMS_PF_ONSALE',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Show')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Hide')
							)
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Auto Scroll per page'),
						'name' => 'JMS_PF_SCROLL',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Display products\' prices'),
						'desc' => $this->l('Show the prices of the products displayed in the block.'),
						'name' => 'JMS_PF_PRICES',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						),
					),
					array(
						'type'  => 'categories',
						'label' => $this->l('Category'),
						'name'  => 'JMS_PF_CATEGORIES',
						'desc' => 'Please choose your category that you want to show',
						'tree'  => array(
							'id'                  => 'categories-tree',
							'selected_categories' => $selected_categories,
							'root_category'       => $context->shop->getCategory(),
							'use_checkbox' => true
						),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);
		
		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitConfig';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
			'fields_value' => $this->getAddFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => $this->_path.'img/'
		);
		$helper->override_folder = '/';		
		return $helper->generateForm(array($fields_form));
	}
	
	public function getAddFieldsValues()
	{
		return array(
			'JMS_PF_TOTALS_NUMBERS' => Tools::getValue('JMS_PF_TOTALS_NUMBERS', Configuration::get('JMS_PF_TOTALS_NUMBERS')),
			'JMS_PF_PRICES' => Tools::getValue('JMS_PF_PRICES', Configuration::get('JMS_PF_PRICES')),
			'JMS_PF_ROWS_NUMBERS' => Tools::getValue('JMS_PF_ROWS_NUMBERS', Configuration::get('JMS_PF_ROWS_NUMBERS')),
			'JMS_PF_COLS_NUMBERS' => Tools::getValue('JMS_PF_COLS_NUMBERS', Configuration::get('JMS_PF_COLS_NUMBERS')),
			'JMS_PF_SCROLL' => Tools::getValue('JMS_PF_SCROLL', Configuration::get('JMS_PF_SCROLL')),
			'JMS_PF_FEATURED' => Tools::getValue('JMS_PF_FEATURED', Configuration::get('JMS_PF_FEATURED')),
			'JMS_PF_NEW' => Tools::getValue('JMS_PF_NEW', Configuration::get('JMS_PF_NEW')),
			'JMS_PF_ONSALE' => Tools::getValue('JMS_PF_ONSALE', Configuration::get('JMS_PF_ONSALE')),
			'JMS_PF_SPECIAL' => Tools::getValue('JMS_PF_SPECIAL', Configuration::get('JMS_PF_SPECIAL')),
			'JMS_PF_TOPSELLER' => Tools::getValue('JMS_PF_TOPSELLER', Configuration::get('JMS_PF_TOPSELLER')),
		);
	}
	
	public static function sqlCategoryProduct($id_lang, $categories_ids, $page_number = 0, $nb_products = 10, Context $context = null)
	{
		if (!$context)
			$context = Context::getContext();
		if ($page_number < 0) $page_number = 0;
		if ($nb_products < 1) $nb_products = 10;

		$sql_groups = '';
		if (Group::isFeatureActive())
		{
			$groups = FrontController::getCurrentCustomerGroups();
			$sql_groups = 'AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
		}

		//Subquery: get product ids in a separate query to (greatly!) improve performances and RAM usage
		$products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT cp.`id_product`
		FROM `'._DB_PREFIX_.'category_product` cp
		LEFT JOIN `'._DB_PREFIX_.'category_group` cg ON (cg.`id_category` = cp.`id_category`)
		WHERE cg.`id_group` '.$sql_groups);
		
		$ids = array();
		foreach ($products as $product)
			$ids[$product['id_product']] = 1;

		$ids = array_keys($ids);		
		sort($ids);
		$ids = count($ids) > 0 ? implode(',', $ids) : 'NULL';

		//Main query
		$sql = '
		SELECT
			p.id_product,  MAX(product_attribute_shop.id_product_attribute) id_product_attribute, pl.`link_rewrite`, pl.`name`, pl.`description_short`, product_shop.`id_category_default`,
			MAX(image_shop.`id_image`) id_image, il.`legend`,
			ps.`quantity` AS sales, p.`ean13`, p.`upc`, cl.`link_rewrite` AS category, p.show_price, p.available_for_order, IFNULL(stock.quantity, 0) as quantity, p.customizable,
			IFNULL(pa.minimal_quantity, p.minimal_quantity) as minimal_quantity, stock.out_of_stock,
			product_shop.`date_add` > "'.date('Y-m-d', strtotime('-'.(Configuration::get('PS_NB_DAYS_NEW_PRODUCT') ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY')).'" as new, product_shop.`on_sale`
		FROM `'._DB_PREFIX_.'product_sale` ps
		LEFT JOIN `'._DB_PREFIX_.'product` p ON ps.`id_product` = p.`id_product`
		'.Shop::addSqlAssociation('product', 'p').'
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa
			ON (p.`id_product` = pa.`id_product`)
		'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1').'
		'.Product::sqlStock('p', 'product_attribute_shop', false, $context->shop).'
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
			ON p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
		Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
			ON cl.`id_category` = product_shop.`id_category_default`
			AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').'
			
		LEFT JOIN `'._DB_PREFIX_.'category_product` cp on cp.`id_product` = p.`id_product`
		
		WHERE product_shop.`active` = 1
		AND p.`visibility` != \'none\'
		AND p.`id_product` IN ('.$ids.')';
		if(count($categories_ids))
			$sql .= ' AND cp.`id_category` IN ('.implode(",", $categories_ids).')';
		
		$sql .= ' GROUP BY product_shop.id_product
		ORDER BY sales DESC
		LIMIT '.(int)($page_number * $nb_products).', '.(int)$nb_products;
		
		if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql))
			return false;

		return Product::getProductsProperties($id_lang, $result);
	}
	function sliceProducts($products) 
	{
		$rows_number = (int)(Configuration::get('JMS_PF_ROWS_NUMBERS'));
		$cols_number = (int)(Configuration::get('JMS_PF_COLS_NUMBERS'));		
		$total_config = (int)(Configuration::get('JMS_PF_TOTALS_NUMBERS'));		
		if(count($products) > $total_config)
			$total = $total_config;
		else 
			$total = count($products);						
		if (Validate::isInt($total / $cols_number))
			$number_cols = $total / $cols_number;
		else
			$number_cols = $total / $cols_number + 1;
		if($rows_number > $number_cols)	
			$number_cols = $rows_number;		
		$result = array();		
		for ($i = 0; $i < count($products); $i++)
		{	
			if($i % $number_cols == 0)
				$_index = 0;
			else 
				$_index = $i % $number_cols;			
			$result[$_index][] = $products[$i];
		}							
		return $result;
	}
	public function hookdisplayHome($params)
	{		
		$category_ids = explode(',', Configuration::get('JMS_PF_CATEGORIES'));		
		$featured_products = $this->getFeaturedProduct($category_ids);		
		$new_products = $this->getNewProduct($category_ids);				
		$top_sellers = $this->getTopSellerProduct($category_ids, Configuration::get('JMS_PF_TOTALS_NUMBERS'), $params);		
		$special_products = $this->getspecialProduct(Configuration::get('JMS_PF_TOTALS_NUMBERS'));		
		$onsale_products = $this->getonSaleProducts($category_ids);
		$result_featured_products = $this->sliceProducts($featured_products);
		$result_new_products = $this->sliceProducts($new_products);
		$result_topseller_products = $this->sliceProducts($top_sellers);
		//print_r(count($result_topseller_products)); exit;
		$result_special_products = $this->sliceProducts($special_products);
		$result_onsale_products = $this->sliceProducts($onsale_products);		
		$product_filter = array();
		$product_filter = array(			
			'JMS_PF_FEATURED' => Configuration::get('JMS_PF_FEATURED'),
			'JMS_PF_NEW' => Configuration::get('JMS_PF_NEW'),
			'JMS_PF_ONSALE' => Configuration::get('JMS_PF_ONSALE'),
			'JMS_PF_SPECIAL' => Configuration::get('JMS_PF_SPECIAL'),
			'JMS_PF_TOPSELLER' => Configuration::get('JMS_PF_TOPSELLER'),
		);
		$this->smarty->assign(
				array(
					'product_filter' => $product_filter,					
					'result_featured_products' => $result_featured_products,
					'result_new_products' => $result_new_products,
					'result_topseller_products' => $result_topseller_products,
					'result_special_products' => $result_special_products,
					'result_onsale_products' => $result_onsale_products,
					'ProdDisplayPrice' => Configuration::get('JMS_PF_PRICES'),
					'autoscroll' => Configuration::get('JMS_PF_SCROLL'),
					'number_row' => Configuration::get('JMS_PF_ROWS_NUMBERS'),
		));
		return $this->display(__FILE__, 'jmsproductfilter.tpl');
	}
}