<?php
/**
* 2007-2014 PrestaShop
*
* Jms Ajax Search
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2014 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

require_once('../../config/config.inc.php');
require_once('../../init.php');
require_once(dirname(__FILE__).'/jmsajaxsearch.php');
$context = Context::getContext();
$languages = Language::getLanguages(false);	
$search_key = Tools::getValue('search_key');
if (!$context)
	$context = Context::getContext();	
$currency = $context->currency;
$result_products = array();
$ajaxsearch = new JmsAjaxsearch();
$products = array();
$link = $context->link;
$count = 0;
if (Tools::strlen($search_key) >= 3)
{	
	$products = Product::searchByName($context->language->id, $search_key);		
	$total_products = count($products);
	if ($total_products)
	{		
		for ($i = 0; $i < $total_products; $i++)
		{
			if (($products[$i]['name']) && ($products[$i]['active']))
			{	
				$images = Image::getImages($context->language->id, $products[$i]['id_product']);
				$product = new Product($products[$i]['id_product']);
				if ((int)Configuration::get('JMS_AJAXSEARCH_DESC_COUNT') < Tools::strlen($product->description_short[$context->language->id]))
				{
					$pos = strpos($product->description_short[$context->language->id], ' ', (int)Configuration::get('JMS_AJAXSEARCH_DESC_COUNT'));
					$products[$i]['desc'] = Tools::substr($product->description_short[$context->language->id], 0, $pos );
				} 	
				else				
					$products[$i]['desc'] = $product->description_short[$context->language->id];
				
				$products[$i]['link'] = $link->getProductLink($products[$i]['id_product'], $product->link_rewrite[1], $product->id_category_default, $product->ean13);
				$products[$i]['link_rewrite'] = $product->link_rewrite[1];
				$products[$i]['id_image'] = $images[0]['id_image'];				
				$products[$i]['price'] = Tools::displayPrice(Tools::convertPrice($products[$i]['price_tax_incl'], $currency), $currency);														
				if ($count < Configuration::get('JMS_AJAXSEARCH_COUNT'))
				{									
					$result_products[] = $products[$i];
					$count ++;
				}	
				else
					break;
			}		
		}			
	}
	$products = $result_products;

	$context->smarty->assign(array(
		'products' => $products,
		'no_text'	=> $ajaxsearch->no_products
	));
	$context->smarty->display(dirname(__FILE__).'/views/templates/hook/search-ajax.tpl');
}
else 
	echo '<div class="result_div">'.$ajaxsearch->morecharacter.'</div>';