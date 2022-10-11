<?php
// OLD Connexion à la base de données.
//define('_LIMIT_SQL_', '');//' DESC LIMIT 0, 10 ');
//define('_DB_PREFIX_', 'pm_');
//define('_DB_NAME_', 'piscinemateriel_prestashop');
//define('DSN', 'mysql:host=192.168.1.100;dbname='._DB_NAME_);
//define('LOGIN', 'appli');
//define('PASSWORD', 'ap23vb456h');



//new connexion
include '/home/magicflyer/public_html/config/settings.inc.php';


define('_LIMIT_SQL_', '');//' DESC LIMIT 0, 10 ');
define('DSN', 'mysql:host='._DB_SERVER_.';dbname='._DB_NAME_);
define('LOGIN', _DB_USER_);
define('PASSWORD', _DB_PASSWD_);


$options = array(
PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
);

// Essai de la fonction generateCSV("","");
try
{
	
	
	$connexion = new PDO(DSN, LOGIN, PASSWORD, $options);
	
	$start = "";
	$end = "";	
	
	$start = $_GET['start'];
	$end = $_GET['end'];
	
	if($start!= ''&&$end!='')generatecsv($start, $end, $connexion); /* A été modifié **********************************/
}
catch (PDOException $e)
{
	die('Echec : '.$e->getMessage());
}

/***********************
*
* Liste des fonctions. 
*
**************************/

function select_form_piscine(){
	
	global $connexion;
	$h = '';
	
	$liste_commande = '';
	$sql = "SELECT o.* ,c.lastname
	FROM "._DB_PREFIX_."orders AS o 
	LEFT JOIN "._DB_PREFIX_."customer AS c ON o.id_customer = c.id_customer 
	AND invoice_number != 0 
	WHERE o.invoice_date LIKE '2019%'
	ORDER BY invoice_date DESC";

	$query = $connexion->prepare($sql);
	$query->execute();
	
	while ($r = $query->fetch()) { //start one loop
		$dd = explode(' ',$r["invoice_date"])[0];
		if( $dd != '0000-00-00'){
		$h .= '<option value="'.$r["id_order"].'" >'.$dd.' '.$r["lastname"].' '.$r["payment"].'</option>';
		}
		
	}
	
	
	
	$select_start = 'Debut : <br><select name="start">'.$h.'</select>';
	
	
	$select_end = 'Fin : <br><select name="end">'.$h.'</select>';
	
	
	
	return $select_start.'<br /><br />'.$select_end;
}
// generates csv file from $start order to $end order, inclusive

function generatecsv($start, $end, $connexion) /* A été modifié **********************************/
{
	$titres = ""; $csv_output = "";
	
	// Requête SQL
	$sql = "SELECT o.*, c.*, a.*, s.*, co.*, cl.*, 
			o.invoice_date AS date_commande,
			c.firstname AS prenom, c.lastname AS nom, 
			cl.name AS pays 
			
			
			
				FROM "._DB_PREFIX_."orders AS o
				LEFT JOIN "._DB_PREFIX_."customer AS c ON o.id_customer = c.id_customer
				LEFT JOIN "._DB_PREFIX_."address AS a ON o.id_address_delivery = a.id_address
				LEFT JOIN "._DB_PREFIX_."shop AS s ON s.id_shop = c.id_shop
				LEFT JOIN "._DB_PREFIX_."country AS co ON a.id_country = co.id_country
				LEFT JOIN "._DB_PREFIX_."country_lang AS cl ON co.id_country = cl.id_country AND cl.id_lang = 1";
	// Patch dlan
	// if both fields are empty we select all orders
	if ($start == "" && $end == "") {
		$sql .= " ORDER BY id_order "._LIMIT_SQL_;
		
		// if $start is empty we select all orders up to $end
	} else if ($start == "" && $end != "") {
		$sql .= " WHERE id_order <= ".$end." AND NOT ISNULL(id_order) 
				ORDER BY id_order";
		
		// if $end is empty we select all orders from $start
	} else if ($start != "" && $end == "") {
		$sql .= " WHERE id_order >= ".$start." AND NOT ISNULL(id_order)  
				ORDER BY id_order";
		
		// if both fields are filed in we select orders betwenn $start and $end
	} else {
		$sql .= " WHERE id_order >= ".$start." AND id_order <= ".$end." AND NOT ISNULL(id_order)  
				ORDER BY id_order";
	}
	
	if($_GET['debug']) {echo $sql;exit;}
	// Recupération des données.
	$query = $connexion->prepare($sql);
	$query->execute();
	
	//patch
	//$titres = "Date|N° commande|Nom, prénom|Statut commande|Code postal|Montant Total réglé|HT métropole / Corse|TVA métropole/Corse|Port HT métropole/Corse|Port TVA métropole/Corse|HT Dom Tom|Port HT Dom Tom"; 																																
	
	$titres = "DATE;JOURNAL;GENERAL;AUXILIAIRE;SENS;MONTANT;LIBELLE;REFINTERNE";
	
	$csv_output = $titres . "\r\n"; 
	
	while ($row_orders = $query->fetch()) { //start one loop
		
		//on exportes que les commandes avec factures
		if($row_orders["invoice_number"]==0) continue;
		
		// $Orders_id      = $row_orders["id_order"];
		$Orders_id      = 'FA'.$row_orders["invoice_number"];
		$payment_method = str_replace('h&egrave;que', 'hèque', $row_orders["module"]);
		
		
		$_datee             =  explode(' ', $row_orders['date_commande'])[0] ;//
		$_date = date("d/m/Y", strtotime($_datee) );
		
		
		$First_Name        = $row_orders['prenom'];
		$Last_Name		   = ' ' . $row_orders['nom'];
		$pays = $row_orders["pays"];
		
		$Company           = filter_text($row_orders["company"]); // A revoir ***************
		$email             = filter_text($row_orders["email"]);
		$Billing_Address_1 = filter_text($row_orders["address1"]);
		$Billing_Address_2 = filter_text($row_orders["address2"]);
		$Billing_City      = filter_text($row_orders["city"]);
		$Billing_State     = filter_text($row_orders["name"]); // A revoir ***************
		$Billing_Zip       = filter_text($row_orders["postcode"]);
		$Billing_Country   = filter_text($row_orders["pays"]);
		$Billing_Phone     = filter_text($row_orders["phone"]);
		$ShipTo_Name1      = $First_Name.", ".$Last_Name; //$row_orders["delivery_name"];
		$ShipTo_Name       = filter_text($ShipTo_Name1); // order changed
		
		list($ShipTo_First_Name, $ShipTo_Last_Name) = explode(', ', $ShipTo_Name1); // order changed
		
		$ShipTo_Company      = filter_text($row_orders["company"]);
		$ShipTo_Address_1    = filter_text($row_orders["address1"]);
		$ShipTo_Address_2    = filter_text($row_orders["address2"]);;
		$ShipTo_City         = filter_text($row_orders["city"]);
		$ShipTo_State        = filter_text($row_orders["name"]);
		$ShipTo_Zip          = filter_text($row_orders["postcode"]);
		$ShipTo_Country      = filter_text($row_orders["pays"]);
		
		if( iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE',$ShipTo_Country) == 'France mtropolitaine' ){
			
			$ShipTo_Country ='France';
		}
		
		
		
		
		$ShipTo_Phone        = "";
		$Card_Type           = ""; // $row_orders["cc_type"]; **** Non  trouvé.
		$Card_Number         = ""; // $row_orders["cc_number"]; **** Non  trouvé.
		$Exp_Date            = ""; // $row_orders["cc_expires"]; **** Non  trouvé.
		$Bank_Name           = "";
		$Gateway             = "";
		$AVS_Code            = "";
		$Transaction_ID      = "";
		$Order_Special_Notes = "";
		
		/*
		// --------------------    QUERIES 1  ------------------------------------//
		//Orders_status_history for comments
		$orders_status_history = tep_db_query("select comments from orders_status_history
where orders_id = " . $Orders_id);
		//$row_orders_status_history = tep_db_fetch_array($comments);
		while ($row_orders_status_history = mysql_fetch_array($orders_status_history)) {
			// end //
			
			$Comments = filter_text($row_orders_status_history["comments"]);
			
		}
		
		*/
		
		$sql1 = "SELECT * FROM "._DB_PREFIX_."order_state
				where id_order_state = " . $row_orders["current_state"];
		
		$query1 = $connexion->prepare($sql1);
		$query1->execute();
		
		$orders_status      = '';
		$row_orders_status_ = $query1->fetch();
		if ($row_orders_status_['delivery'] == 1)
		{
			$orders_status      = 'delivery';
		}
		elseif ($row_orders_status_['shipped'] == 1)
		{
			$orders_status      = 'shipped';
		}
		elseif ($row_orders_status_['paid'] == 1)
		{
			$orders_status      = 'paid';
		}
		else
		{
			$orders_status      = 'not paid';
		}
		
		
		// --------------------    QUERIES 2  ------------------------------------//
		
		
		//str_replace('.', ',', $row['mon_chiffre'])
		// --------------------    QUERIES 3  ------------------------------------//
		//Orders_tax
		$Order_Tax = 1; //filter_text($row_orders_tax["value"]); // A modifier **********************
		
		/*
		$orders_tax = tep_db_query("select value from orders_total
where class = 'ot_tax' and orders_id = " . $Orders_id);
		//$row_orders_tax = tep_db_fetch_array($orders_tax);
		while ($row_orders_tax = mysql_fetch_array($orders_tax)) {
			// end //
			$Order_Tax = filter_text($row_orders_tax["value"]);
		}
		*/
		// --------------------    QUERIES 5  ------------------------------------//
		//Orders_Shipping
		
		$Order_Shipping_Total = $row_orders["total_shipping"];
		$Shipping_Method      = $row_orders["module"];
		
		
		
		// --------------------    QUERIES 7  ------------------------------------//
		//Orders_Total
		$Order_Grand_Total = $row_orders["total_paid"];
		
		//$ttc_hors_frais_port=number_format(($Order_Grand_Total-$Order_Shipping_Total),2,",","");
		$ttc_hors_frais_port = ($Order_Grand_Total - $Order_Shipping_Total);
		//si y a pas de tax TTC == HT
		if ($Order_Tax != 0)
		$ht_hors_frais_port = ($ttc_hors_frais_port / 1.2);
		else
		$ht_hors_frais_port = $ttc_hors_frais_port;
		
		
		
		
		
		//Montant TTC avec Frais d'expédition|Frais d'expédition|Montant TTC hors frais d'expédition|Montant HT hors frais d'expédition
		$Montant_TTC_avec_Frais_d_expédition = $Order_Grand_Total;
		$Frais_d_expédition                  = $Order_Shipping_Total;
		$Montant_TTC_hors_Frais_d_expédition = ($Montant_TTC_avec_Frais_d_expédition - $Frais_d_expédition);
		
		//tva OU pas
		
		//Le principe: Les clients dont l'adresse de facturation se situe dans un pays étranger ou dans un DOM ne payent pas de TVA
		//L'exception: Les clients dont l'adresse de facturation se situe dans un pays appartenant à la communauté européenne ne payent pas la TVA à condition de possèder un numéro de TVA intracommunautaire.
		
		
		//debug2018  car changement nom de pays
		//if (($row_orders["name"] == 'France') ) { /* France Métropolitaine hors Corse */
		if (($ShipTo_Country == 'France')||($ShipTo_Country == 'France Métropolitaine') || ($ShipTo_Country == 'France Corse') ) { /* France Métropolitaine hors Corse */
			$Montant_HT_hors_Frais_d_expédition = ($Montant_TTC_hors_Frais_d_expédition / 1.2);
			//$Montant_HT_hors_Frais_d_expédition = ($Montant_TTC_hors_Frais_d_expédition );
		} elseif(
				($row_orders["name"] == 'France Corse') /* France Corse à créer dans la BD table : ps_country_lang et ps_country */
				||($row_orders["name"] == 'France Reunion') /* France Reunion à créer dans la BD table : ps_country_lang et ps_country */
				||($row_orders["name"] == 'France, Martinique') /* France, Martinique à créer dans la BD table : ps_country_lang et ps_country */
				
				){
			$Montant_HT_hors_Frais_d_expédition = $Montant_TTC_hors_Frais_d_expédition; // bug juillet 2015 alliance compta
			
		} else {
			$Montant_HT_hors_Frais_d_expédition = $Montant_TTC_hors_Frais_d_expédition;
			
		}
		
		
		
		// --------------------    QUERIES 8  ------------------------------------//
		//Products COunt
		$sql2 = "SELECT COUNT(*) FROM "._DB_PREFIX_."order_detail
				where id_order = " . $row_orders["id_order"];
		
		$query2 = $connexion->prepare($sql2);
		$query2->execute();

		$orders_count = $query2->fetch();
		
		$Number_of_Items = $orders_count; // A vérifier****************
		
		// csv settings 
		$G                      = '  "  ';
		$CSV_SEPARATOR          = ";";
		$CSV_NEWLINE            = "\r\n";
		$Frais_d_expédition_HT = ($Frais_d_expédition / 1.2);
		$TVA_transport          = ($Frais_d_expédition - $Frais_d_expédition_HT);
		//$Montant_HT_hors_Frais_d_expédition= ($Montant_TTC_hors_Frais_d_expédition /1.2);	
		$TVA                    = ($Montant_TTC_hors_Frais_d_expédition - $Montant_HT_hors_Frais_d_expédition);
		$TVA_ET_PORT            = $TVA + ($Frais_d_expédition - $Frais_d_expédition_HT);
		//  $_date                  = '"' . date('d-m-Y', strtotime($row_orders['date_add'])) . '"';
		$_journal               = "VE";
		//CSV SETTINGS ENDOF
		
		
		/***************/
		if (($ShipTo_Country == 'France') || ($ShipTo_Country == 'France Corse')) {
			
			$port_HT_metropole = ($Order_Shipping_Total / 1.2);
			
			
		} else {
			
			$port_HT_domtom = ($Order_Shipping_Total / 1.2);
			
		}
		/*****************/
		
		

		if (($ShipTo_Country == 'France') ||($ShipTo_Country == 'France Métropolitaine') || ($ShipTo_Country == 'France Corse')) {
			$csv_output .= $CSV_NEWLINE;
			$csv_output .= $_date . $CSV_SEPARATOR;
			$csv_output .= $_journal . $CSV_SEPARATOR;
			// $csv_output .= "411000" . $CSV_SEPARATOR;
			$csv_output .= "911201" . $CSV_SEPARATOR;
			$csv_output .= "Client internet" . $CSV_SEPARATOR;
			$csv_output .= "D" . $CSV_SEPARATOR;
			$csv_output .= Reduire_Decimale($Montant_TTC_avec_Frais_d_expédition, 2) . "" . $CSV_SEPARATOR;
			$csv_output .= $First_Name . $Last_Name .  $CSV_SEPARATOR;
			$csv_output .= $Orders_id . $CSV_SEPARATOR;
			
			$csv_output .= $CSV_NEWLINE;
			
			$csv_output .= $_date . $CSV_SEPARATOR;
			$csv_output .= $_journal . $CSV_SEPARATOR;
			// $csv_output .= "707150" . $CSV_SEPARATOR;
			$csv_output .= "701000" . $CSV_SEPARATOR;
			$csv_output .= "Vente de produits finis" . $CSV_SEPARATOR;
			$csv_output .= "C" . $CSV_SEPARATOR;			
			$csv_output .= Reduire_Decimale( $Montant_HT_hors_Frais_d_expédition, 2 ) . "" . $CSV_SEPARATOR;
			$csv_output .= $First_Name . $Last_Name . $CSV_SEPARATOR;
			$csv_output .= $Orders_id . $CSV_SEPARATOR;
			
			$csv_output .= $CSV_NEWLINE;			
			
			
			//$csv_output .= $_date . $CSV_SEPARATOR;
			//$csv_output .= $_journal . $CSV_SEPARATOR;
			//// $csv_output .= "707150" . $CSV_SEPARATOR;
			//$csv_output .= "701920" . $CSV_SEPARATOR;
			//$csv_output .= "   " . $CSV_SEPARATOR;
			//$csv_output .= "C" . $CSV_SEPARATOR;			
			//$csv_output .= Reduire_Decimale( $Montant_HT_hors_Frais_d_expédition, 2 ) . "" . $CSV_SEPARATOR;
			//$csv_output .= $First_Name . $Last_Name . $CSV_SEPARATOR;
			//$csv_output .= $Orders_id . $CSV_SEPARATOR;
			//
			//$csv_output .= $CSV_NEWLINE;
			
			$csv_output .= $_date . $CSV_SEPARATOR;
			$csv_output .= $_journal . $CSV_SEPARATOR;
			$csv_output .= "708500" . $CSV_SEPARATOR;
			$csv_output .= "Ports et frais facturés" . $CSV_SEPARATOR;
			$csv_output .= "C" . $CSV_SEPARATOR;
			$csv_output .= Reduire_Decimale($Frais_d_expédition, 2) . "" . $CSV_SEPARATOR;
			$csv_output .= $First_Name . $Last_Name . $CSV_SEPARATOR;
			$csv_output .= $Orders_id . $CSV_SEPARATOR;
			
			//$csv_output .= $CSV_NEWLINE;
			//
			//$csv_output .= $_date . $CSV_SEPARATOR;
			//$csv_output .= $_journal . $CSV_SEPARATOR;
			//// $csv_output .= "708500" . $CSV_SEPARATOR;
			//$csv_output .= "708590" . $CSV_SEPARATOR;
			//$csv_output .= "   " . $CSV_SEPARATOR;
			//$csv_output .= "C" . $CSV_SEPARATOR;
			//$csv_output .= Reduire_Decimale($Frais_d_expédition_HT, 2) . "" . $CSV_SEPARATOR;
			//$csv_output .= $First_Name . $Last_Name . $CSV_SEPARATOR;
			//$csv_output .= $Orders_id . $CSV_SEPARATOR;
			
			// $csv_output .= $CSV_NEWLINE;
			
			// $csv_output .= $_date . $CSV_SEPARATOR;
			// $csv_output .= $_journal . $CSV_SEPARATOR;
			// $csv_output .= "708590" . $CSV_SEPARATOR;
			// $csv_output .= "   " . $CSV_SEPARATOR;
			// $csv_output .= "C" . $CSV_SEPARATOR;
			// $csv_output .= "0" . $CSV_SEPARATOR;
			// $csv_output .= $First_Name . $Last_Name . $CSV_SEPARATOR;
			// $csv_output .= $Orders_id . $CSV_SEPARATOR;
			
			$csv_output .= $CSV_NEWLINE;
			
			$csv_output .= $_date . $CSV_SEPARATOR;
			$csv_output .= $_journal . $CSV_SEPARATOR;
			$csv_output .= "445718" . $CSV_SEPARATOR;
			$csv_output .= "TVA collectée à 20%" . $CSV_SEPARATOR;
			$csv_output .= "C" . $CSV_SEPARATOR;
			$csv_output .= Reduire_Decimale($TVA, 2) . "" . $CSV_SEPARATOR;
			$csv_output .= $First_Name . $Last_Name . $CSV_SEPARATOR;
			$csv_output .= $Orders_id . $CSV_SEPARATOR;
			
			$csv_output .= $CSV_NEWLINE;
			
			
		} else {
			
			$csv_output .= $CSV_NEWLINE;
			$csv_output .= $_date . $CSV_SEPARATOR;
			$csv_output .= $_journal . $CSV_SEPARATOR;
			// $csv_output .= "411000" . $CSV_SEPARATOR;
			$csv_output .= "911201" . $CSV_SEPARATOR;
			$csv_output .= "Client internet".$ShipTo_Country . $CSV_SEPARATOR;
			$csv_output .= "D" . $CSV_SEPARATOR;
			$csv_output .= Reduire_Decimale($Montant_TTC_avec_Frais_d_expédition, 2) . "" . $CSV_SEPARATOR;
			$csv_output .= $First_Name . $Last_Name .  $CSV_SEPARATOR;
			$csv_output .= $Orders_id . $CSV_SEPARATOR;
			
			$csv_output .= $CSV_NEWLINE;
			
			$csv_output .= $_date . $CSV_SEPARATOR;
			$csv_output .= $_journal . $CSV_SEPARATOR;
			// $csv_output .= "707150" . $CSV_SEPARATOR;
			$csv_output .= "701900" . $CSV_SEPARATOR;
			$csv_output .= "Vente de produits finis CEE" . $CSV_SEPARATOR;
			$csv_output .= "C" . $CSV_SEPARATOR;			
			$csv_output .= Reduire_Decimale( $Montant_HT_hors_Frais_d_expédition, 2 ) . "" . $CSV_SEPARATOR;
			$csv_output .= $First_Name . $Last_Name . $CSV_SEPARATOR;
			$csv_output .= $Orders_id . $CSV_SEPARATOR;
			
			$csv_output .= $CSV_NEWLINE;			
			
			
			//$csv_output .= $_date . $CSV_SEPARATOR;
			//$csv_output .= $_journal . $CSV_SEPARATOR;
			//// $csv_output .= "707150" . $CSV_SEPARATOR;
			//$csv_output .= "701920" . $CSV_SEPARATOR;
			//$csv_output .= "   " . $CSV_SEPARATOR;
			//$csv_output .= "C" . $CSV_SEPARATOR;			
			//$csv_output .= Reduire_Decimale( $Montant_HT_hors_Frais_d_expédition, 2 ) . "" . $CSV_SEPARATOR;
			//$csv_output .= $First_Name . $Last_Name . $CSV_SEPARATOR;
			//$csv_output .= $Orders_id . $CSV_SEPARATOR;
			//
			//$csv_output .= $CSV_NEWLINE;
			
			$csv_output .= $_date . $CSV_SEPARATOR;
			$csv_output .= $_journal . $CSV_SEPARATOR;
			$csv_output .= "708520" . $CSV_SEPARATOR;
			$csv_output .= " Frais Ports/Ventes CEE" . $CSV_SEPARATOR;
			$csv_output .= "C" . $CSV_SEPARATOR;
			$csv_output .= Reduire_Decimale($Frais_d_expédition, 2) . "" . $CSV_SEPARATOR;
			$csv_output .= $First_Name . $Last_Name . $CSV_SEPARATOR;
			$csv_output .= $Orders_id . $CSV_SEPARATOR;
			
			$csv_output .= $CSV_NEWLINE;
			//
			//$csv_output .= $_date . $CSV_SEPARATOR;
			//$csv_output .= $_journal . $CSV_SEPARATOR;
			//// $csv_output .= "708500" . $CSV_SEPARATOR;
			//$csv_output .= "708590" . $CSV_SEPARATOR;
			//$csv_output .= "   " . $CSV_SEPARATOR;
			//$csv_output .= "C" . $CSV_SEPARATOR;
			//$csv_output .= Reduire_Decimale($Frais_d_expédition_HT, 2) . "" . $CSV_SEPARATOR;
			//$csv_output .= $First_Name . $Last_Name . $CSV_SEPARATOR;
			//$csv_output .= $Orders_id . $CSV_SEPARATOR;			
			

			
			
			
			
		}
		
		
		
		
		
	} // while loop main first
	$Montant_TTC_avec_Frais_d_expédition = '';
	$Frais_d_expédition                  = '';
	$Montant_TTC_hors_Frais_d_expédition = '';
	$Montant_HT_hors_Frais_d_expédition  = '';
	//print
	//echo number_format($Order_Subtotal,2,",","");
	
	if($_GET['debug']){
		echo $csv_output;
	}else{
		header("Content-type: application/vnd.ms-excel");
		header("Content-Type: application/force-download\n");
		header("Cache-Control: cache, must-revalidate");
		header("Pragma: public");
		header("Content-Disposition: attachment; filename=Suivi_des_commandes_" . date("dmY") . ".csv");
		print str_replace(".", ",", $csv_output);
	}
	exit;
	
	
	//echo $csv_output;
} //function main

function filter_text($text)
{
	$filter_array = array(
	"|",
	"\r",
	"\n",
	"\t"
	);
	return str_replace($filter_array, "", $text);
} // function for the filter

function Reduire_Decimale($nombre, $chiffre_apres_LaVirgule)
{
	return number_format($nombre, $chiffre_apres_LaVirgule, ',', ' ');
}


?>
<form name='compta_pisc' method='GET'>
<!--ID Début
<input type='text' name='start' value='' />
<br />
ID Fin
<input type='text' name='end' value='' />
-->


<?php echo select_form_piscine(); ?>
<br /><br /><input type='submit' value='exporter les factures' />
</form>