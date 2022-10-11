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
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jmsadv_homepages`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jmsadv_prof`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jmsadv_rows`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jmsadv_position`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jmsadv_blocks`';
	$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'jmsadv_blocks_lang`';	