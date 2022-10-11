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

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('jmssliderlayer.php');

$context = Context::getContext();
$slides = array();

if (Tools::getValue('action') == 'updateSlidesOrdering' && Tools::getValue('slides'))
{

	$slides = Tools::getValue('slides');
	
	foreach ($slides as $position => $id_slide)
	{		
		$res = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'jms_sliderlayer_slides` SET `ordering` = '.(int)$position.'
			WHERE `slide_id` = '.(int)$id_slide
		);

	}
	$jms_slider = new JmsSlide();
	$jms_slider->clearCache();
} 
else if (Tools::getValue('action') == 'addLayer' && Tools::getValue('slide_id'))
{
	$slide_id 	= Tools::getValue('slide_id');	
	$data_type	= Tools::getValue('data_type');
	if ($data_type == 'text') 
	{		
		$layer_text	= Tools::getValue('layer_text');		
		$layer = new JmsLayer();		
		$layer->slide_id 		= $slide_id;						
		$layer->title 			= 'New Layer';
		$layer->data_type 		= $data_type;		
		$layer->ordering 		= 0;		
		$layer->active 			= 1;		
		$layer->layer_text = Tools::purifyHTML($layer_text);		
		$layer->add();
	} 
	else if ($data_type == 'img') 
	{
		$layer_img	= Tools::getValue('layer_img');		
		$layer = new JmsLayer();		
		$layer->slide_id 		= $slide_id;						
		$layer->title 			= 'New Image Layer';
		$layer->data_type 		= $data_type;		
		$layer->ordering 		= 0;		
		$layer->active 			= 1;		
		$layer->layer_img 		= $layer_img;		
		$layer->add();
	} 
	else 
	{
		$layer_video = Tools::getValue('layer_video');
		$video_width = Tools::getValue('video_width');
		$video_height = Tools::getValue('video_height');	
		$layer = new JmsLayer();		
		$layer->slide_id 		= $slide_id;						
		$layer->title 			= 'New Video Layer';
		$layer->data_type 		= $data_type;		
		$layer->ordering 		= 0;		
		$layer->active 			= 1;		
		$layer->layer_video 		= $layer_video;
		$layer->video_width 		= $video_width;
		$layer->video_height 		= $video_height;
		$layer->add();
	}
} 
else if (Tools::getValue('action') == 'updateLayersOrdering' && Tools::getValue('layers'))
{
	$layers = Tools::getValue('layers');	
	foreach ($layers as $position => $id_layer)
	{		
		$res = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'jms_sliderlayer_layers` SET `ordering` = '.(int)$position.'
			WHERE `layer_id` = '.(int)$id_layer
		);
	}
	$jms_layer = new JmsLayer();
	$jms_layer->clearCache();
}