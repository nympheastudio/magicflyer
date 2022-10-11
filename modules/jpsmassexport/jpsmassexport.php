<?php
if (!defined('_CAN_LOAD_FILES_'))
	exit;
	
function getFloat($version) { return (float) (substr($version,0,strpos($version,".")+strpos(substr($version,strpos($version,".")+1,strlen($version)),".")+1)); }	
function isP15() { if (getFloat(Configuration::get('PS_INSTALL_VERSION'))>=1.5) { return true; } else { return false; } }	
	
class jpsmassexport extends Module
{
	function __construct()
	{
		$this->name = 'jpsmassexport';
		$this->tab = 'front_office_features';
		$this->version = 2;
		$this->author = 'JPS';
		$this->need_instance = 0;
		$this->module_key = "3be2ccdd4bf4c5d82b17df101e557ef3";
		
		parent::__construct();
		
		$this->displayName = $this->l('JPS Mass Export');
		$this->description = $this->l('Manage Datas by exporting them in CSV file.');
	}
	
	public function installDB()
	{	
		$query = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jps_massexport_profiles` (
		  `id_profile` int(11) NOT NULL AUTO_INCREMENT,
		  `title` text,
		  `date` datetime,
		  `content` text,
		  PRIMARY KEY (`id_profile`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;
		';
		Db::getInstance()->Execute($query);
		return true;
	}
	
	public function uninstallDB()
	{
		Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'jps_massexport_profiles` ');
		return true;
	}
	
	public function install()
	{
	
		$dsd = Db::getInstance()->ExecuteS("SELECT * FROM "._DB_PREFIX_."carrier where name='[no carrier]'");
	
		if (empty($dsd) || !is_array($dsd) || !array_key_exists(0,$dsd)) {
			Db::getInstance()->Execute("INSERT into "._DB_PREFIX_."carrier(name) values('[no carrier]')");
			Db::getInstance()->Execute("UPDATE "._DB_PREFIX_."carrier set id_carrier = 0 WHERE `name` = '[no carrier]' ");
		}
		
	  if(!parent::install()
		|| !$this->installDB() 	
		// || !Configuration::updateValue('', '') 
		) 
		return false;
	  return true;
	}
	
	function uninstall()
	{
		Db::getInstance()->Execute("DELETE FROM "._DB_PREFIX_."carrier where id_carrier = 0 AND `name` = '[no carrier]' ");
	
		if (!parent::uninstall() 	
			|| !$this->uninstallDB() 	
			// || !Configuration::deleteByName('')	
			)
			return false;
		return true;
	}
	
	
	public function getFields() {
	
		return $fields = Array($this->l('Order no'),$this->l('Order State'),$this->l('Product ID'),$this->l('Product Ref'),$this->l('Product Name'),$this->l('Product Price'),$this->l('Product Quantity'),$this->l('Payment'),$this->l('Recycled packaging'),$this->l('Gift wrapping'),$this->l('Gift message'),$this->l('Total discounts'),$this->l('Total paid'),
			$this->l('Shipping number'),$this->l('Total paid real'),$this->l('Total products'),$this->l('Total shipping'),$this->l('Total wrapping'),$this->l('Invoice no'),$this->l('Delivery no'),$this->l('Invoice date'),
			$this->l('Delivery date'),$this->l('Date added'),$this->l('Date updated'),$this->l('Carrier no'),$this->l('Validity'),$this->l('Total products with tax'),$this->l('Customer no'),$this->l('Name carrier'),
			$this->l('Firstname'),$this->l('Lastname'),$this->l('Email'),$this->l('Delivery address line 1'),$this->l('Delivery address line 2'),$this->l('Delivery postcode'),$this->l('Delivery city'),
			$this->l('Delivery phone'),$this->l('Delivery phone(mobile)'),$this->l('Delivery VAT'),$this->l('Delivery DNI'),$this->l('Delivery state'),$this->l('Delivery country'),
			$this->l('Invoice address line 1'),$this->l('Invoice address line 2'),$this->l('Invoice postcode'),$this->l('Invoice city'),$this->l('Invoice phone'),
			$this->l('Invoice phone (mobile)'),$this->l('Invoice firstname'),$this->l('Invoice lastname'),$this->l('Invoice company name'),$this->l('Delivery country iso code'));
	}
	
	public static function restore(){
		if (isset($_POST['save_activ']))
			return true;
		return false;
	}
	
	
	public static function restorePut($dataTrue = "", $dataFalse = ""){
		if (self::restore()) {
				return $_POST[$dataTrue];
		}	
		return $dataFalse;
	}
	

	public function getContent()
	{
		global $cookie;
	
	  
	 
	
	  $html = '<script type="text/javascript" src="'.$this->_path.'/jquery/js/jquery-ui-1.8.22.custom.min.js"></script>';
	  echo '<link href="'.$this->_path.'/jquery/css/ui-lightness/jquery-ui-1.8.22.custom.css" rel="stylesheet" type="text/css" media="all" />';
			

	 	  $html.='<script type="text/javascript">
		  
			function checked(id) {$("#"+id).click();}
			
			function checkall() {
				$(".sort_atrbs").attr("checked","true");
			}
			
			function uncheckall() {
				$(".sort_atrbs").removeAttr("checked");
			}
			
			function deleteProfile(id){
				$.ajax({
						url: "../modules/jpsmassexport/jpsmassexportsaveprofile.php?cmd=del&id="+id,
						type: "POST",
						data: $("#profileform").serialize(),
						success: function(data) { 			
							$("#profile_"+id).remove();
						}
				});
			}
			
			function setProfile(id){
				$.ajax({
						url: "../modules/jpsmassexport/jpsmassexportsaveprofile.php?cmd=set&id="+id,
						type: "POST",
						data: $("#profileform").serialize(),
						success: function(data) { 		

							var infos = jQuery.parseJSON(data);
							
							jQuery.each(infos, function(key, value) { 
								$("#"+key).val(value);
							});
							$("#activ_profile").val(id);

							$("#profileform").submit();
						}
				});
			}
			
			jQuery(function($) {
			  $(document).ready(function(){		
					
					$("#saveprofile").click(function(){
						
						// saveprofilef();
						
					});
					
					
					
					
					';
					if (self::restore()) {		
					
							$payments_s = explode(',',$_POST['c_payment_save']);
							$c_payment_save = "[";
							foreach($payments_s as $prs) $c_payment_save .= "'$prs',";
							$c_payment_save = substr($c_payment_save,0,strlen($c_payment_save)-1)."]";

							$html.= '				
								$("#c_groups").val(['.$_POST['c_groups_save'].']);
								$("#c_payment").val('.$c_payment_save.');	
								$("#c_states").val(['.$_POST['c_states_save'].']);
								$("#c_carriers").val(['.$_POST['c_carriers_save'].']);		
							';					
					}
			  
					$html.= 'jQuery("#submitJMS").click(function(){ 
						// submitJMSf();
					});
					
					$("#date_start").datepicker({ "dateFormat":\'yy-mm-dd\' });
					$("#date_end").datepicker({ "dateFormat":\'yy-mm-dd\' });
			  });
		  });
		  
		  function submitJMSf(){
				var out = "";
				var outheader = "";
				var checked = "";
				$(".sort_atrbs").each(function(){
					out += ($(this).val()+";");
					if ($(this).attr("checked")==true || $(this).attr("checked")=="checked")
						checked += "1;";
					else
						checked += "0;";
				});	
				
				$(".sort_header").each(function(){
					outheader += ($(this).html()+";");
				});	
			
				
				$("#json_atrs").val(out.substring(0,out.length-1));
				$("#checked_atrs").val(checked.substring(0,checked.length-1));
				$("#json_headers").val(outheader.substring(0,outheader.length-1));

				$("#out").html("<br /><strong>Please wait...</strong>");

				
				$.ajax({
						url: "../modules/jpsmassexport/jpsmassexportajax.php",
						type: "POST",
						data: $("#info_export").serialize(),
						success: function(data) { 
							
							$("#out").html("<br /><strong>Generated file in (if doesn\'t appear click on follow link) :</strong><br /> <a href=\'http://'.$_SERVER['HTTP_HOST'].''._PS_IMG_.'"+data+"\'>"+data+"</a>");
							if (data[0]=="0") 
								alert("'.$this->l('No orders...').'"); 
							else if (data[0]==".") 
								document.location.href=data; 
							}
					});
		  }
		  
		  function saveprofilef(){
				var out = "";
				var outheader = "";
				var checked = "";
				$(".sort_atrbs").each(function(){
					out += ($(this).val()+";");
					if ($(this).attr("checked")==true || $(this).attr("checked")=="checked")
						checked += "1;";
					else
						checked += "0;";
				});	
				
				$(".sort_header").each(function(){
					outheader += ($(this).html()+";");
				});	
			
				$("#json_atrs_save").val(out.substring(0,out.length-1));
				$("#checked_atrs_save").val(checked.substring(0,checked.length-1));
				$("#json_headers_save").val(outheader.substring(0,outheader.length-1));
			
				$("#enable_header_save").val($("#enable_header:checked").length);
				$("#date_start_save").val($("#date_start").val());
				$("#date_end_save").val($("#date_end").val());
				
				$("#c_groups_save").val($("#c_groups").val());
				$("#c_payment_save").val($("#c_payment").val());	
				$("#c_states_save").val($("#c_states").val());
				$("#c_carriers_save").val($("#c_carriers").val());
			
				$("#activ_ftp_save").val($("#activ_ftp:checked").length);
				$("#ftp_host_save").val($("#ftp_host").val());
				$("#ftp_port_save").val($("#ftp_port").val());
				$("#ftp_login_save").val($("#ftp_login").val());
				$("#ftp_pass_save").val($("#ftp_pass").val());
			
				
				
				$.ajax({
						url: "../modules/jpsmassexport/jpsmassexportsaveprofile.php?cmd=add",
						type: "POST",
						data: $("#profileform").serialize(),
						success: function(data) { 		
							$("#profilebody").append(data);
						}
				});
				// $("#profileform").submit();
		  }
		  
		</script>';
	 
	  $html .= '<h2>'.$this->l('JPS Mass Export Module').'</h2>
	  
	  
	  <div>
		<fieldset>
		  <legend><img src="'._MODULE_DIR_.$this->name.'/logo.gif" />'.$this->l('Export Orders from your database').'</legend>
		  
		  <div class="clear">&nbsp;</div>';
		
	
			$fields = $this->getFields();
			
			$fields_atrs = Array ("por_id_order","pod_download_hash","pod_product_id","pod_product_reference","pod_product_name","pod_product_price","pod_product_quantity","por_payment","por_recyclable","por_gift","por_gift_message","por_total_discounts","por_total_paid",
			"por_shipping_number","por_total_paid_real","por_total_products","por_total_shipping","por_total_wrapping","por_invoice_number","por_delivery_number",
			"por_invoice_date","por_delivery_date","por_date_add","por_date_upd","por_id_carrier","por_valid","por_total_products_wt","por_id_customer",
			"pca_name","pcu_firstname","pcu_lastname","pcu_email","pad_address1","pad_address2","pad_postcode","pad_city","pad_phone",
			"pad_phone_mobile","pad_vat_number","pad_dni","pad_id_state","pad_id_country","pai_address1","pai_address2","pai_postcode","pai_city",
			"pai_phone","pai_phone_mobile","pai_firstname","pai_lastname","pai_company","pco_iso_code");
				
		
	
			$html .='
			<style>
				#profiles_table{
					padding:5px;
					margin:0 auto;
				}
				#profiles_table td{
					padding:5px;
					margin:0px;
					font-size:11px;
				}
				#profiles_table tr{
					padding:5px;
					margin:0px;
					font-size:11px;
				}
				#profiles_table th{
					padding:5px;
					margin:0px;
					font-size:12px;
					background:#CCC;
				}
				
				#profiles_table .trone {
					background:white;
				}
				
				#profiles_table .trtwo {
					background:#FFD82E;
				}
				#profiles_table .trone:hover, #profiles_table .trtwo:hover {
					background:#A5D9FF;
				}
				.sort_attrs ul { list-style-type: none; margin: 0; padding: 0; margin-bottom: 10px; }
				.sort_attrs li { margin: 5px; padding: 5px; width: 270px; color:#333; padding-left:10px; cursor:move; background:#CCC;}
				.sort_attrs li:hover { background:#BBB; }
				.sort_attrs a {  color:#333; }
				.sort_attrs a:hover {  font-weight:bold;  }
				.sort_attrs li input { float:right;margin-right:10px; }
				.ibx { display: -moz-inline-stack;display: inline-block;vertical-align: top;zoom: 1;*display: inline; }
				#out { display:block; }
			</style>
			<script>
				$(function() {
					$( "#listattrs" ).sortable({
						revert: true
					});
					$( "ul, li" ).disableSelection();
				});
			</script>

				<div class="sort_attrs ibx" >
					
					<input type="button" class="button" value="'.$this->l('Select all').'" onclick="checkall()" />
					<input type="button" class="button" value="'.$this->l('Deselect all').'" onclick="uncheckall()" style="float:right;"/>
					<br /><br />
					<ul id="listattrs">';
					$i = 0;
					
					if (self::restore()) {
					
						$attrbs_s = explode(";",Tools::getValue("json_atrs_save"));
						$attrbslib_s = explode(";",Tools::getValue("json_headers_save"));
						$checked_s = explode(";",Tools::getValue("checked_atrs_save"));
				
						
						foreach ($attrbslib_s as $field) {
							$html.='
							<div style="background:#555;width:297px;">
								<li class="ui-state-default">	
									<a href="javascript:void(0);" class="sort_header" style="color:#555;" onclick="checked(\''.$attrbs_s[$i].'\');">'.($field).'</a>			
									<input type="checkbox" class="sort_atrbs" name="'.$attrbs_s[$i].'" value="'.$attrbs_s[$i].'" id="'.$attrbs_s[$i].'" '.($checked_s[$i]=="1" ? 'checked="checked"' : "").'/>
								</li>
							</div>'."\n";
							$i++;
						}	
					}
					else {						
						foreach ($fields as $field) {
							$html.='
							<div style="background:#555;width:297px;">
								<li class="ui-state-default">	
									<a href="javascript:void(0);" class="sort_header" style="color:#555;" onclick="checked(\''.$fields_atrs[$i].'\');">'.$field.'</a>			
									<input type="checkbox" class="sort_atrbs" name="'.$fields_atrs[$i].'" value="'.$fields_atrs[$i].'" id="'.$fields_atrs[$i].'" checked="checked"/>
								</li>
							</div>'."\n";
							$i++;

						}	
					}
					
					
					$html.='				
					</ul>

		  
				</div>';
				
				$customer_groups = Db::getInstance()->ExecuteS("SELECT * from "._DB_PREFIX_."group_lang where id_lang=".$cookie->id_lang);
				$payement_types = $this->getInstalledPaymentModules();
				
				$csql = '';
				
				if ($this->columnExists('deleted',_DB_PREFIX_.'order_state'))
					$csql = "SELECT * from "._DB_PREFIX_."order_state o,"._DB_PREFIX_."order_state_lang ol  where id_lang=".$cookie->id_lang." and o.id_order_state = ol.id_order_state and o.deleted = 0 order by name";
				else if ($this->columnExists('hidden',_DB_PREFIX_.'order_state'))
					$csql = "SELECT * from "._DB_PREFIX_."order_state o,"._DB_PREFIX_."order_state_lang ol  where id_lang=".$cookie->id_lang." and o.id_order_state = ol.id_order_state and o.hidden = 0 order by name";

				$order_states = Db::getInstance()->ExecuteS($csql);
				
				$carriers = Db::getInstance()->ExecuteS("SELECT * from "._DB_PREFIX_."carrier where deleted = 0");
				
				// d($payement_types);
				
				$html.='
				<div style="margin-left:50px;width:auto;" class="ibx">
					<form id="info_export" method="POST">
						<fieldset><legend>'.$this->l('Settings').'</legend>
							<input type="hidden" name="json_atrs" id="json_atrs" value="" />
							<input type="hidden" name="checked_atrs" id="checked_atrs" value="" />
							<input type="hidden" name="json_headers" id="json_headers" value="" />
							
							<label for="enable_header">'.$this->l('Enable header').'</label>
							<div class="margin-form" style="margin-top:4px;">
								<input type="checkbox" name="enable_header" id="enable_header" '.(isset($_POST['enable_header_save']) && $_POST['enable_header_save']=="1" ? 'checked="checked"':'').'/> '.$this->l('display attributes names').'
							</div>
							
							 <label>'.$this->l('Start date').'</label>
							  <div class="margin-form">
								<input type="text" size="9" id="date_start" name="date_start" value="'.self::restorePut('date_start_save').'" /> '.$this->l('(yyyy-mm-dd)').'
							  </div>
							  
							  <label>'.$this->l('End date').'</label>
							  <div class="margin-form">
								<input type="text" size="9" id="date_end" name="date_end" value="'.self::restorePut('date_end_save').'" /> '.$this->l('(yyyy-mm-dd)').'
							  </div>
							  
							  <label>'.$this->l('Customer group').'</label>
							  <div class="margin-form">
								<select id="c_groups" name="c_groups[]" multiple>';

									foreach($customer_groups as $group){
										$html .='
										<option value="'.$group['id_group'].'">
											'.$group['name'].'
										</option>';
									}	
								$html .='
								</select>
							  </div>
							  
							  <label>'.$this->l('Payment mode').'</label>
							  <div class="margin-form">
								<select id="c_payment" name="c_payment[]" multiple>';
									
									foreach($payement_types as $payment){
										$html .='
										<option value="'.$payment['name'].'">
											'.ucfirst($payment['name']).'
										</option>';
									}	
								$html .='
								</select>
							  </div>
							  
							   <label>'.$this->l('Order state').'</label>
							  <div class="margin-form">
								<select id="c_states" name="c_states[]" size="7" multiple>';
									$i = 0;
									foreach($order_states as $state){
										$html .='
										<option value="'.$state['id_order_state'].'" style="padding:4px;border-bottom:1px solid black; color:black;  background:'.$state['color'].'">
											'.$state['name'].'
										</option>';
										$i++;
									}	
								$html .='
								</select>
							  </div>
							  
							   <label>'.$this->l('Carriers').'</label>
							  <div class="margin-form">
								<select id="c_carriers" name="c_carriers[]" multiple>';
									
									foreach($carriers as $carrier){
										$html .='
										<option value="'.$carrier['id_carrier'].'">
											'.$carrier['name'].'
										</option>';
									}	
							$html .='
								</select>
							  </div>';
							  
							  $host = Configuration::get('JMS_HOST');
						$port = Configuration::get('JMS_PORT');
						$log = Configuration::get('JMS_LOG');
						$pass = Configuration::get('JMS_PWD');	
											
							
						$html .='	
						</fieldset>
						<br />
						<fieldset><legend>'.$this->l('FTP settings').'</legend>
							
							<label for="activ_ftp">'.$this->l('Send to FTP').'</label>
							<div class="margin-form" style="margin-top:4px;">
								<input type="checkbox" name="activ_ftp" id="activ_ftp" '.(isset($_POST['activ_ftp_save']) && $_POST['activ_ftp_save']=="1" ? 'checked="checked"':'').' />
							</div>
							
							 <label>'.$this->l('Host').'</label>
							  <div class="margin-form">
								<input type="text" size="12" id="ftp_host" name="ftp_host" value="'.self::restorePut('ftp_host_save',$host).'" />
							  </div>
							  
							  <label>'.$this->l('Port').'</label>
							  <div class="margin-form">
								<input type="text" size="4" id="ftp_port" name="ftp_port" value="'.self::restorePut('ftp_port_save',21).'" /> '.$this->l('(ex 21)').'
							  </div>
							  
							  <label>'.$this->l('Login').'</label>
							  <div class="margin-form">
								<input type="text" size="9" id="ftp_login" name="ftp_login" value="'.self::restorePut('ftp_login_save',$log).'" /> 
							  </div>
							  
							  <label>'.$this->l('Password').'</label>
							  <div class="margin-form">
								<input type="password" size="9" id="ftp_pass" name="ftp_pass" value="'.self::restorePut('ftp_pass_save',$pass).'" /> 
							  </div>
							  
						</fieldset>
						
						<br />
						
						
						
					</form>
					<form method="post" id="profileform">		
						<fieldset><legend>'.$this->l('Export profile settings').'</legend>
						
							<input type="hidden" name="save_activ" value="1" />
							<input type="hidden" name="activ_profile" id="activ_profile" value="0" />
							
							<input type="hidden" name="json_atrs_save" id="json_atrs_save" value="" />
							<input type="hidden" name="checked_atrs_save" id="checked_atrs_save" value="" />
							<input type="hidden" name="json_headers_save" id="json_headers_save" value="" />
							
							<input type="hidden" name="enable_header_save" id="enable_header_save" value="" />
							<input type="hidden" name="date_start_save" id="date_start_save" value="" />
							<input type="hidden" name="date_end_save" id="date_end_save" value="" />

							<input type="hidden" name="c_groups_save" id="c_groups_save" value="" />
							<input type="hidden" name="c_payment_save" id="c_payment_save" value="" />
							<input type="hidden" name="c_states_save" id="c_states_save" value="" />
							<input type="hidden" name="c_carriers_save" id="c_carriers_save" value="" />

							<input type="hidden" name="activ_ftp_save" id="activ_ftp_save" value="" />
							<input type="hidden" name="ftp_host_save" id="ftp_host_save" value="" />
							<input type="hidden" name="ftp_port_save" id="ftp_port_save" value="" />
							<input type="hidden" name="ftp_login_save" id="ftp_login_save" value="" />
							<input type="hidden" name="ftp_pass_save" id="ftp_pass_save" value="" />

							<input type="hidden" name="path" id="path" value="'.$this->_path.'" />
							
							
							
							<label>'.$this->l('Profile name').'</label><input type="text" name="nameprofile" id="nameprofile" />
							<input id="saveprofile" type="button" class="button" value="Add" onclick="saveprofilef();" />		  
							
							<table id="profiles_table">
								<thead>
									<th style="min-width:20px;">'.$this->l('ID').'</th>
									<th style="min-width:150px;">'.$this->l('Profile name').'</th>
									<th style="min-width:150px;">'.$this->l('Date add').'</th>
									<th style="min-width:110;">'.$this->l('Actions').'</th>
								</thead>
								<tbody id="profilebody">';
								$profiles = Db::getInstance()->ExecuteS("SELECT * from "._DB_PREFIX_."jps_massexport_profiles order by date");
								foreach($profiles as $profile) {
									$class = (isset($_POST['activ_profile']) && $_POST['activ_profile'] == $profile['id_profile']) ? 'class="trtwo"' : 'class="trone"';
									$html .='
									<tr '.$class.' id="profile_'.$profile['id_profile'].'">
										<td>'.$profile['id_profile'].'</td>
										<td>'.$profile['title'].'</td>
										<td>'.$profile['date'].'</td>
										<td style="text-align:center;">
											<a href="javascript:setProfile('.$profile['id_profile'].')" >
												<img src="'.$this->_path.'enabled.gif" title="Apply" />
											</a>
										
											<a href="javascript:deleteProfile('.$profile['id_profile'].')" >
												<img src="'.$this->_path.'delete.gif" title="Delete" />
											</a>
										</td>
									</tr>
									';
								}
								
									
								$html .='
								</tbody>							
							</table>
	
						</fieldset>				
						<br />
					</form>
										
					  <div class="clear center">	
						<div id="out"></div>	
						<p>&nbsp;</p>		
						<input class="button" id="submitJMS" type="button" onclick="submitJMSf()" name="submitJMS" value="'.$this->l('   Generate CSV   ').'" />
						
						
					  </div>
					  
					  
				</div>';
				
		 
		  $html.= '	  
		</fieldset>
		</div>
		'.$this->aboutUs();

	  
	  return $html;
	}
		
	public function aboutUs() {
		$out = '
		<br /><br />
		<fieldset>
			<legend><img src="'._MODULE_DIR_.$this->name.'/jps_seal.png" />'.$this->l('About us').'</legend>
			<div>
				'.$this->l('This module was developed by JPS, if you have any question feel free to contact us at the following address: jpsmodules@gmail.com').'
			</div>';
			
			$site = "http://www.jps-prestashop.com/ads.php";
			$file = @fopen($site, 'r'); 
			if ($file) 
			{
				$out .= '
				<iframe src="'.$site.'" style="width:98%;height:200px;border:1px dotted gray;margin-top:5px;></iframe> 
				';
			} 

		$out .= '
		</fieldset>
	  </form>';
	  
	  return $out;
	}
	
	public  function getInstalledPaymentModules()
	{
	
		$sql = '
		SELECT DISTINCT m.`id_module`, h.`id_hook`, m.`name`, hm.`position`
		FROM `'._DB_PREFIX_.'module` m
		LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
		LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
		WHERE (h.`name` = \'payment\' or lower(h.`title`) regexp \'payment\')
		AND m.`active` = 1 group by m.name
		';
		
	
		$dba = Db::getInstance()->executeS($sql);
		
		
		return $dba;
	}
	
	public function columnExists($col,$table){
		$cols = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM '.$table);
		$ncols = array();
		foreach($cols as $c) {
			$ncols[] = $c['Field'];
		}
		return in_array($col,$ncols);
	}
	
}