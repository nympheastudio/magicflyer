<?php
class CustomizationControllerCore extends FrontController
{
}
/*
require_once(dirname(__FILE__).'/../../modules/itemstyle/core/ItemStyleInit.php');
require_once(dirname(__FILE__).'/../../modules/itemstyle/core/ItemStyleCore.php');
require_once(dirname(__FILE__).'/../../modules/itemstyle/core/ItemStyleFront.php');
require_once(dirname(__FILE__).'/../../modules/itemstyle/core/ItemStyleCustom.php');
require_once(dirname(__FILE__).'/../../modules/itemstyle/core/ItemStyleCustomAttribut.php');
class CustomizationControllerCore extends FrontController
{
    public $php_self = 'customization';
 
    public function setMedia()
    {
        parent::setMedia();
        
    }
 

    public function initContent()
    {
        if (isset($_GET['mod']) && $_GET['mod']=='buildForm')
		{
		   $cart=Context::getContext()->cart;
           $idShop=Context::getContext()->shop->id;
           $idProduct=$_GET['idProduct'];
           $face=$_GET['face'];  		   
		   if ((int)$cart->id==0)
          {		    
			Context::getContext()->cart->add();
			Context::getContext()->cookie->id_cart = (int)Context::getContext()->cart->id;
			$cart=Context::getContext()->cart;
		   }
		  
		   $core=new ItemStyleCore();
		   ItemStyleTools::sendCookieGeneral('idCart',(int)$cart->id,'customerCart',time()+(3600*24));
		   $stockData=$core->getPublicStockData($_GET['sessID']);	
		   $stockData['idCart']=Context::getContext()->cart->id;
		  
		   $core->saveStockData($stockData,$_GET['sessID']);	
		
		   $info=array();
	       $idattribute=0;
	       $array=unserialize(stripslashes($_COOKIE["customerLang"]));
           $obj=new ItemStyleFront($array['def'][0],$array['lang']);
	       // a voir caracteres speciaux	
	       $info[0]=$obj->linkPageCart($idShop);
	
	       if (isset($_GET['attribute']) && $_GET['attribute']!='')
	       {
	        $obj1=new ItemStyleCustomAttribut($array['def'][0],$array['lang']);
	        $idattribute=$obj1->getIdProductAttribute($idProduct,$_GET['attribute']);
	       }
	        $donnee=$obj->saveCustomizationCart($idProduct,$face,$idattribute,$idShop,$_POST);
	
	        if (isset($_POST['sauv']))
	        {
	         $d=json_decode($_POST['sauv'],true);
       
	         if (is_array($d['objet']))
	         {	   
	          $chaine=serialize($d['objet']);
	          ItemStyleTools::saveCustomization($idProduct,$chaine);
	         }
	         }
	
	           $info[1]=$obj->buildForm($idProduct,$_GET['qty'], $idattribute);
	          //$info[1]=str_replace("&","&amp;",$info[1]);
	          $info[3]=$donnee[0];
	          $info[4]=$donnee[1];
	          $info[5]=$obj->linkPageOrder($idShop);
	          echo 'var info='.json_encode($info).';';
		     exit;
		}
		else
		{		
	  	  $this->display_column_left = false;
          $this->display_column_right = false;
		  parent::initContent();
          $this->setTemplate(_PS_THEME_DIR_.'customization.tpl');
		}
    }
}*/
?>