<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class CmsController extends CmsControllerCore
{
	/**
	 * Assign template vars related to page content
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		

		
		if($this->cms->id==53){
			// get cart id if exists
if ($this->context->cookie->id_cart)
{
    $cart = new Cart($this->context->cookie->id_cart);
}

// create new cart if needed
if (!isset($cart) OR !$cart->id)
{
    $cart = new Cart();
    $cart->id_customer = (int)($this->context->cookie->id_customer);
    $cart->id_address_delivery = (int)  (Address::getFirstCustomerAddressId($cart->id_customer));
    $cart->id_address_invoice = $cart->id_address_delivery;
    $cart->id_lang = (int)($this->context->cookie->id_lang);
    $cart->id_currency = (int)($this->context->cookie->id_currency);
    $cart->id_carrier = 1;
    $cart->recyclable = 0;
    $cart->gift = 0;
    $cart->add();
    $this->context->cookie->id_cart = (int)($cart->id);    
    $cart->update();
}
		//echo 'id_guest:'.$this->context->cookie->id_guest;
		//echo 'cookie->id_cart:'.$this->context->cookie->id_cart;
		//echo 'context->id_cart:'.$this->context->cart->id;
		}
		
		$cat_papillons_magiques = 52;

		$sql = 'SELECT p.*, pl.* ,pi.*,cp.position, m.`name` AS manufacturer_name FROM `'._DB_PREFIX_.'product` p   '.
						' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)'.
						' LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (p.`id_product` = cp.`id_product`)'.
						' LEFT JOIN `'._DB_PREFIX_.'image` pi ON (p.`id_product` = pi.`id_product`)'.
						' LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)'.
						' WHERE cp.id_category = '.$cat_papillons_magiques.' AND pl.`id_lang` = '.(int)$this->context->language->id.' AND p.active=1   ORDER BY cp.position ASC';
		$papillons_magiques = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		
		$this->context->smarty->assign('papillons_magiques', $papillons_magiques);
		
		
		
		

		$parent_cat = new CMSCategory(1, $this->context->language->id);
		$this->context->smarty->assign('id_current_lang', $this->context->language->id);
		$this->context->smarty->assign('home_title', $parent_cat->name);
		$this->context->smarty->assign('cgv_id', Configuration::get('PS_CONDITIONS_CMS_ID'));
		if (isset($this->cms->id_cms_category) && $this->cms->id_cms_category)
			$path = Tools::getFullPath($this->cms->id_cms_category, $this->cms->meta_title, 'CMS');
		elseif (isset($this->cms_category->meta_title))
			$path = Tools::getFullPath(1, $this->cms_category->meta_title, 'CMS');
		if ($this->assignCase == 1)
		{
			$this->context->smarty->assign(array(
				'cms' => $this->cms,
				'content_only' => (int)Tools::getValue('content_only'),
				'path' => $path,
				'body_classes' => array($this->php_self.'-'.$this->cms->id, $this->php_self.'-'.$this->cms->link_rewrite)
			));

			if ($this->cms->indexation == 0)
				$this->context->smarty->assign('nobots', true);
		}
		elseif ($this->assignCase == 2)
		{
			$this->context->smarty->assign(array(
				'category' => $this->cms_category, //for backward compatibility
				'cms_category' => $this->cms_category,
				'sub_category' => $this->cms_category->getSubCategories($this->context->language->id),
				'cms_pages' => CMS::getCMSPages($this->context->language->id, (int)$this->cms_category->id, true, (int)$this->context->shop->id),
				'path' => ($this->cms_category->id !== 1) ? Tools::getPath($this->cms_category->id, $this->cms_category->name, false, 'CMS') : '',
				'body_classes' => array($this->php_self.'-'.$this->cms_category->id, $this->php_self.'-'.$this->cms_category->link_rewrite)
			));
		}
		
		//pr la boutique des pro on testes les groupes PRO
		$groupe_id = Customer::getGroupsStatic($this->context->cookie->id_customer) ;
		$isPro = 0;
		
		if( in_array(4,$groupe_id)== true  || in_array(5,$groupe_id)== true || in_array(6,$groupe_id) == true ){ 
			
			$isPro = 1;
		
		}
		//
		
		$this->context->smarty->assign('isPro' , $isPro);
		
		$this->setTemplate(_PS_THEME_DIR_.'cms.tpl');
	}
		
}
