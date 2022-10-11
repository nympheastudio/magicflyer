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
include_once(_PS_MODULE_DIR_.'jmsblog/JmsPost.php');
class AdminJmsblogPostController extends ModuleAdminController {
	public function __construct()
	{
		$this->name = 'jmsblog';
		$this->tab = 'front_office_features';
		$this->bootstrap = true;
		$this->lang = true;
		$this->context = Context::getContext();
		$this->secure_key = Tools::encrypt($this->name);
		$this->catselect = array();
		parent::__construct();
	}

	public function renderList()
	{			
		
		$this->_html = $this->headerHTML();
		/* Validate & process */		
		if (Tools::isSubmit('CancelAddForm'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogPost', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		elseif (Tools::isSubmit('submitPost') || Tools::isSubmit('delete_id_post') || Tools::isSubmit('changePostStatus'))
		{	
			if ($this->_postValidation())
			{
				$this->_postProcess();				
				$this->_html .= $this->renderFilter();
				$this->_html .= $this->renderListPost($this->context->cookie->filter_category_id, $this->context->cookie->filter_state, $this->context->cookie->filter_start, $this->context->cookie->filter_limit);
				$this->html .= $this->renderPagination();
			}
			else
			{
				$this->_html .= $this->renderNavigation();
				$this->_html .= $this->renderAddPost();
			}
		}
		elseif (Tools::isSubmit('addPost') || ((Tools::isSubmit('id_post') && $this->postExists((int)Tools::getValue('id_post')))))
		{			
			$this->_html .= $this->renderNavigation();
			$this->_html .= $this->renderAddPost();
		}
		else
		{	
			if(Tools::isSubmit('filter_category_id')) {
				$this->context->cookie->filter_category_id = (int)Tools::getValue('filter_category_id', 0);				
			}	
			if(Tools::isSubmit('filter_state'))
				$this->context->cookie->filter_state 		= (int)Tools::getValue('filter_state', -1);
			else 
				$this->context->cookie->filter_state 		= -1;
			if(Tools::isSubmit('start'))
				$this->context->cookie->filter_start 		= (int)Tools::getValue('start', 0);
			else 
				$this->context->cookie->filter_start 		= 0;
			if(Tools::isSubmit('limit'))
				$this->context->cookie->filter_limit 		= (int)Tools::getValue('limit', 20);
			else 
				$this->context->cookie->filter_limit 		= 20;								
			$this->_html .= $this->renderFilter();	
			$this->_html .= $this->renderListPost($this->context->cookie->filter_category_id, $this->context->cookie->filter_state, $this->context->cookie->filter_start, $this->context->cookie->filter_limit);
			$this->_html .= $this->renderPagination();
		}		
		return $this->_html;
	}
	
	private function _postValidation()
	{
		$errors = array();		

		/* Validation for configuration */
		if (Tools::isSubmit('changePostStatus'))
		{
			if (!Validate::isInt(Tools::getValue('status_id_post')))
				$errors[] = $this->l('Invalid Post');
		}			
		elseif (Tools::isSubmit('delete_id_post'))
		{
			if	((!Validate::isInt(Tools::getValue('delete_id_post')) || !$this->postExists((int)Tools::getValue('delete_id_post'))))
				$errors[] = $this->l('Invalid id_post');
		}		
		elseif (Tools::isSubmit('submitPost')) 
		{
			/* Checks position */
			if (!Validate::isInt(Tools::getValue('ordering')) || (Tools::getValue('ordering') < 0))
				$errors[] = $this->l('Invalid Post ordering');
			/* If edit : checks post_id */
			if (Tools::isSubmit('id_post'))
			{					
				if (!Validate::isInt(Tools::getValue('id_post')) && !$this->postExists(Tools::getValue('id_post')))
					$errors[] = $this->l('Invalid id_post');
			}
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
			{
				if (Tools::strlen(Tools::getValue('title_'.$language['id_lang'])) > 255)
					$errors[] = $this->l('The title is too long.');				
				if (Tools::strlen(Tools::getValue('alias_'.$language['id_lang'])) > 255)
					$errors[] = $this->l('The URL is too long.');
				if (Tools::strlen(Tools::getValue('introtext_'.$language['id_lang'])) > 4000)
					$errors[] = $this->l('The introtext is too long.');
				if (Tools::strlen(Tools::getValue('fulltext_'.$language['id_lang'])) > 4000)
					$errors[] = $this->l('The fulltext is too long.');				
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
		if (Tools::isSubmit('submitPost'))
		{			
			/* Sets ID if needed */
			if (Tools::getValue('id_post'))
			{
				$item = new JmsPost((int)Tools::getValue('id_post'));
				if (Tools::getValue('created') == '') 
					$item->created = date('Y-m-d h:i:s');				
				else 
					$item->created = Tools::getValue('created');				
				
				if (Tools::getValue('modified') == '') 
					$item->modified = date('Y-m-d h:i:s');				
				else 
					$item->modified = Tools::getValue('modified');
					
				if (!Validate::isLoadedObject($item))
				{
					$this->_html .= $this->displayError($this->l('Invalid id_post'));
					return;
				}
			}
			else
			{
				$item = new JmsPost();				
				if (Tools::getValue('created') == '') 
					$item->created = date('Y-m-d h:i:s');				
				else 
					$item->created = Tools::getValue('created');
				if (Tools::getValue('modified') == '') 
					$item->modified = date('Y-m-d h:i:s');				
				else 
					$item->modified = Tools::getValue('modified');				
				$item->views = 0;
			}
			/* Sets ordering */
			$item->ordering = (int)Tools::getValue('ordering');
			$item->link_video = Tools::getValue('link_video');
			$item->category_id = Tools::getValue('category_id');

			/* Sets active */
			$item->active = (int)Tools::getValue('active');
			/* Sets each langue fields */
			$languages = Language::getLanguages(false);
			foreach ($languages as $language)
			{
				$item->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
				$item->alias[$language['id_lang']] = Tools::getValue('alias_'.$language['id_lang']);
				$item->tags[$language['id_lang']] = Tools::getValue('tags_'.$language['id_lang']);
				if (!$item->alias[$language['id_lang']])
					$item->alias[$language['id_lang']] = JmsBlogHelper::makeAlias($item->title[$language['id_lang']]);				
				$item->introtext[$language['id_lang']] = Tools::getValue('introtext_'.$language['id_lang']);
				$item->fulltext[$language['id_lang']] = Tools::getValue('fulltext_'.$language['id_lang']);				
				$item->meta_desc[$language['id_lang']] = Tools::getValue('meta_desc_'.$language['id_lang']);
				$item->meta_key[$language['id_lang']] = Tools::getValue('meta_key_'.$language['id_lang']);
				$item->key_ref[$language['id_lang']] = Tools::getValue('key_ref_'.$language['id_lang']);
				
				/* Uploads image and sets item */
				$type = Tools::strtolower(Tools::substr(strrchr($_FILES['image_'.$language['id_lang']]['name'], '.'), 1));
				$imagesize = array();
				$imagesize = @getimagesize($_FILES['image_'.$language['id_lang']]['tmp_name']);
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
					JmsBlogHelper::CreateThumb(_PS_MODULE_DIR_.'/jmsblog/views/img/', Tools::encrypt($_FILES['image_'.($language['id_lang'])]['name'].$salt).'.'.$type, $jmsblog_setting['JMSBLOG_IMAGE_THUMB_WIDTH'], $jmsblog_setting['JMSBLOG_IMAGE_THUMB_HEIGHT'], 'thumb_', 0);
					//delete old img
					$old_img = Tools::getValue('image_old_'.$language['id_lang']);
					if	($old_img && file_exists(_PS_MODULE_DIR_.'/jmsblog/views/img/'.$old_img))
						@unlink(_PS_MODULE_DIR_.'/jmsblog/views/img/'.$old_img);
						@unlink(_PS_MODULE_DIR_.'/jmsblog/views/img/resized_'.$old_img);
						@unlink(_PS_MODULE_DIR_.'/jmsblog/views/img/thumb_'.$old_img);												
				}
				elseif (Tools::getValue('image_old_'.$language['id_lang']) != '')
					$item->image[$language['id_lang']] = Tools::getValue('image_old_'.$language['id_lang']);												
			}
			//print_r($item->tags); exit;
			/* Processes if no errors  */
			if (!$errors)
			{
				/* Adds */
				if (!Tools::getValue('id_post'))
				{
					if (!$item->add())
						$errors[] = $this->displayError($this->l('The post could not be added.'));
				}
				/* Update */
				elseif (!$item->update())
					$errors[] = $this->displayError($this->l('The post could not be updated.'));				
			}
		}
		elseif (Tools::isSubmit('delete_id_post'))
		{
			$item = new JmsPost((int)Tools::getValue('delete_id_post'));
			$res = $item->delete();	
			if (!$res)
				$this->_html .= Tools::displayError('Could not delete');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogPost', true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		}
		elseif (Tools::isSubmit('changePostStatus') && Tools::isSubmit('status_id_post'))
		{			
			$item = new JmsPost((int)Tools::getValue('status_id_post'));
			if ($item->active == 0)
				$item->active = 1;
			else
				$item->active = 0;
			$res = $item->update();
			if (!$res)
				$this->_html .= Tools::displayError('The status could not be updated.');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogPost', true).'&conf=5&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		}
		
		if (count($errors))
			$this->_html .= Tools::displayError(implode('<br />', $errors));
		elseif (Tools::isSubmit('submitPost') && Tools::getValue('id_post'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminJmsblogPost', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
	}
		
	public function postExists($id_post)
	{
		$req = 'SELECT hs.`post_id`
				FROM `'._DB_PREFIX_.'jmsblog_posts` hs
				WHERE hs.`post_id` = '.(int)$id_post;
		$post = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);
		return ($post);
	}
	
	public function getPosts($category_id = 0, $state = -1, $start = 0, $limit = 20)
	{
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;
		$filter = '';
		if	($state != -1)
			$filter = ' AND hss.`active` = '.$state;		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`post_id` as post_id, hssl.`image`,hss.`category_id`, hss.`ordering`, hss.`active`, hssl.`title`, hssll.`title` as category_title,
			hssl.`alias`,hssl.`fulltext`,hssl.`introtext`,hssl.`meta_desc`,hssl.`meta_key`,hssl.`key_ref`
			FROM '._DB_PREFIX_.'jmsblog_posts hss
			LEFT JOIN '._DB_PREFIX_.'jmsblog_posts_lang hssl ON (hss.post_id = hssl.post_id)
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang hssll ON (hss.category_id = hssll.category_id)
			WHERE hssl.id_lang = '.(int)$id_lang.
			' AND hssll.id_lang = '.(int)$id_lang.
			$filter.
			($category_id ? ' AND hss.`category_id` = '.$category_id : ' ').'
			ORDER BY hss.created DESC 
			LIMIT '.$start.','.$limit
		);
	}
	
	public function getPostCount($category_id = 0, $state = -1)
	{				
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;
		$filter = '';
		if	($state != -1)
			$filter = ' AND hss.`active` = '.$state;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT COUNT(hss.`post_id`)
			FROM '._DB_PREFIX_.'jmsblog_posts hss
			LEFT JOIN '._DB_PREFIX_.'jmsblog_posts_lang hssl ON (hss.post_id = hssl.post_id)
			WHERE hssl.id_lang = '.(int)$id_lang.
			$filter.
			($category_id ? ' AND hss.`category_id` = '.$category_id : ' ').'
			ORDER BY hss.post_id'
		);
	}
	public function renderFilter()
	{		
		$filter_category_id = $this->context->cookie->filter_category_id;		
		$filter_state = $this->context->cookie->filter_state;
		$this->getCategorySelect(0, 0);
		$categories = $this->catselect;
		$tpl = $this->createTemplate('filter.tpl');
		$tpl->assign( array(
			'categories'=>$categories,
			'filter_state'=>$filter_state,
			'link' => $this->context->link,	
			'filter_category_id' => $filter_category_id	
		));		
		return $tpl->fetch();
	}
	
	public function renderPagination()
	{	
		$start = (int)Tools::getValue('start', 0);
		$limit = (int)Tools::getValue('limit', 20);
		$total = $this->getPostCount($this->context->cookie->filter_category_id, $this->context->cookie->filter_state);
		if ($total % $limit)
			$pages = (int)($total / $limit) + 1;
		else		
			$pages = $total / $limit;
		$tpl = $this->createTemplate('pagination.tpl');
		$tpl->assign( array(
			'start'=>$start,
			'limit'=>$limit,
			'pages'=>$pages,
			'total'=>$total,
			'link' => $this->context->link
		));	
		
		return $tpl->fetch();		
	}
	
	public function renderListPost($category_id = 0, $state = -1, $start = 0, $limit = 20)
	{
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');				
		if (!$category_id)
			$category_id = (int)Tools::getValue('filter_category_id', 0);
		$items = $this->getPosts($category_id, $state, $start, $limit);		
		$tpl = $this->createTemplate('listposts.tpl');
		$tpl->assign( array(
			'category_id'=>$category_id,
			'state'=>$state,
			'start'=>$start,
			'limit'=>$limit,
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
				&token='.Tools::getAdminTokenLite('AdminJmsblogPost').'" title="Back to Dashboard"><i class="icon-home"></i>Back to Dashboard</a>';
		$html .= '</div>';
		return $html;
	}
	public function getCategorySelect($parent = 0, $lvl = 0)
	{
		$lvl ++;
		$str = '';
		for	($i = 1; $i <= $lvl; $i++)
			$str .= '- ';
		$this->context = Context::getContext();
		$id_lang = $this->context->language->id;
		$items = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`category_id` as category_id,hssl.`title`
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
				$items[key($items)]['title'] = $str.$items[key($items)]['title'];
				$this->catselect[] = $items[key($items)];
				$this->getCategorySelect($element['category_id'], $lvl);
				next($items);
			}
		}
	}
	public function renderAddPost()
	{
		$this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/admin_style.css', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');
		$category_id	= (int)Tools::getValue('id_category', 0);		
		$this->getCategorySelect(0, 0);
		$categories = $this->catselect;
		array_unshift($categories, array ( 'category_id' => 0,'title' => 'Root Category' ));
		$this->fields_form = array(
			'legend' => array(
					'title' => $this->l('Post informations'),
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
						'label' => $this->l('Category'),
						'name' => 'category_id',
						'desc' => $this->l('Please Select a category'),
						'options' => array('query' => $categories,'id' => 'category_id','name' => 'title')
					),
					array(
						'type' => 'datetime',
						'label' => $this->l('Created'),
						'name' => 'created',
						'desc' => $this->l('Created Time')
					),
					array(
						'type' => 'datetime',
						'label' => $this->l('Modified'),
						'name' => 'modified',
						'desc' => $this->l('Modified Time')
					),	
					array(
						'type' => 'textarea',
						'label' => $this->l('Introtext'),
						'name' => 'introtext',
						'autoload_rte' => true,
						'lang' => true,
					),
					array(
						'type' => 'textarea',
						'label' => $this->l('Fulltext'),
						'name' => 'fulltext',
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
						'type' => 'text',
						'label' => $this->l('Link Video'),
						'name' => 'link_video',
						'desc' => $this->l('Add share link video that you want to show. Example : <iframe title="Victoria Secret" src="//player.vimeo.com/video/43115415"></iframe>.')
					),
					array(
						'type' => 'text',
						'label' => $this->l('Meta Description'),
						'name' => 'meta_desc',
						'lang' => true,
						'desc' => $this->l('An optional paragraph to be used as the description of the page in the HTML output. This will generally display in the results of search engines.')
					),
					array(
						'type' => 'text',
						'label' => $this->l('Meta Keywords'),
						'name' => 'meta_key',
						'lang' => true,
						'desc' => $this->l('An optional comma-separated list of keywords and/or phrases to be used in the HTML output.')
					),
					array(
						'type' => 'text',
						'label' => $this->l('Key Reference'),
						'name' => 'key_ref',
						'lang' => true,
						'desc' => $this->l('Used to store information referring to an external resource .')
					),
					array(
						'type' => 'file_tags',
						'label' => $this->l('Tags'),
						'name' => 'tags',
						'lang' => true,
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
				'name' => 'submitPost'
			)
		);
		
		if (Tools::isSubmit('id_post')) {
			$item = new JmsPost((int)Tools::getValue('id_post'));
			$this->fields_form['input'][] = array('type' => 'hidden', 'name' => 'id_post');								
			$has_picture = false;			
			$this->fields_form['tags'] = $item->tags;
			foreach (Language::getLanguages(false) as $lang)				
				if (isset($item->image[$lang['id_lang']]) && Tools::strlen($item->image[$lang['id_lang']]) > 0)					
					$has_picture = true;				

			if ($has_picture)
				$this->fields_form['input']['has_picture'] = array('type' => 'hidden', 'name' => 'has_picture');	
			if ($has_picture) 				
				$this->fields_form['images'] = $item->image;
			
		}	
		
		$this->fields_value = $this->getPostFieldsValues();		
		$this->tpl_folder = 'jmsblogform/';		
		$this->tpl_form_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),			
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => __PS_BASE_URI__.'/modules/jmsblog/views/img/'
		);		
		return adminController::renderForm();
	}
	public function getPostFieldsValues()
	{
		$fields = array();
		if (Tools::isSubmit('id_post') && $this->postExists((int)Tools::getValue('id_post')))
		{
			$item = new JmsPost((int)Tools::getValue('id_post'));
			$fields['id_post'] 		= (int)Tools::getValue('id_post', $item->id);
			$fields['category_id'] 	= (int)Tools::getValue('category_id', $item->category_id);
			$fields['created'] 		= Tools::getValue('created', $item->created);
			$fields['modified'] 	= Tools::getValue('modified', $item->modified);
		}
		else
			$item = new JmsPost();
		$fields['active'] = Tools::getValue('active', $item->active);
		$fields['category_id'] = (int)Tools::getValue('category_id', $item->category_id);
		$fields['link_video'] = Tools::getValue('link_video', $item->link_video);
		$fields['has_picture'] = true;
		$fields['cat_id_current'] = (int)Tools::getValue('cat_id_current', 0);
		$fields['state'] = (int)Tools::getValue('state', -1);
		$fields['start'] = (int)Tools::getValue('start', 0);
		$fields['limit'] = (int)Tools::getValue('limit', 20);

		$languages = Language::getLanguages(false);	
		foreach ($languages as $lang)
		{
			$fields['image'][$lang['id_lang']]	= Tools::getValue('image_'.(int)$lang['id_lang']);			
			$fields['title'][$lang['id_lang']]	= Tools::getValue('title_'.(int)$lang['id_lang'], $item->title[$lang['id_lang']]);
			$fields['alias'][$lang['id_lang']]	= Tools::getValue('alias_'.(int)$lang['id_lang'], $item->alias[$lang['id_lang']]);			
			$fields['introtext'][$lang['id_lang']] = Tools::getValue('introtext_'.(int)$lang['id_lang'], $item->introtext[$lang['id_lang']]);
			$fields['fulltext'][$lang['id_lang']] = Tools::getValue('fulltext_'.(int)$lang['id_lang'], $item->fulltext[$lang['id_lang']]);			
			$fields['meta_desc'][$lang['id_lang']] = Tools::getValue('meta_desc_'.(int)$lang['id_lang'], $item->meta_desc[$lang['id_lang']]);
			$fields['meta_key'][$lang['id_lang']] = Tools::getValue('meta_key_'.(int)$lang['id_lang'], $item->meta_key[$lang['id_lang']]);
			$fields['key_ref'][$lang['id_lang']] = Tools::getValue('key_ref_'.(int)$lang['id_lang'], $item->key_ref[$lang['id_lang']]);
			$fields['tags'][$lang['id_lang']] = Tools::getValue('tags_'.$lang['id_lang'], $item->tags[$lang['id_lang']]);
		}
		return $fields;
	}

	
	public function headerHTML()
	{
		if (Tools::getValue('controller') != 'AdminJmsblogPost' && Tools::getValue('configure') != $this->name)
			return;		
		$this->context->controller->addJqueryUI('ui.sortable');		
		$this->context->controller->addJqueryPlugin('tagify');
		$html = '<script type="text/javascript">
			$(function() {
				var $posts = $("#posts");
				$posts.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updatePostOrdering";						
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
					},
					stop: function( event, ui ) {
						showSuccessMessage("Saved!");
					}
				});
				$posts.hover(function() {
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