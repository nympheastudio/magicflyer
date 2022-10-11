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
class JmsBlogHelper
{
	static function makeAlias($url)
	{
		$url    = preg_replace('/[^a-z A-Z0-9-]*/', '', $url);
		$url    = preg_replace('/-{2,}/', '-', $url);
		$url    = preg_replace('/ /', '-', $url);
		$url    = trim(Tools::strtolower($url));
		$url    = ltrim($url, '-');
		$url    = rtrim($url, '-');
		return $url;
	}
	static function CreateThumb($src, $image, $max_width, $max_height, $resize_name, $crop) 
	{
		if ($image) 
		{
			if ($crop)
			{
				$imgInfo = getimagesize($src.'/'.$image);
				$width = $imgInfo[0];
				$height = $imgInfo[1];
				$ratio = max($max_width / $width, $max_height / $height);
				$y = ($height - $max_height / $ratio) / 2;
				$height = $max_height / $ratio;
				$x = ($width - $max_width / $ratio) / 2;
				$width = $max_width / $ratio;
				$rzname = $resize_name.$image; // get the file extension
	
				$resized = $src.'/'.$rzname;
	
				switch ($imgInfo[2]) 
				{
					case 1: 
						$im = imagecreatefromgif($src.'/'.$image); 
						break;
					case 2: 
						$im = imagecreatefromjpeg($src.'/'.$image);  
						break;
					case 3: 
						$im = imagecreatefrompng($src.'/'.$image); 
						break;
					default: 
						return '';
				}
				$newImg = imagecreatetruecolor($max_height, $max_height);
	
				/* Check if this image is PNG or GIF, then set if Transparent*/
				if	(($imgInfo[2] == 1) || ($imgInfo[2] == 3))
				{
					imagealphablending($newImg, false);
					imagesavealpha($newImg, true);
					$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
					imagefilledrectangle($newImg, 0, 0, $max_width, $max_height, $transparent);
				}				
				imagecopyresampled($newImg, $im, 0, 0, $x, $y, $max_width, $max_height, $width, $height);
	
				//Generate the file, and rename it to $newfilename
				switch ($imgInfo[2]) 
				{
					case 1: 
						imagegif($newImg, $resized); 
						break;
					case 2: 
						imagejpeg($newImg, $resized, 90);  
						break;
					case 3: 
						imagepng($newImg, $resized); 
						break;
					default: 
						return '';
				}
				return $src.'/'.$rzname;
			}
			else
			{
				$imgInfo = getimagesize($src.'/'.$image);
				$width = $imgInfo[0];
				$height = $imgInfo[1];
				if (!$max_width && !$max_height) 
				{
					$max_width = $width;
					$max_height = $height;
				} 
				else
				{
					if (!$max_width) $max_width = 1000;
					if (!$max_height) $max_height = 1000;
				}
				$x_ratio = $max_width / $width;
				$y_ratio = $max_height / $height;
				if (($width <= $max_width) && ($height <= $max_height)) 
				{
					$tn_width = $width;
					$tn_height = $height;
				} 
				else if (($x_ratio * $height) < $max_height) 
				{
					$tn_height = ceil($x_ratio * $height);
					$tn_width = $max_width;
				} 
				else 
				{
					$tn_width = ceil($y_ratio * $width);
					$tn_height = $max_height;
				}
	
				$rzname = $resize_name.$image; // get the file extension
	
				$resized = $src.'/'.$rzname;
	
				switch ($imgInfo[2]) 
				{
					case 1: 
						$im = imagecreatefromgif($src.'/'.$image); 
						break;
					case 2: 
						$im = imagecreatefromjpeg($src.'/'.$image);  
						break;
					case 3: 
						$im = imagecreatefrompng($src.'/'.$image); 
						break;
					default: 
						return '';
				}

				$newImg = imagecreatetruecolor($tn_width, $tn_height);

				/* Check if this image is PNG or GIF, then set if Transparent*/
				if	(($imgInfo[2] == 1) || ($imgInfo[2] == 3))
				{
					imagealphablending($newImg, false);
					imagesavealpha($newImg, true);
					$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
					imagefilledrectangle($newImg, 0, 0, $tn_width, $tn_height, $transparent);
				}
				imagecopyresampled($newImg, $im, 0, 0, 0, 0, $tn_width, $tn_height, $imgInfo[0], $imgInfo[1]);
	
				//Generate the file, and rename it to $newfilename
				switch ($imgInfo[2]) 
				{
					case 1: 
						imagegif($newImg, $resized); 
						break;
					case 2: 
						imagejpeg($newImg, $resized, 90);  
						break;
					case 3: 
						imagepng($newImg, $resized); 
						break;
					default: 
						return '';
				}
				return $src.'/'.$rzname;
			}
		}
	}
	static function UpdateViews($post_id) 
	{
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'jmsblog_posts` SET views = views+1 WHERE post_id = '.$post_id);
	}
	static function getCategory($category_id = 0)
	{
		$context = Context::getContext();		
		$id_lang = $context->language->id;		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT hss.`category_id` as category_id, hssl.`title` 			
			FROM '._DB_PREFIX_.'jmsblog_categories hss			
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang hssl ON (hss.category_id = hssl.category_id)
			WHERE hss.active = 1 AND hssl.id_lang = '.(int)$id_lang.
			' AND hss.`category_id` = '.$category_id.' 
			ORDER BY hss.ordering'
		);
	}
	
	static function getSettingFieldsValues()
	{		
		return array(
			'JMSBLOG_INTROTEXT_LIMIT' => (int)Tools::getValue('JMSBLOG_INTROTEXT_LIMIT', Configuration::get('JMSBLOG_INTROTEXT_LIMIT')),
			'JMSBLOG_SHOW_CATEGORY' => (int)Tools::getValue('JMSBLOG_SHOW_CATEGORY', Configuration::get('JMSBLOG_SHOW_CATEGORY')),
			'JMSBLOG_SHOW_VIEWS' => (int)Tools::getValue('JMSBLOG_SHOW_VIEWS', Configuration::get('JMSBLOG_SHOW_VIEWS')),
			'JMSBLOG_SHOW_COMMENTS' => (int)Tools::getValue('JMSBLOG_SHOW_COMMENTS', Configuration::get('JMSBLOG_SHOW_COMMENTS')),
			'JMSBLOG_SHOW_MEDIA' => (int)Tools::getValue('JMSBLOG_SHOW_MEDIA', Configuration::get('JMSBLOG_SHOW_MEDIA')),
			
			'JMSBLOG_IMAGE_WIDTH' => (int)Tools::getValue('JMSBLOG_IMAGE_WIDTH', Configuration::get('JMSBLOG_IMAGE_WIDTH')),
			'JMSBLOG_IMAGE_HEIGHT' => (int)Tools::getValue('JMSBLOG_IMAGE_HEIGHT', Configuration::get('JMSBLOG_IMAGE_HEIGHT')),
			'JMSBLOG_IMAGE_THUMB_WIDTH' => (int)Tools::getValue('JMSBLOG_IMAGE_THUMB_WIDTH', Configuration::get('JMSBLOG_IMAGE_THUMB_WIDTH')),
			'JMSBLOG_IMAGE_THUMB_HEIGHT' => (int)Tools::getValue('JMSBLOG_IMAGE_THUMB_HEIGHT', Configuration::get('JMSBLOG_IMAGE_THUMB_HEIGHT')),			

			'JMSBLOG_COMMENT_ENABLE' => (int)Tools::getValue('JMSBLOG_COMMENT_ENABLE', Configuration::get('JMSBLOG_COMMENT_ENABLE')),
			'JMSBLOG_ALLOW_GUEST_COMMENT' => (int)Tools::getValue('JMSBLOG_ALLOW_GUEST_COMMENT', Configuration::get('JMSBLOG_ALLOW_GUEST_COMMENT')),
			'JMSBLOG_FACEBOOK_COMMENT' => (int)Tools::getValue('JMSBLOG_FACEBOOK_COMMENT', Configuration::get('JMSBLOG_FACEBOOK_COMMENT')),
			'JMSBLOG_COMMENT_DELAY' => (int)Tools::getValue('JMSBLOG_COMMENT_DELAY', Configuration::get('JMSBLOG_COMMENT_DELAY')),
			'JMSBLOG_AUTO_APPROVE_COMMENT' => (int)Tools::getValue('JMSBLOG_AUTO_APPROVE_COMMENT', Configuration::get('JMSBLOG_AUTO_APPROVE_COMMENT')),
			
			'JMSBLOG_SHOW_SOCIAL_SHARING' => (int)Tools::getValue('JMSBLOG_SHOW_SOCIAL_SHARING', Configuration::get('JMSBLOG_SHOW_SOCIAL_SHARING')),
			'JMSBLOG_SHOW_FACEBOOK' => (int)Tools::getValue('JMSBLOG_SHOW_FACEBOOK', Configuration::get('JMSBLOG_SHOW_FACEBOOK')),
			'JMSBLOG_SHOW_TWITTER' => (int)Tools::getValue('JMSBLOG_SHOW_TWITTER', Configuration::get('JMSBLOG_SHOW_TWITTER')),
			'JMSBLOG_SHOW_GOOGLEPLUS' => (int)Tools::getValue('JMSBLOG_SHOW_GOOGLEPLUS', Configuration::get('JMSBLOG_SHOW_GOOGLEPLUS')),
			'JMSBLOG_SHOW_LINKEDIN' => (int)Tools::getValue('JMSBLOG_SHOW_LINKEDIN', Configuration::get('JMSBLOG_SHOW_LINKEDIN')),
			'JMSBLOG_SHOW_PINTEREST' => (int)Tools::getValue('JMSBLOG_SHOW_PINTEREST', Configuration::get('JMSBLOG_SHOW_PINTEREST')),
			'JMSBLOG_SHOW_EMAIL' => (int)Tools::getValue('JMSBLOG_SHOW_EMAIL', Configuration::get('JMSBLOG_SHOW_EMAIL')),
		);
	}
	
	static function genIntrotext($introtext, $c_limit) 
	{	
		if(Tools::strlen($introtext) <= $c_limit)
			return $introtext;
		$pos = strpos($introtext, ' ', $c_limit);
		if((int)$pos == 0)
			return $introtext;
		$result = Tools::substr($introtext, 0, $pos ); 
		if(Tools::strlen($introtext) > Tools::strlen($result))
			$result .= ' ...';
		return $result;
	}
	
	static function getCommentCount($post_id) 
	{	
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT count(cps.`comment_id`)
			FROM '._DB_PREFIX_.'jmsblog_posts_comments cps						
			WHERE cps.`status` = 1 AND cps.`post_id` = '.$post_id 
		);
	}
	
	static function getArchives() 
	{	
		$archives = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT DISTINCT DATE_FORMAT(hss.created,"%Y-%m") AS postmonth FROM '._DB_PREFIX_.'jmsblog_posts hss
		');			
		return $archives;
	}
	static function getPopularPost() 
	{	
		$context = Context::getContext();		
		$id_lang = $context->language->id;		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`post_id`,hss.`link_video`, hssl.`image`,hss.`category_id`, hss.`ordering`, hss.`active`, hssl.`title`, hss.`created`, hss.`modified`, hss.`views`,
			hssl.`alias`, hssl.`fulltext`, hssl.`introtext`,hssl.`meta_desc`, hssl.`meta_key`, hssl.`key_ref`, catsl.`title` AS category_name, catsl.`alias` AS category_alias
			FROM '._DB_PREFIX_.'jmsblog_posts hss			
			LEFT JOIN '._DB_PREFIX_.'jmsblog_posts_lang hssl ON (hss.post_id = hssl.post_id)
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang catsl ON (catsl.category_id = hss.category_id)
			WHERE hss.active = 1 AND hssl.id_lang = '.(int)$id_lang.			
			' GROUP BY hss.post_id	
			ORDER BY hss.views DESC
			LIMIT 0,'.Configuration::get('JBW_SB_ITEM_SHOW')
		);
	}
	static function getLatestPost() 
	{	
		$context = Context::getContext();		
		$id_lang = $context->language->id;		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`post_id`,hss.`link_video`, hssl.`image`,hss.`category_id`, hss.`ordering`, hss.`active`, hssl.`title`, hss.`created`, hss.`modified`, hss.`views`,
			hssl.`alias`, hssl.`fulltext`, hssl.`introtext`,hssl.`meta_desc`, hssl.`meta_key`, hssl.`key_ref`, catsl.`title` AS category_name, catsl.`alias` AS category_alias
			FROM '._DB_PREFIX_.'jmsblog_posts hss			
			LEFT JOIN '._DB_PREFIX_.'jmsblog_posts_lang hssl ON (hss.post_id = hssl.post_id)
			LEFT JOIN '._DB_PREFIX_.'jmsblog_categories_lang catsl ON (catsl.category_id = hss.category_id)
			WHERE hss.active = 1 AND hssl.id_lang = '.(int)$id_lang.
			' GROUP BY hss.post_id	
			ORDER BY hss.created DESC
			LIMIT 0,'.Configuration::get('JBW_SB_ITEM_SHOW')
		);
	}
	static function getLatestComment() 
	{	
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`comment_id`, hss.`title`, hss.`comment`, hss.`customer_name`, hss.`email`, hss.`customer_site`, hss.`time_add`, hss.`status`
			FROM '._DB_PREFIX_.'jmsblog_posts_comments hss			
			WHERE 1  AND hss.`status` = 1			
			ORDER BY hss.time_add DESC 
			LIMIT 0,'.Configuration::get('JBW_SB_ITEM_SHOW')
		);
	}
	
}