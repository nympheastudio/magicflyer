<?php
class OrderInvoice extends OrderInvoiceCore
{
    public function getProductTaxesBreakdown($order = null)
	{
		Tools::$round_mode = $order->round_mode;
		$tmp_tax_infos = array();
		if ($this->useOneAfterAnotherTaxComputationMethod())
		{
			// sum by taxes
			$taxes_infos = Db::getInstance()->executeS('
			SELECT t.`rate` AS `name`, t.`rate`, SUM(`total_amount`) AS `total_amount`
			FROM `'._DB_PREFIX_.'order_detail_tax` odt
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = odt.`id_tax`)
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order_detail` = odt.`id_order_detail`)
			WHERE od.`id_order` = '.(int)$this->id_order.'
			AND od.`id_order_invoice` = '.(int)$this->id.'
			GROUP BY odt.`id_tax`
			');

			 $taxes_infos=self::ajustTvaCustomization($taxes_infos);
			// format response
			foreach ($taxes_infos as $tax_infos)
			{
				$tmp_tax_infos[$tax_infos['rate']]['total_amount'] = $tax_infos['total_amount'];
				$tmp_tax_infos[$tax_infos['rate']]['name'] = $tax_infos['name'];
			}
		}
		else
		{
			// sum by order details in order to retrieve real taxes rate
			$taxes_infos = Db::getInstance()->executeS('
			SELECT t.`rate` AS `name`, od.`total_price_tax_excl` AS total_price_tax_excl, SUM(t.`rate`) AS rate, SUM(`total_amount`) AS `total_amount`, od.`ecotax`, od.`ecotax_tax_rate`, od.`product_quantity`
			FROM `'._DB_PREFIX_.'order_detail_tax` odt
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = odt.`id_tax`)
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order_detail` = odt.`id_order_detail`)
			WHERE od.`id_order` = '.(int)$this->id_order.'
			AND od.`id_order_invoice` = '.(int)$this->id.'
			GROUP BY odt.`id_order_detail`
			');

			// sum by taxes
			$tmp_tax_infos = array();
			$shipping_tax_amount = 0;
			foreach ($order->getCartRules() as $cart_rule)
				if ($cart_rule['free_shipping'])
				{
					$shipping_tax_amount = $this->total_shipping_tax_excl;
					break;
				}

			foreach ($taxes_infos as $tax_infos)
			{
				if (!isset($tmp_tax_infos[$tax_infos['rate']]))
					$tmp_tax_infos[$tax_infos['rate']] = array(
						'total_amount' => 0,
						'name' => 0,
						'total_price_tax_excl' => 0
					);
				$ratio = $tax_infos['total_price_tax_excl'] / $this->total_products;
				$order_reduction_amount = ($this->total_discount_tax_excl - $shipping_tax_amount) * $ratio;
				$tmp_tax_infos[$tax_infos['rate']]['total_amount'] += ($tax_infos['total_amount'] - Tools::ps_round($tax_infos['ecotax'] * $tax_infos['product_quantity'] * $tax_infos['ecotax_tax_rate'] / 100, _PS_PRICE_COMPUTE_PRECISION_));
				$tmp_tax_infos[$tax_infos['rate']]['name'] = $tax_infos['name'];
				$tmp_tax_infos[$tax_infos['rate']]['total_price_tax_excl'] += $tax_infos['total_price_tax_excl'] - $order_reduction_amount - Tools::ps_round($tax_infos['ecotax'] * $tax_infos['product_quantity'], _PS_PRICE_COMPUTE_PRECISION_);
			}
		}
		
		// AJUSTER TVA PRECIS
			if (is_array($tmp_tax_infos))
			{
			  foreach ($tmp_tax_infos as $cle=>$val)
			  {
			     if ($this->total_products>0 && $this->total_products_wt>0)
				 {
				 $tmp_tax_infos[$cle]['total_amount']=$this->total_products_wt-$this->total_products;
				 $tmp_tax_infos[$cle]['total_price_tax_excl']=$this->total_products;
				}
			  }
			}

		foreach ($tmp_tax_infos as &$tax)
		{
			$tax['total_amount'] = Tools::ps_round($tax['total_amount'], _PS_PRICE_DISPLAY_PRECISION_);
			$tax['total_price_tax_excl'] = Tools::ps_round($tax['total_price_tax_excl'], _PS_PRICE_DISPLAY_PRECISION_);
		}
		
		

		return $tmp_tax_infos;
	}
	
	
	public function getProductTaxesBreakdown1($order = null)
	{
		$tmp_tax_infos = array();
		if ($this->useOneAfterAnotherTaxComputationMethod())
		{
			// sum by taxes
			$taxes_infos = Db::getInstance()->executeS('
			SELECT od.product_quantity ,od.product_attribute_id ,odt.`id_order_detail`, t.`rate` AS `name`, t.`rate`, SUM(`total_amount`) AS `total_amount`
			FROM `'._DB_PREFIX_.'order_detail_tax` odt
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = odt.`id_tax`)
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order_detail` = odt.`id_order_detail`)
			WHERE od.`id_order` = '.(int)$this->id_order.'
			AND od.`id_order_invoice` = '.(int)$this->id.'
			GROUP BY odt.`id_tax`
			');

		      $taxes_infos=self::ajustTvaCustomization($taxes_infos);
			// format response
			$tmp_tax_infos = array();
			foreach ($taxes_infos as $tax_infos)
			{
				$tmp_tax_infos[$tax_infos['rate']]['total_amount'] = $tax_infos['total_amount'];
				$tmp_tax_infos[$tax_infos['rate']]['name'] = $tax_infos['name'];
			}

			$shipping_taxes = Db::getInstance()->executeS('
			SELECT *
			FROM `'._DB_PREFIX_.'order_invoice_tax` od
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = od.`id_tax`)
			WHERE `id_order_invoice` = '.(int)$this->id
			);

			foreach ($shipping_taxes as $tax_infos)
			{
				if (!isset($tmp_tax_infos[$tax_infos['rate']]))
				{
					$tmp_tax_infos[$tax_infos['rate']]['total_amount'] = 0;
					$tmp_tax_infos[$tax_infos['rate']]['name'] = 0;
				}

				$tmp_tax_infos[$tax_infos['rate']]['total_amount'] += $tax_infos['amount'];
				$tmp_tax_infos[$tax_infos['rate']]['name'] += $tax_infos['rate'];
			}


		}
		else
		{
			// sum by order details in order to retrieve real taxes rate
			$taxes_infos = Db::getInstance()->executeS('
			SELECT  od.product_quantity ,od.product_attribute_id ,odt.`id_order_detail`, t.`rate` AS `name`, SUM(od.`total_price_tax_excl`) AS total_price_tax_excl, SUM(t.`rate`) AS rate, SUM(`total_amount`) AS `total_amount`
			FROM `'._DB_PREFIX_.'order_detail_tax` odt
			LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = odt.`id_tax`)
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order_detail` = odt.`id_order_detail`)
			WHERE od.`id_order` = '.(int)$this->id_order.'
			AND od.`id_order_invoice` = '.(int)$this->id.'
			GROUP BY odt.`id_order_detail`
			');
	
	
			$taxes_infos=self::ajustTvaCustomization($taxes_infos);
					
			
			// sum by taxes
			$tmp_tax_infos = array();
			foreach ($taxes_infos as $tax_infos)
			{
				if (!isset($tmp_tax_infos[$tax_infos['rate']]))
					$tmp_tax_infos[$tax_infos['rate']] = array(
						'total_amount' => 0,
						'name' => 0,
						'total_price_tax_excl' => 0
					);

				$ratio = $tax_infos['total_price_tax_excl'] / $this->total_products;
				$order_reduction_amount = $this->total_discount_tax_excl * $ratio;

				$tmp_tax_infos[$tax_infos['rate']]['total_amount'] += $tax_infos['total_amount'];
				$tmp_tax_infos[$tax_infos['rate']]['name'] = $tax_infos['name'];
				$tmp_tax_infos[$tax_infos['rate']]['total_price_tax_excl'] += ($tax_infos['total_price_tax_excl'] - $order_reduction_amount);
			}
			
		}
		
		// AJUSTER TVA PRECIS
			if (is_array($tmp_tax_infos))
			{
			  foreach ($tmp_tax_infos as $cle=>$val)
			  {
			     if ($this->total_products>0 && $this->total_products_wt>0)
				 {
				 $tmp_tax_infos[$cle]['total_amount']=$this->total_products_wt-$this->total_products;
				 $tmp_tax_infos[$cle]['total_price_tax_excl']=$this->total_products;
				}
			  }
			}

		return $tmp_tax_infos;
	}
	
	private function ajustTvaCustomization($taxes_infos)
	{	  
	        // tester si existe custom remplacer taxes		    
		    $rq= Db::getInstance()->executeS('SELECT *  FROM `'._DB_PREFIX_.'itemstyle_order` WHERE id_order=\''.$this->id_order.'\'');
	    
			if (is_array($rq))
			{
			   foreach ($rq as $val)
			   {
			     $a=0;				 
				 foreach ($taxes_infos as $tax_infos)
			     {				  
				    $rq1= Db::getInstance()->executeS('SELECT id_customization  FROM `'._DB_PREFIX_.'customization` WHERE id_product_attribute=\''.$tax_infos['product_attribute_id'].'\'');
				    if (is_array($rq1))
			       {
				      foreach ($rq1 as $val1)
			         {
					     if ($val1['id_customization']==$val['id_customization'])
						 {
						   $tax_rate=number_format($tax_infos['name'], 2, '.', '');
						   $newTaxe=(($val['price']/$val['qt'])-Tools::ps_round(($val['price']/$val['qt'])/(1+($tax_rate/100)),3));
						   $price=number_format(Tools::ps_round(($val['price']/$val['qt'])/(1+($tax_rate/100))*$tax_infos['product_quantity'],6),2,'.', '');
						   $taxes_infos[$a]['total_amount']=$newTaxe*$tax_infos['product_quantity'];
						   $taxes_infos[$a]['total_price_tax_excl']=$price;
						 }
						
					 }
				   }
				   $a++;
				 }	
			   }
			}
			
		return 	$taxes_infos;
	   
	}
}

