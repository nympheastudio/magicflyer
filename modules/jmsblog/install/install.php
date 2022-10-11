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
include_once(_PS_MODULE_DIR_.'jmsblog/class/JmsBlogHelper.php');
include_once(_PS_MODULE_DIR_.'jmsblog/JmsCategory.php');
include_once(_PS_MODULE_DIR_.'jmsblog/JmsPost.php');
class JmsInstall
{
	public function createTable()
	{
		$sql = array();
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jmsblog_categories` (
				  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `ordering` int(10) unsigned NOT NULL DEFAULT \'0\',
				  `active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
				  `parent` int(1) unsigned NOT NULL DEFAULT \'0\',
				  PRIMARY KEY (`category_id`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `ps_jmsblog_categories_lang` (
				`category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_lang` int(10) unsigned NOT NULL,
				`title` varchar(255) NOT NULL,
				`alias` varchar(255) NOT NULL,
				`description` text NOT NULL,
				`image` varchar(255) NOT NULL,
				PRIMARY KEY (`category_id`,`id_lang`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jmsblog_posts` (
			`post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`ordering` int(10) unsigned NOT NULL DEFAULT \'0\',
			`active` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
			`category_id` int(1) unsigned NOT NULL DEFAULT \'0\',
			`created` datetime NOT NULL,
			`link_video` text NOT NULL,
			`modified` datetime NOT NULL,
			`views` int(1) unsigned NOT NULL DEFAULT \'0\',
			PRIMARY KEY (`post_id`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jmsblog_posts_comments` (
				`comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`post_id` int(10) unsigned NOT NULL,
				`title` varchar(255) NOT NULL,
				`comment` text NOT NULL,
				`customer_name` varchar(255) NOT NULL,
				`email` varchar(50) NOT NULL,
				`customer_site` varchar(50) NOT NULL,
				`time_add` datetime NOT NULL,
				`status` int(2) NOT NULL,
				PRIMARY KEY (`comment_id`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';

		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'jmsblog_posts_lang` (
				`post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_lang` int(10) unsigned NOT NULL,
				`title` varchar(255) NOT NULL,
				`alias` varchar(255) NOT NULL,
				`introtext` text NOT NULL,
				`fulltext` text NOT NULL,
				`meta_desc` text NOT NULL,
				`meta_key` text NOT NULL,
				`key_ref` text NOT NULL,
				`image` varchar(255) NOT NULL,
				`tags` text NOT NULL,
				PRIMARY KEY (`post_id`,`id_lang`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
	}
	
	public function installSamples()
	{
		$languages = Language::getLanguages(false);
		$res = true;		
		//add categories
		$row = new JmsCategory();
		foreach ($languages as $language)
		{
			$row->title[$language['id_lang']] = 'News';
			$row->alias[$language['id_lang']] = 'news';
			$row->image[$language['id_lang']] = 'catimg1.jpg';
		}	
		$row->parent = 0;
		$row->active = 1;
		$row->ordering = 0;
		$res &= $row->add();
		for	($i = 1; $i < 6; $i++) 
		{
			$row = new JmsCategory();
			foreach ($languages as $language)
			{
				$row->title[$language['id_lang']] = 'Category demo '.$i;
				$row->alias[$language['id_lang']] = 'category-demo-'.$i;
				$row->image[$language['id_lang']] = 'catimg'.($i+1).'.jpg';
			}	
			$row->parent = 0;
			$row->active = 1;
			$row->ordering = $i;
			$res &= $row->add();
		}		
		//add Item 		
		for	($i = 1; $i < 7; $i++) 
		{
			$item = new JmsPost();
			$item->created = date('Y-m-d h:i:s'); 
			$item->modified = date('Y-m-d h:i:s');
			$item->views = 5;
			$item->ordering = $i;
			$item->link_video = '';
			$item->category_id = $row->id;
			$item->tags = 'fashion,women collection,men fashion';
			$item->active = 1;
			/* Sets each langue fields */
			foreach ($languages as $language)
			{			
				$item->title[$language['id_lang']] = 'Vestibulum mattis erat';
				$item->alias[$language['id_lang']] = JmsBlogHelper::makeAlias($item->title[$language['id_lang']]).'-'.($i + 1);							
				$item->introtext[$language['id_lang']] = 'Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. ivamus elementum semper nisi. Aenean vulputate eleifend tellus.';
				
				$item->fulltext[$language['id_lang']] = '<p>Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat.</p>
<p><span class="quote"><span class="quote-author"><em class="placeholder">Hello</em> wrote:</span>Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. At vero eos et accusam et justo duo dolores et ea rebum. Consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</span></p>
<h1>h1. Heading 1</h1>
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.</p>
<ul>
<li>Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet</li>
<li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit</li>
<li>Lorem ipsum dolor sit amet, consetetur sadipscing elitr</li>
</ul>
<h2>h2. Heading 2</h2>
<p>Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>
<h3>h3. Heading 3</h3>
<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Sanctus sea sed takimata ut vero voluptua.</p>';			
				$item->image[$language['id_lang']] = 'img'.$i.'.jpg';				
			}
			$res	&= $item->add();
			
		}
		return $res;
	}
}
