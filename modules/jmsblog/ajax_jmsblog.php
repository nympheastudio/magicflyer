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

include_once('../../config/config.inc.php');
include_once('../../init.php');

$context = Context::getContext();
$rows = array();
if (Tools::getValue('action') == 'updateCategoryOrdering' && Tools::getValue('categories'))
{
	$categories = Tools::getValue('categories');

	foreach ($categories as $position => $id_category)
	{
		$res = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'jmsblog_categories` SET `ordering` = '.(int)$position.'
			WHERE `category_id` = '.(int)$id_category
		);
	}	
}
