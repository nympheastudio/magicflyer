<?php

class personnalisationController extends FrontController
{

	/**
	*  Initialize controller
	* @see FrontController::init()
	*/
	public function init()
	{
		parent::init();
		$this->page_name = 'Personnalisation'; // page_name and body id
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
		
		
		
		$this->initPersonnalisation();
		/*$papillon_cat_id = 51;

		$papillon_category = new Category($papillon_cat_id);


		$products_partial = $papillon_category->getProducts($this->context->language->id, 0, 100, 'name', 'asc');
		
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
		
		$this->setTemplate(_PS_THEME_DIR_ . 'perso_coeur.tpl');
		*/
		
		$id_gabarit = 1;
		
		$id_produit = Tools::getValue('id_produit');
		
		switch($id_produit){
		case 228: $id_gabarit = 2;break;
		case 231: $id_gabarit = 2;break;
		case 308: $id_gabarit = 2;break;
			
		case 227: $id_gabarit = 1;break;
		case 230: $id_gabarit = 1;break;			
		case 309: $id_gabarit = 1;break;			
		case 310: $id_gabarit = 1;break;			
			
		case 229: $id_gabarit = 3;break;
		case 232: $id_gabarit = 3;break;
			
		}


		$listPictures = Db::getInstance()->ExecuteS('
			SELECT i.`cover`, i.`id_image`, i.`position`
			FROM `'._DB_PREFIX_.'image` i
			WHERE i.`id_product` = '.(int)($id_produit).'
			ORDER BY i.cover DESC, i.`position` ASC ');
		
		$extension = ".jpg";
		$i = 1;
		foreach($listPictures as $img){
			
			if($i == 3){
				$dossier = implode('/',str_split($img["id_image"])).'/'; 
				$image1 = _PS_BASE_URL_SSL_._THEME_PROD_DIR_.$dossier.$img["id_image"].$extension;
			}		
			if($i == 2){
				$dossier = implode('/',str_split($img["id_image"])).'/'; 
				
				$image2 = _PS_BASE_URL_SSL_._THEME_PROD_DIR_.$dossier.$img["id_image"].$extension;
			}
			$i++;
			
		}
		
		

		$product = new Product($id_produit);
		// var_dump($product);var_dump($product);
		

		
		$this->context->smarty->assign(array(
		'image1' => $image1,
		'image2' => $image2,
		'product' => $product,
		));	
		
		
		
		switch($id_produit){
			case 228: case 231: case 308: case 227: case 230: case 309: case 310: case 229: case 232: 
			$this->setTemplate(_PS_THEME_DIR_ . 'perso_fairepart-pap.tpl'); break;
			default:
			$this->setTemplate(_PS_THEME_DIR_ . 'perso_fairepart.tpl');
		}
		
	}
	
	public function initPersonnalisation(){
		global $_POST;
		ini_set('display_errors',1);
		
		
		$user_id = (int)$this->context->cookie->id_customer;
		
		$ajouter_au_panier = Tools::getValue('formsubmit');
		
		if($ajouter_au_panier == 1){
			
			$this->createCart();
			
			$id_produit = Tools::getValue('id_produit');
			$qty = Tools::getValue('qty');
			$couleur 			= Tools::getValue('couleur_fairepart');
			$texte1 			= Tools::getValue('texte_ext1_fairepart');
			$texte2 			= Tools::getValue('texte_ext2_fairepart');
			$id_police 			= Tools::getValue('police_fairepart');
			$prenoms 			= Tools::getValue('prenoms_fairepart');
			$date_fairepart 	= Tools::getValue('date_fairepart');
			
			
			// echo $couleur 			.'<br>' ;
			// echo $texte1 			.'<br>' ;
			// echo $texte2 			.'<br>' ;
			// echo $police 			.'<br>' ;
			// echo $prenoms 			.'<br>' ;
			// echo $date_fairepart 	.'<br>' ;
			// exit;
			
			
			if(isset($_FILES['photo_fairepart'])){
				$field_name = 'photo_fairepart';
				
				$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');

				$type = strtolower(substr(strrchr($_FILES[$field_name]['name'], '.'), 1));
				
				if (!$temp_name || !move_uploaded_file($_FILES[$field_name]['tmp_name'], $temp_name)){
					
					$this->context->smarty->assign(array('testnotif' => 'Erreur lors de l\'envoi de ta photo !'));
					
				}elseif ( ! $img_current = $this->creerVignettes($temp_name,$user_id.'_'.rand(5, 1500),$type)    ){
					
					$this->context->smarty->assign(array('testnotif' => 'Erreur lors de l\'envoi de ta photo !'));
				}else{
					$this->context->smarty->assign(array('testnotif' => 'Photo enregistr??e !'));
				}
				
				
				if (isset($temp_name)) @unlink($temp_name);
			}
			
			
			
			$qte = (int)$qty;
			
			$this->context->cart->deleteProduct($id_produit);
			
			//police
			switch($id_police ){
			case 1 : $police = 'Arial'; break;
			case 2 : $police = 'Comics'; break;
			case 3 : $police = 'Trebuchet'; break;
			case 4 : $police = 'Lucida Sans'; break;
			case 5 : $police = 'Tahoma'; break;
			case 6 : $police = 'Verdana'; break;
			case 7 : $police = 'Impact'; break;
			case 8 : $police = 'MV Boli'; break;
			case 9 : $police = 'Segoe Print'; break;
			case 10: $police = 'Segoe Script'; break;
			}
			
			
			$sql = 'SELECT id_customization_field FROM `'._DB_PREFIX_.'customization_field` WHERE `id_product` = '.(int)($id_produit)
			.' ORDER BY `id_product` ASC';
			
			$listcustomization_field = Db::getInstance()->ExecuteS($sql);
			
			
			//var_dump($listcustomization_field);
			$i=1;
			foreach ($listcustomization_field as $key => $value) {
				//echo $i.':'.$listcustomization_field[$key]['id_customization_field'].'<br>' ;

				if( $i == 1) $id_champ1 = $listcustomization_field[$key]['id_customization_field'];
				if( $i == 2) $id_champ2 = $listcustomization_field[$key]['id_customization_field'];
				if( $i == 3) $id_champ3 = $listcustomization_field[$key]['id_customization_field'];
				if( $i == 4) $id_champ4 = $listcustomization_field[$key]['id_customization_field'];
				if( $i == 5) $id_champ5 = $listcustomization_field[$key]['id_customization_field'];
				if( $i == 6) $id_champ6 = $listcustomization_field[$key]['id_customization_field'];
				if( $i == 7) $id_champ7 = $listcustomization_field[$key]['id_customization_field'];


				$i++;
			}		

			if($id_champ1==''){echo 'erreur champ 1';exit;}
			$cust = $this->context->cart->addTextFieldToProduct($id_produit, $id_champ1, Product::CUSTOMIZE_TEXTFIELD, $couleur );
			$cust = $this->context->cart->addTextFieldToProduct($id_produit, $id_champ2, Product::CUSTOMIZE_TEXTFIELD, $texte1 			 );
			$cust = $this->context->cart->addTextFieldToProduct($id_produit, $id_champ3, Product::CUSTOMIZE_TEXTFIELD, $texte2 			 );
			$cust = $this->context->cart->addTextFieldToProduct($id_produit, $id_champ4, Product::CUSTOMIZE_TEXTFIELD, $police 			 );
			$cust = $this->context->cart->addTextFieldToProduct($id_produit, $id_champ5, Product::CUSTOMIZE_TEXTFIELD, $prenoms 			 );
			$cust = $this->context->cart->addTextFieldToProduct($id_produit, $id_champ6, Product::CUSTOMIZE_TEXTFIELD, $date_fairepart );
			$cust = $this->context->cart->addTextFieldToProduct($id_produit, $id_champ7, Product::CUSTOMIZE_TEXTFIELD, $img_current );
			

			
			//exit;
			
			
			
			$this->context->cart->updateQty($qte, $id_produit, null, '');
			
			
			
			
			
			$this->context->cart->save();
			$this->context->cookie->__set('id_cart', $this->context->cart->id);			
			
			
			// echo 'Fin ajout panier';
			
			// exit;
			//https://www.magicflyer.com/index.php?controller=category&id_category=51
			// Tools::redirect('index.php?controller=category&id_category=63&carte_perso_added=1');
			Tools::redirect('index.php?controller=category&id_category=51&carte_perso_added=1');
			// //exit;
			//exit;
			
			
			
			
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
	
	public function creerVignettes($a, $b,$c ){
		
		if($a=='') return;
		if($b=='') return;
		if($c=='') return;
		$chemin = '/home/magicflyer/public_html/img/upload_fairepart/'. $b.'_fairepart.'.$c;
		$tmp = ImageManager::resize($a, $chemin ,500);
		
		return  $b.'_fairepart.'.$c;
		
	}
}
