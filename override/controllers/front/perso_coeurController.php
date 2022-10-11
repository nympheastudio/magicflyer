<?php

class perso_coeurController extends FrontController
{

	/**
	*  Initialize controller
	* @see FrontController::init()
	*/
	public function init()
	{
		parent::init();
		$this->page_name = 'Perso_coeur'; // page_name and body id
		$this->display_column_left = false;
		$this->display_column_right = false;
	}

	/**
	*  Assign template vars related to page content
	* @see FrontController::initContent()
	*/
	public function initContent()
	{
		parent::initContent();
		$this->add2cart();
		$papillon_cat_id = 51;//51;

		$papillon_category = new Category($papillon_cat_id);


		$products_partial = $papillon_category->getProducts($this->context->language->id, 0, 100, 'name', 'asc');
		
		//var_dump($products_partial);exit;
		
		$products = Product::getProductsProperties($this->context->language->id, $products_partial);
		
		foreach ($products as $key => $product) {
			foreach ($products as $key => $product) {
				$products[$key]['id_image'] = Product::getCover($product['id_product'])['id_image'];
			}
			
		}   
		
		//$product_coeur=new Product(117);
		$this->context->smarty->assign(array(
		'products' => $products,
		'homeSize' => Image::getSize('home_default'),
		//'prix' =>  Product::getPriceStatic(117)//$product_coeur->price
		));	
		
					// $product_coeur=new Product(117);
			// var_dump($product_coeur->getCustomizationFieldIds());
// exit;		
		
		$this->setTemplate(_PS_THEME_DIR_ . 'perso_coeur.tpl');
	}
	
	public function add2cart(){
		global $_GET;
		$ids = Tools::getValue('ids');
		$prenom_pap = Tools::getValue('prenom');
		$date_pap = date("d-m-Y", strtotime(Tools::getValue('date')));
		$qty_pap = Tools::getValue('qty');
		
		

		
		if($ids!=''){
			


			$user_id = (int)$this->context->cookie->id_customer;
			$this->createCart();
			
			$prenom_pap = str_replace('ET_HTML','&', $prenom_pap);
			
			//echo $prenom_pap;exit;
			
			$text_personnalise = $prenom_pap . "_DATE_" .$date_pap;
			
			$qte = Tools::getValue("qty");
	
			$field_customization_id = 141;
			
			
			$this->context->cart->addTextFieldToProduct(117,$field_customization_id, Product::CUSTOMIZE_TEXTFIELD, $text_personnalise);
			
			
			$exising_customization = Db::getInstance()->executeS('SELECT id_customization FROM '._DB_PREFIX_.'customized_data ORDER BY id_customization DESC LIMIT 0,1');


			$customization = $exising_customization[0]['id_customization'];

			$this->context->cart->updateQty($qte, 117, null, $customization);
			
			
			

			
			$ids=explode(",",$ids);
			
			$nb_type_papillon = count(array_keys($ids))-1;
			//echo $nb_type_papillon;exit;
			$multiple_qte = 10;
			
			switch($nb_type_papillon){
				case 1:
				$multiple_qte = 30;
				break;				
				case 2:
				$multiple_qte = 15;
				break;				
				case 3:
				$multiple_qte = 10;
				break;
				
			}
			
			
			if($ids!='') {
				
				$i=0;
				foreach($ids as $idp) {
					$qte_idp=10;
					$p=new Product($idp);
					
					
					
					$quantite_papillon = /*$qte_idp * */$qte * $multiple_qte/*$p->minimal_quantity*/;
					
					
					
					
					$this->context->cart->updateQty($quantite_papillon, $idp, null, false,
        'up', 0, null, true, 117);
					
					
					
					$i++;
					
					
				}
				
			}
			

			
			
			
			
			
			$this->context->cart->save();
			$this->context->cookie->__set('id_cart', $this->context->cart->id);			
			
			exit;
			

		}
		
	}
	
	
	
	private function createCart()
	{
		if (is_null($this->context->cart)) {

			$this->context->cart = 
			new Cart($this->context->cookie->id_cart);
		}

		if (is_null($this->context->cart->id_lang)) {
			$this->context->cart->id_lang = $this->context->cookie->id_lang;
		}

		if (is_null($this->context->cart->id_currency)) {
			$this->context->cart->id_currency = $this->context->cookie->id_currency;
		}

		if (is_null($this->context->cart->id_customer)) {
			$this->context->cart->id_customer = $this->context->cookie->id_customer;
		}

		if (is_null($this->context->cart->id_guest)) {

			if (empty($this->context->cookie->id_guest)){
				$this->context->cookie->__set(
				'id_guest', 
				Guest::getFromCustomer($this->context->cookie->id_customer)
				);
			}
			$this->context->cart->id_guest = $this->context->cookie->id_guest;
		}

		if (is_null($this->context->cart->id)) {

			$this->context->cart->add();

			$this->context->cookie->__set('id_cart', $this->context->cart->id);
		}
	}

}
