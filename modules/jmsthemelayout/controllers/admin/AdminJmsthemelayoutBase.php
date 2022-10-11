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

if (!defined('_PS_VERSION_'))
	exit;

include_once(_PS_MODULE_DIR_.'jmsthemelayout/class/JmsExecute.php');
class AdminJmsthemelayoutBaseController extends ModuleAdminControllerCore {
	public function __construct()
	{
		$this->name = 'jmsthemelayout';
		$this->tab = 'front_office_features';
		$this->bootstrap = true;
		$this->lang = true;
		$this->context = Context::getContext();
		$this->secure_key = Tools::encrypt($this->name);		
		$_controller = Tools::getValue('controller');
		$this->classname = $_controller;
		$this->profiletype = Tools::strtolower(Tools::substr($_controller, 19, Tools::strlen($_controller)));
		parent::__construct();
	}

	public function renderList()
	{
		//echo $this->profiletype; exit;
		$jms_exc = new JmsExecute();
		$this->_html = $this->headerHTML();
		/* Validate & process */

		if (Tools::isSubmit('submitCancelAddForm') || Tools::isSubmit('submitCancelBlock') || Tools::isSubmit('submitCancelPosition') || Tools::isSubmit('submitLinkCancel'))
			Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_prof='.Tools::getValue('id_prof'));		
		elseif (Tools::isSubmit('addProf') || (Tools::isSubmit('edit_id_prof') && $jms_exc->profileExists((int)Tools::getValue('edit_id_prof'))))
		{
			$this->_html .= $this->renderNavigation();
			$this->_html .= $this->renderAddProf();
		}
		elseif (Tools::isSubmit('submitProf') || Tools::isSubmit('delete_id_prof'))
		{
			if ($this->_postValidation())
			{
				$this->_postProcess();				
				$this->_html .= $this->renderListProf();
			}
			else
			{
				$this->_html .= $this->renderNavigation();
				$this->_html .= $this->renderAddProf();
			}
		}
		elseif (Tools::isSubmit('rowlist'))
		{			
			$this->_html .= $this->renderNavigation();
			$this->_html .= $this->renderListRows();
		}
		elseif (Tools::isSubmit('submitRow') || Tools::isSubmit('delete_id_row') || Tools::isSubmit('changeRowStatus') || Tools::isSubmit('submitPosition') || Tools::isSubmit('delete_id_position') || Tools::isSubmit('changePositionStatus') || Tools::isSubmit('submitBlock') || Tools::isSubmit('delete_id_block') || Tools::isSubmit('changeBlockStatus') || Tools::isSubmit('changeLinkStatus') || Tools::isSubmit('submitLink') || Tools::isSubmit('delete_id_link'))
		{
			if ($this->_postValidation())
			{
				$this->_postProcess();
				$this->_html .= $this->renderListRows();
			}
			else 
			{
				$this->_html .= $this->renderNavigation();
				$this->_html .= $this->renderListRows();
			}	

		}
		elseif (Tools::isSubmit('addPosition') || (Tools::isSubmit('edit_position') && $jms_exc->positionExists((int)Tools::getValue('id_position'))))
		{
			$this->_html .= $this->renderNavigation();
			$this->_html .= $this->renderAddPosition();
			$this->_html .= $this->renderBlockList((int)Tools::getValue('id_row'), Tools::getValue('id_prof'));
		}
		elseif (Tools::isSubmit('addRow') || (Tools::isSubmit('id_row') && $jms_exc->rowExists((int)Tools::getValue('id_row')))) 
		{
			$this->_html .= $this->renderNavigation();
			$this->_html .= $this->renderRowForm();
		}
		elseif (Tools::isSubmit('addBlock') || (Tools::isSubmit('edit_block') && $jms_exc->blockExists((int)Tools::getValue('id_block')))) 
		{
			$this->_html .= $this->renderNavigation();
			$this->_html .= $this->renderBlockAddForm();			
		}		
		elseif (Tools::isSubmit('duplicate'))
		{
			$jms_exc->_postDuplicate((int)Tools::getValue('id_prof'));			
			$this->_html .= $this->renderListProf();
		}
		else		
			$this->_html .= $this->renderListProf();		
		return $this->_html;
	}
	
	private function _postValidation()
	{
		$errors = array();
		$jms_exc = new JmsExecute();

		/* Validation for configuration */
		if (Tools::isSubmit('changeRowStatus'))
		{
			if (!Validate::isInt(Tools::getValue('id_row')))
				$errors[] = $this->l('Invalid row');
		}
		elseif (Tools::isSubmit('submitProf'))
		{
			if (Tools::strlen(Tools::getValue('title')) > 255)
				$errors[] = $this->l('The title is too long.');
			if (Tools::strlen(Tools::getValue('title')) == 0)
				$errors[] = $this->l('The title is not set.');
		}
		elseif (Tools::isSubmit('submitPosition'))
		{
			if (Tools::strlen(Tools::getValue('title')) > 255)
				$errors[] = $this->l('The title is too long.');
			if (Tools::strlen(Tools::getValue('title')) == 0)
				$errors[] = $this->l('The title is not set.');
		}
		/* Validation for Point */
		elseif (Tools::isSubmit('submitRow'))
		{
			/* If edit : checks id_row */
			if (Tools::isSubmit('id_row'))
			{
				if (!Validate::isInt(Tools::getValue('id_row')) && !$jms_exc->rowExists(Tools::getValue('id_row')))
					$errors[] = $this->l('Invalid id_row');
			}
			if (Tools::strlen(Tools::getValue('title')) > 255)
				$errors[] = $this->l('The title is too long.');
			if (Tools::strlen(Tools::getValue('title')) == 0)
				$errors[] = $this->l('The title is not set.');
		}
		elseif (Tools::isSubmit('changeBlockStatus')) 
		{
			if (!Validate::isInt(Tools::getValue('id_block')))
				$errors[] = $this->l('Invalid block');
		}
		elseif (Tools::isSubmit('submitBlock')) 
		{
			/* If edit : checks id_row */
			if (Tools::isSubmit('id_block'))
			{
				if (!Validate::isInt(Tools::getValue('id_block')) && !$jms_exc->blockExists(Tools::getValue('id_block')))
					$errors[] = $this->l('Invalid id_block');
			}
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
			{
				if (Tools::strlen(Tools::getValue('title_'.$language['id_lang'])) > 255)
					$errors[] = $this->l('The title is too long.');				
			}
			$id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
			if (Tools::strlen(Tools::getValue('title_'.$id_lang_default)) == 0)
				$errors[] = $this->l('The title is not set.');
		}		
		elseif (Tools::isSubmit('changeLinkStatus')) 
		{
			if (!Validate::isInt(Tools::getValue('id_link')))
				$errors[] = $this->l('Invalid link');
		}		
		elseif (Tools::isSubmit('submitLink')) 
		{
			/* If edit : checks id_row */
			if (Tools::isSubmit('id_link'))
			{					
				if (!Validate::isInt(Tools::getValue('id_link')) && !$jms_exc->linkExists(Tools::getValue('id_link')))
					$errors[] = $this->l('Invalid id_link');
			}
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
			{
				if (Tools::strlen(Tools::getValue('title_'.$language['id_lang'])) > 255)
					$errors[] = $this->l('The title is too long.');				
			}
			$id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
			if (Tools::strlen(Tools::getValue('title_'.$id_lang_default)) == 0)
				$errors[] = $this->l('The title is not set.');								
		} /* Validation for deletion */
		elseif (Tools::isSubmit('delete_id_row'))
		{
			if	((!Validate::isInt(Tools::getValue('delete_id_row')) || !$jms_exc->rowExists((int)Tools::getValue('delete_id_row'))))
				$errors[] = $this->l('Invalid id_row');
		}
		elseif (Tools::isSubmit('delete_id_position'))
		{
			if	(!Validate::isInt(Tools::getValue('delete_id_position')))
				$errors[] = $this->l('Invalid id position');
		}
		elseif (Tools::isSubmit('delete_id_block') && (!Validate::isInt(Tools::getValue('delete_id_block')) || !$jms_exc->blockExists((int)Tools::getValue('delete_id_block'))))
			$errors[] = $this->l('Invalid id_block');
		elseif (Tools::isSubmit('delete_id_link') && (!Validate::isInt(Tools::getValue('delete_id_link')) || !$jms_exc->linkExists((int)Tools::getValue('delete_id_link'))))
			$errors[] = $this->l('Invalid id_link');
		/* Display errors if needed */
		if (count($errors))
		{
			$this->_html .= Tools::displayError(implode('<br />', $errors));
			return false;
		}

		/* Returns if validation is ok */
		return true;
	}
	private function _postProcess()
	{
		$jms_exc = new JmsExecute();
		$errors = array();
		if (Tools::isSubmit('submitProf'))
		{
			if (Tools::getValue('id_prof'))
				$id_prof = Tools::getValue('id_prof');
			else
				$id_prof = null;
			$res = $jms_exc->addUpdateProf($id_prof, Tools::getValue('title'), Tools::getValue('class_suffix'), $this->profiletype);
			if ($res)
				$errors[] = $res;
		}
		elseif (Tools::isSubmit('delete_id_prof'))
		{
			$res = $jms_exc->deleteProfile((int)Tools::getValue('delete_id_prof'));
			if (!$res)
				$this->_html .= Tools::displayError('Could not delete');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		}
		elseif (Tools::isSubmit('changeRowStatus') && Tools::isSubmit('id_row'))
		{
			$res = $jms_exc->changeRowStatus(Tools::getValue('id_row'));
			if (!$res)
				$this->_html .= Tools::displayError('The status could not be updated.');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=5&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_prof='.Tools::getValue('id_prof'));
		}
		elseif (Tools::isSubmit('submitRow'))
		{
			/* Sets ID if needed */
			if (Tools::getValue('id_row'))
				$id_row = Tools::getValue('id_row');
			else
				$id_row = null;
			$res = $jms_exc->addUpdateRows($id_row, Tools::getValue('id_prof'), Tools::getValue('title'), Tools::getValue('class'), Tools::getValue('fullwidth'), Tools::getValue('active'));
			if ($res)
				$errors[] = $res;

		}
		elseif (Tools::isSubmit('delete_id_row'))
		{
			$res = $jms_exc->deleteRow((int)Tools::getValue('delete_id_row'));
			if (!$res)
				$this->_html .= Tools::displayError('Could not delete');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_prof='.Tools::getValue('id_prof'));
		}
		elseif (Tools::isSubmit('submitPosition'))
		{
			if (Tools::getValue('id_position'))
				$id_position = Tools::getValue('id_position');
			else
				$id_position = null;
			$res = $jms_exc->addUpdatePosition($id_position, Tools::getValue('id_row'), Tools::getValue('title'), Tools::getValue('class_suffix'), Tools::getValue('col_lg'), Tools::getValue('col_md'), Tools::getValue('col_sm'), Tools::getValue('col_xs'), Tools::getValue('active'));
			if ($res)
				$errors[] = $res;

		}
		elseif (Tools::isSubmit('delete_id_position'))
		{
			$res = $jms_exc->deletePosition(Tools::getValue('delete_id_position'));
			if (!$res)
				$this->_html .= Tools::displayError('Could not delete');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_prof='.Tools::getValue('id_prof'));
		}
		elseif (Tools::isSubmit('changePositionStatus') && Tools::isSubmit('id_position'))
		{
			$res = $jms_exc->changePositionStatus(Tools::getValue('id_position'));
			if (!$res)
				$this->_html .= Tools::displayError('The status could not be updated.');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=5&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_prof='.Tools::getValue('id_prof'));
		}
		elseif (Tools::isSubmit('submitBlock'))
		{
			/* Sets ID if needed */
			if (Tools::getValue('id_block'))
			{
				$block = new JmsBlock((int)Tools::getValue('id_block'));
				if (!Validate::isLoadedObject($block))
				{
					$this->_html .= Tools::displayError($this->l('Invalid id_block'));
					return;
				}
			}
			else
				$block = new JmsBlock();
			/* Sets values */
			$block->id_position = (int)Tools::getValue('id_position');
			$block->active = (int)Tools::getValue('active');
			$block->block_type = Tools::getValue('block_type');			
			$block->module_name = Tools::getValue('module_name');
			$block->hook_name = Tools::getValue('hook_name');
			$block->show_title = Tools::getValue('show_title');
			$id_lang = $this->context->language->id;
			/* Sets each langue fields */
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
			{
				$block->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
				if (Tools::getValue('id_block'))
					$block->html_content[$language['id_lang']] = Tools::getValue('html_content_'.$language['id_lang']);
				else
				{
					$html_content = Tools::getValue('html_content_'.$id_lang);
					$block->html_content[$language['id_lang']] = $html_content;
				}
			}
			/* Processes if no errors  */
			if (!$errors)
			{
				/* Adds */
				if (!Tools::getValue('id_block'))
				{
					if (!$block->add())
						$errors[] = Tools::displayError($this->l('The block could not be added.'));
				}
				/* Update */
				elseif (!$block->update())
					$errors[] = Tools::displayError($this->l('The block could not be updated.'));
			}
		}
		elseif (Tools::isSubmit('delete_id_block'))
		{
			$res = $jms_exc->deleteBlock(Tools::getValue('delete_id_block'));
			if (!$res)
				$this->_html .= Tools::displayError('Could not delete');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_prof='.Tools::getValue('id_prof'));
		}
		elseif (Tools::isSubmit('changeBlockStatus') && Tools::isSubmit('id_block'))
		{
			$res = $jms_exc->changeBlockStatus(Tools::getValue('id_block'));
			if (!$res)
				$this->_html .= Tools::displayError('The status could not be updated.');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=5&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_prof='.Tools::getValue('id_prof'));
		}		
		if (count($errors))
			$this->_html .= Tools::displayError(implode('<br />', $errors));
		elseif (Tools::isSubmit('submitRow') && Tools::getValue('id_row'))
			Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_prof='.Tools::getValue('id_prof'));
		elseif (Tools::isSubmit('submitRow'))
			Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=3&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_prof='.Tools::getValue('id_prof'));
		elseif (Tools::isSubmit('submitLink') && Tools::getValue('id_link'))
			Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&edit_block&id_prof='.Tools::getValue('id_prof').'&id_block='.Tools::getValue('id_block').'&id_position='.Tools::getValue('id_position'));
		elseif (Tools::isSubmit('submitLink'))
			Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=3&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&edit_block&id_prof='.Tools::getValue('id_prof').'&id_block='.Tools::getValue('id_block').'&id_position='.Tools::getValue('id_position'));
		elseif (Tools::isSubmit('submitBlock') && Tools::getValue('id_block'))
			Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_prof='.Tools::getValue('id_prof'));
		elseif (Tools::isSubmit('submitBlock') || Tools::isSubmit('submitPosition') || Tools::isSubmit('submitRow'))
			Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=3&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&rowlist&id_prof='.Tools::getValue('id_prof'));
		elseif (Tools::isSubmit('changeLinkStatus'))	
			Tools::redirectAdmin($this->context->link->getAdminLink($this->classname, true).'&conf=5&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&edit_block&id_prof='.Tools::getValue('id_prof').'&id_block='.Tools::getValue('id_block').'&id_position='.Tools::getValue('id_position'));	
	}

	public function renderListProf()
	{
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');
		$jms_exc = new JmsExecute();
		$get_prof = $jms_exc->getProf($this->profiletype);
		$this->override_folder = 'jmsthemelayout_base/';
		$tpl = $this->createTemplate('listProf.tpl');
		$tpl->assign( array(
			'link' => $this->context->link,
			'getProf' => $get_prof,
			'adminlink' => $this->context->link->getAdminLink($this->classname),
		));
		return $tpl->fetch();
	}
	public function renderNavigation()
	{
		$html = '<div class="navigation">';
		$html .= '<a class="btn btn-default" href="'.AdminController::$currentIndex.
			'&configure='.$this->name.'
				&token='.Tools::getAdminTokenLite($this->classname).'" title="Back to Dashboard"><i class="icon-home"></i>Back to Dashboard</a>';
		$html .= '</div>';
		return $html;
	}
	public function renderAddProf()
	{
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Profile Informations'),
				'icon' => 'icon-cogs'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Title'),
					'name' => 'title'
				),
				array(
					'type' => 'text',
					'label' => $this->l('class_suffix'),
					'name' => 'class_suffix'
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'name' => 'submitProf'
			)
		);
		if (Tools::isSubmit('edit_id_prof'))
			$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_prof');
		$this->fields_value = $this->getProfFieldsValues();

		return adminController::renderForm();
	}
	public function getProfFieldsValues()
	{
		$jms_exc = new JmsExecute();
		return $jms_exc->getProfFieldsValues((int)Tools::getValue('edit_id_prof'));
	}

	public function renderListRows()
	{
		$jms_exc = new JmsExecute();
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');
		if (Tools::isSubmit('rowlist'))
		{
			$rows = $jms_exc->getRows(null, Tools::getValue('id_prof'));
			$id_prof = Tools::getValue('id_prof');
			$name_prof = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT title
				FROM '._DB_PREFIX_.'jmsadv_prof
				'.($id_prof ? 'WHERE id_prof = '.$id_prof : '')
			);			
		}
		
		foreach ($rows as $key => $row) 
		{
			$rows[$key]['status'] = $jms_exc->displayRowStatus($row['id_row'], $row['active'], Tools::getValue('id_prof'), $this->classname);
			$rows[$key]['positions'] = $jms_exc->getPositions($row['id_row'], null, Tools::getValue('id_prof'), $this->classname);
			foreach ($rows[$key]['positions'] as $number => $position)
				$rows[$key]['positions'][$number]['blocks'] = $jms_exc->getBlocks($position['id_position'], null, $id_prof, $this->classname);
		}	
		$this->override_folder = 'jmsthemelayout_base/';
		$tpl = $this->createTemplate('rowslist.tpl');
		$tpl->assign(
			array(
				'link' => $this->context->link,
				'adminlink' => $this->context->link->getAdminLink($this->classname),
				'rows' => $rows,
				'id_prof' => $id_prof,
				'name_prof' => $name_prof[0]['title'],
				'image_baseurl' => _MODULE_DIR_.$this->module->name.'/views/img/',
				'current_url' => Tools::getHttpHost(true).__PS_BASE_URI__.'modules/'.$this->name.'/ajax_'.$this->name.'.php'
				)
		);

		return $tpl->fetch();
	}

	public function headerHTML()
	{
		if (Tools::getValue('controller') != $this->classname && Tools::getValue('configure') != $this->name)
			return;
		$this->context->controller->addJqueryUI('ui.resizable');		
		$this->context->controller->addJqueryUI('ui.sortable');
		$this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/views/js/resize_script.js', 'all');
		/* Style & js for fieldset 'rows configuration' */
		$html = '<script type="text/javascript">
			$(function() {
				var $myProf = $("#prof_list");
				$myProf.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updateProfOrdering";						
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}
				});
				$myProf.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
				var $myRows = $(".rowlist");
				$myRows.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updateRowsOrdering";						
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}	
				});
				$myRows.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
				var $myposition = $(".row-positions");
				$myposition.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updatePositionsOrdering";
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}	
				});
				
				$myposition.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
				var $myblocks = $(".pos-blocks");
				$myblocks.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updateBlocksOrdering";
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}					
				});
				$myblocks.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
				var $myLinks = $("#links");
				$myLinks.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updateLinksOrdering";
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}
				});
				$myLinks.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
			});
		</script>';

		return $html;
	}
	public function renderAddPosition()
	{
		$columns = array();				
		$columns[] = array('value' => 12);		
		$columns[] = array('value' => 11);
		$columns[] = array('value' => 10);
		$columns[] = array('value' => 9);
		$columns[] = array('value' => 8);
		$columns[] = array('value' => 7);
		$columns[] = array('value' => 6);
		$columns[] = array('value' => 5);
		$columns[] = array('value' => 4);
		$columns[] = array('value' => 3);
		$columns[] = array('value' => 2);
		$columns[] = array('value' => 1);
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Position informations'),
				'icon' => 'icon-cogs'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('title'),
					'name' => 'title',
					'class' => ' fixed-width-xl'
					),
				array(
					'type' => 'text',
					'label' => $this->l('Class Suffix'),
					'name' => 'class_suffix',
					'class' => ' fixed-width-xl'
					),
				array(
					'type' => 'select',
					'label' => $this->l('Desktops Width( ≥1200px )'),
					'name' => 'col_lg',
					'desc' => $this->l('Bootstrap Grid has 12 columns, You select number of column.'),
					'options' => array('query' => $columns,'id' => 'value','name' => 'value')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Medium devices Width( ≥992px )'),
					'name' => 'col_md',
					'desc' => $this->l('Bootstrap Grid has 12 columns, You select number of column.'),
					'options' => array('query' => $columns,'id' => 'value','name' => 'value')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Tablets Width( ≥768px )'),
					'name' => 'col_sm',
					'desc' => $this->l('Bootstrap Grid has 12 columns, You select number of column.'),
					'options' => array('query' => $columns,'id' => 'value','name' => 'value')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Phone Width( <768px )'),
					'name' => 'col_xs',
					'desc' => $this->l('Bootstrap Grid has 12 columns, You select number of column.'),
					'options' => array('query' => $columns,'id' => 'value','name' => 'value')
				),
				array(
					'type' => 'switch',
					'label' => $this->l('Active'),
					'name' => 'active',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					),
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'name' => 'submitPosition'
			)
		);

		if (Tools::isSubmit('edit_position'))
			$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_position');
		$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_prof');
		$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_row');
		$this->fields_form['buttons'][] = array( 'type' => 'submit', 'name' => 'submitCancelPosition','class' => 'pull-left', 'title' => 'Cancel','icon' => 'process-icon-cancel');
		$this->fields_value = $this->getFieldsValuesOfPosition();
		return adminController::renderForm();
	}
	public function getFieldsValuesOfPosition()
	{
		$jms_exc = new JmsExecute();
		return $jms_exc->getFieldsValuesOfPosition((int)Tools::getValue('id_position'));
	}
	public function renderRowForm()
	{
		$jms_exc = new JmsExecute();
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Row informations'),
				'icon' => 'icon-cogs'
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Title'),
					'name' => 'title'
				),
				array(
					'type' => 'text',
					'label' => $this->l('Class'),
					'name' => 'class'
				),
				array(
					'type' => 'switch',
					'label' => $this->l('Full Width'),
					'name' => 'fullwidth',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					),
				),
				array(
					'type' => 'switch',
					'label' => $this->l('Active'),
					'name' => 'active',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					),
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'name' => 'submitRow'
			)
		);

		if (Tools::isSubmit('id_row') && $jms_exc->rowExists((int)Tools::getValue('id_row')))
			$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_row');
		$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_prof');

		$this->fields_form['buttons'][] = array( 'type' => 'submit', 'name' => 'submitCancelAddForm','class' => 'pull-left', 'title' => 'Cancel','icon' => 'process-icon-cancel');
		
		$this->fields_value = $this->getFieldsValuesOfRows();
		return adminController::renderForm();
	}
	public function getFieldsValuesOfRows()
	{
		$jms_exc = new JmsExecute();
		return $jms_exc->getFieldsValuesOfRows((int)Tools::getValue('id_row'));
	}
	public function renderBlockAddForm()
	{
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/views/js/admin_script.js', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');
		$jms_exc = new JmsExecute();
		$btypes = array();
		$btypes[] = array('value' => 'custom_html','title' => 'Custom Html');
		$btypes[] = array('value' => 'module','title' => 'Assign Module');		
		$btypes[] = array('value' => 'logo','title' => 'Theme logo');		
		$modules = array();			  
		$hmodules = $jms_exc->getModules();		 
		foreach ($hmodules as $_module) 
			if	($jms_exc->checkModuleCallable($_module['id_module']))
				$modules[] = $_module;
		$hookAssign = array();
		$hookAssign[] = array('name' => 'rightcolumn');
		$hookAssign[] = array('name' => 'leftcolumn');
		$hookAssign[] = array('name' => 'home');
		$hookAssign[] = array('name' => 'top');
		$hookAssign[] = array('name' => 'footer');		
		$this->fields_form = array(
			'legend' => array(
				'title' => $this->l('Block informations'),
				'icon' => 'icon-cogs'
				
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Title'),
					'name' => 'title',
					'lang' => true,
					
				),
				array(
					'type' => 'switch',
					'label' => $this->l('Show Title'),
					'name' => 'show_title',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'show_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'show_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					),
				),
				array(
					'type' => 'select',
					'lang' => true,
					'label' => $this->l('Block Type'),
					'name' => 'block_type',
					'desc' => $this->l('Select Block Type'),
					'options' => array('query' => $btypes,'id' => 'value','name' => 'title'
					)
				),	
				array(
					'type' => 'textarea',
					'label' => $this->l('Html Content'),
					'name' => 'html_content',
					'autoload_rte' => true,
					'lang' => true,
					'form_group_class' => 'html_content',
				),				
				array(
					'type' => 'select',
					'lang' => true,
					'label' => $this->l('Module Assign'),
					'name' => 'module_name',
					'desc' => $this->l('Select a Module Name'),
					'form_group_class' => 'module',
					'options' => array('query' => $modules,'id' => 'name','name' => 'name'
					)
				),
				array(
					'type' => 'select',
					'lang' => true,
					'label' => $this->l('Hook'),
					'name' => 'hook_name',
					'desc' => $this->l('Select a Hook'),
					'form_group_class' => 'module',
					'options' => array('query' => $hookAssign,'id' => 'name','name' => 'name')
				),
				array(
					'type' => 'switch',
					'label' => $this->l('Active'),
					'name' => 'active',
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					),
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'name' => 'submitBlock'
			)
		);

		if (Tools::isSubmit('id_block') && $jms_exc->blockExists((int)Tools::getValue('id_block')))
			$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_block');
		$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_prof');
		$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_position');
		$this->fields_form['buttons'][] = array( 'type' => 'submit', 'name' => 'submitCancelBlock','class' => 'pull-left', 'title' => 'Cancel','icon' => 'process-icon-cancel');

		$this->fields_value = $this->getFieldsValuesOfBlock();
		return adminController::renderForm();
	}
	public function getFieldsValuesOfBlock()
	{
		$jms_exc = new JmsExecute();
		$fields = array();

		if (Tools::isSubmit('id_block') && $jms_exc->blockExists((int)Tools::getValue('id_block')))
		{
			$block = new JmsBlock((int)Tools::getValue('id_block'));
			$fields['id_block'] = (int)Tools::getValue('id_block', $block->id);
		}
		else
			$block = new JmsBlock();
		
		$fields['id_prof']		= (int)Tools::getValue('id_prof');
		$fields['id_position']	= (int)Tools::getValue('id_position');
		$fields['active'] 		= Tools::getValue('active', $block->active);		
		$fields['block_type'] 	= Tools::getValue('block_type', $block->block_type);							
		$fields['module_name'] 	= Tools::getValue('module_name', $block->module_name);
		$fields['hook_name'] 	= Tools::getValue('hook_name', $block->hook_name);
		$fields['show_title'] 	= Tools::getValue('show_title', $block->show_title);
		
		$languages = Language::getLanguages(false);

		foreach ($languages as $lang)
		{	
			$fields['title'][$lang['id_lang']] = Tools::getValue('title_'.(int)$lang['id_lang'], $block->title[$lang['id_lang']]);			
			$fields['html_content'][$lang['id_lang']] = Tools::getValue('html_content_'.(int)$lang['id_lang'], $block->html_content[$lang['id_lang']]);
		}
		return $fields;
	}
		
	public function renderBlockList($id_row, $id_prof)
	{
		$jms_exc = new JmsExecute();
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$positions = $jms_exc->getIdPositions($id_row);
		//get id
		foreach ($positions as $key => $position)
			$positions[$key]['status'] = $jms_exc->displayPositionStatus($position['id_position'], $position['id_row'], $position['active'], $id_prof, $this->classname);
		$this->override_folder = 'jmsthemelayout_base/';	
		$tpl = $this->createTemplate('positionlist.tpl');
		$tpl->assign(
			array(
				'link' => $this->context->link,
				'adminlink' => $this->context->link->getAdminLink($this->classname),
				'positions' => $positions,
				'id_row' => $id_row,
				'id_prof' => $id_prof
				)
		);
		return $tpl->fetch();
	}	

}
?>