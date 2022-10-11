<?php
ini_set('display_errors',1);
class perso_mignognetteController extends FrontController
{

	/**
	*  Initialize controller
	* @see FrontController::init()
	*/
	public function init()
	{
		parent::init();
		$this->page_name = 'perso_mignognette'; // page_name and body id
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
		$text_personnalise = '';
		
		$cur_id_product = Tools::getValue('id_product');
		
		$product = new Product((int)$cur_id_product, true, $this->context->language->id, $this->context->shop->id);
		$attachments = self::getWsProductAttachments($cur_id_product);
 $this->context->smarty->assign(array(
     'product' => $product,
	 'attachments' => $attachments,
	 'price_with_taxes' => $product->getPrice(true)

));

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
		
		$this->setTemplate(_PS_THEME_DIR_ . 'perso_mignognette.tpl');
	}
	
	public function add2cart(){
		global $_POST;
		$ids = Tools::getValue('ids');
		$qte = Tools::getValue("qty_livret");
		$id_enveloppe = 226;
		$id_p = Tools::getValue('id_product');
		$id_textfield = 1170;
		

		if(isset($_FILES['photo_fairepart'])){
			$field_name = 'photo_fairepart';
			//echo $_FILES[$field_name]['name'];exit;
			$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');

			$type = strtolower(substr(strrchr($_FILES[$field_name]['name'], '.'), 1));
			
			if (!$temp_name || !move_uploaded_file($_FILES[$field_name]['tmp_name'], $temp_name)){
				
				$this->context->smarty->assign(array('testnotif' => 'Erreur lors de l\'envoi de ta photo !'));
				
			// }elseif ( ! $img_current = $this->creerVignettes($temp_name,$user_id.'_'.rand(5, 1500),$type)    ){
			/*}elseif ( ! $img_current =    ){
				
				
				
				
				$this->context->smarty->assign(array('testnotif' => 'Erreur lors de l\'envoi de ta photo !'));
				
			*/}else{
				
				$uploader = new UploaderCore();
				$uploader->upload($_FILES['photo_fairepart']);
				
				$this->context->smarty->assign(array('testnotif' => 'Photo enregistrée !'));
			}
			
			
			if (isset($temp_name)) @unlink($temp_name);
		}
		
		
		if($ids!=''){
		//echo 'ajout panier depuis perso_mignognette.tpl -> bloqué ds controller !';exit;
	
			// $id_p = Tools::getValue('id_product');//Mignonette + pap classique
			
			$text_personnalise = $img_current;

			$user_id = (int)$this->context->cookie->id_customer;
			
			$this->createCart();
			
		
			if($text_personnalise!=''){
				$this->context->cart->addTextFieldToProduct($id_p, $id_textfield, Product::CUSTOMIZE_TEXTFIELD, $text_personnalise);
			}
			
			$this->context->cart->updateQty($qte, $id_p, null, null);
			
			
			

			
			$ids=explode(",",$ids);
			
			$nb_type_papillon = count(array_keys($ids))-1;
			//echo $nb_type_papillon;exit;
			$multiple_qte = 1;
			
			
			if( $id_p == 324) $multiple_qte = 10;
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
					//$qte_idp=10;
					$p=new Product($idp);
					
					
					
					$quantite_papillon = ($qte * $multiple_qte)/$nb_type_papillon;
					
					
					
					
					$this->context->cart->updateQty($quantite_papillon, $idp, null, false,
        'up', 0, null, true, '');
					
					
					
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
	
	public function creerVignettes($a, $b,$c ){
		
		//$tmp = ImageManager::resize($temp_name, '/home/hobbynice/public_html/img/as/'.$user_id.'.'.$c ,'',200 );
		
		
		$tmp = ImageManager::resize($a, '/home/magicflyer/public_html/_proto/img/upload_fairepart/'. $b.'_fairepart.'.$c ,500);
		
		
		/*$tmp = ImageManager::resize($a, '/home/magicflyer/public_html/img/upload_fairepart/'. $b.'_fp.'.$c ,115);
			$tmp = ImageManager::resize($a, '/home/magicflyer/public_html/img/upload_fairepart/'. $b.'.'.$c ,80);*/
		
		
		//echo '<img src="//www.magicflyer.com/img/upload_fairepart/'. $b.'_fairepart.'.$c.'" />';
		
		return  $b.'_fairepart.'.$c;
		
	}
	
	
	public function getWsProductAttachments($id)
{
    $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
        'SELECT pa.`id_attachment` AS id, a.file, a.file_name
        FROM `' . _DB_PREFIX_ . 'product_attachment` pa
        LEFT JOIN `' . _DB_PREFIX_ . 'attachment` a ON (a.id_attachment = pa.id_attachment)
        ' . Shop::addSqlAssociation('attachment', 'a') . '
        WHERE pa.`id_product` = ' . (int) $id
    );

    return $result;
}
}
