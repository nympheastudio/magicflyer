<?php
class OrderSlip extends OrderSlipCore
{
 
 
 public static function createPartialOrderSlip($order, $amount, $shipping_cost_amount, $order_detail_list)
	{
		$currency = new Currency($order->id_currency);
		$orderSlip = new OrderSlip();
		$array=array();
		
		$mod=0;		
		if (is_array($_POST['partialRefundProductQuantity']))
		{
		   foreach($_POST['partialRefundProductQuantity'] as $val)
		   {
		      if ($val>0)
			  {
			    $mod=$val;				
			  }
		   }
		}
		
		if (is_array($_POST['product_quantity']) && $mod>0)
		{
		   foreach ($_POST['product_quantity'] as $cle=>$val)
		   {
			  if ($cle>0 )
			  {
			       $priceByCustomization=Db::getInstance()->ExecuteS('SELECT price,qt FROM `'._DB_PREFIX_.'itemstyle_order` WHERE id_order=\''.(int)($order->id).'\' and id_customization=\''.$cle.'\'');		
 		           if (is_array($priceByCustomization))
                   {                      
					  $amount=0;
				      $qtt=0;
					  
				      foreach($priceByCustomization as $val)
				      {				  
				        $amount+=$val['price'];
				        $qtt+=$val['qt'];
				      }
				       $amount = ($amount/$qtt)*$mod;
                    }
			  }
		   }
		}
		  
		foreach($order_detail_list as $cle=> $val)
		{
		  $order_detail_list[$cle]['amount'] = $amount;
		}
				
		$orderSlip->id_customer = (int)($order->id_customer);
		$orderSlip->id_order = (int)($order->id);
		$orderSlip->amount = (float)($amount);
		$orderSlip->shipping_cost = false;
		$orderSlip->shipping_cost_amount = (float)($shipping_cost_amount);
		$orderSlip->conversion_rate = $currency->conversion_rate;
		$orderSlip->partial = 1;
		
		
		
		if (!$orderSlip->add())
			return false;

		
		$orderSlip->addPartialSlipDetail($order_detail_list);
		
		return true;
	}
		
	
		
	public function getTotal_price_tax_incl($id_order_detail)
	{
	       $total=0;
		   $p=Db::getInstance()->getRow('SELECT id_order FROM `'._DB_PREFIX_.'order_detail` WHERE id_order_detail=\''.(int)($id_order_detail).'\'');		
		   $priceByCustomization=Db::getInstance()->ExecuteS('SELECT price,qt FROM `'._DB_PREFIX_.'itemstyle_order` WHERE id_order=\''.(int)($p['id_order']).'\'');		
 		   if (is_array($priceByCustomization))
               {
				    foreach($priceByCustomization as $val)
				   {
					$total+=$val['price'];
				   }
			   }
			return $total;   
	}
 
 public function addPartialSlipDetail($order_detail_list)
	{	
		
		foreach ($order_detail_list as $id_order_detail => $tab)
		{
			$order_detail = new OrderDetail($id_order_detail);
			$order_slip_resume = self::getProductSlipResume($id_order_detail);
			
			 $totalC=self::getTotal_price_tax_incl($id_order_detail);
			 if ($totalC>0)
			 {
			  $order_detail->total_price_tax_incl= $totalC;
			 }
			
			
			$id_tax = (int)Db::getInstance()->getValue('SELECT `id_tax` FROM `'._DB_PREFIX_.'order_detail_tax` WHERE `id_order_detail` = '.(int)$id_order_detail);
			
						
			if ($tab['amount'] + $order_slip_resume['amount_tax_incl'] > $order_detail->total_price_tax_incl)
			{
				$tab['amount'] = $order_detail->total_price_tax_incl - $order_slip_resume['amount_tax_incl'];
			}
			
			
			if ($tab['amount'] == 0)
				continue;
			
			if ($tab['quantity'] + $order_slip_resume['product_quantity'] > $order_detail->product_quantity)
				$tab['quantity'] = $order_detail->product_quantity - $order_slip_resume['product_quantity'];
			
			$tab['amount_tax_excl'] = $tab['amount_tax_incl'] = $tab['amount'];
			
			
			
			if ($id_tax > 0)
			{
				$rate = (float)Db::getInstance()->getValue('SELECT `rate` FROM `'._DB_PREFIX_.'tax` WHERE `id_tax` = '.(int)$id_tax);
				if ($rate > 0)
				{
					$rate = 1 + ($rate / 100);
					$tab['amount_tax_excl'] = $tab['amount_tax_excl'] / $rate;
				}
			}
			
		
			
			if ($tab['quantity'] > 0 && $tab['quantity'] > $order_detail->product_quantity_refunded)
			{
				$order_detail->product_quantity_refunded = $tab['quantity'];
				$order_detail->save();
			}
			
				
			
			$insertOrderSlip = array(
				'id_order_slip' => (int)($this->id),
				'id_order_detail' => (int)($id_order_detail),
				'product_quantity' => (int)($tab['quantity']),
				'amount_tax_excl' => (float)($tab['amount_tax_excl']),
				'amount_tax_incl' => (float)($tab['amount_tax_incl']),
			);
			
			Db::getInstance()->insert('order_slip_detail', $insertOrderSlip);
			
		}
		
	}

}