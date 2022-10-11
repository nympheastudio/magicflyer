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
if (Tools::getValue('action') == 'updateMenuOrdering' && Tools::getValue('menus'))
{
	$menus = Tools::getValue('menus');

	foreach ($menus as $position => $id_menu)
	{
		$res = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'jmsdropmegamenu` SET `ordering` = '.(int)$position.'
			WHERE `mitem_id` = '.(int)$id_menu
		);
	}	
}
