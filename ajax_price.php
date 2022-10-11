<?php
ini_set('display_errors',1);
require(dirname(__FILE__).'/config/settings.inc.php');
require(dirname(__FILE__).'/config/defines.inc.php');
require(dirname(__FILE__).'/config/config.inc.php');

$tva = 20/100 ;

$qty= (int)$_GET['qty'];
$id_product = (int)$_GET['id_product'];

$sql = 'SELECT * FROM ps_product WHERE id_product='.$id_product;
$products = Db::getInstance()->ExecuteS( $sql );


 
foreach($products as $product){
        //$productObj = new Product($product['id_product']);
		//if($product['id_product']==7){
			
		
		
		$specific_price = SpecificPrice::getSpecificPrice(
                (int)$product['id_product'],
                1,
                1,
                $id_country,
                $id_group,
                $qty,
                $id_product_attribute,
                $id_customer,
                $id_cart,
                $real_quantity
            );
		
		
		$prix_unitaire_ttc = $product['price'] * (1 + $tva);
		
		$prix_total_ttc = round( ($prix_unitaire_ttc * $qty)-($specific_price["reduction"]*$qty),2); 
		//echo $product['id_product'].'<br>';
		//echo round($prix_ttc,2).'€ TTC<br>';
		//echo $specific_price["reduction"].'€<hr>';
		
		//var_dump($specific_price).'€<hr>';
		echo $prix_total_ttc;
		exit;
		//}
		
}

?>