<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

	global $cookie;

	if ($_GET['cmd']=="add") {
	
		if (empty($_POST['nameprofile']))
			$_POST['nameprofile'] = "Empty name";
	
		foreach($_POST as &$post){
			$post = str_replace("'"," ",$post);
		}

		$content = json_encode($_POST);
		
		$content = preg_replace('/\\\u([0-9A-F]{4})/ei', "chr(hexdec('\\1'))", $content);
	
		$content = (utf8_encode($content));
		
		Db::getInstance()->execute("INSERT INTO "._DB_PREFIX_."jps_massexport_profiles(title,date,content) VALUES('".$_POST['nameprofile']."',now(),'$content')");
		
		
		$id = Db::getInstance()->getValue("SELECT LAST_INSERT_ID()");

		$results = Db::getInstance()->ExecuteS("SELECT * from "._DB_PREFIX_."jps_massexport_profiles WHERE id_profile=$id LIMIT 1");
		$profile = $results[0];
		
		$class = 'class="trone"';
		
		echo '
		<tr '.$class.' id="profile_'.$profile['id_profile'].'">
			<td>'.$profile['id_profile'].'</td>						<td>'.$profile['title'].'</td>
			<td>'.$profile['date'].'</td>
			<td style="text-align:center;">
				<a href="javascript:setProfile('.$profile['id_profile'].')" >
					<img src="'.$_POST['path'].'enabled.gif" title="Apply" />
				</a>
				<a href="javascript:deleteProfile('.$profile['id_profile'].')" >
					<img src="'.$_POST['path'].'delete.gif" title="Delete" />
				</a>
			</td>
		</tr>
		';
	
	}
	else if ($_GET['cmd']=="del"){
		$id = $_GET['id'];
		Db::getInstance()->execute("DELETE FROM "._DB_PREFIX_."jps_massexport_profiles WHERE id_profile = $id");
	}
	else if ($_GET['cmd']=="set"){
		$id = $_GET['id'];
		$results = Db::getInstance()->ExecuteS("SELECT * from "._DB_PREFIX_."jps_massexport_profiles WHERE id_profile=$id LIMIT 1");
		$profile = $results[0]['content'];
		echo $profile;
	}