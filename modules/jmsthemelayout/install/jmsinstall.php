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

include_once(_PS_MODULE_DIR_.'jmsthemelayout/JmsHomepage.php');
include_once(_PS_MODULE_DIR_.'jmsthemelayout/JmsProf.php');
include_once(_PS_MODULE_DIR_.'jmsthemelayout/JmsRow.php');
include_once(_PS_MODULE_DIR_.'jmsthemelayout/JmsPosition.php');
include_once(_PS_MODULE_DIR_.'jmsthemelayout/JmsBlock.php');

class JmsLayoutInstall
{
	public function createTable()
	{
		$sql = array();
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jmsadv_homepages` (
					`id_homepage` int(11) NOT NULL AUTO_INCREMENT,
					`title` varchar(100) NOT NULL,
					`id_header` int(11) NOT NULL,
					`id_homebody` int(11) NOT NULL,
					`id_footer` int(11) NOT NULL,
					`css_file` varchar(30) NOT NULL,
					`font_url` varchar(255) NOT NULL,
					`ordering` int(11) NOT NULL,
					PRIMARY KEY (`id_homepage`)
				 ) ENGINE=InnoDB  DEFAULT CHARSET=UTF8';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jmsadv_prof` (
					`id_prof` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`title` varchar(255) NOT NULL,
					`profile_type` varchar(255) NOT NULL,
					`class_suffix` varchar(30),					
					`ordering` int(10) unsigned NOT NULL,
					PRIMARY KEY (`id_prof`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8';

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jmsadv_rows` (
					`id_row` int(10) unsigned NOT NULL AUTO_INCREMENT,					
					`id_prof` int(10) unsigned NOT NULL,
					`title` varchar(255) NOT NULL,
					`class` varchar(50) NOT NULL,
					`fullwidth` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
					`active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
					`ordering` int(10) unsigned NOT NULL,
					PRIMARY KEY (`id_row`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8';

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jmsadv_position` (
					`id_position` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`id_row` int(10) unsigned NOT NULL,
					`title` varchar(255) NOT NULL,
					`class_suffix` varchar(30) NOT NULL,
					`col_lg` int(11) NOT NULL,
					`col_sm` int(11) NOT NULL,
					`col_md` int(11) NOT NULL,
					`col_xs` int(11) NOT NULL,
					`active` tinyint(1) unsigned NOT NULL DEFAULT 0,
					`ordering` int(10) unsigned NOT NULL,
				  PRIMARY KEY (`id_position`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8';

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jmsadv_blocks` (
				  `id_block` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `id_position` int(10) unsigned NOT NULL,
				  `show_title` tinyint(1) unsigned NOT NULL,
				  `block_type` varchar(25) NOT NULL,
				  `hook_name` varchar(30) NOT NULL,
				  `module_name` varchar(100) NOT NULL,
				  `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
				  `ordering` int(10) unsigned NOT NULL,
				  PRIMARY KEY (`id_block`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8';

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jmsadv_blocks_lang` (
					`id_block` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`id_lang` int(10) unsigned NOT NULL,
					`title` varchar(255) NOT NULL,
					`html_content` text NOT NULL,
					PRIMARY KEY (`id_block`, `id_lang`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8';

		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
	}
	public function _addHomePage($title, $header_id, $body_id, $footer_id, $ordering, $css_file = '', $font_url = '')
	{
		$homepage = new JmsHomepage();
		$homepage->title = $title;
		$homepage->id_header = $header_id;
		$homepage->id_homebody = $body_id;
		$homepage->id_footer = $footer_id;
		$homepage->css_file = $css_file;
		$homepage->font_url = $font_url;
		$homepage->ordering = $ordering;
		$homepage->add();
		return $homepage->id;
	}
	public function _addProfile($title, $profile_type, $class_suffix, $ordering)
	{
		$prof = new JmsProf();
		$prof->title = $title;
		$prof->profile_type = $profile_type;
		$prof->class_suffix = $class_suffix;
		$prof->ordering = $ordering;		
		$prof->add();
		return $prof->id;
	}
	public function _addRow($prof_id, $title, $class_suffix, $fullwidth, $ordering = 0, $active = 1)
	{
		$row = new JmsRow();		
		$row->id_prof = $prof_id;
		$row->title = $title;
		$row->class = $class_suffix;
		$row->fullwidth = $fullwidth;
		$row->active = $active;
		$row->ordering = $ordering;
		$row->add();
		return $row->id;
	}
	public function _addPos($row_id, $title, $class_suffix, $ordering = 0, $col_lg = 3, $col_md = 3, $col_sm = 6, $col_xs = 12, $active = 1)
	{
		$position = new JmsPosition();
		$position->id_row = $row_id;
		$position->title = $title;
		$position->class_suffix = $class_suffix;
		$position->col_lg = $col_lg;
		$position->col_md = $col_md;
		$position->col_sm = $col_sm;
		$position->col_xs = $col_xs;
		$position->active = $active;
		$position->ordering = $ordering;
		$position->add();
		return $position->id;
	}
	public function _addModule($pos_id, $blockname, $title, $hookname, $ordering = 0, $showtitle = 0, $active = 1)
	{
		$languages = Language::getLanguages(false);
		$block = new JmsBlock();
		$block->id_position = $pos_id;
		$block->show_title = $showtitle;
		$block->block_type = 'module';
		$block->hook_name = $hookname;		
		$block->module_name = $blockname;
		$block->active = $active;
		$block->ordering = $ordering;
		foreach ($languages as $language)
			$block->title[$language['id_lang']] = $title;
		$block->add();
	}
	public function _addHtml($pos_id, $title, $html, $ordering = 0, $showtitle = 1, $active = 1)
	{
		$languages = Language::getLanguages(false);
		$block = new JmsBlock();
		$block->id_position = $pos_id;
		$block->show_title = $showtitle;
		$block->block_type = 'custom_html';
		$block->hook_name = 'footer';
		$block->module_name = '';
		$block->active = $active;
		$block->ordering = $ordering;
		foreach ($languages as $language)
		{
			$block->title[$language['id_lang']] = $title;
			$block->html_content[$language['id_lang']] = $html;
		}
		$block->add();
	}
	public function _addLogoTheme($pos_id, $title, $ordering = 0, $active = 1)
	{
		$languages = Language::getLanguages(false);
		$block = new JmsBlock();
		$block->id_position = $pos_id;
		$block->show_title = 0;
		$block->block_type = 'logo';
		$block->hook_name = 'footer';
		$block->module_id = 0;
		$block->active = $active;
		$block->ordering = $ordering;
		foreach ($languages as $language)
		{
			$block->title[$language['id_lang']] = $title;			
		}
		$block->add();
	}
	public function InstallXML($filename) 
	{			
		$profile_ids = array();
		$context = Context::getContext();
		$shop_id = $context->shop->id;
		if (file_exists(_PS_MODULE_DIR_.'jmsthemelayout/xml/'.$filename))	
		{	
			$xml = simplexml_load_file(_PS_MODULE_DIR_.'jmsthemelayout/xml/'.$filename);
			foreach ($xml->profile as $profile)
			{				
				$profile_id = $this->_addProfile((string)$profile['title'], (string)$profile['profile_type'], (string)$profile['class_suffix']);
				$profile_ids[(string)$profile['profile_type']][] = $profile_id;
				$row_ordering = 0;
				foreach ($profile->row as $row)
				{
					$row_id = $this->_addRow($profile_id, (string)$row['title'], (string)$row['class'], (int)$row['fullwidth'], $row_ordering);			
					$pos_ordering = 0;
					foreach ($row->position as $position)
					{
						$pos_id = $this->_addPos($row_id, (string)$position['title'], (string)$position['class_suffix'], $pos_ordering, (int)$position['col_lg'], (int)$position['col_sm'], (int)$position['col_md'], (int)$position['col_xs']);
						$block_ordering = 0;
						foreach ($position->block as $block)
						{
							if ((string)$block['block_type'] == 'module')
								$this->_addModule($pos_id, (string)$block['module_name'], (string)$block['title'], (string)$block['hook_name'], $block_ordering);
							elseif ((string)$block['block_type'] == 'custom_html') 
							{
								$html = (string)$block->htmlData[0];
								$this->_addHtml($pos_id, (string)$block['title'], $html, $block_ordering, (int)$block['show_title']);
							}
							elseif ((string)$block['block_type'] == 'logo') 
							{
								$this->_addLogoTheme($pos_id, 'Logo', 0);
							}
							$block_ordering++;
						}		
						$pos_ordering++;
					}
					$row_ordering++;
				}
			}	
		}
		return $profile_ids;	
	}
	public function InstallDemo() 
	{
		$profile1_ids = $this->InstallXML('homepage1.xml');
		$home1_id = $this->_addHomePage('Home Page 1', $profile1_ids['header'][0], $profile1_ids['homebody'][0], $profile1_ids['footer'][0], 0);
		Configuration::updateValue('JMSADV_HOMEPAGE', $home1_id);
		$profile2_ids = $this->InstallXML('homepage2.xml');
		$home2_id = $this->_addHomePage('Home Page 2', $profile2_ids['header'][0], $profile2_ids['homebody'][0], $profile2_ids['footer'][0], 1);
		$profile3_ids = $this->InstallXML('homepage3.xml');
		$home3_id = $this->_addHomePage('Home Page 3', $profile3_ids['header'][0], $profile3_ids['homebody'][0], $profile3_ids['footer'][0], 2);
		$profile4_ids = $this->InstallXML('homepage4.xml');
		$home4_id = $this->_addHomePage('Home Page 4', $profile4_ids['header'][0], $profile4_ids['homebody'][0], $profile4_ids['footer'][0], 3);
		$profile5_ids = $this->InstallXML('homepage5.xml');
		$home5_id = $this->_addHomePage('Home Page 5', $profile5_ids['header'][0], $profile5_ids['homebody'][0], $profile5_ids['footer'][0], 4);
		$profile6_ids = $this->InstallXML('homepage6.xml');
		$home6_id = $this->_addHomePage('Home Page 6', $profile6_ids['header'][0], $profile6_ids['homebody'][0], $profile6_ids['footer'][0], 5);
		$profile7_ids = $this->InstallXML('homepage7.xml');
		$home7_id = $this->_addHomePage('Home Page 7', $profile6_ids['header'][0], $profile7_ids['homebody'][0], $profile7_ids['footer'][0], 6);
	}
	
}
