<?php
class SpecificPrice extends SpecificPriceCore
{
  public static function getSpecificPriceHack($id_product, $id_shop, $id_currency, $id_country, $id_group, $quantity, $id_product_attribute = null, $id_customer = 0, $id_cart = 0, $real_quantity = 0,$price)
	{
		   
		   $reduc =0;
		   $now = date('Y-m-d H:i:s');
		   
		  
		   
				$sql='SELECT *, '.SpecificPrice::_getScoreQuery($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_customer).'
				FROM `'._DB_PREFIX_.'specific_price`
				WHERE `id_product` IN (0, '.(int)$id_product.')
				AND `id_product_attribute` IN (0, '.(int)$id_product_attribute.')
				AND `id_shop` IN (0, '.(int)$id_shop.')
				AND `id_currency` IN (0, '.(int)$id_currency.')
				AND `id_country` IN (0, '.(int)$id_country.')
				AND `id_group` IN (0, '.(int)$id_group.')
				AND `id_customer` IN (0, '.(int)$id_customer.')
				AND
				(
					(`from` = \'0000-00-00 00:00:00\' OR \''.$now.'\' >= `from`)
					AND
					(`to` = \'0000-00-00 00:00:00\' OR \''.$now.'\' <= `to`)
				)
				AND id_cart IN (0, '.(int)$id_cart.')'.
				(($real_quantity != 0 && !Configuration::get('PS_QTY_DISCOUNT_ON_COMBINATION')) ? ' AND IF(`from_quantity` > 1, `from_quantity`, 0) <= IF(id_product_attribute=0,'.(int)$quantity.' ,'.(int)$real_quantity.')' : 'AND `from_quantity` <= '.(int)$real_quantity).'
				ORDER BY `id_product_attribute` DESC, `from_quantity` DESC, `id_specific_price_rule` ASC, `score` DESC';
				
				
			
			$specific_price = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
				SELECT *, '.SpecificPrice::_getScoreQuery($id_product, $id_shop, $id_currency, $id_country, $id_group, $id_customer).'
				FROM `'._DB_PREFIX_.'specific_price`
				WHERE `id_product` IN (0, '.(int)$id_product.')
				AND `id_product_attribute` IN (0, '.(int)$id_product_attribute.')
				AND `id_shop` IN (0, '.(int)$id_shop.')
				AND `id_currency` IN (0, '.(int)$id_currency.')
				AND `id_country` IN (0, '.(int)$id_country.')
				AND `id_group` IN (0, '.(int)$id_group.')
				AND `id_customer` IN (0, '.(int)$id_customer.')
				AND
				(
					(`from` = \'0000-00-00 00:00:00\' OR \''.$now.'\' >= `from`)
					AND
					(`to` = \'0000-00-00 00:00:00\' OR \''.$now.'\' <= `to`)
				)
				AND id_cart IN (0, '.(int)$id_cart.')'.
				(($real_quantity != 0 && !Configuration::get('PS_QTY_DISCOUNT_ON_COMBINATION')) ? ' AND IF(`from_quantity` > 1, `from_quantity`, 0) <= IF(id_product_attribute=0,'.(int)$quantity.' ,'.(int)$real_quantity.')' : 'AND `from_quantity` <= '.(int)$real_quantity).'
				ORDER BY `id_product_attribute` DESC, `from_quantity` DESC, `id_specific_price_rule` ASC, `score` DESC');
		
		
		
		 if ($specific_price['reduction_type'] == 'amount')
			{
				$reduction_amount = $specific_price['reduction'];
			    
				if (!$specific_price['id_currency'])
					$reduction_amount = Tools::convertPrice($reduction_amount, $id_currency);
				$reduc = Tools::ps_round($reduction_amount, 2);			
			}
			else	
            {	
				
				$reduc = Tools::ps_round($price * $specific_price['reduction'], 2);				 
			}
			
			 
			
			  $reduction_from_category = GroupReduction::getValueForProduct($id_product, $id_group);
			  if ($reduction_from_category>0)
				{
				  $reduc+= Tools::ps_round($price *(float)$reduction_from_category/100, 2);	                 				  
			 	}
				else if ((float)Group::getReductionByIdGroup($id_group)>0)
				{				   
				   $reduc+= Tools::ps_round($price *(float)Group::getReductionByIdGroup($id_group)/100, 2);	
				}
			

              if ($specific_price['price']>0)	
              {			    
			    $reduc=-$specific_price['price'];
			  }		
		
		
		return $reduc;
	}
}

