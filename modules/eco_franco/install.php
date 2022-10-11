<?php
class Modulefranco {	 
	public static function ModulefrancoInstall() {
		Db::getInstance()->Execute("
			CREATE TABLE `"._DB_PREFIX_."eco_francoregles` (
			    `id_francoregles` int(10) NOT NULL AUTO_INCREMENT,
				`libelle_francoregles` text NOT NULL,
                `montant_francoregles` float NOT NULL,
                 PRIMARY KEY (`id_francoregles`)
			) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
		
		Db::getInstance()->Execute("
			CREATE TABLE `"._DB_PREFIX_."eco_francoconditions` (
			    `francoconditions_id` int(10) NOT NULL AUTO_INCREMENT,
				`francoregles_id` int(10) NOT NULL,
                `francoconditions_type` int(10) NOT NULL,
			    `francoconditions_id_type` int(10) NOT NULL,
                 PRIMARY KEY (`francoconditions_id`)
			) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");	
			
		return true; 
		
	}
	public static function ModulefrancoDesinstall() {
	Db::getInstance()->Execute("DROP TABLE`"._DB_PREFIX_."eco_francoregles`");
	Db::getInstance()->Execute("DROP TABLE`"._DB_PREFIX_."eco_francoconditions`");
	return true; 
	}
	 
}
?>
