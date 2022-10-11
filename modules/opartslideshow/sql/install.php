<?php

	// Init
	$sql = array();

	// Create Table slideshow
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow` (
		  `id_opartslideshow_slideshow` int(10) NOT NULL AUTO_INCREMENT,
		  `active` tinyint(1),
		  `width` int(4),
		  `height` int(4),
		  `spw` int(2),
		  `sph` int(2),
		  `delay`int(4),
		  `sDelay` int(3),
		  `opacity` float,
		  `titleSpeed` int(4),
		  `effect` int(1),		  
		  `navigation` tinyint(1),
		  `links` tinyint(1),
		  `hoverpause`tinyint(1),
		  `home` tinyint(1),
		  `hook` varchar(64) NOT NULL,
		  `showOnCat` tinyint(1),
		  `showOnProd` tinyint(1),
		  `showOnCms` tinyint(1),
  		PRIMARY KEY (`id_opartslideshow_slideshow`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
	
	// Create Table slideshow_lang
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_lang` (
		  `id_opartslideshow_slideshow` int(10) NOT NULL AUTO_INCREMENT,
		  `id_lang` int(10) NOT NULL,
		  `name` varchar(64) NOT NULL,
  		UNIQUE KEY `opartslideshow_slideshow_lang_index` (`id_opartslideshow_slideshow`,`id_lang`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';		
	
	
	// Create Table slideshow_image
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_image` (
	`id_opartslideshow_slideshow_image` int(10) NOT NULL AUTO_INCREMENT,
	`id_opartslideshow_slideshow` int(10),
	`filename` varchar(255) NOT NULL,
	`active` tinyint(1),
	`position` int(4),
	PRIMARY KEY (`id_opartslideshow_slideshow_image`),
	Foreign Key (id_opartslideshow_slideshow) references '._DB_PREFIX_.'opartslideshow_slideshow(id_opartslideshow_slideshow)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
	
	// Create Table slideshow_image_lang
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_image_lang` (
	`id_opartslideshow_slideshow_image` int(10) NOT NULL AUTO_INCREMENT,
	`id_lang` int(10) NOT NULL,
	`name` varchar(64) NOT NULL,
	`targeturl` varchar(255),	
	`description` varchar(255),
	UNIQUE KEY `opartslideshow_slideshow_image_lang_index` (`id_opartslideshow_slideshow_image`,`id_lang`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
	
	// Create Table slideshow_product
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_product` (
	`id_opartslideshow_slideshow_product` int(10) NOT NULL AUTO_INCREMENT,
	`id_opartslideshow_slideshow` int(10),
	`id_product` int(10),
	PRIMARY KEY (`id_opartslideshow_slideshow_product`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
	
	// Create Table slideshow_category
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_category` (
	`id_opartslideshow_slideshow_category` int(10) NOT NULL AUTO_INCREMENT,
	`id_opartslideshow_slideshow` int(10),
	`id_category` int(10),
	PRIMARY KEY (`id_opartslideshow_slideshow_category`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
	
	// Create Table slideshow_cms
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_cms` (
	`id_opartslideshow_slideshow_cms` int(10) NOT NULL AUTO_INCREMENT,
	`id_opartslideshow_slideshow` int(10),
	`id_cms` int(10),
	PRIMARY KEY (`id_opartslideshow_slideshow_cms`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';