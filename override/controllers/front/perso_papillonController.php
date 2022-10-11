<?php

class perso_papillonController extends FrontController
{

    /**
     *  Initialize controller
     * @see FrontController::init()
     */
    public function init()
    {
        parent::init();
		$this->page_name = 'perso_papillon'; // page_name and body id
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


		$products_partial = $papillon_category->getProducts($this->context->language->id, 0, 100, 'position', 'asc');
		
		//var_dump($products_partial);exit;
		
		$products = Product::getProductsProperties($this->context->language->id, $products_partial);
		
		foreach ($products as $key => $product) {
			foreach ($products as $key => $product) {
				$products[$key]['id_image'] = Product::getCover($product['id_product'])['id_image'];
			}
			
		}   
		

		$this->context->smarty->assign(array(
		'products' => $products,
		'homeSize' => Image::getSize('home_default')
		));	
		
		$this->setTemplate(_PS_THEME_DIR_ .'perso_papillon.tpl');
	}
	
	public function add2cart(){
		global $_GET;
		$ids = Tools::getValue('ids');
		$color = Tools::getValue('color'); 
		$qte = Tools::getValue("qty_livret");
		$qte_env = Tools::getValue("qty_env");
		$id_enveloppe = 226;
		
		//echo $qte;


		
		if($ids!=''){
			
			$id_p = 0;
			
			if($color=='or')$id_p = 184;//id livret or :183
			if($color=='argente')$id_p = 173;//id livret argenté :174
			if($color=='metal')$id_p = 312;//id livret argenté :174


			$user_id = (int)$this->context->cookie->id_customer;
			$this->createCart();
			

			
			//$this->context->cart->addTextFieldToProduct(117, 141, Product::CUSTOMIZE_TEXTFIELD, $text_personnalise);
			
			$this->context->cart->updateQty($qte, $id_p, null, null);
			
			
			

			
			$ids=explode(",",$ids);
			
			$nb_type_papillon = count(array_keys($ids))-1;
			//echo $nb_type_papillon;exit;
			$multiple_qte = 10;
			
			// switch($nb_type_papillon){
				// case 1:
				// $multiple_qte = 30;
				// break;				
				// case 2:
				// $multiple_qte = 15;
				// break;				
				// case 3:
				// $multiple_qte = 10;
				// break;
				
			// }
			
			
			if($ids!='') {
				
				$i=0;
				foreach($ids as $idp) {
					$qte_idp=10;
					$p=new Product($idp);
					
					
					
					$quantite_papillon = ($qte * 10)/$nb_type_papillon;
					
					
					
					
					$this->context->cart->updateQty($quantite_papillon, $idp, null, false,
        'up', 0, null, true, '');
					
					
					
					$i++;
					
					
				}
				
			}
			

			
			if($qte_env >= 1){
			
			$this->context->cart->updateQty($qte_env, $id_enveloppe, null, null);
			
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
