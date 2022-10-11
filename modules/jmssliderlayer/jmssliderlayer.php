<?php
/**
* 2007-2014 PrestaShop
*
* Slider Layer module for prestashop
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2014 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

if (!defined('_PS_VERSION_'))
	exit;
include_once(_PS_MODULE_DIR_.'jmssliderlayer/JmsSlide.php');
include_once(_PS_MODULE_DIR_.'jmssliderlayer/JmsLayer.php');
class JmsSliderlayer extends Module
{
	private $_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'jmssliderlayer';
		$this->tab = 'front_office_features';
		$this->version = '1.2.0';		
		$this->author = 'Joommasters';
		$this->module_key = '17e63c9a15553b324dcc11caeb56a7ad';
		$this->need_instance = 0;		
		$this->secure_key = Tools::encrypt($this->name);
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Jms Slider Layer.');
		$this->description = $this->l('Slider layer for prestashop site.');
	}

	public function install()
	{
		if (parent::install() && $this->registerHook('header') && $this->registerHook('actionShopDataDuplication') && $this->installSamples())
		{			
			$res = Configuration::updateValue('JMS_SL_CLASSSUFFIX', '');
			$res &= Configuration::updateValue('JMS_SL_DELAY', 9000);
			$res &= Configuration::updateValue('JMS_SL_STARTHEIGHT', 800);
			$res &= Configuration::updateValue('JMS_SL_STARTWIDTH', 1920);
			$res &= Configuration::updateValue('JMS_SL_NAVIGATION', 1);
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONTYPE', '');
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONARROWS', 'solo');
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONSTYLE', 'round');
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONHALIGN', 'center');
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONVALIGN', 'bottom');
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONHOFFSET', 0);
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONVOFFSET', 20);
			$res &= Configuration::updateValue('JMS_SL_MODE', 'boxed');
			$res &= Configuration::updateValue('JMS_SL_SHADOW', 0);
			$res &= Configuration::updateValue('JMS_SL_SPINNER', 'spinner1');
			$res &= Configuration::updateValue('JMS_SL_ONHOVERSTOP', 0);
			$res &= Configuration::updateValue('JMS_SL_HIDETIMERBAR', 1);
			$res &= Configuration::updateValue('JMS_SL_TIMERBARPOS', 'top');
			$res &= Configuration::updateValue('JMS_SL_TOUCHENABLED', 1);
			$res &= Configuration::updateValue('JMS_SL_IMGRESIZE', 0);
			$res &= Configuration::updateValue('JMS_SL_IMGWIDTH', 1100);
			$res &= Configuration::updateValue('JMS_SL_IMGHEIGHT', 600);
			$res &= Configuration::updateValue('JMS_SL_THUMBWIDTH', 120);
			$res &= Configuration::updateValue('JMS_SL_THUMBHEIGHT', 80);
			$res &= Configuration::updateValue('JMS_SL_GOOGLEFONT', '');			
			return $res;	
		}		
		return false;		
	}
	private function installSamples() 
	{
		$query = '';		
		require_once( dirname(__FILE__).'/install/install.sql.php' );
		$return = true;
		if (isset($query) && !empty($query))
		{
			if (!(Db::getInstance()->ExecuteS( "SHOW TABLES LIKE '"._DB_PREFIX_."jms_sliderlayer'" )))
			{
				$query = str_replace( '_DB_PREFIX_', _DB_PREFIX_, $query );
				$query = str_replace( '_MYSQL_ENGINE_', _MYSQL_ENGINE_, $query );
				$db_data_settings = preg_split("/;\s*[\r\n]+/", $query);
				foreach ($db_data_settings as $query)
				{
					$query = trim($query);
					if (!empty($query))	
					{
						if (!Db::getInstance()->Execute($query))
							$return = false;						
					}
				}
			}
		} 
		else 
			$return = false; 		
		return $return;
	}
	public function uninstall()
	{
		/* Deletes Module */
		if (parent::uninstall())
		{
			/* Deletes tables */
			$res = $this->deleteTables();
			/* Unsets configuration */			
			$res &= Configuration::deleteByName('JMS_SL_CLASSSUFFIX');
			$res &= Configuration::deleteByName('JMS_SL_DELAY');
			$res &= Configuration::deleteByName('JMS_SL_STARTHEIGHT');
			$res &= Configuration::deleteByName('JMS_SL_STARTWIDTH');
			$res &= Configuration::deleteByName('JMS_SL_NAVIGATION');
			$res &= Configuration::deleteByName('JMS_SL_NAVIGATIONTYPE');
			$res &= Configuration::deleteByName('JMS_SL_NAVIGATIONARROWS');
			$res &= Configuration::deleteByName('JMS_SL_NAVIGATIONSTYLE');
			$res &= Configuration::deleteByName('JMS_SL_NAVIGATIONHALIGN');
			$res &= Configuration::deleteByName('JMS_SL_NAVIGATIONVALIGN');
			$res &= Configuration::deleteByName('JMS_SL_NAVIGATIONHOFFSET');
			$res &= Configuration::deleteByName('JMS_SL_NAVIGATIONVOFFSET');
			$res &= Configuration::deleteByName('JMS_SL_MODE');
			$res &= Configuration::deleteByName('JMS_SL_SHADOW');
			$res &= Configuration::deleteByName('JMS_SL_SPINNER');
			$res &= Configuration::deleteByName('JMS_SL_ONHOVERSTOP');
			$res &= Configuration::deleteByName('JMS_SL_HIDETIMERBAR');
			$res &= Configuration::deleteByName('JMS_SL_TIMERBARPOS');
			$res &= Configuration::deleteByName('JMS_SL_TOUCHENABLED');						
			$res &= Configuration::deleteByName('JMS_SL_BGIMG');
			$res &= Configuration::deleteByName('JMS_SL_BGCOLOR');
			$res &= Configuration::deleteByName('JMS_SL_IMGRESIZE');
			$res &= Configuration::deleteByName('JMS_SL_IMGWIDTH');
			$res &= Configuration::deleteByName('JMS_SL_IMGHEIGHT');
			$res &= Configuration::deleteByName('JMS_SL_THUMBWIDTH');			
			$res &= Configuration::deleteByName('JMS_SL_THUMBHEIGHT');
			$res &= Configuration::deleteByName('JMS_SL_GOOGLEFONT');
			return $res;
		}
		return false;
	}

	/**
	 * deletes tables
	 */
	protected function deleteTables()
	{	
		Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'jms_sliderlayer`;');
		Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'jms_sliderlayer_slides`;');
		Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'jms_sliderlayer_slides_lang`;');
		Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'jms_sliderlayer_layers`;');
		return true;
	}
	
	public function getContent()
	{
		$this->_html .= $this->headerHTML();

		/* Validate & process */
		if (Tools::isSubmit('submitSlide') || Tools::isSubmit('delete_id_slide') || Tools::isSubmit('submitLayer') || Tools::isSubmit('delete_id_layer') || Tools::isSubmit('submitSlider') || Tools::isSubmit('submitLayers') || Tools::isSubmit('changeStatus') || Tools::isSubmit('copySlide') || Tools::isSubmit('changeLayerStatus'))
		{			
			if ($this->_postValidation())
			{
				$this->_postProcess();				
				$this->_html .= $this->renderList();
				$this->_html .= $this->renderForm();
			}
			elseif (Tools::isSubmit('submitLayer')) 
				$this->_html .= $this->renderLayersList((int)Tools::getValue('id_slide'));
			elseif (Tools::isSubmit('submitLayers')) 
				$this->_html .= $this->renderLayersList((int)Tools::getValue('id_slide'));			
			else 
				$this->_html .= $this->renderAddForm();			

			$this->clearCache();
		}
		elseif (Tools::isSubmit('addSlide') || Tools::isSubmit('editSlide') && (Tools::isSubmit('id_slide') && $this->slideExists((int)Tools::getValue('id_slide'))))
			$this->_html .= $this->renderAddForm();
		elseif (Tools::isSubmit('layers') && (Tools::isSubmit('id_slide') && $this->slideExists((int)Tools::getValue('id_slide'))))
			$this->_html .= $this->renderLayersList((int)Tools::getValue('id_slide'));			
		else
		{			
			$this->_html .= $this->renderList();
			$this->_html .= $this->renderForm();
		}

		return $this->_html;
	}
	
	private function _postValidation()
	{
		$errors = array();

		/* Validation for Slider configuration */
		if (Tools::isSubmit('changeStatus'))
		{
			if (!Validate::isInt(Tools::getValue('id_slide')))
				$errors[] = $this->l('Invalid slide');
		}
		elseif (Tools::isSubmit('changeLayerStatus'))
		{			
			if (!Validate::isInt(Tools::getValue('id_layer')))
				$errors[] = $this->l('Invalid layer');
		}
		/* Validation for Slide */
		elseif (Tools::isSubmit('submitSlide'))
		{			
			/* Checks position */
			if (!Validate::isInt(Tools::getValue('ordering')) || (Tools::getValue('ordering') < 0))
				$errors[] = $this->l('Invalid slide ordering');
			/* If edit : checks id_slide */
			if (Tools::isSubmit('slide_id'))
			{					
				if (!Validate::isInt(Tools::getValue('slide_id')) && !$this->slideExists(Tools::getValue('slide_id')))
					$errors[] = $this->l('Invalid id_slide');
			}
			
			if (Tools::strlen(Tools::getValue('title')) == 0)
				$errors[] = $this->l('The title is not set.');
		} /* Validation for Layer */
		elseif (Tools::isSubmit('submitLayer'))
		{			
			/* Checks position */			
			if (!Validate::isInt(Tools::getValue('slide_id')) || (Tools::getValue('slide_id') <= 0))
				$errors[] = $this->l('Invalid slide id');
			/* If edit : checks id_slide */	
			if (Tools::isSubmit('layer_id'))
			{					
				if (!Validate::isInt(Tools::getValue('layer_id')) && !$this->slideExists(Tools::getValue('layer_id')))
					$errors[] = $this->l('Invalid id_layer');
			}			
			
			if (Tools::strlen(Tools::getValue('title')) == 0)
				$errors[] = $this->l('The layer title is not set.');	
		} /* Validation for deletion */		
		elseif (Tools::isSubmit('delete_id_slide') && (!Validate::isInt(Tools::getValue('delete_id_slide')) || !$this->slideExists((int)Tools::getValue('delete_id_slide'))))
			$errors[] = $this->l('Invalid id_slide');
		elseif (Tools::isSubmit('copySlide') && (!Validate::isInt(Tools::getValue('id_slide')) || !$this->slideExists((int)Tools::getValue('id_slide'))))
			$errors[] = $this->l('Invalid id_slide');
		/* Display errors if needed */
		if (count($errors))
		{
			$this->_html .= $this->displayError(implode('<br />', $errors));
			return false;
		}

		/* Returns if validation is ok */
		return true;
	}
	private function _postProcess()
	{
		$errors = array();

		/* Processes Slider */
		if (Tools::isSubmit('submitSlider'))
		{
			$res = Configuration::updateValue('JMS_SL_CLASSSUFFIX', Tools::getValue('JMS_SL_CLASSSUFFIX'));
			$res = Configuration::updateValue('JMS_SL_DELAY', (int)(Tools::getValue('JMS_SL_DELAY')));
			$res &= Configuration::updateValue('JMS_SL_STARTHEIGHT', (int)(Tools::getValue('JMS_SL_STARTHEIGHT')));
			$res &= Configuration::updateValue('JMS_SL_STARTWIDTH', (int)(Tools::getValue('JMS_SL_STARTWIDTH')));
			$res &= Configuration::updateValue('JMS_SL_NAVIGATION', (int)(Tools::getValue('JMS_SL_NAVIGATION')));
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONTYPE', Tools::getValue('JMS_SL_NAVIGATIONTYPE'));
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONARROWS', Tools::getValue('JMS_SL_NAVIGATIONARROWS'));
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONSTYLE', Tools::getValue('JMS_SL_NAVIGATIONSTYLE'));
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONHALIGN', Tools::getValue('JMS_SL_NAVIGATIONHALIGN'));
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONVALIGN', Tools::getValue('JMS_SL_NAVIGATIONVALIGN'));
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONHOFFSET', Tools::getValue('JMS_SL_NAVIGATIONHOFFSET'));
			$res &= Configuration::updateValue('JMS_SL_NAVIGATIONVOFFSET', Tools::getValue('JMS_SL_NAVIGATIONVOFFSET'));
			$res &= Configuration::updateValue('JMS_SL_MODE', Tools::getValue('JMS_SL_MODE'));
			$res &= Configuration::updateValue('JMS_SL_SHADOW', (int)(Tools::getValue('JMS_SL_SHADOW')));
			$res &= Configuration::updateValue('JMS_SL_SPINNER', Tools::getValue('JMS_SL_SPINNER'));
			$res &= Configuration::updateValue('JMS_SL_ONHOVERSTOP', (int)(Tools::getValue('JMS_SL_ONHOVERSTOP')));
			$res = Configuration::updateValue('JMS_SL_HIDETIMERBAR', (int)(Tools::getValue('JMS_SL_HIDETIMERBAR')));
			$res = Configuration::updateValue('JMS_SL_TIMERBARPOS', Tools::getValue('JMS_SL_TIMERBARPOS'));
			$res &= Configuration::updateValue('JMS_SL_TOUCHENABLED', (int)(Tools::getValue('JMS_SL_TOUCHENABLED')));
			$res &= Configuration::updateValue('JMS_SL_IMGRESIZE', (int)(Tools::getValue('JMS_SL_IMGRESIZE')));
			$res &= Configuration::updateValue('JMS_SL_IMGWIDTH', (int)(Tools::getValue('JMS_SL_IMGWIDTH')));
			$res &= Configuration::updateValue('JMS_SL_IMGHEIGHT', (int)(Tools::getValue('JMS_SL_IMGHEIGHT')));
			$res &= Configuration::updateValue('JMS_SL_THUMBWIDTH', (int)(Tools::getValue('JMS_SL_THUMBWIDTH')));
			$res &= Configuration::updateValue('JMS_SL_THUMBHEIGHT', (int)(Tools::getValue('JMS_SL_THUMBHEIGHT')));
			$res &= Configuration::updateValue('JMS_SL_GOOGLEFONT', Tools::getValue('JMS_SL_GOOGLEFONT'));			
			$this->clearCache();			
			if (!$res)
				$errors[] = $this->displayError($this->l('The configuration could not be updated.'));
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=6&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		} /* Process Slide status */
		elseif (Tools::isSubmit('changeStatus') && Tools::isSubmit('id_slide'))
		{
			$slide = new JmsSlide((int)Tools::getValue('id_slide'));
			if ($slide->active == 0)
				$slide->active = 1;
			else
				$slide->active = 0;
			$res = $slide->update();
			$this->clearCache();
			$this->_html .= ($res ? $this->displayConfirmation($this->l('Status changed')) : $this->displayError($this->l('The Status could not be updated.')));
		}  /* Process Layer status */
		elseif (Tools::isSubmit('changeLayerStatus') && Tools::isSubmit('id_layer'))
		{				
			$layer = new JmsLayer((int)Tools::getValue('id_layer'));
			if ($layer->active == 0)
				$layer->active = 1;
			else
				$layer->active = 0;
			$res = $layer->update();
			$this->clearCache();
			$this->_html .= ($res ? $this->displayConfirmation($this->l('Layer Status changed')) : $this->displayError($this->l('Layer Status could not be updated.')));
		} /* Processes Slide */
		elseif (Tools::isSubmit('submitSlide'))
		{
			
			/* Sets ID if needed */			
			if (Tools::getValue('id_slide'))
			{					
				$slide = new JmsSlide((int)Tools::getValue('id_slide'));
				if (!Validate::isLoadedObject($slide))
				{
					$this->_html .= $this->displayError($this->l('Invalid id_slide'));
					return;
				}				
			}
			else
				$slide = new JmsSlide();
			/* Sets ordering */
			$slide->ordering 		= (int)Tools::getValue('ordering');						
			$slide->title 			= Tools::getValue('title');
			$slide->transition 		= Tools::getValue('transition');
			$slide->slotamount 		= (int)Tools::getValue('slotamount');
			$slide->masterspeed 	= (int)Tools::getValue('masterspeed');
			$slide->delay 			= (int)Tools::getValue('delay');
			$slide->link 			= Tools::getValue('link');
			$slide->target 			= Tools::getValue('target');
			$slide->bg_type 		= (int)Tools::getValue('bg_type');
			$slide->main_img 		= Tools::getValue('main_img');
			$slide->bg_color 		= Tools::getValue('bg_color');
			$slide->thumb_img 		= Tools::getValue('thumb_img');
			$slide->kenburns 		= (int)Tools::getValue('kenburns');
			$slide->duration 		= (int)Tools::getValue('duration');
			$slide->ease 			= Tools::getValue('ease');
			$slide->bgrepeat 		= Tools::getValue('bgrepeat');
			if ($slide->kenburns)
				$slide->bgfit 			= Tools::getValue('bgfit_kenburns');
			else 	
				$slide->bgfit 			= Tools::getValue('bgfit');
			$slide->bgfitend		= Tools::getValue('bgfitend');
			$slide->bgposition 		= Tools::getValue('bgposition');			
			$slide->bgpositionend	= Tools::getValue('bgpositionend');
			/* Sets active */
			$slide->active = (int)Tools::getValue('active');			
			/* Uploads image*/
			$type = Tools::strtolower(Tools::substr(strrchr($_FILES['main_img']['name'], '.'), 1));
			$imagesize = array();
			$imagesize = @getimagesize($_FILES['main_img']['tmp_name']);			
			if (isset($_FILES['main_img']) && isset($_FILES['main_img']['tmp_name']) && !empty($_FILES['main_img']['tmp_name']) && !empty($imagesize) && in_array(Tools::strtolower(Tools::substr(strrchr($imagesize['mime'], '/'), 1)), array('jpg', 'gif', 'jpeg', 'png')) && in_array($type, array('jpg', 'gif', 'jpeg', 'png')))
			{
					$temp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');					
					$salt = sha1(microtime());
					$new_name = Tools::encrypt($_FILES['main_image']['name'].$salt);
					if ($error = ImageManager::validateUpload($_FILES['main_img']))
						$errors[] = $error;
					elseif (!$temp_name || !move_uploaded_file($_FILES['main_img']['tmp_name'], $temp_name))
						return false;
					elseif (!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/'.$new_name.'.'.$type, null, null, $type))
						$errors[] = $this->displayError($this->l('An error occurred during the image upload process.'));						
					
					$slide->main_img = $new_name.'.'.$type;
					if (!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/resized_'.$new_name.'.'.$type, Configuration::get('JMS_SL_IMGWIDTH'), Configuration::get('JMS_SL_IMGHEIGHT'), $type)) 
						$errors[] = $this->displayError($this->l('An error occurred during the image resize process.'));
					if (!ImageManager::resize($temp_name, dirname(__FILE__).'/views/img/thumb_'.$new_name.'.'.$type, Configuration::get('JMS_SL_THUMBWIDTH'), Configuration::get('JMS_SL_THUMBHEIGHT'), $type)) 
						$errors[] = $this->displayError($this->l('An error occurred during the image resize process.'));					
					if (isset($temp_name))
						@unlink($temp_name);
					//delete old img
					$old_img = Tools::getValue('image_old');
					if ($old_img && file_exists(dirname(__FILE__).'/views/img/'.$old_img))
						@unlink(dirname(__FILE__).'/views/img/'.$old_img);
						@unlink(dirname(__FILE__).'/views/img/resized_'.$old_img);																
						@unlink(dirname(__FILE__).'/views/img/thumb_'.$old_img);												
			}
			elseif (Tools::getValue('image_old') != '')
				$slide->main_img = Tools::getValue('image_old');												
			
			/* Processes if no errors  */			
			if (!$errors)
			{
				
				/* Adds */
				if (!Tools::getValue('id_slide'))
				{					
					if (!$slide->add())
						$errors[] = $this->displayError($this->l('The slide could not be added.'));					
					$id_lang = (int)Tools::getValue('id_lang');				
					$res = Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'jms_sliderlayer_slides_lang`(`slide_id`,`id_lang`) VALUES('.$slide->id.','.$id_lang.')'
					);		
				}
				/* Update */
				else 
				{
					if (!$slide->update())
						$errors[] = $this->displayError($this->l('The slide could not be updated.'));
					
					$id_lang = (int)Tools::getValue('id_lang');					
					$res = Db::getInstance()->execute('
						UPDATE `'._DB_PREFIX_.'jms_sliderlayer_slides_lang` SET `id_lang` = '.$id_lang.' WHERE `slide_id` = '.$slide->id
					);		
				}	
				$this->clearCache();
				$slide_id_back = $slide->id;
			}
		} /* submit layer */
		elseif (Tools::isSubmit('copySlide')) 
		{
			$id_shop = $this->context->shop->id;
			$slideobj = new JmsSlide((int)Tools::getValue('id_slide'));
			$slideduplicated = $slideobj->duplicateObject();
			$slideduplicated->title = $slideduplicated->title.' - Copy';
			$slideduplicated->active = 0;
			if (!$slideduplicated->update())
					$errors[] = $this->displayError($this->l('The duplicated slide cant update.'));
			$res = Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'jms_sliderlayer`(`slide_id`,`id_shop`) VALUES('.$slideduplicated->id.','.$id_shop.')'
					);
			$res = Db::getInstance()->execute('
						INSERT INTO `'._DB_PREFIX_.'jms_sliderlayer_slides_lang`(`slide_id`,`id_lang`) VALUES('.$slideduplicated->id.','.$this->getSlideLang($slideobj->id).')'
					);	
			$layers = $this->getLayers(Tools::getValue('id_slide'));			
			foreach ($layers as $layer) 
			{				
				$layerobj = new JmsLayer((int)$layer['layer_id']);
				$layerduplicated = $layerobj->duplicateObject();	
				$res = Db::getInstance()->execute('
					UPDATE `'._DB_PREFIX_.'jms_sliderlayer_layers` SET `slide_id` = '.$slideduplicated->id.' WHERE `layer_id` = '.$layerduplicated->id
				);	
			}		
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
		} /* submit layer */
		elseif (Tools::isSubmit('submitLayer'))
		{
			
			/* Sets ID if needed */			
			if (Tools::getValue('id_layer'))
			{					
				$layer = new JmsLayer((int)Tools::getValue('id_layer'));
				if (!Validate::isLoadedObject($layer))
				{
					$this->_html .= $this->displayError($this->l('Invalid id_layer'));
					return;
				}				
			}
			else
				$layer = new JmsLayer();
			/* Sets ordering */
			$layer->slide_id 		= (int)Tools::getValue('slide_id');						
			$layer->title 			= Tools::getValue('title');
			$layer->layer_class 	= Tools::getValue('layer_class');
			$layer->parallax_class 	= Tools::getValue('parallax_class');
			$layer->data_x 			= Tools::getValue('data_x');
			$layer->data_y 			= Tools::getValue('data_y');
			$layer->speed 			= (int)Tools::getValue('speed');
			$layer->start 			= (int)Tools::getValue('start');			
			$layer->easing 			= Tools::getValue('easing');
			$layer->endspeed 		= (int)Tools::getValue('endspeed');
			$layer->end 			= (int)Tools::getValue('end');
			$layer->endeasing 		= Tools::getValue('endeasing');
			$layer->incoming_class 	= Tools::getValue('incoming_class');
			$layer->outgoing_class 	= Tools::getValue('outgoing_class');						
			$layer->special_class 	= Tools::getValue('special_class');
			$layer->customin 		= Tools::getValue('customin');
			$layer->customout 		= Tools::getValue('customout');
			$layer->splitin 		= Tools::getValue('splitin');
			$layer->splitout 		= Tools::getValue('splitout');
			$layer->elementdelay 	= (float)Tools::getValue('elementdelay');
			$layer->endelementdelay = (float)Tools::getValue('endelementdelay');
			$layer->linktoslide 	= Tools::getValue('linktoslide');
			$layer->data_type 		= Tools::getValue('data_type');
			$layer->layer_img 		= Tools::getValue('layer_img');
			$layer->img_ww 			= (int)Tools::getValue('img_ww');
			$layer->img_hh 			= (int)Tools::getValue('img_hh');
			$layer->layer_video 	= Tools::getValue('layer_video');
			$layer->video_width 	= (int)Tools::getValue('video_width');
			$layer->video_height 	= (int)Tools::getValue('video_height');
			$layer->video_fullscreen = (int)Tools::getValue('video_fullscreen');
			$layer->video_autoplay 	= (int)Tools::getValue('video_autoplay');
			$layer->layer_text 		= Tools::getValue('layer_text');
			/* Sets active */
			$layer->active = (int)Tools::getValue('active');			
			
			if (!$errors)
			{	
				/* Adds */
				if (!Tools::getValue('id_layer'))
				{
					//print_r($layer);	exit;
					if (!$layer->add())
						$errors[] = $this->displayError($this->l('The layer could not be added.'));
				}
				/* Update */
				elseif (!$layer->update())
					$errors[] = $this->displayError($this->l('The layer could not be updated.'));
				$this->clearCache();
			}
		} //save layers
		elseif (Tools::isSubmit('submitLayers')) 
		{
			$layer_ids = Tools::getValue('layer_ids');	
			$total_layers = count($layer_ids);			
			for ($i = 0; $i < $total_layers; $i++) 
			{
				$layer_id = $layer_ids[$i];
				$layer = new JmsLayer((int)$layer_ids[$i]);
				if (!Validate::isLoadedObject($layer))
				{
					$this->_html .= $this->displayError($this->l('Invalid id_layer'));
					return;
				}				
				$layer->title 			= Tools::getValue('title_'.$layer_id);
				$layer->layer_class 	= Tools::getValue('layer_class_'.$layer_id);				
				$layer->data_x 			= Tools::getValue('data_x_'.$layer_id);
				$layer->data_y 			= Tools::getValue('data_y_'.$layer_id);
				$layer->speed 			= (int)Tools::getValue('speed_'.$layer_id);				
				$layer->easing 			= Tools::getValue('easing_'.$layer_id);				
				$layer->incoming_class 	= Tools::getValue('incoming_class_'.$layer_id);
				$layer->outgoing_class 	= Tools::getValue('outgoing_class_'.$layer_id);
				$layer->start 			= Tools::getValue('start_'.$layer_id);
				$layer->end 			= Tools::getValue('end_'.$layer_id);
				$layer->data_type 		= Tools::getValue('data_type_'.$layer_id);
				if ($layer->data_type == 'text') 
				{
					$layer->layer_text 		= Tools::getValue('layer_text_'.$layer_id);
					$layer->splitin 		= Tools::getValue('splitin_'.$layer_id);
					$layer->splitout 		= Tools::getValue('splitout_'.$layer_id);
				} 
				else if ($layer->data_type == 'img') 
					$layer->layer_img 		= Tools::getValue('layer_img_'.$layer_id);								
				else 
				{	
					$layer->data_type		= Tools::getValue('video_source_'.$layer_id);
					$layer->layer_video 	= Tools::getValue('layer_video_'.$layer_id);
					$layer->video_width 	= (int)Tools::getValue('video_width_'.$layer_id);
					$layer->video_height 	= (int)Tools::getValue('video_height_'.$layer_id);
					$layer->video_fullscreen = (int)Tools::getValue('video_fullscreen_'.$layer_id);
					$layer->video_autoplay 	= (int)Tools::getValue('video_autoplay_'.$layer_id);
				}	
				
				if (!$layer->update())
					$errors[] = $this->displayError($this->l('The layer could not be updated.'));
				$this->clearCache();
			}	
		} /* Deletes */

		elseif (Tools::isSubmit('delete_id_slide'))
		{
			$slide = new JmsSlide((int)Tools::getValue('delete_id_slide'));
			$res = $slide->delete();
			$this->clearCache();
			if (!$res)
				$this->_html .= $this->displayError('Could not delete');
			else 
			{
				Db::getInstance()->execute('
						DELETE FROM `'._DB_PREFIX_.'jms_sliderlayer_slides_lang` WHERE `slide_id` = '.Tools::getValue('delete_id_slide')
					);						
				Db::getInstance()->execute('
						DELETE FROM `'._DB_PREFIX_.'jms_sliderlayer_layers` WHERE `slide_id` = '.Tools::getValue('delete_id_slide')
					);							
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name);
			}	
		}
		elseif (Tools::isSubmit('delete_id_layer') && Tools::getValue('id_slide')) 
		{
			$layer = new JmsLayer((int)Tools::getValue('delete_id_layer'));
			$res = $layer->delete();
			$this->clearCache();
			if (!$res)
				$this->_html .= $this->displayError('Could not delete');
			else
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=1&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&layers=1&id_slide='.Tools::getValue('id_slide'));
		}
		/* Display errors if needed */
		if (count($errors))
			$this->_html .= $this->displayError(implode('<br />', $errors));
		elseif (Tools::isSubmit('submitSlider') && Tools::getValue('id_slide'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=4&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&editSlide=1&id_slide='.$slide_id_back);
		elseif (Tools::isSubmit('submitSlide'))
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=3&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&editSlide=1&id_slide='.$slide_id_back);
		elseif (Tools::isSubmit('submitLayers'))			
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&conf=3&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&layers=1&id_slide='.Tools::getValue('slide_id'));
		elseif (Tools::isSubmit('changeLayerStatus'))		
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&layers=1&id_slide='.Tools::getValue('id_slide'));
	}
	public function clearCache()
	{
		$this->_clearCache('jmssliderlayer.tpl');
	}
	public function hookActionShopDataDuplication($params)
	{
		Db::getInstance()->execute('
		INSERT IGNORE INTO '._DB_PREFIX_.'jms_sliderlayer (slide_id, id_shop)
		SELECT slide_id, '.(int)$params['new_id_shop'].'
		FROM '._DB_PREFIX_.'jms_sliderlayer
		WHERE id_shop = '.(int)$params['old_id_shop']);
		$this->clearCache();
	}
	public function headerHTML()
	{
		if (Tools::getValue('controller') != 'AdminModules' && Tools::getValue('configure') != $this->name)
			return;

		$this->context->controller->addJqueryUI('ui.sortable');
		/* Style & js for fieldset 'slides configuration' */
		$html = '<script type="text/javascript">
			$(function() {
				var $mySlides = $("#slides");
				$mySlides.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updateSlidesOrdering";						
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);
						}
					});
				$mySlides.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
			});
			$(function() {
				var $myLayers = $("#layers");
				$myLayers.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&action=updateLayersOrdering";						
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax_'.$this->name.'.php?secure_key='.$this->secure_key.'", order);		
						var temp = $(this).sortable("serialize").substring(9,200);	
						var layerids = temp.split("&layers[]=");
						var i = 1;
						jQuery.each(layerids, function(index, value) {
							$("#caption_" + value).css("z-index",i);
							i++;
						});
					}
				});
				$myLayers.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
			});
		</script>';
		
		return $html;
	}
	
	public function getNextPosition()
	{
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT MAX(hss.`position`) AS `next_position`
			FROM `'._DB_PREFIX_.'homeslider_slides` hss, `'._DB_PREFIX_.'homeslider` hs
			WHERE hss.`id_homeslider_slides` = hs.`id_homeslider_slides` AND hs.`id_shop` = '.(int)$this->context->shop->id
		);

		return (++$row['next_position']);
	}

	public function getSlides($active = null)
	{
		$this->context = Context::getContext();
		$id_shop = $this->context->shop->id;			
		$slides = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.`slide_id` as id_slide, hss.`ordering`, hss.`active`, hss.`title`, hss.`link`, hssl.`id_lang`
			FROM '._DB_PREFIX_.'jms_sliderlayer hs
			LEFT JOIN '._DB_PREFIX_.'jms_sliderlayer_slides hss ON (hs.slide_id = hss.slide_id)
			LEFT JOIN '._DB_PREFIX_.'jms_sliderlayer_slides_lang hssl ON (hs.slide_id = hssl.slide_id)			
			WHERE id_shop = '.(int)$id_shop.
			($active ? ' AND hss.`active` = 1' : ' ').'
			ORDER BY hss.ordering'
		);
		$i = 0;
		foreach ($slides as $slide) 		
			$slides[$i]['iso_lang'] = Language::getIsoById($slide['id_lang']);			
		return $slides;
	}
	public function getCurrentSlide($id_slide)
	{
		$this->context = Context::getContext();
		$id_shop = $this->context->shop->id;
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT hs.`slide_id` as id_slide, hss.`title`, hss.`main_img`, hss.`bgfit`, hss.`bgrepeat`, hss.`bgposition`,hss.`bg_type`,hss.`bg_color`
			FROM '._DB_PREFIX_.'jms_sliderlayer hs
			LEFT JOIN '._DB_PREFIX_.'jms_sliderlayer_slides hss ON (hs.slide_id = hss.slide_id)			
			WHERE id_shop = '.(int)$id_shop.
			' AND hss.slide_id = '.$id_slide
		);
	}
	public function getLayers($id_slide, $active = null)
	{
		$this->context = Context::getContext();		
			
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.*
			FROM '._DB_PREFIX_.'jms_sliderlayer_layers hs						
			WHERE hs.slide_id = '.(int)$id_slide.
			($active ? ' AND hs.`active` = 1' : ' ').'
			ORDER BY hs.ordering ASC,hs.layer_id ASC'
		);
	}
	public function getLayerImgs() 
	{
		$dir = _PS_MODULE_DIR_.'jmssliderlayer/views/img/layers/';
		//get all image files with a .jpg extension.
		$files = glob($dir.'*.{jpg,png,gif}', GLOB_BRACE);
		$images = array();
		$i = 0;
		foreach ($files as $img)
		{
			$images[$i]['id'] = Tools::substr($img, Tools::strlen($dir), Tools::strlen($img) - Tools::strlen($dir));
			$i++;
		}	
		return $images;
	}
	public function getThumbImgs() 
	{
		$dir = _PS_MODULE_DIR_.'jmssliderlayer/views/img/thumbs/';
		//get all image files with a .jpg extension.		
		$files = glob($dir.'*.{jpg,png,gif}', GLOB_BRACE);
		$images = array();
		$images[0]['id'] = '';
		$images[0]['name'] = 'Default';
		$i = 1;		
		foreach ($files as $img) 
		{
			$img_name = Tools::substr($img, Tools::strlen($dir), Tools::strlen($img) - Tools::strlen($dir));
			if (Tools::strlen($img_name) > 0)
			{
				$images[$i]['id'] = $img_name;
				$images[$i]['name'] = $img_name;				
				$i++;
			}		
		}		
		return $images;
	}
	public function displayStatus($id_slide, $active)
	{
		$title = ((int)$active == 0 ? $this->l('Disabled') : $this->l('Enabled'));
		$icon = ((int)$active == 0 ? 'icon-remove' : 'icon-check');
		$class = ((int)$active == 0 ? 'btn-danger' : 'btn-success');
		$html = '<a class="btn '.$class.'" href="'.AdminController::$currentIndex.
			'&configure='.$this->name.'
				&token='.Tools::getAdminTokenLite('AdminModules').'
				&changeStatus&id_slide='.(int)$id_slide.'" title="'.$title.'"><i class="'.$icon.'"></i> '.$title.'</a>';

		return $html;
	}

	public function displayLayerStatus($id_layer, $active, $id_slide)
	{
		$title = ((int)$active == 0 ? $this->l('Disabled') : $this->l('Enabled'));
		$icon = ((int)$active == 0 ? 'icon-remove' : 'icon-check');
		$class = ((int)$active == 0 ? 'btn-danger' : 'btn-success');
		$html = '<a class="btn '.$class.'" href="'.AdminController::$currentIndex.
			'&configure='.$this->name.'
				&token='.Tools::getAdminTokenLite('AdminModules').'
				&changeLayerStatus&id_layer='.(int)$id_layer.'&id_slide='.$id_slide.'" title="'.$title.'"><i class="'.$icon.'"></i> '.$title.'</a>';

		return $html;
	}
		
	public function slideExists($id_slide)
	{
		$req = 'SELECT hs.`slide_id` as id_slide
				FROM `'._DB_PREFIX_.'jms_sliderlayer` hs
				WHERE hs.`slide_id` = '.(int)$id_slide;
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);

		return ($row);
	}

	public function layerExists($id_layer)
	{
		$req = 'SELECT hs.`layer_id` as id_layer
				FROM `'._DB_PREFIX_.'jms_sliderlayer_layers` hs
				WHERE hs.`layer_id` = '.(int)$id_layer;
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);

		return ($row);
	}
		
	public function renderList()
	{
		$this->context->controller->addCSS(($this->_path).'views/css/admin_style.css', 'all');
		$slides = $this->getSlides();				
		foreach ($slides as $key => $slide)
			$slides[$key]['status'] = $this->displayStatus($slide['id_slide'], $slide['active']);

		$this->context->smarty->assign(
			array(
				'link' => $this->context->link,
				'slides' => $slides
			)
		);

		return $this->display(__FILE__, 'list.tpl');
	}
	
	public function renderLayersList($id_slide)
	{
		$this->context->controller->addCSS(($this->_path).'views/css/admin_style.css', 'all');
		$this->context->controller->addCSS(($this->_path).'views/css/settings.css', 'all');
		$this->context->controller->addCSS(($this->_path).'views/css/extralayers.css', 'all');
		$this->context->controller->addJS(($this->_path).'views/js/admin_script.js', 'all');
		$this->context->controller->addJqueryUI('ui.draggable');
		$this->context->controller->addJqueryUI('ui.dialog');
		$layers = $this->getLayers($id_slide);				
		$current_slide = $this->getCurrentSlide($id_slide);		
		$caption_cls = array(
			0 => array('id' => ''),
			1 => array('id' => 'medium_grey'),
			2 => array('id' => 'small_text'),
			3 => array('id' => 'medium_text'),
			4 => array('id' => 'large_text'),
			5 => array('id' => 'very_large_text'),
			6 => array('id' => 'very_big_white'),
			7 => array('id' => 'very_big_black'),
			8 => array('id' => 'modern_medium_fat'),
			9 => array('id' => 'modern_medium_fat_white'),
			10 => array('id' => 'modern_medium_light'),
			11 => array('id' => 'modern_big_bluebg'),
			12 => array('id' => 'modern_big_redbg'),
			13 => array('id' => 'modern_small_text_dark'),
			14 => array('id' => 'thinheadline_dark'),
			15 => array('id' => 'largeblackbg'),
			16 => array('id' => 'largepinkbg'),
			17 => array('id' => 'largewhitebg'),
			18 => array('id' => 'largegreenbg'),
			19 => array('id' => 'excerpt'),
			20 => array('id' => 'large_bold_grey'),
			21 => array('id' => 'medium_thin_grey'),
			22 => array('id' => 'small_thin_grey'),
			23 => array('id' => 'lightgrey_divider'),
			24 => array('id' => 'large_bold_darkblue'),
			25 => array('id' => 'medium_bg_darkblue'),
			26 => array('id' => 'medium_bold_red'),
			27 => array('id' => 'medium_light_red'),
			28 => array('id' => 'medium_bg_red'),
			29 => array('id' => 'medium_bold_orange'),
			30 => array('id' => 'medium_bg_orange'),
			31 => array('id' => 'large_bold_white'),
			32 => array('id' => 'medium_light_white'),
			33 => array('id' => 'mediumlarge_light_white'),
			34 => array('id' => 'mediumlarge_light_white_center'),
			35 => array('id' => 'medium_bg_asbestos'),
			36 => array('id' => 'medium_light_black'),
			37 => array('id' => 'large_bold_black'),
			38 => array('id' => 'mediumlarge_light_darkblue'),
			39 => array('id' => 'small_light_white'),
			40 => array('id' => 'large_bg_black'),
			41 => array('id' => 'mediumwhitebg'),
			42 => array('id' => 'black_bold_bg_20'),
			43 => array('id' => 'greenbox30'),
			44 => array('id' => 'arrowicon'),
			45 => array('id' => 'fullbg_gradient'),
			46 => array('id' => 'light_heavy_70_shadowed'),
			47 => array('id' => 'black_heavy_60'),
			48 => array('id' => 'fullrounded'),
			49 => array('id' => 'white_thin_34'),
			50 => array('id' => 'white_heavy_70'),
			51 => array('id' => 'noshadow'),
			52 => array('id' => 'white_bold_bg_20'),
			53 => array('id' => 'red_bold_bg_20'),
			54 => array('id' => 'blue_bold_bg_20'),
			55 => array('id' => 'light_heavy_40'),
			56 => array('id' => 'black_heavy_60'),
			57 => array('id' => 'light_heavy_70'),
			58 => array('id' => 'big_grey_text'),
			59 => array('id' => 'small_grey_text'),
			60 => array('id' => 'raleway_extrabol'),
			61 => array('id' => 'raleway_medium'),
			62 => array('id' => 'raleway_regular'),
			63 => array('id' => 'raleway_border'),
			64 => array('id' => 'shopnow_button'),
			65 => array('id' => 'big_button'),
			66 => array('id' => 'roboto_72'),
			67 => array('id' => 'roboto_24'),
			68 => array('id' => 'roboto_72_n'),
			69 => array('id' => 'roboto_42'),
			70 => array('id' => 'roboto_36'),
		);
		$easing = array(
			0 => array('id' => 'easeOutBack'), 
			1 => array('id' => 'easeInQuad'),
			2 => array('id' => 'easeOutQuad'),
			3 => array('id' => 'easeInOutQuad'),
			4 => array('id' => 'easeInCubic'),
			5 => array('id' => 'easeOutCubic'),
			6 => array('id' => 'easeInOutCubic'),
			7 => array('id' => 'easeInQuart'),
			8 => array('id' => 'easeOutQuart'),
			9 => array('id' => 'easeInOutQuart'),
			10 => array('id' => 'easeInQuint'),
			11 => array('id' => 'easeOutQuint'),
			12 => array('id' => 'easeInOutQuint'),
			13 => array('id' => 'easeInSine'),
			14 => array('id' => 'easeOutSine'),
			15 => array('id' => 'easeInOutSine'),
			16 => array('id' => 'easeInExpo'),
			17 => array('id' => 'easeOutExpo'),
			18 => array('id' => 'easeInOutExpo'),
			19 => array('id' => 'easeInCirc'),
			20 => array('id' => 'easeOutCirc'),
			21 => array('id' => 'easeInOutCirc'),
			22 => array('id' => 'easeInElastic'),			
			23 => array('id' => 'easeOutElastic'),
			24 => array('id' => 'easeInOutElastic'),
			25 => array('id' => 'easeInBack'),
			26 => array('id' => 'easeOutBack'),
			27 => array('id' => 'easeInOutBack'),
			28 => array('id' => 'easeInBounce'),
			29 => array('id' => 'easeOutBounce'),
			30 => array('id' => 'easeInOutBounce'),			
			31 => array('id' => 'Linear.easeNone'),
			32 => array('id' => 'Power0.easeIn'),
			33 => array('id' => 'Power0.easeInOut'),			
			34 => array('id' => 'Power0.easeOut'),
			35 => array('id' => 'Power1.easeIn'),
			36 => array('id' => 'Power1.easeInOut'),
			37 => array('id' => 'Power1.easeOut'),
			38 => array('id' => 'Power2.easeIn'),			
			39 => array('id' => 'Power2.easeInOut'),			
			40 => array('id' => 'Power2.easeOut'),
			41 => array('id' => 'Power3.easeIn'),
			42 => array('id' => 'Power3.easeInOut'),
			43 => array('id' => 'Power3.easeOut'),
			44 => array('id' => 'Power4.easeIn'),
			45 => array('id' => 'Power4.easeInOut'),
			46 => array('id' => 'Power4.easeOut'),
			47 => array('id' => 'Quad.easeIn'),
			48 => array('id' => 'Quad.easeInOut'),
			49 => array('id' => 'Quad.easeOut'),
			50 => array('id' => 'Cubic.easeIn'),
			51 => array('id' => 'Cubic.easeInOut'),
			52 => array('id' => 'Cubic.easeOut'),
			53 => array('id' => 'Quart.easeIn'),
			54 => array('id' => 'Quart.easeInOut'),
			55 => array('id' => 'Quart.easeOut'),
			56 => array('id' => 'Quint.easeIn'),
			57 => array('id' => 'Quint.easeInOut'),
			58 => array('id' => 'Quint.easeOut'),
			59 => array('id' => 'Strong.easeIn'),
			60 => array('id' => 'Strong.easeInOut'),
			61 => array('id' => 'Strong.easeOut'),
			62 => array('id' => 'Back.easeIn'),
			63 => array('id' => 'Back.easeInOut'),
			64 => array('id' => 'Back.easeOut'),
			65 => array('id' => 'Bounce.easeIn'),
			66 => array('id' => 'Bounce.easeInOut'),
			67 => array('id' => 'Bounce.easeOut'),
			68 => array('id' => 'Circ.easeIn'),
			69 => array('id' => 'Circ.easeInOut'),
			70 => array('id' => 'Circ.easeOut'),
			71 => array('id' => 'Elastic.easeIn'),
			72 => array('id' => 'Elastic.easeInOut'),
			73 => array('id' => 'Elastic.easeOut'),
			74 => array('id' => 'Expo.easeIn'),
			75 => array('id' => 'Expo.easeInOut'),
			76 => array('id' => 'Expo.easeOut'),
			77 => array('id' => 'Sine.easeIn'),
			78 => array('id' => 'Sine.easeInOut'),
			79 => array('id' => 'Sine.easeOut'),
			80 => array('id' => 'SlowMo.ease')			
		);
		$incoming = array(
			0 => array('id' => '','name' => 'None'),
			1 => array('id' => 'sft','name' => 'Short from Top'), 
			2 => array('id' => 'sfb','name' => 'Short from Bottom'),
			3 => array('id' => 'sfr','name' => 'Short from Right'),
			4 => array('id' => 'sfl','name' => 'Short from Left'),
			5 => array('id' => 'lft','name' => 'Long from Top'),
			6 => array('id' => 'lfb','name' => 'Long from Bottom'),
			7 => array('id' => 'lfr','name' => 'Long from Right'),
			8 => array('id' => 'lfl','name' => 'Long from Left'),
			9 => array('id' => 'skewfromleft','name' => 'Skew from Left'),
			10 => array('id' => 'skewfromright','name' => 'Skew from Right'),
			11 => array('id' => 'skewfromleftshort','name' => 'Skew Short from Left'),
			12 => array('id' => 'skewfromrightshort','name' => 'Skew Short from Right'),
			13 => array('id' => 'fade','name' => 'fading'),
			14 => array('id' => 'randomrotate','name' => 'Fade in, Rotate from a Random position and Degree')
		);
		$outgoing = array(
			0 => array('id' => '','name' => 'None'),
			1 => array('id' => 'stt','name' => 'Short to Top'), 
			2 => array('id' => 'stb','name' => 'Short to Bottom'),
			3 => array('id' => 'str','name' => 'Short to Right'),
			4 => array('id' => 'stl','name' => 'Short to Left'),
			5 => array('id' => 'ltt','name' => 'Long to Top'),
			6 => array('id' => 'ltb','name' => 'Long to Bottom'),
			7 => array('id' => 'ltr','name' => 'Long to Right'),
			8 => array('id' => 'ltl','name' => 'Long to Left'),
			9 => array('id' => 'skewtoleft','name' => 'Skew to Left'),
			10 => array('id' => 'skewtoright','name' => 'Skew to Right'),
			11 => array('id' => 'skewtoleftshort','name' => 'Skew Short to Left'),
			12 => array('id' => 'skewtorightshort','name' => 'Skew Short to Right'),
			13 => array('id' => 'fadeout','name' => 'fading'),
			14 => array('id' => 'randomrotateout','name' => 'Fade in, Rotate from a Random position and Degree')
		);
		$grid_width = Configuration::get('JMS_SL_STARTWIDTH');
		$grid_height = Configuration::get('JMS_SL_STARTHEIGHT');		
		foreach ($layers as $key => $layer)
			$layers[$key]['status'] = $this->displayLayerStatus($layer['layer_id'], $layer['active'], $id_slide);
		//$root_url = _PS_BASE_URL_.__PS_BASE_URI__;
		$root_url = Tools::getHttpHost(true).__PS_BASE_URI__;
		$this->context->smarty->assign(
			array(
				'link' => $this->context->link,
				'layers' => $layers,
				'root_url' => $root_url,
				'caption_cls' => $caption_cls,
				'easing' => $easing,
				'incoming' => $incoming,
				'outgoing' => $outgoing,
				'layerimgs' => $this->getLayerImgs(), 
				'current_slide' => $current_slide,
				'grid_width' => $grid_width,
				'grid_height' => $grid_height,
			)
		);

		return $this->display(__FILE__, 'layerslist.tpl');
	}
	
	public function renderAddForm()
	{
		$this->context->controller->addCSS(($this->_path).'views/css/admin_style.css', 'all');
		$this->context->controller->addJS(($this->_path).'views/js/admin_script.js', 'all');		
		$transitions = array(
			0 => array('id' => 'slideup','name' => 'Slide To Top'), 
			1 => array('id' => 'slidedown','name' => 'Slide To Bottom'),
			2 => array('id' => 'slideright','name' => 'Slide To Right'),
			3 => array('id' => 'slideleft','name' => 'Slide To Left'),
			4 => array('id' => 'slidehorizontal','name' => 'Slide Horizontal (depending on Next/Previous)'),
			5 => array('id' => 'slidevertical','name' => 'Slide Vertical (depending on Next/Previous)'),
			6 => array('id' => 'boxslide','name' =>	'Slide Boxes'),
			7 => array('id' => 'slotslide-horizontal','name' =>	'Slide Slots Horizontal'),
			8 => array('id' => 'slotslide-vertical','name' => 'Slide Slots Vertical'),
			9 => array('id' => 'boxfade','name' =>	'Fade Boxes'),
			10 => array('id' => 'slotfade-horizontal','name' =>	'Fade Slots Horizontal'),
			11 => array('id' => 'slotfade-vertical','name' => 'Fade Slots Vertical'),
			12 => array('id' => 'fadefromright','name' => 'Fade and Slide from Right'),
			13 => array('id' => 'fadefromleft','name' =>	'Fade and Slide from Left'),
			14 => array('id' => 'fadefromtop','name' =>'Fade and Slide from Top'),
			15 => array('id' => 'fadefrombottom','name' => 'Fade and Slide from Bottom'),
			16 => array('id' => 'fadetoleftfadefromright','name' => 'Fade To Left and Fade From Right'),
			17 => array('id' => 'fadetorightfadefromleft','name' => 'Fade To Right and Fade From Left'),
			18 => array('id' => 'fadetotopfadefrombottom','name' => 'Fade To Top and Fade From Bottom'),
			19 => array('id' => 'fadetobottomfadefromtop','name' => 'Fade To Bottom and Fade From Top'),
			20 => array('id' => 'parallaxtoright','name' => 'Parallax to Right'),
			21 => array('id' => 'parallaxtoleft','name' => 'Parallax to Left'),
			22 => array('id' => 'parallaxtotop','name' => 'Parallax to Top'),
			23 => array('id' => 'parallaxtobottom','name' =>	'Parallax to Bottom'),
			24 => array('id' => 'scaledownfromright','name' => 'Zoom Out and Fade From Right'),
			25 => array('id' => 'scaledownfromleft','name' => 'Zoom Out and Fade From Left'),
			26 => array('id' => 'scaledownfromtop','name' =>	'Zoom Out and Fade From Top'),
			27 => array('id' => 'scaledownfrombottom','name' => 'Zoom Out and Fade From Bottom'),
			28 => array('id' => 'zoomout','name' => 'ZoomOut'),
			29 => array('id' => 'zoomin','name' => 'ZoomIn'),
			30 => array('id' => 'slotzoom-horizontal','name' => 'Zoom Slots Horizontal'),
			31 => array('id' => 'slotzoom-vertical','name' => 'Zoom Slots Vertical'),
			32 => array('id' => 'fade','name' => 'Fade'),
			33 => array('id' => 'random-static','name' => 'Random Flat'),
			34 => array('id' => 'random','name' => 'Random Flat and Premium'),
			35 => array('id' => 'curtain-1','name' => 'Curtain from Left'),
			36 => array('id' => 'curtain-2','name' => 'Curtain from Right'),
			37 => array('id' => 'curtain-3','name' => 'Curtain from Middle'),
			38 => array('id' => '3dcurtain-horizontal','name' => '3D Curtain Horizontal'),
			39 => array('id' => '3dcurtain-vertical','name' => '3D Curtain Vertical'),
			40 => array('id' => 'cube','name' => 'Cube Vertical'),
			41 => array('id' => 'cube-horizontal','name' => 'Cube Horizontal'),
			42 => array('id' => 'incube','name' => 'In Cube Vertical'),
			43 => array('id' => 'incube-horizontal','name' => 'In Cube Horizontal'),
			44 => array('id' => 'turnoff','name' => 'TurnOff Horizontal'),
			45 => array('id' => 'turnoff-vertical','name' => 'TurnOff Vertical'),
			46 => array('id' => 'papercut','name' => 'Paper Cut'),
			47 => array('id' => 'flyin','name' => 'Fly In'),
			48 => array('id' => 'random-premium','name' => 'Random Premium')
		);
		$easing = array(
			0 => array('id' => ''),
			1 => array('id' => 'easeOutBack'), 
			2 => array('id' => 'easeInQuad'),
			3 => array('id' => 'easeOutQuad'),
			4 => array('id' => 'easeInOutQuad'),
			5 => array('id' => 'easeInCubic'),
			6 => array('id' => 'easeOutCubic'),
			7 => array('id' => 'easeInOutCubic'),
			8 => array('id' => 'easeInQuart'),
			9 => array('id' => 'easeOutQuart'),
			10 => array('id' => 'easeInOutQuart'),
			11 => array('id' => 'easeInQuint'),
			12 => array('id' => 'easeOutQuint'),
			13 => array('id' => 'easeInOutQuint'),
			14 => array('id' => 'easeInSine'),
			15 => array('id' => 'easeOutSine'),
			16 => array('id' => 'easeInOutSine'),
			17 => array('id' => 'easeInExpo'),
			18 => array('id' => 'easeOutExpo'),
			19 => array('id' => 'easeInOutExpo'),
			20 => array('id' => 'easeInCirc'),
			21 => array('id' => 'easeOutCirc'),
			22 => array('id' => 'easeInOutCirc'),
			23 => array('id' => 'easeInElastic'),			
			24 => array('id' => 'easeOutElastic'),
			25 => array('id' => 'easeInOutElastic'),
			26 => array('id' => 'easeInBack'),
			27 => array('id' => 'easeOutBack'),
			28 => array('id' => 'easeInOutBack'),
			29 => array('id' => 'easeInBounce'),
			30 => array('id' => 'easeOutBounce'),
			31 => array('id' => 'easeInOutBounce'),			
			32 => array('id' => 'Linear.easeNone'),
			33 => array('id' => 'Power0.easeIn'),
			34 => array('id' => 'Power0.easeInOut'),			
			35 => array('id' => 'Power0.easeOut'),
			36 => array('id' => 'Power1.easeIn'),
			37 => array('id' => 'Power1.easeInOut'),
			38 => array('id' => 'Power1.easeOut'),
			39 => array('id' => 'Power2.easeIn'),			
			40 => array('id' => 'Power2.easeInOut'),			
			41 => array('id' => 'Power2.easeOut'),
			42 => array('id' => 'Power3.easeIn'),
			43 => array('id' => 'Power3.easeInOut'),
			44 => array('id' => 'Power3.easeOut'),
			45 => array('id' => 'Power4.easeIn'),
			46 => array('id' => 'Power4.easeInOut'),
			47 => array('id' => 'Power4.easeOut'),
			48 => array('id' => 'Quad.easeIn'),
			49 => array('id' => 'Quad.easeInOut'),
			50 => array('id' => 'Quad.easeOut'),
			51 => array('id' => 'Cubic.easeIn'),
			52 => array('id' => 'Cubic.easeInOut'),
			53 => array('id' => 'Cubic.easeOut'),
			54 => array('id' => 'Quart.easeIn'),
			55 => array('id' => 'Quart.easeInOut'),
			56 => array('id' => 'Quart.easeOut'),
			57 => array('id' => 'Quint.easeIn'),
			58 => array('id' => 'Quint.easeInOut'),
			59 => array('id' => 'Quint.easeOut'),
			60 => array('id' => 'Strong.easeIn'),
			61 => array('id' => 'Strong.easeInOut'),
			62 => array('id' => 'Strong.easeOut'),
			63 => array('id' => 'Back.easeIn'),
			64 => array('id' => 'Back.easeInOut'),
			65 => array('id' => 'Back.easeOut'),
			66 => array('id' => 'Bounce.easeIn'),
			67 => array('id' => 'Bounce.easeInOut'),
			68 => array('id' => 'Bounce.easeOut'),
			69 => array('id' => 'Circ.easeIn'),
			70 => array('id' => 'Circ.easeInOut'),
			71 => array('id' => 'Circ.easeOut'),
			72 => array('id' => 'Elastic.easeIn'),
			73 => array('id' => 'Elastic.easeInOut'),
			74 => array('id' => 'Elastic.easeOut'),
			75 => array('id' => 'Expo.easeIn'),
			76 => array('id' => 'Expo.easeInOut'),
			77 => array('id' => 'Expo.easeOut'),
			78 => array('id' => 'Sine.easeIn'),
			79 => array('id' => 'Sine.easeInOut'),
			80 => array('id' => 'Sine.easeOut'),
			81 => array('id' => 'SlowMo.ease')			
		);
		$languages = array();
		$languages[0]['id_lang'] = 0;
		$languages[0]['name'] = 'All';
		$syslanguages = Language::getLanguages(false);
		foreach ($syslanguages as $language)
			$languages[] =	$language;
				
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Slide Setting'),
					'icon' => 'icon-cogs'
				),
				'tabs' => array('general' => 'General Setting','images' => 'Background & Thumbnail'),
				'input' => array(										
					array(
						'type' => 'text',
						'label' => $this->l('Title'),
						'name' => 'title',
						'class' => 'fixed-width-xl',
						'tab' => 'general'
					),
				array(
						'type' => 'select',
						'label' => $this->l('Language'),
						'name' => 'id_lang',
						'class' => 'fixed-width-xl',
						'options' => array(
							'query' => $languages,
							'id' => 'id_lang',
							'name' => 'name'
						),
						'tab' => 'general'
					),					
					array(
						'type' => 'select',
						'label' => $this->l('Transition'),
						'name' => 'transition',
						'desc' => $this->l('The appearance transition of this slide.'),
						'options' => array(
							'query' => $transitions,
							'id' => 'id',
							'name' => 'name'
						),
						'tab' => 'general'					
					),
					array(
						'type' => 'text',
						'label' => $this->l('Slot Amount'),
						'name' => 'slotamount',
						'desc' => $this->l('The number of slots or boxes the slide is divided into. If you use boxfade, over 7 slots can be juggy.'),
						'class' => ' fixed-width-xl',
						'tab' => 'general'
					),
					array(
						'type' => 'text',
						'label' => $this->l('Master Speed'),
						'name' => 'masterspeed',
						'desc' => $this->l('The speed of the transition in "ms".  default value is 300 (0.3 sec).'),
						'class' => ' fixed-width-xl',
						'tab' => 'general'
					),
					array(
						'type' => 'text',
						'label' => $this->l('Delay'),
						'name' => 'delay',
						'desc' => $this->l('A new delay value for this Slide. If it is defined, it will overwrite the default delay time in general setting of the module.'),
						'class' => ' fixed-width-xl',
						'tab' => 'general'
					),
					array(
						'type' => 'text',
						'label' => $this->l('Link'),
						'name' => 'link',
						'desc' => $this->l('A link on the whole slide pic.'),
						'class' => ' fixed-width-xl',
						'tab' => 'general'
					),
					array(
						'type' => 'select',
						'label' => $this->l('Target'),
						'name' => 'target',
						'desc' => $this->l('The target of the Link for the whole slide pic.'),
						'options' => array(
							'query' => array(0 => array('id' => '_blank'),1 => array('id' => '_self'),2 => array('id' => '_parent'),3 => array('id' => '_top')),
							'id' => 'id',
							'name' => 'id'
							),
							'tab' => 'general'						
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Background Type (Yes - Image, No - Color)'),
						'name' => 'bg_type',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Background Image')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Background Color')
							)
						),
						'tab' => 'images'
					),
					array(
						'type' => 'color',
						'label' => $this->l('Background Color'),
						'name' => 'bg_color',						
						'desc' => $this->l('Colored Background if dont use background image.'),
						'form_group_class' => 'bg_color',
						'tab' => 'images'
					),
					array(
						'type' => 'img',
						'label' => $this->l('Background Image'),
						'name' => 'main_img',
						'link' => $this->context->link->getAdminLink('AdminJms_sliderlayer', true),						 						
						'pdesc' => $this->l('Select Background Image for slide'),						
						'form_group_class' => 'bg_img',
						'tab' => 'images'
					),
					array(
						'type' => 'select',
						'label' => $this->l('Thumbnail'),
						'name' => 'thumb_img',	
						'options' => array(
							'query' => $this->getThumbImgs(),
							'id' => 'id',
							'name' => 'name'                               
						),
						'desc' => $this->l('Please upload thumbnail image to modules/jmssliderlayer/views/img/thumbs/ folder.If use Default it will get thumbnail of background image.'),																
						'tab' => 'images'
					),
					array(
						'type' => 'html',	
						'label' => $this->l(''),					
						'name' => 'thumb_preview',
						'html_content' => '<div id="thumb_preview"></div>',
						'form_group_class' => 'thumb_img',											
						'tab' => 'images'
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Ken Burn'),
						'name' => 'kenburns',
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
						'tab' => 'general'
					),
					array(
						'type' => 'text',
						'label' => $this->l('Duration for Ken Burns'),
						'name' => 'duration',
						'desc' => $this->l('The value in ms how long the animation of ken burns effect should go. i.e. 3000 will make a 3s zoom and movement.'),
						'class' => ' fixed-width-xl',
						'form_group_class' => 'kenburns_data',	
						'tab' => 'general'
					),	
					array(
						'type' => 'select',
						'label' => $this->l('Easing of Ken Burns Effect'),
						'name' => 'ease',
						'desc' => $this->l('The Movement Easing.'),
						'form_group_class' => 'kenburns_data',	
						'options' => array(
							'query' => $easing,
							'id' => 'id',
							'name' => 'id'                               
						),	
						'tab' => 'general'					
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Background Repeat'),
						'name' => 'bgrepeat',
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
						'tab' => 'general'
					),					
					array(
						'type' => 'select',
						'label' => $this->l('Background Position'),
						'name' => 'bgposition',						
						'options' => array(
							'query' => array(0 => array('id' => ''),1 => array('id' => 'left top'),2 => array('id' => 'left center'),3 => array('id' => 'left bottom'),4 => array('id' => 'center top'),5 => array('id' => 'center center'),6 => array('id' => 'center bottom'),7 => array('id' => 'right top'),8 => array('id' => 'right center'),9 => array('id' => 'right bottom')),
							'id' => 'id',
							'name' => 'id'
							),
							'tab' => 'general'						
					),
					array(
						'type' => 'select',
						'label' => $this->l('Background Position End'),
						'name' => 'bgpositionend',	
						'desc' => $this->l('For Ken Burns Animation. This is where the IMG will be animatied'), 					
						'options' => array(
							'query' => array(0 => array('id' => ''),1 => array('id' => 'left top'),2 => array('id' => 'left center'),3 => array('id' => 'left bottom'),4 => array('id' => 'center top'),5 => array('id' => 'center center'),6 => array('id' => 'center bottom'),7 => array('id' => 'right top'),8 => array('id' => 'right center'),9 => array('id' => 'right bottom')),
							'id' => 'id',
							'name' => 'id'
							),
							'tab' => 'general'						
					),
					array(
						'type' => 'select',
						'label' => $this->l('Background Fitting'),
						'name' => 'bgfit',
						'form_group_class' => 'no_kenburns_data',							
						'options' => array(
							'query' => array(0 => array('id' => 'normal'),1 => array('id' => 'contain'),2 => array('id' => 'cover')),
							'id' => 'id',
							'name' => 'id'
							),
							'tab' => 'general'	
					),
					array(
						'type' => 'text',
						'label' => $this->l('Background Fitting'),
						'name' => 'bgfit_kenburns',
						'desc' => $this->l('For Ken Burn use only a Number, which is the % Zoom at start. 100 will fit with Width or height automatically, 200 will be double sized etc.'),
						'class' => ' fixed-width-xl',
						'form_group_class' => 'kenburns_data',								
						'tab' => 'general'	
					),
					array(
						'type' => 'text',
						'label' => $this->l('Background Fitting End'),
						'name' => 'bgfitend',
						'desc' => $this->l('Use only a Number . i.e. 300 will be a 300% Zoomed image where the basic 100% is fitting with width or height.'),
						'class' => ' fixed-width-xl',
						'tab' => 'general'	
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
						'tab' => 'general'
					),													
				),				
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		if (Tools::isSubmit('id_slide') && $this->slideExists((int)Tools::getValue('id_slide')))
		{
			$slide = new JmsSlide((int)Tools::getValue('id_slide'));
			$fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_slide');
			$fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'ordering');			
			if (count($slide->main_img) > 0)
				$fields_form['form']['img_old'] = $slide->main_img;
		}
		$fields_form['form']['buttons'][] = array('href' => $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),'title' => 'Back to Slides List','icon' => 'process-icon-back');
		$helper = new HelperForm();
		$helper->show_toolbar = false;		
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitSlide';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
			'fields_value' => $this->getAddFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
			'image_baseurl' => $this->_path.'views/img/'
		);

		$helper->override_folder = '/';		
		return $helper->generateForm(array($fields_form));
	}
		
	public function renderForm()
	{	
		$fields_form = array(
			'form' => array(
				'tabs' => array('general' => 'General Setting','navigation' => 'Navigation Setting','image' => 'Image Setting'),
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
					
				),				
				'input' => array(
					array(
						'type' => 'select',
						'label' => $this->l('The mode of the slider'),
						'name' => 'JMS_SL_MODE',
						'desc' => $this->l('Slider Mode (boxed, fullwidth, fullscreen).'),
						'options' => array('query' => array(0 => array('id' => 'boxed'),1 => array('id' => 'fullwidth'),2 => array('id' => 'fullscreen')),'id' => 'id','name' => 'id'),
						'tab' => 'general'
					),
					array(
						'type' => 'text',
						'label' => $this->l('Slider Class Suffix'),
						'name' => 'JMS_SL_CLASSSUFFIX',
						'desc' => $this->l('Class Suffix for slider, this class added to cover div of slider.'),
						'class' => ' fixed-width-xl',
						'tab' => 'general'							
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Touch Enabled'),
						'name' => 'JMS_SL_TOUCHENABLED',
						'desc' => $this->l('Enable Swipe Function on touch devices.'),
						'values'    => array(
							array('id'    => 'active_on','value' => 1,'label' => $this->l('Enabled')),
							array('id'    => 'active_off','value' => 0,'label' => $this->l('Disabled'))
							),
						'tab' => 'general'							
					),						
					array(
						'type' => 'text',
						'label' => $this->l('Delay'),
						'name' => 'JMS_SL_DELAY',
						'desc' => $this->l('The time one slide stays on the screen in Milliseconds'),
						'class' => ' fixed-width-xl',
						'tab' => 'general'						
					),
					array(
						'type' => 'text',
						'label' => $this->l('Grid Height'),
						'name' => 'JMS_SL_STARTHEIGHT',
						'desc' => $this->l('This Height of the Grid where Layers are displayed in Pixel. This Height is the Max Height of the Slider in Fullwidth Layout and in Responsive Layout. In Fullscreen Layout the Grid will be centered Vertically in case the Slider is higher than this value.'),
						'class' => ' fixed-width-xl',
						'tab' => 'general'							
					),
					array(
						'type' => 'text',
						'label' => $this->l('Grid Width'),
						'name' => 'JMS_SL_STARTWIDTH',
						'desc' => $this->l('This Width of the Grid where Layers are displayed in Pixel. This Width is the Max Width of the Slider in Responsive Layout. In Fullscreen and in FullWidth Layout the Grid will be centered Horizontally in case the Slider is wider than this value.'),
						'class' => ' fixed-width-xl',
						'tab' => 'general'							
					),
					array(
						'type' => 'select',
						'label' => $this->l('Slider Shadow'),
						'name' => 'JMS_SL_SHADOW',
						'desc' => $this->l('The shadow style of the slider box.'),
						'options' => array('query' => array(0 => array('id' => 0,'name' => 'No Shadow'),1 => array('id' => 1,'name' => 'Shadow 1'),2 => array('id' => 2,'name' => 'Shadow 2'),3 => array('id' => 3,'name' => 'Shadow 3')),'id' => 'id','name' => 'name'),
						'tab' => 'general'						
					),
					array(
						'type' => 'select',
						'label' => $this->l('Spinner'),
						'name' => 'JMS_SL_SPINNER',
						'desc' => $this->l('The Layout of Loader. If not defined, it will use the basic spinner.'),
						'options' => array('query' => array(0 => array('id' => 'spinner1','name' => 'Spinner 1'),1 => array('id' => 'spinner2','name' => 'Spinner 2'),2 => array('id' => 'spinner3','name' => 'Spinner 3'),3 => array('id' => 'spinner4','name' => 'Spinner 4'),4 => array('id' => 'spinner5','name' => 'Spinner 5')),'id' => 'id','name' => 'name'),
						'tab' => 'general'
					),
					array(
						'type' => 'switch',
						'label' => $this->l('On Hover Stop'),
						'name' => 'JMS_SL_ONHOVERSTOP',
						'desc' => $this->l('Stop the Timer if mouse is hovering the Slider.  Caption/Layer animations are not stopped !! They will just play to the end.'),
						'values'    => array(
							array('id'    => 'active_on','value' => 1,'label' => $this->l('Enabled')),
							array('id'    => 'active_off','value' => 0,'label' => $this->l('Disabled'))
							),
						'tab' => 'general'							
					),	
					array(
						'type' => 'switch',
						'label' => $this->l('Hide Timer Bar'),
						'name' => 'JMS_SL_HIDETIMERBAR',
						'desc' => $this->l('Hidden the Timer Bar.'),
						'values'    => array(
							array('id'    => 'active_on','value' => 1,'label' => $this->l('Enabled')),
							array('id'    => 'active_off','value' => 0,'label' => $this->l('Disabled'))
							),
						'tab' => 'general'							
					),
					array(
						'type' => 'select',
						'label' => $this->l('Timer Bar Position'),
						'name' => 'JMS_SL_TIMERBARPOS',
						'desc' => $this->l('The Position of timer bar.'),
						'options' => array('query' => array(0 => array('id' => 'top'),1 => array('id' => 'bottom')),'id' => 'id','name' => 'id'),
						'tab' => 'general'							
					),
					array(
						'type' => 'text',
						'label' => $this->l('Use Google Font'),
						'name' => 'JMS_SL_GOOGLEFONT',
						'desc' => $this->l('Enter google font family it loaded when use slider.'),
						'class' => ' fixed-width-xl',
						'tab' => 'general'							
					),
					
					array(
						'type' => 'switch',
						'label' => $this->l('Keyboard Navigation'),
						'name' => 'JMS_SL_NAVIGATION',
						'desc' => $this->l('Allows to use the Left/Right Arrow for Keyboard navigation when Slider is in Focus.'),
						'values'    => array(
							array('id'    => 'active_on','value' => 1,'label' => $this->l('Enabled')),
							array('id'    => 'active_off','value' => 0,'label' => $this->l('Disabled'))
							),
						'tab' => 'navigation'							
					),	
					array(
						'type' => 'select',
						'label' => $this->l('Navigation Type'),
						'name' => 'JMS_SL_NAVIGATIONTYPE',
						'desc' => $this->l('Display type of the "bullet/thumbnail" bar (Default:"None").'),
						'options' => array('query' => array(0 => array('id' => '','name' => 'None'),1 => array('id' => 'bullet','name' => 'Bullet'),2 => array('id' => 'thumb','name' => 'Thumb')),'id' => 'id','name' => 'name'),
						'tab' => 'navigation'							
					),	
					array(
						'type' => 'select',
						'label' => $this->l('Navigation Arrows'),
						'name' => 'JMS_SL_NAVIGATIONARROWS',
						'desc' => $this->l('Display position of the Navigation Arrows.'),
						'options' => array('query' => array(0 => array('id' => 'nexttobullets','name' => 'Next to Bullets'),1 => array('id' => 'solo','name' => 'Independent Position')),'id' => 'id','name' => 'name'),
						'tab' => 'navigation'							
					),
					array(
						'type' => 'select',
						'label' => $this->l('Navigation Style'),
						'name' => 'JMS_SL_NAVIGATIONSTYLE',
						'desc' => $this->l("The style of Bullets and Arrows if the navigation type is 'Bullet'."),
						'options' => array('query' => array(0 => array('id' => 'preview1','name' => 'Preview 1'),1 => array('id' => 'preview2','name' => 'Preview 2'),2 => array('id' => 'preview3','name' => 'Preview 3'),3 => array('id' => 'preview4','name' => 'Preview 4'),4 => array('id' => 'round','name' => 'Round'),5 => array('id' => 'square','name' => 'Square'),6 => array('id' => 'round-old','name' => 'Round Old'),7 => array('id' => 'square-old','name' => 'Square Old'),8 => array('id' => 'navbar-old','name' => 'Navbar Old')),'id' => 'id','name' => 'name'),
						'tab' => 'navigation'							
					),
					array(
						'type' => 'select',
						'label' => $this->l('Navigation Horizontal Align'),
						'name' => 'JMS_SL_NAVIGATIONHALIGN',
						'desc' => $this->l('Horizontal Align of the Navigation bullets / thumbs (depending on which navigation type was selected).'),
						'options' => array('query' => array(0 => array('id' => 'center','name' => 'Center'),1 => array('id' => 'left','name' => 'Left'),2 => array('id' => 'right','name' => 'Right')),'id' => 'id','name' => 'name'),
						'tab' => 'navigation'							
					),
					array(
						'type' => 'select',
						'label' => $this->l('Navigation Vertical Align'),
						'name' => 'JMS_SL_NAVIGATIONVALIGN',
						'desc' => $this->l('Vertical Align of the Navigation bullets / thumbs (depending on which navigation type was selected).'),
						'options' => array('query' => array(0 => array('id' => 'center','name' => 'Center'),1 => array('id' => 'top','name' => 'Top'),2 => array('id' => 'bottom','name' => 'Bottom')),'id' => 'id','name' => 'name'),
						'tab' => 'navigation'							
					),
					array(
						'type' => 'text',
						'label' => $this->l('Navigation Horizontal Offset'),
						'name' => 'JMS_SL_NAVIGATIONHOFFSET',
						'desc' => $this->l('The Horizontal Offset position of the navigation depending on the aligned position.'),
						'class' => ' fixed-width-xl',
						'tab' => 'navigation'							
					),
					array(
						'type' => 'text',
						'label' => $this->l('Navigation Vertical Offset'),
						'name' => 'JMS_SL_NAVIGATIONVOFFSET',
						'desc' => $this->l('The Vertical Offset position of the navigation depending on the aligned position.'),
						'class' => ' fixed-width-xl',
						'tab' => 'navigation'							
					),	
					array(
						'type' => 'switch',
						'label' => $this->l('Image Resize'),
						'name' => 'JMS_SL_IMGRESIZE',
						'desc' => $this->l('Image resize setting for background image.'),
						'values'    => array(
							array(
								'id'    => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
								),
							array(
								'id'    => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
								)
						),
						'tab' => 'image'							
					),	
					array(
						'type' => 'text',
						'label' => $this->l('Image Width'),
						'name' => 'JMS_SL_IMGWIDTH',
						'desc' => $this->l('This Width of image in Pixel. This Value use for resize image and Max-Width.'),
						'class' => ' fixed-width-xl',
						'tab' => 'image'							
					),	
					array(
						'type' => 'text',
						'label' => $this->l('Image Height'),
						'name' => 'JMS_SL_IMGHEIGHT',
						'desc' => $this->l('This Height of image in Pixel. This Value use for resize image and Max-Height.'),
						'class' => ' fixed-width-xl',	
						'tab' => 'image'							
					),
					array(
						'type' => 'text',
						'label' => $this->l('Thumbnail Width'),
						'name' => 'JMS_SL_THUMBWIDTH',
						'desc' => $this->l('This Width of thumbnail in Pixel. This Value use for resize image and Max-Width.'),
						'class' => ' fixed-width-xl',
						'tab' => 'image'							
					),
					array(
						'type' => 'text',
						'label' => $this->l('Thumbnail Height'),
						'name' => 'JMS_SL_THUMBHEIGHT',
						'desc' => $this->l('This Height of thumbnail in Pixel. This Value use for resize image and Max-Width.'),
						'class' => ' fixed-width-xl',	
						'tab' => 'image'							
					),		
				),
				
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitSlider';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array(
			'JMS_SL_CLASSSUFFIX' => Tools::getValue('JMS_SL_CLASSSUFFIX', Configuration::get('JMS_SL_CLASSSUFFIX')),
			'JMS_SL_DELAY' => Tools::getValue('JMS_SL_DELAY', Configuration::get('JMS_SL_DELAY')),
			'JMS_SL_STARTHEIGHT' => Tools::getValue('JMS_SL_STARTHEIGHT', Configuration::get('JMS_SL_STARTHEIGHT')),
			'JMS_SL_STARTWIDTH' => Tools::getValue('JMS_SL_STARTWIDTH', Configuration::get('JMS_SL_STARTWIDTH')),
			'JMS_SL_NAVIGATION' => Tools::getValue('JMS_SL_NAVIGATION', Configuration::get('JMS_SL_NAVIGATION')),
			'JMS_SL_NAVIGATIONTYPE' => Tools::getValue('JMS_SL_NAVIGATIONTYPE', Configuration::get('JMS_SL_NAVIGATIONTYPE')),
			'JMS_SL_NAVIGATIONARROWS' => Tools::getValue('JMS_SL_NAVIGATIONARROWS', Configuration::get('JMS_SL_NAVIGATIONARROWS')),
			'JMS_SL_NAVIGATIONSTYLE' => Tools::getValue('JMS_SL_NAVIGATIONSTYLE', Configuration::get('JMS_SL_NAVIGATIONSTYLE')),
			'JMS_SL_NAVIGATIONHALIGN' => Tools::getValue('JMS_SL_NAVIGATIONHALIGN', Configuration::get('JMS_SL_NAVIGATIONHALIGN')),
			'JMS_SL_NAVIGATIONVALIGN' => Tools::getValue('JMS_SL_NAVIGATIONVALIGN', Configuration::get('JMS_SL_NAVIGATIONVALIGN')),
			'JMS_SL_NAVIGATIONHOFFSET' => Tools::getValue('JMS_SL_NAVIGATIONHOFFSET', Configuration::get('JMS_SL_NAVIGATIONHOFFSET')),
			'JMS_SL_NAVIGATIONVOFFSET' => Tools::getValue('JMS_SL_NAVIGATIONVOFFSET', Configuration::get('JMS_SL_NAVIGATIONVOFFSET')),			
			'JMS_SL_MODE' => Tools::getValue('JMS_SL_MODE', Configuration::get('JMS_SL_MODE')),
			'JMS_SL_SHADOW' => Tools::getValue('JMS_SL_SHADOW', Configuration::get('JMS_SL_SHADOW')),			
			'JMS_SL_SPINNER' => Tools::getValue('JMS_SL_SPINNER', Configuration::get('JMS_SL_SPINNER')),
			'JMS_SL_ONHOVERSTOP' => Tools::getValue('JMS_SL_ONHOVERSTOP', Configuration::get('JMS_SL_ONHOVERSTOP')),
			'JMS_SL_HIDETIMERBAR' => Tools::getValue('JMS_SL_HIDETIMERBAR', Configuration::get('JMS_SL_HIDETIMERBAR')),
			'JMS_SL_TIMERBARPOS' => Tools::getValue('JMS_SL_TIMERBARPOS', Configuration::get('JMS_SL_TIMERBARPOS')),
			'JMS_SL_TOUCHENABLED' => Tools::getValue('JMS_SL_TOUCHENABLED', Configuration::get('JMS_SL_TOUCHENABLED')),
			'JMS_SL_IMGRESIZE' => Tools::getValue('JMS_SL_IMGRESIZE', Configuration::get('JMS_SL_IMGRESIZE')),
			'JMS_SL_IMGWIDTH' => Tools::getValue('JMS_SL_IMGWIDTH', Configuration::get('JMS_SL_IMGWIDTH')),
			'JMS_SL_IMGHEIGHT' => Tools::getValue('JMS_SL_IMGHEIGHT', Configuration::get('JMS_SL_IMGHEIGHT')),
			'JMS_SL_THUMBWIDTH' => Tools::getValue('JMS_SL_THUMBWIDTH', Configuration::get('JMS_SL_THUMBWIDTH')),
			'JMS_SL_THUMBHEIGHT' => Tools::getValue('JMS_SL_THUMBHEIGHT', Configuration::get('JMS_SL_THUMBHEIGHT')),
			'JMS_SL_GOOGLEFONT' => Tools::getValue('JMS_SL_GOOGLEFONT', Configuration::get('JMS_SL_GOOGLEFONT')),
			
		);
	}

	public function getAddFieldsValues()
	{
		$fields = array();

		if (Tools::isSubmit('id_slide') && $this->slideExists((int)Tools::getValue('id_slide')))
		{
			$slide = new JmsSlide((int)Tools::getValue('id_slide'));
			$fields['id_slide'] = (int)Tools::getValue('id_slide', $slide->id);	
			$fields['id_lang'] = (int)Tools::getValue('id_lang', $this->getSlideLang((int)Tools::getValue('id_slide')));	
			$fields['ordering'] = (int)Tools::getValue('ordering', $slide->ordering);			
		}
		else 
		{
			$slide = new JmsSlide();
			$slide->slotamount = 7;
			$slide->masterspeed = 300;
			$slide->bg_color = '#FFFFFF';
			$slide->kenburns = 0;
			$slide->bg_type = 1;
		}	

		$fields['active'] 			= Tools::getValue('active', $slide->active);
		$fields['ordering'] 		= Tools::getValue('ordering', $slide->ordering);
		$fields['title'] 			= Tools::getValue('title', $slide->title);
		$fields['transition'] 		= Tools::getValue('transition', $slide->transition);
		$fields['slotamount'] 		= Tools::getValue('slotamount', $slide->slotamount);
		$fields['masterspeed'] 		= Tools::getValue('masterspeed', $slide->masterspeed);
		$fields['delay'] 			= Tools::getValue('delay', $slide->delay);
		$fields['link'] 			= Tools::getValue('link', $slide->link);
		$fields['target'] 			= Tools::getValue('target', $slide->target);
		$fields['bg_type'] 			= Tools::getValue('bg_type', $slide->bg_type);
		$fields['main_img'] 		= Tools::getValue('main_img', $slide->main_img);
		$fields['bg_color'] 		= Tools::getValue('bg_color', $slide->bg_color);
		$fields['thumb_img'] 		= Tools::getValue('thumb_img', $slide->thumb_img);
		$fields['kenburns'] 		= Tools::getValue('kenburns', $slide->kenburns);
		$fields['duration'] 		= Tools::getValue('duration', $slide->duration);
		$fields['ease'] 			= Tools::getValue('ease', $slide->ease);
		$fields['bgrepeat'] 		= Tools::getValue('bgrepeat', $slide->bgrepeat);
		$fields['bgfit'] 			= Tools::getValue('bgfit', $slide->bgfit);
		$fields['bgfit_kenburns'] 	= Tools::getValue('bgfit', $slide->bgfit);
		$fields['bgfitend'] 		= Tools::getValue('bgfitend', $slide->bgfitend);		
		$fields['bgposition'] 		= Tools::getValue('bgposition', $slide->bgposition);
		$fields['bgpositionend'] 	= Tools::getValue('bgpositionend', $slide->bgpositionend);
		$fields['ordering'] 		= Tools::getValue('ordering', $slide->ordering);	
		return $fields;
	}
	public function getSlideLang($slide_id) 
	{
		return Db::getInstance()->getValue('
			SELECT id_lang FROM `'._DB_PREFIX_.'jms_sliderlayer_slides_lang` WHERE slide_id = '.$slide_id
		);	
	}
	
	public function getSlideLayers()
	{
		$this->context = Context::getContext();
		$id_shop = $this->context->shop->id;
		$id_lang = $this->context->language->id;		
		$slides = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT sls.*
			FROM '._DB_PREFIX_.'jms_sliderlayer sl
			LEFT JOIN '._DB_PREFIX_.'jms_sliderlayer_slides sls ON (sl.slide_id = sls.slide_id)			
			LEFT JOIN '._DB_PREFIX_.'jms_sliderlayer_slides_lang slsl ON (sl.slide_id = slsl.slide_id)			
			WHERE id_shop = '.(int)$id_shop.'
			AND (slsl.id_lang = '.(int)$id_lang.' OR slsl.id_lang = 0)'.
			' AND sls.`active` = 1 ORDER BY sls.ordering'
		);
		$i = 0;
		
		foreach ($slides as $slide) 
		{
			$slides[$i]['layers'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT sll.*
			FROM '._DB_PREFIX_.'jms_sliderlayer_layers sll						
			WHERE slide_id = '.(int)$slide['slide_id'].
			' AND sll.`active` = 1 ORDER BY sll.ordering ASC, sll.layer_id ASC'
			);			 
			$i++;
		}		
		return $slides;
	}
	public function hookHeader()	
	{
		if (!isset($this->context->controller->php_self) || $this->context->controller->php_self != 'index')
			return;		
		$this->context->controller->addJS(($this->_path).'views/js/jquery.themepunch.tools.min.js', 'all');		
		$this->context->controller->addJS(($this->_path).'views/js/jquery.themepunch.revolution.min.js', 'all');		
		$this->context->controller->addCSS(($this->_path).'views/css/settings.css', 'all');	
		$this->context->controller->addCSS(($this->_path).'views/css/extralayers.css', 'all');
	}
	public function hookDisplayHome()	
	{		
		if (!isset($this->context->controller->php_self) || $this->context->controller->php_self != 'index')
			return;		
		$slides = $this->getSlideLayers();
		$config = $this->getConfigFieldsValues();	
		//$root_url = _PS_BASE_URL_.__PS_BASE_URI__;
		$root_url = Tools::getHttpHost(true).__PS_BASE_URI__;
		$this->smarty->assign(array(
			'slides' => $slides,
			'root_url' => $root_url,
			'config' => $config
		));		
		return $this->display(__FILE__, 'jmssliderlayer.tpl');
	}
	public function hookDisplayTopColumn() 
	{	
		return $this->hookDisplayHome();
	}
	public function hookDisplayNav() 
	{	
		return $this->hookDisplayHome();
	}
	public function hookdisplayTop($params)
	{
		return $this->hookDisplayHome();
	}
	
}
