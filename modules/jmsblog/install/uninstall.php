<?php
/**
* 2007-2015 PrestaShop
*
* Jms Theme Layout
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

	$sql = array();
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jmsblog_categories`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jmsblog_categories_lang`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jmsblog_posts`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jmsblog_posts_lang`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jmsblog_posts_comments`';	