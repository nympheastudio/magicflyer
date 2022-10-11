<?php
/**
 * Tab Example - Controller Admin Example
 *
 * @category   	Module / checkout
 * @author     	PrestaEdit <j.danse@prestaedit.com>
 * @copyright  	2012 PrestaEdit
 * @version   	1.0	
 * @link       	http://www.prestaedit.com/
 * @since      	File available since Release 1.0
*/

class AdminSlideshowImageController extends ModuleAdminController
{
	
	public $listSlideshow;
	public $imgDir;
	
	protected $position_identifier = 'id_opartslideshow_slideshow_image';
	
	public function __construct()
	{
		$this->bootstrap = true;
		$this->table = 'opartslideshow_slideshow_image';
		$this->className = 'MyImage';
		$this->lang = true;
		/*$this->deleted = false;
		$this->colorOnBackground = false;*/
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
		$this->context = Context::getContext();
		$this->imgDir= _PS_ROOT_DIR_.'/modules/opartslideshow/upload/';
		
		parent::__construct();
	}
		
	/**
	 * Function used to render the list to display for this controller
	 */
	public function renderList()
	{
		$this->addRowAction('edit');
		$this->addRowAction('delete');
		$this->_defaultOrderBy = 'position';
		$this->bulk_actions = array();
		
		$this->fields_list = array(
			'id_opartslideshow_slideshow_image' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25
			),
			'name' => array(
				'title' => $this->l('Name'),
				'width' => 'auto',
			),
			'active' => array(
				'title' => $this->l('Displayed'),
				'width' => 'auto',
				'type' => 'bool',
				'active' => 'status',
			),
			'id_opartslideshow_slideshow' => array(
				'title' => $this->l('Slideshow'),
				'width' => 230,
				'callback' => 'displaySlideshowName',
				'callback_object' => $this,
				'type' => 'choice'
			),
			/*'position' => array(
				'title' => $this->l('Position'),
				'width' => 40,
				'filter_key' => 'a!position',
				'align' => 'center',
				'position' => 'position'
			)*/
		);
		
		if(isset($_GET['id_opartslideshow_slideshow']) && is_numeric($_GET['id_opartslideshow_slideshow'])) {
			$this->fields_list['position'] = array(
				'title' => $this->l('Position'),
				'width' => 40,
				'filter_key' => 'a!position',
				'align' => 'center',
				'position' => 'position'
			);
		}
		$content=$header=$this->context->smarty->fetch(parent::getTemplatePath().'header.tpl');
		$slides=$this->getSlideshows();
		
		if(count($slides)>0) {
			$content.='<fieldset><legend>'.$this->l('Filter by slideshow').'</legend>';
			$content.='<div class="help">'.$this->l('Choose a slideshow below to filter images').'</div><br />';
			foreach($slides as $slide) {				
				$selected=(isset($_GET['id_opartslideshow_slideshow']) && $_GET['id_opartslideshow_slideshow']==(int)$slide['key'])?'style="color:red; text-decoration:underline;"':'';
				$href=self::$currentIndex.'&amp;id_opartslideshow_slideshow='.(int)$slide['key'].'&amp;token='.$this->token;
				$content.='<a href="'.$href.'" '.$selected.'>'.$slide['name'].'</a> | ';
			}
			$content.='<br /><br /><a href="index.php?controller=AdminSlideshowImage&token='.$this->token.'">[ '.$this->l('reset filter').' ]</a>';
			$content.='</fieldset><br />';
			$content.=parent::renderList();
			
		}
		else 
			$this->errors[]= Tools::displayError($this->l('You must add one or more slideshow before adding images'));
		
		
	
		
		
		//parent::initToolbar();
		$content.=$this->context->smarty->fetch(parent::getTemplatePath().'help.tpl');
		return $content;
	}
		
	public function displaySlideshowName($value, $tr) {
		$slideshow=new MySlideshow($value);
		return $slideshow->name[$this->context->language->id];
	}

	public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
	{
		
		$order_by = 'position';
	
		parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
		if(isset($_GET['id_opartslideshow_slideshow']) && is_numeric($_GET['id_opartslideshow_slideshow'])) {
			if ($this->_list && is_array($this->_list)) {
				foreach ($this->_list as $key => $list) {							
					if ($list['id_opartslideshow_slideshow'] == $_GET['id_opartslideshow_slideshow']) {						
						$result[]=$list;						
					}
				}
			}
			$this->_list=$result;
		}
		
	}
	
	public function renderForm()
	{
		$this->fields_form = array(
			'tinymce' => true,
			'legend' => array(
				'title' => $this->l('Images'),
				'image' => '../img/admin/cog.gif'
			),
			'input' => array(
				array(
					'type' => 'select',
					'label' => $this->l('Slideshow:'),
					'name' => 'id_opartslideshow_slideshow',
					'cast' => 'intval',
					'options' => array(
						'query' => $this->getSlideshows(),
						'name' => 'name',
						'id' => 'key'
					)
				),
				array(
					'type' => 'file',
					'label' => 'image',
					'name' => 'image_file',
					'display_image' => true
				),
				array(
					'type' => 'text',
					'lang' => true,
					'label' => $this->l('Name:'),
					'name' => 'name',
					'size' => 40
				),
				array(
					'type' => 'text',
					'lang' => true,
					'label' => $this->l('Url:'),
					'name' => 'targeturl',
					'size' => 255
				),								
				array(
					'type' => 'text',
					'lang' => true,
					'label' => $this->l('Description:'),
					'name' => 'description',
					'size' => 255
				),
				array(
					'type' => 'text',
					'label' => $this->l('Filename:'),
					'name' => 'filename',
					'size' => 50,
					'readonly' => true
				),
				array(
					'type' => 'select',
					'label' => $this->l('Statut:'),
					'name' => 'active',						
					'cast' => 'intval', //<- intval ?
					'options' => array(
						'query' => array(
							array('key' => 0, 'name' => $this->l('disable')),
							array('key' => 1, 'name' => $this->l('enable')),
						),
						'name' => 'name',
						'id' => 'key'
					)
				),					
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);

		if (!($obj = $this->loadObject(true)))
			return;
		

		$filename=$obj->filename;
		$image = ImageManager::thumbnail($this->imgDir.$filename, $this->table.'_'.$filename, 350, $this->imageType, false);
		if(file_exists($this->imgDir.$filename)) {
			$this->fields_value['image'] = $image ? $image : false;
			$this->fields_value['size'] = $image ? filesize($this->imgDir.$filename) / 1000 : false;
		}
		return parent::renderForm();
	}
	
	public function getSlideshows()
	{
		$sql='
			SELECT s.id_opartslideshow_slideshow,sl.name FROM '._DB_PREFIX_.'opartslideshow_slideshow s
			LEFT JOIN '._DB_PREFIX_.'opartslideshow_slideshow_lang sl ON (s.id_opartslideshow_slideshow=sl.id_opartslideshow_slideshow)
			WHERE sl.id_lang = '.(int)$this->context->language->id.'
			ORDER BY sl.name
		';
		$choices=array();
		if ($results = Db::getInstance()->ExecuteS($sql))
			foreach ($results as $row)
				$choices[]=array('key' => $row['id_opartslideshow_slideshow'], 'name' => $row['name']);
		
		return $choices;
	}
	
	public function getSlideshow($id) {
		$sql='
		SELECT sl.name,s.active,s.width,s.height FROM '._DB_PREFIX_.'opartslideshow_slideshow s
		LEFT JOIN '._DB_PREFIX_.'opartslideshow_slideshow_lang sl ON (s.id_opartslideshow_slideshow=sl.id_opartslideshow_slideshow)
		WHERE sl.id_lang = '.(int)$this->context->language->id.'
		AND s.id_opartslideshow_slideshow='.$id;
		
		return Db::getInstance()->getRow($sql);		
	}
	/*
	public function ajaxProcessUpdatePositions()
	{
		die('cette fonction ne se lance pas ?');
		if ($this->tabAccess['edit'] === '1')
		{
			$way = (int)(Tools::getValue('way'));
			$id_opartslideshow_slideshow_image = (int)(Tools::getValue('id_opartslideshow_slideshow_image'));
			$id_opartslideshow_slideshow = (int)(Tools::getValue('id_opartslideshow_slideshow'));
			$positions = Tools::getValue('myImage');
	
			if (is_array($positions))
				foreach ($positions as $position => $value)
				{
					$pos = explode('_', $value);
	
					if ((isset($pos[1]) && isset($pos[2])) && ($pos[1] == $id_opartslideshow_slideshow && (int)$pos[2] === $id_opartslideshow_slideshow_image))
					{
						if ($myImage = new MyImage((int)$pos[2]))
							if (isset($position) && $myImage->updatePosition($way, $position))
							echo 'ok position '.(int)$position.' for product '.(int)$pos[2].'\r\n';
						else
							echo '{"hasError" : true, "errors" : "Can not update product '.(int)$id_opartslideshow_slideshow_image.' to position '.(int)$position.' "}';
						else
							echo '{"hasError" : true, "errors" : "This product ('.(int)$id_opartslideshow_slideshow_image.') can t be loaded"}';
	
						break;
					}
				}
		}
	}
*/
	
	
	public function ajaxProcessUpdatePositions()
	{
		$way = (int)(Tools::getValue('way'));
		$id_image = (int)(Tools::getValue('id'));
		$positions = Tools::getValue($this->table);
	
		foreach ($positions as $position => $value)
		{
			$pos = explode('_', $value);
	
			if (isset($pos[2]) && (int)$pos[2] === $id_image)
			{
				if ($myImage = new MyImage((int)$pos[2]))
					if (isset($position) && $myImage->updatePosition($way, $position))
					echo 'ok position '.(int)$position.' for carrier '.(int)$pos[1].'\r\n';
				else
					echo '{"hasError" : true, "errors" : "Can not update carrier '.(int)$id_image.' to position '.(int)$position.' "}';
				else
					echo '{"hasError" : true, "errors" : "This carrier ('.(int)$id_image.') can t be loaded"}';
	
				break;
			}
		}
	}
	
	/*
	public function ajaxProcessUpdatePositions()
	{
		die('ajax');
		
		$id_opartslideshow_slideshow_image = (int)(Tools::getValue('id_opartslideshow_slideshow_image'));
		$id_opartslideshow_slideshow = (int)(Tools::getValue('id_opartslideshow_slideshow'));
		$way = (int)(Tools::getValue('way'));
		$positions = Tools::getValue('category');
		if (is_array($positions))
			foreach ($positions as $key => $value)
			{
				$pos = explode('_', $value);
				if ((isset($pos[1]) && isset($pos[2])) && ($pos[1] == $id_category_parent && $pos[2] == $id_category_to_move))
				{
					$position = $key + 1;
					break;
				}
			}
	
			$category = new Category($id_category_to_move);
			if (Validate::isLoadedObject($category))
			{
				if (isset($position) && $category->updatePosition($way, $position))
				{
					Hook::exec('actionCategoryUpdate');
					die(true);
				}
				else
					die('{"hasError" : true, errors : "Can not update categories position"}');
			}
			else
				die('{"hasError" : true, "errors" : "This category can not be loaded"}');
	}
	*/
	public function processDelete()
	{
		$obj = $this->loadObject(true);
		unlink($this->imgDir.$obj->filename);
	
		return parent::processDelete();
	}
	
	
	public function processPosition()
	{
		if (!Validate::isLoadedObject($object = $this->loadObject()))
		{
			$this->errors[] = Tools::displayError('An error occurred while updating status for object.').
				' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
		}
		elseif (!$object->updatePosition((int)Tools::getValue('way'), (int)Tools::getValue('position')))
			$this->errors[] = Tools::displayError('Failed to update the position.');
		else
		{
			$id_identifier_str = ($id_identifier = (int)Tools::getValue($this->identifier)) ? '&'.$this->identifier.'='.$id_identifier : '';
			$redirect = self::$currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=5'.$id_identifier_str.'&token='.$this->token.'&id_opartslideshow_slideshow='.$object->id_opartslideshow_slideshow;
			$this->redirect_after = $redirect;
		}
		return $object;
	}
	
	public function postProcess()
	{
		if (Tools::isSubmit('submitAdd'.$this->table)) {
			//$newFilename="image_".time().".jpg";
					
			$slideshowInfos=$this->getSlideshow((int)Tools::getValue('id_opartslideshow_slideshow'));
			$obj = new MyImage((int)Tools::getValue('id_opartslideshow_slideshow_image'));
			$errors="";
			
			if (isset($_FILES['image_file']) && is_uploaded_file($_FILES['image_file']['tmp_name'])) {
				
				$filename=Tools::getValue('filename');				
				if (file_exists($this->imgDir.$filename)){
					unlink($this->imgDir.$filename);
					if (file_exists(_PS_ROOT_DIR_."/img/tmp/".$this->table.'_'.$filename))
						unlink(_PS_ROOT_DIR_."/img/tmp/".$this->table.'_'.$filename);
					//on rechange le filename pour eviter la mise en cache de la nouvelle image
					$filename="image_".time().".jpg";
					$changeFilename=true;
				}
				
				$sizes=getimagesize($_FILES['image_file']['tmp_name']);
								
				if ($error = ImageManager::validateUpload($_FILES['image_file'])) 
					$errors .= $error;
								
				elseif (!($tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS')) || !move_uploaded_file($_FILES['image_file']['tmp_name'], $tmpName)) 
					return false;
				
				if($sizes[0]==$slideshowInfos['width'] && $sizes[1]==$slideshowInfos['height']) {
					rename($tmpName,$this->imgDir.$filename);			
					chmod($this->imgDir.$filename,0755);
				}					
				elseif (!ImageManager::resize($tmpName, $this->imgDir.$filename, $slideshowInfos['width'], $slideshowInfos['height'])) 
					$errors .= Tools::displayError($this->l('An error occurred during the image upload.'));
				
				if (file_exists($tmpName))
					unlink($tmpName);

				
				$obj->copyFromPost();
				if(isset($changeFilename) && $changeFilename===true)
					$obj->filename=$filename;								
			}
			else 
				$obj->copyFromPost();

			$obj->save();
		}
		else		
			parent::postProcess();
	}
}