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

class AdminSlideshowController extends ModuleAdminController
{
	public function __construct()
	{
		$this->bootstrap = true;
		$this->table = 'opartslideshow_slideshow';
		$this->className = 'MySlideshow';
		$this->lang = true;
		$this->deleted = false;
		$this->colorOnBackground = false;
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
		
		$this->bulk_actions = array(
			'delete' => array(
				'text' => $this->l('Delete selected'),
				'confirm' => $this->l('Delete selected items?')
				)
			);
		$this->fields_list = array(
			'id_opartslideshow_slideshow' => array(
				'title' => $this->l('ID'),
				'align' => 'center',
				'width' => 25
			),
			'name' => array(
				'title' => $this->l('Name'),
				'width' => 'auto',
			),
			'width' => array(
				'title' => $this->l('Width'),
				'width' => 'auto',
			),
			'height' => array(
				'title' => $this->l('Height'),
				'width' => 'auto',
			),
			'effect' => array(
				'title' => $this->l('Effect'),
				'width' => 'auto',
				'callback' => 'displayEffectName',
				'callback_object' => $this
			),
			'active' => array(
				'title' => $this->l('Displayed'),
				'width' => 'auto',
				'active' => 'status',
				'type' => 'bool',
			),
		);
		
		$lists = parent::renderList();
		
		parent::initToolbar();
		$html=$header=$this->context->smarty->fetch(parent::getTemplatePath().'header.tpl');
		$html.=$lists;
		$html.=$this->context->smarty->fetch(parent::getTemplatePath().'help.tpl');
		return $html;
	}
		
	public function displayEffectName($value, $tr) {
		$effectNames=array("random","swirl", "rain", "straight");		
		return $effectNames[$value];
	}
	
	public function renderForm()
	{
		if (!($obj = $this->loadObject(true)))
			return;
		
		
		$products=Product::getProducts((int)$this->context->language->id,0,0,'id_product','ASC');
		foreach($products as $product) 
			$productList[]=array('key'=>$product['id_product'],'name'=>'('.$product['id_product'].') '.$product['name']);
		
		$categories=Category::getSimpleCategories((int)$this->context->language->id);
		foreach($categories as $category)
			$categoryList[]=array('key'=>$category['id_category'],'name'=>'('.$category['id_category'].') '.$category['name']);
		
		$cmss=CMS::listCms();
		foreach($cmss as $cms)
			$cmsList[]=array('key'=>$cms['id_cms'],'name'=>'('.$cms['id_cms'].') '.$cms['meta_title']);
		
		$this->fields_form = array(
			'tinymce' => true,
			'legend' => array(
				'title' => $this->l('Slideshow'),
				'image' => '../img/admin/cog.gif',				
			),
			'input' => array(
				array(
					'type' => 'text',
					'lang' => true,
					'label' => $this->l('Name:'),
					'name' => 'name',
					'size' => 40					
				),
				array(
					'type' => 'text',
					'label' => $this->l('Width:'),
					'name' => 'width',
					'size' => 10,
					'desc' => $this->l('If you change the width of the it will be necessary to upload images again')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Height:'),
					'name' => 'height',
					'size' => 10,
					'desc' => $this->l('If you change the height of the it will be necessary to upload images again')
				),
					
				array(
					'type' => 'select',
					'label' => $this->l('Effect:'),
					'name' => 'effect',
					'cast' => 'strval',
					'identifier' => 'mode',
					'options' => array(
							'query' => array(
									array('key' => 0, 'name' => $this->l('random')),
									array('key' => 1, 'name' => $this->l('swirl')),
									array('key' => 2, 'name' => $this->l('rain')),
									array('key' => 3, 'name' => $this->l('straight')),
							),
							'name' => 'name',
							'id' => 'key'
					)
				),
				array(
					'type' => 'text',
					'label' => $this->l('Squares per width:'),
					'name' => 'spw',
					'size' => 10,
					'desc' => $this->l('large number can cause transitions problems, Example: 7')
				),	
				array(
					'type' => 'text',
					'label' => $this->l('Squares per height:'),
					'name' => 'sph',
					'size' => 10,
					'desc' => $this->l('large number can cause transitions problems, Example: 5')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Delay:'),
					'name' => 'delay',
					'size' => 10,
					'desc' => $this->l('delay between images in ms, Example: 3000')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Square delay:'),
					'name' => 'sDelay',
					'size' => 10,
					'desc' => $this->l('delay beetwen squares in ms. Example: 30')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Opacity:'),
					'name' => 'opacity',
					'size' => 10,
					'desc' => $this->l('opacity of title and navigation. Example: 0.7')
				),
				array(
					'type' => 'text',
					'label' => $this->l('Title speed:'),
					'name' => 'titleSpeed',
					'size' => 10,
					'desc' => $this->l('speed of title appereance in ms. Example: 500')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Navigation:'),
					'name' => 'navigation',						
						'cast' => 'strval',
						'identifier' => 'mode',
					'options' => array(
						'query' => array(
							array('key' => 0, 'name' => $this->l('no')),
							array('key' => 1, 'name' => $this->l('yes')),
						),
						'name' => 'name',
						'id' => 'key'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Links:'),
					'name' => 'links',
					'desc' => $this->l('Enable links.'),
					'cast' => 'strval',
					'identifier' => 'mode',
					'options' => array(
							'query' => array(
									array('key' => 0, 'name' => $this->l('no')),
									array('key' => 1, 'name' => $this->l('yes')),
							),
							'name' => 'name',
							'id' => 'key'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Hover pause:'),
					'name' => 'hoverpause',
					'desc' => $this->l('pause on hover.'),
					'cast' => 'strval',
					'identifier' => 'mode',
					'options' => array(
							'query' => array(
									array('key' => 0, 'name' => $this->l('no')),
									array('key' => 1, 'name' => $this->l('yes')),
							),
							'name' => 'name',
							'id' => 'key'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Show on home:'),
					'name' => 'home',
					'cast' => 'strval',
					'identifier' => 'mode',
					'options' => array(
						'query' => array(
							array('key' => 0, 'name' => $this->l('no')),
							array('key' => 1, 'name' => $this->l('yes')),
						),
						'name' => 'name',
						'id' => 'key'
					)
				),
				//category
				array(
					'type' => 'select',
					'label' => $this->l('Show on all category:'),
					'name' => 'showOnCat',
					'cast' => 'strval',
					'identifier' => 'mode',
					'options' => array(
						'query' => array(
								array('key' => 0, 'name' => $this->l('no')),
								array('key' => 1, 'name' => $this->l('yes')),
						),
						'name' => 'name',
						'id' => 'key'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Categories:'),
					'name' => 'categories[]',
					'id' => 'categories',
					'options' => array(
						'query' => $categoryList,
						'name' => 'name',
						'id' => 'key'
					),
					'multiple' => true,
					'desc' => $this->l('Choose one or several category s page where the slideshow will be displayed.')
				),
				//product
				array(
					'type' => 'select',
					'label' => $this->l('Show on all product:'),
					'name' => 'showOnProd',
					'cast' => 'strval',
					'identifier' => 'mode',
					'options' => array(
						'query' => array(
								array('key' => 0, 'name' => $this->l('no')),
								array('key' => 1, 'name' => $this->l('yes')),
						),
						'name' => 'name',
						'id' => 'key'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Products:'),
					'name' => 'products[]',
					'id' => 'products',
						'options' => array(
								'query' => $productList,
								'name' => 'name',
								'id' => 'key'
						),
					'multiple' => true,
					'desc' => $this->l('Choose one or several product s page where the slideshow will be displayed.')
				),
				//cms
				array(
					'type' => 'select',
					'label' => $this->l('Show on all cms page:'),
					'name' => 'showOnCms',
					'cast' => 'strval',
					'identifier' => 'mode',
					'options' => array(
						'query' => array(
								array('key' => 0, 'name' => $this->l('no')),
								array('key' => 1, 'name' => $this->l('yes')),
						),
						'name' => 'name',
						'id' => 'key'
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Cms:'),
					'name' => 'cms[]',
					'id' => 'cms',
					'options' => array(
						'query' => $cmsList,
						'name' => 'name',
						'id' => 'key'
					),
					'multiple' => true,
					'desc' => $this->l('Choose one or several cms s page where the slideshow will be displayed.')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Hook:'),
					'name' => 'hook',
					'cast' => 'strval',
					'identifier' => 'mode',
					'options' => array(
							'query' => array(
									array('name' => 'displayTop'),
									array('name' => 'displayTopColumn'),
									array('name' => 'displayHome'),
									array('name' => 'displayLeftColumn'),
									array('name' => 'displayRightColumn'),
									array('name' => 'displayOpartSlideshowHook'),
									array('name' => 'displayFooter')
							),
							'name' => 'name',
							'id' => 'name'
					),
					'desc'=>$this->l('You must hook the module for display your slideshow on each position. Go to modules/positions menu for setup this')
				),
				array(
					'type' => 'select',
					'label' => $this->l('Statut:'),
					'name' => 'active',						
						'cast' => 'strval',
						'identifier' => 'mode',
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
		
		if(is_numeric($obj->id)) {
			//products value
			$sql = 'SELECT id_product FROM '._DB_PREFIX_.'opartslideshow_slideshow_product WHERE id_opartslideshow_slideshow = '.$obj->id;
			$array=Db::getInstance()->executeS($sql);
			if(count($array)) {
				foreach($array as $row)
					$productValues[]=$row['id_product'];
				$this->fields_value['products[]']=$productValues;
			}
			
			//categories value
			$sql = 'SELECT id_category FROM '._DB_PREFIX_.'opartslideshow_slideshow_category WHERE id_opartslideshow_slideshow = '.$obj->id;
			$array=Db::getInstance()->executeS($sql);
			if(count($array)) {
				foreach($array as $row)
					$categoriesValues[]=$row['id_category'];
				$this->fields_value['categories[]']=$categoriesValues;
			}
			
			//cms value
			$sql = 'SELECT id_cms FROM '._DB_PREFIX_.'opartslideshow_slideshow_cms WHERE id_opartslideshow_slideshow = '.$obj->id;
			$array=Db::getInstance()->executeS($sql);
			if(count($array)) {
				foreach($array as $row)
					$cmsValues[]=$row['id_cms'];
				$this->fields_value['cms[]']=$cmsValues;
			}
		}
		
		$html=$header=$this->context->smarty->fetch(parent::getTemplatePath().'header.tpl');
		$html.=parent::renderForm();
		return $html;
	}
	
	public function postProcess()
	{		
		$return=parent::postProcess();
		if($return==false)
			return $return;

		if (Tools::isSubmit('submitAdd'.$this->table)) {				
			
			//products
			Db::getInstance()->delete('opartslideshow_slideshow_product', 'id_opartslideshow_slideshow='.$return->id);
			$products=(Tools::getValue('products'));
			if(count($products)>0 && $products!="") {								
				foreach($products AS $productId) 
					$insertProductArray[]=array('id_opartslideshow_slideshow'=>$return->id,'id_product'=>$productId);
				
				if(!Db::getInstance()->insert('opartslideshow_slideshow_product', $insertProductArray))
					$this->errors[]= Tools::displayError($this->l('An error occurred during hte save of your product\'s pages'));
			}					
			//categories
			Db::getInstance()->delete('opartslideshow_slideshow_category', 'id_opartslideshow_slideshow='.$return->id);
			$categories=(Tools::getValue('categories'));
			if(count($categories)>0 && $categories!="") {				
				foreach($categories AS $categoryId)
					$insertCategoryArray[]=array('id_opartslideshow_slideshow'=>$return->id,'id_category'=>$categoryId);
			
				if(!Db::getInstance()->insert('opartslideshow_slideshow_category', $insertCategoryArray))
					$this->errors[]= Tools::displayError($this->l('An error occurred during hte save of your category\'s pages'));
			}
			//cms
			Db::getInstance()->delete('opartslideshow_slideshow_cms', 'id_opartslideshow_slideshow='.$return->id);
			$cms=(Tools::getValue('cms'));
			if(count($cms)>0 && $cms!="") {
				foreach($cms AS $cmsId)
					$insertCmsArray[]=array('id_opartslideshow_slideshow'=>$return->id,'id_cms'=>$cmsId);
					
				if(!Db::getInstance()->insert('opartslideshow_slideshow_cms', $insertCmsArray))
					$this->errors[]= Tools::displayError($this->l('An error occurred during hte save of your cms\'s pages'));
			}
		}		
		return $return;
	}
	
	public function processDelete()
	{
		$obj = $this->loadObject(true);
		$sql = 'SELECT id_opartslideshow_slideshow_image,filename FROM '._DB_PREFIX_.'opartslideshow_slideshow_image WHERE id_opartslideshow_slideshow = '.$obj->id;
		$array=Db::getInstance()->executeS($sql);
		if(count($array)>0) {
			$where="";
			foreach($array AS $row) {
				$where.=($where=="")?" id_opartslideshow_slideshow_image=".$row['id_opartslideshow_slideshow_image']:" OR id_opartslideshow_slideshow_image=".$row['id_opartslideshow_slideshow_image'];			
				unlink($this->imgDir.$row['filename']);
			}
			//delete imagelang
			Db::getInstance()->delete('opartslideshow_slideshow_image_lang', $where);
			//delete image
			Db::getInstance()->delete('opartslideshow_slideshow_image', 'id_opartslideshow_slideshow='.$obj->id);
		}

		//delete products
		Db::getInstance()->delete('opartslideshow_slideshow_product', 'id_opartslideshow_slideshow='.$obj->id);
		
		//delete categorie
		Db::getInstance()->delete('opartslideshow_slideshow_category', 'id_opartslideshow_slideshow='.$obj->id);
		
		//delete cms
		Db::getInstance()->delete('opartslideshow_slideshow_cms', 'id_opartslideshow_slideshow='.$obj->id);

		return parent::processDelete();
	}

}