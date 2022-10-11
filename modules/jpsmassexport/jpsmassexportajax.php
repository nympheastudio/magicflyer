<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

	global $cookie;

	$list = explode(";",Tools::getValue("json_atrs"));
	$jheaders = explode(";",Tools::getValue("json_headers"));
	$checked = explode(";",Tools::getValue("checked_atrs"));

	
	foreach ($jheaders as $jheader) {
		$fields[] = $jheader;
	}
	
	$out = "";

	if (isset($_POST['enable_header'])) {
	
		$i = 0;
		foreach($fields as $item) {
			if ($checked[$i]=="1") {			
				$out .= utf8_decode($item).";";
			}
			$i++;
		}
		$out = substr($out,0,strlen($out)-1)."\n";
	}


	$por = "por";
	$pca  = "pca";
	$pcu  = "pcu";
	$pad  = "pad";
	$pai = "pai";
	$pco = "pco";
	$prod = "pod";
	$px = _DB_PREFIX_;
	
				 
	$Apca = Array ("name");
	$Apco = Array ("iso_code");
	$Apst = Array ("iso_code");
	$Apcu = Array ("firstname","lastname","email");
	$Apad = Array ("address1","address2","postcode","city","phone","phone_mobile","vat_number","dni","id_state","id_country","company");
	$Apai = Array ("address1","address2","postcode","city","phone","phone_mobile","firstname","lastname","company");
	$Apor = Array ("id_order","payment","recyclable","gift","gift_message","total_discounts","total_paid","shipping_number","total_paid_real","total_shipping",
			 "total_wrapping","invoice_number","delivery_number","invoice_date","delivery_date","date_add","date_upd","id_carrier","valid",
			 "total_products","total_products_wt","id_customer");

	$Aprod = Array ("product_id","product_name","product_quantity","product_price","product_reference","download_hash");
	 
			 
			 
	$table_alias = Array("orders","carrier","customer","address","addressX","country","order_detail");
	$table_prefx = Array($por,$pca,$pcu,$pad,$pai,$pco,$prod);
	$table_attrs = Array($Apor,$Apca,$Apcu,$Apad,$Apai,$Apco,$Aprod);

	$sql = " select ";
	$i=0;
	$limit=35;
			
	// $last_key = end(array_keys($table_attrs));
	foreach($table_attrs as $k => $table_attr) {
		// $last_key2 = end(array_keys($table_attr));
		foreach($table_attr as $l => $attr) {
			$sql.= $table_prefx[$i].".".$attr." ".$table_prefx[$i]."_".$attr;
			$sql.=", ";
		}
		$i++;
		if ($i == $limit)
			break;
	}
	
	

	
	$sql = substr($sql,0,strlen($sql)-2);
	$sql .= " from ";
	$i=0;
	// $last_key = end(array_keys($table_alias));
	foreach ($table_alias as $tablias) {
		// $sql .= $px.$tablias." ".$table_prefx[$i];
		if ($tablias == "orders")
			$sql .= $px.$tablias." ".$table_prefx[$i];
		else if ($tablias == "carrier")
			$sql .= "  join "._DB_PREFIX_."carrier ".$table_prefx[$i]." on ".$table_prefx[$i].".id_carrier = por.id_carrier ";
		else if ($tablias == "customer")
			$sql .= " join "._DB_PREFIX_."customer ".$table_prefx[$i]." on ".$table_prefx[$i].".id_customer = por.id_customer ";
		else if ($tablias == "address") {
			$sql .= " join "._DB_PREFIX_."address pad on pad.id_address = por.id_address_delivery ";
		}
		else if ($tablias == "addressX") {
			$sql .= " join "._DB_PREFIX_."address pai on pai.id_address = por.id_address_invoice  ";
		}
		else if ($tablias == "country")
			$sql .= " LEFT OUTER join "._DB_PREFIX_."country ".$table_prefx[$i]." on ".$table_prefx[$i].".id_country = pad.id_country ";
		else if ($tablias == "order_detail")
			$sql .= " LEFT OUTER join "._DB_PREFIX_."order_detail ".$table_prefx[$i]." on ".$table_prefx[$i].".id_order = por.id_order ";
		else if ($tablias == "message")
			$sql .= " LEFT outer join  (select * from "._DB_PREFIX_."message group by id_order)  psm on psm.id_order = pod.id_order  ";	
			
		
		// $sql.=", ";
		$i++;
		if ($i == $limit)
			break;
	}
	
	

	$sql .= "  WHERE 1=1 ";


				
	if (!empty($_POST['date_start']) && !empty($_POST['date_end']) && $_POST['date_start'] == $_POST['date_end']) {
		
			$sql .= " and date(por.date_add) = str_to_date('".$_POST['date_start']."','%Y-%m-%d') ";
	}
	else {

		if (!empty($_POST['date_start'])) {
			
			$sql .= " and date(por.date_add) >= str_to_date('".$_POST['date_start']."','%Y-%m-%d') ";
		
		}
		
		if (!empty($_POST['date_end'])) {
			
			$sql .= " and date(por.date_add) <= str_to_date('".$_POST['date_end']."','%Y-%m-%d') ";
		
		}
	
	}
	
	if (!empty($_POST['c_groups']) && count($_POST['c_groups'])>0) {
		
		$groups = $_POST['c_groups'];
		$sql .= " and por.id_customer in (
				select id_customer from "._DB_PREFIX_."customer_group
				where ( id_group = ";
		
		$j = 0;
		foreach ($groups as $group) {
			
			$sql.= $group;
			if ($j<count($groups)-1)
				$sql.=" or id_group = ";
			$j++;
		}
		
		$sql .= ")  )";
	}
	
	if (!empty($_POST['c_payment']) && count($_POST['c_payment'])>0) {
		
		$payments = $_POST['c_payment'];
		$sql .= " and ( por.module regexp  ";
		
		$j = 0;
		foreach ($payments as $payment) {
			
			$sql.= "'$payment'";
			if ($j<count($payments)-1)
				$sql.=" or por.module regexp ";
			$j++;
		}
		$sql .= " ) ";
		
	}
	
	if (!empty($_POST['c_states']) && count($_POST['c_states'])>0) {
		
		$states = $_POST['c_states'];
		$sql .= " and por.id_order in (
				select id_order from "._DB_PREFIX_."order_history
				where ( id_order_state = ";
		
		$j = 0;
		foreach ($states as $state) {
			
			$sql.= $state." and date_add = (select max(date_add) from "._DB_PREFIX_."order_history where id_order = por.id_order)";
			if ($j<count($states)-1)
				$sql.=" or id_order_state = ";
			$j++;
		}
		
		$sql .= ")  )  ";
	}
	
	// d($sql);
	if (!empty($_POST['c_carriers']) && count($_POST['c_carriers'])>0) {
		
		$carriers = $_POST['c_carriers'];
		$sql .= " and ( por.id_carrier =  ";
		
		$j = 0;
		foreach ($carriers as $carrier) {
			
			$sql.= "$carrier";
			if ($j<count($carriers)-1)
				$sql.=" or por.id_carrier = ";
			$j++;
		}
		$sql .= " ) ";
	}
	
	// die($sql);

	$data = Db::getInstance()->ExecuteS($sql);

	$last_order = 0;
	$id_product = 0;
	$tx = 0;
	$qte = 1;
	
	foreach($data as &$Rtuple) {
	
		$current_order = $Rtuple['por_id_order'];
		
		// if ($current_order == $last_order) {
			foreach ($Rtuple as $k => &$Ritem) {		
				
				if ($k == "pod_product_id")
					$id_product = $Ritem;

				
				if ($k == "pod_tax_rate")
					$tx = $Ritem;
					
				if ($k == "pod_product_quantity")
					$qte = $Ritem;
					
				if ($k == "pod_download_hash"){
					$Ritem = (Db::getInstance()->getValue("select name from "._DB_PREFIX_."order_state_lang where id_lang=".$cookie->id_lang." and id_order_state = (select id_order_state from "._DB_PREFIX_."order_history WHERE id_order = $current_order ORDER BY date_add desc LIMIT 1)"));
				}
					
				if ($k == "por_gift_message") {	
					$Ritem =   str_replace('<br />',' ',preg_replace("/(\r\n|\n|\r)/", " ", utf8_encode(html_entity_decode($Ritem))));
					$Ritem =   str_replace(';','.',$Ritem);
				}	
				
				if ($k == "pod_product_price") {

					$Ritem = round(round($Ritem * (($tx/100)+1),2) * $qte,2);
					// ((tax_rate/100)+1)*
				}			
		// }
		}		
		$last_order = $current_order;
	}
	


	
	if (count($data)<=0) die("0");
	
	foreach($data as $tuple) {
		$i = 0;
		foreach($list as $item) {
			if ($checked[$i]=="1") {
				$out .= utf8_decode($tuple[$item]).";";
			}
			$i++;
		}
		$out = substr($out,0,strlen($out)-1)."\n";
	}
	
	$date = date("d-m-Y H\hi\ms\s");


	$fichier = @fopen("exports/orders-$date.csv","w+");
	fputs($fichier,$out);
	fclose($fichier);
	
	if (isset($_POST['activ_ftp'])) {
	
		$host = Tools::getValue('ftp_host');
		$port = Tools::getValue('ftp_port');
		$log = Tools::getValue('ftp_login');
		$pass = Tools::getValue('ftp_pass');
		$file = 'exports/orders-'.$date.'.csv';
		$remote_file = 'orders-'.$date.'.csv';

		// Mise en place d'une connexion basique
		$conn_id = ftp_connect($host);

		// Identification avec un nom d'utilisateur et un mot de passe
		$login_result = ftp_login($conn_id, $log, $pass);

		// Charge un fichier
		ftp_put($conn_id, $remote_file, $file, FTP_ASCII); 
	
	}
	
	
	
	 echo '../modules/jpsmassexport/exports/orders-'.$date.'.csv';


?>
