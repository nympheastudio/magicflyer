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

class JmsImportExport extends Module
{
	public	function __construct()
	{
		$this->name = 'jmsthemelayout';
		parent::__construct();
	}
	public function getHomepage($id_homepage) 
	{
		$req = 'SELECT hs.*
				FROM `'._DB_PREFIX_.'jmsadv_homepages` hs
				WHERE hs.`id_homepage` = '.(int)$id_homepage;
		$homepage = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
		return ($homepage);	
	}
	public function getProfile($id_profile) 
	{
		$req = 'SELECT hs.*
				FROM `'._DB_PREFIX_.'jmsadv_prof` hs
				WHERE hs.`id_prof` = '.(int)$id_profile;
		$profile = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
		return ($profile);	
	}
	public function getRows($id_profile) 
	{
		$this->context = Context::getContext();		
		$_rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM '._DB_PREFIX_.'jmsadv_rows hs			
			WHERE hs.id_prof = '.(int)$id_profile.
			' AND hs.`active` = 1
			ORDER BY hs.ordering'
		);
		return $_rows;
	}
	public function getPositions($id_row) 
	{
		$this->context = Context::getContext();		
		$_blocks = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.*
			FROM '._DB_PREFIX_.'jmsadv_position hs			
			WHERE hs.id_row = '.(int)$id_row.
			' AND hs.`active` = 1
			ORDER BY hs.ordering'
		);
		return $_blocks;
	}
	public function getBlocks($id_position) 
	{
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;	
		$_blocks = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.`show_title`,hs.`block_type`,hs.`hook_name`,hs.`module_name`,hsl.`title`,hsl.`html_content`
			FROM '._DB_PREFIX_.'jmsadv_blocks hs
			LEFT JOIN '._DB_PREFIX_.'jmsadv_blocks_lang hsl ON (hsl.id_block = hs.id_block)
			WHERE hsl.id_lang = '.(int)$id_lang.
			' AND hs.id_position = '.(int)$id_position.
			' AND hs.`active` = 1
			ORDER BY hs.ordering'
		);
		return $_blocks;
	}
	public function exportHomepage($id_homepage) 
	{
		$homepage = $this->getHomepage($id_homepage);		
		$filename = $homepage['title'].'.xml';
		header('Content-type: text/xml');
		header('Content-Disposition: attachment; filename="'.$filename.'"');	
		$xml_output = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$xml_output .= '<entity_profile>'."\n";		
		$header = $this->getProfile($homepage['id_header']);
		$xml_output .= '<profile title="'.$header['title'].'" profile_type="header" class_suffix="'.$header['class_suffix'].'">'."\n";					
		$rows = $this->getRows($homepage['id_header']);
		foreach ($rows as $row)			
		{	
			$xml_output .= '<row title="'.$row['title'].'" class="'.$row['class'].'" fullwidth="'.$row['fullwidth'].'">'."\n";
			$positions = $this->getPositions($row['id_row']);
			foreach ($positions as $position)			
			{
				$xml_output .= '<position title="'.$position['title'].'" class_suffix="'.$position['class_suffix'].'" col_lg="'.$position['col_lg'].'" col_sm="'.$position['col_sm'].'" col_md="'.$position['col_md'].'" col_xs="'.$position['col_xs'].'">'."\n";
				$blocks = $this->getBlocks($position['id_position']);
				foreach ($blocks as $block)			
				{
					if ($block['block_type'] == 'module')	
						$xml_output .= '<block title="'.$block['title'].'" block_type="module" module_name="'.$block['module_name'].'" hook_name="'.$block['hook_name'].'" show_title="'.$block['show_title'].'" />'."\n";
					elseif ($block['block_type'] == 'custom_html')
					{
						$html_content = $block['html_content'];
						$xml_output .= '<block title="'.$block['title'].'" block_type="custom_html" module_name="" hook_name="top" show_title="'.$block['show_title'].'">
				<htmlData><![CDATA['.$html_content.']]></htmlData>
			</block>'."\n";
					}	
				}	
				$xml_output .= '</position>'."\n";
			}	
			$xml_output .= '</row>'."\n";	
		}
		$xml_output .= '</profile>'."\n";	
		
		$homebody = $this->getProfile($homepage['id_homebody']);
		$xml_output .= '<profile title="'.$homebody['title'].'" profile_type="homebody" class_suffix="'.$homebody['class_suffix'].'">'."\n";					
		$rows = $this->getRows($homepage['id_homebody']);
		foreach ($rows as $row)			
		{	
			$xml_output .= '<row title="'.$row['title'].'" class="'.$row['class'].'" fullwidth="'.$row['fullwidth'].'">'."\n";
			$positions = $this->getPositions($row['id_row']);
			foreach ($positions as $position)			
			{
				$xml_output .= '<position title="'.$position['title'].'" class_suffix="'.$position['class_suffix'].'" col_lg="'.$position['col_lg'].'" col_sm="'.$position['col_sm'].'" col_md="'.$position['col_md'].'" col_xs="'.$position['col_xs'].'">'."\n";
				$blocks = $this->getBlocks($position['id_position']);
				foreach ($blocks as $block)			
				{
					if ($block['block_type'] == 'module')	
						$xml_output .= '<block title="'.$block['title'].'" block_type="module" module_name="'.$block['module_name'].'" hook_name="'.$block['hook_name'].'" show_title="'.$block['show_title'].'" />'."\n";
					elseif ($block['block_type'] == 'custom_html')
					{
						$html_content = $block['html_content'];
						$xml_output .= '<block title="'.$block['title'].'" block_type="custom_html" module_name="" hook_name="top" show_title="'.$block['show_title'].'">
				<htmlData><![CDATA['.$html_content.']]></htmlData>
			</block>'."\n";
					}	
				}	
				$xml_output .= '</position>'."\n";
			}	
			$xml_output .= '</row>'."\n";	
		}
		$xml_output .= '</profile>'."\n";

		$footer = $this->getProfile($homepage['id_footer']);
		$xml_output .= '<profile title="'.$footer['title'].'" profile_type="footer" class_suffix="'.$footer['class_suffix'].'">'."\n";					
		$rows = $this->getRows($homepage['id_footer']);
		foreach ($rows as $row)			
		{	
			$xml_output .= '<row title="'.$row['title'].'" class="'.$row['class'].'" fullwidth="'.$row['fullwidth'].'">'."\n";
			$positions = $this->getPositions($row['id_row']);
			foreach ($positions as $position)			
			{
				$xml_output .= '<position title="'.$position['title'].'" class_suffix="'.$position['class_suffix'].'" col_lg="'.$position['col_lg'].'" col_sm="'.$position['col_sm'].'" col_md="'.$position['col_md'].'" col_xs="'.$position['col_xs'].'">'."\n";
				$blocks = $this->getBlocks($position['id_position']);
				foreach ($blocks as $block)			
				{
					if ($block['block_type'] == 'module')	
						$xml_output .= '<block title="'.$block['title'].'" block_type="module" module_name="'.$block['module_name'].'" hook_name="'.$block['hook_name'].'" show_title="'.$block['show_title'].'" />'."\n";
					elseif ($block['block_type'] == 'custom_html')
					{
						$html_content = $block['html_content'];
						$xml_output .= '<block title="'.$block['title'].'" block_type="custom_html" module_name="" hook_name="top" show_title="'.$block['show_title'].'">
				<htmlData><![CDATA['.$html_content.']]></htmlData>
			</block>'."\n";
					}	
				}	
				$xml_output .= '</position>'."\n";
			}	
			$xml_output .= '</row>'."\n";	
		}
		$xml_output .= '</profile>'."\n";	
		$xml_output .= '</entity_profile>'."\n";
		echo $xml_output; 
		exit;		
	}	
}