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

class JmsExecute extends Module
{
	public	function __construct()
	{
		$this->name = 'jmsthemelayout';
		parent::__construct();
	}
	/* check id value of profiles, rows, positions, blocks*/
	public function profileExists($id_prof)
	{
		$req = 'SELECT hs.`id_prof`
				FROM `'._DB_PREFIX_.'jmsadv_prof` hs
				WHERE hs.`id_prof` = '.(int)$id_prof;
		$profile = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
		return ($profile);
	}
	public function homeExists($id_homepage)
	{
		$req = 'SELECT hs.`id_homepage`
				FROM `'._DB_PREFIX_.'jmsadv_homepages` hs
				WHERE hs.`id_homepage` = '.(int)$id_homepage;
		$homepage = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
		return ($homepage);
	}
	public function rowExists($id_row)
	{
		$req = 'SELECT hs.`id_row`
				FROM `'._DB_PREFIX_.'jmsadv_rows` hs
				WHERE hs.`id_row` = '.(int)$id_row;
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
		return ($row);
	}

	public function positionExists($id_position)
	{
		$req = 'SELECT hs.`id_position`
				FROM `'._DB_PREFIX_.'jmsadv_position` hs
				WHERE hs.`id_position` = '.(int)$id_position;
		$position = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
		return ($position);
	}
	public function blockExists($id_block)
	{
		$req = 'SELECT hs.`id_block`
				FROM `'._DB_PREFIX_.'jmsadv_blocks` hs
				WHERE hs.`id_block` = '.(int)$id_block;
		$block = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
		return ($block);
	}	
	/* Profiles */
	/* Duplicate profile */
	public function _postDuplicate($id_prof)
	{
		$errors = array();
		$res = true;
		$getProf = new JmsProf($id_prof);
		$duplicateProf = $getProf->duplicateObject();
		$duplicateProf->title = $duplicateProf->title.'- Copy';
		$duplicateProf->profile_type = $duplicateProf->profile_type;
		if (!$duplicateProf->update())
			$errors[] = 'The duplicated profile cant update.';
		$listRows = $this->getRows(null, $id_prof);

		foreach ($listRows as $listRow)
		{
			$getRow = new JmsRow((int)$listRow['id_row']);
			$duplicateRow = $getRow->duplicateObject();
			$duplicateRow->title = $duplicateRow->title.'- Copy';
			$res = Db::getInstance()->execute('
				UPDATE `'._DB_PREFIX_.'jmsadv_rows` SET `id_prof` = '.$duplicateProf->id.' WHERE `id_row` = '.$duplicateRow->id
			);
			$listPositions = $this->getPositions((int)$listRow['id_row']);
			foreach ($listPositions as $lisPosition)
			{
				$getPos = new JmsPosition((int)$lisPosition['id_position']);
				$duplicatePos = $getPos->duplicateObject();
				$duplicatePos->title = $duplicatePos->title.'- Copy';
				$res &= Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'jmsadv_position` SET `id_row` = '.$duplicateRow->id.' WHERE `id_position` = '.$duplicatePos->id
				);
				$listBlocks = $this->getBlocks((int)$lisPosition['id_position']);
				foreach ($listBlocks as $listBlock)
				{
					$getBlock = new JmsBlock((int)$listBlock['id_block']);
					$duplicateBlock = $getBlock->duplicateObject();
					$duplicateBlock->title = $duplicateBlock->title.'- Copy';
					$res &= Db::getInstance()->execute('
						UPDATE `'._DB_PREFIX_.'jmsadv_blocks` SET `id_position` = '.$duplicatePos->id.' WHERE `id_block` = '.$duplicateBlock->id
					);					
				}
			}
		}
	}
	public function deleteHome($id_homepage)
	{
		$homepage = new JmsHomepage($id_homepage);	
		return $homepage->delete();
	}
	public function deleteProfile($id_prof)
	{
		$prof = new JmsProf($id_prof);
		$id_rows = Db::getInstance()->executeS('
			SELECT id_row FROM '._DB_PREFIX_.'jmsadv_rows 
			WHERE id_prof = '.$id_prof
		);
		foreach ($id_rows as $id_row)
			$this->deleteRow($id_row['id_row']);
		return $prof->delete();
	}
	/* Add or Update Values of profile */
	public function addUpdateProf($id_prof, $title, $class, $profile)
	{
		$errors = array();
		if ($id_prof)
			$prof = new JmsProf($id_prof);
		else
			$prof = new JmsProf();
		$prof->title = $title;
		$prof->class_suffix = $class;
		$prof->profile_type = $profile;		
		if (!$id_prof)
		{
			if (!$prof->add())
				$errors[] = $this->displayError($this->l('The item could not be added.'));
		}
		elseif (!$prof->update())
			$errors[] = $this->displayError($this->l('The item could not be updated.'));
		if ($errors)
			return $errors;
	}

	public function getProfFieldsValues($id)
	{
		$fields = array();
		if ($id)
		{
			$prof = new JmsProf($id);
			$fields['id_prof'] 	= (int)Tools::getValue('edit_id_prof', $prof->id);
		}
		else
			$prof = new JmsProf();
		$fields['title'] = Tools::getValue('title', $prof->title);
		$fields['class_suffix'] = Tools::getValue('class_suffix', $prof->class_suffix);		
		return $fields;
	}
	/* Add or Update Values of homepage */
	public function addUpdateHome($id_homepage, $title, $id_header, $id_homebody, $id_footer, $css_file = '', $font_url = '')
	{
		$errors = array();
		if ($id_homepage)
			$homepage = new JmsHomepage($id_homepage);
		else
			$homepage = new JmsHomepage();
		$homepage->title = $title;
		$homepage->id_header = $id_header;
		$homepage->id_homebody = $id_homebody;
		$homepage->id_footer = $id_footer;			
		$homepage->css_file = $css_file;			
		$homepage->font_url = $font_url;			
		if ((int)$id_homepage == 0)
		{
			if (!$homepage->add())
				$errors[] = $this->displayError($this->l('The item could not be added.'));
		}
		elseif (!$homepage->update())			
			$errors[] = $this->displayError($this->l('The item could not be updated.'));		
		if ($errors)
			return $errors;
	}
	public function getHomeFieldsValues($id)
	{
		$fields = array();
		if ($id)
		{
			$homepage = new JmsHomepage($id);
			$fields['id_homepage'] 	= (int)Tools::getValue('edit_id_homepage', $homepage->id);
		}
		else
			$homepage = new JmsHomepage();
		$fields['title'] = Tools::getValue('title', $homepage->title);
		$fields['id_header'] = Tools::getValue('id_header', $homepage->id_header);
		$fields['id_homebody'] = Tools::getValue('id_homebody', $homepage->id_homebody);
		$fields['id_footer'] = Tools::getValue('id_footer', $homepage->id_footer);
		$fields['css_file'] = Tools::getValue('css_file', $homepage->css_file);
		$fields['font_url'] = Tools::getValue('font_url', $homepage->font_url);
		return $fields;
	}
	/* get all values of profile */
	public function getProf($profile_type)
	{
		$this->context = Context::getContext();
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM '._DB_PREFIX_.'jmsadv_prof
			WHERE profile_type = "'.$profile_type.'"
			ORDER BY ordering'
		);
	}
	public function getHomes()
	{
		$this->context = Context::getContext();
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM '._DB_PREFIX_.'jmsadv_homepages
			ORDER BY ordering'
		);
	}
	/* Rows */
	/* add or update values of rows */
	public function addUpdateRows($id_row, $id_prof, $title, $class, $fullwidth, $active = null)
	{
		$errors = array();
		$context = Context::getContext();
		if ($id_row)
			$row = new JmsRow($id_row);
		else
			$row = new JmsRow();		
		$row->id_prof = $id_prof;
		$row->title = $title;
		$row->class = $class;
		$row->fullwidth = $fullwidth;
		$row->active = $active;
		if (!$id_row)
		{
			if (!$row->add())
				$errors[] = $this->displayError($this->l('The item could not be added.'));
		}
		elseif (!$row->update())
			$errors[] = $this->displayError($this->l('The item could not be updated.'));
		if ($errors)
			return $errors;
		else
			return;
	}

	public function getFieldsValuesOfRows($id_row)
	{
		$fields = array();
		if ($id_row)
		{
			$row = new JmsRow($id_row);
			$fields['id_row'] 	= (int)Tools::getValue('id_row', $row->id);
		}
		else
			$row = new JmsRow();
		$fields['id_prof'] = (int)Tools::getValue('id_prof');
		$fields['fullwidth'] = Tools::getValue('fullwidth', $row->fullwidth);
		$fields['active'] = Tools::getValue('active', $row->active);
		$fields['title'] = Tools::getValue('title', $row->title);
		$fields['class'] = Tools::getValue('class', $row->class);
		return $fields;
	}

	public function deleteRow($id_row)
	{
		$row = new JmsRow($id_row);
		$id_positions = Db::getInstance()->executeS('
			SELECT id_position FROM '._DB_PREFIX_.'jmsadv_position
			WHERE id_row = '.$id_row
		);
		foreach ($id_positions as $id_position)
			$this->deletePosition($id_position['id_position']);
		return $row->delete();
	}
	public function getRows($active = null, $id_prof)
	{
		$this->context = Context::getContext();
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.`id_row`, hs.`title`, hs.`active`, hs.`class`, hs.`fullwidth`
			FROM '._DB_PREFIX_.'jmsadv_rows hs
			WHERE 1 '.
			($id_prof ? ' AND hs.id_prof = '.$id_prof.'' : ' ').
			($active ? ' AND hs.`active` = 1' : ' ').'
			ORDER BY hs.ordering'
		);
	}
	public function changeRowStatus($id_row)
	{
		$row = new JmsRow($id_row);
		if ($row->active == 0)
			$row->active = 1;
		else
			$row->active = 0;
		return $row->update();
	}

	public function displayRowStatus($id_row, $active, $id_prof, $controller)
	{
		$title = ((int)$active == 0 ? $this->l('Disabled') : $this->l('Enabled'));
		$icon = ((int)$active == 0 ? 'icon-remove' : 'icon-check');
		$class = ((int)$active == 0 ? 'btn-danger' : 'btn-success');
		$html = '<a class="btn '.$class.'" href="'.AdminController::$currentIndex.
			'&configure='.$this->name.'
				&token='.Tools::getAdminTokenLite($controller).'
				&changeRowStatus&id_prof='.(int)$id_prof.'&id_row='.(int)$id_row.'" title="'.$title.'"><i class="'.$icon.'"></i></a>';

		return $html;
	}

	/* Position */
	public function addUpdatePosition($id_position, $id_row, $title, $class_suffix, $col_lg, $col_md, $col_sm, $col_xs, $active)
	{
		$errors = array();
		if ($id_position)
			$position = new JmsPosition($id_position);
		else
			$position = new JmsPosition();
		$position->id_row = $id_row;
		$position->title = $title;
		$position->class_suffix = $class_suffix;
		$position->col_lg = $col_lg;
		$position->col_md = $col_md;
		$position->col_sm = $col_sm;
		$position->col_xs = $col_xs;
		$position->active = $active;
		if (!$id_position)
		{
			if (!$position->add())
				$errors[] = $this->displayError($this->l('The item could not be added.'));
		}
		elseif (!$position->update())
			$errors[] = $this->displayError($this->l('The item could not be updated.'));
		if ($errors)
			return $errors;
	}

	public function getFieldsValuesOfPosition($id_position)
	{
		$fields = array();
		if ($id_position)
		{
			$position = new JmsPosition($id_position);
			$fields['id_position'] = (int)Tools::getValue('id_position', $position->id);
		}
		else
			$position = new JmsPosition();
		$fields['title'] 		= Tools::getValue('title', $position->title);
		$fields['class_suffix'] = Tools::getValue('class_suffix', $position->class_suffix);
		$fields['active'] 		= Tools::getValue('active', $position->active);
		$fields['col_lg'] 		= Tools::getValue('col_lg', $position->col_lg);
		$fields['col_md'] 		= Tools::getValue('col_md', $position->col_md);
		$fields['col_sm'] 		= Tools::getValue('col_sm', $position->col_sm);
		$fields['col_xs'] 		= Tools::getValue('col_xs', $position->col_xs);
		$fields['id_prof']		= Tools::getValue('id_prof');
		$fields['id_row'] 		= Tools::getValue('id_row');
		return $fields;
	}

	public function deletePosition($id_position)
	{
		$position = new JmsPosition($id_position);
		$id_blocks = Db::getInstance()->executeS('
			SELECT id_block FROM '._DB_PREFIX_.'jmsadv_blocks
			WHERE id_position = '.$id_position
		);
		foreach ($id_blocks as $id_block)
			$this->deleteBlock($id_block['id_block']);
		return $position->delete();
	}

	public function getIdPositions($id_row, $active = null)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM '._DB_PREFIX_.'jmsadv_position
			WHERE id_row = '.(int)$id_row.
			($active ? ' AND `active` = 1' : ' ').'
			ORDER BY ordering
		');
	}
	public function getPositions($id_row, $active = null, $id_prof, $controller) 
	{		
		$_positions = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT *
			FROM '._DB_PREFIX_.'jmsadv_position
			WHERE id_row = '.(int)$id_row.
			($active ? ' AND `active` = 1' : ' ').'
			ORDER BY ordering
		');
		foreach ($_positions as $key => $_position)
			$_positions[$key]['status'] = $this->displayPositionStatus($_position['id_position'], $id_row, $_position['active'], $id_prof, $controller);
		return $_positions;
	}

	public function changePositionStatus($id_position)
	{
		$position = new JmsPosition($id_position);
		if ($position->active == 0)
			$position->active = 1;
		else
			$position->active = 0;
		return $position->update();
	}

	public function displayPositionStatus($id_position, $id_row, $active, $id_prof, $controller)
	{
		$title = ((int)$active == 0 ? $this->l('Disabled') : $this->l('Enabled'));
		$icon = ((int)$active == 0 ? 'icon-remove' : 'icon-check');
		$class = ((int)$active == 0 ? 'btn-danger' : 'btn-success');
		$html = '<a class="btn '.$class.'" href="'.AdminController::$currentIndex.
			'&configure='.$this->name.'
				&token='.Tools::getAdminTokenLite($controller).'
				&changePositionStatus&id_prof='.(int)$id_prof.'&id_row='.$id_row.'&id_position='.(int)$id_position.'" title="'.$title.'"><i class="'.$icon.'"></i> '.$title.'</a>';
		return $html;
	}

	/* Block */
	public function addUpdateBlock($id_block, $id_position, $title, $show_title, $html_content, $block_type, $hook_name, $module_name, $active)
	{
		$errors = array();
		/* Sets ID if needed */
		if ($id_block)
			$jmsblock = new JmsBlock($id_block);
		else
			$jmsblock = new JmsBlock();
		/* Sets values */
		$jmsblock->id_position = $id_position;
		$jmsblock->show_title = $show_title;
		$jmsblock->block_type = $block_type;
		$jmsblock->hook_name = $hook_name;		
		$jmsblock->module_name = $module_name;
		$jmsblock->active = $active;
		/* Sets each langue fields */
		$languages = Language::getLanguages(false);
		foreach ($languages as $language)
		{
			$jmsblock->title[$language['id_lang']] = $title;
			$jmsblock->html_content[$language['id_lang']] = $html_content;
		}

		/* Update */
		if ($id_block)
		{
			if (!$jmsblock->update())
				$errors[] = $this->displayError($this->l('The block could not be updated.'));
		}
		/* Add */
		elseif (!$jmsblock->add())
				$errors[] = $this->displayError($this->l('The block could not be added.'));
		if ($errors)
			return $errors;
	}
	public function deleteBlock($id_block)
	{		
		$block = new JmsBlock($id_block);
		return $block->delete();
	}

	public function changeBlockStatus($id_block)
	{
		$block = new JmsBlock($id_block);
		if ($block->active == 0)
			$block->active = 1;
		else
			$block->active = 0;
		return $block->update();
	}

	public function getBlocks($id_position, $active = null, $id_prof, $controller) 
	{
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;
		$_blocks = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.`id_block`, hsl.`title`, hs.`active`,hs.`id_position`,hs.`block_type`,hs.`hook_name`,hsl.`html_content`
			FROM '._DB_PREFIX_.'jmsadv_blocks hs
			LEFT JOIN '._DB_PREFIX_.'jmsadv_blocks_lang hsl ON (hsl.id_block = hs.id_block)
			WHERE hsl.id_lang = '.(int)$id_lang.
			' AND hs.id_position = '.(int)$id_position.
			($active ? ' AND hs.`active` = 1' : ' ').'
			ORDER BY hs.ordering'
		);
		foreach ($_blocks as $key => $_block) 
			$_blocks[$key]['status'] = $this->displayBlockStatus($_block['id_block'], $id_position, $_block['active'], $id_prof, $controller);
		return $_blocks;
	}

	public function displayBlockStatus($id_block, $id_row, $active, $id_prof, $controller)
	{
		$icon = ((int)$active == 0 ? 'icon-remove' : 'icon-check');
		$class = ((int)$active == 0 ? 'btn-danger' : 'btn-success');
		$html = '<a class="btn '.$class.'" href="'.AdminController::$currentIndex.
			'&configure='.$this->name.'
				&token='.Tools::getAdminTokenLite($controller).'
				&changeBlockStatus&id_prof='.(int)$id_prof.'&id_row='.$id_row.'&id_block='.(int)$id_block.'"><i class="'.$icon.'"></i> </a>';

		return $html;
	}

	/* get modules list */
	public function getModules()
	{
		$this->context = Context::getContext();
		$id_shop = $this->context->shop->id;

		return Db::getInstance()->ExecuteS('
		SELECT m.*
		FROM `'._DB_PREFIX_.'module` m
		JOIN `'._DB_PREFIX_.'module_shop` ms ON (m.`id_module` = ms.`id_module` AND ms.`id_shop` = '.(int)($id_shop).')
		');
	}

	public static function checkModuleCallable($id_module) 
	{
		if (!($moduleInstance = Module::getInstanceByID($id_module)))
			return false;
		$hooks = array();
		$hookAssign = array('rightcolumn','leftcolumn','home','top','footer');
		foreach ($hookAssign as $hook)
		{
			$retro_hook_name = Hook::getRetroHookName($hook);
			if (is_callable(array($moduleInstance, 'hook'.$hook)) || is_callable(array($moduleInstance, 'hook'.$retro_hook_name)))
				$hooks[] = $retro_hook_name;
		}
		$results = self::getHookByArrName( $hooks );
		return $results;

	}

	public static function getHookByArrName($arrName)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_hook`, `name`
		FROM `'._DB_PREFIX_.'hook` 
		WHERE `name` IN (\''.implode("','", $arrName).'\')');
		return $result;
	}

	public function getActBlocks($id_position)
	{
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;

		$_blocks = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.`id_block`, hsl.`title`, hs.`active`,hs.`id_position`,hs.`block_type`,hs.`hook_name`,hs.`module_name`,hsl.`html_content`,hs.`show_title`
			FROM '._DB_PREFIX_.'jmsadv_blocks hs
			LEFT JOIN '._DB_PREFIX_.'jmsadv_blocks_lang hsl ON (hsl.id_block = hs.id_block)
			WHERE hsl.id_lang = '.(int)$id_lang.
			' AND hs.id_position = '.(int)$id_position.
			' AND hs.`active` = 1
			ORDER BY hs.ordering'
		);

		foreach ($_blocks as $key => $_block) 
		{
			if	($_block['block_type'] == 'custom_html') 
				$_blocks[$key]['return_value'] = $_block['html_content'];
			else if	($_block['block_type'] == 'module') 
			{				
				$module_name = $_block['module_name'];				
				$_blocks[$key]['return_value'] = $this->MNexec($_block['hook_name'], array(), $module_name);
			}	
			else if	($_block['block_type'] == 'logo') 
			{
				$_force_ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
				$protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
				$mobile_device = $this->context->getMobileDevice();
				if ($mobile_device && Configuration::get('PS_LOGO_MOBILE'))
					$logo = $this->context->link->getMediaLink(_PS_IMG_.Configuration::get('PS_LOGO_MOBILE').'?'.Configuration::get('PS_IMG_UPDATE_TIME'));
				else
					$logo = $this->context->link->getMediaLink(_PS_IMG_.Configuration::get('PS_LOGO'));
				$_blocks[$key]['return_value'] = '<a href="';
				if (isset($force_ssl) && $force_ssl)
					$_blocks[$key]['return_value'] .= $protocol_link.Tools::getShopDomainSsl().__PS_BASE_URI__;
				else
					$_blocks[$key]['return_value'] .= _PS_BASE_URL_.__PS_BASE_URI__;
				 $_blocks[$key]['return_value'] .= '" title="'.Configuration::get('PS_SHOP_NAME').'">';
				$_blocks[$key]['return_value'] .= '<img class="logo" src="'.$logo.'" alt="'.Configuration::get('PS_SHOP_NAME').'" />';
				$_blocks[$key]['return_value'] .='</a>';
			}				
		}		
		return $_blocks;
	}
	
	public static function MNexec($hook_name, $hookArgs = array(), $module_name = null)
	{		
		if (empty($module_name) || !Validate::isHookName($hook_name))			
			die(Tools::displayError());
		
		$context = Context::getContext();
		if (!isset($hookArgs['cookie']) || !$hookArgs['cookie'])
			$hookArgs['cookie'] = $context->cookie;
		if (!isset($hookArgs['cart']) || !$hookArgs['cart'])
			$hookArgs['cart'] = $context->cart;

		if (!($moduleInstance = Module::getInstanceByName($module_name)))
			return;
		$retro_hook_name = Hook::getRetroHookName($hook_name);
		
		$hook_callable = is_callable(array($moduleInstance, 'hook'.$hook_name));
		$hook_retro_callable = is_callable(array($moduleInstance, 'hook'.$retro_hook_name));
		$output = '';
		
		if (($hook_callable || $hook_retro_callable) && Module::preCall($moduleInstance->name))
		{
			if ($hook_callable)
				$output = $moduleInstance->{'hook'.$hook_name}($hookArgs);
			else if ($hook_retro_callable)
				$output = $moduleInstance->{'hook'.$retro_hook_name}($hookArgs);
		}
		return $output;
	}
	
	public static function exec($hook_name, $hookArgs = array(), $id_module = null)
	{
		if ((!empty($id_module) && !Validate::isUnsignedId($id_module)) || !Validate::isHookName($hook_name))
			die(Tools::displayError());
		$context = Context::getContext();
		if (!isset($hookArgs['cookie']) || !$hookArgs['cookie'])
			$hookArgs['cookie'] = $context->cookie;
		if (!isset($hookArgs['cart']) || !$hookArgs['cart'])
			$hookArgs['cart'] = $context->cart;

		if (!($moduleInstance = Module::getInstanceByID($id_module)))
			return;
		$retro_hook_name = Hook::getRetroHookName($hook_name);
		
		$hook_callable = is_callable(array($moduleInstance, 'hook'.$hook_name));
		$hook_retro_callable = is_callable(array($moduleInstance, 'hook'.$retro_hook_name));
		$output = '';
		
		if (($hook_callable || $hook_retro_callable) && Module::preCall($moduleInstance->name))
		{
			if ($hook_callable)
				$output = $moduleInstance->{'hook'.$hook_name}($hookArgs);
			else if ($hook_retro_callable)
				$output = $moduleInstance->{'hook'.$retro_hook_name}($hookArgs);
		}
		return $output;
	}
	public function getProfClass($id_prof)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT class_suffix
			FROM '._DB_PREFIX_.'jmsadv_prof
			'.($id_prof ? 'WHERE id_prof = '.$id_prof : '')
		);
	}
	public function getHomeCssFile($id_homepage)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT css_file
			FROM '._DB_PREFIX_.'jmsadv_homepages
			'.($id_homepage ? 'WHERE id_homepage = '.$id_homepage : '')
		);
	}
	public function getFontUrl($id_homepage)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT font_url
			FROM '._DB_PREFIX_.'jmsadv_homepages
			'.($id_homepage ? 'WHERE id_homepage = '.$id_homepage : '')
		);
	}	
}