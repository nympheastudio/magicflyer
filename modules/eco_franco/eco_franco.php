	<?php
	if ( !defined( '_PS_VERSION_' ) )
	  exit;
	require_once(_PS_MODULE_DIR_."eco_franco/install.php");
	//require_once(_PS_MODULE_DIR_."eco_franco/class/franco.php");
	
	class eco_franco extends Module{
  
    public function __construct(){
		$this->name = 'eco_franco';
		$this->tab = 'front_office_features';
		$this->version = 3.0;
		$this->author = 'EcomiZ';
		$this->need_instance = 0;
		$this->module_key = '495fac2e40b5fa02023ba58dc1ecdf44';

		parent::__construct();

		$this->displayName = $this->l('shipping');
		$this->description = $this->l('shipping costs for an area or a carrier.');
		
		 
    }

     
	
 public function install() {
		global $smarty;
		$errors = array();
		$instaled = null;
		$fichier =_PS_ROOT_DIR_.'/override/classes/Cart.php';
		
		$file = file_get_contents($fichier);
		if(!strpos($file, "getPackageShippingCost")) {
		$exists = 0;
		}
		else $exists = 1;
		
		if ($exists == 1) {
		// mettre manuellement le code en cas ou la fontion getPackageShippingCost() déja redefini
			 $errors[] =  $this->l('Caution Class cart.php and overburdened, so you must add the following line in the function');		
			$instaled = false;
		}
		else{
			
			if(Modulefranco::ModulefrancoInstall()){
			
				return (parent::install()
				AND $this->registerHook('LeftColumn')
				AND $this->registerHook('displayShoppingCartFooter')
				// displayShoppingCart position plus en bas
				// AND $this->registerHook('displayShoppingCart')
				AND $this->registerHook('header'));
				$instaled = true;
				}
				
				else{
				$errors[] = $this->l('Thank you to grant the right on the 777 directory override / classes before starting the installation');
				$instaled = false;
			}
		}
		$smarty->assign('errors', $errors);
		
		
	}
		
	public function uninstall(){
		Modulefranco::ModulefrancoDesinstall();
		return (parent::uninstall());
		
    }
	
	public function getContent(){
	$_SESSION['eco_franco_url'] = ''; 
		echo"<link rel='stylesheet' href='"._MODULE_DIR_."eco_franco/resource/BO/css/validationEngine.jquery.css' type='text/css'/>
        <link rel='stylesheet' href='"._MODULE_DIR_."eco_franco/resource/BO/css/template.css'/>
        <script src='"._MODULE_DIR_."eco_franco/resource/BO/js/jquery-1.6.min.js' type='text/javascript'>
        </script>
        <script src='"._MODULE_DIR_."eco_franco/resource/BO/js/jquery.validationEngine-en.js' type='text/javascript' charset='utf-8'>
        </script>
        <script src='"._MODULE_DIR_."eco_franco/resource/BO/js/jquery.validationEngine.js' type='text/javascript' charset='utf-8'>
        </script>
        <script>
            jQuery(document).ready(function(){
                jQuery('#formID').validationEngine();
            });
        </script>";
		if(!isset($_GET['regles_id']) || empty($_GET['regles_id']) ){
		
					if(isset($_POST['action']) && $_POST['action']){
						 eco_franco::action($_POST['id_francoregles'],$_POST['libelle_francoregles'],$_POST['montant_francoregles'],$_POST['action']);
					     return' '.eco_franco::AfficheTA().' ';
					}
						
				if(!isset($_GET['id_regles']) || empty($_GET['id_regles']) ){
				$_SESSION['eco_franco_url'] = Tools::htmlentitiesutf8($_SERVER['REQUEST_URI']);
					return' '.eco_franco::AfficheTA().' ';
					
					
				}
				else{
					if($_GET['id_regles']==-2){
						$rowSiteUpdate['id_francoregles'] = "-2";
						$rowSiteUpdate['libelle_francoregles'] ="";
						$rowSiteUpdate['montant_francoregles'] ="";
						$strAction = "ADD";
					}
					else{
						
						$sqlSiteUpdate="SELECT * From "._DB_PREFIX_."eco_francoregles where id_francoregles='".mysql_escape_string($_GET['id_regles'])."'";
						if ($rowSiteUpdate = Db::getInstance()->getRow($sqlSiteUpdate)){
							if(isset($_GET['del']) && $_GET['del']== 1){
								$strAction = "DELETE";
							}
							else{
									$strAction = "UPDATE";
							}	
						}
						
					}
					
					// Affichage formulkaire avec les champ input remplu avec les variable
					return' '.eco_franco::affiche_form($rowSiteUpdate,$strAction).' ';
				
				}
		
		}
		else{
/******************************************************************************************************/
			if(isset($_POST['action']) && $_POST['action']){
				 eco_franco::actionb($_POST['francoconditions_id'],$_POST['francoregles_id'],$_POST['francoconditions_type'],$_POST['francoconditions_id_type'],$_POST['action']);
				 return' '.eco_franco::AfficheTB().' ';
			}
			if(!isset($_GET['id_conditions']) || empty($_GET['id_conditions']) ){
				$_SESSION['eco_francocondition_url'] = Tools::htmlentitiesutf8($_SERVER['REQUEST_URI']);
				return' '.eco_franco::AfficheTB().' ';
			}
			else{
						if($_GET['id_conditions']==-2 && $_GET['ajt']){
							$rowSiteUpdate['id_francoconditions'] = "-2";
							if($_GET['ajt'] == '1'){
								$rowSiteUpdate['francoconditions_type'] = '1';
							}
							else 
							if($_GET['ajt'] == '2'){
							$rowSiteUpdate['francoconditions_type'] = '2';
							}
							else{	
							$rowSiteUpdate['francoconditions_type'] = '3';
							}
							$strAction = "ADD";
							
							
						}
						else{
							
							$sqlSiteUpdate="SELECT * From "._DB_PREFIX_."eco_francoconditions where francoconditions_id='".mysql_escape_string($_GET['id_conditions'])."'";
							if ($rowSiteUpdate = Db::getInstance()->getRow($sqlSiteUpdate)){
								if(isset($_GET['del']) && $_GET['del']== 1){
									$strAction = "DELETE";
									
								}
								else{
										$strAction = "UPDATE";
								}	
							}
							
						}
					// Affichage formulkaire avec les champ input remplu avec les variable
					return' '.eco_franco::affiche_formb($rowSiteUpdate,$strAction).' ';
				
				}
				
		
		}
		
	}
	
	
	public function hookdisplayShoppingCartFooter ( $params ){
				global $smarty;	
				global $cart;
				$mantant = NULL;
				$idRegle = 1; 
				// requete recuperation monbtant min 
				$StrMantant = "SELECT montant_francoregles FROM "._DB_PREFIX_."eco_francoregles ORDER BY id_francoregles LIMIT 1";
					if ($list = Db::getInstance()->ExecuteS($StrMantant)){
						foreach ($list as $row){
						$mantant = $row["montant_francoregles"];
						}
					}
				$totalTtc = $cart->getOrderTotal(true,4);
				if($mantant != NULL){
					if($totalTtc < $mantant){
						$fReste = $mantant - $totalTtc;
					$smarty->assign('mantant',$fReste);
					 return $this->display( __FILE__, 'cartfooter.tpl' );
					}
				}
	}
	 
	
	/********************IMPORTE DE EX eco_franco ********************************/
	//Afficher le tableau de données
			public static function AfficheTA() {
			$form="";
			$strfranco = new eco_franco();
			$form .='
			<fieldset>
			<legend>'.$strfranco->l('Lists of area and carrier with free shipping :', 'franco').'</legend>
			<div><a class="button" href="'.Tools::htmlentitiesutf8($_SERVER['REQUEST_URI']).'&id_regles=-2"><img src="'._MODULE_DIR_.'eco_franco/resource/images/add.gif">'.$strfranco->l('Add a rule', 'franco').'</a></div>
			<br />
			<div class="demo_jui">
				<table cellpadding="0" cellspacing="0" border="0" class="table " id="example">
				<thead>
				<tr>
				<th>'.$strfranco->l('ID', 'franco').'</th>
				<th>'.$strfranco->l('libel', 'franco').'</th>
				<th>'.$strfranco->l('Price', 'franco').'</th>
				<th>'.$strfranco->l('Action', 'franco').'</th>
				</tr>
				</thead>
				<tbody>'.eco_franco::Afficheregles().'</tbody></table></div></fieldset><br />'.eco_franco::Support().'';
				return $form;
			}
			public static function AfficheTB() {
			$form="";
			$strfranco = new eco_franco();
					$form .='
					<fieldset>
					<legend>'.$strfranco->l('List of conditions for this rule :', 'franco').'</legend>
					<div>
					<a class="button" href="'.Tools::htmlentitiesutf8($_SERVER['REQUEST_URI']).'&id_conditions=-2&ajt=1"><img src="'._MODULE_DIR_.'eco_franco/resource/images/add.gif">'.$strfranco->l('Add Zone', 'franco').'</a>
					<a class="button" href="'.Tools::htmlentitiesutf8($_SERVER['REQUEST_URI']).'&id_conditions=-2&ajt=2"><img src="'._MODULE_DIR_.'eco_franco/resource/images/add.gif">'.$strfranco->l('Add Carrier', 'franco').'</a>
					<a class="button" href="'.Tools::htmlentitiesutf8($_SERVER['REQUEST_URI']).'&id_conditions=-2&ajt=3"><img src="'._MODULE_DIR_.'eco_franco/resource/images/add.gif">'.$strfranco->l('Add Group', 'franco').'</a>
					</div>
					<br/>
					<div class="demo_jui">
						<table cellpadding="0" cellspacing="0" border="0" class="table tableDnD" id="border">
						<thead>
						<tr>
						<th>'.$strfranco->l('Rules', 'franco').'</th>
						<th>'.$strfranco->l('Type', 'franco').'</th>						
						<th>'.$strfranco->l('Name', 'franco').'</th>
						<th>'.$strfranco->l('Action', 'franco').'</th>
						</tr>
						</thead>
						<tbody>'.eco_franco::Afficheconditions().'</tbody></table></div>
						<br />
						<div>
						 <a href="'.$_SESSION['eco_franco_url'].'" class="button"><img src="../img/admin/arrow2.gif">retour</a>
						</div>	
						<br />						
						</fieldset><br/>'.eco_franco::Support().'';
						return $form;
					}
			public static function Afficheregles() {
			$strfranco = new eco_franco();
			$form="";
			$sql = "SELECT * FROM "._DB_PREFIX_."eco_francoregles ORDER by id_francoregles Desc";
				if ($list = Db::getInstance()->ExecuteS($sql)){
					foreach ($list as $row){
						$form .="<tr class=''>
						<td >".$row["id_francoregles"]."</td>
						<td >".$row["libelle_francoregles"]."</td>
						<td >".$row["montant_francoregles"]."</td>";
						$form .="<form action='".Tools::htmlentitiesutf8($_SERVER['REQUEST_URI'])."' method='post'>
							<td>
							<input type='hidden' name='id' value='".$row['id_francoregles']."'/>
							<a href='".Tools::htmlentitiesutf8($_SERVER['REQUEST_URI'])."&id_regles=".$row["id_francoregles"]."'><img src='"._MODULE_DIR_."eco_franco/resource/images/edit.gif' title=".$strfranco->l('Update', 'franco')."></a>
							<a href='".Tools::htmlentitiesutf8($_SERVER['REQUEST_URI'])."&id_regles=".$row["id_francoregles"]."&del=1'><img src='"._MODULE_DIR_."eco_franco/resource/images/delete.gif' title=".$strfranco->l('Delete', 'franco')."></a>	
							<a href='".Tools::htmlentitiesutf8($_SERVER['REQUEST_URI'])."&regles_id=".$row["id_francoregles"]."'><img src='"._MODULE_DIR_."eco_franco/resource/images/details.gif' title=".$strfranco->l('Detail', 'franco')."></a>							
							</td>
							</tr>
							</form>";

					}
				}
			return $form;
		}
		public static function Afficheconditions() {
		$form="";
		$strfranco = new eco_franco();
		global $smarty, $cookie;
		$id_lang =((int)$cookie->id_lang);
			$sql = "SELECT *
			FROM "._DB_PREFIX_."eco_francoconditions 
			LEFT JOIN "._DB_PREFIX_."eco_francoregles
			ON  "._DB_PREFIX_."eco_francoconditions.francoregles_id= "._DB_PREFIX_."eco_francoregles.id_francoregles
			where francoregles_id='".mysql_escape_string($_GET['regles_id'])."' ";
				if ($list = Db::getInstance()->ExecuteS($sql)){
					foreach ($list as $row){
					if($row["francoconditions_type"]==1){
					$type = 'zone';
					$Strtype = $strfranco->l('Zone', 'franco');
					}
					else if($row["francoconditions_type"]==2){
					$type = 'carrier';
					$Strtype = $strfranco->l('Carrier', 'franco');
					}
					else{
					$type = 'group';
					$Strtype = $strfranco->l('Group', 'franco');
					}
						$form .="<tr>
						<td >".$row["libelle_francoregles"]."</td>
						<td >".$Strtype."</td>";
						
				if(($row["francoconditions_type"]<> 1) And ($row["francoconditions_type"]<> 2)){
				$req = "SELECT * 
				FROM "._DB_PREFIX_.$type."_lang
				Where id_".$type."=".$row["francoconditions_id_type"]." 
				AND `id_lang` = ".$id_lang."
				ORDER by id_".$type." ASC";
				// echo$req;exit;
				}else{				
						$req = "SELECT * FROM "._DB_PREFIX_.$type." Where id_".$type."=".$row["francoconditions_id_type"]." ORDER by id_".$type." ASC";
					}	// echo $req;exit;
						if ($lis = Db::getInstance()->ExecuteS($req)){
						foreach ($lis as $rows){
						$form .="<td >".$rows["name"]."</td>";
						}
						}
						$form .="<form action='".Tools::htmlentitiesutf8($_SERVER['REQUEST_URI'])."' method='post'>
							<td>
							<input type='hidden' name='id' value='".$row['francoconditions_id']."'/>
							<a href='".Tools::htmlentitiesutf8($_SERVER['REQUEST_URI'])."&id_conditions=".$row["francoconditions_id"]."&ajt=".$row["francoconditions_type"]."&id=".$row["francoconditions_id"]."'><img src='"._MODULE_DIR_."eco_franco/resource/images/edit.gif' title=".$strfranco->l('Update', 'franco')."></a>
							<a href='".Tools::htmlentitiesutf8($_SERVER['REQUEST_URI'])."&id_conditions=".$row["francoconditions_id"]."&ajt=".$row["francoconditions_type"]."&id=".$row["francoconditions_id"]."&del=1'><img src='"._MODULE_DIR_."eco_franco/resource/images/delete.gif' title=".$strfranco->l('Delete', 'franco')."></a>								
							</td>
						</tr></form>";

					}
				}
			return $form;
		}
		public static function affiche_form($rowSiteUpdate,$strAction){
		 $form="";
		$strfranco = new eco_franco();
			global $smarty, $cookie;
			if($strAction ==  'ADD'){
			$strlibelleaction = $strfranco->l('ADD', 'franco');
			}
			else{ 
			if($strAction ==  'DELETE'){
				$strlibelleaction = $strfranco->l('DELETE', 'franco');
			}		
			else{
			$strlibelleaction = $strfranco->l('UPDATE', 'franco');	
			}
			}
		$form .='
		<form  id="formID" class="formular" action="'.$_SESSION['eco_franco_url'].'"  method="POST">
		  <label>'.$strfranco->l('Name Rule', 'franco').':</label>
		  <input type="texte" name="libelle_francoregles" id="libelle_francoregles" value="'.$rowSiteUpdate['libelle_francoregles'].'">
		  <label>'.$strfranco->l('Amount', 'franco').':</label>
		  <input type="texte" name="montant_francoregles" id="montant_francoregles" value="'.$rowSiteUpdate['montant_francoregles'].'">
		  <input type="hidden" name="id_francoregles" value="'.$rowSiteUpdate['id_francoregles'].'">
		  <input type="hidden" name="action" value="'.$strAction.'">
		  <input class="button" type="submit" value="'.$strlibelleaction.'">
		</form>
		<div>
        <a href="'.$_SESSION['eco_franco_url'].'" class="button"><img src="../img/admin/arrow2.gif">retour</a></div><br />'.eco_franco::Support().'';
		   return $form;
		  
		}		
		
		public static function action($id, $libelle, $montant, $strAction){
			$strRequete="";
			if($strAction == 'ADD'){
				$strRequete = 'INSERT INTO '._DB_PREFIX_.'eco_francoregles(`libelle_francoregles`,`montant_francoregles`) 
										VALUES("'.$libelle.'","'.$montant.'")';
				 Db::getInstance()->Execute($strRequete);
			}
			else if($strAction == 'UPDATE'){
				Db::getInstance()->autoExecute(_DB_PREFIX_."eco_francoregles", array(
				'libelle_francoregles' =>    pSQL($libelle),
				'montant_francoregles' =>    pSQL($montant),
				), 'UPDATE', 'id_francoregles = '.pSQL($id));
			}
			else{
				$strRequete ='DELETE FROM  '._DB_PREFIX_.'eco_francoregles WHERE id_francoregles = '.intval($id);
				 Db::getInstance()->Execute($strRequete);
			}
		
		
		}	


		public static function affiche_formb($rowSiteUpdate,$strAction){
			//echo 'test';exit;
			$sqlUpdate="";
			$req=false;
			$form="";
			$strCondition="";
			$strfranco = new eco_franco();
			
			global $smarty, $cookie;
			$id_lang =((int)$cookie->id_lang);
			if($strAction ==  'ADD'){
				$strlibelleaction = $strfranco->l('ADD', 'franco');
			}
			
			
			else{ 
				if($strAction ==  'DELETE'){
					$strlibelleaction = $strfranco->l('DELETE', 'franco');
				}		
				else{
				$strlibelleaction = $strfranco->l('UPDATE', 'franco');	
				}
			}
            if($rowSiteUpdate["francoconditions_type"]==1){
					$type = 'zone';
					$strCondition = ' AND active = 1';
			}
			else if($rowSiteUpdate["francoconditions_type"]==2){
					$type = 'carrier';
					$strCondition = '  AND deleted = 0 ';
			}
			else{
					$type = 'group';
					$Strtype_lang= 'group_lang';
			}		
					
			$form .='<form id="formID" class="formular" action="'.(isset($_SESSION['eco_francocondition_url']) ? $_SESSION['eco_francocondition_url'] : '').'"  method="POST">
					<input type="hidden" name="francoregles_id" value="'.$_GET['regles_id'].'">
					<input type="hidden" name="francoconditions_type" value="'.(isset($rowSiteUpdate['francoconditions_type'])? $rowSiteUpdate['francoconditions_type']: '').'">
					<input type="hidden" name="francoconditions_id" value="'.(isset($rowSiteUpdate['francoconditions_id'])? $rowSiteUpdate['francoconditions_id']: '').'">
					<label>'.$strfranco->l('Choose', 'franco').':</label>	
					<select name="francoconditions_id_type" id="zonetransp"  class="validate[required]">';
			
			if(($rowSiteUpdate["francoconditions_type"]<> 1) And ($rowSiteUpdate["francoconditions_type"]<> 2)){			
				if($strAction ==  'ADD'){
							$sql = 'SELECT *
					FROM '._DB_PREFIX_.$Strtype_lang.'
					where `id_lang` = '.$id_lang.' order by id_'.$type.'  ASC';
					$form .='<option value="" selected>- - -</option>';
					
				}
				
				else{
				
					$sql = 'SELECT *
					FROM '._DB_PREFIX_.$Strtype_lang.'
					where `id_lang` = '.$id_lang.'
					AND  
					id_'.$type.'='.mysql_escape_string($rowSiteUpdate['francoconditions_id_type']).' order by id_'.$type.' ASC';
					$req = True;
						
				}		
				if ($result = Db::getInstance()->ExecuteS($sql)){
					//echo 'test2';
					foreach ($result as $roww){
					$form .='<option value="'.$roww['id_'.$type.''].'" >'.$roww['name'].'</option>';
					}
					 
				}
				if($req == True){
					//echo 'test2';
					$sqlUpdate='SELECT * FROM '._DB_PREFIX_.''.mysql_escape_string($Strtype_lang).'
					WHERE id_'.mysql_escape_string($type).'<>'.$roww['id_'.$type.''].'
					AND  `id_lang` = '.$id_lang;		
				
					//echo 'test2';
					//echo $sqlUpdate;exit;
					if ($rowUpdate = Db::getInstance()->ExecuteS($sqlUpdate)){			
						foreach ($rowUpdate as $rows){
						$form .='<option value="'.$rows['id_'.$type.''].'">'.$rows['name'].'</option>';
					}
				}
			}
				// echo $sq                                                                                                                                                                                                                                                                                                         l;exit;
		}
		else{
				if($strAction !=  'ADD'){
					$sql = 'SELECT *
								FROM '._DB_PREFIX_.$type.'
								where id_'.$type.'='.mysql_escape_string($rowSiteUpdate['francoconditions_id_type']).' '.$strCondition;
					/// echo $sql;

					if ($result = Db::getInstance()->ExecuteS($sql)){
						foreach ($result as $roww){
							$form .='<option value="'.$roww['id_'.$type.''].'" selected>'.$roww['name'].'</option>';
						}
						True;
					}
					else{
						$form .='<option value="" selected>- - -</option>';
					}
				}
			
			if($strAction !=  'ADD'){
				if($result == True){
					$sqlSiteUpdate='SELECT * FROM '._DB_PREFIX_.''.mysql_escape_string($type).' WHERE id_'.mysql_escape_string($type).'<>'.$roww['id_'.$type.''].' '.$strCondition;
				}else{
					$sqlSiteUpdate='SELECT * FROM '._DB_PREFIX_.''.mysql_escape_string($type).' WHERE 1 '.$strCondition;
					//echo $sqlSiteUpdate;
				}
			}else{
				$sqlSiteUpdate='SELECT * FROM '._DB_PREFIX_.''.mysql_escape_string($type).' WHERE 1 '.$strCondition;
			}
				
			if ($rowSiteUpdate = Db::getInstance()->ExecuteS($sqlSiteUpdate)){			
				foreach ($rowSiteUpdate as $row){
					$form .='<option value="'.$row['id_'.$type.''].'">'.$row['name'].'</option>';
				}
			}
			}
			
		$form .='</select>
		  <input type="hidden" name="action" value="'.$strAction.'">
		  <input class="button" type="submit" value="'.$strlibelleaction.'">
		</form><br /><div>
        <a href="'.(isset($_SESSION['eco_francocondition_url']) ? $_SESSION['eco_francocondition_url'] : '').'" class="button"><img src="../img/admin/arrow2.gif">retour</a></div><br />
		'.eco_franco::Support().'';
		   return $form;
		 
		}		
		
		public static function actionb($id, $francoregles_id, $francoconditions_type, $francoconditions_id_type, $strAction){
		
		$strfranco = new eco_franco();
			if($strAction == "ADD"){
			$result ='SELECT francoregles_id, francoconditions_type 
			FROM 
			'._DB_PREFIX_.'eco_francoconditions WHERE francoregles_id ='.$francoregles_id.' AND francoconditions_type ='.$francoconditions_type.'';
			if ($list = Db::getInstance()->ExecuteS($result) && $francoconditions_type != 3){
					if($francoconditions_type==1){
					$Strtype = $strfranco->l('Zone', 'franco');
					}else if($francoconditions_type==2){
					$Strtype = $strfranco->l('Carrier', 'franco');
					}else{
						$Strtype = $strfranco->l('Groups', 'franco');
					}
				echo"<div class='error'><span style='float:right'><a id='hideError' href=''><img alt='X' src='../img/admin/close.png'></a></span><img src='../img/admin/error2.png'>".$strfranco->l('You can not add a single ', 'franco').$Strtype."</div>";
			}else{
				$strRequete = 'INSERT INTO '._DB_PREFIX_.'eco_francoconditions(`francoregles_id`,`francoconditions_type`,`francoconditions_id_type`) 
									VALUES("'.$francoregles_id.'","'.$francoconditions_type.'","'.$francoconditions_id_type.'")';
				Db::getInstance()->Execute($strRequete);
				echo "<div class='conf'><span style='float:right'><a id='hideError' href=''><img src='../img/admin/close.png' alt=''></a></span><img src='../img/admin/ok2.png'>".$strfranco->l('Update successful','franco')."</div>";
			}
			}
			else if($strAction == 'UPDATE'){
				Db::getInstance()->autoExecute(_DB_PREFIX_."eco_francoconditions", array(
				'francoregles_id' =>    pSQL($francoregles_id),
				'francoconditions_type' =>    pSQL($francoconditions_type),
				'francoconditions_id_type' =>    pSQL($francoconditions_id_type),
				), 'UPDATE', 'francoconditions_id = '.pSQL($id));
			}
			else{
				$strRequete ='DELETE FROM  '._DB_PREFIX_.'eco_francoconditions 
									WHERE francoconditions_id = '.intval($id);
				Db::getInstance()->Execute($strRequete);
			}
		 
		
		}	
		
		public static function Support(){
			$Form="";
			$strfranco = new eco_franco();
				$Form.='
				<fieldset>
				<legend>EcomiZ</legend>
				<p>
				'.$strfranco->l('This module has been developped by', 'franco').'<strong><a href="http://www.ecomiz.com">  EcomiZ</a></strong><br />
				'.$strfranco->l('Please report all bugs to', 'franco').'<strong><a  href="mailto:support@ecomiz.com">  support@ecomiz.com</a></strong>
				</p>
				</fieldset>';
				Return $Form;
		}	
	
	
}