<?php
ini_set('display_errors',0);


class cartePapillonController extends FrontController
{

	
	/**
	*  Assign template vars related to page content
	* @see FrontController::initContent()
	*/
	public function initContent()
	{
		parent::initContent();
		$this->getImgId();
		$this->getProductName();
		$this->add2cart();
		


		$papillon_cat_id = 51;//51;

		$papillon_category = new Category($papillon_cat_id);


		$products_partial = $papillon_category->getProducts($this->context->language->id, 0, 100, 'position', 'asc');
		
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
		
		$this->setTemplate(_PS_THEME_DIR_ .'cartePapillon.tpl');
	}
	
	public function add2cart(){
		global $_GET;
		$ids = Tools::getValue('ids');
		$color = Tools::getValue('color'); 
		$qte = Tools::getValue("qty_livret");
		$qte_env = Tools::getValue("qty_env");
		$msg_carte = Tools::getValue("msg_carte");
		$msg_enveloppe = Tools::getValue("msg_enveloppe");
		
		
		//echo $qte;


		
		if($ids!=''){
			
			$id_produit_carte_papillon = 336;
			$id_option_couleur = 1191;
			$id_option_msg_carte = 1192;
			$id_option_msg_enveloppe = 1193;
			$id_option_papillons = 1194;
			
			
			$user_id = (int)$this->context->cookie->id_customer;
			$this->createCart();
			
			$this->context->cart->addTextFieldToProduct($id_produit_carte_papillon, $id_option_couleur, Product::CUSTOMIZE_TEXTFIELD, $color);
			$this->context->cart->addTextFieldToProduct($id_produit_carte_papillon, $id_option_msg_carte, Product::CUSTOMIZE_TEXTFIELD, $msg_carte);
			$this->context->cart->addTextFieldToProduct($id_produit_carte_papillon, $id_option_msg_enveloppe, Product::CUSTOMIZE_TEXTFIELD, $msg_enveloppe);
			//$this->context->cart->addTextFieldToProduct($id_produit_carte_papillon, $id_option_papillons, Product::CUSTOMIZE_TEXTFIELD, $ids);
			
			
			
			

			
			$ids=explode(",",$ids);
			
			$nb_type_papillon = count(array_keys($ids))-1;
			//echo $nb_type_papillon;exit;
			$multiple_qte = 10;
			
		
			
			if($ids!='') {
				
				$i=0;

				$texte_option_papillons ='';
				foreach($ids as $idp) {
					$qte_idp=10;
					$p=new Product($idp);
					
					
					
					$quantite_papillon = ($qte * 10)/$nb_type_papillon;
					
					$texte_option_papillons .= $idp.':'.$quantite_papillon.';';
					
			
					//$this->context->cart->updateQty($quantite_papillon, $idp, null, false, 'up', 0, null, true, '');
					
					
					
					$i++;
					
					
				}

				$this->context->cart->addTextFieldToProduct($id_produit_carte_papillon, $id_option_papillons, Product::CUSTOMIZE_TEXTFIELD, $texte_option_papillons);
		
				
			}
			

			
			$this->context->cart->updateQty($qte, $id_produit_carte_papillon, null, null);
			
			
			
			$this->context->cart->save();
			$this->context->cookie->__set('id_cart', $this->context->cart->id);			
			
			exit;
			

		}
		
	}
	
	public function getImgId(){
		
		$id = Tools::getValue('id');
		$action = Tools::getValue('action');

		if($action=='getImgId'){
			
			$id_product = $id; // set your product ID here
			$image = Image::getCover($id_product);
			$product = new Product($id_product, false, Context::getContext()->language->id);
			$link = new Link; // because getImageLink is not static function
			$imagePath = $link->getImageLink($product->link_rewrite, $image['id_image'], 'home_default');
			echo  'https://'.$imagePath;
			exit;

			//www.magicflyer.com/1026-home_default/papillon-blanc-impulsion.jpg
		}


	}

	public function getProductName(){
		
		$id = Tools::getValue('id');
		$action = Tools::getValue('action');

		if($action=='getProductName'){
			
			$id_product = $id; // set your product ID here

			$product = new Product($id_product, false, Context::getContext()->language->id);

			echo  $product->name;
			exit;

			//www.magicflyer.com/1026-home_default/papillon-blanc-impulsion.jpg
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

