<?php
class Product extends ProductCore
{
    protected static $_prices = array();
	protected static $_pricesLevel2 = array();
	
	
	public function updateLabels()
	{
		$has_required_fields = 0;
		foreach ($_POST as $field => $value)
			/* Label update */
			if (strncmp($field, 'label_', 6) == 0)
			{
				if (!$tmp = $this->_checkLabelField($field, $value))
					return false;
				/* Multilingual label name update */
				if (!Db::getInstance()->execute('
					INSERT INTO `'._DB_PREFIX_.'customization_field_lang`
					(`id_customization_field`, `id_lang`, `name`) VALUES ('.(int)$tmp[2].', '.(int)$tmp[3].', \''.pSQL($value).'\')
					ON DUPLICATE KEY UPDATE `name` = \''.pSQL($value).'\''))
					return false;
				$is_required = isset($_POST['require_'.(int)$tmp[1].'_'.(int)$tmp[2]]) ? 1 : 0;
				$has_required_fields |= $is_required;
				/* Require option update */
				if (!Db::getInstance()->execute(
					'UPDATE `'._DB_PREFIX_.'customization_field`
					SET `required` = '.(int)$is_required.'
					WHERE `id_customization_field` = '.(int)$tmp[2]))
					return false;
			}

		if ($has_required_fields && !ObjectModel::updateMultishopTable('product', array('customizable' => 2), 'a.id_product = '.(int)$this->id))
			return false;

			
		$actifuppl=Db::getInstance()->getRow('SELECT id_el FROM `'._DB_PREFIX_.'itemstyle_el` WHERE product_id=\''.(int)$this->id.'\'');		
		if ($actifuppl['id_el']=='')
           {		
		    if (!$this->_deleteOldLabels())
			return false;
			}

		return true;
	}
	
	public static function duplicateCustomizationFields($old_product_id, $product_id)
	{
		// If customization is not activated, return success
		if (!Customization::isFeatureActive())
			return true;
		if (($customizations = Product::_getCustomizationFieldsNLabels($old_product_id)) === false)
			return false;
		if (empty($customizations))
			return true;
		foreach ($customizations['fields'] as $customization_field)
		{
			/* The new datas concern the new product */
			$customization_field['id_product'] = (int)$product_id;
			$old_customization_field_id = (int)$customization_field['id_customization_field'];

			unset($customization_field['id_customization_field']);

			if (!Db::getInstance()->insert('customization_field', $customization_field)
				|| !$customization_field_id = Db::getInstance()->Insert_ID())
				return false;

			if (isset($customizations['labels']))   
			{      
			  $data = array();
			  foreach ($customizations['labels'][$old_customization_field_id] as $customization_label)
			  {          
			     $data = array('id_customization_field' => (int)$customization_field_id,'id_lang' => (int)$customization_label['id_lang'],'name' => pSQL($customization_label['name']));
				 $result = Db::getInstance()->AutoExecute( _DB_PREFIX_.'customization_field_lang',$data,'INSERT');
			   }
			 }  
		}
		return true;
	}
	
	public static function addCustomizationPrice(&$products, &$customized_datas)
	{
		if (!$customized_datas)
			return;
			
			
		$cook=new Cookie("priceCUST");	
		$tmpArrayCustomization=array();
		$tmp=array();
	    $tmpPu=array();
		

		if (is_array($products))
		{
		foreach ($products as &$product_update)
		{
			if (!Customization::isFeatureActive())
			{
				$product_update['customizationQuantityTotal'] = 0;
				$product_update['customizationQuantityRefunded'] = 0;
				$product_update['customizationQuantityReturned'] = 0;
			}
			else
			{
			   
				$customization_quantity = 0;
				$customization_quantity_refunded = 0;
				$customization_quantity_returned = 0;

				/* Compatibility */
				$product_id = (int)(isset($product_update['id_product']) ? $product_update['id_product'] : $product_update['product_id']);
				$product_attribute_id = (int)(isset($product_update['id_product_attribute']) ? $product_update['id_product_attribute'] : $product_update['product_attribute_id']);
				$id_address_delivery = (int)$product_update['id_address_delivery'];
				$product_quantity = (int)(isset($product_update['cart_quantity']) ? $product_update['cart_quantity'] : $product_update['product_quantity']);
				$price = isset($product_update['price']) ? $product_update['price'] : $product_update['product_price'];
				$price_wt = $price * (1 + ((isset($product_update['tax_rate']) ? $product_update['tax_rate'] : $product_update['rate']) * 0.01));
               
			    $pricewithoutcustom=$price;
				$price_wtwithoutcustom=$price_wt;
				
            
			 $taux=number_format(Tax::getProductTaxRate($product_id, $id_address_delivery),2, '.', '');
			
			
			$priceWt = self::TTC($price,$taux);
			$priceAttr=0;
			$priceAttr1=0;
				
			 $qtyy=0;
			 
			 if (!isset($customized_datas[$product_id][$product_attribute_id][$id_address_delivery]))
                                        $id_address_delivery = 0;
			
			   if (isset($customized_datas[$product_id][$product_attribute_id]))
			   foreach ($customized_datas[$product_id][$product_attribute_id][$id_address_delivery] as $cle=>$customization)
			    {
						$qtyy += (int)$customization['quantity'];
			    }
					
					if ($qtyy>0 && $qtyy!=$product_quantity)
                    { 					  
					   //Context::getContext()->cart->updateQty(0, $product_id, $product_attribute_id, false);					  
					}					
				
				if (isset($customized_datas[$product_id][$product_attribute_id]))
					foreach ($customized_datas[$product_id][$product_attribute_id][$id_address_delivery] as $cle=>$customization)
					{
						$customization_quantity += (int)$customization['quantity'];
						$customization_quantity_refunded += (int)$customization['quantity_refunded'];
						$customization_quantity_returned += (int)$customization['quantity_returned'];
						
					  $reqsuppl=Db::getInstance()->getRow('SELECT supplement FROM `'._DB_PREFIX_.'itemstyle_cart_el` WHERE idproductattribute=\''.intval($product_attribute_id).'\' and product_id=\''.$product_id.'\' and cart_id=\''.Context::getContext()->cart->id.'\' and id_custom=\''.$cle.'\' and id_order=\'0\'');
						
					 if ($reqsuppl['supplement']!='' && !isset($_GET['id_order']) && !isset($_GET['id_order_slip']))
					{					 
					  $bonus=$reqsuppl['supplement'];
					  $specific_price_output = null;
					  if (Tax::excludeTaxeOption())
					  {					  
                        $p=Tools::ps_round(Product::getPriceStatic($product_id, false, $product_attribute_id,6,null,false,false,1,false,null,null, null, $specific_price_output,true, false,null,true)+$bonus,6);					  
					  }
					  else
					  {	
						 $p=Tools::ps_round(Product::getPriceStatic($product_id, true, $product_attribute_id,6,null,false,false,1,false,null,null, null, $specific_price_output,true, false,null,true)+self::TTC($bonus,$taux),6);
					  }						 					 
                      if ($customization['quantity']>0)
					  {	
						$reduc=self::getReduction($taux,$p,$customization['quantity'],$product_id,$product_attribute_id);						
						if ($reduc>0)
						{
						  $p=$p-$reduc;
						}
						else if ($reduc<0)
						{
						  $p= ($reduc*-1)+$bonus;
						  if ($product_attribute_id>0)
						  {
						    $addDeclinaison=Db::getInstance()->getRow('SELECT price FROM `'._DB_PREFIX_.'product_attribute` WHERE id_product_attribute=\''.intval($product_attribute_id).'\'');
                            if ($addDeclinaison['price']!=0 && $addDeclinaison['price']!='')
                            {
							   $p=$p+$addDeclinaison['price'];							   
							 }							
						  }
						  
						   if (!Tax::excludeTaxeOption())
					      {
						     $p=self::TTC($p,$taux);
						  }
						}
					  }
					  
					  $priceAttr+=($p*(int)($customization['quantity']));    
                     				  
                      
                      $tmpArrayCustomization[$cle]=(Tools::ps_round($p,2)*(int)($customization['quantity']));					  
					 
					}
					else if (isset($_GET['id_order']) || isset($_GET['id_order_invoice']))
				     {                       
					    if (isset($_GET['id_order_invoice'])) 
						
						{
						  $rq1= Db::getInstance()->getRow('SELECT id_order FROM `'._DB_PREFIX_.'order_invoice` WHERE id_order_invoice=\''.intval($_GET['id_order_invoice']).'\'');  
                    	  $_GET['id_order']=$rq1['id_order'];
						}
					   
					    $rq= Db::getInstance()->getRow('SELECT price FROM `'._DB_PREFIX_.'itemstyle_order` WHERE id_order=\''.intval($_GET['id_order']).'\' and id_customization=\''.$cle.'\'');  
                    
					   if ($rq['price']!='')
					   {                       				   
						 $product_update['product_price_wt_'.$cle]=$rq['price'];
                         $product_update['price_'.$cle]=($taux>0) ?  Tools::ps_round($rq['price']/(1+($taux/100)),6) : $rq['price'];				
                         $product_update['product_price_'.$cle]=$product_update['price_'.$cle];                       						
					     $priceAttr+=$rq['price'];						 
					   }
					   else
					   {
					    $product_update['product_price_wt_'.$cle]=$priceWt;
						$product_update['price_'.$cle]=$price;					   
					   }
					   
                     }
					 else if (isset($_GET['id_order_slip']))
					 {					 
					   
						//VOIR POUR LIGNE AVOIR
						$priceByCustomization=Db::getInstance()->getRow('SELECT price,id_order,id_customization,qt FROM `'._DB_PREFIX_.'itemstyle_order` WHERE id_slip=\''.intval($_GET['id_order_slip']).'\'');  		
					    if ($priceByCustomization['price']!='')
						{  
					       $priceAttr1=$priceByCustomization['price']/$priceByCustomization['qt'];
						}
						if ($priceByCustomization['id_order']!='')
						{
						  $array=Db::getInstance()->ExecuteS('SELECT price,id_customization,id_slip FROM `'._DB_PREFIX_.'itemstyle_order` WHERE id_order=\''.intval($priceByCustomization['id_order']).'\'');  		
						  if (is_array($array))
						  {
						     foreach($array as $v)
							 {
							    if ($v['id_customization']==$cle )
								{ 
								  $priceAttr+=Tools::ps_round($v['price'],6);
								}
								 
							 }
						  }
						}						
					 }
						
					}
                $cook->__set('orderCustomization'.$product_id ,serialize($tmpArrayCustomization));
				$product_update['customizationQuantityTotal'] = $customization_quantity;
				$product_update['customizationQuantityRefunded'] = $customization_quantity_refunded;
				$product_update['customizationQuantityReturned'] = $customization_quantity_returned;

				
				if ($customization_quantity>0)
				{	
                  	
				   if ($priceAttr>0)
				   {	
				   
                     $priceWt=Tools::ps_round($priceAttr/$customization_quantity,6); 
                     // hack si produit hors personnalisation moyenne prix
                     if ($product_quantity - $customization_quantity>0)
					{					 
					 
					  $priceWt=($price_wt*($product_quantity - $customization_quantity)+($priceWt*$customization_quantity))/$product_quantity;
					}					 
                
				     $product_update['price']=($taux>0) ? $priceWt/(1+($taux/100)) : $priceWt;						 
					 
					 $product_update['price_wt']=Tools::ps_round($priceWt,2);
				     $product_update['product_price_wt']=Tools::ps_round($priceWt,2);	
					 $product_update['product_price_wt_but_ecotax']=Tools::ps_round($priceWt,2);					
                     $product_update['product_price']=$product_update['price'];	
				     $product_update['unit_price_tax_incl']=Tools::ps_round($priceWt,2);
					 $product_update['unit_price_tax_excl']=$product_update['price'];
				
				 
                     $tmpPu[$product_id][$product_attribute_id]=Tools::ps_round($priceWt,2);
					 
					 $result = Db::getInstance()->AutoExecute( _DB_PREFIX_.'itemstyle_specific',array('PuByProduct'=>serialize($tmpPu),'detailCustomization'=>serialize($cook->getFamily("orderCustomization"))),'UPDATE','uniq=\''.$cook->__get('idFlex').'\'');
				   }
				}
				if ($customization_quantity)
				{
					
					$priceWt=Tools::ps_round($priceWt,2);
					$product_update['price']=isset($product_update['price']) ? $product_update['price'] : $price;
					//$product_update['total_wt'] = $price_wt * ($product_quantity - $customization_quantity);
					//$product_update['total'] = $product_update['price'] * ($product_quantity - $customization_quantity);
					
					$product_update['total_customization_wt'] =$priceWt * $customization_quantity;
					
					$product_update['total_customization'] = $product_update['price'] * $customization_quantity;
					$product_update['total_price_tax_incl']=$product_update['total_customization_wt'];
					$product_update['total_price_tax_excl']=$product_update['price'];
										
										
					//$product_update['total']=$product_update['total_price_tax_excl'];
					//$product_update['total_wt']=$product_update['total_price_tax_incl'];
					
					$product_update['total'] = $product_update['price'] * ($product_quantity - $customization_quantity);
					$product_update['total_wt'] = $product_update['price'] * (1 + ((isset($product_update['tax_rate']) ? $product_update['tax_rate'] : $product_update['rate']) * 0.01)) * ($product_quantity - $customization_quantity);
					
					
					
				}
				//print_r($product_update);
			}
		}
	   }
	}
	public static function TTC($ht,$taux)
	{     
             $priceWt = $ht * (1 + ($taux / 100));
		     $priceWt = Tools::ps_round($priceWt, 6);	
			 return $priceWt;
	}
	
	public static function getReduction($taux,$priceHT,$customizationQuantity,$productId,$id_product_attribute)
	{
	   $reduc=0;
	   
	   $id_currency=Context::getContext()->cookie->id_currency;
	   $id_country=Context::getContext()->customer->geoloc_id_country;
	   $id_customer=Context::getContext()->customer->id;
	   $id_group=Group::getCurrent()->id;
	   $idShop=Context::getContext()->shop->id;
	   $reduc=SpecificPrice::getSpecificPriceHack($productId,$idShop, $id_currency, $id_country, $id_group, $customizationQuantity, $id_product_attribute, $id_customer , 0, $customizationQuantity,$priceHT);
	   
	   return $reduc;
	}
	
public function getProductsPersoImg($product_id){
$sqlQ = 'SELECT id_image, id_product from `'._DB_PREFIX_.'image` WHERE id_product="'.$product_id.'" AND position=3';
$result = Db::getInstance()->ExecuteS($sqlQ);

return 'https://www.magicflyer.com/img/p/'.implode('/',str_split($result[0]['id_image'])).'/'.$result[0]['id_image'].'.jpg';
}
}
?>