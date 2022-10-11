<?php

	// Init
	$sql = array();
	$sql[] = 'SET foreign_key_checks = 0;';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow`;';	
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_lang`;';	
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_image`;';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_image_lang`;';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_product`;';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_category`;';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'opartslideshow_slideshow_cms`;';
	$sql[] = 'SET foreign_key_checks = 1;';