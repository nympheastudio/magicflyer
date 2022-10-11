<?php
/**
* 2007-2015 PrestaShop
*
* Jms Blog
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2015 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

if (!defined('_PS_VERSION_'))
	exit;
include_once(_PS_MODULE_DIR_.'jmsblog/class/JmsBlogHelper.php');
include_once(_PS_MODULE_DIR_.'jmsblog/JmsCategory.php');
class AdminJmsblogCategoriesController extends ModuleAdminController {
	public function __construct()
	{
		$this->name = 'jmsblog';
		$this->tab = 'front_office_features';
		$this->bootstrap = true;
		$this->lang = true;
		$this->context = Context::getContext();
		$this->secure_key = Tools::encrypt($this->name);
		$this->child	= array();
		parent::__construct();
	}

	public function renderList()
	{			
		
		$this->_html = $this->headerHTML();
		/* Validate & process */		
		if (Tools::isSubmit('CancelAddForm'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogCategories', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		elseif (Tools::isSubmit('submitCategory') || Tools::isSubmit('delete_id_category') || Tools::isSubmit('changeCategoryStatus'))
		{	
			if ($this->_postValidation())
			{
				$this->_postProcess();				
				$this->_html .= $this->renderListCategories();
			}
			else
			{
				$this->_html .= $this->renderNavigation();
				$this->_html .= $this->renderAddCategory();
			}
		}
		elseif (Tools::isSubmit('addCategory') || ((Tools::isSubmit('id_category') && $this->categoryExists((int)Tools::getValue('id_category')))))
		{			
			$this->_html .= $this->renderNavigation();
			$this->_html .= $this->renderAddCategory();
		}
		else
		{	
			$this->_html .= $this->renderListCategories();
		}		
		return $this->_html;
	}
	
	private function _postValidation()
	{
		$errors = array();		

		/* Validation for configuration */
		if (Tools::isSubmit('changeCategoryStatus'))
		{
			if (!Validate::isInt(Tools::getValue('status_id_category')))
				$errors[] = $this->l('Invalid Category');
		}			
		elseif (Tools::isSubmit('delete_id_category'))
		{
			if	((!Validate::isInt(Tools::getValue('delete_id_category')) || !$this->categoryExists((int)Tools::getValue('delete_id_category'))))
				$errors[] = $this->l('Invalid id_category');
		}		
		elseif (Tools::isSubmit('submitCategory')) 
		{
		/* Checks position */
			if (!Validate::isInt(Tools::getValue('ordering')) || (Tools::getValue('ordering') < 0))
				$errors[] = $this->l('Invalid Category ordering');
			/* If edit : checks post_id */
			if (Tools::isSubmit('id_category'))
			{					
				if (!Validate::isInt(Tools::getValue('id_category')) && !$this->itemExists(Tools::getValue('id_category')))
					$errors[] = $this->l('Invalid id_category');
			}

			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
			{
				if (Tools::strlen(Tools::getValue('title_'.$language['id_lang'])) > 255)
					$errors[] = $this->l('The title is too long.');
				if (Tools::strlen(Tools::getValue('alias_'.$language['id_lang'])) > 255)
					$errors[] = $this->l('The URL is too long.');
				if (Tools::strlen(Tools::getValue('description_'.$language['id_lang'])) > 4000)
					$errors[] = $this->l('The description is too long.');							
				if (Tools::getValue('image_'.$language['id_lang']) != null && !Validate::isFileName(Tools::getValue('image_'.$language['id_lang'])))
					$errors[] = $this->l('Invalid filename');
				if (Tools::getValue('image_old_'.$language['id_lang']) != null && !Validate::isFileName(Tools::getValue('image_old_'.$language['id_lang'])))
					$errors[] = $this->l('Invalid filename');

			}
			$id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
			if (Tools::strlen(Tools::getValue('title_'.$id_lang_default)) == 0)
				$errors[] = $this->l('The title is not set.');
		}	
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
		$errors = array();
		$jmsblog_setting = JmsBlogHelper::getSettingFieldsValues();
		if (Tools::isSubmit('submitCategory'))
		{			
			if (Tools::getValue('id_category'))
			{				
				$item = new JmsCategory((int)Tools::getValue('id_category'));
				if (!Validate::isLoadedObject($item))
				{
					$this->_html .= $this->displayError($this->l('Invalid id_category'));
					return;
				}
			}
			else
				$item = new JmsCategory();
			/* Sets ordering */			
			$item->ordering = (int)Tools::getValue('ordering');
			/* Sets ordering */
			$item->parent = Tools::getValue('parent');
			/* Sets active */
			$item->active = (int)Tools::getValue('active');
			/* Sets each langue fields */
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
			{
				$item->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
				$item->alias[$language['id_lang']] = Tools::getValue('alias_'.$language['id_lang']);
				if (!$item->alias[$language['id_lang']])
					$item->alias[$language['id_lang']] = JmsBlogHelper::makeAlias($item->title[$language['id_lang']]);
				$item->description[$language['id_lang']] = Tools::getValue('description_'.$language['id_lang']);								
								
				/* Uploads image and sets item */
				$type = Tools::strtolower(Tools::substr(strrchr($_FILES['image_'.$language['id_lang']]['name'], '.'), 1));
				$imagesize = array();
				$imagesize = @getimagesize($_FILES['image_'.$language['id_lang']]['tmp_name']);
				//echo "aaa"; exit;
				if (isset($_FILES['image_'.$language['id_lang']]) && isset($_FILES['image_'.$language['id_lang']]['tmp_name']) && !empty($_FILES['image_'.$language['id_lang']]['tmp_name']) && !empty($imagesize) && in_array(Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)), array('jpg', 'gif', 'jpeg', 'png')) && in_array($type, array('jpg', 'gif', 'jpeg', 'png')))
				{
					
					$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
					$salt = sha1(microtime());
					if ($error = ImageManager::validateUpload($_FILES['image_'.$language['id_lang']]))
						$errors[] = $error;
					elseif (!$temp_name || !move_uploaded_file($_FILES['image_'.$language['id_lang']]['tmp_name'], $temp_name))
						return false;
					elseif (!ImageManager::resize($temp_name, _PS_MODULE_DIR_.'/jmsblog/views/img/'.Tools::encrypt($_FILES['image_'.$language['id_lang']]['name'].$salt).'.'.$type, null, null, $type))
						$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));						
					if (isset($temp_name))
						@unlink($temp_name);
					$item->image[$language['id_lang']] = Tools::encrypt($_FILES['image_'.($language['id_lang'])]['name'].$salt).'.'.$type;							
					JmsBlogHelper::CreateThumb(_PS_MODULE_DIR_.'/jmsblog/views/img/', Tools::encrypt($_FILES['image_'.($language['id_lang'])]['name'].$salt).'.'.$type, $jmsblog_setting['JMSBLOG_IMAGE_WIDTH'], $jmsblog_setting['JMSBLOG_IMAGE_HEIGHT'], 'resized_', 0);
					JmsBlogHelper::CreateThumb(_PS_MODULE_DIR_.'/jmsblog/views/img/', Tools::encrypt($_FILES['image_'.($language['id_lang'])]['name'].$salt).'.'.$type, $jmsblog_setting['JMSBLOG_IMAGE_THUMB_WIDTH'], $jmsblog_setting['JMSBLOG_IMAGE_THUMB_HEIGHT'], 'thumb_', 1);					
					//delete old img
					$old_img = Tools::getValue('image_old_'.$language['id_lang']);
					if ($old_img && file_exists(_PS_MODULE_DIR_.'/jmsblog/views/img/'.$old_img))
						@unlink(_PS_MODULE_DIR_.'/jmsblog/views/img/'.$old_img);
						@unlink(_PS_MODULE_DIR_.'/jmsblog/views/img/resized_'.$old_img);												
						@unlink(_PS_MODULE_DIR_.'/jmsblog/views/img/thumb_'.$old_img);
				}
				elseif (Tools::getValue('image_old_'.$language['id_lang']) != '')
					$item->image[$language['id_lang']] = Tools::getValue('image_old_'.$language['id_lang']);												
			}
			
			/* Processes if no errors  */
			if (!$errors)
			{
				/* Adds */
				if (!Tools::getValue('id_category'))
				{				
					if (!$item->add())
						$errors[] = $this->displayError($this->l('The item could not be added.'));
				}
				/* Update */
				elseif (!$item->update())
					$errors[] = $this->displayError($this->l('The item could not be updated.'));				
			}
		}
		elseif (Tools::isSubmit('delete_id_category'))
		{
			$item = new JmsCategory((int)Tools::getValue('delete_id_category'));
			$res = $item->delete();	
			if (!$res)
				$this->_html .= Tools::displayError('Could not delete');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogCategories', true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		}
		elseif (Tools::isSubmit('changeCategoryStatus') && Tools::isSubmit('status_id_category'))
		{			
			$item = new JmsCategory((int)Tools::getValue('status_id_category'));
			if ($item->active == 0)
				$item->active = 1;
			else
				$item->active = 0;
			$res = $item->update();
			if (!$res)
				$this->_html .= Tools::displayError('The status could not be updated.');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogCategories', true).'&conf=5&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		}
		
		if (count($errors))
			$this->_html .= Tools::displayError(implode('<br />', $errors));
		elseif (Tools::isSubmit('submitCategory') && Tools::getValue('id_category'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogCategories', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
	}
	
	public function categoryExists($id_category)
	{
		$req = 'SELECT hs.`category_id`
				FROM `'._DB_PREFIX_.'jmsblog_categories` hs
				WHERE hs.`category_id` = '.(int)$id_category;
		$_category = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
		return ($_category);
	}
	
	public function treeCats($parent = 0, $lvl = 0)
	{
		$lvl ++;
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;
		$items = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`category_id` as category_id, hssl.`image`, hss.`ordering`, hss.`active`, hssl.`title`		
			FROM '._DB_PREFIX_.'jmsblog_categories hss
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang hssl ON (hss.category_id = hssl.category_id)
			WHERE hssl.id_lang = '.(int)$id_lang.
			' AND hss.`parent` = '.$parent.' 
			ORDER BY hss.`category_id` ASC'
		);			
		if (count($items))
		{
			while ($element = current($items)) 
			{
				$items[key($items)]['lvl'] = $lvl;
				$this->child[] = $items[key($items)];
				$this->treeCats($element['category_id'], $lvl);
				next($items);
			}
		}
	}
	public function getItemCount($category_id)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT COUNT(hss.`post_id`)
			FROM '._DB_PREFIX_.'jmsblog_posts hss
			WHERE hss.category_id = '.(int)$category_id
		);
	}
	public function makeTitleLevel($title, $level) 
	{
		$result_title = '';
		for($i = 1; $i < $level; $i++)
			$result_title .= '----';		
		$result_title .= "  ".$title;	
		return $result_title;
	}
	public function renderListCategories()
	{
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');		
		$this->treeCats(0, 0);
		$items = $this->child;
		foreach ($items as $key => $item) {
			$items[$key]['item_count'] = $this->getItemCount($item['category_id']);		
			$items[$key]['title'] = $this->makeTitleLevel($items[$key]['title'],$items[$key]['lvl']);
		}	
		$tpl = $this->createTemplate('listcategories.tpl');
		$tpl->assign( array(
			'link' => $this->context->link,
			'items' => $items,
		));
		return $tpl->fetch();
	}
	public function renderNavigation()
	{
		$html = '<div class="navigation">';
		$html .= '<a class="btn btn-default" href="'.AdminController::$currentIndex.
			'&configure='.$this->name.'
				&token='.Tools::getAdminTokenLite('AdminJmsblogCategories').'" title="Back to Dashboard"><i class="icon-home"></i>Back to Dashboard</a>';
		$html .= '</div>';
		return $html;
	}
	public function getParentOptions($category_id = 0)
	{		
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;
		$cat_arr = array();
		$cats = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`category_id` as category_id, hssl.`title` as title
			FROM '._DB_PREFIX_.'jmsblog_categories hss
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang hssl ON (hss.category_id = hssl.category_id)
			WHERE hssl.id_lang = '.(int)$id_lang.' 
			ORDER BY hss.ordering'
		);
		$total_cats = count($cats);
		for	($i = 0; $i < $total_cats; $i++)
		{
			$check = $this->is_child($category_id, $cats[$i]['category_id']);
			if (!$check)			
				$cat_arr[] = $cats[$i];
		}
		return $cat_arr;
	}
	public function is_child($parent_id, $test_id)
	{
		$is_child = 0;
		if ($parent_id == $test_id)		
			$is_child = 1;
		else
		{	
			$cat_list = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT hss.`category_id` as category_id, hss.`parent` as parent
				FROM '._DB_PREFIX_.'jmsblog_categories hss
				ORDER BY hss.ordering');
			$cats = array();
			$total_catlist = count($cat_list);
			for ($i = 0; $i < $total_catlist; $i ++)
				$cats[$cat_list[$i]['category_id']] = $cat_list[$i]['parent'];
			while ($cats[$test_id] != 0) 
			{
				if ($cats[$test_id] == $parent_id)
					$is_child = 1;
				$test_id = $cats[$test_id];
			}
		}
		return $is_child;
	}
	public function renderAddCategory()
	{
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');
		$category_id	= (int)Tools::getValue('id_category', 0);
		$categories 	= $this->getParentOptions($category_id);
		if (!count($categories))		
			$categories = array();		
		array_unshift($categories, array ( 'category_id' => 0,'title' => 'Root Category' ));
		$this->fields_form = array(
			'legend' => array(
					'title' => $this->l('Category informations'),
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
						'type' => 'text',
						'label' => $this->l('Alias'),
						'name' => 'alias',
						'lang' => true,
					),
					array(
						'type' => 'select',
						'lang' => true,
						'label' => $this->l('Parent Category'),
						'name' => 'parent',
						'desc' => $this->l('Please Select parent category'),
						'options' => array('query' => $categories,'id' => 'category_id','name' => 'title')
					),
					array(
						'type' => 'textarea',
						'label' => $this->l('Description'),
						'name' => 'description',
						'autoload_rte' => true,
						'lang' => true,
					),
					array(
						'type' => 'file_lang',
						'label' => $this->l('Image'),
						'name' => 'image',
						'lang' => true,
						'desc' => $this->l(sprintf('Max image size %s', ini_get('upload_max_filesize')))
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
				'name' => 'submitCategory'
			)
		);
		if (Tools::isSubmit('id_category')) {
			$item = new JmsCategory((int)Tools::getValue('id_category'));
			$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_category');					
			
			$has_picture = false;			
			foreach (Language::getLanguages(false) as $lang)
				if (isset($item->image[$lang['id_lang']]) && Tools::strlen($item->image[$lang['id_lang']]) > 0)					
					$has_picture = true;				

			if ($has_picture)
				$this->fields_form['input']['has_picture'] = array('type' => 'hidden', 'name' => 'has_picture');	
			if ($has_picture) 				
				$this->fields_form['images'] = $item->image;
			
		}	
		$this->fields_value = $this->getCategoryFieldsValues();
		$this->tpl_folder = 'jmsblogform/';		
		$this->tpl_form_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),			
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => __PS_BASE_URI__.'/modules/jmsblog/views/img/'
		);		
		return adminController::renderForm();
	}
	public function getCategoryFieldsValues()
	{
		$fields = array();
		if ((int)Tools::getValue('id_category'))
		{
			$item = new JmsCategory((int)Tools::getValue('id_category'));
			$fields['id_category'] 	= (int)Tools::getValue('id_category', $item->id);			
		}
		else
			$item = new JmsCategory();		
		$fields['parent'] 	= (int)Tools::getValue('parent', $item->parent);
		$fields['active'] = Tools::getValue('active', $item->active);			
		
		$languages = Language::getLanguages(false);	
		foreach ($languages as $lang)
		{
			$fields['image'][$lang['id_lang']] = Tools::getValue('image_'.(int)$lang['id_lang']);			
			$fields['title'][$lang['id_lang']] = Tools::getValue('title_'.(int)$lang['id_lang'], $item->title[$lang['id_lang']]);
			$fields['alias'][$lang['id_lang']] = Tools::getValue('alias_'.(int)$lang['id_lang'], $item->alias[$lang['id_lang']]);			
			$fields['description'][$lang['id_lang']] = Tools::getValue('description_'.(int)$lang['id_lang'], $item->description[$lang['id_lang']]);					
		}
		return $fields;
	}

	
	public function headerHTML()
	{
		if (Tools::getValue('controller') != 'AdminJmsblogCategories' && Tools::getValue('configure') != $this->name)
			return;		
		$this->context->controller->addJqueryUI('ui.sortable');		
		$html = '<script type="text/javascript">
			$(function() {
				var $categories = $("#categories");
				$categories.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updateCategoryOrdering";						
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}
				});
				$categories.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});				
			});
		</script>';

		return $html;
	}	
}
?>