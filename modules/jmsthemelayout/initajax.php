<?php
/**
* 2007-2014 PrestaShop
*
* Jms Theme Setting
*
*  @author    Joommasters <joommasters@gmail.com>
*  @copyright 2007-2014 Joommasters
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.joommasters.com
*/

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/jmsthemelayout.php');

$productids = Tools::getValue('productids');

$result = array();
$jmsthemelayout = new JmsThemeLayout();

if ($productids)
{
	$productids = explode(',', $productids);
	$productids = array_unique($productids);
	$productids = implode(',', $productids);
	$result['img2arr'] = $jmsthemelayout->getSecondImgs($productids);
}
if ($result && $productids)
	die(Tools::jsonEncode($result));