<?php
//Commençons par tester si la version de Prestashop est bien définie :
if (!defined('_PS_VERSION_'))
	exit;

//démarrer notre class qui va étendre tout bêtement la class Module.
class export_compta_pisc extends Module
{
	
	public function __construct()
  	{
		$this->name = 'export_compta_pisc';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'author';
		$this->need_instance = 0; 
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6'); 
		$this->dependencies = array('blockcart');
		
		parent::__construct();//Cet appel doit être fait après la création de $this->name et avant toute utilisation de la méthode $this->l()
	 
		$this->displayName = $this->l('export_compta_pisc');
		$this->description = $this->l('Permet l export des factures au format CSV.');
	 
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	 
  	}
	
  	public function getContent()
	{
		$output = '<h2>Export compta</h2>';
		$output .= '<iframe width="700" height="300" frameBorder="0" src="https://www.magicflyer.com/modules/export_compta_pisc/export_comptable.php"></iframe>';
		return $output;
	}
	
  	public function install()
	{
	  if (parent::install() == false)
		return false;
	  return true;
	  
	  if (Shop::isFeatureActive())
		Shop::setContext(Shop::CONTEXT_ALL); //gérer la fonctionnalité multiboutique
	 
	  return parent::install() &&

		$this->registerHook('top');

		//Configuration::updateValue('HOBBYNICE_DATA1', 'test');
	  
	}
	
	public function uninstall()
	{
	  return parent::uninstall() /* && Configuration::deleteByName('HOBBYNICE_DATA1')*/;
	}
	
	

	   
	public function hookTop()
	{
	  
	 
		$test=(int)Tools::getValue('test');
		
		if($test==1)die('ca marche');

		
		
		
	}   
	
	

	


}	
	
	

?>