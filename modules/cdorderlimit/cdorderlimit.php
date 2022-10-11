<?php
/**
* cdorderlimit :: Autoriser les commandes sous certaines conditions
*
* @author    contact@cleanpresta.com (www.cleanpresta.com)
* @copyright 2013-2016 cleandev.net
* @license   You only can use module, nothing more!
*/

if (!defined('_PS_VERSION_'))
	exit;
if(!class_exists('CleanModule'))
    require_once(dirname(__FILE__) .'/CleanModule.php');

class CdOrderLimit extends CleanModule
{
	private $_html = '';
	
	public function __construct()
	{
		$this->name = 'cdorderlimit';
		$this->tab = 'administration';
		$this->version = '2.1.0';
		$this->author = 'cleanpresta.com';
		$this->mprefix 		= "CDOL_";
		$this->reference 	= "CDOL";
		$this->addon_id 	= '20222'; // addon module id
		$this->author		= $this->l('CleanPresta');
		$this->ps_versions_compliancy['min'] = '1.5.0'; // min ps version
		$this->ps_versions_compliancy['max'] = '1.7.0'; // min ps version
		
		parent::__construct();
		
		$this->module_key = 'd8144272b5049f0949976ac5a58d5cba';
		$this->displayName = $this->l('Restrict or allow orders conditionally');
		$this->description = $this->l('This module allows or restrict orders under certain conditions: amount of the order, quantity of products ....');
		$this->full_description = $this->l('This module allows or restrict orders under certain conditions: amount of the order, quantity of products ....');
		
		//cleanpresta var
		$this->hooks = array('displayHeader','displayShoppingCartFooter', 'displayTop');
		
		//table of error
		$this->cartLimitError = array(
			1 => $this->l('you must have a cart amount minimum of : '),
			2 => $this->l('you must have a cart amount maximun of : '),
			3 => $this->l('you must have a cart quantity minimum of : '),
			4 => $this->l('you must have a cart quantity maximun of : '),
			5 => $this->l('you must have a cart quantity of multiple of : '),
			6 => $this->l('you must have a cart quantity of not multiple of : '),
			7 => $this->l('you must have a amount minimum of each products of : '),
			8 => $this->l('you must have a amount maximum of each products of : '),
			9 => $this->l('you must have a quantity minimum of each products of : '),
			10 => $this->l('you must have a quantity maximum of each products of : '),
			11 => $this->l('you must have a quantity of multiple products of each products of : '),
			12 => $this->l('you must have a quantity of not multiple products of each products of : ')
		);
		
		$this->config_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'tabs' => array(
					'tab1' => $this->l('Cart Conditions'),
					'tab2' => $this->l('Suppliers Conditions'),
					'tab4' => $this->l('Exceptions'),
				),
				'description' => $this->description, // can be success, error, warning
				'input' => array(
					/*array(
						'type' => $this->formBoolType,
						'label' => $this->l('Suppliers Order'),
						'name' => 'CDO_SUPPLIER_LIMIT', 
						'is_bool' => true,
						'values' => array(
							array('id' => 'type_switch_on','value' => 1,'label' => $this->l('Yes')),
							array('id' => 'type_switch_off','value' => 0,'label' => $this->l('No'))
						),
						'desc' => $this->l('Requires the customer to order for a single supplier'),
						'tab' => 'tab2',
					),*/
					array(
						'type' => 'radio',
						'label' => $this->l('Limit option'),
						'name' => 'CDO_OPTION_LIMITE',
						'required' => true,
						'desc' => $this->l(''),
						'default' => 3,  
						'class' => 't',
						'tab' => 'tab1',
						'values' => array(
							array('id' => 'active_0','value' => 0,'label' => $this->l('Minimum amount in the Cart')),
							array('id' => 'active_1','value' => 1,'label' => $this->l('Maximum amount in the Cart')),
							array('id' => 'active_2','value' => 2,'label' => $this->l('Quantity Minimum in the Cart')),
							array('id' => 'active_3','value' => 3,'label' => $this->l('Quantity Maximum in the Cart')),
							array('id' => 'active_4','value' => 4,'label' => $this->l('Multiple quantity in the Cart of')),
							array('id' => 'active_5','value' => 5,'label' => $this->l('Not a multiple quantity in the Cart of'))
						)
					),
					array(
						'type' => 'text',
						'label' => $this->l('Limit value'),
						'name' => 'CDO_VALUE',
						'required' => true,
						'desc' => $this->l('Enter the value of the constraint.'),
						'tab' => 'tab1',
					),
					array(
						'type' => 'radio',
						'name' => 'CDO_APPLICABLE',
						'label' => $this->l('Apply on'),
						'desc' => $this->l('Select the type or product category on which we apply the constraint'),
						'is_bool' => true,
						'tab' => 'tab1',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Order')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Product')
							)
						)
					),
					array(
						'type' => 'radio',
						'name' => 'CDO_APPLIQUE_SUR',
						'label' => $this->l('Types of items concerned'),
						'desc' => $this->l('Select "Product" in "A applied to the " before choosing the types of items concerned.'),
						'is_bool' => true,
						'tab' => 'tab4',
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Product')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Product Category')
							)
						)
					),
					array(
						'type' => 'text',
						'label' => $this->l('Ids Concerned'),
						'name' => 'CDO_IDS_CONCERNE',
						'tab' => 'tab4',
						'desc' => $this->l('Enter Ids product or category concerned separated by comma. When nothing is indicated, that will take into account all the products and categories of the cart'),
					),
					array(
						'type' => 'group',
						'label' => $this->l('Customers Group'),
						'name' => 'CDO_GROUP',
						'values' => Group::getGroups(Context::getContext()->language->id),
						'info_introduction' => $this->l('You now have three default customer groups.'),
						'hint' => $this->l('Select the groups concerned'),
						'desc' => $this->l('Will choose the groups of customers whom you want to apply the limits. If you do not choose anything, the limits will be applied to all the groups customers.'),
						'tab' => 'tab4'
					),
				),
				'submit' => array(
					'title' => $this->l('Save') 
				)
			)
		);
	}
	
	public function hookActionBeforeCartUpdateQtyOld($param)
	{
		if((bool)Configuration::get('CDO_SUPPLIER_LIMIT')){ //supplier limit
			$error = '';
			$cartProducts = $param['cart']->getProducts(); //var_dump($cartProducts);
			if(count($cartProducts) > 0){ 
				if($cartProducts[0]['id_supplier'] != $param['product']->id_supplier){
					$error .= $this->l('You can only add supplier Products : ').Supplier::getNameById($cartProducts[0]['id_supplier']);
					$error .= $this->l('\n Or remove its products from the cart and start again');
				}
			}
			
			if(!empty($error)){
				echo '<script>alert("'.$error.'");</script>';
				echo '<script>return ;</script>';
				die(); 
			}
		}
		return true;
	}
	
	public function hookdisplayTop($params)
    {
		if(Tools::getValue('cartLimit'))
		{
			if(in_array(Tools::getValue('cartLimit'), array(0 => 1, 1 => 2, 2 => 7, 3 => 8)))
			{		
				$this->context->smarty->assign(array(
					'CDO_messages' => $this->cartLimitError[Tools::getValue('cartLimit')].' '.Tools::displayPrice((float)Configuration::get('CDO_VALUE'), Currency::getCurrencyInstance((int)$params['cart']->id_currency), false),
					'CDO_valeurs' => true,
				));
			}
			else
			{
				$this->context->smarty->assign(array(
					'CDO_messages' => $this->cartLimitError[Tools::getValue('cartLimit')].' '.Configuration::get('CDO_VALUE'),
					'CDO_valeurs' => true,
				));
			}
			return $this->display(__FILE__, 'error.tpl');
		
		}
	}
	
	public function hookdisplayShoppingCartFooter($params)
    {
		$OPTIONLIMITE = (int)Configuration::get('CDO_OPTION_LIMITE');
		$APPLICABLES = (bool)Configuration::get('CDO_APPLICABLE');
		
		$messages = 0; 
		$NAME_PRODUCTS = array();
		
		//Traitement des contraintes
		if ($APPLICABLES)
		{
			if ($OPTIONLIMITE == 0)
			{
				$messages = 1;
			}
			
			if($OPTIONLIMITE == 1)
			{
				$messages = 2;
			}
			
			if($OPTIONLIMITE == 2)
			{
				$messages = 3;
			}
			
			if($OPTIONLIMITE == 3)
			{
				$messages = 4;
			}
			
			if($OPTIONLIMITE == 4)
			{
				$messages = 5;
			}
			
			if($OPTIONLIMITE == 5)
			{
				$messages = 6;
			}
		}
		else
		{
			$APPLIQUES_SUR = (bool)Configuration::get('CDO_APPLIQUE_SUR');
			$IDS_CONCERNES = explode(',', trim(Configuration::get('CDO_IDS_CONCERNE'), ' ,'));
			
			//traitement si produit
			$j = 0;
			foreach($params['products'] as $element)
			{
				if(($APPLIQUES_SUR && in_array($element['id_product'], $IDS_CONCERNES)) || (!$APPLIQUES_SUR && in_array($element['id_category_default'], $IDS_CONCERNES)))
				{
					if(!(in_array($element['id_product'], $NAME_PRODUCTS)))
					{
						$NAME_PRODUCTS[$j] = $element['reference'].'('.$element['id_product'].')';
						$j++;
					}
				}
			}
			//sort($NAME_PRODUCTS);
			
			if($OPTIONLIMITE == 0)
			{
				$messages = 7;
			}
			
			if($OPTIONLIMITE == 1)
			{
				$messages = 8;
			}
			
			if($OPTIONLIMITE == 2)
			{
				$messages = 9;
			}
			
			if($OPTIONLIMITE == 3)
			{
				$messages = 10;
			}
			
			if($OPTIONLIMITE == 4)
			{
				$messages = 11;
			}
			
			if($OPTIONLIMITE == 5)
			{
				$messages = 12;
			}
		}
		
		if($messages != 0)
		{
			if(in_array($messages, array(0 => 1, 1 => 2, 2 => 7, 3 => 8)))
			{
				$this->context->smarty->assign(array(
					'CDO_message' => $this->cartLimitError[$messages].' '.Tools::displayPrice((float)Configuration::get('CDO_VALUE'), Currency::getCurrencyInstance((int)$params['cart']->id_currency), false),
					'CDO_valeur' => true,
					'NAME_PRODUCTS' => implode(' - ',$NAME_PRODUCTS), 
				));

			}
			else
			{
				$this->context->smarty->assign(array(
					'CDO_message' => $this->cartLimitError[$messages].' '.Configuration::get('CDO_VALUE'),
					'CDO_valeur' => true,
					'NAME_PRODUCTS' => implode(' - ',$NAME_PRODUCTS)
				));
			}
			$this->context->smarty->assign('CDO_error', $this->orderLimiteControl($params['cart']));	
			return $this->display(__FILE__, 'cdorderlimit.tpl');
		}
	}
	
	
	public function hookdisplayHeader($params)
	{
		if((Tools::getValue('step'))&&(Tools::getValue('step') > 0))
		{
			$all_groupe = Tools::unSerialize(Configuration::get('CDO_GROUP'));
			$customer = new Customer((int)$params['cart']->id_customer);
			
			if((empty($all_groupe)) || (!empty($all_groupe) && in_array((int)$customer->id_default_group, $all_groupe))){
				
				$codeError = $this->orderLimiteControl($params['cart']);
				if($codeError != 0)
					Tools::redirect($this->context->link->getPageLink((Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order'), null, null, array('cartLimit'=>$codeError)));
			}
		}
		parent::hookdisplayHeader($params);
	}
	
	public function orderLimiteControl($panier)
	{
		//récupération des données de conditions
		$OPTION_LIMITE = (int)Configuration::get('CDO_OPTION_LIMITE');
		$VALUE = (float)Configuration::get('CDO_VALUE');
		$APPLICABLE = (bool)Configuration::get('CDO_APPLICABLE');
		
		$message = 0;
		
		//Traitement des contraintes
		if($APPLICABLE)
		{
			//quantité de produit du panier
			$quantity_product = 0;
			foreach($panier->getProducts(true) as $element)
			{
				$quantity_product = $quantity_product + (int)$element['cart_quantity'];
			}
			
			//prix total du panier
			$price_total = (float)$panier->getOrderTotal(false, Cart::BOTH);
			
			if(($OPTION_LIMITE == 0) && ($price_total < $VALUE))
			{
				$message = 1;
			}
			
			if(($OPTION_LIMITE == 1) && ($price_total > $VALUE))
			{
				$message = 2;
			}
			
			if(($OPTION_LIMITE == 2)&&($quantity_product < $VALUE))
			{
				$message = 3;
			}
			
			if(($OPTION_LIMITE == 3)&&($quantity_product > $VALUE))
			{
				$message = 4;
			}
			
			if(($OPTION_LIMITE == 4)&&(($quantity_product % $VALUE) != 0))
			{
				$message = 5;
			}
			
			if(($OPTION_LIMITE == 5)&&(($quantity_product % $VALUE) == 0))
			{
				$message = 6;
			}
		
		}
		else
		{
			$APPLIQUE_SUR = Configuration::get('CDO_APPLIQUE_SUR');
			$IDS_CONCERNE = array_filter(explode(',', trim(Configuration::get('CDO_IDS_CONCERNE'),' ,')));
			
			//traitement si produit
			if (empty($IDS_CONCERNE) || !count($IDS_CONCERNE)) 
			{
				///cas où il existe pas les IDS
				foreach($panier->getProducts(true) as $element)
				{
					if(($OPTION_LIMITE == 0)&&($element['total_wt'] < $VALUE))
					{
						$message = 7;
						break;
					}
					
					if(($OPTION_LIMITE == 1)&&($element['total_wt'] > $VALUE))
					{
						$message = 8;
						break;
					}
					
					if(($OPTION_LIMITE == 2)&&($element['cart_quantity'] < $VALUE))
					{
						$message = 9;
						break;
					}
					
					if(($OPTION_LIMITE == 3)&&($element['cart_quantity'] > $VALUE))
					{
						$message = 10;
						break;
					}
					
					if(($OPTION_LIMITE == 4)&&(($element['cart_quantity'] % $VALUE) != 0))
					{
						$message = 11;
						break;
					}
					
					if(($OPTION_LIMITE == 5)&&(($element['cart_quantity'] % $VALUE) == 0))
					{
						$message = 12;
						break;
					}
				}
			}
			else
			{
				///cas où il existe les IDS
				foreach($panier->getProducts(true) as $element)
				{
					if(($APPLIQUE_SUR && in_array($element['id_product'], $IDS_CONCERNE)) || (!$APPLIQUE_SUR && in_array($element['id_category_default'], $IDS_CONCERNE)))
					{
						
						if(($OPTION_LIMITE == 0)&&($element['total_wt'] < $VALUE))
						{
							$message = 7;
							break;
						}
						
						if(($OPTION_LIMITE == 1)&&($element['total_wt'] > $VALUE))
						{
							$message = 8;
							break;
						}
						
						if(($OPTION_LIMITE == 2)&&($element['cart_quantity'] < $VALUE))
						{
							$message = 9;
							break;
						}
						
						if(($OPTION_LIMITE == 3)&&($element['cart_quantity'] > $VALUE))
						{
							$message = 10;
							break;
						}
						
						if(($OPTION_LIMITE == 4)&&(($element['cart_quantity'] % $VALUE) != 0))
						{
							$message = 11;
							break;
						}
						
						if(($OPTION_LIMITE == 5)&&(($element['cart_quantity'] % $VALUE) == 0))
						{
							$message = 12;
							break;
						}
					}
				}
			}
		}
		return $message;
	}
}